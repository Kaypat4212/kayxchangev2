@extends('layouts.app')
@section('title', 'Naira wallet activity')
@section('content')
<style>
.naira-activity{max-width:1100px;margin:auto;padding:28px 16px 70px;color:#edf5ef}.naira-hero,.naira-panel{border:1px solid rgba(255,255,255,.09);border-radius:20px;background:#121a16}.naira-hero{padding:24px;background:radial-gradient(ellipse at 90% 0,rgba(0,204,80,.2),transparent 40%),linear-gradient(130deg,#111c15,#18241c)}.naira-hero h1{font-weight:850;letter-spacing:-.04em}.naira-muted{color:#9caea1}.naira-panel{margin-top:16px;overflow:hidden}.naira-filter{display:grid;grid-template-columns:repeat(5,minmax(0,1fr)) auto auto;gap:10px;align-items:end;padding:16px;border-bottom:1px solid #ffffff12}.naira-label{display:block;color:#aebdb2;font-size:.7rem;font-weight:750;margin-bottom:5px}.naira-input{width:100%;min-height:42px;border-radius:10px;border:1px solid #35443a;background:#0b120e;color:#eff8f0;padding:9px 10px}.naira-btn{display:inline-flex;justify-content:center;align-items:center;gap:7px;border-radius:10px;padding:10px 14px;text-decoration:none;font-size:.82rem;font-weight:750;border:1px solid #ffffff22;background:#18251c;color:#e8f3ea}.naira-btn-primary{background:#19cc62;color:#07150b;border-color:#19cc62}.naira-row{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(130px,.7fr) minmax(120px,.7fr) minmax(150px,1fr);gap:12px;align-items:center;padding:16px 18px;border-bottom:1px solid #ffffff0d}.naira-row:last-child{border-bottom:0}.naira-title{font-weight:800;color:#f1f8f2}.naira-meta{font-size:.75rem;color:#9caea1;margin-top:4px;overflow-wrap:anywhere}.naira-amount{font-weight:850;font-variant-numeric:tabular-nums}.naira-credit{color:#5be28b}.naira-debit{color:#ffbd6b}.naira-direction{display:inline-flex;align-items:center;gap:6px;font-size:.72rem;font-weight:800;text-transform:capitalize}.naira-balance{font-size:.8rem;color:#c7d3ca}.naira-empty{padding:48px 18px;text-align:center;color:#a6b4aa}.naira-empty i{font-size:2rem;color:#4be883}.naira-pagination{padding:14px 18px}@media(max-width:800px){.naira-filter{grid-template-columns:repeat(2,minmax(0,1fr))}.naira-row{grid-template-columns:minmax(0,1fr) auto}.naira-row .naira-balance,.naira-row .naira-date{grid-column:1/-1}.naira-row .naira-amount{text-align:right}}@media(max-width:460px){.naira-filter{grid-template-columns:1fr 1fr}.naira-filter .wide{grid-column:1/-1}.naira-hero{padding:18px}.naira-row{padding:13px}}
</style>
<main class="naira-activity">
    <header class="naira-hero">
        <div class="text-success small fw-bold text-uppercase mb-2"><i class="bi bi-clock-history me-1"></i>Wallet activity</div>
        <h1 class="h3 mb-2">Naira wallet activity</h1>
        <p class="naira-muted mb-0">See what came into or left your Naira wallet, which transaction caused it, and the balance after each entry.</p>
    </header>

    <section class="naira-panel" aria-label="Naira wallet transaction activity">
        <form class="naira-filter" method="GET" action="{{ route('wallet.naira-activity') }}">
            <div class="wide"><label class="naira-label" for="activitySearch">Search activity</label><input class="naira-input" id="activitySearch" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Reference or source"></div>
            <div><label class="naira-label" for="activityDirection">Money moved</label><select class="naira-input" id="activityDirection" name="direction"><option value="">All</option><option value="credit" @selected(($filters['direction'] ?? '') === 'credit')>Came in</option><option value="debit" @selected(($filters['direction'] ?? '') === 'debit')>Went out</option></select></div>
            <div><label class="naira-label" for="activitySource">Transaction type</label><select class="naira-input" id="activitySource" name="source"><option value="">All types</option>@foreach($sourceOptions as $source)<option value="{{ $source }}" @selected(($filters['source'] ?? '') === $source)>{{ \Illuminate\Support\Str::headline(str_replace('.', ' ', $source)) }}</option>@endforeach</select></div>
            <div><label class="naira-label" for="activityFrom">From</label><input class="naira-input" type="date" id="activityFrom" name="from" value="{{ $filters['from'] ?? '' }}"></div>
            <div><label class="naira-label" for="activityTo">To</label><input class="naira-input" type="date" id="activityTo" name="to" value="{{ $filters['to'] ?? '' }}"></div>
            <button class="naira-btn naira-btn-primary" type="submit"><i class="bi bi-funnel"></i>Filter</button>
            <a class="naira-btn" href="{{ route('wallet.naira-activity') }}">Reset</a>
        </form>

        @if(!$ledgerReady)
            <div class="naira-empty"><i class="bi bi-hourglass-split d-block mb-2"></i><strong>Activity history is being prepared</strong><p class="naira-muted mb-0 mt-2">Your current Naira balance remains available. Transaction activity will appear after wallet history is enabled.</p></div>
        @elseif($activity->isEmpty())
            <div class="naira-empty"><i class="bi bi-wallet2 d-block mb-2"></i><strong>No matching Naira activity</strong><p class="naira-muted mb-0 mt-2">New deposits, withdrawals, trades, transfers, bills, and wallet adjustments will appear here.</p></div>
        @else
            @foreach($activity as $entry)
                @php
                    $credit = strtolower((string)$entry->entry_type) === 'credit';
                    $source = (string)$entry->source;
                    $labels = [
                        'deposit.approved'=>'Naira deposit', 'withdrawal.approved'=>'Naira withdrawal',
                        'sell.completed'=>'Crypto sell payout', 'crypto_deposit.completed'=>'Crypto deposit converted to Naira',
                        'gift_card.purchase'=>'Gift card purchase', 'gift_card.purchase_refund'=>'Gift card purchase refund',
                        'gift_card.sale_credit'=>'Gift card sale payout', 'bill_payment'=>'Bill payment', 'bill_payment.debit'=>'Bill payment',
                        'bill_payment.completed'=>'Bill payment', 'bill_payment.refund'=>'Bill payment refund',
                        'usd_wallet.withdraw_to_ngn'=>'Crypto converted to Naira', 'p2p.sent'=>'Wallet transfer sent',
                        'p2p.received'=>'Wallet transfer received', 'referral.reward'=>'Referral reward',
                        'opening_balance'=>'Opening balance', 'admin.balance_adjustment'=>'Admin balance adjustment',
                        'balance.adjustment'=>'Balance adjustment'
                    ];
                    $label = $labels[$source] ?? \Illuminate\Support\Str::headline(str_replace('.', ' ', $source));
                    $reference = $entry->reference ?: data_get($entry->metadata, 'reference');
                @endphp
                <article class="naira-row">
                    <div><div class="naira-title">{{ $label }}</div><div class="naira-meta">{{ $source }}@if($reference) · Ref {{ $reference }}@endif</div></div>
                    <div class="naira-direction {{ $credit ? 'naira-credit':'naira-debit' }}"><i class="bi {{ $credit ? 'bi-arrow-down-left-circle':'bi-arrow-up-right-circle' }}"></i>{{ $credit ? 'Came in':'Went out' }}</div>
                    <div class="naira-amount {{ $credit ? 'naira-credit':'naira-debit' }}">{{ $credit ? '+':'−' }}₦{{ number_format((float)$entry->amount, 2) }}</div>
                    <div class="naira-date"><div class="naira-balance">Balance after · ₦{{ number_format((float)$entry->balance_after, 2) }}</div><div class="naira-meta">{{ optional($entry->created_at)->format('d M Y · H:i') }}</div></div>
                </article>
            @endforeach
            <div class="naira-pagination">{{ $activity->links() }}</div>
        @endif
    </section>
    <div class="d-flex flex-wrap gap-2 mt-3"><a class="naira-btn" href="{{ route('transactions.history') }}"><i class="bi bi-list-ul"></i>All transactions</a><a class="naira-btn" href="{{ route('wallet.index') }}"><i class="bi bi-wallet2"></i>Wallet overview</a></div>
</main>
@endsection
