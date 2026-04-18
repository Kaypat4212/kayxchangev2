

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('admin.blog._editor-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="editor-topbar d-flex align-items-center gap-3 mb-4">
    <a href="<?php echo e(route('admin.blog.index')); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i>Posts
    </a>
    <div class="flex-grow-1">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-2 text-success"></i>New Post</h5>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" form="blog-form" name="_action" value="draft"
                class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-floppy me-1"></i>Save Draft
        </button>
        <button type="submit" form="blog-form" name="_action" value="publish"
                class="btn btn-success btn-sm rounded-pill px-3">
            <i class="bi bi-send me-1"></i>Publish
        </button>
    </div>
</div>

<form id="blog-form" method="POST" action="<?php echo e(route('admin.blog.store')); ?>">
    <?php echo csrf_field(); ?>
    <?php echo $__env->make('admin.blog._form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</form>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo $__env->make('admin.blog._editor-scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/admin/blog/create.blade.php ENDPATH**/ ?>