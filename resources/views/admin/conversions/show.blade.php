@extends('adminnavlayout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.detail-row{display:flex;justify-content:space-between;padding:.65rem 0;border-bottom:1px solid var(--kx-border);font-size:.85rem;}
.detail-row:last-child{border-bottom:none;}
.detail-row .k{color:var(--kx-muted);}
.detail-row .v{color:var(--kx-text);font-weight:600;text-align:right;}
.mono{font-family:monospace;font-size:.78rem;word-break:break-all;}
.status-pill{font-size:.7rem;font-weight:700;padding:.25rem .7rem;border-radius:999px;text-transform:uppercase;letter-spacing:.03em;}
.status-pill.completed{background:rgba(16,185,129,.12);color:#34d399;}
.status-pill.pending{background:rgba(245,158,11,.12);color:#fbbf24;}
.status-pill.processing{background:rgba(56,189,248,.12);color:#38bdf8;}
.status-pill.failed{background:rgba(239,68,68,.12);color:#f87171;}
.mode-tag{font-size:.7rem;font-weight:700;padding:.25rem .7rem;border-radius:6px;}
.mode-tag.manual{background:rgba(167,139,250,.12);color:#a78bfa;}
.mode-tag.cryptomus{background:rgba(0,204,0,.1);color:#4ade80;}
.btn-action{border:none;border-radius:10px;padding:.7rem 1.5rem;font-weight:700;font-size:.85rem;cursor:pointer;width:100%;}
.btn-complete{background:linear-gradient(90deg,#00cc00,#009900);color:#fff;}
.btn-complete:hover{opacity:.9;}
.btn-fail{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.3);}
.btn-fail:hover{background:rgba(239,68,68,.25);}
.form-input{width:100%;background:rgba(0,0,0,.35);border:1px solid var(--kx-border);color:var(--kx-text);border-radius:8px;padding:.55rem .75rem;font-size:.85rem;}
.form-input:focus{border-color:var(--kx-green);box-shadow:0 0 0 3px rgba(0,204,0,.12);outline:none;}
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:800px">

    <div class="kx-page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1><i class="bi bi-arrow-left-right me-2" style="color:var(--kx-green)"></i>Conversion #{{ $conversion->id }}</h1>
            <p>{{ $conversion->created_at->format('M d, Y H:i') }} · requested by {{ $conversion->user->name ?? 'User #'.$conversion->user_id }}</p>
        </div>
        <div class="d-flex gap-2">
            <span class="mode-tag {{ $conversion->mode }}">{{ ucfirst($conversion->mode) }}</span>
            <span class="status-pill {{ $conversion->status }}">{{ $conversion->status }}</span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Details --}}
    <div class="kx-card">
        <h6 class="mb-3" style="color:var(--kx-text)">Conversion Details</h6>
        <div class="detail-row"><span class="k">User</span><span class="v">{{ $conversion->user->name ?? '—' }} ({{ $conversion->user->email ?? '—' }})</span></div>
        <div class="detail-row"><span class="k">Pair</span><span class="v">{{ $conversion->from_coin }} → {{ $conversion->to_coin }}</span></div>
        <div class="detail-row"><span class="k">User sends</span><span class="v">{{ rtrim(rtrim(number_format((float) $conversion->from_amount, 8), '0'), '.') }} {{ $conversion->from_coin }}</span></div>
        <div class="detail-row"><span class="k">Rate used</span><span class="v">1 {{ $conversion->from_coin }} = {{ rtrim(rtrim(number_format((float) $conversion->rate_used, 8), '0'), '.') }} {{ $conversion->to_coin }}</span></div>
        <div class="detail-row"><span class="k">Fee</span><span class="v" style="color:#f59e0b">{{ rtrim(rtrim(number_format((float) $conversion->fee_amount, 8), '0'), '.') }} {{ $conversion->to_coin }}</span></div>
        <div class="detail-row"><span class="k">User receives</span><span class="v" style="color:var(--kx-green)">{{ rtrim(rtrim(number_format((float) $conversion->to_amount, 8), '0'), '.') }} {{ $conversion->to_coin }}</span></div>

        @if($conversion->wallet_address)
        <div class="detail-row">
            <span class="k">Destination wallet</span>
            <span class="v mono" style="color:var(--kx-green)">{{ $conversion->wallet_address }}</span>
        </div>
        @endif

        @if($conversion->cryptomus_order_id)
        <div class="detail-row"><span class="k">Cryptomus UUID</span><span class="v mono">{{ $conversion->cryptomus_order_id }}</span></div>
        @endif

        @if($conversion->processed_by)
        <div class="detail-row"><span class="k">Processed by</span><span class="v">{{ $conversion->processor->name ?? 'Admin #'.$conversion->processed_by }}</span></div>
        @endif
        @if($conversion->completed_at)
        <div class="detail-row"><span class="k">Completed at</span><span class="v">{{ $conversion->completed_at->format('M d, Y H:i') }}</span></div>
        @endif
        @if($conversion->failure_reason)
        <div class="detail-row"><span class="k">Failure reason</span><span class="v" style="color:#f87171">{{ $conversion->failure_reason }}</span></div>
        @endif
        @if($conversion->admin_note)
        <div class="detail-row"><span class="k">Admin note</span><span class="v">{{ $conversion->admin_note }}</span></div>
        @endif
    </div>

    {{-- Actions --}}
    @if(!$conversion->isCompleted() && !$conversion->isFailed())
    <div class="row g-3">
        <div class="col-md-6">
            <div class="kx-card">
                <h6 class="mb-2" style="color:#34d399"><i class="bi bi-check-circle me-1"></i>Mark Completed</h6>
                <p style="font-size:.75rem;color:var(--kx-muted)">
                    @if($conversion->isManual())
                        Confirm after you have sent <strong style="color:var(--kx-text)">{{ rtrim(rtrim(number_format((float) $conversion->to_amount, 8), '0'), '.') }} {{ $conversion->to_coin }}</strong> to the wallet above.
                    @else
                        Confirm this conversion has been fulfilled.
                    @endif
                </p>
                <form method="POST" action="{{ route('admin.conversions.complete', $conversion->id) }}">
                    @csrf
                    <input type="text" name="admin_note" class="form-input mb-2" placeholder="Note (optional, e.g. tx hash)">
                    <button type="submit" class="btn-action btn-complete" onclick="return confirm('Mark conversion #{{ $conversion->id }} as completed?')">
                        <i class="bi bi-check-lg me-1"></i>Complete
                    </button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="kx-card">
                <h6 class="mb-2" style="color:#f87171"><i class="bi bi-x-circle me-1"></i>Mark Failed</h6>
                <p style="font-size:.75rem;color:var(--kx-muted)">Cancel this conversion. The user will see the failure reason.</p>
                <form method="POST" action="{{ route('admin.conversions.fail', $conversion->id) }}">
                    @csrf
                    <input type="text" name="failure_reason" class="form-input mb-2" placeholder="Failure reason (required)" required>
                    <input type="hidden" name="admin_note" value="">
                    <button type="submit" class="btn-action btn-fail" onclick="return confirm('Mark conversion #{{ $conversion->id }} as failed?')">
                        <i class="bi bi-x-lg me-1"></i>Fail
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <a href="{{ route('admin.conversions.index') }}" style="color:var(--kx-muted);font-size:.8rem;text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i>Back to conversions
    </a>
</div>
@endsection
