<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelegramLoginController extends Controller
{
    /**
     * Handle the Telegram Login Widget callback.
     * Telegram sends user data as POST fields after JS widget authentication.
     * Verified via HMAC-SHA256 of sorted data_check_string using SHA256(bot_token) as key.
     */
    public function callback(Request $request)
    {
        $fields = ['id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash'];
        $data   = $request->only($fields);

        // Basic presence check
        if (empty($data['hash']) || empty($data['id']) || empty($data['auth_date'])) {
            return redirect()->route('login')
                ->withErrors(['telegram' => 'Incomplete Telegram authentication data.']);
        }

        // Verify Telegram hash
        if (!$this->verifyTelegramHash($data)) {
            Log::warning('Telegram web login: invalid hash', [
                'chat_id' => $data['id'],
                'ip'      => $request->ip(),
            ]);
            return redirect()->route('login')
                ->withErrors(['telegram' => 'Telegram authentication could not be verified. Please try again.']);
        }

        // auth_date must be within the last 24 hours
        if ((time() - (int) $data['auth_date']) > 86400) {
            return redirect()->route('login')
                ->withErrors(['telegram' => 'Telegram login has expired. Please try again.']);
        }

        // Find user by telegram_chat_id
        $user = User::where('telegram_chat_id', (string) $data['id'])
                    ->where('telegram_verified', true)
                    ->first();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['telegram' =>
                    'No KayXchange account is linked to this Telegram. '.
                    'Sign in with email first, then link your Telegram in Settings.'
                ]);
        }

        // Log in
        Auth::login($user, true);
        $request->session()->regenerate();

        Log::info('User logged in via Telegram widget', [
            'user_id'  => $user->id,
            'chat_id'  => $data['id'],
            'username' => $data['username'] ?? null,
        ]);

        return redirect()->intended('/dashboard');
    }

    /**
     * Verify Telegram's hash.
     * Secret key = SHA256(bot_token) as raw bytes.
     * Data check string = sorted "key=value" pairs joined by \n (hash excluded).
     */
    private function verifyTelegramHash(array $data): bool
    {
        $token = env('KAYXCHANGE_TELEGRAM_BOT_TOKEN');
        if (empty($token)) {
            Log::error('KAYXCHANGE_TELEGRAM_BOT_TOKEN is not set — Telegram login disabled.');
            return false;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        // Build sorted data_check_string (skip empty values)
        $parts = [];
        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                $parts[] = $key . '=' . $value;
            }
        }
        sort($parts);
        $dataCheckString = implode("\n", $parts);

        // Compute expected hash
        $secretKey    = hash('sha256', $token, /* raw = */ true);
        $expectedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($expectedHash, $hash);
    }
}
