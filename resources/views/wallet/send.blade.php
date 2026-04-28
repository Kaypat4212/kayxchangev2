@extends('selllayout')

@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-green-dim: rgba(0,204,0,0.12);
    --kx-card: rgba(14,22,14,0.88);
    --kx-card-border: rgba(0,204,0,0.18);
    --kx-input-bg: rgba(255,255,255,0.05);
    --kx-input-border: rgba(0,204,0,0.22);
    --kx-danger: #ef4444;

    /* Text colors that adapt to light mode */
    --text-primary: #fff;
    --text-secondary: rgba(255,255,255,.75);
    --text-muted: rgba(255,255,255,.55);
    --text-placeholder: rgba(255,255,255,.3);
    --text-error: #ef4444;
}

/* Light mode overrides */
body.light-mode {
    --kx-card: rgba(255,255,255,0.95);
    --kx-card-border: rgba(0,204,0,0.25);
    --kx-input-bg: rgba(0,0,0,0.03);
    --kx-input-border: rgba(0,204,0,0.3);

    --text-primary: #0a1a0a;
    --text-secondary: rgba(10,26,10,.75);
    --text-muted: rgba(10,26,10,.55);
    --text-placeholder: rgba(10,26,10,.3);
}

.send-page { max-width: 540px; margin: 0 auto; }

/* Balance card */
.send-balance-card {
    background: linear-gradient(135deg, rgba(0,204,0,0.18) 0%, rgba(0,50,0,0.60) 100%);
    border: 1px solid var(--kx-card-border);
    border-radius: 20px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}
.send-balance-card::before {
    content: ''; position: absolute; top: -50px; right: -50px;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(0,204,0,0.15) 0%, transparent 70%);
    pointer-events: none;
}
.send-balance-label { font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .25rem; }
.send-balance-val   { font-size: 2.2rem; font-weight: 700; color: var(--text-primary); line-height: 1.1; }
.send-kxtag         { font-size: .8rem; color: var(--kx-green); background: rgba(0,204,0,.1); padding: .2rem .65rem; border-radius: 20px; display: inline-block; margin-top: .4rem; font-weight: 600; }

/* Card */
.send-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-card-border);
    border-radius: 20px;
    padding: 1.75rem;
    margin-bottom: 1.5rem;
}
.send-card-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.25rem; display: flex; align-items: center; gap: .5rem; }
.send-card-title i { color: var(--kx-green); }

/* Label + input */
.send-label { font-size: .78rem; font-weight: 600; color: var(--text-muted); margin-bottom: .35rem; display: block; }
.send-input {
    width: 100%;
    background: var(--kx-input-bg);
    border: 1px solid var(--kx-input-border);
    border-radius: 10px;
    padding: .7rem 1rem;
    color: var(--text-primary);
    font-size: .95rem;
    outline: none;
    transition: border-color .2s;
}
.send-input:focus { border-color: var(--kx-green); }
.send-input::placeholder { color: var(--text-placeholder); }

/* Recipient preview */
.recipient-card {
    display: none;
    background: rgba(0,204,0,0.07);
    border: 1px solid rgba(0,204,0,0.28);
    border-radius: 12px;
    padding: .9rem 1.1rem;
    margin-top: .75rem;
    align-items: center;
    gap: .85rem;
}
.recipient-card.visible { display: flex; }
.recipient-avatar {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #00cc00 0%, #006600 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.recipient-name  { font-size: .95rem; font-weight: 700; color: var(--text-primary); }
.recipient-tag   { font-size: .78rem; color: var(--kx-green); }
.recipient-email { font-size: .75rem; color: var(--text-muted); }
.recipient-error { display: none; font-size: .82rem; color: var(--text-error); margin-top: .5rem; }
.recipient-error.visible { display: block; }

/* Amount display */
.amount-display { font-size: 2rem; font-weight: 700; color: var(--kx-green); text-align: center; margin: .5rem 0; }
.amount-sub     { font-size: .8rem; color: var(--text-muted); text-align: center; }

/* PIN dots */
.pin-row { display: flex; gap: .6rem; justify-content: center; margin: .5rem 0 1rem; }
.pin-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
    border: 1.5px solid rgba(0,204,0,.35);
    transition: background .15s;
}
.pin-dot.filled { background: var(--kx-green); border-color: var(--kx-green); }

/* Submit btn */
.send-btn {
    width: 100%;
    background: linear-gradient(90deg, #00cc00, #009900);
    border: none;
    border-radius: 12px;
    padding: .85rem;
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
}
.send-btn:hover   { opacity: .9; }
.send-btn:active  { transform: scale(.98); }
.send-btn:disabled { opacity: .45; cursor: not-allowed; }

/* History link */
.history-link {
    display: flex; align-items: center; gap: .5rem;
    background: var(--kx-card);
    border: 1px solid var(--kx-card-border);
    border-radius: 14px;
    padding: .9rem 1.25rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: .9rem;
    font-weight: 600;
    transition: border-color .2s, color .2s;
}
.history-link:hover { border-color: var(--kx-green); color: var(--kx-green); }
</style>
@endpush

@section('content')
<div class="container py-4 send-page">

    {{-- Balance + KX tag --}}
    <div class="send-balance-card">
        <div class="send-balance-label">Wallet Balance</div>
        <div class="send-balance-val">₦{{ number_format(Auth::user()->balance, 2) }}</div>
        <div class="send-kxtag"><i class="bi bi-tag-fill"></i> {{ Auth::user()->kx_tag }}</div>
    </div>

    {{-- Success / error flash --}}
    @if(session('success'))
        <div class="alert alert-success mb-3" style="border-radius:12px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-3" style="border-radius:12px;">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Send Form --}}
    <form method="POST" action="{{ route('wallet.send.post') }}" id="sendForm">
        @csrf

        {{-- Step 1: Recipient --}}
        <div class="send-card">
            <div class="send-card-title"><i class="bi bi-person-fill-check"></i> Recipient</div>

            <label class="send-label">KX Tag or Email Address</label>
            <input type="text" id="recipientQuery" class="send-input" placeholder="e.g. KX4A8B2C or user@email.com"
                   autocomplete="off" spellcheck="false">

            <div class="recipient-card" id="recipientCard">
                <div class="recipient-avatar" id="recipientInitial">?</div>
                <div>
                    <div class="recipient-name"  id="recipientName"></div>
                    <div class="recipient-tag"   id="recipientTagDisplay"></div>
                    <div class="recipient-email" id="recipientEmailDisplay"></div>
                </div>
            </div>
            <div class="recipient-error" id="recipientError"></div>

            {{-- Hidden field submitted with form --}}
            <input type="hidden" name="recipient_id" id="recipientIdInput" value="{{ old('recipient_id') }}">
        </div>

        {{-- Step 2: Amount --}}
        <div class="send-card">
            <div class="send-card-title"><i class="bi bi-currency-exchange"></i> Amount</div>

            <label class="send-label">Amount (₦)</label>
            <input type="number" name="amount" id="amountInput" class="send-input"
                   placeholder="Min ₦100" min="100" step="1"
                   value="{{ old('amount') }}">
            <div class="amount-sub mt-2" id="amountPreview">&nbsp;</div>

            <label class="send-label mt-3">Note (optional)</label>
            <input type="text" name="note" class="send-input" placeholder="e.g. Rent split, thanks!"
                   maxlength="200" value="{{ old('note') }}">
        </div>

        {{-- Step 3: PIN --}}
        <div class="send-card">
            <div class="send-card-title"><i class="bi bi-shield-lock-fill"></i> Confirm with PIN</div>
            <div class="pin-row" id="pinDots">
                <div class="pin-dot"></div>
                <div class="pin-dot"></div>
                <div class="pin-dot"></div>
                <div class="pin-dot"></div>
            </div>
            <input type="password" name="pin" id="pinInput" class="send-input text-center"
                   placeholder="Enter 4-digit PIN" maxlength="4" inputmode="numeric" pattern="\d{4}"
                   style="letter-spacing: .4em; font-size:1.4rem;">
        </div>

        <button type="submit" class="send-btn" id="sendBtn" disabled>
            <i class="bi bi-send-fill me-2"></i> Send Money
        </button>
    </form>

    {{-- History link --}}
    <a href="{{ route('wallet.transfers') }}" class="history-link mt-3">
        <i class="bi bi-clock-history" style="color:var(--kx-green);"></i>
        View Transfer History
        <i class="bi bi-chevron-right ms-auto"></i>
    </a>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const queryInput   = document.getElementById('recipientQuery');
    const recipientCard  = document.getElementById('recipientCard');
    const recipientError = document.getElementById('recipientError');
    const recipientIdInput = document.getElementById('recipientIdInput');
    const recipientInitial = document.getElementById('recipientInitial');
    const recipientName    = document.getElementById('recipientName');
    const recipientTagDisplay  = document.getElementById('recipientTagDisplay');
    const recipientEmailDisplay = document.getElementById('recipientEmailDisplay');
    const amountInput  = document.getElementById('amountInput');
    const amountPreview = document.getElementById('amountPreview');
    const pinInput     = document.getElementById('pinInput');
    const pinDots      = document.querySelectorAll('.pin-dot');
    const sendBtn      = document.getElementById('sendBtn');

    let lookupTimer = null;
    let recipientConfirmed = false;

    function setRecipient(data) {
        recipientIdInput.value = data.id;
        recipientInitial.textContent = data.name.charAt(0).toUpperCase();
        recipientName.textContent    = data.name;
        recipientTagDisplay.textContent  = data.kx_tag;
        recipientEmailDisplay.textContent = data.email;
        recipientCard.classList.add('visible');
        recipientError.classList.remove('visible');
        recipientConfirmed = true;
        checkReady();
    }

    function clearRecipient(msg) {
        recipientIdInput.value = '';
        recipientCard.classList.remove('visible');
        recipientConfirmed = false;
        if (msg) {
            recipientError.textContent = msg;
            recipientError.classList.add('visible');
        } else {
            recipientError.classList.remove('visible');
        }
        checkReady();
    }

    queryInput.addEventListener('input', function () {
        const q = this.value.trim();
        clearRecipient();
        clearTimeout(lookupTimer);
        if (q.length < 2) return;
        lookupTimer = setTimeout(() => {
            fetch('/wallet/lookup?query=' + encodeURIComponent(q), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.found) { setRecipient(data); }
                else { clearRecipient(data.message || 'User not found.'); }
            })
            .catch(() => clearRecipient('Lookup failed. Try again.'));
        }, 500);
    });

    amountInput.addEventListener('input', function () {
        const v = parseFloat(this.value);
        if (v >= 100) {
            amountPreview.textContent = '₦' + v.toLocaleString('en-NG', {minimumFractionDigits:2, maximumFractionDigits:2});
        } else {
            amountPreview.textContent = '';
        }
        checkReady();
    });

    pinInput.addEventListener('input', function () {
        const len = this.value.replace(/\D/g, '').length;
        pinDots.forEach((dot, i) => dot.classList.toggle('filled', i < len));
        checkReady();
    });

    function checkReady() {
        const amount = parseFloat(amountInput.value);
        const pin    = pinInput.value.replace(/\D/g, '');
        sendBtn.disabled = !(recipientConfirmed && amount >= 100 && pin.length === 4);
    }

    // Prevent double-submit
    document.getElementById('sendForm').addEventListener('submit', function () {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
    });
})();
</script>
@endpush
