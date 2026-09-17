<?php $__env->startSection('title', 'Leads CRM'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Leads CRM</h1><p>Capture leads and move them through your sales pipeline.</p></div>
    <button class="btn" onclick="document.getElementById('lead-modal').classList.add('open')">+ Add lead</button>
</div>

<?php if($clients->isEmpty()): ?><div class="alert info">No clients yet. Run the seeder to create a demo client.</div><?php endif; ?>

<?php $labels = ['NEW'=>'New','CONTACTED'=>'Contacted','FOLLOW_UP'=>'Follow Up','APPOINTMENT'=>'Appointment','CONVERTED'=>'Converted','LOST'=>'Lost']; ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;align-items:start;">
    <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $items = $leads[$stage] ?? collect(); ?>
        <div class="card" style="padding:12px;min-height:120px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding:0 2px;">
                <strong style="font-size:13px;"><?php echo e($labels[$stage]); ?></strong>
                <span style="font-size:11px;color:var(--muted);background:#f0eee6;border-radius:999px;padding:1px 8px;"><?php echo e($items->count()); ?></span>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div style="background:#fcfcfa;border:1px solid var(--line);border-radius:10px;padding:11px;">
                        <div style="font-weight:600;font-size:13.5px;margin-bottom:6px;"><?php echo e($lead->name); ?></div>
                        <?php if($lead->phone): ?><div style="font-size:11.5px;color:var(--muted);">✆ <?php echo e($lead->phone); ?></div><?php endif; ?>
                        <?php if($lead->email): ?><div style="font-size:11.5px;color:var(--muted);margin-bottom:6px;">✉ <?php echo e($lead->email); ?></div><?php endif; ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
                            <span class="badge gray"><?php echo e(ucfirst(strtolower(str_replace('_',' ',$lead->source)))); ?></span>
                            <div style="display:flex;gap:3px;">
                                <?php $idx = array_search($stage, $stages); ?>
                                <?php if($idx > 0): ?>
                                    <form method="POST" action="<?php echo e(route('leads.move', $lead)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="stage" value="<?php echo e($stages[$idx-1]); ?>"><button style="width:24px;height:24px;border-radius:6px;border:1px solid var(--line);background:#fff;cursor:pointer;">‹</button></form>
                                <?php endif; ?>
                                <?php if($idx < count($stages)-1): ?>
                                    <form method="POST" action="<?php echo e(route('leads.move', $lead)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="stage" value="<?php echo e($stages[$idx+1]); ?>"><button style="width:24px;height:24px;border-radius:6px;border:1px solid var(--line);background:#fff;cursor:pointer;color:var(--teal-ink);">›</button></form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div style="font-size:11.5px;color:#b8b4a8;text-align:center;padding:10px 0;">—</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="modal-bg" id="lead-modal">
    <div class="modal">
        <h2>Add lead</h2>
        <form method="POST" action="<?php echo e(route('leads.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Client</span><select name="client_id"><?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label><span class="lbl">Name</span><input type="text" name="name" required></label>
            <label><span class="lbl">Phone</span><input type="text" name="phone"></label>
            <label><span class="lbl">Email</span><input type="email" name="email"></label>
            <label><span class="lbl">Source</span><select name="source">
                <option value="WEBSITE">Website</option><option value="FACEBOOK">Facebook</option><option value="INSTAGRAM">Instagram</option><option value="WHATSAPP">WhatsApp</option><option value="GOOGLE_FORM">Google Form</option>
            </select></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('lead-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add lead</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\leads.blade.php ENDPATH**/ ?>