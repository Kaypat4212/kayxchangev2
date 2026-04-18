<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\CompanyAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepositTestController extends Controller
{
    /**
     * Test deposit creation functionality
     */
    public function testDeposit()
    {
        try {
            // Check if we have required data
            $user = User::first();
            $companyAccount = CompanyAccount::first();
            
            if (!$user) {
                return response()->json(['error' => 'No users found'], 400);
            }
            
            if (!$companyAccount) {
                return response()->json(['error' => 'No company accounts found'], 400);
            }
            
            // Create test deposit
            $deposit = new Deposit();
            $deposit->amount = 1000;
            $deposit->status = 'pending';
            $deposit->transaction_ref = 'TEST-' . Str::upper(Str::random(10));
            $deposit->company_account_id = $companyAccount->id;
            $deposit->user_id = $user->id;
            $deposit->payment_method = 'bank_transfer';
            $deposit->proof = 'test-proof.jpg';
            $deposit->proof_of_payment = 'test-proof.jpg';
            
            $result = $deposit->save();
            
            return response()->json([
                'success' => $result,
                'deposit_id' => $deposit->id,
                'transaction_ref' => $deposit->transaction_ref,
                'message' => 'Test deposit created successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
    
    /**
     * Test deposit form validation
     */
    public function testValidation(Request $request)
    {
        try {
            $rules = [
                'amount' => 'required|numeric|min:1000',
                'payment_method' => 'required|in:bank_transfer',
                'company_account_id' => 'required_if:payment_method,bank_transfer|exists:company_accounts,id',
            ];
            
            $request->validate($rules);
            
            return response()->json([
                'success' => true,
                'message' => 'Validation passed',
                'data' => $request->all()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null
            ], 422);
        }
    }
}