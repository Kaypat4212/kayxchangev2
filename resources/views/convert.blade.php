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

.convert-form{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1.5rem;margin-bottom:1rem;}

.convert-input-group{margin-bottom:1.5rem;}
.convert-input-label{font-size:.85rem;color:var(--kx-muted);margin-bottom:.5rem;display:block;}
.convert-input{display:flex;align-items:center;gap:.75rem;}
.convert-input select,.convert-input input{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:8px;padding:.75rem;color:var(--kx-text);font-size:1rem;flex:1;}
.convert-input select:focus,.convert-input input:focus{outline:none;border-color:var(--kx-green);box-shadow:0 0 0 2px rgba(0,204,0,.1);}
.convert-input-icon{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#00cc00,#007a0c);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;}

.convert-arrow{text-align:center;margin:1rem 0;}
.convert-arrow-icon{font-size:2rem;color:var(--kx-green);}

.convert-result{background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.2);border-radius:8px;padding:1rem;margin-top:1rem;text-align:center;}
.convert-result-amount{font-size:1.5rem;font-weight:700;color:var(--kx-green);margin-bottom:.25rem;}
.convert-result-rate{font-size:.8rem;color:var(--kx-muted);}

.convert-fee{background:rgba(234,179,8,.08);border:1px solid rgba(234,179,8,.2);border-radius:8px;padding:.75rem;margin-bottom:1rem;}
.convert-fee-label{font-size:.8rem;color:#f59e0b;margin-bottom:.25rem;}
.convert-fee-value{font-size:1rem;font-weight:600;color:#f59e0b;}

.btn-kx-primary{background:linear-gradient(90deg,#00cc00,#009900);border:none;border-radius:8px;padding:.75rem 1.5rem;color:#fff;font-weight:600;cursor:pointer;transition:opacity .2s;width:100%;}
.btn-kx-primary:hover{opacity:.9;}
.btn-kx-primary:disabled{opacity:.5;cursor:not-allowed;}

.btn-kx-secondary{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:.75rem 1.5rem;color:var(--kx-text);font-weight:600;cursor:pointer;transition:all .2s;}
.btn-kx-secondary:hover{border-color:var(--kx-green);color:var(--kx-green);}

.crypto-info{display:flex;align-items:center;gap:.5rem;padding:.75rem;background:linear-gradient(135deg,rgba(0,204,0,.1),rgba(0,100,0,.05));border:1px solid rgba(0,204,0,.2);border-radius:8px;margin-bottom:1rem;}
.crypto-info-icon{width:32px;height:32px;border-radius:6px;background:#fff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:#00cc00;}
.crypto-info-text{flex:1;}
.crypto-info-text h6{font-size:.85rem;font-weight:600;color:var(--kx-text);margin:0 0 .2rem;}
.crypto-info-text p{font-size:.75rem;color:var(--kx-muted);margin:0;}
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:500px;">

    {{-- Header --}}
    <div class="kx-hero">
        <h1><i class="bi bi-arrow-left-right me-2"></i>Crypto Converter</h1>
        <p>Convert between cryptocurrencies instantly</p>
    </div>

    {{-- Conversion Form --}}
    <div class="kx-card">
        <form id="convertForm" action="{{ route('convert.submit') }}" method="post">
            @csrf

            {{-- From Currency --}}
            <div class="convert-input-group">
                <label class="convert-input-label">From</label>
                <div class="convert-input">
                    <div class="convert-input-icon">
                        <i class="bi bi-currency-bitcoin" id="fromIcon"></i>
                    </div>
                    <select id="fromCoin" name="from_coin" required>
                        <option value="">Select cryptocurrency</option>
                        @foreach($rates as $coin => $rate)
                        <option value="{{ $coin }}" {{ $coin === 'BTC' ? 'selected' : '' }}>{{ $coin }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="number" id="fromAmount" name="from_amount" placeholder="0.00" step="0.00000001" min="0" required style="margin-top:.5rem;">
            </div>

            {{-- Arrow --}}
            <div class="convert-arrow">
                <i class="bi bi-arrow-down convert-arrow-icon"></i>
            </div>

            {{-- To Currency --}}
            <div class="convert-input-group">
                <label class="convert-input-label">To</label>
                <div class="convert-input">
                    <div class="convert-input-icon">
                        <i class="bi bi-gem" id="toIcon"></i>
                    </div>
                    <select id="toCoin" name="to_coin" required>
                        <option value="">Select cryptocurrency</option>
                        @foreach($rates as $coin => $rate)
                        <option value="{{ $coin }}" {{ $coin === 'ETH' ? 'selected' : '' }}>{{ $coin }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="number" id="toAmount" name="to_amount" placeholder="0.00" step="0.00000001" readonly style="margin-top:.5rem;background:var(--kx-card2);">
            </div>

            {{-- Fee Information --}}
            <div class="convert-fee">
                <div class="convert-fee-label">Conversion Fee</div>
                <div class="convert-fee-value">0.5% (minimum ₦100)</div>
            </div>

            {{-- Convert Button --}}
            <button type="submit" class="btn-kx-primary" id="convertBtn">
                <i class="bi bi-arrow-left-right me-1"></i>Convert Crypto
            </button>
        </form>
    </div>

    {{-- Cryptomus Partnership --}}
    <div class="crypto-info">
        <div class="crypto-info-icon">
            <img src="{{ asset('assets/img/cryptomus-logo.svg') }}" alt="Cryptomus" style="width:100%;height:100%;object-fit:contain;border-radius:4px;">
        </div>
        <div class="crypto-info-text">
            <h6>Powered by Cryptomus</h6>
            <p>Secure and instant cryptocurrency conversions</p>
        </div>
    </div>

    {{-- Features --}}
    <div class="kx-card">
        <h6 style="color:var(--kx-text);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-star" style="color:var(--kx-green);"></i>
            Why Choose Our Converter?
        </h6>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div style="text-align:center;">
                <i class="bi bi-lightning-charge" style="font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;"></i>
                <div style="font-size:.8rem;color:var(--kx-text);font-weight:600;">Instant</div>
                <div style="font-size:.7rem;color:var(--kx-muted);">Convert in seconds</div>
            </div>
            <div style="text-align:center;">
                <i class="bi bi-shield-check" style="font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;"></i>
                <div style="font-size:.8rem;color:var(--kx-text);font-weight:600;">Secure</div>
                <div style="font-size:.7rem;color:var(--kx-muted);">Bank-level security</div>
            </div>
            <div style="text-align:center;">
                <i class="bi bi-cash-stack" style="font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;"></i>
                <div style="font-size:.8rem;color:var(--kx-text);font-weight:600;">Low Fees</div>
                <div style="font-size:.7rem;color:var(--kx-muted);">Only 0.5% fee</div>
            </div>
            <div style="text-align:center;">
                <i class="bi bi-graph-up" style="font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;"></i>
                <div style="font-size:.8rem;color:var(--kx-text);font-weight:600;">Best Rates</div>
                <div style="font-size:.7rem;color:var(--kx-muted);">Competitive pricing</div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn-kx-secondary" style="flex:1;text-decoration:none;text-align:center;">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
        <a href="{{ route('buy') }}" class="btn-kx-secondary" style="flex:1;text-decoration:none;text-align:center;">
            <i class="bi bi-cash me-1"></i>Buy Crypto
        </a>
    </div>
</div>

<script>
// Crypto icons mapping
const cryptoIcons = {
    'BTC': 'bi-currency-bitcoin',
    'ETH': 'bi-gem',
    'USDT': 'bi-cash-stack',
    'BNB': 'bi-circle',
    'ADA': 'bi-hexagon',
    'SOL': 'bi-circle',
    'DOT': 'bi-circle',
    'DOGE': 'bi-circle',
    'LTC': 'bi-circle',
    'XRP': 'bi-circle'
};

// Update icons when coin selection changes
document.getElementById('fromCoin').addEventListener('change', function() {
    const coin = this.value;
    const icon = cryptoIcons[coin] || 'bi-cash-stack';
    document.getElementById('fromIcon').className = `bi ${icon}`;
    calculateConversion();
});

document.getElementById('toCoin').addEventListener('change', function() {
    const coin = this.value;
    const icon = cryptoIcons[coin] || 'bi-cash-stack';
    document.getElementById('toIcon').className = `bi ${icon}`;
    calculateConversion();
});

// Calculate conversion when amount changes
document.getElementById('fromAmount').addEventListener('input', calculateConversion);

// Conversion rates (simplified - in real app, fetch from API)
const rates = @json($rates);

function calculateConversion() {
    const fromCoin = document.getElementById('fromCoin').value;
    const toCoin = document.getElementById('toCoin').value;
    const fromAmount = parseFloat(document.getElementById('fromAmount').value) || 0;

    if (!fromCoin || !toCoin || fromAmount <= 0) {
        document.getElementById('toAmount').value = '';
        return;
    }

    // Simplified conversion logic
    // In real implementation, you'd use proper exchange rates
    const fromRate = rates[fromCoin]?.buy_rate || 1;
    const toRate = rates[toCoin]?.sell_rate || 1;

    // Convert to USD first, then to target crypto
    const usdValue = fromAmount * fromRate;
    const toAmount = usdValue / toRate;

    // Apply 0.5% fee
    const fee = Math.max(toAmount * 0.005, 0.000001); // minimum fee
    const finalAmount = toAmount - fee;

    document.getElementById('toAmount').value = finalAmount.toFixed(8);
}

// Initialize icons
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('fromCoin').dispatchEvent(new Event('change'));
    document.getElementById('toCoin').dispatchEvent(new Event('change'));
});

// Form submission
document.getElementById('convertForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('convertBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spinning me-1"></i>Processing...';
    btn.disabled = true;

    // Get form data
    const formData = new FormData(this);

    // Submit the form via AJAX
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Redirecting to Payment...';
            btn.style.background = 'linear-gradient(90deg,#10b981,#059669)';

            // Redirect to payment URL if available
            if (data.payment_url) {
                setTimeout(() => {
                    window.location.href = data.payment_url;
                }, 1000);
            } else {
                setTimeout(() => {
                    window.location.href = '{{ route('dashboard') }}';
                }, 2000);
            }
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;

            // Show error message
            alert(data.message || 'An error occurred during conversion');
        }
    })
    .catch(error => {
        console.error('Conversion error:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Network error. Please try again.');
    });
});

// Add spinning animation
const style = document.createElement('style');
style.textContent = '.spinning { animation: spin 1s linear infinite; } @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
@endsection