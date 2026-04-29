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

.success-icon{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#fff;font-size:2rem;}

.btn-kx-primary{background:linear-gradient(90deg,#00cc00,#009900);border:none;border-radius:8px;padding:.75rem 1.5rem;color:#fff;font-weight:600;cursor:pointer;transition:opacity .2s;width:100%;}
.btn-kx-primary:hover{opacity:.9;}
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width:500px;">

    {{-- Header --}}
    <div class="kx-hero">
        <h1><i class="bi bi-check-circle me-2"></i>Conversion Successful</h1>
        <p>Your cryptocurrency conversion has been completed</p>
    </div>

    {{-- Success Card --}}
    <div class="kx-card text-center">
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <h4 style="color:var(--kx-text);margin-bottom:1rem;">Conversion Completed!</h4>

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
                <span style="color:var(--kx-muted);">Fee:</span>
                <span style="color:#f59e0b;font-weight:600;">{{ $conversion->fee_amount }} {{ $conversion->to_coin }}</span>
            </div>
        </div>

        <p style="color:var(--kx-muted);font-size:.9rem;margin-bottom:1.5rem;">
            Your converted {{ $conversion->to_coin }} has been credited to your wallet.
        </p>

        <a href="{{ route('dashboard') }}" class="btn-kx-primary" style="text-decoration:none;display:inline-block;">
            <i class="bi bi-house me-1"></i>Back to Dashboard
        </a>
    </div>

    {{-- Additional Actions --}}
    <div class="kx-card">
        <h6 style="color:var(--kx-text);margin-bottom:1rem;">What would you like to do next?</h6>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <a href="{{ route('convert') }}" style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:1rem;text-decoration:none;text-align:center;color:var(--kx-text);">
                <i class="bi bi-arrow-left-right" style="font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;"></i>
                <div style="font-size:.9rem;font-weight:600;">Convert Again</div>
            </a>
            <a href="{{ route('buy') }}" style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:8px;padding:1rem;text-decoration:none;text-align:center;color:var(--kx-text);">
                <i class="bi bi-cash" style="font-size:1.5rem;color:var(--kx-green);margin-bottom:.5rem;"></i>
                <div style="font-size:.9rem;font-weight:600;">Buy Crypto</div>
            </a>
        </div>
    </div>
</div>
@endsection