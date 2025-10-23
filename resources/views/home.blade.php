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


    <!-- ======= Bootstrap Header ======= -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <img width="40px" src="/Assests/favicon.png" alt="KayXchange" class="me-2">
                KayXchange
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#homeNavbarNav" aria-controls="homeNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="homeNavbarNav">
                <div class="navbar-nav me-auto">
                    @auth
                        <a class="nav-link @if(request()->is('dashboard')) active @endif" @if(request()->is('dashboard')) aria-current="page" @endif href="/dashboard">Dashboard</a>
                        <a class="nav-link @if(request()->is('rate')) active @endif" @if(request()->is('rate')) aria-current="page" @endif href="/rate">Rates</a>
                        <a class="nav-link @if(request()->is('buy*')) active @endif" @if(request()->is('buy*')) aria-current="page" @endif href="/buy">Buy Crypto</a>
                        <a class="nav-link @if(request()->is('sell*')) active @endif" @if(request()->is('sell*')) aria-current="page" @endif href="/sell">Sell Crypto</a>
                    @else
                        <a class="nav-link @if(request()->is('/')) active @endif" @if(request()->is('/')) aria-current="page" @endif href="/">Home</a>
                        <a class="nav-link @if(request()->is('rate')) active @endif" @if(request()->is('rate')) aria-current="page" @endif href="/rate">Exchange Rates</a>
                        <a class="nav-link @if(request()->is('blog*')) active @endif" @if(request()->is('blog*')) aria-current="page" @endif href="/blog">Blog</a>
                        <a class="nav-link @if(request()->is('faqs*')) active @endif" @if(request()->is('faqs*')) aria-current="page" @endif href="/faqs">FAQs</a>
                        <a class="nav-link @if(request()->is('about*')) active @endif" @if(request()->is('about*')) aria-current="page" @endif href="/about">About Us</a>
                    @endauth
                </div>

                <!-- Right side navigation -->
                <div class="d-flex align-items-center">
                    @auth
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
    </nav><!-- End Header -->

    <!-- ======= Hero Section ======= -->
    <section id="hero" class="hero d-flex align-items-center">

        <div class="container">
            <div class="row">
                <div class="col-lg-6 d-flex flex-column justify-content-center">
                    <h1 data-aos="fade-up">Buy, Sell and Exchange your Crypto to NGN <br> On <h3>Kay xchange</h3>
                    </h1>
                    <!-- <small>No KYC Required</small> -->
                    <h2 data-aos="fade-up" data-aos-delay="400">Easily Trade Cryptocurrencies like <br> BTC, USDT, ETH, LTC & XRP To NGN</h2>
                    <div data-aos="fade-up" data-aos-delay="600">
                        @auth
                        <a href="/dashboard" class="btn-get-started scrollto m-3 d-inline-flex align-items-center justify-content-center align-self-center">
                                <span>Dashboard</span>
                                <i class="bi bi-arrow-right"></i>
                        </a>

                        @else
                        <div class="text-center justify-content-lg-start justify-content-center d-flex text-lg-start">
                            <a href="/register" class="btn-get-started scrollto m-3 d-inline-flex align-items-center justify-content-center align-self-center">
                                <span>Register</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="/login" class="btn-get-started m-3 scrollto d-inline-flex align-items-center justify-content-center align-self-center">
                                <span>Login</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        @endauth
                    </div>
                    <div data-aos="fade-up" data-aos-delay="600">
                        <div class="text-center text-lg-start">
                            <a href="https://wa.me/+2349016740523" class="btn-get-started scrollto d-inline-flex align-items-center justify-content-center align-self-center">
                                <span>Quick Trade {Whatsapp}</span>
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                    <div data-aos="fade-up" data-aos-delay="600">
                        <div class="text-center text-lg-start">
                            <a href="/rate" class="btn-get-started scrollto d-inline-flex align-items-center justify-content-center align-self-center">
                                <span>Check Rates {Crypto}</span>
                                <i class="bi bi-graph-up-arrow"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-img" data-aos="zoom-out" data-aos-delay="200">
                    <img src="/Assests/images/kay-xchange-logo-mockup.png" class="img-fluid" alt="">
                </div>
                <div class="d-lg-none justify-content-center">
                    <a href=""><img width="120px" src="/Assests/appstore.png" alt=""></a>
                </div>
            </div>

            <h1 class="ml4">
                <span class="letters letters-1">No</span>
                <span class="letters letters-2">KYC</span>
                <span class="letters letters-3">Required</span>
            </h1>
        </div>

    </section><!-- End Hero -->

    <div id="prices-container" class="container d-inline-flex"></div>

    <!-- ======= Telegram Notification Banner ======= -->
    <section class="telegram-banner py-4 m-5 rounded rounded-3 mb-4 mt-5" style="background: linear-gradient(135deg, #078b00 0%, #004b10 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="text-white">
                        @auth
                        <h5 class="mb-2 text-center">
                            <i class="fab fa-telegram-plane me-2"></i>
                            Get Instant Trade Notifications on Telegram!
                        </h5>
                        <p class="mb-0 opacity-90">
                            Never miss a trade update! Connect with our Telegram bot for real-time notifications about your transactions, rate changes, and account activities.
                        </p>
                        @else
                        <h5 class="mb-2 text-center">
                            <i class="fab fa-telegram-plane me-2"></i>
                            Trade Instantly with Us on Our Telegram Bot!
                        </h5>
                        <p class="mb-0 opacity-90">
                            Start trading cryptocurrencies instantly through our Telegram bot and receive real-time notifications as a user. Quick, secure, and convenient!
                        </p>
                        @endauth
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center align-items-center">
                        <a href="https://t.me/TradewithkayxchangeBOT" 
                           target="_blank" 
                           class="btn btn-telegram-cta btn-lg">
                            <i class="fab fa-telegram-plane me-2"></i>
                            Start Bot Now
                        </a>
                        <button type="button" 
                                class="btn btn-outline-light btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#homeTelegramModal">
                            <i class="fas fa-qrcode me-2"></i>
                            QR Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        #prices-container {
            display: flex;
            justify-content: space-evenly;
            height: 80px;
            margin: auto;
            padding: auto;
            border-radius: 5px;

        }
        
        .btn-telegram-cta {
            background-color: white;
            color: #0088cc;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-telegram-cta:hover {
            color: #005fa3;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .telegram-banner .col-lg-4 {
                margin-top: 20px;
            }
        }
    </style>

    <main id="main">
        <!-- ======= About Section ======= -->
        <section id="about" class="about">

            <div class="container" data-aos="fade-up">
                <div class="row gx-0">

                    <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
                        <div class="">
                            <h3>About Kay Xchange</h3>
                            <h2>Kay Xchange is a financial services company that specializes in buying and selling digital assets</h2>
                            <p>
                                Our platform is designed to provide a seamless and secure experience for our clients who are looking to exchange these assets.
                                We understand that the world of digital assets can be complex and confusing, which is why we strive to make the process as simple and straightforward as possible.
                                Our team of experts is always available to provide guidance and support to our clients, ensuring that they are making informed decisions when buying or selling digital assets.
                            </p>
                            <div class="text-center text-lg-start">
                                <a href="/About-us.html" class="btn-read-more d-inline-flex align-items-center justify-content-center align-self-center">
                                    <span>Read More</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 d-flex mt-4 align-items-center" data-aos="zoom-out" data-aos-delay="200">
                        <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img class="d-block w-100" src="Assests/images/carousel/ourratedeybuga.jpeg" alt="First slide">
                                </div>
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="Assests/images/carousel/Youdontneedtogofar.jpeg" alt="Second slide">
                                </div>
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="Assests/images/carousel/neednairaforexchange.jpeg" alt="Third slide">
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                                <span class="sr-only fs-5 text-black">P</span>
                                <span class="carousel-control-prev-icon text-black" aria-hidden="true"></span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                                <span class="carousel-control-next-icon text-black" aria-hidden="true"></span>
                                <span class="sr-only fs-5 text-black">N</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </section><!-- End About Section -->

        <!-- ======= Values Section ======= -->
        <section id="values" class="values">

            <div class="container" data-aos="fade-up">

                <header class="section-header">
                    <h3>Why do people get involved with cryptocurrency?</h3>
                </header>

                <div class="row">

                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="box">
                            <img src="Assests/images/easy mode of payment.png" class="img-fluid" alt="">
                            <h3>Easy Mode Of Payment</h3>
                            <p>People can now easily send and receive money from anywhere in the world to purchase goods and pay for services</p>
                        </div>
                    </div>

                    <div class="col-lg-4 mt-4 mt-lg-0" data-aos="fade-up" data-aos-delay="400">
                        <div class="box">
                            <img src="Assests/images/financial freedom.png" class="img-fluid" alt="">
                            <h3>Financial freedom</h3>
                            <p>Just like the internet no single entity controls the Crypto network which provides users transparency and privacy, which puts you in absolute control of your money</p>
                        </div>
                    </div>

                    <div class="col-lg-4 mt-4 mt-lg-0" data-aos="fade-up" data-aos-delay="600">
                        <div class="box">
                            <img src="Assests/images/invest.png" class="img-fluid" alt="">
                            <h3>Investment</h3>
                            <p>The constant demand has made Cryptocurrecies a Digital Gold used for alternative store of wealth on long term investments. </p>
                        </div>
                    </div>

                </div>

            </div>

        </section><!-- End Values Section -->

        <!-- ======= Counts Section ======= -->
        <section id="counts" class="counts">
            <div class="container" data-aos="fade-up">

                <div class="row gy-4">

                    <div class="col-lg-3 col-md-6">
                        <div class="count-box">
                            <i class="bi bi-emoji-smile"></i>
                            <div>
                                <span data-purecounter-start="0" data-purecounter-end="3000" data-purecounter-duration="1" class="purecounter"></span>
                                <p>Happy Clients</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="count-box">
                            <i class="bi bi-journal-richtext" style="color: #ee6c20;"></i>
                            <div>
                                <span data-purecounter-start="0" data-purecounter-end="90000" data-purecounter-duration="1" class="purecounter"></span>
                                <p>Trades</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="count-box">
                            <i class="bi bi-headset" style="color: #15be56;"></i>
                            <div>
                                <span data-purecounter-start="0" data-purecounter-end="24" data-purecounter-duration="1" class="purecounter"></span>
                                <p>Hours Of Support</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="count-box">
                            <i class="bi bi-people" style="color: #bb0852;"></i>
                            <div>
                                <span data-purecounter-start="0" data-purecounter-end="15" data-purecounter-duration="1" class="purecounter"></span>
                                <p>Hard Workers</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section><!-- End Counts Section -->

        <!-- ======= Features Section ======= -->
        <section id="features" class="features">

            <div class="container" data-aos="fade-up">

                <header class="section-header">
                    <h2>What kay xchange has to offer</h2>
                    <small>Hello chief, Below is a list of services we offer on this platform</small>
                </header>

                <div class="row">

                    <div class="col-lg-6">
                        <img src="Assests/favicon.png" class="img-fluid" alt="">
                    </div>

                    <div class="col-lg-6 mt-5 mt-lg-0 d-flex">
                        <div class="row align-self-center gy-4">

                            <div class="col-md-6" data-aos="zoom-out" data-aos-delay="200">
                                <div class="feature-box d-flex align-items-center">
                                    <i class="bi bi-check"></i>
                                    <h3>Instant Conversion To NGN</h3>
                                </div>
                            </div>

                            <div class="col-md-6" data-aos="zoom-out" data-aos-delay="300">
                                <div class="feature-box d-flex align-items-center">
                                    <i class="bi bi-check"></i>
                                    <h3>Competitive Exchange Rates</h3>
                                </div>
                            </div>

                            <div class="col-md-6" data-aos="zoom-out" data-aos-delay="400">
                                <div class="feature-box d-flex align-items-center">
                                    <i class="bi bi-check"></i>
                                    <h3>Security & Reliablity</h3>
                                </div>
                            </div>


                        </div>
                    </div>

                </div> <!-- / row -->

                <!-- Feature Tabs -->
                <div class="row feture-tabs" data-aos="fade-up">
                    <div class="col-lg-6">
                        <h3>Top 3 Cryptocurrencies and there uses</h3>

                        <!-- Tabs -->
                        <ul class="nav nav-pills mb-3">
                            <li>
                                <a class="nav-link active" data-bs-toggle="pill" href="#tab1">Bitcoin</a>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="pill" href="#tab2">Ethereum</a>
                            </li>
                            <li>
                                <a class="nav-link" data-bs-toggle="pill" href="#tab3">Usdt</a>
                            </li>
                        </ul><!-- End Tabs -->

                        <!-- Tab Content -->
                        <div class="tab-content">

                            <div class="tab-pane fade show active" id="tab1">
                                <h4 class="mb-3 pt-3">The Pioneer of Cryptocurrencies:</h4>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-check2"></i>
                                    <h4>Bitcoin (BTC)</h4>
                                </div>
                                <p>
                                    Bitcoin, often referred to as the king of cryptocurrencies, revolutionized the digital currency landscape.</p>
                                <div class="d-flex align-items-center mb-2">
                                    <!-- <i class="bi bi-check2"></i> -->
                                    <!-- <h4>Here is more</h4> -->
                                </div>
                                <p>As the first decentralized cryptocurrency, Bitcoin introduced a secure and transparent peer-to-peer payment system. Its primary use case lies in facilitating online transactions and acting as a store of value. <br> Bitcoin's decentralized nature and limited supply make it a popular choice for individuals seeking to hedge against traditional financial systems.</p>
                            </div><!-- End Tab 1 Content -->

                            <div class="tab-pane fade show" id="tab2">
                                <h4 class="mb-3 pt-3">The Foundation for Smart Contracts:</h4>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-check2"></i>
                                    <h4>Ethereum (ETH)</h4>
                                </div>
                                <p>Ethereum is not just a cryptocurrency but also a decentralized platform that enables developers to build and deploy smart contracts and decentralized applications (DApps).</p>
                                <div class="d-flex align-items-center mb-2">
                                    <!-- <i class="bi bi-check2"></i> -->
                                    <!-- <h4>Incidunt non veritatis illum ea ut nisi</h4> -->
                                </div>
                                <p>While Ether (ETH) serves as the native cryptocurrency of the Ethereum network, its value extends beyond transactions. <br> ETH fuels the execution of smart contracts and serves as a gateway to access various decentralized services, including decentralized finance (DeFi), non-fungible tokens (NFTs), and decentralized exchanges (DEXs).</p>
                            </div><!-- End Tab 2 Content -->

                            <div class="tab-pane fade show" id="tab3">
                                <h4>The Stablecoin for Digital Transactions:<h4>
                                        <div class="d-flex align-items-center mb-2">
                                            <!-- <i class="bi bi-check2"></i> -->
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <!-- <i class="bi bi-check2"></i>
                  <h4>Incidunt non veritatis illum ea ut nisi</h4> -->
                                        </div>
                                        <p style="text-decoration: none; font-weight: 200;">Tether is a cryptocurrency that aims to bridge the gap between traditional fiat currencies and digital assets. Unlike most cryptocurrencies, Tether is a stablecoin designed to maintain a stable value by pegging it to a specific fiat currency, such as the US dollar.</p>
                                        <p style="font-weight: 200;">This stability makes USDT a preferred choice for traders and investors seeking to mitigate the volatility often associated with other cryptocurrencies. <br> USDT provides a reliable means of transferring value across different cryptocurrency exchanges and platforms.</p>
                            </div><!-- End Tab 3 Content -->

                        </div>
                        <!-- <div class="col-lg-6 m-auto">
              <img src="/exchangeratesassetimages/usdt.svg" width="250px" class="img-fluid" alt="">
            </div> -->
                    </div>



                </div><!-- End Feature Tabs -->

                <!-- Feature Icons -->
                <div class="row">

                    <div class="col-xl-4 text-center" data-aos="fade-right" data-aos-delay="100">
                        <img src="/Assests/images/Aboutusimages/ourvision.png" class="img-fluid p-4" alt="">
                    </div>

                    <div class="col-xl-8 d-flex content">
                        <div class="row align-self-center gy-4">

                            <div class="col-md-6 icon-box" data-aos="fade-up">
                                <i class="text-success ri-line-chart-line"></i>
                                <div>
                                    <h4>No Limits on Financial Growth</h4>
                                    <p>Experience the freedom to expand your financial opportunities with our cryptocurrency exchange app. Unlock the potential for unlimited growth and take control of your financial future.</p>
                                </div>
                            </div>

                            <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="100">
                                <i class="ri-wallet-2-line text-success"></i>
                                <div>
                                    <h4>Best Market Rates</h4>
                                    <p>Enjoy the most competitive market rates for your cryptocurrency transactions. We strive to provide you with favorable exchange rates, ensuring that you get the best value for your digital assets.</p>
                                </div>
                            </div>

                            <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="200">
                                <i class="text-success ri-secure-payment-line"></i>
                                <div>
                                    <h4>Reliability at its Core</h4>
                                    <p>Your trust is our priority. <br> Kay Xchange cryptocurrency trading app offers a reliable and secure platform for your financial transactions. <br> Rest assured knowing that your funds and personal information are protected at all times.</p>
                                </div>
                            </div>

                            <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="300">
                                <i class="ri-magic-line text-success"></i>
                                <div>
                                    <h4>Seamless User Experience</h4>
                                    <p>Enjoy a seamless and user-friendly experience while navigating our cryptocurrency exchange app. We prioritize intuitive design and smooth functionality, making your transactions effortless and efficient.</p>
                                </div>
                            </div>

                            <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="400">
                                <i class="ri-command-line text-success"></i>
                                <div>
                                    <h4>Free and Limitless Transactions</h4>
                                    <p>Get started with our app and enjoy free and limitless transactions without any KYC verification. Experience the freedom of transacting with ease and explore the world of cryptocurrencies without restrictions.</p>
                                </div>
                            </div>

                            <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="500">
                                <i class="ri-p2p-line text-success"></i>
                                <div>
                                    <h4>Secure and Transparent Transactions</h4>
                                    <p>Rest easy knowing that your transactions are secure and transparent. Our cryptocurrency exchange app utilizes advanced security measures and ensures transparent processes, providing you with peace of mind.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div><!--
   End Feature Icons -->

            </div>

        </section><!-- End Features Section -->

        <!-- ======= Services Section ======= -->
        <section id="services" class="services">
            <div class="container" data-aos="fade-up">
                <header class="section-header">
                    <h2>Start Trading Directly</h2>
                </header>

                <div class="row gy-4">

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="service-box orange">
                            <img src="/Assests//crypto-icons/btc.svg" alt="">
                            <h3>Bitcoin</h3>
                            <p>Trade Bitcoin to Naira and tap into the world of cryptocurrencies.</p>
                            <a href="https://wa.me/+2349016740523?text=Hello%2C%20I%20would%20like%20to%20trade%20$__%20BTC%20to%20Naira" class="read-more"><span>Trade Now</span> <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="service-box blue">
                            <img src="/Assests/crypto-icons/eth.svg" alt="">
                            <h3>Ethereum</h3>
                            <p>Trade Ethereum to Naira and explore the potential of the second-largest cryptocurrency. </p>
                            <a href="https://wa.me/+2349016740523?text=Hello%2C%20I%20would%20like%20to%20trade%20$__%20ETH%20to%20Naira" class="read-more"><span>Trade Now</span> <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                        <div class="service-box green">
                            <img width="35px" src="https://cdn.jsdelivr.net/gh/atomiclabs/cryptocurrency-icons@1a63530be6e374711a8554f31b17e4cb92c25fa5/svg/color/usdt.svg" alt="">
                            <h3>Usdt Coin</h3>
                            <p>Trade Usdt Coin to Naira and be part of the vibrant ecosystem.</p>
                            <a href="https://wa.me/+2349016740523?text=Hello%2C%20I%20would%20like%20to%20trade%20$__%20USDT%20to%20Naira" class="read-more"><span>Trade Now</span> <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section><!-- End Services Section -->



        <div class="container d-flex justify-content-center" data-aos="fade-up" data-aos-delay="500">
            <div style="text-align: center;" class="service-box green">
                <h3>Got other cryptocurrencies?</h3>
                <img width="200px" src="/Assests/favicon.png" alt="">
                <p>Kay xchange is here to Help you convert that to Naira</p>
                <div class="container">
                    <div>
                        <img width="350px" src="/Assests/mocku_ image.png" alt="">
                    </div>
                </div>
                <a href="https://User.kayxchange.net" class="read-more">
                    <span>Trade Now</span>
                    <i class="bi bi-arrow-right"></i></a>
                <div>
                    <div>
                        <a href=""><img width="120px" src="/Assests/appstore.png" alt=""></a>
                    </div>
                    <!-- <div style="margin: auto;"><a href="/"><h4 class="text-start">Download now</h4></a></div> -->
                </div>
            </div>
        </div>


        <section id="testimonials" class="testimonials">

            <div class="container" data-aos="fade-up">

                <header class="section-header">
                    <h2>What Our Users Say</h2>
                    <p>Discover why our users love our cryptocurrency exchange app</p>
                </header>

                <div class="testimonials-slider swiper" data-aos="fade-up" data-aos-delay="200">
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                                <p>
                                    I am amazed by the seamless experience and reliability of Kay xchange. It has made trading effortless and convenient for me. Highly recommended!
                                </p>
                                <div class="profile mt-auto">
                                    <img width="300px" src="/Assests/images/image1.png" class="testimonial-img" alt="">
                                    <h3>Amarachi</h3>
                                    <h4>Cryptocurrency Enthusiast</h4>
                                </div>
                            </div>
                        </div><!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                                <p>
                                    This app is a game-changer. <br> It offers the best market rates and ensures secure transactions. <br> I have complete trust in this platform.
                                </p>
                                <div class="profile mt-auto">
                                    <img src="/Assests/images/image2.png" class="testimonial-img" alt="">
                                    <h3>Ade simi</h3>
                                    <h4>Crypto Investor</h4>
                                </div>
                            </div>
                        </div><!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </div>
                                <p>
                                    I have been using this cryptocurrency exchange app for a while now, and I must say it's the most reliable platform I have come across. It offers fast and efficient transactions with no limits.
                                </p>
                                <div class="profile mt-auto">
                                    <img src="/Assests/images/image3.png" class="testimonial-img" alt="">
                                    <h3>Oliseh</h3>
                                    <h4>Cryptocurrency Trader</h4>
                                </div>
                            </div>
                        </div><!-- End testimonial item -->

       

                    </div>
                    <div class="swiper-pagination"></div>
                </div>

            </div>

        </section><!-- End Testimonials Section -->




        <!-- ======= Recent Blog Posts Section ======= -->
        <section id="recent-blog-posts" class="recent-blog-posts">

            <div class="container" data-aos="fade-up">

                <header class="section-header">
                    <p>Learn more about cryptocurrency</p>
                    <small>Below is a well curated list of topics, to get you started with cryptocurrecy</small>
                </header>

                <div class="row">

                    <div class="col-lg-4">
                        <div class="post-box">
                            <!-- <div class="post-img"><img src="assets/img/blog/blog-1.jpg" class="img-fluid" alt=""></div> -->
                            <span class="post-date">Tue, September 15</span>
                            <h3 class="post-title">Introduction to Blockchain Technology</h3>
                            <a href="/Blogpost/introductiontoblockchain.html" class="readmore stretched-link mt-auto"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="post-box">
                            <!-- <div class="post-img"><img src="assets/img/blog/blog-2.jpg" class="img-fluid" alt=""></div> -->
                            <span class="post-date">Fri, August 28</span>
                            <h3 class="post-title">Understanding Cryptocurrency Wallets</h3>
                            <a href="/Blogpost/Understandingcryptocurrencywallets.html" class="readmore stretched-link mt-auto"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="post-box">
                            <!-- <div class="post-img"><img src="assets/img/blog/blog-3.jpg" class="img-fluid" alt=""></div> -->
                            <span class="post-date">Mon, July 11</span>
                            <h3 class="post-title">The Basics of Cryptocurrency</h3>
                            <a href="/Blogpost/Thebasicsofcryptocurrency.html" class="readmore stretched-link mt-auto"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="post-box">
                            <!-- <div class="post-img"><img src="assets/img/blog/blog-3.jpg" class="img-fluid" alt=""></div> -->
                            <span class="post-date">Mon, July 11</span>
                            <h3 class="post-title">Types of Cryptocurrencies</h3>
                            <a href="/Blogpost/Typesofcryptocurrency.html" class="readmore stretched-link mt-auto"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                </div>

            </div>

        </section><!-- End Recent Blog Posts Section -->



    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="footer-newsletter">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center">
                        <h4 class="text-white">Stay Updated with Crypto Trends</h4>
                        <p class="text-white-50">Subscribe to our newsletter for the latest market insights, trading tips, and exclusive offers</p>
                    </div>
                    <div class="col-lg-6">
                        <form method="post" action="./subscribe.php" class="newsletter-form">
                            <div class="input-group">
                                <input type="text" placeholder="Your name" name="name" class="form-control" required>
                                <input type="email" placeholder="Email address" name="email" class="form-control" required>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-envelope-check me-2"></i>Subscribe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-5 col-md-12 footer-info">
                        <a href="/" class="logo d-flex align-items-center mb-3">
                            <img src="Assests/favicon.png" alt="KayXchange" class="me-3" width="50">
                            <span class="h4 text-success fw-bold mb-0">KayXchange</span>
                        </a>
                        <p class="mb-4">Your trusted platform for seamless cryptocurrency trading. Experience secure, fast, and reliable crypto-to-NGN conversions with competitive rates.</p>
                        <div class="social-links mt-4">
                            <a href="https://www.twitter.com/kay__xchange" class="twitter" data-bs-toggle="tooltip" title="Follow us on Twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?phone=+2349016740523&text=Hello%2C%20I%20would%20like%20to%20start%20a%20trade" class="whatsapp" data-bs-toggle="tooltip" title="Chat on WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://www.instagram.com/kay__xchange" class="instagram" data-bs-toggle="tooltip" title="Follow us on Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="https://t.snapchat.com/nIuJb4u1" class="snapchat" data-bs-toggle="tooltip" title="Add us on Snapchat">
                                <i class="bi bi-snapchat"></i>
                            </a>
                            <a href="https://t.me/TradewithkayxchangeBOT" class="telegram" data-bs-toggle="tooltip" title="Join our Telegram Bot">
                                <i class="bi bi-telegram"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-2 col-6 footer-links">
                        <h4 class="text-success fw-bold">Quick Links</h4>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('dashboard') }}" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>Dashboard</a></li>
                            <li><a href="{{ route('buy') }}" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>Buy Crypto</a></li>
                            <li><a href="{{ route('sell.form') }}" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>Sell Crypto</a></li>
                            <li><a href="{{ route('transactions.history') }}" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>Transactions</a></li>
                            <li><a href="{{ route('feature.request.form') }}" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>Feature Request</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-6 footer-links">
                        <h4 class="text-success fw-bold">Services</h4>
                        <ul class="list-unstyled">
                            <li><a href="/rate" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>Exchange Rates</a></li>
                            <li><a href="/about" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>About Us</a></li>
                            <li><a href="/faqs" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>FAQs</a></li>
                            <li><a href="/blog" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>Blog</a></li>
                            <li><a href="/kyc" class="text-decoration-none"><i class="bi bi-chevron-right me-2"></i>KYC</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-12 footer-contact">
                        <h4 class="text-success fw-bold">Contact Info</h4>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-envelope-fill text-success me-3 fs-5"></i>
                            <div>
                                <strong>Email</strong><br>
                                <a href="mailto:support@kayxchange.net" class="text-decoration-none">support@kayxchange.net</a>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-telephone-fill text-success me-3 fs-5"></i>
                            <div>
                                <strong>Phone</strong><br>
                                <a href="tel:+2349016740523" class="text-decoration-none">+234 901 674 0523</a>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-geo-alt-fill text-success me-3 fs-5"></i>
                            <div>
                                <strong>Location</strong><br>
                                <span>Nigeria</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h6 class="text-success fw-bold mb-2">Available 24/7</h6>
                            <small class="text-muted">Round-the-clock support for all your trading needs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="copyright text-center text-md-start">
                        &copy; <span id="currentYear"></span> <strong><span class="text-success">KayXchange</span></strong>.
                        All Rights Reserved | <a href="/privacy" class="text-decoration-none">Privacy Policy</a> | <a href="/terms" class="text-decoration-none">Terms of Service</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center text-md-end">
                        <div class="d-flex justify-content-center justify-content-md-end gap-3">
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="bi bi-shield-check me-1"></i>Secure Trading
                            </span>
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="bi bi-lightning me-1"></i>Instant Transfers
                            </span>
                            <span class="badge bg-success-subtle text-success px-3 py-2">
                                <i class="bi bi-star me-1"></i>Best Rates
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script>
        var ml4 = {};
        ml4.opacityIn = [0, 1];
        ml4.scaleIn = [0.2, 1];
        ml4.scaleOut = 3;
        ml4.durationIn = 800;
        ml4.durationOut = 600;
        ml4.delay = 500;

        anime.timeline({
                loop: true
            })
            .add({
                targets: '.ml4 .letters-1',
                opacity: ml4.opacityIn,
                scale: ml4.scaleIn,
                duration: ml4.durationIn
            }).add({
                targets: '.ml4 .letters-1',
                opacity: 0,
                scale: ml4.scaleOut,
                duration: ml4.durationOut,
                easing: "easeInExpo",
                delay: ml4.delay
            }).add({
                targets: '.ml4 .letters-2',
                opacity: ml4.opacityIn,
                scale: ml4.scaleIn,
                duration: ml4.durationIn
            }).add({
                targets: '.ml4 .letters-2',
                opacity: 0,
                scale: ml4.scaleOut,
                duration: ml4.durationOut,
                easing: "easeInExpo",
                delay: ml4.delay
            }).add({
                targets: '.ml4 .letters-3',
                opacity: ml4.opacityIn,
                scale: ml4.scaleIn,
                duration: ml4.durationIn
            }).add({
                targets: '.ml4 .letters-3',
                opacity: 0,
                scale: ml4.scaleOut,
                duration: ml4.durationOut,
                easing: "easeInExpo",
                delay: ml4.delay
            }).add({
                targets: '.ml4',
                opacity: 0,
                duration: 500,
                delay: 500
            });
    </script>

    <!-- Template Main JS File -->
    <script>
        const toggleModeButton = document.getElementById('toggle-mode');
        const contentElement = document.getElementById('content');

        toggleModeButton.addEventListener('click', () => {
            if (contentElement.classList.contains('dark-mode')) {
                contentElement.classList.remove('dark-mode');
            } else {
                contentElement.classList.add('dark-mode');
            }
        });
    </script>

    <!-- Home Telegram QR Code Modal -->
    <div class="modal fade" id="homeTelegramModal" tabindex="-1" aria-labelledby="homeTelegramModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #0088cc 0%, #005fa3 100%); color: white;">
                    <h5 class="modal-title" id="homeTelegramModalLabel">
                        <i class="fab fa-telegram-plane me-2"></i>
                        Connect with KayXchange Bot
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-3">Get instant notifications for all your trades!</p>
                    <div id="homeQrcode" class="d-flex justify-content-center mb-3"></div>
                    <div class="alert" style="background-color: #e3f2fd; border-color: #0088cc; color: #1565c0;">
                        <i class="fas fa-bell me-2"></i>
                        <strong>Get notified about:</strong>
                        <ul class="mb-0 mt-2 text-start">
                            <li>Trade confirmations</li>
                            <li>Rate updates</li>
                            <li>Security alerts</li>
                            <li>Withdrawal status</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" class="btn btn-telegram-cta">
                        <i class="fab fa-telegram-plane me-2"></i>
                        Open Bot
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Multiple QR Code library sources for better reliability -->
    <script>
        // QR Code generation with multiple fallback sources
        let qrCodeLibraryLoaded = false;
        
        function loadQRCodeLibrary() {
            return new Promise((resolve, reject) => {
                // Try primary CDN first
                const script1 = document.createElement('script');
                script1.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
                script1.onload = () => {
                    qrCodeLibraryLoaded = true;
                    resolve();
                };
                script1.onerror = () => {
                    // Try alternative CDN
                    const script2 = document.createElement('script');
                    script2.src = 'https://unpkg.com/qrcode@1.5.3/build/qrcode.min.js';
                    script2.onload = () => {
                        qrCodeLibraryLoaded = true;
                        resolve();
                    };
                    script2.onerror = () => {
                        // Try another alternative
                        const script3 = document.createElement('script');
                        script3.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js';
                        script3.onload = () => {
                            qrCodeLibraryLoaded = true;
                            resolve();
                        };
                        script3.onerror = () => {
                            reject(new Error('All QR Code libraries failed to load'));
                        };
                        document.head.appendChild(script3);
                    };
                    document.head.appendChild(script2);
                };
                document.head.appendChild(script1);
            });
        }

        // Generate QR code with fallback to text link
        function generateQRCode() {
            const qrCodeDiv = document.getElementById('homeQrcode');
            const botUrl = 'https://t.me/TradewithkayxchangeBOT';
            
            qrCodeDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            
            if (!qrCodeLibraryLoaded) {
                loadQRCodeLibrary().then(() => {
                    createQRCode(qrCodeDiv, botUrl);
                }).catch((error) => {
                    console.error('QR Code library loading failed:', error);
                    showFallbackLink(qrCodeDiv, botUrl);
                });
            } else {
                createQRCode(qrCodeDiv, botUrl);
            }
        }

        function createQRCode(container, url) {
            try {
                if (typeof QRCode !== 'undefined') {
                    QRCode.toCanvas(url, {
                        width: 200,
                        height: 200,
                        colorDark: '#0088cc',
                        colorLight: '#ffffff',
                        margin: 2,
                        errorCorrectionLevel: 'M'
                    }, function (error, canvas) {
                        if (error) {
                            console.error('QR Code generation error:', error);
                            showFallbackLink(container, url);
                        } else {
                            container.innerHTML = '';
                            canvas.style.borderRadius = '8px';
                            canvas.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                            container.appendChild(canvas);
                        }
                    });
                } else {
                    showFallbackLink(container, url);
                }
            } catch (e) {
                console.error('QR Code generation exception:', e);
                showFallbackLink(container, url);
            }
        }

        function showFallbackLink(container, url) {
            container.innerHTML = `
                <div class="text-center">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        QR Code unavailable, but you can still access the bot directly!
                    </div>
                    <a href="${url}" target="_blank" class="btn btn-primary btn-lg">
                        <i class="fab fa-telegram-plane me-2"></i>
                        Open Telegram Bot
                    </a>
                    <div class="mt-3">
                        <small class="text-muted">Or search for <strong>@TradewithkayxchangeBOT</strong> in Telegram</small>
                    </div>
                </div>
            `;
        }

        // Generate QR code when modal is shown
        document.getElementById('homeTelegramModal').addEventListener('shown.bs.modal', generateQRCode);
    </script>

    <!-- Custom page-specific scripts -->
    <script src="./assets/js/prices.js"></script>

    <!-- Footer JavaScript -->
    <script>
        // Set current year in footer
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Newsletter form handling
        document.addEventListener('DOMContentLoaded', function() {
            const newsletterForm = document.querySelector('.newsletter-form');

            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const form = this;
                    const formData = new FormData(form);
                    const submitBtn = form.querySelector('.btn');

                    // Add loading state
                    form.classList.add('submitting');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Subscribing...';

                    // Simulate form submission (replace with actual AJAX call)
                    setTimeout(() => {
                        // Remove loading state
                        form.classList.remove('submitting');
                        submitBtn.innerHTML = originalText;

                        // Show success message
                        showSuccessMessage('Thank you for subscribing! Check your email for confirmation.');

                        // Reset form
                        form.reset();
                    }, 2000);
                });
            }
        });

        // Success message function
        function showSuccessMessage(message) {
            // Remove existing success message if any
            const existingMessage = document.querySelector('.newsletter-success');
            if (existingMessage) {
                existingMessage.remove();
            }

            // Create new success message
            const successDiv = document.createElement('div');
            successDiv.className = 'newsletter-success';
            successDiv.innerHTML = `
                <i class="bi bi-check-circle-fill me-2"></i>
                ${message}
                <button type="button" class="btn-close btn-close-white ms-3" onclick="this.parentElement.remove()"></button>
            `;

            document.body.appendChild(successDiv);

            // Show message
            setTimeout(() => successDiv.classList.add('show'), 100);

            // Auto hide after 5 seconds
            setTimeout(() => {
                successDiv.classList.remove('show');
                setTimeout(() => successDiv.remove(), 300);
            }, 5000);
        }

        // Back to top button functionality
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopBtn = document.querySelector('.back-to-top');

            if (backToTopBtn) {
                // Show/hide button based on scroll position
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopBtn.classList.add('show');
                    } else {
                        backToTopBtn.classList.remove('show');
                    }
                });

                // Smooth scroll to top
                backToTopBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });

        // Enhanced footer animations
        document.addEventListener('DOMContentLoaded', function() {
            const footer = document.querySelector('.footer');
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            if (footer) {
                observer.observe(footer);
            }
        });

        // Social media link tracking (optional analytics)
        document.addEventListener('DOMContentLoaded', function() {
            const socialLinks = document.querySelectorAll('.social-links a');

            socialLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const platform = this.className.split(' ')[0];
                    // You can add analytics tracking here
                    console.log(`Social media click: ${platform}`);
                });
            });
        });

        // Newsletter form validation enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[name="email"]');
            const nameInput = document.querySelector('input[name="name"]');

            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value;
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (email && !emailRegex.test(email)) {
                        this.style.borderColor = '#dc3545';
                        this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                    } else {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }
                });
            }

            if (nameInput) {
                nameInput.addEventListener('blur', function() {
                    if (this.value.length < 2) {
                        this.style.borderColor = '#dc3545';
                        this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                    } else {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }
                });
            }
        });

        // Add loading animation to footer on page load
        window.addEventListener('load', function() {
            const footer = document.querySelector('.footer');
            if (footer) {
                footer.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
            }
        });
    </script>
@endsection