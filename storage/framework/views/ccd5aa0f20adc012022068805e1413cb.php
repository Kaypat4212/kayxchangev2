<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<title>Welcome to <?php echo e(config('app.name')); ?></title>
<link rel="icon" type="image/png" href="<?php echo e(asset('Assests/favicon.png')); ?>">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Poppins',sans-serif;background:#050c05;color:#e8f5e8;overflow:hidden}

/* ── Background ── */
.ob-bg{
    position:fixed;inset:0;
    background:radial-gradient(ellipse 90% 80% at 50% -10%, rgba(0,204,0,.25) 0%, transparent 65%),
               radial-gradient(ellipse 60% 55% at 85% 90%, rgba(0,120,0,.18) 0%, transparent 60%),
               #050c05;
}
.ob-bg-ring{
    position:absolute;border-radius:50%;border:1px solid rgba(0,204,0,.07);
}

/* ── Shell ── */
.ob-shell{
    position:relative;z-index:10;
    height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:1.5rem 1rem;
}

/* ── Progress bar ── */
.ob-progress{
    position:fixed;top:0;left:0;right:0;height:3px;
    background:rgba(255,255,255,.08);z-index:100;
}
.ob-progress-fill{
    height:100%;background:linear-gradient(90deg,#00cc00,#4ade80);
    border-radius:2px;transition:width .5s cubic-bezier(.4,0,.2,1);
}

/* ── Step dots ── */
.ob-dots{
    display:flex;gap:8px;margin-bottom:2rem;
}
.ob-dot{
    width:8px;height:8px;border-radius:50%;
    background:rgba(255,255,255,.2);transition:all .35s;
}
.ob-dot.active{width:24px;border-radius:4px;background:#00cc00;}
.ob-dot.done{background:rgba(0,204,0,.5);}

/* ── Card ── */
.ob-card{
    width:100%;max-width:420px;
    background:rgba(10,18,10,.88);
    border:1px solid rgba(0,204,0,.18);
    border-radius:24px;
    padding:2.5rem 2rem;
    backdrop-filter:blur(20px);
    box-shadow:0 24px 80px rgba(0,0,0,.55), 0 0 0 1px rgba(0,204,0,.06);
}

/* ── Steps ── */
.ob-step{display:none;animation:obFadeUp .4s ease both}
.ob-step.active{display:block}
@keyframes obFadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

/* ── Step 1 (welcome) ── */
.ob-logo{
    width:72px;height:72px;border-radius:20px;
    background:linear-gradient(135deg,#00cc00,#007a0c);
    display:flex;align-items:center;justify-content:center;
    font-size:2rem;color:#fff;margin:0 auto 1.5rem;
    box-shadow:0 8px 30px rgba(0,204,0,.35);
}
.ob-welcome-title{font-size:1.6rem;font-weight:800;color:#fff;text-align:center;line-height:1.25;margin-bottom:.5rem}
.ob-welcome-sub{font-size:.87rem;color:rgba(255,255,255,.5);text-align:center;line-height:1.6;margin-bottom:2rem}
.ob-features{display:flex;flex-direction:column;gap:.6rem;margin-bottom:2rem}
.ob-feat{display:flex;align-items:center;gap:.75rem;font-size:.83rem;color:rgba(255,255,255,.7);padding:.6rem .9rem;background:rgba(0,204,0,.07);border-radius:12px;border:1px solid rgba(0,204,0,.1)}
.ob-feat-icon{width:32px;height:32px;border-radius:10px;background:rgba(0,204,0,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem}

/* ── Step 2 (PIN) ── */
.ob-step-title{font-size:1.35rem;font-weight:700;color:#fff;margin-bottom:.35rem}
.ob-step-sub{font-size:.83rem;color:rgba(255,255,255,.45);margin-bottom:1.75rem;line-height:1.55}

/* PIN dots */
.pin-dots{display:flex;justify-content:center;gap:1rem;margin-bottom:1.5rem}
.pin-dot{
    width:16px;height:16px;border-radius:50%;
    background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.25);
    transition:all .2s cubic-bezier(.34,1.56,.64,1);
}
.pin-dot.filled{background:#00cc00;border-color:#00cc00;transform:scale(1.15);box-shadow:0 0 10px rgba(0,204,0,.5)}
.pin-dot.error{background:#ef4444;border-color:#ef4444;animation:pinShake .4s}
@keyframes pinShake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-5px)}40%,80%{transform:translateX(5px)}}

/* PIN pad */
.pin-pad{display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:1rem}
.pin-btn{
    padding:.9rem .5rem;border-radius:14px;border:1px solid rgba(0,204,0,.15);
    background:rgba(0,204,0,.06);color:#fff;font-size:1.2rem;font-weight:600;
    cursor:pointer;transition:all .15s;font-family:inherit;
    display:flex;flex-direction:column;align-items:center;line-height:1;gap:2px;
}
.pin-btn:active,.pin-btn.pressed{background:rgba(0,204,0,.2);transform:scale(.94);border-color:rgba(0,204,0,.4)}
.pin-btn .pin-letters{font-size:.42rem;font-weight:500;color:rgba(255,255,255,.3);letter-spacing:.08em}
.pin-btn.zero{grid-column:2}
.pin-btn.del{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.1);grid-column:3}
.pin-confirm-group{margin-top:1rem}

/* ── Step 3 (bank) ── */
.ob-input{
    width:100%;padding:.65rem .9rem;
    background:rgba(255,255,255,.05);border:1px solid rgba(0,204,0,.22);
    border-radius:10px;color:#fff;font-size:.88rem;font-family:inherit;
    outline:none;transition:border-color .2s;margin-bottom:.85rem;
}
.ob-input:focus{border-color:#00cc00;box-shadow:0 0 0 3px rgba(0,204,0,.1)}
.ob-input::placeholder{color:rgba(255,255,255,.3)}
.ob-label{display:block;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.5);margin-bottom:.3rem;letter-spacing:.04em}

/* ── Step 4 (done) ── */
.ob-done-icon{
    width:80px;height:80px;border-radius:50%;
    background:linear-gradient(135deg,rgba(0,204,0,.25),rgba(0,100,0,.3));
    border:2px solid rgba(0,204,0,.4);
    display:flex;align-items:center;justify-content:center;
    font-size:2.2rem;margin:0 auto 1.5rem;
    box-shadow:0 0 40px rgba(0,204,0,.25);
    animation:doneScale .5s cubic-bezier(.34,1.56,.64,1);
}
@keyframes doneScale{from{transform:scale(0.5);opacity:0}to{transform:scale(1);opacity:1}}

/* ── Buttons ── */
.ob-btn{
    width:100%;padding:.85rem;border-radius:14px;border:none;
    background:linear-gradient(135deg,#00cc00,#007a0c);
    color:#fff;font-size:.95rem;font-weight:700;font-family:inherit;
    cursor:pointer;transition:opacity .2s,transform .1s;
    display:flex;align-items:center;justify-content:center;gap:.5rem;
}
.ob-btn:hover{opacity:.9;transform:translateY(-1px)}
.ob-btn:active{transform:translateY(0)}
.ob-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.ob-btn-ghost{
    width:100%;padding:.75rem;border-radius:14px;
    border:1px solid rgba(255,255,255,.12);background:transparent;
    color:rgba(255,255,255,.5);font-size:.87rem;font-family:inherit;
    cursor:pointer;transition:all .2s;margin-top:.6rem;
}
.ob-btn-ghost:hover{border-color:rgba(255,255,255,.25);color:rgba(255,255,255,.75)}

/* ── Error msg ── */
.ob-err{font-size:.8rem;color:#f87171;text-align:center;margin-bottom:.75rem;min-height:1.1rem}

/* ── Responsive ── */
@media(max-height:680px){
    .ob-card{padding:1.75rem 1.5rem}
    .ob-features{display:none}
}
@media(max-width:360px){
    .ob-card{padding:1.5rem 1rem;border-radius:16px}
    .pin-btn{padding:.75rem .35rem;font-size:1.1rem}
}
</style>
</head>
<body>

<!-- Background decoration -->
<div class="ob-bg">
    <div class="ob-bg-ring" style="width:600px;height:600px;top:-200px;left:50%;transform:translateX(-50%)"></div>
    <div class="ob-bg-ring" style="width:300px;height:300px;bottom:-60px;right:-40px;opacity:.4"></div>
</div>

<!-- Progress bar -->
<div class="ob-progress"><div class="ob-progress-fill" id="obProgress" style="width:25%"></div></div>

<div class="ob-shell">
    <!-- Step dots -->
    <div class="ob-dots">
        <div class="ob-dot active" id="dot0"></div>
        <div class="ob-dot" id="dot1"></div>
        <div class="ob-dot" id="dot2"></div>
        <div class="ob-dot" id="dot3"></div>
    </div>

    <div class="ob-card">

        
        <div class="ob-step active" id="step0">
            <div class="ob-logo">&#x2B22;</div>
            <h1 class="ob-welcome-title">Welcome,<br><?php echo e(explode(' ', Auth::user()->name)[0]); ?>!</h1>
            <p class="ob-welcome-sub">You're just a few steps away from trading crypto with ease. Let's get your account ready.</p>
            <div class="ob-features">
                <div class="ob-feat"><div class="ob-feat-icon">&#x1F512;</div><span>Set up your security PIN to protect withdrawals</span></div>
                <div class="ob-feat"><div class="ob-feat-icon">&#x1F3E6;</div><span>Add your bank details for fast NGN payouts</span></div>
                <div class="ob-feat"><div class="ob-feat-icon">&#x26A1;</div><span>Buy & sell crypto instantly at great rates</span></div>
            </div>
            <button class="ob-btn" onclick="goStep(1)">
                Get Started <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>

        
        <div class="ob-step" id="step1">
            <p class="ob-step-title">&#x1F512; Set Your Security PIN</p>
            <p class="ob-step-sub">Your 4-digit PIN protects withdrawals and sensitive actions. Keep it private.</p>

            <!-- PIN entry sub-steps -->
            <div id="pinEnterBlock">
                <div class="pin-dots" id="pinDots1">
                    <div class="pin-dot" data-i="0"></div>
                    <div class="pin-dot" data-i="1"></div>
                    <div class="pin-dot" data-i="2"></div>
                    <div class="pin-dot" data-i="3"></div>
                </div>
                <small style="display:block;text-align:center;color:rgba(255,255,255,.35);font-size:.75rem;margin-bottom:.9rem">Enter your new PIN</small>
            </div>

            <div id="pinConfirmBlock" style="display:none">
                <div class="pin-dots" id="pinDots2">
                    <div class="pin-dot" data-i="0"></div>
                    <div class="pin-dot" data-i="1"></div>
                    <div class="pin-dot" data-i="2"></div>
                    <div class="pin-dot" data-i="3"></div>
                </div>
                <small style="display:block;text-align:center;color:rgba(255,255,255,.35);font-size:.75rem;margin-bottom:.9rem">Confirm your PIN</small>
            </div>

            <div class="ob-err" id="pinErr"></div>

            <!-- Numpad -->
            <div class="pin-pad" id="obPinPad">
                <button class="pin-btn" data-n="1">1</button>
                <button class="pin-btn" data-n="2">2<span class="pin-letters">ABC</span></button>
                <button class="pin-btn" data-n="3">3<span class="pin-letters">DEF</span></button>
                <button class="pin-btn" data-n="4">4<span class="pin-letters">GHI</span></button>
                <button class="pin-btn" data-n="5">5<span class="pin-letters">JKL</span></button>
                <button class="pin-btn" data-n="6">6<span class="pin-letters">MNO</span></button>
                <button class="pin-btn" data-n="7">7<span class="pin-letters">PQRS</span></button>
                <button class="pin-btn" data-n="8">8<span class="pin-letters">TUV</span></button>
                <button class="pin-btn" data-n="9">9<span class="pin-letters">WXYZ</span></button>
                <button class="pin-btn zero" data-n="0">0</button>
                <button class="pin-btn del" id="obDelBtn">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6m0 0l4-4m-4 4l4 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h12"/></svg>
                    &#x232B;
                </button>
            </div>
            <button class="ob-btn" id="obPinSaveBtn" style="display:none" onclick="savePinToServer()">Save PIN &amp; Continue</button>
            <button class="ob-btn-ghost" onclick="goStep(0)">&#x2190; Back</button>
        </div>

        
        <div class="ob-step" id="step2">
            <p class="ob-step-title">&#x1F3E6; Bank Details</p>
            <p class="ob-step-sub">Add your bank account for quick NGN withdrawals. You can skip this and add it later in Settings.</p>

            <div class="ob-err" id="bankErr"></div>

            <label class="ob-label">Bank Name</label>
            <input class="ob-input" id="obBankName" type="text" placeholder="e.g. Access Bank" autocomplete="off">

            <label class="ob-label">Account Number</label>
            <input class="ob-input" id="obAccNum" type="tel" placeholder="10-digit account number" maxlength="10" inputmode="numeric">

            <label class="ob-label">Account Name</label>
            <input class="ob-input" id="obAccName" type="text" placeholder="Account holder name" autocomplete="off">

            <button class="ob-btn" id="obBankSaveBtn" onclick="saveBank()">Save &amp; Continue</button>
            <button class="ob-btn-ghost" onclick="skipBank()">Skip for now</button>
        </div>

        
        <div class="ob-step" id="step3">
            <div class="ob-done-icon">&#x2705;</div>
            <h2 class="ob-welcome-title" style="font-size:1.5rem;margin-bottom:.4rem">You're all set!</h2>
            <p class="ob-welcome-sub" style="margin-bottom:2rem">Your account is ready. Start buying and selling crypto at the best rates.</p>
            <button class="ob-btn" id="obFinishBtn" onclick="finish()">
                &#x1F680; Start Trading
            </button>
        </div>

    </div><!-- .ob-card -->
</div><!-- .ob-shell -->

<script>
(function(){
const TOTAL = 4;
const PROGRESS = [25, 50, 75, 100];

let currentStep = 0;
let pin1 = '';
let pin2 = '';
let confirmMode = false;

// ── Navigation ──
window.goStep = function(n) {
    const cur = document.getElementById('step' + currentStep);
    const next = document.getElementById('step' + n);
    if (cur) cur.classList.remove('active');
    if (next) next.classList.add('active');
    currentStep = n;
    updateProgress(n);
    updateDots(n);
};

function updateProgress(n) {
    document.getElementById('obProgress').style.width = PROGRESS[n] + '%';
}

function updateDots(active) {
    for (let i = 0; i < TOTAL; i++) {
        const d = document.getElementById('dot' + i);
        d.classList.toggle('active', i === active);
        d.classList.toggle('done', i < active);
    }
}

// ── PIN pad setup ──
function renderDots(dots, value) {
    dots.forEach((d, i) => {
        d.classList.toggle('filled', i < value.length);
        d.classList.remove('error');
    });
}

function flashError(dotsEl, msg) {
    document.getElementById('pinErr').textContent = msg;
    dotsEl.forEach(d => {
        d.classList.add('error');
        d.classList.remove('filled');
    });
    setTimeout(() => {
        dotsEl.forEach(d => d.classList.remove('error'));
        document.getElementById('pinErr').textContent = '';
    }, 700);
}

const dots1 = Array.from(document.querySelectorAll('#pinDots1 .pin-dot'));
const dots2 = Array.from(document.querySelectorAll('#pinDots2 .pin-dot'));

document.getElementById('obPinPad').addEventListener('click', function(e) {
    const btn = e.target.closest('[data-n]');
    const del = e.target.closest('#obDelBtn');

    if (btn) {
        btn.classList.add('pressed');
        setTimeout(() => btn.classList.remove('pressed'), 150);
        addDigit(btn.dataset.n);
    }
    if (del) {
        delDigit();
    }
});

// Keyboard support
document.addEventListener('keydown', function(e) {
    if (currentStep !== 1) return;
    if (e.key >= '0' && e.key <= '9') addDigit(e.key);
    if (e.key === 'Backspace') delDigit();
});

function addDigit(d) {
    if (!confirmMode) {
        if (pin1.length < 4) {
            pin1 += d;
            renderDots(dots1, pin1);
            if (pin1.length === 4) {
                // Move to confirm
                setTimeout(() => {
                    confirmMode = true;
                    document.getElementById('pinEnterBlock').style.display = 'none';
                    document.getElementById('pinConfirmBlock').style.display = 'block';
                }, 200);
            }
        }
    } else {
        if (pin2.length < 4) {
            pin2 += d;
            renderDots(dots2, pin2);
            if (pin2.length === 4) {
                setTimeout(() => {
                    if (pin1 !== pin2) {
                        flashError(dots2, 'PINs do not match. Try again.');
                        pin2 = '';
                        // Reset both
                        setTimeout(() => {
                            pin1 = ''; pin2 = ''; confirmMode = false;
                            renderDots(dots1, pin1);
                            document.getElementById('pinEnterBlock').style.display = 'block';
                            document.getElementById('pinConfirmBlock').style.display = 'none';
                        }, 700);
                    } else {
                        document.getElementById('obPinSaveBtn').style.display = 'block';
                        document.getElementById('obPinPad').style.opacity = '.4';
                        document.getElementById('obPinPad').style.pointerEvents = 'none';
                    }
                }, 200);
            }
        }
    }
}

function delDigit() {
    if (!confirmMode && pin1.length > 0) {
        pin1 = pin1.slice(0, -1);
        renderDots(dots1, pin1);
    } else if (confirmMode && pin2.length > 0) {
        pin2 = pin2.slice(0, -1);
        renderDots(dots2, pin2);
    }
}

// ── Save PIN via AJAX ──
window.savePinToServer = async function() {
    const btn = document.getElementById('obPinSaveBtn');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    try {
        const res = await fetch('<?php echo e(route("onboard.pin")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ pin: pin1, pin_confirm: pin2 }),
        });
        const data = await res.json();
        if (res.ok && data.success) {
            goStep(2);
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join(' ') : 'Error saving PIN.';
            document.getElementById('pinErr').textContent = msg;
            btn.disabled = false; btn.textContent = 'Save PIN & Continue';
            // Reset
            pin1=''; pin2=''; confirmMode=false;
            renderDots(dots1, pin1); renderDots(dots2, pin2);
            document.getElementById('pinEnterBlock').style.display = 'block';
            document.getElementById('pinConfirmBlock').style.display = 'none';
            document.getElementById('obPinPad').style.opacity = '';
            document.getElementById('obPinPad').style.pointerEvents = '';
        }
    } catch(e) {
        document.getElementById('pinErr').textContent = 'Network error. Try again.';
        btn.disabled = false; btn.textContent = 'Save PIN & Continue';
    }
};

// ── Save Bank via AJAX ──
window.saveBank = async function() {
    const bname = document.getElementById('obBankName').value.trim();
    const accnum = document.getElementById('obAccNum').value.trim();
    const accname = document.getElementById('obAccName').value.trim();
    const errEl = document.getElementById('bankErr');

    if (!bname || !accnum || !accname) {
        errEl.textContent = 'Please fill all fields or skip.'; return;
    }
    if (!/^\d{10}$/.test(accnum)) {
        errEl.textContent = 'Account number must be 10 digits.'; return;
    }
    errEl.textContent = '';

    const btn = document.getElementById('obBankSaveBtn');
    btn.disabled = true; btn.textContent = 'Saving…';

    try {
        const res = await fetch('<?php echo e(route("onboard.bank")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ bank_name: bname, account_number: accnum, account_name: accname }),
        });
        const data = await res.json();
        if (res.ok && data.success) {
            goStep(3);
        } else {
            const msg = data.errors ? Object.values(data.errors).flat().join(' ') : 'Error saving bank.';
            errEl.textContent = msg;
            btn.disabled = false; btn.textContent = 'Save & Continue';
        }
    } catch(e) {
        errEl.textContent = 'Network error. Try again.';
        btn.disabled = false; btn.textContent = 'Save & Continue';
    }
};

window.skipBank = function() { goStep(3); };

// ── Complete onboarding ──
window.finish = async function() {
    const btn = document.getElementById('obFinishBtn');
    btn.disabled = true; btn.textContent = 'Starting…';
    try {
        const res = await fetch('<?php echo e(route("onboard.complete")); ?>', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        });
        const data = await res.json();
        window.location.href = data.redirect || '<?php echo e(route("dashboard")); ?>';
    } catch(e) {
        window.location.href = '<?php echo e(route("dashboard")); ?>';
    }
};

}());
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\onboarding\index.blade.php ENDPATH**/ ?>