<nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo e(url('/')); ?>">
            <img width="40px" src="<?php echo e(asset('Assests/favicon.png')); ?>" alt="KayXchange" class="me-2">
            KayXchange
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ratesNavbarNav" aria-controls="ratesNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ratesNavbarNav">
            <div class="navbar-nav me-auto">
                <a class="nav-link <?php if(request()->is('/')): ?> active <?php endif; ?>" <?php if(request()->is('/')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/')); ?>">Home</a>
                <a class="nav-link <?php if(request()->is('rate')): ?> active <?php endif; ?>" <?php if(request()->is('rate')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/rate')); ?>">Exchange Rates</a>
                <a class="nav-link <?php if(request()->is('blog*')): ?> active <?php endif; ?>" <?php if(request()->is('blog*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/blog')); ?>">Blog</a>
                <a class="nav-link <?php if(request()->is('faqs*')): ?> active <?php endif; ?>" <?php if(request()->is('faqs*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/faqs')); ?>">FAQs</a>
                <a class="nav-link <?php if(request()->is('about*')): ?> active <?php endif; ?>" <?php if(request()->is('about*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/about')); ?>">About Us</a>
            </div>

            <!-- Right side navigation -->
            <div class="d-flex align-items-center">
                <?php if(auth()->guard()->check()): ?>
                    <a class="nav-link <?php if(request()->is('dashboard')): ?> active <?php endif; ?>" <?php if(request()->is('dashboard')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/dashboard')); ?>">Dashboard</a>
                    <a class="nav-link <?php if(request()->is('buy*')): ?> active <?php endif; ?>" <?php if(request()->is('buy*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/buy')); ?>">Buy Crypto</a>
                    <a class="nav-link <?php if(request()->is('sell*')): ?> active <?php endif; ?>" <?php if(request()->is('sell*')): ?> aria-current="page" <?php endif; ?> href="<?php echo e(url('/sell')); ?>">Sell Crypto</a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline me-3">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-outline-danger" title="Logout">
                          <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-outline-primary me-2" href="<?php echo e(route('login')); ?>">
                      <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                    <a class="btn btn-primary" href="<?php echo e(route('register')); ?>">
                      <i class="bi bi-person-plus me-1"></i>Register
                    </a>
                <?php endif; ?>

                <!-- Dark Mode Toggle Button -->
                <button id="toggle-mode" class="btn btn-outline-secondary ms-2" title="Toggle Dark Mode">
                  <i class="bi bi-moon-stars-fill" id="mode-icon"></i>
                </button>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\layouts\rates.blade.php ENDPATH**/ ?>