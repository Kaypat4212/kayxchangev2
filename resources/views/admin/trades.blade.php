@extends('adminnavlayout')
@push('styles')
<style>
/* ── KX Trade Manager ─────────────────────────────────── */
:root{--kx-green:#00cc00;--kx-gdim:rgba(0,204,0,.12);--kx-glow:rgba(0,204,0,.22);
--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,.07);
--kx-text:#e4e8f0;--kx-muted:#7a8599;--kx-red:#ef4444;--kx-yellow:#f59e0b;
--kx-blue:#38bdf8;--kx-purple:#a855f7;}
body{background:var(--kx-dark)!important;color:var(--kx-text)!important;font-family:'Poppins',sans-serif;}
.kx-page-header{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;
padding:1rem 1.4rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;}
.kx-page-header h4{margin:0;font-size:1rem;font-weight:700;color:#fff;}
.kx-page-header small{font-size:.75rem;color:var(--kx-muted);}
.kx-panel{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;margin-bottom:1.25rem;overflow:hidden;}
.kx-panel-header{padding:.875rem 1.25rem;border-bottom:1px solid var(--kx-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;background:var(--kx-card2);}
.kx-panel-title{font-size:.9rem;font-weight:600;color:#fff;margin:0;}
.kx-table{width:100%;border-collapse:collapse;}
.kx-table thead th{background:var(--kx-card2);color:var(--kx-muted);font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;
padding:.7rem 1rem;border-bottom:1px solid var(--kx-border);white-space:nowrap;font-weight:600;}
.kx-table tbody tr{border-bottom:1px solid var(--kx-border);transition:background .15s;}
.kx-table tbody tr:hover{background:rgba(255,255,255,.025);}
.kx-table tbody tr:last-child{border-bottom:none;}
.kx-table td{padding:.75rem 1rem;font-size:.83rem;color:var(--kx-text);vertical-align:middle;}
.kx-table-wrap{overflow-x:auto;}
.kx-badge{display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:20px;font-size:.7rem;font-weight:600;}
.kx-badge-green{background:rgba(0,204,0,.12);color:var(--kx-green);}
.kx-badge-red{background:rgba(239,68,68,.12);color:var(--kx-red);}
.kx-badge-yellow{background:rgba(245,158,11,.12);color:var(--kx-yellow);}
.kx-badge-blue{background:rgba(56,189,248,.12);color:var(--kx-blue);}
.kx-badge-purple{background:rgba(168,85,247,.12);color:var(--kx-purple);}
.kx-badge-gray{background:rgba(255,255,255,.06);color:var(--kx-muted);}
.kx-sla-badge{display:inline-flex;align-items:center;gap:.25rem;border-radius:999px;padding:.12rem .45rem;font-size:.64rem;font-weight:700;margin-top:.3rem;}
.kx-sla-ok{background:rgba(0,204,0,.12);color:var(--kx-green);}
.kx-sla-warn{background:rgba(245,158,11,.13);color:var(--kx-yellow);}
.kx-sla-danger{background:rgba(239,68,68,.12);color:var(--kx-red);}
.btn-kx-green{background:var(--kx-green);color:#000;border:none;border-radius:8px;font-weight:600;font-size:.8rem;padding:.45rem 1rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;}
.btn-kx-green:hover{background:#00e600;color:#000;}
.btn-kx-outline{background:transparent;color:var(--kx-text);border:1px solid var(--kx-border);font-size:.8rem;padding:.45rem 1rem;border-radius:8px;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;cursor:pointer;}
.btn-kx-outline:hover{background:var(--kx-card2);color:#fff;border-color:rgba(255,255,255,.2);}
.btn-kx-danger{background:transparent;color:var(--kx-red);border:1px solid rgba(239,68,68,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;text-decoration:none;}
.btn-kx-danger:hover{background:rgba(239,68,68,.1);color:var(--kx-red);}
.btn-kx-edit{background:transparent;color:var(--kx-blue);border:1px solid rgba(56,189,248,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;text-decoration:none;}
.btn-kx-edit:hover{background:rgba(56,189,248,.1);color:var(--kx-blue);}
.btn-kx-approve{background:transparent;color:var(--kx-green);border:1px solid rgba(0,204,0,.3);font-size:.75rem;padding:.3rem .7rem;border-radius:7px;display:inline-flex;align-items:center;gap:.3rem;cursor:pointer;}
.btn-kx-approve:hover{background:var(--kx-gdim);color:var(--kx-green);}
.btn-kx-icon{background:transparent;border:1px solid var(--kx-border);border-radius:7px;color:var(--kx-muted);width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:.85rem;text-decoration:none;}
.btn-kx-icon:hover{background:var(--kx-card2);color:#fff;border-color:rgba(255,255,255,.15);}
.kx-input{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:8px!important;padding:.5rem .85rem!important;font-size:.83rem!important;}
.kx-input:focus{border-color:rgba(0,204,0,.4)!important;box-shadow:0 0 0 2px rgba(0,204,0,.1)!important;color:#fff!important;outline:none!important;}
.kx-input::placeholder{color:var(--kx-muted)!important;}
select.kx-input option{background:var(--kx-card2);color:var(--kx-text);}
.kx-alert-success{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:var(--kx-green);border-radius:8px;padding:.75rem 1rem;font-size:.84rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
.kx-alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:var(--kx-red);border-radius:8px;padding:.75rem 1rem;font-size:.84rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
/* Modal */
.modal-content{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;color:var(--kx-text);}
.modal-header{border-bottom:1px solid var(--kx-border);padding:1rem 1.25rem;}
.modal-body{padding:1.25rem;}
.modal-footer{border-top:1px solid var(--kx-border);padding:.875rem 1.25rem;}
.modal-title{font-weight:700;font-size:.95rem;color:#fff;}
/* Confirm modal */
.kx-confirm-box{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:1rem 1.25rem;margin-bottom:1rem;}
.kx-confirm-box .lbl{font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.15rem;}
.kx-confirm-box .val{font-size:.9rem;color:#fff;font-weight:600;}
.kx-checklist{background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.25);border-radius:10px;padding:.875rem 1rem;margin-top:1rem;}
.kx-checklist label{font-size:.84rem;color:var(--kx-text);cursor:pointer;display:flex;align-items:flex-start;gap:.6rem;}
.kx-checklist input[type=checkbox]{margin-top:.15rem;accent-color:var(--kx-yellow);}
/* Stats */
.kx-stat-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.875rem;margin-bottom:1.25rem;}
.kx-stat{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;}
.kx-stat-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.icon-green{background:var(--kx-gdim);color:var(--kx-green);}
.icon-yellow{background:rgba(245,158,11,.15);color:var(--kx-yellow);}
.icon-blue{background:rgba(56,189,248,.12);color:var(--kx-blue);}
.icon-red{background:rgba(239,68,68,.12);color:var(--kx-red);}
.icon-purple{background:rgba(168,85,247,.12);color:var(--kx-purple);}
.kx-stat-label{font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;}
.kx-stat-value{font-size:1.4rem;font-weight:700;color:#fff;line-height:1.1;}
/* Filter bar */
.kx-filter-bar{padding:.6rem 1.25rem;background:var(--kx-card);border-bottom:1px solid var(--kx-border);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.kx-filter-btn{background:transparent;border:1px solid var(--kx-border);border-radius:20px;color:var(--kx-muted);font-size:.73rem;padding:.25rem .75rem;cursor:pointer;}
.kx-filter-btn.active,.kx-filter-btn:hover{background:var(--kx-card2);color:var(--kx-text);border-color:rgba(255,255,255,.15);}
.kx-filter-btn.f-pending.active{background:rgba(245,158,11,.12);color:var(--kx-yellow);border-color:rgba(245,158,11,.3);}
.kx-filter-btn.f-completed.active{background:var(--kx-gdim);color:var(--kx-green);border-color:rgba(0,204,0,.3);}
.kx-filter-btn.f-failed.active{background:rgba(239,68,68,.1);color:var(--kx-red);border-color:rgba(239,68,68,.3);}
/* Proof thumbnail */
.proof-thumb{width:36px;height:36px;border-radius:6px;object-fit:cover;border:1px solid var(--kx-border);cursor:pointer;}
.proof-thumb:hover{opacity:.8;}
/* Sell detail info */
.kx-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.kx-info-item{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:.6rem .875rem;}
.kx-info-item .lbl{font-size:.68rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;}
.kx-info-item .val{font-size:.85rem;color:#fff;font-weight:600;word-break:break-all;}
.nav-tabs-kx{border-bottom:1px solid var(--kx-border);margin-bottom:0;display:flex;gap:.25rem;padding:.75rem 1.25rem .75rem;}
.nav-tabs-kx .nav-link{background:transparent;border:1px solid var(--kx-border);color:var(--kx-muted);border-radius:8px;font-size:.8rem;padding:.35rem .9rem;}
.nav-tabs-kx .nav-link.active{background:var(--kx-green);color:#000;border-color:var(--kx-green);font-weight:600;}
.nav-tabs-kx .nav-link:hover:not(.active){background:var(--kx-card2);color:var(--kx-text);}
.kx-warn-banner{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:8px;padding:.65rem 1rem;font-size:.8rem;color:var(--kx-yellow);display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.75rem;}
</style>
@endpush
@section('content')
<div class="container-fluid py-3 px-3 px-md-4">

    {{-- Page Header --}}
    <div class="kx-page-header">
        <div>
            <h4><i class="bi bi-arrow-left-right me-2" style="color:var(--kx-green)"></i>Transaction Management</h4>
            <small>Review all trades · Confirm payments before approving</small>
        </div>
        <button class="btn-kx-green" onclick="aiTradeSummary()" id="btn-ai-summary">
            <i class="bi bi-robot"></i> AI Summary
        </button>
    </div>

    {{-- AI Trade Summary result --}}
    <div id="ai-summary-box" style="display:none;background:var(--kx-card);border:1px solid rgba(0,204,0,.25);border-radius:12px;padding:1.25rem;margin-bottom:1.25rem;position:relative;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
            <span style="font-size:.8rem;font-weight:600;color:var(--kx-green)"><i class="bi bi-robot me-1"></i>AI Trade Summary</span>
            <button onclick="document.getElementById('ai-summary-box').style.display='none'" style="background:none;border:none;color:var(--kx-muted);cursor:pointer;font-size:1rem;">&times;</button>
        </div>
        <div id="ai-summary-content" style="font-size:.84rem;color:var(--kx-text);line-height:1.7;"></div>
    </div>

    {{-- AI Suspicious Trade Modal --}}
    <div class="modal fade" id="aiSpotModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-shield-exclamation me-2" style="color:#f59e0b"></i>AI Suspicious Trade Analysis</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="ai-spot-loading" style="text-align:center;padding:1.5rem;display:none;">
                        <div class="spinner-border spinner-border-sm" style="color:var(--kx-green)"></div>
                        <p style="font-size:.8rem;color:var(--kx-muted);margin-top:.75rem">Analysing trade patterns…</p>
                    </div>
                    <div id="ai-spot-content" style="font-size:.84rem;color:var(--kx-text);line-height:1.7;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="kx-alert-success"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="kx-alert-error"><i class="bi bi-exclamation-circle-fill"></i>{{ session('error') }}</div>@endif

    {{-- Stats --}}
    <div class="kx-stat-row">
        <div class="kx-stat"><div class="kx-stat-icon icon-blue"><i class="bi bi-graph-up"></i></div>
            <div><div class="kx-stat-label">Total Tx</div><div class="kx-stat-value">{{ number_format($statistics['total_transactions']) }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-yellow"><i class="bi bi-clock-history"></i></div>
            <div><div class="kx-stat-label">Pending</div><div class="kx-stat-value">{{ number_format($statistics['pending_transactions']) }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-green"><i class="bi bi-check-circle-fill"></i></div>
            <div><div class="kx-stat-label">Completed</div><div class="kx-stat-value">{{ number_format($statistics['completed_transactions']) }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-purple"><i class="bi bi-cash-coin"></i></div>
            <div><div class="kx-stat-label">Volume</div><div class="kx-stat-value" style="font-size:1rem">₦{{ number_format($statistics['total_volume'],0) }}</div></div></div>
    </div>

    <div class="kx-panel">
        {{-- Tab buttons --}}
        <div class="nav-tabs-kx">
            <button class="nav-link active" onclick="switchTab('buy',this)">
                <i class="bi bi-arrow-down-circle me-1"></i>Buy Trades
                @php $pendingBuy = $buyTrades->where('status','pending')->count(); @endphp
                @if($pendingBuy > 0)<span class="kx-badge kx-badge-yellow ms-1" style="font-size:.65rem">{{ $pendingBuy }}</span>@endif
            </button>
            <button class="nav-link" onclick="switchTab('sell',this)">
                <i class="bi bi-arrow-up-circle me-1"></i>Sell Trades
                @php $pendingSell = $sellTrades->where('status','pending')->count(); @endphp
                @if($pendingSell > 0)<span class="kx-badge kx-badge-yellow ms-1" style="font-size:.65rem">{{ $pendingSell }}</span>@endif
            </button>
            <button class="nav-link" onclick="switchTab('dep',this)"><i class="bi bi-bank me-1"></i>Deposits ({{ $deposits->count() }})</button>
            <button class="nav-link" onclick="switchTab('wd',this)"><i class="bi bi-arrow-up-right-circle me-1"></i>Withdrawals ({{ $withdrawals->count() }})</button>
        </div>

        {{-- ─────────────── BUY TRADES ─────────────── --}}
        <div id="tab-buy">
            {{-- Filter bar --}}
            <div class="kx-filter-bar">
                <span style="font-size:.72rem;color:var(--kx-muted);margin-right:.25rem"><i class="bi bi-funnel me-1"></i>Filter:</span>
                <button class="kx-filter-btn active" onclick="filterRows('buy','all',this)">All</button>
                <button class="kx-filter-btn f-pending" onclick="filterRows('buy','pending',this)">Pending</button>
                <button class="kx-filter-btn f-completed" onclick="filterRows('buy','completed',this)">Completed</button>
                <button class="kx-filter-btn f-failed" onclick="filterRows('buy','failed',this)">Failed/Cancelled</button>
            </div>
            <div class="kx-warn-banner">
                <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                <span><strong>Important:</strong> Before marking a Buy Trade as <strong>Completed</strong>, confirm you have verified the user's payment proof and sent the requested crypto to their wallet address.</span>
            </div>
            <div class="kx-table-wrap">
            <table class="kx-table" id="buy-table">
                <thead><tr>
                    <th>#</th><th>User</th><th>Coin</th><th>USD</th><th>Amount (₦)</th><th>Method</th><th>Wallet</th><th>Proof</th><th>Status</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                @forelse($buyTrades as $t)
                @php
                    $s = $t->status;
                    $statusClass = in_array($s,['completed','successful','approved']) ? 'completed'
                        : ($s==='pending' ? 'pending'
                        : (in_array($s,['failed','cancelled','rejected']) ? 'failed' : 'other'));
                    $user = $t->user;
                @endphp
                <tr data-status="{{ $statusClass }}" data-created="{{ $t->created_at?->toIso8601String() }}" data-kind="buy">
                    <td style="color:var(--kx-muted);white-space:nowrap">#{{ $t->id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:.84rem">{{ $user->name ?? $t->name ?? 'N/A' }}</div>
                        <div style="font-size:.72rem;color:var(--kx-muted)">{{ $user->email ?? '—' }}</div>
                    </td>
                    <td><span class="kx-badge kx-badge-green" style="font-family:monospace">{{ strtoupper($t->coin ?? '—') }}</span></td>
                    <td style="color:var(--kx-blue);font-size:.82rem">
                        ${{ number_format($t->usd_amount ?? 0, 2) }}
                        <div class="kx-crypto-sub" data-coin="{{ strtoupper($t->coin ?? '') }}" data-usd="{{ $t->usd_amount ?? 0 }}" style="font-size:.68rem;color:#f7931a;margin-top:.1rem"></div>
                    </td>
                    <td style="font-weight:700">₦{{ number_format($t->naira_amount ?? 0, 2) }}</td>
                    <td style="font-size:.74rem;color:var(--kx-muted);white-space:nowrap">{{ $t->payment_method ?? 'Bank Transfer' }}</td>
                    <td>
                        @if($t->wallet_address)
                            <span style="font-size:.72rem;font-family:monospace;color:var(--kx-muted);max-width:145px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $t->wallet_address }}">{{ $t->wallet_address }}</span>
                        @else
                            <span style="font-size:.75rem;color:var(--kx-muted)">—</span>
                        @endif
                    </td>
                    <td>
                        @if($t->payment_proof)
                            <a href="{{ asset('storage/'.$t->payment_proof) }}" target="_blank" title="View proof">
                                <img src="{{ asset('storage/'.$t->payment_proof) }}" class="proof-thumb" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                                <span class="btn-kx-icon" style="display:none"><i class="bi bi-image"></i></span>
                            </a>
                        @else
                            <span style="font-size:.75rem;color:var(--kx-muted)">—</span>
                        @endif
                    </td>
                    <td>
                        @if(in_array($s,['completed','successful','approved']))
                            <span class="kx-badge kx-badge-green"><i class="bi bi-check me-1"></i>{{ ucfirst($s) }}</span>
                        @elseif($s === 'pending')
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-clock me-1"></i>Pending</span>
                        @elseif(in_array($s,['failed','cancelled','rejected']))
                            <span class="kx-badge kx-badge-red">{{ ucfirst($s) }}</span>
                        @else
                            <span class="kx-badge kx-badge-gray">{{ ucfirst($s) }}</span>
                        @endif
                    </td>
                    <td style="font-size:.74rem;color:var(--kx-muted);white-space:nowrap">
                        <div>{{ $t->created_at?->format('d M Y H:i') ?? '—' }}</div>
                        @if($s === 'pending')
                        <span class="kx-sla-badge kx-sla-ok js-sla-badge" data-created="{{ $t->created_at?->toIso8601String() }}" data-kind="buy"></span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:.35rem;align-items:center;flex-wrap:wrap">
                            {{-- View Details button --}}
                            <button type="button" class="btn-kx-edit"
                                onclick="openBuyDetail({
                                    id:'{{ $t->id }}',
                                    user:'{{ addslashes($user->name ?? $t->name ?? 'N/A') }}',
                                    email:'{{ addslashes($user->email ?? '—') }}',
                                    coin:'{{ strtoupper($t->coin ?? '—') }}',
                                    network:'{{ $t->network ?? '—' }}',
                                    wallet:'{{ addslashes($t->wallet_address ?? '—') }}',
                                    usd:'{{ number_format($t->usd_amount ?? 0,2) }}',
                                    naira:'{{ number_format($t->naira_amount ?? 0,2) }}',
                                    proof:'{{ $t->payment_proof ? asset('storage/'.$t->payment_proof) : '' }}',
                                    adminProof:'{{ $t->admin_payment_proof ? asset('storage/'.$t->admin_payment_proof) : '' }}',
                                    txid:'{{ addslashes($t->blockchain_txid ?? '—') }}',
                                    method:'{{ addslashes($t->payment_method ?? 'Bank Transfer') }}',
                                    status:'{{ $s }}'
                                })">
                                <i class="bi bi-eye"></i> View
                            </button>
                            {{-- Status change form --}}
                            <form id="buy-form-{{ $t->id }}" action="{{ route('admin.buy.updateStatus', $t->id) }}" method="POST" style="display:flex;gap:.3rem;align-items:center">
                                @csrf
                                <select name="status" id="buy-sel-{{ $t->id }}" class="kx-input" style="width:108px;padding:.3rem .5rem!important;font-size:.72rem!important">
                                    @foreach(['pending','completed','rejected'] as $st)
                                    <option value="{{ $st }}" {{ $s === $st ? 'selected':'' }}>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="btn-kx-green"
                                    style="padding:.3rem .6rem;font-size:.72rem"
                                    onclick="handleBuyApprove({{ $t->id }},'{{ addslashes($user->name ?? $t->name ?? 'N/A') }}','{{ addslashes($user->bank_name ?? 'N/A') }}','{{ addslashes($user->account_number ?? 'N/A') }}','{{ addslashes($user->account_name ?? 'N/A') }}','{{ addslashes($t->wallet_address ?? 'N/A') }}','{{ strtoupper($t->coin ?? '') }}','{{ number_format($t->naira_amount ?? 0,2) }}','{{ number_format($t->usd_amount ?? 0,2) }}','{{ addslashes($t->blockchain_txid ?? '') }}','{{ $t->admin_payment_proof ? asset('storage/'.$t->admin_payment_proof) : '' }}')">
                                    <i class="bi bi-save"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" style="text-align:center;color:var(--kx-muted);padding:2.5rem"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>No buy trades found.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- ─────────────── SELL TRADES ─────────────── --}}
        <div id="tab-sell" style="display:none">
            {{-- Filter bar --}}
            <div class="kx-filter-bar">
                <span style="font-size:.72rem;color:var(--kx-muted);margin-right:.25rem"><i class="bi bi-funnel me-1"></i>Filter:</span>
                <button class="kx-filter-btn active" onclick="filterRows('sell','all',this)">All</button>
                <button class="kx-filter-btn f-pending" onclick="filterRows('sell','pending',this)">Pending</button>
                <button class="kx-filter-btn f-completed" onclick="filterRows('sell','completed',this)">Completed</button>
                <button class="kx-filter-btn f-failed" onclick="filterRows('sell','failed',this)">Failed/Cancelled</button>
            </div>
            <div class="kx-warn-banner">
                <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                <span><strong>Important:</strong> Before marking a Sell Trade as <strong>Completed</strong>, confirm you have received the crypto from the user's wallet AND successfully transferred the naira payment to their bank account.</span>
            </div>
            <div class="kx-table-wrap">
            <table class="kx-table" id="sell-table">
                <thead><tr>
                    <th>#</th><th>User</th><th>Coin</th><th>Amount (₦)</th><th>Bank Details</th><th>Wallet</th><th>Proof</th><th>Status</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                @forelse($sellTrades as $t)
                @php
                    $s = $t->status;
                    $statusClass = in_array($s,['completed','successful','approved']) ? 'completed'
                        : ($s==='pending' ? 'pending'
                        : (in_array($s,['failed','cancelled','rejected']) ? 'failed' : 'other'));
                    $user = $t->user;
                @endphp
                <tr data-status="{{ $statusClass }}" data-created="{{ $t->created_at?->toIso8601String() }}" data-kind="sell">
                    <td style="color:var(--kx-muted);white-space:nowrap">#{{ $t->id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:.84rem">{{ $user->name ?? $t->name ?? 'N/A' }}</div>
                        <div style="font-size:.72rem;color:var(--kx-muted)">{{ $user->email ?? '—' }}</div>
                    </td>
                    <td><span class="kx-badge kx-badge-yellow" style="font-family:monospace">{{ strtoupper($t->coin ?? '—') }}</span></td>
                    <td style="font-weight:700">
                        ₦{{ number_format($t->naira_amount ?? $t->amount ?? 0, 2) }}
                        <div style="font-size:.72rem;color:var(--kx-blue);margin-top:.1rem">${{ number_format($t->usd_amount ?? 0, 2) }}</div>
                        <div class="kx-crypto-sub" data-coin="{{ strtoupper($t->coin ?? '') }}" data-usd="{{ $t->usd_amount ?? 0 }}" style="font-size:.68rem;color:var(--kx-yellow);margin-top:.05rem"></div>
                    </td>
                    <td>
                        {{-- Sell trade: show where admin must PAY the user --}}
                        @if($t->bank_name || $t->account_number)
                        <div style="font-size:.76rem;line-height:1.6">
                            <div style="color:var(--kx-text);font-weight:600">{{ $t->account_name ?? '—' }}</div>
                            <div style="color:var(--kx-muted)">{{ $t->bank_name ?? '—' }}</div>
                            <div style="color:var(--kx-blue);font-family:monospace;font-size:.78rem">{{ $t->account_number ?? '—' }}</div>
                        </div>
                        @elseif($t->payment_method)
                        <span class="kx-badge kx-badge-blue">{{ $t->payment_method }}</span>
                        @else
                        <span style="font-size:.75rem;color:var(--kx-muted)">—</span>
                        @endif
                    </td>
                    <td>
                        @if($t->wallet_address)
                        <span style="font-size:.72rem;font-family:monospace;color:var(--kx-muted);max-width:90px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $t->wallet_address }}">{{ $t->wallet_address }}</span>
                        @else
                        <span style="color:var(--kx-muted)">—</span>
                        @endif
                    </td>
                    <td>
                        @php $proofPath = $t->payment_proof ?? $t->proof; @endphp
                        @if($proofPath)
                            <a href="{{ asset('storage/'.$proofPath) }}" target="_blank">
                                <img src="{{ asset('storage/'.$proofPath) }}" class="proof-thumb" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                                <span class="btn-kx-icon" style="display:none"><i class="bi bi-image"></i></span>
                            </a>
                        @else
                        <span style="font-size:.75rem;color:var(--kx-muted)">—</span>
                        @endif
                    </td>
                    <td>
                        @if(in_array($s,['completed','successful','approved']))
                            <span class="kx-badge kx-badge-green"><i class="bi bi-check me-1"></i>{{ ucfirst($s) }}</span>
                        @elseif($s === 'pending')
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-clock me-1"></i>Pending</span>
                        @elseif(in_array($s,['failed','cancelled','rejected']))
                            <span class="kx-badge kx-badge-red">{{ ucfirst($s) }}</span>
                        @else
                            <span class="kx-badge kx-badge-gray">{{ ucfirst($s) }}</span>
                        @endif
                    </td>
                    <td style="font-size:.74rem;color:var(--kx-muted);white-space:nowrap">
                        <div>{{ $t->created_at?->format('d M Y H:i') ?? '—' }}</div>
                        @if($s === 'pending')
                        <span class="kx-sla-badge kx-sla-ok js-sla-badge" data-created="{{ $t->created_at?->toIso8601String() }}" data-kind="sell"></span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:.35rem;align-items:center;flex-wrap:wrap">
                            {{-- View Full Details --}}
                            <button type="button" class="btn-kx-edit"
                                onclick="openSellDetail({
                                    id:'{{ $t->id }}',
                                    user:'{{ addslashes($user->name ?? $t->name ?? 'N/A') }}',
                                    email:'{{ addslashes($user->email ?? '—') }}',
                                    coin:'{{ strtoupper($t->coin ?? '—') }}',
                                    wallet:'{{ addslashes($t->wallet_address ?? '—') }}',
                                    usd:'{{ number_format($t->usd_amount ?? 0,2) }}',
                                    naira:'{{ number_format($t->naira_amount ?? $t->amount ?? 0,2) }}',
                                    bank:'{{ addslashes($t->bank_name ?? '—') }}',
                                    accNum:'{{ addslashes($t->account_number ?? '—') }}',
                                    accName:'{{ addslashes($t->account_name ?? '—') }}',
                                    method:'{{ addslashes($t->payment_method ?? 'Bank Transfer') }}',
                                    proof:'{{ $proofPath ? asset('storage/'.$proofPath) : '' }}',
                                    status:'{{ $s }}'
                                })">
                                <i class="bi bi-eye"></i> View
                            </button>
                            {{-- Status form --}}
                            <form id="sell-form-{{ $t->id }}" action="{{ route('admin.sells.updateStatus', $t->id) }}" method="POST" style="display:flex;gap:.3rem;align-items:center">
                                @csrf @method('PATCH')
                                <select name="status" id="sell-sel-{{ $t->id }}" class="kx-input" style="width:108px;padding:.3rem .5rem!important;font-size:.72rem!important">
                                    @foreach(['pending','completed','rejected'] as $st)
                                    <option value="{{ $st }}" {{ $s === $st ? 'selected':'' }}>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="btn-kx-green"
                                    style="padding:.3rem .6rem;font-size:.72rem"
                                    onclick="handleSellApprove({{ $t->id }},'{{ addslashes($user->name ?? $t->name ?? 'N/A') }}','{{ addslashes($t->bank_name ?? '—') }}','{{ addslashes($t->account_number ?? '—') }}','{{ addslashes($t->account_name ?? '—') }}','{{ addslashes($t->wallet_address ?? '—') }}','{{ strtoupper($t->coin ?? '') }}','{{ number_format($t->naira_amount ?? $t->amount ?? 0,2) }}','{{ number_format($t->usd_amount ?? 0,2) }}')">
                                    <i class="bi bi-save"></i>
                                </button>
                            </form>
                            @if($t->user_id)
                            <button type="button" class="btn-kx-icon" title="AI Fraud Check"
                                onclick="aiSpotSuspicious({{ $t->user_id }}, '{{ addslashes($user->name ?? 'User') }}')"
                                style="color:#f59e0b;border-color:rgba(245,158,11,.35);">
                                <i class="bi bi-shield-exclamation"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align:center;color:var(--kx-muted);padding:2.5rem"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>No sell trades found.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- ─────────────── DEPOSITS ─────────────── --}}
        <div id="tab-dep" style="display:none">
            <div class="kx-table-wrap">
            <table class="kx-table">
                <thead><tr><th>#</th><th>User</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($deposits as $d)
                <tr>
                    <td style="color:var(--kx-muted)">#{{ $d->id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:.84rem">{{ $d->user->name ?? 'N/A' }}</div>
                        <div style="font-size:.72rem;color:var(--kx-muted)">{{ $d->user->email ?? '—' }}</div>
                    </td>
                    <td style="font-weight:700;color:var(--kx-blue)">₦{{ number_format($d->amount, 2) }}</td>
                    <td style="font-size:.78rem;color:var(--kx-muted)">{{ $d->payment_method ?? 'Bank Transfer' }}</td>
                    <td>
                        @if(in_array($d->status,['approved','successful','completed']))
                            <span class="kx-badge kx-badge-green"><i class="bi bi-check me-1"></i>Approved</span>
                        @elseif($d->status === 'pending')
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-clock me-1"></i>Pending</span>
                        @else
                            <span class="kx-badge kx-badge-red">{{ ucfirst($d->status) }}</span>
                        @endif
                    </td>
                    <td style="font-size:.74rem;color:var(--kx-muted)">{{ $d->created_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--kx-muted);padding:2.5rem"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>No deposits found.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- ─────────────── WITHDRAWALS ─────────────── --}}
        <div id="tab-wd" style="display:none">
            <div class="kx-table-wrap">
            <table class="kx-table">
                <thead><tr><th>#</th><th>User</th><th>Amount</th><th>Bank Details</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($withdrawals as $w)
                <tr>
                    <td style="color:var(--kx-muted)">#{{ $w->id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:.84rem">{{ $w->user->name ?? 'N/A' }}</div>
                        <div style="font-size:.72rem;color:var(--kx-muted)">{{ $w->user->email ?? '—' }}</div>
                    </td>
                    <td style="font-weight:700;color:var(--kx-yellow)">₦{{ number_format($w->amount, 2) }}</td>
                    <td>
                        @php $wu = $w->user; @endphp
                        @if($wu && $wu->bank_name)
                        <div style="font-size:.76rem;line-height:1.6">
                            <div style="font-weight:600;color:var(--kx-text)">{{ $wu->account_name ?? '—' }}</div>
                            <div style="color:var(--kx-muted)">{{ $wu->bank_name }}</div>
                            <div style="color:var(--kx-blue);font-family:monospace;font-size:.78rem">{{ $wu->account_number ?? '—' }}</div>
                        </div>
                        @else
                        <span style="font-size:.76rem;color:var(--kx-muted)">No bank details</span>
                        @endif
                    </td>
                    <td>
                        @if($w->status === 'approved')
                            <span class="kx-badge kx-badge-green"><i class="bi bi-check me-1"></i>Approved</span>
                        @elseif($w->status === 'pending')
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-clock me-1"></i>Pending</span>
                        @else
                            <span class="kx-badge kx-badge-red">{{ ucfirst($w->status) }}</span>
                        @endif
                    </td>
                    <td style="font-size:.74rem;color:var(--kx-muted)">{{ $w->created_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--kx-muted);padding:2.5rem"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>No withdrawals found.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>

    </div>{{-- /kx-panel --}}
</div>{{-- /container --}}

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Buy Trade Approval Confirmation                        --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="buyConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shield-check me-2" style="color:var(--kx-yellow)"></i>Buy Trade — Approval Checklist</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.84rem;color:var(--kx-muted);margin-bottom:1rem">
                    You are about to change status for <strong style="color:#fff">Buy Trade #<span id="bc-id"></span></strong>.
                    The new status is: <strong id="bc-newstatus" style="color:var(--kx-yellow)"></strong>.
                </p>

                {{-- Trade summary --}}
                <div class="kx-confirm-box">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                        <div><div class="lbl">User</div><div class="val" id="bc-user"></div></div>
                        <div><div class="lbl">Coin</div><div class="val" id="bc-coin"></div></div>
                        <div><div class="lbl">USD Amount</div><div class="val" id="bc-usd"></div></div>
                        <div><div class="lbl">Naira Amount</div><div class="val" id="bc-naira"></div></div>
                        <div style="grid-column:1/-1;background:rgba(247,147,26,.08);border:1px solid rgba(247,147,26,.25);border-radius:8px;padding:.6rem .875rem;margin-top:.25rem">
                            <div class="lbl" style="color:#f7931a"><i class="bi bi-send me-1"></i>Crypto Qty to Send</div>
                            <div class="val" id="bc-crypto" style="color:#f7931a;font-size:1.15rem;font-weight:800;letter-spacing:.02em">—</div>
                        </div>
                    </div>
                </div>

                {{-- Crypto delivery details --}}
                <div class="kx-confirm-box" id="bc-wallet-block">
                    <div style="margin-bottom:.5rem"><span class="kx-badge kx-badge-blue" style="font-size:.7rem"><i class="bi bi-send me-1"></i>Crypto Delivery</span></div>
                    <div><div class="lbl">Send To Wallet</div><div class="val" id="bc-wallet" style="font-family:monospace;font-size:.8rem;word-break:break-all"></div></div>
                </div>

                {{-- User's registered bank (for reference) --}}
                <div class="kx-confirm-box" id="bc-bank-block">
                    <div style="margin-bottom:.5rem"><span class="kx-badge kx-badge-purple" style="font-size:.7rem"><i class="bi bi-bank me-1"></i>User's Registered Bank</span></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
                        <div><div class="lbl">Bank</div><div class="val" id="bc-bank"></div></div>
                        <div><div class="lbl">Account No.</div><div class="val" id="bc-accnum" style="font-family:monospace"></div></div>
                        <div><div class="lbl">Account Name</div><div class="val" id="bc-accname"></div></div>
                    </div>
                </div>

                <form id="buy-modal-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="status" id="bc-status-hidden" value="pending">
                    <input type="hidden" id="bc-existing-proof" value="">

                    <div class="kx-confirm-box">
                        <div style="margin-bottom:.5rem"><span class="kx-badge kx-badge-green" style="font-size:.7rem"><i class="bi bi-receipt-cutoff me-1"></i>Admin Delivery Evidence</span></div>
                        <div class="mb-2">
                            <label for="bc-txid" class="form-label" style="font-size:.76rem;color:var(--kx-muted)">Blockchain TXID (optional if image proof is provided)</label>
                            <input type="text" id="bc-txid" name="blockchain_txid" class="form-control kx-input" placeholder="Paste transaction hash">
                        </div>
                        <div class="mb-1">
                            <label for="bc-proof" class="form-label" style="font-size:.76rem;color:var(--kx-muted)">Payment Proof Image (required when TXID is empty for completed status)</label>
                            <input type="file" id="bc-proof" name="admin_payment_proof" class="form-control kx-input" accept="image/png,image/jpeg,image/webp">
                        </div>
                        <div id="bc-existing-proof-wrap" style="display:none;font-size:.74rem;color:var(--kx-muted)">
                            Existing proof on file: <a id="bc-existing-proof-link" href="#" target="_blank" style="color:var(--kx-blue)">View current proof</a>
                        </div>
                    </div>
                </form>

                {{-- Checklist (only shown when completing) --}}
                <div id="bc-checklist-area" class="kx-checklist" style="display:none">
                    <p style="font-size:.78rem;color:var(--kx-yellow);font-weight:600;margin-bottom:.6rem"><i class="bi bi-exclamation-triangle me-1"></i>Completing this trade — please confirm:</p>
                    <label><input type="checkbox" id="bc-chk1" onchange="checkBuyReady()"> I have verified the user's payment proof.</label>
                    <label style="margin-top:.4rem"><input type="checkbox" id="bc-chk2" onchange="checkBuyReady()"> I have sent <strong id="bc-coin-chk"></strong> to the wallet address above.</label>
                </div>
            </div>
            <div class="modal-footer" style="justify-content:flex-end;gap:.5rem">
                <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="bc-confirm-btn" class="btn-kx-green" onclick="submitBuyForm()">
                    <i class="bi bi-check-circle me-1"></i>Confirm &amp; Update
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Sell Trade Approval Confirmation                       --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="sellConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shield-check me-2" style="color:var(--kx-yellow)"></i>Sell Trade — Approval Checklist</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.84rem;color:var(--kx-muted);margin-bottom:1rem">
                    You are about to change status for <strong style="color:#fff">Sell Trade #<span id="sc-id"></span></strong>.
                    The new status is: <strong id="sc-newstatus" style="color:var(--kx-yellow)"></strong>.
                </p>

                {{-- Trade summary --}}
                <div class="kx-confirm-box">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                        <div><div class="lbl">User</div><div class="val" id="sc-user"></div></div>
                        <div><div class="lbl">Coin Sold</div><div class="val" id="sc-coin"></div></div>
                        <div><div class="lbl">Naira to Pay</div><div class="val" id="sc-naira" style="color:var(--kx-yellow)"></div></div>
                        <div><div class="lbl">USD Value</div><div class="val" id="sc-usd"></div></div>
                        <div style="grid-column:1/-1;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:8px;padding:.6rem .875rem;margin-top:.25rem">
                            <div class="lbl" style="color:var(--kx-yellow)"><i class="bi bi-arrow-down-circle me-1"></i>Crypto Qty Received</div>
                            <div class="val" id="sc-crypto" style="color:var(--kx-yellow);font-size:1.15rem;font-weight:800;letter-spacing:.02em">—</div>
                        </div>
                    </div>
                </div>

                {{-- Where admin must send crypto from (user's wallet they sent from) --}}
                <div class="kx-confirm-box">
                    <div style="margin-bottom:.5rem"><span class="kx-badge kx-badge-yellow" style="font-size:.7rem"><i class="bi bi-wallet2 me-1"></i>Crypto Received From</span></div>
                    <div><div class="lbl">Wallet Address</div><div class="val" id="sc-wallet" style="font-family:monospace;font-size:.8rem;word-break:break-all"></div></div>
                </div>

                {{-- Where admin must pay the naira --}}
                <div class="kx-confirm-box">
                    <div style="margin-bottom:.5rem"><span class="kx-badge kx-badge-green" style="font-size:.7rem"><i class="bi bi-bank me-1"></i>Pay User To This Account</span></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
                        <div><div class="lbl">Bank</div><div class="val" id="sc-bank"></div></div>
                        <div><div class="lbl">Account No.</div><div class="val" id="sc-accnum" style="font-family:monospace"></div></div>
                        <div><div class="lbl">Account Name</div><div class="val" id="sc-accname"></div></div>
                        <div><div class="lbl">Method</div><div class="val" id="sc-method"></div></div>
                    </div>
                </div>

                {{-- Checklist (only shown when completing) --}}
                <div id="sc-checklist-area" class="kx-checklist" style="display:none">
                    <p style="font-size:.78rem;color:var(--kx-yellow);font-weight:600;margin-bottom:.6rem"><i class="bi bi-exclamation-triangle me-1"></i>Completing this trade — please confirm:</p>
                    <label><input type="checkbox" id="sc-chk1" onchange="checkSellReady()"> I have received the <strong id="sc-coin-chk"></strong> from the user's wallet.</label>
                    <label style="margin-top:.4rem"><input type="checkbox" id="sc-chk2" onchange="checkSellReady()"> I have transferred <strong id="sc-naira-chk"></strong> to <strong id="sc-accname-chk"></strong> — <strong id="sc-accnum-chk"></strong>.</label>
                </div>
            </div>
            <div class="modal-footer" style="justify-content:flex-end;gap:.5rem">
                <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="sc-confirm-btn" class="btn-kx-green" onclick="submitSellForm()">
                    <i class="bi bi-check-circle me-1"></i>Confirm &amp; Update
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Buy Trade Full Detail View                             --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="buyDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-down-circle me-2" style="color:var(--kx-green)"></i>Buy Trade Details #<span id="bd-id"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="kx-info-grid">
                    <div class="kx-info-item"><div class="lbl">User</div><div class="val" id="bd-user"></div></div>
                    <div class="kx-info-item"><div class="lbl">Email</div><div class="val" id="bd-email"></div></div>
                    <div class="kx-info-item"><div class="lbl">Coin</div><div class="val" id="bd-coin"></div></div>
                    <div class="kx-info-item"><div class="lbl">Network</div><div class="val" id="bd-network"></div></div>
                    <div class="kx-info-item"><div class="lbl">USD Amount</div><div class="val" id="bd-usd"></div></div>
                    <div class="kx-info-item"><div class="lbl">Naira Amount</div><div class="val" id="bd-naira"></div></div>
                    <div class="kx-info-item" style="background:rgba(247,147,26,.07);border-color:rgba(247,147,26,.2)"><div class="lbl" style="color:#f7931a">Crypto Qty</div><div class="val" id="bd-crypto" style="color:#f7931a;font-size:1rem;font-weight:800">—</div></div>
                    <div class="kx-info-item" style="grid-column:1/-1"><div class="lbl">Destination Wallet</div><div class="val" id="bd-wallet" style="font-family:monospace;font-size:.82rem"></div></div>
                    <div class="kx-info-item"><div class="lbl">Payment Method</div><div class="val" id="bd-method"></div></div>
                    <div class="kx-info-item"><div class="lbl">Status</div><div class="val" id="bd-status"></div></div>
                </div>
                <div id="bd-proof-area" class="mt-3" style="display:none">
                    <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Payment Proof</div>
                    <a id="bd-proof-link" href="#" target="_blank">
                        <img id="bd-proof-img" src="" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid var(--kx-border)">
                    </a>
                </div>
                <div class="mt-3" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                    <div class="kx-info-item"><div class="lbl">Blockchain TXID</div><div class="val" id="bd-txid" style="font-family:monospace;font-size:.76rem"></div></div>
                    <div class="kx-info-item"><div class="lbl">Admin Payment Proof</div><div class="val" id="bd-admin-proof-wrap"><span style="color:var(--kx-muted)">—</span></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Sell Trade Full Detail View                            --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="sellDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-up-circle me-2" style="color:var(--kx-yellow)"></i>Sell Trade Details #<span id="sd-id"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="kx-info-grid">
                    <div class="kx-info-item"><div class="lbl">User</div><div class="val" id="sd-user"></div></div>
                    <div class="kx-info-item"><div class="lbl">Email</div><div class="val" id="sd-email"></div></div>
                    <div class="kx-info-item"><div class="lbl">Coin Sold</div><div class="val" id="sd-coin"></div></div>
                    <div class="kx-info-item"><div class="lbl">Payment Method</div><div class="val" id="sd-method"></div></div>
                    <div class="kx-info-item"><div class="lbl">USD Value</div><div class="val" id="sd-usd"></div></div>
                    <div class="kx-info-item"><div class="lbl">Naira Payout</div><div class="val" id="sd-naira" style="color:var(--kx-yellow)"></div></div>
                    <div class="kx-info-item" style="background:rgba(245,158,11,.07);border-color:rgba(245,158,11,.2)"><div class="lbl" style="color:var(--kx-yellow)">Crypto Qty</div><div class="val" id="sd-crypto" style="color:var(--kx-yellow);font-size:1rem;font-weight:800">—</div></div>
                    <div class="kx-info-item" style="grid-column:1/-1"><div class="lbl">Wallet Address (Sent From)</div><div class="val" id="sd-wallet" style="font-family:monospace;font-size:.82rem"></div></div>
                </div>
                {{-- Bank account box — "PAY TO THIS ACCOUNT" --}}
                <div style="margin-top:1rem;background:rgba(0,204,0,.06);border:1px solid rgba(0,204,0,.2);border-radius:10px;padding:1rem">
                    <div style="font-size:.72rem;color:var(--kx-green);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:.75rem"><i class="bi bi-bank me-1"></i>Pay User To This Account</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Bank</div><div style="font-weight:700;color:#fff" id="sd-bank"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Account No.</div><div style="font-weight:700;color:var(--kx-blue);font-family:monospace" id="sd-accnum"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Account Name</div><div style="font-weight:700;color:#fff" id="sd-accname"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Status</div><div id="sd-statusbadge"></div></div>
                    </div>
                </div>
                <div id="sd-proof-area" class="mt-3" style="display:none">
                    <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Proof of Transaction</div>
                    <a id="sd-proof-link" href="#" target="_blank">
                        <img id="sd-proof-img" src="" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid var(--kx-border)">
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Active buy/sell form tracker ──────────────
let _activeBuyId = null, _activeSellId = null;

// ── Live crypto USD prices (for qty display) ───
let _kxAdminCryptoPrices = { BTC: 65000, ETH: 3500, USDT: 1.0 };
fetch('/api/crypto-prices').then(r => r.json()).then(d => {
    _kxAdminCryptoPrices = d;
    // Fill crypto sub-texts in tables
    document.querySelectorAll('.kx-crypto-sub[data-coin][data-usd]').forEach(el => {
        const coin = el.dataset.coin;
        const usd  = parseFloat(el.dataset.usd) || 0;
        if(coin && usd > 0) el.textContent = '\u2248 ' + getCryptoQty(coin, usd);
    });
}).catch(() => {});

function getCryptoQty(coin, usd) {
    const price    = _kxAdminCryptoPrices[coin] || 1;
    const qty      = parseFloat(usd) / price;
    const decimals = coin === 'USDT' ? 2 : (coin === 'BTC' ? 8 : 6);
    return qty.toFixed(decimals) + ' ' + coin;
}

// ── Tab switching ──────────────────────────────
function switchTab(name, btn){
    ['buy','sell','dep','wd'].forEach(t=>{
        const el = document.getElementById('tab-'+t);
        if(el) el.style.display = 'none';
    });
    document.getElementById('tab-'+name).style.display = 'block';
    document.querySelectorAll('.nav-tabs-kx .nav-link').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
}

// ── Row filtering ──────────────────────────────
function filterRows(tab, filter, btn){
    const tableId = tab+'-table';
    const table = document.getElementById(tableId);
    if(!table) return;
    table.querySelectorAll('tbody tr[data-status]').forEach(row=>{
        const st = row.dataset.status;
        if(filter === 'all'){
            row.style.display = '';
        } else if(filter === 'failed'){
            row.style.display = (st === 'failed') ? '' : 'none';
        } else {
            row.style.display = (st === filter) ? '' : 'none';
        }
    });
    btn.closest('.kx-filter-bar').querySelectorAll('.kx-filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
}

// ── Pending SLA badges ─────────────────────────────
const SLA_MINUTES = {{ (int) config('trade_alerts.pending_sla_minutes', 20) }};
const ESCALATE_MINUTES = {{ (int) config('trade_alerts.escalate_after_minutes', 30) }};
function refreshSlaBadges(){
    document.querySelectorAll('.js-sla-badge').forEach(el => {
        const created = el.dataset.created;
        if(!created){
            el.textContent = '';
            return;
        }
        const createdAt = new Date(created);
        const now = new Date();
        const mins = Math.max(0, Math.floor((now - createdAt) / 60000));

        el.classList.remove('kx-sla-ok','kx-sla-warn','kx-sla-danger');
        if(mins >= ESCALATE_MINUTES){
            el.classList.add('kx-sla-danger');
            el.innerHTML = '<i class="bi bi-exclamation-octagon-fill"></i> Escalate ' + mins + 'm';
        } else if(mins >= SLA_MINUTES){
            el.classList.add('kx-sla-warn');
            el.innerHTML = '<i class="bi bi-hourglass-split"></i> SLA ' + mins + 'm';
        } else {
            el.classList.add('kx-sla-ok');
            el.innerHTML = '<i class="bi bi-check2-circle"></i> ' + mins + 'm';
        }
    });
}
refreshSlaBadges();
setInterval(refreshSlaBadges, 60000);

// ── BUY TRADE: open detail modal ──────────────
function openBuyDetail(d){
    document.getElementById('bd-id').textContent     = d.id;
    document.getElementById('bd-user').textContent   = d.user;
    document.getElementById('bd-email').textContent  = d.email;
    document.getElementById('bd-coin').textContent   = d.coin;
    document.getElementById('bd-network').textContent= d.network;
    document.getElementById('bd-usd').textContent    = '$'+d.usd;
    document.getElementById('bd-naira').textContent  = '₦'+d.naira;    const bdCrypto = document.getElementById('bd-crypto');
    if(bdCrypto) bdCrypto.textContent = '\u2248 ' + getCryptoQty(d.coin, d.usd);    document.getElementById('bd-wallet').textContent  = d.wallet;
    document.getElementById('bd-method').textContent  = d.method || 'Bank Transfer';
    document.getElementById('bd-status').textContent  = d.status;
    document.getElementById('bd-txid').textContent = d.txid || '—';
    const pa = document.getElementById('bd-proof-area');
    if(d.proof){ pa.style.display='block'; document.getElementById('bd-proof-img').src=d.proof; document.getElementById('bd-proof-link').href=d.proof; }
    else { pa.style.display='none'; }

    const adminProofWrap = document.getElementById('bd-admin-proof-wrap');
    if (d.adminProof) {
        adminProofWrap.innerHTML = `<a href="${d.adminProof}" target="_blank" style="color:var(--kx-blue)">View admin proof image</a>`;
    } else {
        adminProofWrap.innerHTML = '<span style="color:var(--kx-muted)">—</span>';
    }

    new bootstrap.Modal(document.getElementById('buyDetailModal')).show();
}

// ── BUY TRADE: handle save click ──────────────
function handleBuyApprove(id, user, bank, accNum, accName, wallet, coin, naira, usd, txid, existingProofUrl){
    const selEl = document.getElementById('buy-sel-'+id);
    const newStatus = selEl.value;
    _activeBuyId = id;
    const rowForm = document.getElementById('buy-form-'+id);
    const modalForm = document.getElementById('buy-modal-form');
    if (rowForm && modalForm) {
        modalForm.action = rowForm.action;
    }
    // Populate modal
    document.getElementById('bc-id').textContent       = id;
    document.getElementById('bc-newstatus').textContent = newStatus.toUpperCase();
    document.getElementById('bc-status-hidden').value = newStatus;
    document.getElementById('bc-user').textContent     = user;
    document.getElementById('bc-coin').textContent     = coin;
    document.getElementById('bc-usd').textContent      = '$'+usd;
    document.getElementById('bc-naira').textContent    = '₦'+naira;
    document.getElementById('bc-wallet').textContent   = wallet;
    document.getElementById('bc-bank').textContent     = bank;
    document.getElementById('bc-accnum').textContent   = accNum;
    document.getElementById('bc-accname').textContent  = accName;
    document.getElementById('bc-coin-chk').textContent = coin;
    document.getElementById('bc-crypto').textContent    = '\u2248 ' + getCryptoQty(coin, usd);
    document.getElementById('bc-txid').value = txid || '';
    document.getElementById('bc-proof').value = '';
    document.getElementById('bc-existing-proof').value = existingProofUrl || '';
    const existingWrap = document.getElementById('bc-existing-proof-wrap');
    const existingLink = document.getElementById('bc-existing-proof-link');
    if (existingProofUrl) {
        existingWrap.style.display = 'block';
        existingLink.href = existingProofUrl;
    } else {
        existingWrap.style.display = 'none';
        existingLink.href = '#';
    }
    // Show/hide checklist
    const isCompleting = ['completed','approved','successful'].includes(newStatus);
    const cl = document.getElementById('bc-checklist-area');
    cl.style.display = isCompleting ? 'block' : 'none';
    if(isCompleting){
        document.getElementById('bc-chk1').checked = false;
        document.getElementById('bc-chk2').checked = false;
        document.getElementById('bc-confirm-btn').disabled = true;
    } else {
        document.getElementById('bc-confirm-btn').disabled = false;
    }
    new bootstrap.Modal(document.getElementById('buyConfirmModal')).show();
}
function checkBuyReady(){
    const ok = document.getElementById('bc-chk1').checked && document.getElementById('bc-chk2').checked;
    document.getElementById('bc-confirm-btn').disabled = !ok;
}
function submitBuyForm(){
    if(_activeBuyId !== null){
        const status = document.getElementById('bc-status-hidden').value;
        const txid = (document.getElementById('bc-txid').value || '').trim();
        const proofInput = document.getElementById('bc-proof');
        const hasNewProof = proofInput && proofInput.files && proofInput.files.length > 0;
        const existingProof = document.getElementById('bc-existing-proof').value;
        const isCompleting = ['completed','approved','successful'].includes(status);

        if (isCompleting && !txid && !hasNewProof && !existingProof) {
            alert('Upload payment proof image or provide blockchain TXID before completing this trade.');
            return;
        }

        document.getElementById('buy-modal-form').submit();
    }
}

// ── SELL TRADE: open detail modal ─────────────
function openSellDetail(d){
    document.getElementById('sd-id').textContent      = d.id;
    document.getElementById('sd-user').textContent    = d.user;
    document.getElementById('sd-email').textContent   = d.email;
    document.getElementById('sd-coin').textContent    = d.coin;
    document.getElementById('sd-method').textContent  = d.method;
    document.getElementById('sd-usd').textContent     = '$'+d.usd;
    document.getElementById('sd-naira').textContent   = '₦'+d.naira;    const sdCrypto = document.getElementById('sd-crypto');
    if(sdCrypto) sdCrypto.textContent = '\u2248 ' + getCryptoQty(d.coin, d.usd);    document.getElementById('sd-wallet').textContent  = d.wallet;
    document.getElementById('sd-bank').textContent    = d.bank;
    document.getElementById('sd-accnum').textContent  = d.accNum;
    document.getElementById('sd-accname').textContent = d.accName;
    document.getElementById('sd-statusbadge').textContent = d.status;
    const pa = document.getElementById('sd-proof-area');
    if(d.proof){ pa.style.display='block'; document.getElementById('sd-proof-img').src=d.proof; document.getElementById('sd-proof-link').href=d.proof; }
    else { pa.style.display='none'; }
    new bootstrap.Modal(document.getElementById('sellDetailModal')).show();
}

// ── SELL TRADE: handle save click ─────────────
function handleSellApprove(id, user, bank, accNum, accName, wallet, coin, naira, usd){
    const selEl = document.getElementById('sell-sel-'+id);
    const newStatus = selEl.value;
    _activeSellId = id;
    document.getElementById('sc-id').textContent        = id;
    document.getElementById('sc-newstatus').textContent = newStatus.toUpperCase();
    document.getElementById('sc-user').textContent      = user;
    document.getElementById('sc-coin').textContent      = coin;
    document.getElementById('sc-naira').textContent     = '₦'+naira;
    document.getElementById('sc-usd').textContent       = usd ? '$'+usd : '—';
    document.getElementById('sc-crypto').textContent    = usd ? '≈ '+getCryptoQty(coin, usd) : '—';
    document.getElementById('sc-wallet').textContent    = wallet;
    document.getElementById('sc-bank').textContent      = bank;
    document.getElementById('sc-accnum').textContent    = accNum;
    document.getElementById('sc-accname').textContent   = accName;
    document.getElementById('sc-method').textContent    = 'Bank Transfer';
    document.getElementById('sc-coin-chk').textContent  = coin;
    document.getElementById('sc-naira-chk').textContent = '₦'+naira;
    document.getElementById('sc-accname-chk').textContent = accName;
    document.getElementById('sc-accnum-chk').textContent  = accNum;
    const isCompleting = ['completed','approved','successful'].includes(newStatus);
    const cl = document.getElementById('sc-checklist-area');
    cl.style.display = isCompleting ? 'block' : 'none';
    if(isCompleting){
        document.getElementById('sc-chk1').checked = false;
        document.getElementById('sc-chk2').checked = false;
        document.getElementById('sc-confirm-btn').disabled = true;
    } else {
        document.getElementById('sc-confirm-btn').disabled = false;
    }
    new bootstrap.Modal(document.getElementById('sellConfirmModal')).show();
}
function checkSellReady(){
    const ok = document.getElementById('sc-chk1').checked && document.getElementById('sc-chk2').checked;
    document.getElementById('sc-confirm-btn').disabled = !ok;
}
function submitSellForm(){
    if(_activeSellId !== null){
        bootstrap.Modal.getInstance(document.getElementById('sellConfirmModal'))?.hide();
        document.getElementById('sell-form-'+_activeSellId).submit();
    }
}

// ── AI: Trade Summary ─────────────────────────
async function aiTradeSummary(){
    const btn = document.getElementById('btn-ai-summary');
    const box = document.getElementById('ai-summary-box');
    const content = document.getElementById('ai-summary-content');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Analysing…';
    content.innerHTML = '';
    box.style.display = 'none';
    try {
        const res = await fetch('{{ route("ai.trade-summary") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({})
        });
        const data = await res.json();
        box.style.display = 'block';
        content.innerHTML = data.summary || ('<span style="color:var(--kx-red)">' + (data.error||'Unknown error') + '</span>');
    } catch(e) {
        box.style.display = 'block';
        content.innerHTML = '<span style="color:var(--kx-red)">Request failed: '+e.message+'</span>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-robot"></i> AI Summary';
    }
}

// ── AI: Spot Suspicious ───────────────────────
async function aiSpotSuspicious(userId, userName){
    const modal = new bootstrap.Modal(document.getElementById('aiSpotModal'));
    document.getElementById('ai-spot-content').innerHTML = '';
    document.getElementById('ai-spot-loading').style.display = 'block';
    modal.show();
    try {
        const res = await fetch('{{ route("ai.spot-suspicious") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({user_id: userId})
        });
        const data = await res.json();
        document.getElementById('ai-spot-loading').style.display = 'none';
        document.getElementById('ai-spot-content').innerHTML = data.analysis ||
            '<span style="color:var(--kx-red)">'+(data.error||'Unknown error')+'</span>';
    } catch(e) {
        document.getElementById('ai-spot-loading').style.display = 'none';
        document.getElementById('ai-spot-content').innerHTML = '<span style="color:var(--kx-red)">Request failed: '+e.message+'</span>';
    }
}
</script>
@endpush
@endsection
