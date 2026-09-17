@extends('layouts.app')
@section('title', 'Credits')
@section('content')
<div class="page-head">
    <div><h1>Credits</h1><p>Your AI credit balance and usage history.</p></div>
    <div style="display:flex;align-items:center;gap:8px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:8px 14px;">
        <div style="width:36px;height:36px;border-radius:50%;background:{{ $creditBalance > 20 ? 'var(--teal-soft)' : 'var(--rose-soft)' }};display:grid;place-items:center;font-size:16px;font-weight:700;color:{{ $creditBalance > 20 ? 'var(--teal-ink)' : 'var(--rose)' }};">{{ $creditBalance > 50 ? '✓' : '!' }}</div>
        <div>
            <div style="font-size:22px;font-weight:700;line-height:1;">{{ number_format($creditBalance) }}</div>
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;">Credits Left</div>
        </div>
    </div>
</div>

{{-- Balance stats --}}
<div class="stats" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
    @if($sub)
    <div class="stat accent">
        <div style="font-size:12px;color:var(--teal-ink);">Plan</div>
        <div class="v" style="font-size:20px;">{{ ucfirst(strtolower($sub->plan)) }}</div>
        <div class="l">{{ number_format($sub->monthly_credits) }} credits/mo</div>
    </div>
    <div class="stat">
        <div style="font-size:12px;color:var(--muted);">Used this month</div>
        @php $usedTotal = collect($usageThisMonth)->sum('total_credits'); @endphp
        <div class="v">{{ number_format($usedTotal) }}</div>
        <div class="l">of {{ number_format($sub->monthly_credits) }}</div>
    </div>
    <div class="stat">
        <div style="font-size:12px;color:var(--muted);">Remaining</div>
        <div class="v" style="color:{{ $creditBalance > 20 ? 'var(--teal)' : 'var(--rose)' }};">{{ number_format($creditBalance) }}</div>
        <div class="l">{{ $sub->monthly_credits > 0 ? round($creditBalance / $sub->monthly_credits * 100) . '%' : '' }}</div>
    </div>
    <div class="stat">
        <div style="font-size:12px;color:var(--muted);">Renews</div>
        <div class="v" style="font-size:18px;">{{ $sub->renews_at ? $sub->renews_at->format('d M') : '—' }}</div>
        <div class="l">{{ $sub->status }}</div>
    </div>
    @else
    <div class="stat accent" style="grid-column:1/-1;">
        <div style="font-size:14px;">No active plan — <a href="{{ route('admin.billing') }}" style="color:var(--teal);text-decoration:underline;">subscribe to get credits</a></div>
    </div>
    @endif
</div>

{{-- Usage progress bar --}}
@if($sub && $sub->monthly_credits > 0)
<div class="card" style="margin-bottom:18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <strong style="font-size:14px;">Monthly Usage</strong>
        <span style="font-size:13px;color:var(--muted);">{{ number_format($usedTotal) }} / {{ number_format($sub->monthly_credits) }} credits</span>
    </div>
    @php $pct = min(100, round($usedTotal / $sub->monthly_credits * 100)); @endphp
    <div style="background:#f3f1ea;border-radius:8px;height:10px;overflow:hidden;">
        <div style="background:{{ $pct > 80 ? 'var(--rose)' : 'var(--teal)' }};height:100%;width:{{ $pct }}%;border-radius:8px;transition:width .3s;"></div>
    </div>
</div>
@endif

{{-- Cost table + Usage side by side --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px;margin-bottom:20px;">
    <div class="card">
        <div style="font-size:16px;font-weight:700;margin-bottom:10px;">Credit Costs</div>
        <table>
            <thead><tr><th>Action</th><th style="text-align:right;">Credits</th></tr></thead>
            <tbody>
                @foreach($creditCosts as $action => $cost)
                <tr>
                    <td>{{ app(\App\Services\CreditService::class)->actionLabel($action) }}</td>
                    <td style="text-align:right;"><span class="badge {{ $cost >= 5 ? 'amber' : ($cost >= 2 ? 'gray' : 'teal') }}" style="min-width:28px;text-align:center;">{{ $cost }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <div style="font-size:16px;font-weight:700;margin-bottom:10px;">This Month's Breakdown</div>
        @if(empty($usageThisMonth))
            <div class="empty" style="padding:30px 20px;">No usage yet this month.</div>
        @else
            <table>
                <thead><tr><th>Action</th><th style="text-align:right;">Times</th><th style="text-align:right;">Credits</th></tr></thead>
                <tbody>
                    @foreach($usageThisMonth as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td style="text-align:right;">{{ $row['times'] }}x</td>
                        <td style="text-align:right;font-weight:700;">{{ $row['total_credits'] }}</td>
                    </tr>
                    @endforeach
                    <tr style="border-top:2px solid var(--line);">
                        <td style="font-weight:700;">Total</td>
                        <td></td>
                        <td style="text-align:right;font-weight:700;color:var(--teal);">{{ $usedTotal }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Plan cards --}}
<div class="card" style="margin-bottom:20px;">
    <div style="font-size:16px;font-weight:700;margin-bottom:12px;">Plans</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
        @foreach($planCredits as $plan => $credits)
            @php $isCurrent = $sub && $sub->plan === $plan; @endphp
            <div style="padding:18px;border-radius:14px;text-align:center;{{ $isCurrent ? 'border:2px solid var(--teal);background:var(--teal-soft);' : 'border:1px solid var(--line);' }}">
                <div style="font-weight:700;font-size:16px;">{{ ucfirst(strtolower($plan)) }}</div>
                <div style="font-size:26px;font-weight:700;color:var(--teal);margin:8px 0 2px;">{{ number_format($credits) }}</div>
                <div style="font-size:12px;color:var(--muted);">credits/mo</div>
                @if($isCurrent)<div style="margin-top:6px;"><span class="badge teal">Current Plan</span></div>@endif
            </div>
        @endforeach
    </div>
</div>

{{-- Transaction ledger --}}
<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);"><strong style="font-size:16px;">Recent Transactions</strong></div>
    @if($ledger->isEmpty())
        <div class="empty">No credit transactions yet.</div>
    @else
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Date</th><th>Action</th><th>Description</th><th style="text-align:right;">Credits</th><th style="text-align:right;">Balance</th></tr></thead>
            <tbody>
                @foreach($ledger as $entry)
                <tr>
                    <td style="white-space:nowrap;">{{ $entry->created_at->format('d M, h:i A') }}</td>
                    <td><span class="badge {{ $entry->amount > 0 ? 'teal' : 'rose' }}">{{ app(\App\Services\CreditService::class)->actionLabel($entry->action) }}</span></td>
                    <td style="color:var(--muted);font-size:12.5px;">{{ $entry->description ?? '—' }}</td>
                    <td style="text-align:right;font-weight:700;color:{{ $entry->amount > 0 ? 'var(--teal)' : 'var(--rose)' }};">{{ $entry->amount > 0 ? '+' : '' }}{{ $entry->amount }}</td>
                    <td style="text-align:right;">{{ number_format($entry->balance_after) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endsection
