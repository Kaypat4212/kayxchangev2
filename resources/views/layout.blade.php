<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'KayXchange') }} — Dashboard</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('Assests/favicon.png') }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- AOS (scroll animations) -->
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">

    <style>
    /* ── Reset conflict with old template style.css ── */
    * { box-sizing: border-box; }
    body {
        font-family: 'Poppins', sans-serif;
        background: #070d07;
        color: #e8f5e8;
        margin: 0;
        padding: 0;
        padding-bottom: 70px; /* space for mobile bottom nav */
    }
    body.light-mode {
        background: #f4faf4;
        color: #0a1a0a;
    }

    /* ── Top Navbar overrides (fix style.css .navbar conflict) ── */
    .kx-navbar .navbar-nav {
        padding: 0 !important;
        margin: 0 !important;
        display: flex !important;
        list-style: none !important;
        /* default: vertical (mobile) */
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    /* desktop: switch to horizontal row */
    @media (min-width: 992px) {
        .kx-navbar .navbar-nav {
            flex-direction: row !important;
            align-items: center !important;
        }
    }
    .kx-navbar .nav-item { margin: 0 !important; }
    .kx-navbar a { text-decoration: none !important; }

    /* ── Mobile Bottom Navigation ── */
    .kx-bottom-nav {
        display: none;
        position: fixed;
        bottom: 0; left: 0; right: 0;
        height: 64px;
        background: rgba(8, 14, 8, 0.97);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-top: 1px solid rgba(0, 204, 0, 0.15);
        z-index: 1050;
        padding: 0;
        align-items: stretch;
        justify-content: space-around;
    }
    @media (max-width: 991.98px) {
        .kx-bottom-nav { display: flex; }
        body { padding-bottom: 68px; }
    }
    .kx-bnav-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        text-decoration: none !important;
        color: rgba(255, 255, 255, 0.45);
        font-size: 0.62rem;
        font-weight: 500;
        padding: 8px 4px;
        border-top: 2px solid transparent;
        transition: all 0.2s ease;
        position: relative;
    }
    .kx-bnav-item i { font-size: 1.25rem; line-height: 1; }
    .kx-bnav-item:hover { color: #00cc00; }
    .kx-bnav-item.kx-bnav-active {
        color: #00cc00;
        border-top-color: #00cc00;
    }
    .kx-bnav-center {
        position: relative;
        flex: 1.2;
    }
    .kx-bnav-center-btn {
        position: absolute;
        top: -18px;
        left: 50%;
        transform: translateX(-50%);
        width: 52px; height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00cc00, #007a0c);
        box-shadow: 0 4px 18px rgba(0, 204, 0, 0.45);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; color: #fff;
        text-decoration: none !important;
        transition: all 0.22s ease;
    }
    .kx-bnav-center-btn:hover { transform: translateX(-50%) scale(1.08); box-shadow: 0 8px 28px rgba(0,204,0,0.55); color:#fff; }
    .kx-bnav-center-lbl {
        position: absolute;
        bottom: 6px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.6rem;
        color: rgba(255,255,255,0.4);
        white-space: nowrap;
    }
    /* Light mode bottom nav */
    body.light-mode .kx-bottom-nav {
        background: rgba(255, 255, 255, 0.97);
        border-top-color: rgba(0, 153, 0, 0.15);
    }
    body.light-mode .kx-bnav-item { color: rgba(0, 0, 0, 0.38); }
    body.light-mode .kx-bnav-item.kx-bnav-active { color: #007a0c; border-top-color: #007a0c; }
    body.light-mode .kx-bnav-center-lbl { color: rgba(0,0,0,0.38); }

    #loader {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
    }
    </style>
    @stack('styles')
</head>

<body>

    @include('components.navbar')

    <!-- ══ Mobile Bottom Navigation (visible ≤ 992px) ══ -->
    @auth
    <nav class="kx-bottom-nav" id="kxBottomNav" aria-label="Mobile navigation">
        <a href="{{ url('/dashboard') }}"
           class="kx-bnav-item @if(request()->is('dashboard')) kx-bnav-active @endif">
            <i class="bi bi-grid-1x2-fill"></i><span>Home</span>
        </a>
        <a href="{{ url('/rate') }}"
           class="kx-bnav-item @if(request()->is('rate')) kx-bnav-active @endif">
            <i class="bi bi-graph-up-arrow"></i><span>Rates</span>
        </a>
        <!-- Centre FAB: Buy -->
        <div class="kx-bnav-item kx-bnav-center">
            <a href="{{ Auth::user()->kyc_verified ? url('/buy') : url('/kyc') }}"
               class="kx-bnav-center-btn" title="Buy Crypto">
                <i class="bi bi-arrow-down-circle-fill"></i>
            </a>
            <span class="kx-bnav-center-lbl">Buy</span>
        </div>
        <a href="{{ url('/sell') }}"
           class="kx-bnav-item @if(request()->is('sell*')) kx-bnav-active @endif">
            <i class="bi bi-arrow-up-circle-fill"></i><span>Sell</span>
        </a>
        <a href="{{ url('/settings') }}"
           class="kx-bnav-item @if(request()->is('settings*')) kx-bnav-active @endif">
            <i class="bi bi-person-fill"></i><span>Account</span>
        </a>
    </nav>
    @endauth

    <!-- Content Section -->
    <div style="min-height: calc(100vh - 80px);">
        @yield('content')
    </div>

    @include('components.kx-footer')

    <!-- Telegram Floating Action Button (only for authenticated users) -->
    @auth
    @if(!Auth::user()->telegram_verified)
    <div class="telegram-fab" id="telegramFab">
        <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" class="fab-button" title="Connect Telegram for Notifications">
            <i class="fab fa-telegram-plane"></i>
            <span class="fab-text">Connect</span>
        </a>
    </div>

    <style>
        .telegram-fab {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: pulse 2s infinite;
        }

        .fab-button {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0088cc 0%, #005fa3 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(0, 136, 204, 0.4);
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 14px;
        }

        .fab-button:hover {
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 136, 204, 0.6);
        }

        .fab-button i {
            font-size: 18px;
            margin-right: 8px;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @media (max-width: 768px) {
            .telegram-fab {
                bottom: 15px;
                right: 15px;
            }
            
            .fab-button {
                padding: 10px 16px;
                font-size: 12px;
            }
            
            .fab-text {
                display: none;
            }
            
            .fab-button i {
                margin-right: 0;
            }
        }

        /* Hide FAB on Telegram settings page */
        body.telegram-settings .telegram-fab {
            display: none;
        }
    </style>

    <script>
        // Add Font Awesome if not already included
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const fontAwesome = document.createElement('link');
            fontAwesome.rel = 'stylesheet';
            fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
            document.head.appendChild(fontAwesome);
        }

        // Hide FAB if user gets verified
        function checkAndHideFab() {
            fetch('/api/user/telegram-status', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.verified) {
                    const fab = document.getElementById('telegramFab');
                    if (fab) {
                        fab.style.display = 'none';
                    }
                }
            })
            .catch(error => console.log('FAB status check error:', error));
        }

        // Check every 30 seconds
        setInterval(checkAndHideFab, 30000);
    </script>
    @endif
    @endauth

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS (scroll animations) -->
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script>AOS.init({ duration: 600, once: true });</script>

    <!-- Bottom nav active-state helper -->
    <script>
    (function () {
        const path = window.location.pathname.replace(/\/$/, '');
        document.querySelectorAll('.kx-bnav-item[href]').forEach(function (el) {
            const href = el.getAttribute('href').replace(/\/$/, '');
            if (path === href || (path.startsWith(href) && href !== '/dashboard')) {
                el.classList.add('kx-bnav-active');
            }
        });
    })();
    </script>

    <script>
    (function () {
        function applyUserTheme(light) {
            if (light) {
                document.body.classList.add('light-mode');
                var ic = document.getElementById('mode-icon');
                if (ic) ic.className = 'bi bi-sun-fill';
            } else {
                document.body.classList.remove('light-mode');
                var ic = document.getElementById('mode-icon');
                if (ic) ic.className = 'bi bi-moon-stars-fill';
            }
        }
        function wireUserToggle() {
            var btn = document.getElementById('toggle-mode');
            if (btn && !btn._kxWired) {
                btn._kxWired = true;
                btn.addEventListener('click', function () {
                    var nowLight = !document.body.classList.contains('light-mode');
                    applyUserTheme(nowLight);
                    localStorage.setItem('theme', nowLight ? 'light' : 'dark');
                });
            }
        }
        // Apply saved theme immediately (default dark)
        applyUserTheme(localStorage.getItem('theme') === 'light');
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                applyUserTheme(localStorage.getItem('theme') === 'light');
                wireUserToggle();
            });
        } else {
            wireUserToggle();
        }
    })();
    </script>

    @stack('scripts')

    @include('components.ai-chatbot-widget')
</body>

</html>