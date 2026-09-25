<?php $__env->startSection('title', 'Tally Export'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <h1>Tally Export</h1>
        <p>Download a month of invoices and receipts as a Tally XML file, then import it in TallyPrime under <strong>Gateway of Tally → Import Data</strong>.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px;">
    
    <div class="card">
        <div style="font-size:16px;font-weight:700;margin-bottom:14px;">What to export</div>
        <form method="GET" action="<?php echo e(route('tally-export.download')); ?>" id="tally-form">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <label><span class="lbl" style="margin-top:0;">From</span><input type="date" name="from" value="<?php echo e(now()->startOfMonth()->format('Y-m-d')); ?>"></label>
                <label><span class="lbl" style="margin-top:0;">To</span><input type="date" name="to" value="<?php echo e(now()->endOfMonth()->format('Y-m-d')); ?>"></label>
            </div>

            <div style="margin-top:18px;">
                <span class="lbl" style="margin-top:0;">Invoice status</span>
                <div style="display:flex;gap:16px;margin-top:6px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="status_sent" value="1" checked style="width:auto;accent-color:var(--teal);width:18px;height:18px;">
                        <span style="font-size:13.5px;font-weight:500;">Sent</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="status_paid" value="1" checked style="width:auto;accent-color:var(--teal);width:18px;height:18px;">
                        <span style="font-size:13.5px;font-weight:500;">Paid</span>
                    </label>
                </div>
                <div style="font-size:11.5px;color:var(--muted);margin-top:5px;">Drafts and cancelled invoices are never exported.</div>
            </div>

            <div style="border-top:1px solid var(--line);margin-top:16px;padding-top:14px;display:flex;flex-direction:column;gap:12px;">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="include_ledgers" value="1" checked style="width:auto;accent-color:var(--teal);width:18px;height:18px;margin-top:2px;">
                    <div>
                        <div style="font-size:13.5px;font-weight:600;">Include customer ledgers</div>
                        <div style="font-size:11.5px;color:var(--muted);">Creates the party ledgers with GSTIN and state. Switch off if your Tally already has them.</div>
                    </div>
                </label>
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="skip_exported" value="1" style="width:auto;accent-color:var(--teal);width:18px;height:18px;margin-top:2px;">
                    <div>
                        <div style="font-size:13.5px;font-weight:600;">Skip anything already exported</div>
                        <div style="font-size:11.5px;color:var(--muted);">Tally cannot tell a repeat import from a new one, so this is what stops duplicate vouchers.</div>
                    </div>
                </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="submit" class="btn" style="flex:1;justify-content:center;padding:12px 20px;">Download Tally XML</button>
            </div>
        </form>
    </div>

    
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div style="font-size:16px;font-weight:700;margin-bottom:14px;">In this file</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                <div style="background:var(--teal-soft);border-radius:12px;padding:16px;">
                    <div style="font-size:12px;color:var(--teal-ink);">Invoices</div>
                    <div style="font-size:28px;font-weight:700;color:var(--teal-ink);margin-top:4px;"><?php echo e($count); ?></div>
                </div>
                <div style="background:#f3f1ea;border-radius:12px;padding:16px;">
                    <div style="font-size:12px;color:var(--muted);">Receipts</div>
                    <div style="font-size:28px;font-weight:700;margin-top:4px;">0</div>
                </div>
                <div style="background:#f3f1ea;border-radius:12px;padding:16px;">
                    <div style="font-size:12px;color:var(--muted);">Ledgers</div>
                    <div style="font-size:28px;font-weight:700;margin-top:4px;"><?php echo e($count); ?></div>
                </div>
            </div>
            <?php if($count === 0): ?>
                <div style="font-size:13px;color:var(--muted);margin-top:10px;">Nothing to export for this period.</div>
            <?php endif; ?>
        </div>

        <?php if(!$settings->state): ?>
            <div style="background:var(--rose-soft);border:1px solid #e8c0c4;border-radius:12px;padding:14px 18px;margin-bottom:16px;">
                <div style="font-size:14px;font-weight:700;color:var(--rose);margin-bottom:4px;">Your business state is not set</div>
                <div style="font-size:13px;color:#6d3038;line-height:1.5;">GST is split into CGST+SGST or IGST by comparing your state with the customer's. Set your state under Billing Settings before exporting.</div>
                <a href="<?php echo e(route('billing-settings')); ?>" class="btn btn-ghost" style="margin-top:10px;padding:8px 14px;font-size:12.5px;">Open Billing Settings</a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">How to import</div>
            <ol style="font-size:13px;color:#3a4a45;line-height:1.8;padding-left:18px;">
                <li>Open <strong>TallyPrime</strong> or <strong>Tally.ERP 9</strong></li>
                <li>Go to <strong>Gateway of Tally → Import Data → Vouchers</strong></li>
                <li>Select the downloaded <code>.xml</code> file</li>
                <li>Tally will create any missing ledgers during import</li>
            </ol>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\invoicing\tally-export.blade.php ENDPATH**/ ?>