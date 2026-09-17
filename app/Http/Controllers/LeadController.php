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
        $leads = Lead::where('agency_id', $aid)->latest()->get()->groupBy('stage');
        $clients = Client::where('agency_id', $aid)->get();
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
        $data['agency_id'] = $request->user()->agency_id;
        Lead::create($data);
        return back()->with('success', 'Lead added.');
    }

    public function move(Request $request, Lead $lead)
    {
        abort_unless($lead->agency_id === $request->user()->agency_id, 403);
        $data = $request->validate(['stage' => 'required|in:NEW,CONTACTED,FOLLOW_UP,APPOINTMENT,CONVERTED,LOST']);
        $lead->update($data);
        return back();
    }
}
