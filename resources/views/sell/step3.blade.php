<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Crypto - Step 3</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            min-height: 100vh;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .form-label {
            color: #1a3c34;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2e6b5e;
            box-shadow: 0 0 0 0.2rem rgba(46, 107, 94, 0.25);
            outline: none;
        }
        .form-check-input {
            border-radius: 4px;
            border: 2px solid #d1d5db;
        }
        .form-check-input:checked {
            background-color: #2e6b5e;
            border-color: #2e6b5e;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1a3c34, #2e6b5e);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2e6b5e, #1a3c34);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .btn-primary:disabled {
            background: #6b7280;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-primary.loading::after {
            content: '';
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
        }
        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }
        .error-border {
            border-color: #dc2626 !important;
            animation: shake 0.3s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .alert {
            border-radius: 8px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 0.9rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .toast {
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .toast-header {
            background: linear-gradient(135deg, #1a3c34, #2e6b5e);
            color: white;
            border-radius: 8px 8px 0 0;
        }
        .toast-body {
            background: white;
            color: #dc2626;
            font-weight: 500;
        }
        .form-check-label {
            color: #4b5563;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }
        .form-check-input:checked + .form-check-label {
            color: #1a3c34;
            font-weight: 500;
        }
        .slide-up {
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (max-width: 768px) {
            .card {
                padding: 1.5rem;
            }
            .btn-primary {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
            .form-label {
                font-size: 0.9rem;
            }
            .form-control, .form-select {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center">
            <div class="w-full max-w-lg">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-body p-6 sm:p-8">
                        <h1 class="text-2xl sm:text-3xl font-bold text-center text-gray-800 mb-6">Sell Crypto - Payout Method</h1>

                        @if ($errors->any())
                            <div class="alert alert-danger animate__animated animate__fadeInDown">
                                <ul class="mb-0 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="sellForm" action="{{ route('sell.finalize') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label class="form-label block mb-2">Payout Method</label>
                                <div class="space-y-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payout_method" value="default_bank" id="default_bank" required>
                                        <label class="form-check-label" for="default_bank">
                                            Default Bank ({{ $userData['bank_name'] }} - {{ $userData['account_number'] }})
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payout_method" value="external_bank" id="external_bank">
                                        <label class="form-check-label" for="external_bank">
                                            External Bank
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payout_method" value="wallet_balance" id="wallet_balance">
                                        <label class="form-check-label" for="wallet_balance">
                                            Wallet Balance (₦{{ number_format($balance, 2) }})
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="external_bank_fields" class="mb-6 hidden slide-up">
                                <div class="mb-4">
                                    <label for="alt_bank" class="form-label">Bank Name</label>
                                    <select name="alt_bank" id="alt_bank" class="form-select">
                                        <option value="">Select Bank</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank['name'] }}">{{ $bank['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="alt_account_number" class="form-label">Account Number</label>
                                    <input type="text" name="alt_account_number" id="alt_account_number" maxlength="10" class="form-control">
                                </div>
                                <div class="mb-4">
                                    <label for="alt_account_name" class="form-label">Account Name</label>
                                    <input type="text" name="alt_account_name" id="alt_account_name" readonly class="form-control bg-gray-100">
                                </div>
                                <div class="mb-4">
                                    <label for="external_password" class="form-label">Password</label>
                                    <input type="password" name="password" id="external_password" class="form-control" placeholder="Enter your password">
                                </div>
                                <button type="button" id="validate_bank" class="btn btn-primary w-full hidden">
                                    Validate Bank
                                </button>
                            </div>

                            <div id="password_field" class="mb-6 hidden slide-up">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" name="password" id="confirm_password" class="form-control" placeholder="Enter your password">
                            </div>

                            <button type="submit" id="submit_button" disabled class="btn btn-primary w-full">
                                Finalize Trade
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Bank validation failed. Please check your details or click "Validate Bank".
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Handle payout method selection
            $('input[name="payout_method"]').change(function () {
                $('#external_bank_fields, #password_field').addClass('hidden');
                $('#submit_button').prop('disabled', true).addClass('btn-primary:disabled');
                $('#external_password, #confirm_password, #alt_bank, #alt_account_number').val('').removeClass('error-border');
                $('#alt_account_name').val('');
                $('#validate_bank').addClass('hidden');

                const method = $(this).val();
                if (method === 'external_bank') {
                    $('#external_bank_fields').removeClass('hidden');
                } else {
                    $('#password_field').removeClass('hidden');
                    $('#submit_button').prop('disabled', false).removeClass('btn-primary:disabled');
                }
            });

            // Automatic bank validation on account number input
            let isValidating = false;
            $('#alt_account_number').on('input', function () {
                const accountNumber = $(this).val();
                const bankName = $('#alt_bank').val();
                const password = $('#external_password').val();

                if (accountNumber.length === 10 && bankName && password && !isValidating) {
                    isValidating = true;
                    $('#submit_button').addClass('loading').prop('disabled', true);

                    $.ajax({
                        url: '{{ route('sell.validateBank') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            bank_name: bankName,
                            account_number: accountNumber,
                            password: password
                        },
                        success: function (response) {
                            $('#alt_account_name').val(response.account_name);
                            $('#submit_button').prop('disabled', false).removeClass('btn-primary:disabled loading');
                            $('#alt_bank, #alt_account_number, #external_password').removeClass('error-border');
                        },
                        error: function (xhr) {
                            $('#validate_bank').removeClass('hidden');
                            $('#alt_bank, #alt_account_number, #external_password').addClass('error-border');
                            $('#submit_button').removeClass('loading').prop('disabled', true);

                            const toastElement = document.getElementById('errorToast');
                            const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
                            toast.show();
                        },
                        complete: function () {
                            isValidating = false;
                        }
                    });
                }
            });

            // Manual bank validation
            $('#validate_bank').click(function () {
                const bankName = $('#alt_bank').val();
                const accountNumber = $('#alt_account_number').val();
                const password = $('#external_password').val();

                if (!bankName || !accountNumber || !password) {
                    if (!bankName) $('#alt_bank').addClass('error-border');
                    if (!accountNumber) $('#alt_account_number').addClass('error-border');
                    if (!password) $('#external_password').addClass('error-border');
                    return;
                }

                $('#validate_bank').addClass('loading').prop('disabled', true);

                $.ajax({
                    url: '{{ route('sell.validateBank') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        bank_name: bankName,
                        account_number: accountNumber,
                        password: password
                    },
                    success: function (response) {
                        $('#alt_account_name').val(response.account_name);
                        $('#submit_button').prop('disabled', false).removeClass('btn-primary:disabled');
                        $('#alt_bank, #alt_account_number, #external_password').removeClass('error-border');
                        $('#validate_bank').addClass('hidden').removeClass('loading').prop('disabled', false);
                    },
                    error: function (xhr) {
                        $('#alt_bank, #alt_account_number, #external_password').addClass('error-border');
                        $('#validate_bank').removeClass('loading').prop('disabled', false);

                        const toastElement = document.getElementById('errorToast');
                        const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
                        toast.show();
                    }
                });
            });

            // Clear error borders on input
            $('#alt_bank, #alt_account_number, #external_password, #confirm_password').on('input', function () {
                $(this).removeClass('error-border');
            });

            // Validate password on form submission
            $('#sellForm').submit(function (event) {
                const method = $('input[name="payout_method"]:checked').val();
                const password = method === 'external_bank' ? $('#external_password').val() : $('#confirm_password').val();

                if (!password) {
                    event.preventDefault();
                    (method === 'external_bank' ? $('#external_password') : $('#confirm_password')).addClass('error-border');

                    const toastElement = document.getElementById('errorToast');
                    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
                    toastElement.querySelector('.toast-body').textContent = 'Please enter your password to finalize the trade.';
                    toast.show();
                } else {
                    $('#submit_button').addClass('loading').prop('disabled', true);
                }
            });

            // Ensure bank name or password changes trigger validation
            $('#alt_bank, #external_password').on('change input', function () {
                $('#alt_account_name').val('');
                $('#submit_button').prop('disabled', true).addClass('btn-primary:disabled');
                $('#validate_bank').addClass('hidden');
                if ($('#alt_account_number').val().length === 10) {
                    $('#alt_account_number').trigger('input');
                }
            });
        });
    </script>
</body>
</html>