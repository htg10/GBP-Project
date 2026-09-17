<?php $__env->startSection('title', 'Plans & Upgrade'); ?>
<?php $__env->startSection('content'); ?>

<div class="page-head">
    <div>
        <h1>Plans &amp; Upgrade</h1>
        <p>Choose the plan that fits your business. Upgrade anytime — credits are added instantly.</p>
    </div>
    <a href="<?php echo e(route('credits')); ?>" class="btn btn-ghost">⚡ View Credits</a>
</div>

<?php
    $current = $sub->plan ?? null;
    $order = ['STARTER' => 1, 'GROWTH' => 2, 'AGENCY' => 3];
    $accent = ['STARTER' => '#22c55e', 'GROWTH' => '#4c6fff', 'AGENCY' => '#8b5cf6'];
?>

<div class="plan-grid">
    <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $isCurrent = $current === $key;
            $isPopular = $key === 'GROWTH';
            $clr = $accent[$key];
        ?>
        <div class="plan-card <?php echo e($isCurrent ? 'current' : ''); ?>" style="--pc:<?php echo e($clr); ?>;">
            <?php if($isPopular): ?><div class="plan-tag">Most Popular</div><?php endif; ?>
            <div class="plan-name"><?php echo e($plan['name']); ?></div>
            <div class="plan-price">₹<?php echo e(number_format($plan['price'])); ?><span>/mo</span></div>
            <div class="plan-credits"><?php echo e(number_format($planCredits[$key] ?? 0)); ?> AI credits / month</div>
            <ul class="plan-feats">
                <?php $__currentLoopData = $plan['features']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><span class="pf-check">✓</span> <?php echo e($feat); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <?php $label = ($order[$key] ?? 0) > ($order[$current] ?? 0) ? 'Upgrade' : 'Switch'; ?>
            <?php if($isCurrent): ?>
                <button class="btn" style="width:100%;justify-content:center;background:#eef1f8;color:var(--muted);cursor:default;" disabled>Current Plan</button>
            <?php elseif(!$canManage): ?>
                <button class="btn btn-ghost" style="width:100%;justify-content:center;" disabled title="Only the account owner can change the plan">Owner only</button>
            <?php elseif($razorpayReady): ?>
                <button class="btn pay-btn" style="width:100%;justify-content:center;background:var(--pc);" data-plan="<?php echo e($key); ?>" data-name="<?php echo e($plan['name']); ?>">
                    <?php echo e($label); ?> — Pay ₹<?php echo e(number_format($plan['price'])); ?>

                </button>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('plans.upgrade')); ?>" onsubmit="return confirm('Switch to the <?php echo e($plan['name']); ?> plan? <?php echo e(number_format($planCredits[$key] ?? 0)); ?> credits will be allocated.')">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="plan" value="<?php echo e($key); ?>">
                    <button type="submit" class="btn" style="width:100%;justify-content:center;background:var(--pc);"><?php echo e($label); ?> to <?php echo e($plan['name']); ?></button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="card" style="margin-top:18px;display:flex;align-items:center;gap:14px;">
    <div style="width:42px;height:42px;border-radius:12px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-size:18px;flex-shrink:0;">⚡</div>
    <div style="flex:1;">
        <strong style="font-size:14px;">Current balance: <?php echo e(number_format($creditBalance)); ?> credits</strong>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">
            <?php if($razorpayReady): ?>
                🔒 Secure payments powered by Razorpay (test mode). Your plan renews monthly.
            <?php else: ?>
                Demo mode — upgrades activate instantly. Add Razorpay keys in <code>.env</code> to take live payments.
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if($razorpayReady): ?>
<?php $__env->startPush('scripts'); ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
document.querySelectorAll('.pay-btn').forEach(btn => {
    const original = btn.textContent;
    btn.addEventListener('click', async () => {
        const plan = btn.dataset.plan;
        btn.textContent = 'Please wait…'; btn.disabled = true;
        try {
            const res = await fetch("<?php echo e(route('plans.checkout')); ?>", {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
                body: JSON.stringify({plan})
            });
            const data = await res.json();
            if (data.error) { alert(data.error); btn.textContent = original; btn.disabled = false; return; }
            const options = {
                key: data.key, amount: data.amount, currency: data.currency,
                name: 'ReviewFlow', description: data.plan_name + ' plan', order_id: data.order_id,
                theme: {color: '#4c6fff'},
                handler: async function (response) {
                    const vr = await fetch("<?php echo e(route('plans.verify')); ?>", {
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
                    if (vd.success) { alert('Payment successful! Your ' + data.plan_name + ' plan is now active.'); location.reload(); }
                    else { alert(vd.error || 'Verification failed'); btn.textContent = original; btn.disabled = false; }
                },
                modal: { ondismiss: function () { btn.textContent = original; btn.disabled = false; } }
            };
            new Razorpay(options).open();
        } catch (e) {
            alert('Something went wrong starting checkout.');
            btn.textContent = original; btn.disabled = false;
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startPush('head'); ?>
<style>
    .plan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
    .plan-card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:26px 22px;box-shadow:var(--shadow);position:relative;display:flex;flex-direction:column;transition:transform .15s,box-shadow .15s;}
    .plan-card:hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(76,111,255,.14);}
    .plan-card.current{border:1.5px solid var(--pc);}
    .plan-tag{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:var(--pc);color:#fff;font-size:11px;font-weight:700;padding:4px 14px;border-radius:999px;white-space:nowrap;}
    .plan-name{font-size:15px;font-weight:700;color:var(--pc);text-transform:uppercase;letter-spacing:.04em;}
    .plan-price{font-size:36px;font-weight:800;margin-top:8px;}
    .plan-price span{font-size:15px;font-weight:500;color:var(--muted);}
    .plan-credits{font-size:13px;color:var(--muted);margin-top:2px;margin-bottom:18px;}
    .plan-feats{list-style:none;flex:1;margin-bottom:20px;}
    .plan-feats li{font-size:13.5px;padding:7px 0;border-top:1px solid var(--line);display:flex;align-items:center;gap:9px;}
    .plan-feats li:first-child{border-top:none;}
    .pf-check{width:18px;height:18px;border-radius:50%;background:var(--pc);color:#fff;display:grid;place-items:center;font-size:10px;flex-shrink:0;}
    @media (max-width:760px){ .plan-grid{grid-template-columns:1fr;} }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/dashboard/plans.blade.php ENDPATH**/ ?>