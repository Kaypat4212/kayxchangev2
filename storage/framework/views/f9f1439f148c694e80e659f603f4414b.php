

<?php $__env->startPush('styles'); ?>
<style>
:root {
    --blog-green: #00cc00;
    --blog-dark: #070d07;
    --blog-card: #0f1a0f;
    --blog-card2: #162016;
    --blog-border: rgba(0,204,0,0.12);
    --blog-muted: #7a9a7a;
    --blog-text: #e0ece0;
}

.bls-breadcrumb { font-size: .78rem; color: var(--blog-muted); margin-bottom: 1.5rem; }
.bls-breadcrumb a { color: var(--blog-green); text-decoration: none; }
.bls-breadcrumb a:hover { text-decoration: underline; }

.bls-cover {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    border: 1px solid var(--blog-border);
}
.bls-cover-placeholder {
    width: 100%;
    height: 200px;
    border-radius: 16px;
    background: linear-gradient(135deg, #0a1f0a, #0d2a12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: rgba(0,204,0,.2);
    margin-bottom: 1.5rem;
    border: 1px solid var(--blog-border);
}

.bls-cat {
    display: inline-block;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--blog-green);
    background: rgba(0,204,0,.1);
    border-radius: 4px;
    padding: .2rem .6rem;
    margin-bottom: .75rem;
}
.bls-title { font-size: 1.7rem; font-weight: 800; color: #fff; line-height: 1.3; margin-bottom: .75rem; }
.bls-meta { display: flex; gap: 1rem; font-size: .78rem; color: var(--blog-muted); margin-bottom: 1.75rem; flex-wrap: wrap; }
.bls-meta span { display: flex; align-items: center; gap: .3rem; }

/* Article body */
.bls-body {
    font-size: .93rem;
    line-height: 1.8;
    color: var(--blog-text);
}
.bls-body h2, .bls-body h3 { color: #fff; margin-top: 1.75rem; margin-bottom: .6rem; }
.bls-body h2 { font-size: 1.25rem; border-left: 3px solid var(--blog-green); padding-left: .75rem; }
.bls-body h3 { font-size: 1.05rem; color: var(--blog-green); }
.bls-body p  { margin-bottom: 1rem; }
.bls-body ul, .bls-body ol { padding-left: 1.4rem; margin-bottom: 1rem; }
.bls-body li { margin-bottom: .35rem; }
.bls-body blockquote {
    border-left: 3px solid var(--blog-green);
    background: rgba(0,204,0,.05);
    padding: .75rem 1rem;
    border-radius: 0 8px 8px 0;
    color: var(--blog-muted);
    font-style: italic;
    margin: 1rem 0;
}
.bls-body code {
    background: rgba(255,255,255,.07);
    border-radius: 4px;
    padding: .1rem .4rem;
    font-size: .85em;
    color: #a8f0a8;
}
.bls-body strong { color: #fff; }
.bls-body a { color: var(--blog-green); }

/* Divider */
.bls-divider { border-top: 1px solid var(--blog-border); margin: 2rem 0; }

/* Related posts */
.bls-related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; margin-top: 1rem; }
.bls-rel-card {
    background: var(--blog-card);
    border: 1px solid var(--blog-border);
    border-radius: 12px;
    padding: 1rem;
    text-decoration: none !important;
    color: inherit;
    transition: border-color .2s;
}
.bls-rel-card:hover { border-color: rgba(0,204,0,.3); }
.bls-rel-cat { font-size: .68rem; font-weight: 700; color: var(--blog-green); text-transform: uppercase; letter-spacing: .05em; margin-bottom: .4rem; }
.bls-rel-title { font-size: .85rem; font-weight: 600; color: #fff; line-height: 1.4; }
.bls-rel-date { font-size: .72rem; color: var(--blog-muted); margin-top: .5rem; }

@media(max-width:600px) { .bls-title { font-size: 1.3rem; } }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3 pb-5">
<div class="row justify-content-center">
<div class="col-xl-8 col-lg-9">

    <div class="bls-breadcrumb">
        <a href="<?php echo e(url('/blog')); ?>"><i class="bi bi-journal-richtext me-1"></i>Blog</a>
        <span class="mx-1">/</span>
        <span><?php echo e($post->category); ?></span>
    </div>

    <?php if($post->cover_image): ?>
        <img src="<?php echo e(asset('storage/'.$post->cover_image)); ?>" alt="<?php echo e($post->title); ?>" class="bls-cover">
    <?php else: ?>
        <div class="bls-cover-placeholder"><i class="bi bi-journal-text"></i></div>
    <?php endif; ?>

    <span class="bls-cat"><?php echo e($post->category); ?></span>
    <h1 class="bls-title"><?php echo e($post->title); ?></h1>

    <div class="bls-meta">
        <span><i class="bi bi-calendar3"></i><?php echo e($post->published_at?->format('F d, Y')); ?></span>
        <span><i class="bi bi-clock"></i><?php echo e($post->readingTime()); ?> min read</span>
    </div>

    <div class="bls-body">
        <?php echo $post->content; ?>

    </div>

    <div class="bls-divider"></div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="<?php echo e(url('/blog')); ?>" class="btn btn-sm btn-outline-secondary" style="border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.6);border-radius:8px;">
            <i class="bi bi-arrow-left me-1"></i>Back to Blog
        </a>
        <a href="<?php echo e(url('/blog?category='.urlencode($post->category))); ?>"
           class="btn btn-sm" style="background:rgba(0,204,0,.1);border:1px solid rgba(0,204,0,.25);color:#00cc00;border-radius:8px;">
            <i class="bi bi-tag me-1"></i>More in <?php echo e($post->category); ?>

        </a>
    </div>

    <?php if($related->count()): ?>
    <div class="bls-divider"></div>
    <div style="font-size:.82rem;font-weight:700;color:#fff;margin-bottom:.25rem;">Related Articles</div>
    <div class="bls-related-grid">
        <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(url('/blog/'.$rel->slug)); ?>" class="bls-rel-card">
            <div class="bls-rel-cat"><?php echo e($rel->category); ?></div>
            <div class="bls-rel-title"><?php echo e($rel->title); ?></div>
            <div class="bls-rel-date"><i class="bi bi-calendar3 me-1"></i><?php echo e($rel->published_at?->format('M d, Y')); ?></div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

</div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('selllayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\blog\show.blade.php ENDPATH**/ ?>