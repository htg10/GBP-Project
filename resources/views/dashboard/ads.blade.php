@extends('layouts.app')
@section('title', 'Ads Reports')
@section('content')
<div class="page-head">
    <div><h1>Ads Reports</h1><p>Track campaign performance across Google and Meta.</p></div>
    <button class="btn" onclick="document.getElementById('ad-modal').classList.add('open')">+ Add report</button>
</div>

@if($clients->isEmpty())<div class="alert info">Add a client first — reports attach to a client.</div>@endif

@if($reports->count())
    <div class="stats" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
        <div class="stat accent"><div>₹</div><div class="v">₹{{ number_format($totals['spend']) }}</div><div class="l">Total spend</div></div>
        <div class="stat"><div>☄</div><div class="v">{{ number_format($totals['clicks']) }}</div><div class="l">Total clicks</div></div>
        <div class="stat"><div>◎</div><div class="v">{{ number_format($totals['conversions']) }}</div><div class="l">Conversions</div></div>
    </div>
@endif

@if($insight)
    <div style="background:var(--teal-soft);border:1px solid #cfe6e0;border-radius:12px;padding:13px 16px;margin-bottom:18px;color:var(--teal-ink);font-size:13.5px;">✦ {{ $insight }}</div>
@endif

@if($reports->isEmpty())
    <div class="card"><div class="empty">No reports yet. Click “Add report” to log campaign data.</div></div>
@else
    <div class="card" style="padding:0;overflow:hidden;">
        <table>
            <thead><tr><th>Campaign</th><th>Network</th><th style="text-align:right;">Spend</th><th style="text-align:right;">Clicks</th><th style="text-align:right;">Conv.</th><th style="text-align:right;">Leads</th></tr></thead>
            <tbody>
                @foreach($reports as $r)
                    <tr>
                        <td><strong>{{ $r->campaign }}</strong></td>
                        <td><span class="badge {{ $r->network==='GOOGLE'?'teal':'amber' }}">{{ $r->network }}</span></td>
                        <td style="text-align:right;">₹{{ number_format($r->spend) }}</td>
                        <td style="text-align:right;">{{ number_format($r->clicks) }}</td>
                        <td style="text-align:right;">{{ $r->conversions }}</td>
                        <td style="text-align:right;">{{ $r->leads }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="modal-bg" id="ad-modal">
    <div class="modal">
        <h2>Add ad report</h2>
        <form method="POST" action="{{ route('ads.store') }}">
            @csrf
            <label><span class="lbl">Client</span><select name="client_id">@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
            <label><span class="lbl">Network</span><select name="network"><option value="GOOGLE">Google Ads</option><option value="META">Meta Ads</option></select></label>
            <label><span class="lbl">Campaign name</span><input type="text" name="campaign" required></label>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;">
                <label><span class="lbl">Spend (₹)</span><input type="number" name="spend" step="0.01"></label>
                <label><span class="lbl">Clicks</span><input type="number" name="clicks"></label>
                <label><span class="lbl">Conversions</span><input type="number" name="conversions"></label>
                <label><span class="lbl">Leads</span><input type="number" name="leads"></label>
            </div>
            <label><span class="lbl">Period start</span><input type="date" name="period_start" value="{{ now()->subDays(30)->format('Y-m-d') }}"></label>
            <label><span class="lbl">Period end</span><input type="date" name="period_end" value="{{ now()->format('Y-m-d') }}"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('ad-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add report</button>
            </div>
        </form>
    </div>
</div>
@endsection
