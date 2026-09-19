<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>ReviewFlow — Dominate Local Search</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
    --blue:#4c6fff;--blue2:#6b8afd;--purple:#8b5cf6;
    --ink:#1b2437;--muted:#5a6478;--paper:#f4f6fb;--card:#ffffff;--line:#e7eaf3;
    --green:#22c55e;--amber:#f59e0b;--rose:#ef4757;
    --shadow:0 1px 2px rgba(20,30,60,.04),0 10px 30px rgba(20,30,60,.06);
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,sans-serif;background:var(--paper);color:var(--ink);min-height:100vh;overflow-x:hidden;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

/* Navbar */
.navbar{display:flex;align-items:center;justify-content:space-between;padding:16px 48px;position:sticky;top:0;z-index:20;background:rgba(244,246,251,.82);backdrop-filter:blur(12px);border-bottom:1px solid var(--line);}
.nav-brand{display:flex;align-items:center;gap:10px;}
.nav-brand-icon{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4c6fff,#8b5cf6);display:grid;place-items:center;font-weight:800;font-size:17px;color:#fff;}
.nav-brand strong{font-size:19px;letter-spacing:-.02em;}
.nav-links{display:flex;align-items:center;gap:32px;}
.nav-links a{font-size:14px;font-weight:600;color:var(--muted);transition:color .15s;}
.nav-links a:hover{color:var(--ink);}
.nav-cta{padding:10px 24px;border-radius:10px;background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;font-weight:700;font-size:14px;border:none;cursor:pointer;transition:transform .15s,box-shadow .15s;}
.nav-cta:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(76,111,255,.35);}

/* Hero */
.hero{display:grid;grid-template-columns:1fr 1fr;align-items:center;padding:70px 48px 90px;gap:48px;position:relative;background:
    radial-gradient(700px 500px at 88% -8%, rgba(76,111,255,.10), transparent 70%),
    radial-gradient(560px 460px at 6% 108%, rgba(139,92,246,.09), transparent 70%);}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:#eaefff;border:1px solid #d5deff;border-radius:999px;padding:6px 16px;font-size:13px;font-weight:600;color:#3452d1;margin-bottom:24px;}
.hero-badge::before{content:'';width:7px;height:7px;border-radius:50%;background:#4c6fff;box-shadow:0 0 0 4px rgba(76,111,255,.18);}
.hero h1{font-size:54px;font-weight:800;line-height:1.1;letter-spacing:-.03em;margin-bottom:20px;}
.hero h1 .accent{background:linear-gradient(135deg,#4c6fff,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.hero p{font-size:17px;line-height:1.65;color:var(--muted);max-width:490px;margin-bottom:32px;}
.hero-btns{display:flex;gap:14px;flex-wrap:wrap;}
.btn-primary{padding:14px 32px;border-radius:12px;background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:transform .15s,box-shadow .15s;display:inline-block;}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(76,111,255,.35);}
.btn-secondary{padding:14px 32px;border-radius:12px;background:var(--card);border:1px solid var(--line);color:var(--ink);font-weight:600;font-size:15px;cursor:pointer;transition:border-color .15s;display:inline-block;box-shadow:var(--shadow);}
.btn-secondary:hover{border-color:#c7d0e8;}
.hero-trust{display:flex;gap:22px;margin-top:30px;flex-wrap:wrap;}
.hero-trust div{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:7px;}
.hero-trust b{color:var(--ink);}

/* Hero right — dashboard mock */
.hero-visual{position:relative;display:flex;align-items:center;justify-content:center;min-height:440px;}
.mock{width:100%;max-width:460px;background:var(--card);border:1px solid var(--line);border-radius:20px;padding:18px;box-shadow:0 30px 60px rgba(20,30,60,.14);}
.mock-head{display:flex;align-items:center;gap:6px;margin-bottom:14px;}
.mock-dot{width:9px;height:9px;border-radius:50%;}
.mock-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;}
.mock-tile{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:12px 14px;}
.mock-tile .mt-l{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;}
.mock-tile .mt-v{font-size:22px;font-weight:800;margin-top:3px;}
.mock-bars{display:flex;align-items:flex-end;gap:6px;height:70px;padding:10px 4px 0;}
.mock-bars span{flex:1;border-radius:4px 4px 0 0;background:linear-gradient(180deg,#6b8afd,#4c6fff);}
.float-card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:14px 18px;position:absolute;box-shadow:0 16px 40px rgba(20,30,60,.16);}
.float-card .fc-title{font-size:13px;font-weight:700;}
.float-card .fc-sub{font-size:11px;color:var(--muted);margin-top:2px;}
.fc-a{top:-18px;right:-14px;animation:float 6s ease-in-out infinite;}
.fc-b{bottom:-18px;left:-16px;animation:float 6s ease-in-out infinite 3s;}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}

/* Stats bar */
.stats-bar{display:flex;justify-content:center;gap:64px;padding:40px 48px;background:var(--card);border-top:1px solid var(--line);border-bottom:1px solid var(--line);flex-wrap:wrap;}
.sbar-item{text-align:center;}
.sbar-item .sv{font-size:36px;font-weight:800;background:linear-gradient(135deg,#4c6fff,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.sbar-item .sl{font-size:13px;color:var(--muted);margin-top:4px;}

/* Section shell */
.section{padding:84px 48px;}
.section h2{text-align:center;font-size:36px;font-weight:800;letter-spacing:-.02em;margin-bottom:12px;}
.section .sub{text-align:center;font-size:15.5px;color:var(--muted);margin-bottom:52px;max-width:560px;margin-left:auto;margin-right:auto;}
.kicker{text-align:center;font-size:13px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--blue);margin-bottom:12px;}
.section.alt{background:var(--card);border-top:1px solid var(--line);border-bottom:1px solid var(--line);}

/* How it works */
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;max-width:1000px;margin:0 auto;}
.step{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:30px 26px;box-shadow:var(--shadow);}
.section.alt .step{background:var(--paper);}
.step-num{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#4c6fff,#8b5cf6);color:#fff;display:grid;place-items:center;font-weight:800;font-size:17px;margin-bottom:16px;}
.step h3{font-size:18px;font-weight:700;margin-bottom:7px;}
.step p{font-size:13.5px;color:var(--muted);line-height:1.6;}

/* Features */
.feat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;max-width:1080px;margin:0 auto;}
.feat-card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:28px;transition:transform .2s,box-shadow .2s,border-color .2s;box-shadow:var(--shadow);}
.feat-card:hover{transform:translateY(-4px);border-color:#c7d0e8;box-shadow:0 16px 36px rgba(76,111,255,.12);}
.feat-card .fi{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;font-size:22px;margin-bottom:16px;}
.feat-card h3{font-size:17px;font-weight:700;margin-bottom:6px;}
.feat-card p{font-size:13.5px;color:var(--muted);line-height:1.6;}

/* Modules strip */
.mod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;max-width:1080px;margin:0 auto;}
.mod{display:flex;align-items:center;gap:12px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 16px;transition:border-color .15s,transform .15s;box-shadow:var(--shadow);}
.mod:hover{border-color:#c7d0e8;transform:translateY(-2px);}
.mod-ic{width:34px;height:34px;border-radius:9px;background:#eaefff;color:#3452d1;display:grid;place-items:center;font-size:16px;flex-shrink:0;}
.mod b{font-size:13.5px;font-weight:600;}

/* Pricing */
.price-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;max-width:960px;margin:0 auto;}
.price-card{background:var(--card);border:1px solid var(--line);border-radius:20px;padding:32px 26px;position:relative;display:flex;flex-direction:column;box-shadow:var(--shadow);}
.price-card.pop{border:1.5px solid var(--blue);box-shadow:0 18px 40px rgba(76,111,255,.16);}
.price-tag{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#4c6fff,#8b5cf6);color:#fff;font-size:11px;font-weight:700;padding:5px 16px;border-radius:999px;}
.price-name{font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--blue);}
.price-amt{font-size:38px;font-weight:800;margin-top:10px;}
.price-amt span{font-size:15px;color:var(--muted);font-weight:500;}
.price-cr{font-size:13px;color:var(--muted);margin:4px 0 20px;}
.price-feats{list-style:none;flex:1;margin-bottom:22px;}
.price-feats li{font-size:13.5px;color:var(--ink);padding:7px 0;border-top:1px solid var(--line);display:flex;gap:9px;align-items:center;}
.price-feats li:first-child{border-top:none;}
.price-feats li::before{content:'✓';color:var(--blue);font-weight:800;}
.price-btn{padding:12px;border-radius:11px;text-align:center;font-weight:700;font-size:14px;background:var(--paper);border:1px solid var(--line);color:var(--ink);}
.price-card.pop .price-btn{background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;border:none;}

/* CTA */
.cta-section{padding:84px 48px;text-align:center;}
.cta-box{background:linear-gradient(135deg,#4c6fff,#6b8afd 55%,#8b5cf6);border-radius:26px;padding:60px 40px;max-width:760px;margin:0 auto;color:#fff;box-shadow:0 24px 60px rgba(76,111,255,.28);}
.cta-box h2{font-size:32px;font-weight:800;margin-bottom:10px;color:#fff;}
.cta-box p{font-size:15.5px;color:rgba(255,255,255,.9);margin-bottom:28px;}
.cta-box .btn-primary{background:#fff;color:#3452d1;}
.cta-box .btn-primary:hover{box-shadow:0 10px 30px rgba(0,0,0,.18);}

/* Footer */
.footer{padding:32px 48px;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;font-size:13px;color:var(--muted);background:var(--card);}

@media(max-width:900px){
    .navbar{padding:14px 20px;}
    .nav-links{display:none;}
    .hero{grid-template-columns:1fr;padding:44px 20px 60px;text-align:center;}
    .hero h1{font-size:36px;}
    .hero p{margin:0 auto 28px;}
    .hero-btns,.hero-trust{justify-content:center;}
    .hero-visual{display:none;}
    .stats-bar{gap:32px;padding:32px 20px;}
    .section{padding:56px 20px;}
    .section h2{font-size:27px;}
    .steps,.price-grid{grid-template-columns:1fr;}
    .cta-section{padding:56px 20px;}
    .footer{flex-direction:column;text-align:center;}
}
</style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <div class="nav-brand-icon">R</div>
        <strong>ReviewFlow</strong>
    </div>
    <div class="nav-links">
        <a href="#how">How it works</a>
        <a href="#features">Features</a>
        <a href="#pricing">Pricing</a>
        <a href="<?php echo e(route('login')); ?>">Dashboard</a>
    </div>
    <a href="<?php echo e(route('login')); ?>" class="nav-cta">Login</a>
</nav>

<section class="hero">
    <div>
        <div class="hero-badge">Reviews → Customers → Growth</div>
        <h1>Your reputation.<br><span class="accent">Your growth.</span></h1>
        <p>ReviewFlow helps multi-location businesses manage Google Business Profiles, reviews, rankings, and local reputation from one AI-powered dashboard.</p>
        <div class="hero-btns">
            <a href="<?php echo e(route('register')); ?>" class="btn-primary">Get Started Free</a>
            <a href="<?php echo e(route('login')); ?>" class="btn-secondary">Sign In</a>
        </div>
        <div class="hero-trust">
            <div>⭐ <b>4.8/5</b> avg client rating</div>
            <div>⚡ <b>98%</b> AI reply rate</div>
            <div>🔒 No credit card required</div>
        </div>
    </div>
    <div class="hero-visual">
        <div class="mock">
            <div class="mock-head">
                <span class="mock-dot" style="background:#ef4757;"></span>
                <span class="mock-dot" style="background:#f59e0b;"></span>
                <span class="mock-dot" style="background:#22c55e;"></span>
                <span style="margin-left:auto;font-size:11px;color:var(--muted);">Advanced Reports</span>
            </div>
            <div class="mock-row">
                <div class="mock-tile"><div class="mt-l">Avg Rating</div><div class="mt-v" style="color:#4c6fff;">4.83</div></div>
                <div class="mock-tile"><div class="mt-l">Reviews</div><div class="mt-v">1,862</div></div>
            </div>
            <div class="mock-row">
                <div class="mock-tile"><div class="mt-l">Reply Rate</div><div class="mt-v" style="color:#22c55e;">98%</div></div>
                <div class="mock-tile"><div class="mt-l">Visibility</div><div class="mt-v" style="color:#8b5cf6;">+34%</div></div>
            </div>
            <div class="mock-tile" style="margin-top:2px;">
                <div class="mt-l" style="margin-bottom:2px;">Ratings trend</div>
                <div class="mock-bars">
                    <span style="height:45%"></span><span style="height:60%"></span><span style="height:52%"></span>
                    <span style="height:72%"></span><span style="height:85%"></span><span style="height:68%"></span>
                    <span style="height:90%"></span><span style="height:78%"></span>
                </div>
            </div>
        </div>
        <div class="float-card fc-a"><div class="fc-title">⭐ 4.8 Avg Rating</div><div class="fc-sub">Across 12 locations</div></div>
        <div class="float-card fc-b"><div class="fc-title">✦ AI Auto-Reply</div><div class="fc-sub">98% response rate</div></div>
    </div>
</section>

<div class="stats-bar">
    <div class="sbar-item"><div class="sv">5,000+</div><div class="sl">Businesses Managed</div></div>
    <div class="sbar-item"><div class="sv">2M+</div><div class="sl">Reviews Tracked</div></div>
    <div class="sbar-item"><div class="sv">10K+</div><div class="sl">AI Replies Sent</div></div>
    <div class="sbar-item"><div class="sv">99.9%</div><div class="sl">Uptime</div></div>
</div>

<section class="section" id="how">
    <div class="kicker">How it works</div>
    <h2>Live in three simple steps</h2>
    <div class="sub">Connect once, and ReviewFlow keeps your Google presence optimised on autopilot.</div>
    <div class="steps">
        <div class="step">
            <div class="step-num">1</div>
            <h3>Connect Google</h3>
            <p>Securely link your Google Business Profile in one click. We import every location, review and rating automatically.</p>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <h3>Automate with AI</h3>
            <p>AI drafts on-brand replies, posts, captions and images. Sentiment is analysed for every review as it lands.</p>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <h3>Track &amp; Grow</h3>
            <p>Watch rankings climb on the live dashboard — rating trends, reply rate, local rank and competitor gaps in real time.</p>
        </div>
    </div>
</section>

<section class="section alt" id="features">
    <div class="kicker">Features</div>
    <h2>Everything you need to rank locally</h2>
    <div class="sub">All the tools your agency needs, in one powerful dashboard.</div>
    <div class="feat-grid">
        <div class="feat-card">
            <div class="fi" style="background:#eaefff;color:#4c6fff;">★</div>
            <h3>Review Management</h3>
            <p>Monitor, reply and analyse reviews across all locations. AI-powered responses in seconds with live sentiment.</p>
        </div>
        <div class="feat-card">
            <div class="fi" style="background:#dcfce7;color:#16a34a;">◎</div>
            <h3>Google Business Profiles</h3>
            <p>Manage posts, photos and business info. Bulk-optimise every location at once from a single screen.</p>
        </div>
        <div class="feat-card">
            <div class="fi" style="background:#fef3d6;color:#d97706;">📍</div>
            <h3>Local Rank Tracking</h3>
            <p>Track keyword rankings on a real geo-grid powered by Google Places. Get alerts when positions move.</p>
        </div>
        <div class="feat-card">
            <div class="fi" style="background:#f3e8ff;color:#8b5cf6;">✦</div>
            <h3>AI Content Studio</h3>
            <p>Generate posts, captions, review replies and images with built-in Gemini AI. Simple credits-based billing.</p>
        </div>
        <div class="feat-card">
            <div class="fi" style="background:#fde7ef;color:#db2777;">⚔</div>
            <h3>Competitor Analysis</h3>
            <p>Benchmark competitor ratings, review velocity and rankings so you always stay a step ahead locally.</p>
        </div>
        <div class="feat-card">
            <div class="fi" style="background:#e0f7fb;color:#0891b2;">🧾</div>
            <h3>Invoicing &amp; Billing</h3>
            <p>Professional invoices, GST support, Tally export and expense tracking — the whole back office, built in.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="kicker">One platform</div>
    <h2>All your local marketing, unified</h2>
    <div class="sub">Twelve powerful modules working together under one login.</div>
    <div class="mod-grid">
        <div class="mod"><div class="mod-ic">★</div><b>Reviews &amp; Replies</b></div>
        <div class="mod"><div class="mod-ic">🖼</div><b>Posts &amp; Photos</b></div>
        <div class="mod"><div class="mod-ic">🎨</div><b>AI Generated Media</b></div>
        <div class="mod"><div class="mod-ic">◎</div><b>Google Audit</b></div>
        <div class="mod"><div class="mod-ic">📍</div><b>Rank Checker</b></div>
        <div class="mod"><div class="mod-ic">⚔</div><b>Competitors</b></div>
        <div class="mod"><div class="mod-ic">◍</div><b>Social Posting</b></div>
        <div class="mod"><div class="mod-ic">☎</div><b>WhatsApp Outreach</b></div>
        <div class="mod"><div class="mod-ic">◉</div><b>Leads CRM</b></div>
        <div class="mod"><div class="mod-ic">🔍</div><b>Keyword Ideas</b></div>
        <div class="mod"><div class="mod-ic">🧾</div><b>Invoicing &amp; Tally</b></div>
        <div class="mod"><div class="mod-ic">✦</div><b>AI Marketing Chat</b></div>
    </div>
</section>

<section class="section alt" id="pricing">
    <div class="kicker">Pricing</div>
    <h2>Simple, transparent plans</h2>
    <div class="sub">Start free, upgrade when you grow. Every plan includes monthly AI credits.</div>
    <div class="price-grid">
        <div class="price-card">
            <div class="price-name">Starter</div>
            <div class="price-amt">₹999<span>/mo</span></div>
            <div class="price-cr">100 AI credits / month</div>
            <ul class="price-feats"><li>1 GBP location</li><li>Reviews + AI reply</li><li>Photo posting</li></ul>
            <a href="<?php echo e(route('register')); ?>" class="price-btn">Get Started</a>
        </div>
        <div class="price-card pop">
            <div class="price-tag">Most Popular</div>
            <div class="price-name">Growth</div>
            <div class="price-amt">₹2,999<span>/mo</span></div>
            <div class="price-cr">500 AI credits / month</div>
            <ul class="price-feats"><li>Multiple GBP locations</li><li>Social posting</li><li>Lead CRM + WhatsApp</li></ul>
            <a href="<?php echo e(route('register')); ?>" class="price-btn">Start Free Trial</a>
        </div>
        <div class="price-card">
            <div class="price-name">Agency</div>
            <div class="price-amt">₹9,999<span>/mo</span></div>
            <div class="price-cr">2,000 AI credits / month</div>
            <ul class="price-feats"><li>Unlimited clients</li><li>Ads reporting</li><li>White label</li></ul>
            <a href="<?php echo e(route('register')); ?>" class="price-btn">Contact Sales</a>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="cta-box">
        <h2>Ready to grow your local business?</h2>
        <p>Join thousands of agencies managing their Google presence with ReviewFlow.</p>
        <a href="<?php echo e(route('register')); ?>" class="btn-primary" style="padding:15px 40px;font-size:16px;">Start Free Trial</a>
    </div>
</section>

<footer class="footer">
    <div class="nav-brand"><div class="nav-brand-icon" style="width:30px;height:30px;font-size:14px;">R</div><strong style="font-size:16px;">ReviewFlow</strong></div>
    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:18px;margin:14px 0 10px;">
        <a href="<?php echo e(route('policy.pricing')); ?>" style="font-size:13px;color:#5a6478;">Pricing Policy</a>
        <a href="<?php echo e(route('policy.shipping')); ?>" style="font-size:13px;color:#5a6478;">Shipping Policy</a>
        <a href="<?php echo e(route('policy.terms')); ?>" style="font-size:13px;color:#5a6478;">Terms & Conditions</a>
        <a href="<?php echo e(route('policy.privacy')); ?>" style="font-size:13px;color:#5a6478;">Privacy Policy</a>
        <a href="<?php echo e(route('policy.refund')); ?>" style="font-size:13px;color:#5a6478;">Cancellation / Refund</a>
    </div>
    <div>&copy; <?php echo e(date('Y')); ?> ReviewFlow. All rights reserved.</div>
</footer>

</body>
</html>
<?php /**PATH D:\reviewflow-laravel\resources\views\welcome.blade.php ENDPATH**/ ?>