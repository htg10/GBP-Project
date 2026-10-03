<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function index(Request $request)
    {
        $messages = WhatsappMessage::where('agency_id', $request->user()->agency_id)
            ->latest()->take(100)->get();

        return response()->json($messages->map(fn ($m) => [
            'id' => $m->id,
            'to_phone' => $m->to_phone,
            'body' => $m->body,
            'template' => $m->template,
            'direction' => $m->direction,
            'status' => $m->status,
            'created_at' => $m->created_at,
        ]));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'to_phone' => 'required|string',
            'template' => 'required|string',
            'body' => 'nullable|string',
        ]);

        $msg = WhatsappMessage::create([
            'agency_id' => $request->user()->agency_id,
            'to_phone' => $data['to_phone'],
            'direction' => 'OUTBOUND',
            'template' => $data['template'],
            'body' => $data['body'] ?? null,
            'status' => 'queued',
        ]);

        return response()->json($msg, 201);
    }
}
