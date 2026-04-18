<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class EditBankController extends Controller
{
    public function showEditBankForm()
    {
        $user = Auth::user();
        $bankDetailsSet = !empty($user->bank_name) && !empty($user->bank_code) && 
                         !empty($user->account_name) && !empty($user->account_number);

        return view('settings.edit-bank', [
            'user' => $user,
            'bankDetailsSet' => $bankDetailsSet
        ]);
    }

    public function updateBankDetails(Request $request)
    {
        $user = Auth::user();

        // Check if bank details are already set
        $bankDetailsSet = !empty($user->bank_name) && !empty($user->bank_code) && 
                         !empty($user->account_name) && !empty($user->account_number);

        if ($bankDetailsSet) {
            return redirect()->back()->with('error', 'Bank details can only be set once.');
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'bank_code' => 'required|string|max:50',
            'account_number' => 'required|string|size:10',
            'account_name' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withErrors(['password' => 'Incorrect password.'])
                ->withInput();
        }

        // Verify account details with Paystack
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret'),
            'Cache-Control' => 'no-cache',
        ])->get('https://api.paystack.co/bank/resolve', [
            'account_number' => $request->account_number,
            'bank_code' => $request->bank_code,
        ]);

        if (!$response->successful() || !$response->json('status')) {
            return redirect()->back()
                ->withErrors(['account_number' => 'Invalid account number or bank: ' . ($response->json('message') ?? 'Unknown error')])
                ->withInput();
        }

        // Fetch bank name for the selected bank_code
        $bankName = $this->getBankName($request->bank_code);
        if (!$bankName) {
            return redirect()->back()
                ->withErrors(['bank_code' => 'Unable to retrieve bank name. Please try again.'])
                ->withInput();
        }

        // Update user with bank details
        try {
            $user->update([
                'bank_name' => $bankName,
                'bank_code' => $request->bank_code,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update bank details: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to save bank details. Please try again.'])
                ->withInput();
        }

        return redirect()->route('edit-bank')->with('success', 'Bank details added successfully.');
    }

    public function getPaystackBanks()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret'),
            'Cache-Control' => 'no-cache',
        ])->get('https://api.paystack.co/bank', [
            'country' => 'nigeria',
        ]);

        if (!$response->successful() || !$response->json('status')) {
            \Log::error('Paystack banks API failed: ' . ($response->json('message') ?? 'Unknown error'));
            return response()->json(['status' => false, 'message' => 'Failed to fetch banks'], 500);
        }

        return response()->json($response->json());
    }

    public function resolvePaystackAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|string|size:10',
            'bank_code' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret'),
            'Cache-Control' => 'no-cache',
        ])->get('https://api.paystack.co/bank/resolve', [
            'account_number' => $request->account_number,
            'bank_code' => $request->bank_code,
        ]);

        if (!$response->successful() || !$response->json('status')) {
            \Log::error('Paystack resolve account failed: ' . ($response->json('message') ?? 'Unknown error'));
            return response()->json(['status' => false, 'message' => 'Invalid account details'], 400);
        }

        return response()->json($response->json());
    }

    private function getBankName($bankCode)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret'),
            'Cache-Control' => 'no-cache',
        ])->get('https://api.paystack.co/bank', [
            'country' => 'nigeria',
        ]);

        if ($response->successful() && $response->json('status')) {
            foreach ($response->json('data') as $bank) {
                if ($bank['code'] === $bankCode) {
                    return $bank['name'];
                }
            }
        }

        \Log::error('Failed to fetch bank name for code: ' . $bankCode);
        return null;
    }
}