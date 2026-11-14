@extends('adminnavlayout')

@section('title', 'USD Wallet Rates')

@section('content')
<main class="container py-4" style="max-width:900px">
    <div class="card bg-dark text-light border-secondary shadow-lg">
        <div class="card-body p-4 p-lg-5">
            <div class="small text-success text-uppercase fw-bold">Wallet pricing</div>
            <h1 class="h3 fw-bold mt-2">USD wallet rates</h1>
            <p class="text-secondary">Manage the USD to Naira rates shown to users. You can copy the active platform USDT rates below, then adjust either rate manually.</p>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap p-3 mb-4 rounded-3" style="background:#101713;border:1px solid rgba(255,255,255,.08)">
                <div><div class="small text-secondary text-uppercase fw-bold">Current platform USDT rates</div><div class="mt-1">Buy <strong class="text-success">₦{{ $usdtBuyRate ? number_format((float)$usdtBuyRate,2) : '—' }}</strong><span class="mx-2 text-secondary">·</span>Sell <strong class="text-success">₦{{ $usdtSellRate ? number_format((float)$usdtSellRate,2) : '—' }}</strong></div></div>
                <form method="POST" action="{{ route('admin.wallet-rates.sync-usdt') }}" onsubmit="return confirm('Copy the current platform USDT buy and sell rates to the USD wallet rates?')">@csrf<button class="btn btn-outline-success" type="submit" {{ !$usdtBuyRate || !$usdtSellRate ? 'disabled' : '' }}><i class="bi bi-arrow-repeat me-1"></i>Update from platform USDT</button></form>
            </div>
            <form method="POST" action="{{ route('admin.wallet-rates.update') }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="buy_rate">Platform buy rate <span class="text-secondary">(NGN per USD)</span></label><input class="form-control bg-black text-light border-secondary" type="number" id="buy_rate" name="buy_rate" min="1" step="0.01" value="{{ old('buy_rate', $buyRate) }}" required><div class="form-text">Rate used when the platform buys USD from a customer.</div></div>
                    <div class="col-md-6"><label class="form-label" for="sell_rate">Platform sell rate <span class="text-secondary">(NGN per USD)</span></label><input class="form-control bg-black text-light border-secondary" type="number" id="sell_rate" name="sell_rate" min="1" step="0.01" value="{{ old('sell_rate', $sellRate) }}" required><div class="form-text">Rate used when the platform sells USD to a customer.</div></div>
                </div>
                <button class="btn btn-success mt-4" type="submit"><i class="bi bi-check2-circle me-1"></i>Save USD rates</button>
            </form>
        </div>
    </div>
</main>
@endsection
