<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Review;
use App\Services\AiService;
use App\Services\CreditService;
use Illuminate\Http\Request;

/**
 * Google Audit — scores a client's review/profile health from the data we
 * actually have (reviews, replies, ratings, sentiment, locations), and asks
 * the AI for improvement tips. No Google API needed — works on stored data.
 */
class AuditController extends Controller
{
    public function __construct(private AiService $ai, private CreditService $creditService) {}

    public function index(Request $request)
    {
        $clientId = $request->user()->client_id;
        $clients = Client::where('agency_id', $request->user()->agency_id)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))
            ->withCount('locations')
            ->get();

        $autoClient = $clients->count() === 1 ? $clients->first() : null;

        return view('dashboard.audit', compact('clients', 'autoClient'));
    }

    public function run(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        if (!$this->creditService->canAfford($agencyId, 'ai_audit')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_audit'] . ' needed).'], 402);
        }

        $data = $request->validate(['client_id' => 'required|exists:clients,id']);

        $client = Client::where('agency_id', $request->user()->agency_id)
            ->where('id', $data['client_id'])
            ->with('locations')
            ->firstOrFail();

        // Gather review data for this client's locations.
        $locationIds = $client->locations->pluck('id');
        $reviews = Review::whereIn('gbp_location_id', $locationIds)->get();

        $total = $reviews->count();
        $avg = $total ? round($reviews->avg('star_rating'), 1) : 0;
        $replied = $reviews->whereNotNull('reply_text')->count();
        $replyRate = $total ? round($replied / $total * 100) : 0;
        $negative = $reviews->where('sentiment', 'NEGATIVE')->count();
        $negativeUnreplied = $reviews->where('sentiment', 'NEGATIVE')->whereNull('reply_text')->count();
        $hasLocation = $client->locations->count() > 0;

        // --- Scoring (0-100) across weighted checks ---
        $checks = [];
        $score = 0;

        // 1. Has a location set up (15)
        $ok = $hasLocation;
        $score += $ok ? 15 : 0;
        $checks[] = ['label' => 'Google location connected', 'ok' => $ok, 'weight' => 15,
            'note' => $ok ? "{$client->locations->count()} location(s) set up." : 'No location added yet — reviews can\'t sync.'];

        // 2. Has reviews (15)
        $ok = $total > 0;
        $score += $ok ? 15 : 0;
        $checks[] = ['label' => 'Reviews collected', 'ok' => $ok, 'weight' => 15,
            'note' => $ok ? "{$total} reviews on record." : 'No reviews yet — run a sync or request reviews from customers.'];

        // 3. Average rating >= 4.0 (20)
        $ok = $avg >= 4.0;
        $score += $ok ? 20 : ($avg >= 3 ? 10 : 0);
        $checks[] = ['label' => 'Healthy average rating', 'ok' => $ok, 'weight' => 20,
            'note' => $total ? "Average rating is {$avg} / 5." : 'No rating yet.'];

        // 4. Reply rate >= 80% (25)
        $ok = $replyRate >= 80;
        $score += $ok ? 25 : ($replyRate >= 50 ? 15 : ($replyRate > 0 ? 8 : 0));
        $checks[] = ['label' => 'Replying to reviews', 'ok' => $ok, 'weight' => 25,
            'note' => $total ? "{$replyRate}% of reviews have a reply ({$replied}/{$total})." : 'No reviews to reply to yet.'];

        // 5. Negative reviews handled (25)
        $ok = $negative === 0 || $negativeUnreplied === 0;
        $score += $ok ? 25 : ($negativeUnreplied <= 2 ? 12 : 0);
        $checks[] = ['label' => 'Negative reviews addressed', 'ok' => $ok, 'weight' => 25,
            'note' => $negative === 0 ? 'No negative reviews — great!' : "{$negativeUnreplied} negative review(s) still need a reply."];

        $score = min(100, $score);
        $grade = $score >= 85 ? 'Excellent' : ($score >= 65 ? 'Good' : ($score >= 40 ? 'Needs work' : 'Poor'));

        // --- AI suggestions ---
        $summary = "Business: {$client->name}. Reviews: {$total}, avg rating {$avg}/5, reply rate {$replyRate}%, "
            ."negative reviews: {$negative} ({$negativeUnreplied} unreplied). Locations: {$client->locations->count()}.";
        $aiTips = $this->ai->generateContent(
            "Based on this Google Business Profile audit, give 3 short, specific action tips to improve local ranking and reputation. Data: {$summary}. Reply as 3 bullet points, no preamble."
        );
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_audit', $client->name);

        return response()->json([
            'score' => $score,
            'grade' => $grade,
            'checks' => $checks,
            'stats' => ['total' => $total, 'avg' => $avg, 'reply_rate' => $replyRate, 'negative' => $negative],
            'ai_tips' => $aiTips,
        ]);
    }
}
