@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto p-6 bg-white shadow rounded-xl mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center">Step 2: Upload Payment Proof</h2>
    
    <p class="mb-4 text-gray-700">Send your crypto to this address:</p>
    <div class="bg-gray-100 p-3 rounded mb-4 text-center font-mono">
        {{ $walletAddress }}
    </div>

    <form action="{{ route('sell.postStep2') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block font-semibold mb-1">Upload Payment Proof</label>
            <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required class="w-full border rounded p-2">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700">
            Continue to Step 3
        </button>
    </form>
</div>
@endsection
