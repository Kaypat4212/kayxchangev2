<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Trade - KayXchange</title>
    <!-- Toastr CSS -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
    @include('components.navbar-head')
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

    @include('components.navbar')

    <!-- Loader -->
    <div id="loader">
        <p>Loading...</p>
    </div>

    <!-- Content Section -->
    <div class="container mt-4">
        @yield('content')
    </div>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tronweb@5.3.2/dist/TronWeb.min.js"></script>

<script>
    if (typeof TronWeb === 'undefined') {
        console.error('TronWeb library is not loaded');
    } else {
        console.log('TronWeb library loaded successfully');
    }
</script>

@include('components.navbar-scripts')
</body>

</html>