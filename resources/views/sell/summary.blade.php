@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white shadow rounded-xl mt-10">
    <h2 class="text-2xl font-bold mb-4 text-center">Trade Summary</h2>

    <ul class="text-gray-700 mb-4 space-y-2">
        <li><strong>Coin:</strong> {{ $trade->coin }}</li>
        <li><strong>Amount (USD):</strong> ${{ $trade->amount }}</li>
        <li><strong>Naira Equivalent:</strong> ₦{{ number_format($nairaEquivalent, 2) }}</li>
        <li><strong>Status:</strong> <span class="text-yellow-600">{{ $trade->status }}</span></li>
        <li><strong>Wallet:</strong> {{ $walletMap[$trade->coin] ?? 'N/A' }}</li>
    </ul>

    <div class="mb-4">
        <a href="{{ Storage::url($trade->proof) }}" class="text-blue-600 underline" target="_blank">
            View Uploaded Proof
        </a>
    </div>

    <div class="text-center">
        <a href="{{ route('dashboard') }}" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">
            Return to Dashboard
        </a>
    </div>
</div>
@endsection
