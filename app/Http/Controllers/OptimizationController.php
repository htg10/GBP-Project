<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Review;
use App\Models\SocialPost;
use App\Services\AiService;
use App\Services\CreditService;
use Illuminate\Http\Request;

/**
 * One-Click Optimization — runs a batch of AI actions for the agency in one go:
 *   - Drafts AI replies for every review that has no reply yet
 *   - Generates a fresh social post caption idea
 * Returns a summary of what was done. Uses Gemini when configured, else local.
 */
class OptimizationController extends Controller
{
    public function __construct(private AiService $ai, private CreditService $creditService) {}

    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $pending = Review::where('agency_id', $agencyId)->whereNull('reply_text')->count();
        $total = Review::where('agency_id', $agencyId)->count();

        return view('dashboard.optimize', compact('pending', 'total'));
    }

    public function run(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        if (!$this->creditService->canAfford($agencyId, 'ai_optimize')) {
            return response()->json(['error' => 'Insufficient credits (' . CreditService::COSTS['ai_optimize'] . ' needed).'], 402);
        }

        $this->creditService->deduct($agencyId, $request->user()->id, 'ai_optimize', 'One-click optimize batch');
        $actions = [];

        // 1. Draft AI replies for all unreplied reviews.
        $unreplied = Review::where('agency_id', $agencyId)->whereNull('reply_text')->get();
        $replied = 0;
        foreach ($unreplied as $review) {
            $reply = $this->ai->generateReviewReply($review->reviewer_name, $review->comment ?? '', $review->star_rating);
            $review->update([
                'reply_text' => $reply,
                'replied_at' => now(),
                'replied_by' => $request->user()->id,
            ]);
            $replied++;
        }
        if ($replied > 0) {
            $actions[] = ['icon' => '★', 'title' => "Replied to {$replied} review(s)", 'detail' => 'AI drafted and posted replies to reviews that were awaiting a response.'];
        } else {
            $actions[] = ['icon' => '★', 'title' => 'All reviews already replied', 'detail' => 'No pending reviews found — you are all caught up.'];
        }

        // 2. Generate a social post idea for the first client.
        $client = Client::where('agency_id', $agencyId)->first();
        if ($client) {
            $caption = $this->ai->generateContent("Write a short promotional social media post for {$client->name}");
            SocialPost::create([
                'agency_id' => $agencyId,
                'client_id' => $client->id,
                'platform' => 'FACEBOOK',
                'body' => $caption,
                'status' => 'DRAFT',
            ]);
            $actions[] = ['icon' => '◈', 'title' => 'Drafted a social post', 'detail' => 'A ready-to-edit post was added to your Social drafts.'];
        }

        // 3. Sentiment recap.
        $negative = Review::where('agency_id', $agencyId)->where('sentiment', 'NEGATIVE')->count();
        if ($negative > 0) {
            $actions[] = ['icon' => '▽', 'title' => "{$negative} negative review(s) flagged", 'detail' => 'These were replied to with care — consider following up personally too.'];
        }

        return response()->json(['actions' => $actions]);
    }
}
