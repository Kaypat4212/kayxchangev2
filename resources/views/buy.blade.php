@extends('buylayout')

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
</style>

@section('content')
<div class="form max-w-md mx-auto mt-6 bg-darkk text-white p-4 rounded-lg shadow">
    <h3 class="text-center text-xl font-semibold mb-2">Buy Crypto</h3>
    <p class="text-center mb-4">Fill in the form to purchase crypto.</p>

    <form method="POST" class="text-center" action="{{ route('buy.submit') }}">
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

        <!-- Amount Inputs -->
        <div class="mb-4 text-center">
            <label id="inputLabel" class="block text-sm font-medium">Amount (USD)</label><br>
            <input type="number" name="amount" id="amountInput" step="0.01" class="w-full p-2 rounded bg-gray-800 text-black" required>
            @error('amount')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Converted Amount -->
        <div class="mb-4">
            <label class="block text-sm font-medium" id="convertedLabel">You'll Pay (₦)</label><br>
            <input type="text" id="convertedAmount" class="w-full p-2 rounded bg-gray-900 text-black" readonly>
          <br>  <small class="text-gray-400">Rate: <span id="rateValue">-</span></small>
        </div>

        <!-- Wallet Address -->
        <div class="mb-4">
            <label for="wallet_address" class="block text-sm font-medium">Your Wallet Address</label><br>
            <input type="text" name="wallet_address" id="wallet_address" class="w-full p-2 rounded bg-gray-800 text-black" required>
            @error('wallet_address')
                <div class="error-message">{{ $message }}</div>
            @enderror
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

    const rates = {
        BTC: 1600,
        ETH: 1500,
        USDT: 1400
    };

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
        convertedLabel.innerText = isUSD ? "You'll Pay (₦)" : "You'll Get (USD)";
        currencyLabel.innerText = isUSD ? 'Naira' : 'Dollar';
        
        // Clear the inputs when switching
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
