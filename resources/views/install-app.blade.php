@extends('layouts.app')

@section('content')
<style>
.kx-install-hero {
    text-align: center;
    padding: 2.5rem 1rem 1.5rem;
}
.kx-install-app-icon {
    width: 100px; height: 100px;
    border-radius: 22px;
    background: linear-gradient(135deg, #00cc00, #007a0c);
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem;
    margin: 0 auto 1.2rem;
    box-shadow: 0 8px 32px rgba(0,204,0,0.35);
}
.kx-install-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(0,204,0,0.15);
    border-radius: 16px;
    padding: 1.4rem;
    margin-bottom: 1rem;
}
.kx-benefit-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 1.5rem;
}
@media (min-width: 576px) {
    .kx-benefit-grid { grid-template-columns: repeat(4, 1fr); }
}
.kx-benefit-item {
    background: rgba(0,204,0,0.06);
    border: 1px solid rgba(0,204,0,0.15);
    border-radius: 12px;
    padding: 14px 10px;
    text-align: center;
}
.kx-benefit-item .bi {
    font-size: 1.6rem;
    color: #00cc00;
    display: block;
    margin-bottom: 6px;
}
.kx-benefit-item span {
    font-size: 0.75rem;
    font-weight: 600;
    color: rgba(255,255,255,0.7);
}
.kx-step-num {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg,#00cc00,#007a0c);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.kx-step-row {
    display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px;
}
.kx-step-row:last-child { margin-bottom: 0; }
.kx-step-body h6 { color: #fff; font-weight: 700; margin: 0 0 2px; font-size: 0.9rem; }
.kx-step-body p  { color: rgba(255,255,255,0.5); font-size: 0.8rem; margin: 0; }
.kx-ios-share-icon {
    display: inline-flex; align-items: center; gap: 4px;
    background: rgba(0,122,255,0.15);
    border: 1px solid rgba(0,122,255,0.25);
    color: #60a5fa;
    border-radius: 6px;
    padding: 1px 8px;
    font-size: 0.78rem;
    font-weight: 600;
}
.kx-platform-tabs .nav-link {
    color: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    margin-right: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 6px 16px;
}
.kx-platform-tabs .nav-link.active {
    background: rgba(0,204,0,0.15);
    border-color: rgba(0,204,0,0.4);
    color: #00cc00;
}
.kx-big-install-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; padding: 15px;
    background: linear-gradient(135deg,#00cc00,#007a0c);
    color: #fff; border: none; border-radius: 14px;
    font-size: 1.05rem; font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.2s;
    text-decoration: none;
}
.kx-big-install-btn:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }
.kx-big-install-btn:disabled { opacity: 0.4; cursor: not-allowed; }
</style>

<div class="row justify-content-center">
<div class="col-12 col-md-8 col-lg-6">

    {{-- Hero --}}
    <div class="kx-install-hero">
        <div class="kx-install-app-icon">📲</div>
        <h2 class="fw-800 mb-1" style="font-size:1.6rem;">Add KayXchange to your device</h2>
        <p class="text-muted mb-0" style="font-size:0.88rem;">
            Install our app in seconds — no App Store, no Play Store needed.
        </p>
    </div>

    {{-- Benefits --}}
    <div class="kx-benefit-grid">
        <div class="kx-benefit-item">
            <i class="bi bi-lightning-charge-fill"></i>
            <span>Instant Access</span>
        </div>
        <div class="kx-benefit-item">
            <i class="bi bi-bell-fill"></i>
            <span>Push Alerts</span>
        </div>
        <div class="kx-benefit-item">
            <i class="bi bi-wifi-off"></i>
            <span>Offline Ready</span>
        </div>
        <div class="kx-benefit-item">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Secure</span>
        </div>
    </div>

    {{-- Platform tabs --}}
    <ul class="nav kx-platform-tabs mb-3" id="platformTabs">
        <li class="nav-item">
            <button class="nav-link active" data-target="android">
                <i class="bi bi-android2"></i> Android / Chrome
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-target="ios">
                <i class="bi bi-apple"></i> iPhone / iPad
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-target="desktop">
                <i class="bi bi-laptop"></i> Desktop
            </button>
        </li>
    </ul>

    {{-- Android / Chrome --}}
    <div class="kx-install-card" id="tab-android">
        <h6 class="mb-3" style="color:#00cc00;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
            <i class="bi bi-android2 me-1"></i>Android / Chrome
        </h6>
        <div class="kx-step-row">
            <div class="kx-step-num">1</div>
            <div class="kx-step-body">
                <h6>Tap the button below</h6>
                <p>Chrome will show a native install prompt</p>
            </div>
        </div>
        <div class="kx-step-row">
            <div class="kx-step-num">2</div>
            <div class="kx-step-body">
                <h6>Tap "Install"</h6>
                <p>The app downloads instantly — no data wasted</p>
            </div>
        </div>
        <div class="kx-step-row">
            <div class="kx-step-num">3</div>
            <div class="kx-step-body">
                <h6>Find KayXchange on your home screen</h6>
                <p>Open it just like any other app 🎉</p>
            </div>
        </div>

        <button class="kx-big-install-btn mt-3" id="page-install-btn" disabled>
            <i class="bi bi-download"></i>
            <span id="install-btn-label">Loading…</span>
        </button>
        <p class="text-muted text-center mt-2" style="font-size:0.75rem;" id="android-hint"></p>
    </div>

    {{-- iOS --}}
    <div class="kx-install-card" id="tab-ios" style="display:none;">
        <h6 class="mb-3" style="color:#60a5fa;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
            <i class="bi bi-apple me-1"></i>iPhone / iPad (Safari)
        </h6>
        <div class="kx-step-row">
            <div class="kx-step-num">1</div>
            <div class="kx-step-body">
                <h6>Open this page in Safari</h6>
                <p>The install feature only works in Safari on iOS</p>
            </div>
        </div>
        <div class="kx-step-row">
            <div class="kx-step-num">2</div>
            <div class="kx-step-body">
                <h6>Tap the Share button</h6>
                <p>
                    Look for <span class="kx-ios-share-icon"><i class="bi bi-box-arrow-up"></i> Share</span>
                    in Safari's bottom bar
                </p>
            </div>
        </div>
        <div class="kx-step-row">
            <div class="kx-step-num">3</div>
            <div class="kx-step-body">
                <h6>Tap "Add to Home Screen"</h6>
                <p>Scroll down in the share sheet to find it</p>
            </div>
        </div>
        <div class="kx-step-row">
            <div class="kx-step-num">4</div>
            <div class="kx-step-body">
                <h6>Tap "Add" to confirm</h6>
                <p>KayXchange appears on your home screen instantly 🎉</p>
            </div>
        </div>
        <div class="kx-install-card mt-3" style="background:rgba(0,122,255,0.07);border-color:rgba(0,122,255,0.2);">
            <p class="mb-0" style="font-size:0.8rem;color:rgba(255,255,255,0.6);">
                <i class="bi bi-info-circle text-info me-1"></i>
                iOS doesn't allow websites to trigger an install prompt automatically. Follow the steps above — it's quick!
            </p>
        </div>
    </div>

    {{-- Desktop --}}
    <div class="kx-install-card" id="tab-desktop" style="display:none;">
        <h6 class="mb-3" style="color:#a78bfa;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
            <i class="bi bi-laptop me-1"></i>Desktop Chrome / Edge
        </h6>
        <div class="kx-step-row">
            <div class="kx-step-num">1</div>
            <div class="kx-step-body">
                <h6>Click the install icon in the address bar</h6>
                <p>Look for <strong style="color:#fff">⊕</strong> or a computer icon at the right of your URL bar</p>
            </div>
        </div>
        <div class="kx-step-row">
            <div class="kx-step-num">2</div>
            <div class="kx-step-body">
                <h6>Click "Install"</h6>
                <p>KayXchange opens in its own window, like a native app</p>
            </div>
        </div>

        <button class="kx-big-install-btn mt-3" id="page-install-btn-desktop" disabled>
            <i class="bi bi-download"></i>
            <span id="install-btn-desktop-label">Loading…</span>
        </button>
    </div>

    {{-- Already installed notice --}}
    <div class="kx-install-card text-center" id="already-installed" style="display:none;border-color:rgba(0,204,0,0.3);background:rgba(0,204,0,0.06);">
        <i class="bi bi-check-circle-fill text-success" style="font-size:2.5rem;"></i>
        <h5 class="mt-2 mb-1" style="color:#00cc00;">Already installed!</h5>
        <p class="text-muted mb-0" style="font-size:0.85rem;">
            KayXchange is running as an installed app on this device. You're all set.
        </p>
        <a href="{{ route('dashboard') }}" class="kx-big-install-btn mt-3">
            <i class="bi bi-grid-1x2-fill"></i> Go to Dashboard
        </a>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // ── Already installed check ────────────────────────────────────────────
    const isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    if (isStandalone) {
        document.querySelectorAll('#tab-android,#tab-ios,#tab-desktop').forEach(el => el.style.display = 'none');
        document.getElementById('already-installed').style.display = 'block';
        document.getElementById('platformTabs').style.display = 'none';
        return;
    }

    // ── Platform auto-detect ───────────────────────────────────────────────
    const isIos    = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isSafari = /safari/i.test(navigator.userAgent) && !/chrome/i.test(navigator.userAgent);
    const isMobile = /android|iphone|ipad|ipod/i.test(navigator.userAgent);

    function activateTab(name) {
        document.querySelectorAll('#platformTabs .nav-link').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.target === name);
        });
        ['android','ios','desktop'].forEach(id => {
            document.getElementById('tab-' + id).style.display = (id === name) ? 'block' : 'none';
        });
    }

    // Auto-select tab
    if (isIos) activateTab('ios');
    else if (!isMobile) activateTab('desktop');
    // else stays on android

    // ── Tab switching ──────────────────────────────────────────────────────
    document.querySelectorAll('#platformTabs .nav-link').forEach(btn => {
        btn.addEventListener('click', () => activateTab(btn.dataset.target));
    });

    // ── Install buttons ────────────────────────────────────────────────────
    const androidBtn      = document.getElementById('page-install-btn');
    const androidLabel    = document.getElementById('install-btn-label');
    const androidHint     = document.getElementById('android-hint');
    const desktopBtn      = document.getElementById('page-install-btn-desktop');
    const desktopLabel    = document.getElementById('install-btn-desktop-label');

    function tryEnableButtons() {
        if (window.kxPwaIsReady && window.kxPwaIsReady()) {
            [androidBtn, desktopBtn].forEach(b => b.removeAttribute('disabled'));
            androidLabel.textContent  = 'Install App — It\'s Free';
            desktopLabel.textContent  = 'Install App — It\'s Free';
            androidHint.textContent   = '';
        } else {
            androidLabel.textContent  = 'Open this page in Chrome to install';
            desktopLabel.textContent  = 'Use Chrome or Edge to install';
            androidHint.textContent   = isIos
                ? 'Use Safari on iOS and follow the steps above.'
                : 'Make sure you\'re using Chrome on Android.';
        }
    }

    // Check immediately (banner script might have already caught the event)
    tryEnableButtons();

    // Also check 1 s later (in case banner JS fires after this)
    setTimeout(tryEnableButtons, 1200);

    // Listen for the event directly too (belt-and-suspenders)
    window.addEventListener('beforeinstallprompt', function () {
        setTimeout(tryEnableButtons, 100);
    });

    [androidBtn, desktopBtn].forEach(btn => {
        btn.addEventListener('click', async function () {
            if (window.kxInstallPwa) await window.kxInstallPwa();
        });
    });
})();
</script>
@endpush
