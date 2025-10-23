@extends('layout')

@section('title', 'Deposit History')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4 animate-slide-in">
        <h2 class="text-white fw-bold">Deposit History</h2>
        <a href="{{ route('deposits.create') }}" class="btn btn-primary btn-make-deposit animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle me-1" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Make New Deposit
        </a>
    </div>

    @if ($deposits->isEmpty())
        <div class="alert alert-info text-center animate-fade-in">
            No deposits found. Click "Make New Deposit" to get started!
        </div>
    @else
        <div class="card bg-dark text-white shadow-lg animate-fade-in">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-borderless mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="sortable" data-sort="amount">
                                    Amount <span class="sort-icon"></span>
                                </th>
                                <th scope="col" class="sortable" data-sort="status">
                                    Status <span class="sort-icon"></span>
                                </th>
                                <th scope="col" class="sortable" data-sort="transaction_ref">
                                    Transaction Ref <span class="sort-icon"></span>
                                </th>
                                <th scope="col">Admin Note</th>
                                <th scope="col" class="sortable" data-sort="created_at">
                                    Created At <span class="sort-icon"></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deposits as $index => $deposit)
                                <tr class="animate-row" style="--row-index: {{ $index }}">
                                    <td>{{ number_format($deposit->amount, 2) }} NGN</td>
                                    <td>
                                        <span class="badge {{ ($deposit->status === 'pending' ? 'bg-warning text-dark' : ($deposit->status === 'approved' ? 'bg-success' : ($deposit->status === 'rejected' ? 'bg-danger' : 'bg-secondary'))) }}">
                                            {{ ucfirst($deposit->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $deposit->transaction_ref }}</td>
                                    <td>{{ $deposit->admin_note ?? 'N/A' }}</td>
                                    <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex justify-content-center">
            {{ $deposits->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        @if (session('success'))
            <div id="success-toast" class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sortableHeaders = document.querySelectorAll('.sortable');
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function () {
                const sortKey = this.dataset.sort;
                const currentUrl = new URL(window.location);
                const currentSort = currentUrl.searchParams.get('sort') || 'created_at';
                const currentDirection = currentUrl.searchParams.get('direction') || 'desc';
                const newDirection = currentSort === sortKey && currentDirection === 'asc' ? 'desc' : 'asc';
                currentUrl.searchParams.set('sort', sortKey);
                currentUrl.searchParams.set('direction', newDirection);
                window.location.href = currentUrl.toString();
            });
        });

        // Highlight sorted column
        const urlParams = new URLSearchParams(window.location.search);
        const sortedKey = urlParams.get('sort') || 'created_at';
        const direction = urlParams.get('direction') || 'desc';
        const sortedHeader = document.querySelector(`.sortable[data-sort="${sortedKey}"] .sort-icon`);
        if (sortedHeader) {
            sortedHeader.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    });
</script>

<style>
    .card {
        background-color: #1c2526;
        border-radius: 12px;
        border: 1px solid #3a3f41;
        box-shadow: 0 4px 20px rgba(0, 255, 0, 0.1);
        transition: box-shadow 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 6px 30px rgba(0, 255, 0, 0.2);
    }
    .table-dark {
        --bs-table-bg: #1c2526;
        --bs-table-hover-bg: #2a2f31;
    }
    .table th {
        color: #b0b0b0;
        font-weight: 600;
        padding: 1rem;
        text-transform: uppercase;
        font-size: 0.9rem;
    }
    .table td {
        padding: 1rem;
        vertical-align: middle;
        color: #ffffff;
    }
    .btn-make-deposit {
        background-color: #00cc00;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .btn-make-deposit:hover {
        background-color: #00b300;
        transform: scale(1.05);
    }
    .badge {
        font-size: 0.85rem;
        padding: 0.5em 1em;
        border-radius: 6px;
    }
    .sortable {
        cursor: pointer;
        user-select: none;
    }
    .sort-icon::after {
        content: '↕';
        margin-left: 0.5rem;
        opacity: 0.5;
        font-size: 0.8rem;
    }
    .sort-asc::after {
        content: '↑';
        opacity: 1;
    }
    .sort-desc::after {
        content: '↓';
        opacity: 1;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    .animate-slide-in {
        animation: slideIn 0.5s ease-out forwards;
    }
    .animate-row {
        animation: fadeIn 0.5s ease-out forwards;
        animation-delay: calc(var(--row-index) * 0.1s);
    }
    .animate-pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>
@endsection