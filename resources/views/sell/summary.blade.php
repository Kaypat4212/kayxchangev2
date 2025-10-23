@extends('selllayout')

@section('content')
<style>
    .summary-container {
        margin: 2rem auto;
        max-width: 600px;
        padding: 2rem;
        background: linear-gradient(135deg, #1a1a1a, #2a3a2a);
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        color: #ffffff;
    }
    .header { text-align: center; margin-bottom: 2rem; }
    .header h3 { font-size: 1.75rem; font-weight: 700; }
    .header p { color: #a0a0a0; font-size: 0.95rem; }
    .summary-details { margin-bottom: 1.5rem; }
    .summary-details p { margin: 0.5rem 0; }
    .summary-details strong { color: #10b981; }
    .proof-preview img { max-width: 100%; max-height: 100px; border-radius: 8px; }
    .back-btn { display: inline-block; padding: 0.75rem 1.5rem; background: #10b981; color: #ffffff; border-radius: 8px; text-decoration: none; margin-top: 1rem; }
    .back-btn:hover { background: #059669; }
</style>

<div class="summary-container">
    <div class="header">
        <h3>Trade Summary</h3>
        <p>Your sell trade has been submitted successfully.</p>
    </div>

    @if (session('success'))
        <div style="color: #10b981; font-size: 0.9rem; margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <div class="summary-details">
        <p><strong>Trade ID:</strong> {{ $trade->id }}</p>
        <p><strong>Transaction Reference:</strong> {{ $trade->transaction_ref }}</p>
        <p><strong>Coin:</strong> {{ $trade->coin }}</p>
        <p><strong>USD Amount:</strong> ${{ number_format($trade->usd_amount, 2) }}</p>
        <p><strong>Naira Amount:</strong> ₦{{ number_format($trade->naira_amount, 2) }}</p>
        <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $trade->payment_method)) }}</p>
        @if ($trade->payment_method !== 'wallet_balance')
            <p><strong>Bank Name:</strong> {{ $trade->bank_name }}</p>
            <p><strong>Account Number:</strong> {{ $trade->account_number }}</p>
            <p><strong>Account Name:</strong> {{ $trade->bank_name }}</p>
        @endif
        <p><strong>Wallet Address:</strong> {{ $trade->wallet_address }}</p>
        <p><strong>Status:</strong> {{ $trade->status }}</p>
        <p><strong>Submitted At:</strong> {{ $trade->created_at->toDateTimeString() }}</p>
        <div class="proof-preview">
            <p><strong>Proof:</strong></p>
            @if (Str::endsWith($trade->proof, ['.jpg', '.jpeg', '.png']))
                <img src="{{ asset('storage/' . $trade->proof) }}" alt="Proof Preview">
            @else
                <a href="{{ asset('storage/' . $trade->proof) }}" target="_blank">View PDF Proof</a>
            @endif
        </div>
    </div>

    <a href="{{ route('dashboard') }}" class="back-btn">Back to Dashboard</a>
</div>
@endsection