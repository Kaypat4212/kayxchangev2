<?php

namespace App\Services;

use App\Models\CryptoRate;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;
    private string $phoneNumberId;
    private string $accessToken;

    public function __construct()
    {
        $this->accessToken   = config('services.whatsapp.access_token', '');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->apiUrl        = config('services.whatsapp.api_url', 'https://graph.facebook.com/v19.0');
    }

    // ─────────────────────────── Enabled guard ───────────────────────────────

    public function isEnabled(): bool
    {
        return config('services.whatsapp.enabled', false)
            && !empty($this->accessToken)
            && !empty($this->phoneNumberId);
    }

    // ─────────────────────────── State machine ───────────────────────────────

    private function getState(string $phone): ?string
    {
        return Cache::get("wa_state_{$phone}");
    }

    private function setState(string $phone, string $state): void
    {
        Cache::put("wa_state_{$phone}", $state, now()->addHour());
    }

    private function clearState(string $phone): void
    {
        Cache::forget("wa_state_{$phone}");
        Cache::forget("wa_data_{$phone}");
    }

    private function getData(string $phone): array
    {
        return Cache::get("wa_data_{$phone}", []);
    }

    private function setData(string $phone, array $data): void
    {
        Cache::put("wa_data_{$phone}", $data, now()->addHour());
    }

    private function mergeData(string $phone, array $merge): void
    {
        $this->setData($phone, array_merge($this->getData($phone), $merge));
    }

    // ─────────────────────────── Send helpers ────────────────────────────────

    /**
     * Send a plain text message (WhatsApp supports basic *bold* and _italic_ in some clients).
     */
    public function sendMessage(string $to, string $text): bool
    {
        if (!$this->isEnabled()) {
            Log::info('WhatsApp disabled — skipping message', ['to' => $to]);
            return false;
        }

        // Normalise phone: strip +, spaces, dashes
        $to = preg_replace('/\D/', '', $to);

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => false,
                        'body'        => $text,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['to' => $to]);
                return true;
            }

            Log::error('WhatsApp send failed', [
                'to'     => $to,
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp exception', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send a message with interactive reply buttons (max 3 buttons).
     */
    public function sendButtons(string $to, string $bodyText, array $buttons, string $headerText = ''): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $to = preg_replace('/\D/', '', $to);

        // Build button list (max 3, each id max 256 chars, title max 20 chars)
        $rows = [];
        foreach (array_slice($buttons, 0, 3) as $btn) {
            $rows[] = [
                'type'  => 'reply',
                'reply' => [
                    'id'    => substr($btn['id'], 0, 256),
                    'title' => substr($btn['title'], 0, 20),
                ],
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'interactive',
            'interactive'       => [
                'type' => 'button',
                'body' => ['text' => $bodyText],
                'action' => ['buttons' => $rows],
            ],
        ];

        if ($headerText) {
            $payload['interactive']['header'] = ['type' => 'text', 'text' => substr($headerText, 0, 60)];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp button send exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send a list menu (up to 10 rows).
     */
    public function sendList(string $to, string $bodyText, string $buttonLabel, array $rows, string $headerText = ''): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $to = preg_replace('/\D/', '', $to);

        $listRows = [];
        foreach (array_slice($rows, 0, 10) as $row) {
            $listRows[] = [
                'id'          => substr($row['id'], 0, 200),
                'title'       => substr($row['title'], 0, 24),
                'description' => isset($row['desc']) ? substr($row['desc'], 0, 72) : '',
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'interactive',
            'interactive'       => [
                'type' => 'list',
                'body' => ['text' => $bodyText],
                'action' => [
                    'button'   => substr($buttonLabel, 0, 20),
                    'sections' => [
                        ['title' => 'Options', 'rows' => $listRows],
                    ],
                ],
            ],
        ];

        if ($headerText) {
            $payload['interactive']['header'] = ['type' => 'text', 'text' => substr($headerText, 0, 60)];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp list send exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Mark a message as read.
     */
    public function markRead(string $messageId): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        try {
            Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'status'            => 'read',
                    'message_id'        => $messageId,
                ]);
        } catch (\Exception $e) {
            // Non-critical — don't throw
        }
    }

    /**
     * Notify all admin users who have WhatsApp number stored.
     */
    public function notifyAdmins(string $text): void
    {
        $admins = User::where('is_admin', true)
            ->whereNotNull('whatsapp_phone')
            ->where('whatsapp_notifications', true)
            ->get();

        foreach ($admins as $admin) {
            $this->sendMessage($admin->whatsapp_phone, $text);
        }
    }

    /**
     * Notify a single user if they have WhatsApp notifications enabled.
     */
    public function notifyUser(User $user, string $text): bool
    {
        if (!$user->whatsapp_phone || !$user->whatsapp_notifications || !$user->whatsapp_verified) {
            return false;
        }

        return $this->sendMessage($user->whatsapp_phone, $text);
    }

    // ─────────────────────────── Webhook processor ───────────────────────────

    public function processWebhook(array $payload): bool
    {
        try {
            $entry   = $payload['entry'][0] ?? null;
            $changes = $entry['changes'][0] ?? null;
            $value   = $changes['value'] ?? null;

            if (!$value) {
                return true;
            }

            // Delivery/read status updates — ignore
            if (isset($value['statuses'])) {
                return true;
            }

            $messages = $value['messages'] ?? [];
            if (empty($messages)) {
                return true;
            }

            foreach ($messages as $message) {
                $this->processMessage($message, $value['contacts'][0] ?? []);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing error', ['error' => $e->getMessage(), 'payload' => $payload]);
            return false;
        }
    }

    private function processMessage(array $message, array $contact): void
    {
        $phone     = $message['from'];       // E.164 without +
        $messageId = $message['id'];
        $type      = $message['type'];
        $name      = $contact['profile']['name'] ?? 'there';

        // Mark as read immediately
        $this->markRead($messageId);

        Log::info('WhatsApp message received', ['phone' => $phone, 'type' => $type]);

        // Extract text (handles text messages, button replies, list replies)
        $text = match ($type) {
            'text'        => $message['text']['body'] ?? '',
            'interactive' => $this->extractInteractiveId($message),
            'image'       => '__image__',
            default       => '',
        };

        $text = trim($text);

        // Handle image upload for proof steps
        if ($type === 'image') {
            $state = $this->getState($phone);
            if (in_array($state, ['sell_proof', 'buy_proof'])) {
                $mediaId = $message['image']['id'] ?? null;
                if ($mediaId) {
                    $this->handleProofImageUpload($phone, $mediaId, $state);
                }
            } else {
                $this->sendMessage($phone, "📸 I received your image, but I'm not expecting one right now. Type *menu* to see options.");
            }
            return;
        }

        if (empty($text)) {
            $this->sendMessage($phone, "I couldn't understand that. Type *menu* to see what I can do.");
            return;
        }

        // Check for command keywords
        $command = $this->resolveCommand($text);
        if ($command !== null) {
            $this->handleCommand($phone, $command, $name);
            return;
        }

        // State-aware input
        $state = $this->getState($phone);
        if ($state !== null) {
            $this->handleStateInput($phone, $text, $state, $name);
            return;
        }

        // Email address for account linking
        if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
            $this->handleEmailLinking($phone, $text);
            return;
        }

        $this->sendMessage($phone, "I'm not sure what you mean. Type *menu* or *help* to see available commands.");
    }

    private function extractInteractiveId(array $message): string
    {
        $interactive = $message['interactive'] ?? [];
        return match ($interactive['type'] ?? '') {
            'button_reply' => $interactive['button_reply']['id'] ?? '',
            'list_reply'   => $interactive['list_reply']['id'] ?? '',
            default        => '',
        };
    }

    private function resolveCommand(string $text): ?string
    {
        $lower = strtolower(trim($text));
        // Strip leading slash if typed
        $lower = ltrim($lower, '/');

        $known = ['start', 'menu', 'register', 'rates', 'buy', 'sell', 'balance', 'trades', 'verify', 'help', 'cancel', 'link'];

        // Exact match
        if (in_array($lower, $known, true)) {
            return $lower;
        }

        // cmd_ prefix (from button IDs)
        if (str_starts_with($lower, 'cmd_')) {
            $cmd = substr($lower, 4);
            return in_array($cmd, $known, true) ? $cmd : null;
        }

        return null;
    }

    private function handleCommand(string $phone, string $command, string $name): void
    {
        match ($command) {
            'start', 'menu' => $this->handleStartCommand($phone, $name),
            'register'      => $this->handleRegisterCommand($phone),
            'rates'         => $this->handleRatesCommand($phone),
            'buy'           => $this->handleBuyCommand($phone),
            'sell'          => $this->handleSellCommand($phone),
            'balance'       => $this->handleBalanceCommand($phone),
            'trades'        => $this->handleTradesCommand($phone),
            'help'          => $this->handleHelpCommand($phone),
            'link'          => $this->handleLinkCommand($phone),
            'cancel'        => $this->handleCancelCommand($phone),
            default         => $this->sendMessage($phone, "Unknown command. Type *menu* for help."),
        };
    }

    private function handleStateInput(string $phone, string $text, string $state, string $name): void
    {
        switch ($state) {
            case 'reg_name':          $this->handleRegNameInput($phone, $text);          break;
            case 'reg_email':         $this->handleRegEmailInput($phone, $text);         break;
            case 'reg_password':      $this->handleRegPasswordInput($phone, $text);      break;
            case 'reg_confirm_pass':  $this->handleRegConfirmPassInput($phone, $text);   break;
            case 'reg_set_pin':       $this->handleRegSetPinInput($phone, $text);        break;
            case 'reg_confirm_pin':   $this->handleRegConfirmPinInput($phone, $text);    break;
            case 'link_email':        $this->handleLinkEmailInput($phone, $text);        break;
            case 'link_pin':          $this->handleLinkPinInput($phone, $text);          break;
            case 'sell_coin':         $this->handleSellCoinInput($phone, $text);         break;
            case 'sell_amount':       $this->handleSellAmountInput($phone, $text);       break;
            case 'sell_txid':         $this->handleSellTxidInput($phone, $text);         break;
            case 'sell_pin':          $this->handleSellPinInput($phone, $text);          break;
            case 'buy_coin':          $this->handleBuyCoinInput($phone, $text);          break;
            case 'buy_amount':        $this->handleBuyAmountInput($phone, $text);        break;
            case 'buy_wallet':        $this->handleBuyWalletInput($phone, $text);        break;
            case 'buy_pin':           $this->handleBuyPinInput($phone, $text);           break;
            default:
                $this->clearState($phone);
                $this->sendMessage($phone, "Something went wrong. Type *menu* to start fresh.");
        }
    }

    // ─────────────────────────── Commands ────────────────────────────────────

    private function handleStartCommand(string $phone, string $name): void
    {
        $user    = User::where('whatsapp_phone', $phone)->where('whatsapp_verified', true)->first();
        $appUrl  = config('app.url');

        if ($user) {
            $kycStatus   = $user->kyc_verified ? 'Verified ✅' : 'Pending ⏳';
            $notifStatus = $user->whatsapp_notifications ? 'On 🔔' : 'Off 🔕';

            $body = "👋 *Welcome back, {$user->name}!*\n\n"
                  . "💰 Balance: *₦" . number_format($user->balance, 2) . "*\n"
                  . "🔐 KYC: {$kycStatus}\n"
                  . "🔔 Alerts: {$notifStatus}\n\n"
                  . "What would you like to do?";

            $this->sendButtons($phone, $body, [
                ['id' => 'cmd_sell',    'title' => '💵 Sell Crypto'],
                ['id' => 'cmd_buy',     'title' => '💸 Buy Crypto'],
                ['id' => 'cmd_balance', 'title' => '💰 My Balance'],
            ], 'KayXchange Menu');

            $this->sendButtons($phone, "More options:", [
                ['id' => 'cmd_rates',  'title' => '📊 Live Rates'],
                ['id' => 'cmd_trades', 'title' => '📋 My Trades'],
                ['id' => 'cmd_help',   'title' => '❓ Help'],
            ]);
            return;
        }

        // New / unlinked user
        $body = "🚀 *Welcome to KayXchange, {$name}!*\n\n"
              . "Buy & sell crypto fast in Nigeria.\n\n"
              . "To get started:\n"
              . "• *register* — Create a new account\n"
              . "• *link* — Link an existing account\n"
              . "• *rates* — View live rates\n\n"
              . "🌐 {$appUrl}";

        $this->sendButtons($phone, $body, [
            ['id' => 'cmd_register', 'title' => '🆕 Register'],
            ['id' => 'cmd_link',     'title' => '🔗 Link Account'],
            ['id' => 'cmd_rates',    'title' => '📊 Live Rates'],
        ], 'KayXchange Bot');
    }

    private function handleHelpCommand(string $phone): void
    {
        $text = "📖 *KayXchange WhatsApp Bot*\n\n"
              . "*Commands:*\n"
              . "• *menu* — Main menu\n"
              . "• *register* — Create account\n"
              . "• *link* — Link existing account\n"
              . "• *rates* — Live crypto rates\n"
              . "• *buy* — Buy cryptocurrency\n"
              . "• *sell* — Sell cryptocurrency\n"
              . "• *balance* — Check your balance\n"
              . "• *trades* — Recent trades\n"
              . "• *cancel* — Cancel current action\n\n"
              . "_Reply with any keyword to get started._";

        $this->sendMessage($phone, $text);
    }

    private function handleRatesCommand(string $phone): void
    {
        $rates = CryptoRate::all();

        if ($rates->isEmpty()) {
            $this->sendMessage($phone, "⚠️ No rates configured yet. Check back soon.");
            return;
        }

        $lines = ["📊 *Live Exchange Rates*\n"];
        foreach ($rates as $rate) {
            $lines[] = "• *{$rate->coin}*\n"
                     . "  Buy: ₦" . number_format($rate->buy_rate, 2) . "\n"
                     . "  Sell: ₦" . number_format($rate->sell_rate, 2);
        }
        $lines[] = "\n_Rates update frequently_";

        $this->sendMessage($phone, implode("\n", $lines));
    }

    private function handleBalanceCommand(string $phone): void
    {
        $user = $this->requireLinkedUser($phone);
        if (!$user) {
            return;
        }

        $text = "💰 *Your Balance*\n\n"
              . "Available: *₦" . number_format($user->balance, 2) . "*\n"
              . "KYC: " . ($user->kyc_verified ? 'Verified ✅' : 'Pending ⏳') . "\n"
              . "Account: {$user->bank_name} {$user->account_number}";

        $this->sendMessage($phone, $text);
    }

    private function handleTradesCommand(string $phone): void
    {
        $user = $this->requireLinkedUser($phone);
        if (!$user) {
            return;
        }

        $buys  = $user->buyTrades()->latest()->take(3)->get();
        $sells = $user->sellTrades()->latest()->take(3)->get();

        if ($buys->isEmpty() && $sells->isEmpty()) {
            $this->sendMessage($phone, "📋 You have no trades yet.\n\nType *buy* or *sell* to start trading.");
            return;
        }

        $lines = ["📋 *Recent Trades*\n"];

        foreach ($buys as $trade) {
            $lines[] = "• BUY {$trade->coin} | \${$trade->usd_amount} → ₦" . number_format($trade->naira_amount)
                     . " | " . ucfirst($trade->status)
                     . " | " . $trade->created_at->format('d M');
        }
        foreach ($sells as $trade) {
            $lines[] = "• SELL {$trade->coin} | \${$trade->usd_amount} → ₦" . number_format($trade->naira_amount)
                     . " | " . ucfirst($trade->status)
                     . " | " . $trade->created_at->format('d M');
        }

        $this->sendMessage($phone, implode("\n", $lines));
    }

    private function handleCancelCommand(string $phone): void
    {
        $this->clearState($phone);
        $this->sendMessage($phone, "✅ Cancelled. Type *menu* to start over.");
    }

    // ─────────────────────────── Link / Register ─────────────────────────────

    private function handleLinkCommand(string $phone): void
    {
        $this->clearState($phone);
        $this->setState($phone, 'link_email');
        $this->sendMessage($phone, "🔗 *Link Your KayXchange Account*\n\nReply with your registered email address.\n\n_Type *cancel* to go back._");
    }

    private function handleLinkEmailInput(string $phone, string $text): void
    {
        if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
            $this->sendMessage($phone, "❌ That doesn't look like a valid email. Please try again or type *cancel*.");
            return;
        }

        $this->handleEmailLinking($phone, $text);
    }

    private function handleEmailLinking(string $phone, string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->sendMessage($phone, "❌ No account found with that email. Double-check and try again, or type *register* to create a new account.");
            $this->clearState($phone);
            return;
        }

        if ($user->whatsapp_phone && $user->whatsapp_phone !== $phone && $user->whatsapp_verified) {
            $this->sendMessage($phone, "⚠️ This account is already linked to a different WhatsApp number. Contact support if this is yours.");
            $this->clearState($phone);
            return;
        }

        // PIN gate
        if ($user->transaction_pin) {
            $this->clearState($phone);
            $this->setState($phone, 'link_pin');
            $this->setData($phone, ['pending_user_id' => $user->id]);
            $this->sendMessage($phone, "🔐 Enter your 4-digit transaction PIN to verify it's you.\n\n_Type *cancel* to abort._");
            return;
        }

        $this->linkAccount($phone, $user);
    }

    private function handleLinkPinInput(string $phone, string $text): void
    {
        $data = $this->getData($phone);
        $user = User::find($data['pending_user_id'] ?? null);

        if (!$user) {
            $this->clearState($phone);
            $this->sendMessage($phone, "❌ Something went wrong. Type *link* to try again.");
            return;
        }

        if (!Hash::check($text, $user->transaction_pin)) {
            $this->sendMessage($phone, "❌ Incorrect PIN. Try again or type *cancel* to abort.");
            return;
        }

        $this->clearState($phone);
        $this->linkAccount($phone, $user);
    }

    private function linkAccount(string $phone, User $user): void
    {
        $user->update([
            'whatsapp_phone'         => $phone,
            'whatsapp_verified'      => true,
            'whatsapp_notifications' => true,
        ]);

        $text = "✅ *Account Linked Successfully!*\n\n"
              . "Welcome, *{$user->name}*!\n"
              . "Your KayXchange account is now connected to this WhatsApp.\n\n"
              . "💰 Balance: ₦" . number_format($user->balance, 2) . "\n\n"
              . "Type *menu* to get started.";

        $this->sendMessage($phone, $text);
    }

    private function handleRegisterCommand(string $phone): void
    {
        // Check already linked
        $existing = User::where('whatsapp_phone', $phone)->where('whatsapp_verified', true)->first();
        if ($existing) {
            $this->sendMessage($phone, "✅ You already have an account linked (*{$existing->name}*). Type *menu* to continue.");
            return;
        }

        $this->clearState($phone);
        $this->setState($phone, 'reg_name');
        $this->sendMessage($phone, "🆕 *Create Your KayXchange Account*\n\nStep 1 of 4: What's your full name?\n\n_Type *cancel* at any time to stop._");
    }

    private function handleRegNameInput(string $phone, string $text): void
    {
        if (strlen($text) < 3) {
            $this->sendMessage($phone, "❌ Name too short. Please enter your full name.");
            return;
        }

        $this->mergeData($phone, ['name' => $text]);
        $this->setState($phone, 'reg_email');
        $this->sendMessage($phone, "✅ Got it, *{$text}*!\n\nStep 2 of 4: Enter your email address.");
    }

    private function handleRegEmailInput(string $phone, string $text): void
    {
        if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
            $this->sendMessage($phone, "❌ Invalid email. Please enter a valid email address.");
            return;
        }

        if (User::where('email', $text)->exists()) {
            $this->sendMessage($phone, "❌ This email is already registered. Type *link* to link your existing account.");
            return;
        }

        $this->mergeData($phone, ['email' => $text]);
        $this->setState($phone, 'reg_password');
        $this->sendMessage($phone, "✅ Email saved.\n\nStep 3 of 4: Choose a password (minimum 8 characters).");
    }

    private function handleRegPasswordInput(string $phone, string $text): void
    {
        if (strlen($text) < 8) {
            $this->sendMessage($phone, "❌ Password must be at least 8 characters. Try again.");
            return;
        }

        $this->mergeData($phone, ['password' => $text]);
        $this->setState($phone, 'reg_confirm_pass');
        $this->sendMessage($phone, "✅ Password set.\n\nStep 4 of 4: Confirm your password.");
    }

    private function handleRegConfirmPassInput(string $phone, string $text): void
    {
        $data = $this->getData($phone);

        if ($text !== ($data['password'] ?? '')) {
            $this->sendMessage($phone, "❌ Passwords don't match. Enter your password again.");
            $this->setState($phone, 'reg_password');
            return;
        }

        $this->mergeData($phone, ['confirmed_password' => $text]);
        $this->setState($phone, 'reg_set_pin');
        $this->sendMessage($phone, "✅ Password confirmed.\n\nAlmost there! Set a 4-digit transaction PIN (used to authorise trades).");
    }

    private function handleRegSetPinInput(string $phone, string $text): void
    {
        if (!preg_match('/^\d{4}$/', $text)) {
            $this->sendMessage($phone, "❌ PIN must be exactly 4 digits. Try again.");
            return;
        }

        $this->mergeData($phone, ['pin' => $text]);
        $this->setState($phone, 'reg_confirm_pin');
        $this->sendMessage($phone, "✅ PIN set. Confirm your PIN.");
    }

    private function handleRegConfirmPinInput(string $phone, string $text): void
    {
        $data = $this->getData($phone);

        if ($text !== ($data['pin'] ?? '')) {
            $this->sendMessage($phone, "❌ PINs don't match. Enter your PIN again.");
            $this->setState($phone, 'reg_set_pin');
            return;
        }

        // Create account
        try {
            $user = User::create([
                'name'             => $data['name'],
                'email'            => $data['email'],
                'password'         => Hash::make($data['password']),
                'transaction_pin'  => Hash::make($data['pin']),
                'whatsapp_phone'   => $phone,
                'whatsapp_verified'     => true,
                'whatsapp_notifications'=> true,
                'balance'          => 0,
            ]);

            $this->clearState($phone);

            $text = "🎉 *Account Created!*\n\n"
                  . "Welcome to KayXchange, *{$user->name}*!\n\n"
                  . "✅ Account linked to this WhatsApp\n"
                  . "💰 Starting balance: ₦0.00\n\n"
                  . "Type *menu* to start trading, or visit: " . config('app.url');

            $this->sendMessage($phone, $text);
        } catch (\Exception $e) {
            $this->clearState($phone);
            Log::error('WhatsApp registration failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            $this->sendMessage($phone, "❌ Registration failed. Please try again or visit our website.");
        }
    }

    // ─────────────────────────── Sell flow ───────────────────────────────────

    private function handleSellCommand(string $phone): void
    {
        $user = $this->requireLinkedUser($phone);
        if (!$user) {
            return;
        }

        $this->clearState($phone);
        $this->setState($phone, 'sell_coin');

        $coins = CryptoRate::pluck('coin')->toArray();
        if (empty($coins)) {
            $this->sendMessage($phone, "⚠️ No coins available right now. Check back soon.");
            return;
        }

        $rows = array_map(fn($coin) => [
            'id'    => "sell_coin:{$coin}",
            'title' => $coin,
            'desc'  => 'Sell ' . $coin,
        ], $coins);

        $this->sendList($phone, "💵 *Sell Cryptocurrency*\n\nWhich coin do you want to sell?", 'Choose Coin', $rows, 'KayXchange Sell');
    }

    private function handleSellCoinInput(string $phone, string $text): void
    {
        // Support both list reply ID "sell_coin:BTC" and plain "BTC"
        $coin = strtoupper(str_replace('sell_coin:', '', $text));

        $rate = CryptoRate::where('coin', $coin)->first();
        if (!$rate) {
            $this->sendMessage($phone, "❌ Coin not found. Type *sell* to try again.");
            $this->clearState($phone);
            return;
        }

        $this->mergeData($phone, ['sell_coin' => $coin]);
        $this->setState($phone, 'sell_amount');
        $this->sendMessage($phone,
            "✅ *{$coin}* selected.\n\n"
            . "Current sell rate: ₦" . number_format($rate->sell_rate, 2) . " per USD\n\n"
            . "How many USD worth of {$coin} do you want to sell? (e.g. 50)");
    }

    private function handleSellAmountInput(string $phone, string $text): void
    {
        $amount = (float) $text;
        if ($amount <= 0) {
            $this->sendMessage($phone, "❌ Invalid amount. Enter a number greater than 0.");
            return;
        }

        $data = $this->getData($phone);
        $rate = CryptoRate::where('coin', $data['sell_coin'] ?? '')->first();
        if (!$rate) {
            $this->clearState($phone);
            $this->sendMessage($phone, "❌ Rate not found. Type *sell* to start over.");
            return;
        }

        $naira = $amount * $rate->sell_rate;
        $this->mergeData($phone, [
            'sell_amount_usd'   => $amount,
            'sell_amount_naira' => $naira,
        ]);
        $this->setState($phone, 'sell_txid');

        $this->sendMessage($phone,
            "✅ *Sell Summary*\n\n"
            . "Coin: {$data['sell_coin']}\n"
            . "Amount: \${$amount}\n"
            . "Payout: ₦" . number_format($naira, 2) . "\n\n"
            . "Now enter the *blockchain transaction ID (TXID)* for your transfer.\n\n"
            . "_Type *cancel* to abort._");
    }

    private function handleSellTxidInput(string $phone, string $text): void
    {
        if (strlen($text) < 10) {
            $this->sendMessage($phone, "❌ TXID seems too short. Please enter the full transaction ID.");
            return;
        }

        $this->mergeData($phone, ['sell_txid' => $text]);
        $this->setState($phone, 'sell_proof');

        $this->sendMessage($phone,
            "✅ TXID saved.\n\n"
            . "Now send a *screenshot/photo* of the transaction as proof.\n\n"
            . "_Supported: JPG, PNG, WEBP images_");
    }

    private function handleSellPinInput(string $phone, string $text): void
    {
        $user = User::where('whatsapp_phone', $phone)->where('whatsapp_verified', true)->first();
        if (!$user || !Hash::check($text, $user->transaction_pin)) {
            $this->sendMessage($phone, "❌ Incorrect PIN. Try again or type *cancel*.");
            return;
        }

        $this->clearState($phone);
        $data = $this->getData($phone);

        try {
            $trade = $user->sellTrades()->create([
                'coin'           => $data['sell_coin'],
                'usd_amount'     => $data['sell_amount_usd'],
                'naira_amount'   => $data['sell_amount_naira'],
                'blockchain_txid'=> $data['sell_txid'],
                'proof'          => $data['sell_proof'] ?? null,
                'payment_method' => 'default_bank',
                'bank_name'      => $user->bank_name,
                'account_number' => $user->account_number,
                'account_name'   => $user->account_name,
                'name'           => $user->name,
                'wallet_address' => null,
                'status'         => 'pending',
                'transaction_ref'=> 'SELL-WA-' . strtoupper(\Illuminate\Support\Str::random(8)),
            ]);

            $this->sendMessage($phone,
                "🎉 *Sell Order Submitted!*\n\n"
                . "Ref: {$trade->transaction_ref}\n"
                . "Coin: {$data['sell_coin']}\n"
                . "Amount: \${$data['sell_amount_usd']}\n"
                . "Payout: ₦" . number_format($data['sell_amount_naira'], 2) . "\n"
                . "Bank: {$user->bank_name} {$user->account_number}\n\n"
                . "✅ Your order is under review. We'll pay within 15–30 minutes after confirmation.\n\n"
                . "Type *trades* to check status.");
        } catch (\Exception $e) {
            Log::error('WhatsApp sell submission failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            $this->sendMessage($phone, "❌ Failed to submit order. Please try again or use the website.");
        }
    }

    // ─────────────────────────── Buy flow ────────────────────────────────────

    private function handleBuyCommand(string $phone): void
    {
        $user = $this->requireLinkedUser($phone);
        if (!$user) {
            return;
        }

        $this->clearState($phone);
        $this->setState($phone, 'buy_coin');

        $coins = CryptoRate::pluck('coin')->toArray();
        if (empty($coins)) {
            $this->sendMessage($phone, "⚠️ No coins available right now. Check back soon.");
            return;
        }

        $rows = array_map(fn($coin) => [
            'id'    => "buy_coin:{$coin}",
            'title' => $coin,
            'desc'  => 'Buy ' . $coin,
        ], $coins);

        $this->sendList($phone, "💸 *Buy Cryptocurrency*\n\nWhich coin do you want to buy?", 'Choose Coin', $rows, 'KayXchange Buy');
    }

    private function handleBuyCoinInput(string $phone, string $text): void
    {
        $coin = strtoupper(str_replace('buy_coin:', '', $text));
        $rate = CryptoRate::where('coin', $coin)->first();

        if (!$rate) {
            $this->sendMessage($phone, "❌ Coin not found. Type *buy* to try again.");
            $this->clearState($phone);
            return;
        }

        $this->mergeData($phone, ['buy_coin' => $coin]);
        $this->setState($phone, 'buy_amount');
        $this->sendMessage($phone,
            "✅ *{$coin}* selected.\n\n"
            . "Current buy rate: ₦" . number_format($rate->buy_rate, 2) . " per USD\n\n"
            . "How many USD worth of {$coin} do you want to buy? (e.g. 50)");
    }

    private function handleBuyAmountInput(string $phone, string $text): void
    {
        $amount = (float) $text;
        if ($amount <= 0) {
            $this->sendMessage($phone, "❌ Invalid amount. Enter a number greater than 0.");
            return;
        }

        $data = $this->getData($phone);
        $rate = CryptoRate::where('coin', $data['buy_coin'] ?? '')->first();
        if (!$rate) {
            $this->clearState($phone);
            $this->sendMessage($phone, "❌ Rate not found. Type *buy* to start over.");
            return;
        }

        $naira = $amount * $rate->buy_rate;
        $this->mergeData($phone, [
            'buy_amount_usd'   => $amount,
            'buy_amount_naira' => $naira,
        ]);
        $this->setState($phone, 'buy_wallet');

        $this->sendMessage($phone,
            "✅ *Buy Summary*\n\n"
            . "Coin: {$data['buy_coin']}\n"
            . "Amount: \${$amount}\n"
            . "Cost: ₦" . number_format($naira, 2) . "\n\n"
            . "Enter your *{$data['buy_coin']} wallet address* to receive the crypto.\n\n"
            . "_Type *cancel* to abort._");
    }

    private function handleBuyWalletInput(string $phone, string $text): void
    {
        if (strlen($text) < 10) {
            $this->sendMessage($phone, "❌ Wallet address seems too short. Please enter a valid address.");
            return;
        }

        $this->mergeData($phone, ['buy_wallet' => $text]);
        $this->setState($phone, 'buy_proof');

        $data   = $this->getData($phone);
        $user   = User::where('whatsapp_phone', $phone)->first();
        $company = \App\Models\CompanyAccount::first();

        $accountDetails = $company
            ? "{$company->bank_name}\nAcc No: {$company->account_number}\nAcc Name: {$company->account_name}"
            : "Check website for payment details";

        $this->sendMessage($phone,
            "✅ Wallet address saved.\n\n"
            . "💳 *Make payment of ₦" . number_format($data['buy_amount_naira'], 2) . " to:*\n"
            . $accountDetails . "\n\n"
            . "After payment, send a *photo/screenshot* of your receipt as proof.\n\n"
            . "_Supported: JPG, PNG, WEBP images_");
    }

    private function handleBuyPinInput(string $phone, string $text): void
    {
        $user = User::where('whatsapp_phone', $phone)->where('whatsapp_verified', true)->first();
        if (!$user || !Hash::check($text, $user->transaction_pin)) {
            $this->sendMessage($phone, "❌ Incorrect PIN. Try again or type *cancel*.");
            return;
        }

        $this->clearState($phone);
        $data = $this->getData($phone);

        try {
            $trade = $user->buyTrades()->create([
                'coin'           => $data['buy_coin'],
                'usd_amount'     => $data['buy_amount_usd'],
                'naira_amount'   => $data['buy_amount_naira'],
                'wallet_address' => $data['buy_wallet'],
                'payment_proof'  => $data['buy_proof'] ?? null,
                'payment_method' => 'Bank Transfer',
                'name'           => $user->name,
                'status'         => 'pending',
                'transaction_ref'=> 'BUY-WA-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'network'        => $data['buy_coin'],
            ]);

            $this->sendMessage($phone,
                "🎉 *Buy Order Submitted!*\n\n"
                . "Ref: {$trade->transaction_ref}\n"
                . "Coin: {$data['buy_coin']}\n"
                . "Amount: \${$data['buy_amount_usd']}\n"
                . "Cost: ₦" . number_format($data['buy_amount_naira'], 2) . "\n"
                . "Wallet: " . substr($data['buy_wallet'], 0, 20) . "...\n\n"
                . "✅ Your order is under review. Crypto will be sent after payment confirmation.\n\n"
                . "Type *trades* to check status.");
        } catch (\Exception $e) {
            Log::error('WhatsApp buy submission failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            $this->sendMessage($phone, "❌ Failed to submit order. Please try again or use the website.");
        }
    }

    // ─────────────────────────── Proof upload ────────────────────────────────

    private function handleProofImageUpload(string $phone, string $mediaId, string $state): void
    {
        try {
            // Download media from WhatsApp
            $mediaInfoRes = Http::withToken($this->accessToken)
                ->get("{$this->apiUrl}/{$mediaId}");

            if (!$mediaInfoRes->successful()) {
                $this->sendMessage($phone, "❌ Could not fetch your image. Please try again.");
                return;
            }

            $mediaUrl = $mediaInfoRes->json('url');

            $imageRes = Http::withToken($this->accessToken)->get($mediaUrl);
            if (!$imageRes->successful()) {
                $this->sendMessage($phone, "❌ Could not download your image. Please try again.");
                return;
            }

            $ext      = 'jpg';
            $filename = 'payment_proofs/' . \Illuminate\Support\Str::uuid() . ".{$ext}";
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $imageRes->body());

            $this->mergeData($phone, [
                $state === 'sell_proof' ? 'sell_proof' : 'buy_proof' => $filename,
            ]);

            // Move to PIN confirmation
            $this->setState($phone, str_replace('_proof', '_pin', $state));
            $this->sendMessage($phone, "✅ Proof received!\n\nEnter your 4-digit transaction PIN to confirm the trade.\n\n_Type *cancel* to abort._");
        } catch (\Exception $e) {
            Log::error('WhatsApp proof upload failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            $this->sendMessage($phone, "❌ Failed to save your image. Please try again.");
        }
    }

    // ─────────────────────────── Helpers ─────────────────────────────────────

    private function requireLinkedUser(string $phone): ?User
    {
        $user = User::where('whatsapp_phone', $phone)->where('whatsapp_verified', true)->first();

        if (!$user) {
            $this->sendMessage($phone,
                "🔗 You need to link your KayXchange account first.\n\n"
                . "Type *link* if you have an account, or *register* to create one.");
            return null;
        }

        return $user;
    }
}
