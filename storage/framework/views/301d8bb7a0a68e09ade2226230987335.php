<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice INV-<?php echo e(str_pad($payment->id, 5, '0', STR_PAD_LEFT)); ?> — ReviewFlow</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#eef1f8;color:#1b2437;padding:28px;-webkit-font-smoothing:antialiased;}
.sheet{max-width:760px;margin:0 auto;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(20,30,60,.1);overflow:hidden;}
.head{background:linear-gradient(135deg,#4c6fff,#8b5cf6);color:#fff;padding:30px 36px;display:flex;justify-content:space-between;align-items:flex-start;}
.brand{display:flex;align-items:center;gap:12px;}
.brand .logo{width:46px;height:46px;border-radius:12px;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.35);display:grid;place-items:center;font-weight:800;font-size:22px;}
.brand .bn{font-size:22px;font-weight:800;line-height:1;}
.brand .bs{font-size:12px;opacity:.85;margin-top:3px;}
.inv-meta{text-align:right;}
.inv-meta .t{font-size:13px;opacity:.85;text-transform:uppercase;letter-spacing:.08em;}
.inv-meta .n{font-size:20px;font-weight:800;margin-top:2px;}
.body{padding:30px 36px;}
.parties{display:flex;justify-content:space-between;gap:24px;margin-bottom:26px;flex-wrap:wrap;}
.party .l{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#8b93a7;font-weight:700;margin-bottom:5px;}
.party .v{font-size:14px;font-weight:600;line-height:1.5;}
.party .v span{font-weight:400;color:#5a6478;}
table{width:100%;border-collapse:collapse;margin-bottom:20px;}
th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#8b93a7;padding:10px 12px;border-bottom:2px solid #eef1f8;}
td{padding:12px;border-bottom:1px solid #eef1f8;font-size:14px;}
.right{text-align:right;}
.totals{margin-left:auto;width:280px;}
.totals .row{display:flex;justify-content:space-between;padding:7px 0;font-size:14px;}
.totals .row.grand{border-top:2px solid #1b2437;margin-top:6px;padding-top:12px;font-size:18px;font-weight:800;}
.totals .row .muted{color:#8b93a7;}
.status{display:inline-block;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;background:#dcfce7;color:#16a34a;}
.foot{padding:20px 36px 30px;border-top:1px solid #eef1f8;font-size:12px;color:#8b93a7;text-align:center;line-height:1.7;}
.bar{padding:14px 36px;background:#fafbff;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #eef1f8;}
.btn{padding:10px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,#4c6fff,#8b5cf6);color:#fff;font-weight:700;font-size:13.5px;cursor:pointer;font-family:inherit;}
.btn.ghost{background:#fff;border:1px solid #e4e7f0;color:#1b2437;}
@media print{ body{background:#fff;padding:0;} .sheet{box-shadow:none;border-radius:0;max-width:100%;} .bar{display:none;} }
</style>
</head>
<body>
<div class="sheet">
    <div class="head">
        <div class="brand">
            <div class="logo">R</div>
            <div><div class="bn">ReviewFlow</div><div class="bs">AI-Powered Local Marketing</div></div>
        </div>
        <div class="inv-meta">
            <div class="t">Invoice</div>
            <div class="n">INV-<?php echo e(str_pad($payment->id, 5, '0', STR_PAD_LEFT)); ?></div>
        </div>
    </div>

    <div class="body">
        <div class="parties">
            <div class="party">
                <div class="l">Billed To</div>
                <div class="v"><?php echo e($client->name ?? $agency->name ?? 'Customer'); ?><br>
                    <span><?php echo e($client->email ?? ''); ?></span></div>
            </div>
            <div class="party" style="text-align:right;">
                <div class="l">Invoice Date</div>
                <div class="v"><?php echo e($payment->created_at->format('d M Y')); ?><br>
                    <span>Payment ID: <?php echo e($payment->razorpay_payment_id ?? '—'); ?></span></div>
            </div>
        </div>

        <table>
            <thead><tr><th>Description</th><th class="right">Qty</th><th class="right">Amount</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong><?php echo e($plan->name ?? $payment->plan); ?> Plan</strong><br>
                        <span style="color:#8b93a7;font-size:12.5px;">Monthly subscription<?php echo e($plan ? ' · '.number_format($plan->credits).' AI credits' : ''); ?></span></td>
                    <td class="right">1</td>
                    <td class="right">₹<?php echo e(number_format($base, 2)); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><span class="muted">Subtotal</span><span>₹<?php echo e(number_format($base, 2)); ?></span></div>
            <div class="row"><span class="muted">GST (<?php echo e($gstRate); ?>%)</span><span>₹<?php echo e(number_format($gst, 2)); ?></span></div>
            <div class="row grand"><span>Total</span><span>₹<?php echo e(number_format($total, 2)); ?></span></div>
            <div class="row" style="margin-top:10px;"><span class="muted">Status</span><span class="status"><?php echo e($payment->status); ?></span></div>
        </div>
    </div>

    <div class="bar">
        <button class="btn ghost" onclick="window.close()">Close</button>
        <button class="btn" onclick="window.print()">⬇ Download PDF</button>
    </div>

    <div class="foot">
        This is a computer-generated invoice and does not require a signature.<br>
        <?php echo e($agency->name ?? 'ReviewFlow'); ?> · Thank you for your business.
    </div>
</div>
</body>
</html>
<?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\invoice.blade.php ENDPATH**/ ?>