<?php $__env->startPush('styles'); ?>
<style>
:root {
    --kx-green: #00cc00;
    --kx-green-dim: rgba(0,204,0,0.12);
    --kx-card: rgba(14,22,14,0.85);
    --kx-card-border: rgba(0,204,0,0.18);
    --kx-input-bg: rgba(255,255,255,0.05);
    --kx-input-border: rgba(0,204,0,0.25);
    --kx-danger: #ef4444;
    --kx-warning: #f59e0b;
    --kx-info: #3b82f6;
}

.wd-page { max-width: 520px; margin: 0 auto; }

/* Balance card */
.wd-balance-card {
    background: linear-gradient(135deg, rgba(0,204,0,0.18) 0%, rgba(0,60,0,0.55) 100%);
    border: 1px solid var(--kx-card-border);
    border-radius: 20px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}
.wd-balance-card::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 130px; height: 130px;
    background: radial-gradient(circle, rgba(0,204,0,0.18) 0%, transparent 70%);
    pointer-events: none;
}
.wd-balance-label { font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: rgba(255,255,255,.55); margin-bottom: .3rem; }
.wd-balance-amount { font-size: 2.2rem; font-weight: 700; color: #fff; line-height: 1.15; }
.wd-balance-currency { font-size: .75rem; font-weight: 600; letter-spacing: .08em; color: var(--kx-green); background: rgba(0,204,0,.12); padding: .15rem .55rem; border-radius: 20px; display: inline-block; margin-top: .35rem; }
.wd-min { font-size: .82rem; color: rgba(255,255,255,.5); margin-top: .5rem; }
.wd-min strong { color: rgba(255,255,255,.75); }

/* Form card */
.wd-form-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-card-border);
    border-radius: 20px;
    padding: 1.75rem;
}
.wd-form-title { font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 1.25rem; display: flex; align-items: center; gap: .5rem; }
.wd-form-title i { color: var(--kx-green); font-size: 1.1rem; }

.wd-label { font-size: .78rem; font-weight: 600; color: rgba(255,255,255,.6); margin-bottom: .35rem; display: block; }
.wd-input {
    width: 100%;
    background: var(--kx-input-bg);
    border: 1px solid var(--kx-input-border);
    border-radius: 10px;
    padding: .65rem .9rem;
    color: #fff;
    font-size: .9rem;
    font-family: inherit;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    margin-bottom: 1rem;
}
.wd-input:focus { border-color: var(--kx-green); box-shadow: 0 0 0 3px rgba(0,204,0,.12); }
.wd-input option { background: #101e10; }

.wd-bank-info {
    background: rgba(0,204,0,.07);
    border: 1px solid rgba(0,204,0,.15);
    border-radius: 10px;
    padding: .75rem 1rem;
    margin-bottom: 1rem;
    font-size: .83rem;
    color: rgba(255,255,255,.7);
    display: none;
}
.wd-bank-info .wd-bank-row { display: flex; justify-content: space-between; margin-bottom: .2rem; }
.wd-bank-info .wd-bank-row:last-child { margin-bottom: 0; }
.wd-bank-info .wd-bank-val { color: #fff; font-weight: 600; }
.wd-external { display: none; }

.wd-btn {
    width: 100%;
    padding: .8rem;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #00cc00, #007a0c);
    color: #fff;
    font-size: .95rem;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
    margin-top: .25rem;
}
.wd-btn:disabled { opacity: .55; cursor: not-allowed; transform: none !important; }
.wd-btn:not(:disabled):hover { opacity: .9; transform: translateY(-1px); }

/* Toast */
.wd-toast {
    display: none;
    position: fixed;
    bottom: 90px;
    left: 50%;
    transform: translateX(-50%);
    min-width: 260px;
    max-width: 90vw;
    padding: .75rem 1.25rem;
    border-radius: 12px;
    font-size: .87rem;
    font-weight: 600;
    text-align: center;
    z-index: 9000;
    animation: fadeIn .3s;
    color: #fff;
}
.wd-toast.success { background: #16a34a; }
.wd-toast.error   { background: var(--kx-danger); }
.wd-toast.info    { background: var(--kx-info); }
@keyframes fadeIn { from { opacity:0; transform:translateX(-50%) translateY(10px); } to { opacity:1; transform:translateX(-50%) translateY(0); } }

/* Section header */
.wd-section-h { font-size: 1.3rem; font-weight: 700; color: #fff; margin-bottom: .25rem; }
.wd-section-sub { font-size: .82rem; color: rgba(255,255,255,.45); margin-bottom: 1.5rem; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $balance = auth()->user()?->balance ?? 0;
    $minimum_withdrawal = config('withdrawal.minimum', 10);
    $bank_details = [
        'bank_name' => auth()->user()?->bank_name ?? 'N/A',
        'account_number' => auth()->user()?->account_number ?? 'N/A',
        'account_name' => auth()->user()?->account_name ?? 'N/A',
    ];
?>

<div id="wd-toast" class="wd-toast"></div>

<div class="wd-page">

    <!-- Page heading -->
    <div class="mb-4">
        <h1 class="wd-section-h">Withdraw Funds</h1>
        <p class="wd-section-sub">Transfer your NGN balance to your bank account</p>
    </div>

    <!-- Balance card -->
    <div class="wd-balance-card">
        <div class="wd-balance-label">Available Balance</div>
        <div class="wd-balance-amount">&#x20A6;<?php echo e(number_format($balance, 2)); ?></div>
        <div class="wd-balance-currency">NGN</div>
        <div class="wd-min">Minimum withdrawal: <strong>&#x20A6;<?php echo e(number_format($minimum_withdrawal, 2)); ?></strong></div>
    </div>

    <!-- Withdraw form -->
    <div class="wd-form-card">
        <div class="wd-form-title"><i class="bi bi-arrow-up-circle-fill"></i> Withdrawal Details</div>

        <form id="withdrawForm" method="POST" action="<?php echo e(route('withdraw.process')); ?>" autocomplete="off" novalidate>
            <?php echo csrf_field(); ?>

            <label class="wd-label" for="amount">Amount (&#x20A6;)</label>
            <input type="number" name="amount" id="amount" class="wd-input" step="0.01" min="<?php echo e($minimum_withdrawal); ?>" max="<?php echo e($balance); ?>" placeholder="Enter amount" required>

            <label class="wd-label" for="password">Password</label>
            <input type="password" name="password" id="password" class="wd-input" placeholder="Your account password" required autocomplete="current-password">

            <label class="wd-label" for="payment_method">Payment Method</label>
            <select name="payment_method" id="payment_method" class="wd-input" required>
                <option value="bank">Bank Transfer</option>
            </select>

            <div id="bank_option_group" style="display:none;">
                <label class="wd-label" for="bank_option">Bank Option</label>
                <select name="bank_option" id="bank_option" class="wd-input">
                    <option value="default">Default Bank</option>
                    <option value="external">External Bank</option>
                </select>
            </div>

            <!-- Default bank info display -->
            <div id="default_bank_group" class="wd-bank-info">
                <div class="wd-bank-row"><span>Bank</span><span class="wd-bank-val"><?php echo e($bank_details['bank_name']); ?></span></div>
                <div class="wd-bank-row"><span>Account Number</span><span class="wd-bank-val"><?php echo e($bank_details['account_number']); ?></span></div>
                <div class="wd-bank-row"><span>Account Name</span><span class="wd-bank-val"><?php echo e($bank_details['account_name']); ?></span></div>
            </div>

            <!-- External bank fields -->
            <div id="external_bank_group" class="wd-external">
                <label class="wd-label" for="external_bank_name">Bank Name</label>
                <div class="wd-bank-select-wrap" style="position:relative;">
                    <input type="text" id="bank_search_input" class="wd-input" placeholder="Search bank…" autocomplete="off">
                    <div id="bank_dropdown" class="wd-bank-dropdown" style="display:none;position:absolute;z-index:200;width:100%;max-height:220px;overflow-y:auto;background:#0f1f0f;border:1px solid #2a5a2a;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.6);">
                        <div id="bank_dropdown_list"></div>
                    </div>
                </div>
                <input type="hidden" name="external_bank_name" id="external_bank_name">
                <input type="hidden" name="external_bank_code" id="external_bank_code">

                <label class="wd-label mt-3" for="external_account_number">Account Number</label>
                <input type="tel" name="external_account_number" id="external_account_number" class="wd-input" placeholder="10-digit account number" maxlength="10" inputmode="numeric" pattern="\d{10}">

                <!-- Verification status row -->
                <div id="acct_verify_status" style="display:none;margin:8px 0 6px;font-size:.85rem;"></div>

                <label class="wd-label" for="external_account_name">Account Name</label>
                <input type="text" name="external_account_name" id="external_account_name" class="wd-input" placeholder="Auto-filled after verification" readonly style="cursor:not-allowed;opacity:.75;">
            </div>

            <button type="submit" class="wd-btn" id="submitBtn">
                <i class="bi bi-arrow-up-circle-fill me-1"></i> Submit Withdrawal
            </button>
        </form>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const form         = document.getElementById('withdrawForm');
    const pmEl         = document.getElementById('payment_method');
    const boGroup      = document.getElementById('bank_option_group');
    const boEl         = document.getElementById('bank_option');
    const defGroup     = document.getElementById('default_bank_group');
    const extGroup     = document.getElementById('external_bank_group');
    const amtEl        = document.getElementById('amount');
    const pwdEl        = document.getElementById('password');
    const submitBtn    = document.getElementById('submitBtn');
    const extName      = document.getElementById('external_bank_name');      // hidden
    const extBankCode  = document.getElementById('external_bank_code');      // hidden
    const bankSearchEl = document.getElementById('bank_search_input');
    const bankDropdown = document.getElementById('bank_dropdown');
    const bankDropList = document.getElementById('bank_dropdown_list');
    const extAccNum    = document.getElementById('external_account_number');
    const extAccName   = document.getElementById('external_account_name');
    const verifyStatus = document.getElementById('acct_verify_status');
    const MIN          = <?php echo e($minimum_withdrawal); ?>;
    const BAL          = <?php echo e($balance); ?>;

    // ── Toast ───────────────────────────────────────────────────────────────
    function showToast(msg, type) {
        const t = document.getElementById('wd-toast');
        t.textContent = msg;
        t.className = 'wd-toast ' + type;
        t.style.display = 'block';
        clearTimeout(t._timer);
        t._timer = setTimeout(() => { t.style.display = 'none'; }, 5000);
    }

    // ── Bank list ────────────────────────────────────────────────────────────
    let allBanks = [];
    let bankListLoaded = false;

    function loadBanks() {
        if (bankListLoaded) return;
        bankListLoaded = true;  // mark immediately to avoid double-fetch
        bankSearchEl.placeholder = 'Loading banks…';
        bankSearchEl.disabled = true;

        fetch('<?php echo e(route("ajax.banks")); ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Failed to load banks');
            allBanks = data.banks;
            bankSearchEl.placeholder = 'Search bank…';
            bankSearchEl.disabled = false;
        })
        .catch(err => {
            bankListLoaded = false;
            bankSearchEl.placeholder = 'Tap to retry loading banks';
            bankSearchEl.disabled = false;
            showToast('Could not load bank list. Please tap the bank field to retry.', 'error');
        });
    }

    function renderDropdown(filter) {
        const q = (filter || '').toLowerCase();
        const matches = q ? allBanks.filter(b => b.name.toLowerCase().includes(q)) : allBanks;

        bankDropList.innerHTML = '';
        if (!matches.length) {
            bankDropList.innerHTML = '<div style="padding:10px 14px;color:#8a9a8a;font-size:.87rem;">No banks found</div>';
        } else {
            matches.slice(0, 80).forEach(bank => {
                const div = document.createElement('div');
                div.textContent = bank.name;
                div.dataset.code = bank.code;
                div.style.cssText = 'padding:9px 14px;cursor:pointer;font-size:.9rem;color:#d9f0d9;border-bottom:1px solid #1c3a1c;';
                div.addEventListener('mouseenter', () => div.style.background = '#1a3a1a');
                div.addEventListener('mouseleave', () => div.style.background = '');
                div.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectBank(bank.name, bank.code);
                });
                bankDropList.appendChild(div);
            });
        }
        bankDropdown.style.display = 'block';
    }

    function selectBank(name, code) {
        bankSearchEl.value = name;
        extName.value = name;
        extBankCode.value = code;
        bankDropdown.style.display = 'none';
        // Trigger verify if account number already filled
        if (/^\d{10}$/.test(extAccNum.value.trim())) {
            triggerVerify();
        }
    }

    function clearBankSelection() {
        extName.value = '';
        extBankCode.value = '';
        clearVerification();
    }

    bankSearchEl.addEventListener('focus', () => {
        loadBanks();
        if (allBanks.length) renderDropdown(bankSearchEl.value);
    });

    bankSearchEl.addEventListener('input', () => {
        clearBankSelection();
        renderDropdown(bankSearchEl.value);
    });

    bankSearchEl.addEventListener('blur', () => {
        // Delay so click on dropdown item fires first
        setTimeout(() => { bankDropdown.style.display = 'none'; }, 200);
    });

    // ── Account verification ─────────────────────────────────────────────────
    let verifyTimer = null;
    let verifiedAccountName = '';

    function clearVerification() {
        verifiedAccountName = '';
        extAccName.value = '';
        verifyStatus.style.display = 'none';
        verifyStatus.innerHTML = '';
    }

    function setVerifyStatus(html, color) {
        verifyStatus.innerHTML = html;
        verifyStatus.style.color = color;
        verifyStatus.style.display = 'block';
    }

    function triggerVerify() {
        const acctNum  = extAccNum.value.trim();
        const bankCode = extBankCode.value.trim();

        if (!bankCode) {
            setVerifyStatus('<i class="bi bi-exclamation-circle"></i> Please select a bank first.', '#f0c040');
            return;
        }
        if (!/^\d{10}$/.test(acctNum)) return;

        setVerifyStatus('<span class="spinner-border spinner-border-sm me-1" style="width:.9rem;height:.9rem;border-width:2px;"></span> Verifying account…', '#8ab4f8');
        extAccName.value = '';
        verifiedAccountName = '';

        fetch(`<?php echo e(route("ajax.verify-account")); ?>?account_number=${encodeURIComponent(acctNum)}&bank_code=${encodeURIComponent(bankCode)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                verifiedAccountName = data.account_name;
                extAccName.value    = data.account_name;
                setVerifyStatus('<i class="bi bi-check-circle-fill me-1"></i> Verified: <strong>' + data.account_name + '</strong>', '#4caf82');
            } else {
                clearVerification();
                setVerifyStatus('<i class="bi bi-x-circle-fill me-1"></i> ' + (data.message || 'Account not found. Check the number and bank.'), '#f87171');
            }
        })
        .catch(() => {
            clearVerification();
            setVerifyStatus('<i class="bi bi-wifi-off me-1"></i> Network error. Please try again.', '#f87171');
        });
    }

    extAccNum.addEventListener('input', () => {
        clearVerification();
        clearTimeout(verifyTimer);
        if (/^\d{10}$/.test(extAccNum.value.trim())) {
            verifyTimer = setTimeout(triggerVerify, 400);
        }
    });

    // ── Show/hide logic ──────────────────────────────────────────────────────
    function syncBankOption() {
        const def = boEl.value === 'default';
        defGroup.style.display   = def ? 'block' : 'none';
        extGroup.style.display   = def ? 'none'  : 'block';
        if (def) {
            bankSearchEl.removeAttribute('required');
            extAccNum.removeAttribute('required');
            extAccName.removeAttribute('required');
        } else {
            bankSearchEl.setAttribute('required', '');
            extAccNum.setAttribute('required', '');
            extAccName.setAttribute('required', '');
            loadBanks();
        }
    }

    function syncPaymentMethod() {
        if (pmEl.value === 'bank') {
            boGroup.style.display = 'block';
            syncBankOption();
        } else {
            boGroup.style.display = 'none';
            defGroup.style.display = 'none';
            extGroup.style.display = 'none';
        }
    }

    // ── Validation ───────────────────────────────────────────────────────────
    function validate() {
        const amt = parseFloat(amtEl.value) || 0;
        if (!amt || amt < MIN) {
            showToast('Amount must be at least \u20a6' + MIN.toLocaleString('en-NG') + '.', 'error');
            return false;
        }
        if (amt > BAL) {
            showToast('Insufficient balance for this withdrawal.', 'error');
            return false;
        }
        if (!pwdEl.value) {
            showToast('Please enter your password.', 'error');
            return false;
        }
        if (boEl.value === 'default') {
            const bname = '<?php echo e($bank_details["bank_name"]); ?>';
            if (!bname || bname === 'N/A') {
                showToast('Default bank not set. Redirecting to settings…', 'error');
                setTimeout(() => { window.location.href = '<?php echo e(route("edit-bank")); ?>'; }, 2500);
                return false;
            }
        }
        if (boEl.value === 'external') {
            if (!extBankCode.value.trim()) { showToast('Please select a bank from the list.', 'error'); return false; }
            if (!extAccNum.value.trim()) { showToast('Enter the account number.', 'error'); return false; }
            if (!/^\d{10}$/.test(extAccNum.value.trim())) { showToast('Account number must be 10 digits.', 'error'); return false; }
            if (!verifiedAccountName) { showToast('Account not verified yet. Please wait for verification or re-enter details.', 'error'); return false; }
        }
        return true;
    }

    // ── Form submit ──────────────────────────────────────────────────────────
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!validate()) return;

        if (boEl.value === 'default') {
            showToast('Sending to your default bank. Please wait…', 'info');
            await new Promise(r => setTimeout(r, 1800));
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting…';

        const fd = new FormData(form);
        try {
            const res  = await fetch('<?php echo e(route("withdraw.process")); ?>', {
                method: 'POST', body: fd,
                headers: { 'X-CSRF-TOKEN': fd.get('_token') },
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showToast('Withdrawal submitted! Awaiting admin approval.', 'success');
                setTimeout(() => { window.location.href = data.redirect; }, 1200);
            } else {
                if (data.errors) {
                    Object.values(data.errors).forEach(e => showToast(e[0], 'error'));
                } else {
                    showToast(data.error || 'An error occurred.', 'error');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-arrow-up-circle-fill me-1"></i> Submit Withdrawal';
            }
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-arrow-up-circle-fill me-1"></i> Submit Withdrawal';
        }
    });

    pmEl.addEventListener('change', syncPaymentMethod);
    boEl.addEventListener('change', syncBankOption);

    document.addEventListener('DOMContentLoaded', function () {
        syncPaymentMethod();
        <?php if(session('success')): ?>
            showToast(<?php echo e(Js::from(session('success'))); ?>, 'success');
        <?php elseif(session('error')): ?>
            showToast(<?php echo e(Js::from(session('error'))); ?>, 'error');
        <?php endif; ?>
        const errs = <?php echo json_encode($errors->all(), 15, 512) ?>;
        errs.forEach(e => showToast(e, 'error'));
    });
}());
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('selllayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\withdraw\form.blade.php ENDPATH**/ ?>