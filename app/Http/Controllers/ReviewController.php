<?php

namespace App\Http\Controllers;

use App\Models\GbpLocation;
use App\Models\Integration;
use App\Models\Review;
use App\Services\AiService;
use App\Services\CreditService;
use App\Services\GbpService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private AiService $ai,
        private GbpService $gbp,
        private CreditService $creditService,
    ) {}

    /**
     * Card grid — one card per Google location, so an admin can jump straight
     * into managing a single business's reviews instead of one giant mixed
     * list. (Syncing used to loop every location for the whole agency in one
     * request, which is what made "Sync from Google" hang — now sync is
     * scoped to whichever location's card you're on.)
     */
    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        // Client-bound users only see their own location(s).
        $clientId = $request->user()->client_id;

        $locations = GbpLocation::whereHas('client', function ($q) use ($agencyId, $clientId) {
                $q->where('agency_id', $agencyId);
                if ($clientId) $q->where('id', $clientId);
            })
            ->with('client')
            ->withCount(['reviews as total_reviews'])
            ->withCount(['reviews as unreplied_count' => fn ($q) => $q->whereNull('reply_text')])
            ->withCount(['reviews as negative_count' => fn ($q) => $q->where('sentiment', 'NEGATIVE')])
            ->withAvg('reviews', 'star_rating')
            ->orderBy('title')
            ->get();

        $connectedClientIds = Integration::where('agency_id', $agencyId)
            ->where('provider', 'GOOGLE_GBP')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->pluck('client_id')->all();

        return view('dashboard.reviews', compact('locations', 'connectedClientIds'));
    }

    /** Scoped review list + sync + reply, for a single location. */
    public function show(Request $request, GbpLocation $location)
    {
        $this->authorizeLocation($request, $location);
        $location->load('client');

        $filter = $request->query('filter', 'all');
        $query = Review::where('gbp_location_id', $location->id);
        if ($filter === 'unreplied') $query->whereNull('reply_text');
        if ($filter === 'negative') $query->where('sentiment', 'NEGATIVE');

        $reviews = $query->orderByDesc('review_time')->get();

        $all = Review::where('gbp_location_id', $location->id)->get();
        $stats = [
            'total' => $all->count(),
            'avg' => $all->count() ? round($all->avg('star_rating'), 1) : 0,
            'unreplied' => $all->whereNull('reply_text')->count(),
            'negative' => $all->where('sentiment', 'NEGATIVE')->count(),
        ];

        $isLive = $this->gbp->isLive($location->client);

        return view('dashboard.review-location', compact('location', 'reviews', 'stats', 'filter', 'isLive'));
    }

    /** Sync just this one location's reviews from Google. */
    public function sync(Request $request, GbpLocation $location)
    {
        $this->authorizeLocation($request, $location);
        $agencyId = $request->user()->agency_id;

        $raw = $this->gbp->listReviews($location->client, $location->google_name);

        if (empty($raw)) {
            $reason = $this->gbp->lastError();
            return back()->with('error', $reason ?? 'Google returned no reviews for this location yet (it may genuinely have none).');
        }

        $count = 0;
        foreach ($raw as $r) {
            $sentiment = $this->ai->analyzeSentiment($r['comment'] ?? '');
            Review::updateOrCreate(
                ['google_review_id' => $r['reviewId']],
                [
                    'agency_id' => $agencyId,
                    'gbp_location_id' => $location->id,
                    'reviewer_name' => $r['name'],
                    'star_rating' => $r['rating'],
                    'comment' => $r['comment'],
                    'sentiment' => $sentiment,
                    'review_time' => $r['time'],
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} review(s).");
    }

    public function generateReply(Request $request, Review $review)
    {
        $this->authorizeReview($request, $review);

        $agencyId = $request->user()->agency_id;
        if (!$this->creditService->canAfford($agencyId, 'ai_review_reply')) {
            return response()->json(['error' => 'Insufficient credits. You need ' . CreditService::COSTS['ai_review_reply'] . ' credit(s) for this action.'], 402);
        }

        $reply = $this->ai->generateReviewReply($review->reviewer_name, $review->comment ?? '', $review->star_rating);
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_review_reply', "Reply for review by {$review->reviewer_name}");

        return response()->json(['reply' => $reply]);
    }

    public function reply(Request $request, Review $review)
    {
        $this->authorizeReview($request, $review);
        $data = $request->validate(['reply_text' => 'required|string']);

        $location = $review->location;
        $client = $location->client;
        $ok = $this->gbp->putReply($client, $location->google_name, $review->google_review_id, $data['reply_text']);

        $review->update([
            'reply_text' => $data['reply_text'],
            'replied_at' => now(),
            'replied_by' => $request->user()->id,
        ]);

        $msg = $ok ? 'Reply posted.' : 'Reply saved locally, but Google rejected it (check connection).';
        return back()->with('success', $msg);
    }

    private function authorizeLocation(Request $request, GbpLocation $location): void
    {
        abort_unless($location->client->agency_id === $request->user()->agency_id, 403);
        $scoped = $request->user()->client_id;
        abort_if($scoped && $location->client_id !== $scoped, 403, 'You can only access your own business.');
    }

    private function authorizeReview(Request $request, Review $review): void
    {
        abort_unless($review->agency_id === $request->user()->agency_id, 403);
        $scoped = $request->user()->client_id;
        abort_if($scoped && $review->location && $review->location->client_id !== $scoped, 403);
    }
}
