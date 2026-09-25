<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
    <div><h1 style="font-size:22px;font-weight:800;">Users</h1><p style="font-size:13px;color:var(--muted);margin-top:3px;">Manage platform users and assign plans.</p></div>
    <button class="btn" onclick="openModal()" style="gap:6px;">
        <span style="width:20px;height:20px;border-radius:6px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:14px;">+</span> New user
    </button>
</div>

<?php $labels=['SUPER_ADMIN'=>'Super Admin','CLIENT_OWNER'=>'Client']; ?>

<div class="au-grid">
    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $initials = strtoupper(substr($u->name,0,1));
            $isAdmin = $u->role === 'SUPER_ADMIN';
            $colors = ['#4c6fff','#22c55e','#f59e0b','#8b5cf6','#ef4757','#ec4899','#14b8a6','#f97316'];
            $bgClr = $colors[$u->id % count($colors)];
            $sub = $subscriptions[$u->agency_id] ?? null;
            $planName = $sub?->plan ?? null;
        ?>
        <div class="card au-card">
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div class="au-avatar" style="background:<?php echo e($bgClr); ?>;"><?php echo e($initials); ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <strong style="font-size:14.5px;"><?php echo e($u->name); ?></strong>
                        <span class="badge <?php echo e($isAdmin ? 'dark' : 'teal'); ?>" style="font-size:11px;"><?php echo e($labels[$u->role] ?? $u->role); ?></span>
                    </div>
                    <div style="font-size:12.5px;color:var(--muted);margin-top:2px;"><?php echo e($u->email); ?></div>
                </div>
            </div>

            <div class="au-meta">
                <div class="au-meta-item">
                    <span class="au-meta-label">Client</span>
                    <span class="au-meta-value"><?php echo e($u->client->name ?? ($isAdmin ? 'All agency' : '—')); ?></span>
                </div>
                <div class="au-meta-item">
                    <span class="au-meta-label">Plan</span>
                    <span class="au-meta-value">
                        <?php if($planName): ?>
                            <span style="color:var(--teal-ink);font-weight:600;"><?php echo e($planName); ?></span>
                        <?php else: ?>
                            <span style="color:var(--muted);">No plan</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="au-meta-item">
                    <span class="au-meta-label">Joined</span>
                    <span class="au-meta-value"><?php echo e($u->created_at?->format('d M Y')); ?></span>
                </div>
            </div>

            <div class="au-actions">
                <button class="btn btn-ghost au-act-btn" data-user="<?php echo e(json_encode($u->only('id','name','email','role','agency_id'))); ?>" data-plan="<?php echo e($planName); ?>" onclick="openEdit(JSON.parse(this.dataset.user), this.dataset.plan)">
                    ✎ Edit
                </button>
                <?php if(!$isAdmin): ?>
                <form method="POST" action="<?php echo e(route('admin.users.destroy', $u)); ?>" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn au-act-btn au-del-btn">🗑 Delete</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card" style="grid-column:1/-1;text-align:center;padding:40px;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:10px;">👥</div>
            No users yet. Create your first user above.
        </div>
    <?php endif; ?>
</div>


<div class="modal-bg" id="user-modal">
    <div class="modal" style="max-width:420px;">
        <h2 id="modal-title" style="font-size:18px;font-weight:700;margin-bottom:16px;">New user</h2>
        <form method="POST" id="user-form" action="<?php echo e(route('admin.users.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Name</span><input type="text" name="name" id="f-name" required></label>
            <label><span class="lbl">Email</span><input type="email" name="email" id="f-email" required></label>
            <label><span class="lbl" id="pw-label">Password</span><input type="password" name="password" id="f-password"></label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <label><span class="lbl">Role</span><select name="role" id="f-role">
                    <?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($val); ?>"><?php echo e($lbl); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select></label>
                <label><span class="lbl">Assign Plan</span><select name="plan_id" id="f-plan">
                    <option value="">— No plan —</option>
                    <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?> (₹<?php echo e(number_format($p->price)); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select></label>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('user-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="submit-btn">Create user</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('head'); ?>
<style>
.au-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;}
.au-card{padding:18px;display:flex;flex-direction:column;gap:14px;transition:box-shadow .15s,border-color .15s;}
.au-card:hover{border-color:#d3d9e8;box-shadow:0 2px 12px rgba(20,30,60,.08);}
.au-avatar{width:42px;height:42px;border-radius:12px;color:#fff;display:grid;place-items:center;font-size:16px;font-weight:700;flex-shrink:0;}
.au-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:10px 0;border-top:1px solid var(--line);}
.au-meta-item{display:flex;flex-direction:column;gap:2px;}
.au-meta-label{font-size:10.5px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;}
.au-meta-value{font-size:12.5px;font-weight:500;}
.au-actions{display:flex;gap:8px;padding-top:10px;border-top:1px solid var(--line);}
.au-act-btn{padding:6px 12px !important;font-size:12px !important;flex:1;}
.au-del-btn{background:var(--rose-soft) !important;color:var(--rose) !important;border:1px solid #f8c4c9 !important;}
.au-del-btn:hover{background:#fde0e3 !important;}
@media(max-width:640px){.au-grid{grid-template-columns:1fr;} .au-meta{grid-template-columns:1fr 1fr;}}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
const form = document.getElementById('user-form');
const storeUrl = "<?php echo e(route('admin.users.store')); ?>";
function openModal(){
    document.getElementById('modal-title').textContent='New user';
    document.getElementById('submit-btn').textContent='Create user';
    document.getElementById('pw-label').textContent='Password';
    document.getElementById('f-name').value='';document.getElementById('f-email').value='';
    document.getElementById('f-password').value='';document.getElementById('f-role').value='CLIENT_OWNER';
    document.getElementById('f-plan').value='';
    form.action = storeUrl;
    document.getElementById('user-modal').classList.add('open');
}
function openEdit(u, planName){
    document.getElementById('modal-title').textContent='Edit user';
    document.getElementById('submit-btn').textContent='Save changes';
    document.getElementById('pw-label').textContent='New password (leave blank to keep)';
    document.getElementById('f-name').value=u.name;document.getElementById('f-email').value=u.email;
    document.getElementById('f-password').value='';document.getElementById('f-role').value=u.role;
    // Try to match plan by name
    const planSelect = document.getElementById('f-plan');
    planSelect.value = '';
    if(planName){
        for(let i=0;i<planSelect.options.length;i++){
            if(planSelect.options[i].textContent.startsWith(planName)){planSelect.selectedIndex=i;break;}
        }
    }
    form.action = "<?php echo e(url('admin/users')); ?>/" + u.id;
    document.getElementById('user-modal').classList.add('open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\admin\users.blade.php ENDPATH**/ ?>