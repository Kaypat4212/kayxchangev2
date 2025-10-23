<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Withdrawal;
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
            'external_account_number' => 'required_if:bank_option,external|string|max:50|nullable',
            'external_account_name' => 'required_if:bank_option,external|string|max:100|nullable',
        ], [
            'amount.required' => 'Please enter an amount.',
            'amount.min' => 'Amount must be at least ₦' . config('withdrawal.minimum', 10) . '.',
            'amount.max' => 'Insufficient balance.',
            'password.required' => 'Please enter your password.',
            'payment_method.required' => 'Please select a payment method.',
            'bank_option.required_if' => 'Please select a bank option.',
            'external_bank_name.required_if' => 'Please enter the bank name.',
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

            DB::commit();
            Log::info('Withdrawal created: ', $withdrawal->toArray());

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

            // Attempt to send email notification using PHPMailer with .env settings
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = config('mail.host', 'smtp.gmail.com');
                $mail->SMTPAuth = true;
                $mail->Username = config('mail.username', 'admin@kayxchange.net');
                $mail->Password = config('mail.password', 'gqzvjmmdqgkeelwk');
                $mail->SMTPSecure = config('mail.encryption', 'tls');
                $mail->Port = config('mail.port', 587);

                $mail->setFrom(config('mail.from.address', 'admin@kayxchange.net'), config('mail.from.name', 'Kayxchange'));
                $mail->addAddress($user->email);
                $mail->addReplyTo(config('mail.from.address', 'admin@kayxchange.net'), config('mail.from.name', 'Kayxchange'));

                $mail->isHTML(false);
                $mail->Subject = 'Your Withdrawal Request Has Been Approved';
                $mail->Body = "Dear {$user->name},\n\n" .
                              "We have approved your withdrawal request with the following details:\n\n" .
                              "- Amount: ₦" . number_format((float)($withdrawal->amount ?? 0), 2) . "\n" .
                              "- Bank: {$bankDetails['bank_name']}\n" .
                              "- Account Number: {$bankDetails['account_number']}\n" .
                              "- Account Name: {$bankDetails['account_name']}\n" .
                              "- Reference: {$withdrawal->reference}\n" .
                              "- Status: " . ucfirst($withdrawal->status) . "\n" .
                              "- Date: {$withdrawal->created_at->format('Y-m-d H:i:s')}\n\n" .
                              "Your funds will be processed to your account shortly.\n\n" .
                              "Thank you,\nKayxchange Team";

                $mail->send();
                Log::info('Email notification sent for withdrawal approval', [
                    'user_id' => $user->id,
                    'withdrawal_id' => $id,
                    'email' => $user->email,
                ]);
            } catch (Exception $e) {
                Log::warning('Failed to send email notification for withdrawal approval: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'withdrawal_id' => $id,
                    'email' => $user->email,
                ]);
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
            return response()->jsoqn(['error' => 'Approval failed: ' . $e->getMessage()], 500);
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

            // Attempt to send email notification using PHPMailer with .env settings
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = config('mail.host', 'smtp.gmail.com');
                $mail->SMTPAuth = true;
                $mail->Username = config('mail.username', 'admin@kayxchange.net');
                $mail->Password = config('mail.password', 'gqzvjmmdqgkeelwk');
                $mail->SMTPSecure = config('mail.encryption', 'tls');
                $mail->Port = config('mail.port', 587);

                $mail->setFrom(config('mail.from.address', 'admin@kayxchange.net'), config('mail.from.name', 'Kayxchange'));
                $mail->addAddress($user->email);
                $mail->addReplyTo(config('mail.from.address', 'admin@kayxchange.net'), config('mail.from.name', 'Kayxchange'));

                $mail->isHTML(false);
                $mail->Subject = 'Your Withdrawal Request Has Been Cancelled';
                $mail->Body = "Dear {$user->name},\n\n" .
                              "We have cancelled your withdrawal request with the following details:\n\n" .
                              "- Amount: ₦" . number_format((float)($withdrawal->amount ?? 0), 2) . "\n" .
                              "- Bank: {$bankDetails['bank_name']}\n" .
                              "- Account Number: {$bankDetails['account_number']}\n" .
                              "- Account Name: {$bankDetails['account_name']}\n" .
                              "- Reference: {$withdrawal->reference}\n" .
                              "- Status: " . ucfirst($withdrawal->status) . "\n" .
                              "- Date: {$withdrawal->created_at->format('Y-m-d H:i:s')}\n\n" .
                              "Please contact support if you have any questions or wish to submit a new withdrawal request.\n\n" .
                              "Thank you,\nKayxchange Team";

                $mail->send();
                Log::info('Email notification sent for withdrawal cancellation', [
                    'user_id' => $user->id,
                    'withdrawal_id' => $id,
                    'email' => $user->email,
                ]);
            } catch (Exception $e) {
                Log::warning('Failed to send email notification for withdrawal cancellation: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'withdrawal_id' => $id,
                    'email' => $user->email,
                ]);
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
            if ($request->status === 'successful' && $withdrawal->user && $withdrawal->user->telegram_chat_id) {
                $bankDetails = [
                    'bank_name' => $withdrawal->bank_name,
                    'account_number' => $withdrawal->account_number,
                    'account_name' => $withdrawal->account_name
                ];
                $this->sendTelegramNotification($withdrawal->user, $withdrawal, $bankDetails, true);
            } elseif ($request->status === 'canceled' && $withdrawal->user && $withdrawal->user->telegram_chat_id) {
                $bankDetails = [
                    'bank_name' => $withdrawal->bank_name,
                    'account_number' => $withdrawal->account_number,
                    'account_name' => $withdrawal->account_name
                ];
                $this->sendTelegramNotification($withdrawal->user, $withdrawal, $bankDetails, false);
            }

            return back()->with('success', 'Withdrawal status updated successfully.');
            
        } catch (\Exception $e) {
            Log::error('Error updating withdrawal status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update withdrawal status.');
        }
    }
}