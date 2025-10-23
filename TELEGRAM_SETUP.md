# Telegram Bot Setup Guide for KayXchange

## Overview
This guide will help you set up the Telegram notification system using your bot token: `6249943820:AAEGJ2Oc7XJLtPGFBx8-rbPUuXZtgBvQ7hI`

## Prerequisites
1. Laravel application running on a publicly accessible domain (for webhooks)
2. PHP with cURL extension
3. Database migration completed
4. Environment variables configured

## Step 1: Environment Configuration
The bot token has already been added to your `.env` file:
```
KAYXCHANGE_TELEGRAM_BOT_TOKEN=6249943820:AAEGJ2Oc7XJLtPGFBx8-rbPUuXZtgBvQ7hI
```

## Step 2: Database Migration
Run the migration to add Telegram fields to users table:
```bash
php artisan migrate
```

This adds:
- `telegram_username` - User's Telegram username
- `telegram_notifications` - Boolean for notification preference
- `telegram_chat_id` - Chat ID for sending messages
- `telegram_verified` - Boolean for verification status

## Step 3: Test Bot Connection
Test if the bot is working:
```bash
# Via web browser or curl
curl http://your-domain.com/api/telegram/bot-info
```

Expected response:
```json
{
    "success": true,
    "bot_info": {
        "id": 6249943820,
        "is_bot": true,
        "first_name": "Your Bot Name",
        "username": "your_bot_username"
    }
}
```

## Step 4: Set Up Webhook (Production)
For production with a public domain:
```bash
# Via web browser or curl
curl http://your-domain.com/api/telegram/setup-webhook
```

Expected response:
```json
{
    "success": true,
    "message": "Webhook set up successfully",
    "webhook_url": "https://your-domain.com/api/telegram/webhook"
}
```

## Step 5: Configure Bot Commands (Optional)
You can set up bot commands through BotFather on Telegram:

1. Open Telegram and find @BotFather
2. Send `/setcommands`
3. Select your bot
4. Send this command list:
```
start - Start the bot and get welcome message
help - Show help and instructions
verify - Check verification status
```

## Step 6: Test User Flow

### 6.1 User starts bot on Telegram:
1. User finds your bot on Telegram
2. User sends `/start`
3. Bot responds with welcome message

### 6.2 User verifies email:
1. User sends their KayXchange email address
2. Bot verifies email exists in database
3. Bot updates user record with chat_id and telegram_username
4. Bot sends confirmation message

### 6.3 User enables notifications:
1. User logs into KayXchange dashboard
2. Goes to Settings → Telegram Notifications
3. Toggles notifications on
4. Tests notification

## Available API Endpoints

### Public Endpoints:
- `POST /api/telegram/webhook` - Receives Telegram updates
- `GET /api/telegram/bot-info` - Test bot connection

### Admin Endpoints:
- `GET /api/telegram/setup-webhook` - Set up webhook URL

### User Dashboard:
- Settings page with Telegram integration
- Test notification functionality

## Usage Examples

### Send Test Notification:
```php
use App\Services\TelegramService;

$telegramService = new TelegramService();
$user = User::find(1);
$telegramService->sendTestNotification($user);
```

### Notify Trade Completion:
```php
TelegramSettingsController::notifyTradeCompletion($user, $trade);
```

### Notify Security Alert:
```php
TelegramSettingsController::notifySecurityAlert($user, 'Login from new device');
```

## Troubleshooting

### Common Issues:

1. **Webhook not receiving updates:**
   - Check if URL is publicly accessible
   - Verify SSL certificate is valid
   - Check Laravel logs for errors

2. **Bot not responding:**
   - Verify bot token is correct
   - Check if bot is blocked by user
   - Verify internet connectivity

3. **Database errors:**
   - Run migration: `php artisan migrate`
   - Check database connection
   - Verify table structure

### Debug Commands:
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test webhook setup
curl -X GET http://your-domain.com/api/telegram/setup-webhook

# Test bot info
curl -X GET http://your-domain.com/api/telegram/bot-info
```

## Security Considerations

1. **Webhook Security:**
   - Use HTTPS for webhook URL
   - Implement request validation if needed
   - Monitor webhook logs

2. **User Privacy:**
   - Store minimal user data
   - Allow users to disable notifications
   - Provide clear privacy settings

3. **Bot Token Security:**
   - Keep token in environment variables
   - Never commit token to version control
   - Regenerate token if compromised

## Production Deployment

1. **Domain Setup:**
   - Ensure your domain has valid SSL
   - Configure proper DNS settings
   - Test webhook accessibility

2. **Server Configuration:**
   - Enable required PHP extensions
   - Set proper file permissions
   - Configure web server (Apache/Nginx)

3. **Monitoring:**
   - Set up log monitoring
   - Monitor webhook response times
   - Track notification delivery rates

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`  
2. Review Telegram Bot API documentation
3. Test individual components using the API endpoints

The system is now ready for production use with real Telegram notifications!