<!-- ======= Admin KayXchange Header ======= -->
<nav class="navbar navbar-expand-lg bg-dark navbar-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo e(url('/admin/dashboard')); ?>">
        <img width="40px" src="<?php echo e(asset('Assests/favicon.png')); ?>" alt="KayXchange Admin" class="me-2">
        <span class="text-warning">Admin Panel</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarNav" aria-controls="adminNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbarNav">
      <div class="navbar-nav me-auto">
        <a class="nav-link <?php if(request()->is('admin/dashboard') || request()->is('admin/enhanced-dashboard')): ?> active <?php endif; ?>" <?php if(request()->is('admin/dashboard') || request()->is('admin/enhanced-dashboard')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/admin/dashboard')); ?>">
          <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a class="nav-link <?php if(request()->is('admin/users*')): ?> active <?php endif; ?>" <?php if(request()->is('admin/users*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/admin/users')); ?>">
          <i class="bi bi-people me-1"></i>Users
        </a>
        <a class="nav-link <?php if(request()->is('admin/trades*')): ?> active <?php endif; ?>" <?php if(request()->is('admin/trades*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/admin/trades')); ?>">
          <i class="bi bi-arrow-left-right me-1"></i>Buy Trades
        </a>
        <a class="nav-link <?php if(request()->is('admin/sells*')): ?> active <?php endif; ?>" <?php if(request()->is('admin/sells*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/admin/sells')); ?>">
          <i class="bi bi-arrow-right-left me-1"></i>Sell Trades
        </a>
        <a class="nav-link <?php if(request()->is('admin/deposits*')): ?> active <?php endif; ?>" <?php if(request()->is('admin/deposits*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(route('admin.deposits.index')); ?>">
          <i class="bi bi-wallet2 me-1"></i>Deposits
        </a>
        <a class="nav-link <?php if(request()->is('admin/withdrawals*')): ?> active <?php endif; ?>" <?php if(request()->is('admin/withdrawals*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(route('admin.withdrawals')); ?>">
          <i class="bi bi-bank me-1"></i>Withdrawals
        </a>
        <a class="nav-link <?php if(request()->is('admin/crypto-rates*')): ?> active <?php endif; ?>" <?php if(request()->is('admin/crypto-rates*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/admin/crypto-rates')); ?>">
          <i class="bi bi-currency-bitcoin me-1"></i>Crypto Rates
        </a>
        <a class="nav-link <?php if(request()->is('admin/gift-card-rates*')): ?> active <?php endif; ?>" <?php if(request()->is('admin/gift-card-rates*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/admin/gift-card-rates')); ?>">
          <i class="bi bi-gift me-1"></i>Gift Card Rates
        </a>

        <a class="nav-link <?php if(request()->is('admin/blog*')): ?> active <?php endif; ?>" href="<?php echo e(url('/admin/blog')); ?>">
          <i class="bi bi-journal-richtext me-1"></i>Blog
        </a>
        <a class="nav-link <?php if(request()->is('admin/telegram*')): ?> active <?php endif; ?>" href="<?php echo e(url('/admin/telegram')); ?>">
          <i class="bi bi-telegram me-1"></i>Telegram Bot
        </a>
        <a class="nav-link <?php if(request()->is('admin/chat*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.chat')); ?>">
          <i class="bi bi-headset me-1"></i>Support Inbox
        </a>

        
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?php if(request()->is('admin/site-content*') || request()->is('admin/email-settings*') || request()->is('admin/email-templates*') || request()->is('admin/env-editor*')): ?> active <?php endif; ?>"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear-fill me-1"></i>Settings
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li>
              <a class="dropdown-item <?php if(request()->is('admin/site-content*')): ?> active <?php endif; ?>" href="<?php echo e(url('/admin/site-content')); ?>">
                <i class="bi bi-pencil-square me-2"></i>Site Content
              </a>
            </li>
            <li>
              <a class="dropdown-item <?php if(request()->is('admin/email-settings*')): ?> active <?php endif; ?>" href="<?php echo e(url('/admin/email-settings')); ?>">
                <i class="bi bi-envelope-gear-fill me-2"></i>Email / SMTP
              </a>
            </li>
            <li>
              <a class="dropdown-item <?php if(request()->is('admin/email-templates*')): ?> active <?php endif; ?>" href="<?php echo e(route('admin.email-templates')); ?>">
                <i class="bi bi-file-earmark-text me-2"></i>Email Templates
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item <?php if(request()->is('admin/env-editor*')): ?> active <?php endif; ?>" href="<?php echo e(url('/admin/env-editor')); ?>">
                <i class="bi bi-key-fill me-2"></i>API Keys
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Right side navigation -->
      <div class="d-flex align-items-center">
        <?php if(session('admin_id')): ?>
        <a class="btn btn-outline-warning me-2" href="<?php echo e(route('admin.revert')); ?>" title="Revert to User">
          <i class="bi bi-arrow-left me-1"></i>Revert to User
        </a>
        <?php endif; ?>
        
        <button id="adminModeToggleBtn" class="btn btn-outline-secondary me-2" title="Toggle light/dark mode" style="min-width:40px;">
          <i id="adminModeIcon" class="bi bi-moon-stars-fill"></i>
        </button>

        <a class="btn btn-outline-info me-2" href="<?php echo e(url('/')); ?>" title="View Site" target="_blank">
          <i class="bi bi-eye me-1"></i>View Site
        </a>
        
        <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-outline-danger" title="Logout">
              <i class="bi bi-box-arrow-right me-1"></i>Logout
            </button>
        </form>
      </div>
    </div>
  </div>
</nav><!-- End Admin Header --><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/components/admin-navbar.blade.php ENDPATH**/ ?>