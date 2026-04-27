{{-- PWA Install Banner / Widget
     - Mobile: slides up as a bottom sheet
     - Desktop: small floating card (bottom-right)
     - iOS: shows "tap Share → Add to Home Screen" guide
     - Android/Chrome: triggers native BeforeInstallPromptEvent
     - Already installed (standalone mode): completely hidden
     - Dismissed: hidden for 7 days via localStorage
--}}

@once
<style>
/* ── PWA Banner Base ──────────────────────────────────────────────────── */
#kx-pwa-banner {
    display: none; /* JS reveals it */
    position: fixed;
    z-index: 2000;
    font-family: 'Poppins', sans-serif;
}

/* Mobile — bottom sheet */
@media (max-width: 767.98px) {
    #kx-pwa-banner {
        bottom: 0; left: 0; right: 0;
        background: #0d1a0d;
        border-top: 1px solid rgba(0, 204, 0, 0.25);
        border-radius: 20px 20px 0 0;
        padding: 20px 20px 28px;
        box-shadow: 0 -8px 40px rgba(0,0,0,0.55);
        transform: translateY(100%);
        transition: transform 0.38s cubic-bezier(0.16,1,0.3,1);
    }
    #kx-pwa-banner.kx-pwa-show { transform: translateY(0); }
    /* nudge bottom nav up when banner is open */
    body.kx-pwa-open .kx-bottom-nav { bottom: var(--pwa-banner-h, 160px); }
}

/* Desktop — floating card */
@media (min-width: 768px) {
    #kx-pwa-banner {
        bottom: 24px; right: 24px;
        width: 340px;
        background: #0d1a0d;
        border: 1px solid rgba(0, 204, 0, 0.25);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 12px 48px rgba(0,0,0,0.6);
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    #kx-pwa-banner.kx-pwa-show { opacity: 1; transform: translateY(0); }
}

/* ── Inner Layout ─────────────────────────────────────────────────────── */
.kx-pwa-drag-handle {
    width: 40px; height: 4px;
    background: rgba(255,255,255,0.15);
    border-radius: 2px;
    margin: 0 auto 16px;
}
@media (min-width: 768px) { .kx-pwa-drag-handle { display: none; } }

.kx-pwa-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 14px;
}
.kx-pwa-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    background: linear-gradient(135deg,#00cc00,#007a0c);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(0,204,0,0.35);
}
.kx-pwa-text h6 {
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    margin: 0 0 3px;
}
.kx-pwa-text p {
    color: rgba(255,255,255,0.55);
    font-size: 0.78rem;
    margin: 0;
    line-height: 1.4;
}
.kx-pwa-close {
    margin-left: auto;
    background: none;
    border: none;
    color: rgba(255,255,255,0.4);
    font-size: 1.2rem;
    padding: 0;
    cursor: pointer;
    line-height: 1;
    flex-shrink: 0;
}
.kx-pwa-close:hover { color: rgba(255,255,255,0.8); }

/* Benefits */
.kx-pwa-benefits {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.kx-pwa-chip {
    background: rgba(0,204,0,0.1);
    border: 1px solid rgba(0,204,0,0.2);
    color: #00cc00;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 20px;
    padding: 3px 10px;
}

/* iOS steps */
.kx-pwa-ios-steps {
    background: rgba(255,255,255,0.04);
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 14px;
}
.kx-pwa-ios-steps p {
    color: rgba(255,255,255,0.7);
    font-size: 0.78rem;
    margin: 0 0 8px;
    font-weight: 600;
}
.kx-pwa-ios-steps ol {
    margin: 0; padding-left: 18px;
}
.kx-pwa-ios-steps li {
    color: rgba(255,255,255,0.55);
    font-size: 0.75rem;
    margin-bottom: 4px;
    line-height: 1.4;
}
.kx-pwa-ios-steps .kx-share-icon {
    display: inline-block;
    background: rgba(0,122,255,0.2);
    border-radius: 4px;
    padding: 0px 5px;
    font-size: 0.75rem;
    color: #60a5fa;
}

/* Install button */
.kx-pwa-install-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg,#00cc00,#007a0c);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.2s;
    text-decoration: none;
}
.kx-pwa-install-btn:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }
.kx-pwa-install-btn:active { transform: scale(0.98); }

/* Dismiss link */
.kx-pwa-dismiss {
    display: block;
    text-align: center;
    margin-top: 10px;
    font-size: 0.73rem;
    color: rgba(255,255,255,0.3);
    cursor: pointer;
}
.kx-pwa-dismiss:hover { color: rgba(255,255,255,0.6); }

/* Already installed badge */
#kx-pwa-installed-toast {
    display: none;
    position: fixed;
    bottom: 80px; left: 50%; transform: translateX(-50%);
    background: rgba(0,204,0,0.15);
    border: 1px solid rgba(0,204,0,0.3);
    color: #00cc00;
    padding: 10px 22px;
    border-radius: 30px;
    font-size: 0.82rem;
    font-weight: 600;
    z-index: 2001;
    backdrop-filter: blur(10px);
    white-space: nowrap;
}
</style>

{{-- ── Banner HTML ────────────────────────────────────────────────────────── --}}
<div id="kx-pwa-banner" role="dialog" aria-label="Install KayXchange app">
    <div class="kx-pwa-drag-handle"></div>

    <div class="kx-pwa-header">
        <div class="kx-pwa-icon">📲</div>
        <div class="kx-pwa-text">
            <h6>Add KayXchange to your home screen</h6>
            <p>Trade crypto faster — no app store needed</p>
        </div>
        <button class="kx-pwa-close" id="kx-pwa-close-btn" aria-label="Dismiss">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="kx-pwa-benefits">
        <span class="kx-pwa-chip">⚡ Instant access</span>
        <span class="kx-pwa-chip">🔔 Push alerts</span>
        <span class="kx-pwa-chip">📴 Works offline</span>
        <span class="kx-pwa-chip">🔒 Secure</span>
    </div>

    {{-- iOS instructions (hidden by default, shown by JS) --}}
    <div class="kx-pwa-ios-steps" id="kx-pwa-ios-guide" style="display:none">
        <p>Install on iPhone / iPad:</p>
        <ol>
            <li>Tap <span class="kx-share-icon"><i class="bi bi-box-arrow-up"></i> Share</span> in Safari's toolbar</li>
            <li>Scroll and tap <strong style="color:#fff">"Add to Home Screen"</strong></li>
            <li>Tap <strong style="color:#fff">"Add"</strong> — done! 🎉</li>
        </ol>
    </div>

    {{-- Android / Chrome install button (hidden by default, shown by JS) --}}
    <button class="kx-pwa-install-btn" id="kx-pwa-install-btn" style="display:none">
        <i class="bi bi-download"></i>
        Install App — It's Free
    </button>

    <span class="kx-pwa-dismiss" id="kx-pwa-dismiss-link">Maybe later</span>
</div>

<div id="kx-pwa-installed-toast">
    ✅ KayXchange is installed on your device!
</div>

<script>
(function () {
    'use strict';

    const STORAGE_KEY   = 'kx_pwa_dismissed_until';
    const DISMISS_DAYS  = 7;
    const SHOW_DELAY_MS = 4000; // wait 4 s before showing

    const banner       = document.getElementById('kx-pwa-banner');
    const installBtn   = document.getElementById('kx-pwa-install-btn');
    const iosGuide     = document.getElementById('kx-pwa-ios-guide');
    const closeBtn     = document.getElementById('kx-pwa-close-btn');
    const dismissLink  = document.getElementById('kx-pwa-dismiss-link');
    const toast        = document.getElementById('kx-pwa-installed-toast');

    let deferredPrompt = null;

    // ── Guards ────────────────────────────────────────────────────────────
    // 1. Already running as installed PWA
    const isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    // 2. User dismissed recently
    const dismissedUntil = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
    const isDismissed    = Date.now() < dismissedUntil;

    if (isStandalone || isDismissed) return;

    // ── Platform detection ────────────────────────────────────────────────
    const isIos    = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isSafari = /safari/i.test(navigator.userAgent) && !/chrome/i.test(navigator.userAgent);

    // ── Show banner helper ────────────────────────────────────────────────
    function showBanner() {
        banner.style.display = 'block';
        // Measure height for mobile nav adjustment
        requestAnimationFrame(() => {
            document.documentElement.style.setProperty(
                '--pwa-banner-h', banner.offsetHeight + 'px'
            );
            document.body.classList.add('kx-pwa-open');
            banner.classList.add('kx-pwa-show');
        });
    }

    function hideBanner() {
        banner.classList.remove('kx-pwa-show');
        document.body.classList.remove('kx-pwa-open');
        setTimeout(() => { banner.style.display = 'none'; }, 400);
    }

    function dismiss() {
        const until = Date.now() + DISMISS_DAYS * 24 * 60 * 60 * 1000;
        localStorage.setItem(STORAGE_KEY, until);
        hideBanner();
    }

    // ── iOS flow ──────────────────────────────────────────────────────────
    if (isIos && isSafari) {
        iosGuide.style.display = 'block';
        setTimeout(showBanner, SHOW_DELAY_MS);
    }

    // ── Chrome / Android flow ─────────────────────────────────────────────
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        installBtn.style.display = 'flex';
        setTimeout(showBanner, SHOW_DELAY_MS);
    });

    installBtn.addEventListener('click', async function () {
        if (!deferredPrompt) return;
        hideBanner();
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        deferredPrompt = null;
        if (outcome === 'accepted') {
            showInstalledToast();
        }
    });

    // ── Post-install event ────────────────────────────────────────────────
    window.addEventListener('appinstalled', showInstalledToast);

    function showInstalledToast() {
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 4000);
    }

    // ── Dismiss handlers ──────────────────────────────────────────────────
    closeBtn.addEventListener('click', dismiss);
    dismissLink.addEventListener('click', dismiss);

    // ── Expose globally so the /install-app page can trigger it ──────────
    window.kxShowPwaBanner = showBanner;
    window.kxInstallPwa    = async function () {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            if (outcome === 'accepted') showInstalledToast();
        }
    };
    window.kxPwaIsReady = function () { return !!deferredPrompt; };
})();
</script>
@endonce
