@extends('selllayout')

@push('styles')
<style>
.chpin-page { max-width: 420px; margin: 0 auto; }

.chpin-card {
    background: rgba(10,18,10,.85);
    border: 1px solid rgba(0,204,0,.18);
    border-radius: 22px;
    padding: 2rem 1.75rem;
    backdrop-filter: blur(16px);
}

.chpin-title { font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: .3rem;
               display: flex; align-items: center; gap: .5rem; }
.chpin-title i { color: #00cc00; }
.chpin-sub { font-size: .8rem; color: rgba(255,255,255,.4); margin-bottom: 1.75rem; line-height: 1.55; }

/* PIN dots */
.chpin-dots { display: flex; justify-content: center; gap: 1rem; margin: .5rem 0 .6rem; }
.chpin-dot {
    width: 14px; height: 14px; border-radius: 50%;
    background: rgba(255,255,255,.12); border: 2px solid rgba(255,255,255,.2);
    transition: all .2s cubic-bezier(.34,1.56,.64,1);
}
.chpin-dot.filled { background: #00cc00; border-color: #00cc00; transform: scale(1.15); box-shadow: 0 0 8px rgba(0,204,0,.45); }
.chpin-dot.active-ring { border-color: rgba(0,204,0,.6); }
.chpin-dot.error { background: #ef4444; border-color: #ef4444; animation: cpShake .4s; }
@keyframes cpShake { 0%,100%{transform:translateX(0)} 25%,75%{transform:translateX(-4px)} 50%{transform:translateX(4px)} }

.chpin-label { display: block; font-size: .72rem; font-weight: 600;
               color: rgba(255,255,255,.4); text-align: center; margin-bottom: .4rem;
               letter-spacing: .06em; text-transform: uppercase; }

/* Separator */
.chpin-sep { border: none; border-top: 1px solid rgba(255,255,255,.06); margin: 1.5rem 0; }

/* Numpad */
.chpin-pad { display: grid; grid-template-columns: repeat(3,1fr); gap: .6rem; }
.ch-btn {
    padding: .82rem .4rem; border-radius: 13px; border: 1px solid rgba(0,204,0,.13);
    background: rgba(0,204,0,.05); color: #fff; font-size: 1.18rem; font-weight: 600;
    cursor: pointer; font-family: inherit; transition: all .15s;
    display: flex; flex-direction: column; align-items: center; gap: 2px; line-height: 1;
}
.ch-btn:active, .ch-btn.pressed { background: rgba(0,204,0,.18); transform: scale(.93); }
.ch-btn .ch-sub { font-size: .42rem; color: rgba(255,255,255,.28); letter-spacing: .08em; }
.ch-btn.zero { grid-column: 2; }
.ch-btn.del  { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.08); grid-column: 3; }

.chpin-step-indicator {
    display: flex; justify-content: center; gap: .4rem; margin-bottom: 1.5rem;
}
.chpin-step-pip {
    height: 4px; border-radius: 2px; background: rgba(255,255,255,.1);
    transition: all .35s;
}
.chpin-step-pip.active   { background: #00cc00; }
.chpin-step-pip.done     { background: rgba(0,204,0,.4); }

.chpin-err { font-size: .78rem; color: #f87171; text-align: center; margin-top: .5rem; min-height: 1rem; }

/* Submit btn */
.chpin-btn {
    width: 100%; margin-top: 1.25rem; padding: .82rem;
    border-radius: 13px; border: none;
    background: linear-gradient(135deg,#00cc00,#007a0c);
    color: #fff; font-weight: 700; font-size: .93rem; font-family: inherit;
    cursor: pointer; transition: opacity .2s, transform .1s;
    display: none; align-items: center; justify-content: center; gap: .45rem;
}
.chpin-btn:hover { opacity: .9; transform: translateY(-1px); }
.chpin-btn:disabled { opacity: .4; cursor: not-allowed; transform: none; }
.chpin-btn.show { display: flex; }

@media(max-width:380px){ .ch-btn { padding: .7rem .25rem; font-size: 1.05rem; } }
</style>
@endpush

@section('content')
@php $hasPin = (bool) auth()->user()->transaction_pin; @endphp

<div class="chpin-page">
    <div class="mb-4">
        <h1 style="font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:.2rem">
            {{ $hasPin ? 'Change PIN' : 'Set Up PIN' }}
        </h1>
        <p style="font-size:.82rem;color:rgba(255,255,255,.4)">
            {{ $hasPin ? 'Update your 4-digit transaction security PIN.' : 'Create a 4-digit PIN to secure your transactions.' }}
        </p>
    </div>

    <div class="chpin-card">

        @if(session('success'))
        <div style="background:rgba(0,204,0,.12);border:1px solid rgba(0,204,0,.25);border-radius:10px;padding:.65rem .9rem;font-size:.83rem;color:#4ade80;margin-bottom:1.25rem">
            &#x2705; {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);border-radius:10px;padding:.65rem .9rem;font-size:.83rem;color:#f87171;margin-bottom:1.25rem">
            @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
        </div>
        @endif

        <!-- Step indicators -->
        <div class="chpin-step-indicator">
            @if($hasPin)<div class="chpin-step-pip" id="cpPip0" style="width:48px"></div>@endif
            <div class="chpin-step-pip" id="cpPip{{ $hasPin ? 1 : 0 }}" style="width:48px"></div>
            <div class="chpin-step-pip" id="cpPip{{ $hasPin ? 2 : 1 }}" style="width:48px"></div>
        </div>

        <!-- Step label -->
        <span class="chpin-label" id="cpStepLabel">
            @if($hasPin)Enter current PIN@else Enter new PIN@endif
        </span>

        <!-- Dots -->
        <div class="chpin-dots" id="cpDots">
            <div class="chpin-dot"></div>
            <div class="chpin-dot"></div>
            <div class="chpin-dot"></div>
            <div class="chpin-dot"></div>
        </div>

        <div class="chpin-err" id="cpErr"></div>

        <hr class="chpin-sep">

        <!-- Numpad -->
        <div class="chpin-pad" id="cpPad">
            <button type="button" class="ch-btn" data-n="1">1</button>
            <button type="button" class="ch-btn" data-n="2">2<span class="ch-sub">ABC</span></button>
            <button type="button" class="ch-btn" data-n="3">3<span class="ch-sub">DEF</span></button>
            <button type="button" class="ch-btn" data-n="4">4<span class="ch-sub">GHI</span></button>
            <button type="button" class="ch-btn" data-n="5">5<span class="ch-sub">JKL</span></button>
            <button type="button" class="ch-btn" data-n="6">6<span class="ch-sub">MNO</span></button>
            <button type="button" class="ch-btn" data-n="7">7<span class="ch-sub">PQRS</span></button>
            <button type="button" class="ch-btn" data-n="8">8<span class="ch-sub">TUV</span></button>
            <button type="button" class="ch-btn" data-n="9">9<span class="ch-sub">WXYZ</span></button>
            <button type="button" class="ch-btn zero" data-n="0">0</button>
            <button type="button" class="ch-btn del" id="cpDel">&#x232B;</button>
        </div>

        <!-- Hidden form submitted on completion -->
        <form method="POST" action="{{ route('pin.change') }}" id="cpForm">
            @csrf
            @if($hasPin)<input type="hidden" name="current_pin" id="cpCurrentInput">@endif
            <input type="hidden" name="new_pin" id="cpNewInput">
            <input type="hidden" name="pin_confirm" id="cpConfirmInput">
            <button type="submit" class="chpin-btn" id="cpSubmitBtn">
                <i class="bi bi-shield-lock-fill"></i>
                {{ $hasPin ? 'Change PIN' : 'Set PIN' }}
            </button>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const HAS_PIN = {{ $hasPin ? 'true' : 'false' }};
    // Steps: HAS_PIN → [current, new, confirm],  else → [new, confirm]
    const STEPS = HAS_PIN ? ['current', 'new', 'confirm'] : ['new', 'confirm'];
    const LABELS = {
        current: 'Enter current PIN',
        new:     'Enter new PIN',
        confirm: 'Confirm new PIN',
    };
    const pips = STEPS.map((_,i) => document.getElementById('cpPip' + i));

    let step   = 0;
    let values = { current:'', new:'', confirm:'' };
    let field  = STEPS[0];   // current active field

    const dots   = Array.from(document.querySelectorAll('#cpDots .chpin-dot'));
    const label  = document.getElementById('cpStepLabel');
    const errEl  = document.getElementById('cpErr');
    const submitBtn = document.getElementById('cpSubmitBtn');

    function renderDots(value) {
        dots.forEach((d,i) => {
            d.classList.toggle('filled', i < value.length);
            d.classList.remove('error');
        });
    }

    function updatePips() {
        pips.forEach((p,i) => {
            if (!p) return;
            p.classList.toggle('active', i === step);
            p.classList.toggle('done',   i < step);
        });
    }

    function flashError(msg) {
        errEl.textContent = msg;
        dots.forEach(d => { d.classList.remove('filled'); d.classList.add('error'); });
        setTimeout(() => {
            dots.forEach(d => d.classList.remove('error'));
            errEl.textContent = '';
        }, 700);
    }

    function nextStep() {
        step++;
        if (step >= STEPS.length) return;
        field = STEPS[step];
        label.textContent = LABELS[field];
        renderDots('');
        updatePips();
    }

    function add(d) {
        if (values[field].length >= 4) return;
        values[field] += d;
        renderDots(values[field]);

        if (values[field].length === 4) {
            setTimeout(() => {
                if (field === 'current') {
                    nextStep();
                } else if (field === 'new') {
                    nextStep();
                } else if (field === 'confirm') {
                    if (values.new !== values.confirm) {
                        flashError('PINs do not match.');
                        values.new = ''; values.confirm = '';
                        // Go back to "new" step
                        step = HAS_PIN ? 1 : 0;
                        field = 'new';
                        label.textContent = LABELS['new'];
                        renderDots('');
                        updatePips();
                    } else {
                        // All good — fill form and show submit
                        if (HAS_PIN) document.getElementById('cpCurrentInput').value = values.current;
                        document.getElementById('cpNewInput').value    = values.new;
                        document.getElementById('cpConfirmInput').value = values.confirm;
                        submitBtn.classList.add('show');
                        document.getElementById('cpPad').style.opacity = '.4';
                        document.getElementById('cpPad').style.pointerEvents = 'none';
                    }
                }
            }, 200);
        }
    }

    function del() {
        if (values[field].length > 0) {
            values[field] = values[field].slice(0,-1);
            renderDots(values[field]);
            submitBtn.classList.remove('show');
            document.getElementById('cpPad').style.opacity = '';
            document.getElementById('cpPad').style.pointerEvents = '';
        }
    }

    document.getElementById('cpPad').addEventListener('click', function(e) {
        const btn = e.target.closest('[data-n]');
        const d   = e.target.closest('#cpDel');
        if (btn) { btn.classList.add('pressed'); setTimeout(()=>btn.classList.remove('pressed'),150); add(btn.dataset.n); }
        if (d)   del();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key >= '0' && e.key <= '9') add(e.key);
        if (e.key === 'Backspace') del();
    });

    // Initialize pips
    updatePips();
    label.textContent = LABELS[field];
}());
</script>
@endpush
