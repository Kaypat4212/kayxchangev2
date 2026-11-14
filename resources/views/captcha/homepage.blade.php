<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Verify your visit · KayXchange</title>
    <link rel="icon" href="{{ asset('Assests/favicon.png') }}" type="image/png">
    @if($mode === 'turnstile' && $providerConfigured)<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endif
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;background:radial-gradient(ellipse at 15% 5%,rgba(0,204,0,.13),transparent 38%),#08110d;color:#eef7f0;font:15px/1.55 Inter,system-ui,-apple-system,Segoe UI,sans-serif}.verify{width:min(100%,460px);padding:clamp(24px,6vw,38px);border:1px solid rgba(255,255,255,.1);border-radius:24px;background:linear-gradient(145deg,#111d17,#0d1512);box-shadow:0 24px 80px rgba(0,0,0,.4)}.brand{display:flex;align-items:center;gap:11px;margin-bottom:31px;font-weight:800;letter-spacing:.01em}.brand img{width:38px;height:38px;object-fit:contain}.eyebrow{font-size:.7rem;color:#6ee795;font-weight:800;letter-spacing:.16em;text-transform:uppercase}h1{margin:.55rem 0 .65rem;font-size:clamp(1.55rem,6vw,2rem);letter-spacing:-.04em;line-height:1.15}.intro{margin:0;color:#a9b9af;font-size:.9rem}.captcha-box{margin-top:24px;padding:18px;border:1px solid rgba(255,255,255,.1);border-radius:15px;background:rgba(0,0,0,.18)}label{display:block;margin-bottom:8px;font-size:.8rem;font-weight:750}.answer{width:100%;height:48px;padding:0 13px;border:1px solid #34473b;border-radius:10px;background:#09110d;color:#f2fff5;font-size:1rem;outline:none}.answer:focus{border-color:#4fe07e;box-shadow:0 0 0 3px rgba(79,224,126,.12)}.submit{width:100%;height:49px;margin-top:15px;border:0;border-radius:10px;background:linear-gradient(110deg,#62ed8b,#29bd5c);color:#07140b;font-weight:850;cursor:pointer}.submit:disabled{opacity:.45;cursor:not-allowed}.error{margin:14px 0 0;padding:10px 12px;border-radius:9px;background:rgba(248,113,113,.1);color:#ffaaaa;font-size:.82rem}.turnstile{min-height:66px;display:flex;justify-content:center}.help{margin:18px 0 0;color:#74857a;font-size:.73rem;text-align:center}.challenge-question{margin:2px 0 13px;color:#e5f3e8;font-size:1.1rem;font-weight:800}
    </style>
</head>
<body>
<main class="verify">
    <div class="brand"><img src="{{ asset('Assests/favicon.png') }}" alt=""><span>KayXchange</span></div>
    <div class="eyebrow">Security check</div>
    <h1>Confirm you’re visiting</h1>
    <p class="intro">Complete this quick check to continue to the KayXchange homepage. It also helps us keep automated traffic out of our visitor alerts.</p>

    <form method="POST" action="{{ route('home.captcha.verify') }}" class="captcha-box">
        @csrf
        @if($mode === 'custom')
            <label for="captcha_answer">Human check</label>
            <div class="challenge-question">{{ $question }}</div>
            <input class="answer" id="captcha_answer" name="captcha_answer" type="text" inputmode="numeric" autocomplete="off" required autofocus aria-describedby="captcha-help">
            <div id="captcha-help" class="help" style="margin:10px 0 0;text-align:left">Enter the answer using numbers.</div>
        @elseif($providerConfigured)
            <div class="turnstile"><div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-theme="dark" data-callback="kayxTurnstileSolved" data-expired-callback="kayxTurnstileReset" data-error-callback="kayxTurnstileReset"></div></div>
        @else
            <p class="error" style="margin:0">Cloudflare verification has not been configured yet. Please contact support while the site administrator completes setup.</p>
        @endif
        @error('captcha_answer')<p class="error">{{ $message }}</p>@enderror
        @error('captcha')<p class="error">{{ $message }}</p>@enderror
        @if($mode === 'custom' || $providerConfigured)
            <button class="submit" type="submit" @if($mode === 'turnstile') disabled id="verifySubmit" @endif>Continue securely <span aria-hidden="true">→</span></button>
        @endif
    </form>
    <p class="help">Your answer is checked securely and is not stored with your account data.</p>
</main>
@if($mode === 'turnstile' && $providerConfigured)
<script>
window.kayxTurnstileSolved=()=>{const button=document.getElementById('verifySubmit');if(button)button.disabled=false};
window.kayxTurnstileReset=()=>{const button=document.getElementById('verifySubmit');if(button)button.disabled=true};
</script>
@endif
</body>
</html>
