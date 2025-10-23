<nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img width="40px" src="/Assests/favicon.png" alt="KayXchange" class="me-2">
            KayXchange
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ratesNavbarNav" aria-controls="ratesNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ratesNavbarNav">
            <div class="navbar-nav me-auto">
                <a class="nav-link @if(request()->is('/')) active @endif" @if(request()->is('/')) aria-current="page" @endif href="/">Home</a>
                <a class="nav-link @if(request()->is('rate')) active @endif" @if(request()->is('rate')) aria-current="page" @endif href="/rate">Exchange Rates</a>
                <a class="nav-link @if(request()->is('blog*')) active @endif" @if(request()->is('blog*')) aria-current="page" @endif href="/blog">Blog</a>
                <a class="nav-link @if(request()->is('faqs*')) active @endif" @if(request()->is('faqs*')) aria-current="page" @endif href="/faqs">FAQs</a>
                <a class="nav-link @if(request()->is('about*')) active @endif" @if(request()->is('about*')) aria-current="page" @endif href="/about">About Us</a>
            </div>

            <!-- Right side navigation -->
            <div class="d-flex align-items-center">
                @auth
                    <a class="nav-link @if(request()->is('dashboard')) active @endif" @if(request()->is('dashboard')) aria-current="page" @endif href="/dashboard">Dashboard</a>
                    <a class="nav-link @if(request()->is('buy*')) active @endif" @if(request()->is('buy*')) aria-current="page" @endif href="/buy">Buy Crypto</a>
                    <a class="nav-link @if(request()->is('sell*')) active @endif" @if(request()->is('sell*')) aria-current="page" @endif href="/sell">Sell Crypto</a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline me-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger" title="Logout">
                          <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                @else
                    <a class="btn btn-outline-primary me-2" href="/login">
                      <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                    <a class="btn btn-primary" href="/register">
                      <i class="bi bi-person-plus me-1"></i>Register
                    </a>
                @endauth

                <!-- Dark Mode Toggle Button -->
                <button id="toggle-mode" class="btn btn-outline-secondary ms-2" title="Toggle Dark Mode">
                  <i class="bi bi-moon-stars-fill" id="mode-icon"></i>
                </button>
            </div>
        </div>
    </div>
</nav>
