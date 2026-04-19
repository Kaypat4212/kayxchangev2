@extends('adminnavlayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
}
body{background:var(--kx-dark);color:var(--kx-text);}

.diag-wrap{padding:28px 0 60px;}
.diag-page-title{font-size:clamp(1.3rem,3vw,1.8rem);font-weight:800;color:#fff;margin-bottom:4px;}
.diag-page-sub{color:var(--kx-muted);font-size:.875rem;margin-bottom:28px;}

.diag-run-btn{
    display:inline-flex;align-items:center;gap:.6rem;
    background:var(--kx-green);color:#000;border:none;
    font-weight:700;font-size:.95rem;border-radius:12px;
    padding:.75rem 2rem;cursor:pointer;transition:opacity .2s;
}
.diag-run-btn:hover{opacity:.85;}
.diag-run-btn:disabled{opacity:.5;cursor:not-allowed;}

.diag-spinner{display:none;width:18px;height:18px;border:3px solid rgba(0,0,0,.3);
    border-top-color:#000;border-radius:50%;animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}

/* Check cards grid */
.diag-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(340px,1fr));
    gap:16px;
    margin-top:28px;
}

.diag-card{
    background:var(--kx-card);
    border:1px solid var(--kx-border);
    border-radius:16px;
    padding:20px 22px;
    position:relative;overflow:hidden;
    transition:border-color .3s;
}
.diag-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:var(--status-color,#7a8599);transition:background .3s;
}
.diag-card.status-ok   { --status-color:#00cc00; border-color:rgba(0,204,0,.2); }
.diag-card.status-warn { --status-color:#f59e0b; border-color:rgba(245,158,11,.2); }
.diag-card.status-fail { --status-color:#ef4444; border-color:rgba(239,68,68,.2); }

.diag-card-header{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:10px;}
.diag-card-name{
    display:flex;align-items:center;gap:.6rem;
    font-weight:700;font-size:.95rem;color:#fff;
}
.diag-card-name i{font-size:1.1rem;color:var(--status-color,#7a8599);}
.diag-badge{
    font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
    padding:.25em .7em;border-radius:999px;
    background:rgba(122,133,153,.12);color:#7a8599;
}
.diag-badge.ok   {background:rgba(0,204,0,.12);   color:#00cc00;}
.diag-badge.warn {background:rgba(245,158,11,.12); color:#f59e0b;}
.diag-badge.fail {background:rgba(239,68,68,.12);  color:#ef4444;}

.diag-message{font-size:.82rem;color:var(--kx-muted);line-height:1.5;}
.diag-detail {font-size:.75rem;color:#4b5563;margin-top:4px;font-family:monospace;}

/* Idle placeholder */
.diag-idle{
    text-align:center;padding:48px 0;color:var(--kx-muted);
}
.diag-idle i{font-size:2.5rem;margin-bottom:12px;display:block;opacity:.4;}

/* Env hint box */
.diag-hint{
    background:rgba(0,204,0,.06);border:1px solid rgba(0,204,0,.15);
    border-radius:12px;padding:14px 18px;margin-top:28px;
    font-size:.82rem;color:#86efac;
}
.diag-hint a{color:#00cc00;text-decoration:underline;}

/* Key labels shown in cards */
.diag-env-key{
    font-size:.7rem;font-family:monospace;
    color:rgba(255,255,255,.2);margin-top:6px;display:block;
}
</style>
@endpush

@section('content')
<div class="container diag-wrap">
    <h1 class="diag-page-title"><i class="bi bi-stethoscope me-2"></i>System Diagnostics</h1>
    <p class="diag-page-sub">Test all API integrations, SMTP connectivity, and server capabilities in one click.</p>

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <button class="diag-run-btn" id="runBtn" onclick="runDiagnostics()">
            <i class="bi bi-play-fill" id="runIcon"></i>
            <span class="diag-spinner" id="runSpinner"></span>
            <span id="runLabel">Run All Checks</span>
        </button>
        <span class="text-muted small" id="lastRunLabel"></span>
        <a href="{{ route('admin.env.index') }}" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-key-fill me-1"></i>Edit API Keys
        </a>
        <a href="{{ route('admin.email-settings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-envelope-fill me-1"></i>Email Settings
        </a>
    </div>

    <div id="diagResults">
        <div class="diag-idle">
            <i class="bi bi-stethoscope"></i>
            Click <strong>Run All Checks</strong> to test your integrations.
        </div>
    </div>

    <div class="diag-hint mt-3">
        <i class="bi bi-lightbulb-fill me-1"></i>
        <strong>Tip:</strong> If any API key fails, go to <a href="{{ route('admin.env.index') }}">API Keys &amp; Environment</a> to update it.
        For SMTP failures, check <a href="{{ route('admin.email-settings.index') }}">Email Settings</a> and make sure your cPanel email account password is correct.
    </div>
</div>
@endsection

@push('scripts')
<script>
const CHECK_META = {
    smtp:        { name: 'SMTP / Email',       icon: 'bi-envelope-fill',      keys: ['MAIL_HOST','MAIL_USERNAME','MAIL_PASSWORD','MAIL_PORT'] },
    telegram:    { name: 'Telegram Bot',        icon: 'bi-telegram',           keys: ['TELEGRAM_BOT_TOKEN'] },
    paystack:    { name: 'Paystack',            icon: 'bi-credit-card-fill',   keys: ['PAYSTACK_SECRET_KEY'] },
    groq:        { name: 'Groq AI',             icon: 'bi-robot',              keys: ['GROQ_API_KEY'] },
    etherscan:   { name: 'Etherscan',           icon: 'bi-currency-ethereum',  keys: ['ETHERSCAN_API_KEY'] },
    blockcypher: { name: 'BlockCypher (BTC)',   icon: 'bi-currency-bitcoin',   keys: ['BLOCKCYPHER_TOKEN'] },
    trongrid:    { name: 'TronGrid (TRX/USDT)', icon: 'bi-lightning-charge-fill', keys: ['TRONGRID_API_KEY'] },
    php_mail:    { name: 'PHP mail() Function', icon: 'bi-code-square',        keys: [] },
    curl:        { name: 'cURL / HTTP Client',  icon: 'bi-globe2',             keys: [] },
    database:    { name: 'Database',            icon: 'bi-database-fill',      keys: [] },
};

function runDiagnostics() {
    const btn     = document.getElementById('runBtn');
    const icon    = document.getElementById('runIcon');
    const spinner = document.getElementById('runSpinner');
    const label   = document.getElementById('runLabel');
    const results = document.getElementById('diagResults');

    btn.disabled     = true;
    icon.style.display   = 'none';
    spinner.style.display = 'block';
    label.textContent = 'Running…';

    // Show loading skeletons
    results.innerHTML = buildSkeletons();

    fetch('{{ route('admin.diagnostics.run') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        renderResults(data);
        document.getElementById('lastRunLabel').textContent =
            'Last checked: ' + new Date().toLocaleTimeString();
    })
    .catch(err => {
        results.innerHTML = `<div class="alert alert-danger mt-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Failed to run diagnostics: ${err.message}
        </div>`;
    })
    .finally(() => {
        btn.disabled = false;
        icon.style.display = '';
        spinner.style.display = 'none';
        label.textContent = 'Run All Checks';
    });
}

function buildSkeletons() {
    const keys = Object.keys(CHECK_META);
    return `<div class="diag-grid">${keys.map(k => `
        <div class="diag-card" style="opacity:.5">
            <div class="diag-card-header">
                <div class="diag-card-name">
                    <i class="bi ${CHECK_META[k].icon}"></i>
                    ${CHECK_META[k].name}
                </div>
                <span class="diag-badge">checking…</span>
            </div>
            <div class="diag-message text-muted" style="font-style:italic">Running check…</div>
        </div>`).join('')}</div>`;
}

function renderResults(data) {
    const container = document.createElement('div');
    container.className = 'diag-grid';

    let okCount = 0, warnCount = 0, failCount = 0;

    for (const [checkKey, meta] of Object.entries(CHECK_META)) {
        const result = data[checkKey] || { status: 'fail', message: 'No response received.' };
        const status = result.status || 'fail';
        if (status === 'ok')   okCount++;
        if (status === 'warn') warnCount++;
        if (status === 'fail') failCount++;

        const card = document.createElement('div');
        card.className = `diag-card status-${status}`;
        card.innerHTML = `
            <div class="diag-card-header">
                <div class="diag-card-name">
                    <i class="bi ${meta.icon}"></i>
                    ${meta.name}
                </div>
                <span class="diag-badge ${status}">${statusLabel(status)}</span>
            </div>
            <div class="diag-message">${escHtml(result.message || '')}</div>
            ${result.detail ? `<div class="diag-detail">${escHtml(result.detail)}</div>` : ''}
            ${meta.keys.length ? `<span class="diag-env-key">${meta.keys.join(' · ')}</span>` : ''}
        `;
        container.appendChild(card);
    }

    // Summary banner
    const banner = document.createElement('div');
    const bannerClass = failCount > 0 ? 'danger' : (warnCount > 0 ? 'warning' : 'success');
    const bannerText  = failCount > 0
        ? `${failCount} check(s) failed — see red cards below`
        : (warnCount > 0 ? `${warnCount} warning(s) — see amber cards` : `All ${okCount} checks passed`);

    banner.innerHTML = `
        <div class="alert alert-${bannerClass} d-flex align-items-center gap-2 mt-4 mb-0" style="border-radius:12px;">
            <i class="bi bi-${failCount > 0 ? 'x-circle-fill' : (warnCount > 0 ? 'exclamation-triangle-fill' : 'check-circle-fill')} fs-5"></i>
            <span><strong>${bannerText}</strong> &nbsp;·&nbsp; ${okCount} OK · ${warnCount} Warnings · ${failCount} Failed</span>
        </div>`;

    const wrap = document.createElement('div');
    wrap.appendChild(banner);
    wrap.appendChild(container);

    document.getElementById('diagResults').innerHTML = '';
    document.getElementById('diagResults').appendChild(wrap);
}

function statusLabel(s) {
    return s === 'ok' ? '✓ OK' : (s === 'warn' ? '⚠ Warning' : '✗ Failed');
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>
@endpush
