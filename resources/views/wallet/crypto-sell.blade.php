@extends('layouts.app')
@section('title', 'Sell Crypto from Wallet')

@push('styles')
<style>
.cws-page{max-width:940px;margin:auto;padding:28px 16px 68px;color:#edf5f0}.cws-panel{border:1px solid rgba(255,255,255,.09);border-radius:22px;background:linear-gradient(145deg,#111b25,#111a18);padding:clamp(19px,4vw,32px);box-shadow:0 18px 48px rgba(0,0,0,.16)}.cws-kicker{color:#73e59e;font-size:.72rem;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.cws-muted{color:#9daeb0;font-size:.83rem}.cws-field{display:block;width:100%;border:1px solid rgba(255,255,255,.13);border-radius:12px;background:#0b1217;color:#f3f7f5;padding:12px 13px}.cws-field:focus{outline:0;border-color:#30d878;box-shadow:0 0 0 3px rgba(48,216,120,.12)}.cws-label{display:block;margin:16px 0 7px;color:#dbe6df;font-size:.82rem;font-weight:750}.cws-quote{border:1px solid rgba(85,222,130,.18);border-radius:15px;background:rgba(41,166,88,.07);padding:16px}.cws-quote strong{color:#8cf0ae}.cws-summary{display:grid;gap:8px}.cws-row{display:flex;justify-content:space-between;gap:14px;color:#a8b8ae;font-size:.8rem}.cws-row strong{color:#f1f6f2;text-align:right}.cws-help{border-radius:13px;padding:13px 14px;background:rgba(68,145,255,.07);border:1px solid rgba(68,145,255,.15);color:#b7cede;font-size:.77rem;line-height:1.55}.cws-warning{border-radius:13px;padding:13px 14px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:#f4d38c;font-size:.78rem;line-height:1.55}.cws-submit{width:100%;border:0;border-radius:13px;background:linear-gradient(135deg,#18c85a,#07863c);color:#fff;padding:14px;font-weight:850}.cws-submit:disabled{opacity:.48;cursor:not-allowed}.cws-select option{background:#101a18;color:#fff}
@media(max-width:575px){.cws-page{padding:16px 12px 56px}.cws-panel{border-radius:18px}}
</style>
@endpush

@section('content')
<main class="cws-page">
    <a class="text-success text-decoration-none small" href="{{ route('wallet.crypto') }}"><i class="bi bi-arrow-left me-1"></i>Crypto portfolio</a>
    <section class="cws-panel mt-3">
        <div class="cws-kicker">NOWPayments Custody</div>
        <h1 class="h2 fw-bold mt-2 mb-2">Sell crypto from your wallet</h1>
        <p class="cws-muted mb-4">Choose an asset already in your KayXchange wallet. We convert it through NOWPayments Custody and credit your Naira wallet after the provider confirms the conversion.</p>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

        @if(!$providerReady)
            <div class="cws-warning"><strong>Temporarily unavailable.</strong> NOWPayments wallet selling is switched on, but Custody credentials or conversion access are not ready. Your wallet balance has not changed. Contact support.</div>
        @elseif(!$marketFresh)
            <div class="cws-warning"><strong>Live prices are temporarily unavailable.</strong> You can review your balance, but selling will be enabled when fresh market quotes return.</div>
        @elseif($wallets->isEmpty())
            <div class="cws-warning">No NOWPayments-supported crypto wallets are currently available on your account.</div>
        @endif

        <form method="POST" action="{{ route('wallet.crypto.sell.submit') }}" id="cryptoSellForm">
            @csrf
            <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">

            <label class="cws-label" for="wallet_id">Crypto asset</label>
            <select class="cws-field cws-select" name="wallet_id" id="wallet_id" required>
                @foreach($wallets as $wallet)
                    @php($coin = strtoupper((string) $wallet->currency))
                    @php($price = (float) ($marketPrices[$coin] ?? 0))
                    <option value="{{ $wallet->id }}" data-coin="{{ $coin }}" data-balance="{{ (float) $wallet->balance }}" data-price="{{ $price }}" @selected((string) old('wallet_id', $wallets->firstWhere('balance', '>', 0)?->id ?? $wallet->id) === (string) $wallet->id) @disabled((float) $wallet->balance <= 0 || $price <= 0)>
                        {{ $coin }} · Available {{ number_format((float) $wallet->balance, 8) }} {{ $coin }}
                    </option>
                @endforeach
            </select>
            <div class="cws-muted mt-2" id="availableBalance">Select a wallet balance to sell.</div>

            <label class="cws-label" for="amount">Amount to sell</label>
            <input class="cws-field" type="number" name="amount" id="amount" min="0.00000001" max="1000000000" step="0.00000001" value="{{ old('amount') }}" inputmode="decimal" placeholder="0.00000000" required>

            <div class="cws-quote mt-3" aria-live="polite">
                <div class="cws-kicker mb-2">Estimated payout</div>
                <div class="h3 fw-bold mb-1" id="estimatedNgn">₦0.00</div>
                <div class="cws-muted" id="estimatedUsd">Enter an amount to see the estimate.</div>
            </div>

            <div class="cws-summary mt-3">
                <div class="cws-row"><span>Platform USD sell rate</span><strong>₦{{ number_format($ngnRate, 2) }} / USD</strong></div>
                <div class="cws-row"><span>Estimated combined fees</span><strong>{{ number_format($feePercent, 2) }}%</strong></div>
                <div class="cws-row"><span>Sell limits</span><strong>${{ number_format($minimumUsd, 2) }} – ${{ number_format($maximumUsd, 2) }} USD equivalent</strong></div>
            </div>

            <div class="cws-help mt-3"><i class="bi bi-shield-check me-1"></i>Your crypto is reserved while NOWPayments processes the conversion. We credit NGN only after provider success. If NOWPayments rejects the request, your crypto is restored. Quotes can change before confirmation.</div>

            <label class="cws-label" for="pin">Transaction PIN</label>
            <input class="cws-field" type="password" id="pin" name="pin" inputmode="numeric" autocomplete="current-password" pattern="[0-9]{4}" maxlength="4" placeholder="Enter your 4-digit PIN" required>

            <button class="cws-submit mt-4" id="submitSell" type="submit" @disabled(!$providerReady || !$marketFresh || $wallets->isEmpty())><i class="bi bi-arrow-left-right me-2"></i>Confirm crypto sale</button>
        </form>
    </section>
</main>
<script>
(() => {
    const select = document.getElementById('wallet_id');
    const amount = document.getElementById('amount');
    const available = document.getElementById('availableBalance');
    const estimateNgn = document.getElementById('estimatedNgn');
    const estimateUsd = document.getElementById('estimatedUsd');
    const fee = {{ json_encode((float) $feePercent) }};
    const ngnRate = {{ json_encode((float) $ngnRate) }};
    const fmt = new Intl.NumberFormat('en-NG', {minimumFractionDigits:2, maximumFractionDigits:2});
    function refreshQuote() {
        const option = select?.selectedOptions?.[0];
        const balance = Number(option?.dataset.balance || 0);
        const price = Number(option?.dataset.price || 0);
        const coin = option?.dataset.coin || '';
        const value = Number(amount.value || 0);
        amount.max = String(balance);
        available.textContent = coin ? `Available: ${balance.toFixed(8)} ${coin} · Market price: $${price.toLocaleString('en-US', {maximumFractionDigits: 6})}` : 'Select a wallet balance to sell.';
        const usd = value * price;
        const ngn = usd * ngnRate * Math.max(0, 1 - fee / 100);
        estimateNgn.textContent = `₦${fmt.format(ngn || 0)}`;
        estimateUsd.textContent = value > 0 && price > 0 ? `${value.toFixed(8)} ${coin} ≈ $${usd.toFixed(2)} before estimated fees · final amount uses NOWPayments settlement` : 'Enter an amount to see the estimate.';
    }
    select?.addEventListener('change', refreshQuote);
    amount?.addEventListener('input', refreshQuote);
    document.getElementById('cryptoSellForm')?.addEventListener('submit', event => {
        const option = select.selectedOptions[0];
        if (Number(amount.value) <= 0 || Number(amount.value) > Number(option.dataset.balance || 0)) {
            event.preventDefault();
            amount.setCustomValidity('Enter an amount within your available wallet balance.');
            amount.reportValidity();
        } else amount.setCustomValidity('');
        if (!event.defaultPrevented) {
            const button = document.getElementById('submitSell');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Checking reserve and submitting…';
        }
    });
    refreshQuote();
})();
</script>
@endsection
