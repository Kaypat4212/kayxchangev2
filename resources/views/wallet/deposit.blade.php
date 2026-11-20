@extends('layouts.app')
@section('title', 'Deposit Crypto')
@section('content')
<style>
.dep-page{max-width:1040px;margin:0 auto;padding:24px 16px 64px;color:#eaf1f8}.dep-hero{border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:27px;background:radial-gradient(circle at 88% 5%,rgba(0,204,0,.16),transparent 32%),linear-gradient(130deg,#101a27,#121b21)}.dep-card{height:100%;background:#131b25;border:1px solid rgba(255,255,255,.1);border-radius:19px;padding:22px}.dep-label{font-size:.82rem;color:#aab7c5;font-weight:700;margin-bottom:7px}.dep-control{width:100%;background:#0b121a;color:#f3f7fa;border:1px solid #354353;border-radius:12px;padding:13px;min-height:48px}.dep-control:focus{outline:2px solid rgba(0,204,0,.28);border-color:#00cc00}.dep-muted{color:#98a6b4}.dep-estimate{border:1px solid rgba(0,204,0,.18);border-radius:15px;background:rgba(0,204,0,.055);padding:16px}.dep-estimate-value{font-size:1.55rem;font-weight:850;color:#e9f9ec}.dep-submit{border:0;border-radius:12px;background:#00bf36;color:#041008;font-weight:850;padding:13px 18px}.dep-submit:disabled{opacity:.55}.dep-alert{border:1px solid rgba(245,158,11,.22);background:rgba(245,158,11,.08);color:#f4d69b;padding:13px;border-radius:12px;font-size:.82rem}.dep-minimum{border:1px solid rgba(56,189,248,.24);background:rgba(56,189,248,.07);color:#d6eaf5;padding:12px 14px;border-radius:12px;font-size:.82rem}.dep-assets{display:flex;gap:9px;flex-wrap:wrap}.dep-asset{background:#0d151d;border:1px solid rgba(255,255,255,.09);border-radius:12px;padding:11px 13px;min-width:110px}.dep-asset strong{display:block}.dep-asset span{color:#8fa0af;font-size:.75rem}
.dep-feedback{display:flex;gap:13px;align-items:flex-start;padding:16px 18px;border-radius:15px;border:1px solid rgba(248,113,113,.28);background:linear-gradient(120deg,rgba(127,29,29,.25),rgba(19,27,37,.98));color:#f8fafc;box-shadow:0 12px 32px rgba(0,0,0,.16)}.dep-feedback-icon{width:38px;height:38px;flex:0 0 38px;border-radius:12px;display:grid;place-items:center;background:rgba(248,113,113,.13);color:#fca5a5}.dep-feedback h2{font-size:.98rem;font-weight:800;margin:0 0 4px}.dep-feedback p{font-size:.86rem;color:#c4ced8;margin:0}.dep-feedback a{color:#86efac;font-weight:700;text-decoration:none}.dep-feedback-details{margin-top:10px;color:#fcd38d;font-size:.78rem;overflow-wrap:anywhere}
</style>
<main class="dep-page">
 <header class="dep-hero mb-4"><div class="small text-success fw-bold text-uppercase">Fund your portfolio</div><h1 class="h2 fw-bold mt-2 mb-2">Create a crypto deposit</h1><p class="dep-muted mb-0">Enter the amount in USD or crypto. We’ll show the live estimate and prepare secure payment instructions.</p>@if($depositProvider)<div class="small mt-3 text-success"><i class="bi bi-shield-check me-1"></i>Secure payment service ready</div>@else<div class="small mt-3 text-warning">Crypto deposits are temporarily unavailable. Please contact support.</div>@endif</header>
 <div class="dep-alert mb-3"><i class="bi bi-wallet2 me-1"></i>Confirmed deposits will {{ $settlementPreference === 'ngn' ? 'be converted into your Naira wallet' : 'stay in the selected crypto wallet and appear in your USD portfolio total' }}. <a href="{{ route('settings.index') }}" class="text-success fw-bold">Change this in wallet preferences</a>. This choice is saved with each new invoice.</div>
 @if(session('deposit_error'))<div class="dep-feedback mb-4" role="alert"><div class="dep-feedback-icon"><i class="bi bi-exclamation-triangle-fill"></i></div><div><h2>{{ session('deposit_error.title') }}</h2><p>{{ session('deposit_error.message') }}</p><a href="{{ route('support.chat') }}" class="d-inline-block mt-2">Contact support <i class="bi bi-arrow-up-right"></i></a>@if(session('deposit_error.diagnostic'))<div class="dep-feedback-details"><strong>Developer diagnostic:</strong> {{ session('deposit_error.diagnostic') }}</div>@endif</div></div>@elseif(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
 @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
 <div class="row g-3">
  <div class="col-lg-7"><section class="dep-card">
   <form action="{{ route('wallet.deposit.create') }}" method="POST" id="depositForm">@csrf
    <div class="mb-3"><label class="dep-label" for="currency">Cryptocurrency</label><select name="currency" id="currency" class="dep-control" required><option value="">Choose an asset</option>@foreach($supportedCryptos as $symbol=>$asset)<option value="{{ $symbol }}" {{ $selectedCurrency===$symbol?'selected':'' }}>{{ $asset['name'] }} ({{ $symbol }})</option>@endforeach</select></div>
    <div class="mb-3"><label class="dep-label" for="network">Network</label><select name="network" id="network" class="dep-control" required><option value="">Choose a network</option></select><div class="dep-muted small mt-1">Send only using the selected network. The invoice address and amount are generated for this choice.</div></div>
    <div class="dep-minimum mb-3" id="depositMinimumBox" hidden aria-live="polite"><i class="bi bi-info-circle-fill me-1"></i><span id="depositMinimumText">Checking the minimum for this network…</span><div class="small mt-1" id="depositMinimumMeta"></div></div>
    <div class="row g-3 mb-3"><div class="col-sm-5"><label class="dep-label" for="input_type">I want to enter</label><select name="input_type" id="input_type" class="dep-control"><option value="usd" {{ old('input_type','usd')==='usd'?'selected':'' }}>USD value</option><option value="crypto" {{ old('input_type')==='crypto'?'selected':'' }}>Crypto amount</option></select></div><div class="col-sm-7"><label class="dep-label" for="amount" id="amountLabel">Deposit value (USD)</label><input name="amount" id="amount" type="number" min="0.00000001" step="any" value="{{ old('amount') }}" class="dep-control" placeholder="Enter amount" required></div></div>
    <div class="dep-estimate mb-3"><div class="dep-muted small" id="estimateLabel">Estimated crypto amount</div><div class="dep-estimate-value" id="estimateAmount">Choose an asset and amount</div><div class="dep-muted small mt-1" id="estimateMeta">Prices refresh automatically from the market price service.</div></div>
    <div class="dep-alert mb-3"><i class="bi bi-info-circle-fill me-1"></i>The provider locks the invoice in USD when it is created. At settlement, we save the received crypto amount and market value snapshot. Market values can move after the deposit.</div>
    <button class="dep-submit w-100" type="submit" id="createDepositBtn" {{ !$depositProvider ? 'disabled' : '' }}><i class="bi bi-shield-check me-1"></i>Create deposit invoice</button>
   </form>
  </section></div>
  <div class="col-lg-5"><section class="dep-card"><div class="small text-success fw-bold text-uppercase">Supported assets</div><h2 class="h5 fw-bold mt-2">Deposit choices</h2><p class="dep-muted small">Select the network shown on the invoice carefully. Only send the selected asset over the matching network.</p><div class="dep-assets">@foreach($supportedCryptos as $symbol=>$asset)<div class="dep-asset"><strong>{{ $symbol }}</strong><span>{{ implode(' / ', $asset['networks']) }}</span></div>@endforeach</div><div class="border-top border-secondary border-opacity-25 mt-4 pt-3 dep-muted small">Your invoice address and QR code are provided by the payment provider after you continue. Never pay an address from an old or unrelated invoice.</div></section></div>
 </div>
 @include('components.flow-resources', ['flow' => 'deposit'])
</main>
<script>
(()=>{
 const endpoint=@json(route('wallet.market-prices')),$=id=>document.getElementById(id);
 const supported=@json(collect($supportedCryptos)->map(fn($asset)=>$asset['networks'])->all()),initialNetwork=@json($selectedNetwork),initialCurrency=@json($selectedCurrency);
 let livePrices={},priceSource='unavailable',lastUpdated=null,pricesStale=false,minimumQuote=null,minimumRequest=0;
 function refreshEstimate(){
  const coin=$('currency').value,kind=$('input_type').value,amount=Number($('amount').value||0),price=Number(livePrices[coin]?.price_usd||0);
  $('amountLabel').textContent=kind==='usd'?'Deposit value (USD)':'Crypto amount ('+(coin||'crypto')+')';
  $('estimateLabel').textContent=kind==='usd'?'Estimated crypto amount':'Estimated USD invoice';
  if(!coin||amount<=0){$('estimateAmount').textContent='Choose an asset and amount';$('estimateMeta').textContent='Prices refresh automatically from the market price service.';return}
  if(kind==='crypto'&&(price<=0||pricesStale)){ $('estimateAmount').textContent=pricesStale?'Waiting for a fresh market quote':'Live price unavailable';$('estimateMeta').textContent='Crypto-amount deposits require a fresh market price. Try again shortly or enter a USD amount.';$('createDepositBtn').disabled=true;return}
  $('createDepositBtn').disabled=false;
  const usd=kind==='usd'?amount:amount*price,crypto=kind==='usd'?(price>0?amount/price:null):amount;
  $('estimateAmount').textContent=kind==='usd'?(crypto!==null?crypto.toLocaleString('en-US',{maximumFractionDigits:8})+' '+coin:'USD invoice · $'+usd.toFixed(2)):'$'+usd.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})+' USD';
  $('estimateMeta').textContent=(kind==='usd'?'USD invoice · $'+usd.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}):'Market price · $'+price.toLocaleString('en-US',{maximumFractionDigits:price<1?6:2})+' / '+coin)+(lastUpdated?' · '+priceSource+' · '+new Date(lastUpdated).toLocaleTimeString():'');
  const minimumUsd=Number(minimumQuote?.minimum_usd||0);
  if(minimumUsd>0&&usd+0.000001<minimumUsd){$('estimateMeta').textContent+=' · Below the $'+minimumUsd.toFixed(2)+' minimum';$('createDepositBtn').disabled=true}
 }
 async function loadDepositMinimum(){
  const coin=$('currency').value,network=$('network').value,box=$('depositMinimumBox'),text=$('depositMinimumText'),meta=$('depositMinimumMeta'),requestId=++minimumRequest;
  minimumQuote=null;if(!coin||!network){box.hidden=true;refreshEstimate();return}
  box.hidden=false;text.textContent='Checking the minimum for '+coin+' on '+network+'…';meta.textContent='';
  try{
   const url=new URL(@json(route('wallet.deposit.minimum')),window.location.origin);url.searchParams.set('currency',coin);url.searchParams.set('network',network);
   const response=await fetch(url,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin',cache:'no-store'});const data=await response.json();
   if(!response.ok)throw new Error(data.message||'Minimum could not be loaded.');if(requestId!==minimumRequest)return;
   minimumQuote=data;const effective=Number(data.minimum_usd||0),provider=Number(data.provider_minimum_usd||0),floor=Number(data.admin_minimum_usd||0);
   if(data.provider_status==='live'){
    text.innerHTML='Minimum deposit for '+coin+' · '+network+': <strong>$'+effective.toFixed(2)+' USD</strong>';
    meta.textContent='NOWPayments minimum: $'+provider.toFixed(2)+' · Platform minimum floor: $'+floor.toFixed(2)+'. The higher amount applies.';
   }else if(data.provider_status==='unavailable'){
    text.textContent=floor>0?'Platform minimum for '+coin+' · '+network+': $'+floor.toFixed(2)+' USD':'Live provider minimum is temporarily unavailable.';
    meta.textContent='NOWPayments may require a higher minimum. The invoice will be checked against the provider minimum before it is created.';
   }else{
    text.textContent=floor>0?'Minimum deposit for '+coin+' · '+network+': $'+floor.toFixed(2)+' USD':'No platform minimum is configured for this network.';
    meta.textContent='Minimum is the higher of the platform floor and current provider minimum when NOWPayments is active.';
   }
   refreshEstimate();
  }catch(error){if(requestId!==minimumRequest)return;text.textContent='Could not refresh the live minimum.';meta.textContent='The final invoice request still checks the provider minimum. '+(error.message||'');refreshEstimate()}
 }
 async function loadPrices(){try{const res=await fetch(endpoint,{headers:{Accept:'application/json'},cache:'no-store'});if(!res.ok)throw new Error();const data=await res.json();livePrices=data.prices||{};priceSource=data.source||'market';lastUpdated=data.updated_at||null;pricesStale=Boolean(data.stale);refreshEstimate()}catch(e){pricesStale=true;$('estimateMeta').textContent='Could not refresh market prices. USD input remains available; crypto input needs a live quote.';refreshEstimate()}}
 function refreshNetworks(){const select=$('network'),wanted=$('currency').value===initialCurrency?initialNetwork:'';select.innerHTML='<option value="">Choose a network</option>';for(const network of (supported[$('currency').value]||[])){const option=document.createElement('option');option.value=network;option.textContent=network;if(wanted===network)option.selected=true;select.appendChild(option)}if(!select.value&&select.options.length===2)select.selectedIndex=1}
 $('network').addEventListener('change',()=>{$('network').dataset.userChanged='1';loadDepositMinimum()});
 $('currency').addEventListener('change',()=>{refreshNetworks();refreshEstimate();loadDepositMinimum()});$('input_type').addEventListener('change',refreshEstimate);$('amount').addEventListener('input',refreshEstimate);refreshNetworks();loadDepositMinimum();
 $('depositForm').addEventListener('submit',e=>{const amount=Number($('amount').value),price=Number(livePrices[$('currency').value]?.price_usd||0),usd=$('input_type').value==='crypto'?amount*price:amount,minimum=Number(minimumQuote?.minimum_usd||0);if($('input_type').value==='crypto'&&(price<=0||pricesStale)){e.preventDefault();refreshEstimate()}else if(amount<=0){e.preventDefault()}else if(minimum>0&&usd+0.000001<minimum){e.preventDefault();refreshEstimate();$('amount').focus()}});
 loadPrices();window.setInterval(loadPrices,60000);
})();
</script>
@endsection
