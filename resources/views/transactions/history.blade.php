@extends('layout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
    .table-modern {
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }
    .table-modern th {
        background: linear-gradient(135deg, #1a3c34, #2e6b5e);
        color: white;
        font-weight: 600;
        padding: 1.2rem;
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    .table-modern td {
        padding: 1.2rem;
        vertical-align: middle;
        font-size: 0.95rem;
    }
    .table-modern tr {
        transition: all 0.3s ease;
    }
    .table-modern tr:hover {
        background-color: #f0fdf4;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-pending {
        background-color: #fef3c7;
        color: #d97706;
    }
    .status-completed {
        background-color: #d1fae5;
        color: #059669;
    }
    .status-failed {
        background-color: #fee2e2;
        color: #dc2626;
    }
    .status-cancelled {
        background-color: #e5e7eb;
        color: #4b5563;
    }
    .filter-form {
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    .btn-primary {
        background: linear-gradient(135deg, #1a3c34, #2e6b5e);
        border: none;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #2e6b5e, #1a3c34);
        transform: translateY(-1px);
    }
    .btn-export {
        background: #059669;
        color: white;
    }
    .btn-export:hover {
        background: #047857;
    }
    @media (max-width: 768px) {
        .filter-form .row > div {
            margin-bottom: 1rem;
        }
        .table-modern {
            font-size: 0.85rem;
        }
        .table-modern th, .table-modern td {
            padding: 0.8rem;
        }
    }
</style>

<div class="container my-5">
    <h3 class="text-2xl font-bold text-green-700 mb-4 animate__animated animate__fadeIn">Your Transactions</h3>

    <!-- Filter Form -->
    <div class="filter-form animate__animated animate__fadeInDown">
        <form method="GET" action="{{ route('transactions.history') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <input type="text" name="search" class="form-control" placeholder="Search coin, ref..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="buy" {{ request('type') == 'buy' ? 'selected' : '' }}>Buy</option>
                        <option value="sell" {{ request('type') == 'sell' ? 'selected' : '' }}>Sell</option>
                        <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <input type="number" name="min_amount" class="form-control" placeholder="Min Amount (₦)" value="{{ request('min_amount') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <input type="number" name="max_amount" class="form-control" placeholder="Max Amount (₦)" value="{{ request('max_amount') }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <select name="per_page" class="form-control" onchange="document.getElementById('filterForm').submit()">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 per page</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per page</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-12">
                    <button type="submit" class="btn btn-primary w-full">Filter</button>
                </div>
                <div class="col-md-3 col-sm-12">
                    <a href="{{ route('transactions.history') }}" class="btn btn-secondary w-full">Clear</a>
                </div>
                <div class="col-md-3 col-sm-12">
                    <a href="{{ route('transactions.history', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-export w-full">Export CSV</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="table-responsive">
        <table class="table table-modern animate__animated animate__fadeInUp">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Coin</th>
                    <th>Amount (NGN)</th>
                    <th>Amount (USD)</th>
                    <th>Status</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allTrades as $trade)
                    <tr class="animate__animated animate__fadeIn" style="animation-delay: {{ $loop->index * 0.1 }}s">
                        <td>{{ ucfirst($trade->type) }}</td>
                        <td>{{ $trade->coin ?? 'N/A' }}</td>
                        <td>₦{{ number_format($trade->naira_amount ?? $trade->amount, 2) }}</td>
                        <td>{{ $trade->usd_amount ? '$' . number_format($trade->usd_amount, 2) : 'N/A' }}</td>
                        <td>
                            <span class="status-badge status-{{ $trade->status }}">
                                {{ ucfirst($trade->status) }}
                            </span>
                        </td>
                        <td>
                            @if ($trade->payment_method && $trade->type === 'withdrawal')
                                @php
                                    $bankDetails = json_decode($trade->payment_method, true);
                                @endphp
                                {{ $bankDetails['bank_name'] ?? 'N/A' }} ({{ $bankDetails['account_number'] ?? 'N/A' }})
                            @else
                                {{ $trade->payment_method ?? 'N/A' }}
                            @endif
                        </td>
                        <td>{{ $trade->reference ?? 'N/A' }}</td>
                        <td>{{ $trade->created_at->format('d M, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $allTrades->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
    // Auto-clear search input on clear button click
    document.querySelector('.btn-secondary').addEventListener('click', () => {
        document.querySelectorAll('.filter-form input, .filter-form select').forEach(el => {
            if (el.type !== 'submit') el.value = '';
        });
    });
</script>
@endsection