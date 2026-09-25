@extends('layouts.admin')
@section('title', 'Overview')
@section('content')
<div class="page-head"><h1>Admin Overview</h1><p>Platform-wide view of every agency, user, and their activity.</p></div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;" class="adm-kpis">
    <div class="card" style="padding:16px 18px;">
        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#4c6fff,#6b8afd);display:grid;place-items:center;color:#fff;font-size:18px;margin-bottom:12px;">🏛</div>
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Agencies</div>
        <div style="font-size:30px;font-weight:800;margin-top:5px;">{{ $stats['agencies'] }}</div>
    </div>
    <div class="card" style="padding:16px 18px;">
        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#22c55e,#4ade80);display:grid;place-items:center;color:#fff;font-size:18px;margin-bottom:12px;">◉</div>
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Total Users</div>
        <div style="font-size:30px;font-weight:800;margin-top:5px;">{{ $stats['users'] }}</div>
    </div>
    <div class="card" style="padding:16px 18px;">
        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:grid;place-items:center;color:#fff;font-size:18px;margin-bottom:12px;">🏢</div>
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Clients</div>
        <div style="font-size:30px;font-weight:800;margin-top:5px;">{{ $stats['clients'] }}</div>
    </div>
    <div class="card" style="padding:16px 18px;">
        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#a78bfa);display:grid;place-items:center;color:#fff;font-size:18px;margin-bottom:12px;">★</div>
        <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;color:var(--muted);">Reviews</div>
        <div style="font-size:30px;font-weight:800;margin-top:5px;">{{ $stats['reviews'] }}</div>
    </div>
</div>

{{-- All users across the platform --}}
<div class="card" style="padding:0;overflow:hidden;margin-bottom:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid var(--line);">
        <div>
            <strong style="font-size:15px;">All Users</strong>
            <div style="font-size:12px;color:var(--muted);">Every user across every agency</div>
        </div>
        <a href="{{ route('admin.users') }}" class="btn" style="padding:7px 14px;font-size:12.5px;">Manage Users →</a>
    </div>
    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr><th>User</th><th>Role</th><th>Agency</th><th>Reviews</th><th>Verified</th><th>Joined</th></tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        <div>
                            <div style="font-weight:600;">{{ $u->name }}</div>
                            <div style="font-size:12px;color:var(--muted);">{{ $u->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @php $roleClr = $u->role === 'SUPER_ADMIN' ? 'dark' : 'teal'; @endphp
                    <span class="badge {{ $roleClr }}">{{ ucwords(strtolower(str_replace('_',' ',$u->role))) }}</span>
                </td>
                <td style="font-size:13px;">{{ $u->agency->name ?? '—' }}</td>
                <td style="font-weight:700;">{{ $reviewsByAgency[$u->agency_id] ?? 0 }}</td>
                <td>
                    @if($u->email_verified_at)
                        <span style="color:var(--green);font-weight:600;font-size:12.5px;">✓ Yes</span>
                    @else
                        <span style="color:var(--muted);font-size:12.5px;">Pending</span>
                    @endif
                </td>
                <td style="font-size:12.5px;color:var(--muted);">{{ $u->created_at?->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;" class="adm-split">
    <div class="card">
        <strong style="font-size:15px;">Your Subscription</strong>
        <div style="display:flex;gap:36px;margin-top:16px;">
            <div>
                <div style="font-size:11.5px;color:var(--muted);text-transform:uppercase;">Plan</div>
                <div style="font-size:20px;font-weight:800;margin-top:4px;color:var(--teal);">{{ $sub->plan ?? 'None' }}</div>
            </div>
            <div>
                <div style="font-size:11.5px;color:var(--muted);text-transform:uppercase;">Status</div>
                <div style="font-size:20px;font-weight:800;margin-top:4px;">{{ $sub->status ?? 'None' }}</div>
            </div>
            <div>
                <div style="font-size:11.5px;color:var(--muted);text-transform:uppercase;">Credits</div>
                <div style="font-size:20px;font-weight:800;margin-top:4px;color:var(--purple);">{{ number_format($sub->credit_balance ?? 0) }}</div>
            </div>
        </div>
    </div>
    <div class="card">
        <strong style="font-size:15px;">Quick Manage</strong>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:14px;">
            <a href="{{ route('admin.users') }}" class="btn btn-ghost" style="justify-content:space-between;">Manage Users <span>→</span></a>
            <a href="{{ route('admin.plans') }}" class="btn btn-ghost" style="justify-content:space-between;">Manage Plans <span>→</span></a>
            <a href="{{ route('dashboard') }}" class="btn" style="justify-content:space-between;">Open User Dashboard <span>→</span></a>
        </div>
    </div>
</div>

@push('head')
<style>
    .btn-ghost{width:100%;}
    @media (max-width:760px){ .adm-kpis{grid-template-columns:repeat(2,1fr) !important;} .adm-split{grid-template-columns:1fr !important;} }
</style>
@endpush
@endsection
