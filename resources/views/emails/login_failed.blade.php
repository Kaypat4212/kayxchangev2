<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Failed Login Attempt – KayXchange</title>
</head>
<body style="margin:0;padding:0;background:#0d1117;font-family:'Segoe UI',Arial,sans-serif;color:#e4e8f0;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0d1117;padding:40px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#1a0a0a,#1f0d0a);border-radius:16px 16px 0 0;padding:28px 40px;text-align:center;border:1px solid rgba(239,68,68,0.2);border-bottom:none;">
      <div style="font-size:26px;font-weight:800;color:#ef4444;">KayXchange</div>
      <div style="color:#7a8599;font-size:12px;margin-top:4px;">Security Alert</div>
    </td>
  </tr>

  <!-- Body -->
  <tr>
    <td style="background:#161b27;padding:32px 40px;border:1px solid rgba(255,255,255,0.07);border-top:none;border-bottom:none;">
      <div style="display:inline-block;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:6px 16px;font-size:13px;color:#ef4444;font-weight:600;margin-bottom:20px;">
        ⚠️ Failed Login Attempt
      </div>

      <h2 style="color:#ffffff;font-size:20px;font-weight:700;margin:0 0 8px;">Suspicious login attempt detected</h2>
      <p style="color:#7a8599;font-size:14px;margin:0 0 24px;">
        Someone tried to log into your KayXchange account with the email <strong style="color:#e4e8f0;">{{ $log->email }}</strong> but the attempt failed.
      </p>

      <div style="background:#1e2535;border:1px solid rgba(239,68,68,0.2);border-radius:12px;padding:20px 24px;margin-bottom:24px;">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="font-size:13px;color:#7a8599;padding-bottom:10px;">Date &amp; Time</td>
            <td style="font-size:13px;color:#e4e8f0;font-weight:600;text-align:right;padding-bottom:10px;">{{ $log->created_at->format('d M Y, g:i A') }}</td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#7a8599;padding-bottom:10px;">IP Address</td>
            <td style="font-size:13px;color:#ef4444;font-weight:600;text-align:right;padding-bottom:10px;">{{ $log->ip_address ?? 'Unknown' }}</td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#7a8599;">Browser / Device</td>
            <td style="font-size:13px;color:#e4e8f0;font-weight:600;text-align:right;">{{ Str::limit($log->user_agent ?? 'Unknown', 50) }}</td>
          </tr>
        </table>
      </div>

      <p style="color:#e4e8f0;font-size:14px;line-height:1.7;margin:0 0 20px;">
        If this was you, you can safely ignore this message. If you did <strong style="color:#ef4444;">not</strong> attempt to log in, we recommend changing your password immediately to secure your account.
      </p>

      <div style="text-align:center;margin:24px 0;">
        <a href="{{ url('/settings/change-password') }}"
           style="display:inline-block;background:#ef4444;color:#fff;font-weight:700;font-size:14px;padding:12px 30px;border-radius:10px;text-decoration:none;">
          Secure My Account
        </a>
      </div>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#0d1117;border:1px solid rgba(255,255,255,0.07);border-top:none;border-radius:0 0 16px 16px;padding:18px 40px;text-align:center;">
      <p style="color:#7a8599;font-size:12px;margin:0;">
        © {{ date('Y') }} KayXchange. This is an automated security notification.
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
