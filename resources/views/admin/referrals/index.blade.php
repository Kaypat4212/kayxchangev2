@extends('adminnavlayout')

@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.08);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
}
.ref-wrap { max-width: 1400px; margin: 0 auto; }
.ref-head {
    background: linear-gradient(135deg, rgba(0,204,0,0.15), rgba(0,90,0,0.08));
    border: 1px solid rgba(0,204,0,0.24);
    border-radius: 14px;
    padding: 1.2rem 1.4rem;
    margin-bottom: 1.2rem;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .6rem;
}
.ref-head h4 { margin: 0 0 .25rem; color: #fff; font-weight: 700; }
.ref-head p  { margin: 0; color: var(--kx-muted); font-size: .83rem; }
.ref-stat {
    background: var(--kx-card); border: 1px solid var(--kx-border); border-radius: 12px;
    padding: 1rem 1.1rem; display: flex; align-items: center; gap: 12px;
}
.ref-stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.ref-stat-icon.green  { background: rgba(0,204,0,.12);   color: #00cc00; }
.ref-stat-icon.amber  { background: rgba(251,191,36,.12); color: #fbbf24; }
.ref-stat-icon.blue   { background: rgba(56,189,248,.12); color: #38bdf8; }
.ref-stat-icon.purple { background: rgba(168,85,247,.12); color: #a855f7; }
.ref-stat-icon.red    { background: rgba(239,68,68,.12);  color: #ef4444; }
.ref-stat-icon.orange { background: rgba(249,115,22,.12); color: #f97316; }
.ref-stat-val   { font-size: 1.35rem; font-weight: 700; color: #fff; line-height:1; }
.ref-stat-label { font-size: .72rem; color: var(--kx-muted); margin-top: 2px; text-transform: uppercase; letter-spacing:.04em; }
.ref-panel { background: var(--kx-card); border: 1px solid var(--kx-border); border-radius: 14px; margin-bottom: 1rem; }
.ref-panel .hd {
    border-bottom: 1px solid var(--kx-border); padding: .9rem 1.1rem;
    color: #fff; font-weight: 600; font-size: .88rem;
    display: flex; align-items: center; gap: .5rem; flex-wrap: wrap;
}
.ref-panel .bd { padding: 0; }
.ref-table { width: 100%; border-collapse: collapse; }
.ref-table th { border-bottom: 1px solid var(--kx-border); padding: .65rem .75rem; color: var(--kx-muted); font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; }
.ref-table td { border-bottom: 1px solid rgba(255,255,255,0.04); padding: .65rem .75rem; font-size: .78rem; color: var(--kx-text); vertical-align: middle; }
.ref-table tr:last-child td { border-bottom: none; }
.ref-table tr:hover td { background: rgba(255,255,255,.025); }
.ref-table tr.fraud-row td { background: rgba(239,68,68,.04); }
.ref-pill { border-radius: 999px; font-size: .67rem; padding: .15rem .5rem; display: inline-block; font-weight: 600; }
.ref-pill.completed { background: rgba(0,204,0,.13);    color: #00cc00; }
.ref-pill.pending   { background: rgba(251,191,36,.13); color: #fbbf24; }
.ref-pill.blocked   { background: rgba(239,68,68,.13);  color: #ef4444; }
.ref-pill.flagged   { background: rgba(249,115,22,.13); color: #f97316; }
.ref-pill.kyc-yes   { background: rgba(56,189,248,.13); color: #38bdf8; }
.ref-pill.kyc-no    { background: rgba(239,68,68,.13);  color: #ef4444; }
.ref-name   { font-weight: 600; color: #fff; }
.ref-email  { color: var(--kx-muted); font-size: .7rem; }
.ref-ip     { font-family: monospace; font-size: .7rem; color: var(--kx-muted); }
.ref-ip.match { color: #ef4444; font-weight: 700; }
.ref-filter a {
    display: inline-block; padding: .3rem .8rem; border-radius: 999px;
    font-size: .75rem; font-weight: 600; text-decoration: none;
    border: 1px solid var(--kx-border); color: var(--kx-muted); transition: .2s;
}
.ref-filter a.active,
.ref-filter a:hover { background: rgba(0,204,0,.12); border-color: rgba(0,204,0,.35); color: #00cc00; }
.ref-filter a.danger.active,
.ref-filter a.danger:hover { background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.35); color: #ef4444; }
.ref-empty { padding: 3rem 1rem; text-align: center; color: var(--kx-muted); font-size: .85rem; }
.ref-scroll { overflow-x: auto; }
.ref-amt { font-weight: 600; color: #00cc00; }
.ref-deposit-bar { height: 4px; border-radius: 2px; background: var(--kx-border); min-width: 60px; position: relative; overflow: hidden; }
.ref-deposit-fill { position: absolute; top: 0; left: 0; height: 100%; border-radius: 2px; background: #fbbf24; }
.ref-risk-bar { height: 5px; border-radius: 3px; background: var(--kx-border); min-width: 55px; position: relative; overflow: hidden; }
.ref-risk-fill { position: absolute; top: 0; left: 0; height: 100%; border-radius: 3px; transition: width .4s; }
.ref-risk-low  .ref-risk-fill { background: #00cc00; }
.ref-risk-mid  .ref-risk-fill { background: #f97316; }
.ref-risk-high .ref-risk-fill { background: #ef4444; }
.ref-risk-val  { font-size: .72rem; font-weight: 700; }
.ref-risk-val.low  { color: #00cc00; }
.ref-risk-val.mid  { color: #f97316; }
.ref-risk-val.high { color: #ef4444; }
.ref-signals { list-style: none; padding: 0; margin: 4px 0 0; font-size: .68rem; color: var(--kx-muted); }
.ref-signals li::before { content: '⚠ '; color: #f97316; }
.ref-action-btn { border: none; border-radius: 7px; font-size: .72rem; font-weight: 600; padding: .3rem .6rem; cursor: pointer; transition: .15s; }
.ref-action-btn.block   { background: rgba(239,68,68,.13); color: #ef4444; }
.ref-action-btn.block:hover   { background: rgba(239,68,68,.25); }
.ref-action-btn.unblock { background: rgba(0,204,0,.13); color: #00cc00; }
.ref-action-btn.unblock:hover { background: rgba(0,204,0,.25); }
/* Modal */
.fraud-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.65); z-index: 1050; display: none; align-items: center; justify-content: center; }
.fraud-modal-backdrop.show { display: flex; }
.fraud-modal { background: var(--kx-card2); border: 1px solid var(--kx-border); border-radius: 14px; padding: 1.5rem; width: 100%; max-width: 440px; }
.fraud-modal h5 { color: #ef4444; margin-bottom: .75rem; font-weight: 700; }
.fraud-modal label { color: var(--kx-text); font-size: .82rem; }
.fraud-modal input { background: var(--kx-card) !important; border: 1px solid var(--kx-border) !important; color: #fff !important; border-radius: 8px !important; }
.fraud-modal input:focus { border-color: rgba(239,68,68,.45) !important; box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important; }
</style>
@endpush

@section('content')
<div class="ref-wrap">

    {{-- Header --}}
    <div class="ref-head">
        <div>
            <h4><i class="bi bi-people-fill me-2" style="color:#00cc00"></i>Referral Overview</h4>
            <p>Track referrals, detect fraud, and manage rewards.</p>
        </div>
        <a href="{{ route('admin.referrals.settings') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-gear me-1"></i>Referral Settings
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="font-size:.82rem">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="font-size:.82rem">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-2">
            <div class="ref-stat">
                <div class="ref-stat-icon green"><i class="bi bi-people-fill"></i></div>
                <div><div class="ref-stat-val">{{ number_format($stats['total']) }}</div><div class="ref-stat-label">Total</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="ref-stat">
                <div class="ref-stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="ref-stat-val">{{ number_format($stats['pending']) }}</div><div class="ref-stat-label">Pending</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="ref-stat">
                <div class="ref-stat-icon blue"><i class="bi bi-check-circle-fill"></i></div>
                <div><div class="ref-stat-val">{{ number_format($stats['completed']) }}</div><div class="ref-stat-label">Completed</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="ref-stat">
                <div class="ref-stat-icon purple"><i class="bi bi-cash-stack"></i></div>
                <div><div class="ref-stat-val">&#8358;{{ number_format($stats['paid'], 0) }}</div><div class="ref-stat-label">Paid Out</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <a href="{{ route('admin.referrals.index', ['filter' => 'flagged']) }}" style="text-decoration:none">
                <div class="ref-stat" style="{{ $stats['flagged'] > 0 ? 'border-color:rgba(249,115,22,.4)' : '' }}">
                    <div class="ref-stat-icon orange"><i class="bi bi-flag-fill"></i></div>
                    <div><div class="ref-stat-val" style="{{ $stats['flagged'] > 0 ? 'color:#f97316' : '' }}">{{ number_format($stats['flagged']) }}</div><div class="ref-stat-label">Flagged</div></div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-2">
            <a href="{{ route('admin.referrals.index', ['filter' => 'flagged']) }}" style="text-decoration:none">
                <div class="ref-stat" style="{{ $stats['blocked'] > 0 ? 'border-color:rgba(239,68,68,.4)' : '' }}">
                    <div class="ref-stat-icon red"><i class="bi bi-slash-circle-fill"></i></div>
                    <div><div class="ref-stat-val" style="{{ $stats['blocked'] > 0 ? 'color:#ef4444' : '' }}">{{ number_format($stats['blocked']) }}</div><div class="ref-stat-label">Blocked</div></div>
                </div>
            </a>
        </div>
    </div>

    {{-- Table panel --}}
    <div class="ref-panel">
        <div class="hd">
            <span>Referral Records</span>
            <div class="ref-filter ms-auto d-flex gap-1 flex-wrap">
                <a href="{{ route('admin.referrals.index', ['status' => 'all']) }}"
                   class="{{ $status === 'all' && $filter === 'all' ? 'active' : '' }}">All ({{ $stats['total'] }})</a>
                <a href="{{ route('admin.referrals.index', ['status' => 'pending']) }}"
                   class="{{ $status === 'pending' ? 'active' : '' }}">Pending ({{ $stats['pending'] }})</a>
                <a href="{{ route('admin.referrals.index', ['status' => 'completed']) }}"
                   class="{{ $status === 'completed' ? 'active' : '' }}">Completed ({{ $stats['completed'] }})</a>
                <a href="{{ route('admin.referrals.index', ['filter' => 'flagged']) }}"
                   class="danger {{ $filter === 'flagged' ? 'active' : '' }}">
                    <i class="bi bi-flag-fill me-1"></i>Flagged ({{ $stats['flagged'] }})
                </a>
            </div>
        </div>
        <div class="bd ref-scroll">
            @if($referrals->isEmpty())
                <div class="ref-empty">
                    <i class="bi bi-people" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
                    No referral records found.
                </div>
            @else
            <table class="ref-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Referrer</th>
                        <th>Referred User</th>
                        <th>KYC</th>
                        <th>Reg. IP</th>
                        <th>Deposits</th>
                        <th>Reward</th>
                        <th>Status</th>
                        <th>Risk Score</th>
                        <th>Fraud Signals</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($referrals as $ref)
                    @php
                        $referred  = $ref->referred;
                        $referrer  = $ref->referrer;
                        $totalDeposit = $referred
                            ? \App\Models\Deposit::where('user_id', $referred->id)->where('status', 'approved')->sum('amount')
                            : 0;
                        $depositPct = min(100, $totalDeposit / 100);
                        $sameIp = $referred && $referrer
                            && $referred->registration_ip
                            && $referrer->registration_ip
                            && $referred->registration_ip === $referrer->registration_ip;
                        $isBlocked  = $ref->blocked_at !== null;
                        $riskScore  = $ref->risk_score ?? 0;
                        $riskClass  = $riskScore >= 70 ? 'high' : ($riskScore >= 30 ? 'mid' : 'low');
                        $riskSignals = $ref->risk_signals ?? [];
                    @endphp
                    <tr class="{{ $ref->fraud_flagged ? 'fraud-row' : '' }}">
                        <td class="ref-muted">{{ $referrals->firstItem() + $loop->index }}</td>
                        <td>
                            @if($referrer)
                                <div class="ref-name">{{ $referrer->name }}</div>
                                <div class="ref-email">{{ $referrer->email }}</div>
                            @else
                                <span class="ref-muted">Deleted</span>
                            @endif
                        </td>
                        <td>
                            @if($referred)
                                <div class="ref-name">{{ $referred->name }}</div>
                                <div class="ref-email">{{ $referred->email }}</div>
                            @else
                                <span class="ref-muted">Deleted</span>
                            @endif
                        </td>
                        <td>
                            @if($referred)
                                @if($referred->kyc_verified)
                                    <span class="ref-pill kyc-yes"><i class="bi bi-check me-1"></i>Verified</span>
                                @else
                                    <span class="ref-pill kyc-no"><i class="bi bi-x me-1"></i>Not yet</span>
                                @endif
                            @else
                                <span class="ref-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($referred && $referrer)
                                <div class="ref-ip {{ $sameIp ? 'match' : '' }}" title="Referrer: {{ $referrer->registration_ip ?? 'unknown' }}">
                                    {{ $referred->registration_ip ?? '—' }}
                                </div>
                                @if($sameIp)
                                    <div style="font-size:.65rem;color:#ef4444;margin-top:2px">⚠ Same as referrer</div>
                                @endif
                            @else
                                <span class="ref-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:.75rem;color:#fff;margin-bottom:4px">
                                ₦{{ number_format($totalDeposit, 0) }}<span class="ref-muted"> / ₦10k</span>
                            </div>
                            <div class="ref-deposit-bar">
                                <div class="ref-deposit-fill" style="width:{{ $depositPct }}%"></div>
                            </div>
                        </td>
                        <td class="ref-amt">
                            @if($ref->reward_amount > 0)
                                ₦{{ number_format($ref->reward_amount, 0) }}
                            @else
                                <span class="ref-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($isBlocked)
                                <span class="ref-pill blocked"><i class="bi bi-slash-circle me-1"></i>Blocked</span>
                            @else
                                <span class="ref-pill {{ $ref->status }}">{{ ucfirst($ref->status) }}</span>
                            @endif
                        </td>
                        {{-- Risk score --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ref-risk-bar ref-risk-{{ $riskClass }}" style="flex:1;min-width:50px">
                                    <div class="ref-risk-fill" style="width:{{ $riskScore }}%"></div>
                                </div>
                                <span class="ref-risk-val {{ $riskClass }}">{{ $riskScore }}</span>
                            </div>
                            @if($riskScore >= 70)
                                <div style="font-size:.64rem;color:#ef4444;margin-top:2px">Auto-blocked</div>
                            @elseif($riskScore >= 30)
                                <div style="font-size:.64rem;color:#f97316;margin-top:2px">Needs review</div>
                            @endif
                        </td>
                        {{-- Fraud signals --}}
                        <td>
                            @if(count($riskSignals) > 0)
                                <ul class="ref-signals">
                                @foreach($riskSignals as $sig)
                                    <li title="{{ $sig['note'] ?? '' }}">
                                        {{ $sig['key'] ?? '?' }}
                                        <span style="color:#f97316">(+{{ $sig['score'] ?? 0 }})</span>
                                    </li>
                                @endforeach
                                </ul>
                            @elseif($ref->fraud_reason)
                                <div style="font-size:.7rem;color:#ef4444;max-width:200px;word-break:break-word">{{ Str::limit($ref->fraud_reason, 80) }}</div>
                            @else
                                <span class="ref-muted" style="font-size:.72rem">None</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                @if($isBlocked)
                                    <form method="POST" action="{{ route('admin.referrals.unblock', $ref) }}" onsubmit="return confirm('Unblock this referral and allow the reward to process?')">
                                        @csrf
                                        <button type="submit" class="ref-action-btn unblock">
                                            <i class="bi bi-check-circle me-1"></i>Unblock
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="ref-action-btn block"
                                        onclick="openBlockModal({{ $ref->id }}, '{{ addslashes($referred?->name ?? 'Unknown') }}')">
                                        <i class="bi bi-slash-circle me-1"></i>Block
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if($referrals->hasPages())
            <div style="padding:.9rem 1rem;border-top:1px solid var(--kx-border)">
                {{ $referrals->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>

</div>

{{-- Block reason modal --}}
<div class="fraud-modal-backdrop" id="blockModalBackdrop">
    <div class="fraud-modal">
        <h5><i class="bi bi-slash-circle me-2"></i>Block Referral Reward</h5>
        <p style="color:var(--kx-muted);font-size:.82rem;margin-bottom:1rem">
            Blocking will prevent the reward from being paid for <strong id="blockUserName"></strong>.
        </p>
        <form method="POST" id="blockForm" action="">
            @csrf
            <div class="mb-3">
                <label class="form-label">Reason <span style="color:#ef4444">*</span></label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. Same device / IP as referrer, suspected duplicate account" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger btn-sm">Confirm Block</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeBlockModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openBlockModal(referralId, userName) {
    document.getElementById('blockUserName').textContent = userName;
    document.getElementById('blockForm').action = '/admin/referrals/' + referralId + '/block';
    document.getElementById('blockModalBackdrop').classList.add('show');
}
function closeBlockModal() {
    document.getElementById('blockModalBackdrop').classList.remove('show');
}
document.getElementById('blockModalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeBlockModal();
});
</script>
@endpush


@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.08);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
}
.ref-wrap { max-width: 1400px; margin: 0 auto; }
.ref-head {
    background: linear-gradient(135deg, rgba(0,204,0,0.15), rgba(0,90,0,0.08));
    border: 1px solid rgba(0,204,0,0.24);
    border-radius: 14px;
    padding: 1.2rem 1.4rem;
    margin-bottom: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .6rem;
}
.ref-head h4 { margin: 0 0 .25rem; color: #fff; font-weight: 700; }
.ref-head p  { margin: 0; color: var(--kx-muted); font-size: .83rem; }
.ref-stat {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 12px;
    padding: 1rem 1.1rem;
    display: flex; align-items: center; gap: 12px;
}
.ref-stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.ref-stat-icon.green  { background: rgba(0,204,0,.12);   color: #00cc00; }
.ref-stat-icon.amber  { background: rgba(251,191,36,.12); color: #fbbf24; }
.ref-stat-icon.blue   { background: rgba(56,189,248,.12); color: #38bdf8; }
.ref-stat-icon.purple { background: rgba(168,85,247,.12); color: #a855f7; }
.ref-stat-val   { font-size: 1.35rem; font-weight: 700; color: #fff; line-height:1; }
.ref-stat-label { font-size: .72rem; color: var(--kx-muted); margin-top: 2px; text-transform: uppercase; letter-spacing:.04em; }
.ref-panel {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 14px;
    margin-bottom: 1rem;
}
.ref-panel .hd {
    border-bottom: 1px solid var(--kx-border);
    padding: .9rem 1.1rem;
    color: #fff; font-weight: 600; font-size: .88rem;
    display: flex; align-items: center; gap: .5rem;
    flex-wrap: wrap;
}
.ref-panel .bd { padding: 0; }
.ref-table { width: 100%; border-collapse: collapse; }
.ref-table th {
    border-bottom: 1px solid var(--kx-border);
    padding: .65rem .75rem;
    color: var(--kx-muted); font-size: .68rem;
    text-transform: uppercase; letter-spacing: .05em;
    white-space: nowrap;
}
.ref-table td {
    border-bottom: 1px solid rgba(255,255,255,0.04);
    padding: .7rem .75rem;
    font-size: .78rem; color: var(--kx-text);
    vertical-align: middle;
}
.ref-table tr:last-child td { border-bottom: none; }
.ref-table tr:hover td { background: rgba(255,255,255,.025); }
.ref-pill {
    border-radius: 999px; font-size: .67rem;
    padding: .15rem .5rem; display: inline-block; font-weight: 600;
}
.ref-pill.completed { background: rgba(0,204,0,.13);    color: #00cc00; }
.ref-pill.pending   { background: rgba(251,191,36,.13); color: #fbbf24; }
.ref-pill.kyc-yes   { background: rgba(56,189,248,.13); color: #38bdf8; }
.ref-pill.kyc-no    { background: rgba(239,68,68,.13);  color: #ef4444; }
.ref-name   { font-weight: 600; color: #fff; }
.ref-email  { color: var(--kx-muted); font-size: .7rem; }
.ref-filter a {
    display: inline-block;
    padding: .3rem .8rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid var(--kx-border);
    color: var(--kx-muted);
    transition: .2s;
}
.ref-filter a.active,
.ref-filter a:hover {
    background: rgba(0,204,0,.12);
    border-color: rgba(0,204,0,.35);
    color: #00cc00;
}
.ref-empty { padding: 3rem 1rem; text-align: center; color: var(--kx-muted); font-size: .85rem; }
.ref-scroll { overflow-x: auto; }
.ref-amt { font-weight: 600; color: #00cc00; }
.ref-deposit-bar {
    height: 4px; border-radius: 2px;
    background: var(--kx-border);
    min-width: 60px;
    position: relative;
    overflow: hidden;
}
.ref-deposit-fill {
    position: absolute; top: 0; left: 0; height: 100%;
    border-radius: 2px;
    background: #fbbf24;
    transition: width .4s;
}
</style>
@endpush

@section('content')
<div class="ref-wrap">

    {{-- Header --}}
    <div class="ref-head">
        <div>
            <h4><i class="bi bi-people-fill me-2" style="color:#00cc00"></i>Referral Overview</h4>
            <p>All referral records — track who referred who, reward status, and KYC gating.</p>
        </div>
        <a href="{{ route('admin.referrals.settings') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-gear me-1"></i>Referral Settings
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="font-size:.82rem">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="ref-stat">
                <div class="ref-stat-icon green"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="ref-stat-val">{{ number_format($stats['total']) }}</div>
                    <div class="ref-stat-label">Total Referrals</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ref-stat">
                <div class="ref-stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="ref-stat-val">{{ number_format($stats['pending']) }}</div>
                    <div class="ref-stat-label">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ref-stat">
                <div class="ref-stat-icon blue"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="ref-stat-val">{{ number_format($stats['completed']) }}</div>
                    <div class="ref-stat-label">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ref-stat">
                <div class="ref-stat-icon purple"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="ref-stat-val">&#8358;{{ number_format($stats['paid'], 0) }}</div>
                    <div class="ref-stat-label">Rewards Paid</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table panel --}}
    <div class="ref-panel">
        <div class="hd">
            <span>Referral Records</span>
            <div class="ref-filter ms-auto d-flex gap-1">
                <a href="{{ route('admin.referrals.index', ['status' => 'all']) }}"
                   class="{{ $status === 'all' ? 'active' : '' }}">All ({{ $stats['total'] }})</a>
                <a href="{{ route('admin.referrals.index', ['status' => 'pending']) }}"
                   class="{{ $status === 'pending' ? 'active' : '' }}">Pending ({{ $stats['pending'] }})</a>
                <a href="{{ route('admin.referrals.index', ['status' => 'completed']) }}"
                   class="{{ $status === 'completed' ? 'active' : '' }}">Completed ({{ $stats['completed'] }})</a>
            </div>
        </div>
        <div class="bd ref-scroll">
            @if($referrals->isEmpty())
                <div class="ref-empty">
                    <i class="bi bi-people" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
                    No referral records found.
                </div>
            @else
            <table class="ref-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Referrer</th>
                        <th>Referred User</th>
                        <th>KYC</th>
                        <th>Deposits (₦)</th>
                        <th>Reward (₦)</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($referrals as $ref)
                    @php
                        $referred = $ref->referred;
                        $referrer = $ref->referrer;
                        $totalDeposit = $referred
                            ? \App\Models\Deposit::where('user_id', $referred->id)->where('status', 'approved')->sum('amount')
                            : 0;
                        $depositPct = min(100, $totalDeposit / 100); // 100% at ₦10,000
                    @endphp
                    <tr>
                        <td class="ref-muted">{{ $referrals->firstItem() + $loop->index }}</td>
                        <td>
                            @if($referrer)
                                <div class="ref-name">{{ $referrer->name }}</div>
                                <div class="ref-email">{{ $referrer->email }}</div>
                            @else
                                <span class="ref-muted">Deleted</span>
                            @endif
                        </td>
                        <td>
                            @if($referred)
                                <div class="ref-name">{{ $referred->name }}</div>
                                <div class="ref-email">{{ $referred->email }}</div>
                            @else
                                <span class="ref-muted">Deleted</span>
                            @endif
                        </td>
                        <td>
                            @if($referred)
                                @if($referred->kyc_verified)
                                    <span class="ref-pill kyc-yes"><i class="bi bi-check me-1"></i>Verified</span>
                                @else
                                    <span class="ref-pill kyc-no"><i class="bi bi-x me-1"></i>Not verified</span>
                                @endif
                            @else
                                <span class="ref-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:.75rem;color:#fff;margin-bottom:4px">
                                ₦{{ number_format($totalDeposit, 0) }}
                                <span class="ref-muted">/ ₦10,000</span>
                            </div>
                            <div class="ref-deposit-bar">
                                <div class="ref-deposit-fill" style="width:{{ $depositPct }}%"></div>
                            </div>
                        </td>
                        <td class="ref-amt">
                            @if($ref->reward_amount > 0)
                                ₦{{ number_format($ref->reward_amount, 0) }}
                            @else
                                <span class="ref-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="ref-pill {{ $ref->status }}">{{ ucfirst($ref->status) }}</span>
                        </td>
                        <td class="ref-muted">{{ $ref->created_at->format('d M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($referrals->hasPages())
            <div style="padding:.9rem 1rem;border-top:1px solid var(--kx-border)">
                {{ $referrals->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>

</div>
@endsection
