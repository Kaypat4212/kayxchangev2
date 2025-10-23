@extends('selllayout')

@section('content')
@php
    $balance = auth()->user()?->balance ?? 0;
    $minimum_withdrawal = config('withdrawal.minimum', 10);
    $bank_details = [
        'bank_name' => auth()->user()?->bank_name ?? 'N/A',
        'account_number' => auth()->user()?->account_number ?? 'N/A',
        'account_name' => auth()->user()?->account_name ?? 'N/A',
    ];
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

    .modal-content {
        background: linear-gradient(135deg, var(--dark-bg), #2a3a2a);
        color: #ffffff;
        border-radius: 16px;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 8px 32px var(--glow-color);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .modal-content:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px var(--glow-color);
    }

    .modal-header {
        border-bottom: 1px solid #3a3a3a;
    }

    .modal-title {
        color: var(--primary-green);
        font-weight: 600;
    }

    .btn-close {
        filter: invert(1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-control, .form-select {
        background: #2d2d2d;
        border: 1px solid #3a3a3a;
        color: #ffffff;
        border-radius: 8px;
        padding: 0.75rem;
        font-size: 0.95rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 8px var(--glow-color);
        outline: none;
    }

    .guidelines {
        background: #14532d;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px var(--glow-color);
    }

    .guidelines h4 {
        font-size: 1.25rem;
        color: var(--primary-green);
        margin-bottom: 0.75rem;
    }

    .guidelines ul {
        list-style: none;
        padding: 0;
        color: #d1d5db;
        font-size: 0.9rem;
    }

    .guidelines li {
        margin-bottom: 0.5rem;
        display: flex;
        align-items: flex-start;
    }

    .guidelines li::before {
        content: '✔';
        color: var(--primary-green);
        margin-right: 0.5rem;
    }

    .btn-primary {
        background-color: var(--primary-green);
        border-color: var(--primary-green);
        border-radius: 8px;
        padding: 0.75rem;
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
        z-index: 2000;
        display: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .toast.error {
        background: #ef4444;
    }

    .toast.success {
        background: var(--primary-green);
    }

    .toast.info {
        background: #3b82f6;
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
</style>

<div id="toast" class="toast"></div>

<!-- Trigger Button -->
<button type="button" class="btn btn-primary animate-pulse" data-bs-toggle="modal" data-bs-target="#withdrawModal">
    Withdraw Funds
</button>

<!-- Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animate-slide-up">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawModalLabel">Withdraw Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Guidelines -->
                <div class="guidelines animate-fade">
                    <h4>How to Withdraw Funds</h4>
                    <ul>
                        <li>Enter the amount (minimum ₦{{ number_format($minimum_withdrawal, 2) }}).</li>
                        <li>Enter your password to authenticate.</li>
                        <li>Select "Bank Transfer" as the payment method.</li>
                        <li>Choose your default bank or enter external bank details.</li>
                        <li>Verify all details before submitting.</li>
                        <li>Ensure your balance is sufficient.</li>
                    </ul>
                </div>

                <!-- Balance Info -->
                <p>Current balance: <strong class="text-success">₦{{ number_format($balance, 2) }}</strong></p>
                <p>Minimum withdrawal: <strong class="text-success">₦{{ number_format($minimum_withdrawal, 2) }}</strong></p>

                <form id="withdrawForm" method="POST" action="{{ route('withdraw.process') }}">
                    @csrf
                    <div class="form-group">
                        <label for="amount">Amount (₦)</label>
                        <input type="number" name="amount" id="amount" class="form-control" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="form-group" id="bank_option_group" style="display: none;">
                        <label for="bank_option">Bank Option</label>
                        <select name="bank_option" id="bank_option" class="form-control" required>
                            <option value="default">Default Bank</option>
                            <option value="external">External Bank</option>
                        </select>
                    </div>

                    <!-- Default Bank Details -->
                    <div class="form-group" id="default_bank_group" style="display: none;">
                        <p><strong>Default Bank Details:</strong></p>
                        <p>Bank: {{ $bank_details['bank_name'] }}</p>
                        <p>Account Number: {{ $bank_details['account_number'] }}</p>
                        <p>Account Name: {{ $bank_details['account_name'] }}</p>
                    </div>

                    <!-- External Bank Form -->
                    <div class="form-group" id="external_bank_group" style="display: none;">
                        <label for="external_bank_name">Bank Name</label>
                        <input type="text" name="external_bank_name" id="external_bank_name" class="form-control" required>
                        <label for="external_account_number">Account Number</label>
                        <input type="text" name="external_account_number" id="external_account_number" class="form-control" required>
                        <label for="external_account_name">Account Name</label>
                        <input type="text" name="external_account_name" id="external_account_name" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 animate-pulse" id="submitBtn">Submit Withdrawal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // DOM elements
    const withdrawForm = document.getElementById('withdrawForm');
    const paymentMethod = document.getElementById('payment_method');
    const bankOptionGroup = document.getElementById('bank_option_group');
    const bankOption = document.getElementById('bank_option');
    const defaultBankGroup = document.getElementById('default_bank_group');
    const externalBankGroup = document.getElementById('external_bank_group');
    const amountInput = document.getElementById('amount');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');
    const externalBankName = document.getElementById('external_bank_name');
    const externalAccountNumber = document.getElementById('external_account_number');
    const externalAccountName = document.getElementById('external_account_name');
    const minimumWithdrawal = {{ $minimum_withdrawal }};
    const balance = {{ $balance }};

    // Show toast notification
    function showToast(message, type) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = `toast ${type} animate-fade`;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 5000); // Increased duration for bank update message
    }

    // Update form visibility based on payment method
    function updatePaymentMethod() {
        if (paymentMethod.value === 'bank') {
            bankOptionGroup.style.display = 'block';
            updateBankOption();
        } else {
            bankOptionGroup.style.display = 'none';
            defaultBankGroup.style.display = 'none';
            externalBankGroup.style.display = 'none';
        }
    }

    // Update form visibility based on bank option
    function updateBankOption() {
        if (bankOption.value === 'default') {
            defaultBankGroup.style.display = 'block';
            externalBankGroup.style.display = 'none';
            externalBankName.removeAttribute('required');
            externalAccountNumber.removeAttribute('required');
            externalAccountName.removeAttribute('required');
        } else {
            defaultBankGroup.style.display = 'none';
            externalBankGroup.style.display = 'block';
            externalBankName.setAttribute('required', 'required');
            externalAccountNumber.setAttribute('required', 'required');
            externalAccountName.setAttribute('required', 'required');
        }
    }

    // Client-side validation
    function validateForm() {
        const amount = parseFloat(amountInput.value) || 0;
        const password = passwordInput.value;
        const paymentMethodValue = paymentMethod.value;
        const bankOptionValue = bankOption.value;
        const externalBankNameValue = externalBankName.value.trim();
        const externalAccountNumberValue = externalAccountNumber.value.trim();
        const externalAccountNameValue = externalAccountName.value.trim();

        if (!amount || amount < minimumWithdrawal) {
            showToast(`Amount must be at least ₦${minimumWithdrawal.toLocaleString('en-NG')}.`, 'error');
            return false;
        }
        if (amount > balance) {
            showToast('Insufficient balance for withdrawal.', 'error');
            return false;
        }
        if (!password) {
            showToast('Please enter your password.', 'error');
            return false;
        }
        if (!paymentMethodValue) {
            showToast('Please select a payment method.', 'error');
            return false;
        }
        if (paymentMethodValue === 'bank' && !bankOptionValue) {
            showToast('Please select a bank option.', 'error');
            return false;
        }
        if (bankOptionValue === 'default') {
            const bankName = defaultBankGroup.querySelector('p:nth-child(2)').textContent.replace('Bank: ', '');
            if (bankName === 'N/A' || !bankName.trim()) {
                showToast('Default bank details are not set. Please update your bank details in Settings.', 'error');
                setTimeout(() => {
                    window.location.href = '{{ route("edit-bank") }}';
                }, 3000);
                return false;
            }
        }
        if (bankOptionValue === 'external') {
            if (!externalBankNameValue) {
                showToast('Please enter the bank name.', 'error');
                return false;
            }
            if (!externalAccountNumberValue) {
                showToast('Please enter the account number.', 'error');
                return false;
            }
            if (!externalAccountNameValue) {
                showToast('Please enter the account name.', 'error');
                return false;
            }
            if (!/^\d{10}$/.test(externalAccountNumberValue)) {
                showToast('Account number must be 10 digits.', 'error');
                return false;
            }
        }
        return true;
    }

    // Form submission via AJAX
    withdrawForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validateForm()) return;

        // Show confirmation for default bank
        if (bankOption.value === 'default') {
            showToast(
                `The amount will be sent to your default bank: ${defaultBankGroup.querySelector('p:nth-child(2)').textContent}.`,
                'info'
            );
            await new Promise(resolve => setTimeout(resolve, 2000));
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        const formData = new FormData(withdrawForm);
        console.log('Form Data:', Object.fromEntries(formData));

        try {
            const response = await fetch('{{ route("withdraw.process") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                },
            });

            const data = await response.json();
            if (response.ok && data.success) {
                showToast('Withdrawal request submitted! Awaiting admin approval.', 'success');
                document.querySelector('#withdrawModal .btn-close').click();
                window.location.href = '/withdraw/success/' + data.withdrawal_id;
            } else {
                if (data.errors) {
                    Object.values(data.errors).forEach(error => showToast(error[0], 'error'));
                } else {
                    showToast(data.error || 'An error occurred.', 'error');
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Withdrawal';
            }
        } catch (error) {
            console.error('Submission error:', error);
            showToast('An error occurred. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Withdrawal';
        }
    });

    // Event listeners
    paymentMethod.addEventListener('change', updatePaymentMethod);
    bankOption.addEventListener('change', updateBankOption);
    amountInput.addEventListener('input', () => {
        if (amountInput.value < 0) {
            amountInput.value = '';
            showToast('Amount must be positive!', 'error');
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Display session messages
        @if(session('success'))
            showToast('{!! e(session('success')) !!}', 'success');
        @elseif(session('error'))
            showToast('{!! e(session('error')) !!}', 'error');
        @endif

        // Display Laravel validation errors
        const errors = @json($errors->all());
        if (errors.length > 0) {
            errors.forEach(error => showToast(error, 'error'));
        }

        // Initialize form state
        updatePaymentMethod();
    });
</script>

@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection