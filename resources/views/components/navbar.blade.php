<style>
.kx-navbar {
    background: rgba(8, 14, 8, 0.97);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 204, 0, 0.12);
    padding: 10px 0;
    transition: all 0.3s ease;
    position: sticky;
    top: 0;
    z-index: 1040;
}
.kx-navbar.kx-scrolled {
    padding: 6px 0;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.6);
    border-bottom-color: rgba(0, 204, 0, 0.2);
}
.kx-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none !important;
    color: #ffffff;
    font-size: 1.2rem;
    font-weight: 700;
    letter-spacing: -0.3px;
}
.kx-brand img {
    width: 36px; height: 36px;
    border-radius: 8px;
    box-shadow: 0 0 14px rgba(0, 204, 0, 0.45);
}
.kx-brand-green { color: #00cc00; }
.kx-nav-link {
    color: rgba(255, 255, 255, 0.75) !important;
    font-weight: 500;
    font-size: 0.875rem;
    padding: 7px 12px !important;
    border-radius: 8px;
    transition: all 0.22s ease;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    position: relative;
}
.kx-nav-link:hover { color: #00cc00 !important; background: rgba(0, 204, 0, 0.08); }
.kx-nav-link.kx-active { color: #00cc00 !important; background: rgba(0, 204, 0, 0.1); }
.kx-nav-link.kx-active::after {
    content: '';
    position: absolute;
    bottom: 1px; left: 50%;
    transform: translateX(-50%);
    width: 16px; height: 2px;
    background: #00cc00;
    border-radius: 2px;
}
.kx-btn-login {
    color: #00cc00 !important;
    border: 1.5px solid rgba(0, 204, 0, 0.5);
    border-radius: 25px;
    padding: 9px 22px;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none !important;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(0,204,0,0.06);
    backdrop-filter: blur(8px);
    position: relative;
    overflow: hidden;
}
.kx-btn-login::before { content:''; position:absolute; inset:0; background: linear-gradient(135deg,rgba(0,204,0,0.12),transparent); opacity:0; transition:opacity 0.25s ease; border-radius:25px; pointer-events:none; }
.kx-btn-login:hover { background: rgba(0, 204, 0, 0.12); border-color: #00cc00; box-shadow: 0 0 20px rgba(0,204,0,0.22), inset 0 1px 0 rgba(255,255,255,0.08); }
.kx-btn-login:hover::before { opacity:1; }
.kx-btn-register {
    background: linear-gradient(135deg, #00cc00 0%, #009e0f 50%, #007a0c 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 25px;
    padding: 10px 24px;
    font-weight: 700;
    font-size: 0.875rem;
    text-decoration: none !important;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 4px 20px rgba(0, 204, 0, 0.38), inset 0 1px 0 rgba(255,255,255,0.2);
    position: relative;
    overflow: hidden;
}
.kx-btn-register::after { content:''; position:absolute; inset:0; background: linear-gradient(135deg,rgba(255,255,255,0.18) 0%,transparent 55%); border-radius:25px; pointer-events:none; }
.kx-btn-register:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0, 204, 0, 0.52); color: #fff !important; }
.kx-btn-register:active { transform: translateY(0); box-shadow: 0 3px 12px rgba(0, 204, 0, 0.35); }
.kx-btn-logout {
    color: rgba(255, 110, 110, 0.9);
    border: 1.5px solid rgba(255, 80, 80, 0.35);
    background: transparent;
    border-radius: 25px;
    padding: 7px 16px;
    font-weight: 500;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.22s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.kx-btn-logout:hover { background: rgba(255, 80, 80, 0.1); border-color: rgba(255,80,80,0.7); color: #ff5555; }
.kx-theme-btn {
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.6);
    border-radius: 50%;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.22s ease;
    flex-shrink: 0;
}
.kx-theme-btn:hover { background: rgba(0, 204, 0, 0.15); border-color: rgba(0,204,0,0.35); color: #00cc00; }
.kx-navbar .navbar-toggler { border: 1.5px solid rgba(0, 204, 0, 0.45); border-radius: 8px; padding: 5px 10px; }
.kx-navbar .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0, 204, 0, 0.85)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}
[data-bs-theme="light"] .kx-navbar { background: rgba(255, 255, 255, 0.97); border-bottom-color: rgba(0,153,0,0.15); }
[data-bs-theme="light"] .kx-brand { color: #111; }
[data-bs-theme="light"] .kx-nav-link { color: rgba(20,20,20,0.75) !important; }
[data-bs-theme="light"] .kx-nav-link:hover,[data-bs-theme="light"] .kx-nav-link.kx-active { color: #007a0f !important; background: rgba(0,130,17,0.07); }
[data-bs-theme="light"] .kx-theme-btn { background: rgba(0,0,0,0.05); border-color: rgba(0,0,0,0.1); color: #555; }
@media (max-width: 991.98px) {
    .kx-navbar .navbar-collapse {
        background: rgba(8, 14, 8, 0.98);
        border: 1px solid rgba(0, 204, 0, 0.14);
        border-radius: 14px;
        margin-top: 10px;
        padding: 16px;
    }
    [data-bs-theme="light"] .kx-navbar .navbar-collapse { background: rgba(255,255,255,0.99); border-color: rgba(0,153,0,0.15); }
    .kx-nav-link.kx-active::after { display: none; }
    .kx-nav-actions { flex-wrap: wrap; gap: 8px; margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(0,204,0,0.13); }
}
/* ── Notification Bell ── */
.kx-notif-wrap { position: relative; }
.kx-notif-btn {
    background: rgba(255,255,255,0.05);
    border: 1.5px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.75);
    border-radius: 10px;
    width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; position: relative;
    transition: all .2s;
}
.kx-notif-btn:hover { border-color: #00cc00; color: #00cc00; background: rgba(0,204,0,0.08); }
.kx-notif-badge {
    position: absolute; top: -5px; right: -5px;
    background: #ef4444; color: #fff;
    font-size: .6rem; font-weight: 700;
    min-width: 16px; height: 16px; border-radius: 99px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 3px; line-height: 1;
}
.kx-notif-dropdown {
    display: none; position: absolute; right: 0; top: calc(100% + 8px);
    width: 320px; max-height: 380px;
    background: #131d1a; border: 1px solid rgba(0,204,0,0.2);
    border-radius: 14px; box-shadow: 0 12px 40px rgba(0,0,0,0.5);
    z-index: 2000; overflow: hidden;
}
.kx-notif-dropdown.open { display: flex; flex-direction: column; }
.kx-notif-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .7rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.07);
    font-size: .8rem; font-weight: 600; color: #e4e8f0;
}
.kx-notif-markall {
    background: none; border: none; color: #00cc00; font-size: .72rem; cursor: pointer; padding: 0;
}
.kx-notif-list { overflow-y: auto; flex: 1; }
.kx-notif-item {
    display: flex; gap: .7rem; padding: .8rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    cursor: pointer; transition: background .15s;
    text-decoration: none; color: inherit;
}
.kx-notif-item:hover { background: rgba(255,255,255,0.04); }
.kx-notif-item.unread { background: rgba(0,204,0,0.05); }
.kx-notif-icon { font-size: 1.1rem; flex-shrink:0; margin-top:.1rem; }
.kx-notif-body { flex: 1; min-width: 0; }
.kx-notif-title { font-size: .8rem; font-weight: 600; color: #e4e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kx-notif-msg { font-size: .72rem; color: #90a099; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kx-notif-time { font-size: .65rem; color: #90a099; margin-top: .2rem; }
.kx-notif-dot { width: 7px; height: 7px; border-radius: 50%; background: #00cc00; flex-shrink:0; margin-top:.35rem; }
.kx-notif-empty { padding: 2rem 1rem; text-align: center; font-size: .8rem; color: #90a099; line-height: 1.9; }
@media (max-width: 480px) { .kx-notif-dropdown { width: calc(100vw - 30px); right: -40px; } }
</style>

<nav class="kx-navbar navbar navbar-expand-lg" id="kxMainNav">
  <div class="container">
    <a class="kx-brand" href="@auth {{ url('/dashboard') }} @else {{ url('/') }} @endauth">
      <img src="{{ asset('Assests/favicon.png') }}" alt="KayXchange">
      <span>Kay<span class="kx-brand-green">Xchange</span></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#kxNav" aria-controls="kxNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="kxNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
        @auth
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('dashboard')) kx-active @endif" href="{{ url('/dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('deposits*')) kx-active @endif" href="{{ url('/deposits/create') }}">
            <i class="bi bi-plus-circle-fill"></i>Deposit
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('wallet*')) kx-active @endif" href="{{ url('/wallet/send') }}">
            <i class="bi bi-send-fill"></i>Send Money
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('rate')) kx-active @endif" href="{{ url('/rate') }}">
            <i class="bi bi-graph-up-arrow"></i>Rates
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('rate-calculator')) kx-active @endif" href="{{ url('/rate-calculator') }}">
            <i class="bi bi-calculator-fill"></i>Calculator
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('buy*')) kx-active @endif" href="{{ url('/buy') }}">
            <i class="bi bi-arrow-down-circle-fill"></i>Buy
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('convert*')) kx-active @endif" href="{{ url('/convert') }}">
            <i class="bi bi-arrow-left-right"></i>Convert
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('sell*')) kx-active @endif" href="{{ url('/sell') }}">
            <i class="bi bi-arrow-up-circle-fill"></i>Sell
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('settings*')) kx-active @endif" href="{{ url('/settings') }}">
            <i class="bi bi-gear-fill"></i>Settings
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('support/chat')) kx-active @endif" href="{{ route('support.chat') }}">
            <i class="bi bi-headset"></i>Support
          </a>
        </li>
        @php
        try {
            $kaybotVisible = \App\Models\AdminSetting::getSetting('ai_chatbot_enabled','1') == '1' &&
                           (\App\Models\AdminSetting::getSetting('openai_api_key') ?: env('OPENAI_API_KEY') ?: \App\Models\AdminSetting::getSetting('groq_api_key') ?: env('GROQ_API_KEY'));
        } catch (\Exception $e) {
            $kaybotVisible = env('OPENAI_API_KEY') ?: env('GROQ_API_KEY');
        }
        @endphp
        @if($kaybotVisible)
        <li class="nav-item">
          <button type="button" onclick="kaybotToggle()" class="kx-nav-link" style="background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:.4rem;" title="Chat with KayBot AI">
            <i class="bi bi-robot" style="color:#00cc00"></i><span style="color:inherit">KayBot</span>
          </button>
        </li>
        @endif
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('referrals')) kx-active @endif" href="{{ url('/referrals') }}">
            <i class="bi bi-people-fill"></i>Referrals
          </a>
        </li>
        @if(session('admin_id'))
        <li class="nav-item">
          <a class="kx-nav-link" href="{{ route('admin.revert') }}">
            <i class="bi bi-arrow-return-left"></i>Back to Admin
          </a>
        </li>
        @endif
        @else
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('/')) kx-active @endif" href="{{ url('/') }}">
            <i class="bi bi-house-fill"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('rate')) kx-active @endif" href="{{ url('/rate') }}">
            <i class="bi bi-graph-up-arrow"></i>Rates
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('rate-calculator')) kx-active @endif" href="{{ url('/rate-calculator') }}">
            <i class="bi bi-calculator-fill"></i>Calculator
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('blog*')) kx-active @endif" href="{{ url('/blog') }}">
            <i class="bi bi-newspaper"></i>Blog
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('faqs*')) kx-active @endif" href="{{ url('/faqs') }}">
            <i class="bi bi-question-circle-fill"></i>FAQs
          </a>
        </li>
        <li class="nav-item">
          <a class="kx-nav-link @if(request()->is('about*')) kx-active @endif" href="{{ url('/about') }}">
            <i class="bi bi-info-circle-fill"></i>About
          </a>
        </li>
        @endauth
      </ul>

      <div class="d-flex align-items-center kx-nav-actions gap-2">
        @auth
        {{-- Notification Bell --}}
        <div class="kx-notif-wrap" id="kxNotifWrap">
          <button class="kx-notif-btn" id="kxNotifBtn" title="Notifications" aria-label="Notifications">
            <i class="bi bi-bell-fill"></i>
            <span class="kx-notif-badge" id="kxNotifBadge" style="display:none">0</span>
          </button>
          <div class="kx-notif-dropdown" id="kxNotifDropdown">
            <div class="kx-notif-header">
              <span><i class="bi bi-bell me-1"></i>Notifications</span>
              <button class="kx-notif-markall" id="kxMarkAll" onclick="kxMarkAllRead()">Mark all read</button>
            </div>
            <div class="kx-notif-list" id="kxNotifList">
              <div class="kx-notif-empty" id="kxNotifEmpty"><i class="bi bi-bell-slash"></i><br>No new notifications</div>
            </div>
          </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
          @csrf
          <button type="submit" class="kx-btn-logout">
            <i class="bi bi-box-arrow-right"></i>Logout
          </button>
        </form>
        @else
        <a class="kx-btn-login" href="{{ route('login') }}">
          <i class="bi bi-box-arrow-in-right"></i>Login
        </a>
        <a class="kx-btn-register" href="{{ route('register') }}">
          <i class="bi bi-rocket-takeoff-fill"></i>Get Started
        </a>
        @endauth
        <button id="toggle-mode" class="kx-theme-btn" title="Toggle Dark Mode">
          <i class="bi bi-moon-stars-fill" id="mode-icon"></i>
        </button>
      </div>
    </div>
  </div>
</nav>

<script>
(function () {
    var nav = document.getElementById('kxMainNav');
    if (!nav) return;
    window.addEventListener('scroll', function () {
        nav.classList.toggle('kx-scrolled', window.scrollY > 20);
    }, { passive: true });
})();
</script>

@auth
<script>
(function() {
    const btn    = document.getElementById('kxNotifBtn');
    const drop   = document.getElementById('kxNotifDropdown');
    const badge  = document.getElementById('kxNotifBadge');
    const list   = document.getElementById('kxNotifList');
    const empty  = document.getElementById('kxNotifEmpty');
    if (!btn) return;

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60) return diff + 's ago';
        if (diff < 3600) return Math.floor(diff/60) + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return Math.floor(diff/86400) + 'd ago';
    }
    function iconForType(type) {
        const map = {
            trade_approved:'bi-check-circle-fill', trade_rejected:'bi-x-circle-fill',
            trade_pending:'bi-clock-fill', kyc_approved:'bi-shield-check',
            kyc_rejected:'bi-shield-x', deposit:'bi-wallet2', withdrawal:'bi-cash-stack',
        };
        return map[type] || 'bi-bell-fill';
    }
    function colorForType(type) {
        if (type && type.includes('rejected')) return '#ef4444';
        if (type && type.includes('approved')) return '#00cc00';
        return '#60a5fa';
    }

    function renderNotifs(items) {
        if (!items.length) {
            list.innerHTML = '<div class="kx-notif-empty" id="kxNotifEmpty"><i class="bi bi-bell-slash"></i><br>No notifications yet</div>';
            return;
        }
        list.innerHTML = items.map(n => `
            <a href="#" class="kx-notif-item ${n.is_read ? '' : 'unread'}" onclick="kxReadNotif(event,${n.id})">
                <i class="bi ${iconForType(n.type)} kx-notif-icon" style="color:${colorForType(n.type)}"></i>
                <div class="kx-notif-body">
                    <div class="kx-notif-title">${n.title}</div>
                    <div class="kx-notif-msg">${n.message}</div>
                    <div class="kx-notif-time">${timeAgo(n.created_at)}</div>
                </div>
                ${n.is_read ? '' : '<div class="kx-notif-dot"></div>'}
            </a>
        `).join('');
    }

    function fetchNotifs() {
        fetch('/notifications/api', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                const unread = data.unread_count || 0;
                badge.textContent = unread > 99 ? '99+' : unread;
                badge.style.display = unread > 0 ? 'flex' : 'none';
                renderNotifs(data.notifications || []);
            })
            .catch(() => {});
    }

    window.kxReadNotif = function(e, id) {
        e.preventDefault();
        fetch('/notifications/'+id+'/mark-read', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }
        }).then(() => fetchNotifs());
    };

    window.kxMarkAllRead = function() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }
        }).then(() => fetchNotifs());
    };

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const open = drop.classList.toggle('open');
        if (open) fetchNotifs();
    });
    document.addEventListener('click', function(e) {
        if (!document.getElementById('kxNotifWrap')?.contains(e.target)) {
            drop.classList.remove('open');
        }
    });

    fetchNotifs();
    setInterval(fetchNotifs, 30000);
})();
</script>
@endauth