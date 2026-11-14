@extends('adminnavlayout')

@section('title', 'Admin Profile')

@section('content')
@php
    $show2faTab = session('show_2fa_setup') || session('show_2fa_settings') || session('success_2fa') || $errors->has('code') || request('tab') === '2fa';
    $showPasswordTab = session('success_password') || ($errors->has('current_password') && !session('success_email') && !session('show_2fa_settings'));
@endphp
<style>
.admin-profile-page{max-width:1100px;margin-inline:auto;padding:1.5rem;color:var(--bs-body-color)}
.admin-profile-hero{position:relative;overflow:hidden;border:1px solid rgba(148,163,184,.2);border-radius:24px;padding:clamp(1.25rem,3vw,2.25rem);margin-bottom:1.4rem;background:radial-gradient(ellipse at 92% 0,rgba(37,211,102,.15),transparent 38%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(17,31,40,.96));color:#f8fafc;box-shadow:0 18px 55px rgba(2,8,23,.18)}
.admin-profile-hero-row{display:flex;align-items:center;gap:1rem}.admin-profile-avatar{width:62px;height:62px;border-radius:20px;display:grid;place-items:center;background:linear-gradient(145deg,#25d366,#14a44d);color:#07150c;font-weight:900;font-size:1.55rem;box-shadow:0 8px 24px rgba(37,211,102,.2)}
.admin-profile-eyebrow{color:#7ee6a3;font-size:.72rem;font-weight:800;letter-spacing:.14em;margin-bottom:.3rem}.admin-profile-heading{color:#fff;font-weight:800;letter-spacing:-.03em;margin:0}.admin-profile-subtitle{color:#afbdc8;margin:.35rem 0 0;font-size:.92rem}
.admin-profile-card{border:1px solid rgba(148,163,184,.2)!important;border-radius:19px!important;overflow:hidden;box-shadow:0 12px 38px rgba(2,8,23,.08)!important}.admin-profile-card>.card-header{padding:1.15rem 1.35rem!important;border-bottom:1px solid rgba(148,163,184,.14)!important}.admin-profile-card>.card-body{padding:clamp(1rem,3vw,1.7rem)!important}
.admin-profile-tabs{gap:.45rem;border:0!important;overflow-x:auto;flex-wrap:nowrap;padding:.2rem}.admin-profile-tabs .nav-link{white-space:nowrap;border:1px solid rgba(148,163,184,.22)!important;border-radius:12px!important;color:var(--bs-secondary-color);font-weight:700;padding:.72rem 1rem}.admin-profile-tabs .nav-link.active{background:#10271a!important;border-color:rgba(37,211,102,.45)!important;color:#40d978!important}
.twofa-status{border:1px solid rgba(37,211,102,.25);border-radius:16px;background:rgba(37,211,102,.08);padding:1rem 1.1rem}.twofa-step-card{border:1px solid rgba(148,163,184,.2);border-radius:16px;padding:1rem;background:rgba(148,163,184,.055)}.twofa-qr{width:190px;height:190px;object-fit:contain;background:#fff;border:1px solid #dbe3ea;border-radius:12px;padding:8px}
@media(max-width:575px){.admin-profile-page{padding:1rem .8rem}.admin-profile-hero{border-radius:19px}.admin-profile-avatar{width:52px;height:52px;border-radius:17px}}
</style>
<div class="admin-profile-page">

    <div class="admin-profile-hero">
      <div class="admin-profile-hero-row">
        <div class="admin-profile-avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div>
            <div class="admin-profile-eyebrow">ACCOUNT SECURITY</div>
            <h1 class="admin-profile-heading h3">Admin profile</h1>
            <p class="admin-profile-subtitle">Manage your sign-in details and protect administrator access.</p>
        </div>
      </div>
    </div>

    {{-- Tab Nav --}}
    <ul class="nav nav-tabs admin-profile-tabs mb-4" id="profileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ !$show2faTab && !$showPasswordTab ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-email" role="tab">
                <i class="bi bi-envelope me-1"></i> Email
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $showPasswordTab ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-password" role="tab">
                <i class="bi bi-key me-1"></i> Password
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $show2faTab ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-2fa" role="tab">
                <i class="bi bi-shield-lock me-1"></i> Two-Factor Auth
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ── Tab 1: Email ──────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ !$show2faTab && !$showPasswordTab ? 'show active' : '' }}" id="tab-email" role="tabpanel">
            <div class="card admin-profile-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 fw-semibold">
                    <i class="bi bi-envelope text-primary me-1"></i> Change Email Address
                </div>
                <div class="card-body">
                    @if(session('success_email'))
                        <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> {{ session('success_email') }}</div>
                    @endif
                    <form method="POST" action="{{ route('admin.profile.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Email</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->email }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm with Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-envelope-check me-1"></i> Update Email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Tab 2: Password ───────────────────────────────────────── --}}
        <div class="tab-pane fade {{ $showPasswordTab ? 'show active' : '' }}" id="tab-password" role="tabpanel">
            <div class="card admin-profile-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 fw-semibold">
                    <i class="bi bi-key text-primary me-1"></i> Change Password
                </div>
                <div class="card-body">
                    @if(session('success_password'))
                        <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> {{ session('success_password') }}</div>
                    @endif
                    <form method="POST" action="{{ route('admin.profile.password') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="newPass" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Min 8 chars, mixed case + numbers.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Tab 3: 2FA ────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ $show2faTab ? 'show active' : '' }}" id="tab-2fa" role="tabpanel">
            <div class="card admin-profile-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 fw-semibold">
                    <i class="bi bi-shield-lock text-primary me-1"></i> Two-Factor Authentication (TOTP)
                </div>
                <div class="card-body">

                    @if(session('success_2fa'))
                        <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> {{ session('success_2fa') }}</div>
                    @endif
                    @error('code')
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> {{ $message }}</div>
                    @enderror

                    @if($twoFactorEnabled)
                        {{-- ── 2FA is ON ── --}}
                        <div class="twofa-status d-flex align-items-center gap-3 mb-4">
                            <i class="bi bi-shield-fill-check fs-4"></i>
                            <div>
                                <strong>2FA is enabled</strong> on your account.<br>
                                <small class="text-muted">Enabled on {{ auth()->user()->two_factor_confirmed_at?->format('d M Y, H:i') ?? 'date unavailable' }}</small>
                            </div>
                        </div>

                        <p class="text-muted small mb-3">
                            To disable 2FA, confirm both your current password and a fresh authenticator code.
                        </p>

                        <form method="POST" action="{{ route('admin.profile.2fa.disable') }}" onsubmit="return confirm('Disable two-factor protection for this admin account?')">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirm Password to Disable</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Authenticator code</label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required style="max-width:220px;letter-spacing:.22em;font-size:1.2rem">
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-shield-x me-1"></i> Disable 2FA
                            </button>
                        </form>

                    @else
                        {{-- ── 2FA is OFF ── --}}
                        <p class="text-muted mb-4">
                            Protect your admin account with an authenticator app like <strong>Authy</strong> or <strong>Google Authenticator</strong>.
                            Once enabled, you'll need your app to log in.
                        </p>

                        {{-- Step 1: Generate QR --}}
                        <div id="step1" class="{{ $pendingSetup ? 'd-none' : '' }}">
                            <button type="button" class="btn btn-success" id="startSetupBtn">
                                <i class="bi bi-qr-code me-1"></i> Set Up Two-Factor Auth
                            </button>
                            <div id="setupError" class="alert alert-danger mt-3 d-none" role="alert"></div>
                        </div>

                        {{-- Step 2: Scan QR + verify --}}
                        <div id="step2" class="{{ $pendingSetup ? '' : 'd-none' }} mt-3">
                            <div class="twofa-step-card mb-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-auto text-center">
                                        <img id="qrImage" class="twofa-qr" src="{{ $pendingSetup['qr_data_uri'] ?? '' }}" alt="Authenticator setup QR code" @if(!$pendingSetup) hidden @endif>
                                        <div id="qrUnavailable" class="small text-muted" @if($pendingSetup && $pendingSetup['qr_data_uri']) hidden @endif>QR preview unavailable. Enter the setup key manually.</div>
                                    </div>
                                    <div class="col">
                                        <p class="fw-semibold mb-1">1. Open Authy or Google Authenticator</p>
                                        <p class="text-muted small mb-2">Scan this QR code. The code is generated on this KayXchange server.</p>
                                        <p class="fw-semibold mb-1">2. Manual setup key</p>
                                        <div class="d-flex align-items-center gap-2">
                                            <code id="secretDisplay" class="fs-6 bg-body border rounded px-2 py-1 user-select-all">{{ $pendingSetup['secret'] ?? '' }}</code>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copySecret(this)" aria-label="Copy setup key">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info small" role="note">Keep this setup key private. KayXchange staff will never ask you to share your authenticator code.</div>

                            <form method="POST" action="{{ route('admin.profile.2fa.confirm') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">3. Enter the 6-digit code from your app</label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                           pattern="[0-9]{6}" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="000000"
                                           style="max-width:180px;font-size:1.3rem;letter-spacing:0.2em;" required
                                           value="{{ old('code') }}">
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i> Verify &amp; Enable 2FA
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.profile.2fa.cancel') }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted px-0">Cancel setup</button>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>

@push('scripts')
<script>
function showSetupError(message) {
    const errorBox = document.getElementById('setupError');
    if (!errorBox) return;
    errorBox.textContent = message;
    errorBox.classList.remove('d-none');
}

function renderSetup(data) {
    document.getElementById('secretDisplay').textContent = data.secret || '';
    const image = document.getElementById('qrImage');
    if (data.qr_data_uri) {
        image.src = data.qr_data_uri;
        image.hidden = false;
        document.getElementById('qrUnavailable').hidden = true;
    } else {
        image.hidden = true;
        document.getElementById('qrUnavailable').hidden = false;
    }
    document.getElementById('step1').classList.add('d-none');
    document.getElementById('step2').classList.remove('d-none');
    document.querySelector('#step2 input[name="code"]')?.focus();
}

document.getElementById('startSetupBtn')?.addEventListener('click', async function () {
    const button = this;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating…';
    document.getElementById('setupError')?.classList.add('d-none');
    try {
        const response = await fetch('{{ route("admin.profile.2fa.setup") }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.error || data.message || 'Could not start setup. Refresh and try again.');
        renderSetup(data);
    } catch (error) {
        showSetupError(error instanceof Error ? error.message : 'Could not start setup. Please try again.');
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-qr-code me-1"></i> Set Up Two-Factor Auth';
    }
});

function copySecret(button) {
    const secret = document.getElementById('secretDisplay').textContent;
    navigator.clipboard?.writeText(secret).then(() => {
        button.innerHTML = '<i class="bi bi-check"></i>';
        setTimeout(() => button.innerHTML = '<i class="bi bi-clipboard"></i>', 1500);
    }).catch(() => alert('Copy failed. Select and copy the setup key manually.'));
}

// Open the requested tab when arriving from the navbar or after a form redirect.
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash === '#tab-2fa') {
        const trigger = document.querySelector('[href="#tab-2fa"]');
        if (trigger && window.bootstrap?.Tab) bootstrap.Tab.getOrCreateInstance(trigger).show();
    }
});
</script>
@endpush
@endsection
