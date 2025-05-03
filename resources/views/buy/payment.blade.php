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
        max-width: 600px;
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

    .payment-info{
        margin: auto;
        text-align: start;
    }
    .payment-info p {
        font-size: 16px;
        margin-bottom: 10px;
    }
</style>


@section('content')
{{-- Trade Info --}}

<h1 style="font-weight: 300; text-decoration: underline;">Trade Summary</h1>
<div class="payment-info  mb-8 space-y-4">
        <p><strong>Crypcurrency:</strong> {{ $trade->coin }}</p>
        <p><strong>Amount (USD):</strong> ${{ number_format($trade->usd_amount, 2) }}</p>
        <p><strong>Amount (Naira):</strong> ₦{{ number_format($trade->naira_amount, 2) }}</p>
        <p><strong>Wallet Address:</strong> {{ $trade->wallet_address }}</p>
    </div>
<div class="form max-w-md mx-auto m-5 mt-6 text-black p-6 rounded-lg shadow">
   
    

    {{-- Payment Instruction --}}
    <div class="bg-gray-800 p-6 rounded-lg mb-8 text-center">
        <h4 class="text-xl font-semibold mb-4 text-green-400">Payment Instructions</h4>
        <p class="text-gray-200 mb-2">Please transfer the exact amount to the company account below:</p>
        <p class="text-lg"><strong>Account Name:</strong> {{ $accountDetails->name }}</p>
        <p class="text-lg"><strong>Account Number:</strong> {{ $accountDetails->account_number }}</p>
    </div>

    {{-- Payment Proof Upload Form --}}
    <form method="POST" action="{{ route('buy.uploadPayment', ['id' => $trade->id]) }}" enctype="multipart/form-data" class="text-center mb-5">
        @csrf
        <div class="mb-4">
            <label for="payment_proof" class="block text-sm font-medium">Upload Payment Proof</label><br>
            <input type="file" name="payment_proof" id="payment_proof" class="w-full p-2 rounded bg-gray-800 text-black" required>
            @error('payment_proof')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class=" bg-green-600 mb-5 mt-4 p-3 rounded font-semibold">Submit Payment Proof</button>
    </form>
</div>

<footer class="text-center">
    Kay Xchange 2025
</footer>
@endsection
