<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GoogleGbpProvider — talks to the REAL Google Business Profile APIs using a
 * client's stored OAuth token. Handles automatic access-token refresh.
 *
 * Google splits this across a few API hosts:
 *   - Account Management API : mybusinessaccountmanagement.googleapis.com
 *   - Business Information API: mybusinessbusinessinformation.googleapis.com
 *   - Reviews (legacy v4)     : mybusiness.googleapis.com/v4  (reviews + replies)
 *   - Media / Local Posts (v4): mybusiness.googleapis.com/v4
 *
 * Note: Reviews & posts still live on the v4 endpoint; Google has not migrated
 * them to a v1 host yet. Access requires approved Business Profile API scope.
 */
class GoogleGbpProvider
{
    /**
     * Human-readable reason the last call returned nothing, so the UI can show
     * something more useful than a generic guess. Reset at the start of every
     * public call that can fail.
     */
    private ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /** Return a valid access token for this client, refreshing if expired. */
    private function token(Integration $integration): ?string
    {
        if ($integration->expires_at && $integration->expires_at->isFuture()) {
            return $integration->access_token;
        }

        // Token expired (or no expiry stored) -> refresh using refresh_token.
        if (! $integration->refresh_token) {
            return $integration->access_token; // best effort
        }

        $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $integration->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $res->successful()) {
            Log::warning('Google token refresh failed: '.$res->body());

            // "invalid_grant" means the refresh token itself is dead (revoked
            // by the user, or — very commonly — the OAuth consent screen is
            // still in "Testing" mode, where Google expires test-user tokens
            // after ~7 days). No amount of retrying fixes this; the client
            // has to reconnect. Clear the stored tokens so the rest of the
            // app (the "Connected" badge on the client page, isLive() checks)
            // stops claiming a live connection that no longer exists.
            if ($res->json('error') === 'invalid_grant') {
                $integration->update(['access_token' => null, 'refresh_token' => null, 'expires_at' => null]);
                $this->lastError = 'Your Google connection has expired or was revoked. Reconnect Google for this client to sync again.';
            } else {
                $this->lastError = 'Could not refresh the Google connection: '.($res->json('error_description') ?? $res->body());
            }

            return null;
        }

        $data = $res->json();
        $integration->update([
            'access_token' => $data['access_token'] ?? $integration->access_token,
            'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null,
        ]);

        return $integration->access_token;
    }

    private function client(Integration $integration)
    {
        // A hard timeout matters here: without one, a slow/unresponsive Google
        // endpoint can hang a request past PHP's max_execution_time, which is
        // what made "Sync from Google" spin forever with nothing to show for it.
        return Http::withToken($this->token($integration))->acceptJson()->timeout(15);
    }

    /** Pull a plain-English reason out of a failed Google API response. */
    private function describeFailure(\Illuminate\Http\Client\Response $res): string
    {
        $message = $res->json('error.message');
        if (! $message) return $res->status().' '.$res->body();

        return match ($res->json('error.status')) {
            'PERMISSION_DENIED' => "Google denied access ({$message}). Business Profile API access is likely still pending Google's approval for this project.",
            'UNAUTHENTICATED' => 'Your Google connection is no longer valid. Reconnect Google for this client.',
            default => $message,
        };
    }

    /**
     * List the Business Profile accounts this Google user can manage.
     * Returns array of ['name' => 'accounts/123', 'accountName' => '...'].
     */
    public function listAccounts(Integration $integration): array
    {
        $this->lastError = null;
        $url = 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts';
        $res = $this->client($integration)->get($url);

        if (! $res->successful()) {
            Log::warning('GBP listAccounts failed: '.$res->status().' '.$res->body());
            $this->lastError ??= $this->describeFailure($res);
            return [];
        }
        return $res->json('accounts', []);
    }

    /**
     * List the locations under a given account.
     * $accountName e.g. "accounts/123". Returns Google's location objects.
     */
    public function listLocations(Integration $integration, string $accountName): array
    {
        $this->lastError = null;
        // readMask is required by the Business Information API.
        $url = "https://mybusinessbusinessinformation.googleapis.com/v1/{$accountName}/locations";
        $res = $this->client($integration)->get($url, [
            'readMask' => 'name,title,storefrontAddress',
            'pageSize' => 100,
        ]);

        if (! $res->successful()) {
            Log::warning('GBP listLocations failed: '.$res->status().' '.$res->body());
            $this->lastError ??= $this->describeFailure($res);
            return [];
        }

        $out = [];
        foreach ($res->json('locations', []) as $loc) {
            // Build a readable address from storefrontAddress if present.
            $addr = '';
            if (isset($loc['storefrontAddress'])) {
                $a = $loc['storefrontAddress'];
                $parts = array_merge($a['addressLines'] ?? [], [$a['locality'] ?? '', $a['administrativeArea'] ?? '']);
                $addr = trim(implode(', ', array_filter($parts)), ', ');
            }
            $locName = $loc['name'] ?? '';
            // Business Information API returns "locations/Y". Reviews API needs
            // "accounts/X/locations/Y". Prefix the account only if not already present.
            $fullName = str_starts_with($locName, 'accounts/')
                ? $locName
                : $accountName.'/'.$locName;
            $out[] = [
                'google_name' => $fullName,
                'title' => $loc['title'] ?? 'Untitled location',
                'address' => $addr,
            ];
        }
        return $out;
    }

    /**
     * List reviews for a location.
     * $locationName is Google's resource name, e.g. "accounts/123/locations/456".
     * Returns a normalized array matching the shape our ReviewController expects.
     */
    public function listReviews(Integration $integration, string $locationName): array
    {
        $this->lastError = null;
        $url = "https://mybusiness.googleapis.com/v4/{$locationName}/reviews";

        try {
            $res = $this->client($integration)->get($url);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Timed out / unreachable — log and move on instead of letting one
            // slow location kill a sync that covers many locations.
            Log::warning("GBP listReviews timed out for {$locationName}: ".$e->getMessage());
            $this->lastError ??= 'Google took too long to respond. Try again in a moment.';
            return [];
        }

        if (! $res->successful()) {
            Log::warning('GBP listReviews failed: '.$res->status().' '.$res->body());
            $this->lastError ??= $this->describeFailure($res);
            return [];
        }

        $out = [];
        foreach ($res->json('reviews', []) as $r) {
            $starMap = ['ONE' => 1, 'TWO' => 2, 'THREE' => 3, 'FOUR' => 4, 'FIVE' => 5];
            $out[] = [
                'reviewId' => $r['reviewId'] ?? ($r['name'] ?? uniqid()),
                'name' => $r['reviewer']['displayName'] ?? 'Anonymous',
                'rating' => $starMap[$r['starRating'] ?? 'THREE'] ?? 3,
                'comment' => $r['comment'] ?? '',
                'time' => isset($r['createTime']) ? date('Y-m-d H:i:s', strtotime($r['createTime'])) : now()->toDateTimeString(),
            ];
        }
        return $out;
    }

    /** Post (or update) a reply to a review. */
    public function putReply(Integration $integration, string $locationName, string $reviewId, string $comment): bool
    {
        $url = "https://mybusiness.googleapis.com/v4/{$locationName}/reviews/{$reviewId}/reply";

        try {
            $res = $this->client($integration)->put($url, ['comment' => $comment]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('GBP putReply timed out: '.$e->getMessage());
            return false;
        }

        if (! $res->successful()) {
            Log::warning('GBP putReply failed: '.$res->body());
            return false;
        }
        return true;
    }

    /** Create a local post (offer / event / update). */
    public function createPost(Integration $integration, string $locationName, array $payload): bool
    {
        $url = "https://mybusiness.googleapis.com/v4/{$locationName}/localPosts";

        try {
            $res = $this->client($integration)->post($url, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('GBP createPost timed out: '.$e->getMessage());
            return false;
        }

        if (! $res->successful()) {
            Log::warning('GBP createPost failed: '.$res->body());
            return false;
        }
        return true;
    }

    /** Upload a media item (photo) to a location. $sourceUrl must be a real, publicly fetchable URL — Google fetches it server-side. */
    public function uploadPhoto(Integration $integration, string $locationName, string $sourceUrl, ?string $description = null): bool
    {
        $url = "https://mybusiness.googleapis.com/v4/{$locationName}/media";

        try {
            $res = $this->client($integration)->post($url, [
                'mediaFormat' => 'PHOTO',
                'sourceUrl' => $sourceUrl,
                'description' => $description,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('GBP uploadPhoto timed out: '.$e->getMessage());
            return false;
        }

        if (! $res->successful()) {
            Log::warning('GBP uploadPhoto failed: '.$res->body());
            return false;
        }
        return true;
    }
}
