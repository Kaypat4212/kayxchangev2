<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deposit Approved - KayXchange</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #28a745;">Your Deposit Has Been Approved! ✅</h2>
        
        <p>Dear {{ $deposit->user->name }},</p>
        
        <p>Great news! Your deposit has been approved and processed successfully.</p>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0;">
            <h3 style="margin-top: 0;">Deposit Details:</h3>
            <p><strong>Amount:</strong> ₦{{ number_format($deposit->amount, 2) }}</p>
            <p><strong>Transaction Reference:</strong> {{ $deposit->transaction_reference ?? 'N/A' }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst($deposit->payment_method) }}</p>
            <p><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">Approved</span></p>
            <p><strong>Date:</strong> {{ $deposit->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        
        <p>Your wallet has been credited with the deposit amount and is now available for trading.</p>
        
        <p>You can now:</p>
        <ul>
            <li>View your updated balance in your dashboard</li>
            <li>Start trading cryptocurrencies</li>
            <li>Make withdrawals when needed</li>
        </ul>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        
        <p>Thank you for choosing KayXchange!</p>
        
        <hr style="border: none; height: 1px; background-color: #eee; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #666;">
            This is an automated email from KayXchange. Please do not reply to this email.
        </p>
    </div>
</body>
</html>