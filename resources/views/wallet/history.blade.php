@extends('layouts.app')

@section('title', 'Wallet activity')

@section('content')
@php
    $depositCount = $deposits->total();
    $withdrawalCount = $withdrawals->total();
@endphp
<style>
.wh-page{--wh-bg:#0a100d;--wh-card:#111a15;--wh-card2:#0d1511;--wh-border:rgba(211,238,219,.09);--wh-muted:#98a89d;--wh-text:#f2f7f3;--wh-green:#78e795;max-width:1160px;margin:0 auto;padding:24px 14px 76px;color:var(--wh-text)}
.wh-page *{box-sizing:border-box}.wh-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:30px;border:1px solid var(--wh-border);border-radius:26px;background:radial-gradient(ellipse at 90% 0%,rgba(45,191,92,.2),transparent 37%),linear-gradient(130deg,#142019,#101a16 58%,#111b20);box-shadow:0 24px 65px rgba(0,0,0,.2)}.wh-hero:after{content:"";position:absolute;z-index:-1;width:220px;height:220px;right:7%;bottom:-175px;border-radius:50%;border:1px solid rgba(120,231,149,.18);box-shadow:0 0 0 24px rgba(120,231,149,.025),0 0 0 50px rgba(120,231,149,.02)}.wh-eyebrow{display:flex;align-items:center;gap:8px;color:var(--wh-green);font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.wh-eyebrow i{font-size:1rem}.wh-hero h1{margin:10px 0 6px;color:#fff;font-size:clamp(1.65rem,4vw,2.45rem);font-weight:800;letter-spacing:-.045em}.wh-hero p{max-width:560px;margin:0;color:#aab8ae;font-size:.91rem;line-height:1.65}.wh-hero-action{flex:none;display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:12px 17px;border-radius:12px;background:linear-gradient(135deg,#96f3aa,#56d77c);color:#09200f!important;text-decoration:none;font-weight:800;font-size:.86rem;box-shadow:0 10px 24px rgba(55,202,97,.17);transition:transform .2s,filter .2s}.wh-hero-action:hover{transform:translateY(-2px);filter:brightness(1.04)}
.wh-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px;margin:16px 0 23px}.wh-stat{display:flex;align-items:center;gap:13px;min-height:83px;padding:16px;border:1px solid var(--wh-border);border-radius:17px;background:linear-gradient(145deg,rgba(20,31,25,.96),rgba(14,22,18,.96))}.wh-stat-icon{width:43px;height:43px;flex:none;display:grid;place-items:center;border-radius:13px;background:rgba(120,231,149,.1);color:var(--wh-green);font-size:1.1rem}.wh-stat-icon.out{background:rgba(250,166,120,.1);color:#ffb08b}.wh-stat-icon.wait{background:rgba(244,197,96,.1);color:#f4c560}.wh-stat-label{display:block;color:var(--wh-muted);font-size:.74rem;font-weight:600}.wh-stat-value{display:block;margin-top:3px;color:#f1f6f2;font-size:1.16rem;line-height:1.2;font-weight:800}
.wh-tabs-wrap{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:13px}.wh-tabs{display:inline-flex;gap:5px;padding:5px;border:1px solid var(--wh-border);border-radius:14px;background:#0e1611}.wh-tab{display:inline-flex;align-items:center;gap:8px;min-height:40px;padding:8px 15px;border:0;border-radius:10px;background:transparent;color:#9eaca2;font-family:inherit;font-size:.82rem;font-weight:600;cursor:pointer;transition:background .18s,color .18s}.wh-tab:hover{color:#fff}.wh-tab[aria-selected=true]{background:#1b2a20;color:#fff;box-shadow:inset 0 0 0 1px rgba(120,231,149,.12)}.wh-tab-count{display:inline-grid;min-width:21px;height:21px;place-items:center;padding:0 5px;border-radius:20px;background:rgba(255,255,255,.07);color:#b4c1b8;font-size:.67rem;font-weight:800}.wh-tab[aria-selected=true] .wh-tab-count{background:rgba(120,231,149,.15);color:var(--wh-green)}.wh-hint{color:var(--wh-muted);font-size:.76rem}
.wh-list{overflow:hidden;border:1px solid var(--wh-border);border-radius:20px;background:linear-gradient(160deg,#111a15,#0e1511);box-shadow:0 18px 55px rgba(0,0,0,.18)}.wh-list-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:19px 22px;border-bottom:1px solid var(--wh-border)}.wh-list-title{margin:0;color:#f4f8f5;font-size:1rem;font-weight:750}.wh-list-subtitle{margin:4px 0 0;color:var(--wh-muted);font-size:.75rem}.wh-list-total{padding:6px 10px;border:1px solid var(--wh-border);border-radius:999px;color:#bac8bd;font-size:.72rem;font-weight:700}
.wh-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;padding:20px 22px;border-bottom:1px solid rgba(211,238,219,.065);transition:background .18s}.wh-item:last-child{border-bottom:0}.wh-item:hover{background:rgba(255,255,255,.018)}.wh-item-main{display:flex;align-items:flex-start;gap:14px;min-width:0}.wh-coin-icon{width:46px;height:46px;flex:none;display:grid;place-items:center;border-radius:15px;background:rgba(120,231,149,.1);color:#90eaa6;font-size:1.1rem}.wh-coin-icon.withdraw{background:rgba(250,166,120,.1);color:#ffad89}.wh-item-copy{min-width:0}.wh-item-title{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin:1px 0 5px;color:#f4f7f5;font-size:.94rem;font-weight:750}.wh-network{padding:3px 7px;border:1px solid rgba(255,255,255,.09);border-radius:7px;color:#aab7ae;font-size:.64rem;font-weight:700}.wh-date{color:#9ba99f;font-size:.75rem}.wh-meta{display:flex;flex-wrap:wrap;gap:7px 14px;margin-top:11px;color:#93a198;font-size:.72rem}.wh-meta span{display:inline-flex;align-items:center;gap:5px;min-width:0}.wh-meta i{color:#6d8073}.wh-mono{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;overflow-wrap:anywhere}.wh-item-side{display:flex;flex-direction:column;align-items:flex-end;justify-content:center;gap:9px;text-align:right}.wh-amount{color:#f5faf6;font-size:.98rem;font-weight:800;white-space:nowrap}.wh-amount.in{color:#89e89f}.wh-amount.out{color:#ffae8b}.wh-subamount{color:#9ca99f;font-size:.7rem}.wh-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid transparent;border-radius:999px;font-size:.68rem;font-weight:750;white-space:nowrap}.wh-status:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}.wh-status.success{color:#8ce8a1;background:rgba(38,173,81,.1);border-color:rgba(69,211,112,.16)}.wh-status.pending{color:#f4d27a;background:rgba(234,179,8,.09);border-color:rgba(234,179,8,.15)}.wh-status.processing{color:#91c8ff;background:rgba(59,130,246,.1);border-color:rgba(59,130,246,.18)}.wh-status.failed{color:#ffa0a0;background:rgba(239,68,68,.09);border-color:rgba(239,68,68,.17)}.wh-status.neutral{color:#b0bdb3;background:rgba(255,255,255,.06);border-color:var(--wh-border)}.wh-track{display:inline-flex;align-items:center;gap:6px;color:#80df96!important;text-decoration:none;font-size:.72rem;font-weight:750}.wh-track:hover{color:#b1f7bf!important}.wh-pagination{padding:15px 20px;border-top:1px solid var(--wh-border)}.wh-pagination nav{display:flex;justify-content:center}.wh-pagination svg{width:16px;height:16px}.wh-pagination a,.wh-pagination span[aria-current=page] span{background:#17231b!important;border-color:rgba(255,255,255,.08)!important;color:#c8d5cb!important}.wh-pagination span[aria-current=page] span{color:#89e89f!important}
.wh-empty{display:grid;justify-items:center;text-align:center;padding:54px 20px}.wh-empty-icon{width:62px;height:62px;display:grid;place-items:center;border-radius:20px;background:rgba(120,231,149,.08);color:#7bdd92;font-size:1.45rem}.wh-empty h3{margin:16px 0 5px;color:#f5f8f5;font-size:1rem;font-weight:750}.wh-empty p{max-width:360px;margin:0;color:var(--wh-muted);font-size:.8rem;line-height:1.65}.wh-empty a{margin-top:18px;color:#08190c!important;text-decoration:none;font-size:.78rem;font-weight:800;background:#82e797;border-radius:10px;padding:10px 14px}
body.light-mode .wh-page{--wh-card:#fff;--wh-card2:#f8fcf9;--wh-border:rgba(15,50,25,.11);--wh-muted:#64766a;--wh-text:#18271d}body.light-mode .wh-hero{background:radial-gradient(ellipse at 90% 0%,rgba(45,191,92,.16),transparent 37%),linear-gradient(130deg,#f5fff7,#eff8f1 58%,#f4faf5)}body.light-mode .wh-hero h1,body.light-mode .wh-list-title,body.light-mode .wh-item-title,body.light-mode .wh-amount,body.light-mode .wh-stat-value{color:#17251b}.wh-page body.light-mode{color:#17251b}body.light-mode .wh-stat,body.light-mode .wh-list{background:#fff}body.light-mode .wh-tabs{background:#edf5ef}body.light-mode .wh-tab[aria-selected=true]{background:#fff;color:#16251a}body.light-mode .wh-item:hover{background:#f8fcf9}
@media(max-width:700px){.wh-page{padding:18px 12px 70px}.wh-hero{align-items:flex-start;flex-direction:column;padding:22px}.wh-hero-action{width:100%}.wh-stats{gap:8px;margin:12px 0 18px}.wh-stat{min-height:74px;gap:9px;padding:11px}.wh-stat-icon{width:36px;height:36px;border-radius:11px;font-size:.95rem}.wh-stat-label{font-size:.64rem}.wh-stat-value{font-size:1rem}.wh-tabs-wrap{align-items:flex-start;flex-direction:column}.wh-hint{display:none}.wh-tabs{width:100%}.wh-tab{flex:1;justify-content:center;padding:8px 9px}.wh-list{border-radius:16px}.wh-list-head{padding:16px}.wh-item{grid-template-columns:minmax(0,1fr);gap:13px;padding:16px}.wh-item-side{align-items:flex-start;flex-direction:row;justify-content:space-between;gap:10px;padding-left:60px;text-align:left}.wh-meta{gap:7px 10px}.wh-pagination{padding:12px}.wh-empty{padding:44px 18px}}
@media(max-width:380px){.wh-stat{align-items:flex-start;flex-direction:column}.wh-stat-icon{width:31px;height:31px}.wh-stat-value{font-size:.9rem}.wh-meta{font-size:.67rem}.wh-item-side{padding-left:0}}
</style>

<main class="wh-page">
    <header class="wh-hero">
        <div>
            <div class="wh-eyebrow"><i class="bi bi-activity"></i> Wallet activity</div>
            <h1>Transaction history</h1>
            <p>Keep track of crypto moving in and out of your wallets. Check payment details, review request progress, and follow deposits until they’re settled.</p>
        </div>
        <a href="{{ route('wallet.index') }}" class="wh-hero-action"><i class="bi bi-wallet2"></i> Open my wallet</a>
    </header>

    <section class="wh-stats" aria-label="Wallet activity summary">
        <article class="wh-stat"><span class="wh-stat-icon"><i class="bi bi-arrow-down-left"></i></span><span><span class="wh-stat-label">Crypto deposits</span><strong class="wh-stat-value">{{ number_format($depositCount) }}</strong></span></article>
        <article class="wh-stat"><span class="wh-stat-icon out"><i class="bi bi-arrow-up-right"></i></span><span><span class="wh-stat-label">Crypto withdrawals</span><strong class="wh-stat-value">{{ number_format($withdrawalCount) }}</strong></span></article>
        <article class="wh-stat"><span class="wh-stat-icon wait"><i class="bi bi-hourglass-split"></i></span><span><span class="wh-stat-label">Awaiting an update</span><strong class="wh-stat-value">{{ number_format($awaitingCount) }}</strong></span></article>
    </section>

    <div class="wh-tabs-wrap">
        <div class="wh-tabs" role="tablist" aria-label="Transaction type">
            <button type="button" class="wh-tab" id="deposits-tab" role="tab" aria-controls="deposits-content" aria-selected="true"><i class="bi bi-arrow-down-left-circle"></i> Deposits <span class="wh-tab-count">{{ $depositCount }}</span></button>
            <button type="button" class="wh-tab" id="withdrawals-tab" role="tab" aria-controls="withdrawals-content" aria-selected="false"><i class="bi bi-arrow-up-right-circle"></i> Withdrawals <span class="wh-tab-count">{{ $withdrawalCount }}</span></button>
        </div>
        <span class="wh-hint" id="wh-sync-label" role="status" aria-live="polite"><i class="bi bi-arrow-repeat me-1"></i>Auto-sync is on · Updated just now</span>
    </div>

    <section id="deposits-content" class="wh-list" role="tabpanel" aria-labelledby="deposits-tab">
        <header class="wh-list-head"><div><h2 class="wh-list-title">Crypto deposits</h2><p class="wh-list-subtitle">Incoming transfers and their wallet credit status.</p></div><span class="wh-list-total">{{ number_format($depositCount) }} total</span></header>
        @forelse($deposits as $deposit)
            @php
                $depositInput = data_get($deposit->payment_data, 'deposit_input', []);
                $depositValuation = data_get($deposit->payment_data, 'deposit_valuation', []);
                $depositStatus = match($deposit->status) {
                    'completed' => ['Completed', 'success'],
                    'pending' => [data_get($deposit->payment_data, 'provider_status') === 'partially_paid' ? 'Partially paid' : 'Awaiting payment', 'pending'],
                    'awaiting_admin_review' => ['Awaiting admin review', 'pending'],
                    'failed', 'expired' => [ucfirst($deposit->status), 'failed'],
                    default => [ucfirst(str_replace('_', ' ', $deposit->status)), 'neutral'],
                };
            @endphp
            <article class="wh-item">
                <div class="wh-item-main">
                    <span class="wh-coin-icon"><i class="bi bi-currency-bitcoin"></i></span>
                    <div class="wh-item-copy">
                        <h3 class="wh-item-title">{{ strtoupper($deposit->currency) }} deposit @if(!empty($depositInput['network']))<span class="wh-network">{{ strtoupper($depositInput['network']) }}</span>@endif</h3>
                        <div class="wh-date">{{ $deposit->created_at?->format('M d, Y · H:i') }}</div>
                        <div class="wh-meta">
                            @if(!empty($depositInput))<span><i class="bi bi-pencil-square"></i>Entered {{ number_format((float)($depositInput['input_amount'] ?? 0), ($depositInput['input_type'] ?? '') === 'crypto' ? 8 : 2) }} {{ strtoupper($depositInput['input_currency'] ?? 'USD') }}</span>@endif
                            @if($deposit->transaction_hash)<span class="wh-mono" title="{{ $deposit->transaction_hash }}"><i class="bi bi-link-45deg"></i>TX {{ Str::limit($deposit->transaction_hash, 22) }}</span>@endif
                            @if(!empty($depositValuation['market_value_usd']))<span><i class="bi bi-graph-up-arrow"></i>Deposit value ${{ number_format((float)$depositValuation['market_value_usd'], 2) }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="wh-item-side">
                    <div>
                        <div class="wh-amount {{ $deposit->status === 'completed' ? 'in' : '' }}">
                            @if($deposit->currency === 'BTC' && $deposit->ngn_credited_amount)
                                +₦{{ number_format((float)$deposit->ngn_credited_amount, 2) }}
                            @elseif($deposit->received_amount)
                                +{{ number_format((float)$deposit->received_amount, 8) }} {{ strtoupper($deposit->currency) }}
                            @else
                                ${{ number_format((float)$deposit->expected_amount, 2) }} USD
                            @endif
                        </div>
                        @if(!$deposit->received_amount && $deposit->status !== 'completed')<div class="wh-subamount">Requested deposit</div>@endif
                    </div>
                    <div class="text-end">
                        <span class="wh-status {{ $depositStatus[1] }}">{{ $depositStatus[0] }}</span>
                        <a class="wh-track mt-2" href="{{ route('wallet.deposit.success', $deposit->id) }}">View details <i class="bi bi-arrow-up-right"></i></a>
                    </div>
                </div>
            </article>
        @empty
            <div class="wh-empty"><span class="wh-empty-icon"><i class="bi bi-box-arrow-in-down"></i></span><h3>No crypto deposits yet</h3><p>Your incoming crypto transfers will appear here, along with their confirmation and credit status.</p><a href="{{ route('wallet.deposit') }}"><i class="bi bi-plus-lg me-1"></i>Start a deposit</a></div>
        @endforelse
        @if($deposits->hasPages())<div class="wh-pagination">{{ $deposits->withQueryString()->links() }}</div>@endif
    </section>

    <section id="withdrawals-content" class="wh-list" role="tabpanel" aria-labelledby="withdrawals-tab" hidden>
        <header class="wh-list-head"><div><h2 class="wh-list-title">Crypto withdrawals</h2><p class="wh-list-subtitle">Outgoing transfers to your saved destination addresses.</p></div><span class="wh-list-total">{{ number_format($withdrawalCount) }} total</span></header>
        @forelse($withdrawals as $withdrawal)
            @php
                $withdrawalStatus = match($withdrawal->status) {
                    'completed' => ['Completed', 'success'],
                    'pending' => ['Awaiting admin review', 'pending'],
                    'processing' => ['Processing payout', 'processing'],
                    'rejected' => ['Declined', 'failed'],
                    default => [ucfirst(str_replace('_', ' ', $withdrawal->status)), 'neutral'],
                };
            @endphp
            <article class="wh-item">
                <div class="wh-item-main">
                    <span class="wh-coin-icon withdraw"><i class="bi bi-send"></i></span>
                    <div class="wh-item-copy">
                        <h3 class="wh-item-title">{{ strtoupper($withdrawal->wallet?->currency ?? 'Crypto') }} withdrawal <span class="wh-network">{{ strtoupper((string)$withdrawal->network) }}</span></h3>
                        <div class="wh-date">{{ $withdrawal->created_at?->format('M d, Y · H:i') }}</div>
                        <div class="wh-meta">
                            <span class="wh-mono" title="{{ $withdrawal->address }}"><i class="bi bi-geo-alt"></i>To {{ Str::limit($withdrawal->address, 26) }}</span>
                            @if($withdrawal->transaction_hash)<span class="wh-mono" title="{{ $withdrawal->transaction_hash }}"><i class="bi bi-link-45deg"></i>TX {{ Str::limit($withdrawal->transaction_hash, 22) }}</span>@endif
                            @if($withdrawal->rejected_reason)<span><i class="bi bi-info-circle"></i>{{ Str::limit($withdrawal->rejected_reason, 90) }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="wh-item-side">
                    <div><div class="wh-amount out">−{{ number_format((float)$withdrawal->amount, 8) }} {{ strtoupper($withdrawal->wallet?->currency ?? '') }}</div>@if($withdrawal->fee > 0)<div class="wh-subamount">Fee {{ number_format((float)$withdrawal->fee, 8) }} {{ strtoupper($withdrawal->wallet?->currency ?? '') }}</div>@endif</div>
                    <span class="wh-status {{ $withdrawalStatus[1] }}">{{ $withdrawalStatus[0] }}</span>
                </div>
            </article>
        @empty
            <div class="wh-empty"><span class="wh-empty-icon"><i class="bi bi-send"></i></span><h3>No crypto withdrawals yet</h3><p>Withdrawal requests and their payout progress will be listed here.</p><a href="{{ route('wallet.withdraw') }}"><i class="bi bi-arrow-up-right me-1"></i>Make a withdrawal</a></div>
        @endforelse
        @if($withdrawals->hasPages())<div class="wh-pagination">{{ $withdrawals->withQueryString()->links() }}</div>@endif
    </section>
    <section class="wh-list mt-4" aria-labelledby="ledger-title">
        <header class="wh-list-head"><div><h2 id="ledger-title" class="wh-list-title">Wallet balance activity</h2><p class="wh-list-subtitle">Credits and debits recorded for each currency, including the opening balance at migration time.</p></div><a href="{{ route('support.cases.index') }}" class="wh-track">Need help? Open a case</a></header>
        @unless($ledgerReady)<div class="alert alert-warning m-3 mb-0">Detailed wallet balance activity is temporarily unavailable. Your deposit and withdrawal history remains available above.</div>@endunless
        @forelse($ledgerEntries as $entry)
            <article class="wh-item"><div class="wh-item-main"><span class="wh-coin-icon {{ $entry->entry_type === 'credit' ? 'deposit' : 'withdraw' }}"><i class="bi {{ $entry->entry_type === 'credit' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i></span><div class="wh-item-copy"><h3 class="wh-item-title">{{ Str::headline(str_replace('.', ' ', $entry->source)) }} <span class="wh-network">{{ $entry->currency }}</span></h3><div class="wh-date">{{ $entry->created_at?->format('M d, Y · H:i') }}</div>@if($entry->reference)<div class="wh-meta"><span class="wh-mono">Reference {{ $entry->reference }}</span></div>@endif</div></div><div class="wh-item-side"><div class="wh-amount {{ $entry->entry_type === 'credit' ? 'in' : 'out' }}">{{ $entry->entry_type === 'credit' ? '+' : '−' }}{{ number_format((float)$entry->amount, 8) }} {{ $entry->currency }}</div><div class="wh-subamount">Balance {{ number_format((float)$entry->balance_after, 8) }} {{ $entry->currency }}</div></div></article>
        @empty<div class="wh-empty"><span class="wh-empty-icon"><i class="bi bi-journal-text"></i></span><h3>No wallet ledger entries</h3><p>Balance changes will be itemized here.</p></div>@endforelse
        @if($ledgerEntries->hasPages())<div class="wh-pagination">{{ $ledgerEntries->withQueryString()->links() }}</div>@endif
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let activeTabId = 'deposits-tab';
    let syncing = false;
    function getTabs() { return [...document.querySelectorAll('.wh-tab')]; }
    function getPanels() { return [...document.querySelectorAll('.wh-list[role="tabpanel"]')]; }
    function activate(tab) {
        if (!tab) return;
        activeTabId = tab.id;
        getTabs().forEach(button => button.setAttribute('aria-selected', button === tab ? 'true' : 'false'));
        getPanels().forEach(panel => { panel.hidden = panel.id !== tab.getAttribute('aria-controls'); });
    }
    function wireTabs() { getTabs().forEach(tab => tab.addEventListener('click', () => activate(tab))); }
    wireTabs();
    activate(document.getElementById(activeTabId));

    async function syncActivity() {
        if (syncing || document.visibilityState !== 'visible') return;
        syncing = true;
        try {
            const response = await fetch(window.location.href, {
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'},
                credentials: 'same-origin', cache: 'no-store'
            });
            if (!response.ok) return;
            const html = await response.text();
            const nextRoot = new DOMParser().parseFromString(html, 'text/html').querySelector('.wh-page');
            const currentRoot = document.querySelector('.wh-page');
            if (!nextRoot || !currentRoot) return;

            const oldScrollY = window.scrollY;
            currentRoot.innerHTML = nextRoot.innerHTML;
            wireTabs();
            activate(document.getElementById(activeTabId));
            const syncLabel = document.getElementById('wh-sync-label');
            if (syncLabel) syncLabel.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Auto-sync is on · Updated just now';
            window.scrollTo({top: oldScrollY, behavior: 'auto'});
        } catch (_) {
            const syncLabel = document.getElementById('wh-sync-label');
            if (syncLabel) syncLabel.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Sync paused · retrying automatically';
        } finally { syncing = false; }
    }

    window.setInterval(syncActivity, 15000);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') syncActivity(); });
    window.addEventListener('focus', syncActivity);
});
</script>
@endsection
