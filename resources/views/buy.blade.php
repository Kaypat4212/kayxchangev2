@extends('buylayout')

@section('content')
    @php
        $balance = auth()->user()?->balance ?? 0;
    @endphp

    <div class="min-vh-100 d-flex align-items-center justify-content-center p-3 p-md-4">
        <div class="card bg-dark text-light shadow-lg border-0 w-100 animate-slide-up" style="max-width: 450px;">
            <div class="card-body p-4 p-md-5">
                <!-- Toast notification container -->
                <div id="toast" class="toast"></div>

                <!-- Guidelines section -->
                <div class="guidelines animate-fade mb-4">
                    <h4 class="fw-bold text-white text-center">How to Buy Crypto</h4>
                    <ul>
                        <li>Select the cryptocurrency (BTC, ETH, or USDT-TRC20).</li>
                        <li>Enter the amount in USD or Naira (minimum $10 or ₦14,000).</li>
                        <li>Review the exchange rate and converted amount.</li>
                        <li>Enter your wallet address and confirm the network.</li>
                        <li>Confirm the purchase after verifying all details.</li>
                    </ul>
                </div>

                <!-- Heading -->
                <h3 class="h2 fw-bold text-center mb-3 text-success">Buy Crypto</h3>
                <p class="text-center text-white mb-4">Complete the purchase in two simple steps.</p>

                <form method="POST" action="{{ route('buy.submit') }}" id="buyForm">
                    @csrf

                    <input type="hidden" name="input_type" id="inputType" value="usd">
                    <input type="hidden" name="selected_coin" id="selectedCoinInput" value="">

                    <!-- Step 1: Coin and Amount -->
                    <div id="step1" class="fade-in">
                        <div class="mb-4">
                            <label for="coin" class="form-label fw-semibold">
                                Select Coin
                                <span class="position-relative d-inline-block" data-bs-toggle="tooltip"
                                    data-bs-title="Choose Bitcoin, Ethereum, or Tether (TRC20).">
                                    <i class="bi bi-info-circle text-muted ms-1"></i>
                                </span>
                            </label>
                            <select name="coin" id="coin" class="form-select bg-dark text-light border-secondary"
                                required onchange="updateRate()">
                                <option value="">Choose a coin</option>
                                <option value="BTC">Bitcoin (BTC)</option>
                                <option value="ETH">Ethereum (ETH)</option>
                                <option value="USDT">Tether (USDT-TRC20)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label id="inputLabel" for="amountInput" class="form-label fw-semibold">
                                Amount (USD)
                                <span class="position-relative d-inline-block" data-bs-toggle="tooltip"
                                    data-bs-title="Enter amount in USD or Naira (minimum $10 or ₦14,000).">
                                    <i class="bi bi-info-circle text-muted ms-1"></i>
                                </span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="amount" id="amountInput" step="0.01" min="10"
                                    class="form-control bg-dark text-light border-secondary" required
                                    aria-describedby="amountHelp">
                                <button type="button" id="toggleCurrency" class="btn btn-success animate-pulse"
                                    aria-label="Toggle Currency">
                                    <i class="bi bi-currency-exchange"></i>
                                </button>
                            </div>
                            <small id="amountHelp" class="form-text text-white">Minimum amount is $10 USD or ₦14,000</small>
                        </div>

                        <div class="mb-4">
                            <label id="convertedLabel" for="convertedAmount" class="form-label fw-semibold">
                                You'll Pay (₦)
                                <span class="position-relative d-inline-block" data-bs-toggle="tooltip"
                                    data-bs-title="Equivalent amount based on current exchange rate.">
                                    <i class="bi bi-info-circle text-muted ms-1"></i>
                                </span>
                            </label>
                            <input type="text" id="convertedAmount"
                                class="form-control bg-dark text-light border-secondary" readonly
                                aria-describedby="convertedHelp">
                            <small id="convertedHelp" class="form-text text-white">Converted amount based on the selected
                                coin's rate.</small>
                        </div>

                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-success animate-pulse" disabled
                                aria-label="Exchange Rate">
                                Rate: <span id="rateValue">-</span>
                            </button>
                        </div>

                        <button type="button" id="nextButton" class="btn btn-success w-100 animate-pulse"
                            aria-label="Continue to Step 2">
                            <span id="nextButtonText">Continue</span>
                            <i id="nextSpinner" class="bi bi-arrow-repeat ms-2 d-none"></i>
                        </button>
                    </div>

                    <!-- Step 2: Wallet and Network -->
                    <div id="step2" class="d-none fade-in">
                        <div class="mb-4">
                            <p class="fw-semibold">
                                Selected Coin: <span id="selectedCoinDisplay" class="fw-bold text-success"></span>
                            </p>
                        </div>
                        <div class="mb-4">
                            <label for="wallet_address" class="form-label fw-semibold">
                                Your Wallet Address
                                <span class="position-relative d-inline-block" data-bs-toggle="tooltip"
                                    data-bs-title="Enter the wallet address for the selected coin and network.">
                                    <i class="bi bi-info-circle text-white ms-1"></i>
                                </span>
                            </label>
                            <input type="text" name="wallet_address" id="wallet_address"
                                class="form-control bg-dark text-light border-secondary @error('wallet_address') border-danger @enderror"
                                required aria-describedby="walletHelp">
                            <small id="walletHelp" class="form-text text-white">Ensure this matches the selected coin and
                                network.</small>
                        </div>

                        <div class="mb-4">
                            <label for="network" class="form-label fw-semibold">
                                Network
                                <span class="position-relative d-inline-block" data-bs-toggle="tooltip"
                                    data-bs-title="Select the blockchain network for the transaction.">
                                    <i class="bi bi-info-circle text-muted ms-1"></i>
                                </span>
                            </label>
                            <select name="network" id="network"
                                class="form-select bg-dark text-light border-secondary @error('network') border-danger @enderror"
                                required aria-describedby="networkHelp">
                                <option value="">Select Network</option>
                            </select>
                            <small id="networkHelp" class="form-text text-white">Choose a network compatible with your
                                wallet and coin.</small>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-3">
                            <button type="button" class="btn btn-secondary flex-fill animate-pulse"
                                onclick="goToStep1()" aria-label="Back to Step 1">
                                Back
                            </button>
                            <button type="submit" id="submitButton" class="btn btn-success flex-fill animate-pulse"
                                aria-label="Confirm Purchase">
                                <span id="submitButtonText">Confirm Purchase</span>
                                <i id="submitSpinner" class="bi bi-arrow-repeat ms-2 d-none"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-green: #22c55e;
            --dark-bg: #1a1a1a;
            --card-bg: #2c2c2c;
            --text-muted: #a0a0a0;
            --glow-color: rgba(34, 197, 94, 0.5);
        }

        .card {
            background: linear-gradient(135deg, var(--dark-bg), #2a3a2a) !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 16px !important;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px var(--glow-color) !important;
        }

        .form-control,
        .form-select {
            background-color: #2d2d2d !important;
            border-color: #3a3a3a !important;
            color: #ffffff !important;
            border-radius: 8px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-green) !important;
            box-shadow: 0 0 8px var(--glow-color) !important;
        }

        .form-control[readonly] {
            background-color: #3a3a3a !important;
            opacity: 1;
        }

        .btn-success {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
            border-radius: 8px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .btn-success:hover {
            background-color: #16a34a;
            border-color: #16a34a;
            transform: scale(1.05);
        }

        .btn-secondary {
            background-color: #3a3a3a;
            border-color: #3a3a3a;
            border-radius: 8px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #4a4a4a;
            border-color: #4a4a4a;
            transform: scale(1.05);
        }

        .btn:disabled {
            opacity: 0.65;
        }

        .guidelines {
            background: #14532d;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px var(--glow-color);
        }

        .guidelines h4 {
            font-size: 1.25rem;
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

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        .animate-slide-up {
            animation: slideUp 0.8s ease-in-out;
        }

        .animate-pulse {
            animation: glowPulse 2s infinite;
        }

        .bi-arrow-repeat {
            animation: spin 1s linear infinite;
        }

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

        .toast.success {
            background: #22c55e;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes glowPulse {
            0% {
                box-shadow: 0 0 5px var(--glow-color);
            }

            50% {
                box-shadow: 0 0 20px var(--glow-color);
            }

            100% {
                box-shadow: 0 0 5px var(--glow-color);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 1.5rem !important;
            }

            .h2 {
                font-size: 1.75rem;
            }

            .form-label {
                font-size: 0.9rem;
            }
        }
    </style>

    <script>
        // DOM elements
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
        const toggleCurrency = document.getElementById('toggleCurrency');
        const inputType = document.getElementById('inputType');
        const walletAddressInput = document.getElementById('wallet_address');

        // Initialize state
        let isUSD = true;
        let selectedCoin = '';
        const rates = {!! json_encode($rates ?? ['BTC' => 1600, 'ETH' => 1500, 'USDT' => 1400]) !!};

        // Network configurations
        const networks = {
            BTC: [{ value: 'Bitcoin', text: 'Bitcoin Network' }],
            ETH: [{ value: 'Ethereum', text: 'Ethereum Network' }],
            USDT: [{ value: 'Tron', text: 'Tron Network' }]
        };

        // Fallback regex patterns (matching BuyCryptoRequest.php)
        const fallbackPatterns = {
            BTC: {
                Bitcoin: /^(1|3|bc1)[A-Za-z0-9]{25,74}$/
            },
            ETH: {
                Ethereum: /^0x[a-fA-F0-9]{40}$/
            },
            USDT: {
                Tron: /^T[A-Za-z0-9]{33}$/
            }
        };

        // Log rates for debugging
        console.log('Rates:', rates);

        // Show toast notification (user-facing errors only)
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type} animate-fade`;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        // Log errors to Laravel
        async function logError(message, details) {
            try {
                await fetch('/log-error', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message, details })
                });
                console.log('Error logged:', message, details);
            } catch (e) {
                console.error('Failed to log error:', e);
            }
        }

        // Validate wallet address
        async function validateWalletAddress(address, coin, network) {
            if (!address) {
                return { valid: false, message: 'Wallet address is required.' };
            }

            address = address.trim();
            const librariesAvailable = {
                bitcoin: typeof bitcoinAddressValidation !== 'undefined',
                ethers: typeof ethers !== 'undefined',
                tronweb: typeof TronWeb !== 'undefined'
            };

            try {
                if (coin === 'BTC' && network === 'Bitcoin') {
                    if (librariesAvailable.bitcoin) {
                        const validation = bitcoinAddressValidation(address);
                        if (!validation.valid) {
                            throw new Error('Invalid Bitcoin wallet address.');
                        }
                        return { valid: true };
                    }
                    throw new Error('bitcoin-address-validation not loaded');
                } else if (coin === 'ETH' && network === 'Ethereum') {
                    if (librariesAvailable.ethers) {
                        if (!ethers.utils.isAddress(address)) {
                            throw new Error('Invalid Ethereum wallet address.');
                        }
                        return { valid: true };
                    }
                    throw new Error('ethers.js not loaded');
                } else if (coin === 'USDT' && network === 'Tron') {
                    if (librariesAvailable.tronweb) {
                        const tronWeb = new TronWeb({ fullHost: 'https://api.trongrid.io' });
                        const isValid = await tronWeb.isAddress(address);
                        if (!isValid) {
                            throw new Error('Invalid Tron (TRC20) wallet address.');
                        }
                        return { valid: true };
                    }
                    throw new Error('tronweb not loaded');
                } else {
                    throw new Error(`Unsupported coin or network: ${coin}/${network}.`);
                }
            } catch (error) {
                // Log error silently
                logError('Wallet validation error', {
                    coin,
                    network,
                    address,
                    error: error.message,
                    stack: error.stack,
                    libraries: librariesAvailable
                });

                // Fallback to regex
                if (fallbackPatterns[coin]?.[network]?.test(address)) {
                    logError('Using regex fallback for wallet validation', { coin, network, address });
                    return { valid: true };
                }

                return { valid: false, message: 'Invalid wallet address.' };
            }
        }

        // Check library loading
        async function checkLibraries() {
            const librariesAvailable = {
                bitcoin: typeof bitcoinAddressValidation !== 'undefined',
                ethers: typeof ethers !== 'undefined',
                tronweb: typeof TronWeb !== 'undefined'
            };
            if (!librariesAvailable.bitcoin || !librariesAvailable.ethers || !librariesAvailable.tronweb) {
                logError('Validation libraries failed to load', { libraries: librariesAvailable });
            }
        }

        // Update rate display
        function updateRate() {
            const coin = coinSelect.value;
            if (coin && rates[coin]) {
                rateValue.textContent = '₦' + parseFloat(rates[coin]).toLocaleString('en-NG') + '/USD';
                calculateConversion();
            } else {
                rateValue.textContent = '-';
                convertedAmount.value = '';
                if (coin) {
                    showToast('Rate unavailable for ' + coin + '. Please try again later.', 'error');
                }
            }
        }

        // Calculate USD/NGN conversion
        function calculateConversion() {
            const amount = parseFloat(amountInput.value) || 0;
            const coin = coinSelect.value;
            if (!coin || !rates[coin]) {
                convertedAmount.value = '';
                return;
            }

            const rate = rates[coin];
            if (isUSD) {
                if (amount < 10) {
                    convertedAmount.value = '';
                    return;
                }
                const naira = (amount * rate).toFixed(2);
                convertedAmount.value = '₦' + parseFloat(naira).toLocaleString('en-NG');
            } else {
                if (amount < 14000) {
                    convertedAmount.value = '';
                    return;
                }
                const usd = (amount / rate).toFixed(2);
                convertedAmount.value = '$' + parseFloat(usd).toLocaleString('en-US');
            }
        }

        // Toggle currency mode
        function toggleCurrencyMode() {
            const amount = parseFloat(amountInput.value) || 0;
            const coin = coinSelect.value;
            if (!coin) {
                showToast('Please select a coin first!', 'error');
                return;
            }
            if (!rates[coin]) {
                showToast('Rate unavailable for ' + coin + '. Please try again later.', 'error');
                return;
            }

            const rate = rates[coin];
            isUSD = !isUSD;
            inputType.value = isUSD ? 'usd' : 'naira';

            if (isUSD) {
                inputLabel.textContent = 'Amount (USD)';
                convertedLabel.textContent = "You'll Pay (₦)";
                amountInput.step = '0.01';
                amountInput.min = '10';
                amountInput.placeholder = 'Enter amount in USD';
                if (amount >= 14000) {
                    amountInput.value = (amount / rate).toFixed(2);
                } else {
                    amountInput.value = '';
                }
            } else {
                inputLabel.textContent = 'Amount (₦)';
                convertedLabel.textContent = "You'll Receive (USD)";
                amountInput.step = '1';
                amountInput.min = '14000';
                amountInput.placeholder = 'Enter amount in Naira';
                if (amount >= 10) {
                    amountInput.value = (amount * rate).toFixed(2);
                } else {
                    amountInput.value = '';
                }
            }

            calculateConversion();
        }

        // Update network options
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

        // Proceed to Step 2
        function goToStep2() {
            const amount = parseFloat(amountInput.value) || 0;
            const coin = coinSelect.value;
            if (!coin) {
                showToast('Please select a coin!', 'error');
                coinSelect.classList.add('border-danger');
                return;
            }
            if (!rates[coin]) {
                showToast('Rate unavailable for ' + coin + '. Please try again later.', 'error');
                coinSelect.classList.add('border-danger');
                return;
            }
            if (!amount || (isUSD && amount < 10) || (!isUSD && amount < 14000)) {
                showToast('Please enter a valid amount (minimum $10 or ₦14,000).', 'error');
                amountInput.classList.add('border-danger');
                return;
            }

            coinSelect.classList.remove('border-danger');
            amountInput.classList.remove('border-danger');

            selectedCoin = coin;
            selectedCoinInput.value = selectedCoin;
            const coinText = coinSelect.options[coinSelect.selectedIndex].text;
            selectedCoinDisplay.textContent = coinText;

            nextButton.disabled = true;
            nextButtonText.textContent = 'Processing...';
            nextSpinner.classList.remove('d-none');

            updateNetworkOptions();

            setTimeout(() => {
                step1.classList.add('d-none');
                step2.classList.remove('d-none');
                nextButton.disabled = false;
                nextButtonText.textContent = 'Continue';
                nextSpinner.classList.add('d-none');
            }, 500);
        }

        // Return to Step 1
        function goToStep1() {
            step2.classList.add('d-none');
            step1.classList.remove('d-none');
            selectedCoin = '';
            selectedCoinInput.value = '';
            selectedCoinDisplay.textContent = '';
            networkSelect.innerHTML = '<option value="">Select Network</option>';
        }

        // Form submission
        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            if (submitButton.disabled) return;

            const walletAddress = walletAddressInput.value.trim();
            const network = networkSelect.value;
            const validNetworks = networks[selectedCoin]?.map(n => n.value) || [];

            // Validate inputs
            if (!network) {
                showToast('Please select a network!', 'error');
                networkSelect.classList.add('border-danger');
                return;
            }
            if (!validNetworks.includes(network)) {
                showToast('Selected network is not compatible with the chosen coin.', 'error');
                networkSelect.classList.add('border-danger');
                return;
            }
            if (!walletAddress) {
                showToast('Please enter a wallet address!', 'error');
                walletAddressInput.classList.add('border-danger');
                return;
            }

            // Client-side validation (logged, not shown)
            const walletValidation = await validateWalletAddress(walletAddress, selectedCoin, network);
            if (!walletValidation.valid) {
                logError('Client-side wallet validation failed', {
                    coin: selectedCoin,
                    network,
                    address: walletAddress,
                    message: walletValidation.message
                });
                // Allow submission; server-side will handle
            }

            walletAddressInput.classList.remove('border-danger');
            networkSelect.classList.remove('border-danger');

            submitButton.disabled = true;
            submitButtonText.textContent = 'Processing...';
            submitSpinner.classList.remove('d-none');

            try {
                await new Promise(resolve => setTimeout(resolve, 1000));
                form.submit();
            } catch (error) {
                logError('Form submission error', {
                    error: error.message,
                    stack: error.stack
                });
                showToast('An error occurred during submission. Please try again.', 'error');
                submitButton.disabled = false;
                submitButtonText.textContent = 'Confirm Purchase';
                submitSpinner.classList.add('d-none');
            }
        });

        // Real-time wallet validation (logged, not shown)
        walletAddressInput.addEventListener('input', async function() {
            const walletAddress = this.value.trim();
            const network = networkSelect.value;
            if (walletAddress && selectedCoin && network) {
                const validation = await validateWalletAddress(walletAddress, selectedCoin, network);
                if (!validation.valid) {
                    logError('Real-time wallet validation failed', {
                        coin: selectedCoin,
                        network,
                        address: walletAddress,
                        message: validation.message
                    });
                    this.classList.add('border-danger');
                } else {
                    this.classList.remove('border-danger');
                }
            } else {
                this.classList.remove('border-danger');
            }
        });

        // Event listeners
        nextButton.addEventListener('click', goToStep2);
        toggleCurrency.addEventListener('click', toggleCurrencyMode);
        amountInput.addEventListener('input', () => {
            if (amountInput.value < 0) {
                amountInput.value = '';
                showToast('Amount must be positive!', 'error');
            }
            calculateConversion();
        });
        coinSelect.addEventListener('change', updateRate);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            checkLibraries();

            // Display session messages
            @if (session('success'))
                showToast('{!! e(session('success')) !!}', 'success');
            @elseif (session('error'))
                showToast('{!! e(session('error')) !!}', 'error');
            @endif

            // Display Laravel validation errors
            const errors = @json($errors->all());
            if (errors.length > 0) {
                errors.forEach(error => showToast(error, 'error'));
            }

            // Initialize rates
            if (!Object.keys(rates).length) {
                showToast('No rates available. Please try again later.', 'error');
            } else if (coinSelect.value) {
                updateRate();
            }

            // Initialize Bootstrap tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });
    </script>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bitcoin-address-validation@2.2.3/dist/index.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/ethers@5.7.2/dist/ethers.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tronweb@5.3.2/dist/TronWeb.min.js"></script>
@endsection
