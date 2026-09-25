@extends('layouts.admin')
@section('title', 'Plans')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
    <div><h1 style="font-size:22px;font-weight:800;">Plans</h1><p style="font-size:13px;color:var(--muted);margin-top:3px;">GST-inclusive pricing with per-plan module access.</p></div>
    <button class="btn" onclick="openCreate()" style="gap:6px;">
        <span style="width:20px;height:20px;border-radius:6px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:14px;">+</span> New plan
    </button>
</div>

<div class="ap-grid">
    @forelse($plans as $p)
        <div class="card ap-card {{ $p->is_active ? '' : 'ap-hidden' }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <strong style="font-size:16px;">{{ $p->name }}</strong>
                        <span class="badge {{ $p->is_active ? 'teal' : 'dark' }}" style="font-size:10.5px;">{{ $p->is_active ? 'Active' : 'Hidden' }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--muted);margin-top:2px;">{{ $p->code }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:24px;font-weight:800;color:var(--teal);">₹{{ number_format($p->price) }}</div>
                    <div style="font-size:11px;color:var(--muted);">/month incl. GST</div>
                </div>
            </div>

            <div class="ap-details">
                <div class="ap-detail-row">
                    <span class="ap-detail-label">Base Price</span>
                    <span>₹{{ number_format($p->baseAmount(),2) }}</span>
                </div>
                <div class="ap-detail-row">
                    <span class="ap-detail-label">GST ({{ $p->gst_rate }}%)</span>
                    <span>₹{{ number_format($p->gstAmount(),2) }}</span>
                </div>
                <div class="ap-detail-row">
                    <span class="ap-detail-label">Credits / month</span>
                    <span style="font-weight:700;color:var(--purple);">{{ number_format($p->credits) }}</span>
                </div>
                <div class="ap-detail-row">
                    <span class="ap-detail-label">Module Access</span>
                    <span>{{ empty($p->permissions) ? 'All modules' : count($p->permissions).' modules' }}</span>
                </div>
            </div>

            @if(!empty($p->features))
            <div style="padding-top:10px;border-top:1px solid var(--line);">
                <div style="font-size:10.5px;font-weight:600;color:var(--muted);text-transform:uppercase;margin-bottom:6px;">Features</div>
                @foreach($p->features as $f)
                    <div style="font-size:12.5px;padding:3px 0;color:var(--ink);">✓ {{ $f }}</div>
                @endforeach
            </div>
            @endif

            <div class="au-actions" style="padding-top:12px;border-top:1px solid var(--line);">
                <button class="btn btn-ghost au-act-btn" onclick='openEdit(@json($p))'>✎ Edit</button>
                <form method="POST" action="{{ route('admin.plans.destroy', $p) }}" style="display:inline;flex:1;" onsubmit="return confirm('Delete {{ addslashes($p->name) }}?')">
                    @csrf @method('DELETE')
                    <button class="btn au-act-btn au-del-btn" style="width:100%;">🗑 Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card" style="grid-column:1/-1;text-align:center;padding:40px;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:10px;">📋</div>
            No plans yet. Create your first plan.
        </div>
    @endforelse
</div>

{{-- Modal --}}
<div class="modal-bg" id="plan-modal">
    <div class="modal" style="max-width:560px;">
        <h2 id="pm-title" style="font-size:18px;font-weight:700;margin-bottom:16px;">New plan</h2>
        <form method="POST" id="plan-form" action="{{ route('admin.plans.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <label><span class="lbl">Plan name</span><input type="text" name="name" id="f-name" required></label>
                <label><span class="lbl">Code (unique)</span><input type="text" name="code" id="f-code" placeholder="GROWTH" required></label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <label><span class="lbl">Price ₹ (incl. GST)</span><input type="number" name="price" id="f-price" min="0" value="0" oninput="calcGst()" required></label>
                <label><span class="lbl">GST %</span><input type="number" name="gst_rate" id="f-gst" min="0" max="50" value="18" oninput="calcGst()" required></label>
                <label><span class="lbl">Credits / mo</span><input type="number" name="credits" id="f-credits" min="0" value="0" required></label>
            </div>
            <div id="gst-preview" style="background:var(--teal-soft);color:var(--teal-ink);border-radius:10px;padding:9px 12px;font-size:12.5px;margin-top:8px;font-weight:600;"></div>

            <label><span class="lbl">Features (one per line)</span><textarea name="features" id="f-features" rows="3" style="width:100%;padding:10px 12px;border-radius:9px;border:1px solid var(--line);font-family:inherit;font-size:14px;background:#fcfcfa;"></textarea></label>

            <span class="lbl" style="margin-top:12px;">Module access (unchecked = all allowed)</span>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:4px;">
                @foreach($modules as $key => $label)
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:var(--ink);margin:0;">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" class="perm-cb" style="width:16px;height:16px;"> {{ $label }}
                    </label>
                @endforeach
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;">
                <label><span class="lbl">Sort order</span><input type="number" name="sort" id="f-sort" min="0" value="0"></label>
                <label style="display:flex;align-items:center;gap:8px;margin-top:28px;font-size:13px;"><input type="checkbox" name="is_active" id="f-active" value="1" checked style="width:16px;height:16px;"> Active (visible to users)</label>
            </div>

            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('plan-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;" id="pm-submit">Create plan</button>
            </div>
        </form>
    </div>
</div>

@push('head')
<style>
.ap-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;}
.ap-card{padding:20px;display:flex;flex-direction:column;gap:12px;transition:box-shadow .15s,border-color .15s;}
.ap-card:hover{border-color:#d3d9e8;box-shadow:0 2px 12px rgba(20,30,60,.08);}
.ap-card.ap-hidden{opacity:.65;border-style:dashed;}
.ap-details{display:flex;flex-direction:column;gap:6px;padding:10px 14px;background:var(--paper);border-radius:10px;}
.ap-detail-row{display:flex;justify-content:space-between;font-size:12.5px;}
.ap-detail-label{color:var(--muted);}
.au-actions{display:flex;gap:8px;}
.au-act-btn{padding:6px 12px !important;font-size:12px !important;flex:1;}
.au-del-btn{background:var(--rose-soft) !important;color:var(--rose) !important;border:1px solid #f8c4c9 !important;}
.au-del-btn:hover{background:#fde0e3 !important;}
@media(max-width:640px){.ap-grid{grid-template-columns:1fr;}}
</style>
@endpush
@push('scripts')
<script>
const pform = document.getElementById('plan-form');
const createUrl = "{{ route('admin.plans.store') }}";
function calcGst(){
    const price = +document.getElementById('f-price').value || 0;
    const gst = +document.getElementById('f-gst').value || 0;
    const base = price / (1 + gst/100);
    const gstAmt = price - base;
    document.getElementById('gst-preview').textContent =
        `Base ₹${base.toFixed(2)}  +  ${gst}% GST ₹${gstAmt.toFixed(2)}  =  ₹${price.toFixed(2)} total`;
}
function setPerms(list){
    document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = (list||[]).includes(cb.value));
}
function openCreate(){
    document.getElementById('pm-title').textContent = 'New plan';
    document.getElementById('pm-submit').textContent = 'Create plan';
    pform.action = createUrl;
    document.getElementById('f-name').value=''; document.getElementById('f-code').value='';
    document.getElementById('f-price').value=0; document.getElementById('f-gst').value=18;
    document.getElementById('f-credits').value=0; document.getElementById('f-features').value='';
    document.getElementById('f-sort').value=0; document.getElementById('f-active').checked=true;
    setPerms([]); calcGst();
    document.getElementById('plan-modal').classList.add('open');
}
function openEdit(p){
    document.getElementById('pm-title').textContent = 'Edit plan';
    document.getElementById('pm-submit').textContent = 'Save changes';
    pform.action = "{{ url('admin/plans') }}/" + p.id;
    document.getElementById('f-name').value=p.name; document.getElementById('f-code').value=p.code;
    document.getElementById('f-price').value=p.price; document.getElementById('f-gst').value=p.gst_rate;
    document.getElementById('f-credits').value=p.credits;
    document.getElementById('f-features').value=(p.features||[]).join('\n');
    document.getElementById('f-sort').value=p.sort||0; document.getElementById('f-active').checked=!!p.is_active;
    setPerms(p.permissions||[]); calcGst();
    document.getElementById('plan-modal').classList.add('open');
}
</script>
@endpush
@endsection
