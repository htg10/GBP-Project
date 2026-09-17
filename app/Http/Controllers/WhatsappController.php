<?php

namespace App\Http\Controllers;

use App\Models\WhatsappMessage;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function index(Request $request)
    {
        $messages = WhatsappMessage::where('agency_id', $request->user()->agency_id)->latest()->take(100)->get();
        return view('dashboard.whatsapp', compact('messages'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'to_phone' => 'required|string',
            'template' => 'required|string',
            'body' => 'nullable|string',
        ]);
        // TODO: real WhatsApp Cloud API call when connected.
        WhatsappMessage::create([
            'agency_id' => $request->user()->agency_id,
            'to_phone' => $data['to_phone'],
            'direction' => 'OUTBOUND',
            'template' => $data['template'],
            'body' => $data['body'] ?? null,
            'status' => 'queued',
        ]);
        return back()->with('success', 'Message queued.');
    }
}
