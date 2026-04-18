<footer class="kx-footer">
<div class="kx-footer-inner container-xl">
    <div class="kx-footer-grid">

        <!-- Brand -->
        <div class="kx-footer-brand-col">
            <a href="<?php echo e(url('/')); ?>" class="kx-footer-brand">
                <img src="<?php echo e(asset('Assests/favicon.png')); ?>" alt="KayXchange">
                <span>Kay<span class="kx-green">Xchange</span></span>
            </a>
            <p class="kx-footer-tagline">Nigeria's trusted crypto exchange platform. Fast, secure, and reliable.</p>
            <div class="kx-footer-socials">
                <a href="https://t.me/TradewithkayxchangeBOT" target="_blank" title="Telegram"><i class="bi bi-telegram"></i></a>
                <a href="https://wa.me/2349016740523" target="_blank" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                <a href="mailto:support@kayxchange.net" title="Email"><i class="bi bi-envelope-fill"></i></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="kx-footer-col">
            <h6 class="kx-footer-heading">Quick Links</h6>
            <ul class="kx-footer-links">
                <?php if(auth()->guard()->check()): ?>
                <li><a href="<?php echo e(url('/dashboard')); ?>"><i class="bi bi-grid-1x2-fill me-2"></i>Dashboard</a></li>
                <li><a href="<?php echo e(url('/rate')); ?>"><i class="bi bi-graph-up-arrow me-2"></i>Rates</a></li>
                <li><a href="<?php echo e(Auth::user()->kyc_verified ? url('/buy') : url('/kyc')); ?>"><i class="bi bi-arrow-down-circle-fill me-2"></i>Buy Crypto</a></li>
                <li><a href="<?php echo e(url('/sell')); ?>"><i class="bi bi-arrow-up-circle-fill me-2"></i>Sell Crypto</a></li>
                <li><a href="<?php echo e(url('/transactions/history')); ?>"><i class="bi bi-clock-history me-2"></i>Transactions</a></li>
                <?php else: ?>
                <li><a href="<?php echo e(url('/')); ?>"><i class="bi bi-house-fill me-2"></i>Home</a></li>
                <li><a href="<?php echo e(url('/rate')); ?>"><i class="bi bi-graph-up-arrow me-2"></i>Rates</a></li>
                <li><a href="<?php echo e(route('login')); ?>"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                <li><a href="<?php echo e(route('register')); ?>"><i class="bi bi-person-plus-fill me-2"></i>Register</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Account -->
        <div class="kx-footer-col">
            <h6 class="kx-footer-heading">Account</h6>
            <ul class="kx-footer-links">
                <?php if(auth()->guard()->check()): ?>
                <li><a href="<?php echo e(url('/settings')); ?>"><i class="bi bi-gear-fill me-2"></i>Settings</a></li>
                <li><a href="<?php echo e(url('/referrals')); ?>"><i class="bi bi-people-fill me-2"></i>Referrals</a></li>
                <li><a href="<?php echo e(url('/deposits/create')); ?>"><i class="bi bi-wallet-fill me-2"></i>Deposit</a></li>
                <li><a href="<?php echo e(url('/withdraw')); ?>"><i class="bi bi-cash-stack me-2"></i>Withdraw</a></li>
                <?php else: ?>
                <li><a href="<?php echo e(route('login')); ?>"><i class="bi bi-person-fill me-2"></i>My Account</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Contact -->
        <div class="kx-footer-col">
            <h6 class="kx-footer-heading">Contact Us</h6>
            <ul class="kx-footer-links">
                <li>
                    <a href="mailto:support@kayxchange.net">
                        <i class="bi bi-envelope-fill me-2"></i>support@kayxchange.net
                    </a>
                </li>
                <li>
                    <a href="tel:+2349016740523">
                        <i class="bi bi-telephone-fill me-2"></i>+234 901 674 0523
                    </a>
                </li>
                <li>
                    <a href="https://t.me/TradewithkayxchangeBOT" target="_blank">
                        <i class="bi bi-telegram me-2"></i>Telegram Support
                    </a>
                </li>
            </ul>
        </div>

    </div>

    <div class="kx-footer-bottom">
        <span>© <?php echo e(date('Y')); ?> KayXchange. All rights reserved.</span>
        <span class="kx-footer-badge"><i class="bi bi-shield-check-fill me-1"></i>Secured &amp; Trusted</span>
    </div>
</div>
</footer>

<style>
.kx-footer {
    background: linear-gradient(180deg, #080e08 0%, #060c06 100%);
    border-top: 1px solid rgba(0, 204, 0, 0.12);
    padding: 48px 0 24px;
    margin-top: 40px;
    font-family: 'Poppins', sans-serif;
}
body.light-mode .kx-footer { background: #0f1a0f; border-top-color: rgba(0,153,0,0.18); }
.kx-footer-inner { max-width: 1280px; margin: 0 auto; padding: 0 20px; }
.kx-footer-grid {
    display: grid;
    grid-template-columns: 1.8fr 1fr 1fr 1.2fr;
    gap: 40px;
    margin-bottom: 36px;
}
@media (max-width: 991.98px) { .kx-footer-grid { grid-template-columns: 1fr 1fr; gap: 28px; } }
@media (max-width: 575.98px)  { .kx-footer-grid { grid-template-columns: 1fr; gap: 24px; } }

.kx-footer-brand { display: inline-flex; align-items: center; gap: 10px; text-decoration: none !important; color: #fff; font-size: 1.25rem; font-weight: 700; letter-spacing: -0.3px; margin-bottom: 12px; }
.kx-footer-brand img { width: 38px; height: 38px; border-radius: 8px; box-shadow: 0 0 14px rgba(0,204,0,0.4); }
.kx-footer-brand .kx-green { color: #00cc00; }
.kx-footer-tagline { color: rgba(255,255,255,0.42); font-size: 0.8rem; line-height: 1.6; margin: 10px 0 16px; max-width: 240px; }
.kx-footer-socials { display: flex; gap: 10px; }
.kx-footer-socials a { width: 34px; height: 34px; border-radius: 8px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.5); font-size: 0.95rem; text-decoration: none !important; transition: all 0.22s ease; }
.kx-footer-socials a:hover { background: rgba(0,204,0,0.15); border-color: rgba(0,204,0,0.4); color: #00cc00; }
.kx-footer-heading { color: #e8f5e8; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
.kx-footer-links { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
.kx-footer-links li a { color: rgba(255,255,255,0.48); font-size: 0.82rem; text-decoration: none !important; transition: color 0.2s ease; display: flex; align-items: center; }
.kx-footer-links li a:hover { color: #00cc00; }
.kx-footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.07);
    padding-top: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.3);
}
.kx-footer-badge { display: inline-flex; align-items: center; gap: 4px; background: rgba(0,204,0,0.1); border: 1px solid rgba(0,204,0,0.2); color: rgba(0,204,0,0.75); padding: 4px 12px; border-radius: 20px; font-size: 0.73rem; font-weight: 500; }
</style>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/components/kx-footer.blade.php ENDPATH**/ ?>