<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PlacesService — checks a business's real position in Google local search
 * results for a keyword, using the Places API (Text Search). This is a
 * separate API key from the GBP OAuth flow (no user consent needed — it's a
 * plain server-side API key), so it works even for clients who haven't
 * connected Google yet.
 */
class PlacesService
{
    public function configured(): bool
    {
        return (bool) config('services.google.places_key');
    }

    /**
     * Search "$keyword near $location" and find where $businessName lands.
     * Returns null if not configured or the request fails; otherwise an array:
     *   ['rank' => int|null, 'results' => [['name','rating','address','ratings_total'], ...]]
     * $rank is null when the business isn't in the (top 20) results checked.
     */
    public function checkRank(string $keyword, string $location, string $businessName): ?array
    {
        $key = config('services.google.places_key');
        if (! $key) return null;

        $res = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
            'query' => "{$keyword} near {$location}",
            'key' => $key,
        ]);

        if (! $res->successful() || $res->json('status') !== 'OK') {
            Log::warning('Places textsearch failed: '.$res->status().' '.$res->body());
            return null;
        }

        $results = collect($res->json('results', []))->take(20)->values();

        $needle = strtolower(trim($businessName));
        $rank = null;
        foreach ($results as $i => $r) {
            if (str_contains(strtolower($r['name'] ?? ''), $needle) || str_contains($needle, strtolower($r['name'] ?? ''))) {
                $rank = $i + 1;
                break;
            }
        }

        return [
            'rank' => $rank,
            'checked' => $results->count(),
            'results' => $results->map(fn ($r) => [
                'name' => $r['name'] ?? 'Unknown',
                'rating' => $r['rating'] ?? null,
                'ratings_total' => $r['user_ratings_total'] ?? null,
                'address' => $r['formatted_address'] ?? '',
            ])->all(),
        ];
    }
}
