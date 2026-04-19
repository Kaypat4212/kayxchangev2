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
</style>
<div class="container-fluid py-3 px-3 px-md-4">
    <div class="kx-page-header">
        <div>
            <h4><i class="bi bi-bank me-2" style="color:var(--kx-blue)"></i>Deposit Review</h4>
            <small>Approve or cancel pending deposits</small>
        </div>
        <div class="kx-search"><i class="bi bi-search" style="color:var(--kx-muted)"></i>
            <input class="kx-search-input" id="dSearch" placeholder="Search…">
        </div>
    </div>

    @if(session('success'))<div class="kx-alert-success"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="kx-alert-error"><i class="bi bi-exclamation-circle-fill"></i>{{ session('error') }}</div>@endif

    @php
        $depPending  = collect($deposits)->where('status','pending')->count();
        $depApproved = collect($deposits)->whereIn('status',['approved','successful','completed'])->count();
        $depTotal    = collect($deposits)->sum('amount');
    @endphp
    <div class="kx-stat-row">
        <div class="kx-stat"><div class="kx-stat-icon icon-yellow"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="kx-stat-label">Pending</div><div class="kx-stat-value">{{ $depPending }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-green"><i class="bi bi-check-circle-fill"></i></div>
            <div><div class="kx-stat-label">Approved</div><div class="kx-stat-value">{{ $depApproved }}</div></div></div>
        <div class="kx-stat"><div class="kx-stat-icon icon-blue"><i class="bi bi-cash-coin"></i></div>
            <div><div class="kx-stat-label">Total Volume</div><div class="kx-stat-value" style="font-size:1rem">₦{{ number_format($depTotal,0) }}</div></div></div>
    </div>

    <div class="kx-panel">
        <div class="kx-panel-header"><span class="kx-panel-title"><i class="bi bi-list-ul me-2"></i>All Deposits</span></div>
        <div class="kx-table-wrap">
            <table class="kx-table" id="dTable">
                <thead><tr>
                    <th>#ID</th><th>User</th><th>Amount</th><th>Destination</th>
                    <th>Date</th><th>Proof</th><th>Status</th><th>Note</th><th>Action</th>
                </tr></thead>
                <tbody>
                @forelse($deposits as $dep)
                @php
                    $gatewayMeta = [];
                    if (!empty($dep->gateway_response) && is_string($dep->gateway_response)) {
                        $decoded = json_decode($dep->gateway_response, true);
                        if (is_array($decoded)) {
                            $gatewayMeta = $decoded;
                        }
                    }
                @endphp
                <tr>
                    <td><span style="color:var(--kx-muted)">#{{ $dep->id }}</span></td>
                    <td><span style="font-weight:600">{{ $dep->user->name ?? 'N/A' }}</span><br>
                        <span style="font-size:.72rem;color:var(--kx-muted)">{{ $dep->user->email ?? '' }}</span></td>
                    <td><span style="font-weight:700;color:var(--kx-blue)">₦{{ number_format($dep->amount, 2) }}</span></td>
                    <td style="font-size:.78rem">
                        @if($dep->payment_method === 'crypto_transfer')
                            <strong>{{ $gatewayMeta['crypto_wallet_name'] ?? 'Crypto Wallet' }}</strong><br>
                            <span style="color:var(--kx-muted)">{{ $gatewayMeta['crypto_wallet_network'] ?? 'Network' }}</span><br>
                            <span style="font-family:monospace;color:var(--kx-muted)">{{ $gatewayMeta['crypto_wallet_address'] ?? 'N/A' }}</span>
                        @elseif($dep->companyAccount)
                            {{ $dep->companyAccount->bank_name }}<br>
                            <span style="font-family:monospace;color:var(--kx-muted)">{{ $dep->companyAccount->account_number }}</span>
                        @else
                            <span style="color:var(--kx-muted)">—</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem;color:var(--kx-muted);white-space:nowrap">
                        <div>{{ optional($dep->created_at)->format('d M Y') }}</div>
                        <div style="font-size:.68rem;color:var(--kx-muted)">{{ optional($dep->created_at)->format('h:i A') }}</div>
                    </td>
                    <td>
                        @if($dep->proof_of_payment)
                            @php
                                $proofPath = ltrim((string) $dep->proof_of_payment, '/');
                                $proofUrl = preg_match('/^https?:\/\//i', $proofPath)
                                    ? $proofPath
                                    : route('storage.file', preg_replace('/^storage\//', '', $proofPath));
                            @endphp
                            <a href="{{ $proofUrl }}" target="_blank" class="btn-kx-edit" style="font-size:.72rem;padding:.25rem .55rem">
                                <i class="bi bi-file-image"></i> View
                            </a>
                        @else
                            <span style="color:var(--kx-muted);font-size:.75rem">None</span>
                        @endif
                    </td>
                    <td>
                        @if(in_array($dep->status, ['approved','successful','completed']))
                            <span class="kx-badge kx-badge-green">Approved</span>
                        @elseif($dep->status === 'pending')
                            <span class="kx-badge kx-badge-yellow">Pending</span>
                        @elseif($dep->status === 'cancelled')
                            <span class="kx-badge kx-badge-red">Cancelled</span>
                        @else
                            <span class="kx-badge kx-badge-gray">{{ $dep->status }}</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem;color:var(--kx-muted)">{{ $dep->admin_note ?? '—' }}</td>
                    <td>
                        <form id="dep-form-{{ $dep->id }}" action="{{ route('admin.deposits.update.post', $dep) }}" method="POST">
                            @csrf
                            <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap">
                                <select name="status" id="dep-sel-{{ $dep->id }}" class="kx-input" style="width:110px;padding:.3rem .5rem!important;font-size:.75rem!important">
                                    <option value="approved"  {{ $dep->status === 'approved'  ? 'selected':'' }}>Approve</option>
                                    <option value="cancelled" {{ $dep->status === 'cancelled' ? 'selected':'' }}>Cancel</option>
                                </select>
                                <input type="text" name="admin_note" id="dep-note-{{ $dep->id }}" class="kx-input" style="width:120px" placeholder="Note…" value="{{ $dep->admin_note }}">
                                <button type="button"
                                    class="btn-kx-green"
                                    style="padding:.3rem .65rem;font-size:.72rem"
                                    onclick="openDepConfirm({{ $dep->id }},'{{ addslashes($dep->user->name ?? 'N/A') }}','{{ number_format($dep->amount, 2) }}','{{ addslashes($dep->payment_method ?? 'Bank Transfer') }}')"
                                    title="Review &amp; Save">
                                    <i class="bi bi-eye"></i> Review
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;color:var(--kx-muted);padding:2.5rem">No deposits found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Deposit Confirmation Modal --}}
<div class="modal fade" id="depConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depm-title" style="font-size:.95rem;font-weight:700;color:#fff"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Amount highlight --}}
                <div style="background:var(--kx-card2);border:1px solid rgba(56,189,248,.3);border-radius:12px;padding:1rem 1.25rem;text-align:center;margin-bottom:1rem">
                    <div style="font-size:.72rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Deposit Amount</div>
                    <div id="depm-amount" style="font-size:1.6rem;font-weight:800;color:var(--kx-blue)">&#8212;</div>
                </div>
                {{-- Details --}}
                <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:.875rem 1rem">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">User</div><div style="font-weight:700;color:#fff" id="depm-user"></div></div>
                        <div><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Method</div><div style="font-weight:600;color:#fff" id="depm-method"></div></div>
                        <div style="grid-column:1/-1"><div style="font-size:.68rem;color:var(--kx-muted);text-transform:uppercase">Action</div><div style="font-weight:700" id="depm-action"></div></div>
                    </div>
                </div>
                <p style="font-size:.8rem;color:var(--kx-muted);margin-top:.875rem;margin-bottom:0"><i class="bi bi-info-circle me-1"></i>This will update the deposit status. Ensure you have verified the payment proof before approving.</p>
            </div>
            <div class="modal-footer" style="justify-content:flex-end;gap:.5rem">
                <button type="button" class="btn-kx-outline" data-bs-dismiss="modal">Go Back</button>
                <button type="button" id="depm-confirm-btn" class="btn-kx-green"><i class="bi bi-check-circle me-1"></i>Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
let _depPendingId = null;

function openDepConfirm(id, user, amount, method) {
    _depPendingId = id;
    const sel    = document.getElementById('dep-sel-' + id);
    const action = sel ? sel.value : 'approved';
    const isApprove = action === 'approved' || action === 'approve';

    document.getElementById('depm-title').innerHTML =
        isApprove
        ? '<i class="bi bi-bank me-2" style="color:var(--kx-blue)"></i>Approve Deposit'
        : '<i class="bi bi-x-circle me-2" style="color:#ef4444"></i>Cancel Deposit';
    document.getElementById('depm-amount').textContent  = '\u20a6' + amount;
    document.getElementById('depm-user').textContent    = user;
    document.getElementById('depm-method').textContent  = method;
    const actionEl = document.getElementById('depm-action');
    if(isApprove) {
        actionEl.style.color = '#00cc00';
        actionEl.textContent = 'Approve — Credit user wallet with \u20a6' + amount;
    } else {
        actionEl.style.color = '#ef4444';
        actionEl.textContent = 'Cancel — Deposit will be marked cancelled';
    }
    const btn = document.getElementById('depm-confirm-btn');
    btn.style.background = isApprove ? '#00cc00' : '#ef4444';
    btn.style.color = isApprove ? '#000' : '#fff';
    new bootstrap.Modal(document.getElementById('depConfirmModal')).show();
}

document.getElementById('depm-confirm-btn').addEventListener('click', function() {
    if(_depPendingId !== null) {
        bootstrap.Modal.getInstance(document.getElementById('depConfirmModal'))?.hide();
        setTimeout(() => document.getElementById('dep-form-' + _depPendingId)?.submit(), 180);
    }
});

document.getElementById('dSearch').addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#dTable tbody tr').forEach(r=>{ r.style.display=r.textContent.toLowerCase().includes(q)?'':'none'; });
});
</script>
@endsection
