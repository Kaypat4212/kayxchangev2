<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daily Trade Summary</title>
<style>
body{margin:0;padding:0;background:#f4f6f8;font-family:'Segoe UI',Arial,sans-serif;}
.wrap{max-width:580px;margin:30px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);}
.header{background:linear-gradient(135deg,#043a0c 0%,#0a2010 100%);padding:32px 32px 24px;text-align:center;}
.header h1{color:#fff;font-size:1.5rem;font-weight:800;margin:0 0 4px;}
.header p{color:rgba(255,255,255,.6);font-size:.85rem;margin:0;}
.body{padding:28px 32px;}
.section-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#888;margin:0 0 12px;padding-bottom:6px;border-bottom:2px solid #f0f0f0;}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px;}
.stat-card{background:#f8fafb;border-radius:12px;padding:14px 16px;border-left:3px solid #00cc00;}
.stat-card.red{border-color:#ef4444;}
.stat-card.blue{border-color:#3b82f6;}
.stat-card.amber{border-color:#f59e0b;}
.stat-label{font-size:.72rem;color:#888;margin-bottom:4px;}
.stat-value{font-size:1.1rem;font-weight:800;color:#111;}
.stat-sub{font-size:.72rem;color:#aaa;margin-top:2px;}
.vol-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f2f2f2;font-size:.85rem;}
.vol-row:last-child{border-bottom:none;}
.vol-label{color:#666;}
.vol-value{font-weight:700;color:#111;}
.vol-value.green{color:#009900;}
.footer{background:#f8fafb;padding:18px 32px;text-align:center;border-top:1px solid #f0f0f0;}
.footer p{font-size:.75rem;color:#aaa;margin:0;}
.total-box{background:#043a0c;border-radius:12px;padding:18px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;}
.total-label{color:rgba(255,255,255,.7);font-size:.82rem;}
.total-value{color:#00cc00;font-size:1.4rem;font-weight:800;}
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>📊 Daily Trade Summary</h1>
        <p>{{ $date }}</p>
    </div>
    <div class="body">

        <div class="total-box">
            <div>
                <div class="total-label">Total Volume (NGN)</div>
                <div class="total-value">₦{{ number_format($total_volume_ngn, 2) }}</div>
            </div>
            <div style="text-align:right;">
                <div class="total-label">New Users</div>
                <div class="total-value" style="font-size:1.2rem;">{{ $new_users }}</div>
            </div>
        </div>

        <div class="section-title">Sell Trades</div>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ $total_sell }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completed</div>
                <div class="stat-value">{{ $completed_sell }}</div>
                <div class="stat-sub">₦{{ number_format($sell_volume_ngn, 0) }}</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $pending_sell }}</div>
            </div>
            <div class="stat-card red">
                <div class="stat-label">Rejected/Cancelled</div>
                <div class="stat-value">{{ $rejected_sell }}</div>
            </div>
        </div>

        <div class="section-title">Buy Trades</div>
        <div class="stat-grid">
            <div class="stat-card blue">
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ $total_buy }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Approved</div>
                <div class="stat-value">{{ $approved_buy }}</div>
                <div class="stat-sub">₦{{ number_format($buy_volume_ngn, 0) }}</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $pending_buy }}</div>
            </div>
        </div>

        <div class="section-title">Volume Breakdown</div>
        <div class="vol-row">
            <span class="vol-label">Sell Volume (NGN)</span>
            <span class="vol-value green">₦{{ number_format($sell_volume_ngn, 2) }}</span>
        </div>
        <div class="vol-row">
            <span class="vol-label">Buy Volume (NGN)</span>
            <span class="vol-value green">₦{{ number_format($buy_volume_ngn, 2) }}</span>
        </div>
        <div class="vol-row">
            <span class="vol-label">Sell Volume (USD)</span>
            <span class="vol-value">${{ number_format($sell_volume_usd, 2) }}</span>
        </div>
        <div class="vol-row">
            <span class="vol-label">Buy Volume (USD)</span>
            <span class="vol-value">${{ number_format($buy_volume_usd, 2) }}</span>
        </div>
    </div>
    <div class="footer">
        <p>KayXchange Admin Digest — auto-generated on {{ now()->format('d M Y H:i') }} UTC</p>
    </div>
</div>
</body>
</html>
