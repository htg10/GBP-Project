<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GbpLocation;
use App\Models\Review;
use App\Services\AiService;
use App\Services\CreditService;
use App\Services\GbpService;
use Illuminate\Http\Request;

/**
 * JSON twin of ReviewController. Same authorization rules (agency + client
 * scoping) and the same AI-reply credit flow as the web app.
 */
class ReviewController extends Controller
{
    public function __construct(
        private AiService $ai,
        private GbpService $gbp,
        private CreditService $creditService,
    ) {}

    /** One row per GBP location, with review counts — mirrors the web card grid. */
    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
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

        return response()->json($locations->map(fn ($l) => [
            'id' => $l->id,
            'title' => $l->title,
            'address' => $l->address,
            'client_name' => $l->client->name,
            'total_reviews' => $l->total_reviews,
            'unreplied_count' => $l->unreplied_count,
            'negative_count' => $l->negative_count,
            'avg_rating' => $l->reviews_avg_star_rating ? round($l->reviews_avg_star_rating, 1) : 0,
        ]));
    }

    /** Paginated review list for a single location. */
    public function show(Request $request, GbpLocation $location)
    {
        $this->authorizeLocation($request, $location);

        $filter = $request->query('filter', 'all');
        $query = Review::where('gbp_location_id', $location->id);
        if ($filter === 'unreplied') $query->whereNull('reply_text');
        if ($filter === 'negative') $query->where('sentiment', 'NEGATIVE');

        $reviews = $query->orderByDesc('review_time')->paginate(15);

        return response()->json([
            'location' => ['id' => $location->id, 'title' => $location->title],
            'reviews' => $reviews->through(fn ($r) => [
                'id' => $r->id,
                'reviewer_name' => $r->reviewer_name,
                'reviewer_photo' => $r->reviewer_photo,
                'star_rating' => $r->star_rating,
                'comment' => $r->comment,
                'sentiment' => $r->sentiment,
                'reply_text' => $r->reply_text,
                'replied_at' => $r->replied_at,
                'review_time' => $r->review_time,
            ]),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function generateReply(Request $request, Review $review)
    {
        $this->authorizeReview($request, $review);

        $agencyId = $request->user()->agency_id;
        if (! $this->creditService->canAfford($agencyId, 'ai_review_reply')) {
            return response()->json([
                'error' => 'Insufficient credits. You need '.CreditService::COSTS['ai_review_reply'].' credit(s) for this action.',
            ], 402);
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
            'draft_reply' => null,
            'replied_at' => now(),
            'replied_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'posted_to_google' => $ok,
        ]);
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
