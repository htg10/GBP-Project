<?php

namespace App\Services;

/**
 * MockGbpProvider — returns demo reviews shaped like Google Business Profile
 * API responses. Swap this for a real GoogleGbpProvider that calls the live
 * REST API with the client's stored OAuth token once you connect Google.
 */
class MockGbpProvider
{
    public function listReviews(string $googleLocationName): array
    {
        return [
            ['reviewId' => 'g_r1', 'name' => 'Aarti Mehta', 'rating' => 5, 'comment' => 'Dr. Sharma was incredibly professional and patient. The clinic was spotless and staff explained everything clearly. Best dental experience I have had.', 'time' => '2026-06-24 09:12:00'],
            ['reviewId' => 'g_r2', 'name' => 'Rohit Verma', 'rating' => 2, 'comment' => 'Waited over an hour past my appointment and nobody updated me. Treatment was fine but the wait was frustrating.', 'time' => '2026-06-23 16:40:00'],
            ['reviewId' => 'g_r3', 'name' => 'Priya Nair', 'rating' => 4, 'comment' => 'Good service overall. Slightly expensive but the quality made up for it.', 'time' => '2026-06-23 11:05:00'],
            ['reviewId' => 'g_r4', 'name' => 'Sameer Khan', 'rating' => 1, 'comment' => 'Terrible experience. Billing was not transparent and I was charged for things nobody mentioned. Very disappointed.', 'time' => '2026-06-22 18:22:00'],
            ['reviewId' => 'g_r5', 'name' => 'Neha Kapoor', 'rating' => 5, 'comment' => 'Absolutely wonderful. Booked online, seen on time, painless cleaning. Highly recommend!', 'time' => '2026-06-22 08:15:00'],
            ['reviewId' => 'g_r6', 'name' => 'Vikram Singh', 'rating' => 3, 'comment' => 'Decent clinic. Doctor was knowledgeable but the front desk seemed disorganized when I arrived.', 'time' => '2026-06-21 14:30:00'],
        ];
    }

    public function putReply(string $googleLocationName, string $reviewId, string $comment): void
    {
        // Real implementation: PUT .../reviews/{id}/reply with OAuth token.
        // Mock: nothing to do; reply is persisted in our DB by the controller.
    }
}
