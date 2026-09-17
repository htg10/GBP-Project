@extends('layouts.app')
@section('title', $location->title ?: 'Reviews')
@section('content')
<div style="margin-bottom:6px;"><a href="{{ route('reviews') }}" style="font-size:13px;color:var(--muted);">← All reviews</a></div>
<div class="page-head">
    <div><h1>{{ $location->title ?: $location->google_name }}</h1><p>{{ $location->client->name }}@if($location->address) · {{ $location->address }}@endif</p></div>
    <form method="POST" action="{{ route('reviews.sync', $location) }}">@csrf<button class="btn btn-ghost" type="submit" id="sync-btn">⟳ Sync from Google</button></form>
</div>

@if(!$isLive)
    <div style="background:var(--amber-soft);border:1px solid #f0d9a8;border-radius:11px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:#8a5a08;">
        ⚡ Showing <strong>demo reviews</strong>. To pull real Google reviews, open this location's client and click <strong>“Connect Google”</strong>.
    </div>
@else
    <div style="background:var(--teal-soft);border:1px solid #cfe6e0;border-radius:11px;padding:11px 15px;margin-bottom:16px;font-size:13px;color:var(--teal-ink);">
        ✓ Connected to Google — sync pulls real reviews for this location.
    </div>
@endif

<div class="stats">
    <div class="stat accent"><div>★</div><div class="v">{{ number_format($stats['avg'],1) }}</div><div class="l">Avg rating</div></div>
    <div class="stat"><div>✉</div><div class="v">{{ $stats['total'] }}</div><div class="l">Total</div></div>
    <div class="stat"><div>↗</div><div class="v" style="color:var(--amber)">{{ $stats['unreplied'] }}</div><div class="l">Awaiting reply</div></div>
    <div class="stat"><div>▽</div><div class="v">{{ $stats['negative'] }}</div><div class="l">Negative</div></div>
</div>

<div style="display:flex;gap:8px;margin-bottom:16px;">
    @foreach(['all'=>'All','unreplied'=>'Needs reply','negative'=>'Negative'] as $key=>$lbl)
        <a href="{{ route('reviews.show', [$location, 'filter'=>$key]) }}"
           style="border:1px solid var(--line);border-radius:999px;padding:7px 14px;font-size:13px;font-weight:500;{{ $filter===$key ? 'background:var(--ink);color:#fff;' : 'background:var(--card);color:var(--muted);' }}">{{ $lbl }}</a>
    @endforeach
</div>

@if($reviews->isEmpty())
    <div class="card"><div class="empty">No reviews in this view yet. Click “Sync from Google”.</div></div>
@else
    <div style="display:flex;flex-direction:column;gap:13px;">
        @foreach($reviews as $review)
            @php $sent = $review->sentiment; $badge = $sent==='POSITIVE'?'teal':($sent==='NEGATIVE'?'rose':'gray'); @endphp
            <div class="card">
                <div style="display:flex;gap:11px;margin-bottom:11px;align-items:flex-start;">
                    <div class="avatar">{{ strtoupper(substr($review->reviewer_name,0,1)) }}</div>
                    <div style="flex:1;">
                        <div style="display:flex;justify-content:space-between;">
                            <strong>{{ $review->reviewer_name }}</strong>
                            @if($review->reply_text)<span class="badge teal">✓ Replied</span>@endif
                        </div>
                        <div style="margin-top:5px;display:flex;align-items:center;gap:9px;">
                            <span style="color:var(--amber);letter-spacing:1px;">{{ str_repeat('★', $review->star_rating) }}<span style="color:#d9d6cc;">{{ str_repeat('★', 5-$review->star_rating) }}</span></span>
                            @if($sent)<span class="badge {{ $badge }}">{{ ucfirst(strtolower($sent)) }}</span>@endif
                        </div>
                    </div>
                </div>
                <p style="font-size:14px;line-height:1.55;color:#2a3a35;margin-bottom:14px;">{{ $review->comment }}</p>

                <div style="border-top:1px dashed var(--line);padding-top:13px;">
                    @if($review->reply_text)
                        <div style="font-size:11px;font-weight:600;color:var(--teal);text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;">Your reply</div>
                        <p style="font-size:13.5px;line-height:1.5;color:#3a4a45;">{{ $review->reply_text }}</p>
                    @else
                        <form method="POST" action="{{ route('reviews.reply', $review) }}" id="reply-form-{{ $review->id }}">
                            @csrf
                            <textarea name="reply_text" id="reply-{{ $review->id }}" rows="3" placeholder="Write a reply…" style="margin-bottom:9px;"></textarea>
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <button type="button" class="btn btn-ghost" style="padding:8px 13px;font-size:12.5px;" onclick="genReply({{ $review->id }}, this)">✦ Draft AI reply</button>
                                <button type="submit" class="btn" style="padding:8px 14px;font-size:12.5px;">Post reply</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
async function genReply(id, btn){
    const original = btn.textContent;
    btn.textContent = 'Drafting…'; btn.disabled = true;
    try{
        const res = await fetch(`{{ url('reviews') }}/${id}/generate`, {
            method:'POST',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},
        });
        const data = await res.json();
        document.getElementById('reply-'+id).value = data.reply;
    }catch(e){ alert('Could not generate reply.'); }
    finally{ btn.textContent = original; btn.disabled = false; }
}
// Sync can take a few seconds (one real network round-trip to Google) —
// give clear feedback instead of leaving the button looking dead.
document.getElementById('sync-btn')?.closest('form')?.addEventListener('submit', function(){
    const b = document.getElementById('sync-btn');
    b.textContent = '⟳ Syncing…'; b.disabled = true;
});
</script>
@endpush
@endsection
