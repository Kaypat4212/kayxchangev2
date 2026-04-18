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

/* Countdown */
.countdown-band{background:rgba(234,179,8,.08);border:1px solid rgba(234,179,8,.2);border-radius:12px;padding:.85rem 1rem;display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.countdown-text{font-size:.8rem;color:#f59e0b;}
.countdown-time{font-size:1.1rem;font-weight:700;color:#f59e0b;font-variant-numeric:tabular-nums;}

/* Trade recap */
.recap-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
.recap-item{background:rgba(255,255,255,.03);border:1px solid var(--kx-border);border-radius:8px;padding:.6rem .8rem;}
.recap-item .ri-label{font-size:.7rem;color:var(--kx-muted);margin-bottom:.2rem;}
.recap-item .ri-value{font-size:.85rem;color:var(--kx-text);font-weight:600;word-break:break-all;}
.recap-item.full{grid-column:1/-1;}
.amount-green{color:var(--kx-green)!important;}

/* Bank account card */
.bank-card{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1.25rem;margin-bottom:1rem;}
.bank-row{display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--kx-border);}
.bank-row:last-child{border-bottom:none;}
.bank-label{font-size:.78rem;color:var(--kx-muted);}
.bank-value{font-size:.9rem;color:var(--kx-text);font-weight:700;}
.copy-btn{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:var(--kx-green);border-radius:6px;padding:.2rem .5rem;font-size:.75rem;cursor:pointer;transition:all .2s;}
.copy-btn:hover{background:rgba(0,204,0,.2);}

/* Upload */
.upload-zone{background:var(--kx-card2);border:2px dashed var(--kx-border);border-radius:12px;padding:2rem 1rem;text-align:center;cursor:pointer;transition:all .25s;margin-bottom:1rem;}
.upload-zone:hover,.upload-zone.drag-over{border-color:var(--kx-green);background:rgba(0,204,0,.05);}
.upload-zone .uz-icon{font-size:2.5rem;color:var(--kx-muted);margin-bottom:.5rem;}
.upload-zone .uz-title{font-size:.9rem;font-weight:600;color:var(--kx-text);margin-bottom:.25rem;}
.upload-zone .uz-hint{font-size:.75rem;color:var(--kx-muted);}
.upload-zone.has-file{border-color:var(--kx-green);background:rgba(0,204,0,.05);}
.upload-zone.has-file .uz-icon{color:var(--kx-green);}
.preview-img{width:100%;max-height:200px;object-fit:cover;border-radius:10px;margin-top:1rem;display:none;}

/* Buttons */
.btn-kx-primary{background:var(--kx-green);border:none;color:#000;font-weight:700;border-radius:10px;padding:.85rem 1.5rem;font-size:.95rem;width:100%;transition:all .2s;}
.btn-kx-primary:hover{background:#00e600;transform:translateY(-1px);box-shadow:0 4px 20px rgba(0,204,0,.3);}
.btn-kx-primary:disabled{opacity:.5;transform:none;cursor:not-allowed;}

.important-note{background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:.85rem 1rem;font-size:.8rem;color:#fca5a5;margin-bottom:1rem;}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="kx-hero">
    <h1><i class="bi bi-bank me-2" style="color:var(--kx-green);"></i>Make Payment</h1>
    <p>Transfer to the account below and upload your proof</p>
</div>

<div class="container-fluid px-3">
<div class="row justify-content-center">
<div class="col-xl-5 col-lg-6 col-md-8">

    <!-- Progress steps -->
    <div class="kx-steps">
        <div class="kx-step">
            <div class="step-circle"><i class="bi bi-check-lg" style="font-size:.7rem;"></i></div>
            <div class="step-label">Coin & Amount</div>
        </div>
        <div class="kx-step">
            <div class="step-circle"><i class="bi bi-check-lg" style="font-size:.7rem;"></i></div>
            <div class="step-label">Summary</div>
        </div>
        <div class="kx-step active">
            <div class="step-circle">3</div>
            <div class="step-label">Payment</div>
        </div>
    </div>

    <!-- Countdown -->
    <div class="countdown-band">
        <div class="countdown-text"><i class="bi bi-clock me-1"></i>Complete payment within</div>
        <div class="countdown-time" id="countdown">50:00</div>
    </div>

    <!-- Trade recap -->
    <div class="kx-card">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-receipt" style="color:var(--kx-green);font-size:1.1rem;"></i>
            <span style="font-weight:700;">Your Order</span>
        </div>
        <div class="recap-grid">
            <div class="recap-item">
                <div class="ri-label">Coin</div>
                <div class="ri-value"><?php echo e($trade->coin); ?></div>
            </div>
            <div class="recap-item">
                <div class="ri-label">Amount (USD)</div>
                <div class="ri-value amount-green">$<?php echo e(number_format($trade->usd_amount, 2)); ?></div>
            </div>
            <div class="recap-item">
                <div class="ri-label">Amount (NGN)</div>
                <div class="ri-value">₦<?php echo e(number_format($trade->naira_amount, 2)); ?></div>
            </div>
            <div class="recap-item">
                <div class="ri-label">Network</div>
                <div class="ri-value"><?php echo e($trade->network); ?></div>
            </div>
            <div class="recap-item full">
                <div class="ri-label">Wallet Address</div>
                <div class="ri-value"><?php echo e($trade->wallet_address); ?></div>
            </div>
        </div>
    </div>

    <!-- Bank account details -->
    <div class="kx-card">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-building me-1" style="color:var(--kx-green);font-size:1.1rem;"></i>
            <span style="font-weight:700;">Payment Instructions</span>
        </div>
        <p style="font-size:.85rem;color:var(--kx-muted);margin-bottom:1rem;">
            Transfer <strong style="color:var(--kx-green);">₦<?php echo e(number_format($trade->naira_amount, 2)); ?></strong> to the account below:
        </p>

        <?php if($accountDetails): ?>
        <div class="bank-card">
            <div class="bank-row">
                <span class="bank-label">Bank Name</span>
                <span class="bank-value"><?php echo e($accountDetails->bank_name ?? 'N/A'); ?></span>
            </div>
            <div class="bank-row">
                <span class="bank-label">Account Name</span>
                <span class="bank-value"><?php echo e($accountDetails->account_name); ?></span>
            </div>
            <div class="bank-row">
                <span class="bank-label">Account Number</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="bank-value" id="acctNumber"><?php echo e($accountDetails->account_number); ?></span>
                    <button class="copy-btn" onclick="copyAcct()" title="Copy" type="button">
                        <i class="bi bi-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:.85rem 1rem;font-size:.85rem;color:#fca5a5;">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Payment account details unavailable. Please contact support.
        </div>
        <?php endif; ?>
    </div>

    <!-- Important note -->
    <div class="important-note">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>Important:</strong> Transfer the <strong>exact amount</strong> shown. Use your Trade ID as narration. After paying, upload your receipt below.
    </div>

    <!-- Upload form -->
    <div class="kx-card">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-upload" style="color:var(--kx-green);font-size:1.1rem;"></i>
            <span style="font-weight:700;">Upload Payment Proof</span>
        </div>

        <form method="POST" action="<?php echo e(route('buy.uploadPayment', ['id' => $trade->id])); ?>" enctype="multipart/form-data" id="uploadForm">
            <?php echo csrf_field(); ?>

            <label for="payment_proof_input" class="upload-zone d-block" id="uploadZone">
                <div class="uz-icon"><i class="bi bi-image" id="uploadIcon"></i></div>
                <div class="uz-title" id="uploadTitle">Click or drag & drop your screenshot</div>
                <div class="uz-hint">JPG, PNG or JPEG — max 5MB</div>
                <input type="file" name="payment_proof" id="payment_proof_input" accept="image/*" required class="d-none">
            </label>
            <img id="previewImg" class="preview-img" alt="Preview">

            <?php $__errorArgs = ['payment_proof'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="text-danger mb-2" style="font-size:.8rem;"><i class="bi bi-exclamation-circle me-1"></i><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <button type="submit" class="btn-kx-primary mt-2" id="submitBtn">
                <i class="bi bi-send-check me-2"></i>Submit Payment Proof
            </button>
        </form>
    </div>

    <div style="height:2rem;"></div>
</div>
</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Countdown timer — 50 minutes
(function() {
    let total = 50 * 60;
    const el = document.getElementById('countdown');
    const interval = setInterval(() => {
        total--;
        if (total <= 0) { clearInterval(interval); el.textContent = '00:00'; return; }
        const m = String(Math.floor(total / 60)).padStart(2,'0');
        const s = String(total % 60).padStart(2,'0');
        el.textContent = m+':'+s;
        if (total < 300) el.style.color = '#f87171';
    }, 1000);
})();

// File upload handling
const input   = document.getElementById('payment_proof_input');
const zone    = document.getElementById('uploadZone');
const title   = document.getElementById('uploadTitle');
const icon    = document.getElementById('uploadIcon');
const preview = document.getElementById('previewImg');

function handleFile(file) {
    if (!file) return;
    title.textContent = file.name;
    icon.className = 'bi bi-check-circle-fill';
    zone.classList.add('has-file');
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(file);
}

input.addEventListener('change', () => handleFile(input.files[0]));

zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) { const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files; handleFile(file); }
});

// Copy account number
function copyAcct() {
    const num = document.getElementById('acctNumber').textContent.trim();
    navigator.clipboard.writeText(num).then(() => {
        const btn = document.querySelector('.copy-btn');
        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="bi bi-copy"></i>'; }, 2000);
    });
}

// Submit feedback
document.getElementById('uploadForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
});
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('buylayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\buy\paymentPage.blade.php ENDPATH**/ ?>