<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Verify your visit · KayXchange</title>
    <link rel="icon" href="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" type="{{ $siteFaviconMime ?? 'image/png' }}">
    @if($mode === 'turnstile' && $providerConfigured)<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endif
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;background:radial-gradient(ellipse at 15% 5%,rgba(0,204,0,.13),transparent 38%),radial-gradient(ellipse at 100% 100%,rgba(52,133,255,.11),transparent 36%),#08110d;color:#eef7f0;font:15px/1.55 Inter,system-ui,-apple-system,Segoe UI,sans-serif}.verify{position:relative;isolation:isolate;overflow:hidden;width:min(100%,500px);padding:clamp(24px,6vw,38px);border:1px solid rgba(255,255,255,.1);border-radius:26px;background:linear-gradient(145deg,rgba(20,31,25,.98),rgba(10,18,14,.99));box-shadow:0 24px 80px rgba(0,0,0,.4)}.verify:before{content:'';position:absolute;z-index:-1;width:220px;height:220px;right:-120px;top:-140px;border-radius:50%;background:rgba(69,231,126,.17);filter:blur(18px)}.brand{display:flex;align-items:center;gap:11px;margin-bottom:29px;font-weight:800;letter-spacing:.01em}.brand-mark{display:grid;place-items:center;width:40px;height:40px;border:1px solid rgba(103,237,145,.24);border-radius:13px;background:rgba(60,214,111,.11);color:#76eda0;font-size:1.15rem}.brand-mark img{width:29px;height:29px;object-fit:contain}.eyebrow{display:flex;align-items:center;gap:8px;font-size:.7rem;color:#6ee795;font-weight:800;letter-spacing:.16em;text-transform:uppercase}.eyebrow:before{content:'';width:7px;height:7px;border-radius:50%;background:#64e88e;box-shadow:0 0 13px #64e88e}h1{margin:.55rem 0 .65rem;font-size:clamp(1.55rem,6vw,2rem);letter-spacing:-.04em;line-height:1.15}.intro{margin:0;color:#a9b9af;font-size:.9rem}.captcha-box{margin-top:24px;padding:18px;border:1px solid rgba(255,255,255,.1);border-radius:18px;background:linear-gradient(135deg,rgba(255,255,255,.045),rgba(255,255,255,.015))}.challenge-top{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:16px}.challenge-label{font-size:.68rem;color:#8ca194;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.challenge-step{padding:5px 8px;border:1px solid rgba(103,237,145,.16);border-radius:999px;background:rgba(51,195,94,.08);color:#82eaa4;font-size:.64rem;font-weight:750}.challenge-title{margin:0 0 6px;color:#f0f8f2;font-size:1.03rem;font-weight:800}.challenge-instruction{margin:0 0 14px;color:#9eaea3;font-size:.8rem}.answer{width:100%;height:48px;padding:0 13px;border:1px solid #34473b;border-radius:11px;background:#09110d;color:#f2fff5;font-size:1rem;outline:none}.answer:focus{border-color:#4fe07e;box-shadow:0 0 0 3px rgba(79,224,126,.12)}.submit{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;height:50px;margin-top:17px;border:1px solid rgba(255,255,255,.12);border-radius:12px;background:linear-gradient(110deg,#72ee98,#2dc466);color:#07140b;font-weight:850;cursor:pointer;box-shadow:0 9px 24px rgba(33,177,81,.15);transition:transform .16s,filter .16s}.submit:hover{transform:translateY(-1px);filter:brightness(1.04)}.submit:disabled{opacity:.45;cursor:not-allowed}.error{margin:14px 0 0;padding:10px 12px;border-radius:9px;background:rgba(248,113,113,.1);color:#ffaaaa;font-size:.82rem}.turnstile{min-height:66px;display:flex;justify-content:center}.help{margin:18px 0 0;color:#74857a;font-size:.73rem;text-align:center}.captcha-foot{display:flex;justify-content:center;align-items:center;gap:7px;margin-top:20px;color:#788a7e;font-size:.7rem}.sequence{display:flex;align-items:center;justify-content:center;gap:clamp(4px,2vw,10px);margin:15px 0}.sequence span{display:grid;place-items:center;min-width:43px;height:43px;padding:0 7px;border:1px solid rgba(255,255,255,.08);border-radius:11px;background:#0c1510;color:#dff5e4;font-size:.9rem;font-weight:800}.sequence span:last-child{border-color:rgba(104,235,143,.25);background:rgba(51,195,94,.1);color:#78e99c}.math-question{display:grid;place-items:center;min-height:76px;margin-bottom:14px;border:1px solid rgba(255,255,255,.07);border-radius:13px;background:radial-gradient(circle at center,rgba(63,219,112,.1),transparent 66%),#0b1410;color:#eaffee;font-size:1.75rem;font-weight:850;letter-spacing:.04em}.symbol-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:9px}.symbol-option{position:relative;cursor:pointer}.symbol-option input{position:absolute;opacity:0;pointer-events:none}.symbol-option .symbol-tile{display:grid;place-items:center;aspect-ratio:1;border:1px solid rgba(255,255,255,.09);border-radius:13px;background:rgba(6,14,9,.55);font-size:clamp(1.6rem,8vw,2rem);transition:.16s}.symbol-option:hover .symbol-tile{transform:translateY(-2px);border-color:rgba(107,236,145,.36)}.symbol-option input:checked+.symbol-tile{border-color:#68e994;background:rgba(64,205,105,.16);box-shadow:0 0 0 3px rgba(81,223,134,.1),inset 0 0 22px rgba(64,205,105,.08)}.symbol-option input:focus-visible+.symbol-tile{outline:2px solid #b3f4c7;outline-offset:2px}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
        @media(max-width:420px){body{padding:12px}.verify{border-radius:20px;padding:23px 18px}.captcha-box{padding:15px}.sequence span{min-width:37px;height:39px;font-size:.8rem}}
        @media(prefers-reduced-motion:reduce){*,*:before,*:after{scroll-behavior:auto!important;transition:none!important}}
    </style>
</head>
<body>
<main class="verify">
    <div class="brand"><span class="brand-mark"><img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt=""></span><span>KayXchange</span></div>
    <div class="eyebrow">Security check</div>
    <h1>One quick check, then you’re in.</h1>
    <p class="intro">This lightweight security step helps protect KayXchange and keeps automated traffic out of our visitor alerts.</p>
    @if(!empty($ipChanged))<p class="error" role="status">Your network address changed since your last check. Please verify again; this check will stay valid for this IP for 24 hours.</p>@endif

    <form method="POST" action="{{ route('home.captcha.verify') }}" class="captcha-box">
        @csrf
        @if($mode === 'custom')
            <div class="challenge-top"><span class="challenge-label">KayXchange human check</span><span class="challenge-step">CHALLENGE 1 OF 1</span></div>
            <h2 class="challenge-title">{{ $challenge['prompt'] ?? 'Complete the quick check' }}</h2>
            @if(($challenge['type'] ?? '') === 'math')
                <p class="challenge-instruction">Enter the answer to continue.</p>
                <div class="math-question" aria-label="{{ $challenge['question'] }}">{{ $challenge['question'] }}</div>
                <input class="answer" id="captcha_answer" name="captcha_answer" type="text" inputmode="numeric" autocomplete="off" placeholder="Your answer" required autofocus aria-label="Answer to the math challenge">
            @elseif(($challenge['type'] ?? '') === 'symbol')
                <p class="challenge-instruction">{{ $challenge['instruction'] }}</p>
                <div class="symbol-grid" role="radiogroup" aria-label="Choose the matching symbol">
                    @foreach($challenge['choices'] as $index => $symbol)
                        <label class="symbol-option"><input type="radio" name="captcha_answer" value="{{ $index }}" required><span class="symbol-tile" aria-hidden="true">{{ $symbol }}</span><span class="sr-only">Symbol choice {{ $index + 1 }}</span></label>
                    @endforeach
                </div>
            @else
                <p class="challenge-instruction">Choose the number that continues the pattern.</p>
                <div class="sequence" aria-label="Number sequence: {{ implode(', ', $challenge['sequence'] ?? []) }}, question mark">
                    @foreach($challenge['sequence'] ?? [] as $number)<span>{{ $number }}</span>@endforeach<span aria-hidden="true">?</span>
                </div>
                <input class="answer" id="captcha_answer" name="captcha_answer" type="text" inputmode="numeric" autocomplete="off" placeholder="Next number" required autofocus aria-label="Next number in the sequence">
            @endif
        @elseif($providerConfigured)
            <div class="turnstile"><div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-theme="dark" data-callback="kayxTurnstileSolved" data-expired-callback="kayxTurnstileReset" data-error-callback="kayxTurnstileReset"></div></div>
        @else
            <p class="error" style="margin:0">Cloudflare verification has not been configured yet. Please contact support while the site administrator completes setup.</p>
        @endif
        @if($mode === 'turnstile' && $providerConfigured)
            <button class="submit" type="submit" disabled id="verifySubmit">Verify and continue <span aria-hidden="true">→</span></button>
        @elseif($mode === 'custom')
            <button class="submit" type="submit">Verify and continue <span aria-hidden="true">→</span></button>
        @endif
    </form>
    @error('captcha_answer')<p class="error">{{ $message }}</p>@enderror
    @error('captcha')<p class="error">{{ $message }}</p>@enderror
    <div class="captcha-foot"><span aria-hidden="true">◆</span> Secure check · Your answer is not saved to your account</div>
</main>
@if($mode === 'turnstile' && $providerConfigured)
<script>
window.kayxTurnstileSolved=()=>{const button=document.getElementById('verifySubmit');if(button)button.disabled=false};
window.kayxTurnstileReset=()=>{const button=document.getElementById('verifySubmit');if(button)button.disabled=true};
</script>
@endif
</body>
</html>
