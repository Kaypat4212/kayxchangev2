

<?php $__env->startSection('title', 'About Us — KayXchange'); ?>

<?php $__env->startPush('styles'); ?>
<style>
:root {
    --kx-green:#00cc00; --kx-dark:#0d1117; --kx-card:#161b27;
    --kx-card2:#1e2535; --kx-border:rgba(255,255,255,0.07);
    --kx-text:#e4e8f0; --kx-muted:#7a8599;
}
body { background:var(--kx-dark); color:var(--kx-text); }

/* ── Hero ── */
.about-hero {
    background: linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);
    border-bottom: 1px solid var(--kx-border);
    padding: 3rem 1rem 2.5rem; text-align:center; position:relative; overflow:hidden;
}
.about-hero::before {
    content:''; position:absolute; top:-80px; left:50%; transform:translateX(-50%);
    width:420px; height:420px;
    background:radial-gradient(circle,rgba(0,204,0,.12),transparent 65%);
    pointer-events:none;
}
.about-hero-icon {
    width:70px; height:70px; border-radius:50%;
    background:rgba(0,204,0,.1); border:1px solid rgba(0,204,0,.3);
    display:flex; align-items:center; justify-content:center;
    font-size:2rem; color:var(--kx-green); margin:0 auto 1.2rem;
}
.about-hero h1 { font-size:clamp(1.7rem,4vw,2.4rem); font-weight:800; color:#fff; margin:0 0 .5rem; }
.about-hero p  { color:var(--kx-muted); font-size:.92rem; max-width:520px; margin:0 auto; line-height:1.6; }

/* ── Wrap ── */
.about-wrap { max-width:860px; margin:0 auto; padding:2.5rem 1rem 4rem; }

/* ── Section headings ── */
.ab-section-title {
    font-size:1.25rem; font-weight:800; color:#fff; margin-bottom:.4rem;
    display:flex; align-items:center; gap:.6rem;
}
.ab-section-title i { color:var(--kx-green); font-size:1.1rem; }
.ab-section-sub { color:var(--kx-muted); font-size:.86rem; margin-bottom:1.5rem; }

/* ── Story card ── */
.ab-story {
    background: var(--kx-card);
    border: 1px solid var(--kx-border);
    border-radius: 16px; padding: 2rem; margin-bottom: 2rem;
    position:relative; overflow:hidden;
}
.ab-story::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background: linear-gradient(90deg,#00cc00,#007a0c,transparent);
}
.ab-story p { color:rgba(228,232,240,.75); font-size:.9rem; line-height:1.8; margin:0; }

/* ── Mission / Vision row ── */
.ab-mv-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:2rem; }
@media(max-width:600px){ .ab-mv-grid { grid-template-columns:1fr; } }
.ab-mv-card {
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:14px; padding:1.5rem;
}
.ab-mv-icon {
    width:44px; height:44px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.2rem; margin-bottom:1rem;
}
.ab-mv-icon.mission { background:rgba(0,204,0,.1); color:var(--kx-green); border:1px solid rgba(0,204,0,.25); }
.ab-mv-icon.vision  { background:rgba(96,165,250,.1); color:#60a5fa; border:1px solid rgba(96,165,250,.25); }
.ab-mv-card h3 { font-size:.95rem; font-weight:700; color:#fff; margin:0 0 .6rem; }
.ab-mv-card p  { font-size:.84rem; color:rgba(228,232,240,.6); line-height:1.7; margin:0; }

/* ── Values / Core ── */
.ab-values-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:1rem; margin-bottom:2rem; }
@media(max-width:500px){ .ab-values-grid { grid-template-columns:1fr; } }
.ab-val-card {
    background:var(--kx-card2); border:1px solid var(--kx-border);
    border-radius:14px; padding:1.4rem;
    transition: border-color .2s, transform .2s;
}
.ab-val-card:hover { border-color:rgba(0,204,0,.35); transform:translateY(-2px); }
.ab-val-icon {
    width:42px; height:42px; border-radius:50%;
    background:rgba(0,204,0,.08); border:1px solid rgba(0,204,0,.2);
    display:flex; align-items:center; justify-content:center;
    color:var(--kx-green); font-size:1.1rem; margin-bottom:.85rem;
}
.ab-val-title { font-size:.92rem; font-weight:700; color:#fff; margin-bottom:.35rem; }
.ab-val-desc  { font-size:.82rem; color:var(--kx-muted); line-height:1.65; }

/* ── Stats ── */
.ab-stats {
    display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:2rem;
    background:var(--kx-card); border:1px solid var(--kx-border);
    border-radius:16px; padding:1.75rem 1.5rem;
}
@media(max-width:650px){ .ab-stats { grid-template-columns:repeat(2,1fr); } }
.ab-stat { text-align:center; }
.ab-stat-num {
    font-size:clamp(1.4rem,3vw,2rem); font-weight:900;
    background:linear-gradient(135deg,#00cc00,#80ff80);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    line-height:1.1; margin-bottom:.3rem;
}
.ab-stat-lbl { font-size:.76rem; color:var(--kx-muted); font-weight:600; text-transform:uppercase; letter-spacing:.5px; }

/* ── CTA banner ── */
.ab-cta {
    background: linear-gradient(135deg,rgba(0,204,0,.1),rgba(0,60,0,.15));
    border: 1px solid rgba(0,204,0,.25);
    border-radius: 18px; padding: 2.2rem 2rem; text-align:center;
}
.ab-cta h3 { font-size:1.3rem; font-weight:800; color:#fff; margin-bottom:.5rem; }
.ab-cta p  { color:rgba(228,232,240,.6); font-size:.88rem; margin-bottom:1.4rem; }
.ab-cta-btn {
    display:inline-flex; align-items:center; gap:.5rem;
    background:linear-gradient(135deg,#00cc00,#007a0c);
    color:#fff; font-weight:700; font-size:.9rem;
    border:none; border-radius:12px; padding:.75rem 1.75rem;
    text-decoration:none; transition:all .2s;
    box-shadow:0 4px 18px rgba(0,204,0,.3);
}
.ab-cta-btn:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(0,204,0,.45); color:#fff; }

/* ── Light mode overrides ── */
body.light-mode { background:#f1f5fb; color:#1a1f2e; }
body.light-mode .about-hero { background:linear-gradient(135deg,#dbeafe 0%,#dcfce7 100%); }
body.light-mode .ab-story,
body.light-mode .ab-mv-card { background:#fff; border-color:#e2e8f0; }
body.light-mode .ab-story p,
body.light-mode .ab-mv-card p { color:#475569; }
body.light-mode .ab-mv-card h3 { color:#0f172a; }
body.light-mode .ab-val-card { background:#f8fafc; border-color:#e2e8f0; }
body.light-mode .ab-val-title { color:#0f172a; }
body.light-mode .ab-val-desc  { color:#64748b; }
body.light-mode .ab-stats { background:#fff; border-color:#e2e8f0; }
body.light-mode .ab-stat-lbl { color:#64748b; }
body.light-mode .about-hero p,
body.light-mode .ab-section-sub { color:#64748b; }
body.light-mode .about-hero h1,
body.light-mode .ab-section-title { color:#0f172a; }
body.light-mode .ab-cta { background:linear-gradient(135deg,rgba(0,180,0,.07),rgba(0,100,0,.05)); }
body.light-mode .ab-cta h3 { color:#0f172a; }
body.light-mode .ab-cta p  { color:#64748b; }
</style>
<?php $__env->stopPush(); ?>

<?php
use App\Models\SiteContent;
$sc = SiteContent::allKeyed();
function sc(array $sc, string $key, string $default = ''): string {
    return e($sc[$key] ?? $default);
}
?>

<?php $__env->startSection('content'); ?>


<div class="about-hero">
    <div class="about-hero-icon"><i class="bi bi-building-check"></i></div>
    <h1><?php echo e($sc['about_page_hero_title'] ?? 'About KayXchange'); ?></h1>
    <p><?php echo e($sc['about_page_hero_subtitle'] ?? "Nigeria's most trusted platform for buying &amp; selling digital assets."); ?></p>
</div>

<div class="about-wrap">

    
    <div class="ab-stats mb-4">
        <div class="ab-stat">
            <div class="ab-stat-num"><?php echo e($sc['about_page_stat1_num'] ?? '3,000+'); ?></div>
            <div class="ab-stat-lbl"><?php echo e($sc['about_page_stat1_label'] ?? 'Happy Clients'); ?></div>
        </div>
        <div class="ab-stat">
            <div class="ab-stat-num"><?php echo e($sc['about_page_stat2_num'] ?? '90,000+'); ?></div>
            <div class="ab-stat-lbl"><?php echo e($sc['about_page_stat2_label'] ?? 'Trades Completed'); ?></div>
        </div>
        <div class="ab-stat">
            <div class="ab-stat-num"><?php echo e($sc['about_page_stat3_num'] ?? '24/7'); ?></div>
            <div class="ab-stat-lbl"><?php echo e($sc['about_page_stat3_label'] ?? 'Customer Support'); ?></div>
        </div>
        <div class="ab-stat">
            <div class="ab-stat-num"><?php echo e($sc['about_page_stat4_num'] ?? '15+'); ?></div>
            <div class="ab-stat-lbl"><?php echo e($sc['about_page_stat4_label'] ?? 'Team Members'); ?></div>
        </div>
    </div>

    
    <div class="ab-section-title"><i class="bi bi-journal-text"></i><?php echo e($sc['about_page_story_heading'] ?? 'Our Story'); ?></div>
    <div class="ab-story mb-4">
        <p><?php echo e($sc['about_page_story_text'] ?? ''); ?></p>
    </div>

    
    <div class="ab-mv-grid">
        <div class="ab-mv-card">
            <div class="ab-mv-icon mission"><i class="bi bi-rocket-takeoff-fill"></i></div>
            <h3><?php echo e($sc['about_page_mission_heading'] ?? 'Our Mission'); ?></h3>
            <p><?php echo e($sc['about_page_mission_text'] ?? ''); ?></p>
        </div>
        <div class="ab-mv-card">
            <div class="ab-mv-icon vision"><i class="bi bi-eye-fill"></i></div>
            <h3><?php echo e($sc['about_page_vision_heading'] ?? 'Our Vision'); ?></h3>
            <p><?php echo e($sc['about_page_vision_text'] ?? ''); ?></p>
        </div>
    </div>

    
    <div class="ab-section-title mt-2"><i class="bi bi-gem"></i>Our Core Values</div>
    <p class="ab-section-sub">The principles that guide everything we do</p>
    <div class="ab-values-grid">
        <div class="ab-val-card">
            <div class="ab-val-icon"><i class="bi bi-lightning-charge-fill"></i></div>
            <div class="ab-val-title"><?php echo e($sc['about_page_val1_title'] ?? 'Speed'); ?></div>
            <div class="ab-val-desc"><?php echo e($sc['about_page_val1_desc'] ?? ''); ?></div>
        </div>
        <div class="ab-val-card">
            <div class="ab-val-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <div class="ab-val-title"><?php echo e($sc['about_page_val2_title'] ?? 'Security'); ?></div>
            <div class="ab-val-desc"><?php echo e($sc['about_page_val2_desc'] ?? ''); ?></div>
        </div>
        <div class="ab-val-card">
            <div class="ab-val-icon"><i class="bi bi-eye-fill"></i></div>
            <div class="ab-val-title"><?php echo e($sc['about_page_val3_title'] ?? 'Transparency'); ?></div>
            <div class="ab-val-desc"><?php echo e($sc['about_page_val3_desc'] ?? ''); ?></div>
        </div>
        <div class="ab-val-card">
            <div class="ab-val-icon"><i class="bi bi-headset"></i></div>
            <div class="ab-val-title"><?php echo e($sc['about_page_val4_title'] ?? 'Support'); ?></div>
            <div class="ab-val-desc"><?php echo e($sc['about_page_val4_desc'] ?? ''); ?></div>
        </div>
    </div>

    
    <div class="ab-cta mt-4">
        <h3><?php echo e($sc['about_page_cta_heading'] ?? 'Ready to Start Trading?'); ?></h3>
        <p><?php echo e($sc['about_page_cta_text'] ?? 'Join thousands of Nigerians who trust KayXchange for their crypto needs.'); ?></p>
        <a href="<?php echo e(route('register')); ?>" class="ab-cta-btn">
            <i class="bi bi-person-plus-fill"></i> Create Free Account
        </a>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\about.blade.php ENDPATH**/ ?>