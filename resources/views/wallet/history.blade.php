@extends('layouts.app')

@section('title', 'Transaction History')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Transaction History</h1>

        <!-- Tabs -->
        <div class="mb-8">
            <nav class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
                <button id="deposits-tab" class="tab-button active px-4 py-2 text-sm font-medium rounded-md transition-colors">
                    Deposits
                </button>
                <button id="withdrawals-tab" class="tab-button px-4 py-2 text-sm font-medium rounded-md transition-colors">
                    Withdrawals
                </button>
            </nav>
        </div>

        <!-- Deposits Tab Content -->
        <div id="deposits-content" class="tab-content">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Crypto Deposits</h2>
                </div>

                @if($deposits->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($deposits as $deposit)
                            <div class="p-6 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-coins text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $deposit->currency }} Deposit</div>
                                            <div class="text-sm text-gray-600">
                                                {{ $deposit->created_at->format('M d, Y H:i') }}
                                                @if($deposit->transaction_hash)
                                                    <br><span class="text-xs text-gray-500">TX: {{ Str::limit($deposit->transaction_hash, 20) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-gray-900">+{{ number_format($deposit->amount, 8) }} {{ $deposit->currency }}</div>
                                        <div class="text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                @if($deposit->status === 'completed') bg-green-100 text-green-800
                                                @elseif($deposit->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($deposit->status === 'failed') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($deposit->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border-gray-200">
                        {{ $deposits->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No deposits yet</h3>
                        <p class="text-gray-600">Your crypto deposit history will appear here.</p>
                        <a href="{{ route('wallet.deposit') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md">
                            Make Your First Deposit
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Withdrawals Tab Content -->
        <div id="withdrawals-content" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Crypto Withdrawals</h2>
                </div>

                @if($withdrawals->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($withdrawals as $withdrawal)
                            <div class="p-6 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-arrow-up text-red-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $withdrawal->wallet->currency }} Withdrawal</div>
                                            <div class="text-sm text-gray-600">
                                                {{ $withdrawal->created_at->format('M d, Y H:i') }}
                                                @if($withdrawal->transaction_hash)
                                                    <br><span class="text-xs text-gray-500">TX: {{ Str::limit($withdrawal->transaction_hash, 20) }}</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $withdrawal->address }}">
                                                To: {{ Str::limit($withdrawal->address, 25) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-gray-900">-{{ number_format($withdrawal->amount, 8) }} {{ $withdrawal->wallet->currency }}</div>
                                        <div class="text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                @if($withdrawal->status === 'completed') bg-green-100 text-green-800
                                                @elseif($withdrawal->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($withdrawal->status === 'processing') bg-blue-100 text-blue-800
                                                @elseif($withdrawal->status === 'rejected') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($withdrawal->status) }}
                                            </span>
                                        </div>
                                        @if($withdrawal->fee > 0)
                                            <div class="text-xs text-gray-500">Fee: {{ number_format($withdrawal->fee, 8) }} {{ $withdrawal->wallet->currency }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border-gray-200">
                        {{ $withdrawals->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No withdrawals yet</h3>
                        <p class="text-gray-600">Your crypto withdrawal history will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active', 'bg-white', 'text-gray-900'));
            tabs.forEach(t => t.classList.add('text-gray-600'));

            // Add active class to clicked tab
            this.classList.add('active', 'bg-white', 'text-gray-900');
            this.classList.remove('text-gray-600');

            // Hide all contents
            contents.forEach(content => content.classList.add('hidden'));

            // Show corresponding content
            const targetId = this.id.replace('-tab', '-content');
            document.getElementById(targetId).classList.remove('hidden');
        });
    });
});
</script>
@endsection