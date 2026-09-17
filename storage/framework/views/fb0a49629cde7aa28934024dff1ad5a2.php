<?php $__env->startSection('title', 'Social'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Social Media</h1><p>Draft and publish posts across your channels, with AI help.</p></div>
    <button class="btn" onclick="document.getElementById('post-modal').classList.add('open')">+ New post</button>
</div>

<?php if(($liveCount ?? 0) === 0): ?>
    <div style="background:var(--amber-soft);border:1px solid #f0d9a8;border-radius:11px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#8a5a08;">
        ⚡ Facebook/Instagram posts save as <strong>drafts only</strong> right now. To publish for real, open a client and click <strong>“Connect Meta”</strong>.
    </div>
<?php else: ?>
    <div style="background:var(--teal-soft);border:1px solid #cfe6e0;border-radius:11px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:var(--teal-ink);">
        ✓ <strong><?php echo e($liveCount); ?></strong> client(s) connected to Meta — Facebook/Instagram posts publish for real.
    </div>
<?php endif; ?>

<?php if($clients->isEmpty()): ?><div class="alert info">Add a client first — posts attach to a client.</div><?php endif; ?>

<?php $badgeFor = fn($s) => $s==='PUBLISHED'?'teal':($s==='SCHEDULED'?'amber':($s==='FAILED'?'rose':'gray')); ?>

<?php if($posts->isEmpty()): ?>
    <div class="card"><div class="empty">No posts yet. Click “New post” to draft your first one.</div></div>
<?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
        <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <span style="font-weight:600;font-size:13.5px;color:var(--teal-ink);"><?php echo e(ucfirst(strtolower($p->platform))); ?></span>
                    <span class="badge <?php echo e($badgeFor($p->status)); ?>"><?php echo e($p->status); ?></span>
                </div>
                <?php if(!empty($p->media_urls[0])): ?>
                    <img src="<?php echo e($p->media_urls[0]); ?>" alt="" style="width:100%;max-height:220px;object-fit:cover;border-radius:8px;background:#f3f1ea;margin-bottom:10px;">
                <?php endif; ?>
                <p style="font-size:14px;line-height:1.55;color:#2a3a35;"><?php echo e($p->body); ?></p>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                    <?php if($p->scheduled_at): ?><div style="font-size:12px;color:var(--muted);">🗓 <?php echo e($p->scheduled_at->format('d M Y, h:i A')); ?></div><?php else: ?><span></span><?php endif; ?>
                    <?php if($p->status === 'FAILED' && in_array($p->platform, ['FACEBOOK','INSTAGRAM'])): ?>
                        <form method="POST" action="<?php echo e(route('social.retry', $p)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-ghost" style="padding:6px 12px;font-size:12px;">Retry publish</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div class="modal-bg" id="post-modal">
    <div class="modal">
        <h2>New post</h2>
        <form method="POST" action="<?php echo e(route('social.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Client</span><select name="client_id"><?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label><span class="lbl">Platform</span><select name="platform">
                <option value="FACEBOOK">Facebook</option><option value="INSTAGRAM">Instagram (image required)</option><option value="LINKEDIN">LinkedIn</option><option value="X">X (Twitter)</option>
            </select></label>
            <label><span class="lbl">Caption</span><textarea name="body" id="post-body" rows="4" placeholder="What's on your mind?"></textarea></label>
            <button type="button" onclick="genCaption(this)" style="background:none;border:none;color:var(--teal);font-size:12.5px;font-weight:600;cursor:pointer;margin-top:8px;">✦ Generate with AI</button>
            <label><span class="lbl">Image URL (optional — required for Instagram)</span><input type="url" name="media_url" placeholder="https://…"></label>
            <label><span class="lbl">Schedule (optional — leave blank to publish now)</span><input type="datetime-local" name="scheduled_at"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('post-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Save post</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->startPush('scripts'); ?>
<script>
async function genCaption(btn){
    const body = document.getElementById('post-body');
    btn.textContent='Generating…';btn.disabled=true;
    try{
        const res = await fetch('<?php echo e(route('social.caption')); ?>',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},body:JSON.stringify({prompt:body.value||'a social media post'})});
        const data = await res.json(); body.value = data.body;
    }catch(e){alert('Could not generate.');}
    finally{btn.textContent='✦ Generate with AI';btn.disabled=false;}
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\social.blade.php ENDPATH**/ ?>