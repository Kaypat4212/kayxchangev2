@extends('layout')

@section('title', 'Withdrawal Submitted')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-green-dim:rgba(0,204,0,0.10);--kx-green-glow:rgba(0,204,0,0.22);
    --kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
}
body{ background:var(--kx-dark); color:var(--kx-text); }

.ws-wrap{ max-width:520px; margin:0 auto; padding:2.5rem 1rem 4rem; }

/* Success hero card */
.ws-hero-card{
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:20px; padding:2.5rem 1.75rem; text-align:center;
    margin-bottom:1.25rem; position:relative; overflow:hidden;
}
.ws-hero-card::before{
    content:''; position:absolute; top:-60px; left:50%; transform:translateX(-50%);
    width:260px; height:260px;
    background:radial-gradient(circle,rgba(0,204,0,.1),transparent 65%);
    pointer-events:none;
}
.ws-check-ring{
    width:80px; height:80px; border-radius:50%; margin:0 auto 1.25rem;
    background:rgba(0,204,0,.1); border:2px solid rgba(0,204,0,.25);
    display:flex; align-items:center; justify-content:center;
    position:relative; z-index:1;
}
.ws-check-ring i{ font-size:2.2rem; color:var(--kx-green); }
.ws-hero-card h2{ font-size:1.35rem; font-weight:800; color:#fff; margin:0 0 .4rem; }
.ws-hero-card p{ color:var(--kx-muted); font-size:.85rem; margin:0; }

/* Status pill */
.ws-status-pill{
    display:inline-flex; align-items:center; gap:.4rem;
    border-radius:20px; padding:.3rem .9rem; font-size:.75rem; font-weight:700;
    margin-top:.85rem;
}
.ws-status-pill.pending{ background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.25); color:#fbbf24; }
.ws-status-pill.approved{ background:var(--kx-green-dim); border:1px solid rgba(0,204,0,.25); color:var(--kx-green); }
.ws-status-pill.cancelled{ background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25); color:#ef4444; }

/* Details card */
.ws-details-card{
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:16px; padding:1.5rem 1.75rem; margin-bottom:1.25rem;
}
.ws-details-title{
    font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
    color:var(--kx-muted); margin-bottom:1rem; display:flex; align-items:center; gap:.45rem;
}
.ws-details-title i{ color:var(--kx-green); }
.ws-row{
    display:flex; justify-content:space-between; align-items:center;
    padding:.4rem 0; font-size:.85rem;
}
.ws-row:not(:last-child){ border-bottom:1px solid rgba(255,255,255,.04); }
.ws-row-key{ color:var(--kx-muted); }
.ws-row-val{ color:#fff; font-weight:600; text-align:right; }
.ws-row-val.amount{ color:var(--kx-green); font-size:1rem; font-weight:800; }
.ws-row-val.ref{ font-family:monospace; font-size:.8rem; color:#93c5fd; }

/* Info banner */
.ws-info-banner{
    background:rgba(96,165,250,.07); border:1px solid rgba(96,165,250,.18);
    border-radius:12px; padding:.85rem 1rem; font-size:.8rem;
    color:#93c5fd; display:flex; align-items:flex-start; gap:.6rem;
    margin-bottom:1.25rem; line-height:1.5;
}
.ws-info-banner i{ flex-shrink:0; margin-top:.1rem; }

/* CTA buttons */
.ws-btn-primary{
    width:100%; background:linear-gradient(135deg,#00cc00,#007a0c);
    border:none; border-radius:12px; color:#fff; font-weight:700;
    font-size:.92rem; padding:.9rem; cursor:pointer;
    box-shadow:0 4px 18px rgba(0,204,0,.25); transition:all .22s;
    text-decoration:none; display:block; text-align:center; margin-bottom:.75rem;
}
.ws-btn-primary:hover{ transform:translateY(-2px); box-shadow:0 8px 28px rgba(0,204,0,.35); color:#fff; }
.ws-btn-sec{
    width:100%; background:none; border:1px solid var(--kx-border); color:var(--kx-muted);
    border-radius:12px; font-size:.87rem; font-weight:600; padding:.75rem;
    cursor:pointer; transition:all .18s; text-decoration:none; display:block; text-align:center;
}
.ws-btn-sec:hover{ border-color:rgba(255,255,255,.2); color:#fff; }

/* animated check draw */
.ws-svg-check{ display:block; margin:0 auto 1.5rem; }
.ws-check-path{
    stroke:var(--kx-green); stroke-width:3; fill:none; stroke-linecap:round; stroke-linejoin:round;
    stroke-dasharray:60; stroke-dashoffset:60;
    animation:check-draw .5s .2s ease forwards;
}
@keyframes check-draw{ to{ stroke-dashoffset:0; } }
</style>
@endpush

@section('content')
@php
    $bankDetails = is_array($bank_details) ? $bank_details : json_decode($bank_details, true);
    $statusClass = match($withdrawal->status) { 'approved' => 'approved', 'cancelled' => 'cancelled', default => 'pending' };
    $statusIcon  = match($withdrawal->status) { 'approved' => 'bi-check-circle-fill', 'cancelled' => 'bi-x-circle-fill', default => 'bi-hourglass-split' };
    $statusLabel = match($withdrawal->status) { 'approved' => 'Approved', 'cancelled' => 'Cancelled', default => 'Pending approval' };
@endphp

<div class="ws-wrap">

    {{-- Hero card --}}
    <div class="ws-hero-card">
        <div class="ws-check-ring">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2>Withdrawal Request Submitted!</h2>
        <p>Your request is under review. Funds will be sent once approved by our team.</p>
        <div class="ws-status-pill {{ $statusClass }}">
            <i class="bi {{ $statusIcon }}"></i> {{ $statusLabel }}
        </div>
    </div>

    {{-- Transaction details --}}
    <div class="ws-details-card">
        <div class="ws-details-title"><i class="bi bi-receipt"></i> Transaction Details</div>
        <div class="ws-row">
            <span class="ws-row-key">Amount</span>
            <span class="ws-row-val amount">₦{{ number_format($withdrawal->amount, 2) }}</span>
        </div>
        <div class="ws-row">
            <span class="ws-row-key">Reference</span>
            <span class="ws-row-val ref">{{ $withdrawal->reference }}</span>
        </div>
        <div class="ws-row">
            <span class="ws-row-key">Payment Method</span>
            <span class="ws-row-val">Bank Transfer</span>
        </div>
        <div class="ws-row">
            <span class="ws-row-key">Submitted</span>
            <span class="ws-row-val">{{ $withdrawal->created_at->format('d M Y, H:i') }}</span>
        </div>
    </div>

    {{-- Bank details --}}
    <div class="ws-details-card">
        <div class="ws-details-title"><i class="bi bi-bank2"></i> Destination Account</div>
        <div class="ws-row">
            <span class="ws-row-key">Bank</span>
            <span class="ws-row-val">{{ $bankDetails['bank_name'] ?? 'N/A' }}</span>
        </div>
        <div class="ws-row">
            <span class="ws-row-key">Account Number</span>
            <span class="ws-row-val">{{ $bankDetails['account_number'] ?? 'N/A' }}</span>
        </div>
        <div class="ws-row">
            <span class="ws-row-key">Account Name</span>
            <span class="ws-row-val">{{ $bankDetails['account_name'] ?? 'N/A' }}</span>
        </div>
    </div>

    {{-- Info note --}}
    <div class="ws-info-banner">
        <i class="bi bi-info-circle-fill"></i>
        <span>Processing typically takes <strong>1–3 business hours</strong>. You'll be notified by email when your withdrawal is approved.</span>
    </div>

    {{-- CTA --}}
    <a href="{{ route('dashboard') }}" class="ws-btn-primary">
        <i class="bi bi-grid-1x2-fill me-2"></i>Back to Dashboard
    </a>
    <a href="{{ route('withdraw') }}" class="ws-btn-sec">
        Make Another Withdrawal
    </a>

</div>
@endsection

