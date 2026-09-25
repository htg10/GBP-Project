<?php $__env->startSection('title', $client->name); ?>
<?php $__env->startSection('content'); ?>
<div style="margin-bottom:6px;"><a href="<?php echo e(route('clients')); ?>" style="font-size:13px;color:var(--muted);">← All clients</a></div>
<div class="page-head">
    <div style="display:flex;gap:14px;align-items:center;">
        <div class="avatar" style="width:52px;height:52px;font-size:20px;background:var(--teal-soft);color:var(--teal-ink);"><?php echo e(strtoupper(substr($client->name,0,1))); ?></div>
        <div><h1><?php echo e($client->name); ?></h1><p><?php echo e($client->industry ?: 'No industry set'); ?></p></div>
    </div>
    <form method="POST" action="<?php echo e(route('clients.destroy', $client)); ?>" onsubmit="return confirm('Delete this client and all its data?')">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <button class="btn btn-ghost" style="color:var(--rose);border-color:var(--rose-soft);">Delete client</button>
    </form>
</div>

<!-- ===== Individual account dashboard: stats ===== -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px;" class="acct-stats">
    <div class="card" style="padding:15px;">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Total Reviews</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px;"><?php echo e(number_format($stats['total'])); ?></div>
    </div>
    <div class="card" style="padding:15px;">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Avg Rating</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px;color:#f59e0b;"><?php echo e(number_format($stats['avg'],1)); ?> <span style="font-size:16px;">★</span></div>
    </div>
    <div class="card" style="padding:15px;">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Replied</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px;color:var(--teal);"><?php echo e($stats['replied']); ?></div>
    </div>
    <div class="card" style="padding:15px;">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Pending</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px;color:var(--amber);"><?php echo e($stats['pending']); ?></div>
    </div>
</div>

<!-- ===== Rating breakdown + recent reviews ===== -->
<div style="display:grid;grid-template-columns:1fr 1.2fr;gap:14px;margin-bottom:14px;" class="acct-split">
    <div class="card">
        <strong style="font-size:14.5px;">Rating Breakdown</strong>
        <div style="margin-top:14px;display:flex;flex-direction:column;gap:9px;">
            <?php $__currentLoopData = $starDist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $star => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $pct = $stats['total'] ? round($count / $stats['total'] * 100) : 0; ?>
                <div style="display:flex;align-items:center;gap:9px;font-size:12.5px;">
                    <span style="width:34px;font-weight:600;"><?php echo e($star); ?> <span style="color:#f59e0b;">★</span></span>
                    <div style="flex:1;height:8px;background:#eef1f8;border-radius:6px;overflow:hidden;"><div style="height:100%;width:<?php echo e($pct); ?>%;background:linear-gradient(90deg,#4c6fff,#6b8afd);border-radius:6px;"></div></div>
                    <span style="width:64px;text-align:right;font-weight:600;"><?php echo e($count); ?> <span style="color:var(--muted);font-weight:500;">(<?php echo e($pct); ?>%)</span></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid var(--line);">
            <strong style="font-size:14.5px;">Recent Reviews</strong>
            <a href="<?php echo e(route('reviews')); ?>" style="color:var(--teal);font-size:12.5px;font-weight:600;">All →</a>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $recentReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="display:flex;gap:10px;padding:12px 18px;<?php echo e(!$loop->last ? 'border-bottom:1px solid var(--line);' : ''); ?>">
                <div class="avatar" style="width:32px;height:32px;font-size:11px;flex-shrink:0;background:<?php echo e($rev->star_rating >= 4 ? 'var(--green-soft)' : ($rev->star_rating >= 3 ? 'var(--amber-soft)' : 'var(--rose-soft)')); ?>;color:<?php echo e($rev->star_rating >= 4 ? 'var(--green)' : ($rev->star_rating >= 3 ? '#8a5a08' : 'var(--rose)')); ?>;"><?php echo e(strtoupper(substr($rev->reviewer_name ?: '?', 0, 1))); ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;">
                        <strong style="font-size:13px;"><?php echo e($rev->reviewer_name ?: 'Anonymous'); ?></strong>
                        <span style="font-size:12px;color:#f59e0b;"><?php for($i=1;$i<=5;$i++): ?><?php echo e($i <= $rev->star_rating ? '★' : '☆'); ?><?php endfor; ?></span>
                    </div>
                    <?php if($rev->comment): ?><div style="font-size:12.5px;color:var(--muted);margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e(Str::limit($rev->comment, 80)); ?></div><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty" style="padding:28px 20px;">No reviews synced yet for this account.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Google connection -->
<div class="card" style="margin-bottom:14px;<?php echo e($googleIntegration && $googleIntegration->access_token ? 'border:1.5px solid var(--teal);' : ''); ?>">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:12px;align-items:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;font-weight:700;color:#4285F4;">G</div>
            <div>
                <strong style="font-size:14.5px;">Google Business Profile</strong>
                <?php if($googleIntegration && $googleIntegration->access_token): ?>
                    <div style="font-size:12.5px;color:var(--teal-ink);margin-top:2px;">✓ Connected — reviews sync from Google for real</div>
                <?php else: ?>
                    <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Not connected — using demo data until you connect</div>
                <?php endif; ?>
            </div>
        </div>
        <?php if($googleIntegration && $googleIntegration->access_token): ?>
            <form method="POST" action="<?php echo e(route('google.disconnect', $client)); ?>" onsubmit="return confirm('Disconnect Google for this client?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="btn btn-ghost" style="color:var(--rose);border-color:var(--rose-soft);">Disconnect</button>
            </form>
        <?php else: ?>
            <a href="<?php echo e(route('google.connect', $client)); ?>" class="btn">Connect Google</a>
        <?php endif; ?>
    </div>
    <?php if($googleIntegration && $googleIntegration->access_token): ?>
        <div style="border-top:1px solid var(--line);margin-top:14px;padding-top:14px;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12.5px;color:var(--muted);">Pull this business's real locations from Google, then sync reviews.</div>
            <form method="POST" action="<?php echo e(route('clients.import-locations', $client)); ?>">
                <?php echo csrf_field(); ?>
                <button class="btn" style="padding:8px 14px;font-size:12.5px;">⬇ Import real locations</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Meta connection -->
<div class="card" style="margin-bottom:14px;<?php echo e($metaIntegration && $metaIntegration->access_token ? 'border:1.5px solid var(--teal);' : ''); ?>">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:12px;align-items:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;font-weight:700;color:#0866FF;">M</div>
            <div>
                <strong style="font-size:14.5px;">Meta (Facebook / Instagram)</strong>
                <?php if($metaIntegration && $metaIntegration->access_token): ?>
                    <div style="font-size:12.5px;color:var(--teal-ink);margin-top:2px;">
                        ✓ Connected — Page "<?php echo e($metaIntegration->meta['page_name'] ?? '—'); ?>"<?php echo e(!empty($metaIntegration->meta['ig_user_id']) ? ' + Instagram' : ''); ?>. Social posts publish for real.
                    </div>
                <?php else: ?>
                    <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Not connected — Facebook/Instagram posts save as drafts until you connect</div>
                <?php endif; ?>
            </div>
        </div>
        <?php if($metaIntegration && $metaIntegration->access_token): ?>
            <form method="POST" action="<?php echo e(route('meta.disconnect', $client)); ?>" onsubmit="return confirm('Disconnect Meta for this client?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="btn btn-ghost" style="color:var(--rose);border-color:var(--rose-soft);">Disconnect</button>
            </form>
        <?php else: ?>
            <a href="<?php echo e(route('meta.connect', $client)); ?>" class="btn">Connect Meta</a>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;align-items:start;">
    <!-- Locations -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <strong>Google locations</strong>
            <button class="btn" style="padding:7px 12px;font-size:12.5px;" onclick="document.getElementById('loc-modal').classList.add('open')">+ Add</button>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $client->locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:11px 0;<?php echo e(!$loop->first ? 'border-top:1px solid var(--line);' : ''); ?>">
                <div>
                    <div style="font-weight:600;font-size:13.5px;"><?php echo e($loc->title); ?></div>
                    <?php if($loc->address): ?><div style="font-size:12px;color:var(--muted);margin-top:2px;">📍 <?php echo e($loc->address); ?></div><?php endif; ?>
                    <div style="font-size:11px;color:#b8b4a8;margin-top:3px;font-family:monospace;"><?php echo e(\Illuminate\Support\Str::limit($loc->google_name, 32)); ?></div>
                </div>
                <form method="POST" action="<?php echo e(route('clients.locations.destroy', [$client, $loc])); ?>" onsubmit="return confirm('Remove this location?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);font-size:13px;">🗑</button>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="font-size:13px;color:var(--muted);padding:8px 0;">No locations yet. Add one so Reviews can sync.</div>
        <?php endif; ?>
    </div>

    <!-- Details + leads -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="card">
            <strong>Contact</strong>
            <div style="font-size:13px;color:#3a4a45;line-height:1.9;margin-top:8px;">
                <div>✆ <?php echo e($client->phone ?: '—'); ?></div>
                <div>✉ <?php echo e($client->email ?: '—'); ?></div>
            </div>
        </div>
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <strong>Leads</strong>
                <span class="badge teal"><?php echo e($client->leads->count()); ?></span>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $client->leads->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div style="display:flex;justify-content:space-between;padding:9px 0;<?php echo e(!$loop->first ? 'border-top:1px solid var(--line);' : 'margin-top:8px;border-top:1px solid var(--line);'); ?>">
                    <span style="font-size:13px;"><?php echo e($lead->name); ?></span>
                    <span class="badge gray"><?php echo e(ucfirst(strtolower(str_replace('_',' ',$lead->stage)))); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div style="font-size:13px;color:var(--muted);margin-top:8px;">No leads yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-bg" id="loc-modal">
    <div class="modal">
        <h2>Add location</h2>
        <form method="POST" action="<?php echo e(route('clients.locations.store', $client)); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Location title</span><input type="text" name="title" placeholder="e.g. Bright Smile — Ghaziabad" required></label>
            <label><span class="lbl">Address</span><input type="text" name="address" placeholder="City, State"></label>
            <label><span class="lbl">Google location ID (optional)</span><input type="text" name="google_name" placeholder="Leave blank — set on Google connect"></label>
            <div style="font-size:12px;color:var(--muted);margin-top:6px;">Once you connect Google (coming soon), this fills automatically and reviews sync for real.</div>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('loc-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add location</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('head'); ?>
<style>
    @media (max-width:760px){
        .acct-stats{grid-template-columns:repeat(2,1fr) !important;}
        .acct-split{grid-template-columns:1fr !important;}
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\client-show.blade.php ENDPATH**/ ?>