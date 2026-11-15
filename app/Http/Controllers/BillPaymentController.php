<?php

namespace App\Http\Controllers;

use App\Models\BillPayment;
use App\Models\AdminSetting;
use App\Services\BalanceSettlementService;
use App\Services\BillPaymentProviderService;
use App\Services\AdminTradeAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillPaymentController extends Controller
{
    public function index(Request $request, BillPaymentProviderService $providers)
    {
        $categories = [
            'airtime'=>'Airtime', 'data'=>'Mobile data', 'electricity'=>'Electricity',
            'cable_tv'=>'Cable TV',
        ];
        $selectedCategory = array_key_exists((string) $request->query('category'), $categories)
            ? (string) $request->query('category') : '';
        $billPaymentsEnabled = $providers->billPaymentsEnabled();
        $utilityPaymentsEnabled = $providers->utilityPaymentsEnabled();
        if (!$utilityPaymentsEnabled) {
            unset($categories['electricity'], $categories['cable_tv']);
            if (in_array($selectedCategory, ['electricity', 'cable_tv'], true)) $selectedCategory = '';
        }
        return view('bills.index', [
            'providers' => $providers->providers(),
            'billPaymentsEnabled' => $billPaymentsEnabled,
            'utilityPaymentsEnabled' => $utilityPaymentsEnabled,
            'sandboxProviders' => array_values(array_filter($providers->providers(), fn (string $provider) => $providers->isSandbox($provider))),
            'balance' => (float)auth()->user()->balance,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    public function catalog(Request $request, BillPaymentProviderService $providers)
    {
        $data = $request->validate([
            'provider'=>'required|in:vtpass,monnify', 'category'=>'nullable|string|max:60', 'biller'=>'nullable|string|max:120',
        ]);
        try { $providers->assertCategoryEnabled((string)($data['category'] ?? '')); }
        catch (\RuntimeException $e) { return response()->json(['message' => $e->getMessage()], 403); }
        try {
            $result = $providers->catalog($data['provider'], $data['category'] ?? '', $data['biller'] ?? '');
            return response()->json(['items'=>$this->normalizeCatalog($data['provider'], $result)]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message'=>'Could not load bill options. Check provider configuration and try again.'], 422);
        }
    }

    public function validateCustomer(Request $request, BillPaymentProviderService $providers)
    {
        $data = $request->validate([
            'provider'=>'required|in:vtpass,monnify', 'category'=>'required|string|max:60',
            'biller_code'=>'nullable|string|max:100', 'service_code'=>'required|string|max:100',
            'product_code'=>'nullable|string|max:150', 'customer_identifier'=>'required|string|max:120',
            'phone'=>'required|string|max:30', 'amount'=>'required|numeric|min:1|max:1000000', 'type'=>'nullable|string|max:30',
        ]);
        try { $providers->assertCategoryEnabled($data['category']); }
        catch (\RuntimeException $e) { throw ValidationException::withMessages(['category' => $e->getMessage()]); }
        try {
            $providers->assertLiveVending($data['provider']);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['provider' => $e->getMessage()]);
        }
        if (!in_array($data['provider'], $providers->providers(), true)) abort(404);
        if (($data['provider'] === 'monnify' || in_array($data['category'], ['data','cable_tv'], true)) && empty($data['product_code'])) {
            throw ValidationException::withMessages(['product_code'=>'Choose a bill plan before continuing.']);
        }

        if (!empty($data['product_code'])) {
            try {
                $providerAmount = $providers->productPrice($data['provider'], $data['category'], $data['service_code'], $data['product_code']);
                if ($providerAmount !== null && round((float)$data['amount'], 2) !== round($providerAmount, 2)) {
                    throw ValidationException::withMessages(['amount'=>'The selected plan price changed. Refresh the plan and review the updated amount.']);
                }
            } catch (ValidationException $e) { throw $e; }
            catch (\Throwable $e) {
                report($e);
                throw ValidationException::withMessages(['product_code'=>'This plan could not be verified. Refresh the available options and try again.']);
            }
        }

        $providerResponse = [];
        $customerName = null;
        $requiresValidation = $data['provider'] === 'monnify' || in_array($data['category'], ['electricity','cable_tv'], true);
        if ($requiresValidation) {
            try {
                $providerResponse = $providers->validateCustomer($data['provider'], $data);
                if ($data['provider'] === 'vtpass' && (string)($providerResponse['code'] ?? '') !== '000') {
                    throw ValidationException::withMessages(['customer_identifier'=>'The provider could not verify those customer details. Check the number and try again.']);
                }
                $customerName = data_get($providerResponse, 'responseBody.customerName')
                    ?? data_get($providerResponse, 'content.Customer_Name')
                    ?? data_get($providerResponse, 'content.customerName');
                if ($data['provider'] === 'monnify' && !$customerName) {
                    $customerName = data_get($providerResponse, 'customerName');
                }
            } catch (ValidationException $e) { throw $e; }
            catch (\Throwable $e) {
                report($e);
                throw ValidationException::withMessages(['customer_identifier'=>'Customer verification is temporarily unavailable. No balance was charged.']);
            }
        }

        $payment = BillPayment::create([
            'user_id'=>auth()->id(), 'reference'=>now('Africa/Lagos')->format('YmdHi') . Str::lower(Str::random(16)),
            'provider'=>$data['provider'], 'category'=>$data['category'], 'biller_code'=>$data['biller_code'] ?? null,
            'service_code'=>$data['service_code'], 'product_code'=>$data['product_code'] ?? null,
            'customer_identifier'=>$data['customer_identifier'], 'customer_name'=>$customerName,
            'phone'=>$data['phone'], 'amount'=>round((float)$data['amount'], 2), 'status'=>'validated',
            'validation_data'=>$providerResponse, 'validated_at'=>now(),
        ]);

        try {
            app(AdminTradeAlertService::class)->sendTriggeredAlert('bill_payment', [
                'trade_id' => $payment->id, 'reference' => $payment->reference,
                'user_name' => auth()->user()->name, 'user_email' => auth()->user()->email,
                'coin' => strtoupper($payment->category), 'naira_amount' => $payment->amount,
                'status' => 'validated', 'network' => $payment->provider,
            ]);
        } catch (\Throwable $e) { report($e); }

        return response()->json([
            'id'=>$payment->id, 'reference'=>$payment->reference, 'customer_name'=>$customerName,
            'amount'=>(float)$payment->amount, 'message'=>$customerName ? 'Verified for ' . $customerName : 'Details accepted. Review and confirm this bill.',
        ]);
    }

    public function purchase(Request $request, int $id, BalanceSettlementService $settlement, BillPaymentProviderService $providers)
    {
        $request->validate(['pin'=>'required|digits:4']);
        $pinError = DB::transaction(function () use ($request) {
            $user = \App\Models\User::query()->lockForUpdate()->findOrFail(auth()->id());
            if (!$user->transaction_pin) return 'Set up your security PIN in Profile before paying bills.';
            if ($user->pin_locked_until && now()->lt($user->pin_locked_until)) return 'Your security PIN is temporarily locked. Try again after the lock expires.';
            if (!Hash::check($request->input('pin'), $user->transaction_pin)) {
                $user->pin_attempts = (int)$user->pin_attempts + 1;
                if ($user->pin_attempts >= 5) $user->pin_locked_until = now()->addMinutes(15);
                $user->save();
                return 'Incorrect security PIN. Check it and try again.';
            }
            $user->pin_attempts = 0;
            $user->pin_locked_until = null;
            $user->save();
            return null;
        });
        if ($pinError) throw ValidationException::withMessages(['pin'=>$pinError]);

        $payment = BillPayment::query()->where('user_id', auth()->id())->findOrFail($id);
        try { $providers->assertCategoryEnabled((string)$payment->category); }
        catch (\RuntimeException $e) { throw ValidationException::withMessages(['bill' => $e->getMessage()]); }
        try {
            $providers->assertLiveVending($payment->provider);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['bill' => $e->getMessage()]);
        }
        $requiresReview = filter_var(AdminSetting::getSetting('bill_admin_review_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $acceptableStatus = $requiresReview ? $payment->status === 'approved' : $payment->status === 'validated';
        if (!$acceptableStatus || !$payment->validated_at || $payment->validated_at->lt(now()->subMinutes(10))) {
            throw ValidationException::withMessages(['bill'=>'This confirmation expired. Please verify the customer details again.']);
        }
        $reserved = DB::transaction(function () use ($payment, $settlement, $requiresReview) {
            $row = BillPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $allowed = $requiresReview ? ['approved'] : ['validated'];
            if (!in_array($row->status, $allowed, true)) throw ValidationException::withMessages(['bill'=>'This bill has already been submitted or is not approved.']);
            $settlement->adjustNgnBalance((int)$row->user_id, -(float)$row->amount, 'bill_payment.debit', $row->reference);
            $row->status = 'processing';
            $row->save();
            return $row;
        });

        try {
            $response = $providers->vend($reserved->provider, [
                'reference'=>$reserved->reference, 'service_code'=>$reserved->service_code,
                'product_code'=>$reserved->product_code, 'customer_identifier'=>$reserved->customer_identifier,
                'phone'=>$reserved->phone, 'amount'=>(float)$reserved->amount,
                'validation_data'=>$reserved->validation_data,
            ]);
            $status = $providers->normalizeStatus($reserved->provider, $response);
            DB::transaction(function () use ($reserved, $response, $status, $settlement) {
                $row = BillPayment::query()->lockForUpdate()->findOrFail($reserved->id);
                if ($row->status !== 'processing') return;
                $row->provider_response = $response;
                $row->provider_reference = (string)(data_get($response,'responseBody.transactionReference') ?? data_get($response,'content.transactions.transactionId') ?? data_get($response,'transactionReference') ?? '');
                $row->receipt_token = data_get($response,'purchased_code') ?? data_get($response,'content.token') ?? data_get($response,'content.transactions.token');
                $row->status = $status;
                if ($status === 'failed') {
                    $settlement->adjustNgnBalance((int)$row->user_id, (float)$row->amount, 'bill_payment.refund', $row->reference);
                    $row->status = 'failed_refunded';
                    $row->refunded_at = now();
                }
                if ($status !== 'pending') $row->processed_at = now();
                $row->save();
            });
        } catch (\Throwable $e) {
            // A timeout may happen after the provider has fulfilled the bill.
            // Keep the debit pending and requery; never blindly vend a second time.
            report($e);
            DB::transaction(function () use ($reserved, $e) {
                $row = BillPayment::query()->lockForUpdate()->findOrFail($reserved->id);
                if ($row->status === 'processing') {
                    $row->status = 'pending';
                    $row->provider_response = ['message'=>'Provider response was uncertain. Use status check before retrying.'];
                    $row->save();
                }
            });
        }
        return redirect()->route('bills.show', $payment->id);
    }

    public function show(int $id)
    {
        $payment = BillPayment::query()->where('user_id', auth()->id())->findOrFail($id);
        return view('bills.show', compact('payment'));
    }

    public function requery(int $id, BalanceSettlementService $settlement, BillPaymentProviderService $providers)
    {
        $payment = BillPayment::query()->where('user_id', auth()->id())->findOrFail($id);
        if (!in_array($payment->status, ['pending','processing'], true)) return back()->with('info', 'This transaction is already ' . $payment->status . '.');
        try {
            $response = $providers->requery($payment->provider, $payment->reference);
            $status = $providers->normalizeStatus($payment->provider, $response);
            DB::transaction(function () use ($payment, $response, $status, $settlement) {
                $row = BillPayment::query()->lockForUpdate()->findOrFail($payment->id);
                if (!in_array($row->status, ['pending','processing'], true)) return;
                $row->provider_response = $response;
                $row->status = $status;
                if ($status === 'failed') {
                    if (!$row->refunded_at) {
                        $settlement->adjustNgnBalance((int)$row->user_id, (float)$row->amount, 'bill_payment.refund', $row->reference);
                        $row->refunded_at = now();
                    }
                    $row->status = 'failed_refunded';
                }
                if ($status !== 'pending') $row->processed_at = now();
                $row->save();
            });
            return back()->with('success', 'Provider status checked.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Status could not be confirmed. Your balance remains reserved; please try the status check again later.');
        }
    }

    public function history()
    {
        $payments = BillPayment::where('user_id', auth()->id())->latest()->paginate(20);
        return view('bills.history', compact('payments'));
    }

    public function adminHistory()
    {
        $payments = BillPayment::with('user')->latest()->paginate(30);
        return view('admin.bill-providers.transactions', compact('payments'));
    }

    public function adminReview(int $id, Request $request)
    {
        $data = $request->validate(['decision' => 'required|in:approve,reject', 'admin_note' => 'nullable|string|max:500']);
        $changed = DB::transaction(function () use ($id, $data) {
            $row = BillPayment::query()->lockForUpdate()->findOrFail($id);
            if ($row->status !== 'validated') return false;
            $row->status = $data['decision'] === 'approve' ? 'approved' : 'rejected';
            if ($data['decision'] === 'reject') {
                $row->provider_response = array_merge($row->provider_response ?? [], ['admin_review_note' => $data['admin_note'] ?? 'Request declined by admin.']);
                $row->processed_at = now();
            }
            $row->save();
            return true;
        });
        return back()->with($changed ? 'success' : 'error', $changed
            ? ($data['decision'] === 'approve' ? 'Bill request approved. The user can now confirm payment.' : 'Bill request rejected.')
            : 'This bill request has already been reviewed or processed.');
    }

    public function adminRequery(int $id, BalanceSettlementService $settlement, BillPaymentProviderService $providers)
    {
        $payment = BillPayment::findOrFail($id);
        if (!in_array($payment->status, ['pending','processing'], true)) return back()->with('info', 'Only unresolved transactions can be queried.');
        try {
            $response = $providers->requery($payment->provider, $payment->reference);
            $status = $providers->normalizeStatus($payment->provider, $response);
            DB::transaction(function () use ($payment, $response, $status, $settlement) {
                $row = BillPayment::query()->lockForUpdate()->findOrFail($payment->id);
                if (!in_array($row->status, ['pending','processing'], true)) return;
                $row->provider_response = $response;
                $row->status = $status;
                if ($status === 'failed') {
                    if (!$row->refunded_at) {
                        $settlement->adjustNgnBalance((int)$row->user_id, (float)$row->amount, 'bill_payment.refund', $row->reference);
                        $row->refunded_at = now();
                    }
                    $row->status = 'failed_refunded';
                }
                if ($status !== 'pending') $row->processed_at = now();
                $row->save();
            });
            return back()->with('success', 'Provider status checked for ' . $payment->reference . '.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Provider status remains uncertain. No second purchase was started.');
        }
    }

    private function normalizeCatalog(string $provider, array $result): array
    {
        $items = $result['items'] ?? [];
        if ($provider === 'vtpass' && is_array($items)) {
            if (isset($items['variations'])) $items = $items['variations'];
            elseif (isset($items['varations'])) $items = $items['varations'];
            elseif (isset($items['content']) && is_array($items['content'])) $items = $items['content'];
        }
        if (!is_array($items)) return [];
        return array_values(array_map(static function ($item) {
            if (!is_array($item)) return ['code'=>(string)$item, 'name'=>(string)$item, 'amount'=>null];
            return [
                'code'=>(string)($item['productCode'] ?? $item['billerCode'] ?? $item['serviceID'] ?? $item['variation_code'] ?? $item['categoryCode'] ?? $item['code'] ?? ''),
                'name'=>(string)($item['productName'] ?? $item['billerName'] ?? $item['categoryName'] ?? $item['name'] ?? $item['ServiceName'] ?? 'Bill option'),
                'amount'=>isset($item['amount']) ? (float)$item['amount'] : (isset($item['variation_amount']) ? (float)$item['variation_amount'] : null),
                'category'=>(string)($item['categoryCode'] ?? ''),
                'requires_identifier'=>(bool)($item['isCustomerValidationRequired'] ?? true),
            ];
        }, $items));
    }
}
