<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div class="page-head" style="margin:0;"><h1>Users</h1><p>Create and manage everyone on your platform.</p></div>
    <button class="btn" onclick="openModal()">+ New user</button>
</div>
<?php $labels=['SUPER_ADMIN'=>'Super Admin','AGENCY_OWNER'=>'Agency Owner','CLIENT_OWNER'=>'Client Owner','MARKETING_MANAGER'=>'Marketing Manager','STAFF'=>'Staff']; ?>
<div class="card" style="padding:0;overflow:hidden;">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Scoped to client</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($u->name); ?></strong></td>
                    <td style="color:var(--muted);"><?php echo e($u->email); ?></td>
                    <td><span class="badge <?php echo e($u->role==='SUPER_ADMIN'?'dark':'teal'); ?>"><?php echo e($labels[$u->role] ?? $u->role); ?></span></td>
                    <td style="font-size:13px;"><?php echo e($u->client->name ?? '— All agency —'); ?></td>
                    <td style="text-align:right;">
                        <button class="icon-btn" onclick='openEdit(<?php echo json_encode($u, 15, 512) ?>)'>✎</button>
                        <form method="POST" action="<?php echo e(route('admin.users.destroy', $u)); ?>" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="icon-btn" style="color:var(--rose);">🗑</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px;">No users yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-bg" id="user-modal">
    <div class="modal">
        <h2 id="modal-title" style="font-size:18px;margin-bottom:14px;">New user</h2>
        <form method="POST" id="user-form" action="<?php echo e(route('admin.users.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Name</span><input type="text" name="name" id="f-name" required></label>
            <label><span class="lbl">Email</span><input type="email" name="email" id="f-email" required></label>
            <label><span class="lbl" id="pw-label">Password</span><input type="password" name="password" id="f-password"></label>
            <label><span class="lbl">Role</span><select name="role" id="f-role">
                <?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($val); ?>"><?php echo e($lbl); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select></label>
            <label><span class="lbl">Scope to client (optional)</span><select name="client_id" id="f-client">
                <option value="">— All agency data —</option>
                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <span style="font-size:11.5px;color:var(--muted);display:block;margin-top:5px;">When set, this user only sees this client's own Google Business data.</span>
            </label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn" style="flex:1;background:#fff;border:1px solid var(--line);color:var(--ink);" onclick="document.getElementById('user-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="submit-btn">Create user</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->startPush('scripts'); ?>
<script>
const form = document.getElementById('user-form');
const storeUrl = "<?php echo e(route('admin.users.store')); ?>";
function openModal(){
    document.getElementById('modal-title').textContent='New user';
    document.getElementById('submit-btn').textContent='Create user';
    document.getElementById('pw-label').textContent='Password';
    document.getElementById('f-name').value='';document.getElementById('f-email').value='';
    document.getElementById('f-password').value='';document.getElementById('f-role').value='STAFF';
    document.getElementById('f-client').value='';
    form.action = storeUrl;
    document.getElementById('user-modal').classList.add('open');
}
function openEdit(u){
    document.getElementById('modal-title').textContent='Edit user';
    document.getElementById('submit-btn').textContent='Save changes';
    document.getElementById('pw-label').textContent='New password (leave blank to keep)';
    document.getElementById('f-name').value=u.name;document.getElementById('f-email').value=u.email;
    document.getElementById('f-password').value='';document.getElementById('f-role').value=u.role;
    document.getElementById('f-client').value=u.client_id || '';
    form.action = "<?php echo e(url('admin/users')); ?>/" + u.id;
    document.getElementById('user-modal').classList.add('open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/admin/users.blade.php ENDPATH**/ ?>