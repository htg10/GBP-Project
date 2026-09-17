<?php $__env->startSection('title', 'Billing'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head"><h1>Billing</h1><p>Choose a plan and pay securely via Razorpay.</p></div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:22px;">
    <div style="background:var(--teal-soft);border:1px solid #cfe6e0;border-radius:12px;padding:14px 18px;font-size:14px;">
        Plan: <strong><?php echo e($sub->plan ?? 'None'); ?></strong>
        <?php if($sub): ?><span style="margin-left:6px;font-size:12px;color:var(--teal-ink);">(<?php echo e($sub->status); ?>)</span><?php endif; ?>
    </div>
    <div style="background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 18px;font-size:14px;">
        Credits: <strong style="color:var(--teal);"><?php echo e(number_format($creditBalance)); ?></strong>
        <?php if($sub && $sub->monthly_credits > 0): ?>
            <span style="font-size:12px;color:var(--muted);">/ <?php echo e(number_format($sub->monthly_credits)); ?> monthly</span>
        <?php endif; ?>
    </div>
</div>

<?php if(!$razorpayReady): ?>
    <div class="alert error" style="margin-bottom:18px;">
        Razorpay keys missing. Add RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET to your .env to enable payments.
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;">
    <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id=>$p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $active = ($sub->plan ?? null) === $id; ?>
        <div class="card" style="<?php echo e($active ? 'border:2px solid var(--teal);' : ''); ?>">
            <div style="font-size:16px;font-weight:700;"><?php echo e($p['name']); ?></div>
            <div style="font-size:20px;font-weight:700;color:var(--teal);margin:6px 0 4px;">₹<?php echo e(number_format($p['price'])); ?>/mo</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:10px;"><?php echo e(number_format($planCredits[$id] ?? 0)); ?> AI credits/mo</div>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                <?php $__currentLoopData = $p['features']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li style="font-size:13px;color:#3a4a45;">✓ <?php echo e($f); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <?php if($active): ?>
                <button class="btn" style="width:100%;background:transparent;border:1px solid var(--line);color:var(--muted);justify-content:center;" disabled>Current plan</button>
            <?php else: ?>
                <button class="btn pay-btn" style="width:100%;justify-content:center;" data-plan="<?php echo e($id); ?>" <?php echo e($razorpayReady ? '' : 'disabled'); ?>>Subscribe →</button>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="card" style="margin-top:22px;max-width:420px;">
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

<?php $__env->startPush('scripts'); ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;

document.querySelectorAll('.pay-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const plan = btn.dataset.plan;
        btn.textContent = 'Please wait…'; btn.disabled = true;

        try {
            // Step 1: create order on our server
            const res = await fetch("<?php echo e(route('admin.billing.checkout')); ?>", {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
                body: JSON.stringify({plan})
            });
            const data = await res.json();
            if (data.error) { alert(data.error); btn.textContent = 'Subscribe →'; btn.disabled = false; return; }

            // Step 2: open Razorpay checkout
            const options = {
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: 'ReviewFlow',
                description: data.plan_name + ' plan',
                order_id: data.order_id,
                theme: {color: '#0f6b5c'},
                handler: async function (response) {
                    // Step 3: verify on our server
                    const vr = await fetch("<?php echo e(route('admin.billing.verify')); ?>", {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature,
                            plan: plan
                        })
                    });
                    const vd = await vr.json();
                    if (vd.success) { alert('Payment successful! Plan activated.'); location.reload(); }
                    else { alert(vd.error || 'Verification failed'); }
                },
                modal: {
                    ondismiss: function () { btn.textContent = 'Subscribe →'; btn.disabled = false; }
                }
            };
            const rzp = new Razorpay(options);
            rzp.open();
        } catch (e) {
            alert('Something went wrong. Check console.');
            btn.textContent = 'Subscribe →'; btn.disabled = false;
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\admin\billing.blade.php ENDPATH**/ ?>