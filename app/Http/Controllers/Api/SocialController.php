<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Integration;
use App\Models\SocialPost;
use App\Services\AiService;
use App\Services\CreditService;
use App\Services\MetaService;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function __construct(
        private AiService $ai,
        private CreditService $creditService,
        private MetaService $meta,
    ) {}

    public function index(Request $request)
    {
        $aid = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $posts = SocialPost::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->with('client')
            ->latest()
            ->get();

        $clients = Client::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))
            ->get(['id', 'name']);

        $liveCount = Integration::where('agency_id', $aid)
            ->where('provider', 'META_GRAPH')
            ->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->count();

        return response()->json([
            'posts' => $posts->map(fn ($p) => [
                'id' => $p->id,
                'platform' => $p->platform,
                'body' => $p->body,
                'media_urls' => $p->media_urls,
                'status' => $p->status,
                'error' => $p->error,
                'external_id' => $p->external_id,
                'scheduled_at' => $p->scheduled_at,
                'client_name' => $p->client->name ?? null,
                'client_id' => $p->client_id,
                'created_at' => $p->created_at,
            ]),
            'clients' => $clients,
            'meta_connected' => $liveCount > 0,
        ]);
    }

    public function store(Request $request)
    {
        $aid = $request->user()->agency_id;

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => 'required|in:FACEBOOK,INSTAGRAM,LINKEDIN,X',
            'body' => 'required|string',
            'media_url' => 'nullable|url',
            'media_data' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        if ($request->user()->client_id) {
            $data['client_id'] = $request->user()->client_id;
        }

        $client = Client::where('agency_id', $aid)->findOrFail($data['client_id']);

        $mediaUrls = [];
        if (! empty($data['media_data'])) {
            $mediaUrls[] = $data['media_data'];
        } elseif (! empty($data['media_url'])) {
            $mediaUrls[] = $data['media_url'];
        }

        $post = SocialPost::create([
            'agency_id' => $aid,
            'client_id' => $client->id,
            'platform' => $data['platform'],
            'body' => $data['body'],
            'media_urls' => $mediaUrls,
            'status' => ! empty($data['scheduled_at']) ? 'SCHEDULED' : 'DRAFT',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        if ($post->status === 'DRAFT') {
            $this->publish($client, $post);
        }

        return response()->json([
            'id' => $post->id,
            'status' => $post->status,
            'error' => $post->error,
        ], 201);
    }

    public function retry(Request $request, SocialPost $post)
    {
        abort_unless($post->agency_id === $request->user()->agency_id, 403);

        $client = Client::findOrFail($post->client_id);
        $this->publish($client, $post);

        return response()->json([
            'success' => $post->status === 'PUBLISHED',
            'status' => $post->status,
            'error' => $post->error,
        ]);
    }

    public function caption(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        if (! $this->creditService->canAfford($agencyId, 'ai_social_caption')) {
            return response()->json(['error' => 'Insufficient credits.'], 402);
        }

        $data = $request->validate(['prompt' => 'required|string']);

        $body = $this->ai->generateContent($data['prompt']);
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_social_caption');

        return response()->json(['body' => $body]);
    }

    private function publish(Client $client, SocialPost $post): void
    {
        try {
            $result = $this->meta->publish($client, $post);
            $post->update([
                'status' => 'PUBLISHED',
                'external_id' => $result['id'] ?? null,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            $post->update([
                'status' => 'FAILED',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
