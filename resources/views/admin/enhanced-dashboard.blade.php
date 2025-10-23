@extends('layouts.admin')

@section('title', 'Enhanced Dashboard')

@push('styles')
<style>
    .metric-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        pointer-events: none;
    }

    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .metric-card.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .metric-card.warning {
        background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
    }

    .metric-card.info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .metric-card.danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0;
    }

    .metric-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .growth-indicator {
        font-size: 0.8rem;
        padding: 2px 8px;
        border-radius: 20px;
        background: rgba(255,255,255,0.2);
        display: inline-block;
        margin-top: 5px;
    }

    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .status-healthy { background-color: #28a745; }
    .status-warning { background-color: #ffc107; }
    .status-error { background-color: #dc3545; }

    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
        transition: background 0.3s ease;
    }

    .activity-item:hover {
        background: #f8f9fa;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
    }

    .activity-icon.trade { background: #007bff; }
    .activity-icon.user { background: #28a745; }
    .activity-icon.system { background: #6c757d; }

    .refresh-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        border: none;
        background: rgba(255,255,255,0.2);
        color: white;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .refresh-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(180deg);
    }

    .system-health {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Enhanced Dashboard</h1>
                <button class="btn btn-primary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>

    <!-- Overview Metrics -->
    <div class="row mb-4" id="overview-metrics">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card success p-4 position-relative">
                <button class="refresh-btn" onclick="refreshSection('overview')">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="metric-value" id="total-users">Loading...</div>
                <div class="metric-label">Total Users</div>
                <div class="growth-indicator" id="user-growth">
                    <i class="fas fa-arrow-up"></i> Loading...
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card info p-4 position-relative">
                <button class="refresh-btn" onclick="refreshSection('overview')">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="metric-value" id="active-trades">Loading...</div>
                <div class="metric-label">Active Trades</div>
                <div class="growth-indicator" id="telegram-users">
                    <i class="fas fa-telegram"></i> Loading...
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card warning p-4 position-relative">
                <button class="refresh-btn" onclick="refreshSection('overview')">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="metric-value" id="pending-items">Loading...</div>
                <div class="metric-label">Pending Items</div>
                <div class="growth-indicator" id="volume-today">
                    $Loading...
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card danger p-4 position-relative">
                <button class="refresh-btn" onclick="refreshSection('revenue')">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="metric-value" id="revenue-today">Loading...</div>
                <div class="metric-label">Revenue Today</div>
                <div class="growth-indicator" id="success-rate">
                    Loading...% Success
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Trading Volume (Last 7 Days)</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshCharts()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="volumeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Popular Coins</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="coinsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health & Recent Activities -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">System Health</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshSection('system')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div id="system-health">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Database</span>
                            <span><span class="status-indicator status-healthy"></span><span id="db-status">Checking...</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Telegram Bot</span>
                            <span><span class="status-indicator status-healthy"></span><span id="telegram-status">Checking...</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Cache System</span>
                            <span><span class="status-indicator status-healthy"></span><span id="cache-status">Checking...</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Storage Usage</span>
                            <span id="storage-usage">Checking...</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Response Time</span>
                            <span id="response-time">Checking...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshSection('activities')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <div id="recent-activities">
                        <div class="text-center p-4">
                            <i class="fas fa-spinner fa-spin"></i> Loading activities...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Growth Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">User Growth (Last 30 Days)</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshCharts()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Traders -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Top Traders</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshSection('traders')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="topTradersTable">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>User</th>
                                    <th>Buy Trades</th>
                                    <th>Sell Trades</th>
                                    <th>Total Trades</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Loading traders...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let charts = {};
let refreshInterval;

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    startAutoRefresh();
});

function initializeDashboard() {
    loadDashboardData();
}

function startAutoRefresh() {
    // Refresh data every 5 minutes
    refreshInterval = setInterval(function() {
        loadDashboardData(false);
    }, 300000);
}

function loadDashboardData(showLoading = true) {
    if (showLoading) {
        document.body.classList.add('loading');
    }

    fetch('/admin/analytics/dashboard-data')
        .then(response => response.json())
        .then(data => {
            updateOverviewMetrics(data.overview);
            updateTradingStats(data.trading);
            updateUserStats(data.users);
            updateRevenueStats(data.revenue);
            updateSystemHealth(data.system);
            updateRecentActivities(data.recent_activities);
            updateCharts(data.charts);
            updateTopTraders(data.users.top_traders);
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
            showNotification('Error loading dashboard data', 'error');
        })
        .finally(() => {
            document.body.classList.remove('loading');
        });
}

function updateOverviewMetrics(overview) {
    document.getElementById('total-users').textContent = formatNumber(overview.total_users);
    document.getElementById('active-trades').textContent = formatNumber(overview.active_trades);
    document.getElementById('pending-items').textContent = formatNumber(overview.pending_withdrawals + overview.pending_kyc);
    
    const userGrowth = document.getElementById('user-growth');
    userGrowth.innerHTML = `<i class="fas fa-arrow-${overview.new_users_growth >= 0 ? 'up' : 'down'}"></i> ${overview.new_users_growth}%`;
    
    document.getElementById('telegram-users').innerHTML = `<i class="fas fa-telegram"></i> ${formatNumber(overview.telegram_connected)}`;
    document.getElementById('volume-today').textContent = '$' + formatNumber(overview.total_volume_today, 2);
}

function updateRevenueStats(revenue) {
    document.getElementById('revenue-today').textContent = '$' + formatNumber(revenue.revenue_today, 2);
}

function updateSystemHealth(system) {
    updateHealthStatus('db-status', system.database_status);
    updateHealthStatus('telegram-status', system.telegram_bot_status);
    updateHealthStatus('cache-status', system.cache_status);
    
    document.getElementById('storage-usage').textContent = system.storage_usage.used;
    document.getElementById('response-time').textContent = system.response_time;
}

function updateHealthStatus(elementId, status) {
    const element = document.getElementById(elementId);
    const indicator = element.previousElementSibling;
    
    if (status.status === 'healthy') {
        indicator.className = 'status-indicator status-healthy';
        element.textContent = 'Healthy';
    } else if (status.status === 'warning') {
        indicator.className = 'status-indicator status-warning';
        element.textContent = 'Warning';
    } else {
        indicator.className = 'status-indicator status-error';
        element.textContent = 'Error';
    }
}

function updateRecentActivities(activities) {
    const container = document.getElementById('recent-activities');
    container.innerHTML = '';
    
    activities.forEach(activity => {
        const activityHtml = `
            <div class="activity-item">
                <div class="activity-icon ${activity.type}">
                    <i class="fas fa-${getActivityIcon(activity.type)}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="font-weight-bold">${activity.message}</div>
                    <small class="text-muted">${formatTime(activity.time)} • ${activity.user}</small>
                </div>
                <span class="badge badge-${getStatusBadge(activity.status)}">${activity.status}</span>
            </div>
        `;
        container.innerHTML += activityHtml;
    });
}

function updateCharts(chartData) {
    // Destroy existing charts
    Object.values(charts).forEach(chart => {
        if (chart) chart.destroy();
    });

    // Volume Chart
    const volumeCtx = document.getElementById('volumeChart').getContext('2d');
    charts.volume = new Chart(volumeCtx, {
        type: 'line',
        data: {
            labels: Object.keys(chartData.trading_volume),
            datasets: [{
                label: 'Trading Volume ($)',
                data: Object.values(chartData.trading_volume),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Coins Chart
    const coinsCtx = document.getElementById('coinsChart').getContext('2d');
    charts.coins = new Chart(coinsCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(chartData.coin_distribution),
            datasets: [{
                data: Object.values(chartData.coin_distribution),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    charts.userGrowth = new Chart(userGrowthCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(chartData.user_growth),
            datasets: [{
                label: 'New Users',
                data: Object.values(chartData.user_growth),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateTopTraders(traders) {
    const tbody = document.querySelector('#topTradersTable tbody');
    tbody.innerHTML = '';
    
    traders.forEach((trader, index) => {
        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${trader.name}</td>
                <td>${trader.buy_trades_count}</td>
                <td>${trader.sell_trades_count}</td>
                <td>${trader.buy_trades_count + trader.sell_trades_count}</td>
                <td>
                    <span class="badge badge-${trader.telegram_verified ? 'success' : 'secondary'}">
                        ${trader.telegram_verified ? 'Verified' : 'Unverified'}
                    </span>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function refreshDashboard() {
    loadDashboardData(true);
    showNotification('Dashboard refreshed successfully', 'success');
}

function refreshSection(section) {
    // Individual section refresh logic can be implemented here
    loadDashboardData(false);
}

function refreshCharts() {
    loadDashboardData(false);
}

// Utility functions
function formatNumber(num, decimals = 0) {
    return Number(num).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

function formatTime(timestamp) {
    return new Date(timestamp).toLocaleString();
}

function getActivityIcon(type) {
    switch(type) {
        case 'buy_trade': return 'arrow-up';
        case 'sell_trade': return 'arrow-down';
        case 'new_user': return 'user-plus';
        default: return 'info';
    }
}

function getStatusBadge(status) {
    switch(status) {
        case 'completed': return 'success';
        case 'pending': return 'warning';
        case 'verified': return 'success';
        default: return 'secondary';
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    Object.values(charts).forEach(chart => {
        if (chart) chart.destroy();
    });
});
</script>
@endpush