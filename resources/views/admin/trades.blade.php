@extends('adminnavlayout')

@push('styles')
<link href="{{ asset('css/admin-trades.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Transaction Management</h1>
                    <p class="text-muted mb-0">Monitor and manage all platform transactions</p>
                </div>
                <div class="d-flex gap-2">
                    <button id="exportBtn" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-1"></i>Export CSV
                    </button>
                    <button id="refreshBtn" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Transactions</h6>
                            <h4 class="mb-0">{{ number_format($statistics['total_transactions']) }}</h4>
                        </div>
                        <i class="bi bi-graph-up fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pending</h6>
                            <h4 class="mb-0">{{ number_format($statistics['pending_transactions']) }}</h4>
                        </div>
                        <i class="bi bi-clock-history fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Completed</h6>
                            <h4 class="mb-0">{{ number_format($statistics['completed_transactions']) }}</h4>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Volume</h6>
                            <h4 class="mb-0">₦{{ number_format($statistics['total_volume'], 2) }}</h4>
                        </div>
                        <i class="bi bi-currency-exchange fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search transactions...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="successful">Successful</option>
                        <option value="completed">Completed</option>
                        <option value="approved">Approved</option>
                        <option value="canceled">Canceled</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select id="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="deposit">Deposits</option>
                        <option value="withdrawal">Withdrawals</option>
                        <option value="buy">Buy Trades</option>
                        <option value="sell">Sell Trades</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" id="dateFromFilter" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" id="dateToFilter" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button id="clearFilters" class="btn btn-outline-secondary w-100">Clear</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="transactionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="deposits-tab" data-bs-toggle="tab" data-bs-target="#deposits" type="button" role="tab">
                        <i class="bi bi-arrow-down-circle me-1"></i>Deposits ({{ $deposits->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="withdrawals-tab" data-bs-toggle="tab" data-bs-target="#withdrawals" type="button" role="tab">
                        <i class="bi bi-arrow-up-circle me-1"></i>Withdrawals ({{ $withdrawals->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="buy-trades-tab" data-bs-toggle="tab" data-bs-target="#buy-trades" type="button" role="tab">
                        <i class="bi bi-cart-plus me-1"></i>Buy Trades ({{ $buyTrades->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sell-trades-tab" data-bs-toggle="tab" data-bs-target="#sell-trades" type="button" role="tab">
                        <i class="bi bi-cart-dash me-1"></i>Sell Trades ({{ $sellTrades->count() }})
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="transactionTabContent">
                
                <!-- Deposits Tab -->
                <div class="tab-pane fade show active" id="deposits" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="depositsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Proof</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                <tr data-transaction-type="deposit" data-status="{{ $deposit->status }}" data-date="{{ $deposit->created_at->format('Y-m-d') }}">
                                    <td>
                                        <span class="badge bg-light text-dark">#{{ $deposit->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $deposit->user->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $deposit->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">₦{{ number_format($deposit->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($deposit->payment_method ?? 'Bank Transfer') }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('deposits.updateStatus', $deposit->id) }}" class="status-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()">
                                                <option value="pending" {{ $deposit->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="approved" {{ $deposit->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="rejected" {{ $deposit->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        @if($deposit->proof_of_payment)
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#proofModal" data-proof="{{ asset('storage/' . $deposit->proof_of_payment) }}">
                                                <i class="bi bi-image me-1"></i>View
                                            </button>
                                        @else
                                            <span class="text-muted">No proof</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $deposit->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal" data-transaction="{{ json_encode($deposit) }}" data-type="deposit">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if($deposit->user)
                                            <a href="/admin/users/{{ $deposit->user->id }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-person"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No deposits found
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Withdrawals Tab -->
                <div class="tab-pane fade" id="withdrawals" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="withdrawalsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Bank Details</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $withdrawal)
                                <tr data-transaction-type="withdrawal" data-status="{{ $withdrawal->status }}" data-date="{{ $withdrawal->created_at->format('Y-m-d') }}">
                                    <td>
                                        <span class="badge bg-light text-dark">#{{ $withdrawal->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-danger rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $withdrawal->user->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $withdrawal->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-danger">₦{{ number_format($withdrawal->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">{{ $withdrawal->bank_name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $withdrawal->account_number ?? 'N/A' }}</small>
                                            <small class="text-muted d-block">{{ $withdrawal->account_name ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('withdrawals.updateStatus', $withdrawal->id) }}" class="status-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()">
                                                <option value="pending" {{ $withdrawal->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="successful" {{ $withdrawal->status == 'successful' ? 'selected' : '' }}>Successful</option>
                                                <option value="canceled" {{ $withdrawal->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <small>{{ $withdrawal->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal" data-transaction="{{ json_encode($withdrawal) }}" data-type="withdrawal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if($withdrawal->user)
                                            <a href="/admin/users/{{ $withdrawal->user->id }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-person"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No withdrawals found
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Buy Trades Tab -->
                <div class="tab-pane fade" id="buy-trades" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="buyTradesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Coin</th>
                                    <th>USD Amount</th>
                                    <th>Naira Amount</th>
                                    <th>Wallet</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($buyTrades as $trade)
                                <tr data-transaction-type="buy" data-status="{{ $trade->status }}" data-date="{{ $trade->created_at->format('Y-m-d') }}">
                                    <td>
                                        <span class="badge bg-light text-dark">#{{ $trade->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $trade->user->name ?? $trade->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $trade->user->email ?? $trade->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning me-2">{{ strtoupper($trade->coin ?? 'N/A') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">${{ number_format($trade->usd_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">₦{{ number_format($trade->naira_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary copy-btn" data-copy="{{ $trade->wallet_address ?? '' }}" title="{{ $trade->wallet_address ?? 'N/A' }}">
                                            <i class="bi bi-wallet2 me-1"></i>{{ \Illuminate\Support\Str::limit($trade->wallet_address ?? 'N/A', 12, '...') }}
                                        </button>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('buy.updateStatus', $trade->id) }}" class="status-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()">
                                                <option value="pending" {{ $trade->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="successful" {{ $trade->status == 'successful' ? 'selected' : '' }}>Successful</option>
                                                <option value="canceled" {{ $trade->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <small>{{ $trade->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal" data-transaction="{{ json_encode($trade) }}" data-type="buy">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if($trade->user)
                                            <a href="/admin/users/{{ $trade->user->id }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-person"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No buy trades found
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sell Trades Tab -->
                <div class="tab-pane fade" id="sell-trades" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="sellTradesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Coin</th>
                                    <th>USD Amount</th>
                                    <th>Naira Amount</th>
                                    <th>Bank Details</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sellTrades as $trade)
                                <tr data-transaction-type="sell" data-status="{{ $trade->status }}" data-date="{{ $trade->created_at->format('Y-m-d') }}">
                                    <td>
                                        <span class="badge bg-light text-dark">#{{ $trade->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $trade->user->name ?? $trade->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $trade->user->email ?? $trade->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-info me-2">{{ strtoupper($trade->coin ?? 'N/A') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">${{ number_format($trade->usd_amount ?? 0, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-warning">₦{{ number_format($trade->naira_amount ?? 0, 2) }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium">{{ $trade->bank_name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $trade->account_number ?? 'N/A' }}</small>
                                            <small class="text-muted d-block">{{ $trade->account_name ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.sells.updateStatus', $trade->id) }}" class="status-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()">
                                                <option value="pending" {{ $trade->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="successful" {{ $trade->status == 'successful' ? 'selected' : '' }}>Successful</option>
                                                <option value="canceled" {{ $trade->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <small>{{ $trade->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal" data-transaction="{{ json_encode($trade) }}" data-type="sell">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if($trade->user)
                                            <a href="/admin/users/{{ $trade->user->id }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-person"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No sell trades found
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Proof Modal -->
    <div class="modal fade" id="proofModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proof of Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="proofImage" src="" alt="Proof of Payment" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="transactionDetails">
                    <!-- Transaction details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.copy-btn {
    border: none;
    background: none;
    color: inherit;
    text-decoration: none;
    cursor: pointer;
}

.copy-btn:hover {
    color: var(--bs-primary);
}

.status-select {
    min-width: 120px;
}

.table-responsive {
    border-radius: 0.375rem;
}

@media (max-width: 768px) {
    .btn-group-sm {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy functionality
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            if (text && text !== '' && text !== 'N/A') {
                navigator.clipboard.writeText(text).then(() => {
                    // Show success feedback
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-check text-success"></i>';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 1000);
                });
            }
        });
    });

    // Proof modal
    document.getElementById('proofModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const proofUrl = button.getAttribute('data-proof');
        document.getElementById('proofImage').src = proofUrl;
    });

    // Detail modal
    document.getElementById('detailModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const transaction = JSON.parse(button.getAttribute('data-transaction'));
        const type = button.getAttribute('data-type');
        
        let detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Transaction Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>ID:</strong></td><td>#${transaction.id}</td></tr>
                        <tr><td><strong>Type:</strong></td><td>${type.charAt(0).toUpperCase() + type.slice(1)}</td></tr>
                        <tr><td><strong>Status:</strong></td><td><span class="badge bg-${getStatusColor(transaction.status)}">${transaction.status}</span></td></tr>
                        <tr><td><strong>Created:</strong></td><td>${new Date(transaction.created_at).toLocaleString()}</td></tr>
                        <tr><td><strong>Updated:</strong></td><td>${new Date(transaction.updated_at).toLocaleString()}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>User Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Name:</strong></td><td>${transaction.user?.name || transaction.name || 'N/A'}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>${transaction.user?.email || transaction.email || 'N/A'}</td></tr>
        `;
        
        if (type === 'deposit') {
            detailsHtml += `
                        <tr><td><strong>Amount:</strong></td><td>₦${parseFloat(transaction.amount).toLocaleString()}</td></tr>
                        <tr><td><strong>Method:</strong></td><td>${transaction.payment_method || 'Bank Transfer'}</td></tr>
            `;
        } else if (type === 'withdrawal') {
            detailsHtml += `
                        <tr><td><strong>Amount:</strong></td><td>₦${parseFloat(transaction.amount).toLocaleString()}</td></tr>
                        <tr><td><strong>Bank:</strong></td><td>${transaction.bank_name || 'N/A'}</td></tr>
                        <tr><td><strong>Account:</strong></td><td>${transaction.account_number || 'N/A'}</td></tr>
                        <tr><td><strong>Account Name:</strong></td><td>${transaction.account_name || 'N/A'}</td></tr>
            `;
        } else if (type === 'buy' || type === 'sell') {
            detailsHtml += `
                        <tr><td><strong>Coin:</strong></td><td>${transaction.coin || 'N/A'}</td></tr>
                        <tr><td><strong>USD Amount:</strong></td><td>$${parseFloat(transaction.usd_amount || 0).toLocaleString()}</td></tr>
                        <tr><td><strong>Naira Amount:</strong></td><td>₦${parseFloat(transaction.naira_amount || 0).toLocaleString()}</td></tr>
            `;
            if (type === 'buy' && transaction.wallet_address) {
                detailsHtml += `<tr><td><strong>Wallet:</strong></td><td>${transaction.wallet_address}</td></tr>`;
            }
        }
        
        detailsHtml += `
                    </table>
                </div>
            </div>
        `;
        
        document.getElementById('transactionDetails').innerHTML = detailsHtml;
    });

    function getStatusColor(status) {
        switch(status) {
            case 'pending': return 'warning';
            case 'successful':
            case 'completed':
            case 'approved': return 'success';
            case 'canceled':
            case 'cancelled':
            case 'rejected': return 'danger';
            default: return 'secondary';
        }
    }

    // Search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const dateFromFilter = document.getElementById('dateFromFilter');
    const dateToFilter = document.getElementById('dateToFilter');
    const clearFilters = document.getElementById('clearFilters');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const typeValue = typeFilter.value;
        const dateFrom = dateFromFilter.value;
        const dateTo = dateToFilter.value;

        document.querySelectorAll('tr[data-transaction-type]').forEach(row => {
            const type = row.getAttribute('data-transaction-type');
            const status = row.getAttribute('data-status');
            const date = row.getAttribute('data-date');
            const text = row.textContent.toLowerCase();

            let show = true;

            if (searchTerm && !text.includes(searchTerm)) show = false;
            if (statusValue && status !== statusValue) show = false;
            if (typeValue && type !== typeValue) show = false;
            if (dateFrom && date < dateFrom) show = false;
            if (dateTo && date > dateTo) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);
    dateFromFilter.addEventListener('change', applyFilters);
    dateToFilter.addEventListener('change', applyFilters);

    clearFilters.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        typeFilter.value = '';
        dateFromFilter.value = '';
        dateToFilter.value = '';
        applyFilters();
    });

    // Export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Simple CSV export
        let csv = 'Type,ID,User,Amount,Status,Date\n';
        
        document.querySelectorAll('tr[data-transaction-type]:not([style*="display: none"])').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                const type = row.getAttribute('data-transaction-type');
                const data = Array.from(cells).slice(0, 6).map(cell => 
                    cell.textContent.replace(/,/g, ';').trim()
                );
                csv += `${type},${data.join(',')}\n`;
            }
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `transactions_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    });

    // Refresh functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
        location.reload();
    });
});
</script>

@endsection
                            <div class="card bg-dark bg-opacity-75 border-neon mb-3 trade-card"
                                data-trade-id="{{ $trade->id }}" data-coin="{{ $trade->coin ?? '' }}"
                                data-status="{{ $trade->status }}" data-date="{{ $trade->created_at->format('Y-m-d') }}"
                                data-user-name="{{ $trade->name ?? '' }}"
                                data-transaction-ref="{{ $trade->transaction_ref ?? '' }}" data-aos="fade-up"
                                data-aos-duration="800" data-aos-delay="200">
                                <div class="card-body">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success text-dark">Buy</span>
                                            <span class="text-white fw-medium">{{ $trade->coin ?? 'N/A' }}</span>
                                        </div>
                                        <small class="text-muted">{{ $trade->created_at->format('d M, Y H:i') }}</small>
                                    </div>
                                    <div class="row g-3 text-sm text-white">
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">User</p>
                                            <p class="fw-medium">{{ $trade->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">USD Amount</p>
                                            <p class="fw-medium">${{ number_format($trade->usd_amount, 2) }}</p>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">Naira Amount</p>
                                            <p class="fw-medium">₦{{ number_format($trade->naira_amount, 2) }}</p>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">Status</p>
                                            <form method="POST" action="{{ route('buy.updateStatus', $trade->id) }}"
                                                class="status-form">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status"
                                                    class="form-select form-select-sm bg-dark text-white border-neon status-select"
                                                    data-trade-id="{{ $trade->id }}"
                                                    data-original-status="{{ $trade->status }}"
                                                    aria-label="Update status">
                                                    <option value="pending"
                                                        {{ $trade->status == 'pending' ? 'selected' : '' }}>Pending
                                                    </option>
                                                    <option value="successful"
                                                        {{ $trade->status == 'successful' ? 'selected' : '' }}>Successful
                                                    </option>
                                                    <option value="canceled"
                                                        {{ $trade->status == 'canceled' ? 'selected' : '' }}>Canceled
                                                    </option>
                                                </select>
                                            </form>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-white mb-1">Wallet</p>
                                            <button
                                                class="copy-btn text-muted text-monospace text-sm text-truncate d-block hover-text-neon"
                                                data-copy="{{ $trade->wallet_address ?? '' }}"
                                                title="{{ $trade->wallet_address ?? 'N/A' }}"
                                                aria-label="Copy wallet address">
                                                {{ \Illuminate\Support\Str::limit($trade->wallet_address ?? 'N/A', 16, '...') }}
                                            </button>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">Transaction Ref</p>
                                            <button
                                                class="copy-btn text-muted text-monospace text-sm text-truncate d-block hover-text-neon"
                                                data-copy="{{ $trade->transaction_ref ?? '' }}"
                                                title="{{ $trade->transaction_ref ?? 'N/A' }}"
                                                aria-label="Copy transaction ref">
                                                {{ \Illuminate\Support\Str::limit($trade->transaction_ref ?? 'N/A', 16, '...') }}
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <p class="text-muted mb-1">Payment Proof</p>
                                            @if ($trade->payment_proof)
                                                <a href="{{ asset('storage/' . $trade->payment_proof) }}" target="_blank"
                                                    class="text-neon hover-text-neon">
                                                    <img src="{{ asset('storage/' . $trade->payment_proof) }}"
                                                        alt="Payment Proof" class="img-fluid rounded"
                                                        style="max-width: 100px;">
                                                </a>
                                            @else
                                                <span class="text-muted">No proof uploaded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card bg-dark bg-opacity-75 border-neon text-center" data-aos="fade-up"
                                data-aos-duration="800" data-aos-delay="200">
                                <div class="card-body">
                                    <i class="bi bi-file-earmark-x w-10 h-10 mb-2 text-muted mx-auto d-block"></i>
                                    <p class="text-muted">No buy trades found.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @if ($buyTrades->hasPages())
                        <div class="d-flex justify-content-center mt-4" data-aos="fade-up" data-aos-duration="800"
                            data-aos-delay="300">
                            {{ $buyTrades->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                </div>

                <!-- Sell Trades -->
                <div class="tab-pane fade" id="sell-trades" role="tabpanel" aria-labelledby="sell-trades-tab">
                    <div id="sellTradesList">
                        @forelse($sellTrades as $sell)
                            <div class="card bg-dark bg-opacity-75 border-neon mb-3 trade-card"
                                data-trade-id="{{ $sell->id }}" data-coin="{{ $sell->coin }}"
                                data-status="{{ $sell->status }}" data-date="{{ $sell->created_at->format('Y-m-d') }}"
                                data-user-name="{{ $sell->user->name ?? '' }}"
                                data-transaction-ref="{{ $sell->transaction_ref ?? '' }}" data-aos="fade-up"
                                data-aos-duration="800" data-aos-delay="200">
                                <div class="card-body">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-danger text-white">Sell</span>
                                            <span class="text-white fw-medium">{{ strtoupper($sell->coin) }}</span>
                                        </div>
                                        <small class="text-muted">{{ $sell->created_at->format('d M, Y H:i') }}</small>
                                    </div>
                                    <div class="row g-3 text-sm text-white">
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">User</p>
                                            <p class="fw-medium">{{ $sell->user->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">Amount</p>
                                            <p class="fw-medium">{{ number_format($sell->crypto_amount, 8) }}
                                                {{ strtoupper($sell->coin) }}</p>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">Naira Amount</p>
                                            <p class="fw-medium">₦{{ number_format($sell->naira_amount, 2) }}</p>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">Status</p>
                                            <form method="POST" action="{{ route('sell.updateStatus', $sell->id) }}"
                                                class="status-form">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status"
                                                    class="form-select form-select-sm bg-dark text-white border-neon status-select"
                                                    data-trade-id="{{ $sell->id }}"
                                                    data-original-status="{{ $sell->status }}"
                                                    aria-label="Update status">
                                                    <option value="pending"
                                                        {{ $sell->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="completed"
                                                        {{ $sell->status == 'completed' ? 'selected' : '' }}>Completed
                                                    </option>
                                                    <option value="canceled"
                                                        {{ $sell->status == 'canceled' ? 'selected' : '' }}>Canceled
                                                    </option>
                                                </select>
                                            </form>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-white mb-1">Transaction Ref</p>
                                            <button
                                                class="copy-btn text-muted text-monospace text-sm text-truncate d-block hover-text-neon"
                                                data-copy="{{ $sell->transaction_ref ?? '' }}"
                                                title="{{ $sell->transaction_ref ?? 'N/A' }}"
                                                aria-label="Copy transaction ref">
                                                {{ \Illuminate\Support\Str::limit($sell->transaction_ref ?? 'N/A', 16, '...') }}
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <p class="text-white mb-1">Proof</p>
                                            @if ($sell->proof)
                                                <a href="{{ asset('storage/' . $sell->proof) }}" target="_blank"
                                                    class="text-neon hover-text-neon">
                                                    <img src="{{ asset('storage/' . $sell->proof) }}" alt="Proof"
                                                        class="img-fluid rounded" style="max-width: 100px;">
                                                </a>
                                            @else
                                                <span class="text-white">No proof uploaded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card bg-dark bg-opacity-75 border-neon text-center" data-aos="fade-up"
                                data-aos-duration="800" data-aos-delay="200">
                                <div class="card-body">
                                    <i class="bi bi-file-earmark-x w-10 h-10 mb-2 text-muted mx-auto d-block"></i>
                                    <p class="text-muted">No sell trades found.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @if ($sellTrades->hasPages())
                        <div class="d-flex justify-content-center mt-4" data-aos="fade-up" data-aos-duration="800"
                            data-aos-delay="300">
                            {{ $sellTrades->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                </div>

                <!-- Withdrawals -->
                <div class="tab-pane fade" id="withdrawals" role="tabpanel" aria-labelledby="withdrawals-tab">
                    <div id="withdrawalsList">
                        @forelse($withdrawals as $withdrawal)
                            <div class="card bg-dark bg-opacity-75 border-neon mb-3 trade-card"
                                data-trade-id="{{ $withdrawal->id }}" data-coin="{{ $withdrawal->currency ?? 'NGN' }}"
                                data-status="{{ $withdrawal->status }}"
                                data-date="{{ $withdrawal->created_at->format('Y-m-d') }}"
                                data-user-name="{{ $withdrawal->user->name ?? '' }}"
                                data-transaction-ref="{{ $withdrawal->reference ?? '' }}" data-aos="fade-up"
                                data-aos-duration="800" data-aos-delay="200">
                                <div class="card-body">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-warning text-dark">Withdrawal</span>
                                            <span
                                                class="text-white fw-medium">{{ strtoupper($withdrawal->currency ?? 'NGN') }}</span>
                                        </div>
                                        <small
                                            class="text-muted">{{ $withdrawal->created_at->format('d M, Y H:i') }}</small>
                                    </div>
                                    <div class="row g-3 text-sm text-white">
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">User</p>
                                            <p class="fw-medium">{{ $withdrawal->user->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">Amount</p>
                                            <p class="fw-medium">₦{{ number_format($withdrawal->amount, 2) }}
                                                {{ strtoupper($withdrawal->currency ?? 'NGN') }}</p>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-muted mb-1">Status</p>
                                            <form method="POST"
                                                action="{{ route('withdrawal.updateStatus', $withdrawal->id) }}"
                                                class="status-form">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status"
                                                    class="form-select form-select-sm bg-dark text-white border-neon status-select"
                                                    data-trade-id="{{ $withdrawal->id }}"
                                                    data-original-status="{{ $withdrawal->status }}"
                                                    aria-label="Update status">
                                                    <option value="pending"
                                                        {{ $withdrawal->status == 'pending' ? 'selected' : '' }}>Pending
                                                    </option>
                                                    <option value="approved"
                                                        {{ $withdrawal->status == 'approved' ? 'selected' : '' }}>Approved
                                                    </option>
                                                    <option value="cancelled"
                                                        {{ $withdrawal->status == 'cancelled' ? 'selected' : '' }}>
                                                        Cancelled</option>
                                                </select>
                                            </form>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-white mb-1">Bank Details</p>
                                            <button
                                                class="copy-btn text-muted text-monospace text-sm text-truncate d-block hover-text-neon"
                                                data-copy="{{ json_decode($withdrawal->bank_account, true)['account_number'] ?? 'N/A' }}"
                                                title="{{ json_decode($withdrawal->bank_account, true)['account_number'] ?? 'N/A' }}"
                                                aria-label="Copy account number">
                                                {{ \Illuminate\Support\Str::limit(json_decode($withdrawal->bank_account, true)['account_number'] ?? 'N/A', 16, '...') }}
                                            </button>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <p class="text-white mb-1">Reference</p>
                                            <button
                                                class="copy-btn text-muted text-monospace text-sm text-truncate d-block hover-text-neon"
                                                data-copy="{{ $withdrawal->reference ?? '' }}"
                                                title="{{ $withdrawal->reference ?? 'N/A' }}"
                                                aria-label="Copy reference">
                                                {{ \Illuminate\Support\Str::limit($withdrawal->reference ?? 'N/A', 16, '...') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card bg-dark bg-opacity-75 border-neon text-center" data-aos="fade-up"
                                data-aos-duration="800" data-aos-delay="200">
                                <div class="card-body">
                                    <i class="bi bi-file-earmark-x w-10 h-10 mb-2 text-muted mx-auto d-block"></i>
                                    <p class="text-muted">No withdrawals found.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @if ($withdrawals->hasPages())
                        <div class="d-flex justify-content-center mt-4" data-aos="fade-up" data-aos-duration="800"
                            data-aos-delay="300">
                            {{ $withdrawals->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-5 pt-4 border-top border-neon text-center text-muted" data-aos="fade-up"
                data-aos-duration="800" data-aos-delay="400">
                <p>© {{ date('Y') }} Crypto Exchange. All rights reserved.</p>
                <div class="mt-2 d-flex justify-content-center gap-3">
                    <a href="#" class="text-muted hover-text-neon">Privacy Policy</a>
                    <a href="#" class="text-muted hover-text-neon">Terms of Service</a>
                    <a href="#" class="text-muted hover-text-neon">Contact Us</a>
                </div>
            </footer>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading"
        class="d-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center z-50">
        <div class="spinner-border text-neon" role="status" aria-label="Loading"></div>
    </div>

    <!-- Toasts -->
    <div id="successToast" class="toast position-fixed bottom-0 end-0 m-3 bg-success text-white border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
            <span id="successMessage"></span>
        </div>
    </div>
    <div id="errorToast" class="toast position-fixed bottom-0 end-0 m-3 bg-danger text-white border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
            <span id="errorMessage"></span>
        </div>
    </div>
    <div id="copyToast" class="toast position-fixed bottom-0 end-0 m-3 bg-neon text-dark border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
            Copied to clipboard!
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        AOS.init();

        // Utility: Debounce function
        const debounce = (func, wait) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        };

        // Show Toast
        const showToast = (type, message) => {
            const toastElement = document.getElementById(`${type}Toast`);
            if (type !== 'copy') {
                document.getElementById(`${type}Message`).textContent = message;
            }
            const toast = new bootstrap.Toast(toastElement, {
                delay: 3000
            });
            toast.show();
        };

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');
            const clearFilters = document.getElementById('clearFilters');
            const loading = document.getElementById('loading');
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');

            // Theme Toggle
            const setTheme = (theme) => {
                document.documentElement.setAttribute('data-theme', theme);
                themeIcon.className = theme === 'dark' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
                localStorage.setItem('theme', theme);
            };
            const savedTheme = localStorage.getItem('theme') || 'dark';
            setTheme(savedTheme);
            themeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });

            // Filter Trades
            const filterTrades = debounce(() => {
                const search = searchInput.value.toLowerCase();
                const status = statusFilter.value;
                const date = dateFilter.value;
                const tabs = ['buyTradesList', 'sellTradesList', 'withdrawalsList'];

                tabs.forEach(tabId => {
                    const tradeCards = document.querySelectorAll(`#${tabId} .trade-card`);
                    let visibleCount = 0;

                    tradeCards.forEach(card => {
                        const tradeId = card.dataset.tradeId.toLowerCase();
                        const coin = card.dataset.coin.toLowerCase() || '';
                        const statusCard = card.dataset.status;
                        const dateCard = card.dataset.date;
                        const userName = card.dataset.userName.toLowerCase() || '';
                        const transactionRef = card.dataset.transactionRef.toLowerCase() ||
                            '';

                        const matchesSearch = !search || tradeId.includes(search) || coin
                            .includes(search) || userName.includes(search) || transactionRef
                            .includes(search);
                        const matchesStatus = !status || statusCard === status;
                        const matchesDate = !date || dateCard === date;

                        if (matchesSearch && matchesStatus && matchesDate) {
                            card.classList.remove('d-none');
                            visibleCount++;
                        } else {
                            card.classList.add('d-none');
                        }
                    });

                    const emptyState = document.querySelector(`#${tabId} .card.text-center`);
                    if (emptyState) {
                        emptyState.classList.toggle('d-none', visibleCount > 0);
                    }
                });
            }, 300);

            [searchInput, statusFilter, dateFilter].forEach(filter => filter.addEventListener('input',
                filterTrades));
            clearFilters.addEventListener('click', () => {
                searchInput.value = '';
                statusFilter.value = '';
                dateFilter.value = '';
                filterTrades();
            });

            // Export to CSV
            document.getElementById('exportBtn').addEventListener('click', () => {
                const activeTab = document.querySelector('.nav-tabs .nav-link.active').getAttribute('href')
                    .substring(1);
                const tradeCards = document.querySelectorAll(`#${activeTab} .trade-card:not(.d-none)`);
                const data = Array.from(tradeCards).map(card => {
                    if (activeTab === 'buy-trades') {
                        return {
                            ID: card.dataset.tradeId,
                            Type: 'Buy',
                            User: card.dataset.userName || 'N/A',
                            Coin: card.dataset.coin || 'N/A',
                            'USD Amount': card.querySelector(
                                '.row > div:nth-child(2) p:nth-child(2)').textContent,
                            'Naira Amount': card.querySelector(
                                '.row > div:nth-child(3) p:nth-child(2)').textContent,
                            Status: card.dataset.status,
                            Wallet: card.querySelector('.row > div:nth-child(5) .copy-btn').dataset
                                .copy,
                            'Transaction Ref': card.querySelector(
                                '.row > div:nth-child(6) .copy-btn').dataset.copy,
                            Date: card.querySelector('.text-muted').textContent
                        };
                    } else if (activeTab === 'sell-trades') {
                        return {
                            ID: card.dataset.tradeId,
                            Type: 'Sell',
                            User: card.dataset.userName,
                            Coin: card.dataset.coin,
                            'Crypto Amount': card.querySelector(
                                '.row > div:nth-child(2) p:nth-child(2)').textContent,
                            'Naira Amount': card.querySelector(
                                '.row > div:nth-child(3) p:nth-child(2)').textContent,
                            Status: card.dataset.status,
                            'Transaction Ref': card.querySelector(
                                '.row > div:nth-child(5) .copy-btn').dataset.copy,
                            Date: card.querySelector('.text-muted').textContent
                        };
                    } else {
                        return {
                            ID: card.dataset.tradeId,
                            Type: 'Withdrawal',
                            User: card.dataset.userName,
                            Coin: card.dataset.coin,
                            'Crypto Amount': card.querySelector(
                                '.row > div:nth-child(2) p:nth-child(2)').textContent,
                            Status: card.dataset.status,
                            Wallet: card.querySelector('.row > div:nth-child(4) .copy-btn').dataset
                                .copy,
                            'Transaction Ref': card.querySelector(
                                '.row > div:nth-child(5) .copy-btn').dataset.copy,
                            Date: card.querySelector('.text-muted').textContent
                        };
                    }
                });

                const headers = activeTab === 'buy-trades' ?
                    'ID,Type,User,Coin,USD Amount,Naira Amount,Status,Wallet,Transaction Ref,Date' :
                    'ID,Type,User,Coin,Crypto Amount,Naira Amount,Status,Transaction Ref,Date';

                if (activeTab === 'withdrawals') {
                    headers = 'ID,Type,User,Coin,Crypto Amount,Status,Wallet,Transaction Ref,Date';
                }

                const csv = [
                    headers,
                    ...data.map(row => Object.values(row).map(val => `"${val.replace(/"/g, '""')}"`)
                        .join(','))
                ].join('\n');

                const blob = new Blob([csv], {
                    type: 'text/csv'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${activeTab}_${new Date().toISOString().split('T')[0]}.csv`;
                a.click();
                URL.revokeObjectURL(url);
            });

            // Copy to Clipboard
            const attachCopyListeners = () => {
                document.querySelectorAll('.copy-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        navigator.clipboard.writeText(btn.dataset.copy)
                            .then(() => showToast('copy', 'Copied to clipboard!'))
                            .catch(err => {
                                console.error('Copy failed:', err);
                                showToast('error', 'Failed to copy to clipboard');
                            });
                    });
                });
            };

            // Status Update with AJAX
            const attachStatusListeners = () => {
                document.querySelectorAll('.status-select').forEach(select => {
                    select.addEventListener('change', async (e) => {
                        const newStatus = e.target.value;
                        const tradeId = e.target.dataset.tradeId;
                        const originalStatus = e.target.dataset.originalStatus;
                        const form = e.target.closest('.status-form');
                        const card = form.closest('.trade-card');

                        if (confirm(
                            `Update transaction ID ${tradeId} to "${newStatus}"?`)) {
                            loading.classList.remove('d-none');
                            try {
                                const response = await fetch(form.action, {
                                    method: 'PATCH',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content,
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        status: newStatus
                                    })
                                });

                                const data = await response.json();
                                if (response.ok) {
                                    showToast('success', data.message ||
                                        `Status updated to ${newStatus}`);
                                    card.dataset.status = newStatus;
                                    e.target.dataset.originalStatus = newStatus;
                                    filterTrades();
                                } else {
                                    throw new Error(data.message ||
                                        'Failed to update status');
                                }
                            } catch (error) {
                                console.error('Status update failed:', error);
                                showToast('error',
                                    `Failed to update status: ${error.message}`);
                                e.target.value = originalStatus;
                            } finally {
                                loading.classList.add('d-none');
                            }
                        } else {
                            e.target.value = originalStatus;
                        }
                    });
                });
            };

            attachCopyListeners();
            attachStatusListeners();
            filterTrades();
        });
    </script>

    <style>
        :root {
            --neon: #00ff88;
            --dark-bg: #0a0e17;
            --dark-card: rgba(20, 25, 40, 0.85);
            --border-neon: #00ff8833;
            --text-white: #f0f0f5;
            --text-muted: #8a93a6;
            --light-bg: #e6e9ef;
            --light-card: #ffffff;
            --light-border: #d1d9e6;
        }

        .crypto-bg {
            background: linear-gradient(135deg, #0a0e17, #1a2333);
            color: var(--text-white);
        }

        [data-theme="dark"] {
            background-color: var(--dark-bg);
            color: var(--text-white);
        }

        [data-theme="dark"] .main-container {
            background: var(--dark-card);
            backdrop-filter: blur(10px);
            border-color: var(--border-neon);
        }

        [data-theme="dark"] .card {
            background: var(--dark-card);
            border-color: var(--border-neon);
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: #1c2535;
            border-color: var(--border-neon);
            color: var(--text-white);
        }

        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .form-select:focus {
            border-color: var(--neon);
            box-shadow: 0 0 10px var(--neon);
            background-color: #1c2535;
        }

        [data-theme="dark"] .nav-tabs .nav-link {
            color: var(--text-muted);
        }

        [data-theme="dark"] .nav-tabs .nav-link.active {
            background-color: var(--dark-card);
            border-color: var(--border-neon);
            color: var(--neon);
        }

        [data-theme="light"] {
            background-color: var(--light-bg);
            color: #1a2333;
        }

        [data-theme="light"] .main-container {
            background: var(--light-card);
            border-color: var(--light-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        [data-theme="light"] .card {
            background: var(--light-card);
            border-color: var(--light-border);
        }

        [data-theme="light"] .form-control,
        [data-theme="light"] .form-select {
            background-color: #ffffff;
            border-color: var(--light-border);
            color: #1a2333;
        }

        [data-theme="light"] .form-control:focus,
        [data-theme="light"] .form-select:focus {
            border-color: var(--neon);
            box-shadow: 0 0 10px var(--neon);
        }

        [data-theme="light"] .nav-tabs .nav-link {
            color: #1a2333;
        }

        [data-theme="light"] .nav-tabs .nav-link.active {
            background-color: var(--light-card);
            border-color: var(--light-border);
            color: var(--neon);
        }

        .main-container {
            max-width: 1400px;
            border-radius: 12px;
        }

        .shadow-glow {
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
        }

        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 8px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 255, 136, 0.3);
        }

        .text-neon {
            color: var(--neon) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .btn-outline-neon {
            border-color: var(--neon);
            color: var(--neon);
        }

        .btn-outline-neon:hover {
            background-color: var(--neon);
            color: var(--dark-bg);
        }

        .border-neon {
            border-color: var(--border-neon) !important;
        }

        .hover-text-neon:hover {
            color: var(--neon) !important;
        }

        .text-truncate {
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 576px) {
            .main-container {
                padding: 1rem;
            }

            .trade-card {
                padding: 0.75rem;
            }

            .row.g-3 {
                font-size: 0.875rem;
            }
        }
    </style>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-trades.js') }}"></script>
@endpush
