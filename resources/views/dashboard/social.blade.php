@extends('layouts.app')
@section('title', 'Social')
@section('content')
<div class="page-head">
    <div><h1>Social Media</h1><p>Draft and publish posts across your channels, with AI help.</p></div>
    <button class="btn" onclick="document.getElementById('post-modal').classList.add('open')">+ New post</button>
</div>

@if(($liveCount ?? 0) === 0)
    <div style="background:var(--amber-soft);border:1px solid #f0d9a8;border-radius:11px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#8a5a08;">
        ⚡ Facebook/Instagram posts save as <strong>drafts only</strong> right now. To publish for real, open a client and click <strong>“Connect Meta”</strong>.
    </div>
@else
    <div style="background:var(--teal-soft);border:1px solid #cfe6e0;border-radius:11px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:var(--teal-ink);">
        ✓ <strong>{{ $liveCount }}</strong> client(s) connected to Meta — Facebook/Instagram posts publish for real.
    </div>
@endif

@if($clients->isEmpty())<div class="alert info">Add a client first — posts attach to a client.</div>@endif

@php $badgeFor = fn($s) => $s==='PUBLISHED'?'teal':($s==='SCHEDULED'?'amber':($s==='FAILED'?'rose':'gray')); @endphp

@if($posts->isEmpty())
    <div class="card"><div class="empty">No posts yet. Click “New post” to draft your first one.</div></div>
@else
    <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach($posts as $p)
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <span style="font-weight:600;font-size:13.5px;color:var(--teal-ink);">{{ ucfirst(strtolower($p->platform)) }}</span>
                    <span class="badge {{ $badgeFor($p->status) }}">{{ $p->status }}</span>
                </div>
                @if(!empty($p->media_urls[0]))
                    <img src="{{ $p->media_urls[0] }}" alt="" style="width:100%;max-height:220px;object-fit:cover;border-radius:8px;background:#f3f1ea;margin-bottom:10px;">
                @endif
                <p style="font-size:14px;line-height:1.55;color:#2a3a35;">{{ $p->body }}</p>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                    @if($p->scheduled_at)<div style="font-size:12px;color:var(--muted);">🗓 {{ $p->scheduled_at->format('d M Y, h:i A') }}</div>@else<span></span>@endif
                    @if($p->status === 'FAILED' && in_array($p->platform, ['FACEBOOK','INSTAGRAM']))
                        <form method="POST" action="{{ route('social.retry', $p) }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost" style="padding:6px 12px;font-size:12px;">Retry publish</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="modal-bg" id="post-modal">
    <div class="modal">
        <h2>New post</h2>
        <form method="POST" action="{{ route('social.store') }}">
            @csrf
            <label><span class="lbl">Client</span><select name="client_id">@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
            <label><span class="lbl">Platform</span><select name="platform">
                <option value="FACEBOOK">Facebook</option><option value="INSTAGRAM">Instagram (image required)</option><option value="LINKEDIN">LinkedIn</option><option value="X">X (Twitter)</option>
            </select></label>
            <label><span class="lbl">Caption</span><textarea name="body" id="post-body" rows="4" placeholder="What's on your mind?"></textarea></label>
            <button type="button" onclick="genCaption(this)" style="background:none;border:none;color:var(--teal);font-size:12.5px;font-weight:600;cursor:pointer;margin-top:8px;">✦ Generate with AI</button>
            <label><span class="lbl">Image URL (optional — required for Instagram)</span><input type="url" name="media_url" placeholder="https://…"></label>
            <label><span class="lbl">Schedule (optional — leave blank to publish now)</span><input type="datetime-local" name="scheduled_at"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('post-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Save post</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
async function genCaption(btn){
    const body = document.getElementById('post-body');
    btn.textContent='Generating…';btn.disabled=true;
    try{
        const res = await fetch('{{ route('social.caption') }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},body:JSON.stringify({prompt:body.value||'a social media post'})});
        const data = await res.json(); body.value = data.body;
    }catch(e){alert('Could not generate.');}
    finally{btn.textContent='✦ Generate with AI';btn.disabled=false;}
}
</script>
@endpush
@endsection
