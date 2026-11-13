@extends('adminnavlayout')
@section('content')
<style>
.tx-wrap{max-width:1500px;margin:auto;color:#e4e8f0}.tx-head,.tx-panel{background:#161b27;border:1px solid rgba(255,255,255,.08);border-radius:14px}.tx-head{padding:1.2rem 1.4rem;margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}.tx-head h1{font-size:1.2rem;color:#fff;margin:0}.tx-filters{display:flex;gap:.6rem;flex-wrap:wrap}.tx-filters input,.tx-filters select{background:#0d1117;border:1px solid #30394a;color:#e4e8f0;border-radius:8px;padding:.55rem .75rem}.tx-filters button{background:#00cc00;color:#071007;border:0;border-radius:8px;padding:.55rem .9rem;font-weight:700}.tx-panel{overflow:hidden}.tx-scroll{overflow:auto}table{width:100%;border-collapse:collapse;min-width:900px}th,td{padding:.8rem 1rem;border-bottom:1px solid rgba(255,255,255,.06);text-align:left;font-size:.82rem}th{color:#8994a8;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em}td{color:#e4e8f0}.muted{color:#8994a8}.pill{display:inline-block;border-radius:20px;padding:.2rem .6rem;background:#283044;font-size:.72rem}.pill.pending,.pill.validated,.pill.processing{color:#fbbf24}.pill.completed,.pill.approved{color:#00d26a}.pill.failed,.pill.rejected,.pill.expired,.pill.reversed{color:#ff7373}.tx-pagination{padding:1rem}
</style>
<div class="tx-wrap">
 <div class="tx-head"><div><h1><i class="bi bi-clock-history me-2" style="color:#00cc00"></i>All Transactions</h1><div class="muted small mt-1">Unified activity across customer deposits, trades, withdrawals, bills and wallet transfers.</div></div>
 <form class="tx-filters" method="GET" action="{{ route('admin.transactions.index') }}">
  <input name="search" value="{{ request('search') }}" placeholder="Customer, reference…">
  <select name="type"><option value="">All types</option>@foreach(['buy'=>'Buy','sell'=>'Sell','deposit'=>'Deposit','withdrawal'=>'NGN withdrawal','crypto_deposit'=>'Crypto deposit','crypto_withdrawal'=>'Crypto withdrawal','bill_payment'=>'Bill payment','transfer'=>'Wallet transfer'] as $key=>$label)<option value="{{ $key }}" @selected(request('type')===$key)>{{ $label }}</option>@endforeach</select>
  <select name="status"><option value="">All statuses</option>@foreach(['pending','validated','processing','completed','approved','failed','rejected','cancelled','expired','reversed'] as $status)<option @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select>
  <input type="date" name="date" value="{{ request('date') }}"><button>Filter</button>
 </form></div>
 <div class="tx-panel"><div class="tx-scroll"><table><thead><tr><th>Customer</th><th>Type</th><th>Asset / service</th><th>Amount</th><th>Status</th><th>Reference</th><th>Date</th></tr></thead><tbody>
 @forelse($transactions as $tx)<tr><td><strong>{{ $tx->user?->name ?? 'Deleted user' }}</strong><div class="muted">{{ $tx->user?->email ?? '—' }}</div></td><td>{{ ucwords(str_replace('_',' ',$tx->type)) }}</td><td>{{ strtoupper($tx->currency ?? '—') }}</td><td>{{ in_array($tx->type,['crypto_deposit','crypto_withdrawal']) ? number_format((float)$tx->amount,8).' '.strtoupper($tx->currency ?? '') : '₦'.number_format((float)$tx->amount,2) }}</td><td><span class="pill {{ strtolower($tx->status) }}">{{ ucfirst($tx->status) }}</span></td><td class="muted">{{ $tx->reference }}</td><td>{{ $tx->created_at?->format('d M Y, H:i') }}</td></tr>
 @empty<tr><td colspan="7" class="text-center muted py-5">No transactions match these filters.</td></tr>@endforelse
 </tbody></table></div><div class="tx-pagination">{{ $transactions->links() }}</div></div>
</div>
@endsection
