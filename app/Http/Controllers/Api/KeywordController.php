<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use App\Services\CreditService;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function __construct(private AiService $ai, private CreditService $creditService) {}

    public function generate(Request $request)
    {
        $data = $request->validate([
            'business' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
        ]);

        $agencyId = $request->user()->agency_id;
        if (! $this->creditService->canAfford($agencyId, 'ai_keyword_gen')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_keyword_gen'] . ' needed).'], 402);
        }

        $result = $this->ai->generateKeywords($data['business'], $data['city'], $data['industry'] ?? null);
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_keyword_gen', $data['business']);

        return response()->json(['groups' => $result['groups'], 'source' => $result['source']]);
    }
}
