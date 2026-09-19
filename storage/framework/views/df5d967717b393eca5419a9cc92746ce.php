<?php $__env->startSection('title', 'Billing'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head"><h1>Billing</h1><p>Subscription status, credits, and payment history.</p></div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:22px;">
    <div style="background:var(--teal-soft);border:1px solid #d5deff;border-radius:12px;padding:14px 18px;font-size:14px;">
        Plan: <strong><?php echo e($sub->plan ?? 'None'); ?></strong>
        <?php if($sub): ?><span style="margin-left:6px;font-size:12px;color:var(--teal-ink);">(<?php echo e($sub->status); ?>)</span><?php endif; ?>
    </div>
    <div style="background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 18px;font-size:14px;">
        Credits: <strong style="color:var(--teal);"><?php echo e(number_format($creditBalance)); ?></strong>
        <?php if($sub && $sub->monthly_credits > 0): ?>
            <span style="font-size:12px;color:var(--muted);">/ <?php echo e(number_format($sub->monthly_credits)); ?> monthly</span>
        <?php endif; ?>
    </div>
    <a href="<?php echo e(route('admin.plans')); ?>" style="background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 18px;font-size:14px;display:flex;align-items:center;justify-content:space-between;">
        Manage Plans <span>→</span>
    </a>
</div>


<div class="card" style="max-width:440px;">
    <strong style="font-size:14px;">Manual credit top-up</strong>
    <form method="POST" action="<?php echo e(route('admin.billing.topup')); ?>" style="display:flex;gap:10px;align-items:flex-end;margin-top:10px;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="agency_id" value="<?php echo e(auth()->user()->agency_id); ?>">
        <label style="flex:1;"><span class="lbl">Credits to add</span><input type="number" name="credits" min="1" max="10000" value="100" required></label>
        <button type="submit" class="btn" style="margin-bottom:1px;">+ Add credits</button>
    </form>
</div>

<?php if($payments->count()): ?>
    <div style="margin-top:26px;">
        <strong style="font-size:14px;">Payment history</strong>
        <div class="card" style="padding:0;overflow:hidden;margin-top:10px;">
            <table>
                <thead><tr><th>Date</th><th>Plan</th><th style="text-align:right;">Amount</th><th>Status</th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($pay->created_at->format('d M Y, h:i A')); ?></td>
                            <td><?php echo e($pay->plan); ?></td>
                            <td style="text-align:right;">₹<?php echo e(number_format($pay->amount / 100)); ?></td>
                            <td><span class="badge <?php echo e($pay->status==='PAID'?'teal':'dark'); ?>"><?php echo e($pay->status); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\admin\billing.blade.php ENDPATH**/ ?>