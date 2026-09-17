@extends('layouts.app')
@section('title', 'Google Audit')
@section('content')

<div class="page-head">
    <div><h1>Google Audit</h1><p>Score your Google Business Profile health and get AI-powered fixes.</p></div>
</div>

@if($clients->isEmpty())
    <div class="card"><div class="empty">No clients yet. <a href="{{ route('clients') }}" style="color:var(--teal);">Add a client</a> first, then run an audit.</div></div>
@else
    {{-- Controls --}}
    <div class="card" style="margin-bottom:16px;">
        <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <label style="flex:1;min-width:200px;">
                <span class="lbl">Business Location</span>
                <select id="audit-client" style="width:100%;">
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->locations_count }} location{{ $c->locations_count !== 1 ? 's' : '' }})</option>
                    @endforeach
                </select>
            </label>
            <button class="btn" id="run-btn" style="height:40px;padding:0 22px;">
                <span style="display:inline-flex;align-items:center;gap:6px;">&#9678; Analyze Profile</span>
            </button>
        </div>
    </div>

    {{-- Results --}}
    <div id="audit-results"></div>
@endif

@push('head')
<style>
    .audit-cat{display:flex;flex-direction:column;gap:8px;margin-top:14px;}
    .audit-cat-item{display:flex;align-items:center;gap:12px;}
    .audit-cat-label{font-size:13px;font-weight:600;min-width:140px;}
    .audit-bar{flex:1;height:8px;background:#f3f1ea;border-radius:6px;overflow:hidden;}
    .audit-bar-fill{height:100%;border-radius:6px;transition:width .5s;}
    .audit-pct{font-size:12px;font-weight:700;min-width:36px;text-align:right;}
    .qw-item{display:flex;gap:12px;padding:12px 0;}
    .qw-item+.qw-item{border-top:1px solid var(--line);}
    .qw-num{width:28px;height:28px;border-radius:8px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-weight:700;font-size:12px;flex-shrink:0;}
    .filter-badge{display:inline-flex;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid var(--line);background:var(--card);color:var(--muted);transition:all .15s;}
    .filter-badge.active{background:var(--ink);color:#fff;border-color:var(--ink);}
    @media (max-width:700px){
        .audit-grid{grid-template-columns:1fr !important;}
    }
</style>
@endpush

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
let auditData = null;

document.getElementById('run-btn')?.addEventListener('click', async () => {
    const clientId = document.getElementById('audit-client').value;
    const box = document.getElementById('audit-results');
    const btn = document.getElementById('run-btn');
    btn.innerHTML = '<span class="spin" style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:sp .6s linear infinite;"></span> Analyzing&hellip;';
    btn.disabled = true;
    box.innerHTML = '<div class="card" style="text-align:center;padding:32px;"><div class="spin" style="display:inline-block;width:32px;height:32px;border:3px solid var(--line);border-top-color:var(--teal);border-radius:50%;animation:sp .6s linear infinite;margin-bottom:10px;"></div><div style="color:var(--muted);font-size:13px;">Running comprehensive audit&hellip;</div></div>';

    try {
        const res = await fetch("{{ route('audit.run') }}", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
            body: JSON.stringify({client_id: clientId})
        });
        const data = await res.json();
        if (data.error) { box.innerHTML = '<div class="card" style="border-left:3px solid var(--rose);"><strong style="color:var(--rose);">Error:</strong> ' + esc(data.error) + '</div>'; return; }
        auditData = data;
        render(data, 'all');
    } catch (e) {
        box.innerHTML = '<div class="card" style="border-left:3px solid var(--rose);">Could not run audit. Try again.</div>';
    } finally {
        btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;">&#9678; Analyze Profile</span>'; btn.disabled = false;
    }
});

function getColor(score){
    return score >= 85 ? 'var(--teal)' : (score >= 65 ? '#3a8a2a' : (score >= 40 ? 'var(--amber)' : 'var(--rose)'));
}

function render(d, filter){
    const box = document.getElementById('audit-results');
    const color = getColor(d.score);

    let html = '';

    // Top row: score donut + business details
    html += '<div class="audit-grid" style="display:grid;grid-template-columns:320px 1fr;gap:16px;margin-bottom:16px;">';

    // Score card
    html += '<div class="card" style="text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;">'
        + '<div style="position:relative;width:160px;height:160px;">'
        + '<div style="width:160px;height:160px;border-radius:50%;background:conic-gradient(' + color + ' ' + (d.score*3.6) + 'deg, #f3f1ea 0deg);display:grid;place-items:center;">'
        + '<div style="width:126px;height:126px;border-radius:50%;background:var(--card);display:grid;place-items:center;">'
        + '<div><div style="font-size:38px;font-weight:800;color:' + color + ';">' + d.score + '%</div>'
        + '<div style="font-size:12px;color:' + color + ';font-weight:600;">' + esc(d.grade) + '</div></div>'
        + '</div></div></div>'
        + '<div style="display:flex;justify-content:center;gap:20px;margin-top:16px;padding-top:14px;border-top:1px solid var(--line);width:100%;">'
        + '<div><div style="font-weight:700;font-size:18px;">' + d.stats.avg + '</div><div style="font-size:11px;color:var(--muted);">Avg Rating</div></div>'
        + '<div><div style="font-weight:700;font-size:18px;">' + d.stats.reply_rate + '%</div><div style="font-size:11px;color:var(--muted);">Reply Rate</div></div>'
        + '<div><div style="font-weight:700;font-size:18px;">' + d.stats.total + '</div><div style="font-size:11px;color:var(--muted);">Reviews</div></div>'
        + '</div></div>';

    // Business details + Score by category
    html += '<div style="display:flex;flex-direction:column;gap:16px;">';

    // Score by Category
    const cats = [
        {label:'Profile Completeness', pct: d.checks[0].ok ? 100 : 30},
        {label:'Customer Engagement', pct: Math.min(100, Math.round((d.stats.reply_rate || 0)))},
        {label:'Review Volume', pct: Math.min(100, d.stats.total >= 20 ? 100 : Math.round(d.stats.total / 20 * 100))},
        {label:'Reputation Score', pct: Math.min(100, Math.round((d.stats.avg || 0) / 5 * 100))},
        {label:'Negative Handling', pct: d.stats.negative === 0 ? 100 : (d.checks[4] && d.checks[4].ok ? 90 : 40)}
    ];
    html += '<div class="card"><div style="font-weight:700;font-size:15px;margin-bottom:4px;">Score by Category</div><div class="audit-cat">';
    cats.forEach(c => {
        const col = c.pct >= 80 ? 'var(--teal)' : (c.pct >= 50 ? 'var(--amber)' : 'var(--rose)');
        html += '<div class="audit-cat-item"><span class="audit-cat-label">' + c.label + '</span>'
            + '<div class="audit-bar"><div class="audit-bar-fill" style="width:' + c.pct + '%;background:' + col + ';"></div></div>'
            + '<span class="audit-pct" style="color:' + col + ';">' + c.pct + '%</span></div>';
    });
    html += '</div></div>';

    html += '</div></div>';

    // Filter badges
    html += '<div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">';
    ['All','Poor','Warning','Good'].forEach(f => {
        const active = filter === f.toLowerCase() ? ' active' : '';
        html += '<button class="filter-badge' + active + '" onclick="filterChecks(\'' + f.toLowerCase() + '\')">' + f + '</button>';
    });
    html += '</div>';

    // Quick Wins + AI Recommendations
    html += '<div class="audit-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">';

    // Quick wins
    html += '<div class="card"><div style="font-weight:700;font-size:15px;margin-bottom:4px;">Quick Wins</div>';
    let num = 0;
    d.checks.forEach(c => {
        const status = c.ok ? 'good' : (c.weight >= 20 ? 'poor' : 'warning');
        if (filter !== 'all' && status !== filter) return;
        num++;
        const bg = c.ok ? 'var(--teal-soft)' : (c.weight >= 20 ? 'var(--rose-soft)' : 'var(--amber-soft)');
        const col = c.ok ? 'var(--teal-ink)' : (c.weight >= 20 ? 'var(--rose)' : '#8a5a08');
        const icon = c.ok ? '&#10003;' : '&#10007;';
        const pct = c.ok ? 100 : (c.weight >= 20 ? 20 : 50);
        html += '<div class="qw-item">'
            + '<div class="qw-num" style="background:' + bg + ';color:' + col + ';">' + num + '</div>'
            + '<div style="flex:1;"><div style="display:flex;justify-content:space-between;align-items:center;">'
            + '<span style="font-weight:600;font-size:13px;">' + esc(c.label) + '</span>'
            + '<span style="font-size:11px;font-weight:600;color:' + col + ';">' + icon + '</span></div>'
            + '<div style="font-size:12px;color:var(--muted);margin-top:3px;">' + esc(c.note) + '</div>'
            + '<div style="margin-top:6px;background:#f3f1ea;border-radius:4px;height:5px;overflow:hidden;">'
            + '<div style="width:' + pct + '%;height:100%;background:' + col + ';border-radius:4px;"></div></div>'
            + '</div></div>';
    });
    if (num === 0) html += '<div style="padding:16px 0;color:var(--muted);font-size:13px;text-align:center;">No items match this filter.</div>';
    html += '</div>';

    // AI recommendations
    html += '<div class="card" style="border-left:3px solid var(--teal);">'
        + '<div style="font-weight:700;font-size:15px;margin-bottom:10px;">&#10024; AI Recommendations</div>'
        + '<div style="font-size:13.5px;line-height:1.7;color:var(--ink);white-space:pre-wrap;">' + esc(d.ai_tips || 'No AI recommendations available.') + '</div>'
        + '</div>';

    html += '</div>';

    box.innerHTML = html;
}

function filterChecks(f){
    if (auditData) render(auditData, f);
}

function esc(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
<style>@keyframes sp{to{transform:rotate(360deg)}}</style>
@endpush
@endsection
