<?php $__env->startSection('content'); ?>
<div class="container text-center my-5">
    <h3 class="text-success">Buy Trade Placed Successfully</h3>
    <p>Thank you for your transaction. Here are the trade details:</p>
    <p><strong>Coin:</strong> <?php echo e($trade->coin); ?></p>
    <p><strong>Amount:</strong> $<?php echo e($trade->amount); ?></p>
    <p><strong>Status:</strong> <?php echo e($trade->status); ?></p>
    <p><strong>Payment Method:</strong> <?php echo e($trade->payment_method); ?></p>
    <a href="<?php echo e(route('transaction.history')); ?>" class="btn btn-success">View Transaction History</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('buylayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\trade\buy_success.blade.php ENDPATH**/ ?>