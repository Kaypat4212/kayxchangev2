<?php

return [
    // Company receiving wallets. A blank value means deposits for that
    // asset/network are unavailable; never substitute a random address.
    'BTC' => env('WALLET_BTC_ADDRESS', ''),
    'ETH' => env('WALLET_ETH_ADDRESS', ''),
    'SOL' => env('WALLET_SOL_ADDRESS', ''),
    'BNB' => env('WALLET_BNB_ADDRESS', ''),
    'USDC' => env('WALLET_USDC_ADDRESS', ''),

    // Token addresses must be configured per network. Do not silently reuse
    // an Ethereum address for BNB Chain or Tron.
    'USDT' => env('WALLET_USDT_ADDRESS', ''),
    'USDT_ERC20' => env('WALLET_USDT_ERC20_ADDRESS', ''),
    'USDT_TRC20' => env('WALLET_USDT_TRC20_ADDRESS', ''),
    'USDT_BEP20' => env('WALLET_USDT_BEP20_ADDRESS', ''),
    'USDC_ERC20' => env('WALLET_USDC_ERC20_ADDRESS', ''),
    'USDC_BEP20' => env('WALLET_USDC_BEP20_ADDRESS', ''),
];
