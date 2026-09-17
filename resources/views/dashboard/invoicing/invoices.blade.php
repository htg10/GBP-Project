@extends('layouts.app')
@section('title', 'Invoices')
@section('content')
<div class="page-head">
    <div><h1>Invoices</h1><p>Create an invoice and send it straight to a client.</p></div>
    <a href="{{ route('invoices.create') }}" class="btn">+ New Invoice</a>
</div>

<div class="stats" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
    <div class="stat"><div>⏱</div><div class="v">₹{{ number_format($stats['outstanding'],2) }}</div><div class="l">Outstanding</div></div>
    <div class="stat accent"><div>✓</div><div class="v">₹{{ number_format($stats['paid_this_month'],2) }}</div><div class="l">Paid this month</div></div>
    <div class="stat"><div>📄</div><div class="v">{{ $stats['drafts'] }}</div><div class="l">Drafts</div></div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);">
        <form method="GET">
            <input type="search" name="q" value="{{ $search }}" placeholder="🔍 Search invoice # or client…" style="max-width:320px;">
        </form>
    </div>
    @if($invoices->isEmpty())
        <div class="empty">No invoices yet. Click "New Invoice" to create your first one.</div>
    @else
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Number</th><th>Customer</th><th>Date</th><th style="text-align:right;">Total</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($invoices as $inv)
                    @php $badge = ['DRAFT'=>'gray','SENT'=>'amber','PAID'=>'teal','OVERDUE'=>'rose','CANCELLED'=>'gray'][$inv->status] ?? 'gray'; @endphp
                    <tr>
                        <td><a href="{{ route('invoices.show', $inv) }}" style="font-weight:600;color:var(--teal-ink);">{{ $inv->invoice_number }}</a></td>
                        <td>{{ $inv->client->name }}</td>
                        <td>{{ $inv->issue_date->format('d M Y') }}</td>
                        <td style="text-align:right;">₹{{ number_format($inv->total,2) }}</td>
                        <td>{{ $inv->due_date?->format('d M Y') ?: '—' }}</td>
                        <td><span class="badge {{ $badge }}">{{ $inv->status }}</span></td>
                        <td><a href="{{ route('invoices.show', $inv) }}" style="font-size:12.5px;color:var(--teal);font-weight:600;">View →</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endsection
