@extends('layouts.app')

@section('title', 'Service Status · KayXchange')

@push('styles')
<style>
.kx-status-page{max-width:1040px;margin:auto;padding:clamp(24px,5vw,58px) 16px 84px;color:#e7f2ea}.kx-status-hero{padding:clamp(22px,4vw,38px);border:1px solid rgba(80,210,120,.18);border-radius:24px;background:radial-gradient(ellipse at 90% 0%,rgba(0,204,80,.13),transparent 55%),#101a17;box-shadow:0 22px 70px rgba(0,0,0,.18)}.kx-status-kicker{font-size:.72rem;font-weight:800;letter-spacing:.15em;text-transform:uppercase;color:#54d77e}.kx-status-title{font-size:clamp(1.7rem,4vw,2.5rem);font-weight:850;color:#fff;margin:.55rem 0}.kx-status-copy{max-width:650px;color:#9caf9f;line-height:1.7;margin:0}.kx-status-summary{display:inline-flex;align-items:center;gap:9px;padding:8px 13px;margin-top:20px;border-radius:99px;font-size:.8rem;font-weight:750;background:{{ $maintenance ? 'rgba(245,158,11,.13)' : 'rgba(0,204,80,.12)' }};color:{{ $maintenance ? '#fbbf24' : '#58df83' }};border:1px solid currentColor}.kx-status-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,285px),1fr));gap:14px;margin-top:22px}.kx-status-item{display:flex;align-items:flex-start;gap:14px;padding:19px;border:1px solid rgba(255,255,255,.07);border-radius:18px;background:rgba(18,29,25,.9)}.kx-status-icon{width:43px;height:43px;display:grid;place-items:center;border-radius:13px;background:rgba(0,204,80,.11);color:#54d77e;font-size:1.1rem;flex:none}.kx-status-item.is-paused .kx-status-icon{background:rgba(148,163,184,.12);color:#aab6ad}.kx-status-item.is-maintenance .kx-status-icon{background:rgba(245,158,11,.12);color:#fbbf24}.kx-status-details{flex:1;min-width:0}.kx-status-name{font-weight:750;color:#f4f8f5}.kx-status-note{font-size:.78rem;color:#91a196;margin-top:4px}.kx-status-pill{font-size:.69rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#58df83;white-space:nowrap}.is-paused .kx-status-pill{color:#aab6ad}.is-maintenance .kx-status-pill{color:#fbbf24}.kx-status-foot{margin-top:22px;color:#85958a;font-size:.78rem;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}.kx-status-help{color:#62de87;text-decoration:none;font-weight:700}.kx-status-help:hover{color:#9affb4}
</style>
@endpush

@section('content')
<main class="kx-status-page">
    <header class="kx-status-hero">
        <div class="kx-status-kicker"><i class="bi bi-activity me-1"></i> KayXchange status</div>
        <h1 class="kx-status-title">Service availability</h1>
        <p class="kx-status-copy">Check which platform services are currently enabled. A service marked enabled may still take time to complete when a payment provider or blockchain is confirming a transaction.</p>
        <div class="kx-status-summary"><i class="bi {{ $maintenance ? 'bi-tools' : 'bi-check-circle-fill' }}"></i>{{ $maintenance ? 'Platform maintenance is active' : 'Platform is available' }}</div>
    </header>
    <section class="kx-status-grid" aria-label="Platform service availability">
        @foreach($services as $service)
            @php($stateClass = $maintenance ? 'is-maintenance' : ($service['available'] ? '' : 'is-paused'))
            <article class="kx-status-item {{ $stateClass }}">
                <div class="kx-status-icon"><i class="bi {{ $service['icon'] }}"></i></div>
                <div class="kx-status-details"><div class="d-flex align-items-center justify-content-between gap-2"><div class="kx-status-name">{{ $service['name'] }}</div><span class="kx-status-pill">{{ $service['state'] }}</span></div><div class="kx-status-note">{{ $service['note'] }}</div></div>
            </article>
        @endforeach
    </section>
    <footer class="kx-status-foot"><span>Feature availability checked {{ $updatedAt->format('d M Y · H:i') }} WAT</span><a class="kx-status-help" href="{{ route('support.chat') }}">Need help? Contact support <i class="bi bi-arrow-up-right"></i></a></footer>
</main>
@endsection
