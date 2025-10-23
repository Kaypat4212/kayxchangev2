@extends('selllayout')

@section('content')
    @php
        $amountInUsd = $amountInUsd ?? session('sell.usd_amount', 0);
        $nairaAmount = $nairaAmount ?? session('sell.naira_amount', 0);
        $coin = $coin ?? session('sell.coin', '');
        $walletAddress = $walletAddress ?? '';
        $proofUrl = $proofUrl ?? '';
        $barcodeImages = [
            'BTC' => asset('barcodes/btc-barcode.png'),
            'ETH' => asset('barcodes/eth-barcode.png'),
            'USDT' => asset('barcodes/usdttron-barcode.png'),
        ];
        $barcode = $barcodeImages[$coin] ?? null;
    @endphp

    <style>
        .form-container {
            margin: 2rem auto;
            max-width: 600px;
            padding: 2rem;
            background: linear-gradient(180deg, rgb(122, 0, 0), rgb(0, 90, 18), rgb(0, 164, 0));
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            color: #ffffff;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h3 {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .header p {
            color: #a0a0a0;
            font-size: 0.95rem;
        }

        .amount-info {
            background: rgba(20, 83, 45, 0.55);
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .amount-info .amounts {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .amount-info strong {
            color: #10b981;
            font-size: 1.25rem;
        }

        .wallet-address {
            background: #2d2d2d;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            font-size: 12px;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .copy-button {
            background: #10b981;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .copy-button:hover {
            background: #059669;
        }

        .countdown {
            background: #2d2d2d;
            padding: 0.75rem;
            border-radius: 8px;
            text-align: center;
            color: #f59e0b;
            margin-bottom: 1.5rem;
        }

        .barcode-container {
            text-align: center;
            margin: 1.5rem 0;
            background: black;
            padding: 20px;
            border-radius: 20px;
        }

        .barcode-container img {
            max-width: 300px;
            height: 160px;
            border-radius: 15px;
        }

        .file-upload {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .file-upload label {
            background: #10b981;
            padding: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .file-upload label:hover {
            background: #059669;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 100px;
            border-radius: 8px;
        }

        .remove-file {
            color: #ef4444;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            text-align: center;
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background: #10b981;
            color: #ffffff;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #059669;
        }

        .submit-btn:disabled {
            background: #6b7280;
            cursor: not-allowed;
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
            background: #10b981;
        }

        .toast.warning {
            background: #f59e0b;
        }
    </style>

    <div class="form-container">
        <div class="header">
            <h3>Step 2: Upload Payment Proof</h3>
            <p>Complete the transaction by sending crypto and uploading proof.</p>
        </div>

        <div id="toast" class="toast"></div>

        @if (session('error'))
            <div class="error-message">{{ session('error') }}</div>
        @endif

        <div class="amount-info">
            <p>You're selling:</p>
            <div class="amounts">
                <div>
                    <strong>${{ number_format($amountInUsd, 2) }}</strong>
                    <p>USD</p>
                </div>
                <div class="">
                    <span>-></span>
                </div>
                <div>
                    <strong>₦{{ number_format($nairaAmount, 2) }}</strong>
                    <p>Naira</p>
                </div>
            </div>
            <p>Send <strong>${{ number_format($amountInUsd, 2) }}</strong> worth of <strong>{{ $coin }}</strong> to:
            </p>
        </div>

        <div class="countdown" id="countdown">
            Time remaining: <span id="timer">50:00</span>
        </div>

        <div class="wallet-address container">
           <div class="container">
             <span id="walletAddress">{{ $walletAddress }}</span> 
           </div> 
        </div>
        <div class="container d-flex justify-content-center">
             <button class="copy-button" onclick="copyWalletAddress()"> Copy Wallet Address</button>
        </div>

        @if ($barcode)
            <div class="barcode-container">
                <img src="{{ $barcode }}" alt="{{ $coin }} Barcode">
                <p>Scan to send {{ $coin }}</p>
            </div>
        @endif

        @if ($proofUrl)
            <div class="file-preview">
                <p>Uploaded Proof:</p>
                @if (Str::endsWith($proofUrl, ['.jpg', '.jpeg', '.png']))
                    <img src="{{ $proofUrl }}" alt="Proof Preview">
                @else
                    <a href="{{ $proofUrl }}" target="_blank">View PDF Proof</a>
                @endif
            </div>
        @endif

        <form id="paymentProofForm" action="{{ route('sell.postStep2') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="file-upload">
                <label for="proof">Upload Proof</label>
                <input type="file" name="proof" id="proof" accept=".jpg,.jpeg,.png,.pdf" required>
                <p>Supported formats: JPG, PNG, PDF (max 2MB)</p>
                @error('proof')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                <div class="file-preview" id="filePreview" style="display: none;"></div>
            </div>
            <button type="submit" class="submit-btn" id="submitBtn">Next</button>
        </form>
    </div>

    <script>
        function copyWalletAddress() {
            const walletAddress = document.getElementById('walletAddress').textContent;
            navigator.clipboard.writeText(walletAddress).then(() => {
                showToast('Wallet address copied!', 'success');
            }).catch(error => {
                showToast('Failed to copy address!', 'error');
            });
        }

        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 3000);
        }

        const fileInput = document.getElementById('proof');
        const filePreview = document.getElementById('filePreview');

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                handleFile(fileInput.files[0]);
            } else {
                clearPreview();
            }
        });

        function handleFile(file) {
            const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            const maxSize = 2 * 1024 * 1024;

            if (!validTypes.includes(file.type)) {
                showToast('Invalid file type! Please upload JPG, PNG, or PDF.', 'error');
                fileInput.value = '';
                clearPreview();
                return;
            }

            if (file.size > maxSize) {
                showToast('File too large! Maximum size is 2MB.', 'error');
                fileInput.value = '';
                clearPreview();
                return;
            }

            filePreview.style.display = 'block';
            filePreview.innerHTML = '';

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src文URL.createObjectURL(file);
                filePreview.appendChild(img);
            } else {
                const p = document.createElement('p');
                p.textContent = `Selected file: ${file.name}`;
                filePreview.appendChild(p);
            }

            const removeLink = document.createElement('div');
            removeLink.className = 'remove-file';
            removeLink.textContent = 'Remove file';
            removeLink.onclick = clearPreview;
            filePreview.appendChild(removeLink);
        }

        function clearPreview() {
            fileInput.value = '';
            filePreview.style.display = 'none';
            filePreview.innerHTML = '';
        }

        document.getElementById('paymentProofForm').addEventListener('submit', function(e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                showToast('Please upload a payment proof!', 'error');
            }
        });

        const countdownElement = document.getElementById('timer');
        const submitBtn = document.getElementById('submitBtn');
        let timeLeft = 50 * 60;

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            if (timeLeft <= 0) {
                showToast('Time expired! Please restart the transaction.', 'error');
                clearInterval(timerInterval);
                submitBtn.disabled = true;
            }
            timeLeft--;
        }

        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    </script>
@endsection
