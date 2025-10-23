<?php
return [
    'install-button' => true,
    'manifest' => [
        'name' => env('APP_NAME', 'Kayxchange'),
        'short_name' => 'Kayxchange',
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => '#6777ef',
        'description' => 'A cryptocurrency exchange platform',
        'icons' => [
            [
                'src' => '/images/icons/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
            ],
            [
                'src' => '/images/icons/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
            ],
        ],
    ],
    'debug' => env('APP_DEBUG', false),
];