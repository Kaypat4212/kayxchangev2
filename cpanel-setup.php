<?php

// cPanel Deployment Helper
// Run this file once after uploading to cPanel to set up everything automatically

echo "<h1>KayXchange cPanel Setup</h1>";

// Check requirements
echo "<h2>Checking Requirements...</h2>";

// PHP Version
$phpVersion = phpversion();
echo "PHP Version: $phpVersion ";
if (version_compare($phpVersion, '8.1.0') >= 0) {
    echo "✅<br>";
} else {
    echo "❌ (Need PHP 8.1+)<br>";
}

// Extensions
$required = ['curl', 'json', 'mbstring', 'openssl', 'pdo', 'pdo_mysql'];
foreach ($required as $ext) {
    echo "Extension $ext: ";
    if (extension_loaded($ext)) {
        echo "✅<br>";
    } else {
        echo "❌<br>";
    }
}

// SSL Check
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
          || $_SERVER['SERVER_PORT'] == 443;
echo "HTTPS: " . ($isHttps ? "✅" : "❌ (Required for Telegram webhook)") . "<br>";

// Writable directories
$dirs = ['storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views'];
echo "<h2>Directory Permissions...</h2>";
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "$dir: " . (is_writable($dir) ? "✅" : "❌") . "<br>";
    } else {
        echo "$dir: ❌ (Missing)<br>";
    }
}

// Database connection test
echo "<h2>Database Connection...</h2>";
try {
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    echo "Database: ✅<br>";
} catch (Exception $e) {
    echo "Database: ❌ (" . $e->getMessage() . ")<br>";
}

// Telegram bot test
echo "<h2>Telegram Bot Test...</h2>";
$botToken = $_ENV['KAYXCHANGE_TELEGRAM_BOT_TOKEN'] ?? '';
if ($botToken) {
    $url = "https://api.telegram.org/bot$botToken/getMe";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if ($data && $data['ok']) {
        echo "Bot Connection: ✅<br>";
        echo "Bot Name: " . $data['result']['first_name'] . "<br>";
        echo "Bot Username: @" . $data['result']['username'] . "<br>";
    } else {
        echo "Bot Connection: ❌<br>";
    }
} else {
    echo "Bot Token: ❌ (Not set)<br>";
}

echo "<h2>Setup Commands</h2>";
echo "<p>If everything looks good above, run these commands:</p>";
echo "<code>php artisan migrate</code><br>";
echo "<code>php artisan telegram:setup</code><br>";

echo "<h2>Quick Links</h2>";
echo "<a href='/api/telegram/bot-info' target='_blank'>Test Bot Info</a><br>";
echo "<a href='/api/telegram/setup-webhook' target='_blank'>Setup Webhook</a><br>";
echo "<a href='/dashboard' target='_blank'>Dashboard</a><br>";

?>