@extends('selllayout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}

/* Success animation */
.kx-success-wrap{display:flex;flex-direction:column;align-items:center;text-align:center;padding:2.5rem 1.5rem 2rem;margin-bottom:1.5rem;}
.kx-success-ring{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,rgba(0,204,0,.18),rgba(0,80,0,.1));border:3px solid rgba(0,204,0,.25);display:flex;align-items:center;justify-content:center;margin-bottom:1.4rem;animation:popIn .5s cubic-bezier(.175,.885,.32,1.275) forwards;position:relative;}
.kx-success-ring::before{content:'';position:absolute;inset:-8px;border-radius:50%;border:2px solid rgba(0,204,0,.12);animation:pulse 2s ease-in-out infinite;}
.kx-success-inner{width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,#00cc00,#007a0c);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;box-shadow:0 8px 32px rgba(0,204,0,.4);}
@keyframes popIn{0%{transform:scale(.4);opacity:0}100%{transform:scale(1);opacity:1}}
@keyframes pulse{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.12);opacity:.2}}

.kx-success-wrap h1{font-size:1.7rem;font-weight:800;color:#fff;margin-bottom:.4rem;animation:fadeUp .5s .25s both;}
.kx-success-wrap p{color:var(--kx-muted);font-size:.9rem;max-width:340px;animation:fadeUp .5s .35s both;}
@keyframes fadeUp{0%{transform:translateY(14px);opacity:0}100%{transform:translateY(0);opacity:1}}

/* Trade card */
.kx-trade-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:18px;overflow:hidden;margin-bottom:1.5rem;animation:fadeUp .5s .45s both;}
.kx-trade-top{padding:1.2rem 1.4rem;border-bottom:1px solid var(--kx-border);display:flex;align-items:center;justify-content:space-between;}
.kx-trade-top .label{font-size:.78rem;color:var(--kx-muted);}
.kx-trade-top .ref{font-size:.8rem;font-family:monospace;color:#00cc00;}
.kx-trade-amounts{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:.75rem;padding:1.4rem;}
.kx-amt-side{text-align:center;}
.kx-amt-label{font-size:.73rem;color:var(--kx-muted);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em;}
.kx-amt-value{font-size:1.2rem;font-weight:800;color:#fff;}
.kx-amt-sub{font-size:.75rem;color:var(--kx-muted);}
.kx-amt-value.green{color:#00cc00;}
.kx-amt-arrow{width:38px;height:38px;border-radius:50%;background:rgba(0,204,0,.1);border:1.5px solid rgba(0,204,0,.2);display:flex;align-items:center;justify-content:center;color:#00cc00;font-size:1rem;}

/* Status pill */
.kx-status-pill{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,193,7,.1);border:1px solid rgba(255,193,7,.25);border-radius:20px;padding:.3rem .8rem;font-size:.78rem;font-weight:600;color:#ffc107;}

/* Buttons */
.kx-btn{display:flex;align-items:center;justify-content:center;gap:.55rem;border-radius:12px;font-size:.95rem;font-weight:600;padding:.85rem 1.2rem;cursor:pointer;transition:all .22s ease;text-decoration:none;border:none;}
.kx-btn-primary{background:linear-gradient(135deg,#00cc00,#007a0c);color:#fff;box-shadow:0 4px 20px rgba(0,204,0,.22);}
.kx-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,204,0,.35);color:#fff;}
.kx-btn-outline{background:transparent;border:1.5px solid var(--kx-border);color:var(--kx-muted);}
.kx-btn-outline:hover{border-color:rgba(0,204,0,.3);color:#fff;}

.kx-confetti{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;overflow:hidden;}
.dot{position:absolute;top:-10px;border-radius:3px;animation:fall linear forwards;}
@keyframes fall{to{transform:translateY(100vh) rotate(720deg);opacity:0}}

.kx-actions-anim{animation:fadeUp .5s .55s both;}
</style>
@endpush

@section('content')
{{-- Confetti --}}
<div class="kx-confetti" id="confetti"></div>

<div class="row justify-content-center" style="position:relative;z-index:1;">
<div class="col-xl-5 col-lg-6">

    {{-- Success hero --}}
    <div class="kx-success-wrap">
        <div class="kx-success-ring">
            <div class="kx-success-inner"><i class="bi bi-check-lg"></i></div>
        </div>
        <h1>Trade Submitted!</h1>
        <p>Your sell order is under review. We'll process your payout as soon as we confirm your payment.</p>
    </div>

    {{-- Trade recap --}}
    <div class="kx-trade-card">
        <div class="kx-trade-top">
            <div>
                <div class="label">Transaction Ref</div>
                <div class="ref">{{ $trade->transaction_ref }}</div>
            </div>
            <div class="kx-status-pill"><i class="bi bi-hourglass-split"></i> Pending Review</div>
        </div>
        <div class="kx-trade-amounts">
            <div class="kx-amt-side">
                <div class="kx-amt-label">You Sent</div>
                <div class="kx-amt-value">{{ $trade->coin }}</div>
                <div class="kx-amt-sub">${{ number_format($trade->usd_amount, 2) }}</div>
            </div>
            <div class="kx-amt-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="kx-amt-side">
                <div class="kx-amt-label">You Get</div>
                <div class="kx-amt-value green">₦{{ number_format($trade->naira_amount, 2) }}</div>
                <div class="kx-amt-sub">Nigerian Naira</div>
            </div>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="d-flex flex-column gap-3 kx-actions-anim" style="margin-bottom:2rem;">
        <a href="{{ route('dashboard') }}" class="kx-btn kx-btn-primary">
            <i class="bi bi-house-fill"></i> Back to Dashboard
        </a>
        <a href="{{ route('transactions.history') }}" class="kx-btn kx-btn-outline">
            <i class="bi bi-clock-history"></i> View Transaction History
        </a>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
/* Simple confetti burst */
(function(){
    const colors = ['#00cc00','#007a0c','#b3ffb3','#ffc107','#fff','rgba(0,204,0,.5)'];
    const container = document.getElementById('confetti');
    for (let i = 0; i < 55; i++) {
        const d = document.createElement('div');
        d.className = 'dot';
        const size = Math.random() * 10 + 6;
        d.style.cssText = `
            width:${size}px;height:${size}px;
            left:${Math.random()*100}%;
            background:${colors[Math.floor(Math.random()*colors.length)]};
            opacity:${Math.random()*.8+.2};
            animation-duration:${Math.random()*2+1.5}s;
            animation-delay:${Math.random()*.8}s;
            border-radius:${Math.random()>0.5?'50%':'3px'};
        `;
        container.appendChild(d);
    }
    setTimeout(() => container.remove(), 4000);
})();
</script>
@endpush

