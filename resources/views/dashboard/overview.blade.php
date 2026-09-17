@extends('layouts.app')
@section('title', 'Advanced Reports')
@section('content')

@php
    $firstName = explode(' ', auth()->user()->name)[0];
    $replyPct = $totalReviews ? round($repliedCount / $totalReviews * 100) : 0;
@endphp

{{-- ===== Header ===== --}}
<div class="rf-hero">
    <div>
        <div class="rf-hero-title">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ $firstName }} 👋</div>
        <div class="rf-hero-sub">
            {{ now()->format('l, F j, Y') }} · {{ ucfirst(strtolower($currentPlan)) }} plan
            @if($hasGoogle)
                · <span class="rf-live">● Live</span> Google Business Profile
                @if($lastSynced) · synced {{ $lastSynced->diffForHumans() }} @endif
            @else
                · <span style="opacity:.85;">Demo data — connect Google for live data</span>
            @endif
        </div>
    </div>
    <div class="rf-hero-actions">
        @if($hasGoogle)
            <form method="POST" action="{{ route('dashboard.sync') }}" style="display:inline;">@csrf
                <button type="submit" class="rf-hero-btn primary">⟳ Sync Live Data</button>
            </form>
        @else
            <a href="{{ route('clients') }}" class="rf-hero-btn primary">🔗 Connect Google</a>
        @endif
        <a href="{{ route('plans') }}" class="rf-hero-btn">⬆ Upgrade Plan</a>
    </div>
</div>

{{-- ===== Section tabs ===== --}}
<div class="rf-tabs">
    <span class="rf-tab active">Reviews</span>
    <a href="{{ route('reviews') }}" class="rf-tab">Locations</a>
    <a href="{{ route('gbp-content') }}" class="rf-tab">Google</a>
    <a href="{{ route('social') }}" class="rf-tab">Social</a>
    <a href="{{ route('competitors') }}" class="rf-tab">Competition</a>
    <a href="{{ route('keywords') }}" class="rf-tab">Keywords</a>
    <a href="{{ route('rank-checker') }}" class="rf-tab">Rank</a>
</div>

{{-- ===== KPI cards ===== --}}
<div class="rf-kpis">
    <div class="rf-kpi">
        <div class="rf-kpi-ic" style="background:linear-gradient(135deg,#4c6fff,#6b8afd);">🏢</div>
        <div class="rf-kpi-l">Business Accounts</div>
        <div class="rf-kpi-v">{{ $clients->count() }}</div>
        <div class="rf-kpi-s">Google My Business</div>
    </div>
    <div class="rf-kpi">
        <div class="rf-kpi-ic" style="background:linear-gradient(135deg,#22c55e,#4ade80);">📍</div>
        <div class="rf-kpi-l">Business Locations</div>
        <div class="rf-kpi-v">{{ $locations->count() }}</div>
        <div class="rf-kpi-s">Active locations</div>
    </div>
    <div class="rf-kpi">
        <div class="rf-kpi-ic" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">★</div>
        <div class="rf-kpi-l">Total Reviews</div>
        <div class="rf-kpi-v">{{ number_format($totalReviews) }}</div>
        <div class="rf-kpi-s">Avg {{ number_format($avgRating,1) }} / 5</div>
    </div>
    <div class="rf-kpi">
        <div class="rf-kpi-ic" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa);">⚡</div>
        <div class="rf-kpi-l">AI Credits</div>
        <div class="rf-kpi-v" style="color:var(--purple);">{{ number_format($creditBalance) }}</div>
        <div class="rf-kpi-s">Available for AI</div>
    </div>
</div>

{{-- ===== Row 1: Rating breakdown · Directory · Replies ===== --}}
<div class="rf-grid-3">
    {{-- Average Rating and Breakdown --}}
    <div class="card">
        <div class="rf-card-head"><h3>Average Rating and Breakdown</h3><span class="rf-info">ⓘ</span></div>
        <div class="rf-rating-row">
            <div class="rf-donut-wrap">
                <canvas id="chartRating" width="150" height="150"></canvas>
                <div class="rf-donut-center">
                    <div class="rf-donut-big">{{ number_format($avgRating,2) }}</div>
                    <div class="rf-donut-small">{{ number_format($totalReviews) }} Reviews</div>
                </div>
            </div>
            <div class="rf-bars">
                @foreach($charts['starDist'] as $star => $count)
                    @php $pct = $totalReviews ? round($count / $totalReviews * 100) : 0; @endphp
                    <div class="rf-bar-line">
                        <span class="rf-bar-star">{{ $star }} <span style="color:#f59e0b;">★</span></span>
                        <div class="rf-bar-track"><div class="rf-bar-fill" style="width:{{ $pct }}%;"></div></div>
                        <span class="rf-bar-val">{{ $count }} <span class="rf-bar-pct">({{ $pct }}%)</span></span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Reviews by Directory --}}
    <div class="card">
        <div class="rf-card-head"><h3>Reviews by Directory</h3><span class="rf-info">ⓘ</span></div>
        <div class="rf-rating-row">
            <div class="rf-donut-wrap">
                <canvas id="chartDir" width="150" height="150"></canvas>
                <div class="rf-donut-center">
                    <div class="rf-donut-big" style="font-size:26px;">{{ number_format($totalReviews) }}</div>
                    <div class="rf-donut-small">Total</div>
                </div>
            </div>
            <div class="rf-legend">
                <div class="rf-leg"><span class="rf-dot" style="background:#4c6fff;"></span> Google <b>{{ $charts['directory']['Google'] }}</b></div>
                <div class="rf-leg"><span class="rf-dot" style="background:#f59e0b;"></span> Facebook <b>{{ $charts['directory']['Facebook'] }}</b></div>
                <div class="rf-leg"><span class="rf-dot" style="background:#ef4757;"></span> Others <b>{{ $charts['directory']['Others'] }}</b></div>
            </div>
        </div>
    </div>

    {{-- Replies and Breakdown --}}
    <div class="card">
        <div class="rf-card-head"><h3>Replies and Breakdown</h3><span class="rf-info">ⓘ</span></div>
        <div class="rf-rating-row">
            <div class="rf-donut-wrap">
                <canvas id="chartReplies" width="150" height="150"></canvas>
                <div class="rf-donut-center">
                    <div class="rf-donut-big" style="font-size:26px;">{{ $replyPct }}%</div>
                    <div class="rf-donut-small">Replied</div>
                </div>
            </div>
            <div class="rf-legend">
                <div class="rf-leg"><span class="rf-dot" style="background:#4c6fff;"></span> Replied <b>{{ $repliedCount }}</b></div>
                <div class="rf-leg"><span class="rf-dot" style="background:#c7d0e8;"></span> Unreplied <b>{{ $pendingCount }}</b></div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Row 2: Response time · Ratings breakdown · Avg trend ===== --}}
<div class="rf-grid-3">
    <div class="card">
        <div class="rf-card-head"><h3>Response Time on Avg</h3><span class="rf-info">ⓘ</span></div>
        <div class="rf-metric">{{ $charts['avgResponse'] ?: '—' }} <span>days avg</span></div>
        <div class="rf-chartbox"><canvas id="chartResp"></canvas></div>
    </div>
    <div class="card">
        <div class="rf-card-head"><h3>Ratings &amp; Reviews Breakdown</h3><span class="rf-info">ⓘ</span></div>
        <div class="rf-metric">{{ number_format($totalReviews) }} <span>total reviews</span></div>
        <div class="rf-chartbox"><canvas id="chartBreakdown"></canvas></div>
    </div>
    <div class="card">
        <div class="rf-card-head"><h3>Avg Rating Trend</h3><span class="rf-info">ⓘ</span></div>
        <div class="rf-metric">{{ number_format($avgRating,1) }} <span>current avg</span></div>
        <div class="rf-chartbox"><canvas id="chartTrend"></canvas></div>
    </div>
</div>

{{-- ===== Business Locations (clickable → individual dashboard) ===== --}}
<div class="card" style="margin-bottom:20px;">
    <div class="rf-card-head" style="margin-bottom:14px;">
        <div>
            <h3>Your Accounts &amp; Locations</h3>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">Click any account to open its complete individual dashboard</div>
        </div>
        <a href="{{ route('clients') }}" class="btn btn-ghost" style="padding:6px 14px;font-size:12px;">All Clients →</a>
    </div>

    @if($locations->isEmpty())
        <div class="empty">No locations yet. <a href="{{ route('clients') }}" style="color:var(--teal);">Add a client</a> to get started.</div>
    @else
        <div class="rf-acct-grid">
            @foreach($locations as $loc)
                @php
                    $live = in_array($loc->client_id, $connectedClientIds);
                    $locAvg = round($loc->reviews_avg_star_rating ?? 0, 1);
                    $locCount = $loc->total_reviews ?? 0;
                @endphp
                <a href="{{ route('clients.show', $loc->client) }}" class="rf-acct">
                    <div class="rf-acct-top">
                        <div class="rf-acct-av">{{ strtoupper(substr($loc->title ?: $loc->client->name, 0, 1)) }}</div>
                        <span class="badge {{ $live ? 'teal' : 'gray' }}">{{ $live ? 'Live' : 'Demo' }}</span>
                    </div>
                    <div class="rf-acct-name">{{ $loc->title ?: $loc->google_name }}</div>
                    <div class="rf-acct-addr">{{ $loc->address ?: $loc->client->name }}</div>
                    <div class="rf-acct-stats">
                        <div><b>{{ number_format($locAvg,1) }}</b><span>★ Rating</span></div>
                        <div><b>{{ $locCount }}</b><span>Reviews</span></div>
                        <div class="rf-acct-open">Open →</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

{{-- ===== Recent reviews + quick actions ===== --}}
<div class="rf-grid-2">
    <div class="card" style="padding:0;overflow:hidden;">
        <div class="rf-card-head" style="padding:16px 18px;border-bottom:1px solid var(--line);">
            <h3>Recent Reviews</h3>
            <a href="{{ route('reviews') }}" style="color:var(--teal);font-size:13px;font-weight:600;">All →</a>
        </div>
        @if($recentReviews->isEmpty())
            <div class="empty">No reviews yet.</div>
        @else
            @foreach($recentReviews as $rev)
                <div style="display:flex;gap:10px;padding:12px 18px;{{ !$loop->last ? 'border-bottom:1px solid var(--line);' : '' }}">
                    <div class="avatar" style="width:34px;height:34px;font-size:11px;flex-shrink:0;background:{{ $rev->star_rating >= 4 ? 'var(--green-soft)' : ($rev->star_rating >= 3 ? 'var(--amber-soft)' : 'var(--rose-soft)') }};color:{{ $rev->star_rating >= 4 ? 'var(--green)' : ($rev->star_rating >= 3 ? '#8a5a08' : 'var(--rose)') }};">{{ strtoupper(substr($rev->reviewer_name ?: '?', 0, 1)) }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <strong style="font-size:13px;">{{ $rev->reviewer_name ?: 'Anonymous' }}</strong>
                            <span style="font-size:11px;color:var(--muted);">{{ $rev->review_time ? $rev->review_time->diffForHumans() : '' }}</span>
                        </div>
                        <div style="font-size:12px;color:#f59e0b;margin-top:2px;">@for($i=1;$i<=5;$i++){{ $i <= $rev->star_rating ? '★' : '☆' }}@endfor <span style="color:var(--muted);">{{ $rev->star_rating }}/5</span></div>
                        @if($rev->comment)<div style="font-size:12.5px;color:var(--muted);margin-top:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($rev->comment, 70) }}</div>@endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="rf-actions">
        <a href="{{ route('optimize') }}" class="rf-action">
            <div class="rf-action-ic" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">⚡</div>
            <div><div class="rf-action-t">One-Click Optimize</div><div class="rf-action-s">AI handles pending tasks</div></div>
        </a>
        <a href="{{ route('audit') }}" class="rf-action">
            <div class="rf-action-ic" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa);">◎</div>
            <div><div class="rf-action-t">Google Audit</div><div class="rf-action-s">Score profile health</div></div>
        </a>
        <a href="{{ route('gbp-content') }}" class="rf-action">
            <div class="rf-action-ic" style="background:linear-gradient(135deg,#22c55e,#4ade80);">📝</div>
            <div><div class="rf-action-t">Google Posts</div><div class="rf-action-s">Manage posts &amp; photos</div></div>
        </a>
        <a href="{{ route('ai') }}" class="rf-action">
            <div class="rf-action-ic" style="background:linear-gradient(135deg,#4c6fff,#6b8afd);">✦</div>
            <div><div class="rf-action-t">AI Mode</div><div class="rf-action-s">Ask the marketing assistant</div></div>
        </a>
    </div>
</div>

@push('head')
<style>
    .main{max-width:1180px;}
    .rf-hero{background:linear-gradient(120deg,#4c6fff,#6b8afd 55%,#8b5cf6);border-radius:18px;padding:22px 26px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;color:#fff;box-shadow:0 10px 30px rgba(76,111,255,.25);}
    .rf-hero-title{font-size:22px;font-weight:800;letter-spacing:-.02em;}
    .rf-hero-sub{font-size:13px;opacity:.9;margin-top:4px;}
    .rf-live{color:#a7f3d0;font-weight:700;}
    .rf-hero-actions{display:flex;gap:8px;flex-wrap:wrap;}
    .rf-hero-btn{padding:9px 16px;border-radius:10px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.28);color:#fff;font-size:13px;font-weight:600;backdrop-filter:blur(6px);}
    .rf-hero-btn.primary{background:#fff;color:#3452d1;border-color:#fff;}
    .rf-tabs{display:flex;gap:4px;margin-bottom:18px;overflow-x:auto;padding-bottom:2px;border-bottom:1px solid var(--line);}
    .rf-tab{padding:9px 14px;font-size:13px;font-weight:600;color:var(--muted);white-space:nowrap;border-bottom:2px solid transparent;margin-bottom:-1px;}
    .rf-tab:hover{color:var(--ink);}
    .rf-tab.active{color:var(--teal);border-bottom-color:var(--teal);}
    .rf-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
    .rf-kpi{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px 18px;box-shadow:var(--shadow);}
    .rf-kpi-ic{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;color:#fff;font-size:18px;margin-bottom:12px;}
    .rf-kpi-l{font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.03em;color:var(--muted);}
    .rf-kpi-v{font-size:30px;font-weight:800;line-height:1;margin-top:6px;}
    .rf-kpi-s{font-size:12px;color:var(--muted);margin-top:4px;}
    .rf-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:14px;}
    .rf-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;}
    .rf-card-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;}
    .rf-card-head h3{font-size:15px;font-weight:700;}
    .rf-info{color:#c7d0e8;font-size:13px;cursor:help;}
    .rf-rating-row{display:flex;align-items:center;gap:18px;}
    .rf-donut-wrap{position:relative;width:150px;height:150px;flex-shrink:0;}
    .rf-donut-center{position:absolute;inset:0;display:grid;place-items:center;text-align:center;pointer-events:none;}
    .rf-donut-big{font-size:30px;font-weight:800;line-height:1;}
    .rf-donut-small{font-size:11px;color:var(--muted);margin-top:3px;}
    .rf-bars{flex:1;display:flex;flex-direction:column;gap:8px;min-width:0;}
    .rf-bar-line{display:flex;align-items:center;gap:8px;font-size:12px;}
    .rf-bar-star{width:30px;font-weight:600;flex-shrink:0;}
    .rf-bar-track{flex:1;height:8px;background:#eef1f8;border-radius:6px;overflow:hidden;}
    .rf-bar-fill{height:100%;background:linear-gradient(90deg,#4c6fff,#6b8afd);border-radius:6px;}
    .rf-bar-val{font-size:11.5px;color:var(--ink);font-weight:600;white-space:nowrap;flex-shrink:0;}
    .rf-bar-pct{color:var(--muted);font-weight:500;}
    .rf-legend{flex:1;display:flex;flex-direction:column;gap:10px;}
    .rf-leg{font-size:13px;color:var(--muted);display:flex;align-items:center;gap:8px;}
    .rf-leg b{margin-left:auto;color:var(--ink);}
    .rf-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
    .rf-metric{font-size:22px;font-weight:800;margin-bottom:8px;}
    .rf-metric span{font-size:12px;font-weight:500;color:var(--muted);}
    .rf-chartbox{position:relative;height:170px;width:100%;}
    .rf-acct-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;}
    .rf-acct{border:1px solid var(--line);border-radius:14px;padding:15px;transition:border-color .15s,box-shadow .15s,transform .15s;background:var(--card);display:block;}
    .rf-acct:hover{border-color:var(--teal);box-shadow:0 8px 22px rgba(76,111,255,.14);transform:translateY(-2px);}
    .rf-acct-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
    .rf-acct-av{width:40px;height:40px;border-radius:11px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-weight:700;font-size:16px;}
    .rf-acct-name{font-weight:700;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .rf-acct-addr{font-size:12px;color:var(--muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .rf-acct-stats{display:flex;align-items:center;gap:14px;margin-top:12px;padding-top:12px;border-top:1px solid var(--line);}
    .rf-acct-stats b{font-size:16px;font-weight:800;display:block;}
    .rf-acct-stats span{font-size:11px;color:var(--muted);}
    .rf-acct-open{margin-left:auto;color:var(--teal);font-weight:700;font-size:12.5px;}
    .rf-actions{display:flex;flex-direction:column;gap:12px;}
    .rf-action{display:flex;align-items:center;gap:14px;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:var(--shadow);transition:border-color .15s,transform .15s;}
    .rf-action:hover{border-color:var(--teal);transform:translateX(3px);}
    .rf-action-ic{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;color:#fff;font-size:20px;flex-shrink:0;}
    .rf-action-t{font-weight:700;font-size:14px;}
    .rf-action-s{font-size:12px;color:var(--muted);margin-top:2px;}
    @media (max-width:1000px){
        .rf-grid-3{grid-template-columns:1fr 1fr;}
        .rf-kpis{grid-template-columns:repeat(2,1fr);}
    }
    @media (max-width:700px){
        .rf-grid-3,.rf-grid-2{grid-template-columns:1fr;}
        .rf-rating-row{flex-direction:column;align-items:stretch;}
        .rf-donut-wrap{margin:0 auto;}
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const RF = @json($charts);
const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const gridClr = isDark ? 'rgba(255,255,255,.06)' : 'rgba(20,30,60,.06)';
const tickClr = isDark ? '#8b97a8' : '#7a8599';
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = tickClr;

function donut(id, data, colors, cut='72%'){
    const el = document.getElementById(id); if(!el) return;
    const total = data.reduce((a,b)=>a+b,0);
    new Chart(el, {
        type:'doughnut',
        data:{ datasets:[{ data: total? data : [1], backgroundColor: total? colors : ['#eef1f8'], borderWidth:0 }] },
        options:{ cutout:cut, plugins:{legend:{display:false}, tooltip:{enabled: total>0}}, responsive:false }
    });
}

// Average rating donut (fill proportion of 5 stars, blue over track)
const ratePct = {{ $avgRating }} / 5;
donut('chartRating', [ratePct, 1-ratePct], ['#4c6fff', '#eef1f8']);

// Reviews by directory
donut('chartDir', [RF.directory.Google, RF.directory.Facebook, RF.directory.Others], ['#4c6fff','#f59e0b','#ef4757']);

// Replies breakdown
donut('chartReplies', [RF.replied, RF.unreplied], ['#4c6fff','#c7d0e8']);

const areaOpts = (label, fill) => ({
    type:'line',
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{legend:{display:false}},
        scales:{
            x:{grid:{display:false},ticks:{font:{size:11}}},
            y:{grid:{color:gridClr},ticks:{font:{size:11}},beginAtZero:true}
        },
        elements:{point:{radius:0,hoverRadius:5}}
    }
});

// Response time area
new Chart(document.getElementById('chartResp'), {
    ...areaOpts(),
    data:{ labels:RF.trendLabels, datasets:[{
        data:RF.trendResp, borderColor:'#4c6fff', backgroundColor:'rgba(76,111,255,.12)',
        fill:true, tension:.4, borderWidth:2, spanGaps:true
    }]}
});

// Avg rating trend area
new Chart(document.getElementById('chartTrend'), {
    ...areaOpts(),
    data:{ labels:RF.trendLabels, datasets:[{
        data:RF.trendAvg, borderColor:'#8b5cf6', backgroundColor:'rgba(139,92,246,.12)',
        fill:true, tension:.4, borderWidth:2, spanGaps:true
    }]},
    options:{ ...areaOpts().options, scales:{ ...areaOpts().options.scales, y:{ ...areaOpts().options.scales.y, max:5 } } }
});

// Ratings & reviews breakdown — stacked bars per star + avg line
const starColors = {1:'#ef4757',2:'#f97316',3:'#f59e0b',4:'#84cc16',5:'#22c55e'};
new Chart(document.getElementById('chartBreakdown'), {
    type:'bar',
    data:{
        labels:RF.trendLabels,
        datasets:[5,4,3,2,1].map(s=>({
            label:s+'★', data:RF.breakdown[s], backgroundColor:starColors[s],
            stack:'r', borderRadius:3, barPercentage:.7
        }))
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{legend:{display:false}},
        scales:{
            x:{stacked:true,grid:{display:false},ticks:{font:{size:11}}},
            y:{stacked:true,grid:{color:gridClr},ticks:{font:{size:11}},beginAtZero:true}
        }
    }
});
</script>
@endpush
@endsection
