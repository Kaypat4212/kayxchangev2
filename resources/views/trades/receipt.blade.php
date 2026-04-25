<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trade Receipt #{{ $trade->id }} — KayXchange</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#0d1117;color:#e4e8f0;min-height:100vh;padding:24px 16px;}
.receipt-wrap{max-width:560px;margin:0 auto;}
.receipt-card{background:#161b27;border:1px solid rgba(0,204,0,0.18);border-radius:18px;overflow:hidden;}
.receipt-header{background:linear-gradient(135deg,#0a2010 0%,#0f2a18 100%);padding:28px 28px 22px;text-align:center;border-bottom:1px solid rgba(0,204,0,0.15);}
.receipt-logo{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:10px;}
.receipt-logo-text{font-size:1.4rem;font-weight:700;color:#fff;letter-spacing:-.5px;}
.receipt-logo-green{color:#00cc00;}
.receipt-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:99px;font-size:.75rem;font-weight:600;margin-top:6px;}
.badge-completed{background:rgba(0,204,0,.15);color:#00cc00;border:1px solid rgba(0,204,0,.3);}
.badge-pending{background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.25);}
.badge-rejected{background:rgba(239,68,68,.12);color:#ef4444;border:1px solid rgba(239,68,68,.25);}
.receipt-id{font-size:.75rem;color:#7a8599;margin-top:8px;}
.receipt-body{padding:24px 28px;}
.receipt-section{margin-bottom:20px;}
.receipt-section-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#7a8599;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,0.06);}
.receipt-row{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;gap:12px;}
.receipt-label{font-size:.8rem;color:#7a8599;flex-shrink:0;}
.receipt-value{font-size:.85rem;color:#e4e8f0;font-weight:500;text-align:right;word-break:break-all;}
.receipt-value.green{color:#00cc00;font-weight:700;}
.receipt-value.large{font-size:1.1rem;font-weight:700;}
.receipt-divider{border:none;border-top:1px dashed rgba(255,255,255,0.08);margin:18px 0;}
.receipt-footer{background:#0d1117;padding:18px 28px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-top:1px solid rgba(255,255,255,0.06);}
.receipt-footer-note{font-size:.72rem;color:#7a8599;line-height:1.5;}
.receipt-actions{display:flex;gap:10px;margin-bottom:20px;}
.btn-print{background:#00cc00;color:#000;font-weight:700;border:none;border-radius:10px;padding:10px 22px;font-size:.85rem;cursor:pointer;display:flex;align-items:center;gap:6px;transition:.2s;}
.btn-print:hover{background:#00e600;}
.btn-back{background:rgba(255,255,255,0.05);color:#e4e8f0;font-weight:600;border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:10px 18px;font-size:.85rem;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:6px;transition:.2s;}
.btn-back:hover{background:rgba(255,255,255,0.09);color:#e4e8f0;}
.coin-chip{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:.8rem;font-weight:600;}
.chip-btc{background:rgba(247,147,26,.15);color:#f7931a;}
.chip-eth{background:rgba(98,126,234,.15);color:#627eea;}
.chip-usdt{background:rgba(38,161,123,.15);color:#26a17b;}

@media print {
    body{background:#fff;color:#111;padding:0;}
    .receipt-actions{display:none;}
    .receipt-card{border:1.5px solid #ccc;border-radius:12px;}
    .receipt-header{background:#f0faf0 !important;}
    .receipt-logo-text,.receipt-logo-green{color:#007a0f !important;}
    .receipt-section-title,.receipt-label{color:#666;}
    .receipt-value{color:#111;}
    .receipt-footer{background:#f9f9f9;}
    .receipt-footer-note{color:#666;}
    .badge-completed{background:#d1fae5;color:#065f46;border-color:#a7f3d0;}
    .badge-pending{background:#fef3c7;color:#92400e;border-color:#fcd34d;}
    .badge-rejected{background:#fee2e2;color:#991b1b;border-color:#fca5a5;}
    .receipt-body{padding:20px;}
}
</style>
</head>
<body>
<div class="receipt-wrap">
    <div class="receipt-actions">
        <a href="{{ url()->previous() }}" class="btn-back">&#8592; Back</a>
        <button class="btn-print" onclick="window.print()">&#128438; Download / Print</button>
    </div>
    <div class="receipt-card">
        <div class="receipt-header">
            <div class="receipt-logo">
                <div class="receipt-logo-text">Kay<span class="receipt-logo-green">Xchange</span></div>
            </div>
            <div style="font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:6px;">
                {{ $type === 'sell' ? 'Sell Trade Receipt' : 'Buy Trade Receipt' }}
            </div>
            @php
                $statusClass = match($trade->status) {
                    'completed','approved' => 'badge-completed',
                    'rejected','cancelled' => 'badge-rejected',
                    default => 'badge-pending',
                };
                $statusLabel = ucfirst($trade->status ?? 'pending');
            @endphp
            <span class="receipt-badge {{ $statusClass }}">
                {{ $statusLabel }}
            </span>
            <div class="receipt-id">Receipt #{{ str_pad($trade->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="receipt-body">
            {{-- Trade Info --}}
            <div class="receipt-section">
                <div class="receipt-section-title">Trade Details</div>
                <div class="receipt-row">
                    <span class="receipt-label">Trade ID</span>
                    <span class="receipt-value">#{{ str_pad($trade->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Type</span>
                    <span class="receipt-value">{{ $type === 'sell' ? 'Sell Crypto → Naira' : 'Buy Crypto (Naira → Crypto)' }}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Coin</span>
                    <span class="receipt-value">
                        <span class="coin-chip chip-{{ strtolower($trade->coin ?? 'btc') }}">{{ $trade->coin ?? 'N/A' }}</span>
                    </span>
                </div>
                @if($trade->network)
                <div class="receipt-row">
                    <span class="receipt-label">Network</span>
                    <span class="receipt-value">{{ $trade->network }}</span>
                </div>
                @endif
                <div class="receipt-row">
                    <span class="receipt-label">Date</span>
                    <span class="receipt-value">{{ $trade->created_at?->format('d M Y, H:i') ?? 'N/A' }}</span>
                </div>
                @if($trade->transaction_ref ?? null)
                <div class="receipt-row">
                    <span class="receipt-label">Reference</span>
                    <span class="receipt-value" style="font-family:monospace;font-size:.75rem;">{{ $trade->transaction_ref }}</span>
                </div>
                @endif
            </div>

            <hr class="receipt-divider">

            {{-- Amount --}}
            <div class="receipt-section">
                <div class="receipt-section-title">Amount</div>
                @if($trade->usd_amount ?? null)
                <div class="receipt-row">
                    <span class="receipt-label">Crypto Value (USD)</span>
                    <span class="receipt-value">${{ number_format($trade->usd_amount, 2) }}</span>
                </div>
                @endif
                @if($trade->rate_used ?? null)
                <div class="receipt-row">
                    <span class="receipt-label">Rate Used</span>
                    <span class="receipt-value">₦{{ number_format($trade->rate_used, 2) }}/USD</span>
                </div>
                @endif
                <div class="receipt-row">
                    <span class="receipt-label">Naira {{ $type === 'sell' ? 'Received' : 'Paid' }}</span>
                    <span class="receipt-value green large">₦{{ number_format($trade->naira_amount ?? 0, 2) }}</span>
                </div>
            </div>

            @if($type === 'sell')
            <hr class="receipt-divider">
            {{-- Payment details --}}
            <div class="receipt-section">
                <div class="receipt-section-title">Payout Details</div>
                @if($trade->payment_method === 'wallet')
                <div class="receipt-row">
                    <span class="receipt-label">Payout Method</span>
                    <span class="receipt-value">KayXchange Wallet</span>
                </div>
                @else
                <div class="receipt-row">
                    <span class="receipt-label">Payout Method</span>
                    <span class="receipt-value">Bank Transfer</span>
                </div>
                @if($trade->bank_name ?? null)
                <div class="receipt-row">
                    <span class="receipt-label">Bank</span>
                    <span class="receipt-value">{{ $trade->bank_name }}</span>
                </div>
                @endif
                @if($trade->account_number ?? null)
                <div class="receipt-row">
                    <span class="receipt-label">Account No.</span>
                    <span class="receipt-value" style="font-family:monospace;">{{ $trade->account_number }}</span>
                </div>
                @endif
                @if($trade->account_name ?? null)
                <div class="receipt-row">
                    <span class="receipt-label">Account Name</span>
                    <span class="receipt-value">{{ $trade->account_name }}</span>
                </div>
                @endif
                @endif
            </div>
            @else
            <hr class="receipt-divider">
            <div class="receipt-section">
                <div class="receipt-section-title">Delivery Details</div>
                @if($trade->wallet_address ?? null)
                <div class="receipt-row">
                    <span class="receipt-label">Wallet Address</span>
                    <span class="receipt-value" style="font-family:monospace;font-size:.72rem;">{{ $trade->wallet_address }}</span>
                </div>
                @endif
                @if($trade->blockchain_txid ?? null)
                <div class="receipt-row">
                    <span class="receipt-label">TX ID</span>
                    <span class="receipt-value" style="font-family:monospace;font-size:.72rem;">{{ $trade->blockchain_txid }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        <div class="receipt-footer">
            <div class="receipt-footer-note">
                Thank you for trading with KayXchange.<br>
                Support: support@tradewithkay.com
            </div>
            <div style="font-size:.7rem;color:#7a8599;text-align:right;">
                Generated<br>{{ now()->format('d M Y H:i') }}
            </div>
        </div>
    </div>
</div>
</body>
</html>
