@extends('layout')

@section('title', 'Digital tools | KayXchange')

@push('styles')
<style>
.kx-tools{--t-bg:#0b120e;--t-card:#111a16;--t-line:rgba(255,255,255,.09);--t-muted:#91a198;--t-green:#12d45a;color:#eaf4ed;padding:clamp(18px,4vw,42px) 0 72px}
.kx-tools a{text-decoration:none}.kx-tools-hero{position:relative;overflow:hidden;padding:clamp(24px,5vw,48px);border:1px solid rgba(18,212,90,.2);border-radius:26px;background:radial-gradient(ellipse at 85% 0,rgba(18,212,90,.17),transparent 40%),linear-gradient(130deg,#15251b,#0c1310 72%)}
.kx-tools-hero:after{content:'₿';position:absolute;right:5%;top:-30%;font-size:clamp(9rem,24vw,18rem);font-weight:900;color:rgba(18,212,90,.055);pointer-events:none}.kx-tools-kicker{color:#65e993;font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.kx-tools-hero h1{font-size:clamp(1.8rem,4vw,2.8rem);letter-spacing:-.045em;font-weight:850;margin:.65rem 0}.kx-tools-hero p{color:#adbbb2;max-width:640px;margin:0;line-height:1.7}.kx-tools-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:16px;margin-top:20px}.kx-tool-card{grid-column:span 4;min-width:0;padding:20px;border:1px solid var(--t-line);border-radius:19px;background:linear-gradient(145deg,rgba(255,255,255,.045),rgba(255,255,255,.018));box-shadow:0 12px 32px rgba(0,0,0,.13)}.kx-tool-card.wide{grid-column:span 8}.kx-tool-heading{display:flex;align-items:center;gap:12px;margin-bottom:14px}.kx-tool-icon{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;border-radius:14px;color:#6ef29a;background:rgba(18,212,90,.11);border:1px solid rgba(18,212,90,.17);font-size:1.1rem}.kx-tool-heading h2{font-size:1rem;font-weight:780;margin:0}.kx-tool-heading p{color:var(--t-muted);font-size:.74rem;margin:3px 0 0}.kx-tool-card label{display:block;color:#b8c8bd;font-size:.74rem;font-weight:650;margin:11px 0 6px}.kx-tool-card input,.kx-tool-card select{width:100%;min-height:44px;padding:10px 12px;border-radius:11px;border:1px solid var(--t-line);outline:none;background:#0b120f;color:#f0f7f2}.kx-tool-card input:focus,.kx-tool-card select:focus{border-color:rgba(18,212,90,.65);box-shadow:0 0 0 3px rgba(18,212,90,.1)}.kx-tool-result{margin-top:13px;border:1px solid rgba(18,212,90,.18);border-radius:13px;padding:13px;background:rgba(18,212,90,.055)}.kx-tool-result small{display:block;color:var(--t-muted);font-size:.67rem;text-transform:uppercase;letter-spacing:.08em}.kx-tool-result strong{display:block;margin-top:3px;color:#76f29e;font-size:1.2rem;overflow-wrap:anywhere}.kx-tool-hint{color:var(--t-muted);font-size:.7rem;line-height:1.55;margin:9px 0 0}.kx-tool-link{display:inline-flex;align-items:center;gap:7px;margin-top:13px;color:#70ed98;font-size:.76rem;font-weight:700}.kx-tool-link:hover{color:#b2ffc8}.kx-tool-rates{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-top:12px}.kx-tool-rate{border:1px solid var(--t-line);border-radius:12px;padding:10px 11px;background:#ffffff04}.kx-tool-rate b{display:block;font-size:.79rem}.kx-tool-rate span{display:block;color:#a7b6ad;font-size:.68rem;margin-top:4px}.kx-tools-status{font-size:.68rem;color:var(--t-muted);margin-top:10px}.kx-tools-shortcuts{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px}.kx-tools-shortcuts a{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border-radius:999px;border:1px solid var(--t-line);background:#ffffff05;color:#dce9df;font-size:.72rem;font-weight:650}.kx-tools-shortcuts a:hover{border-color:rgba(18,212,90,.4);color:#77f39c}
.kx-dev-head{display:flex;align-items:end;justify-content:space-between;gap:14px;margin:34px 0 14px}.kx-dev-head h2{font-size:1.3rem;font-weight:820;margin:5px 0}.kx-dev-head p{font-size:.76rem;color:var(--t-muted);margin:0}.kx-dev-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}.kx-dev-card{grid-column:span 6;min-width:0;padding:18px;border:1px solid var(--t-line);border-radius:18px;background:linear-gradient(145deg,rgba(255,255,255,.045),rgba(255,255,255,.018))}.kx-dev-card.wide{grid-column:span 12}.kx-dev-card textarea,.kx-dev-card input{width:100%;min-height:42px;padding:10px 12px;border-radius:11px;border:1px solid var(--t-line);outline:none;background:#0b120f;color:#f0f7f2;font: .82rem/1.5 ui-monospace,SFMono-Regular,Consolas,monospace}.kx-dev-card textarea{min-height:124px;resize:vertical}.kx-dev-card textarea:focus,.kx-dev-card input:focus{border-color:rgba(18,212,90,.65);box-shadow:0 0 0 3px rgba(18,212,90,.1)}.kx-dev-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.kx-dev-actions button,.kx-dev-copy{border:1px solid rgba(18,212,90,.25);border-radius:10px;padding:8px 12px;background:rgba(18,212,90,.1);color:#8af4aa;font-weight:700;font-size:.75rem}.kx-dev-actions button:hover,.kx-dev-copy:hover{background:rgba(18,212,90,.2)}.kx-dev-output{white-space:pre-wrap;overflow-wrap:anywhere;color:#dce9df;font: .78rem/1.55 ui-monospace,SFMono-Regular,Consolas,monospace;max-height:260px;overflow:auto}.kx-dev-row{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center}.kx-dev-note{font-size:.68rem;color:var(--t-muted);margin:8px 0 0}.kx-dev-status{font-size:.73rem;color:#75ef9a;margin-top:9px;min-height:1em}
.kx-tools-hero{padding:24px 0 20px;border:0;border-bottom:1px solid rgba(255,255,255,.12);border-radius:0;background:none}.kx-tools-hero:after{display:none}.kx-tools-kicker{font-size:.67rem;letter-spacing:.08em;color:#9aa79f}.kx-tools-hero h1{font-size:clamp(1.65rem,3vw,2.2rem);font-weight:720;letter-spacing:-.025em}.kx-tools-hero p{font-size:.9rem;color:#aab5ae}.kx-tools-shortcuts{margin-top:14px}.kx-tools-shortcuts a{border-radius:7px;padding:7px 10px;background:transparent}.kx-tool-card,.kx-dev-card{border-radius:10px;background:#101714;box-shadow:none}.kx-tool-icon{width:34px;height:34px;flex-basis:34px;border-radius:7px;border-color:transparent;background:rgba(18,212,90,.08);font-size:.95rem}.kx-tool-heading{gap:10px;margin-bottom:12px}.kx-tool-heading h2{font-size:.94rem;font-weight:700}.kx-tool-heading p,.kx-dev-head p{font-size:.75rem}.kx-tools-grid,.kx-dev-grid{gap:10px}.kx-tool-card{padding:16px}.kx-dev-card{padding:16px}.kx-dev-head{margin:28px 0 12px}.kx-dev-head h2{font-size:1.1rem;font-weight:720}.kx-dev-card textarea,.kx-dev-card input,.kx-tool-card input,.kx-tool-card select{border-radius:7px;background:#0c110f}.kx-dev-card textarea:focus,.kx-dev-card input:focus,.kx-tool-card input:focus,.kx-tool-card select:focus{border-color:rgba(18,212,90,.48);box-shadow:0 0 0 2px rgba(18,212,90,.08)}.kx-dev-actions button,.kx-dev-copy{border-radius:7px;background:#17231b;color:#b9e9c7;border-color:#26392c}.kx-dev-actions button:hover,.kx-dev-copy:hover{background:#203328}.kx-tool-result{border-radius:7px;background:#0e1711;border-color:#1b3022}.kx-tool-result strong{color:#a3e8b6}.kx-tool-rates{gap:6px}.kx-tool-rate{border-radius:7px;background:transparent}.kx-tools-shortcuts a:hover{border-color:#304638;color:#b9e9c7}
.kx-markdown-preview{min-height:124px;padding:14px;border:1px solid var(--t-line);border-radius:7px;background:#0c110f;color:#e5ede7;font-size:.84rem;line-height:1.65;overflow-wrap:anywhere}.kx-markdown-preview h1,.kx-markdown-preview h2,.kx-markdown-preview h3{font-size:1.1rem;margin:.6em 0 .35em}.kx-markdown-preview p{margin:.35em 0}.kx-markdown-preview pre{padding:10px;background:#080b09;border-radius:5px;overflow:auto}.kx-markdown-preview code{color:#b9e9c7}.kx-markdown-preview a{color:#9bc9a8;text-decoration:underline}.kx-diff-output{margin-top:12px;min-height:48px;max-height:300px;overflow:auto;padding:12px;border:1px solid var(--t-line);border-radius:7px;background:#0b100d;color:#dbe5dd;white-space:pre-wrap;overflow-wrap:anywhere;font: .78rem/1.6 ui-monospace,SFMono-Regular,Consolas,monospace}.kx-contrast-controls{display:grid;grid-template-columns:1fr 1fr;gap:12px}.kx-contrast-controls label{display:grid;gap:5px;color:#b8c8bd;font-size:.74rem}.kx-contrast-controls input[type=color]{width:100%;height:42px;padding:4px;border-radius:6px;border:1px solid var(--t-line);background:#0c110f}.kx-contrast-preview{margin-top:12px;padding:18px;border-radius:7px;border:1px solid var(--t-line);font-size:1.1rem;font-weight:700;text-align:center}.kx-text-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-top:12px}.kx-text-stats div{padding:10px 7px;border-top:1px solid var(--t-line)}.kx-text-stats strong,.kx-text-stats span{display:block}.kx-text-stats strong{font-size:1rem;color:#dce9df}.kx-text-stats span{font-size:.64rem;color:var(--t-muted)}
.kx-dev-card select{appearance:none;-webkit-appearance:none;display:block;width:100%;min-height:46px;padding:11px 42px 11px 13px;border:1px solid #344239;border-radius:8px;background-color:#121a15;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%2395aa9b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.7' d='m3 6 5 5 5-5'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;color:#e6eee8;font:600 .84rem/1.2 inherit;cursor:pointer;transition:border-color .15s,background-color .15s,box-shadow .15s}.kx-dev-card select:hover{border-color:#587060;background-color:#151f19}.kx-dev-card select:focus-visible{outline:2px solid #61d887;outline-offset:2px;border-color:#61d887;box-shadow:0 0 0 3px rgba(97,216,135,.12)}.kx-dev-card select option{background:#121a15;color:#e6eee8}.kx-dev-card select option:checked{background:#24402d;color:#b8f3c6}.kx-api-records{display:grid;gap:7px;margin-top:12px}.kx-api-record{padding:10px 11px;border:1px solid var(--t-line);border-radius:7px;background:#0c110f;overflow-wrap:anywhere}.kx-api-record b{display:block;color:#dce9df;font-size:.78rem}.kx-api-record span{display:block;margin-top:4px;color:#aab8ae;font-size:.72rem;line-height:1.5}.kx-api-status{min-height:1.1em;margin-top:9px;color:var(--t-muted);font-size:.72rem}
.kx-social-platforms{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:7px;margin-top:13px}.kx-social-platforms label{display:flex;align-items:center;gap:7px;margin:0;padding:9px;border:1px solid var(--t-line);border-radius:7px;background:#0c110f;color:#dce9df;font-size:.74rem;cursor:pointer}.kx-social-platforms input{width:15px;height:15px;min-height:0;margin:0;accent-color:#12d45a}.kx-social-results{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;margin-top:12px}.kx-social-results a{display:flex;justify-content:space-between;gap:8px;align-items:center;padding:10px 11px;border:1px solid var(--t-line);border-radius:7px;background:#0c110f;color:#dce9df;font-size:.76rem;text-decoration:none;overflow-wrap:anywhere}.kx-social-results a:hover{border-color:#40604a;color:#9ce3ad}.kx-social-results a span{color:#94a69a;font-size:.68rem}@media(max-width:700px){.kx-social-platforms{grid-template-columns:repeat(2,minmax(0,1fr))}.kx-social-results{grid-template-columns:1fr}}
@media(max-width:900px){.kx-tool-card{grid-column:span 6}.kx-tool-card.wide{grid-column:span 12}}@media(max-width:600px){.kx-tools{padding-top:14px}.kx-tools-hero{border-radius:20px}.kx-tool-card,.kx-tool-card.wide{grid-column:span 12;padding:17px}.kx-tools-grid{gap:12px}.kx-tool-rates{grid-template-columns:1fr 1fr}.kx-dev-card,.kx-dev-card.wide{grid-column:span 12;padding:15px}.kx-dev-head{display:block}}
.kx-api-record small{display:block;margin-top:4px;color:#aab8ae;font-size:.72rem;line-height:1.5}.kx-api-record a{display:block;color:#8feaa8;font-size:.8rem;font-weight:700}.kx-api-record a:hover{text-decoration:underline}
</style>
@endpush

@section('content')
<main class="container kx-tools">
    <header class="kx-tools-hero">
        <div class="kx-tools-kicker">KAYXCHANGE · WORKSPACE</div>
        <h1>Tools</h1>
        <p>Utilities for crypto rates, coding, and everyday web work. Text and code tools run in your browser.</p>
        <nav class="kx-tools-shortcuts" aria-label="Quick links">
            <a href="{{ route('calc.index') }}"><i class="bi bi-calculator"></i>Full rate calculator</a>
            <a href="{{ route('portfolio.index') }}"><i class="bi bi-pie-chart"></i>Portfolio</a>
            <a href="{{ route('wallet.history') }}"><i class="bi bi-clock-history"></i>Wallet history</a>
        </nav>
    </header>

    <section class="kx-tools-grid" aria-label="Digital tools">
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
    <section aria-labelledby="kx-toolkit-title">
        <header class="kx-dev-head"><div><div class="kx-tools-kicker">FREE TOOLS</div><h2 id="kx-toolkit-title">Everyday toolkit</h2><p>Quick checks for cards, currencies, accounts and text. Sensitive values stay in your browser.</p></div></header>
        <div class="kx-dev-grid">
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-credit-card-2-front"></i></span><div><h2>BIN checker</h2><p>Identify a card issuer from its BIN/IIN prefix</p></div></div><label for="kx-bin-input">BIN prefix · exactly 6–8 digits</label><input id="kx-bin-input" type="text" inputmode="numeric" autocomplete="off" minlength="6" maxlength="8" pattern="[0-9]{6,8}" title="Enter 6 to 8 digits only" placeholder="e.g. 45717360"><div class="kx-dev-actions"><button type="button" id="kx-bin-run">Check BIN</button></div><div id="kx-bin-output" class="kx-api-records" aria-live="polite"></div><div id="kx-bin-status" class="kx-api-status" aria-live="polite"></div><p class="kx-dev-note">Checks multiple international datasets, including Nigerian ranges where indexed. Enter 8 digits for a more specific match. Only this prefix is sent—never enter a full card number or CVV.</p></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-check2-square"></i></span><div><h2>Card number validator</h2><p>Run a local format and Luhn checksum check</p></div></div><label for="kx-card-input">Card number · 12–19 digits</label><input id="kx-card-input" type="password" inputmode="numeric" autocomplete="off" minlength="12" maxlength="19" pattern="[0-9]{12,19}" title="Enter 12 to 19 digits with no spaces or punctuation" placeholder="Digits are checked locally"><label style="display:flex;align-items:center;gap:7px"><input id="kx-card-show" type="checkbox" style="width:auto;min-height:auto">Show number while checking</label><div class="kx-dev-actions"><button type="button" id="kx-card-run">Validate locally</button><button type="button" class="kx-dev-copy" id="kx-card-clear">Clear</button></div><div id="kx-card-output" class="kx-api-status" role="status">No card data is sent or saved.</div><p class="kx-dev-note">Enter digits only. This cannot confirm that a card is genuine, active, funded, or usable. Avoid entering real payment details on shared devices.</p></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-currency-exchange"></i></span><div><h2>Currency converter</h2><p>Indicative reference rates from a public API</p></div></div><label for="kx-currency-amount">Amount</label><input id="kx-currency-amount" type="number" min="0.000001" step="any" value="1" inputmode="decimal"><div class="row g-2"><div class="col-6"><label for="kx-currency-from">From</label><select id="kx-currency-from"><option>USD</option><option>NGN</option><option>EUR</option><option>GBP</option><option>GHS</option><option>ZAR</option><option>CAD</option><option>AUD</option><option>JPY</option><option>INR</option><option>KES</option><option>BRL</option></select></div><div class="col-6"><label for="kx-currency-to">To</label><select id="kx-currency-to"><option>NGN</option><option>USD</option><option>EUR</option><option>GBP</option><option>GHS</option><option>ZAR</option><option>CAD</option><option>AUD</option><option>JPY</option><option>INR</option><option>KES</option><option>BRL</option></select></div></div><div class="kx-dev-actions"><button type="button" id="kx-currency-run">Convert</button></div><div class="kx-tool-result"><small>Estimated result</small><strong id="kx-currency-output">Enter an amount</strong></div><div id="kx-currency-status" class="kx-api-status" aria-live="polite"></div><p class="kx-dev-note">Indicative reference rate; not KayXchange’s trade rate or a payment quote.</p></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-hash"></i></span><div><h2>Password hashing</h2><p>Derive a salted hash locally with PBKDF2</p></div></div><label for="kx-pwhash-input">Password or test string</label><input id="kx-pwhash-input" type="password" autocomplete="new-password" placeholder="Enter sample text"><label for="kx-pwhash-iterations">Iterations</label><select id="kx-pwhash-iterations"><option value="100000">100,000 · faster</option><option value="310000" selected>310,000 · recommended</option><option value="600000">600,000 · slower</option></select><div class="kx-dev-actions"><button type="button" id="kx-pwhash-run">Create salted hash</button><button type="button" class="kx-dev-copy" data-copy-target="kx-pwhash-output">Copy hash</button></div><div id="kx-pwhash-output" class="kx-tool-result kx-dev-output" aria-live="polite">Hash appears here</div><p class="kx-dev-note">Uses Web Crypto PBKDF2-SHA-256 and a fresh random salt. This demo does not store the password; production apps should hash passwords server-side.</p></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-envelope-check"></i></span><div><h2>SMTP connection tester</h2><p>Check your SMTP login securely without sending an email</p></div></div><label for="kx-smtp-host">SMTP host</label><input id="kx-smtp-host" autocomplete="off" placeholder="smtp.example.com"><div class="row g-2"><div class="col-6"><label for="kx-smtp-port">Port</label><select id="kx-smtp-port"><option value="587">587 · STARTTLS</option><option value="465">465 · Implicit TLS</option><option value="25">25 · STARTTLS</option><option value="2525">2525 · STARTTLS</option></select></div><div class="col-6"><label for="kx-smtp-encryption">Encryption</label><select id="kx-smtp-encryption"><option value="tls">STARTTLS</option><option value="ssl">Implicit TLS</option></select></div></div><label for="kx-smtp-email">SMTP email / username</label><input id="kx-smtp-email" type="email" autocomplete="username" maxlength="254" placeholder="you@example.com"><label for="kx-smtp-password">SMTP password or app password</label><input id="kx-smtp-password" type="password" autocomplete="new-password" maxlength="1024" placeholder="Used only for this test"><div class="kx-dev-actions"><button type="button" id="kx-smtp-run">Test SMTP login</button></div><div id="kx-smtp-output" class="kx-api-records" aria-live="polite"></div><div id="kx-smtp-status" class="kx-api-status" aria-live="polite"></div><p class="kx-dev-note"><strong>Private test:</strong> credentials are sent only over HTTPS, used to authenticate over TLS, and are not saved to the database, cache, or platform logs. No email is sent. Use your provider’s app password when required. Clear this page after testing.</p></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-person-check"></i></span><div><h2>Username checker</h2><p>Check a handle on GitHub or npm</p></div></div><label for="kx-user-service">Service</label><select id="kx-user-service"><option value="github">GitHub</option><option value="npm">npm</option></select><label for="kx-user-name">Username</label><input id="kx-user-name" autocomplete="off" autocapitalize="off" placeholder="Enter a username"><div class="kx-dev-actions"><button type="button" id="kx-user-run">Check username</button></div><div id="kx-user-output" class="kx-api-status" aria-live="polite">Checks public account availability on the selected service only.</div></article>
            <article class="kx-dev-card wide"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-search"></i></span><div><h2>Social username checker</h2><p>Build direct profile links using each platform’s username format</p></div></div><label for="kx-social-handle">Username or handle</label><input id="kx-social-handle" autocomplete="off" autocapitalize="off" placeholder="e.g. kay_trades or @kay_trades"><div class="kx-social-platforms" aria-label="Choose platforms to check">
                <label><input type="checkbox" value="instagram" checked> Instagram</label><label><input type="checkbox" value="tiktok" checked> TikTok</label><label><input type="checkbox" value="x" checked> X</label><label><input type="checkbox" value="facebook" checked> Facebook</label><label><input type="checkbox" value="threads" checked> Threads</label><label><input type="checkbox" value="youtube" checked> YouTube</label><label><input type="checkbox" value="linkedin" checked> LinkedIn</label><label><input type="checkbox" value="reddit" checked> Reddit</label><label><input type="checkbox" value="github" checked> GitHub</label><label><input type="checkbox" value="bluesky" checked> Bluesky</label>
            </div><div class="kx-dev-actions"><button type="button" id="kx-social-search">Build profile links</button></div><div id="kx-social-results" class="kx-social-results" aria-live="polite"></div><p class="kx-dev-note">Each link opens the actual profile URL. The platform may redirect, require sign-in, or hide unavailable accounts; this tool does not confirm account ownership or availability.</p></article>
            <article class="kx-dev-card wide"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-type"></i></span><div><h2>Invisible character inspector</h2><p>Find or remove zero-width and other invisible Unicode characters</p></div></div><label for="kx-invisible-input">Text to inspect</label><textarea id="kx-invisible-input" spellcheck="false" placeholder="Paste text that may contain hidden characters"></textarea><div class="kx-dev-actions"><button type="button" id="kx-invisible-scan">Inspect text</button><button type="button" id="kx-invisible-remove">Remove invisible characters</button><button type="button" class="kx-dev-copy" data-copy-target="kx-invisible-output">Copy cleaned text</button></div><div id="kx-invisible-output" class="kx-dev-output" aria-live="polite">Inspection results appear here</div><div id="kx-invisible-status" class="kx-api-status" aria-live="polite"></div></article>
        </div>
    </section>
    <section aria-labelledby="kx-api-tools-title">
        <header class="kx-dev-head"><div><div class="kx-tools-kicker">CONNECTED LOOKUPS</div><h2 id="kx-api-tools-title">Live web tools</h2><p>Fetches public DNS, weather, currency, and GitHub information. Lookup values are sent to the relevant providers.</p></div></header>
        <div class="kx-dev-grid">
            <article class="kx-dev-card">
                <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-globe2"></i></span><div><h2>DNS lookup</h2><p>Check public DNS records through Google DNS</p></div></div>
                <label for="kx-dns-name">Domain or hostname</label><input id="kx-dns-name" type="text" inputmode="url" autocomplete="url" placeholder="example.com">
                <label for="kx-dns-type">Record type</label><select id="kx-dns-type"><option value="A">A · IPv4 address</option><option value="AAAA">AAAA · IPv6 address</option><option value="MX">MX · Mail server</option><option value="TXT">TXT · Verification / policy</option><option value="NS">NS · Name server</option><option value="CNAME">CNAME · Alias</option><option value="PTR">PTR · Reverse name</option><option value="SRV">SRV · Service location</option><option value="SOA">SOA · Zone authority</option><option value="CAA">CAA · Certificate authority</option></select>
                <div class="kx-dev-actions"><button type="button" id="kx-dns-run">Look up records</button></div><div id="kx-dns-output" class="kx-api-records" aria-live="polite"></div><div id="kx-dns-status" class="kx-api-status" aria-live="polite"></div>
            </article>
            <article class="kx-dev-card">
                <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-cloud-sun"></i></span><div><h2>Weather & air quality</h2><p>Current conditions for a city</p></div></div>
                <label for="kx-weather-place">City or place</label><input id="kx-weather-place" type="text" maxlength="100" autocomplete="off" placeholder="Lagos, Nigeria">
                <p class="kx-dev-note">Powered by WeatherAPI.com. Location is sent to the provider. Requires the admin to save a RapidAPI key and enable the free WeatherAPI.com plan.</p>
                <div class="kx-dev-actions"><button type="button" id="kx-weather-run">Check weather</button></div><div id="kx-weather-output" class="kx-api-records" aria-live="polite"></div><div id="kx-weather-status" class="kx-api-status" aria-live="polite"></div>
            </article>
            <article class="kx-dev-card">
                <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-briefcase"></i></span><div><h2>Tech & remote jobs</h2><p>Search public job listings</p></div></div>
                <label for="kx-jobs-query">Role, skill, or location</label><input id="kx-jobs-query" type="text" maxlength="100" autocomplete="off" placeholder="Remote Laravel developer in Nigeria">
                <p class="kx-dev-note">Listings come from public job sites through JSearch. Search text is sent to the provider. Requires the admin to save a RapidAPI key and enable the free JSearch plan; free-plan requests may take several seconds.</p>
                <div class="kx-dev-actions"><button type="button" id="kx-jobs-run">Find jobs</button></div><div id="kx-jobs-output" class="kx-api-records" aria-live="polite"></div><div id="kx-jobs-status" class="kx-api-status" aria-live="polite"></div>
            </article>
            <article class="kx-dev-card">
                <div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-github"></i></span><div><h2>GitHub repository</h2><p>View public repository details and activity</p></div></div>
                <label for="kx-github-repo">Repository</label><input id="kx-github-repo" type="text" autocomplete="off" placeholder="laravel / framework">
                <p class="kx-dev-note">Enter an owner and repository, for example <code>laravel/framework</code>. Private repositories are not supported.</p>
                <div class="kx-dev-actions"><button type="button" id="kx-github-run">Fetch repository</button></div><div id="kx-github-output" class="kx-api-records" aria-live="polite"></div><div id="kx-github-status" class="kx-api-status" aria-live="polite"></div>
            </article>
        </div>
    </section>
    <section aria-labelledby="kx-dev-tools-title">
        <header class="kx-dev-head"><div><div class="kx-tools-kicker">BROWSER UTILITIES</div><h2 id="kx-dev-tools-title">Developer tools</h2><p>Runs locally in this tab; tool input is not sent to the server.</p></div></header>
        <div class="kx-dev-grid">
            <article class="kx-dev-card wide"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-braces"></i></span><div><h2>JSON formatter & validator</h2><p>Format, compact, or validate JSON locally</p></div></div><label for="kx-json-input">JSON input</label><textarea id="kx-json-input" spellcheck="false" placeholder='{"name":"KayXchange","active":true}'></textarea><div class="kx-dev-actions"><button type="button" data-json-action="pretty">Format</button><button type="button" data-json-action="minify">Minify</button><button type="button" data-json-action="validate">Validate</button><button type="button" class="kx-dev-copy" data-copy-target="kx-json-input">Copy input</button></div><div class="kx-dev-status" id="kx-json-status" aria-live="polite"></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-arrow-repeat"></i></span><div><h2>Text encoder</h2><p>Base64 and URL encode or decode</p></div></div><label for="kx-encode-input">Text</label><textarea id="kx-encode-input" spellcheck="false" placeholder="Enter text to transform"></textarea><label for="kx-encode-mode">Transformation</label><select id="kx-encode-mode"><option value="base64-encode">Base64 encode</option><option value="base64-decode">Base64 decode</option><option value="url-encode">URL encode</option><option value="url-decode">URL decode</option></select><div class="kx-dev-actions"><button type="button" id="kx-encode-run">Transform</button><button type="button" class="kx-dev-copy" data-copy-target="kx-encode-output">Copy result</button></div><div class="kx-tool-result kx-dev-output" id="kx-encode-output" aria-live="polite">Result appears here</div><div class="kx-dev-status" id="kx-encode-status"></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-key"></i></span><div><h2>Password generator</h2><p>Cryptographically secure random passwords</p></div></div><label for="kx-password-length">Length (8–128)</label><input id="kx-password-length" type="number" min="8" max="128" value="20"><label><input id="kx-password-symbols" type="checkbox" checked style="width:auto;min-height:auto;margin-right:7px">Include symbols</label><div class="kx-dev-actions"><button type="button" id="kx-password-generate">Generate password</button><button type="button" class="kx-dev-copy" data-copy-target="kx-password-output">Copy</button></div><div class="kx-tool-result kx-dev-output" id="kx-password-output" aria-live="polite">Generate a password</div><p class="kx-dev-note">Generated locally with your browser’s secure random number generator. Save it in a trusted password manager.</p></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-hash"></i></span><div><h2>SHA-256 hash</h2><p>Hash text locally with Web Crypto</p></div></div><label for="kx-hash-input">Text to hash</label><textarea id="kx-hash-input" placeholder="Enter text"></textarea><div class="kx-dev-actions"><button type="button" id="kx-hash-run">Create SHA-256</button><button type="button" class="kx-dev-copy" data-copy-target="kx-hash-output">Copy hash</button></div><div class="kx-tool-result kx-dev-output" id="kx-hash-output" aria-live="polite">Hash appears here</div><div class="kx-dev-status" id="kx-hash-status"></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-regex"></i></span><div><h2>Regex tester</h2><p>Test a JavaScript regular expression</p></div></div><label for="kx-regex-pattern">Pattern</label><input id="kx-regex-pattern" placeholder="e.g. ^[A-Z]{2,5}$"><div class="row g-2"><div class="col-4"><label for="kx-regex-flags">Flags</label><input id="kx-regex-flags" value="g" maxlength="6" placeholder="gim"></div><div class="col-8"><label for="kx-regex-text">Test text</label><input id="kx-regex-text" placeholder="Enter test text"></div></div><div class="kx-tool-result kx-dev-output" id="kx-regex-output" aria-live="polite">Matches appear here</div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-fingerprint"></i></span><div><h2>UUID generator</h2><p>Create a random version 4 identifier</p></div></div><div class="kx-dev-actions"><button type="button" id="kx-uuid-generate">Generate UUID</button><button type="button" class="kx-dev-copy" data-copy-target="kx-uuid-output">Copy</button></div><div class="kx-tool-result kx-dev-output" id="kx-uuid-output" aria-live="polite">UUID appears here</div></article>
            <article class="kx-dev-card wide"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-markdown"></i></span><div><h2>Markdown editor</h2><p>Write Markdown and preview the rendered result</p></div></div><div class="row g-3"><div class="col-lg-6"><label for="kx-markdown-input">Markdown</label><textarea id="kx-markdown-input" spellcheck="false" placeholder="# Notes&#10;Write **bold text**, lists, links, and code here."></textarea></div><div class="col-lg-6"><label>Preview</label><div class="kx-markdown-preview" id="kx-markdown-preview" aria-live="polite">Preview appears here</div></div></div></article>
            <article class="kx-dev-card wide"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-file-diff"></i></span><div><h2>Text diff</h2><p>Compare two versions line by line</p></div></div><div class="row g-3"><div class="col-md-6"><label for="kx-diff-left">Original</label><textarea id="kx-diff-left" spellcheck="false" placeholder="Paste the original text"></textarea></div><div class="col-md-6"><label for="kx-diff-right">Updated</label><textarea id="kx-diff-right" spellcheck="false" placeholder="Paste the updated text"></textarea></div></div><div class="kx-dev-actions"><button type="button" id="kx-diff-run">Compare</button></div><div class="kx-diff-output" id="kx-diff-output" aria-live="polite">Changes appear here</div><div class="kx-dev-status" id="kx-diff-status"></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-palette2"></i></span><div><h2>Contrast checker</h2><p>Check text contrast against WCAG guidance</p></div></div><div class="kx-contrast-controls"><label>Text color<input id="kx-contrast-fg" type="color" value="#f4f7f4" aria-label="Text color"></label><label>Background color<input id="kx-contrast-bg" type="color" value="#111a16" aria-label="Background color"></label></div><div class="kx-contrast-preview" id="kx-contrast-preview">Sample text · Aa 123</div><div class="kx-tool-result"><small>Contrast ratio</small><strong id="kx-contrast-ratio">—</strong><div id="kx-contrast-result" class="kx-tools-status"></div></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-text-paragraph"></i></span><div><h2>Text statistics</h2><p>Count words, characters, and lines</p></div></div><label for="kx-stats-input">Text</label><textarea id="kx-stats-input" placeholder="Paste or type text"></textarea><div class="kx-text-stats"><div><strong id="kx-stats-words">0</strong><span>Words</span></div><div><strong id="kx-stats-chars">0</strong><span>Characters</span></div><div><strong id="kx-stats-lines">0</strong><span>Lines</span></div><div><strong id="kx-stats-read">0 min</strong><span>Reading time</span></div></div></article>
            <article class="kx-dev-card"><div class="kx-tool-heading"><span class="kx-tool-icon"><i class="bi bi-clock"></i></span><div><h2>Timestamp converter</h2><p>Convert Unix seconds and milliseconds</p></div></div><label for="kx-timestamp-input">Unix timestamp</label><input id="kx-timestamp-input" inputmode="numeric" placeholder="e.g. 1792267200"><div class="kx-dev-actions"><button type="button" id="kx-timestamp-now">Use current time</button></div><div class="kx-tool-result"><small>Local time</small><strong id="kx-timestamp-local">Enter a timestamp</strong><small class="mt-2">UTC</small><strong id="kx-timestamp-utc">—</strong></div><label for="kx-datetime-input">Date and time to convert</label><input type="datetime-local" id="kx-datetime-input"><div class="kx-dev-actions"><button type="button" id="kx-datetime-convert">Get Unix timestamp</button></div><div class="kx-dev-output" id="kx-datetime-result"></div></article>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
(() => {
    const ratesUrl = @json(route('calc.rates'));
    const marketUrl = @json(route('calc.market-prices'));
    const toolApi = {
        bin: @json(route('tools.api.bin')),
        username: @json(route('tools.api.username')),
        dns: @json(route('tools.api.dns')),
        currency: @json(route('tools.api.currency')),
        weather: @json(route('tools.api.weather')),
        jobs: @json(route('tools.api.jobs')),
        github: @json(route('tools.api.github-repository')),
        smtp: @json(route('tools.api.smtp-probe')),
    };
    const $ = (id) => document.getElementById(id);
    const money = (value, currency = 'NGN') => new Intl.NumberFormat('en-NG', {style:'currency',currency,maximumFractionDigits:2}).format(value);
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
            const rates = await response.json();
            const coins = Object.keys(rates).filter((coin) => rates[coin] && (Number(rates[coin].buy_rate) > 0 || Number(rates[coin].sell_rate) > 0));
            const selected = coins;
            $('kx-tools-rates').innerHTML = selected.map((coin) => `<div class="kx-tool-rate"><b>${coin}</b><span>Buy: ${money(Number(rates[coin].buy_rate)||0)} · Sell: ${money(Number(rates[coin].sell_rate)||0)}</span></div>`).join('') || '<div class="kx-tools-status">No active rates available.</div>';
            $('kx-tools-updated').textContent = `Updated ${new Date().toLocaleTimeString('en-NG')} · Auto-refreshes every minute`;
        } catch (_) { $('kx-tools-rates').innerHTML = '<div class="kx-tools-status">Could not load live rates. Try the full rate page.</div>'; }
    };
    const setStatus = (id, message, isError = false) => { $(id).textContent = message; $(id).style.color = isError ? '#ff8585' : '#75ef9a'; };
    const setApiLoading = (button, active, label) => { button.disabled = active; button.textContent = active ? 'Loading…' : label; };
    const addApiRecord = (container, label, value) => { const row=document.createElement('div'), title=document.createElement('b'), detail=document.createElement('span'); row.className='kx-api-record'; title.textContent=label; detail.textContent=value || 'Not provided'; row.append(title,detail); container.append(row); };
    $('kx-bin-run').addEventListener('click', async (event) => {
        const button=event.currentTarget, bin=$('kx-bin-input').value, output=$('kx-bin-output'); output.replaceChildren();
        if(!/^[0-9]{6,8}$/.test(bin)){setStatus('kx-bin-status','Enter exactly 6 to 8 digits. Do not paste a full card number.',true);return;}
        setApiLoading(button,true,'Check BIN'); setStatus('kx-bin-status','Checking public issuer data…');
        try { const url=new URL(toolApi.bin,window.location.href);url.searchParams.set('bin',bin);const response=await fetch(url,{headers:{Accept:'application/json'}});const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.message||`BIN lookup failed (HTTP ${response.status}).`);addApiRecord(output,'Scheme / type',[data.scheme,data.type,data.brand].filter(Boolean).join(' · ')||'Not provided');addApiRecord(output,'Issuer',data.bank?.name||'Not provided');addApiRecord(output,'Country',[data.country?.name,data.country?.alpha2].filter(Boolean).join(' · '));addApiRecord(output,'Matched prefix / source',[data.matched_bin,data.provider].filter(Boolean).join(' · '));setStatus('kx-bin-status','Lookup complete. Issuer records can vary by bank and may not cover every local range.'); }
        catch(error){setStatus('kx-bin-status',error.message||'BIN lookup failed.',true);} finally{setApiLoading(button,false,'Check BIN');}
    });
    $('kx-card-show').addEventListener('change',()=>{$('kx-card-input').type=$('kx-card-show').checked?'text':'password';});
    $('kx-card-run').addEventListener('click',()=>{
        const digits=$('kx-card-input').value;
        if(!/^[0-9]{12,19}$/.test(digits)){setStatus('kx-card-output','Enter 12–19 digits only, with no spaces or punctuation.',true);return;}
        let sum=0, double=false; for(let i=digits.length-1;i>=0;i--){let n=Number(digits[i]);if(double){n*=2;if(n>9)n-=9;}sum+=n;double=!double;}
        const valid=sum%10===0; setStatus('kx-card-output',valid?'Checksum passes the Luhn test. This does not verify the card.':'Checksum does not pass the Luhn test.',!valid);
    });
    $('kx-card-clear').addEventListener('click',()=>{$('kx-card-input').value='';$('kx-card-input').type='password';$('kx-card-show').checked=false;setStatus('kx-card-output','Card data cleared from the input. No card data is sent or saved.');});
    $('kx-currency-run').addEventListener('click',async(event)=>{
        const button=event.currentTarget, amount=Number($('kx-currency-amount').value), from=$('kx-currency-from').value, to=$('kx-currency-to').value;
        if(!Number.isFinite(amount)||amount<=0){setStatus('kx-currency-status','Enter an amount greater than zero.',true);return;}
        setApiLoading(button,true,'Convert'); setStatus('kx-currency-status','Loading public reference rates…'); $('kx-currency-output').textContent='Loading…';
        try{const url=new URL(toolApi.currency,window.location.href);url.searchParams.set('amount',String(amount));url.searchParams.set('from',from);url.searchParams.set('to',to);const response=await fetch(url,{headers:{Accept:'application/json'},cache:'no-store'});const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.message||`Rate provider returned HTTP ${response.status}.`);const rate=Number(data.rate),converted=Number(data.converted);if(!Number.isFinite(rate)||!Number.isFinite(converted))throw new Error('That currency pair is currently unavailable.');$('kx-currency-output').textContent=`${new Intl.NumberFormat(undefined,{maximumFractionDigits:6}).format(converted)} ${to}`;setStatus('kx-currency-status',`1 ${from} = ${rate.toLocaleString(undefined,{maximumFractionDigits:6})} ${to} · Provider: ${data.provider}${data.updated_at?` · Updated ${data.updated_at}`:''}`);}
        catch(error){$('kx-currency-output').textContent='Conversion unavailable';setStatus('kx-currency-status',error.message||'Could not load rates. Try again later.',true);}finally{setApiLoading(button,false,'Convert');}
    });
    $('kx-weather-run').addEventListener('click',async(event)=>{
        const place=$('kx-weather-place').value.trim(),output=$('kx-weather-output');output.replaceChildren();
        if(place.length<2||place.length>100){setStatus('kx-weather-status','Enter a city or place name between 2 and 100 characters.',true);return;}
        const button=event.currentTarget;setApiLoading(button,true,'Check weather');setStatus('kx-weather-status','Loading current conditions…');
        try{const url=new URL(toolApi.weather,window.location.href);url.searchParams.set('q',place);const response=await fetch(url,{headers:{Accept:'application/json'},cache:'no-store'});const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.message||`Weather lookup failed (HTTP ${response.status}).`);addApiRecord(output,'Location',data.location);addApiRecord(output,'Conditions',`${data.condition||'Unknown'} · ${data.temperature_c??'—'}°C (feels like ${data.feelslike_c??'—'}°C)`);addApiRecord(output,'Humidity / wind',`${data.humidity??'—'}% · ${data.wind_kph??'—'} km/h`);const aqiLabels={1:'Good',2:'Moderate',3:'Unhealthy for sensitive groups',4:'Unhealthy',5:'Very unhealthy',6:'Hazardous'};addApiRecord(output,'Air quality index',data.air_quality_index===null||data.air_quality_index===undefined?'Not provided':`${data.air_quality_index}${aqiLabels[data.air_quality_index]?` · ${aqiLabels[data.air_quality_index]}`:''}`);setStatus('kx-weather-status',`Updated ${data.updated_at||'recently'} · ${data.provider}`);}
        catch(error){setStatus('kx-weather-status',error.message||'Weather lookup failed. Try again later.',true);}finally{setApiLoading(button,false,'Check weather');}
    });
    $('kx-jobs-run').addEventListener('click',async(event)=>{
        const query=$('kx-jobs-query').value.trim(),output=$('kx-jobs-output');output.replaceChildren();
        if(query.length<2||query.length>100){setStatus('kx-jobs-status','Enter a role, skill, or location between 2 and 100 characters.',true);return;}
        const button=event.currentTarget;setApiLoading(button,true,'Find jobs');setStatus('kx-jobs-status','Searching public job listings… this can take a few seconds.');
        try{const url=new URL(toolApi.jobs,window.location.href);url.searchParams.set('q',query);const response=await fetch(url,{headers:{Accept:'application/json'},cache:'no-store'});const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.message||`Job search failed (HTTP ${response.status}).`);if(!data.jobs?.length){setStatus('kx-jobs-status','No matching public listings were returned. Try a broader search.');return;}data.jobs.forEach((job)=>{const row=document.createElement('div'),link=document.createElement('a'),meta=document.createElement('small');row.className='kx-api-record';link.href=job.url;link.target='_blank';link.rel='noopener noreferrer';link.textContent=job.title;meta.textContent=[job.company,job.location,job.type,job.posted_at].filter(Boolean).join(' · ');row.append(link,meta);output.append(row);});setStatus('kx-jobs-status',`${data.jobs.length} public listing${data.jobs.length===1?'':'s'} · ${data.provider}. Check the listing details before applying.`);}
        catch(error){setStatus('kx-jobs-status',error.message||'Job search failed. Try again later.',true);}finally{setApiLoading(button,false,'Find jobs');}
    });
    $('kx-pwhash-run').addEventListener('click',async(event)=>{
        const button=event.currentTarget, text=$('kx-pwhash-input').value, iterations=Number($('kx-pwhash-iterations').value), output=$('kx-pwhash-output');
        if(!text){setStatus('kx-pwhash-output','Enter text to hash.',true);return;} if(!crypto.subtle){setStatus('kx-pwhash-output','Web Crypto is unavailable. Use a secure HTTPS page.',true);return;}
        setApiLoading(button,true,'Create salted hash'); output.textContent='Deriving…';
        try{const salt=crypto.getRandomValues(new Uint8Array(16)), material=await crypto.subtle.importKey('raw',new TextEncoder().encode(text),'PBKDF2',false,['deriveBits']), bits=await crypto.subtle.deriveBits({name:'PBKDF2',salt,iterations,hash:'SHA-256'},material,256);const b64=(bytes)=>btoa(String.fromCharCode(...bytes)).replace(/\+/g,'-').replace(/\//g,'_').replace(/=+$/,'');output.textContent=`pbkdf2-sha256$${iterations}$${b64(salt)}$${b64(new Uint8Array(bits))}`;}
        catch(_){output.textContent='Hashing failed in this browser.';}finally{setApiLoading(button,false,'Create salted hash');}
    });
    $('kx-smtp-run').addEventListener('click',async(event)=>{
        const host=$('kx-smtp-host').value.trim(),port=Number($('kx-smtp-port').value),encryption=$('kx-smtp-encryption').value,email=$('kx-smtp-email').value.trim(),password=$('kx-smtp-password').value,output=$('kx-smtp-output');output.replaceChildren();
        if(location.protocol!=='https:'&& !['localhost','127.0.0.1'].includes(location.hostname)){setStatus('kx-smtp-status','For your privacy, SMTP credentials can only be tested over HTTPS.',true);return;}
        const validHost=host.length<=253&&/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i.test(host);
        if(!validHost){setStatus('kx-smtp-status','Enter a valid SMTP hostname, such as smtp.example.com.',true);return;}
        if(!email||!password){setStatus('kx-smtp-status','Enter the SMTP email and password or app password.',true);return;}
        const button=event.currentTarget;setApiLoading(button,true,'Test SMTP login');setStatus('kx-smtp-status','Connecting securely and testing SMTP authentication…');
        try{const body=new URLSearchParams({host,port:String(port),encryption,email,password});const response=await fetch(toolApi.smtp,{method:'POST',headers:{Accept:'application/json','Content-Type':'application/x-www-form-urlencoded;charset=UTF-8','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||''},body,cache:'no-store',credentials:'same-origin'});const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.message||`SMTP login test failed (HTTP ${response.status}).`);addApiRecord(output,'Host and port',`${data.host}:${data.port}`);addApiRecord(output,'Encryption',data.encryption==='ssl'?'Implicit TLS':'STARTTLS');setStatus('kx-smtp-status',data.message||'SMTP authentication passed. No email was sent.');}
        catch(error){setStatus('kx-smtp-status',error.message||'SMTP authentication failed.',true);}finally{$('kx-smtp-password').value='';$('kx-smtp-email').value='';setApiLoading(button,false,'Test SMTP login');}
    });
    $('kx-smtp-port').addEventListener('change',()=>{$('kx-smtp-encryption').value=Number($('kx-smtp-port').value)===465?'ssl':'tls';});
    $('kx-user-run').addEventListener('click',async(event)=>{
        const button=event.currentTarget,service=$('kx-user-service').value,name=$('kx-user-name').value.trim(),output=$('kx-user-output');
        if(!/^[a-z\d](?:[a-z\d-]{0,37}[a-z\d])?$/i.test(name)){setStatus('kx-user-output','Enter a username using letters, numbers, and internal hyphens.',true);return;}
        setApiLoading(button,true,'Check username');setStatus('kx-user-output',`Checking public ${service==='github'?'GitHub':'npm'} data…`);
        try{const url=new URL(toolApi.username,window.location.href);url.searchParams.set('service',service);url.searchParams.set('username',name);const response=await fetch(url,{headers:{Accept:'application/json'}});const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.message||`Username lookup failed (HTTP ${response.status}).`);setStatus('kx-user-output',data.exists?(service==='github'?`GitHub handle exists: ${data.username}${data.display_name?` · ${data.display_name}`:''}`:`An npm package already uses this name: ${data.username}`):(service==='github'?'No public GitHub profile found for this handle.':'No npm package found with this name; package names are not npm usernames.'));}
        catch(error){setStatus('kx-user-output',error.message||'Lookup failed. Try again later.',true);}finally{setApiLoading(button,false,'Check username');}
    });
    $('kx-social-search').addEventListener('click',()=>{
        const raw=$('kx-social-handle').value.trim().replace(/^@+/,''), results=$('kx-social-results'); results.replaceChildren();
        if(!/^[a-z\d._-]{1,50}$/i.test(raw)){$('kx-social-handle').setAttribute('aria-invalid','true');results.textContent='Enter a handle with letters, numbers, dots, underscores, or hyphens.';return;}
        $('kx-social-handle').removeAttribute('aria-invalid');
        const selected=[...document.querySelectorAll('.kx-social-platforms input:checked')];
        if(!selected.length){results.textContent='Select at least one platform to search.';return;}
        const profileUrls={
            instagram:(handle)=>`https://www.instagram.com/${encodeURIComponent(handle)}/`,
            tiktok:(handle)=>`https://www.tiktok.com/@${encodeURIComponent(handle)}`,
            x:(handle)=>`https://x.com/${encodeURIComponent(handle)}`,
            facebook:(handle)=>`https://www.facebook.com/${encodeURIComponent(handle)}`,
            threads:(handle)=>`https://www.threads.net/@${encodeURIComponent(handle)}`,
            youtube:(handle)=>`https://www.youtube.com/@${encodeURIComponent(handle)}`,
            linkedin:(handle)=>`https://www.linkedin.com/in/${encodeURIComponent(handle)}/`,
            reddit:(handle)=>`https://www.reddit.com/user/${encodeURIComponent(handle)}/`,
            github:(handle)=>`https://github.com/${encodeURIComponent(handle)}`,
            bluesky:(handle)=>`https://bsky.app/profile/${encodeURIComponent(handle.includes('.')?handle:`${handle}.bsky.social`)}`,
        };
        const names={instagram:'Instagram',tiktok:'TikTok',x:'X',facebook:'Facebook',threads:'Threads',youtube:'YouTube',linkedin:'LinkedIn',reddit:'Reddit',github:'GitHub',bluesky:'Bluesky'};
        selected.forEach((checkbox)=>{
            const platform=checkbox.value, link=document.createElement('a'), label=document.createElement('strong'), action=document.createElement('span'), url=profileUrls[platform](raw);
            link.href=url;link.target='_blank';link.rel='noopener noreferrer';label.textContent=names[platform];action.textContent=new URL(url).host+new URL(url).pathname;link.append(label,action);results.append(link);
        });
        const notice=document.createElement('p');notice.className='kx-api-status';notice.textContent=`${selected.length} profile link${selected.length===1?'':'s'} built for “${raw}”.`;results.append(notice);
    });
    const invisiblePattern=/[\u00ad\u034f\u061c\u115f\u1160\u17b4\u17b5\u180e\u200b-\u200f\u202a-\u202e\u2060-\u206f\ufeff\ufff9-\ufffb]/u;
    const charLabel=(char)=>{const cp=char.codePointAt(0).toString(16).toUpperCase().padStart(4,'0'),names={0x00ad:'soft hyphen',0x034f:'combining grapheme joiner',0x061c:'Arabic letter mark',0x200b:'zero-width space',0x200c:'zero-width non-joiner',0x200d:'zero-width joiner',0x200e:'left-to-right mark',0x200f:'right-to-left mark',0x2060:'word joiner',0xfeff:'BOM / zero-width no-break space'};return `U+${cp}${names[char.codePointAt(0)]?` · ${names[char.codePointAt(0)]}`:''}`;};
    $('kx-invisible-scan').addEventListener('click',()=>{const text=$('kx-invisible-input').value,found=Array.from(text).filter((char)=>invisiblePattern.test(char));$('kx-invisible-output').textContent=found.length?`Found ${found.length} invisible/control character${found.length===1?'':'s'}:\n${found.slice(0,100).map(charLabel).join('\n')}${found.length>100?'\n…':''}`:'No selected invisible Unicode characters found.';setStatus('kx-invisible-status',`${Array.from(text).length} visible code points inspected.`);});
    $('kx-invisible-remove').addEventListener('click',()=>{const input=$('kx-invisible-input'),before=input.value,after=Array.from(before).filter((char)=>!invisiblePattern.test(char)).join('');input.value=after;$('kx-invisible-output').textContent=after;const removed=Array.from(before).length-Array.from(after).length;setStatus('kx-invisible-status',`${removed} invisible/control character${removed===1?'':'s'} removed from the text.`);});
    $('kx-dns-run').addEventListener('click', async (event) => {
        const button = event.currentTarget, host = $('kx-dns-name').value.trim().replace(/^https?:\/\//i,'').split(/[\/?#]/)[0];
        $('kx-dns-output').replaceChildren();
        if (!host || !/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i.test(host)) { setStatus('kx-dns-status','Enter a valid domain or hostname.',true); return; }
        setApiLoading(button,true,'Look up records'); setStatus('kx-dns-status','Checking live DNS records…');
        try {
            const url = new URL(toolApi.dns,window.location.href); url.searchParams.set('name',host); url.searchParams.set('type',$('kx-dns-type').value);
            const response = await fetch(url,{headers:{Accept:'application/dns-json'}});
            const data = await response.json().catch(()=>({})); if (!response.ok) throw new Error(data.message||`DNS lookup failed (HTTP ${response.status}).`);
            const answers = data.answers || [];
            if (!answers.length) { setStatus('kx-dns-status',data.comment || 'No records of that type were found.'); return; }
            $('kx-dns-output').replaceChildren(...answers.map((answer) => { const row=document.createElement('div'), value=document.createElement('span'), title=document.createElement('b'); row.className='kx-api-record'; title.textContent=`${$('kx-dns-type').value}${Number.isFinite(Number(answer.ttl)) ? ` · TTL ${answer.ttl}s` : ''}`; value.textContent=String(answer.data || '').replace(/^"|"$/g,''); row.append(title,value); return row; }));
            setStatus('kx-dns-status',`${answers.length} record${answers.length===1?'':'s'} found · ${data.provider||'DNS provider'}.`);
        } catch (error) { setStatus('kx-dns-status',error.message || 'DNS lookup failed. Try again shortly.',true); }
        finally { setApiLoading(button,false,'Look up records'); }
    });
    $('kx-github-run').addEventListener('click', async (event) => {
        const button=event.currentTarget, match=$('kx-github-repo').value.trim().replace(/^https?:\/\/(www\.)?github\.com\//i,'').replace(/\.git$/i,'').match(/^([a-z\d](?:[a-z\d-]{0,37}[a-z\d])?)\/([a-z\d._-]{1,100})$/i);
        $('kx-github-output').replaceChildren();
        if (!match) { setStatus('kx-github-status','Use the format owner/repository.',true); return; }
        setApiLoading(button,true,'Fetch repository'); setStatus('kx-github-status','Contacting GitHub…');
        try {
            const url=new URL(toolApi.github,window.location.href);url.searchParams.set('repo',`${match[1]}/${match[2]}`);const response=await fetch(url,{headers:{Accept:'application/json'}});const payload=await response.json().catch(()=>({}));
            if (response.status===404) throw new Error(payload.message||'Public repository not found. Check the owner and repository name.');
            if (response.status===429) throw new Error(payload.message||'GitHub rate limit reached. Wait a minute and try again.');
            if (!response.ok) throw new Error(payload.message||`GitHub lookup failed (HTTP ${response.status}).`);
            const repo=payload, output=$('kx-github-output');
            const add=(label,value)=>{ const row=document.createElement('div'), title=document.createElement('b'), detail=document.createElement('span'); row.className='kx-api-record'; title.textContent=label; detail.textContent=value; row.append(title,detail); output.append(row); };
            add(repo.full_name,repo.description || 'No description provided.');
            add('Repository stats',`${Number(repo.stargazers_count||0).toLocaleString()} stars · ${Number(repo.forks_count||0).toLocaleString()} forks · ${repo.language || 'Language not specified'}`);
            add('Updated',repo.updated_at ? new Date(repo.updated_at).toLocaleString() : 'Not available');
            const link=document.createElement('a'); link.className='kx-tool-link'; link.href=repo.html_url; link.target='_blank'; link.rel='noopener noreferrer'; link.textContent='Open on GitHub ↗'; output.append(link);
            setStatus('kx-github-status','Public repository details loaded.');
        } catch (error) { setStatus('kx-github-status',error.message || 'Repository lookup failed. Try again shortly.',true); }
        finally { setApiLoading(button,false,'Fetch repository'); }
    });
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
    const escapeHtml = (value) => value.replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const renderMarkdown = (source) => {
        const lines = source.replace(/\r/g,'').split('\n'), html = [];
        let inCode = false, codeLines = [], inList = false;
        const closeList = () => { if (inList) { html.push('</ul>'); inList = false; } };
        const inline = (text) => escapeHtml(text)
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            .replace(/\*([^*]+)\*/g, '<em>$1</em>')
            .replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
        for (const line of lines) {
            if (line.startsWith('```')) { closeList(); if (inCode) { html.push(`<pre><code>${escapeHtml(codeLines.join('\n'))}</code></pre>`); codeLines=[]; inCode=false; } else inCode=true; continue; }
            if (inCode) { codeLines.push(line); continue; }
            const heading = line.match(/^(#{1,3})\s+(.+)$/);
            const item = line.match(/^\s*[-*+]\s+(.+)$/);
            if (heading) { closeList(); const level=heading[1].length; html.push(`<h${level}>${inline(heading[2])}</h${level}>`); }
            else if (item) { if (!inList) { html.push('<ul>'); inList=true; } html.push(`<li>${inline(item[1])}</li>`); }
            else { closeList(); if (line.trim()) html.push(`<p>${inline(line)}</p>`); }
        }
        closeList(); if (inCode) html.push(`<pre><code>${escapeHtml(codeLines.join('\n'))}</code></pre>`);
        return html.join('') || '<span class="kx-tools-status">Preview appears here</span>';
    };
    $('kx-markdown-input').addEventListener('input', () => { $('kx-markdown-preview').innerHTML = renderMarkdown($('kx-markdown-input').value); });
    $('kx-diff-run').addEventListener('click', () => {
        const left = $('kx-diff-left').value.split(/\r?\n/), right = $('kx-diff-right').value.split(/\r?\n/);
        if (left.length * right.length > 90000) { setStatus('kx-diff-status', 'Compare up to 300 lines on each side.', true); return; }
        const rows = Array.from({length:left.length+1}, () => new Uint16Array(right.length+1));
        for (let i=left.length-1;i>=0;i--) for (let j=right.length-1;j>=0;j--) rows[i][j] = left[i]===right[j] ? rows[i+1][j+1]+1 : Math.max(rows[i+1][j],rows[i][j+1]);
        let i=0,j=0,added=0,removed=0; const out=[];
        while(i<left.length || j<right.length) {
            if(i<left.length && j<right.length && left[i]===right[j]) { i++; j++; continue; }
            if(j<right.length && (i===left.length || rows[i][j+1]>=rows[i+1][j])) { out.push(`+ ${right[j++]}`); added++; }
            else { out.push(`− ${left[i++]}`); removed++; }
        }
        $('kx-diff-output').textContent = out.length ? out.join('\n') : 'The two texts are identical.';
        setStatus('kx-diff-status', out.length ? `${removed} removed · ${added} added` : 'No differences');
    });
    const hexRgb = (hex) => { const value=hex.replace('#',''); const full=value.length===3?[...value].map((x)=>x+x).join(''):value; return full.length===6 ? [0,2,4].map((i)=>parseInt(full.slice(i,i+2),16)) : null; };
    const luminance = (rgb) => rgb.map((v)=>{v/=255;return v<=.04045?v/12.92:((v+.055)/1.055)**2.4;}).reduce((sum,v,i)=>sum+v*[.2126,.7152,.0722][i],0);
    const paintContrast = () => {
        const fg=hexRgb($('kx-contrast-fg').value), bg=hexRgb($('kx-contrast-bg').value), a=luminance(fg), b=luminance(bg), ratio=(Math.max(a,b)+.05)/(Math.min(a,b)+.05);
        $('kx-contrast-preview').style.color=$('kx-contrast-fg').value; $('kx-contrast-preview').style.backgroundColor=$('kx-contrast-bg').value;
        $('kx-contrast-ratio').textContent=`${ratio.toFixed(2)}:1`;
        $('kx-contrast-result').textContent=`${ratio>=4.5?'AA text ✓':'AA text ✕'} · ${ratio>=7?'AAA text ✓':'AAA text ✕'} · ${ratio>=3?'Large text ✓':'Large text ✕'}`;
    };
    ['kx-contrast-fg','kx-contrast-bg'].forEach((id)=>$(id).addEventListener('input',paintContrast)); paintContrast();
    $('kx-stats-input').addEventListener('input', () => {
        const text=$('kx-stats-input').value, words=text.trim()?text.trim().match(/[\p{L}\p{N}]+(?:['’.-][\p{L}\p{N}]+)*/gu)||[]:[], lines=text?text.split(/\r?\n/).length:0;
        $('kx-stats-words').textContent=words.length.toLocaleString(); $('kx-stats-chars').textContent=Array.from(text).length.toLocaleString(); $('kx-stats-lines').textContent=lines.toLocaleString(); $('kx-stats-read').textContent=words.length?`${Math.max(1,Math.ceil(words.length/200))} min`:'0 min';
    });
    const updateTimestamp = () => {
        const raw=$('kx-timestamp-input').value.trim(), value=Number(raw);
        if(!raw || !Number.isFinite(value)){ $('kx-timestamp-local').textContent='Enter a timestamp'; $('kx-timestamp-utc').textContent='—'; return; }
        const date=new Date(Math.abs(value)<1e12?value*1000:value);
        if(Number.isNaN(date.getTime())){ $('kx-timestamp-local').textContent='Timestamp out of range'; $('kx-timestamp-utc').textContent='—'; return; }
        $('kx-timestamp-local').textContent=date.toLocaleString(); $('kx-timestamp-utc').textContent=date.toISOString();
    };
    $('kx-timestamp-input').addEventListener('input',updateTimestamp);
    $('kx-timestamp-now').addEventListener('click',()=>{ $('kx-timestamp-input').value=Math.floor(Date.now()/1000); updateTimestamp(); });
    $('kx-datetime-convert').addEventListener('click',()=>{
        const date=new Date($('kx-datetime-input').value);
        $('kx-datetime-result').textContent=$('kx-datetime-input').value && !Number.isNaN(date.getTime()) ? `Seconds: ${Math.floor(date.getTime()/1000)}\nMilliseconds: ${date.getTime()}` : 'Choose a valid date and time.';
    });
    loadRates(); loadMarket(); window.setInterval(loadRates,60000); window.setInterval(loadMarket,60000);
})();
</script>
@endpush
