<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\GbpPhoto;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\Review;
use App\Models\AdReport;
use App\Models\SocialPost;
use App\Models\Subscription;
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
        // When a user is tied to a single client, they see ONLY their own
        // Google Business Profile data — never other clients in the agency.
        $clientId = $user->client_id;

        $reviews = Review::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->whereHas('location', fn ($l) => $l->where('client_id', $clientId)))
            ->get();

        $clients = Client::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))
            ->withCount('locations')->get();

        $locations = GbpLocation::whereHas('client', function ($q) use ($aid, $clientId) {
                $q->where('agency_id', $aid);
                if ($clientId) $q->where('id', $clientId);
            })
            ->with('client')
            ->withCount('reviews as total_reviews')
            ->withAvg('reviews', 'star_rating')
            ->get();

        $connectedClientIds = Integration::where('agency_id', $aid)
            ->where('provider', 'GOOGLE_GBP')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->pluck('client_id')->all();

        $hasGoogle = ! empty($connectedClientIds);

        $totalReviews = $reviews->count();
        $avgRating = $totalReviews ? round($reviews->avg('star_rating'), 1) : 0;
        $repliedCount = $reviews->whereNotNull('reply_text')->count();
        $pendingCount = $reviews->whereNull('reply_text')->count();

        $creditBalance = Subscription::where('agency_id', $aid)->value('credit_balance') ?? 0;
        $currentPlan = Subscription::where('agency_id', $aid)->value('plan') ?? 'STARTER';

        // Live-data status: when reviews were last pulled/updated from Google.
        $lastSynced = Review::where('agency_id', $aid)->max('updated_at');
        $lastSynced = $lastSynced ? \Illuminate\Support\Carbon::parse($lastSynced) : null;

        $photosUploaded = GbpPhoto::whereHas('location.client', function ($q) use ($aid, $clientId) {
            $q->where('agency_id', $aid);
            if ($clientId) $q->where('id', $clientId);
        })->count();

        $recentReviews = Review::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->whereHas('location', fn ($l) => $l->where('client_id', $clientId)))
            ->with('location.client')
            ->orderByDesc('review_time')
            ->limit(5)
            ->get();

        // ---- Advanced Reports chart data (computed from real reviews) ----

        // Star-rating distribution (5 → 1)
        $starDist = [];
        for ($s = 5; $s >= 1; $s--) {
            $starDist[$s] = $reviews->where('star_rating', $s)->count();
        }

        // Sentiment split (uses stored sentiment, falls back to star rating)
        $sentPos = $reviews->filter(fn ($r) => $r->sentiment === 'POSITIVE' || (! $r->sentiment && $r->star_rating >= 4))->count();
        $sentNeg = $reviews->filter(fn ($r) => $r->sentiment === 'NEGATIVE' || (! $r->sentiment && $r->star_rating <= 2))->count();
        $sentNeu = max(0, $totalReviews - $sentPos - $sentNeg);

        // Reviews by directory (this platform stores Google Business reviews)
        $directory = [
            'Google'   => $totalReviews,
            'Facebook' => 0,
            'Others'   => 0,
        ];

        // Last 8 months trend: labels, avg rating, review count, response time, star breakdown
        $trendLabels = $trendAvg = $trendCount = $trendResp = [];
        $breakdown = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
        foreach (range(7, 0) as $i) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = (clone $start)->endOfMonth();
            $inMonth = $reviews->filter(fn ($r) => $r->review_time && $r->review_time->between($start, $end));

            $trendLabels[] = $start->format('M');
            $cnt = $inMonth->count();
            $trendCount[] = $cnt;
            $trendAvg[] = $cnt ? round($inMonth->avg('star_rating'), 2) : null;

            for ($s = 1; $s <= 5; $s++) {
                $breakdown[$s][] = $inMonth->where('star_rating', $s)->count();
            }

            $replied = $inMonth->filter(fn ($r) => $r->replied_at && $r->review_time);
            $trendResp[] = $replied->count()
                ? round($replied->avg(fn ($r) => $r->review_time->diffInHours($r->replied_at)) / 24, 1)
                : null;
        }

        // Avg response time overall (in days) for the KPI badge
        $repliedAll = $reviews->filter(fn ($r) => $r->replied_at && $r->review_time);
        $avgResponseDays = $repliedAll->count()
            ? round($repliedAll->avg(fn ($r) => $r->review_time->diffInHours($r->replied_at)) / 24, 1)
            : 0;

        $charts = [
            'starDist'       => $starDist,
            'sentiment'      => ['positive' => $sentPos, 'neutral' => $sentNeu, 'negative' => $sentNeg],
            'directory'      => $directory,
            'replied'        => $repliedCount,
            'unreplied'      => $pendingCount,
            'trendLabels'    => $trendLabels,
            'trendAvg'       => $trendAvg,
            'trendCount'     => $trendCount,
            'trendResp'      => $trendResp,
            'breakdown'      => $breakdown,
            'avgResponse'    => $avgResponseDays,
        ];

        $stats = [
            'avg' => $avgRating,
            'reviews' => $totalReviews,
            'unreplied' => $pendingCount,
            'replied' => $repliedCount,
            'leads' => Lead::where('agency_id', $aid)->count(),
            'converted' => Lead::where('agency_id', $aid)->where('stage', 'CONVERTED')->count(),
            'spend' => AdReport::where('agency_id', $aid)->sum('spend'),
            'posts' => SocialPost::where('agency_id', $aid)->count(),
        ];

        $health = $healthService->compute($aid, $clientId);

        return view('dashboard.overview', compact(
            'stats', 'clients', 'locations', 'connectedClientIds', 'hasGoogle',
            'totalReviews', 'avgRating', 'repliedCount', 'pendingCount',
            'creditBalance', 'photosUploaded', 'recentReviews', 'charts',
            'currentPlan', 'lastSynced', 'health'
        ));
    }

    /**
     * Pull LIVE reviews from Google Business Profile for every connected
     * location in the agency, so the dashboard reflects real GBP data.
     */
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
            return back()->with('error', 'Connect a client to Google Business Profile first to pull live data.');
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

        return back()->with('success', $count
            ? "Live sync complete — {$count} review(s) pulled from Google Business Profile."
            : 'Connected, but Google returned no new reviews right now.');
    }
}
