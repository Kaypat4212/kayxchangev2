@extends('layouts.header')
<!-- <body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">KayXchange</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        @yield('content')
    </div>
</body> -->

<body id="content" class="mt-5">

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
    </style>


    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top">
        <div class="container-fluid container-xl d-flex align-items-center justify-content-between">

            <!-- <button id="toggle-mode">Toggle Dark Mode</button> -->
            <a href="index.html" class="logo d-flex align-items-center">
                <img width="70px" src="/Assests/favicon.png" alt="">
            </a>


            <nav id="navbar" class="navbar">

                <ul>

                   


                    @auth
                    <!-- <li><a class="getstarted mx-4" href="/dashboard">Dashboard</a></li> -->
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="getstarted mx-4">Logout</button>
                        </form>
                    </li>
                    <li>
                        <a href="/rates">Rates</a>
                    </li>
                    <li>
                        <a href="/#">Buy Crypto</a>
                    </li>
                    <li>
                        <a href="/#">Sell Crypto</a>
                    </li>
                    @else
                    <li><a class="getstarted mx-4" href="/register">Register</a></li>
                    <li><a class="getstarted mx-4" href="/login">Login</a></li>
                    <li><a class="nav-link scrollto active" href="/index.html">Home</a></li>
                    <li><a class="nav-link scrollto" href="/Rates.html">Exchange Rates</a></li>
                    <li><a class="nav-link scrollto" href="/blog.html">Blog</a></li>
                    <li><a class="nav-link scrollto" href="/Faqs.html">Faqs</a></li>
                    <li><a class="nav-link scrollto" href="/About-us.html">About us</a></li>
                    <li><a href="/blog.html">Learn</a></li>
                    <li class="dropdown"><a href="#"><span>More</span> <i class="bi bi-chevron-down"></i></a>
                        <ul>
                            <li><a href="/Learn.html">Learn</a></li>
                            <li class="dropdown"><a href="/blog.html"><span>Blog</span> <i class="bi bi-chevron-right"></i></a>
                                <ul>
                                    <li><a href="/Blogpost/introductiontoblockchain.html">Introduction to cryptocurrency</a></li>
                                    <li><a href="/Blogpost/Typesofcryptocurrency.html">Types of cryptocurrency</a></li>
                                    <li><a href="/Blogpost/Thebasicsofcryptocurrency.html">The basics of cryptocurrency</a></li>
                                    <!-- <li><a href="#">Deep Drop Down 4</a></li>
                  <li><a href="#">Deep Drop Down 5</a></li> -->
                                </ul>
                            </li>
                            <li><a href="/alts.html">Alts</a></li>
                            <li><a href="/Fiats.html">Fiats</a></li>
                            <!-- <li><a href="#"></a></li> -->
                        </ul>
                    </li>
                    @endauth


                    <!-- <div class="d-flex justify-content-center">
                        <a href=""><img width="120px" src="/Assests/appstore.png" alt=""></a>
                    </div> -->
                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav>
            <!-- .navbar -->

        </div>
    </header><!-- End Header -->

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
                            <a href="/rates" class="btn-get-started scrollto d-inline-flex align-items-center justify-content-center align-self-center">
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

    <style>
        #prices-container {
            display: flex;
            justify-content: space-evenly;
            height: 80px;
            margin: auto;
            padding: auto;
            border-radius: 5px;

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

    @extends('layouts.footer')

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

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
    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="./assets/js/prices.js"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>