<?php $__env->startSection('title', 'Local Rank Checker'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>Local Rank Checker</h1><p>Track your business position in local search results and benchmark against competitors.</p></div>
</div>

<?php if($locations->isEmpty()): ?>
    <div class="card"><div class="empty">No locations yet. Add a client with a Google location first — see <a href="<?php echo e(route('clients')); ?>">Clients</a>.</div></div>
<?php else: ?>
    <div class="card" style="margin-bottom:18px;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
            <label><span class="lbl">Your business</span>
                <select id="r-location">
                    <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($loc->id); ?>"><?php echo e($loc->title ?: $loc->google_name); ?> — <?php echo e($loc->client->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label><span class="lbl">Search keyword</span>
                <input type="text" id="r-keyword" placeholder="e.g. coffee shop, dentist, plumber">
            </label>
            <label><span class="lbl">Search radius</span>
                <select id="r-radius">
                    <option value="2">2 km</option>
                    <option value="5" selected>5 km</option>
                    <option value="10">10 km</option>
                    <option value="20">20 km</option>
                </select>
            </label>
        </div>
        <button class="btn" id="r-run" style="margin-top:16px;">📊 Check Ranking</button>
    </div>

    <div id="r-results"></div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;

document.getElementById('r-run')?.addEventListener('click', async () => {
    const location_id = document.getElementById('r-location').value;
    const keyword = document.getElementById('r-keyword').value.trim();
    const radius_km = document.getElementById('r-radius').value;
    const box = document.getElementById('r-results');

    if (!keyword) { alert('Enter a search keyword.'); return; }

    const btn = document.getElementById('r-run');
    btn.textContent = 'Checking…'; btn.disabled = true;
    box.innerHTML = '<div class="card"><div style="text-align:center;color:var(--muted);padding:20px;">Checking local search results…</div></div>';

    try {
        const res = await fetch("<?php echo e(route('rank-checker.check')); ?>", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
            body: JSON.stringify({location_id, keyword, radius_km})
        });
        const data = await res.json();
        if(res.status === 402 && window.handlePlanRequired(data)) return;
        if(data.error){ box.innerHTML = '<div class="alert error">' + data.error + '</div>'; return; }
        render(data);
    } catch (e) {
        box.innerHTML = '<div class="alert error">Could not check ranking. Try again.</div>';
    } finally {
        btn.textContent = '📊 Check Ranking'; btn.disabled = false;
    }
});

function render(d){
    const box = document.getElementById('r-results');

    if (d.source === 'places') {
        const rankText = d.rank ? '#' + d.rank : 'Not in top ' + d.checked;
        const rankColor = d.rank && d.rank <= 3 ? 'var(--teal)' : (d.rank && d.rank <= 10 ? 'var(--amber)' : 'var(--rose)');
        let html = '<div class="card" style="margin-bottom:14px;">'
            + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;"><strong>' + escapeHtml(d.business) + '</strong><span class="badge teal">✓ Real Google results</span></div>'
            + '<div style="display:flex;gap:24px;align-items:center;">'
            + '<div><div style="font-size:30px;font-weight:800;color:' + rankColor + ';">' + rankText + '</div><div style="font-size:11px;color:var(--muted);">for "' + escapeHtml(d.keyword) + '" within ' + d.radius_km + 'km</div></div>'
            + '</div></div>';

        if (d.results && d.results.length) {
            html += '<div class="card"><strong style="font-size:13.5px;">Top results</strong><div style="display:flex;flex-direction:column;gap:0;margin-top:10px;">';
            d.results.forEach((r, i) => {
                const mine = r.name.toLowerCase().includes(d.business.toLowerCase()) || d.business.toLowerCase().includes(r.name.toLowerCase());
                html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;' + (i > 0 ? 'border-top:1px solid var(--line);' : '') + (mine ? 'background:var(--teal-soft);margin:0 -18px;padding:10px 18px;' : '') + '">'
                    + '<div><span style="font-weight:600;font-size:13px;">#' + (i+1) + ' ' + escapeHtml(r.name) + '</span>' + (mine ? ' <span class="badge teal">You</span>' : '') + '<div style="font-size:11.5px;color:var(--muted);">' + escapeHtml(r.address||'') + '</div></div>'
                    + '<div style="font-size:12.5px;color:var(--muted);">' + (r.rating ? '★ ' + r.rating + ' (' + (r.ratings_total||0) + ')' : '—') + '</div>'
                    + '</div>';
            });
            html += '</div></div>';
        }
        box.innerHTML = html;
        return;
    }

    // AI-estimated fallback
    const badge = d.source === 'ai'
        ? '<span class="badge amber">✨ AI estimate (not live Google data)</span>'
        : '<span class="badge gray">⚡ Limited</span>';
    let html = '<div class="card" style="margin-bottom:14px;">'
        + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;"><strong>' + escapeHtml(d.business) + '</strong>' + badge + '</div>'
        + '<div style="font-size:22px;font-weight:700;color:var(--teal);margin:8px 0;">' + escapeHtml(d.estimated_rank) + '</div>'
        + '<p style="font-size:13.5px;line-height:1.6;color:#2a3a35;border-top:1px solid var(--line);padding-top:10px;">' + escapeHtml(d.summary||'') + '</p>';
    if (!d.results) {
        html += '<div style="font-size:12px;color:var(--muted);margin-top:10px;">For real ranking data (not an estimate), add GOOGLE_PLACES_API_KEY to your .env.</div>';
    }
    html += '</div>';

    if (d.tips && d.tips.length) {
        html += '<div class="card"><strong style="font-size:14px;">✦ Tips to improve ranking</strong><ul style="list-style:none;display:flex;flex-direction:column;gap:8px;margin-top:10px;">';
        d.tips.forEach(t => html += '<li style="font-size:13px;color:#2a3a35;">• ' + escapeHtml(t) + '</li>');
        html += '</ul></div>';
    }
    box.innerHTML = html;
}

function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views/dashboard/rank-checker.blade.php ENDPATH**/ ?>