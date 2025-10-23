<!-- ======= Admin KayXchange Header ======= -->
<nav class="navbar navbar-expand-lg bg-dark navbar-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="/admin/dashboard">
        <img width="40px" src="/Assests/favicon.png" alt="KayXchange Admin" class="me-2">
        <span class="text-warning">Admin Panel</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarNav" aria-controls="adminNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbarNav">
      <div class="navbar-nav me-auto">
        <a class="nav-link @if(request()->is('admin/dashboard') || request()->is('admin/enhanced-dashboard')) active @endif" @if(request()->is('admin/dashboard') || request()->is('admin/enhanced-dashboard')) aria-current="page" @endif href="/admin/dashboard">
          <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a class="nav-link @if(request()->is('admin/users*')) active @endif" @if(request()->is('admin/users*')) aria-current="page" @endif href="/admin/users">
          <i class="bi bi-people me-1"></i>Users
        </a>
        <a class="nav-link @if(request()->is('admin/trades*')) active @endif" @if(request()->is('admin/trades*')) aria-current="page" @endif href="/admin/trades">
          <i class="bi bi-arrow-left-right me-1"></i>Buy Trades
        </a>
        <a class="nav-link @if(request()->is('admin/sells*')) active @endif" @if(request()->is('admin/sells*')) aria-current="page" @endif href="/admin/sells">
          <i class="bi bi-arrow-right-left me-1"></i>Sell Trades
        </a>
        <a class="nav-link @if(request()->is('admin/crypto-rates*')) active @endif" @if(request()->is('admin/crypto-rates*')) aria-current="page" @endif href="/admin/crypto-rates">
          <i class="bi bi-currency-bitcoin me-1"></i>Crypto Rates
        </a>
        <a class="nav-link @if(request()->is('admin/rate')) active @endif" @if(request()->is('admin/rate')) aria-current="page" @endif href="/admin/rate">
          <i class="bi bi-graph-up me-1"></i>General Rates
        </a>
      </div>

      <!-- Right side navigation -->
      <div class="d-flex align-items-center">
        @if (session('admin_id'))
        <a class="btn btn-outline-warning me-2" href="{{ route('admin.revert') }}" title="Revert to User">
          <i class="bi bi-arrow-left me-1"></i>Revert to User
        </a>
        @endif
        
        <a class="btn btn-outline-info me-2" href="/" title="View Site" target="_blank">
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