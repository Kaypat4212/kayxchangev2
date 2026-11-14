@extends('adminnavlayout')

@section('content')
<style>
:root{
    --kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
    --kx-green:#00cc00;--kx-green-dim:rgba(0,204,0,0.12);
    --kx-orange:#f97316;--kx-red:#ef4444;
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
.trm-wrap{max-width:1220px;padding:24px clamp(12px,2.4vw,30px) 56px}
.trm-hdr{position:relative;overflow:hidden;padding:clamp(20px,3vw,32px);border:1px solid rgba(148,163,184,.16);border-radius:24px;background:radial-gradient(ellipse at 95% 0,rgba(34,197,94,.16),transparent 36%),linear-gradient(135deg,#101824,#111d1a);box-shadow:0 20px 55px rgba(0,0,0,.2)}
.trm-hdr-icon{width:58px;height:58px;border-radius:18px;color:#54e889;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.19)}
.trm-hdr-title{font-size:clamp(1.35rem,3vw,1.8rem);letter-spacing:-.035em;color:#f8fafc}
.trm-hdr-sub{max-width:660px;font-size:.88rem;line-height:1.6;color:#a6b4c2}
.trm-hdr-meta{margin-left:auto;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.trm-secure-pill{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(34,197,94,.24);border-radius:999px;background:rgba(34,197,94,.08);color:#90efb2;padding:8px 12px;font-size:.72rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.trm-topbar{padding:12px 14px;border:1px solid var(--kx-border);border-radius:14px;background:rgba(22,27,39,.82)}
.trm-cmd-card,.trm-allowed-card{border-radius:18px;padding:clamp(16px,2vw,22px);box-shadow:0 12px 34px rgba(0,0,0,.1)}
.trm-cmd-label,.trm-quick-label,.trm-allowed-title{font-size:.69rem;font-weight:800;letter-spacing:.12em}
.trm-cmd-row{align-items:stretch}
.trm-cmd-inp{min-width:0;min-height:48px}
.trm-run-btn{min-width:112px;border-radius:11px}
.trm-output{min-height:190px;max-height:480px;border-radius:13px;padding:18px;color:#c2f2ce}
.trm-quick-grid{gap:9px}
.trm-quick-btn{min-height:38px;border-radius:10px}
.trm-pin-card{max-width:470px;padding:clamp(23px,4vw,38px);border-radius:23px;box-shadow:0 24px 65px rgba(0,0,0,.24)}
.trm-pin-inp{min-height:54px}
.trm-pin-btn{min-height:48px;border-radius:11px}
.trm-workspace{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(270px,.85fr);gap:16px;align-items:start}
.trm-main-column,.trm-side-column{min-width:0}
.trm-side-note{margin-top:14px;padding:15px 16px;border-radius:14px;border:1px solid rgba(148,163,184,.14);background:rgba(22,27,39,.62);color:#9ba9b8;font-size:.8rem;line-height:1.65}
.trm-side-note strong{display:block;color:#e6edf3;margin-bottom:4px}
.trm-pin-config{padding:14px;border:1px solid rgba(251,191,36,.22);border-radius:12px;background:rgba(251,191,36,.07);color:#f4d38a;font-size:.82rem;line-height:1.6;margin-top:14px}
.trm-pin-config a{color:#b6f2ca;font-weight:700}
.trm-output-status{display:inline-block;margin-top:8px;padding:4px 8px;border-radius:7px;background:rgba(148,163,184,.09);color:#aab7c5;font-size:.73rem}
@media(max-width:820px){.trm-workspace{grid-template-columns:1fr}.trm-side-column{display:grid;grid-template-columns:1fr 1fr;gap:14px}.trm-side-note{margin-top:0}}
@media(max-width:560px){.trm-hdr{align-items:flex-start;flex-wrap:wrap;border-radius:19px}.trm-hdr-meta{width:100%;margin-left:72px}.trm-topbar{align-items:stretch;flex-direction:column}.trm-topbar>*{width:100%;justify-content:center}.trm-cmd-row{flex-direction:column}.trm-run-btn{width:100%}.trm-side-column{display:flex;flex-direction:column}.trm-output{font-size:.76rem;min-height:150px}}
/* Operations console refresh */
.trm-wrap{max-width:1380px;padding:clamp(16px,3vw,38px) clamp(12px,2.5vw,34px) 68px;color:#edf4f7}
.trm-hdr{display:flex;gap:18px;margin-bottom:22px;padding:clamp(22px,4vw,38px);border-radius:26px;border:1px solid rgba(148,163,184,.15);background:radial-gradient(ellipse at 86% 0,rgba(34,197,94,.18),transparent 34%),radial-gradient(ellipse at 10% 110%,rgba(56,189,248,.09),transparent 32%),linear-gradient(130deg,#111a24,#101914);box-shadow:0 22px 65px rgba(0,0,0,.22)}
.trm-hdr-icon{width:58px;height:58px;border-radius:18px;display:grid;place-items:center;font-size:1.45rem;color:#71ef9b;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.22)}
.trm-hdr-title{margin:0;color:#f6faf8;font-size:clamp(1.45rem,3vw,2rem);font-weight:850;letter-spacing:-.045em}
.trm-hdr-sub{margin-top:7px;color:#9baeb8;font-size:.88rem;line-height:1.6}
.trm-hdr-meta{margin-left:auto;align-self:flex-start}.trm-secure-pill{padding:9px 13px}
.trm-topbar{display:flex;gap:10px;align-items:center;padding:12px 14px;margin:0 0 18px;border:1px solid rgba(148,163,184,.14);border-radius:16px;background:linear-gradient(130deg,rgba(23,33,43,.96),rgba(18,27,34,.9));box-shadow:0 8px 24px rgba(0,0,0,.12)}
.trm-timer{display:flex;align-items:center;gap:8px;margin:0 auto 0 0;padding:8px 11px;border-radius:10px;background:rgba(255,255,255,.04);font-size:.78rem}.trm-timer:before{content:'●';font-size:.56rem;color:#4ade80;text-shadow:0 0 9px #4ade80}.trm-timer span{font-variant-numeric:tabular-nums;font-weight:800}
.trm-workspace{grid-template-columns:minmax(0,1.7fr) minmax(285px,.8fr);gap:18px}.trm-cmd-card,.trm-allowed-card{border:1px solid rgba(148,163,184,.13);border-radius:20px;background:linear-gradient(145deg,rgba(22,30,40,.98),rgba(16,23,31,.98));box-shadow:0 16px 40px rgba(0,0,0,.16)}
.trm-cmd-card{padding:clamp(18px,2.5vw,26px);margin-bottom:16px}.trm-quick-label,.trm-cmd-label,.trm-allowed-title{color:#dce8e4;font-size:.75rem;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.trm-hdr-sub{max-width:none}
.trm-quick-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.trm-quick-btn{padding:10px 12px;text-align:left;background:#111922;border-color:rgba(148,163,184,.13);font-family:ui-monospace,SFMono-Regular,Menlo,monospace;color:#b8c7d1;transition:transform .16s,border-color .16s,background .16s}.trm-quick-btn:before{content:'›';color:#4ade80;margin-right:7px;font-weight:900}.trm-quick-btn:hover{transform:translateY(-1px);background:rgba(34,197,94,.07)}
.trm-cmd-row{gap:9px}.trm-cmd-inp{background:#0a1016;border-color:rgba(148,163,184,.2);border-radius:12px;color:#f2f7f5}.trm-run-btn{border-radius:12px;padding:10px 18px;box-shadow:0 7px 20px rgba(0,204,0,.14)}
.trm-output-wrap{position:relative;margin-top:14px;padding:14px;border:1px solid rgba(148,163,184,.14);border-radius:15px;background:#090e13}.trm-output-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 0 8px;color:#8495a0;font-size:.69rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.trm-output-head span:first-child:before{content:'';display:inline-block;width:7px;height:7px;margin-right:7px;border-radius:50%;background:#4ade80}.trm-output{margin:0;padding:12px 2px 2px;min-height:150px;max-height:440px;border:0;border-radius:0;background:transparent;font-size:.8rem;line-height:1.75;overflow-wrap:anywhere}.trm-output-action{padding:5px 8px;border:1px solid rgba(148,163,184,.15);border-radius:8px;background:rgba(255,255,255,.04);color:#b7c6ce;font-size:.68rem}.trm-output-action:hover{color:#fff;border-color:rgba(74,222,128,.4)}
.trm-allowed-card{padding:20px}.trm-allowed-list{gap:7px}.trm-allowed-tag{padding:6px 9px;border-radius:8px;background:#101820;color:#9fb1bd;border-color:rgba(148,163,184,.13)}.trm-side-note{margin-top:12px;padding:17px;border-color:rgba(56,189,248,.15);background:rgba(13,26,34,.64);border-radius:16px}.trm-side-note strong{color:#dcecf4}
.trm-pin-screen{align-items:center;min-height:calc(100vh - 340px);padding:25px 0}.trm-pin-card{position:relative;overflow:hidden;max-width:480px;padding:clamp(25px,5vw,42px);border:1px solid rgba(148,163,184,.17);border-radius:25px;background:radial-gradient(ellipse at 50% 0,rgba(34,197,94,.12),transparent 43%),linear-gradient(145deg,#17212b,#10171e);box-shadow:0 26px 80px rgba(0,0,0,.32)}.trm-pin-icon{display:grid;place-items:center;width:62px;height:62px;margin:0 auto 17px;border-radius:20px;background:rgba(34,197,94,.12);color:#68e897;font-size:1.6rem}.trm-pin-title{color:#f3f7f5;font-size:1.32rem;font-weight:850}.trm-pin-sub{max-width:340px;margin:0 auto 22px;color:#9aaab5;line-height:1.65}.trm-pin-inp{background:#0a1016;border-color:rgba(148,163,184,.2);border-radius:12px}.trm-pin-btn{border-radius:12px;box-shadow:0 8px 23px rgba(0,204,0,.14)}
@media(max-width:900px){.trm-workspace{grid-template-columns:1fr}.trm-side-column{display:grid;grid-template-columns:1fr 1fr;gap:12px}.trm-side-note{margin:0}}
@media(max-width:620px){.trm-hdr{gap:13px;border-radius:20px}.trm-hdr-icon{width:48px;height:48px;border-radius:15px}.trm-hdr-meta{width:100%;margin:0}.trm-topbar{align-items:stretch;flex-direction:column}.trm-timer{margin:0;justify-content:center}.trm-topbar>*{width:100%;justify-content:center}.trm-workspace{gap:12px}.trm-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.trm-cmd-row{flex-direction:column}.trm-run-btn{width:100%}.trm-side-column{display:flex;flex-direction:column}.trm-output{min-height:125px;font-size:.75rem}.trm-pin-screen{min-height:0}}
@media(max-width:380px){.trm-quick-grid{grid-template-columns:1fr}.trm-output-wrap{padding:11px}}
</style>

<div class="trm-wrap">
    <div class="trm-hdr">
        <div class="trm-hdr-icon"><i class="bi bi-terminal-fill"></i></div>
        <div>
            <p class="trm-quick-label mb-1" style="color:#78e5a0">PLATFORM OPERATIONS</p>
            <h1 class="trm-hdr-title">System terminal</h1>
            <p class="trm-hdr-sub">Run approved Laravel maintenance tasks from a protected operations workspace.</p>
        </div>
        <div class="trm-hdr-meta"><span class="trm-secure-pill"><i class="bi bi-shield-lock-fill"></i> Restricted session</span></div>
    </div>

    @if(!$unlocked)
    {{-- ── PIN Lock Screen ── --}}
    <div class="trm-pin-screen">
        <div class="trm-pin-card">
            <div class="trm-pin-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <p class="trm-pin-title">Unlock operations console</p>
            <p class="trm-pin-sub">Verify your separate terminal PIN to open the operations console.</p>

            @if(session('pin_error'))
                <div class="trm-pin-err">{{ session('pin_error') }}</div>
            @endif

            @if(!$terminalPinConfigured)
                <div class="trm-pin-config">Terminal access has not been configured yet. Add <code>ADMIN_TERMINAL_PIN</code> in <a href="{{ route('admin.env.index') }}">Admin → Environment Settings</a>, then refresh this page.</div>
            @else

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
            @endif
        </div>
    </div>

    @else
    {{-- ── Terminal Panel ── --}}
    <div class="trm-topbar">
        <p class="trm-timer">Secure session · auto-locks in <span id="countdown">30:00</span></p>

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

    <div class="trm-workspace">
    <div class="trm-main-column">
    {{-- Quick commands --}}
    <div class="trm-cmd-card">
        <p class="trm-quick-label"><i class="bi bi-lightning-charge-fill me-2" style="color:#55df83"></i>Quick actions</p>
        <p class="trm-hdr-sub mb-3">Select a task to fill the command box. Review it before running.</p>
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
        <p class="trm-cmd-label"><i class="bi bi-code-slash me-2" style="color:#55df83"></i>Command runner</p>
        <p class="trm-hdr-sub mb-3">Only commands on the server allowlist can run. Command arguments are validated by Laravel.</p>
        <div class="trm-cmd-row">
            <input type="text" id="cmdInput" class="trm-cmd-inp"
                placeholder="e.g. migrate:status"
                onkeydown="if(event.key==='Enter')runCmd()">
            <button class="trm-run-btn" id="runBtn" onclick="runCmd()">▶ Run</button>
        </div>
        <div class="trm-output-wrap"><div class="trm-output-head"><span>Execution output</span><span class="d-flex gap-2"><button class="trm-output-action" type="button" onclick="copyOutput()"><i class="bi bi-copy me-1"></i>Copy</button><button class="trm-output-action" type="button" onclick="clearOutput()"><i class="bi bi-eraser me-1"></i>Clear</button></span></div><div id="output" class="trm-output" aria-live="polite"><span style="color:var(--kx-muted);">Choose a quick action or enter an allowlisted command to get started.</span></div></div>
    </div>
    </div>

    {{-- Allowed commands list --}}
    <aside class="trm-side-column">
    <div class="trm-allowed-card">
        <p class="trm-allowed-title"><i class="bi bi-shield-check me-2" style="color:#55df83"></i>Server command allowlist</p>
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
    <div class="trm-side-note"><strong><i class="bi bi-shield-check me-1"></i>Session protection</strong>The console locks after 30 minutes. Commands are checked against the server allowlist before they run. Lock the session before leaving this device.</div>
    </aside>
    </div>
    @endif
</div>

@if($unlocked)
<script>
const CSRF = '{{ csrf_token() }}';
const RUN_URL = '{{ route("admin.terminal.artisan") }}';

const SITE_MODE_TOGGLE_URL = '{{ route("admin.site-mode.toggle") }}';

function kxSetSiteModeUI(mode) {
    const btn = document.getElementById('kx-mode-toggle-btn-terminal');
    if (!btn) return;

    const nextMode = mode === 'developer' ? 'developer' : 'production';
    btn.dataset.mode = nextMode;
    btn.classList.remove('mode-production', 'mode-developer');
    btn.classList.add(nextMode === 'developer' ? 'mode-developer' : 'mode-production');

    const labelEl = btn.querySelector('span.kx-mode-label');
    // If label span doesn't exist, update by matching the second span
    if (labelEl) {
        labelEl.textContent = nextMode === 'developer' ? 'Developer Mode' : 'Production Mode';
    } else {
        const spans = btn.querySelectorAll('span');
        if (spans && spans.length >= 2) {
            spans[1].textContent = nextMode === 'developer' ? 'Developer Mode' : 'Production Mode';
        }
    }
}

function kxShowModeOverlay() {
    const overlay = document.getElementById('kx-mode-overlay-terminal');
    if (!overlay) return;
    overlay.classList.add('active');

    const title = document.getElementById('kx-mode-confirm-title-terminal');
    const postMsg = document.getElementById('kx-mode-post-msg-terminal');

    if (title) title.textContent = 'Switch Site Mode?';

    if (postMsg) {
        postMsg.style.display = 'none';
        postMsg.textContent = '';
        postMsg.className = 'mt-3';
    }
}

function kxHideModeOverlay() {
    const overlay = document.getElementById('kx-mode-overlay-terminal');
    if (!overlay) return;
    overlay.classList.remove('active');
}

// Wire toggle button + confirmation
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('kx-mode-toggle-btn-terminal');
    const confirmBtn = document.getElementById('kx-mode-confirm-btn-terminal');

    if (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            kxShowModeOverlay();
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function () {
            try {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Switching…';

                const res = await fetch(SITE_MODE_TOGGLE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                const data = await res.json();

                if (!res.ok || !data || !data.mode) {
                    throw new Error(data && data.message ? data.message : 'Toggle failed');
                }

                kxSetSiteModeUI(data.mode);

                const postMsg = document.getElementById('kx-mode-post-msg-terminal');
                if (postMsg) {
                    postMsg.style.display = 'block';
                    postMsg.style.color = '#00cc00';
                    postMsg.textContent = 'Mode updated to ' + (data.mode === 'developer' ? 'Developer' : 'Production') + '.';
                }

                setTimeout(function () {
                    kxHideModeOverlay();
                    if (postMsg) {
                        postMsg.style.display = 'none';
                        postMsg.textContent = '';
                    }
                }, 600);
            } catch (e) {
                const postMsg = document.getElementById('kx-mode-post-msg-terminal');
                if (postMsg) {
                    postMsg.style.display = 'block';
                    postMsg.style.color = '#ef4444';
                    postMsg.textContent = 'Failed to switch mode. Please try again.';
                }
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Yes, Switch Mode';
            }
        });
    }
});

function setCmd(cmd) {
    document.getElementById('cmdInput').value = cmd;
    document.getElementById('cmdInput').focus();
}

function clearOutput() {
    const output = document.getElementById('output');
    if (output) output.innerHTML = '<span style="color:var(--kx-muted);">Output cleared. Run an approved command to see results.</span>';
}

async function copyOutput() {
    const output = document.getElementById('output');
    if (!output) return;
    const text = output.innerText.trim();
    if (!text) return;
    try {
        await navigator.clipboard.writeText(text);
        const btn = document.querySelector('.trm-output-action');
        if (btn) { const previous = btn.innerHTML; btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied'; setTimeout(() => btn.innerHTML = previous, 1400); }
    } catch (_) {
        const range = document.createRange();
        range.selectNodeContents(output);
        const selection = window.getSelection();
        selection.removeAllRanges(); selection.addRange(range);
    }
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
        const data = await res.json().catch(() => ({}));

        if (!res.ok || data.error) {
            const message = data.output || data.error || data.message || ('Request failed with HTTP ' + res.status);
            output.innerHTML = '<span class="trm-prompt">$ php artisan ' + escHtml(cmd) + '</span>\n' +
                '<span class="trm-err">❌ ' + escHtml(message) + '</span>\n' +
                '<span class="trm-output-status">Request failed · HTTP ' + res.status + '</span>';
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
