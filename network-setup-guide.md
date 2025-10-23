# KayXchange Network Access Setup Guide

## Virtual Host Configuration for XAMPP

Add this to your `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/kayxchange-laravel/public"
    ServerName kayxchange.local
    ServerAlias kayxchange.local
    <Directory "C:/xampp/htdocs/kayxchange-laravel/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Method 1: PHP Built-in Server
```bash
# Navigate to your project
cd c:\xampp\htdocs\kayxchange-laravel

# Start server accessible from network
php artisan serve --host=0.0.0.0 --port=8000
```

## Method 2: Laravel Valet (Windows)
```bash
# Install Valet
composer global require laravel/valet
valet install

# In your project directory
valet link kayxchange
valet share  # For temporary public access
```

## Method 3: Using ngrok (Public Access)
```bash
# Download ngrok from ngrok.com
# In your project directory, start Laravel
php artisan serve

# In another terminal
ngrok http 8000
```

## Firewall Configuration

### Windows Firewall Rules
1. Open Windows Defender Firewall
2. Click "Advanced settings"
3. Click "Inbound Rules" → "New Rule"
4. Select "Port" → Next
5. Select "TCP" and enter port 8000 (or 80 for XAMPP)
6. Allow the connection
7. Apply to all networks
8. Name it "Laravel Development Server"

### Quick PowerShell Command (Run as Administrator)
```powershell
# Allow port 8000 for Laravel development server
New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow

# Allow port 80 for XAMPP
New-NetFirewallRule -DisplayName "XAMPP Apache" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow
```

## Finding Your IP Address
```powershell
# PowerShell command
ipconfig | findstr "IPv4"

# Or get only WiFi adapter IP
Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias "Wi-Fi"
```

## Testing Access
Once configured, other devices on your network can access:
- **Method 1 (PHP Server):** `http://YOUR_IP:8000`
- **Method 2 (XAMPP):** `http://YOUR_IP` or `http://kayxchange.local`

## Mobile Device Access
1. Connect your phone/tablet to the same WiFi
2. Open browser and navigate to your computer's IP address
3. Example: `http://192.168.1.100:8000`

## Troubleshooting
- Ensure Windows Firewall allows the connection
- Check if antivirus is blocking the port
- Verify your IP address hasn't changed (DHCP)
- Make sure both devices are on the same network
- Try disabling Windows Firewall temporarily for testing

## Security Note
Only use these methods on trusted home networks. For production, use proper domain and SSL configuration.