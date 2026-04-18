

<?php $__env->startPush('styles'); ?>
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;
    --kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0;--kx-muted:#7a8599;
}
body{background:var(--kx-dark);color:var(--kx-text);}

.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1rem;}

/* Success ring */
.success-ring{width:100px;height:100px;border-radius:50%;margin:0 auto 1.25rem;position:relative;display:flex;align-items:center;justify-content:center;}
.success-ring::before{content:'';position:absolute;inset:0;border-radius:50%;border:3px solid var(--kx-green);animation:ringPulse 2s ease-out infinite;}
.success-ring::after{content:'';position:absolute;inset:6px;border-radius:50%;background:rgba(0,204,0,.1);}
.success-icon{font-size:2.8rem;color:var(--kx-green);position:relative;z-index:1;animation:iconPop .5s cubic-bezier(.175,.885,.32,1.275) .2s both;}

/* Confetti particles */
.confetti-container{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0;overflow:hidden;}
.confetti{position:absolute;width:8px;height:8px;border-radius:2px;animation:confettiFall linear forwards;}
@keyframes confettiFall{0%{transform:translateY(-20px) rotate(0deg);opacity:1;}100%{transform:translateY(100vh) rotate(720deg);opacity:0;}}
@keyframes ringPulse{0%{transform:scale(1);opacity:1;}100%{transform:scale(1.5);opacity:0;}}
@keyframes iconPop{0%{transform:scale(0);}100%{transform:scale(1);}}

/* Recap */
.recap-row{display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--kx-border);}
.recap-row:last-child{border-bottom:none;}
.rr-label{font-size:.8rem;color:var(--kx-muted);}
.rr-value{font-size:.9rem;color:var(--kx-text);font-weight:600;}
.rr-value.green{color:var(--kx-green);}

/* Status */
.status-badge{display:inline-block;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:700;background:rgba(234,179,8,.15);color:#f59e0b;border:1px solid rgba(234,179,8,.25);}

/* Buttons */
.btn-kx-primary{background:var(--kx-green);border:none;color:#000;font-weight:700;border-radius:10px;padding:.8rem 1.5rem;font-size:.95rem;width:100%;transition:all .2s;display:block;text-align:center;text-decoration:none;}
.btn-kx-primary:hover{background:#00e600;transform:translateY(-1px);box-shadow:0 4px 20px rgba(0,204,0,.3);color:#000;}
.btn-kx-secondary{background:var(--kx-card2);border:1px solid var(--kx-border);color:var(--kx-text);font-weight:600;border-radius:10px;padding:.8rem 1.5rem;font-size:.95rem;width:100%;display:block;text-align:center;text-decoration:none;transition:background .2s;}
.btn-kx-secondary:hover{background:rgba(255,255,255,.06);color:var(--kx-text);}

.kx-notice{background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.15);border-radius:10px;padding:.85rem 1rem;font-size:.82rem;color:var(--kx-muted);text-align:center;}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="confetti-container" id="confetti"></div>

<div class="container-fluid px-3 py-4" style="position:relative;z-index:1;">
<div class="row justify-content-center">
<div class="col-xl-5 col-lg-6 col-md-8">

    <!-- Success card -->
    <div class="kx-card text-center">
        <div class="success-ring">
            <span class="success-icon"><i class="bi bi-check-lg"></i></span>
        </div>
        <h2 style="font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:.4rem;">Trade Submitted!</h2>
        <p style="font-size:.875rem;color:var(--kx-muted);margin-bottom:1rem;">
            You've successfully submitted your buy order for
            <strong style="color:var(--kx-green);">$<?php echo e(number_format($trade->usd_amount, 2)); ?> of <?php echo e($trade->coin); ?></strong>
        </p>

        <?php
            $coinClass = strtolower($trade->coin);
            $coinIcons = ['btc'=>'bi-currency-bitcoin','eth'=>'bi-gem','usdt'=>'bi-cashstack'];
            $icon = $coinIcons[$coinClass] ?? 'bi-coin';
        ?>

        <!-- Trade recap -->
        <div class="text-start mt-3">
            <div class="recap-row">
                <span class="rr-label">Cryptocurrency</span>
                <span class="rr-value"><i class="bi <?php echo e($icon); ?> me-1"></i><?php echo e($trade->coin); ?></span>
            </div>
            <div class="recap-row">
                <span class="rr-label">Amount (USD)</span>
                <span class="rr-value green">$<?php echo e(number_format($trade->usd_amount, 2)); ?></span>
            </div>
            <div class="recap-row">
                <span class="rr-label">Amount (NGN)</span>
                <span class="rr-value">₦<?php echo e(number_format($trade->naira_amount, 2)); ?></span>
            </div>
            <div class="recap-row">
                <span class="rr-label">Status</span>
                <span class="rr-value"><span class="status-badge">Under Review</span></span>
            </div>
        </div>
    </div>

    <!-- What happens next -->
    <div class="kx-notice mb-3">
        <i class="bi bi-info-circle me-1" style="color:var(--kx-green);"></i>
        Your trade is under review. Our team will verify your payment proof and approve it shortly. You'll receive a notification once approved.
    </div>

    <!-- Actions -->
    <a href="<?php echo e(route('transactions.history')); ?>" class="btn-kx-primary mb-3">
        <i class="bi bi-clock-history me-2"></i>View Transaction History
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
<script>
// Confetti burst
(function() {
    const container = document.getElementById('confetti');
    const colors = ['#00cc00','#00e600','#f7931a','#627eea','#26a17b','#ffffff'];
    for (let i = 0; i < 60; i++) {
        const el = document.createElement('div');
        el.className = 'confetti';
        el.style.left = Math.random() * 100 + 'vw';
        el.style.background = colors[Math.floor(Math.random() * colors.length)];
        el.style.animationDuration = (Math.random() * 2 + 1.5) + 's';
        el.style.animationDelay = (Math.random() * 1.5) + 's';
        el.style.width = el.style.height = (Math.random() * 8 + 5) + 'px';
        container.appendChild(el);
    }
    setTimeout(() => container.remove(), 5000);
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('buylayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/buy/success.blade.php ENDPATH**/ ?>