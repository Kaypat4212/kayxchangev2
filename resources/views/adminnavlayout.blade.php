<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — {{ config('app.name', 'KayXchange') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('Assests/favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
    #loader { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:9999; }

    /* ── Light-mode: override CSS variables so all admin pages inherit them ── */
    body.kx-light {
        --kx-dark:   #f0f4f8;
        --kx-card:   #ffffff;
        --kx-card2:  #e8edf4;
        --kx-border: rgba(0,0,0,0.09);
        --kx-text:   #1a2332;
        --kx-muted:  #5a6a7e;
        background:  #f0f4f8 !important;
        color:       #1a2332 !important;
    }
    /* Tables */
    body.kx-light .table        { --bs-table-bg: #fff; color: #1a2332; }
    body.kx-light .table thead th { color: #3a4a5e; border-color: rgba(0,0,0,0.1); }
    body.kx-light .table td      { border-color: rgba(0,0,0,0.06); color: #1a2332; }
    body.kx-light .table-hover tbody tr:hover td { background: rgba(0,100,0,0.04); }
    /* Stat / nav cards that use var() colours */
    body.kx-light .kx-stat  { box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
    body.kx-light .kx-stat:hover { box-shadow: 0 0 0 1px var(--kx-green), 0 8px 18px rgba(0,0,0,0.12); }
    body.kx-light .kx-stat-val { color: #0a1a0a; }
    body.kx-light .kx-welcome   { background: linear-gradient(135deg,#e8f5e8 0%,#dceede 100%); }
    /* Navbar */
    body.kx-light .navbar.bg-dark    { background-color: #ffffff !important; border-bottom: 1px solid rgba(0,0,0,0.1); }
    body.kx-light .navbar .navbar-brand,
    body.kx-light .navbar .nav-link  { color: #1a2332 !important; }
    body.kx-light .navbar .nav-link:hover,
    body.kx-light .navbar .nav-link.active { color: #00aa00 !important; }
    body.kx-light .navbar-toggler    { border-color: rgba(0,0,0,0.2); }
    body.kx-light .navbar-toggler-icon { filter: invert(1); }
    /* Footer */
    body.kx-light footer { background: #e4eaf2 !important; color: rgba(0,0,0,0.5) !important; border-top: 1px solid rgba(0,0,0,0.1) !important; }
    /* Badges / pills */
    body.kx-light .badge.bg-secondary { background: #d0d7e2 !important; color: #1a2332; }
    </style>
    @stack('styles')
</head>
<body>
    @include('components.admin-navbar')

    <div id="loader"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div></div>

    <div class="container-fluid" style="margin-top: 24px; padding-bottom: 40px;">
        @yield('content')
    </div>

    <!-- Admin footer -->
    <footer style="background:#1a1f2e;color:rgba(255,255,255,0.4);font-size:0.75rem;text-align:center;padding:14px;border-top:1px solid rgba(255,255,255,0.07);">
        © {{ date('Y') }} KayXchange Admin Panel &mdash; <a href="{{ url('/') }}" target="_blank" style="color:rgba(0,204,0,0.7);text-decoration:none">View Site</a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        function applyTheme(light) {
            if (light) {
                document.body.classList.add('kx-light');
                var ic = document.getElementById('adminModeIcon');
                if (ic) ic.className = 'bi bi-sun-fill';
            } else {
                document.body.classList.remove('kx-light');
                var ic = document.getElementById('adminModeIcon');
                if (ic) ic.className = 'bi bi-moon-stars-fill';
            }
        }

        function wireToggle() {
            var btn = document.getElementById('adminModeToggleBtn');
            if (btn && !btn._kxWired) {
                btn._kxWired = true;
                btn.addEventListener('click', function () {
                    var nowLight = !document.body.classList.contains('kx-light');
                    applyTheme(nowLight);
                    localStorage.setItem('kx_admin_theme', nowLight ? 'light' : 'dark');
                });
            }
        }

        // Apply saved theme immediately (before paint — prevents flash)
        applyTheme(localStorage.getItem('kx_admin_theme') === 'light');

        // Wire click handler — handles both loading and already-ready states
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                applyTheme(localStorage.getItem('kx_admin_theme') === 'light');
                wireToggle();
            });
        } else {
            // DOMContentLoaded already fired (script is at bottom of body)
            wireToggle();
        }
    })();
    </script>
    @stack('scripts')
</body>
</html>