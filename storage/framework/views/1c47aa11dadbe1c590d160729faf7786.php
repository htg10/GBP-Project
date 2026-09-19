<?php $__env->startSection('title', 'One-Click Optimize'); ?>
<?php $__env->startSection('content'); ?>

<?php $__env->startPush('head'); ?>
<style>
    .opt-wrap{display:grid;grid-template-columns:280px 1fr;gap:0;min-height:calc(100vh - 180px);background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}

    /* Sidebar */
    .opt-side{border-right:1px solid var(--line);display:flex;flex-direction:column;}
    .opt-side-head{padding:20px 18px 16px;border-bottom:1px solid var(--line);}
    .opt-side-head h2{font-size:16px;font-weight:700;margin-bottom:2px;}
    .opt-side-head p{font-size:12px;color:var(--muted);}

    .opt-score-ring{width:90px;height:90px;margin:18px auto 8px;position:relative;}
    .opt-score-ring svg{width:100%;height:100%;transform:rotate(-90deg);}
    .opt-score-ring circle{fill:none;stroke-width:7;stroke-linecap:round;}
    .opt-score-ring .bg{stroke:var(--line);}
    .opt-score-ring .fg{transition:stroke-dashoffset .8s ease;}
    .opt-score-val{position:absolute;inset:0;display:grid;place-items:center;font-size:22px;font-weight:800;}
    .opt-score-label{text-align:center;font-size:11.5px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:16px;}

    .opt-cats{flex:1;overflow-y:auto;padding:6px 8px;}
    .opt-cat{display:flex;align-items:center;gap:11px;padding:11px 12px;border-radius:10px;cursor:pointer;transition:background .12s;margin-bottom:2px;position:relative;}
    .opt-cat:hover{background:var(--paper);}
    .opt-cat.active{background:var(--teal-soft);}
    .opt-cat-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:16px;flex-shrink:0;}
    .opt-cat-icon.amber{background:var(--amber-soft);color:var(--amber);}
    .opt-cat-icon.blue{background:var(--teal-soft);color:var(--teal);}
    .opt-cat-icon.green{background:var(--green-soft);color:var(--green);}
    .opt-cat-icon.purple{background:#f3eaff;color:var(--purple);}
    .opt-cat-icon.rose{background:var(--rose-soft);color:var(--rose);}
    .opt-cat-info{flex:1;min-width:0;}
    .opt-cat-label{font-size:13px;font-weight:600;}
    .opt-cat-bar{height:4px;border-radius:4px;background:var(--line);margin-top:5px;overflow:hidden;}
    .opt-cat-bar-fill{height:100%;border-radius:4px;transition:width .6s ease;}
    .opt-cat-score{font-size:12px;font-weight:700;flex-shrink:0;min-width:32px;text-align:right;}

    .opt-side-footer{padding:12px 14px;border-top:1px solid var(--line);}
    .opt-run-btn{width:100%;padding:12px;border-radius:10px;border:none;background:linear-gradient(135deg,#7c3aed,#a78bfa);color:#fff;font-weight:700;font-size:13.5px;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:7px;transition:opacity .15s;}
    .opt-run-btn:hover{opacity:.9;}
    .opt-run-btn:disabled{opacity:.6;cursor:default;}

    /* Main content */
    .opt-main{padding:24px 28px;display:flex;flex-direction:column;gap:18px;overflow-y:auto;}

    .opt-hero{background:linear-gradient(135deg,#7c3aed 0%,#a78bfa 50%,#c084fc 100%);color:#fff;border-radius:14px;padding:22px 24px;display:flex;align-items:center;gap:18px;}
    .opt-hero-icon{width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:24px;flex-shrink:0;backdrop-filter:blur(8px);}
    .opt-hero-info{flex:1;}
    .opt-hero-info h3{font-size:17px;font-weight:800;margin-bottom:3px;}
    .opt-hero-info p{font-size:13px;opacity:.85;}
    .opt-hero-stats{display:flex;gap:14px;}
    .opt-hero-stat{background:rgba(255,255,255,.12);border-radius:10px;padding:10px 16px;text-align:center;backdrop-filter:blur(8px);min-width:80px;}
    .opt-hero-stat .v{font-size:22px;font-weight:800;}
    .opt-hero-stat .l{font-size:11px;opacity:.8;margin-top:1px;}

    /* Category detail panel */
    .opt-detail{display:none;}
    .opt-detail.show{display:block;}
    .opt-detail-head{display:flex;align-items:center;gap:14px;margin-bottom:18px;}
    .opt-detail-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;font-size:22px;flex-shrink:0;}
    .opt-detail-head h3{font-size:18px;font-weight:700;}
    .opt-detail-head p{font-size:13px;color:var(--muted);margin-top:2px;}
    .opt-detail-score{margin-left:auto;font-size:28px;font-weight:800;flex-shrink:0;}

    .opt-detail-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:18px;}
    .opt-detail-stat{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:16px;}
    .opt-detail-stat .v{font-size:22px;font-weight:800;margin-bottom:2px;}
    .opt-detail-stat .l{font-size:11.5px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.03em;}

    .opt-detail-progress{margin-bottom:18px;}
    .opt-detail-progress-label{display:flex;justify-content:space-between;font-size:12px;color:var(--muted);margin-bottom:6px;}
    .opt-detail-progress-bar{height:8px;border-radius:8px;background:var(--line);overflow:hidden;}
    .opt-detail-progress-fill{height:100%;border-radius:8px;transition:width .8s ease;}

    .opt-tip{display:flex;gap:12px;align-items:flex-start;background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:14px 16px;}
    .opt-tip-icon{width:32px;height:32px;border-radius:8px;background:var(--amber-soft);color:var(--amber);display:grid;place-items:center;font-size:14px;flex-shrink:0;}
    .opt-tip-text{font-size:13px;line-height:1.55;color:var(--ink);}
    .opt-tip-label{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px;}

    /* Checklist */
    .opt-checklist{display:flex;flex-direction:column;gap:8px;margin-bottom:18px;}
    .opt-check{display:flex;align-items:center;gap:10px;padding:11px 14px;background:var(--paper);border:1px solid var(--line);border-radius:10px;font-size:13px;}
    .opt-check-dot{width:22px;height:22px;border-radius:50%;display:grid;place-items:center;font-size:11px;font-weight:700;flex-shrink:0;}
    .opt-check-dot.done{background:var(--green-soft);color:var(--green);}
    .opt-check-dot.pending{background:var(--amber-soft);color:var(--amber);}
    .opt-check-text{flex:1;}
    .opt-check-text.done{color:var(--muted);text-decoration:line-through;}

    /* Results overlay */
    .opt-results-card{background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow);padding:20px;}
    .opt-result-item{display:flex;gap:12px;padding:12px 0;align-items:flex-start;}
    .opt-result-item:not(:first-child){border-top:1px solid var(--line);}
    .opt-result-icon{width:34px;height:34px;border-radius:9px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;flex-shrink:0;font-size:14px;}

    @keyframes sp{to{transform:rotate(360deg)}}
    .spin{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:sp .6s linear infinite;}

    @media (max-width:800px){
        .opt-wrap{grid-template-columns:1fr;}
        .opt-side{border-right:none;border-bottom:1px solid var(--line);}
        .opt-cats{display:flex;overflow-x:auto;padding:8px;gap:6px;}
        .opt-cat{flex-shrink:0;min-width:160px;}
        .opt-score-ring{display:none;}
        .opt-score-label{display:none;}
        .opt-hero-stats{flex-wrap:wrap;}
    }
</style>
<?php $__env->stopPush(); ?>

<?php
    $scoreColor = $overallScore >= 70 ? 'var(--green)' : ($overallScore >= 40 ? 'var(--amber)' : 'var(--rose)');
    $circumference = 2 * 3.14159 * 38;
    $dashOffset = $circumference - ($circumference * $overallScore / 100);
?>

<div class="opt-wrap">
    
    <div class="opt-side">
        <div class="opt-side-head">
            <h2>Optimization</h2>
            <p>AI-powered health check</p>
        </div>

        
        <div class="opt-score-ring">
            <svg viewBox="0 0 84 84">
                <circle class="bg" cx="42" cy="42" r="38"/>
                <circle class="fg" cx="42" cy="42" r="38" stroke="<?php echo e($scoreColor); ?>" stroke-dasharray="<?php echo e($circumference); ?>" stroke-dashoffset="<?php echo e($dashOffset); ?>"/>
            </svg>
            <div class="opt-score-val"><?php echo e($overallScore); ?>%</div>
        </div>
        <div class="opt-score-label">Overall Score</div>

        
        <div class="opt-cats">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $barColor = $cat['score'] >= 70 ? 'var(--green)' : ($cat['score'] >= 40 ? 'var(--amber)' : 'var(--rose)');
                ?>
                <div class="opt-cat <?php echo e($i === 0 ? 'active' : ''); ?>" data-cat="<?php echo e($cat['key']); ?>" onclick="selectCat('<?php echo e($cat['key']); ?>', this)">
                    <div class="opt-cat-icon <?php echo e($cat['color']); ?>"><?php echo e($cat['icon']); ?></div>
                    <div class="opt-cat-info">
                        <div class="opt-cat-label"><?php echo e($cat['label']); ?></div>
                        <div class="opt-cat-bar"><div class="opt-cat-bar-fill" style="width:<?php echo e($cat['score']); ?>%;background:<?php echo e($barColor); ?>;"></div></div>
                    </div>
                    <div class="opt-cat-score" style="color:<?php echo e($barColor); ?>;"><?php echo e($cat['score']); ?>%</div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="opt-side-footer">
            <button class="opt-run-btn" id="opt-btn" onclick="runOptimize()">⚡ Run Full Optimization</button>
        </div>
    </div>

    
    <div class="opt-main">
        
        <div class="opt-hero">
            <div class="opt-hero-icon">⚡</div>
            <div class="opt-hero-info">
                <h3>AI-Powered Optimization</h3>
                <p>Drafts replies, generates posts, and flags issues automatically</p>
            </div>
            <div class="opt-hero-stats">
                <div class="opt-hero-stat"><div class="v"><?php echo e($pending); ?></div><div class="l">Pending</div></div>
                <div class="opt-hero-stat"><div class="v"><?php echo e($total); ?></div><div class="l">Reviews</div></div>
                <div class="opt-hero-stat"><div class="v"><?php echo e($total ? round(($total - $pending) / $total * 100) : 0); ?>%</div><div class="l">Reply Rate</div></div>
            </div>
        </div>

        
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $catColor = $cat['score'] >= 70 ? 'var(--green)' : ($cat['score'] >= 40 ? 'var(--amber)' : 'var(--rose)');
                $iconBg = match($cat['color']) {
                    'amber' => 'var(--amber-soft)', 'blue' => 'var(--teal-soft)',
                    'green' => 'var(--green-soft)', 'purple' => '#f3eaff',
                    'rose' => 'var(--rose-soft)', default => 'var(--teal-soft)'
                };
                $iconColor = match($cat['color']) {
                    'amber' => 'var(--amber)', 'blue' => 'var(--teal)',
                    'green' => 'var(--green)', 'purple' => 'var(--purple)',
                    'rose' => 'var(--rose)', default => 'var(--teal)'
                };
            ?>
            <div class="opt-detail <?php echo e($i === 0 ? 'show' : ''); ?>" id="detail-<?php echo e($cat['key']); ?>">
                <div class="opt-detail-head">
                    <div class="opt-detail-icon" style="background:<?php echo e($iconBg); ?>;color:<?php echo e($iconColor); ?>;"><?php echo e($cat['icon']); ?></div>
                    <div>
                        <h3><?php echo e($cat['label']); ?></h3>
                        <p>Performance overview and recommendations</p>
                    </div>
                    <div class="opt-detail-score" style="color:<?php echo e($catColor); ?>;"><?php echo e($cat['score']); ?>%</div>
                </div>

                
                <div class="opt-detail-progress">
                    <div class="opt-detail-progress-label">
                        <span>Optimization Progress</span>
                        <span><?php echo e($cat['score']); ?>%</span>
                    </div>
                    <div class="opt-detail-progress-bar">
                        <div class="opt-detail-progress-fill" style="width:<?php echo e($cat['score']); ?>%;background:<?php echo e($catColor); ?>;"></div>
                    </div>
                </div>

                
                <div class="opt-detail-stats">
                    <?php $__currentLoopData = $cat['stats']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="opt-detail-stat">
                            <div class="v"><?php echo e($val); ?></div>
                            <div class="l"><?php echo e($label); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="opt-checklist">
                    <?php if($cat['key'] === 'reviews'): ?>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e($pending === 0 ? 'done' : 'pending'); ?>"><?php echo e($pending === 0 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e($pending === 0 ? 'done' : ''); ?>">Reply to all pending reviews</span>
                        </div>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($total ? round(($total - $pending) / $total * 100) : 0) >= 90 ? 'done' : 'pending'); ?>"><?php echo e(($total ? round(($total - $pending) / $total * 100) : 0) >= 90 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($total ? round(($total - $pending) / $total * 100) : 0) >= 90 ? 'done' : ''); ?>">Maintain 90%+ reply rate</span>
                        </div>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($categories[0]['stats']['Negative reviews'] ?? 0) === 0 ? 'done' : 'pending'); ?>"><?php echo e(($categories[0]['stats']['Negative reviews'] ?? 0) === 0 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($categories[0]['stats']['Negative reviews'] ?? 0) === 0 ? 'done' : ''); ?>">Address all negative reviews</span>
                        </div>
                    <?php elseif($cat['key'] === 'posts'): ?>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($cat['stats']['Total posts'] ?? 0) > 0 ? 'done' : 'pending'); ?>"><?php echo e(($cat['stats']['Total posts'] ?? 0) > 0 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($cat['stats']['Total posts'] ?? 0) > 0 ? 'done' : ''); ?>">Create your first Google post</span>
                        </div>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($cat['stats']['Published'] ?? 0) >= 4 ? 'done' : 'pending'); ?>"><?php echo e(($cat['stats']['Published'] ?? 0) >= 4 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($cat['stats']['Published'] ?? 0) >= 4 ? 'done' : ''); ?>">Publish at least 4 posts per month</span>
                        </div>
                    <?php elseif($cat['key'] === 'photos'): ?>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($cat['stats']['Photos uploaded'] ?? 0) >= 10 ? 'done' : 'pending'); ?>"><?php echo e(($cat['stats']['Photos uploaded'] ?? 0) >= 10 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($cat['stats']['Photos uploaded'] ?? 0) >= 10 ? 'done' : ''); ?>">Upload at least 10 business photos</span>
                        </div>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($cat['stats']['Photos uploaded'] ?? 0) >= 3 ? 'done' : 'pending'); ?>"><?php echo e(($cat['stats']['Photos uploaded'] ?? 0) >= 3 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($cat['stats']['Photos uploaded'] ?? 0) >= 3 ? 'done' : ''); ?>">Add photos for all key areas</span>
                        </div>
                    <?php elseif($cat['key'] === 'social'): ?>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($cat['stats']['Total posts'] ?? 0) > 0 ? 'done' : 'pending'); ?>"><?php echo e(($cat['stats']['Total posts'] ?? 0) > 0 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($cat['stats']['Total posts'] ?? 0) > 0 ? 'done' : ''); ?>">Create social media content</span>
                        </div>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($cat['stats']['Drafts'] ?? 0) === 0 ? 'done' : 'pending'); ?>"><?php echo e(($cat['stats']['Drafts'] ?? 0) === 0 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($cat['stats']['Drafts'] ?? 0) === 0 ? 'done' : ''); ?>">Publish all draft posts</span>
                        </div>
                    <?php elseif($cat['key'] === 'leads'): ?>
                        <div class="opt-check">
                            <div class="opt-check-dot <?php echo e(($cat['stats']['Needs follow-up'] ?? 0) === 0 ? 'done' : 'pending'); ?>"><?php echo e(($cat['stats']['Needs follow-up'] ?? 0) === 0 ? '✓' : '!'); ?></div>
                            <span class="opt-check-text <?php echo e(($cat['stats']['Needs follow-up'] ?? 0) === 0 ? 'done' : ''); ?>">Follow up on all new leads</span>
                        </div>
                        <div class="opt-check">
                            <div class="opt-check-dot pending">!</div>
                            <span class="opt-check-text">Respond to leads within 5 minutes</span>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div class="opt-tip">
                    <div class="opt-tip-icon">💡</div>
                    <div>
                        <div class="opt-tip-label">AI Recommendation</div>
                        <div class="opt-tip-text"><?php echo e($cat['tip']); ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <div id="opt-results"></div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;

function selectCat(key, el){
    document.querySelectorAll('.opt-cat').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.opt-detail').forEach(d => d.classList.remove('show'));
    const detail = document.getElementById('detail-' + key);
    if(detail) detail.classList.add('show');
}

async function runOptimize(){
    const btn = document.getElementById('opt-btn');
    const box = document.getElementById('opt-results');
    btn.innerHTML = '<span class="spin"></span> Optimizing…';
    btn.disabled = true;
    box.innerHTML = '<div class="opt-results-card" style="text-align:center;padding:28px;"><div style="width:28px;height:28px;border:3px solid var(--line);border-top-color:var(--teal);border-radius:50%;animation:sp .6s linear infinite;margin:0 auto 12px;"></div><div style="color:var(--muted);font-size:13px;">AI is working through your tasks…</div></div>';

    try{
        const res = await fetch("<?php echo e(route('optimize.run')); ?>", {
            method:'POST',
            headers:{'X-CSRF-TOKEN': csrf, 'Content-Type':'application/json'}
        });
        const data = await res.json();
        if(data.error){
            box.innerHTML = '<div class="opt-results-card" style="border-left:3px solid var(--rose);"><strong style="color:var(--rose);">Error:</strong> ' + escapeHtml(data.error) + '</div>';
            return;
        }
        renderActions(data.actions || []);
    } catch(e){
        box.innerHTML = '<div class="opt-results-card" style="border-left:3px solid var(--rose);">Something went wrong. Please try again.</div>';
    } finally {
        btn.innerHTML = '⚡ Run Full Optimization';
        btn.disabled = false;
    }
}

function renderActions(actions){
    const box = document.getElementById('opt-results');
    if(!actions.length){ box.innerHTML = ''; return; }
    let html = '<div class="opt-results-card"><div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;"><div style="width:30px;height:30px;border-radius:8px;background:var(--green-soft);color:var(--green);display:grid;place-items:center;font-weight:700;font-size:14px;">✓</div><div style="font-weight:700;font-size:16px;">Optimization Complete</div></div>';
    actions.forEach((a, i) => {
        html += '<div class="opt-result-item">'
            + '<div class="opt-result-icon">' + escapeHtml(a.icon || '✓') + '</div>'
            + '<div><div style="font-weight:600;font-size:13.5px;">' + escapeHtml(a.title || '') + '</div>'
            + '<div style="font-size:12.5px;color:var(--muted);margin-top:2px;">' + escapeHtml(a.detail || '') + '</div></div></div>';
    });
    html += '<div style="margin-top:14px;display:flex;gap:10px;">'
        + '<a href="<?php echo e(route("reviews")); ?>" class="btn btn-ghost" style="font-size:12.5px;">View Reviews</a>'
        + '<a href="<?php echo e(route("social")); ?>" class="btn btn-ghost" style="font-size:12.5px;">View Social</a></div></div>';
    box.innerHTML = html;
}

function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/dashboard/optimize.blade.php ENDPATH**/ ?>