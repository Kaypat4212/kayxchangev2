@extends('selllayout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}

.kx-summary-hero{background:linear-gradient(135deg,rgba(0,204,0,.09) 0%,rgba(0,80,0,.05) 100%);border:1px solid rgba(0,204,0,.15);border-radius:20px;padding:2rem 1.5rem;text-align:center;margin-bottom:1.75rem;position:relative;overflow:hidden;}
.kx-summary-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% -20%,rgba(0,204,0,.08),transparent 60%);pointer-events:none;}
.kx-summary-icon{width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,#007a0c,#00cc00);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.8rem;color:#fff;box-shadow:0 8px 32px rgba(0,204,0,.32);}
.kx-summary-hero h1{font-size:1.45rem;font-weight:700;color:#fff;margin-bottom:.3rem;}
.kx-summary-hero p{color:var(--kx-muted);font-size:.87rem;margin:0;}
.kx-ref{display:inline-block;background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.18);border-radius:8px;color:#00cc00;font-size:.8rem;font-family:monospace;padding:.35rem .8rem;margin-top:.6rem;word-break:break-all;}

/* Status badge */
.kx-status{display:inline-flex;align-items:center;gap:.5rem;border-radius:20px;padding:.35rem .9rem;font-size:.8rem;font-weight:600;text-transform:capitalize;}
.kx-status.pending{background:rgba(255,193,7,.12);border:1px solid rgba(255,193,7,.3);color:#ffc107;}
.kx-status.approved,.kx-status.completed{background:rgba(0,204,0,.12);border:1px solid rgba(0,204,0,.3);color:#00cc00;}
.kx-status.rejected,.kx-status.failed{background:rgba(220,53,69,.12);border:1px solid rgba(220,53,69,.3);color:#ff6b6b;}
.kx-status.processing{background:rgba(13,110,253,.12);border:1px solid rgba(13,110,253,.3);color:#6ea8fe;}

.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;overflow:hidden;margin-bottom:1.25rem;}
.kx-card-hd{padding:1.1rem 1.4rem;border-bottom:1px solid var(--kx-border);display:flex;align-items:center;gap:.7rem;}
.kx-card-hd .hico{width:36px;height:36px;border-radius:9px;background:rgba(0,204,0,.1);display:flex;align-items:center;justify-content:center;color:var(--kx-green);font-size:1rem;flex-shrink:0;}
.kx-card-hd h5{font-size:.95rem;font-weight:600;color:#fff;margin:0;}
.kx-card-hd p{font-size:.76rem;color:var(--kx-muted);margin:0;}
.kx-card-bd{padding:0;}

.kx-detail-row{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.4rem;border-bottom:1px solid var(--kx-border);gap:1rem;}
.kx-detail-row:last-child{border-bottom:none;}
.kx-detail-label{font-size:.8rem;color:var(--kx-muted);flex-shrink:0;}
.kx-detail-value{font-size:.9rem;color:#fff;font-weight:500;text-align:right;word-break:break-all;}
.kx-detail-value.green{color:#00cc00;}
.kx-detail-value.mono{font-family:monospace;font-size:.8rem;}

/* Proof preview */
.kx-proof-box{padding:1.25rem 1.4rem;}
.kx-proof-img{width:100%;max-height:280px;object-fit:cover;border-radius:10px;display:block;border:1px solid var(--kx-border);}
.kx-proof-pdf{display:flex;align-items:center;gap:.75rem;background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:.85rem 1.1rem;text-decoration:none;color:var(--kx-text);transition:border-color .2s;}
.kx-proof-pdf:hover{border-color:rgba(0,204,0,.3);color:#fff;}
.kx-proof-pdf .icon{font-size:1.5rem;color:#ff6b6b;flex-shrink:0;}
.kx-proof-pdf .info .name{font-size:.85rem;font-weight:600;}
.kx-proof-pdf .info .hint{font-size:.75rem;color:var(--kx-muted);}

/* Buttons */
.kx-btn{display:flex;align-items:center;justify-content:center;gap:.6rem;border-radius:12px;font-size:.95rem;font-weight:600;padding:.85rem;cursor:pointer;transition:all .22s ease;text-decoration:none;border:none;}
.kx-btn-primary{background:linear-gradient(135deg,#00cc00,#007a0c);color:#fff;box-shadow:0 4px 20px rgba(0,204,0,.22);}
.kx-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,204,0,.35);color:#fff;}
.kx-btn-outline{background:transparent;border:1.5px solid var(--kx-border);color:var(--kx-muted);}
.kx-btn-outline:hover{border-color:rgba(0,204,0,.3);color:#fff;}

.kx-info-box{display:flex;align-items:flex-start;gap:.7rem;background:rgba(255,193,7,.07);border:1px solid rgba(255,193,7,.2);border-radius:10px;padding:.9rem 1.1rem;font-size:.82rem;color:#ffc107;margin-bottom:1.25rem;}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-xl-6 col-lg-7">

    {{-- Hero --}}
    <div class="kx-summary-hero">
        <div class="kx-summary-icon"><i class="bi bi-receipt-cutoff"></i></div>
        <h1>Trade Summary</h1>
        <p>Your sell order has been submitted and is under review</p>
        <div class="kx-ref">REF: {{ $trade->transaction_ref }}</div>
    </div>

    {{-- Info --}}
    <div class="kx-info-box">
        <i class="bi bi-info-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
        <div>Your trade is being reviewed. We'll credit your payout within <strong>15–30 minutes</strong> after confirmation.</div>
    </div>

    {{-- Trade Details --}}
    <div class="kx-card">
        <div class="kx-card-hd">
            <div class="hico"><i class="bi bi-list-ul"></i></div>
            <div><h5>Trade Details</h5><p>Full breakdown of your sell order</p></div>
        </div>
        <div class="kx-card-bd">
            <div class="kx-detail-row">
                <span class="kx-detail-label">Status</span>
                <span class="kx-detail-value">
                    <span class="kx-status {{ $trade->status }}">
                        @if($trade->status === 'pending')<i class="bi bi-hourglass-split me-1"></i>
                        @elseif(in_array($trade->status,['approved','completed']))<i class="bi bi-check-circle-fill me-1"></i>
                        @elseif(in_array($trade->status,['rejected','failed']))<i class="bi bi-x-circle-fill me-1"></i>
                        @else<i class="bi bi-arrow-repeat me-1"></i>@endif
                        {{ ucfirst($trade->status) }}
                    </span>
                </span>
            </div>
            <div class="kx-detail-row">
                <span class="kx-detail-label">Coin</span>
                <span class="kx-detail-value">{{ $trade->coin }}</span>
            </div>
            <div class="kx-detail-row">
                <span class="kx-detail-label">USD Amount</span>
                <span class="kx-detail-value">${{ number_format($trade->usd_amount, 2) }}</span>
            </div>
            <div class="kx-detail-row">
                <span class="kx-detail-label">Naira Payout</span>
                <span class="kx-detail-value green">₦{{ number_format($trade->naira_amount, 2) }}</span>
            </div>
            <div class="kx-detail-row">
                <span class="kx-detail-label">Payout Method</span>
                <span class="kx-detail-value">
                    @php $methods=['default_bank'=>'Default Bank','external_bank'=>'External Bank','wallet_balance'=>'Wallet Balance']; @endphp
                    {{ $methods[$trade->payment_method] ?? ucfirst(str_replace('_',' ',$trade->payment_method)) }}
                </span>
            </div>
            @if($trade->bank_name)
            <div class="kx-detail-row">
                <span class="kx-detail-label">Bank</span>
                <span class="kx-detail-value">{{ $trade->bank_name }}</span>
            </div>
            @endif
            @if($trade->account_number)
            <div class="kx-detail-row">
                <span class="kx-detail-label">Account No.</span>
                <span class="kx-detail-value mono">{{ $trade->account_number }}</span>
            </div>
            @endif
            @if($trade->account_name)
            <div class="kx-detail-row">
                <span class="kx-detail-label">Account Name</span>
                <span class="kx-detail-value">{{ $trade->account_name }}</span>
            </div>
            @endif
            @if($trade->wallet_address)
            <div class="kx-detail-row">
                <span class="kx-detail-label">Wallet Used</span>
                <span class="kx-detail-value mono">{{ Str::limit($trade->wallet_address, 24) }}</span>
            </div>
            @endif
            <div class="kx-detail-row">
                <span class="kx-detail-label">Date</span>
                <span class="kx-detail-value">{{ $trade->created_at->format('M j, Y · g:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- Proof --}}
    @if($trade->proof)
    <div class="kx-card">
        <div class="kx-card-hd">
            <div class="hico"><i class="bi bi-image-fill"></i></div>
            <div><h5>Payment Proof</h5><p>Your uploaded receipt</p></div>
        </div>
        @php
            $proofExt = strtolower(pathinfo($trade->proof, PATHINFO_EXTENSION));
            $proofUrl = route('storage.file', $trade->proof);
        @endphp
        <div class="kx-proof-box">
            @if(in_array($proofExt, ['jpg','jpeg','png','webp','gif']))
                <img src="{{ $proofUrl }}" alt="Payment proof" class="kx-proof-img"
                     onerror="this.style.display='none';document.getElementById('proof-fallback').style.display='flex';">
                <a id="proof-fallback" href="{{ $proofUrl }}" target="_blank" class="kx-proof-pdf" style="display:none;margin-top:.75rem;">
                    <i class="bi bi-image icon" style="color:var(--kx-green)"></i>
                    <div class="info">
                        <div class="name">View Payment Proof</div>
                        <div class="hint">Image could not load — tap to open directly</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
                <a href="{{ $proofUrl }}" target="_blank" class="kx-proof-pdf" style="margin-top:.75rem;">
                    <i class="bi bi-arrows-fullscreen icon" style="color:var(--kx-green);font-size:1rem;"></i>
                    <div class="info">
                        <div class="name">View Full Image</div>
                        <div class="hint">Open in new tab</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
            @else
                <a href="{{ $proofUrl }}" target="_blank" class="kx-proof-pdf">
                    <i class="bi bi-file-earmark-pdf-fill icon"></i>
                    <div class="info">
                        <div class="name">Payment Proof (PDF)</div>
                        <div class="hint">Tap to view or download</div>
                    </div>
                    <i class="bi bi-box-arrow-up-right ms-auto" style="color:var(--kx-muted)"></i>
                </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="d-flex gap-3" style="margin-bottom:2rem;">
        <a href="{{ route('dashboard') }}" class="kx-btn kx-btn-outline flex-fill">
            <i class="bi bi-house"></i> Dashboard
        </a>
        <a href="{{ route('transactions.history') }}" class="kx-btn kx-btn-primary flex-fill">
            <i class="bi bi-clock-history"></i> View History
        </a>
    </div>

</div>
</div>
@endsection

