<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - KayXchange</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    function applyTheme(light) {
        if (light) { document.body.classList.add('light-mode'); var ic=document.getElementById('mode-icon'); if(ic) ic.className='bi bi-sun-fill'; }
        else        { document.body.classList.remove('light-mode'); var ic=document.getElementById('mode-icon'); if(ic) ic.className='bi bi-moon-stars-fill'; }
    }
    function wireToggle() {
        var btn = document.getElementById('toggle-mode');
        if (btn && !btn._kxWired) {
            btn._kxWired = true;
            btn.addEventListener('click', function() {
                var nowLight = !document.body.classList.contains('light-mode');
                applyTheme(nowLight);
                localStorage.setItem('theme', nowLight ? 'light' : 'dark');
            });
        }
    }
    applyTheme(localStorage.getItem('theme') === 'light');
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { applyTheme(localStorage.getItem('theme') === 'light'); wireToggle(); });
    } else { wireToggle(); }
})();
</script>
</body>

</html>

