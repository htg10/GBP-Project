<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Review;
use App\Services\AiService;
use App\Services\CreditService;
use Illuminate\Http\Request;

class CompetitorController extends Controller
{
    public function __construct(private AiService $ai, private CreditService $creditService) {}

    public function run(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        if (! $this->creditService->canAfford($agencyId, 'ai_competitor')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_competitor'] . ' needed).'], 402);
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'competitors' => 'required|string|max:500',
            'city' => 'nullable|string|max:255',
        ]);

        $client = Client::where('agency_id', $agencyId)
            ->where('id', $data['client_id'])
            ->with('locations')
            ->firstOrFail();

        $locationIds = $client->locations->pluck('id');
        $reviews = Review::whereIn('gbp_location_id', $locationIds)->get();
        $total = $reviews->count();
        $avg = $total ? round($reviews->avg('star_rating'), 1) : 0;

        $competitors = collect(explode(',', $data['competitors']))
            ->map(fn ($c) => trim($c))
            ->filter()
            ->take(5)
            ->values()
            ->all();

        $city = $data['city'] ?: '';
        $compList = implode(', ', $competitors);

        $prompt = "You are a local marketing strategist. Compare this business against its competitors and give a practical competitive analysis.\n\n"
            . "My business: {$client->name}" . ($client->industry ? " ({$client->industry})" : '') . "\n"
            . "My Google stats: {$avg}/5 average from {$total} reviews.\n"
            . "City: {$city}\n"
            . "Competitors: {$compList}\n\n"
            . "Return ONLY valid JSON (no markdown/backticks) in this shape:\n"
            . '{"summary":"2-3 sentence overview","strengths":["..."],"gaps":["..."],"actions":["..."]}' . "\n"
            . "strengths = where my business likely leads; gaps = where competitors likely beat me; "
            . "actions = 4 concrete steps to win locally. Keep each point one short line.";

        $result = ['summary' => '', 'strengths' => [], 'gaps' => [], 'actions' => [], 'source' => 'fallback'];

        $raw = $this->ai->generateContent($prompt);
        $parsed = $this->ai->extractJson($raw);
        if (is_array($parsed) && isset($parsed['summary'])) {
            $result = array_merge($result, $parsed, ['source' => 'ai']);
        } else {
            $result['summary'] = "Comparing {$client->name} against {$compList}. Add your GEMINI_API_KEY or wait for the rate limit to reset for a full AI analysis.";
            $result['strengths'] = ["Your {$avg}/5 rating from {$total} reviews is your main asset"];
            $result['gaps'] = ['Competitor data needs Google API access (pending) for exact numbers'];
            $result['actions'] = ['Ask happy customers for reviews', 'Reply to every review', 'Post weekly updates on Google', 'Keep business info accurate'];
        }

        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_competitor', $client->name);

        $result['competitors'] = $competitors;
        $result['my_stats'] = ['avg' => $avg, 'total' => $total, 'name' => $client->name];

        return response()->json($result);
    }
}
