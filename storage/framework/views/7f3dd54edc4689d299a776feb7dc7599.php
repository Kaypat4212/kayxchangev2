 <?php $__env->startComponent('mail::message'); ?>
     # Withdrawal <?php echo e(ucfirst($withdrawal->status)); ?>


     Dear <?php echo e($withdrawal->user->name); ?>,

     Your withdrawal request has been <?php echo e($withdrawal->status); ?>.

     **Details:**
     - **Amount**: ₦<?php echo e(number_format($withdrawal->amount, 2)); ?>

     - **Bank**: <?php echo e(json_decode($withdrawal->bank_account)->bank_name); ?>

     - **Account Number**: <?php echo e(json_decode($withdrawal->bank_account)->account_number); ?>

     - **Account Name**: <?php echo e(json_decode($withdrawal->bank_account)->account_name); ?>

     - **Reference**: <?php echo e($withdrawal->reference); ?>

     - **Status**: <?php echo e(ucfirst($withdrawal->status)); ?>

     - **Submitted**: <?php echo e($withdrawal->created_at); ?>

     <?php if($withdrawal->status === 'approved'): ?>
     - **Processed**: <?php echo e($withdrawal->processed_at); ?>

     <?php endif; ?>

     Thank you for using <?php echo e(config('app.name')); ?>.

     Regards,<br>
     <?php echo e(config('app.name')); ?> Team
     <?php echo $__env->renderComponent(); ?>
     ```<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\emails\withdrawal_notification.blade.php ENDPATH**/ ?>