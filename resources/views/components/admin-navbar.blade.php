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
              <a class="dropdown-item @if(request()->is('admin/referrals*')) active @endif" href="{{ route('admin.referrals.settings') }}">
                <i class="bi bi-megaphone me-2"></i>Referrals
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
              <a class="dropdown-item @if(request()->is('admin/telegram*')) active @endif" href="{{ url('/admin/telegram') }}">
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

        {{-- Finance --}}
        <a class="nav-link @if(request()->is('admin/company-account*')) active @endif"
           href="{{ route('admin.company-account') }}">
          <i class="bi bi-building me-1"></i>Company Account
        </a>

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
                <i class="bi bi-key-fill me-2"></i>API Keys
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
      <div class="d-flex align-items-center">
        @if (session('admin_id'))
        <a class="btn btn-outline-warning me-2" href="{{ route('admin.revert') }}" title="Revert to User">
          <i class="bi bi-arrow-left me-1"></i>Revert to User
        </a>
        @endif
        
        <button id="adminModeToggleBtn" class="btn btn-outline-secondary me-2" title="Toggle light/dark mode" style="min-width:40px;">
          <i id="adminModeIcon" class="bi bi-moon-stars-fill"></i>
        </button>

        <a class="btn btn-outline-info me-2" href="{{ url('/') }}" title="View Site" target="_blank">
          <i class="bi bi-eye me-1"></i>View Site
        </a>
        
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-danger" title="Logout">
              <i class="bi bi-box-arrow-right me-1"></i>Logout
            </button>
        </form>
      </div>
    </div>
  </div>
</nav><!-- End Admin Header -->