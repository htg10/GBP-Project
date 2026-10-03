<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Review;
use App\Services\AiService;
use App\Services\CreditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __construct(private AiService $ai, private CreditService $creditService) {}

    public function run(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        if (! $this->creditService->canAfford($agencyId, 'ai_audit')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_audit'] . ' needed).'], 402);
        }

        $data = $request->validate(['client_id' => 'required|exists:clients,id']);

        $client = Client::where('agency_id', $agencyId)
            ->where('id', $data['client_id'])
            ->with('locations')
            ->firstOrFail();

        $locationIds = $client->locations->pluck('id');
        $reviews = Review::whereIn('gbp_location_id', $locationIds)->get();

        $total = $reviews->count();
        $avg = $total ? round($reviews->avg('star_rating'), 1) : 0;
        $replied = $reviews->whereNotNull('reply_text')->count();
        $replyRate = $total ? round($replied / $total * 100) : 0;
        $negative = $reviews->where('sentiment', 'NEGATIVE')->count();
        $negativeUnreplied = $reviews->where('sentiment', 'NEGATIVE')->whereNull('reply_text')->count();
        $hasLocation = $client->locations->count() > 0;

        $checks = [];
        $score = 0;

        $ok = $hasLocation;
        $score += $ok ? 15 : 0;
        $checks[] = ['label' => 'Google location connected', 'ok' => $ok, 'weight' => 15,
            'note' => $ok ? "{$client->locations->count()} location(s) set up." : 'No location added yet — reviews can\'t sync.'];

        $ok = $total > 0;
        $score += $ok ? 15 : 0;
        $checks[] = ['label' => 'Reviews collected', 'ok' => $ok, 'weight' => 15,
            'note' => $ok ? "{$total} reviews on record." : 'No reviews yet — run a sync or request reviews from customers.'];

        $ok = $avg >= 4.0;
        $score += $ok ? 20 : ($avg >= 3 ? 10 : 0);
        $checks[] = ['label' => 'Healthy average rating', 'ok' => $ok, 'weight' => 20,
            'note' => $total ? "Average rating is {$avg} / 5." : 'No rating yet.'];

        $ok = $replyRate >= 80;
        $score += $ok ? 25 : ($replyRate >= 50 ? 15 : ($replyRate > 0 ? 8 : 0));
        $checks[] = ['label' => 'Replying to reviews', 'ok' => $ok, 'weight' => 25,
            'note' => $total ? "{$replyRate}% of reviews have a reply ({$replied}/{$total})." : 'No reviews to reply to yet.'];

        $ok = $negative === 0 || $negativeUnreplied === 0;
        $score += $ok ? 25 : ($negativeUnreplied <= 2 ? 12 : 0);
        $checks[] = ['label' => 'Negative reviews addressed', 'ok' => $ok, 'weight' => 25,
            'note' => $negative === 0 ? 'No negative reviews — great!' : "{$negativeUnreplied} negative review(s) still need a reply."];

        $score = min(100, $score);
        $grade = $score >= 85 ? 'Excellent' : ($score >= 65 ? 'Good' : ($score >= 40 ? 'Needs work' : 'Poor'));

        $summary = "Business: {$client->name}. Reviews: {$total}, avg rating {$avg}/5, reply rate {$replyRate}%, "
            . "negative reviews: {$negative} ({$negativeUnreplied} unreplied). Locations: {$client->locations->count()}.";
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
