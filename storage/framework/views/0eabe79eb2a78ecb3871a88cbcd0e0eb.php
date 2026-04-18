<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KayXchange — Trade with Kay</title>
    <meta name="description" content="Welcome to Kay xchange! We provide fast and reliable Crypto to Naira exchange services.">
    <meta name="keywords" content="Kay xchange, currency exchange, Cryptocurrency to naira, Cryptotonaira, p2p, foreign exchange">
    <link rel="icon" href="<?php echo e(asset('Assests/favicon.png')); ?>" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="splash-screen" id="splashScreen">

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
            <img src="<?php echo e(asset('Assests/favicon.png')); ?>" alt="KayXchange Logo" class="logo-img" onerror="this.style.display='none'">
            <span class="logo-text">Kay<span class="logo-accent">Xchange</span></span>
        </div>

        <!-- Headline -->
        <h1 class="headline">
            <span class="headline-trade">Trade</span>
            <span class="headline-with"> with </span>
            <span class="headline-kay">Kay</span>
        </h1>

        <p class="tagline">Fast · Secure · Best Rates</p>

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
        <p class="loading-text">Loading your experience…</p>

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
                window.location.href = '<?php echo e(url("/home")); ?>';
            }, 600);
        }
    }
    requestAnimationFrame(step);
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/index.blade.php ENDPATH**/ ?>