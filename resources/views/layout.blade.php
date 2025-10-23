<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
     
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
   
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
    <div class="container" style="margin-top: 120px;">
        @yield('content')
    </div>

    <!-- Telegram Floating Action Button (only for authenticated users) -->
    @auth
    @if(!Auth::user()->telegram_verified)
    <div class="telegram-fab" id="telegramFab">
        <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" class="fab-button" title="Connect Telegram for Notifications">
            <i class="fab fa-telegram-plane"></i>
            <span class="fab-text">Connect</span>
        </a>
    </div>

    <style>
        .telegram-fab {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: pulse 2s infinite;
        }

        .fab-button {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0088cc 0%, #005fa3 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(0, 136, 204, 0.4);
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 14px;
        }

        .fab-button:hover {
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 136, 204, 0.6);
        }

        .fab-button i {
            font-size: 18px;
            margin-right: 8px;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @media (max-width: 768px) {
            .telegram-fab {
                bottom: 15px;
                right: 15px;
            }
            
            .fab-button {
                padding: 10px 16px;
                font-size: 12px;
            }
            
            .fab-text {
                display: none;
            }
            
            .fab-button i {
                margin-right: 0;
            }
        }

        /* Hide FAB on Telegram settings page */
        body.telegram-settings .telegram-fab {
            display: none;
        }
    </style>

    <script>
        // Add Font Awesome if not already included
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const fontAwesome = document.createElement('link');
            fontAwesome.rel = 'stylesheet';
            fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
            document.head.appendChild(fontAwesome);
        }

        // Hide FAB if user gets verified
        function checkAndHideFab() {
            fetch('/api/user/telegram-status', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.verified) {
                    const fab = document.getElementById('telegramFab');
                    if (fab) {
                        fab.style.display = 'none';
                    }
                }
            })
            .catch(error => console.log('FAB status check error:', error));
        }

        // Check every 30 seconds
        setInterval(checkAndHideFab, 30000);
    </script>
    @endif
    @endauth

    @include('components.navbar-scripts')

</body>

</html>