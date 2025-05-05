@extends('buylayout')

@section('content')
<div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-gray-800 rounded-2xl shadow-2xl p-8 transform transition-all hover:shadow-blue-500/20 duration-300">
        <h3 class="text-2xl font-bold text-center text-white mb-2">Trade Summary</h3>
        <p class="text-center text-gray-400 mb-6">Review your purchase details below.</p>

        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Trade ID:</span>
                <span class="text-sm font-semibold text-white">{{ $trade->id }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Coin:</span>
                <span class="text-sm font-semibold text-white">{{ $trade->coin }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Amount (USD):</span>
                <span class="text-sm font-semibold text-white">${{ number_format($trade->usd_amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Amount (NGN):</span>
                <span class="text-sm font-semibold text-white">₦{{ number_format($trade->naira_amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Wallet Address:</span>
                <span class="text-sm font-semibold text-white break-all">{{ $trade->wallet_address }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Network:</span>
                <span class="text-sm font-semibold text-white">{{ $trade->network }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Status:</span>
                <span class="text-sm font-semibold text-white capitalize">{{ $trade->status }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Created At:</span>
                <span class="text-sm font-semibold text-white">{{ $trade->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('buy.payment', ['id' => $trade->id]) }}" class="w-full bg-gradient-to-r from-blue-600 to-teal-500 p-3 rounded-lg font-semibold text-white shadow-md hover:from-blue-700 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 text-center inline-block">
                Proceed to Payment
            </a>
        </div>
    </div>
</div>

<style>
    /* Reuse styles from buy.blade.php for consistency */
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
    padding: 2rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.max-w-md:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.3);
}

.text-2xl {
    font-size: 1.75rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.5rem;
    text-align: center;
}

.text-gray-400.mb-6 {
    font-size: 0.875rem;
    color: #9ca3af;
    text-align: center;
    margin-bottom: 1.5rem;
}

.text-sm.font-medium.text-gray-300 {
    font-size: 0.875rem;
    font-weight: 500;
    color: #d1d5db;
}

.text-sm.font-semibold.text-white {
    font-size: 0.875rem;
    font-weight: 600;
    color: #ffffff;
}

.w-full.bg-gradient-to-r.from-blue-600.to-teal-500 {
    width: 100%;
    padding: 0.75rem;
    border-radius: 0.5rem;
    background-image: linear-gradient(to right, #2563eb, #14b8a6);
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: background-image 0.2s ease, transform 0.2s ease;
}

.w-full.bg-gradient-to-r.from-blue-600.to-teal-500:hover {
    background-image: linear-gradient(to right, #1d4ed8, #0d9488);
    transform: translateY(-2px);
}

.w-full.bg-gradient-to-r.from-blue-600.to-teal-500:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.space-y-4 {
    margin-bottom: 1.5rem;
}

.break-all {
    word-break: break-all;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .max-w-md {
        padding: 1.5rem;
    }

    .text-2xl {
        font-size: 1.5rem;
    }
}
</style>
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