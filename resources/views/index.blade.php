<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KayXchange — Trade with Kay</title>
    <meta name="description" content="Welcome to Kay xchange! We provide fast and reliable Crypto to Naira exchange services.">
    <meta name="keywords" content="Kay xchange, currency exchange, Cryptocurrency to naira, Cryptotonaira, p2p, foreign exchange">
    <link rel="icon" href="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" type="{{ $siteFaviconMime ?? 'image/png' }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="splash-screen splash-theme-{{ in_array($siteLoaderTheme ?? 'classic', ['classic','festive','christmas','aurora','orbit','gold','ocean','cosmic','matrix','neon'], true) ? $siteLoaderTheme : 'classic' }}" id="splashScreen">

    @if(($siteLoaderTheme ?? 'classic') === 'christmas')
    <div class="holiday-snow" aria-hidden="true">
        <span>✦</span><span>❄</span><span>✧</span><span>❄</span><span>✦</span><span>❄</span><span>✧</span><span>❄</span><span>✦</span><span>❄</span>
    </div>
    <div class="holiday-tree" aria-hidden="true">
        <span class="holiday-star">✦</span><i></i><i></i><i></i><b></b>
        <span class="holiday-bauble bauble-one"></span><span class="holiday-bauble bauble-two"></span><span class="holiday-bauble bauble-three"></span>
        <span class="holiday-gift gift-one"></span><span class="holiday-gift gift-two"></span>
    </div>
    @endif

    <!-- Floating crypto icons background -->
    <div class="bg-icons" aria-hidden="true">
        <span class="bg-icon" style="left:8%;top:15%;animation-delay:0s">₿</span>
        <span class="bg-icon" style="left:85%;top:10%;animation-delay:1.2s">Ξ</span>
        <span class="bg-icon" style="left:20%;top:75%;animation-delay:0.6s">₮</span>
        <span class="bg-icon" style="left:75%;top:70%;animation-delay:1.8s">₿</span>
        <span class="bg-icon" style="left:50%;top:5%;animation-delay:0.9s">Ξ</span>
        <span class="bg-icon" style="left:5%;top:50%;animation-delay:2.1s">₮</span>
        <span class="bg-icon" style="left:92%;top:45%;animation-delay:0.3s">₿</span>
        <span class="bg-icon" style="left:40%;top:88%;animation-delay:1.5s">Ξ</span>
    </div>

    <div class="splash-content">

        <!-- Logo -->
        <div class="logo-wrap">
            <img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt="KayXchange Logo" class="logo-img" onerror="this.style.display='none'">
            <span class="logo-text">Kay<span class="logo-accent">Xchange</span></span>
        </div>

        <!-- Headline -->
        <h1 class="headline">
            <span class="headline-trade">Trade</span>
            <span class="headline-with"> with </span>
            <span class="headline-kay">Kay</span>
        </h1>

        <p class="tagline">Fast · Secure · Best Rates</p>
    @if(($siteLoaderTheme ?? 'classic') === 'christmas')
        <p class="holiday-greeting">Merry Christmas <span aria-hidden="true">✦</span> Warm wishes from KayXchange</p>
    @elseif(($siteLoaderTheme ?? 'classic') === 'festive')
        <p class="holiday-greeting">Welcome to 2027 <span aria-hidden="true">✦</span> A bright new year ahead</p>
    @endif

        <!-- Coin row -->
        <div class="coins-row" aria-hidden="true">
            <div class="coin coin-btc">₿</div>
            <div class="coin coin-eth">Ξ</div>
            <div class="coin coin-usdt">₮</div>
        </div>

        <!-- Progress bar -->
        <div class="progress-track">
            <div class="progress-bar" id="progressBar"></div>
        </div>
        <p class="loading-text">{{ ($siteLoaderTheme ?? 'classic') === 'christmas' ? 'Preparing a little holiday magic…' : 'Loading your experience…' }}</p>

    </div>
</div>

<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        background: #050f0b;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
    }

    /* ─── Splash Screen ─── */
    .splash-screen {
        position: fixed;
        inset: 0;
        background: radial-gradient(ellipse at 30% 40%, #0a2212 0%, #050f0b 70%);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    .splash-screen.fade-out {
        opacity: 0;
        transform: scale(1.04);
        pointer-events: none;
    }

    /* ─── Floating bg icons ─── */
    .bg-icons { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
    .bg-icon {
        position: absolute;
        font-size: clamp(1.8rem, 4vw, 3.5rem);
        color: rgba(34, 197, 94, 0.06);
        animation: floatUp 8s ease-in-out infinite;
        user-select: none;
    }
    @keyframes floatUp {
        0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.06; }
        50% { transform: translateY(-30px) rotate(8deg); opacity: 0.12; }
    }

    /* ─── Content ─── */
    .splash-content {
        text-align: center;
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.4rem;
        padding: 2rem;
        animation: fadeInUp 0.8s ease both;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ─── Logo ─── */
    .logo-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        justify-content: center;
    }
    .logo-img {
        width: clamp(44px, 8vw, 64px);
        height: auto;
        border-radius: 12px;
        box-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
        animation: pulseGlow 2.5s ease-in-out infinite;
    }
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 18px rgba(34, 197, 94, 0.35); }
        50%       { box-shadow: 0 0 38px rgba(34, 197, 94, 0.65); }
    }
    .logo-text {
        font-size: clamp(1.6rem, 5vw, 2.4rem);
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.5px;
    }
    .logo-accent { color: #22c55e; }

    /* ─── Headline ─── */
    .headline {
        font-size: clamp(2.4rem, 8vw, 4.5rem);
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -1px;
    }
    .headline-trade {
        color: #ffffff;
        display: inline-block;
        animation: slideInLeft 0.7s ease 0.3s both;
    }
    .headline-with {
        color: rgba(255,255,255,0.45);
        font-weight: 300;
        font-size: 0.65em;
        vertical-align: middle;
        animation: fadeIn 0.7s ease 0.5s both;
    }
    .headline-kay {
        color: #22c55e;
        display: inline-block;
        animation: slideInRight 0.7s ease 0.7s both;
        text-shadow: 0 0 30px rgba(34, 197, 94, 0.5);
    }
    @keyframes slideInLeft  { from { opacity:0; transform:translateX(-40px); } to { opacity:1; transform:translateX(0); } }
    @keyframes slideInRight { from { opacity:0; transform:translateX(40px);  } to { opacity:1; transform:translateX(0); } }
    @keyframes fadeIn       { from { opacity:0; } to { opacity:1; } }

    /* ─── Tagline ─── */
    .tagline {
        color: rgba(255,255,255,0.5);
        font-size: clamp(0.8rem, 2.5vw, 1rem);
        letter-spacing: 3px;
        text-transform: uppercase;
        font-weight: 400;
        animation: fadeIn 0.6s ease 1s both;
    }

    /* ─── Coins ─── */
    .coins-row {
        display: flex;
        gap: 1.2rem;
        animation: fadeIn 0.6s ease 1.1s both;
    }
    .coin {
        width: clamp(44px, 8vw, 58px);
        height: clamp(44px, 8vw, 58px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(1.2rem, 3vw, 1.6rem);
        font-weight: 700;
        animation: coinBounce 1.8s ease-in-out infinite;
    }
    .coin-btc  { background: rgba(247,147,26,0.15); color:#f7931a; border: 1.5px solid rgba(247,147,26,0.25); animation-delay: 0s;    }
    .coin-eth  { background: rgba(98,126,234,0.15);  color:#627eea; border: 1.5px solid rgba(98,126,234,0.25); animation-delay: 0.25s; }
    .coin-usdt { background: rgba(38,161,123,0.15);  color:#26a17b; border: 1.5px solid rgba(38,161,123,0.25); animation-delay: 0.5s;  }
    @keyframes coinBounce {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-10px); }
    }

    /* ─── Progress bar ─── */
    .progress-track {
        width: clamp(200px, 50vw, 320px);
        height: 4px;
        background: rgba(255,255,255,0.08);
        border-radius: 99px;
        overflow: hidden;
        animation: fadeIn 0.5s ease 1.2s both;
    }
    .progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #16a34a, #22c55e, #4ade80);
        border-radius: 99px;
        transition: width 0.1s linear;
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.6);
    }
    .loading-text {
        color: rgba(255,255,255,0.3);
        font-size: 0.75rem;
        letter-spacing: 1px;
        animation: fadeIn 0.5s ease 1.3s both;
    }

    /* Seasonal styling for the branded opening splash. */
    .holiday-greeting{color:#f4d58a;font-size:clamp(.68rem,2.4vw,.82rem);font-weight:700;letter-spacing:1.1px;text-transform:uppercase;text-shadow:0 0 18px rgba(240,197,104,.25);animation:fadeIn .7s ease 1.05s both}
    .holiday-greeting span{color:#ed6871;margin:0 .35rem}
    .splash-theme-festive{background:radial-gradient(ellipse at 30% 40%,#153c23 0%,#07130d 70%)}
    .splash-theme-festive .progress-bar{background:linear-gradient(90deg,#16a34a,#f2c86f,#42db83)}
    .splash-theme-christmas{background:radial-gradient(ellipse at 25% 18%,rgba(24,105,64,.6),transparent 39%),radial-gradient(ellipse at 85% 80%,rgba(161,35,55,.3),transparent 42%),linear-gradient(145deg,#07140e,#090e0d 58%,#12100d)!important}
    .splash-theme-christmas .bg-icon{color:rgba(242,205,130,.095)}
    .splash-theme-christmas .logo-img{box-shadow:0 0 26px rgba(54,219,124,.38),0 0 46px rgba(194,51,72,.12)}
    .splash-theme-christmas .headline-kay{text-shadow:0 0 28px rgba(34,197,94,.44),0 0 44px rgba(207,57,77,.18)}
    .splash-theme-christmas .progress-bar{background:linear-gradient(90deg,#16874c,#d84c59,#f2d183,#35c777);background-size:220% 100%;animation:holiday-shimmer 2.2s ease-in-out infinite}
    .splash-theme-christmas .progress-track{box-shadow:0 0 18px rgba(210,62,78,.12)}
    .splash-theme-christmas .coin{box-shadow:0 8px 24px rgba(0,0,0,.2),inset 0 1px rgba(255,255,255,.12)}
    .splash-theme-aurora{background:radial-gradient(ellipse at 22% 25%,rgba(39,214,187,.25),transparent 42%),radial-gradient(ellipse at 84% 75%,rgba(105,94,255,.23),transparent 43%),linear-gradient(145deg,#071a1b,#07101b)!important}.splash-theme-aurora .logo-accent,.splash-theme-aurora .headline-kay{color:#60efd1}.splash-theme-aurora .progress-bar{background:linear-gradient(90deg,#4fe4c4,#7e8cff,#b281ff)}.splash-theme-aurora .bg-icon{color:rgba(91,241,221,.1)}
    .splash-theme-orbit{background:radial-gradient(circle at 50% 45%,rgba(47,92,216,.22),transparent 34%),radial-gradient(ellipse at 80% 10%,rgba(172,82,255,.17),transparent 38%),#080a1b!important}.splash-theme-orbit .logo-accent,.splash-theme-orbit .headline-kay{color:#74dfff;text-shadow:0 0 25px rgba(101,194,255,.55)}.splash-theme-orbit .progress-bar{background:linear-gradient(90deg,#42d9ff,#817aff,#d57cff)}.splash-theme-orbit .coin{box-shadow:0 0 20px rgba(107,144,255,.13)}
    .splash-theme-gold{background:radial-gradient(ellipse at 50% 18%,rgba(171,125,40,.24),transparent 42%),linear-gradient(145deg,#171309,#090a0a 62%,#161108)!important}.splash-theme-gold .logo-accent,.splash-theme-gold .headline-kay{color:#f1ce78;text-shadow:0 0 24px rgba(240,202,111,.28)}.splash-theme-gold .progress-bar{background:linear-gradient(90deg,#a87625,#f6da91,#c49945)}.splash-theme-gold .bg-icon{color:rgba(239,207,130,.1)}
    .splash-theme-ocean{background:radial-gradient(ellipse at 50% 100%,rgba(0,168,181,.26),transparent 46%),radial-gradient(ellipse at 5% 20%,rgba(22,96,173,.28),transparent 43%),linear-gradient(155deg,#061522,#071018 68%,#06201f)!important}.splash-theme-ocean .logo-accent,.splash-theme-ocean .headline-kay{color:#53e5dc;text-shadow:0 0 24px rgba(83,229,220,.28)}.splash-theme-ocean .progress-bar{background:linear-gradient(90deg,#1689c2,#47e4d6,#8dfff0)}.splash-theme-ocean .coin{animation:coinFloat 2.6s ease-in-out infinite}
    .splash-theme-cosmic{background:radial-gradient(circle at 50% 18%,rgba(133,77,224,.25),transparent 36%),radial-gradient(circle at 85% 90%,rgba(235,87,188,.15),transparent 38%),linear-gradient(145deg,#110927,#070913 66%,#160c25)!important}.splash-theme-cosmic .logo-accent,.splash-theme-cosmic .headline-kay{color:#c69bff;text-shadow:0 0 28px rgba(194,145,255,.48)}.splash-theme-cosmic .progress-bar{background:linear-gradient(90deg,#7f5cff,#d26bff,#ff94d1)}.splash-theme-cosmic .bg-icon{color:rgba(198,155,255,.11)}
    .splash-theme-matrix{background:repeating-linear-gradient(0deg,rgba(53,209,107,.035) 0 1px,transparent 1px 38px),repeating-linear-gradient(90deg,rgba(53,209,107,.035) 0 1px,transparent 1px 38px),radial-gradient(ellipse at center,rgba(18,100,52,.32),transparent 60%),#030b08!important}.splash-theme-matrix .logo-accent,.splash-theme-matrix .headline-kay{color:#68ff9c;text-shadow:0 0 26px rgba(75,255,139,.5)}.splash-theme-matrix .progress-bar{background:linear-gradient(90deg,#138d43,#57ff91,#b8ffd0)}.splash-theme-matrix .coin{border-radius:12px;transform:rotate(45deg);animation:coinMatrix 2.8s ease-in-out infinite}
    .splash-theme-neon{background:radial-gradient(ellipse at 18% 25%,rgba(255,53,194,.19),transparent 36%),radial-gradient(ellipse at 83% 76%,rgba(50,219,255,.18),transparent 40%),#100819!important}.splash-theme-neon .logo-accent,.splash-theme-neon .headline-kay{color:#ff70dc;text-shadow:0 0 13px rgba(255,74,211,.7),0 0 30px rgba(255,74,211,.36)}.splash-theme-neon .progress-bar{background:linear-gradient(90deg,#ff54cc,#7d7cff,#48e7ff)}.splash-theme-neon .coin{box-shadow:0 0 20px rgba(87,231,255,.16),inset 0 0 12px rgba(255,255,255,.08)}
    @keyframes coinFloat{50%{translate:0 -9px;rotate:3deg}}@keyframes coinMatrix{50%{translate:0 -8px;rotate:50deg}}
    .holiday-snow{position:absolute;inset:0;overflow:hidden;pointer-events:none;z-index:0}
    .holiday-snow span{position:absolute;top:-8vh;left:var(--x);color:rgba(239,248,255,.75);font-size:var(--s);filter:drop-shadow(0 0 7px rgba(255,255,255,.35));animation:snow-drift var(--d) linear var(--delay) infinite}
    .holiday-snow span:nth-child(1){--x:7%;--s:12px;--d:8s;--delay:-2s}.holiday-snow span:nth-child(2){--x:19%;--s:17px;--d:11s;--delay:-7s}.holiday-snow span:nth-child(3){--x:31%;--s:10px;--d:9s;--delay:-3s}.holiday-snow span:nth-child(4){--x:43%;--s:15px;--d:12s;--delay:-9s}.holiday-snow span:nth-child(5){--x:56%;--s:11px;--d:8.5s;--delay:-5s}.holiday-snow span:nth-child(6){--x:67%;--s:16px;--d:10s;--delay:-1s}.holiday-snow span:nth-child(7){--x:77%;--s:10px;--d:12s;--delay:-8s}.holiday-snow span:nth-child(8){--x:88%;--s:14px;--d:9.5s;--delay:-4s}.holiday-snow span:nth-child(9){--x:96%;--s:9px;--d:11s;--delay:-6s}.holiday-snow span:nth-child(10){--x:25%;--s:13px;--d:13s;--delay:-10s}
    .holiday-tree{position:absolute;right:clamp(14px,7vw,100px);bottom:clamp(20px,7vh,72px);width:104px;height:138px;opacity:.72;filter:drop-shadow(0 15px 22px rgba(0,0,0,.34));transform-origin:50% 100%;animation:tree-sway 5s ease-in-out infinite;z-index:1}
    .holiday-tree i{position:absolute;left:50%;width:0;height:0;transform:translateX(-50%);border-left:var(--w) solid transparent;border-right:var(--w) solid transparent;border-bottom:var(--h) solid var(--green);filter:drop-shadow(0 5px 7px rgba(0,0,0,.18))}
    .holiday-tree i:nth-of-type(1){--w:24px;--h:40px;--green:#43c47a;top:16px}.holiday-tree i:nth-of-type(2){--w:37px;--h:48px;--green:#15834c;top:39px}.holiday-tree i:nth-of-type(3){--w:51px;--h:54px;--green:#075b37;top:67px}
    .holiday-tree b{position:absolute;left:47px;bottom:0;width:12px;height:24px;border-radius:2px;background:linear-gradient(90deg,#68411e,#bd8545,#68411e)}
    .holiday-star{position:absolute;z-index:2;top:1px;left:50%;transform:translateX(-50%);color:#ffe29a;font-size:21px;text-shadow:0 0 17px #f5ca62;animation:star-glow 1.8s ease-in-out infinite}
    .holiday-bauble{position:absolute;z-index:3;width:12px;height:12px;border-radius:50%;background:radial-gradient(circle at 30% 25%,#fff5dc,#e9c262 36%,#966019);box-shadow:0 0 10px rgba(255,205,93,.48);animation:bauble-float 2.8s ease-in-out infinite}.bauble-one{top:52px;left:40px}.bauble-two{top:83px;left:64px;background:radial-gradient(circle at 30% 25%,#fff1f1,#ef7680 36%,#982e46);animation-delay:.5s}.bauble-three{top:104px;left:31px;background:radial-gradient(circle at 30% 25%,#f4ffff,#7fd5e5 36%,#287487);animation-delay:1s}
    .holiday-gift{position:absolute;z-index:2;bottom:7px;width:25px;height:21px;border-radius:4px;box-shadow:0 7px 12px rgba(0,0,0,.28);animation:gift-bob 2.5s ease-in-out infinite}.holiday-gift:before{content:"";position:absolute;left:10px;top:0;width:5px;height:100%;background:rgba(255,239,199,.82)}.holiday-gift:after{content:"";position:absolute;left:7px;top:-6px;width:11px;height:7px;border:2px solid #f4dc9e;border-radius:50% 50% 0 50%;transform:rotate(15deg)}.gift-one{left:2px;background:linear-gradient(135deg,#ee777a,#a92843)}.gift-two{right:0;bottom:12px;background:linear-gradient(135deg,#f2d587,#a9782b);animation-delay:.7s}
    @keyframes snow-drift{to{transform:translate3d(22px,115vh,0) rotate(220deg);opacity:.1}}@keyframes holiday-shimmer{50%{background-position:100% 0}}@keyframes tree-sway{50%{transform:rotate(1.2deg)}}@keyframes star-glow{50%{transform:translateX(-50%) scale(1.18);filter:brightness(1.35)}}@keyframes bauble-float{50%{translate:0 -5px;scale:1.08}}@keyframes gift-bob{50%{translate:0 -3px}}
    @media(max-width:680px){.holiday-tree{width:70px;height:96px;right:9px;bottom:14px;opacity:.42;transform:scale(.74);transform-origin:bottom right}.holiday-greeting{max-width:86vw;text-align:center;letter-spacing:.7px}.splash-theme-christmas .splash-content{gap:1.15rem;padding:1.5rem}}
    @media(prefers-reduced-motion:reduce){.splash-content,.logo-img,.headline-trade,.headline-with,.headline-kay,.tagline,.coins-row,.coin,.progress-track,.loading-text,.holiday-greeting,.holiday-snow span,.holiday-tree,.holiday-star,.holiday-bauble,.holiday-gift,.progress-bar{animation:none!important}.holiday-snow{display:none}}
</style>

<script>
    // Animate progress bar over 5.5s then redirect
    const bar = document.getElementById('progressBar');
    const duration = 5500;
    const start = performance.now();

    function step(now) {
        const elapsed = now - start;
        const pct = Math.min((elapsed / duration) * 100, 100);
        bar.style.width = pct + '%';
        if (elapsed < duration) {
            requestAnimationFrame(step);
        } else {
            document.getElementById('splashScreen').classList.add('fade-out');
            setTimeout(() => {
                window.location.href = '{{ url("/home") }}';
            }, 600);
        }
    }
    requestAnimationFrame(step);
</script>
</body>
</html>
