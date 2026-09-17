@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="page-head">
    <div><h1>Expenses</h1><p>Money going out — rent, stock, salaries, utilities.</p></div>
    <button class="btn" onclick="document.getElementById('exp-modal').classList.add('open')">+ Add Expense</button>
</div>

<div class="stats" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));">
    <div class="stat"><div>↓</div><div class="v" style="color:var(--rose);">₹{{ number_format($stats['spent'],2) }}</div><div class="l">Spent this month</div></div>
    <div class="stat"><div>▤</div><div class="v">{{ $stats['count'] }}</div><div class="l">Entries {{ $month ? 'in view' : '(all time)' }}</div></div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid var(--line);display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <form method="GET" style="display:flex;gap:8px;">
            <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()">
            @if($month)<a href="{{ route('expenses') }}" class="btn btn-ghost" style="padding:8px 12px;font-size:12.5px;">All time</a>@endif
        </form>
    </div>
    @if($expenses->isEmpty())
        <div class="empty">No expenses logged{{ $month ? ' for this month' : '' }}.</div>
    @else
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Date</th><th>Expense</th><th>Category</th><th>Method</th><th style="text-align:right;">Amount</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($expenses as $e)
                    <tr>
                        <td>{{ $e->date->format('d M Y') }}</td>
                        <td>{{ $e->description }}</td>
                        <td>{{ $e->category ?: '—' }}</td>
                        <td><span class="badge gray">{{ $e->method }}</span></td>
                        <td style="text-align:right;color:var(--rose);">₹{{ number_format($e->amount,2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('expenses.destroy', $e) }}" onsubmit="return confirm('Delete this expense?')">@csrf @method('DELETE')<button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);">🗑</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

<div class="modal-bg" id="exp-modal">
    <div class="modal">
        <h2>Add expense</h2>
        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf
            <label><span class="lbl">Date</span><input type="date" name="date" value="{{ now()->format('Y-m-d') }}" required></label>
            <label><span class="lbl">Description</span><input type="text" name="description" required placeholder="e.g. Office rent"></label>
            <label><span class="lbl">Category</span><input type="text" name="category" placeholder="e.g. Rent, Salaries, Utilities"></label>
            <label><span class="lbl">Method</span>
                <select name="method"><option value="BANK">Bank transfer</option><option value="CASH">Cash</option><option value="UPI">UPI</option><option value="CARD">Card</option><option value="OTHER">Other</option></select>
            </label>
            <label><span class="lbl">Amount (₹)</span><input type="number" step="0.01" name="amount" required></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('exp-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add expense</button>
            </div>
        </form>
    </div>
</div>
@endsection
