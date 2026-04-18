@extends('adminnavlayout')

@section('content')
<style>
    :root {
        --kx-green: #00cc00; --kx-green-dim: rgba(0,204,0,0.12);
        --kx-dark: #0d1117;  --kx-card: #161b27; --kx-card2: #1e2535;
        --kx-border: rgba(255,255,255,0.07); --kx-text: #e4e8f0;
        --kx-muted: #7a8599; --kx-danger: #ef4444; --kx-warning: #f59e0b;
        --kx-info: #38bdf8;  --kx-tg: #2aabee; --kx-purple: #a855f7;
    }
    body { background: var(--kx-dark); color: var(--kx-text); font-family: 'Poppins', sans-serif; }

    .kx-welcome { background: linear-gradient(135deg,#1a0d2e 0%,#0d1117 100%);
        border-bottom: 1px solid var(--kx-border); padding: 20px 24px; margin-bottom: 24px; }
    .kx-panel { background: var(--kx-card); border: 1px solid var(--kx-border);
        border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
    .kx-panel-header { padding: 14px 20px; border-bottom: 1px solid var(--kx-border);
        display: flex; align-items: center; justify-content: space-between; }
    .kx-panel-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; }
    .kx-panel-body { padding: 20px; }

    .kx-form-group { margin-bottom: 18px; }
    .kx-form-label { display: block; font-size: 0.82rem; color: var(--kx-muted);
        margin-bottom: 6px; font-weight: 500; }
    .kx-form-help  { font-size: 0.76rem; color: var(--kx-muted); margin-top: 5px; }

    .kx-form-control {
        background: var(--kx-card2); border: 1px solid var(--kx-border);
        color: var(--kx-text); border-radius: 8px; padding: 10px 14px;
        width: 100%; font-size: 0.875rem; outline: none; transition: border 0.2s;
    }
    .kx-form-control:focus { border-color: var(--kx-purple); box-shadow: 0 0 0 3px rgba(168,85,247,0.15); }
    .kx-form-control::placeholder { color: var(--kx-muted); }
    textarea.kx-form-control { resize: vertical; min-height: 120px; }

    .kx-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
        border-radius: 8px; font-size: 0.85rem; font-weight: 600; border: none;
        cursor: pointer; transition: opacity 0.15s, transform 0.1s; text-decoration: none; }
    .kx-btn:active { transform: scale(0.97); }
    .kx-btn-green  { background: var(--kx-green); color: #000; }
    .kx-btn-purple { background: var(--kx-purple); color: #fff; }
    .kx-btn-muted  { background: var(--kx-card2); border: 1px solid var(--kx-border); color: var(--kx-text); }
    .kx-btn-danger { background: var(--kx-danger); color: #fff; }

    .kx-badge { display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .kx-badge-green  { background: rgba(0,204,0,0.12);  color: #00cc00; }
    .kx-badge-red    { background: rgba(239,68,68,0.12); color: #ef4444; }
    .kx-badge-purple { background: rgba(168,85,247,0.15); color: #a855f7; }

    /* Toggle switch */
    .kx-toggle { position: relative; display: inline-block; width: 48px; height: 26px; }
    .kx-toggle input { display: none; }
    .kx-toggle-slider { position: absolute; inset: 0; background: var(--kx-card2);
        border: 1px solid var(--kx-border); border-radius: 26px; cursor: pointer; transition: 0.3s; }
    .kx-toggle-slider::before { content: ''; position: absolute; width: 20px; height: 20px;
        background: var(--kx-muted); border-radius: 50%; top: 2px; left: 2px; transition: 0.3s; }
    .kx-toggle input:checked + .kx-toggle-slider { background: rgba(0,204,0,0.2); border-color: var(--kx-green); }
    .kx-toggle input:checked + .kx-toggle-slider::before { background: var(--kx-green); transform: translateX(22px); }

    /* Range slider */
    input[type=range].kx-range { width: 100%; accent-color: var(--kx-purple); }

    /* Chat test panel */
    .ai-chat-box { background: var(--kx-card2); border: 1px solid var(--kx-border);
        border-radius: 10px; padding: 14px; height: 280px; overflow-y: auto;
        display: flex; flex-direction: column; gap: 10px; }
    .ai-msg { max-width: 80%; padding: 9px 13px; border-radius: 10px;
        font-size: 0.84rem; line-height: 1.5; white-space: pre-wrap; }
    .ai-msg-user { background: rgba(168,85,247,0.18); color: var(--kx-text);
        align-self: flex-end; border-bottom-right-radius: 2px; }
    .ai-msg-bot  { background: var(--kx-card); border: 1px solid var(--kx-border);
        color: var(--kx-text); align-self: flex-start; border-bottom-left-radius: 2px; }
    .ai-msg-sys  { color: var(--kx-muted); font-size: 0.75rem; text-align: center; align-self: center; }

    .alert-success { background: rgba(0,204,0,0.08); border: 1px solid rgba(0,204,0,0.25);
        color: #4ade80; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.875rem; }
    .alert-danger  { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25);
        color: #f87171; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.875rem; }
</style>

<div class="kx-welcome d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h4 class="mb-1" style="font-weight:700;">🤖 AI Trade Assistant</h4>
        <p class="mb-0" style="color:var(--kx-muted);font-size:0.85rem;">
            Configure KAI — the in-bot Telegram AI assistant powered by Groq
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($settings['ai_bot_enabled'] === '1')
            <span class="kx-badge kx-badge-green"><i class="bi bi-circle-fill" style="font-size:8px"></i> Active</span>
        @else
            <span class="kx-badge kx-badge-red"><i class="bi bi-circle-fill" style="font-size:8px"></i> Disabled</span>
        @endif
        <a href="{{ route('admin.telegram.index') }}" class="kx-btn kx-btn-muted" style="padding:7px 14px;font-size:0.8rem;">
            ← Telegram Settings
        </a>
    </div>
</div>

<div class="container-fluid px-3 pb-4">

    @if(session('success'))
        <div class="alert-success">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-danger">⚠️ {{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
        {{-- Left column: Configuration --}}
        <div class="col-lg-7">
            <form method="POST" action="{{ route('admin.telegram.ai-config.update') }}">
                @csrf
                @method('PUT')

                {{-- Enable / Disable --}}
                <div class="kx-panel">
                    <div class="kx-panel-header">
                        <h5>⚙️ Global Toggle</h5>
                    </div>
                    <div class="kx-panel-body">
                        <div class="d-flex align-items-center gap-3">
                            <label class="kx-toggle">
                                <input type="checkbox" name="ai_bot_enabled" value="1"
                                    {{ $settings['ai_bot_enabled'] === '1' ? 'checked' : '' }}>
                                <span class="kx-toggle-slider"></span>
                            </label>
                            <div>
                                <div style="font-size:0.9rem;font-weight:600;">Enable AI Trade Assistant</div>
                                <div class="kx-form-help">When disabled, the /ai command will show a "coming soon" message.</div>
                            </div>
                        </div>

                        <hr style="border-color:var(--kx-border);margin:18px 0;">

                        <div class="d-flex align-items-center gap-3">
                            <label class="kx-toggle">
                                <input type="checkbox" name="ai_bot_trade_suggestions" value="1"
                                    {{ $settings['ai_bot_trade_suggestions'] === '1' ? 'checked' : '' }}>
                                <span class="kx-toggle-slider"></span>
                            </label>
                            <div>
                                <div style="font-size:0.9rem;font-weight:600;">Allow Trade Suggestions</div>
                                <div class="kx-form-help">Let KAI suggest when a rate looks favourable. If off, only informational answers.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Welcome Message --}}
                <div class="kx-panel">
                    <div class="kx-panel-header">
                        <h5>👋 Welcome Message</h5>
                    </div>
                    <div class="kx-panel-body">
                        <div class="kx-form-group">
                            <label class="kx-form-label">Message shown when user types /ai (Markdown supported)</label>
                            <textarea name="ai_bot_welcome_message" class="kx-form-control" rows="4"
                                placeholder="Leave blank to use default welcome message...">{{ $settings['ai_bot_welcome_message'] }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- System Prompt --}}
                <div class="kx-panel">
                    <div class="kx-panel-header">
                        <h5>🧠 System Prompt</h5>
                    </div>
                    <div class="kx-panel-body">
                        <div class="kx-form-group">
                            <label class="kx-form-label">Custom system instructions for KAI</label>
                            <textarea name="ai_bot_system_prompt" class="kx-form-control" rows="6"
                                placeholder="Leave blank to use the built-in KayXchange default prompt...">{{ $settings['ai_bot_system_prompt'] }}</textarea>
                            <p class="kx-form-help">
                                Live rates, user balance and context are always injected automatically.
                                You only need to customise the personality / rules here.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Model Settings --}}
                <div class="kx-panel">
                    <div class="kx-panel-header">
                        <h5>🔧 Model Settings</h5>
                    </div>
                    <div class="kx-panel-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="kx-form-group mb-0">
                                    <label class="kx-form-label">Groq Model</label>
                                    <select name="ai_bot_model" class="kx-form-control">
                                        @foreach([
                                            'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile (default)',
                                            'llama-3.1-8b-instant'    => 'Llama 3.1 8B Instant (fast)',
                                            'llama3-70b-8192'         => 'Llama3 70B 8192',
                                            'mixtral-8x7b-32768'      => 'Mixtral 8×7B 32768',
                                            'gemma2-9b-it'            => 'Gemma2 9B IT',
                                        ] as $value => $label)
                                            <option value="{{ $value }}" {{ $settings['ai_bot_model'] === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="kx-form-group mb-0">
                                    <label class="kx-form-label">
                                        Max Tokens — <span id="tokenVal">{{ $settings['ai_bot_max_tokens'] }}</span>
                                    </label>
                                    <input type="range" name="ai_bot_max_tokens" class="kx-range"
                                        min="100" max="2048" step="50"
                                        value="{{ $settings['ai_bot_max_tokens'] }}"
                                        oninput="document.getElementById('tokenVal').textContent=this.value">
                                    <p class="kx-form-help">100 = short answers, 2048 = detailed. Recommended: 400–700.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="kx-form-group mb-0">
                                    <label class="kx-form-label">
                                        Temperature — <span id="tempVal">{{ $settings['ai_bot_temperature'] }}</span>
                                    </label>
                                    <input type="range" name="ai_bot_temperature" class="kx-range"
                                        min="0" max="1.5" step="0.1"
                                        value="{{ $settings['ai_bot_temperature'] }}"
                                        oninput="document.getElementById('tempVal').textContent=parseFloat(this.value).toFixed(1)">
                                    <p class="kx-form-help">0.0 = precise data, 1.0 = creative. Recommended: 0.5–0.7.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="kx-btn kx-btn-green w-100" style="padding:12px;">
                    <i class="bi bi-floppy"></i> Save Configuration
                </button>
            </form>
        </div>

        {{-- Right column: Test Chat --}}
        <div class="col-lg-5">
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5>🧪 Test KAI</h5>
                    <span class="kx-badge kx-badge-purple">Live Preview</span>
                </div>
                <div class="kx-panel-body">
                    <div id="chatBox" class="ai-chat-box mb-3">
                        <div class="ai-msg ai-msg-sys">Start chatting to test KAI's responses</div>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="text" id="testMsg" class="kx-form-control"
                            placeholder="Type a test message…" autocomplete="off">
                        <button id="sendBtn" class="kx-btn kx-btn-purple" style="white-space:nowrap;" onclick="sendTest()">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                    <p class="kx-form-help mt-2">
                        Uses <strong>saved</strong> settings. Click "Save" first if you changed the prompt or model.
                    </p>

                    <hr style="border-color:var(--kx-border);margin:16px 0;">

                    <p style="font-size:0.8rem;color:var(--kx-muted);">
                        <strong>Quick test prompts:</strong>
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(["What is today's BTC rate?", "Should I sell USDT now?", "How do I buy crypto?", "What's my balance?"] as $q)
                            <button class="kx-btn kx-btn-muted" style="font-size:0.75rem;padding:5px 10px;"
                                onclick="quickTest('{{ $q }}')">{{ $q }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Info panel --}}
            <div class="kx-panel">
                <div class="kx-panel-header">
                    <h5>ℹ️ How it Works</h5>
                </div>
                <div class="kx-panel-body" style="font-size:0.83rem;color:var(--kx-muted);line-height:1.7;">
                    <ul class="mb-0 ps-3">
                        <li>Users type <code>/ai</code> in the Telegram bot to enter AI mode.</li>
                        <li>KAI receives live rates, user balance and trade history context automatically.</li>
                        <li>Conversation history is kept for <strong>60 minutes</strong> of inactivity.</li>
                        <li>Users can exit at any time with <code>/cancel</code>.</li>
                        <li>Individual users can opt-out from their Telegram Settings page.</li>
                        <li>Powered by <strong>Groq</strong> — ultra-fast inference.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function appendMsg(text, type) {
    const box = document.getElementById('chatBox');
    const div = document.createElement('div');
    div.className = 'ai-msg ai-msg-' + type;
    div.textContent = text;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}

async function sendTest() {
    const input = document.getElementById('testMsg');
    const btn   = document.getElementById('sendBtn');
    const msg   = input.value.trim();
    if (!msg) return;

    appendMsg(msg, 'user');
    input.value = '';
    btn.disabled = true;

    const thinking = document.createElement('div');
    thinking.className = 'ai-msg ai-msg-sys';
    thinking.id = 'thinking';
    thinking.textContent = '⏳ KAI is thinking…';
    document.getElementById('chatBox').appendChild(thinking);
    document.getElementById('chatBox').scrollTop = 9999;

    try {
        const resp = await fetch('{{ route("admin.telegram.ai-config.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ message: msg }),
        });

        const data = await resp.json();
        document.getElementById('thinking')?.remove();

        if (data.reply) {
            appendMsg(data.reply, 'bot');
        } else {
            appendMsg('⚠️ Error: ' + (data.error ?? 'Unknown error'), 'sys');
        }
    } catch (e) {
        document.getElementById('thinking')?.remove();
        appendMsg('⚠️ Network error — check console.', 'sys');
    } finally {
        btn.disabled = false;
    }
}

function quickTest(q) {
    document.getElementById('testMsg').value = q;
    sendTest();
}

document.getElementById('testMsg').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') sendTest();
});
</script>
@endsection
