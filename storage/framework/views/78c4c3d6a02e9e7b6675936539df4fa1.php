<?php $__env->startSection('title', 'Categories'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Categories</h1><p>How your services are grouped in the invoice picker.</p></div>
    <button class="btn" onclick="document.getElementById('cat-modal').classList.add('open')">+ Add Category</button>
</div>

<?php if($categories->isEmpty()): ?>
    <div class="card"><div class="empty">No categories yet. Add one here, or type a category straight into a service.</div></div>
<?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card" style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;">
                <div><strong style="font-size:14px;"><?php echo e($cat->name); ?></strong><div style="font-size:11.5px;color:var(--muted);margin-top:2px;"><?php echo e($cat->services_count); ?> service(s)</div></div>
                <form method="POST" action="<?php echo e(route('service-categories.destroy', $cat)); ?>" onsubmit="return confirm('Delete this category?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);">🗑</button>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div class="modal-bg" id="cat-modal">
    <div class="modal">
        <h2>Add category</h2>
        <form method="POST" action="<?php echo e(route('service-categories.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Name</span><input type="text" name="name" required placeholder="e.g. SEO, Ads, Design"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('cat-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add category</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\invoicing\categories.blade.php ENDPATH**/ ?>