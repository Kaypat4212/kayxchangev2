@extends('buylayout')

<style>
    /* Modern styling for crypto exchange platform */
    :root {
        --primary: #11B981;
        --secondary: #1E293B;
        --accent: #3B82F6;
        --text: #F8FAFC;
        --background: #0F172A;
        --card: #1E293B;
        --error: #EF4444;
        --success: #10B981;
        --border-radius: 12px;
    }

    body {
        background-color: var(--background);
        
        font-family: 'Inter', sans-serif;
    }

    .trade-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: black;
        text-align: center;
        margin-bottom: 30px;
        position: relative;
    }

    .page-title:after {
        content: '';
        position: absolute;
        width: 80px;
        height: 4px;
        background: var(--primary);
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 2px;
    }

    .summary-card {
        background-color: var(--card);
        border-radius: var(--border-radius);
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .summary-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;

    }

    .info-item {
        margin-bottom: 16px;
    }

    .info-label {
        color:rgb(0, 235, 4);
        font-size: 14px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 16px;
        color: white;
        font-weight: 500;
    }

    .payment-card {
        background: linear-gradient(145deg, #1E293B, #0F172A);
        border: 1px solid #334155;
        border-radius: var(--border-radius);
        padding: 24px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .payment-card:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
    }

    .payment-title {
        color: white;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .payment-title svg {
        width: 24px;
        height: 24px;
    }

    .account-details {
        background-color: rgba(15, 23, 42, 0.6);
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
        color: white;
    }

    .account-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #334155;
    }

    .account-item:last-child {
        border-bottom: none;
    }

    .account-label {
        color:rgb(0, 195, 49);
    }

    .account-value {
        font-weight: 500;
        color: white;
    }

    .upload-section {
        background-color: grey;
        border-radius: var(--border-radius);
        padding: 24px;
        margin-bottom: 30px;
    }

    .file-input-container {
        border: 2px dashed #334155;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .file-input-container:hover {
        border-color: var(--primary);
        background-color: rgba(17, 185, 129, 0.05);
    }

    .file-input-container svg {
        width: 48px;
        height: 48px;
        color: var(--primary);
        margin-bottom: 16px;
    }

    .file-input {
        display: none;
    }

    .upload-hint {
        font-size: 14px;
        color: #94A3B8;
        margin-top: 8px;
    }

    .submit-button {
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: all 0.2s ease;
    }

    .submit-button:hover {
        background-color: #0EA572;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(17, 185, 129, 0.2);
    }

    .submit-button:active {
        transform: translateY(0);
    }

    .error-message {
        color: var(--error);
        font-size: 14px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .footer {
        text-align: center;
        padding: 20px;
        color: #64748B;
        font-size: 14px;
        margin-top: 60px;
    }

    @media (max-width: 768px) {
        .summary-info {
            grid-template-columns: 1fr;
        }
    }
</style>

@section('content')
<div class="trade-container">
    <h1 class="page-title">Trade Summary</h1>

    <!-- Transaction Summary Card -->
    <div class="summary-card">
        <div class="summary-info">
            <div class="info-item">
                <div class="info-label">Cryptocurrency</div>
                <div class="info-value">{{ $trade->coin }}</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Amount (USD)</div>
                <div class="info-value">${{ number_format($trade->usd_amount, 2) }}</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Amount (Naira)</div>
                <div class="info-value">₦{{ number_format($trade->naira_amount, 2) }}</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Wallet Address</div>
                <div class="info-value" style="word-break: break-all;">{{ $trade->wallet_address }}</div>
            </div>
        </div>
    </div>

    <!-- Payment Instructions Card -->
    <div class="payment-card">
        <h4 class="payment-title">
          
            Payment Instructions
        </h4>
        
        <p class="text-white">Please transfer the exact amount to the company account below:</p>
        
        <div class="account-details">
            <div class="account-item">
                <span class="account-label">Account Name</span>
                <span class="account-value">{{ $accountDetails->account_name }}</span>
            </div>
            <div class="account-item">
                <span class="account-label">Account Number</span>
                <span class="account-value">{{ $accountDetails->account_number }}</span>
            </div>
            <div class="account-item">
                <span class="account-label">Bank Name</span>
                <span class="account-value">{{ $accountDetails->bank_name ?? 'Bank Name' }}</span>
            </div>
        </div>
    </div>

    <!-- Payment Proof Upload Form -->
    <div class="upload-section">
        <form method="POST" action="{{ route('buy.uploadPayment', ['id' => $trade->id]) }}" enctype="multipart/form-data">
            @csrf
            <label for="payment_proof_input" class="file-input-container" id="upload-container">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div id="file-name">Upload Payment Proof</div>
                <p class="upload-hint">Click to select or drag and drop your proof of payment</p>
                <input type="file" name="payment_proof" id="payment_proof_input" class="file-input" accept="image/*" required>
            </label>
            
            @error('payment_proof')
                <div class="error-message">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    {{ $message }}
                </div>
            @enderror

            <button type="submit" class="submit-button">Submit Payment Proof</button>
        </form>
    </div>
</div>

<footer class="footer">
    &copy; Kay Xchange {{ date('Y') }} | All Rights Reserved
</footer>

<script>
    // Add client-side functionality
    document.getElementById('payment_proof_input').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'Upload Payment Proof';
        document.getElementById('file-name').textContent = fileName;
        
        if (e.target.files[0]) {
            document.getElementById('upload-container').style.borderColor = '#11B981';
            document.getElementById('upload-container').style.backgroundColor = 'rgba(17, 185, 129, 0.05)';
        }
    });
</script>
@endsection
