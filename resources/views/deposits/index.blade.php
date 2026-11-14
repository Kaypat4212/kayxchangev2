@extends('layout')

@section('title', 'Deposit Center')

@section('content')
<style>
.dc-page{max-width:1240px;margin:auto;padding:28px 16px 72px;color:#eaf1f7}.dc-hero,.dc-card,.dc-stat,.dc-action{border:1px solid rgba(255,255,255,.085);background:#121a24;border-radius:20px}.dc-hero{padding:27px;background:radial-gradient(circle at 90% 0,rgba(0,204,0,.17),transparent 34%),linear-gradient(130deg,#101a27,#121d19);margin-bottom:18px}.dc-eyebrow{font-size:.73rem;letter-spacing:.12em;text-transform:uppercase;color:#4ade80;font-weight:800}.dc-hero h1{color:#fff;font-weight:850;letter-spacing:-.03em}.dc-muted{color:#9aa9b8}.dc-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:16px 0}.dc-action{padding:17px;display:flex;align-items:center;gap:13px;text-decoration:none;color:#eff6fb;transition:transform .18s,border-color .18s}.dc-action:hover{transform:translateY(-2px);border-color:rgba(74,222,128,.4);color:#fff}.dc-action-icon{height:42px;width:42px;display:grid;place-items:center;border-radius:13px;background:rgba(34,197,94,.12);color:#4ade80;font-size:1.1rem}.dc-action.crypto .dc-action-icon{background:rgba(56,189,248,.12);color:#7dd3fc}.dc-action strong{display:block}.dc-action small{display:block;color:#9aa9b8;margin-top:2px}.dc-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}.dc-stat{padding:15px 17px}.dc-stat-label{font-size:.73rem;color:#9aa9b8}.dc-stat-value{font-size:1.25rem;font-weight:850;color:#fff;margin-top:4px;font-variant-numeric:tabular-nums}.dc-card{padding:19px;margin-bottom:17px}.dc-section-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:14px}.dc-section-head h2{font-size:1.02rem;color:#fff;font-weight:800;margin:0}.dc-pill{display:inline-flex;align-items:center;border-radius:999px;padding:5px 9px;font-size:.68rem;text-transform:uppercase;font-weight:800;letter-spacing:.04em;background:rgba(255,255,255,.07);color:#cbd5df}.dc-pill.pending,.dc-pill.processing,.dc-pill.awaiting_admin_review{background:rgba(245,158,11,.12);color:#fbbf24}.dc-pill.approved,.dc-pill.completed,.dc-pill.successful{background:rgba(34,197,94,.12);color:#4ade80}.dc-pill.failed,.dc-pill.rejected{background:rgba(239,68,68,.12);color:#fca5a5}.dc-filter{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:0 0 13px}.dc-control{background:#0a1119;border:1px solid #344252;border-radius:10px;padding:9px 11px;color:#eef4f8;min-height:40px}.dc-control:focus{outline:2px solid rgba(34,197,94,.25);border-color:#22c55e}.dc-table-wrap{overflow-x:auto}.dc-table{width:100%;border-collapse:collapse;min-width:720px}.dc-table th{font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#91a0ae;text-align:left;padding:10px;border-bottom:1px solid rgba(255,255,255,.09)}.dc-table td{padding:13px 10px;border-bottom:1px solid rgba(255,255,255,.055);font-size:.82rem;color:#e3ebf1;vertical-align:middle}.dc-table tr:last-child td{border-bottom:0}.dc-ref{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.75rem;color:#cbd5df;overflow-wrap:anywhere}.dc-amount{font-weight:800;font-variant-numeric:tabular-nums}.dc-sub{font-size:.7rem;color:#92a1af;margin-top:3px}.dc-view{color:#4ade80;text-decoration:none;font-weight:750;white-space:nowrap}.dc-view:hover{color:#86efac}.dc-empty{text-align:center;padding:30px 14px;color:#a6b3c0}.dc-empty i{display:block;font-size:1.7rem;color:#4ade80;margin-bottom:8px}.dc-pagination{padding-top:12px}.dc-alert{border-radius:12px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.23);color:#fecaca;padding:12px 14px;margin-bottom:14px}.dc-alert-success{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.2);color:#bbf7d0}
@media(max-width:760px){.dc-page{padding:18px 12px 48px}.dc-hero{padding:20px}.dc-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.dc-stat{padding:13px}.dc-stat-value{font-size:1.08rem}.dc-actions{gap:9px}.dc-action{padding:13px;align-items:flex-start}.dc-action-icon{width:36px;height:36px;flex:0 0 36px}.dc-section-head{align-items:center}}
@media(max-width:420px){.dc-actions{grid-template-columns:1fr}.dc-control.search{width:100%}}
</style>

<main class="dc-page">
    <header class="dc-hero">
        <div class="dc-eyebrow">Your money, clearly tracked</div>
        <h1 class="h2 mt-2 mb-2">Deposit center</h1>
        <p class="dc-muted mb-0">Add naira through a payment method or create a crypto invoice. Follow every deposit here from request to confirmation.</p>
    </header>

    @if(session('success'))<div class="dc-alert dc-alert-success"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="dc-alert"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>@endif
    @if($errors->any())<div class="dc-alert"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}</div>@endif

    <div class="dc-actions">
        <a class="dc-action" href="{{ route('deposits.create') }}"><span class="dc-action-icon"><i class="bi bi-bank"></i></span><span><strong>Add naira</strong><small>Bank transfer or available payment gateway</small></span><i class="bi bi-arrow-up-right ms-auto"></i></a>
        <a class="dc-action crypto" href="{{ route('wallet.deposit') }}"><span class="dc-action-icon"><i class="bi bi-currency-bitcoin"></i></span><span><strong>Deposit to crypto wallet</strong><small>Choose a coin and network, then follow your deposit status</small></span><i class="bi bi-arrow-up-right ms-auto"></i></a>
    </div>

    <section class="dc-stats" aria-label="Deposit summary">
        <div class="dc-stat"><div class="dc-stat-label">All deposit records</div><div class="dc-stat-value">{{ number_format($stats['all_count']) }}</div></div>
        <div class="dc-stat"><div class="dc-stat-label">Naira credited</div><div class="dc-stat-value">₦{{ number_format((float)$stats['fiat_total'], 2) }}</div></div>
        <div class="dc-stat"><div class="dc-stat-label">Crypto credited (NGN value)</div><div class="dc-stat-value">₦{{ number_format((float)$stats['crypto_total_ngn'], 2) }}</div></div>
        <div class="dc-stat"><div class="dc-stat-label">Awaiting update</div><div class="dc-stat-value">{{ number_format($stats['fiat_pending'] + $stats['crypto_pending']) }}</div></div>
    </section>

    <form class="dc-filter" method="GET" action="{{ route('deposits.index') }}">
        <input class="dc-control search" type="search" name="search" value="{{ $search }}" placeholder="Search reference, asset or transaction hash" aria-label="Search deposits">
        <select class="dc-control" name="status" aria-label="Filter by status"><option value="">All statuses</option>@foreach(['pending','processing','awaiting_admin_review','approved','completed','rejected','failed'] as $filterStatus)<option value="{{ $filterStatus }}" @selected($status===$filterStatus)>{{ ucwords(str_replace('_',' ',$filterStatus)) }}</option>@endforeach</select>
        <select class="dc-control" name="asset" aria-label="Filter crypto asset"><option value="">All crypto assets</option>@foreach(['BTC','ETH','USDT','USDC','BNB','SOL'] as $cryptoAsset)<option value="{{ $cryptoAsset }}" @selected($asset===$cryptoAsset)>{{ $cryptoAsset }}</option>@endforeach</select>
        <button class="btn btn-success" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if($search || $status || $asset)<a class="btn btn-outline-light" href="{{ route('deposits.index') }}">Clear</a>@endif
    </form>

    <section class="dc-card" id="naira-deposits">
        <div class="dc-section-head"><div><h2><i class="bi bi-bank me-2 text-success"></i>Naira deposits</h2><div class="dc-sub">Payment gateway and bank transfer history</div></div><span class="dc-pill">{{ $deposits->total() }} records</span></div>
        @if($deposits->isEmpty())<div class="dc-empty"><i class="bi bi-inbox"></i><strong>No naira deposits match this view</strong><div class="dc-sub">Make a naira deposit or adjust the filters above.</div></div>
        @else
        <div class="dc-table-wrap"><table class="dc-table"><thead><tr><th>Amount</th><th>Method</th><th>Status</th><th>Reference</th><th>Date</th><th></th></tr></thead><tbody>
        @foreach($deposits as $deposit)<tr>
            <td><span class="dc-amount">₦{{ number_format((float)$deposit->amount,2) }}</span>@if((float)$deposit->fee_amount>0)<div class="dc-sub">Fee ₦{{ number_format((float)$deposit->fee_amount,2) }}</div>@endif</td>
            <td>{{ ucfirst(str_replace('_',' ',$deposit->payment_method ?? 'Bank transfer')) }}</td>
            <td><span class="dc-pill {{ strtolower($deposit->status) }}">{{ ucwords(str_replace('_',' ',$deposit->status)) }}</span></td>
            <td class="dc-ref">{{ $deposit->transaction_ref ?: $deposit->gateway_reference ?: '—' }}@if($deposit->admin_note)<div class="dc-sub">{{ $deposit->admin_note }}</div>@endif</td>
            <td>{{ $deposit->created_at?->format('M d, Y') }}<div class="dc-sub">{{ $deposit->created_at?->format('H:i') }}</div></td>
            <td>@if($deposit->status==='pending')<form method="POST" action="{{ route('deposit.user.cancel',$deposit->id) }}" onsubmit="return confirm('Cancel this pending deposit?')">@csrf<button class="btn btn-sm btn-outline-danger" type="submit">Cancel</button></form>@else<span class="dc-sub">—</span>@endif</td>
        </tr>@endforeach
        </tbody></table></div><div class="dc-pagination">{{ $deposits->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>

    <section class="dc-card" id="crypto-deposits">
        <div class="dc-section-head"><div><h2><i class="bi bi-currency-bitcoin me-2 text-info"></i>Crypto deposits</h2><div class="dc-sub">Live provider invoices and manually reviewed transfers</div></div><a class="dc-view" href="{{ route('wallet.deposit') }}">New crypto deposit <i class="bi bi-arrow-up-right"></i></a></div>
        @if($cryptoDeposits->isEmpty())<div class="dc-empty"><i class="bi bi-wallet2"></i><strong>No crypto deposits match this view</strong><div class="dc-sub">Create an invoice to track pending confirmations and wallet credits here.</div></div>
        @else
        <div class="dc-table-wrap"><table class="dc-table"><thead><tr><th>Asset / Network</th><th>Invoice value</th><th>Received</th><th>Status</th><th>Reference / TxID</th><th>Created</th><th></th></tr></thead><tbody>
        @foreach($cryptoDeposits as $deposit)@php($network=data_get($deposit->payment_data,'deposit_input.network') ?: $deposit->wallet?->network ?: '—')@php($provider=data_get($deposit->payment_data,'provider') ?: data_get($deposit->payment_data,'_provider'))<tr>
            <td><span class="dc-amount">{{ strtoupper($deposit->currency) }}</span><div class="dc-sub">{{ $network }}{{ $provider?' · '.strtoupper($provider):'' }}</div></td>
            <td>${{ number_format((float)$deposit->expected_amount,2) }}<div class="dc-sub">Invoice USD value</div></td>
            <td>{{ $deposit->received_amount ? rtrim(rtrim(number_format((float)$deposit->received_amount,8),'0'),'.').' '.strtoupper($deposit->currency) : '—' }}@if($deposit->ngn_credited_amount)<div class="dc-sub">₦{{ number_format((float)$deposit->ngn_credited_amount,2) }} credited</div>@endif</td>
            <td><span class="dc-pill {{ strtolower($deposit->status) }}">{{ ucwords(str_replace('_',' ',$deposit->status)) }}</span></td>
            <td class="dc-ref">{{ data_get($deposit->payment_data,'provider_payment_id') ? 'Provider '.data_get($deposit->payment_data,'provider_payment_id') : 'DEP-'.$deposit->id }}@if($deposit->transaction_hash)<div class="dc-sub">Tx {{ \Illuminate\Support\Str::limit($deposit->transaction_hash,22) }}</div>@endif</td>
            <td>{{ $deposit->created_at?->format('M d, Y') }}<div class="dc-sub">{{ $deposit->created_at?->format('H:i') }}</div></td>
            <td><a class="dc-view" href="{{ route('wallet.deposit.success',$deposit->id) }}">Details <i class="bi bi-arrow-up-right"></i></a></td>
        </tr>@endforeach
        </tbody></table></div><div class="dc-pagination">{{ $cryptoDeposits->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
</main>
@endsection
