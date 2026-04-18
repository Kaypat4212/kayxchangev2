@extends('layout')

@push('body-class', 'telegram-settings')

@push('styles')
<style>
:root {
    --tg-blue: #2aabee;
    --tg-blue-dim: rgba(42,171,238,0.12);
    --tg-blue-glow: rgba(42,171,238,0.22);
    --kx-green: #00cc00;
    --kx-green-dim: rgba(0,204,0,0.10);
    --kx-dark: #0d1117;
    --kx-card: #161b27;
    --kx-card2: #1e2535;
    --kx-border: rgba(255,255,255,0.07);
    --kx-text: #e4e8f0;
    --kx-muted: #7a8599;
}
body { background: var(--kx-dark); color: var(--kx-text); }

.tg-wrap { max-width: 760px; margin: 0 auto; padding: 2.5rem 1rem 4rem; }

/* Page header */
.tg-page-header {
    display: flex; align-items: center; gap: 1rem; margin-bottom: 1.75rem;
}
.tg-page-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: var(--tg-blue-dim); border: 1px solid rgba(42,171,238,0.25);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.tg-page-icon i { font-size: 1.5rem; color: var(--tg-blue); }
.tg-page-header h1 { font-size: 1.35rem; font-weight: 800; color: #fff; margin: 0 0 .2rem; }
.tg-page-header p { font-size: .82rem; color: var(--kx-muted); margin: 0; }

/* Cards */
.tg-card {
    background: var(--kx-card); border: 1px solid var(--kx-border);
    border-radius: 18px; padding: 1.5rem 1.75rem; margin-bottom: 1.25rem;
}
.tg-card-title {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--kx-muted);
    display: flex; align-items: center; gap: .45rem; margin-bottom: 1.25rem;
}
.tg-card-title i { color: var(--tg-blue); font-size: .85rem; }

/* Alert banners */
.tg-alert {
    border-radius: 12px; padding: .85rem 1rem; font-size: .82rem;
    display: flex; align-items: flex-start; gap: .6rem; margin-bottom: 1.25rem; line-height: 1.55;
}
.tg-alert.success { background: var(--kx-green-dim); border: 1px solid rgba(0,204,0,.25); color: #4ade80; }
.tg-alert.error   { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #f87171; }
.tg-alert i { flex-shrink: 0; margin-top: .1rem; }

/* Form elements */
.tg-label {
    font-size: .8rem; font-weight: 600; color: var(--kx-muted);
    text-transform: uppercase; letter-spacing: .04em; display: block; margin-bottom: .55rem;
}
.tg-input-group { display: flex; align-items: center; gap: 0; }
.tg-input-prefix {
    background: var(--kx-card2); border: 1px solid var(--kx-border); border-right: none;
    border-radius: 10px 0 0 10px; padding: .65rem .9rem;
    color: var(--tg-blue); font-weight: 700; font-size: .95rem; line-height: 1;
}
.tg-input {
    flex: 1; background: var(--kx-card2); border: 1px solid var(--kx-border);
    border-radius: 0 10px 10px 0; padding: .65rem .9rem;
    color: var(--kx-text); font-size: .9rem; outline: none; transition: border-color .2s;
}
.tg-input:focus { border-color: var(--tg-blue); box-shadow: 0 0 0 3px rgba(42,171,238,.1); }
.tg-input.is-invalid { border-color: #f87171; }
.tg-hint { font-size: .75rem; color: var(--kx-muted); margin-top: .45rem; }
.tg-hint.error-text { color: #f87171; }

/* Toggle switch */
.tg-switch-row {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--kx-card2); border: 1px solid var(--kx-border);
    border-radius: 12px; padding: .9rem 1rem;
}
.tg-switch-info strong { font-size: .9rem; color: #fff; display: block; margin-bottom: .15rem; }
.tg-switch-info span { font-size: .78rem; color: var(--kx-muted); }
.tg-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
.tg-switch input { opacity: 0; width: 0; height: 0; }
.tg-switch-slider {
    position: absolute; inset: 0; background: rgba(255,255,255,.12);
    border-radius: 24px; cursor: pointer; transition: background .25s;
}
.tg-switch-slider::before {
    content: ''; position: absolute; width: 18px; height: 18px;
    left: 3px; top: 3px; border-radius: 50%; background: #fff; transition: transform .25s;
}
.tg-switch input:checked + .tg-switch-slider { background: var(--tg-blue); }
.tg-switch input:checked + .tg-switch-slider::before { transform: translateX(20px); }

/* Buttons */
.tg-btn-primary {
    display: inline-flex; align-items: center; gap: .5rem;
    background: linear-gradient(135deg, var(--tg-blue), #1a8fd1);
    border: none; border-radius: 12px; color: #fff; font-weight: 700;
    font-size: .9rem; padding: .75rem 1.5rem; cursor: pointer; transition: opacity .2s, transform .15s;
    text-decoration: none;
}
.tg-btn-primary:hover { opacity: .88; transform: translateY(-1px); color: #fff; }
.tg-btn-secondary {
    display: inline-flex; align-items: center; gap: .5rem;
    background: transparent; border: 1px solid var(--kx-border);
    border-radius: 12px; color: var(--kx-muted); font-weight: 600;
    font-size: .9rem; padding: .73rem 1.4rem; text-decoration: none; transition: border-color .2s, color .2s;
}
.tg-btn-secondary:hover { border-color: rgba(255,255,255,.2); color: #fff; }
.tg-btn-test {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--kx-green-dim); border: 1px solid rgba(0,204,0,.3);
    border-radius: 12px; color: var(--kx-green); font-weight: 700;
    font-size: .85rem; padding: .65rem 1.25rem; cursor: pointer; transition: opacity .2s;
}
.tg-btn-test:hover { opacity: .8; }
.tg-action-row { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.5rem; }

/* Status grid */
.tg-status-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.tg-status-item {
    background: var(--kx-card2); border: 1px solid var(--kx-border);
    border-radius: 14px; padding: 1.1rem; text-align: center;
}
.tg-status-icon { font-size: 1.6rem; margin-bottom: .5rem; }
.tg-status-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--kx-muted); margin-bottom: .25rem; }
.tg-status-val { font-size: .8rem; font-weight: 600; color: #fff; }
.tg-status-val.ok   { color: #4ade80; }
.tg-status-val.warn { color: #fbbf24; }
.tg-status-val.off  { color: var(--kx-muted); }

/* Setup steps */
.tg-steps { display: flex; flex-direction: column; gap: 0; }
.tg-step { display: flex; gap: .9rem; position: relative; padding-bottom: 1.25rem; }
.tg-step:last-child { padding-bottom: 0; }
.tg-step::before {
    content: ''; position: absolute; left: 14px; top: 32px;
    bottom: 0; width: 2px; background: rgba(42,171,238,.15);
}
.tg-step:last-child::before { display: none; }
.tg-step-num {
    width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
    background: var(--tg-blue-dim); border: 1px solid rgba(42,171,238,.3);
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 800; color: var(--tg-blue); z-index: 1;
}
.tg-step-body strong { font-size: .87rem; color: #fff; display: block; margin-bottom: .2rem; }
.tg-step-body p { font-size: .79rem; color: var(--kx-muted); margin: 0; line-height: 1.5; }

/* Divider */
.tg-divider { border: none; border-top: 1px solid var(--kx-border); margin: 1.25rem 0; }

/* AI toggle (reuse kx-toggle style) */
.tg-toggle { position: relative; display: inline-block; width: 48px; height: 26px; }
.tg-toggle input { display: none; }
.tg-toggle-slider { position: absolute; inset: 0; background: var(--kx-card2,#1e2535);
    border: 1px solid var(--kx-border); border-radius: 26px; cursor: pointer; transition: 0.3s; }
.tg-toggle-slider::before { content: ''; position: absolute; width: 20px; height: 20px;
    background: var(--kx-muted,#7a8599); border-radius: 50%; top: 2px; left: 2px; transition: 0.3s; }
.tg-toggle input:checked + .tg-toggle-slider { background: rgba(168,85,247,0.2); border-color: #a855f7; }
.tg-toggle input:checked + .tg-toggle-slider::before { background: #a855f7; transform: translateX(22px); }

/* Pill badge */
.tg-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    border-radius: 20px; padding: .25rem .8rem; font-size: .72rem; font-weight: 700;
}
.tg-pill.connected   { background: var(--kx-green-dim); border: 1px solid rgba(0,204,0,.25); color: #4ade80; }
.tg-pill.pending-bot { background: rgba(251,191,36,.08); border: 1px solid rgba(251,191,36,.2); color: #fbbf24; }
.tg-pill.setup       { background: var(--tg-blue-dim); border: 1px solid rgba(42,171,238,.25); color: var(--tg-blue); }
.tg-pill.inactive    { background: rgba(255,255,255,.05); border: 1px solid var(--kx-border); color: var(--kx-muted); }

/* QR Modal */
.tg-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 1000; align-items: center; justify-content: center; }
.tg-modal-backdrop.show { display: flex; }
.tg-modal {
    background: var(--kx-card); border: 1px solid var(--kx-border); border-radius: 20px;
    padding: 2rem; max-width: 340px; width: 90%; text-align: center;
}
.tg-modal h5 { font-size: 1rem; font-weight: 800; color: #fff; margin-bottom: .5rem; }
.tg-modal p  { font-size: .8rem; color: var(--kx-muted); margin-bottom: 1rem; }
.tg-modal-close {
    display: block; width: 100%; margin-top: 1rem;
    background: transparent; border: 1px solid var(--kx-border);
    border-radius: 10px; padding: .6rem; color: var(--kx-muted);
    font-size: .85rem; cursor: pointer; transition: border-color .2s;
}
.tg-modal-close:hover { border-color: rgba(255,255,255,.2); color: #fff; }

@media (max-width: 576px) {
    .tg-status-grid { grid-template-columns: repeat(3,1fr); gap: .6rem; }
    .tg-status-item { padding: .75rem .5rem; }
    .tg-status-icon { font-size: 1.25rem; }
    .tg-status-val  { font-size: .72rem; }
}
</style>
@endpush

@section('content')
<div class="tg-wrap">

    {{-- Page header --}}
    <div class="tg-page-header">
        <div class="tg-page-icon"><i class="fab fa-telegram-plane"></i></div>
        <div>
            <h1>Telegram Notifications</h1>
            <p>Connect your Telegram to receive real-time trade alerts and updates</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="tg-alert success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
    @endif
    @if(session('error'))
        <div class="tg-alert error"><i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
    @endif

    {{-- Main grid --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;" class="tg-main-grid">

        {{-- LEFT: Settings form --}}
        <div>
            <div class="tg-card" style="height:100%;">
                <div class="tg-card-title"><i class="fas fa-sliders-h"></i> Settings</div>

                <form method="POST" action="{{ route('settings.telegram.update') }}">
                    @csrf
                    @method('PUT')

                    {{-- Username --}}
                    <div style="margin-bottom:1.25rem;">
                        <label class="tg-label">Telegram Username</label>
                        <div class="tg-input-group">
                            <span class="tg-input-prefix">@</span>
                            <input type="text"
                                   class="tg-input @error('telegram_username') is-invalid @enderror"
                                   name="telegram_username"
                                   value="{{ old('telegram_username', Auth::user()->telegram_username) }}"
                                   placeholder="your_username">
                        </div>
                        @error('telegram_username')
                            <p class="tg-hint error-text"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</p>
                        @enderror
                        <p class="tg-hint"><i class="fas fa-info-circle me-1"></i>Enter your username without the @ symbol</p>
                    </div>

                    <hr class="tg-divider">

                    {{-- Toggle --}}
                    <div style="margin-bottom:1.5rem;">
                        <label class="tg-label">Notifications</label>
                        <div class="tg-switch-row">
                            <div class="tg-switch-info">
                                <strong>Enable Telegram Notifications</strong>
                                <span>Trade confirmations and important updates</span>
                            </div>
                            <label class="tg-switch">
                                <input type="checkbox" name="telegram_notifications" value="1"
                                       {{ Auth::user()->telegram_notifications ? 'checked' : '' }}>
                                <span class="tg-switch-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="tg-action-row">
                        <button type="submit" class="tg-btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                        <a href="{{ route('settings.index') }}" class="tg-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT: Setup guide --}}
        <div>
            <div class="tg-card" style="height:100%;">
                <div class="tg-card-title"><i class="fas fa-list-ol"></i> How to Connect</div>

                <div class="tg-steps">
                    <div class="tg-step">
                        <div class="tg-step-num">1</div>
                        <div class="tg-step-body">
                            <strong>Find Your Username</strong>
                            <p>Open Telegram → Settings → note your username (without @)</p>
                        </div>
                    </div>
                    <div class="tg-step">
                        <div class="tg-step-num">2</div>
                        <div class="tg-step-body">
                            <strong>Start Our Bot</strong>
                            <p style="margin-bottom:.6rem;">Open the bot and tap <strong>Start</strong></p>
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                                <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" class="tg-btn-primary" style="font-size:.78rem;padding:.5rem 1rem;">
                                    <i class="fab fa-telegram-plane"></i> @TradewithkayxchangeBOT
                                </a>
                                <button type="button" onclick="document.getElementById('qrModal').classList.add('show')" class="tg-btn-secondary" style="font-size:.78rem;padding:.48rem .9rem;">
                                    <i class="fas fa-qrcode"></i> QR
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="tg-step">
                        <div class="tg-step-num">3</div>
                        <div class="tg-step-body">
                            <strong>Enter Username Here</strong>
                            <p>Paste your Telegram username in the form and save</p>
                        </div>
                    </div>
                    <div class="tg-step">
                        <div class="tg-step-num">4</div>
                        <div class="tg-step-body">
                            <strong>Enable Notifications</strong>
                            <p>Toggle on notifications and save to start receiving alerts</p>
                        </div>
                    </div>
                </div>

                <hr class="tg-divider">
                <p style="font-size:.77rem;color:var(--kx-muted);margin:0;">
                    <i class="fas fa-shield-alt me-1" style="color:var(--tg-blue);"></i>
                    Your username is only used to send you notifications and is never shared with third parties.
                </p>
            </div>
        </div>
    </div>

    {{-- Status strip --}}
    <div class="tg-card">
        <div class="tg-card-title"><i class="fas fa-chart-bar"></i> Connection Status</div>
        <div class="tg-status-grid">
            <div class="tg-status-item">
                <div class="tg-status-icon">
                    @if(Auth::user()->telegram_username)
                        <i class="fas fa-check-circle" style="color:#4ade80;"></i>
                    @else
                        <i class="fas fa-times-circle" style="color:#f87171;"></i>
                    @endif
                </div>
                <div class="tg-status-label">Username</div>
                <div class="tg-status-val {{ Auth::user()->telegram_username ? 'ok' : 'off' }}">
                    {{ Auth::user()->telegram_username ? '@'.Auth::user()->telegram_username : 'Not set' }}
                </div>
            </div>
            <div class="tg-status-item">
                <div class="tg-status-icon">
                    @if(Auth::user()->telegram_notifications)
                        <i class="fas fa-bell" style="color:#4ade80;"></i>
                    @else
                        <i class="fas fa-bell-slash" style="color:#fbbf24;"></i>
                    @endif
                </div>
                <div class="tg-status-label">Notifications</div>
                <div class="tg-status-val {{ Auth::user()->telegram_notifications ? 'ok' : 'warn' }}">
                    {{ Auth::user()->telegram_notifications ? 'Enabled' : 'Disabled' }}
                </div>
            </div>
            <div class="tg-status-item">
                <div class="tg-status-icon">
                    @if(Auth::user()->telegram_chat_id && Auth::user()->telegram_verified && Auth::user()->telegram_notifications)
                        <i class="fas fa-rocket" style="color:#4ade80;"></i>
                    @elseif(Auth::user()->telegram_username && Auth::user()->telegram_notifications)
                        <i class="fas fa-clock" style="color:#fbbf24;"></i>
                    @else
                        <i class="fas fa-cog" style="color:var(--kx-muted);"></i>
                    @endif
                </div>
                <div class="tg-status-label">Setup</div>
                <div class="tg-status-val
                    @if(Auth::user()->telegram_chat_id && Auth::user()->telegram_verified && Auth::user()->telegram_notifications) ok
                    @elseif(Auth::user()->telegram_username && Auth::user()->telegram_notifications) warn
                    @else off @endif">
                    @if(Auth::user()->telegram_chat_id && Auth::user()->telegram_verified && Auth::user()->telegram_notifications)
                        Fully connected
                    @elseif(Auth::user()->telegram_username && Auth::user()->telegram_notifications)
                        Bot verification needed
                    @elseif(Auth::user()->telegram_username)
                        Enable notifications
                    @else
                        Setup required
                    @endif
                </div>
            </div>
        </div>

        @if(Auth::user()->telegram_chat_id && Auth::user()->telegram_verified && Auth::user()->telegram_notifications)
            <hr class="tg-divider">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
                <div>
                    <p style="font-size:.85rem;color:#fff;font-weight:700;margin:0 0 .15rem;">Test Your Notifications</p>
                    <p style="font-size:.78rem;color:var(--kx-muted);margin:0;">Send a test message to verify everything is working</p>
                </div>
                <form method="POST" action="{{ route('settings.telegram.test') }}">
                    @csrf
                    <button type="submit" class="tg-btn-test">
                        <i class="fas fa-paper-plane"></i> Send Test
                    </button>
                </form>
            </div>
        @elseif(Auth::user()->telegram_username && Auth::user()->telegram_notifications)
            <hr class="tg-divider">
            <div class="tg-alert" style="background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.2);color:#fbbf24;margin:0;">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>Next step:</strong> Open <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" style="color:#fbbf24;">@TradewithkayxchangeBOT</a> on Telegram and send your email <strong>{{ Auth::user()->email }}</strong> to complete verification.</span>
            </div>
        @endif
    </div>

{{-- AI Trade Assistant Section --}}
@if(Auth::user()->telegram_chat_id && Auth::user()->telegram_verified)
<div class="tg-card" style="margin-top:1.5rem;">
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
        <div style="width:38px;height:38px;border-radius:8px;background:rgba(168,85,247,0.15);
            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-robot" style="color:#a855f7;font-size:1rem;"></i>
        </div>
        <div>
            <p style="font-size:.9rem;color:#fff;font-weight:700;margin:0;">AI Trade Assistant (KAI)</p>
            <p style="font-size:.77rem;color:var(--kx-muted);margin:0;">
                Chat with KAI in the Telegram bot for trade advice, rate queries &amp; platform help.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.telegram.ai.update') }}">
        @csrf
        @method('PUT')

        <div style="background:var(--kx-card2,#1e2535);border:1px solid rgba(255,255,255,0.07);
            border-radius:10px;padding:1rem 1.25rem;display:flex;align-items:center;
            justify-content:space-between;gap:1rem;">
            <div>
                <p style="font-size:.85rem;font-weight:600;color:#fff;margin:0 0 .2rem;">
                    Enable AI Assistant
                </p>
                <p style="font-size:.76rem;color:var(--kx-muted);margin:0;">
                    Type <code style="color:#a855f7;">/ai</code> in the bot to start chatting.
                </p>
            </div>
            <label class="tg-toggle">
                <input type="checkbox" name="telegram_ai_enabled" value="1"
                    {{ Auth::user()->telegram_ai_enabled ? 'checked' : '' }}>
                <span class="tg-toggle-slider"></span>
            </label>
        </div>

        <div style="margin-top:.75rem;display:flex;justify-content:flex-end;">
            <button type="submit" class="tg-btn-primary" style="padding:8px 20px;font-size:.82rem;">
                <i class="fas fa-save"></i> Save AI Preference
            </button>
        </div>
    </form>

    <div style="margin-top:1rem;background:rgba(168,85,247,0.06);border:1px solid rgba(168,85,247,0.2);
        border-radius:8px;padding:.9rem 1rem;font-size:.78rem;color:#c4b5fd;line-height:1.6;">
        <strong>💡 What can KAI do?</strong><br>
        Ask about current BTC/USDT/ETH rates, get buying and selling guidance, check your balance,
        understand your trade history, and get general platform help — all in natural language inside Telegram.
    </div>
</div>
@endif

</div>

{{-- QR Modal --}}
<div class="tg-modal-backdrop" id="qrModal" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="tg-modal">
        <h5><i class="fas fa-qrcode me-2" style="color:var(--tg-blue);"></i>Scan to Open Bot</h5>
        <p>Scan with your phone camera to open the bot directly in Telegram</p>
        <div id="qrcode" style="display:flex;justify-content:center;margin-bottom:.75rem;"></div>
        <p style="font-size:.73rem;">Or search <code style="color:var(--tg-blue);">@TradewithkayxchangeBOT</code> in Telegram</p>
        <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" class="tg-btn-primary" style="width:100%;justify-content:center;margin-bottom:.5rem;">
            <i class="fab fa-telegram-plane"></i> Open in Telegram
        </a>
        <button class="tg-modal-close" onclick="document.getElementById('qrModal').classList.remove('show')">Close</button>
    </div>
</div>

<style>
@media (max-width: 640px) {
    .tg-main-grid { grid-template-columns: 1fr !important; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
document.getElementById('qrModal').addEventListener('transitionend', function(){});
document.getElementById('qrModal').addEventListener('click', function(e){
    if(e.target === this) this.classList.remove('show');
});
// Lazy-generate QR only when modal opens
const observer = new MutationObserver(() => {
    if (document.getElementById('qrModal').classList.contains('show')) {
        const wrap = document.getElementById('qrcode');
        if (!wrap.querySelector('canvas')) {
            QRCode.toCanvas(document.createElement('canvas'), 'https://t.me/TradewithkayxchangeBOT', {
                width: 200, margin: 2, color: { dark: '#2aabee', light: '#161b27' }
            }, (err, canvas) => {
                if (!err) { wrap.innerHTML=''; wrap.appendChild(canvas); }
            });
        }
    }
});
observer.observe(document.getElementById('qrModal'), { attributes: true, attributeFilter: ['class'] });

@if(!Auth::user()->telegram_verified)
let statusTimer = setInterval(() => {
    fetch('/api/user/telegram-status', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => { if (d.verified) location.reload(); })
        .catch(() => {});
}, 5000);
window.addEventListener('beforeunload', () => clearInterval(statusTimer));
@endif
</script>
@endsection