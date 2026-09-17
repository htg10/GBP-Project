<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Integration;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MetaService — publishes SocialPost records to the real Meta Graph API
 * (Facebook Page feed, Instagram Business Account) using the client's stored
 * Page access token. Same "single entry point" shape as GbpService: if the
 * client isn't connected yet, it pretends success so the app keeps working
 * on demo data.
 *
 * Only FACEBOOK and INSTAGRAM are backed by a real API here — LinkedIn and X
 * publishing are out of scope for the Meta Graph API and stay manual/draft.
 */
class MetaService
{
    private function graph(): string
    {
        return 'https://graph.facebook.com/'.config('services.meta.graph_version');
    }

    private function integrationFor(Client $client): ?Integration
    {
        return Integration::where('client_id', $client->id)
            ->where('provider', 'META_GRAPH')
            ->whereNotNull('access_token')
            ->first();
    }

    public function isLive(Client $client): bool
    {
        return (bool) $this->integrationFor($client);
    }

    /** Returns true/false on real publish attempts; true (no-op) when not connected yet. */
    public function publish(Client $client, SocialPost $post): bool
    {
        $integration = $this->integrationFor($client);
        if (! $integration) {
            return true; // mock: pretend success so drafts still "work" pre-connection
        }

        $pageToken = $integration->access_token;
        $imageUrl = $post->media_urls[0] ?? null;

        return match ($post->platform) {
            'FACEBOOK' => $this->publishToFacebook($integration, $pageToken, $post, $imageUrl),
            'INSTAGRAM' => $this->publishToInstagram($integration, $pageToken, $post, $imageUrl),
            default => false, // LinkedIn / X — no Graph API support, stays manual
        };
    }

    private function publishToFacebook(Integration $integration, string $pageToken, SocialPost $post, ?string $imageUrl): bool
    {
        $pageId = $integration->meta['page_id'] ?? null;
        if (! $pageId) return false;

        // With an image -> /photos (image + caption). Text-only -> /feed.
        $endpoint = $imageUrl ? "{$pageId}/photos" : "{$pageId}/feed";
        $payload = $imageUrl
            ? ['url' => $imageUrl, 'caption' => $post->body, 'access_token' => $pageToken]
            : ['message' => $post->body, 'access_token' => $pageToken];

        $res = Http::asForm()->post($this->graph()."/{$endpoint}", $payload);

        if (! $res->successful()) {
            Log::warning('Meta Facebook publish failed: '.$res->body());
            return false;
        }
        return true;
    }

    private function publishToInstagram(Integration $integration, string $pageToken, SocialPost $post, ?string $imageUrl): bool
    {
        $igUserId = $integration->meta['ig_user_id'] ?? null;
        if (! $igUserId) {
            Log::warning('Meta Instagram publish failed: no linked Instagram Business Account for this Page.');
            return false;
        }
        if (! $imageUrl) {
            Log::warning('Meta Instagram publish failed: Instagram requires an image (media_urls[0]).');
            return false;
        }

        // Step 1: create a media container.
        $create = Http::asForm()->post($this->graph()."/{$igUserId}/media", [
            'image_url' => $imageUrl,
            'caption' => $post->body,
            'access_token' => $pageToken,
        ]);
        if (! $create->successful()) {
            Log::warning('Meta Instagram container create failed: '.$create->body());
            return false;
        }
        $creationId = $create->json('id');

        // Step 2: publish the container.
        $publish = Http::asForm()->post($this->graph()."/{$igUserId}/media_publish", [
            'creation_id' => $creationId,
            'access_token' => $pageToken,
        ]);
        if (! $publish->successful()) {
            Log::warning('Meta Instagram publish failed: '.$publish->body());
            return false;
        }
        return true;
    }
}
