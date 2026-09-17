@extends('layouts.app')
@section('title', $client->name)
@section('content')
<div style="margin-bottom:6px;"><a href="{{ route('clients') }}" style="font-size:13px;color:var(--muted);">← All clients</a></div>
<div class="page-head">
    <div style="display:flex;gap:14px;align-items:center;">
        <div class="avatar" style="width:52px;height:52px;font-size:20px;background:var(--teal-soft);color:var(--teal-ink);">{{ strtoupper(substr($client->name,0,1)) }}</div>
        <div><h1>{{ $client->name }}</h1><p>{{ $client->industry ?: 'No industry set' }}</p></div>
    </div>
    <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Delete this client and all its data?')">
        @csrf @method('DELETE')
        <button class="btn btn-ghost" style="color:var(--rose);border-color:var(--rose-soft);">Delete client</button>
    </form>
</div>

<!-- ===== Individual account dashboard: stats ===== -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px;" class="acct-stats">
    <div class="card" style="padding:15px;">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Total Reviews</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px;">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="card" style="padding:15px;">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Avg Rating</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px;color:#f59e0b;">{{ number_format($stats['avg'],1) }} <span style="font-size:16px;">★</span></div>
    </div>
    <div class="card" style="padding:15px;">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Replied</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px;color:var(--teal);">{{ $stats['replied'] }}</div>
    </div>
    <div class="card" style="padding:15px;">
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Pending</div>
        <div style="font-size:28px;font-weight:800;margin-top:6px;color:var(--amber);">{{ $stats['pending'] }}</div>
    </div>
</div>

<!-- ===== Rating breakdown + recent reviews ===== -->
<div style="display:grid;grid-template-columns:1fr 1.2fr;gap:14px;margin-bottom:14px;" class="acct-split">
    <div class="card">
        <strong style="font-size:14.5px;">Rating Breakdown</strong>
        <div style="margin-top:14px;display:flex;flex-direction:column;gap:9px;">
            @foreach($starDist as $star => $count)
                @php $pct = $stats['total'] ? round($count / $stats['total'] * 100) : 0; @endphp
                <div style="display:flex;align-items:center;gap:9px;font-size:12.5px;">
                    <span style="width:34px;font-weight:600;">{{ $star }} <span style="color:#f59e0b;">★</span></span>
                    <div style="flex:1;height:8px;background:#eef1f8;border-radius:6px;overflow:hidden;"><div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#4c6fff,#6b8afd);border-radius:6px;"></div></div>
                    <span style="width:64px;text-align:right;font-weight:600;">{{ $count }} <span style="color:var(--muted);font-weight:500;">({{ $pct }}%)</span></span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid var(--line);">
            <strong style="font-size:14.5px;">Recent Reviews</strong>
            <a href="{{ route('reviews') }}" style="color:var(--teal);font-size:12.5px;font-weight:600;">All →</a>
        </div>
        @forelse($recentReviews as $rev)
            <div style="display:flex;gap:10px;padding:12px 18px;{{ !$loop->last ? 'border-bottom:1px solid var(--line);' : '' }}">
                <div class="avatar" style="width:32px;height:32px;font-size:11px;flex-shrink:0;background:{{ $rev->star_rating >= 4 ? 'var(--green-soft)' : ($rev->star_rating >= 3 ? 'var(--amber-soft)' : 'var(--rose-soft)') }};color:{{ $rev->star_rating >= 4 ? 'var(--green)' : ($rev->star_rating >= 3 ? '#8a5a08' : 'var(--rose)') }};">{{ strtoupper(substr($rev->reviewer_name ?: '?', 0, 1)) }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;">
                        <strong style="font-size:13px;">{{ $rev->reviewer_name ?: 'Anonymous' }}</strong>
                        <span style="font-size:12px;color:#f59e0b;">@for($i=1;$i<=5;$i++){{ $i <= $rev->star_rating ? '★' : '☆' }}@endfor</span>
                    </div>
                    @if($rev->comment)<div style="font-size:12.5px;color:var(--muted);margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($rev->comment, 80) }}</div>@endif
                </div>
            </div>
        @empty
            <div class="empty" style="padding:28px 20px;">No reviews synced yet for this account.</div>
        @endforelse
    </div>
</div>

<!-- Google connection -->
<div class="card" style="margin-bottom:14px;{{ $googleIntegration && $googleIntegration->access_token ? 'border:1.5px solid var(--teal);' : '' }}">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:12px;align-items:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;font-weight:700;color:#4285F4;">G</div>
            <div>
                <strong style="font-size:14.5px;">Google Business Profile</strong>
                @if($googleIntegration && $googleIntegration->access_token)
                    <div style="font-size:12.5px;color:var(--teal-ink);margin-top:2px;">✓ Connected — reviews sync from Google for real</div>
                @else
                    <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Not connected — using demo data until you connect</div>
                @endif
            </div>
        </div>
        @if($googleIntegration && $googleIntegration->access_token)
            <form method="POST" action="{{ route('google.disconnect', $client) }}" onsubmit="return confirm('Disconnect Google for this client?')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost" style="color:var(--rose);border-color:var(--rose-soft);">Disconnect</button>
            </form>
        @else
            <a href="{{ route('google.connect', $client) }}" class="btn">Connect Google</a>
        @endif
    </div>
    @if($googleIntegration && $googleIntegration->access_token)
        <div style="border-top:1px solid var(--line);margin-top:14px;padding-top:14px;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12.5px;color:var(--muted);">Pull this business's real locations from Google, then sync reviews.</div>
            <form method="POST" action="{{ route('clients.import-locations', $client) }}">
                @csrf
                <button class="btn" style="padding:8px 14px;font-size:12.5px;">⬇ Import real locations</button>
            </form>
        </div>
    @endif
</div>

<!-- Meta connection -->
<div class="card" style="margin-bottom:14px;{{ $metaIntegration && $metaIntegration->access_token ? 'border:1.5px solid var(--teal);' : '' }}">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:12px;align-items:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;font-weight:700;color:#0866FF;">M</div>
            <div>
                <strong style="font-size:14.5px;">Meta (Facebook / Instagram)</strong>
                @if($metaIntegration && $metaIntegration->access_token)
                    <div style="font-size:12.5px;color:var(--teal-ink);margin-top:2px;">
                        ✓ Connected — Page "{{ $metaIntegration->meta['page_name'] ?? '—' }}"{{ !empty($metaIntegration->meta['ig_user_id']) ? ' + Instagram' : '' }}. Social posts publish for real.
                    </div>
                @else
                    <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">Not connected — Facebook/Instagram posts save as drafts until you connect</div>
                @endif
            </div>
        </div>
        @if($metaIntegration && $metaIntegration->access_token)
            <form method="POST" action="{{ route('meta.disconnect', $client) }}" onsubmit="return confirm('Disconnect Meta for this client?')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost" style="color:var(--rose);border-color:var(--rose-soft);">Disconnect</button>
            </form>
        @else
            <a href="{{ route('meta.connect', $client) }}" class="btn">Connect Meta</a>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;align-items:start;">
    <!-- Locations -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <strong>Google locations</strong>
            <button class="btn" style="padding:7px 12px;font-size:12.5px;" onclick="document.getElementById('loc-modal').classList.add('open')">+ Add</button>
        </div>
        @forelse($client->locations as $loc)
            <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:11px 0;{{ !$loop->first ? 'border-top:1px solid var(--line);' : '' }}">
                <div>
                    <div style="font-weight:600;font-size:13.5px;">{{ $loc->title }}</div>
                    @if($loc->address)<div style="font-size:12px;color:var(--muted);margin-top:2px;">📍 {{ $loc->address }}</div>@endif
                    <div style="font-size:11px;color:#b8b4a8;margin-top:3px;font-family:monospace;">{{ \Illuminate\Support\Str::limit($loc->google_name, 32) }}</div>
                </div>
                <form method="POST" action="{{ route('clients.locations.destroy', [$client, $loc]) }}" onsubmit="return confirm('Remove this location?')">
                    @csrf @method('DELETE')
                    <button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);font-size:13px;">🗑</button>
                </form>
            </div>
        @empty
            <div style="font-size:13px;color:var(--muted);padding:8px 0;">No locations yet. Add one so Reviews can sync.</div>
        @endforelse
    </div>

    <!-- Details + leads -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="card">
            <strong>Contact</strong>
            <div style="font-size:13px;color:#3a4a45;line-height:1.9;margin-top:8px;">
                <div>✆ {{ $client->phone ?: '—' }}</div>
                <div>✉ {{ $client->email ?: '—' }}</div>
            </div>
        </div>
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <strong>Leads</strong>
                <span class="badge teal">{{ $client->leads->count() }}</span>
            </div>
            @forelse($client->leads->take(5) as $lead)
                <div style="display:flex;justify-content:space-between;padding:9px 0;{{ !$loop->first ? 'border-top:1px solid var(--line);' : 'margin-top:8px;border-top:1px solid var(--line);' }}">
                    <span style="font-size:13px;">{{ $lead->name }}</span>
                    <span class="badge gray">{{ ucfirst(strtolower(str_replace('_',' ',$lead->stage))) }}</span>
                </div>
            @empty
                <div style="font-size:13px;color:var(--muted);margin-top:8px;">No leads yet.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal-bg" id="loc-modal">
    <div class="modal">
        <h2>Add location</h2>
        <form method="POST" action="{{ route('clients.locations.store', $client) }}">
            @csrf
            <label><span class="lbl">Location title</span><input type="text" name="title" placeholder="e.g. Bright Smile — Ghaziabad" required></label>
            <label><span class="lbl">Address</span><input type="text" name="address" placeholder="City, State"></label>
            <label><span class="lbl">Google location ID (optional)</span><input type="text" name="google_name" placeholder="Leave blank — set on Google connect"></label>
            <div style="font-size:12px;color:var(--muted);margin-top:6px;">Once you connect Google (coming soon), this fills automatically and reviews sync for real.</div>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('loc-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add location</button>
            </div>
        </form>
    </div>
</div>

@push('head')
<style>
    @media (max-width:760px){
        .acct-stats{grid-template-columns:repeat(2,1fr) !important;}
        .acct-split{grid-template-columns:1fr !important;}
    }
</style>
@endpush
@endsection
