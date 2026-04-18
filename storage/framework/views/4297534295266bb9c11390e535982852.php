<?php $__env->startSection('title', 'Sell Crypto'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-lg mx-auto bg-white shadow-lg rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Sell Your Crypto</h2>
        <p class="text-center text-gray-600 mb-6">Begin the process to sell your cryptocurrency securely and easily.</p>
        
        <?php if(session('error')): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="flex justify-center">
            <a href="<?php echo e(route('sell.step1')); ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-300">
                Start Selling
            </a>
        </div>

        <div class="mt-6 text-center">
            <a href="<?php echo e(route('transaction.history')); ?>" class="text-blue-500 hover:underline">View Transaction History</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\sell.blade.php ENDPATH**/ ?>