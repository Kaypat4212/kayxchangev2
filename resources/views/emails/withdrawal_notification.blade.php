@php
    $bankAccount = null;
    try {
        $bankAccount = is_string($withdrawal->bank_account)
            ? json_decode($withdrawal->bank_account, true)
            : (is_array($withdrawal->bank_account) ? $withdrawal->bank_account : null);
    } catch (\Throwable $e) {
        $bankAccount = null;
    }

    $bankName     = $bankAccount['bank_name'] ?? null;
    $accountNoRaw = $bankAccount['account_number'] ?? null;
    $accountName  = $bankAccount['account_name'] ?? null;

    $accountNoStr = is_scalar($accountNoRaw) ? (string)$accountNoRaw : '';
    $accountNoStr = trim($accountNoStr);

    $maskAccountNumber = function (?string $value) {
        $value = $value ?? '';
        $value = trim($value);
        if ($value === '') return 'N/A';
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') return 'N/A';
        $last4 = substr($digits, -4);
        return '****' . $last4;
    };

    $maskAccountName = function (?string $value) {
        $value = $value ?? '';
        $value = trim($value);
        if ($value === '') return 'N/A';
        // Reveal first 2 characters, hide the rest (basic + consistent)
        $first2 = mb_substr($value, 0, 2);
        return $first2 . '***';
    };

    $bankNameDisplay    = $bankName ? (string)$bankName : 'N/A';
    $accountNoDisplay  = $maskAccountNumber(is_scalar($accountNoRaw) ? (string)$accountNoRaw : null);
    $accountNameDisplay= $maskAccountName(is_scalar($accountName) ? (string)$accountName : null);
@endphp

@component('mail::message')
     # Withdrawal {{ ucfirst($withdrawal->status) }}

     Dear {{ $withdrawal->user->name }},

     Your withdrawal request has been {{ $withdrawal->status }}.

     **Details:**
     - **Amount**: ₦{{ number_format($withdrawal->amount, 2) }}
     - **Bank**: {{ $bankNameDisplay }}
     - **Account Number**: {{ $accountNoDisplay }}
     - **Account Name**: {{ $accountNameDisplay }}
     - **Reference**: {{ $withdrawal->reference }}
     - **Status**: {{ ucfirst($withdrawal->status) }}
     - **Submitted**: {{ $withdrawal->created_at }}
     @if($withdrawal->status === 'approved')
     - **Processed**: {{ $withdrawal->processed_at }}
     @endif

     Thank you for using {{ config('app.name') }}.

     Regards,<br>
     {{ config('app.name') }} Team
     @endcomponent
     ```