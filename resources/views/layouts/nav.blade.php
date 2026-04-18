@extends('layouts.header')

<nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img width="40px" src="{{ asset('Assests/favicon.png') }}" alt="KayXchange" class="me-2">
            KayXchange
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#layoutNavbarNav" aria-controls="layoutNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="layoutNavbarNav">
            <div class="navbar-nav me-auto">
                <a class="nav-link @if(request()->is('/')) active @endif" @if(request()->is('/')) aria-current="page" @endif href="{{ url('/') }}">Home</a>
                <a class="nav-link @if(request()->is('rate')) active @endif" @if(request()->is('rate')) aria-current="page" @endif href="{{ url('/rate') }}">Exchange Rates</a>
                <a class="nav-link @if(request()->is('blog*')) active @endif" @if(request()->is('blog*')) aria-current="page" @endif href="{{ url('/blog') }}">Blog</a>
                <a class="nav-link @if(request()->is('faqs*')) active @endif" @if(request()->is('faqs*')) aria-current="page" @endif href="{{ url('/faqs') }}">FAQs</a>
                <a class="nav-link @if(request()->is('about*')) active @endif" @if(request()->is('about*')) aria-current="page" @endif href="{{ url('/about') }}">About Us</a>
                
                <!-- More Dropdown -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots me-1"></i>More
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ url('/Learn.html') }}"><i class="bi bi-book me-2"></i>Learn</a></li>
                        <li><a class="dropdown-item" href="{{ url('/blog') }}"><i class="bi bi-newspaper me-2"></i>Blog</a></li>
                        <li><a class="dropdown-item" href="{{ url('/alts.html') }}"><i class="bi bi-currency-exchange me-2"></i>Alts</a></li>
                        <li><a class="dropdown-item" href="{{ url('/Fiats.html') }}"><i class="bi bi-bank me-2"></i>Fiats</a></li>
                    </ul>
                </div>
            </div>

            <!-- Right side navigation -->
            <div class="d-flex align-items-center">
                <a class="btn btn-outline-primary me-2" href="{{ route('login') }}">
                  <i class="bi bi-box-arrow-in-right me-1"></i>Login
                </a>
                <a class="btn btn-primary" href="{{ route('register') }}">
                  <i class="bi bi-person-plus me-1"></i>Register
                </a>

                <!-- Dark Mode Toggle Button -->
                <button id="toggle-mode" class="btn btn-outline-secondary ms-2" title="Toggle Dark Mode">
                  <i class="bi bi-moon-stars-fill" id="mode-icon"></i>
                </button>
            </div>
        </div>
    </div>
</nav>