@extends('selllayout')

<style>
    .form {
        margin-top: 140px;
        margin-bottom: 140px;
    }
    .bg-darkk {
        background-image: linear-gradient(black, green, darkgreen);
        border-radius: 30px;
        width: 100%;
        max-width: 400px;
    }
    input, select {
        color: black;
    }
    .toggle-currency {
        background-color: #0f5132;
        color: white;
        padding: 5px 10px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 5px;
        display: inline-block;
    }
    .error-message {
        color: red;
        font-size: 12px;
        margin-top: 4px;
    }
    .balance-box {
        background-color: #14532d;
        color: #ffffff;
        padding: 10px 16px;
        border-radius: 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        font-weight: 600;
        font-size: 14px;
        display: inline-block;
    }
</style>

@section('content')

@php
    $rates = [
        'BTC' => 1530,
        'ETH' => 1550,
        'USDT' => 1540,
    ];
    $balance = auth()->user()?->balance ?? 0;
@endphp

<div class="form max-w-md mx-auto mt-6 bg-darkk text-white p-4 rounded-lg shadow">
    <h3 class="text-center text-xl font-semibold mb-2">Sell Crypto</h3>
    <p class="text-center mb-4">Fill in the form to sell your crypto.</p>

    <!-- Styled Balance Display -->
    <div class="flex justify-end mb-4">
        <div class="balance-box">
            💰 Balance: ₦{{ number_format($balance, 2) }}
        </div>
    </div>

    <form method="POST" action="{{ route('sell.postStep1') }}" class="text-center">
        @csrf

        <input type="hidden" name="input_type" id="inputType" value="usd">

        <!-- Select Coin -->
        <div class="mb-4">
            <label class="block text-sm font-medium" for="coin">Select Coin</label><br>
            <select name="coin" id="coin" class="w-full p-2 rounded bg-gray-800 text-black" required onchange="updateRate()">
                <option value="">Choose a coin</option>
                <option value="BTC">Bitcoin (BTC)</option>
                <option value="ETH">Ethereum (ETH)</option>
                <option value="USDT">Tether (USDT)</option>
            </select>
            @error('coin')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Toggle Input Type -->
        <div class="mb-4">
            <span class="toggle-currency" onclick="toggleCurrency()">Switch to <span id="currencyLabel">Naira</span></span>
        </div>

        <!-- Amount Input -->
        <div class="mb-4 text-center">
            <label id="inputLabel" class="block text-sm font-medium">Amount (USD)</label><br>
            <input type="number" name="amount" id="amountInput" step="0.01" class="w-full p-2 rounded bg-gray-800 text-black" required>
            @error('amount')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Converted Amount -->
        <div class="mb-4">
            <label class="block text-sm font-medium" id="convertedLabel">You'll Receive (₦)</label><br>
            <input type="text" id="convertedAmount" class="w-full p-2 rounded bg-gray-900 text-black" readonly>
            <br><small class="text-gray-300">Rate: <span id="rateValue">-</span></small>
        </div>

        <button type="submit" class="w-full bg-green-600 p-3 rounded font-semibold">Continue</button>
    </form>
</div>

<script>
    const amountInput = document.getElementById('amountInput');
    const convertedAmount = document.getElementById('convertedAmount');
    const inputLabel = document.getElementById('inputLabel');
    const convertedLabel = document.getElementById('convertedLabel');
    const currencyLabel = document.getElementById('currencyLabel');
    const inputType = document.getElementById('inputType');
    const coinSelect = document.getElementById('coin');
    const rateValue = document.getElementById('rateValue');

    let isUSD = true;

    const rates = {!! json_encode($rates) !!};

    function updateRate() {
        const coin = coinSelect.value;
        if (coin) {
            rateValue.innerText = rates[coin] + ' ₦/USD';
            calculateConversion();
        }
    }

    function toggleCurrency() {
        isUSD = !isUSD;
        inputType.value = isUSD ? 'usd' : 'naira';
        inputLabel.innerText = isUSD ? 'Amount (USD)' : 'Amount (₦)';
        convertedLabel.innerText = isUSD ? "You'll Receive (₦)" : "You'll Get (USD)";
        currencyLabel.innerText = isUSD ? 'Naira' : 'Dollar';

        amountInput.value = '';
        convertedAmount.value = '';
    }

    amountInput.addEventListener('input', calculateConversion);
    coinSelect.addEventListener('change', calculateConversion);

    function calculateConversion() {
        const amount = parseFloat(amountInput.value) || 0;
        const coin = coinSelect.value;
        if (!coin || !rates[coin]) {
            convertedAmount.value = '';
            return;
        }

        const rate = rates[coin];
        if (isUSD) {
            const naira = amount * rate;
            convertedAmount.value = '₦' + naira.toLocaleString();
        } else {
            const usd = amount / rate;
            convertedAmount.value = '$' + usd.toFixed(2);
        }
    }
</script>

@endsection
