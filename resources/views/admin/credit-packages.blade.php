@extends('layouts.admin')
@section('title', 'Credit Packages')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
    <div><h1 style="font-size:22px;font-weight:800;">Credit Packs</h1><p style="font-size:13px;color:var(--muted);margin-top:3px;">Create credit packs clients can buy. Pause any pack to hide it.</p></div>
    <button class="btn" onclick="openCreate()" style="gap:6px;">
        <span style="width:20px;height:20px;border-radius:6px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:14px;">+</span> New package
    </button>
</div>

<div class="cp-grid">
    @forelse($packages as $p)
        <div class="card cp-card {{ $p->is_active ? '' : 'cp-paused' }}">
            <div style="display:flex;align-items:flex-start;gap:14px;">
                <div class="cp-icon">⚡</div>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <strong style="font-size:15px;">{{ $p->name }}</strong>
                        <span class="badge {{ $p->is_active ? 'teal' : 'dark' }}" style="font-size:10.5px;">{{ $p->is_active ? 'Active' : 'Paused' }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--muted);margin-top:2px;">Sort: {{ $p->sort ?? 0 }}</div>
                </div>
            </div>

            <div class="cp-stats">
                <div class="cp-stat">
                    <div class="cp-stat-val" style="color:var(--purple);">{{ number_format($p->credits) }}</div>
                    <div class="cp-stat-label">Credits</div>
                </div>
                <div class="cp-stat">
                    <div class="cp-stat-val" style="color:var(--teal);">₹{{ number_format($p->price) }}</div>
                    <div class="cp-stat-label">Price (incl. GST)</div>
                </div>
                <div class="cp-stat">
                    <div class="cp-stat-val">₹{{ number_format($p->gstAmount(),2) }}</div>
                    <div class="cp-stat-label">GST ({{ $p->gst_rate }}%)</div>
                </div>
            </div>

            <div style="display:flex;gap:8px;padding-top:12px;border-top:1px solid var(--line);">
                <form method="POST" action="{{ route('admin.credit-packages.toggle', $p) }}" style="flex:1;">@csrf
                    <button class="btn btn-ghost au-act-btn" style="width:100%;">{{ $p->is_active ? '⏸ Pause' : '▶ Activate' }}</button>
                </form>
                <button class="btn btn-ghost au-act-btn" onclick='openEdit(@json($p))' style="flex:1;">✎ Edit</button>
                <form method="POST" action="{{ route('admin.credit-packages.destroy', $p) }}" style="flex:1;" onsubmit="return confirm('Delete {{ addslashes($p->name) }}?')">
                    @csrf @method('DELETE')
                    <button class="btn au-act-btn au-del-btn" style="width:100%;">🗑</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card" style="grid-column:1/-1;text-align:center;padding:40px;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:10px;">⚡</div>
            No credit packages yet. Create your first one.
        </div>
    @endforelse
</div>

{{-- Modal --}}
<div class="modal-bg" id="pkg-modal">
    <div class="modal" style="max-width:440px;">
        <h2 id="pm-title" style="font-size:18px;font-weight:700;margin-bottom:16px;">New package</h2>
        <form method="POST" id="pkg-form" action="{{ route('admin.credit-packages.store') }}">
            @csrf
            <label><span class="lbl">Package name</span><input type="text" name="name" id="f-name" placeholder="e.g. Booster Pack" required></label>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <label><span class="lbl">Credits</span><input type="number" name="credits" id="f-credits" min="1" value="500" required></label>
                <label><span class="lbl">Price ₹ (incl GST)</span><input type="number" name="price" id="f-price" min="0" value="0" oninput="calcGst()" required></label>
                <label><span class="lbl">GST %</span><input type="number" name="gst_rate" id="f-gst" min="0" max="50" value="18" oninput="calcGst()" required></label>
            </div>
            <div id="gst-preview" style="background:var(--teal-soft);color:var(--teal-ink);border-radius:10px;padding:9px 12px;font-size:12.5px;margin-top:8px;font-weight:600;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;">
                <label><span class="lbl">Sort order</span><input type="number" name="sort" id="f-sort" min="0" value="0"></label>
                <label style="display:flex;align-items:center;gap:8px;margin-top:28px;font-size:13px;"><input type="checkbox" name="is_active" id="f-active" value="1" checked style="width:16px;height:16px;"> Active</label>
            </div>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('pkg-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="pm-submit">Create package</button>
            </div>
        </form>
    </div>
</div>

@push('head')
<style>
.cp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;}
.cp-card{padding:18px;display:flex;flex-direction:column;gap:14px;transition:box-shadow .15s,border-color .15s;}
.cp-card:hover{border-color:#d3d9e8;box-shadow:0 2px 12px rgba(20,30,60,.08);}
.cp-card.cp-paused{opacity:.6;border-style:dashed;}
.cp-icon{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#a78bfa);color:#fff;display:grid;place-items:center;font-size:18px;flex-shrink:0;}
.cp-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:10px 0;border-top:1px solid var(--line);}
.cp-stat{text-align:center;}
.cp-stat-val{font-size:16px;font-weight:700;}
.cp-stat-label{font-size:10.5px;color:var(--muted);margin-top:2px;}
.au-act-btn{padding:6px 12px !important;font-size:12px !important;}
.au-del-btn{background:var(--rose-soft) !important;color:var(--rose) !important;border:1px solid #f8c4c9 !important;}
.au-del-btn:hover{background:#fde0e3 !important;}
@media(max-width:640px){.cp-grid{grid-template-columns:1fr;}}
</style>
@endpush
@push('scripts')
<script>
const pform = document.getElementById('pkg-form');
const createUrl = "{{ route('admin.credit-packages.store') }}";
function calcGst(){
    const price=+document.getElementById('f-price').value||0, gst=+document.getElementById('f-gst').value||0;
    const base=price/(1+gst/100);
    document.getElementById('gst-preview').textContent = `Base ₹${base.toFixed(2)}  +  ${gst}% GST ₹${(price-base).toFixed(2)}  =  ₹${price.toFixed(2)}`;
}
function openCreate(){
    document.getElementById('pm-title').textContent='New package';
    document.getElementById('pm-submit').textContent='Create package';
    pform.action=createUrl;
    document.getElementById('f-name').value=''; document.getElementById('f-credits').value=500;
    document.getElementById('f-price').value=0; document.getElementById('f-gst').value=18;
    document.getElementById('f-sort').value=0; document.getElementById('f-active').checked=true;
    calcGst(); document.getElementById('pkg-modal').classList.add('open');
}
function openEdit(p){
    document.getElementById('pm-title').textContent='Edit package';
    document.getElementById('pm-submit').textContent='Save changes';
    pform.action="{{ url('admin/credit-packages') }}/"+p.id;
    document.getElementById('f-name').value=p.name; document.getElementById('f-credits').value=p.credits;
    document.getElementById('f-price').value=p.price; document.getElementById('f-gst').value=p.gst_rate;
    document.getElementById('f-sort').value=p.sort||0; document.getElementById('f-active').checked=!!p.is_active;
    calcGst(); document.getElementById('pkg-modal').classList.add('open');
}
</script>
@endpush
@endsection
