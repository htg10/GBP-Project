@extends('layouts.app')
@section('title', 'One-Click Optimize')
@section('content')

<div class="page-head">
    <div><h1>One-Click Optimization</h1><p>Let AI handle all pending tasks in a single click.</p></div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;">
    {{-- Main content --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Hero card --}}
        <div class="card" style="background:linear-gradient(135deg,#7c3aed 0%,#a78bfa 50%,#c084fc 100%);color:#fff;border:none;">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
                <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:24px;backdrop-filter:blur(8px);">&#9889;</div>
                <div>
                    <div style="font-size:18px;font-weight:800;">AI-Powered Optimization</div>
                    <div style="font-size:13px;opacity:.85;">Drafts replies, generates posts, and flags issues automatically</div>
                </div>
            </div>
            <div style="display:flex;gap:16px;margin-bottom:18px;">
                <div style="background:rgba(255,255,255,.12);border-radius:12px;padding:14px 20px;flex:1;backdrop-filter:blur(8px);">
                    <div style="font-size:28px;font-weight:800;">{{ $pending }}</div>
                    <div style="font-size:12px;opacity:.8;">Pending Reviews</div>
                </div>
                <div style="background:rgba(255,255,255,.12);border-radius:12px;padding:14px 20px;flex:1;backdrop-filter:blur(8px);">
                    <div style="font-size:28px;font-weight:800;">{{ $total }}</div>
                    <div style="font-size:12px;opacity:.8;">Total Reviews</div>
                </div>
                <div style="background:rgba(255,255,255,.12);border-radius:12px;padding:14px 20px;flex:1;backdrop-filter:blur(8px);">
                    <div style="font-size:28px;font-weight:800;">{{ $total ? round(($total - $pending) / $total * 100) : 0 }}%</div>
                    <div style="font-size:12px;opacity:.8;">Reply Rate</div>
                </div>
            </div>
            <button class="btn" id="opt-btn" style="background:#fff;color:#7c3aed;padding:13px 28px;font-size:14px;font-weight:700;border:none;width:100%;justify-content:center;">&#9889; Run Full Optimization</button>
        </div>

        {{-- What AI will do --}}
        <div class="card">
            <div style="font-weight:700;font-size:15px;margin-bottom:14px;">What AI will do</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <div style="width:36px;height:36px;border-radius:10px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;flex-shrink:0;font-size:15px;">&#9733;</div>
                    <div><div style="font-weight:600;font-size:13.5px;">Reply to all pending reviews</div><div style="font-size:12.5px;color:var(--muted);margin-top:2px;">AI drafts professional, personalized responses for {{ $pending }} review{{ $pending !== 1 ? 's' : '' }}</div></div>
                </div>
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <div style="width:36px;height:36px;border-radius:10px;background:var(--amber-soft);color:#8a5a08;display:grid;place-items:center;flex-shrink:0;font-size:15px;">&#9670;</div>
                    <div><div style="font-weight:600;font-size:13.5px;">Generate social media content</div><div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Creates a fresh promotional post draft for your business</div></div>
                </div>
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <div style="width:36px;height:36px;border-radius:10px;background:var(--rose-soft);color:var(--rose);display:grid;place-items:center;flex-shrink:0;font-size:15px;">&#9660;</div>
                    <div><div style="font-weight:600;font-size:13.5px;">Flag negative reviews</div><div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Highlights reviews requiring personal follow-up attention</div></div>
                </div>
            </div>
        </div>

        {{-- Results area --}}
        <div id="opt-results"></div>
    </div>

    {{-- Sidebar --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="border-left:3px solid var(--amber);">
            <div style="font-weight:700;font-size:14px;margin-bottom:8px;">&#9888; Important Rules</div>
            <ul style="margin:0;padding-left:18px;font-size:13px;color:var(--muted);line-height:1.7;">
                <li>AI replies are posted automatically</li>
                <li>Review AI responses after optimization</li>
                <li>Negative reviews get extra careful handling</li>
                <li>Credits are consumed per optimization run</li>
            </ul>
        </div>

        <div class="card" style="border-left:3px solid var(--teal);">
            <div style="font-weight:700;font-size:14px;margin-bottom:8px;">&#10024; AI Suggestions</div>
            <ul style="margin:0;padding-left:18px;font-size:13px;color:var(--muted);line-height:1.7;">
                <li>Reply to reviews within 24 hours for better ranking</li>
                <li>Personalize responses with customer names</li>
                <li>Address negative feedback with empathy first</li>
                <li>Include keywords naturally in your replies</li>
            </ul>
        </div>

        <div class="card">
            <div style="font-weight:700;font-size:14px;margin-bottom:8px;">Current Profile</div>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
                <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Total reviews</span><strong>{{ $total }}</strong></div>
                <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Pending replies</span><strong style="color:var(--amber);">{{ $pending }}</strong></div>
                <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Reply rate</span><strong>{{ $total ? round(($total - $pending) / $total * 100) : 0 }}%</strong></div>
            </div>
            @if($pending > 0)
            <div style="margin-top:10px;background:#f3f1ea;border-radius:6px;height:6px;overflow:hidden;">
                @php $rp = $total ? round(($total - $pending) / $total * 100) : 0; @endphp
                <div style="background:{{ $rp > 70 ? 'var(--teal)' : ($rp > 40 ? 'var(--amber)' : 'var(--rose)') }};height:100%;width:{{ $rp }}%;border-radius:6px;"></div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('head')
<style>
    @media (max-width:800px){
        [style*="grid-template-columns:1fr 340px"]{grid-template-columns:1fr !important;}
    }
</style>
@endpush

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;

document.getElementById('opt-btn').addEventListener('click', async () => {
    const btn = document.getElementById('opt-btn');
    const box = document.getElementById('opt-results');
    btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;"><span class="spin" style="display:inline-block;width:16px;height:16px;border:2px solid rgba(124,58,237,.2);border-top-color:#7c3aed;border-radius:50%;animation:sp .6s linear infinite;"></span> Optimizing&hellip;</span>';
    btn.disabled = true;
    box.innerHTML = '<div class="card" style="text-align:center;padding:24px;"><div class="spin" style="display:inline-block;width:28px;height:28px;border:3px solid var(--line);border-top-color:var(--teal);border-radius:50%;animation:sp .6s linear infinite;margin-bottom:10px;"></div><div style="color:var(--muted);font-size:13px;">AI is working through your tasks&hellip;</div></div>';

    try {
        const res = await fetch("{{ route('optimize.run') }}", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'}
        });
        const data = await res.json();
        if (data.error) { box.innerHTML = '<div class="card" style="border-left:3px solid var(--rose);"><strong style="color:var(--rose);">Error:</strong> ' + escapeHtml(data.error) + '</div>'; return; }
        renderActions(data.actions || []);
    } catch (e) {
        box.innerHTML = '<div class="card" style="border-left:3px solid var(--rose);">Something went wrong. Please try again.</div>';
    } finally {
        btn.innerHTML = '&#9889; Run Full Optimization'; btn.disabled = false;
    }
});

function renderActions(actions){
    const box = document.getElementById('opt-results');
    if (!actions.length){ box.innerHTML = ''; return; }
    let html = '<div class="card"><div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;"><div style="width:28px;height:28px;border-radius:8px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-weight:700;">&#10003;</div><div style="font-weight:700;font-size:15px;">Optimization Complete</div></div>';
    actions.forEach((a, i) => {
        html += '<div style="display:flex;gap:12px;padding:12px 0;' + (i>0?'border-top:1px solid var(--line);':'') + '">'
            + '<div style="width:34px;height:34px;border-radius:9px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;flex-shrink:0;">' + escapeHtml(a.icon || '&#10003;') + '</div>'
            + '<div><div style="font-weight:600;font-size:13.5px;">' + escapeHtml(a.title || '') + '</div>'
            + '<div style="font-size:12.5px;color:var(--muted);margin-top:2px;">' + escapeHtml(a.detail || '') + '</div></div></div>';
    });
    html += '<div style="margin-top:14px;display:flex;gap:10px;">'
        + '<a href="{{ route('reviews') }}" class="btn btn-ghost" style="font-size:12.5px;">View Reviews</a>'
        + '<a href="{{ route('social') }}" class="btn btn-ghost" style="font-size:12.5px;">View Social Drafts</a></div>';
    html += '</div>';
    box.innerHTML = html;
}

function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
<style>@keyframes sp{to{transform:rotate(360deg)}}</style>
@endpush
@endsection
