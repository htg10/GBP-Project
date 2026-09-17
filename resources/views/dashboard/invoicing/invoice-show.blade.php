@extends('layouts.app')
@section('title', $invoice->invoice_number)
@push('head')
<style>
@media print {
    .sidebar, .topbar, .page-head, .no-print { display: none !important; }
    .main { padding: 0 !important; max-width: 100% !important; }
    body { background: #fff !important; }
}
</style>
@endpush
@section('content')
<div style="margin-bottom:6px;" class="no-print"><a href="{{ route('invoices') }}" style="font-size:13px;color:var(--muted);">← All invoices</a></div>
<div class="page-head no-print">
    <div><h1>{{ $invoice->invoice_number }}</h1><p>{{ $invoice->client->name }}</p></div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-ghost" onclick="window.print()">🖨 Print / Save as PDF</button>
        @if($invoice->status === 'DRAFT')
            <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}">@csrf<button class="btn btn-ghost">Mark as sent</button></form>
            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this draft invoice?')">@csrf @method('DELETE')<button class="btn btn-ghost" style="color:var(--rose);">Delete</button></form>
        @endif
        @if(in_array($invoice->status, ['SENT','OVERDUE']))
            <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">@csrf<button class="btn">Mark as paid</button></form>
        @endif
    </div>
</div>

<div class="card" style="max-width:760px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:20px;border-bottom:2px solid var(--ink);margin-bottom:20px;">
        <div>
            <h2 style="font-size:20px;">{{ $settings->company_name ?: 'Your Company' }}</h2>
            @if($settings->address)<div style="font-size:12.5px;color:var(--muted);margin-top:4px;">{{ $settings->address }}</div>@endif
            @if($settings->gstin)<div style="font-size:12.5px;color:var(--muted);">GSTIN: {{ $settings->gstin }}</div>@endif
        </div>
        <div style="text-align:right;">
            <div style="font-size:22px;font-weight:700;color:var(--teal);">INVOICE</div>
            <div style="font-size:13px;margin-top:4px;">{{ $invoice->invoice_number }}</div>
            <span class="badge {{ ['DRAFT'=>'gray','SENT'=>'amber','PAID'=>'teal','OVERDUE'=>'rose'][$invoice->status] ?? 'gray' }}" style="margin-top:6px;">{{ $invoice->status }}</span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <div>
            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:6px;">Billed to</div>
            <div style="font-weight:600;font-size:14px;">{{ $invoice->client->name }}</div>
            @if($invoice->client->billing_address)<div style="font-size:12.5px;color:var(--muted);">{{ $invoice->client->billing_address }}</div>@endif
            @if($invoice->client->gstin)<div style="font-size:12.5px;color:var(--muted);">GSTIN: {{ $invoice->client->gstin }}</div>@endif
            @if($invoice->client->email)<div style="font-size:12.5px;color:var(--muted);">{{ $invoice->client->email }}</div>@endif
        </div>
        <div style="text-align:right;">
            <div style="font-size:12.5px;color:var(--muted);">Issue date: <strong style="color:var(--ink);">{{ $invoice->issue_date->format('d M Y') }}</strong></div>
            <div style="font-size:12.5px;color:var(--muted);margin-top:4px;">Due date: <strong style="color:var(--ink);">{{ $invoice->due_date?->format('d M Y') ?: '—' }}</strong></div>
        </div>
    </div>

    <table style="margin-bottom:20px;">
        <thead><tr><th>Description</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Rate</th><th style="text-align:right;">GST%</th><th style="text-align:right;">Amount</th></tr></thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align:right;">{{ rtrim(rtrim(number_format($item->quantity,2),'0'),'.') }}</td>
                    <td style="text-align:right;">₹{{ number_format($item->unit_price,2) }}</td>
                    <td style="text-align:right;">{{ number_format($item->gst_percent,1) }}%</td>
                    <td style="text-align:right;">₹{{ number_format($item->amount,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display:flex;justify-content:flex-end;">
        <div style="width:240px;">
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;"><span>Subtotal</span><span>₹{{ number_format($invoice->subtotal,2) }}</span></div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;"><span>GST</span><span>₹{{ number_format($invoice->gst_total,2) }}</span></div>
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-top:1.5px solid var(--ink);font-size:15px;font-weight:700;"><span>Total</span><span>₹{{ number_format($invoice->total,2) }}</span></div>
        </div>
    </div>

    @if($invoice->notes)
        <div style="border-top:1px solid var(--line);margin-top:16px;padding-top:14px;font-size:12.5px;color:#3a4a45;">{{ $invoice->notes }}</div>
    @endif

    @if($settings->bank_name)
        <div style="border-top:1px solid var(--line);margin-top:16px;padding-top:14px;font-size:12px;color:var(--muted);">
            Pay to: {{ $settings->bank_name }} · A/C {{ $settings->bank_account }} · IFSC {{ $settings->ifsc }}
        </div>
    @endif
</div>
@endsection
