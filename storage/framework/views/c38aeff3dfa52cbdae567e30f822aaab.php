<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <h3>Settings</h3>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <ul class="list-group mt-4">
        <li class="list-group-item">
            <a href="<?php echo e(route('edit.bank')); ?>">Edit Bank Account Information</a>
        </li>
        <li class="list-group-item">
            <a href="<?php echo e(route('change.password.form')); ?>">Change Password</a>
        </li>
    </ul>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\settings.blade.php ENDPATH**/ ?>