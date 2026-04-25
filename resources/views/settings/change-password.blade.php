@extends('layout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,.08);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
body{background:var(--kx-dark)!important;color:var(--kx-text)!important;}
.cp-wrap{max-width:480px;margin:48px auto 80px;padding:0 16px;}
.cp-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:18px;padding:32px 28px;}
.cp-title{font-size:1.2rem;font-weight:800;color:#e4e8f0;margin-bottom:4px;}
.cp-sub{font-size:.82rem;color:var(--kx-muted);margin-bottom:28px;}
.cp-field{margin-bottom:18px;}
.cp-label{display:block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--kx-muted);margin-bottom:7px;}
.cp-input-wrap{position:relative;}
.cp-input{width:100%;padding:12px 44px 12px 14px;background:#0d1117;border:1.5px solid var(--kx-border);border-radius:11px;color:#e4e8f0;font-size:.9rem;outline:none;transition:border .15s;box-sizing:border-box;}
.cp-input:focus{border-color:rgba(0,204,0,.45);box-shadow:0 0 0 3px rgba(0,204,0,.07);}
.cp-input.is-error{border-color:#ef4444;}
.cp-eye{position:absolute;right:13px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--kx-muted);cursor:pointer;font-size:1rem;padding:0;line-height:1;}
.cp-eye:hover{color:#e4e8f0;}
.cp-err{font-size:.75rem;color:#ef4444;margin-top:5px;}
.cp-strength{margin-top:8px;}
.cp-strength-bar{height:4px;border-radius:4px;background:var(--kx-card2);overflow:hidden;}
.cp-strength-fill{height:100%;border-radius:4px;transition:width .3s,background .3s;width:0%;}
.cp-strength-label{font-size:.72rem;color:var(--kx-muted);margin-top:4px;}
.cp-hint{font-size:.72rem;color:var(--kx-muted);margin-top:6px;line-height:1.5;}
.cp-hint span{margin-right:6px;}
.cp-hint .ok{color:#00cc00;}
.cp-hint .bad{color:var(--kx-muted);}
.cp-btn{width:100%;padding:13px;background:var(--kx-green);color:#000;font-size:.92rem;font-weight:700;border:none;border-radius:11px;cursor:pointer;transition:opacity .15s,transform .1s;margin-top:8px;}
.cp-btn:hover{opacity:.88;transform:translateY(-1px);}
.cp-btn:disabled{opacity:.45;cursor:not-allowed;transform:none;}
.cp-success{background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.25);border-radius:11px;padding:14px 18px;color:#00cc00;font-size:.87rem;display:flex;align-items:center;gap:10px;margin-bottom:20px;}
.cp-error-box{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:11px;padding:14px 18px;color:#ef4444;font-size:.87rem;margin-bottom:20px;}
</style>
@endpush

@section('content')
<div class="cp-wrap">
    <div style="margin-bottom:20px;">
        <a href="{{ url('/settings') }}" style="color:var(--kx-muted);font-size:.82rem;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>
    </div>

    <div class="cp-card">
        <div class="cp-title"><i class="bi bi-shield-lock-fill me-2" style="color:#00cc00"></i>Change Password</div>
        <div class="cp-sub">Use a strong password with uppercase letters and numbers.</div>

        @if(session('success'))
            <div class="cp-success"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>
        @endif

        @if($errors->has('current_password'))
            <div class="cp-error-box"><i class="bi bi-x-circle-fill me-2"></i>{{ $errors->first('current_password') }}</div>
        @endif

        @if($errors->has('new_password'))
            <div class="cp-error-box"><i class="bi bi-x-circle-fill me-2"></i>{{ $errors->first('new_password') }}</div>
        @endif

        <form method="POST" action="{{ route('change.password') }}" id="cpForm" novalidate>
            @csrf

            {{-- Current Password --}}
            <div class="cp-field">
                <label class="cp-label" for="current_password">Current Password</label>
                <div class="cp-input-wrap">
                    <input type="password" name="current_password" id="current_password"
                        class="cp-input {{ $errors->has('current_password') ? 'is-error' : '' }}"
                        placeholder="Enter your current password" required autocomplete="current-password">
                    <button type="button" class="cp-eye" onclick="togglePwd('current_password',this)">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            {{-- New Password --}}
            <div class="cp-field">
                <label class="cp-label" for="new_password">New Password</label>
                <div class="cp-input-wrap">
                    <input type="password" name="new_password" id="new_password"
                        class="cp-input {{ $errors->has('new_password') ? 'is-error' : '' }}"
                        placeholder="Min 8 chars, 1 uppercase, 1 number"
                        required minlength="8" autocomplete="new-password"
                        oninput="checkStrength(this.value)">
                    <button type="button" class="cp-eye" onclick="togglePwd('new_password',this)">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                <div class="cp-strength">
                    <div class="cp-strength-bar"><div class="cp-strength-fill" id="strengthFill"></div></div>
                    <div class="cp-strength-label" id="strengthLabel"></div>
                </div>
                <div class="cp-hint" id="pwHints">
                    <span id="h-len" class="bad"><i class="bi bi-x-circle"></i> 8+ characters</span>
                    <span id="h-upper" class="bad"><i class="bi bi-x-circle"></i> Uppercase letter</span>
                    <span id="h-num" class="bad"><i class="bi bi-x-circle"></i> Number</span>
                </div>
            </div>

            {{-- Confirm New Password --}}
            <div class="cp-field">
                <label class="cp-label" for="new_password_confirmation">Confirm New Password</label>
                <div class="cp-input-wrap">
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                        class="cp-input" placeholder="Repeat new password"
                        required autocomplete="new-password"
                        oninput="checkMatch()">
                    <button type="button" class="cp-eye" onclick="togglePwd('new_password_confirmation',this)">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                <div class="cp-err" id="matchErr" style="display:none;">Passwords do not match.</div>
            </div>

            <button type="submit" class="cp-btn" id="cpBtn">
                <i class="bi bi-shield-check me-1"></i> Update Password
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-slash';
    }
}

function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    const hLen   = document.getElementById('h-len');
    const hUpper = document.getElementById('h-upper');
    const hNum   = document.getElementById('h-num');

    const hasLen   = val.length >= 8;
    const hasUpper = /[A-Z]/.test(val);
    const hasNum   = /[0-9]/.test(val);
    const hasSpec  = /[^A-Za-z0-9]/.test(val);

    hLen.className   = hasLen   ? 'ok' : 'bad';
    hLen.innerHTML   = (hasLen   ? '<i class="bi bi-check-circle"></i>' : '<i class="bi bi-x-circle"></i>') + ' 8+ characters';
    hUpper.className = hasUpper ? 'ok' : 'bad';
    hUpper.innerHTML = (hasUpper ? '<i class="bi bi-check-circle"></i>' : '<i class="bi bi-x-circle"></i>') + ' Uppercase letter';
    hNum.className   = hasNum   ? 'ok' : 'bad';
    hNum.innerHTML   = (hasNum   ? '<i class="bi bi-check-circle"></i>' : '<i class="bi bi-x-circle"></i>') + ' Number';

    let score = [hasLen, hasUpper, hasNum, hasSpec, val.length >= 12].filter(Boolean).length;

    const levels = [
        {w:'0%',   c:'transparent', t:''},
        {w:'25%',  c:'#ef4444',     t:'Weak'},
        {w:'50%',  c:'#f97316',     t:'Fair'},
        {w:'75%',  c:'#fbbf24',     t:'Good'},
        {w:'100%', c:'#00cc00',     t:'Strong'},
    ];
    const lv = levels[score] || levels[0];
    fill.style.width      = lv.w;
    fill.style.background = lv.c;
    label.textContent     = lv.t;
    label.style.color     = lv.c;

    checkMatch();
}

function checkMatch() {
    const pw  = document.getElementById('new_password').value;
    const pw2 = document.getElementById('new_password_confirmation').value;
    const err = document.getElementById('matchErr');
    if (pw2 && pw !== pw2) {
        err.style.display = 'block';
    } else {
        err.style.display = 'none';
    }
}

document.getElementById('cpForm').addEventListener('submit', function(e) {
    const pw  = document.getElementById('new_password').value;
    const pw2 = document.getElementById('new_password_confirmation').value;
    if (pw !== pw2) { e.preventDefault(); document.getElementById('matchErr').style.display = 'block'; return; }
    document.getElementById('cpBtn').disabled = true;
    document.getElementById('cpBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';
});
</script>
@endpush

@endsection

