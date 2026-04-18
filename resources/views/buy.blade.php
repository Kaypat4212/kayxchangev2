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

.kx-hero{
    background:linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);
    border-bottom:1px solid var(--kx-border);
    padding:1.5rem 1rem 1rem;
    text-align:center;
    margin-bottom:1.5rem;
}
.kx-hero h1{font-size:1.5rem;font-weight:700;color:#fff;margin:0 0 .25rem;}
.kx-hero p{color:var(--kx-muted);font-size:.875rem;margin:0;}

.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1rem;}

/* Progress steps */
.kx-steps{display:flex;gap:0;margin-bottom:1.5rem;}
.kx-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative;}
.kx-step:not(:last-child)::after{content:'';position:absolute;top:14px;left:50%;width:100%;height:2px;background:var(--kx-border);z-index:0;}
.kx-step.active:not(:last-child)::after{background:var(--kx-green);}
.step-circle{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;border:2px solid var(--kx-border);background:var(--kx-card2);color:var(--kx-muted);position:relative;z-index:1;}
.kx-step.active .step-circle{border-color:var(--kx-green);background:rgba(0,204,0,.15);color:var(--kx-green);}
.kx-step.done .step-circle{border-color:var(--kx-green);background:var(--kx-green);color:#000;}
.step-label{font-size:.72rem;color:var(--kx-muted);margin-top:.3rem;text-align:center;}
.kx-step.active .step-label{color:var(--kx-green);}

/* Coin selector */
.coin-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1.25rem;}
.coin-card{background:var(--kx-card2);border:2px solid var(--kx-border);border-radius:12px;padding:.85rem .5rem;text-align:center;cursor:pointer;transition:all .2s;}
.coin-card:hover{border-color:rgba(255,255,255,.2);}
.coin-card.selected-btc{border-color:#f7931a;background:rgba(247,147,26,.1);}
.coin-card.selected-eth{border-color:#627eea;background:rgba(98,126,234,.1);}
.coin-card.selected-usdt{border-color:#26a17b;background:rgba(38,161,123,.1);}
.coin-icon{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;margin:0 auto .5rem;}
.coin-icon.btc{background:rgba(247,147,26,.2);color:#f7931a;}
.coin-icon.eth{background:rgba(98,126,234,.2);color:#627eea;}
.coin-icon.usdt{background:rgba(38,161,123,.2);color:#26a17b;}
.coin-name{font-size:.8rem;font-weight:600;color:var(--kx-text);}
.coin-ticker{font-size:.7rem;color:var(--kx-muted);}

/* Rate badge */
.rate-badge{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);border-radius:8px;padding:.5rem 1rem;text-align:center;font-size:.85rem;color:var(--kx-green);margin-bottom:1rem;}

/* Inputs */
.kx-label{font-size:.8rem;font-weight:600;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;}
.kx-input{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:10px!important;padding:.75rem 1rem!important;font-size:.95rem!important;}
.kx-input:focus{border-color:var(--kx-green)!important;box-shadow:0 0 0 3px rgba(0,204,0,.1)!important;outline:none!important;}
.kx-input[readonly]{background:rgba(255,255,255,.03)!important;color:var(--kx-muted)!important;}
.input-group .kx-input{border-radius:10px 0 0 10px!important;}

/* Toggle btn */
.btn-toggle{background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-green);border-radius:0 10px 10px 0!important;padding:.75rem 1rem;transition:background .2s;}
.btn-toggle:hover{background:rgba(0,204,0,.1);color:var(--kx-green);}

/* Conversion display */
.conversion-box{background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.15);border-radius:10px;padding:1rem;margin-bottom:1rem;display:none;}
.conversion-box.visible{display:block;}
.conversion-box .conv-label{font-size:.75rem;color:var(--kx-muted);margin-bottom:.25rem;}
.conversion-box .conv-value{font-size:1.2rem;font-weight:700;color:var(--kx-green);}

/* Guidelines */
.kx-guide{background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.12);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;}
.kx-guide h6{color:var(--kx-green);font-size:.85rem;font-weight:700;margin-bottom:.6rem;}
.kx-guide ul{list-style:none;padding:0;margin:0;}
.kx-guide li{font-size:.8rem;color:var(--kx-muted);padding:.2rem 0;padding-left:1.1rem;position:relative;}
.kx-guide li::before{content:'\2713';color:var(--kx-green);position:absolute;left:0;}

/* Wallet info pill */
.wallet-info-row{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;}
.wallet-info-row .wi-label{font-size:.75rem;color:var(--kx-muted);}
.wallet-info-row .wi-value{font-size:.9rem;color:var(--kx-text);font-weight:600;}

/* Buttons */
.btn-kx-primary{background:var(--kx-green);border:none;color:#000;font-weight:700;border-radius:10px;padding:.85rem 1.5rem;font-size:.95rem;width:100%;transition:all .2s;}
.btn-kx-primary:hover{background:#00e600;transform:translateY(-1px);box-shadow:0 4px 20px rgba(0,204,0,.3);}
.btn-kx-primary:disabled{opacity:.5;transform:none;cursor:not-allowed;}
.btn-kx-secondary{background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text);font-weight:600;border-radius:10px;padding:.85rem 1.5rem;font-size:.95rem;width:100%;transition:background .2s;}
.btn-kx-secondary:hover{background:rgba(255,255,255,.06);}

/* Toast */
.kx-toast{position:fixed;top:1rem;right:1rem;z-index:9999;min-width:220px;padding:.75rem 1rem;border-radius:10px;font-size:.875rem;font-weight:500;display:none;}
.kx-toast.success{background:#0d2d0d;border:1px solid var(--kx-green);color:var(--kx-green);}
.kx-toast.error{background:#2d0d0d;border:1px solid #ff4444;color:#ff4444;}

/* Other sources section */
.os-section{margin-top:1.5rem;}
.os-header{display:flex;align-items:center;gap:.6rem;margin-bottom:.85rem;}
.os-header-icon{width:32px;height:32px;border-radius:8px;background:rgba(99,160,255,.12);display:flex;align-items:center;justify-content:center;color:#63a0ff;font-size:1rem;flex-shrink:0;}
.os-header h6{margin:0;font-size:.92rem;font-weight:700;color:var(--kx-text);}
.os-header p{margin:0;font-size:.76rem;color:var(--kx-muted);}
.os-disclaimer{background:rgba(255,183,77,.06);border:1px solid rgba(255,183,77,.2);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.8rem;color:#c8a256;line-height:1.55;}
.os-disclaimer i{color:#ffb74d;}
.os-grid{display:grid;grid-template-columns:1fr 1fr;gap:.65rem;}
.os-card{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:.85rem .9rem;display:flex;align-items:flex-start;gap:.65rem;transition:border-color .2s;}
.os-card:hover{border-color:rgba(255,255,255,.18);}
.os-card-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;flex-shrink:0;}
.os-card-body{min-width:0;}
.os-card-name{font-size:.82rem;font-weight:700;color:var(--kx-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.os-card-desc{font-size:.72rem;color:var(--kx-muted);margin-top:.1rem;line-height:1.4;}
.os-card-tag{display:inline-block;font-size:.65rem;padding:.1rem .45rem;border-radius:4px;margin-top:.3rem;font-weight:600;}
.os-manual-note{background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.15);border-radius:10px;padding:.8rem 1rem;margin-top:1rem;display:flex;gap:.6rem;align-items:flex-start;font-size:.8rem;color:var(--kx-muted);line-height:1.55;}
.os-manual-note i{color:var(--kx-green);font-size:1rem;margin-top:.05rem;flex-shrink:0;}
</style>
@endpush

@section('content')
    @php $balance = auth()->user()?->balance ?? 0; @endphp

<div class="kx-toast" id="kxToast"></div>

<div class="kx-hero">
    <h1><i class="bi bi-arrow-down-circle-fill me-2" style="color:var(--kx-green);"></i>Buy Crypto</h1>
    <p>Purchase BTC, ETH or USDT instantly with Naira</p>
</div>

<div class="container-fluid px-3 pb-4">
<div class="row justify-content-center">
<div class="col-xl-5 col-lg-6 col-md-8">

    <!-- Progress steps -->
    <div class="kx-steps">
        <div class="kx-step active" id="progressStep1">
            <div class="step-circle" id="circleStep1">1</div>
            <div class="step-label">Coin &amp; Amount</div>
        </div>
        <div class="kx-step" id="progressStep2">
            <div class="step-circle" id="circleStep2">2</div>
            <div class="step-label">Wallet &amp; Network</div>
        </div>
    </div>

    <form method="POST" action="{{ route('buy.submit') }}" id="buyForm">
        @csrf
        <input type="hidden" name="input_type" id="inputType" value="usd">
        <input type="hidden" name="selected_coin" id="selectedCoinInput" value="">

        <!-- ===== STEP 1 ===== -->
        <div id="step1">
            <div class="kx-guide">
                <h6><i class="bi bi-info-circle me-1"></i>How to Buy</h6>
                <ul>
                    <li>Select a cryptocurrency below</li>
                    <li>Enter the amount in USD or Naira (min $10 / &#8358;14,000)</li>
                    <li>Review the rate then continue to Step 2</li>
                </ul>
            </div>

            <!-- Coin cards -->
            <div class="kx-label mb-1">Select Cryptocurrency</div>
            <div class="coin-cards">
                <div class="coin-card" id="coinCardBTC" onclick="selectCoin('BTC')">
                    <div class="coin-icon btc"><i class="bi bi-currency-bitcoin"></i></div>
                    <div class="coin-name">Bitcoin</div>
                    <div class="coin-ticker">BTC</div>
                </div>
                <div class="coin-card" id="coinCardETH" onclick="selectCoin('ETH')">
                    <div class="coin-icon eth"><i class="bi bi-gem"></i></div>
                    <div class="coin-name">Ethereum</div>
                    <div class="coin-ticker">ETH</div>
                </div>
                <div class="coin-card" id="coinCardUSDT" onclick="selectCoin('USDT')">
                    <div class="coin-icon usdt"><i class="bi bi-cashstack"></i></div>
                    <div class="coin-name">Tether</div>
                    <div class="coin-ticker">USDT-TRC20</div>
                </div>
            </div>
            <select name="coin" id="coin" class="d-none" required>
                <option value="">Choose</option>
                <option value="BTC">BTC</option>
                <option value="ETH">ETH</option>
                <option value="USDT">USDT</option>
            </select>

            <!-- Rate badge -->
            <div class="rate-badge d-none" id="rateBadge">
                <i class="bi bi-graph-up me-1"></i>Current Rate: <strong id="rateValue">&#8212;</strong>
            </div>

            <!-- Amount input -->
            <div class="mb-3">
                <div class="kx-label" id="inputLabel">Amount in USD</div>
                <div class="input-group">
                    <input type="number" name="amount" id="amountInput" step="0.01" min="10"
                        class="form-control kx-input" placeholder="Enter amount" required>
                    <button type="button" id="toggleCurrency" class="btn btn-toggle" title="Toggle USD / NGN">
                        <i class="bi bi-currency-exchange"></i>
                    </button>
                </div>
                <div class="form-text" style="color:var(--kx-muted);font-size:.75rem;">Min: $10 USD or &#8358;14,000</div>
            </div>

            <!-- Conversion display -->
            <div class="conversion-box" id="conversionBox">
                <div class="conv-label" id="convertedLabel">You&#8217;ll Pay (&#8358;)</div>
                <div class="conv-value" id="convertedAmount">&#8212;</div>
            </div>

            <button type="button" id="nextButton" class="btn-kx-primary" disabled>
                Continue <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div><!-- /step1 -->

        <!-- ===== STEP 2 ===== -->
        <div id="step2" class="d-none">
            <!-- Summary pill -->
            <div class="wallet-info-row d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="wi-label">You&#8217;re buying</div>
                    <div class="wi-value" id="step2CoinDisplay">&#8212;</div>
                </div>
                <div class="text-end">
                    <div class="wi-label">Amount</div>
                    <div class="wi-value" id="step2AmountDisplay" style="color:var(--kx-green);">&#8212;</div>
                </div>
            </div>

            <!-- Wallet address -->
            <div class="mb-3">
                <div class="kx-label">Your Wallet Address</div>
                <input type="text" name="wallet_address" id="wallet_address"
                    class="form-control kx-input @error('wallet_address') border-danger @enderror"
                    placeholder="Paste your wallet address here" required>
                <div class="form-text" style="color:var(--kx-muted);font-size:.75rem;">Double-check before confirming</div>
                @error('wallet_address')
                    <div class="text-danger mt-1" style="font-size:.8rem;">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Network -->
            <div class="mb-3">
                <div class="kx-label">Network</div>
                <select name="network" id="network"
                    class="form-select kx-input @error('network') border-danger @enderror" required>
                    <option value="">Select Network</option>
                </select>
                @error('network')
                    <div class="text-danger mt-1" style="font-size:.8rem;">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn-kx-secondary" style="width:auto;min-width:90px;" onclick="goToStep1()">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </button>
                <button type="submit" id="submitButton" class="btn-kx-primary" style="flex:1;">
                    <span id="submitButtonText">Confirm Purchase</span>
                    <i id="submitSpinner" class="bi bi-arrow-repeat ms-2 d-none"></i>
                </button>
            </div>
        </div><!-- /step2 -->
    </form>

    <!-- ===== OTHER SOURCES ===== -->
    <div class="os-section">
        <div class="kx-card">
            <div class="os-header">
                <div class="os-header-icon"><i class="bi bi-shop"></i></div>
                <div>
                    <h6>Other Places to Buy Crypto</h6>
                    <p>External platforms you can use to source your crypto</p>
                </div>
            </div>

            <div class="os-disclaimer">
                <i class="bi bi-shield-exclamation me-1"></i>
                <strong>Heads up:</strong> The platforms below are independent third-party services. We do <strong>not</strong> endorse or take responsibility for any transactions made on them. Use them at your own discretion and always verify you are on the correct website before transacting.
            </div>

            <div class="os-grid">
                <!-- Simplex -->
                <div class="os-card">
                    <div class="os-card-icon" style="background:rgba(0,192,143,.12);color:#00c08f;"><i class="bi bi-credit-card-fill"></i></div>
                    <div class="os-card-body">
                        <div class="os-card-name">Simplex</div>
                        <div class="os-card-desc">Buy crypto with debit/credit card instantly. No ID required for small amounts.</div>
                        <span class="os-card-tag" style="background:rgba(0,192,143,.12);color:#00c08f;">Card</span>
                    </div>
                </div>
                <!-- MoonPay -->
                <div class="os-card">
                    <div class="os-card-icon" style="background:rgba(118,67,255,.12);color:#7643ff;"><i class="bi bi-moon-stars-fill"></i></div>
                    <div class="os-card-body">
                        <div class="os-card-name">MoonPay</div>
                        <div class="os-card-desc">Fast fiat-to-crypto gateway. Supports card, bank &amp; Apple Pay.</div>
                        <span class="os-card-tag" style="background:rgba(118,67,255,.12);color:#7643ff;">Fast</span>
                    </div>
                </div>
                <!-- BitPay -->
                <div class="os-card">
                    <div class="os-card-icon" style="background:rgba(0,126,255,.12);color:#007eff;"><i class="bi bi-shield-check-fill"></i></div>
                    <div class="os-card-body">
                        <div class="os-card-name">BitPay</div>
                        <div class="os-card-desc">Trusted crypto payment processor. Buy BTC, ETH &amp; more securely.</div>
                        <span class="os-card-tag" style="background:rgba(0,126,255,.12);color:#007eff;">Trusted</span>
                    </div>
                </div>
                <!-- Bybit -->
                <div class="os-card">
                    <div class="os-card-icon" style="background:rgba(255,99,72,.12);color:#ff6348;"><i class="bi bi-bar-chart-fill"></i></div>
                    <div class="os-card-body">
                        <div class="os-card-name">Bybit</div>
                        <div class="os-card-desc">P2P buying with NGN support and competitive rates.</div>
                        <span class="os-card-tag" style="background:rgba(255,99,72,.12);color:#ff6348;">P2P</span>
                    </div>
                </div>
                <!-- Coinbase -->
                <div class="os-card">
                    <div class="os-card-icon" style="background:rgba(0,129,255,.12);color:#0081ff;"><i class="bi bi-hexagon-fill"></i></div>
                    <div class="os-card-body">
                        <div class="os-card-name">Coinbase</div>
                        <div class="os-card-desc">Beginner-friendly exchange. Buy with card or bank transfer.</div>
                        <span class="os-card-tag" style="background:rgba(0,129,255,.12);color:#0081ff;">Beginner</span>
                    </div>
                </div>
                <!-- Noones -->
                <div class="os-card">
                    <div class="os-card-icon" style="background:rgba(255,140,0,.12);color:#ff8c00;"><i class="bi bi-people-fill"></i></div>
                    <div class="os-card-body">
                        <div class="os-card-name">Noones</div>
                        <div class="os-card-desc">Nigeria-popular P2P platform. Buy direct from local traders.</div>
                        <span class="os-card-tag" style="background:rgba(255,140,0,.12);color:#ff8c00;">Nigeria</span>
                    </div>
                </div>
            </div>

            <div class="os-manual-note">
                <i class="bi bi-clock-history"></i>
                <div>
                    <strong style="color:var(--kx-text);">Our process is 100% manual.</strong> Once you place an order here, our team processes it by hand &#8212; verifying your details, sourcing the crypto, and sending it to your provided wallet. This typically takes <strong style="color:var(--kx-green);">15 &#8211; 60 minutes</strong> depending on network conditions. You can use any external platform above to buy crypto independently, but if you choose to buy through us, rest assured we take our time to ensure every order is handled carefully and delivered safely.
                </div>
            </div>
        </div>
    </div>
    <!-- /other sources -->

</div>
</div>
</div><!-- /container -->
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bitcoin-address-validation@2.2.3/dist/index.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/ethers@5.7.2/dist/ethers.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tronweb@5.3.2/dist/TronWeb.min.js"></script>
<script>
(function() {
    // DOM refs
    const amountInput      = document.getElementById('amountInput');
    const inputLabelEl     = document.getElementById('inputLabel');
    const coinSelect       = document.getElementById('coin');
    const rateValue        = document.getElementById('rateValue');
    const rateBadge        = document.getElementById('rateBadge');
    const nextButton       = document.getElementById('nextButton');
    const submitButton     = document.getElementById('submitButton');
    const submitButtonText = document.getElementById('submitButtonText');
    const submitSpinner    = document.getElementById('submitSpinner');
    const form             = document.getElementById('buyForm');
    const step1            = document.getElementById('step1');
    const step2            = document.getElementById('step2');
    const selectedCoinInput = document.getElementById('selectedCoinInput');
    const networkSelect    = document.getElementById('network');
    const inputType        = document.getElementById('inputType');
    const walletInput      = document.getElementById('wallet_address');
    const conversionBox    = document.getElementById('conversionBox');
    const convertedLabel   = document.getElementById('convertedLabel');
    const convertedAmountEl = document.getElementById('convertedAmount');
    const step2CoinDisplay  = document.getElementById('step2CoinDisplay');
    const step2AmountDisplay = document.getElementById('step2AmountDisplay');
    const progressStep1    = document.getElementById('progressStep1');
    const progressStep2    = document.getElementById('progressStep2');
    const circleStep2      = document.getElementById('circleStep2');

    let isUSD = true;
    let selectedCoin = '';

    const rates = {!! json_encode($rates ?? ['BTC' => 1600, 'ETH' => 1500, 'USDT' => 1400]) !!};

    const networks = {
        BTC:  [{ value: 'Bitcoin',  text: 'Bitcoin Network'  }],
        ETH:  [{ value: 'Ethereum', text: 'Ethereum Network' }],
        USDT: [{ value: 'Tron',     text: 'Tron Network'     }]
    };

    const fallbackPatterns = {
        BTC:  { Bitcoin:  /^(1|3|bc1)[A-Za-z0-9]{25,74}$/ },
        ETH:  { Ethereum: /^0x[a-fA-F0-9]{40}$/ },
        USDT: { Tron:     /^T[A-Za-z0-9]{33}$/ }
    };

    const coinColors = { BTC: '#f7931a', ETH: '#627eea', USDT: '#26a17b' };
    const coinLabels = { BTC: 'Bitcoin (BTC)', ETH: 'Ethereum (ETH)', USDT: 'USDT-TRC20' };

    // Toast
    function showToast(msg, type) {
        const t = document.getElementById('kxToast');
        t.textContent = msg;
        t.className = 'kx-toast ' + type;
        t.style.display = 'block';
        setTimeout(() => { t.style.display = 'none'; }, 3500);
    }

    // Log errors silently
    async function logErr(msg, details) {
        try {
            await fetch('/log-error', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: msg, details: details })
            });
        } catch(e) {}
    }

    // Wallet validation
    async function validateWallet(address, coin, network) {
        if (!address) return { valid: false };
        address = address.trim();
        const lib = {
            bitcoin: typeof bitcoinAddressValidation !== 'undefined',
            ethers:  typeof ethers !== 'undefined',
            tronweb: typeof TronWeb !== 'undefined'
        };
        try {
            if (coin === 'BTC' && network === 'Bitcoin') {
                if (lib.bitcoin) {
                    const v = bitcoinAddressValidation(address);
                    if (!v.valid) throw new Error('Invalid Bitcoin address');
                    return { valid: true };
                }
                throw new Error('bitcoin-address-validation not loaded');
            } else if (coin === 'ETH' && network === 'Ethereum') {
                if (lib.ethers) {
                    if (!ethers.utils.isAddress(address)) throw new Error('Invalid Ethereum address');
                    return { valid: true };
                }
                throw new Error('ethers not loaded');
            } else if (coin === 'USDT' && network === 'Tron') {
                if (lib.tronweb) {
                    const tw = new TronWeb({ fullHost: 'https://api.trongrid.io' });
                    if (!await tw.isAddress(address)) throw new Error('Invalid Tron address');
                    return { valid: true };
                }
                throw new Error('tronweb not loaded');
            }
        } catch(err) {
            logErr('Wallet validation error', { coin, network, address, error: err.message });
            if (fallbackPatterns[coin]?.[network]?.test(address)) return { valid: true };
        }
        return { valid: false };
    }

    // Select coin
    window.selectCoin = function(coin) {
        ['BTC','ETH','USDT'].forEach(c => {
            document.getElementById('coinCard' + c).classList.remove('selected-btc','selected-eth','selected-usdt');
        });
        const cls = { BTC: 'selected-btc', ETH: 'selected-eth', USDT: 'selected-usdt' };
        document.getElementById('coinCard' + coin).classList.add(cls[coin]);
        coinSelect.value = coin;
        selectedCoin = coin;
        updateRate();
        // re-evaluate next button
        const val = parseFloat(amountInput.value) || 0;
        nextButton.disabled = !val || (isUSD ? val < 10 : val < 14000);
    };

    function updateRate() {
        const coin = coinSelect.value;
        if (coin && rates[coin]) {
            rateValue.textContent = '\u20A6' + parseFloat(rates[coin]).toLocaleString('en-NG') + '/USD';
            rateBadge.classList.remove('d-none');
            calculateConversion();
        } else {
            rateBadge.classList.add('d-none');
            conversionBox.classList.remove('visible');
        }
    }

    function calculateConversion() {
        const amount = parseFloat(amountInput.value) || 0;
        const coin   = coinSelect.value;
        if (!coin || !rates[coin] || !amount) {
            conversionBox.classList.remove('visible');
            return;
        }
        const rate = rates[coin];
        if (isUSD) {
            if (amount < 10) { conversionBox.classList.remove('visible'); return; }
            convertedLabel.textContent = "You'll Pay (\u20A6)";
            convertedAmountEl.textContent = '\u20A6' + (amount * rate).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            if (amount < 14000) { conversionBox.classList.remove('visible'); return; }
            convertedLabel.textContent = "You'll Receive (USD)";
            convertedAmountEl.textContent = '$' + (amount / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        conversionBox.classList.add('visible');
        nextButton.disabled = false;
    }

    // Toggle USD/NGN
    document.getElementById('toggleCurrency').addEventListener('click', function() {
        if (!selectedCoin) { showToast('Please select a coin first!', 'error'); return; }
        const amount = parseFloat(amountInput.value) || 0;
        const rate   = rates[selectedCoin] || 1;
        isUSD = !isUSD;
        inputType.value = isUSD ? 'usd' : 'naira';
        if (isUSD) {
            inputLabelEl.textContent = 'Amount in USD';
            amountInput.step = '0.01'; amountInput.min = '10';
            amountInput.placeholder = 'Enter amount in USD';
            amountInput.value = amount >= 14000 ? (amount / rate).toFixed(2) : '';
        } else {
            inputLabelEl.textContent = 'Amount in Naira (\u20A6)';
            amountInput.step = '1'; amountInput.min = '14000';
            amountInput.placeholder = 'Enter amount in Naira';
            amountInput.value = amount >= 10 ? Math.round(amount * rate) : '';
        }
        calculateConversion();
    });

    function updateNetworkOptions() {
        networkSelect.innerHTML = '<option value="">Select Network</option>';
        if (networks[selectedCoin]) {
            networks[selectedCoin].forEach(function(n) {
                const opt = document.createElement('option');
                opt.value = n.value;
                opt.textContent = n.text;
                networkSelect.appendChild(opt);
            });
            if (networks[selectedCoin].length === 1) {
                networkSelect.selectedIndex = 1;
            }
        }
    }

    // Go to step 2
    window.goToStep2 = function() {
        const amount = parseFloat(amountInput.value) || 0;
        if (!selectedCoin) { showToast('Please select a cryptocurrency!', 'error'); return; }
        if (!amount || (isUSD && amount < 10) || (!isUSD && amount < 14000)) {
            showToast('Please enter a valid amount (min $10 or \u20A614,000).', 'error');
            amountInput.classList.add('border-danger');
            return;
        }
        amountInput.classList.remove('border-danger');
        selectedCoinInput.value = selectedCoin;

        step2CoinDisplay.innerHTML = '<span style="color:' + coinColors[selectedCoin] + '">' + (coinLabels[selectedCoin] || selectedCoin) + '</span>';
        if (isUSD) {
            step2AmountDisplay.textContent = '$' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            step2AmountDisplay.textContent = '\u20A6' + amount.toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        updateNetworkOptions();

        progressStep1.classList.add('done');
        progressStep1.classList.remove('active');
        progressStep2.classList.add('active');

        step1.classList.add('d-none');
        step2.classList.remove('d-none');
    };

    // Go back to step 1
    window.goToStep1 = function() {
        step2.classList.add('d-none');
        step1.classList.remove('d-none');
        progressStep1.classList.remove('done');
        progressStep1.classList.add('active');
        progressStep2.classList.remove('active');
        networkSelect.innerHTML = '<option value="">Select Network</option>';
    };

    // Next button
    nextButton.addEventListener('click', goToStep2);

    // Amount input
    amountInput.addEventListener('input', function() {
        if (this.value < 0) this.value = '';
        const val = parseFloat(this.value) || 0;
        nextButton.disabled = !val || !selectedCoin || (isUSD ? val < 10 : val < 14000);
        calculateConversion();
    });

    // Wallet real-time validation
    walletInput.addEventListener('input', async function() {
        const addr = this.value.trim();
        const net  = networkSelect.value;
        if (addr && selectedCoin && net) {
            const v = await validateWallet(addr, selectedCoin, net);
            this.classList.toggle('border-danger', !v.valid);
        } else {
            this.classList.remove('border-danger');
        }
    });

    // Form submit
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (submitButton.disabled) return;

        const wallet  = walletInput.value.trim();
        const network = networkSelect.value;

        if (!network) {
            showToast('Please select a network!', 'error');
            networkSelect.classList.add('border-danger');
            return;
        }
        if (!wallet) {
            showToast('Please enter your wallet address!', 'error');
            walletInput.classList.add('border-danger');
            return;
        }

        walletInput.classList.remove('border-danger');
        networkSelect.classList.remove('border-danger');

        // Async validate (silent — server will catch invalid)
        validateWallet(wallet, selectedCoin, network).then(function(v) {
            if (!v.valid) logErr('Client wallet validation failed', { coin: selectedCoin, network, address: wallet });
        });

        submitButton.disabled = true;
        submitButtonText.textContent = 'Processing...';
        submitSpinner.classList.remove('d-none');
        setTimeout(function() { form.submit(); }, 500);
    });

    // Init
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showToast(@json(session('success')), 'success');
        @elseif(session('error'))
            showToast(@json(session('error')), 'error');
        @endif
        var errs = @json($errors->all());
        if (errs.length) errs.forEach(function(e) { showToast(e, 'error'); });
    });
})();
</script>
@endpush
