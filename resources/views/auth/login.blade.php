<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - KayXchange</title>

  <!-- Toastr CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  
  @include('components.navbar-head')

  <style>
    .dark-mode {
      background-color:rgb(0, 0, 0);
      color: #ffffff;
    }

    .dark-mode .card {
      background: #2d2d2d;
      color: #ffffff;
      border: 1px solid #444;
    }

    .dark-mode .form-control {
      background: #333;
      border-color: #555;
      color: #ffffff;
    }

    .dark-mode .form-control:focus {
      border-color: #00ff00;
      box-shadow: 0 0 5px 2px rgba(0, 255, 0, 0.3);
    }

    .dark-mode .navbar {
      background: #1a1a1a !important;
      border-bottom: 2px solid #00ff00;
    }

    .dark-mode .navbar-nav .nav-link {
      color: #ffffff !important;
    }

    .dark-mode .navbar-nav .nav-link:hover {
      color: #00ff00 !important;
    }

    .dark-mode .navbar-brand {
      color: #ffffff !important;
    }

    .card > a{
      color: green;
    }

    .card {
      max-width: 100%;
      margin: 20px auto;
      margin-top: 200px;
      padding: 50px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
      border-radius: 0% 10% 20% 10%;
      background: #fff;
      position: relative;
      overflow: hidden;
      animation: blobAnimation 10s infinite;
    }

    .card::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: rgba(0, 255, 0, 0.2);
      border-radius: 50%;
      animation: glow 3s infinite linear;
      z-index: -1;
    }

    @keyframes glow {
      0% {
        transform: rotate(0deg);
        box-shadow: 0 0 20px 5px rgba(0, 255, 0, 0.5);
      }
      50% {
        transform: rotate(180deg);
        box-shadow: 0 0 100px 10px rgba(0, 255, 0, 0.8);
      }
      100% {
        transform: rotate(360deg);
        box-shadow: 0 0 120px 35px rgba(0, 255, 0, 0.5);
      }
    }

    @keyframes blobAnimation {
      0% { border-radius: 30% 50% 50% 10%; }
      25% { border-radius: 20% 10% 10% 10%; }
      50% { border-radius: 10% 10% 40% 20%; }
      75% { border-radius: 0% 10% 20% 20%; }
      100% { border-radius: 0% 10% 20% 0%; }
    }

    .form-control {
      transition: all 0.3s ease-in-out;
    }

    .form-control:focus {
      border-color: #00ff00;
      box-shadow: 0 0 5px 2px rgba(0, 255, 0, 0.6);
    }

    button[type="submit"] {
      transition: background-color 0.3s, transform 0.3s;
    }

    button[type="submit"]:hover {
      background-color: #28a745;
      transform: scale(1.05);
    }

    .animated-image {
      width: 100px;
      margin: 0 auto;
      display: block;
      animation: fadeIn 2s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .card {
      width: 90%;
      max-width: 500px;
      padding: 25px;
    }

    @media (max-width: 576px) {
      .card {
        width: 100%;
        padding: 20px;
      }

      .navbar-brand img {
        width: 40px;
      }

      .animated-image {
        width: 80px;
      }
    }

    .navbar-brand img {
      width: 50px;
    }

    .navbar {
      border-bottom: black 2px solid;
      box-shadow: 9px 0px 5px 2px darkgreen;
    }

    footer {
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
      color: #ffffff;
      padding: 60px 0 30px;
      margin-top: 50px;
      position: relative;
      overflow: hidden;
    }

    footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 20% 80%, rgba(0, 255, 0, 0.1) 0%, transparent 50%),
                  radial-gradient(circle at 80% 20%, rgba(0, 255, 0, 0.1) 0%, transparent 50%);
      pointer-events: none;
    }

    .footer-content {
      position: relative;
      z-index: 1;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
    }

    .footer-section h4 {
      color: #00ff00;
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 20px;
      position: relative;
    }

    .footer-section h4::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 50px;
      height: 2px;
      background: linear-gradient(90deg, #00ff00, transparent);
    }

    .footer-section p {
      color: #cccccc;
      line-height: 1.6;
      margin-bottom: 15px;
    }

    .newsletter-form {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .newsletter-form input {
      flex: 1;
      padding: 12px 16px;
      border: 1px solid #444;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.1);
      color: #ffffff;
      font-size: 14px;
    }

    .newsletter-form input::placeholder {
      color: #aaaaaa;
    }

    .newsletter-form input:focus {
      outline: none;
      border-color: #00ff00;
      box-shadow: 0 0 0 2px rgba(0, 255, 0, 0.2);
    }

    .newsletter-form button {
      padding: 12px 24px;
      background: linear-gradient(135deg, #00ff00, #28a745);
      color: #000;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .newsletter-form button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 255, 0, 0.3);
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 45px;
      height: 45px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      color: #ffffff;
      text-decoration: none;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .social-links a::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(0, 255, 0, 0.2);
      border-radius: 50%;
      transition: all 0.3s ease;
      transform: translate(-50%, -50%);
    }

    .social-links a:hover::before {
      width: 100%;
      height: 100%;
    }

    .social-links a:hover {
      color: #00ff00;
      transform: translateY(-3px);
      box-shadow: 0 4px 15px rgba(0, 255, 0, 0.3);
    }

    .contact-info {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .contact-info li {
      display: flex;
      align-items: center;
      margin-bottom: 12px;
      color: #cccccc;
    }

    .contact-info i {
      color: #00ff00;
      margin-right: 12px;
      width: 20px;
      text-align: center;
    }

    .footer-bottom {
      border-top: 1px solid #444;
      padding-top: 30px;
      text-align: center;
    }

    .footer-bottom p {
      color: #888;
      margin: 0;
      font-size: 14px;
    }

    .footer-bottom a {
      color: #00ff00;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer-bottom a:hover {
      color: #28a745;
      text-decoration: underline;
    }

    .trust-badges {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin: 30px 0;
      flex-wrap: wrap;
    }

    .trust-badge {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #cccccc;
      font-size: 14px;
    }

    .trust-badge i {
      color: #00ff00;
      font-size: 16px;
    }

    @media (max-width: 768px) {
      .footer-grid {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .newsletter-form {
        flex-direction: column;
      }

      .social-links {
        justify-content: center;
      }

      .trust-badges {
        gap: 20px;
      }
    }

    /* Password toggle button styles */
    #toggle-password {
      cursor: pointer;
      color: #28a745;
      transition: color 0.3s ease;
    }

    #toggle-password:hover {
      color: #1e7e34;
    }

    #toggle-password:focus {
      box-shadow: none;
      outline: none;
    }
  </style>
</head>
<body>
@if(session('status'))
    <script type="text/javascript">
        toastr.success("{{ session('status') }}");
    </script>
@endif
@include('components.navbar')





<!-- Login Form -->
<div class="container">
  <div class="card p-4">
    <h3 class="text-center">Login</h3>

    @if (session('status'))
      <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      <img src="{{ asset('assets/favicon.png') }}" alt="Login Illustration" class="animated-image mt-3 mb-4">
      @csrf

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input id="email" type="email" class="form-control" name="email"
               value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="position-relative">
          <input id="password" type="password" class="form-control" name="password"
                 required autocomplete="current-password">
          <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent" 
                  id="toggle-password" style="z-index: 10; padding: 0 10px;" aria-label="Toggle password visibility">
            <i class="fas fa-eye" id="eye-icon"></i>
          </button>
        </div>
        @error('password')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-check mb-3">
        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
        <label for="remember_me" class="form-check-label text-success">Remember me</label>
      </div>

      <div class="d-flex justify-content-between align-items-center">
        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}" class="text-decoration-none text-success">Forgot your password?</a>
        @endif
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <a href="{{ route('register') }}" class="text-decoration-none text-success">Don't have an account? Register</a> 
      </div>
      <br>
        <button type="submit" class="btn btn-success"> <span>Log in</span></button>
    </form>
  </div>
</div>

<!-- Modern Footer -->
<footer>
  <div class="footer-content">
    <div class="footer-grid">
      <!-- Company Info -->
      <div class="footer-section">
        <h4>KayXchange</h4>
        <p>Your trusted cryptocurrency exchange platform. Buy, sell, and trade digital assets securely with competitive rates and fast transactions.</p>
        <div class="trust-badges">
          <div class="trust-badge">
            <i class="fas fa-shield-alt"></i>
            <span>Secure</span>
          </div>
          <div class="trust-badge">
            <i class="fas fa-clock"></i>
            <span>24/7 Support</span>
          </div>
          <div class="trust-badge">
            <i class="fas fa-lock"></i>
            <span>Encrypted</span>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="footer-section">
        <h4>Quick Links</h4>
        <ul class="contact-info">
          <li><a href="/rate" style="color: #cccccc; text-decoration: none;"><i class="fas fa-chart-line"></i> Exchange Rates</a></li>
          <li><a href="/blog" style="color: #cccccc; text-decoration: none;"><i class="fas fa-blog"></i> Blog</a></li>
          <li><a href="/faqs" style="color: #cccccc; text-decoration: none;"><i class="fas fa-question-circle"></i> FAQs</a></li>
          <li><a href="/about" style="color: #cccccc; text-decoration: none;"><i class="fas fa-info-circle"></i> About Us</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="footer-section">
        <h4>Contact Us</h4>
        <ul class="contact-info">
          <li><i class="fas fa-envelope"></i> support@kayxchange.com</li>
          <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
          <li><i class="fas fa-map-marker-alt"></i> Lagos, Nigeria</li>
          <li><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM GMT</li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div class="footer-section">
        <h4>Stay Updated</h4>
        <p>Subscribe to our newsletter for the latest crypto news and market updates.</p>
        <form class="newsletter-form" id="newsletter-form">
          <input type="email" placeholder="Enter your email" required>
          <button type="submit">Subscribe</button>
        </form>
        <div class="social-links">
          <a href="#" data-bs-toggle="tooltip" title="Follow us on Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" data-bs-toggle="tooltip" title="Follow us on Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" data-bs-toggle="tooltip" title="Follow us on Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" data-bs-toggle="tooltip" title="Join our Telegram"><i class="fab fa-telegram-plane"></i></a>
          <a href="#" data-bs-toggle="tooltip" title="Subscribe on YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; {{ date('Y') }} KayXchange. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a> | <a href="#">Cookie Policy</a></p>
    </div>
  </div>
</footer>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Optional JavaScript -->
<script>
  // Dark mode toggle functionality
  const toggleButton = document.getElementById('toggle-mode');
  const modeIcon = document.getElementById('mode-icon');
  const body = document.body;

  // Check for saved theme preference or default to light mode
  const currentTheme = localStorage.getItem('theme') || 'light';
  if (currentTheme === 'dark') {
    body.classList.add('dark-mode');
    if (modeIcon) modeIcon.classList.remove('bi-moon-stars-fill');
    if (modeIcon) modeIcon.classList.add('bi-sun-fill');
  }

  if (toggleButton) {
    toggleButton.addEventListener('click', function() {
      body.classList.toggle('dark-mode');
      
      if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        if (modeIcon) {
          modeIcon.classList.remove('bi-moon-stars-fill');
          modeIcon.classList.add('bi-sun-fill');
        }
      } else {
        localStorage.setItem('theme', 'light');
        if (modeIcon) {
          modeIcon.classList.remove('bi-sun-fill');
          modeIcon.classList.add('bi-moon-stars-fill');
        }
      }
    });
  }

  // Newsletter form handling
  const newsletterForm = document.getElementById('newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const email = this.querySelector('input[type="email"]').value;
      
      // Simple email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        toastr.error('Please enter a valid email address');
        return;
      }
      
      // Simulate subscription (replace with actual API call)
      toastr.success('Thank you for subscribing! We\'ll keep you updated.');
      this.reset();
    });
  }

  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Back to top functionality
  const backToTopBtn = document.getElementById('back-to-top');
  if (backToTopBtn) {
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
        backToTopBtn.style.display = 'block';
      } else {
        backToTopBtn.style.display = 'none';
      }
    });

    backToTopBtn.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  document.addEventListener("DOMContentLoaded", function () {
    // Password toggle functionality
    const togglePasswordButton = document.getElementById('toggle-password');
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (togglePasswordButton && passwordField && eyeIcon) {
      togglePasswordButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Toggle eye icon
        if (type === 'text') {
          eyeIcon.classList.remove('fa-eye');
          eyeIcon.classList.add('fa-eye-slash');
          togglePasswordButton.setAttribute('aria-label', 'Hide password');
        } else {
          eyeIcon.classList.remove('fa-eye-slash');
          eyeIcon.classList.add('fa-eye');
          togglePasswordButton.setAttribute('aria-label', 'Show password');
        }
      });
    }
  });
</script>

@include('components.navbar-scripts')

<script>
  // Configure Toastr options
  toastr.options = {
    "positionClass": "toast-top-right",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showMethod": "slideDown",
    "hideMethod": "slideUp",
    "preventDuplicates": true
  };

  // Show status message if present
  @if(session('status'))
    toastr.success("{{ session('status') }}");
  @endif

  @if(session('error'))
    toastr.error("{{ session('error') }}");
  @endif
</script>

</body>
</html>
