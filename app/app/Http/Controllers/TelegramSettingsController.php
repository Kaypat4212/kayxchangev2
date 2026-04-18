<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;
use App\Models\User;

class TelegramSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the Telegram settings form
     */
    public function show()
    {
        return view('settings.telegram');
    }

    /**
     * Update the user's Telegram settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telegram_username' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'telegram_notifications' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::find(Auth::id());
        
        // Clean the username (remove @ if provided)
        $telegramUsername = $request->telegram_username;
        if ($telegramUsername && str_starts_with($telegramUsername, '@')) {
            $telegramUsername = substr($telegramUsername, 1);
        }

        // Check if username has changed
        $usernameChanged = $user->telegram_username !== $telegramUsername;
        
        // Prepare update data
        $updateData = [
            'telegram_username' => $telegramUsername,
            'telegram_notifications' => $request->has('telegram_notifications'),
        ];

        // If username changed, reset verification status and chat_id
        if ($usernameChanged && $telegramUsername) {
            $updateData['telegram_chat_id'] = null;
            $updateData['telegram_verified'] = false;
            
            Log::info('Telegram username changed - resetting verification', [
                'user_id' => $user->id,
                'old_username' => $user->telegram_username,
                'new_username' => $telegramUsername,
            ]);
        }

        // If username is cleared completely, reset all telegram data
        if (!$telegramUsername) {
            $updateData['telegram_chat_id'] = null;
            $updateData['telegram_verified'] = false;
            $updateData['telegram_notifications'] = false;
        }

        $user->update($updateData);

        // Log the settings update
        Log::info('User updated Telegram settings', [
            'user_id' => $user->id,
            'telegram_username' => $telegramUsername,
            'notifications_enabled' => $request->has('telegram_notifications'),
            'username_changed' => $usernameChanged,
        ]);

        // Customize success message based on what happened
        $message = 'Telegram settings updated successfully!';
        if ($usernameChanged && $telegramUsername) {
            $message = 'Telegram username updated! Please verify your new username with our bot @TradewithkayxchangeBOT to receive notifications.';
        } elseif (!$telegramUsername) {
            $message = 'Telegram integration disabled successfully.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Send a test notification to the user's Telegram
     */
    public function test(Request $request)
    {
        $user = User::find(Auth::id());

        // Check if user has completed bot verification
        if (!$user->telegram_chat_id || !$user->telegram_verified) {
            if ($user->telegram_username) {
                return redirect()->back()->with('error', 
                    'To receive notifications, please start a chat with @TradewithkayxchangeBOT and send your email address for verification.');
            } else {
                return redirect()->back()->with('error', 
                    'Please complete Telegram setup: add your username and verify with our bot.');
            }
        }

        // Check if notifications are enabled
        if (!$user->telegram_notifications) {
            return redirect()->back()->with('error', 
                'Please enable Telegram notifications first.');
        }

        try {
            $telegramService = new TelegramService();
            $result = $telegramService->sendTestNotification($user);

            if ($result) {
                return redirect()->back()->with('success', 'Test notification sent successfully! Check your Telegram.');
            } else {
                return redirect()->back()->with('error', 'Failed to send test notification. Please ensure you have started a chat with our bot.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send test Telegram message', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to send test notification. Please check your setup and try again.');
        }
    }



    /**
     * Send notification for completed trades
     */
    public static function notifyTradeComplete($user, $trade)
    {
        try {
            $telegramService = new TelegramService();
            $telegramService->notifyTradeComplete($user, $trade);
        } catch (\Exception $e) {
            Log::error('Failed to send trade completion notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification for security alerts
     */
    public static function notifySecurityAlert($user, $alert, $details = [])
    {
        try {
            $telegramService = new TelegramService();
            $telegramService->notifySecurityAlert($user, $alert, $details);
        } catch (\Exception $e) {
            Log::error('Failed to send security alert notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification for rate updates
     */
    public static function notifyRateUpdate($user, $coins)
    {
        try {
            $telegramService = new TelegramService();
            $telegramService->notifyRateUpdate($user, $coins);
        } catch (\Exception $e) {
            Log::error('Failed to send rate update notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
