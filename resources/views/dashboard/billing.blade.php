@extends('layouts.app')
@section('title', 'Billing')
@section('content')
<div class="page-head">
    <div><h1>Billing</h1><p>Your subscription, payment history and downloadable invoices.</p></div>
    <a href="{{ route('plans') }}" class="btn">⬆ Change Plan</a>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;" class="bill-cards">
    <div class="card">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Current Plan</div>
        <div style="font-size:26px;font-weight:800;margin-top:6px;color:var(--teal);">{{ $sub->plan ?? 'None' }}</div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Status: {{ $sub->status ?? '—' }}</div>
    </div>
    <div class="card">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">AI Credits</div>
        <div style="font-size:26px;font-weight:800;margin-top:6px;color:var(--purple);">{{ number_format($sub->credit_balance ?? 0) }}</div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">{{ number_format($sub->monthly_credits ?? 0) }} / month</div>
    </div>
    <div class="card">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Renews</div>
        <div style="font-size:26px;font-weight:800;margin-top:6px;">{{ $sub && $sub->renews_at ? $sub->renews_at->format('d M') : '—' }}</div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">{{ $sub && $sub->renews_at ? $sub->renews_at->format('Y') : 'No active renewal' }}</div>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);"><strong style="font-size:15px;">Payment History</strong></div>
    @if($payments->isEmpty())
        <div class="empty" style="padding:36px;">No payments yet. Your invoices will appear here after your first payment.</div>
    @else
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Invoice #</th><th>Date</th><th>Plan</th><th style="text-align:right;">Amount</th><th>Status</th><th style="text-align:right;">Invoice</th></tr></thead>
            <tbody>
                @foreach($payments as $pay)
                    <tr>
                        <td style="font-family:monospace;font-size:12.5px;">INV-{{ str_pad($pay->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $pay->created_at->format('d M Y') }}</td>
                        <td>{{ $pay->plan }}</td>
                        <td style="text-align:right;font-weight:600;">₹{{ number_format($pay->amount / 100, 2) }}</td>
                        <td><span class="badge {{ $pay->status==='PAID'?'teal':($pay->status==='FAILED'?'rose':'gray') }}">{{ $pay->status }}</span></td>
                        <td style="text-align:right;">
                            @if($pay->status === 'PAID')
                                <a href="{{ route('client-billing.invoice', $pay) }}" target="_blank" class="btn btn-ghost" style="padding:6px 12px;font-size:12px;">⬇ PDF</a>
                            @else
                                <span style="color:var(--muted);font-size:12px;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

@push('head')
<style>@media (max-width:760px){ .bill-cards{grid-template-columns:1fr !important;} }</style>
@endpush
@endsection
