@extends('layouts.app')
@section('title', 'Google Reviews')
@section('content')

<div class="page-head">
    <div><h1>Google Reviews</h1><p>Manage, reply to, and analyze reviews across all your business locations.</p></div>
</div>

@php
    $totalR = $locations->sum('total_reviews');
    $repliedR = $locations->sum(fn($l) => $l->total_reviews - $l->unreplied_count);
    $pendingR = $locations->sum('unreplied_count');
    $avgAll = $totalR ? $locations->avg('reviews_avg_star_rating') : 0;
@endphp

{{-- Toggle controls --}}
<div style="display:flex;gap:14px;margin-bottom:16px;flex-wrap:wrap;">
    <div class="card" style="display:flex;align-items:center;gap:12px;padding:12px 18px;flex:0 0 auto;">
        <div>
            <div style="font-size:13px;font-weight:600;">WhatsApp Notifications</div>
            <div style="font-size:11px;color:var(--muted);">Get notified for new reviews</div>
        </div>
        <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;">
            <input type="checkbox" id="wa-toggle" style="opacity:0;width:0;height:0;">
            <span style="position:absolute;cursor:pointer;inset:0;background:var(--line);border-radius:24px;transition:.2s;" onclick="this.previousElementSibling.checked=!this.previousElementSibling.checked;this.style.background=this.previousElementSibling.checked?'var(--teal)':'var(--line)';this.querySelector('i').style.transform=this.previousElementSibling.checked?'translateX(20px)':'translateX(0)';">
                <i style="position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;left:3px;top:3px;transition:.2s;display:block;box-shadow:0 1px 3px rgba(0,0,0,.12);"></i>
            </span>
        </label>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:12px;padding:12px 18px;flex:0 0 auto;">
        <div>
            <div style="font-size:13px;font-weight:600;">Universal Auto Reply</div>
            <div style="font-size:11px;color:var(--muted);">AI replies to new reviews automatically</div>
        </div>
        <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;">
            <input type="checkbox" id="ar-toggle" style="opacity:0;width:0;height:0;">
            <span style="position:absolute;cursor:pointer;inset:0;background:var(--line);border-radius:24px;transition:.2s;" onclick="this.previousElementSibling.checked=!this.previousElementSibling.checked;this.style.background=this.previousElementSibling.checked?'var(--teal)':'var(--line)';this.querySelector('i').style.transform=this.previousElementSibling.checked?'translateX(20px)':'translateX(0)';">
                <i style="position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;left:3px;top:3px;transition:.2s;display:block;box-shadow:0 1px 3px rgba(0,0,0,.12);"></i>
            </span>
        </label>
    </div>
</div>

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px;">
    <div class="card" style="text-align:center;padding:16px;">
        <div style="font-size:28px;font-weight:800;">{{ number_format($totalR) }}</div>
        <div style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.03em;">Total Reviews</div>
    </div>
    <div class="card" style="text-align:center;padding:16px;">
        <div style="font-size:28px;font-weight:800;color:var(--teal);">{{ $repliedR }}</div>
        <div style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.03em;">Replied</div>
    </div>
    <div class="card" style="text-align:center;padding:16px;">
        <div style="font-size:28px;font-weight:800;color:var(--amber);">{{ $pendingR }}</div>
        <div style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.03em;">Pending</div>
    </div>
    <div class="card" style="text-align:center;padding:16px;">
        <div style="font-size:28px;font-weight:800;">{{ number_format($avgAll, 1) }} <span style="font-size:16px;color:#f59e0b;">&#9733;</span></div>
        <div style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.03em;">Avg Rating</div>
    </div>
</div>

@if($locations->isEmpty())
    <div class="card"><div class="empty">No locations yet. <a href="{{ route('clients') }}" style="color:var(--teal);">Add a client</a> with a Google location first.</div></div>
@else
    {{-- Filters --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
        <input type="search" id="review-search" placeholder="Search business or client..."
               style="flex:1;min-width:220px;border:1px solid var(--line);border-radius:9px;padding:9px 13px;font-size:13.5px;background:var(--card);">
        <select id="star-filter" onchange="applyFilter()" style="border:1px solid var(--line);border-radius:9px;padding:9px 13px;font-size:13px;background:var(--card);min-width:120px;">
            <option value="all">All Stars</option>
            <option value="5">5 Stars</option>
            <option value="4">4 Stars</option>
            <option value="3">3 Stars</option>
            <option value="1">1-2 Stars</option>
        </select>
        <div style="display:flex;gap:6px;">
            <button type="button" class="filter-chip active" data-scope="all" onclick="setScope('all', this)">All</button>
            <button type="button" class="filter-chip" data-scope="live" onclick="setScope('live', this)">Live</button>
            <button type="button" class="filter-chip" data-scope="unreplied" onclick="setScope('unreplied', this)">Needs Reply</button>
        </div>
    </div>

    {{-- Location cards --}}
    <div id="review-card-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;">
        @foreach($locations as $loc)
            @php $live = in_array($loc->client_id, $connectedClientIds); @endphp
            <div class="card review-card"
                 data-search="{{ strtolower(($loc->title ?: $loc->google_name).' '.$loc->client->name) }}"
                 data-live="{{ $live ? '1' : '0' }}"
                 data-unreplied="{{ $loc->unreplied_count }}"
                 data-avgstar="{{ $loc->reviews_avg_star_rating ? round($loc->reviews_avg_star_rating) : 0 }}">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div style="display:flex;gap:11px;align-items:center;min-width:0;">
                        <div class="avatar" style="background:var(--teal-soft);color:var(--teal-ink);flex-shrink:0;">{{ strtoupper(substr($loc->title ?: $loc->client->name, 0, 1)) }}</div>
                        <div style="min-width:0;">
                            <strong style="font-size:14px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $loc->title ?: $loc->google_name }}</strong>
                            <div style="font-size:12px;color:var(--muted);">{{ $loc->client->name }}</div>
                        </div>
                    </div>
                    <span class="badge {{ $live ? 'teal' : 'gray' }}">{{ $live ? 'Live' : 'Demo' }}</span>
                </div>

                {{-- Stars display --}}
                <div style="font-size:14px;color:#f59e0b;margin-bottom:10px;">
                    @for($i = 1; $i <= 5; $i++){{ $i <= round($loc->reviews_avg_star_rating ?? 0) ? '★' : '☆' }}@endfor
                    <span style="color:var(--ink);font-weight:700;margin-left:4px;">{{ $loc->reviews_avg_star_rating ? number_format($loc->reviews_avg_star_rating, 1) : '—' }}</span>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:10px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);margin-bottom:12px;">
                    <div style="text-align:center;"><div style="font-size:18px;font-weight:700;">{{ $loc->total_reviews }}</div><div style="font-size:11px;color:var(--muted);">Reviews</div></div>
                    <div style="text-align:center;"><div style="font-size:18px;font-weight:700;color:var(--amber);">{{ $loc->unreplied_count }}</div><div style="font-size:11px;color:var(--muted);">Pending</div></div>
                    @if($loc->negative_count)
                    <div style="text-align:center;"><div style="font-size:18px;font-weight:700;color:var(--rose);">{{ $loc->negative_count }}</div><div style="font-size:11px;color:var(--muted);">Negative</div></div>
                    @else
                    <div style="text-align:center;"><div style="font-size:18px;font-weight:700;color:var(--teal);">{{ $loc->total_reviews - $loc->unreplied_count }}</div><div style="font-size:11px;color:var(--muted);">Replied</div></div>
                    @endif
                </div>

                {{-- Sentiment badge --}}
                @php
                    $sentiment = ($loc->reviews_avg_star_rating ?? 0) >= 4 ? 'POSITIVE' : (($loc->reviews_avg_star_rating ?? 0) >= 3 ? 'NEUTRAL' : 'NEGATIVE');
                    $sentColor = $sentiment === 'POSITIVE' ? 'teal' : ($sentiment === 'NEUTRAL' ? 'amber' : 'rose');
                @endphp
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="badge {{ $sentColor }}">{{ $sentiment }}</span>
                    <a href="{{ route('reviews.show', $loc) }}" class="btn btn-ghost" style="padding:7px 14px;font-size:12.5px;">Manage &rarr;</a>
                </div>
            </div>
        @endforeach
    </div>
    <div id="review-empty" class="card" style="display:none;"><div class="empty">No businesses match your filters.</div></div>
@endif

@push('head')
<style>
    .filter-chip{border:1px solid var(--line);border-radius:999px;padding:8px 14px;font-size:13px;font-weight:500;background:var(--card);color:var(--muted);cursor:pointer;transition:all .15s;}
    .filter-chip.active{background:var(--ink);color:#fff;border-color:var(--ink);}
    @media (max-width:700px){
        [style*="grid-template-columns:repeat(4,1fr)"]{grid-template-columns:repeat(2,1fr) !important;}
    }
</style>
@endpush

@push('scripts')
<script>
let scope = 'all';
const searchInput = document.getElementById('review-search');
const cards = Array.from(document.querySelectorAll('.review-card'));
const emptyState = document.getElementById('review-empty');

function setScope(s, btn){
    scope = s;
    document.querySelectorAll('.filter-chip').forEach(b=>{b.classList.remove('active');});
    btn.classList.add('active');
    applyFilter();
}

function applyFilter(){
    const q = searchInput?.value.trim().toLowerCase() || '';
    const star = document.getElementById('star-filter')?.value || 'all';
    let visible = 0;
    cards.forEach(c => {
        const matchesSearch = !q || c.dataset.search.includes(q);
        const matchesScope = scope === 'all'
            || (scope === 'live' && c.dataset.live === '1')
            || (scope === 'unreplied' && parseInt(c.dataset.unreplied, 10) > 0);
        const avg = parseInt(c.dataset.avgstar, 10);
        const matchesStar = star === 'all'
            || (star === '5' && avg === 5)
            || (star === '4' && avg === 4)
            || (star === '3' && avg === 3)
            || (star === '1' && avg <= 2);
        const show = matchesSearch && matchesScope && matchesStar;
        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    if(emptyState) emptyState.style.display = visible === 0 ? '' : 'none';
}

searchInput?.addEventListener('input', applyFilter);
</script>
@endpush
@endsection
