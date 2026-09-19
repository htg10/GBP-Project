<?php $__env->startSection('title', 'Credit Packages'); ?>
<?php $__env->startSection('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div class="page-head" style="margin:0;"><h1>Credit Packages</h1><p>Create credit packs clients can buy. Pause any pack to hide it from the store.</p></div>
    <button class="btn" onclick="openCreate()">+ New package</button>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <table>
        <thead><tr><th>Package</th><th>Credits</th><th>Price (incl. GST)</th><th>GST</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($p->name); ?></strong></td>
                    <td style="font-weight:700;"><?php echo e(number_format($p->credits)); ?></td>
                    <td>₹<?php echo e(number_format($p->price)); ?></td>
                    <td style="font-size:12.5px;color:var(--muted);"><?php echo e($p->gst_rate); ?>% = ₹<?php echo e(number_format($p->gstAmount(),2)); ?></td>
                    <td><span class="badge <?php echo e($p->is_active ? 'teal' : 'dark'); ?>"><?php echo e($p->is_active ? 'Active' : 'Paused'); ?></span></td>
                    <td style="text-align:right;white-space:nowrap;">
                        <form method="POST" action="<?php echo e(route('admin.credit-packages.toggle', $p)); ?>" style="display:inline;"><?php echo csrf_field(); ?>
                            <button class="icon-btn" title="<?php echo e($p->is_active ? 'Pause' : 'Activate'); ?>"><?php echo e($p->is_active ? '⏸' : '▶'); ?></button>
                        </form>
                        <button class="icon-btn" onclick='openEdit(<?php echo json_encode($p, 15, 512) ?>)'>✎</button>
                        <form method="POST" action="<?php echo e(route('admin.credit-packages.destroy', $p)); ?>" style="display:inline;" onsubmit="return confirm('Delete this package?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="icon-btn" style="color:var(--rose);">🗑</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px;">No packages yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-bg" id="pkg-modal">
    <div class="modal">
        <h2 id="pm-title" style="font-size:18px;margin-bottom:14px;">New package</h2>
        <form method="POST" id="pkg-form" action="<?php echo e(route('admin.credit-packages.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Package name</span><input type="text" name="name" id="f-name" placeholder="e.g. Booster Pack" required></label>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <label><span class="lbl">Credits</span><input type="number" name="credits" id="f-credits" min="1" value="500" required></label>
                <label><span class="lbl">Price ₹ (incl GST)</span><input type="number" name="price" id="f-price" min="0" value="0" oninput="calcGst()" required></label>
                <label><span class="lbl">GST %</span><input type="number" name="gst_rate" id="f-gst" min="0" max="50" value="18" oninput="calcGst()" required></label>
            </div>
            <div id="gst-preview" style="background:var(--teal-soft);color:var(--teal-ink);border-radius:10px;padding:9px 12px;font-size:12.5px;margin-top:8px;font-weight:600;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;">
                <label><span class="lbl">Sort order</span><input type="number" name="sort" id="f-sort" min="0" value="0"></label>
                <label style="display:flex;align-items:center;gap:8px;margin-top:28px;font-size:13px;"><input type="checkbox" name="is_active" id="f-active" value="1" checked style="width:16px;height:16px;"> Active</label>
            </div>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('pkg-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="pm-submit">Create package</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const pform = document.getElementById('pkg-form');
const createUrl = "<?php echo e(route('admin.credit-packages.store')); ?>";
function calcGst(){
    const price=+document.getElementById('f-price').value||0, gst=+document.getElementById('f-gst').value||0;
    const base=price/(1+gst/100);
    document.getElementById('gst-preview').textContent = `Base ₹${base.toFixed(2)}  +  ${gst}% GST ₹${(price-base).toFixed(2)}  =  ₹${price.toFixed(2)}`;
}
function openCreate(){
    document.getElementById('pm-title').textContent='New package';
    document.getElementById('pm-submit').textContent='Create package';
    pform.action=createUrl;
    document.getElementById('f-name').value=''; document.getElementById('f-credits').value=500;
    document.getElementById('f-price').value=0; document.getElementById('f-gst').value=18;
    document.getElementById('f-sort').value=0; document.getElementById('f-active').checked=true;
    calcGst(); document.getElementById('pkg-modal').classList.add('open');
}
function openEdit(p){
    document.getElementById('pm-title').textContent='Edit package';
    document.getElementById('pm-submit').textContent='Save changes';
    pform.action="<?php echo e(url('admin/credit-packages')); ?>/"+p.id;
    document.getElementById('f-name').value=p.name; document.getElementById('f-credits').value=p.credits;
    document.getElementById('f-price').value=p.price; document.getElementById('f-gst').value=p.gst_rate;
    document.getElementById('f-sort').value=p.sort||0; document.getElementById('f-active').checked=!!p.is_active;
    calcGst(); document.getElementById('pkg-modal').classList.add('open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\admin\credit-packages.blade.php ENDPATH**/ ?>