@extends('selllayout')

@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-card: rgba(14,22,14,0.88);
    --kx-card-border: rgba(0,204,0,0.18);

    /* Text colors that adapt to light mode */
    --text-primary: #fff;
    --text-secondary: rgba(255,255,255,.75);
    --text-muted: rgba(255,255,255,.55);
    --text-placeholder: rgba(255,255,255,.3);
    --text-error: #ef4444;
}

/* Light mode overrides */
body.light-mode {
    --kx-card: rgba(255,255,255,0.95);
    --kx-card-border: rgba(0,204,0,0.25);

    --text-primary: #0a1a0a;
    --text-secondary: rgba(10,26,10,.75);
    --text-muted: rgba(10,26,10,.55);
    --text-placeholder: rgba(10,26,10,.3);
}
.tx-page { max-width: 780px; margin: 0 auto; }

/* ── Summary row ───────────────────────────────────────────── */
.tx-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.tx-sum-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-card-border);
    border-radius: 14px;
    padding: .9rem 1rem;
    text-align: center;
}
.tx-sum-label { font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); margin-bottom: .2rem; }
.tx-sum-val   { font-size: 1.15rem; font-weight: 700; color: var(--text-primary); }
.tx-sum-val.green { color: var(--kx-green); }
.tx-sum-val.red   { color: #ef4444; }

/* ── Tabs ──────────────────────────────────────────────────── */
.tx-tabs { display: flex; gap: .5rem; margin-bottom: 1.1rem; }
.tx-tab  {
    flex: 1; padding: .55rem .5rem; border-radius: 10px; border: 1px solid var(--kx-card-border);
    background: var(--kx-card); color: var(--text-muted); font-size: .8rem; font-weight: 600;
    cursor: pointer; text-align: center; transition: all .2s;
}
.tx-tab.active { background: rgba(0,204,0,.18); border-color: var(--kx-green); color: var(--kx-green); }

/* ── People cards ──────────────────────────────────────────── */
#tab-people { display: none; }
.people-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
@media(max-width:500px){ .people-grid { grid-template-columns: 1fr; } }
.people-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-card-border);
    border-radius: 14px;
    padding: 1rem 1.1rem;
    text-decoration: none;
    transition: border-color .2s;
    display: block;
}
.people-card:hover { border-color: rgba(0,204,0,.4); text-decoration: none; }
.people-name  { font-size: .9rem; font-weight: 700; color: var(--text-primary); }
.people-tag   { font-size: .75rem; color: var(--kx-green); margin-bottom: .6rem; }
.people-stats { display: flex; justify-content: space-between; gap: .5rem; }
.ps-col       { flex: 1; }
.ps-label     { font-size: .63rem; text-transform: uppercase; letter-spacing: .07em; color: var(--text-placeholder); }
.ps-val       { font-size: .85rem; font-weight: 700; }
.ps-val.red   { color: #ef4444; }
.ps-val.green { color: var(--kx-green); }
.ps-val.neutral { color: var(--text-secondary); }
.ps-txns      { font-size: .68rem; color: var(--text-placeholder); margin-top: .2rem; }
.people-net {
    margin-top: .65rem; padding-top: .6rem; border-top: 1px solid rgba(255,255,255,.07);
    font-size: .75rem; color: var(--text-muted);
}
.people-net span { font-weight: 700; }
.people-net span.pos { color: var(--kx-green); }
.people-net span.neg { color: #ef4444; }

/* ── Filter banner ─────────────────────────────────────────── */
.filter-banner {
    background: rgba(0,204,0,.08);
    border: 1px solid rgba(0,204,0,.25);
    border-radius: 12px;
    padding: .75rem 1rem;
    margin-bottom: 1rem;
    display: flex; align-items: center; justify-content: space-between; gap: .75rem;
}
.filter-banner-info { font-size: .82rem; color: var(--text-secondary); }
.filter-banner-name { font-weight: 700; color: var(--text-primary); }
.filter-banner-stats { display: flex; gap: 1.25rem; font-size: .78rem; }
.fbs-item label { display: block; font-size: .63rem; text-transform: uppercase; letter-spacing: .07em; color: var(--text-placeholder); }
.fbs-item .val  { font-weight: 700; }
.filter-clear   { color: var(--text-placeholder); font-size: .8rem; text-decoration: none; flex-shrink: 0; }
.filter-clear:hover { color: #fff; }

/* ── Transfer item ─────────────────────────────────────────── */
.tx-item {
    background: var(--kx-card);
    border: 1px solid var(--kx-card-border);
    border-radius: 14px;
    padding: .9rem 1.1rem;
    margin-bottom: .65rem;
    display: flex;
    align-items: center;
    gap: .9rem;
    transition: border-color .2s;
}
.tx-item:hover { border-color: rgba(0,204,0,.35); }

.tx-icon {
    width: 42px; height: 42px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.tx-icon.sent     { background: rgba(239,68,68,.12); color: #ef4444; }
.tx-icon.received { background: rgba(0,204,0,.12); color: var(--kx-green); }
.tx-icon.reversed { background: rgba(251,191,36,.12); color: #fbbf24; }

.tx-body  { flex: 1; min-width: 0; }
.tx-party { font-size: .88rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tx-tag   { font-size: .73rem; color: var(--kx-green); }
.tx-ref   { font-size: .7rem; color: var(--text-placeholder); margin-top: .1rem; }
.tx-note  { font-size: .76rem; color: var(--text-muted); font-style: italic; margin-top: .1rem; }

.tx-right  { text-align: right; flex-shrink: 0; }
.tx-amount { font-size: 1rem; font-weight: 700; }
.tx-amount.sent     { color: #ef4444; }
.tx-amount.received { color: var(--kx-green); }
.tx-amount.reversed { color: #fbbf24; }
.tx-time   { font-size: .7rem; color: var(--text-placeholder); margin-top: .15rem; }
.tx-badge  {
    display: inline-block;
    font-size: .63rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    padding: .08rem .4rem; border-radius: 20px; margin-top: .2rem;
}
.tx-badge.completed { background: rgba(0,204,0,.12); color: var(--kx-green); }
.tx-badge.reversed  { background: rgba(251,191,36,.12); color: #fbbf24; }
.tx-badge.failed    { background: rgba(239,68,68,.12); color: #ef4444; }
.tx-view-all { font-size: .68rem; color: rgba(0,204,0,.6); text-decoration: none; display: block; margin-top: .1rem; }
.tx-view-all:hover { color: var(--kx-green); }

/* ── Empty ─────────────────────────────────────────────────── */
.tx-empty {
    background: var(--kx-card);
    border: 1px dashed var(--kx-card-border);
    border-radius: 14px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    color: var(--text-muted);
}
.tx-empty i { font-size: 2.2rem; margin-bottom: .6rem; color: rgba(0,204,0,.25); }

/* ── Send button ───────────────────────────────────────────── */
.send-cta {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    background: linear-gradient(90deg, #00cc00, #009900);
    border-radius: 12px;
    padding: .75rem 1.4rem;
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    transition: opacity .2s;
    margin-bottom: 1.1rem;
}
.send-cta:hover { opacity: .88; color: #fff; }
</style>
@endpush

@section('content')
<div class="container py-4 tx-page">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 fw-bold" style="color: var(--text-primary);"><i class="bi bi-clock-history me-2" style="color:var(--kx-green)"></i>Transfer History</h5>
        <span style="font-size:.8rem; color: var(--text-muted);">{{ Auth::user()->kx_tag }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3" style="border-radius:12px;">{{ session('success') }}</div>
    @endif

    {{-- Global summary (all-time, all contacts) --}}
    @php
        $allTransfers = \App\Models\P2pTransfer::where(function($q){
            $q->where('sender_id', Auth::id())->orWhere('recipient_id', Auth::id());
        })->where('status', 'completed')->get();

        $globalSent     = $allTransfers->where('sender_id', Auth::id())->sum('amount');
        $globalReceived = $allTransfers->where('recipient_id', Auth::id())->sum('recipient_amount');
        $totalCount     = $allTransfers->count();
    @endphp
    <div class="tx-summary">
        <div class="tx-sum-card">
            <div class="tx-sum-label">Total Sent</div>
            <div class="tx-sum-val red">₦{{ number_format($globalSent, 2) }}</div>
        </div>
        <div class="tx-sum-card">
            <div class="tx-sum-label">Total Received</div>
            <div class="tx-sum-val green">₦{{ number_format($globalReceived, 2) }}</div>
        </div>
        <div class="tx-sum-card">
            <div class="tx-sum-label">Transfers</div>
            <div class="tx-sum-val">{{ $totalCount }}</div>
        </div>
    </div>

    <a href="{{ route('wallet.send') }}" class="send-cta">
        <i class="bi bi-send-fill"></i> Send Money
    </a>

    {{-- Tabs --}}
    <div class="tx-tabs">
        <div class="tx-tab {{ !$filterUserId ? 'active' : '' }}" id="tab-btn-history" onclick="switchTab('history')">
            <i class="bi bi-list-ul me-1"></i> History
        </div>
        <div class="tx-tab {{ count($contacts) ? '' : 'opacity-50' }}" id="tab-btn-people" onclick="switchTab('people')">
            <i class="bi bi-people me-1"></i> People
            @if(count($contacts))
                <span style="background:rgba(0,204,0,.2);color:var(--kx-green);font-size:.65rem;padding:.05rem .35rem;border-radius:20px;margin-left:.25rem;">{{ count($contacts) }}</span>
            @endif
        </div>
    </div>

    {{-- ── PEOPLE TAB ─────────────────────────────────────────── --}}
    <div id="tab-people">
        @if($contacts->isEmpty())
            <div class="tx-empty"><i class="bi bi-people d-block"></i>No transfers yet.</div>
        @else
            <p style="font-size:.75rem;color:rgba(255,255,255,.35);margin-bottom:.75rem;">Tap a person to filter transfers between you two.</p>
            <div class="people-grid">
                @foreach($contacts as $contact)
                    @php
                        $net     = $contact->total_received - $contact->total_sent;
                        $netSign = $net >= 0 ? '+' : '';
                        $netCls  = $net > 0 ? 'pos' : ($net < 0 ? 'neg' : '');
                    @endphp
                    <a href="{{ route('wallet.transfers', ['with' => $contact->id]) }}" class="people-card">
                        <div class="people-name">{{ $contact->name }}</div>
                        <div class="people-tag">{{ $contact->kx_tag }}</div>
                        <div class="people-stats">
                            <div class="ps-col">
                                <div class="ps-label">You Sent</div>
                                <div class="ps-val red">₦{{ number_format($contact->total_sent, 0) }}</div>
                                @if($contact->send_count)
                                    <div class="ps-txns">{{ $contact->send_count }} txn{{ $contact->send_count > 1 ? 's' : '' }}</div>
                                @endif
                            </div>
                            <div class="ps-col" style="text-align:right;">
                                <div class="ps-label">You Received</div>
                                <div class="ps-val green">₦{{ number_format($contact->total_received, 0) }}</div>
                                @if($contact->receive_count)
                                    <div class="ps-txns">{{ $contact->receive_count }} txn{{ $contact->receive_count > 1 ? 's' : '' }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="people-net">
                            Net: <span class="{{ $netCls }}">{{ $netSign }}₦{{ number_format(abs($net), 0) }}</span>
                            <span style="color:rgba(255,255,255,.25);">
                                ({{ $net >= 0 ? 'you\'re up' : 'you\'re down' }})
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── HISTORY TAB ─────────────────────────────────────────── --}}
    <div id="tab-history">

        {{-- Filter banner when viewing a specific person --}}
        @if($filterContact)
            <div class="filter-banner">
                <div>
                    <div class="filter-banner-info">Showing transfers with <span class="filter-banner-name">{{ $filterContact->name }}</span> <span style="color:var(--kx-green);">{{ $filterContact->kx_tag }}</span></div>
                    <div class="filter-banner-stats mt-1">
                        <div class="fbs-item">
                            <label>You Sent</label>
                            <span class="val" style="color:#ef4444;">₦{{ number_format($filterContact->total_sent, 2) }}</span>
                        </div>
                        <div class="fbs-item">
                            <label>You Received</label>
                            <span class="val" style="color:var(--kx-green);">₦{{ number_format($filterContact->total_received, 2) }}</span>
                        </div>
                        <div class="fbs-item">
                            <label>Txns</label>
                            <span class="val" style="color:rgba(255,255,255,.7);">{{ $filterContact->send_count + $filterContact->receive_count }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('wallet.transfers') }}" class="filter-clear"><i class="bi bi-x-circle me-1"></i>Clear</a>
            </div>
        @endif

        {{-- List --}}
        @forelse($transfers as $t)
            @php
                $isSent  = $t->sender_id === Auth::id();
                $dir     = $t->status === 'reversed' ? 'reversed' : ($isSent ? 'sent' : 'received');
                $icon    = $t->status === 'reversed' ? 'bi-arrow-counterclockwise' : ($isSent ? 'bi-arrow-up-right' : 'bi-arrow-down-left');
                $party   = $isSent ? $t->recipient : $t->sender;
                $label   = $isSent ? 'To' : 'From';
                $amtSign = $isSent ? '-' : '+';
                $amtVal  = $isSent ? $t->amount + $t->fee : $t->recipient_amount;

                // Per-person stats for this contact
                $pc = $contacts->firstWhere('id', $party?->id);
            @endphp
            <div class="tx-item">
                <div class="tx-icon {{ $dir }}"><i class="bi {{ $icon }}"></i></div>
                <div class="tx-body">
                    <div class="tx-party">{{ $label }}: {{ $party?->name }}</div>
                    <div class="tx-tag">{{ $party?->kx_tag }}</div>
                    @if($t->note)
                        <div class="tx-note">"{{ $t->note }}"</div>
                    @endif
                    <div class="tx-ref">Ref: {{ $t->reference }}</div>
                    @if($pc && !$filterUserId)
                        <a href="{{ route('wallet.transfers', ['with' => $party->id]) }}" class="tx-view-all">
                            <i class="bi bi-bar-chart-fill me-1"></i>
                            All with {{ $party?->name }}:
                            <span style="color:#ef4444;">-₦{{ number_format($pc->total_sent, 0) }}</span>
                            &nbsp;/&nbsp;
                            <span style="color:var(--kx-green);">+₦{{ number_format($pc->total_received, 0) }}</span>
                        </a>
                    @endif
                </div>
                <div class="tx-right">
                    <div class="tx-amount {{ $dir }}">{{ $amtSign }}₦{{ number_format($amtVal, 2) }}</div>
                    <div class="tx-time">{{ $t->created_at->setTimezone('Africa/Lagos')->format('d M Y, g:i A') }}</div>
                    <div><span class="tx-badge {{ $t->status }}">{{ $t->status }}</span></div>
                    @if($isSent && $t->fee > 0)
                        <div style="font-size:.68rem;color:var(--text-placeholder);">Fee: ₦{{ number_format($t->fee, 2) }}</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="tx-empty">
                <i class="bi bi-send d-block"></i>
                @if($filterContact)
                    No transfers with {{ $filterContact->name }} yet.
                @else
                    No transfers yet.<br>
                    <a href="{{ route('wallet.send') }}" style="color:var(--kx-green);">Send your first transfer →</a>
                @endif
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($transfers->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function switchTab(tab) {
    document.getElementById('tab-history').style.display = tab === 'history' ? 'block' : 'none';
    document.getElementById('tab-people').style.display  = tab === 'people'  ? 'block' : 'none';
    document.getElementById('tab-btn-history').classList.toggle('active', tab === 'history');
    document.getElementById('tab-btn-people').classList.toggle('active', tab === 'people');
    // Save preference
    sessionStorage.setItem('tx_tab', tab);
}

// On load: restore tab preference, or default to 'people' if a ?with= filter is active
document.addEventListener('DOMContentLoaded', () => {
    const withFilter = {{ $filterUserId ? 'true' : 'false' }};
    const saved      = sessionStorage.getItem('tx_tab');
    switchTab(withFilter ? 'history' : (saved ?? 'history'));
});
</script>
@endsection
