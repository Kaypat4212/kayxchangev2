<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#0d1117;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#e4e8f0;-webkit-font-smoothing:antialiased;">

<!-- Preheader text -->
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#0d1117;">
  Welcome to {{ config('app.name') }}, {{ $user->name }}! Your account is ready. &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
</div>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#0d1117;padding:32px 0;">
<tr><td align="center" style="padding:0 16px;">
<table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;width:100%;">

  <!-- Logo strip -->
  <tr>
    <td style="padding:0 0 12px;text-align:center;">
      <a href="{{ url('/') }}" style="text-decoration:none;">
        <img src="{{ url('/Assests/Logo.svg') }}" width="140" alt="{{ config('app.name') }}"
             style="border:0;display:inline-block;max-width:140px;height:auto;"
             onerror="this.style.display='none'">
      </a>
    </td>
  </tr>

  <!-- Header -->
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
      <h2 style="color:#ffffff;font-size:22px;font-weight:700;margin:0 0 8px;">Welcome aboard, {{ $user->name }}! &#127881;</h2>
      <p style="color:#7a8599;font-size:14px;margin:0 0 24px;line-height:1.6;">Your {{ config('app.name') }} account has been created successfully. We're excited to have you on board.</p>

      <!-- Account info box -->
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:24px;">
        <tr>
          <td style="background:#1e2535;border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:18px 22px;">
            <div style="font-size:11px;color:#7a8599;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:5px;">Account Email</div>
            <div style="font-size:15px;color:#e4e8f0;font-weight:600;">{{ $user->email }}</div>
          </td>
        </tr>
      </table>

      <p style="color:#e4e8f0;font-size:14px;line-height:1.8;margin:0 0 20px;">
        You can now <strong style="color:#00cc00;">buy and sell crypto</strong> instantly with Naira, manage your wallet, and track all your transactions in one place.
      </p>

      <!-- Feature pills -->
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:28px;">
        <tr>
          <td width="33%" style="text-align:center;padding:12px 6px;">
            <div style="background:#1e2535;border:1px solid rgba(0,204,0,0.15);border-radius:10px;padding:14px 10px;">
              <div style="font-size:20px;margin-bottom:6px;">&#128176;</div>
              <div style="font-size:11px;color:#00cc00;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Buy Crypto</div>
            </div>
          </td>
          <td width="33%" style="text-align:center;padding:12px 6px;">
            <div style="background:#1e2535;border:1px solid rgba(0,204,0,0.15);border-radius:10px;padding:14px 10px;">
              <div style="font-size:20px;margin-bottom:6px;">&#128184;</div>
              <div style="font-size:11px;color:#00cc00;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Sell Crypto</div>
            </div>
          </td>
          <td width="33%" style="text-align:center;padding:12px 6px;">
            <div style="background:#1e2535;border:1px solid rgba(0,204,0,0.15);border-radius:10px;padding:14px 10px;">
              <div style="font-size:20px;margin-bottom:6px;">&#128200;</div>
              <div style="font-size:11px;color:#00cc00;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Track Trades</div>
            </div>
          </td>
        </tr>
      </table>

      <!-- CTA -->
      <div style="text-align:center;margin:0 0 28px;">
        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ url('/onboard') }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="21%" stroke="f" fillcolor="#00cc00"><w:anchorlock/><center style="color:#000;font-family:sans-serif;font-size:15px;font-weight:700;">Complete Setup &rarr;</center></v:roundrect><![endif]-->
        <a href="{{ url('/onboard') }}"
           style="background:#00cc00;color:#000;display:inline-block;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;line-height:48px;text-align:center;text-decoration:none;width:240px;border-radius:10px;mso-hide:all;">
          Complete Setup &rarr;
        </a>
      </div>

      <!-- Onboarding steps -->
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:24px;">
        <tr><td style="background:#1a2035;border:1px solid rgba(0,204,0,0.12);border-radius:12px;padding:20px 22px;">
          <div style="font-size:12px;color:#00cc00;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:14px;">&#x1F4CB; Quick Setup — 3 Easy Steps</div>
          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
              <td style="padding:6px 0;vertical-align:top;width:28px;"><div style="width:22px;height:22px;border-radius:50%;background:rgba(0,204,0,.2);border:1px solid rgba(0,204,0,.4);text-align:center;line-height:22px;font-size:11px;font-weight:700;color:#00cc00;">1</div></td>
              <td style="padding:6px 0;vertical-align:top;"><div style="font-size:13px;color:#e4e8f0;font-weight:600;">Set your 4-digit security PIN</div><div style="font-size:12px;color:#7a8599;margin-top:2px;">Protects your withdrawals and sensitive actions.</div></td>
            </tr>
            <tr>
              <td style="padding:6px 0;vertical-align:top;width:28px;"><div style="width:22px;height:22px;border-radius:50%;background:rgba(0,204,0,.2);border:1px solid rgba(0,204,0,.4);text-align:center;line-height:22px;font-size:11px;font-weight:700;color:#00cc00;">2</div></td>
              <td style="padding:6px 0;vertical-align:top;"><div style="font-size:13px;color:#e4e8f0;font-weight:600;">Add your bank account</div><div style="font-size:12px;color:#7a8599;margin-top:2px;">Required to receive NGN payouts when you sell crypto.</div></td>
            </tr>
            <tr>
              <td style="padding:6px 0;vertical-align:top;width:28px;"><div style="width:22px;height:22px;border-radius:50%;background:rgba(0,204,0,.2);border:1px solid rgba(0,204,0,.4);text-align:center;line-height:22px;font-size:11px;font-weight:700;color:#00cc00;">3</div></td>
              <td style="padding:6px 0;vertical-align:top;"><div style="font-size:13px;color:#e4e8f0;font-weight:600;">Start trading!</div><div style="font-size:12px;color:#7a8599;margin-top:2px;">Buy &amp; sell Bitcoin, USDT, and more — instantly at great rates.</div></td>
            </tr>
          </table>
        </td></tr>
      </table>

      <!-- Support -->
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
          <td style="background:#1a2035;border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:14px 18px;">
            <p style="color:#7a8599;font-size:13px;line-height:1.6;margin:0;">
              &#x1F4AC;&nbsp; Have questions? Our support team is here to help you every step of the way.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#111720;border:1px solid rgba(255,255,255,0.08);border-top:1px solid rgba(255,255,255,0.05);border-radius:0 0 16px 16px;padding:24px 40px;">
      <p style="text-align:center;margin:0 0 12px;">
        <a href="{{ url('/dashboard') }}" style="color:#7a8599;font-size:12px;text-decoration:none;margin:0 10px;">Dashboard</a>
        <span style="color:rgba(255,255,255,0.15);">|</span>
        <a href="{{ url('/support') }}" style="color:#7a8599;font-size:12px;text-decoration:none;margin:0 10px;">Support</a>
        <span style="color:rgba(255,255,255,0.15);">|</span>
        <a href="{{ url('/') }}" style="color:#7a8599;font-size:12px;text-decoration:none;margin:0 10px;">Website</a>
      </p>
      <p style="color:#4a5568;font-size:11px;text-align:center;line-height:1.6;margin:0;">
        &copy; {{ date('Y') }} <strong style="color:#7a8599;">{{ config('app.name') }}</strong>. All rights reserved.<br>
        This is an automated message — please do not reply to this email.
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
