<?php $__env->startSection('title', 'My Team'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <h1>My Team</h1>
        <p>Add Staff and Marketing Managers for your business. They only see your data.</p>
    </div>
    <button class="btn" onclick="document.getElementById('team-modal').classList.add('open')">+ Add member</button>
</div>

<?php $labels = ['STAFF'=>'Staff','MARKETING_MANAGER'=>'Marketing Manager']; ?>

<div class="card" style="padding:0;overflow:hidden;">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $team; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="display:flex;align-items:center;gap:10px;">
                        <div class="avatar" style="width:32px;height:32px;font-size:12px;"><?php echo e(strtoupper(substr($u->name,0,1))); ?></div>
                        <strong><?php echo e($u->name); ?></strong>
                    </td>
                    <td style="color:var(--muted);"><?php echo e($u->email); ?></td>
                    <td><span class="badge teal"><?php echo e($labels[$u->role] ?? ucwords(strtolower(str_replace('_',' ',$u->role)))); ?></span></td>
                    <td style="text-align:right;">
                        <form method="POST" action="<?php echo e(route('team.destroy', $u)); ?>" style="display:inline;" onsubmit="return confirm('Remove this team member?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);font-size:14px;">🗑 Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="empty" style="padding:30px;">No team members yet. Add your first Staff or Marketing Manager.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-bg" id="team-modal">
    <div class="modal">
        <h2>Add team member</h2>
        <p style="font-size:13px;color:var(--muted);margin-bottom:8px;">This person will be created under your business only.</p>
        <form method="POST" action="<?php echo e(route('team.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Name</span><input type="text" name="name" required></label>
            <label><span class="lbl">Email</span><input type="email" name="email" required></label>
            <label><span class="lbl">Temporary password</span><input type="password" name="password" minlength="8" required></label>
            <label><span class="lbl">Role</span><select name="role">
                <option value="STAFF">Staff</option>
                <option value="MARKETING_MANAGER">Marketing Manager</option>
            </select></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('team-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add member</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\team.blade.php ENDPATH**/ ?>