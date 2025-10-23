@extends('selllayout')

@section('content')
<div class="min-h-screen bg-gray-900 flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-md bg-gray-800 rounded-2xl shadow-2xl p-6 sm:p-8 transform transition-all hover:shadow-blue-500/20 duration-300 text-center">
        <!-- Success SVG -->
        <div class="mb-4 sm:mb-6">
            <svg class="w-20 h-20 sm:w-24 sm:h-24 mx-auto" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="40" stroke="#14b8a6" stroke-width="4" fill="none">
                    <animate attributeName="r" from="30" to="40" dur="1s" repeatCount="indefinite" />
                    <animate attributeName="opacity" from="0.5" to="1" dur="1s" repeatCount="indefinite" />
                </circle>
                <path d="M30 50 L45 65 L70 35" stroke="#2563eb" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
                    <animate attributeName="stroke-dasharray" from="0 100" to="100 0" dur="0.8s" fill="freeze" />
                </path>
                <circle cx="30" cy="30" r="5" fill="#14b8a6">
                    <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="2s" repeatCount="indefinite" />
                </circle>
                <circle cx="70" cy="30" r="5" fill="#2563eb">
                    <animateTransform attributeName="transform" type="rotate" from="360 50 50" to="0 50 50" dur="2s" repeatCount="indefinite" />
                </circle>
            </svg>
        </div>

        <!-- Success Message -->
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-3">Sell Trade Successful!</h2>
        <p class="text-sm sm:text-base text-gray-300 mb-3">You have successfully sold ${{ number_format($trade->usd_amount, 2) }} of {{ $trade->coin }}. <br> Your transaction is being processed.</p>
        <p class="text-xs sm:text-sm text-gray-400 mb-4">Equivalent to ₦{{ number_format($trade->naira_amount, 2) }}.</p>
        <p class="text-xs sm:text-sm text-gray-400 mb-6">Please check your <a href="{{ route('transactions') }}" class="text-blue-400 hover:underline">transaction history</a> to monitor the status of your trade.</p>

        <!-- Support Information -->
        <div class="mb-4 sm:mb-6">
            <p class="text-xs sm:text-sm text-gray-400">Need assistance? Contact our support team:</p>
            <a href="mailto:support@tradefinance.com" class="text-blue-400 hover:underline font-semibold text-xs sm:text-sm">support@tradefinance.com</a>
        </div>

        <!-- Dashboard Button -->
        <a href="{{ route('dashboard') }}" class="w-full bg-gradient-to-r from-blue-600 to-teal-500 p-3 rounded-lg font-semibold text-white shadow-md hover:from-blue-700 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 inline-block text-sm sm:text-base">
            Go to Dashboard
        </a>
    </div>
</div>

<style>
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: #111827;
        margin: 0;
        padding: 0;
    }

    .min-h-screen {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .max-w-md {
        background-color: #1f2937;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .max-w-md:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.3);
    }

    .text-xl {
        font-size: clamp(1.125rem, 4vw, 1.25rem);
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 0.75rem;
    }

    .text-2xl {
        font-size: clamp(1.25rem, 5vw, 1.75rem);
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 0.75rem;
    }

    .text-sm {
        font-size: clamp(0.75rem, 3vw, 0.875rem);
        color: #d1d5db;
        margin-bottom: 0.75rem;
    }

    .text-base {
        font-size: clamp(0.875rem, 3.5vw, 1rem);
        color: #d1d5db;
        margin-bottom: 1rem;
    }

    .text-xs {
        font-size: clamp(0.7rem, 2.5vw, 0.75rem);
        color: #9ca3af;
        margin-bottom: 1rem;
    }

    .text-blue-400 {
        color: #60a5fa;
        transition: color 0.2s ease;
    }

    .text-blue-400:hover {
        color: #3b82f6;
    }

    .w-full.bg-gradient-to-r.from-blue-600.to-teal-500 {
        width: 100%;
        padding: clamp(0.6rem, 2.5vw, 0.75rem);
        border-radius: 0.5rem;
        background-image: linear-gradient(to right, #2563eb, #14b8a6);
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-image 0.2s ease, transform 0.2s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .w-full.bg-gradient-to-r.from-blue-600.to-teal-500:hover {
        background-image: linear-gradient(to right, #1d4ed8, #0d9488);
        transform: translateY(-2px);
    }

    .w-full.bg-gradient-to-r.from-blue-600.to-teal-500:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
    }

    @media (min-width: 640px) {
        .max-w-md {
            padding: 2rem;
        }

        .text-xl {
            font-size: 1.5rem;
        }

        .text-2xl {
            font-size: 1.75rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-base {
            font-size: 1rem;
        }

        .text-xs {
            font-size: 0.875rem;
        }

        .w-20 {
            width: 6rem;
            height: 6rem;
        }
    }

    @media (max-width: 639px) {
        .w-20 {
            width: 5rem;
            height: 5rem;
        }
    }
</style>

<!-- Tawk.to Chat Widget -->
<script type="text/javascript">
    var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
    (function(){
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/YOUR_PROPERTY_ID/YOUR_WIDGET_ID';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
</script>

@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @if(session('success'))
        <script>
            toastr.success('{!! e(session('success')) !!}');
        </script>
    @elseif(session('error'))
        <script>
            toastr.error('{!! e(session('error')) !!}');
        </script>
    @endif
@endsection