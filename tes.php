<?php
// Test script — token loaded from .env, do NOT hardcode here
$token = env('TELEGRAM_BOT_TOKEN', '');
if (empty($token)) { die('Set TELEGRAM_BOT_TOKEN in .env'); }

$ch = curl_init("https://api.telegram.org/bot{$token}/getMe");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    echo 'Success:' . $response;
}
curl_close($ch);
?>