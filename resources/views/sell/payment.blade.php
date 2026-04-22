@extends('layout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}

.kx-hero{background:linear-gradient(135deg,#0a1628,#0d1f1a);border-bottom:1px solid var(--kx-border);padding:1.5rem 1rem 1rem;text-align:center;margin-bottom:1.5rem;}
.kx-hero h1{font-size:1.45rem;font-weight:700;color:#fff;margin:0 0 .25rem;}
.kx-hero p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1rem;}

/* Trade recap */
.recap-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
.recap-item{background:rgba(255,255,255,.03);border:1px solid var(--kx-border);border-radius:8px;padding:.6rem .8rem;}
.recap-item .ri-label{font-size:.7rem;color:var(--kx-muted);margin-bottom:.2rem;}
.recap-item .ri-value{font-size:.85rem;color:var(--kx-text);font-weight:600;word-break:break-all;}
.recap-item.full{grid-column:1/-1;}

/* Wallet card */
.wallet-card{background:var(--kx-card2);border:1px solid rgba(0,204,0,.2);border-radius:12px;padding:1.25rem;margin-bottom:1rem;}
.wallet-row{display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--kx-border);}
.wallet-row:last-child{border-bottom:none;}
.wallet-label{font-size:.78rem;color:var(--kx-muted);}
.wallet-value{font-size:.88rem;color:var(--kx-text);font-weight:700;word-break:break-all;max-width:65%;text-align:right;}
.copy-btn{background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:var(--kx-green);border-radius:6px;padding:.2rem .5rem;font-size:.75rem;cursor:pointer;transition:all .2s;white-space:nowrap;margin-left:.5rem;}
.copy-btn:hover{background:rgba(0,204,0,.2);}

/* Upload zone */
.upload-zone{background:var(--kx-card2);border:2px dashed var(--kx-border);border-radius:12px;padding:2rem 1rem;text-align:center;cursor:pointer;transition:all .25s;margin-bottom:1rem;}
.upload-zone:hover,.upload-zone.drag-over{border-color:var(--kx-green);background:rgba(0,204,0,.05);}
.upload-icon{font-size:2rem;color:var(--kx-muted);margin-bottom:.6rem;}
.upload-title{font-size:.9rem;font-weight:600;color:var(--kx-text);margin-bottom:.3rem;}
.upload-sub{font-size:.78rem;color:var(--kx-muted);}
.preview-wrap{display:none;margin-top:1rem;}
.preview-wrap img{max-width:100%;max-height:200px;border-radius:10px;border:1px solid var(--kx-border);}

.btn-kx-submit{width:100%;background:linear-gradient(135deg,#00cc00,#009900);color:#000;border:none;border-radius:12px;font-size:1rem;font-weight:700;padding:.85rem 1.5rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:.5rem;}
.btn-kx-submit:hover{filter:brightness(1.08);transform:translateY(-1px);}
.btn-kx-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;}

.kx-info-banner{background:rgba(56,189,248,.07);border:1px solid rgba(56,189,248,.2);border-radius:10px;padding:.75rem 1rem;font-size:.8rem;color:#7dd3fc;display:flex;align-items:flex-start;gap:.6rem;margin-bottom:1rem;}
</style>
@endpush

@section('content')
<div class="kx-hero">
    <h1>Upload Crypto Send Proof</h1>
    <p>Sell {{ strtoupper($trade->coin) }} — Upload a screenshot showing you sent the crypto</p>
</div>

<div class="container" style="max-width:520px;padding-bottom:3rem;">

    {{-- Success / error --}}
    @if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif
    @if($errors->has('proof'))
    <div class="alert alert-danger mb-3">{{ $errors->first('proof') }}</div>
    @endif

    {{-- Trade recap --}}
    <div class="kx-card">
        <h6 style="font-size:.85rem;font-weight:700;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.9rem;">Trade Summary</h6>
        <div class="recap-grid">
            <div class="recap-item">
                <div class="ri-label">Coin</div>
                <div class="ri-value">{{ strtoupper($trade->coin) }}</div>
            </div>
            <div class="recap-item">
                <div class="ri-label">Amount (USD)</div>
                <div class="ri-value">${{ number_format($trade->usd_amount, 2) }}</div>
            </div>
            <div class="recap-item">
                <div class="ri-label">You receive (NGN)</div>
                <div class="ri-value" style="color:var(--kx-green)">₦{{ number_format($trade->naira_amount, 2) }}</div>
            </div>
            <div class="recap-item">
                <div class="ri-label">Reference</div>
                <div class="ri-value" style="font-family:monospace;font-size:.75rem">{{ $trade->transaction_ref ?? $trade->id }}</div>
            </div>
        </div>
    </div>

    {{-- Send-to wallet --}}
    @if($walletAddress)
    <div class="kx-card">
        <h6 style="font-size:.85rem;font-weight:700;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.9rem;">Send Crypto To This Wallet</h6>
        <div class="kx-info-banner">
            <i class="bi bi-info-circle-fill flex-shrink-0"></i>
            Send <strong>{{ strtoupper($trade->coin) }}</strong> to the address below, then upload a screenshot as proof below.
        </div>
        <div class="wallet-card">
            <div class="wallet-row">
                <span class="wallet-label">Network</span>
                <span class="wallet-value">{{ strtoupper($trade->coin) }}</span>
            </div>
            <div class="wallet-row">
                <span class="wallet-label">Wallet Address</span>
                <div style="display:flex;align-items:center;justify-content:flex-end;flex:1;padding-left:1rem;">
                    <span class="wallet-value" id="walletAddr">{{ $walletAddress }}</span>
                    <button class="copy-btn" onclick="copyWallet()"><i class="bi bi-copy"></i> Copy</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Upload form --}}
    <div class="kx-card">
        <h6 style="font-size:.85rem;font-weight:700;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.9rem;">Upload Send Proof</h6>
        @if($trade->proof && $trade->proof !== 'bot_initiated')
        <div style="margin-bottom:1rem;padding:.75rem;background:rgba(0,204,0,.07);border:1px solid rgba(0,204,0,.2);border-radius:10px;font-size:.82rem;color:#4ade80;">
            <i class="bi bi-check-circle-fill me-1"></i> You've already uploaded proof. You can replace it below.
        </div>
        @endif
        <form method="POST" action="{{ route('sell.upload', $trade->id) }}" enctype="multipart/form-data" id="proofForm">
            @csrf
            <label for="proofInput" class="upload-zone d-block" id="uploadZone">
                <div class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                <div class="upload-title">Drag &amp; drop or click to upload</div>
                <div class="upload-sub">JPG, PNG, WEBP — max 5MB</div>
                <input type="file" name="proof" id="proofInput" accept="image/*" required class="d-none">
                <div class="preview-wrap" id="previewWrap">
                    <img id="previewImg" src="" alt="preview">
                    <div id="previewName" style="font-size:.78rem;color:var(--kx-muted);margin-top:.4rem;"></div>
                </div>
            </label>
            @error('proof')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn-kx-submit mt-3" id="submitBtn" disabled>
                <i class="bi bi-upload"></i> Upload Proof &amp; Submit
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
const input    = document.getElementById('proofInput');
const zone     = document.getElementById('uploadZone');
const preview  = document.getElementById('previewWrap');
const img      = document.getElementById('previewImg');
const nameEl   = document.getElementById('previewName');
const submitBtn = document.getElementById('submitBtn');

function handleFile(file) {
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => {
        img.src = e.target.result;
        nameEl.textContent = file.name;
        preview.style.display = 'block';
        submitBtn.disabled = false;
    };
    reader.readAsDataURL(file);
}

input.addEventListener('change', () => handleFile(input.files[0]));

zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        handleFile(file);
    }
});

document.getElementById('proofForm').addEventListener('submit', () => {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading…';
});

function copyWallet() {
    const addr = document.getElementById('walletAddr')?.textContent?.trim();
    if (!addr) return;
    navigator.clipboard.writeText(addr).then(() => {
        const btn = event.target.closest('.copy-btn');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}
</script>
@endpush
