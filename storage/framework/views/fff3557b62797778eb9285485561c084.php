<?php $__env->startSection('content'); ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        /* Enhanced Color Palette */
        --primary-green: #00c851;
        --secondary-green: #007e33;
        --accent-green: #a8e6cf;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --info: #17a2b8;
        
        /* Dark Mode Variables */
        --dark-bg: #0f1419;
        --dark-bg-secondary: #1a1f2e;
        --dark-card-bg: #1e2329;
        --dark-text: #ffffff;
        --dark-text-muted: #8c9198;
        --dark-border: #2b3139;
        
        /* Light Mode Variables */
        --light-bg: #f8fafc;
        --light-bg-secondary: #ffffff;
        --light-card-bg: #ffffff;
        --light-text: #1a202c;
        --light-text-muted: #718096;
        --light-border: #e2e8f0;
        
        /* Gradients */
        --gradient-primary: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        --gradient-card: linear-gradient(145deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
        --gradient-glass: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
        
        /* Shadows */
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.07), 0 1px 3px rgba(0,0,0,0.06);
        --shadow-lg: 0 10px 25px rgba(0,0,0,0.15), 0 4px 10px rgba(0,0,0,0.08);
        --shadow-xl: 0 20px 25px rgba(0,0,0,0.15), 0 10px 10px rgba(0,0,0,0.04);
        
        /* Transitions */
        --transition-fast: all 0.15s ease;
        --transition-normal: all 0.3s ease;
        --transition-slow: all 0.5s ease;
    }

    /* Base Styles */
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: var(--dark-bg);
        color: var(--dark-text);
        line-height: 1.6;
        font-weight: 400;
        overflow-x: hidden;
        transition: var(--transition-normal);
    }

    body.light-mode {
        background: var(--light-bg);
        color: var(--light-text);
    }

    /* Container and Layout */
    .dashboard-container {
        padding: 1rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    @media (min-width: 768px) {
        .dashboard-container {
            padding: 2rem;
        }
    }

    /* Card Components */
    .dashboard-card {
        background: var(--dark-card-bg);
        border: 1px solid var(--dark-border);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        transition: var(--transition-normal);
        backdrop-filter: blur(10px);
        position: relative;
        overflow: hidden;
    }

    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--gradient-primary);
        opacity: 0;
        transition: var(--transition-normal);
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .dashboard-card:hover::before {
        opacity: 1;
    }

    body.light-mode .dashboard-card {
        background: var(--light-card-bg);
        border-color: var(--light-border);
    }

    /* Glass Effect Cards */
    .glass-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    }

    body.light-mode .glass-card {
        background: rgba(255, 255, 255, 0.8);
        border-color: rgba(255, 255, 255, 0.3);
    }

    /* Welcome Section */
    .welcome-section {
        background: var(--gradient-primary);
        color: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .welcome-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Balance Display */
    .balance-display {
        font-size: clamp(1.8rem, 4vw, 3rem);
        font-weight: 700;
        background: linear-gradient(45deg, #fff, #e8f5e8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    .stat-card {
        background: var(--dark-card-bg);
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        transition: var(--transition-normal);
        border: 1px solid var(--dark-border);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: left 0.5s;
    }

    .stat-card:hover::before {
        left: 100%;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    body.light-mode .stat-card {
        background: var(--light-card-bg);
        border-color: var(--light-border);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        background: var(--gradient-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
        color: white;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-green);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--dark-text-muted);
        font-size: 0.875rem;
        font-weight: 500;
    }

    body.light-mode .stat-label {
        color: var(--light-text-muted);
    }

    /* Action Buttons */
    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 768px) {
        .action-buttons {
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .action-buttons {
            grid-template-columns: 1fr;
        }
    }

    .action-btn {
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: var(--transition-normal);
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.3s;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        color: white;
        text-decoration: none;
    }

    .action-btn:hover::before {
        left: 100%;
    }

    .action-btn.outline {
        background: transparent;
        color: var(--primary-green);
        border: 2px solid var(--primary-green);
    }

    .action-btn.outline:hover {
        background: var(--primary-green);
        color: white;
    }

    /* Tables */
    .table-container {
        background: var(--dark-card-bg);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
    }

    body.light-mode .table-container {
        background: var(--light-card-bg);
    }

    .table-header {
        background: var(--dark-bg-secondary);
        padding: 1.5rem;
        border-bottom: 1px solid var(--dark-border);
    }

    body.light-mode .table-header {
        background: var(--light-bg);
        border-color: var(--light-border);
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--dark-border);
    }

    body.light-mode .data-table th,
    body.light-mode .data-table td {
        border-color: var(--light-border);
    }

    .data-table th {
        font-weight: 600;
        color: var(--dark-text-muted);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    body.light-mode .data-table th {
        color: var(--light-text-muted);
    }

    .data-table tr:hover {
        background: rgba(0, 200, 81, 0.05);
    }

    /* Responsive Table */
    @media (max-width: 768px) {
        .table-container {
            border-radius: 12px;
        }
        
        .data-table,
        .data-table thead,
        .data-table tbody,
        .data-table th,
        .data-table td,
        .data-table tr {
            display: block;
        }

        .data-table thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }

        .data-table tr {
            background: var(--dark-card-bg);
            border-radius: 12px;
            margin-bottom: 1rem;
            padding: 1rem;
            box-shadow: var(--shadow-sm);
        }

        body.light-mode .data-table tr {
            background: var(--light-card-bg);
        }

        .data-table td {
            border: none;
            position: relative;
            padding: 0.5rem 0 0.5rem 30%;
            border-bottom: 1px solid var(--dark-border);
        }

        body.light-mode .data-table td {
            border-color: var(--light-border);
        }

        .data-table td:before {
            content: attr(data-label) ": ";
            position: absolute;
            left: 0;
            width: 25%;
            padding-right: 10px;
            white-space: nowrap;
            font-weight: 600;
            color: var(--dark-text-muted);
        }

        body.light-mode .data-table td:before {
            color: var(--light-text-muted);
        }
    }

    /* Status Badges */
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.success {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success);
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .status-badge.warning {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning);
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .status-badge.danger {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    /* Theme Toggle */
    .theme-toggle {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1000;
        background: var(--gradient-primary);
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        box-shadow: var(--shadow-lg);
        transition: var(--transition-normal);
    }

    .theme-toggle:hover {
        transform: scale(1.1);
        box-shadow: var(--shadow-xl);
    }

    /* Loading States */
    .loading-shimmer {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    /* Notifications */
    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: var(--danger);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    /* Charts Container */
    .chart-container {
        height: 300px;
        margin-bottom: 2rem;
    }

    @media (max-width: 768px) {
        .chart-container {
            height: 250px;
        }
    }

    /* Quick Actions Floating Button */
    .quick-actions {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }

    .quick-action-btn {
        background: var(--gradient-primary);
        border: none;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        color: white;
        font-size: 1.5rem;
        box-shadow: var(--shadow-lg);
        transition: var(--transition-normal);
        margin-bottom: 10px;
        display: block;
    }

    .quick-action-btn:hover {
        transform: scale(1.1);
        box-shadow: var(--shadow-xl);
    }

    /* Animations */
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .animate-slide-up {
        animation: slideUp 0.6s ease-out;
    }

    .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--dark-bg-secondary);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-green);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-green);
    }

    body.light-mode ::-webkit-scrollbar-track {
        background: var(--light-bg);
    }

    /* Utility Classes */
    .text-primary { color: var(--primary-green) !important; }
    .text-success { color: var(--success) !important; }
    .text-warning { color: var(--warning) !important; }
    .text-danger { color: var(--danger) !important; }
    .text-muted { color: var(--dark-text-muted) !important; }
    body.light-mode .text-muted { color: var(--light-text-muted) !important; }

    .bg-primary { background: var(--gradient-primary) !important; }
    .border-primary { border-color: var(--primary-green) !important; }

    /* Section Spacing */
    .section-spacing {
        margin-bottom: 3rem;
    }

    @media (max-width: 768px) {
        .section-spacing {
            margin-bottom: 2rem;
        }
    }
</style>

<!-- Theme Toggle Button -->
<button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
    <i class="fas fa-sun" id="themeIcon"></i>
</button>

<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section animate-fade-in">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">Welcome back, <?php echo e(Auth::user()->name); ?>! 👋</h1>
                <p class="mb-0 opacity-75">Here's what's happening with your account today.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="balance-display">₦<?php echo e(number_format(Auth::user()->balance, 2)); ?></div>
                <small class="opacity-75">Available Balance</small>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid animate-slide-up section-spacing">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-value" id="totalBalance">₦<?php echo e(number_format(Auth::user()->balance, 2)); ?></div>
            <div class="stat-label">Total Balance</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value" id="totalTransactions">Loading...</div>
            <div class="stat-label">Total Transactions</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-value" id="activeTrades">Loading...</div>
            <div class="stat-label">Active Trades</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value" id="referrals">Loading...</div>
            <div class="stat-label">Referrals Made</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="action-buttons animate-slide-up section-spacing">
        <a href="<?php echo e(Auth::user()->kyc_verified ? route('buy') : route('kyc.form')); ?>" class="action-btn">
            <i class="fas fa-shopping-cart"></i>
            Buy Crypto
        </a>
        <a href="<?php echo e(route('sell.form')); ?>" class="action-btn outline">
            <i class="fas fa-coins"></i>
            Sell Crypto
        </a>
        <a href="<?php echo e(route('withdraw')); ?>" class="action-btn">
            <i class="fas fa-money-bill-wave"></i>
            Withdraw
        </a>
        <a href="<?php echo e(route('deposits.index')); ?>" class="action-btn outline">
            <i class="fas fa-plus-circle"></i>
            Deposit
        </a>
        <a href="<?php echo e(route('transactions.history')); ?>" class="action-btn">
            <i class="fas fa-history"></i>
            Transactions
        </a>
        <a href="<?php echo e(route('referrals')); ?>" class="action-btn outline">
            <i class="fas fa-share-alt"></i>
            Refer Friends
        </a>
    </div>

    <!-- Portfolio Overview Chart -->
    <div class="dashboard-card animate-slide-up section-spacing">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Portfolio Overview</h3>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary active" onclick="updateChart('7d')">7D</button>
                <button type="button" class="btn btn-outline-primary" onclick="updateChart('30d')">30D</button>
                <button type="button" class="btn btn-outline-primary" onclick="updateChart('90d')">90D</button>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="portfolioChart"></canvas>
        </div>
    </div>

    <!-- Current Rates -->
    <div class="dashboard-card animate-slide-up section-spacing">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Current Exchange Rates</h3>
            <button class="btn btn-outline-primary btn-sm" onclick="refreshRates()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cryptocurrency</th>
                        <th>Buy Rate (₦/USD)</th>
                        <th>Sell Rate (₦/USD)</th>
                        <th>Change (24h)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ratesTableBody">
                    <tr>
                        <td data-label="Cryptocurrency">
                            <div class="d-flex align-items-center gap-2">
                                <img src="https://cryptoicons.org/api/icon/btc/32" alt="BTC" class="rounded-circle">
                                <div>
                                    <strong>Bitcoin</strong>
                                    <br><small class="text-muted">BTC</small>
                                </div>
                            </div>
                        </td>
                        <td data-label="Buy Rate">₦1,600</td>
                        <td data-label="Sell Rate">₦1,580</td>
                        <td data-label="Change">
                            <span class="status-badge success">+2.5%</span>
                        </td>
                        <td data-label="Action">
                            <button class="btn btn-sm btn-primary">Trade</button>
                        </td>
                    </tr>
                    <tr>
                        <td data-label="Cryptocurrency">
                            <div class="d-flex align-items-center gap-2">
                                <img src="https://cryptoicons.org/api/icon/eth/32" alt="ETH" class="rounded-circle">
                                <div>
                                    <strong>Ethereum</strong>
                                    <br><small class="text-muted">ETH</small>
                                </div>
                            </div>
                        </td>
                        <td data-label="Buy Rate">₦1,500</td>
                        <td data-label="Sell Rate">₦1,480</td>
                        <td data-label="Change">
                            <span class="status-badge danger">-1.2%</span>
                        </td>
                        <td data-label="Action">
                            <button class="btn btn-sm btn-primary">Trade</button>
                        </td>
                    </tr>
                    <tr>
                        <td data-label="Cryptocurrency">
                            <div class="d-flex align-items-center gap-2">
                                <img src="https://cryptoicons.org/api/icon/usdt/32" alt="USDT" class="rounded-circle">
                                <div>
                                    <strong>Tether</strong>
                                    <br><small class="text-muted">USDT</small>
                                </div>
                            </div>
                        </td>
                        <td data-label="Buy Rate">₦1,400</td>
                        <td data-label="Sell Rate">₦1,390</td>
                        <td data-label="Change">
                            <span class="status-badge success">+0.1%</span>
                        </td>
                        <td data-label="Action">
                            <button class="btn btn-sm btn-primary">Trade</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="dashboard-card animate-slide-up section-spacing">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Recent Transactions</h3>
            <a href="<?php echo e(route('transactions.history')); ?>" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Asset</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="transactionsTableBody">
                    <!-- Dynamic content will be loaded here -->
                    <tr>
                        <td data-label="Date" colspan="5" class="text-center text-muted">
                            <div class="loading-shimmer" style="height: 20px; border-radius: 4px;"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KYC Status Alert -->
    <?php if(!Auth::user()->kyc_verified): ?>
    <div class="dashboard-card animate-slide-up section-spacing" style="border: 1px solid var(--warning); background: rgba(255, 193, 7, 0.1);">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: var(--warning);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1 text-warning">Complete Your KYC Verification</h5>
                <p class="mb-0 text-muted">Verify your identity to access all trading features and increase your limits.</p>
            </div>
            <a href="<?php echo e(route('kyc.form')); ?>" class="btn btn-warning">
                <i class="fas fa-shield-alt"></i>
                Verify Now
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Actions Floating Buttons -->
<div class="quick-actions d-md-none">
    <button class="quick-action-btn" onclick="location.href='<?php echo e(route('buy')); ?>'" title="Buy Crypto">
        <i class="fas fa-plus"></i>
    </button>
    <button class="quick-action-btn" onclick="location.href='<?php echo e(route('sell.form')); ?>'" title="Sell Crypto">
        <i class="fas fa-minus"></i>
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Theme Management
    let currentTheme = localStorage.getItem('dashboardTheme') || 'dark';
    
    function toggleTheme() {
        currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme();
        localStorage.setItem('dashboardTheme', currentTheme);
    }
    
    function applyTheme() {
        const body = document.body;
        const themeIcon = document.getElementById('themeIcon');
        
        if (currentTheme === 'light') {
            body.classList.add('light-mode');
            themeIcon.className = 'fas fa-moon';
        } else {
            body.classList.remove('light-mode');
            themeIcon.className = 'fas fa-sun';
        }
    }
    
    // Initialize theme on page load
    document.addEventListener('DOMContentLoaded', function() {
        applyTheme();
        initializeCharts();
        loadDashboardData();
        
        // Add stagger animation to cards
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });
    
    // Chart Initialization
    let portfolioChart;
    
    function initializeCharts() {
        const ctx = document.getElementById('portfolioChart').getContext('2d');
        
        portfolioChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Portfolio Value (₦)',
                    data: [50000, 55000, 52000, 58000, 65000, <?php echo e(Auth::user()->balance); ?>],
                    borderColor: '#00c851',
                    backgroundColor: 'rgba(0, 200, 81, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#00c851',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            color: currentTheme === 'dark' ? '#2b3139' : '#e2e8f0'
                        },
                        ticks: {
                            color: currentTheme === 'dark' ? '#8c9198' : '#718096'
                        }
                    },
                    y: {
                        grid: {
                            color: currentTheme === 'dark' ? '#2b3139' : '#e2e8f0'
                        },
                        ticks: {
                            color: currentTheme === 'dark' ? '#8c9198' : '#718096',
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    point: {
                        hoverBackgroundColor: '#00c851'
                    }
                }
            }
        });
    }
    
    function updateChart(period) {
        // Update active button
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Here you would typically fetch new data based on the period
        // For now, we'll just update with sample data
        const sampleData = {
            '7d': [48000, 49000, 51000, 52000, 50000, 53000, <?php echo e(Auth::user()->balance); ?>],
            '30d': [45000, 48000, 52000, 55000, 58000, 60000, <?php echo e(Auth::user()->balance); ?>],
            '90d': [40000, 45000, 50000, 55000, 58000, 62000, <?php echo e(Auth::user()->balance); ?>]
        };
        
        portfolioChart.data.datasets[0].data = sampleData[period] || sampleData['7d'];
        portfolioChart.update('active');
    }
    
    // Load Dashboard Data
    async function loadDashboardData() {
        try {
            // Load stats
            const statsResponse = await fetch('/api/dashboard/stats');
            const stats = await statsResponse.json();
            
            if (stats.success) {
                document.getElementById('totalTransactions').textContent = stats.data.totalTransactions || '0';
                document.getElementById('activeTrades').textContent = stats.data.activeTrades || '0';
                document.getElementById('referrals').textContent = stats.data.referrals || '0';
            }
            
            // Load recent transactions
            const transactionsResponse = await fetch('/api/dashboard/recent-transactions');
            const transactions = await transactionsResponse.json();
            
            if (transactions.success) {
                updateTransactionsTable(transactions.data);
            }
            
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            // Show fallback data
            document.getElementById('totalTransactions').textContent = '0';
            document.getElementById('activeTrades').textContent = '0';
            document.getElementById('referrals').textContent = '0';
        }
    }
    
    function updateTransactionsTable(transactions) {
        const tbody = document.getElementById('transactionsTableBody');
        
        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td data-label="Status" colspan="5" class="text-center text-muted">
                        <i class="fas fa-inbox mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                        <br>No transactions found
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = transactions.slice(0, 5).map(transaction => `
            <tr>
                <td data-label="Date">${new Date(transaction.created_at).toLocaleDateString()}</td>
                <td data-label="Type">
                    <span class="status-badge ${transaction.type === 'buy' ? 'success' : transaction.type === 'sell' ? 'warning' : 'info'}">
                        ${transaction.type.toUpperCase()}
                    </span>
                </td>
                <td data-label="Asset">${transaction.coin || transaction.bank_account || 'N/A'}</td>
                <td data-label="Amount">₦${parseFloat(transaction.amount_ngn || 0).toLocaleString()}</td>
                <td data-label="Status">
                    <span class="status-badge ${transaction.status === 'completed' ? 'success' : transaction.status === 'pending' ? 'warning' : 'danger'}">
                        ${transaction.status}
                    </span>
                </td>
            </tr>
        `).join('');
    }
    
    function refreshRates() {
        const refreshBtn = event.target.closest('button');
        const icon = refreshBtn.querySelector('i');
        
        icon.classList.add('fa-spin');
        
        // Simulate API call
        setTimeout(() => {
            icon.classList.remove('fa-spin');
            // Here you would typically fetch new rates from your API
            console.log('Rates refreshed');
        }, 1000);
    }
    
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading states to buttons
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.classList.contains('loading')) {
                this.classList.add('loading');
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                
                // Reset after navigation (this is just for UX, actual navigation will occur)
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.innerHTML = originalText;
                }, 2000);
            }
        });
    });
    
    // Service Worker and PWA Installation
    let deferredPrompt;
    let isInstalled = false;
    
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('Service Worker registered successfully');
                
                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            showUpdateNotification();
                        }
                    });
                });
            })
            .catch(error => console.log('Service Worker registration failed:', error));
    }
    
    // PWA Installation Prompt
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showInstallPrompt();
    });
    
    // Check if app is already installed
    window.addEventListener('appinstalled', () => {
        console.log('PWA installed successfully');
        isInstalled = true;
        hideInstallPrompt();
    });
    
    function showInstallPrompt() {
        if (isInstalled) return;
        
        const installBanner = document.createElement('div');
        installBanner.id = 'installBanner';
        installBanner.innerHTML = `
            <div class="dashboard-card" style="position: fixed; top: 80px; right: 20px; z-index: 1001; max-width: 300px; background: var(--gradient-primary); color: white; border: none;">
                <div class="d-flex align-items-center gap-3">
                    <div style="background: rgba(255,255,255,0.2); padding: 8px; border-radius: 8px;">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Install KayXchange</h6>
                        <small style="opacity: 0.9;">Get the app for quick access</small>
                    </div>
                    <button class="btn btn-sm btn-light" onclick="installPWA()">Install</button>
                    <button class="btn btn-sm btn-link text-white p-1" onclick="hideInstallPrompt()">×</button>
                </div>
            </div>
        `;
        
        // Only show if not already present
        if (!document.getElementById('installBanner')) {
            document.body.appendChild(installBanner);
        }
    }
    
    function installPWA() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                } else {
                    console.log('User dismissed the install prompt');
                }
                deferredPrompt = null;
                hideInstallPrompt();
            });
        }
    }
    
    function hideInstallPrompt() {
        const banner = document.getElementById('installBanner');
        if (banner) {
            banner.remove();
        }
    }
    
    function showUpdateNotification() {
        const updateBanner = document.createElement('div');
        updateBanner.innerHTML = `
            <div class="dashboard-card" style="position: fixed; bottom: 20px; left: 20px; z-index: 1001; max-width: 300px; background: var(--success); color: white; border: none;">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-sync-alt"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Update Available</h6>
                        <small style="opacity: 0.9;">New features are ready</small>
                    </div>
                    <button class="btn btn-sm btn-light" onclick="updateApp()">Update</button>
                    <button class="btn btn-sm btn-link text-white p-1" onclick="this.parentElement.parentElement.remove()">×</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(updateBanner);
        
        // Auto hide after 10 seconds
        setTimeout(() => {
            const banner = updateBanner.querySelector('.dashboard-card');
            if (banner) {
                banner.remove();
            }
        }, 10000);
    }
    
    function updateApp() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistration().then(registration => {
                if (registration && registration.waiting) {
                    registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                    window.location.reload();
                }
            });
        }
    }
    
    // Network status monitoring
    function updateOnlineStatus() {
        const statusIndicator = document.createElement('div');
        statusIndicator.id = 'networkStatus';
        statusIndicator.style.cssText = `
            position: fixed;
            top: 50px;
            right: 20px;
            z-index: 1002;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        `;
        
        if (navigator.onLine) {
            statusIndicator.innerHTML = '<i class="fas fa-wifi"></i> Online';
            statusIndicator.style.background = 'var(--success)';
            statusIndicator.style.color = 'white';
            
            // Hide online status after 3 seconds
            setTimeout(() => {
                const status = document.getElementById('networkStatus');
                if (status) status.remove();
            }, 3000);
        } else {
            statusIndicator.innerHTML = '<i class="fas fa-wifi-off"></i> Offline';
            statusIndicator.style.background = 'var(--danger)';
            statusIndicator.style.color = 'white';
        }
        
        // Remove existing status
        const existing = document.getElementById('networkStatus');
        if (existing) existing.remove();
        
        document.body.appendChild(statusIndicator);
    }
    
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    
    // Cache management for offline support
    async function getCacheInfo() {
        if ('serviceWorker' in navigator && 'caches' in window) {
            try {
                const cacheNames = await caches.keys();
                const cacheInfo = {
                    count: cacheNames.length,
                    names: cacheNames
                };
                console.log('Cache info:', cacheInfo);
                return cacheInfo;
            } catch (error) {
                console.error('Error getting cache info:', error);
            }
        }
    }
    
    // Initialize cache info on load
    getCacheInfo();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\dashboard-enhanced.blade.php ENDPATH**/ ?>