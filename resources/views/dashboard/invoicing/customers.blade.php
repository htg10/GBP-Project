@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="page-head">
    <div><h1>Customers</h1><p>The people and businesses you invoice.</p></div>
    <button class="btn" onclick="openNew()">+ Add Customer</button>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    @if($customers->isEmpty())
        <div class="empty">No customers yet. Click "Add Customer" — this list is shared with your Clients page.</div>
    @else
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Invoices</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($customers as $c)
                    <tr>
                        <td><strong>{{ $c->name }}</strong>@if($c->gstin)<div style="font-size:11px;color:var(--muted);">GSTIN: {{ $c->gstin }}</div>@endif</td>
                        <td>{{ $c->phone ?: '—' }}</td>
                        <td>{{ $c->email ?: '—' }}</td>
                        <td>{{ $c->invoices_count }}</td>
                        <td><button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--muted);" onclick='openEdit(@json($c))'>✎ Edit</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

<div class="modal-bg" id="cust-modal">
    <div class="modal">
        <h2 id="cm-title">Add customer</h2>
        <form method="POST" id="cust-form">
            @csrf
            <label><span class="lbl">Name</span><input type="text" name="name" id="c-name" required></label>
            <label><span class="lbl">Phone</span><input type="text" name="phone" id="c-phone"></label>
            <label><span class="lbl">Email</span><input type="email" name="email" id="c-email"></label>
            <label><span class="lbl">GSTIN (optional)</span><input type="text" name="gstin" id="c-gstin"></label>
            <label><span class="lbl">Billing address</span><input type="text" name="billing_address" id="c-address"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('cust-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="cm-submit">Add customer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const custForm = document.getElementById('cust-form');
const custStore = "{{ route('customers.store') }}";
function openNew(){
    custForm.action = custStore;
    document.getElementById('cm-title').textContent = 'Add customer';
    document.getElementById('cm-submit').textContent = 'Add customer';
    ['name','phone','email','gstin','address'].forEach(f => document.getElementById('c-'+f).value = '');
    document.getElementById('cust-modal').classList.add('open');
}
function openEdit(c){
    custForm.action = "{{ url('customers') }}/" + c.id;
    document.getElementById('cm-title').textContent = 'Edit customer';
    document.getElementById('cm-submit').textContent = 'Save changes';
    document.getElementById('c-name').value = c.name || '';
    document.getElementById('c-phone').value = c.phone || '';
    document.getElementById('c-email').value = c.email || '';
    document.getElementById('c-gstin').value = c.gstin || '';
    document.getElementById('c-address').value = c.billing_address || '';
    document.getElementById('cust-modal').classList.add('open');
}
</script>
@endpush
@endsection
