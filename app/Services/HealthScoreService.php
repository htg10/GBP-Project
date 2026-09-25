<?php

namespace App\Services;

use App\Models\AdReport;
use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\GbpPhoto;
use App\Models\GbpPost;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\Review;
use App\Models\SocialPost;

class HealthScoreService
{
    public function compute(int $agencyId, ?int $clientId = null): array
    {
        $locQ = fn () => GbpLocation::whereHas('client', function ($q) use ($agencyId, $clientId) {
            $q->where('agency_id', $agencyId);
            if ($clientId) $q->where('id', $clientId);
        });

        $reviews = Review::where('agency_id', $agencyId)
            ->when($clientId, fn ($q) => $q->whereHas('location', fn ($l) => $l->where('client_id', $clientId)))
            ->get();

        $totalReviews = $reviews->count();
        $avgRating = $totalReviews ? round($reviews->avg('star_rating'), 1) : 0;
        $repliedCount = $reviews->whereNotNull('reply_text')->count();
        $pendingCount = $reviews->whereNull('reply_text')->count();
        $negativeCount = $reviews->where('sentiment', 'NEGATIVE')->count();

        $locationCount = $locQ()->count();
        $postTotal = GbpPost::whereHas('location.client', fn ($q) => $q->where('agency_id', $agencyId)
            ->when($clientId, fn ($q2) => $q2->where('id', $clientId)))->count();
        $postPublished = GbpPost::whereHas('location.client', fn ($q) => $q->where('agency_id', $agencyId)
            ->when($clientId, fn ($q2) => $q2->where('id', $clientId)))->where('status', 'PUBLISHED')->count();
        $photoCount = GbpPhoto::whereHas('location.client', fn ($q) => $q->where('agency_id', $agencyId)
            ->when($clientId, fn ($q2) => $q2->where('id', $clientId)))->count();
        $socialTotal = SocialPost::where('agency_id', $agencyId)->count();
        $socialPublished = SocialPost::where('agency_id', $agencyId)->whereNotIn('status', ['DRAFT'])->count();
        $socialDrafts = SocialPost::where('agency_id', $agencyId)->where('status', 'DRAFT')->count();
        $leadsTotal = Lead::where('agency_id', $agencyId)->count();
        $leadsNew = Lead::where('agency_id', $agencyId)->whereIn('stage', ['NEW', 'CONTACTED'])->count();
        $leadsConverted = Lead::where('agency_id', $agencyId)->where('stage', 'CONVERTED')->count();

        $hasGoogle = Integration::where('agency_id', $agencyId)
            ->where('provider', 'GOOGLE_GBP')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->exists();

        $hasMeta = Integration::where('agency_id', $agencyId)
            ->where('provider', 'META')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->exists();

        $reviewScore = $totalReviews
            ? round(min(100, ($avgRating / 5 * 40) + (($totalReviews - $pendingCount) / $totalReviews * 60)))
            : 0;

        $postScore = $postTotal ? round($postPublished / $postTotal * 100) : 0;
        $photoScore = min(100, $photoCount * 10);
        $socialScore = $socialTotal ? round(($socialTotal - $socialDrafts) / max(1, $socialTotal) * 100) : 0;
        $leadScore = $leadsTotal ? round(($leadsTotal - $leadsNew) / max(1, $leadsTotal) * 100) : 100;

        $connectionScore = 0;
        if ($hasGoogle) $connectionScore += 50;
        if ($hasMeta) $connectionScore += 50;
        if (!$hasGoogle && !$hasMeta) $connectionScore = 0;
        elseif ($hasGoogle && !$hasMeta) $connectionScore = 70;
        elseif (!$hasGoogle && $hasMeta) $connectionScore = 40;
        else $connectionScore = 100;

        $subScores = [
            ['key' => 'reviews', 'label' => 'Reviews', 'score' => $reviewScore, 'icon' => '★', 'color' => '#f59e0b',
             'detail' => $totalReviews . ' reviews · ' . number_format($avgRating, 1) . '★ avg · ' . $pendingCount . ' pending'],
            ['key' => 'posts', 'label' => 'Google Posts', 'score' => $postScore, 'icon' => '📝', 'color' => '#4c6fff',
             'detail' => $postPublished . '/' . $postTotal . ' published'],
            ['key' => 'photos', 'label' => 'Photos', 'score' => $photoScore, 'icon' => '📷', 'color' => '#22c55e',
             'detail' => $photoCount . ' photos uploaded'],
            ['key' => 'social', 'label' => 'Social', 'score' => $socialScore, 'icon' => '💬', 'color' => '#8b5cf6',
             'detail' => $socialPublished . '/' . $socialTotal . ' published'],
            ['key' => 'leads', 'label' => 'Leads', 'score' => $leadScore, 'icon' => '🎯', 'color' => '#ec4899',
             'detail' => $leadsConverted . '/' . $leadsTotal . ' converted'],
            ['key' => 'connections', 'label' => 'Connections', 'score' => $connectionScore, 'icon' => '🔗', 'color' => '#38bdf8',
             'detail' => ($hasGoogle ? 'Google ✓' : 'Google ✗') . ' · ' . ($hasMeta ? 'Meta ✓' : 'Meta ✗')],
        ];

        $overallScore = round(collect($subScores)->avg('score'));

        $doNext = $this->buildDoNext(
            $pendingCount, $negativeCount, $totalReviews, $avgRating,
            $postTotal, $postPublished, $photoCount, $socialDrafts, $socialTotal,
            $leadsNew, $leadsTotal, $hasGoogle, $hasMeta, $locationCount
        );

        return [
            'overall' => $overallScore,
            'subScores' => $subScores,
            'doNext' => $doNext,
        ];
    }

    private function buildDoNext(
        int $pendingCount, int $negativeCount, int $totalReviews, float $avgRating,
        int $postTotal, int $postPublished, int $photoCount, int $socialDrafts, int $socialTotal,
        int $leadsNew, int $leadsTotal, bool $hasGoogle, bool $hasMeta, int $locationCount
    ): array {
        $actions = [];

        if (!$hasGoogle) {
            $actions[] = [
                'priority' => 1, 'icon' => '🔗', 'module' => 'Setup',
                'title' => 'Connect Google Business Profile',
                'why' => 'Everything starts here — connect Google to pull live reviews, insights, and posts.',
                'route' => 'clients', 'cta' => 'Connect Now',
                'impact' => 'critical',
            ];
        }

        if ($pendingCount > 0) {
            $actions[] = [
                'priority' => 2, 'icon' => '★', 'module' => 'Reviews',
                'title' => "Reply to {$pendingCount} pending review" . ($pendingCount > 1 ? 's' : ''),
                'why' => 'Responding to reviews boosts rankings and shows customers you care.',
                'route' => 'reviews', 'cta' => 'Open Reviews',
                'impact' => 'high',
            ];
        }

        if ($negativeCount > 0) {
            $actions[] = [
                'priority' => 3, 'icon' => '⚠', 'module' => 'Reviews',
                'title' => "Address {$negativeCount} negative review" . ($negativeCount > 1 ? 's' : ''),
                'why' => 'Negative reviews need prompt, empathetic replies to protect your reputation.',
                'route' => 'reviews', 'cta' => 'View Negative',
                'impact' => 'high',
            ];
        }

        if ($photoCount < 10) {
            $need = 10 - $photoCount;
            $actions[] = [
                'priority' => 4, 'icon' => '📷', 'module' => 'Photos',
                'title' => "Upload {$need} more photo" . ($need > 1 ? 's' : '') . " to your profile",
                'why' => 'Businesses with 10+ photos get 35% more clicks on Google.',
                'route' => 'gbp-content', 'cta' => 'Upload Photos',
                'impact' => 'medium',
            ];
        }

        if ($postTotal < 4) {
            $actions[] = [
                'priority' => 5, 'icon' => '📝', 'module' => 'Posts',
                'title' => 'Create a Google Business post',
                'why' => 'Regular posts keep your profile fresh and visible in local search.',
                'route' => 'gbp-content', 'cta' => 'Create Post',
                'impact' => 'medium',
            ];
        }

        if ($socialDrafts > 0) {
            $actions[] = [
                'priority' => 6, 'icon' => '💬', 'module' => 'Social',
                'title' => "Publish {$socialDrafts} draft social post" . ($socialDrafts > 1 ? 's' : ''),
                'why' => 'Draft posts are ready to go — review and publish them.',
                'route' => 'social', 'cta' => 'View Drafts',
                'impact' => 'low',
            ];
        }

        if ($leadsNew > 0) {
            $actions[] = [
                'priority' => 7, 'icon' => '🎯', 'module' => 'Leads',
                'title' => "Follow up on {$leadsNew} new lead" . ($leadsNew > 1 ? 's' : ''),
                'why' => 'Respond to leads within 5 minutes for the best conversion rate.',
                'route' => 'leads', 'cta' => 'View Leads',
                'impact' => 'high',
            ];
        }

        if (!$hasMeta) {
            $actions[] = [
                'priority' => 8, 'icon' => '📱', 'module' => 'Setup',
                'title' => 'Connect Meta (Facebook & Instagram)',
                'why' => 'Unlock social publishing and lead capture from Meta platforms.',
                'route' => 'social', 'cta' => 'Connect Meta',
                'impact' => 'medium',
            ];
        }

        if ($totalReviews > 0 && $avgRating < 4.0) {
            $actions[] = [
                'priority' => 9, 'icon' => '⬆', 'module' => 'Reviews',
                'title' => 'Improve average rating (currently ' . number_format($avgRating, 1) . '★)',
                'why' => 'Reply to all reviews and use AI drafts to address concerns professionally.',
                'route' => 'optimize', 'cta' => 'Optimize',
                'impact' => 'high',
            ];
        }

        if ($locationCount === 0) {
            $actions[] = [
                'priority' => 0, 'icon' => '📍', 'module' => 'Setup',
                'title' => 'Add your first business location',
                'why' => 'Locations are the foundation — add one to start tracking reviews and insights.',
                'route' => 'clients', 'cta' => 'Add Location',
                'impact' => 'critical',
            ];
        }

        usort($actions, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return array_slice($actions, 0, 6);
    }
}
