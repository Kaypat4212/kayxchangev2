@extends('layout')

@section('title', 'Trading Guides & Resources — KayXchange')

@push('styles')
<style>
.kx-resources{max-width:1160px;margin:0 auto;padding:34px 16px 70px;color:#e8f0ea}.kr-hero{position:relative;overflow:hidden;padding:clamp(24px,5vw,48px);border:1px solid rgba(77,210,128,.2);border-radius:25px;background:radial-gradient(circle at 88% 12%,rgba(34,197,94,.15),transparent 34%),linear-gradient(135deg,#101c26,#12251b);margin-bottom:24px}.kr-kicker{font-size:.72rem;font-weight:800;letter-spacing:.15em;text-transform:uppercase;color:#75dfa0}.kr-hero h1{font-size:clamp(1.8rem,4vw,2.8rem);font-weight:850;letter-spacing:-.04em;margin:9px 0}.kr-hero p{max-width:700px;color:#aabbb0;margin:0;line-height:1.7}.kr-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.kr-card{overflow:hidden;border:1px solid rgba(255,255,255,.085);border-radius:18px;background:#121a22}.kr-card-body{padding:20px}.kr-head{display:flex;align-items:center;gap:12px}.kr-icon{width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:rgba(45,190,94,.12);color:#72e49b;font-size:1.2rem}.kr-head h2{margin:0;color:#f3f8f4;font-size:1.08rem;font-weight:800}.kr-head p{margin:3px 0 0;color:#98aa9d;font-size:.77rem}.kr-card ol{display:grid;gap:9px;padding:0;margin:19px 0;list-style:none;counter-reset:kr}.kr-card li{display:flex;gap:10px;align-items:flex-start;color:#c6d3c9;font-size:.82rem;line-height:1.55;counter-increment:kr}.kr-card li:before{content:counter(kr);flex:0 0 22px;width:22px;height:22px;display:grid;place-items:center;border-radius:50%;background:rgba(45,190,94,.14);color:#80e8a1;font-size:.7rem;font-weight:800}.kr-video{aspect-ratio:16/9;width:100%;border:0;border-radius:12px;background:#080d11}.kr-video-wrap{margin:0 0 16px}.kr-video-label{margin:0 0 7px;color:#9cafa2;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.kr-no-video{padding:13px;border:1px dashed rgba(255,255,255,.14);border-radius:12px;color:#8d9e93;font-size:.77rem}.kr-actions{display:flex;gap:8px;flex-wrap:wrap}.kr-actions a{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 13px;border-radius:10px;text-decoration:none;background:#1b2b22;border:1px solid rgba(76,210,128,.2);color:#a8f0bd;font-size:.78rem;font-weight:750}.kr-actions a.primary{background:#16a34a;color:white;border-color:#16a34a}.kr-safety{margin-top:18px;padding:17px 19px;border-radius:16px;border:1px solid rgba(251,191,36,.18);background:rgba(251,191,36,.045);color:#c5c7ae;font-size:.8rem;line-height:1.65}.kr-safety b{color:#f2d78b}.kr-safety a{color:#95e7ac}
@media(max-width:760px){.kx-resources{padding:20px 12px 48px}.kr-layout{grid-template-columns:1fr}.kr-card-body{padding:17px}.kr-hero{border-radius:20px}}
body.light-mode .kx-resources{color:#1d3124}body.light-mode .kr-hero{background:radial-gradient(circle at 88% 12%,rgba(34,197,94,.12),transparent 34%),linear-gradient(135deg,#fff,#f1faf3);border-color:#d5e8da}body.light-mode .kr-hero h1,body.light-mode .kr-head h2{color:#1d3124}body.light-mode .kr-hero p,body.light-mode .kr-head p,body.light-mode .kr-card li{color:#5f7466}body.light-mode .kr-card{background:#fff;border-color:#dce9df;box-shadow:0 10px 28px rgba(30,70,40,.05)}body.light-mode .kr-video{background:#e8eee9}body.light-mode .kr-no-video{color:#6f8274;border-color:#d8e4dc}body.light-mode .kr-safety{background:#fff9e8;color:#6f6138}body.light-mode .kr-safety b{color:#5e4a14}
</style>
@endpush

@section('content')
<main class="kx-resources">
    <header class="kr-hero">
        <div class="kr-kicker">KayXchange learning centre</div>
        <h1>Trade with clarity.</h1>
        <p>Short, practical walkthroughs for buying, selling, depositing, and withdrawing. Each guide explains what to check before you confirm and where to follow the transaction afterward.</p>
    </header>

    <section class="kr-layout" aria-label="Trading guides">
        @foreach($guides as $key => $guide)
            <article class="kr-card">
                <div class="kr-card-body">
                    <div class="kr-head"><span class="kr-icon"><i class="bi {{ $guide['icon'] }}"></i></span><div><h2>{{ $guide['title'] }}</h2><p>Step-by-step platform guide</p></div></div>
                    @if($guide['video'])
                        <div class="kr-video-wrap mt-3"><div class="kr-video-label"><i class="bi bi-play-circle me-1"></i>Video walkthrough</div><iframe class="kr-video" src="{{ $guide['video'] }}" title="{{ $guide['title'] }} video guide" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>
                    @else
                        <div class="kr-no-video mt-3"><i class="bi bi-camera-video me-1"></i>Video walkthrough will appear here when added by an admin.</div>
                    @endif
                    <ol>@foreach($guide['steps'] as $step)<li>{{ $step }}</li>@endforeach</ol>
                    <div class="kr-actions"><a class="primary" href="{{ $guide['url'] }}"><i class="bi bi-arrow-right-circle"></i>Open {{ strtolower($guide['title']) }}</a>@if($key === 'sell')<a href="{{ route('wallet.usd', ['withdraw' => 1]) }}"><i class="bi bi-wallet2"></i>Sell from crypto wallet</a>@endif<a href="{{ route('transactions.history') }}"><i class="bi bi-clock-history"></i>Transaction history</a></div>
                </div>
            </article>
        @endforeach
    </section>

    <aside class="kr-safety"><b><i class="bi bi-shield-check me-1"></i>Before sending funds:</b> confirm the coin, network, amount, and destination shown on your current order. KayXchange support will never ask you to reveal your password, transaction PIN, seed phrase, or one-time code. Need help? Visit <a href="{{ route('support.chat') }}">Support</a> or review the <a href="{{ route('faqs') }}">FAQs</a>.</aside>
</main>
@endsection
