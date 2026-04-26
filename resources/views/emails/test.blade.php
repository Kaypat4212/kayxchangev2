<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Configuration Test</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="520" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:6px;border:1px solid #dde1e7;padding:36px 40px;">
                    <tr>
                        <td style="border-bottom:1px solid #eee;padding-bottom:20px;margin-bottom:24px;">
                            <p style="margin:0;font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#888;">System Notification</p>
                            <h2 style="margin:8px 0 0;font-size:20px;color:#1a1a1a;">SMTP Configuration Test</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:24px;">
                            <p style="margin:0 0 16px;font-size:15px;color:#333;line-height:1.6;">
                                This is an automated system message to confirm that your outgoing mail (SMTP) settings are configured correctly.
                            </p>
                            <table width="100%" cellpadding="8" cellspacing="0" style="background:#f8f9fa;border-radius:4px;font-size:13px;color:#555;">
                                <tr>
                                    <td style="font-weight:600;width:100px;">Sent to:</td>
                                    <td>{{ $toAddress }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">Sent at:</td>
                                    <td>{{ $sentAt }}</td>
                                </tr>
                            </table>
                            <p style="margin:24px 0 0;font-size:14px;color:#555;line-height:1.6;">
                                If you received this message, your email configuration is working correctly.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
