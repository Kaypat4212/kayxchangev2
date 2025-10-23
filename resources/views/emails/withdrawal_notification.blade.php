 @component('mail::message')
     # Withdrawal {{ ucfirst($withdrawal->status) }}

     Dear {{ $withdrawal->user->name }},

     Your withdrawal request has been {{ $withdrawal->status }}.

     **Details:**
     - **Amount**: ₦{{ number_format($withdrawal->amount, 2) }}
     - **Bank**: {{ json_decode($withdrawal->bank_account)->bank_name }}
     - **Account Number**: {{ json_decode($withdrawal->bank_account)->account_number }}
     - **Account Name**: {{ json_decode($withdrawal->bank_account)->account_name }}
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