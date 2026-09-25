<?php $__env->startSection('title', 'Customers'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Customers</h1><p>The people and businesses you invoice.</p></div>
    <button class="btn" onclick="openNew()">+ Add Customer</button>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <?php if($customers->isEmpty()): ?>
        <div class="empty">No customers yet. Click "Add Customer" — this list is shared with your Clients page.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Invoices</th><th>Actions</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong><?php echo e($c->name); ?></strong><?php if($c->gstin): ?><div style="font-size:11px;color:var(--muted);">GSTIN: <?php echo e($c->gstin); ?></div><?php endif; ?></td>
                        <td><?php echo e($c->phone ?: '—'); ?></td>
                        <td><?php echo e($c->email ?: '—'); ?></td>
                        <td><?php echo e($c->invoices_count); ?></td>
                        <td><button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--muted);" onclick='openEdit(<?php echo json_encode($c, 15, 512) ?>)'>✎ Edit</button></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<div class="modal-bg" id="cust-modal">
    <div class="modal">
        <h2 id="cm-title">Add customer</h2>
        <form method="POST" id="cust-form">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Name</span><input type="text" name="name" id="c-name" required></label>
            <label><span class="lbl">Phone</span><input type="text" name="phone" id="c-phone"></label>
            <label><span class="lbl">Email</span><input type="email" name="email" id="c-email"></label>
            <label><span class="lbl">GSTIN (optional)</span><input type="text" name="gstin" id="c-gstin"></label>
            <label><span class="lbl">Billing address</span><input type="text" name="billing_address" id="c-address"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('cust-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="cm-submit">Add customer</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const custForm = document.getElementById('cust-form');
const custStore = "<?php echo e(route('customers.store')); ?>";
function openNew(){
    custForm.action = custStore;
    document.getElementById('cm-title').textContent = 'Add customer';
    document.getElementById('cm-submit').textContent = 'Add customer';
    ['name','phone','email','gstin','address'].forEach(f => document.getElementById('c-'+f).value = '');
    document.getElementById('cust-modal').classList.add('open');
}
function openEdit(c){
    custForm.action = "<?php echo e(url('customers')); ?>/" + c.id;
    document.getElementById('cm-title').textContent = 'Edit customer';
    document.getElementById('cm-submit').textContent = 'Save changes';
    document.getElementById('c-name').value = c.name || '';
    document.getElementById('c-phone').value = c.phone || '';
    document.getElementById('c-email').value = c.email || '';
    document.getElementById('c-gstin').value = c.gstin || '';
    document.getElementById('c-address').value = c.billing_address || '';
    document.getElementById('cust-modal').classList.add('open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\invoicing\customers.blade.php ENDPATH**/ ?>