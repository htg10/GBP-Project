<?php $__env->startSection('title', 'Expenses'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Expenses</h1><p>Money going out — rent, stock, salaries, utilities.</p></div>
    <button class="btn" onclick="document.getElementById('exp-modal').classList.add('open')">+ Add Expense</button>
</div>

<div class="stats" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
    <div class="stat"><div>↓</div><div class="v" style="color:var(--rose);">₹<?php echo e(number_format($stats['spent'],2)); ?></div><div class="l">Spent this month</div></div>
    <div class="stat"><div>▤</div><div class="v"><?php echo e($stats['count']); ?></div><div class="l">Entries <?php echo e($month ? 'in view' : '(all time)'); ?></div></div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <form method="GET" style="display:flex;gap:8px;">
            <input type="month" name="month" value="<?php echo e($month); ?>" onchange="this.form.submit()">
            <?php if($month): ?><a href="<?php echo e(route('expenses')); ?>" class="btn btn-ghost" style="padding:8px 12px;font-size:12.5px;">All time</a><?php endif; ?>
        </form>
    </div>
    <?php if($expenses->isEmpty()): ?>
        <div class="empty">No expenses logged<?php echo e($month ? ' for this month' : ''); ?>.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Date</th><th>Expense</th><th>Category</th><th>Method</th><th style="text-align:right;">Amount</th><th>Actions</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($e->date->format('d M Y')); ?></td>
                        <td><?php echo e($e->description); ?></td>
                        <td><?php echo e($e->category ?: '—'); ?></td>
                        <td><span class="badge gray"><?php echo e($e->method); ?></span></td>
                        <td style="text-align:right;color:var(--rose);">₹<?php echo e(number_format($e->amount,2)); ?></td>
                        <td>
                            <form method="POST" action="<?php echo e(route('expenses.destroy', $e)); ?>" onsubmit="return confirm('Delete this expense?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);">🗑</button></form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<div class="modal-bg" id="exp-modal">
    <div class="modal">
        <h2>Add expense</h2>
        <form method="POST" action="<?php echo e(route('expenses.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Date</span><input type="date" name="date" value="<?php echo e(now()->format('Y-m-d')); ?>" required></label>
            <label><span class="lbl">Description</span><input type="text" name="description" required placeholder="e.g. Office rent"></label>
            <label><span class="lbl">Category</span><input type="text" name="category" placeholder="e.g. Rent, Salaries, Utilities"></label>
            <label><span class="lbl">Method</span>
                <select name="method"><option value="BANK">Bank transfer</option><option value="CASH">Cash</option><option value="UPI">UPI</option><option value="CARD">Card</option><option value="OTHER">Other</option></select>
            </label>
            <label><span class="lbl">Amount (₹)</span><input type="number" step="0.01" name="amount" required></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('exp-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add expense</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\invoicing\expenses.blade.php ENDPATH**/ ?>