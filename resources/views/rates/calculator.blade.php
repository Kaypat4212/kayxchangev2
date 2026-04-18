@extends('layout')

@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.07);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
}
body { background: var(--kx-dark); color: var(--kx-text); }

.calc-wrap { padding: 40px 0 80px; }
.calc-hero {
    text-align: center;
    padding: 40px 0 32px;
}
.calc-hero-icon {
    width: 68px; height: 68px; border-radius: 20px;
    background: rgba(0,204,0,0.1); border: 1px solid rgba(0,204,0,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; color: var(--kx-green);
    margin: 0 auto 18px;
}
.calc-hero h1 { font-size: clamp(1.5rem,4vw,2.2rem); font-weight: 800; color: #fff; margin-bottom: 8px; }
.calc-hero p  { color: var(--kx-muted); font-size: .9rem; }

/* Mode tabs */
.calc-tabs {
    display: flex; background: var(--kx-card); border: 1px solid var(--kx-border);
    border-radius: 14px; padding: 5px; margin-bottom: 24px;
}
.calc-tab {
    flex: 1; padding: .65rem 1rem; text-align: center;
    font-size: .88rem; font-weight: 700; border-radius: 10px;
    cursor: pointer; transition: all .2s; color: var(--kx-muted); border: none; background: none;
}
.calc-tab.active { background: var(--kx-card2); color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
.calc-tab.buy.active  { color: #60a5fa; }
.calc-tab.sell.active { color: var(--kx-green); }

/* Card */
.calc-card {
    background: var(--kx-card); border: 1px solid var(--kx-border);
    border-radius: 20px; padding: 28px; margin-bottom: 20px;
    position: relative; overflow: hidden;
}
.calc-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
.calc-card.buy-mode::before  { background: #3b82f6; }
.calc-card.sell-mode::before { background: var(--kx-green); }

/* Coin selector */
.kx-coin-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .75rem; margin-bottom: 24px; }
.kx-coin-btn {
    background: var(--kx-card2); border: 2px solid var(--kx-border);
    border-radius: 14px; padding: .9rem .5rem;
    text-align: center; cursor: pointer; transition: all .2s; user-select: none;
}
.kx-coin-btn:hover { border-color: rgba(0,204,0,.3); background: rgba(0,204,0,.04); }
.kx-coin-btn.active {
    border-color: var(--kx-green); background: rgba(0,204,0,.08);
    box-shadow: 0 0 0 3px rgba(0,204,0,.12);
}
.buy-mode .kx-coin-btn.active { border-color: #3b82f6; background: rgba(59,130,246,.08); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.coin-icon-w { width: 40px; height: 40px; border-radius: 50%; margin: 0 auto .45rem; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; }
.icon-btc  { background: rgba(247,147,26,.15); color: #f7931a; }
.icon-eth  { background: rgba(98,126,234,.15); color: #627eea; }
.icon-usdt { background: rgba(38,161,123,.15); color: #26a17b; }
.coin-name-lbl { font-size: .82rem; font-weight: 700; color: #fff; }
.coin-rate-lbl { font-size: .7rem; color: var(--kx-muted); margin-top: .15rem; transition: color .2s; }
.kx-coin-btn.active .coin-rate-lbl { color: var(--kx-green); }
.buy-mode .kx-coin-btn.active .coin-rate-lbl { color: #60a5fa; }

/* Amount type toggle */
.amt-toggle {
    display: flex; background: var(--kx-card2); border: 1px solid var(--kx-border);
    border-radius: 10px; overflow: hidden; margin-bottom: 14px;
}
.amt-opt {
    flex: 1; padding: .55rem; text-align: center; font-size: .82rem; font-weight: 600;
    color: var(--kx-muted); cursor: pointer; transition: all .2s; border: none; background: none;
}
.amt-opt.active { background: rgba(0,204,0,.1); color: #00cc00; border-bottom: 2px solid #00cc00; }
.buy-mode .amt-opt.active { background: rgba(59,130,246,.1); color: #60a5fa; border-bottom-color: #3b82f6; }

/* Input */
.calc-input-wrap { position: relative; margin-bottom: 6px; }
.calc-prefix {
    position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
    font-size: 1.1rem; font-weight: 700; color: var(--kx-green); pointer-events: none;
}
.buy-mode .calc-prefix { color: #60a5fa; }
.calc-input {
    width: 100%; background: var(--kx-card2); border: 1.5px solid var(--kx-border);
    border-radius: 14px; color: #fff; font-size: 1.4rem; font-weight: 700;
    padding: .85rem 1rem .85rem 2.4rem;
    transition: border-color .2s, box-shadow .2s; outline: none;
}
.calc-input:focus { border-color: var(--kx-green); box-shadow: 0 0 0 3px rgba(0,204,0,.12); }
.buy-mode .calc-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.calc-input::placeholder { color: var(--kx-muted); font-weight: 400; font-size: 1rem; }

/* Result box */
.calc-result {
    background: rgba(0,204,0,.04); border: 1px solid rgba(0,204,0,.12);
    border-radius: 14px; padding: 1.1rem 1.4rem; margin-top: 16px;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    flex-wrap: wrap;
}
.buy-mode .calc-result { background: rgba(59,130,246,.04); border-color: rgba(59,130,246,.12); }
.calc-res-row { display: flex; flex-direction: column; gap: 3px; }
.calc-res-lbl { font-size: .7rem; font-weight: 600; color: var(--kx-muted); text-transform: uppercase; letter-spacing: .5px; }
.calc-res-val { font-size: 1.15rem; font-weight: 800; color: #fff; font-family: monospace; }
.calc-res-val.green { color: var(--kx-green); }
.calc-res-val.blue  { color: #60a5fa; }
.calc-arrow { font-size: 1.3rem; color: var(--kx-muted); }

.calc-rate-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    background: var(--kx-card2); border: 1px solid var(--kx-border);
    border-radius: 20px; padding: .3rem .9rem; font-size: .75rem;
    color: var(--kx-muted); margin-top: 14px;
}
.calc-rate-pill strong { color: #fff; }

/* CTA buttons */
.calc-cta-row { display: flex; gap: .75rem; margin-top: 22px; flex-wrap: wrap; }
.calc-cta {
    flex: 1; border: none; border-radius: 12px; padding: .9rem 1rem;
    font-size: .9rem; font-weight: 700; cursor: pointer;
    transition: all .22s; display: flex; align-items: center; justify-content: center; gap: .5rem;
    min-width: 130px; text-decoration: none;
}
.calc-cta.sell-cta {
    background: linear-gradient(135deg,#00cc00,#007a0c); color: #fff;
    box-shadow: 0 4px 18px rgba(0,204,0,.3);
}
.calc-cta.sell-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 26px rgba(0,204,0,.42); color: #fff; }
.calc-cta.buy-cta {
    background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: #fff;
    box-shadow: 0 4px 18px rgba(59,130,246,.3);
}
.calc-cta.buy-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 26px rgba(59,130,246,.4); color: #fff; }

/* Rates table card */
.rates-card {
    background: var(--kx-card); border: 1px solid var(--kx-border);
    border-radius: 20px; overflow: hidden; margin-top: 8px;
}
.rates-card-hd {
    padding: 16px 22px; border-bottom: 1px solid var(--kx-border);
    display: flex; align-items: center; gap: 8px;
    font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    color: var(--kx-muted);
}
.rates-table { width: 100%; border-collapse: collapse; }
.rates-table th {
    padding: 10px 22px; font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: var(--kx-muted); border-bottom: 1px solid var(--kx-border);
    text-align: left;
}
.rates-table td { padding: 14px 22px; border-bottom: 1px solid rgba(255,255,255,.04); }
.rates-table tbody tr:last-child td { border-bottom: none; }
.rates-table tbody tr:hover td { background: rgba(255,255,255,.02); }
.coin-cell { display: flex; align-items: center; gap: .75rem; }
.coin-cell-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .9rem; font-weight: 800; }
.buy-rate-val  { color: #60a5fa; font-weight: 700; font-family: monospace; }
.sell-rate-val { color: var(--kx-green); font-weight: 700; font-family: monospace; }
.spread-val { color: var(--kx-muted); font-size: .82rem; font-family: monospace; }

/* live dot */
.live-dot {
    width: 8px; height: 8px; border-radius: 50%; background: var(--kx-green);
    animation: pulse 1.5s infinite;
}
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.4;} }

@media (max-width: 576px) {
    .kx-coin-grid { grid-template-columns: repeat(3,1fr); }
    .calc-hero h1 { font-size: 1.4rem; }
    .calc-input { font-size: 1.1rem; }
    .calc-res-val { font-size: 1rem; }
}
</style>
@endpush

@section('content')
<div class="calc-wrap">
<div class="container">
<div class="row justify-content-center">
<div class="col-xl-8 col-lg-9">

    {{-- Hero --}}
    <div class="calc-hero">
        <div class="calc-hero-icon"><i class="bi bi-calculator-fill"></i></div>
        <h1>Rate Calculator</h1>
        <p>Instantly calculate how much you'll get — before you trade.</p>
    </div>

    {{-- Mode tabs --}}
    <div class="calc-tabs" id="calcTabs">
        <button class="calc-tab sell active" data-mode="sell" onclick="setMode('sell')">
            <i class="bi bi-arrow-up-circle-fill me-1"></i>Sell Crypto
        </button>
        <button class="calc-tab buy" data-mode="buy" onclick="setMode('buy')">
            <i class="bi bi-arrow-down-circle-fill me-1"></i>Buy Crypto
        </button>
    </div>

    {{-- Main calculator card --}}
    <div class="calc-card sell-mode" id="calcCard">

        {{-- Coin selector --}}
        <div class="kx-coin-grid" id="coinGrid">
            @foreach(['BTC' => ['Bitcoin','icon-btc','BTC'], 'ETH' => ['Ethereum','icon-eth','ETH'], 'USDT' => ['Tether','icon-usdt','₮']] as $symbol => [$name, $iconCls, $symIcon])
            @php $r = $rates[$symbol] ?? null; @endphp
            <div class="kx-coin-btn {{ $loop->first ? 'active' : '' }}"
                 id="coin-btn-{{ $symbol }}"
                 onclick="selectCoin('{{ $symbol }}')"
                 data-coin="{{ $symbol }}">
                <div class="coin-icon-w {{ $iconCls }}">{{ $symIcon }}</div>
                <div class="coin-name-lbl">{{ $symbol }}</div>
                <div class="coin-rate-lbl" id="coin-rate-{{ $symbol }}">
                    @if($r)₦{{ number_format($r->sell_rate, 0) }}/USD@else—@endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Amount type toggle --}}
        <div class="amt-toggle" id="amtToggle">
            <button class="amt-opt active" onclick="setAmtType('usd')" id="opt-usd">USD Amount</button>
            <button class="amt-opt" onclick="setAmtType('ngn')" id="opt-ngn">NGN Amount</button>
        </div>

        {{-- Input --}}
        <div class="calc-input-wrap">
            <span class="calc-prefix" id="calcPrefix">$</span>
            <input type="number"
                   id="calcAmt"
                   class="calc-input"
                   placeholder="0.00"
                   min="0"
                   step="0.01"
                   oninput="calculate()">
        </div>
        <div style="font-size:.72rem;color:var(--kx-muted);margin-bottom:4px;" id="calcHint">Enter amount in USD</div>

        {{-- Result --}}
        <div class="calc-result" id="calcResult">
            <div class="calc-res-row">
                <div class="calc-res-lbl">You Send</div>
                <div class="calc-res-val" id="resSend">—</div>
            </div>
            <div class="calc-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="calc-res-row">
                <div class="calc-res-lbl" id="resRecLabel">You Receive (₦)</div>
                <div class="calc-res-val green" id="resReceive">—</div>
            </div>
        </div>

        <div class="calc-rate-pill" id="ratePill">
            <span class="live-dot"></span>
            Rate: <strong id="ratePillVal">—</strong>
        </div>

        {{-- CTA --}}
        <div class="calc-cta-row" id="ctaRow">
            <a href="{{ url('/sell') }}" class="calc-cta sell-cta" id="ctaBtn">
                <i class="bi bi-arrow-up-circle-fill"></i>Start Selling
            </a>
        </div>

    </div>

    {{-- All rates table --}}
    <div class="rates-card">
        <div class="rates-card-hd">
            <div class="live-dot"></div>
            Current Exchange Rates
            <span style="margin-left:auto;font-size:.72rem;color:var(--kx-muted);" id="lastRefreshed">—</span>
        </div>
        <div class="table-responsive">
            <table class="rates-table">
                <thead>
                    <tr>
                        <th>Coin</th>
                        <th><i class="bi bi-arrow-down-circle me-1" style="color:#60a5fa"></i>Buy Rate</th>
                        <th><i class="bi bi-arrow-up-circle me-1" style="color:#00cc00"></i>Sell Rate</th>
                        <th>Spread</th>
                    </tr>
                </thead>
                <tbody id="ratesTableBody">
                    @foreach($rates as $symbol => $r)
                    @php
                        $icons = ['BTC'=>['icon-btc','#f7931a','BTC'], 'ETH'=>['icon-eth','#627eea','ETH'], 'USDT'=>['icon-usdt','#26a17b','₮']];
                        [$ic, $clr, $sym] = $icons[$symbol] ?? ['','#aaa',$symbol];
                    @endphp
                    <tr>
                        <td>
                            <div class="coin-cell">
                                <div class="coin-cell-icon {{ $ic }}" style="font-size:.8rem;">{{ $sym }}</div>
                                <div>
                                    <div style="font-weight:700;color:#fff;font-size:.88rem;">{{ $symbol }}</div>
                                    <div style="font-size:.7rem;color:var(--kx-muted);">
                                        @if($symbol==='BTC') Bitcoin @elseif($symbol==='ETH') Ethereum @else Tether @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><span class="buy-rate-val">₦{{ number_format($r->buy_rate, 2) }}</span></td>
                        <td><span class="sell-rate-val">₦{{ number_format($r->sell_rate, 2) }}</span></td>
                        <td><span class="spread-val">₦{{ number_format(abs($r->buy_rate - $r->sell_rate), 2) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    // ── State ──
    let currentMode = 'sell';  // sell | buy
    let currentCoin = 'BTC';
    let amtType = 'usd';       // usd | ngn

    const rates = @json($rates->map(fn($r) => ['buy_rate' => (float)$r->buy_rate, 'sell_rate' => (float)$r->sell_rate]));

    // ── Set mode ──
    window.setMode = function(mode) {
        currentMode = mode;
        document.getElementById('calcCard').className = 'calc-card ' + mode + '-mode';
        document.querySelectorAll('.calc-tab').forEach(t => t.classList.toggle('active', t.dataset.mode === mode));

        // Update CTA
        const cta = document.getElementById('ctaBtn');
        if (mode === 'sell') {
            cta.href   = '{{ url("/sell") }}';
            cta.innerHTML = '<i class="bi bi-arrow-up-circle-fill"></i>Start Selling';
            cta.className = 'calc-cta sell-cta';
        } else {
            cta.href   = '{{ url("/buy") }}';
            cta.innerHTML = '<i class="bi bi-arrow-down-circle-fill"></i>Start Buying';
            cta.className = 'calc-cta buy-cta';
        }

        updateCoinRateLabels();
        calculate();
    };

    // ── Select coin ──
    window.selectCoin = function(coin) {
        currentCoin = coin;
        document.querySelectorAll('.kx-coin-btn').forEach(b => b.classList.toggle('active', b.dataset.coin === coin));
        calculate();
    };

    // ── Amount type ──
    window.setAmtType = function(type) {
        amtType = type;
        document.getElementById('opt-usd').classList.toggle('active', type === 'usd');
        document.getElementById('opt-ngn').classList.toggle('active', type === 'ngn');
        document.getElementById('calcPrefix').textContent = type === 'usd' ? '$' : '₦';
        document.getElementById('calcHint').textContent   = type === 'usd' ? 'Enter amount in USD' : 'Enter amount in Naira (₦)';
        calculate();
    };

    // ── Core calculation ──
    window.calculate = function() {
        const raw     = parseFloat(document.getElementById('calcAmt').value) || 0;
        const rateRow = rates[currentCoin];
        if (!rateRow) { clear(); return; }

        const rate = currentMode === 'sell' ? rateRow.sell_rate : rateRow.buy_rate;
        if (!rate) { clear(); return; }

        let sendAmt, recvAmt, sendLabel, recvLabel;

        if (amtType === 'usd') {
            sendAmt  = raw;
            recvAmt  = raw * rate;
            sendLabel = '$' + fmt(sendAmt, 2) + ' USD';
            recvLabel = '₦' + fmt(recvAmt, 0);
        } else {
            sendAmt  = raw;
            recvAmt  = raw / rate;   // NGN → USD
            sendLabel = '₦' + fmt(sendAmt, 0);
            recvLabel = '$' + fmt(recvAmt, 2) + ' USD';
        }

        const colorCls = currentMode === 'sell' ? 'green' : 'blue';
        document.getElementById('resSend').textContent    = raw > 0 ? sendLabel : '—';
        document.getElementById('resReceive').textContent = raw > 0 ? recvLabel : '—';
        document.getElementById('resReceive').className   = 'calc-res-val ' + colorCls;
        document.getElementById('resRecLabel').textContent = amtType === 'usd' ? 'You Receive (₦)' : 'Equivalent (USD)';
        document.getElementById('ratePillVal').textContent = '₦' + fmt(rate, 0) + ' / USD  ·  ' + currentCoin;
    };

    function clear() {
        document.getElementById('resSend').textContent    = '—';
        document.getElementById('resReceive').textContent = '—';
        document.getElementById('ratePillVal').textContent = '—';
    }

    function fmt(n, dec) {
        return Number(n).toLocaleString('en-NG', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }

    function updateCoinRateLabels() {
        Object.keys(rates).forEach(function(coin) {
            const el = document.getElementById('coin-rate-' + coin);
            if(!el) return;
            const r = rates[coin];
            const v = currentMode === 'sell' ? r.sell_rate : r.buy_rate;
            el.textContent = '₦' + fmt(v, 0) + '/USD';
        });
    }

    // ── Live rate refresh every 60s ──
    function refreshRates() {
        fetch('{{ route("calc.rates") }}')
            .then(r => r.json())
            .then(data => {
                Object.assign(rates, data);
                updateCoinRateLabels();
                calculate();
                // Update table
                document.getElementById('lastRefreshed').textContent = 'Updated ' + new Date().toLocaleTimeString();
            })
            .catch(() => {});
    }
    setInterval(refreshRates, 60000);
    document.getElementById('lastRefreshed').textContent = 'Updated ' + new Date().toLocaleTimeString();

    // Init
    calculate();
})();
</script>
@endpush
