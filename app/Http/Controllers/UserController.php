<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function settings()
    {
        return view('settings.index');
    }

    public function editBank()
    {
        $user = Auth::user();
        return view('settings.edit-bank', compact('user'));
    }

    public function updateBank(Request $request)
    {
        // Validate the request
        $request->validate([
            'bank_code' => 'required|string',
            'account_number' => 'required|string|size:10',
            'account_name' => 'required|string',
            'password' => 'required|string',
        ]);

        // Verify the user's password
        if (!Hash::check($request->password, Auth::user()->password)) {
            Log::error('Password verification failed for user ID: ' . Auth::id());
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        // Verify Paystack Secret Key
        $paystackKey = config('paystack.secret_key');
        if (empty($paystackKey) || !str_starts_with($paystackKey, 'sk_')) {
            Log::error('Paystack Secret Key is missing or invalid', [
                'key' => $paystackKey ? substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4) : 'null',
            ]);
            return back()->withErrors(['account_number' => 'Server configuration error. Please contact support.']);
        }

        Log::info('Paystack Secret Key used (masked):', [
            'key' => substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4),
        ]);

        // Verify account number with Paystack
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $paystackKey,
        ])->withOptions([
            'verify' => false, // Temporary for local testing; remove in production
        ])->get('https://api.paystack.co/bank/resolve', [
            'account_number' => $request->account_number,
            'bank_code' => $request->bank_code,
        ]);

        Log::info('Paystack Resolve Request:', [
            'account_number' => $request->account_number,
            'bank_code' => $request->bank_code,
            'form_account_name' => $request->account_name,
            'response' => $response->json(),
            'http_status' => $response->status(),
            'authorization_header' => 'Bearer ' . substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4),
        ]);

        if (!$response->successful()) {
            Log::error('Paystack API request failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            $errorMessage = $response->json('message') ?: 'Unknown error';
            if ($response->status() === 401) {
                $errorMessage = 'Authentication error with payment provider. Please contact support.';
            }
            return back()->withErrors(['account_number' => 'Failed to validate account: ' . $errorMessage]);
        }

        if (!$response->json('status')) {
            Log::error('Paystack account validation failed', [
                'message' => $response->json('message'),
                'response' => $response->json(),
            ]);
            return back()->withErrors(['account_number' => 'Invalid account number or bank: ' . ($response->json('message') ?: 'Unknown error')]);
        }

        $paystackAccountName = $response->json('data.account_name');
        $formAccountName = $request->account_name;

        if (strtolower(trim($paystackAccountName)) !== strtolower(trim($formAccountName))) {
            Log::error('Account name mismatch', [
                'paystack_account_name' => $paystackAccountName,
                'form_account_name' => $formAccountName,
                'normalized_paystack' => strtolower(trim($paystackAccountName)),
                'normalized_form' => strtolower(trim($formAccountName)),
            ]);
            return back()->withErrors(['account_number' => 'Account name does not match bank records.']);
        }

        // Update user's bank details
        $user = Auth::user();
        $user->update([
            'bank_code' => $request->bank_code,
            'account_number' => $request->account_number,
            'account_name' => $paystackAccountName,
        ]);

        Log::info('Bank details updated for user ID: ' . Auth::id());
        return redirect()->route('edit.bank')->with('success', 'Bank details updated successfully!');
    }

    public function changePasswordForm()
    {
        return view('settings.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }
}