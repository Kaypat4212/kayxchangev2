{{-- AI Trading Chatbot Widget --}}
{{-- Include this in your main user layout (navlayout.blade.php), just before </body> --}}
@php
    $kaybotEnabled = \App\Models\AdminSetting::get('ai_chatbot_enabled', '1') == '1';
    $kaybotHasKey  = (bool) (
        \App\Models\AdminSetting::get('openai_api_key') ?: env('OPENAI_API_KEY') ?:
        \App\Models\AdminSetting::get('groq_api_key')   ?: env('GROQ_API_KEY')
    );
    $kaybotReady   = $kaybotEnabled && $kaybotHasKey;
@endphp
@if($kaybotEnabled)
<style>
#kaybot-wrap{position:fixed;bottom:24px;right:24px;z-index:9999;font-family:'Inter',sans-serif;}
#kaybot-toggle{width:54px;height:54px;border-radius:50%;background:linear-gradient(135deg,#00cc00,#009900);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(0,204,0,.45);transition:transform .2s;}
#kaybot-toggle:hover{transform:scale(1.08);}
#kaybot-toggle svg{width:26px;height:26px;fill:#fff;}
#kaybot-box{display:none;flex-direction:column;width:340px;height:480px;background:#0d1117;border:1px solid rgba(0,204,0,.2);border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,.7);margin-bottom:12px;}
#kaybot-box.open{display:flex;}
#kaybot-header{background:linear-gradient(135deg,#0a1f0a,#0a1628);padding:.75rem 1rem;display:flex;align-items:center;gap:.65rem;border-bottom:1px solid rgba(255,255,255,.07);}
#kaybot-header .avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#00cc00,#009900);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
#kaybot-header .meta h6{margin:0;font-size:.875rem;color:#fff;font-weight:700;}
#kaybot-header .meta p{margin:0;font-size:.7rem;color:#4ade80;}
#kaybot-close{margin-left:auto;background:none;border:none;color:#7a8599;cursor:pointer;font-size:1.1rem;padding:.2rem;}
#kaybot-close:hover{color:#fff;}
#kaybot-msgs{flex:1;overflow-y:auto;padding:.75rem 1rem;display:flex;flex-direction:column;gap:.6rem;scroll-behavior:smooth;}
#kaybot-msgs::-webkit-scrollbar{width:4px;}
#kaybot-msgs::-webkit-scrollbar-thumb{background:rgba(0,204,0,.25);border-radius:4px;}
.kb-msg{max-width:82%;padding:.55rem .8rem;border-radius:14px;font-size:.82rem;line-height:1.5;white-space:pre-wrap;}
.kb-msg.user{align-self:flex-end;background:linear-gradient(135deg,#00aa00,#007700);color:#fff;border-bottom-right-radius:4px;}
.kb-msg.bot{align-self:flex-start;background:#1e2535;color:#e4e8f0;border-bottom-left-radius:4px;}
.kb-msg.typing{color:#7a8599;font-style:italic;}
.kb-quick{display:flex;flex-wrap:wrap;gap:.35rem;padding:.5rem 1rem;border-top:1px solid rgba(255,255,255,.06);}
.kb-quick button{font-size:.72rem;padding:.25rem .65rem;border-radius:12px;background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.2);color:#4ade80;cursor:pointer;}
.kb-quick button:hover{background:rgba(0,204,0,.2);}
#kaybot-input-area{display:flex;padding:.65rem .75rem;gap:.5rem;border-top:1px solid rgba(255,255,255,.07);background:#0a0e17;}
#kaybot-input{flex:1;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#e4e8f0;padding:.5rem .75rem;font-size:.82rem;resize:none;outline:none;height:38px;}
#kaybot-input:focus{border-color:rgba(0,204,0,.4);}
#kaybot-send{width:38px;height:38px;background:var(--kx-green,#00cc00);border:none;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
#kaybot-send:hover{background:#009900;}
#kaybot-send svg{width:16px;height:16px;fill:#081108;}
.kb-badge{position:absolute;top:-4px;right:-4px;width:18px;height:18px;background:#ef4444;border-radius:50%;font-size:.62rem;color:#fff;display:flex;align-items:center;justify-content:center;display:none;}

/* ── Light mode overrides ─────────────────────────────────────────── */
body.light-mode #kaybot-box{background:#ffffff;border-color:rgba(0,150,0,.25);box-shadow:0 8px 40px rgba(0,0,0,.18);}
body.light-mode #kaybot-header{background:linear-gradient(135deg,#f0fff0,#e8f4f8);border-bottom-color:rgba(0,0,0,.08);}
body.light-mode #kaybot-header .meta h6{color:#111;}
body.light-mode #kaybot-header .meta p{color:#007a0c;}
body.light-mode #kaybot-close{color:#555;}
body.light-mode #kaybot-close:hover{color:#000;}
body.light-mode #kaybot-msgs{background:#f8f9fa;color:#111111;}
body.light-mode #kaybot-msgs::-webkit-scrollbar-thumb{background:rgba(0,150,0,.2);}
body.light-mode .kb-msg.bot{background:#e8e8e8;color:#111111 !important;}
body.light-mode .kb-msg.bot *{color:#111111 !important;}
body.light-mode .kb-msg.typing{color:#555 !important;}
body.light-mode .kb-quick{border-top-color:rgba(0,0,0,.08);}
body.light-mode .kb-quick button{background:rgba(0,150,0,.08);border-color:rgba(0,150,0,.25);color:#007a0c;}
body.light-mode .kb-quick button:hover{background:rgba(0,150,0,.18);}
body.light-mode #kaybot-input-area{background:#f0f0f0;border-top-color:rgba(0,0,0,.08);}
body.light-mode #kaybot-input{background:#ffffff;border-color:rgba(0,0,0,.15);color:#111111;}
body.light-mode #kaybot-input::placeholder{color:#888;}
body.light-mode #kaybot-input:focus{border-color:rgba(0,150,0,.45);}
</style>

<div id="kaybot-wrap">
    <div id="kaybot-box">
        <div id="kaybot-header">
            <div class="avatar">🤖</div>
            <div class="meta">
                <h6>KayBot</h6>
                <p>● Online — Trading Assistant</p>
            </div>
            <button id="kaybot-close" onclick="kaybotToggle()" title="Close">✕</button>
        </div>

        <div id="kaybot-msgs">
            @if($kaybotReady)
            <div class="kb-msg bot">👋 Hi{{ auth()->check() ? ' ' . auth()->user()->name : '' }}! I'm <strong>KayBot</strong>, your KayXchange trading assistant.<br><br>I can help you with rates, how to buy/sell crypto, account questions, and more. What would you like to know?</div>
            @else
            <div class="kb-msg bot">🤖 <strong>KayBot</strong> is almost ready!<br><br>Our AI assistant is being set up. Check back soon — it'll be available to help you with trading questions shortly.</div>
            @endif
        </div>

        @if($kaybotReady)
        <div class="kb-quick">
            <button onclick="kaybotAsk('How do I buy crypto?')">Buy crypto</button>
            <button onclick="kaybotAsk('What are the current rates?')">Current rates</button>
            <button onclick="kaybotAsk('How long does a trade take?')">Trade time</button>
            <button onclick="kaybotAsk('How do I contact support?')">Support</button>
        </div>
        @endif

        <div id="kaybot-input-area">
            <textarea id="kaybot-input" placeholder="{{ $kaybotReady ? 'Ask anything about trading…' : 'Coming soon…' }}" rows="1" {{ $kaybotReady ? '' : 'disabled' }} onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();kaybotSend();}"></textarea>
            <button id="kaybot-send" onclick="kaybotSend()" {{ $kaybotReady ? '' : 'disabled' }}>
                <svg viewBox="0 0 24 24"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
            </button>
        </div>
    </div>

    <div style="position:relative;display:inline-block;">
        <button id="kaybot-toggle" onclick="kaybotToggle()" title="Chat with KayBot">
            <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
        </button>
        <span class="kb-badge" id="kb-badge">1</span>
    </div>
</div>

<script>
let kaybotOpen = false;
let kaybotBusy = false;

function kaybotToggle() {
    kaybotOpen = !kaybotOpen;
    const box = document.getElementById('kaybot-box');
    if (kaybotOpen) {
        box.classList.add('open');
        document.getElementById('kb-badge').style.display = 'none';
        document.getElementById('kaybot-input').focus();
    } else {
        box.classList.remove('open');
    }
}

function kaybotAsk(text) {
    document.getElementById('kaybot-input').value = text;
    kaybotSend();
}

async function kaybotSend() {
    if (kaybotBusy) return;
    const input = document.getElementById('kaybot-input');
    const msg   = input.value.trim();
    if (!msg) return;

    @if(!$kaybotReady)
    return; // AI not configured yet
    @endif

    input.value = '';
    kaybotBusy  = true;
    kaybotAppend('user', msg);
    const typing = kaybotAppend('bot', '…', true);

    try {
        const r = await fetch('{{ route("ai.chat") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message: msg })
        });
        const data = await r.json();
        typing.remove();
        kaybotAppend('bot', data.reply ?? 'Sorry, something went wrong.');
    } catch (e) {
        typing.remove();
        kaybotAppend('bot', '⚠️ Connection error. Please try again.');
    }
    kaybotBusy = false;
}

function kaybotAppend(role, text, isTyping = false) {
    const msgs = document.getElementById('kaybot-msgs');
    const el   = document.createElement('div');
    el.className = 'kb-msg ' + role + (isTyping ? ' typing' : '');
    el.textContent = text;
    msgs.appendChild(el);
    msgs.scrollTop = msgs.scrollHeight;
    return el;
}

// Show badge when widget is closed after 3s
setTimeout(() => {
    if (!kaybotOpen) document.getElementById('kb-badge').style.display = 'flex';
}, 3000);
</script>
@endif
