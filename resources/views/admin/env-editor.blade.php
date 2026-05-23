@extends('adminnavlayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
}

/* Site mode toggle styling (matches admin dashboard) */
.kx-mode-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 14px;
    border-radius:999px;
    font-size:0.78rem;
    font-weight:700;
    letter-spacing:0.04em;
    text-transform:uppercase;
    cursor:pointer;
    border:none;
    transition:background 0.25s, box-shadow 0.25s, transform 0.15s;
    white-space:nowrap;
}
.kx-mode-badge.mode-production{
    background:rgba(0,204,0,0.15);
    color:var(--kx-green);
    border:1px solid rgba(0,204,0,0.35);
}
.kx-mode-badge.mode-production:hover{
    background:rgba(0,204,0,0.28);
    box-shadow:0 0 0 3px rgba(0,204,0,0.25);
    transform:translateY(-1px);
}
.kx-mode-badge.mode-developer{
    background:rgba(251,191,36,0.15);
    color:#fbbf24;
    border:1px solid rgba(251,191,36,0.35);
}
.kx-mode-badge.mode-developer:hover{
    background:rgba(251,191,36,0.28);
    box-shadow:0 0 0 3px rgba(251,191,36,0.2);
    transform:translateY(-1px);
}
.kx-mode-dot{
    width:7px;height:7px;border-radius:50%;
    flex-shrink:0;
    background:#999;
}
.mode-production .kx-mode-dot{ background:var(--kx-green); animation:pulse-dot 1.6s ease infinite; }
.mode-developer  .kx-mode-dot{ background:var(--kx-amber, #fbbf24); }
@keyframes pulse-dot{
    0%,100% { box-shadow: 0 0 0 0 rgba(0,204,0,0.25); }
    50%      { box-shadow: 0 0 0 6px transparent; }
}

body{background:var(--kx-dark);color:var(--kx-text);}

.env-wrap{padding:28px 0 60px;}
.env-page-title{font-size:clamp(1.3rem,3vw,1.8rem);font-weight:800;color:#fff;margin-bottom:4px;}
.env-page-sub{color:var(--kx-muted);font-size:.875rem;margin-bottom:28px;}

/* Security badge */
.env-security-note{
    display:flex;align-items:flex-start;gap:.75rem;
    background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);
    border-radius:12px;padding:14px 18px;margin-bottom:24px;font-size:.82rem;color:#fca5a5;
}
.env-security-note i{font-size:1rem;margin-top:1px;flex-shrink:0;color:#ef4444;}

/* Group card */
.env-card{
    background:var(--kx-card);border:1px solid var(--kx-border);
    border-radius:16px;padding:24px 28px;margin-bottom:20px;
    position:relative;overflow:hidden;
}
.env-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:var(--env-accent,#00cc00);
}
.env-group-label{
    font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;
    color:var(--env-accent,#00cc00);margin-bottom:18px;
    display:flex;align-items:center;gap:8px;
}
.env-group-label::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.06);}
.env-group-label i{font-size:.9rem;}

/* Field */
.env-field{margin-bottom:0;}
.env-label{font-size:.75rem;font-weight:600;color:var(--kx-muted);margin-bottom:5px;display:flex;align-items:center;gap:.4rem;}
.env-key{font-size:.68rem;font-family:monospace;color:rgba(255,255,255,.25);margin-left:auto;}
.env-input-wrap{position:relative;}
.env-input{
    width:100%;background:var(--kx-card2);border:1px solid var(--kx-border);
    color:var(--kx-text);border-radius:10px;padding:.65rem 2.6rem .65rem .85rem;
    font-size:.84rem;font-family:monospace;outline:none;transition:border-color .2s,box-shadow .2s;
}
.env-input:focus{border-color:rgba(0,204,0,.5);box-shadow:0 0 0 3px rgba(0,204,0,.08);}
.env-input[type=password]{letter-spacing:.1em;}

/* ── Payment Method Toggles ── */
.pm-toggle-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.85rem;}
@media(max-width:500px){.pm-toggle-grid{grid-template-columns:1fr;}}
.pm-toggle-card{
    background:var(--kx-card2);border:2px solid var(--kx-border);
    border-radius:14px;padding:1.1rem 1.25rem;
    display:flex;align-items:center;justify-content:space-between;gap:1rem;
    transition:border-color .2s;
}
.pm-toggle-card.pm-on { border-color:rgba(0,204,0,.35); }
.pm-toggle-card.pm-off{ border-color:rgba(239,68,68,.3); opacity:.7; }
.pm-tc-left{display:flex;align-items:center;gap:.75rem;}
.pm-tc-icon{
    width:42px;height:42px;border-radius:10px;border:1.5px solid var(--kx-border);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.2rem;
}
.pm-tc-name{font-size:.9rem;font-weight:700;color:#fff;}
.pm-tc-status{font-size:.72rem;font-weight:600;margin-top:.15rem;}
.pm-tc-status.on{color:var(--kx-green);}
.pm-tc-status.off{color:#ef4444;}
.pm-no-keys{
    display:inline-flex;align-items:center;gap:.3rem;
    background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);
    color:#fbbf24;border-radius:6px;padding:.1rem .5rem;font-size:.68rem;font-weight:700;
    margin-top:.3rem;
}
.pm-toggle-card.pm-locked { opacity:.55; }
.pm-switch.pm-switch-disabled { cursor:not-allowed; }
.pm-switch.pm-switch-disabled .pm-slider { cursor:not-allowed; }

/* Toggle switch */
.pm-switch{position:relative;display:inline-block;width:46px;height:26px;flex-shrink:0;}
.pm-switch input{opacity:0;width:0;height:0;}
.pm-slider{
    position:absolute;inset:0;background:rgba(239,68,68,.3);border:1px solid rgba(239,68,68,.4);
    border-radius:13px;cursor:pointer;transition:all .25s;
}
.pm-slider::before{
    content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;
    background:#fff;border-radius:50%;transition:transform .25s;
}
.pm-switch input:checked + .pm-slider{background:rgba(0,204,0,.3);border-color:rgba(0,204,0,.5);}
.pm-switch input:checked + .pm-slider::before{transform:translateX(20px);background:var(--kx-green);}
.env-eye{
    position:absolute;right:.7rem;top:50%;transform:translateY(-50%);
    background:none;border:none;color:var(--kx-muted);cursor:pointer;padding:0;font-size:.9rem;
    transition:color .15s;
}
.env-eye:hover{color:var(--kx-text);}

/* Save bar */
.env-save-bar{
    position:sticky;bottom:0;z-index:10;
    background:rgba(13,17,23,.92);backdrop-filter:blur(12px);
    border-top:1px solid var(--kx-border);padding:16px 0;margin:0;
}
.env-save-btn{
    background:linear-gradient(135deg,#00cc00,#007a0c);color:#fff;
    border:none;border-radius:12px;padding:13px 32px;font-weight:700;
    font-size:.92rem;cursor:pointer;transition:all .22s;
    box-shadow:0 4px 18px rgba(0,204,0,.3);
}
.env-save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,204,0,.42);}
.env-alert{
    background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);
    color:#00cc00;border-radius:12px;padding:12px 18px;
    margin-bottom:20px;font-size:.875rem;
}
.env-alert.error{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.25);color:#ef4444;}

/* Korapay guide */
.env-guide{
    background:rgba(167,139,250,.06);border:1px solid rgba(167,139,250,.2);
    border-radius:14px;padding:16px 20px;margin-top:8px;font-size:.8rem;color:rgba(228,232,240,.7);
    line-height:1.7;
}
.env-guide strong{color:#c4b5fd;}
.env-guide a{color:#a78bfa;text-decoration:none;}
.env-guide a:hover{text-decoration:underline;}
.env-guide-toggle{
    background:none;border:none;color:#a78bfa;font-size:.78rem;font-weight:600;
    cursor:pointer;padding:0;margin-top:12px;display:flex;align-items:center;gap:.4rem;
}
</style>
@endpush

@section('content')
<div class="env-wrap">
<div class="container-xl">

    @php $siteMode = $siteMode ?? 'production'; @endphp

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div>
            <h1 class="env-page-title"><i class="bi bi-sliders me-2" style="color:#00cc00"></i>API Keys & Environment</h1>
            <p class="env-page-sub">Set payment gateway credentials, Telegram bot, and mail settings. Sensitive fields are masked.</p>
        </div>

        {{-- MODE TOGGLE (Production <-> Developer) --}}
        <div>
            <button id="kx-mode-toggle-btn-env"
                class="kx-mode-badge {{ $siteMode === 'developer' ? 'mode-developer' : 'mode-production' }}"
                data-mode="{{ $siteMode }}"
                title="Toggle site mode">
                <span class="kx-mode-dot"></span>
                <span>{{ $siteMode === 'developer' ? 'Developer Mode' : 'Production Mode' }}</span>
                <i class="bi bi-arrow-repeat" style="font-size:0.7rem;opacity:0.7"></i>
            </button>
            <div style="height:10px"></div>
            <div style="font-size:.78rem;color:var(--kx-muted);">
                Warning: Developer Mode may expose extra debug info.
            </div>
        </div>
    </div>

    <!-- MODE CONFIRM OVERLAY (Env Editor) -->
    <div id="kx-mode-overlay-env" class="kx-mode-confirm-overlay">
        <div class="kx-mode-confirm-box">
            <div id="kx-mode-confirm-icon-env" class="kx-mode-confirm-icon">🔄</div>
            <div id="kx-mode-confirm-title-env" class="kx-mode-confirm-title">Switch Site Mode?</div>
            <div id="kx-mode-confirm-text-env" class="kx-mode-confirm-text"></div>
            <div class="kx-mode-confirm-btns">
                <button class="btn-kx-cancel" onclick="document.getElementById('kx-mode-overlay-env').classList.remove('active')">Cancel</button>
                <button id="kx-mode-confirm-btn-env" class="btn-kx-amber">Yes, Switch Mode</button>
            </div>
            <div id="kx-mode-post-msg-env" class="mt-3" style="display:none;font-size:0.82rem"></div>
        </div>
    </div>

    {{-- Security note --}}
    <div class="env-security-note">
        <i class="bi bi-shield-exclamation-fill"></i>
        <span>Only whitelisted API keys and credentials are editable here. Core system keys (APP_KEY, DB_PASSWORD, etc.) are protected and cannot be changed from this panel.</span>
    </div>

    @if(session('success'))
    <div class="env-alert"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="env-alert error"><i class="bi bi-x-circle-fill me-2"></i>{{ $errors->first() }}</div>
    @endif

    {{-- ══ Payment Method Toggles ══ --}}
    <div class="env-card mb-4" style="--env-accent:#00cc00;">
        <div class="env-group-label" style="color:#00cc00;">
            <i class="bi bi-toggles"></i>Payment Methods — Visible to Users
        </div>
        <p style="font-size:.8rem;color:var(--kx-muted);margin-bottom:1.25rem;">
            Toggle each payment method on or off. Disabled methods will be hidden from the deposit page immediately.
        </p>
        <div class="pm-toggle-grid" id="pm-toggle-grid">

            @php
            $pmCards = [
                'bank_transfer' => ['label' => 'Bank Transfer',  'icon' => 'bi-bank',                 'color' => '#3b82f6', 'bg' => 'rgba(59,130,246,.12)',  'border' => 'rgba(59,130,246,.3)'],
                'crypto_transfer' => ['label' => 'Crypto Transfer', 'icon' => 'bi-currency-bitcoin',  'color' => '#a855f7', 'bg' => 'rgba(168,85,247,.12)', 'border' => 'rgba(168,85,247,.3)'],
                'paystack'      => ['label' => 'Paystack',       'icon' => 'bi-credit-card-2-front-fill','color' => '#00a35e','bg' => 'rgba(0,163,94,.12)',     'border' => 'rgba(0,163,94,.3)'],
                'korapay'       => ['label' => 'Korapay',        'icon' => 'bi-lightning-charge-fill', 'color' => '#6338e1', 'bg' => 'rgba(99,56,225,.12)',   'border' => 'rgba(99,56,225,.3)'],
                'flutterwave'   => ['label' => 'Flutterwave',    'icon' => 'bi-fire',                  'color' => '#f55b14', 'bg' => 'rgba(245,91,20,.12)',   'border' => 'rgba(245,91,20,.3)'],
            ];
            @endphp

            @foreach($pmCards as $pmKey => $pmMeta)
            @php
                $isOn      = $enabledMethods[$pmKey] ?? true;
                $hasKeys   = $keysConfigured[$pmKey] ?? true;
                $isVisible = $isOn && $hasKeys; // actually shown to users
            @endphp
            <div class="pm-toggle-card {{ $isVisible ? 'pm-on' : 'pm-off' }} {{ !$hasKeys ? 'pm-locked' : '' }}" id="pm-card-{{ $pmKey }}">
                <div class="pm-tc-left">
                    <div class="pm-tc-icon" style="background:{{ $pmMeta['bg'] }};border-color:{{ $pmMeta['border'] }}">
                        <i class="bi {{ $pmMeta['icon'] }}" style="color:{{ $pmMeta['color'] }};"></i>
                    </div>
                    <div>
                        <div class="pm-tc-name">{{ $pmMeta['label'] }}</div>
                        <div class="pm-tc-status {{ $isVisible ? 'on' : 'off' }}" id="pm-status-{{ $pmKey }}">
                            @if(!$hasKeys)
                                Hidden — API keys not configured
                            @elseif($isOn)
                                Enabled — visible to users
                            @else
                                Disabled — hidden from users
                            @endif
                        </div>
                        @if(!$hasKeys)
                        <div class="pm-no-keys"><i class="bi bi-exclamation-triangle-fill"></i> Set API keys below to activate</div>
                        @endif
                    </div>
                </div>
                <label class="pm-switch {{ !$hasKeys ? 'pm-switch-disabled' : '' }}" title="{{ !$hasKeys ? 'Add API keys first to enable this method' : 'Toggle '.$pmMeta['label'] }}">
                    <input type="checkbox" class="pm-toggle-input"
                           data-method="{{ $pmKey }}"
                           {{ $isOn ? 'checked' : '' }}
                           {{ !$hasKeys ? 'disabled' : '' }}>
                    <span class="pm-slider"></span>
                </label>
            </div>
            @endforeach

        </div>
        <div id="pm-toast" style="display:none;margin-top:12px;font-size:.8rem;padding:8px 14px;border-radius:8px;"></div>
    </div>

    <form method="POST" action="{{ route('admin.env.update') }}" id="envForm">
        @csrf
        @method('PUT')

        @foreach($groups as $groupKey => $fields)
        @php $meta = $groupMeta[$groupKey] ?? ['label' => ucfirst($groupKey), 'icon' => 'bi-key', 'color' => '#00cc00']; @endphp
        <div class="env-card" style="--env-accent:{{ $meta['color'] }}">
            <div class="env-group-label" style="color:{{ $meta['color'] }}">
                <i class="bi {{ $meta['icon'] }}"></i>{{ $meta['label'] }}
            </div>
            <div class="row g-3">
                @foreach($fields as $field)
                <div class="col-md-6">
                    <div class="env-field">
                        <div class="env-label">
                            {{ $field['label'] }}
                            <span class="env-key">{{ $field['key'] }}</span>
                        </div>
                        <div class="env-input-wrap">
                            <input
                                type="{{ $field['type'] === 'password' ? 'password' : ($field['type'] === 'email' ? 'email' : ($field['type'] === 'url' ? 'url' : 'text')) }}"
                                name="env[{{ $field['key'] }}]"
                                value="{{ old('env.'.$field['key'], $field['value']) }}"
                                class="env-input"
                                autocomplete="off"
                                data-type="{{ $field['type'] }}"
                                id="field_{{ $field['key'] }}"
                            >
                            @if($field['type'] === 'password')
                            <button type="button" class="env-eye" onclick="toggleEye(this)" title="Show/hide">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Korapay help guide --}}
            @if($groupKey === 'korapay')
            <div style="margin-top:16px">
                <button type="button" class="env-guide-toggle" onclick="this.nextElementSibling.classList.toggle('d-none')">
                    <i class="bi bi-question-circle-fill"></i> Where do I get Korapay credentials?
                </button>
                <div class="env-guide d-none mt-2">
                    <strong>Step 1:</strong> Go to <a href="https://merchant.korapay.com" target="_blank">merchant.korapay.com</a> and create a free account.<br>
                    <strong>Step 2:</strong> After signup, go to <strong>Settings → API Keys</strong> in your Korapay dashboard.<br>
                    <strong>Step 3:</strong> Copy your <strong>Public Key</strong> (starts with <code>pk_</code>) and <strong>Secret Key</strong> (starts with <code>sk_</code>).<br>
                    <strong>Step 4:</strong> For webhooks, go to <strong>Settings → Webhooks</strong> and set your URL to:<br>
                    <code style="color:#c4b5fd">{{ url('/deposits/webhook/korapay') }}</code><br><br>
                    <strong>Test keys</strong> are available instantly. Live keys require business verification (ID + CAC docs).
                </div>
            </div>
            @endif

            {{-- Paystack help guide --}}
            @if($groupKey === 'paystack')
            <div style="margin-top:16px">
                <button type="button" class="env-guide-toggle" onclick="this.nextElementSibling.classList.toggle('d-none')" style="color:#00cc00">
                    <i class="bi bi-question-circle-fill"></i> Where do I get Paystack credentials?
                </button>
                <div class="env-guide d-none mt-2" style="border-color:rgba(0,204,0,.2);background:rgba(0,204,0,.05)">
                    <strong>Step 1:</strong> Go to <a href="https://dashboard.paystack.com" target="_blank" style="color:#4ade80">dashboard.paystack.com</a> → sign up or log in.<br>
                    <strong>Step 2:</strong> In the sidebar go to <strong>Settings → API Keys & Webhooks</strong>.<br>
                    <strong>Step 3:</strong> Copy <strong>Secret Key</strong> (starts with <code>sk_</code>) and <strong>Public Key</strong> (starts with <code>pk_</code>).<br>
                    <strong>Step 4:</strong> Set your webhook URL to:<br>
                    <code style="color:#86efac">{{ url('/deposits/webhook/paystack') }}</code>
                </div>
            </div>
            @endif

            {{-- Flutterwave help guide --}}
            @if($groupKey === 'flutterwave')
            <div style="margin-top:16px">
                <button type="button" class="env-guide-toggle" onclick="this.nextElementSibling.classList.toggle('d-none')" style="color:#fbbf24">
                    <i class="bi bi-question-circle-fill"></i> Where do I get Flutterwave credentials?
                </button>
                <div class="env-guide d-none mt-2" style="border-color:rgba(251,191,36,.2);background:rgba(251,191,36,.05)">
                    <strong>Step 1:</strong> Go to <a href="https://dashboard.flutterwave.com" target="_blank" style="color:#fcd34d">dashboard.flutterwave.com</a> → sign up or log in.<br>
                    <strong>Step 2:</strong> In the sidebar go to <strong>Settings → API</strong>.<br>
                    <strong>Step 3:</strong> Copy your <strong>Secret Key</strong> and <strong>Public Key</strong>.<br>
                    <strong>Step 4:</strong> Go to <strong>Settings → Webhooks</strong>, set URL to:<br>
                    <code style="color:#fde68a">{{ url('/deposits/webhook/flutterwave') }}</code><br>
                    And set a <strong>Secret Hash</strong> — paste the same value into <em>Webhook Hash / Secret</em> above.
                </div>
            </div>
            @endif
        </div>
        @endforeach

        {{-- Sticky save bar --}}
        <div class="env-save-bar">
            <div class="container-xl d-flex align-items-center gap-3">
                <button type="submit" class="env-save-btn">
                    <i class="bi bi-floppy-fill me-2"></i>Save All Keys
                </button>
                <span id="envSaveMsg" class="text-success small" style="display:none">
                    <i class="bi bi-check-circle-fill me-1"></i>Saved!
                </span>
            </div>
        </div>
    </form>

</div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    // ── Show/hide password fields ──
    function toggleEye(btn) {
        const input = btn.previousElementSibling;
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    }
    window.toggleEye = toggleEye;

    // ── Payment method toggles ──
    const pmToast = document.getElementById('pm-toast');

    function showPmToast(msg, ok) {
        pmToast.textContent = msg;
        pmToast.style.display = 'block';
        pmToast.style.background  = ok ? 'rgba(0,204,0,.12)' : 'rgba(239,68,68,.12)';
        pmToast.style.border      = ok ? '1px solid rgba(0,204,0,.3)' : '1px solid rgba(239,68,68,.3)';
        pmToast.style.color       = ok ? '#00cc00' : '#ef4444';
        clearTimeout(pmToast._t);
        pmToast._t = setTimeout(() => { pmToast.style.display = 'none'; }, 3000);
    }

    document.querySelectorAll('.pm-toggle-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const method  = this.dataset.method;
            const enabled = this.checked;
            const card    = document.getElementById('pm-card-' + method);
            const status  = document.getElementById('pm-status-' + method);

            // Optimistic UI update
            card.classList.toggle('pm-on',  enabled);
            card.classList.toggle('pm-off', !enabled);
            status.className = 'pm-tc-status ' + (enabled ? 'on' : 'off');
            status.textContent = enabled ? 'Enabled — visible to users' : 'Disabled — hidden from users';

            // AJAX call
            fetch('{{ route("admin.env.toggle-pm") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ method: method, enabled: enabled ? 1 : 0 }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showPmToast((enabled ? '✓ ' : '✕ ') + method.replace('_', ' ') + (enabled ? ' enabled' : ' disabled'), true);
                } else {
                    // Revert on failure
                    input.checked = !enabled;
                    card.classList.toggle('pm-on',  !enabled);
                    card.classList.toggle('pm-off', enabled);
                    showPmToast('Failed to save. Please try again.', false);
                }
            })
            .catch(() => {
                input.checked = !enabled;
                card.classList.toggle('pm-on',  !enabled);
                card.classList.toggle('pm-off', enabled);
                showPmToast('Network error. Please try again.', false);
            });
        });
    });
})();
</script>
@endpush
