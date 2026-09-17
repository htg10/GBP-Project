<?php $__env->startSection('title', $location->title ?: 'Reviews'); ?>
<?php $__env->startSection('content'); ?>
<div style="margin-bottom:6px;"><a href="<?php echo e(route('reviews')); ?>" style="font-size:13px;color:var(--muted);">← All reviews</a></div>
<div class="page-head">
    <div><h1><?php echo e($location->title ?: $location->google_name); ?></h1><p><?php echo e($location->client->name); ?><?php if($location->address): ?> · <?php echo e($location->address); ?><?php endif; ?></p></div>
    <form method="POST" action="<?php echo e(route('reviews.sync', $location)); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost" type="submit" id="sync-btn">⟳ Sync from Google</button></form>
</div>

<?php if(!$isLive): ?>
    <div style="background:var(--amber-soft);border:1px solid #f0d9a8;border-radius:11px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#8a5a08;">
        ⚡ Showing <strong>demo reviews</strong>. To pull real Google reviews, open this location's client and click <strong>“Connect Google”</strong>.
    </div>
<?php else: ?>
    <div style="background:var(--teal-soft);border:1px solid #cfe6e0;border-radius:11px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:var(--teal-ink);">
        ✓ Connected to Google — sync pulls real reviews for this location.
    </div>
<?php endif; ?>

<div class="stats">
    <div class="stat accent"><div>★</div><div class="v"><?php echo e(number_format($stats['avg'],1)); ?></div><div class="l">Avg rating</div></div>
    <div class="stat"><div>✉</div><div class="v"><?php echo e($stats['total']); ?></div><div class="l">Total</div></div>
    <div class="stat"><div>↗</div><div class="v" style="color:var(--amber)"><?php echo e($stats['unreplied']); ?></div><div class="l">Awaiting reply</div></div>
    <div class="stat"><div>▽</div><div class="v"><?php echo e($stats['negative']); ?></div><div class="l">Negative</div></div>
</div>

<div style="display:flex;gap:8px;margin-bottom:16px;">
    <?php $__currentLoopData = ['all'=>'All','unreplied'=>'Needs reply','negative'=>'Negative']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('reviews.show', [$location, 'filter'=>$key])); ?>"
           style="border:1px solid var(--line);border-radius:999px;padding:7px 14px;font-size:13px;font-weight:500;<?php echo e($filter===$key ? 'background:var(--ink);color:#fff;' : 'background:var(--card);color:var(--muted);'); ?>"><?php echo e($lbl); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if($reviews->isEmpty()): ?>
    <div class="card"><div class="empty">No reviews in this view yet. Click “Sync from Google”.</div></div>
<?php else: ?>
    <div style="display:flex;flex-direction:column;gap:13px;">
        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $sent = $review->sentiment; $badge = $sent==='POSITIVE'?'teal':($sent==='NEGATIVE'?'rose':'gray'); ?>
            <div class="card">
                <div style="display:flex;gap:11px;margin-bottom:11px;align-items:flex-start;">
                    <div class="avatar"><?php echo e(strtoupper(substr($review->reviewer_name,0,1))); ?></div>
                    <div style="flex:1;">
                        <div style="display:flex;justify-content:space-between;">
                            <strong><?php echo e($review->reviewer_name); ?></strong>
                            <?php if($review->reply_text): ?><span class="badge teal">✓ Replied</span><?php endif; ?>
                        </div>
                        <div style="margin-top:5px;display:flex;align-items:center;gap:9px;">
                            <span style="color:var(--amber);letter-spacing:1px;"><?php echo e(str_repeat('★', $review->star_rating)); ?><span style="color:#d9d6cc;"><?php echo e(str_repeat('★', 5-$review->star_rating)); ?></span></span>
                            <?php if($sent): ?><span class="badge <?php echo e($badge); ?>"><?php echo e(ucfirst(strtolower($sent))); ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
                <p style="font-size:14px;line-height:1.55;color:#2a3a35;margin-bottom:14px;"><?php echo e($review->comment); ?></p>

                <div style="border-top:1px dashed var(--line);padding-top:13px;">
                    <?php if($review->reply_text): ?>
                        <div style="font-size:11px;font-weight:600;color:var(--teal);text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;">Your reply</div>
                        <p style="font-size:13.5px;line-height:1.5;color:#3a4a45;"><?php echo e($review->reply_text); ?></p>
                    <?php else: ?>
                        <form method="POST" action="<?php echo e(route('reviews.reply', $review)); ?>" id="reply-form-<?php echo e($review->id); ?>">
                            <?php echo csrf_field(); ?>
                            <textarea name="reply_text" id="reply-<?php echo e($review->id); ?>" rows="3" placeholder="Write a reply…" style="margin-bottom:9px;"></textarea>
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <button type="button" class="btn btn-ghost" style="padding:8px 13px;font-size:12.5px;" onclick="genReply(<?php echo e($review->id); ?>, this)">✦ Draft AI reply</button>
                                <button type="submit" class="btn" style="padding:8px 14px;font-size:12.5px;">Post reply</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
async function genReply(id, btn){
    const original = btn.textContent;
    btn.textContent = 'Drafting…'; btn.disabled = true;
    try{
        const res = await fetch(`<?php echo e(url('reviews')); ?>/${id}/generate`, {
            method:'POST',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},
        });
        const data = await res.json();
        document.getElementById('reply-'+id).value = data.reply;
    }catch(e){ alert('Could not generate reply.'); }
    finally{ btn.textContent = original; btn.disabled = false; }
}
// Sync can take a few seconds (one real network round-trip to Google) —
// give clear feedback instead of leaving the button looking dead.
document.getElementById('sync-btn')?.closest('form')?.addEventListener('submit', function(){
    const b = document.getElementById('sync-btn');
    b.textContent = '⟳ Syncing…'; b.disabled = true;
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\review-location.blade.php ENDPATH**/ ?>