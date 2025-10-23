@extends('adminnavlayout')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <i class="fas fa-coins me-2"></i>
                                Cryptocurrency Rate Management
                            </h3>
                            <small class="opacity-75">Update buy and sell rates for all supported cryptocurrencies</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light btn-sm" onclick="refreshRates()">
                                <i class="fas fa-sync-alt me-1"></i>
                                Refresh
                            </button>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCoinModal">
                                <i class="fas fa-plus me-1"></i>
                                Add Coin
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Crypto Prices Marquee -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                Live Market Prices (CoinGecko)
                            </h6>
                        </div>
                        <div>
                            <small class="opacity-75">
                                <i class="fas fa-sync-alt me-1" id="marquee-refresh-icon"></i>
                                Last updated: <span id="last-updated">Loading...</span>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="crypto-marquee-container">
                        <div class="crypto-marquee" id="crypto-marquee">
                            <div class="marquee-content">
                                <span class="loading-text">
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                    Loading live cryptocurrency prices...
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Coins</h5>
                            <h2 class="mb-0">{{ $rates->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-coins fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Rates</h5>
                            <h2 class="mb-0">{{ $rates->where('buy_rate', '>', 0)->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Last Updated</h5>
                            <h6 class="mb-0">{{ $rates->max('updated_at') ? $rates->max('updated_at')->diffForHumans() : 'Never' }}</h6>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Avg. Spread</h5>
                            <h6 class="mb-0">
                                @php
                                    $avgSpread = $rates->where('buy_rate', '>', 0)->where('sell_rate', '>', 0)->avg(function($rate) {
                                        return (($rate->buy_rate - $rate->sell_rate) / $rate->sell_rate) * 100;
                                    });
                                @endphp
                                {{ number_format($avgSpread ?? 0, 2) }}%
                            </h6>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-percentage fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Update Form -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Bulk Rate Update
                    </h5>
                </div>
                <div class="card-body">
                    <form id="bulkUpdateForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Apply to All:</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" step="0.01" class="form-control" id="usdToNairaRate" placeholder="USD to Naira rate">
                                        <button type="button" class="btn btn-outline-primary" onclick="applyUsdRate()">
                                            Apply
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Set base USD to Naira rate for all coins</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Percentage Adjustment:</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" class="form-control" id="percentageAdjustment" placeholder="±5.0">
                                        <span class="input-group-text">%</span>
                                        <button type="button" class="btn btn-outline-success" onclick="applyPercentageAdjustment()">
                                            Adjust
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Increase/decrease all rates by percentage</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Quick Actions:</label>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-warning" onclick="resetAllRates()">
                                            <i class="fas fa-undo me-1"></i>
                                            Reset
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="copyFromPrevious()">
                                            <i class="fas fa-copy me-1"></i>
                                            Copy Previous
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Rates Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        Individual Rate Management
                    </h5>
                </div>
                <div class="card-body p-0">
                    <form action="{{ route('admin.crypto-rates.bulk-update') }}" method="POST" id="ratesForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="ratesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th width="15%">Cryptocurrency</th>
                                        <th width="15%">Current USD Price</th>
                                        <th width="20%">Buy Rate (₦)</th>
                                        <th width="20%">Sell Rate (₦)</th>
                                        <th width="10%">Spread</th>
                                        <th width="10%">Last Updated</th>
                                        <th width="5%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rates as $rate)
                                    <tr data-coin="{{ $rate->coin }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input rate-checkbox" name="selected_rates[]" value="{{ $rate->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://cryptoicons.org/api/icon/{{ strtolower($rate->coin) }}/32" 
                                                     alt="{{ $rate->coin }}" 
                                                     class="rounded-circle me-2" 
                                                     width="24" height="24"
                                                     onerror="this.src='https://via.placeholder.com/24x24/007bff/ffffff?text={{ substr($rate->coin, 0, 1) }}'">
                                                <div>
                                                    <strong>{{ strtoupper($rate->coin) }}</strong>
                                                    <br><small class="text-muted">{{ $rate->coin }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark" id="usd-price-{{ $rate->id }}">
                                                Loading...
                                            </span>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₦</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control buy-rate-input" 
                                                       name="rates[{{ $rate->id }}][buy_rate]" 
                                                       value="{{ $rate->buy_rate }}"
                                                       onchange="calculateSpread({{ $rate->id }})">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₦</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control sell-rate-input" 
                                                       name="rates[{{ $rate->id }}][sell_rate]" 
                                                       value="{{ $rate->sell_rate }}"
                                                       onchange="calculateSpread({{ $rate->id }})">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" id="spread-{{ $rate->id }}">
                                                @if($rate->buy_rate > 0 && $rate->sell_rate > 0)
                                                    {{ number_format((($rate->buy_rate - $rate->sell_rate) / $rate->sell_rate) * 100, 2) }}%
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $rate->updated_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        onclick="quickUpdate({{ $rate->id }})" 
                                                        title="Quick Update">
                                                    <i class="fas fa-bolt"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        onclick="resetRate({{ $rate->id }})" 
                                                        title="Reset Rate">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No cryptocurrency rates found. Add some coins to get started.</p>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCoinModal">
                                                <i class="fas fa-plus me-1"></i>
                                                Add First Coin
                                            </button>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($rates->count() > 0)
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted">
                                        <span id="selectedCount">0</span> of {{ $rates->count() }} selected
                                    </span>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="selectAll()">
                                        Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="selectNone()">
                                        Select None
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-1"></i>
                                        Update Selected Rates
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Coin Modal -->
<div class="modal fade" id="addCoinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Cryptocurrency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.crypto-rates.add') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="coin" class="form-label">Coin Symbol</label>
                        <input type="text" class="form-control" id="coin" name="coin" placeholder="BTC, ETH, etc." required>
                        <div class="form-text">Enter the coin symbol (e.g., BTC for Bitcoin)</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="buy_rate" class="form-label">Initial Buy Rate (₦)</label>
                                <input type="number" step="0.01" class="form-control" id="buy_rate" name="buy_rate" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sell_rate" class="form-label">Initial Sell Rate (₦)</label>
                                <input type="number" step="0.01" class="form-control" id="sell_rate" name="sell_rate" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Cryptocurrency</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-size: 0.75rem;
}

.card {
    border-radius: 12px;
    border: none;
}

.btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.375rem;
}

.input-group-sm > .form-control, .input-group-sm > .input-group-text {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

#ratesTable input[type="number"] {
    min-width: 100px;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}
</style>

<script>
// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    loadCurrentPrices();
    initializeSpreadCalculations();
});

// Select all/none functionality
function selectAll() {
    document.querySelectorAll('.rate-checkbox').forEach(cb => cb.checked = true);
    document.getElementById('selectAll').checked = true;
    updateSelectedCount();
}

function selectNone() {
    document.querySelectorAll('.rate-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateSelectedCount();
}

// Update selected count
function updateSelectedCount() {
    const count = document.querySelectorAll('.rate-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

// Master checkbox functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checked = this.checked;
    document.querySelectorAll('.rate-checkbox').forEach(cb => cb.checked = checked);
    updateSelectedCount();
});

// Individual checkbox listeners
document.querySelectorAll('.rate-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

// Calculate spread for individual rates
function calculateSpread(rateId) {
    const buyRate = parseFloat(document.querySelector(`input[name="rates[${rateId}][buy_rate]"]`).value) || 0;
    const sellRate = parseFloat(document.querySelector(`input[name="rates[${rateId}][sell_rate]"]`).value) || 0;
    const spreadElement = document.getElementById(`spread-${rateId}`);
    
    if (buyRate > 0 && sellRate > 0) {
        const spread = ((buyRate - sellRate) / sellRate) * 100;
        spreadElement.textContent = spread.toFixed(2) + '%';
        spreadElement.className = `badge ${spread > 5 ? 'bg-success' : spread > 2 ? 'bg-warning' : 'bg-danger'}`;
    } else {
        spreadElement.textContent = 'N/A';
        spreadElement.className = 'badge bg-secondary';
    }
}

// Initialize spread calculations
function initializeSpreadCalculations() {
    document.querySelectorAll('input[name^="rates["][name$="][buy_rate]"], input[name^="rates["][name$="][sell_rate]"]').forEach(input => {
        input.addEventListener('input', function() {
            const match = this.name.match(/rates\[(\d+)\]/);
            if (match) {
                calculateSpread(match[1]);
            }
        });
    });
}

// Apply USD to Naira rate to all coins
function applyUsdRate() {
    const usdRate = parseFloat(document.getElementById('usdToNairaRate').value);
    if (!usdRate || usdRate <= 0) {
        alert('Please enter a valid USD to Naira rate');
        return;
    }
    
    // This would typically use current USD prices from an API
    // For now, we'll apply a base rate adjustment
    document.querySelectorAll('.buy-rate-input').forEach(input => {
        const currentValue = parseFloat(input.value) || 0;
        if (currentValue > 0) {
            input.value = (usdRate * 1.02).toFixed(2); // 2% markup for buy
        }
    });
    
    document.querySelectorAll('.sell-rate-input').forEach(input => {
        const currentValue = parseFloat(input.value) || 0;
        if (currentValue > 0) {
            input.value = (usdRate * 0.98).toFixed(2); // 2% discount for sell
        }
    });
    
    // Recalculate all spreads
    document.querySelectorAll('input[name^="rates["][name$="][buy_rate]"]').forEach(input => {
        const match = input.name.match(/rates\[(\d+)\]/);
        if (match) {
            calculateSpread(match[1]);
        }
    });
    
    alert('USD rate applied to all cryptocurrencies!');
}

// Apply percentage adjustment
function applyPercentageAdjustment() {
    const percentage = parseFloat(document.getElementById('percentageAdjustment').value);
    if (isNaN(percentage)) {
        alert('Please enter a valid percentage');
        return;
    }
    
    const multiplier = 1 + (percentage / 100);
    
    document.querySelectorAll('.buy-rate-input, .sell-rate-input').forEach(input => {
        const currentValue = parseFloat(input.value) || 0;
        if (currentValue > 0) {
            input.value = (currentValue * multiplier).toFixed(2);
        }
    });
    
    // Recalculate all spreads
    document.querySelectorAll('input[name^="rates["][name$="][buy_rate]"]').forEach(input => {
        const match = input.name.match(/rates\[(\d+)\]/);
        if (match) {
            calculateSpread(match[1]);
        }
    });
    
    alert(`${percentage > 0 ? 'Increased' : 'Decreased'} all rates by ${Math.abs(percentage)}%`);
}

// Reset individual rate
function resetRate(rateId) {
    if (confirm('Reset this rate to default values?')) {
        document.querySelector(`input[name="rates[${rateId}][buy_rate]"]`).value = '0.00';
        document.querySelector(`input[name="rates[${rateId}][sell_rate]"]`).value = '0.00';
        calculateSpread(rateId);
    }
}

// Quick update individual rate
function quickUpdate(rateId) {
    const buyRate = document.querySelector(`input[name="rates[${rateId}][buy_rate]"]`).value;
    const sellRate = document.querySelector(`input[name="rates[${rateId}][sell_rate]"]`).value;
    
    if (!buyRate || !sellRate) {
        alert('Please enter both buy and sell rates');
        return;
    }
    
    // Create a form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.crypto-rates.update", ":id") }}'.replace(':id', rateId);
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    
    const buyRateInput = document.createElement('input');
    buyRateInput.type = 'hidden';
    buyRateInput.name = 'buy_rate';
    buyRateInput.value = buyRate;
    
    const sellRateInput = document.createElement('input');
    sellRateInput.type = 'hidden';
    sellRateInput.name = 'sell_rate';
    sellRateInput.value = sellRate;
    
    form.appendChild(csrfInput);
    form.appendChild(buyRateInput);
    form.appendChild(sellRateInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Load current crypto prices (mock function - replace with real API)
function loadCurrentPrices() {
    // This would typically fetch from CoinGecko or similar API
    document.querySelectorAll('[id^="usd-price-"]').forEach(element => {
        element.textContent = '$' + (Math.random() * 50000 + 1000).toFixed(2);
        element.className = 'badge bg-info text-white';
    });
}

// Refresh rates
function refreshRates() {
    const refreshButton = event.target.closest('button');
    const originalContent = refreshButton.innerHTML;
    
    refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';
    refreshButton.disabled = true;
    
    setTimeout(() => {
        loadCurrentPrices();
        refreshButton.innerHTML = originalContent;
        refreshButton.disabled = false;
        
        // Show success message
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            Rates refreshed successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild.nextSibling);
        
        setTimeout(() => alert.remove(), 3000);
    }, 1500);
}

// Reset all rates
function resetAllRates() {
    if (confirm('This will reset all rates to zero. Are you sure?')) {
        document.querySelectorAll('.buy-rate-input, .sell-rate-input').forEach(input => {
            input.value = '0.00';
        });
        
        document.querySelectorAll('[id^="spread-"]').forEach(element => {
            element.textContent = 'N/A';
            element.className = 'badge bg-secondary';
        });
        
        alert('All rates have been reset');
    }
}

// Copy from previous (placeholder function)
function copyFromPrevious() {
    alert('This feature will copy rates from the previous day/session. Implementation depends on your data storage strategy.');
}

// === MARQUEE FUNCTIONALITY ===

let marqueeInterval;
let marqueeData = [];

// Initialize marquee on page load
document.addEventListener('DOMContentLoaded', function() {
    loadMarqueeData();
    setInterval(loadMarqueeData, 120000); // Refresh every 2 minutes
});

// Load cryptocurrency data for marquee
async function loadMarqueeData() {
    try {
        const refreshIcon = document.getElementById('marquee-refresh-icon');
        refreshIcon.classList.add('fa-spin');
        
        const response = await fetch('{{ route("admin.crypto-rates.live-rates") }}');
        const result = await response.json();
        
        if (result.success && result.data) {
            marqueeData = result.data;
            updateMarquee();
            updateLastUpdated();
        } else {
            console.error('Failed to load marquee data:', result.message);
            showMarqueeError('Failed to load live prices');
        }
        
        refreshIcon.classList.remove('fa-spin');
    } catch (error) {
        console.error('Error loading marquee data:', error);
        showMarqueeError('Connection error');
        document.getElementById('marquee-refresh-icon').classList.remove('fa-spin');
    }
}

// Update marquee content
function updateMarquee() {
    const marqueeContent = document.querySelector('.marquee-content');
    
    if (marqueeData.length === 0) {
        marqueeContent.innerHTML = '<span class="error-text">No data available</span>';
        return;
    }
    
    let marqueeHTML = '';
    
    marqueeData.forEach((coin, index) => {
        const changeClass = coin.change_24h >= 0 ? 'text-success' : 'text-danger';
        const changeIcon = coin.change_24h >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
        const changePercent = Math.abs(coin.change_24h).toFixed(2);
        
        marqueeHTML += `
            <span class="crypto-item">
                <strong class="coin-symbol">${coin.symbol}</strong>
                <span class="coin-price">$${formatNumber(coin.price_usd)}</span>
                <span class="coin-change ${changeClass}">
                    <i class="fas ${changeIcon}"></i>
                    ${changePercent}%
                </span>
                ${coin.price_ngn > 0 ? `<span class="coin-naira">₦${formatNumber(coin.price_ngn)}</span>` : ''}
            </span>
        `;
        
        // Add separator except for last item
        if (index < marqueeData.length - 1) {
            marqueeHTML += '<span class="separator">•</span>';
        }
    });
    
    marqueeContent.innerHTML = marqueeHTML;
    
    // Restart animation
    marqueeContent.style.animation = 'none';
    marqueeContent.offsetHeight; // Trigger reflow
    marqueeContent.style.animation = null;
}

// Show error in marquee
function showMarqueeError(message) {
    const marqueeContent = document.querySelector('.marquee-content');
    marqueeContent.innerHTML = `
        <span class="error-text">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
        </span>
    `;
}

// Update last updated timestamp
function updateLastUpdated() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    document.getElementById('last-updated').textContent = timeString;
}

// Format number with commas
function formatNumber(num) {
    if (num >= 1) {
        return parseFloat(num).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    } else {
        return parseFloat(num).toFixed(6);
    }
}
</script>

<style>
/* === MARQUEE STYLES === */
.crypto-marquee-container {
    overflow: hidden;
    background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 50%, #f8f9fa 100%);
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
}

.crypto-marquee {
    white-space: nowrap;
    padding: 12px 0;
    background: linear-gradient(90deg, 
        rgba(0,123,255,0.05) 0%, 
        rgba(40,167,69,0.05) 50%, 
        rgba(220,53,69,0.05) 100%);
}

.marquee-content {
    display: inline-block;
    animation: marquee 60s linear infinite;
    padding-left: 100%;
    font-size: 14px;
    font-weight: 500;
}

@keyframes marquee {
    0% {
        transform: translateX(0%);
    }
    100% {
        transform: translateX(-100%);
    }
}

.crypto-item {
    display: inline-block;
    margin-right: 40px;
    padding: 8px 12px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.crypto-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.coin-symbol {
    color: #495057;
    font-size: 16px;
    margin-right: 8px;
}

.coin-price {
    color: #007bff;
    font-weight: 600;
    margin-right: 8px;
}

.coin-change {
    font-size: 12px;
    font-weight: 600;
    margin-right: 8px;
}

.coin-naira {
    color: #6c757d;
    font-size: 12px;
    font-style: italic;
}

.separator {
    color: #adb5bd;
    margin: 0 20px;
    font-size: 18px;
}

.loading-text, .error-text {
    color: #6c757d;
    font-style: italic;
    padding: 8px 16px;
}

.error-text {
    color: #dc3545;
}

/* Responsive marquee */
@media (max-width: 768px) {
    .marquee-content {
        animation-duration: 45s;
    }
    
    .crypto-item {
        margin-right: 20px;
        padding: 6px 10px;
    }
    
    .coin-symbol {
        font-size: 14px;
    }
}

/* Pause animation on hover */
.crypto-marquee:hover .marquee-content {
    animation-play-state: paused;
}
</style>
@endsection