<?php

return [
    // Read through config() so the terminal PIN remains available when config is cached.
    'terminal_pin' => env('ADMIN_TERMINAL_PIN', ''),
];
