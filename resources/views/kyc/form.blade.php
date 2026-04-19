@extends('kyclayout')

@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.07);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
}

.kyc-hero {
    background: linear-gradient(135deg, rgba(0,204,0,0.08) 0%, rgba(0,100,0,0.04) 100%);
    border: 1px solid rgba(0,204,0,0.15);
    border-radius: 20px;
    padding: 2.5rem 2rem;
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
.kyc-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(0,204,0,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.kyc-hero-icon {
    width: 72px; height: 72px;
    background: linear-gradient(135deg, #00cc00, #007a0c);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.25rem;
    font-size: 2rem; color: #fff;
    box-shadow: 0 8px 32px rgba(0,204,0,0.3);
}
.kyc-hero h1 { font-size: 1.75rem; font-weight: 700; color: #fff; margin-bottom: .5rem; }
.kyc-hero p { color: var(--kx-muted); font-size: .95rem; margin: 0; }

/* Steps */
.kx-steps { display: flex; margin-bottom: 2rem; }
.kx-step {
    flex: 1;
    display: flex; flex-direction: column; align-items: center;
    position: relative;
    padding: 0 .5rem;
}
.kx-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 20px; left: calc(50% + 22px);
    right: calc(-50% + 22px);
    height: 2px;
    background: var(--kx-border);
    z-index: 0;
}
.kx-step.done:not(:last-child)::after { background: var(--kx-green); }
.kx-step-num {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 700;
    background: var(--kx-card2); border: 2px solid var(--kx-border);
    color: var(--kx-muted); position: relative; z-index: 1;
    transition: all .3s;
}
.kx-step.active .kx-step-num {
    background: linear-gradient(135deg, #00cc00, #007a0c);
    border-color: #00cc00; color: #fff;
    box-shadow: 0 4px 16px rgba(0,204,0,0.35);
}
.kx-step.done .kx-step-num {
    background: rgba(0,204,0,0.15); border-color: #00cc00; color: #00cc00;
}
.kx-step-label { font-size: .72rem; color: var(--kx-muted); margin-top: .5rem; text-align: center; }
.kx-step.active .kx-step-label { color: var(--kx-green); font-weight: 600; }
.kx-step.done .kx-step-label { color: var(--kx-green); }

/* KYC Card */
.kx-kyc-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.kx-kyc-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--kx-border);
    display: flex; align-items: center; gap: .75rem;
}
.kx-kyc-card-header .hicon {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(0,204,0,0.1);
    display: flex; align-items: center; justify-content: center;
    color: var(--kx-green); font-size: 1.1rem; flex-shrink: 0;
}
.kx-kyc-card-header h5 { font-size: 1rem; font-weight: 600; color: #fff; margin: 0; }
.kx-kyc-card-header p { font-size: .8rem; color: var(--kx-muted); margin: 0; }
.kx-kyc-card-body { padding: 1.5rem; }

/* File drop zone */
.kx-drop-zone {
    border: 2px dashed rgba(0,204,0,0.25);
    border-radius: 12px;
    padding: 2rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .25s ease;
    background: rgba(0,204,0,0.02);
    position: relative;
}
.kx-drop-zone:hover, .kx-drop-zone.drag-over {
    border-color: var(--kx-green);
    background: rgba(0,204,0,0.06);
}
.kx-drop-zone input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.kx-drop-icon {
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(0,204,0,0.1);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto .75rem;
    font-size: 1.4rem; color: var(--kx-green);
    transition: transform .25s;
}
.kx-drop-zone:hover .kx-drop-icon { transform: scale(1.1); }
.kx-drop-title { font-size: .9rem; font-weight: 600; color: #fff; margin-bottom: .25rem; }
.kx-drop-hint { font-size: .78rem; color: var(--kx-muted); }
.kx-drop-types { font-size: .72rem; color: rgba(0,204,0,0.7); margin-top: .5rem; }

.kx-preview {
    display: none;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 1rem;
    border: 1px solid rgba(0,204,0,0.2);
}
.kx-preview img { width: 100%; max-height: 180px; object-fit: cover; display: block; }
.kx-preview-info {
    background: rgba(0,204,0,0.08);
    padding: .6rem 1rem;
    display: flex; align-items: center; gap: .5rem;
}
.kx-preview-info .fname { flex: 1; font-size: .8rem; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kx-preview-info .fsize { font-size: .75rem; color: var(--kx-muted); flex-shrink: 0; }
.kx-preview-remove {
    background: none; border: none; color: #ff4d4d;
    font-size: 1.1rem; cursor: pointer; padding: 0; flex-shrink: 0;
}
.kx-preview.has-file { display: block; }

/* Accepted list */
.kx-accept-list { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.kx-accept-list li {
    font-size: .82rem; color: var(--kx-text);
    display: flex; align-items: center; gap: .5rem;
    padding: .5rem .75rem;
    background: var(--kx-card2); border-radius: 8px;
}
.kx-accept-list li i { color: var(--kx-green); font-size: .85rem; flex-shrink: 0; }

/* Status banners */
.kx-status-banner {
    border-radius: 14px;
    padding: 1.5rem 1.75rem;
    display: flex; align-items: flex-start; gap: 1rem;
    margin-bottom: 1.5rem;
}
.kx-status-banner.pending  { background: rgba(255,193,7,0.08);  border: 1px solid rgba(255,193,7,0.25); }
.kx-status-banner.approved { background: rgba(0,204,0,0.08);    border: 1px solid rgba(0,204,0,0.25); }
.kx-status-banner.rejected { background: rgba(220,53,69,0.08);  border: 1px solid rgba(220,53,69,0.25); }
.kx-status-icon {
    width: 48px; height: 48px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem; flex-shrink: 0;
}
.pending  .kx-status-icon { background: rgba(255,193,7,0.15); color: #ffc107; }
.approved .kx-status-icon { background: rgba(0,204,0,0.15);   color: #00cc00; }
.rejected .kx-status-icon { background: rgba(220,53,69,0.15); color: #dc3545; }
.kx-status-body h5 { font-size: 1rem; font-weight: 700; margin: 0 0 .3rem; }
.pending  .kx-status-body h5 { color: #ffc107; }
.approved .kx-status-body h5 { color: #00cc00; }
.rejected .kx-status-body h5 { color: #dc3545; }
.kx-status-body p { font-size: .85rem; color: var(--kx-muted); margin: 0; }

/* Submit button */
.kx-submit-btn {
    background: linear-gradient(135deg, #00cc00, #007a0c);
    border: none; border-radius: 12px;
    color: #fff; font-size: 1rem; font-weight: 600;
    padding: .9rem 2rem; width: 100%;
    cursor: pointer; transition: all .25s ease;
    display: flex; align-items: center; justify-content: center; gap: .6rem;
    box-shadow: 0 4px 20px rgba(0,204,0,0.25);
    text-decoration: none;
}
.kx-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(0,204,0,0.35);
    color: #fff;
}
.kx-submit-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* Alert messages */
.kx-alert {
    padding: 1rem 1.25rem;
    border-radius: 10px;
    font-size: .875rem;
    display: flex; align-items: flex-start; gap: .75rem;
    margin-bottom: 1.5rem;
}
.kx-alert-success { background: rgba(0,204,0,0.1); border: 1px solid rgba(0,204,0,0.25); color: #b3ffb3; }
.kx-alert-error   { background: rgba(220,53,69,0.1); border: 1px solid rgba(220,53,69,0.25); color: #ffb3b3; }
.kx-alert i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

@media (max-width: 576px) {
    .kyc-hero { padding: 1.75rem 1.25rem; }
    .kyc-hero h1 { font-size: 1.4rem; }
    .kx-accept-list { grid-template-columns: 1fr; }
    .kx-step-label { font-size: .65rem; }
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">

        {{-- Hero --}}
        <div class="kyc-hero">
            <div class="kyc-hero-icon"><i class="bi bi-patch-check-fill"></i></div>
            <h1>Identity Verification</h1>
            <p>Complete KYC to unlock full access — buy, sell &amp; withdraw without limits.</p>
        </div>

        {{-- Progress Steps --}}
        <div class="kx-steps">
            <div class="kx-step done">
                <div class="kx-step-num"><i class="bi bi-check-lg"></i></div>
                <span class="kx-step-label">Account</span>
            </div>
            <div class="kx-step {{ $existingKyc ? 'done' : 'active' }}">
                <div class="kx-step-num">
                    @if($existingKyc)<i class="bi bi-check-lg"></i>@else 2 @endif
                </div>
                <span class="kx-step-label">Documents</span>
            </div>
            <div class="kx-step {{ ($existingKyc && $existingKyc->status === 'approved') ? 'done' : (($existingKyc && $existingKyc->status === 'pending') ? 'active' : '') }}">
                <div class="kx-step-num">
                    @if($existingKyc && $existingKyc->status === 'approved')<i class="bi bi-check-lg"></i>@else 3 @endif
                </div>
                <span class="kx-step-label">Verified</span>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="kx-alert kx-alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="kx-alert kx-alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- Status banner if already submitted --}}
        @if($existingKyc)
            @if($existingKyc->status === 'pending')
            <div class="kx-status-banner pending">
                <div class="kx-status-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="kx-status-body">
                    <h5>Under Review</h5>
                    <p>Your documents have been submitted and are being reviewed by our team. This usually takes 1–24 hours.</p>
                </div>
            </div>
            @elseif($existingKyc->status === 'approved')
            <div class="kx-status-banner approved">
                <div class="kx-status-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div class="kx-status-body">
                    <h5>KYC Verified</h5>
                    <p>Your identity has been verified. You have full access to all KayXchange features.</p>
                </div>
            </div>
            @elseif($existingKyc->status === 'rejected')
            <div class="kx-status-banner rejected">
                <div class="kx-status-icon"><i class="bi bi-x-circle-fill"></i></div>
                <div class="kx-status-body">
                    <h5>Verification Rejected</h5>
                    @if($existingKyc->rejection_reason)
                    <p style="margin-bottom:.6rem;">Your submission was rejected for the following reason:</p>
                    <div style="background:rgba(220,53,69,0.08);border:1px solid rgba(220,53,69,0.2);border-radius:8px;padding:.75rem 1rem;font-size:.85rem;color:#ffb3b3;line-height:1.6;">
                        {{ $existingKyc->rejection_reason }}
                    </div>
                    <p style="margin-top:.65rem;margin-bottom:0;font-size:.8rem;">Please correct the issue and re-upload your documents below.</p>
                    @else
                    <p>Your submission was rejected. Please re-upload clear, valid documents and resubmit below.</p>
                    @endif
                </div>
            </div>
            @endif
        @endif

        {{-- Accepted documents info --}}
        @if(!$existingKyc || $existingKyc->status === 'rejected')
        <div class="kx-kyc-card">
            <div class="kx-kyc-card-header">
                <div class="hicon"><i class="bi bi-info-circle-fill"></i></div>
                <div>
                    <h5>Accepted Documents</h5>
                    <p>Any one of the following government-issued IDs</p>
                </div>
            </div>
            <div class="kx-kyc-card-body">
                <ul class="kx-accept-list">
                    <li><i class="bi bi-check-circle-fill"></i> National ID Card</li>
                    <li><i class="bi bi-check-circle-fill"></i> International Passport</li>
                    <li><i class="bi bi-check-circle-fill"></i> Driver's License</li>
                    <li><i class="bi bi-check-circle-fill"></i> Voter's Card</li>
                </ul>
            </div>
        </div>

        {{-- Upload Form --}}
        <form method="POST" action="{{ route('kyc.submit') }}" enctype="multipart/form-data" id="kycForm">
            @csrf

            {{-- ID Document --}}
            <div class="kx-kyc-card">
                <div class="kx-kyc-card-header">
                    <div class="hicon"><i class="bi bi-card-image"></i></div>
                    <div>
                        <h5>Government-Issued ID</h5>
                        <p>Front &amp; back clearly visible — PDF, JPEG, PNG — max 2MB</p>
                    </div>
                </div>
                <div class="kx-kyc-card-body">
                    <div class="kx-drop-zone" id="idZone">
                        <input type="file" name="id_document" id="id_document" accept=".pdf,.jpg,.jpeg,.png"
                               onchange="handleFile(this,'idPreview','idName','idSize','idPreviewImg')">
                        <div class="kx-drop-icon"><i class="bi bi-upload"></i></div>
                        <div class="kx-drop-title">Click or drag &amp; drop your ID</div>
                        <div class="kx-drop-hint">Supports PDF, JPEG, PNG</div>
                        <div class="kx-drop-types">Max file size: 2MB</div>
                    </div>
                    <div class="kx-preview" id="idPreview">
                        <img id="idPreviewImg" src="" alt="Preview" style="display:none">
                        <div class="kx-preview-info">
                            <i class="bi bi-file-earmark-check text-success" style="flex-shrink:0"></i>
                            <span class="fname" id="idName">—</span>
                            <span class="fsize" id="idSize"></span>
                            <button type="button" class="kx-preview-remove"
                                    onclick="removeFile('id_document','idPreview')">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                    @error('id_document')
                    <div class="mt-2 small" style="color:#ff6b6b"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Selfie --}}
            <div class="kx-kyc-card">
                <div class="kx-kyc-card-header">
                    <div class="hicon"><i class="bi bi-camera-fill"></i></div>
                    <div>
                        <h5>Selfie with ID</h5>
                        <p>Hold your ID clearly next to your face — JPEG, PNG — max 2MB</p>
                    </div>
                </div>
                <div class="kx-kyc-card-body">
                    <div class="kx-drop-zone" id="selfieZone">
                        <input type="file" name="selfie" id="selfie" accept=".jpg,.jpeg,.png"
                               onchange="handleFile(this,'selfiePreview','selfieName','selfieSize','selfiePreviewImg')">
                        <div class="kx-drop-icon"><i class="bi bi-person-bounding-box"></i></div>
                        <div class="kx-drop-title">Click or drag &amp; drop your selfie</div>
                        <div class="kx-drop-hint">Hold your ID clearly visible next to your face</div>
                        <div class="kx-drop-types">JPEG, PNG — Max 2MB</div>
                    </div>
                    <div class="kx-preview" id="selfiePreview">
                        <img id="selfiePreviewImg" src="" alt="Preview">
                        <div class="kx-preview-info">
                            <i class="bi bi-file-earmark-check text-success" style="flex-shrink:0"></i>
                            <span class="fname" id="selfieName">—</span>
                            <span class="fsize" id="selfieSize"></span>
                            <button type="button" class="kx-preview-remove"
                                    onclick="removeFile('selfie','selfiePreview')">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                    @error('selfie')
                    <div class="mt-2 small" style="color:#ff6b6b"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Photo tips --}}
            <div class="kx-kyc-card mb-4">
                <div class="kx-kyc-card-body p-3">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div style="background:var(--kx-card2);border-radius:10px;padding:.75rem .5rem;">
                                <i class="bi bi-lightbulb-fill d-block mb-1" style="font-size:1.25rem;color:#ffc107"></i>
                                <div style="font-size:.72rem;color:var(--kx-muted)">Good lighting</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background:var(--kx-card2);border-radius:10px;padding:.75rem .5rem;">
                                <i class="bi bi-eye-fill d-block mb-1" style="font-size:1.25rem;color:#17a2b8"></i>
                                <div style="font-size:.72rem;color:var(--kx-muted)">All text visible</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background:var(--kx-card2);border-radius:10px;padding:.75rem .5rem;">
                                <i class="bi bi-shield-check d-block mb-1" style="font-size:1.25rem;color:var(--kx-green)"></i>
                                <div style="font-size:.72rem;color:var(--kx-muted)">Not expired</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="kx-submit-btn" id="submitBtn">
                <i class="bi bi-patch-check-fill"></i>
                <span>Submit KYC Documents</span>
            </button>
        </form>

        @elseif($existingKyc && $existingKyc->status === 'approved')
        {{-- Approved state --}}
        <div class="kx-kyc-card">
            <div class="kx-kyc-card-body text-center py-4">
                <div style="width:80px;height:80px;background:rgba(0,204,0,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:2.5rem;color:#00cc00;">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <h5 style="color:#fff;font-weight:700;margin-bottom:.5rem">You're fully verified!</h5>
                <p style="color:var(--kx-muted);font-size:.9rem;margin-bottom:1.5rem">Your identity has been confirmed. Enjoy unrestricted access to KayXchange.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="{{ route('buy') }}" class="kx-submit-btn" style="width:auto;padding:.7rem 1.75rem;">
                        <i class="bi bi-bag-check-fill"></i> Buy Crypto
                    </a>
                    <a href="{{ route('sell.form') }}"
                       style="background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;
                              color:#fff;padding:.7rem 1.75rem;text-decoration:none;font-size:.95rem;
                              font-weight:600;display:inline-flex;align-items:center;gap:.5rem;">
                        <i class="bi bi-arrow-up-circle-fill"></i> Sell Crypto
                    </a>
                </div>
            </div>
        </div>

        @else
        {{-- Pending state --}}
        <div class="kx-kyc-card">
            <div class="kx-kyc-card-body text-center py-4">
                <div style="width:80px;height:80px;background:rgba(255,193,7,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:2rem;color:#ffc107;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <h5 style="color:#fff;font-weight:700;margin-bottom:.5rem">Awaiting Review</h5>
                <p style="color:var(--kx-muted);font-size:.9rem;margin-bottom:0">
                    Our team reviews documents within 1–24 hours.<br>You'll be notified once your KYC is processed.
                </p>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
function handleFile(input, previewId, nameId, sizeId, imgId) {
    const file = input.files[0];
    if (!file) return;
    const preview = document.getElementById(previewId);
    document.getElementById(nameId).textContent = file.name;
    document.getElementById(sizeId).textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    preview.classList.add('has-file');
    const imgEl = document.getElementById(imgId);
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { imgEl.src = e.target.result; imgEl.style.display = 'block'; };
        reader.readAsDataURL(file);
    } else {
        imgEl.style.display = 'none';
    }
}

function removeFile(inputId, previewId) {
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).classList.remove('has-file');
}

// Drag-and-drop visual feedback
document.querySelectorAll('.kx-drop-zone').forEach(zone => {
    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop',      e => { e.preventDefault(); zone.classList.remove('drag-over'); });
});

// Loading state on submit
const form = document.getElementById('kycForm');
const btn  = document.getElementById('submitBtn');
if (form && btn) {
    form.addEventListener('submit', () => {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Uploading...';
    });
}
</script>
@endpush