@extends('layout')

@section('title', 'Digital tools | KayXchange')

@push('styles')
<style>
.kx-tools{--t-bg:#0b120e;--t-card:#111a16;--t-line:rgba(255,255,255,.09);--t-muted:#91a198;--t-green:#12d45a;color:#eaf4ed;padding:clamp(18px,4vw,42px) 0 72px}
.kx-tools a{text-decoration:none}.kx-tools-hero{position:relative;overflow:hidden;padding:clamp(24px,5vw,48px);border:1px solid rgba(18,212,90,.2);border-radius:26px;background:radial-gradient(ellipse at 85% 0,rgba(18,212,90,.17),transparent 40%),linear-gradient(130deg,#15251b,#0c1310 72%)}
.kx-tools-hero:after{content:'₿';position:absolute;right:5%;top:-30%;font-size:clamp(9rem,24vw,18rem);font-weight:900;color:rgba(18,212,90,.055);pointer-events:none}.kx-tools-kicker{color:#65e993;font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.kx-tools-hero h1{font-size:clamp(1.8rem,4vw,2.8rem);letter-spacing:-.045em;font-weight:850;margin:.65rem 0}.kx-tools-hero p{color:#adbbb2;max-width:640px;margin:0;line-height:1.7}.kx-tools-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:16px;margin-top:20px}.kx-tool-card{grid-column:span 4;min-width:0;padding:20px;border:1px solid var(--t-line);border-radius:19px;background:linear-gradient(145deg,rgba(255,255,255,.045),rgba(255,255,255,.018));box-shadow:0 12px 32px rgba(0,0,0,.13)}.kx-tool-card.wide{grid-column:span 8}.kx-tool-heading{display:flex;align-items:center;gap:12px;margin-bottom:14px}.kx-tool-icon{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;border-radius:14px;color:#6ef29a;background:rgba(18,212,90,.11);border:1px solid rgba(18,212,90,.17);font-size:1.1rem}.kx-tool-heading h2{font-size:1rem;font-weight:780;margin:0}.kx-tool-heading p{color:var(--t-muted);font-size:.74rem;margin:3px 0 0}.kx-tool-card label{display:block;color:#b8c8bd;font-size:.74rem;font-weight:650;margin:11px 0 6px}.kx-tool-card input,.kx-tool-card select{width:100%;min-height:44px;padding:10px 12px;border-radius:11px;border:1px solid var(--t-line);outline:none;background:#0b120f;color:#f0f7f2}.kx-tool-card input:focus,.kx-tool-card select:focus{border-color:rgba(18,212,90,.65);box-shadow:0 0 0 3px rgba(18,212,90,.1)}.kx-tool-result{margin-top:13px;border:1px solid rgba(18,212,90,.18);border-radius:13px;padding:13px;background:rgba(18,212,90,.055)}.kx-tool-result small{display:block;color:var(--t-muted);font-size:.67rem;text-transform:uppercase;letter-spacing:.08em}.kx-tool-result strong{display:block;margin-top:3px;color:#76f29e;font-size:1.2rem;overflow-wrap:anywhere}.kx-tool-hint{color:var(--t-muted);font-size:.7rem;line-height:1.55;margin:9px 0 0}.kx-tool-link{display:inline-flex;align-items:center;gap:7px;margin-top:13px;color:#70ed98;font-size:.76rem;font-weight:700}.kx-tool-link:hover{color:#b2ffc8}.kx-tool-rates{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-top:12px}.kx-tool-rate{border:1px solid var(--t-line);border-radius:12px;padding:10px 11px;background:#ffffff04}.kx-tool-rate b{display:block;font-size:.79rem}.kx-tool-rate span{display:block;color:#a7b6ad;font-size:.68rem;margin-top:4px}.kx-tools-status{font-size:.68rem;color:var(--t-muted);margin-top:10px}.kx-tools-shortcuts{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px}.kx-tools-shortcuts a{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border-radius:999px;border:1px solid var(--t-line);background:#ffffff05;color:#dce9df;font-size:.72rem;font-weight:650}.kx-tools-shortcuts a:hover{border-color:rgba(18,212,90,.4);color:#77f39c}
.kx-dev-head{display:flex;align-items:end;justify-content:space-between;gap:14px;margin:34px 0 14px}.kx-dev-head h2{font-size:1.3rem;font-weight:820;margin:5px 0}.kx-dev-head p{font-size:.76rem;color:var(--t-muted);margin:0}.kx-dev-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}.kx-dev-card{grid-column:span 6;min-width:0;padding:18px;border:1px solid var(--t-line);border-radius:18px;background:linear-gradient(145deg,rgba(255,255,255,.045),rgba(255,255,255,.018))}.kx-dev-card.wide{grid-column:span 12}.kx-dev-card textarea,.kx-dev-card input{width:100%;min-height:42px;padding:10px 12px;border-radius:11px;border:1px solid var(--t-line);outline:none;background:#0b120f;color:#f0f7f2;font: .82rem/1.5 ui-monospace,SFMono-Regular,Consolas,monospace}.kx-dev-card textarea{min-height:124px;resize:vertical}.kx-dev-card textarea:focus,.kx-dev-card input:focus{border-color:rgba(18,212,90,.65);box-shadow:0 0 0 3px rgba(18,212,90,.1)}.kx-dev-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.kx-dev-actions button,.kx-dev-copy{border:1px solid rgba(18,212,90,.25);border-radius:10px;padding:8px 12px;background:rgba(18,212,90,.1);color:#8af4aa;font-weight:700;font-size:.75rem}.kx-dev-actions button:hover,.kx-dev-copy:hover{background:rgba(18,212,90,.2)}.kx-dev-output{white-space:pre-wrap;overflow-wrap:anywhere;color:#dce9df;font: .78rem/1.55 ui-monospace,SFMono-Regular,Consolas,monospace;max-height:260px;overflow:auto}.kx-dev-row{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center}.kx-dev-note{font-size:.68rem;color:var(--t-muted);margin:8px 0 0}.kx-dev-status{font-size:.73rem;color:#75ef9a;margin-top:9px;min-height:1em}
@media(max-width:900px){.kx-tool-card{grid-column:span 6}.kx-tool-card.wide{grid-column:span 12}}@media(max-width:600px){.kx-tools{padding-top:14px}.kx-tools-hero{border-radius:20px}.kx-tool-card,.kx-tool-card.wide{grid-column:span 12;padding:17px}.kx-tools-grid{gap:12px}.kx-tool-rates{grid-template-columns:1fr 1fr}.kx-dev-card,.kx-dev-card.wide{grid-column:span 12;padding:15px}.kx-dev-head{display:block}}
</style>
@endpush

@section('content')
<main class="container kx-tools">
    <header class="kx-tools-hero">
        <div class="kx-tools-kicker"><i class="bi bi-stars me-1"></i>KayXchange workspace</div>
        <h1>Useful tools, all in one place</h1>
        <p>Use KayXchange’s live crypto calculators and a growing set of built-in tools for coding, data formatting, and everyday digital work.</p>
        <nav class="kx-tools-shortcuts" aria-label="Quick links">
            <a href="{{ route('calc.index') }}"><i class="bi bi-calculator"></i>Full rate calculator</a>
            <a href="{{ route('portfolio.index') }}"><i class="bi bi-pie-chart"></i>Portfolio</a>
            <a href="{{ route('wallet.history') }}"><i class="bi bi-clock-history"></i>Wallet history</a>
            <a href="{{ route('transactions.history') }}"><i class="bi bi-receipt"></i>Transactions</a>
        </nav>
    </header>

    <section class="kx-tools-grid" aria-label="Digital tools">
        <article class="kx-tool-card wide">
            <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-arrow-left-right"></i></span><div><h2>Crypto ↔ Naira estimator</h2><p>Uses current KayXchange trade rates</p></div></div>
            <label for="kx-estimator-side">Estimate</label>
            <select id="kx-estimator-side"><option value="buy">Naira needed to buy crypto</option><option value="sell">Naira value when selling crypto</option></select>
            <div class="row g-2"><div class="col-sm-6"><label for="kx-estimator-coin">Crypto asset</label><select id="kx-estimator-coin"><option value="USDT">USDT</option></select></div><div class="col-sm-6"><label for="kx-estimator-amount" id="kx-estimator-amount-label">Naira amount (₦)</label><input id="kx-estimator-amount" type="number" min="0" step="any" inputmode="decimal" placeholder="e.g. 50000"></div></div>
            <div class="kx-tool-result" aria-live="polite"><small id="kx-estimator-caption">Estimated crypto</small><strong id="kx-estimator-output">Enter an amount</strong></div>
            <p class="kx-tool-hint">Estimates only. The amount shown at confirmation may reflect limits, fees, network, and the latest rate.</p>
            <div id="kx-estimator-rate" class="kx-tools-status">Loading platform rates…</div>
        </article>

        <article class="kx-tool-card">
            <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-percent"></i></span><div><h2>Profit / loss checker</h2><p>Compare entry and current value</p></div></div>
            <label for="kx-entry">Amount invested (₦)</label><input id="kx-entry" type="number" min="0" step="any" inputmode="decimal" placeholder="e.g. 100000">
            <label for="kx-current">Current value (₦)</label><input id="kx-current" type="number" min="0" step="any" inputmode="decimal" placeholder="e.g. 125000">
            <div class="kx-tool-result" aria-live="polite"><small>Change</small><strong id="kx-pl-output">Enter both values</strong><span id="kx-pl-percent" class="kx-tools-status"></span></div>
            <p class="kx-tool-hint">This is a simple estimate and does not include fees, taxes, or exchange spreads.</p>
        </article>

        <article class="kx-tool-card">
            <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-layers"></i></span><div><h2>Average entry calculator</h2><p>Find a weighted average for buys</p></div></div>
            <label for="kx-buy-one">First buy · units and price (₦)</label><div class="row g-2"><div class="col-6"><input id="kx-buy-one-units" type="number" min="0" step="any" placeholder="Units" aria-label="First buy units"></div><div class="col-6"><input id="kx-buy-one-price" type="number" min="0" step="any" placeholder="Price" aria-label="First buy price"></div></div>
            <label for="kx-buy-two-units">Second buy · units and price (₦)</label><div class="row g-2"><div class="col-6"><input id="kx-buy-two-units" type="number" min="0" step="any" placeholder="Units" aria-label="Second buy units"></div><div class="col-6"><input id="kx-buy-two-price" type="number" min="0" step="any" placeholder="Price" aria-label="Second buy price"></div></div>
            <div class="kx-tool-result" aria-live="polite"><small>Weighted average entry price</small><strong id="kx-average-output">Enter both buys</strong></div>
        </article>

        <article class="kx-tool-card">
            <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-graph-up-arrow"></i></span><div><h2>Live platform rates</h2><p>NGN per USD unit, refreshed automatically</p></div></div>
            <div id="kx-tools-rates" class="kx-tool-rates"><div class="kx-tool-status">Loading rates…</div></div>
            <div id="kx-tools-updated" class="kx-tools-status" aria-live="polite"></div>
            <a class="kx-tool-link" href="{{ route('rates.crypto') }}">View all rates <i class="bi bi-arrow-up-right"></i></a>
        </article>

        <article class="kx-tool-card">
            <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-currency-bitcoin"></i></span><div><h2>Crypto spot prices</h2><p>Indicative market prices in USD</p></div></div>
            <div id="kx-tools-market" class="kx-tool-rates"><div class="kx-tools-status">Loading market prices…</div></div>
            <div id="kx-tools-market-updated" class="kx-tools-status" aria-live="polite"></div>
            <p class="kx-tool-hint">Market prices are indicative and may be delayed. Your KayXchange trade quote uses the platform rate shown above.</p>
        </article>

        <article class="kx-tool-card wide">
            <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-shield-check"></i></span><div><h2>Account and wallet shortcuts</h2><p>Open the right page for common account tasks</p></div></div>
            <div class="kx-tools-shortcuts">
                <a href="{{ route('wallet.deposit') }}"><i class="bi bi-download"></i>Deposit crypto</a>
                <a href="{{ route('wallet.withdraw') }}"><i class="bi bi-upload"></i>Withdraw</a>
                <a href="{{ route('wallet.usd') }}"><i class="bi bi-currency-dollar"></i>USD crypto wallet</a>
                <a href="{{ route('calc.index') }}"><i class="bi bi-currency-exchange"></i>Rate calculator</a>
                <a href="{{ route('price-alerts.index') }}"><i class="bi bi-bell"></i>Price alerts</a>
                <a href="{{ route('support.chat') }}"><i class="bi bi-chat-dots"></i>Get support</a>
            </div>
            <p class="kx-tool-hint">Keep your transaction PIN and wallet recovery information private. KayXchange support will never ask you to send your PIN or seed phrase in chat.</p>
        </article>
    </section>
    <section aria-labelledby="kx-dev-tools-title">
        <header class="kx-dev-head"><div><div class="kx-tools-kicker">Built into KayXchange</div><h2 id="kx-dev-tools-title">Developer & everyday web tools</h2><p>Use them here without creating another account. Inputs stay in your browser and are not sent to KayXchange.</p></div></header>
        <div class="kx-dev-grid">
            <article class="kx-dev-card wide"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-braces"></i></span><div><h2>JSON formatter & validator</h2><p>Format, compact, or validate JSON locally</p></div></div><label for="kx-json-input">JSON input</label><textarea id="kx-json-input" spellcheck="false" placeholder='{"name":"KayXchange","active":true}'></textarea><div class="kx-dev-actions"><button type="button" data-json-action="pretty">Format</button><button type="button" data-json-action="minify">Minify</button><button type="button" data-json-action="validate">Validate</button><button type="button" class="kx-dev-copy" data-copy-target="kx-json-input">Copy input</button></div><div class="kx-dev-status" id="kx-json-status" aria-live="polite"></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-arrow-repeat"></i></span><div><h2>Text encoder</h2><p>Base64 and URL encode or decode</p></div></div><label for="kx-encode-input">Text</label><textarea id="kx-encode-input" spellcheck="false" placeholder="Enter text to transform"></textarea><label for="kx-encode-mode">Transformation</label><select id="kx-encode-mode"><option value="base64-encode">Base64 encode</option><option value="base64-decode">Base64 decode</option><option value="url-encode">URL encode</option><option value="url-decode">URL decode</option></select><div class="kx-dev-actions"><button type="button" id="kx-encode-run">Transform</button><button type="button" class="kx-dev-copy" data-copy-target="kx-encode-output">Copy result</button></div><div class="kx-tool-result kx-dev-output" id="kx-encode-output" aria-live="polite">Result appears here</div><div class="kx-dev-status" id="kx-encode-status"></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-key"></i></span><div><h2>Password generator</h2><p>Cryptographically secure random passwords</p></div></div><label for="kx-password-length">Length (8–128)</label><input id="kx-password-length" type="number" min="8" max="128" value="20"><label><input id="kx-password-symbols" type="checkbox" checked style="width:auto;min-height:auto;margin-right:7px">Include symbols</label><div class="kx-dev-actions"><button type="button" id="kx-password-generate">Generate password</button><button type="button" class="kx-dev-copy" data-copy-target="kx-password-output">Copy</button></div><div class="kx-tool-result kx-dev-output" id="kx-password-output" aria-live="polite">Generate a password</div><p class="kx-dev-note">Generated locally with your browser’s secure random number generator. Save it in a trusted password manager.</p></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-hash"></i></span><div><h2>SHA-256 hash</h2><p>Hash text locally with Web Crypto</p></div></div><label for="kx-hash-input">Text to hash</label><textarea id="kx-hash-input" placeholder="Enter text"></textarea><div class="kx-dev-actions"><button type="button" id="kx-hash-run">Create SHA-256</button><button type="button" class="kx-dev-copy" data-copy-target="kx-hash-output">Copy hash</button></div><div class="kx-tool-result kx-dev-output" id="kx-hash-output" aria-live="polite">Hash appears here</div><div class="kx-dev-status" id="kx-hash-status"></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-regex"></i></span><div><h2>Regex tester</h2><p>Test a JavaScript regular expression</p></div></div><label for="kx-regex-pattern">Pattern</label><input id="kx-regex-pattern" placeholder="e.g. ^[A-Z]{2,5}$"><div class="row g-2"><div class="col-4"><label for="kx-regex-flags">Flags</label><input id="kx-regex-flags" value="g" maxlength="6" placeholder="gim"></div><div class="col-8"><label for="kx-regex-text">Test text</label><input id="kx-regex-text" placeholder="Enter test text"></div></div><div class="kx-tool-result kx-dev-output" id="kx-regex-output" aria-live="polite">Matches appear here</div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-fingerprint"></i></span><div><h2>UUID generator</h2><p>Create a random version 4 identifier</p></div></div><div class="kx-dev-actions"><button type="button" id="kx-uuid-generate">Generate UUID</button><button type="button" class="kx-dev-copy" data-copy-target="kx-uuid-output">Copy</button></div><div class="kx-tool-result kx-dev-output" id="kx-uuid-output" aria-live="polite">UUID appears here</div></article>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
(() => {
    const ratesUrl = @json(route('calc.rates'));
    const marketUrl = @json(route('calc.market-prices'));
    const $ = (id) => document.getElementById(id);
    const money = (value, currency = 'NGN') => new Intl.NumberFormat('en-NG', {style:'currency',currency,maximumFractionDigits:2}).format(value);
    let rates = {};
    const paintEstimate = () => {
        const side = $('kx-estimator-side').value;
        const coin = $('kx-estimator-coin').value;
        const amount = Number($('kx-estimator-amount').value);
        const row = rates[coin] || {};
        const rate = Number(side === 'buy' ? row.sell_rate : row.buy_rate);
        $('kx-estimator-amount-label').textContent = side === 'buy' ? 'Naira amount (₦)' : `Crypto amount (${coin})`;
        $('kx-estimator-caption').textContent = side === 'buy' ? 'Estimated crypto' : 'Estimated Naira proceeds';
        if (!(amount > 0) || !(rate > 0)) { $('kx-estimator-output').textContent = 'Enter an amount'; $('kx-estimator-rate').textContent = rate > 0 ? `Platform rate: ${money(rate)} per ${coin}` : 'Rate unavailable right now'; return; }
        $('kx-estimator-output').textContent = side === 'buy' ? `${(amount / rate).toLocaleString('en-US',{maximumFractionDigits:8})} ${coin}` : money(amount * rate);
        $('kx-estimator-rate').textContent = `Platform rate: ${money(rate)} per ${coin}`;
    };
    const loadMarket = async () => {
        try {
            const response = await fetch(marketUrl, {headers:{Accept:'application/json'},cache:'no-store'});
            if (!response.ok) throw new Error('Market prices unavailable');
            const data = await response.json();
            const coins = (data.coins || []).slice(0,8);
            $('kx-tools-market').innerHTML = coins.map((coin) => {
                const change = Number(coin.change_24h);
                const changeLabel = Number.isFinite(change) ? `${change >= 0 ? '+' : ''}${change.toFixed(2)}%` : '—';
                return `<div class="kx-tool-rate"><b>${String(coin.symbol).replace(/[&<>"']/g,'')} <span style="float:right;color:${change >= 0 ? '#76f29e' : '#ff8585'}">${changeLabel}</span></b><span>$${Number(coin.price_usd).toLocaleString('en-US',{maximumFractionDigits:6})} USD</span></div>`;
            }).join('') || '<div class="kx-tools-status">Market data is temporarily unavailable.</div>';
            $('kx-tools-market-updated').textContent = data.updated_at ? `Provider snapshot: ${new Date(data.updated_at).toLocaleTimeString('en-NG')}${data.stale ? ' · cached' : ''}` : 'Provider snapshot unavailable';
        } catch (_) { $('kx-tools-market').innerHTML = '<div class="kx-tools-status">Market prices could not be loaded right now.</div>'; }
    };
    const loadRates = async () => {
        try {
            const response = await fetch(ratesUrl, {headers:{Accept:'application/json'},cache:'no-store'});
            if (!response.ok) throw new Error('Rates unavailable');
            rates = await response.json();
            const coins = Object.keys(rates).filter((coin) => rates[coin] && (Number(rates[coin].buy_rate) > 0 || Number(rates[coin].sell_rate) > 0));
            if (coins.length) $('kx-estimator-coin').innerHTML = coins.map((coin) => `<option value="${coin.replace(/[&<>"']/g,'')}">${coin.replace(/[&<>"']/g,'')}</option>`).join('');
            const selected = coins;
            $('kx-tools-rates').innerHTML = selected.map((coin) => `<div class="kx-tool-rate"><b>${coin}</b><span>Buy: ${money(Number(rates[coin].sell_rate)||0)} · Sell: ${money(Number(rates[coin].buy_rate)||0)}</span></div>`).join('') || '<div class="kx-tools-status">No active rates available.</div>';
            $('kx-tools-updated').textContent = `Updated ${new Date().toLocaleTimeString('en-NG')} · Auto-refreshes every minute`;
            paintEstimate();
        } catch (_) { $('kx-tools-rates').innerHTML = '<div class="kx-tools-status">Could not load live rates. Try the full rate page.</div>'; $('kx-estimator-rate').textContent = 'Could not load platform rates'; }
    };
    ['kx-estimator-side','kx-estimator-coin','kx-estimator-amount'].forEach((id) => $(id).addEventListener('input', paintEstimate));
    ['kx-entry','kx-current'].forEach((id) => $(id).addEventListener('input', () => {
        const entry = Number($('kx-entry').value), current = Number($('kx-current').value);
        if (!(entry > 0) || !(current >= 0)) { $('kx-pl-output').textContent = 'Enter both values'; $('kx-pl-percent').textContent = ''; return; }
        const delta = current - entry; $('kx-pl-output').textContent = `${delta >= 0 ? '+' : '−'}${money(Math.abs(delta))}`;
        $('kx-pl-percent').textContent = `${delta >= 0 ? '+' : ''}${((delta / entry) * 100).toFixed(2)}%`;
        $('kx-pl-output').style.color = delta >= 0 ? '#76f29e' : '#ff8585';
    }));
    ['kx-buy-one-units','kx-buy-one-price','kx-buy-two-units','kx-buy-two-price'].forEach((id) => $(id).addEventListener('input', () => {
        const q1=Number($('kx-buy-one-units').value), p1=Number($('kx-buy-one-price').value), q2=Number($('kx-buy-two-units').value), p2=Number($('kx-buy-two-price').value), total=q1+q2;
        $('kx-average-output').textContent = q1>0 && p1>0 && q2>0 && p2>0 ? money(((q1*p1)+(q2*p2))/total) : 'Enter both buys';
    }));
    const setStatus = (id, message, isError = false) => { $(id).textContent = message; $(id).style.color = isError ? '#ff8585' : '#75ef9a'; };
    document.querySelectorAll('[data-json-action]').forEach((button) => button.addEventListener('click', () => {
        try {
            const parsed = JSON.parse($('kx-json-input').value);
            const action = button.dataset.jsonAction;
            if (action !== 'validate') $('kx-json-input').value = JSON.stringify(parsed, null, action === 'pretty' ? 2 : 0);
            setStatus('kx-json-status', action === 'validate' ? 'Valid JSON ✓' : `JSON ${action === 'pretty' ? 'formatted' : 'minified'} ✓`);
        } catch (error) { setStatus('kx-json-status', `Invalid JSON: ${error.message}`, true); }
    }));
    $('kx-encode-run').addEventListener('click', () => {
        try {
            const input = $('kx-encode-input').value, mode = $('kx-encode-mode').value;
            let result;
            if (mode === 'url-encode') result = encodeURIComponent(input);
            else if (mode === 'url-decode') result = decodeURIComponent(input);
            else if (mode === 'base64-encode') result = btoa(unescape(encodeURIComponent(input)));
            else result = decodeURIComponent(escape(atob(input.trim())));
            $('kx-encode-output').textContent = result || '(empty result)'; setStatus('kx-encode-status', 'Done · processed in this browser');
        } catch (error) { $('kx-encode-output').textContent = 'Could not transform this input.'; setStatus('kx-encode-status', error.message, true); }
    });
    const readTarget = (id) => { const el = $(id); return 'value' in el ? el.value : el.textContent; };
    document.querySelectorAll('[data-copy-target]').forEach((button) => button.addEventListener('click', async () => {
        const value = readTarget(button.dataset.copyTarget);
        if (!value || /appears here|Generate a password/i.test(value)) return;
        try { await navigator.clipboard.writeText(value); const old = button.textContent; button.textContent = 'Copied ✓'; window.setTimeout(() => button.textContent = old, 1400); }
        catch (_) { button.textContent = 'Copy unavailable'; }
    }));
    $('kx-password-generate').addEventListener('click', () => {
        const length = Math.max(8, Math.min(128, Number($('kx-password-length').value) || 20));
        $('kx-password-length').value = length;
        const alphabet = `ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789${$('kx-password-symbols').checked ? '!@#$%^&*()-_=+[]{}?' : ''}`;
        const ceiling = Math.floor(256 / alphabet.length) * alphabet.length, out = [];
        while (out.length < length) { const bytes = new Uint8Array(Math.max(16, length - out.length)); crypto.getRandomValues(bytes); for (const byte of bytes) if (byte < ceiling && out.length < length) out.push(alphabet[byte % alphabet.length]); }
        $('kx-password-output').textContent = out.join('');
    });
    $('kx-hash-run').addEventListener('click', async () => {
        try { const digest = await crypto.subtle.digest('SHA-256', new TextEncoder().encode($('kx-hash-input').value)); $('kx-hash-output').textContent = [...new Uint8Array(digest)].map((byte) => byte.toString(16).padStart(2,'0')).join(''); setStatus('kx-hash-status', 'SHA-256 created locally ✓'); }
        catch (_) { setStatus('kx-hash-status', 'SHA-256 is not available in this browser context.', true); }
    });
    const paintRegex = () => {
        try {
            const flags = $('kx-regex-flags').value;
            const regex = new RegExp($('kx-regex-pattern').value, flags.includes('g') ? flags : `${flags}g`);
            regex.lastIndex = 0;
            const text = $('kx-regex-text').value, matches = [...text.matchAll(regex)].slice(0,100);
            $('kx-regex-output').textContent = matches.length ? `${matches.length}${matches.length === 100 ? '+' : ''} match(es):\n${matches.map((match, index) => `${index + 1}. “${match[0]}” at position ${match.index}`).join('\n')}` : 'No matches';
        } catch (error) { $('kx-regex-output').textContent = `Regex error: ${error.message}`; }
    };
    ['kx-regex-pattern','kx-regex-flags','kx-regex-text'].forEach((id) => $(id).addEventListener('input', paintRegex));
    $('kx-uuid-generate').addEventListener('click', () => {
        const uuid = crypto.randomUUID ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => { const r = crypto.getRandomValues(new Uint8Array(1))[0] & 15; return (c === 'x' ? r : (r & 3 | 8)).toString(16); });
        $('kx-uuid-output').textContent = uuid;
    });
    loadRates(); loadMarket(); window.setInterval(loadRates,60000); window.setInterval(loadMarket,60000);
})();
</script>
@endpush
