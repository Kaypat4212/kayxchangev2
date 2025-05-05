@extends('layout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="container py-4 py-md-5">
    <!-- Welcome Card with Notifications -->
    <div class="row justify-content-center mb-4 mb-md-5">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 p-3 p-md-4 text-center bg-gradient-welcome animate-fade position-relative">
                <h3 class="fw-bold text-green-dark mb-3">Welcome, {{ Auth::user()->name }}</h3>
                <p class="display-6 text-dark mb-0 fs-4 fs-md-3">Balance: <span class="text-green fw-bold">${{ number_format(Auth::user()->balance, 2) }}</span></p>
                <div class="position-absolute top-0 end-0 p-2">
                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill"></i>
                        <span id="notification-count" class="badge bg-green rounded-pill position-absolute top-0 start-100 translate-middle">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="notifications-container">
                        <li class="dropdown-item text-muted">Loading notifications...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Summary with Chart -->
    <div class="row justify-content-center mb-4 mb-md-5">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-3 p-md-4 animate-slide-up">
                <h4 class="fw-bold text-green-dark mb-4">Portfolio Summary</h4>
                <div class="row align-items-center">
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <div id="portfolio-container" class="row">
                            <div class="col-12 text-center text-muted">Loading portfolio...</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <canvas id="portfolio-chart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Buy/Sell Buttons -->
    <div class="row justify-content-center mb-4 mb-md-5">
        <div class="col-auto d-flex flex-wrap gap-3 justify-content-center">
            <button class="btn btn-green btn-lg px-4 py-2 px-md-5 py-md-3 shadow-sm animate-pulse" onclick="navigateTo('buy')">Buy Crypto</button>
            <button class="btn btn-outline-green btn-lg px-4 py-2 px-md-5 py-md-3 shadow-sm" onclick="navigateTo('sell')">Sell Crypto</button>
        </div>
    </div>

    <!-- Watchlist -->
    <div class="row justify-content-center mb-4 mb-md-5">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-3 p-md-4 animate-slide-up">
                <h4 class="fw-bold text-green-dark mb-4">Watchlist</h4>
                <div id="watchlist-container" class="list-group">
                    <div class="list-group-item text-center text-muted">Loading watchlist...</div>
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-outline-green btn-sm" onclick="addToWatchlist()">Add to Watchlist</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="row justify-content-center mb-4 mb-md-5">
        <div class="col-12 col-lg-10">
            <div class="card border-0 shadow-lg rounded-4 p-3 p-md-4 animate-slide-up">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-2">
                    <h4 class="fw-bold text-green-dark">Transaction History</h4>
                    <div class="d-flex flex-column flex-md-row gap-2 align-items-center w-100 w-md-auto">
                        <input type="text" id="transaction-search" class="form-control form-control-sm mb-2 mb-md-0" placeholder="Search by coin..." oninput="searchTransactions()">
                        <select id="transaction-filter" class="form-select form-select-sm mb-2 mb-md-0" onchange="filterTransactions()">
                            <option value="all">All Types</option>
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                        </select>
                        <button class="btn btn-outline-green btn-sm" onclick="exportTransactions()">Export CSV</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="py-2 py-md-3 text-muted">Date</th>
                                <th class="py-2 py-md-3 text-muted">Type</th>
                                <th class="py-2 py-md-3 text-muted">Coin</th>
                                <th class="py-2 py-md-3 text-muted">Amount</th>
                                <th class="py-2 py-md-3 text-muted">Value (USD)</th>
                                <th class="py-2 py-md-3 text-muted">Status</th>
                            </tr>
                        </thead>
                        <tbody id="transactions-container">
                            <tr>
                                <td colspan="6" class="py-4 text-center text-muted">Loading transactions...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="/transactions" class="btn btn-link text-green-dark fw-semibold">View All Transactions</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Crypto Prices -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-3 p-md-4 animate-slide-up">
                <h4 class="fw-bold text-green-dark mb-4">Current Crypto Prices</h4>
                <div id="prices-container" class="list-group">
                    <div class="list-group-item text-center text-muted">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function navigateTo(page) {
        window.location.href = `/${page}`;
    }

    let allTransactions = [];
    let portfolioData = [];

    // Fetch Crypto Prices with Price Change
    document.addEventListener('DOMContentLoaded', () => {
        fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,tether,litecoin,binancecoin')
            .then(response => response.json())
            .then(data => {
                const pricesContainer = document.getElementById('prices-container');
                pricesContainer.innerHTML = '';
                data.forEach(coin => {
                    const priceChangeClass = coin.price_change_percentage_24h >= 0 ? 'text-green' : 'text-danger';
                    pricesContainer.innerHTML += `
                        <div class="list-group-item d-flex align-items-center justify-content-between py-2 py-md-3 border-0">
                            <div class="d-flex align-items-center gap-2 gap-md-3">
                                <img src="${coin.image}" width="32" alt="${coin.name}" class="rounded-circle" />
                                <strong class="text-dark">${coin.name}</strong>
                            </div>
                            <div class="text-end">
                                <span class="text-green fw-bold">$${coin.current_price.toLocaleString()}</span>
                                <small class="${priceChangeClass} d-block">${coin.price_change_percentage_24h.toFixed(2)}% (24h)</small>
                            </div>
                        </div>`;
                });
            })
            .catch(() => {
                document.getElementById('prices-container').innerHTML = '<div class="list-group-item text-center text-danger">Error loading prices</div>';
            });

        // Fetch Portfolio Summary
        fetch('/api/user/portfolio')
            .then(response => response.json())
            .then(data => {
                portfolioData = data;
                const portfolioContainer = document.getElementById('portfolio-container');
                portfolioContainer.innerHTML = '';
                if (data.length === 0) {
                    portfolioContainer.innerHTML = '<div class="col-12 text-center text-muted">No assets in portfolio</div>';
                    return;
                }
                data.forEach(asset => {
                    portfolioContainer.innerHTML += `
                        <div class="col-6 col-md-4 mb-2 mb-md-3">
                            <div class="card border-0 shadow-sm rounded-3 p-2 p-md-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img src="${asset.image}" width="24" alt="${asset.name}" class="rounded-circle" />
                                    <strong class="text-dark">${asset.name}</strong>
                                </div>
                                <p class="mb-1 small">Holding: <span class="fw-bold">${asset.amount}</span></p>
                                <p class="mb-0 small">Value: <span class="text-green fw-bold">$${asset.value.toLocaleString()}</span></p>
                            </div>
                        </div>`;
                });
                // Render Portfolio Chart
                renderPortfolioChart(data);
            })
            .catch(() => {
                document.getElementById('portfolio-container').innerHTML = '<div class="col-12 text-center text-danger">Error loading portfolio</div>';
            });

        // Fetch Recent Transactions
        fetch('/api/user/transactions?limit=5')
            .then(response => response.json())
            .then(data => {
                allTransactions = data;
                renderTransactions(data);
            })
            .catch(() => {
                document.getElementById('transactions-container').innerHTML = '<tr><td colspan="6" class="py-4 text-center text-danger">Error loading transactions</td></tr>';
            });

        // Fetch Notifications (Mock API call)
        fetch('/api/user/notifications?limit=5')
            .then(response => response.json())
            .then(data => {
                const notificationsContainer = document.getElementById('notifications-container');
                const notificationCount = document.getElementById('notification-count');
                notificationsContainer.innerHTML = '';
                notificationCount.textContent = data.length;
                if (data.length === 0) {
                    notificationsContainer.innerHTML = '<li class="dropdown-item text-muted">No new notifications</li>';
                    return;
                }
                data.forEach(notification => {
                    notificationsContainer.innerHTML += `
                        <li class="dropdown-item">
                            <small>${notification.message}</small>
                            <div class="text-muted small">${new Date(notification.date).toLocaleString()}</div>
                        </li>`;
                });
            })
            .catch(() => {
                document.getElementById('notifications-container').innerHTML = '<li class="dropdown-item text-danger">Error loading notifications</li>';
            });

        // Fetch Watchlist (Mock API call)
        fetch('/api/user/watchlist')
            .then(response => response.json())
            .then(data => {
                const watchlistContainer = document.getElementById('watchlist-container');
                watchlistContainer.innerHTML = '';
                if (data.length === 0) {
                    watchlistContainer.innerHTML = '<div class="list-group-item text-center text-muted">No coins in watchlist</div>';
                    return;
                }
                data.forEach(coin => {
                    const priceChangeClass = coin.price_change_percentage_24h >= 0 ? 'text-green' : 'text-danger';
                    watchlistContainer.innerHTML += `
                        <div class="list-group-item d-flex align-items-center justify-content-between py-2 py-md-3 border-0">
                            <div class="d-flex align-items-center gap-2 gap-md-3">
                                <img src="${coin.image}" width="32" alt="${coin.name}" class="rounded-circle" />
                                <strong class="text-dark">${coin.name}</strong>
                            </div>
                            <div class="text-end">
                                <span class="text-green fw-bold">$${coin.current_price.toLocaleString()}</span>
                                <small class="${priceChangeClass} d-block">${coin.price_change_percentage_24h.toFixed(2)}% (24h)</small>
                            </div>
                        </div>`;
                });
            })
            .catch(() => {
                document.getElementById('watchlist-container').innerHTML = '<div class="list-group-item text-center text-danger">Error loading watchlist</div>';
            });
    });

    function renderTransactions(transactions) {
        const transactionsContainer = document.getElementById('transactions-container');
        transactionsContainer.innerHTML = '';
        if (transactions.length === 0) {
            transactionsContainer.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-muted">No transactions found</td></tr>';
            return;
        }
        transactions.forEach(tx => {
            const date = new Date(tx.date).toLocaleDateString();
            const typeClass = tx.type === 'buy' ? 'text-green' : 'text-danger';
            const statusBadge = tx.status === 'completed' ? 'bg-green' : 'bg-warning';
            transactionsContainer.innerHTML += `
                <tr class="align-middle">
                    <td class="py-2 py-md-3">${date}</td>
                    <td class="py-2 py-md-3 ${typeClass} text-capitalize">${tx.type}</td>
                    <td class="py-2 py-md-3">${tx.coin}</td>
                    <td class="py-2 py-md-3">${tx.amount}</td>
                    <td class="py-2 py-md-3">$${tx.value.toLocaleString()}</td>
                    <td class="py-2 py-md-3"><span class="badge ${statusBadge} text-white">${tx.status}</span></td>
                </tr>`;
        });
    }

    function filterTransactions() {
        const filter = document.getElementById('transaction-filter').value;
        const search = document.getElementById('transaction-search').value.toLowerCase();
        let filtered = filter === 'all' ? allTransactions : allTransactions.filter(tx => tx.type === filter);
        if (search) {
            filtered = filtered.filter(tx => tx.coin.toLowerCase().includes(search));
        }
        renderTransactions(filtered);
    }

    function searchTransactions() {
        filterTransactions();
    }

    function exportTransactions() {
        const csv = ['Date,Type,Coin,Amount,Value,Status'];
        allTransactions.forEach(tx => {
            const date = new Date(tx.date).toLocaleDateString();
            csv.push(`${date},${tx.type},${tx.coin},${tx.amount},${tx.value},${tx.status}`);
        });
        const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'transactions.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }

    function addToWatchlist() {
        // Mock function for adding to watchlist (replace with actual implementation)
        alert('Add to watchlist functionality to be implemented');
    }

    function renderPortfolioChart(data) {
        const ctx = document.getElementById('portfolio-chart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(asset => asset.name),
                datasets: [{
                    data: data.map(asset => asset.value),
                    backgroundColor: ['#4CAF50', '#66BB6A', '#81C784', '#A5D6A7', '#C8E6C9'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': $';
                                }
                                label += context.raw.toLocaleString();
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
</script>

<style>
    /* Green Color Scheme */
    .text-green { color: #2E7D32 !important; }
    .text-green-dark { color: #1B5E20 !important; }
    .bg-green { background-color: #2E7D32 !important; }
    .btn-green { background-color: #2E7D32; border-color: #2E7D32; color: #fff; }
    .btn-green:hover { background-color: #1B5E20; border-color: #1B5E20; color: #fff; }
    .btn-outline-green { border-color: #2E7D32; color: #2E7D32; }
    .btn-outline-green:hover { background-color: #2E7D32; color: #fff; }

    /* Custom Gradient for Welcome Card */
    .bg-gradient-welcome {
        background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);
    }

    /* Animations */
    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .animate-slide-up {
        animation: slideUp 0.6s ease-out forwards;
    }

    .animate-fade {
        animation: fadeIn 0.8s ease-out forwards;
    }

    /* Card Hover Effect */
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
    }

    /* Button Hover Effect */
    .btn-green, .btn-outline-green {
        transition: all 0.3s ease;
    }

    .btn-green:hover, .btn-outline-green:hover {
        transform: scale(1.05);
    }

    /* Table Styling */
    .table th, .table td {
        border: none;
    }

    .table-hover tbody tr:hover {
        background-color: #F1F8E9;
    }

    /* Notification Icon */
    .bi-bell-fill {
        font-size: 1.2rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 576px) {
        .display-6 { font-size: 1.8rem !important; }
        .btn-lg { font-size: 0.9rem; padding: 0.5rem 1rem; }
        .card { padding: 1rem !important; }
        .list-group-item img { width: 24px !important; }
        .table th, .table td { font-size: 0.85rem; padding: 0.5rem; }
        #portfolio-chart { height: 120px !important; }
    }

    @media (min-width: 576px) and (max-width: 768px) {
        .display-6 { font-size: 2.2rem !important; }
        .btn-lg { font-size: 1rem; }
        .card { padding: 1.5rem !important; }
        .list-group-item img { width: 28px !important; }
        .table th, .table td { font-size: 0.9rem; }
    }
</style>
@endsection