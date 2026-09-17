<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Generates realistic DEMO reviews so the Advanced Reports dashboard has data
 * to visualise. Safe to re-run — it clears its own demo rows first.
 *
 *   php artisan db:seed --class=DemoReviewsSeeder
 */
class DemoReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $location = GbpLocation::first();
        if (! $location) {
            $this->command->warn('No GBP location found. Run: php artisan db:seed  first.');
            return;
        }

        $client = Client::find($location->client_id);
        $agencyId = $client?->agency_id;
        if (! $agencyId) {
            $this->command->warn('Location has no agency. Aborting.');
            return;
        }

        // Clear previously seeded demo reviews for a clean re-run.
        Review::where('google_review_id', 'like', 'demo-%')->delete();

        $firstNames = ['Aarav', 'Priya', 'Rahul', 'Sneha', 'Vikram', 'Ananya', 'Rohan', 'Meera', 'Karan', 'Divya', 'Arjun', 'Neha', 'Sanjay', 'Pooja', 'Amit', 'Ritu', 'Nikhil', 'Kavya', 'Rajesh', 'Isha'];
        $lastNames = ['Sharma', 'Verma', 'Patel', 'Gupta', 'Singh', 'Reddy', 'Nair', 'Mehta', 'Jain', 'Kapoor'];

        $positive = [
            'Absolutely wonderful experience, the staff was professional and friendly!',
            'Best service in the city, highly recommend to everyone.',
            'Clean, spotless and very well organised. Will come again.',
            'The team was patient and helpful throughout. Great quality!',
            'Smooth and painless process. Excellent work, thank you!',
        ];
        $neutral = [
            'Decent service overall, but the wait was a little long.',
            'It was okay. Some parts were good, others could improve.',
            'Average experience, nothing special but got the job done.',
        ];
        $negative = [
            'Waited far too long and felt the service was disorganised.',
            'Disappointed with the experience, expected better for the price.',
            'Staff seemed rushed and the place felt a bit untidy.',
        ];

        $count = 150;
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            // Weight toward high ratings (target avg ~4.8, like a healthy profile).
            $roll = random_int(1, 100);
            $star = $roll <= 68 ? 5 : ($roll <= 88 ? 4 : ($roll <= 94 ? 3 : ($roll <= 98 ? 2 : 1)));

            if ($star >= 4) { $sentiment = 'POSITIVE'; $comment = $positive[array_rand($positive)]; }
            elseif ($star == 3) { $sentiment = 'NEUTRAL'; $comment = $neutral[array_rand($neutral)]; }
            else { $sentiment = 'NEGATIVE'; $comment = $negative[array_rand($negative)]; }

            // Spread review dates across the last 8 months.
            $reviewTime = Carbon::now()->subDays(random_int(0, 240))->subHours(random_int(0, 23));

            // ~72% get a reply, 1–6 days later.
            $replied = random_int(1, 100) <= 72;
            $replyText = $replied ? 'Thank you so much for your feedback — we truly appreciate it!' : null;
            $repliedAt = $replied ? (clone $reviewTime)->addDays(random_int(1, 6))->addHours(random_int(0, 20)) : null;

            $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];

            Review::create([
                'agency_id'        => $agencyId,
                'gbp_location_id'  => $location->id,
                'google_review_id' => 'demo-' . $i . '-' . uniqid(),
                'reviewer_name'    => $name,
                'star_rating'      => $star,
                'comment'          => $comment,
                'sentiment'        => $sentiment,
                'reply_text'       => $replyText,
                'replied_at'       => $repliedAt,
                'review_time'      => $reviewTime,
            ]);
            $created++;
        }

        $this->command->info("Seeded {$created} demo reviews for location: {$location->title}");
    }
}
