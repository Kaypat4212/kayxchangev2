<?php $__env->startSection('content'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
    .rate-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        transition: all 0.3s ease;
    }
    .rate-card:hover:not(.disabled) {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    .btn-rate {
        background: linear-gradient(135deg, #1a3c34, #2e6b5e);
        color: white;
        font-weight: 600;
        padding: 1rem 2rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        width: 100%;
        text-align: center;
        border: none;
    }
    .btn-rate:hover:not(.disabled) {
        background: linear-gradient(135deg, #2e6b5e, #1a3c34);
        transform: translateY(-2px);
    }
    .btn-rate.disabled {
        background: #d1d5db;
        cursor: not-allowed;
        text-decoration: line-through;
        color: #6b7280;
    }
    .toast {
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
    .toast-header {
        background: linear-gradient(135deg, #1a3c34, #2e6b5e);
        color: white;
        border-radius: 8px 8px 0 0;
    }
    .toast-body {
        background: white;
        color: #dc2626;
        font-weight: 500;
    }
    @media (max-width: 768px) {
        .rate-card {
            padding: 1.5rem;
        }
        .btn-rate {
            padding: 0.8rem 1.5rem;
            font-size: 0.9rem;
        }
        .toast {
            width: 90%;
            margin: 0 auto;
        }
    }
</style>

<div class="container my-5">
    <h3 class="text-2xl font-bold text-green-700 mb-4 animate__animated animate__fadeIn">Select Rates Type</h3>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="rate-card animate__animated animate__fadeInLeft">
                <a href="<?php echo e(route('rates.crypto')); ?>" class="btn-rate">Crypto Rates</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="rate-card animate__animated animate__fadeInRight">
                <button class="btn-rate disabled" onclick="showGiftcardToast()">Giftcard Rates</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="giftcardToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Gift card rates and exchange feature will be coming soon.
        </div>
    </div>
</div>

<script>
    function showGiftcardToast() {
        const toastElement = document.getElementById('giftcardToast');
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 5000
        });
        toast.show();
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\rates\index.blade.php ENDPATH**/ ?>