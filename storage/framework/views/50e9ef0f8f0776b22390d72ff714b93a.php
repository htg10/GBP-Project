<?php $__env->startSection('title', 'Credits'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Credits</h1><p>Your AI credit balance and usage history.</p></div>
    <div style="display:flex;align-items:center;gap:8px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:8px 14px;">
        <div style="width:36px;height:36px;border-radius:50%;background:<?php echo e($creditBalance > 20 ? 'var(--teal-soft)' : 'var(--rose-soft)'); ?>;display:grid;place-items:center;font-size:16px;font-weight:700;color:<?php echo e($creditBalance > 20 ? 'var(--teal-ink)' : 'var(--rose)'); ?>;"><?php echo e($creditBalance > 50 ? '✓' : '!'); ?></div>
        <div>
            <div style="font-size:22px;font-weight:700;line-height:1;"><?php echo e(number_format($creditBalance)); ?></div>
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;">Credits Left</div>
        </div>
    </div>
</div>


<div class="stats" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
    <?php if($sub): ?>
    <div class="stat accent">
        <div style="font-size:12px;color:var(--teal-ink);">Plan</div>
        <div class="v" style="font-size:20px;"><?php echo e(ucfirst(strtolower($sub->plan))); ?></div>
        <div class="l"><?php echo e(number_format($sub->monthly_credits)); ?> credits/mo</div>
    </div>
    <div class="stat">
        <div style="font-size:12px;color:var(--muted);">Used this month</div>
        <?php $usedTotal = collect($usageThisMonth)->sum('total_credits'); ?>
        <div class="v"><?php echo e(number_format($usedTotal)); ?></div>
        <div class="l">of <?php echo e(number_format($sub->monthly_credits)); ?></div>
    </div>
    <div class="stat">
        <div style="font-size:12px;color:var(--muted);">Remaining</div>
        <div class="v" style="color:<?php echo e($creditBalance > 20 ? 'var(--teal)' : 'var(--rose)'); ?>;"><?php echo e(number_format($creditBalance)); ?></div>
        <div class="l"><?php echo e($sub->monthly_credits > 0 ? round($creditBalance / $sub->monthly_credits * 100) . '%' : ''); ?></div>
    </div>
    <div class="stat">
        <div style="font-size:12px;color:var(--muted);">Renews</div>
        <div class="v" style="font-size:18px;"><?php echo e($sub->renews_at ? $sub->renews_at->format('d M') : '—'); ?></div>
        <div class="l"><?php echo e($sub->status); ?></div>
    </div>
    <?php else: ?>
    <div class="stat accent" style="grid-column:1/-1;">
        <div style="font-size:14px;">No active plan — <a href="<?php echo e(route('admin.billing')); ?>" style="color:var(--teal);text-decoration:underline;">subscribe to get credits</a></div>
    </div>
    <?php endif; ?>
</div>


<?php if($sub && $sub->monthly_credits > 0): ?>
<div class="card" style="margin-bottom:18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <strong style="font-size:14px;">Monthly Usage</strong>
        <span style="font-size:13px;color:var(--muted);"><?php echo e(number_format($usedTotal)); ?> / <?php echo e(number_format($sub->monthly_credits)); ?> credits</span>
    </div>
    <?php $pct = min(100, round($usedTotal / $sub->monthly_credits * 100)); ?>
    <div style="background:#f3f1ea;border-radius:8px;height:10px;overflow:hidden;">
        <div style="background:<?php echo e($pct > 80 ? 'var(--rose)' : 'var(--teal)'); ?>;height:100%;width:<?php echo e($pct); ?>%;border-radius:8px;transition:width .3s;"></div>
    </div>
</div>
<?php endif; ?>


<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px;margin-bottom:20px;">
    <div class="card">
        <div style="font-size:16px;font-weight:700;margin-bottom:10px;">Credit Costs</div>
        <table>
            <thead><tr><th>Action</th><th style="text-align:right;">Credits</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $creditCosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action => $cost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(app(\App\Services\CreditService::class)->actionLabel($action)); ?></td>
                    <td style="text-align:right;"><span class="badge <?php echo e($cost >= 5 ? 'amber' : ($cost >= 2 ? 'gray' : 'teal')); ?>" style="min-width:28px;text-align:center;"><?php echo e($cost); ?></span></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div style="font-size:16px;font-weight:700;margin-bottom:10px;">This Month's Breakdown</div>
        <?php if(empty($usageThisMonth)): ?>
            <div class="empty" style="padding:30px 20px;">No usage yet this month.</div>
        <?php else: ?>
            <table>
                <thead><tr><th>Action</th><th style="text-align:right;">Times</th><th style="text-align:right;">Credits</th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $usageThisMonth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['label']); ?></td>
                        <td style="text-align:right;"><?php echo e($row['times']); ?>x</td>
                        <td style="text-align:right;font-weight:700;"><?php echo e($row['total_credits']); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <tr style="border-top:2px solid var(--line);">
                        <td style="font-weight:700;">Total</td>
                        <td></td>
                        <td style="text-align:right;font-weight:700;color:var(--teal);"><?php echo e($usedTotal); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>


<div class="card" style="margin-bottom:20px;">
    <div style="font-size:16px;font-weight:700;margin-bottom:12px;">Plans</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
        <?php $__currentLoopData = $planCredits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan => $credits): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $isCurrent = $sub && $sub->plan === $plan; ?>
            <div style="padding:18px;border-radius:14px;text-align:center;<?php echo e($isCurrent ? 'border:2px solid var(--teal);background:var(--teal-soft);' : 'border:1px solid var(--line);'); ?>">
                <div style="font-weight:700;font-size:16px;"><?php echo e(ucfirst(strtolower($plan))); ?></div>
                <div style="font-size:26px;font-weight:700;color:var(--teal);margin:8px 0 2px;"><?php echo e(number_format($credits)); ?></div>
                <div style="font-size:12px;color:var(--muted);">credits/mo</div>
                <?php if($isCurrent): ?><div style="margin-top:6px;"><span class="badge teal">Current Plan</span></div><?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>


<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);"><strong style="font-size:16px;">Recent Transactions</strong></div>
    <?php if($ledger->isEmpty()): ?>
        <div class="empty">No credit transactions yet.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Date</th><th>Action</th><th>Description</th><th style="text-align:right;">Credits</th><th style="text-align:right;">Balance</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $ledger; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="white-space:nowrap;"><?php echo e($entry->created_at->format('d M, h:i A')); ?></td>
                    <td><span class="badge <?php echo e($entry->amount > 0 ? 'teal' : 'rose'); ?>"><?php echo e(app(\App\Services\CreditService::class)->actionLabel($entry->action)); ?></span></td>
                    <td style="color:var(--muted);font-size:12.5px;"><?php echo e($entry->description ?? '—'); ?></td>
                    <td style="text-align:right;font-weight:700;color:<?php echo e($entry->amount > 0 ? 'var(--teal)' : 'var(--rose)'); ?>;"><?php echo e($entry->amount > 0 ? '+' : ''); ?><?php echo e($entry->amount); ?></td>
                    <td style="text-align:right;"><?php echo e(number_format($entry->balance_after)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/dashboard/credits.blade.php ENDPATH**/ ?>