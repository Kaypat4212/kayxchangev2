<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign In – KayXchange</title>
  <link rel="icon" type="image/png" href="<?php echo e(asset('Assests/favicon.png')); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo e(asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')); ?>" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: ''Poppins'', sans-serif; }
    body { background: #060e06; color: #e0e0e0; min-height: 100vh; display: flex; flex-direction: column; }
    .lx-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; background-image: linear-gradient(rgba(0,204,0,0.035) 1px,transparent 1px),linear-gradient(90deg,rgba(0,204,0,0.035) 1px,transparent 1px); background-size: 55px 55px; }
    .lx-orb1 { position: fixed; z-index: 0; pointer-events: none; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle,rgba(0,204,0,0.10) 0%,transparent 70%); top: -200px; right: -150px; filter: blur(80px); }
    .lx-orb2 { position: fixed; z-index: 0; pointer-events: none; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle,rgba(0,100,0,0.12) 0%,transparent 70%); bottom: -140px; left: -100px; filter: blur(80px); }
    .lx-topbar { position: relative; z-index: 10; background: rgba(8,14,8,0.96); backdrop-filter: blur(18px); border-bottom: 1px solid rgba(0,204,0,0.12); padding: 12px 0; }
    .lx-brand { display: flex; align-items: center; gap: 10px; text-decoration: none !important; color: #fff; font-size: 1.15rem; font-weight: 700; }
    .lx-brand img { width: 34px; height: 34px; border-radius: 8px; box-shadow: 0 0 12px rgba(0,204,0,0.4); }
    .lx-brand span { color: #00cc00; }
    .lx-theme-btn { background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.6); border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
    .lx-theme-btn:hover { background: rgba(0,204,0,0.15); border-color: rgba(0,204,0,0.35); color: #00cc00; }
    .lx-back-link { font-size: 0.82rem; color: #00cc00; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; }
    .lx-back-link:hover { color: #00ff44; }
    .lx-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 16px; position: relative; z-index: 2; }
    .lx-wrap { display: grid; grid-template-columns: 1fr 1fr; max-width: 1000px; width: 100%; background: rgba(10,18,10,0.85); border: 1px solid rgba(0,204,0,0.14); border-radius: 24px; overflow: hidden; box-shadow: 0 30px 80px rgba(0,0,0,0.55),0 0 0 1px rgba(0,204,0,0.06); animation: lx-in 0.45s ease both; }
    @keyframes lx-in { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
    .lx-left { background: linear-gradient(160deg,#0a1a0a 0%,#051005 60%,#021002 100%); padding: 56px 44px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden; border-right: 1px solid rgba(0,204,0,0.10); }
    .lx-left::before { content: ''; position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 30% 60%,rgba(0,204,0,0.09) 0%,transparent 60%); }
    .lx-left-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; position: relative; }
    .lx-left-logo img { width: 52px; height: 52px; border-radius: 14px; box-shadow: 0 0 22px rgba(0,204,0,0.45); }
    .lx-left-logo-text { font-size: 1.5rem; font-weight: 800; color: #fff; }
    .lx-left-logo-text span { color: #00cc00; }
    .lx-left h2 { font-size: clamp(1.65rem,2.5vw,2.1rem); font-weight: 800; color: #fff; line-height: 1.22; margin-bottom: 14px; position: relative; }
    .lx-left h2 em { font-style: normal; color: #00cc00; }
    .lx-left p { font-size: 0.9rem; color: rgba(255,255,255,0.5); line-height: 1.75; margin-bottom: 34px; position: relative; }
    .lx-left-features { list-style: none; padding: 0; margin: 0; position: relative; }
    .lx-left-features li { display: flex; align-items: center; gap: 12px; font-size: 0.83rem; color: rgba(255,255,255,0.65); margin-bottom: 14px; }
    .lx-left-features li i { width: 30px; height: 30px; border-radius: 50%; background: rgba(0,204,0,0.12); border: 1px solid rgba(0,204,0,0.25); color: #00cc00; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem; }
    .lx-left-coins { display: flex; gap: 10px; margin-top: 36px; flex-wrap: wrap; position: relative; }
    .lx-coin-pill { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 6px 14px; font-size: 0.75rem; color: rgba(255,255,255,0.6); }
    .lx-coin-pill .lx-ci { font-size: 1rem; font-weight: 800; }
    .lx-ci-btc{color:#f7931a}.lx-ci-eth{color:#627eea}.lx-ci-usdt{color:#26a17b}.lx-ci-ltc{color:#b3b3b3}.lx-ci-xrp{color:#00aae4}
    .lx-right { padding: 52px 44px; display: flex; flex-direction: column; justify-content: center; }
    .lx-right-head { margin-bottom: 32px; }
    .lx-right-head h1 { font-size: 1.75rem; font-weight: 800; color: #fff; margin-bottom: 6px; }
    .lx-right-head p { font-size: 0.875rem; color: rgba(255,255,255,0.45); }
    .lx-right-head p a { color: #00cc00; text-decoration: none; font-weight: 600; }
    .lx-right-head p a:hover { color: #00ff44; }
    .lx-label { font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.65); margin-bottom: 7px; display: block; letter-spacing: 0.03em; text-transform: uppercase; }
    .lx-field { background: rgba(255,255,255,0.05) !important; border: 1.5px solid rgba(255,255,255,0.1) !important; border-radius: 12px !important; color: #fff !important; font-size: 0.92rem; padding: 12px 16px !important; transition: all 0.22s ease; width: 100%; font-family: ''Poppins'',sans-serif; }
    .lx-field::placeholder { color: rgba(255,255,255,0.28); }
    .lx-field:focus { background: rgba(0,204,0,0.05) !important; border-color: rgba(0,204,0,0.55) !important; box-shadow: 0 0 0 3px rgba(0,204,0,0.1) !important; outline: none; color: #fff !important; }
    .lx-field.is-invalid { border-color: rgba(220,53,69,0.6) !important; box-shadow: 0 0 0 3px rgba(220,53,69,0.1) !important; }
    .lx-pw-wrap { position: relative; }
    .lx-pw-toggle { position: absolute; top: 50%; right: 14px; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: rgba(255,255,255,0.35); font-size: 0.9rem; transition: color 0.2s; }
    .lx-pw-toggle:hover { color: #00cc00; }
    .lx-row-extra { display: flex; align-items: center; justify-content: space-between; margin: 10px 0 24px; }
    .lx-check-label { display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: rgba(255,255,255,0.5); cursor: pointer; }
    .lx-check { accent-color: #00cc00; cursor: pointer; width: 15px; height: 15px; }
    .lx-forgot { font-size: 0.82rem; color: #00cc00; text-decoration: none; font-weight: 500; }
    .lx-forgot:hover { color: #00ff44; text-decoration: underline; }
    .lx-submit { width: 100%; background: linear-gradient(135deg,#00cc00,#008f11); color: #fff; border: none; border-radius: 12px; padding: 13px; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.25s ease; box-shadow: 0 6px 22px rgba(0,204,0,0.3); display: flex; align-items: center; justify-content: center; gap: 8px; font-family: ''Poppins'',sans-serif; }
    .lx-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(0,204,0,0.42); }
    .lx-submit:active { transform: translateY(0); }
    .lx-submit .lx-spin { display: none; }
    .lx-submit.loading .lx-spin { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }
    .lx-submit.loading .lx-txt { display: none; }
    @keyframes spin { to{transform:rotate(360deg)} }
    .lx-divider { display: flex; align-items: center; gap: 12px; margin: 24px 0; font-size: 0.78rem; color: rgba(255,255,255,0.25); }
    .lx-divider::before,.lx-divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.1); }
    .lx-socials { display: flex; gap: 10px; }
    .lx-social-btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 7px; background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px; font-size: 0.8rem; color: rgba(255,255,255,0.6); text-decoration: none; font-weight: 500; transition: all 0.2s ease; }
    .lx-social-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.22); color: #fff; }
    .lx-tg-login-wrap { display:flex; justify-content:center; margin:0 0 4px; min-height:46px; }
    .lx-tg-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#fca5a5;
        border-radius:10px; padding:11px 16px; font-size:0.82rem; margin-bottom:18px;
        display:flex; align-items:flex-start; gap:8px; }
    .lx-alert-success { background: rgba(0,204,0,0.12); border: 1px solid rgba(0,204,0,0.3); color: #86efac; border-radius: 10px; padding: 12px 16px; font-size: 0.85rem; margin-bottom: 22px; }
    .lx-footer { position: relative; z-index: 2; background: rgba(6,12,6,0.9); border-top: 1px solid rgba(0,204,0,0.1); padding: 28px 0; }
    .lx-footer-inner { max-width: 1000px; margin: 0 auto; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
    .lx-footer-copy { font-size: 0.78rem; color: rgba(255,255,255,0.3); }
    .lx-footer-links { display: flex; gap: 20px; flex-wrap: wrap; }
    .lx-footer-links a { font-size: 0.78rem; color: rgba(255,255,255,0.35); text-decoration: none; transition: color 0.2s; }
    .lx-footer-links a:hover { color: #00cc00; }
    [data-bs-theme="light"] body { background: #f0f8f0; color: #111; }
    [data-bs-theme="light"] .lx-bg { background-image: linear-gradient(rgba(0,153,0,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,153,0,0.04) 1px,transparent 1px); }
    [data-bs-theme="light"] .lx-topbar { background: rgba(255,255,255,0.97); border-color: rgba(0,153,0,0.15); }
    [data-bs-theme="light"] .lx-brand { color: #111; }
    [data-bs-theme="light"] .lx-theme-btn { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.1); color: #555; }
    [data-bs-theme="light"] .lx-wrap { background: #fff; border-color: rgba(0,153,0,0.15); box-shadow: 0 20px 60px rgba(0,0,0,0.1); }
    [data-bs-theme="light"] .lx-left { background: linear-gradient(160deg,#f0fff0 0%,#e8f8e8 100%); border-right-color: rgba(0,153,0,0.12); }
    [data-bs-theme="light"] .lx-left::before { background: radial-gradient(circle at 30% 60%,rgba(0,153,0,0.07) 0%,transparent 60%); }
    [data-bs-theme="light"] .lx-left-logo-text,[data-bs-theme="light"] .lx-left h2 { color: #0a1a0a; }
    [data-bs-theme="light"] .lx-left p,[data-bs-theme="light"] .lx-left-features li { color: rgba(0,0,0,0.55); }
    [data-bs-theme="light"] .lx-coin-pill { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.09); color: #555; }
    [data-bs-theme="light"] .lx-right-head h1 { color: #0a1a0a; }
    [data-bs-theme="light"] .lx-right-head p { color: rgba(0,0,0,0.45); }
    [data-bs-theme="light"] .lx-label { color: rgba(0,0,0,0.6); }
    [data-bs-theme="light"] .lx-field { background: rgba(0,0,0,0.03) !important; border-color: rgba(0,0,0,0.12) !important; color: #111 !important; }
    [data-bs-theme="light"] .lx-field::placeholder { color: rgba(0,0,0,0.3); }
    [data-bs-theme="light"] .lx-field:focus { background: rgba(0,153,0,0.04) !important; border-color: rgba(0,153,0,0.5) !important; }
    [data-bs-theme="light"] .lx-pw-toggle { color: rgba(0,0,0,0.3); }
    [data-bs-theme="light"] .lx-check-label { color: rgba(0,0,0,0.5); }
    [data-bs-theme="light"] .lx-divider { color: rgba(0,0,0,0.2); }
    [data-bs-theme="light"] .lx-divider::before,[data-bs-theme="light"] .lx-divider::after { background: rgba(0,0,0,0.1); }
    [data-bs-theme="light"] .lx-social-btn { background: rgba(0,0,0,0.03); border-color: rgba(0,0,0,0.1); color: #555; }
    [data-bs-theme="light"] .lx-social-btn:hover { background: rgba(0,0,0,0.07); color: #111; }
    [data-bs-theme="light"] .lx-footer { background: rgba(240,248,240,0.97); border-color: rgba(0,153,0,0.12); }
    [data-bs-theme="light"] .lx-footer-copy { color: rgba(0,0,0,0.3); }
    [data-bs-theme="light"] .lx-footer-links a { color: rgba(0,0,0,0.35); }
    @media (max-width:768px) { .lx-wrap{grid-template-columns:1fr} .lx-left{display:none} .lx-right{padding:38px 28px} }
    @media (max-width:480px) { .lx-right{padding:28px 20px} .lx-socials{flex-direction:column} }
  </style>
</head>
<body>
  <div class="lx-bg"></div>
  <div class="lx-orb1"></div>
  <div class="lx-orb2"></div>

  <header class="lx-topbar">
    <div class="container d-flex align-items-center justify-content-between">
      <a class="lx-brand" href="<?php echo e(url('/')); ?>">
        <img src="<?php echo e(asset('Assests/favicon.png')); ?>" alt="KayXchange">
        Kay<span>Xchange</span>
      </a>
      <div class="d-flex align-items-center gap-3">
        <a href="<?php echo e(url('/home')); ?>" class="lx-back-link"><i class="bi bi-arrow-left"></i>Back to Home</a>
        <button id="lx-theme-btn" class="lx-theme-btn" title="Toggle theme">
          <i class="bi bi-moon-stars-fill" id="lx-theme-icon"></i>
        </button>
      </div>
    </div>
  </header>

  <main class="lx-main">
    <div class="lx-wrap">

      <div class="lx-left">
        <div class="lx-left-logo">
          <img src="<?php echo e(asset('Assests/favicon.png')); ?>" alt="KayXchange">
          <div class="lx-left-logo-text">Kay<span>Xchange</span></div>
        </div>
        <h2>Welcome<br>back, <em>trader.</em></h2>
        <p>Sign in to access your dashboard, check live rates, and manage your crypto trades instantly.</p>
        <ul class="lx-left-features">
          <li><i class="bi bi-shield-check-fill"></i>Secure &amp; encrypted transactions</li>
          <li><i class="bi bi-lightning-charge-fill"></i>Instant NGN settlements</li>
          <li><i class="bi bi-graph-up-arrow"></i>Live crypto rates — BTC, ETH, USDT &amp; more</li>
          <li><i class="bi bi-headset"></i>24/7 customer support</li>
          <li><i class="bi bi-person-check-fill"></i>No KYC required to start</li>
        </ul>
        <div class="lx-left-coins">
          <span class="lx-coin-pill"><span class="lx-ci lx-ci-btc">&#8383;</span>Bitcoin</span>
          <span class="lx-coin-pill"><span class="lx-ci lx-ci-eth">&#926;</span>Ethereum</span>
          <span class="lx-coin-pill"><span class="lx-ci lx-ci-usdt">&#8366;</span>USDT</span>
          <span class="lx-coin-pill"><span class="lx-ci lx-ci-ltc">&#321;</span>Litecoin</span>
          <span class="lx-coin-pill"><span class="lx-ci lx-ci-xrp">&#10005;</span>XRP</span>
        </div>
      </div>

      <div class="lx-right">
        <div class="lx-right-head">
          <h1>Sign in</h1>
          <p>New here? <a href="<?php echo e(route('register')); ?>">Create an account &rarr;</a></p>
        </div>

        <?php if(session('status')): ?>
          <div class="lx-alert-success">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('status')); ?>

          </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login')); ?>" id="lx-login-form" novalidate>
          <?php echo csrf_field(); ?>

          <div class="mb-4">
            <label for="email" class="lx-label">Email address</label>
            <input id="email" name="email" type="email"
              class="lx-field <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
              value="<?php echo e(old('email')); ?>"
              placeholder="you@example.com"
              required autofocus autocomplete="username">
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="lx-error"><i class="bi bi-exclamation-circle"></i><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="mb-2">
            <label for="password" class="lx-label">Password</label>
            <div class="lx-pw-wrap">
              <input id="password" name="password" type="password"
                class="lx-field <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                required autocomplete="current-password"
                style="padding-right:44px;">
              <button type="button" class="lx-pw-toggle" id="lx-pw-toggle" aria-label="Toggle password visibility">
                <i class="bi bi-eye" id="lx-eye-icon"></i>
              </button>
            </div>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="lx-error"><i class="bi bi-exclamation-circle"></i><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <div class="lx-row-extra">
            <label class="lx-check-label">
              <input type="checkbox" name="remember" id="remember_me" class="lx-check">
              Remember me
            </label>
            <?php if(Route::has('password.request')): ?>
              <a href="<?php echo e(route('password.request')); ?>" class="lx-forgot">Forgot password?</a>
            <?php endif; ?>
          </div>

          <button type="submit" class="lx-submit" id="lx-submit-btn">
            <span class="lx-txt">Sign in &nbsp;<i class="bi bi-arrow-right-circle-fill"></i></span>
            <span class="lx-spin"></span>
          </button>
        </form>

        
        <?php $__errorArgs = ['telegram'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div class="lx-tg-error mt-3">
            <i class="bi bi-telegram" style="font-size:1rem;flex-shrink:0"></i>
            <?php echo e($message); ?>

          </div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <div class="lx-divider">or sign in with</div>

        
        <div class="lx-tg-login-wrap" id="tg-widget-wrap">
          <script
            async
            src="https://telegram.org/js/telegram-widget.js?22"
            data-telegram-login="<?php echo e(config('services.telegram.bot_username', 'TradewithkayxchangeBOT')); ?>"
            data-size="large"
            data-radius="10"
            data-onauth="onTelegramAuth(user)"
            data-request-access="write"
            onerror="telegramWidgetError()">
          </script>
        </div>
        <div id="tg-domain-error" style="display:none;margin-top:8px;padding:10px 14px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:9px;font-size:0.78rem;color:#f87171;">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          <strong>Telegram login unavailable</strong> — bot domain not verified. Admin: open <strong>@BotFather</strong> → /mybots → your bot → <strong>Bot Settings → Domain</strong> → set to <code style="color:#fca5a5">tradewithkay.com</code>.
        </div>

        
        <form id="tg-auth-form" method="POST" action="<?php echo e(route('telegram.login.callback')); ?>" style="display:none">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="id"         id="tg_f_id">
          <input type="hidden" name="first_name" id="tg_f_first_name">
          <input type="hidden" name="last_name"  id="tg_f_last_name">
          <input type="hidden" name="username"   id="tg_f_username">
          <input type="hidden" name="photo_url"  id="tg_f_photo_url">
          <input type="hidden" name="auth_date"  id="tg_f_auth_date">
          <input type="hidden" name="hash"       id="tg_f_hash">
        </form>

        <div class="lx-socials">
          <a href="https://wa.me/+2349016740523" target="_blank" class="lx-social-btn">
            <i class="bi bi-whatsapp" style="color:#25d366"></i> WhatsApp Trade
          </a>
          <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" class="lx-social-btn">
            <i class="bi bi-telegram" style="color:#0088cc"></i> Telegram Bot
          </a>
        </div>
      </div>

    </div>
  </main>

  <footer class="lx-footer">
    <div class="lx-footer-inner">
      <p class="lx-footer-copy">&copy; <?php echo e(date('Y')); ?> KayXchange. All rights reserved.</p>
      <nav class="lx-footer-links">
        <a href="<?php echo e(url('/rate')); ?>">Rates</a>
        <a href="<?php echo e(url('/blog')); ?>">Blog</a>
        <a href="<?php echo e(url('/faqs')); ?>">FAQs</a>
        <a href="<?php echo e(url('/about')); ?>">About</a>
        <a href="<?php echo e(url('/privacy')); ?>">Privacy</a>
        <a href="<?php echo e(url('/terms')); ?>">Terms</a>
      </nav>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script>
    (function(){
      var html=document.documentElement;
      var btn=document.getElementById('lx-theme-btn');
      var icon=document.getElementById('lx-theme-icon');
      var saved=localStorage.getItem('theme')||'dark';
      html.setAttribute('data-bs-theme',saved);
      if(icon) icon.className=saved==='dark'?'bi bi-moon-stars-fill':'bi bi-sun-fill';
      if(btn){btn.addEventListener('click',function(){
        var cur=html.getAttribute('data-bs-theme');
        var next=cur==='dark'?'light':'dark';
        html.setAttribute('data-bs-theme',next);
        localStorage.setItem('theme',next);
        if(icon) icon.className=next==='dark'?'bi bi-moon-stars-fill':'bi bi-sun-fill';
      });}
    })();
    document.addEventListener('DOMContentLoaded',function(){
      var t=document.getElementById('lx-pw-toggle');
      var f=document.getElementById('password');
      var i=document.getElementById('lx-eye-icon');
      if(t&&f&&i){t.addEventListener('click',function(){
        var show=f.type==='password';
        f.type=show?'text':'password';
        i.className=show?'bi bi-eye-slash':'bi bi-eye';
      });}
      var form=document.getElementById('lx-login-form');
      var sub=document.getElementById('lx-submit-btn');
      if(form&&sub){form.addEventListener('submit',function(){
        sub.classList.add('loading');sub.disabled=true;
      });}
    });
    toastr.options={positionClass:'toast-top-right',timeOut:5000,extendedTimeOut:1000,showMethod:'slideDown',hideMethod:'slideUp',preventDuplicates:true};
    <?php if(session('status')): ?> toastr.success("<?php echo e(session('status')); ?>"); <?php endif; ?>
    <?php if(session('error')): ?> toastr.error("<?php echo e(session('error')); ?>"); <?php endif; ?>

    function onTelegramAuth(user) {
      // Fill the hidden form and submit it
      document.getElementById('tg_f_id').value         = user.id         || '';
      document.getElementById('tg_f_first_name').value = user.first_name || '';
      document.getElementById('tg_f_last_name').value  = user.last_name  || '';
      document.getElementById('tg_f_username').value   = user.username   || '';
      document.getElementById('tg_f_photo_url').value  = user.photo_url  || '';
      document.getElementById('tg_f_auth_date').value  = user.auth_date  || '';
      document.getElementById('tg_f_hash').value       = user.hash       || '';
      document.getElementById('tg-auth-form').submit();
    }

    function telegramWidgetError() {
      document.getElementById('tg-domain-error').style.display = 'block';
      document.getElementById('tg-widget-wrap').style.display  = 'none';
    }

    // Telegram widget signals "bot domain invalid" via an iframe with specific content.
    // Poll until the widget iframe appears, then check its title / src for the error.
    (function detectTelegramDomainError() {
      var attempts = 0;
      var timer = setInterval(function () {
        attempts++;
        var wrap  = document.getElementById('tg-widget-wrap');
        var frame = wrap ? wrap.querySelector('iframe') : null;
        if (frame) {
          // The "Bot domain invalid" iframe loads a page that contains that text
          try {
            var title = frame.contentDocument && frame.contentDocument.title;
            if (title && title.toLowerCase().includes('invalid')) {
              telegramWidgetError(); clearInterval(timer); return;
            }
          } catch(e) { /* cross-origin — can't read */ }
          // If the iframe src contains "error" or no button rendered after 5s, show warning
          if (frame.src && frame.src.includes('error')) {
            telegramWidgetError(); clearInterval(timer); return;
          }
          clearInterval(timer); // widget loaded fine
        }
        if (attempts > 30) clearInterval(timer); // give up after 6s
      }, 200);
    })();
  </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/auth/login.blade.php ENDPATH**/ ?>