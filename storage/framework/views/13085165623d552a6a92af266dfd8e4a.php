<?php $__env->startSection('title', 'Billing'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Billing</h1><p>Your subscription, payment history and downloadable invoices.</p></div>
    <a href="<?php echo e(route('plans')); ?>" class="btn">⬆ Change Plan</a>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;" class="bill-cards">
    <div class="card">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Current Plan</div>
        <div style="font-size:26px;font-weight:800;margin-top:6px;color:var(--teal);"><?php echo e($sub->plan ?? 'None'); ?></div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Status: <?php echo e($sub->status ?? '—'); ?></div>
    </div>
    <div class="card">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">AI Credits</div>
        <div style="font-size:26px;font-weight:800;margin-top:6px;color:var(--purple);"><?php echo e(number_format($sub->credit_balance ?? 0)); ?></div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;"><?php echo e(number_format($sub->monthly_credits ?? 0)); ?> / month</div>
    </div>
    <div class="card">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Renews</div>
        <div style="font-size:26px;font-weight:800;margin-top:6px;"><?php echo e($sub && $sub->renews_at ? $sub->renews_at->format('d M') : '—'); ?></div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;"><?php echo e($sub && $sub->renews_at ? $sub->renews_at->format('Y') : 'No active renewal'); ?></div>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);"><strong style="font-size:15px;">Payment History</strong></div>
    <?php if($payments->isEmpty()): ?>
        <div class="empty" style="padding:36px;">No payments yet. Your invoices will appear here after your first payment.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Invoice #</th><th>Date</th><th>Plan</th><th style="text-align:right;">Amount</th><th>Status</th><th style="text-align:right;">Invoice</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="font-family:monospace;font-size:12.5px;">INV-<?php echo e(str_pad($pay->id, 5, '0', STR_PAD_LEFT)); ?></td>
                        <td><?php echo e($pay->created_at->format('d M Y')); ?></td>
                        <td><?php echo e($pay->plan); ?></td>
                        <td style="text-align:right;font-weight:600;">₹<?php echo e(number_format($pay->amount / 100, 2)); ?></td>
                        <td><span class="badge <?php echo e($pay->status==='PAID'?'teal':($pay->status==='FAILED'?'rose':'gray')); ?>"><?php echo e($pay->status); ?></span></td>
                        <td style="text-align:right;">
                            <?php if($pay->status === 'PAID'): ?>
                                <a href="<?php echo e(route('client-billing.invoice', $pay)); ?>" target="_blank" class="btn btn-ghost" style="padding:6px 12px;font-size:12px;">⬇ PDF</a>
                            <?php else: ?>
                                <span style="color:var(--muted);font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('head'); ?>
<style>@media (max-width:760px){ .bill-cards{grid-template-columns:1fr !important;} }</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\billing.blade.php ENDPATH**/ ?>