@extends('selllayout')

@section('content')

@php
$barcodeImages = [
    'BTC' => asset('barcodes/btc-barcode.png'),
    'ETH' => asset('barcodes/eth-barcode.png'),
    'USDT' => asset('barcodes/usdt-barcode.png'),
];
$barcode = $barcodeImages[$coin] ?? null;
@endphp

<style>
    .bg-darkk {
        border: black solid black;
        border-radius: 30px;
        background-image: linear-gradient(green, white, green);
    }

    .copybutton {
        cursor: pointer;
        margin-left: 10px;
        color: #0f805e;
    }

    .copybutton svg {
        width: 70px;
        height: 40px;
        fill: #0f805e;
    }

    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #f44336;
        color: white;
        padding: 10px;
        border-radius: 5px;
        display: none;
        z-index: 1000;
    }

    .error-message {
        color: red;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .custum-file-upload {
        height: 200px;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 20px;
        cursor: pointer;
        border: 2px dashed #cacaca;
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0px 48px 35px -48px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s ease;
    }

    .custum-file-upload:hover {
        background-color: #f0f0f0;
    }

    .custum-file-upload .icon svg {
        height: 80px;
        fill: rgba(75, 85, 99, 1);
    }

    .custum-file-upload input[type="file"] {
        display: block;
    }

    .custum-file-upload .text span {
        color: rgba(75, 85, 99, 1);
        font-weight: 400;
    }
</style>

<div class="form max-w-md mx-auto mt-6 bg-darkk text-black p-4 rounded-lg shadow">
    <h3 class="text-center text-xl font-semibold mb-2">Step 2: Upload Payment Proof</h3>

    <div id="toast" class="toast">Please upload a payment proof before submitting!</div>

    <div class="text-center mb-4">
        <p class="font-medium">You're selling:</p>
        <p class="text-lg font-semibold text-green-800">${{ number_format($amountInUsd, 2) }} USD</p>
        <p class="mt-2 text-sm">Send <strong>${{ number_format($amountInUsd, 2) }}</strong> worth of <strong>{{ $coin }}</strong> to the wallet address below:</p>
    </div>

    <div class="bg-gray-100 p-4 rounded-lg text-center font-mono text-sm text-gray-800 mb-4">
        <span id="walletAddress">{{ $walletAddress }}</span>
        <span class="copybutton" onclick="copyWalletAddress()">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6.59961 11.3974C6.59961 8.67119 6.59961 7.3081 7.44314 6.46118C8.28667 5.61426 9.64432 5.61426 12.3596 5.61426H15.2396C17.9549 5.61426 19.3125 5.61426 20.1561 6.46118C20.9996 7.3081 20.9996 8.6712 20.9996 11.3974V16.2167C20.9996 18.9429 20.9996 20.306 20.1561 21.1529C19.3125 21.9998 17.9549 21.9998 15.2396 21.9998H12.3596C9.64432 21.9998 8.28667 21.9998 7.44314 21.1529C6.59961 20.306 6.59961 18.9429 6.59961 16.2167V11.3974Z" fill="#027e27" />
                <path opacity="0.5" d="M4.17157 3.17157C3 4.34315 3 6.22876 3 10V12C3 15.7712 3 17.6569 4.17157 18.8284C4.78913 19.446 5.6051 19.738 6.79105 19.8761C6.59961 19.0353 6.59961 17.8796 6.59961 16.2167V11.3974C6.59961 8.6712 6.59961 7.3081 7.44314 6.46118C8.28667 5.61426 9.64432 5.61426 12.3596 5.61426H15.2396C16.8915 5.61426 18.0409 5.61426 18.8777 5.80494C18.7403 4.61146 18.4484 3.79154 17.8284 3.17157C16.6569 2 14.7712 2 11 2C7.22876 2 5.34315 2 4.17157 3.17157Z" fill="#027e27" />
            </svg>
        </span>
    </div>

    @if ($barcode)
    <div class="text-center mb-6">
        <img src="{{ $barcode }}" alt="{{ $coin }} Barcode" class="mx-auto w-40 h-40 object-contain rounded-md shadow">
        <p class="text-xs text-gray-500 mt-1">Scan to send {{ $coin }}</p>
    </div>
    @endif

    <form id="paymentProofForm" action="{{ route('sell.postStep2') }}" method="POST" enctype="multipart/form-data" class="text-center">
        @csrf

        <div class="mb-4">
            <label for="proof" class="block text-sm font-medium">Upload Payment Proof</label><br>

            <label class="custum-file-upload">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" />
                    </svg>
                </div>
                <div class="text"><span>Click to upload image</span></div>
                <input type="file" name="proof" id="proof" accept=".jpg,.jpeg,.png,.pdf" required>
            </label>

            @error('proof')
            <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="w-full btn p-3 rounded font-semibold text-white hover:bg-green-700 transition duration-200">
            Next
        </button>
    </form>
</div>

<script>
    function copyWalletAddress() {
        const walletAddress = document.getElementById('walletAddress').textContent;
        navigator.clipboard.writeText(walletAddress).then(() => {
            const toast = document.getElementById('toast');
            toast.textContent = 'Wallet address copied!';
            toast.style.backgroundColor = '#38a169';
            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 3000);
        }).catch(error => {
            console.error('Copy failed: ', error);
        });
    }

    document.getElementById("paymentProofForm").addEventListener("submit", function(e) {
        let proofFile = document.getElementById("proof").files.length;
        if (proofFile === 0) {
            e.preventDefault();
            const toast = document.getElementById('toast');
            toast.textContent = 'Payment proof is required!';
            toast.style.backgroundColor = '#f44336';
            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 3000);
        }
    });
</script>

@endsection
