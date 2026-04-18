<?php $__env->startPush('styles'); ?>
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;
    --kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0;--kx-muted:#7a8599;
}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-hero{background:linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);border-bottom:1px solid var(--kx-border);padding:1.5rem 1rem 1rem;text-align:center;margin-bottom:1.5rem;}
.kx-hero h1{font-size:1.5rem;font-weight:700;color:#fff;margin:0 0 .25rem;}
.kx-hero p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1rem;}
.coin-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.4rem .9rem;border-radius:20px;font-size:.85rem;font-weight:700;}
.coin-badge.btc{background:rgba(247,147,26,.15);color:#f7931a;border:1px solid rgba(247,147,26,.3);}
.coin-badge.eth{background:rgba(98,126,234,.15);color:#627eea;border:1px solid rgba(98,126,234,.3);}
.coin-badge.usdt{background:rgba(38,161,123,.15);color:#26a17b;border:1px solid rgba(38,161,123,.3);}
.detail-row{display:flex;justify-content:space-between;align-items:flex-start;padding:.75rem 0;border-bottom:1px solid var(--kx-border);}
.detail-row:last-child{border-bottom:none;}
.dr-label{font-size:.8rem;color:var(--kx-muted);}
.dr-value{font-size:.9rem;color:var(--kx-text);font-weight:600;text-align:right;max-width:60%;word-break:break-all;}
.dr-value.green{color:var(--kx-green);}
.status-badge{display:inline-block;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:capitalize;}
.status-pending{background:rgba(234,179,8,.15);color:#f59e0b;border:1px solid rgba(234,179,8,.25);}
.status-completed{background:rgba(0,204,0,.15);color:var(--kx-green);border:1px solid rgba(0,204,0,.25);}
.status-failed{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25);}
.amount-highlight{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1rem 1.25rem;margin:.75rem 0;}
.amount-highlight .ah-label{font-size:.75rem;color:var(--kx-muted);margin-bottom:.2rem;}
.amount-highlight .ah-value{font-size:1.4rem;font-weight:700;color:var(--kx-green);}
.amount-highlight .ah-sub{font-size:.8rem;color:var(--kx-muted);}
.btn-kx-primary{background:var(--kx-green);border:none;color:#000;font-weight:700;border-radius:10px;padding:.8rem 1.5rem;font-size:.95rem;width:100%;transition:all .2s;display:block;text-align:center;text-decoration:none;}
.btn-kx-primary:hover{background:#00e600;transform:translateY(-1px);box-shadow:0 4px 20px rgba(0,204,0,.3);color:#000;}
.btn-kx-secondary{background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text);font-weight:600;border-radius:10px;padding:.8rem 1.5rem;font-size:.95rem;width:100%;display:block;text-align:center;text-decoration:none;transition:background .2s;}
.btn-kx-secondary:hover{background:rgba(255,255,255,.06);color:var(--kx-text);}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="kx-hero">
    <h1><i class="bi bi-file-text-fill me-2" style="color:var(--kx-green);"></i>Trade Summary</h1>
    <p>Your purchase order details</p>
</div>

<div class="container-fluid px-3">
<div class="row justify-content-center">
<div class="col-xl-5 col-lg-6 col-md-8">

    <?php
        $coinClass = strtolower($trade->coin);
        $coinIcons = ['btc'=>'bi-currency-bitcoin','eth'=>'bi-gem','usdt'=>'bi-cashstack'];
        $icon = $coinIcons[$coinClass] ?? 'bi-coin';
    ?>

    <div class="kx-card text-center">
        <div class="mb-2">
            <span class="coin-badge <?php echo e($coinClass); ?>">
                <i class="bi <?php echo e($icon); ?>"></i> <?php echo e($trade->coin); ?>

            </span>
        </div>
        <div class="amount-highlight">
            <div class="ah-label">You're paying</div>
            <div class="ah-value">₦<?php echo e(number_format($trade->naira_amount, 2)); ?></div>
            <div class="ah-sub">≈ $<?php echo e(number_format($trade->usd_amount, 2)); ?> USD</div>
        </div>
    </div>

    <div class="kx-card">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-receipt" style="color:var(--kx-green);font-size:1.1rem;"></i>
            <span style="font-weight:700;font-size:.95rem;">Order Details</span>
        </div>
        <div class="detail-row">
            <span class="dr-label">Trade ID</span>
            <span class="dr-value">#<?php echo e($trade->id); ?></span>
        </div>
        <div class="detail-row">
            <span class="dr-label">Cryptocurrency</span>
            <span class="dr-value"><?php echo e($trade->coin); ?></span>
        </div>
        <div class="detail-row">
            <span class="dr-label">Amount (USD)</span>
            <span class="dr-value green">$<?php echo e(number_format($trade->usd_amount, 2)); ?></span>
        </div>
        <div class="detail-row">
            <span class="dr-label">Amount (NGN)</span>
            <span class="dr-value">₦<?php echo e(number_format($trade->naira_amount, 2)); ?></span>
        </div>
        <div class="detail-row">
            <span class="dr-label">Wallet Address</span>
            <span class="dr-value"><?php echo e($trade->wallet_address); ?></span>
        </div>
        <div class="detail-row">
            <span class="dr-label">Network</span>
            <span class="dr-value"><?php echo e($trade->network); ?></span>
        </div>
        <div class="detail-row">
            <span class="dr-label">Status</span>
            <span class="dr-value">
                <?php
                    $statusClass = match(strtolower($trade->status)) {
                        'completed','success' => 'status-completed',
                        'failed','cancelled'  => 'status-failed',
                        default               => 'status-pending',
                    };
                ?>
                <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e($trade->status); ?></span>
            </span>
        </div>
        <div class="detail-row">
            <span class="dr-label">Date</span>
            <span class="dr-value"><?php echo e($trade->created_at->format('d M Y, H:i')); ?></span>
        </div>
    </div>

    <a href="<?php echo e(route('buy.payment', ['id' => $trade->id])); ?>" class="btn-kx-primary mb-3">
        <i class="bi bi-credit-card me-2"></i>Proceed to Payment
    </a>
    <a href="<?php echo e(route('dashboard')); ?>" class="btn-kx-secondary">
        <i class="bi bi-house me-2"></i>Back to Dashboard
    </a>

    <div style="height:2rem;"></div>
</div>
</div>
</div>

<?php $__env->stopSection(); ?>


        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Trade ID:</span>
                <span class="text-sm font-semibold text-white"><?php echo e($trade->id); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Coin:</span>
                <span class="text-sm font-semibold text-white"><?php echo e($trade->coin); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Amount (USD):</span>
                <span class="text-sm font-semibold text-white">$<?php echo e(number_format($trade->usd_amount, 2)); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Amount (NGN):</span>
                <span class="text-sm font-semibold text-white">₦<?php echo e(number_format($trade->naira_amount, 2)); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Wallet Address:</span>
                <span class="text-sm font-semibold text-white break-all"><?php echo e($trade->wallet_address); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Network:</span>
                <span class="text-sm font-semibold text-white"><?php echo e($trade->network); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Status:</span>
                <span class="text-sm font-semibold text-white capitalize"><?php echo e($trade->status); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-300">Created At:</span>
                <span class="text-sm font-semibold text-white"><?php echo e($trade->created_at->format('d M Y, H:i')); ?></span>
            </div>
        </div>

        <div class="mt-6">
            <a href="<?php echo e(route('buy.payment', ['id' => $trade->id])); ?>" class="w-full bg-gradient-to-r from-blue-600 to-teal-500 p-3 rounded-lg font-semibold text-white shadow-md hover:from-blue-700 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 text-center inline-block">
                Proceed to Payment
            </a>
        </div>
    </div>
</div>

<style>
    /* Reuse styles from buy.blade.php for consistency */
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #111827;
    margin: 0;
    padding: 0;
}

.min-h-screen {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.max-w-md {
    background-color: #1f2937;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.max-w-md:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.3);
}

.text-2xl {
    font-size: 1.75rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.5rem;
    text-align: center;
}

.text-gray-400.mb-6 {
    font-size: 0.875rem;
    color: #9ca3af;
    text-align: center;
    margin-bottom: 1.5rem;
}

.text-sm.font-medium.text-gray-300 {
    font-size: 0.875rem;
    font-weight: 500;
    color: #d1d5db;
}

.text-sm.font-semibold.text-white {
    font-size: 0.875rem;
    font-weight: 600;
    color: #ffffff;
}

.w-full.bg-gradient-to-r.from-blue-600.to-teal-500 {
    width: 100%;
    padding: 0.75rem;
    border-radius: 0.5rem;
    background-image: linear-gradient(to right, #2563eb, #14b8a6);
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: background-image 0.2s ease, transform 0.2s ease;
}

.w-full.bg-gradient-to-r.from-blue-600.to-teal-500:hover {
    background-image: linear-gradient(to right, #1d4ed8, #0d9488);
    transform: translateY(-2px);
}

.w-full.bg-gradient-to-r.from-blue-600.to-teal-500:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.space-y-4 {
    margin-bottom: 1.5rem;
}

.break-all {
    word-break: break-all;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .max-w-md {
        padding: 1.5rem;
    }

    .text-2xl {
        font-size: 1.5rem;
    }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <?php if(session('success')): ?>
        <script>
            toastr.success('<?php echo e(session('success')); ?>');
        </script>
    <?php elseif(session('error')): ?>
        <script>
            toastr.error('<?php echo e(session('error')); ?>');
        </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('buylayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\buy\trade_summary.blade.php ENDPATH**/ ?>