@extends('layouts.app')
@section('title','Bill payment receipt')
@section('content')
<style>.bp-wrap{max-width:760px;margin:0 auto;padding:40px 16px 70px;color:#e8edf5}.bp-receipt{background:#131b25;border:1px solid rgba(255,255,255,.1);border-radius:22px;padding:28px}.bp-ref{color:#97a5b8;font-family:monospace}.bp-state{display:inline-flex;padding:7px 12px;border-radius:99px;background:rgba(0,204,0,.12);color:#70ef70;font-weight:750;text-transform:capitalize}.bp-state.pending,.bp-state.processing{background:rgba(245,158,11,.12);color:#f9c96e}.bp-state.failed_refunded{background:rgba(239,68,68,.12);color:#ff9292}.bp-line{display:flex;justify-content:space-between;gap:20px;padding:13px 0;border-bottom:1px solid rgba(255,255,255,.07)}.bp-line span{color:#98a6b8}.bp-token{background:#0b121a;border:1px solid #334155;padding:14px;border-radius:12px;word-break:break-word;color:#b6ffc0}</style>
<main class="bp-wrap"><section class="bp-receipt">
 <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap"><div><div class="text-success small fw-bold text-uppercase">Bill payment receipt</div><h1 class="h3 text-white mt-2">{{ ucfirst(str_replace('_',' ',$payment->category)) }}</h1><div class="bp-ref">{{ $payment->reference }}</div></div><span class="bp-state {{ $payment->status }}">{{ str_replace('_',' ',$payment->status) }}</span></div>
 <div class="mt-4">
  <div class="bp-line"><span>Provider</span><strong>{{ ucfirst($payment->provider) }}</strong></div>
  <div class="bp-line"><span>Biller / Plan</span><strong class="text-end">{{ $payment->product_code ?: $payment->service_code }}</strong></div>
  <div class="bp-line"><span>Customer</span><strong>{{ $payment->customer_name ?: 'Account holder' }}</strong></div>
  <div class="bp-line"><span>Account</span><strong>{{ $payment->customer_identifier }}</strong></div>
  <div class="bp-line"><span>Amount</span><strong>₦{{ number_format((float)$payment->amount,2) }}</strong></div>
  @if($payment->provider_reference)<div class="bp-line"><span>Provider reference</span><strong>{{ $payment->provider_reference }}</strong></div>@endif
  @if($payment->refunded_at)<div class="alert alert-warning mt-3 mb-0">The provider reported a failed bill. ₦{{ number_format((float)$payment->amount,2) }} has been returned to your NGN balance.</div>@endif
  @if($payment->receipt_token)<div class="mt-4"><div class="small text-success fw-bold mb-2">Electricity token / receipt</div><div class="bp-token">{{ $payment->receipt_token }}</div></div>@endif
  @if($payment->status==='validated')<div class="alert alert-warning mt-4">Your bill request is waiting for an admin to review it. You’ll be able to confirm payment after approval.</div>@endif
  @if($payment->status==='approved')<form method="POST" action="{{ route('bills.purchase',$payment->id) }}" class="mt-4 p-3 rounded-3" style="background:#0b121a;border:1px solid rgba(255,255,255,.08)">@csrf<label for="billPin" class="form-label text-white">Confirm payment with your 4-digit transaction PIN</label><div class="d-flex gap-2 flex-wrap"><input id="billPin" name="pin" class="form-control" type="password" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required style="max-width:220px"><button class="btn btn-success" type="submit">Pay ₦{{ number_format((float)$payment->amount,2) }}</button></div></form>@endif
  @if($payment->status==='rejected')<div class="alert alert-danger mt-4">This bill request was declined by the admin. No amount was charged.</div>@endif
  @if(in_array($payment->status,['pending','processing'],true))<div class="alert alert-warning mt-4">We’re confirming this payment with the provider. Your wallet debit is held while the status is uncertain. Do not submit the bill again.</div><form method="POST" action="{{ route('bills.requery',$payment->id) }}">@csrf<button class="btn btn-outline-warning" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Check provider status</button></form>@endif
 </div>
 <div class="d-flex gap-2 flex-wrap mt-4"><a class="btn btn-success" href="{{ route('bills.index') }}">Pay another bill</a><a class="btn btn-outline-light" href="{{ route('bills.history') }}">View history</a><a class="btn btn-outline-secondary" href="{{ url('/dashboard') }}">Dashboard</a></div>
</section></main>
@endsection
