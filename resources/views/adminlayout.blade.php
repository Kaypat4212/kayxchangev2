<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - KayXchange</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')
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

        /* Dark mode support */
        body.dark-mode {
            background-color: #1a1a1a !important;
            color: #f9fafb !important;
        }

        body.dark-mode .bg-light {
            background-color: #1a1a1a !important;
        }

        body.dark-mode .text-dark {
            color: #f9fafb !important;
        }

        body.dark-mode .text-muted {
            color: #9ca3af !important;
        }

        body.dark-mode .border {
            border-color: #374151 !important;
        }

        body.dark-mode .card {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #f9fafb !important;
        }

        body.dark-mode .form-control {
            background-color: #111827 !important;
            border-color: #374151 !important;
            color: #f9fafb !important;
        }

        body.dark-mode .form-control:focus {
            background-color: #111827 !important;
            border-color: #60a5fa !important;
            color: #f9fafb !important;
        }

        body.dark-mode .btn-outline-secondary {
            border-color: #6b7280 !important;
            color: #d1d5db !important;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background-color: #374151 !important;
            border-color: #9ca3af !important;
            color: #f9fafb !important;
        }
    </style>
</head>

<body class="bg-light">

    @include('components.admin-navbar')

    <!-- Loader -->
    <div id="loader">
        <p>Loading...</p>
    </div>

    <!-- Content Section -->
    <div class="container" style="margin-top: 120px;">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

</body>

</html>