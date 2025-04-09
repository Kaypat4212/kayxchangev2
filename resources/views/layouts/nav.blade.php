@extends('layouts.header')


<nav id="navbar" class="navbar">

<ul>
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

    <li><a class="getstarted mx-4" href="/register">Register</a></li>
    <li><a class="getstarted mx-4" href="/login">Login</a></li>
    <div class="d-flex justify-content-center">
        <a href=""><img width="120px" src="/Assests/appstore.png" alt=""></a>
    </div>
</ul>
<i class="bi bi-list mobile-nav-toggle"></i>
</nav>
<!-- .navbar -->