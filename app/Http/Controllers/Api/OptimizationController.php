<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            ['key' => 'reviews', 'label' => 'Review Management', 'color' => 'amber',
             'score' => $total ? round(($total - $pending) / $total * 100) : 100,
             'stats' => ['total_reviews' => $total, 'pending_replies' => $pending, 'negative_reviews' => $negative]],
            ['key' => 'posts', 'label' => 'Google Posts', 'color' => 'blue',
             'score' => $postTotal ? round($postPublished / $postTotal * 100) : 0,
             'stats' => ['total_posts' => $postTotal, 'published' => $postPublished, 'locations' => $locationCount]],
            ['key' => 'photos', 'label' => 'Business Photos', 'color' => 'green',
             'score' => min(100, $photoCount * 10),
             'stats' => ['photos_uploaded' => $photoCount, 'locations' => $locationCount]],
            ['key' => 'social', 'label' => 'Social Media', 'color' => 'purple',
             'score' => $socialTotal ? round(max(0, $socialTotal - $socialDrafts) / max(1, $socialTotal) * 100) : 0,
             'stats' => ['total_posts' => $socialTotal, 'drafts' => $socialDrafts]],
            ['key' => 'leads', 'label' => 'Lead Follow-up', 'color' => 'rose',
             'score' => $leadsTotal ? round(max(0, $leadsTotal - $leadsNew) / max(1, $leadsTotal) * 100) : 100,
             'stats' => ['total_leads' => $leadsTotal, 'needs_followup' => $leadsNew]],
        ];

        $overallScore = count($categories) ? round(collect($categories)->avg('score')) : 0;

        return response()->json([
            'overall_score' => $overallScore,
            'categories' => $categories,
            'pending_replies' => $pending,
            'total_reviews' => $total,
        ]);
    }

    public function run(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        if (! $this->creditService->canAfford($agencyId, 'ai_optimize')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_optimize'] . ' needed).'], 402);
        }

        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_optimize', 'One-click optimize batch');
        $actions = [];

        $unreplied = Review::where('agency_id', $agencyId)->whereNull('reply_text')->whereNull('draft_reply')->get();
        $drafted = 0;
        foreach ($unreplied as $review) {
            $reply = $this->ai->generateReviewReply($review->reviewer_name, $review->comment ?? '', $review->star_rating);
            $review->update(['draft_reply' => $reply]);
            $drafted++;
        }
        if ($drafted > 0) {
            $actions[] = ['title' => "Drafted {$drafted} review reply(s)", 'detail' => 'AI generated draft replies — go to Reviews to review and post them to Google.'];
        } else {
            $actions[] = ['title' => 'All reviews already replied', 'detail' => 'No pending reviews found — you are all caught up.'];
        }

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
            $actions[] = ['title' => 'Drafted a social post', 'detail' => 'A ready-to-edit post was added to your Social drafts.'];
        }

        $negative = Review::where('agency_id', $agencyId)->where('sentiment', 'NEGATIVE')->count();
        if ($negative > 0) {
            $actions[] = ['title' => "{$negative} negative review(s) flagged", 'detail' => 'These were replied to with care — consider following up personally too.'];
        }

        return response()->json(['actions' => $actions]);
    }
}
