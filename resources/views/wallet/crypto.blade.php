@extends('layouts.app')
@section('title', 'Crypto Portfolio')

@push('styles')
<style>
.cp-page{--cp-ink:#f4f7fb;--cp-muted:#9eacbc;--cp-panel:#111a24;--cp-line:rgba(255,255,255,.09);max-width:1160px;margin:0 auto;padding:28px 16px 70px;color:var(--cp-ink)}
.cp-hero{position:relative;overflow:hidden;border:1px solid rgba(91,230,145,.22);border-radius:28px;padding:clamp(22px,4vw,40px);background:radial-gradient(ellipse at 84% 8%,rgba(12,190,105,.23),transparent 34%),radial-gradient(ellipse at 4% 100%,rgba(39,114,235,.16),transparent 38%),linear-gradient(135deg,#101a27,#101c19 72%)}
.cp-hero:after{content:"";position:absolute;width:260px;height:260px;border:1px solid rgba(113,245,171,.09);border-radius:50%;right:-80px;top:-150px;box-shadow:0 0 0 24px rgba(113,245,171,.025),0 0 0 50px rgba(113,245,171,.02);pointer-events:none}
.cp-eyebrow{color:#79e9a7;letter-spacing:.13em;text-transform:uppercase;font-size:.72rem;font-weight:800}.cp-total{font-size:clamp(2rem,6vw,3.5rem);line-height:1.06;font-weight:850;letter-spacing:-.045em}.cp-sub{color:var(--cp-muted)}
.cp-pill{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--cp-line);border-radius:999px;background:rgba(255,255,255,.045);padding:8px 12px;color:#dce7f1;font-size:.78rem}
.cp-card{height:100%;position:relative;overflow:hidden;border:1px solid var(--cp-line);border-radius:22px;padding:20px;background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.018) 54%),var(--cp-panel);box-shadow:0 18px 50px rgba(0,0,0,.14);transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease}
.cp-card:hover{transform:translateY(-3px);border-color:rgba(90,224,144,.28);box-shadow:0 22px 55px rgba(0,0,0,.22)}
.cp-card:before{content:"";position:absolute;width:132px;height:132px;border-radius:50%;right:-72px;top:-76px;background:var(--coin-glow,rgba(27,204,113,.13));filter:blur(3px);pointer-events:none}
.cp-coin{width:50px;height:50px;display:grid;place-items:center;flex:0 0 auto;border-radius:50%;background:var(--coin-bg,#143c2b);border:1px solid rgba(255,255,255,.12);box-shadow:0 8px 22px rgba(0,0,0,.25),inset 0 1px rgba(255,255,255,.14);color:var(--coin-color,#75eea3);font-weight:850;font-size:1.25rem;overflow:hidden}
.cp-coin img{width:100%;height:100%;padding:10px;object-fit:contain}.cp-coin[data-coin="BTC"]{--coin-bg:#3e2b16;--coin-color:#f7931a;--coin-glow:rgba(247,147,26,.17)}.cp-coin[data-coin="ETH"]{--coin-bg:#202c46;--coin-color:#9db4fa;--coin-glow:rgba(100,135,244,.16)}.cp-coin[data-coin="USDT"]{--coin-bg:#123e38;--coin-color:#4dd6b0;--coin-glow:rgba(77,214,176,.16)}.cp-coin[data-coin="USDC"]{--coin-bg:#172f56;--coin-color:#66a6ff;--coin-glow:rgba(70,139,255,.16)}.cp-coin[data-coin="SOL"]{--coin-bg:#30254b;--coin-color:#c6a9ff;--coin-glow:rgba(168,115,255,.16)}.cp-coin[data-coin="LTC"]{--coin-bg:#25303c;--coin-color:#b7c5d5;--coin-glow:rgba(176,195,215,.13)}.cp-coin[data-coin="BNB"]{--coin-bg:#423614;--coin-color:#ffcf40;--coin-glow:rgba(255,201,49,.15)}.cp-coin[data-coin="XRP"]{--coin-bg:#26303a;--coin-color:#d7e0e9}.cp-coin[data-coin="DOGE"]{--coin-bg:#3a3519;--coin-color:#e1cf66}.cp-coin[data-coin="TRX"]{--coin-bg:#401e25;--coin-color:#ff6d7e}.cp-coin[data-coin="ADA"]{--coin-bg:#1a2e56;--coin-color:#73a6ff}.cp-coin[data-coin="TON"]{--coin-bg:#182b51;--coin-color:#83b6ff;font-size:.8rem}
.cp-name{font-weight:800;font-size:1rem}.cp-muted{color:var(--cp-muted);font-size:.82rem}.cp-balance{font-size:1.25rem;font-weight:800;letter-spacing:-.02em;word-break:break-word}.cp-value{font-weight:700;color:#e3edf5}.cp-change{font-size:.76rem;font-weight:700}.cp-change-up{color:#65dfa0}.cp-change-down{color:#ff8995}.cp-change-flat{color:#aab7c4}.cp-divider{height:1px;background:var(--cp-line);margin:16px 0}.cp-rate{font-size:.78rem;color:#bdc8d3}.cp-address{background:rgba(0,0,0,.2);border:1px solid var(--cp-line);border-radius:11px;padding:9px 11px;font-size:.73rem;word-break:break-all;color:#b8c6d4}.cp-actions{display:flex;gap:8px;flex-wrap:wrap}.cp-actions .btn{border-radius:11px;font-size:.82rem}.cp-section-title{font-size:1.05rem;font-weight:800}.cp-empty{border:1px dashed rgba(255,255,255,.18);border-radius:22px;background:rgba(255,255,255,.025);padding:42px 20px;color:var(--cp-muted)}
@media(max-width:575.98px){.cp-page{padding:16px 12px 56px}.cp-hero{border-radius:22px}.cp-card{padding:17px;border-radius:19px}.cp-actions .btn{flex:1 1 auto}}
@media(prefers-reduced-motion:reduce){.cp-card{transition:none}.cp-card:hover{transform:none}}
</style>
@endpush

@section('content')
<main class="cp-page">
    <section class="cp-hero mb-4">
        <div class="position-relative" style="z-index:1">
            <div class="cp-eyebrow mb-2">Your assets at a glance</div>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-4">
                <div>
                    <h1 class="h2 fw-bold mb-3">Crypto portfolio</h1>
                    <div class="cp-total">@if($portfolioUsd !== null)${{ number_format($portfolioUsd, 2) }}@else Value unavailable @endif</div>
                    <div class="cp-sub mt-2">@if($portfolioNgn !== null)≈ ₦{{ number_format($portfolioNgn, 2) }} <span class="mx-1">·</span> Converted using the platform USD sell rate @else NGN estimate unavailable @endif</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-success rounded-pill px-3" href="{{ route('wallet.deposit') }}"><i class="bi bi-plus-circle me-1"></i> Deposit</a>
                    @if(\App\Models\AdminSetting::getSetting('nowpayments_wallet_sell_enabled', '0') === '1')<a class="btn btn-outline-success rounded-pill px-3" href="{{ route('wallet.crypto.sell') }}"><i class="bi bi-cash-coin me-1"></i> Sell crypto</a>@endif
                    <a class="btn btn-outline-light rounded-pill px-3" href="{{ route('wallet.crypto.statement') }}"><i class="bi bi-download me-1"></i> Download statement</a>
                    <a class="btn btn-outline-success rounded-pill px-3" href="{{ route('price-alerts.index') }}"><i class="bi bi-bell me-1"></i> Price alerts</a>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <span class="cp-pill"><i class="bi bi-wallet2 text-success"></i>{{ $wallets->count() }} active assets</span>
                <span class="cp-pill"><i class="bi bi-clock-history text-info"></i>{{ number_format($completedDeposits->count()) }} confirmed deposits</span>
                <span class="cp-pill"><i class="bi bi-arrow-down-left-circle text-success"></i>Received value ${{ number_format($depositedValueUsd, 2) }}</span>
                <span class="cp-pill"><i class="bi bi-activity {{ $marketStale ? 'text-warning' : 'text-success' }}"></i>{{ $marketStale ? 'Market prices may be delayed' : 'Market prices live' }}@if($marketUpdatedAt) · {{ \Carbon\Carbon::parse($marketUpdatedAt)->diffForHumans() }}@endif</span>
            </div>
            @if($portfolioUsd === null)<div class="small text-warning mt-3"><i class="bi bi-info-circle me-1"></i>Some held assets do not have a current market quote, so the portfolio total is hidden to avoid an incomplete figure.</div>@endif
        </div>
    </section>

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
        <div><h2 class="cp-section-title mb-1">Your crypto balances</h2><div class="cp-muted">Current holdings, market value and KayXchange trade rates.</div></div>
        <a class="cp-muted text-decoration-none" href="{{ route('wallet.history') }}"><i class="bi bi-clock-history me-1"></i>Transaction history</a>
    </div>

    @if($wallets->isEmpty())
        <div class="cp-empty text-center"><div class="cp-coin mx-auto mb-3" data-coin="BTC">₿</div><h3 class="h5 text-light">No crypto wallets available</h3><p class="mb-3">Your active crypto wallets will appear here.</p><a class="btn btn-success rounded-pill px-4" href="{{ route('wallet.deposit') }}">Explore deposits</a></div>
    @else
        <div class="row g-3">
            @foreach($wallets as $wallet)
                @php
                    $currency = strtoupper((string) $wallet->currency);
                    $rate = $rates[$currency] ?? ['buy_rate' => null, 'sell_rate' => null];
                    $marketPrice = $marketPrices[$currency] ?? null;
                    $coinMeta = \App\Services\ExchangeRateService::SUPPORTED_COINS[$currency] ?? null;
                    $networks = ['BTC'=>['BTC'],'ETH'=>['ERC20'],'LTC'=>['LTC'],'USDT'=>['TRC20','ERC20','BEP20'],'USDC'=>['ERC20','BEP20'],'BNB'=>['BEP20'],'SOL'=>['SOL']][$currency] ?? [];
                    $suggestedNetwork = in_array(strtoupper((string) $wallet->network), $networks, true) ? strtoupper((string) $wallet->network) : ($networks[0] ?? '');
                    $change = $marketPrice['change_24h'] ?? null;
                @endphp
                <div class="col-md-6 col-xl-4">
                    <article class="cp-card">
                        <div class="d-flex align-items-center gap-3 position-relative" style="z-index:1">
                            <div class="cp-coin" data-coin="{{ $currency }}">
                                @if(isset($logoFiles[$currency]))<img src="{{ asset('Assests/crypto-icons/'.$logoFiles[$currency]) }}" alt="{{ $currency }} logo">@else{{ $logoGlyphs[$currency] ?? mb_substr($currency, 0, 1) }}@endif
                            </div>
                            <div class="flex-grow-1 min-w-0"><div class="cp-name">{{ $coinMeta['name'] ?? $currency }} <span class="cp-muted">{{ $currency }}</span></div><div class="cp-muted">{{ $wallet->network ?: 'Network shown at deposit' }}</div></div>
                            @if(is_numeric($change))<span class="cp-change {{ $change > 0 ? 'cp-change-up' : ($change < 0 ? 'cp-change-down' : 'cp-change-flat') }}">{{ $change > 0 ? '+' : '' }}{{ number_format((float) $change, 2) }}%</span>@endif
                        </div>
                        <div class="mt-4"><div class="cp-muted mb-1">Available balance</div><div class="cp-balance">{{ number_format((float) $wallet->balance, 8) }} <span class="fs-6">{{ $currency }}</span></div>
                            <div class="d-flex justify-content-between align-items-center gap-2 mt-2"><span class="cp-muted">Market value</span><span class="cp-value">@if($marketPrice)${{ number_format((float) $wallet->balance * $marketPrice['usd'], 2) }}@else —@endif</span></div>
                            <div class="cp-muted small mt-1">@if($marketPrice)1 {{ $currency }} ≈ ${{ number_format($marketPrice['usd'], 6) }}@if(!$marketPrice['live']) · cached/fallback quote @endif @else No market quote available @endif</div>
                        </div>
                        <div class="cp-divider"></div>
                        <div class="cp-rate">KayXchange rates · Buy ₦{{ is_numeric($rate['buy_rate'] ?? null) && $rate['buy_rate'] > 0 ? number_format((float) $rate['buy_rate'], 2) : '—' }} <span class="mx-1">·</span> Sell ₦{{ is_numeric($rate['sell_rate'] ?? null) && $rate['sell_rate'] > 0 ? number_format((float) $rate['sell_rate'], 2) : '—' }}</div>
                        <div class="cp-muted small mt-2">Confirmed received: <strong class="text-light">{{ number_format((float) ($depositedTotals[$currency] ?? 0), 8) }} {{ $currency }}</strong></div>
                        @if($currency === 'USDT')<div class="cp-muted small mt-1">USDT balance is shared across supported networks.</div>@endif
                        @if($wallet->address)<div class="cp-address mt-3">{{ $wallet->address }}</div>@endif
                        <div class="cp-actions mt-3"><a class="btn btn-success" href="{{ route('wallet.deposit', ['currency' => $currency, 'network' => $suggestedNetwork]) }}"><i class="bi bi-download me-1"></i>Deposit</a>
                            @if(\App\Models\AdminSetting::getSetting('nowpayments_crypto_withdrawals_enabled', '0') === '1')<a class="btn btn-outline-success" href="{{ route('wallet.withdraw', ['wallet_id' => $wallet->id, 'network' => $suggestedNetwork]) }}"><i class="bi bi-send me-1"></i>Withdraw</a>@else<button type="button" class="btn btn-outline-secondary" disabled title="Crypto withdrawals are currently disabled">Withdraw paused</button>@endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
    <div class="cp-actions mt-4"><a class="btn btn-outline-info" href="{{ route('wallet.usd') }}">USD wallet</a><a class="btn btn-outline-light" href="{{ route('wallet.index') }}">Wallet overview</a></div>
</main>
@endsection
