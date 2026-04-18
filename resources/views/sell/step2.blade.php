@extends('selllayout')
@php
    $amountInUsd = $amountInUsd ?? session('sell.usd_amount', 0);
    $nairaAmount = $nairaAmount ?? session('sell.naira_amount', 0);
    $coin        = $coin        ?? session('sell.coin', '');
    $walletAddress = $walletAddress ?? '';
    $barcodeImages = [
        'BTC'  => asset('barcodes/btc-barcode.png'),
        'ETH'  => asset('barcodes/eth-barcode.png'),
        'USDT' => asset('barcodes/usdttron-barcode.png'),
    ];
    $barcode = $barcodeImages[$coin] ?? null;
    $coinColors = ['BTC'=>'#f7931a','ETH'=>'#627eea','USDT'=>'#26a17b'];
    $coinColor  = $coinColors[$coin] ?? '#00cc00';
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

/* Trade summary row */
.kx-trade-row{display:flex;align-items:center;justify-content:space-between;gap:.75rem;background:rgba(0,204,0,.04);border:1px solid rgba(0,204,0,.1);border-radius:12px;padding:1rem 1.25rem;margin-bottom:.75rem;}
.kx-trade-left{display:flex;align-items:center;gap:.75rem;}
.kx-trade-icon{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;flex-shrink:0;}
.kx-trade-label{font-size:.78rem;color:var(--kx-muted);}
.kx-trade-value{font-size:1rem;font-weight:700;color:#fff;}
.kx-trade-arrow{color:var(--kx-muted);font-size:1.1rem;}
.kx-trade-right .kx-trade-label{text-align:right;}
.kx-trade-right .kx-trade-value{text-align:right;color:#00cc00;}

/* Countdown */
.kx-timer{display:flex;align-items:center;gap:.75rem;background:rgba(255,193,7,.07);border:1px solid rgba(255,193,7,.2);border-radius:12px;padding:.85rem 1.1rem;}
.kx-timer-icon{font-size:1.25rem;color:#ffc107;}
.kx-timer-text{font-size:.82rem;color:var(--kx-muted);}
.kx-timer-count{font-size:1.05rem;font-weight:700;color:#ffc107;font-variant-numeric:tabular-nums;}
.kx-timer.danger{background:rgba(220,53,69,.07);border-color:rgba(220,53,69,.25);}
.kx-timer.danger .kx-timer-icon,.kx-timer.danger .kx-timer-count{color:#dc3545;}

/* Wallet address */
.kx-wallet-box{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;overflow:hidden;}
.kx-wallet-address{padding:.85rem 1rem;font-size:.82rem;font-family:monospace;color:var(--kx-text);word-break:break-all;line-height:1.6;}
.kx-wallet-copy-btn{width:100%;background:none;border:none;border-top:1px solid var(--kx-border);color:var(--kx-green);font-size:.82rem;font-weight:600;padding:.6rem;cursor:pointer;transition:background .2s;display:flex;align-items:center;justify-content:center;gap:.4rem;}
.kx-wallet-copy-btn:hover{background:rgba(0,204,0,.07);}
.kx-wallet-copy-btn.copied{color:#00cc00;}

/* Barcode */
.kx-barcode{text-align:center;background:#000;border-radius:12px;padding:1.25rem;margin-top:.85rem;}
.kx-barcode img{max-width:220px;height:auto;border-radius:10px;}
.kx-barcode p{font-size:.78rem;color:var(--kx-muted);margin-top:.6rem 0 0;}

/* File drop zone */
.kx-drop-zone{border:2px dashed rgba(0,204,0,.25);border-radius:12px;padding:1.75rem 1.5rem;text-align:center;cursor:pointer;transition:all .25s ease;background:rgba(0,204,0,.02);position:relative;}
.kx-drop-zone:hover,.kx-drop-zone.drag-over{border-color:var(--kx-green);background:rgba(0,204,0,.06);}
.kx-drop-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.kx-drop-icon{width:48px;height:48px;border-radius:50%;background:rgba(0,204,0,.1);display:flex;align-items:center;justify-content:center;margin:0 auto .65rem;font-size:1.3rem;color:var(--kx-green);transition:transform .25s;}
.kx-drop-zone:hover .kx-drop-icon{transform:scale(1.1);}
.kx-drop-title{font-size:.88rem;font-weight:600;color:#fff;margin-bottom:.2rem;}
.kx-drop-hint{font-size:.76rem;color:var(--kx-muted);}
.kx-preview{display:none;border-radius:10px;overflow:hidden;margin-top:.85rem;border:1px solid rgba(0,204,0,.2);}
.kx-preview img{width:100%;max-height:160px;object-fit:cover;display:block;}
.kx-preview-info{background:rgba(0,204,0,.07);padding:.55rem 1rem;display:flex;align-items:center;gap:.5rem;}
.kx-preview-info .fname{flex:1;font-size:.78rem;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.kx-preview-info .fsize{font-size:.73rem;color:var(--kx-muted);flex-shrink:0;}
.kx-preview-remove{background:none;border:none;color:#ff4d4d;font-size:1rem;cursor:pointer;padding:0;flex-shrink:0;}
.kx-preview.has-file{display:block;}

/* Submit button */
.kx-btn{width:100%;background:linear-gradient(135deg,#00cc00,#007a0c);border:none;border-radius:12px;color:#fff;font-size:1rem;font-weight:600;padding:.9rem;cursor:pointer;transition:all .22s ease;display:flex;align-items:center;justify-content:center;gap:.6rem;box-shadow:0 4px 20px rgba(0,204,0,.22);}
.kx-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,204,0,.35);}
.kx-btn:disabled{opacity:.5;cursor:not-allowed;}

/* Alert */
.kx-alert{display:flex;align-items:flex-start;gap:.7rem;padding:.9rem 1.1rem;border-radius:10px;font-size:.85rem;margin-bottom:1.25rem;}
.kx-alert-err{background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.25);color:#ffb3b3;}

/* Toast */
#kxToast{position:fixed;bottom:84px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--kx-card);border:1px solid var(--kx-border);color:var(--kx-text);border-radius:12px;padding:.7rem 1.4rem;font-size:.82rem;font-weight:500;z-index:9999;opacity:0;pointer-events:none;transition:all .3s ease;white-space:nowrap;box-shadow:0 8px 28px rgba(0,0,0,.4);}
#kxToast.show{opacity:1;transform:translateX(-50%) translateY(0);}
#kxToast.err{border-color:rgba(220,53,69,.4);color:#ffb3b3;}
#kxToast.ok{border-color:rgba(0,204,0,.4);color:#b3ffb3;}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-xl-6 col-lg-7">

    {{-- Hero --}}
    <div class="sell-hero">
        <div class="sell-hero-icon"><i class="bi bi-send-fill"></i></div>
        <h1>Send Crypto &amp; Upload Proof</h1>
        <p>Send the exact amount to the wallet below, then upload your transaction receipt.</p>
    </div>

    {{-- Progress --}}
    <div class="kx-steps">
        <div class="kx-step done"><div class="kx-step-num"><i class="bi bi-check-lg"></i></div><span class="kx-step-lbl">Amount</span></div>
        <div class="kx-step active"><div class="kx-step-num">2</div><span class="kx-step-lbl">Send &amp; Proof</span></div>
        <div class="kx-step"><div class="kx-step-num">3</div><span class="kx-step-lbl">Payout</span></div>
    </div>

    {{-- Errors --}}
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

    {{-- Trade summary --}}
    <div class="kx-card">
        <div class="kx-card-hd">
            <div class="hico"><i class="bi bi-receipt-cutoff"></i></div>
            <div><h5>Trade Summary</h5><p>Selling {{ $coin }} for Naira</p></div>
        </div>
        <div class="kx-card-bd">
            <div class="kx-trade-row">
                <div class="kx-trade-left">
                    <div class="kx-trade-icon" style="background:rgba({{ $coin=='BTC'?'247,147,26':($coin=='ETH'?'98,126,234':'38,161,123') }},.15);color:{{ $coinColor }}">
                        {{ $coin=='BTC'?'₿':($coin=='ETH'?'Ξ':'₮') }}
                    </div>
                    <div>
                        <div class="kx-trade-label">You Send</div>
                        <div class="kx-trade-value">{{ $coin }} · ${{ number_format($amountInUsd,2) }}</div>
                    </div>
                </div>
                <div class="kx-trade-arrow"><i class="bi bi-arrow-right"></i></div>
                <div class="kx-trade-right">
                    <div class="kx-trade-label">You Receive</div>
                    <div class="kx-trade-value">₦{{ number_format($nairaAmount,2) }}</div>
                </div>
            </div>

            {{-- Countdown --}}
            <div class="kx-timer" id="timerBox">
                <i class="bi bi-hourglass-split kx-timer-icon"></i>
                <div class="kx-timer-text">Time to send payment:</div>
                <div class="kx-timer-count" id="timerDisplay">50:00</div>
            </div>
        </div>
    </div>

    {{-- Wallet Address --}}
    <div class="kx-card">
        <div class="kx-card-hd">
            <div class="hico"><i class="bi bi-wallet2"></i></div>
            <div><h5>Wallet Address</h5><p>Send <strong style="color:#00cc00">${{ number_format($amountInUsd,2) }} of {{ $coin }}</strong> to this address</p></div>
        </div>
        <div class="kx-card-bd">
            <div class="kx-wallet-box">
                <div class="kx-wallet-address" id="walletAddr">{{ $walletAddress }}</div>
                <button type="button" class="kx-wallet-copy-btn" onclick="copyWallet()" id="copyBtn">
                    <i class="bi bi-clipboard" id="copyIcon"></i>
                    <span id="copyText">Copy Address</span>
                </button>
            </div>

            @if($barcode)
            <div class="kx-barcode mt-3">
                <img src="{{ $barcode }}" alt="{{ $coin }} QR Code">
                <p class="mt-2" style="font-size:.78rem;color:var(--kx-muted)">Scan QR to send {{ $coin }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Upload Proof --}}
    <form method="POST" action="{{ route('sell.postStep2') }}" enctype="multipart/form-data" id="proofForm">
        @csrf
        <div class="kx-card">
            <div class="kx-card-hd">
                <div class="hico"><i class="bi bi-image-fill"></i></div>
                <div><h5>Upload Payment Proof</h5><p>Screenshot or PDF of your sent transaction</p></div>
            </div>
            <div class="kx-card-bd">
                <div class="kx-drop-zone" id="dropZone">
                    <input type="file" name="proof" id="proofFile" accept=".jpg,.jpeg,.png,.pdf" required
                           onchange="handleFile(this)">
                    <div class="kx-drop-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                    <div class="kx-drop-title">Click or drag &amp; drop your proof</div>
                    <div class="kx-drop-hint">JPG, PNG, PDF — max 2MB</div>
                </div>
                <div class="kx-preview" id="filePreview">
                    <img id="previewImg" src="" alt="Preview" style="display:none">
                    <div class="kx-preview-info">
                        <i class="bi bi-file-earmark-check text-success" style="flex-shrink:0"></i>
                        <span class="fname" id="fileName">—</span>
                        <span class="fsize" id="fileSize"></span>
                        <button type="button" class="kx-preview-remove" onclick="removeFile()">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>
                @error('proof')
                <div class="mt-2 small" style="color:#ff6b6b"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="kx-btn" id="submitBtn">
            <i class="bi bi-arrow-right-circle-fill"></i>
            <span>Continue to Payout Details</span>
        </button>
    </form>

</div>
</div>
<div id="kxToast"></div>
@endsection

@push('scripts')
<script>
/* ── File handling ── */
function handleFile(input) {
    const file = input.files[0];
    if (!file) return;
    const maxSize = 2 * 1024 * 1024;
    const validTypes = ['image/jpeg','image/png','application/pdf'];
    if (!validTypes.includes(file.type)) { showToast('Invalid file type. Use JPG, PNG or PDF.','err'); input.value=''; return; }
    if (file.size > maxSize) { showToast('File too large. Max 2MB.','err'); input.value=''; return; }

    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent  = (file.size/1024/1024).toFixed(2)+' MB';
    const preview = document.getElementById('filePreview');
    preview.classList.add('has-file');
    const img = document.getElementById('previewImg');
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; img.style.display = 'block'; };
        reader.readAsDataURL(file);
    } else { img.style.display = 'none'; }
}

function removeFile() {
    document.getElementById('proofFile').value = '';
    document.getElementById('filePreview').classList.remove('has-file');
}

/* ── Drag feedback ── */
const zone = document.getElementById('dropZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('drag-over'); });

/* ── Copy wallet ── */
function copyWallet() {
    const addr = document.getElementById('walletAddr').textContent.trim();
    navigator.clipboard.writeText(addr).then(() => {
        document.getElementById('copyIcon').className = 'bi bi-clipboard-check';
        document.getElementById('copyText').textContent = 'Copied!';
        document.getElementById('copyBtn').classList.add('copied');
        showToast('Wallet address copied!','ok');
        setTimeout(() => {
            document.getElementById('copyIcon').className = 'bi bi-clipboard';
            document.getElementById('copyText').textContent = 'Copy Address';
            document.getElementById('copyBtn').classList.remove('copied');
        }, 2500);
    }).catch(() => showToast('Failed to copy address.','err'));
}

/* ── Countdown timer ── */
let timeLeft = 50 * 60;
const timerEl = document.getElementById('timerDisplay');
const timerBox = document.getElementById('timerBox');
const submitBtn = document.getElementById('submitBtn');
function tick() {
    const m = Math.floor(timeLeft / 60);
    const s = timeLeft % 60;
    timerEl.textContent = `${m}:${s.toString().padStart(2,'0')}`;
    if (timeLeft <= 300) timerBox.classList.add('danger');
    if (timeLeft <= 0) {
        clearInterval(timerInterval);
        submitBtn.disabled = true;
        showToast('Time expired! Please restart the transaction.','err');
    }
    timeLeft--;
}
tick();
const timerInterval = setInterval(tick, 1000);

/* ── Form submit ── */
document.getElementById('proofForm').addEventListener('submit', function(e) {
    if (!document.getElementById('proofFile').files.length) {
        e.preventDefault(); showToast('Please upload a payment proof.','err'); return;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Uploading...';
    setTimeout(() => {
        if (submitBtn.disabled) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i><span>Continue to Payout Details</span>';
            showToast('Upload timed out. Please try again.', 'err');
        }
    }, 60000);
});

// Reset spinner if user presses Back
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        if (submitBtn && submitBtn.disabled) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i><span>Continue to Payout Details</span>';
        }
    }
});

/* ── Toast ── */
function showToast(msg, type='err') {
    const t = document.getElementById('kxToast');
    t.textContent = msg; t.className = `show ${type}`;
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 3500);
}
</script>
@endpush

