@extends('layouts.app')

@section('title', 'Deposit Successful')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-green-50 border border-green-200 rounded-lg p-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-green-900 mb-4">Deposit Invoice Created!</h1>
            <p class="text-green-700 mb-6">
                Your deposit invoice has been created successfully. You will be redirected to complete the payment.
            </p>

            <div class="bg-white rounded-lg p-6 mb-6 text-left">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Deposit Details</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Amount:</span>
                        <span class="font-medium">{{ number_format($deposit->amount, 8) }} {{ $deposit->currency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">{{ ucfirst($deposit->status) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Created:</span>
                        <span class="font-medium">{{ $deposit->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex space-x-4 justify-center">
                <a href="{{ route('wallet.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                    Back to Wallet
                </a>
                <a href="{{ route('wallet.history') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">
                    View History
                </a>
            </div>
        </div>
    </div>
</div>
@endsection