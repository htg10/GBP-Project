<?php
    $timeAgo = $review->review_time ? $review->review_time->diffForHumans() : '';
    $replyTimeAgo = $review->replied_at ? $review->replied_at->diffForHumans() : '';
    $sent = $review->sentiment;
    $badgeCls = $sent === 'POSITIVE' ? 'teal' : ($sent === 'NEGATIVE' ? 'rose' : 'gray');
    $commentLength = mb_strlen($review->comment ?? '');
?>
<div class="rv">
    <div class="rv-top">
        <?php if($review->reviewer_photo): ?>
            <img src="<?php echo e($review->reviewer_photo); ?>" alt="<?php echo e($review->reviewer_name); ?>" class="rv-photo" referrerpolicy="no-referrer">
        <?php else: ?>
            <div class="rv-avatar"><?php echo e(strtoupper(substr($review->reviewer_name, 0, 1))); ?></div>
        <?php endif; ?>
        <div class="rv-meta">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span class="rv-name"><?php echo e($review->reviewer_name); ?></span>
                <?php if($sent): ?><span class="badge <?php echo e($badgeCls); ?>" style="font-size:10.5px;padding:2px 8px;"><?php echo e(ucfirst(strtolower($sent))); ?></span><?php endif; ?>
            </div>
            <div class="rv-stars">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <span <?php if($i > $review->star_rating): ?>class="empty"<?php endif; ?>>★</span>
                <?php endfor; ?>
            </div>
        </div>
        <span class="rv-time"><?php echo e($timeAgo); ?></span>
    </div>

    <?php if($review->comment): ?>
        <div class="rv-comment <?php echo e($commentLength > 200 ? 'clamped' : ''); ?>" id="comment-<?php echo e($review->id); ?>"><?php echo e($review->comment); ?></div>
        <?php if($commentLength > 200): ?>
            <span class="rv-readmore">Read more</span>
        <?php endif; ?>
    <?php endif; ?>

    
    <?php if($review->reply_text): ?>
        <div class="rv-reply">
            <div class="rv-reply-head">
                <div class="rv-reply-avatar"><?php echo e($locationInitial); ?></div>
                <span class="rv-reply-label">Owner Reply</span>
                <?php if($replyTimeAgo): ?><span class="rv-reply-time"><?php echo e($replyTimeAgo); ?></span><?php endif; ?>
            </div>
            <div class="rv-reply-text"><?php echo e($review->reply_text); ?></div>
        </div>
    <?php else: ?>
        <div class="rv-reply-form">
            <form method="POST" action="<?php echo e(route('reviews.reply', $review)); ?>" id="reply-form-<?php echo e($review->id); ?>">
                <?php echo csrf_field(); ?>
                <textarea name="reply_text" id="reply-<?php echo e($review->id); ?>" rows="3" placeholder="Write a reply to <?php echo e($review->reviewer_name); ?>…"></textarea>
                <div class="rv-form-actions">
                    <button type="button" class="btn btn-ghost" style="padding:8px 14px;font-size:12.5px;" onclick="genReply(<?php echo e($review->id); ?>, this)">✦ AI Draft</button>
                    <button type="submit" class="btn" style="padding:8px 16px;font-size:12.5px;">Post Reply</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\partials\_review-card.blade.php ENDPATH**/ ?>