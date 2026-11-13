@extends('adminnavlayout')

@section('content')
<style>
.bill-admin{max-width:1060px;margin:0 auto;padding:28px 16px 60px;color:#e8edf5}.bill-hero{padding:26px;border-radius:20px;background:radial-gradient(circle at 90% 5%,rgba(0,204,0,.18),transparent 34%),linear-gradient(130deg,#101a27,#121d19);border:1px solid rgba(255,255,255,.08);margin-bottom:22px}.bill-hero h1{font-weight:800;color:white}.bill-hero p,.muted{color:#93a0b2}.bill-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(310px,1fr));gap:18px}.bill-card{background:#141b25;border:1px solid rgba(255,255,255,.09);border-radius:17px;padding:22px}.bill-card h2{font-size:1.05rem;color:#fff;font-weight:750}.bill-check{display:flex;align-items:center;gap:10px;padding:12px;border-radius:12px;background:#1b2531;margin:14px 0}.bill-input{width:100%;background:#0d131b;color:#f4f7fa;border:1px solid #334155;padding:11px 13px;border-radius:10px}.bill-label{font-size:.8rem;color:#aab6c5;font-weight:650;margin:12px 0 5px}.bill-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:20px}.bill-status{font-size:.85rem;padding:9px 13px;border-radius:10px;background:#1b2531}.bill-warning{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.22);border-radius:12px;padding:14px;color:#fcd38d;font-size:.88rem}.bill-secret-wrap{position:relative}.bill-secret-wrap .bill-input{padding-right:78px}.bill-secret-toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:#93c5fd;font-size:.8rem;font-weight:700}
</style>
<main class="bill-admin">
    <header class="bill-hero"><div class="d-flex justify-content-between align-items-start gap-3 flex-wrap"><div><div class="text-success small fw-bold text-uppercase">Utilities & bills</div><h1>Bill payment providers</h1><p class="mb-0">Run VTpass, Monnify, or both. Users can choose among enabled providers at checkout.</p></div><div class="d-flex gap-2 flex-wrap"><a class="btn btn-outline-light" href="{{ route('admin.features.index') }}"><i class="bi bi-toggle-on me-1"></i>Feature controls</a><a class="btn btn-outline-light" href="{{ route('admin.bill-payments.index') }}">Bill transactions</a></div></div></header>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="bill-warning mb-3"><strong>Security and activation:</strong> Secrets are encrypted in the database and also synced into the server’s .env file. Keep .env outside the public document root and back it up securely. Monnify Bills Payment needs explicit activation on your Monnify merchant account. Begin in sandbox; do not enable live vending until the account and balance float are approved.</div>
    <form method="POST" action="{{ route('admin.bill-providers.update') }}">
        @csrf @method('PUT')
        <section class="bill-card mb-3">
            <h2><i class="bi bi-power text-success me-2"></i>Customer bill-payment switch</h2>
            <div class="muted">Turn the whole bills service on or off without deleting provider credentials or selections.</div>
            <label class="bill-check mb-0"><input type="checkbox" name="bill_payments_enabled" value="1" {{ $billPaymentsEnabled ? 'checked' : '' }}> Enable bill payments for customers</label>
            <label class="bill-check mb-0"><input type="checkbox" name="utility_payments_enabled" value="1" {{ $utilityPaymentsEnabled ? 'checked' : '' }}> Enable utility payments (electricity and cable TV)</label>
        </section>
        <div class="bill-grid">
            <section class="bill-card">
                <h2><i class="bi bi-lightning-charge-fill text-warning me-2"></i>VTpass</h2>
                <div class="muted">Airtime, data, electricity, cable TV and additional bill services.</div>
                <label class="bill-check"><input type="checkbox" name="providers[]" value="vtpass" {{ in_array('vtpass',$settings->providers(),true) ? 'checked' : '' }}> Enable VTpass for customers</label>
                <label class="bill-check"><input type="checkbox" name="vtpass_sandbox" value="1" {{ $settings->exists ? ($settings->vtpass_sandbox ? 'checked' : '') : 'checked' }}> Sandbox environment</label>
                <label class="bill-label" for="vtpass_api_key">API key {{ filled($settings->vtpass_api_key) ? '· saved' : '· required' }}</label><div class="bill-secret-wrap"><input class="bill-input" id="vtpass_api_key" name="vtpass_api_key" type="password" value="{{ $settings->vtpass_api_key }}" autocomplete="new-password" placeholder="Paste VTpass API key"><button class="bill-secret-toggle" type="button" data-secret-toggle="vtpass_api_key">Show</button></div>
                <label class="bill-label" for="vtpass_public_key">Public key {{ filled($settings->vtpass_public_key) ? '· saved' : '' }}</label><div class="bill-secret-wrap"><input class="bill-input" id="vtpass_public_key" name="vtpass_public_key" type="password" value="{{ $settings->vtpass_public_key }}" autocomplete="new-password" placeholder="Paste VTpass public key"><button class="bill-secret-toggle" type="button" data-secret-toggle="vtpass_public_key">Show</button></div>
                <label class="bill-label" for="vtpass_secret_key">Secret key {{ filled($settings->vtpass_secret_key) ? '· saved' : '' }}</label><div class="bill-secret-wrap"><input class="bill-input" id="vtpass_secret_key" name="vtpass_secret_key" type="password" value="{{ $settings->vtpass_secret_key }}" autocomplete="new-password" placeholder="Paste VTpass secret key"><button class="bill-secret-toggle" type="button" data-secret-toggle="vtpass_secret_key">Show</button></div>
                <button class="btn btn-outline-info btn-sm mt-3" type="button" onclick="testProvider('vtpass')" {{ $configured['vtpass'] ? '' : 'disabled' }}>Test VTpass catalog</button>
            </section>
            <section class="bill-card">
                <h2><i class="bi bi-bank2 text-info me-2"></i>Monnify Bills Payment</h2>
                <div class="muted">Airtime, data, electricity, cable TV, education and other billers.</div>
                <label class="bill-check"><input type="checkbox" name="providers[]" value="monnify" {{ in_array('monnify',$settings->providers(),true) ? 'checked' : '' }}> Enable Monnify for customers</label>
                <label class="bill-check"><input type="checkbox" name="monnify_sandbox" value="1" {{ $settings->exists ? ($settings->monnify_sandbox ? 'checked' : '') : 'checked' }}> Sandbox environment</label>
                <label class="bill-label" for="monnify_api_key">API key {{ filled($settings->monnify_api_key) ? '· saved' : '· required' }}</label><div class="bill-secret-wrap"><input class="bill-input" id="monnify_api_key" name="monnify_api_key" type="password" value="{{ $settings->monnify_api_key }}" autocomplete="new-password" placeholder="Paste Monnify API key"><button class="bill-secret-toggle" type="button" data-secret-toggle="monnify_api_key">Show</button></div>
                <label class="bill-label" for="monnify_secret_key">Client secret {{ filled($settings->monnify_secret_key) ? '· saved' : '' }}</label><div class="bill-secret-wrap"><input class="bill-input" id="monnify_secret_key" name="monnify_secret_key" type="password" value="{{ $settings->monnify_secret_key }}" autocomplete="new-password" placeholder="Paste Monnify client secret"><button class="bill-secret-toggle" type="button" data-secret-toggle="monnify_secret_key">Show</button></div>
                <label class="bill-label" for="monnify_contract_code">Contract code</label><input class="bill-input" id="monnify_contract_code" name="monnify_contract_code" value="{{ old('monnify_contract_code', $settings->monnify_contract_code) }}" autocomplete="off">
                <button class="btn btn-outline-info btn-sm mt-3" type="button" onclick="testProvider('monnify')" {{ $configured['monnify'] ? '' : 'disabled' }}>Test Monnify catalog</button>
            </section>
        </div>
        <div class="bill-actions"><button class="btn btn-success px-4" type="submit"><i class="bi bi-shield-check me-1"></i>Save provider settings</button><span id="provider-test-status" class="bill-status" aria-live="polite">Saved keys are masked. Use Show to reveal them on this admin page.</span></div>
    </form>
</main>
<script>
async function testProvider(provider){const out=document.getElementById('provider-test-status');out.textContent='Checking '+provider+'…';try{const response=await fetch(@json(route('admin.bill-providers.test')),{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json'},body:JSON.stringify({provider})});const data=await response.json();out.textContent=data.message+(data.count!==undefined?' ('+data.count+' options)':'');out.className='bill-status '+(response.ok?'text-success':'text-warning')}catch(e){out.textContent='Could not reach the server. Try again.';out.className='bill-status text-warning'}}
document.querySelectorAll('[data-secret-toggle]').forEach(button=>button.addEventListener('click',()=>{const input=document.getElementById(button.dataset.secretToggle);const reveal=input.type==='password';input.type=reveal?'text':'password';button.textContent=reveal?'Hide':'Show';}));
</script>
@endsection
