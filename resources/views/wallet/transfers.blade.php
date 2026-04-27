@extends('selllayout')

@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-card: rgba(14,22,14,0.88);
    --kx-card-border: rgba(0,204,0,0.18);
}
.tx-page { max-width: 720px; margin: 0 auto; }

/* Header summary */
.tx-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .85rem;
    margin-bottom: 1.5rem;
}
.tx-sum-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-card-border);
    border-radius: 16px;
    padding: 1rem 1.25rem;
    text-align: center;
}
.tx-sum-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.45); margin-bottom: .25rem; }
.tx-sum-val   { font-size: 1.25rem; font-weight: 700; color: #fff; }
.tx-sum-val.green { color: var(--kx-green); }
.tx-sum-val.red   { color: #ef4444; }

/* Transfer item */
.tx-item {
    background: var(--kx-card);
    border: 1px solid var(--kx-card-border);
    border-radius: 16px;
    padding: 1rem 1.25rem;
    margin-bottom: .75rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: border-color .2s;
}
.tx-item:hover { border-color: rgba(0,204,0,.35); }

.tx-icon {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
.tx-icon.sent     { background: rgba(239,68,68,.12); color: #ef4444; }
.tx-icon.received { background: rgba(0,204,0,.12); color: var(--kx-green); }
.tx-icon.reversed { background: rgba(251,191,36,.12); color: #fbbf24; }

.tx-body  { flex: 1; min-width: 0; }
.tx-party { font-size: .9rem; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tx-tag   { font-size: .75rem; color: var(--kx-green); }
.tx-ref   { font-size: .72rem; color: rgba(255,255,255,.35); margin-top: .1rem; }
.tx-note  { font-size: .78rem; color: rgba(255,255,255,.5); font-style: italic; margin-top: .15rem; }

.tx-right  { text-align: right; flex-shrink: 0; }
.tx-amount { font-size: 1.05rem; font-weight: 700; }
.tx-amount.sent     { color: #ef4444; }
.tx-amount.received { color: var(--kx-green); }
.tx-amount.reversed { color: #fbbf24; }
.tx-time   { font-size: .72rem; color: rgba(255,255,255,.35); margin-top: .2rem; }
.tx-badge  {
    display: inline-block;
    font-size: .65rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    padding: .1rem .45rem; border-radius: 20px; margin-top: .25rem;
}
.tx-badge.completed { background: rgba(0,204,0,.12); color: var(--kx-green); }
.tx-badge.reversed  { background: rgba(251,191,36,.12); color: #fbbf24; }
.tx-badge.failed    { background: rgba(239,68,68,.12); color: #ef4444; }

/* Empty */
.tx-empty {
    background: var(--kx-card);
    border: 1px dashed var(--kx-card-border);
    border-radius: 16px;
    padding: 3rem 1.5rem;
    text-align: center;
    color: rgba(255,255,255,.4);
}
.tx-empty i { font-size: 2.5rem; margin-bottom: .75rem; color: rgba(0,204,0,.25); }

/* Send button */
.send-cta {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    background: linear-gradient(90deg, #00cc00, #009900);
    border-radius: 12px;
    padding: .8rem 1.5rem;
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    transition: opacity .2s;
    margin-bottom: 1.25rem;
}
.send-cta:hover { opacity: .88; color: #fff; }
</style>
@endpush

@section('content')
<div class="container py-4 tx-page">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-clock-history me-2" style="color:var(--kx-green)"></i>Transfer History</h5>
        <span class="text-muted" style="font-size:.8rem;">{{ Auth::user()->kx_tag }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3" style="border-radius:12px;">{{ session('success') }}</div>
    @endif

    {{-- Summary row --}}
    @php
        $sent     = $transfers->filter(fn($t) => $t->sender_id === Auth::id() && $t->status === 'completed');
        $received = $transfers->filter(fn($t) => $t->recipient_id === Auth::id() && $t->status === 'completed');
    @endphp
    <div class="tx-summary">
        <div class="tx-sum-card">
            <div class="tx-sum-label">Total Sent</div>
            <div class="tx-sum-val red">₦{{ number_format($sent->sum('amount'), 2) }}</div>
        </div>
        <div class="tx-sum-card">
            <div class="tx-sum-label">Total Received</div>
            <div class="tx-sum-val green">₦{{ number_format($received->sum('recipient_amount'), 2) }}</div>
        </div>
        <div class="tx-sum-card">
            <div class="tx-sum-label">Transfers</div>
            <div class="tx-sum-val">{{ $transfers->total() }}</div>
        </div>
    </div>

    <a href="{{ route('wallet.send') }}" class="send-cta">
        <i class="bi bi-send-fill"></i> Send Money
    </a>

    {{-- List --}}
    @forelse($transfers as $t)
        @php
            $isSent   = $t->sender_id === Auth::id();
            $dir      = $t->status === 'reversed' ? 'reversed' : ($isSent ? 'sent' : 'received');
            $icon     = $t->status === 'reversed' ? 'bi-arrow-counterclockwise' : ($isSent ? 'bi-arrow-up-right' : 'bi-arrow-down-left');
            $party    = $isSent ? $t->recipient : $t->sender;
            $label    = $isSent ? 'To' : 'From';
            $amtSign  = $isSent ? '-' : '+';
            $amtVal   = $isSent ? $t->amount + $t->fee : $t->recipient_amount;
        @endphp
        <div class="tx-item">
            <div class="tx-icon {{ $dir }}"><i class="bi {{ $icon }}"></i></div>
            <div class="tx-body">
                <div class="tx-party">{{ $label }}: {{ $party->name }}</div>
                <div class="tx-tag">{{ $party->kx_tag }}</div>
                @if($t->note)
                    <div class="tx-note">{{ $t->note }}</div>
                @endif
                <div class="tx-ref">Ref: {{ $t->reference }}</div>
            </div>
            <div class="tx-right">
                <div class="tx-amount {{ $dir }}">{{ $amtSign }}₦{{ number_format($amtVal, 2) }}</div>
                <div class="tx-time">{{ $t->created_at->setTimezone('Africa/Lagos')->format('d M Y, g:i A') }}</div>
                <div><span class="tx-badge {{ $t->status }}">{{ $t->status }}</span></div>
                @if($isSent && $t->fee > 0)
                    <div style="font-size:.7rem;color:rgba(255,255,255,.35);">Fee: ₦{{ number_format($t->fee, 2) }}</div>
                @endif
            </div>
        </div>
    @empty
        <div class="tx-empty">
            <i class="bi bi-send d-block"></i>
            No transfers yet.<br>
            <a href="{{ route('wallet.send') }}" style="color:var(--kx-green);">Send your first transfer →</a>
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($transfers->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $transfers->links() }}
        </div>
    @endif
</div>
@endsection
