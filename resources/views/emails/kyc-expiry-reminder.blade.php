<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KYC Document Expiry Reminder</title>
<style>
  body { margin:0; padding:0; background:#0d1117; font-family:'Segoe UI',Arial,sans-serif; }
  .wrap { max-width:560px; margin:32px auto; background:#161b27; border:1px solid rgba(255,255,255,.07); border-radius:16px; overflow:hidden; }
  .hdr { background:linear-gradient(135deg,#1e2535,#0d1117); padding:32px 28px; text-align:center; border-bottom:1px solid rgba(0,204,0,.15); }
  .logo { color:#00cc00; font-size:1.5rem; font-weight:900; letter-spacing:.04em; }
  .body { padding:28px; color:#c9d1d9; font-size:.9rem; line-height:1.65; }
  .alert-box { background:rgba(251,191,36,.07); border:1px solid rgba(251,191,36,.25); border-radius:12px; padding:16px 20px; margin:20px 0; }
  .alert-box .days { font-size:2rem; font-weight:800; color:#fbbf24; }
  .alert-box p { margin:4px 0 0; color:#e4e8f0; font-size:.88rem; }
  .detail-row { display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid rgba(255,255,255,.05); font-size:.85rem; }
  .detail-row:last-child { border:none; }
  .detail-label { color:#7a8599; }
  .detail-val { color:#e4e8f0; font-weight:600; }
  .cta-btn { display:inline-block; margin:20px 0 0; background:#00cc00; color:#000; font-weight:700; font-size:.9rem; text-decoration:none; padding:13px 28px; border-radius:10px; }
  .footer { padding:16px 28px 24px; text-align:center; color:#7a8599; font-size:.75rem; border-top:1px solid rgba(255,255,255,.05); }
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <div class="logo">KayXchange</div>
    <p style="color:#7a8599;margin:6px 0 0;font-size:.82rem;">Document Expiry Reminder</p>
  </div>
  <div class="body">
    <p>Hi <strong style="color:#e4e8f0">{{ $user->name }}</strong>,</p>
    <p>Your KYC identity document on file is expiring soon. Please update your verification before the expiry date to avoid any disruption to your trading account.</p>

    <div class="alert-box">
      <div class="days">{{ $daysLeft }} day{{ $daysLeft != 1 ? 's' : '' }} left</div>
      <p>until your document expires</p>
    </div>

    <div style="background:#0d1117;border-radius:12px;padding:16px 20px;margin-bottom:20px;">
      <div class="detail-row"><span class="detail-label">Document Type</span><span class="detail-val">{{ ucwords(str_replace('_',' ', $kyc->document_type)) }}</span></div>
      <div class="detail-row"><span class="detail-label">Expiry Date</span><span class="detail-val">{{ $expiryDate }}</span></div>
    </div>

    <p>To renew your KYC, visit the KYC verification page and upload a new valid document before the expiry date.</p>

    <div style="text-align:center;">
      <a href="{{ url('/kyc') }}" class="cta-btn">Update KYC Document →</a>
    </div>
  </div>
  <div class="footer">
    © {{ date('Y') }} KayXchange &nbsp;|&nbsp; tradewithkay.com<br>
    This is an automated reminder. Do not reply to this email.
  </div>
</div>
</body>
</html>
