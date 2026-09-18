<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ReviewFlow')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --ink:#1b2437; --paper:#f4f6fb; --card:#ffffff;
            --teal:#4c6fff; --teal-soft:#eaefff; --teal-ink:#3452d1;
            --amber:#f59e0b; --amber-soft:#fef3d6;
            --rose:#ef4757; --rose-soft:#fde7e9;
            --line:#eaecf3; --muted:#7a8599;
            --green:#22c55e; --green-soft:#dcfce7;
            --blue:#4c6fff; --purple:#8b5cf6; --sky:#38bdf8; --pink:#ec4899;
            --shadow:0 1px 2px rgba(20,30,60,.04), 0 8px 28px rgba(20,30,60,.06);
            --radius:16px;
        }
        [data-theme="dark"]{
            --ink:#e6e9f0; --paper:#0e1420; --card:#161d2b;
            --teal:#6b8afd; --teal-soft:#1d2740; --teal-ink:#a9bcff;
            --amber:#f59e0b; --amber-soft:#2d2008;
            --rose:#f87171; --rose-soft:#2d1215;
            --line:#263041; --muted:#8b97a8;
            --green:#4ade80; --green-soft:#0f2a1a;
            --blue:#6b8afd; --purple:#a78bfa; --sky:#38bdf8; --pink:#f472b6;
            --shadow:0 1px 2px rgba(0,0,0,.3), 0 8px 28px rgba(0,0,0,.35);
        }
        [data-theme="dark"] .sidebar{background:#141a22;border-color:#2a3340;}
        [data-theme="dark"] .header-bar{background:#141a22;border-color:#2a3340;}
        [data-theme="dark"] .topbar{background:#141a22;border-color:#2a3340;}
        [data-theme="dark"] .nav-item:hover{background:#1e2630;}
        [data-theme="dark"] input,[data-theme="dark"] select,[data-theme="dark"] textarea{background:#1a2028;border-color:#2a3340;color:var(--ink);}
        [data-theme="dark"] .hdr-btn{background:#1a2028;border-color:#2a3340;color:#8b97a8;}
        [data-theme="dark"] .hdr-btn:hover{background:#242c36;border-color:#3a4550;}
        [data-theme="dark"] .hdr-user:hover{background:#1e2630;}
        [data-theme="dark"] .brand-logo{box-shadow:0 4px 12px rgba(20,184,160,.2);}
        [data-theme="dark"] .admin-btn{background:#e8e6dd;color:#0f1419;}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,sans-serif;background:var(--paper);color:var(--ink);-webkit-font-smoothing:antialiased;}
        a{text-decoration:none;color:inherit;}

        /* === Top Header Bar === */
        .header-bar{height:52px;background:var(--card);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:flex-end;padding:0 24px;gap:12px;position:sticky;top:0;z-index:50;}
        .hdr-google{display:inline-flex;align-items:center;gap:7px;padding:7px 16px;border-radius:9px;background:#0f6b5c;color:#fff;font-size:12.5px;font-weight:600;cursor:default;white-space:nowrap;}
        .hdr-google svg{width:16px;height:16px;flex-shrink:0;}
        .hdr-google.disconnected{background:var(--amber-soft);color:#8a5a08;border:1px solid #e6d3a8;}
        .hdr-btn{width:36px;height:36px;border-radius:10px;border:1px solid var(--line);background:var(--card);display:grid;place-items:center;cursor:pointer;font-size:16px;color:var(--muted);transition:background .12s,border-color .12s;position:relative;}
        .hdr-btn:hover{background:#eef1f8;border-color:#d3d9e8;}
        .hdr-btn .notif-dot{position:absolute;top:6px;right:6px;width:7px;height:7px;border-radius:50%;background:var(--rose);border:2px solid var(--card);}
        .hdr-sep{width:1px;height:28px;background:var(--line);margin:0 4px;}
        .hdr-user{display:flex;align-items:center;gap:9px;padding:4px 10px 4px 4px;border-radius:10px;cursor:pointer;transition:background .12s;}
        .hdr-user:hover{background:#eef1f8;}
        .hdr-avatar{width:32px;height:32px;border-radius:50%;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-size:12px;font-weight:700;overflow:hidden;}
        .hdr-avatar img{width:100%;height:100%;object-fit:cover;}
        .hdr-user-name{font-size:13px;font-weight:600;line-height:1.2;}
        .hdr-user-role{font-size:11px;color:var(--muted);line-height:1.2;}

        /* === Layout === */
        .layout{display:flex;min-height:calc(100vh - 52px);}
        .sidebar{width:240px;border-right:1px solid var(--line);background:var(--card);padding:16px 14px;display:flex;flex-direction:column;position:sticky;top:52px;height:calc(100vh - 52px);overflow-y:auto;}
        .brand{display:flex;align-items:center;gap:11px;padding:4px 12px 12px;}
        .brand-logo{width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;display:grid;place-items:center;font-weight:800;font-size:17px;box-shadow:0 4px 12px rgba(76,111,255,.3);flex-shrink:0;}
        .brand .brand-txt{min-width:0;line-height:1.15;}
        .brand strong{font-size:16px;display:block;font-weight:800;letter-spacing:-.01em;}
        .brand span{font-size:11px;color:var(--muted);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;}
        .nav-label{font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;padding:18px 12px 6px;}
        .nav-item{display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:9px;font-size:13.5px;font-weight:500;color:var(--ink);margin-bottom:2px;transition:background .12s;}
        .nav-item:hover{background:#eef1f8;}
        .nav-item.active{background:var(--teal-soft);color:var(--teal-ink);}
        .nav-item .ic{width:16px;text-align:center;}
        .nav-spacer{flex:1;}
        .admin-btn{display:flex;align-items:center;gap:9px;padding:10px 12px;border-radius:9px;background:#10241f;color:#fff;font-size:13.5px;font-weight:600;margin-bottom:8px;}
        .signout{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:9px;border:none;background:none;color:var(--muted);font-size:13.5px;font-weight:500;cursor:pointer;width:100%;font-family:inherit;text-align:left;}
        .main{flex:1;padding:28px 32px;max-width:1040px;}
        .page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;gap:16px;flex-wrap:wrap;}
        .page-head h1{font-size:23px;font-weight:700;letter-spacing:-.02em;}
        .page-head p{font-size:13.5px;color:var(--muted);margin-top:4px;}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:10px 16px;border-radius:10px;border:none;background:var(--teal);color:#fff;font-weight:600;font-size:13.5px;cursor:pointer;font-family:inherit;}
        .btn:hover{background:var(--teal-ink);}
        .btn-ghost{background:var(--card);border:1px solid var(--line);color:var(--ink);transition:border-color .12s,background .12s,color .12s,transform .1s;}
        .btn-ghost:hover{border-color:var(--teal);background:var(--teal-soft);color:var(--teal-ink);transform:translateY(-1px);}
        .icon-btn{width:32px;height:32px;border-radius:9px;border:1px solid var(--line);background:var(--card);display:inline-grid;place-items:center;cursor:pointer;color:var(--muted);font-size:14px;transition:border-color .12s,background .12s,color .12s;vertical-align:middle;margin-left:2px;}
        .icon-btn:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-soft);}
        .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow);}
        .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px;}
        .stat{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:var(--shadow);}
        .stat.accent{background:var(--teal-soft);}
        .stat .v{font-size:25px;font-weight:700;line-height:1;margin-top:9px;}
        .stat .l{font-size:12px;color:var(--muted);margin-top:5px;}
        .badge{font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:999px;display:inline-block;}
        .badge.teal{background:var(--teal-soft);color:var(--teal-ink);}
        .badge.amber{background:var(--amber-soft);color:#8a5a08;}
        .badge.rose{background:var(--rose-soft);color:var(--rose);}
        .badge.gray{background:#eef1f8;color:#5a6273;}
        .alert{padding:11px 14px;border-radius:10px;font-size:13.5px;margin-bottom:16px;}
        .alert.success{background:var(--teal-soft);color:var(--teal-ink);}
        .alert.error{background:var(--rose-soft);color:var(--rose);}
        .alert.info{background:var(--amber-soft);color:#8a5a08;}
        table{width:100%;border-collapse:collapse;font-size:13.5px;}
        th{text-align:left;padding:12px 16px;font-weight:600;color:var(--muted);font-size:12px;}
        td{padding:12px 16px;border-top:1px solid var(--line);}
        input,select,textarea{width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--line);font-size:14px;background:#fcfcfa;font-family:inherit;}
        input:focus,select:focus,textarea:focus{outline:2px solid var(--teal);border-color:transparent;}
        label .lbl{font-size:12.5px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px;margin-top:13px;}
        .modal-bg{position:fixed;inset:0;background:rgba(16,36,31,.4);display:none;place-items:center;padding:20px;z-index:50;}
        .modal-bg.open{display:grid;}
        .modal{width:100%;max-width:440px;background:var(--card);border-radius:18px;padding:24px;max-height:90vh;overflow-y:auto;}
        .modal h2{font-size:18px;margin-bottom:8px;}
        .avatar{width:38px;height:38px;border-radius:50%;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-size:13px;font-weight:700;overflow:hidden;}
        .avatar img{width:100%;height:100%;object-fit:cover;}
        .empty{text-align:center;padding:48px 20px;color:var(--muted);font-size:14px;}

        /* ---- Responsive ---- */
        .topbar{display:none;position:fixed;top:0;left:0;right:0;height:58px;background:var(--card);border-bottom:1px solid var(--line);align-items:center;gap:12px;padding:0 14px;z-index:101;}
        .topbar .brand-logo{width:32px;height:32px;border-radius:9px;background:var(--teal);color:#fff;display:grid;place-items:center;font-weight:700;flex-shrink:0;}
        .topbar strong{font-size:15px;flex:1;}
        .topbar-right{display:flex;align-items:center;gap:8px;}
        .nav-toggle{background:none;border:1px solid var(--line);border-radius:9px;width:38px;height:38px;font-size:18px;cursor:pointer;color:var(--ink);flex-shrink:0;}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(16,36,31,.45);z-index:99;}
        .sidebar-overlay.open{display:block;}
        table{max-width:100%;}
        img{max-width:100%;}
        /* Dark mode toggle */
        .dark-toggle{width:36px;height:36px;border-radius:10px;border:1px solid var(--line);background:var(--card);display:grid;place-items:center;cursor:pointer;font-size:16px;color:var(--muted);transition:background .12s,border-color .12s;}
        .dark-toggle:hover{background:#eef1f8;border-color:#d3d9e8;}
        [data-theme="dark"] .dark-toggle:hover{background:#242c36;border-color:#3a4550;}
        /* Verify email banner */
        .verify-banner{background:#fef3cd;border-bottom:1px solid #f0dca0;padding:10px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;}
        .verify-banner .vb-left{display:flex;align-items:center;gap:12px;font-size:14px;color:#664d03;}
        .verify-banner .vb-icon{width:28px;height:28px;border-radius:8px;background:#fff3cd;border:1px solid #f0dca0;display:grid;place-items:center;font-size:15px;flex-shrink:0;}
        .verify-banner .vb-actions{display:flex;gap:8px;flex-shrink:0;}
        .verify-banner .vb-btn{padding:7px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;}
        .verify-banner .vb-btn.primary{background:#d97706;color:#fff;}
        .verify-banner .vb-btn.primary:hover{background:#b45309;}
        .verify-banner .vb-btn.ghost{background:transparent;border:1px solid #d4a93a;color:#664d03;}
        .verify-banner .vb-btn.ghost:hover{background:#fde68a;}
        [data-theme="dark"] .verify-banner{background:#2d2008;border-color:#4a3510;}
        [data-theme="dark"] .verify-banner .vb-left{color:#fde68a;}
        [data-theme="dark"] .verify-banner .vb-icon{background:#3d2e0a;border-color:#5a4220;}
        [data-theme="dark"] .verify-banner .vb-btn.ghost{border-color:#5a4220;color:#fde68a;}

        @media (max-width:960px){
            .header-bar{display:none;}
            .topbar{display:flex;}
            .layout{flex-direction:column;min-height:calc(100vh - 58px);}
            .sidebar{position:fixed;top:0;left:0;width:260px;max-width:82vw;height:100vh;transform:translateX(-100%);transition:transform .22s ease;z-index:100;box-shadow:0 0 40px rgba(16,36,31,.2);}
            .sidebar.open{transform:translateX(0);}
            .main{max-width:100%;padding:18px 16px;padding-top:76px;}
            .stats{grid-template-columns:repeat(auto-fit,minmax(120px,1fr));}
            table{display:block;overflow-x:auto;white-space:nowrap;-webkit-overflow-scrolling:touch;}
        }
        @media (max-width:520px){
            .main{padding:14px 12px;padding-top:72px;}
            .page-head{flex-direction:column;align-items:stretch;}
            .page-head h1{font-size:19px;}
            .stats{grid-template-columns:repeat(2,1fr);}
            .modal{padding:18px;border-radius:14px;}
        }
    </style>
    @stack('head')
</head>
<body>
{{-- ===== Top Header Bar (desktop) ===== --}}
<div class="header-bar">
    @php
        $hasGoogle = \App\Models\Integration::where('agency_id', auth()->user()->agency_id)->where('provider', 'GOOGLE_GBP')->exists();
    @endphp
    @if($hasGoogle)
        <div class="hdr-google">
            <svg viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#fff" fill-opacity=".9"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#fff" fill-opacity=".7"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#fff" fill-opacity=".6"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#fff" fill-opacity=".8"/></svg>
            Connected with Google
        </div>
    @else
        <div class="hdr-google disconnected">
            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Google Not Connected
        </div>
    @endif

    <div class="hdr-sep"></div>

    <a href="{{ route('credits') }}" class="hdr-btn" title="Credits" style="font-size:13px;width:auto;padding:0 12px;gap:5px;display:flex;align-items:center;">
        @php $creditBal = \App\Models\Subscription::where('agency_id', auth()->user()->agency_id)->value('credit_balance') ?? 0; @endphp
        <span style="font-size:14px;">&#9889;</span> <span style="font-weight:600;">{{ number_format($creditBal) }}</span>
    </a>

    <button class="hdr-btn" title="Notifications" onclick="alert('Notifications coming soon!')">
        <span>&#128276;</span>
        {{-- <div class="notif-dot"></div> --}}
    </button>

    <button class="dark-toggle" title="Toggle dark mode" onclick="toggleDarkMode()">
        <span id="dark-icon">&#9790;</span>
    </button>

    <button class="hdr-btn" title="Settings" onclick="window.location.href='{{ route('billing-settings') }}'">
        <span>&#9881;</span>
    </button>

    <div class="hdr-sep"></div>

    <a href="{{ route('profile') }}" class="hdr-user">
        <div class="hdr-avatar">
            @if(auth()->user()->avatar)<img src="{{ auth()->user()->avatar }}" alt="">@else{{ strtoupper(substr(auth()->user()->name,0,1)) }}@endif
        </div>
        <div>
            <div class="hdr-user-name">{{ auth()->user()->name }}</div>
            <div class="hdr-user-role">{{ auth()->user()->isAdmin() ? 'Admin' : ucwords(strtolower(str_replace('_',' ',auth()->user()->role))) }}</div>
        </div>
    </a>

    <form method="POST" action="{{ route('logout') }}" style="margin:0;">@csrf
        <button class="hdr-btn" type="submit" title="Sign out" style="width:auto;padding:0 12px;gap:6px;font-size:13px;font-weight:600;">
            <span style="font-size:15px;">&#8677;</span> Sign out
        </button>
    </form>
</div>

{{-- ===== Verify Email Banner ===== --}}
@if(!auth()->user()->email_verified_at)
<div class="verify-banner">
    <div class="vb-left">
        <div class="vb-icon">&#9993;</div>
        <div>
            <strong>Verify Your Email</strong> &mdash;
            Verify your email address to receive important alerts and notifications.
            <span style="display:block;margin-top:2px;font-size:13px;opacity:.8;">Email: {{ auth()->user()->email }}</span>
        </div>
    </div>
    <div class="vb-actions">
        <form method="POST" action="{{ route('verification.send') }}" style="display:inline;">@csrf
            <button type="submit" class="vb-btn primary">Send Link</button>
        </form>
        <button type="button" class="vb-btn ghost" onclick="document.getElementById('change-email-modal').classList.add('open')">Change Email</button>
    </div>
</div>
@endif

{{-- Change email modal --}}
<div class="modal-bg" id="change-email-modal">
    <div class="modal">
        <h2>Change Email Address</h2>
        <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Enter your new email. You'll need to verify it after changing.</p>
        <form method="POST" action="{{ route('email.change') }}">
            @csrf
            <label><span class="lbl">New Email Address</span>
                <input type="email" name="email" value="{{ auth()->user()->email }}" required>
            </label>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <button type="submit" class="btn">Update Email</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('change-email-modal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Mobile Top Bar ===== --}}
<div class="topbar">
    <button type="button" class="nav-toggle" onclick="toggleSidebar()" aria-label="Menu">&#9776;</button>
    <div class="brand-logo">R</div>
    <strong>ReviewFlow</strong>
    <div class="topbar-right">
        <a href="{{ route('credits') }}" class="hdr-btn" title="Credits" style="font-size:12px;width:auto;padding:0 8px;gap:4px;display:flex;align-items:center;">&#9889; {{ number_format($creditBal ?? 0) }}</a>
        <button class="hdr-btn" title="Notifications" style="width:34px;height:34px;" onclick="alert('Notifications coming soon!')"><span style="font-size:14px;">&#128276;</span></button>
    </div>
</div>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar(false)"></div>

<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-logo">R</div>
            <div class="brand-txt">
                <strong>ReviewFlow</strong>
                <span>{{ auth()->user()->name }}</span>
            </div>
        </div>

        <div class="nav-label">Workspace</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="ic">&#9638;</span> Overview</a>
        <a href="{{ route('optimize') }}" class="nav-item {{ request()->routeIs('optimize') ? 'active' : '' }}"><span class="ic">&#9889;</span> One-Click Optimize</a>
        <a href="{{ route('ai') }}" class="nav-item {{ request()->routeIs('ai') ? 'active' : '' }}"><span class="ic">&#10022;</span> AI Mode</a>
        <a href="{{ route('clients') }}" class="nav-item {{ request()->routeIs('clients') || request()->routeIs('clients.show') ? 'active' : '' }}"><span class="ic">&#127970;</span> Clients</a>
        @if(auth()->user()->role === 'CLIENT_OWNER')
        <a href="{{ route('team') }}" class="nav-item {{ request()->routeIs('team') ? 'active' : '' }}"><span class="ic">&#128101;</span> My Team</a>
        @endif
        <a href="{{ route('reviews') }}" class="nav-item {{ request()->routeIs('reviews') || request()->routeIs('reviews.show') ? 'active' : '' }}"><span class="ic">&#9733;</span> Reviews</a>
        <a href="{{ route('gbp-content') }}" class="nav-item {{ request()->routeIs('gbp-content') ? 'active' : '' }}"><span class="ic">&#128444;</span> Posts & Photos</a>
        <a href="{{ route('ai-media') }}" class="nav-item {{ request()->routeIs('ai-media') ? 'active' : '' }}"><span class="ic">&#127912;</span> AI Generated Media</a>
        <a href="{{ route('audit') }}" class="nav-item {{ request()->routeIs('audit') ? 'active' : '' }}"><span class="ic">&#9678;</span> Google Audit</a>
        <a href="{{ route('competitors') }}" class="nav-item {{ request()->routeIs('competitors') ? 'active' : '' }}"><span class="ic">&#9876;</span> Competitors</a>
        <a href="{{ route('rank-checker') }}" class="nav-item {{ request()->routeIs('rank-checker') ? 'active' : '' }}"><span class="ic">&#128205;</span> Rank Checker</a>
        <a href="{{ route('social') }}" class="nav-item {{ request()->routeIs('social') ? 'active' : '' }}"><span class="ic">&#9672;</span> Social</a>
        <a href="{{ route('leads') }}" class="nav-item {{ request()->routeIs('leads') ? 'active' : '' }}"><span class="ic">&#9673;</span> Leads CRM</a>
        <a href="{{ route('whatsapp') }}" class="nav-item {{ request()->routeIs('whatsapp') ? 'active' : '' }}"><span class="ic">&#9742;</span> WhatsApp</a>
        <a href="{{ route('ads') }}" class="nav-item {{ request()->routeIs('ads') ? 'active' : '' }}"><span class="ic">&#9636;</span> Ads Reports</a>
        <a href="{{ route('keywords') }}" class="nav-item {{ request()->routeIs('keywords') ? 'active' : '' }}"><span class="ic">&#128269;</span> Keywords</a>

        <div class="nav-label">Billing</div>
        <a href="{{ route('plans') }}" class="nav-item {{ request()->routeIs('plans') ? 'active' : '' }}"><span class="ic">&#11014;</span> Plans &amp; Upgrade</a>
        <a href="{{ route('client-billing') }}" class="nav-item {{ request()->routeIs('client-billing') ? 'active' : '' }}"><span class="ic">&#128179;</span> Billing &amp; Invoices</a>
        <a href="{{ route('credits') }}" class="nav-item {{ request()->routeIs('credits') ? 'active' : '' }}"><span class="ic">&#9889;</span> Credits</a>
        <a href="{{ route('buy-credits') }}" class="nav-item {{ request()->routeIs('buy-credits') ? 'active' : '' }}"><span class="ic">&#128722;</span> Buy Credits</a>
        <a href="{{ route('invoices') }}" class="nav-item {{ request()->routeIs('invoices') || request()->routeIs('invoices.*') ? 'active' : '' }}"><span class="ic">&#129534;</span> Invoices</a>
        <a href="{{ route('customers') }}" class="nav-item {{ request()->routeIs('customers') ? 'active' : '' }}"><span class="ic">&#128101;</span> Customers</a>
        <a href="{{ route('services') }}" class="nav-item {{ request()->routeIs('services') ? 'active' : '' }}"><span class="ic">&#128230;</span> Services</a>
        <a href="{{ route('service-categories') }}" class="nav-item {{ request()->routeIs('service-categories') ? 'active' : '' }}"><span class="ic">&#128193;</span> Categories</a>
        <a href="{{ route('expenses') }}" class="nav-item {{ request()->routeIs('expenses') ? 'active' : '' }}"><span class="ic">&#128198;</span> Expenses</a>
        <a href="{{ route('tally-export') }}" class="nav-item {{ request()->routeIs('tally-export') || request()->routeIs('tally-export.*') ? 'active' : '' }}"><span class="ic">&#128228;</span> Tally Export</a>
        <a href="{{ route('billing-settings') }}" class="nav-item {{ request()->routeIs('billing-settings') ? 'active' : '' }}"><span class="ic">&#9881;</span> Billing Settings</a>

        <div class="nav-spacer"></div>
        <a href="{{ route('profile') }}" class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}"><span class="ic">&#9680;</span> My Profile</a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.overview') }}" class="admin-btn"><span>&#128737;</span> Admin panel</a>
        @endif
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="signout" type="submit">&#8678; Sign out</button></form>
    </aside>
    <main class="main">
        @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>
<script>
function toggleSidebar(force){
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sidebar-overlay');
    const open = typeof force === 'boolean' ? force : !sb.classList.contains('open');
    sb.classList.toggle('open', open);
    ov.classList.toggle('open', open);
}
document.querySelectorAll('.sidebar .nav-item, .sidebar .admin-btn').forEach(el => {
    el.addEventListener('click', () => toggleSidebar(false));
});
function toggleDarkMode(){
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    if(isDark){
        html.removeAttribute('data-theme');
        localStorage.removeItem('rf-theme');
    } else {
        html.setAttribute('data-theme','dark');
        localStorage.setItem('rf-theme','dark');
    }
    updateDarkIcon();
}
function updateDarkIcon(){
    const icon = document.getElementById('dark-icon');
    if(icon) icon.innerHTML = document.documentElement.getAttribute('data-theme') === 'dark' ? '&#9728;' : '&#9790;';
}
(function(){
    if(localStorage.getItem('rf-theme') === 'dark'){
        document.documentElement.setAttribute('data-theme','dark');
    }
    updateDarkIcon();
})();
</script>
@stack('scripts')
</body>
</html>
