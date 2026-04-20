<!-- ======= Admin KayXchange Header ======= -->
<nav class="navbar navbar-expand-lg bg-dark navbar-dark sticky-top">
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
           href="{{ url('/admin/dashboard') }}">
          <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>

        {{-- Trades dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/trades*') || request()->is('admin/sells*') || request()->is('admin/deposits*') || request()->is('admin/withdrawals*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-arrow-left-right me-1"></i>Trades
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
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
          </ul>
        </div>

        {{-- Users & KYC dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/users*') || request()->is('admin/kyc*') || request()->is('admin/referrals*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
          </ul>
        </div>

        {{-- Rates dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/crypto-rates*') || request()->is('admin/gift-card-rates*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
            </li>
          </ul>
        </div>

        {{-- Support dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/chat*') || request()->is('admin/notifications*') || request()->is('admin/telegram*') || request()->is('admin/blog*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-headset me-1"></i>Support
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li>
              <a class="dropdown-item @if(request()->is('admin/chat*')) active @endif" href="{{ route('admin.chat') }}">
                <i class="bi bi-chat-dots me-2"></i>Support Inbox
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
          </ul>
        </div>

        {{-- Finance / Company Account --}}
        <a class="nav-link @if(request()->is('admin/company-account*')) active @endif"
           href="{{ route('admin.company-account') }}">
          <i class="bi bi-building me-1"></i>Company Account
        </a>

        {{-- Tools dropdown (Terminal, Bug Reports, Feature Requests) --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/terminal*') || request()->is('admin/bug-reports*') || request()->is('admin/feature-requests*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-tools me-1"></i>Tools
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
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
            <li>
              <a class="dropdown-item @if(request()->is('admin/feature-requests*')) active @endif" href="{{ route('admin.feature-requests') }}">
                <i class="bi bi-lightbulb-fill me-2"></i>Feature Requests
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item @if(request()->is('admin/backup*')) active @endif" href="{{ route('admin.backup.index') }}">
                <i class="bi bi-cloud-arrow-up me-2"></i>Backup
              </a>
            </li>
          </ul>
        </div>

        {{-- Settings dropdown --}}
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(request()->is('admin/site-content*') || request()->is('admin/email-settings*') || request()->is('admin/email-templates*') || request()->is('admin/env-editor*') || request()->is('admin/diagnostics*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear-fill me-1"></i>Settings
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li>
              <a class="dropdown-item @if(request()->is('admin/site-content*')) active @endif" href="{{ url('/admin/site-content') }}">
                <i class="bi bi-pencil-square me-2"></i>Site Content
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
              <a class="dropdown-item @if(request()->is('admin/diagnostics*')) active @endif" href="{{ route('admin.diagnostics') }}">
                <i class="bi bi-stethoscope me-2"></i>System Diagnostics
              </a>
            </li>
          </ul>
        </div>

      </div>

      <!-- Right side navigation -->
      <div class="d-flex align-items-center gap-1">
        @if (session('admin_id'))
        <a class="btn btn-outline-warning btn-sm" href="{{ route('admin.revert') }}" title="Revert to User">
          <i class="bi bi-arrow-left me-1"></i>Revert to User
        </a>
        @endif

        <button id="adminModeToggleBtn" class="btn btn-outline-secondary btn-sm" title="Toggle light/dark mode" style="min-width:38px;">
          <i id="adminModeIcon" class="bi bi-moon-stars-fill"></i>
        </button>

        <a class="btn btn-outline-info btn-sm" href="{{ url('/') }}" title="View Site" target="_blank">
          <i class="bi bi-eye me-1"></i>View Site
        </a>

        {{-- Profile dropdown --}}
        <div class="nav-item dropdown">
          <a class="btn btn-outline-secondary btn-sm dropdown-toggle @if(request()->is('admin/profile*')) active @endif"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="My Profile">
            <i class="bi bi-person-circle me-1"></i>
            {{ Str::limit(auth()->user()->name ?? 'Admin', 12) }}
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