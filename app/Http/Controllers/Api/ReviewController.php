<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GbpLocation;
use App\Models\Review;
use App\Models\Subscription;
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

    public function show(Request $request, GbpLocation $location)
    {
        $this->authorizeLocation($request, $location);

        $filter = $request->query('filter', 'all');
        $query = Review::where('gbp_location_id', $location->id);
        if ($filter === 'unreplied') $query->whereNull('reply_text');
        if ($filter === 'negative') $query->where('sentiment', 'NEGATIVE');

        $reviews = $query->orderByDesc('review_time')->paginate(15);

        $all = Review::where('gbp_location_id', $location->id)->get();
        $stats = [
            'total' => $all->count(),
            'avg' => $all->count() ? round($all->avg('star_rating'), 1) : 0,
            'unreplied' => $all->whereNull('reply_text')->count(),
            'negative' => $all->where('sentiment', 'NEGATIVE')->count(),
        ];

        return response()->json([
            'location' => ['id' => $location->id, 'title' => $location->title],
            'stats' => $stats,
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

    public function sync(Request $request, GbpLocation $location)
    {
        $this->authorizeLocation($request, $location);
        $agencyId = $request->user()->agency_id;

        $raw = $this->gbp->listReviews($location->client, $location->google_name);

        if (empty($raw)) {
            return response()->json([
                'message' => $this->gbp->lastError() ?? 'Google returned no reviews for this location.',
                'synced' => 0,
            ]);
        }

        $autoReply = (bool) Subscription::where('agency_id', $agencyId)->value('auto_reply');

        $count = 0;
        foreach ($raw as $r) {
            $data = [
                'agency_id' => $agencyId,
                'gbp_location_id' => $location->id,
                'reviewer_name' => $r['name'],
                'reviewer_photo' => $r['photo'] ?? null,
                'star_rating' => $r['rating'],
                'comment' => $r['comment'],
                'sentiment' => $this->ai->analyzeSentiment($r['comment'] ?? ''),
                'review_time' => $r['time'],
            ];
            if (! empty($r['reply'])) {
                $data['reply_text'] = $r['reply'];
                $data['replied_at'] = $r['reply_time'] ?? now();
            } else {
                $data['reply_text'] = null;
                $data['replied_at'] = null;
            }
            $review = Review::updateOrCreate(
                ['google_review_id' => $r['reviewId']],
                $data
            );

            if ($autoReply && ! $review->reply_text && $this->creditService->canAfford($agencyId, 'ai_review_reply')) {
                $reply = $this->ai->generateReviewReply($review->reviewer_name, $review->comment ?? '', $review->star_rating);
                $this->gbp->putReply($location->client, $location->google_name, $review->google_review_id, $reply);
                $review->update(['reply_text' => $reply, 'replied_at' => now(), 'replied_by' => $request->user()->id]);
                $this->creditService->deduct($agencyId, $request->user()->id, 'ai_review_reply', 'Auto-reply');
            }
            $count++;
        }

        return response()->json(['success' => true, 'synced' => $count, 'auto_reply' => $autoReply]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'auto_reply' => 'nullable|boolean',
            'wa_notify' => 'nullable|boolean',
        ]);

        Subscription::where('agency_id', $request->user()->agency_id)
            ->update([
                'auto_reply' => $request->boolean('auto_reply'),
                'wa_notify' => $request->boolean('wa_notify'),
            ]);

        return response()->json(['success' => true]);
    }

    public function generateReply(Request $request, Review $review)
    {
        $this->authorizeReview($request, $review);

        $agencyId = $request->user()->agency_id;
        if (! $this->creditService->canAfford($agencyId, 'ai_review_reply')) {
            return response()->json([
                'error' => 'Insufficient credits. You need ' . CreditService::COSTS['ai_review_reply'] . ' credit(s).',
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

        return response()->json(['success' => true, 'posted_to_google' => $ok]);
    }

    private function authorizeLocation(Request $request, GbpLocation $location): void
    {
        abort_unless($location->client->agency_id === $request->user()->agency_id, 403);
        $scoped = $request->user()->client_id;
        abort_if($scoped && $location->client_id !== $scoped, 403);
    }

    private function authorizeReview(Request $request, Review $review): void
    {
        abort_unless($review->agency_id === $request->user()->agency_id, 403);
        $scoped = $request->user()->client_id;
        abort_if($scoped && $review->location && $review->location->client_id !== $scoped, 403);
    }
}
