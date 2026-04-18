<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BankVerificationController extends Controller
{
    private string $baseUrl = 'https://api.paystack.co';

    /**
     * Return list of Nigerian banks from Paystack (cached 1 hour).
     */
    public function banks(): JsonResponse
    {
        $banks = Cache::remember('paystack_banks_ngn', 3600, function () {
            $secret = config('paystack.secret_key');

            $response = Http::withToken($secret)
                ->timeout(15)
                ->get("{$this->baseUrl}/bank", [
                    'currency' => 'NGN',
                    'perPage'  => 200,
                    'use_cursor' => 'false',
                ]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json('data');

            // Sort alphabetically
            usort($data, fn($a, $b) => strcmp($a['name'], $b['name']));

            return array_map(fn($bank) => [
                'name' => $bank['name'],
                'code' => $bank['code'],
            ], $data);
        });

        if ($banks === null) {
            return response()->json(['success' => false, 'message' => 'Unable to load bank list. Please try again.'], 502);
        }

        return response()->json(['success' => true, 'banks' => $banks]);
    }

    /**
     * Verify a bank account number with Paystack.
     */
    public function verifyAccount(Request $request): JsonResponse
    {
        $request->validate([
            'account_number' => ['required', 'digits:10'],
            'bank_code'      => ['required', 'string', 'max:20'],
        ]);

        $accountNumber = $request->input('account_number');
        $bankCode      = $request->input('bank_code');

        // Cache resolved names for 10 minutes to avoid redundant API calls
        $cacheKey = "paystack_acct_{$bankCode}_{$accountNumber}";

        $result = Cache::remember($cacheKey, 600, function () use ($accountNumber, $bankCode) {
            $secret   = config('paystack.secret_key');

            $response = Http::withToken($secret)
                ->timeout(15)
                ->get("{$this->baseUrl}/bank/resolve", [
                    'account_number' => $accountNumber,
                    'bank_code'      => $bankCode,
                ]);

            if ($response->status() === 422 || $response->failed()) {
                return ['error' => true, 'message' => $response->json('message') ?? 'Account could not be verified.'];
            }

            $data = $response->json('data');

            return [
                'error'        => false,
                'account_name' => $data['account_name'] ?? '',
            ];
        });

        if ($result['error']) {
            // Don't cache errors
            Cache::forget($cacheKey);
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'success'      => true,
            'account_name' => $result['account_name'],
        ]);
    }
}
