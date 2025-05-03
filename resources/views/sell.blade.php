@extends('selllayout')

@section('content')
<div class="container max-w-2xl mx-auto p-6">
    <form id="sellForm" enctype="multipart/form-data" method="POST" action="{{ route('sell.submit') }}">
        @csrf

        <!-- Step 1 -->
        <div id="step1" class="step">
            <h2 class="text-2xl font-semibold mb-4">Step 1: Enter Amount to Sell</h2>
            <div class="mb-4">
                <label for="coin" class="block mb-1 font-medium">Select Coin</label>
                <select id="coin" name="coin" class="w-full p-2 border rounded">
                    <option value="BTC" data-rate="1350">Bitcoin (BTC)</option>
                    <option value="USDT" data-rate="1200">Tether (USDT)</option>
                    <option value="ETH" data-rate="1300">Ethereum (ETH)</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="amount" class="block mb-1 font-medium">Amount (USD)</label>
                <input type="number" name="amount" id="amount" class="w-full p-2 border rounded" required>
            </div>

            <div class="mb-4 text-sm">
                <p>Rate: <span id="rateDisplay">0</span> NGN</p>
                <p class="font-bold">You will receive: <span id="totalDisplay">0</span> NGN</p>
            </div>

            <button type="button" id="toStep2Btn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Next</button>
        </div>

        <!-- Step 2 -->
        <div id="step2" class="step hidden">
            <h2 class="text-2xl font-semibold mb-4">Step 2: Send Crypto</h2>
            <div class="mb-4">
                <p><strong>Send your <span id="selectedCoin">-</span> to:</strong></p>
                <p id="walletAddress" class="text-lg font-mono">Loading...</p>
                <img id="walletQR" class="w-32 h-32 my-4" src="" alt="Wallet QR">
            </div>

            <div class="mb-4">
                <label for="proofUpload" class="block mb-1 font-medium">Upload Payment Proof</label>
                <input type="file" name="proof" id="proofUpload" class="w-full border p-2 rounded" required>
            </div>

            <button type="button" id="toStep3Btn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Next</button>
        </div>

        <!-- Step 3 -->
        <div id="step3" class="step hidden">
            <h2 class="text-2xl font-semibold mb-4">Step 3: Choose Payout Method</h2>
            <div class="mb-4">
                <label for="payoutMethod" class="block mb-1 font-medium">Select Payout Method</label>
                <select name="paymentMethod" id="payoutMethod" class="w-full p-2 border rounded">
                    <option value="default">My Default Bank</option>
                    <option value="external">External Bank</option>
                    <option value="balance">Add to My Balance</option>
                </select>
            </div>

            <div id="externalBankForm" class="hidden">
                <input type="text" name="alt_bank" placeholder="Bank Name" class="w-full p-2 mb-2 border rounded">
                <input type="text" name="alt_account_number" placeholder="Account Number" class="w-full p-2 mb-2 border rounded">
                <input type="text" name="alt_account_name" placeholder="Account Name" class="w-full p-2 mb-4 border rounded">
            </div>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Submit Trade</button>
        </div>
    </form>

    <div id="loadingSpinner" class="hidden text-center mt-6">
        <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto"></div>
        <p class="mt-2 text-gray-600">Processing...</p>
    </div>
</div>

<script>
    const coinRates = {
        BTC: 1350,
        USDT: 1200,
        ETH: 1300
    };

    const walletMap = {
        BTC: 'bc1qbtcwallet123',
        USDT: 'trxusdtwallet456',
        ETH: '0xethwallet789'
    };

    const qrMap = {
        BTC: '/images/btc_qr.png',
        USDT: '/images/usdt_qr.png',
        ETH: '/images/eth_qr.png'
    };

    const coinSelect = document.getElementById('coin');
    const amountInput = document.getElementById('amount');
    const rateDisplay = document.getElementById('rateDisplay');
    const totalDisplay = document.getElementById('totalDisplay');
    const form = document.getElementById('sellForm');
    const loadingSpinner = document.getElementById('loadingSpinner');

    function updateRate() {
        const coin = coinSelect.value;
        const rate = coinRates[coin] || 0;
        const amount = parseFloat(amountInput.value || 0);
        rateDisplay.textContent = rate;
        totalDisplay.textContent = rate * amount;
    }

    coinSelect.addEventListener('change', updateRate);
    amountInput.addEventListener('input', updateRate);

    document.getElementById('toStep2Btn').addEventListener('click', () => {
        const coin = coinSelect.value;
        document.getElementById('selectedCoin').textContent = coin;
        document.getElementById('walletAddress').textContent = walletMap[coin];
        document.getElementById('walletQR').src = qrMap[coin];

        showStep(2);
    });

    document.getElementById('toStep3Btn').addEventListener('click', () => showStep(3));

    document.getElementById('payoutMethod').addEventListener('change', function () {
        const externalForm = document.getElementById('externalBankForm');
        externalForm.classList.toggle('hidden', this.value !== 'external');
    });

    // ✅ AJAX form submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        loadingSpinner.classList.remove('hidden');

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loadingSpinner.classList.add('hidden');

            if (data.status === 'success') {
                window.location.href = `/trade-summary/${data.trade_id}`;
            } else {
                alert(data.message || 'Something went wrong.');
            }
        })
        .catch(error => {
            loadingSpinner.classList.add('hidden');
            alert('Submission failed. Please try again.');
            console.error(error);
        });
    });

    function showStep(stepNum) {
        document.querySelectorAll('.step').forEach(el => el.classList.add('hidden'));
        document.getElementById(`step${stepNum}`).classList.remove('hidden');
    }
</script>
@endsection
