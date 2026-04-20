@extends('adminnavlayout')

@section('title', 'Admin Profile')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 860px;">

    <div class="d-flex align-items-center mb-4 gap-3">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4"
             style="width:52px;height:52px;">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div>
            <h4 class="fw-bold mb-0">Admin Profile</h4>
            <p class="text-muted small mb-0">{{ auth()->user()->email }}</p>
        </div>
    </div>

    {{-- Tab Nav --}}
    <ul class="nav nav-tabs mb-4" id="profileTabs">
        <li class="nav-item">
            <a class="nav-link {{ !session('show_2fa_setup') && !session('success_password') ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-email">
                <i class="bi bi-envelope me-1"></i> Email
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ session('success_password') || $errors->has('current_password') && !session('success_email') ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-password">
                <i class="bi bi-key me-1"></i> Password
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ session('success_2fa') || session('show_2fa_setup') || $errors->has('code') ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-2fa">
                <i class="bi bi-shield-lock me-1"></i> Two-Factor Auth
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ── Tab 1: Email ──────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ !session('show_2fa_setup') && !session('success_password') && !$errors->has('code') ? 'show active' : '' }}" id="tab-email">
            <div class="card border-0 shadow-sm">
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
        <div class="tab-pane fade {{ session('success_password') ? 'show active' : '' }}" id="tab-password">
            <div class="card border-0 shadow-sm">
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
        <div class="tab-pane fade {{ session('success_2fa') || session('show_2fa_setup') || $errors->has('code') ? 'show active' : '' }}" id="tab-2fa">
            <div class="card border-0 shadow-sm">
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
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                            <i class="bi bi-shield-fill-check fs-4"></i>
                            <div>
                                <strong>2FA is enabled</strong> on your account.<br>
                                <small class="text-muted">Enabled on {{ auth()->user()->two_factor_confirmed_at?->format('d M Y, H:i') }}</small>
                            </div>
                        </div>

                        <p class="text-muted small mb-3">
                            To disable 2FA, enter your current password below.
                        </p>

                        <form method="POST" action="{{ route('admin.profile.2fa.disable') }}" onsubmit="return confirm('Are you sure you want to disable 2FA? This will reduce your account security.')">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirm Password to Disable</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        <div id="step1">
                            <button type="button" class="btn btn-primary" id="startSetupBtn">
                                <i class="bi bi-qr-code me-1"></i> Set Up Two-Factor Auth
                            </button>
                        </div>

                        {{-- Step 2: Scan QR + verify --}}
                        <div id="step2" class="d-none mt-3">
                            <div class="card bg-light border-0 p-3 mb-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-auto text-center">
                                        <canvas id="qrCanvas" class="border rounded p-1 bg-white d-block" style="width:180px;height:180px;"></canvas>
                                    </div>
                                    <div class="col">
                                        <p class="fw-semibold mb-1">1. Open Authy or Google Authenticator</p>
                                        <p class="text-muted small mb-2">Scan the QR code with your app.</p>
                                        <p class="fw-semibold mb-1">2. Or enter this secret manually:</p>
                                        <div class="d-flex align-items-center gap-2">
                                            <code id="secretDisplay" class="fs-6 bg-white border rounded px-2 py-1 user-select-all"></code>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copySecret()">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.profile.2fa.confirm') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">3. Enter the 6-digit code from your app</label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                           pattern="\d{6}" maxlength="6" inputmode="numeric" placeholder="000000"
                                           style="max-width:180px;font-size:1.3rem;letter-spacing:0.2em;" required
                                           value="{{ old('code') }}">
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i> Verify &amp; Enable 2FA
                                </button>
                                <button type="button" class="btn btn-link text-muted ms-2" onclick="cancelSetup()">Cancel</button>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
<script>
function renderQR(otpUri) {
    QRCode.toCanvas(document.getElementById('qrCanvas'), otpUri, {
        width: 180,
        margin: 2,
        errorCorrectionLevel: 'M',
        color: { dark: '#000000', light: '#ffffff' }
    }, function (err) {
        if (err) {
            document.getElementById('qrCanvas').insertAdjacentHTML('afterend',
                '<p class="text-danger small mt-1">QR error: ' + err.message + '</p>');
        }
    });
}

document.getElementById('startSetupBtn')?.addEventListener('click', function () {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating…';

    fetch('{{ route("admin.profile.2fa.setup") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('secretDisplay').textContent = data.secret;
        document.getElementById('step1').classList.add('d-none');
        document.getElementById('step2').classList.remove('d-none');
        renderQR(data.otp_uri);
    })
    .catch(() => {
        alert('Failed to start 2FA setup. Please try again.');
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-qr-code me-1"></i> Set Up Two-Factor Auth';
    });
});

function cancelSetup() {
    document.getElementById('step1').classList.remove('d-none');
    document.getElementById('step2').classList.add('d-none');
    document.getElementById('startSetupBtn').disabled = false;
    document.getElementById('startSetupBtn').innerHTML = '<i class="bi bi-qr-code me-1"></i> Set Up Two-Factor Auth';
}

function copySecret() {
    const secret = document.getElementById('secretDisplay').textContent;
    navigator.clipboard.writeText(secret).then(() => {
        const btn = event.target.closest('button');
        btn.innerHTML = '<i class="bi bi-check"></i>';
        setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i>', 1500);
    });
}

// Auto-show step2 if there was a validation error on code
@if($errors->has('code'))
(function() {
    fetch('{{ route("admin.profile.2fa.setup") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        document.getElementById('secretDisplay').textContent = data.secret;
        document.getElementById('step1').classList.add('d-none');
        document.getElementById('step2').classList.remove('d-none');
        renderQR(data.otp_uri);
    });
})();
@endif
</script>
@endpush
@endsection
