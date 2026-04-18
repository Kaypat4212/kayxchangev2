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
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\AdminTradeAlertService;
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

    public function withdraw()
    {
        $user = Auth::user();
        return view('withdraw.form', [
            'balance' => $user->balance ?? 0,
            'minimum_withdrawal' => config('withdrawal.minimum', 10),
            'bank_details' => [
                'bank_name' => $user->bank_name ?? 'N/A',
                'account_number' => $user->account_number ?? 'N/A',
                'account_name' => $user->account_name ?? 'N/A',
            ],
        ]);
    }

    public function processWithdrawal(Request $request)
    {
        $user = Auth::user();

        Log::debug('Processing withdrawal request', ['request' => $request->all()]);

        // Validation rules with custom messages
        $validator = Validator::make($request->all(), [
            'amount' => [
                'required',
                'numeric',
                'min:' . config('withdrawal.minimum', 10),
                'max:' . ($user->balance ?? 0),
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
            'amount.max' => 'Insufficient balance.',
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
            // Create withdrawal record
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'bank_account' => json_encode($bankDetails),
                'status' => 'pending',
                'currency' => 'NGN',
                'reference' => 'WDRW-' . Str::random(8),
            ]);

            // Send Telegram notification
            $this->sendTelegramNotification($user, $withdrawal, $bankDetails);

            // Standardized admin alert + in-app badge notification
            try {
                app(AdminTradeAlertService::class)->sendTriggeredAlert('withdrawal', [
                    'user_id' => $user->id,
                    'reference' => $withdrawal->reference,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'coin' => 'NGN',
                    'usd_amount' => 'N/A',
                    'naira_amount' => number_format((float) $withdrawal->amount, 2),
                    'wallet_address' => 'N/A',
                    'network' => 'Bank Transfer',
                    'status' => $withdrawal->status,
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
                'success' => true,
                'withdrawal_id' => $withdrawal->id,
                'bank_option' => $request->bank_option,
                'bank_details' => $bankDetails,
                'redirect' => route('withdraw.success', $withdrawal->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'request' => $request->all(),
            ]);
            return response()->json(['error' => 'Withdrawal failed: ' . $e->getMessage()], 500);
        }
    }

    public function approveWithdrawal(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        $user = User::findOrFail($withdrawal->user_id);

        if ($withdrawal->status !== 'pending') {
            Log::warning('Withdrawal approval failed: Not in pending status', ['withdrawal_id' => $id]);
            return response()->json(['error' => 'Withdrawal is not pending.'], 400);
        }

        DB::beginTransaction();
        try {
            if ($user->balance < $withdrawal->amount) {
                throw new \Exception('Insufficient balance.');
            }

            // Deduct balance
            $user->balance -= $withdrawal->amount;
            $user->save();

            // Update withdrawal status and processed_at
            $withdrawal->status = 'approved';
            $withdrawal->processed_at = now();
            $withdrawal->save();

            // Send Telegram notification for approval
            $bankDetails = $withdrawal->bank_account;
            $this->sendTelegramNotification($user, $withdrawal, $bankDetails, true);

            // Send withdrawal approved email
            try {
                $bd = is_array($bankDetails) ? $bankDetails : (json_decode($bankDetails, true) ?? []);
                $accountDetails = ($bd['account_name'] ?? 'N/A') . ' — ' . ($bd['account_number'] ?? 'N/A') . ' (' . ($bd['bank_name'] ?? 'N/A') . ')';
                Mail::to($user->email)->send(new TradeNotification(
                    user: $user,
                    templateKey: 'withdrawal_approved',
                    data: [
                        'amount'          => number_format((float)($withdrawal->amount ?? 0), 2),
                        'payment_method'  => 'Bank Transfer',
                        'account_details' => $accountDetails,
                    ],
                    badge: ['text' => 'Payment Sent', 'color' => '#00cc00'],
                    ctaUrl: url('/dashboard'),
                    ctaText: 'Go to Dashboard',
                ));
            } catch (\Exception $mailEx) {
                Log::warning('Withdrawal approved email failed: ' . $mailEx->getMessage());
            }

            DB::commit();
            Log::info('Withdrawal approved: ', $withdrawal->toArray());

            return response()->json(['message' => 'Transaction approved, funds sent.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal approval failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'withdrawal_id' => $id,
            ]);
            return response()->json(['error' => 'Approval failed: ' . $e->getMessage()], 500);
        }
    }

    public function cancelWithdrawal(Request $request, $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        $user = User::findOrFail($withdrawal->user_id);

        if ($withdrawal->status !== 'pending') {
            Log::warning('Withdrawal cancellation failed: Not in pending status', ['withdrawal_id' => $id]);
            return response()->json(['error' => 'Withdrawal is not pending.'], 400);
        }

        DB::beginTransaction();
        try {
            // Update withdrawal status
            $withdrawal->status = 'cancelled';
            $withdrawal->processed_at = now();
            $withdrawal->save();

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

            DB::commit();
            Log::info('Withdrawal cancelled: ', $withdrawal->toArray());

            return response()->json(['message' => 'Withdrawal cancelled successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
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
        return view('admin.withdrawals', compact('withdrawals'));
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
                'status' => 'required|in:pending,successful,canceled'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $oldStatus = $withdrawal->status;
            $withdrawal->status = $request->status;
            $withdrawal->save();

            // Log the status change
            Log::info('Withdrawal status updated', [
                'withdrawal_id' => $withdrawal->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'admin_user' => Auth::user()->id
            ]);

            // Send notification to user if needed
            if ($withdrawal->user && in_array($request->status, ['successful', 'canceled']) && $oldStatus !== $request->status) {
                $wUser = $withdrawal->user;
                $bd = is_array($withdrawal->bank_account)
                    ? $withdrawal->bank_account
                    : (json_decode($withdrawal->bank_account, true) ?? []);
                $accountDetails = ($bd['account_name'] ?? 'N/A') . ' — '
                    . ($bd['account_number'] ?? 'N/A') . ' (' . ($bd['bank_name'] ?? 'N/A') . ')';
                try {
                    Mail::to($wUser->email)->send(new TradeNotification(
                        user: $wUser,
                        templateKey: $request->status === 'successful' ? 'withdrawal_approved' : 'withdrawal_cancelled',
                        data: [
                            'amount'          => number_format((float)$withdrawal->amount, 2),
                            'payment_method'  => 'Bank Transfer',
                            'account_details' => $accountDetails,
                            'reason'          => 'Please contact support for more details.',
                        ],
                        badge: [
                            'text'  => $request->status === 'successful' ? 'Payment Sent' : 'Withdrawal Cancelled',
                            'color' => $request->status === 'successful' ? '#00cc00' : '#dc3545',
                        ],
                        ctaUrl: url('/dashboard'),
                        ctaText: 'Go to Dashboard',
                    ));
                } catch (\Exception $mailEx) {
                    Log::warning('Withdrawal updateStatus email failed: ' . $mailEx->getMessage());
                }
                // Also send Telegram if chat ID set
                if ($wUser->telegram_chat_id) {
                    $this->sendTelegramNotification($wUser, $withdrawal, $bd, $request->status === 'successful');
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

            return back()->with('success', 'Withdrawal status updated successfully.');
            
        } catch (\Exception $e) {
            Log::error('Error updating withdrawal status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update withdrawal status.');
        }
    }
}