@extends('layouts.app')
@section('title', 'Google Posts')
@section('content')

<div class="page-head">
    <div><h1>Google Posts</h1><p>Publish updates, offers, and events to your Google Business Profile.</p></div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-ghost" onclick="document.getElementById('photo-modal').classList.add('open')">+ New Photo</button>
        <button class="btn" onclick="openPostModal()">+ Add Post</button>
    </div>
</div>

@if($locations->isEmpty())
    <div class="alert info">Add a client with a Google location first — posts and photos attach to a location.</div>
@endif

{{-- Tab switcher --}}
<div style="display:flex;gap:6px;margin-bottom:16px;">
    <button type="button" class="tab-btn active" data-tab="posts" onclick="switchTab('posts', this)" style="border:1px solid var(--line);border-radius:999px;padding:7px 14px;font-size:13px;font-weight:500;background:var(--ink);color:#fff;">Posts ({{ $posts->count() }})</button>
    <button type="button" class="tab-btn" data-tab="photos" onclick="switchTab('photos', this)" style="border:1px solid var(--line);border-radius:999px;padding:7px 14px;font-size:13px;font-weight:500;background:var(--card);color:var(--muted);">Photos ({{ $photos->count() }})</button>
</div>

@php $badgeFor = fn($s) => $s==='PUBLISHED'?'teal':($s==='SCHEDULED'?'amber':($s==='FAILED'?'rose':'gray')); @endphp

{{-- Posts list --}}
<div id="tab-posts">
    @if($posts->isEmpty())
        <div class="card"><div class="empty">No posts yet. Click "Add Post" to create your first update, offer, or event.</div></div>
    @else
        <div class="card" style="padding:0;overflow:hidden;">
            <table>
                <thead>
                    <tr><th style="width:50px;"></th><th>Post</th><th>Type</th><th>Status</th><th>Date</th><th style="width:80px;"></th></tr>
                </thead>
                <tbody>
                    @foreach($posts as $p)
                    <tr>
                        <td>
                            @if($p->image)
                                <img src="{{ $p->image }}" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:8px;background:#f3f1ea;">
                            @else
                                <div style="width:42px;height:42px;border-radius:8px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-size:14px;">&#9998;</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:13.5px;">{{ Str::limit($p->body, 60) }}</div>
                            <div style="font-size:12px;color:var(--muted);">{{ $p->location->title ?? $p->location->google_name }} &middot; {{ $p->location->client->name }}</div>
                        </td>
                        <td><span class="badge {{ $p->type === 'OFFER' ? 'amber' : ($p->type === 'EVENT' ? 'rose' : 'teal') }}">{{ ucfirst(strtolower($p->type)) }}</span></td>
                        <td><span class="badge {{ $badgeFor($p->status) }}">{{ $p->status === 'PUBLISHED' ? 'Live' : $p->status }}</span></td>
                        <td style="font-size:12px;color:var(--muted);">{{ ($p->scheduled_at ?? $p->created_at)->format('M d, Y') }}</td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                @if(in_array($p->status, ['SCHEDULED','FAILED']))
                                    <form method="POST" action="{{ route('gbp-content.posts.publish', $p) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost" style="padding:5px 10px;font-size:11px;">Publish</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Photos grid --}}
<div id="tab-photos" style="display:none;">
    @if($photos->isEmpty())
        <div class="card"><div class="empty">No photos yet. Click "New Photo" to add one.</div></div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;">
            @foreach($photos as $ph)
                <div class="card" style="padding:12px;">
                    <img src="{{ $ph->image }}" alt="" style="width:100%;height:140px;object-fit:cover;border-radius:8px;background:#f3f1ea;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:9px;">
                        <span style="font-size:12px;color:var(--muted);">{{ $ph->location->client->name }}</span>
                        <span class="badge {{ $badgeFor($ph->status) }}">{{ $ph->status }}</span>
                    </div>
                    @if($ph->caption)<p style="font-size:13px;color:var(--ink);margin-top:6px;">{{ $ph->caption }}</p>@endif
                    @if(in_array($ph->status, ['DRAFT','SCHEDULED','FAILED']))
                        <form method="POST" action="{{ route('gbp-content.photos.publish', $ph) }}" style="margin-top:9px;">
                            @csrf
                            <button type="submit" class="btn btn-ghost" style="width:100%;padding:6px 12px;font-size:12px;">{{ $ph->status === 'FAILED' ? 'Retry publish' : 'Publish now' }}</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Post modal --}}
<div class="modal-bg" id="post-modal">
    <div class="modal" style="max-width:580px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="margin:0;">Add Post</h2>
            <button type="button" onclick="document.getElementById('post-modal').classList.remove('open')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);">&times;</button>
        </div>

        {{-- Type tabs --}}
        <div style="display:flex;gap:0;margin-bottom:18px;border:1px solid var(--line);border-radius:10px;overflow:hidden;">
            <button type="button" class="post-type-tab active" data-type="UPDATE" onclick="setPostType('UPDATE',this)" style="flex:1;padding:10px;font-size:13px;font-weight:600;background:var(--ink);color:#fff;border:none;cursor:pointer;">Update</button>
            <button type="button" class="post-type-tab" data-type="OFFER" onclick="setPostType('OFFER',this)" style="flex:1;padding:10px;font-size:13px;font-weight:600;background:var(--card);color:var(--muted);border:none;border-left:1px solid var(--line);cursor:pointer;">Offer</button>
            <button type="button" class="post-type-tab" data-type="EVENT" onclick="setPostType('EVENT',this)" style="flex:1;padding:10px;font-size:13px;font-weight:600;background:var(--card);color:var(--muted);border:none;border-left:1px solid var(--line);cursor:pointer;">Event</button>
        </div>

        <form method="POST" action="{{ route('gbp-content.posts.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" id="post-type-input" value="UPDATE">

            <label><span class="lbl">Location</span>
                <select name="gbp_location_id" id="post-location-select">
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" data-business="{{ $loc->client->name }}">{{ $loc->title ?? $loc->google_name }} &mdash; {{ $loc->client->name }}</option>
                    @endforeach
                </select>
            </label>

            {{-- Image upload area --}}
            <div style="margin-top:12px;">
                <span class="lbl">Image (optional)</span>
                <div id="img-drop" style="border:2px dashed var(--line);border-radius:12px;padding:28px;text-align:center;cursor:pointer;transition:border-color .15s;" onclick="document.getElementById('post-image-input').click()">
                    <div style="font-size:24px;color:var(--muted);">&#128247;</div>
                    <div style="font-size:13px;color:var(--muted);margin-top:6px;">Click to upload or drag & drop</div>
                    <div style="font-size:11px;color:var(--muted);">PNG, JPG, GIF up to 5MB</div>
                </div>
                <input type="file" id="post-image-input" name="image" accept="image/*" style="display:none;" onchange="previewImg(this)">
                <div id="img-preview" style="display:none;margin-top:8px;position:relative;">
                    <img id="img-preview-src" src="" style="width:100%;max-height:180px;object-fit:cover;border-radius:10px;">
                    <button type="button" onclick="clearImg()" style="position:absolute;top:6px;right:6px;width:24px;height:24px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;border:none;cursor:pointer;font-size:14px;">&times;</button>
                </div>
            </div>

            {{-- Body with counter --}}
            <label style="margin-top:12px;">
                <div style="display:flex;justify-content:space-between;"><span class="lbl">Description</span><span id="char-count" style="font-size:11px;color:var(--muted);">0 / 1500</span></div>
                <textarea name="body" id="post-body" rows="4" placeholder="Describe the update, offer, or event..." oninput="document.getElementById('char-count').textContent=this.value.length+' / 1500'" maxlength="1500"></textarea>
            </label>
            <button type="button" onclick="genPostCopy(this)" style="background:none;border:none;color:var(--teal);font-size:12.5px;font-weight:600;cursor:pointer;margin-top:6px;">&#10024; Generate with AI</button>

            <label style="margin-top:10px;"><span class="lbl">Call to Action URL (optional)</span><input type="url" name="cta_url" placeholder="https://..."></label>

            {{-- Offer fields --}}
            <div id="offer-fields" style="display:none;">
                <label style="margin-top:10px;"><span class="lbl">Coupon Code</span><input type="text" name="coupon_code" placeholder="e.g. SAVE20"></label>
                <label style="margin-top:10px;"><span class="lbl">Terms & Conditions</span><textarea name="terms" rows="2" placeholder="Offer terms..."></textarea></label>
            </div>

            {{-- Event/Offer date fields --}}
            <div id="date-fields" style="display:none;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px;">
                    <label><span class="lbl">Start Date & Time</span><input type="datetime-local" name="start_date"></label>
                    <label><span class="lbl">End Date & Time</span><input type="datetime-local" name="end_date"></label>
                </div>
            </div>

            <label style="margin-top:10px;"><span class="lbl">Schedule (leave blank to publish now)</span><input type="datetime-local" name="scheduled_at"></label>

            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('post-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Save Post</button>
            </div>
        </form>
    </div>
</div>

{{-- New Photo modal --}}
<div class="modal-bg" id="photo-modal">
    <div class="modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="margin:0;">New Photo</h2>
            <button type="button" onclick="document.getElementById('photo-modal').classList.remove('open')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);">&times;</button>
        </div>
        <form method="POST" action="{{ route('gbp-content.photos.store') }}">
            @csrf
            <label><span class="lbl">Location</span>
                <select name="gbp_location_id">
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->title ?? $loc->google_name }} &mdash; {{ $loc->client->name }}</option>
                    @endforeach
                </select>
            </label>
            <label><span class="lbl">Image URL</span><input type="url" name="image" id="photo-url" placeholder="https://... (must be a public image URL)"></label>
            <label><span class="lbl">Caption</span><textarea name="caption" id="photo-caption" rows="3" placeholder="Short caption..."></textarea></label>
            <button type="button" onclick="genCaption(this)" style="background:none;border:none;color:var(--teal);font-size:12.5px;font-weight:600;cursor:pointer;margin-top:6px;">&#10024; Generate caption with AI</button>
            <label style="margin-top:10px;"><span class="lbl">Schedule (leave blank to publish now)</span><input type="datetime-local" name="scheduled_at"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('photo-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Save Photo</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function switchTab(tab, btn){
    document.querySelectorAll('.tab-btn').forEach(b=>{b.classList.remove('active');b.style.background='var(--card)';b.style.color='var(--muted)';});
    btn.classList.add('active'); btn.style.background='var(--ink)'; btn.style.color='#fff';
    document.getElementById('tab-posts').style.display = tab==='posts' ? '' : 'none';
    document.getElementById('tab-photos').style.display = tab==='photos' ? '' : 'none';
}

function openPostModal(){
    setPostType('UPDATE', document.querySelector('.post-type-tab[data-type="UPDATE"]'));
    document.getElementById('post-modal').classList.add('open');
}

function setPostType(type, btn){
    document.querySelectorAll('.post-type-tab').forEach(b=>{b.style.background='var(--card)';b.style.color='var(--muted)';b.classList.remove('active');});
    btn.style.background='var(--ink)';btn.style.color='#fff';btn.classList.add('active');
    document.getElementById('post-type-input').value = type;
    document.getElementById('offer-fields').style.display = type==='OFFER' ? '' : 'none';
    document.getElementById('date-fields').style.display = (type==='OFFER'||type==='EVENT') ? '' : 'none';
}

function previewImg(input){
    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('img-preview-src').src = e.target.result;
            document.getElementById('img-preview').style.display = '';
            document.getElementById('img-drop').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function clearImg(){
    document.getElementById('post-image-input').value = '';
    document.getElementById('img-preview').style.display = 'none';
    document.getElementById('img-drop').style.display = '';
}

async function genPostCopy(btn){
    const body = document.getElementById('post-body');
    const type = document.getElementById('post-type-input').value;
    const opt = document.getElementById('post-location-select').selectedOptions[0];
    const business = opt ? opt.dataset.business : 'the business';
    btn.textContent='Generating...';btn.disabled=true;
    try{
        const res = await fetch('{{ route('gbp-content.posts.generate') }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},body:JSON.stringify({type,business})});
        const data = await res.json();
        if(data.error){alert(data.error);return;}
        body.value = data.body;
        document.getElementById('char-count').textContent = body.value.length + ' / 1500';
    }catch(e){alert('Could not generate.');}
    finally{btn.textContent='✨ Generate with AI';btn.disabled=false;}
}

async function genCaption(btn){
    const caption = document.getElementById('photo-caption');
    const url = document.getElementById('photo-url').value || 'a business photo';
    btn.textContent='Generating...';btn.disabled=true;
    try{
        const res = await fetch('{{ route('gbp-content.photos.caption') }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},body:JSON.stringify({prompt:url})});
        const data = await res.json();
        if(data.error){alert(data.error);return;}
        caption.value = data.caption;
    }catch(e){alert('Could not generate.');}
    finally{btn.textContent='✨ Generate caption with AI';btn.disabled=false;}
}

// Drag and drop
const drop = document.getElementById('img-drop');
if(drop){
    ['dragenter','dragover'].forEach(e=>drop.addEventListener(e,ev=>{ev.preventDefault();drop.style.borderColor='var(--teal)';}));
    ['dragleave','drop'].forEach(e=>drop.addEventListener(e,ev=>{ev.preventDefault();drop.style.borderColor='var(--line)';}));
    drop.addEventListener('drop', ev => {
        const files = ev.dataTransfer.files;
        if(files.length){
            document.getElementById('post-image-input').files = files;
            previewImg(document.getElementById('post-image-input'));
        }
    });
}
</script>
@endpush
@endsection
