@extends('adminnavlayout')
@section('content')
<style>
.md-wrap{max-width:1200px;margin:0 auto;padding:28px 16px 60px;color:#e8eef6}.md-hero,.md-card{background:#131b25;border:1px solid rgba(255,255,255,.09);border-radius:19px}.md-hero{padding:24px;background:radial-gradient(circle at 90% 5%,rgba(0,204,0,.16),transparent 34%),linear-gradient(130deg,#101a27,#121d19);margin-bottom:18px}.md-hero h1{color:#fff;font-weight:850}.md-muted{color:#98a6b7}.md-card{padding:20px;margin-bottom:14px}.md-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}.md-info{padding:12px;border-radius:12px;background:#0d151e;border:1px solid rgba(255,255,255,.07)}.md-info small{display:block;color:#91a0b2}.md-info strong{overflow-wrap:anywhere;color:#eff5fa}.md-input{width:100%;background:#080f16;color:#fff;border:1px solid #344455;border-radius:9px;padding:10px}.md-form{display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end}.md-actions{display:flex;gap:8px;align-items:end}@media(max-width:720px){.md-form{grid-template-columns:1fr}.md-actions{align-items:stretch}.md-actions>*{flex:1}}
</style>
<main class="md-wrap">
 <header class="md-hero d-flex justify-content-between align-items-center gap-3 flex-wrap"><div><div class="text-success small fw-bold text-uppercase">Deposit reconciliation</div><h1 class="h2 mt-2 mb-2">Crypto deposit activity</h1><p class="md-muted mb-0">All NOWPayments, Cryptomus and manual crypto deposits appear here. Only records awaiting review can be approved; balances remain protected by provider verification.</p></div><div class="d-flex gap-2 flex-wrap align-items-center"><span class="badge text-bg-warning">{{ $reviewCount }} need review</span><span class="badge text-bg-secondary">{{ $deposits->total() }} total</span><a href="{{ route('admin.deposits.index') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-list-ul me-1"></i>All deposits</a></div></header>
 @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
 @forelse($deposits as $deposit)
  @php($data=$deposit->payment_data ?? []) @php($isManual=data_get($data,'provider')==='manual') @php($network=strtoupper((string)data_get($data,'deposit_input.network',''))) @php($explorerBase=match($network){'BTC'=>'https://blockstream.info/tx/','ERC20'=>'https://etherscan.io/tx/','TRC20'=>'https://tronscan.org/#/transaction/','BEP20'=>'https://bscscan.com/tx/','SOL'=>'https://solscan.io/tx/',default=>null})
  @php($expectedCrypto=(float)data_get($data,'expected_crypto_amount',0)) @php($providerStatus=strtolower((string)data_get($data,'provider_status','pending'))) @php($latest=data_get($data,'latest_webhook',[])) @php($actualCrypto=(float)($deposit->received_amount ?: data_get($latest,'actually_paid',data_get($latest,'payer_amount',0)))) @php($remainingCrypto=max(0,$expectedCrypto-$actualCrypto)) @php($isProviderFinal=in_array($providerStatus,['finished','paid','paid_over'],true)) @php($paymentId=data_get($data,'provider_payment_id')) @php($depositValuation=data_get($data,'deposit_valuation',[])) @php($quotedPrice=(float)data_get($data,'deposit_input.quoted_market_price_usd',0)) @php($receivedUsd=(float)data_get($depositValuation,'market_value_usd',($quotedPrice>0?$actualCrypto*$quotedPrice:0)))
  <section class="md-card">
   <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3"><div><div class="small text-success fw-bold">DEPOSIT #{{ $deposit->id }} · {{ strtoupper($deposit->currency) }} / {{ data_get($data,'deposit_input.network','—') }}</div><h2 class="h5 text-white mt-1 mb-1">{{ $deposit->user?->name ?? 'Unknown user' }}</h2><div class="md-muted small">{{ $deposit->user?->email ?? '—' }} · Created {{ $deposit->created_at?->format('d M Y, H:i') }}</div></div><span class="badge text-bg-warning">{{ $deposit->status === 'awaiting_admin_review' ? 'Awaiting admin approval' : ucfirst($deposit->status) }} · {{ str_replace('_',' ',data_get($data,'provider_status','pending')) }}</span></div>
   <div class="md-grid mb-3">
    <div class="md-info"><small>Invoice value</small><strong>${{ number_format((float)$deposit->expected_amount,2) }} USD</strong></div>
    <div class="md-info"><small>Expected transfer</small><strong>{{ number_format($expectedCrypto,8) }} {{ strtoupper($deposit->currency) }} · {{ $network ?: 'Network unavailable' }}</strong></div>
    <div class="md-info"><small>Provider-confirmed amount</small><strong>{{ number_format($actualCrypto,8) }} {{ strtoupper($deposit->currency) }}</strong>
     @if($receivedUsd>0)<div class="md-muted small">Approx. ${{ number_format($receivedUsd,2) }} USD at deposit quote</div>@endif
    </div>
    <div class="md-info"><small>Provider status</small><strong>{{ str_replace('_',' ',$providerStatus) }}</strong>
     @if($isProviderFinal && $remainingCrypto>0)<div class="text-success small mt-1">Provider marked complete; no more payment is expected.</div>
     @elseif(!$isProviderFinal && $remainingCrypto>0)<div class="text-warning small mt-1">Approx. {{ number_format($remainingCrypto,8) }} {{ strtoupper($deposit->currency) }} remaining.</div>
     @endif
    </div>
    <div class="md-info"><small>NOWPayments payment ID</small><strong class="font-monospace">{{ $paymentId ?: 'Not available' }}</strong></div>
    <div class="md-info"><small>Settlement destination</small><strong>{{ strtoupper((string)data_get($data,'deposit_input.settlement_preference','usd')) }} {{ data_get($data,'deposit_input.settlement_preference','usd')==='ngn' ? 'wallet' : 'crypto portfolio' }}</strong></div>
    <div class="md-info"><small>Receiving address</small><strong>{{ $deposit->payment_address ?: 'Not available' }}</strong></div>
    @php($txHash=$deposit->transaction_hash ?: data_get($latest,'txid'))
    <div class="md-info"><small>Transaction hash</small><strong>
     @if($txHash && $explorerBase)<a class="text-success" href="{{ $explorerBase.rawurlencode($txHash) }}" target="_blank" rel="noopener">{{ $txHash }} <i class="bi bi-box-arrow-up-right"></i></a>
     @else{{ $txHash ?: 'Not reported yet' }}
     @endif
    </strong></div>
    @if(data_get($data,'bitcoin_confirmations')!==null)
     <div class="md-info"><small>Bitcoin confirmations</small><strong>{{ (int)data_get($data,'bitcoin_confirmations',0) }} / {{ (int)data_get($data,'bitcoin_required_confirmations',3) }}</strong></div>
    @endif
   </div>
   @if($deposit->status === 'awaiting_admin_review')
    <form method="POST" action="{{ route('admin.manual-crypto-deposits.approve',$deposit->id) }}" class="d-flex gap-2 flex-wrap">@csrf<button class="btn btn-success" onclick="return confirm('Approve the confirmed deposit and credit the user wallet?')"><i class="bi bi-check2-circle me-1"></i>Approve & credit wallet</button></form>
    <form method="POST" action="{{ route('admin.manual-crypto-deposits.reject',$deposit->id) }}" class="d-flex gap-2 mt-2">@csrf<input class="md-input" name="admin_note" maxlength="500" placeholder="Reason (optional)"><button class="btn btn-outline-danger" onclick="return confirm('Reject this deposit? No wallet credit will be made.')">Reject</button></form>
   @elseif($deposit->status === 'pending' && $isManual)
    <form method="POST" action="{{ route('admin.manual-crypto-deposits.approve',$deposit->id) }}" class="md-form">@csrf
     <div><label class="md-muted small" for="received_{{ $deposit->id }}">Verified amount received</label><input class="md-input" type="number" min="0.00000001" step="any" id="received_{{ $deposit->id }}" name="received_amount" value="{{ old('received_amount',data_get($data,'expected_crypto_amount')) }}" required></div>
     <div><label class="md-muted small" for="tx_{{ $deposit->id }}">Verified transaction hash</label><input class="md-input" type="text" id="tx_{{ $deposit->id }}" name="transaction_hash" value="{{ old('transaction_hash',$deposit->transaction_hash) }}" minlength="20" maxlength="150" required></div>
     <div class="md-actions"><button class="btn btn-success" onclick="return confirm('Confirm you verified this transfer on the correct network and amount?')"><i class="bi bi-check2-circle me-1"></i>Approve & credit</button></div>
    </form>
    <form method="POST" action="{{ route('admin.manual-crypto-deposits.reject',$deposit->id) }}" class="d-flex gap-2 mt-2">@csrf<input class="md-input" name="admin_note" maxlength="500" placeholder="Reason (optional)"><button class="btn btn-outline-danger" onclick="return confirm('Reject this fallback deposit?')">Reject</button></form>
   @elseif($deposit->status === 'pending')
    <div class="md-muted small"><i class="bi bi-arrow-repeat me-1"></i>Provider is monitoring this invoice. Refresh deposit status from the customer status page or review it in the main deposits table.</div>
   @endif
  </section>
 @empty<div class="md-card text-center py-5"><i class="bi bi-check-circle text-success fs-2"></i><h2 class="h5 text-white mt-2">No deposits awaiting review</h2><p class="md-muted mb-0">Confirmed crypto deposits and manual address payments awaiting approval will appear here.</p></div>@endforelse
 <div class="mt-3">{{ $deposits->links() }}</div>
</main>
@endsection
