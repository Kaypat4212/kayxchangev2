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

/* Steps */
.kx-steps{display:flex;gap:0;margin-bottom:1.5rem;}
.kx-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative;}
.kx-step:not(:last-child)::after{content:'';position:absolute;top:14px;left:50%;width:100%;height:2px;background:var(--kx-green);z-index:0;}
.step-circle{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;border:2px solid var(--kx-green);background:var(--kx-green);color:#000;position:relative;z-index:1;}
.step-label{font-size:.72rem;color:var(--kx-green);margin-top:.3rem;text-align:center;}

/* Crypto payment card */
.crypto-payment-card{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1.25rem;margin-bottom:1rem;}
.crypto-address{display:flex;align-items:center;gap:.75rem;padding:1rem;background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.2);border-radius:8px;margin-bottom:1rem;}
.crypto-address-icon{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#00cc00,#007a0c);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;}
.crypto-address-details{flex:1;}
.crypto-address-label{font-size:.8rem;color:var(--kx-muted);margin-bottom:.25rem;}
.crypto-address-value{font-size:1rem;font-weight:600;color:var(--kx-text);word-break:break-all;font-family:monospace;}
.crypto-copy-btn{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:var(--kx-green);border-radius:6px;padding:.3rem .6rem;font-size:.75rem;cursor:pointer;transition:all .2s;}
.crypto-copy-btn:hover{background:rgba(0,204,0,.2);}

.crypto-amount{display:flex;justify-content:space-between;align-items:center;padding:.75rem 0;border-bottom:1px solid var(--kx-border);}
.crypto-amount-label{font-size:.85rem;color:var(--kx-muted);}
.crypto-amount-value{font-size:1.1rem;font-weight:700;color:var(--kx-green);}

.crypto-timer{background:rgba(234,179,8,.08);border:1px solid rgba(234,179,8,.2);border-radius:8px;padding:.75rem;margin-bottom:1rem;text-align:center;}
.crypto-timer-label{font-size:.8rem;color:#f59e0b;margin-bottom:.25rem;}
.crypto-timer-value{font-size:1.2rem;font-weight:700;color:#f59e0b;font-variant-numeric:tabular-nums;}

.crypto-instructions{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:1rem;margin-bottom:1rem;}
.crypto-instructions h6{font-size:.9rem;font-weight:600;color:var(--kx-text);margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;}
.crypto-instructions ol{margin:0;padding-left:1.2rem;}
.crypto-instructions li{font-size:.8rem;color:var(--kx-muted);margin-bottom:.25rem;}

.crypto-partner{display:flex;align-items:center;gap:.5rem;padding:.75rem;background:linear-gradient(135deg,rgba(0,204,0,.1),rgba(0,100,0,.05));border:1px solid rgba(0,204,0,.2);border-radius:8px;margin-bottom:1rem;}
.crypto-partner-logo{width:32px;height:32px;border-radius:6px;background:#fff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:#00cc00;}
.crypto-partner-text{flex:1;}
.crypto-partner-text h6{font-size:.85rem;font-weight:600;color:var(--kx-text);margin:0 0 .2rem;}
.crypto-partner-text p{font-size:.75rem;color:var(--kx-muted);margin:0;}

.crypto-status{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:1rem;text-align:center;}
.crypto-status-icon{font-size:2rem;margin-bottom:.5rem;}
.crypto-status-icon.pending{color:#f59e0b;}
.crypto-status-icon.processing{color:#3b82f6;}
.crypto-status-icon.completed{color:var(--kx-green);}
.crypto-status-title{font-size:1rem;font-weight:600;color:var(--kx-text);margin-bottom:.25rem;}
.crypto-status-desc{font-size:.8rem;color:var(--kx-muted);}

.btn-kx-primary{background:linear-gradient(90deg,#00cc00,#009900);border:none;border-radius:8px;padding:.75rem 1.5rem;color:#fff;font-weight:600;cursor:pointer;transition:opacity .2s;width:100%;}
.btn-kx-primary:hover{opacity:.9;}
.btn-kx-primary:disabled{opacity:.5;cursor:not-allowed;}

.btn-kx-secondary{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:.75rem 1.5rem;color:var(--kx-text);font-weight:600;cursor:pointer;transition:all .2s;}
.btn-kx-secondary:hover{border-color:var(--kx-green);color:var(--kx-green);}
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:500px;">

    {{-- Header --}}
    <div class="kx-hero">
        <h1><i class="bi bi-lightning-charge me-2"></i>Crypto Payment</h1>
        <p>Pay instantly with cryptocurrency</p>
    </div>

    {{-- Progress Steps --}}
    <div class="kx-steps">
        <div class="kx-step">
            <div class="step-circle">✓</div>
            <div class="step-label">Order Created</div>
        </div>
        <div class="kx-step">
            <div class="step-circle">2</div>
            <div class="step-label">Pay Crypto</div>
        </div>
        <div class="kx-step">
            <div class="step-circle">3</div>
            <div class="step-label">Receive Crypto</div>
        </div>
    </div>

    {{-- Trade Summary --}}
    <div class="kx-card">
        <div class="crypto-amount">
            <span class="crypto-amount-label">You're buying</span>
            <span class="crypto-amount-value">{{ number_format($trade->usd_amount, 2) }} USD worth of {{ $trade->coin }}</span>
        </div>
        <div class="crypto-amount">
            <span class="crypto-amount-label">Crypto equivalent</span>
            <span class="crypto-amount-value">{{ number_format($trade->usd_amount / $trade->rate_used, 8) }} {{ $trade->coin }}</span>
        </div>
        <div class="crypto-amount" style="border-bottom:none;">
            <span class="crypto-amount-label">Payment amount</span>
            <span class="crypto-amount-value">₦{{ number_format($trade->naira_amount, 2) }}</span>
        </div>
    </div>

    {{-- Crypto Payment Card --}}
    <div class="crypto-payment-card">
        <h6 style="color:var(--kx-text);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-credit-card" style="color:var(--kx-green);"></i>
            Send Payment
        </h6>

        @if(isset($paymentData['address']))
        {{-- Crypto Address --}}
        <div class="crypto-address">
            <div class="crypto-address-icon">
                @if($trade->coin === 'BTC')
                    <i class="bi bi-currency-bitcoin"></i>
                @elseif($trade->coin === 'ETH')
                    <i class="bi bi-gem"></i>
                @else
                    <i class="bi bi-cash-stack"></i>
                @endif
            </div>
            <div class="crypto-address-details">
                <div class="crypto-address-label">Send {{ $trade->coin }} to this address</div>
                <div class="crypto-address-value" id="cryptoAddress">{{ $paymentData['address'] }}</div>
            </div>
            <button class="crypto-copy-btn" onclick="copyToClipboard('{{ $paymentData['address'] }}')">
                <i class="bi bi-clipboard me-1"></i>Copy
            </button>
        </div>

        {{-- Payment Amount --}}
        <div class="crypto-amount">
            <span class="crypto-amount-label">Exact amount to send</span>
            <span class="crypto-amount-value">{{ $paymentData['amount'] ?? '0' }} {{ $paymentData['currency'] ?? $trade->coin }}</span>
        </div>

        {{-- Timer --}}
        @if(isset($paymentData['expired_at']))
        <div class="crypto-timer">
            <div class="crypto-timer-label">Payment expires in</div>
            <div class="crypto-timer-value" id="countdown">00:00:00</div>
        </div>
        @endif
        @endif
    </div>

    {{-- Instructions --}}
    <div class="crypto-instructions">
        <h6><i class="bi bi-info-circle" style="color:var(--kx-green);"></i>How to Pay</h6>
        <ol>
            <li>Open your crypto wallet app</li>
            <li>Copy the address above</li>
            <li>Send exactly {{ $paymentData['amount'] ?? 'the' }} {{ $paymentData['currency'] ?? $trade->coin }} to this address</li>
            <li>Wait for confirmation (usually 1-10 minutes)</li>
            <li>Your crypto will be sent to your wallet automatically</li>
        </ol>
    </div>

    {{-- Cryptomus Partnership --}}
    <div class="crypto-partner">
        <div class="crypto-partner-logo">
            <img src="{{ asset('assets/img/cryptomus-logo.svg') }}" alt="Cryptomus" style="width:100%;height:100%;object-fit:contain;">
        </div>
        <div class="crypto-partner-text">
            <h6>Powered by Cryptomus</h6>
            <p>Trusted cryptocurrency payment gateway with 99.9% uptime</p>
        </div>
    </div>

    {{-- Status --}}
    <div class="crypto-status">
        <div class="crypto-status-icon pending">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="crypto-status-title">Waiting for Payment</div>
        <div class="crypto-status-desc">We'll notify you once payment is confirmed</div>
    </div>

    {{-- Actions --}}
    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('buy.summary', $trade->id) }}" class="btn-kx-secondary" style="flex:1;text-decoration:none;text-align:center;">
            <i class="bi bi-arrow-left me-1"></i>Back to Summary
        </a>
        <button class="btn-kx-primary" style="flex:1;" onclick="checkPaymentStatus()">
            <i class="bi bi-arrow-repeat me-1"></i>Check Status
        </button>
    </div>

    {{-- Reference --}}
    <div style="text-align:center;margin-top:1rem;">
        <small style="color:var(--kx-muted);">Reference: {{ $trade->transaction_ref }}</small>
    </div>
</div>

<script>
// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success feedback
        const btn = event.target.closest('.crypto-copy-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
        btn.style.background = 'rgba(0,204,0,.2)';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
        }, 2000);
    });
}

// Countdown timer
@if(isset($paymentData['expired_at']))
const expiredAt = new Date('{{ $paymentData['expired_at'] }}').getTime();

function updateCountdown() {
    const now = new Date().getTime();
    const distance = expiredAt - now;

    if (distance < 0) {
        document.getElementById('countdown').innerHTML = 'EXPIRED';
        return;
    }

    const hours = Math.floor(distance / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById('countdown').innerHTML =
        String(hours).padStart(2, '0') + ':' +
        String(minutes).padStart(2, '0') + ':' +
        String(seconds).padStart(2, '0');

    setTimeout(updateCountdown, 1000);
}

updateCountdown();
@endif

// Check payment status
function checkPaymentStatus() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spinning me-1"></i>Checking...';
    btn.disabled = true;

    fetch('{{ route('cryptomus.currencies') }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // This is just a placeholder - in real implementation you'd check the specific payment status
        btn.innerHTML = originalText;
        btn.disabled = false;
    })
    .catch(error => {
        console.error('Error checking status:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Add spinning animation
const style = document.createElement('style');
style.textContent = '.spinning { animation: spin 1s linear infinite; } @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
@endsection