<?php $__env->startSection('title', 'New Invoice'); ?>
<?php $__env->startSection('content'); ?>
<div style="margin-bottom:6px;"><a href="<?php echo e(route('invoices')); ?>" style="font-size:13px;color:var(--muted);">← All invoices</a></div>
<div class="page-head">
    <div><h1>New Invoice</h1><p>Next number will be <?php echo e($settings->invoice_prefix); ?>-<?php echo e(str_pad($settings->next_invoice_number,4,'0',STR_PAD_LEFT)); ?>.</p></div>
</div>

<?php if($clients->isEmpty()): ?>
    <div class="alert info">Add a client first under <a href="<?php echo e(route('customers')); ?>">Customers</a> before creating an invoice.</div>
<?php else: ?>
<form method="POST" action="<?php echo e(route('invoices.store')); ?>" id="inv-form">
    <?php echo csrf_field(); ?>
    <div class="card" style="margin-bottom:14px;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
            <label><span class="lbl">Customer</span>
                <select name="client_id" required>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label><span class="lbl">Issue date</span><input type="date" name="issue_date" value="<?php echo e(now()->format('Y-m-d')); ?>" required></label>
            <label><span class="lbl">Due date</span><input type="date" name="due_date" value="<?php echo e(now()->addDays(7)->format('Y-m-d')); ?>"></label>
        </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <strong>Line items</strong>
            <button type="button" class="btn btn-ghost" style="padding:6px 12px;font-size:12.5px;" onclick="addRow()">+ Add line</button>
        </div>
        <div id="item-rows"></div>
        <div style="display:flex;justify-content:flex-end;gap:24px;border-top:1px solid var(--line);padding-top:12px;margin-top:8px;font-size:13.5px;">
            <div>Subtotal: <strong id="t-subtotal">₹0.00</strong></div>
            <div>GST: <strong id="t-gst">₹0.00</strong></div>
            <div>Total: <strong id="t-total" style="color:var(--teal);">₹0.00</strong></div>
        </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
        <label><span class="lbl">Notes (optional)</span><textarea name="notes" rows="2" placeholder="Payment terms, thank-you note, etc."></textarea></label>
    </div>

    <button type="submit" class="btn">Save invoice as draft</button>
</form>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
const services = <?php echo json_encode($serviceOptions, 15, 512) ?>;
let rowIndex = 0;

function addRow(){
    const i = rowIndex++;
    const div = document.createElement('div');
    div.style.cssText = 'display:grid;grid-template-columns:2fr 90px 110px 80px 110px 30px;gap:8px;margin-bottom:8px;align-items:center;';
    div.innerHTML = `
        <select onchange="fillService(${i}, this)" style="padding:8px;">
            <option value="">Custom line…</option>
            ${services.map(s => `<option value="${s.id}">${s.name}</option>`).join('')}
        </select>
        <input type="text" name="items[${i}][description]" placeholder="Description" required style="padding:8px;">
        <input type="number" name="items[${i}][quantity]" value="1" min="0.01" step="0.01" oninput="recalc()" style="padding:8px;">
        <input type="number" name="items[${i}][unit_price]" value="0" min="0" step="0.01" oninput="recalc()" style="padding:8px;">
        <input type="number" name="items[${i}][gst_percent]" value="18" min="0" max="100" step="0.01" oninput="recalc()" style="padding:8px;">
        <button type="button" onclick="this.parentElement.remove();recalc()" style="background:none;border:none;color:var(--rose);cursor:pointer;font-size:16px;">×</button>
    `;
    document.getElementById('item-rows').appendChild(div);
}

function fillService(i, sel){
    const row = sel.closest('div');
    const svc = services.find(s => s.id == sel.value);
    if (svc) {
        row.querySelector(`[name="items[${i}][description]"]`).value = svc.name;
        row.querySelector(`[name="items[${i}][unit_price]"]`).value = svc.price;
        row.querySelector(`[name="items[${i}][gst_percent]"]`).value = svc.gst;
    }
    recalc();
}

function recalc(){
    let subtotal = 0, gst = 0;
    document.querySelectorAll('#item-rows > div').forEach(row => {
        const qty = parseFloat(row.querySelector('[name$="[quantity]"]')?.value) || 0;
        const price = parseFloat(row.querySelector('[name$="[unit_price]"]')?.value) || 0;
        const gstPct = parseFloat(row.querySelector('[name$="[gst_percent]"]')?.value) || 0;
        const base = qty * price;
        subtotal += base;
        gst += base * gstPct / 100;
    });
    document.getElementById('t-subtotal').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('t-gst').textContent = '₹' + gst.toFixed(2);
    document.getElementById('t-total').textContent = '₹' + (subtotal + gst).toFixed(2);
}

addRow(); // start with one row
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\invoicing\invoice-form.blade.php ENDPATH**/ ?>