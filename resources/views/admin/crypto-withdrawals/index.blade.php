@extends('adminnavlayout')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h3 class="mb-1">Crypto withdrawals</h3><p class="text-muted mb-0">Review requests and record the on-chain transaction hash when paid.</p></div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="table-responsive"><table class="table table-dark table-striped align-middle">
        <thead><tr><th>User</th><th>Asset / network</th><th>Amount + fee</th><th>Destination</th><th>Status</th><th>Settlement</th></tr></thead>
        <tbody>
        @forelse($withdrawals as $withdrawal)
            <tr>
                <td>{{ $withdrawal->user->name ?? 'User #'.$withdrawal->user_id }}<br><small>{{ $withdrawal->user->email ?? '' }}</small></td>
                <td>{{ $withdrawal->wallet->currency ?? 'Crypto' }} / {{ $withdrawal->network }}</td>
                <td>{{ number_format((float)$withdrawal->amount, 8) }} + {{ number_format((float)$withdrawal->fee, 8) }}<br><small>≈ ${{ number_format((float)$withdrawal->usd_value_at_request, 2) }} USD</small></td>
                <td style="max-width:220px;word-break:break-all">{{ $withdrawal->address }}</td>
                <td>{{ ucfirst($withdrawal->status) }} @if($withdrawal->risk_flags)<br><span class="badge text-bg-warning">Elevated review</span><small class="d-block">{{ implode(', ', $withdrawal->risk_flags) }}</small>@endif @if($withdrawal->transaction_hash)<br><small>{{ $withdrawal->transaction_hash }}</small>@endif</td>
                <td>
                    @if(in_array($withdrawal->status, ['pending','processing']))
                    <form method="POST" action="{{ route('admin.crypto-withdrawals.update', $withdrawal) }}" class="d-grid gap-2">
                        @csrf @method('PATCH')
                        <select class="form-select form-select-sm" name="status" required>
                            <option value="processing" @selected($withdrawal->status === 'processing')>Approve for payout / processing</option>
                            <option value="completed">Completed (paid)</option>
                            <option value="rejected">Reject</option>
                        </select>
                        <input class="form-control form-control-sm" name="transaction_hash" placeholder="Transaction hash (required to complete)">
                        <input class="form-control form-control-sm" name="rejected_reason" placeholder="Reason (required to reject)">
                        <button class="btn btn-sm btn-primary">Save status</button>
                    </form>
                    @else <span class="text-muted">Finalized</span> @endif
                </td>
            </tr>
        @empty <tr><td colspan="6" class="text-center text-muted py-4">No crypto withdrawal requests yet.</td></tr> @endforelse
        </tbody>
    </table></div>
    {{ $withdrawals->links() }}
</div>
@endsection
