<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Trade Escalation</title>
    <style>
        body { margin: 0; padding: 0; background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { background: #1e293b; padding: 28px 32px; text-align: center; }
        .header h1 { color: #f8fafc; font-size: 20px; margin: 0; }
        .header .badge { display: inline-block; margin-top: 8px; background: #f59e0b; color: #1e293b; font-size: 11px; font-weight: 700; letter-spacing: 1px; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; }
        .body { padding: 28px 32px; }
        .body p { color: #475569; font-size: 14px; margin: 0 0 20px; line-height: 1.6; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .info-table td { padding: 10px 12px; font-size: 14px; border-bottom: 1px solid #e2e8f0; }
        .info-table td:first-child { color: #64748b; width: 40%; font-weight: 600; }
        .info-table td:last-child { color: #1e293b; }
        .cta { text-align: center; margin: 28px 0 12px; }
        .cta a { display: inline-block; background: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 7px; font-size: 15px; font-weight: 600; }
        .footer { background: #f8fafc; padding: 18px 32px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>⏰ Pending Trade Escalation</h1>
        <span class="badge">Action Required</span>
    </div>
    <div class="body">
        <p>
            A <strong>{{ $tradeType }}</strong> trade has been pending for
            <strong>{{ $data['pending_minutes'] ?? 'N/A' }} minute(s)</strong>
            and has been automatically escalated for review.
        </p>

        <table class="info-table">
            <tr>
                <td>Trade Type</td>
                <td>{{ $tradeType }}</td>
            </tr>
            <tr>
                <td>Reference</td>
                <td>{{ $data['reference'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>User</td>
                <td>{{ $data['user_name'] ?? 'N/A' }}</td>
            </tr>
            @if(!empty($data['coin']))
            <tr>
                <td>Coin</td>
                <td>{{ $data['coin'] }}</td>
            </tr>
            @endif
            <tr>
                <td>Amount (NGN)</td>
                <td>₦{{ $data['naira_amount'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Pending For</td>
                <td>{{ $data['pending_minutes'] ?? 'N/A' }} minute(s)</td>
            </tr>
            <tr>
                <td>Escalated At</td>
                <td>{{ now()->format('D, d M Y H:i:s') }} UTC</td>
            </tr>
        </table>

        <div class="cta">
            <a href="{{ $tradeUrl }}">🔍 Review Trade Now</a>
        </div>
    </div>
    <div class="footer">
        KayXchange Admin Alert &mdash; {{ config('app.name') }}<br>
        This email was generated automatically. Do not reply.
    </div>
</div>
</body>
</html>
