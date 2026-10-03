
<?php $__env->startSection('title', $location->title ?: 'Reviews'); ?>
<?php $__env->startSection('content'); ?>

<?php $__env->startPush('head'); ?>
<style>
    .rl-back{font-size:13px;color:var(--muted);display:inline-flex;align-items:center;gap:5px;margin-bottom:8px;transition:color .15s;}
    .rl-back:hover{color:var(--teal);}
    .rl-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:14px;flex-wrap:wrap;}
    .rl-header h1{font-size:22px;font-weight:700;letter-spacing:-.02em;}
    .rl-header p{font-size:13px;color:var(--muted);margin-top:3px;}
    .rl-actions{display:flex;gap:8px;align-items:center;flex-shrink:0;}

    .rl-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px;}
    .rl-stat{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px 18px;box-shadow:var(--shadow);display:flex;align-items:center;gap:14px;}
    .rl-stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:18px;flex-shrink:0;}
    .rl-stat-icon.blue{background:var(--teal-soft);color:var(--teal);}
    .rl-stat-icon.amber{background:var(--amber-soft);color:var(--amber);}
    .rl-stat-icon.green{background:var(--green-soft);color:var(--green);}
    .rl-stat-icon.rose{background:var(--rose-soft);color:var(--rose);}
    .rl-stat-val{font-size:24px;font-weight:800;line-height:1;}
    .rl-stat-lbl{font-size:11.5px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-top:2px;}

    .rl-conn{border-radius:11px;padding:10px 15px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:8px;}
    .rl-conn.live{background:var(--teal-soft);border:1px solid #cfe6e0;color:var(--teal-ink);}
    .rl-conn.demo{background:var(--amber-soft);border:1px solid #f0d9a8;color:#8a5a08;}

    .rl-filters{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;align-items:center;}
    .rl-chip{border:1px solid var(--line);border-radius:999px;padding:7px 16px;font-size:13px;font-weight:500;background:var(--card);color:var(--muted);cursor:pointer;transition:all .15s;text-decoration:none;}
    .rl-chip:hover{border-color:var(--teal);color:var(--teal);}
    .rl-chip.active{background:var(--ink);color:#fff;border-color:var(--ink);}

    .rl-section-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin:18px 0 10px;display:flex;align-items:center;gap:8px;}
    .rl-section-label .dot{width:8px;height:8px;border-radius:50%;}
    .rl-section-label .dot.amber{background:var(--amber);}
    .rl-section-label .dot.teal{background:var(--teal);}

    .rv{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);margin-bottom:12px;transition:border-color .15s;}
    .rv:hover{border-color:#d0d5e0;}
    .rv-top{display:flex;gap:12px;align-items:flex-start;}
    .rv-photo{width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;}
    .rv-avatar{width:44px;height:44px;border-radius:50%;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-size:15px;font-weight:700;flex-shrink:0;}
    .rv-meta{flex:1;min-width:0;}
    .rv-name{font-size:14.5px;font-weight:700;}
    .rv-stars{color:var(--amber);font-size:14px;letter-spacing:1px;margin-top:3px;display:inline-flex;align-items:center;gap:6px;}
    .rv-stars .empty{color:#d9d6cc;}
    .rv-time{font-size:12px;color:var(--muted);flex-shrink:0;white-space:nowrap;}
    .rv-comment{font-size:14px;line-height:1.6;color:var(--ink);margin:12px 0 0;overflow:hidden;}
    .rv-comment.clamped{max-height:4.8em;position:relative;}
    .rv-comment.clamped::after{content:'';position:absolute;bottom:0;left:0;right:0;height:1.6em;background:linear-gradient(transparent,var(--card));}
    .rv-readmore{font-size:12.5px;color:var(--teal);font-weight:600;cursor:pointer;margin-top:4px;display:inline-block;}
    .rv-readmore:hover{text-decoration:underline;}

    .rv-reply{margin-top:14px;padding:14px 16px;background:var(--paper);border-radius:12px;border:1px solid var(--line);}
    .rv-reply-head{display:flex;align-items:center;gap:10px;margin-bottom:8px;}
    .rv-reply-avatar{width:28px;height:28px;border-radius:50%;background:var(--teal);color:#fff;display:grid;place-items:center;font-size:11px;font-weight:700;flex-shrink:0;}
    .rv-reply-label{font-size:12px;font-weight:700;color:var(--teal-ink);}
    .rv-reply-time{font-size:11px;color:var(--muted);margin-left:auto;}
    .rv-reply-text{font-size:13.5px;line-height:1.55;color:var(--ink);}

    .rv-reply-form{margin-top:14px;}
    .rv-reply-form textarea{min-height:80px;resize:vertical;font-size:13.5px;}
    .rv-reply-form .rv-form-actions{display:flex;justify-content:space-between;align-items:center;margin-top:8px;gap:8px;}

    .rl-pagination{display:flex;justify-content:center;gap:4px;margin-top:22px;flex-wrap:wrap;}
    .rl-pagination a,.rl-pagination span{padding:8px 13px;border-radius:8px;font-size:13px;font-weight:500;border:1px solid var(--line);background:var(--card);color:var(--muted);text-decoration:none;transition:all .15s;}
    .rl-pagination a:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-soft);}
    .rl-pagination .active span{background:var(--teal);color:#fff;border-color:var(--teal);}
    .rl-pagination .disabled span{opacity:.4;cursor:default;}

    .sync-overlay{position:fixed;inset:0;background:rgba(16,36,31,.55);display:none;place-items:center;z-index:60;}
    .sync-overlay.show{display:grid;}
    .sync-box{background:var(--card);border-radius:18px;padding:32px 40px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.15);}
    .sync-spinner{width:40px;height:40px;border:3px solid var(--line);border-top-color:var(--teal);border-radius:50%;margin:0 auto 16px;animation:spin .7s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg)}}

    @media (max-width:700px){
        .rl-stats{grid-template-columns:repeat(2,1fr);}
        .rv-top{flex-wrap:wrap;}
        .rv-time{width:100%;order:3;margin-top:4px;}
    }
    @media (max-width:480px){
        .rl-stats{grid-template-columns:1fr 1fr;}
        .rv{padding:14px;}
    }
</style>
<?php $__env->stopPush(); ?>

<a href="<?php echo e(route('reviews')); ?>" class="rl-back">← All locations</a>

<div class="rl-header">
    <div>
        <h1><?php echo e($location->title ?: $location->google_name); ?></h1>
        <p><?php echo e($location->client->name); ?><?php if($location->address): ?> · <?php echo e($location->address); ?><?php endif; ?></p>
    </div>
    <div class="rl-actions">
        <form method="POST" action="<?php echo e(route('reviews.sync', $location)); ?>" id="sync-form">
            <?php echo csrf_field(); ?>
            <button class="btn btn-ghost" type="submit" id="sync-btn">⟳ Sync from Google</button>
        </form>
    </div>
</div>

<div class="rl-conn <?php echo e($isLive ? 'live' : 'demo'); ?>">
    <?php if($isLive): ?>
        <span>✓</span> Connected to Google — sync pulls real reviews for this location.
    <?php else: ?>
        <span>⚡</span> Showing <strong>demo reviews</strong>. Open this location's client and click <strong>"Connect Google"</strong> to pull real data.
    <?php endif; ?>
</div>


<div class="rl-stats">
    <div class="rl-stat">
        <div class="rl-stat-icon blue">★</div>
        <div>
            <div class="rl-stat-val"><?php echo e(number_format($stats['avg'], 1)); ?></div>
            <div class="rl-stat-lbl">Avg Rating</div>
        </div>
    </div>
    <div class="rl-stat">
        <div class="rl-stat-icon green">✉</div>
        <div>
            <div class="rl-stat-val"><?php echo e($stats['total']); ?></div>
            <div class="rl-stat-lbl">Total Reviews</div>
        </div>
    </div>
    <div class="rl-stat">
        <div class="rl-stat-icon amber">↗</div>
        <div>
            <div class="rl-stat-val"><?php echo e($stats['unreplied']); ?></div>
            <div class="rl-stat-lbl">Awaiting Reply</div>
        </div>
    </div>
    <div class="rl-stat">
        <div class="rl-stat-icon rose">▽</div>
        <div>
            <div class="rl-stat-val"><?php echo e($stats['negative']); ?></div>
            <div class="rl-stat-lbl">Negative</div>
        </div>
    </div>
</div>


<div class="rl-filters">
    <?php $__currentLoopData = ['all'=>'All Reviews','unreplied'=>'Needs Reply','negative'=>'Negative']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('reviews.show', [$location, 'filter'=>$key])); ?>"
           class="rl-chip <?php echo e($filter===$key ? 'active' : ''); ?>"><?php echo e($lbl); ?>

            <?php if($key==='unreplied' && $stats['unreplied']): ?><span style="margin-left:3px;font-size:11px;opacity:.8;">(<?php echo e($stats['unreplied']); ?>)</span><?php endif; ?>
            <?php if($key==='negative' && $stats['negative']): ?><span style="margin-left:3px;font-size:11px;opacity:.8;">(<?php echo e($stats['negative']); ?>)</span><?php endif; ?>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if($reviews->isEmpty()): ?>
    <div class="card"><div class="empty">No reviews in this view yet. Click "Sync from Google" to pull data.</div></div>
<?php else: ?>
    <?php
        $locationInitial = strtoupper(substr($location->title ?: $location->client->name, 0, 1));
    ?>

    <?php if($filter === 'all'): ?>
        <?php
            $unreplied = $reviews->filter(fn($r) => !$r->reply_text);
            $replied = $reviews->filter(fn($r) => !!$r->reply_text);
        ?>

        <?php if($unreplied->isNotEmpty()): ?>
            <div class="rl-section-label"><span class="dot amber"></span> Unreplied (<?php echo e($unreplied->count()); ?>)</div>
            <?php $__currentLoopData = $unreplied; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('dashboard.partials._review-card', ['review' => $review, 'locationInitial' => $locationInitial, 'location' => $location], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        <?php if($replied->isNotEmpty()): ?>
            <div class="rl-section-label"><span class="dot teal"></span> Replied (<?php echo e($replied->count()); ?>)</div>
            <?php $__currentLoopData = $replied; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('dashboard.partials._review-card', ['review' => $review, 'locationInitial' => $locationInitial, 'location' => $location], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    <?php else: ?>
        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make('dashboard.partials._review-card', ['review' => $review, 'locationInitial' => $locationInitial, 'location' => $location], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    
    <?php if($reviews->hasPages()): ?>
        <div class="rl-pagination">
            <?php if($reviews->onFirstPage()): ?>
                <span class="disabled"><span>←</span></span>
            <?php else: ?>
                <a href="<?php echo e($reviews->previousPageUrl()); ?>">←</a>
            <?php endif; ?>

            <?php $__currentLoopData = $reviews->getUrlRange(1, $reviews->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($page == $reviews->currentPage()): ?>
                    <span class="active"><span><?php echo e($page); ?></span></span>
                <?php else: ?>
                    <a href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($reviews->hasMorePages()): ?>
                <a href="<?php echo e($reviews->nextPageUrl()); ?>">→</a>
            <?php else: ?>
                <span class="disabled"><span>→</span></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>


<div class="sync-overlay" id="sync-overlay">
    <div class="sync-box">
        <div class="sync-spinner"></div>
        <div style="font-size:16px;font-weight:700;margin-bottom:6px;">Syncing Live Data</div>
        <div style="font-size:13px;color:var(--muted);">Pulling reviews from Google Business Profile...</div>
    </div>
</div>

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
        if(res.status === 402 && window.handlePlanRequired(data)) return;
        if(data.error){ alert(data.error); return; }
        document.getElementById('reply-'+id).value = data.reply;
    }catch(e){ alert('Could not generate reply.'); }
    finally{ btn.textContent = original; btn.disabled = false; }
}

document.getElementById('sync-form')?.addEventListener('submit', function(){
    document.getElementById('sync-overlay').classList.add('show');
    document.getElementById('sync-btn').textContent = '⟳ Syncing…';
    document.getElementById('sync-btn').disabled = true;
});

document.querySelectorAll('.rv-readmore').forEach(btn => {
    btn.addEventListener('click', function(){
        const comment = this.previousElementSibling;
        comment.classList.remove('clamped');
        this.style.display = 'none';
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/dashboard/review-location.blade.php ENDPATH**/ ?>