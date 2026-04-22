@extends('layout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}

/* ── Page hero ── */
.kx-hist-hero{background:linear-gradient(135deg,rgba(0,204,0,.08),rgba(0,80,0,.04));border:1px solid rgba(0,204,0,.14);border-radius:20px;padding:1.6rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;position:relative;overflow:hidden;}
.kx-hist-hero::before{content:'';position:absolute;top:-40px;right:-40px;width:160px;height:160px;background:radial-gradient(circle,rgba(0,204,0,.07),transparent 70%);pointer-events:none;}
.kx-hist-hero-left{display:flex;align-items:center;gap:1rem;}
.kx-hist-icon{width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#00cc00,#007a0c);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff;box-shadow:0 6px 22px rgba(0,204,0,.28);flex-shrink:0;}
.kx-hist-title{font-size:1.25rem;font-weight:700;color:#fff;margin-bottom:.15rem;}
.kx-hist-sub{font-size:.82rem;color:var(--kx-muted);}
.kx-export-btn{display:inline-flex;align-items:center;gap:.5rem;background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);border-radius:10px;color:#00cc00;font-size:.82rem;font-weight:600;padding:.55rem 1rem;text-decoration:none;transition:all .2s;}
.kx-export-btn:hover{background:rgba(0,204,0,.18);color:#00cc00;}

/* ── Stats row ── */
.kx-stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-bottom:1.5rem;}
@media(max-width:640px){.kx-stat-row{grid-template-columns:repeat(2,1fr);}}
.kx-stat-pill{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;padding:.9rem 1.1rem;display:flex;align-items:center;gap:.75rem;}
.kx-stat-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.kx-stat-dot.all{background:#00cc00;}
.kx-stat-dot.pend{background:#ffc107;}
.kx-stat-dot.ok{background:#00cc00;}
.kx-stat-dot.fail{background:#ef4444;}
.kx-stat-info .lbl{font-size:.7rem;color:var(--kx-muted);}
.kx-stat-info .val{font-size:1.05rem;font-weight:700;color:#fff;}

/* ── Filter panel ── */
.kx-filter-panel{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;margin-bottom:1.25rem;overflow:hidden;}
.kx-filter-toggle{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.25rem;cursor:pointer;user-select:none;}
.kx-filter-toggle h6{font-size:.88rem;font-weight:600;color:#fff;margin:0;display:flex;align-items:center;gap:.5rem;}
.kx-filter-toggle .chevron{color:var(--kx-muted);transition:transform .25s;font-size:.85rem;}
.kx-filter-toggle.open .chevron{transform:rotate(180deg);}
.kx-filter-body{padding:0 1.25rem 1.25rem;display:none;}
.kx-filter-body.open{display:block;}

/* active filter indicator */
.kx-filter-dot{display:none;width:8px;height:8px;border-radius:50%;background:#ffc107;flex-shrink:0;}
.kx-filter-dot.active{display:inline-block;}

/* inputs */
.kx-finput{width:100%;background:var(--kx-dark);border:1.5px solid var(--kx-border);border-radius:9px;color:var(--kx-text);font-size:.83rem;padding:.55rem .8rem;outline:none;transition:border-color .2s;}
.kx-finput:focus{border-color:rgba(0,204,0,.4);}
.kx-finput option{background:var(--kx-card);}
.kx-finput::placeholder{color:var(--kx-muted);}
.kx-fbtn{border:none;border-radius:9px;font-size:.83rem;font-weight:600;padding:.55rem 1rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:.4rem;white-space:nowrap;}
.kx-fbtn-primary{background:linear-gradient(135deg,#00cc00,#007a0c);color:#fff;}
.kx-fbtn-primary:hover{filter:brightness(1.1);}
.kx-fbtn-ghost{background:transparent;border:1.5px solid var(--kx-border);color:var(--kx-muted);}
.kx-fbtn-ghost:hover{border-color:rgba(0,204,0,.3);color:#fff;}

/* ── Type tabs ── */
.kx-type-tabs{display:flex;gap:.5rem;margin-bottom:1.25rem;overflow-x:auto;padding-bottom:2px;scrollbar-width:none;}
.kx-type-tabs::-webkit-scrollbar{display:none;}
.kx-tab{display:inline-flex;align-items:center;gap:.4rem;white-space:nowrap;background:var(--kx-card);border:1.5px solid var(--kx-border);border-radius:20px;color:var(--kx-muted);font-size:.78rem;font-weight:600;padding:.35rem .9rem;cursor:pointer;transition:all .2s;text-decoration:none;}
.kx-tab:hover{border-color:rgba(0,204,0,.3);color:#fff;}
.kx-tab.active{background:rgba(0,204,0,.1);border-color:var(--kx-green);color:#00cc00;}
.kx-tab .kx-tab-count{background:rgba(0,204,0,.15);border-radius:10px;padding:0 .45rem;font-size:.7rem;color:#00cc00;}

/* ── Table ── */
.kx-table-wrap{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;overflow:hidden;}
.kx-table{width:100%;border-collapse:collapse;}
.kx-table thead th{background:var(--kx-card2);color:var(--kx-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;padding:.75rem 1rem;border-bottom:1px solid var(--kx-border);white-space:nowrap;font-weight:600;}
.kx-table thead th:first-child{padding-left:1.25rem;}
.kx-table tbody tr{border-bottom:1px solid var(--kx-border);transition:background .15s;}
.kx-table tbody tr:last-child{border-bottom:none;}
.kx-table tbody tr:hover{background:rgba(255,255,255,.02);}
.kx-table td{padding:.8rem 1rem;font-size:.83rem;color:var(--kx-text);vertical-align:middle;}
.kx-table td:first-child{padding-left:1.25rem;}

/* Type badge */
.kx-type-badge{display:inline-flex;align-items:center;gap:.35rem;border-radius:8px;padding:.25rem .65rem;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
.kx-type-buy{background:rgba(56,189,248,.12);color:#38bdf8;}
.kx-type-sell{background:rgba(0,204,0,.1);color:#00cc00;}
.kx-type-withdrawal{background:rgba(168,85,247,.12);color:#c084fc;}
.kx-type-deposit{background:rgba(245,158,11,.1);color:#fbbf24;}

/* Status badge */
.kx-sts{display:inline-flex;align-items:center;gap:.3rem;border-radius:20px;padding:.22rem .65rem;font-size:.73rem;font-weight:600;}
.kx-sts-pending{background:rgba(255,193,7,.12);color:#ffc107;}
.kx-sts-completed,.kx-sts-approved{background:rgba(0,204,0,.12);color:#00cc00;}
.kx-sts-failed,.kx-sts-rejected{background:rgba(239,68,68,.12);color:#ef4444;}
.kx-sts-cancelled{background:rgba(107,114,128,.12);color:#9ca3af;}
.kx-sts-processing{background:rgba(56,189,248,.12);color:#38bdf8;}

/* ref mono */
.kx-ref{font-family:monospace;font-size:.76rem;color:var(--kx-muted);}
.kx-amt{font-weight:700;color:#fff;}
.kx-amt-usd{font-size:.75rem;color:var(--kx-muted);}

/* Mobile card layout */
@media(max-width:768px){
    .kx-desktop-only{display:none;}
    .kx-mobile-cards{display:flex;flex-direction:column;gap:.65rem;}
    .kx-tx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;padding:1rem 1.1rem;}
    .kx-tx-card-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem;}
    .kx-tx-card-mid{display:flex;align-items:center;justify-content:space-between;margin-bottom:.45rem;}
    .kx-tx-card-bot{display:flex;align-items:center;justify-content:space-between;}
    .kx-tx-date{font-size:.72rem;color:var(--kx-muted);}
}
@media(min-width:769px){
    .kx-mobile-cards{display:none;}
}

/* ── Preview button ── */
.kx-preview-btn{display:inline-flex;align-items:center;gap:.3rem;background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.18);border-radius:7px;color:#00cc00;font-size:.74rem;font-weight:600;padding:.28rem .65rem;cursor:pointer;transition:all .15s;white-space:nowrap;}
.kx-preview-btn:hover{background:rgba(0,204,0,.18);border-color:rgba(0,204,0,.4);}

/* ── Upload proof button (inside drawer) ── */
.btn-kx-upload-proof{display:inline-flex;align-items:center;gap:.45rem;background:linear-gradient(135deg,#00cc00,#009900);color:#000;border:none;border-radius:10px;font-size:.84rem;font-weight:700;padding:.65rem 1.2rem;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-kx-upload-proof:hover{filter:brightness(1.08);color:#000;}

/* ── Detail Drawer ── */
.kx-drawer-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:1050;opacity:0;pointer-events:none;transition:opacity .25s;}
.kx-drawer-overlay.open{opacity:1;pointer-events:all;}
.kx-drawer{position:fixed;top:0;right:0;height:100%;width:460px;max-width:100vw;background:#161b27;border-left:1px solid rgba(255,255,255,0.08);z-index:1051;transform:translateX(100%);transition:transform .3s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;}
.kx-drawer.open{transform:translateX(0);}
@media(max-width:500px){.kx-drawer{width:100vw;border-left:none;border-top:1px solid rgba(255,255,255,0.08);top:auto;bottom:0;height:92vh;border-radius:20px 20px 0 0;transform:translateY(100%) !important;}}
@media(max-width:500px){.kx-drawer.open{transform:translateY(0) !important;}}
.kx-drawer-header{display:flex;align-items:center;justify-content:space-between;padding:1.1rem 1.3rem;border-bottom:1px solid rgba(255,255,255,0.07);flex-shrink:0;}
.kx-drawer-title{font-size:1rem;font-weight:700;color:#fff;}
.kx-drawer-close{background:rgba(255,255,255,.06);border:none;border-radius:8px;color:var(--kx-muted);width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1rem;transition:all .15s;}
.kx-drawer-close:hover{background:rgba(255,255,255,.12);color:#fff;}
.kx-drawer-body{flex:1;overflow-y:auto;padding:1.3rem;scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.1) transparent;}
.kx-drawer-body::-webkit-scrollbar{width:4px;}
.kx-drawer-body::-webkit-scrollbar-track{background:transparent;}
.kx-drawer-body::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:2px;}

/* Drawer detail rows */
.kx-detail-section{margin-bottom:1.1rem;}
.kx-detail-section-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--kx-muted);margin-bottom:.6rem;padding-bottom:.4rem;border-bottom:1px solid rgba(255,255,255,0.05);}
.kx-detail-row{display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;padding:.45rem 0;border-bottom:1px solid rgba(255,255,255,0.04);}
.kx-detail-row:last-child{border-bottom:none;}
.kx-detail-label{font-size:.76rem;color:var(--kx-muted);flex-shrink:0;}
.kx-detail-val{font-size:.82rem;color:#fff;font-weight:600;text-align:right;word-break:break-all;}
.kx-detail-val.mono{font-family:monospace;font-size:.78rem;font-weight:400;color:#b0bec5;}
.kx-detail-val.green{color:#00cc00;}
.kx-detail-val.yellow{color:#ffc107;}
.kx-detail-val.red{color:#ef4444;}
.kx-detail-val.muted{color:var(--kx-muted);font-weight:400;}

/* Status tracker */
.kx-timeline{display:flex;flex-direction:column;gap:0;}
.kx-step{display:flex;align-items:flex-start;gap:.75rem;position:relative;}
.kx-step:not(:last-child) .kx-step-line{position:absolute;left:11px;top:22px;width:2px;height:calc(100% + 4px);background:rgba(255,255,255,0.08);}
.kx-step:not(:last-child).done .kx-step-line{background:rgba(0,204,0,.3);}
.kx-step-dot{width:24px;height:24px;border-radius:50%;border:2px solid rgba(255,255,255,.12);background:#1e2535;display:flex;align-items:center;justify-content:center;font-size:.65rem;flex-shrink:0;z-index:1;margin-top:2px;}
.kx-step.done .kx-step-dot{border-color:#00cc00;background:rgba(0,204,0,.15);color:#00cc00;}
.kx-step.active .kx-step-dot{border-color:#ffc107;background:rgba(255,193,7,.15);color:#ffc107;animation:kx-pulse 1.5s ease-in-out infinite;}
.kx-step.error .kx-step-dot{border-color:#ef4444;background:rgba(239,68,68,.15);color:#ef4444;}
@keyframes kx-pulse{0%,100%{box-shadow:0 0 0 0 rgba(255,193,7,.3);}50%{box-shadow:0 0 0 6px rgba(255,193,7,0);}}
.kx-step-info{padding:.1rem 0 .9rem;}
.kx-step-name{font-size:.82rem;font-weight:600;color:#fff;}
.kx-step-desc{font-size:.73rem;color:var(--kx-muted);margin-top:.1rem;}

/* Proof image */
.kx-proof-img{width:100%;border-radius:10px;border:1px solid rgba(255,255,255,0.08);margin-top:.5rem;cursor:pointer;transition:opacity .15s;}
.kx-proof-img:hover{opacity:.9;}

/* Copy btn */
.kx-copy-btn{background:none;border:none;padding:0 0 0 6px;color:var(--kx-muted);cursor:pointer;font-size:.8rem;vertical-align:middle;transition:color .15s;}
.kx-copy-btn:hover{color:#00cc00;}

/* Cancel trade button */
.kx-cancel-btn{width:100%;padding:.7rem 1rem;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:10px;color:#ef4444;font-size:.84rem;font-weight:600;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:.5rem;margin-top:.5rem;}
.kx-cancel-btn:hover:not(:disabled){background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.5);}
.kx-cancel-btn:disabled{opacity:.5;cursor:not-allowed;}
.kx-cancel-msg{margin-top:.6rem;padding:.55rem .9rem;border-radius:8px;font-size:.78rem;text-align:center;}
.kx-cancel-msg.success{background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.2);color:#00cc00;}
.kx-cancel-msg.error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#ef4444;}

/* Skeleton loader */
.kx-skel{background:linear-gradient(90deg,#1e2535 25%,#252d3f 50%,#1e2535 75%);background-size:200% 100%;animation:kx-skel .9s ease-in-out infinite;border-radius:6px;}
@keyframes kx-skel{0%{background-position:200% 0;}100%{background-position:-200% 0;}}

/* ── Empty state ── */
.kx-empty{padding:3.5rem 1.5rem;text-align:center;}
.kx-empty-icon{width:64px;height:64px;border-radius:50%;background:var(--kx-card2);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.6rem;color:var(--kx-muted);}
.kx-empty-title{font-size:1rem;font-weight:600;color:#fff;margin-bottom:.35rem;}
.kx-empty-sub{font-size:.83rem;color:var(--kx-muted);}

/* ── Pagination override ── */
.pagination{gap:.35rem;flex-wrap:wrap;}
.page-item .page-link{background:var(--kx-card)!important;border:1px solid var(--kx-border)!important;color:var(--kx-muted)!important;border-radius:8px!important;font-size:.82rem;padding:.4rem .7rem;}
.page-item.active .page-link{background:var(--kx-green)!important;border-color:var(--kx-green)!important;color:#000!important;font-weight:700;}
.page-item.disabled .page-link{opacity:.4;}
.page-item .page-link:hover:not(.active){border-color:rgba(0,204,0,.3)!important;color:#fff!important;}
</style>
@endpush

@section('content')
@php
    $totalCount     = $allTrades->total();
    $pendingCount   = $allTrades->getCollection()->where('status','pending')->count();
    $completedCount = $allTrades->getCollection()->whereIn('status',['completed','approved'])->count();
    $failedCount    = $allTrades->getCollection()->whereIn('status',['failed','rejected','cancelled'])->count();
    $hasFilter      = request()->hasAny(['search','status','type','date','min_amount','max_amount']);
    $activeType     = request('type','');
@endphp

<div class="container" style="padding:1.25rem 0 3rem;">

    {{-- Hero --}}
    <div class="kx-hist-hero">
        <div class="kx-hist-hero-left">
            <div class="kx-hist-icon"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="kx-hist-title">Transaction History</div>
                <div class="kx-hist-sub">All your trades, deposits &amp; withdrawals</div>
            </div>
        </div>
        <a href="{{ route('transactions.history', array_merge(request()->query(), ['export' => 'csv'])) }}"
           class="kx-export-btn">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>

    {{-- Stats --}}
    <div class="kx-stat-row">
        <div class="kx-stat-pill">
            <div class="kx-stat-dot all"></div>
            <div class="kx-stat-info"><div class="lbl">Total</div><div class="val">{{ $allTrades->total() }}</div></div>
        </div>
        <div class="kx-stat-pill">
            <div class="kx-stat-dot pend"></div>
            <div class="kx-stat-info"><div class="lbl">Pending</div><div class="val">{{ $allTrades->getCollection()->where('status','pending')->count() }}</div></div>
        </div>
        <div class="kx-stat-pill">
            <div class="kx-stat-dot ok"></div>
            <div class="kx-stat-info"><div class="lbl">Completed</div><div class="val">{{ $allTrades->getCollection()->whereIn('status',['completed','approved'])->count() }}</div></div>
        </div>
        <div class="kx-stat-pill">
            <div class="kx-stat-dot fail"></div>
            <div class="kx-stat-info"><div class="lbl">Failed</div><div class="val">{{ $allTrades->getCollection()->whereIn('status',['failed','rejected','cancelled'])->count() }}</div></div>
        </div>
    </div>

    {{-- Type tabs --}}
    <div class="kx-type-tabs">
        <a href="{{ route('transactions.history', array_merge(request()->except('type'), [])) }}"
           class="kx-tab {{ $activeType==='' ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> All
        </a>
        <a href="{{ route('transactions.history', array_merge(request()->except('type'), ['type'=>'buy'])) }}"
           class="kx-tab {{ $activeType==='buy' ? 'active' : '' }}">
            <i class="bi bi-arrow-down-circle-fill"></i> Buy
        </a>
        <a href="{{ route('transactions.history', array_merge(request()->except('type'), ['type'=>'sell'])) }}"
           class="kx-tab {{ $activeType==='sell' ? 'active' : '' }}">
            <i class="bi bi-arrow-up-circle-fill"></i> Sell
        </a>
        <a href="{{ route('transactions.history', array_merge(request()->except('type'), ['type'=>'withdrawal'])) }}"
           class="kx-tab {{ $activeType==='withdrawal' ? 'active' : '' }}">
            <i class="bi bi-bank"></i> Withdrawals
        </a>
    </div>

    {{-- Filter panel --}}
    <div class="kx-filter-panel">
        <div class="kx-filter-toggle {{ $hasFilter ? 'open' : '' }}" id="filterToggle">
            <h6>
                <i class="bi bi-sliders" style="color:var(--kx-green)"></i>
                Filters
                <span class="kx-filter-dot {{ $hasFilter ? 'active' : '' }}" id="filterDot"></span>
            </h6>
            <i class="bi bi-chevron-down chevron {{ $hasFilter ? 'open' : '' }}" id="filterChevron"></i>
        </div>
        <div class="kx-filter-body {{ $hasFilter ? 'open' : '' }}" id="filterBody">
            <form method="GET" action="{{ route('transactions.history') }}" id="filterForm">
                @if($activeType)<input type="hidden" name="type" value="{{ $activeType }}">@endif
                <div class="row g-2 mb-2">
                    <div class="col-md-4 col-sm-6">
                        <input type="text" name="search" class="kx-finput" placeholder="Search coin, ref…" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="status" class="kx-finput">
                            <option value="">All Statuses</option>
                            <option value="pending"   {{ request('status')=='pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option>
                            <option value="approved"  {{ request('status')=='approved'  ? 'selected' : '' }}>Approved</option>
                            <option value="failed"    {{ request('status')=='failed'    ? 'selected' : '' }}>Failed</option>
                            <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" name="date" class="kx-finput" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="number" name="min_amount" class="kx-finput" placeholder="Min ₦" value="{{ request('min_amount') }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="number" name="max_amount" class="kx-finput" placeholder="Max ₦" value="{{ request('max_amount') }}">
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <button type="submit" class="kx-fbtn kx-fbtn-primary">
                        <i class="bi bi-funnel-fill"></i> Apply Filters
                    </button>
                    <a href="{{ route('transactions.history', $activeType ? ['type'=>$activeType] : []) }}"
                       class="kx-fbtn kx-fbtn-ghost">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <span style="font-size:.76rem;color:var(--kx-muted);">Rows:</span>
                        <select name="per_page" class="kx-finput" style="width:auto" onchange="document.getElementById('filterForm').submit()">
                            <option value="10" {{ request('per_page','10')=='10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page')=='25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page')=='50' ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Desktop table --}}
    <div class="kx-table-wrap kx-desktop-only">
        <table class="kx-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Coin</th>
                    <th>Amount (₦)</th>
                    <th>Amount ($)</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>Wallet</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($allTrades as $trade)
                @php
                    $typClass = 'kx-type-'.($trade->type ?? 'other');
                    $stsClass = 'kx-sts-'.($trade->status ?? 'other');
                    $typIcon  = $trade->type==='buy' ? 'bi-arrow-down-circle-fill' : ($trade->type==='sell' ? 'bi-arrow-up-circle-fill' : ($trade->type==='withdrawal' ? 'bi-bank' : 'bi-circle'));
                    $walletLabel = in_array($trade->type, ['buy', 'sell']) ? ($trade->wallet_address ?? '—') : '—';
                    if($trade->payment_method && $trade->type==='withdrawal'){
                        $bd = json_decode($trade->payment_method,true);
                        $methodLabel = ($bd['bank_name']??'').' '.($bd['account_number']??'');
                    } else { $methodLabel = $trade->payment_method ?? '—'; }
                @endphp
                <tr>
                    <td>
                        <span class="kx-type-badge {{ $typClass }}">
                            <i class="bi {{ $typIcon }}"></i>
                            {{ ucfirst($trade->type) }}
                        </span>
                    </td>
                    <td style="font-weight:600;color:#fff;">{{ $trade->coin ?? '—' }}</td>
                    <td><span class="kx-amt">₦{{ number_format($trade->naira_amount ?? $trade->amount, 2) }}</span></td>
                    <td><span class="kx-amt-usd">{{ $trade->usd_amount ? '$'.number_format($trade->usd_amount,2) : '—' }}</span></td>
                    <td>
                        <span class="kx-sts {{ $stsClass }}">
                            @if(in_array($trade->status,['completed','approved']))<i class="bi bi-check-circle-fill"></i>
                            @elseif($trade->status==='pending')<i class="bi bi-hourglass-split"></i>
                            @elseif(in_array($trade->status,['failed','rejected']))<i class="bi bi-x-circle-fill"></i>
                            @else<i class="bi bi-dash-circle"></i>@endif
                            {{ ucfirst($trade->status) }}
                        </span>
                    </td>
                    <td style="color:var(--kx-muted);font-size:.78rem;">{{ Str::limit($methodLabel, 28) }}</td>
                    <td style="max-width:170px;">
                        <span class="kx-ref" title="{{ $walletLabel }}">{{ Str::limit($walletLabel, 26) }}</span>
                    </td>
                    <td><span class="kx-ref">{{ $trade->reference ?? '—' }}</span></td>
                    <td style="color:var(--kx-muted);font-size:.78rem;white-space:nowrap;">{{ $trade->created_at->format('d M Y, H:i') }}</td>
                    <td>
                        <button class="kx-preview-btn" onclick="openPreview('{{ $trade->type }}', {{ $trade->id }})">
                            <i class="bi bi-eye-fill"></i> Preview
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="kx-empty">
                            <div class="kx-empty-icon"><i class="bi bi-inbox"></i></div>
                            <div class="kx-empty-title">No transactions found</div>
                            <div class="kx-empty-sub">Try adjusting your filters or make your first trade.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile card list --}}
    <div class="kx-mobile-cards">
    @forelse($allTrades as $trade)
        @php
            $typClass = 'kx-type-'.($trade->type ?? 'other');
            $stsClass = 'kx-sts-'.($trade->status ?? 'other');
            $typIcon  = $trade->type==='buy' ? 'bi-arrow-down-circle-fill' : ($trade->type==='sell' ? 'bi-arrow-up-circle-fill' : ($trade->type==='withdrawal' ? 'bi-bank' : 'bi-circle'));
        @endphp
        <div class="kx-tx-card">
            <div class="kx-tx-card-top">
                <span class="kx-type-badge {{ $typClass }}">
                    <i class="bi {{ $typIcon }}"></i> {{ ucfirst($trade->type) }}
                </span>
                <span class="kx-sts {{ $stsClass }}">
                    @if(in_array($trade->status,['completed','approved']))<i class="bi bi-check-circle-fill"></i>
                    @elseif($trade->status==='pending')<i class="bi bi-hourglass-split"></i>
                    @else<i class="bi bi-x-circle-fill"></i>@endif
                    {{ ucfirst($trade->status) }}
                </span>
            </div>
            <div class="kx-tx-card-mid">
                <div>
                    <div style="font-size:.82rem;font-weight:700;color:#fff;">₦{{ number_format($trade->naira_amount ?? $trade->amount, 2) }}</div>
                    @if($trade->usd_amount)<div class="kx-amt-usd">${{ number_format($trade->usd_amount,2) }}</div>@endif
                    @if(in_array($trade->type, ['buy','sell']) && !empty($trade->wallet_address))
                        <div class="kx-ref" style="margin-top:.15rem;max-width:140px" title="{{ $trade->wallet_address }}">{{ Str::limit($trade->wallet_address, 18) }}</div>
                    @endif
                </div>
                <div style="text-align:right;">
                    <div style="font-size:.85rem;font-weight:600;color:#fff;">{{ $trade->coin ?? '—' }}</div>
                    @if($trade->reference)<div class="kx-ref">{{ $trade->reference }}</div>@endif
                </div>
            </div>
            <div class="kx-tx-card-bot">
                <span class="kx-tx-date"><i class="bi bi-calendar3 me-1"></i>{{ $trade->created_at->format('d M Y, H:i') }}</span>
                <button class="kx-preview-btn" onclick="openPreview('{{ $trade->type }}', {{ $trade->id }})">
                    <i class="bi bi-eye-fill"></i> Preview
                </button>
            </div>
        </div>
    @empty
        <div class="kx-empty" style="background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;">
            <div class="kx-empty-icon"><i class="bi bi-inbox"></i></div>
            <div class="kx-empty-title">No transactions found</div>
            <div class="kx-empty-sub">Try adjusting your filters or make your first trade.</div>
        </div>
    @endforelse
    </div>

    {{-- Pagination --}}
    @if($allTrades->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $allTrades->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif

</div>
@endsection

{{-- Detail Drawer --}}
<div class="kx-drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<div class="kx-drawer" id="txDrawer">
    <div class="kx-drawer-header">
        <div class="kx-drawer-title" id="drawerTitle">Transaction Details</div>
        <button class="kx-drawer-close" onclick="closeDrawer()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="kx-drawer-body" id="drawerBody">
        <!-- content injected by JS -->
    </div>
</div>

@push('scripts')
<script>
/* Filter panel toggle */
const toggle   = document.getElementById('filterToggle');
const filterBody = document.getElementById('filterBody');
const chevron  = document.getElementById('filterChevron');
toggle.addEventListener('click', () => {
    const open = filterBody.classList.toggle('open');
    toggle.classList.toggle('open', open);
    chevron.classList.toggle('open', open);
});

/* ── Drawer ── */
const detailUrl = '{{ url("/transactions/detail") }}';

function openPreview(type, id) {
    document.getElementById('drawerOverlay').classList.add('open');
    document.getElementById('txDrawer').classList.add('open');
    document.body.style.overflow = 'hidden';
    const body = document.getElementById('drawerBody');
    body.innerHTML = skeletonHtml();
    document.getElementById('drawerTitle').textContent = 'Transaction Details';

    fetch(`${detailUrl}/${type}/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.ok ? r.json() : Promise.reject(r.status))
    .then(data => renderDrawer(data))
    .catch(() => {
        body.innerHTML = `<div style="text-align:center;padding:2rem;color:#ef4444;">
            <i class="bi bi-exclamation-circle" style="font-size:2rem;"></i>
            <p style="margin-top:.75rem;font-size:.88rem;">Failed to load transaction details.</p>
        </div>`;
    });
}

function closeDrawer() {
    document.getElementById('drawerOverlay').classList.remove('open');
    document.getElementById('txDrawer').classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

function skeletonHtml() {
    const row = (w1, w2) =>
        `<div class="kx-detail-row">
            <div class="kx-skel" style="width:${w1};height:12px;"></div>
            <div class="kx-skel" style="width:${w2};height:12px;"></div>
         </div>`;
    return `<div style="padding:.5rem 0;">${[['40%','35%'],['30%','45%'],['50%','28%'],['35%','40%'],['45%','30%'],['38%','42%']].map(p=>row(p[0],p[1])).join('')}</div>`;
}

function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg" style="color:#00cc00;"></i>';
        setTimeout(() => btn.innerHTML = orig, 1500);
    });
}

function cancelTrade(url, btn) {
    if (!confirm('Are you sure you want to cancel this transaction? This cannot be undone.')) return;
    const msgEl = document.getElementById('kx-cancel-msg');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Cancelling...';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok) {
            msgEl.className = 'kx-cancel-msg success';
            msgEl.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>' + (data.message || 'Cancelled successfully.');
            msgEl.style.display = 'block';
            btn.style.display = 'none';
            setTimeout(() => closeDrawer(), 2200);
        } else {
            msgEl.className = 'kx-cancel-msg error';
            msgEl.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i>' + (data.error || 'Cancellation failed.');
            msgEl.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-circle-fill"></i> Cancel This Transaction';
        }
    })
    .catch(() => {
        msgEl.className = 'kx-cancel-msg error';
        msgEl.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i>Network error. Please try again.';
        msgEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-x-circle-fill"></i> Cancel This Transaction';
    });
}

function statusColor(s) {
    if (['completed','approved'].includes(s)) return 'green';
    if (s === 'pending' || s === 'processing') return 'yellow';
    return 'red';
}

function statusIcon(s) {
    if (['completed','approved'].includes(s)) return 'bi-check-circle-fill';
    if (s === 'pending') return 'bi-hourglass-split';
    if (s === 'processing') return 'bi-arrow-repeat';
    return 'bi-x-circle-fill';
}

function fmt(n) {
    if (n == null || n === '') return '—';
    return '₦' + parseFloat(n).toLocaleString('en-NG', {minimumFractionDigits:2, maximumFractionDigits:2});
}
function fmtUsd(n) {
    if (n == null || n === '') return '—';
    return '$' + parseFloat(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function timelineSteps(data) {
    let steps = [];
    const s = data.status;

    if (data.type === 'buy') {
        const done  = ['completed','rejected'];
        const isDone = done.includes(s);
        steps = [
            { name: 'Order Submitted',    desc: 'Your buy order was received.',           state: 'done' },
            { name: 'Payment Uploaded',   desc: 'Waiting for payment proof.',              state: isDone || s === 'processing' ? 'done' : (s === 'pending' ? 'active' : 'pending') },
            { name: 'Under Review',       desc: 'Admin is verifying your payment.',        state: s === 'completed' || s === 'rejected' ? (s==='rejected'?'error':'done') : (s==='processing'?'active':'pending') },
            { name: s === 'rejected' ? 'Rejected' : 'Completed', desc: s === 'rejected' ? 'Your order was rejected.' : 'Crypto sent to your wallet.', state: s==='completed'?'done':(s==='rejected'?'error':'pending') },
        ];
    } else if (data.type === 'sell') {
        steps = [
            { name: 'Order Submitted',    desc: 'Your sell order was received.',           state: 'done' },
            { name: 'Crypto Received',    desc: 'Waiting for your crypto transfer.',        state: ['completed','processing','rejected'].includes(s) ? (s==='rejected'?'error':'done') : 'active' },
            { name: 'Processing Payout',  desc: 'We are processing your NGN payout.',      state: s === 'completed' ? 'done' : (s==='rejected'?'error':(s==='processing'?'active':'pending')) },
            { name: s==='rejected'?'Rejected':'Payout Sent', desc: s==='rejected'?'Order was rejected.':'Naira sent to your bank.', state: s==='completed'?'done':(s==='rejected'?'error':'pending') },
        ];
    } else {
        steps = [
            { name: 'Withdrawal Requested', desc: 'Your request was submitted.',           state: 'done' },
            { name: 'Under Review',          desc: 'Admin is reviewing your request.',     state: ['approved','cancelled'].includes(s) ? (s==='cancelled'?'error':'done') : 'active' },
            { name: s==='cancelled'?'Cancelled':'Payout Sent', desc: s==='cancelled'?'Request was cancelled.':'Funds transferred to your bank.', state: s==='approved'?'done':(s==='cancelled'?'error':'pending') },
        ];
    }
    return steps.map(step => {
        const cls = step.state === 'done' ? 'done' : step.state === 'active' ? 'active' : step.state === 'error' ? 'error' : '';
        const icon = step.state === 'done' ? '<i class="bi bi-check-lg"></i>' : step.state === 'active' ? '<i class="bi bi-clock-fill"></i>' : step.state === 'error' ? '<i class="bi bi-x-lg"></i>' : '';
        return `<div class="kx-step ${cls}">
            <div class="kx-step-line"></div>
            <div class="kx-step-dot">${icon}</div>
            <div class="kx-step-info">
                <div class="kx-step-name">${step.name}</div>
                <div class="kx-step-desc">${step.desc}</div>
            </div>
        </div>`;
    }).join('');
}

function row(label, val, cls = '', copyVal = null) {
    const copyBtn = copyVal ? `<button class="kx-copy-btn" onclick="copyText('${copyVal.replace(/'/g,"\\'")  }', this)" title="Copy"><i class="bi bi-copy"></i></button>` : '';
    return `<div class="kx-detail-row">
        <span class="kx-detail-label">${label}</span>
        <span class="kx-detail-val ${cls}">${val}${copyBtn}</span>
    </div>`;
}

function renderDrawer(data) {
    const typeLabels  = { buy: 'Buy Order', sell: 'Sell Order', withdrawal: 'Withdrawal' };
    const typeColors  = { buy: '#38bdf8', sell: '#00cc00', withdrawal: '#c084fc' };
    const typeIcons   = { buy: 'bi-arrow-down-circle-fill', sell: 'bi-arrow-up-circle-fill', withdrawal: 'bi-bank' };
    const sColor = statusColor(data.status);
    const colorMap = { green: '#00cc00', yellow: '#ffc107', red: '#ef4444' };

    document.getElementById('drawerTitle').innerHTML =
        `<i class="bi ${typeIcons[data.type]}" style="color:${typeColors[data.type]}"></i> ${typeLabels[data.type]}`;

    let html = '';

    // Status banner
    html += `<div style="background:rgba(${sColor==='green'?'0,204,0':sColor==='yellow'?'255,193,7':'239,68,68'},.08);border:1px solid rgba(${sColor==='green'?'0,204,0':sColor==='yellow'?'255,193,7':'239,68,68'},.2);border-radius:12px;padding:.85rem 1rem;display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;">
        <div>
            <div style="font-size:.72rem;color:#7a8599;text-transform:uppercase;letter-spacing:.06em;">Status</div>
            <div style="font-size:1rem;font-weight:700;color:${colorMap[sColor]};margin-top:2px;"><i class="bi ${statusIcon(data.status)} me-1"></i>${data.status.charAt(0).toUpperCase()+data.status.slice(1)}</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:.72rem;color:#7a8599;text-transform:uppercase;letter-spacing:.06em;">Amount</div>
            <div style="font-size:1rem;font-weight:700;color:#fff;margin-top:2px;">${fmt(data.naira_amount ?? data.amount)}</div>
        </div>
    </div>`;

    // Progress tracker
    html += `<div class="kx-detail-section">
        <div class="kx-detail-section-title">Progress</div>
        <div class="kx-timeline">${timelineSteps(data)}</div>
    </div>`;

    // Transaction info
    html += `<div class="kx-detail-section">
        <div class="kx-detail-section-title">Transaction Info</div>`;
    if (data.coin)      html += row('Currency / Coin', data.coin.toUpperCase());
    if (data.network)   html += row('Network', data.network);
    if (data.usd_amount) html += row('Amount (USD)', fmtUsd(data.usd_amount));
    if ((data.naira_amount || data.amount)) html += row('Amount (NGN)', fmt(data.naira_amount ?? data.amount));
    if (data.rate_used) {
        html += row('Exchange Rate',
            '<span style="color:#00cc00;font-weight:700">₦' +
            parseFloat(data.rate_used).toLocaleString('en-NG', {minimumFractionDigits:0, maximumFractionDigits:0}) +
            ' / $1</span>' +
            '<span style="font-size:.68rem;color:#f59e0b;margin-left:.4rem;font-weight:600">locked at submit</span>'
        );
    } else if (data.usd_amount > 0 && (data.naira_amount || data.amount)) {
        const impliedRate = ((data.naira_amount ?? data.amount) / data.usd_amount);
        html += row('Exchange Rate', '<span style="color:#7a8599;font-weight:700">₦' + impliedRate.toLocaleString('en-NG', {minimumFractionDigits:0, maximumFractionDigits:0}) + ' / $1</span>' + '<span style="font-size:.68rem;color:#7a8599;margin-left:.4rem">estimated</span>');
    }
    if (data.reference) html += row('Reference', `<span class="mono">${data.reference}</span>`, '', data.reference);
    if (data.created_at) html += row('Submitted', data.created_at, 'muted');
    if (data.updated_at && data.updated_at !== data.created_at) html += row('Last Updated', data.updated_at, 'muted');
    if (data.processed_at) html += row('Processed', data.processed_at, 'muted');
    html += `</div>`;

    // Type-specific info
    if (data.type === 'buy') {
        html += `<div class="kx-detail-section">
            <div class="kx-detail-section-title">Destination Wallet</div>`;
        if (data.wallet_address) html += row('Wallet Address', `<span class="mono">${data.wallet_address}</span>`, '', data.wallet_address);
        if (data.payment_method) html += row('Payment Source', data.payment_method);
        html += `</div>`;
        if (data.payment_proof) {
            html += `<div class="kx-detail-section">
                <div class="kx-detail-section-title">Payment Proof</div>
                <a href="${data.payment_proof}" target="_blank">
                    <img src="${data.payment_proof}" class="kx-proof-img" alt="Payment proof">
                </a>
            </div>`;
        } else if (data.proof_upload_url) {
            html += `<div class="kx-detail-section">
                <div class="kx-detail-section-title">Payment Proof</div>
                <div style="background:rgba(255,193,7,.07);border:1px solid rgba(255,193,7,.2);border-radius:10px;padding:.85rem 1rem;font-size:.82rem;color:#ffc107;margin-bottom:.75rem;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>No payment proof uploaded yet. Your trade cannot be processed until you upload proof.
                </div>
                <a href="${data.proof_upload_url}" class="btn-kx-upload-proof">
                    <i class="bi bi-upload"></i> Upload Payment Proof
                </a>
            </div>`;
        }
    }

    if (data.type === 'sell') {
        if (data.wallet_address || data.network) {
            html += `<div class="kx-detail-section">
                <div class="kx-detail-section-title">Crypto Details</div>`;
            if (data.wallet_address) html += row('Send-To Address', `<span class="mono">${data.wallet_address}</span>`, '', data.wallet_address);
            if (data.network) html += row('Network', data.network);
            html += `</div>`;
        }
        if (data.bank_name || data.account_number) {
            html += `<div class="kx-detail-section">
                <div class="kx-detail-section-title">Payout Bank Account</div>`;
            if (data.bank_name)      html += row('Bank', data.bank_name);
            if (data.account_name)   html += row('Account Name', data.account_name);
            if (data.account_number) html += row('Account No.', `<span class="mono">${data.account_number}</span>`, '', data.account_number);
            html += `</div>`;
        }
        if (data.payment_proof) {
            html += `<div class="kx-detail-section">
                <div class="kx-detail-section-title">Send Proof</div>
                <a href="${data.payment_proof}" target="_blank">
                    <img src="${data.payment_proof}" class="kx-proof-img" alt="Payment proof">
                </a>
            </div>`;
        } else if (data.proof_upload_url) {
            html += `<div class="kx-detail-section">
                <div class="kx-detail-section-title">Send Proof</div>
                <div style="background:rgba(255,193,7,.07);border:1px solid rgba(255,193,7,.2);border-radius:10px;padding:.85rem 1rem;font-size:.82rem;color:#ffc107;margin-bottom:.75rem;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>No proof uploaded yet. Upload a screenshot showing you sent the crypto.
                </div>
                <a href="${data.proof_upload_url}" class="btn-kx-upload-proof">
                    <i class="bi bi-upload"></i> Upload Send Proof
                </a>
            </div>`;
        }
    }

    if (data.type === 'withdrawal') {
        if (data.bank_name || data.account_number) {
            html += `<div class="kx-detail-section">
                <div class="kx-detail-section-title">Destination Bank</div>`;
            if (data.bank_name)      html += row('Bank', data.bank_name);
            if (data.account_name)   html += row('Account Name', data.account_name);
            if (data.account_number) html += row('Account No.', `<span class="mono">${data.account_number}</span>`, '', data.account_number);
            html += `</div>`;
        }
    }

    if (data.type === 'deposit') {
        html += `<div class="kx-detail-section">
            <div class="kx-detail-section-title">Payment Info</div>`;
        if (data.payment_method) html += row('Method', data.payment_method);
        html += `</div>`;
    }

    // Cancel button for pending transactions
    if (data.status === 'pending') {
        const cancelRoutes = {
            buy:        '/trades/buy/' + data.id + '/cancel',
            sell:       '/trades/sell/' + data.id + '/cancel',
            deposit:    '/deposits/' + data.id + '/cancel',
            withdrawal: '/withdrawals/' + data.id + '/cancel',
        };
        const cancelUrl = cancelRoutes[data.type];
        if (cancelUrl) {
            const lockNote = (data.type === 'buy' || data.type === 'sell')
                ? `<div style="font-size:.73rem;color:rgba(255,193,7,.75);text-align:center;margin-top:.4rem;"><i class="bi bi-clock me-1"></i>Buy & sell trades can be cancelled 30 minutes after submission.</div>`
                : '';
            html += `<div class="kx-detail-section" style="margin-top:.25rem;">
                <div class="kx-detail-section-title">Actions</div>
                <button class="kx-cancel-btn" id="kx-cancel-btn" onclick="cancelTrade('${cancelUrl}', this)">
                    <i class="bi bi-x-circle-fill"></i> Cancel This ${data.type.charAt(0).toUpperCase() + data.type.slice(1)}
                </button>
                ${lockNote}
                <div class="kx-cancel-msg" id="kx-cancel-msg" style="display:none;"></div>
            </div>`;
        }
    }

    document.getElementById('drawerBody').innerHTML = html;
}
</script>
@endpush
