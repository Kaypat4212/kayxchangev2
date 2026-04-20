<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Welcome to the KayXchange Newsletter!</title>
</head>
<body style="margin:0;padding:0;background:#0d1117;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#e4e8f0;-webkit-font-smoothing:antialiased;">

<!-- Preheader -->
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#0d1117;">
  You're now subscribed to KayXchange market updates &amp; news! &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
</div>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#0d1117;padding:32px 0;">
<tr><td align="center" style="padding:0 16px;">
<table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;width:100%;">

  <!-- Logo -->
  <tr>
    <td style="padding:0 0 12px;text-align:center;">
      <a href="{{ url('/') }}" style="text-decoration:none;">
        <img src="{{ url('/Assests/Logo.svg') }}" width="140" alt="{{ config('app.name') }}"
             style="border:0;display:inline-block;max-width:140px;height:auto;"
             onerror="this.style.display='none'">
      </a>
    </td>
  </tr>

  <!-- Header banner -->
  <tr>
    <td style="background:linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);border-radius:16px 16px 0 0;padding:28px 40px 22px;text-align:center;border:1px solid rgba(255,255,255,0.08);border-bottom:none;">
      <div style="font-size:22px;font-weight:800;color:#00cc00;letter-spacing:-0.5px;">{{ config('app.name') }}</div>
      <div style="color:#7a8599;font-size:13px;margin-top:5px;letter-spacing:0.3px;">Secure Crypto Exchange &#183; Nigeria</div>
      <div style="height:1px;background:linear-gradient(90deg,transparent,rgba(0,204,0,0.3),transparent);margin-top:20px;"></div>
    </td>
  </tr>

  <!-- Body -->
  <tr>
    <td style="background:#161b27;padding:36px 40px;border:1px solid rgba(255,255,255,0.08);border-top:none;border-bottom:none;">

      <h2 style="color:#ffffff;font-size:22px;font-weight:700;margin:0 0 8px;">
        You're subscribed! &#127881;
      </h2>
      <p style="color:#7a8599;font-size:14px;margin:0 0 24px;line-height:1.6;">
        @if($subscriberName)
          Hey <strong style="color:#e4e8f0;">{{ $subscriberName }}</strong>, welcome to the KayXchange newsletter!
        @else
          Welcome to the KayXchange newsletter!
        @endif
        You'll now receive the latest market news, rate updates, and exclusive tips straight to your inbox.
      </p>

      <!-- What to expect box -->
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:28px;">
        <tr>
          <td style="background:#1e2535;border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:22px 24px;">
            <div style="font-size:12px;color:#00cc00;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;margin-bottom:14px;">What to expect</div>
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
              <tr>
                <td style="padding:6px 0;">
                  <table cellpadding="0" cellspacing="0" role="presentation"><tr>
                    <td style="width:28px;color:#00cc00;font-size:16px;vertical-align:top;">&#128200;</td>
                    <td style="color:#e4e8f0;font-size:13px;line-height:1.5;vertical-align:top;padding-left:6px;">Live crypto rate updates (BTC, ETH, USDT &amp; more)</td>
                  </tr></table>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0;">
                  <table cellpadding="0" cellspacing="0" role="presentation"><tr>
                    <td style="width:28px;color:#00cc00;font-size:16px;vertical-align:top;">&#127758;</td>
                    <td style="color:#e4e8f0;font-size:13px;line-height:1.5;vertical-align:top;padding-left:6px;">Global market news &amp; analysis</td>
                  </tr></table>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0;">
                  <table cellpadding="0" cellspacing="0" role="presentation"><tr>
                    <td style="width:28px;color:#00cc00;font-size:16px;vertical-align:top;">&#127381;</td>
                    <td style="color:#e4e8f0;font-size:13px;line-height:1.5;vertical-align:top;padding-left:6px;">Exclusive promos &amp; trading tips</td>
                  </tr></table>
                </td>
              </tr>
              <tr>
                <td style="padding:6px 0;">
                  <table cellpadding="0" cellspacing="0" role="presentation"><tr>
                    <td style="width:28px;color:#00cc00;font-size:16px;vertical-align:top;">&#128276;</td>
                    <td style="color:#e4e8f0;font-size:13px;line-height:1.5;vertical-align:top;padding-left:6px;">Platform announcements &amp; new features</td>
                  </tr></table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <!-- CTA -->
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:28px;">
        <tr>
          <td align="center">
            <a href="{{ url('/') }}"
               style="display:inline-block;background:#00cc00;color:#081108;font-size:15px;font-weight:700;text-decoration:none;padding:14px 36px;border-radius:8px;letter-spacing:0.3px;">
              Start Trading Now &#8594;
            </a>
          </td>
        </tr>
      </table>

      <p style="color:#7a8599;font-size:13px;line-height:1.7;margin:0;">
        Not interested anymore? You can
        <a href="{{ url('/newsletter/unsubscribe?email=' . urlencode($subscriberEmail)) }}"
           style="color:#00cc00;text-decoration:underline;">unsubscribe at any time</a>.
        We respect your inbox &mdash; no spam, ever.
      </p>

    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#0d1117;border-radius:0 0 16px 16px;padding:22px 40px;text-align:center;border:1px solid rgba(255,255,255,0.08);border-top:none;">
      <p style="color:#4a5568;font-size:12px;margin:0 0 8px;line-height:1.6;">
        &#169; {{ date('Y') }} {{ config('app.name') }} &bull; Secure Crypto Exchange, Nigeria<br>
        You are receiving this because you subscribed at <strong>{{ config('app.url') }}</strong>
      </p>
      <div style="margin-top:12px;">
        <a href="https://www.twitter.com/kay__xchange" style="color:#4a5568;text-decoration:none;margin:0 6px;font-size:12px;">Twitter</a>
        <a href="https://www.instagram.com/kay__xchange" style="color:#4a5568;text-decoration:none;margin:0 6px;font-size:12px;">Instagram</a>
        <a href="https://api.whatsapp.com/send?phone=+2349016740523" style="color:#4a5568;text-decoration:none;margin:0 6px;font-size:12px;">WhatsApp</a>
      </div>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
