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
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:1100px">

    <div class="kx-page-header">
        <h1><i class="bi bi-sliders me-2" style="color:var(--kx-green)"></i>Platform Settings & API Keys</h1>
        <p>Manage integration keys for Cloudflare, AI chatbot, payments, and Telegram. Encrypted fields are stored securely.</p>
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

    {{-- Tab bar --}}
    <div class="group-tab">
        <a href="#cloudflare" class="active" onclick="showGroup('cloudflare',this)">☁️ Cloudflare</a>
        <a href="#ai"         onclick="showGroup('ai',this)">🤖 AI Chatbot</a>
        <a href="#payment"    onclick="showGroup('payment',this)">💳 Payment</a>
        <a href="#telegram"   onclick="showGroup('telegram',this)">✈️ Telegram</a>
        <a href="#general"    onclick="showGroup('general',this)">⚙️ General</a>
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
                    @if($row->key === 'cf_enabled')
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
                    <textarea name="_cf_purge_urls" id="cfPurgeUrls" rows="2" style="width:100%;background:rgba(0,0,0,.35);border:1px solid var(--kx-border);color:var(--kx-text);border-radius:8px;padding:.5rem;font-size:.8rem;" placeholder="https://tradewithkay.com/&#10;https://tradewithkay.com/rates"></textarea>
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
                        <div style="color:var(--kx-muted);">Llama 3.3 70B: $0.59/1M tokens — free tier available</div>
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
                            <option value="groq"   {{ ($row->value ?? '') === 'groq' ? 'selected' : '' }}>⚡ Groq (Llama / Mixtral — Free & Fast)</option>
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
                            @foreach(['llama-3.3-70b-versatile','llama-3.1-8b-instant','mixtral-8x7b-32768','llama-3.3-70b-specdec','gemma2-9b-it'] as $m)
                                <option value="{{ $m }}" {{ ($row->value ?? 'llama-3.3-70b-versatile') === $m ? 'selected' : '' }}>{{ $m }}</option>
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
            <h5 class="mb-3 fw-bold" style="color:#4ade80">💳 Payment (Paystack)</h5>
            @foreach($grouped['payment'] ?? [] as $row)
            <div class="field-row">
                <div>
                    <div class="field-label">{{ $row->label ?? $row->key }}
                        @if($row->is_encrypted)<span class="badge-enc">🔒 encrypted</span>@else<span class="badge-plain">visible</span>@endif
                    </div>
                    @if($row->description)<div class="field-desc">{{ $row->description }}</div>@endif
                </div>
                <div class="field-input">
                    @if($row->is_encrypted)
                        <input type="password" name="{{ $row->key }}" placeholder="{{ $row->display_value ?: 'Enter secret key' }}" autocomplete="new-password">
                    @else
                        <input type="text" name="{{ $row->key }}" value="{{ $row->value ?? '' }}" placeholder="{{ $row->label }}">
                    @endif
                </div>
            </div>
            @endforeach
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
                    @else
                        <input type="text" name="{{ $row->key }}" value="{{ $row->value ?? '' }}" placeholder="{{ $row->label }}">
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="d-flex gap-3 mt-2">
            <button type="submit" class="btn px-4 py-2 fw-bold" style="background:var(--kx-green);color:#081108;border-radius:10px;">
                <i class="bi bi-floppy me-1"></i>Save All Settings
            </button>
        </div>
    </form>
</div>

<script>
function showGroup(name, el) {
    document.querySelectorAll('.group-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.group-tab a').forEach(a => a.classList.remove('active'));
    document.getElementById('group-' + name).style.display = '';
    el.classList.add('active');
    event.preventDefault();
}

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
@endsection
