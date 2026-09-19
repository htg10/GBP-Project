<?php $__env->startSection('title', 'Leads CRM'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $stageMeta = [
        'NEW'         => ['label' => 'New',          'clr' => '#4c6fff', 'ic' => '✦'],
        'CONTACTED'   => ['label' => 'Contacted',    'clr' => '#8b5cf6', 'ic' => '☎'],
        'FOLLOW_UP'   => ['label' => 'Follow Up',    'clr' => '#f59e0b', 'ic' => '↻'],
        'APPOINTMENT' => ['label' => 'Appointment',  'clr' => '#06b6d4', 'ic' => '📅'],
        'CONVERTED'   => ['label' => 'Converted',    'clr' => '#22c55e', 'ic' => '✓'],
        'LOST'        => ['label' => 'Lost',         'clr' => '#ef4757', 'ic' => '✕'],
    ];
    $srcIcon = ['FACEBOOK'=>'📘','INSTAGRAM'=>'📸','WEBSITE'=>'🌐','WHATSAPP'=>'💬','GOOGLE_FORM'=>'📄'];
    $allLeads = collect($leads)->flatten();
    $total = $allLeads->count();
    $converted = ($leads['CONVERTED'] ?? collect())->count();
    $active = $total - $converted - (($leads['LOST'] ?? collect())->count());
    $convRate = $total ? round($converted / $total * 100) : 0;
?>

<div class="page-head">
    <div><h1>Leads CRM</h1><p>Track every lead from first touch to conversion. Drag cards between stages.</p></div>
    <button class="btn" onclick="document.getElementById('lead-modal').classList.add('open')">+ Add Lead</button>
</div>


<div class="lead-kpis">
    <div class="lead-kpi"><div class="lk-ic" style="background:linear-gradient(135deg,#4c6fff,#6b8afd);">◉</div><div><div class="lk-v"><?php echo e($total); ?></div><div class="lk-l">Total Leads</div></div></div>
    <div class="lead-kpi"><div class="lk-ic" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">⚡</div><div><div class="lk-v"><?php echo e($active); ?></div><div class="lk-l">Active Pipeline</div></div></div>
    <div class="lead-kpi"><div class="lk-ic" style="background:linear-gradient(135deg,#22c55e,#4ade80);">✓</div><div><div class="lk-v"><?php echo e($converted); ?></div><div class="lk-l">Converted</div></div></div>
    <div class="lead-kpi"><div class="lk-ic" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa);">%</div><div><div class="lk-v"><?php echo e($convRate); ?>%</div><div class="lk-l">Conversion Rate</div></div></div>
</div>


<div class="kanban">
    <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $m = $stageMeta[$stage]; $col = $leads[$stage] ?? collect(); ?>
        <div class="kcol" data-stage="<?php echo e($stage); ?>" ondragover="event.preventDefault();this.classList.add('drop')" ondragleave="this.classList.remove('drop')" ondrop="dropLead(event, this)">
            <div class="kcol-head" style="--sc:<?php echo e($m['clr']); ?>;">
                <span class="kdot"></span> <?php echo e($m['label']); ?>

                <span class="kcount"><?php echo e($col->count()); ?></span>
            </div>
            <div class="kcol-body">
                <?php $__empty_1 = true; $__currentLoopData = $col; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="lead-card" draggable="true" ondragstart="dragLead(event, <?php echo e($lead->id); ?>)" style="--sc:<?php echo e($m['clr']); ?>;">
                        <div class="lc-top">
                            <div class="lc-av"><?php echo e(strtoupper(substr($lead->name,0,1))); ?></div>
                            <div style="min-width:0;">
                                <div class="lc-name"><?php echo e($lead->name); ?></div>
                                <div class="lc-client"><?php echo e($lead->client->name ?? ''); ?></div>
                            </div>
                            <span class="lc-src" title="<?php echo e(ucfirst(strtolower(str_replace('_',' ',$lead->source)))); ?>"><?php echo e($srcIcon[$lead->source] ?? '•'); ?></span>
                        </div>
                        <?php if($lead->phone || $lead->email): ?>
                        <div class="lc-meta">
                            <?php if($lead->phone): ?><span>✆ <?php echo e($lead->phone); ?></span><?php endif; ?>
                            <?php if($lead->email): ?><span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">✉ <?php echo e($lead->email); ?></span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <div class="lc-foot">
                            <span><?php echo e($lead->created_at->diffForHumans(null, true)); ?> ago</span>
                            <select class="lc-move" onchange="moveLead(<?php echo e($lead->id); ?>, this.value)">
                                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($s); ?>" <?php echo e($s === $stage ? 'selected' : ''); ?>><?php echo e($stageMeta[$s]['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="kempty">No leads</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="modal-bg" id="lead-modal">
    <div class="modal">
        <h2>Add Lead</h2>
        <form method="POST" action="<?php echo e(route('leads.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Client</span>
                <select name="client_id" required>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label><span class="lbl">Lead name</span><input type="text" name="name" required></label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <label><span class="lbl">Phone</span><input type="text" name="phone"></label>
                <label><span class="lbl">Email</span><input type="email" name="email"></label>
            </div>
            <label><span class="lbl">Source</span>
                <select name="source" required>
                    <option value="WEBSITE">🌐 Website</option>
                    <option value="FACEBOOK">📘 Facebook</option>
                    <option value="INSTAGRAM">📸 Instagram</option>
                    <option value="WHATSAPP">💬 WhatsApp</option>
                    <option value="GOOGLE_FORM">📄 Google Form</option>
                </select>
            </label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('lead-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add Lead</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('head'); ?>
<style>
    .main{max-width:1240px;}
    .lead-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
    .lead-kpi{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:var(--shadow);display:flex;align-items:center;gap:12px;}
    .lk-ic{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;color:#fff;font-size:18px;flex-shrink:0;}
    .lk-v{font-size:24px;font-weight:800;line-height:1;}
    .lk-l{font-size:12px;color:var(--muted);margin-top:3px;}
    .kanban{display:grid;grid-template-columns:repeat(6,minmax(210px,1fr));gap:12px;overflow-x:auto;padding-bottom:8px;}
    .kcol{background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:10px;min-height:200px;transition:background .12s,border-color .12s;}
    [data-theme="dark"] .kcol{background:#0f1622;}
    .kcol.drop{background:var(--teal-soft);border-color:var(--teal);}
    .kcol-head{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;padding:6px 8px 10px;}
    .kdot{width:9px;height:9px;border-radius:50%;background:var(--sc);}
    .kcount{margin-left:auto;background:var(--sc);color:#fff;font-size:11px;font-weight:700;min-width:20px;height:20px;border-radius:10px;display:grid;place-items:center;padding:0 6px;}
    .kcol-body{display:flex;flex-direction:column;gap:9px;}
    .lead-card{background:var(--card);border:1px solid var(--line);border-left:3px solid var(--sc);border-radius:11px;padding:11px 12px;box-shadow:0 1px 2px rgba(20,30,60,.05);cursor:grab;transition:box-shadow .12s,transform .12s;}
    .lead-card:hover{box-shadow:0 8px 20px rgba(20,30,60,.1);transform:translateY(-1px);}
    .lead-card:active{cursor:grabbing;}
    .lc-top{display:flex;align-items:center;gap:9px;}
    .lc-av{width:32px;height:32px;border-radius:50%;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-weight:700;font-size:13px;flex-shrink:0;}
    .lc-name{font-size:13.5px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .lc-client{font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .lc-src{margin-left:auto;font-size:14px;}
    .lc-meta{display:flex;flex-direction:column;gap:2px;font-size:11.5px;color:var(--muted);margin-top:8px;}
    .lc-foot{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:9px;padding-top:8px;border-top:1px solid var(--line);}
    .lc-foot span{font-size:11px;color:var(--muted);}
    .lc-move{font-size:11px;padding:3px 6px;border-radius:7px;width:auto;max-width:110px;}
    .kempty{text-align:center;color:var(--muted);font-size:12px;padding:20px 0;}
    @media (max-width:900px){ .lead-kpis{grid-template-columns:repeat(2,1fr);} }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
let draggedLead = null;
function dragLead(e, id){ draggedLead = id; e.dataTransfer.effectAllowed='move'; }
function dropLead(e, col){
    e.preventDefault(); col.classList.remove('drop');
    if(draggedLead) moveLead(draggedLead, col.dataset.stage);
    draggedLead = null;
}
function moveLead(id, stage){
    fetch("<?php echo e(url('leads')); ?>/"+id+"/move", {
        method:'POST',
        headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json','Accept':'application/json'},
        body:JSON.stringify({stage})
    }).then(()=>location.reload());
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\leads.blade.php ENDPATH**/ ?>