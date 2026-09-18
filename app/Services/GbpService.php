<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Integration;

/**
 * GbpService — single entry point the app uses for Google Business Profile work.
 *
 * It checks whether a client has a live Google connection (a GOOGLE_GBP
 * integration with an access token). If yes, it calls the REAL Google API via
 * GoogleGbpProvider. If not, it falls back to MockGbpProvider so the app keeps
 * working with demo data during development / before API approval.
 *
 * Controllers only ever talk to THIS class — they never care which mode is on.
 */
class GbpService
{
    public function __construct(
        private GoogleGbpProvider $real,
        private MockGbpProvider $mock,
    ) {}

    private function integrationFor(Client $client): ?Integration
    {
        return Integration::where('client_id', $client->id)
            ->where('provider', 'GOOGLE_GBP')
            ->whereNotNull('access_token')
            ->first();
    }

    public function isLive(Client $client): bool
    {
        return (bool) $this->integrationFor($client);
    }

    /** Why the last real-API call returned nothing, if it did. Null on mock calls / success. */
    public function lastError(): ?string
    {
        return $this->real->getLastError();
    }

    public function listReviews(Client $client, string $locationName): array
    {
        $integration = $this->integrationFor($client);
        if ($integration) {
            return $this->real->listReviews($integration, $locationName);
        }
        return $this->mock->listReviews($locationName);
    }

    public function putReply(Client $client, string $locationName, string $reviewId, string $comment): bool
    {
        $integration = $this->integrationFor($client);
        if ($integration) {
            return $this->real->putReply($integration, $locationName, $reviewId, $comment);
        }
        $this->mock->putReply($locationName, $reviewId, $comment);
        return true;
    }

    public function createPost(Client $client, string $locationName, array $payload): bool
    {
        $integration = $this->integrationFor($client);
        if ($integration) {
            return $this->real->createPost($integration, $locationName, $payload);
        }
        return true; // mock: pretend success
    }

    public function uploadPhoto(Client $client, string $locationName, string $sourceUrl, ?string $description = null): bool
    {
        $integration = $this->integrationFor($client);
        if ($integration) {
            return $this->real->uploadPhoto($integration, $locationName, $sourceUrl, $description);
        }
        return true; // mock: pretend success
    }

    public function fetchPerformanceMetrics(Client $client, string $locationName, string $startDate, string $endDate): array
    {
        $integration = $this->integrationFor($client);
        if ($integration) {
            return $this->real->fetchPerformanceMetrics($integration, $locationName, $startDate, $endDate);
        }
        return [];
    }
}
