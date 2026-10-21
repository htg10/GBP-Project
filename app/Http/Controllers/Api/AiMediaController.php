<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiMedia;
use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\GbpPhoto;
use App\Services\AiService;
use App\Services\CreditService;
use Illuminate\Http\Request;

class AiMediaController extends Controller
{
    public function __construct(private AiService $ai, private CreditService $creditService) {}

    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $items = AiMedia::where('agency_id', $agencyId)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->latest()
            ->take(30)
            ->get();

        return response()->json($items->map(fn ($m) => [
            'id' => $m->id,
            'prompt' => $m->prompt,
            'image_data' => $m->image_data,
            'source' => $m->source,
            'created_at' => $m->created_at?->format('d M Y, h:i A'),
        ]));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'prompt' => 'required|string|max:4000',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        @set_time_limit(120);

        $agencyId = $request->user()->agency_id;
        if ($request->user()->client_id) {
            $data['client_id'] = $request->user()->client_id;
        } elseif ($data['client_id'] ?? null) {
            Client::where('agency_id', $agencyId)->findOrFail($data['client_id']);
        }

        if (! $this->creditService->canAfford($agencyId, 'ai_media_generate')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_media_generate'] . ' needed).'], 402);
        }

        $result = $this->ai->generateImage($data['prompt']);
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_media_generate', mb_substr($data['prompt'], 0, 80));

        $media = AiMedia::create([
            'agency_id' => $agencyId,
            'client_id' => $data['client_id'] ?? null,
            'created_by' => $request->user()->id,
            'type' => 'IMAGE',
            'prompt' => $data['prompt'],
            'image_data' => $result['data_uri'],
            'model' => config('services.gemini.image_model'),
            'source' => $result['source'],
        ]);

        return response()->json([
            'success' => $result['success'],
            'source' => $result['source'],
            'media' => [
                'id' => $media->id,
                'prompt' => $media->prompt,
                'image_data' => $media->image_data,
                'created_at' => $media->created_at->format('d M Y, h:i A'),
            ],
        ]);
    }

    public function destroy(Request $request, AiMedia $media)
    {
        abort_unless($media->agency_id === $request->user()->agency_id, 403);
        $media->delete();

        return response()->json(['success' => true]);
    }

    public function useAsPhoto(Request $request, AiMedia $media)
    {
        abort_unless($media->agency_id === $request->user()->agency_id, 403);

        $data = $request->validate(['gbp_location_id' => 'required|exists:gbp_locations,id']);
        $location = GbpLocation::whereHas('client', fn ($q) => $q->where('agency_id', $request->user()->agency_id))
            ->findOrFail($data['gbp_location_id']);

        $photo = GbpPhoto::create([
            'gbp_location_id' => $location->id,
            'image' => $media->image_data,
            'caption' => mb_substr($media->prompt, 0, 200),
            'status' => 'DRAFT',
        ]);

        return response()->json(['success' => true, 'photo_id' => $photo->id]);
    }
}
