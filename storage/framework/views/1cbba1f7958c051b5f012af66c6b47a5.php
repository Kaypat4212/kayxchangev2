<?php $__env->startSection('title', 'Settings'); ?>

<?php $__env->startPush('styles'); ?>
<style>
:root{
    --kx-green:#00cc00;--kx-green-dim:rgba(0,204,0,0.10);--kx-green-glow:rgba(0,204,0,0.22);
    --kx-dark:#0d1117;--kx-card:#161b27;--kx-card2:#1e2535;
    --kx-border:rgba(255,255,255,0.07);--kx-text:#e4e8f0;--kx-muted:#7a8599;
}
body{ background:var(--kx-dark); color:var(--kx-text); }

/* Hero */
.st-hero{
    background:linear-gradient(135deg,#0a1628 0%,#10211a 100%);
    border-bottom:1px solid var(--kx-border);
    padding:2rem 1rem 1.5rem; text-align:center; margin-bottom:2rem;
    position:relative; overflow:hidden;
}
.st-hero::before{
    content:''; position:absolute; top:-60px; right:-60px;
    width:200px; height:200px;
    background:radial-gradient(circle,var(--kx-green-glow),transparent 70%);
    pointer-events:none;
}
.st-hero-icon{
    width:56px; height:56px; border-radius:50%;
    background:var(--kx-green-dim); border:1px solid rgba(0,204,0,0.22);
    display:flex; align-items:center; justify-content:center;
    font-size:1.5rem; color:var(--kx-green); margin:0 auto .9rem;
}
.st-hero h1{ font-size:1.4rem; font-weight:700; color:#fff; margin:0 0 .3rem; }
.st-hero p{ color:var(--kx-muted); font-size:.84rem; margin:0; }

/* Layout */
.st-wrap{ max-width:760px; margin:0 auto; padding:0 1rem 3rem; }

/* Section label */
.st-section-label{
    font-size:.72rem; font-weight:700; color:var(--kx-muted);
    text-transform:uppercase; letter-spacing:.08em;
    margin-bottom:.75rem; padding-left:.25rem;
}

/* Setting row card */
.st-row{
    display:flex; align-items:center; gap:1rem;
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:14px; padding:1.1rem 1.25rem; margin-bottom:.75rem;
    text-decoration:none; color:var(--kx-text);
    transition:border-color .2s, box-shadow .2s, transform .2s;
}
.st-row:hover{
    border-color:rgba(0,204,0,.25); color:var(--kx-text);
    box-shadow:0 4px 20px rgba(0,0,0,.3); transform:translateY(-1px);
}
.st-row-icon{
    width:46px; height:46px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.25rem; flex-shrink:0;
}
.st-row-icon.green  { background:rgba(0,204,0,.10); color:var(--kx-green); }
.st-row-icon.blue   { background:rgba(56,189,248,.10); color:#38bdf8; }
.st-row-icon.purple { background:rgba(168,85,247,.10); color:#a855f7; }
.st-row-icon.amber  { background:rgba(251,191,36,.10); color:#fbbf24; }
.st-row-icon.red    { background:rgba(239,68,68,.10); color:#ef4444; }
.st-row-body{ flex:1; min-width:0; }
.st-row-title{ font-size:.92rem; font-weight:600; color:#fff; margin-bottom:.15rem; }
.st-row-sub{ font-size:.78rem; color:var(--kx-muted); }
.st-row-arrow{ color:var(--kx-muted); font-size:1rem; flex-shrink:0; transition:transform .2s; }
.st-row:hover .st-row-arrow{ transform:translateX(3px); color:var(--kx-green); }

/* Profile info card */
.st-profile{
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:16px; padding:1.5rem; margin-bottom:1.75rem;
    display:flex; align-items:center; gap:1.25rem;
}
.st-avatar{
    width:60px; height:60px; border-radius:50%;
    background:var(--kx-green-dim); border:2px solid rgba(0,204,0,.2);
    display:flex; align-items:center; justify-content:center;
    font-size:1.5rem; font-weight:700; color:var(--kx-green); flex-shrink:0;
    text-transform:uppercase;
}
.st-profile-name{ font-size:1.05rem; font-weight:700; color:#fff; margin-bottom:.15rem; }
.st-profile-email{ font-size:.82rem; color:var(--kx-muted); }
.st-badge{
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.2rem .65rem; border-radius:99px; font-size:.72rem; font-weight:600;
    margin-top:.4rem;
}
.st-badge.verified { background:rgba(0,204,0,.1); color:var(--kx-green); border:1px solid rgba(0,204,0,.2); }
.st-badge.unverified { background:rgba(245,158,11,.1); color:#fbbf24; border:1px solid rgba(245,158,11,.2); }

/* Danger zone */
.st-danger-zone{
    background:rgba(239,68,68,.04); border:1px solid rgba(239,68,68,.12);
    border-radius:14px; padding:1rem 1.25rem; margin-bottom:.75rem;
    display:flex; align-items:center; gap:1rem;
    text-decoration:none; color:var(--kx-text);
    transition:background .2s, border-color .2s;
}
.st-danger-zone:hover{ background:rgba(239,68,68,.08); border-color:rgba(239,68,68,.25); color:var(--kx-text); }

/* Divider */
.st-divider{ height:1px; background:var(--kx-border); margin:1.5rem 0; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="st-hero">
    <div class="st-hero-icon"><i class="bi bi-gear-fill"></i></div>
    <h1>Account Settings</h1>
    <p>Manage your profile, security &amp; notification preferences</p>
</div>

<div class="st-wrap">

    <?php if(session('success')): ?>
    <div class="alert d-flex align-items-center gap-2 mb-3"
         style="background:rgba(0,204,0,.08);border:1px solid rgba(0,204,0,.2);color:var(--kx-green);border-radius:12px;font-size:.85rem;">
        <i class="bi bi-check-circle-fill"></i> <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    
    <div class="st-profile">
        <div class="st-avatar"><?php echo e(substr(auth()->user()->name ?? 'U', 0, 1)); ?></div>
        <div>
            <div class="st-profile-name"><?php echo e(auth()->user()->name); ?></div>
            <div class="st-profile-email"><?php echo e(auth()->user()->email); ?></div>
            <?php if(auth()->user()->kyc_verified): ?>
                <span class="st-badge verified"><i class="bi bi-patch-check-fill"></i> KYC Verified</span>
            <?php else: ?>
                <span class="st-badge unverified"><i class="bi bi-clock-history"></i> KYC Pending</span>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="st-section-label">Account</div>

    <a href="<?php echo e(route('edit-bank')); ?>" class="st-row">
        <div class="st-row-icon green"><i class="bi bi-bank2"></i></div>
        <div class="st-row-body">
            <div class="st-row-title">Bank Account</div>
            <div class="st-row-sub">
                <?php if(auth()->user()->account_number): ?>
                    <?php echo e(auth()->user()->account_name); ?> &mdash; <?php echo e(auth()->user()->account_number); ?>

                <?php else: ?>
                    Add or update your withdrawal bank account
                <?php endif; ?>
            </div>
        </div>
        <i class="bi bi-chevron-right st-row-arrow"></i>
    </a>

    <a href="<?php echo e(route('profile.edit')); ?>" class="st-row">
        <div class="st-row-icon blue"><i class="bi bi-person-fill"></i></div>
        <div class="st-row-body">
            <div class="st-row-title">Profile Information</div>
            <div class="st-row-sub">Update your name and email address</div>
        </div>
        <i class="bi bi-chevron-right st-row-arrow"></i>
    </a>

    <?php if(!auth()->user()->kyc_verified): ?>
    <a href="<?php echo e(route('kyc.form')); ?>" class="st-row">
        <div class="st-row-icon amber"><i class="bi bi-shield-check"></i></div>
        <div class="st-row-body">
            <div class="st-row-title">Complete KYC Verification</div>
            <div class="st-row-sub">Submit your verification details to unlock full account features</div>
        </div>
        <i class="bi bi-chevron-right st-row-arrow"></i>
    </a>
    <?php endif; ?>

    
    <div class="st-section-label mt-3">Security</div>

    <a href="<?php echo e(route('change.password.form')); ?>" class="st-row">
        <div class="st-row-icon purple"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="st-row-body">
            <div class="st-row-title">Change Password</div>
            <div class="st-row-sub">Update your account password</div>
        </div>
        <i class="bi bi-chevron-right st-row-arrow"></i>
    </a>

    <a href="<?php echo e(route('pin.change')); ?>" class="st-row">
        <div class="st-row-icon" style="background:rgba(0,204,0,.1);color:#00cc00;"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <div class="st-row-body">
            <div class="st-row-title"><?php echo e(auth()->user()->transaction_pin ? 'Change PIN' : 'Set Up PIN'); ?></div>
            <div class="st-row-sub"><?php echo e(auth()->user()->transaction_pin ? '4-digit transaction security PIN' : 'Protect withdrawals with a PIN'); ?></div>
        </div>
        <i class="bi bi-chevron-right st-row-arrow"></i>
    </a>

    
    <div class="st-section-label mt-3">Notifications</div>

    <a href="<?php echo e(route('settings.telegram')); ?>" class="st-row">
        <div class="st-row-icon blue" style="background:rgba(0,136,204,.12);color:#29b6f6;">
            <i class="bi bi-telegram"></i>
        </div>
        <div class="st-row-body">
            <div class="st-row-title">Telegram Notifications</div>
            <div class="st-row-sub">
                <?php if(auth()->user()->telegram_verified): ?>
                    Connected as <span style="color:#29b6f6;"><?php echo e('@' . auth()->user()->telegram_username); ?></span>
                <?php else: ?>
                    Connect Telegram to receive trade alerts
                <?php endif; ?>
            </div>
        </div>
        <i class="bi bi-chevron-right st-row-arrow"></i>
    </a>

    <a href="<?php echo e(route('support.chat')); ?>" class="st-row">
        <div class="st-row-icon green"><i class="bi bi-headset"></i></div>
        <div class="st-row-body">
            <div class="st-row-title">Support Chat</div>
            <div class="st-row-sub">Talk to admin support about trades, KYC, deposits and withdrawals</div>
        </div>
        <i class="bi bi-chevron-right st-row-arrow"></i>
    </a>

    
    <div class="st-divider"></div>
    <div class="st-section-label">Danger Zone</div>

    <a href="<?php echo e(route('profile.edit')); ?>#delete-account" class="st-danger-zone">
        <div class="st-row-icon red" style="background:rgba(239,68,68,.1);color:#ef4444;width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
            <i class="bi bi-trash3-fill"></i>
        </div>
        <div class="st-row-body">
            <div class="st-row-title" style="color:#ef4444;">Delete Account</div>
            <div class="st-row-sub">Permanently remove your account and all data</div>
        </div>
        <i class="bi bi-chevron-right" style="color:#ef4444;flex-shrink:0;"></i>
    </a>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\settings\index.blade.php ENDPATH**/ ?>