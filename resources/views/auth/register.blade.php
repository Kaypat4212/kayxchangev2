<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KayXchange</title>

    @include('components.navbar-head')

    <style>
        body {
            background: linear-gradient(45deg, #2d6a4f, #84a98c, #52796f);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            min-height: 100vh;
            color: #333;
        }

        .card {
            max-width: 420px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            background: rgba(255, 255, 255, 0.95);
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .dark-mode {
            background: linear-gradient(45deg, #0a0a0a, #1a1a1a, #0a0a0a);
            color: #ffffff;
        }

        .dark-mode .card {
            background: rgba(45, 45, 45, 0.95);
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

        .navbar {
            border-bottom: 2px solid black;
            box-shadow: 0px 3px 10px rgba(0,0,0,0.2);
        }

        .navbar-nav .nav-link {
            color: #fff;
        }

        .navbar-nav .nav-link:hover {
            color: #84a98c;
        }

        .navbar-brand img {
            height: 40px;
            animation: fadeIn 2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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
    </style>
</head>
<body>

@include('components.navbar')

<!-- Register Form Card -->
<div class="container">
    <div class="card glow-effect">
        <h3 class="text-center mb-4">Create Account</h3>
        <p>Fill the form correctly to create an account</p>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input id="name" type="text" class="form-control" placeholder="(Eg: FirstName LastName)" name="name" value="{{ old('name') }}" required autofocus>
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input id="email" type="email" class="form-control" name="email" placeholder="johndoe@gmail.com" value="{{ old('email') }}" required>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" class="form-control" name="password" placeholder="12345678$" required>
                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" placeholder="12345678$" required>
                @error('password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="referral_code" class="form-label">Referral Code (Optional)</label>
                <input id="referral_code" type="text" class="form-control" name="referral_code" value="{{ request()->query('ref') ?? old('referral_code') }}">
                @error('referral_code') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success">Register</button>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-decoration-none">Already have an account? Log in</a>
            </div>
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

@include('components.navbar-scripts')

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
        alert('Please enter a valid email address');
        return;
      }
      
      // Simulate subscription (replace with actual API call)
      alert('Thank you for subscribing! We\'ll keep you updated.');
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
</script>
</body>
</html>