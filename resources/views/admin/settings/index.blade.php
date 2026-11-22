@extends('adminnavlayout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.group-tab{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.group-tab a{padding:.45rem 1.1rem;border-radius:10px;font-size:.82rem;font-weight:600;color:var(--kx-muted);background:var(--kx-card2);text-decoration:none;border:1px solid var(--kx-border);transition:.15s;}
.group-tab a.active{background:var(--kx-green);color:#081108;border-color:var(--kx-green);}
.group-tab a:hover:not(.active){color:var(--kx-text);background:#252d3a;}
.field-row{display:grid;grid-template-columns:200px 1fr;gap:1rem;align-items:start;padding:.9rem 0;border-bottom:1px solid var(--kx-border);}
.field-row:last-child{border-bottom:none;}
.field-label{font-size:.85rem;font-weight:600;color:var(--kx-text);padding-top:.35rem;}
.field-desc{font-size:.75rem;color:var(--kx-muted);margin-top:.2rem;}
.field-input input,.field-input textarea,.field-input select{width:100%;background:rgba(0,0,0,.35);border:1px solid var(--kx-border);color:var(--kx-text);border-radius:8px;padding:.5rem .75rem;font-size:.85rem;}
.field-input input:focus,.field-input textarea:focus,.field-input select:focus{border-color:var(--kx-green);box-shadow:0 0 0 3px rgba(0,204,0,.12);outline:none;background:rgba(0,0,0,.45);}
.field-input input[type="password"]{font-family:monospace;}
.badge-enc{font-size:.68rem;padding:.15em .5em;border-radius:6px;background:rgba(251,191,36,.12);color:#fbbf24;margin-left:.4rem;}
.badge-plain{font-size:.68rem;padding:.15em .5em;border-radius:6px;background:rgba(0,204,0,.1);color:#4ade80;margin-left:.4rem;}
.cf-action-bar{display:flex;gap:.65rem;flex-wrap:wrap;margin-top:1rem;}
.btn-cf{font-size:.8rem;padding:.4rem 1rem;border-radius:8px;font-weight:600;cursor:pointer;border:none;}
.btn-purge{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25);}
.btn-purge:hover{background:rgba(239,68,68,.25);}
.btn-dev{background:rgba(99,102,241,.15);color:#a5b4fc;border:1px solid rgba(99,102,241,.25);}
.btn-dev:hover{background:rgba(99,102,241,.25);}
.btn-attack{background:rgba(239,68,68,.35);color:#fca5a5;border:1px solid rgba(239,68,68,.4);}
.cf-status-pill{display:inline-flex;align-items:center;gap:.4rem;font-size:.78rem;padding:.25rem .8rem;border-radius:20px;}
.cf-status-pill.active{background:rgba(0,204,0,.12);color:#4ade80;}
.cf-status-pill.paused{background:rgba(239,68,68,.12);color:#f87171;}
.stat-mini{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:10px;padding:.8rem 1rem;text-align:center;}
.stat-mini .v{font-size:1.4rem;font-weight:800;color:var(--kx-green);}
.stat-mini .l{font-size:.72rem;color:var(--kx-muted);}
.kx-switch-row{display:flex;align-items:center;gap:.75rem;padding-top:.25rem;}
.kx-switch{position:relative;display:inline-block;width:52px;height:28px;flex-shrink:0;margin:0;}
.kx-switch input{opacity:0;width:0;height:0;position:absolute;}
.kx-slider{position:absolute;inset:0;cursor:pointer;background:#2a3348;border:1px solid var(--kx-border);border-radius:999px;transition:.2s;}
.kx-slider::before{content:'';position:absolute;width:20px;height:20px;left:3px;top:3px;background:#7a8599;border-radius:50%;transition:.2s;}
.kx-switch input:checked + .kx-slider{background:rgba(0,204,0,.25);border-color:var(--kx-green);}
.kx-switch input:checked + .kx-slider::before{transform:translateX(24px);background:var(--kx-green);}
.kx-switch-state{font-size:.78rem;font-weight:700;color:var(--kx-muted);letter-spacing:.04em;}
.kx-switch-state.on{color:var(--kx-green);}
.kx-settings-shell{max-width:1240px;margin-inline:auto;padding-inline:clamp(.65rem,2vw,1.25rem)}
.kx-page-header{position:relative;overflow:hidden;box-shadow:0 18px 48px rgba(0,0,0,.22)}
.kx-page-header:after{content:'';position:absolute;width:260px;height:260px;right:-90px;top:-145px;border-radius:50%;background:rgba(0,204,0,.13);filter:blur(2px);pointer-events:none}
.kx-page-header .header-actions{position:relative;z-index:1;display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1.1rem}
.kx-page-header .header-actions a{border-radius:10px;font-weight:650}
.kx-card{box-shadow:0 12px 32px rgba(0,0,0,.12);transition:border-color .2s,transform .2s}
.kx-card:hover{border-color:rgba(0,204,0,.19)}
.group-tab{position:sticky;top:72px;z-index:20;padding:.55rem;background:rgba(13,17,23,.92);backdrop-filter:blur(14px);border:1px solid var(--kx-border);border-radius:14px;overflow-x:auto;scrollbar-width:thin}
.group-tab a{white-space:nowrap;display:inline-flex;align-items:center;gap:.45rem;padding:.65rem 1rem}
.group-section{scroll-margin-top:140px}
.field-row{grid-template-columns:minmax(180px,260px) minmax(0,1fr)}
.field-input input,.field-input textarea,.field-input select{min-height:44px}
.settings-savebar{position:sticky;bottom:12px;z-index:15;padding:.7rem;background:rgba(22,27,39,.94);border:1px solid var(--kx-border);border-radius:14px;backdrop-filter:blur(12px);box-shadow:0 10px 28px rgba(0,0,0,.28)}
.settings-savebar .btn{min-height:44px}
@media(max-width:767.98px){.kx-settings-shell{padding-inline:.5rem}.kx-page-header{padding:20px}.kx-card{padding:1rem}.field-row{grid-template-columns:1fr;gap:.45rem;padding:.75rem 0}.group-tab{top:58px}.group-tab a{padding:.58rem .8rem}.settings-savebar{bottom:6px}.settings-savebar form,.settings-savebar button{width:100%}}
</style>
@endpush

@section('content')
<div class="container-fluid py-4 kx-settings-shell">

    <div class="kx-page-header">
        <h1><i class="bi bi-sliders me-2" style="color:var(--kx-green)"></i>Platform Settings & API Keys</h1>
        <p>Manage integration keys for Cloudflare, AI chatbot, payments, and Telegram. Encrypted fields are stored securely.</p>
        <div class="header-actions">
            <a class="btn btn-sm btn-outline-light" href="{{ route('admin.logs.index') }}"><i class="bi bi-file-earmark-text me-1"></i>Open error logs · copy errors</a>
            <span class="badge rounded-pill text-bg-success align-self-center"><i class="bi bi-shield-lock me-1"></i>Admin-only settings</span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <section class="kx-card" aria-labelledby="branding-title">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <img src="{{ $siteFaviconUrl ?? asset('Assests/favicon.png') }}" alt="Current platform logo" width="64" height="64" style="border-radius:16px;object-fit:cover;border:1px solid var(--kx-border)" id="brandingPreview">
            <div class="flex-grow-1"><h2 id="branding-title" class="h5 fw-bold mb-1">Platform logo and favicon</h2><p class="field-desc mb-0">Upload once here to update the icon and default brand mark on customer and admin pages. Use a square PNG, JPG, or WebP image from 64×64 to 1024×1024 (max 2 MB).</p></div>
        </div>
        <form method="POST" action="{{ route('admin.settings.branding') }}" enctype="multipart/form-data" class="mt-3 d-flex gap-2 flex-wrap align-items-center">
            @csrf
            <input class="form-control" type="file" name="favicon" accept="image/png,image/jpeg,image/webp" required style="max-width:440px" onchange="if(this.files[0])document.getElementById('brandingPreview').src=URL.createObjectURL(this.files[0])">
            <button class="btn px-4 fw-bold" type="submit" style="background:var(--kx-green);color:#081108;border-radius:10px"><i class="bi bi-image me-1"></i>Save platform logo</button>
            @error('favicon')<span class="text-danger small">{{ $message }}</span>@enderror
        </form>
    </section>
    <section class="kx-card" aria-labelledby="loader-theme-title">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div style="width:52px;height:52px;border-radius:16px;display:grid;place-items:center;background:linear-gradient(145deg,#123f28,#0c1a13);color:#69ef9a;font-size:1.4rem"><i class="bi bi-stars"></i></div>
            <div class="flex-grow-1"><h2 id="loader-theme-title" class="h5 fw-bold mb-1">Platform loading experience</h2><p class="field-desc mb-0">Choose one of ten designs. “Original KayXchange intro” keeps the branded KayXchange logo, “Trade with Kay” headline, and ₿ Ξ ₮ animation. Your saved selection is used on the opening splash and shared page loader.</p></div>
        </div>
        <form method="POST" action="{{ route('admin.settings.loader-theme') }}" class="mt-3 d-flex gap-2 flex-wrap align-items-center">
            @csrf
            <select class="form-select" name="loader_theme" id="loaderThemeSelect" aria-label="Platform loader theme" style="max-width:300px">
                <option value="classic" {{ ($siteLoaderTheme ?? 'classic') === 'classic' ? 'selected' : '' }}>Original KayXchange intro · Classic</option>
                <option value="festive" {{ ($siteLoaderTheme ?? 'classic') === 'festive' ? 'selected' : '' }}>Festive 2027 intro</option>
                <option value="christmas" {{ ($siteLoaderTheme ?? 'classic') === 'christmas' ? 'selected' : '' }}>Christmas loader</option>
                <option value="aurora" {{ ($siteLoaderTheme ?? 'classic') === 'aurora' ? 'selected' : '' }}>Aurora Glow</option>
                <option value="orbit" {{ ($siteLoaderTheme ?? 'classic') === 'orbit' ? 'selected' : '' }}>Neon Orbit</option>
                <option value="gold" {{ ($siteLoaderTheme ?? 'classic') === 'gold' ? 'selected' : '' }}>Golden Luxe</option>
                <option value="ocean" {{ ($siteLoaderTheme ?? 'classic') === 'ocean' ? 'selected' : '' }}>Ocean Wave</option>
                <option value="cosmic" {{ ($siteLoaderTheme ?? 'classic') === 'cosmic' ? 'selected' : '' }}>Cosmic Pulse</option>
                <option value="matrix" {{ ($siteLoaderTheme ?? 'classic') === 'matrix' ? 'selected' : '' }}>Matrix Grid</option>
                <option value="neon" {{ ($siteLoaderTheme ?? 'classic') === 'neon' ? 'selected' : '' }}>Neon Pulse</option>
            </select>
            <button class="btn btn-outline-light px-4 fw-bold" type="button" id="previewLoaderTheme" style="border-radius:10px"><i class="bi bi-eye me-1"></i>Preview</button>
            <button class="btn px-4 fw-bold" type="submit" style="background:var(--kx-green);color:#081108;border-radius:10px"><i class="bi bi-check2-circle me-1"></i>Save selected loader</button>
        </form>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const preview = document.getElementById('previewLoaderTheme');
            const selector = document.getElementById('loaderThemeSelect');
            const overlay = document.getElementById('loader');
            if (!preview || !selector || !overlay) return;
            preview.addEventListener('click', function () {
                const theme = selector.value;
                overlay.className = 'kx-loader kx-loader--' + theme;
                const labels = {classic:['Trade with Kay','Fast · Secure · Best Rates'],festive:['Welcome to 2027','A bright new year. A little festive magic.'],christmas:['Merry Christmas','Warm wishes from KayXchange.'],aurora:['Aurora Glow','A calm, luminous loading experience.'],orbit:['Neon Orbit','Your KayXchange experience is coming into orbit.'],gold:['Golden Luxe','A refined loading experience, prepared for you.'],ocean:['Ocean Wave','Smoothly preparing your secure experience.'],cosmic:['Cosmic Pulse','Connecting your KayXchange universe.'],matrix:['Matrix Grid','Secure connection in progress.'],neon:['Neon Pulse','Powering up your KayXchange experience.']};
                const copy = labels[theme] || labels.classic;
                window.kxLoading?.show(copy[0], copy[1]);
                window.setTimeout(() => window.kxLoading?.hide(), 2600);
            });
        });
        </script>
    </section>

    <section class="kx-card" aria-labelledby="homepage-scroll-fx-title">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div style="width:52px;height:52px;border-radius:16px;display:grid;place-items:center;background:linear-gradient(145deg,#123f28,#0c1a13);color:#69ef9a;font-size:1.4rem"><i class="bi bi-currency-bitcoin"></i></div>
            <div class="flex-grow-1"><h2 id="homepage-scroll-fx-title" class="h5 fw-bold mb-1">Homepage scroll reveal</h2><p class="field-desc mb-0">Reveal homepage sections with a subtle falling Bitcoin matrix effect as visitors scroll. This setting affects the homepage only and honors reduced-motion preferences.</p></div>
        </div>
        <form method="POST" action="{{ route('admin.settings.homepage-scroll-effects') }}" class="mt-3 d-flex gap-3 flex-wrap align-items-center">
            @csrf
            <input type="hidden" name="enabled" value="0">
            <label class="kx-switch" aria-label="Toggle homepage Bitcoin scroll reveal">
                <input type="checkbox" name="enabled" value="1" {{ ($homepageScrollFxEnabled ?? false) ? 'checked' : '' }} onchange="this.form.submit()">
                <span class="kx-slider"></span>
            </label>
            <span class="kx-switch-state {{ ($homepageScrollFxEnabled ?? false) ? 'on' : '' }}">{{ ($homepageScrollFxEnabled ?? false) ? 'ON · homepage only' : 'OFF' }}</span>
            <noscript><button class="btn btn-outline-light" type="submit">Save animation setting</button></noscript>
        </form>
    </section>

    {{-- Tab bar --}}
    <div class="group-tab">
        <a href="#cloudflare" class="active" onclick="showGroup('cloudflare',this,event)"><i class="bi bi-cloud"></i> Cloudflare</a>
        <a href="#ai" onclick="showGroup('ai',this,event)"><i class="bi bi-stars"></i> AI Chatbot</a>
        <a href="#payment" onclick="showGroup('payment',this,event)"><i class="bi bi-credit-card"></i> Payment</a>
        <a href="#telegram" onclick="showGroup('telegram',this,event)"><i class="bi bi-telegram"></i> Telegram</a>
        <a href="#general" onclick="showGroup('general',this,event)"><i class="bi bi-gear"></i> General</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('POST')

        {{-- ─── CLOUDFLARE ─────────────────────────────────────────────── --}}
        <div id="group-cloudflare" class="kx-card group-section">
            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                <h5 class="mb-0 fw-bold" style="color:#f6821f">☁️ Cloudflare</h5>
                @if(isset($cfStatus) && !isset($cfStatus['error']))
                    <span class="cf-status-pill {{ $cfStatus['paused'] ? 'paused' : 'active' }}">
                        <span style="width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                        {{ $cfStatus['paused'] ? 'Paused' : 'Active' }} — {{ $cfStatus['name'] ?? '' }}
                    </span>
                    <span class="ms-auto" style="font-size:.78rem;color:var(--kx-muted);">Plan: {{ $cfStatus['plan']['name'] ?? 'Free' }}</span>
                @elseif(isset($cfStatus['error']))
                    <span class="cf-status-pill paused">⚠️ {{ $cfStatus['error'] }}</span>
                @endif
            </div>

            @if(isset($cfStatus) && !isset($cfStatus['error']))
            <div class="row g-2 mb-3">
                <div class="col-4"><div class="stat-mini"><div class="v">{{ number_format($cfStatus['analytics']['totals']['requests']['all'] ?? 0) }}</div><div class="l">Requests (all time)</div></div></div>
                <div class="col-4"><div class="stat-mini"><div class="v">{{ number_format($cfStatus['analytics']['totals']['bandwidth']['all'] ?? 0, 0, '.', ',') }}</div><div class="l">Bandwidth (bytes)</div></div></div>
                <div class="col-4"><div class="stat-mini"><div class="v" style="color:#f87171">{{ number_format($cfStatus['analytics']['totals']['threats']['all'] ?? 0) }}</div><div class="l">Threats Blocked</div></div></div>
            </div>
            @endif

            @foreach($grouped['cloudflare'] ?? [] as $row)
            <div class="field-row">
                <div>
                    <div class="field-label">
                        {{ $row->label ?? $row->key }}
                        @if($row->is_encrypted)<span class="badge-enc">🔒 encrypted</span>@else<span class="badge-plain">visible</span>@endif
                    </div>
                    @if($row->description)<div class="field-desc">{{ $row->description }}</div>@endif
                </div>
                <div class="field-input">
                    @if($row->key === 'homepage_captcha_mode')
                        <select name="{{ $row->key }}">
                            <option value="off" {{ ($row->value ?? 'custom') === 'off' ? 'selected' : '' }}>Off</option>
                            <option value="custom" {{ ($row->value ?? 'custom') === 'custom' ? 'selected' : '' }}>Custom challenge</option>
                            <option value="turnstile" {{ ($row->value ?? 'custom') === 'turnstile' ? 'selected' : '' }}>Cloudflare Turnstile</option>
                        </select>
                    @elseif($row->key === 'runtime_mode')
                        <select name="{{ $row->key }}">
                            <option value="production" {{ ($row->value ?? 'production') === 'production' ? 'selected' : '' }}>Production · safe errors for customers</option>
                            <option value="local" {{ ($row->value ?? '') === 'local' ? 'selected' : '' }}>Local · detailed diagnostics for admins only</option>
                            <option value="off" {{ ($row->value ?? '') === 'off' ? 'selected' : '' }}>Off · maintenance page for customers</option>
                        </select>
                    @elseif($row->key === 'site_maintenance')
                        <select name="{{ $row->key }}">
                            <option value="0" {{ ($row->value ?? '0') !== '1' ? 'selected' : '' }}>Live</option>
                            <option value="1" {{ ($row->value ?? '0') === '1' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    @elseif($row->key === 'blocked_countries')
                        @php
                            $decodedCountries = json_decode((string) $row->value, true);
                            $selectedCountries = is_array($decodedCountries) ? $decodedCountries : [];
                            $extraCountries = array_values(array_diff($selectedCountries, array_keys($countryOptions)));
                        @endphp
                        <select name="blocked_countries[]" multiple size="7" aria-label="Countries to block">
                            @foreach($countryOptions as $code => $countryName)
                                <option value="{{ $code }}" {{ in_array($code, $selectedCountries, true) ? 'selected' : '' }}>{{ $countryName }} ({{ $code }})</option>
                            @endforeach
                        </select>
                        <input type="text" name="blocked_country_codes" class="mt-2" value="{{ implode(', ', $extraCountries) }}" placeholder="Other ISO codes: RU, KP, BY">
                    @elseif($row->key === 'cf_enabled')
                        <select name="{{ $row->key }}">
                            <option value="1" {{ ($row->value ?? '') == '1' ? 'selected' : '' }}>✅ Enabled</option>
                            <option value="0" {{ ($row->value ?? '0') == '0' ? 'selected' : '' }}>❌ Disabled</option>
                        </select>
                    @elseif($row->is_encrypted)
                        <input type="password" name="{{ $row->key }}" placeholder="{{ $row->display_value ?: 'Enter value (leave blank to keep existing)' }}" autocomplete="new-password">
                    @else
                        <input type="text" name="{{ $row->key }}" value="{{ $row->value ?? '' }}" placeholder="{{ $row->label }}">
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Cloudflare Quick Actions --}}
            <div class="mt-4">
                <div class="field-label mb-2">Quick Actions</div>
                <div class="cf-action-bar">
                    <button type="button" class="btn-cf btn-purge" onclick="cfAction('purge_all')">
                        🔄 Purge All Cache
                    </button>
                    <button type="button" class="btn-cf btn-dev" onclick="cfAction('dev_mode_on')">
                        🛠️ Dev Mode ON
                    </button>
                    <button type="button" class="btn-cf btn-dev" onclick="cfAction('dev_mode_off')">
                        ✅ Dev Mode OFF
                    </button>
                    <button type="button" class="btn-cf btn-attack" onclick="cfAction('under_attack_on')">
                        🛡️ Under Attack Mode ON
                    </button>
                </div>
                <div class="mt-2">
                    <div class="field-label mb-1" style="font-size:.78rem;">Purge specific URLs (one per line)</div>
                    <textarea id="cfPurgeUrls" rows="2" style="width:100%;background:rgba(0,0,0,.35);border:1px solid var(--kx-border);color:var(--kx-text);border-radius:8px;padding:.5rem;font-size:.8rem;" placeholder="https://tradewithkay.com/&#10;https://tradewithkay.com/rates"></textarea>
                    <button type="button" class="btn-cf btn-purge mt-1" onclick="cfAction('purge_url')">🔄 Purge These URLs</button>
                </div>
                <div id="cfActionResult" class="mt-2" style="font-size:.82rem;color:#4ade80;display:none;"></div>
            </div>
        </div>

        {{-- ─── AI CHATBOT ──────────────────────────────────────────────── --}}
        <div id="group-ai" class="kx-card group-section" style="display:none;">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="mb-0 fw-bold" style="color:#a78bfa">🤖 AI Trading Chatbot</h5>
                <a href="{{ route('admin.settings.ai-usage') }}" class="btn btn-sm" style="background:rgba(167,139,250,.12);color:#c4b5fd;border:1px solid rgba(167,139,250,.2);border-radius:8px;font-size:.78rem;font-weight:600;">
                    <i class="bi bi-speedometer2 me-1"></i>View Limits & Usage →
                </a>
            </div>

            {{-- Provider comparison banner --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div style="background:rgba(0,204,0,.06);border:1px solid rgba(0,204,0,.15);border-radius:10px;padding:.9rem 1rem;font-size:.78rem;">
                        <div style="font-weight:700;color:#4ade80;margin-bottom:.3rem;">🌐 OpenAI</div>
                        <div style="color:var(--kx-muted);">GPT-4o-mini: $0.15/1M tokens — requires paid credit</div>
                        <div style="color:var(--kx-muted);">Free tier: 3 RPM, 200 requests/day</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div style="background:rgba(249,115,22,.06);border:1px solid rgba(249,115,22,.15);border-radius:10px;padding:.9rem 1rem;font-size:.78rem;">
                        <div style="font-weight:700;color:#fb923c;margin-bottom:.3rem;">⚡ Groq (Free & Fast)</div>
                        <div style="color:var(--kx-muted);">GPT-OSS 120B: $0.15/1M tokens — free tier available</div>
                        <div style="color:var(--kx-muted);">Free tier: 30 RPM, ~1,000 requests/day, ultra fast</div>
                    </div>
                </div>
            </div>

            @foreach($grouped['ai'] ?? [] as $row)
            <div class="field-row">
                <div>
                    <div class="field-label">
                        {{ $row->label ?? $row->key }}
                        @if($row->is_encrypted)<span class="badge-enc">🔒 encrypted</span>@else<span class="badge-plain">visible</span>@endif
                    </div>
                    @if($row->description)<div class="field-desc">{{ $row->description }}</div>@endif
                </div>
                <div class="field-input">
                    @if($row->key === 'ai_provider')
                        <select name="{{ $row->key }}" id="aiProviderSelect">
                            <option value="openai" {{ ($row->value ?? 'openai') === 'openai' ? 'selected' : '' }}>🌐 OpenAI (GPT models)</option>
                            <option value="groq"   {{ ($row->value ?? '') === 'groq' ? 'selected' : '' }}>⚡ Groq (GPT-OSS / Qwen — Free & Fast)</option>
                        </select>
                    @elseif($row->key === 'ai_chatbot_enabled')
                        <select name="{{ $row->key }}">
                            <option value="1" {{ ($row->value ?? '1') == '1' ? 'selected' : '' }}>✅ Enabled (show chatbot)</option>
                            <option value="0" {{ ($row->value ?? '') == '0' ? 'selected' : '' }}>❌ Disabled (hide chatbot)</option>
                        </select>
                    @elseif($row->key === 'openai_model')
                        <select name="{{ $row->key }}">
                            @foreach(['gpt-4o-mini','gpt-4o','gpt-4-turbo','gpt-3.5-turbo','o1-mini'] as $m)
                                <option value="{{ $m }}" {{ ($row->value ?? 'gpt-4o-mini') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    @elseif($row->key === 'groq_model')
                        <select name="{{ $row->key }}">
                            @foreach(['openai/gpt-oss-120b','openai/gpt-oss-20b','qwen/qwen3.8-27b'] as $m)
                                <option value="{{ $m }}" {{ ($row->value ?? 'openai/gpt-oss-120b') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    @elseif($row->key === 'ai_system_prompt')
                        <textarea name="{{ $row->key }}" rows="6" style="font-family:monospace;font-size:.78rem;">{{ $row->value ?? '' }}</textarea>
                    @elseif($row->is_encrypted)
                        <input type="password" name="{{ $row->key }}" placeholder="{{ $row->display_value ?: 'Enter API key' }}" autocomplete="new-password">
                    @else
                        <input type="text" name="{{ $row->key }}" value="{{ $row->value ?? '' }}" placeholder="{{ $row->label }}">
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Live test buttons --}}
            <div class="d-flex gap-2 mt-3 flex-wrap">
                <button type="button" class="btn btn-sm" style="background:rgba(0,204,0,.1);color:#4ade80;border:1px solid rgba(0,204,0,.2);border-radius:8px;font-size:.78rem;" onclick="testAiProvider('openai')">
                    🌐 Test OpenAI Connection
                </button>
                <button type="button" class="btn btn-sm" style="background:rgba(249,115,22,.1);color:#fb923c;border:1px solid rgba(249,115,22,.2);border-radius:8px;font-size:.78rem;" onclick="testAiProvider('groq')">
                    ⚡ Test Groq Connection
                </button>
            </div>
            <div id="ai-test-result" class="mt-2" style="font-size:.8rem;display:none;"></div>
        </div>

        <script>
        async function testAiProvider(provider) {
            const el = document.getElementById('ai-test-result');
            el.style.display = '';
            el.style.color   = '#fbbf24';
            el.textContent   = `Testing ${provider}…`;
            const url = provider === 'groq' ? '{{ route("admin.settings.groq-test") }}' : '{{ route("admin.settings.ai-test") }}';
            try {
                const r    = await fetch(url);
                const data = await r.json();
                if (data.ok) {
                    el.style.color = '#4ade80';
                    el.textContent = `✅ ${provider === 'groq' ? 'Groq' : 'OpenAI'} connected! Model: ${data.model}. RPM limit: ${data.rpm_limit || '—'}. TPM: ${data.tpm_limit ? Number(data.tpm_limit).toLocaleString() : '—'}`;
                } else {
                    el.style.color = '#f87171';
                    el.textContent = `❌ ${data.error || 'Connection failed.'}`;
                }
            } catch(e) {
                el.style.color = '#f87171';
                el.textContent = '❌ Network error. Save settings first then try again.';
            }
        }
        </script>

        {{-- ─── PAYMENT ─────────────────────────────────────────────────── --}}
        <div id="group-payment" class="kx-card group-section" style="display:none;">
            <h5 class="mb-3 fw-bold" style="color:#4ade80">💳 Payments & Swaps</h5>
            @foreach($grouped['payment'] ?? [] as $row)
            <div class="field-row">
                <div>
                    <div class="field-label">{{ $row->key === 'convert_fee_percent' ? 'Platform Swap Fee (%)' : ($row->key === 'cryptomus_convert_enabled' ? 'Enable Swap Feature' : ($row->label ?? $row->key)) }}
                        @if($row->is_encrypted)<span class="badge-enc">🔒 encrypted</span>@else<span class="badge-plain">visible</span>@endif
                    </div>
                    @if($row->key === 'convert_fee_percent')
                        <div class="field-desc">Your platform fee is added to NOWPayments’ provider swap fee. Users see one combined fee percentage and amount.</div>
                    @elseif($row->key === 'cryptomus_convert_enabled')<div class="field-desc">Show or hide the crypto swap page for customers.</div>
                    @elseif($row->description)<div class="field-desc">{{ $row->description }}</div>@endif
                </div>
                <div class="field-input">
                    @if(in_array($row->key, ['cryptomus_enabled', 'cryptomus_convert_enabled', 'nowpayments_enabled', 'nowpayments_sandbox', 'nowpayments_conversion_enabled', 'nowpayments_crypto_withdrawals_enabled']))
                        @php
                            $toggleVal = $row->value ?? (in_array($row->key, ['cryptomus_convert_enabled', 'nowpayments_sandbox'], true) ? '1' : '0');
                            $toggleOn  = $toggleVal === '1';
                        @endphp
                        <div class="kx-switch-row">
                            <input type="hidden" name="{{ $row->key }}" id="hid_{{ $row->key }}" value="{{ $toggleOn ? '1' : '0' }}">
                            <label class="kx-switch">
                                <input type="checkbox" {{ $toggleOn ? 'checked' : '' }}
                                    onchange="document.getElementById('hid_{{ $row->key }}').value=this.checked?'1':'0';var s=document.getElementById('state_{{ $row->key }}');s.textContent=this.checked?'ON':'OFF';s.classList.toggle('on',this.checked);">
                                <span class="kx-slider"></span>
                            </label>
                            <span class="kx-switch-state {{ $toggleOn ? 'on' : '' }}" id="state_{{ $row->key }}">{{ $toggleOn ? 'ON' : 'OFF' }}</span>
                        </div>
                    @elseif($row->key === 'crypto_deposit_provider')
                        @php $dp = $row->value ?? 'auto'; @endphp
                        <select name="{{ $row->key }}">
                            <option value="auto" {{ $dp === 'auto' ? 'selected' : '' }}>Automatic — use a configured provider</option>
                            <option value="nowpayments" {{ $dp === 'nowpayments' ? 'selected' : '' }}>NOWPayments</option>
                            <option value="cryptomus" {{ $dp === 'cryptomus' ? 'selected' : '' }}>Cryptomus</option>
                        </select>
                    @elseif($row->key === 'convert_mode')
                        @php $cm = $row->value ?? 'cryptomus'; @endphp
                        <select name="{{ $row->key }}">
                            <option value="compare"   {{ $cm === 'compare'   ? 'selected' : '' }}>📊 Compare — price comparison only, swaps disabled</option>
                            <option value="cryptomus" {{ $cm === 'cryptomus' ? 'selected' : '' }}>⚡ Cryptomus — automated via Cryptomus API</option>
                            <option value="nowpayments" {{ $cm === 'nowpayments' ? 'selected' : '' }}>🔁 NOWPayments Custody — swap from user crypto wallets</option>
                            <option value="manual"    {{ $cm === 'manual'    ? 'selected' : '' }}>🖐 Manual — user submits wallet, admin processes</option>
                        </select>
                    @elseif($row->key === 'convert_fee_percent')
                        <input type="number" name="{{ $row->key }}" value="{{ $row->value ?? '0.5' }}" min="0" max="19.5" step="0.1">
                    @elseif($row->key === 'convert_min_usd')
                        <input type="number" name="{{ $row->key }}" value="{{ $row->value ?? '1' }}" min="0.01" max="100000000" step="0.01">
                    @elseif($row->key === 'convert_max_usd')
                        <input type="number" name="{{ $row->key }}" value="{{ $row->value ?? '1000' }}" min="0.01" max="100000000" step="0.01">
                    @elseif($row->key === 'swap_supported_coins')
                        <input type="text" name="{{ $row->key }}" value="{{ $row->value ?? 'BTC,ETH,USDT,USDC,BNB,SOL,XRP,DOGE,LTC,TRX,ADA,TON' }}" placeholder="BTC,ETH,USDT,LTC">
                        <div class="field-desc mt-2">Available price and NOWPayments network mappings: <span class="text-success">BTC · ETH · USDT · USDC · BNB · SOL · XRP · DOGE · LTC · TRX · ADA · TON</span>. Add only symbols with a live/fallback market price and a supported payout/swap network.</div>
                    @elseif($row->key === 'convert_instructions')
                        <textarea name="{{ $row->key }}" rows="3" style="font-size:.82rem;">{{ $row->value ?? '' }}</textarea>
                    @elseif($row->key === 'nowpayments_api_key')
                        <div class="input-group">
                            <input type="password" name="{{ $row->key }}" id="nowpayments-api-key-field" placeholder="{{ $row->display_value ?: 'Enter API key' }}" autocomplete="new-password" spellcheck="false" oninput="credentialReplacementTyped(this)">
                            <button type="button" class="btn btn-outline-secondary" id="nowpayments-api-key-preview" onclick="previewNowPaymentsApiKey()" aria-label="Preview saved NOWPayments API key" aria-pressed="false">
                                <i class="bi bi-eye me-1" aria-hidden="true"></i>Preview
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="copyNowPaymentsCredential('api_key','nowpayments-api-key-preview-status')"><i class="bi bi-clipboard me-1" aria-hidden="true"></i>Copy</button>
                        </div>
                        <div id="nowpayments-api-key-preview-status" class="field-desc mt-1" role="status" aria-live="polite">The saved key stays masked until you preview it.</div>
                        <button type="button" class="btn btn-sm btn-outline-success mt-2 me-2" onclick="saveNowPaymentsCredential('api_key','nowpayments-api-key-preview-status')"><i class="bi bi-floppy me-1"></i>Save API key</button>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearNowPaymentsCredential('api_key','nowpayments-api-key-preview-status')"><i class="bi bi-trash3 me-1"></i>Clear saved API key</button>
                    @elseif($row->key === 'nowpayments_ipn_secret')
                        <div class="input-group">
                            <input type="password" name="{{ $row->key }}" id="nowpayments-ipn-secret-field" placeholder="{{ $row->display_value ?: 'Enter IPN secret' }}" autocomplete="new-password" spellcheck="false" oninput="credentialReplacementTyped(this)">
                            <button type="button" class="btn btn-outline-secondary" id="nowpayments-ipn-secret-preview" onclick="previewNowPaymentsIpnSecret()" aria-label="Preview saved NOWPayments IPN secret" aria-pressed="false">
                                <i class="bi bi-eye me-1" aria-hidden="true"></i>Preview
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="copyNowPaymentsCredential('ipn_secret','nowpayments-ipn-secret-preview-status')"><i class="bi bi-clipboard me-1" aria-hidden="true"></i>Copy</button>
                        </div>
                        <div id="nowpayments-ipn-secret-preview-status" class="field-desc mt-1" role="status" aria-live="polite">The saved IPN secret stays masked until you preview it.</div>
                        <button type="button" class="btn btn-sm btn-outline-success mt-2 me-2" onclick="saveNowPaymentsCredential('ipn_secret','nowpayments-ipn-secret-preview-status')"><i class="bi bi-floppy me-1"></i>Save IPN secret</button>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearNowPaymentsCredential('ipn_secret','nowpayments-ipn-secret-preview-status')"><i class="bi bi-trash3 me-1"></i>Clear saved IPN secret</button>
                    @elseif($row->key === 'cardbincheck_api_key')
                        <input type="password" name="{{ $row->key }}" id="cardbincheck-api-key-field" placeholder="{{ $row->display_value ?: 'Paste CardBinCheck API key' }}" autocomplete="new-password" spellcheck="false" oninput="credentialReplacementTyped(this)">
                        <button type="button" class="btn btn-sm btn-outline-success mt-2" id="cardbincheck-api-key-save" onclick="saveCardBinCheckApiKey()"><i class="bi bi-floppy me-1"></i>Save CardBinCheck API key</button>
                        <span id="cardbincheck-api-key-status" class="field-desc d-block mt-2" role="status" aria-live="polite">{{ $row->display_value ? 'A key is saved encrypted. Enter a new key to replace it.' : 'No database key saved yet.' }}</span>
                        <label class="field-desc d-inline-flex align-items-center gap-2 mt-2"><input type="checkbox" name="clear_settings[]" value="{{ $row->key }}" onchange="confirmCredentialClear(this)"> Clear saved CardBinCheck API key</label>
                    @elseif($row->is_encrypted)
                        <input type="password" name="{{ $row->key }}" placeholder="{{ $row->display_value ?: 'Enter secret key' }}" autocomplete="new-password" oninput="credentialReplacementTyped(this)">
                        <label class="field-desc d-inline-flex align-items-center gap-2 mt-2"><input type="checkbox" name="clear_settings[]" value="{{ $row->key }}" onchange="confirmCredentialClear(this)"> Clear saved {{ strtolower($row->label ?? 'credential') }}</label>
                    @else
                        <input type="text" name="{{ $row->key }}" value="{{ $row->value ?? '' }}" placeholder="{{ $row->label }}">
                    @endif
                </div>
            </div>
            @endforeach
            <div class="mt-3 p-3 rounded-3" style="background:rgba(255,255,255,.03);border:1px solid var(--kx-border)">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-success" id="nowpayments-test-btn" onclick="testNowPaymentsKey()"><i class="bi bi-plug me-1"></i>Test saved NOWPayments API key</button>
                    <span id="nowpayments-test-result" class="small" role="status">Save the API key and environment setting before testing.</span>
                </div>
                <div class="field-desc mt-2">This checks the saved API key against the selected Sandbox or Live endpoint without creating a payment.</div>
                <div class="d-flex flex-wrap gap-2 mt-3" aria-label="Active NOWPayments credential sources">
                    @foreach(['api_key' => 'API key', 'ipn_secret' => 'IPN secret'] as $credentialName => $credentialLabel)
                        @php $credentialSource = $nowPaymentsCredentialStatus[$credentialName] ?? 'missing'; @endphp
                        <span id="nowpayments-{{ $credentialName }}-source" class="badge rounded-pill {{ $credentialSource === 'missing' ? 'text-bg-secondary' : ($credentialSource === 'database' ? 'text-bg-success' : 'text-bg-info') }}">
                            {{ $credentialLabel }}: {{ $credentialSource === 'database' ? 'Database' : ($credentialSource === 'environment' ? 'Environment fallback' : 'Not configured') }}
                        </span>
                    @endforeach
                </div>
                <div class="field-desc mt-2">The database value takes priority. If it is cleared, the service falls back to <code>NOWPAYMENTS_API_KEY</code> and <code>NOWPAYMENTS_IPN_SECRET</code> from the server environment. A server environment key remains active until it is removed there.</div>
                @php
                    $nowPaymentsIpnUrl = route('nowpayments.webhook');
                    $nowPaymentsIpnScheme = parse_url($nowPaymentsIpnUrl, PHP_URL_SCHEME);
                    $nowPaymentsIpnHost = strtolower((string) parse_url($nowPaymentsIpnUrl, PHP_URL_HOST));
                    $nowPaymentsIpnSecretConfigured = (string) \App\Models\AdminSetting::getSetting('nowpayments_ipn_secret', '') !== '';
                    $nowPaymentsIpnReady = $nowPaymentsIpnScheme === 'https'
                        && $nowPaymentsIpnHost !== ''
                        && !in_array($nowPaymentsIpnHost, ['localhost', '127.0.0.1', '::1', 'example.com', 'www.example.com'], true)
                        && !str_ends_with($nowPaymentsIpnHost, '.test');
                @endphp
                <div class="mt-3 p-3 rounded-3" style="background:rgba(56,189,248,.055);border:1px solid rgba(56,189,248,.16)">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="fw-bold" style="color:#d9f2ff"><i class="bi bi-broadcast-pin me-1"></i>NOWPayments IPN webhook</div>
                            <div class="field-desc mt-1">Use this callback URL in NOWPayments notifications. New crypto deposit requests also send this URL automatically.</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge {{ $nowPaymentsIpnReady ? 'bg-success' : 'bg-warning text-dark' }}">{{ $nowPaymentsIpnReady ? 'HTTPS endpoint ready' : 'Check APP_URL / HTTPS' }}</span>
                            <span class="badge {{ $nowPaymentsIpnSecretConfigured ? 'bg-success' : 'bg-secondary' }}">{{ $nowPaymentsIpnSecretConfigured ? 'IPN secret saved' : 'IPN secret not set' }}</span>
                        </div>
                    </div>
                    <div class="input-group mt-3">
                        <input type="text" class="form-control" id="nowpayments-ipn-url" value="{{ $nowPaymentsIpnUrl }}" readonly aria-label="NOWPayments IPN callback URL">
                        <button class="btn btn-outline-info" type="button" onclick="copyNowPaymentsIpnUrl()"><i class="bi bi-copy me-1"></i>Copy URL</button>
                        <button class="btn btn-outline-success" type="button" id="nowpayments-ipn-health-btn" onclick="testNowPaymentsIpnEndpoint()"><i class="bi bi-heart-pulse me-1"></i>Test endpoint</button>
                    </div>
                    <div id="nowpayments-ipn-health-result" class="field-desc mt-2" role="status" aria-live="polite">Endpoint check confirms public reachability; provider signatures are still verified on each payment callback.</div>
                    <ol class="field-desc mt-3 mb-0 ps-3">
                        <li>Save the NOWPayments IPN Secret above.</li>
                        <li>Copy this URL into the NOWPayments dashboard under Payment Settings → IPN callback URL.</li>
                        <li>Keep the site on HTTPS and allow NOWPayments callbacks through your firewall or CDN.</li>
                    </ol>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-3">
                    <button type="button" class="btn btn-sm btn-outline-info" id="nowpayments-custody-test-btn" onclick="testNowPaymentsCustody()"><i class="bi bi-shield-check me-1"></i>Test Custody credentials</button>
                    <span id="nowpayments-custody-test-result" class="small" role="status">Save the account email and password first.</span>
                </div>
                <div class="field-desc mt-2">Custody account credentials are encrypted in the settings database and used only on the server to obtain short-lived swap tokens. Enter the separate platform swap fee above; customers see only the combined fee estimate.</div>
            </div>
        </div>

        {{-- ─── TELEGRAM ────────────────────────────────────────────────── --}}
        <div id="group-telegram" class="kx-card group-section" style="display:none;">
            <h5 class="mb-3 fw-bold" style="color:#38bdf8">✈️ Telegram</h5>
            @foreach($grouped['telegram'] ?? [] as $row)
            <div class="field-row">
                <div>
                    <div class="field-label">{{ $row->label ?? $row->key }}
                        @if($row->is_encrypted)<span class="badge-enc">🔒 encrypted</span>@else<span class="badge-plain">visible</span>@endif
                    </div>
                    @if($row->description)<div class="field-desc">{{ $row->description }}</div>@endif
                </div>
                <div class="field-input">
                    @if($row->is_encrypted)
                        <input type="password" name="{{ $row->key }}" placeholder="{{ $row->display_value ?: 'Enter bot token' }}" autocomplete="new-password">
                    @else
                        <input type="text" name="{{ $row->key }}" value="{{ $row->value ?? '' }}" placeholder="{{ $row->label }}">
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- ─── GENERAL ─────────────────────────────────────────────────── --}}
        <div id="group-general" class="kx-card group-section" style="display:none;">
            <h5 class="mb-3 fw-bold">⚙️ General</h5>
            <div class="alert alert-info py-2 small mb-3"><i class="bi bi-camera-video me-1"></i>Paste optional YouTube or Vimeo links into the four guide video fields below. Save settings to publish them on <code>/resources</code>; unsafe or unsupported video hosts are rejected.</div>
            @foreach($grouped['general'] ?? [] as $row)
            <div class="field-row">
                <div>
                    <div class="field-label">{{ $row->label ?? $row->key }}
                        @if($row->is_encrypted)<span class="badge-enc">🔒 encrypted</span>@else<span class="badge-plain">visible</span>@endif
                    </div>
                    @if($row->description)<div class="field-desc">{{ $row->description }}</div>@endif
                </div>
                <div class="field-input">
                    @if($row->key === 'site_maintenance')
                        <select name="{{ $row->key }}">
                            <option value="0" {{ ($row->value ?? '0') == '0' ? 'selected' : '' }}>✅ Live (normal)</option>
                            <option value="1" {{ ($row->value ?? '') == '1' ? 'selected' : '' }}>🔧 Maintenance Mode</option>
                        </select>
                    @elseif($row->key === 'tools_page_enabled')
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="tools-page-enabled" value="1" aria-label="Enable or disable the user tools page" data-save-url="{{ route('admin.settings.tools-page') }}" data-csrf="{{ csrf_token() }}" {{ (string)($row->value ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="tools-page-enabled">Show Tools to users</label>
                        </div>
                        <span class="field-desc d-block mt-2">When off, Tools is removed from user navigation and direct page/API requests are unavailable.</span>
                        <span id="tools-page-save-status" class="field-desc d-block mt-2" role="status" aria-live="polite"></span>
                    @else
                        <input type="text" name="{{ $row->key }}" value="{{ $row->value ?? '' }}" placeholder="{{ $row->label }}">
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="settings-savebar d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
            <span class="small text-secondary"><i class="bi bi-info-circle me-1"></i>Blank credential inputs keep the saved value. Use dedicated clear controls to erase credentials.</span>
            <button type="submit" class="btn px-4 py-2 fw-bold" style="background:var(--kx-green);color:#081108;border-radius:10px;">
                <i class="bi bi-floppy me-1"></i>Save settings
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('tools-page-enabled')?.addEventListener('change', async (event) => {
    const toggle = event.currentTarget;
    const status = document.getElementById('tools-page-save-status');
    const previousValue = toggle.dataset.savedValue || (toggle.checked ? '0' : '1');
    const enabled = toggle.checked ? '1' : '0';
    toggle.disabled = true;
    status.textContent = 'Saving tools page availability…';
    try {
        const response = await fetch(toggle.dataset.saveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'X-CSRF-TOKEN': toggle.dataset.csrf,
                'Accept': 'application/json',
            },
            body: new URLSearchParams({enabled}),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) throw new Error(data.message || `Save failed (HTTP ${response.status}).`);
        toggle.dataset.savedValue = enabled;
        status.textContent = data.message;
        status.style.color = '#75ef9a';
    } catch (error) {
        toggle.checked = previousValue === '1';
        status.textContent = error.message || 'Could not save the tools page setting.';
        status.style.color = '#ff8585';
    } finally {
        toggle.disabled = false;
    }
});

function confirmCredentialClear(checkbox) {
    if (!checkbox.checked) return;
    if (!window.confirm('Clear this saved credential? Any feature using it may stop working until you save a replacement.')) {
        checkbox.checked = false;
        return;
    }
    const credentialField = Array.from(document.querySelectorAll('.field-input input[name]'))
        .find(input => input.name === checkbox.value);
    if (credentialField) {
        credentialField.value = '';
        credentialField.type = 'password';
        delete credentialField.dataset.loadedSavedKey;
        delete credentialField.dataset.loadedSavedSecret;
    }
    const previewButton = checkbox.value === 'nowpayments_api_key'
        ? document.getElementById('nowpayments-api-key-preview')
        : checkbox.value === 'nowpayments_ipn_secret'
            ? document.getElementById('nowpayments-ipn-secret-preview')
            : null;
    if (previewButton) {
        previewButton.innerHTML = '<i class="bi bi-eye me-1" aria-hidden="true"></i>Preview';
        previewButton.setAttribute('aria-pressed', 'false');
    }
}

function credentialReplacementTyped(input) {
    if (!input.value.trim()) return;
    const clearCheckbox = Array.from(document.querySelectorAll('input[name="clear_settings[]"]'))
        .find(checkbox => checkbox.value === input.name);
    if (clearCheckbox?.checked) clearCheckbox.checked = false;
}

async function saveCardBinCheckApiKey() {
    const field = document.getElementById('cardbincheck-api-key-field');
    const button = document.getElementById('cardbincheck-api-key-save');
    const status = document.getElementById('cardbincheck-api-key-status');
    const apiKey = field?.value.trim() || '';
    if (!apiKey) {
        status.textContent = 'Paste your CardBinCheck API key, then save it.';
        status.className = 'field-desc d-block mt-2 text-warning';
        field?.focus();
        return;
    }
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Saving securely…';
    status.textContent = 'Saving encrypted key to platform settings…';
    status.className = 'field-desc d-block mt-2';
    try {
        const response = await fetch(@json(route('admin.settings.cardbincheck-credential')), {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({api_key: apiKey}),
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.ok) throw new Error(result.message || `Save failed (HTTP ${response.status}).`);
        field.value = '';
        field.placeholder = 'Saved encrypted · paste a new key to replace';
        status.textContent = result.message;
        status.className = 'field-desc d-block mt-2 text-success';
    } catch (error) {
        status.textContent = error.message || 'Could not save the CardBinCheck API key. Try again.';
        status.className = 'field-desc d-block mt-2 text-danger';
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-floppy me-1"></i>Save CardBinCheck API key';
    }
}

function showGroup(name, el, ev) {
    const validGroups = ['cloudflare', 'ai', 'payment', 'telegram', 'general'];
    if (!validGroups.includes(name)) name = 'cloudflare';
    document.querySelectorAll('.group-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.group-tab a').forEach(a => a.classList.remove('active'));
    const section = document.getElementById('group-' + name);
    if (!section) return;
    section.style.display = '';
    el = el || document.querySelector(`.group-tab a[href="#${name}"]`);
    el?.classList.add('active');
    ev?.preventDefault();
    history.replaceState(null, '', '#' + name);
}

document.addEventListener('DOMContentLoaded', () => {
    const initialGroup = window.location.hash.replace(/^#/, '');
    if (initialGroup) showGroup(initialGroup);
});

async function cfAction(action) {
    const resultEl = document.getElementById('cfActionResult');
    resultEl.style.display = '';
    resultEl.style.color   = '#fbbf24';
    resultEl.textContent   = 'Processing…';

    const body = new FormData();
    body.append('_token', '{{ csrf_token() }}');
    body.append('action', action);

    if (action === 'purge_url') {
        body.append('urls', document.getElementById('cfPurgeUrls').value);
    }
    if (action === 'under_attack_on') {
        body.set('action', 'set_security');
    }

    try {
        const r = await fetch('{{ route("admin.settings.cloudflare-action") }}', {
            method: 'POST', body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await r.json();
        resultEl.style.color = data.success ? '#4ade80' : '#f87171';
        resultEl.textContent = (data.success ? '✅ ' : '❌ ') + (data.message ?? 'Done.');
    } catch (e) {
        resultEl.style.color = '#f87171';
        resultEl.textContent = '❌ Network error. Make sure Cloudflare credentials are saved first.';
    }
}
</script>

        <script>
        async function testNowPaymentsKey() {
            const button = document.getElementById('nowpayments-test-btn');
            const result = document.getElementById('nowpayments-test-result');
            button.disabled = true;
            result.textContent = 'Checking saved key against NOWPayments…';
            result.className = 'small text-secondary';
            try {
                const response = await fetch(@json(route('admin.settings.nowpayments-test')), {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json'},
                });
                const data = await response.json();
                result.textContent = data.message || 'NOWPayments key check failed.';
                result.className = 'small ' + (response.ok ? 'text-success' : 'text-warning');
            } catch (_) {
                result.textContent = 'Could not reach the server. Save settings and try again.';
                result.className = 'small text-warning';
            } finally { button.disabled = false; }
        }

        async function copyNowPaymentsCredential(credential, statusId) {
            const status = document.getElementById(statusId);
            if (!status) return;
            status.textContent = 'Requesting a secure copy…';
            try {
                const response = await fetch(@json(route('admin.settings.nowpayments-credential-copy')), {
                    method: 'POST', credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json', 'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({credential})
                });
                const data = await response.json();
                if (!response.ok || !data.ok || !data.value) throw new Error(data.message || 'Could not copy the saved credential.');
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(data.value);
                } else {
                    const temporaryField = document.createElement('textarea');
                    temporaryField.value = data.value;
                    temporaryField.setAttribute('readonly', '');
                    temporaryField.style.position = 'fixed';
                    temporaryField.style.opacity = '0';
                    document.body.appendChild(temporaryField);
                    temporaryField.select();
                    const copied = document.execCommand('copy');
                    temporaryField.remove();
                    if (!copied) throw new Error('Clipboard access was blocked. Use Preview to copy manually.');
                }
                status.textContent = 'Copied. Admin security alerts were sent.';
            } catch (error) {
                status.textContent = error.message || 'Copy failed. Use Preview to reveal the saved value.';
            }
        }

        async function previewNowPaymentsApiKey() {
            const field = document.getElementById('nowpayments-api-key-field');
            const button = document.getElementById('nowpayments-api-key-preview');
            const status = document.getElementById('nowpayments-api-key-preview-status');
            if (!field || !button || !status) return;

            if (field.type === 'text') {
                field.type = 'password';
                button.innerHTML = '<i class="bi bi-eye me-1" aria-hidden="true"></i>Preview';
                button.setAttribute('aria-pressed', 'false');
                status.textContent = 'The key is hidden again.';
                return;
            }

            // If the admin typed a replacement key, preview it locally. Only
            // request the server-side saved value when the input is empty.
            if (field.value && field.dataset.loadedSavedKey !== 'true') {
                field.type = 'text';
                button.innerHTML = '<i class="bi bi-eye-slash me-1" aria-hidden="true"></i>Hide';
                button.setAttribute('aria-pressed', 'true');
                status.textContent = 'Previewing the unsaved key. Hide it when finished.';
                return;
            }

            button.disabled = true;
            status.textContent = 'Loading the saved key securely…';
            try {
                const response = await fetch(@json(route('admin.settings.nowpayments-api-key-preview')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({})
                });
                const data = await response.json();
                if (!response.ok || !data.ok || !data.api_key) throw new Error(data.message || 'The saved API key could not be loaded.');
                field.value = data.api_key;
                field.dataset.loadedSavedKey = 'true';
                field.type = 'text';
                field.focus();
                field.select();
                button.innerHTML = '<i class="bi bi-eye-slash me-1" aria-hidden="true"></i>Hide';
                button.setAttribute('aria-pressed', 'true');
                status.textContent = 'Saved API key revealed. Hide it when you are finished.';
            } catch (error) {
                status.textContent = error.message || 'Could not preview the saved key. Try again.';
            } finally {
                button.disabled = false;
            }
        }

        async function clearNowPaymentsCredential(credential, statusId) {
            const isApiKey = credential === 'api_key';
            const label = isApiKey ? 'NOWPayments API key' : 'NOWPayments IPN secret';
            const status = document.getElementById(statusId);
            const button = status?.parentElement?.querySelector('button.btn-outline-danger');
            if (!status || !['api_key', 'ipn_secret'].includes(credential)) return;
            if (!window.confirm(`Permanently erase the saved ${label} from the database?`)) return;

            if (button) button.disabled = true;
            status.style.color = '#fbbf24';
            status.textContent = 'Erasing saved credential…';
            try {
                const response = await fetch(@json(route('admin.settings.nowpayments-credential-clear')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ credential })
                });
                const data = await response.json();
                if (!response.ok || !data.ok) throw new Error(data.message || 'The credential could not be cleared.');

                const field = document.getElementById(isApiKey ? 'nowpayments-api-key-field' : 'nowpayments-ipn-secret-field');
                const preview = document.getElementById(isApiKey ? 'nowpayments-api-key-preview' : 'nowpayments-ipn-secret-preview');
                if (field) {
                    field.value = '';
                    field.type = 'password';
                    delete field.dataset.loadedSavedKey;
                    delete field.dataset.loadedSavedSecret;
                }
                if (preview) {
                    preview.innerHTML = '<i class="bi bi-eye me-1" aria-hidden="true"></i>Preview';
                    preview.setAttribute('aria-pressed', 'false');
                }
                const sourceBadge = document.getElementById(`nowpayments-${credential}-source`);
                if (sourceBadge) {
                    sourceBadge.className = `badge rounded-pill ${data.source === 'environment' ? 'text-bg-info' : 'text-bg-secondary'}`;
                    sourceBadge.textContent = `${isApiKey ? 'API key' : 'IPN secret'}: ${data.source === 'environment' ? 'Environment fallback' : 'Not configured'}`;
                }
                status.style.color = data.source === 'environment' ? '#7dd3fc' : '#4ade80';
                status.textContent = data.message;
            } catch (error) {
                status.style.color = '#f87171';
                status.textContent = error.message || `Could not clear the ${label}. Please try again.`;
            } finally {
                if (button) button.disabled = false;
            }
        }

        async function saveNowPaymentsCredential(credential, statusId) {
            const isApiKey = credential === 'api_key';
            const label = isApiKey ? 'NOWPayments API key' : 'NOWPayments IPN secret';
            const field = document.getElementById(isApiKey ? 'nowpayments-api-key-field' : 'nowpayments-ipn-secret-field');
            const status = document.getElementById(statusId);
            const button = status?.parentElement?.querySelector('button.btn-outline-success');
            if (!field || !status || !['api_key', 'ipn_secret'].includes(credential)) return;
            if (!field.value.trim()) {
                status.style.color = '#f87171';
                status.textContent = `Enter the replacement ${label} first.`;
                field.focus();
                return;
            }

            if (button) button.disabled = true;
            status.style.color = '#fbbf24';
            status.textContent = 'Encrypting and saving credential…';
            try {
                const response = await fetch(@json(route('admin.settings.nowpayments-credential-save')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ credential, value: field.value })
                });
                const data = await response.json();
                if (!response.ok || !data.ok) throw new Error(data.message || `Could not save ${label}.`);

                field.value = '';
                field.type = 'password';
                field.placeholder = data.masked;
                delete field.dataset.loadedSavedKey;
                delete field.dataset.loadedSavedSecret;
                const preview = document.getElementById(isApiKey ? 'nowpayments-api-key-preview' : 'nowpayments-ipn-secret-preview');
                if (preview) {
                    preview.innerHTML = '<i class="bi bi-eye me-1" aria-hidden="true"></i>Preview';
                    preview.setAttribute('aria-pressed', 'false');
                }
                const sourceBadge = document.getElementById(`nowpayments-${credential}-source`);
                if (sourceBadge) {
                    sourceBadge.className = 'badge rounded-pill text-bg-success';
                    sourceBadge.textContent = `${isApiKey ? 'API key' : 'IPN secret'}: Database`;
                }
                status.style.color = '#4ade80';
                status.textContent = data.message;
            } catch (error) {
                status.style.color = '#f87171';
                status.textContent = error.message || `Could not save ${label}. Please retry.`;
            } finally {
                if (button) button.disabled = false;
            }
        }

        async function previewNowPaymentsIpnSecret() {
            const field = document.getElementById('nowpayments-ipn-secret-field');
            const button = document.getElementById('nowpayments-ipn-secret-preview');
            const status = document.getElementById('nowpayments-ipn-secret-preview-status');
            if (!field || !button || !status) return;

            if (field.type === 'text') {
                field.type = 'password';
                button.innerHTML = '<i class="bi bi-eye me-1" aria-hidden="true"></i>Preview';
                button.setAttribute('aria-pressed', 'false');
                status.textContent = 'The IPN secret is hidden again.';
                return;
            }
            if (field.value && field.dataset.loadedSavedSecret !== 'true') {
                field.type = 'text';
                button.innerHTML = '<i class="bi bi-eye-slash me-1" aria-hidden="true"></i>Hide';
                button.setAttribute('aria-pressed', 'true');
                status.textContent = 'Previewing the unsaved IPN secret. Hide it when finished.';
                return;
            }

            button.disabled = true;
            status.textContent = 'Loading the saved IPN secret securely…';
            try {
                const response = await fetch(@json(route('admin.settings.nowpayments-ipn-secret-preview')), {
                    method: 'POST', credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json', 'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({})
                });
                const data = await response.json();
                if (!response.ok || !data.ok || !data.ipn_secret) throw new Error(data.message || 'The saved IPN secret could not be loaded.');
                field.value = data.ipn_secret;
                field.dataset.loadedSavedSecret = 'true';
                field.type = 'text';
                field.focus();
                field.select();
                button.innerHTML = '<i class="bi bi-eye-slash me-1" aria-hidden="true"></i>Hide';
                button.setAttribute('aria-pressed', 'true');
                status.textContent = 'Saved IPN secret revealed. Hide it when finished.';
            } catch (error) {
                status.textContent = error.message || 'Could not preview the saved IPN secret. Try again.';
            } finally {
                button.disabled = false;
            }
        }

        async function copyNowPaymentsIpnUrl() {
            const field = document.getElementById('nowpayments-ipn-url');
            const result = document.getElementById('nowpayments-ipn-health-result');
            try {
                await navigator.clipboard.writeText(field.value);
                result.textContent = 'IPN callback URL copied.';
            } catch (_) {
                field.select();
                document.execCommand('copy');
                result.textContent = 'IPN callback URL copied.';
            }
        }

        async function testNowPaymentsIpnEndpoint() {
            const button = document.getElementById('nowpayments-ipn-health-btn');
            const result = document.getElementById('nowpayments-ipn-health-result');
            button.disabled = true;
            result.textContent = 'Checking the public webhook endpoint…';
            try {
                const response = await fetch(@json(route('nowpayments.webhook.health')), {
                    method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                const data = await response.json();
                if (!response.ok || !data.ok) throw new Error(data.message || 'The webhook endpoint did not respond successfully.');
                result.textContent = 'Endpoint is reachable. NOWPayments can POST signed notifications to the URL above.';
            } catch (error) {
                result.textContent = error.message || 'Could not reach the webhook endpoint.';
            } finally {
                button.disabled = false;
            }
        }

        async function testNowPaymentsCustody() {
            const button = document.getElementById('nowpayments-custody-test-btn');
            const result = document.getElementById('nowpayments-custody-test-result');
            button.disabled = true;
            result.textContent = 'Checking Custody access…';
            try {
                const response = await fetch(@json(route('admin.settings.nowpayments-custody-test')), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                const data = await response.json();
                result.textContent = data.message || (data.ok ? 'Custody access verified.' : 'Custody check failed.');
                result.style.color = data.ok ? '#4ade80' : '#f87171';
            } catch (e) {
                result.textContent = 'Could not reach NOWPayments. Check hosting network access.';
                result.style.color = '#f87171';
            } finally {
                button.disabled = false;
            }
        }
        </script>
@endsection
