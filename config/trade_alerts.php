<?php

return [
    // Master switch for trade-op alerts
    'enabled' => env('TRADE_ALERTS_ENABLED', true),

    // Trigger rules
    'notify_new_buy' => env('TRADE_ALERT_NOTIFY_NEW_BUY', true),
    'notify_new_sell' => env('TRADE_ALERT_NOTIFY_NEW_SELL', true),
    'notify_new_withdrawal' => env('TRADE_ALERT_NOTIFY_NEW_WITHDRAWAL', true),
    'notify_status_changes' => env('TRADE_ALERT_NOTIFY_STATUS_CHANGES', true),

    // Risk and routing
    'high_value_ngn_threshold' => (float) env('TRADE_ALERT_HIGH_VALUE_NGN_THRESHOLD', 500000),
    'high_risk_score_threshold' => (int) env('TRADE_ALERT_HIGH_RISK_SCORE_THRESHOLD', 60),
    'vip_chat_id' => env('TRADE_ALERT_VIP_CHAT_ID', ''),

    // Pending trade SLA + escalation
    'pending_sla_minutes' => (int) env('TRADE_ALERT_PENDING_SLA_MINUTES', 20),
    'escalate_after_minutes' => (int) env('TRADE_ALERT_ESCALATE_AFTER_MINUTES', 30),
    'escalation_chat_id' => env('TRADE_ALERT_ESCALATION_CHAT_ID', ''),
];
