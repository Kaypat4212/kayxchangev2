@extends('adminnavlayout')

@push('styles')
<style>
:root{--kx-green:#00cc00;--kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;--kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;}
body{background:var(--kx-dark);color:var(--kx-text);}
.kx-page-header{background:linear-gradient(135deg,#0a1628,#0d1f1a);border:1px solid var(--kx-border);border-radius:16px;padding:24px 28px;margin-bottom:24px;}
.kx-page-header h1{font-size:1.35rem;font-weight:700;color:#fff;margin:0 0 4px;}
.kx-page-header p{color:var(--kx-muted);font-size:.875rem;margin:0;}
.kx-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:1.5rem;margin-bottom:1.25rem;}
.kx-card h5{font-size:.9rem;font-weight:700;margin-bottom:1rem;}
.stat-box{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1rem 1.25rem;text-align:center;}
.stat-box .v{font-size:1.7rem;font-weight:800;color:var(--kx-green);}
.stat-box .l{font-size:.72rem;color:var(--kx-muted);margin-top:2px;}
.model-card{background:var(--kx-card2);border:1px solid var(--kx-border);border-radius:12px;padding:1rem 1.2rem;position:relative;transition:.15s;}
.model-card.current{border-color:var(--kx-green);box-shadow:0 0 0 1px rgba(0,204,0,.3);}
.model-card .tier-badge{font-size:.65rem;padding:.2em .6em;border-radius:10px;font-weight:700;margin-left:.5rem;}
.ctx-bar{height:6px;border-radius:4px;background:rgba(255,255,255,.08);overflow:hidden;margin-top:.5rem;}
.ctx-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--kx-green),#009900);}
.limit-row{display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--kx-border);font-size:.82rem;}
.limit-row:last-child{border-bottom:none;}
.limit-key{color:var(--kx-muted);}
.limit-val{font-weight:700;font-family:monospace;color:var(--kx-text);}
.pill{display:inline-block;padding:.2em .7em;border-radius:12px;font-size:.72rem;font-weight:700;}
.pill-green{background:rgba(0,204,0,.12);color:#4ade80;}
.pill-yellow{background:rgba(251,191,36,.12);color:#fbbf24;}
.pill-red{background:rgba(239,68,68,.12);color:#f87171;}
.pill-blue{background:rgba(56,189,248,.12);color:#7dd3fc;}
.pill-purple{background:rgba(167,139,250,.12);color:#c4b5fd;}
.pill-gray{background:rgba(148,163,184,.1);color:#94a3b8;}
.live-box{background:rgba(0,0,0,.35);border:1px solid var(--kx-border);border-radius:12px;padding:1rem;font-size:.8rem;}
.live-box .lk{color:var(--kx-muted);font-size:.75rem;}
.live-box .lv{font-family:monospace;color:var(--kx-text);}
.meter{height:8px;border-radius:4px;background:rgba(255,255,255,.08);overflow:hidden;flex:1;}
.meter-fill{height:100%;border-radius:4px;transition:width .5s;}
.test-btn{background:rgba(0,204,0,.12);border:1px solid rgba(0,204,0,.2);color:#4ade80;border-radius:8px;padding:.4rem 1.1rem;font-size:.82rem;font-weight:600;cursor:pointer;}
.test-btn:hover{background:rgba(0,204,0,.22);}
.tier-table td,.tier-table th{padding:.45rem .65rem;font-size:.78rem;border-bottom:1px solid var(--kx-border);}
.tier-table th{color:var(--kx-muted);font-weight:600;}
.tier-table tr.highlight td{background:rgba(0,204,0,.04);}
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width:1100px">

    <div class="kx-page-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-speedometer2 me-2" style="color:#a78bfa"></i>AI Limits & Usage</h1>
                <p>OpenAI model token limits, rate limits by tier, real-time API status, and your chatbot usage stats.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('admin.settings.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.07);color:var(--kx-muted);border-radius:8px;">
                    <i class="bi bi-gear me-1"></i>Settings
                </a>
                <button class="test-btn" onclick="testConnection()">
                    <i class="bi bi-plug me-1"></i>Test Live Connection
                </button>
            </div>
        </div>
    </div>

    {{-- Connection status bar --}}
    @if(!$apiKey)
    <div class="alert mb-3" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:10px;font-size:.85rem;color:#fca5a5;">
        <i class="bi bi-exclamation-triangle me-1"></i>
        No OpenAI API key configured. <a href="{{ route('admin.settings.index') }}" style="color:#f87171;font-weight:700;">Add it in Settings →</a>
    </div>
    @elseif($connectionOk)
    <div class="alert mb-3" style="background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.15);border-radius:10px;font-size:.85rem;color:#4ade80;">
        <i class="bi bi-check-circle me-1"></i>
        API key is valid. Connected to OpenAI.
        @if($rateLimitHeaders['org_id'] ?? null)
            Org: <code style="color:#86efac;">{{ $rateLimitHeaders['org_id'] }}</code>
        @endif
    </div>
    @else
    <div class="alert mb-3" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:10px;font-size:.85rem;color:#fca5a5;">
        <i class="bi bi-x-circle me-1"></i>
        Could not connect to OpenAI. Check your API key.
    </div>
    @endif

    {{-- ── LIVE RATE LIMITS (from actual headers) ── --}}
    @if($rateLimitHeaders && !isset($rateLimitHeaders['error']) && ($rateLimitHeaders['rpm_limit'] || $rateLimitHeaders['tpm_limit']))
    <div class="kx-card">
        <h5 style="color:#38bdf8"><i class="bi bi-activity me-2"></i>Live Rate Limits <span class="pill pill-blue ms-2">From API Headers</span></h5>
        <p style="font-size:.78rem;color:var(--kx-muted);margin-bottom:1rem;">These are your <strong>actual</strong> limits for the current model based on your OpenAI tier.</p>
        <div class="row g-3">
            {{-- Requests per minute --}}
            <div class="col-md-6">
                <div class="live-box">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="lk">Requests per Minute (RPM)</span>
                        <span class="pill pill-green">{{ $rateLimitHeaders['rpm_reset'] ? 'Resets: '.$rateLimitHeaders['rpm_reset'] : 'Live' }}</span>
                    </div>
                    @php
                        $rpmLimit = (int)($rateLimitHeaders['rpm_limit'] ?? 0);
                        $rpmRem   = (int)($rateLimitHeaders['rpm_remaining'] ?? $rpmLimit);
                        $rpmUsed  = max(0, $rpmLimit - $rpmRem);
                        $rpmPct   = $rpmLimit > 0 ? round(($rpmUsed / $rpmLimit) * 100) : 0;
                    @endphp
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="lv" style="min-width:80px">{{ number_format($rpmRem) }} / {{ number_format($rpmLimit) }}</span>
                        <div class="meter">
                            <div class="meter-fill" style="width:{{ $rpmPct }}%;background:{{ $rpmPct > 80 ? '#ef4444' : ($rpmPct > 50 ? '#f59e0b' : '#00cc00') }}"></div>
                        </div>
                        <span class="lk" style="min-width:40px;text-align:right">{{ $rpmPct }}%</span>
                    </div>
                    <div class="lk mt-1">Remaining requests this minute</div>
                </div>
            </div>

            {{-- Tokens per minute --}}
            <div class="col-md-6">
                <div class="live-box">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="lk">Tokens per Minute (TPM)</span>
                        <span class="pill pill-purple">{{ $rateLimitHeaders['tpm_reset'] ? 'Resets: '.$rateLimitHeaders['tpm_reset'] : 'Live' }}</span>
                    </div>
                    @php
                        $tpmLimit = (int)($rateLimitHeaders['tpm_limit'] ?? 0);
                        $tpmRem   = (int)($rateLimitHeaders['tpm_remaining'] ?? $tpmLimit);
                        $tpmUsed  = max(0, $tpmLimit - $tpmRem);
                        $tpmPct   = $tpmLimit > 0 ? round(($tpmUsed / $tpmLimit) * 100) : 0;
                    @endphp
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="lv" style="min-width:80px">{{ number_format($tpmRem) }} / {{ number_format($tpmLimit) }}</span>
                        <div class="meter">
                            <div class="meter-fill" style="width:{{ $tpmPct }}%;background:{{ $tpmPct > 80 ? '#ef4444' : ($tpmPct > 50 ? '#f59e0b' : '#00cc00') }}"></div>
                        </div>
                        <span class="lk" style="min-width:40px;text-align:right">{{ $tpmPct }}%</span>
                    </div>
                    <div class="lk mt-1">Remaining tokens this minute</div>
                </div>
            </div>
        </div>

        {{-- Live test result panel --}}
        <div id="live-test-result" class="mt-3" style="display:none;">
            <div class="live-box" id="live-test-inner"></div>
        </div>
    </div>
    @else
    <div class="kx-card" style="border-color:rgba(167,139,250,.2);">
        <h5 style="color:#a78bfa"><i class="bi bi-activity me-2"></i>Live Rate Limits</h5>
        <p style="font-size:.82rem;color:var(--kx-muted);">
            @if(!$apiKey)
                Configure your OpenAI API key to see live rate limit data.
            @else
                Click <strong>"Test Live Connection"</strong> above to pull live rate limit headers from OpenAI.
            @endif
        </p>
        <div id="live-test-result" class="mt-2" style="display:none;">
            <div class="live-box" id="live-test-inner"></div>
        </div>
    </div>
    @endif

    {{-- ── YOUR CHATBOT USAGE ── --}}
    <div class="kx-card">
        <h5 style="color:var(--kx-green)"><i class="bi bi-bar-chart me-2"></i>Your Chatbot Usage</h5>
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3"><div class="stat-box"><div class="v">{{ number_format($localStats['today']) }}</div><div class="l">Messages Today</div></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><div class="v">{{ number_format($localStats['this_week']) }}</div><div class="l">This Week</div></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><div class="v">{{ number_format($localStats['this_month']) }}</div><div class="l">This Month</div></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><div class="v">{{ number_format($localStats['total']) }}</div><div class="l">All Time</div></div></div>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="live-box">
                    <div class="lk">Sessions Today</div>
                    <div class="lv" style="font-size:1.3rem;font-weight:700;color:var(--kx-green)">{{ $localStats['sessions_today'] }}</div>
                    <div class="lk mt-1">Avg {{ $localStats['avg_per_session'] }} messages/session</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="live-box">
                    <div class="lk">Estimated Cost Today</div>
                    <div class="lv" style="font-size:1.3rem;font-weight:700;color:#fbbf24">${{ $localStats['est_cost_today'] }}</div>
                    <div class="lk mt-1">~${{ $localStats['est_cost_month'] }} this month</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="live-box">
                    <div class="lk">Current Model</div>
                    <div class="lv" style="font-size:1rem;font-weight:700;color:#a78bfa">{{ $currentModel }}</div>
                    @php $cm = $modelInfo[$currentModel] ?? null; @endphp
                    @if($cm)
                    <div class="lk mt-1">${{ $cm['input_cost'] }}/1M in · ${{ $cm['output_cost'] }}/1M out</div>
                    @endif
                </div>
            </div>
        </div>
        @if($localStats['top_hours']->isNotEmpty())
        <div class="mt-3">
            <div style="font-size:.78rem;color:var(--kx-muted);margin-bottom:.4rem;">Peak hours today</div>
            <div class="d-flex gap-2">
            @foreach($localStats['top_hours'] as $h)
                <span class="pill pill-purple">{{ str_pad($h->hr, 2, '0', STR_PAD_LEFT) }}:00 — {{ $h->cnt }} msg</span>
            @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── ALL MODEL LIMITS ── --}}
    <div class="kx-card">
        <h5><i class="bi bi-cpu me-2" style="color:#f59e0b"></i>Model Token Limits & Pricing</h5>
        <p style="font-size:.78rem;color:var(--kx-muted);margin-bottom:1rem;">Context window = max tokens the model can see at once. Output = max tokens it can generate per response.</p>
        <div class="row g-3">
            @foreach($modelInfo as $slug => $m)
            <div class="col-md-6">
                <div class="model-card {{ $slug === $currentModel ? 'current' : '' }}">
                    <div class="d-flex align-items-center mb-1 flex-wrap gap-1">
                        <span style="font-weight:700;color:{{ $m['color'] }};font-size:.9rem;">{{ $m['name'] }}</span>
                        <span class="tier-badge pill" style="background:{{ $m['color'] }}1a;color:{{ $m['color'] }}">{{ $m['tier'] }}</span>
                        @if($slug === $currentModel)
                            <span class="pill pill-green ms-auto">✓ Active</span>
                        @endif
                    </div>
                    <div style="font-size:.75rem;color:var(--kx-muted);margin-bottom:.75rem;">{{ $m['desc'] }}</div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div style="font-size:.68rem;color:var(--kx-muted)">Context Window</div>
                            <div style="font-size:.92rem;font-weight:700;color:var(--kx-text)">{{ number_format($m['context']) }} <span style="font-size:.68rem;color:var(--kx-muted)">tokens</span></div>
                        </div>
                        <div class="col-6">
                            <div style="font-size:.68rem;color:var(--kx-muted)">Max Output</div>
                            <div style="font-size:.92rem;font-weight:700;color:var(--kx-text)">{{ number_format($m['max_output']) }} <span style="font-size:.68rem;color:var(--kx-muted)">tokens</span></div>
                        </div>
                    </div>

                    {{-- Context bar (relative to 128K) --}}
                    <div class="ctx-bar">
                        <div class="ctx-bar-fill" style="width:{{ round(($m['context'] / 128000) * 100) }}%;background:{{ $m['color'] }}66"></div>
                    </div>

                    <div class="d-flex gap-3 mt-2">
                        <div>
                            <div style="font-size:.65rem;color:var(--kx-muted)">Input price</div>
                            <div style="font-size:.8rem;font-weight:700;color:#4ade80">${{ $m['input_cost'] }}<span style="font-size:.65rem;color:var(--kx-muted)">/1M tokens</span></div>
                        </div>
                        <div>
                            <div style="font-size:.65rem;color:var(--kx-muted)">Output price</div>
                            <div style="font-size:.8rem;font-weight:700;color:#fbbf24">${{ $m['output_cost'] }}<span style="font-size:.65rem;color:var(--kx-muted)">/1M tokens</span></div>
                        </div>
                        <div class="ms-auto">
                            <div style="font-size:.65rem;color:var(--kx-muted)">~cost/msg</div>
                            @php
                                $perMsg = number_format((($m['input_cost']*300 + $m['output_cost']*250) / 1_000_000), 6);
                            @endphp
                            <div style="font-size:.8rem;font-weight:700;color:#94a3b8">${{ $perMsg }}</div>
                        </div>
                    </div>

                    @if($slug !== $currentModel)
                    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="openai_model" value="{{ $slug }}">
                        <button type="submit" style="font-size:.72rem;background:rgba(255,255,255,.06);border:1px solid var(--kx-border);color:var(--kx-muted);border-radius:6px;padding:.2rem .7rem;cursor:pointer;">Switch to {{ $m['name'] }}</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── RATE LIMITS BY TIER ── --}}
    <div class="kx-card">
        <h5><i class="bi bi-layers me-2" style="color:#f59e0b"></i>OpenAI Rate Limits by Account Tier</h5>
        <p style="font-size:.78rem;color:var(--kx-muted);margin-bottom:.75rem;">
            Your tier is determined by how much you've spent. Check your tier at
            <a href="https://platform.openai.com/settings/organization/limits" target="_blank" style="color:#4ade80;">platform.openai.com → Limits</a>.
            RPM = Requests per Minute. TPM = Tokens per Minute. RPD = Requests per Day.
        </p>
        <div class="table-responsive">
            <table class="tier-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>Tier</th>
                        <th>Spend Requirement</th>
                        <th>RPM (gpt-4o-mini)</th>
                        <th>RPD</th>
                        <th>TPM</th>
                        <th>Suitable for</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="pill pill-gray">Free</span></td>
                        <td style="color:var(--kx-muted)">$0 (new account)</td>
                        <td style="font-family:monospace;font-weight:700">3</td>
                        <td style="font-family:monospace">200</td>
                        <td style="font-family:monospace">40,000</td>
                        <td style="color:var(--kx-muted);font-size:.75rem">Testing only</td>
                    </tr>
                    <tr>
                        <td><span class="pill pill-blue">Tier 1</span></td>
                        <td style="color:var(--kx-muted)">$5 paid</td>
                        <td style="font-family:monospace;font-weight:700">500</td>
                        <td style="font-family:monospace">10,000</td>
                        <td style="font-family:monospace">200,000</td>
                        <td style="color:var(--kx-muted);font-size:.75rem">Small apps, low traffic</td>
                    </tr>
                    <tr class="highlight">
                        <td><span class="pill pill-green">Tier 2</span></td>
                        <td style="color:var(--kx-muted)">$50 paid + 7 days</td>
                        <td style="font-family:monospace;font-weight:700">5,000</td>
                        <td style="font-family:monospace">—</td>
                        <td style="font-family:monospace">2,000,000</td>
                        <td style="color:#4ade80;font-size:.75rem">✓ Recommended for KayXchange</td>
                    </tr>
                    <tr>
                        <td><span class="pill pill-yellow">Tier 3</span></td>
                        <td style="color:var(--kx-muted)">$100 paid + 7 days</td>
                        <td style="font-family:monospace;font-weight:700">5,000</td>
                        <td style="font-family:monospace">—</td>
                        <td style="font-family:monospace">4,000,000</td>
                        <td style="color:var(--kx-muted);font-size:.75rem">Medium traffic</td>
                    </tr>
                    <tr>
                        <td><span class="pill pill-purple">Tier 4</span></td>
                        <td style="color:var(--kx-muted)">$250 paid + 14 days</td>
                        <td style="font-family:monospace;font-weight:700">10,000</td>
                        <td style="font-family:monospace">—</td>
                        <td style="font-family:monospace">10,000,000</td>
                        <td style="color:var(--kx-muted);font-size:.75rem">High traffic apps</td>
                    </tr>
                    <tr>
                        <td><span class="pill" style="background:rgba(239,68,68,.12);color:#f87171">Tier 5</span></td>
                        <td style="color:var(--kx-muted)">$1,000 paid + 30 days</td>
                        <td style="font-family:monospace;font-weight:700">30,000</td>
                        <td style="font-family:monospace">—</td>
                        <td style="font-family:monospace">150,000,000</td>
                        <td style="color:var(--kx-muted);font-size:.75rem">Enterprise</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3 p-3" style="background:rgba(0,204,0,.05);border:1px solid rgba(0,204,0,.12);border-radius:10px;font-size:.78rem;color:var(--kx-muted);">
            💡 <strong style="color:var(--kx-text)">KayXchange tip:</strong>
            With <code>gpt-4o-mini</code> at Tier 1 (500 RPM), you can handle ~8 concurrent users chatting. 
            At Tier 2 (5,000 RPM), that's ~83 concurrent users. 
            Each chatbot message costs roughly <strong style="color:#fbbf24">$0.0001</strong> — about <strong style="color:#fbbf24">₦0.15</strong>.
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- GROQ SECTION                                                      --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}

    <div class="kx-card" style="border-color:rgba(249,115,22,.2);">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h5 class="mb-0" style="color:#fb923c"><i class="bi bi-lightning-charge-fill me-2"></i>⚡ Groq — Free & Ultra-Fast Inference</h5>
            <a href="https://console.groq.com/keys" target="_blank" class="btn btn-sm" style="background:rgba(249,115,22,.1);color:#fb923c;border:1px solid rgba(249,115,22,.2);border-radius:8px;font-size:.75rem;">
                Get Free API Key →
            </a>
        </div>
        <div class="alert mb-3" style="background:rgba(249,115,22,.06);border:1px solid rgba(249,115,22,.15);border-radius:10px;font-size:.8rem;color:#fdba74;">
            <i class="bi bi-info-circle me-1"></i>
            Groq uses <strong>LPU™ inference</strong> hardware — 5–50× faster than OpenAI for the same models.
            Free tier at <strong>console.groq.com</strong> — no credit card needed.
            Uses the same OpenAI-compatible API format, so switching is instant.
        </div>

        {{-- Groq connection status --}}
        @if(!$groqApiKey)
        <div class="alert mb-3" style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);border-radius:10px;font-size:.83rem;color:#fca5a5;">
            <i class="bi bi-exclamation-triangle me-1"></i>
            No Groq API key configured. <a href="{{ route('admin.settings.index') }}" style="color:#f87171;font-weight:700;">Add it in Settings →</a> (AI tab → Groq API Key)
        </div>
        @elseif($groqConnectionOk)
        <div class="alert mb-3" style="background:rgba(249,115,22,.08);border:1px solid rgba(249,115,22,.15);border-radius:10px;font-size:.83rem;color:#fdba74;">
            <i class="bi bi-check-circle me-1"></i>
            Groq API key is valid. Active model: <code style="color:#fb923c;">{{ $groqModel }}</code>
        </div>
        @else
        <div class="alert mb-3" style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);border-radius:10px;font-size:.83rem;color:#fca5a5;">
            <i class="bi bi-x-circle me-1"></i>
            Could not connect to Groq. Check your API key.
        </div>
        @endif

        {{-- Groq active provider badge --}}
        @if($activeProvider === 'groq')
        <div class="mb-3" style="background:rgba(249,115,22,.1);border:1px solid rgba(249,115,22,.25);border-radius:10px;padding:.75rem 1rem;font-size:.82rem;color:#fb923c;font-weight:600;">
            ⚡ Groq is your <strong>active chatbot provider</strong>. All new chat messages will use Groq.
        </div>
        @endif

        {{-- Groq live rate limit meters --}}
        @if($groqHeaders && !isset($groqHeaders['error']) && ($groqHeaders['rpm_limit'] || $groqHeaders['tpm_limit']))
        <div class="mb-3">
            <div style="font-size:.8rem;color:var(--kx-muted);margin-bottom:.6rem;font-weight:600;">Live Rate Limits (from Groq headers)</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="live-box">
                        <div class="lk mb-1">Requests per Minute</div>
                        @php
                            $grpm  = (int)($groqHeaders['rpm_limit'] ?? 0);
                            $grpmR = (int)($groqHeaders['rpm_remaining'] ?? $grpm);
                            $grpmU = max(0, $grpm - $grpmR);
                            $grpmP = $grpm > 0 ? round(($grpmU / $grpm) * 100) : 0;
                        @endphp
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="lv" style="min-width:70px">{{ number_format($grpmR) }} / {{ number_format($grpm) }}</span>
                            <div class="meter"><div class="meter-fill" style="width:{{ $grpmP }}%;background:#fb923c"></div></div>
                            <span class="lk" style="min-width:35px">{{ $grpmP }}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="live-box">
                        <div class="lk mb-1">Tokens per Minute</div>
                        @php
                            $gtpm  = (int)($groqHeaders['tpm_limit'] ?? 0);
                            $gtpmR = (int)($groqHeaders['tpm_remaining'] ?? $gtpm);
                            $gtpmU = max(0, $gtpm - $gtpmR);
                            $gtpmP = $gtpm > 0 ? round(($gtpmU / $gtpm) * 100) : 0;
                        @endphp
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="lv" style="min-width:70px">{{ number_format($gtpmR) }} / {{ number_format($gtpm) }}</span>
                            <div class="meter"><div class="meter-fill" style="width:{{ $gtpmP }}%;background:#f59e0b"></div></div>
                            <span class="lk" style="min-width:35px">{{ $gtpmP }}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="live-box">
                        <div class="lk mb-1">Requests per Day (RPD)</div>
                        @php
                            $grpd  = (int)($groqHeaders['rpd_limit'] ?? 0);
                            $grpdR = (int)($groqHeaders['rpd_remaining'] ?? $grpd);
                            $grpdU = max(0, $grpd - $grpdR);
                            $grpdP = $grpd > 0 ? round(($grpdU / $grpd) * 100) : 0;
                        @endphp
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="lv" style="min-width:70px">{{ $grpd > 0 ? number_format($grpdR).' / '.number_format($grpd) : '—' }}</span>
                            @if($grpd > 0)
                            <div class="meter"><div class="meter-fill" style="width:{{ $grpdP }}%;background:#fb923c"></div></div>
                            <span class="lk" style="min-width:35px">{{ $grpdP }}%</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Test Groq button --}}
        <div class="d-flex align-items-center gap-3 mb-3">
            <button class="test-btn" style="background:rgba(249,115,22,.12);border-color:rgba(249,115,22,.2);color:#fb923c;" onclick="testGroqConnection()">
                <i class="bi bi-plug me-1"></i>Test Groq Connection
            </button>
            <div id="groq-test-result" style="display:none;font-size:.8rem;"></div>
        </div>
        <div id="groq-test-panel" style="display:none;margin-bottom:1rem;">
            <div class="live-box" id="groq-test-inner"></div>
        </div>
    </div>

    {{-- ── GROQ MODELS ── --}}
    <div class="kx-card" style="border-color:rgba(249,115,22,.15);">
        <h5><i class="bi bi-cpu me-2" style="color:#fb923c"></i>Groq Models — Token Limits & Pricing</h5>
        <p style="font-size:.78rem;color:var(--kx-muted);margin-bottom:1rem;">
            All Groq models are open-source, running on LPU hardware.
            Speed is measured in tokens per second output.
        </p>
        <div class="row g-3">
            @foreach($groqModelInfo as $slug => $m)
            <div class="col-md-6">
                <div class="model-card {{ $slug === $groqModel ? 'current' : '' }}" style="{{ $slug === $groqModel ? 'border-color:#fb923c;box-shadow:0 0 0 1px rgba(249,115,22,.3)' : '' }}">
                    <div class="d-flex align-items-center mb-1 flex-wrap gap-1">
                        <span style="font-weight:700;color:{{ $m['color'] }};font-size:.88rem;">{{ $m['name'] }}</span>
                        <span class="tier-badge pill" style="background:{{ $m['color'] }}1a;color:{{ $m['color'] }}">{{ $m['tier'] }}</span>
                        @if($slug === $groqModel)
                            <span class="pill ms-auto" style="background:rgba(249,115,22,.15);color:#fb923c;">⚡ Active</span>
                        @endif
                    </div>
                    <div style="font-size:.73rem;color:var(--kx-muted);margin-bottom:.75rem;">{{ $m['desc'] }}</div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div style="font-size:.65rem;color:var(--kx-muted)">Context Window</div>
                            <div style="font-size:.9rem;font-weight:700;color:var(--kx-text)">{{ number_format($m['context']) }} <span style="font-size:.65rem;color:var(--kx-muted)">tokens</span></div>
                        </div>
                        <div class="col-6">
                            <div style="font-size:.65rem;color:var(--kx-muted)">Max Output</div>
                            <div style="font-size:.9rem;font-weight:700;color:var(--kx-text)">{{ number_format($m['max_output']) }} <span style="font-size:.65rem;color:var(--kx-muted)">tokens</span></div>
                        </div>
                    </div>

                    <div class="ctx-bar">
                        <div class="ctx-bar-fill" style="width:{{ round(($m['context'] / 131072) * 100) }}%;background:{{ $m['color'] }}66"></div>
                    </div>

                    <div class="d-flex gap-3 mt-2">
                        <div>
                            <div style="font-size:.63rem;color:var(--kx-muted)">Input price</div>
                            <div style="font-size:.78rem;font-weight:700;color:#4ade80">${{ $m['input_cost'] }}<span style="font-size:.63rem;color:var(--kx-muted)">/1M tokens</span></div>
                        </div>
                        <div>
                            <div style="font-size:.63rem;color:var(--kx-muted)">Output price</div>
                            <div style="font-size:.78rem;font-weight:700;color:#fbbf24">${{ $m['output_cost'] }}<span style="font-size:.63rem;color:var(--kx-muted)">/1M tokens</span></div>
                        </div>
                        <div>
                            <div style="font-size:.63rem;color:var(--kx-muted)">Speed</div>
                            <div style="font-size:.78rem;font-weight:700;color:#fb923c">{{ $m['speed'] ?? '—' }}</div>
                        </div>
                        <div class="ms-auto">
                            <div style="font-size:.63rem;color:var(--kx-muted)">~cost/msg</div>
                            @php $perMsgG = number_format((($m['input_cost']*300 + $m['output_cost']*250) / 1_000_000), 6); @endphp
                            <div style="font-size:.78rem;font-weight:700;color:#94a3b8">${{ $perMsgG }}</div>
                        </div>
                    </div>

                    @if($slug !== $groqModel)
                    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="groq_model" value="{{ $slug }}">
                        <button type="submit" style="font-size:.7rem;background:rgba(249,115,22,.08);border:1px solid rgba(249,115,22,.2);color:#fb923c;border-radius:6px;padding:.2rem .7rem;cursor:pointer;">Switch to {{ $m['name'] }}</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── GROQ FREE TIER RATE LIMITS TABLE ── --}}
    <div class="kx-card" style="border-color:rgba(249,115,22,.15);">
        <h5><i class="bi bi-layers me-2" style="color:#fb923c"></i>Groq Free Tier Rate Limits</h5>
        <p style="font-size:.78rem;color:var(--kx-muted);margin-bottom:.75rem;">
            These are the <strong>free tier</strong> limits from <a href="https://console.groq.com" target="_blank" style="color:#fb923c;">console.groq.com</a>.
            RPM = Requests/Min · RPD = Requests/Day · TPM = Tokens/Min · TPD = Tokens/Day.
        </p>
        <div class="table-responsive">
            <table class="tier-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>RPM</th>
                        <th>RPD</th>
                        <th>TPM</th>
                        <th>TPD</th>
                        <th>Speed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groqRateLimits as $slug => $limits)
                    @php $gm = $groqModelInfo[$slug] ?? []; @endphp
                    <tr class="{{ $slug === $groqModel ? 'highlight' : '' }}">
                        <td>
                            <span style="font-weight:600;color:{{ $gm['color'] ?? '#fb923c' }}">{{ $gm['name'] ?? $slug }}</span>
                            @if($slug === $groqModel)<span class="pill ms-1" style="background:rgba(249,115,22,.15);color:#fb923c;font-size:.6rem;">active</span>@endif
                        </td>
                        <td style="font-family:monospace;font-weight:700;color:var(--kx-text)">{{ number_format($limits['rpm']) }}</td>
                        <td style="font-family:monospace;color:var(--kx-muted)">{{ number_format($limits['rpd']) }}</td>
                        <td style="font-family:monospace;color:var(--kx-muted)">{{ number_format($limits['tpm']) }}</td>
                        <td style="font-family:monospace;color:var(--kx-muted)">{{ number_format($limits['tpd']) }}</td>
                        <td style="color:#fb923c;font-size:.75rem;font-weight:600">{{ $gm['speed'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3 p-3" style="background:rgba(249,115,22,.05);border:1px solid rgba(249,115,22,.12);border-radius:10px;font-size:.78rem;color:var(--kx-muted);">
            ⚡ <strong style="color:var(--kx-text)">Groq advantage:</strong>
            At 30 RPM free tier, <code>llama-3.1-8b-instant</code> handles ~0.5 concurrent users but at
            <strong style="color:#fb923c">750 t/s</strong> — responses feel near-instant.
            For production, upgrade at <a href="https://console.groq.com/settings/billing" target="_blank" style="color:#fb923c">console.groq.com</a> for 600+ RPM.
        </div>
    </div>

    {{-- ── PROVIDER COMPARISON ── --}}
    <div class="kx-card">
        <h5><i class="bi bi-arrow-left-right me-2" style="color:#38bdf8"></i>OpenAI vs Groq — Quick Comparison</h5>
        <div class="table-responsive">
            <table class="tier-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th style="color:#4ade80">🌐 OpenAI</th>
                        <th style="color:#fb923c">⚡ Groq</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="color:var(--kx-muted)">Free tier</td>
                        <td>3 RPM, 200 RPD</td>
                        <td style="color:#fb923c;font-weight:600">30 RPM, 1,000 RPD</td>
                    </tr>
                    <tr>
                        <td style="color:var(--kx-muted)">Response speed</td>
                        <td>~20–50 tokens/sec</td>
                        <td style="color:#fb923c;font-weight:600">~275–1,600 tokens/sec</td>
                    </tr>
                    <tr>
                        <td style="color:var(--kx-muted)">Cheapest model cost</td>
                        <td>gpt-4o-mini: $0.15/1M</td>
                        <td style="color:#fb923c;font-weight:600">llama-3.1-8b: $0.05/1M</td>
                    </tr>
                    <tr>
                        <td style="color:var(--kx-muted)">Models</td>
                        <td>GPT-4o, o1, o3 (proprietary)</td>
                        <td>Llama 3.3, Mixtral, Gemma (open source)</td>
                    </tr>
                    <tr>
                        <td style="color:var(--kx-muted)">API format</td>
                        <td>OpenAI standard</td>
                        <td style="color:#4ade80">OpenAI-compatible ✓</td>
                    </tr>
                    <tr>
                        <td style="color:var(--kx-muted)">Best for</td>
                        <td>Complex reasoning, GPT-4 quality</td>
                        <td style="color:#fb923c;font-weight:600">Fast responses, cost savings, free tier</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if($activeProvider)
        <div class="mt-3 p-3" style="background:rgba(56,189,248,.05);border:1px solid rgba(56,189,248,.1);border-radius:10px;font-size:.8rem;color:var(--kx-muted);">
            Currently using: <strong style="color:{{ $activeProvider === 'groq' ? '#fb923c' : '#4ade80' }}">{{ $activeProvider === 'groq' ? '⚡ Groq' : '🌐 OpenAI' }}</strong>.
            <a href="{{ route('admin.settings.index') }}" style="color:#38bdf8;margin-left:.5rem;">Switch provider in Settings →</a>
        </div>
        @endif
    </div>

</div>

<script>
async function testConnection() {
    const btn = document.querySelector('.test-btn');
    btn.textContent = '⏳ Testing…';
    btn.disabled = true;

    try {
        const r    = await fetch('{{ route("admin.settings.ai-test") }}');
        const data = await r.json();
        const el   = document.getElementById('live-test-inner');
        const wrap = document.getElementById('live-test-result');
        wrap.style.display = '';

        if (data.ok) {
            el.innerHTML = `
                <div style="color:#4ade80;font-weight:700;margin-bottom:.5rem;">✅ Connected — Model: <code>${data.model}</code> (${data.owned_by})</div>
                ${data.org_id ? `<div><span class="lk">Org ID:</span> <span class="lv">${data.org_id}</span></div>` : ''}
                <div class="row g-2 mt-1">
                    <div class="col-md-6">
                        <div class="lk">RPM Limit</div>
                        <div class="lv" style="font-size:1.1rem;color:#4ade80">${data.rpm_limit || '—'}</div>
                        <div class="lk">Remaining: ${data.rpm_remaining || '—'} · Resets: ${data.rpm_reset || '—'}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="lk">TPM Limit</div>
                        <div class="lv" style="font-size:1.1rem;color:#a78bfa">${data.tpm_limit ? Number(data.tpm_limit).toLocaleString() : '—'}</div>
                        <div class="lk">Remaining: ${data.tpm_remaining ? Number(data.tpm_remaining).toLocaleString() : '—'} · Resets: ${data.tpm_reset || '—'}</div>
                    </div>
                </div>`;
        } else {
            el.innerHTML = `<div style="color:#f87171">❌ ${data.error || 'Connection failed.'}</div>`;
        }
    } catch (e) {
        document.getElementById('live-test-inner').innerHTML = `<div style="color:#f87171">❌ Network error: ${e.message}</div>`;
        document.getElementById('live-test-result').style.display = '';
    }

    btn.textContent = '🔌 Test Live Connection';
    btn.disabled = false;
}

async function testGroqConnection() {
    const panel  = document.getElementById('groq-test-panel');
    const inner  = document.getElementById('groq-test-inner');
    const result = document.getElementById('groq-test-result');

    result.style.display = '';
    result.style.color   = '#fbbf24';
    result.textContent   = '⏳ Testing Groq…';
    panel.style.display  = 'none';

    try {
        const r    = await fetch('{{ route("admin.settings.groq-test") }}');
        const data = await r.json();

        if (data.ok) {
            result.style.color  = '#fb923c';
            result.textContent  = `✅ Groq connected! Model: ${data.model}`;
            panel.style.display = '';
            inner.innerHTML = `
                <div style="color:#fb923c;font-weight:700;margin-bottom:.5rem;">⚡ Groq Connected — Model: <code>${data.model}</code></div>
                <div class="row g-2 mt-1">
                    <div class="col-md-4">
                        <div class="lk">RPM Limit</div>
                        <div class="lv" style="font-size:1.1rem;color:#fb923c">${data.rpm_limit || '—'}</div>
                        <div class="lk">Remaining: ${data.rpm_remaining || '—'}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="lk">TPM Limit</div>
                        <div class="lv" style="font-size:1.1rem;color:#f59e0b">${data.tpm_limit ? Number(data.tpm_limit).toLocaleString() : '—'}</div>
                        <div class="lk">Remaining: ${data.tpm_remaining ? Number(data.tpm_remaining).toLocaleString() : '—'}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="lk">RPD Limit</div>
                        <div class="lv" style="font-size:1.1rem;color:#fdba74">${data.rpd_limit ? Number(data.rpd_limit).toLocaleString() : '—'}</div>
                        <div class="lk">Remaining: ${data.rpd_remaining ? Number(data.rpd_remaining).toLocaleString() : '—'}</div>
                    </div>
                </div>`;
        } else {
            result.style.color = '#f87171';
            result.textContent = `❌ ${data.error || 'Groq connection failed.'}`;
        }
    } catch (e) {
        result.style.color = '#f87171';
        result.textContent = `❌ Network error: ${e.message}`;
    }
}
</script>
@endsection
