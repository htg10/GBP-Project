<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdReport;
use App\Models\Client;
use App\Models\Integration;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $aid = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $reports = AdReport::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->orderByDesc('period_end')
            ->get();

        $clients = Client::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))
            ->get(['id', 'name']);

        $hasGoogle = Integration::where('agency_id', $aid)
            ->where('provider', 'GOOGLE_GBP')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->exists();

        $hasMeta = Integration::where('agency_id', $aid)
            ->where('provider', 'META_GRAPH')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->exists();

        $totals = [
            'spend' => $reports->sum('spend'),
            'clicks' => $reports->sum('clicks'),
            'conversions' => $reports->sum('conversions'),
            'leads' => $reports->sum('leads'),
        ];

        return response()->json([
            'reports' => $reports,
            'clients' => $clients,
            'totals' => $totals,
            'has_google' => $hasGoogle,
            'has_meta' => $hasMeta,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'network' => 'required|in:GOOGLE,META',
            'campaign' => 'required|string',
            'spend' => 'nullable|numeric',
            'clicks' => 'nullable|integer',
            'impressions' => 'nullable|integer',
            'conversions' => 'nullable|integer',
            'leads' => 'nullable|integer',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
        ]);

        if ($request->user()->client_id) {
            $data['client_id'] = $request->user()->client_id;
        }
        $data['agency_id'] = $request->user()->agency_id;

        $report = AdReport::create($data);

        return response()->json($report, 201);
    }
}
