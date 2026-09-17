@extends('layouts.admin')
@section('title', 'Billing')
@section('content')
<div class="page-head"><h1>Billing</h1><p>Subscription status, credits, and payment history.</p></div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:22px;">
    <div style="background:var(--teal-soft);border:1px solid #d5deff;border-radius:12px;padding:14px 18px;font-size:14px;">
        Plan: <strong>{{ $sub->plan ?? 'None' }}</strong>
        @if($sub)<span style="margin-left:6px;font-size:12px;color:var(--teal-ink);">({{ $sub->status }})</span>@endif
    </div>
    <div style="background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 18px;font-size:14px;">
        Credits: <strong style="color:var(--teal);">{{ number_format($creditBalance) }}</strong>
        @if($sub && $sub->monthly_credits > 0)
            <span style="font-size:12px;color:var(--muted);">/ {{ number_format($sub->monthly_credits) }} monthly</span>
        @endif
    </div>
    <a href="{{ route('admin.plans') }}" style="background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 18px;font-size:14px;display:flex;align-items:center;justify-content:space-between;">
        Manage Plans <span>→</span>
    </a>
</div>

{{-- Manual credit top-up --}}
<div class="card" style="max-width:440px;">
    <strong style="font-size:14px;">Manual credit top-up</strong>
    <form method="POST" action="{{ route('admin.billing.topup') }}" style="display:flex;gap:10px;align-items:flex-end;margin-top:10px;">
        @csrf
        <input type="hidden" name="agency_id" value="{{ auth()->user()->agency_id }}">
        <label style="flex:1;"><span class="lbl">Credits to add</span><input type="number" name="credits" min="1" max="10000" value="100" required></label>
        <button type="submit" class="btn" style="margin-bottom:1px;">+ Add credits</button>
    </form>
</div>

@if($payments->count())
    <div style="margin-top:26px;">
        <strong style="font-size:14px;">Payment history</strong>
        <div class="card" style="padding:0;overflow:hidden;margin-top:10px;">
            <table>
                <thead><tr><th>Date</th><th>Plan</th><th style="text-align:right;">Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($payments as $pay)
                        <tr>
                            <td>{{ $pay->created_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $pay->plan }}</td>
                            <td style="text-align:right;">₹{{ number_format($pay->amount / 100) }}</td>
                            <td><span class="badge {{ $pay->status==='PAID'?'teal':'dark' }}">{{ $pay->status }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
