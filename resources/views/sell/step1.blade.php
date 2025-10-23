@extends('selllayout')

@section('content')
@php
    $balance = auth()->user()?->balance ?? 0;
    $selectedCoin = session('sell.coin', '');
    $inputType = session('sell.input_type', 'usd');
    $amount = $inputType === 'usd' ? session('sell.usd_amount', '') : session('sell.naira_amount', '');
@endphp

<style>
    /* Import Poppins font for consistency with dashboard */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    /* Root variables for theming */
    :root {
        --primary-green: #10b981;
        --dark-bg: #1a1a1a;
        --card-bg: #2c2c2c;
        --text-muted: #a0a0a0;
        --glow-color: rgba(16, 185, 129, 0.5);
    }

    /* Form container with glowing effect */
    .form-container {
        margin: 2rem auto;
        max-width: 600px;
        padding: 2rem;
        background: linear-gradient(135deg, var(--dark-bg), #2a3a2a);
        border-radius: 16px;
        box-shadow: 0 8px 32px var(--glow-color);
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px var(--glow-color);
    }

    /* Header styling */
    .header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .header h3 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary-green);
    }

    .header p {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    /* Guidelines styling */
    .guidelines {
        background: #14532d;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px var(--glow-color);
    }

    .guidelines h4 {
        font-size: 1.25rem;
        color: var(--primary-green);
        margin-bottom: 0.75rem;
    }

    .guidelines ul {
        list-style: none;
        padding: 0;
        color: #d1d5db;
        font-size: 0.9rem;
    }

    .guidelines li {
        margin-bottom: 0.5rem;
        display: flex;
        align-items: flex-start;
    }

    .guidelines li::before {
        content: '✔';
        color: var(--primary-green);
        margin-right: 0.5rem;
    }

    /* Balance box with glow */
    .balance-box {
        background: #14532d;
        padding: 0.75rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        text-align: right;
        box-shadow: 0 2px 10px var(--glow-color);
    }

    /* Form group styling */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        font-size: 0.9rem;
        color: #d1d5db;
        margin-bottom: 0.5rem;
        display: block;
    }

    /* Input and select styling */
    select, input[type="number"], input[type="text"] {
        width: 100%;
        padding: 0.75rem;
        border-radius: 8px;
        background: #2d2d2d;
        color: #ffffff;
        border: 1px solid #3a3a3a;
        font-size: 0.95rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    select:focus, input:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow: 0 0 8px var(--glow-color);
    }

    /* Toggle currency button */
    .toggle-currency {
        background: var(--primary-green);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 0.5rem;
        text-align: center;
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .toggle-currency:hover {
        background: #059669;
        transform: scale(1.05);
    }

    /* Converted amounts display */
    .converted-amounts {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .converted-amounts strong {
        color: var(--primary-green);
        font-size: 1.25rem;
    }

    /* Rate info with animation */
    .rate-info {
        text-align: center;
        margin-top: 0.5rem;
        color: var(--text-muted);
    }

    .rate-info span {
        color: var(--primary-green);
        transition: opacity 0.3s ease;
    }

    /* Submit button with glow */
    .submit-btn {
        width: 100%;
        padding: 0.75rem;
        background: var(--primary-green);
        color: #ffffff;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 10px var(--glow-color);
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .submit-btn:hover {
        background: #059669;
        transform: scale(1.05);
    }

    /* Toast notification */
    .toast {
        position: fixed;
        top: 1rem;
        right: 1rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        color: #ffffff;
        font-size: 0.9rem;
        z-index: 1000;
        display: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .toast.error {
        background: #ef4444;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes glowPulse {
        0% { box-shadow: 0 0 5px var(--glow-color); }
        50% { box-shadow: 0 0 20px var(--glow-color); }
        100% { box-shadow: 0 0 5px var(--glow-color); }
    }

    .animate-fade {
        animation: fadeIn 0.5s ease-in-out;
    }

    .animate-slide-up {
        animation: slideUp 0.8s ease-in-out;
    }

    .animate-pulse {
        animation: glowPulse 2s infinite;
    }
</style>

<!-- Form container with glowing effect -->
<div class="form-container animate-slide-up">
    <!-- Header section -->
    <div class="header">
        <h3>Sell Crypto</h3>
        <p>Quickly and securely sell your cryptocurrency.</p>
    </div>

    <!-- Toast notification -->
    <div id="toast" class="toast"></div>

    <!-- Guidelines section -->
    <div class="guidelines animate-fade">
        <h4>How to Sell Crypto</h4>
        <ul>
            <li>Select the cryptocurrency you want to sell from the dropdown menu.</li>
            <li>Choose your input currency (USD or Naira) and enter the amount.</li>
            <li>Review the exchange rate and converted amount displayed below.</li>
            <li>Click "Continue" to proceed with the transaction.</li>
            <li>Ensure your balance is sufficient and double-check all details before submitting.</li>
        </ul>
    </div>

    <!-- Balance display -->
    <div class="balance-box animate-pulse">
        <span>💰 Balance:</span> ₦{{ number_format($balance, 2) }}
    </div>

    <!-- Session error message -->
    @if (session('error'))
        <div class="error-message animate-fade" style="display: none;" data-error="{{ session('error') }}"></div>
    @endif

    <!-- Sell form -->
    <form method="POST" action="{{ route('sell.postStep1') }}" id="sellForm">
        @csrf

        <!-- Hidden inputs for form state -->
        <input type="hidden" name="input_type" id="inputType" value="{{ $inputType }}">
        <input type="hidden" name="usd_amount" id="usdAmount" value="{{ session('sell.usd_amount', '') }}">
        <input type="hidden" name="naira_amount" id="nairaAmount" value="{{ session('sell.naira_amount', '') }}">

        <!-- Coin selection -->
        <div class="form-group">
            <label for="coin">Select Coin</label>
            <select name="coin" id="coin" required onchange="updateRate()">
                <option value="">Choose a coin</option>
                <option value="BTC" {{ $selectedCoin === 'BTC' ? 'selected' : '' }}>Bitcoin (BTC)</option>
                <option value="ETH" {{ $selectedCoin === 'ETH' ? 'selected' : '' }}>Ethereum (ETH)</option>
                <option value="USDT" {{ $selectedCoin === 'USDT' ? 'selected' : '' }}>USDT (Tron)</option>
            </select>
            <!-- Rate display -->
            <div class="rate-info">
                Rate: <span id="rateValue" class="animate-fade">-</span>
            </div>
        </div>

        <!-- Amount input -->
        <div class="form-group">
            <label id="inputLabel">Amount ({{ $inputType === 'usd' ? 'USD' : '₦' }})</label>
            <input type="number" name="amount" id="amountInput" step="0.01" min="0.01" value="{{ $amount }}" required>
            <!-- Toggle currency button -->
            <div class="toggle-currency animate-pulse" onclick="switchCurrency()">
                <span id="toggleText">Switch to {{ $inputType === 'usd' ? 'Naira' : 'USD' }}</span>
            </div>
        </div>

        <!-- Conversion display -->
        <div class="form-group">
            <label id="convertedLabel">You'll {{ $inputType === 'usd' ? 'Receive (₦)' : 'Send (USD)' }}</label>
            <input type="text" id="convertedAmount" readonly>
            <div class="converted-amounts">
                <div>
                    <strong id="usdDisplay">-</strong>
                    <p>USD</p>
                </div>
                <div class="">
                    <span>-></span>
                </div>
                <div>
                    <strong id="nairaDisplay">-</strong>
                    <p>Naira</p>
                </div>
            </div>
        </div>

        <!-- Submit button -->
        <button type="submit" class="submit-btn animate-pulse">Continue</button>
    </form>
</div>

<script>
// DOM elements
const amountInput = document.getElementById('amountInput');
const convertedAmount = document.getElementById('convertedAmount');
const inputLabel = document.getElementById('inputLabel');
const convertedLabel = document.getElementById('convertedLabel');
const inputType = document.getElementById('inputType');
const coinSelect = document.getElementById('coin');
const rateValue = document.getElementById('rateValue');
const sellForm = document.getElementById('sellForm');
const toggleText = document.getElementById('toggleText');
const usdAmountInput = document.getElementById('usdAmount');
const nairaAmountInput = document.getElementById('nairaAmount');
const usdDisplay = document.getElementById('usdDisplay');
const nairaDisplay = document.getElementById('nairaDisplay');

// Initialize currency mode
let isUSD = {{ $inputType === 'usd' ? 'true' : 'false' }};
const rates = {!! json_encode($rates) !!} || {};

// Log rates for debugging
console.log('Rates:', rates);

// Update rate display
function updateRate() {
    const coin = coinSelect.value;
    if (coin && rates[coin]) {
        rateValue.innerText = '₦' + parseFloat(rates[coin]).toLocaleString('en-NG') + '/USD';
        calculateConversion();
    } else {
        resetConversion();
        if (coin) {
            showToast('Rate unavailable for ' + coin + '. Please try again later.', 'error');
        } else {
            rateValue.innerText = '-';
        }
    }
}

// Switch between USD and Naira
function switchCurrency() {
    const coin = coinSelect.value;
    if (!coin) {
        showToast('Please select a coin first!', 'error');
        return;
    }
    if (!rates[coin]) {
        showToast('Rate unavailable for ' + coin + '. Please try again later.', 'error');
        return;
    }

    const currentAmount = parseFloat(amountInput.value) || 0;
    const rate = rates[coin];

    isUSD = !isUSD;
    inputType.value = isUSD ? 'usd' : 'naira';
    inputLabel.innerText = `Amount (${isUSD ? 'USD' : '₦'})`;
    convertedLabel.innerText = isUSD ? "You'll Receive (₦)" : "You'll Send (USD)";
    toggleText.innerText = isUSD ? 'Switch to Naira' : 'Switch to USD';

    if (currentAmount > 0) {
        amountInput.value = isUSD
            ? (currentAmount / rate).toFixed(2)
            : (currentAmount * rate).toFixed(2);
    } else {
        amountInput.value = '';
    }

    calculateConversion();
}

// Calculate USD/NGN conversion
function calculateConversion() {
    const amount = parseFloat(amountInput.value) || 0;
    const coin = coinSelect.value;
    if (!coin || !rates[coin]) {
        resetConversion();
        return;
    }
    if (amount <= 0) {
        resetConversion();
        return;
    }

    const rate = rates[coin];
    if (isUSD) {
        const naira = (amount * rate).toFixed(2);
        convertedAmount.value = '₦' + parseFloat(naira).toLocaleString('en-NG');
        usdAmountInput.value = amount.toFixed(2);
        nairaAmountInput.value = naira;
        usdDisplay.innerText = '$' + amount.toLocaleString('en-US');
        nairaDisplay.innerText = '₦' + parseFloat(naira).toLocaleString('en-NG');
    } else {
        const usd = (amount / rate).toFixed(2);
        convertedAmount.value = '$' + parseFloat(usd).toLocaleString('en-US');
        usdAmountInput.value = usd;
        nairaAmountInput.value = amount.toFixed(2);
        usdDisplay.innerText = '$' + parseFloat(usd).toLocaleString('en-US');
        nairaDisplay.innerText = '₦' + amount.toLocaleString('en-NG');
    }
}

// Reset conversion display
function resetConversion() {
    convertedAmount.value = '';
    usdAmountInput.value = '';
    nairaAmountInput.value = '';
    usdDisplay.innerText = '-';
    nairaDisplay.innerText = '-';
}

// Show toast notification
function showToast(message, type) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type} animate-fade`;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Display session errors as toasts
    const sessionError = document.querySelector('.error-message[data-error]');
    if (sessionError) {
        showToast(sessionError.dataset.error, 'error');
    }

    // Display Laravel validation errors as toasts
    const errors = @json($errors->all());
    if (errors.length > 0) {
        errors.forEach(error => showToast(error, 'error'));
    }

    // Check rates and initialize rate display
    if (!Object.keys(rates).length) {
        showToast('No rates available. Please try again later.', 'error');
    } else if (coinSelect.value) {
        updateRate();
        if (amountInput.value) {
            calculateConversion();
        }
    }
});

// Handle coin selection
coinSelect.addEventListener('change', () => {
    updateRate();
});

// Handle amount input
amountInput.addEventListener('input', () => {
    if (amountInput.value < 0) {
        amountInput.value = '';
        showToast('Amount must be positive!', 'error');
    }
    calculateConversion();
});

// Validate form on submit
sellForm.addEventListener('submit', function(e) {
    const amount = parseFloat(amountInput.value);
    const coin = coinSelect.value;
    if (!coin) {
        e.preventDefault();
        showToast('Please select a coin!', 'error');
    } else if (!rates[coin]) {
        e.preventDefault();
        showToast('Rate unavailable for ' + coin + '. Please try again later.', 'error');
    } else if (!amount || amount <= 0) {
        e.preventDefault();
        showToast('Please enter a valid amount!', 'error');
    } else if (!usdAmountInput.value || !nairaAmountInput.value) {
        e.preventDefault();
        showToast('Conversion error. Please try again.', 'error');
    }
});
</script>
@endsection