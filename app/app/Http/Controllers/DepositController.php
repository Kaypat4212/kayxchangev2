<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\CompanyAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\DepositApproved;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        $validSorts = ['amount', 'status', 'transaction_ref', 'created_at'];
        $sort = in_array($sort, $validSorts) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        $deposits = Deposit::orderBy($sort, $direction)->paginate(10);

        Log::info('Deposits Loaded', [
            'count' => $deposits->count(),
            'total' => $deposits->total(),
            'current_page' => $deposits->currentPage(),
            'sort' => $sort,
            'direction' => $direction,
            'class' => get_class($deposits)
        ]);

        return view('deposits.index', compact('deposits'));
    }

    public function create()
    {
        $companyAccounts = CompanyAccount::all();
        Log::info('Company Accounts Loaded', ['count' => $companyAccounts->count()]);
        return view('deposits.create', compact('companyAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:bank_transfer',
            'company_account_id' => 'required_if:payment_method,bank_transfer|exists:company_accounts,id',
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // Increased to 10MB
        ]);

        try {
            $deposit = new Deposit();
            $deposit->amount = $request->amount;
            $deposit->status = 'pending';
            $deposit->transaction_ref = 'DEP-' . Str::upper(Str::random(10));
            $deposit->company_account_id = $request->company_account_id;
            $deposit->user_id = auth()->id();
            $deposit->payment_method = $request->payment_method;

            // Store proof of payment
            if ($request->hasFile('proof_of_payment') && $request->file('proof_of_payment')->isValid()) {
                $path = $request->file('proof_of_payment')->store('proofs', 'public');
                $deposit->proof_of_payment = $path;
                // Also set the 'proof' column for backward compatibility
                $deposit->proof = $path;
            }

            $deposit->save();

            // Send Telegram notification
            $this->sendTelegramAlert($deposit, true);

            return redirect()->route('deposits.index')->with('success', 'Deposit submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to process deposit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to submit deposit. Please try again.']);
        }
    }

    public function updateStatus(Request $request, Deposit $deposit)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string|max:255',
        ]);

        try {
            $deposit->status = $request->status;
            $deposit->admin_note = $request->admin_note;
            $deposit->save();

            // Send email notification if approved
            if ($request->status === 'approved' && $deposit->user && $deposit->user->email) {
                Mail::to($deposit->user->email)->send(new DepositApproved($deposit));
                Log::info('Deposit approval email sent', [
                    'deposit_id' => $deposit->id,
                    'user_email' => $deposit->user->email
                ]);
            }

            return redirect()->route('deposits.index')->with('success', 'Deposit status updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update deposit status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to update deposit status. Please try again.']);
        }
    }

    protected function sendTelegramAlert(Deposit $deposit, $includeProof = false)
    {
        $user = auth()->user();
        $token = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        Log::info('Attempting Telegram notification', [
            'deposit_id' => $deposit->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'token_set' => !empty($token) ? 'yes' : 'no',
            'chat_id_set' => !empty($chatId) ? 'yes' : 'no'
        ]);

        if (empty($token) || empty($chatId)) {
            Log::error('Telegram notification aborted: Missing token or chat_id', [
                'deposit_id' => $deposit->id,
                'user_id' => $user->id
            ]);
            return false;
        }

        $companyAccount = CompanyAccount::find($deposit->company_account_id);
        $message = "📥 *New Deposit Alert!*\n\n"
            . "*User:* {$user->name} ({$user->email})\n"
            . "*User ID:* {$user->id}\n"
            . "*Transaction Ref:* {$deposit->transaction_ref}\n"
            . "*Amount:* ₦" . number_format($deposit->amount, 2) . "\n"
            . "*Payment Method:* " . ucfirst(str_replace('_', ' ', $deposit->payment_method ?? 'bank_transfer')) . "\n"
            . "*Company Account:* " . ($companyAccount ? "{$companyAccount->account_name} ({$companyAccount->bank_name} - {$companyAccount->account_number})" : 'N/A') . "\n"
            . "*Status:* {$deposit->status}\n"
            . "*Submitted At:* " . $deposit->created_at->format('M d, Y H:i');

        if ($includeProof && $deposit->proof_of_payment) {
            $proofPath = $deposit->proof_of_payment;
            $proofUrl = asset('storage/' . $proofPath);
            if (Storage::disk('public')->exists($proofPath)) {
                $message .= "\n🧾 *Proof of Payment:* [View Image]({$proofUrl})";
                Log::info('Proof of payment URL included', [
                    'deposit_id' => $deposit->id,
                    'url' => $proofUrl,
                    'path' => $proofPath
                ]);
            } else {
                Log::warning('Proof of payment file not found', [
                    'deposit_id' => $deposit->id,
                    'path' => $proofPath
                ]);
                $message .= "\n🧾 *Proof of Payment:* (File not found)";
            }
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => false,
        ];

        Log::debug('Telegram payload', [
            'deposit_id' => $deposit->id,
            'url' => $url,
            'payload' => $payload
        ]);

        // Primary method: HTTP client
        try {
            $response = Http::withOptions([
                'verify' => 'C:\xampp\php\extras\ssl\cacert.pem',
                'timeout' => 15,
            ])->retry(3, 1000)->post($url, $payload);
            if ($response->successful()) {
                Log::info('Telegram notification sent successfully via Http', [
                    'deposit_id' => $deposit->id,
                    'chat_id' => $chatId,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Telegram notification failed via Http', [
                    'deposit_id' => $deposit->id,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed via Http: ' . $e->getMessage(), [
                'deposit_id' => $deposit->id,
                'exception' => $e->getTraceAsString()
            ]);
        }

        // Fallback: cURL
        try {
            Log::info('Attempting Telegram notification via cURL', [
                'deposit_id' => $deposit->id,
                'url' => $url
            ]);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, 'C:\xampp\php\extras\ssl\cacert.pem');
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && json_decode($result, true)['ok']) {
                Log::info('Telegram notification sent successfully via cURL', [
                    'deposit_id' => $deposit->id,
                    'chat_id' => $chatId,
                    'response' => $result
                ]);
                return true;
            } else {
                Log::error('Telegram notification failed via cURL', [
                    'deposit_id' => $deposit->id,
                    'http_code' => $httpCode,
                    'response' => $result,
                    'curl_error' => $curlError
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification failed via cURL: ' . $e->getMessage(), [
                'deposit_id' => $deposit->id,
                'exception' => $e->getTraceAsString()
            ]);
        }

        return false;
    }
}