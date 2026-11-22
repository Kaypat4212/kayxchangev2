<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\TradeNotification;
use App\Models\AdminSetting;
use App\Models\SiteContent;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\AdminTradeAlertService;
use App\Services\BalanceSettlementService;
use App\Services\PayoutService;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['approveWithdrawal', 'cancelWithdrawal']);
        $this->middleware('throttle:10,1')->only('processWithdrawal');
        $this->middleware('admin')->only('approveWithdrawal', 'cancelWithdrawal');
    }

    // ── Fee helper ────────────────────────────────────────────────────────────
    private function calculateFee(float $amount, string $type, float $value): float
    {
        $value = max(0, $value);
        if ($type === 'flat') return $value;
        if ($type === 'percentage') return round($amount * $value / 100, 2);
        return 0.0;
    }

    private function maximumWithdrawableAmount(float $availableBalance, string $feeType, float $feeValue): float
    {
        return match ($feeType) {
            'flat' => max(0, round($availableBalance - $feeValue, 2)),
            'percentage' => $feeValue >= 0
                ? max(0, floor(($availableBalance / (1 + $feeValue / 100)) * 100) / 100)
                : max(0, round($availableBalance, 2)),
            default => max(0, round($availableBalance, 2)),
        };
    }

    public function withdraw()
    {
        $user = Auth::user();
        $pendingReserved = (float) Withdrawal::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum(DB::raw('amount + COALESCE(fee_amount, 0)'));
        $walletBalance = max(0, (float) ($user->balance ?? 0));
        $availableBalance = max(0, $walletBalance - $pendingReserved);
        $feeType = (string) AdminSetting::getSetting('withdrawal_fee_type', 'none');
        $feeValue = max(0, (float) AdminSetting::getSetting('withdrawal_fee_value', '0'));
        $maxWithdrawable = $this->maximumWithdrawableAmount($availableBalance, $feeType, $feeValue);
        return view('withdraw.form', [
            'balance'            => $walletBalance,
            'available_balance' => $availableBalance,
            'max_withdrawable'  => $maxWithdrawable,
            'minimum_withdrawal' => config('withdrawal.minimum', 10),
            'bank_details'       => [
                'bank_name'      => $user->bank_name ?? 'N/A',
                'account_number' => $user->account_number ?? 'N/A',
                'account_name'   => $user->account_name ?? 'N/A',
            ],
            'fee_type'  => $feeType,
            'fee_value' => $feeValue,
        ]);
    }

    public function processWithdrawal(Request $request)
    {
        $user = Auth::user();

        $cooldownHours = max(0, min(720, (int) AdminSetting::getSetting('withdrawal_security_cooldown_hours', '24')));
        if ($cooldownHours > 0 && $user->payout_details_changed_at && !$request->session()->has('admin_id')) {
            $cooldownEndsAt = $user->payout_details_changed_at->copy()->addHours($cooldownHours);
            if (now()->lt($cooldownEndsAt)) {
                $message = 'For your security, Naira withdrawals are paused after payout bank details change. Try again after ' . $cooldownEndsAt->format('d M Y, H:i') . ' WAT.';
                return response()->json(['errors' => ['withdrawal' => [$message]]], 422);
            }
        }

        // Validation rules with custom messages
        $feeType  = AdminSetting::getSetting('withdrawal_fee_type', 'none');
        $feeValue = max(0, (float) AdminSetting::getSetting('withdrawal_fee_value', '0'));
        $reqAmount = (float) $request->input('amount', 0);
        $fee = $this->calculateFee($reqAmount, $feeType, $feeValue);
        $pendingReserved = (float) Withdrawal::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum(DB::raw('amount + COALESCE(fee_amount, 0)'));
        $availableBalance = max(0, (float) ($user->balance ?? 0) - $pendingReserved);
        $maximumWithdrawable = $this->maximumWithdrawableAmount($availableBalance, $feeType, $feeValue);
        Log::debug('Processing Naira withdrawal request', [
            'user_id' => $user->id,
            'amount' => $reqAmount,
            'payment_method' => $request->input('payment_method'),
            'bank_option' => $request->input('bank_option'),
        ]);

        $validator = Validator::make($request->all(), [
            'amount' => [
                'required',
                'numeric',
                'min:' . config('withdrawal.minimum', 10),
                'max:' . $maximumWithdrawable,
            ],
            'password' => [
                'required',
                'string',
            ],
            'payment_method' => 'required|in:bank',
            'bank_option' => 'required_if:payment_method,bank|in:default,external',
            'external_bank_name' => 'required_if:bank_option,external|string|max:100|nullable',
            'external_bank_code' => 'required_if:bank_option,external|string|max:20|nullable',
            'external_account_number' => 'required_if:bank_option,external|string|max:50|nullable',
            'external_account_name' => 'required_if:bank_option,external|string|max:100|nullable',
        ], [
            'amount.required' => 'Please enter an amount.',
            'amount.min' => 'Amount must be at least ₦' . config('withdrawal.minimum', 10) . '.',
            'amount.max' => 'This exceeds your available Naira withdrawal amount after pending withdrawals and fees.',
            'password.required' => 'Please enter your password.',
            'payment_method.required' => 'Please select a payment method.',
            'bank_option.required_if' => 'Please select a bank option.',
            'external_bank_name.required_if' => 'Please select a bank.',
            'external_bank_code.required_if' => 'Please select a bank.',
            'external_account_number.required_if' => 'Please enter the account number.',
            'external_account_name.required_if' => 'Please enter the account name.',
        ]);

        if ($validator->fails()) {
            Log::warning('Withdrawal validation failed: ', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Withdrawal failed: Incorrect password', ['user_id' => $user->id]);
            return response()->json(['errors' => ['password' => ['Incorrect password.']]], 422);
        }

        // Determine bank details
        $bankDetails = $request->bank_option === 'default' ? [
            'bank_name' => $user->bank_name ?? 'N/A',
            'account_number' => $user->account_number ?? 'N/A',
            'account_name' => $user->account_name ?? 'N/A',
        ] : [
            'bank_name' => $request->external_bank_name ?? 'N/A',
            'bank_code' => $request->external_bank_code ?? '',
            'account_number' => $request->external_account_number ?? 'N/A',
            'account_name' => $request->external_account_name ?? 'N/A',
        ];

        // Start transaction
        DB::beginTransaction();
        try {
            // Re-calculate fee inside transaction with validated amount
            $validatedAmount = (float) $request->amount;
            $fee = $this->calculateFee($validatedAmount, $feeType, $feeValue);
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $pendingReserved = (float) Withdrawal::query()
                ->where('user_id', $lockedUser->id)
                ->where('status', 'pending')
                ->sum(DB::raw('amount + COALESCE(fee_amount, 0)'));
            if ((float) $lockedUser->balance - $pendingReserved < $validatedAmount + $fee) {
                throw new \RuntimeException('Your available Naira balance is ₦' . number_format(max(0, (float) $lockedUser->balance - $pendingReserved), 2) . ' after pending withdrawals. The requested amount plus the ₦' . number_format($fee, 2) . ' fee exceeds it.');
            }

            // Create withdrawal record
            $withdrawal = Withdrawal::create([
                'user_id'      => $user->id,
                'amount'       => $validatedAmount,
                'fee_amount'   => $fee,
                'bank_account' => json_encode($bankDetails),
                'status'       => 'pending',
                'currency'     => 'NGN',
                'reference'    => 'WDRW-' . Str::random(8),
            ]);

            // Send Telegram notification
            $this->sendTelegramNotification($user, $withdrawal, $bankDetails);

            // Standardized admin alert + in-app badge notification
            try {
                app(AdminTradeAlertService::class)->sendTriggeredAlert('withdrawal', [
                    'trade_id'    => $withdrawal->id,
                    'user_id'     => $user->id,
                    'reference'   => $withdrawal->reference,
                    'user_name'   => $user->name,
                    'user_email'  => $user->email,
                    'coin'        => 'NGN',
                    'usd_amount'  => 'N/A',
                    'naira_amount' => number_format((float) $withdrawal->amount, 2),
                    'wallet_address' => 'N/A',
                    'network'     => 'Bank Transfer',
                    'status'      => $withdrawal->status,
                ]);
            } catch (\Throwable $alertEx) {
                Log::warning('Withdrawal admin alert failed: ' . $alertEx->getMessage());
            }

            DB::commit();
            Log::info('Withdrawal created: ', $withdrawal->toArray());

            // Send withdrawal submitted email
            try {
                $accountDetails = ($bankDetails['account_name'] ?? 'N/A') . ' — '
                    . ($bankDetails['account_number'] ?? 'N/A') . ' (' . ($bankDetails['bank_name'] ?? 'N/A') . ')';
                Mail::to($user->email)->send(new TradeNotification(
                    user: $user,
                    templateKey: 'withdrawal_submitted',
                    data: [
                        'amount'          => number_format((float)$request->amount, 2),
                        'payment_method'  => 'Bank Transfer',
                        'account_details' => $accountDetails,
                    ],
                    badge: ['text' => 'Withdrawal Received', 'color' => '#f0a500'],
                    ctaUrl: url('/dashboard'),
                    ctaText: 'Go to Dashboard',
                ));
            } catch (\Exception $mailEx) {
                Log::warning('Withdrawal submitted email failed: ' . $mailEx->getMessage());
            }

            return response()->json([
                'success'        => true,
                'withdrawal_id'  => $withdrawal->id,
                'bank_option'    => $request->bank_option,
                'bank_details'   => $bankDetails,
                'fee_amount'     => $fee,
                'total_deducted' => $validatedAmount + $fee,
                'redirect'       => route('withdraw.success', $withdrawal->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if (str_contains($e->getMessage(), 'available Naira balance')) {
                Log::warning('Naira withdrawal declined because available balance is insufficient.', [
                    'user_id' => $user->id,
                    'amount' => (float) $request->input('amount', 0),
                ]);
                return response()->json(['errors' => ['amount' => [$e->getMessage()]]], 422);
            }
            Log::error('Naira withdrawal processing failed.', [
                'user_id' => $user->id,
                'amount' => (float) $request->input('amount', 0),
                'exception' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Withdrawal failed: ' . $e->getMessage()], 500);
        }
    }

    public function approveWithdrawal(Request $request, $id)
    {
        try {
            $changed = app(BalanceSettlementService::class)->approveWithdrawal((int) $id);
            if (!$changed) {
                return response()->json(['error' => 'Withdrawal is no longer pending.'], 409);
            }
            $withdrawal = Withdrawal::with('user')->findOrFail($id);
            $user = $withdrawal->user;

            app(\App\Services\WithdrawalNotificationService::class)->approved($withdrawal);

            try {
                app(AdminTradeAlertService::class)->sendStatusChangeAlert('withdrawal', [
                    'reference' => $withdrawal->reference,
                    'user_name' => $user->name ?? 'N/A',
                    'old_status' => 'pending',
                    'new_status' => $withdrawal->status,
                ]);
            } catch (\Throwable $alertEx) {
                Log::warning('Withdrawal approval admin alert failed.', ['withdrawal_id' => $withdrawal->id, 'error' => $alertEx->getMessage()]);
            }

            Log::info('Withdrawal approved: ', $withdrawal->toArray());

            // ── Auto-payout after commit ────────────────────────────────
            try {
                $this->attemptAutoPayout($withdrawal, $user);
            } catch (\Throwable $payoutEx) {
                Log::error('Withdrawal was approved, but the auto-payout follow-up failed.', [
                    'withdrawal_id' => $withdrawal->id,
                    'error' => $payoutEx->getMessage(),
                ]);
            }

            return response()->json(['message' => 'Transaction approved, funds sent.']);
        } catch (\Exception $e) {
            Log::error('Withdrawal approval failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'withdrawal_id' => $id,
            ]);
            $statusCode = str_contains($e->getMessage(), 'Insufficient') ? 422 : 500;
            return response()->json(['error' => 'Approval failed: ' . $e->getMessage()], $statusCode);
        }
    }

    /**
     * Attempt to auto-payout via the configured gateway after approval.
     * Runs outside the main DB transaction so a payout failure never rolls back the approval.
     */
    protected function attemptAutoPayout(Withdrawal $withdrawal, User $user): void
    {
        // Check master switch
        if (SiteContent::get('auto_payout_enabled', '0') !== '1') {
            return;
        }

        $gateway  = SiteContent::get('auto_payout_gateway', 'paystack'); // 'paystack' | 'opay'
        $gatewayEnabled = SiteContent::get("auto_payout_{$gateway}_enabled", '0');
        if ($gatewayEnabled !== '1') {
            Log::info("[AutoPayout] Gateway '{$gateway}' disabled for withdrawal #{$withdrawal->id}");
            return;
        }

        $bd = (array) ($withdrawal->bank_account ?? []);

        if (empty($bd['account_number']) || empty($bd['bank_code'])) {
            Log::warning("[AutoPayout] Missing bank_code for withdrawal #{$withdrawal->id} — skipping auto-payout");
            return;
        }

        $payoutService = app(PayoutService::class);
        $amount        = (float) $withdrawal->amount;
        $reference     = $withdrawal->reference . '-PAY';

        try {
            if ($gateway === 'paystack') {
                $result = $payoutService->payoutViaPaystack($bd, $amount, $reference);
            } elseif ($gateway === 'opay') {
                $result = $payoutService->payoutViaOpay($bd, $amount, $reference, $user->name);
            } else {
                Log::warning("[AutoPayout] Unknown gateway '{$gateway}'");
                return;
            }

            $withdrawal->payout_gateway       = $gateway;
            $withdrawal->payout_reference     = $result['reference'] ?? $reference;
            $withdrawal->payout_status        = $result['success'] ? ($result['payout_status'] ?? 'pending') : 'failed';
            $withdrawal->payout_recipient_code = $result['recipient_code'] ?? null;
            $withdrawal->payout_response      = json_encode($result);
            $withdrawal->save();

            if ($result['success']) {
                Log::info("[AutoPayout] {$gateway} payout initiated for withdrawal #{$withdrawal->id}", $result);
            } else {
                Log::error("[AutoPayout] {$gateway} payout failed for withdrawal #{$withdrawal->id}: " . ($result['error'] ?? 'Unknown'));
            }
        } catch (\Throwable $e) {
            Log::error("[AutoPayout] Exception for withdrawal #{$withdrawal->id}: " . $e->getMessage());
        }
    }

    /**
     * Admin: manually retry a failed auto-payout.
     */
    public function retryPayout(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        $user       = User::findOrFail($withdrawal->user_id);

        if ($withdrawal->status !== 'approved') {
            return response()->json(['error' => 'Withdrawal must be approved first.'], 400);
        }

        $this->attemptAutoPayout($withdrawal, $user);

        return response()->json(['message' => 'Payout retry triggered.', 'payout_status' => $withdrawal->fresh()->payout_status]);
    }

    /**
     * Admin: toggle auto-payout settings.
     */
    public function toggleAutoPayout(Request $request)
    {
        $request->validate([
            'key'   => 'required|in:auto_payout_enabled,auto_payout_paystack_enabled,auto_payout_opay_enabled,auto_payout_gateway',
            'value' => 'required|string|max:50',
        ]);

        SiteContent::updateOrCreate(
            ['key' => $request->key],
            [
                'group' => 'auto_payout',
                'label' => ucwords(str_replace('_', ' ', $request->key)),
                'value' => $request->value,
            ]
        );

        return response()->json(['success' => true, 'key' => $request->key, 'value' => $request->value]);
    }

    /**
     * Payout/Transfer webhook handler (Paystack transfer.* events / OPay disbursement callbacks).
     */
    public function payoutWebhook(Request $request, string $gateway)
    {
        $rawBody = $request->getContent();
        $payoutService = app(PayoutService::class);

        if ($gateway === 'paystack') {
            // Verify HMAC-SHA512 signature
            $secret    = config('services.paystack.secret_key');
            $signature = $request->header('x-paystack-signature', '');
            $expected  = hash_hmac('sha512', $rawBody, $secret);
            if (!hash_equals($expected, $signature)) {
                Log::warning('[PayoutWebhook] Paystack signature mismatch');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $payload = $request->json()->all();
            $parsed  = $payoutService->parsePaystackTransferWebhook($payload);

            if (!$parsed['handled']) {
                return response()->json(['message' => 'Event ignored'], 200);
            }

            // Find withdrawal by payout_reference
            $withdrawal = Withdrawal::where('payout_reference', $parsed['reference'])->first();
            if (!$withdrawal) {
                Log::warning('[PayoutWebhook] Paystack: no withdrawal found for reference ' . $parsed['reference']);
                return response()->json(['message' => 'Not found'], 200);
            }

            $withdrawal->payout_status   = $parsed['status']; // 'success'|'failed'|'reversed'
            $withdrawal->payout_response = json_encode($payload);
            $withdrawal->save();

            Log::info("[PayoutWebhook] Paystack transfer {$parsed['status']} for withdrawal #{$withdrawal->id}");

            if ($parsed['status'] === 'failed' || $parsed['status'] === 'reversed') {
                Log::error("[PayoutWebhook] Payout {$parsed['status']} for withdrawal #{$withdrawal->id} — manual review required");
            }

            return response()->json(['message' => 'OK'], 200);
        }

        if ($gateway === 'opay') {
            $signature = $request->header('Sign', '');
            if (!$payoutService->verifyOpayPayoutWebhookSignature($rawBody, $signature)) {
                Log::warning('[PayoutWebhook] OPay signature mismatch');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $payload   = $request->json()->all();
            $reference = $payload['reference'] ?? ($payload['data']['reference'] ?? null);
            $status    = strtolower($payload['status'] ?? ($payload['data']['status'] ?? ''));

            if (!$reference) {
                return response()->json(['message' => 'No reference'], 200);
            }

            $withdrawal = Withdrawal::where('payout_reference', $reference)->first();
            if (!$withdrawal) {
                return response()->json(['message' => 'Not found'], 200);
            }

            $opayStatus = match ($status) {
                'success', 'successful' => 'success',
                'fail', 'failed'        => 'failed',
                default                 => 'pending',
            };

            $withdrawal->payout_status   = $opayStatus;
            $withdrawal->payout_response = json_encode($payload);
            $withdrawal->save();

            Log::info("[PayoutWebhook] OPay transfer {$opayStatus} for withdrawal #{$withdrawal->id}");
            return response()->json(['message' => 'OK'], 200);
        }

        return response()->json(['error' => 'Unknown gateway'], 400);
    }

    public function cancelWithdrawal(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        $user = User::findOrFail($withdrawal->user_id);

        if ($withdrawal->status !== 'pending') {
            Log::warning('Withdrawal cancellation failed: Not in pending status', ['withdrawal_id' => $id]);
            return response()->json(['error' => 'Withdrawal is not pending.'], 400);
        }

        try {
            $changed = app(BalanceSettlementService::class)->setWithdrawalStatus((int) $id, 'cancelled');
            if (!$changed) {
                return response()->json(['error' => 'Withdrawal is no longer pending.'], 409);
            }
            $withdrawal->refresh();

            // Send Telegram notification for cancellation
            $bankDetails = $withdrawal->bank_account;
            $this->sendTelegramNotification($user, $withdrawal, $bankDetails, false);

            // Send withdrawal cancelled email
            try {
                $bd = is_array($bankDetails) ? $bankDetails : (json_decode($bankDetails, true) ?? []);
                $accountDetails = ($bd['account_name'] ?? 'N/A') . ' — ' . ($bd['account_number'] ?? 'N/A') . ' (' . ($bd['bank_name'] ?? 'N/A') . ')';
                Mail::to($user->email)->send(new TradeNotification(
                    user: $user,
                    templateKey: 'withdrawal_cancelled',
                    data: [
                        'amount'          => number_format((float)($withdrawal->amount ?? 0), 2),
                        'payment_method'  => 'Bank Transfer',
                        'account_details' => $accountDetails,
                        'reason'          => 'Please contact support for more details.',
                    ],
                    badge: ['text' => 'Withdrawal Cancelled', 'color' => '#dc3545'],
                    ctaUrl: url('/dashboard'),
                    ctaText: 'Go to Dashboard',
                ));
            } catch (\Exception $mailEx) {
                Log::warning('Withdrawal cancelled email failed: ' . $mailEx->getMessage());
            }

            Log::info('Withdrawal cancelled: ', $withdrawal->toArray());

            return response()->json(['message' => 'Withdrawal cancelled successfully.']);
        } catch (\Exception $e) {
            Log::error('Withdrawal cancellation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'withdrawal_id' => $id,
            ]);
            return response()->json(['error' => 'Cancellation failed: ' . $e->getMessage()], 500);
        }
    }

    public function summary($id)
    {
        $withdrawal = Withdrawal::where('user_id', Auth::id())->findOrFail($id);
        return view('withdraw.summary', [
            'withdrawal' => $withdrawal,
            'bank_details' => $withdrawal->bank_account,
        ]);
    }

    public function success($id)
    {
        $withdrawal = Withdrawal::where('user_id', Auth::id())->findOrFail($id);
        return view('withdraw.success', [
            'withdrawal' => $withdrawal,
            'bank_details' => $withdrawal->bank_account,
        ]);
    }

    public function listWithdrawals()
    {
        $withdrawals = Withdrawal::with('user')->latest()->get();
        $autoPayoutSettings = [
            'auto_payout_enabled'          => SiteContent::get('auto_payout_enabled', '0'),
            'auto_payout_gateway'          => SiteContent::get('auto_payout_gateway', 'paystack'),
            'auto_payout_paystack_enabled' => SiteContent::get('auto_payout_paystack_enabled', '0'),
            'auto_payout_opay_enabled'     => SiteContent::get('auto_payout_opay_enabled', '0'),
        ];
        return view('admin.withdrawals', compact('withdrawals', 'autoPayoutSettings'));
    }

    protected function sendTelegramNotification($user, $withdrawal, $bankDetails, $isApproval = false)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            Log::warning('Telegram notification not sent: Missing bot token or chat ID.', [
                'bot_token' => $botToken ? 'set' : 'missing',
                'chat_id' => $chatId ? 'set' : 'missing',
            ]);
            return;
        }

        $action = $isApproval ? 'Approved Withdrawal' : ($withdrawal->status === 'cancelled' ? 'Cancelled Withdrawal' : 'New Withdrawal Request');
        $message = "{$action}\n" .
                   "User: {$user->name} (ID: {$user->id})\n" .
                   "Amount: ₦" . number_format((float)($withdrawal->amount ?? 0), 2) . "\n" .
                   "Bank: {$bankDetails['bank_name']}\n" .
                   "Account Number: {$bankDetails['account_number']}\n" .
                   "Account Name: {$bankDetails['account_name']}\n" .
                   "Reference: {$withdrawal->reference}\n" .
                   "Status: {$withdrawal->status}\n" .
                   "Submitted: {$withdrawal->created_at->format('Y-m-d H:i:s')}";

        // Retry logic: attempt up to 3 times
        $maxRetries = 3;
        $retryDelay = 2; // seconds
        $attempt = 1;

        while ($attempt <= $maxRetries) {
            try {
                Log::debug('Attempting to send Telegram notification', [
                    'attempt' => $attempt,
                    'bot_token' => substr($botToken, 0, 10) . '...',
                    'chat_id' => $chatId,
                    'message' => $message,
                ]);

                $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                ]);

                if ($response->successful()) {
                    Log::info('Telegram notification sent successfully.', [
                        'attempt' => $attempt,
                        'message' => $message,
                        'response' => $response->json(),
                    ]);
                    return;
                } else {
                    Log::warning('Telegram notification attempt failed.', [
                        'attempt' => $attempt,
                        'status' => $response->status(),
                        'response' => $response->json(),
                        'bot_token' => substr($botToken, 0, 10) . '...',
                        'chat_id' => $chatId,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Telegram notification error on attempt ' . $attempt . ': ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'bot_token' => substr($botToken, 0, 10) . '...',
                    'chat_id' => $chatId,
                ]);
            }

            if ($attempt < $maxRetries) {
                Log::info('Retrying Telegram notification in ' . $retryDelay . ' seconds...', [
                    'attempt' => $attempt,
                ]);
                sleep($retryDelay);
            }
            $attempt++;
        }

        Log::error('All Telegram notification attempts failed after ' . $maxRetries . ' tries.', [
            'bot_token' => substr($botToken, 0, 10) . '...',
            'chat_id' => $chatId,
            'message' => $message,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $withdrawal = Withdrawal::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                // Support both legacy UI/API values and current ones
                'status' => 'required|in:pending,approved,cancelled,successful,canceled'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $oldStatus = $withdrawal->status;
            $changed = app(BalanceSettlementService::class)
                ->setWithdrawalStatus((int) $withdrawal->id, $request->status);
            if (!$changed) {
                return back()->with('info', 'This withdrawal has already been processed.');
            }
            $withdrawal->refresh();

            // Log the status change
            Log::info('Withdrawal status updated', [
                'withdrawal_id' => $withdrawal->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'admin_user' => Auth::user()->id
            ]);

            // Send notification to user if needed
            $isApprovedNow = in_array($request->status, ['approved', 'successful'], true);
            $isCancelledNow = in_array($request->status, ['cancelled', 'canceled'], true);

            if (
                $withdrawal->user &&
                ($isApprovedNow || $isCancelledNow) &&
                $oldStatus !== $request->status
            ) {
                $wUser = $withdrawal->user;
                if ($isApprovedNow) {
                    app(\App\Services\WithdrawalNotificationService::class)->approved($withdrawal);
                } else {
                    $bd = is_array($withdrawal->bank_account)
                        ? $withdrawal->bank_account
                        : (json_decode((string) $withdrawal->bank_account, true) ?: []);
                    $accountDetails = ($bd['account_name'] ?? 'N/A') . ' — '
                        . ($bd['account_number'] ?? 'N/A') . ' (' . ($bd['bank_name'] ?? 'N/A') . ')';

                    try {
                        Mail::to($wUser->email)->send(new TradeNotification(
                            user: $wUser,
                            templateKey: 'withdrawal_cancelled',
                            data: [
                                'amount' => number_format((float) $withdrawal->amount, 2),
                                'payment_method' => 'Bank Transfer',
                                'account_details' => $accountDetails,
                                'reason' => 'Please contact support for more details.',
                            ],
                            badge: ['text' => 'Withdrawal Cancelled', 'color' => '#dc3545'],
                            ctaUrl: url('/dashboard'),
                            ctaText: 'Go to Dashboard',
                        ));
                    } catch (\Throwable $mailEx) {
                        Log::warning('Withdrawal updateStatus email failed: ' . $mailEx->getMessage());
                    }

                    if ($wUser->telegram_chat_id) {
                        try {
                            $this->sendTelegramNotification($wUser, $withdrawal, $bd, false);
                        } catch (\Throwable $notifyEx) {
                            Log::warning('Withdrawal cancellation Telegram notification failed.', ['withdrawal_id' => $withdrawal->id, 'error' => $notifyEx->getMessage()]);
                        }
                    }
                }

                // Standardized admin status-change alert
                try {
                    app(AdminTradeAlertService::class)->sendStatusChangeAlert('withdrawal', [
                        'reference' => $withdrawal->reference,
                        'user_name' => $wUser->name ?? 'N/A',
                        'old_status' => $oldStatus,
                        'new_status' => $request->status,
                    ]);
                } catch (\Throwable $alertEx) {
                    Log::warning('Withdrawal status admin alert failed: ' . $alertEx->getMessage());
                }
            }

            if ($request->status === 'approved' && $oldStatus === 'pending' && $withdrawal->user) {
                try {
                    $this->attemptAutoPayout($withdrawal, $withdrawal->user);
                } catch (\Throwable $payoutEx) {
                    Log::error('Withdrawal was approved, but the auto-payout follow-up failed.', [
                        'withdrawal_id' => $withdrawal->id,
                        'error' => $payoutEx->getMessage(),
                    ]);
                }
            }

            return back()->with('success', 'Withdrawal status updated successfully.');
            
        } catch (\Exception $e) {
            Log::error('Error updating withdrawal status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update withdrawal status.');
        }
    }

    /**
     * User cancels their own pending withdrawal (no balance deduction yet, so no refund needed).
     */
    public function userCancelWithdrawal(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json(['error' => 'Only pending withdrawals can be cancelled.'], 422);
        }

        $changed = app(BalanceSettlementService::class)->setWithdrawalStatus((int) $withdrawal->id, 'cancelled');
        if (!$changed) {
            return response()->json(['error' => 'This withdrawal has already been processed.'], 409);
        }
        $withdrawal->refresh();

        // Notify user by email
        try {
            $bd = (array) ($withdrawal->bank_account ?? []);
            $accountDetails = ($bd['account_name'] ?? 'N/A') . ' — ' . ($bd['account_number'] ?? 'N/A') . ' (' . ($bd['bank_name'] ?? 'N/A') . ')';
            Mail::to(Auth::user()->email)->send(new TradeNotification(
                user: Auth::user(),
                templateKey: 'withdrawal_cancelled',
                data: [
                    'amount'          => number_format((float) ($withdrawal->amount ?? 0), 2),
                    'payment_method'  => 'Bank Transfer',
                    'account_details' => $accountDetails,
                    'reason'          => 'You cancelled this withdrawal.',
                ],
                badge: ['text' => 'Withdrawal Cancelled', 'color' => '#6b7280'],
                ctaUrl: url('/dashboard'),
                ctaText: 'Go to Dashboard',
            ));
        } catch (\Exception $e) {
            Log::warning('Cancel withdrawal email failed: ' . $e->getMessage());
        }

        Log::info('Withdrawal cancelled by user', ['withdrawal_id' => $withdrawal->id, 'user_id' => Auth::id()]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Withdrawal cancelled successfully.']);
        }
        return redirect()->route('dashboard')->with('success', 'Withdrawal cancelled successfully.');
    }
}
