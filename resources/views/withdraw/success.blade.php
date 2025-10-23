@extends('selllayout')

@section('content')
@php
    $withdrawal = $withdrawal;
    $bank_details = $bank_details;
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-green: #10b981;
        --dark-bg: #1a1a1a;
        --card-bg: #2c2c2c;
        --text-muted: #a0a0a0;
        --glow-color: rgba(16, 185, 129, 0.5);
    }

    body {
        background-color: var(--dark-bg);
        font-family: 'Poppins', sans-serif;
        color: #ffffff;
    }

    .container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        background: linear-gradient(135deg, var(--dark-bg), #2a3a2a);
        border-radius: 16px;
        box-shadow: 0 8px 32px var(--glow-color);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px var(--glow-color);
    }

    .header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .header h2 {
        color: var(--primary-green);
        font-weight: 600;
        font-size: 1.75rem;
    }

    .header p {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .success-box {
        background: #14532d;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px var(--glow-color);
        margin-bottom: 1.5rem;
    }

    .success-box p {
        margin: 0.5rem 0;
        font-size: 0.95rem;
    }

    .success-box strong {
        color: var(--primary-green);
    }

    .btn-primary {
        background-color: var(--primary-green);
        border-color: var(--primary-green);
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #059669;
        border-color: #059669;
        transform: scale(1.05);
    }

    .toast {
        position: fixed;
        top: 1rem;
        right: 1rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        color: #ffffff;
        font-size: 0.9rem;
        z-index: 1000;
        display: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .toast.error {
        background: #ef4444;
    }

    .toast.success {
        background: var(--primary-green);
    }

    .svg-container {
        display: flex;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .checkmark {
        stroke: var(--primary-green);
        stroke-width: 4;
        fill: none;
        stroke-dasharray: 100;
        stroke-dashoffset: 100;
        animation: draw 1s ease-out forwards;
    }

    .animate-fade {
        animation: fadeIn 0.5s ease-in-out;
    }

    .animate-slide-up {
        animation: slideUp 0.8s ease-in-out;
    }

    .animate-pulse {
        animation: glowPulse 2s infinite;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes glowPulse {
        0% { box-shadow: 0 0 5px var(--glow-color); }
        50% { box-shadow: 0 0 20px var(--glow-color); }
        100% { box-shadow: 0 0 5px var(--glow-color); }
    }

    @keyframes draw {
        to { stroke-dashoffset: 0; }
    }
</style>

<div id="toast" class="toast"></div>

<div class="container animate-slide-up">
    <div class="header">
        <h2>Withdrawal Successful</h2>
        <p>Your withdrawal request has been submitted successfully.</p>
    </div>

    <div class="svg-container">
        <svg width="100" height="100" viewBox="0 0 100 100">
            <path class="checkmark" d="M20 50 L40 70 L80 30" />
        </svg>
    </div>

    <div class="success-box animate-fade">
        <p><strong>Amount:</strong> ₦{{ number_format($withdrawal->amount, 2) }}</p>
        <p><strong>Reference:</strong> {{ $withdrawal->reference }}</p>
        <p><strong>Status:</strong> {{ ucfirst($withdrawal->status) }}</p>
        <p><strong>Payment Method:</strong> {{ ucfirst($withdrawal->payment_method) }}</p>
        <p><strong>Bank Name:</strong> {{ $bank_details['bank_name'] }}</p>
        <p><strong>Account Number:</strong> {{ $bank_details['account_number'] }}</p>
        <p><strong>Account Name:</strong> {{ $bank_details['account_name'] }}</p>
        <p><strong>Submitted:</strong> {{ $withdrawal->created_at->format('d M Y, H:i') }}</p>
    </div>

    <div class="text-center">
        <a href="{{ route('dashboard') }}" class="btn btn-primary animate-pulse">Back to Dashboard</a>
    </div>
</div>

<script>
    // Show toast notification
    function showToast(message, type) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = `toast ${type} animate-fade`;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Display session messages
        @if(session('success'))
            showToast('{!! e(session('success')) !!}', 'success');
        @elseif(session('error'))
            showToast('{!! e(session('error')) !!}', 'error');
        @endif
    });
</script>

@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection