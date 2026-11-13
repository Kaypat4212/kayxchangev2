@extends('adminnavlayout')

@section('title', 'USD Wallet Rates')

@section('content')
<main class="container py-4" style="max-width:900px">
    <div class="card bg-dark text-light border-secondary shadow-lg">
        <div class="card-body p-4 p-lg-5">
            <div class="small text-success text-uppercase fw-bold">Wallet pricing</div>
            <h1 class="h3 fw-bold mt-2">USD wallet rates</h1>
            <p class="text-secondary">Set independent NGN prices for USD wallet transactions. These rates do not change crypto trade rates.</p>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
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
