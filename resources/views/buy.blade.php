@extends('buylayout')

@section('content')
<div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-gray-800 rounded-2xl shadow-2xl p-8 transform transition-all hover:shadow-blue-500/20 duration-300">
        <h3 class="text-2xl font-bold text-center text-white mb-2">Buy Crypto</h3>
        <p class="text-center text-gray-400 mb-6">Complete the purchase in two simple steps.</p>

        <form method="POST" action="{{ route('buy.submit') }}" id="buyForm" class="relative overflow-hidden">
            @csrf

            <input type="hidden" name="input_type" id="inputType" value="usd">
            <input type="hidden" name="selected_coin" id="selectedCoinInput" value="">

            <!-- Step 1: Coin and Amount -->
            <div id="step1" class="space-y-6 transition-all duration-500 ease-in-out transform" aria-live="polite">
                <div>
                    <label for="coin" class="block text-sm font-medium text-gray-300 mb-1">
                        Select Coin
                        <span class="relative inline-block group">
                            <svg class="w-4 h-4 text-gray-400 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="absolute z-10 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-2 px-3 -top-10 left-1/2 transform -translate-x-1/2 w-64">
                                Choose the cryptocurrency you want to buy (e.g., Bitcoin, Ethereum, or Tether).
                            </span>
                        </span>
                    </label>
                    <select name="coin" id="coin" class="w-full p-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200" required onchange="updateRate()">
                        <option value="">Choose a coin</option>
                        <option value="BTC">Bitcoin (BTC)</option>
                        <option value="ETH">Ethereum (ETH)</option>
                        <option value="USDT">Tether (USDT)</option>
                    </select>
                    @error('coin')
                        <div class="text-red-400 mt-1 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label id="inputLabel" for="amountInput" class="block text-sm font-medium text-gray-300 mb-1">
                        Amount (USD)
                        <span class="relative inline-block group">
                            <svg class="w-4 h-4 text-gray-400 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="absolute z-10 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-2 px-3 -top-10 left-1/2 transform -translate-x-1/2 w-64">
                                Enter the amount in USD you wish to spend. Minimum is $10.
                            </span>
                        </span>
                    </label>
                    <input type="number" name="amount" id="amountInput" step="0.01" min="10" class="w-full p-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200" required aria-describedby="amountHelp" oninput="calculateConversion()">
                    <small id="amountHelp" class="text-gray-400 text-xs">Minimum amount is $10 USD</small>
                    @error('amount')
                        <div class="text-red-400 mt-1 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label id="convertedLabel" for="convertedAmount" class="block text-sm font-medium text-gray-300 mb-1">
                        You'll Pay (₦)
                        <span class="relative inline-block group">
                            <svg class="w-4 h-4 text-gray-400 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="absolute z-10 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-2 px-3 -top-10 left-1/2 transform -translate-x-1/2 w-64">
                                This shows the equivalent amount in Naira based on the current exchange rate.
                            </span>
                        </span>
                    </label>
                    <input type="text" id="convertedAmount" class="w-full p-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none" readonly>
                    <small class="text-gray-400 text-xs">Rate: <span id="rateValue">-</span></small>
                </div>

                <div class="relative">
                    <button type="button" id="nextButton" class="w-full bg-gradient-to-r from-blue-600 to-teal-500 p-3 rounded-lg font-semibold text-white shadow-md hover:from-blue-700 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 disabled:opacity-50" onclick="goToStep2()">
                        <span id="nextButtonText">Continue</span>
                        <svg id="nextSpinner" class="w-5 h-5 text-white animate-spin absolute right-4 top-1/2 transform -translate-y-1/2 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Step 2: Wallet and Network -->
            <div id="step2" class="space-y-6 absolute top-0 left-0 w-full transition-all duration-500 ease-in-out transform translate-x-full opacity-0" aria-live="polite">
                <div>
                    <p class="text-sm font-medium text-gray-300 mb-4">
                        Selected Coin: <span id="selectedCoinDisplay" class="font-semibold text-white"></span>
                    </p>
                </div>
                <div>
                    <label for="wallet_address" class="block text-sm font-medium text-gray-300 mb-1">
                        Your Wallet Address
                        <span class="relative inline-block group">
                            <svg class="w-4 h-4 text-gray-400 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="absolute z-10 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-2 px-3 -top-10 left-1/2 transform -translate-x-1/2 w-64">
                                Enter the wallet address where you want to receive the crypto. Double-check for accuracy, as incorrect addresses may result in loss of funds.
                            </span>
                        </span>
                    </label>
                    <input type="text" name="wallet_address" id="wallet_address" class="w-full p-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200" required aria-describedby="walletHelp">
                    <small id="walletHelp" class="text-gray-400 text-xs">Ensure this matches the selected coin and network (e.g., BTC address for Bitcoin).</small>
                    @error('wallet_address')
                        <div class="text-red-400 mt-1 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="network" class="block text-sm font-medium text-gray-300 mb-1">
                        Network
                        <span class="relative inline-block group">
                            <svg class="w-4 h-4 text-gray-400 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="absolute z-10 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-2 px-3 -top-10 left-1/2 transform -translate-x-1/2 w-64">
                                Select the blockchain network for the transaction. Ensure your wallet supports the chosen network.
                            </span>
                        </span>
                    </label>
                    <select name="network" id="network" class="w-full p-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200" required>
                        <option value="">Select Network</option>
                    </select>
                    <small class="text-gray-400 text-xs">Choose a network compatible with your wallet and coin.</small>
                    @error('network')
                        <div class="text-red-400 mt-1 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex space-x-4">
                    <button type="button" class="w-1/2 bg-gray-600 p-3 rounded-lg font-semibold text-white shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-200" onclick="goToStep1()">
                        Back
                    </button>
                    <div class="relative w-1/2">
                        <button type="submit" id="submitButton" class="w-full bg-gradient-to-r from-blue-600 to-teal-500 p-3 rounded-lg font-semibold text-white shadow-md hover:from-blue-700 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 disabled:opacity-50">
                            <span id="submitButtonText">Confirm Purchase</span>
                            <svg id="submitSpinner" class="w-5 h-5 text-white animate-spin absolute right-4 top-1/2 transform -translate-y-1/2 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Custom styles for the Buy Crypto form */
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

.relative.overflow-hidden {
    position: relative;
    overflow: hidden;
}

.space-y-6 {
    margin-bottom: 1.5rem;
}

.text-sm.font-medium.text-gray-300 {
    font-size: 0.875rem;
    font-weight: 500;
    color: #d1d5db;
    display: block;
    margin-bottom: 0.25rem;
}

.w-full.p-3.rounded-lg.bg-gray-700 {
    width: 100%;
    padding: 0.75rem;
    border-radius: 0.5rem;
    background-color: #374151;
    border: 1px solid #4b5563;
    color: #ffffff;
    font-size: 0.875rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.w-full.p-3.rounded-lg.bg-gray-700:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.w-full.p-3.rounded-lg.bg-gray-700[readonly] {
    background-color: #4b5563;
    cursor: not-allowed;
}

select.w-full.p-3.rounded-lg.bg-gray-700 {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23d1d5db'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
}

.text-red-400.mt-1.text-sm {
    color: #f87171;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.text-gray-400.text-xs {
    color: #9ca3af;
    font-size: 0.75rem;
    display: block;
    margin-top: 0.25rem;
}

.relative .group {
    position: relative;
    display: inline-block;
}

.w-4.h-4.text-gray-400 {
    width: 1rem;
    height: 1rem;
    color: #9ca3af;
    margin-left: 0.25rem;
    vertical-align: middle;
}

.absolute.z-10.hidden.group-hover\:block {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    background-color: #374151;
    color: #ffffff;
    font-size: 0.75rem;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    width: 16rem;
    top: -2.5rem;
    left: 50%;
    transform: translateX(-50%);
    transition: opacity 0.2s ease, visibility 0.2s ease;
    z-index: 10;
}

.group:hover .absolute.z-10.hidden.group-hover\:block {
    visibility: visible;
    opacity: 1;
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

.w-full.bg-gradient-to-r.from-blue-600.to-teal-500:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.w-1\/2.bg-gray-600 {
    width: 50%;
    padding: 0.75rem;
    border-radius: 0.5rem;
    background-color: #4b5563;
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.w-1\/2.bg-gray-600:hover {
    background-color: #6b7280;
    transform: translateY(-2px);
}

.w-1\/2.bg-gray-600:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(107, 114, 128, 0.5);
}

.w-5.h-5.text-white.animate-spin {
    width: 1.25rem;
    height: 1.25rem;
    color: #ffffff;
}

.transition-all.duration-500.ease-in-out.transform {
    transition: all 0.5s ease-in-out;
}

.translate-x-full.opacity-0 {
    transform: translateX(100%);
    opacity: 0;
}

.hidden {
    display: none;
}

.flex.space-x-4 {
    display: flex;
    gap: 1rem;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .max-w-md {
        padding: 1.5rem;
    }

    .text-2xl {
        font-size: 1.5rem;
    }

    .absolute.z-10.hidden.group-hover\:block {
        width: 12rem;
        font-size: 0.7rem;
        top: -3rem;
    }
}
</style>

<script>
    const amountInput = document.getElementById('amountInput');
    const convertedAmount = document.getElementById('convertedAmount');
    const inputLabel = document.getElementById('inputLabel');
    const convertedLabel = document.getElementById('convertedLabel');
    const coinSelect = document.getElementById('coin');
    const rateValue = document.getElementById('rateValue');
    const nextButton = document.getElementById('nextButton');
    const submitButton = document.getElementById('submitButton');
    const nextButtonText = document.getElementById('nextButtonText');
    const submitButtonText = document.getElementById('submitButtonText');
    const nextSpinner = document.getElementById('nextSpinner');
    const submitSpinner = document.getElementById('submitSpinner');
    const form = document.getElementById('buyForm');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const selectedCoinDisplay = document.getElementById('selectedCoinDisplay');
    const selectedCoinInput = document.getElementById('selectedCoinInput');
    const networkSelect = document.getElementById('network');

    let isUSD = true;
    let selectedCoin = '';

    const rates = {
        BTC: 1600,
        ETH: 1500,
        USDT: 1400
    };

    const networks = {
        BTC: [
            { value: 'Bitcoin', text: 'Bitcoin Network' },
            { value: 'Lightning', text: 'Lightning Network' }
        ],
        ETH: [
            { value: 'ERC20', text: 'ERC20 (Ethereum)' }
        ],
        USDT: [
            { value: 'ERC20', text: 'ERC20 (Ethereum)' },
            { value: 'BEP20', text: 'BEP20 (Binance Smart Chain)' },
            { value: 'TRC20', text: 'TRC20 (Tron)' }
        ]
    };

    function updateRate() {
        const coin = coinSelect.value;
        if (coin && rates[coin]) {
            rateValue.textContent = rates[coin] + ' ₦/USD';
            calculateConversion();
        } else {
            rateValue.textContent = '-';
            convertedAmount.value = '';
        }
    }

    function calculateConversion() {
        const amount = parseFloat(amountInput.value) || 0;
        const coin = coinSelect.value;
        if (!coin || !rates[coin] || amount < 10) {
            convertedAmount.value = '';
            return;
        }

        const rate = rates[coin];
        if (isUSD) {
            const naira = amount * rate;
            convertedAmount.value = '₦' + naira.toLocaleString('en-NG', { minimumFractionDigits: 2 });
        } else {
            const usd = amount / rate;
            convertedAmount.value = '$' + usd.toFixed(2);
        }
    }

    function updateNetworkOptions() {
        networkSelect.innerHTML = '<option value="">Select Network</option>';
        const coin = coinSelect.value;
        if (networks[coin]) {
            networks[coin].forEach(network => {
                const option = document.createElement('option');
                option.value = network.value;
                option.textContent = network.text;
                networkSelect.appendChild(option);
            });
        }
    }

    function goToStep2() {
        if (!coinSelect.value || !amountInput.value || parseFloat(amountInput.value) < 10) {
            toastr.error('Please select a coin and enter a valid amount (minimum $10).');
            return;
        }

        selectedCoin = coinSelect.value;
        selectedCoinInput.value = selectedCoin;
        const coinText = coinSelect.options[coinSelect.selectedIndex].text;
        selectedCoinDisplay.textContent = coinText;

        nextButton.disabled = true;
        nextButtonText.textContent = 'Processing...';
        nextSpinner.classList.remove('hidden');

        updateNetworkOptions();

        setTimeout(() => {
            step1.classList.add('translate-x-full', 'opacity-0');
            step2.classList.remove('translate-x-full', 'opacity-0');
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            nextButton.disabled = false;
            nextButtonText.textContent = 'Continue';
            nextSpinner.classList.add('hidden');
        }, 500);
    }

    function goToStep1() {
        step2.classList.add('translate-x-full', 'opacity-0');
        step1.classList.remove('translate-x-full', 'opacity-0');
        step2.classList.add('hidden');
        step1.classList.remove('hidden');
        selectedCoin = '';
        selectedCoinInput.value = '';
        selectedCoinDisplay.textContent = '';
        networkSelect.innerHTML = '<option value="">Select Network</option>';
    }

    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        if (submitButton.disabled) return;

        // Validate network selection
        if (!networkSelect.value) {
            toastr.error('Please select a network.');
            submitButton.disabled = false;
            submitButtonText.textContent = 'Confirm Purchase';
            submitSpinner.classList.add('hidden');
            return;
        }

        // Validate network compatibility
        const validNetworks = networks[selectedCoin]?.map(n => n.value) || [];
        if (!validNetworks.includes(networkSelect.value)) {
            toastr.error('Selected network is not compatible with the chosen coin.');
            submitButton.disabled = false;
            submitButtonText.textContent = 'Confirm Purchase';
            submitSpinner.classList.add('hidden');
            return;
        }

        submitButton.disabled = true;
        submitButtonText.textContent = 'Processing...';
        submitSpinner.classList.remove('hidden');

        try {
            await new Promise(resolve => setTimeout(resolve, 2000));
            form.submit();
        } catch (error) {
            console.error('Submission error:', error);
            submitButton.disabled = false;
            submitButtonText.textContent = 'Confirm Purchase';
            submitSpinner.classList.add('hidden');
            toastr.error('An error occurred. Please try again.');
        }
    });

    updateRate();
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