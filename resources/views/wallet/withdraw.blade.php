@extends('layouts.app')

@section('title', 'Withdraw Crypto')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Withdraw Cryptocurrency</h1>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('wallet.withdraw.create') }}" method="POST">
                @csrf

                <!-- Wallet Selection -->
                <div class="mb-6">
                    <label for="wallet_id" class="block text-sm font-medium text-gray-700 mb-2">Select Wallet</label>
                    <select name="wallet_id" id="wallet_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Choose wallet...</option>
                        @foreach($wallets as $wallet)
                            @if($wallet->isCrypto() && $wallet->balance > 0)
                                <option value="{{ $wallet->id }}" data-balance="{{ $wallet->balance }}" data-currency="{{ $wallet->currency }}">
                                    {{ $wallet->currency }} - Balance: {{ number_format($wallet->balance, 8) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('wallet_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <input type="number" name="amount" id="amount" step="0.00000001" min="0.00000001"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter amount" required>
                    <div id="max-amount" class="mt-1 text-sm text-gray-600 hidden">
                        Available: <span id="available-balance"></span>
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="mb-6">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Recipient Address</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter recipient address" required>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Network -->
                <div class="mb-6">
                    <label for="network" class="block text-sm font-medium text-gray-700 mb-2">Network</label>
                    <select name="network" id="network" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select network...</option>
                        <option value="BTC">Bitcoin (BTC)</option>
                        <option value="ETH">Ethereum (ETH)</option>
                        <option value="ERC20">ERC20</option>
                        <option value="BEP20">BEP20</option>
                        <option value="TRC20">TRC20</option>
                        <option value="SOL">Solana (SOL)</option>
                        <option value="ADA">Cardano (ADA)</option>
                        <option value="DOT">Polkadot (DOT)</option>
                        <option value="MATIC">Polygon (MATIC)</option>
                    </select>
                    @error('network')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Warning -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium">Important:</p>
                            <ul class="mt-1 list-disc list-inside">
                                <li>Double-check the recipient address</li>
                                <li>Ensure the network is correct</li>
                                <li>Withdrawals are processed manually and may take 1-3 business days</li>
                                <li>Network fees will be deducted from your withdrawal</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-md transition duration-200">
                    Submit Withdrawal Request
                </button>
            </form>
        </div>

        <!-- Recent Withdrawals -->
        @php
            $recentWithdrawals = \App\Models\CryptoWithdrawal::where('user_id', auth()->id())
                ->latest()
                ->limit(3)
                ->get();
        @endphp

        @if($recentWithdrawals->count() > 0)
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Withdrawals</h2>
                <div class="space-y-4">
                    @foreach($recentWithdrawals as $withdrawal)
                        <div class="flex items-center justify-between p-4 bg-white rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-arrow-up text-red-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $withdrawal->wallet->currency }} Withdrawal</div>
                                    <div class="text-sm text-gray-600">{{ $withdrawal->created_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900">-{{ number_format($withdrawal->amount, 8) }} {{ $withdrawal->wallet->currency }}</div>
                                <div class="text-sm {{ $withdrawal->status === 'completed' ? 'text-green-600' : ($withdrawal->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.getElementById('wallet_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const balance = selectedOption.getAttribute('data-balance');
    const currency = selectedOption.getAttribute('data-currency');
    const maxAmountDiv = document.getElementById('max-amount');
    const availableBalanceSpan = document.getElementById('available-balance');

    if (balance && currency) {
        availableBalanceSpan.textContent = balance + ' ' + currency;
        maxAmountDiv.classList.remove('hidden');
        document.getElementById('amount').max = balance;
    } else {
        maxAmountDiv.classList.add('hidden');
        document.getElementById('amount').removeAttribute('max');
    }
});
</script>
@endsection