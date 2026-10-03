<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GbpLocation;
use App\Models\Integration;
use App\Models\Review;
use App\Services\AiService;
use App\Services\GbpService;
use App\Services\HealthScoreService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, HealthScoreService $healthService)
    {
        $user = $request->user();
        $aid = $user->agency_id;
        $clientId = $user->client_id;

        $reviews = Review::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->whereHas('location', fn ($l) => $l->where('client_id', $clientId)))
            ->get();

        $locations = GbpLocation::whereHas('client', function ($q) use ($aid, $clientId) {
                $q->where('agency_id', $aid);
                if ($clientId) $q->where('id', $clientId);
            })
            ->with('client')
            ->withCount('reviews as total_reviews')
            ->withAvg('reviews', 'star_rating')
            ->get();

        $hasGoogle = Integration::where('agency_id', $aid)
            ->where('provider', 'GOOGLE_GBP')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->exists();

        $recentReviews = Review::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->whereHas('location', fn ($l) => $l->where('client_id', $clientId)))
            ->with('location.client')
            ->orderByDesc('review_time')
            ->limit(5)
            ->get();

        return response()->json([
            'health' => $healthService->compute($aid, $clientId),
            'stats' => [
                'total_reviews' => $reviews->count(),
                'avg_rating' => $reviews->count() ? round($reviews->avg('star_rating'), 1) : 0,
                'replied' => $reviews->whereNotNull('reply_text')->count(),
                'unreplied' => $reviews->whereNull('reply_text')->count(),
            ],
            'has_google' => $hasGoogle,
            'locations' => $locations->map(fn ($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'address' => $l->address,
                'client_name' => $l->client->name,
                'total_reviews' => $l->total_reviews,
                'avg_rating' => $l->reviews_avg_star_rating ? round($l->reviews_avg_star_rating, 1) : 0,
            ]),
            'recent_reviews' => $recentReviews->map(fn ($r) => [
                'id' => $r->id,
                'reviewer_name' => $r->reviewer_name,
                'star_rating' => $r->star_rating,
                'comment' => $r->comment,
                'location_title' => $r->location?->title,
                'review_time' => $r->review_time,
            ]),
        ]);
    }

    public function syncAll(Request $request, GbpService $gbp, AiService $ai)
    {
        $user = $request->user();
        $aid = $user->agency_id;
        $clientId = $user->client_id;

        $connectedClientIds = Integration::where('agency_id', $aid)
            ->where('provider', 'GOOGLE_GBP')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->pluck('client_id')->all();

        if (empty($connectedClientIds)) {
            return response()->json(['message' => 'Connect a client to Google Business Profile first.'], 422);
        }

        $locations = GbpLocation::whereIn('client_id', $connectedClientIds)->with('client')->get();

        $count = 0;
        foreach ($locations as $loc) {
            $raw = $gbp->listReviews($loc->client, $loc->google_name);
            foreach ($raw as $r) {
                $data = [
                    'agency_id' => $aid,
                    'gbp_location_id' => $loc->id,
                    'reviewer_name' => $r['name'],
                    'reviewer_photo' => $r['photo'] ?? null,
                    'star_rating' => $r['rating'],
                    'comment' => $r['comment'],
                    'sentiment' => $ai->analyzeSentiment($r['comment'] ?? ''),
                    'review_time' => $r['time'],
                ];
                if (! empty($r['reply'])) {
                    $data['reply_text'] = $r['reply'];
                    $data['replied_at'] = $r['reply_time'] ?? now();
                }
                Review::updateOrCreate(
                    ['google_review_id' => $r['reviewId']],
                    $data
                );
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $count,
        ]);
    }
}
