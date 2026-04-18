<?php
return [
    'name' => 'Kay Xchange',
    'manifest' => [
        'name' => env('APP_NAME', 'Kay Xchange'),
        'short_name' => 'KayXchange',
        'start_url' => '/',
        'display' => 'standalone',
        'theme_color' => '#000000',
        'background_color' => '#ffffff',
        'icons' => [
            [
                'src' => '/public/assets/favicon.png',
                'sizes' => '72x72',
                'type' => 'image/png',
            ],
            // Add more icon sizes as needed
        ],
    ],
];