<?php $__env->startSection('title', $invoice->invoice_number); ?>
<?php $__env->startPush('head'); ?>
<style>
@media print {
    .sidebar, .topbar, .page-head, .no-print { display: none !important; }
    .main { padding: 0 !important; max-width: 100% !important; }
    body { background: #fff !important; }
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div style="margin-bottom:6px;" class="no-print"><a href="<?php echo e(route('invoices')); ?>" style="font-size:13px;color:var(--muted);">← All invoices</a></div>
<div class="page-head no-print">
    <div><h1><?php echo e($invoice->invoice_number); ?></h1><p><?php echo e($invoice->client->name); ?></p></div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-ghost" onclick="window.print()">🖨 Print / Save as PDF</button>
        <?php if($invoice->status === 'DRAFT'): ?>
            <form method="POST" action="<?php echo e(route('invoices.mark-sent', $invoice)); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost">Mark as sent</button></form>
            <form method="POST" action="<?php echo e(route('invoices.destroy', $invoice)); ?>" onsubmit="return confirm('Delete this draft invoice?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-ghost" style="color:var(--rose);">Delete</button></form>
        <?php endif; ?>
        <?php if(in_array($invoice->status, ['SENT','OVERDUE'])): ?>
            <form method="POST" action="<?php echo e(route('invoices.mark-paid', $invoice)); ?>"><?php echo csrf_field(); ?><button class="btn">Mark as paid</button></form>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="max-width:760px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:20px;border-bottom:2px solid var(--ink);margin-bottom:20px;">
        <div>
            <h2 style="font-size:20px;"><?php echo e($settings->company_name ?: 'Your Company'); ?></h2>
            <?php if($settings->address): ?><div style="font-size:12.5px;color:var(--muted);margin-top:4px;"><?php echo e($settings->address); ?></div><?php endif; ?>
            <?php if($settings->gstin): ?><div style="font-size:12.5px;color:var(--muted);">GSTIN: <?php echo e($settings->gstin); ?></div><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <div style="font-size:22px;font-weight:700;color:var(--teal);">INVOICE</div>
            <div style="font-size:13px;margin-top:4px;"><?php echo e($invoice->invoice_number); ?></div>
            <span class="badge <?php echo e(['DRAFT'=>'gray','SENT'=>'amber','PAID'=>'teal','OVERDUE'=>'rose'][$invoice->status] ?? 'gray'); ?>" style="margin-top:6px;"><?php echo e($invoice->status); ?></span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <div>
            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:6px;">Billed to</div>
            <div style="font-weight:600;font-size:14px;"><?php echo e($invoice->client->name); ?></div>
            <?php if($invoice->client->billing_address): ?><div style="font-size:12.5px;color:var(--muted);"><?php echo e($invoice->client->billing_address); ?></div><?php endif; ?>
            <?php if($invoice->client->gstin): ?><div style="font-size:12.5px;color:var(--muted);">GSTIN: <?php echo e($invoice->client->gstin); ?></div><?php endif; ?>
            <?php if($invoice->client->email): ?><div style="font-size:12.5px;color:var(--muted);"><?php echo e($invoice->client->email); ?></div><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <div style="font-size:12.5px;color:var(--muted);">Issue date: <strong style="color:var(--ink);"><?php echo e($invoice->issue_date->format('d M Y')); ?></strong></div>
            <div style="font-size:12.5px;color:var(--muted);margin-top:4px;">Due date: <strong style="color:var(--ink);"><?php echo e($invoice->due_date?->format('d M Y') ?: '—'); ?></strong></div>
        </div>
    </div>

    <table style="margin-bottom:20px;">
        <thead><tr><th>Description</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Rate</th><th style="text-align:right;">GST%</th><th style="text-align:right;">Amount</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->description); ?></td>
                    <td style="text-align:right;"><?php echo e(rtrim(rtrim(number_format($item->quantity,2),'0'),'.')); ?></td>
                    <td style="text-align:right;">₹<?php echo e(number_format($item->unit_price,2)); ?></td>
                    <td style="text-align:right;"><?php echo e(number_format($item->gst_percent,1)); ?>%</td>
                    <td style="text-align:right;">₹<?php echo e(number_format($item->amount,2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div style="display:flex;justify-content:flex-end;">
        <div style="width:240px;">
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;"><span>Subtotal</span><span>₹<?php echo e(number_format($invoice->subtotal,2)); ?></span></div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;"><span>GST</span><span>₹<?php echo e(number_format($invoice->gst_total,2)); ?></span></div>
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-top:1.5px solid var(--ink);font-size:15px;font-weight:700;"><span>Total</span><span>₹<?php echo e(number_format($invoice->total,2)); ?></span></div>
        </div>
    </div>

    <?php if($invoice->notes): ?>
        <div style="border-top:1px solid var(--line);margin-top:16px;padding-top:14px;font-size:12.5px;color:#3a4a45;"><?php echo e($invoice->notes); ?></div>
    <?php endif; ?>

    <?php if($settings->bank_name): ?>
        <div style="border-top:1px solid var(--line);margin-top:16px;padding-top:14px;font-size:12px;color:var(--muted);">
            Pay to: <?php echo e($settings->bank_name); ?> · A/C <?php echo e($settings->bank_account); ?> · IFSC <?php echo e($settings->ifsc); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\invoicing\invoice-show.blade.php ENDPATH**/ ?>