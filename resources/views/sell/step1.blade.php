@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto p-6 bg-white shadow rounded-xl mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center">Step 1: Sell Crypto</h2>
    <form action="{{ route('sell.postStep1') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block font-semibold mb-1">Select Coin</label>
            <select name="coin" required class="w-full border rounded p-2">
                <option value="">-- Select Coin --</option>
                <option value="BTC">Bitcoin (BTC)</option>
                <option value="ETH">Ethereum (ETH)</option>
                <option value="USDT">USDT (TRC20)</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Amount (USD)</label>
            <input type="number" name="amount" min="1" required class="w-full border rounded p-2">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700">
            Continue to Step 2
        </button>
    </form>
</div>
@endsection
