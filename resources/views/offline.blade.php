<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - KayXchange</title>
    <link rel="icon" type="image/png" href="{{ asset('Assests/favicon.png') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f1419 0%, #1a1f2e 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .offline-container {
            text-align: center;
            max-width: 500px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .offline-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 0.6;
            }
            50% {
                opacity: 1;
            }
        }

        .brand-logo {
            color: #00c851;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .brand-logo::before {
            content: "⚡";
            font-size: 1.5rem;
        }

        h1 {
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .subtitle {
            color: #00c851;
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 2rem;
        }

        .description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 3rem;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 200px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00c851 0%, #007e33 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 200, 81, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 200, 81, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .connection-status {
            margin-top: 2rem;
            padding: 1rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            background: #ff4757;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 50% {
                opacity: 1;
            }
            51%, 100% {
                opacity: 0.3;
            }
        }

        .features {
            margin-top: 3rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            text-align: left;
        }

        .feature {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
        }

        .feature h3 {
            color: #00c851;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .feature p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .offline-container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .description {
                font-size: 1rem;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                max-width: 280px;
            }

            .features {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        .refresh-animation {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">📡</div>
        
        <div class="brand-logo">KayXchange</div>
        
        <h1>You're Offline</h1>
        <p class="subtitle">No Internet Connection</p>
        
        <p class="description">
            Don't worry! Some features are still available while you're offline. 
            We'll automatically reconnect when your internet connection is restored.
        </p>

        <div class="actions">
            <button class="btn btn-primary" onclick="retryConnection()">
                <span class="retry-icon">🔄</span>
                Try Again
            </button>
            
            <a href="{{ url('/dashboard') }}" class="btn btn-secondary">
                📊 View Cached Dashboard
            </a>
        </div>

        <div class="connection-status">
            <span class="status-indicator"></span>
            <span id="status-text">Checking connection...</span>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">💾</div>
                <h3>Cached Data</h3>
                <p>View your previously loaded transactions and portfolio data</p>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🔒</div>
                <h3>Secure Storage</h3>
                <p>Your sensitive information remains protected offline</p>
            </div>
            
            <div class="feature">
                <div class="feature-icon">⚡</div>
                <h3>Quick Access</h3>
                <p>Fast loading of cached pages and data when back online</p>
            </div>
        </div>
    </div>

    <script>
        let retryAttempts = 0;
        const maxRetries = 3;

        function retryConnection() {
            const retryIcon = document.querySelector('.retry-icon');
            const statusText = document.getElementById('status-text');
            
            retryIcon.classList.add('refresh-animation');
            statusText.textContent = 'Retrying connection...';
            
            retryAttempts++;
            
            // Simulate connection check
            setTimeout(() => {
                if (navigator.onLine) {
                    statusText.textContent = 'Connection restored! Redirecting...';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    retryIcon.classList.remove('refresh-animation');
                    statusText.textContent = `Still offline (${retryAttempts}/${maxRetries} attempts)`;
                    
                    if (retryAttempts >= maxRetries) {
                        statusText.textContent = 'Please check your internet connection';
                    }
                }
            }, 2000);
        }

        // Check connection status periodically
        function checkConnection() {
            const statusIndicator = document.querySelector('.status-indicator');
            const statusText = document.getElementById('status-text');
            
            if (navigator.onLine) {
                statusIndicator.style.background = '#00c851';
                statusIndicator.style.animation = 'none';
                statusText.textContent = 'Connection restored! Click "Try Again" to continue';
            } else {
                statusIndicator.style.background = '#ff4757';
                statusIndicator.style.animation = 'blink 1.5s infinite';
                statusText.textContent = 'No internet connection detected';
            }
        }

        // Listen for online/offline events
        window.addEventListener('online', () => {
            checkConnection();
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        });

        window.addEventListener('offline', checkConnection);

        // Initial connection check
        checkConnection();

        // Check connection every 5 seconds
        setInterval(checkConnection, 5000);

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
                e.preventDefault();
                retryConnection();
            }
            
            if (e.key === 'Escape') {
                history.back();
            }
        });

        // Service Worker registration check
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.ready.then(registration => {
                console.log('Service Worker is ready');
            });
        }
    </script>
</body>
</html>