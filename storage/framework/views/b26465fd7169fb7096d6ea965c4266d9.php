<?php $__env->startSection('title', 'Google Audit'); ?>
<?php $__env->startSection('content'); ?>

<?php $__env->startPush('head'); ?>
<style>
    .au-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:14px;flex-wrap:wrap;}
    .au-header h1{font-size:22px;font-weight:700;letter-spacing:-.02em;}
    .au-header p{font-size:13px;color:var(--muted);margin-top:3px;}

    .au-controls{display:flex;gap:12px;align-items:flex-end;margin-bottom:20px;flex-wrap:wrap;}
    .au-controls label{flex:1;min-width:200px;}
    .au-controls .btn{height:42px;padding:0 24px;flex-shrink:0;}

    /* Score donut */
    .au-score-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;}
    .au-donut{position:relative;width:180px;height:180px;}
    .au-donut svg{width:100%;height:100%;transform:rotate(-90deg);}
    .au-donut circle{fill:none;stroke-linecap:round;}
    .au-donut .bg{stroke:var(--line);stroke-width:12;}
    .au-donut .fg{stroke-width:12;transition:stroke-dashoffset 1s ease,stroke .3s;}
    .au-donut-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .au-donut-score{font-size:42px;font-weight:800;line-height:1;}
    .au-donut-grade{font-size:13px;font-weight:700;margin-top:2px;}
    .au-score-stats{display:flex;gap:24px;margin-top:18px;padding-top:14px;border-top:1px solid var(--line);width:100%;}
    .au-score-stat{text-align:center;flex:1;}
    .au-score-stat .v{font-size:20px;font-weight:800;}
    .au-score-stat .l{font-size:11px;color:var(--muted);margin-top:1px;}

    /* Category bars */
    .au-cats{display:flex;flex-direction:column;gap:14px;}
    .au-cat{display:flex;align-items:center;gap:12px;}
    .au-cat-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:15px;flex-shrink:0;}
    .au-cat-info{flex:1;min-width:0;}
    .au-cat-label{font-size:13px;font-weight:600;margin-bottom:4px;}
    .au-cat-bar{height:6px;border-radius:6px;background:var(--line);overflow:hidden;}
    .au-cat-bar-fill{height:100%;border-radius:6px;transition:width .8s ease;}
    .au-cat-pct{font-size:13px;font-weight:700;min-width:40px;text-align:right;}

    /* Check cards */
    .au-checks{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .au-check{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px 18px;box-shadow:var(--shadow);display:flex;gap:12px;align-items:flex-start;transition:border-color .15s;}
    .au-check:hover{border-color:#d0d5e0;}
    .au-check-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:14px;font-weight:700;flex-shrink:0;}
    .au-check-icon.pass{background:var(--green-soft);color:var(--green);}
    .au-check-icon.fail{background:var(--rose-soft);color:var(--rose);}
    .au-check-icon.warn{background:var(--amber-soft);color:var(--amber);}
    .au-check-body{flex:1;min-width:0;}
    .au-check-title{font-size:13.5px;font-weight:600;margin-bottom:3px;display:flex;align-items:center;gap:8px;}
    .au-check-note{font-size:12.5px;color:var(--muted);line-height:1.45;}
    .au-check-bar{height:4px;border-radius:4px;background:var(--line);margin-top:8px;overflow:hidden;}
    .au-check-bar-fill{height:100%;border-radius:4px;}

    /* Filter chips */
    .au-filters{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;}
    .au-chip{border:1px solid var(--line);border-radius:999px;padding:6px 14px;font-size:12.5px;font-weight:500;background:var(--card);color:var(--muted);cursor:pointer;transition:all .15s;font-family:inherit;border:1px solid var(--line);}
    .au-chip:hover{border-color:var(--teal);color:var(--teal);}
    .au-chip.active{background:var(--ink);color:#fff;border-color:var(--ink);}

    /* AI tips */
    .au-tips{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow);border-left:3px solid var(--teal);}
    .au-tips h4{font-size:15px;font-weight:700;margin-bottom:10px;display:flex;align-items:center;gap:8px;}
    .au-tips-text{font-size:13.5px;line-height:1.7;color:var(--ink);}
    .au-tips-text ul{margin:6px 0 0 4px;padding-left:16px;}
    .au-tips-text li{margin-bottom:6px;}

    /* Loading / empty */
    .au-loading{text-align:center;padding:48px 20px;}
    .au-loading .spin{width:36px;height:36px;border:3px solid var(--line);border-top-color:var(--teal);border-radius:50%;animation:sp .6s linear infinite;margin:0 auto 14px;}

    .au-empty-state{text-align:center;padding:60px 20px;}
    .au-empty-icon{width:72px;height:72px;border-radius:50%;background:var(--teal-soft);color:var(--teal);display:grid;place-items:center;font-size:30px;margin:0 auto 16px;}
    .au-empty-state h3{font-size:17px;font-weight:700;margin-bottom:6px;}
    .au-empty-state p{font-size:13.5px;color:var(--muted);max-width:380px;margin:0 auto;}

    @keyframes sp{to{transform:rotate(360deg)}}
    @media (max-width:700px){
        .au-checks{grid-template-columns:1fr;}
        .au-top-grid{grid-template-columns:1fr !important;}
        .au-score-stats{gap:14px;}
    }
</style>
<?php $__env->stopPush(); ?>

<div class="au-header">
    <div>
        <h1>Google Audit</h1>
        <p>Score your Google Business Profile health and get AI-powered fixes.</p>
    </div>
</div>

<?php if($clients->isEmpty()): ?>
    <div class="card">
        <div class="au-empty-state">
            <div class="au-empty-icon">📊</div>
            <h3>No Clients Yet</h3>
            <p>Add a client first, then run an audit to score your Google Business Profile health.</p>
            <a href="<?php echo e(route('clients')); ?>" class="btn" style="margin-top:16px;">+ Add Client</a>
        </div>
    </div>
<?php else: ?>
    <div class="au-controls">
        <?php if($clients->count() > 1): ?>
        <label>
            <span class="lbl">Business Location</span>
            <select id="audit-client">
                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?> (<?php echo e($c->locations_count); ?> location<?php echo e($c->locations_count !== 1 ? 's' : ''); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <?php else: ?>
        <input type="hidden" id="audit-client" value="<?php echo e($clients->first()->id); ?>">
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="avatar" style="background:var(--teal-soft);color:var(--teal-ink);"><?php echo e(strtoupper(substr($clients->first()->name, 0, 1))); ?></div>
            <div>
                <div style="font-weight:700;font-size:14px;"><?php echo e($clients->first()->name); ?></div>
                <div style="font-size:12px;color:var(--muted);"><?php echo e($clients->first()->locations_count); ?> location<?php echo e($clients->first()->locations_count !== 1 ? 's' : ''); ?></div>
            </div>
        </div>
        <?php endif; ?>
        <button class="btn" id="run-btn">📊 Analyze Profile</button>
    </div>

    
    <div id="audit-placeholder">
        <div class="card">
            <div class="au-empty-state">
                <div class="au-empty-icon">📊</div>
                <h3>Ready to Audit</h3>
                <p>Select a business location above and click "Analyze Profile" to get a comprehensive health score with AI recommendations.</p>
            </div>
        </div>
    </div>

    <div id="audit-results" style="display:none;"></div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
let auditData = null;

document.getElementById('run-btn')?.addEventListener('click', async () => {
    const clientId = document.getElementById('audit-client').value;
    const box = document.getElementById('audit-results');
    const placeholder = document.getElementById('audit-placeholder');
    const btn = document.getElementById('run-btn');

    btn.innerHTML = '<span class="spin" style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:sp .6s linear infinite;vertical-align:middle;"></span> Analyzing…';
    btn.disabled = true;
    placeholder.style.display = 'none';
    box.style.display = '';
    box.innerHTML = '<div class="card"><div class="au-loading"><div class="spin"></div><div style="font-size:14px;font-weight:600;margin-bottom:4px;">Running Comprehensive Audit</div><div style="font-size:13px;color:var(--muted);">Analyzing reviews, ratings, and profile health…</div></div></div>';

    try {
        const res = await fetch("<?php echo e(route('audit.run')); ?>", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
            body: JSON.stringify({client_id: clientId})
        });
        const data = await res.json();
        if (res.status === 402 && window.handlePlanRequired(data)) return;
        if (data.error) {
            box.innerHTML = '<div class="card" style="border-left:3px solid var(--rose);padding:18px;"><strong style="color:var(--rose);">Error:</strong> ' + esc(data.error) + '</div>';
            return;
        }
        auditData = data;
        renderAudit(data, 'all');
    } catch (e) {
        box.innerHTML = '<div class="card" style="border-left:3px solid var(--rose);padding:18px;">Could not run audit. Please try again.</div>';
    } finally {
        btn.innerHTML = '📊 Analyze Profile';
        btn.disabled = false;
    }
});

function getColor(score){
    if (score >= 80) return 'var(--green)';
    if (score >= 60) return 'var(--teal)';
    if (score >= 40) return 'var(--amber)';
    return 'var(--rose)';
}

function renderAudit(d, filter){
    const box = document.getElementById('audit-results');
    const color = getColor(d.score);
    const circ = 2 * Math.PI * 76;
    const offset = circ - (circ * d.score / 100);

    const cats = [
        {label:'Profile Setup', icon:'📍', bg:'var(--teal-soft)', pct: d.checks[0]?.ok ? 100 : 30},
        {label:'Review Volume', icon:'✉', bg:'var(--green-soft)', pct: Math.min(100, d.stats.total >= 20 ? 100 : Math.round(d.stats.total / 20 * 100))},
        {label:'Average Rating', icon:'★', bg:'var(--amber-soft)', pct: Math.min(100, Math.round((d.stats.avg || 0) / 5 * 100))},
        {label:'Reply Rate', icon:'💬', bg:'var(--teal-soft)', pct: Math.min(100, d.stats.reply_rate || 0)},
        {label:'Reputation', icon:'🛡', bg:'var(--green-soft)', pct: d.stats.negative === 0 ? 100 : (d.checks[4]?.ok ? 90 : 40)}
    ];

    let html = '';

    // Top row: Score donut + Category breakdown
    html += '<div class="au-top-grid" style="display:grid;grid-template-columns:340px 1fr;gap:16px;margin-bottom:18px;">';

    // Score donut card
    html += '<div class="card"><div class="au-score-wrap">'
        + '<div class="au-donut">'
        + '<svg viewBox="0 0 180 180"><circle class="bg" cx="90" cy="90" r="76"/>'
        + '<circle class="fg" cx="90" cy="90" r="76" stroke="' + color + '" stroke-dasharray="' + circ + '" stroke-dashoffset="' + offset + '"/></svg>'
        + '<div class="au-donut-center"><div class="au-donut-score" style="color:' + color + ';">' + d.score + '</div>'
        + '<div class="au-donut-grade" style="color:' + color + ';">' + esc(d.grade) + '</div></div></div>'
        + '<div class="au-score-stats">'
        + '<div class="au-score-stat"><div class="v">' + d.stats.avg + '</div><div class="l">Avg Rating</div></div>'
        + '<div class="au-score-stat"><div class="v">' + d.stats.reply_rate + '%</div><div class="l">Reply Rate</div></div>'
        + '<div class="au-score-stat"><div class="v">' + d.stats.total + '</div><div class="l">Reviews</div></div>'
        + '</div></div></div>';

    // Category breakdown card
    html += '<div class="card" style="display:flex;flex-direction:column;justify-content:center;">'
        + '<div style="font-weight:700;font-size:16px;margin-bottom:16px;">Score Breakdown</div>'
        + '<div class="au-cats">';
    cats.forEach(c => {
        const col = getColor(c.pct);
        html += '<div class="au-cat">'
            + '<div class="au-cat-icon" style="background:' + c.bg + ';">' + c.icon + '</div>'
            + '<div class="au-cat-info"><div class="au-cat-label">' + c.label + '</div>'
            + '<div class="au-cat-bar"><div class="au-cat-bar-fill" style="width:' + c.pct + '%;background:' + col + ';"></div></div></div>'
            + '<div class="au-cat-pct" style="color:' + col + ';">' + c.pct + '%</div></div>';
    });
    html += '</div></div></div>';

    // Filter chips
    html += '<div class="au-filters">';
    ['All','Pass','Warning','Fail'].forEach(f => {
        const key = f.toLowerCase();
        const active = filter === key ? ' active' : '';
        html += '<button class="au-chip' + active + '" onclick="filterChecks(\'' + key + '\')">' + f + '</button>';
    });
    html += '</div>';

    // Detailed check cards
    html += '<div class="au-checks">';
    let visible = 0;
    d.checks.forEach(c => {
        const status = c.ok ? 'pass' : (c.weight >= 20 ? 'fail' : 'warning');
        if (filter !== 'all' && status !== filter) return;
        visible++;

        const iconCls = c.ok ? 'pass' : (c.weight >= 20 ? 'fail' : 'warn');
        const icon = c.ok ? '✓' : (c.weight >= 20 ? '✕' : '!');
        const barColor = c.ok ? 'var(--green)' : (c.weight >= 20 ? 'var(--rose)' : 'var(--amber)');
        const barPct = c.ok ? 100 : (c.weight >= 20 ? 20 : 50);
        const weightLabel = c.weight >= 20 ? 'High impact' : 'Medium impact';

        html += '<div class="au-check">'
            + '<div class="au-check-icon ' + iconCls + '">' + icon + '</div>'
            + '<div class="au-check-body">'
            + '<div class="au-check-title">' + esc(c.label)
            + '<span class="badge ' + (c.ok ? 'teal' : (c.weight >= 20 ? 'rose' : 'amber')) + '" style="font-size:10px;padding:1px 7px;">' + weightLabel + '</span></div>'
            + '<div class="au-check-note">' + esc(c.note) + '</div>'
            + '<div class="au-check-bar"><div class="au-check-bar-fill" style="width:' + barPct + '%;background:' + barColor + ';"></div></div>'
            + '</div></div>';
    });
    if (visible === 0) {
        html += '<div class="card" style="grid-column:1/-1;text-align:center;padding:24px;color:var(--muted);">No checks match this filter.</div>';
    }
    html += '</div>';

    // AI Recommendations
    html += '<div class="au-tips" style="margin-top:18px;">'
        + '<h4>✦ AI Recommendations</h4>'
        + '<div class="au-tips-text">' + formatTips(d.ai_tips || 'No AI recommendations available.') + '</div></div>';

    box.innerHTML = html;
}

function filterChecks(f){
    if (auditData) renderAudit(auditData, f);
}

function formatTips(text){
    let html = esc(text)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    const lines = html.split('\n');
    let result = '';
    let inList = false;
    for (const line of lines){
        const trimmed = line.trim();
        const m = trimmed.match(/^(\d+\.|[-•*])\s+(.*)/);
        if (m){
            if (!inList){ result += '<ul>'; inList = true; }
            result += '<li>' + m[2] + '</li>';
        } else {
            if (inList){ result += '</ul>'; inList = false; }
            if (trimmed) result += '<p style="margin:0 0 6px;">' + trimmed + '</p>';
        }
    }
    if (inList) result += '</ul>';
    return result;
}

function esc(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

<?php if($autoClient): ?>
document.addEventListener('DOMContentLoaded', () => document.getElementById('run-btn')?.click());
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\audit.blade.php ENDPATH**/ ?>