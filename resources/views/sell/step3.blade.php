@extends('selllayout')
@php
    $nairaAmount = $nairaAmount ?? session('sell.naira_amount', 0);
    $coin        = session('sell.coin', '');
    $amountInUsd = session('sell.usd_amount', 0);
@endphp

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}

.sell-hero{background:linear-gradient(135deg,rgba(0,204,0,.08) 0%,rgba(0,80,0,.04) 100%);border:1px solid rgba(0,204,0,.14);border-radius:20px;padding:1.75rem 1.5rem;text-align:center;margin-bottom:1.75rem;position:relative;overflow:hidden;}
.sell-hero::before{content:'';position:absolute;top:-50px;left:-50px;width:180px;height:180px;background:radial-gradient(circle,rgba(0,204,0,.07) 0%,transparent 70%);pointer-events:none;}
.sell-hero-icon{width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#00cc00,#007a0c);display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;font-size:1.6rem;color:#fff;box-shadow:0 6px 28px rgba(0,204,0,.28);}
.sell-hero h1{font-size:1.45rem;font-weight:700;color:#fff;margin-bottom:.35rem;}
.sell-hero p{color:var(--kx-muted);font-size:.88rem;margin:0;}

.kx-steps{display:flex;margin-bottom:1.75rem;}
.kx-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative;padding:0 .5rem;}
.kx-step:not(:last-child)::after{content:'';position:absolute;top:19px;left:calc(50% + 22px);right:calc(-50% + 22px);height:2px;background:var(--kx-border);z-index:0;}
.kx-step.done:not(:last-child)::after{background:rgba(0,204,0,.4);}
.kx-step.active:not(:last-child)::after{background:rgba(0,204,0,.2);}
.kx-step-num{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700;background:var(--kx-card2);border:2px solid var(--kx-border);color:var(--kx-muted);position:relative;z-index:1;transition:all .3s;}
.kx-step.done .kx-step-num{background:rgba(0,204,0,.15);border-color:#00cc00;color:#00cc00;}
.kx-step.active .kx-step-num{background:linear-gradient(135deg,#00cc00,#007a0c);border-color:#00cc00;color:#fff;box-shadow:0 3px 14px rgba(0,204,0,.35);}
.kx-step-lbl{font-size:.68rem;color:var(--kx-muted);margin-top:.45rem;text-align:center;}
.kx-step.active .kx-step-lbl{color:var(--kx-green);font-weight:600;}
.kx-step.done .kx-step-lbl{color:rgba(0,204,0,.7);}

.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;overflow:hidden;margin-bottom:1.25rem;}
.kx-card-hd{padding:1.1rem 1.4rem;border-bottom:1px solid var(--kx-border);display:flex;align-items:center;gap:.7rem;}
.kx-card-hd .hico{width:36px;height:36px;border-radius:9px;background:rgba(0,204,0,.1);display:flex;align-items:center;justify-content:center;color:var(--kx-green);font-size:1rem;flex-shrink:0;}
.kx-card-hd h5{font-size:.95rem;font-weight:600;color:#fff;margin:0;}
.kx-card-hd p{font-size:.76rem;color:var(--kx-muted);margin:0;}
.kx-card-bd{padding:1.4rem;}

.kx-amount-bar{display:flex;gap:.75rem;margin-bottom:1.25rem;}
.kx-amount-pill{flex:1;background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:.7rem .9rem;text-align:center;}
.kx-amount-pill .lbl{font-size:.7rem;color:var(--kx-muted);margin-bottom:.15rem;}
.kx-amount-pill .val{font-size:.95rem;font-weight:700;color:#fff;}
.kx-amount-pill.highlight .val{color:#00cc00;}

.kx-method{display:flex;flex-direction:column;gap:.75rem;}
.kx-method-card{background:var(--kx-card2);border:2px solid var(--kx-border);border-radius:14px;cursor:pointer;transition:all .22s ease;overflow:hidden;}
.kx-method-card:hover{border-color:rgba(0,204,0,.3);}
.kx-method-card.selected{border-color:var(--kx-green);background:rgba(0,204,0,.04);}
.kx-method-top{display:flex;align-items:center;gap:.85rem;padding:1rem 1.1rem;}
.kx-method-radio{width:20px;height:20px;border-radius:50%;border:2px solid var(--kx-border);background:var(--kx-card);flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .2s;}
.kx-method-card.selected .kx-method-radio{border-color:#00cc00;background:#00cc00;}
.kx-method-radio-dot{width:8px;height:8px;border-radius:50%;background:#fff;display:none;}
.kx-method-card.selected .kx-method-radio-dot{display:block;}
.kx-method-icon{width:38px;height:38px;border-radius:10px;background:rgba(0,204,0,.1);display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--kx-green);flex-shrink:0;}
.kx-method-info .name{font-size:.92rem;font-weight:600;color:#fff;line-height:1.2;}
.kx-method-info .sub{font-size:.76rem;color:var(--kx-muted);}
.kx-method-body{padding:0 1.1rem 1rem;display:none;}
.kx-method-card.selected .kx-method-body{display:block;}
.kx-method-divider{height:1px;background:var(--kx-border);margin-bottom:.9rem;}

.kx-label{font-size:.8rem;font-weight:600;color:var(--kx-muted);margin-bottom:.4rem;display:block;text-transform:uppercase;letter-spacing:.03em;}
.kx-input{width:100%;background:var(--kx-dark);border:1.5px solid var(--kx-border);border-radius:10px;color:var(--kx-text);font-size:.9rem;padding:.65rem .9rem;outline:none;transition:border-color .2s;}
.kx-input:focus{border-color:rgba(0,204,0,.4);}
.kx-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='%237a8599' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .75rem center;background-size:14px;}

.kx-validate-row{display:flex;align-items:center;gap:.6rem;margin-top:.4rem;min-height:20px;}
.kx-validate-text{font-size:.78rem;}
.kx-validate-text.resolving{color:var(--kx-muted);}
.kx-validate-text.ok{color:#00cc00;}
.kx-validate-text.fail{color:#ff6b6b;}

.kx-balance-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.2);border-radius:8px;padding:.4rem .8rem;font-size:.82rem;color:#00cc00;font-weight:600;margin-bottom:.75rem;}

.kx-btn{width:100%;background:linear-gradient(135deg,#00cc00,#007a0c);border:none;border-radius:12px;color:#fff;font-size:1rem;font-weight:600;padding:.9rem;cursor:pointer;transition:all .22s ease;display:flex;align-items:center;justify-content:center;gap:.6rem;box-shadow:0 4px 20px rgba(0,204,0,.22);}
.kx-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,204,0,.35);}
.kx-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}

.kx-alert{display:flex;align-items:flex-start;gap:.7rem;padding:.9rem 1.1rem;border-radius:10px;font-size:.85rem;margin-bottom:1.25rem;}
.kx-alert-err{background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.25);color:#ffb3b3;}

#kxToast{position:fixed;bottom:84px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--kx-card);border:1px solid var(--kx-border);color:var(--kx-text);border-radius:12px;padding:.7rem 1.4rem;font-size:.82rem;font-weight:500;z-index:9999;opacity:0;pointer-events:none;transition:all .3s ease;white-space:nowrap;box-shadow:0 8px 28px rgba(0,0,0,.4);}
#kxToast.show{opacity:1;transform:translateX(-50%) translateY(0);}
#kxToast.err{border-color:rgba(220,53,69,.4);color:#ffb3b3;}
#kxToast.ok{border-color:rgba(0,204,0,.4);color:#b3ffb3;}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-xl-6 col-lg-7">

    <div class="sell-hero">
        <div class="sell-hero-icon"><i class="bi bi-bank2"></i></div>
        <h1>Choose Your Payout Method</h1>
        <p>Select how you'd like to receive ₦{{ number_format($nairaAmount, 2) }}</p>
    </div>

    <div class="kx-steps">
        <div class="kx-step done"><div class="kx-step-num"><i class="bi bi-check-lg"></i></div><span class="kx-step-lbl">Amount</span></div>
        <div class="kx-step done"><div class="kx-step-num"><i class="bi bi-check-lg"></i></div><span class="kx-step-lbl">Send &amp; Proof</span></div>
        <div class="kx-step active"><div class="kx-step-num">3</div><span class="kx-step-lbl">Payout</span></div>
    </div>

    @if($errors->any())
    <div class="kx-alert kx-alert-err">
        <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
    @endif
    @if(session('error'))
    <div class="kx-alert kx-alert-err">
        <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:2px"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <div class="kx-amount-bar">
        <div class="kx-amount-pill">
            <div class="lbl">Crypto</div>
            <div class="val">{{ $coin }} · ${{ number_format($amountInUsd,2) }}</div>
        </div>
        <div class="kx-amount-pill highlight">
            <div class="lbl">You Receive</div>
            <div class="val">₦{{ number_format($nairaAmount,2) }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('sell.finalize') }}" id="finalizeForm">
        @csrf
        <input type="hidden" name="payout_method" id="payoutMethodInput">
        <input type="hidden" name="password" id="passwordFinal">

        <div class="kx-card">
            <div class="kx-card-hd">
                <div class="hico"><i class="bi bi-credit-card-2-back-fill"></i></div>
                <div><h5>Payout Method</h5><p>Choose where to receive your Naira</p></div>
            </div>
            <div class="kx-card-bd" style="padding:.85rem;">
                <div class="kx-method">

                    {{-- Default bank --}}
                    <div class="kx-method-card" id="card_default_bank" onclick="selectMethod('default_bank')">
                        <div class="kx-method-top">
                            <div class="kx-method-radio"><div class="kx-method-radio-dot"></div></div>
                            <div class="kx-method-icon"><i class="bi bi-bank"></i></div>
                            <div class="kx-method-info">
                                <div class="name">Default Bank Account</div>
                                <div class="sub">
                                    {{ $userData['bank_name'] ?? 'Saved bank' }}
                                    @if(!empty($userData['account_number']))· ****{{ substr($userData['account_number'],-4) }}@endif
                                </div>
                            </div>
                        </div>
                        <div class="kx-method-body">
                            <div class="kx-method-divider"></div>
                            <div style="background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.12);border-radius:10px;padding:.75rem;font-size:.82rem;margin-bottom:.9rem;">
                                <div style="color:var(--kx-muted);margin-bottom:.3rem;">Receiving account</div>
                                <div style="color:#fff;font-weight:600;">{{ $userData['bank_name'] ?? '—' }}</div>
                                <div style="color:var(--kx-muted);">{{ $userData['account_number'] ?? '—' }} &nbsp;·&nbsp; {{ $userData['account_name'] ?? '' }}</div>
                            </div>
                            <label class="kx-label">Confirm Password</label>
                            <input type="password" class="kx-input password-input" id="pass_default" placeholder="Your account password">
                        </div>
                    </div>

                    {{-- External bank --}}
                    <div class="kx-method-card" id="card_external_bank" onclick="selectMethod('external_bank')">
                        <div class="kx-method-top">
                            <div class="kx-method-radio"><div class="kx-method-radio-dot"></div></div>
                            <div class="kx-method-icon"><i class="bi bi-building"></i></div>
                            <div class="kx-method-info">
                                <div class="name">Different Bank Account</div>
                                <div class="sub">Send to any Nigerian bank</div>
                            </div>
                        </div>
                        <div class="kx-method-body">
                            <div class="kx-method-divider"></div>
                            <div class="mb-3">
                                <label class="kx-label">Select Bank</label>
                                <div id="bankLoadingMsg" style="font-size:.8rem;color:var(--kx-muted);display:none">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Loading banks…
                                </div>
                                <select class="kx-input kx-select" id="bankSelect" onchange="onBankChange()" style="display:none">
                                    <option value="">— Choose a bank —</option>
                                </select>
                                <div id="bankLoadErr" style="font-size:.78rem;color:#ff6b6b;display:none">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i><span class="err-msg">Could not load banks.</span> <a href="#" onclick="loadBanks();return false" style="color:#ffb3b3">Retry</a>
                                </div>
                                <input type="hidden" name="alt_bank_code" id="altBankCode">
                                <input type="hidden" name="alt_bank_name" id="altBankName">
                            </div>
                            <div class="mb-3">
                                <label class="kx-label">Account Number</label>
                                <input type="text" class="kx-input" id="altAccNumber" name="alt_account_number"
                                       placeholder="10-digit account number" maxlength="10" inputmode="numeric">
                                <div class="kx-validate-row">
                                    <span class="kx-validate-text" id="validateStatus"></span>
                                </div>
                                <input type="hidden" name="alt_account_name" id="altAccountName">
                            </div>
                            <div class="mb-3">
                                <label class="kx-label">Confirm Password</label>
                                <input type="password" class="kx-input password-input" id="pass_external" placeholder="Your account password">
                            </div>
                        </div>
                    </div>

                    {{-- Wallet balance --}}
                    <div class="kx-method-card" id="card_wallet_balance" onclick="selectMethod('wallet_balance')">
                        <div class="kx-method-top">
                            <div class="kx-method-radio"><div class="kx-method-radio-dot"></div></div>
                            <div class="kx-method-icon"><i class="bi bi-wallet-fill"></i></div>
                            <div class="kx-method-info">
                                <div class="name">KayXchange Wallet</div>
                                <div class="sub">Credit your platform wallet</div>
                            </div>
                        </div>
                        <div class="kx-method-body">
                            <div class="kx-method-divider"></div>
                            <div class="kx-balance-badge"><i class="bi bi-wallet2"></i> Balance: ₦{{ number_format($balance, 2) }}</div>
                            <label class="kx-label">Confirm Password</label>
                            <input type="password" class="kx-input password-input" id="pass_wallet" placeholder="Your account password">
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <button type="submit" class="kx-btn" id="submitBtn" disabled>
            <i class="bi bi-check2-circle"></i>
            <span>Complete Trade</span>
        </button>
    </form>

</div>
</div>
<div id="kxToast"></div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let selectedMethod = null;
let bankValidated  = false;
let banksLoaded    = false;

/* ── Lazy bank list load ── */
function loadBanks() {
    const loadMsg = document.getElementById('bankLoadingMsg');
    const loadErr = document.getElementById('bankLoadErr');
    const sel     = document.getElementById('bankSelect');
    loadMsg.style.display = 'block';
    loadErr.style.display = 'none';
    sel.style.display = 'none';

    $.get('{{ route('sell.fetchBanks') }}', function(res) {
        const banks = res.banks || [];
        if (!banks.length) {
            loadMsg.style.display = 'none';
            loadErr.querySelector('span.err-msg') && (loadErr.querySelector('span.err-msg').textContent = res.error || 'Could not load banks.');
            loadErr.style.display = 'block';
            return;
        }
        sel.innerHTML = '<option value="">— Choose a bank —</option>';
        banks.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.code;
            opt.dataset.name = b.name;
            opt.textContent = b.name;
            sel.appendChild(opt);
        });
        loadMsg.style.display = 'none';
        sel.style.display = 'block';
        banksLoaded = true;
    }).fail(function(xhr) {
        loadMsg.style.display = 'none';
        const errMsg = xhr.responseJSON?.error || 'Could not load banks.';
        const msgEl = loadErr.querySelector('span.err-msg');
        if (msgEl) msgEl.textContent = errMsg;
        loadErr.style.display = 'block';
    });
}

function selectMethod(method) {
    if (selectedMethod) document.getElementById('card_'+selectedMethod).classList.remove('selected');
    selectedMethod = method;
    document.getElementById('card_'+method).classList.add('selected');
    document.getElementById('payoutMethodInput').value = method;
    // Lazy-load banks the first time External Bank is selected
    if (method === 'external_bank' && !banksLoaded) {
        loadBanks();
    }
    checkSubmit();
}

document.querySelectorAll('.password-input').forEach(inp => {
    inp.addEventListener('input', () => checkSubmit());
});

function getPassword() {
    if (!selectedMethod) return '';
    const map = {default_bank:'pass_default', external_bank:'pass_external', wallet_balance:'pass_wallet'};
    return document.getElementById(map[selectedMethod])?.value || '';
}

function checkSubmit() {
    const pw = getPassword();
    let ok = selectedMethod && pw.length >= 6;
    if (selectedMethod === 'external_bank') ok = ok && bankValidated;
    document.getElementById('submitBtn').disabled = !ok;
}

function onBankChange() {
    const sel = document.getElementById('bankSelect');
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('altBankCode').value = sel.value;
    document.getElementById('altBankName').value = opt?.dataset.name || '';
    bankValidated = false;
    document.getElementById('altAccountName').value = '';
    document.getElementById('validateStatus').textContent = '';
    checkSubmit();
    if (document.getElementById('altAccNumber').value.length === 10) doValidate();
}

document.getElementById('altAccNumber').addEventListener('input', function() {
    if (this.value.length === 10) doValidate();
    else {
        bankValidated = false;
        document.getElementById('validateStatus').className = 'kx-validate-text';
        document.getElementById('validateStatus').textContent = '';
        checkSubmit();
    }
});

function doValidate() {
    const bankCode = document.getElementById('bankSelect').value;
    const bankName = document.getElementById('bankSelect').options[document.getElementById('bankSelect').selectedIndex]?.dataset.name || '';
    const accNo    = document.getElementById('altAccNumber').value;
    const status   = document.getElementById('validateStatus');
    if (!bankCode) { showToast('Please select a bank first.','err'); return; }
    status.className = 'kx-validate-text resolving';
    status.textContent = 'Verifying account…';
    bankValidated = false;
    document.getElementById('altAccountName').value = '';
    checkSubmit();

    $.ajax({
        url: '{{ route('sell.validateBank') }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', bank_name: bankCode, alt_bank_name: bankName, account_number: accNo },
        success: function(r) {
            const name = r.account_name || (r.data && r.data.account_name) || '';
            if (name) {
                document.getElementById('altAccountName').value = name;
                document.getElementById('altBankName').value = bankName;
                status.className = 'kx-validate-text ok';
                status.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>' + name;
                bankValidated = true;
            } else {
                status.className = 'kx-validate-text fail';
                status.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>Account not found';
            }
            checkSubmit();
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Could not verify account';
            status.className = 'kx-validate-text fail';
            status.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>' + msg;
            checkSubmit();
        }
    });
}

document.getElementById('finalizeForm').addEventListener('submit', function() {
    document.getElementById('passwordFinal').value = getPassword();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing…';
    // Safety net: re-enable button after 45 s if no page navigation occurs
    setTimeout(function() {
        if (btn.disabled) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i><span>Complete Trade</span>';
            showToast('Request timed out. Please try again.', 'err');
        }
    }, 45000);
});

function showToast(msg, type='err') {
    const t = document.getElementById('kxToast');
    t.textContent = msg; t.className = `show ${type}`;
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 3500);
}

// Reset spinner if user presses Back (browser restores from cache)
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        const btn = document.getElementById('submitBtn');
        if (btn && btn.disabled) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i><span>Complete Trade</span>';
        }
    }
});
</script>
@endpush

