@extends('adminnavlayout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    :root {
        --bg-color: #fff;
        --text-color: #333;
        --card-bg: rgba(255, 255, 255, 0.95);
        --card-shadow: 0 0 20px rgba(0,0,0,0.2);
        --gradient: linear-gradient(45deg, #e6f4ea, #d4e7db, #c9e0d1);
        --success-color: #28a745;
        --danger-color: #dc3545;
    }

    [data-theme="dark"] {
        --bg-color: #1e2a2e;
        --text-color: #e0e0e0;
        --card-bg: rgba(40, 52, 56, 0.95);
        --card-shadow: 0 0 20px rgba(0,0,0,0.5);
        --gradient: linear-gradient(45deg, #2d6a4f, #84a98c, #52796f);
        --success-color: #34c759;
        --danger-color: #ff4d4d;
    }

    body {
        background: var(--gradient);
        background-size: 400% 400%;
        animation: gradientAnimation 15s ease infinite;
        min-height: 100vh;
        color: var(--text-color);
        transition: background 0.5s ease, color 0.5s ease;
    }

    .card {
        background: var(--card-bg);
        border-radius: 15px;
        box-shadow: var(--card-shadow);
        margin: 20px auto;
        padding: 20px;
        animation: slideUp 0.8s ease-out;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: scale(1.02);
    }

    @keyframes gradientAnimation {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .glow-effect {
        animation: glow 1.5s infinite alternate;
    }

    @keyframes glow {
        from { box-shadow: 0 0 10px rgba(0, 123, 255, 0.5); }
        to { box-shadow: 0 0 20px rgba(0, 123, 255, 0.9); }
    }

    .badge {
        min-width: 1.5rem;
        height: 1.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        border-radius: 50%;
        background-color: var(--danger-color);
        color: #fff;
        margin-left: 0.5rem;
        transition: transform 0.2s ease;
    }

    .badge:empty {
        display: none;
    }

    .badge:hover {
        transform: scale(1.1);
    }

    .spinner {
        border: 2px solid #f3f3f3;
        border-top: 2px solid var(--success-color);
        border-radius: 50%;
        width: 16px;
        height: 16px;
        animation: spin 0.8s linear infinite;
        display: none;
        margin-left: 8px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .form-control {
        border-radius: 8px;
        padding: 0.75rem;
        background: var(--card-bg);
        color: var(--text-color);
        border: 1px solid rgba(0,0,0,0.1);
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--success-color);
        box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        outline: none;
    }

    .error {
        border-color: var(--danger-color) !important;
        animation: shake 0.3s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    .theme-toggle {
        transition: transform 0.3s ease;
    }

    .theme-toggle:hover {
        transform: scale(1.1);
    }

    .fade-transition {
        animation: fadeIn 0.5s ease-in-out;
    }

    @media (max-width: 576px) {
        .card {
            padding: 15px;
        }
        .row.g-4 {
            gap: 1rem !important;
        }
    }
</style>

<div class="container">
    <div class="card glow-effect fade-transition">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-center">Admin Dashboard</h3>
            <button id="theme-toggle" class="btn btn-outline-secondary theme-toggle" title="Toggle Theme">
                <i class="fas fa-sun"></i>
            </button>
        </div>
        <p class="text-center mb-4">You are logged in as an admin.</p>

        <!-- Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100 glow-effect">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Registered Users</h5>
                        <p class="card-text fs-4" style="color: var(--success-color);">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 glow-effect">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Referrals</h5>
                        <p class="card-text fs-4" style="color: var(--success-color);">{{ $totalReferrals }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 glow-effect">
                    <div class="card-body text-center">
                        <h5 class="card-title">Referral Rewards Paid</h5>
                        <p class="card-text fs-4" style="color: var(--success-color);">₦{{ number_format($totalReferralRewards, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100 glow-effect">
                    <div class="card-body">
                        <a href="/admin/withdrawals" class="text-decoration-none d-flex align-items-center" style="color: var(--text-color);">
                            Withdrawals
                            <span id="withdrawal-badge" class="badge"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 glow-effect">
                    <div class="card-body">
                        <a href="/admin/kyc" class="text-decoration-none d-flex align-items-center" style="color: var(--text-color);">
                            KYC Verification
                            <span id="kyc-badge" class="badge"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 glow-effect">
                    <div class="card-body">
                        <a href="/admin/trades" class="text-decoration-none d-flex align-items-center" style="color: var(--text-color);">
                            Pending Trades
                            <span id="trades-badge" class="badge"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Crypto Rates Management -->
        <div class="mb-4">
            <h5 class="mb-3">Manage Crypto Rates</h5>
            <div class="card glow-effect">
                <div class="card-body">
                    <form id="rates-form">
                        <div id="rates-container" class="mb-4 text-center">
                            <div class="spinner d-inline-block"></div>
                            <p>Loading rates...</p>
                        </div>
                        <button type="submit" id="update-btn" class="btn btn-success w-100">
                            Update Rates
                            <span id="submit-spinner" class="spinner"></span>
                        </button>
                    </form>
                    <div id="form-feedback" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card glow-effect">
                    <div class="card-body text-center">
                        <h5 class="card-title">Send Notification</h5>
                        <p class="card-text">Send notifications to users or broadcast system-wide messages</p>
                        <a href="/admin/notifications" class="btn btn-primary">
                            <i class="bi bi-bell-plus me-1"></i>Manage Notifications
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card glow-effect">
                    <div class="card-body text-center">
                        <h5 class="card-title">System Analytics</h5>
                        <p class="card-text">View detailed analytics and system performance metrics</p>
                        <a href="/admin/analytics" class="btn btn-info">
                            <i class="bi bi-graph-up me-1"></i>View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card glow-effect">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Notifications</h5>
                        <a href="/admin/notifications" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div id="recentNotifications">
                            <div class="text-center py-3">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading notifications...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Account Management -->
        <div>
            <h5 class="mb-3">Manage Company Account Details</h5>
            <div class="card glow-effect">
                <div class="card-body">
                    <form id="company-account-form" action="{{ route('admin.company-account') }}" method="POST">
                        @csrf
                        <div id="account-container" class="text-center mb-4">
                            <div class="spinner d-inline-block"></div>
                            <p>Loading account details...</p>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="bank_name" class="form-label">Bank Name</label>
                                <input type="text" id="bank_name" name="bank_name" class="form-control" required>
                                <small class="text-danger error-text"></small>
                            </div>
                            <div class="col-md-4">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" id="account_number" name="account_number" maxlength="10" class="form-control" required>
                                <small class="text-danger error-text"></small>
                            </div>
                            <div class="col-md-4">
                                <label for="account_name" class="form-label">Account Name</label>
                                <input type="text" id="account_name" name="account_name" class="form-control" required>
                                <small class="text-danger error-text"></small>
                            </div>
                        </div>
                        <button type="submit" id="update-account-btn" class="btn btn-success mt-4 w-100">
                            Update Account Details
                            <span id="account-submit-spinner" class="spinner"></span>
                        </button>
                        <div id="account-form-feedback" class="mt-3"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Load recent notifications
    loadRecentNotifications();
    
    // Theme toggle
    const toggleButton = document.getElementById('theme-toggle');
    const icon = toggleButton.querySelector('i');
    const body = document.body;

    const setTheme = (theme) => {
        body.setAttribute('data-theme', theme);
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        localStorage.setItem('theme', theme);
        body.classList.add('fade-transition');
        setTimeout(() => body.classList.remove('fade-transition'), 500);
    };

    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);

    toggleButton.addEventListener('click', () => {
        const currentTheme = body.getAttribute('data-theme');
        setTheme(currentTheme === 'dark' ? 'light' : 'dark');
    });

    // Fetch badge counts
    const fetchBadgeCounts = () => {
        fetch('/admin/pending-counts', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch counts');
            return response.json();
        })
        .then(data => {
            document.getElementById('withdrawal-badge').textContent = data.pending_withdrawals || '';
            document.getElementById('kyc-badge').textContent = data.pending_kyc || '';
            document.getElementById('trades-badge').textContent = data.pending_trades || '';
        })
        .catch(error => console.error('Error fetching badge counts:', error));
    };
    fetchBadgeCounts();
    setInterval(fetchBadgeCounts, 30000);

    // Fetch crypto rates
    fetch('/admin/rate', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to fetch rates');
        return response.json();
    })
    .then(data => {
        const ratesContainer = document.getElementById('rates-container');
        ratesContainer.innerHTML = '';
        if (!data || data.length === 0) {
            ratesContainer.innerHTML = '<p>No rates available.</p>';
            return;
        }
        data.forEach(rate => {
            ratesContainer.innerHTML += `
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">${rate.coin}</label>
                        <input type="hidden" name="coin" value="${rate.coin}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Buy Rate (₦/USD)</label>
                        <input type="number" step="0.01" class="form-control" name="buy_rate" value="${parseFloat(rate.buy_rate).toFixed(2)}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sell Rate (₦/USD)</label>
                        <input type="number" step="0.01" class="form-control" name="sell_rate" value="${parseFloat(rate.sell_rate).toFixed(2)}" required>
                    </div>
                </div>`;
        });
    })
    .catch(error => {
        console.error('Error fetching rates:', error);
        document.getElementById('rates-container').innerHTML = `<p style="color: var(--danger-color);">Error loading rates: ${error.message}</p>`;
    });

    // Handle rates form submission
    document.getElementById('rates-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const feedback = document.getElementById('form-feedback');
        const submitBtn = document.getElementById('update-btn');
        const spinner = document.getElementById('submit-spinner');
        feedback.innerHTML = '';
        submitBtn.disabled = true;
        spinner.style.display = 'inline-block';
        submitBtn.innerHTML = 'Updating... <span id="submit-spinner" class="spinner d-inline-block"></span>';

        const rates = [];
        const rows = document.querySelectorAll('#rates-container .row');
        rows.forEach(row => {
            const coin = row.querySelector('input[name="coin"]').value;
            const buy_rate = row.querySelector('input[name="buy_rate"]').value;
            const sell_rate = row.querySelector('input[name="sell_rate"]').value;
            if (coin && buy_rate && sell_rate) {
                rates.push({
                    coin,
                    buy_rate: parseFloat(buy_rate) || 0,
                    sell_rate: parseFloat(sell_rate) || 0
                });
            }
        });

        if (rates.length === 0) {
            feedback.innerHTML = `<div style="color: var(--danger-color);">No valid rates provided.</div>`;
            submitBtn.disabled = false;
            spinner.style.display = 'none';
            submitBtn.innerHTML = 'Update Rates <span id="submit-spinner" class="spinner"></span>';
            return;
        }

        fetch('/admin/rate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ rates })
        })
        .then(response => {
            if (!response.ok) throw new Error('Request failed');
            return response.json();
        })
        .then(data => {
            feedback.innerHTML = `<div style="color: var(--success-color);">Rates updated successfully!</div>`;
            setTimeout(() => feedback.innerHTML = '', 3000);
        })
        .catch(error => {
            console.error('Error updating rates:', error);
            feedback.innerHTML = `<div style="color: var(--danger-color);">Error updating rates: ${error.message}</div>`;
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.style.display = 'none';
            submitBtn.innerHTML = 'Update Rates <span id="submit-spinner" class="spinner"></span>';
        });
    });

    // Fetch company account details
    fetch('/admin/company-account', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to fetch account details');
        return response.json();
    })
    .then(data => {
        const accountContainer = document.getElementById('account-container');
        accountContainer.innerHTML = '';
        if (!data) {
            accountContainer.innerHTML = '<p>No account details available. Please add details below.</p>';
        } else {
            document.getElementById('bank_name').value = data.bank_name || '';
            document.getElementById('account_number').value = data.account_number || '';
            document.getElementById('account_name').value = data.account_name || '';
        }
    })
    .catch(error => {
        console.error('Error fetching account details:', error);
        document.getElementById('account-container').innerHTML = `<p style="color: var(--danger-color);">Error loading account details: ${error.message}</p>`;
    });

    // Handle company account form submission
    document.getElementById('company-account-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const feedback = document.getElementById('account-form-feedback');
        const submitBtn = document.getElementById('update-account-btn');
        const spinner = document.getElementById('account-submit-spinner');
        feedback.innerHTML = '';
        submitBtn.disabled = true;
        spinner.style.display = 'inline-block';
        submitBtn.innerHTML = 'Updating... <span id="account-submit-spinner" class="spinner d-inline-block"></span>';

        const formData = new FormData(document.getElementById('company-account-form'));

        fetch('/admin/company-account', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Request failed');
            return response.json();
        })
        .then(data => {
            feedback.innerHTML = `<div style="color: var(--success-color);">Account details updated successfully!</div>`;
            setTimeout(() => feedback.innerHTML = '', 3000);
        })
        .catch(error => {
            console.error('Error updating account details:', error);
            feedback.innerHTML = `<div style="color: var(--danger-color);">Error updating account details: ${error.message}</div>`;
            document.querySelectorAll('.form-control').forEach(input => input.classList.add('error'));
            setTimeout(() => document.querySelectorAll('.form-control').forEach(input => input.classList.remove('error')), 1000);
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.style.display = 'none';
            submitBtn.innerHTML = 'Update Account Details <span id="account-submit-spinner" class="spinner"></span>';
        });
    });

    // Clear error borders on input
    ['bank_name', 'account_number', 'account_name'].forEach(id => {
        document.getElementById(id).addEventListener('input', function () {
            this.classList.remove('error');
            this.nextElementSibling.textContent = '';
        });
    });
    
    // Load recent notifications function
    function loadRecentNotifications() {
        fetch('/admin/notifications?per_page=5')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentNotifications');
            if (data.notifications && data.notifications.data && data.notifications.data.length > 0) {
                const html = data.notifications.data.map(notification => `
                    <div class="d-flex align-items-start mb-3 p-2 border rounded">
                        <div class="me-3">
                            <i class="bi ${notification.icon} fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${notification.title}</h6>
                            <p class="mb-1 text-muted small">${notification.message}</p>
                            <small class="text-muted">
                                ${notification.created_at} 
                                ${notification.is_broadcast ? '<span class="badge bg-primary ms-1">Broadcast</span>' : ''}
                                ${!notification.is_read ? '<span class="badge bg-warning ms-1">Unread</span>' : ''}
                            </small>
                        </div>
                    </div>
                `).join('');
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted text-center py-3">No recent notifications</p>';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('recentNotifications').innerHTML = '<p class="text-danger text-center py-3">Error loading notifications</p>';
        });
    }
});
</script>
@endsection