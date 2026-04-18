<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#0d1117;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#e4e8f0;-webkit-font-smoothing:antialiased;">

<!-- Preheader text (hidden preview shown in inbox) -->
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#0d1117;">
  {{ $preheader ?? $subject }} &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
</div>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#0d1117;padding:32px 0;">
<tr><td align="center" style="padding:0 16px;">
<table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;width:100%;">

  <!-- Pre-header brand strip -->
  <tr>
    <td style="padding:0 0 12px;text-align:center;">
      <a href="{{ url('/') }}" style="text-decoration:none;">
        <img src="{{ url('/Assests/Logo.svg') }}" width="140" alt="{{ config('app.name') }}"
             style="border:0;display:inline-block;max-width:140px;height:auto;"
             onerror="this.style.display='none'">
      </a>
    </td>
  </tr>

  <!-- Header gradient card -->
  <tr>
    <td style="background:linear-gradient(135deg,#0a1628 0%,#0d1f1a 100%);border-radius:16px 16px 0 0;padding:28px 40px 22px;text-align:center;border:1px solid rgba(255,255,255,0.08);border-bottom:none;">
      <!-- Fallback text logo for clients that block images -->
      <div style="font-size:22px;font-weight:800;color:#00cc00;letter-spacing:-0.5px;mso-hide:none;">{{ config('app.name') }}</div>
      <div style="color:#7a8599;font-size:13px;margin-top:5px;letter-spacing:0.3px;">Secure Crypto Exchange &#183; Nigeria</div>
      <!-- Divider -->
      <div style="height:1px;background:linear-gradient(90deg,transparent,rgba(0,204,0,0.3),transparent);margin-top:20px;"></div>
    </td>
  </tr>

  <!-- Status badge row (optional) -->
  @if(isset($badgeText))
  <tr>
    <td style="background:#161b27;padding:20px 40px 0;text-align:center;border-left:1px solid rgba(255,255,255,0.08);border-right:1px solid rgba(255,255,255,0.08);">
      <span style="display:inline-block;background:{{ $badgeColor ?? '#00cc00' }};color:{{ ($badgeColor ?? '#00cc00') === '#ef4444' ? '#fff' : '#000' }};font-weight:700;font-size:11px;padding:7px 20px;border-radius:50px;letter-spacing:1px;text-transform:uppercase;">
        {{ $badgeText }}
      </span>
    </td>
  </tr>
  @endif

  <!-- Body -->
  <tr>
    <td style="background:#161b27;padding:32px 40px;border:1px solid rgba(255,255,255,0.08);border-top:none;border-bottom:none;">

      {!! $bodyHtml !!}

      @if(isset($ctaUrl) && isset($ctaText))
      <!-- CTA button -->
      <div style="text-align:center;margin:32px 0 8px;">
        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $ctaUrl }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="21%" stroke="f" fillcolor="#00cc00"><w:anchorlock/><center style="color:#000;font-family:sans-serif;font-size:15px;font-weight:700;">{{ $ctaText }}</center></v:roundrect><![endif]-->
        <a href="{{ $ctaUrl }}"
           style="background:#00cc00;color:#000;display:inline-block;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;font-weight:700;line-height:48px;text-align:center;text-decoration:none;width:220px;border-radius:10px;mso-hide:all;">
          {{ $ctaText }}
        </a>
      </div>
      @endif

      <!-- Support note -->
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top:28px;">
        <tr>
          <td style="background:#1a2035;border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:14px 18px;">
            <p style="color:#7a8599;font-size:13px;line-height:1.6;margin:0;">
              &#x1F4AC;&nbsp; Need help? Contact our support team and we'll be happy to assist you.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#111720;border:1px solid rgba(255,255,255,0.08);border-top:1px solid rgba(255,255,255,0.05);border-radius:0 0 16px 16px;padding:24px 40px;">
      <!-- Footer links -->
      <p style="text-align:center;margin:0 0 12px;">
        <a href="{{ url('/dashboard') }}" style="color:#7a8599;font-size:12px;text-decoration:none;margin:0 10px;">Dashboard</a>
        <span style="color:rgba(255,255,255,0.15);">|</span>
        <a href="{{ url('/support') }}" style="color:#7a8599;font-size:12px;text-decoration:none;margin:0 10px;">Support</a>
        <span style="color:rgba(255,255,255,0.15);">|</span>
        <a href="{{ url('/') }}" style="color:#7a8599;font-size:12px;text-decoration:none;margin:0 10px;">Website</a>
      </p>
      <!-- Copyright -->
      <p style="color:#4a5568;font-size:11px;text-align:center;line-height:1.6;margin:0;">
        &copy; {{ date('Y') }} <strong style="color:#7a8599;">{{ config('app.name') }}</strong>. All rights reserved.<br>
        This is an automated notification — please do not reply to this email.<br>
        <span style="font-size:10px;">You are receiving this email because you have an account with us.</span>
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
