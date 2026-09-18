<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') — ReviewFlow</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
    --blue:#4c6fff;--blue2:#6b8afd;--purple:#8b5cf6;
    --ink:#1b2437;--muted:#5a6478;--paper:#f4f6fb;--card:#ffffff;--line:#e7eaf3;
    --shadow:0 1px 2px rgba(20,30,60,.04),0 10px 30px rgba(20,30,60,.06);
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,sans-serif;background:var(--paper);color:var(--ink);min-height:100vh;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

.navbar{display:flex;align-items:center;justify-content:space-between;padding:16px 48px;position:sticky;top:0;z-index:20;background:rgba(244,246,251,.82);backdrop-filter:blur(12px);border-bottom:1px solid var(--line);}
.nav-brand{display:flex;align-items:center;gap:10px;}
.nav-brand-icon{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4c6fff,#8b5cf6);display:grid;place-items:center;font-weight:800;font-size:17px;color:#fff;}
.nav-brand strong{font-size:19px;letter-spacing:-.02em;}
.nav-cta{padding:10px 24px;border-radius:10px;background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;font-weight:700;font-size:14px;border:none;cursor:pointer;transition:transform .15s,box-shadow .15s;}
.nav-cta:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(76,111,255,.35);}

.policy-wrap{max-width:780px;margin:0 auto;padding:48px 24px 80px;}
.policy-wrap h1{font-size:32px;font-weight:800;margin-bottom:8px;background:linear-gradient(135deg,var(--blue),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.policy-wrap .updated{font-size:13px;color:var(--muted);margin-bottom:32px;}
.policy-wrap h2{font-size:20px;font-weight:700;margin:32px 0 12px;padding-top:16px;border-top:1px solid var(--line);}
.policy-wrap h2:first-of-type{border-top:none;padding-top:0;}
.policy-wrap p,.policy-wrap li{font-size:15px;line-height:1.75;color:#3a4359;margin-bottom:10px;}
.policy-wrap ul{padding-left:22px;margin-bottom:16px;}
.policy-wrap ul li{margin-bottom:6px;}
.policy-wrap strong{color:var(--ink);}
.policy-wrap a{color:var(--blue);font-weight:600;}
.policy-wrap a:hover{text-decoration:underline;}
.policy-wrap .card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:20px 24px;margin:20px 0;box-shadow:var(--shadow);}

.footer{text-align:center;padding:32px 24px;font-size:13px;color:var(--muted);border-top:1px solid var(--line);margin-top:40px;}
.footer-links{display:flex;flex-wrap:wrap;justify-content:center;gap:20px;margin-bottom:12px;}
.footer-links a{font-size:13px;color:var(--muted);font-weight:500;}
.footer-links a:hover{color:var(--ink);}

@media(max-width:640px){
    .navbar{padding:14px 16px;}
    .policy-wrap{padding:32px 16px 60px;}
    .policy-wrap h1{font-size:26px;}
}
</style>
</head>
<body>

<nav class="navbar">
    <a href="{{ url('/') }}" class="nav-brand">
        <div class="nav-brand-icon">R</div>
        <strong>ReviewFlow</strong>
    </a>
    <a href="{{ route('login') }}" class="nav-cta">Sign In</a>
</nav>

<div class="policy-wrap">
    @yield('content')
</div>

<footer class="footer">
    <div class="footer-links">
        <a href="{{ route('policy.pricing') }}">Pricing Policy</a>
        <a href="{{ route('policy.shipping') }}">Shipping Policy</a>
        <a href="{{ route('policy.terms') }}">Terms & Conditions</a>
        <a href="{{ route('policy.privacy') }}">Privacy Policy</a>
        <a href="{{ route('policy.refund') }}">Cancellation / Refund</a>
    </div>
    <div>&copy; {{ date('Y') }} ReviewFlow. All rights reserved.</div>
</footer>

</body>
</html>
