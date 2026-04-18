@extends('layout')

@section('content')
<style>

    :root {
        --primary-green: #00cc00;
        --primary-red: #ef4444;
        --transition: all 0.28s ease;
    }

    /* ===== Base ===== */
    body {
        background: #060e06;
        color: #e8f5e8;
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        overflow-x: hidden;
    }
    body.light-mode { background: #f2f7f2; color: #1a2e1a; }

    /* ===== Dashboard Container ===== */
    .kx-db { padding: 28px 0 70px; }

    /* ===== Cards ===== */
    .kx-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 20px;
        transition: border-color 0.28s ease, box-shadow 0.28s ease;
        position: relative;
        overflow: hidden;
    }
    .kx-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.02) 0%, transparent 60%);
        pointer-events: none;
        border-radius: 20px;
    }
    .kx-card:hover { border-color: rgba(0,204,0,0.18); box-shadow: 0 10px 36px rgba(0,0,0,0.32); }
    body.light-mode .kx-card { background: #fff; border-color: rgba(0,0,0,0.07); box-shadow: 0 2px 14px rgba(0,0,0,0.05); }
    body.light-mode .kx-card:hover { border-color: rgba(0,153,0,0.2); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }

    /* ===== Dashboard Header ===== */
    .kx-db-head { margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.06); }
    body.light-mode .kx-db-head { border-color: rgba(0,0,0,0.07); }
    .kx-db-label { font-size: 0.75rem; color: rgba(255,255,255,0.38); letter-spacing: 0.6px; text-transform: uppercase; font-weight: 500; margin-bottom: 4px; }
    body.light-mode .kx-db-label { color: rgba(0,0,0,0.38); }
    .kx-db-name { font-size: clamp(1.5rem,3.5vw,2.3rem); font-weight: 800; color: #fff; margin: 0 0 12px; line-height: 1.15; }
    body.light-mode .kx-db-name { color: #0a1a0a; }
    .kx-db-badge { display:inline-flex; align-items:center; gap:5px; font-size:0.7rem; font-weight:600; padding:4px 11px; border-radius:20px; }
    .kx-db-badge-ok  { background:rgba(0,204,0,0.12); border:1px solid rgba(0,204,0,0.28); color:#00cc00; }
    .kx-db-badge-warn{ background:rgba(255,165,0,0.12); border:1px solid rgba(255,165,0,0.28); color:#ffa500; }

    /* ===== Balance Card ===== */
    .kx-balance-card {
        background: linear-gradient(135deg, rgba(0,70,0,0.65) 0%, rgba(0,30,0,0.9) 100%);
        border: 1px solid rgba(0,204,0,0.2);
        border-radius: 22px;
        padding: 28px;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .kx-balance-card::before { content:''; position:absolute; top:-60px; right:-60px; width:200px; height:200px; background:radial-gradient(circle,rgba(0,204,0,0.14),transparent 70%); border-radius:50%; pointer-events:none; }
    .kx-balance-card::after  { content:''; position:absolute; bottom:-40px; left:-40px; width:150px; height:150px; background:radial-gradient(circle,rgba(0,204,0,0.08),transparent 70%); border-radius:50%; pointer-events:none; }
    body.light-mode .kx-balance-card { background:linear-gradient(135deg,#e8f5e8 0%,#d4ecd4 100%); border-color:rgba(0,153,0,0.22); }
    .kx-balance-label { font-size:0.75rem; color:rgba(255,255,255,0.45); text-transform:uppercase; letter-spacing:0.8px; font-weight:600; margin-bottom:6px; }
    body.light-mode .kx-balance-label { color:rgba(0,80,0,0.55); }
    .kx-balance-amount { font-size:clamp(1.65rem,4vw,2.6rem); font-weight:800; color:#fff; line-height:1; margin-bottom:4px; }
    body.light-mode .kx-balance-amount { color:#0a2a0a; }
    .kx-balance-sub { font-size:0.75rem; color:rgba(255,255,255,0.3); }
    body.light-mode .kx-balance-sub { color:rgba(0,80,0,0.4); }
    .kx-usd-equiv {
        display:inline-flex; align-items:center; gap:.25rem;
        background:rgba(0,204,0,0.08); border:1px solid rgba(0,204,0,0.18);
        color:#4ade80; border-radius:20px; padding:.1rem .55rem;
        font-size:.7rem; font-weight:700; cursor:help;
    }
    body.light-mode .kx-usd-equiv { color:#007a0c; background:rgba(0,120,0,.08); border-color:rgba(0,120,0,.2); }
    .kx-bal-btn { display:inline-flex; align-items:center; gap:7px; font-size:0.82rem; font-weight:600; padding:10px 18px; border-radius:14px; text-decoration:none !important; transition:all 0.25s ease; border:none; cursor:pointer; flex:1; justify-content:center; position:relative; z-index:1; }
    .kx-bal-dep { background:rgba(0,136,204,0.2); border:1px solid rgba(0,136,204,0.3); color:#3ab5e5 !important; }
    .kx-bal-dep:hover { background:rgba(0,136,204,0.35); color:#fff !important; transform:translateY(-2px); }
    .kx-bal-wd  { background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.28); color:#fbbf24 !important; }
    .kx-bal-wd:hover  { background:rgba(251,191,36,0.28); color:#fff !important; transform:translateY(-2px); }

    /* ===== Stat Mini Cards ===== */
    .kx-stat { padding: 20px 22px; }
    .kx-stat-icon { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.18rem; margin-bottom:14px; }
    .kx-stat-icon-g { background:rgba(0,204,0,0.12); color:#00cc00; }
    .kx-stat-icon-b { background:rgba(0,136,204,0.12); color:#0088cc; }
    .kx-stat-icon-a { background:rgba(251,191,36,0.12); color:#fbbf24; }
    .kx-stat-icon-p { background:rgba(147,51,234,0.12); color:#9333ea; }
    .kx-stat-label { font-size:0.72rem; color:rgba(255,255,255,0.38); text-transform:uppercase; letter-spacing:0.6px; font-weight:500; margin-bottom:4px; }
    body.light-mode .kx-stat-label { color:rgba(0,0,0,0.38); }
    .kx-stat-val { font-size:1.3rem; font-weight:800; color:#fff; }
    body.light-mode .kx-stat-val { color:#0a1a0a; }

    /* ===== Quick Actions ===== */
    .kx-quick { display:grid; grid-template-columns:repeat(6,1fr); gap:12px; }
    .kx-qbtn {
        display:flex; flex-direction:column; align-items:center; gap:9px;
        padding:18px 10px; border-radius:18px;
        font-size:0.76rem; font-weight:600;
        text-decoration:none !important; transition:all 0.25s ease;
        background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07);
        cursor:pointer; color:rgba(255,255,255,0.72) !important;
    }
    body.light-mode .kx-qbtn { background:#fff; border-color:rgba(0,0,0,0.07); color:#1a2a1a !important; }
    .kx-qbtn:hover { transform:translateY(-3px); box-shadow:0 10px 28px rgba(0,0,0,0.3); color:#fff !important; }
    body.light-mode .kx-qbtn:hover { box-shadow:0 8px 20px rgba(0,0,0,0.1); border-color:rgba(0,120,0,0.2); }
    .kx-qicon { width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; transition:transform 0.25s ease; }
    .kx-qbtn:hover .kx-qicon { transform:scale(1.1); }
    .kx-qi-buy  .kx-qicon { background:rgba(0,204,0,0.12); color:#00cc00; }
    .kx-qi-sell .kx-qicon { background:rgba(239,68,68,0.12); color:#ef4444; }
    .kx-qi-dep  .kx-qicon { background:rgba(0,136,204,0.12); color:#0e9cda; }
    .kx-qi-wd   .kx-qicon { background:rgba(251,191,36,0.12); color:#fbbf24; }
    .kx-qi-hist .kx-qicon { background:rgba(147,51,234,0.12); color:#9333ea; }
    .kx-qi-ref  .kx-qicon { background:rgba(236,72,153,0.12); color:#ec4899; }

    /* ===== Tables ===== */
    .kx-db-tbl { width:100%; border-collapse:separate; border-spacing:0; }
    .kx-db-tbl th { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.7px; color:rgba(255,255,255,0.32); font-weight:600; padding:11px 16px; border-bottom:1px solid rgba(255,255,255,0.06); white-space:nowrap; }
    body.light-mode .kx-db-tbl th { color:rgba(0,0,0,0.35); border-color:rgba(0,0,0,0.07); }
    .kx-db-tbl td { padding:14px 16px; font-size:0.85rem; color:rgba(255,255,255,0.72); border-bottom:1px solid rgba(255,255,255,0.04); vertical-align:middle; white-space:nowrap; }
    body.light-mode .kx-db-tbl td { color:#2a3a2a; border-color:rgba(0,0,0,0.05); }
    .kx-db-tbl tbody tr:hover td { background:rgba(0,204,0,0.04); }
    body.light-mode .kx-db-tbl tbody tr:hover td { background:rgba(0,100,0,0.04); }
    .kx-db-tbl tbody tr:last-child td { border-bottom:none; }
    .kx-rate-buy  { color:#00cc00 !important; font-weight:600; }
    .kx-rate-sell { color:#ef4444 !important; font-weight:600; }
    .kx-coin-row  { display:flex; align-items:center; gap:8px; }
    .kx-coin-row img { width:26px; height:26px; border-radius:50%; flex-shrink:0; }

    /* ===== Live Price Rows ===== */
    .kx-pr-row { display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; transition:background 0.22s ease; }
    .kx-pr-row:hover { background:rgba(0,204,0,0.05); }
    body.light-mode .kx-pr-row:hover { background:rgba(0,80,0,0.04); }
    .kx-pr-img { width:34px; height:34px; border-radius:50%; flex-shrink:0; }
    .kx-pr-sym  { font-size:0.88rem; font-weight:700; color:#fff; }
    body.light-mode .kx-pr-sym { color:#0a1a0a; }
    .kx-pr-name { font-size:0.68rem; color:rgba(255,255,255,0.34); }
    body.light-mode .kx-pr-name { color:rgba(0,0,0,0.36); }
    .kx-pr-usd  { font-size:0.9rem; font-weight:700; color:#fff; white-space:nowrap; }
    body.light-mode .kx-pr-usd { color:#0a1a0a; }
    .kx-pr-chg  { font-size:0.7rem; font-weight:600; padding:2px 7px; border-radius:6px; white-space:nowrap; }
    .kx-pr-up { color:#00cc00; background:rgba(0,204,0,0.1); }
    .kx-pr-dn { color:#ef4444; background:rgba(239,68,68,0.1); }

    /* ===== Transaction Badges ===== */
    .kx-tx-badge { display:inline-flex; align-items:center; gap:4px; font-size:0.7rem; font-weight:600; padding:3px 9px; border-radius:8px; text-transform:capitalize; }
    .kx-tx-buy  { background:rgba(0,204,0,0.12); color:#00cc00; }
    .kx-tx-sell { background:rgba(239,68,68,0.12); color:#ef4444; }
    .kx-tx-wd   { background:rgba(251,191,36,0.12); color:#fbbf24; }
    .kx-tx-dep  { background:rgba(0,136,204,0.12); color:#0e9cda; }
    .kx-tx-other{ background:rgba(255,255,255,0.07); color:rgba(255,255,255,0.5); }
    .kx-status  { display:inline-block; font-size:0.7rem; font-weight:600; padding:3px 9px; border-radius:20px; text-transform:capitalize; }
    .kx-status-pending  { background:rgba(251,191,36,0.12); color:#fbbf24; }
    .kx-status-approved,.kx-status-completed { background:rgba(0,204,0,0.12); color:#00cc00; }
    .kx-status-rejected,.kx-status-failed    { background:rgba(239,68,68,0.12); color:#ef4444; }

    /* ===== Scrollable Table ===== */
    .kx-tx-scroll { overflow-x:auto; overflow-y:auto; max-height:340px; border-radius:0 0 14px 14px; }
    .kx-tx-scroll::-webkit-scrollbar { width:4px; height:4px; }
    .kx-tx-scroll::-webkit-scrollbar-track { background:transparent; }
    .kx-tx-scroll::-webkit-scrollbar-thumb { background:rgba(0,204,0,0.25); border-radius:4px; }

    /* ===== Section Title ===== */
    .kx-sec-title { font-size:1rem; font-weight:700; color:#fff; display:flex; align-items:center; gap:9px; flex-wrap:wrap; }
    body.light-mode .kx-sec-title { color:#0a1a0a; }
    .kx-sec-title i { color:#00cc00; font-size:1rem; }
    .kx-sec-tag { font-size:0.68rem; background:rgba(0,204,0,0.1); border:1px solid rgba(0,204,0,0.2); color:#00cc00; padding:3px 9px; border-radius:20px; font-weight:600; }

    /* ===== Inputs ===== */
    .kx-db-inp { background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:#fff; font-size:0.82rem; padding:8px 14px; transition:border-color 0.22s,box-shadow 0.22s; outline:none; font-family:'Poppins',sans-serif; }
    .kx-db-inp:focus { border-color:rgba(0,204,0,0.42); box-shadow:0 0 0 3px rgba(0,204,0,0.08); }
    body.light-mode .kx-db-inp { background:#f8fdf8; border-color:rgba(0,0,0,0.1); color:#1a2a1a; }
    .kx-db-inp::placeholder { color:rgba(255,255,255,0.28); }
    body.light-mode .kx-db-inp::placeholder { color:rgba(0,0,0,0.28); }

    /* ===== Mode / Notif Buttons ===== */
    .kx-hbtn { width:40px; height:40px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); border-radius:12px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1rem; color:rgba(255,255,255,0.58); transition:all 0.22s ease; flex-shrink:0; outline:none; }
    .kx-hbtn:hover { background:rgba(255,255,255,0.12); color:#fff; }
    body.light-mode .kx-hbtn { background:#fff; border-color:rgba(0,0,0,0.1); color:#1a2a1a; }
    .kx-notif-dot { position:absolute; top:7px; right:7px; width:7px; height:7px; background:#00cc00; border-radius:50%; border:2px solid #060e06; display:none; }
    body.light-mode .kx-notif-dot { border-color:#f2f7f2; }

    /* ===== View More Button ===== */
    .kx-btn-more { display:inline-flex; align-items:center; gap:7px; background:transparent; border:1.5px solid rgba(0,204,0,0.35); color:#00cc00 !important; text-decoration:none !important; padding:9px 22px; border-radius:50px; font-weight:600; font-size:0.85rem; transition:all 0.25s ease; }
    .kx-btn-more:hover { background:rgba(0,204,0,0.1); border-color:#00cc00; transform:translateY(-1px); }

    /* ===== Skeletons ===== */
    .kx-skel { background:rgba(255,255,255,0.06); border-radius:8px; animation:kxSkel 1.4s ease-in-out infinite; }
    body.light-mode .kx-skel { background:rgba(0,0,0,0.06); }
    @keyframes kxSkel { 0%,100%{opacity:0.45} 50%{opacity:0.85} }
    @keyframes kx-pulse { 0%,100%{opacity:0.6} 50%{opacity:1} }

    /* ===== Notification Dropdown ===== */
    .kx-dropdown { background:#0f1e0f; border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:8px; min-width:300px; max-width:340px; box-shadow:0 16px 48px rgba(0,0,0,0.5); max-height:420px; overflow-y:auto; }
    body.light-mode .kx-dropdown { background:#fff; border-color:rgba(0,0,0,0.08); box-shadow:0 8px 28px rgba(0,0,0,0.12); }
    .kx-notif-item { display:flex; align-items:flex-start; gap:10px; padding:10px 12px; border-radius:10px; font-size:0.82rem; color:rgba(255,255,255,0.72); border:none; background:none; transition:background 0.2s; width:100%; }
    .kx-notif-item:hover { background:rgba(255,255,255,0.05) !important; }
    body.light-mode .kx-notif-item { color:#1a2a1a; }
    body.light-mode .kx-notif-unread { background:rgba(0,153,0,0.06) !important; }

    /* ===== Responsive ===== */
    @media (max-width:991.98px) { .kx-quick { grid-template-columns:repeat(3,1fr); } }
    @media (max-width:575.98px) {
        .kx-db { padding:18px 0 50px; }
        .kx-quick { grid-template-columns:repeat(3,1fr); gap:8px; }
        .kx-qbtn { padding:14px 8px; font-size:0.7rem; }
        .kx-qicon { width:40px; height:40px; font-size:1.05rem; }
        .kx-balance-amount { font-size:1.65rem; }
        .kx-sec-title { flex-direction:column; align-items:flex-start; gap:8px; }
    }
</style>

<div class="kx-db">
    <div class="container-xl">

        <!-- ===== Dashboard Header ===== -->
        <div class="kx-db-head">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                <div>
                    <div class="kx-db-label">Dashboard</div>
                    <h1 class="kx-db-name">{{ Auth::user()->name }}</h1>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if(Auth::user()->kyc_verified)
                            <span class="kx-db-badge kx-db-badge-ok"><i class="bi bi-patch-check-fill"></i>KYC Verified</span>
                        @else
                            <span class="kx-db-badge kx-db-badge-warn"><i class="bi bi-exclamation-triangle-fill"></i>KYC Pending</span>
                        @endif
                        <span style="font-size:0.72rem;color:rgba(255,255,255,0.26)">{{ now()->format('l, d M Y') }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2" style="padding-top:4px">
                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="kx-hbtn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="position:relative">
                            <i class="bi bi-bell-fill"></i>
                            <span class="kx-notif-dot" id="notif-dot"></span>
                        </button>
                        <ul class="dropdown-menu kx-dropdown" id="notifications-container">
                            <li class="kx-notif-item">
                                <div class="spinner-border spinner-border-sm me-2" style="color:#00cc00;margin-top:2px;flex-shrink:0" role="status"></div>
                                Loading notifications...
                            </li>
                        </ul>
                    </div>
                    <!-- Mode Toggle -->
                    <button class="kx-hbtn" id="modeToggleBtn" title="Toggle light/dark mode">
                        <i class="bi bi-moon-stars-fill" id="modeIcon"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== Balance + Stats Row ===== -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="kx-balance-card">
                    <div class="kx-balance-label">Available Balance</div>
                    <div class="kx-balance-amount">₦{{ number_format(Auth::user()->balance, 2) }}</div>
                    @php
                        $usdEquiv = $usdtRate > 0 ? Auth::user()->balance / $usdtRate : 0;
                    @endphp
                    <div class="kx-balance-sub" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                        <span>KayXchange Naira Wallet</span>
                        @if($usdtRate > 0)
                        <span class="kx-usd-equiv" title="Based on USDT sell rate ₦{{ number_format($usdtRate,2) }}/USD">
                            ≈ ${{ number_format($usdEquiv, 2) }} USD
                        </span>
                        @endif
                    </div>
                    <div class="d-flex gap-2 mt-4" style="position:relative;z-index:1">
                        <a href="{{ route('deposits.index') }}" class="kx-bal-btn kx-bal-dep">
                            <i class="bi bi-arrow-down-circle-fill"></i>Deposit
                        </a>
                        <a href="{{ route('withdraw') }}" class="kx-bal-btn kx-bal-wd">
                            <i class="bi bi-arrow-up-circle-fill"></i>Withdraw
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-7 col-lg-8">
                <div class="row g-3 h-100">
                    <div class="col-6 col-sm-4">
                        <div class="kx-card kx-stat h-100">
                            <div class="kx-stat-icon kx-stat-icon-g"><i class="bi bi-arrow-left-right"></i></div>
                            <div class="kx-stat-label">Total Trades</div>
                            <div class="kx-stat-val">{{ isset($transactions) ? count($transactions) : '0' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="kx-card kx-stat h-100">
                            <div class="kx-stat-icon kx-stat-icon-a"><i class="bi bi-clock-history"></i></div>
                            <div class="kx-stat-label">Pending</div>
                            <div class="kx-stat-val" id="kx-pending-count">–</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="kx-card kx-stat h-100">
                            <div class="kx-stat-icon kx-stat-icon-p"><i class="bi bi-people-fill"></i></div>
                            <div class="kx-stat-label">Referrals</div>
                            <div class="kx-stat-val">{{ isset($referralCount) ? $referralCount : '–' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== AI Personalised Insight ===== -->
        <div class="kx-card mb-4" id="ai-insight-card" style="padding:1.1rem 1.4rem;border-color:rgba(0,204,0,.18);min-height:62px;display:flex;align-items:center;gap:.875rem">
            <div style="width:36px;height:36px;border-radius:10px;background:rgba(0,204,0,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary-green);font-size:1rem">
                <i class="bi bi-robot"></i>
            </div>
            <div id="ai-insight-text" style="font-size:.83rem;color:rgba(255,255,255,.55)">
                <span class="spinner-border spinner-border-sm me-1" style="color:var(--primary-green)"></span> Loading your AI insight…
            </div>
        </div>

        <!-- ===== Quick Actions Row ===== -->
        <div class="kx-card p-3 mb-4">
            <div class="kx-quick">
                <a href="{{ Auth::user()->kyc_verified ? route('buy') : route('kyc.form') }}" class="kx-qbtn kx-qi-buy">
                    <div class="kx-qicon"><i class="bi bi-cart-fill"></i></div>
                    Buy Crypto
                </a>
                <a href="{{ route('sell.form') }}" class="kx-qbtn kx-qi-sell">
                    <div class="kx-qicon"><i class="bi bi-bag-fill"></i></div>
                    Sell Crypto
                </a>
                <a href="{{ route('deposits.index') }}" class="kx-qbtn kx-qi-dep">
                    <div class="kx-qicon"><i class="bi bi-arrow-down-circle-fill"></i></div>
                    Deposit
                </a>
                <a href="{{ route('withdraw') }}" class="kx-qbtn kx-qi-wd">
                    <div class="kx-qicon"><i class="bi bi-arrow-up-circle-fill"></i></div>
                    Withdraw
                </a>
                <a href="{{ route('transactions.history') }}" class="kx-qbtn kx-qi-hist">
                    <div class="kx-qicon"><i class="bi bi-clock-history"></i></div>
                    History
                </a>
                <a href="{{ route('referrals') }}" class="kx-qbtn kx-qi-ref">
                    <div class="kx-qicon"><i class="bi bi-people-fill"></i></div>
                    Referrals
                </a>
            </div>
        </div>

        <!-- ===== Rates + Prices Row ===== -->
        <div class="row g-4 mb-4">
            <!-- Buy/Sell Rates -->
            <div class="col-12 col-lg-6">
                <div class="kx-card p-4 h-100">
                    <div class="kx-sec-title mb-4">
                        <i class="bi bi-graph-up-arrow"></i>Buy / Sell Rates
                        <span class="kx-sec-tag ms-auto">₦ Naira</span>
                    </div>
                    <div class="table-responsive">
                        <table class="kx-db-tbl">
                            <thead><tr><th>Coin</th><th>Buy Rate</th><th>Sell Rate</th></tr></thead>
                            <tbody id="rates-container">
                                <tr><td colspan="3" class="text-center py-4">
                                    <div class="kx-skel" style="height:14px;width:55%;margin:0 auto 8px"></div>
                                    <div class="kx-skel" style="height:14px;width:40%;margin:0 auto"></div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Live Prices -->
            <div class="col-12 col-lg-6">
                <div class="kx-card p-4 h-100">
                    <div class="kx-sec-title mb-3">
                        <i class="bi bi-currency-bitcoin"></i>Live Prices
                        <span class="kx-sec-tag ms-auto" style="display:inline-flex;align-items:center;gap:5px">
                            <span style="width:6px;height:6px;background:#00cc00;border-radius:50%;display:inline-block;animation:kx-pulse 2s ease-in-out infinite"></span>Live
                        </span>
                    </div>
                    <div id="prices-container">
                        <div class="kx-skel" style="height:48px;border-radius:12px;margin-bottom:8px"></div>
                        <div class="kx-skel" style="height:48px;border-radius:12px;margin-bottom:8px"></div>
                        <div class="kx-skel" style="height:48px;border-radius:12px;margin-bottom:8px"></div>
                        <div class="kx-skel" style="height:48px;border-radius:12px;margin-bottom:8px"></div>
                        <div class="kx-skel" style="height:48px;border-radius:12px"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Transaction History ===== -->
        <div class="kx-card p-4 mb-4">
            <div class="kx-sec-title mb-4">
                <i class="bi bi-receipt-cutoff"></i>Transaction History
                <div class="ms-auto d-flex gap-2 flex-wrap">
                    <input type="text" id="transaction-search" class="kx-db-inp" placeholder="Search coin or bank…" oninput="fetchTransactions()" style="width:160px">
                    <select id="transaction-filter" class="kx-db-inp" onchange="fetchTransactions()">
                        <option value="all">All Types</option>
                        <option value="buy">Buy</option>
                        <option value="sell">Sell</option>
                        <option value="withdrawal">Withdrawal</option>
                    </select>
                </div>
            </div>
            <div class="kx-tx-scroll">
                <table class="kx-db-tbl">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Coin / Bank</th>
                            <th>Amount (₦)</th>
                            <th>Value (USD)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-container">
                        @if(isset($transactions) && !$transactions->isEmpty())
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction['created_at']->toDateString() }}</td>
                                    <td>
                                        @php $t = $transaction['type']; @endphp
                                        <span class="kx-tx-badge kx-tx-{{ $t === 'buy' ? 'buy' : ($t === 'sell' ? 'sell' : ($t === 'withdrawal' ? 'wd' : ($t === 'deposit' ? 'dep' : 'other'))) }}">{{ ucfirst($t) }}</span>
                                    </td>
                                    <td>{{ $t === 'withdrawal' ? ($transaction['bank_account'] ?? 'N/A') : ($transaction['coin'] ?? 'N/A') }}</td>
                                    <td class="{{ $t === 'buy' ? 'kx-rate-buy' : ($t === 'sell' ? 'kx-rate-sell' : '') }}">{{ $transaction['amount_ngn'] ? '₦' . number_format($transaction['amount_ngn'], 2) : 'N/A' }}</td>
                                    <td>{{ $transaction['amount_usd'] ? '$' . number_format($transaction['amount_usd'], 2) : 'N/A' }}</td>
                                    <td><span class="kx-status kx-status-{{ strtolower($transaction['status'] ?? 'other') }}">{{ $transaction['status'] ?? 'N/A' }}</span></td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="6" class="text-center py-5" style="color:rgba(255,255,255,0.3)"><i class="bi bi-inbox me-2"></i>No transactions found</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if(isset($transactions) && $transactions->count() > 3)
                <div class="text-center mt-4">
                    <a href="{{ route('transactions.history') }}" class="kx-btn-more"><i class="bi bi-list-ul"></i>View All Transactions</a>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
    let allTransactions = @json($transactions);

    document.addEventListener('DOMContentLoaded', () => {
        // ---- Mode Toggle ----
        const modeBtn  = document.getElementById('modeToggleBtn');
        const modeIcon = document.getElementById('modeIcon');
        const body     = document.body;

        function applyTheme(light) {
            if (light) { body.classList.add('light-mode'); modeIcon.className = 'bi bi-sun-fill'; }
            else        { body.classList.remove('light-mode'); modeIcon.className = 'bi bi-moon-stars-fill'; }
        }
        applyTheme(localStorage.getItem('theme') === 'light');
        modeBtn.addEventListener('click', () => {
            const nowLight = !body.classList.contains('light-mode');
            applyTheme(nowLight);
            localStorage.setItem('theme', nowLight ? 'light' : 'dark');
        });

        // ---- Pending count ----
        if (allTransactions) {
            const pend = allTransactions.filter(t => t.status && t.status.toLowerCase() === 'pending').length;
            const el = document.getElementById('kx-pending-count');
            if (el) el.textContent = pend;
        }

        fetchRates();
        fetchPrices();
        renderTransactions(allTransactions);
        loadNotifications();
        setInterval(loadNotifications, 60000); // refresh every 60s
    });

    // ---- Notifications ----
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function loadNotifications() {
        fetch('/notifications/api?limit=10', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => { if (!r.ok) throw r.status; return r.json(); })
        .then(data => renderNotifications(data))
        .catch(() => {
            document.getElementById('notifications-container').innerHTML =
                '<li class="kx-notif-item" style="color:rgba(255,255,255,0.35)"><i class="bi bi-wifi-off me-2"></i>Could not load notifications</li>';
        });
    }

    function renderNotifications(data) {
        const container = document.getElementById('notifications-container');
        const dot = document.getElementById('notif-dot');
        const count = data.unread_count || 0;
        const notes = data.notifications || [];

        // Badge dot
        dot.style.display = count > 0 ? 'block' : 'none';

        if (!notes.length) {
            container.innerHTML = `
                <li class="px-3 py-2" style="font-size:0.8rem;color:rgba(255,255,255,0.3);text-align:center">
                    <i class="bi bi-bell-slash me-2"></i>No notifications yet
                </li>`;
            return;
        }

        const typeColors = {
            success: '#00cc00', error: '#ef4444', warning: '#f59e0b',
            info: '#3b82f6', trade_update: '#8b5cf6', system: '#6b7280'
        };
        const typeIcons = {
            success: 'bi-check-circle-fill', error: 'bi-exclamation-triangle-fill',
            warning: 'bi-exclamation-circle-fill', info: 'bi-info-circle-fill',
            trade_update: 'bi-arrow-repeat', system: 'bi-gear-fill'
        };

        const items = notes.map(n => {
            const color = typeColors[n.type] || '#3b82f6';
            const icon  = typeIcons[n.type]  || 'bi-bell-fill';
            const unread = !n.is_read;
            const time = n.time_ago || timeAgo(n.created_at);
            return `<li class="kx-notif-item${unread ? ' kx-notif-unread' : ''}"
                        data-id="${n.id}" data-read="${n.is_read ? '1' : '0'}"
                        style="cursor:pointer;${unread ? 'background:rgba(0,204,0,0.05);border-radius:10px;' : ''}">
                <i class="bi ${icon}" style="color:${color};font-size:1rem;flex-shrink:0;margin-top:1px"></i>
                <div style="min-width:0;flex:1">
                    <div style="font-weight:${unread ? '600' : '500'};color:${unread ? '#e8f5e8' : 'rgba(255,255,255,0.65)'};font-size:0.81rem;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${n.title}</div>
                    <div style="color:rgba(255,255,255,0.45);font-size:0.74rem;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${n.message}</div>
                    <div style="color:rgba(255,255,255,0.25);font-size:0.68rem;margin-top:3px">${time}</div>
                </div>
                ${unread ? '<span style="width:7px;height:7px;background:#00cc00;border-radius:50%;flex-shrink:0;margin-top:5px"></span>' : ''}
            </li>`;
        }).join('<li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.06);margin:3px 8px"></li>');

        const footer = `
            <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.06);margin:4px 8px"></li>
            <li class="d-flex justify-content-between px-2 pb-1" style="gap:6px">
                <button onclick="markAllRead()" class="btn btn-sm w-50" style="font-size:0.73rem;background:rgba(0,204,0,0.1);color:#00cc00;border:1px solid rgba(0,204,0,0.25);border-radius:8px">
                    <i class="bi bi-check2-all me-1"></i>Mark all read
                </button>
                <a href="/notifications" class="btn btn-sm w-50" style="font-size:0.73rem;background:rgba(255,255,255,0.05);color:rgba(255,255,255,0.55);border:1px solid rgba(255,255,255,0.1);border-radius:8px">
                    View all
                </a>
            </li>`;

        container.innerHTML = `
            <li class="px-3 pt-2 pb-1 d-flex justify-content-between align-items-center">
                <span style="font-size:0.8rem;font-weight:600;color:#e8f5e8">Notifications${count > 0 ? ` <span style="background:#00cc00;color:#000;font-size:0.65rem;padding:1px 6px;border-radius:20px;font-weight:700;margin-left:4px">${count}</span>` : ''}</span>
            </li>
            ${items}${footer}`;

        // Click to mark individual as read
        container.querySelectorAll('.kx-notif-item[data-id]').forEach(el => {
            el.addEventListener('click', function () {
                const id = this.dataset.id;
                if (this.dataset.read === '0') {
                    fetch(`/notifications/${id}/mark-read`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                    }).then(() => loadNotifications());
                }
            });
        });
    }

    function markAllRead() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        }).then(() => loadNotifications());
    }

    function timeAgo(dateStr) {
        if (!dateStr) return '';
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)   return diff + 's ago';
        if (diff < 3600) return Math.floor(diff/60) + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return Math.floor(diff/86400) + 'd ago';
    }

    // ---- Rates ----
    function fetchRates() {
        const c = document.getElementById('rates-container');
        c.innerHTML = '<tr><td colspan="3" class="text-center py-4"><div class="kx-skel" style="height:14px;width:55%;margin:0 auto 6px"></div><div class="kx-skel" style="height:14px;width:40%;margin:0 auto"></div></td></tr>';
        fetch('/crypto-rates', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(data => renderRates(data))
            .catch(() => {
                const fb = @json($rates);
                if (Object.keys(fb).length) {
                    renderRates(Object.entries(fb).map(([coin, rates]) => ({ coin, buy_rate: rates.buy_rate, sell_rate: rates.sell_rate })));
                } else {
                    c.innerHTML = '<tr><td colspan="3" class="text-center py-4" style="color:rgba(255,255,255,0.3)"><i class="bi bi-wifi-off me-2"></i>Unable to load rates</td></tr>';
                }
            });
    }

    function renderRates(rates) {
        const c = document.getElementById('rates-container');
        if (!rates || !rates.length) { c.innerHTML = '<tr><td colspan="3" class="text-center py-4" style="color:rgba(255,255,255,0.3)">No rates available</td></tr>'; return; }
        c.innerHTML = rates.map(r => `
            <tr>
                <td><div class="kx-coin-row"><span style="font-weight:600">${r.coin}</span></div></td>
                <td class="kx-rate-buy">₦${parseFloat(r.buy_rate).toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2})}</td>
                <td class="kx-rate-sell">₦${parseFloat(r.sell_rate).toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2})}</td>
            </tr>`).join('');
    }

    // ---- Prices ----
    function fetchPrices() {
        const c = document.getElementById('prices-container');
        fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,tether,binancecoin,litecoin&order=market_cap_desc&price_change_percentage=24h', { headers: { 'Accept': 'application/json' } })
            .then(r => { if (!r.ok) throw 0; return r.json(); })
            .then(data => renderPrices(data))
            .catch(() => {
                const fb = @json(isset($prices) ? $prices : []);
                if (Object.keys(fb).length) {
                    renderPrices(Object.entries(fb).map(([coin, p]) => ({ id: coin.toLowerCase(), symbol: coin, current_price: p.usd, image: p.image, price_change_percentage_24h: 0 })));
                } else {
                    c.innerHTML = '<div style="padding:20px;text-align:center;font-size:0.85rem;color:rgba(255,255,255,0.3)"><i class="bi bi-wifi-off me-2"></i>Unable to load prices</div>';
                }
            });
    }

    function renderPrices(prices) {
        const c = document.getElementById('prices-container');
        if (!prices || !prices.length) { c.innerHTML = '<div style="padding:16px;text-align:center;color:rgba(255,255,255,0.3)">No data available</div>'; return; }
        c.innerHTML = prices.map(p => {
            const chg = p.price_change_percentage_24h || 0;
            const up  = chg >= 0;
            const price = p.current_price >= 1000
                ? '$' + p.current_price.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})
                : '$' + p.current_price.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:4});
            return `<div class="kx-pr-row">
                <img class="kx-pr-img" src="${p.image||''}" alt="${p.symbol}" loading="lazy">
                <div style="flex:1;min-width:0">
                    <div class="kx-pr-sym">${p.symbol.toUpperCase()}</div>
                    <div class="kx-pr-name">${p.name||''}</div>
                </div>
                <div style="text-align:right">
                    <div class="kx-pr-usd">${price}</div>
                    <span class="kx-pr-chg ${up?'kx-pr-up':'kx-pr-dn'}"><i class="bi bi-arrow-${up?'up':'down'}-right"></i>${up?'+':''}${chg.toFixed(2)}%</span>
                </div>
            </div>`;
        }).join('');
    }

    // ---- Transactions ----
    function fetchTransactions() {
        const q = document.getElementById('transaction-search').value;
        const t = document.getElementById('transaction-filter').value;
        fetch(`/transactions?search=${encodeURIComponent(q)}&type=${t}&limit=5`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(data => { allTransactions = data; renderTransactions(data); })
            .catch(err => {
                document.getElementById('transactions-container').innerHTML =
                    `<tr><td colspan="6" class="text-center py-4" style="color:#ef4444"><i class="bi bi-exclamation-triangle me-2"></i>Error loading transactions: ${err.message}</td></tr>`;
            });
    }

    function renderTransactions(transactions) {
        const c = document.getElementById('transactions-container');
        if (!transactions || !transactions.length) {
            c.innerHTML = '<tr><td colspan="6" class="text-center py-5" style="color:rgba(255,255,255,0.3)"><i class="bi bi-inbox me-2"></i>No transactions found</td></tr>';
            return;
        }
        c.innerHTML = transactions.map(tx => {
            const typ = tx.type || '';
            const typClass = typ === 'buy' ? 'kx-tx-buy' : (typ === 'sell' ? 'kx-tx-sell' : (typ === 'withdrawal' ? 'kx-tx-wd' : (typ === 'deposit' ? 'kx-tx-dep' : 'kx-tx-other')));
            const amtClass  = typ === 'buy' ? 'kx-rate-buy' : (typ === 'sell' ? 'kx-rate-sell' : '');
            const sts = (tx.status || '').toLowerCase();
            const stsClass = sts === 'pending' ? 'kx-status-pending' : (sts === 'approved' || sts === 'completed' ? 'kx-status-approved' : (sts === 'rejected' || sts === 'failed' ? 'kx-status-rejected' : 'kx-status-pending'));
            return `<tr>
                <td>${new Date(tx.created_at).toLocaleDateString('en-NG')}</td>
                <td><span class="kx-tx-badge ${typClass}">${typ.charAt(0).toUpperCase()+typ.slice(1)}</span></td>
                <td>${typ === 'withdrawal' ? (tx.bank_account||'N/A') : (tx.coin||'N/A')}</td>
                <td class="${amtClass}">${tx.amount_ngn ? '₦'+parseFloat(tx.amount_ngn).toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2}) : 'N/A'}</td>
                <td>${tx.amount_usd ? '$'+parseFloat(tx.amount_usd).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}) : 'N/A'}</td>
                <td><span class="kx-status ${stsClass}">${tx.status||'N/A'}</span></td>
            </tr>`;
        }).join('');
    }
</script>

<script>
// Auto-load personalised AI insight
(function(){
    fetch('{{ route("ai.dashboard-insight") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        const el = document.getElementById('ai-insight-text');
        if (el) el.innerHTML = data.insight || '<span style="color:#ef4444">'+(data.error||'Could not load insight.')+'</span>';
    })
    .catch(() => {
        const el = document.getElementById('ai-insight-text');
        if (el) el.innerHTML = '<span style="color:rgba(255,255,255,.3)">AI insight unavailable.</span>';
    });
})();
</script>
@endsection
