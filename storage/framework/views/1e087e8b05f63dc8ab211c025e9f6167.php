<?php $__env->startSection('title', 'Invoices'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Invoices</h1><p>Create an invoice and send it straight to a client.</p></div>
    <a href="<?php echo e(route('invoices.create')); ?>" class="btn">+ New Invoice</a>
</div>

<div class="stats" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
    <div class="stat"><div>⏱</div><div class="v">₹<?php echo e(number_format($stats['outstanding'],2)); ?></div><div class="l">Outstanding</div></div>
    <div class="stat accent"><div>✓</div><div class="v">₹<?php echo e(number_format($stats['paid_this_month'],2)); ?></div><div class="l">Paid this month</div></div>
    <div class="stat"><div>📄</div><div class="v"><?php echo e($stats['drafts']); ?></div><div class="l">Drafts</div></div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);">
        <form method="GET">
            <input type="search" name="q" value="<?php echo e($search); ?>" placeholder="🔍 Search invoice # or client…" style="max-width:320px;">
        </form>
    </div>
    <?php if($invoices->isEmpty()): ?>
        <div class="empty">No invoices yet. Click "New Invoice" to create your first one.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Number</th><th>Customer</th><th>Date</th><th style="text-align:right;">Total</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $badge = ['DRAFT'=>'gray','SENT'=>'amber','PAID'=>'teal','OVERDUE'=>'rose','CANCELLED'=>'gray'][$inv->status] ?? 'gray'; ?>
                    <tr>
                        <td><a href="<?php echo e(route('invoices.show', $inv)); ?>" style="font-weight:600;color:var(--teal-ink);"><?php echo e($inv->invoice_number); ?></a></td>
                        <td><?php echo e($inv->client->name); ?></td>
                        <td><?php echo e($inv->issue_date->format('d M Y')); ?></td>
                        <td style="text-align:right;">₹<?php echo e(number_format($inv->total,2)); ?></td>
                        <td><?php echo e($inv->due_date?->format('d M Y') ?: '—'); ?></td>
                        <td><span class="badge <?php echo e($badge); ?>"><?php echo e($inv->status); ?></span></td>
                        <td><a href="<?php echo e(route('invoices.show', $inv)); ?>" style="font-size:12.5px;color:var(--teal);font-weight:600;">View →</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\invoicing\invoices.blade.php ENDPATH**/ ?>