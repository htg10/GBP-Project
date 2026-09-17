<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — ReviewFlow</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',ui-sans-serif,system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;overflow:hidden;}

/* Left panel — gradient */
.left{flex:1;background:linear-gradient(135deg,#7c3aed 0%,#a78bfa 40%,#c084fc 70%,#e9d5ff 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px;position:relative;overflow:hidden;}
.left::before,.left::after{content:'';position:absolute;border-radius:50%;background:rgba(255,255,255,.08);}
.left::before{width:300px;height:300px;top:-60px;left:-60px;}
.left::after{width:200px;height:200px;bottom:80px;right:-40px;}
.deco1,.deco2,.deco3{position:absolute;border-radius:50%;background:rgba(255,255,255,.07);}
.deco1{width:120px;height:120px;bottom:220px;left:60px;}
.deco2{width:160px;height:160px;bottom:-30px;right:100px;}
.deco3{width:90px;height:90px;top:160px;right:80px;}

.left-logo{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);display:grid;place-items:center;font-weight:800;font-size:24px;color:#fff;margin-bottom:28px;border:1px solid rgba(255,255,255,.2);position:relative;z-index:1;}
.left h1{font-size:30px;font-weight:800;color:#fff;text-align:center;line-height:1.25;margin-bottom:8px;position:relative;z-index:1;}
.left p{font-size:15px;color:rgba(255,255,255,.8);text-align:center;margin-bottom:36px;position:relative;z-index:1;}
.feature-pills{display:flex;flex-direction:column;gap:12px;position:relative;z-index:1;width:100%;max-width:400px;}
.fpill{display:flex;align-items:center;gap:12px;background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:14px;padding:14px 20px;color:#fff;font-size:14px;font-weight:500;}
.fpill .fp-icon{width:32px;height:32px;border-radius:9px;background:rgba(255,255,255,.15);display:grid;place-items:center;font-size:15px;flex-shrink:0;}

/* Right panel — form */
.right{width:520px;min-width:420px;display:flex;align-items:center;justify-content:center;background:#fff;padding:48px;}
.form-box{width:100%;max-width:380px;}
.form-box h2{font-size:26px;font-weight:800;color:#1a1a2e;text-align:center;margin-bottom:4px;}
.form-box .sub{font-size:14px;color:#8b8fa3;text-align:center;margin-bottom:28px;}

.form-label{display:block;font-size:13px;font-weight:600;color:#4a4a5a;margin-bottom:6px;margin-top:18px;}
.form-input{width:100%;padding:12px 14px;border-radius:12px;border:2px solid #e8e6f0;font-size:14px;background:#fafafe;font-family:inherit;transition:border-color .15s;}
.form-input:focus{outline:none;border-color:#7c3aed;}

.form-row{display:flex;align-items:center;justify-content:space-between;margin-top:16px;}
.form-row label{display:flex;align-items:center;gap:8px;font-size:13px;color:#6b6b80;cursor:pointer;}
.form-row label input{accent-color:#7c3aed;width:16px;height:16px;}
.form-row a{font-size:13px;color:#7c3aed;font-weight:600;}
.form-row a:hover{text-decoration:underline;}

.btn-signin{width:100%;margin-top:22px;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,#7c3aed,#a78bfa);color:#fff;font-weight:700;font-size:15px;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;transition:transform .1s,box-shadow .15s;}
.btn-signin:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(124,58,237,.3);}

.divider{display:flex;align-items:center;gap:14px;margin:22px 0;color:#c5c5d2;font-size:13px;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e8e6f0;}

.btn-google{width:100%;padding:13px;border-radius:12px;border:2px solid #e8e6f0;background:#fff;font-size:14px;font-weight:600;color:#3c4043;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:10px;transition:background .15s,border-color .15s;}
.btn-google:hover{background:#f8f8fc;border-color:#d0d0e0;}
.btn-google svg{width:20px;height:20px;flex-shrink:0;}

.signup-link{text-align:center;margin-top:22px;font-size:14px;color:#8b8fa3;}
.signup-link a{color:#7c3aed;font-weight:600;display:inline-flex;align-items:center;gap:4px;}
.signup-link a:hover{text-decoration:underline;}

.err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-size:13px;padding:10px 14px;border-radius:10px;margin-bottom:12px;}
.demo{font-size:12px;color:#a0a0b5;text-align:center;margin-top:16px;line-height:1.7;background:#fafafe;padding:10px 14px;border-radius:10px;border:1px solid #e8e6f0;}
.copy{text-align:center;font-size:12px;color:#b0b0c0;margin-top:24px;}

@media(max-width:900px){
    body{flex-direction:column;}
    .left{min-height:280px;padding:32px 24px;}
    .left h1{font-size:22px;}
    .feature-pills{display:none;}
    .right{width:100%;min-width:unset;padding:32px 24px;flex:1;}
}
</style>
</head>
<body>

<div class="left">
    <div class="deco1"></div><div class="deco2"></div><div class="deco3"></div>
    <div class="left-logo">R</div>
    <h1>Boost Your Business<br>Ranking</h1>
    <p>Manage your Google My Business profile with AI-powered tools and insights</p>
    <div class="feature-pills">
        <div class="fpill"><div class="fp-icon">&#10003;</div> AI-Powered Review Responses</div>
        <div class="fpill"><div class="fp-icon">&#9776;</div> Real-Time Analytics Dashboard</div>
        <div class="fpill"><div class="fp-icon">&#9889;</div> Automated Post Scheduling</div>
    </div>
</div>

<div class="right">
    <div class="form-box">
        <h2>Welcome Back</h2>
        <p class="sub">Sign in to boost your business ranking</p>

        <?php if($errors->any()): ?><div class="err"><?php echo e($errors->first()); ?></div><?php endif; ?>

        <form method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo csrf_field(); ?>
            <label class="form-label">Email Address</label>
            <input class="form-input" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="you@company.com" required>

            <label class="form-label">Password</label>
            <input class="form-input" type="password" name="password" placeholder="Enter your password" required>

            <div class="form-row">
                <label><input type="checkbox" name="remember"> Remember me</label>
                <a href="#">Forgot Password?</a>
            </div>

            <button class="btn-signin" type="submit">Sign In &rarr;</button>
        </form>

        <div class="divider">or continue with</div>

        <button class="btn-google" type="button" onclick="window.location.href='<?php echo e(route('google.login')); ?>'">
            <svg viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Login with Google
        </button>

        <div class="signup-link">
            Don't have an account? <a href="<?php echo e(route('register')); ?>">Sign Up &rsaquo;</a>
        </div>

        <div class="demo">Admin: admin@demo.com / admin123<br>User: owner@demo.com / password123</div>
        <div class="copy">&copy; <?php echo e(date('Y')); ?> ReviewFlow. All rights reserved.</div>
    </div>
</div>

</body>
</html>
<?php /**PATH D:\reviewflow-laravel\resources\views\auth\login.blade.php ENDPATH**/ ?>