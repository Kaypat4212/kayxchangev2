@extends('adminnavlayout')

@section('content')
<style>
    :root {
        --kx-green: #00cc00; --kx-green-dim: rgba(0,204,0,0.12);
        --kx-dark: #0d1117;  --kx-card: #161b27; --kx-card2: #1e2535;
        --kx-border: rgba(255,255,255,0.07); --kx-text: #e4e8f0;
        --kx-muted: #7a8599; --kx-danger: #ef4444; --kx-warning: #f59e0b;
        --kx-info: #38bdf8;  --kx-tg: #2aabee;
    }
    body { background: var(--kx-dark); color: var(--kx-text); font-family: 'Poppins', sans-serif; }

    .kx-welcome { background: linear-gradient(135deg,#0d2b1d 0%,#0d1117 100%);
        border-bottom: 1px solid var(--kx-border); padding: 20px 24px; margin-bottom: 24px; }
    .kx-panel { background: var(--kx-card); border: 1px solid var(--kx-border);
        border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
    .kx-panel-header { padding: 14px 20px; border-bottom: 1px solid var(--kx-border);
        display: flex; align-items: center; justify-content: space-between; }
    .kx-panel-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; }
    .kx-panel-body { padding: 20px; }

    .kx-stat-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(160px,1fr)); gap: 14px; }
    .kx-stat { background: var(--kx-card2); border: 1px solid var(--kx-border);
        border-radius: 10px; padding: 16px; text-align: center; }
    .kx-stat-val { font-size: 1.6rem; font-weight: 700; }
    .kx-stat-label { font-size: 0.76rem; color: var(--kx-muted); margin-top: 4px; }

    .kx-badge { display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .kx-badge-green  { background: rgba(0,204,0,0.12);  color: #00cc00; }
    .kx-badge-red    { background: rgba(239,68,68,0.12); color: #ef4444; }
    .kx-badge-blue   { background: rgba(42,171,238,0.12);color: #2aabee; }
    .kx-badge-amber  { background: rgba(251,191,36,0.12);color: #fbbf24; }

    .kx-status-row { display: flex; align-items: center; gap: 10px;
        padding: 10px 0; border-bottom: 1px solid var(--kx-border); font-size: 0.85rem; }
    .kx-status-row:last-child { border-bottom: none; }
    .kx-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
    .kx-dot-green  { background: #00cc00; box-shadow: 0 0 6px #00cc00; }
    .kx-dot-red    { background: #ef4444; }
    .kx-dot-amber  { background: #fbbf24; }

    .kx-form-control {
        background: var(--kx-card2); border: 1px solid var(--kx-border);
        color: var(--kx-text); border-radius: 8px; padding: 10px 14px;
        width: 100%; font-size: 0.875rem; outline: none; transition: border 0.2s;
    }
    .kx-form-control:focus { border-color: var(--kx-tg); background: var(--kx-card2); color: var(--kx-text); }
    .kx-form-control::placeholder { color: var(--kx-muted); }

    .kx-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
        border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: none;
        cursor: pointer; transition: opacity 0.15s, transform 0.1s; text-decoration: none; }
    .kx-btn:active { transform: scale(0.97); }
    .kx-btn-tg     { background: #2aabee; color: #fff; }
    .kx-btn-green  { background: var(--kx-green); color: #000; }
    .kx-btn-danger { background: var(--kx-danger); color: #fff; }
    .kx-btn-amber  { background: var(--kx-warning); color: #000; }
    .kx-btn-outline{ background: transparent; border: 1px solid var(--kx-border); color: var(--kx-text); }
    .kx-btn:hover  { opacity: 0.88; }
    .kx-btn-sm     { padding: 5px 12px; font-size: 0.78rem; }

    .kx-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
    .kx-table th { color: var(--kx-muted); font-weight: 500; padding: 8px 12px;
        border-bottom: 1px solid var(--kx-border); text-align: left; font-size: 0.78rem; text-transform: uppercase; }
    .kx-table td { padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
    .kx-table tr:last-child td { border-bottom: none; }
    .kx-table tr:hover td { background: rgba(255,255,255,0.02); }

    .kx-alert { padding: 12px 16px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 16px; }
    .kx-alert-success { background: rgba(0,204,0,0.1);  border: 1px solid rgba(0,204,0,0.2);  color: #00cc00; }
    .kx-alert-error   { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; }

    .kx-select { background: var(--kx-card2); border: 1px solid var(--kx-border);
        color: var(--kx-text); border-radius: 8px; padding: 9px 12px; width: 100%;
        font-size: 0.875rem; outline: none; }
    .kx-select option { background: var(--kx-card2); }
</style>

<div class="container-fluid px-3 px-md-4 py-3">

    {{-- Page header --}}
    <div class="kx-welcome rounded-3 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h4 class="mb-1 fw-bold"><i class="bi bi-telegram me-2" style="color:var(--kx-tg)"></i>Telegram Bot Control</h4>
                <span class="text-muted" style="font-size:0.82rem">Manage the KayXchange bot, send messages, and monitor users.</span>
            </div>
            <span class="kx-badge {{ $isProduction ? 'kx-badge-green' : 'kx-badge-amber' }}">
                <span class="kx-dot {{ $isProduction ? 'kx-dot-green' : 'kx-dot-amber' }}"></span>
                {{ $isProduction ? 'Production Mode' : 'Development Mode' }}
            </span>
            <a href="{{ route('admin.telegram.ai-config') }}" class="kx-btn" style="background:rgba(168,85,247,0.15);color:#a855f7;border:1px solid rgba(168,85,247,0.3);padding:7px 14px;font-size:0.82rem;">
                🤖 AI Bot Config
            </a>
        </div>
    </div>

    {{-- Flash alerts --}}
    @if(session('success'))
        <div class="kx-alert kx-alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="kx-alert kx-alert-error"><i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">

            {{-- ── Bot Status ── --}}
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5><i class="bi bi-robot me-2" style="color:var(--kx-tg)"></i>Bot Status</h5>
                </div>
                <div class="kx-panel-body">
                    <div class="kx-stat-grid mb-3">
                        <div class="kx-stat">
                            <div class="kx-stat-val" style="color:var(--kx-tg)">{{ $totalVerified }}</div>
                            <div class="kx-stat-label">Verified Users</div>
                        </div>
                        <div class="kx-stat">
                            <div class="kx-stat-val" style="color:var(--kx-green)">{{ $totalWithNotifications }}</div>
                            <div class="kx-stat-label">Notifications On</div>
                        </div>
                        <div class="kx-stat">
                            <div class="kx-stat-val" style="color:var(--kx-warning)">
                                {{ $totalVerified - $totalWithNotifications }}
                            </div>
                            <div class="kx-stat-label">Notif. Disabled</div>
                        </div>
                    </div>

                    @if(!empty($botInfo['result']))
                    <div class="kx-status-row">
                        <span class="kx-dot kx-dot-green"></span>
                        <span><strong>Bot:</strong> {{ $botInfo['result']['first_name'] }}</span>
                        <span class="ms-auto text-muted">@{{ $botInfo['result']['username'] }}</span>
                    </div>
                    @endif

                    <div class="kx-status-row">
                        <span class="kx-dot {{ !empty($webhookInfo['url']) ? 'kx-dot-green' : 'kx-dot-amber' }}"></span>
                        <span><strong>Webhook:</strong>
                            @if(!empty($webhookInfo['url']))
                                <span class="kx-badge kx-badge-green ms-1">Active</span>
                                <span class="text-muted ms-2" style="font-size:0.78rem;word-break:break-all">{{ $webhookInfo['url'] }}</span>
                            @else
                                <span class="kx-badge kx-badge-amber ms-1">Not Set</span>
                                <span class="text-muted ms-2" style="font-size:0.78rem">Use polling (local) or set webhook (production)</span>
                            @endif
                        </span>
                    </div>

                    @if(!empty($webhookInfo['pending_update_count']) && $webhookInfo['pending_update_count'] > 0)
                    <div class="kx-status-row">
                        <span class="kx-dot kx-dot-amber"></span>
                        <span><strong>Pending updates:</strong> {{ $webhookInfo['pending_update_count'] }}</span>
                    </div>
                    @endif

                    <div class="kx-status-row">
                        <span class="kx-dot {{ $isProduction ? 'kx-dot-green' : 'kx-dot-amber' }}"></span>
                        <span><strong>Mode:</strong>
                            @if($isProduction)
                                Webhook (production) — updates arrive automatically
                            @else
                                Polling (local dev) — run <code style="color:var(--kx-tg);font-size:0.78rem">php artisan telegram:poll --continuous</code> in terminal
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── Webhook Controls ── --}}
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5><i class="bi bi-globe me-2" style="color:var(--kx-info)"></i>Webhook Management</h5>
                </div>
                <div class="kx-panel-body">
                    <p style="color:var(--kx-muted);font-size:0.83rem">
                        On a live cPanel server, set the webhook so Telegram delivers messages directly to your app — no polling needed.
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.telegram.set-webhook') }}">
                            @csrf
                            <button type="submit" class="kx-btn kx-btn-tg">
                                <i class="bi bi-link-45deg"></i> Set Webhook
                                <span style="font-size:0.72rem;opacity:0.8">({{ rtrim(config('app.url'),'/') }}/api/telegram/webhook)</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.telegram.delete-webhook') }}">
                            @csrf
                            <button type="submit" class="kx-btn kx-btn-outline"
                                onclick="return confirm('Remove webhook and switch to polling mode?')">
                                <i class="bi bi-unlink"></i> Remove Webhook
                            </button>
                        </form>
                    </div>
                    <div class="mt-3 p-3 rounded-2" style="background:var(--kx-card2);font-size:0.8rem;color:var(--kx-muted)">
                        <strong style="color:var(--kx-text)">cPanel deployment checklist:</strong>
                        <ul class="mb-0 mt-1" style="padding-left:18px">
                            <li>Set <code>APP_URL</code> to your real domain (e.g. <code>https://kayxchange.net</code>)</li>
                            <li>Set <code>SESSION_DOMAIN</code> to your domain</li>
                            <li>Set <code>SESSION_SECURE_COOKIE=true</code></li>
                            <li>Click <strong>Set Webhook</strong> above once — Telegram will push updates to your app</li>
                            <li>No polling command needed on the server</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ── Broadcast ── --}}
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5><i class="bi bi-megaphone me-2" style="color:var(--kx-warning)"></i>Broadcast Message</h5>
                    <span class="kx-badge kx-badge-blue">{{ $totalWithNotifications }} recipients</span>
                </div>
                <div class="kx-panel-body">
                    <p style="color:var(--kx-muted);font-size:0.83rem">
                        Sends to all verified users with notifications enabled. Supports *bold*, _italic_, and `code` (Markdown).
                    </p>
                    <form method="POST" action="{{ route('admin.telegram.broadcast') }}">
                        @csrf
                        <textarea name="message" rows="4" class="kx-form-control mb-3"
                            placeholder="Type your broadcast message here..." required maxlength="4096"></textarea>
                        @error('message')
                            <div class="kx-alert kx-alert-error mb-2">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="kx-btn kx-btn-tg"
                            onclick="return confirm('Send this message to {{ $totalWithNotifications }} users?')">
                            <i class="bi bi-send"></i> Send Broadcast
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Direct Message ── --}}
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5><i class="bi bi-chat-dots me-2" style="color:var(--kx-green)"></i>Direct Message to User</h5>
                </div>
                <div class="kx-panel-body">
                    <form method="POST" action="{{ route('admin.telegram.send-direct') }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size:0.82rem;color:var(--kx-muted);margin-bottom:6px;display:block">Select User</label>
                            <select name="user_id" class="kx-select" required>
                                <option value="">— Choose a verified user —</option>
                                @foreach($recentlyLinked as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})
                                        @if($u->telegram_username) — @{{ $u->telegram_username }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label style="font-size:0.82rem;color:var(--kx-muted);margin-bottom:6px;display:block">Message</label>
                            <textarea name="message" rows="3" class="kx-form-control"
                                placeholder="Type your message..." required maxlength="4096"></textarea>
                        </div>
                        <button type="submit" class="kx-btn kx-btn-green">
                            <i class="bi bi-send-check"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">

            {{-- ── Bot Config ── --}}
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5><i class="bi bi-gear me-2" style="color:var(--kx-muted)"></i>Configuration</h5>
                </div>
                <div class="kx-panel-body">
                    <div class="kx-status-row">
                        <span style="color:var(--kx-muted);width:130px;flex-shrink:0">APP_URL</span>
                        <span style="word-break:break-all;font-size:0.78rem">{{ config('app.url') }}</span>
                    </div>
                    <div class="kx-status-row">
                        <span style="color:var(--kx-muted);width:130px;flex-shrink:0">Bot Token</span>
                        <span class="kx-badge kx-badge-{{ env('KAYXCHANGE_TELEGRAM_BOT_TOKEN') ? 'green' : 'red' }}">
                            {{ env('KAYXCHANGE_TELEGRAM_BOT_TOKEN') ? 'Set ✓' : 'Missing ✗' }}
                        </span>
                    </div>
                    <div class="kx-status-row">
                        <span style="color:var(--kx-muted);width:130px;flex-shrink:0">Secure Cookie</span>
                        <span class="kx-badge kx-badge-{{ env('SESSION_SECURE_COOKIE') ? 'green' : 'amber' }}">
                            {{ env('SESSION_SECURE_COOKIE') ? 'Yes' : 'No (local ok)' }}
                        </span>
                    </div>
                    <div class="kx-status-row">
                        <span style="color:var(--kx-muted);width:130px;flex-shrink:0">Session Domain</span>
                        <span style="font-size:0.78rem">{{ env('SESSION_DOMAIN') ?: '(not set)' }}</span>
                    </div>
                    <div class="mt-3">
                        <a href="{{ url('/admin/env-editor') }}" class="kx-btn kx-btn-outline w-100 justify-content-center">
                            <i class="bi bi-key"></i> Edit API Keys
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── Verified Users ── --}}
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5><i class="bi bi-person-check me-2" style="color:var(--kx-tg)"></i>Linked Users</h5>
                    <span class="kx-badge kx-badge-blue">{{ $totalVerified }} total</span>
                </div>
                <div class="kx-panel-body p-0">
                    @if($recentlyLinked->isEmpty())
                        <div class="p-4 text-center" style="color:var(--kx-muted);font-size:0.84rem">No users linked yet.</div>
                    @else
                    <table class="kx-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Notif</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentlyLinked as $u)
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:0.82rem">{{ Str::limit($u->name, 18) }}</div>
                                    <div style="color:var(--kx-muted);font-size:0.74rem">
                                        @if($u->telegram_username)@{{ $u->telegram_username }}@endif
                                    </div>
                                </td>
                                <td>
                                    <span class="kx-badge {{ $u->telegram_notifications ? 'kx-badge-green' : 'kx-badge-red' }}">
                                        {{ $u->telegram_notifications ? 'On' : 'Off' }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.telegram.unlink-user') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $u->id }}">
                                        <button type="submit" class="kx-btn kx-btn-danger kx-btn-sm"
                                            onclick="return confirm('Unlink Telegram for {{ addslashes($u->name) }}?')"
                                            title="Unlink">
                                            <i class="bi bi-unlink"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>

            {{-- ── Quick Tips ── --}}
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5><i class="bi bi-lightbulb me-2" style="color:var(--kx-warning)"></i>Quick Guide</h5>
                </div>
                <div class="kx-panel-body" style="font-size:0.82rem;color:var(--kx-muted)">
                    <p><span style="color:var(--kx-tg)">🖥 Local Dev</span> — run polling:<br>
                        <code style="color:var(--kx-text)">php artisan telegram:poll --continuous</code></p>
                    <p><span style="color:var(--kx-green)">🌐 Live Server</span> — click <strong style="color:var(--kx-text)">Set Webhook</strong> once after deploying.</p>
                    <p><span style="color:var(--kx-warning)">🔑 Token change?</span> — update <code>KAYXCHANGE_TELEGRAM_BOT_TOKEN</code> in .env / API Keys, then re-set webhook.</p>
                    <p><span style="color:var(--kx-danger)">⚠️ ngrok URL changed?</span> — update <code>APP_URL</code> + <code>SESSION_DOMAIN</code> in API Keys and run <code>php artisan config:clear</code>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
