@extends('layouts.app')

@section('title', 'Deposit Failed')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-red-50 border border-red-200 rounded-lg p-8">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-red-900 mb-4">Deposit Failed</h1>
            <p class="text-red-700 mb-6">
                There was an issue creating your deposit invoice. Please try again.
            </p>

            @if($deposit)
                <div class="bg-white rounded-lg p-6 mb-6 text-left">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Deposit Details</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-medium">{{ number_format($deposit->amount, 8) }} {{ $deposit->currency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">{{ ucfirst($deposit->status) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium">{{ $deposit->created_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex space-x-4 justify-center">
                <a href="{{ route('wallet.deposit') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                    Try Again
                </a>
                <a href="{{ route('wallet.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">
                    Back to Wallet
                </a>
            </div>
        </div>
    </div>
</div>
@endsection