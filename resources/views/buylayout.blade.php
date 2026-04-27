<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'KayXchange') }} — Buy</title>
    <link rel="icon" type="image/png" href="{{ asset('Assests/favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
    <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: #070d07; color: #e8f5e8; margin: 0; padding: 0; padding-bottom: 70px; }
    body.light-mode { background: #f4faf4; color: #0a1a0a; }
    body.light-mode { --kx-card:#ffffff; --kx-card2:#f0f7f0; --kx-border:rgba(0,0,0,0.09); --kx-text:#0a1a0a; --kx-muted:#5a6a5a; --kx-dark:#f4faf4; }
    body.light-mode .kx-card { background:#fff; border-color:rgba(0,0,0,0.09); }
    body.light-mode .kx-input { background:#f0f7f0; border-color:rgba(0,0,0,0.12); color:#0a1a0a; }
    body.light-mode .kx-input::placeholder { color:#8a9a8a; }
    body.light-mode .kx-bottom-nav { background:rgba(244,250,244,0.97); border-top-color:rgba(0,153,0,0.2); }
    body.light-mode .kx-bnav-item { color:rgba(0,0,0,0.38); }
    body.light-mode .kx-bnav-item.kx-bnav-active { color:#007a0c; border-top-color:#007a0c; }
    .kx-navbar .navbar-nav { padding:0!important; margin:0!important; display:flex!important; list-style:none!important; flex-direction:column!important; align-items:flex-start!important; }
    @media(min-width:992px){ .kx-navbar .navbar-nav { flex-direction:row!important; align-items:center!important; } }
    .kx-navbar .nav-item { margin:0!important; }
    .kx-navbar a { text-decoration:none!important; }
    .kx-bottom-nav { display:none; position:fixed; bottom:0; left:0; right:0; height:64px; background:rgba(8,14,8,0.97); backdrop-filter:blur(20px); border-top:1px solid rgba(0,204,0,0.15); z-index:1050; padding:0; align-items:stretch; justify-content:space-around; }
    @media(max-width:991.98px){ .kx-bottom-nav{display:flex;} body{padding-bottom:68px;} }
    .kx-bnav-item { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px; text-decoration:none!important; color:rgba(255,255,255,0.45); font-size:0.62rem; font-weight:500; padding:8px 4px; border-top:2px solid transparent; transition:all 0.2s ease; }
    .kx-bnav-item i { font-size:1.25rem; line-height:1; }
    .kx-bnav-item:hover,.kx-bnav-item.kx-bnav-active { color:#00cc00; }
    .kx-bnav-item.kx-bnav-active { border-top-color:#00cc00; }
    .kx-bnav-center { flex:1.2; position:relative; }
    .kx-bnav-center-btn { position:absolute; top:-18px; left:50%; transform:translateX(-50%); width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#00cc00,#007a0c); box-shadow:0 4px 18px rgba(0,204,0,0.45); display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#fff; text-decoration:none!important; transition:all 0.22s ease; }
    .kx-bnav-center-btn:hover { transform:translateX(-50%) scale(1.08); color:#fff; }
    .kx-bnav-center-lbl { position:absolute; bottom:6px; left:50%; transform:translateX(-50%); font-size:0.6rem; color:rgba(255,255,255,0.4); white-space:nowrap; }
    #loader { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:9999; }
    </style>
    @stack('styles')
</head>
<body>
    @include('components.navbar')

    @auth
    <nav class="kx-bottom-nav" aria-label="Mobile navigation">
        <a href="{{ url('/dashboard') }}" class="kx-bnav-item @if(request()->is('dashboard')) kx-bnav-active @endif"><i class="bi bi-grid-1x2-fill"></i><span>Home</span></a>
        <a href="{{ url('/wallet/send') }}" class="kx-bnav-item @if(request()->is('wallet*')) kx-bnav-active @endif"><i class="bi bi-send-fill"></i><span>Send</span></a>
        <div class="kx-bnav-item kx-bnav-center">
            <a href="{{ url('/buy') }}" class="kx-bnav-center-btn kx-bnav-active" title="Buy Crypto"><i class="bi bi-arrow-down-circle-fill"></i></a>
            <span class="kx-bnav-center-lbl">Buy</span>
        </div>
        <a href="{{ url('/sell') }}" class="kx-bnav-item @if(request()->is('sell*')) kx-bnav-active @endif"><i class="bi bi-arrow-up-circle-fill"></i><span>Sell</span></a>
        <a href="{{ url('/settings') }}" class="kx-bnav-item @if(request()->is('settings*')) kx-bnav-active @endif"><i class="bi bi-person-fill"></i><span>Account</span></a>
    </nav>
    @endauth

    <div id="loader"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div></div>

    <div class="container-xl py-4">
        @yield('content')
    </div>

    @include('components.kx-footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tronweb@5.3.2/dist/TronWeb.min.js"></script>
    <script>
        if (typeof TronWeb === 'undefined') {
            console.warn('TronWeb not loaded');
        }
    </script>
    <script>
    (function() {
        function applyTheme(light) {
            if (light) { document.body.classList.add('light-mode'); var ic=document.getElementById('mode-icon'); if(ic) ic.className='bi bi-sun-fill'; }
            else        { document.body.classList.remove('light-mode'); var ic=document.getElementById('mode-icon'); if(ic) ic.className='bi bi-moon-stars-fill'; }
        }
        function wireToggle() {
            var btn = document.getElementById('toggle-mode');
            if (btn && !btn._kxWired) {
                btn._kxWired = true;
                btn.addEventListener('click', function() {
                    var nowLight = !document.body.classList.contains('light-mode');
                    applyTheme(nowLight);
                    localStorage.setItem('theme', nowLight ? 'light' : 'dark');
                });
            }
        }
        applyTheme(localStorage.getItem('theme') === 'light');
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { applyTheme(localStorage.getItem('theme') === 'light'); wireToggle(); });
        } else { wireToggle(); }
    })();
    </script>
    @stack('scripts')

    @include('components.ai-chatbot-widget')
</body>
</html>