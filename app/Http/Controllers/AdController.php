<?php

namespace App\Http\Controllers;

use App\Models\AdReport;
use App\Models\Client;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $aid = $request->user()->agency_id;
        $clientId = $request->user()->client_id;
        $reports = AdReport::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->orderByDesc('period_end')->get();
        $clients = Client::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))->get();

        $insight = null;
        if ($reports->count() >= 2) {
            $sorted = $reports->sortBy(fn ($r) => $r->spend / max($r->conversions, 1))->values();
            $best = $sorted->first();
            $worst = $sorted->last();
            $insight = "\"{$best->campaign}\" has the lowest cost per conversion. Consider shifting budget from \"{$worst->campaign}\".";
        }

        $totals = [
            'spend' => $reports->sum('spend'),
            'clicks' => $reports->sum('clicks'),
            'conversions' => $reports->sum('conversions'),
        ];

        return view('dashboard.ads', compact('reports', 'clients', 'insight', 'totals'));
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
        AdReport::create($data);
        return back()->with('success', 'Report added.');
    }
}
