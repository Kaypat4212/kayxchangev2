@extends('adminnavlayout')

@section('content')
<style>
.cd-wrap{max-width:1220px;margin:0 auto;padding:28px 16px 70px;color:#eaf0f7}.cd-hero,.cd-card{border:1px solid rgba(255,255,255,.09);border-radius:21px;background:#131b25}.cd-hero{padding:26px;background:radial-gradient(circle at 90% 8%,rgba(0,204,0,.17),transparent 32%),linear-gradient(130deg,#101a27,#121d19);margin-bottom:18px}.cd-hero h1{color:#fff;font-weight:850}.cd-muted{color:#98a6b6}.cd-card{padding:21px;margin-bottom:16px}.cd-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:13px}.cd-network{background:#0d151e;border:1px solid rgba(255,255,255,.08);border-radius:15px;padding:15px}.cd-label{font-size:.75rem;color:#a8b5c3;font-weight:700;display:block;margin:12px 0 6px}.cd-input{width:100%;background:#080f16;border:1px solid #344455;border-radius:10px;padding:11px 12px;color:#f3f7fb}.cd-input:focus{outline:2px solid rgba(0,204,0,.25);border-color:#00c800}.cd-input.address{font-family:monospace;font-size:.78rem}.cd-switch{display:flex;align-items:flex-start;gap:10px;background:#0d151e;border:1px solid rgba(255,255,255,.08);border-radius:13px;padding:14px;height:100%}.cd-switch input{width:19px;height:19px;accent-color:#00c853;margin-top:2px}.cd-security{border:1px solid rgba(245,158,11,.24);background:rgba(245,158,11,.07);border-radius:13px;padding:14px;color:#f3d69c;font-size:.84rem}.cd-save{position:sticky;bottom:0;padding:14px 0;background:linear-gradient(0deg,#0c121a 70%,transparent)}.cd-pill{border:1px solid rgba(255,255,255,.12);border-radius:99px;padding:6px 10px;color:#aab7c4;font-size:.75rem}
</style>
<main class="cd-wrap">
 <header class="cd-hero d-flex justify-content-between gap-3 align-items-center flex-wrap"><div><div class="text-success small fw-bold text-uppercase">Wallet operations</div><h1 class="h2 mt-2 mb-2">Crypto deposit settings</h1><p class="cd-muted mb-0">Set deposit minimums, payment defaults, and backup receiving addresses for each supported network.</p></div><span class="cd-pill"><i class="bi bi-shield-check text-success me-1"></i>Wallet changes require admin PIN</span></header>
 @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
 <form method="POST" action="{{ route('admin.crypto-deposit-settings.update') }}" id="cryptoDepositSettingsForm">
  @csrf @method('PUT')
  <section class="cd-card"><div class="d-flex justify-content-between gap-2 flex-wrap align-items-center mb-3"><div><h2 class="h5 text-white fw-bold mb-1">Payment defaults</h2><p class="cd-muted small mb-0">Minimums are in USD. The provider’s current network minimum is checked before invoices are created.</p><div class="cd-muted small mt-2" id="providerMinimumStatus" role="status" aria-live="polite">Provider minimums have not been refreshed yet.</div></div><div class="d-flex gap-2"><button class="btn btn-outline-success btn-sm" type="button" id="refreshProviderMinimums"><i class="bi bi-arrow-repeat me-1"></i>Fetch NOWPayments minimums</button><a class="btn btn-outline-light btn-sm" href="{{ route('admin.settings.index') }}#payment"><i class="bi bi-key me-1"></i>Payment credentials</a></div></div>
   <div class="row g-3">
    <div class="col-md-4"><label class="cd-label mt-0" for="payment_window_minutes">Customer payment window (minutes)</label><input class="cd-input" type="number" min="30" max="60" id="payment_window_minutes" name="payment_window_minutes" value="{{ old('payment_window_minutes',$paymentWindowMinutes) }}" required><div class="cd-muted small mt-1">NOWPayments invoices cannot be deleted by the platform. At local timeout, instructions are hidden and late payments are monitored for up to 7 days; late funds require admin review.</div></div>
    <div class="col-md-4"><label class="cd-label mt-0" for="expiry_hours">Other provider hold window (hours)</label><input class="cd-input" type="number" min="1" max="72" id="expiry_hours" name="expiry_hours" value="{{ old('expiry_hours',$expiryHours) }}" required><div class="cd-muted small mt-1">Used for deposits that do not use the NOWPayments customer timer.</div></div>
    <div class="col-md-4"><label class="cd-switch"><input type="checkbox" name="nowpayments_fixed_rate" value="1" @checked(old('nowpayments_fixed_rate',$fixedRate))><span><strong class="d-block text-white">Request a fixed quote</strong><span class="cd-muted small">Ask for a fixed rate when the provider supports it.</span></span></label></div>
    <div class="col-md-4"><label class="cd-switch"><input type="checkbox" name="nowpayments_fee_paid_by_user" value="1" @checked(old('nowpayments_fee_paid_by_user',$feePaidByUser))><span><strong class="d-block text-white">Customer pays network fee</strong><span class="cd-muted small">Pass the fee responsibility setting to the payment provider.</span></span></label></div>
    <div class="col-md-4"><label class="cd-switch"><input type="checkbox" name="admin_review_enabled" value="1" @checked(old('admin_review_enabled',$adminReviewEnabled))><span><strong class="d-block text-white">Review deposits before credit</strong><span class="cd-muted small">Confirmed crypto payments wait in the admin review queue before wallet credit.</span></span></label></div>
   </div>
  </section>
  <section class="cd-card"><h2 class="h5 text-white fw-bold mb-1">Deposit minimums</h2><p class="cd-muted small">Set a KayXchange minimum floor per asset/network. A value of $0 uses the provider’s live minimum when available. Manual fallback requires a positive floor because it cannot query the live minimum during an outage.</p>
   @foreach($assets as $currency=>$asset)
    <div class="border-top border-secondary border-opacity-25 pt-3 mt-3"><div class="d-flex align-items-center gap-2 mb-3"><span class="badge text-bg-success">{{ $currency }}</span><h3 class="h6 text-white mb-0">{{ $asset['name'] }}</h3></div><div class="cd-grid">
     @foreach($asset['networks'] as $network)
      <div class="cd-network"><span class="cd-pill">{{ $network }}</span><label class="cd-label" for="minimum_{{ $currency }}_{{ $network }}">KayXchange minimum floor (USD)</label><div class="input-group"><span class="input-group-text bg-dark border-secondary text-secondary">$</span><input class="cd-input" type="number" min="0" step="0.01" id="minimum_{{ $currency }}_{{ $network }}" name="minimums[{{ $currency }}][{{ $network }}]" value="{{ old("minimums.{$currency}.{$network}",data_get($walletSettings,"{$currency}.{$network}.minimum_usd")) }}"></div><div class="cd-muted small mt-2" data-provider-minimum data-currency="{{ $currency }}" data-network="{{ $network }}">NOWPayments minimum: Fetch to check · User minimum is the higher of this floor and the provider minimum.</div></div>
     @endforeach
    </div></div>
   @endforeach
  </section>
  <section class="cd-card"><div class="d-flex justify-content-between gap-3 flex-wrap align-items-start mb-3"><div><h2 class="h5 text-white fw-bold mb-1">Backup receiving addresses</h2><p class="cd-muted small mb-0">Used only when payment creation is temporarily unavailable and the matching wallet is configured.</p></div><label class="cd-switch" style="height:auto"><input type="checkbox" name="fallback_enabled" value="1" @checked(old('fallback_enabled',$fallbackEnabled))><span><strong class="text-white">Enable manual fallback</strong><span class="cd-muted small d-block">Customers submit a transaction hash; an admin reviews and approves it.</span></span></label></div>
   <div class="cd-security mb-3"><i class="bi bi-shield-lock-fill me-1"></i>Changing any address requires the current admin account’s 4-digit security PIN. Address changes are encrypted at rest and audited.</div>
   @foreach($assets as $currency=>$asset)
    <div class="border-top border-secondary border-opacity-25 pt-3 mt-3"><div class="d-flex align-items-center gap-2 mb-3"><span class="badge text-bg-secondary">{{ $currency }}</span><h3 class="h6 text-white mb-0">{{ $asset['name'] }}</h3></div><div class="cd-grid">
     @foreach($asset['networks'] as $network)
      <div class="cd-network"><span class="cd-pill">{{ $network }}</span><label class="cd-label" for="address_{{ $currency }}_{{ $network }}">Receiving address</label><input class="cd-input address" type="text" id="address_{{ $currency }}_{{ $network }}" name="addresses[{{ $currency }}][{{ $network }}]" value="{{ old("addresses.{$currency}.{$network}",data_get($walletSettings,"{$currency}.{$network}.address")) }}" placeholder="Configure a wallet address for {{ $network }}"></div>
     @endforeach
    </div></div>
   @endforeach
   <div class="mt-4" style="max-width:360px"><label class="cd-label" for="wallet_change_pin">Admin security PIN <span class="cd-muted fw-normal">(required only if an address changes)</span></label><input class="cd-input" type="password" inputmode="numeric" autocomplete="current-password" maxlength="4" pattern="[0-9]{4}" id="wallet_change_pin" name="wallet_change_pin" placeholder="4-digit PIN"></div>
  </section>
  <div class="cd-save"><button class="btn btn-success btn-lg px-4" type="submit"><i class="bi bi-shield-check me-1"></i>Save deposit settings</button></div>
 </form>
</main>
<script>
(()=>{
 const button=document.getElementById('refreshProviderMinimums'),status=document.getElementById('providerMinimumStatus');
 if(!button||!status)return;
 button.addEventListener('click',async()=>{
  button.disabled=true;button.innerHTML='<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Checking provider…';status.textContent='Fetching live network minimums from NOWPayments. This may take a few seconds.';status.className='cd-muted small mt-2';
  try{
   const response=await fetch(@json(route('admin.crypto-deposit-settings.provider-minimums')),{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin',cache:'no-store'});
   const data=await response.json();if(!response.ok||!data.ok)throw new Error(data.message||'Could not fetch provider minimums.');
   let available=0;
   document.querySelectorAll('[data-provider-minimum]').forEach(el=>{
    const row=data.minimums?.[el.dataset.currency]?.[el.dataset.network],provider=Number(row?.provider_minimum_usd),floor=Number(row?.admin_floor_usd||0),effective=Number(row?.effective_minimum_usd||floor);
    if(Number.isFinite(provider)&&provider>0){available++;el.innerHTML=`NOWPayments minimum: <strong class="text-info">$${provider.toFixed(2)}</strong> · User minimum: <strong class="text-success">$${effective.toFixed(2)}</strong>`}
    else el.innerHTML='NOWPayments minimum: <span class="text-warning">Unavailable</span> · User floor: <strong class="text-success">$'+floor.toFixed(2)+'</strong>';
   });
   status.textContent=`${available} network minimum${available===1?'':'s'} received from NOWPayments (${String(data.environment||'').toUpperCase()}). Updated ${new Date(data.updated_at).toLocaleTimeString()}.`;
  }catch(error){status.textContent=error.message||'Could not fetch NOWPayments minimums. Check the saved API key and try again.';status.className='text-warning small mt-2'}
  finally{button.disabled=false;button.innerHTML='<i class="bi bi-arrow-repeat me-1"></i>Fetch NOWPayments minimums'}
 });
})();
</script>
@endsection
