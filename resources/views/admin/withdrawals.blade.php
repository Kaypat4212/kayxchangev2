@extends('adminnavlayout')
@section('content')
<style>
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
.kx-table tbody tr:hover{background:rgba(255,255,255,.02);}
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
.kx-input{background:var(--kx-card2)!important;border:1px solid var(--kx-border)!important;color:var(--kx-text)!important;border-radius:8px!important;padding:.5rem .85rem!important;font-size:.83rem!important;}
.kx-input:focus{border-color:rgba(0,204,0,.4)!important;box-shadow:0 0 0 2px rgba(0,204,0,.1)!important;color:#fff!important;outline:none!important;}
.kx-input::placeholder{color:var(--kx-muted)!important;}
select.kx-input option{background:var(--kx-card2);color:var(--kx-text);}
.kx-label{font-size:.75rem;color:var(--kx-muted);display:block;margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.04em;}
.kx-alert-success{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:var(--kx-green);border-radius:8px;padding:.75rem 1rem;font-size:.84rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
.kx-alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:var(--kx-red);border-radius:8px;padding:.75rem 1rem;font-size:.84rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
.modal-content{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;color:var(--kx-text);}
.modal-header{border-bottom:1px solid var(--kx-border);}
.modal-footer{border-top:1px solid var(--kx-border);}
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
.kx-search{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;display:flex;align-items:center;padding:.45rem .75rem;gap:.5rem;}
.kx-search-input{background:transparent;border:none;outline:none;color:var(--kx-text);font-size:.83rem;flex:1;}
.kx-search-input::placeholder{color:var(--kx-muted);}
/* Toggle switch */
.ap-toggle{position:relative;display:inline-block;width:46px;height:26px;flex-shrink:0;cursor:pointer;}
.ap-toggle input{opacity:0;width:0;height:0;}
.ap-slider{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,.12);border-radius:26px;transition:.25s;}
.ap-slider:before{content:'';position:absolute;height:20px;width:20px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.25s;}
.ap-toggle input:checked + .ap-slider{background:var(--kx-green);}
.ap-toggle input:checked + .ap-slider:before{transform:translateX(20px);}
</style>
<div class="container-fluid py-3 px-3 px-md-4">
    <div class="kx-page-header">
        <div>
            <h4><i class="bi bi-arrow-up-circle-fill me-2" style="color:var(--kx-yellow)"></i>Withdrawal Requests</h4>
            <small>Review and process pending withdrawal requests</small>
        </div>
        <div class="kx-search"><i class="bi bi-search" style="color:var(--kx-muted)"></i>
            <input class="kx-search-input" id="wSearch" placeholder="Search…">
        </div>
    </div>

    @if(session('success'))<div class="kx-alert-success"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="kx-alert-error"><i class="bi bi-exclamation-circle-fill"></i>{{ session('error') }}</div>@endif

    {{-- ── Auto-Payout Control Panel ──────────────────────────────── --}}
    <div class="kx-panel" style="margin-bottom:1.25rem">
        <div class="kx-panel-header">
            <span class="kx-panel-title"><i class="bi bi-lightning-charge-fill me-2" style="color:var(--kx-yellow)"></i>Auto-Payout Settings</span>
            <span style="font-size:.72rem;color:var(--kx-muted)">Automatically transfer funds to users' bank accounts on approval</span>
        </div>
        <div style="padding:1.1rem 1.25rem">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem">

                {{-- Master Switch --}}
                <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:1rem 1.1rem">
                    <div style="font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Master Switch</div>
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div>
                            <div style="font-weight:600;font-size:.88rem;color:#fff">Auto-Payout</div>
                            <div style="font-size:.72rem;color:var(--kx-muted);margin-top:.15rem">Auto-send funds on approval</div>
                        </div>
                        <label class="ap-toggle" title="Toggle Auto-Payout">
                            <input type="checkbox" id="ap_enabled"
                                {{ ($autoPayoutSettings['auto_payout_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                onchange="apToggle('auto_payout_enabled', this.checked ? '1' : '0')">
                            <span class="ap-slider"></span>
                        </label>
                    </div>
                </div>

                {{-- Active Gateway --}}
                <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:1rem 1.1rem">
                    <div style="font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Active Gateway</div>
                    <select id="ap_gateway" class="kx-input" style="width:100%;font-size:.84rem"
                        onchange="apToggle('auto_payout_gateway', this.value)">
                        <option value="paystack" {{ ($autoPayoutSettings['auto_payout_gateway'] ?? 'paystack') === 'paystack' ? 'selected' : '' }}>Paystack Transfer</option>
                        <option value="opay"     {{ ($autoPayoutSettings['auto_payout_gateway'] ?? 'paystack') === 'opay'     ? 'selected' : '' }}>OPay Disbursement</option>
                    </select>
                    <div style="font-size:.7rem;color:var(--kx-muted);margin-top:.4rem">Gateway used when auto-payout fires</div>
                </div>

                {{-- Paystack Toggle --}}
                <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:1rem 1.1rem">
                    <div style="font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Paystack</div>
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div>
                            <div style="font-weight:600;font-size:.88rem;color:#fff">Paystack Transfers</div>
                            <div style="font-size:.72rem;color:var(--kx-muted);margin-top:.15rem">Enable Paystack auto-payout</div>
                        </div>
                        <label class="ap-toggle">
                            <input type="checkbox" id="ap_paystack"
                                {{ ($autoPayoutSettings['auto_payout_paystack_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                onchange="apToggle('auto_payout_paystack_enabled', this.checked ? '1' : '0')">
                            <span class="ap-slider"></span>
                        </label>
                    </div>
                </div>

                {{-- OPay Toggle --}}
                <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:1rem 1.1rem">
                    <div style="font-size:.7rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">OPay</div>
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div>
                            <div style="font-weight:600;font-size:.88rem;color:#fff">OPay Disbursement</div>
                            <div style="font-size:.72rem;color:var(--kx-muted);margin-top:.15rem">Enable OPay auto-payout</div>
                        </div>
                        <label class="ap-toggle">
                            <input type="checkbox" id="ap_opay"
                                {{ ($autoPayoutSettings['auto_payout_opay_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                onchange="apToggle('auto_payout_opay_enabled', this.checked ? '1' : '0')">
                            <span class="ap-slider"></span>
                        </label>
                    </div>
                </div>

            </div>
            <div id="ap-save-notice" style="display:none;margin-top:.75rem;font-size:.78rem;padding:.5rem .875rem;border-radius:7px;background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.2);color:var(--kx-green)">
                <i class="bi bi-check-circle me-1"></i> Setting saved.
            </div>
            <div style="margin-top:.85rem;padding:.65rem .9rem;background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);border-radius:8px;font-size:.75rem;color:var(--kx-yellow)">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Webhook URLs to register:</strong>
                Paystack: <code style="background:rgba(0,0,0,.3);padding:.1rem .4rem;border-radius:4px;font-size:.7rem">{{ url('withdrawals/webhook/paystack') }}</code>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                OPay: <code style="background:rgba(0,0,0,.3);padding:.1rem .4rem;border-radius:4px;font-size:.7rem">{{ url('withdrawals/webhook/opay') }}</code>
            </div>
        </div>
    </div>

    @php
        $pending   = collect($withdrawals)->where('status','pending')->count();
        $approved  = collect($withdrawals)->where('status','approved')->count();
        $cancelled = collect($withdrawals)->where('status','cancelled')->count();
        $total     = collect($withdrawals)->sum('amount');
    @endphp
    <div class="kx-stat-row">
        <div class="kx-stat"><div class="kx-stat-icon icon-yellow"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="kx-stat-label">Pending</div><div class="kx-stat-value">{{ $pending }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-green"><i class="bi bi-check-circle-fill"></i></div>
            <div><div class="kx-stat-label">Approved</div><div class="kx-stat-value">{{ $approved }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-red"><i class="bi bi-x-circle-fill"></i></div>
            <div><div class="kx-stat-label">Cancelled</div><div class="kx-stat-value">{{ $cancelled }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-blue"><i class="bi bi-cash-stack"></i></div>
            <div><div class="kx-stat-label">Total Volume</div><div class="kx-stat-value" style="font-size:1rem">₦{{ number_format($total,0) }}</div></div></div>
    </div>

    <div class="kx-panel">
        <div class="kx-panel-header">
            <span class="kx-panel-title"><i class="bi bi-list-ul me-2"></i>All Withdrawals</span>
        </div>
        <div class="kx-table-wrap">
            <table class="kx-table" id="wTable">
                <thead><tr>
                    <th>#ID</th><th>User</th><th>Amount</th><th>Bank</th><th>Account</th><th>Status</th><th>Payout</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                @forelse($withdrawals as $w)
                @php $bank = is_string($w->bank_account) ? json_decode($w->bank_account, true) : (array)$w->bank_account; @endphp
                <tr>
                    <td><span style="color:var(--kx-muted)">#{{ $w->id }}</span></td>
                    <td><span style="font-weight:600">{{ $w->user->name ?? 'N/A' }}</span><br>
                        <span style="font-size:.72rem;color:var(--kx-muted)">{{ $w->user->email ?? '' }}</span></td>
                    <td><span style="font-weight:700;color:var(--kx-yellow)">₦{{ number_format($w->amount, 2) }}</span></td>
                    <td style="font-size:.78rem">{{ $bank['bank_name'] ?? '—' }}</td>
                    <td style="font-size:.78rem;font-family:monospace">{{ $bank['account_number'] ?? '—' }}<br>
                        <span style="color:var(--kx-muted)">{{ $bank['account_name'] ?? '' }}</span></td>
                    <td>
                        @if($w->status === 'pending')
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                        @elseif($w->status === 'approved')
                            <span class="kx-badge kx-badge-green"><i class="bi bi-check me-1"></i>Approved</span>
                        @elseif($w->status === 'cancelled')
                            <span class="kx-badge kx-badge-red"><i class="bi bi-x me-1"></i>Cancelled</span>
                        @else
                            <span class="kx-badge kx-badge-gray">{{ $w->status }}</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem;color:var(--kx-muted)">{{ $w->created_at ? $w->created_at->format('d M Y') : '—' }}</td>
                    <td>
                        @if($w->payout_status === 'success')
                            <span class="kx-badge kx-badge-green"><i class="bi bi-send-check me-1"></i>Sent</span>
                        @elseif($w->payout_status === 'failed' || $w->payout_status === 'reversed')
                            <span class="kx-badge kx-badge-red"><i class="bi bi-send-x me-1"></i>{{ ucfirst($w->payout_status) }}</span>
                        @elseif($w->payout_status === 'pending')
                            <span class="kx-badge kx-badge-yellow"><i class="bi bi-send me-1"></i>Processing</span>
                        @elseif($w->payout_gateway)
                            <span class="kx-badge kx-badge-gray">{{ $w->payout_gateway }}</span>
                        @else
                            <span style="font-size:.7rem;color:var(--kx-muted)">Manual</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem;color:var(--kx-muted)">{{ $w->created_at ? $w->created_at->format('d M Y') : '—' }}</td>
                    <td>
                        @if($w->status === 'pending')
                        <div class="d-flex gap-1">
                            <form id="wd-approve-{{ $w->id }}" action="{{ route('withdrawals.updateStatus', $w->id) }}" method="POST" style="display:inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="button" class="btn-kx-approve"
                                    onclick="openWdConfirm('approve',{{ $w->id }},'{{ addslashes($w->user->name ?? 'N/A') }}','{{ number_format($w->amount, 2) }}','{{ addslashes($bank['bank_name'] ?? '—') }}','{{ addslashes($bank['account_number'] ?? '—') }}','{{ addslashes($bank['account_name'] ?? '—') }}')"
                                    title="Approve Withdrawal">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                            </form>
                            <form id="wd-cancel-{{ $w->id }}" action="{{ route('withdrawals.updateStatus', $w->id) }}" method="POST" style="display:inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="button" class="btn-kx-danger"
                                    onclick="openWdConfirm('cancel',{{ $w->id }},'{{ addslashes($w->user->name ?? 'N/A') }}','{{ number_format($w->amount, 2) }}','{{ addslashes($bank['bank_name'] ?? '—') }}','{{ addslashes($bank['account_number'] ?? '—') }}','{{ addslashes($bank['account_name'] ?? '—') }}')"
                                    title="Cancel Withdrawal">
                                    <i class="bi bi-x-lg"></i> Cancel
                                </button>
                            </form>
                        </div>
                        @else
                        <span style="font-size:.72rem;color:var(--kx-muted)">Processed</span>
                        @if($w->status === 'approved' && in_array($w->payout_status, [null, 'failed', 'reversed']))
                        <br><button type="button" class="btn-kx-edit mt-1"
                            onclick="retryPayout({{ $w->id }})" title="Retry auto-payout">
                            <i class="bi bi-arrow-clockwise"></i> Retry Payout
                        </button>
                        @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:var(--kx-muted);padding:2.5rem">No withdrawals found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Withdrawal Confirmation Modal --}}
<div class="modal fade" id="wdConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wdm-title" style="font-size:.95rem;font-weight:700;color:#fff"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Amount highlight box --}}
                <div style="background:var(--kx-card2);border:1px solid rgba(245,158,11,.3);border-radius:12px;padding:1rem 1.25rem;text-align:center;margin-bottom:1rem">
                    <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Amount to Process</div>
                    <div id="wdm-amount" style="font-size:1.6rem;font-weight:800;color:var(--kx-yellow)">&#8212;</div>
                </div>
                {{-- User + bank details --}}
                <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:.875rem 1rem">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">User</div><div style="font-weight:700;color:#fff" id="wdm-user"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Bank</div><div style="font-weight:600;color:#fff" id="wdm-bank"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Account No.</div><div style="font-weight:700;color:var(--kx-blue);font-family:monospace" id="wdm-accnum"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Account Name</div><div style="font-weight:600;color:#fff" id="wdm-accname"></div></div>
                    </div>
                </div>
                <div id="wdm-action-note" style="margin-top:.85rem;font-size:.82rem;font-weight:600;padding:.6rem .875rem;border-radius:8px"></div>
            </div>
            <div class="modal-footer" style="justify-content:flex-end;gap:.5rem">
                <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Go Back</button>
                <button type="button" id="wdm-confirm-btn" class="btn-kx-green">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
let _wdPendingFormId = null;

function openWdConfirm(action, id, user, amount, bank, accNum, accName) {
    const isApprove = action === 'approve';
    _wdPendingFormId = (isApprove ? 'wd-approve-' : 'wd-cancel-') + id;
    document.getElementById('wdm-title').innerHTML =
        isApprove
        ? '<i class="bi bi-check-circle me-2" style="color:#00cc00"></i>Approve Withdrawal'
        : '<i class="bi bi-x-circle me-2" style="color:#ef4444"></i>Cancel Withdrawal';
    document.getElementById('wdm-amount').textContent  = '\u20a6' + amount;
    document.getElementById('wdm-user').textContent    = user;
    document.getElementById('wdm-bank').textContent    = bank;
    document.getElementById('wdm-accnum').textContent  = accNum;
    document.getElementById('wdm-accname').textContent = accName;
    const note = document.getElementById('wdm-action-note');
    if(isApprove) {
        note.style.background = 'rgba(0,204,0,.08)';
        note.style.border = '1px solid rgba(0,204,0,.25)';
        note.style.color = '#00cc00';
        note.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Confirm you have transferred <strong>\u20a6'+amount+'</strong> to the account above.';
    } else {
        note.style.background = 'rgba(239,68,68,.08)';
        note.style.border = '1px solid rgba(239,68,68,.25)';
        note.style.color = '#ef4444';
        note.innerHTML = '<i class="bi bi-x-circle me-1"></i>This withdrawal will be marked as cancelled.';
    }
    const btn = document.getElementById('wdm-confirm-btn');
    btn.style.background = isApprove ? '#00cc00' : '#ef4444';
    btn.style.color = isApprove ? '#000' : '#fff';
    btn.textContent = isApprove ? '\u2713 Approve & Process' : 'Cancel Withdrawal';
    new bootstrap.Modal(document.getElementById('wdConfirmModal')).show();
}

document.getElementById('wdm-confirm-btn').addEventListener('click', function() {
    if(_wdPendingFormId) {
        bootstrap.Modal.getInstance(document.getElementById('wdConfirmModal'))?.hide();
        setTimeout(() => document.getElementById(_wdPendingFormId)?.submit(), 180);
    }
});

document.getElementById('wSearch').addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#wTable tbody tr').forEach(r => { r.style.display = r.textContent.toLowerCase().includes(q)?'':'none'; });
});

// ── Auto-Payout Toggle ────────────────────────────────────────────
function apToggle(key, value) {
    fetch('{{ route('admin.withdrawals.auto-payout.toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ key, value })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const n = document.getElementById('ap-save-notice');
            n.style.display = 'block';
            setTimeout(() => n.style.display = 'none', 2500);
        }
    })
    .catch(e => console.error('AutoPayout toggle error', e));
}

// ── Retry Payout ──────────────────────────────────────────────────
function retryPayout(id) {
    if (!confirm('Retry auto-payout for withdrawal #' + id + '?')) return;
    fetch('/admin/withdrawals/' + id + '/retry-payout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message + (data.payout_status ? ' (' + data.payout_status + ')' : ''));
        location.reload();
    })
    .catch(e => { alert('Retry failed. Check logs.'); console.error(e); });
}
</script>
@endsection
