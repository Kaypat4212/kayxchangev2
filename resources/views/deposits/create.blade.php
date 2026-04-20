@extends('layout')

@section('title', 'Make a Deposit')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-green-dim:rgba(0,204,0,0.10);--kx-green-glow:rgba(0,204,0,0.22);
    --kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
}
body{ background:var(--kx-dark); color:var(--kx-text); }

/* Hero */
.dep-hero{
    background:linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);
    border-bottom:1px solid var(--kx-border);
    padding:2rem 1rem 1.5rem; text-align:center; margin-bottom:2rem;
    position:relative; overflow:hidden;
}
.dep-hero::before{
    content:''; position:absolute; top:-80px; right:-80px;
    width:260px; height:260px;
    background:radial-gradient(circle,var(--kx-green-glow),transparent 70%);
    pointer-events:none;
}
.dep-hero-icon{
    width:60px; height:60px; border-radius:50%;
    background:var(--kx-green-dim); border:1px solid rgba(0,204,0,0.22);
    display:flex; align-items:center; justify-content:center;
    font-size:1.6rem; color:var(--kx-green); margin:0 auto 1rem;
}
.dep-hero h1{ font-size:1.5rem; font-weight:700; color:#fff; margin:0 0 .3rem; }
.dep-hero p{ color:var(--kx-muted); font-size:.85rem; margin:0; }

/* Layout */
.dep-wrap{ max-width:640px; margin:0 auto; padding:0 1rem 3rem; }

/* Steps indicator */
.dep-steps{ display:flex; gap:0; margin-bottom:1.75rem; }
.dep-step{
    flex:1; padding:.55rem .75rem; font-size:.78rem; font-weight:600;
    display:flex; align-items:center; gap:.45rem; color:var(--kx-muted);
    border-bottom:2px solid var(--kx-border); transition:all .25s;
}
.dep-step.active{ color:var(--kx-green); border-bottom-color:var(--kx-green); }
.dep-step-num{
    width:22px; height:22px; border-radius:50%; font-size:.7rem; font-weight:700;
    display:flex; align-items:center; justify-content:center;
    background:var(--kx-border); color:var(--kx-muted); flex-shrink:0; transition:all .25s;
}
.dep-step.active .dep-step-num{ background:var(--kx-green-dim); color:var(--kx-green); border:1px solid rgba(0,204,0,0.3); }

/* Card */
.dep-card{
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:16px; padding:1.75rem; margin-bottom:1.25rem;
}
.dep-card-title{
    font-size:.8rem; font-weight:600; color:var(--kx-muted);
    text-transform:uppercase; letter-spacing:.05em; margin-bottom:1.25rem;
    display:flex; align-items:center; gap:.45rem;
}
.dep-card-title i{ color:var(--kx-green); }

/* Form inputs */
.kx-label{ font-size:.82rem; color:var(--kx-muted); margin-bottom:.4rem; font-weight:500; }
.kx-input,.kx-select{
    background:var(--kx-card2); border:1px solid var(--kx-border);
    color:var(--kx-text); border-radius:10px; padding:.7rem 1rem;
    width:100%; font-size:.9rem; transition:border-color .2s,box-shadow .2s;
    font-family:'Poppins',sans-serif;
}
.kx-input:focus,.kx-select:focus{
    outline:none; border-color:rgba(0,204,0,.5);
    box-shadow:0 0 0 3px rgba(0,204,0,.08);
    background:var(--kx-card2); color:var(--kx-text);
}
.kx-select option{ background:#1e2535; color:var(--kx-text); }
.kx-input::placeholder{ color:var(--kx-muted); }
.kx-error{ font-size:.78rem; color:#ef4444; margin-top:.3rem; }

/* Amount prefix */
.kx-input-wrap{ position:relative; }
.kx-currency-prefix{
    position:absolute; left:1rem; top:50%; transform:translateY(-50%);
    color:var(--kx-green); font-weight:700; font-size:.95rem; pointer-events:none;
}
.kx-input.has-prefix{ padding-left:2.4rem; }

/* File upload */
.kx-file-label{
    display:flex; align-items:center; gap:.75rem;
    background:var(--kx-card2); border:1px dashed rgba(0,204,0,0.25);
    border-radius:10px; padding:.9rem 1rem; cursor:pointer;
    transition:border-color .2s,background .2s; color:var(--kx-muted);
}
.kx-file-label:hover{ border-color:rgba(0,204,0,.5); background:rgba(0,204,0,.03); }
.kx-file-label .kx-file-icon{ width:36px; height:36px; border-radius:8px; background:var(--kx-green-dim); display:flex; align-items:center; justify-content:center; color:var(--kx-green); flex-shrink:0; }
.kx-file-name{ font-size:.82rem; }
#proof_of_payment{ display:none; }

/* Account details info box */
.kx-acct-box{
    background:rgba(0,204,0,.05); border:1px solid rgba(0,204,0,.15);
    border-radius:12px; padding:1rem 1.25rem; display:none;
}
.kx-acct-row{ display:flex; justify-content:space-between; align-items:center; padding:.35rem 0; font-size:.85rem; }
.kx-acct-row:not(:last-child){ border-bottom:1px solid rgba(0,204,0,.08); }
.kx-acct-key{ color:var(--kx-muted); }
.kx-acct-val{ color:#fff; font-weight:600; display:flex; align-items:center; gap:.5rem; }
.kx-copy-btn{
    background:none; border:1px solid rgba(0,204,0,.25); color:var(--kx-green);
    border-radius:6px; padding:.15rem .45rem; font-size:.75rem; cursor:pointer;
    transition:background .2s;
}
.kx-copy-btn:hover{ background:var(--kx-green-dim); }

/* Alert banner */
.kx-alert{
    background:rgba(245,158,11,.06); border:1px solid rgba(245,158,11,.2);
    border-radius:12px; padding:1rem 1.25rem; font-size:.82rem;
    color:#fbbf24; display:flex; gap:.65rem; align-items:flex-start;
}

/* Submit button */
.kx-submit{
    width:100%; padding:.85rem; border:none; border-radius:12px; font-size:.95rem;
    font-weight:700; cursor:pointer; transition:all .2s;
    background:var(--kx-green); color:#fff;
    display:flex; align-items:center; justify-content:center; gap:.6rem;
}
.kx-submit:hover{ background:#00e600; transform:translateY(-1px); box-shadow:0 6px 20px rgba(0,204,0,0.3); }
.kx-submit:disabled{ background:#2a4a2a; color:#4a6a4a; cursor:not-allowed; transform:none; box-shadow:none; }
.kx-spinner{ width:18px; height:18px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; display:none; }
@keyframes spin{ to{ transform:rotate(360deg); }}

/* Empty state */
.kx-empty{
    text-align:center; padding:3rem 2rem; background:var(--kx-card);
    border:1px solid var(--kx-border); border-radius:16px;
}
.kx-empty i{ font-size:2.5rem; color:var(--kx-muted); margin-bottom:1rem; display:block; }

/* Payment Method Cards */
.kx-method-grid{ display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
.kx-method-card{
    background:var(--kx-card2); border:2px solid var(--kx-border);
    border-radius:12px; padding:.85rem .75rem; cursor:pointer;
    text-align:center; transition:all .2s;
}
.kx-method-card:hover{ border-color:rgba(0,204,0,.35); background:rgba(0,204,0,.03); }
.kx-method-card.selected{ border-color:var(--kx-green); background:rgba(0,204,0,.07); }
.kx-method-icon{
    width:44px; height:44px; border-radius:10px; border:1.5px solid var(--kx-border);
    display:flex; align-items:center; justify-content:center; margin:0 auto .55rem;
}
.kx-method-label{ font-size:.82rem; font-weight:700; color:var(--kx-text); }
.kx-method-sub{ font-size:.7rem; color:var(--kx-muted); margin-top:.1rem; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="dep-hero">
    <div class="dep-hero-icon"><i class="bi bi-bank"></i></div>
    <h1>Make a Deposit</h1>
    <p>Fund your KayXchange wallet securely</p>
</div>

<div class="dep-wrap">

    {{-- Steps --}}
    <div class="dep-steps">
        <div class="dep-step active" id="step1-indicator">
            <div class="dep-step-num">1</div> Enter Amount
        </div>
        <div class="dep-step" id="step2-indicator">
            <div class="dep-step-num">2</div> Payment Details
        </div>
        <div class="dep-step" id="step3-indicator">
            <div class="dep-step-num">3</div> Upload Proof
        </div>
    </div>

    <form id="deposit-form" action="{{ route('deposits.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Card 1: Amount --}}
        <div class="dep-card">
            <div class="dep-card-title"><i class="bi bi-cash-stack"></i> Deposit Amount</div>

            <div class="mb-3">
                <div class="kx-label">Amount (NGN) <span style="color:#ef4444;">*</span></div>
                <div class="kx-input-wrap">
                    <span class="kx-currency-prefix">&#8358;</span>
                    <input type="number" name="amount" id="amount" min="1000" required
                           class="kx-input has-prefix" placeholder="Minimum &#8358;1,000"
                           value="{{ old('amount') }}">
                </div>
                @error('amount')<div class="kx-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
            </div>

            <div class="mb-0">
                <div class="kx-label">Payment Method <span style="color:#ef4444;">*</span></div>

                {{-- Hidden input that holds the selected payment method --}}
                <input type="hidden" name="payment_method" id="payment_method" value="{{ old('payment_method') }}">

                <div class="kx-method-grid" id="method-grid">
                    {{-- Bank Transfer --}}
                    @if($enabledMethods['bank_transfer'])
                    <div class="kx-method-card {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}" data-value="bank_transfer" onclick="selectMethod('bank_transfer')">
                        <div class="kx-method-icon" style="background:rgba(59,130,246,.12);border-color:rgba(59,130,246,.3)">
                            <i class="bi bi-bank" style="color:#3b82f6;font-size:1.3rem;"></i>
                        </div>
                        <div class="kx-method-label">Bank Transfer</div>
                        <div class="kx-method-sub">Manual + proof upload</div>
                    </div>
                    @endif
                    {{-- Crypto Transfer --}}
                    @if($enabledMethods['crypto_transfer'])
                    <div class="kx-method-card {{ old('payment_method') === 'crypto_transfer' ? 'selected' : '' }}" data-value="crypto_transfer" onclick="selectMethod('crypto_transfer')">
                        <div class="kx-method-icon" style="background:rgba(168,85,247,.12);border-color:rgba(168,85,247,.3)">
                            <i class="bi bi-currency-bitcoin" style="color:#a855f7;font-size:1.3rem;"></i>
                        </div>
                        <div class="kx-method-label">Crypto Transfer</div>
                        <div class="kx-method-sub">Send to company wallet + proof</div>
                    </div>
                    @endif
                    {{-- Paystack --}}
                    @if($enabledMethods['paystack'])
                    <div class="kx-method-card {{ old('payment_method') === 'paystack' ? 'selected' : '' }}" data-value="paystack" onclick="selectMethod('paystack')">
                        <div class="kx-method-icon" style="background:rgba(0,163,94,.12);border-color:rgba(0,163,94,.3)">
                            <img src="https://cdn.paystack.com/assets/img/favicons/apple-touch-icon.png" alt="Paystack" style="width:28px;height:28px;border-radius:6px;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <i class="bi bi-credit-card-2-front-fill" style="display:none;color:#00a35e;font-size:1.3rem;"></i>
                        </div>
                        <div class="kx-method-label">Paystack</div>
                        <div class="kx-method-sub">Card · Transfer · USSD</div>
                    </div>
                    @endif
                    {{-- Korapay --}}
                    @if($enabledMethods['korapay'])
                    <div class="kx-method-card {{ old('payment_method') === 'korapay' ? 'selected' : '' }}" data-value="korapay" onclick="selectMethod('korapay')">
                        <div class="kx-method-icon" style="background:rgba(99,56,225,.12);border-color:rgba(99,56,225,.3)">
                            <i class="bi bi-lightning-charge-fill" style="color:#6338e1;font-size:1.3rem;"></i>
                        </div>
                        <div class="kx-method-label">Korapay</div>
                        <div class="kx-method-sub">Card · Transfer · Bank</div>
                    </div>
                    @endif
                    {{-- Flutterwave --}}
                    @if($enabledMethods['flutterwave'])
                    <div class="kx-method-card {{ old('payment_method') === 'flutterwave' ? 'selected' : '' }}" data-value="flutterwave" onclick="selectMethod('flutterwave')">
                        <div class="kx-method-icon" style="background:rgba(245,91,20,.12);border-color:rgba(245,91,20,.3)">
                            <i class="bi bi-fire" style="color:#f55b14;font-size:1.3rem;"></i>
                        </div>
                        <div class="kx-method-label">Flutterwave</div>
                        <div class="kx-method-sub">Card · Transfer · Mobile</div>
                    </div>
                    @endif

                    {{-- No methods available --}}
                    @if(!array_filter($enabledMethods))
                    <div class="col-span-2" style="grid-column:1/-1;text-align:center;padding:1.5rem;color:var(--kx-muted);font-size:.85rem;">
                        <i class="bi bi-x-circle" style="font-size:1.5rem;display:block;margin-bottom:.5rem;"></i>
                        No payment methods are currently available. Please contact support.
                    </div>
                    @endif
                </div>

                @error('payment_method')<div class="kx-error mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                @error('amount')<div class="kx-error mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Auto-pay info banner (shown for gateway methods) --}}
        <div class="dep-card d-none" id="auto-pay-banner" style="background:linear-gradient(135deg,rgba(0,204,0,.06),rgba(0,204,0,.02));border-color:rgba(0,204,0,.2);">
            <div style="display:flex;align-items:flex-start;gap:.75rem;">
                <i class="bi bi-shield-check" style="color:var(--kx-green);font-size:1.4rem;flex-shrink:0;margin-top:.1rem;"></i>
                <div>
                    <div style="font-weight:700;font-size:.9rem;margin-bottom:.25rem;">Automatic Deposit</div>
                    <div style="font-size:.82rem;color:var(--kx-muted);">You'll be redirected to a secure payment page. Once payment is confirmed, your wallet will be credited <strong style="color:var(--kx-green);">instantly</strong> — no proof upload needed.</div>
                </div>
            </div>
        </div>

        {{-- Card 2: Payment info (shown after method selected) --}}
        <div class="dep-card d-none" id="company-account-section">
            <div class="dep-card-title" id="account-section-title"><i class="bi bi-building"></i> Select Payment Account</div>

            <div class="mb-3">
                <div class="kx-label" id="payment-destination-label">Company Bank Account <span style="color:#ef4444;">*</span></div>
                <select name="company_account_id" id="company_account_id" class="kx-select">
                    <option value="" disabled selected>Choose account to transfer to</option>
                    @foreach($companyAccounts as $account)
                        <option value="{{ $account->id }}"
                                data-bank-name="{{ $account->bank_name }}"
                                data-account-number="{{ $account->account_number }}"
                                data-account-name="{{ $account->account_name }}"
                                {{ old('company_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->account_name }} — {{ $account->bank_name }}
                        </option>
                    @endforeach
                </select>
                @error('company_account_id')<div class="kx-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror

                <select name="crypto_wallet_key" id="crypto_wallet_key" class="kx-select d-none">
                    <option value="" disabled selected>Choose wallet to transfer to</option>
                    @foreach(($cryptoWallets ?? []) as $wallet)
                        <option value="{{ $wallet['key'] }}"
                                data-network="{{ $wallet['network'] }}"
                                data-wallet-name="{{ $wallet['name'] }}"
                                data-wallet-address="{{ $wallet['address'] }}"
                                {{ old('crypto_wallet_key') === $wallet['key'] ? 'selected' : '' }}>
                            {{ $wallet['name'] }} — {{ $wallet['network'] }}
                        </option>
                    @endforeach
                </select>
                @error('crypto_wallet_key')<div class="kx-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
            </div>

            {{-- Inline account details box --}}
            <div class="kx-acct-box" id="account-details-box">
                <div class="kx-acct-row">
                    <span class="kx-acct-key" id="acct-key-bank">Bank Name</span>
                    <span class="kx-acct-val" id="disp-bank-name">—</span>
                </div>
                <div class="kx-acct-row">
                    <span class="kx-acct-key" id="acct-key-name">Account Name</span>
                    <span class="kx-acct-val" id="disp-account-name">—</span>
                </div>
                <div class="kx-acct-row">
                    <span class="kx-acct-key" id="acct-key-number">Account Number</span>
                    <span class="kx-acct-val">
                        <span id="disp-account-number">—</span>
                        <button type="button" class="kx-copy-btn" id="copy-acct-btn" title="Copy">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </span>
                </div>
            </div>

            <div class="kx-alert mt-3">
                <i class="bi bi-info-circle" style="flex-shrink:0;margin-top:.05rem;"></i>
                <span id="payment-instruction-text">Please transfer the exact amount to this account, then upload your proof of payment below.</span>
            </div>
        </div>

        {{-- Card 3: Proof of payment (bank transfer only) --}}
        <div class="dep-card" id="proof-section">
            <div class="dep-card-title" id="proof-title"><i class="bi bi-image"></i> Proof of Payment</div>

            <div class="mb-1">
                <div class="kx-label" id="proof-label">Upload Screenshot / Receipt <span style="color:#ef4444;">*</span></div>
                <label class="kx-file-label" for="proof_of_payment" id="file-label">
                    <div class="kx-file-icon"><i class="bi bi-cloud-upload"></i></div>
                    <div>
                        <div class="kx-file-name" id="file-name-display">Click to choose a file</div>
                        <div style="font-size:.75rem;margin-top:.2rem;">JPEG, PNG, WEBP — max 10 MB</div>
                    </div>
                </label>
                <input type="file" name="proof_of_payment" id="proof_of_payment" accept="image/*" required>
                @error('proof_of_payment')<div class="kx-error mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
            </div>

            {{-- Preview --}}
            <div id="img-preview-wrap" style="display:none;margin-top:.75rem;background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;overflow:hidden;text-align:center;padding:.5rem;">
                <img id="img-preview" alt="Proof preview" style="max-width:100%;max-height:220px;border-radius:8px;object-fit:contain;display:block;margin:0 auto;">
                <div id="img-preview-name" style="font-size:.75rem;color:var(--kx-muted);margin-top:.4rem;"></div>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="kx-submit" id="submit-btn">
            <span id="submit-text"><i class="bi bi-check-circle me-1"></i> <span id="submit-label">Select a Payment Method</span></span>
            <div class="kx-spinner" id="submit-spinner"></div>
        </button>

    </form>

    {{-- Session toasts --}}
    @if(session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
        <div class="toast show align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
        <div class="toast show align-items-center text-white bg-danger border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">{{ session('error') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Validation error toast --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
        <div id="err-toast" class="toast align-items-center text-white bg-danger border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="err-toast-msg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        <div id="ok-toast" class="toast align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">Copied to clipboard!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const form        = document.getElementById('deposit-form');
    const pmInput     = document.getElementById('payment_method');
    const acctSec     = document.getElementById('company-account-section');
    const proofSec    = document.getElementById('proof-section');
    const autoBanner  = document.getElementById('auto-pay-banner');
    const acctSel     = document.getElementById('company_account_id');
    const walletSel   = document.getElementById('crypto_wallet_key');
    const acctBox     = document.getElementById('account-details-box');
    const dispBank    = document.getElementById('disp-bank-name');
    const dispName    = document.getElementById('disp-account-name');
    const dispNum     = document.getElementById('disp-account-number');
    const copyBtn     = document.getElementById('copy-acct-btn');
    const proofInp    = document.getElementById('proof_of_payment');
    const fileDisp    = document.getElementById('file-name-display');
    const preview     = document.getElementById('img-preview');
    const previewW    = document.getElementById('img-preview-wrap');
    const submitBtn   = document.getElementById('submit-btn');
    const spinner     = document.getElementById('submit-spinner');
    const submitTxt   = document.getElementById('submit-text');
    const submitLabel = document.getElementById('submit-label');
    const step2       = document.getElementById('step2-indicator');
    const step3       = document.getElementById('step3-indicator');
    const accountTitle = document.getElementById('account-section-title');
    const destinationLabel = document.getElementById('payment-destination-label');
    const acctKeyBank = document.getElementById('acct-key-bank');
    const acctKeyName = document.getElementById('acct-key-name');
    const acctKeyNumber = document.getElementById('acct-key-number');
    const paymentInstruction = document.getElementById('payment-instruction-text');
    const proofTitle = document.getElementById('proof-title');
    const proofLabel = document.getElementById('proof-label');

    const bankTransferAction = form.action; // current action = deposits.store
    const gatewayAction      = '{{ route("deposits.initiate") }}';
    const gatewayMethods     = ['paystack', 'korapay', 'flutterwave'];
    const manualMethods      = ['bank_transfer', 'crypto_transfer'];

    let acctNum = '';

    // ── Method card selection ──────────────────────────────────────
    window.selectMethod = function (value) {
        document.querySelectorAll('.kx-method-card').forEach(c => c.classList.remove('selected'));
        document.querySelector('.kx-method-card[data-value="' + value + '"]').classList.add('selected');
        pmInput.value = value;
        applyMethodUI(value);
    };

    function applyMethodUI(value) {
        if (value === 'bank_transfer') {
            acctSec.classList.remove('d-none');
            proofSec.classList.remove('d-none');
            autoBanner.classList.add('d-none');
            acctSel.required = true;
            walletSel.required = false;
            walletSel.classList.add('d-none');
            acctSel.classList.remove('d-none');
            proofInp.required = true;
            form.action = bankTransferAction;
            form.method = 'POST';
            submitLabel.textContent = 'I Have Made Payment';
            step2.classList.add('active');
            step3.classList.add('active');
            accountTitle.innerHTML = '<i class="bi bi-building"></i> Select Payment Account';
            destinationLabel.innerHTML = 'Company Bank Account <span style="color:#ef4444;">*</span>';
            acctKeyBank.textContent = 'Bank Name';
            acctKeyName.textContent = 'Account Name';
            acctKeyNumber.textContent = 'Account Number';
            paymentInstruction.textContent = 'Please transfer the exact amount to this account, then upload your proof of payment below.';
            proofTitle.innerHTML = '<i class="bi bi-image"></i> Proof of Payment';
            proofLabel.innerHTML = 'Upload Screenshot / Receipt <span style="color:#ef4444;">*</span>';
            if (acctSel.options.length) acctSel.options[0].text = 'Choose account to transfer to';
            acctSel.dispatchEvent(new Event('change'));
        } else if (value === 'crypto_transfer') {
            acctSec.classList.remove('d-none');
            proofSec.classList.remove('d-none');
            autoBanner.classList.add('d-none');
            acctSel.required = false;
            walletSel.required = true;
            acctSel.classList.add('d-none');
            walletSel.classList.remove('d-none');
            proofInp.required = true;
            form.action = bankTransferAction;
            form.method = 'POST';
            submitLabel.textContent = 'I Have Sent Crypto';
            step2.classList.add('active');
            step3.classList.add('active');
            accountTitle.innerHTML = '<i class="bi bi-wallet2"></i> Select Receiving Wallet';
            destinationLabel.innerHTML = 'Company Wallet <span style="color:#ef4444;">*</span>';
            acctKeyBank.textContent = 'Network / Chain';
            acctKeyName.textContent = 'Wallet Name';
            acctKeyNumber.textContent = 'Wallet Address';
            paymentInstruction.textContent = 'Send the exact crypto amount to this wallet address and upload your transfer proof below (screenshot or receipt).';
            proofTitle.innerHTML = '<i class="bi bi-image"></i> Proof of Crypto Transfer';
            proofLabel.innerHTML = 'Upload Transfer Screenshot / Receipt <span style="color:#ef4444;">*</span>';
            if (walletSel.options.length) walletSel.options[0].text = 'Choose wallet to transfer to';
            walletSel.dispatchEvent(new Event('change'));
        } else if (gatewayMethods.includes(value)) {
            acctSec.classList.add('d-none');
            proofSec.classList.add('d-none');
            autoBanner.classList.remove('d-none');
            acctSel.required = false;
            walletSel.required = false;
            acctSel.classList.remove('d-none');
            walletSel.classList.add('d-none');
            proofInp.required = false;
            form.action = gatewayAction;
            form.method = 'POST';
            const labels = { paystack: 'Pay with Paystack', korapay: 'Pay with Korapay', flutterwave: 'Pay with Flutterwave' };
            submitLabel.textContent = labels[value] ?? 'Proceed to Payment';
            step2.classList.remove('active');
            step3.classList.remove('active');
        } else {
            acctSec.classList.add('d-none');
            proofSec.classList.add('d-none');
            autoBanner.classList.add('d-none');
            acctSel.required = false;
            walletSel.required = false;
            acctSel.classList.remove('d-none');
            walletSel.classList.add('d-none');
            proofInp.required = false;
            submitLabel.textContent = 'Select a Payment Method';
        }
    }

    // Restore if old() value was set (validation error redirect)
    if (pmInput.value) applyMethodUI(pmInput.value);

    // Show any server-side validation errors as a toast on page load
    @if($errors->any())
    window.addEventListener('DOMContentLoaded', function () {
        const msgs = @json($errors->all());
        document.getElementById('err-toast-msg').textContent = msgs.join(' · ');
        const t = new bootstrap.Toast(document.getElementById('err-toast'), { delay: 6000 });
        t.show();
    });
    @endif

    // ── Account / wallet details ───────────────────────────────────
    acctSel.addEventListener('change', function () {
        if (pmInput.value !== 'bank_transfer') return;
        const opt = this.options[this.selectedIndex];
        if (this.value) {
            dispBank.textContent = opt.dataset.bankName;
            dispName.textContent = opt.dataset.accountName;
            acctNum = opt.dataset.accountNumber;
            dispNum.textContent = acctNum;
            acctBox.style.display = 'block';
        } else {
            acctBox.style.display = 'none';
        }
    });

    walletSel.addEventListener('change', function () {
        if (pmInput.value !== 'crypto_transfer') return;
        const opt = this.options[this.selectedIndex];
        if (this.value) {
            dispBank.textContent = opt.dataset.network;
            dispName.textContent = opt.dataset.walletName;
            acctNum = opt.dataset.walletAddress;
            dispNum.textContent = acctNum;
            acctBox.style.display = 'block';
        } else {
            acctBox.style.display = 'none';
        }
    });

    // ── Copy account number ────────────────────────────────────────
    copyBtn.addEventListener('click', function () {
        if (!acctNum) return;
        navigator.clipboard.writeText(acctNum).then(function () {
            const t = new bootstrap.Toast(document.getElementById('ok-toast'));
            t.show();
        });
    });

    // ── File picker ────────────────────────────────────────────────
    proofInp.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) {
            fileDisp.textContent = 'Click to choose a file';
            previewW.style.display = 'none';
            preview.removeAttribute('src');
            return;
        }

        fileDisp.textContent = file.name;
        step3.classList.add('active');

        // Only preview actual images
        if (!file.type.startsWith('image/')) {
            previewW.style.display = 'none';
            preview.removeAttribute('src');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            // Set src BEFORE making the container visible to avoid broken-image flash
            preview.src = e.target.result;
            document.getElementById('img-preview-name').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            previewW.style.display = 'block';
        };
        reader.onerror = function () {
            previewW.style.display = 'none';
            preview.removeAttribute('src');
        };
        reader.readAsDataURL(file);
    });

    // ── Form submit ────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        const amount = parseFloat(document.getElementById('amount').value);
        const pm     = pmInput.value;
        const errors = [];

        if (!amount || amount < 1000) errors.push('Amount must be at least ₦1,000');
        if (!pm) errors.push('Please select a payment method');
        if (manualMethods.includes(pm)) {
            if (pm === 'bank_transfer' && !acctSel.value) errors.push('Please select a company account');
            if (pm === 'crypto_transfer' && !walletSel.value) errors.push('Please select a receiving wallet');
            if (!proofInp.files[0]) errors.push('Please upload proof of payment');
        }

        if (errors.length) {
            e.preventDefault();
            document.getElementById('err-toast-msg').textContent = errors.join(' · ');
            const t = new bootstrap.Toast(document.getElementById('err-toast'));
            t.show();
            return;
        }

        submitBtn.disabled = true;
        submitTxt.style.display = 'none';
        spinner.style.display = 'block';
    });
})();
</script>
@endpush
