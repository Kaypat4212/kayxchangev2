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

.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.25rem;margin-bottom:1rem;}

.live-badge{display:inline-flex;align-items:center;gap:.4rem;font-size:.7rem;font-weight:600;color:var(--kx-green);background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.25);border-radius:999px;padding:.25rem .7rem;margin-top:.6rem;}
.live-badge .dot{width:7px;height:7px;border-radius:50%;background:var(--kx-green);animation:pulse 1.6s ease-in-out infinite;}
.live-badge.stale{color:#f59e0b;background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.25);}
.live-badge.stale .dot{background:#f59e0b;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.35;}}

.swap-panel{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:14px;padding:1rem;transition:border-color .2s;}
.swap-panel:focus-within{border-color:rgba(0,204,0,.4);}
.swap-panel-label{font-size:.75rem;color:var(--kx-muted);margin-bottom:.6rem;display:flex;justify-content:space-between;}
.swap-panel-label .usd-val{color:var(--kx-muted);font-variant-numeric:tabular-nums;}
.swap-row{display:flex;align-items:center;gap:.75rem;}
.coin-select-wrap{position:relative;flex-shrink:0;}
.coin-select{appearance:none;background:var(--kx-card);border:1px solid var(--kx-border);border-radius:10px;padding:.55rem 2rem .55rem .7rem;color:var(--kx-text);font-size:.9rem;font-weight:700;cursor:pointer;max-width:130px;}
.coin-select:focus{outline:none;border-color:var(--kx-green);}
.coin-select-wrap::after{content:'\F282';font-family:'bootstrap-icons';position:absolute;right:.6rem;top:50%;transform:translateY(-50%);color:var(--kx-muted);font-size:.7rem;pointer-events:none;}
.amount-input{flex:1;min-width:0;background:transparent;border:none;color:var(--kx-text);font-size:1.4rem;font-weight:700;text-align:right;font-variant-numeric:tabular-nums;}
.amount-input:focus{outline:none;}
.amount-input::placeholder{color:#3a4356;}
.amount-input[readonly]{color:var(--kx-green);}
input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0;}
input[type=number]{-moz-appearance:textfield;appearance:textfield;}

.swap-btn-wrap{display:flex;justify-content:center;margin:-.55rem 0;position:relative;z-index:2;}
.swap-btn{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#00cc00,#007a0c);border:3px solid var(--kx-card);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .25s;font-size:1.1rem;}
.swap-btn:hover{transform:rotate(180deg);}

.quote-box{margin-top:1rem;border-top:1px dashed var(--kx-border);padding-top:.9rem;display:none;}
.quote-box.visible{display:block;}
.quote-row{display:flex;justify-content:space-between;font-size:.8rem;padding:.28rem 0;}
.quote-row .k{color:var(--kx-muted);}
.quote-row .v{color:var(--kx-text);font-weight:600;font-variant-numeric:tabular-nums;}
.quote-row .v.green{color:var(--kx-green);}
.quote-row .v.amber{color:#f59e0b;}

.btn-kx-primary{background:linear-gradient(90deg,#00cc00,#009900);border:none;border-radius:10px;padding:.85rem 1.5rem;color:#fff;font-weight:700;cursor:pointer;transition:opacity .2s;width:100%;font-size:.95rem;margin-top:1rem;}
.btn-kx-primary:hover{opacity:.9;}
.btn-kx-primary:disabled{opacity:.5;cursor:not-allowed;}

.btn-kx-secondary{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:.75rem 1.5rem;color:var(--kx-text);font-weight:600;cursor:pointer;transition:all .2s;}
.btn-kx-secondary:hover{border-color:var(--kx-green);color:var(--kx-green);}

.notice-warn{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:10px;padding:.85rem 1rem;font-size:.8rem;color:#fbbf24;margin-bottom:1rem;display:flex;gap:.6rem;align-items:flex-start;}

.crypto-info{display:flex;align-items:center;gap:.5rem;padding:.75rem;background:linear-gradient(135deg,rgba(0,204,0,.1),rgba(0,100,0,.05));border:1px solid rgba(0,204,0,.2);border-radius:8px;margin-bottom:1rem;}
.crypto-info-icon{width:32px;height:32px;border-radius:6px;background:#fff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:#00cc00;overflow:hidden;}
.crypto-info-text{flex:1;}
.crypto-info-text h6{font-size:.85rem;font-weight:600;color:var(--kx-text);margin:0 0 .2rem;}
.crypto-info-text p{font-size:.75rem;color:var(--kx-muted);margin:0;}

.hist-row{display:flex;align-items:center;justify-content:space-between;padding:.7rem 0;border-bottom:1px solid var(--kx-border);font-size:.8rem;}
.hist-row:last-child{border-bottom:none;}
.hist-pair{font-weight:700;color:var(--kx-text);}
.hist-sub{color:var(--kx-muted);font-size:.7rem;}
.hist-status{font-size:.68rem;font-weight:700;padding:.2rem .55rem;border-radius:999px;text-transform:uppercase;letter-spacing:.03em;}
.hist-status.completed{background:rgba(16,185,129,.12);color:#34d399;}
.hist-status.pending,.hist-status.processing{background:rgba(245,158,11,.12);color:#fbbf24;}
.hist-status.failed{background:rgba(239,68,68,.12);color:#f87171;}

.mode-pill{display:inline-flex;align-items:center;gap:.4rem;font-size:.7rem;font-weight:700;border-radius:999px;padding:.25rem .7rem;margin-top:.5rem;text-transform:uppercase;letter-spacing:.05em;}
.mode-pill.compare{background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.3);color:#38bdf8;}
.mode-pill.manual{background:rgba(167,139,250,.1);border:1px solid rgba(167,139,250,.3);color:#a78bfa;}
.mode-pill.cryptomus{background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.25);color:var(--kx-green);}

.cmp-table{width:100%;border-collapse:collapse;font-size:.78rem;}
.cmp-table th{color:var(--kx-muted);font-weight:600;text-align:left;padding:.5rem .4rem;border-bottom:1px solid var(--kx-border);font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;}
.cmp-table td{padding:.5rem .4rem;border-bottom:1px solid var(--kx-border);color:var(--kx-text);font-variant-numeric:tabular-nums;}
.cmp-table tr:last-child td{border-bottom:none;}
.cmp-table td.usd{color:var(--kx-muted);}
.cmp-table td.conv{color:var(--kx-green);font-weight:700;}

.wallet-input{width:100%;background:var(--kx-card);border:1px solid var(--kx-border);border-radius:10px;padding:.75rem;color:var(--kx-text);font-size:.85rem;font-family:monospace;margin-top:.75rem;}
.wallet-input:focus{outline:none;border-color:var(--kx-green);box-shadow:0 0 0 2px rgba(0,204,0,.1);}
.wallet-label{font-size:.75rem;color:var(--kx-muted);margin-top:1rem;display:block;}

.instructions-box{background:rgba(56,189,248,.06);border:1px solid rgba(56,189,248,.2);border-radius:10px;padding:.85rem 1rem;font-size:.8rem;color:#7dd3fc;margin-bottom:1rem;display:flex;gap:.6rem;align-items:flex-start;white-space:pre-line;}

.feat-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.feat-item{text-align:center;}
.feat-item i{font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;}
.feat-item .t{font-size:.8rem;color:var(--kx-text);font-weight:600;}
.feat-item .d{font-size:.7rem;color:var(--kx-muted);}

.spinning{animation:spin 1s linear infinite;}
@keyframes spin{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:500px;">

    {{-- Header --}}
    <div class="kx-hero">
        <h1><i class="bi bi-arrow-left-right me-2"></i>Crypto Converter</h1>
        <p>Convert between cryptocurrencies instantly</p>
        <div class="live-badge {{ $rateSource === 'fallback' ? 'stale' : '' }}" id="rateBadge">
            <span class="dot"></span>
            @if($rateSource === 'fallback')
                Estimated rates
            @else
                Live rates · {{ ucfirst($rateSource) }}
            @endif
        </div>
        <br>
        <div class="mode-pill {{ $convertMode }}">
            @if($convertMode === 'compare')
                <i class="bi bi-bar-chart"></i> Compare Mode
            @elseif($convertMode === 'manual')
                <i class="bi bi-person-gear"></i> Manual Mode
            @else
                <i class="bi bi-lightning-charge"></i> Instant Convert
            @endif
        </div>
    </div>

    @if($convertMode === 'cryptomus' && !$cryptomusReady)
    <div class="notice-warn">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>Crypto conversion payments are not fully configured yet. You can preview rates, but submitting a conversion is temporarily unavailable.</div>
    </div>
    @endif

    @if($convertMode === 'manual' && $convertInstructions)
    <div class="instructions-box">
        <i class="bi bi-info-circle-fill"></i>
        <div>{{ $convertInstructions }}</div>
    </div>
    @endif

    {{-- Conversion Form --}}
    <div class="kx-card">
        <form id="convertForm" action="{{ $convertMode === 'manual' ? route('convert.manual') : route('convert.submit') }}" method="post">
            @csrf

            {{-- You Pay --}}
            <div class="swap-panel">
                <div class="swap-panel-label">
                    <span>You pay</span>
                    <span class="usd-val" id="fromUsd">≈ $0.00</span>
                </div>
                <div class="swap-row">
                    <div class="coin-select-wrap">
                        <select id="fromCoin" name="from_coin" class="coin-select" required>
                            @foreach($coins as $symbol => $meta)
                            <option value="{{ $symbol }}" {{ $symbol === 'BTC' ? 'selected' : '' }}>{{ $symbol }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="number" id="fromAmount" name="from_amount" class="amount-input" placeholder="0.00" step="0.00000001" min="0" required autocomplete="off">
                </div>
            </div>

            {{-- Swap --}}
            <div class="swap-btn-wrap">
                <button type="button" class="swap-btn" id="swapBtn" title="Swap coins">
                    <i class="bi bi-arrow-down-up"></i>
                </button>
            </div>

            {{-- You Receive --}}
            <div class="swap-panel">
                <div class="swap-panel-label">
                    <span>You receive (after {{ rtrim(rtrim(number_format($feePercent, 2), '0'), '.') }}% fee)</span>
                    <span class="usd-val" id="toUsd">≈ $0.00</span>
                </div>
                <div class="swap-row">
                    <div class="coin-select-wrap">
                        <select id="toCoin" name="to_coin" class="coin-select" required>
                            @foreach($coins as $symbol => $meta)
                            <option value="{{ $symbol }}" {{ $symbol === 'USDT' ? 'selected' : '' }}>{{ $symbol }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="text" id="toAmount" class="amount-input" placeholder="0.00" readonly>
                </div>
            </div>

            {{-- Quote breakdown --}}
            <div class="quote-box" id="quoteBox">
                <div class="quote-row"><span class="k">Rate</span><span class="v" id="qRate">—</span></div>
                <div class="quote-row"><span class="k">Fee ({{ rtrim(rtrim(number_format($feePercent, 2), '0'), '.') }}%)</span><span class="v amber" id="qFee">—</span></div>
                <div class="quote-row"><span class="k">You receive</span><span class="v green" id="qReceive">—</span></div>
            </div>

            @if($convertMode === 'manual')
            {{-- Manual mode: wallet address --}}
            <label class="wallet-label" for="walletAddress">
                <i class="bi bi-wallet2 me-1"></i>Your <span id="walletCoin">USDT</span> wallet address — where we send your converted crypto
            </label>
            <input type="text" id="walletAddress" name="wallet_address" class="wallet-input" placeholder="Enter your wallet address" minlength="10" maxlength="255" required>
            @endif

            @if($convertMode === 'compare')
            <button type="button" class="btn-kx-primary" disabled style="background:var(--kx-card2);color:var(--kx-muted);border:1px solid var(--kx-border);">
                <i class="bi bi-bar-chart me-1"></i>Compare-only mode — conversions disabled
            </button>
            @elseif($convertMode === 'manual')
            <button type="submit" class="btn-kx-primary" id="convertBtn">
                <i class="bi bi-send me-1"></i>Request Conversion
            </button>
            @else
            <button type="submit" class="btn-kx-primary" id="convertBtn" {{ $cryptomusReady ? '' : 'disabled' }}>
                <i class="bi bi-arrow-left-right me-1"></i>Convert Now
            </button>
            @endif
        </form>
    </div>

    @if($convertMode === 'compare')
    {{-- Compare matrix: 1 unit of selected coin in every other coin --}}
    <div class="kx-card">
        <h6 style="color:var(--kx-text);margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-bar-chart" style="color:#38bdf8;"></i>
            Price Comparison — <span id="cmpCoin">BTC</span>
        </h6>
        <div style="overflow-x:auto;">
            <table class="cmp-table">
                <thead>
                    <tr><th>Coin</th><th>USD Price</th><th>1 <span class="cmp-from">BTC</span> =</th></tr>
                </thead>
                <tbody id="cmpBody"></tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Cryptomus Partnership --}}
    <div class="crypto-info">
        <div class="crypto-info-icon">
            <img src="{{ asset('assets/img/cryptomus-logo.svg') }}" alt="Cryptomus" style="width:100%;height:100%;object-fit:contain;border-radius:4px;" onerror="this.parentElement.textContent='C'">
        </div>
        <div class="crypto-info-text">
            <h6>Powered by Cryptomus</h6>
            <p>Secure and instant cryptocurrency conversions</p>
        </div>
    </div>

    {{-- Recent Conversions --}}
    @if($recentConversions->isNotEmpty())
    <div class="kx-card">
        <h6 style="color:var(--kx-text);margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-clock-history" style="color:var(--kx-green);"></i>
            Recent Conversions
        </h6>
        @foreach($recentConversions as $conv)
        <div class="hist-row">
            <div>
                <div class="hist-pair">{{ rtrim(rtrim(number_format((float) $conv->from_amount, 8), '0'), '.') }} {{ $conv->from_coin }} → {{ rtrim(rtrim(number_format((float) $conv->to_amount, 8), '0'), '.') }} {{ $conv->to_coin }}</div>
                <div class="hist-sub">{{ $conv->created_at->diffForHumans() }}</div>
            </div>
            <span class="hist-status {{ $conv->status }}">{{ $conv->status }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Features --}}
    <div class="kx-card">
        <h6 style="color:var(--kx-text);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
            <i class="bi bi-star" style="color:var(--kx-green);"></i>
            Why Choose Our Converter?
        </h6>
        <div class="feat-grid">
            <div class="feat-item">
                <i class="bi bi-lightning-charge"></i>
                <div class="t">Instant</div>
                <div class="d">Convert in seconds</div>
            </div>
            <div class="feat-item">
                <i class="bi bi-shield-check"></i>
                <div class="t">Secure</div>
                <div class="d">Bank-level security</div>
            </div>
            <div class="feat-item">
                <i class="bi bi-cash-stack"></i>
                <div class="t">Low Fees</div>
                <div class="d">Only {{ rtrim(rtrim(number_format($feePercent, 2), '0'), '.') }}% fee</div>
            </div>
            <div class="feat-item">
                <i class="bi bi-graph-up"></i>
                <div class="t">Live Rates</div>
                <div class="d">Real-time market pricing</div>
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
const COINS = @json($coins);
const FEE_PERCENT = {{ $feePercent }};
const CONVERT_MODE = '{{ $convertMode }}';
const QUOTE_URL = '{{ route('convert.quote') }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const fromCoinEl = document.getElementById('fromCoin');
const toCoinEl = document.getElementById('toCoin');
const fromAmountEl = document.getElementById('fromAmount');
const toAmountEl = document.getElementById('toAmount');
const fromUsdEl = document.getElementById('fromUsd');
const toUsdEl = document.getElementById('toUsd');
const quoteBox = document.getElementById('quoteBox');
const qRate = document.getElementById('qRate');
const qFee = document.getElementById('qFee');
const qReceive = document.getElementById('qReceive');

function fmt(n, dp = 8) {
    return Number(n).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: dp });
}
function fmtUsd(n) {
    return '≈ $' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

let quoteTimer = null;
let quoteSeq = 0;

function scheduleQuote() {
    clearTimeout(quoteTimer);
    quoteTimer = setTimeout(fetchQuote, 350);
}

async function fetchQuote() {
    const fromCoin = fromCoinEl.value;
    const toCoin = toCoinEl.value;
    const fromAmount = parseFloat(fromAmountEl.value) || 0;

    // Instant local USD estimates while waiting
    fromUsdEl.textContent = fmtUsd(fromAmount * (COINS[fromCoin]?.usd || 0));

    if (!fromCoin || !toCoin || fromAmount <= 0 || fromCoin === toCoin) {
        toAmountEl.value = '';
        toUsdEl.textContent = fmtUsd(0);
        quoteBox.classList.remove('visible');
        return;
    }

    const seq = ++quoteSeq;

    try {
        const res = await fetch(QUOTE_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ from_coin: fromCoin, to_coin: toCoin, from_amount: fromAmount })
        });
        const data = await res.json();

        if (seq !== quoteSeq) return; // a newer request superseded this one

        if (data.success) {
            const q = data.quote;
            toAmountEl.value = fmt(q.receive_amount);
            toUsdEl.textContent = fmtUsd(q.receive_amount * (COINS[toCoin]?.usd || 0));
            qRate.textContent = `1 ${fromCoin} = ${fmt(q.rate)} ${toCoin}`;
            qFee.textContent = `-${fmt(q.fee_amount)} ${toCoin}`;
            qReceive.textContent = `${fmt(q.receive_amount)} ${toCoin}`;
            quoteBox.classList.add('visible');
        } else {
            toAmountEl.value = '';
            quoteBox.classList.remove('visible');
        }
    } catch (e) {
        if (seq !== quoteSeq) return;
        toAmountEl.value = '';
        quoteBox.classList.remove('visible');
    }
}

fromCoinEl.addEventListener('change', () => { scheduleQuote(); renderCompare(); });
toCoinEl.addEventListener('change', () => {
    scheduleQuote();
    const wc = document.getElementById('walletCoin');
    if (wc) wc.textContent = toCoinEl.value;
});
fromAmountEl.addEventListener('input', scheduleQuote);

// Compare matrix: 1 unit of FROM coin priced in every other coin
function renderCompare() {
    const tbody = document.getElementById('cmpBody');
    if (!tbody) return;
    const from = fromCoinEl.value;
    const fromUsd = COINS[from]?.usd || 0;
    document.getElementById('cmpCoin').textContent = from;
    document.querySelectorAll('.cmp-from').forEach(el => el.textContent = from);

    tbody.innerHTML = Object.entries(COINS)
        .filter(([sym]) => sym !== from)
        .map(([sym, meta]) => {
            const conv = fromUsd > 0 && meta.usd > 0 ? fromUsd / meta.usd : 0;
            return `<tr>
                <td><strong>${sym}</strong> <span style="color:var(--kx-muted);font-size:.7rem;">${meta.name}</span></td>
                <td class="usd">$${Number(meta.usd).toLocaleString('en-US', {maximumFractionDigits: meta.usd < 1 ? 6 : 2})}</td>
                <td class="conv">${fmt(conv)} ${sym}</td>
            </tr>`;
        }).join('');
}
renderCompare();

// Swap coins
document.getElementById('swapBtn').addEventListener('click', function() {
    const f = fromCoinEl.value;
    fromCoinEl.value = toCoinEl.value;
    toCoinEl.value = f;
    scheduleQuote();
});

// Form submission
document.getElementById('convertForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('convertBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spinning me-1"></i>Processing...';
    btn.disabled = true;

    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (CONVERT_MODE === 'manual') {
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Request Submitted!';
                btn.style.background = 'linear-gradient(90deg,#10b981,#059669)';
                setTimeout(() => { window.location.href = '{{ route('convert.success', ['id' => '__ID__']) }}'.replace('__ID__', data.conversion_id); }, 1200);
                return;
            }
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Redirecting to Payment...';
            btn.style.background = 'linear-gradient(90deg,#10b981,#059669)';

            if (data.payment_url) {
                setTimeout(() => { window.location.href = data.payment_url; }, 800);
            } else {
                setTimeout(() => { window.location.href = '{{ route('convert.success', ['id' => '__ID__']) }}'.replace('__ID__', data.conversion_id); }, 1200);
            }
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
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
</script>
@endsection
