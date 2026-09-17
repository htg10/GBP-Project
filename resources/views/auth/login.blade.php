<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — ReviewFlow</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;background:#eef1f8;-webkit-font-smoothing:antialiased;}

/* ---- Left panel ---- */
.left{flex:1.15;background:linear-gradient(150deg,#5b6ef5 0%,#7c6df0 45%,#9b6ef0 100%);padding:40px 48px;position:relative;overflow:hidden;color:#fff;display:flex;flex-direction:column;}
.blob{position:absolute;border-radius:50%;background:rgba(255,255,255,.07);}
.blob.b1{width:340px;height:340px;top:-90px;left:-90px;}
.blob.b2{width:220px;height:220px;bottom:-60px;left:120px;}
.blob.b3{width:160px;height:160px;top:120px;right:-40px;}
.l-top{display:flex;align-items:center;justify-content:space-between;position:relative;z-index:2;}
.l-brand{display:flex;align-items:center;gap:11px;}
.l-brand .lb-logo{width:40px;height:40px;border-radius:11px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.28);backdrop-filter:blur(8px);display:grid;place-items:center;font-weight:800;font-size:18px;}
.l-brand strong{font-size:19px;font-weight:800;letter-spacing:-.01em;}
.l-steps{font-size:11px;font-weight:700;letter-spacing:.14em;color:rgba(255,255,255,.6);}
.l-body{position:relative;z-index:2;margin-top:auto;margin-bottom:auto;padding:24px 0;}
.l-body h1{font-size:42px;font-weight:800;line-height:1.1;letter-spacing:-.02em;}
.l-body p{font-size:16px;color:rgba(255,255,255,.85);margin-top:14px;max-width:380px;line-height:1.5;}

/* floating cards */
.cards{position:relative;height:250px;margin-top:26px;}
.fcard{position:absolute;background:#fff;color:#1b2437;border-radius:16px;box-shadow:0 16px 40px rgba(20,20,60,.22);}
.fc-review{top:0;left:0;width:290px;padding:16px 18px;animation:fl 6s ease-in-out infinite;}
.fc-stars{color:#fbbf24;font-size:15px;letter-spacing:2px;}
.fc-quote{font-size:13.5px;font-weight:600;margin:8px 0 12px;line-height:1.4;}
.fc-user{display:flex;align-items:center;gap:9px;}
.fc-av{width:30px;height:30px;border-radius:50%;background:#eaefff;color:#4c6fff;display:grid;place-items:center;font-weight:700;font-size:12px;}
.fc-user .n{font-size:12.5px;font-weight:700;}
.fc-user .r{font-size:11px;color:#8b93a7;}
.fc-user .t{margin-left:auto;font-size:11px;color:#b4bacc;}
.fc-total{top:14px;right:0;width:210px;padding:15px 17px;animation:fl 6s ease-in-out infinite 1.6s;}
.fc-total .tl{font-size:11.5px;color:#8b93a7;font-weight:600;}
.fc-total .tv{font-size:28px;font-weight:800;margin-top:2px;}
.fc-total .tg{font-size:12px;color:#22c55e;font-weight:700;}
.fc-bars{display:flex;align-items:flex-end;gap:4px;height:40px;margin-top:8px;}
.fc-bars span{flex:1;border-radius:3px 3px 0 0;background:linear-gradient(180deg,#8b9bff,#4c6fff);}
.fc-map{bottom:0;left:36px;width:250px;padding:14px 16px;display:flex;align-items:center;gap:12px;animation:fl 6s ease-in-out infinite 3.2s;}
.fc-map .pin{width:34px;height:34px;border-radius:50%;background:#fde7ef;color:#ec4899;display:grid;place-items:center;font-size:16px;flex-shrink:0;}
.fc-map .mt{font-size:13px;font-weight:700;}
.fc-map .ms{height:6px;background:#eef1f8;border-radius:4px;margin-top:6px;width:120px;}
@keyframes fl{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

.l-feats{display:flex;gap:26px;position:relative;z-index:2;margin-top:8px;}
.l-feat{flex:1;}
.l-feat .lf-ic{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.16);display:grid;place-items:center;font-size:15px;margin-bottom:8px;}
.l-feat h4{font-size:14px;font-weight:700;}
.l-feat p{font-size:11.5px;color:rgba(255,255,255,.75);margin-top:3px;line-height:1.4;}

/* ---- Right panel ---- */
.right{width:520px;min-width:440px;display:flex;align-items:center;justify-content:center;background:#fff;padding:40px;}
.form-box{width:100%;max-width:380px;}
.r-brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:22px;}
.r-brand .rb-logo{width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#4c6fff,#8b5cf6);display:grid;place-items:center;font-weight:800;font-size:17px;color:#fff;}
.r-brand strong{font-size:19px;font-weight:800;color:#1b2437;}
.form-box h2{font-size:26px;font-weight:800;color:#1b2437;text-align:center;}
.form-box .sub{font-size:14px;color:#8b93a7;text-align:center;margin-top:4px;margin-bottom:24px;}

.btn-google{width:100%;padding:13px;border-radius:12px;border:1.5px solid #e4e7f0;background:#fff;font-size:14.5px;font-weight:700;color:#3c4043;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:10px;transition:background .15s,border-color .15s;}
.btn-google:hover{background:#f7f8fc;border-color:#d3d9e8;}
.btn-google svg{width:20px;height:20px;flex-shrink:0;}
.divider{display:flex;align-items:center;gap:14px;margin:20px 0;color:#aab1c4;font-size:12.5px;font-weight:500;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e7eaf3;}

.form-label{display:block;font-size:13px;font-weight:700;color:#3a4256;margin-bottom:7px;margin-top:16px;}
.input-wrap{position:relative;}
.input-wrap .ic{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#aab1c4;font-size:15px;}
.form-input{width:100%;padding:12px 14px 12px 40px;border-radius:12px;border:1.5px solid #e4e7f0;font-size:14px;background:#fbfcfe;font-family:inherit;transition:border-color .15s,box-shadow .15s;}
.form-input:focus{outline:none;border-color:#4c6fff;box-shadow:0 0 0 3px rgba(76,111,255,.12);background:#fff;}
.eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aab1c4;font-size:15px;}

.form-row{display:flex;align-items:center;justify-content:space-between;margin-top:16px;}
.form-row label{display:flex;align-items:center;gap:8px;font-size:13px;color:#5a6478;cursor:pointer;}
.form-row label input{accent-color:#4c6fff;width:16px;height:16px;}
.form-row a{font-size:13px;color:#4c6fff;font-weight:600;}
.form-row a:hover{text-decoration:underline;}

.btn-signin{width:100%;margin-top:22px;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,#4c6fff,#8b5cf6);color:#fff;font-weight:700;font-size:15px;cursor:pointer;font-family:inherit;transition:transform .1s,box-shadow .15s;}
.btn-signin:hover{transform:translateY(-1px);box-shadow:0 8px 22px rgba(76,111,255,.32);}

.signup-link{text-align:center;margin-top:20px;font-size:14px;color:#8b93a7;}
.signup-link a{color:#4c6fff;font-weight:700;}
.signup-link a:hover{text-decoration:underline;}
.err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-size:13px;padding:10px 14px;border-radius:10px;margin-bottom:14px;}
.demo{font-size:11.5px;color:#a0a6b8;text-align:center;margin-top:16px;line-height:1.7;background:#fafbff;padding:9px 14px;border-radius:10px;border:1px solid #eef1f8;}
.foot{text-align:center;font-size:12px;color:#aab1c4;margin-top:22px;}
.foot a{color:#8b93a7;}

@media(max-width:960px){
    body{flex-direction:column;}
    .left{flex:none;padding:28px 22px 34px;}
    .cards,.l-feats{display:none;}
    .l-body{margin:18px 0;}
    .l-body h1{font-size:30px;}
    .right{width:100%;min-width:unset;padding:30px 22px;flex:1;}
}
</style>
</head>
<body>

<div class="left">
    <div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>

    <div class="l-top">
        <div class="l-brand"><div class="lb-logo">R</div><strong>ReviewFlow</strong></div>
        <div class="l-steps">REVIEWS &nbsp;→&nbsp; CUSTOMERS &nbsp;→&nbsp; GROWTH</div>
    </div>

    <div class="l-body">
        <h1>Your reputation.<br>Your growth.</h1>
        <p>Manage reviews, connect with customers, and grow your business.</p>

        <div class="cards">
            <div class="fcard fc-review">
                <div class="fc-stars">★★★★★</div>
                <div class="fc-quote">"Amazing service and great customer support!"</div>
                <div class="fc-user">
                    <div class="fc-av">S</div>
                    <div><div class="n">Sarah M.</div><div class="r">Local Guide</div></div>
                    <div class="t">2 days ago</div>
                </div>
            </div>
            <div class="fcard fc-total">
                <div class="tl">Total Reviews</div>
                <div class="tv">248</div>
                <div class="tg">↑ 12%</div>
                <div class="fc-bars"><span style="height:40%"></span><span style="height:55%"></span><span style="height:48%"></span><span style="height:70%"></span><span style="height:85%"></span><span style="height:95%"></span></div>
            </div>
            <div class="fcard fc-map">
                <div class="pin">📍</div>
                <div><div class="mt">More customers</div><div class="mt" style="font-weight:600;color:#8b93a7;font-size:12px;">in more locations</div><div class="ms"></div></div>
            </div>
        </div>
    </div>

    <div class="l-feats">
        <div class="l-feat"><div class="lf-ic">📊</div><h4>Build Trust</h4><p>Turn happy customers into your best marketing.</p></div>
        <div class="l-feat"><div class="lf-ic">👥</div><h4>Get Found</h4><p>Show up where customers are searching.</p></div>
        <div class="l-feat"><div class="lf-ic">⚡</div><h4>Grow Faster</h4><p>More reviews. More customers. A stronger business.</p></div>
    </div>
</div>

<div class="right">
    <div class="form-box">
        <div class="r-brand"><div class="rb-logo">R</div><strong>ReviewFlow</strong></div>
        <h2>Welcome back</h2>
        <p class="sub">Sign in to your ReviewFlow account</p>

        @if($errors->any())<div class="err">{{ $errors->first() }}</div>@endif

        <button class="btn-google" type="button" onclick="window.location.href='{{ route('google.login') }}'">
            <svg viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Continue with Google
        </button>

        <div class="divider">or sign in with email</div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label class="form-label">Email address</label>
            <div class="input-wrap">
                <span class="ic">✉</span>
                <input class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required>
            </div>

            <label class="form-label">Password</label>
            <div class="input-wrap">
                <span class="ic">🔒</span>
                <input class="form-input" type="password" name="password" id="pw" placeholder="••••••••••" required>
                <button type="button" class="eye" onclick="const p=document.getElementById('pw');p.type=p.type==='password'?'text':'password';this.textContent=p.type==='password'?'👁':'🙈';">👁</button>
            </div>

            <div class="form-row">
                <label><input type="checkbox" name="remember" checked> Remember me</label>
                <a href="#">Forgot password?</a>
            </div>

            <button class="btn-signin" type="submit">Sign In</button>
        </form>

        <div class="signup-link">Don't have an account? <a href="{{ route('register') }}">Sign up</a></div>

        <div class="demo">Admin: admin@demo.com / admin123<br>Client: client@demo.com / clientpass123</div>
        <div class="foot"><a href="#">Privacy Policy</a> &nbsp;·&nbsp; <a href="#">Terms of Service</a></div>
    </div>
</div>

</body>
</html>
