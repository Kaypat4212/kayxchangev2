<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#00cc00">
    <title>{{ config('app.name', 'KayXchange') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img width="40px" src="/Assests/favicon.png" alt="KayXchange" class="me-2">
                KayXchange
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNavbarNav" aria-controls="appNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="appNavbarNav">
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
                        <a class="nav-link @if(request()->is('dashboard')) active @endif" @if(request()->is('dashboard')) aria-current="page" @endif href="{{ url('/dashboard') }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline me-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger" title="Logout">
                              <i class="bi bi-box-arrow-right me-1"></i>Logout
                            </button>
                        </form>
                    @else
                        <a class="btn btn-outline-primary me-2" href="{{ route('login') }}">
                          <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                        <a class="btn btn-primary" href="{{ route('register') }}">
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

    <div class="container mt-5">
        @yield('content')
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dark Mode Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleMode = document.getElementById('toggle-mode');
            const modeIcon = document.getElementById('mode-icon');
            const body = document.body;
            
            // Check for saved theme preference or default to light mode
            const currentTheme = localStorage.getItem('theme') || 'light';
            body.setAttribute('data-bs-theme', currentTheme);
            updateModeIcon(currentTheme);
            
            if (toggleMode) {
                toggleMode.addEventListener('click', function() {
                    const currentTheme = body.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    
                    body.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateModeIcon(newTheme);
                });
            }
            
            function updateModeIcon(theme) {
                if (modeIcon) {
                    modeIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
                }
            }
        });
    </script>
</body>

</html>
