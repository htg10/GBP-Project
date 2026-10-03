<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use App\Services\CreditService;
use Illuminate\Http\Request;

class AiModeController extends Controller
{
    public function __construct(private AiService $ai, private CreditService $creditService) {}

    public function send(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $agencyId = $request->user()->agency_id;
        if (! $this->creditService->canAfford($agencyId, 'ai_chat')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_chat'] . ' needed).'], 402);
        }

        $result = $this->ai->chat($data['message']);
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_chat');

        return response()->json($result);
    }
}
