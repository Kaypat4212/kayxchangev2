@extends('adminnavlayout')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h3 class="mb-1">Crypto withdrawals</h3><p class="text-muted mb-0">Review requests, authorize NOWPayments payouts with provider 2FA, and reconcile before wallet settlement.</p></div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="alert {{ $nowPaymentsPayoutsEnabled ? 'alert-success' : 'alert-secondary' }} d-flex align-items-start gap-2"><i class="bi bi-shield-lock-fill"></i><div><strong>NOWPayments crypto withdrawals are {{ $nowPaymentsPayoutsEnabled ? 'enabled' : 'disabled' }}.</strong> The switch is managed in Admin Settings → Payment. Each payout requires an administrator to create it, enter the NOWPayments one-time verification code, and confirm the final provider status. Customer crypto is debited only when the request is completed.</div></div>
    <div class="table-responsive"><table class="table table-dark table-striped align-middle">
        <thead><tr><th>User</th><th>Asset / network</th><th>Amount + fee</th><th>Destination</th><th>Status</th><th>Settlement</th></tr></thead>
        <tbody>
        @forelse($withdrawals as $withdrawal)
            <tr>
                <td>{{ $withdrawal->user->name ?? 'User #'.$withdrawal->user_id }}<br><small>{{ $withdrawal->user->email ?? '' }}</small></td>
                <td>{{ $withdrawal->wallet->currency ?? 'Crypto' }} / {{ $withdrawal->network }}</td>
                <td>{{ number_format((float)$withdrawal->amount, 8) }} + {{ number_format((float)$withdrawal->fee, 8) }}<br><small>≈ ${{ number_format((float)$withdrawal->usd_value_at_request, 2) }} USD</small></td>
                <td style="max-width:220px;word-break:break-all">{{ $withdrawal->address }}</td>
                <td>{{ ucfirst($withdrawal->status) }} @if($withdrawal->risk_flags)<br><span class="badge text-bg-warning">Elevated review</span><small class="d-block">{{ implode(', ', $withdrawal->risk_flags) }}</small>@endif @if($withdrawal->transaction_hash)<br><small>{{ $withdrawal->transaction_hash }}</small>@endif
                    @if($withdrawal->provider_payout_status)<br><span class="badge text-bg-info mt-1">NOWPayments · {{ $withdrawal->provider_payout_status }}</span>@endif
                    @if($withdrawal->provider_payout_id)<small class="d-block text-break">Batch: {{ $withdrawal->provider_payout_id }}</small>@endif
                </td>
                <td>
                    @if(in_array($withdrawal->status, ['pending','processing']))
                    @if($nowPaymentsPayoutsEnabled)
                        @if($withdrawal->status === 'processing' && !$withdrawal->provider_payout_id && !$withdrawal->provider_payout_started_at)
                        <form method="POST" action="{{ route('admin.crypto-withdrawals.nowpayments.create', $withdrawal) }}" onsubmit="return confirm('Create a NOWPayments payout to this exact address? The payout cannot be recalled after verification.');">@csrf<button class="btn btn-sm btn-outline-info">Create NOWPayments payout</button></form>
                        @elseif($withdrawal->provider_payout_id && !$withdrawal->provider_payout_verified_at)
                        <form method="POST" action="{{ route('admin.crypto-withdrawals.nowpayments.verify', $withdrawal) }}" class="d-grid gap-2">@csrf<label class="small text-secondary">Provider one-time code</label><input class="form-control form-control-sm" name="verification_code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="6 digit code" required><button class="btn btn-sm btn-warning">Verify payout</button></form>
                        @endif
                        @if($withdrawal->provider_payout_id)<form method="POST" action="{{ route('admin.crypto-withdrawals.nowpayments.refresh', $withdrawal) }}">@csrf<button class="btn btn-sm btn-outline-light w-100">Refresh provider status</button></form>@endif
                    @endif
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
