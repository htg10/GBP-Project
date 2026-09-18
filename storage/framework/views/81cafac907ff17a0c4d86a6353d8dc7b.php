<?php $__env->startSection('title', 'Competitor Analysis'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Competitor Analysis</h1><p>See how a client stacks up and get an AI strategy to win locally.</p></div>
</div>

<?php if($clients->isEmpty()): ?>
    <div class="card"><div class="empty">No clients yet. Add a client first.</div></div>
<?php else: ?>
    <div class="card" style="margin-bottom:18px;">
        <label><span class="lbl">Your client</span>
            <select id="c-client"><?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
        </label>
        <label><span class="lbl">Competitors (comma separated)</span>
            <input type="text" id="c-competitors" placeholder="e.g. Smile Care Dental, City Dental Clinic">
        </label>
        <label><span class="lbl">City (optional)</span>
            <input type="text" id="c-city" placeholder="e.g. Ghaziabad">
        </label>
        <button class="btn" id="c-run" style="margin-top:16px;">⚔ Analyze</button>
    </div>

    <div id="c-results"></div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;

document.getElementById('c-run')?.addEventListener('click', async () => {
    const clientId = document.getElementById('c-client').value;
    const competitors = document.getElementById('c-competitors').value.trim();
    const city = document.getElementById('c-city').value.trim();
    const box = document.getElementById('c-results');

    if (!competitors) { alert('Enter at least one competitor.'); return; }

    const btn = document.getElementById('c-run');
    btn.textContent = 'Analyzing…'; btn.disabled = true;
    box.innerHTML = '<div class="card"><div style="text-align:center;color:var(--muted);padding:20px;">AI is analyzing the competition…</div></div>';

    try {
        const res = await fetch("<?php echo e(route('competitors.run')); ?>", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
            body: JSON.stringify({client_id: clientId, competitors, city})
        });
        const data = await res.json();
        render(data);
    } catch (e) {
        box.innerHTML = '<div class="alert error">Could not analyze. Try again.</div>';
    } finally {
        btn.textContent = '⚔ Analyze'; btn.disabled = false;
    }
});

function listCard(title, items, icon, tint){
    let h = '<div class="card"><div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">'
        + '<span style="width:26px;height:26px;border-radius:7px;background:' + tint + ';display:grid;place-items:center;font-size:13px;">' + icon + '</span>'
        + '<strong style="font-size:14px;">' + title + '</strong></div><ul style="list-style:none;display:flex;flex-direction:column;gap:8px;">';
    (items||[]).forEach(i => h += '<li style="font-size:13px;color:#2a3a35;line-height:1.5;">• ' + escapeHtml(i) + '</li>');
    h += '</ul></div>';
    return h;
}

function render(d){
    const box = document.getElementById('c-results');
    const badge = d.source === 'ai'
        ? '<span class="badge teal">✨ AI analysis</span>'
        : '<span class="badge amber">⚡ Limited (AI unavailable)</span>';

    let html = '<div class="card" style="margin-bottom:14px;">'
        + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;"><strong>' + escapeHtml(d.my_stats.name) + ' vs competitors</strong>' + badge + '</div>'
        + '<div style="display:flex;gap:20px;margin:10px 0;">'
        + '<div><div style="font-size:22px;font-weight:700;color:var(--teal);">' + d.my_stats.avg + '</div><div style="font-size:11px;color:var(--muted);">Your rating</div></div>'
        + '<div><div style="font-size:22px;font-weight:700;">' + d.my_stats.total + '</div><div style="font-size:11px;color:var(--muted);">Your reviews</div></div>'
        + '<div style="flex:1;"><div style="font-size:12px;color:var(--muted);margin-bottom:3px;">Compared against</div><div style="font-size:13px;">' + (d.competitors||[]).map(escapeHtml).join(', ') + '</div></div>'
        + '</div>'
        + '<p style="font-size:13.5px;line-height:1.6;color:#2a3a35;border-top:1px solid var(--line);padding-top:10px;">' + escapeHtml(d.summary||'') + '</p></div>';

    html += '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">';
    html += listCard('Your strengths', d.strengths, '✓', 'var(--teal-soft)');
    html += listCard('Gaps to close', d.gaps, '△', 'var(--amber-soft)');
    html += '</div>';
    html += '<div style="margin-top:14px;">' + listCard('✦ Action plan to win locally', d.actions, '→', 'var(--teal-soft)') + '</div>';

    box.innerHTML = html;
}

function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/dashboard/competitors.blade.php ENDPATH**/ ?>