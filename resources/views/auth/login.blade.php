<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | KayXchange</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .dark-mode {
      background-color:rgb(0, 0, 0);
      color: #ffffff;
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
      background-color: #f1f1f1;
      text-align: center;
      padding: 20px;
      margin-top: 30px;
      box-shadow: 0px -3px 5px rgba(0, 0, 0, 0.1);
    }

    footer p {
      margin: 0;
      font-size: 14px;
      color: #555;
    }

    footer a {
      color: #28a745;
      text-decoration: none;
    }

    footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
@if(session('status'))
    <script type="text/javascript">
        toastr.success("{{ session('status') }}");
    </script>
@endif
<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-light fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <img src="/assets/favicon.png" alt="Logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/rates">Rates</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Services</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
             data-bs-toggle="dropdown" aria-expanded="false">
            Crypto
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="#">Buy Crypto</a></li>
            <li><a class="dropdown-item" href="#">Sell Crypto</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Support</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>





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
        <input id="password" type="password" class="form-control" name="password"
               required autocomplete="current-password">
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

<!-- Footer -->
<footer>
  <p>&copy; {{ date('Y') }} KayXchange. All rights reserved. <a href="#">Privacy Policy</a></p>
</footer>

<!-- Bootstrap JS (required for dropdown and navbar toggling) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Optional JavaScript -->
<script>
  function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
  }

  // Collapse navbar on link click (for mobile)
  document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll('.nav-link');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    navLinks.forEach(function(link) {
      link.addEventListener('click', function () {
        if (navbarCollapse.classList.contains('show')) {
          const bootstrapCollapse = new bootstrap.Collapse(navbarCollapse);
          bootstrapCollapse.hide();
        }
      });
    });
  });
</script>

<script>
  toastr.options = {
    "positionClass": "toast-top-right", // Positioning
    "timeOut": "500", // Duration for the toast
};

toastr.success("{{ session('status') }}");

</script>

</body>
</html>
