@extends('adminnavlayout')

@section('content')
<style>
:root{
    --kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
    --kx-green:#00cc00;--kx-green-dim:rgba(0,204,0,0.12);
    --kx-orange:#f97316;--kx-red:#ef4444;
}
body{background:var(--kx-dark);color:var(--kx-text);font-family:'Poppins',sans-serif;}
.trm-wrap{padding:28px 20px 60px;max-width:920px;margin:0 auto;}
.trm-hdr{display:flex;align-items:center;gap:14px;margin-bottom:28px;}
.trm-hdr-icon{width:48px;height:48px;border-radius:14px;background:var(--kx-green-dim);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.trm-hdr-title{font-size:1.3rem;font-weight:700;margin:0;}
.trm-hdr-sub{font-size:.8rem;color:var(--kx-muted);margin:2px 0 0;}

/* PIN screen */
.trm-pin-screen{display:flex;justify-content:center;align-items:flex-start;padding-top:20px;}
.trm-pin-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:20px;padding:36px 32px;width:100%;max-width:400px;text-align:center;}
.trm-pin-icon{font-size:2.4rem;margin-bottom:12px;}
.trm-pin-title{font-size:1.1rem;font-weight:700;margin-bottom:6px;}
.trm-pin-sub{font-size:.82rem;color:var(--kx-muted);margin-bottom:24px;}
.trm-pin-inp{
    width:100%;background:var(--kx-dark);border:1.5px solid var(--kx-border);
    border-radius:10px;color:var(--kx-text);font-size:1.4rem;letter-spacing:.4rem;
    padding:.7rem 1rem;outline:none;text-align:center;font-family:inherit;
    transition:border-color .2s;
}
.trm-pin-inp:focus{border-color:var(--kx-green);box-shadow:0 0 0 3px var(--kx-green-dim);}
.trm-pin-btn{
    width:100%;padding:13px;background:var(--kx-green);color:#0d1117;border:none;
    border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;
    transition:opacity .2s;margin-top:14px;
}
.trm-pin-btn:hover{opacity:.85;}
.trm-pin-btn:disabled{opacity:.5;cursor:not-allowed;}
.trm-pin-err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;border-radius:9px;padding:10px 14px;font-size:.83rem;margin-top:14px;}
.trm-pin-attempts{font-size:.78rem;color:var(--kx-muted);margin-top:10px;}

/* Terminal panel */
.trm-topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;}
.trm-lock-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.25);border-radius:9px;font-size:.8rem;font-weight:600;cursor:pointer;text-decoration:none;}
.trm-lock-btn:hover{background:rgba(239,68,68,.2);color:#f87171;}
.trm-cmd-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:16px;padding:22px;margin-bottom:18px;}
.trm-cmd-label{font-size:.75rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;}
.trm-cmd-row{display:flex;gap:10px;}
.trm-cmd-inp{
    flex:1;background:var(--kx-dark);border:1.5px solid var(--kx-border);
    border-radius:9px;color:var(--kx-text);font-size:.9rem;padding:.65rem .9rem;
    outline:none;font-family:'Courier New',monospace;transition:border-color .2s;
}
.trm-cmd-inp:focus{border-color:var(--kx-green);box-shadow:0 0 0 3px var(--kx-green-dim);}
.trm-run-btn{padding:10px 20px;background:var(--kx-green);color:#0d1117;border:none;border-radius:9px;font-size:.88rem;font-weight:700;cursor:pointer;transition:opacity .2s;white-space:nowrap;}
.trm-run-btn:hover{opacity:.85;}
.trm-run-btn:disabled{opacity:.5;cursor:not-allowed;}
.trm-output{
    background:#0a0c10;border:1px solid var(--kx-border);border-radius:10px;
    padding:16px;min-height:120px;max-height:400px;overflow-y:auto;
    font-family:'Courier New',monospace;font-size:.83rem;line-height:1.7;
    color:#a8d8a8;white-space:pre-wrap;word-break:break-all;margin-top:14px;
}
.trm-output .trm-prompt{color:var(--kx-green);font-weight:700;}
.trm-output .trm-err{color:#f87171;}
.trm-output .trm-ok{color:#4ade80;}
.trm-quick-label{font-size:.75rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;}
.trm-quick-grid{display:flex;flex-wrap:wrap;gap:8px;}
.trm-quick-btn{
    padding:7px 13px;background:var(--kx-card2);border:1px solid var(--kx-border);
    border-radius:8px;color:var(--kx-text);font-size:.78rem;font-family:'Courier New',monospace;
    cursor:pointer;transition:all .15s;
}
.trm-quick-btn:hover{border-color:var(--kx-green);color:var(--kx-green);}
.trm-allowed-card{background:var(--kx-card);border:1px solid var(--kx-border);border-radius:14px;padding:16px 20px;margin-top:18px;}
.trm-allowed-title{font-size:.8rem;color:var(--kx-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;}
.trm-allowed-list{display:flex;flex-wrap:wrap;gap:6px;}
.trm-allowed-tag{padding:4px 10px;background:var(--kx-dark);border:1px solid var(--kx-border);border-radius:6px;font-size:.73rem;font-family:'Courier New',monospace;color:#7a8599;}
.trm-timer{font-size:.78rem;color:var(--kx-muted);}
.trm-timer span{color:var(--kx-green);}
</style>

<div class="trm-wrap">
    <div class="trm-hdr">
        <div class="trm-hdr-icon">⚡</div>
        <div>
            <p class="trm-hdr-title">Admin Terminal</p>
            <p class="trm-hdr-sub">Run Artisan commands securely — requires separate PIN</p>
        </div>
    </div>

    @if(!$unlocked)
    {{-- ── PIN Lock Screen ── --}}
    <div class="trm-pin-screen">
        <div class="trm-pin-card">
            <div class="trm-pin-icon">🔒</div>
            <p class="trm-pin-title">Terminal Locked</p>
            <p class="trm-pin-sub">Enter your admin terminal PIN to continue</p>

            @if(session('pin_error'))
                <div class="trm-pin-err">{{ session('pin_error') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.terminal.unlock') }}">
                @csrf
                <input type="password" name="pin" class="trm-pin-inp"
                    placeholder="••••••" autofocus maxlength="20"
                    autocomplete="current-password">
                <button type="submit" class="trm-pin-btn">Unlock Terminal</button>
                @if(session('pin_attempts_left'))
                    <p class="trm-pin-attempts">
                        {{ session('pin_attempts_left') }} attempt(s) remaining before lockout
                    </p>
                @endif
            </form>
        </div>
    </div>

    @else
    {{-- ── Terminal Panel ── --}}
    <div class="trm-topbar">
        <p class="trm-timer">Session active · auto-locks in <span id="countdown">30:00</span></p>

        @php $siteMode = $siteMode ?? 'production'; @endphp
        <button id="kx-mode-toggle-btn-terminal"
            class="kx-mode-badge {{ $siteMode === 'developer' ? 'mode-developer' : 'mode-production' }}"
            data-mode="{{ $siteMode }}"
            title="Toggle site mode">
            <span class="kx-mode-dot"></span>
            <span>{{ $siteMode === 'developer' ? 'Developer Mode' : 'Production Mode' }}</span>
            <i class="bi bi-arrow-repeat" style="font-size:0.7rem;opacity:0.7"></i>
        </button>

        <form method="POST" action="{{ route('admin.terminal.lock') }}" style="margin:0;">
            @csrf
            <button type="submit" class="trm-lock-btn"><i class="bi bi-lock"></i> Lock Terminal</button>
        </form>
    </div>

    <!-- MODE CONFIRM OVERLAY (Terminal) -->
    <div id="kx-mode-overlay-terminal" class="kx-mode-confirm-overlay">
        <div class="kx-mode-confirm-box">
            <div id="kx-mode-confirm-icon-terminal" class="kx-mode-confirm-icon">🔄</div>
            <div id="kx-mode-confirm-title-terminal" class="kx-mode-confirm-title">Switch Site Mode?</div>
            <div id="kx-mode-confirm-text-terminal" class="kx-mode-confirm-text"></div>
            <div class="kx-mode-confirm-btns">
                <button class="btn-kx-cancel" onclick="document.getElementById('kx-mode-overlay-terminal').classList.remove('active')">Cancel</button>
                <button id="kx-mode-confirm-btn-terminal" class="btn-kx-amber">Yes, Switch Mode</button>
            </div>
            <div id="kx-mode-post-msg-terminal" class="mt-3" style="display:none;font-size:0.82rem"></div>
        </div>
    </div>

    {{-- Quick commands --}}
    <div class="trm-cmd-card">
        <p class="trm-quick-label">Quick Commands</p>
        <div class="trm-quick-grid">
            @foreach([
                'migrate:status','cache:clear','config:cache','route:cache',
                'view:cache','optimize:clear','queue:restart','about','storage:link'
            ] as $qc)
                <button class="trm-quick-btn" onclick="setCmd('{{ $qc }}')">{{ $qc }}</button>
            @endforeach
        </div>
    </div>

    {{-- Command runner --}}
    <div class="trm-cmd-card">
        <p class="trm-cmd-label">Run Artisan Command</p>
        <div class="trm-cmd-row">
            <input type="text" id="cmdInput" class="trm-cmd-inp"
                placeholder="e.g. migrate:status"
                onkeydown="if(event.key==='Enter')runCmd()">
            <button class="trm-run-btn" id="runBtn" onclick="runCmd()">▶ Run</button>
        </div>
        <div id="output" class="trm-output"><span style="color:var(--kx-muted);">— Output will appear here —</span></div>
    </div>

    {{-- Allowed commands list --}}
    <div class="trm-allowed-card">
        <p class="trm-allowed-title">Allowed Commands</p>
        <div class="trm-allowed-list">
            @foreach([
                'migrate','migrate:status','migrate:rollback','db:seed',
                'cache:clear','config:clear','config:cache',
                'route:clear','route:cache','view:clear','view:cache',
                'queue:restart','storage:link','optimize','optimize:clear',
                'schedule:run','about','inspire','telegram:setup','down','up'
            ] as $cmd)
                <span class="trm-allowed-tag">{{ $cmd }}</span>
            @endforeach
        </div>
    </div>
    @endif
</div>

@if($unlocked)
<script>
const CSRF = '{{ csrf_token() }}';
const RUN_URL = '{{ route("admin.terminal.artisan") }}';

function setCmd(cmd) {
    document.getElementById('cmdInput').value = cmd;
    document.getElementById('cmdInput').focus();
}

async function runCmd() {
    const input = document.getElementById('cmdInput');
    const runBtn = document.getElementById('runBtn');
    const output = document.getElementById('output');
    const cmd = input.value.trim();
    if (!cmd) return;

    runBtn.disabled = true;
    runBtn.textContent = '⏳ Running…';
    output.innerHTML = '<span style="color:#7a8599;">Running: php artisan ' + escHtml(cmd) + ' …</span>\n';

    try {
        const res = await fetch(RUN_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ command: cmd })
        });
        const data = await res.json();

        if (data.error) {
            output.innerHTML = '<span class="trm-err">❌ ' + escHtml(data.error) + '</span>';
        } else {
            const out = data.output?.trim() || '(no output)';
            const exitCode = data.exit_code ?? 0;
            const statusClass = exitCode === 0 ? 'trm-ok' : 'trm-err';
            const statusIcon = exitCode === 0 ? '✅' : '❌';
            output.innerHTML =
                '<span class="trm-prompt">$ php artisan ' + escHtml(cmd) + '</span>\n' +
                escHtml(out) + '\n\n' +
                '<span class="' + statusClass + '">' + statusIcon + ' Exited with code ' + exitCode + '</span>';
        }
    } catch (e) {
        output.innerHTML = '<span class="trm-err">❌ Request failed: ' + escHtml(e.message) + '</span>';
    } finally {
        runBtn.disabled = false;
        runBtn.textContent = '▶ Run';
    }
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Countdown timer (auto-lock after 30 min, purely cosmetic — server enforces)
let secs = 30 * 60;
const countdownEl = document.getElementById('countdown');
const timer = setInterval(function() {
    secs--;
    if (secs <= 0) {
        clearInterval(timer);
        countdownEl.textContent = '00:00';
        countdownEl.style.color = '#ef4444';
        return;
    }
    const m = Math.floor(secs / 60).toString().padStart(2, '0');
    const s = (secs % 60).toString().padStart(2, '0');
    countdownEl.textContent = m + ':' + s;
}, 1000);
</script>
@endif
@endsection
