<?php

namespace App\Services;

use App\Models\CreditLedger;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public const COSTS = [
        'ai_review_reply'    => 1,
        'ai_social_caption'  => 1,
        'ai_keyword_gen'     => 2,
        'ai_rank_check'      => 1,
        'ai_media_generate'  => 5,
        'ai_chat'            => 1,
        'ai_post_caption'    => 1,
        'ai_photo_caption'   => 1,
        'ai_optimize'        => 3,
        'ai_audit'           => 2,
        'ai_competitor'      => 2,
    ];

    public const PLAN_CREDITS = [
        'STARTER' => 100,
        'GROWTH'  => 500,
        'AGENCY'  => 2000,
    ];

    public function balance(int $agencyId): int
    {
        $sub = Subscription::where('agency_id', $agencyId)->first();
        return $sub ? $sub->credit_balance : 0;
    }

    public function costFor(string $action): int
    {
        return self::COSTS[$action] ?? 1;
    }

    public function canAfford(int $agencyId, string $action): bool
    {
        return $this->balance($agencyId) >= $this->costFor($action);
    }

    /**
     * Deduct credits for an action. Returns true on success, false if insufficient.
     */
    public function deduct(int $agencyId, ?int $userId, string $action, ?string $description = null): bool
    {
        $cost = $this->costFor($action);

        return DB::transaction(function () use ($agencyId, $userId, $action, $description, $cost) {
            $sub = Subscription::where('agency_id', $agencyId)->lockForUpdate()->first();
            if (!$sub || $sub->credit_balance < $cost) {
                return false;
            }

            $sub->decrement('credit_balance', $cost);

            CreditLedger::create([
                'agency_id' => $agencyId,
                'user_id' => $userId,
                'amount' => -$cost,
                'balance_after' => $sub->credit_balance,
                'action' => $action,
                'description' => $description,
            ]);

            return true;
        });
    }

    /**
     * Add credits (top-up or monthly reset).
     */
    public function add(int $agencyId, int $credits, string $action, ?string $description = null, ?int $userId = null): int
    {
        return DB::transaction(function () use ($agencyId, $credits, $action, $description, $userId) {
            $sub = Subscription::where('agency_id', $agencyId)->lockForUpdate()->first();
            if (!$sub) {
                return 0;
            }

            $sub->increment('credit_balance', $credits);

            CreditLedger::create([
                'agency_id' => $agencyId,
                'user_id' => $userId,
                'amount' => $credits,
                'balance_after' => $sub->credit_balance,
                'action' => $action,
                'description' => $description,
            ]);

            return $sub->credit_balance;
        });
    }

    /**
     * Reset monthly credits for a subscription (called on plan activation or monthly renewal).
     */
    public function resetMonthly(int $agencyId, string $plan): void
    {
        $credits = self::PLAN_CREDITS[$plan] ?? 0;
        $sub = Subscription::where('agency_id', $agencyId)->first();
        if (!$sub) return;

        $sub->update([
            'credit_balance' => $credits,
            'monthly_credits' => $credits,
            'credits_reset_at' => now(),
        ]);

        CreditLedger::create([
            'agency_id' => $agencyId,
            'amount' => $credits,
            'balance_after' => $credits,
            'action' => 'monthly_reset',
            'description' => ucfirst(strtolower($plan)) . " plan — {$credits} credits allocated",
        ]);
    }

    public function usageThisMonth(int $agencyId): array
    {
        $rows = CreditLedger::where('agency_id', $agencyId)
            ->where('amount', '<', 0)
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('action, COUNT(*) as times, SUM(ABS(amount)) as total_credits')
            ->groupBy('action')
            ->get();

        return $rows->map(fn ($r) => [
            'action' => $r->action,
            'label' => $this->actionLabel($r->action),
            'times' => $r->times,
            'total_credits' => $r->total_credits,
        ])->toArray();
    }

    public function actionLabel(string $action): string
    {
        return match ($action) {
            'ai_review_reply' => 'AI Review Reply',
            'ai_social_caption' => 'Social Caption',
            'ai_keyword_gen' => 'Keyword Generation',
            'ai_rank_check' => 'Rank Check',
            'ai_media_generate' => 'AI Image Generation',
            'ai_chat' => 'AI Chat',
            'ai_post_caption' => 'Post Caption',
            'ai_photo_caption' => 'Photo Caption',
            'ai_optimize' => 'One-Click Optimize',
            'ai_audit' => 'Google Audit',
            'ai_competitor' => 'Competitor Analysis',
            'monthly_reset' => 'Monthly Reset',
            'manual_topup' => 'Manual Top-up',
            'plan_purchase' => 'Plan Purchase',
            default => str_replace('_', ' ', ucfirst($action)),
        };
    }
}
