@extends('layout')

@section('title', 'Edit Bank Details')

@push('styles')
<style>
:root {
    --kx-green: #00cc00;
    --kx-green-dim: rgba(0,204,0,0.10);
    --kx-green-glow: rgba(0,204,0,0.22);
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.07);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
}
body { background: var(--kx-dark); color: var(--kx-text); }

/* ── Hero ── */
.eb-hero {
    background: linear-gradient(135deg, #0a1628 0%, #0d1a15 100%);
    border-bottom: 1px solid var(--kx-border);
    padding: 2rem 1rem 1.75rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
}
.eb-hero::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, var(--kx-green-glow), transparent 70%);
    pointer-events: none;
}
.eb-hero-icon {
    width: 58px; height: 58px; border-radius: 50%;
    background: var(--kx-green-dim);
    border: 1px solid rgba(0,204,0,0.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: var(--kx-green);
    margin: 0 auto .9rem;
}
.eb-hero h1 { font-size: 1.35rem; font-weight: 700; color: #fff; margin: 0 0 .3rem; }
.eb-hero p  { color: var(--kx-muted); font-size: .83rem; margin: 0; }

.eb-back {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .8rem; color: var(--kx-muted); text-decoration: none;
    margin-bottom: 1.5rem;
    transition: color .2s;
}
.eb-back:hover { color: var(--kx-green); }

/* ── Wrap ── */
.eb-wrap { max-width: 560px; margin: 0 auto; padding: 0 1rem 3rem; }

/* ── Card ── */
.eb-card {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 18px;
    padding: 2rem 1.75rem;
    margin-bottom: 1.25rem;
}

/* ── Form fields ── */
.eb-label {
    font-size: .78rem; font-weight: 600;
    color: var(--kx-muted); text-transform: uppercase;
    letter-spacing: .05em; margin-bottom: .45rem;
    display: block;
}
.eb-input {
    width: 100%;
    background: var(--kx-card2);
    border: 1px solid var(--kx-border);
    border-radius: 10px;
    color: var(--kx-text);
    font-size: .9rem;
    padding: .75rem 1rem;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    appearance: none;
}
.eb-input:focus {
    border-color: rgba(0,204,0,.4);
    box-shadow: 0 0 0 3px rgba(0,204,0,.08);
    color: #fff;
}
.eb-input:disabled {
    opacity: .5; cursor: not-allowed;
}
.eb-input::placeholder { color: var(--kx-muted); }
.eb-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%237a8599' d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem; }

/* ── Field group ── */
.eb-field { margin-bottom: 1.35rem; }
.eb-field:last-child { margin-bottom: 0; }

/* ── Validation badge ── */
.eb-validation {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .78rem; padding: .3rem .75rem;
    border-radius: 99px; margin-top: .55rem;
    font-weight: 500;
}
.eb-validation.validating { background: rgba(56,189,248,.08); color: #38bdf8; border: 1px solid rgba(56,189,248,.2); }
.eb-validation.success    { background: rgba(0,204,0,.08);  color: var(--kx-green); border: 1px solid rgba(0,204,0,.2); }
.eb-validation.error      { background: rgba(239,68,68,.08); color: #f87171; border: 1px solid rgba(239,68,68,.2); }

/* ── Submit button ── */
.eb-btn {
    width: 100%; padding: .85rem 1rem;
    border-radius: 12px; border: none; outline: none;
    font-size: .95rem; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: opacity .2s, transform .15s, box-shadow .2s;
}
.eb-btn-primary {
    background: linear-gradient(135deg, #00cc00, #00a000);
    color: #fff;
    box-shadow: 0 4px 16px rgba(0,204,0,.2);
}
.eb-btn-primary:hover:not(:disabled) {
    box-shadow: 0 6px 24px rgba(0,204,0,.35);
    transform: translateY(-1px);
}
.eb-btn:disabled { opacity: .45; cursor: not-allowed; }

/* ── Locked notice ── */
.eb-locked {
    background: rgba(251,191,36,.04);
    border: 1px solid rgba(251,191,36,.15);
    border-radius: 14px;
    padding: 1.25rem 1.35rem;
    display: flex; gap: 1rem; align-items: flex-start;
}
.eb-locked-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: rgba(251,191,36,.1); color: #fbbf24;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0; margin-top: .05rem;
}
.eb-locked-title { font-size: .9rem; font-weight: 700; color: #fbbf24; margin-bottom: .3rem; }
.eb-locked-text  { font-size: .82rem; color: var(--kx-muted); line-height: 1.55; }
.eb-locked-link  { color: var(--kx-green); text-decoration: none; font-weight: 500; }
.eb-locked-link:hover { text-decoration: underline; }

/* ── Current bank info display ── */
.eb-info-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .85rem 0;
    border-bottom: 1px solid var(--kx-border);
}
.eb-info-row:last-child { border-bottom: none; padding-bottom: 0; }
.eb-info-key   { font-size: .78rem; color: var(--kx-muted); text-transform: uppercase; letter-spacing: .05em; }
.eb-info-val   { font-size: .88rem; color: #fff; font-weight: 600; }

/* ── Section label ── */
.eb-section-label {
    font-size: .72rem; font-weight: 700; color: var(--kx-muted);
    text-transform: uppercase; letter-spacing: .08em;
    margin-bottom: .75rem;
}

/* ── Field error ── */
.eb-field-error { font-size: .78rem; color: #f87171; margin-top: .4rem; display: flex; align-items: center; gap: .3rem; }

/* ── Alert banners ── */
.eb-alert {
    border-radius: 12px; padding: .9rem 1.1rem; margin-bottom: 1.25rem;
    font-size: .85rem; display: flex; align-items: flex-start; gap: .7rem;
}
.eb-alert-success { background: rgba(0,204,0,.08); border: 1px solid rgba(0,204,0,.2); color: #4ade80; }
.eb-alert-danger  { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #f87171; }

/* ── Password field eye toggle ── */
.eb-pw-wrap { position: relative; }
.eb-pw-eye {
    position: absolute; right: .9rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: var(--kx-muted); cursor: pointer;
    font-size: 1rem; padding: 0;
    transition: color .2s;
}
.eb-pw-eye:hover { color: var(--kx-green); }

/* ── Chat Popup ── */
.eb-chat-popup {
    position: fixed; bottom: 90px; right: 1.25rem;
    width: 320px; background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 18px; overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
    z-index: 9999;
    display: none;
    flex-direction: column;
    transition: transform .25s, opacity .25s;
}
.eb-chat-popup.is-open { display: flex; }
.eb-chat-header {
    background: linear-gradient(135deg, #00cc00, #009900);
    padding: .85rem 1.1rem;
    display: flex; align-items: center; justify-content: space-between;
}
.eb-chat-header-title { font-size: .9rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: .5rem; }
.eb-chat-close { background: rgba(255,255,255,.2); border: none; color: #fff; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .85rem; }
.eb-chat-close:hover { background: rgba(255,255,255,.35); }
.eb-chat-messages { height: 260px; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: .6rem; }
.eb-chat-messages::-webkit-scrollbar { width: 4px; }
.eb-chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }
.eb-chat-msg { max-width: 85%; padding: .55rem .8rem; border-radius: 12px; font-size: .82rem; line-height: 1.45; }
.eb-chat-msg.sent { background: linear-gradient(135deg,#00cc00,#009900); color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
.eb-chat-msg.received { background: var(--kx-card2); color: var(--kx-text); align-self: flex-start; border-bottom-left-radius: 4px; }
.eb-chat-msg small { display: block; opacity: .6; font-size: .68rem; margin-top: .25rem; }
.eb-chat-footer { padding: .75rem; border-top: 1px solid var(--kx-border); display: flex; gap: .5rem; }
.eb-chat-footer input {
    flex: 1; background: var(--kx-card2); border: 1px solid var(--kx-border);
    border-radius: 8px; color: var(--kx-text); font-size: .84rem;
    padding: .55rem .85rem; outline: none;
}
.eb-chat-footer input:focus { border-color: rgba(0,204,0,.4); }
.eb-chat-send { background: var(--kx-green); border: none; color: #fff; border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; font-size: .9rem; }
.eb-chat-send:hover { background: #00a000; }

/* ── FAB chat toggle ── */
.eb-chat-fab {
    position: fixed; bottom: 1.5rem; right: 1.25rem;
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, #00cc00, #009900);
    border: none; color: #fff; font-size: 1.25rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; box-shadow: 0 6px 24px rgba(0,204,0,.35);
    z-index: 9998;
    transition: transform .2s, box-shadow .2s;
}
.eb-chat-fab:hover { transform: scale(1.08); box-shadow: 0 8px 28px rgba(0,204,0,.5); }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="eb-hero">
    <div class="eb-hero-icon"><i class="bi bi-bank2"></i></div>
    <h1>Bank Account Details</h1>
    <p>Your bank details are used to receive NGN settlements</p>
</div>

<div class="eb-wrap">

    {{-- Back link --}}
    <a href="{{ route('settings.index') }}" class="eb-back">
        <i class="bi bi-arrow-left"></i> Back to Settings
    </a>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="eb-alert eb-alert-success">
            <i class="bi bi-check-circle-fill mt-1"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="eb-alert eb-alert-danger">
            <i class="bi bi-exclamation-circle-fill mt-1"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="eb-alert eb-alert-danger">
            <i class="bi bi-exclamation-circle-fill mt-1" style="flex-shrink:0"></i>
            <ul style="margin:0;padding-left:1rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($bankDetailsSet)
    {{-- ── LOCKED STATE ── --}}
    <div class="eb-section-label">Current Bank Account</div>
    <div class="eb-card" style="margin-bottom:1.25rem;">
        <div class="eb-info-row">
            <span class="eb-info-key">Bank Name</span>
            <span class="eb-info-val">{{ $user->bank_name ?? '—' }}</span>
        </div>
        <div class="eb-info-row">
            <span class="eb-info-key">Account Number</span>
            <span class="eb-info-val" style="font-family:monospace;letter-spacing:.1em;">{{ $user->account_number ?? '—' }}</span>
        </div>
        <div class="eb-info-row">
            <span class="eb-info-key">Account Name</span>
            <span class="eb-info-val">{{ $user->account_name ?? '—' }}</span>
        </div>
    </div>

    <div class="eb-locked">
        <div class="eb-locked-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <div>
            <div class="eb-locked-title">Bank details are locked</div>
            <div class="eb-locked-text">
                For your security, bank details cannot be changed once set. To update them, please contact support via
                <a href="#" id="open-chat" class="eb-locked-link">live chat</a>
                or email
                <a href="mailto:info@kayxchange.net" class="eb-locked-link">info@kayxchange.net</a>.
            </div>
        </div>
    </div>

    @else
    {{-- ── EDITABLE FORM ── --}}
    <div class="eb-section-label">Add Bank Account</div>
    <div class="eb-card">
        <form method="POST" action="{{ route('update.bank') }}" id="bank-form">
            @csrf

            {{-- Bank Name --}}
            <div class="eb-field">
                <label for="bank_code" class="eb-label">Bank Name</label>
                <select name="bank_code" id="bank_code" class="eb-input eb-select" required>
                    <option value="">Select your bank…</option>
                </select>
                @error('bank_code')
                    <div class="eb-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Account Number --}}
            <div class="eb-field">
                <label for="account_number" class="eb-label">Account Number</label>
                <input type="text" name="account_number" id="account_number"
                       class="eb-input" maxlength="10"
                       placeholder="10-digit NUBAN number"
                       value="{{ old('account_number', $user->account_number) }}" required>
                @error('account_number')
                    <div class="eb-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                @enderror
                <div id="account-name-result"></div>
            </div>

            {{-- Account Name (auto-filled) --}}
            <div class="eb-field">
                <label for="account_name" class="eb-label">
                    Account Name
                    <span style="color:var(--kx-muted);text-transform:none;letter-spacing:0;font-weight:400;"> — auto-filled after validation</span>
                </label>
                <input type="text" name="account_name" id="account_name"
                       class="eb-input" placeholder="Will be filled automatically"
                       value="{{ old('account_name', $user->account_name) }}"
                       readonly required>
                @error('account_name')
                    <div class="eb-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="eb-field">
                <label for="password" class="eb-label">Confirm with Password</label>
                <div class="eb-pw-wrap">
                    <input type="password" name="password" id="password"
                           class="eb-input" placeholder="Enter your account password" required>
                    <button type="button" class="eb-pw-eye" id="pw-toggle" tabindex="-1">
                        <i class="bi bi-eye" id="pw-icon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="eb-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="eb-btn eb-btn-primary" id="submit-btn" disabled>
                <i class="bi bi-check2-circle"></i> Save Bank Details
            </button>
        </form>
    </div>

    <p style="font-size:.78rem;color:var(--kx-muted);text-align:center;margin-top:.75rem;">
        <i class="bi bi-shield-check" style="color:var(--kx-green)"></i>
        Your details are encrypted and stored securely. Bank details can only be set once.
    </p>
    @endif

</div><!-- /eb-wrap -->

{{-- ── Live Chat Popup ── --}}
<div id="chat-popup" class="eb-chat-popup">
    <div class="eb-chat-header">
        <div class="eb-chat-header-title"><i class="bi bi-headset"></i> Support Chat</div>
        <button id="close-chat" class="eb-chat-close"><i class="bi bi-x"></i></button>
    </div>
    <div id="chat-messages" class="eb-chat-messages"></div>
    <div class="eb-chat-footer">
        <input type="text" id="chat-input" placeholder="Type a message…" autocomplete="off" />
        <button id="send-message" class="eb-chat-send"><i class="bi bi-send-fill"></i></button>
    </div>
</div>

{{-- ── FAB ── --}}
<button id="chat-toggle" class="eb-chat-fab" title="Live Support">
    <i class="bi bi-chat-dots-fill"></i>
</button>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="{{ asset('js/echo.js') }}"></script>
<script>
$(document).ready(function () {

    /* ── Bank list ── */
    $.ajax({
        url: '{{ route('paystack.banks') }}',
        method: 'GET',
        success: function (response) {
            if (response.status && response.data) {
                response.data.forEach(function (bank) {
                    $('#bank_code').append(`<option value="${bank.code}">${bank.name}</option>`);
                });
                @if($user->bank_code)
                    $('#bank_code').val('{{ $user->bank_code }}');
                @endif
            } else {
                showValidation('error', 'Failed to load banks: ' + (response.message || 'Unknown error'));
            }
        },
        error: function () {
            showValidation('error', 'Failed to load banks. Please try again.');
        }
    });

    /* ── Account validation ── */
    $('#account_number').on('input', function () {
        const accountNumber = $(this).val();
        const bankCode = $('#bank_code').val();
        if (accountNumber.length >= 10 && bankCode) {
            showValidation('validating', '<span class="spinner-border spinner-border-sm me-1" style="width:.7rem;height:.7rem;border-width:.12em"></span> Validating account…');
            $.ajax({
                url: '{{ route('paystack.resolve-account') }}',
                method: 'POST',
                data: { account_number: accountNumber, bank_code: bankCode, _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.status && response.data) {
                        $('#account_name').val(response.data.account_name);
                        showValidation('success', '<i class="bi bi-check-circle-fill"></i> ' + response.data.account_name);
                        $('#submit-btn').prop('disabled', false);
                    } else {
                        $('#account_name').val('');
                        showValidation('error', '<i class="bi bi-x-circle-fill"></i> ' + (response.message || 'Could not verify account'));
                        $('#submit-btn').prop('disabled', true);
                    }
                },
                error: function () {
                    $('#account_name').val('');
                    showValidation('error', '<i class="bi bi-x-circle-fill"></i> Validation failed. Please try again.');
                    $('#submit-btn').prop('disabled', true);
                }
            });
        } else {
            $('#account_name').val('');
            $('#account-name-result').html('');
            $('#submit-btn').prop('disabled', true);
        }
    });

    $('#bank_code').on('change', function () { $('#account_number').trigger('input'); });

    function showValidation(type, html) {
        const iconMap = { validating: '', success: '', error: '' };
        $('#account-name-result').html(`<div class="eb-validation ${type}">${html}</div>`);
    }

    /* ── Password toggle ── */
    $('#pw-toggle').on('click', function () {
        const input = $('#password');
        const isText = input.attr('type') === 'text';
        input.attr('type', isText ? 'password' : 'text');
        $('#pw-icon').toggleClass('bi-eye bi-eye-slash');
    });

    /* ── Live Chat ── */
    const userId = {{ auth()->id() }};
    const chatPopup = $('#chat-popup');

    Echo.private(`chat.${userId}`)
        .listen('MessageSent', (e) => {
            appendMessage(e.message.content, e.message.sender_id === userId ? 'sent' : 'received', e.message.created_at);
        });

    $.get('{{ route('chat.history') }}', function (messages) {
        messages.forEach(msg => {
            appendMessage(msg.content, msg.sender_id === userId ? 'sent' : 'received', msg.created_at);
        });
    });

    $('#chat-toggle').on('click', function () {
        chatPopup.toggleClass('is-open');
        $(this).toggleClass('d-none');
    });
    $('#open-chat').on('click', function (e) {
        e.preventDefault();
        chatPopup.addClass('is-open');
        $('#chat-toggle').addClass('d-none');
    });
    $('#close-chat').on('click', function () {
        chatPopup.removeClass('is-open');
        $('#chat-toggle').removeClass('d-none');
    });

    $('#send-message').on('click', sendChat);
    $('#chat-input').on('keypress', function (e) { if (e.which === 13) sendChat(); });

    function sendChat() {
        const msg = $('#chat-input').val().trim();
        if (!msg) return;
        $.ajax({
            url: '{{ route('chat.send') }}',
            method: 'POST',
            data: { message: msg, _token: '{{ csrf_token() }}' },
            success: function () { $('#chat-input').val(''); },
            error: function (xhr) { alert('Failed to send: ' + (xhr.responseJSON?.message || 'Unknown error')); }
        });
    }

    function appendMessage(content, type, createdAt) {
        const time = new Date(createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const html = `<div class="eb-chat-msg ${type}">${content}<small>${time}</small></div>`;
        $('#chat-messages').append(html);
        const el = document.getElementById('chat-messages');
        el.scrollTop = el.scrollHeight;
    }
});
</script>
@endsection