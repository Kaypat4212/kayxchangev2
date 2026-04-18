<style>
.kx-navbar {
    background: rgba(8, 14, 8, 0.97);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 204, 0, 0.12);
    padding: 10px 0;
    transition: all 0.3s ease;
    position: sticky;
    top: 0;
    z-index: 1040;
}
.kx-navbar.kx-scrolled {
    padding: 6px 0;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.6);
    border-bottom-color: rgba(0, 204, 0, 0.2);
}
.kx-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none !important;
    color: #ffffff;
    font-size: 1.2rem;
    font-weight: 700;
    letter-spacing: -0.3px;
}
.kx-brand img {
    width: 36px; height: 36px;
    border-radius: 8px;
    box-shadow: 0 0 14px rgba(0, 204, 0, 0.45);
}
.kx-brand-green { color: #00cc00; }
.kx-nav-link {
    color: rgba(255, 255, 255, 0.75) !important;
    font-weight: 500;
    font-size: 0.875rem;
    padding: 7px 12px !important;
    border-radius: 8px;
    transition: all 0.22s ease;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    position: relative;
}
.kx-nav-link:hover { color: #00cc00 !important; background: rgba(0, 204, 0, 0.08); }
.kx-nav-link.kx-active { color: #00cc00 !important; background: rgba(0, 204, 0, 0.1); }
.kx-nav-link.kx-active::after {
    content: '';
    position: absolute;
    bottom: 1px; left: 50%;
    transform: translateX(-50%);
    width: 16px; height: 2px;
    background: #00cc00;
    border-radius: 2px;
}
.kx-btn-login {
    color: #00cc00 !important;
    border: 1.5px solid rgba(0, 204, 0, 0.5);
    border-radius: 25px;
    padding: 9px 22px;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none !important;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(0,204,0,0.06);
    backdrop-filter: blur(8px);
    position: relative;
    overflow: hidden;
}
.kx-btn-login::before { content:''; position:absolute; inset:0; background: linear-gradient(135deg,rgba(0,204,0,0.12),transparent); opacity:0; transition:opacity 0.25s ease; border-radius:25px; pointer-events:none; }
.kx-btn-login:hover { background: rgba(0, 204, 0, 0.12); border-color: #00cc00; box-shadow: 0 0 20px rgba(0,204,0,0.22), inset 0 1px 0 rgba(255,255,255,0.08); }
.kx-btn-login:hover::before { opacity:1; }
.kx-btn-register {
    background: linear-gradient(135deg, #00cc00 0%, #009e0f 50%, #007a0c 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 25px;
    padding: 10px 24px;
    font-weight: 700;
    font-size: 0.875rem;
    text-decoration: none !important;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 4px 20px rgba(0, 204, 0, 0.38), inset 0 1px 0 rgba(255,255,255,0.2);
    position: relative;
    overflow: hidden;
}
.kx-btn-register::after { content:''; position:absolute; inset:0; background: linear-gradient(135deg,rgba(255,255,255,0.18) 0%,transparent 55%); border-radius:25px; pointer-events:none; }
.kx-btn-register:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0, 204, 0, 0.52); color: #fff !important; }
.kx-btn-register:active { transform: translateY(0); box-shadow: 0 3px 12px rgba(0, 204, 0, 0.35); }
.kx-btn-logout {
    color: rgba(255, 110, 110, 0.9);
    border: 1.5px solid rgba(255, 80, 80, 0.35);
    background: transparent;
    border-radius: 25px;
    padding: 7px 16px;
    font-weight: 500;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.22s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.kx-btn-logout:hover { background: rgba(255, 80, 80, 0.1); border-color: rgba(255,80,80,0.7); color: #ff5555; }
.kx-theme-btn {
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.6);
    border-radius: 50%;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.22s ease;
    flex-shrink: 0;
}
.kx-theme-btn:hover { background: rgba(0, 204, 0, 0.15); border-color: rgba(0,204,0,0.35); color: #00cc00; }
.kx-navbar .navbar-toggler { border: 1.5px solid rgba(0, 204, 0, 0.45); border-radius: 8px; padding: 5px 10px; }
.kx-navbar .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0, 204, 0, 0.85)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}
[data-bs-theme="light"] .kx-navbar { background: rgba(255, 255, 255, 0.97); border-bottom-color: rgba(0,153,0,0.15); }
[data-bs-theme="light"] .kx-brand { color: #111; }
[data-bs-theme="light"] .kx-nav-link { color: rgba(20,20,20,0.75) !important; }
[data-bs-theme="light"] .kx-nav-link:hover,[data-bs-theme="light"] .kx-nav-link.kx-active { color: #007a0f !important; background: rgba(0,130,17,0.07); }
[data-bs-theme="light"] .kx-theme-btn { background: rgba(0,0,0,0.05); border-color: rgba(0,0,0,0.1); color: #555; }
@media (max-width: 991.98px) {
    .kx-navbar .navbar-collapse {
        background: rgba(8, 14, 8, 0.98);
        border: 1px solid rgba(0, 204, 0, 0.14);
        border-radius: 14px;
        margin-top: 10px;
        padding: 16px;
    }
    [data-bs-theme="light"] .kx-navbar .navbar-collapse { background: rgba(255,255,255,0.99); border-color: rgba(0,153,0,0.15); }
    .kx-nav-link.kx-active::after { display: none; }
    .kx-nav-actions { flex-wrap: wrap; gap: 8px; margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(0,204,0,0.13); }
}
</style>

<nav class="kx-navbar navbar navbar-expand-lg" id="kxMainNav">
  <div class="container">
    <a class="kx-brand" href="@auth {{ url('/dashboard') }} @else {{ url('/') }} @endauth">
      <img src="{{ asset('Assests/favicon.png') }}" alt="KayXchange">
      <span>Kay<span class="kx-brand-green">Xchange</span></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#kxNav" aria-controls="kxNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="kxNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
        @auth
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('dashboard')) kx-active @endif" href="{{ url('/dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('deposits*')) kx-active @endif" href="{{ url('/deposits/create') }}">
            <i class="bi bi-plus-circle-fill"></i>Deposit
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('rate')) kx-active @endif" href="{{ url('/rate') }}">
            <i class="bi bi-graph-up-arrow"></i>Rates
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('rate-calculator')) kx-active @endif" href="{{ url('/rate-calculator') }}">
            <i class="bi bi-calculator-fill"></i>Calculator
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('buy*')) kx-active @endif" href="{{ url('/buy') }}">
            <i class="bi bi-arrow-down-circle-fill"></i>Buy
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('sell*')) kx-active @endif" href="{{ url('/sell') }}">
            <i class="bi bi-arrow-up-circle-fill"></i>Sell
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('settings*')) kx-active @endif" href="{{ url('/settings') }}">
            <i class="bi bi-gear-fill"></i>Settings
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('referrals')) kx-active @endif" href="{{ url('/referrals') }}">
            <i class="bi bi-people-fill"></i>Referrals
          </a>
        </li>
        @if(session('admin_id'))
        <li class="nav-item">
          <a class="kx-nav-link" href="{{ route('admin.revert') }}">
            <i class="bi bi-arrow-return-left"></i>Back to Admin
          </a>
        </li>
        @endif
        @else
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('/')) kx-active @endif" href="{{ url('/') }}">
            <i class="bi bi-house-fill"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('rate')) kx-active @endif" href="{{ url('/rate') }}">
            <i class="bi bi-graph-up-arrow"></i>Rates
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('rate-calculator')) kx-active @endif" href="{{ url('/rate-calculator') }}">
            <i class="bi bi-calculator-fill"></i>Calculator
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('blog*')) kx-active @endif" href="{{ url('/blog') }}">
            <i class="bi bi-newspaper"></i>Blog
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('faqs*')) kx-active @endif" href="{{ url('/faqs') }}">
            <i class="bi bi-question-circle-fill"></i>FAQs
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('about*')) kx-active @endif" href="{{ url('/about') }}">
            <i class="bi bi-info-circle-fill"></i>About
          </a>
        </li>
        @endauth
      </ul>

      <div class="d-flex align-items-center kx-nav-actions gap-2">
        @auth
        @include('components.notification-dropdown')
        <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
          @csrf
          <button type="submit" class="kx-btn-logout">
            <i class="bi bi-box-arrow-right"></i>Logout
          </button>
        </form>
        @else
        <a class="kx-btn-login" href="{{ route('login') }}">
          <i class="bi bi-box-arrow-in-right"></i>Login
        </a>
        <a class="kx-btn-register" href="{{ route('register') }}">
          <i class="bi bi-rocket-takeoff-fill"></i>Get Started
        </a>
        @endauth
        <button id="toggle-mode" class="kx-theme-btn" title="Toggle Dark Mode">
          <i class="bi bi-moon-stars-fill" id="mode-icon"></i>
        </button>
      </div>
    </div>
  </div>
</nav>

<script>
(function () {
    var nav = document.getElementById('kxMainNav');
    if (!nav) return;
    window.addEventListener('scroll', function () {
        nav.classList.toggle('kx-scrolled', window.scrollY > 20);
    }, { passive: true });
})();
</script>