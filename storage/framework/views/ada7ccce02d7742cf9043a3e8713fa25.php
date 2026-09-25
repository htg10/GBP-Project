<?php $__env->startSection('title', 'Clients'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Clients</h1><p>The businesses you manage. Everything else attaches to a client.</p></div>
    <button class="btn" onclick="openNew()">+ Add client</button>
</div>

<?php if($clients->isEmpty()): ?>
    <div class="card"><div class="empty">No clients yet. Click “Add client” to create your first one.</div></div>
<?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div style="display:flex;gap:11px;align-items:center;">
                        <div class="avatar" style="background:var(--teal-soft);color:var(--teal-ink);"><?php echo e(strtoupper(substr($c->name,0,1))); ?></div>
                        <div>
                            <strong style="font-size:14.5px;"><?php echo e($c->name); ?></strong>
                            <div style="font-size:12px;color:var(--muted);"><?php echo e($c->industry ?: 'No industry set'); ?></div>
                        </div>
                    </div>
                    <button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;" onclick='openEdit(<?php echo json_encode($c, 15, 512) ?>)'>✎</button>
                </div>

                <div style="display:flex;gap:16px;margin:14px 0;padding:11px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);">
                    <div><div style="font-size:18px;font-weight:700;"><?php echo e($c->locations_count); ?></div><div style="font-size:11px;color:var(--muted);">Locations</div></div>
                    <div><div style="font-size:18px;font-weight:700;"><?php echo e($c->leads_count); ?></div><div style="font-size:11px;color:var(--muted);">Leads</div></div>
                </div>

                <?php if($c->phone || $c->email): ?>
                    <div style="font-size:12.5px;color:var(--muted);line-height:1.7;">
                        <?php if($c->phone): ?><div>✆ <?php echo e($c->phone); ?></div><?php endif; ?>
                        <?php if($c->email): ?><div>✉ <?php echo e($c->email); ?></div><?php endif; ?>
                    </div>
                <?php endif; ?>

                <a href="<?php echo e(route('clients.show', $c)); ?>" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:13px;">Manage →</a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div class="modal-bg" id="client-modal">
    <div class="modal">
        <h2 id="cm-title">Add client</h2>
        <form method="POST" id="client-form" action="<?php echo e(route('clients.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Business name</span><input type="text" name="name" id="c-name" required></label>
            <label><span class="lbl">Industry</span><input type="text" name="industry" id="c-industry" placeholder="e.g. Dental, Salon, Gym"></label>
            <label><span class="lbl">Phone</span><input type="text" name="phone" id="c-phone"></label>
            <label><span class="lbl">Email</span><input type="email" name="email" id="c-email"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('client-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="cm-submit">Add client</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const cForm = document.getElementById('client-form');
const cStore = "<?php echo e(route('clients.store')); ?>";
function openNew(){
    cForm.action = cStore;
    document.getElementById('cm-title').textContent = 'Add client';
    document.getElementById('cm-submit').textContent = 'Add client';
    ['name','industry','phone','email'].forEach(f => document.getElementById('c-'+f).value = '');
    document.getElementById('client-modal').classList.add('open');
}
function openEdit(c){
    cForm.action = "<?php echo e(url('clients')); ?>/" + c.id;
    document.getElementById('cm-title').textContent = 'Edit client';
    document.getElementById('cm-submit').textContent = 'Save changes';
    document.getElementById('c-name').value = c.name || '';
    document.getElementById('c-industry').value = c.industry || '';
    document.getElementById('c-phone').value = c.phone || '';
    document.getElementById('c-email').value = c.email || '';
    document.getElementById('client-modal').classList.add('open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\clients.blade.php ENDPATH**/ ?>