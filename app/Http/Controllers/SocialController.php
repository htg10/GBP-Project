<?php

namespace App\Http\Controllers;

use App\Models\SocialPost;
use App\Models\Client;
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
            ->with('client')->latest()->get();
        $clients = Client::where('agency_id', $aid)
            ->when($clientId, fn ($q) => $q->where('id', $clientId))->get();

        $liveCount = \App\Models\Integration::where('agency_id', $aid)
            ->where('provider', 'META_GRAPH')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))->count();

        return view('dashboard.social', compact('posts', 'clients', 'liveCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => 'required|in:FACEBOOK,INSTAGRAM,LINKEDIN,X',
            'body' => 'required|string',
            'media_url' => 'nullable|url',
            'scheduled_at' => 'nullable|date',
        ]);

        $agencyId = $request->user()->agency_id;
        if ($request->user()->client_id) {
            $data['client_id'] = $request->user()->client_id;
        }
        $client = Client::where('agency_id', $agencyId)->findOrFail($data['client_id']);

        $post = SocialPost::create([
            'agency_id' => $agencyId,
            'client_id' => $client->id,
            'platform' => $data['platform'],
            'body' => $data['body'],
            'media_urls' => $data['media_url'] ?? null ? [$data['media_url']] : null,
            'status' => $request->filled('scheduled_at') ? 'SCHEDULED' : 'DRAFT',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        if ($post->status === 'DRAFT') {
            $this->publish($client, $post);
        }

        $msg = match (true) {
            $post->status === 'SCHEDULED' => 'Post scheduled.',
            $post->fresh()->status === 'PUBLISHED' => 'Post published.',
            in_array($post->platform, ['LINKEDIN', 'X']) => 'Saved as draft — LinkedIn/X publishing is not wired up yet, post manually.',
            default => 'Saved, but publishing to Meta failed — check the connection and try again.',
        };

        return back()->with('success', $msg);
    }

    public function retry(Request $request, SocialPost $post)
    {
        abort_unless($post->agency_id === $request->user()->agency_id, 403);
        $client = Client::findOrFail($post->client_id);
        $this->publish($client, $post);

        return back()->with('success', $post->fresh()->status === 'PUBLISHED' ? 'Post published.' : 'Meta rejected the post — check the connection.');
    }

    public function caption(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);

        $agencyId = $request->user()->agency_id;
        if (!$this->creditService->canAfford($agencyId, 'ai_social_caption')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_social_caption'] . ' needed).'], 402);
        }

        $body = $this->ai->generateContent($request->input('prompt'));
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_social_caption');

        return response()->json(['body' => $body]);
    }

    private function publish(Client $client, SocialPost $post): void
    {
        if (! in_array($post->platform, ['FACEBOOK', 'INSTAGRAM'])) {
            return; // LinkedIn/X have no Graph API route — stays a draft.
        }

        $ok = $this->meta->publish($client, $post);

        $post->update([
            'status' => $ok ? 'PUBLISHED' : 'FAILED',
            'published_at' => $ok ? now() : null,
        ]);
    }
}
