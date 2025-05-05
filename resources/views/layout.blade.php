<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .crypto-btn,
        .crypto-btn1 {
            width: 120px;
            height: 50px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-family: sans-serif;
            padding: 30px;
            border: 5px solid green;
        }

        .crypto-btn1 {
            background-color: white;
        }

        #loader {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>

<body class="bg-light">

    <!-- Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="/dashboard">
                <img src="/Assests/favicon.png" alt="Logo" width="50" height="50">
            </a>

            <!-- Navbar Toggler (for mobile) -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li><a class="nav-link scrollto active" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/rates">Rates</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/settings">Settings</a></li>

                    <!-- Logout Button -->
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Loader -->
    <div id="loader">
        <p>Loading...</p>
    </div>

    <!-- Content Section -->
    <div class="container mt-4">
        @yield('content')
    </div>

</body>

</html>