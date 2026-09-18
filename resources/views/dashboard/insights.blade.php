@extends('layouts.app')
@section('title', 'Insights')
@section('content')

@php
    $metricLabels = [
        'BUSINESS_IMPRESSIONS_DESKTOP_MAPS' => ['Desktop Maps Views', '#4c6fff'],
        'BUSINESS_IMPRESSIONS_DESKTOP_SEARCH' => ['Desktop Search Views', '#6b8afd'],
        'BUSINESS_IMPRESSIONS_MOBILE_MAPS' => ['Mobile Maps Views', '#22c55e'],
        'BUSINESS_IMPRESSIONS_MOBILE_SEARCH' => ['Mobile Search Views', '#4ade80'],
        'CALL_CLICKS' => ['Call Clicks', '#f59e0b'],
        'WEBSITE_CLICKS' => ['Website Clicks', '#8b5cf6'],
        'BUSINESS_DIRECTION_REQUESTS' => ['Direction Requests', '#ec4899'],
        'BUSINESS_BOOKINGS' => ['Bookings', '#38bdf8'],
    ];
@endphp

<div class="page-head">
    <div>
        <h1>Business Insights</h1>
        <p>Google Business Profile performance metrics — views, clicks, calls and more.</p>
    </div>
</div>

{{-- Date range + location selector --}}
<div class="card" style="margin-bottom:18px;">
    <form method="GET" action="{{ route('insights') }}" style="display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap;">
        @if($locations->count() > 1)
        <label style="flex:1;min-width:180px;">
            <span class="lbl">Location</span>
            <select name="location">
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $selectedLocation && $selectedLocation->id === $loc->id ? 'selected' : '' }}>{{ $loc->title ?: $loc->google_name }}</option>
                @endforeach
            </select>
        </label>
        @endif
        <label style="min-width:140px;">
            <span class="lbl">Start Date</span>
            <input type="date" name="start" value="{{ $startDate }}">
        </label>
        <label style="min-width:140px;">
            <span class="lbl">End Date</span>
            <input type="date" name="end" value="{{ $endDate }}">
        </label>
        <button type="submit" class="btn" style="height:42px;">Apply</button>
        @if($hasGoogle && !empty($metrics))
        <a href="{{ route('insights.download', ['start' => $startDate, 'end' => $endDate, 'location' => $selectedLocation?->id]) }}" class="btn btn-ghost" style="height:42px;">&#11015; Download CSV</a>
        @endif
    </form>
</div>

@if($error)
    <div class="alert error">{{ $error }}</div>
@endif

@if(!$hasGoogle)
    <div class="card">
        <div class="empty">
            <div style="font-size:40px;margin-bottom:12px;">&#128200;</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:6px;">Connect Google Business Profile</div>
            <p style="color:var(--muted);margin-bottom:16px;">Connect your Google account to see real performance data — views, calls, direction requests and more.</p>
            <a href="{{ route('clients') }}" class="btn">Connect Google</a>
        </div>
    </div>
@elseif(empty($metrics))
    <div class="card">
        <div class="empty">
            <div style="font-size:40px;margin-bottom:12px;">&#128202;</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:6px;">No Data Available</div>
            <p style="color:var(--muted);">No performance data found for the selected date range. Try a different period or check back later.</p>
        </div>
    </div>
@else

{{-- KPI cards --}}
<div class="ins-kpis">
    <div class="ins-kpi accent">
        <div class="ins-kpi-ic" style="background:linear-gradient(135deg,#4c6fff,#8b5cf6);">&#128200;</div>
        <div class="ins-kpi-l">Total Interactions</div>
        <div class="ins-kpi-v">{{ number_format($totalInteractions) }}</div>
        <div class="ins-kpi-s">{{ $startDate }} to {{ $endDate }}</div>
    </div>
    @foreach(['CALL_CLICKS' => ['Call Clicks', '#f59e0b', '&#128222;'], 'WEBSITE_CLICKS' => ['Website Clicks', '#8b5cf6', '&#127760;'], 'BUSINESS_DIRECTION_REQUESTS' => ['Directions', '#ec4899', '&#128204;']] as $mk => $info)
        <div class="ins-kpi">
            <div class="ins-kpi-ic" style="background:{{ $info[1] }};">{!! $info[2] !!}</div>
            <div class="ins-kpi-l">{{ $info[0] }}</div>
            <div class="ins-kpi-v">{{ number_format($totals[$mk] ?? 0) }}</div>
            <div class="ins-kpi-s">Total in period</div>
        </div>
    @endforeach
</div>

{{-- Main chart --}}
<div class="card" style="margin-bottom:18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div>
            <h3 style="font-size:16px;font-weight:700;">Business Profile Interactions</h3>
            <p style="font-size:12px;color:var(--muted);margin-top:3px;">Daily breakdown of all interactions</p>
        </div>
    </div>
    <div style="position:relative;height:300px;"><canvas id="chartInteractions"></canvas></div>
</div>

{{-- Impressions breakdown --}}
<div class="ins-grid-2">
    <div class="card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">Views Breakdown</h3>
        <div style="position:relative;height:250px;"><canvas id="chartViews"></canvas></div>
    </div>
    <div class="card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">Actions Breakdown</h3>
        <div style="position:relative;height:250px;"><canvas id="chartActions"></canvas></div>
    </div>
</div>

{{-- Detailed metrics table --}}
<div class="card" style="margin-top:18px;padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;">
        <h3 style="font-size:15px;font-weight:700;">Metric Details</h3>
    </div>
    <table>
        <thead><tr><th>Metric</th><th style="text-align:right;">Total</th><th style="text-align:right;">Daily Avg</th></tr></thead>
        <tbody>
            @php
                $days = max(1, (int) \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)));
            @endphp
            @foreach($totals as $metric => $total)
                <tr>
                    <td>
                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $metricLabels[$metric][1] ?? '#888' }};margin-right:8px;"></span>
                        {{ $metricLabels[$metric][0] ?? $metric }}
                    </td>
                    <td style="text-align:right;font-weight:700;">{{ number_format($total) }}</td>
                    <td style="text-align:right;color:var(--muted);">{{ number_format($total / $days, 1) }}</td>
                </tr>
            @endforeach
            <tr style="background:var(--teal-soft);">
                <td><strong>Total</strong></td>
                <td style="text-align:right;font-weight:700;">{{ number_format($totalInteractions) }}</td>
                <td style="text-align:right;color:var(--muted);font-weight:600;">{{ number_format($totalInteractions / $days, 1) }}</td>
            </tr>
        </tbody>
    </table>
</div>

@endif

@push('head')
<style>
    .ins-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
    .ins-kpi{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px 18px;box-shadow:var(--shadow);}
    .ins-kpi.accent{background:linear-gradient(135deg,#eaefff,#f0eaff);}
    .ins-kpi-ic{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;color:#fff;font-size:18px;margin-bottom:10px;}
    .ins-kpi-l{font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.03em;color:var(--muted);}
    .ins-kpi-v{font-size:28px;font-weight:800;line-height:1;margin-top:6px;}
    .ins-kpi-s{font-size:12px;color:var(--muted);margin-top:4px;}
    .ins-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    @media(max-width:800px){.ins-kpis{grid-template-columns:repeat(2,1fr);} .ins-grid-2{grid-template-columns:1fr;}}
    @media(max-width:520px){.ins-kpis{grid-template-columns:1fr;}}
</style>
@endpush

@if(!empty($metrics))
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const gridClr = isDark ? 'rgba(255,255,255,.06)' : 'rgba(20,30,60,.06)';
const tickClr = isDark ? '#8b97a8' : '#7a8599';
Chart.defaults.color = tickClr;

const dailyData = @json($dailyTotals);
const labels = Object.keys(dailyData);
const values = Object.values(dailyData);

new Chart(document.getElementById('chartInteractions'), {
    type: 'line',
    data: {
        labels: labels.map(d => { const dt = new Date(d); return dt.toLocaleDateString('en-IN', {month:'short', day:'numeric'}); }),
        datasets: [{
            data: values,
            borderColor: '#4c6fff',
            backgroundColor: 'rgba(76,111,255,.12)',
            fill: true, tension: .35, borderWidth: 2, pointRadius: values.length > 60 ? 0 : 3,
            pointBackgroundColor: '#4c6fff'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 12, font: { size: 11 } } },
            y: { grid: { color: gridClr }, ticks: { font: { size: 11 } }, beginAtZero: true }
        }
    }
});

@php
    $viewKeys = ['BUSINESS_IMPRESSIONS_DESKTOP_MAPS','BUSINESS_IMPRESSIONS_DESKTOP_SEARCH','BUSINESS_IMPRESSIONS_MOBILE_MAPS','BUSINESS_IMPRESSIONS_MOBILE_SEARCH'];
    $viewData = collect($metrics)->only($viewKeys)->map(fn($v) => array_sum($v));
    $actionKeys = ['CALL_CLICKS','WEBSITE_CLICKS','BUSINESS_DIRECTION_REQUESTS','BUSINESS_BOOKINGS'];
    $actionData = collect($metrics)->only($actionKeys)->map(fn($v) => array_sum($v));
@endphp
const viewMetrics = @json($viewData);
const viewLabels = {
    'BUSINESS_IMPRESSIONS_DESKTOP_MAPS': 'Desktop Maps',
    'BUSINESS_IMPRESSIONS_DESKTOP_SEARCH': 'Desktop Search',
    'BUSINESS_IMPRESSIONS_MOBILE_MAPS': 'Mobile Maps',
    'BUSINESS_IMPRESSIONS_MOBILE_SEARCH': 'Mobile Search'
};
const viewColors = ['#4c6fff','#6b8afd','#22c55e','#4ade80'];
const vLabels = Object.keys(viewMetrics).map(k => viewLabels[k] || k);
const vValues = Object.values(viewMetrics);
const vTotal = vValues.reduce((a,b)=>a+b,0);

new Chart(document.getElementById('chartViews'), {
    type: 'doughnut',
    data: {
        labels: vLabels,
        datasets: [{ data: vTotal ? vValues : [1], backgroundColor: vTotal ? viewColors : ['#eef1f8'], borderWidth: 0 }]
    },
    options: { cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 } } } }, responsive: true, maintainAspectRatio: false }
});

const actionMetrics = @json($actionData);
const actionLabels = { 'CALL_CLICKS': 'Calls', 'WEBSITE_CLICKS': 'Website', 'BUSINESS_DIRECTION_REQUESTS': 'Directions', 'BUSINESS_BOOKINGS': 'Bookings' };
const actionColors = ['#f59e0b','#8b5cf6','#ec4899','#38bdf8'];
const aLabels = Object.keys(actionMetrics).map(k => actionLabels[k] || k);
const aValues = Object.values(actionMetrics);
const aTotal = aValues.reduce((a,b)=>a+b,0);

new Chart(document.getElementById('chartActions'), {
    type: 'doughnut',
    data: {
        labels: aLabels,
        datasets: [{ data: aTotal ? aValues : [1], backgroundColor: aTotal ? actionColors : ['#eef1f8'], borderWidth: 0 }]
    },
    options: { cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 } } } }, responsive: true, maintainAspectRatio: false }
});
</script>
@endpush
@endif

@endsection
