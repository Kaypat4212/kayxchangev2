@extends('layouts.app')

@section('title', 'Deposit Crypto')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Deposit Cryptocurrency</h1>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('wallet.deposit.create') }}" method="POST">
                @csrf

                <!-- Currency Selection -->
                <div class="mb-6">
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Select Cryptocurrency</label>
                    <select name="currency" id="currency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Choose cryptocurrency...</option>
                        @foreach($supportedCryptos as $symbol => $name)
                            <option value="{{ $symbol }}" {{ old('currency') === $symbol ? 'selected' : '' }}>
                                {{ $name }} ({{ $symbol }})
                            </option>
                        @endforeach
                    </select>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <input type="number" name="amount" id="amount" step="0.00000001" min="0.00000001"
                           value="{{ old('amount') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter amount" required>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deposit Info -->
                <div id="deposit-info" class="mb-6 p-4 bg-blue-50 rounded-lg hidden">
                    <h3 class="font-medium text-blue-900 mb-2">Deposit Information</h3>
                    <div id="deposit-details" class="text-sm text-blue-800"></div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-md transition duration-200">
                    Create Deposit Invoice
                </button>
            </form>
        </div>

        <!-- Supported Currencies Info -->
        <div class="mt-8 bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Supported Cryptocurrencies</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($supportedCryptos as $symbol => $name)
                    <div class="text-center">
                        <div class="font-medium text-gray-900">{{ $symbol }}</div>
                        <div class="text-sm text-gray-600">{{ $name }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('currency').addEventListener('change', function() {
    const currency = this.value;
    const infoDiv = document.getElementById('deposit-info');
    const detailsDiv = document.getElementById('deposit-details');

    if (currency) {
        // You can add dynamic info here based on currency
        detailsDiv.innerHTML = `
            <p>You will be redirected to Cryptomus to complete your ${currency} deposit.</p>
            <p>Make sure to send the exact amount to avoid delays.</p>
        `;
        infoDiv.classList.remove('hidden');
    } else {
        infoDiv.classList.add('hidden');
    }
});
</script>
@endsection