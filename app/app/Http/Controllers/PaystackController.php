<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackController extends Controller
{
    public function getBanks()
    {
        $paystackKey = config('paystack.secret_key');
        if (empty($paystackKey) || !str_starts_with($paystackKey, 'sk_')) {
            Log::error('Paystack Secret Key is missing or invalid for getBanks', [
                'key' => $paystackKey ? substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4) : 'null',
            ]);
            return response()->json(['status' => false, 'message' => 'Server configuration error'], 500);
        }

        Log::info('Paystack Secret Key used for getBanks (masked):', [
            'key' => substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4),
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $paystackKey,
        ])->withOptions([
            'verify' => false, // Temporary for local testing; remove in production
        ])->get('https://api.paystack.co/bank?country=nigeria&enabled_for_verification=true');

        Log::info('Paystack Banks Response:', [
            'response' => $response->json(),
            'http_status' => $response->status(),
            'authorization_header' => 'Bearer ' . substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4),
        ]);

        return $response->json();
    }

    public function resolveAccount(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|size:10',
            'bank_code' => 'required|string',
        ]);

        $paystackKey = config('paystack.secret_key');
        if (empty($paystackKey) || !str_starts_with($paystackKey, 'sk_')) {
            Log::error('Paystack Secret Key is missing or invalid for resolveAccount', [
                'key' => $paystackKey ? substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4) : 'null',
            ]);
            return response()->json(['status' => false, 'message' => 'Server configuration error'], 500);
        }

        Log::info('Paystack Secret Key used for resolveAccount (masked):', [
            'key' => substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4),
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $paystackKey,
        ])->withOptions([
            'verify' => false, // Temporary for local testing; remove in production
        ])->get('https://api.paystack.co/bank/resolve', [
            'account_number' => $request->account_number,
            'bank_code' => $request->bank_code,
        ]);

        Log::info('Paystack Resolve Account Request:', [
            'account_number' => $request->account_number,
            'bank_code' => $request->bank_code,
            'response' => $response->json(),
            'http_status' => $response->status(),
            'authorization_header' => 'Bearer ' . substr($paystackKey, 0, 8) . '...' . substr($paystackKey, -4),
        ]);

        return $response->json();
    }
}