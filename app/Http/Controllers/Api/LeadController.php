<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $aid = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $leads = Lead::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->with('client')
            ->latest()
            ->get();

        $clients = Client::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))
            ->get(['id', 'name']);

        $stages = ['NEW', 'CONTACTED', 'FOLLOW_UP', 'APPOINTMENT', 'CONVERTED', 'LOST'];

        return response()->json([
            'leads' => $leads->map(fn ($l) => [
                'id' => $l->id,
                'name' => $l->name,
                'email' => $l->email,
                'phone' => $l->phone,
                'source' => $l->source,
                'stage' => $l->stage,
                'notes' => $l->notes,
                'client_name' => $l->client->name ?? null,
                'client_id' => $l->client_id,
                'created_at' => $l->created_at,
            ]),
            'clients' => $clients,
            'stages' => $stages,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'source' => 'required|in:FACEBOOK,INSTAGRAM,WEBSITE,WHATSAPP,GOOGLE_FORM',
            'notes' => 'nullable|string',
        ]);

        if ($request->user()->client_id) {
            $data['client_id'] = $request->user()->client_id;
        }
        $data['agency_id'] = $request->user()->agency_id;

        $lead = Lead::create($data);

        return response()->json($lead, 201);
    }

    public function move(Request $request, Lead $lead)
    {
        abort_unless($lead->agency_id === $request->user()->agency_id, 403);
        $scoped = $request->user()->client_id;
        abort_if($scoped && $lead->client_id !== $scoped, 403);

        $data = $request->validate([
            'stage' => 'required|in:NEW,CONTACTED,FOLLOW_UP,APPOINTMENT,CONVERTED,LOST',
        ]);

        $lead->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, Lead $lead)
    {
        abort_unless($lead->agency_id === $request->user()->agency_id, 403);
        $scoped = $request->user()->client_id;
        abort_if($scoped && $lead->client_id !== $scoped, 403);

        $lead->delete();

        return response()->json(['success' => true]);
    }
}
