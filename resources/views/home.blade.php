@extends('layouts.header')

@section('content')

    <style>
        .coin-price {
            font-size: 12px;
            display: grid;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
            margin-bottom: 10px;
            /* Add more styling properties as desired */
        }

        .ml4 {
            position: relative;
            font-weight: 900;
            font-size: 16px;
        }

        .ml4 .letters {
            margin: auto;
            opacity: 0;
        }

        .coin-name {
            font-weight: bold;
        }

        /* Modern Footer Styles */
        .footer {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            color: #ffffff;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(0,255,0,0.03)"/><circle cx="75" cy="75" r="1" fill="rgba(0,255,0,0.03)"/><circle cx="50" cy="10" r="0.5" fill="rgba(0,255,0,0.02)"/><circle cx="10" cy="50" r="0.5" fill="rgba(0,255,0,0.02)"/><circle cx="90" cy="30" r="0.5" fill="rgba(0,255,0,0.02)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .footer-newsletter {
            padding: 60px 0;
            background: linear-gradient(135deg, #00cc00 0%, #009911 100%);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: relative;
        }

        .footer-newsletter::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="30" r="1" fill="rgba(255,255,255,0.05)"/></svg>');
            opacity: 0.3;
        }

        .footer-newsletter h4 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .footer-newsletter p {
            font-size: 16px;
            margin-bottom: 30px;
            color: rgba(255,255,255,0.9);
        }

        .newsletter-form .input-group {
            background: rgba(255,255,255,0.95);
            border-radius: 50px;
            padding: 5px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .newsletter-form .form-control {
            border: none;
            background: transparent;
            padding: 12px 20px;
            font-size: 14px;
            border-radius: 45px;
        }

        .newsletter-form .form-control:focus {
            box-shadow: none;
            background: rgba(0,255,0,0.05);
        }

        .newsletter-form .btn {
            border-radius: 45px;
            padding: 12px 30px;
            font-weight: 600;
            border: none;
            margin-left: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .newsletter-form .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .footer-top {
            padding: 60px 0 30px 0;
            background: #111;
            position: relative;
        }

        .footer-info .logo {
            margin-bottom: 20px;
        }

        .footer-info .logo img {
            filter: drop-shadow(0 2px 4px rgba(0,255,0,0.3));
        }

        .footer-info .logo span {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(45deg, #00cc00, #00ff00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-info p {
            color: #cccccc;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: rgba(0,255,0,0.1);
            color: #00cc00;
            border-radius: 50%;
            margin-right: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,255,0,0.2);
            text-decoration: none;
        }

        .social-links a:hover {
            background: #00cc00;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,255,0,0.3);
        }

        .social-links a.twitter:hover { background: #1da1f2; }
        .social-links a.whatsapp:hover { background: #25d366; }
        .social-links a.instagram:hover { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
        .social-links a.snapchat:hover { background: #fffc00; color: #000; }
        .social-links a.telegram:hover { background: #0088cc; }

        .footer-links h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
        }

        .footer-links h4::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 30px;
            height: 2px;
            background: #00cc00;
            border-radius: 2px;
        }

        .footer-links ul li {
            margin-bottom: 12px;
        }

        .footer-links ul a {
            color: #cccccc;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .footer-links ul a:hover {
            color: #00cc00;
            transform: translateX(5px);
        }

        .footer-links ul a i {
            color: #00cc00;
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .footer-links ul a:hover i {
            transform: translateX(3px);
        }

        .footer-contact h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .footer-contact .d-flex {
            margin-bottom: 15px;
        }

        .footer-contact i {
            flex-shrink: 0;
        }

        .footer-contact a {
            color: #cccccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-contact a:hover {
            color: #00cc00;
        }

        .copyright {
            padding: 25px 0;
            color: #888;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 14px;
        }

        .copyright a {
            color: #00cc00;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .copyright a:hover {
            color: #00ff00;
        }

        .badge {
            font-size: 11px;
            font-weight: 500;
            border-radius: 20px;
        }

        /* Animation */
        .footer {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .footer-newsletter {
                padding: 40px 0;
            }

            .footer-newsletter h4 {
                font-size: 24px;
            }

            .newsletter-form .input-group {
                flex-direction: column;
                gap: 10px;
                background: rgba(255,255,255,0.95);
                border-radius: 15px;
                padding: 20px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            }

            .newsletter-form .form-control {
                border-radius: 10px;
                margin-bottom: 10px;
            }

            .newsletter-form .btn {
                margin-left: 0;
                margin-top: 0;
                width: 100%;
                border-radius: 10px;
                padding: 12px;
                font-size: 16px;
            }

            .footer-top {
                padding: 40px 0 20px 0;
            }

            .social-links {
                justify-content: center;
                text-align: center;
            }

            .social-links a {
                margin-bottom: 15px;
            }

            .copyright .row {
                text-align: center;
            }

            .copyright .col-md-6 {
                margin-bottom: 15px;
            }

            .footer-links ul li {
                text-align: center;
                margin-bottom: 15px;
            }

            .footer-contact .d-flex {
                flex-direction: column;
                text-align: center;
                align-items: center;
                margin-bottom: 20px;
            }

            .footer-contact .d-flex > div {
                margin-top: 5px;
            }

            .badge {
                font-size: 10px;
                padding: 8px 16px;
            }
        }

        @media (max-width: 576px) {
            .footer-newsletter h4 {
                font-size: 20px;
                line-height: 1.3;
            }

            .footer-newsletter p {
                font-size: 14px;
            }

            .footer-info .logo span {
                font-size: 24px;
            }

            .footer-links h4 {
                font-size: 16px;
            }

            .footer-contact h4 {
                font-size: 16px;
            }

            .footer-links ul a {
                font-size: 14px;
            }

            .copyright {
                font-size: 12px;
            }

            .social-links a {
                width: 40px;
                height: 40px;
            }

            .social-links a i {
                font-size: 18px;
            }
        }

        @media (max-width: 480px) {
            .footer-newsletter {
                padding: 30px 0;
            }

            .footer-top {
                padding: 30px 0 15px 0;
            }

            .footer-info p {
                font-size: 13px;
                line-height: 1.4;
            }

            .footer-links ul li {
                margin-bottom: 10px;
            }

            .badge {
                display: block;
                width: fit-content;
                margin: 0 auto 8px auto;
            }
        }

        /* Enhanced animations and interactions */
        .footer-links ul a {
            position: relative;
            overflow: hidden;
        }

        .footer-links ul a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 204, 0, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .footer-links ul a:hover::before {
            left: 100%;
        }

        /* Newsletter form enhancements */
        .newsletter-form {
            position: relative;
        }

        .newsletter-form .input-group:focus-within {
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        /* Social media hover effects with brand colors */
        .social-links a.twitter:hover { background: #1da1f2; transform: translateY(-3px) rotate(5deg); }
        .social-links a.whatsapp:hover { background: #25d366; transform: translateY(-3px) rotate(-5deg); }
        .social-links a.instagram:hover { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); transform: translateY(-3px) rotate(5deg); }
        .social-links a.snapchat:hover { background: #fffc00; color: #000; transform: translateY(-3px) rotate(-5deg); }
        .social-links a.telegram:hover { background: #0088cc; transform: translateY(-3px) rotate(5deg); }

        /* Loading animation for form submission */
        .newsletter-form.submitting {
            pointer-events: none;
            opacity: 0.7;
        }

        .newsletter-form.submitting .btn::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Success message styling */
        .newsletter-success {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            z-index: 9999;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .newsletter-success.show {
            transform: translateX(0);
            opacity: 1;
        }

        /* Back to top button enhancement */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #00cc00, #009911);
            color: white;
            border: none;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
    </style>


    <!-- ======= KayXchange Navbar ======= -->
    <style>
    .kx-hp-nav {
        background: rgba(8, 14, 8, 0.97);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(0, 204, 0, 0.12);
        padding: 10px 0;
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1040;
        transition: all 0.3s ease;
    }
    .kx-hp-nav.kx-scrolled { padding: 6px 0; box-shadow: 0 4px 30px rgba(0,0,0,0.6); border-bottom-color: rgba(0,204,0,0.22); }
    .kx-hp-nav .kx-brand {
        display: flex; align-items: center; gap: 10px;
        text-decoration: none !important;
        color: #fff; font-size: 1.2rem; font-weight: 700;
    }
    .kx-hp-nav .kx-brand img { width: 36px; height: 36px; border-radius: 8px; box-shadow: 0 0 14px rgba(0,204,0,0.45); }
    .kx-hp-nav .kx-brand-g { color: #00cc00; }
    .kx-hp-link {
        color: rgba(255,255,255,0.75) !important;
        font-weight: 500; font-size: 0.875rem;
        padding: 7px 12px !important; border-radius: 8px;
        transition: all 0.22s ease;
        text-decoration: none !important;
        display: inline-flex; align-items: center; gap: 6px;
        position: relative;
    }
    .kx-hp-link:hover { color: #00cc00 !important; background: rgba(0,204,0,0.08); }
    .kx-hp-link.kx-act { color: #00cc00 !important; background: rgba(0,204,0,0.1); }
    .kx-hp-nav .navbar-toggler { border: 1.5px solid rgba(0,204,0,0.45); border-radius: 8px; padding: 5px 10px; }
    .kx-hp-nav .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0,204,0,0.85)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    .kx-hp-btn-in { color: #00cc00 !important; border: 1.5px solid rgba(0,204,0,0.5); border-radius: 25px; padding: 9px 22px; font-weight: 600; font-size: 0.875rem; text-decoration: none !important; transition: all 0.25s ease; display: inline-flex; align-items: center; gap: 7px; background: rgba(0,204,0,0.06); backdrop-filter: blur(8px); position: relative; overflow: hidden; }
    .kx-hp-btn-in::before { content:''; position:absolute; inset:0; background: linear-gradient(135deg,rgba(0,204,0,0.12),transparent); opacity:0; transition:opacity 0.25s ease; border-radius:25px; pointer-events:none; }
    .kx-hp-btn-in:hover { background: rgba(0,204,0,0.12); border-color: #00cc00; box-shadow: 0 0 20px rgba(0,204,0,0.22), inset 0 1px 0 rgba(255,255,255,0.08); }
    .kx-hp-btn-in:hover::before { opacity:1; }
    .kx-hp-btn-reg { background: linear-gradient(135deg,#00cc00 0%,#009e0f 50%,#007a0c 100%); color: #fff !important; border: none; border-radius: 25px; padding: 10px 24px; font-weight: 700; font-size: 0.875rem; text-decoration: none !important; transition: all 0.25s ease; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 4px 20px rgba(0,204,0,0.38), inset 0 1px 0 rgba(255,255,255,0.2); position: relative; overflow: hidden; }
    .kx-hp-btn-reg::after { content:''; position:absolute; inset:0; background: linear-gradient(135deg,rgba(255,255,255,0.18) 0%,transparent 55%); border-radius:25px; pointer-events:none; }
    .kx-hp-btn-reg:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,204,0,0.52); color: #fff !important; }
    .kx-hp-btn-reg:active { transform: translateY(0); box-shadow: 0 3px 12px rgba(0,204,0,0.35); }
    .kx-hp-btn-out { color: rgba(255,110,110,0.9); border: 1.5px solid rgba(255,80,80,0.35); background: transparent; border-radius: 25px; padding: 7px 16px; font-weight: 500; font-size: 0.85rem; cursor: pointer; transition: all 0.22s ease; display: inline-flex; align-items: center; gap: 6px; }
    .kx-hp-btn-out:hover { background: rgba(255,80,80,0.1); border-color: rgba(255,80,80,0.7); color: #ff5555; }
    .kx-hp-theme { background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.6); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.22s ease; }
    .kx-hp-theme:hover { background: rgba(0,204,0,0.15); border-color: rgba(0,204,0,0.35); color: #00cc00; }
    @media (max-width: 991.98px) {
        .kx-hp-nav .navbar-collapse { background: rgba(8,14,8,0.98); border: 1px solid rgba(0,204,0,0.14); border-radius: 14px; margin-top: 10px; padding: 16px; }
        .kx-hp-nav-actions { flex-wrap: wrap; gap: 8px; margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(0,204,0,0.13); }
    }
    [data-bs-theme="light"] .kx-hp-nav { background: rgba(255,255,255,0.97); border-bottom-color: rgba(0,153,0,0.15); }
    [data-bs-theme="light"] .kx-hp-nav .kx-brand { color: #111; }
    [data-bs-theme="light"] .kx-hp-link { color: rgba(20,20,20,0.75) !important; }
    [data-bs-theme="light"] .kx-hp-link:hover,[data-bs-theme="light"] .kx-hp-link.kx-act { color: #007a0f !important; background: rgba(0,130,17,0.07); }
    [data-bs-theme="light"] .kx-hp-theme { background: rgba(0,0,0,0.05); border-color: rgba(0,0,0,0.1); color: #555; }
    [data-bs-theme="light"] .kx-hp-nav .navbar-collapse { background: rgba(255,255,255,0.99); }
    </style>

    <nav class="kx-hp-nav navbar navbar-expand-lg" id="kxHomeNav">
        <div class="container">
            <a class="kx-brand" href="@auth {{ url('/dashboard') }} @else {{ url('/') }} @endauth">
                <img src="{{ asset('Assests/favicon.png') }}" alt="KayXchange">
                <span>Kay<span class="kx-brand-g">Xchange</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#kxHomeCollapse" aria-controls="kxHomeCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="kxHomeCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                    @auth
                    <li class="nav-item"><a class="kx-hp-link @if(request()->is('dashboard')) kx-act @endif" href="{{ url('/dashboard') }}"><i class="bi bi-grid-1x2-fill"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="kx-hp-link @if(request()->is('rate')) kx-act @endif" href="{{ url('/rate') }}"><i class="bi bi-graph-up-arrow"></i>Rates</a></li>
                    <li class="nav-item"><a class="kx-hp-link @if(request()->is('buy*')) kx-act @endif" href="{{ url('/buy') }}"><i class="bi bi-arrow-down-circle-fill"></i>Buy</a></li>
                    <li class="nav-item"><a class="kx-hp-link @if(request()->is('sell*')) kx-act @endif" href="{{ url('/sell') }}"><i class="bi bi-arrow-up-circle-fill"></i>Sell</a></li>
                    @else
                    <li class="nav-item"><a class="kx-hp-link kx-act" href="{{ url('/') }}"><i class="bi bi-house-fill"></i>Home</a></li>
                    <li class="nav-item"><a class="kx-hp-link" href="{{ url('/rate') }}"><i class="bi bi-graph-up-arrow"></i>Rates</a></li>
                    <li class="nav-item"><a class="kx-hp-link" href="{{ url('/blog') }}"><i class="bi bi-newspaper"></i>Blog</a></li>
                    <li class="nav-item"><a class="kx-hp-link" href="{{ url('/faqs') }}"><i class="bi bi-question-circle-fill"></i>FAQs</a></li>
                    <li class="nav-item"><a class="kx-hp-link" href="{{ url('/about') }}"><i class="bi bi-info-circle-fill"></i>About</a></li>
                    @endauth
                </ul>
                <div class="d-flex align-items-center kx-hp-nav-actions gap-2">
                    @auth
                    <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="kx-hp-btn-out"><i class="bi bi-box-arrow-right"></i>Logout</button>
                    </form>
                    @else
                    <a class="kx-hp-btn-in" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i>Login</a>
                    <a class="kx-hp-btn-reg" href="{{ route('register') }}"><i class="bi bi-rocket-takeoff-fill"></i>Get Started</a>
                    @endauth
                    <button id="toggle-mode" class="kx-hp-theme" title="Toggle Dark Mode">
                        <i class="bi bi-moon-stars-fill" id="mode-icon"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <script>
    (function(){
        var n=document.getElementById('kxHomeNav');
        if(!n)return;
        window.addEventListener('scroll',function(){n.classList.toggle('kx-scrolled',window.scrollY>20);},{passive:true});
    })();
    </script>

    <!-- ======= Hero Section ======= -->
    <style>
    .kx-hero {
        position: relative;
        background: #070d07;
        overflow: hidden;
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding-top: 70px;
    }
    .kx-h-grid {
        position: absolute; inset: 0; pointer-events: none;
        background-image:
            linear-gradient(rgba(0,204,0,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0,204,0,0.04) 1px, transparent 1px);
        background-size: 60px 60px;
    }
    .kx-h-orb1 {
        position: absolute; border-radius: 50%; pointer-events: none;
        width: 650px; height: 650px;
        background: radial-gradient(circle, rgba(0,204,0,0.11) 0%, transparent 70%);
        top: -200px; right: -100px; filter: blur(80px);
    }
    .kx-h-orb2 {
        position: absolute; border-radius: 50%; pointer-events: none;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(0,100,0,0.14) 0%, transparent 70%);
        bottom: -120px; left: -80px; filter: blur(80px);
    }
    .kx-h-content { position: relative; z-index: 2; padding: 80px 0 60px; }
    .kx-h-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(0,204,0,0.1); border: 1px solid rgba(0,204,0,0.3);
        color: #00cc00; font-size: 0.82rem; font-weight: 600;
        padding: 6px 16px; border-radius: 25px; margin-bottom: 22px;
    }
    .kx-h-dot {
        width: 8px; height: 8px; background: #00cc00; border-radius: 50%;
        display: inline-block; animation: kx-pulse 2s ease-in-out infinite;
    }
    @keyframes kx-pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(1.4)} }
    .kx-h-title {
        font-size: clamp(2.4rem, 5.5vw, 4.2rem);
        font-weight: 800; line-height: 1.12;
        color: #ffffff; margin-bottom: 22px;
        font-family: 'Poppins', sans-serif;
    }
    .kx-h-grad {
        background: linear-gradient(135deg, #00ff66, #00cc00, #008f11);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .kx-h-sub {
        font-size: 1.05rem; color: rgba(255,255,255,0.6);
        line-height: 1.75; max-width: 480px; margin-bottom: 32px;
    }
    .kx-h-stats {
        display: flex; align-items: center; gap: 22px;
        margin-bottom: 36px; flex-wrap: wrap;
    }
    .kx-h-stat { display: flex; flex-direction: column; }
    .kx-h-snum { font-size: 1.55rem; font-weight: 800; color: #00cc00; line-height: 1; }
    .kx-h-slbl { font-size: 0.72rem; color: rgba(255,255,255,0.45); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
    .kx-h-sdiv { width: 1px; height: 36px; background: rgba(255,255,255,0.13); }
    .kx-h-ctas { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 30px; }
    .kx-h-cta1 {
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg,#00cc00,#008f11);
        color: #fff !important; text-decoration: none !important;
        padding: 14px 30px; border-radius: 50px;
        font-weight: 700; font-size: 1rem;
        box-shadow: 0 8px 30px rgba(0,204,0,0.38);
        transition: all 0.28s ease;
    }
    .kx-h-cta1:hover { transform: translateY(-2px); box-shadow: 0 14px 42px rgba(0,204,0,0.48); }
    .kx-h-cta2 {
        display: inline-flex; align-items: center; gap: 8px;
        border: 2px solid rgba(0,204,0,0.5); color: #00cc00 !important;
        text-decoration: none !important; padding: 14px 28px;
        border-radius: 50px; font-weight: 600; font-size: 1rem;
        background: rgba(0,204,0,0.05); transition: all 0.28s ease;
    }
    .kx-h-cta2:hover { background: rgba(0,204,0,0.12); border-color: #00cc00; transform: translateY(-2px); }
    .kx-h-trust { display: flex; flex-wrap: wrap; gap: 10px; }
    .kx-h-tbadge {
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.09);
        color: rgba(255,255,255,0.75); font-size: 0.8rem; font-weight: 500;
        padding: 9px 18px; border-radius: 12px;
        display: inline-flex; align-items: center; gap: 8px;
        text-decoration: none; transition: all 0.25s ease;
        backdrop-filter: blur(8px); position: relative; overflow: hidden;
    }
    .kx-h-tbadge::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(255,255,255,0.05),transparent); border-radius:12px; pointer-events:none; }
    .kx-h-tbadge i { font-size: 0.9rem; }
    .kx-h-tbadge:hover { background: rgba(0,204,0,0.1); border-color: rgba(0,204,0,0.38); color: #00cc00; text-decoration: none; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,204,0,0.18); }
    .kx-h-twa { background: rgba(37,211,102,0.08); border-color: rgba(37,211,102,0.3); color: #25d366 !important; }
    .kx-h-twa:hover { background: rgba(37,211,102,0.16) !important; border-color: rgba(37,211,102,0.55) !important; color: #25d366 !important; box-shadow: 0 8px 24px rgba(37,211,102,0.22) !important; }
    /* Right visual */
    .kx-h-visual { display: flex; justify-content: center; align-items: center; position: relative; padding: 30px 20px; }
    .kx-h-card-wrap { position: relative; width: 100%; max-width: 430px; margin: 0 auto; }
    .kx-h-mockup {
        background: rgba(0,204,0,0.06); border: 1px solid rgba(0,204,0,0.18);
        border-radius: 24px; padding: 28px;
        backdrop-filter: blur(10px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.45), 0 0 80px rgba(0,204,0,0.07);
        animation: kx-float 4s ease-in-out infinite;
    }
    @keyframes kx-float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
    .kx-h-mockup img { border-radius: 10px; width: 100%; }
    .kx-h-coin {
        position: absolute; background: rgba(8,16,8,0.95);
        border: 1px solid rgba(0,204,0,0.22); border-radius: 14px;
        padding: 10px 14px; display: flex; align-items: center; gap: 10px;
        backdrop-filter: blur(15px); box-shadow: 0 8px 32px rgba(0,0,0,0.35);
        white-space: nowrap; font-size: 0.78rem; color: rgba(255,255,255,0.82);
    }
    .kx-h-ci { font-size: 1.45rem; font-weight: 800; line-height: 1; }
    .kx-h-ci-btc { color: #f7931a; }
    .kx-h-ci-eth { color: #627eea; }
    .kx-h-ci-usdt { color: #26a17b; }
    .kx-h-ctag { font-size: 0.68rem; padding: 2px 8px; border-radius: 10px; margin-top: 3px; display: inline-flex; align-items: center; gap: 2px; }
    .kx-h-cup { background: rgba(0,204,0,0.14); color: #00cc00; }
    .kx-h-cst { background: rgba(38,161,123,0.14); color: #26a17b; }
    .kx-h-c1 { top: -22px; right: -15px; animation: kx-fc 3s ease-in-out infinite; }
    .kx-h-c2 { bottom: 10px; right: -20px; animation: kx-fc 3.6s ease-in-out infinite 0.5s; }
    .kx-h-c3 { bottom: -18px; left: -15px; animation: kx-fc 4.2s ease-in-out infinite 1s; }
    @keyframes kx-fc { 0%,100%{transform:translateY(0) rotate(0)} 50%{transform:translateY(-8px) rotate(2deg)} }
    /* Scroll cue */
    .kx-h-scroll { position: absolute; bottom: 28px; left: 50%; transform: translateX(-50%); z-index: 2; }
    .kx-h-scroll-ring { width: 28px; height: 46px; border: 2px solid rgba(0,204,0,0.4); border-radius: 14px; position: relative; }
    .kx-h-scroll-ring::after { content:''; position:absolute; top:6px; left:50%; transform:translateX(-50%); width:5px; height:8px; background:#00cc00; border-radius:3px; animation:kx-sd 2s ease-in-out infinite; }
    @keyframes kx-sd { 0%{opacity:1;top:6px} 100%{opacity:0;top:24px} }
    /* Light mode */
    [data-bs-theme="light"] .kx-hero { background: #f4faf4; }
    [data-bs-theme="light"] .kx-h-grid { background-image: linear-gradient(rgba(0,153,0,0.05) 1px,transparent 1px),linear-gradient(90deg,rgba(0,153,0,0.05) 1px,transparent 1px); background-size:60px 60px; }
    [data-bs-theme="light"] .kx-h-title { color: #0a1a0a; }
    [data-bs-theme="light"] .kx-h-sub { color: rgba(0,0,0,0.6); }
    [data-bs-theme="light"] .kx-h-coin { background: rgba(255,255,255,0.97); }
    [data-bs-theme="light"] .kx-h-tbadge { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.1); color: #444; }
    /* Responsive */
    @media (max-width: 991.98px) {
        .kx-h-content { text-align: center; }
        .kx-h-badge { margin-left: auto; margin-right: auto; }
        .kx-h-sub { margin-left: auto; margin-right: auto; }
        .kx-h-stats { justify-content: center; }
        .kx-h-ctas { justify-content: center; }
        .kx-h-trust { justify-content: center; }
        .kx-h-visual { margin-top: 50px; }
        .kx-h-scroll { display: none; }
    }
    @media (max-width: 575.98px) {
        .kx-h-coin { display: none; }
        .kx-h-title { font-size: 2rem; }
        .kx-h-cta1, .kx-h-cta2 { padding: 12px 20px; font-size: 0.92rem; }
        .kx-hero { padding-top: 60px; min-height: auto; }
        .kx-h-content { padding: 50px 0 40px; }
        .kx-h-visual { padding: 20px 10px; margin-top: 30px; }
        .kx-h-mockup { padding: 18px; }
        .kx-h-sub { font-size: 0.95rem; }
        .kx-h-badge { font-size: 0.75rem; padding: 5px 13px; }
    }
    @media (max-width: 400px) {
        .kx-h-title { font-size: 1.75rem; }
        .kx-h-stats { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 8px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .kx-h-stats::-webkit-scrollbar { display: none; }
        .kx-h-sdiv { flex-shrink: 0; }
        .kx-h-stat { flex-shrink: 0; min-width: 65px; }
        .kx-h-ctas { flex-direction: column; align-items: stretch; }
        .kx-h-cta1, .kx-h-cta2 { justify-content: center; width: 100%; }
        .kx-h-trust { gap: 7px; }
        .kx-h-tbadge { padding: 6px 10px; font-size: 0.72rem; }
    }
    </style>

    {{-- Hero: use CSS keyframe animations so content is visible immediately without waiting for AOS.js --}}
    <style>
    @keyframes kxFadeUp{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:none}}
    @keyframes kxZoomIn{from{opacity:0;transform:scale(.93)}to{opacity:1;transform:none}}
    .kx-hero-anim-1{animation:kxFadeUp .55s ease both}
    .kx-hero-anim-2{animation:kxFadeUp .55s .1s ease both}
    .kx-hero-anim-3{animation:kxFadeUp .55s .18s ease both}
    .kx-hero-anim-4{animation:kxFadeUp .55s .26s ease both}
    .kx-hero-anim-5{animation:kxFadeUp .55s .34s ease both}
    .kx-hero-anim-6{animation:kxZoomIn .65s .18s ease both}
    </style>

    <section id="hero" class="kx-hero">
        <div class="kx-h-grid"></div>
        <div class="kx-h-orb1"></div>
        <div class="kx-h-orb2"></div>

        <div class="container kx-h-content">
            <div class="row align-items-center">
                <!-- Left: Text -->
                <div class="col-lg-6">
                    <div class="kx-h-badge kx-hero-anim-1">
                        <span class="kx-h-dot"></span>
                        Nigeria's Premier Crypto Exchange
                    </div>
                    <h1 class="kx-h-title kx-hero-anim-2">
                        Trade Crypto<br>
                        <span class="kx-h-grad">Instantly</span> &amp; Securely
                    </h1>
                    <p class="kx-h-sub kx-hero-anim-3">
                        Buy, sell &amp; exchange BTC, USDT, ETH, LTC and XRP to NGN at the best rates. Fast settlements, zero hidden fees, 24/7 support.
                    </p>
                    <!-- Stats -->
                    <div class="kx-h-stats kx-hero-anim-4">
                        <div class="kx-h-stat">
                            <span class="kx-h-snum">3K+</span>
                            <span class="kx-h-slbl">Happy Traders</span>
                        </div>
                        <div class="kx-h-sdiv"></div>
                        <div class="kx-h-stat">
                            <span class="kx-h-snum">90K+</span>
                            <span class="kx-h-slbl">Trades Done</span>
                        </div>
                        <div class="kx-h-sdiv"></div>
                        <div class="kx-h-stat">
                            <span class="kx-h-snum">24/7</span>
                            <span class="kx-h-slbl">Support</span>
                        </div>
                        <div class="kx-h-sdiv"></div>
                        <div class="kx-h-stat">
                            <span class="kx-h-snum">No KYC</span>
                            <span class="kx-h-slbl">Required</span>
                        </div>
                    </div>
                    <!-- CTAs -->
                    <div class="kx-h-ctas kx-hero-anim-5">
                        @auth
                        <a href="{{ url('/dashboard') }}" class="kx-h-cta1">
                            <i class="bi bi-grid-1x2-fill"></i>Go to Dashboard<i class="bi bi-arrow-right"></i>
                        </a>
                        @else
                        <a href="{{ route('register') }}" class="kx-h-cta1">
                            <i class="bi bi-rocket-takeoff-fill"></i>Start Trading Free<i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('login') }}" class="kx-h-cta2">
                            <i class="bi bi-box-arrow-in-right"></i>Sign In
                        </a>
                        @endauth
                    </div>
                    <!-- Trust badges -->
                    <div class="kx-h-trust kx-hero-anim-5">
                        <span class="kx-h-tbadge"><i class="bi bi-shield-check-fill" style="color:#00cc00"></i>Secure</span>
                        <span class="kx-h-tbadge"><i class="bi bi-lightning-charge-fill" style="color:#fbbf24"></i>Instant</span>
                        <span class="kx-h-tbadge"><i class="bi bi-star-fill" style="color:#f59e0b"></i>Best Rates</span>
                        <a href="https://wa.me/+2349016740523" class="kx-h-tbadge kx-h-twa" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>Quick Trade
                        </a>
                        <a href="{{ url('/rate') }}" class="kx-h-tbadge">
                            <i class="bi bi-graph-up-arrow" style="color:#00cc00"></i>Check Rates
                        </a>
                    </div>
                </div>

                <!-- Right: Visual -->
                <div class="col-lg-6 kx-h-visual kx-hero-anim-6">
                    <div class="kx-h-card-wrap">
                        <div class="kx-h-mockup">
                            <img src="{{ asset('Assests/images/kay-xchange-logo-mockup.png') }}" alt="KayXchange Platform" fetchpriority="high" loading="eager">
                        </div>
                        <!-- Floating coin badges -->
                        <div class="kx-h-coin kx-h-c1">
                            <span class="kx-h-ci kx-h-ci-btc">₿</span>
                            <div>
                                <div style="font-size:0.8rem;font-weight:600;">Bitcoin</div>
                                <div id="kx-btc-price" style="font-size:0.7rem;color:rgba(255,255,255,0.45);margin-bottom:2px;line-height:1.2">–</div>
                                <div class="kx-h-ctag kx-h-cup"><i class="bi bi-arrow-up-right"></i>BTC</div>
                            </div>
                        </div>
                        <div class="kx-h-coin kx-h-c2">
                            <span class="kx-h-ci kx-h-ci-eth">Ξ</span>
                            <div>
                                <div style="font-size:0.8rem;font-weight:600;">Ethereum</div>
                                <div id="kx-eth-price" style="font-size:0.7rem;color:rgba(255,255,255,0.45);margin-bottom:2px;line-height:1.2">–</div>
                                <div class="kx-h-ctag kx-h-cup"><i class="bi bi-arrow-up-right"></i>ETH</div>
                            </div>
                        </div>
                        <div class="kx-h-coin kx-h-c3">
                            <span class="kx-h-ci kx-h-ci-usdt">₮</span>
                            <div>
                                <div style="font-size:0.8rem;font-weight:600;">Tether</div>
                                <div style="font-size:0.7rem;color:rgba(255,255,255,0.45);margin-bottom:2px;line-height:1.2">$1.00</div>
                                <div class="kx-h-ctag kx-h-cst"><i class="bi bi-dash"></i>USDT</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="kx-h-scroll">
            <div class="kx-h-scroll-ring"></div>
        </div>
    </section><!-- End Hero -->


    <!-- ======= Live Crypto Ticker Ribbon ======= -->
    <style>
    .kx-ribbon-wrap {
        background: rgba(6,14,6,0.97);
        border-top: 1px solid rgba(0,204,0,0.12);
        border-bottom: 1px solid rgba(0,204,0,0.12);
        overflow: hidden;
        position: relative;
        height: 56px;
        display: flex;
        align-items: center;
    }
    /* fade edges */
    .kx-ribbon-wrap::before,
    .kx-ribbon-wrap::after {
        content: '';
        position: absolute;
        top: 0; bottom: 0;
        width: 60px;
        z-index: 2;
        pointer-events: none;
    }
    .kx-ribbon-wrap::before { left:0;  background: linear-gradient(to right, rgba(6,14,6,1), transparent); }
    .kx-ribbon-wrap::after  { right:0; background: linear-gradient(to left,  rgba(6,14,6,1), transparent); }

    .kx-ribbon-track {
        display: flex;
        align-items: center;
        /* width set by JS after duplication */
        animation: kxScroll 28s linear infinite;
        will-change: transform;
        gap: 0;
    }
    .kx-ribbon-wrap:hover .kx-ribbon-track { animation-play-state: paused; }

    @keyframes kxScroll {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    .kx-rib-item {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0 28px;
        height: 56px;
        border-right: 1px solid rgba(255,255,255,0.06);
        flex-shrink: 0;
        white-space: nowrap;
        cursor: default;
        transition: background 0.2s;
    }
    .kx-rib-item:hover { background: rgba(0,204,0,0.05); }

    .kx-rib-img { width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0; }
    .kx-rib-sym  { font-size: 0.82rem; font-weight: 700; color: #fff; line-height: 1.1; }
    .kx-rib-name { font-size: 0.62rem; color: rgba(255,255,255,0.32); }
    .kx-rib-price { font-size: 0.85rem; font-weight: 700; color: #fff; }
    .kx-rib-chg  { font-size: 0.67rem; font-weight: 600; padding: 2px 6px; border-radius: 5px; }
    .kx-tick-up  { color: #00cc00; background: rgba(0,204,0,0.12); }
    .kx-tick-dn  { color: #ef4444; background: rgba(239,68,68,0.12); }

    /* Skeleton */
    .kx-rib-skel { display:inline-flex;align-items:center;gap:10px;padding:0 28px;height:56px;border-right:1px solid rgba(255,255,255,0.06);flex-shrink:0; }
    .kx-sk-circle { width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.07);animation:kxRSkel 1.4s ease-in-out infinite; }
    .kx-sk-line   { border-radius:5px;background:rgba(255,255,255,0.07);animation:kxRSkel 1.4s ease-in-out infinite; }
    @keyframes kxRSkel { 0%,100%{opacity:0.35} 50%{opacity:0.7} }

    /* Light mode */
    [data-bs-theme="light"] .kx-ribbon-wrap {
        background: #f7fdf7;
        border-color: rgba(0,153,0,0.13);
    }
    [data-bs-theme="light"] .kx-ribbon-wrap::before { background: linear-gradient(to right, #f7fdf7, transparent); }
    [data-bs-theme="light"] .kx-ribbon-wrap::after  { background: linear-gradient(to left,  #f7fdf7, transparent); }
    [data-bs-theme="light"] .kx-rib-item { border-right-color: rgba(0,0,0,0.06); }
    [data-bs-theme="light"] .kx-rib-sym,
    [data-bs-theme="light"] .kx-rib-price { color: #0a1a0a; }
    [data-bs-theme="light"] .kx-rib-name  { color: rgba(0,0,0,0.38); }
    [data-bs-theme="light"] .kx-sk-circle,
    [data-bs-theme="light"] .kx-sk-line   { background: rgba(0,0,0,0.07); }

    /* Responsive — smaller padding on mobile */
    @media (max-width: 575.98px) {
        .kx-ribbon-wrap { height: 50px; }
        .kx-rib-item    { padding: 0 18px; height: 50px; gap: 8px; }
        .kx-rib-img     { width: 24px; height: 24px; }
        .kx-rib-sym     { font-size: 0.78rem; }
        .kx-rib-price   { font-size: 0.78rem; }
        @keyframes kxScroll { 0%{transform:translateX(0);} 100%{transform:translateX(-50%);} }
        /* slightly faster on mobile so it still feels alive */
        .kx-ribbon-track { animation-duration: 20s; }
    }
    </style>

    <div class="kx-ribbon-wrap" id="kx-ribbon-wrap">
        <div class="kx-ribbon-track" id="kx-ribbon-track">
            <!-- Skeleton placeholders shown while JS loads -->
            @for($i = 0; $i < 5; $i++)
            <div class="kx-rib-skel">
                <div class="kx-sk-circle"></div>
                <div>
                    <div class="kx-sk-line" style="width:38px;height:11px;margin-bottom:5px"></div>
                    <div class="kx-sk-line" style="width:55px;height:9px"></div>
                </div>
                <div>
                    <div class="kx-sk-line" style="width:65px;height:11px;margin-bottom:5px"></div>
                    <div class="kx-sk-line" style="width:40px;height:9px"></div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    <script>
    (function(){
        var COINS = [
            {id:'bitcoin',          sym:'BTC',  name:'Bitcoin',   img:'https://assets.coingecko.com/coins/images/1/thumb/bitcoin.png'},
            {id:'ethereum',         sym:'ETH',  name:'Ethereum',  img:'https://assets.coingecko.com/coins/images/279/thumb/ethereum.png'},
            {id:'tether',           sym:'USDT', name:'Tether',    img:'https://assets.coingecko.com/coins/images/325/thumb/Tether.png'},
            {id:'binancecoin',      sym:'BNB',  name:'BNB',       img:'https://assets.coingecko.com/coins/images/825/thumb/bnb-icon2_2x.png'},
            {id:'litecoin',         sym:'LTC',  name:'Litecoin',  img:'https://assets.coingecko.com/coins/images/2/thumb/litecoin.png'},
            {id:'solana',           sym:'SOL',  name:'Solana',    img:'https://assets.coingecko.com/coins/images/4128/thumb/solana.png'},
            {id:'ripple',           sym:'XRP',  name:'XRP',       img:'https://assets.coingecko.com/coins/images/44/thumb/xrp-symbol-white-128.png'},
            {id:'dogecoin',         sym:'DOGE', name:'Dogecoin',  img:'https://assets.coingecko.com/coins/images/5/thumb/dogecoin.png'},
            {id:'cardano',          sym:'ADA',  name:'Cardano',   img:'https://assets.coingecko.com/coins/images/975/thumb/cardano.png'},
            {id:'matic-network',    sym:'POL',  name:'POL',       img:'https://assets.coingecko.com/coins/images/4713/thumb/polygon.png'}
        ];

        function fmtPrice(n){
            if(n>=1000) return '$'+n.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
            if(n>=1)    return '$'+n.toFixed(2);
            return '$'+n.toFixed(4);
        }

        function buildItems(data){
            var map={};
            data.forEach(function(c){ map[c.id]=c; });
            return COINS.map(function(c){
                var d=map[c.id]||{};
                var price = d.current_price||0;
                var chg   = d.price_change_percentage_24h||0;
                var cls   = chg>=0 ? 'kx-tick-up' : 'kx-tick-dn';
                var arrow = chg>=0 ? '▲' : '▼';
                return '<div class="kx-rib-item">'
                    +'<img class="kx-rib-img" src="'+c.img+'" alt="'+c.sym+'" loading="lazy">'
                    +'<div><div class="kx-rib-sym">'+c.sym+'</div><div class="kx-rib-name">'+c.name+'</div></div>'
                    +'<div style="text-align:right">'
                    +  '<div class="kx-rib-price">'+fmtPrice(price)+'</div>'
                    +  '<span class="kx-rib-chg '+cls+'">'+arrow+' '+Math.abs(chg).toFixed(2)+'%</span>'
                    +'</div>'
                    +'</div>';
            }).join('');
        }

        function launchRibbon(html){
            var track = document.getElementById('kx-ribbon-track');
            if(!track) return;
            // duplicate for seamless loop
            track.innerHTML = html + html;
            track.style.animation = 'none';
            // force reflow
            void track.offsetWidth;
            track.style.animation = '';
        }

        function loadFallback(){
            var fallback = [
                {id:'bitcoin',       current_price:71524,  price_change_percentage_24h:-1.86},
                {id:'ethereum',      current_price:2205.9, price_change_percentage_24h:-1.56},
                {id:'tether',        current_price:1.00,   price_change_percentage_24h: 0.00},
                {id:'binancecoin',   current_price:595.01, price_change_percentage_24h:-1.76},
                {id:'litecoin',      current_price:53.80,  price_change_percentage_24h:-1.39},
                {id:'solana',        current_price:148.20, price_change_percentage_24h: 2.14},
                {id:'ripple',        current_price:0.52,   price_change_percentage_24h:-0.88},
                {id:'dogecoin',      current_price:0.14,   price_change_percentage_24h: 1.22},
                {id:'cardano',       current_price:0.46,   price_change_percentage_24h:-0.55},
                {id:'matic-network', current_price:0.72,   price_change_percentage_24h: 0.63}
            ];
            launchRibbon(buildItems(fallback));
        }

        var ids = COINS.map(function(c){return c.id;}).join(',');
        var url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids='+ids+'&order=market_cap_desc&per_page=20&page=1&sparkline=false&price_change_percentage=24h';

        fetch(url)
            .then(function(r){ return r.ok ? r.json() : Promise.reject(); })
            .then(function(data){ launchRibbon(buildItems(data)); })
            .catch(loadFallback);
    })();
    </script>

    <!-- ======= Telegram Banner ======= -->
    <style>
    .kx-tg-banner {
        background: linear-gradient(135deg, #0a1a0a 0%, #061006 100%);
        border-top: 1px solid rgba(0,136,204,0.12);
        border-bottom: 1px solid rgba(0,136,204,0.12);
        padding: 40px 0;
        position: relative;
        overflow: hidden;
    }
    .kx-tg-banner::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(0,136,204,0.09) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .kx-tg-banner::after {
        content: '';
        position: absolute;
        bottom: -60px; left: -60px;
        width: 240px; height: 240px;
        background: radial-gradient(circle, rgba(0,204,0,0.06) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .kx-tg-icon-wrap {
        width: 60px; height: 60px;
        background: linear-gradient(135deg, #0088cc, #005fa3);
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; color: #fff;
        box-shadow: 0 8px 24px rgba(0,136,204,0.35);
        flex-shrink: 0;
    }
    .kx-tg-label { font-size: 0.68rem; font-weight: 600; color: #0e9cda; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 6px; }
    .kx-tg-title { font-size: clamp(1.1rem, 2.5vw, 1.55rem); font-weight: 800; color: #fff; margin-bottom: 10px; line-height: 1.25; }
    .kx-tg-sub { font-size: 0.875rem; color: rgba(255,255,255,0.5); line-height: 1.65; max-width: 480px; }
    .kx-tg-btn-start {
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg, #0088cc, #005fa3);
        color: #fff !important; text-decoration: none !important;
        padding: 13px 28px; border-radius: 50px;
        font-weight: 700; font-size: 0.9rem;
        box-shadow: 0 6px 22px rgba(0,136,204,0.38);
        transition: all 0.25s ease;
        border: none; cursor: pointer;
    }
    .kx-tg-btn-start:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(0,136,204,0.52); color: #fff !important; }
    .kx-tg-btn-wa {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(37,211,102,0.12);
        border: 1.5px solid rgba(37,211,102,0.3);
        color: #25d366 !important; text-decoration: none !important;
        padding: 13px 24px; border-radius: 50px;
        font-weight: 600; font-size: 0.9rem;
        transition: all 0.25s ease;
    }
    .kx-tg-btn-wa:hover { background: rgba(37,211,102,0.22); border-color: #25d366; transform: translateY(-2px); }
    .kx-tg-features { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
    .kx-tg-feat { display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; color: rgba(255,255,255,0.5); }
    .kx-tg-feat i { color: #00cc00; font-size: 0.8rem; }
    [data-bs-theme="light"] .kx-tg-banner { background: linear-gradient(135deg,#f0f8ff 0%,#e8f5ff 100%); border-color: rgba(0,136,204,0.15); }
    [data-bs-theme="light"] .kx-tg-title { color: #0a1a0a; }
    [data-bs-theme="light"] .kx-tg-sub { color: rgba(0,0,0,0.52); }
    [data-bs-theme="light"] .kx-tg-feat { color: rgba(0,0,0,0.45); }
    @media (max-width: 767px) {
        .kx-tg-banner { padding: 32px 0; }
        .kx-tg-banner .d-flex.gap-3 { flex-direction: column; align-items: flex-start !important; }
    }
    </style>

    <section class="kx-tg-banner">
        <div class="container" style="position:relative;z-index:1">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="d-flex align-items-start gap-3">
                        <div class="kx-tg-icon-wrap"><i class="bi bi-telegram"></i></div>
                        <div>
                            <div class="kx-tg-label">Telegram Bot</div>
                            @auth
                            <h2 class="kx-tg-title">Get Instant Trade Notifications</h2>
                            <p class="kx-tg-sub">Never miss a trade update. Connect with our bot for real-time notifications on transactions, rate changes, and account activity.</p>
                            @else
                            <h2 class="kx-tg-title">Trade Instantly via Telegram</h2>
                            <p class="kx-tg-sub">Start trading crypto directly through our Telegram bot. Quick, secure, and convenient — no app download needed.</p>
                            @endauth
                            <div class="kx-tg-features">
                                <span class="kx-tg-feat"><i class="bi bi-check-circle-fill"></i>Real-time alerts</span>
                                <span class="kx-tg-feat"><i class="bi bi-check-circle-fill"></i>Instant trade execution</span>
                                <span class="kx-tg-feat"><i class="bi bi-check-circle-fill"></i>Rate notifications</span>
                                <span class="kx-tg-feat"><i class="bi bi-check-circle-fill"></i>24/7 available</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-lg-end align-items-sm-center">
                        <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" rel="noopener" class="kx-tg-btn-start">
                            <i class="bi bi-telegram"></i>Start Bot Now
                        </a>
                        <a href="https://wa.me/+2349016740523" target="_blank" rel="noopener" class="kx-tg-btn-wa">
                            <i class="bi bi-whatsapp"></i>WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <main id="main">

    <!-- ======= Redesigned Sections CSS ======= -->
    <style>
    /* ── Shared ── */
    .kx-sec { padding: 80px 0; position: relative; }
    .kx-sec-dark { background: #070d07; }
    .kx-sec-alt  { background: #060e06; }
    .kx-sec-tag  { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #00cc00; display: inline-flex; align-items: center; gap: 7px; margin-bottom: 12px; }
    .kx-sec-tag::before { content:''; width:18px; height:2px; background:#00cc00; border-radius:2px; }
    .kx-sec-h { font-size: clamp(1.6rem,3.5vw,2.4rem); font-weight: 800; color: #fff; line-height: 1.2; margin-bottom: 14px; font-family:'Poppins',sans-serif; }
    .kx-sec-sub { font-size: 0.95rem; color: rgba(255,255,255,0.48); line-height: 1.75; max-width: 560px; }
    .kx-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; transition: border-color 0.25s, box-shadow 0.25s; }
    .kx-card:hover { border-color: rgba(0,204,0,0.22); box-shadow: 0 8px 36px rgba(0,204,0,0.08); }

    /* Light mode overrides */
    [data-bs-theme="light"] .kx-sec-dark { background: #f4faf4; }
    [data-bs-theme="light"] .kx-sec-alt  { background: #edf7ed; }
    [data-bs-theme="light"] .kx-sec-h    { color: #0a1a0a; }
    [data-bs-theme="light"] .kx-sec-sub  { color: rgba(0,0,0,0.52); }
    [data-bs-theme="light"] .kx-card     { background: rgba(0,0,0,0.02); border-color: rgba(0,0,0,0.07); }

    /* ── About ── */
    .kx-about-img { border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
    .kx-about-img .carousel-item img { border-radius: 20px; height: 340px; object-fit: cover; }
    .kx-about-label { background: rgba(0,204,0,0.1); border: 1px solid rgba(0,204,0,0.25); border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
    .kx-about-label i { font-size: 1.4rem; color: #00cc00; flex-shrink:0; }
    .kx-about-label-t { font-size: 0.8rem; color: rgba(255,255,255,0.45); }
    .kx-about-label-v { font-size: 1.1rem; font-weight: 700; color: #00cc00; }
    [data-bs-theme="light"] .kx-about-label-t { color: rgba(0,0,0,0.45); }
    [data-bs-theme="light"] .kx-about-label-v { color: #007a0c; }
    .kx-about-btn { display: inline-flex; align-items: center; gap: 8px; background: rgba(0,204,0,0.08); border: 1.5px solid rgba(0,204,0,0.35); color: #00cc00 !important; text-decoration: none !important; padding: 12px 26px; border-radius: 50px; font-weight: 600; font-size: 0.88rem; transition: all 0.25s; }
    .kx-about-btn:hover { background: rgba(0,204,0,0.16); border-color: #00cc00; transform: translateY(-2px); color: #00cc00 !important; }

    /* ── Why ── */
    .kx-why-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 30px 28px; height: 100%; transition: all 0.25s; }
    .kx-why-card:hover { border-color: rgba(0,204,0,0.28); transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,204,0,0.1); }
    .kx-why-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 18px; }
    .kx-why-i1 { background: rgba(0,204,0,0.12); color: #00cc00; }
    .kx-why-i2 { background: rgba(167,139,250,0.12); color: #a78bfa; }
    .kx-why-i3 { background: rgba(251,191,36,0.12); color: #fbbf24; }
    .kx-why-t { font-size: 1.05rem; font-weight: 700; color: #fff; margin-bottom: 10px; }
    .kx-why-d { font-size: 0.85rem; color: rgba(255,255,255,0.45); line-height: 1.7; margin: 0; }
    [data-bs-theme="light"] .kx-why-card { background: #fff; border-color: rgba(0,0,0,0.07); }
    [data-bs-theme="light"] .kx-why-t { color: #0a1a0a; }
    [data-bs-theme="light"] .kx-why-d { color: rgba(0,0,0,0.5); }

    /* ── Stats ── */
    .kx-stats-strip { background: linear-gradient(135deg,#00a010 0%,#006f0a 100%); padding: 44px 0; border-top: 1px solid rgba(0,204,0,0.2); border-bottom: 1px solid rgba(0,204,0,0.2); }
    .kx-stat-item { text-align: center; padding: 0 20px; }
    .kx-stat-num  { font-size: clamp(2rem,4vw,2.8rem); font-weight: 900; color: #fff; line-height: 1; }
    .kx-stat-lbl  { font-size: 0.78rem; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.6px; margin-top: 8px; }
    .kx-stat-div  { width: 1px; background: rgba(255,255,255,0.2); align-self: stretch; }
    @media(max-width:575.98px){ .kx-stat-div{display:none;} .kx-stat-item{padding:14px 0;} }

    /* ── How It Works ── */
    .kx-how-step { text-align: center; padding: 24px 20px; position: relative; }
    .kx-how-num { width: 52px; height: 52px; border-radius: 50%; background: rgba(0,204,0,0.12); border: 2px solid rgba(0,204,0,0.3); color: #00cc00; font-size: 1.1rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; }
    .kx-how-arrow { position: absolute; top: 38px; right: -16px; color: rgba(0,204,0,0.3); font-size: 1.4rem; z-index: 1; }
    .kx-how-t { font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 8px; }
    .kx-how-d { font-size: 0.82rem; color: rgba(255,255,255,0.42); line-height: 1.65; }
    [data-bs-theme="light"] .kx-how-t { color: #0a1a0a; }
    [data-bs-theme="light"] .kx-how-d { color: rgba(0,0,0,0.48); }

    /* ── Trade (Services) ── */
    .kx-trade-card { border-radius: 20px; padding: 28px 24px; display: flex; flex-direction: column; gap: 14px; transition: all 0.25s; position: relative; overflow: hidden; }
    .kx-trade-card::before { content:''; position:absolute; top:-40px; right:-40px; width:120px; height:120px; border-radius:50%; opacity:0.06; pointer-events:none; }
    .kx-trade-btc { background: rgba(247,147,26,0.08); border: 1px solid rgba(247,147,26,0.2); }
    .kx-trade-btc::before { background: #f7931a; }
    .kx-trade-eth { background: rgba(98,126,234,0.08); border: 1px solid rgba(98,126,234,0.2); }
    .kx-trade-eth::before { background: #627eea; }
    .kx-trade-usdt { background: rgba(38,161,123,0.08); border: 1px solid rgba(38,161,123,0.2); }
    .kx-trade-usdt::before { background: #26a17b; }
    .kx-trade-card:hover { transform: translateY(-4px); box-shadow: 0 14px 44px rgba(0,0,0,0.28); }
    .kx-trade-icon img { width: 42px; height: 42px; }
    .kx-trade-name { font-size: 1.1rem; font-weight: 800; color: #fff; }
    .kx-trade-desc { font-size: 0.82rem; color: rgba(255,255,255,0.42); line-height: 1.6; flex: 1; }
    .kx-trade-btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px; border-radius: 50px; font-weight: 700; font-size: 0.82rem; text-decoration: none !important; transition: all 0.22s; align-self: flex-start; }
    .kx-trade-btn-btc  { background: rgba(247,147,26,0.15); color: #f7931a !important; border: 1.5px solid rgba(247,147,26,0.35); }
    .kx-trade-btn-btc:hover  { background: rgba(247,147,26,0.28); }
    .kx-trade-btn-eth  { background: rgba(98,126,234,0.15); color: #627eea !important; border: 1.5px solid rgba(98,126,234,0.35); }
    .kx-trade-btn-eth:hover  { background: rgba(98,126,234,0.28); }
    .kx-trade-btn-usdt { background: rgba(38,161,123,0.15); color: #26a17b !important; border: 1.5px solid rgba(38,161,123,0.35); }
    .kx-trade-btn-usdt:hover { background: rgba(38,161,123,0.28); }

    /* ── Reviews (Testimonials) ── */
    .kx-review-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 28px 24px; height: 100%; display: flex; flex-direction: column; gap: 16px; transition: all 0.25s; }
    .kx-review-card:hover { border-color: rgba(0,204,0,0.22); transform: translateY(-3px); }
    .kx-review-stars { color: #fbbf24; font-size: 0.85rem; letter-spacing: 2px; }
    .kx-review-text { font-size: 0.88rem; color: rgba(255,255,255,0.6); line-height: 1.75; flex: 1; font-style: italic; }
    .kx-review-text::before { content: '"'; font-size: 2rem; color: rgba(0,204,0,0.25); line-height: 0.6; display: block; margin-bottom: 8px; }
    .kx-review-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(0,204,0,0.25); }
    .kx-review-name { font-size: 0.88rem; font-weight: 700; color: #fff; }
    .kx-review-role { font-size: 0.72rem; color: rgba(255,255,255,0.35); }
    [data-bs-theme="light"] .kx-review-card  { background:#fff; border-color:rgba(0,0,0,0.07); }
    [data-bs-theme="light"] .kx-review-text  { color:rgba(0,0,0,0.55); }
    [data-bs-theme="light"] .kx-review-name  { color:#0a1a0a; }
    [data-bs-theme="light"] .kx-review-role  { color:rgba(0,0,0,0.38); }

    /* ── Blog ── */
    .kx-blog-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 24px; height: 100%; display: flex; flex-direction: column; gap: 12px; transition: all 0.25s; }
    .kx-blog-card:hover { border-color: rgba(0,204,0,0.22); transform: translateY(-3px); }
    .kx-blog-date { font-size: 0.68rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.6px; }
    .kx-blog-title { font-size: 0.95rem; font-weight: 700; color: #fff; line-height: 1.45; flex: 1; }
    .kx-blog-link { display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 600; color: #00cc00 !important; text-decoration: none !important; transition: gap 0.2s; }
    .kx-blog-link:hover { gap: 10px; }
    [data-bs-theme="light"] .kx-blog-card  { background:#fff; border-color:rgba(0,0,0,0.07); }
    [data-bs-theme="light"] .kx-blog-title { color:#0a1a0a; }
    [data-bs-theme="light"] .kx-blog-date  { color:rgba(0,0,0,0.35); }

    /* ── Newsletter ── */
    .kx-nl { background: linear-gradient(135deg, #04120a 0%, #061006 100%); border-top: 1px solid rgba(0,204,0,0.1); border-bottom: 1px solid rgba(0,204,0,0.1); padding: 64px 0; position: relative; overflow: hidden; }
    .kx-nl::before { content:''; position:absolute; top:-100px; left:-60px; width:360px; height:360px; background:radial-gradient(circle,rgba(0,204,0,0.07) 0%,transparent 70%); border-radius:50%; pointer-events:none; }
    .kx-nl::after  { content:''; position:absolute; bottom:-80px; right:-40px; width:280px; height:280px; background:radial-gradient(circle,rgba(0,136,204,0.06) 0%,transparent 70%); border-radius:50%; pointer-events:none; }
    .kx-nl-inner  { position:relative;z-index:1; }
    .kx-nl h2 { font-size: clamp(1.5rem,3vw,2rem); font-weight: 800; color: #fff; margin-bottom: 10px; }
    .kx-nl-sub { font-size: 0.9rem; color: rgba(255,255,255,0.45); margin-bottom: 28px; max-width: 460px; line-height: 1.7; }
    .kx-nl-form { display: flex; gap: 10px; max-width: 520px; }
    .kx-nl-input { flex: 1; background: rgba(255,255,255,0.06); border: 1.5px solid rgba(255,255,255,0.1); border-radius: 50px; color: #fff; font-size: 0.88rem; padding: 13px 22px; outline: none; font-family:'Poppins',sans-serif; transition: border-color 0.22s, box-shadow 0.22s; }
    .kx-nl-input::placeholder { color: rgba(255,255,255,0.3); }
    .kx-nl-input:focus { border-color: rgba(0,204,0,0.48); box-shadow: 0 0 0 3px rgba(0,204,0,0.1); }
    .kx-nl-btn { background: linear-gradient(135deg,#00cc00,#007a0c); color:#fff !important; border:none; border-radius:50px; padding:13px 28px; font-weight:700; font-size:0.88rem; cursor:pointer; transition:all 0.25s; white-space:nowrap; box-shadow:0 4px 18px rgba(0,204,0,0.3); display:inline-flex;align-items:center;gap:7px; }
    .kx-nl-btn:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(0,204,0,0.42); }
    .kx-nl-perks { display:flex;flex-wrap:wrap;gap:14px;margin-top:18px; }
    .kx-nl-perk { font-size:0.75rem;color:rgba(255,255,255,0.38);display:flex;align-items:center;gap:5px; }
    .kx-nl-perk i { color:#00cc00; }
    @media(max-width:575.98px){
        .kx-nl-form { flex-direction:column; }
        .kx-nl-btn  { justify-content:center; }
    }
    [data-bs-theme="light"] .kx-nl { background:linear-gradient(135deg,#f0fdf4,#e8f5e9); border-color:rgba(0,153,0,0.12); }
    [data-bs-theme="light"] .kx-nl h2 { color:#0a1a0a; }
    [data-bs-theme="light"] .kx-nl-sub { color:rgba(0,0,0,0.5); }
    [data-bs-theme="light"] .kx-nl-input { background:#fff; border-color:rgba(0,0,0,0.12); color:#0a1a0a; }
    [data-bs-theme="light"] .kx-nl-input::placeholder { color:rgba(0,0,0,0.32); }
    [data-bs-theme="light"] .kx-nl-perk { color:rgba(0,0,0,0.42); }

    /* ── Footer ── */
    .kx-footer { background: #030a03; padding: 64px 0 0; border-top: 1px solid rgba(0,204,0,0.1); position: relative; }
    .kx-footer::before { content:''; position:absolute; top:0;left:0;right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(0,204,0,0.35),transparent); }
    .kx-footer-logo { display:flex;align-items:center;gap:10px;text-decoration:none !important;margin-bottom:16px; }
    .kx-footer-logo img { width:38px;height:38px;border-radius:10px;box-shadow:0 0 16px rgba(0,204,0,0.35); }
    .kx-footer-logo span { font-size:1.25rem;font-weight:800;color:#fff; }
    .kx-footer-logo span b { color:#00cc00; }
    .kx-footer-desc { font-size:0.83rem;color:rgba(255,255,255,0.38);line-height:1.75;margin-bottom:22px;max-width:300px; }
    .kx-footer-social { display:flex;gap:10px;flex-wrap:wrap; }
    .kx-footer-soc { width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.5);display:flex;align-items:center;justify-content:center;font-size:1rem;transition:all 0.22s;text-decoration:none; }
    .kx-footer-soc:hover { background:rgba(0,204,0,0.15);border-color:rgba(0,204,0,0.38);color:#00cc00;transform:translateY(-2px); }
    .kx-footer-soc.tw:hover { background:rgba(29,161,242,0.15);border-color:rgba(29,161,242,0.38);color:#1da1f2; }
    .kx-footer-soc.wa:hover { background:rgba(37,211,102,0.15);border-color:rgba(37,211,102,0.38);color:#25d366; }
    .kx-footer-soc.ig:hover { background:rgba(225,48,108,0.15);border-color:rgba(225,48,108,0.38);color:#e1306c; }
    .kx-footer-soc.tg:hover { background:rgba(0,136,204,0.15);border-color:rgba(0,136,204,0.38);color:#0088cc; }
    .kx-footer-h { font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#00cc00;margin-bottom:18px;position:relative;padding-bottom:10px; }
    .kx-footer-h::after { content:'';position:absolute;bottom:0;left:0;width:24px;height:2px;background:#00cc00;border-radius:2px; }
    .kx-footer-links { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px; }
    .kx-footer-links a { font-size:0.83rem;color:rgba(255,255,255,0.42);text-decoration:none;transition:all 0.2s;display:flex;align-items:center;gap:6px; }
    .kx-footer-links a i { font-size:0.65rem;color:rgba(0,204,0,0.5);transition:transform 0.2s; }
    .kx-footer-links a:hover { color:#00cc00;padding-left:4px; }
    .kx-footer-links a:hover i { transform:translateX(3px);color:#00cc00; }
    .kx-footer-contact { display:flex;flex-direction:column;gap:14px; }
    .kx-footer-ci { display:flex;align-items:flex-start;gap:12px; }
    .kx-footer-ci-icon { width:34px;height:34px;border-radius:10px;background:rgba(0,204,0,0.1);border:1px solid rgba(0,204,0,0.2);color:#00cc00;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.9rem; }
    .kx-footer-ci-t { font-size:0.68rem;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px; }
    .kx-footer-ci-v { font-size:0.83rem;color:rgba(255,255,255,0.65); }
    .kx-footer-ci-v a { color:rgba(255,255,255,0.65);text-decoration:none;transition:color 0.2s; }
    .kx-footer-ci-v a:hover { color:#00cc00; }
    .kx-footer-bottom { margin-top:44px;padding:20px 0;border-top:1px solid rgba(255,255,255,0.06); }
    .kx-footer-copy { font-size:0.78rem;color:rgba(255,255,255,0.28); }
    .kx-footer-copy a { color:rgba(0,204,0,0.6);text-decoration:none;transition:color 0.2s; }
    .kx-footer-copy a:hover { color:#00cc00; }
    .kx-footer-badges { display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end; }
    .kx-footer-badge { font-size:0.68rem;font-weight:600;color:rgba(0,204,0,0.6);background:rgba(0,204,0,0.08);border:1px solid rgba(0,204,0,0.18);border-radius:20px;padding:4px 12px;display:inline-flex;align-items:center;gap:5px; }
    /* Back to top */
    .kx-btt { position:fixed;bottom:80px;right:20px;width:42px;height:42px;background:linear-gradient(135deg,#00cc00,#007a0c);color:#fff;border:none;border-radius:50%;box-shadow:0 4px 16px rgba(0,204,0,0.35);display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:990;opacity:0;transform:translateY(10px);transition:all 0.3s;pointer-events:none;font-size:1rem;text-decoration:none; }
    .kx-btt.kx-btt-show { opacity:1;transform:translateY(0);pointer-events:all; }
    .kx-btt:hover { transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,204,0,0.48);color:#fff; }
    @media(max-width:575.98px){
        .kx-sec { padding:56px 0; }
        .kx-footer-badges { justify-content:flex-start; }
        .kx-footer-copy { text-align:center;margin-bottom:10px; }
    }
    [data-bs-theme="light"] .kx-footer { background:#111; }
    [data-bs-theme="light"] .kx-footer-desc { color:rgba(255,255,255,0.45); }
    [data-bs-theme="light"] .kx-footer-links a { color:rgba(255,255,255,0.5); }
    [data-bs-theme="light"] .kx-footer-ci-v { color:rgba(255,255,255,0.7); }
    [data-bs-theme="light"] .kx-footer-ci-v a { color:rgba(255,255,255,0.7); }
    [data-bs-theme="light"] .kx-footer-copy { color:rgba(255,255,255,0.35); }
    </style>

    <!-- ======= About Section ======= -->
    <section class="kx-sec kx-sec-dark" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="kx-sec-tag"><i class="bi bi-info-circle-fill"></i>About Us</div>
                    <h2 class="kx-sec-h">{{ $siteContent['about_heading'] ?? 'About KayXchange' }}</h2>
                    <p class="kx-sec-sub mb-4">{{ $siteContent['about_subheading'] ?? 'Nigeria\'s most trusted platform.' }}</p>
                    <p style="font-size:0.88rem;color:rgba(255,255,255,0.48);line-height:1.8;margin-bottom:28px">{{ $siteContent['about_description'] ?? 'Our platform provides a seamless, secure experience for clients exchanging digital assets.' }}</p>
                    <div class="row g-3 mb-28">
                        <div class="col-6">
                            <div class="kx-about-label">
                                <i class="bi bi-shield-fill-check"></i>
                                <div><div class="kx-about-label-t">Platform Status</div><div class="kx-about-label-v">Verified & Secure</div></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="kx-about-label">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <div><div class="kx-about-label-t">Settlement</div><div class="kx-about-label-v">Instant Payouts</div></div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ url('/about') }}" class="kx-about-btn mt-4 d-inline-flex">
                        <i class="bi bi-arrow-right-circle-fill"></i>Learn More About Us
                    </a>
                </div>
                <div class="col-lg-6" data-aos="zoom-in">
                    <div class="kx-about-img">
                        <div id="kxAboutCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img class="d-block w-100" src="{{ asset('Assests/images/carousel/ourratedeybuga.jpeg') }}" alt="Our Rates">
                                </div>
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="{{ asset('Assests/images/carousel/Youdontneedtogofar.jpeg') }}" alt="Trade Easy">
                                </div>
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="{{ asset('Assests/images/carousel/neednairaforexchange.jpeg') }}" alt="Need Naira">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#kxAboutCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#kxAboutCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= Stats Strip ======= -->
    <div class="kx-stats-strip">
        <div class="container">
            <div class="d-flex justify-content-center align-items-stretch gap-0 flex-wrap">
                <div class="kx-stat-item">
                    <div class="kx-stat-num" data-purecounter-start="0" data-purecounter-end="{{ $siteContent['stat_clients'] ?? 3000 }}" data-purecounter-duration="1" class="purecounter kx-stat-num">{{ $siteContent['stat_clients'] ?? '3000' }}</div>
                    <div class="kx-stat-lbl">Happy Traders</div>
                </div>
                <div class="kx-stat-div d-none d-sm-block"></div>
                <div class="kx-stat-item">
                    <div class="kx-stat-num" data-purecounter-start="0" data-purecounter-end="{{ $siteContent['stat_trades'] ?? 90000 }}" data-purecounter-duration="1" class="purecounter kx-stat-num">{{ $siteContent['stat_trades'] ?? '90,000' }}</div>
                    <div class="kx-stat-lbl">Total Trades</div>
                </div>
                <div class="kx-stat-div d-none d-sm-block"></div>
                <div class="kx-stat-item">
                    <div class="kx-stat-num">24/7</div>
                    <div class="kx-stat-lbl">Support Available</div>
                </div>
                <div class="kx-stat-div d-none d-sm-block"></div>
                <div class="kx-stat-item">
                    <div class="kx-stat-num">0</div>
                    <div class="kx-stat-lbl">Hidden Fees</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======= Why Crypto Section ======= -->
    <section class="kx-sec kx-sec-alt" id="why">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <div class="kx-sec-tag mx-auto"><i class="bi bi-stars"></i>Why Choose Crypto</div>
                <h2 class="kx-sec-h">{{ $siteContent['why_heading'] ?? 'Why People Choose Crypto' }}</h2>
                <p class="kx-sec-sub mx-auto">Understanding the driving forces behind the crypto revolution.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="kx-why-card">
                        <div class="kx-why-icon kx-why-i1"><i class="bi bi-send-fill"></i></div>
                        <h3 class="kx-why-t">{{ $siteContent['why_card1_title'] ?? 'Easy Mode of Payment' }}</h3>
                        <p class="kx-why-d">{{ $siteContent['why_card1_desc'] ?? 'Send and receive money globally with ease.' }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="kx-why-card">
                        <div class="kx-why-icon kx-why-i2"><i class="bi bi-bank2"></i></div>
                        <h3 class="kx-why-t">{{ $siteContent['why_card2_title'] ?? 'Financial Freedom' }}</h3>
                        <p class="kx-why-d">{{ $siteContent['why_card2_desc'] ?? 'Full transparency and privacy over your money.' }}</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="kx-why-card">
                        <div class="kx-why-icon kx-why-i3"><i class="bi bi-graph-up-arrow"></i></div>
                        <h3 class="kx-why-t">{{ $siteContent['why_card3_title'] ?? 'Investment' }}</h3>
                        <p class="kx-why-d">{{ $siteContent['why_card3_desc'] ?? 'Digital Gold — a popular store of wealth for investors.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= How It Works Section ======= -->
    <section class="kx-sec kx-sec-dark" id="how">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <div class="kx-sec-tag mx-auto"><i class="bi bi-list-ol"></i>How It Works</div>
                <h2 class="kx-sec-h">Start Trading in 3 Simple Steps</h2>
                <p class="kx-sec-sub mx-auto">Get from zero to your first trade in under 5 minutes.</p>
            </div>
            <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="100">
                <div class="col-md-4 col-sm-12">
                    <div class="kx-card kx-how-step">
                        <div class="kx-how-num">1</div>
                        <h4 class="kx-how-t">Create Your Account</h4>
                        <p class="kx-how-d">Sign up in minutes — no KYC required. Just your email and a secure password.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="kx-card kx-how-step">
                        <div class="kx-how-num">2</div>
                        <h4 class="kx-how-t">Choose Your Crypto</h4>
                        <p class="kx-how-d">Select from BTC, ETH, USDT, LTC, BNB and more. Get real-time NGN rates instantly.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="kx-card kx-how-step">
                        <div class="kx-how-num">3</div>
                        <h4 class="kx-how-t">Receive Your Naira</h4>
                        <p class="kx-how-d">Submit your trade and receive your NGN directly to your bank account. Fast & secure.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="200">
                @auth
                <a href="{{ url('/sell') }}" class="kx-h-cta1" style="display:inline-flex"><i class="bi bi-arrow-up-circle-fill"></i>Sell Crypto Now</a>
                @else
                <a href="{{ route('register') }}" class="kx-h-cta1" style="display:inline-flex"><i class="bi bi-rocket-takeoff-fill"></i>Get Started Free</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- ======= Trade Section ======= -->
    <section class="kx-sec kx-sec-alt" id="trade">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <div class="kx-sec-tag mx-auto"><i class="bi bi-currency-bitcoin"></i>Trade Now</div>
                <h2 class="kx-sec-h">Start Trading Directly</h2>
                <p class="kx-sec-sub mx-auto">Click Trade Now to send a WhatsApp message and we'll process your trade within minutes.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="kx-trade-card kx-trade-btc">
                        <div class="kx-trade-icon"><img src="{{ asset('Assests/crypto-icons/btc.svg') }}" alt="Bitcoin"></div>
                        <div class="kx-trade-name">Bitcoin (BTC)</div>
                        <p class="kx-trade-desc">Trade Bitcoin to Naira at the best market rates, settled directly to your bank account.</p>
                        <a href="https://wa.me/+2349016740523?text=Hello%2C%20I%20would%20like%20to%20trade%20BTC%20to%20Naira" class="kx-trade-btn kx-trade-btn-btc" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>Trade Now
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="kx-trade-card kx-trade-eth">
                        <div class="kx-trade-icon"><img src="{{ asset('Assests/crypto-icons/eth.svg') }}" alt="Ethereum"></div>
                        <div class="kx-trade-name">Ethereum (ETH)</div>
                        <p class="kx-trade-desc">Sell Ethereum for NGN with competitive rates and fast bank settlement.</p>
                        <a href="https://wa.me/+2349016740523?text=Hello%2C%20I%20would%20like%20to%20trade%20ETH%20to%20Naira" class="kx-trade-btn kx-trade-btn-eth" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>Trade Now
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="kx-trade-card kx-trade-usdt">
                        <div class="kx-trade-icon"><img src="https://cdn.jsdelivr.net/gh/atomiclabs/cryptocurrency-icons@1a63530be6e374711a8554f31b17e4cb92c25fa5/svg/color/usdt.svg" alt="USDT" width="42" height="42"></div>
                        <div class="kx-trade-name">Tether (USDT)</div>
                        <p class="kx-trade-desc">Trade USDT TRC20/ERC20 to Naira stably and instantly with zero surprises.</p>
                        <a href="https://wa.me/+2349016740523?text=Hello%2C%20I%20would%20like%20to%20trade%20USDT%20to%20Naira" class="kx-trade-btn kx-trade-btn-usdt" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>Trade Now
                        </a>
                    </div>
                </div>
                <div class="col-12 text-center" data-aos="fade-up" data-aos-delay="350">
                    <p style="font-size:0.83rem;color:rgba(255,255,255,0.35);margin-top:8px">Also accept: LTC, BNB, XRP, DOGE &amp; more — just ask via WhatsApp.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= Reviews Section ======= -->
    <section class="kx-sec kx-sec-dark" id="reviews">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <div class="kx-sec-tag mx-auto"><i class="bi bi-star-fill"></i>Testimonials</div>
                <h2 class="kx-sec-h">What Our Traders Say</h2>
                <p class="kx-sec-sub mx-auto">Thousands of happy traders trust KayXchange every day.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="kx-review-card">
                        <div class="kx-review-stars">★★★★★</div>
                        <p class="kx-review-text">I am amazed by the seamless experience and reliability. Trading has become effortless and convenient. Highly recommended!</p>
                        <div class="d-flex align-items-center gap-12" style="gap:12px">
                            <img src="{{ asset('Assests/images/image1.png') }}" class="kx-review-avatar" alt="Amarachi">
                            <div><div class="kx-review-name">Amarachi</div><div class="kx-review-role">Cryptocurrency Enthusiast</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="kx-review-card">
                        <div class="kx-review-stars">★★★★★</div>
                        <p class="kx-review-text">A total game-changer. Best market rates and fully secure transactions. I have complete trust in this platform.</p>
                        <div class="d-flex align-items-center" style="gap:12px">
                            <img src="{{ asset('Assests/images/image2.png') }}" class="kx-review-avatar" alt="Ade Simi">
                            <div><div class="kx-review-name">Ade Simi</div><div class="kx-review-role">Crypto Investor</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="kx-review-card">
                        <div class="kx-review-stars">★★★★★</div>
                        <p class="kx-review-text">The most reliable platform I've used. Fast and efficient transactions, no limits. My go-to for crypto trades in Nigeria.</p>
                        <div class="d-flex align-items-center" style="gap:12px">
                            <img src="{{ asset('Assests/images/image3.png') }}" class="kx-review-avatar" alt="Oliseh">
                            <div><div class="kx-review-name">Oliseh</div><div class="kx-review-role">Cryptocurrency Trader</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= Blog Section ======= -->
    <section class="kx-sec kx-sec-alt" id="blog">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <div class="kx-sec-tag mx-auto"><i class="bi bi-newspaper"></i>Blog</div>
                <h2 class="kx-sec-h">Latest from Our Blog</h2>
                <p class="kx-sec-sub mx-auto">Crypto tips, guides, and market insights — updated regularly.</p>
            </div>

        @if(isset($blogPosts) && $blogPosts->isNotEmpty())
        <style>
        .kx-bp-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        @media(max-width:991px){ .kx-bp-grid { grid-template-columns: repeat(2,1fr); } }
        @media(max-width:575px){ .kx-bp-grid { grid-template-columns: 1fr; } }

        .kx-bp-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            text-decoration: none !important;
            transition: transform .25s, border-color .25s, box-shadow .25s;
        }
        .kx-bp-card:hover {
            transform: translateY(-5px);
            border-color: rgba(0,204,0,0.30);
            box-shadow: 0 18px 48px rgba(0,0,0,0.35);
        }
        .kx-bp-img-wrap { position: relative; width: 100%; aspect-ratio: 16/9; overflow: hidden; background: linear-gradient(135deg,rgba(0,60,0,.55),rgba(0,20,0,.85)); flex-shrink: 0; }
        .kx-bp-img { width:100%; height:100%; object-fit:cover; display:block; transition: transform .35s; }
        .kx-bp-card:hover .kx-bp-img { transform: scale(1.04); }
        .kx-bp-img-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:2.8rem; color:rgba(0,204,0,.25); }
        .kx-bp-body { padding: 18px 20px 20px; flex:1; display:flex; flex-direction:column; gap:10px; }
        .kx-bp-meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .kx-bp-cat { font-size:.67rem; font-weight:700; letter-spacing:.55px; text-transform:uppercase; color:#00cc00; background:rgba(0,204,0,.1); border:1px solid rgba(0,204,0,.22); padding:2px 10px; border-radius:20px; }
        .kx-bp-date { font-size:.7rem; color:rgba(255,255,255,.32); margin-left:auto; }
        .kx-bp-title { font-size:.97rem; font-weight:700; color:#e6f5e6; line-height:1.42; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .kx-bp-excerpt { font-size:.78rem; color:rgba(255,255,255,.42); line-height:1.6; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
        .kx-bp-footer { display:flex; align-items:center; justify-content:flex-end; padding-top:10px; border-top:1px solid rgba(255,255,255,.06); margin-top:auto; }
        .kx-bp-read { font-size:.78rem; font-weight:600; color:#00cc00; display:inline-flex; align-items:center; gap:5px; transition:gap .2s; }
        .kx-bp-card:hover .kx-bp-read { gap:9px; }

        .kx-bp-featured { grid-column: span 2; }
        @media(max-width:767px){ .kx-bp-featured { grid-column: span 1; } }
        .kx-bp-featured .kx-bp-img-wrap { aspect-ratio: 21/9; }
        .kx-bp-featured .kx-bp-title { font-size:1.15rem; -webkit-line-clamp:3; }

        .kx-blog-view-all { display:inline-flex; align-items:center; gap:8px; margin-top:36px; padding:11px 28px; border:1.5px solid rgba(0,204,0,.4); border-radius:25px; color:#00cc00; font-weight:600; font-size:.875rem; text-decoration:none; transition:all .25s; background:rgba(0,204,0,.06); }
        .kx-blog-view-all:hover { background:rgba(0,204,0,.14); border-color:#00cc00; color:#00cc00; transform:translateY(-2px); }

        body.light-mode .kx-bp-card { background:#fff; border-color:rgba(0,0,0,.07); }
        body.light-mode .kx-bp-title { color:#0a1a0a; }
        body.light-mode .kx-bp-excerpt { color:rgba(0,0,0,.5); }
        body.light-mode .kx-bp-date { color:rgba(0,0,0,.38); }
        body.light-mode .kx-bp-footer { border-color:rgba(0,0,0,.07); }
        </style>

        <div class="kx-bp-grid" data-aos="fade-up" data-aos-delay="100">
            @foreach($blogPosts->take(6) as $i => $post)
            <a href="{{ url('/blog/'.$post->slug) }}" class="kx-bp-card{{ $i === 0 ? ' kx-bp-featured' : '' }}">
                <div class="kx-bp-img-wrap">
                    @if($post->cover_image)
                        <img class="kx-bp-img"
                             src="{{ asset('storage/'.$post->cover_image) }}"
                             alt="{{ $post->title }}"
                             loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="kx-bp-img-ph" style="display:none"><i class="bi bi-newspaper"></i></div>
                    @else
                        <div class="kx-bp-img-ph"><i class="bi bi-newspaper"></i></div>
                    @endif
                </div>
                <div class="kx-bp-body">
                    <div class="kx-bp-meta">
                        @if($post->category)
                        <span class="kx-bp-cat">{{ $post->category }}</span>
                        @endif
                        <span class="kx-bp-date"><i class="bi bi-calendar3 me-1"></i>{{ $post->published_at?->format('M d, Y') ?? '' }}</span>
                    </div>
                    <div class="kx-bp-title">{{ $post->title }}</div>
                    @if($post->excerpt)
                    <div class="kx-bp-excerpt">{{ $post->excerpt }}</div>
                    @endif
                    <div class="kx-bp-footer">
                        <span class="kx-bp-read">Read more <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ url('/blog') }}" class="kx-blog-view-all"><i class="bi bi-grid-3x3-gap-fill"></i>View All Posts</a>
        </div>

        @else
        <div class="text-center py-5" style="color:rgba(255,255,255,.35)">
            <i class="bi bi-newspaper" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.3"></i>
            No blog posts published yet. Check back soon!
        </div>
        @endif

        </div>
    </section>

    </main>

    <!-- ======= Newsletter Section ======= -->
    <section class="kx-nl" id="newsletter">
        <div class="container kx-nl-inner">
            <div class="row align-items-center g-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <div class="kx-sec-tag"><i class="bi bi-envelope-heart-fill"></i>Newsletter</div>
                    <h2>{{ $siteContent['newsletter_title'] ?? 'Stay Ahead of the Market' }}</h2>
                    <p class="kx-nl-sub">{{ $siteContent['newsletter_subtitle'] ?? 'Get weekly market insights, trading tips, and exclusive rate notifications.' }}</p>
                    <form class="kx-nl-form" id="kxNlForm" novalidate>
                        @csrf
                        <input type="email" class="kx-nl-input" placeholder="Enter your email address" required id="kxNlEmail">
                        <button type="submit" class="kx-nl-btn" id="kxNlBtn">
                            <i class="bi bi-envelope-check-fill"></i>Subscribe
                        </button>
                    </form>
                    <div class="kx-nl-perks">
                        <span class="kx-nl-perk"><i class="bi bi-check-circle-fill"></i>No spam, ever</span>
                        <span class="kx-nl-perk"><i class="bi bi-check-circle-fill"></i>Unsubscribe anytime</span>
                        <span class="kx-nl-perk"><i class="bi bi-check-circle-fill"></i>Weekly insights</span>
                    </div>
                </div>
                <div class="col-lg-5 text-lg-end" data-aos="fade-left">
                    <div style="display:inline-flex;flex-direction:column;align-items:center;gap:14px;background:rgba(0,204,0,0.06);border:1px solid rgba(0,204,0,0.15);border-radius:20px;padding:28px 32px;text-align:center;">
                        <div style="font-size:2.4rem;">📈</div>
                        <div style="font-size:1rem;font-weight:700;color:#fff">3,000+ subscribers</div>
                        <div style="font-size:0.78rem;color:rgba(255,255,255,0.38)">already getting crypto insights</div>
                        <div class="kx-nl-perks" style="justify-content:center">
                            <span class="kx-nl-perk"><i class="bi bi-star-fill" style="color:#fbbf24"></i>Rate Alerts</span>
                            <span class="kx-nl-perk"><i class="bi bi-star-fill" style="color:#fbbf24"></i>Market News</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= Footer ======= -->
    <footer class="kx-footer" id="footer">
        <div class="container">
            <div class="row g-5">
                <!-- Brand -->
                <div class="col-lg-4 col-md-12">
                    <a href="{{ url('/') }}" class="kx-footer-logo">
                        <img src="{{ asset('Assests/favicon.png') }}" alt="KayXchange">
                        <span>Kay<b>Xchange</b></span>
                    </a>
                    <p class="kx-footer-desc">{{ $siteContent['footer_tagline'] ?? 'Your trusted platform for seamless cryptocurrency trading. Fast, secure, and competitive NGN rates.' }}</p>
                    <div class="kx-footer-social">
                        <a href="https://www.twitter.com/kay__xchange" class="kx-footer-soc tw" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="https://api.whatsapp.com/send?phone=+2349016740523&text=Hello" class="kx-footer-soc wa" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        <a href="https://www.instagram.com/kay__xchange" class="kx-footer-soc ig" title="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://t.me/TradewithkayxchangeBOT" class="kx-footer-soc tg" title="Telegram"><i class="bi bi-telegram"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-6">
                    <h5 class="kx-footer-h">Quick Links</h5>
                    <ul class="kx-footer-links">
                        <li><a href="{{ url('/dashboard') }}"><i class="bi bi-chevron-right"></i>Dashboard</a></li>
                        <li><a href="{{ url('/buy') }}"><i class="bi bi-chevron-right"></i>Buy Crypto</a></li>
                        <li><a href="{{ url('/sell') }}"><i class="bi bi-chevron-right"></i>Sell Crypto</a></li>
                        <li><a href="{{ url('/rate') }}"><i class="bi bi-chevron-right"></i>Live Rates</a></li>
                        <li><a href="{{ url('/referrals') }}"><i class="bi bi-chevron-right"></i>Referrals</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="col-lg-2 col-6">
                    <h5 class="kx-footer-h">Company</h5>
                    <ul class="kx-footer-links">
                        <li><a href="{{ url('/about') }}"><i class="bi bi-chevron-right"></i>About Us</a></li>
                        <li><a href="{{ url('/faqs') }}"><i class="bi bi-chevron-right"></i>FAQs</a></li>
                        <li><a href="{{ url('/blog') }}"><i class="bi bi-chevron-right"></i>Blog</a></li>
                        <li><a href="{{ url('/privacy') }}"><i class="bi bi-chevron-right"></i>Privacy Policy</a></li>
                        <li><a href="{{ url('/terms') }}"><i class="bi bi-chevron-right"></i>Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-lg-4 col-md-12">
                    <h5 class="kx-footer-h">Contact Us</h5>
                    <div class="kx-footer-contact">
                        <div class="kx-footer-ci">
                            <div class="kx-footer-ci-icon"><i class="bi bi-envelope-fill"></i></div>
                            <div>
                                <div class="kx-footer-ci-t">Email</div>
                                <div class="kx-footer-ci-v"><a href="mailto:{{ $siteContent['contact_email'] ?? 'support@kayxchange.net' }}">{{ $siteContent['contact_email'] ?? 'support@kayxchange.net' }}</a></div>
                            </div>
                        </div>
                        <div class="kx-footer-ci">
                            <div class="kx-footer-ci-icon"><i class="bi bi-telephone-fill"></i></div>
                            <div>
                                <div class="kx-footer-ci-t">Phone / WhatsApp</div>
                                <div class="kx-footer-ci-v"><a href="tel:+2349016740523">{{ $siteContent['contact_phone'] ?? '+234 901 674 0523' }}</a></div>
                            </div>
                        </div>
                        <div class="kx-footer-ci">
                            <div class="kx-footer-ci-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <div>
                                <div class="kx-footer-ci-t">Location</div>
                                <div class="kx-footer-ci-v">{{ $siteContent['contact_location'] ?? 'Nigeria' }} &bull; <span style="color:#00cc00;">Available 24/7</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom bar -->
            <div class="kx-footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="kx-footer-copy">
                            &copy; <span id="kxYear"></span> <strong>KayXchange</strong>. All rights reserved. &nbsp;|&nbsp;
                            <a href="{{ url('/privacy') }}">Privacy</a> &nbsp;|&nbsp;
                            <a href="{{ url('/terms') }}">Terms</a>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2 mt-md-0">
                        <div class="kx-footer-badges">
                            <span class="kx-footer-badge"><i class="bi bi-shield-fill-check"></i>Secure Trading</span>
                            <span class="kx-footer-badge"><i class="bi bi-lightning-fill"></i>Instant Payouts</span>
                            <span class="kx-footer-badge"><i class="bi bi-star-fill"></i>Best Rates</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <a href="#" class="kx-btt" id="kxBtt" aria-label="Back to top"><i class="bi bi-arrow-up-short"></i></a>

    <script>
    // Year
    document.getElementById('kxYear').textContent = new Date().getFullYear();

    // Back to top
    (function(){
        var btn = document.getElementById('kxBtt');
        window.addEventListener('scroll', function(){ btn.classList.toggle('kx-btt-show', window.scrollY > 320); }, {passive:true});
        btn.addEventListener('click', function(e){ e.preventDefault(); window.scrollTo({top:0,behavior:'smooth'}); });
    })();

    // Newsletter form
    (function(){
        var form = document.getElementById('kxNlForm');
        if(!form) return;
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var email = document.getElementById('kxNlEmail').value.trim();
            var btn   = document.getElementById('kxNlBtn');
            if(!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
                document.getElementById('kxNlEmail').style.borderColor='rgba(239,68,68,0.6)';
                return;
            }
            document.getElementById('kxNlEmail').style.borderColor='';
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Subscribing...';
            btn.disabled = true;
            // Replace with real AJAX to your subscribe endpoint
            setTimeout(function(){
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Subscribed!';
                btn.style.background = '#00a010';
                setTimeout(function(){
                    btn.innerHTML = orig;
                    btn.style.background = '';
                    btn.disabled = false;
                    form.reset();
                }, 3000);
            }, 1200);
        });
    })();
    </script>

    <script>
    // Ensure AOS animates in correctly after all assets load
    window.addEventListener('load', function(){
        if(window.AOS) {
            AOS.init({ duration: 600, once: true, offset: 60 });
        }
    });
    </script>

@endsection
