

<?php $__env->startSection('content'); ?>
<?php
    $liveBySymbol = collect($liveRates)->keyBy('symbol');
?>

<style>
:root {
    --kx-green: #00cc00;
    --kx-green-dim: rgba(0, 204, 0, 0.15);
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.07);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
    --kx-red: #ff4444;
    --kx-yellow: #f0b429;
}
body { background: var(--kx-dark) !important; color: var(--kx-text) !important; }

/* ── panels ── */
.kx-panel {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 12px;
    margin-bottom: 1.25rem;
}
.kx-panel-header {
    padding: .875rem 1.25rem;
    border-bottom: 1px solid var(--kx-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
}
.kx-panel-title {
    font-size: .9rem;
    font-weight: 600;
    color: var(--kx-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.kx-panel-title .dot {
    width: 8px; height: 8px;
    background: var(--kx-green);
    border-radius: 50%;
    display: inline-block;
}
.kx-panel-body { padding: 1.25rem; }

/* ── stat cards ── */
.kx-stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .875rem; margin-bottom: 1.25rem; }
.kx-stat {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.kx-stat-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.kx-stat-icon.green  { background: var(--kx-green-dim); color: var(--kx-green); }
.kx-stat-icon.yellow { background: rgba(240,180,41,.15); color: var(--kx-yellow); }
.kx-stat-icon.blue   { background: rgba(88,166,255,.12); color: #58a6ff; }
.kx-stat-icon.purple { background: rgba(149,128,255,.12); color: #9580ff; }
.kx-stat-label { font-size: .72rem; color: var(--kx-muted); text-transform: uppercase; letter-spacing: .05em; }
.kx-stat-value { font-size: 1.4rem; font-weight: 700; color: var(--kx-text); line-height: 1.1; }
.kx-stat-sub { font-size: .72rem; color: var(--kx-muted); }

/* ── ticker ── */
.kx-ticker-wrap {
    background: var(--kx-card2);
    border: 1px solid var(--kx-border);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1.25rem;
}
.kx-ticker-header {
    padding: .45rem 1rem;
    border-bottom: 1px solid var(--kx-border);
    display: flex; align-items: center; gap: .5rem;
    font-size: .75rem; color: var(--kx-muted);
}
.kx-ticker-live { color: var(--kx-green); font-weight: 700; font-size: .65rem; letter-spacing: .08em; }
.kx-ticker-track { display: flex; overflow: hidden; padding: .55rem 0; }
.kx-ticker-scroller {
    display: flex; gap: 0;
    animation: kxScroll 60s linear infinite;
    white-space: nowrap;
}
.kx-ticker-scroller:hover { animation-play-state: paused; }
@keyframes kxScroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
.kx-tick-item {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: 0 1.2rem; font-size: .78rem; border-right: 1px solid var(--kx-border);
}
.kx-tick-sym { font-weight: 700; color: var(--kx-text); }
.kx-tick-price { color: var(--kx-muted); }
.kx-tick-up   { color: var(--kx-green); font-size: .7rem; }
.kx-tick-down { color: var(--kx-red); font-size: .7rem; }

/* ── buttons ── */
.btn-kx-green {
    background: var(--kx-green); color: #000; border: none;
    font-weight: 600; font-size: .8rem; padding: .45rem 1rem; border-radius: 8px;
}
.btn-kx-green:hover { background: #00e600; color: #000; }
.btn-kx-outline {
    background: transparent; color: var(--kx-text);
    border: 1px solid var(--kx-border); font-size: .8rem; padding: .45rem 1rem; border-radius: 8px;
}
.btn-kx-outline:hover { background: var(--kx-card2); color: var(--kx-text); border-color: rgba(255,255,255,.2); }
.btn-kx-danger {
    background: transparent; color: var(--kx-red);
    border: 1px solid rgba(255,68,68,.3); font-size: .75rem; padding: .3rem .7rem; border-radius: 7px;
}
.btn-kx-danger:hover { background: rgba(255,68,68,.1); color: var(--kx-red); border-color: var(--kx-red); }
.btn-kx-edit {
    background: transparent; color: #58a6ff;
    border: 1px solid rgba(88,166,255,.3); font-size: .75rem; padding: .3rem .7rem; border-radius: 7px;
}
.btn-kx-edit:hover { background: rgba(88,166,255,.1); color: #58a6ff; border-color: #58a6ff; }
.btn-kx-sm { font-size: .75rem; padding: .3rem .75rem; border-radius: 7px; }

/* ── table ── */
.kx-table-wrap { overflow-x: auto; border-radius: 0 0 12px 12px; }
.kx-table { width: 100%; border-collapse: collapse; }
.kx-table thead th {
    background: var(--kx-card2); color: var(--kx-muted);
    font-size: .7rem; text-transform: uppercase; letter-spacing: .06em;
    padding: .7rem 1rem; border-bottom: 1px solid var(--kx-border);
    white-space: nowrap; font-weight: 600;
}
.kx-table tbody tr {
    border-bottom: 1px solid var(--kx-border);
    transition: background .15s;
}
.kx-table tbody tr:hover { background: rgba(255,255,255,.02); }
.kx-table tbody tr:last-child { border-bottom: none; }
.kx-table td {
    padding: .8rem 1rem; font-size: .83rem;
    color: var(--kx-text); vertical-align: middle;
}

/* ── coin cell ── */
.kx-coin-cell { display: flex; align-items: center; gap: .65rem; }
.kx-coin-logo { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.kx-coin-logo-fallback {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--kx-card2); border: 1px solid var(--kx-border);
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem; font-weight: 700; color: var(--kx-green); flex-shrink: 0;
}
.kx-coin-name { font-weight: 600; font-size: .85rem; }
.kx-coin-sym  { font-size: .7rem; color: var(--kx-muted); }

/* ── USD price badge ── */
.kx-usd-price { font-weight: 700; color: var(--kx-text); }
.kx-usd-na    { color: var(--kx-muted); font-style: italic; font-size: .75rem; }
.kx-change-up   { color: var(--kx-green); font-size: .72rem; }
.kx-change-down { color: var(--kx-red); font-size: .72rem; }

/* ── rate inputs ── */
.kx-input {
    background: var(--kx-card2) !important;
    border: 1px solid var(--kx-border) !important;
    color: var(--kx-text) !important;
    border-radius: 8px !important; font-size: .82rem !important;
    padding: .4rem .65rem !important;
    width: 120px;
}
.kx-input:focus {
    background: var(--kx-card2) !important;
    border-color: rgba(0,204,0,.4) !important;
    box-shadow: 0 0 0 2px rgba(0,204,0,.1) !important;
    color: var(--kx-text) !important;
}
.kx-input-group { display: flex; align-items: center; gap: .3rem; }
.kx-input-prefix { font-size: .7rem; color: var(--kx-muted); white-space: nowrap; }

/* ── spread badge ── */
.kx-spread {
    display: inline-flex; align-items: center; padding: .2rem .5rem;
    border-radius: 20px; font-size: .7rem; font-weight: 600;
    background: rgba(0,204,0,.12); color: var(--kx-green);
}

/* ── bulk toolbar ── */
.kx-bulk-bar {
    background: var(--kx-card2); border: 1px solid var(--kx-border);
    border-radius: 10px; padding: .75rem 1.1rem;
    display: flex; align-items: center; flex-wrap: wrap; gap: .75rem;
    margin-bottom: 1rem;
}
.kx-bulk-label { font-size: .78rem; color: var(--kx-muted); white-space: nowrap; }

/* ── alerts ── */
.kx-alert {
    border-radius: 10px; padding: .75rem 1rem; margin-bottom: 1rem;
    font-size: .84rem; display: flex; align-items: center; gap: .6rem;
}
.kx-alert-success { background: rgba(0,204,0,.1); border: 1px solid rgba(0,204,0,.25); color: var(--kx-green); }
.kx-alert-error   { background: rgba(255,68,68,.1); border: 1px solid rgba(255,68,68,.25); color: var(--kx-red); }

/* ── modal ── */
.modal-content { background: var(--kx-card); border: 1px solid var(--kx-border); border-radius: 14px; color: var(--kx-text); }
.modal-header { border-bottom: 1px solid var(--kx-border); padding: 1rem 1.25rem; }
.modal-footer { border-top: 1px solid var(--kx-border); padding: .875rem 1.25rem; }
.modal-title { font-size: .95rem; font-weight: 600; }
.btn-close-kx { background: transparent; border: none; color: var(--kx-muted); font-size: 1.2rem; cursor: pointer; padding: .2rem .4rem; }
.btn-close-kx:hover { color: var(--kx-text); }

/* ── spinner inline ── */
.kx-spin { display: inline-block; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── select all checkbox ── */
input[type=checkbox] { accent-color: var(--kx-green); width: 15px; height: 15px; cursor: pointer; }

/* ── page header bar ── */
.kx-page-header {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 12px;
    padding: 1rem 1.4rem;
    margin-bottom: 1.25rem;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem;
}
.kx-page-header h4 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--kx-text); }
.kx-page-header small { font-size: .75rem; color: var(--kx-muted); }
</style>

<div class="container-fluid py-3 px-3 px-md-4">

    
    <div class="kx-page-header">
        <div>
            <h4><i class="bi bi-currency-bitcoin me-2" style="color:var(--kx-green)"></i>Crypto Rate Management</h4>
            <small>Set buy &amp; sell rates for all supported coins &mdash; live USD prices from CoinGecko</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-kx-outline btn-kx-sm" onclick="refreshTicker()">
                <i class="bi bi-arrow-repeat me-1"></i>Refresh
            </button>
            <button class="btn btn-kx-green btn-kx-sm" data-bs-toggle="modal" data-bs-target="#addCoinModal">
                <i class="bi bi-plus-lg me-1"></i>Add Coin
            </button>
        </div>
    </div>

    
    <?php if(session('success')): ?>
    <div class="kx-alert kx-alert-success">
        <i class="bi bi-check-circle-fill"></i>
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="kx-alert kx-alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?php echo e(session('error')); ?>

    </div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
    <div class="kx-alert kx-alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?php echo e($errors->first()); ?>

    </div>
    <?php endif; ?>

    
    <div class="kx-ticker-wrap">
        <div class="kx-ticker-header">
            <span class="kx-ticker-live">&#9679; LIVE</span>
            <span>CoinGecko market prices</span>
            <span class="ms-auto" id="ticker-ts" style="color:var(--kx-muted)"></span>
        </div>
        <div class="kx-ticker-track">
            <div class="kx-ticker-scroller" id="tickerScroller">
                <?php $__currentLoopData = $liveRates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $chg = round($lr['change_24h'] ?? 0, 2); ?>
                <div class="kx-tick-item">
                    <img src="https://assets.coincap.io/assets/icons/<?php echo e(strtolower($lr['symbol'])); ?>@2x.png"
                         style="width:18px;height:18px;border-radius:50%;object-fit:cover"
                         onerror="this.style.display='none'"
                         alt="<?php echo e($lr['symbol']); ?>">
                    <span class="kx-tick-sym"><?php echo e($lr['symbol']); ?></span>
                    <span class="kx-tick-price">$<?php echo e(number_format($lr['price_usd'], $lr['price_usd'] >= 1 ? 2 : 6)); ?></span>
                    <?php if($chg >= 0): ?>
                        <span class="kx-tick-up">&#9650; <?php echo e($chg); ?>%</span>
                    <?php else: ?>
                        <span class="kx-tick-down">&#9660; <?php echo e(abs($chg)); ?>%</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                <?php $__currentLoopData = $liveRates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $chg = round($lr['change_24h'] ?? 0, 2); ?>
                <div class="kx-tick-item">
                    <img src="https://assets.coincap.io/assets/icons/<?php echo e(strtolower($lr['symbol'])); ?>@2x.png"
                         style="width:18px;height:18px;border-radius:50%;object-fit:cover"
                         onerror="this.style.display='none'"
                         alt="<?php echo e($lr['symbol']); ?>">
                    <span class="kx-tick-sym"><?php echo e($lr['symbol']); ?></span>
                    <span class="kx-tick-price">$<?php echo e(number_format($lr['price_usd'], $lr['price_usd'] >= 1 ? 2 : 6)); ?></span>
                    <?php if($chg >= 0): ?>
                        <span class="kx-tick-up">&#9650; <?php echo e($chg); ?>%</span>
                    <?php else: ?>
                        <span class="kx-tick-down">&#9660; <?php echo e(abs($chg)); ?>%</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <?php
        $totalCoins   = $rates->count();
        $lastUpdated  = $rates->max('updated_at');
        $avgSpread    = $rates->count() > 0
            ? $rates->map(fn($r) => $r->sell_rate > 0 ? (($r->sell_rate - $r->buy_rate) / $r->sell_rate) * 100 : 0)->avg()
            : 0;
        $liveCount    = count($liveRates);
    ?>
    <div class="kx-stat-row">
        <div class="kx-stat">
            <div class="kx-stat-icon green"><i class="bi bi-coin"></i></div>
            <div>
                <div class="kx-stat-label">Total Coins</div>
                <div class="kx-stat-value"><?php echo e($totalCoins); ?></div>
                <div class="kx-stat-sub">in system</div>
            </div>
        </div>
        <div class="kx-stat">
            <div class="kx-stat-icon blue"><i class="bi bi-broadcast"></i></div>
            <div>
                <div class="kx-stat-label">Live Prices</div>
                <div class="kx-stat-value"><?php echo e($liveCount); ?></div>
                <div class="kx-stat-sub">from CoinGecko</div>
            </div>
        </div>
        <div class="kx-stat">
            <div class="kx-stat-icon yellow"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="kx-stat-label">Last Updated</div>
                <div class="kx-stat-value" style="font-size:1rem"><?php echo e($lastUpdated ? \Carbon\Carbon::parse($lastUpdated)->diffForHumans() : '—'); ?></div>
                <div class="kx-stat-sub"><?php echo e($lastUpdated ? \Carbon\Carbon::parse($lastUpdated)->format('d M H:i') : ''); ?></div>
            </div>
        </div>
        <div class="kx-stat">
            <div class="kx-stat-icon purple"><i class="bi bi-percent"></i></div>
            <div>
                <div class="kx-stat-label">Avg Spread</div>
                <div class="kx-stat-value"><?php echo e(number_format($avgSpread, 1)); ?>%</div>
                <div class="kx-stat-sub">buy/sell margin</div>
            </div>
        </div>
    </div>

    
    <form method="POST" action="<?php echo e(route('admin.crypto-rates.bulk-update')); ?>" id="bulkForm">
        <?php echo csrf_field(); ?>
        <div class="kx-bulk-bar">
            <div class="kx-bulk-label d-flex align-items-center gap-2">
                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                <label for="selectAll" style="cursor:pointer;font-size:.78rem;color:var(--kx-muted);margin:0">Select All</label>
            </div>
            <div style="width:1px;height:24px;background:var(--kx-border)"></div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="kx-bulk-label"><i class="bi bi-calculator me-1"></i>Auto-adjust by</span>
                <input type="number" id="adjustPct" class="kx-input" style="width:80px" placeholder="%" step="0.01">
                <button type="button" class="btn btn-kx-outline btn-kx-sm" onclick="applyPctAdjust()">Apply %</button>
            </div>
            <div style="width:1px;height:24px;background:var(--kx-border)"></div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="kx-bulk-label"><i class="bi bi-currency-dollar me-1"></i>USD&#8594;NGN rate</span>
                <input type="number" id="usdNgnRate" class="kx-input" style="width:100px" placeholder="e.g. 1600" step="0.01">
                <button type="button" class="btn btn-kx-outline btn-kx-sm" onclick="applyUsdRate()">Apply</button>
            </div>
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-kx-outline btn-kx-sm" onclick="resetForm()"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
                <button type="submit" class="btn btn-kx-green btn-kx-sm" id="bulkSaveBtn" disabled>
                    <i class="bi bi-save me-1"></i>Save Selected
                </button>
            </div>
        </div>

        
        <div class="kx-panel">
            <div class="kx-panel-header">
                <h6 class="kx-panel-title"><span class="dot"></span>Cryptocurrency Rates</h6>
                <span style="font-size:.75rem;color:var(--kx-muted)">USD prices auto-fetched every page load &bull; <span id="selectedCount">0</span> selected</span>
            </div>
            <div class="kx-table-wrap">
                <table class="kx-table">
                    <thead>
                        <tr>
                            <th style="width:40px"></th>
                            <th>Coin</th>
                            <th>USD Price</th>
                            <th>24h Change</th>
                            <th>Buy Rate (₦)</th>
                            <th>Sell Rate (₦)</th>
                            <th>Spread</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $rates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $live    = $liveBySymbol[$rate->coin] ?? null;
                            $usdPrice = $live['price_usd'] ?? null;
                            $change   = $live ? round($live['change_24h'] ?? 0, 2) : null;
                            $spread   = $rate->sell_rate > 0
                                        ? round((($rate->sell_rate - $rate->buy_rate) / $rate->sell_rate) * 100, 2)
                                        : 0;
                        ?>
                        <tr data-id="<?php echo e($rate->id); ?>" data-usd="<?php echo e($usdPrice ?? 0); ?>">
                            <td>
                                <input type="checkbox" name="selected_rates[]" value="<?php echo e($rate->id); ?>"
                                       class="rate-checkbox" onchange="updateSelectedCount()">
                            </td>
                            <td>
                                <div class="kx-coin-cell">
                                    <img src="https://assets.coincap.io/assets/icons/<?php echo e(strtolower($rate->coin)); ?>@2x.png"
                                         class="kx-coin-logo"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                                         alt="<?php echo e($rate->coin); ?>">
                                    <div class="kx-coin-logo-fallback" style="display:none"><?php echo e(strtoupper(substr($rate->coin, 0, 3))); ?></div>
                                    <div>
                                        <div class="kx-coin-name"><?php echo e($rate->coin); ?></div>
                                        <?php if($live): ?>
                                        <div class="kx-coin-sym"><?php echo e($live['name']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($usdPrice !== null): ?>
                                    <span class="kx-usd-price">$<?php echo e(number_format($usdPrice, $usdPrice >= 1 ? 2 : 6)); ?></span>
                                <?php else: ?>
                                    <span class="kx-usd-na">No data</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($change !== null): ?>
                                    <?php if($change >= 0): ?>
                                        <span class="kx-change-up">&#9650; <?php echo e($change); ?>%</span>
                                    <?php else: ?>
                                        <span class="kx-change-down">&#9660; <?php echo e(abs($change)); ?>%</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:var(--kx-muted)">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="kx-input-group">
                                    <span class="kx-input-prefix">₦</span>
                                    <input type="number" name="rates[<?php echo e($rate->id); ?>][buy_rate]"
                                           class="kx-input buy-input" id="buy_<?php echo e($rate->id); ?>"
                                           value="<?php echo e($rate->buy_rate); ?>" step="0.01" min="0"
                                           onchange="recalcSpread(<?php echo e($rate->id); ?>)">
                                </div>
                            </td>
                            <td>
                                <div class="kx-input-group">
                                    <span class="kx-input-prefix">₦</span>
                                    <input type="number" name="rates[<?php echo e($rate->id); ?>][sell_rate]"
                                           class="kx-input sell-input" id="sell_<?php echo e($rate->id); ?>"
                                           value="<?php echo e($rate->sell_rate); ?>" step="0.01" min="0"
                                           onchange="recalcSpread(<?php echo e($rate->id); ?>)">
                                </div>
                            </td>
                            <td>
                                <span class="kx-spread" id="spread_<?php echo e($rate->id); ?>"><?php echo e($spread); ?>%</span>
                            </td>
                            <td style="font-size:.75rem;color:var(--kx-muted)">
                                <?php echo e($rate->updated_at ? \Carbon\Carbon::parse($rate->updated_at)->diffForHumans() : '—'); ?>

                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button type="button" class="btn btn-kx-edit"
                                            onclick="saveIndividual(<?php echo e($rate->id); ?>, '<?php echo e($rate->coin); ?>')">
                                        <i class="bi bi-save"></i>
                                    </button>
                                    <button type="button" class="btn btn-kx-danger"
                                            onclick="confirmDelete(<?php echo e($rate->id); ?>, '<?php echo e($rate->coin); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" style="text-align:center;color:var(--kx-muted);padding:2.5rem">
                                <i class="bi bi-coin" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>
                                No cryptocurrencies configured yet.
                                <a href="#" data-bs-toggle="modal" data-bs-target="#addCoinModal"
                                   style="color:var(--kx-green)">Add the first coin</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>

</div>


<div class="modal fade" id="addCoinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2" style="color:var(--kx-green)"></i>Add New Coin</h5>
                <button type="button" class="btn-close-kx" data-bs-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="<?php echo e(route('admin.crypto-rates.add')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-body" style="padding:1.25rem">
                    <div class="mb-3">
                        <label style="font-size:.8rem;color:var(--kx-muted);display:block;margin-bottom:.35rem">Coin Symbol <span style="color:var(--kx-red)">*</span></label>
                        <input type="text" name="coin" class="form-control kx-input" style="width:100%"
                               placeholder="e.g. BTC, ETH, SOL" maxlength="10" required value="<?php echo e(old('coin')); ?>">
                        <div style="font-size:.7rem;color:var(--kx-muted);margin-top:.3rem">Use standard ticker symbol (uppercase)</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label style="font-size:.8rem;color:var(--kx-muted);display:block;margin-bottom:.35rem">Buy Rate (₦) <span style="color:var(--kx-red)">*</span></label>
                            <input type="number" name="buy_rate" class="form-control kx-input" style="width:100%"
                                   placeholder="0.00" step="0.01" min="0" required value="<?php echo e(old('buy_rate')); ?>">
                        </div>
                        <div class="col-6">
                            <label style="font-size:.8rem;color:var(--kx-muted);display:block;margin-bottom:.35rem">Sell Rate (₦) <span style="color:var(--kx-red)">*</span></label>
                            <input type="number" name="sell_rate" class="form-control kx-input" style="width:100%"
                                   placeholder="0.00" step="0.01" min="0" required value="<?php echo e(old('sell_rate')); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-kx-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-kx-green">Add Coin</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" style="color:var(--kx-red)"><i class="bi bi-exclamation-triangle me-2"></i>Delete Coin</h6>
                <button type="button" class="btn-close-kx" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="padding:1.1rem;font-size:.85rem">
                Remove <strong id="deleteCoinName" style="color:var(--kx-text)"></strong> from the system? This cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-kx-outline" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-kx-danger" style="padding:.4rem .9rem">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php $__currentLoopData = $rates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<form id="saveForm_<?php echo e($rate->id); ?>" method="POST"
      action="<?php echo e(route('admin.crypto-rates.update', $rate->id)); ?>" style="display:none">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="buy_rate" id="hBuy_<?php echo e($rate->id); ?>">
    <input type="hidden" name="sell_rate" id="hSell_<?php echo e($rate->id); ?>">
</form>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<script>
// ── TICKER REFRESH ──
function refreshTicker() {
    fetch('<?php echo e(route("admin.crypto-rates.live-rates")); ?>')
        .then(r => r.json())
        .then(d => {
            if (!d.success || !d.data.length) return;
            const scroller = document.getElementById('tickerScroller');
            let html = '';
            const build = (arr) => arr.map(c => {
                const chg = (c.change_24h || 0).toFixed(2);
                const chgHtml = parseFloat(chg) >= 0
                    ? `<span class="kx-tick-up">&#9650; ${chg}%</span>`
                    : `<span class="kx-tick-down">&#9660; ${Math.abs(chg)}%</span>`;
                const price = c.price_usd >= 1 ? c.price_usd.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})
                                               : c.price_usd.toFixed(6);
                return `<div class="kx-tick-item">
                    <img src="https://assets.coincap.io/assets/icons/${c.symbol.toLowerCase()}@2x.png"
                         style="width:18px;height:18px;border-radius:50%;object-fit:cover" onerror="this.style.display='none'" alt="${c.symbol}">
                    <span class="kx-tick-sym">${c.symbol}</span>
                    <span class="kx-tick-price">$${price}</span>${chgHtml}</div>`;
            }).join('');
            scroller.innerHTML = build(d.data) + build(d.data);
            const ts = new Date(d.timestamp);
            document.getElementById('ticker-ts').textContent = 'Updated ' + ts.toLocaleTimeString();
        })
        .catch(() => {});
}

// ── SPREAD CALC ──
function recalcSpread(id) {
    const buy  = parseFloat(document.getElementById('buy_'  + id)?.value) || 0;
    const sell = parseFloat(document.getElementById('sell_' + id)?.value) || 0;
    const el   = document.getElementById('spread_' + id);
    if (!el) return;
    const pct = sell > 0 ? (((sell - buy) / sell) * 100).toFixed(2) : '0.00';
    el.textContent = pct + '%';
}

// ── SELECT ALL ──
function toggleSelectAll(master) {
    document.querySelectorAll('.rate-checkbox').forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const n = document.querySelectorAll('.rate-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = n;
    document.getElementById('bulkSaveBtn').disabled = n === 0;
}

// ── APPLY % ADJUSTMENT ──
function applyPctAdjust() {
    const pct = parseFloat(document.getElementById('adjustPct').value);
    if (isNaN(pct)) { alert('Enter a valid percentage'); return; }
    document.querySelectorAll('.rate-checkbox:checked').forEach(cb => {
        const id = cb.value;
        const buyEl  = document.getElementById('buy_'  + id);
        const sellEl = document.getElementById('sell_' + id);
        if (buyEl)  buyEl.value  = (parseFloat(buyEl.value)  * (1 + pct/100)).toFixed(2);
        if (sellEl) sellEl.value = (parseFloat(sellEl.value) * (1 + pct/100)).toFixed(2);
        recalcSpread(id);
    });
}

// ── APPLY USD→NGN RATE ──
function applyUsdRate() {
    const rate = parseFloat(document.getElementById('usdNgnRate').value);
    if (isNaN(rate) || rate <= 0) { alert('Enter a valid USD→NGN rate'); return; }
    document.querySelectorAll('tr[data-id]').forEach(row => {
        const cb = row.querySelector('.rate-checkbox');
        if (!cb || !cb.checked) return;
        const id  = row.dataset.id;
        const usd = parseFloat(row.dataset.usd) || 0;
        if (!usd) return;
        const buyEl  = document.getElementById('buy_'  + id);
        const sellEl = document.getElementById('sell_' + id);
        if (buyEl)  buyEl.value  = (usd * rate * 0.97).toFixed(2);
        if (sellEl) sellEl.value = (usd * rate * 1.03).toFixed(2);
        recalcSpread(id);
    });
}

// ── RESET FORM ──
function resetForm() {
    if (!confirm('Reset all unsaved changes?')) return;
    window.location.reload();
}

// ── SAVE INDIVIDUAL ──
function saveIndividual(id, coin) {
    const buy  = document.getElementById('buy_'  + id)?.value;
    const sell = document.getElementById('sell_' + id)?.value;
    if (!buy || !sell) return;
    if (!confirm(`Save rates for ${coin}?\nBuy: ₦${parseFloat(buy).toLocaleString()}\nSell: ₦${parseFloat(sell).toLocaleString()}`)) return;
    document.getElementById('hBuy_'  + id).value = buy;
    document.getElementById('hSell_' + id).value = sell;
    document.getElementById('saveForm_' + id).submit();
}

// ── DELETE CONFIRM ──
function confirmDelete(id, coin) {
    document.getElementById('deleteCoinName').textContent = coin;
    document.getElementById('deleteForm').action = `/admin/crypto-rates/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── INIT: show all spreads ──
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('tr[data-id]').forEach(row => recalcSpread(row.dataset.id));
    document.getElementById('ticker-ts').textContent = 'Page load: ' + new Date().toLocaleTimeString();
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminnavlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\admin\crypto-rates.blade.php ENDPATH**/ ?>