<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Admin'); ?> — ReviewFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#1b2437;--paper:#f4f6fb;--card:#fff;--teal:#4c6fff;--teal-soft:#eaefff;--teal-ink:#3452d1;--amber:#f59e0b;--line:#eaecf3;--muted:#7a8599;--rose:#ef4757;--rose-soft:#fde7e9;--green:#22c55e;--purple:#8b5cf6;--shadow:0 1px 2px rgba(20,30,60,.04),0 8px 28px rgba(20,30,60,.06);}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--paper);color:var(--ink);-webkit-font-smoothing:antialiased;}
        a{text-decoration:none;color:inherit;}
        .layout{display:flex;min-height:100vh;}
        .sidebar{width:235px;border-right:1px solid var(--line);background:var(--card);padding:20px 14px;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;}
        .brand{display:flex;align-items:center;gap:10px;padding:0 8px 6px;}
        .brand-logo{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#1b2437,#3452d1);color:#fff;display:grid;place-items:center;font-weight:700;}
        .brand strong{font-size:15px;display:block;}.brand span{font-size:11px;color:var(--muted);}
        .nav-label{font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;padding:16px 12px 6px;}
        .nav-item{display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:9px;font-size:13.5px;font-weight:500;margin-bottom:2px;}
        .nav-item:hover{background:#eef1f8;}.nav-item.active{background:var(--teal-soft);color:var(--teal-ink);}
        .nav-spacer{flex:1;}
        .back{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:9px;color:var(--muted);font-size:13.5px;font-weight:500;border-top:1px solid var(--line);margin-top:8px;padding-top:14px;}
        .signout{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:9px;border:none;background:none;color:var(--muted);font-size:13.5px;cursor:pointer;font-family:inherit;width:100%;text-align:left;}
        .main{flex:1;padding:26px 30px;max-width:940px;}
        .page-head{margin-bottom:22px;}.page-head h1{font-size:22px;font-weight:700;}.page-head p{font-size:13px;color:var(--muted);margin-top:3px;}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:9px 14px;border-radius:9px;border:none;background:var(--teal);color:#fff;font-weight:600;font-size:13px;cursor:pointer;font-family:inherit;}
        .btn-ghost{background:var(--card);border:1px solid var(--line);color:var(--ink);}
        .btn-ghost:hover{border-color:#d3d9e8;}
        .card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px;box-shadow:var(--shadow);}
        .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px;}
        .stat{background:var(--card);border:1px solid var(--line);border-radius:13px;padding:15px;}
        .stat.accent{background:var(--teal-soft);}.stat .v{font-size:24px;font-weight:700;margin-top:8px;}.stat .l{font-size:12px;color:var(--muted);margin-top:4px;}
        .badge{font-size:12px;font-weight:600;padding:3px 9px;border-radius:999px;display:inline-block;}
        .badge.teal{background:var(--teal-soft);color:var(--teal-ink);}.badge.dark{background:#1b2437;color:#fff;}
        .alert{padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;}
        .alert.success{background:var(--teal-soft);color:var(--teal-ink);}.alert.error{background:var(--rose-soft);color:var(--rose);}
        table{width:100%;border-collapse:collapse;font-size:13.5px;}th{text-align:left;padding:12px 16px;font-weight:600;color:var(--muted);font-size:12px;}td{padding:12px 16px;border-top:1px solid var(--line);}
        input,select{width:100%;padding:10px 12px;border-radius:9px;border:1px solid var(--line);font-size:14px;background:#fcfcfa;font-family:inherit;}
        label .lbl{font-size:12.5px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px;margin-top:12px;}
        .modal-bg{position:fixed;inset:0;background:rgba(16,36,31,.35);display:none;place-items:center;padding:20px;z-index:50;}
        .modal-bg.open{display:grid;}.modal{width:100%;max-width:400px;background:#fff;border-radius:16px;padding:24px;}
        .icon-btn{width:32px;height:32px;border-radius:9px;border:1px solid var(--line);background:var(--card);cursor:pointer;color:var(--muted);font-size:14px;display:inline-grid;place-items:center;vertical-align:middle;margin-left:2px;transition:border-color .12s,background .12s,color .12s;}
        .icon-btn:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-soft);}
        .btn-ghost:hover{border-color:var(--teal);background:var(--teal-soft);color:var(--teal-ink);}

        /* ---- Responsive: tablet / phone ---- */
        .topbar{display:none;position:fixed;top:0;left:0;right:0;height:56px;background:var(--card);border-bottom:1px solid var(--line);align-items:center;gap:12px;padding:0 14px;z-index:101;}
        .topbar .brand-logo{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#1b2437,#3452d1);color:#fff;display:grid;place-items:center;font-weight:700;flex-shrink:0;}
        .topbar strong{font-size:14.5px;flex:1;}
        .nav-toggle{background:none;border:1px solid var(--line);border-radius:9px;width:36px;height:36px;font-size:17px;cursor:pointer;color:var(--ink);flex-shrink:0;}
        .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(16,36,31,.45);z-index:99;}
        .sidebar-overlay.open{display:block;}
        img{max-width:100%;}

        @media (max-width:960px){
            .topbar{display:flex;}
            .layout{flex-direction:column;}
            .sidebar{position:fixed;top:0;left:0;width:250px;max-width:82vw;transform:translateX(-100%);transition:transform .22s ease;z-index:100;box-shadow:0 0 40px rgba(16,36,31,.2);}
            .sidebar.open{transform:translateX(0);}
            .main{max-width:100%;padding:16px 14px;padding-top:74px;}
            .stats{grid-template-columns:repeat(auto-fit,minmax(120px,1fr));}
            table{display:block;overflow-x:auto;white-space:nowrap;-webkit-overflow-scrolling:touch;}
        }
        @media (max-width:520px){
            .stats{grid-template-columns:repeat(2,1fr);}
            .modal{padding:18px;border-radius:14px;}
        }
    </style>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body>
<div class="topbar">
    <button type="button" class="nav-toggle" onclick="toggleSidebar()" aria-label="Menu">☰</button>
    <div class="brand-logo">🛡</div>
    <strong>Admin Panel</strong>
</div>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar(false)"></div>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand"><div class="brand-logo">🛡</div><div><strong>Admin Panel</strong><span><?php echo e(auth()->user()->name); ?></span></div></div>
        <div class="nav-label">Manage</div>
        <a href="<?php echo e(route('admin.overview')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.overview') ? 'active' : ''); ?>">▦ Overview</a>
        <a href="<?php echo e(route('admin.users')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.users') ? 'active' : ''); ?>">◉ Users</a>
        <a href="<?php echo e(route('admin.plans')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.plans') ? 'active' : ''); ?>">▤ Plans</a>
        <a href="<?php echo e(route('admin.credit-packages')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.credit-packages') ? 'active' : ''); ?>">⚡ Credit Packs</a>
        <div class="nav-spacer"></div>
        <a href="<?php echo e(route('dashboard')); ?>" class="back">⇄ User view</a>
        <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="signout" type="submit">⇦ Sign out</button></form>
    </aside>
    <main class="main">
        <?php if(session('success')): ?><div class="alert success"><?php echo e(session('success')); ?></div><?php endif; ?>
        <?php if($errors->any()): ?><div class="alert error"><?php echo e($errors->first()); ?></div><?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
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
document.querySelectorAll('.sidebar .nav-item, .sidebar .back').forEach(el => {
    el.addEventListener('click', () => toggleSidebar(false));
});
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\reviewflow-laravel\resources\views\layouts\admin.blade.php ENDPATH**/ ?>