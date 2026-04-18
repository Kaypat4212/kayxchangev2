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

/* Steps */
.kx-steps{display:flex;gap:0;margin-bottom:1.5rem;}
.kx-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative;}
.kx-step:not(:last-child)::after{content:'';position:absolute;top:14px;left:50%;width:100%;height:2px;background:var(--kx-green);z-index:0;}
.step-circle{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;border:2px solid var(--kx-green);background:var(--kx-green);color:#000;position:relative;z-index:1;}
.kx-step.active .step-circle{background:rgba(0,204,0,.15);color:var(--kx-green);}
.step-label{font-size:.72rem;color:var(--kx-green);margin-top:.3rem;text-align:center;}

/* Coin badge */
.coin-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.4rem .9rem;border-radius:20px;font-size:.85rem;font-weight:700;}
.coin-badge.btc{background:rgba(247,147,26,.15);color:#f7931a;border:1px solid rgba(247,147,26,.3);}
.coin-badge.eth{background:rgba(98,126,234,.15);color:#627eea;border:1px solid rgba(98,126,234,.3);}
.coin-badge.usdt{background:rgba(38,161,123,.15);color:#26a17b;border:1px solid rgba(38,161,123,.3);}

/* Detail rows */
.detail-row{display:flex;justify-content:space-between;align-items:flex-start;padding:.75rem 0;border-bottom:1px solid var(--kx-border);}
.detail-row:last-child{border-bottom:none;}
.dr-label{font-size:.8rem;color:var(--kx-muted);}
.dr-value{font-size:.9rem;color:var(--kx-text);font-weight:600;text-align:right;max-width:60%;word-break:break-all;}
.dr-value.green{color:var(--kx-green);}

/* Status */
.status-badge{display:inline-block;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:capitalize;}
.status-pending{background:rgba(234,179,8,.15);color:#f59e0b;border:1px solid rgba(234,179,8,.25);}
.status-completed{background:rgba(0,204,0,.15);color:var(--kx-green);border:1px solid rgba(0,204,0,.25);}
.status-failed{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25);}

/* Amount highlight */
.amount-highlight{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1rem 1.25rem;margin:.75rem 0;}
.amount-highlight .ah-label{font-size:.75rem;color:var(--kx-muted);margin-bottom:.2rem;}
.amount-highlight .ah-value{font-size:1.4rem;font-weight:700;color:var(--kx-green);}
.amount-highlight .ah-sub{font-size:.8rem;color:var(--kx-muted);}

/* Buttons */
.btn-kx-primary{background:var(--kx-green);border:none;color:#000;font-weight:700;border-radius:10px;padding:.8rem 1.5rem;font-size:.95rem;width:100%;transition:all .2s;display:block;text-align:center;text-decoration:none;}
.btn-kx-primary:hover{background:#00e600;transform:translateY(-1px);box-shadow:0 4px 20px rgba(0,204,0,.3);color:#000;}
.btn-kx-secondary{background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text);font-weight:600;border-radius:10px;padding:.8rem 1.5rem;font-size:.95rem;width:100%;display:block;text-align:center;text-decoration:none;transition:background .2s;}
.btn-kx-secondary:hover{background:rgba(255,255,255,.06);color:var(--kx-text);}

.info-note{background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.12);border-radius:10px;padding:.75rem 1rem;font-size:.8rem;color:var(--kx-muted);}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="kx-hero">
    <h1><i class="bi bi-file-text-fill me-2" style="color:var(--kx-green);"></i>Trade Summary</h1>
    <p>Review your order before proceeding to payment</p>
</div>

<div class="container-fluid px-3">
<div class="row justify-content-center">
<div class="col-xl-5 col-lg-6 col-md-8">

    <!-- Progress (step 1 + 2 done, step 3 active) -->
    <div class="kx-steps">
        <div class="kx-step">
            <div class="step-circle"><i class="bi bi-check-lg" style="font-size:.7rem;"></i></div>
            <div class="step-label">Coin & Amount</div>
        </div>
        <div class="kx-step">
            <div class="step-circle"><i class="bi bi-check-lg" style="font-size:.7rem;"></i></div>
            <div class="step-label">Wallet & Network</div>
        </div>
        <div class="kx-step active">
            <div class="step-circle">3</div>
            <div class="step-label">Summary</div>
        </div>
    </div>

    <!-- Coin + Amount Highlight -->
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

    <!-- Trade Details -->
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

    <!-- Note -->
    <div class="info-note mb-3">
        <i class="bi bi-info-circle me-1" style="color:var(--kx-green);"></i>
        You will be directed to make payment. Only proceed if all details above are correct.
    </div>

    <!-- CTA -->
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

<?php $__env->startPush('scripts'); ?>
    <?php if(session('success')): ?>
        <script>
            toastr.success('<?php echo e(e(session("success"))); ?>');
        </script>
    <?php elseif(session('error')): ?>
        <script>
            toastr.error('<?php echo e(e(session("error"))); ?>');
        </script>
    <?php endif; ?>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('buylayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/buy/summary.blade.php ENDPATH**/ ?>