@extends('adminnavlayout')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    :root {
        --kx-green: #00cc00;
        --kx-green-dim: rgba(0,204,0,0.12);
        --kx-green-glow: rgba(0,204,0,0.25);
        --kx-dark: #0d1117;
        --kx-card: #161b27;
        --kx-card2: #1e2535;
        --kx-border: rgba(255,255,255,0.07);
        --kx-text: #e4e8f0;
        --kx-muted: #7a8599;
        --kx-danger: #ef4444;
        --kx-warning: #f59e0b;
        --kx-info: #38bdf8;
        --kx-purple: #a855f7;
        --kx-amber: #fbbf24;
    }

    body {
        background: var(--kx-dark);
        color: var(--kx-text);
        font-family: 'Poppins', sans-serif;
    }

    .kx-welcome {
        background: linear-gradient(135deg, #0a1628 0%, #112240 50%, #0a1628 100%);
        border: 1px solid var(--kx-border);
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .kx-welcome::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 200px; height: 200px;
        background: radial-gradient(circle, var(--kx-green-glow), transparent 70%);
        pointer-events: none;
    }
    .kx-welcome-title { font-size: 1.4rem; font-weight: 700; color: #fff; margin-bottom: 2px; }
    .kx-welcome-sub { color: var(--kx-muted); font-size: 0.85rem; }
    .kx-live-dot {
        display: inline-block; width: 8px; height: 8px;
        background: var(--kx-green); border-radius: 50%;
        animation: pulse-dot 1.6s ease infinite;
        vertical-align: middle; margin-right: 6px;
    }
    @keyframes pulse-dot {
        0%,100% { box-shadow: 0 0 0 0 var(--kx-green-glow); }
        50%      { box-shadow: 0 0 0 6px transparent; }
    }

    .kx-stat {
        background: var(--kx-card);
        border: 1px solid var(--kx-border);
        border-radius: 14px;
        padding: 22px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow 0.25s, transform 0.25s;
        text-decoration: none;
        color: var(--kx-text);
        height: 100%;
    }
    .kx-stat:hover { box-shadow: 0 0 0 1px var(--kx-green), 0 8px 24px rgba(0,0,0,0.4); transform: translateY(-2px); color: var(--kx-text); }
    .kx-stat-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .kx-stat-icon.green  { background: var(--kx-green-dim);  color: var(--kx-green); }
    .kx-stat-icon.amber  { background: rgba(251,191,36,0.12); color: var(--kx-amber); }
    .kx-stat-icon.blue   { background: rgba(56,189,248,0.12); color: var(--kx-info); }
    .kx-stat-icon.purple { background: rgba(168,85,247,0.12); color: var(--kx-purple); }
    .kx-stat-icon.red    { background: rgba(239,68,68,0.12);  color: var(--kx-danger); }
    .kx-stat-icon.orange { background: rgba(249,115,22,0.12); color: #f97316; }
    .kx-stat-val { font-size: 1.55rem; font-weight: 700; color: #fff; line-height: 1; }
    .kx-stat-label { font-size: 0.78rem; color: var(--kx-muted); margin-top: 3px; text-transform: uppercase; letter-spacing: 0.04em; }

    .kx-nav-card {
        background: var(--kx-card);
        border: 1px solid var(--kx-border);
        border-radius: 14px;
        padding: 20px;
        text-decoration: none;
        color: var(--kx-text);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow 0.25s, transform 0.25s, border-color 0.25s;
        height: 100%;
    }
    .kx-nav-card:hover { box-shadow: 0 0 0 1px var(--kx-green), 0 6px 20px rgba(0,0,0,0.35); transform: translateY(-2px); border-color: var(--kx-green); color: #fff; }
    .kx-nav-card .kx-stat-icon { width: 44px; height: 44px; font-size: 1.2rem; }
    .kx-nav-card-title { font-size: 0.95rem; font-weight: 600; color: #fff; }
    .kx-nav-card-sub { font-size: 0.75rem; color: var(--kx-muted); }
    .kx-mini-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 6px;
        border-radius: 999px;
        font-size: 0.67rem;
        font-weight: 700;
        line-height: 1;
        background: rgba(239,68,68,0.14);
        color: var(--kx-danger);
        border: 1px solid rgba(239,68,68,0.3);
        margin-left: 6px;
        vertical-align: middle;
    }
    .d-none { display: none !important; }

    .kx-panel {
        background: var(--kx-card);
        border: 1px solid var(--kx-border);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .kx-panel-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--kx-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--kx-card2);
    }
    .kx-panel-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; color: #fff; }
    .kx-panel-body { padding: 22px; }

    .kx-input {
        background: var(--kx-card2) !important;
        border: 1px solid var(--kx-border) !important;
        color: var(--kx-text) !important;
        border-radius: 8px !important;
        padding: 10px 14px !important;
        font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .kx-input:focus {
        border-color: var(--kx-green) !important;
        box-shadow: 0 0 0 3px var(--kx-green-glow) !important;
        outline: none;
        color: #fff !important;
    }
    .kx-input::placeholder { color: var(--kx-muted) !important; }
    .kx-input.error { border-color: var(--kx-danger) !important; }
    .kx-label { font-size: 0.8rem; color: var(--kx-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 500; display: block; }

    .btn-kx-green {
        background: var(--kx-green); color: #000;
        border: none; border-radius: 8px;
        font-weight: 600; font-size: 0.875rem;
        padding: 10px 20px; cursor: pointer;
        transition: background 0.2s, transform 0.15s;
        display: inline-flex; align-items: center; gap: 8px;
        width: 100%; justify-content: center;
    }
    .btn-kx-green:hover { background: #00b300; transform: translateY(-1px); }
    .btn-kx-green:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .btn-kx-outline {
        background: transparent; color: var(--kx-green);
        border: 1px solid var(--kx-green); border-radius: 8px;
        font-size: 0.8rem; padding: 6px 14px; cursor: pointer;
        transition: background 0.2s, color 0.2s;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-kx-outline:hover { background: var(--kx-green); color: #000; }

    .rate-row {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 12px;
        align-items: end;
        padding: 14px 0;
        border-bottom: 1px solid var(--kx-border);
    }
    .rate-row:last-child { border-bottom: none; }
    .rate-coin-name { font-weight: 600; font-size: 0.9rem; color: #fff; display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .rate-coin-icon { width: 28px; height: 28px; background: var(--kx-green-dim); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: var(--kx-green); font-weight: 700; flex-shrink: 0; }

    .notif-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--kx-border);
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-icon-wrap {
        width: 36px; height: 36px; border-radius: 10px;
        background: var(--kx-green-dim); color: var(--kx-green);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 0.9rem;
    }
    .notif-title { font-size: 0.875rem; font-weight: 600; color: #fff; margin-bottom: 2px; }
    .notif-text  { font-size: 0.78rem; color: var(--kx-muted); margin-bottom: 4px; }
    .notif-meta  { font-size: 0.72rem; color: var(--kx-muted); display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
    .tag-broadcast { background: rgba(56,189,248,0.15); color: var(--kx-info);    border-radius: 4px; padding: 1px 6px; font-size: 0.68rem; font-weight: 600; }
    .tag-unread    { background: rgba(245,158,11,0.15);  color: var(--kx-warning); border-radius: 4px; padding: 1px 6px; font-size: 0.68rem; font-weight: 600; }

    .kx-skeleton { background: linear-gradient(90deg, var(--kx-card2) 25%, #243040 50%, var(--kx-card2) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 6px; }
    @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    .kx-alert-success { background: rgba(0,204,0,0.1); border: 1px solid rgba(0,204,0,0.25); color: var(--kx-green); border-radius: 8px; padding: 10px 14px; font-size: 0.85rem; }
    .kx-alert-error   { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: var(--kx-danger); border-radius: 8px; padding: 10px 14px; font-size: 0.85rem; }

    .admin-dashboard { max-width: 1600px; margin: 0 auto; padding: clamp(14px,2vw,28px); }
    .kx-welcome { border-radius: 22px; padding: clamp(22px,3vw,34px); box-shadow: 0 18px 48px rgba(0,0,0,.22); }
    .kx-welcome-title { font-size: clamp(1.3rem,2.2vw,1.8rem); }
    .kx-welcome-sub { margin-top: 6px; }
    .kx-overview-label { color:#80e9a3; font-size:.7rem; font-weight:800; letter-spacing:.13em; text-transform:uppercase; margin-bottom:8px; }
    .kx-overview-actions { display:flex; flex-wrap:wrap; gap:9px; margin-top:20px; }
    .kx-overview-actions .btn-kx-outline { min-height:40px; padding:8px 13px; }
    .kx-queue-card { min-height:116px; position:relative; overflow:hidden; }
    .kx-queue-card .kx-queue-number { margin-left:auto; font-size:1.35rem; font-weight:800; color:#fff; }
    .kx-queue-card .kx-nav-card-title { font-size:.88rem; }
    .kx-queue-card .kx-nav-card-sub { line-height:1.45; }
    .kx-section-heading { display:flex; align-items:end; justify-content:space-between; gap:12px; margin:30px 0 14px; }
    .kx-section-heading h2 { color:#fff; font-weight:800; font-size:1.12rem; margin:0; }
    .kx-section-heading p { color:var(--kx-muted); font-size:.78rem; margin:4px 0 0; }
    .kx-stat { border-radius:16px; min-height:100px; padding:18px; }
    .kx-panel { border-radius:18px; box-shadow:0 14px 34px rgba(0,0,0,.12); }
    .kx-panel-header { padding:16px 20px; }
    .kx-panel-body { padding:20px; }
    .kx-nav-card { border-radius:14px; }
    .kx-config-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
    .kx-config-grid .kx-nav-card { min-width:0; }
    .kx-admin-module-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:10px; }
    @media(max-width:767px){
        .admin-dashboard { padding:12px; }
        .kx-overview-actions { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); }
        .kx-overview-actions > * { justify-content:center; }
        .kx-queue-card { min-height:94px; padding:14px; gap:10px; }
        .kx-queue-card .kx-stat-icon { width:38px;height:38px;font-size:1rem; }
        .kx-queue-card .kx-queue-number { font-size:1.15rem; }
        .kx-section-heading { align-items:flex-start; flex-direction:column; }
        .kx-config-grid { grid-template-columns:1fr; }
    }

    .kx-spin { display: none; width: 16px; height: 16px; border: 2px solid rgba(0,0,0,0.3); border-top-color: #000; border-radius: 50%; animation: kxspin 0.7s linear infinite; flex-shrink: 0; }
    @keyframes kxspin { to { transform: rotate(360deg); } }

    .kx-mode-badge { display:inline-flex;align-items:center;gap:10px;min-height:46px;padding:7px 10px 7px 13px;border-radius:999px;border:1px solid rgba(74,222,128,.3);background:linear-gradient(135deg,rgba(20,39,31,.98),rgba(10,24,19,.98));color:#e8fff0;font-size:.78rem;font-weight:750;cursor:pointer;transition:transform .2s,box-shadow .2s,border-color .2s;white-space:nowrap;box-shadow:0 8px 24px rgba(0,0,0,.22),inset 0 1px rgba(255,255,255,.08); }
    .kx-mode-badge:hover { transform:translateY(-2px);box-shadow:0 12px 30px rgba(0,0,0,.32),0 0 0 4px rgba(34,197,94,.1); }
    .kx-mode-badge:focus-visible { outline:3px solid rgba(134,239,172,.75);outline-offset:3px; }
    .kx-mode-badge.mode-local { border-color:rgba(251,191,36,.42);background:linear-gradient(135deg,rgba(54,39,18,.98),rgba(30,23,13,.98));color:#fff3cf; }
    .kx-mode-status { display:flex;align-items:center;gap:8px; }
    .kx-mode-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0;background:#22c55e;box-shadow:0 0 12px rgba(34,197,94,.8); }
    .mode-local .kx-mode-dot { background:#f59e0b;box-shadow:0 0 12px rgba(245,158,11,.8); }
    .kx-mode-label { font-size:.72rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase; }
    .kx-mode-switch { width:48px;height:27px;padding:3px;display:inline-flex;align-items:center;border-radius:999px;background:linear-gradient(135deg,#168447,#0c6335);box-shadow:inset 0 2px 5px rgba(0,0,0,.35);transition:background .25s;flex-shrink:0; }
    .kx-mode-switch-knob { width:21px;height:21px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(145deg,#fff,#dcefe3);color:#13743d;box-shadow:0 2px 7px rgba(0,0,0,.35);transition:transform .28s cubic-bezier(.2,.8,.2,1),color .2s; }
    .mode-local .kx-mode-switch { background:linear-gradient(135deg,#e5a11b,#b66e0c); }
    .mode-local .kx-mode-switch-knob { transform:translateX(21px);color:#9a5a08; }
    .kx-mode-badge.mode-off { border-color:rgba(248,113,113,.4);background:linear-gradient(135deg,rgba(55,25,28,.98),rgba(30,17,20,.98));color:#ffe1e1; }
    .mode-off .kx-mode-switch { background:linear-gradient(135deg,#c24141,#7f1d1d); }
    .mode-off .kx-mode-switch-knob { transform:translateX(9px);color:#a52d2d; }
    .kx-mode-confirm-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .kx-mode-confirm-overlay.active { display: flex; }
    .kx-mode-confirm-box {
        background: var(--kx-card2);
        border: 1px solid var(--kx-border);
        border-radius: 16px;
        padding: 28px 32px;
        max-width: 400px;
        width: 90%;
        text-align: center;
    }
    .kx-mode-confirm-icon {
        font-size: 2.5rem;
        margin-bottom: 14px;
    }
    .kx-mode-confirm-title { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 8px; }
    .kx-mode-confirm-text  { font-size: 0.84rem; color: var(--kx-muted); margin-bottom: 22px; line-height: 1.6; }
    .kx-mode-confirm-btns  { display: flex; gap: 10px; justify-content: center; }
    .btn-kx-cancel {
        background: var(--kx-card); color: var(--kx-muted);
        border: 1px solid var(--kx-border); border-radius: 8px;
        padding: 9px 22px; cursor: pointer; font-size: 0.875rem;
        transition: background 0.2s;
    }
    .btn-kx-cancel:hover { background: #252e40; color: #fff; }
    .btn-kx-amber {
        background: var(--kx-amber); color: #000;
        border: none; border-radius: 8px;
        padding: 9px 22px; cursor: pointer; font-weight: 700; font-size: 0.875rem;
        transition: background 0.2s, transform 0.15s;
    }
    .btn-kx-amber:hover { background: #e6a800; transform: translateY(-1px); }

    @media (max-width: 767px) {
        .kx-welcome { padding: 18px; }
        .kx-welcome-title { font-size: 1.1rem; }
        .kx-stat-val { font-size: 1.25rem; }
        .rate-row { grid-template-columns: 1fr 1fr; }
        .rate-row > div:first-child { grid-column: 1/-1; }
        .kx-mode-label { display:none; }
    }
</style>

<!-- MODE CONFIRM OVERLAY -->
<div id="kx-mode-overlay" class="kx-mode-confirm-overlay">
    <div class="kx-mode-confirm-box">
        <div id="kx-mode-confirm-icon" class="kx-mode-confirm-icon">🔄</div>
        <div id="kx-mode-confirm-title" class="kx-mode-confirm-title">Switch Site Mode?</div>
        <div id="kx-mode-confirm-text" class="kx-mode-confirm-text"></div>
        <div class="kx-mode-confirm-btns">
            <button class="btn-kx-cancel" onclick="document.getElementById('kx-mode-overlay').classList.remove('active')">Cancel</button>
            <button id="kx-mode-confirm-btn" class="btn-kx-amber">Yes, Switch Mode</button>
        </div>
        <div id="kx-mode-post-msg" class="mt-3" style="display:none;font-size:0.82rem"></div>
    </div>
</div>

<div class="admin-dashboard">
<!-- WELCOME BAR -->
<div class="kx-welcome">
    <div class="row align-items-center">
        <div class="col">
            <div class="kx-welcome-title">
                <span class="kx-live-dot"></span>
                Welcome back, {{ auth()->user()->name ?? 'Admin' }}
            </div>
            <div class="kx-welcome-sub">
                KayXchange Admin Panel &nbsp;&middot;&nbsp;
                <span id="kx-clock"></span>
            </div>
            <div class="kx-overview-actions">
                <a href="{{ route('admin.operations') }}" class="btn-kx-outline"><i class="bi bi-heart-pulse"></i>Operations health</a>
                <a href="{{ route('admin.transactions.index') }}" class="btn-kx-outline"><i class="bi bi-clock-history"></i>All transactions</a>
                <a href="{{ route('admin.support-cases.index') }}" class="btn-kx-outline"><i class="bi bi-headset"></i>Support inbox</a>
                <a href="{{ route('admin.settings.index') }}" class="btn-kx-outline"><i class="bi bi-sliders"></i>Platform settings</a>
            </div>
        </div>
        <div class="col-auto d-none d-md-flex gap-2 align-items-center">
            @php $siteMode = $siteMode ?? 'production'; @endphp
            <button id="kx-mode-toggle-btn"
                class="kx-mode-badge {{ $siteMode === 'local' ? 'mode-local' : ($siteMode === 'off' ? 'mode-off' : 'mode-production') }}"
                data-mode="{{ $siteMode }}"
                role="switch" aria-checked="{{ $siteMode === 'local' ? 'true' : 'false' }}"
                aria-label="Runtime mode. Click to switch between production and local mode."
                title="Switch between production and local runtime mode">
                <span class="kx-mode-status"><span class="kx-mode-dot"></span><span class="kx-mode-label">{{ $siteMode === 'local' ? 'Local Mode' : ($siteMode === 'off' ? 'Maintenance Mode' : 'Production Mode') }}</span></span>
                <span class="kx-mode-switch" aria-hidden="true"><span class="kx-mode-switch-knob"><i class="bi {{ $siteMode === 'local' ? 'bi-code-slash' : ($siteMode === 'off' ? 'bi-pause-fill' : 'bi-shield-check') }}"></i></span></span>
            </button>
            <a href="{{ url('/') }}" target="_blank" class="btn-kx-outline">
                <i class="bi bi-eye"></i> View Site
            </a>
        </div>
        <div class="col-auto d-flex d-md-none">
            <button id="kx-mode-toggle-btn-sm"
                class="kx-mode-badge {{ $siteMode === 'local' ? 'mode-local' : ($siteMode === 'off' ? 'mode-off' : 'mode-production') }}"
                data-mode="{{ $siteMode }}"
                role="switch" aria-checked="{{ $siteMode === 'local' ? 'true' : 'false' }}"
                aria-label="Runtime mode. Click to switch between production and local mode."
                title="Switch between production and local runtime mode">
                <span class="kx-mode-status"><span class="kx-mode-dot"></span><span class="kx-mode-label">{{ $siteMode === 'local' ? 'Local Mode' : ($siteMode === 'off' ? 'Maintenance Mode' : 'Production Mode') }}</span></span>
                <span class="kx-mode-switch" aria-hidden="true"><span class="kx-mode-switch-knob"><i class="bi {{ $siteMode === 'local' ? 'bi-code-slash' : ($siteMode === 'off' ? 'bi-pause-fill' : 'bi-shield-check') }}"></i></span></span>
            </button>
        </div>
    </div>
</div>

<div class="kx-section-heading mt-0">
    <div><h2>Needs your attention</h2><p>Live review queues across payments, accounts, and customer support.</p></div>
    <span class="small text-secondary"><i class="bi bi-arrow-repeat me-1"></i>Refreshes every 30 seconds</span>
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><a href="{{ route('admin.deposits.index') }}" class="kx-nav-card kx-queue-card"><div class="kx-stat-icon blue"><i class="bi bi-cash-coin"></i></div><div><div class="kx-nav-card-title">NGN deposits</div><div class="kx-nav-card-sub">Awaiting review</div></div><span class="kx-queue-number" id="stat-deposits">{{ $pendingCounts['pending_deposits'] ?? 0 }}</span></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.manual-crypto-deposits.index') }}" class="kx-nav-card kx-queue-card"><div class="kx-stat-icon purple"><i class="bi bi-currency-bitcoin"></i></div><div><div class="kx-nav-card-title">Crypto deposits</div><div class="kx-nav-card-sub">Open invoices &amp; reviews</div></div><span class="kx-queue-number" id="stat-crypto-deposits">{{ $pendingCounts['crypto_deposits_open'] ?? 0 }}</span></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.withdrawals') }}" class="kx-nav-card kx-queue-card"><div class="kx-stat-icon orange"><i class="bi bi-bank"></i></div><div><div class="kx-nav-card-title">Withdrawals</div><div class="kx-nav-card-sub">Awaiting payout approval</div></div><span class="kx-queue-number" id="stat-withdrawals">{{ $pendingCounts['pending_withdrawals'] ?? 0 }}</span></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.trades') }}" class="kx-nav-card kx-queue-card"><div class="kx-stat-icon red"><i class="bi bi-arrow-left-right"></i></div><div><div class="kx-nav-card-title">Buy &amp; sell trades</div><div class="kx-nav-card-sub">Pending decisions</div></div><span class="kx-queue-number" id="stat-trades">{{ $pendingCounts['pending_trades'] ?? 0 }}</span></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.crypto-withdrawals.index') }}" class="kx-nav-card kx-queue-card"><div class="kx-stat-icon amber"><i class="bi bi-send-check"></i></div><div><div class="kx-nav-card-title">Crypto payouts</div><div class="kx-nav-card-sub">Pending crypto withdrawals</div></div><span class="kx-queue-number" id="stat-crypto-withdrawals">{{ $pendingCounts['crypto_withdrawals'] ?? 0 }}</span></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.bill-payments.index') }}" class="kx-nav-card kx-queue-card"><div class="kx-stat-icon green"><i class="bi bi-receipt"></i></div><div><div class="kx-nav-card-title">Bill requests</div><div class="kx-nav-card-sub">Pending payment requests</div></div><span class="kx-queue-number" id="stat-bills">{{ $pendingCounts['bill_requests'] ?? 0 }}</span></a></div>
    <div class="col-6 col-xl-3"><a href="{{ url('/admin/kyc') }}" class="kx-nav-card kx-queue-card"><div class="kx-stat-icon blue"><i class="bi bi-patch-check"></i></div><div><div class="kx-nav-card-title">KYC review</div><div class="kx-nav-card-sub">Documents to verify</div></div><span class="kx-queue-number" id="stat-kyc">{{ $pendingCounts['pending_kyc'] ?? 0 }}</span></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.support-cases.index') }}" class="kx-nav-card kx-queue-card"><div class="kx-stat-icon red"><i class="bi bi-chat-square-text"></i></div><div><div class="kx-nav-card-title">Open support cases</div><div class="kx-nav-card-sub">Customer replies needed</div></div><span class="kx-queue-number" id="stat-support">{{ $pendingCounts['open_support'] ?? 0 }}</span></a></div>
</div>

<div class="kx-section-heading">
    <div><h2>Recent deposit activity</h2><p>Latest NGN and crypto deposit requests across providers.</p></div>
    <a class="btn-kx-outline" href="{{ route('admin.deposits.index') }}"><i class="bi bi-list-ul me-1"></i>All deposits</a>
</div>
<div class="row g-3 mb-4">
    @forelse($recentDepositActivity as $activity)
        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ $activity['url'] }}" class="kx-nav-card" style="height:100%;min-height:110px;display:flex;align-items:flex-start;gap:12px;text-decoration:none">
                <div class="kx-stat-icon {{ str_starts_with($activity['kind'],'CRYPTO') ? 'purple' : 'blue' }}"><i class="bi {{ str_starts_with($activity['kind'],'CRYPTO') ? 'bi-currency-bitcoin' : 'bi-cash-coin' }}"></i></div>
                <div style="min-width:0"><div class="kx-nav-card-title">{{ $activity['kind'] }} · #{{ $activity['id'] }}</div><div class="kx-nav-card-sub">{{ $activity['user'] }} · {{ $activity['amount'] }}</div><div class="small text-secondary mt-1">{{ ucfirst(str_replace('_',' ',$activity['status'])) }} · {{ optional($activity['created_at'])->diffForHumans() }}</div></div>
            </a>
        </div>
    @empty
        <div class="col-12"><div class="kx-nav-card text-secondary">No deposits have been recorded yet.</div></div>
    @endforelse
</div>

<div class="kx-section-heading">
    <div><h2>Platform overview</h2><p>Account growth and trading activity at a glance.</p></div>
</div>
<!-- KPI STAT CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="kx-stat">
            <div class="kx-stat-icon green"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="kx-stat-val">{{ $totalUsers }}</div>
                <div class="kx-stat-label">Users</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kx-stat">
            <div class="kx-stat-icon amber"><i class="bi bi-share-fill"></i></div>
            <div>
                <div class="kx-stat-val">{{ $totalReferrals }}</div>
                <div class="kx-stat-label">Referrals</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kx-stat">
            <div class="kx-stat-icon purple"><i class="bi bi-gift-fill"></i></div>
            <div>
                <div class="kx-stat-val">&#8358;{{ number_format($totalReferralRewards, 0) }}</div>
                <div class="kx-stat-label">Ref. Rewards</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kx-stat" style="cursor: default;">
            <div class="kx-stat-icon {{ $swapEnabled ? 'green' : 'purple' }}">
                <i class="bi bi-currency-bitcoin"></i>
            </div>
            <div>
                <div class="kx-stat-val">{{ $totalConversions }}</div>
                <div class="kx-stat-label">Swaps</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kx-stat"><div class="kx-stat-icon blue"><i class="bi bi-person-plus-fill"></i></div><div><div class="kx-stat-val">{{ $newUsersThisMonth ?? 0 }}</div><div class="kx-stat-label">New this month</div></div></div>
    </div>
</div>

<div class="kx-section-heading">
    <div><h2>Operations shortcuts</h2><p>Jump straight to the work that keeps the platform moving.</p></div>
</div>
<!-- QUICK NAV -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ url('/admin/withdrawals') }}" class="kx-nav-card">
            <div class="kx-stat-icon orange"><i class="bi bi-arrow-up-circle"></i></div>
            <div>
                <div class="kx-nav-card-title">Withdrawals</div>
                <div class="kx-nav-card-sub"><span id="nav-withdrawals-text">Loading&hellip;</span></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ url('/admin/kyc') }}" class="kx-nav-card">
            <div class="kx-stat-icon blue"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="kx-nav-card-title">KYC Review</div>
                <div class="kx-nav-card-sub"><span id="nav-kyc-text">Loading&hellip;</span></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ url('/admin/trades') }}" class="kx-nav-card">
            <div class="kx-stat-icon red"><i class="bi bi-arrow-left-right"></i></div>
            <div>
                <div class="kx-nav-card-title">Pending Trades</div>
                <div class="kx-nav-card-sub"><span id="nav-trades-text">Loading&hellip;</span></div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ url('/admin/notifications') }}" class="kx-nav-card">
            <div class="kx-stat-icon green"><i class="bi bi-bell-fill"></i></div>
            <div>
                <div class="kx-nav-card-title">Notifications <span id="nav-notif-badge" class="kx-mini-badge d-none">0</span></div>
                <div class="kx-nav-card-sub">Send &amp; manage</div>
            </div>
        </a>
    </div>
</div>

<!-- MAIN CONTENT GRID -->
<div class="row g-4">

    <!-- LEFT: crypto rates + company account -->
    <div class="col-lg-7">

        <div class="kx-panel">
            <div class="kx-panel-header">
                <h5><i class="bi bi-currency-bitcoin me-2" style="color:var(--kx-amber)"></i>Crypto Exchange Rates</h5>
                <span style="font-size:0.75rem;color:var(--kx-muted)">&#8358; per USD</span>
            </div>
            <div class="kx-panel-body">
                <form id="rates-form">
                    <div id="rates-container">
                        <div class="kx-skeleton mb-3" style="height:48px;"></div>
                        <div class="kx-skeleton mb-3" style="height:48px;"></div>
                        <div class="kx-skeleton" style="height:48px;"></div>
                    </div>
                    <div id="rates-feedback" class="mt-3"></div>
                    <div class="mt-4">
                        <button type="submit" id="update-btn" class="btn-kx-green">
                            <span id="rates-spinner" class="kx-spin"></span>
                            <i class="bi bi-check-lg"></i> Save Rates
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="kx-panel" id="company-account">
            <div class="kx-panel-header">
                <h5><i class="bi bi-building me-2" style="color:var(--kx-info)"></i>Company Bank Account</h5>
            </div>
            <div class="kx-panel-body">
                <form id="company-account-form" action="{{ route('admin.company-account') }}" method="POST">
                    @csrf
                    <div id="account-container"></div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="kx-label">Bank Name / Network</label>
                            <input type="text" id="bank_name" name="bank_name" class="form-control kx-input" placeholder="e.g. GTBank or TRC20" required>
                            <small class="text-danger error-text d-block mt-1" style="font-size:0.75rem"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="kx-label">Account Number / Wallet Address</label>
                            <input type="text" id="account_number" name="account_number" class="form-control kx-input" placeholder="Account no. or wallet address" required>
                            <small class="text-danger error-text d-block mt-1" style="font-size:0.75rem"></small>
                        </div>
                        <div class="col-md-4">
                            <label class="kx-label">Account Name</label>
                            <input type="text" id="account_name" name="account_name" class="form-control kx-input" placeholder="Account holder" required>
                            <small class="text-danger error-text d-block mt-1" style="font-size:0.75rem"></small>
                        </div>
                    </div>
                    <div id="account-form-feedback" class="mt-3"></div>
                    <div class="mt-4">
                        <button type="submit" id="update-account-btn" class="btn-kx-green">
                            <span id="account-spinner" class="kx-spin"></span>
                            <i class="bi bi-save"></i> Update Account
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Crypto services panel --}}
        <div class="kx-panel">
            <div class="kx-panel-header">
                <h5><i class="bi bi-currency-bitcoin me-2" style="color:{{ $cryptoDepositsEnabled || $swapEnabled ? 'var(--kx-green)' : 'var(--kx-muted)' }}"></i>Crypto services</h5>
                <span class="badge {{ $cryptoDepositsEnabled ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.7rem;">
                    {{ $cryptoDepositProviderName }}
                </span>
            </div>
            <div class="kx-panel-body">
                @if($cryptoDepositsEnabled || $swapEnabled)
                    <div class="row g-3">
                        <div class="col-4 text-center">
                            <div style="font-size:1.5rem;font-weight:700;color:var(--kx-green);">{{ $totalConversions }}</div>
                            <div style="font-size:0.75rem;color:var(--kx-muted);">Total swaps</div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="font-size:1.5rem;font-weight:700;color:var(--kx-amber);">{{ $pendingConversions }}</div>
                            <div style="font-size:0.75rem;color:var(--kx-muted);">Pending</div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="font-size:1.5rem;font-weight:700;color:var(--kx-info);">{{ $completedConversions }}</div>
                            <div style="font-size:0.75rem;color:var(--kx-muted);">Completed</div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="font-size:0.85rem;color:var(--kx-text);">Crypto deposits</span>
                            <span class="badge {{ $cryptoDepositsEnabled ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.7rem;">{{ $cryptoDepositsEnabled ? 'Active' : 'Off' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span style="font-size:0.85rem;color:var(--kx-text);">Swaps</span>
                            <span class="badge {{ $swapEnabled ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.7rem;">{{ $swapEnabled ? 'Active' : 'Off' }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-currency-bitcoin" style="font-size:2rem;color:var(--kx-muted);margin-bottom:0.5rem;"></i>
                        <div style="color:var(--kx-muted);font-size:0.9rem;">Crypto deposit and swap services are currently off</div>
                        <div style="color:var(--kx-muted);font-size:0.8rem;margin-top:0.25rem;">Review payment provider settings to enable them.</div>
                        <a href="{{ route('admin.settings.index') }}#payment" class="btn-kx-outline mt-3"><i class="bi bi-sliders"></i>Payment settings</a>
                    </div>
                @endif
            </div>
        </div>

    </div><!-- /LEFT -->

    <!-- RIGHT: notifications + quick actions -->
    <div class="col-lg-5">

        <div class="kx-panel">
            <div class="kx-panel-header">
                <h5><i class="bi bi-bell me-2" style="color:var(--kx-green)"></i>Recent Notifications <span id="recent-notif-badge" class="kx-mini-badge d-none">0</span></h5>
                <a href="{{ url('/admin/notifications') }}" class="btn-kx-outline">
                    <i class="bi bi-arrow-right"></i> View all
                </a>
            </div>
            <div class="kx-panel-body" style="padding:14px 22px;">
                <div id="recentNotifications">
                    <div class="kx-skeleton mb-3" style="height:60px;"></div>
                    <div class="kx-skeleton mb-3" style="height:60px;"></div>
                    <div class="kx-skeleton" style="height:60px;"></div>
                </div>
            </div>
        </div>

        <div class="kx-panel">
            <div class="kx-panel-header">
                <h5><i class="bi bi-lightning-fill me-2" style="color:var(--kx-amber)"></i>Quick Actions</h5>
            </div>
            <div class="kx-panel-body d-flex flex-column gap-3">
                <a href="{{ route('admin.deposits.index') }}" class="kx-nav-card" style="padding:14px;height:auto">
                    <div class="kx-stat-icon blue" style="width:38px;height:38px;font-size:1rem"><i class="bi bi-wallet2"></i></div>
                    <div>
                        <div class="kx-nav-card-title">Deposits</div>
                        <div class="kx-nav-card-sub">Review and approve deposits</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
                <a href="{{ route('admin.crypto-withdrawals.index') }}" class="kx-nav-card" style="padding:14px;height:auto">
                    <div class="kx-nav-card-title">Crypto withdrawals</div><div class="kx-nav-card-sub">Review wallet payouts</div>
                </a>
                <a href="{{ route('admin.withdrawals') }}" class="kx-nav-card" style="padding:14px;height:auto">
                    <div class="kx-stat-icon orange" style="width:38px;height:38px;font-size:1rem"><i class="bi bi-bank"></i></div>
                    <div>
                        <div class="kx-nav-card-title">Withdrawals</div>
                        <div class="kx-nav-card-sub">Approve pending payouts</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
                <a href="{{ url('/admin/notifications') }}" class="kx-nav-card" style="padding:14px;height:auto">
                    <div class="kx-stat-icon green" style="width:38px;height:38px;font-size:1rem"><i class="bi bi-bell-plus"></i></div>
                    <div>
                        <div class="kx-nav-card-title">Send Notification</div>
                        <div class="kx-nav-card-sub">Broadcast to users</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
                <a href="{{ url('/admin/users') }}" class="kx-nav-card" style="padding:14px;height:auto">
                    <div class="kx-stat-icon amber" style="width:38px;height:38px;font-size:1rem"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="kx-nav-card-title">Manage Users</div>
                        <div class="kx-nav-card-sub">View &amp; edit accounts</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
                <a href="{{ url('/admin/sells') }}" class="kx-nav-card" style="padding:14px;height:auto">
                    <div class="kx-stat-icon blue" style="width:38px;height:38px;font-size:1rem"><i class="bi bi-arrow-right-left"></i></div>
                    <div>
                        <div class="kx-nav-card-title">Sell Trades</div>
                        <div class="kx-nav-card-sub">Review sell orders</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
                <a href="{{ url('/admin/crypto-rates') }}" class="kx-nav-card" style="padding:14px;height:auto">
                    <div class="kx-stat-icon purple" style="width:38px;height:38px;font-size:1rem"><i class="bi bi-currency-bitcoin"></i></div>
                    <div>
                        <div class="kx-nav-card-title">Crypto Rates Table</div>
                        <div class="kx-nav-card-sub">Full rates management</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
                @if($swapEnabled)
                <a href="{{ route('admin.conversions.index') }}" class="kx-nav-card" style="padding:14px;height:auto">
                    <div class="kx-stat-icon green" style="width:38px;height:38px;font-size:1rem"><i class="bi bi-arrow-left-right"></i></div>
                    <div>
                        <div class="kx-nav-card-title">Crypto swaps</div>
                        <div class="kx-nav-card-sub">Review and manage user swaps</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
                @endif
            </div>
        </div>

        <div class="kx-panel">
            <div class="kx-panel-header">
                <h5><i class="bi bi-grid-3x3-gap-fill me-2" style="color:var(--kx-info)"></i>Admin Modules</h5>
            </div>
            <div class="kx-panel-body">
                <div class="row g-2">
                    <div class="col-6"><a href="{{ url('/admin/users') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-people me-1"></i>Users</a></div>
                    <div class="col-6"><a href="{{ url('/admin/trades') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-arrow-left-right me-1"></i>Trades</a></div>
                    <div class="col-6"><a href="{{ route('admin.deposits.index') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-wallet2 me-1"></i>Deposits</a></div>
                    <div class="col-6"><a href="{{ route('admin.withdrawals') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-bank me-1"></i>Withdrawals</a></div>
                    <div class="col-6"><a href="{{ url('/admin/kyc') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-patch-check me-1"></i>KYC</a></div>
                    <div class="col-6"><a href="{{ url('/admin/notifications') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-bell me-1"></i>Notifications</a></div>
                    <div class="col-6"><a href="{{ url('/admin/crypto-rates') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-currency-bitcoin me-1"></i>Crypto Rates</a></div>
                    <div class="col-6"><a href="{{ url('/admin/gift-card-rates') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-gift me-1"></i>Gift Card Rates</a></div>
                    <div class="col-6"><a href="{{ route('admin.gift-card-orders.index') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-bag-check me-1"></i>Gift Card Purchases</a></div>
                    <div class="col-6"><a href="{{ route('admin.gift-card-sales.index') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-credit-card-2-front me-1"></i>Gift Card Sales</a></div>
                    @if($swapEnabled)
                    <div class="col-6"><a href="{{ route('admin.conversions.index') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-arrow-left-right me-1"></i>Swaps</a></div>
                    @endif
                    <div class="col-6"><a href="{{ url('/admin/blog') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-journal-richtext me-1"></i>Blog</a></div>
                    <div class="col-6"><a href="{{ url('/admin/site-content') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-pencil-square me-1"></i>Site Content</a></div>
                    <div class="col-6"><a href="{{ route('admin.onboarding-content.index') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-person-check me-1"></i>Onboarding Page</a></div>
                    <div class="col-6"><a href="{{ route('install.complete') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-clipboard-check me-1"></i>Installation Status</a></div>
                    <div class="col-6"><a href="{{ url('/admin/chat') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-chat-dots me-1"></i>Admin Chat</a></div>
                    <div class="col-6"><a href="{{ url('/admin/email-settings') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-envelope-gear me-1"></i>Email Settings</a></div>
                    <div class="col-6"><a href="{{ route('admin.email-templates') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-envelope-paper me-1"></i>Email Templates</a></div>
                    <div class="col-6"><a href="{{ url('/admin/env-editor') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-key me-1"></i>API Keys</a></div>
                    <div class="col-6"><a href="{{ route('admin.diagnostics') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-stethoscope me-1"></i>Diagnostics</a></div>
                    <div class="col-6"><a href="{{ url('/admin/telegram') }}" class="btn-kx-outline w-100 justify-content-center"><i class="bi bi-telegram me-1"></i>Telegram Bot</a></div>
                </div>
            </div>
        </div>

    </div><!-- /RIGHT -->
</div>

<section class="kx-panel" aria-labelledby="admin-ops-guide-title">
    <div class="kx-panel-header">
        <h5 id="admin-ops-guide-title"><i class="bi bi-compass me-2" style="color:var(--kx-green)"></i>Gift Cards &amp; Chatbots — Admin Guide</h5>
        <a href="{{ route('admin.gift-card-rates.index') }}" class="btn-kx-outline"><i class="bi bi-sliders"></i> Manage rates</a>
    </div>
    <div class="kx-panel-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="kx-card h-100" style="padding:1rem;border:1px solid var(--kx-border);border-radius:12px;background:var(--kx-card2)">
                    <strong><i class="bi bi-toggle-on me-1 text-success"></i>1. Configure availability</strong>
                    <p class="mb-2 mt-2" style="font-size:.82rem;color:var(--kx-muted)">Set active USA/UK card rates and purchase service fees. Gift-card purchases and sales have separate switches; sales stay off until you enable them.</p>
                    <a href="{{ route('admin.gift-card-rates.index') }}" class="btn-kx-outline">Rates &amp; switches</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kx-card h-100" style="padding:1rem;border:1px solid var(--kx-border);border-radius:12px;background:var(--kx-card2)">
                    <strong><i class="bi bi-shield-check me-1 text-warning"></i>2. Review every sale</strong>
                    <p class="mb-2 mt-2" style="font-size:.82rem;color:var(--kx-muted)">Purchases reserve NGN after PIN confirmation and await fulfillment. Sales remain pending; verify the card and approve it before any NGN credit.</p>
                    <div class="d-flex flex-wrap gap-2"><a href="{{ route('admin.gift-card-orders.index') }}" class="btn-kx-outline">Purchase queue</a><a href="{{ route('admin.gift-card-sales.index') }}" class="btn-kx-outline">Sale queue</a></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kx-card h-100" style="padding:1rem;border:1px solid var(--kx-border);border-radius:12px;background:var(--kx-card2)">
                    <strong><i class="bi bi-chat-dots me-1 text-info"></i>3. Chat entry points</strong>
                    <p class="mb-2 mt-2" style="font-size:.82rem;color:var(--kx-muted)">Telegram users can use <code>/giftcards</code> or ask to buy/sell a card. Signed-in users can start the same conversational flow in KayBot. Gift-card codes and purchase PINs are handled as secure inputs.</p>
                    <a href="{{ route('admin.telegram.index') }}" class="btn-kx-outline">Telegram bot settings</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── AI Report Generator Card ────────────────────────────────────────── --}}
<div class="kx-panel" style="margin-top:1.5rem">
    <div class="kx-panel-header">
        <h5><i class="bi bi-robot me-2" style="color:var(--kx-green)"></i>AI Report Generator</h5>
    </div>
    <div style="padding:1.25rem">
        <p style="font-size:.82rem;color:var(--kx-muted);margin-bottom:1rem">Ask anything about platform performance in plain English. e.g. <em>"How many trades happened this week?"</em> or <em>"What are the top coins this month?"</em></p>
        <form id="ai-report-form" style="display:flex;gap:.75rem;flex-wrap:wrap">
            <input id="ai-report-query" type="text" class="kx-input" style="flex:1;min-width:260px" placeholder="e.g. Summarise today's trading activity…">
            <button id="ai-report-btn" type="submit" class="btn-kx-green"><i class="bi bi-robot me-1"></i>Generate Report</button>
        </form>
        <div id="ai-report-out" style="margin-top:1rem;font-size:.84rem;color:var(--kx-text);line-height:1.75;"></div>
    </div>
</div>
</div><!-- /.admin-dashboard -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    // Live clock
    const clockEl = document.getElementById('kx-clock');
    function tick() {
        const now = new Date();
        clockEl.textContent = now.toLocaleDateString('en-NG', { weekday:'short', year:'numeric', month:'short', day:'numeric' })
            + '  ' + now.toLocaleTimeString('en-NG', { hour:'2-digit', minute:'2-digit' });
    }
    tick(); setInterval(tick, 60000);

    const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

    // ── Site Mode Toggle ──────────────────────────────────────────────────────
    const modeMessages = {
        local: {
            icon: '🛠️',
            title: 'Switch to Local Mode?',
            text: 'Local mode shows detailed diagnostics to administrators only. Public visitors continue to receive safe error pages. Use this briefly while troubleshooting.',
            btnLabel: 'Yes, switch to Local Mode',
        },
        production: {
            icon: '🚀',
            title: 'Switch to Production Mode?',
            text: 'Production mode returns the site to normal operation with user-friendly errors and optimised performance.',
            btnLabel: 'Yes, switch to Production',
        }
    };

    function initModeToggle(btn) {
        if (!btn) return;
        btn.addEventListener('click', () => {
            const current = btn.dataset.mode;
            const next    = current === 'local' ? 'production' : 'local';
            const cfg     = modeMessages[next];
            document.getElementById('kx-mode-confirm-icon').textContent  = cfg.icon;
            document.getElementById('kx-mode-confirm-title').textContent = cfg.title;
            document.getElementById('kx-mode-confirm-text').innerHTML    = cfg.text;
            const confirmBtn = document.getElementById('kx-mode-confirm-btn');
            confirmBtn.textContent = cfg.btnLabel;
            confirmBtn.className   = next === 'local' ? 'btn-kx-amber' : 'btn-kx-green';
            document.getElementById('kx-mode-post-msg').style.display = 'none';
            document.getElementById('kx-mode-overlay').classList.add('active');
        });
    }
    initModeToggle(document.getElementById('kx-mode-toggle-btn'));
    initModeToggle(document.getElementById('kx-mode-toggle-btn-sm'));

    document.getElementById('kx-mode-confirm-btn').addEventListener('click', function () {
        const desktopBtn = document.getElementById('kx-mode-toggle-btn');
        const current    = desktopBtn ? desktopBtn.dataset.mode : 'production';
        const next       = current === 'local' ? 'production' : 'local';
        const msgEl      = document.getElementById('kx-mode-post-msg');
        this.disabled    = true;
        this.textContent = 'Switching…';

        fetch('{{ route("admin.site-mode.toggle") }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            const newMode  = data.mode;
            const isLocal = newMode === 'local';
            const cssClass = isLocal ? 'mode-local' : 'mode-production';
            const label    = isLocal ? 'Local Mode' : 'Production Mode';
            [document.getElementById('kx-mode-toggle-btn'), document.getElementById('kx-mode-toggle-btn-sm')].forEach(b => {
                if (!b) return;
                b.dataset.mode  = newMode;
                b.className     = 'kx-mode-badge ' + cssClass;
                b.classList.remove('mode-off');
                b.setAttribute('aria-checked', isLocal ? 'true' : 'false');
                const textSpan = b.querySelector('.kx-mode-label');
                if (textSpan) textSpan.textContent = label;
                const icon = b.querySelector('.kx-mode-switch-knob i');
                if (icon) icon.className = 'bi ' + (isLocal ? 'bi-code-slash' : 'bi-shield-check');
            });
            msgEl.style.display = 'block';
            msgEl.style.color   = isLocal ? 'var(--kx-amber)' : 'var(--kx-green)';
            msgEl.textContent   = '✓ Switched to ' + label;
            setTimeout(() => document.getElementById('kx-mode-overlay').classList.remove('active'), 1200);
        })
        .catch(() => {
            msgEl.style.display = 'block';
            msgEl.style.color   = 'var(--kx-danger)';
            msgEl.textContent   = '✗ Failed to switch mode. Please try again.';
        })
        .finally(() => {
            this.disabled    = false;
            this.textContent = modeMessages[next].btnLabel;
        });
    });

    // Pending counts
    const fetchCounts = () => {
        fetch('{{ route("admin.pending-counts") }}', { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': csrf() } })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            const w = data.pending_withdrawals || 0;
            const k = data.pending_kyc        || 0;
            const t = data.pending_trades     || 0;
            const setCount = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
            setCount('stat-withdrawals', w);
            setCount('stat-kyc', k);
            setCount('stat-trades', t);
            setCount('stat-deposits', data.pending_deposits || 0);
            setCount('stat-crypto-deposits', data.crypto_deposits_open || 0);
            setCount('stat-crypto-withdrawals', data.crypto_withdrawals || 0);
            setCount('stat-bills', data.bill_requests || 0);
            setCount('stat-support', data.open_support || 0);
            document.getElementById('nav-withdrawals-text').textContent = w ? w + ' pending' : 'All clear';
            document.getElementById('nav-kyc-text').textContent         = k ? k + ' pending' : 'All clear';
            document.getElementById('nav-trades-text').textContent      = t ? t + ' pending' : 'All clear';
        })
        .catch(() => {});
    };
    fetchCounts(); setInterval(fetchCounts, 30000);

    // Load crypto rates (server-rendered, no AJAX needed)
    (function() {
        const data = @json($cryptoRates);
        const container = document.getElementById('rates-container');
        if (!data || !data.length) {
            container.innerHTML = '<p style="color:var(--kx-muted);margin:0">No rates configured yet. <a href="{{ url("/admin/crypto-rates") }}" style="color:var(--kx-green)">Add rates →</a></p>';
            return;
        }
        container.innerHTML = data.map(rate => `
            <div class="rate-row">
                <div>
                    <div class="rate-coin-name">
                        <span class="rate-coin-icon">${rate.coin[0]}</span>
                        ${rate.coin}
                    </div>
                    <input type="hidden" name="coin" value="${rate.coin}">
                </div>
                <div>
                    <label class="kx-label">Buy Rate (&#8358;)</label>
                    <input type="number" step="0.01" class="form-control kx-input" name="buy_rate" value="${parseFloat(rate.buy_rate).toFixed(2)}" required>
                </div>
                <div>
                    <label class="kx-label">Sell Rate (&#8358;)</label>
                    <input type="number" step="0.01" class="form-control kx-input" name="sell_rate" value="${parseFloat(rate.sell_rate).toFixed(2)}" required>
                </div>
            </div>`).join('');
    })();

    // Save rates
    document.getElementById('rates-form').addEventListener('submit', e => {
        e.preventDefault();
        const feedback = document.getElementById('rates-feedback');
        const btn  = document.getElementById('update-btn');
        const spin = document.getElementById('rates-spinner');
        feedback.innerHTML = ''; btn.disabled = true; spin.style.display = 'inline-block';
        const rates = [];
        document.querySelectorAll('#rates-container .rate-row').forEach(row => {
            const coin      = row.querySelector('input[name="coin"]').value;
            const buy_rate  = row.querySelector('input[name="buy_rate"]').value;
            const sell_rate = row.querySelector('input[name="sell_rate"]').value;
            if (coin && buy_rate && sell_rate) rates.push({ coin, buy_rate: parseFloat(buy_rate), sell_rate: parseFloat(sell_rate) });
        });
        fetch('{{ route("admin.rates.update") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ rates })
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(() => { feedback.innerHTML = '<div class="kx-alert-success"><i class="bi bi-check-circle me-2"></i>Rates updated successfully!</div>'; setTimeout(() => feedback.innerHTML = '', 3500); })
        .catch(() => { feedback.innerHTML = '<div class="kx-alert-error"><i class="bi bi-x-circle me-2"></i>Failed to update rates.</div>'; })
        .finally(() => { btn.disabled = false; spin.style.display = 'none'; });
    });

    // Prefill company account
    fetch('{{ route("admin.company-account") }}', { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': csrf() } })
    .then(r => r.ok ? r.json() : Promise.reject())
    .then(data => {
        document.getElementById('account-container').innerHTML = '';
        if (data) {
            document.getElementById('bank_name').value      = data.bank_name      || '';
            document.getElementById('account_number').value = data.account_number || '';
            document.getElementById('account_name').value   = data.account_name   || '';
        }
    })
    .catch(() => { document.getElementById('account-container').innerHTML = '<p style="color:var(--kx-muted);font-size:0.8rem" class="mb-3">Could not pre-fill details.</p>'; });

    // Save company account
    document.getElementById('company-account-form').addEventListener('submit', e => {
        e.preventDefault();
        const feedback = document.getElementById('account-form-feedback');
        const btn  = document.getElementById('update-account-btn');
        const spin = document.getElementById('account-spinner');
        feedback.innerHTML = ''; btn.disabled = true; spin.style.display = 'inline-block';
        fetch('{{ route("admin.company-account.update") }}', {
            method: 'POST',
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': csrf() },
            body: new FormData(document.getElementById('company-account-form'))
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(() => { feedback.innerHTML = '<div class="kx-alert-success"><i class="bi bi-check-circle me-2"></i>Account details saved!</div>'; setTimeout(() => feedback.innerHTML = '', 3500); })
        .catch(() => {
            feedback.innerHTML = '<div class="kx-alert-error"><i class="bi bi-x-circle me-2"></i>Failed to save account details.</div>';
            document.querySelectorAll('#company-account-form .kx-input').forEach(i => { i.classList.add('error'); setTimeout(() => i.classList.remove('error'), 900); });
        })
        .finally(() => { btn.disabled = false; spin.style.display = 'none'; });
    });

    ['bank_name','account_number','account_name'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() { this.classList.remove('error'); });
    });

    const escapeAdminText = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    // Keep admin alerts fresh while the dashboard stays open.
    const loadAdminRecentNotifications = () => fetch('{{ route("admin.notifications.index") }}?per_page=5', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf()
        }
    })
    .then(r => r.json())
    .then(data => {
        const el = document.getElementById('recentNotifications');
        const list = (data.notifications && data.notifications.data) ? data.notifications.data : [];
        const unread = (data.stats && typeof data.stats.unread !== 'undefined') ? Number(data.stats.unread) : 0;
        const navBadge = document.getElementById('nav-notif-badge');
        const recentBadge = document.getElementById('recent-notif-badge');
        if (navBadge && recentBadge) {
            if (unread > 0) {
                navBadge.textContent = unread > 99 ? '99+' : String(unread);
                recentBadge.textContent = unread > 99 ? '99+' : String(unread);
                navBadge.classList.remove('d-none');
                recentBadge.classList.remove('d-none');
            } else {
                navBadge.classList.add('d-none');
                recentBadge.classList.add('d-none');
            }
        }
        if (!list.length) { el.innerHTML = '<p style="color:var(--kx-muted);text-align:center;padding:16px 0">No notifications yet.</p>'; return; }
        el.innerHTML = list.map(n => `
            <div class="notif-item">
                <div class="notif-icon-wrap"><i class="bi ${n.icon || 'bi-bell'}"></i></div>
                <div class="flex-grow-1">
                    <div class="notif-title">${escapeAdminText(n.title)}</div>
                    <div class="notif-text">${escapeAdminText(n.message)}</div>
                    <div class="notif-meta">
                        <span>${escapeAdminText(n.created_at)}</span>
                        ${n.is_broadcast ? '<span class="tag-broadcast">Broadcast</span>' : ''}
                        ${!n.is_read     ? '<span class="tag-unread">Unread</span>' : ''}
                    </div>
                </div>
            </div>`).join('');
    })
    .catch(() => { document.getElementById('recentNotifications').innerHTML = '<p style="color:var(--kx-danger);text-align:center;padding:16px 0">Failed to load notifications.</p>'; });
    loadAdminRecentNotifications();
    setInterval(loadAdminRecentNotifications, 30000);

});
</script>

{{-- ── AI Report Generator ──────────────────────────────────────────────── --}}
<script>
(function(){
    const form  = document.getElementById('ai-report-form');
    const input = document.getElementById('ai-report-query');
    const out   = document.getElementById('ai-report-out');
    const btn   = document.getElementById('ai-report-btn');
    if (!form) return;
    form.addEventListener('submit', async function(e){
        e.preventDefault();
        const q = input.value.trim();
        if (!q) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating…';
        out.innerHTML = '<div style="text-align:center;padding:1rem;color:var(--kx-muted);font-size:.82rem">Processing your query…</div>';
        try {
            const res  = await fetch('{{ route("ai.report") }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({query: q})
            });
            const data = await res.json();
            out.innerHTML = data.report || '<span style="color:var(--kx-red)">'+(data.error||'Error')+'</span>';
        } catch(e) {
            out.innerHTML = '<span style="color:var(--kx-red)">Request failed: '+e.message+'</span>';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-robot me-1"></i>Generate Report';
        }
    });
})();
</script>
@endsection
