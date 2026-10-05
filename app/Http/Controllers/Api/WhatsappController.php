<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    /**
     * Sends a WhatsApp message through the Cloud API. The template name must be an
     * approved template in the Meta Business account. Use template "text" to send a
     * plain message (only allowed inside the 24-hour customer service window).
     */
    public function send(Request $request)
    {
        $data = $request->validate([
            'to_phone' => 'required|string',
            'template' => 'required|string',
            'language' => 'nullable|string|max:10',
            'body' => 'nullable|string',
        ]);

        $phoneId = config('services.meta.whatsapp_phone_id');
        $token = config('services.meta.whatsapp_token');
        $to = preg_replace('/\D+/', '', $data['to_phone']);

        $msg = WhatsappMessage::create([
            'agency_id' => $request->user()->agency_id,
            'to_phone' => $data['to_phone'],
            'direction' => 'OUTBOUND',
            'template' => $data['template'],
            'body' => $data['body'] ?? null,
            'status' => 'queued',
        ]);

        if (! $phoneId || ! $token) {
            return response()->json([
                'status' => 'queued',
                'message' => 'WhatsApp is not connected on the server yet. Message saved as queued.',
            ], 202);
        }

        $payload = $data['template'] === 'text' && ! empty($data['body'])
            ? ['type' => 'text', 'text' => ['body' => $data['body']]]
            : [
                'type' => 'template',
                'template' => [
                    'name' => $data['template'],
                    'language' => ['code' => $data['language'] ?? 'en_US'],
                ],
            ];

        try {
            $res = Http::withToken($token)
                ->timeout(20)
                ->post("https://graph.facebook.com/v21.0/{$phoneId}/messages", array_merge([
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                ], $payload));
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send exception: ' . $e->getMessage());
            $msg->update(['status' => 'failed']);

            return response()->json(['status' => 'failed', 'message' => 'Could not reach WhatsApp. Try again.'], 502);
        }

        if ($res->successful()) {
            $msg->update(['status' => 'sent']);

            return response()->json(['id' => $msg->id, 'status' => 'sent'], 201);
        }

        Log::warning('WhatsApp send failed: ' . $res->status() . ' ' . $res->body());
        $msg->update(['status' => 'failed']);

        return response()->json([
            'status' => 'failed',
            'message' => $res->json('error.message') ?? 'WhatsApp rejected the message.',
        ], 422);
    }
}
