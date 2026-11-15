<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\User;

class DashboardStackService
{
    public const STACKS = [
        'balance_stats' => 'Balances and account stats',
        'recent_activity' => 'Recent account activity',
        'ai_insight' => 'KayBot insight',
        'badges' => 'Achievements',
        'quick_actions' => 'Quick actions',
        'bills_utilities' => 'Bills and utilities',
        'wallets' => 'Wallet shortcuts',
        'rates_prices' => 'Rates and live prices',
        'transaction_history' => 'Transaction history',
        'blog' => 'Latest news',
    ];

    public function adminDefaults(): array
    {
        $saved = json_decode((string) AdminSetting::getSetting('dashboard_stack_defaults', ''), true);
        return $this->normalize(is_array($saved) ? $saved : []);
    }

    public function forUser(User $user): array
    {
        $saved = $user->dashboard_layout;
        return is_array($saved) ? $this->normalize($saved) : $this->adminDefaults();
    }

    public function normalize(array $layout): array
    {
        $allowed = array_keys(self::STACKS);
        $requestedOrder = is_array($layout['order'] ?? null) ? $layout['order'] : [];
        $order = array_values(array_unique(array_filter(
            $requestedOrder,
            fn ($id) => is_string($id) && in_array($id, $allowed, true)
        )));
        $order = array_values(array_merge($order, array_diff($allowed, $order)));
        $visible = is_array($layout['visible'] ?? null) ? $layout['visible'] : $allowed;
        if ($visible === []) $visible = $allowed;
        $visible = array_values(array_unique(array_filter(
            $visible,
            fn ($id) => is_string($id) && in_array($id, $allowed, true)
        )));

        return ['order' => $order, 'visible' => $visible];
    }
}
