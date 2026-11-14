@extends('adminnavlayout')

@push('styles')
<style>
:root{
    --kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
}

/* Runtime mode: a visible, responsive production/local switch. */
.kx-mode-badge{display:inline-flex;align-items:center;gap:11px;min-height:54px;padding:8px 12px 8px 15px;border:1px solid rgba(74,222,128,.28);border-radius:999px;background:linear-gradient(135deg,rgba(20,39,31,.98),rgba(10,24,19,.98));color:#e8fff0;box-shadow:0 8px 28px rgba(0,0,0,.2),inset 0 1px rgba(255,255,255,.08);cursor:pointer;transition:transform .2s,border-color .2s,box-shadow .2s;white-space:nowrap}
.kx-mode-badge:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(0,0,0,.3),0 0 0 4px rgba(34,197,94,.1)}
.kx-mode-badge:focus-visible{outline:3px solid rgba(134,239,172,.75);outline-offset:3px}
.kx-mode-badge.mode-local{border-color:rgba(251,191,36,.42);background:linear-gradient(135deg,rgba(54,39,18,.98),rgba(30,23,13,.98));color:#fff3cf}
.kx-mode-status{display:flex;align-items:center;gap:9px}
.kx-mode-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;background:#22c55e;box-shadow:0 0 12px rgba(34,197,94,.8)}
.mode-local .kx-mode-dot{background:#f59e0b;box-shadow:0 0 12px rgba(245,158,11,.8)}
.kx-mode-label{font-size:.78rem;font-weight:800;letter-spacing:.045em;text-transform:uppercase}
.kx-mode-switch{width:52px;height:29px;padding:3px;display:inline-flex;align-items:center;border-radius:999px;background:linear-gradient(135deg,#168447,#0c6335);box-shadow:inset 0 2px 5px rgba(0,0,0,.35);transition:background .25s;flex-shrink:0}
.kx-mode-switch-knob{width:23px;height:23px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(145deg,#fff,#dcefe3);color:#13743d;box-shadow:0 2px 7px rgba(0,0,0,.35);transition:transform .28s cubic-bezier(.2,.8,.2,1),color .2s}
.mode-local .kx-mode-switch{background:linear-gradient(135deg,#e5a11b,#b66e0c)}
.mode-local .kx-mode-switch-knob{transform:translateX(23px);color:#9a5a08}
.kx-mode-badge.mode-off{border-color:rgba(248,113,113,.4);background:linear-gradient(135deg,rgba(55,25,28,.98),rgba(30,17,20,.98));color:#ffe1e1}
.mode-off .kx-mode-switch{background:linear-gradient(135deg,#c24141,#7f1d1d)}
.mode-off .kx-mode-switch-knob{transform:translateX(9px);color:#a52d2d}
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
.env-hero{position:relative;overflow:hidden;border:1px solid rgba(148,163,184,.18);border-radius:24px;padding:clamp(22px,4vw,34px);margin-bottom:22px;background:radial-gradient(circle at 85% 0%,rgba(34,197,94,.17),transparent 32%),linear-gradient(135deg,#111a27,#10151e 62%,#102019);box-shadow:0 20px 55px rgba(0,0,0,.2)}
.env-hero:after{content:'';position:absolute;width:200px;height:200px;border:1px solid rgba(255,255,255,.05);border-radius:50%;right:-55px;bottom:-125px;box-shadow:0 0 0 28px rgba(255,255,255,.018),0 0 0 58px rgba(255,255,255,.012)}
.env-hero-copy{position:relative;z-index:1;max-width:700px}.env-eyebrow{display:inline-flex;align-items:center;gap:8px;color:#86efac;font-size:.7rem;letter-spacing:.13em;text-transform:uppercase;font-weight:800}.env-page-title{font-size:clamp(1.5rem,3vw,2.15rem);letter-spacing:-.04em;margin-top:10px}.env-page-sub{line-height:1.65;max-width:650px;margin-bottom:0}
.env-hero-side{position:relative;z-index:1;display:flex;flex-direction:column;align-items:flex-end;gap:12px}.env-mode-caption{color:var(--kx-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;font-weight:800}.env-mode-warning{display:flex;align-items:flex-start;gap:9px;border:1px solid rgba(34,197,94,.2);background:rgba(34,197,94,.07);color:#b5f5c5;border-radius:12px;padding:11px 13px;font-size:.76rem;line-height:1.5;margin:0}.env-mode-warning.mode-local-warning{border-color:rgba(245,158,11,.28);background:rgba(127,72,7,.16);color:#ffe3a0}.env-mode-warning.mode-off-warning{border-color:rgba(248,113,113,.32);background:rgba(127,29,29,.18);color:#fecaca}
.env-overview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:20px}.env-overview-card{display:flex;align-items:center;gap:12px;background:#141c28;border:1px solid var(--kx-border);border-radius:15px;padding:14px 16px}.env-overview-icon{width:40px;height:40px;display:grid;place-items:center;border-radius:12px;background:rgba(34,197,94,.1);color:#86efac;font-size:1.1rem}.env-overview-label{font-size:.68rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.08em;font-weight:750}.env-overview-value{font-size:.9rem;color:#f7fafc;font-weight:800;margin-top:2px}
.env-nav{display:flex;gap:8px;overflow:auto;padding:3px 1px 13px;margin-bottom:6px;scrollbar-width:thin}.env-nav a{white-space:nowrap;display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid var(--kx-border);background:rgba(22,27,39,.86);border-radius:999px;text-decoration:none;color:#b7c2ce;font-size:.73rem;font-weight:700}.env-nav a:hover{border-color:rgba(34,197,94,.4);color:#a7f3b7}
.env-secret-state{display:flex;align-items:center;gap:6px;color:#86efac;font-size:.68rem;font-weight:750;margin-top:6px}.env-secret-state.empty{color:#fbbf24}.env-secret-clear{display:flex;align-items:center;gap:7px;color:#fca5a5;font-size:.7rem;margin-top:8px}.env-secret-clear input{accent-color:#ef4444}
.kx-mode-confirm-overlay{position:fixed;inset:0;z-index:12000;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(3,7,12,.78);backdrop-filter:blur(10px)}.kx-mode-confirm-overlay.active{display:flex}.kx-mode-confirm-box{width:min(100%,440px);padding:26px;border:1px solid rgba(255,255,255,.12);border-radius:22px;background:linear-gradient(145deg,#17212d,#101720);box-shadow:0 28px 90px rgba(0,0,0,.55);text-align:center}.kx-mode-confirm-icon{width:54px;height:54px;display:grid;place-items:center;border-radius:17px;margin:0 auto 14px;background:rgba(34,197,94,.1);font-size:1.5rem}.kx-mode-confirm-title{color:#f8fafc;font-weight:850;font-size:1.15rem;margin-bottom:8px}.kx-mode-confirm-text{color:#aab6c3;font-size:.84rem;line-height:1.65;margin-bottom:20px}.kx-mode-confirm-btns{display:flex;justify-content:center;gap:10px}.btn-kx-cancel,.btn-kx-amber,.btn-kx-green{border-radius:11px;padding:10px 15px;border:1px solid rgba(255,255,255,.12);font-weight:750;color:#edf3f7;background:#202b37}.btn-kx-amber{background:#f59e0b;border-color:#f59e0b;color:#211300}.btn-kx-green{background:#22c55e;border-color:#22c55e;color:#06150a}.btn-kx-cancel:hover,.btn-kx-amber:hover,.btn-kx-green:hover{filter:brightness(1.08)}
@media(max-width:767px){.env-hero-side{align-items:flex-start;width:100%}.env-mode-warning{max-width:100%}.env-overview{grid-template-columns:1fr}.env-card{padding:20px 17px}.env-save-bar .container-xl{flex-wrap:wrap}.env-save-btn{width:100%}}
body.kx-light .env-hero{background:radial-gradient(circle at 85% 0%,rgba(34,197,94,.14),transparent 32%),linear-gradient(135deg,#fff,#f1f6f3);border-color:rgba(15,23,42,.12);box-shadow:0 14px 38px rgba(15,23,42,.08)}body.kx-light .env-page-title,body.kx-light .env-overview-value,body.kx-light .env-tc-name{color:#16202c}body.kx-light .env-card,body.kx-light .env-overview-card{background:#fff;border-color:rgba(15,23,42,.11);box-shadow:0 8px 24px rgba(15,23,42,.04)}body.kx-light .env-nav a{background:#fff;color:#354256;border-color:rgba(15,23,42,.12)}body.kx-light .env-input{background:#f8fafc;color:#17212d;border-color:rgba(15,23,42,.16)}body.kx-light .env-save-bar{background:rgba(240,244,248,.94)}
</style>
@endpush

@section('content')
<div class="env-wrap">
<div class="container-xl">

    @php $siteMode = $siteMode ?? 'production'; @endphp

    <section class="env-hero d-flex justify-content-between align-items-center flex-wrap gap-4">
        <div class="env-hero-copy">
            <div class="env-eyebrow"><i class="bi bi-shield-lock-fill"></i>Platform configuration</div>
            <h1 class="env-page-title"><i class="bi bi-sliders me-2" style="color:#00cc00"></i>API Keys & Environment</h1>
            <p class="env-page-sub">Manage service credentials, wallet endpoints, messaging, and platform behavior in one secure control room. Saved secrets stay hidden unless you replace or remove them.</p>
        </div>
        <div class="env-hero-side">
            <span class="env-mode-caption">Current site mode</span>
            <button id="kx-mode-toggle-btn-env"
                class="kx-mode-badge {{ $siteMode === 'local' ? 'mode-local' : ($siteMode === 'off' ? 'mode-off' : 'mode-production') }}"
                data-mode="{{ $siteMode }}"
                role="switch" aria-checked="{{ $siteMode === 'local' ? 'true' : 'false' }}"
                aria-label="Runtime mode. Click to switch between production and local mode."
                title="Toggle site mode">
                <span class="kx-mode-status"><span class="kx-mode-dot"></span><span class="kx-mode-label">{{ $siteMode === 'local' ? 'Local Mode' : ($siteMode === 'off' ? 'Maintenance Mode' : 'Production Mode') }}</span></span>
                <span class="kx-mode-switch" aria-hidden="true"><span class="kx-mode-switch-knob"><i class="bi {{ $siteMode === 'local' ? 'bi-code-slash' : ($siteMode === 'off' ? 'bi-pause-fill' : 'bi-shield-check') }}"></i></span></span>
            </button>
            @if($siteMode === 'production')
                <p class="env-mode-warning"><i class="bi bi-shield-check mt-1"></i><span>Production mode is active. Technical diagnostics are hidden from users; local mode reveals them only to administrators.</span></p>
            @else
                <p class="env-mode-warning {{ $siteMode === 'off' ? 'mode-off-warning' : 'mode-local-warning' }}"><i class="bi {{ $siteMode === 'off' ? 'bi-pause-circle-fill' : 'bi-exclamation-triangle-fill' }} mt-1"></i><span>{{ $siteMode === 'off' ? 'Maintenance mode is active. Customers cannot use the platform. Use the switch to return to local or production.' : 'Local mode is active. Detailed diagnostics are visible to admins only. Return to production after troubleshooting.' }}</span></p>
            @endif
        </div>
    </section>

    <div class="env-overview">
        <div class="env-overview-card"><span class="env-overview-icon"><i class="bi bi-key-fill"></i></span><div><div class="env-overview-label">Saved secrets</div><div class="env-overview-value">{{ $configuredSecretCount }} configured</div></div></div>
        <div class="env-overview-card"><span class="env-overview-icon" style="background:rgba(56,189,248,.1);color:#7dd3fc"><i class="bi bi-grid-1x2-fill"></i></span><div><div class="env-overview-label">Configuration groups</div><div class="env-overview-value">{{ count($groups) }} areas</div></div></div>
        <div class="env-overview-card"><span class="env-overview-icon" style="background:rgba(167,139,250,.1);color:#c4b5fd"><i class="bi bi-arrow-repeat"></i></span><div><div class="env-overview-label">Credential handling</div><div class="env-overview-value">Masked by default</div></div></div>
    </div>

    <nav class="env-nav" aria-label="Environment settings sections">
        @foreach($groups as $groupKey=>$fields)
            @php $navMeta=$groupMeta[$groupKey] ?? ['label'=>ucfirst($groupKey),'icon'=>'bi-key']; @endphp
            <a href="#env-group-{{ $groupKey }}"><i class="bi {{ $navMeta['icon'] }}"></i>{{ $navMeta['label'] }}</a>
        @endforeach
    </nav>

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
        <div class="env-card" id="env-group-{{ $groupKey }}" style="--env-accent:{{ $meta['color'] }}">
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
                                value="{{ $field['type'] === 'password' ? '' : old('env.'.$field['key'], $field['value']) }}"
                                placeholder="{{ $field['type'] === 'password' ? ($field['configured'] ? 'Saved securely · enter a new value to replace' : 'Not configured · enter to add') : '' }}"
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
                        @if($field['type'] === 'password')
                        <div class="env-secret-state {{ $field['configured'] ? '' : 'empty' }}"><i class="bi {{ $field['configured'] ? 'bi-shield-lock-fill' : 'bi-exclamation-circle' }}"></i>{{ $field['configured'] ? 'Saved and masked' : 'Not configured' }}</div>
                        @endif
                        @if($field['type'] === 'password' && $field['configured'])
                        <label class="env-secret-clear"><input type="checkbox" name="env_clear[{{ $field['key'] }}]" value="1">Remove this saved secret</label>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            @if($groupKey === 'wallets')
            <div class="env-guide mt-3" style="border-color:rgba(245,158,11,.22);background:rgba(245,158,11,.05)">
                <strong style="color:#fcd38d">Wallet address protection:</strong> Changing a receiving wallet requires the current admin security PIN. Leave the PIN field blank when you are only updating other settings.
                <label for="wallet_change_pin" class="d-block mt-3 mb-1" style="color:#e4e8f0;font-weight:700">Admin security PIN</label>
                <input type="password" name="wallet_change_pin" id="wallet_change_pin" class="env-input" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" autocomplete="current-password" placeholder="4-digit PIN">
            </div>
            @endif

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

    // Site mode confirmation and update. Keep the mode label synced with the server response.
    const modeButton = document.getElementById('kx-mode-toggle-btn-env');
    const modeOverlay = document.getElementById('kx-mode-overlay-env');
    const modeConfirm = document.getElementById('kx-mode-confirm-btn-env');
    const modeText = document.getElementById('kx-mode-confirm-text-env');
    const modeMessage = document.getElementById('kx-mode-post-msg-env');
    const modeEndpoint = @json(route('admin.site-mode.toggle'));
    let requestedMode = null;
    const modeCopy = {
        local: {icon:'🛠️', title:'Switch to Local Mode?', text:'Local mode shows detailed diagnostics to administrators only. Public visitors continue to receive safe error pages. Use it briefly while troubleshooting.', button:'Switch to Local Mode'},
        production: {icon:'🚀', title:'Switch to Production Mode?', text:'Production mode hides technical errors from users and restores the normal live experience.', button:'Switch to Production'}
    };
    modeButton?.addEventListener('click', () => {
        requestedMode = modeButton.dataset.mode === 'local' ? 'production' : 'local';
        const copy = modeCopy[requestedMode];
        document.getElementById('kx-mode-confirm-icon-env').textContent = copy.icon;
        document.getElementById('kx-mode-confirm-title-env').textContent = copy.title;
        modeText.innerHTML = copy.text;
        modeConfirm.textContent = copy.button;
        modeConfirm.className = requestedMode === 'local' ? 'btn-kx-amber' : 'btn-kx-green';
        modeMessage.style.display = 'none';
        modeOverlay.classList.add('active');
        modeConfirm.focus();
    });
    modeOverlay?.addEventListener('click', event => { if (event.target === modeOverlay) modeOverlay.classList.remove('active'); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') modeOverlay?.classList.remove('active'); });
    modeConfirm?.addEventListener('click', async () => {
        if (!requestedMode) return;
        const originalText = modeConfirm.textContent;
        modeConfirm.disabled = true;
        modeConfirm.textContent = 'Switching…';
        try {
            const response = await fetch(modeEndpoint, {method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});
            const result = await response.json();
            if (!response.ok || !result.mode) throw new Error(result.message || 'The site mode could not be changed.');
            const isLocal = result.mode === 'local';
            modeButton.dataset.mode = result.mode;
            modeButton.setAttribute('aria-checked', isLocal ? 'true' : 'false');
            modeButton.classList.toggle('mode-local', isLocal);
            modeButton.classList.toggle('mode-production', !isLocal);
            modeButton.classList.remove('mode-off');
            modeButton.querySelector('.kx-mode-label').textContent = isLocal ? 'Local Mode' : 'Production Mode';
            modeButton.querySelector('.kx-mode-switch-knob i').className = 'bi ' + (isLocal ? 'bi-code-slash' : 'bi-shield-check');
            const warning = document.querySelector('.env-mode-warning');
            if (warning) {
                warning.classList.toggle('mode-local-warning', isLocal);
                warning.classList.remove('mode-off-warning');
                warning.innerHTML = isLocal
                    ? '<i class="bi bi-exclamation-triangle-fill mt-1"></i><span>Local mode is active. Detailed diagnostics are visible to admins only. Return to production after troubleshooting.</span>'
                    : '<i class="bi bi-shield-check mt-1"></i><span>Production mode is active. Technical error details are hidden from users.</span>';
            }
            modeMessage.style.display = 'block';
            modeMessage.style.color = isLocal ? '#fbbf24' : '#86efac';
            modeMessage.textContent = '✓ Runtime switched to ' + (isLocal ? 'Local Mode' : 'Production Mode');
            setTimeout(() => modeOverlay.classList.remove('active'), 1000);
        } catch (error) {
            modeMessage.style.display = 'block';
            modeMessage.style.color = '#fca5a5';
            modeMessage.textContent = error.message || 'Could not switch modes. Please try again.';
        } finally {
            modeConfirm.disabled = false;
            modeConfirm.textContent = originalText;
        }
    });

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
