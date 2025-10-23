@extends('layout')

@section('title', 'Create Deposit')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush

@section('content')
<div class="container my-5">
    <h2 class="text-black fw-bold mb-4 animate-fade-in">Make a Deposit</h2>
    @if ($companyAccounts->isEmpty())
        <div class="alert alert-warning text-center animate-fade-in">
            No company accounts available. Please contact support to add a bank account.
        </div>
    @else
        <div class="card bg-dark text-white shadow-lg animate-fade-in">
            <div class="card-body">
                <form id="deposit-form" action="{{ route('deposits.store') }}" method="POST" enctype="multipart/form-data" class="deposit-form mx-auto">
                    @csrf
                    <div class="mb-4 animate-fade-in">
                        <label for="amount" class="form-label">Amount (NGN)</label>
                        <input type="number" name="amount" id="amount" min="1000" required 
                               class="form-control" placeholder="Enter amount (min ₦1000)">
                        @error('amount')
                            <p class="error-text" data-error="amount">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4 animate-fade-in animation-delay-1">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" required class="form-select">
                            <option value="" disabled selected>Select payment method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                        @error('payment_method')
                            <p class="error-text" data-error="payment_method">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="company-account-section" class="mb-4 animate-fade-in animation-delay-2 d-none">
                        <label for="company_account_id" class="form-label d-flex align-items-center">
                            Payment Account
                            <span class="tooltip-icon ms-2">?</span>
                            <span class="tooltip-text">Select the company bank account to which you will transfer the deposit amount.</span>
                        </label>
                        <select name="company_account_id" id="company_account_id" class="form-select">
                            <option value="" disabled selected>Select company account</option>
                            @foreach($companyAccounts as $account)
                                <option value="{{ $account->id }}" 
                                        data-bank-name="{{ $account->bank_name }}"
                                        data-account-number="{{ $account->account_number }}"
                                        data-account-name="{{ $account->account_name }}">
                                    {{ $account->account_name }} ({{ $account->bank_name }} - {{ $account->account_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('company_account_id')
                            <p class="error-text" data-error="company_account_id">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4 animate-fade-in animation-delay-3">
                        <label for="proof_of_payment" class="form-label">Proof of Payment</label>
                        <input type="file" name="proof_of_payment" id="proof_of_payment" accept="image/*" required 
                               class="form-control file-input">
                        <small class="text-muted">Maximum file size: 10MB. Supported formats: JPEG, PNG, JPG, WEBP</small>
                        @error('proof_of_payment')
                            <p class="error-text" data-error="proof_of_payment">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- View Account Details Button - Initially Hidden -->
                    <div class="mb-3 animate-fade-in animation-delay-3" id="view-account-section" style="display: none;">
                        <button type="button" class="btn btn-outline-info w-100" id="view-account-btn">
                            <i class="bi bi-eye me-2"></i>
                            View Account Details Again
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 animate-fade-in animation-delay-4" id="submit-btn">
                        I Have Made Payment
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentDetailsModalLabel">Payment Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Bank Name:</strong> <span id="modal-bank-name"></span></p>
                    <p><strong>Account Name:</strong> <span id="modal-account-name"></span></p>
                    <p><strong>Account Number:</strong> <span id="modal-account-number"></span>
                        <button class="btn btn-sm btn-outline-success ms-2 copy-btn" data-clipboard-text="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16">
                                <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                            </svg>
                            Copy
                        </button>
                    </p>
                    <p class="text-muted">Please transfer the specified amount to this account and upload proof of payment.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="error-toast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toast-message"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="success-toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">Account number copied to clipboard!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        @if (session('success'))
            <div id="form-success-toast" class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const paymentMethodSelect = document.getElementById('payment_method');
        const companyAccountSection = document.getElementById('company-account-section');
        const companyAccountSelect = document.getElementById('company_account_id');
        const paymentDetailsModal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
        const viewAccountSection = document.getElementById('view-account-section');
        const viewAccountBtn = document.getElementById('view-account-btn');
        const proofInput = document.getElementById('proof_of_payment');
        const submitBtn = document.getElementById('submit-btn');

        let selectedAccountDetails = {
            bankName: '',
            accountName: '',
            accountNumber: ''
        };

        // Initial check for payment method
        if (paymentMethodSelect.value === 'bank_transfer') {
            companyAccountSection.classList.remove('d-none');
            companyAccountSection.classList.add('animate-fade-in');
            companyAccountSelect.required = true;
        }

        // Toggle company account section
        paymentMethodSelect.addEventListener('change', function () {
            console.log('Payment method changed to:', this.value);
            if (this.value === 'bank_transfer') {
                companyAccountSection.classList.remove('d-none');
                companyAccountSection.classList.add('animate-fade-in');
                companyAccountSelect.required = true;
            } else {
                companyAccountSection.classList.add('d-none');
                companyAccountSelect.required = false;
                viewAccountSection.style.display = 'none';
                paymentDetailsModal.hide();
            }
        });

        // Show payment details modal when company account is selected
        companyAccountSelect.addEventListener('change', function () {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                selectedAccountDetails = {
                    bankName: selectedOption.dataset.bankName,
                    accountName: selectedOption.dataset.accountName,
                    accountNumber: selectedOption.dataset.accountNumber
                };
                
                showPaymentDetails();
                // Show the "View Account Details Again" button after first selection
                viewAccountSection.style.display = 'block';
            }
        });

        // View Account Details button click
        viewAccountBtn.addEventListener('click', function () {
            if (selectedAccountDetails.bankName) {
                showPaymentDetails();
            }
        });

        function showPaymentDetails() {
            document.getElementById('modal-bank-name').textContent = selectedAccountDetails.bankName;
            document.getElementById('modal-account-name').textContent = selectedAccountDetails.accountName;
            document.getElementById('modal-account-number').textContent = selectedAccountDetails.accountNumber;
            document.querySelector('.copy-btn').dataset.clipboardText = selectedAccountDetails.accountNumber;
            paymentDetailsModal.show();
        }

        // File input change handler (removed automatic button text change)
        proofInput.addEventListener('change', function () {
            // Just log the file change, don't change button text automatically
            if (this.files.length > 0) {
                console.log('File selected:', this.files[0].name);
            }
        });

        // Copy account number to clipboard
        document.querySelector('.copy-btn').addEventListener('click', function () {
            const text = this.dataset.clipboardText;
            navigator.clipboard.writeText(text).then(() => {
                const toast = new bootstrap.Toast(document.getElementById('success-toast'));
                toast.show();
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        });

        // Form submission with button loader and error toast
        document.getElementById('deposit-form')?.addEventListener('submit', function (e) {
            console.log('Form submission started');
            
            // Validate required fields manually
            const amount = document.getElementById('amount').value;
            const paymentMethod = document.getElementById('payment_method').value;
            const companyAccount = document.getElementById('company_account_id').value;
            const proofFile = document.getElementById('proof_of_payment').files[0];
            
            console.log('Form values:', {
                amount: amount,
                paymentMethod: paymentMethod,
                companyAccount: companyAccount,
                proofFile: proofFile ? proofFile.name : 'No file'
            });
            
            let hasErrors = false;
            const errorMessages = [];
            
            if (!amount || amount < 1000) {
                hasErrors = true;
                errorMessages.push('Amount must be at least ₦1000');
            }
            
            if (!paymentMethod) {
                hasErrors = true;
                errorMessages.push('Please select a payment method');
            }
            
            if (paymentMethod === 'bank_transfer' && !companyAccount) {
                hasErrors = true;
                errorMessages.push('Please select a company account');
            }
            
            if (!proofFile) {
                hasErrors = true;
                errorMessages.push('Please upload proof of payment');
            }
            
            if (hasErrors) {
                console.log('Form validation failed:', errorMessages);
                e.preventDefault();
                
                const toast = new bootstrap.Toast(document.getElementById('error-toast'));
                const toastMessage = document.getElementById('toast-message');
                toastMessage.innerText = errorMessages.join('\n');
                toast.show();
                
                return false;
            }
            
            // If no errors, show loading state
            const spinner = submitBtn.querySelector('.spinner-border');
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitBtn.innerHTML = 'Processing... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            
            console.log('Form is valid, submitting...');
            // Form will submit normally
        });
    });
</script>

<style>
    .deposit-form {
        max-width: 500px;
    }
    .card {
        background-color: #1c2526;
        border-radius: 12px;
        border: 1px solid #3a3f41;
    }
    .form-control, .form-select {
        background-color: #2a2f31;
        border: 1px solid #3a3f41;
        color: #ffffff;
        border-radius: 8px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #00cc00;
        box-shadow: 0 0 5px rgba(0, 204, 0, 0.5);
        outline: none;
    }
    .form-label {
        color: #b0b0b0;
        font-size: 0.9rem;
    }
    .btn-primary {
        background-color: #00cc00;
        border: none;
        border-radius: 8px;
        padding: 0.75rem;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .btn-primary:hover {
        background-color: #00b300;
        transform: scale(1.05);
    }
    .btn-outline-success {
        border-color: #00cc00;
        color: #00cc00;
    }
    .btn-outline-success:hover {
        background-color: #00b300;
        color: #ffffff;
    }
    .btn-outline-info {
        border-color: #17a2b8;
        color: #17a2b8;
        transition: all 0.3s ease;
    }
    .btn-outline-info:hover {
        background-color: #17a2b8;
        color: #ffffff;
        transform: scale(1.02);
    }
    .tooltip-icon {
        display: inline-block;
        width: 16px;
        height: 16px;
        background-color: #00cc00;
        color: #1c2526;
        border-radius: 50%;
        text-align: center;
        line-height: 16px;
        cursor: help;
        font-size: 12px;
        position: relative;
    }
    .tooltip-text {
        visibility: hidden;
        width: 200px;
        background-color: #2a2f31;
        color: #ffffff;
        text-align: center;
        border-radius: 6px;
        padding: 8px;
        position: absolute;
        z-index: 1;
        top: 125%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .tooltip-icon:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
    .error-text {
        color: #ff4d4d;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }
    .file-input::-webkit-file-upload-button {
        background-color: #00cc00;
        color: #ffffff;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .file-input::-webkit-file-upload-button:hover {
        background-color: #00b300;
    }
    .modal-content {
        background-color: #1c2526;
        border: 1px solid #3a3f41;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    .animation-delay-1 { animation-delay: 0.1s; }
    .animation-delay-2 { animation-delay: 0.2s; }
    .animation-delay-3 { animation-delay: 0.3s; }
    .animation-delay-4 { animation-delay: 0.4s; }
</style>
@endsection