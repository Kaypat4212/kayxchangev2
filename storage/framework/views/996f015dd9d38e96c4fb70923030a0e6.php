<?php $__env->startSection('content'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
    .card-rate {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    .card-rate:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    .rate-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1a3c34;
    }
    .rate-label {
        color: #6b7280;
        font-size: 0.9rem;
    }
    .btn-back {
        background: linear-gradient(135deg, #1a3c34, #2e6b5e);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background: linear-gradient(135deg, #2e6b5e, #1a3c34);
        transform: translateY(-2px);
    }
    @media (max-width: 768px) {
        .card-rate {
            padding: 1rem;
        }
        .rate-value {
            font-size: 1.1rem;
        }
        .btn-back {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container my-5">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-2xl font-bold text-green-700 animate__animated animate__fadeIn">Cryptocurrency Rates</h3>
        <a href="<?php echo e(route('rates.index')); ?>" class="btn-back animate__animated animate__fadeIn">Back to Rates</a>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger animate__animated animate__fadeIn">
            <?php echo e($error); ?>

        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php $__empty_1 = true; $__currentLoopData = $rates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-4 col-sm-6">
                <div class="card-rate animate__animated animate__fadeInUp" style="animation-delay: <?php echo e($loop->index * 0.2); ?>s">
                    <h4 class="text-lg font-semibold mb-3"><?php echo e($rate->coin); ?></h4>
                    <div class="mb-2">
                        <span class="rate-label">Buy Rate:</span>
                        <span class="rate-value">₦<?php echo e(number_format($rate->buy_rate, 2)); ?></span>
                    </div>
                    <div>
                        <span class="rate-label">Sell Rate:</span>
                        <span class="rate-value">₦<?php echo e(number_format($rate->sell_rate, 2)); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <p class="text-center text-gray-500 py-4 animate__animated animate__fadeIn">No rates available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views/rates/crypto.blade.php ENDPATH**/ ?>