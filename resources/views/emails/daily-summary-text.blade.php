KayXchange daily trade summary
{{ $date }}

OVERVIEW
Total volume: NGN {{ number_format($total_volume_ngn, 2) }}
New users: {{ $new_users }}

SELL TRADES
Total: {{ $total_sell }}
Completed: {{ $completed_sell }}
Pending: {{ $pending_sell }}
Rejected or cancelled: {{ $rejected_sell }}
Completed volume: NGN {{ number_format($sell_volume_ngn, 2) }} (USD {{ number_format($sell_volume_usd, 2) }})

BUY TRADES
Total: {{ $total_buy }}
Approved: {{ $approved_buy }}
Pending: {{ $pending_buy }}
Approved volume: NGN {{ number_format($buy_volume_ngn, 2) }} (USD {{ number_format($buy_volume_usd, 2) }})

This is an automated KayXchange admin digest.
