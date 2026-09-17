<?php $__env->startSection('title', 'Plans'); ?>
<?php $__env->startSection('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div class="page-head" style="margin:0;"><h1>Plans</h1><p>Create plans with GST-inclusive pricing and per-plan module access.</p></div>
    <button class="btn" onclick="openCreate()">+ New plan</button>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <table>
        <thead><tr><th>Plan</th><th>Price (incl. GST)</th><th>GST breakdown</th><th>Credits</th><th>Access</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($p->name); ?></strong><div style="font-size:12px;color:var(--muted);"><?php echo e($p->code); ?></div></td>
                    <td><strong>₹<?php echo e(number_format($p->price)); ?></strong></td>
                    <td style="font-size:12.5px;color:var(--muted);">Base ₹<?php echo e(number_format($p->baseAmount(),2)); ?><br>GST <?php echo e($p->gst_rate); ?>% = ₹<?php echo e(number_format($p->gstAmount(),2)); ?></td>
                    <td><?php echo e(number_format($p->credits)); ?></td>
                    <td style="font-size:12px;color:var(--muted);"><?php echo e(empty($p->permissions) ? 'All modules' : count($p->permissions).' modules'); ?></td>
                    <td><span class="badge <?php echo e($p->is_active ? 'teal' : 'dark'); ?>"><?php echo e($p->is_active ? 'Active' : 'Hidden'); ?></span></td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button class="icon-btn" onclick='openEdit(<?php echo json_encode($p, 15, 512) ?>)'>✎</button>
                        <form method="POST" action="<?php echo e(route('admin.plans.destroy', $p)); ?>" style="display:inline;" onsubmit="return confirm('Delete this plan?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="icon-btn" style="color:var(--rose);">🗑</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px;">No plans yet. Create your first plan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-bg" id="plan-modal">
    <div class="modal" style="max-width:560px;">
        <h2 id="pm-title" style="font-size:18px;margin-bottom:14px;">New plan</h2>
        <form method="POST" id="plan-form" action="<?php echo e(route('admin.plans.store')); ?>">
            <?php echo csrf_field(); ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <label><span class="lbl">Plan name</span><input type="text" name="name" id="f-name" required></label>
                <label><span class="lbl">Code (unique)</span><input type="text" name="code" id="f-code" placeholder="GROWTH" required></label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <label><span class="lbl">Price ₹ (incl. GST)</span><input type="number" name="price" id="f-price" min="0" value="0" oninput="calcGst()" required></label>
                <label><span class="lbl">GST %</span><input type="number" name="gst_rate" id="f-gst" min="0" max="50" value="18" oninput="calcGst()" required></label>
                <label><span class="lbl">Credits / mo</span><input type="number" name="credits" id="f-credits" min="0" value="0" required></label>
            </div>
            <div id="gst-preview" style="background:var(--teal-soft);color:var(--teal-ink);border-radius:10px;padding:9px 12px;font-size:12.5px;margin-top:8px;font-weight:600;"></div>

            <label><span class="lbl">Features (one per line)</span><textarea name="features" id="f-features" rows="3" style="width:100%;padding:10px 12px;border-radius:9px;border:1px solid var(--line);font-family:inherit;font-size:14px;background:#fcfcfa;"></textarea></label>

            <span class="lbl" style="margin-top:12px;">Module access (unchecked = all allowed)</span>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:4px;">
                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:var(--ink);margin:0;">
                        <input type="checkbox" name="permissions[]" value="<?php echo e($key); ?>" class="perm-cb" style="width:16px;height:16px;"> <?php echo e($label); ?>

                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;">
                <label><span class="lbl">Sort order</span><input type="number" name="sort" id="f-sort" min="0" value="0"></label>
                <label style="display:flex;align-items:center;gap:8px;margin-top:28px;font-size:13px;"><input type="checkbox" name="is_active" id="f-active" value="1" checked style="width:16px;height:16px;"> Active (visible to users)</label>
            </div>

            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('plan-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="pm-submit">Create plan</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const pform = document.getElementById('plan-form');
const createUrl = "<?php echo e(route('admin.plans.store')); ?>";
function calcGst(){
    const price = +document.getElementById('f-price').value || 0;
    const gst = +document.getElementById('f-gst').value || 0;
    const base = price / (1 + gst/100);
    const gstAmt = price - base;
    document.getElementById('gst-preview').textContent =
        `Base ₹${base.toFixed(2)}  +  ${gst}% GST ₹${gstAmt.toFixed(2)}  =  ₹${price.toFixed(2)} total`;
}
function setPerms(list){
    document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = (list||[]).includes(cb.value));
}
function openCreate(){
    document.getElementById('pm-title').textContent = 'New plan';
    document.getElementById('pm-submit').textContent = 'Create plan';
    pform.action = createUrl;
    document.getElementById('f-name').value=''; document.getElementById('f-code').value='';
    document.getElementById('f-price').value=0; document.getElementById('f-gst').value=18;
    document.getElementById('f-credits').value=0; document.getElementById('f-features').value='';
    document.getElementById('f-sort').value=0; document.getElementById('f-active').checked=true;
    setPerms([]); calcGst();
    document.getElementById('plan-modal').classList.add('open');
}
function openEdit(p){
    document.getElementById('pm-title').textContent = 'Edit plan';
    document.getElementById('pm-submit').textContent = 'Save changes';
    pform.action = "<?php echo e(url('admin/plans')); ?>/" + p.id;
    document.getElementById('f-name').value=p.name; document.getElementById('f-code').value=p.code;
    document.getElementById('f-price').value=p.price; document.getElementById('f-gst').value=p.gst_rate;
    document.getElementById('f-credits').value=p.credits;
    document.getElementById('f-features').value=(p.features||[]).join('\n');
    document.getElementById('f-sort').value=p.sort||0; document.getElementById('f-active').checked=!!p.is_active;
    setPerms(p.permissions||[]); calcGst();
    document.getElementById('plan-modal').classList.add('open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/admin/plans.blade.php ENDPATH**/ ?>