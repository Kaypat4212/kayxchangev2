# Laravel Network Access Setup Script
# Run this as Administrator in PowerShell

Write-Host "Setting up Laravel for network access..." -ForegroundColor Green

# Get local IP address
$ip = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Wi-Fi*" | Where-Object {$_.IPAddress -like "192.168.*" -or $_.IPAddress -like "10.0.*"}).IPAddress

if ($ip) {
    Write-Host "Your local IP address is: $ip" -ForegroundColor Yellow
    Write-Host "Other devices can access your Laravel app at: http://$ip:8000" -ForegroundColor Cyan
} else {
    Write-Host "Could not detect WiFi IP address. Please check manually with 'ipconfig'" -ForegroundColor Red
}

# Configure Windows Firewall
Write-Host "Configuring Windows Firewall..." -ForegroundColor Green

try {
    # Allow Laravel development server (port 8000)
    New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow -Profile Any -ErrorAction SilentlyContinue
    Write-Host "✓ Firewall rule created for port 8000 (Laravel)" -ForegroundColor Green
    
    # Allow XAMPP Apache (port 80)
    New-NetFirewallRule -DisplayName "XAMPP Apache" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow -Profile Any -ErrorAction SilentlyContinue
    Write-Host "✓ Firewall rule created for port 80 (XAMPP)" -ForegroundColor Green
    
} catch {
    Write-Host "⚠ Could not create firewall rules. Please run as Administrator." -ForegroundColor Red
}

Write-Host "`nNext steps:" -ForegroundColor Yellow
Write-Host "1. Navigate to your Laravel project: cd c:\xampp\htdocs\kayxchange-laravel" -ForegroundColor White
Write-Host "2. Start the server: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor White
Write-Host "3. Access from other devices: http://$ip:8000" -ForegroundColor White

Read-Host "Press Enter to continue..."