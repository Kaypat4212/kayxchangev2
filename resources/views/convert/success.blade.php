@extends('buylayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;
    --kx-dark:#0d1117;
    --kx-card:#161b27;
    --kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0;
    --kx-muted:#7a8599;
}
body{background:var(--kx-dark);color:var(--kx-text);}

.kx-hero{background:linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);border-bottom:1px solid var(--kx-border);padding:1.5rem 1rem 1rem;text-align:center;margin-bottom:1.5rem;}
.kx-hero h1{font-size:1.5rem;font-weight:700;color:#fff;margin:0 0 .25rem;}
.kx-hero p{color:var(--kx-muted);font-size:.875rem;margin:0;}

.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1rem;}

.success-icon{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#fff;font-size:2rem;}

.btn-kx-primary{background:linear-gradient(90deg,#00cc00,#009900);border:none;border-radius:8px;padding:.75rem 1.5rem;color:#fff;font-weight:600;cursor:pointer;transition:opacity .2s;width:100%;}
.btn-kx-primary:hover{opacity:.9;}
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:500px;">

    {{-- Header --}}
    <div class="kx-hero">
        <h1><i class="bi bi-arrow-left-right me-2"></i>{{ $conversion->status === 'completed' ? 'Swap complete' : ($conversion->status === 'failed' ? 'Swap update' : 'Swap in progress') }}</h1>
        <p>{{ $conversion->mode === 'nowpayments' && $conversion->status !== 'completed' ? 'We are confirming your swap and will update your wallets when its status changes.' : 'Your cryptocurrency swap status and details' }}</p>
    </div>

    {{-- Success Card --}}
    <div class="kx-card text-center">
        <div class="success-icon" style="{{ $conversion->status === 'completed' ? '' : 'background:linear-gradient(135deg,#f59e0b,#d97706);' }}">
            <i class="bi {{ $conversion->status === 'completed' ? 'bi-check-circle-fill' : ($conversion->status === 'failed' ? 'bi-x-circle-fill' : 'bi-arrow-repeat') }}"></i>
        </div>

        <h4 id="conversionStatus" style="color:var(--kx-text);margin-bottom:1rem;">{{ ucfirst($conversion->status) }}{{ $conversion->status === 'completed' ? '!' : '' }}</h4>

        <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:1rem;margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
                <span style="color:var(--kx-muted);">From:</span>
                <span style="color:var(--kx-text);font-weight:600;">{{ $conversion->from_amount }} {{ $conversion->from_coin }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
                <span id="toAmountLabel" style="color:var(--kx-muted);">{{ $conversion->status === 'completed' ? 'To:' : 'Estimated receive:' }}</span>
                <span id="toAmountValue" style="color:var(--kx-text);font-weight:600;">{{ rtrim(rtrim(number_format((float) $conversion->to_amount, 8), '0'), '.') }} {{ $conversion->to_coin }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--kx-muted);">{{ $conversion->mode === 'nowpayments' ? 'Total swap fee:' : 'Fee:' }}</span>
                <span id="feeAmountValue" style="color:#f59e0b;font-weight:600;">{{ rtrim(rtrim(number_format((float) $conversion->fee_amount, 8), '0'), '.') }} {{ $conversion->to_coin }}</span>
            </div>
        </div>

        @if($conversion->isManual())
        <p style="color:var(--kx-muted);font-size:.9rem;margin-bottom:1rem;">
            Your swap request has been submitted. Our team will process it and send
            <strong style="color:var(--kx-text);">{{ rtrim(rtrim(number_format((float) $conversion->to_amount, 8), '0'), '.') }} {{ $conversion->to_coin }}</strong>
            to your wallet address:
        </p>
        <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:.75rem;font-family:monospace;font-size:.75rem;word-break:break-all;color:var(--kx-green);margin-bottom:1.5rem;">
            {{ $conversion->wallet_address }}
        </div>
        @elseif($conversion->mode === 'nowpayments')
        <p id="conversionMessage" style="color:var(--kx-muted);font-size:.9rem;margin-bottom:1.5rem;">
            @if($conversion->status === 'completed')
                Your destination wallet has been credited.
            @elseif($conversion->status === 'failed')
                The swap could not be completed. Your source wallet balance has been restored.
            @else
                Your source amount is reserved while the swap is processing. Do not submit this swap again.
            @endif
        </p>
        @else
        <p style="color:var(--kx-muted);font-size:.9rem;margin-bottom:1.5rem;">
            Your swapped {{ $conversion->to_coin }} has been credited to your wallet.
        </p>
        @endif

        <a href="{{ route('dashboard') }}" class="btn-kx-primary" style="text-decoration:none;display:inline-block;">
            <i class="bi bi-house me-1"></i>Back to Dashboard
        </a>
    </div>

    {{-- Additional Actions --}}
    <div class="kx-card">
        <h6 style="color:var(--kx-text);margin-bottom:1rem;">What would you like to do next?</h6>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <a href="{{ route('swap') }}" style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:1rem;text-decoration:none;text-align:center;color:var(--kx-text);">
                <i class="bi bi-arrow-left-right" style="font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;"></i>
                <div style="font-size:.9rem;font-weight:600;">Swap again</div>
            </a>
            <a href="{{ route('buy') }}" style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:1rem;text-decoration:none;text-align:center;color:var(--kx-text);">
                <i class="bi bi-cash" style="font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;"></i>
                <div style="font-size:.9rem;font-weight:600;">Buy Crypto</div>
            </a>
        </div>
    </div>
</div>
@if($conversion->mode === 'nowpayments' && in_array($conversion->status, ['pending', 'processing'], true))
<script>
(function () {
    const url = @json(route('swap.status', $conversion->id));
    const statusEl = document.getElementById('conversionStatus');
    const messageEl = document.getElementById('conversionMessage');
    const toLabelEl = document.getElementById('toAmountLabel');
    const toAmountEl = document.getElementById('toAmountValue');
    const feeEl = document.getElementById('feeAmountValue');
    const fmt = value => Number(value || 0).toLocaleString('en-US', { maximumFractionDigits: 8 });
    let pollTimer = null;
    async function refresh() {
        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!response.ok) return;
            const data = await response.json();
            if (!data.success) return;
            statusEl.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1) + (data.status === 'completed' ? '!' : '');
            toAmountEl.textContent = fmt(data.to_amount) + ' ' + data.to_coin;
            feeEl.textContent = fmt(data.fee_amount) + ' ' + data.to_coin;
            if (data.status === 'completed') {
                toLabelEl.textContent = 'To:';
                messageEl.textContent = 'Your destination wallet has been credited.';
                window.location.reload();
            } else if (data.status === 'failed') {
                messageEl.textContent = 'The swap could not be completed. Your source wallet balance has been restored.';
                window.location.reload();
            } else if (data.syncable === false) {
                messageEl.textContent = data.message || 'Your swap is still being confirmed. Your source balance remains reserved; contact support if this status persists.';
                if (pollTimer) window.clearInterval(pollTimer);
            }
        } catch (e) { /* keep displaying the last confirmed state */ }
    }
    refresh();
    pollTimer = window.setInterval(refresh, 12000);
})();
</script>
@endif
@endsection
