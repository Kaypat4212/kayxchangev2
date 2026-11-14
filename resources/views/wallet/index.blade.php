@extends('layouts.app')
@section('title', 'My Wallet')
@section('content')
<style>
.kw-page{max-width:1180px;margin:0 auto;padding:22px 16px 64px;color:#eaf1f8}.kw-hero{position:relative;overflow:hidden;border:1px solid rgba(255,255,255,.1);border-radius:26px;padding:clamp(22px,4vw,38px);background:radial-gradient(circle at 84% 6%,rgba(0,204,0,.19),transparent 30%),radial-gradient(circle at 8% 100%,rgba(56,189,248,.12),transparent 28%),linear-gradient(135deg,#101821,#111b17 70%,#101821);box-shadow:0 22px 60px rgba(0,0,0,.22)}.kw-kicker{color:#72ed83;text-transform:uppercase;font-size:.72rem;letter-spacing:.13em;font-weight:800}.kw-title{font-weight:850;letter-spacing:-.04em;color:#f8fafc}.kw-sub{color:#a4b1bf;max-width:680px}.kw-total{font-size:clamp(2.3rem,7vw,4rem);font-weight:850;letter-spacing:-.055em;line-height:1}.kw-total small{font-size:.35em;letter-spacing:0;color:#9caaba;font-weight:600}.kw-fiat{color:#aab8c6;font-size:.95rem}.kw-actions{display:flex;gap:10px;flex-wrap:wrap}.kw-action{display:inline-flex;gap:9px;align-items:center;justify-content:center;border-radius:12px;padding:11px 15px;font-weight:750;text-decoration:none!important;border:1px solid rgba(255,255,255,.14);color:#edf4f7;background:rgba(255,255,255,.055)}.kw-action:hover{color:#fff;background:rgba(255,255,255,.1)}.kw-action.primary{background:#00bb32;border-color:#00bb32;color:#06110a}.kw-section-title{font-size:1.15rem;font-weight:800;color:#f5f8fa}.kw-muted{color:#95a3b3}.kw-card{height:100%;min-height:220px;background:linear-gradient(155deg,#17212b,#111820);border:1px solid rgba(255,255,255,.09);border-radius:19px;padding:19px;transition:transform .18s ease,border-color .18s ease}.kw-card:hover{transform:translateY(-2px);border-color:rgba(0,204,0,.32)}.kw-coin-icon{width:44px;height:44px;border-radius:14px;background:rgba(0,204,0,.11);display:grid;place-items:center;color:#6fec81;font-size:1.25rem;font-weight:850}.kw-symbol{font-size:1rem;font-weight:850}.kw-asset-name{font-size:.78rem;color:#92a0b0}.kw-balance{font-size:1.36rem;font-weight:800;letter-spacing:-.025em;margin-top:23px}.kw-value{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-top:6px;font-size:.86rem;color:#a8b5c3}.kw-price{font-size:.8rem;color:#93a3b1;margin-top:12px}.kw-change{font-size:.76rem;font-weight:750}.kw-up{color:#58e18a}.kw-down{color:#ff8585}.kw-asset-actions{display:flex;gap:8px;margin-top:16px}.kw-asset-actions a{font-size:.78rem;padding:7px 11px;border:1px solid rgba(255,255,255,.14);color:#dce7ef;border-radius:9px;text-decoration:none}.kw-asset-actions a:hover{border-color:#00cc00;color:#8cf29b}.kw-fiat-card{background:linear-gradient(150deg,rgba(56,189,248,.11),#121b24 58%);border-color:rgba(56,189,248,.2)}.kw-ngn-card{background:linear-gradient(150deg,rgba(0,204,0,.1),#121b24 60%);border-color:rgba(0,204,0,.18)}.kw-status{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:7px 11px;background:rgba(255,255,255,.07);color:#b8c5d1;font-size:.75rem;font-weight:700}.kw-dot{width:7px;height:7px;border-radius:99px;background:#667085}.kw-dot.live{background:#41dc79;box-shadow:0 0 10px #41dc79}.kw-panel{background:#121a23;border:1px solid rgba(255,255,255,.09);border-radius:19px;overflow:hidden}.kw-panel-head{padding:18px 20px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}.kw-deposit{padding:17px 20px;border-bottom:1px solid rgba(255,255,255,.06)}.kw-deposit:last-child{border-bottom:0}.kw-deposit-grid{display:grid;grid-template-columns:minmax(0,1.3fr) repeat(3,minmax(100px,1fr)) auto;gap:13px;align-items:center}.kw-label{font-size:.68rem;color:#8694a3;text-transform:uppercase;letter-spacing:.08em}.kw-deposit-value{font-weight:750;color:#eaf1f8}.kw-status-pill{display:inline-block;border-radius:999px;padding:5px 9px;font-size:.7rem;font-weight:750;background:rgba(255,255,255,.08);color:#bac6d2}.kw-status-pill.completed{background:rgba(0,204,0,.12);color:#7bea8c}.kw-status-pill.pending{background:rgba(245,158,11,.12);color:#f5cf7b}.kw-empty{padding:40px 18px;text-align:center;color:#99a7b5}.kw-empty-icon{width:56px;height:56px;border-radius:17px;margin:0 auto 14px;display:grid;place-items:center;background:rgba(255,255,255,.06);color:#8ea0b2;font-size:1.5rem}.kw-link{color:#7ee68e;text-decoration:none;font-weight:700}.kw-link:hover{color:#a3f1af}.kw-note{font-size:.75rem;color:#8291a0}.kw-progress{height:3px;border-radius:3px;background:rgba(255,255,255,.06);overflow:hidden}.kw-progress span{display:block;height:100%;width:35%;background:#00cc00;animation:kwload 1.1s ease-in-out infinite alternate}@keyframes kwload{to{transform:translateX(180%)}}@media(max-width:850px){.kw-deposit-grid{grid-template-columns:1fr 1fr}.kw-deposit-main{grid-column:1/-1}}@media(max-width:540px){.kw-page{padding-left:12px;padding-right:12px}.kw-hero{border-radius:20px}.kw-deposit-grid{grid-template-columns:1fr 1fr}.kw-deposit-main{grid-column:1/-1}.kw-actions .kw-action{flex:1}.kw-card{min-height:205px}}
</style>
<main class="kw-page">
 <header class="kw-hero mb-4">
  <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
   <div><div class="kw-kicker">KayXchange Wallet</div><h1 class="kw-title display-6 mt-2 mb-2">Your money, made clear.</h1><p class="kw-sub mb-0">See your available balances, live crypto values, and deposit history in one place.</p></div>
   <span class="kw-status" id="marketStatus"><span class="kw-dot {{ $marketMeta['is_live'] ? 'live' : '' }}" id="marketDot"></span><span id="marketStatusText">{{ $marketMeta['is_live'] ? 'Live prices' : ($marketMeta['stale'] ? 'Last known prices' : 'Market prices unavailable') }}</span></span>
  </div>
  <div class="row align-items-end g-4 mt-2">
  <div class="col-lg-7"><div class="kw-muted small mb-2">Portfolio value</div><div class="kw-total" id="portfolioUsd">{{ $portfolioUsd !== null ? '$'.number_format($portfolioUsd,2) : '—' }} <small>USD</small></div><div class="kw-fiat mt-2" id="portfolioNgn">{{ $portfolioNgn !== null ? '≈ ₦'.number_format($portfolioNgn,2) : 'Naira estimate unavailable' }}</div><div class="kw-note mt-2" id="priceUpdated">{{ $marketMeta['updated_at'] ? 'Prices updated '.\Illuminate\Support\Carbon::parse($marketMeta['updated_at'])->diffForHumans() : 'Waiting for a market price source' }}{{ $ngnPerUsd > 0 ? ' · NGN estimate uses platform USD sell rate' : ' · NGN balance excluded from USD total until a platform rate is configured' }}</div></div>
   <div class="col-lg-5"><div class="kw-actions justify-content-lg-end"><a class="kw-action primary" href="{{ route('wallet.deposit') }}"><i class="bi bi-plus-lg"></i>Deposit crypto</a><a class="kw-action" href="{{ route('wallet.withdraw') }}"><i class="bi bi-arrow-up-right"></i>Withdraw</a><a class="kw-action" href="{{ route('wallet.history') }}"><i class="bi bi-clock-history"></i>History</a></div></div>
  </div>
 </header>

 <div class="d-flex justify-content-between align-items-end gap-2 mb-3"><div><div class="kw-kicker">Your accounts</div><h2 class="kw-section-title mt-1 mb-0">Wallet balances</h2></div><div class="kw-note" id="priceSource">{{ ucfirst($marketMeta['source']) }} market data</div></div>
 <section class="row g-3 mb-4" id="walletCards">
  <div class="col-md-6 col-xl-4"><article class="kw-card kw-ngn-card"><div class="d-flex justify-content-between"><div class="d-flex gap-3 align-items-center"><div class="kw-coin-icon"><i class="bi bi-bank"></i></div><div><div class="kw-symbol">NGN</div><div class="kw-asset-name">Naira balance</div></div></div><span class="kw-status-pill">Fiat</span></div><div class="kw-balance">₦{{ number_format((float)auth()->user()->balance,2) }}</div><div class="kw-value"><span>Available balance</span><span>NGN</span></div><div class="kw-asset-actions"><a href="{{ route('deposits.create') }}">Add funds</a><a href="{{ route('withdraw') }}">Withdraw</a></div></article></div>
  @foreach($wallets as $wallet)
   @php($currency = strtoupper($wallet->currency))
   @if(!in_array($currency, ['NGN', 'USD', 'EUR'], true))
   @php($isUsd = $currency === 'USD')
   @php($isMultiNetworkUsdt = $currency === 'USDT')
   @php($marketPrice = $isUsd ? 1 : ($prices[$currency]['price_usd'] ?? null))
   @php($currentValue = $marketPrice !== null ? (float)$wallet->balance * $marketPrice : null)
   <div class="col-md-6 col-xl-4"><article class="kw-card {{ $isUsd ? 'kw-fiat-card' : '' }} wallet-card" data-currency="{{ $currency }}" data-amount="{{ (float)$wallet->balance }}">
    <div class="d-flex justify-content-between"><div class="d-flex gap-3 align-items-center"><div class="kw-coin-icon"><i class="bi bi-currency-bitcoin"></i></div><div><div class="kw-symbol">{{ $currency }}</div><div class="kw-asset-name">{{ $isMultiNetworkUsdt ? 'Tether · multiple networks' : ($wallet->network ?: 'Crypto wallet') }}</div></div></div><span class="kw-status-pill">{{ $isMultiNetworkUsdt ? 'Multi-network' : ($wallet->network ?: 'Crypto') }}</span></div>
    <div class="kw-balance">{{ number_format((float)$wallet->balance, $isUsd ? 2 : 8) }} <span class="fs-6">{{ $currency }}</span></div>
    <div class="kw-value"><span>Current market value</span><strong class="wallet-value">{{ $currentValue !== null ? '$'.number_format($currentValue,2) : '—' }}</strong></div>
    <div class="kw-price">@if($marketPrice !== null)Spot price: ${{ number_format($marketPrice, $marketPrice < 1 ? 6 : 2) }} @else Live spot price unavailable @endif
     @if(isset($prices[$currency]['change_24h']) && $prices[$currency]['change_24h'] !== null)<span class="kw-change {{ $prices[$currency]['change_24h'] >= 0 ? 'kw-up' : 'kw-down' }} ms-1">{{ $prices[$currency]['change_24h'] >= 0 ? '+' : '' }}{{ number_format($prices[$currency]['change_24h'],2) }}% 24h</span>@endif
    </div>
    @if($wallet->address)<div class="kw-note text-truncate mt-2" title="{{ $wallet->address }}">{{ $wallet->address }}</div>@endif
    @if($isMultiNetworkUsdt)<div class="kw-note mt-2">USDT balance is shared across TRC20, ERC20 and BEP20 deposits. Choose the matching network on each deposit invoice.</div>@endif
    @php($assetNetworks=['BTC'=>['BTC'],'ETH'=>['ERC20'],'USDT'=>['TRC20','ERC20','BEP20'],'USDC'=>['ERC20','BEP20'],'BNB'=>['BEP20'],'SOL'=>['SOL']][$currency] ?? [])
    @php($suggestedNetwork=in_array(strtoupper((string)$wallet->network),$assetNetworks,true)?strtoupper($wallet->network):($assetNetworks[0]??''))
    <div class="kw-asset-actions"><a href="{{ route('wallet.deposit', ['currency'=>$currency,'network'=>$suggestedNetwork]) }}">Deposit</a><a href="{{ route('wallet.withdraw', ['currency'=>$currency,'network'=>$suggestedNetwork]) }}">Withdraw · coming soon</a></div>
   </article></div>
   @endif
  @endforeach
 </section>

 <section class="kw-panel mb-4">
  <div class="kw-panel-head"><div><div class="kw-kicker">Your activity</div><h2 class="kw-section-title mt-1 mb-0">Recent deposits</h2></div><a class="kw-link" href="{{ route('wallet.history') }}">Full wallet history <i class="bi bi-arrow-up-right"></i></a></div>
  @forelse($recentDeposits as $deposit)
   @php($paymentData = $deposit->payment_data ?? [])
   @php($input = $paymentData['deposit_input'] ?? [])
   @php($snapshot = $paymentData['deposit_valuation'] ?? [])
   @php($symbol = strtoupper($deposit->currency))
   @php($currentSpot = $symbol === 'USD' ? 1 : ($prices[$symbol]['price_usd'] ?? null))
   @php($liveDepositValue = $deposit->status === 'completed' && $currentSpot !== null && $deposit->received_amount ? (float)$deposit->received_amount * $currentSpot : null)
   <article class="kw-deposit"><div class="kw-deposit-grid">
    <div class="kw-deposit-main"><div class="kw-deposit-value">{{ $symbol }} deposit <span class="kw-status-pill {{ $deposit->status }} ms-1">{{ ucfirst($deposit->status) }}</span></div><div class="kw-note mt-1">{{ $deposit->created_at->format('M d, Y · H:i') }} @if($deposit->transaction_hash) · {{ \Illuminate\Support\Str::limit($deposit->transaction_hash, 18) }}@endif</div></div>
    <div><div class="kw-label">You entered</div><div class="kw-deposit-value">@if(isset($input['input_amount'])){{ number_format((float)$input['input_amount'], ($input['input_type'] ?? '') === 'crypto' ? 8 : 2) }} {{ $input['input_currency'] ?? 'USD' }}@else${{ number_format((float)$deposit->expected_amount,2) }} USD @endif</div></div>
    <div><div class="kw-label">Value at deposit</div><div class="kw-deposit-value">{{ isset($snapshot['market_value_usd']) ? '$'.number_format((float)$snapshot['market_value_usd'],2) : '—' }}</div><div class="kw-note">{{ isset($snapshot['market_price_usd']) ? '$'.number_format((float)$snapshot['market_price_usd'],2).' / '.$symbol : 'No snapshot yet' }}</div></div>
    <div><div class="kw-label">Current spot value</div><div class="kw-deposit-value" data-deposit-value data-currency="{{ $symbol }}" data-amount="{{ (float)($deposit->received_amount ?? 0) }}">{{ $liveDepositValue !== null ? '$'.number_format($liveDepositValue,2) : '—' }}</div>@if($deposit->ngn_credited_amount)<div class="kw-note">₦{{ number_format((float)$deposit->ngn_credited_amount,2) }} credited</div>@endif</div>
    <div><a class="kw-link" href="{{ route('wallet.deposit.success', $deposit->id) }}">Details</a></div>
   </div></article>
  @empty
   <div class="kw-empty"><div class="kw-empty-icon"><i class="bi bi-inbox"></i></div><div class="fw-bold text-light">No deposits yet</div><p class="mb-3">Create a crypto deposit invoice to start building your wallet history.</p><a class="kw-action primary" href="{{ route('wallet.deposit') }}">Create a deposit</a></div>
  @endforelse
 </section>
 <footer class="kw-note text-center">Market values are estimates based on the latest available spot price. Your actual credited balances and saved deposit-time values remain unchanged.</footer>
</main>
<script>
(()=>{
 const endpoint=@json(route('wallet.market-prices'));
 const usdNgnRate={{ (float)$ngnPerUsd }};
 const currencyFmt=new Intl.NumberFormat('en-US',{style:'currency',currency:'USD',minimumFractionDigits:2,maximumFractionDigits:2});
 const money=n=>currencyFmt.format(n);
 const statusText=document.getElementById('marketStatusText'),dot=document.getElementById('marketDot');
 async function refreshWalletPrices(){
  try{
   const response=await fetch(endpoint,{headers:{Accept:'application/json'},cache:'no-store'});
   if(!response.ok)throw new Error('Market prices unavailable');
   const data=await response.json(),prices=data.prices||{},wallets=data.wallets||{};
   let total=Number(data.ngn_per_usd)>0?(Number(data.ngn_balance)||0)/Number(data.ngn_per_usd):0,complete=true;
   for(const card of document.querySelectorAll('.wallet-card')){
    const currency=card.dataset.currency,amount=Number(wallets[currency]??card.dataset.amount??0),price=currency==='USD'?1:Number(prices[currency]?.price_usd);
    card.dataset.amount=amount;
    if(Number.isFinite(price)&&price>0){const value=amount*price;card.querySelector('.wallet-value').textContent=money(value);total+=value}
    else{card.querySelector('.wallet-value').textContent=amount===0?money(0):'—';if(amount>0)complete=false}
    const priceEl=card.querySelector('.kw-price');if(currency!=='USD'&&prices[currency]){const p=Number(prices[currency].price_usd),change=prices[currency].change_24h;priceEl.firstChild.textContent='Spot price: '+money(p)+' ';if(change!==null&&change!==undefined){let badge=priceEl.querySelector('.kw-change');if(!badge){badge=document.createElement('span');badge.className='kw-change ms-1';priceEl.appendChild(badge)}badge.className='kw-change ms-1 '+(Number(change)>=0?'kw-up':'kw-down');badge.textContent=(Number(change)>=0?'+':'')+Number(change).toFixed(2)+'% 24h'}}
   }
   for(const node of document.querySelectorAll('[data-deposit-value]')){const price=Number(prices[node.dataset.currency]?.price_usd),amount=Number(node.dataset.amount);if(price>0&&amount>0)node.textContent=money(price*amount)}
   document.getElementById('portfolioUsd').innerHTML=complete?money(total)+' <small>USD</small>':'— <small>USD</small>';
   document.getElementById('portfolioNgn').textContent=complete&&Number(data.ngn_per_usd)>0?'≈ ₦'+(total*Number(data.ngn_per_usd)).toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2}):'Naira estimate unavailable';
   const source=(data.source||'').toLowerCase(),live=!data.stale&&source!=='fallback'&&Object.keys(prices).length>0;
   statusText.textContent=live?'Live prices · '+source.replace(/^./,c=>c.toUpperCase()):(data.stale?'Last known prices':'Market prices unavailable');dot.classList.toggle('live',live);
   document.getElementById('priceSource').textContent=(data.source||'Market').replace(/^./,c=>c.toUpperCase())+' market data';
   const stamp=data.updated_at?new Date(data.updated_at):null;document.getElementById('priceUpdated').textContent=(stamp&&!Number.isNaN(stamp.getTime())?'Prices updated '+stamp.toLocaleTimeString():'Price update unavailable')+(Number(data.ngn_per_usd)>0?' · NGN estimate uses platform USD sell rate':'');
  }catch(error){statusText.textContent='Price update unavailable';dot.classList.remove('live')}
 }
 refreshWalletPrices();window.setInterval(refreshWalletPrices,60000);
})();
</script>
@endsection
