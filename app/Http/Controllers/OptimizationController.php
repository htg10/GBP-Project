<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\GbpPhoto;
use App\Models\GbpPost;
use App\Models\Lead;
use App\Models\Review;
use App\Models\SocialPost;
use App\Services\AiService;
use App\Services\CreditService;
use Illuminate\Http\Request;

/**
 * One-Click Optimization — runs a batch of AI actions for the agency in one go:
 *   - Drafts AI replies for every review that has no reply yet
 *   - Generates a fresh social post caption idea
 * Returns a summary of what was done. Uses Gemini when configured, else local.
 */
class OptimizationController extends Controller
{
    public function __construct(private AiService $ai, private CreditService $creditService) {}

    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $pending = Review::where('agency_id', $agencyId)->whereNull('reply_text')->count();
        $total = Review::where('agency_id', $agencyId)->count();
        $negative = Review::where('agency_id', $agencyId)->where('sentiment', 'NEGATIVE')->count();

        $locQ = GbpLocation::whereHas('client', fn ($q) => $q->where('agency_id', $agencyId));
        if ($clientId) $locQ->whereHas('client', fn ($q) => $q->where('id', $clientId));
        $locationCount = $locQ->count();

        $postTotal = GbpPost::whereHas('location.client', fn ($q) => $q->where('agency_id', $agencyId))->count();
        $postPublished = GbpPost::whereHas('location.client', fn ($q) => $q->where('agency_id', $agencyId))->where('status', 'PUBLISHED')->count();
        $photoCount = GbpPhoto::whereHas('location.client', fn ($q) => $q->where('agency_id', $agencyId))->count();
        $socialDrafts = SocialPost::where('agency_id', $agencyId)->where('status', 'DRAFT')->count();
        $socialTotal = SocialPost::where('agency_id', $agencyId)->count();
        $leadsNew = Lead::where('agency_id', $agencyId)->whereIn('stage', ['NEW', 'CONTACTED'])->count();
        $leadsTotal = Lead::where('agency_id', $agencyId)->count();

        $categories = [
            ['key' => 'reviews', 'icon' => '★', 'label' => 'Review Management', 'color' => 'amber',
             'score' => $total ? round(($total - $pending) / $total * 100) : 100,
             'stats' => ['Total reviews' => $total, 'Pending replies' => $pending, 'Negative reviews' => $negative],
             'tip' => 'Reply to all reviews within 24 hours to boost your Google ranking.'],
            ['key' => 'posts', 'icon' => '📝', 'label' => 'Google Posts', 'color' => 'blue',
             'score' => $postTotal ? round($postPublished / $postTotal * 100) : 0,
             'stats' => ['Total posts' => $postTotal, 'Published' => $postPublished, 'Locations' => $locationCount],
             'tip' => 'Post weekly updates to keep your business profile fresh and visible.'],
            ['key' => 'photos', 'icon' => '📷', 'label' => 'Business Photos', 'color' => 'green',
             'score' => min(100, $photoCount * 10),
             'stats' => ['Photos uploaded' => $photoCount, 'Locations' => $locationCount],
             'tip' => 'Businesses with 10+ photos get 35% more clicks than those without.'],
            ['key' => 'social', 'icon' => '💬', 'label' => 'Social Media', 'color' => 'purple',
             'score' => $socialTotal ? round(max(0, $socialTotal - $socialDrafts) / max(1, $socialTotal) * 100) : 0,
             'stats' => ['Total posts' => $socialTotal, 'Drafts' => $socialDrafts],
             'tip' => 'Consistent social posting builds trust and drives local engagement.'],
            ['key' => 'leads', 'icon' => '🎯', 'label' => 'Lead Follow-up', 'color' => 'rose',
             'score' => $leadsTotal ? round(max(0, $leadsTotal - $leadsNew) / max(1, $leadsTotal) * 100) : 100,
             'stats' => ['Total leads' => $leadsTotal, 'Needs follow-up' => $leadsNew],
             'tip' => 'Respond to new leads within 5 minutes for the best conversion rate.'],
        ];

        $overallScore = count($categories) ? round(collect($categories)->avg('score')) : 0;

        return view('dashboard.optimize', compact('pending', 'total', 'categories', 'overallScore'));
    }

    public function run(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        if (!$this->creditService->canAfford($agencyId, 'ai_optimize')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_optimize'] . ' needed).'], 402);
        }

        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_optimize', 'One-click optimize batch');
        $actions = [];

        // 1. Generate AI draft replies (NOT auto-posted — user reviews and posts manually).
        $unreplied = Review::where('agency_id', $agencyId)->whereNull('reply_text')->whereNull('draft_reply')->get();
        $drafted = 0;
        foreach ($unreplied as $review) {
            $reply = $this->ai->generateReviewReply($review->reviewer_name, $review->comment ?? '', $review->star_rating);
            $review->update(['draft_reply' => $reply]);
            $drafted++;
        }
        if ($drafted > 0) {
            $actions[] = ['icon' => '★', 'title' => "Drafted {$drafted} review reply(s)", 'detail' => 'AI generated draft replies — go to Reviews to review and post them to Google.'];
        } else {
            $actions[] = ['icon' => '★', 'title' => 'All reviews already replied', 'detail' => 'No pending reviews found — you are all caught up.'];
        }

        // 2. Generate a social post idea for the first client.
        $client = Client::where('agency_id', $agencyId)->first();
        if ($client) {
            $caption = $this->ai->generateContent("Write a short promotional social media post for {$client->name}");
            SocialPost::create([
                'agency_id' => $agencyId,
                'client_id' => $client->id,
                'platform' => 'FACEBOOK',
                'body' => $caption,
                'status' => 'DRAFT',
            ]);
            $actions[] = ['icon' => '◈', 'title' => 'Drafted a social post', 'detail' => 'A ready-to-edit post was added to your Social drafts.'];
        }

        // 3. Sentiment recap.
        $negative = Review::where('agency_id', $agencyId)->where('sentiment', 'NEGATIVE')->count();
        if ($negative > 0) {
            $actions[] = ['icon' => '▽', 'title' => "{$negative} negative review(s) flagged", 'detail' => 'These were replied to with care — consider following up personally too.'];
        }

        return response()->json(['actions' => $actions]);
    }
}
