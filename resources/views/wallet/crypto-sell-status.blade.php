@extends('layouts.app')
@section('title', 'Crypto Sale Status')
@push('styles')
<style>
.css-wrap{max-width:780px;margin:auto;padding:28px 16px 70px;color:#eef5f0}.css-card{border:1px solid rgba(255,255,255,.09);border-radius:22px;background:linear-gradient(145deg,#111b25,#111a18);padding:clamp(20px,4vw,34px)}.css-muted{color:#9eada5;font-size:.84rem}.css-amount{font-size:clamp(2rem,7vw,3.2rem);font-weight:850;color:#74e89d}.css-row{display:flex;justify-content:space-between;gap:14px;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.07);font-size:.82rem;color:#aab8ae}.css-row strong{color:#f0f6f2;text-align:right}.css-state{display:inline-flex;padding:7px 11px;border-radius:99px;background:rgba(65,160,255,.12);color:#9ed0ff;font-size:.76rem;font-weight:800;text-transform:capitalize}.css-state.completed{background:rgba(34,197,94,.14);color:#80eba1}.css-state.failed{background:rgba(239,68,68,.13);color:#ffa0a0}.css-actions{display:flex;gap:9px;flex-wrap:wrap}
</style>
@endpush
@section('content')
<main class="css-wrap">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <section class="css-card">
        <div class="text-success small fw-bold text-uppercase">NOWPayments Custody sale</div>
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mt-2"><h1 class="h3 fw-bold mb-0">{{ $conversion->from_coin }} sale</h1><span class="css-state {{ $conversion->status }}">{{ $conversion->status }}</span></div>
        <p class="css-muted mt-2">Reference #{{ $conversion->id }} · {{ $conversion->created_at?->format('d M Y, H:i') }}</p>
        @if($conversion->status === 'completed')
            <div class="css-amount mt-4">₦{{ number_format((float) $conversion->to_amount, 2) }}</div>
            <div class="css-muted">Credited to your Naira wallet</div>
        @elseif($conversion->status === 'failed')
            <div class="alert alert-danger mt-4 mb-0">The provider did not complete this conversion. Your {{ $conversion->from_coin }} wallet balance has been restored.</div>
        @else
            <div class="alert alert-info mt-4">NOWPayments is confirming the conversion. Your crypto is reserved during processing; your Naira wallet will only be credited after provider success.</div>
        @endif
        <div class="mt-4">
            <div class="css-row"><span>Crypto sold</span><strong>{{ number_format((float) $conversion->from_amount, 8) }} {{ $conversion->from_coin }}</strong></div>
            <div class="css-row"><span>Estimated Naira payout</span><strong>₦{{ number_format((float) data_get($conversion->cryptomus_response, 'estimated_quoted_naira', $conversion->to_amount), 2) }}</strong></div>
            <div class="css-row"><span>Provider status</span><strong>{{ $conversion->provider_status ?: 'Awaiting confirmation' }}</strong></div>
            @if($conversion->failure_reason)<div class="css-row"><span>Update</span><strong>{{ $conversion->failure_reason }}</strong></div>@endif
        </div>
        <div class="css-actions mt-4"><a class="btn btn-success" href="{{ route('wallet.crypto.sell.show', $conversion) }}"><i class="bi bi-arrow-repeat me-1"></i>Refresh status</a><a class="btn btn-outline-light" href="{{ route('wallet.crypto') }}">Crypto portfolio</a><a class="btn btn-outline-info" href="{{ route('wallet.naira-activity') }}">Naira activity</a></div>
    </section>
</main>
@endsection
