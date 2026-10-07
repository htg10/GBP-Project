<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GbpLocation;
use App\Services\AiService;
use App\Services\CreditService;
use App\Services\PlacesService;
use Illuminate\Http\Request;

class RankCheckerController extends Controller
{
    public function __construct(
        private AiService $ai,
        private CreditService $creditService,
        private PlacesService $places,
    ) {}

    public function locations(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $locations = GbpLocation::whereHas('client', fn ($q) => $q->where('agency_id', $agencyId))
            ->with('client:id,name')
            ->orderBy('title')
            ->get(['id', 'title', 'address', 'client_id']);

        return response()->json($locations);
    }

    public function check(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        if (! $this->creditService->canAfford($agencyId, 'ai_rank_check')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_rank_check'] . ' needed).'], 402);
        }

        $data = $request->validate([
            'location_id' => 'required|exists:gbp_locations,id',
            'keyword' => 'required|string|max:120',
            'radius_km' => 'nullable|integer|min:1|max:50',
        ]);

        $location = GbpLocation::whereHas('client', fn ($q) => $q->where('agency_id', $agencyId))
            ->with('client')->findOrFail($data['location_id']);

        $businessName = $location->title ?: $location->client->name;
        $where = $location->address ?: $location->client->name;
        $radius = $data['radius_km'] ?? 5;

        if ($this->places->configured()) {
            $real = $this->places->checkRank($data['keyword'], $where, $businessName);
            if ($real !== null) {
                $this->creditService->deduct($agencyId, $request->user()->id, 'ai_rank_check', "{$data['keyword']} — {$businessName}");
                return response()->json([
                    'source' => 'places',
                    'business' => $businessName,
                    'keyword' => $data['keyword'],
                    'radius_km' => $radius,
                    'rank' => $real['rank'],
                    'checked' => $real['checked'],
                    'results' => array_slice($real['results'], 0, 10),
                ]);
            }
        }

        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_rank_check', "{$data['keyword']} — {$businessName}");
        return response()->json($this->aiEstimate($businessName, $data['keyword'], $where, $radius));
    }

    private function aiEstimate(string $businessName, string $keyword, string $where, int $radius): array
    {
        $prompt = "You are a local SEO analyst. Estimate how a business likely ranks in Google's local search results.\n\n"
            . "Business: {$businessName}\n"
            . "Location: {$where}\n"
            . "Search keyword: {$keyword}\n"
            . "Search radius: {$radius}km\n\n"
            . "Return ONLY valid JSON (no markdown/backticks) in this shape:\n"
            . '{"estimated_rank":"e.g. Top 3 / 4-10 / Not in top 10","summary":"2-3 sentence realistic estimate and why","tips":["...","...","..."]}' . "\n"
            . 'tips = 4 concrete actions to improve local ranking for this keyword.';

        $raw = $this->ai->generateContent($prompt);
        $parsed = $this->ai->extractJson($raw);

        if (is_array($parsed) && isset($parsed['summary'])) {
            return [
                'source' => 'ai',
                'business' => $businessName,
                'keyword' => $keyword,
                'radius_km' => $radius,
                'estimated_rank' => $parsed['estimated_rank'] ?? 'Unknown',
                'summary' => $parsed['summary'],
                'tips' => $parsed['tips'] ?? [],
            ];
        }

        return [
            'source' => 'fallback',
            'business' => $businessName,
            'keyword' => $keyword,
            'radius_km' => $radius,
            'estimated_rank' => 'Unavailable',
            'summary' => 'Add GOOGLE_PLACES_API_KEY to your .env for a real ranking check, or set GEMINI_API_KEY for an AI estimate.',
            'tips' => ['Keep your Business Profile fully filled out', 'Collect and reply to reviews regularly', 'Post updates on Google weekly', 'Use the keyword in your business description'],
        ];
    }
}
