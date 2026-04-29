@extends('buylayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;
    --kx-dark:#0d1117;
    --kx-card:#161b27;
    --kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0;
    --kx-muted:#7a8599;
}
body{background:var(--kx-dark);color:var(--kx-text);}

.kx-hero{background:linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);border-bottom:1px solid var(--kx-border);padding:1.5rem 1rem 1rem;text-align:center;margin-bottom:1.5rem;}
.kx-hero h1{font-size:1.5rem;font-weight:700;color:#fff;margin:0 0 .25rem;}
.kx-hero p{color:var(--kx-muted);font-size:.875rem;margin:0;}

.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1rem;}

.failed-icon{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#fff;font-size:2rem;}

.btn-kx-primary{background:linear-gradient(90deg,#00cc00,#009900);border:none;border-radius:8px;padding:.75rem 1.5rem;color:#fff;font-weight:600;cursor:pointer;transition:opacity .2s;width:100%;}
.btn-kx-primary:hover{opacity:.9;}

.btn-kx-secondary{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:.75rem 1.5rem;color:var(--kx-text);font-weight:600;cursor:pointer;transition:all .2s;}
.btn-kx-secondary:hover{border-color:var(--kx-green);color:var(--kx-green);}
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:500px;">

    {{-- Header --}}
    <div class="kx-hero">
        <h1><i class="bi bi-x-circle me-2"></i>Conversion Failed</h1>
        <p>Your cryptocurrency conversion could not be completed</p>
    </div>

    {{-- Failed Card --}}
    <div class="kx-card text-center">
        <div class="failed-icon">
            <i class="bi bi-x-circle-fill"></i>
        </div>

        <h4 style="color:var(--kx-text);margin-bottom:1rem;">Conversion Failed</h4>

        @if($conversion->failure_reason)
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:1rem;margin-bottom:1rem;text-align:left;">
            <div style="color:#f87171;font-size:.9rem;">
                <strong>Reason:</strong> {{ $conversion->failure_reason }}
            </div>
        </div>
        @endif

        <div style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:1rem;margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
                <span style="color:var(--kx-muted);">From:</span>
                <span style="color:var(--kx-text);font-weight:600;">{{ $conversion->from_amount }} {{ $conversion->from_coin }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
                <span style="color:var(--kx-muted);">To:</span>
                <span style="color:var(--kx-text);font-weight:600;">{{ $conversion->to_amount }} {{ $conversion->to_coin }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--kx-muted);">Status:</span>
                <span style="color:#f87171;font-weight:600;">{{ ucfirst($conversion->status) }}</span>
            </div>
        </div>

        <p style="color:var(--kx-muted);font-size:.9rem;margin-bottom:1.5rem;">
            Don't worry, no funds were deducted from your account. Please try again or contact support if the issue persists.
        </p>

        <div style="display:flex;gap:.75rem;">
            <a href="{{ route('convert') }}" class="btn-kx-primary" style="text-decoration:none;flex:1;">
                <i class="bi bi-arrow-clockwise me-1"></i>Try Again
            </a>
            <a href="{{ route('dashboard') }}" class="btn-kx-secondary" style="text-decoration:none;flex:1;">
                <i class="bi bi-house me-1"></i>Dashboard
            </a>
        </div>
    </div>

    {{-- Support --}}
    <div class="kx-card">
        <h6 style="color:var(--kx-text);margin-bottom:1rem;">Need Help?</h6>
        <p style="color:var(--kx-muted);font-size:.9rem;margin-bottom:1rem;">
            If you're experiencing issues with conversions, our support team is here to help.
        </p>
        <a href="{{ route('chat') }}" class="btn-kx-secondary" style="text-decoration:none;width:100%;display:inline-block;text-align:center;">
            <i class="bi bi-chat-dots me-1"></i>Contact Support
        </a>
    </div>
</div>
@endsection