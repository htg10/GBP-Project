<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GbpLocation;
use App\Models\GbpPhoto;
use App\Models\GbpPost;
use App\Services\AiService;
use App\Services\CreditService;
use App\Services\GbpService;
use Illuminate\Http\Request;

class GbpContentController extends Controller
{
    public function __construct(
        private GbpService $gbp,
        private AiService $ai,
        private CreditService $creditService,
    ) {}

    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $scope = function ($q) use ($agencyId, $clientId) {
            $q->where('agency_id', $agencyId);
            if ($clientId) $q->where('id', $clientId);
        };

        $posts = GbpPost::whereHas('location.client', $scope)
            ->with('location.client')->latest()->get();

        $photos = GbpPhoto::whereHas('location.client', $scope)
            ->with('location.client')->latest()->get();

        return response()->json([
            'posts' => $posts->map(fn ($p) => [
                'id' => $p->id,
                'type' => $p->type,
                'body' => $p->body,
                'status' => $p->status,
                'cta_url' => $p->cta_url,
                'location_title' => $p->location->title,
                'client_name' => $p->location->client->name,
                'image_url' => $p->image ? route('media.gbp-post-image', $p) : null,
                'published_at' => $p->published_at,
                'created_at' => $p->created_at,
            ]),
            'photos' => $photos->map(fn ($ph) => [
                'id' => $ph->id,
                'caption' => $ph->caption,
                'status' => $ph->status,
                'location_title' => $ph->location->title,
                'client_name' => $ph->location->client->name,
                'image_url' => route('media.gbp-photo', $ph),
                'created_at' => $ph->created_at,
            ]),
        ]);
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
            'image' => 'nullable|string',
        ]);

        $location = $this->locationFor($agencyId, $data['gbp_location_id']);

        $post = GbpPost::create([
            'gbp_location_id' => $location->id,
            'type' => $data['type'],
            'body' => $data['body'],
            'image' => $data['image'] ?? null,
            'cta_url' => $data['cta_url'] ?? null,
            'status' => isset($data['scheduled_at']) ? 'SCHEDULED' : 'DRAFT',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        if ($post->status === 'DRAFT') {
            $this->doPublishPost($post);
        }

        return response()->json([
            'id' => $post->id,
            'status' => $post->status,
        ], 201);
    }

    public function updatePost(Request $request, GbpPost $post)
    {
        $this->authorizePost($request, $post);

        $data = $request->validate([
            'type' => 'required|in:OFFER,EVENT,UPDATE',
            'body' => 'required|string|max:1500',
            'cta_url' => 'nullable|url',
        ]);

        $post->update($data);

        return response()->json(['success' => true, 'post' => $post->fresh()]);
    }

    public function destroyPost(Request $request, GbpPost $post)
    {
        $this->authorizePost($request, $post);
        $post->delete();

        return response()->json(['success' => true]);
    }

    public function publishPost(Request $request, GbpPost $post)
    {
        $this->authorizePost($request, $post);
        $this->doPublishPost($post);

        return response()->json([
            'success' => $post->status === 'PUBLISHED',
            'status' => $post->status,
        ]);
    }

    public function storePhoto(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        $data = $request->validate([
            'gbp_location_id' => 'required|exists:gbp_locations,id',
            'image' => 'required|string',
            'caption' => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date',
        ]);

        $location = $this->locationFor($agencyId, $data['gbp_location_id']);

        $photo = GbpPhoto::create([
            'gbp_location_id' => $location->id,
            'image' => $data['image'],
            'caption' => $data['caption'] ?? null,
            'status' => isset($data['scheduled_at']) ? 'SCHEDULED' : 'DRAFT',
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        if ($photo->status === 'DRAFT') {
            $this->doPublishPhoto($photo);
        }

        return response()->json([
            'id' => $photo->id,
            'status' => $photo->status,
        ], 201);
    }

    public function destroyPhoto(Request $request, GbpPhoto $photo)
    {
        abort_unless($photo->location->client->agency_id === $request->user()->agency_id, 403);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    public function publishPhoto(Request $request, GbpPhoto $photo)
    {
        abort_unless($photo->location->client->agency_id === $request->user()->agency_id, 403);
        $this->doPublishPhoto($photo);

        return response()->json([
            'success' => $photo->status === 'PUBLISHED',
            'status' => $photo->status,
        ]);
    }

    public function generatePostCopy(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        if (! $this->creditService->canAfford($agencyId, 'ai_post_caption')) {
            return response()->json(['error' => 'Insufficient credits.'], 402);
        }

        $data = $request->validate([
            'type' => 'required|in:OFFER,EVENT,UPDATE',
            'business' => 'required|string',
        ]);

        $typeDesc = match ($data['type']) {
            'OFFER' => 'a special offer / promotion',
            'EVENT' => 'an upcoming event',
            default => 'a general business update',
        };

        $body = $this->ai->generateContent(
            "Write a Google Business Profile post ({$typeDesc}) for \"{$data['business']}\". Keep under 300 characters."
        );

        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_post_caption', $data['business']);

        return response()->json(['body' => $body]);
    }

    public function generateCaption(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        if (! $this->creditService->canAfford($agencyId, 'ai_photo_caption')) {
            return response()->json(['error' => 'Insufficient credits.'], 402);
        }

        $data = $request->validate([
            'business' => 'required|string',
            'context' => 'nullable|string',
        ]);

        $caption = $this->ai->generateContent(
            "Write a short photo caption for \"{$data['business']}\""
            . (! empty($data['context']) ? " — context: {$data['context']}" : '')
            . '. Keep it under 150 characters.'
        );

        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_photo_caption', $data['business']);

        return response()->json(['caption' => $caption]);
    }

    private function locationFor(int $agencyId, int $locationId): GbpLocation
    {
        return GbpLocation::whereHas('client', fn ($q) => $q->where('agency_id', $agencyId))
            ->findOrFail($locationId);
    }

    private function authorizePost(Request $request, GbpPost $post): void
    {
        abort_unless($post->location->client->agency_id === $request->user()->agency_id, 403);
    }

    private function doPublishPost(GbpPost $post): void
    {
        $location = $post->location;
        $client = $location->client;

        $topicType = match ($post->type) {
            'OFFER' => 'OFFER',
            'EVENT' => 'EVENT',
            'ALERT' => 'ALERT',
            default => 'STANDARD',
        };

        $payload = [
            'languageCode' => 'en',
            'summary' => $post->body,
            'topicType' => $topicType,
        ];

        if ($post->cta_url) {
            $payload['callToAction'] = ['actionType' => 'LEARN_MORE', 'url' => $post->cta_url];
        }

        if ($post->image) {
            $sourceUrl = str_starts_with($post->image, 'data:')
                ? route('media.gbp-post-image', $post)
                : $post->image;
            $payload['media'] = [['mediaFormat' => 'PHOTO', 'sourceUrl' => $sourceUrl]];
        }

        $ok = $this->gbp->createPost($client, $location->google_name, $payload);
        $post->update([
            'status' => $ok ? 'PUBLISHED' : 'FAILED',
            'published_at' => $ok ? now() : null,
        ]);
    }

    private function doPublishPhoto(GbpPhoto $photo): void
    {
        $location = $photo->location;
        $client = $location->client;

        $sourceUrl = str_starts_with($photo->image, 'data:')
            ? route('media.gbp-photo', $photo)
            : $photo->image;

        $ok = $this->gbp->uploadPhoto($client, $location->google_name, $sourceUrl, $photo->caption);
        $photo->update(['status' => $ok ? 'PUBLISHED' : 'FAILED']);
    }
}
