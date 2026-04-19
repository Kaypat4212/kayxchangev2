<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'telegram' => [
        'token'        => env('TELEGRAM_TOKEN'),
        'chat_id'      => env('TELEGRAM_CHAT_ID'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME', 'TradewithkayxchangeBOT'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model'   => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'api_url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model'   => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'model'   => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'api_url' => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/chat/completions'),
    ],
    
    'paystack' => [
        'secret_key'   => env('PAYSTACK_SECRET_KEY'),
        'public_key'   => env('PAYSTACK_PUBLIC_KEY'),
        'callback_url' => env('PAYSTACK_CALLBACK_URL'),
    ],

    'opay' => [
        'public_key'  => env('OPAY_PUBLIC_KEY'),
        'private_key' => env('OPAY_PRIVATE_KEY'),
        'merchant_id' => env('OPAY_MERCHANT_ID'),
        'base_url'    => env('OPAY_BASE_URL', 'https://api.opayweb.com'),
    ],

    'korapay' => [
        'secret_key' => env('KORAPAY_SECRET_KEY'),
        'public_key' => env('KORAPAY_PUBLIC_KEY'),
    ],

    'flutterwave' => [
        'secret_key'   => env('FLUTTERWAVE_SECRET_KEY'),
        'public_key'   => env('FLUTTERWAVE_PUBLIC_KEY'),
        'webhook_hash' => env('FLUTTERWAVE_WEBHOOK_HASH'),
    ],

    'etherscan' => [
        'key' => env('ETHERSCAN_API_KEY', ''),
    ],

    'bscscan' => [
        'key' => env('BSCSCAN_API_KEY', ''),
    ],

    'trongrid' => [
        'key' => env('TRONGRID_API_KEY', ''),
    ],

    'blockcypher' => [
        'token' => env('BLOCKCYPHER_TOKEN', ''),
    ],

];
