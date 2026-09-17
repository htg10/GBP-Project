@extends('layouts.app')
@section('title', 'Services')
@section('content')
<div class="page-head">
    <div><h1>Services</h1><p>Everything you sell. Pick from this list when building an invoice.</p></div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('service-categories') }}" class="btn btn-ghost">📁 Categories</a>
        <button class="btn" onclick="openNew()">+ Add Service</button>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    @if($services->isEmpty())
        <div class="empty">No services yet. Click "Add Service" to create your price list.</div>
    @else
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Name</th><th>Category</th><th style="text-align:right;">Price</th><th style="text-align:right;">GST</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($services as $s)
                    <tr>
                        <td><strong>{{ $s->name }}</strong></td>
                        <td>{{ $s->category->name ?? '—' }}</td>
                        <td style="text-align:right;">₹{{ number_format($s->price,2) }}</td>
                        <td style="text-align:right;">{{ number_format($s->gst_percent,1) }}%</td>
                        <td><span class="badge {{ $s->status==='ACTIVE'?'teal':'gray' }}">{{ $s->status }}</span></td>
                        <td style="display:flex;gap:10px;">
                            <button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--muted);" onclick='openEdit(@json($s))'>✎</button>
                            <form method="POST" action="{{ route('services.destroy', $s) }}" onsubmit="return confirm('Delete this service?')">@csrf @method('DELETE')<button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);">🗑</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

<div class="modal-bg" id="svc-modal">
    <div class="modal">
        <h2 id="sm-title">Add service</h2>
        <form method="POST" id="svc-form">
            @csrf
            <label><span class="lbl">Name</span><input type="text" name="name" id="s-name" required></label>
            <label><span class="lbl">Category</span>
                <select name="service_category_id" id="s-category">
                    <option value="">— None —</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select>
            </label>
            <label><span class="lbl">Price (₹)</span><input type="number" step="0.01" name="price" id="s-price" required></label>
            <label><span class="lbl">GST %</span><input type="number" step="0.01" name="gst_percent" id="s-gst" value="18"></label>
            <div id="s-status-wrap" style="display:none;">
                <label><span class="lbl">Status</span>
                    <select name="status" id="s-status"><option value="ACTIVE">Active</option><option value="INACTIVE">Inactive</option></select>
                </label>
            </div>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('svc-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="sm-submit">Add service</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const svcForm = document.getElementById('svc-form');
const svcStore = "{{ route('services.store') }}";
function openNew(){
    svcForm.action = svcStore;
    document.getElementById('sm-title').textContent = 'Add service';
    document.getElementById('sm-submit').textContent = 'Add service';
    document.getElementById('s-status-wrap').style.display = 'none';
    ['name','category','price'].forEach(f => document.getElementById('s-'+f).value = '');
    document.getElementById('s-gst').value = 18;
    document.getElementById('svc-modal').classList.add('open');
}
function openEdit(s){
    svcForm.action = "{{ url('services') }}/" + s.id;
    document.getElementById('sm-title').textContent = 'Edit service';
    document.getElementById('sm-submit').textContent = 'Save changes';
    document.getElementById('s-status-wrap').style.display = 'block';
    document.getElementById('s-name').value = s.name || '';
    document.getElementById('s-category').value = s.service_category_id || '';
    document.getElementById('s-price').value = s.price || 0;
    document.getElementById('s-gst').value = s.gst_percent || 18;
    document.getElementById('s-status').value = s.status || 'ACTIVE';
    document.getElementById('svc-modal').classList.add('open');
}
</script>
@endpush
@endsection
