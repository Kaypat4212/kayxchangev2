@extends('layouts.app')
@section('title', 'USD Wallet')
@section('content')
<style>
.usd-page{max-width:1050px;margin:0 auto;padding:28px 16px 60px;color:#eaf1f8}.usd-hero{border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:30px;background:radial-gradient(circle at 90% 10%,rgba(56,189,248,.18),transparent 35%),linear-gradient(130deg,#101a27,#14202d)}.usd-card{background:#131b25;border:1px solid rgba(255,255,255,.1);border-radius:18px;padding:22px}.usd-balance{font-size:clamp(2.4rem,7vw,4rem);font-weight:850;letter-spacing:-.04em}.usd-muted{color:#a0adbb}.usd-rate{font-size:1.2rem;font-weight:800;color:#7dd3fc}.usd-actions{display:flex;gap:10px;flex-wrap:wrap}.usd-actions a{border-radius:12px;padding:11px 15px;font-weight:700;text-decoration:none}
</style>
<main class="usd-page">
 <header class="usd-hero mb-4"><div class="small text-info fw-bold text-uppercase">Fiat wallet</div><h1 class="h2 fw-bold mt-2">USD wallet</h1><p class="usd-muted mb-4">View your US dollar wallet balance and the platform’s USD to naira rates.</p><div class="usd-balance">${{ number_format((float)$wallet->balance, 2) }} <span class="fs-5 text-info">USD</span></div><div class="usd-muted">Wallet balance</div></header>
 <section class="row g-3 mb-4"><div class="col-md-6"><article class="usd-card h-100"><div class="usd-muted">Platform buys USD at</div><div class="usd-rate mt-1">{{ $buyRate > 0 ? '₦'.number_format($buyRate,2).' / USD' : 'Rate not set' }}</div></article></div><div class="col-md-6"><article class="usd-card h-100"><div class="usd-muted">Platform sells USD at</div><div class="usd-rate mt-1">{{ $sellRate > 0 ? '₦'.number_format($sellRate,2).' / USD' : 'Rate not set' }}</div></article></div></section>
 <div class="usd-card mb-4"><p class="usd-muted mb-0">Rates are managed by KayXchange. Use the shown buy/sell rate for USD wallet transactions; crypto prices are listed separately in your crypto wallets.</p></div>
 <div class="usd-actions"><a class="btn btn-info text-dark" href="{{ route('wallet.crypto') }}"><i class="bi bi-currency-bitcoin me-1"></i>Crypto wallets</a><a class="btn btn-outline-light" href="{{ route('wallet.index') }}">Wallet overview</a><a class="btn btn-outline-light" href="{{ route('wallet.history') }}">Wallet history</a></div>
</main>
@endsection
