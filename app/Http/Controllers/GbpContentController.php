<?php

namespace App\Http\Controllers;

use App\Models\GbpLocation;
use App\Models\GbpPhoto;
use App\Models\GbpPost;
use App\Services\AiService;
use App\Services\CreditService;
use App\Services\GbpService;
use Illuminate\Http\Request;

/**
 * GbpContentController — Google Posts (offers/events/updates) & Photos.
 *
 * Mirrors the GBP reviews flow: content is saved locally first, then pushed
 * to the real Google Business Profile API via GbpService (which falls back
 * to a no-op "pretend success" when the client isn't Google-connected yet).
 */
class GbpContentController extends Controller
{
    public function __construct(
        private AiService $ai,
        private CreditService $creditService,
        private GbpService $gbp,
    ) {}

    /**
     * Serves a GbpPhoto's raw image bytes at a plain public URL — no auth,
     * because Google's servers (not the browser) fetch this when publishing
     * a photo via sourceUrl. Only decodes/streams the stored data: URI; it
     * doesn't expose anything not already meant to go public on the profile.
     */
    public function rawPhoto(GbpPhoto $photo)
    {
        if (! str_starts_with($photo->image, 'data:')) {
            return redirect($photo->image);
        }

        [$meta, $base64] = explode(',', $photo->image, 2) + [null, null];
        preg_match('/^data:(.*?);base64$/', $meta ?? '', $m);
        $mime = $m[1] ?? 'image/png';

        return response(base64_decode($base64 ?? ''), 200)->header('Content-Type', $mime);
    }

    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        $locations = GbpLocation::whereHas('client', fn ($q) => $q->where('agency_id', $agencyId))
            ->with('client')->get();

        $posts = GbpPost::whereHas('location.client', fn ($q) => $q->where('agency_id', $agencyId))
            ->with('location.client')->latest()->get();

        $photos = GbpPhoto::whereHas('location.client', fn ($q) => $q->where('agency_id', $agencyId))
            ->with('location.client')->latest()->get();

        return view('dashboard.gbp-content', compact('locations', 'posts', 'photos'));
    }

    public function generatePostCopy(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        if (!$this->creditService->canAfford($agencyId, 'ai_post_caption')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_post_caption'] . ' needed).'], 402);
        }

        $data = $request->validate([
            'type' => 'required|in:OFFER,EVENT,UPDATE',
            'business' => 'required|string',
        ]);

        $kind = match ($data['type']) {
            'OFFER' => 'a limited-time offer / discount promotion',
            'EVENT' => 'an upcoming event announcement',
            default => 'a general business update',
        };

        $body = $this->ai->generateContent(
            "Write a short Google Business Profile post ({$kind}) for {$data['business']}. Keep it under 300 characters, friendly and action-oriented."
        );
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_post_caption', $data['business']);

        return response()->json(['body' => $body]);
    }

    public function storePost(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        $data = $request->validate([
            'gbp_location_id' => 'required|exists:gbp_locations,id',
            'type' => 'required|in:OFFER,EVENT,UPDATE',
            'body' => 'required|string|max:1500',
            'cta_url' => 'nullable|url',
            'scheduled_at' => 'nullable|date',
        ]);

        $location = $this->locationFor($agencyId, $data['gbp_location_id']);

        $post = GbpPost::create([
            'gbp_location_id' => $location->id,
            'type' => $data['type'],
            'body' => $data['body'],
            'cta_url' => $data['cta_url'] ?? null,
            'status' => $request->filled('scheduled_at') ? 'SCHEDULED' : 'DRAFT',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        if ($post->status === 'DRAFT') {
            $this->publishPost($post);
        }

        return back()->with('success', $post->status === 'SCHEDULED' ? 'Post scheduled.' : 'Post published.');
    }

    public function publishExistingPost(Request $request, GbpPost $post)
    {
        $this->authorizePost($request, $post);
        $this->publishPost($post);
        return back()->with('success', $post->status === 'PUBLISHED' ? 'Post published.' : 'Google rejected the post — check the connection.');
    }

    public function publishExistingPhoto(Request $request, GbpPhoto $photo)
    {
        abort_unless($photo->location->client->agency_id === $request->user()->agency_id, 403);
        $this->publishPhoto($photo);
        return back()->with('success', $photo->status === 'PUBLISHED' ? 'Photo published.' : 'Google rejected the photo — check the connection.');
    }

    public function storePhoto(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        $data = $request->validate([
            'gbp_location_id' => 'required|exists:gbp_locations,id',
            'image' => 'required|url',
            'caption' => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date',
        ]);

        $location = $this->locationFor($agencyId, $data['gbp_location_id']);

        $photo = GbpPhoto::create([
            'gbp_location_id' => $location->id,
            'image' => $data['image'],
            'caption' => $data['caption'] ?? null,
            'status' => $request->filled('scheduled_at') ? 'SCHEDULED' : 'DRAFT',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        if ($photo->status === 'DRAFT') {
            $this->publishPhoto($photo);
        }

        return back()->with('success', $photo->status === 'SCHEDULED' ? 'Photo scheduled.' : 'Photo uploaded.');
    }

    public function generateCaption(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        if (!$this->creditService->canAfford($agencyId, 'ai_photo_caption')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_photo_caption'] . ' needed).'], 402);
        }

        $request->validate(['prompt' => 'required|string']);
        $body = $this->ai->generateContent(
            "Write a short, engaging caption (under 150 characters) for this Google Business Profile photo: {$request->input('prompt')}"
        );
        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_photo_caption');

        return response()->json(['caption' => $body]);
    }

    private function locationFor(int $agencyId, int $locationId): GbpLocation
    {
        return GbpLocation::whereHas('client', fn ($q) => $q->where('agency_id', $agencyId))
            ->with('client')->findOrFail($locationId);
    }

    private function publishPost(GbpPost $post): void
    {
        $location = $post->location;
        $client = $location->client;

        $payload = [
            'languageCode' => 'en-US',
            'summary' => $post->body,
            'topicType' => $post->type,
        ];
        if ($post->cta_url) {
            $payload['callToAction'] = ['actionType' => 'LEARN_MORE', 'url' => $post->cta_url];
        }

        $ok = $this->gbp->createPost($client, $location->google_name, $payload);

        $post->update([
            'status' => $ok ? 'PUBLISHED' : 'FAILED',
            'published_at' => $ok ? now() : null,
        ]);
    }

    private function publishPhoto(GbpPhoto $photo): void
    {
        $location = $photo->location;
        $client = $location->client;

        // Google fetches sourceUrl itself — a data: URI isn't fetchable, so
        // route those through our own public raw-image endpoint instead.
        // (That endpoint is only reachable by Google if APP_URL is a real
        // public domain; on localhost this will still fail, same as any
        // other webhook-style callback would.)
        $sourceUrl = str_starts_with($photo->image, 'data:')
            ? route('media.gbp-photo', $photo)
            : $photo->image;

        $ok = $this->gbp->uploadPhoto($client, $location->google_name, $sourceUrl, $photo->caption);

        $photo->update(['status' => $ok ? 'PUBLISHED' : 'FAILED']);
    }

    private function authorizePost(Request $request, GbpPost $post): void
    {
        abort_unless($post->location->client->agency_id === $request->user()->agency_id, 403);
    }
}
