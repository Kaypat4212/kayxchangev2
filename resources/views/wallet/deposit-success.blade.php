@extends('layouts.app')

@section('title', 'Crypto Deposit Status')

@section('content')
@php
    $isBitcoin = strtoupper($deposit->currency) === 'BTC';
    $paymentData = $deposit->payment_data ?? [];
    $providerStatus = $paymentData['provider_status'] ?? 'waiting';
    $confirmations = (int) ($paymentData['bitcoin_confirmations'] ?? 0);
    $requiredConfirmations = (int) ($paymentData['bitcoin_required_confirmations'] ?? 3);
    $payerAmount = data_get($paymentData, 'payer_amount');
@endphp
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 md:p-8">
            <div class="flex items-start gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-coins text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Crypto deposit status</h1>
                    <p class="text-gray-600 mt-1">This page refreshes automatically while your payment is processing.</p>
                </div>
            </div>

            <div class="rounded-lg bg-gray-50 border border-gray-200 p-5 space-y-3">
                <div class="flex justify-between gap-4"><span class="text-gray-600">Asset</span><strong>{{ strtoupper($deposit->currency) }}</strong></div>
                <div class="flex justify-between gap-4"><span class="text-gray-600">Invoice value</span><strong>${{ number_format((float) $deposit->expected_amount, 2) }} USD</strong></div>
                @if($isBitcoin && $payerAmount)
                    <div class="flex justify-between gap-4"><span class="text-gray-600">Expected BTC</span><strong>{{ number_format((float) $payerAmount, 8) }} BTC</strong></div>
                @endif
                <div class="flex justify-between gap-4"><span class="text-gray-600">Payment status</span><strong id="deposit-status">{{ ucfirst(str_replace('_', ' ', $providerStatus)) }}</strong></div>
                @if($isBitcoin)
                    <div class="border-t border-gray-200 pt-3">
                        <div class="flex justify-between gap-4"><span class="text-gray-600">Bitcoin confirmations</span><strong id="confirmation-count">{{ $confirmations }} / {{ $requiredConfirmations }}</strong></div>
                        <div class="text-sm text-gray-600 mt-1" id="confirmation-remaining">@if($deposit->status === 'completed') Payment provider confirmed settlement. @elseif($deposit->transaction_hash) {{ max(0, $requiredConfirmations - $confirmations) }} more confirmation(s) required @else Waiting for the provider to detect your transaction. @endif</div>
                    </div>
                    <div id="tx-row" class="{{ $deposit->transaction_hash ? '' : 'hidden' }} border-t border-gray-200 pt-3">
                        <span class="text-gray-600 text-sm">Transaction</span>
                        <div><a id="tx-link" class="text-blue-700 underline break-all" href="{{ $deposit->transaction_hash ? 'https://blockstream.info/tx/' . rawurlencode($deposit->transaction_hash) : '#' }}" target="_blank" rel="noopener">{{ $deposit->transaction_hash ?: 'Waiting for transaction' }}</a></div>
                    </div>
                @endif
                <div id="ngn-row" class="{{ $deposit->ngn_credited_amount ? '' : 'hidden' }} border-t border-gray-200 pt-3 flex justify-between gap-4">
                    <span class="text-gray-600">NGN credited</span>
                    <strong id="ngn-credited">{{ $deposit->ngn_credited_amount ? '₦' . number_format((float) $deposit->ngn_credited_amount, 2) : '' }}</strong>
                </div>
                @if($deposit->ngn_rate_used)
                    <div class="flex justify-between gap-4 text-sm"><span class="text-gray-600">BTC sell rate used</span><span>₦{{ number_format((float) $deposit->ngn_rate_used, 2) }} / USD</span></div>
                @endif
            </div>

            <p id="deposit-help" class="text-sm text-gray-600 mt-4">
                @if($isBitcoin)
                    Your NGN balance is credited after Cryptomus confirms the Bitcoin payment. The final conversion uses the KayXchange BTC sell rate at confirmation.
                @else
                    Your crypto wallet is credited after the payment provider confirms the deposit.
                @endif
            </p>
            <div class="flex flex-wrap gap-3 mt-6">
                <a href="{{ route('wallet.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">Back to Wallet</a>
                <a href="{{ route('wallet.history') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-5 py-2 rounded-lg font-medium">View History</a>
                @if($deposit->payment_url)
                    <a href="{{ $deposit->payment_url }}" target="_blank" rel="noopener" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700">Open payment page</a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const statusUrl = @json(route('wallet.deposit.status', $deposit->id));
    const statusEl = document.getElementById('deposit-status');
    const helpEl = document.getElementById('deposit-help');
    const timer = setInterval(refreshStatus, 15000);

    function refreshStatus() {
        fetch(statusUrl, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'})
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                if (!data) return;
                statusEl.textContent = data.status === 'completed' ? 'Completed' : (data.status === 'failed' ? 'Needs attention' : String(data.provider_status || 'pending').replaceAll('_', ' '));
                if (data.transaction_hash) {
                    const row = document.getElementById('tx-row');
                    const link = document.getElementById('tx-link');
                    row?.classList.remove('hidden');
                    if (link) { link.textContent = data.transaction_hash; link.href = data.explorer_url; }
                }
                const count = document.getElementById('confirmation-count');
                const remaining = document.getElementById('confirmation-remaining');
                if (count && data.currency === 'BTC') {
                    count.textContent = `${data.confirmations} / ${data.required_confirmations}`;
                    remaining.textContent = data.status === 'completed' ? 'Payment provider confirmed settlement.' : (data.transaction_hash ? (data.remaining_confirmations > 0 ? `${data.remaining_confirmations} more confirmation(s) required` : 'On-chain confirmation target reached; awaiting provider settlement.') : 'Waiting for the provider to detect your transaction.');
                }
                if (data.ngn_credited_amount) {
                    document.getElementById('ngn-row')?.classList.remove('hidden');
                    document.getElementById('ngn-credited').textContent = '₦' + Number(data.ngn_credited_amount).toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    helpEl.textContent = 'Your NGN balance has been credited using the BTC sell rate shown above.';
                }
                if (data.status === 'completed' || data.status === 'failed') clearInterval(timer);
            })
            .catch(() => {});
    }
    refreshStatus();
})();
</script>
@endsection
