@extends('layout')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
        /* Dark Mode Variables */
        --dark-bg: #1a1a1a;
        --dark-card-bg: #2c2c2c;
        --dark-text: #00871b;
        --dark-text-muted: #b0b0b0;
        --dark-gradient-bg: linear-gradient(135deg, #2c2c2c, #3a3a3a);
        --dark-border: #4a4a4a;
        --dark-table-hover: #3a3a3a;

        /* Light Mode Variables */
        --light-bg: #f5f5f5;
        --light-card-bg: #ffffff;
        --light-text: #1a1a1a;
        --light-text-muted: #6c757d;
        --light-gradient-bg: linear-gradient(135deg, #ffffff, #e9ecef);
        --light-border: #dee2e6;
        --light-table-hover: #e9ecef;

        /* Shared Variables */
        --primary-green: #28a745;
        --primary-red: #dc3545;
        --glow-color: rgba(40, 167, 69, 0.5);
        --glow-color-red: rgba(220, 53, 69, 0.5);
        --transition: all 0.3s ease;
    }

    /* Default to Dark Mode */
    body {
        background-color: var(--dark-bg);
        color: var(--dark-text);
        font-family: 'Poppins', sans-serif;
        overflow-x: hidden;
        transition: var(--transition);
    }

    body.light-mode {
        background-color: var(--light-bg);
        color: var(--light-text);
    }

    .container {
        padding: 3rem 1rem;
    }

    .card {
        background-color: var(--dark-card-bg) !important;
        color: var(--dark-text);
        border: none !important;
        border-radius: 1rem;
        box-shadow: 0 4px 15px var(--glow-color);
        transition: var(--transition);
    }

    .light-mode .card {
        background-color: var(--light-card-bg) !important;
        color: var(--light-text);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px var(--glow-color);
    }

    .light-mode .card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .text-dark {
        color: var(--dark-text) !important;
    }

    .light-mode .text-dark {
        color: var(--light-text) !important;
    }

    .text-muted {
        color: var(--dark-text-muted) !important;
    }

    .light-mode .text-muted {
        color: var(--light-text-muted) !important;
    }

    .text-green-dark {
        color: var(--primary-green) !important;
    }

    .text-green {
        color: var(--primary-green) !important;
    }

    .bg-gradient-welcome {
        background: var(--dark-gradient-bg) !important;
    }

    .light-mode .bg-gradient-welcome {
        background: var(--light-gradient-bg) !important;
    }

    .btn-green {
        background-color: var(--primary-green);
        color: #ffffff;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        transition: var(--transition);
        box-shadow: 0 2px 10px var(--glow-color);
    }

    .btn-green:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px var(--glow-color);
        background-color: #2ecc71;
    }

    .btn-outline-green {
        border-color: var(--primary-green);
        color: var(--primary-green);
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        transition: var(--transition);
    }

    .btn-outline-green:hover {
        background-color: var(--primary-green);
        color: #ffffff;
        transform: scale(1.05);
        box-shadow: 0 4px 15px var(--glow-color);
    }

    .btn-transaction {
        border-color: var(--primary-green);
        color: var(--dark-text);
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        transition: var(--transition);
    }

    .light-mode .btn-transaction {
        color: var(--light-text);
    }

    .btn-transaction:hover {
        background-color: var(--primary-green);
        color: #ffffff;
        transform: scale(1.05);
        box-shadow: 0 4px 15px var(--glow-color);
    }

    .btn-withdraw {
        background: linear-gradient(90deg, #28a745, #2ecc71);
        color: #ffffff;
        border: none;
        border-radius: 0.5rem;
        padding: 0.5rem 1.5rem;
        font-size: 0.9rem;
        transition: var(--transition);
        box-shadow: 0 2px 10px var(--glow-color);
    }

    .btn-withdraw:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px var(--glow-color);
    }

    .btn-view-more {
        background: transparent;
        border: 2px solid var(--primary-green);
        color: var(--primary-green);
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: var(--transition);
        box-shadow: 0 2px 10px var(--glow-color);
        width: 100%;
        text-align: center;
    }

    .btn-view-more:hover {
        background-color: var(--primary-green);
        color: #ffffff;
        transform: scale(1.02);
        box-shadow: 0 4px 15px var(--glow-color);
    }

    .transaction-type {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 0.4rem;
        font-size: 0.9rem;
        font-weight: 500;
        text-transform: capitalize;
    }

    .transaction-type-buy {
        background-color: var(--primary-green);
        color: #ffffff;
        box-shadow: 0 2px 8px var(--glow-color);
    }

    .transaction-type-sell {
        background-color: var(--primary-red);
        color: #ffffff;
        box-shadow: 0 2px 8px var(--glow-color-red);
    }

    .transaction-type-other {
        color: var(--dark-text);
    }

    .light-mode .transaction-type-other {
        color: var(--light-text);
    }

    .amount-buy {
        color: var(--primary-green) !important;
        font-weight: 500;
    }

    .amount-sell {
        color: var(--primary-red) !important;
        font-weight: 500;
    }

    .amount-withdrawal {
        color: var(--primary-red) !important;
        font-weight: 500;
    }

    .table {
        color: var(--dark-text);
        border-collapse: separate;
        border-spacing: 0;
    }

    .light-mode .table {
        color: var(--light-text);
    }

    .table th, .table td {
        border: none;
        padding: 1rem;
        border-bottom: 1px solid var(--dark-border);
    }

    .light-mode .table th, .light-mode .table td {
        border-bottom: 1px solid var(--light-border);
    }

    .table-hover tbody tr:hover {
        background-color: var(--dark-table-hover);
        box-shadow: 0 2px 10px var(--glow-color);
    }

    .light-mode .table-hover tbody tr:hover {
        background-color: var(--light-table-hover);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .form-control, .form-select {
        background-color: var(--dark-table-hover);
        color: var(--dark-text);
        border-color: var(--dark-border);
        border-radius: 0.5rem;
        transition: var(--transition);
    }

    .light-mode .form-control, .light-mode .form-select {
        background-color: #ffffff;
        color: var(--light-text);
        border-color: var(--light-border);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 8px var(--glow-color);
        background-color: var(--dark-table-hover);
    }

    .light-mode .form-control:focus, .light-mode .form-select:focus {
        background-color: #ffffff;
    }

    .form-control::placeholder {
        color: var(--dark-text-muted);
    }

    .light-mode .form-control::placeholder {
        color: var(--light-text-muted);
    }

    .dropdown-menu {
        background-color: var(--dark-card-bg);
        color: var(--dark-text);
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 15px var(--glow-color);
    }

    .light-mode .dropdown-menu {
        background-color: var(--light-card-bg);
        color: var(--light-text);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item {
        color: var(--dark-text);
        transition: var(--transition);
    }

    .light-mode .dropdown-item {
        color: var(--light-text);
    }

    .dropdown-item:hover {
        background-color: var(--dark-table-hover);
        color: var(--primary-green);
    }

    .light-mode .dropdown-item:hover {
        background-color: var(--light-table-hover);
    }

    .list-group-item {
        background-color: var(--dark-card-bg);
        color: var(--dark-text);
        border: none;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        transition: var(--transition);
    }

    .light-mode .list-group-item {
        background-color: var(--light-card-bg);
        color: var(--light-text);
    }

    .list-group-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px var(--glow-color);
    }

    .light-mode .list-group-item:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .crypto-icon {
        width: 24px;
        height: 24px;
        margin-right: 0.5rem;
        border-radius: 50%;
        box-shadow: 0 0 8px var(--glow-color);
    }

    .transaction-table-container {
        max-height: 300px;
        overflow-y: auto;
        overflow-x: auto;
        white-space: nowrap;
        border-radius: 0.5rem;
    }

    .transaction-table-container::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .transaction-table-container::-webkit-scrollbar-track {
        background: var(--dark-table-hover);
    }

    .light-mode .transaction-table-container::-webkit-scrollbar-track {
        background: var(--light-table-hover);
    }

    .transaction-table-container::-webkit-scrollbar-thumb {
        background: var(--primary-green);
        border-radius: 4px;
    }

    .spinner-border {
        border-color: var(--primary-green) transparent transparent transparent;
        animation: glowPulse 1.5s infinite;
    }

    footer.bg-dark {
        background-color: var(--dark-bg) !important;
        border-top: 1px solid var(--dark-border);
        padding: 2rem 0;
    }

    .light-mode footer.bg-dark {
        background-color: var(--light-bg) !important;
        border-top: 1px solid var(--light-border);
    }

    .text-white:hover {
        color: var(--primary-green) !important;
        transition: var(--transition);
    }

    /* Mode Toggle Styles */
    .mode-toggle {
        /* position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem; */
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--dark-border);
        transition: var(--transition);
        border-radius: 34px;
    }

    .light-mode .slider {
        background-color: var(--light-border);
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: var(--primary-green);
        transition: var(--transition);
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: var(--dark-table-hover);
    }

    .light-mode input:checked + .slider {
        background-color: var(--light-table-hover);
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .mode-label {
        font-size: 0.9rem;
        color: var(--dark-text-muted);
    }

    .light-mode .mode-label {
        color: var(--light-text-muted);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes zoomIn {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    @keyframes glowPulse {
        0% { box-shadow: 0 0 5px var(--glow-color); }
        50% { box-shadow: 0 0 20px var(--glow-color); }
        100% { box-shadow: 0 0 5px var(--glow-color); }
    }

    .animate-fade {
        animation: fadeIn 1s ease-in-out;
    }

    .animate-slide-up {
        animation: slideUp 0.8s ease-in-out;
    }

    .animate-zoom-in {
        animation: zoomIn 0.6s ease-in-out;
    }

    .animate-pulse {
        animation: glowPulse 2s infinite;
    }

    @media (max-width: 767px) {
        footer .row {
            text-align: center;
        }
        footer .list-unstyled {
            justify-content: center !important;
        }
        .mode-toggle {
            position: static;
            margin: 1rem auto;
            justify-content: center;
        }
    }
</style>

<div class="container py-5">

    <!-- Welcome Card Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 p-4 text-center bg-gradient-welcome animate-zoom-in position-relative">
                <!-- Mode Toggle Button -->
                <div class="mode-toggle">
                    <label class="switch">
                        <input type="checkbox" id="modeToggle">
                        <span class="slider"></span>
                    </label> <br>
                    {{-- <span class="mode-label">Toggle Theme</span> --}}
                </div>
                <h2 class="fw-bold text-green-dark mb-3">Welcome, {{ Auth::user()->name }}</h2>
                <div class="d-flex justify-content-center align-items-center gap-3">
                    <p class="display-5 text-dark mb-0 fs-3 fs-md-2">Balance: <span class="text-green fw-bold">₦{{ number_format(Auth::user()->balance, 2) }}</span></p>
                </div>
<div class="d-block d-lg-flex justify-content-lg-between">
                    <a href="{{ route('withdraw') }}" class="btn mt-3 btn-withdraw animate-pulse">Withdraw</a> <br/>
                    <a href="{{ route('deposits.index') }}" class="btn mt-3 btn-withdraw animate-pulse">Deposit</a>
</div>
                <div class="position-absolute top-0 end-0 p-2">
                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill"></i>
                        <span id="notification-count" class="badge bg-green rounded-pill position-absolute top-0 start-100 translate-middle">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="notifications-container">
                        <li class="dropdown-item text-muted d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm text-green me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            Loading notifications...
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-auto d-flex flex-wrap gap-3 justify-content-center">
            <a href="{{ Auth::user()->kyc_verified ? route('buy') : route('kyc.form') }}" class="btn btn-green btn-lg px-5 py-3 shadow-sm animate-pulse">Buy Crypto</a>
            <a href="{{ route('sell.form') }}" class="btn btn-outline-green btn-lg px-5 py-3 shadow-sm">Sell Crypto</a>
            <a href="{{ route('transactions.history') }}" class="btn btn-transaction btn-lg px-5 py-3 shadow-sm">Transaction History</a>
            <a href="{{ route('referrals') }}" class="btn btn-green btn-lg px-5 py-3 shadow-sm animate-pulse">Refer a Friend</a>
        </div>
    </div>

    <!-- Crypto Rates Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-4 animate-slide-up">
                <h3 class="fw-bold text-green-dark mb-4">Crypto Buy/Sell Rates</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="py-3 text-muted">Coin</th>
                                <th class="py-3 text-muted">Buy Rate (₦/USD)</th>
                                <th class="py-3 text-muted">Sell Rate (₦/USD)</th>
                            </tr>
                        </thead>
                        <tbody id="rates-container">
                            @if(isset($rates) && !empty($rates))
                                @foreach($rates as $coin => $rate)
                                    <tr>
                                        <td class="py-3">{{ $coin }}</td>
                                        <td class="py-3">₦{{ number_format($rate['buy_rate'], 2) }}</td>
                                        <td class="py-3">₦{{ number_format($rate['sell_rate'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-muted">
                                        <div class="spinner-border spinner-border-sm text-green me-2" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        Loading rates...
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Crypto Prices Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-4 animate-slide-up">
                <h3 class="fw-bold text-green-dark mb-4">Current Crypto Prices</h3>
                <div id="prices-container" class="list-group">
                    @if(isset($prices) && !empty($prices))
                        @foreach($prices as $coin => $price)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <img src="{{ $price['image'] ?? 'https://via.placeholder.com/24' }}" alt="{{ $coin }}" class="crypto-icon">
                                    {{ $coin }}
                                </span>
                                <span>${{ number_format($price['usd'], 2) }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="list-group-item text-center text-muted d-flex align-items-center justify-content-center">
                            <div class="spinner-border spinner-border-sm text-green me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            Loading...
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-12 col-lg-10">
            <div class="card border-0 shadow-lg rounded-4 p-4 animate-slide-up">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-2">
                    <h3 class="fw-bold text-green-dark">Transaction History</h3>
                    <div class="d-flex flex-column flex-md-row gap-2 align-items-center w-100 w-md-auto">
                        <input type="text" id="transaction-search" class="form-control form-control-sm mb-2 mb-md-0" placeholder="Search by coin or bank..." oninput="fetchTransactions()">
                        <select id="transaction-filter" class="form-select form-select-sm mb-2 mb-md-0" onchange="fetchTransactions()">
                            <option value="all">All Types</option>
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                            <option value="withdrawal">Withdrawal</option>
                        </select>
                    </div>
                </div>
                <div class="transaction-table-container">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="py-3 text-muted">Date</th>
                                <th class="py-3 text-muted">Type</th>
                                <th class="py-3 text-muted">Coin/Bank</th>
                                <th class="py-3 text-muted">Amount (₦)</th>
                                <th class="py-3 text-muted">Value (USD)</th>
                                <th class="py-3 text-muted">Status</th>
                            </tr>
                        </thead>
                        <tbody id="transactions-container">
                            @if(isset($transactions) && !$transactions->isEmpty())
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td class="py-3">{{ $transaction['created_at']->toDateString() }}</td>
                                        <td class="py-3">
                                            <span class="transaction-type {{ $transaction['type'] === 'buy' ? 'transaction-type-buy' : ($transaction['type'] === 'sell' ? 'transaction-type-sell' : 'transaction-type-other') }}">
                                                {{ ucfirst($transaction['type']) }}
                                            </span>
                                        </td>
                                        <td class="py-3">{{ $transaction['type'] === 'withdrawal' ? ($transaction['bank_account'] ?? 'N/A') : ($transaction['coin'] ?? 'N/A') }}</td>
                                        <td class="py-3 {{ $transaction['type'] === 'buy' ? 'amount-buy' : ($transaction['type'] === 'sell' ? 'amount-sell' : 'amount-withdrawal') }}">
                                            {{ $transaction['amount_ngn'] ? '₦' . number_format($transaction['amount_ngn'], 2) : 'N/A' }}
                                        </td>
                                        <td class="py-3">{{ $transaction['amount_usd'] ? '$' . number_format($transaction['amount_usd'], 2) : 'N/A' }}</td>
                                        <td class="py-3">{{ $transaction['status'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-muted">No transactions found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if(isset($transactions) && $transactions->count() > 3)
                    <a href="{{ route('transactions.history') }}" class="btn btn-view-more mt-3">View More Transactions</a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    let allTransactions = @json($transactions);

    // Mode Toggle Logic
    document.addEventListener('DOMContentLoaded', () => {
        const modeToggle = document.getElementById('modeToggle');
        const body = document.body;

        // Load saved mode from localStorage
        if (localStorage.getItem('theme') === 'light') {
            body.classList.add('light-mode');
            modeToggle.checked = true;
        }

        // Toggle mode on checkbox change
        modeToggle.addEventListener('change', () => {
            body.classList.toggle('light-mode');
            localStorage.setItem('theme', body.classList.contains('light-mode') ? 'light' : 'dark');
        });

        fetchRates();
        fetchPrices();
        renderTransactions(allTransactions);
    });

    function fetchRates() {
        const ratesContainer = document.getElementById('rates-container');
        ratesContainer.innerHTML = `
            <tr>
                <td colspan="3" class="py-4 text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-green me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Loading rates...
                </td>
            </tr>
        `;

        fetch('/crypto-rates', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                renderRates(data);
            })
            .catch(error => {
                console.error('Error fetching rates:', error);
                const fallbackRates = @json($rates);
                if (Object.keys(fallbackRates).length > 0) {
                    renderRates(Object.entries(fallbackRates).map(([coin, rates]) => ({
                        coin,
                        buy_rate: rates.buy_rate,
                        sell_rate: rates.sell_rate,
                    })));
                } else {
                    ratesContainer.innerHTML = `
                        <tr>
                            <td colspan="3" class="py-4 text-center text-danger">
                                Unable to load rates. Please try again later.
                            </td>
                        </tr>
                    `;
                }
            });
    }

    function renderRates(rates) {
        const ratesContainer = document.getElementById('rates-container');
        ratesContainer.innerHTML = '';
        if (!rates || rates.length === 0) {
            ratesContainer.innerHTML = `
                <tr>
                    <td colspan="3" class="py-4 text-center text-muted">
                        No rates available
                    </td>
                </tr>
            `;
        } else {
            rates.forEach(rate => {
                ratesContainer.innerHTML += `
                    <tr>
                        <td class="py-3">${rate.coin}</td>
                        <td class="py-3">₦${parseFloat(rate.buy_rate).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="py-3">₦${parseFloat(rate.sell_rate).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    </tr>
                `;
            });
        }
    }

    function fetchPrices() {
        const pricesContainer = document.getElementById('prices-container');
        pricesContainer.innerHTML = `
            <div class="list-group-item text-center text-muted d-flex align-items-center justify-content-center">
                <div class="spinner-border spinner-border-sm text-green me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Loading...
            </div>
        `;

        fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,tether,binancecoin,ripple', {
            headers: {
                'Accept': 'application/json',
            },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                renderPrices(data);
            })
            .catch(error => {
                console.error('Error fetching prices:', error);
                const fallbackPrices = @json(isset($prices) ? $prices : []);
                if (Object.keys(fallbackPrices).length > 0) {
                    renderPrices(Object.entries(fallbackPrices).map(([coin, price]) => ({
                        id: coin.toLowerCase(),
                        symbol: coin,
                        current_price: price.usd,
                        image: price.image,
                    })));
                } else {
                    pricesContainer.innerHTML = `
                        <div class="list-group-item text-center text-danger">
                            Unable to load prices. Please try again later.
                        </div>
                    `;
                }
            });
    }

    function renderPrices(prices) {
        const pricesContainer = document.getElementById('prices-container');
        pricesContainer.innerHTML = '';
        if (!prices || prices.length === 0) {
            pricesContainer.innerHTML = `
                <div class="list-group-item text-center text-muted">
                    No prices available
                </div>
            `;
        } else {
            prices.forEach(price => {
                pricesContainer.innerHTML += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <img src="${price.image || 'https://via.placeholder.com/24'}" alt="${price.symbol}" class="crypto-icon">
                            ${price.symbol.toUpperCase()}
                        </span>
                        <span>$${parseFloat(price.current_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                    </div>
                `;
            });
        }
    }

    function fetchTransactions() {
        const searchQuery = document.getElementById('transaction-search').value;
        const filter = document.getElementById('transaction-filter').value;

        console.log('Fetching transactions with:', { search: searchQuery, type: filter });

        fetch(`/transactions?search=${encodeURIComponent(searchQuery)}&type=${filter}&limit=5`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received transactions:', data);
                allTransactions = data;
                renderTransactions(data);
            })
            .catch(error => {
                console.error('Error fetching transactions:', error);
                document.getElementById('transactions-container').innerHTML = `
                    <tr>
                        <td colspan="6" class="py-4 text-center text-danger">
                            Error loading transactions: ${error.message}
                        </td>
                    </tr>`;
            });
    }

    function renderTransactions(transactions) {
        const transactionsContainer = document.getElementById('transactions-container');
        transactionsContainer.innerHTML = '';
        console.log('Rendering transactions:', transactions);
        if (!transactions || transactions.length === 0) {
            transactionsContainer.innerHTML = `
                <tr>
                    <td colspan="6" class="py-4 text-center text-muted">
                        No transactions found
                    </td>
                </tr>`;
        } else {
            transactions.forEach(transaction => {
                const typeClass = transaction.type === 'buy' ? 'transaction-type-buy' : (transaction.type === 'sell' ? 'transaction-type-sell' : 'transaction-type-other');
                const amountClass = transaction.type === 'buy' ? 'amount-buy' : (transaction.type === 'sell' ? 'amount-sell' : 'amount-withdrawal');
                transactionsContainer.innerHTML += `
                    <tr>
                        <td class="py-3">${new Date(transaction.created_at).toLocaleDateString()}</td>
                        <td class="py-3">
                            <span class="transaction-type ${typeClass}">
                                ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
                            </span>
                        </td>
                        <td class="py-3">${transaction.type === 'withdrawal' ? (transaction.bank_account || 'N/A') : (transaction.coin || 'N/A')}</td>
                        <td class="py-3 ${amountClass}">
                            ${transaction.amount_ngn ? '₦' + parseFloat(transaction.amount_ngn).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'N/A'}
                        </td>
                        <td class="py-3">${transaction.amount_usd ? '$' + parseFloat(transaction.amount_usd).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : 'N/A'}</td>
                        <td class="py-3">${transaction.status || 'N/A'}</td>
                    </tr>`;
            });
        }
    }
</script>
@include('footer')
@endsection