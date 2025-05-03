@extends('layouts.app')

@section('title', 'Sell Crypto')

@section('content')
<div class="container mx-auto px-4 py-10">
    <div class="max-w-lg mx-auto bg-white shadow-lg rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Sell Your Crypto</h2>
        <p class="text-center text-gray-600 mb-6">Begin the process to sell your cryptocurrency securely and easily.</p>
        
        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex justify-center">
            <a href="{{ route('sell.step1') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-300">
                Start Selling
            </a>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('transaction.history') }}" class="text-blue-500 hover:underline">View Transaction History</a>
        </div>
    </div>
</div>
@endsection
