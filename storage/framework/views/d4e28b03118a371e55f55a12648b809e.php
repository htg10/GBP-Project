<?php $__env->startSection('title', 'Buy Credits'); ?>
<?php $__env->startSection('content'); ?>

<div class="page-head">
    <div>
        <h1>Buy Credits</h1>
        <p>Top up your AI credits anytime. New credits are <strong>added</strong> to your current balance.</p>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:10px;padding:10px 16px;box-shadow:none;">
        <span style="font-size:20px;">⚡</span>
        <div><div style="font-size:22px;font-weight:800;line-height:1;"><?php echo e(number_format($balance)); ?></div><div style="font-size:11px;color:var(--muted);">Current balance</div></div>
    </div>
</div>

<?php if($packages->isEmpty()): ?>
    <div class="card"><div class="empty">No credit packages available yet. Ask your Super Admin to create one.</div></div>
<?php else: ?>
<div class="pkg-grid">
    <?php $accent = ['#22c55e','#4c6fff','#8b5cf6','#f59e0b','#ec4899']; ?>
    <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="pkg-card" style="--pc:<?php echo e($accent[$i % count($accent)]); ?>;">
            <div class="pkg-credits"><?php echo e(number_format($pkg->credits)); ?></div>
            <div class="pkg-credits-l">credits</div>
            <div class="pkg-name"><?php echo e($pkg->name); ?></div>
            <div class="pkg-price">₹<?php echo e(number_format($pkg->price)); ?></div>
            <div class="pkg-gst">Incl. <?php echo e($pkg->gst_rate); ?>% GST (₹<?php echo e(number_format($pkg->gstAmount(),2)); ?>)</div>
            <div class="pkg-after">Balance after: <b><?php echo e(number_format($balance + $pkg->credits)); ?></b></div>
            <?php if(!$canManage): ?>
                <button class="btn btn-ghost" style="width:100%;justify-content:center;" disabled>Owner only</button>
            <?php elseif($razorpayReady): ?>
                <button class="btn buy-btn" style="width:100%;justify-content:center;background:var(--pc);" data-pkg="<?php echo e($pkg->id); ?>" data-name="<?php echo e($pkg->name); ?>">Buy — Pay ₹<?php echo e(number_format($pkg->price)); ?></button>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('buy-credits.instant')); ?>" onsubmit="return confirm('Add <?php echo e(number_format($pkg->credits)); ?> credits to your balance?')">
                    <?php echo csrf_field(); ?><input type="hidden" name="package" value="<?php echo e($pkg->id); ?>">
                    <button type="submit" class="btn" style="width:100%;justify-content:center;background:var(--pc);">Buy <?php echo e(number_format($pkg->credits)); ?> credits</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<?php if($razorpayReady): ?>
<?php $__env->startPush('scripts'); ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
document.querySelectorAll('.buy-btn').forEach(btn => {
    const original = btn.textContent;
    btn.addEventListener('click', async () => {
        const pkg = btn.dataset.pkg;
        btn.textContent = 'Please wait…'; btn.disabled = true;
        try {
            const res = await fetch("<?php echo e(route('buy-credits.checkout')); ?>", {
                method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json'}, body:JSON.stringify({package:pkg})
            });
            const data = await res.json();
            if (data.error){ alert(data.error); btn.textContent=original; btn.disabled=false; return; }
            new Razorpay({
                key:data.key, amount:data.amount, currency:data.currency, name:'ReviewFlow',
                description:data.name+' — credits', order_id:data.order_id, theme:{color:'#4c6fff'},
                handler: async function(r){
                    const vr = await fetch("<?php echo e(route('buy-credits.verify')); ?>", {
                        method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json'},
                        body:JSON.stringify({razorpay_order_id:r.razorpay_order_id, razorpay_payment_id:r.razorpay_payment_id, razorpay_signature:r.razorpay_signature, package:pkg})
                    });
                    const vd = await vr.json();
                    if(vd.success){ alert('Credits added to your balance!'); location.reload(); }
                    else { alert(vd.error||'Verification failed'); btn.textContent=original; btn.disabled=false; }
                },
                modal:{ ondismiss:function(){ btn.textContent=original; btn.disabled=false; } }
            }).open();
        } catch(e){ alert('Something went wrong.'); btn.textContent=original; btn.disabled=false; }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startPush('head'); ?>
<style>
    .pkg-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;}
    .pkg-card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:24px 22px;box-shadow:var(--shadow);text-align:center;border-top:3px solid var(--pc);transition:transform .15s;}
    .pkg-card:hover{transform:translateY(-3px);}
    .pkg-credits{font-size:38px;font-weight:800;color:var(--pc);line-height:1;}
    .pkg-credits-l{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;}
    .pkg-name{font-weight:700;font-size:15px;}
    .pkg-price{font-size:24px;font-weight:800;margin-top:8px;}
    .pkg-gst{font-size:11.5px;color:var(--muted);margin-bottom:6px;}
    .pkg-after{font-size:12.5px;color:var(--muted);margin-bottom:16px;}
    .pkg-after b{color:var(--ink);}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/dashboard/buy-credits.blade.php ENDPATH**/ ?>