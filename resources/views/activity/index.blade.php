@extends('layouts.app')
@section('title','Recent activity · KayXchange')
@section('content')
<style>
.uah{--uah-muted:#9aabb5;max-width:1080px;margin:auto;padding:30px 15px 70px;color:#eef5f2}.uah-hero,.uah-card{border:1px solid rgba(255,255,255,.09);border-radius:22px;background:#121c22;box-shadow:0 18px 50px rgba(0,0,0,.16)}.uah-hero{padding:clamp(22px,4vw,36px);background:radial-gradient(ellipse at 90% 0,rgba(39,211,108,.17),transparent 40%),linear-gradient(135deg,#101a20,#122019)}.uah-kicker{color:#80e9a0;font-size:.7rem;text-transform:uppercase;letter-spacing:.14em;font-weight:850}.uah-title{color:#f6fff8;font-size:clamp(1.65rem,4vw,2.4rem);font-weight:900;letter-spacing:-.04em;margin:.5rem 0}.uah-sub,.uah-muted{color:var(--uah-muted);line-height:1.6}.uah-back{display:inline-flex;gap:8px;align-items:center;margin-bottom:17px;color:#a6c9b2;text-decoration:none;font-weight:750;font-size:.82rem}.uah-card{margin-top:16px;padding:clamp(15px,3vw,24px)}.uah-list{display:grid;gap:10px}.uah-item{display:grid;grid-template-columns:44px minmax(0,1fr);gap:13px;padding:16px;border:1px solid rgba(255,255,255,.075);border-radius:16px;background:rgba(4,12,16,.28)}.uah-icon{width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:rgba(39,211,108,.1);color:#81eaa0;font-size:1.1rem}.uah-main{min-width:0}.uah-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}.uah-title-row{display:flex;align-items:center;gap:9px;flex-wrap:wrap}.uah-item-title{color:#f1f7f4;font-weight:850;font-size:.94rem}.uah-amount{color:#86efa5;font-weight:850;font-size:.92rem}.uah-detail{margin-top:6px;color:#b1c0c6;font-size:.82rem;line-height:1.6;overflow-wrap:anywhere;white-space:pre-line}.uah-values,.uah-meta{display:flex;gap:8px 14px;flex-wrap:wrap;margin-top:9px;color:#899ba4;font-size:.72rem}.uah-values strong{color:#d9e5df}.uah-state{display:inline-flex;padding:5px 9px;border-radius:99px;background:rgba(255,255,255,.06);color:#e3ece7;font-weight:800;font-size:.68rem;text-transform:capitalize}.uah-state.done{background:rgba(39,211,108,.1);color:#8be9a6}.uah-state.bad{background:rgba(251,113,133,.1);color:#fda4af}.uah-link{display:inline-flex;gap:7px;align-items:center;margin-top:11px;color:#8deba8;text-decoration:none;font-weight:800;font-size:.76rem}.uah-link:hover{color:#c5f8d1}.uah-empty{text-align:center;padding:45px 20px;color:#a5b5bc}.uah-empty i{display:block;color:#80e9a0;font-size:1.8rem;margin-bottom:12px}.uah-pagination{margin-top:18px}.uah-pagination nav{display:flex;justify-content:center}@media(max-width:520px){.uah{padding:18px 11px 52px}.uah-item{grid-template-columns:36px minmax(0,1fr);gap:10px;padding:12px}.uah-icon{width:36px;height:36px;border-radius:12px}.uah-top{gap:8px}}
</style>
<main class="uah">
    <a class="uah-back" href="{{ url('/dashboard') }}"><i class="bi bi-arrow-left"></i>Back to dashboard</a>
    <header class="uah-hero"><div class="uah-kicker"><i class="bi bi-activity me-1"></i>Account timeline</div><h1 class="uah-title">Recent activity</h1><p class="uah-sub mb-0">Transactions, wallet changes, support updates, notifications, and successful sign-ins in one place.</p></header>
    <section class="uah-card" aria-label="Full recent activity history">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3"><div><h2 class="h5 fw-bold text-light mb-1">Your activity</h2><div class="uah-muted small">{{ number_format($activities->total()) }} recent events · newest first</div></div><a class="btn btn-sm btn-outline-success" href="{{ route('notifications.index') }}"><i class="bi bi-bell me-1"></i>Notifications</a></div>
        @if($activities->count())<div class="uah-list">
            @foreach($activities as $activity)
                @php
                    $state = strtolower((string)($activity['status'] ?? 'update'));
                    $stateClass = in_array($state,['completed','approved','successful','success','secure'],true)?'done':(in_array($state,['failed','rejected','cancelled','expired'],true)?'bad':'');
                @endphp
                <article class="uah-item">
                    <div class="uah-icon"><i class="bi {{ $activity['icon'] ?? 'bi-activity' }}"></i></div>
                    <div class="uah-main">
                        <div class="uah-top"><div class="uah-title-row"><span class="uah-item-title">{{ $activity['title'] ?? 'Account update' }}</span>@if(isset($activity['amount']) && $activity['amount'] !== null)<span class="uah-amount">{{ ($activity['currency'] ?? 'NGN')==='NGN'?'₦':'' }}{{ number_format((float)$activity['amount'],($activity['currency'] ?? 'NGN')==='NGN'?2:8) }}{{ ($activity['currency'] ?? 'NGN')!=='NGN'?' '.strtoupper($activity['currency']):'' }}</span>@endif</div><span class="uah-state {{ $stateClass }}">{{ str_replace('_',' ',$state) }}</span></div>
                        <div class="uah-detail">{{ $activity['detail'] ?? '' }}</div>
                        @if(isset($activity['amount_ngn']) || isset($activity['amount_usd']))<div class="uah-values">@if(isset($activity['amount_ngn']) && $activity['amount_ngn']!==null)<span>NGN value <strong>₦{{ number_format((float)$activity['amount_ngn'],2) }}</strong></span>@endif @if(isset($activity['amount_usd']) && $activity['amount_usd']!==null)<span>USD value <strong>${{ number_format((float)$activity['amount_usd'],2) }}</strong></span>@endif</div>@endif
                        <div class="uah-meta"><span>{{ $activity['created_at']?->timezone('Africa/Lagos')->format('d M Y · H:i') }}</span>@if(!empty($activity['balance_effect']))<span>{{ $activity['balance_effect'] }}</span>@endif @if(!empty($activity['reference']))<span>Ref {{ $activity['reference'] }}</span>@endif</div>
                        @if(!empty($activity['url']))<a class="uah-link" href="{{ $activity['url'] }}">{{ $activity['action'] ?? 'View details' }} <i class="bi bi-arrow-up-right"></i></a>@endif
                    </div>
                </article>
            @endforeach
        </div><div class="uah-pagination">{{ $activities->links() }}</div>
        @else<div class="uah-empty"><i class="bi bi-stars"></i><strong>No account activity yet</strong><div class="mt-2">Transactions and account updates will appear here when you start using KayXchange.</div></div>@endif
    </section>
</main>
@endsection
