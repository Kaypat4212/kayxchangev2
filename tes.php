<?php
// Test script — token loaded from .env, do NOT hardcode here
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$token = env('TELEGRAM_BOT_TOKEN', '');
if (empty($token)) { die('Set TELEGRAM_BOT_TOKEN in .env'); }

try {
    $response = Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getMe");
    if ($response->successful()) {
        echo 'Success:' . $response->body();
    } else {
        echo 'Error: HTTP ' . $response->status();
    }
} catch (Exception $e) {
    echo 'Error:' . $e->getMessage();
}
?>