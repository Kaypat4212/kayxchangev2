<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f3f6f4;color:#142019;font-family:Arial,sans-serif">
<div style="max-width:620px;margin:32px auto;padding:0 16px">
    <div style="background:#0b1610;color:#f2fff5;padding:22px 26px;border-radius:18px 18px 0 0"><strong style="font-size:20px">Kay<span style="color:#32d875">Xchange</span></strong></div>
    <div style="background:#fff;padding:28px;border:1px solid #e2e9e4;border-radius:0 0 18px 18px">
        <p style="margin-top:0">Hello,</p>
        <h1 style="font-size:22px;margin:0 0 12px">Your gift card is ready 🎁</h1>
        <p>Your {{ $order->card_name }} ({{ $order->country }}) purchase has been fulfilled.</p>
        <div style="background:#f3f8f4;border:1px solid #dce9df;border-radius:12px;padding:16px;margin:20px 0">
            <div><strong>Face value:</strong> {{ $order->currency }} {{ number_format((float) $order->face_value, 2) }}</div>
            <div style="margin-top:7px"><strong>Reference:</strong> {{ $order->reference }}</div>
            @if($includeCode)
                <div style="margin-top:16px"><strong>Gift-card code / delivery details</strong></div>
                <div style="margin-top:7px;padding:13px;background:#fff;border:1px dashed #a8b9ad;border-radius:8px;white-space:pre-wrap;overflow-wrap:anywhere;font-family:monospace">{{ $order->delivery_code }}</div>
                <p style="font-size:13px;color:#65736a;margin-bottom:0">Keep this code private and redeem it only with the official card provider.</p>
            @else
                <p style="margin:14px 0 0">The card was sent to the recipient email supplied with your order. Your purchase record is available in your account.</p>
            @endif
        </div>
        <a href="{{ url('/gift-cards') }}" style="display:inline-block;background:#16a34a;color:#fff;text-decoration:none;font-weight:bold;padding:12px 18px;border-radius:9px">View purchase history</a>
        <p style="font-size:12px;color:#718077;margin:24px 0 0">If the recipient cannot redeem the card, contact KayXchange support and include the reference above.</p>
    </div>
</div>
</body>
</html>
