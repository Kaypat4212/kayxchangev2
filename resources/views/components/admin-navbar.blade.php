<!-- ======= Admin KayXchange Header ======= -->
<nav class="navbar navbar-expand-lg bg-dark navbar-dark sticky-top" id="adminNavbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ url('/admin/dashboard') }}">
        <img width="40px" src="{{ asset('Assests/favicon.png') }}" alt="KayXchange Admin" class="me-2">
        <span class="text-warning">Admin Panel</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarNav" aria-controls="adminNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbarNav">
      <div class="navbar-nav me-auto flex-wrap">

        {{-- Dashboard --}}
        <a class="nav-link @if(request()->is('admin/dashboard') || request()->is('admin/enhanced-dashboard')) active @endif"
           href="{{ route('admin.dashboard') }}" title="Dashboard" aria-label="Dashboard">
          <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>

        {{-- Trades dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/trades*') || request()->is('admin/sells*') || request()->is('admin/deposits*') || request()->is('admin/withdrawals*') || request()->is('admin/conversions*') || request()->is('admin/crypto-withdrawals*') || request()->is('admin/manual-crypto-deposits*') || request()->is('admin/bill-payments*') || request()->is('admin/company-account*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Trades">
            <i class="bi bi-arrow-left-right me-1"></i>Trades
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item @if(request()->is('admin/transactions*')) active @endif" href="{{ route('admin.transactions.index') }}"><i class="bi bi-clock-history me-2"></i>All Transactions</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/trades*')) active @endif" href="{{ url('/admin/trades') }}">
                <i class="bi bi-arrow-left-right me-2"></i>Buy Trades
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/sells*')) active @endif" href="{{ url('/admin/sells') }}">
                <i class="bi bi-arrow-right-left me-2"></i>Sell Trades
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/deposits*')) active @endif" href="{{ route('admin.deposits.index') }}">
                <i class="bi bi-wallet2 me-2"></i>Deposits
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/withdrawals*')) active @endif" href="{{ route('admin.withdrawals') }}">
                <i class="bi bi-bank me-2"></i>Withdrawals
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/conversions*')) active @endif" href="{{ route('admin.conversions.index') }}">
                <i class="bi bi-arrow-repeat me-2"></i>Swaps
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/crypto-withdrawals*')) active @endif" href="{{ route('admin.crypto-withdrawals.index') }}">
                <i class="bi bi-currency-bitcoin me-2"></i>Crypto Withdrawals
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/manual-crypto-deposits*')) active @endif" href="{{ route('admin.manual-crypto-deposits.index') }}">
                <i class="bi bi-wallet2 me-2"></i>Manual Crypto Deposits
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/bill-payments*')) active @endif" href="{{ route('admin.bill-payments.index') }}">
                <i class="bi bi-receipt me-2"></i>Bill Transactions
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/company-account*')) active @endif" href="{{ route('admin.company-account') }}">
                <i class="bi bi-building me-2"></i>Company Accounts
              </a>
            </li>
          </ul>
        </div>

        {{-- Users & KYC dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/users*') || request()->is('admin/kyc*') || request()->is('admin/referrals*') || request()->is('admin/campaigns*') || request()->is('admin/newsletter*') || request()->is('admin/visitor-logs*') || request()->is('admin/blacklist*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Users">
            <i class="bi bi-people me-1"></i>Users
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li>
              <a class="dropdown-item @if(request()->is('admin/users*')) active @endif" href="{{ url('/admin/users') }}">
                <i class="bi bi-people-fill me-2"></i>All Users
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/kyc*')) active @endif" href="{{ route('admin.kyc') }}">
                <i class="bi bi-person-vcard-fill me-2"></i>KYC Verification
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/referrals') || request()->routeIs('admin.referrals.index')) active @endif" href="{{ route('admin.referrals.index') }}">
                <i class="bi bi-megaphone me-2"></i>Referrals
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/referrals/settings*')) active @endif" href="{{ route('admin.referrals.settings') }}">
                <i class="bi bi-gear me-2"></i>Referral Settings
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/blacklist*')) active @endif" href="{{ route('admin.blacklist.index') }}">
                <i class="bi bi-shield-slash me-2"></i>Blacklist &amp; Suspensions
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/newsletter*')) active @endif" href="{{ route('admin.newsletter.index') }}">
                <i class="bi bi-envelope-paper me-2"></i>Newsletter
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/visitor-logs*')) active @endif" href="{{ route('admin.visitor-logs.index') }}">
                <i class="bi bi-person-lines-fill me-2"></i>Visitor Logs
              </a>
            </li>
          </ul>
        </div>

        {{-- Rates dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/crypto-rates*') || request()->is('admin/gift-card-rates*') || request()->is('admin/rate*') || request()->is('admin/set-rates*') || request()->is('admin/edit-rate*') || request()->is('admin/wallet-rates*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Rates">
            <i class="bi bi-currency-bitcoin me-1"></i>Rates
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li>
              <a class="dropdown-item @if(request()->is('admin/crypto-rates*')) active @endif" href="{{ url('/admin/crypto-rates') }}">
                <i class="bi bi-currency-bitcoin me-2"></i>Crypto Rates
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/gift-card-rates*')) active @endif" href="{{ url('/admin/gift-card-rates') }}">
                <i class="bi bi-gift me-2"></i>Gift Card Rates
              </a>
              <a class="dropdown-item @if(request()->is('admin/gift-card-orders*')) active @endif" href="{{ route('admin.gift-card-orders.index') }}">
                <i class="bi bi-inbox me-2"></i>Gift Card Orders
              </a>
              <a class="dropdown-item @if(request()->is('admin/gift-card-sales*')) active @endif" href="{{ route('admin.gift-card-sales.index') }}">
                <i class="bi bi-card-list me-2"></i>Gift Card Sales
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/set-rates*') || request()->is('admin/edit-rate*') || request()->is('admin/rate*')) active @endif" href="{{ route('admin.crypto-rates.index') }}">
                <i class="bi bi-sliders me-2"></i>Trade Rates
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/wallet-rates*')) active @endif" href="{{ route('admin.wallet-rates.index') }}">
                <i class="bi bi-currency-dollar me-2"></i>USD Wallet Rates
              </a>
            </li>
          </ul>
        </div>

        {{-- Support dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/chat*') || request()->is('admin/notifications*') || request()->is('admin/telegram*') || request()->is('admin/blog*') || request()->is('admin/kaybot*') || request()->is('admin/campaigns*') || request()->is('admin/feature-requests*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Support">
            <i class="bi bi-headset me-1"></i>Support
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item @if(request()->is('admin/support-cases*')) active @endif" href="{{ route('admin.support-cases.index') }}"><i class="bi bi-ticket-detailed me-2"></i>Support Cases</a></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/chat*')) active @endif" href="{{ route('admin.chat') }}">
                <i class="bi bi-chat-dots me-2"></i>Support Inbox
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/kaybot*')) active @endif" href="{{ route('admin.kaybot.tickets') }}">
                <i class="bi bi-ticket-perforated me-2"></i>KayBot Tickets
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/notifications*')) active @endif" href="{{ url('/admin/notifications') }}">
                <i class="bi bi-bell-fill me-2"></i>Notifications
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/telegram') || request()->is('admin/telegram/messages*')) active @endif" href="{{ url('/admin/telegram') }}">
                <i class="bi bi-telegram me-2"></i>Telegram Bot
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/telegram/ai-config*')) active @endif" href="{{ route('admin.telegram.ai-config') }}">
                <i class="bi bi-robot me-2"></i>AI Bot Config
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/blog*')) active @endif" href="{{ url('/admin/blog') }}">
                <i class="bi bi-journal-richtext me-2"></i>Blog
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/forum*')) active @endif" href="{{ route('admin.forum.index') }}">
                <i class="bi bi-chat-square-heart me-2"></i>Community Forum
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/campaigns*')) active @endif" href="{{ route('admin.campaigns.index') }}">
                <i class="bi bi-megaphone-fill me-2"></i>Campaigns
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/feature-requests*')) active @endif" href="{{ route('admin.feature-requests') }}">
                <i class="bi bi-lightbulb me-2"></i>Feature Requests
              </a>
            </li>
          </ul>
        </div>

        {{-- Tools and reporting --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/terminal*') || request()->is('admin/bug-reports*') || request()->is('admin/analytics*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Tools & Analytics">
            <i class="bi bi-tools me-1"></i>Tools
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item @if(request()->is('admin/operations*')) active @endif" href="{{ route('admin.operations') }}"><i class="bi bi-heart-pulse me-2"></i>Operations Health</a></li>
            <li><a class="dropdown-item @if(request()->is('admin/logs*')) active @endif" href="{{ route('admin.logs.index') }}"><i class="bi bi-file-earmark-medical me-2"></i>Laravel Logs &amp; AI</a></li>
            <li><a class="dropdown-item @if(request()->is('admin/audit-log*')) active @endif" href="{{ route('admin.audit-log') }}"><i class="bi bi-shield-check me-2"></i>Admin Audit Log</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/terminal*')) active @endif" href="{{ route('admin.terminal') }}">
                <i class="bi bi-terminal-fill me-2"></i>Terminal
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/bug-reports*')) active @endif" href="{{ route('admin.bug-reports') }}">
                <i class="bi bi-bug-fill me-2"></i>Bug Reports
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/backup*')) active @endif" href="{{ route('admin.backup.index') }}">
                <i class="bi bi-cloud-arrow-up me-2"></i>Backup
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/analytics*')) active @endif" href="{{ route('admin.analytics') }}">
                <i class="bi bi-graph-up-arrow me-2"></i>Analytics
              </a>
            </li>
          </ul>
        </div>

        {{-- Settings dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/settings*') || request()->is('admin/site-content*') || request()->is('admin/email-settings*') || request()->is('admin/email-templates*') || request()->is('admin/env-editor*') || request()->is('admin/diagnostics*') || request()->is('admin/onboarding-content*') || request()->is('admin/features*') || request()->is('admin/crypto-deposit-settings*') || request()->is('install/complete*') || request()->is('install/status*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Settings">
            <i class="bi bi-gear-fill me-1"></i>Settings
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li>
              <a class="dropdown-item @if(request()->is('admin/settings') && !request()->is('admin/settings/*')) active @endif" href="{{ route('admin.settings.index') }}">
                <i class="bi bi-sliders me-2"></i>App Settings
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/settings/ai-usage*')) active @endif" href="{{ route('admin.settings.ai-usage') }}">
                <i class="bi bi-robot me-2"></i>AI Usage &amp; Models
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/site-content*')) active @endif" href="{{ url('/admin/site-content') }}">
                <i class="bi bi-pencil-square me-2"></i>Site Content
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/onboarding-content*')) active @endif" href="{{ route('admin.onboarding-content.index') }}">
                <i class="bi bi-person-badge me-2"></i>User Onboarding
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/email-settings*')) active @endif" href="{{ url('/admin/email-settings') }}">
                <i class="bi bi-envelope-gear-fill me-2"></i>Email / SMTP
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/email-templates*')) active @endif" href="{{ route('admin.email-templates') }}">
                <i class="bi bi-file-earmark-text me-2"></i>Email Templates
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/env-editor*')) active @endif" href="{{ url('/admin/env-editor') }}">
                <i class="bi bi-key-fill me-2"></i>API Keys / .env
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/crypto-deposit-settings*')) active @endif" href="{{ route('admin.crypto-deposit-settings.index') }}">
                <i class="bi bi-currency-bitcoin me-2"></i>Crypto Deposit Settings
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/bill-providers*')) active @endif" href="{{ route('admin.bill-providers.index') }}">
                <i class="bi bi-lightning-charge-fill me-2"></i>Bills & Utilities Providers
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/features*')) active @endif" href="{{ route('admin.features.index') }}">
                <i class="bi bi-toggle-on me-2"></i>Feature Controls
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/diagnostics*')) active @endif" href="{{ route('admin.diagnostics') }}">
                <i class="bi bi-stethoscope me-2"></i>System Diagnostics
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('install/complete*') || request()->is('install/status*')) active @endif" href="{{ route('install.complete') }}">
                <i class="bi bi-check2-circle me-2"></i>Installation Status
              </a>
            </li>
          </ul>
        </div>

      </div>

      <!-- Right side navigation -->
      <div class="d-flex align-items-center gap-1 navbar-actions">
        @php($adminIsActive = filter_var(\App\Models\AdminSetting::getSetting('admin_presence_active', '1'), FILTER_VALIDATE_BOOLEAN))
        <form method="POST" action="{{ route('admin.features.presence') }}" class="m-0">
          @csrf
          <button class="btn btn-sm {{ $adminIsActive ? 'btn-outline-success' : 'btn-outline-secondary' }}" type="submit" title="Click to mark yourself {{ $adminIsActive ? 'away' : 'active' }}">
            <i class="bi bi-circle-fill me-1" style="font-size:.55rem"></i><span class="btn-label">{{ $adminIsActive ? 'Admin active' : 'Admin away' }}</span>
          </button>
        </form>
        @if (session('admin_id'))
        <a class="btn btn-outline-warning btn-sm" href="{{ route('admin.revert') }}" title="Revert to User">
          <i class="bi bi-arrow-left me-1"></i><span class="btn-label">Revert to User</span>
        </a>
        @endif

        <button id="adminModeToggleBtn" class="btn btn-outline-secondary btn-sm" title="Toggle light/dark mode" style="min-width:38px;">
          <i id="adminModeIcon" class="bi bi-moon-stars-fill"></i>
        </button>

        <a class="btn btn-outline-info btn-sm" href="{{ url('/') }}" title="View Site" target="_blank">
          <i class="bi bi-eye me-1"></i><span class="btn-label">View Site</span>
        </a>

        {{-- Profile dropdown --}}
        <div class="nav-item dropdown">
          <a class="btn btn-outline-secondary btn-sm dropdown-toggle @if(request()->is('admin/profile*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="My Profile">
            <i class="bi bi-person-circle me-1"></i>
            <span class="admin-name">{{ Str::limit(auth()->user()->name ?? 'Admin', 12) }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
            <li>
              <span class="dropdown-item-text text-muted small">{{ auth()->user()->email ?? '' }}</span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/profile') && !request()->is('admin/profile/2fa*')) active @endif"
                 href="{{ route('admin.profile.index') }}">
                <i class="bi bi-person-gear me-2"></i>Edit Profile
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/profile*')) active @endif"
                 href="{{ route('admin.profile.index') }}#tab-password">
                <i class="bi bi-key me-2"></i>Change Password
              </a>
            </li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/profile*')) active @endif"
                 href="{{ route('admin.profile.index') }}#tab-2fa">
                @if(auth()->user()->two_factor_enabled ?? false)
                  <i class="bi bi-shield-fill-check me-2 text-success"></i>2FA Enabled
                @else
                  <i class="bi bi-shield-exclamation me-2 text-warning"></i>Enable 2FA
                @endif
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                  <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
              </form>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</nav><!-- End Admin Header -->
