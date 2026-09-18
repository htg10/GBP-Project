<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Client;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $aid = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $leads = Lead::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->with('client')->latest()->get()->groupBy('stage');

        $clients = Client::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))->get();

        $stages = ['NEW', 'CONTACTED', 'FOLLOW_UP', 'APPOINTMENT', 'CONVERTED', 'LOST'];
        return view('dashboard.leads', compact('leads', 'clients', 'stages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'source' => 'required|in:FACEBOOK,INSTAGRAM,WEBSITE,WHATSAPP,GOOGLE_FORM',
        ]);
        // Client-bound users can only add leads under their own client.
        if ($request->user()->client_id) {
            $data['client_id'] = $request->user()->client_id;
        }
        $data['agency_id'] = $request->user()->agency_id;
        Lead::create($data);
        return back()->with('success', 'Lead added.');
    }

    public function move(Request $request, Lead $lead)
    {
        abort_unless($lead->agency_id === $request->user()->agency_id, 403);
        if ($request->user()->client_id) {
            abort_unless($lead->client_id === $request->user()->client_id, 403);
        }
        $data = $request->validate(['stage' => 'required|in:NEW,CONTACTED,FOLLOW_UP,APPOINTMENT,CONVERTED,LOST']);
        $lead->update($data);
        return $request->wantsJson() ? response()->json(['ok' => true]) : back();
    }
}
