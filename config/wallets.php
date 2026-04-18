<?php

return [
    // Single-coin wallets
    'BTC' => env('WALLET_BTC_ADDRESS', ''),
    'ETH' => env('WALLET_ETH_ADDRESS', ''),
    'SOL' => env('WALLET_SOL_ADDRESS', ''),

    // USDT per-network (legacy key kept for backward compat)
    'USDT' => env('WALLET_USDT_ADDRESS', env('WALLET_USDT_TRC20_ADDRESS', '')),

    // USDT explicit network keys
    'USDT_ERC20' => env('WALLET_USDT_ERC20_ADDRESS', env('WALLET_ETH_ADDRESS', '')),
    'USDT_TRC20' => env('WALLET_USDT_TRC20_ADDRESS', env('WALLET_USDT_ADDRESS', '')),
    'USDT_BEP20' => env('WALLET_USDT_BEP20_ADDRESS', env('WALLET_ETH_ADDRESS', '')),
];
