<!-- resources/views/waiting.blade.php -->


<?php $__env->startSection('content'); ?>
    <div class="container text-center my-5">
        <h3 class="text-success">Processing Your Trade...</h3>
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Please wait while we process your transaction.</p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\waiting.blade.php ENDPATH**/ ?>