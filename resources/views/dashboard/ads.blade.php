@extends('layouts.app')
@section('title', 'Ads Reports')
@section('content')

@push('head')
<style>
    .ad-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:14px;flex-wrap:wrap;}
    .ad-header h1{font-size:22px;font-weight:700;letter-spacing:-.02em;}
    .ad-header p{font-size:13px;color:var(--muted);margin-top:3px;}

    .ad-connect-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;}
    .ad-connect-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);display:flex;align-items:center;gap:16px;transition:border-color .15s;}
    .ad-connect-card:hover{border-color:#d0d5e0;}
    .ad-connect-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;font-size:22px;flex-shrink:0;}
    .ad-connect-icon.google{background:#eef1ff;color:#4285f4;}
    .ad-connect-icon.meta{background:#ebf3ff;color:#1877f2;}
    .ad-connect-info{flex:1;min-width:0;}
    .ad-connect-info h3{font-size:14px;font-weight:700;margin-bottom:2px;}
    .ad-connect-info p{font-size:12px;color:var(--muted);}
    .ad-connect-status{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;margin-top:6px;}
    .ad-connect-dot{width:8px;height:8px;border-radius:50%;}
    .ad-connect-dot.live{background:var(--green);}
    .ad-connect-dot.off{background:var(--muted);}

    .ad-kpis{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:18px;}
    .ad-kpi{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px 18px;box-shadow:var(--shadow);}
    .ad-kpi.accent{background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;border-color:transparent;}
    .ad-kpi.accent .ad-kpi-l{color:rgba(255,255,255,.7);}
    .ad-kpi-ic{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:16px;margin-bottom:8px;}
    .ad-kpi.accent .ad-kpi-ic{background:rgba(255,255,255,.2);}
    .ad-kpi-v{font-size:22px;font-weight:800;line-height:1;}
    .ad-kpi-l{font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-top:4px;}

    .ad-tabs{display:flex;gap:6px;margin-bottom:16px;}
    .ad-tab{border:1px solid var(--line);border-radius:999px;padding:8px 18px;font-size:13px;font-weight:600;background:var(--card);color:var(--muted);cursor:pointer;transition:all .15s;font-family:inherit;}
    .ad-tab:hover{border-color:var(--teal);color:var(--teal);}
    .ad-tab.active{background:var(--ink);color:#fff;border-color:var(--ink);}
    .ad-tab .count{font-size:11px;opacity:.7;margin-left:3px;}

    .ad-table{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
    .ad-table table th{background:var(--paper);font-size:11.5px;text-transform:uppercase;letter-spacing:.04em;}
    .ad-table table td{font-size:13.5px;}

    .ad-empty{text-align:center;padding:48px 20px;}
    .ad-empty-icon{width:72px;height:72px;border-radius:50%;background:var(--teal-soft);color:var(--teal);display:grid;place-items:center;font-size:30px;margin:0 auto 16px;}

    @media (max-width:700px){
        .ad-connect-grid{grid-template-columns:1fr;}
        .ad-kpis{grid-template-columns:repeat(2,1fr);}
    }
    @media (max-width:480px){
        .ad-kpis{grid-template-columns:1fr 1fr;}
    }
</style>
@endpush

<div class="ad-header">
    <div>
        <h1>Ads Reports</h1>
        <p>Track campaign performance across Google Ads and Meta Ads.</p>
    </div>
    <button class="btn" onclick="document.getElementById('ad-modal').classList.add('open')">+ Add Report</button>
</div>

{{-- Connection status cards --}}
<div class="ad-connect-grid">
    <div class="ad-connect-card">
        <div class="ad-connect-icon google">
            <svg viewBox="0 0 24 24" width="24" height="24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
        </div>
        <div class="ad-connect-info">
            <h3>Google Ads</h3>
            <p>{{ $googleReports->count() }} campaign{{ $googleReports->count() !== 1 ? 's' : '' }} tracked</p>
            <div class="ad-connect-status">
                <span class="ad-connect-dot {{ $hasGoogle ? 'live' : 'off' }}"></span>
                {{ $hasGoogle ? 'Google Connected' : 'Not connected' }}
            </div>
        </div>
        @if(!$hasGoogle)
            <a href="{{ route('clients') }}" class="btn btn-ghost" style="padding:8px 14px;font-size:12px;">Connect</a>
        @endif
    </div>
    <div class="ad-connect-card">
        <div class="ad-connect-icon meta">
            <svg viewBox="0 0 24 24" width="24" height="24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12c0-5.523-4.477-10-10-10z" fill="#1877F2"/></svg>
        </div>
        <div class="ad-connect-info">
            <h3>Meta Ads</h3>
            <p>{{ $metaReports->count() }} campaign{{ $metaReports->count() !== 1 ? 's' : '' }} tracked</p>
            <div class="ad-connect-status">
                <span class="ad-connect-dot {{ $hasMeta ? 'live' : 'off' }}"></span>
                {{ $hasMeta ? 'Meta Connected' : 'Not connected' }}
            </div>
        </div>
        @if(!$hasMeta)
            <a href="{{ route('clients') }}" class="btn btn-ghost" style="padding:8px 14px;font-size:12px;">Connect</a>
        @endif
    </div>
</div>

@if($clients->isEmpty())
    <div class="alert info">Add a client first — reports attach to a client.</div>
@endif

@if($insight)
    <div style="background:var(--teal-soft);border-radius:14px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
        <div style="width:32px;height:32px;border-radius:50%;background:var(--teal);color:#fff;display:grid;place-items:center;font-size:14px;">✦</div>
        <div style="font-size:13.5px;color:var(--teal-ink);">{{ $insight }}</div>
    </div>
@endif

{{-- KPI cards --}}
<div class="ad-kpis">
    <div class="ad-kpi accent">
        <div class="ad-kpi-ic">₹</div>
        <div class="ad-kpi-v">₹{{ number_format($totals['spend']) }}</div>
        <div class="ad-kpi-l">Total Spend</div>
    </div>
    <div class="ad-kpi">
        <div class="ad-kpi-ic" style="background:var(--teal-soft);color:var(--teal);">⚡</div>
        <div class="ad-kpi-v">{{ number_format($totals['clicks']) }}</div>
        <div class="ad-kpi-l">Total Clicks</div>
    </div>
    <div class="ad-kpi">
        <div class="ad-kpi-ic" style="background:var(--green-soft);color:var(--green);">✓</div>
        <div class="ad-kpi-v">{{ number_format($totals['conversions']) }}</div>
        <div class="ad-kpi-l">Conversions</div>
    </div>
    <div class="ad-kpi">
        <div class="ad-kpi-ic" style="background:var(--amber-soft);color:var(--amber);">🎯</div>
        <div class="ad-kpi-v">{{ number_format($totals['leads']) }}</div>
        <div class="ad-kpi-l">Total Leads</div>
    </div>
    <div class="ad-kpi">
        <div class="ad-kpi-ic" style="background:var(--rose-soft);color:var(--rose);">📊</div>
        <div class="ad-kpi-v">{{ $reports->count() }}</div>
        <div class="ad-kpi-l">Campaigns</div>
    </div>
</div>

{{-- Network tabs --}}
<div class="ad-tabs">
    <button type="button" class="ad-tab active" data-net="all" onclick="filterAds('all', this)">All<span class="count">({{ $reports->count() }})</span></button>
    <button type="button" class="ad-tab" data-net="GOOGLE" onclick="filterAds('GOOGLE', this)">Google Ads<span class="count">({{ $googleReports->count() }})</span></button>
    <button type="button" class="ad-tab" data-net="META" onclick="filterAds('META', this)">Meta Ads<span class="count">({{ $metaReports->count() }})</span></button>
</div>

@if($reports->isEmpty())
    <div class="card">
        <div class="ad-empty">
            <div class="ad-empty-icon">📊</div>
            <h3 style="font-size:17px;font-weight:700;margin-bottom:6px;">No Campaigns Yet</h3>
            <p style="font-size:13.5px;color:var(--muted);max-width:380px;margin:0 auto 16px;">Add your Google Ads or Meta Ads campaign data to track performance, compare networks, and get AI insights.</p>
            <button class="btn" onclick="document.getElementById('ad-modal').classList.add('open')">+ Add Your First Campaign</button>
        </div>
    </div>
@else
    <div class="ad-table">
        <table>
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th>Network</th>
                    <th>Period</th>
                    <th style="text-align:right;">Spend</th>
                    <th style="text-align:right;">Clicks</th>
                    <th style="text-align:right;">Conv.</th>
                    <th style="text-align:right;">Leads</th>
                    <th style="text-align:right;">CPC</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $r)
                    <tr class="ad-row" data-network="{{ $r->network }}">
                        <td><strong>{{ $r->campaign }}</strong></td>
                        <td><span class="badge {{ $r->network==='GOOGLE'?'teal':'amber' }}">{{ $r->network }}</span></td>
                        <td style="font-size:12px;color:var(--muted);">{{ optional($r->period_start)->format('M d') }} — {{ optional($r->period_end)->format('M d, Y') }}</td>
                        <td style="text-align:right;font-weight:600;">₹{{ number_format($r->spend) }}</td>
                        <td style="text-align:right;">{{ number_format($r->clicks) }}</td>
                        <td style="text-align:right;">{{ $r->conversions }}</td>
                        <td style="text-align:right;">{{ $r->leads }}</td>
                        <td style="text-align:right;color:var(--muted);">₹{{ $r->clicks > 0 ? number_format($r->spend / $r->clicks, 1) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Add report modal --}}
<div class="modal-bg" id="ad-modal">
    <div class="modal" style="max-width:520px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="margin:0;">Add Campaign Report</h2>
            <button type="button" onclick="document.getElementById('ad-modal').classList.remove('open')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);">&times;</button>
        </div>
        <form method="POST" action="{{ route('ads.store') }}">
            @csrf
            @if($clients->count() > 1)
                <label><span class="lbl">Client</span><select name="client_id">@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
            @else
                <input type="hidden" name="client_id" value="{{ $clients->first()?->id }}">
            @endif
            <label><span class="lbl">Network</span>
                <select name="network">
                    <option value="GOOGLE">Google Ads</option>
                    <option value="META">Meta Ads</option>
                </select>
            </label>
            <label><span class="lbl">Campaign name</span><input type="text" name="campaign" required placeholder="e.g. Brand Awareness Q4"></label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="lbl">Spend (₹)</span><input type="number" name="spend" step="0.01" placeholder="0.00"></label>
                <label><span class="lbl">Clicks</span><input type="number" name="clicks" placeholder="0"></label>
                <label><span class="lbl">Conversions</span><input type="number" name="conversions" placeholder="0"></label>
                <label><span class="lbl">Leads</span><input type="number" name="leads" placeholder="0"></label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="lbl">Period start</span><input type="date" name="period_start" value="{{ now()->subDays(30)->format('Y-m-d') }}"></label>
                <label><span class="lbl">Period end</span><input type="date" name="period_end" value="{{ now()->format('Y-m-d') }}"></label>
            </div>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('ad-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add Report</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function filterAds(net, btn){
    document.querySelectorAll('.ad-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.ad-row').forEach(r => {
        r.style.display = net === 'all' || r.dataset.network === net ? '' : 'none';
    });
}
</script>
@endpush
@endsection
