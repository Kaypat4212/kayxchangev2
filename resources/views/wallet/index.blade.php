@extends('layouts.app')

@section('title', 'My Wallet')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">My Wallet</h1>

        <!-- Wallet Balances -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($wallets as $wallet)
                <div class="bg-white rounded-lg shadow-md p-6 {{ $wallet->isCrypto() ? 'border-l-4 border-blue-500' : 'border-l-4 border-green-500' }}">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $wallet->currency }}</h3>
                        @if($wallet->isCrypto())
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Crypto</span>
                        @else
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Fiat</span>
                        @endif
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-2">{{ $wallet->formattedBalance }}</div>
                    @if($wallet->isCrypto() && $wallet->address)
                        <div class="text-sm text-gray-600 truncate" title="{{ $wallet->address }}">
                            {{ Str::limit($wallet->address, 20) }}
                        </div>
                        @if($wallet->network)
                            <div class="text-xs text-gray-500">{{ $wallet->network }}</div>
                        @endif
                    @endif
                    <div class="mt-4 flex space-x-2">
                        @if($wallet->isCrypto())
                            <a href="{{ route('wallet.deposit') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">Deposit</a>
                            <a href="{{ route('wallet.withdraw') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">Withdraw</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('wallet.deposit') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-medium">
                    <i class="fas fa-plus-circle mr-2"></i>Deposit Crypto
                </a>
                <a href="{{ route('wallet.withdraw') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg text-center font-medium">
                    <i class="fas fa-minus-circle mr-2"></i>Withdraw Crypto
                </a>
                <a href="{{ route('wallet.send') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg text-center font-medium">
                    <i class="fas fa-paper-plane mr-2"></i>Send Money
                </a>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Recent Deposits</h2>
                <a href="{{ route('wallet.history') }}" class="text-blue-500 hover:text-blue-600 text-sm">View All</a>
            </div>

            @if($recentDeposits->count() > 0)
                <div class="space-y-4">
                    @foreach($recentDeposits as $deposit)
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-coins text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $deposit->currency }} Deposit</div>
                                    <div class="text-sm text-gray-600">{{ $deposit->created_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900">{{ number_format($deposit->amount, 8) }} {{ $deposit->currency }}</div>
                                <div class="text-sm {{ $deposit->status === 'completed' ? 'text-green-600' : ($deposit->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ ucfirst($deposit->status) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-4"></i>
                    <p>No recent deposits</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection