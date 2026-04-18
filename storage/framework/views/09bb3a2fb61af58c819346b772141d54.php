<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-white">
            <h2 class="mb-0">Edit Rate for <?php echo e($rate->coin); ?></h2>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('rates.update-rate', $rate->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('POST'); ?>

                <div class="mb-3">
                    <label for="buy_rate" class="form-label">Buy Rate</label>
                    <input type="number" step="0.01" name="buy_rate" value="<?php echo e(old('buy_rate', $rate->buy_rate)); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="sell_rate" class="form-label">Sell Rate</label>
                    <input type="number" step="0.01" name="sell_rate" value="<?php echo e(old('sell_rate', $rate->sell_rate)); ?>" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">Update Rate</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminnavlayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\rates\edit-rate.blade.php ENDPATH**/ ?>