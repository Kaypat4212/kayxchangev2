<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Account - KayXchange</title>
  <link rel="icon" type="image/png" href="<?php echo e(asset('Assests/favicon.png')); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo e(asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')); ?>" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Poppins', sans-serif; }
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
    .lx-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 36px 16px; position: relative; z-index: 2; }
    .lx-wrap { display: grid; grid-template-columns: 1fr 1fr; max-width: 1000px; width: 100%; background: rgba(10,18,10,0.85); border: 1px solid rgba(0,204,0,0.14); border-radius: 24px; overflow: hidden; box-shadow: 0 30px 80px rgba(0,0,0,0.55),0 0 0 1px rgba(0,204,0,0.06); animation: lx-in 0.45s ease both; }
    @keyframes lx-in { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
    .lx-left { background: linear-gradient(160deg,#0a1a0a 0%,#051005 60%,#021002 100%); padding: 48px 40px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden; border-right: 1px solid rgba(0,204,0,0.10); }
    .lx-left::before { content: ''; position: absolute; inset: 0; pointer-events: none; background: radial-gradient(circle at 30% 60%,rgba(0,204,0,0.09) 0%,transparent 60%); }
    .lx-left-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 32px; position: relative; }
    .lx-left-logo img { width: 48px; height: 48px; border-radius: 14px; box-shadow: 0 0 22px rgba(0,204,0,0.45); }
    .lx-left-logo-text { font-size: 1.4rem; font-weight: 800; color: #fff; }
    .lx-left-logo-text span { color: #00cc00; }
    .lx-left h2 { font-size: clamp(1.5rem,2.5vw,2rem); font-weight: 800; color: #fff; line-height: 1.22; margin-bottom: 12px; position: relative; }
    .lx-left h2 em { font-style: normal; color: #00cc00; }
    .lx-left p { font-size: 0.875rem; color: rgba(255,255,255,0.5); line-height: 1.75; margin-bottom: 28px; position: relative; }
    .lx-left-features { list-style: none; padding: 0; margin: 0; position: relative; }
    .lx-left-features li { display: flex; align-items: center; gap: 12px; font-size: 0.82rem; color: rgba(255,255,255,0.65); margin-bottom: 12px; }
    .lx-left-features li i { width: 28px; height: 28px; border-radius: 50%; background: rgba(0,204,0,0.12); border: 1px solid rgba(0,204,0,0.25); color: #00cc00; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.78rem; }
    .lx-left-coins { display: flex; gap: 8px; margin-top: 26px; flex-wrap: wrap; position: relative; }
    .lx-coin-pill { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 5px 12px; font-size: 0.73rem; color: rgba(255,255,255,0.6); }
    .lx-coin-pill .lx-ci { font-size: 0.95rem; font-weight: 800; }
    .lx-ci-btc{color:#f7931a}.lx-ci-eth{color:#627eea}.lx-ci-usdt{color:#26a17b}.lx-ci-ltc{color:#b3b3b3}.lx-ci-xrp{color:#00aae4}
    .lx-right { padding: 40px 40px; display: flex; flex-direction: column; justify-content: center; overflow-y: auto; }
    .lx-right-head { margin-bottom: 22px; }
    .lx-right-head h1 { font-size: 1.65rem; font-weight: 800; color: #fff; margin-bottom: 5px; }
    .lx-right-head p { font-size: 0.875rem; color: rgba(255,255,255,0.45); }
    .lx-right-head p a { color: #00cc00; text-decoration: none; font-weight: 600; }
    .lx-right-head p a:hover { color: #00ff44; }
    .lx-label { font-size: 0.78rem; font-weight: 600; color: rgba(255,255,255,0.65); margin-bottom: 6px; display: block; letter-spacing: 0.03em; text-transform: uppercase; }
    .lx-field { background: rgba(255,255,255,0.05) !important; border: 1.5px solid rgba(255,255,255,0.1) !important; border-radius: 12px !important; color: #fff !important; font-size: 0.88rem; padding: 10px 14px !important; transition: all 0.22s ease; width: 100%; font-family: 'Poppins',sans-serif; }
    .lx-field::placeholder { color: rgba(255,255,255,0.28); }
    .lx-field:focus { background: rgba(0,204,0,0.05) !important; border-color: rgba(0,204,0,0.55) !important; box-shadow: 0 0 0 3px rgba(0,204,0,0.1) !important; outline: none; color: #fff !important; }
    .lx-field.is-invalid { border-color: rgba(220,53,69,0.6) !important; box-shadow: 0 0 0 3px rgba(220,53,69,0.1) !important; }
    .lx-pw-wrap { position: relative; }
    .lx-pw-toggle { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: rgba(255,255,255,0.35); font-size: 0.85rem; transition: color 0.2s; line-height: 1; }
    .lx-pw-toggle:hover { color: #00cc00; }
    .lx-pw-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
    .lx-submit { width: 100%; background: linear-gradient(135deg,#00cc00,#008f11); color: #fff; border: none; border-radius: 12px; padding: 12px; font-size: 0.93rem; font-weight: 700; cursor: pointer; transition: all 0.25s ease; box-shadow: 0 6px 22px rgba(0,204,0,0.3); display: flex; align-items: center; justify-content: center; gap: 8px; font-family: 'Poppins',sans-serif; }
    .lx-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(0,204,0,0.42); }
    .lx-submit:active { transform: translateY(0); }
    .lx-submit .lx-spin { display: none; }
    .lx-submit.loading .lx-spin { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }
    .lx-submit.loading .lx-txt { display: none; }
    @keyframes spin { to{transform:rotate(360deg)} }
    .lx-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; font-size: 0.78rem; color: rgba(255,255,255,0.25); }
    .lx-divider::before,.lx-divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.1); }
    .lx-socials { display: flex; gap: 10px; }
    .lx-social-btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 7px; background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 9px; font-size: 0.78rem; color: rgba(255,255,255,0.6); text-decoration: none; font-weight: 500; transition: all 0.2s ease; }
    .lx-social-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.22); color: #fff; }
    .lx-error { font-size: 0.76rem; color: #f87171; margin-top: 4px; display: flex; align-items: center; gap: 5px; }
    .lx-alert-success { background: rgba(0,204,0,0.12); border: 1px solid rgba(0,204,0,0.3); color: #86efac; border-radius: 10px; padding: 10px 14px; font-size: 0.83rem; margin-bottom: 18px; }
    .lx-opt-label { font-size: 0.72rem; color: rgba(255,255,255,0.3); font-weight: 400; text-transform: none; margin-left: 4px; }
    .lx-footer { position: relative; z-index: 2; background: rgba(6,12,6,0.9); border-top: 1px solid rgba(0,204,0,0.1); padding: 24px 0; }
    .lx-footer-inner { max-width: 1000px; margin: 0 auto; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; }
    .lx-footer-copy { font-size: 0.78rem; color: rgba(255,255,255,0.3); }
    .lx-footer-links { display: flex; gap: 18px; flex-wrap: wrap; }
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
    [data-bs-theme="light"] .lx-divider { color: rgba(0,0,0,0.2); }
    [data-bs-theme="light"] .lx-divider::before,[data-bs-theme="light"] .lx-divider::after { background: rgba(0,0,0,0.1); }
    [data-bs-theme="light"] .lx-social-btn { background: rgba(0,0,0,0.03); border-color: rgba(0,0,0,0.1); color: #555; }
    [data-bs-theme="light"] .lx-social-btn:hover { background: rgba(0,0,0,0.07); color: #111; }
    [data-bs-theme="light"] .lx-footer { background: rgba(240,248,240,0.97); border-color: rgba(0,153,0,0.12); }
    [data-bs-theme="light"] .lx-footer-copy { color: rgba(0,0,0,0.3); }
    [data-bs-theme="light"] .lx-footer-links a { color: rgba(0,0,0,0.35); }
    [data-bs-theme="light"] .lx-opt-label { color: rgba(0,0,0,0.3); }
    @media (max-width:768px) { .lx-wrap{grid-template-columns:1fr} .lx-left{display:none} .lx-right{padding:34px 24px} .lx-pw-row{grid-template-columns:1fr} }
    @media (max-width:480px) { .lx-right{padding:24px 18px} .lx-socials{flex-direction:column} }
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
        <h2>Start trading<br><em>today.</em></h2>
        <p>Join thousands of traders buying and selling crypto in Nigeria with instant NGN payouts and the best rates.</p>
        <ul class="lx-left-features">
          <li><i class="bi bi-people-fill"></i>3,000+ active traders nationwide</li>
          <li><i class="bi bi-clock-fill"></i>Set up your account in under 2 minutes</li>
          <li><i class="bi bi-lightning-charge-fill"></i>Instant NGN bank settlements</li>
          <li><i class="bi bi-percent"></i>Best rates — BTC, ETH, USDT &amp; more</li>
          <li><i class="bi bi-person-check-fill"></i>No KYC required to start trading</li>
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
          <h1>Create account</h1>
          <p>Already have an account? <a href="<?php echo e(route('login')); ?>">Sign in &rarr;</a></p>
        </div>

        <?php if(session('status')): ?>
          <div class="lx-alert-success">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('status')); ?>

          </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('register')); ?>" id="lx-reg-form" novalidate>
          <?php echo csrf_field(); ?>

          <div class="mb-3">
            <label for="name" class="lx-label">Full name</label>
            <input id="name" name="name" type="text"
              class="lx-field <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
              value="<?php echo e(old('name')); ?>"
              placeholder="e.g. John Doe"
              required autofocus autocomplete="name">
            <?php $__errorArgs = ['name'];
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

          <div class="mb-3">
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
              required autocomplete="email">
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

          <div class="lx-pw-row">
            <div>
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
                  placeholder="Min. 8 characters"
                  required autocomplete="new-password"
                  style="padding-right:40px;">
                <button type="button" class="lx-pw-toggle" id="lx-pw1-btn" aria-label="Toggle">
                  <i class="bi bi-eye" id="lx-eye1"></i>
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
            <div>
              <label for="password_confirmation" class="lx-label">Confirm password</label>
              <div class="lx-pw-wrap">
                <input id="password_confirmation" name="password_confirmation" type="password"
                  class="lx-field"
                  placeholder="Repeat password"
                  required autocomplete="new-password"
                  style="padding-right:40px;">
                <button type="button" class="lx-pw-toggle" id="lx-pw2-btn" aria-label="Toggle">
                  <i class="bi bi-eye" id="lx-eye2"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label for="referral_code" class="lx-label">Referral code <span class="lx-opt-label">(optional)</span></label>
            <input id="referral_code" name="referral_code" type="text"
              class="lx-field <?php $__errorArgs = ['referral_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
              value="<?php echo e(request()->query('ref') ?? old('referral_code')); ?>"
              placeholder="Enter referral code if you have one">
            <?php $__errorArgs = ['referral_code'];
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

          <button type="submit" class="lx-submit" id="lx-submit-btn">
            <span class="lx-txt">Create account &nbsp;<i class="bi bi-person-plus-fill"></i></span>
            <span class="lx-spin"></span>
          </button>
        </form>

        <div class="lx-divider">or trade without an account</div>

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
      function pwToggle(btnId,fieldId,iconId){
        var b=document.getElementById(btnId),f=document.getElementById(fieldId),i=document.getElementById(iconId);
        if(b&&f&&i){b.addEventListener('click',function(){
          var s=f.type==='password';
          f.type=s?'text':'password';
          i.className=s?'bi bi-eye-slash':'bi bi-eye';
        });}
      }
      pwToggle('lx-pw1-btn','password','lx-eye1');
      pwToggle('lx-pw2-btn','password_confirmation','lx-eye2');
      var form=document.getElementById('lx-reg-form');
      var sub=document.getElementById('lx-submit-btn');
      if(form&&sub){form.addEventListener('submit',function(){sub.classList.add('loading');sub.disabled=true;});}
    });
    toastr.options={positionClass:'toast-top-right',timeOut:5000,extendedTimeOut:1000,showMethod:'slideDown',hideMethod:'slideUp',preventDuplicates:true};
    <?php if(session('status')): ?> toastr.success("<?php echo e(session('status')); ?>"); <?php endif; ?>
    <?php if(session('error')): ?> toastr.error("<?php echo e(session('error')); ?>"); <?php endif; ?>
  </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\auth\register.blade.php ENDPATH**/ ?>