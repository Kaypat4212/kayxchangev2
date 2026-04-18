<?php echo $__env->make('emails.trade_layout', [
    'subject'    => $subject,
    'bodyHtml'   => $bodyHtml,
    'badgeText'  => $badgeText ?? null,
    'badgeColor' => $badgeColor ?? '#00cc00',
    'ctaUrl'     => $ctaUrl ?? null,
    'ctaText'    => $ctaText ?? null,
], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH C:\xampp\htdocs\kayxchangev2\resources\views\emails\trade_notification.blade.php ENDPATH**/ ?>