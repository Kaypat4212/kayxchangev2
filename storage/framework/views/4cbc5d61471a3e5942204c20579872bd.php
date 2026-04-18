<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verify PIN &mdash; <?php echo e(config('app.name')); ?></title>
<link rel="icon" type="image/png" href="<?php echo e(asset('Assests/favicon.png')); ?>">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Poppins',sans-serif;background:#050c05;color:#e8f5e8}

.pin-bg{
    position:fixed;inset:0;
    background:radial-gradient(ellipse 80% 60% at 50% 0%, rgba(0,204,0,.2) 0%, transparent 60%),#050c05;
}

.pin-shell{
    position:relative;z-index:5;
    min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:1.5rem 1rem;
}

/* Brand mark */
.pin-brand{
    display:flex;align-items:center;gap:.6rem;margin-bottom:2rem;
    text-decoration:none;color:#fff;font-size:1.1rem;font-weight:700;
}
.pin-brand-icon{
    width:40px;height:40px;border-radius:12px;
    background:linear-gradient(135deg,#00cc00,#007a0c);
    display:flex;align-items:center;justify-content:center;font-size:1.2rem;
}

/* Card */
.pin-card{
    width:100%;max-width:360px;
    background:rgba(10,18,10,.9);
    border:1px solid rgba(0,204,0,.16);
    border-radius:24px;
    padding:2.25rem 1.75rem;
    backdrop-filter:blur(16px);
    box-shadow:0 20px 60px rgba(0,0,0,.5);
}

.pin-title{font-size:1.25rem;font-weight:700;color:#fff;text-align:center;margin-bottom:.3rem}
.pin-sub{font-size:.8rem;color:rgba(255,255,255,.4);text-align:center;margin-bottom:1.75rem;line-height:1.55}

/* Avatar */
.pin-avatar{
    width:52px;height:52px;border-radius:50%;
    background:linear-gradient(135deg,rgba(0,204,0,.25),rgba(0,80,0,.4));
    border:2px solid rgba(0,204,0,.3);
    display:flex;align-items:center;justify-content:center;
    font-size:1.1rem;font-weight:700;color:#00cc00;
    margin:0 auto 1.25rem;
    text-transform:uppercase;
}

/* Dots */
.pv-dots{display:flex;justify-content:center;gap:1rem;margin-bottom:.75rem}
.pv-dot{
    width:14px;height:14px;border-radius:50%;
    background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.2);
    transition:all .2s cubic-bezier(.34,1.56,.64,1);
}
.pv-dot.filled{background:#00cc00;border-color:#00cc00;transform:scale(1.15);box-shadow:0 0 8px rgba(0,204,0,.5)}
.pv-dot.error{background:#ef4444;border-color:#ef4444;animation:pvShake .4s}
@keyframes pvShake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-5px)}40%,80%{transform:translateX(5px)}}

/* Error */
.pv-err{font-size:.78rem;color:#f87171;text-align:center;margin-bottom:.85rem;min-height:1rem;line-height:1.4}

/* Numpad */
.pv-pad{display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem}
.pv-btn{
    padding:.85rem .5rem;border-radius:13px;border:1px solid rgba(0,204,0,.14);
    background:rgba(0,204,0,.05);color:#fff;font-size:1.2rem;font-weight:600;
    cursor:pointer;transition:all .15s;font-family:inherit;
    display:flex;flex-direction:column;align-items:center;gap:2px;line-height:1;
}
.pv-btn:active,.pv-btn.pressed{background:rgba(0,204,0,.18);transform:scale(.93)}
.pv-btn .pv-sub{font-size:.42rem;color:rgba(255,255,255,.28);letter-spacing:.08em;font-weight:500}
.pv-btn.zero{grid-column:2}
.pv-btn.del{background:rgba(255,255,255,.03);border-color:rgba(255,255,255,.08);grid-column:3}
.pv-btn.del:active{background:rgba(255,255,255,.08)}

/* Submit */
.pv-submit{
    width:100%;margin-top:1.25rem;padding:.82rem;
    border-radius:13px;border:none;
    background:linear-gradient(135deg,#00cc00,#007a0c);
    color:#fff;font-weight:700;font-size:.93rem;font-family:inherit;
    cursor:pointer;transition:opacity .2s,transform .1s;
    display:flex;align-items:center;justify-content:center;gap:.5rem;
}
.pv-submit:hover{opacity:.9;transform:translateY(-1px)}
.pv-submit:disabled{opacity:.4;cursor:not-allowed;transform:none}

/* Forgot */
.pv-forgot{display:block;text-align:center;margin-top:1rem;font-size:.78rem;color:rgba(255,255,255,.35);text-decoration:none}
.pv-forgot:hover{color:rgba(255,255,255,.6)}

/* Back link */
.pv-back{display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:rgba(255,255,255,.3);text-decoration:none;margin-bottom:1.75rem;justify-content:center}
.pv-back:hover{color:rgba(255,255,255,.6)}

@media(max-width:380px){
    .pv-btn{padding:.75rem .3rem;font-size:1.1rem}
}
</style>
</head>
<body>
<div class="pin-bg"></div>

<div class="pin-shell">

    <a href="<?php echo e(route('dashboard')); ?>" class="pin-brand">
        <div class="pin-brand-icon">&#x2B22;</div>
        <?php echo e(config('app.name')); ?>

    </a>

    <div class="pin-card">
        <?php $initials = strtoupper(mb_substr($user->name, 0, 1)); ?>
        <div class="pin-avatar"><?php echo e($initials); ?></div>
        <h1 class="pin-title">Enter your PIN</h1>
        <p class="pin-sub">Verify your identity to continue<br>to the requested page.</p>

        <div class="pv-dots" id="pvDots">
            <div class="pv-dot"></div>
            <div class="pv-dot"></div>
            <div class="pv-dot"></div>
            <div class="pv-dot"></div>
        </div>

        <?php $__errorArgs = ['pin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="pv-err"><?php echo e($message); ?></div>
        <?php else: ?>
        <div class="pv-err" id="pvErr"></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <form method="POST" action="<?php echo e(route('pin.verify')); ?>" id="pvForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="pin" id="pvPinInput">
            <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">

            <div class="pv-pad" id="pvPad">
                <button type="button" class="pv-btn" data-n="1">1</button>
                <button type="button" class="pv-btn" data-n="2">2<span class="pv-sub">ABC</span></button>
                <button type="button" class="pv-btn" data-n="3">3<span class="pv-sub">DEF</span></button>
                <button type="button" class="pv-btn" data-n="4">4<span class="pv-sub">GHI</span></button>
                <button type="button" class="pv-btn" data-n="5">5<span class="pv-sub">JKL</span></button>
                <button type="button" class="pv-btn" data-n="6">6<span class="pv-sub">MNO</span></button>
                <button type="button" class="pv-btn" data-n="7">7<span class="pv-sub">PQRS</span></button>
                <button type="button" class="pv-btn" data-n="8">8<span class="pv-sub">TUV</span></button>
                <button type="button" class="pv-btn" data-n="9">9<span class="pv-sub">WXYZ</span></button>
                <button type="button" class="pv-btn zero" data-n="0">0</button>
                <button type="button" class="pv-btn del" id="pvDel">&#x232B;</button>
            </div>

            <button type="submit" class="pv-submit" id="pvSubmit" disabled>
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Confirm
            </button>
        </form>

        <a href="<?php echo e(route('pin.change')); ?>" class="pv-forgot">Forgot PIN? Change it here</a>
    </div>

    <a href="<?php echo e(url()->previous() !== url('/verify-pin') ? url()->previous() : route('dashboard')); ?>" class="pv-back" style="margin-top:1.25rem">
        &#x2190; Go back
    </a>

</div>

<script>
(function(){
    let pin = '';
    const dots   = Array.from(document.querySelectorAll('#pvDots .pv-dot'));
    const input  = document.getElementById('pvPinInput');
    const submit = document.getElementById('pvSubmit');
    const errEl  = document.getElementById('pvErr');

    function render() {
        dots.forEach((d,i) => d.classList.toggle('filled', i < pin.length));
        submit.disabled = pin.length < 4;
        if (input) input.value = pin;
    }

    function add(d) {
        if (pin.length < 4) { pin += d; render(); }
        if (pin.length === 4) {
            // Tiny delay → auto-submit
            setTimeout(() => document.getElementById('pvForm').submit(), 380);
        }
    }

    function del() {
        pin = pin.slice(0,-1); render();
    }

    document.getElementById('pvPad').addEventListener('click', function(e) {
        const btn = e.target.closest('[data-n]');
        const d   = e.target.closest('#pvDel');
        if (btn) { btn.classList.add('pressed'); setTimeout(()=>btn.classList.remove('pressed'),150); add(btn.dataset.n); }
        if (d)   { del(); }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key >= '0' && e.key <= '9') add(e.key);
        if (e.key === 'Backspace')          del();
    });

    // Flash existing error dots
    <?php $__errorArgs = ['pin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
    dots.forEach(d => { d.classList.add('error'); setTimeout(()=>d.classList.remove('error'), 1000); });
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
}());
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\pin\verify.blade.php ENDPATH**/ ?>