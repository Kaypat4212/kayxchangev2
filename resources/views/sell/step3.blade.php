@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 py-12 px-4 sm:px-6">
    <div class="max-w-4xl mx-auto">
        <!-- Stepper -->
        <div class="mb-10 flex justify-center">
            <div class="flex items-center">
                <div class="flex items-center text-white relative">
                    <div class="rounded-full h-8 w-8 bg-blue-600 flex items-center justify-center">1</div>
                    <div class="absolute top-0 -ml-4 text-xs font-medium text-blue-600 mt-9 w-16 text-center">
                        Select Coin
                    </div>
                </div>
                <div class="flex-auto border-t-2 border-blue-600"></div>
                <div class="flex items-center text-white relative">
                    <div class="rounded-full h-8 w-8 bg-blue-600 flex items-center justify-center">2</div>
                    <div class="absolute top-0 -ml-4 text-xs font-medium text-blue-600 mt-9 w-16 text-center">
                        Set Amount
                    </div>
                </div>
                <div class="flex-auto border-t-2 border-blue-600"></div>
                <div class="flex items-center text-white relative">
                    <div class="rounded-full h-10 w-10 bg-blue-600 flex items-center justify-center ring-4 ring-blue-100">3</div>
                    <div class="absolute top-0 -ml-4 text-xs font-medium text-blue-600 mt-9 w-16 text-center">
                        Payment Method
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 p-6">
                <h2 class="text-2xl font-bold text-center text-white">
                    Choose Payout Method
                </h2>
                <p class="text-center text-blue-100 mt-2">
                    Select how you'd like to receive your funds
                </p>
            </div>

            <!-- Form Content -->
            <div class="p-6 md:p-8">
                <form id="payoutForm" action="{{ route('sell.finalize') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- Payout Selection -->
                    <div class="mb-6">
                        <label for="payout_method" class="block mb-2 text-lg font-semibold text-gray-700">Select Payout Method</label>
                        <select name="payout_method" id="payout_method" required class="w-full border-gray-300 rounded-lg p-4 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">-- Choose Option --</option>
                            <option value="default_bank">My Default Bank</option>
                            <option value="wallet_balance">Add to Wallet Balance</option>
                        </select>
                    </div>
                    
                    <!-- Default Bank Info (Readonly) -->
                    <div id="default-bank-section" class="space-y-4 hidden animate-fade-in">
                        <label class="block text-sm font-medium text-gray-600">Bank Details</label>
                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Bank Name</p>
                                    <p class="font-semibold">{{ $userData['bank_name'] }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Account Number</p>
                                    <p class="font-semibold">{{ $userData['account_number'] }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Account Holder</p>
                                    <p class="font-semibold">{{ $userData['account_name'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wallet Balance Info -->
                    <div id="wallet-balance-section" class="space-y-4 hidden animate-fade-in">
                        <label class="block text-sm font-medium text-gray-600">Wallet Balance</label>
                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <p class="text-2xl font-semibold text-gray-800">{{ $userData['wallet_balance'] }} Naira</p>
                            <p class="text-sm text-gray-500 mt-2">
                                Your trade amount will be added to this balance
                            </p>
                        </div>
                    </div>
                    
                    <!-- Submit -->
                    <div id="submit-btn-wrapper" class="pt-4 hidden">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-300">
                            Finalize Trade
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-6 text-center text-gray-500 text-sm">
            Need help? Contact our support team at support@tradefinance.com
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-5 right-5 bg-green-600 text-white p-4 rounded-lg shadow-lg">
    <p class="text-sm">Your trade will be added to your wallet balance.</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const payoutSelect = document.getElementById('payout_method');
        const defaultSection = document.getElementById('default-bank-section');
        const walletBalanceSection = document.getElementById('wallet-balance-section');
        const submitBtnWrapper = document.getElementById('submit-btn-wrapper');
        const toast = document.getElementById('toast');

        function hideAllSections() {
            defaultSection.classList.add('hidden');
            walletBalanceSection.classList.add('hidden');
            submitBtnWrapper.classList.add('hidden');
        }

        payoutSelect.addEventListener('change', function () {
            const value = this.value;
            hideAllSections();

            if (value === 'default_bank') {
                defaultSection.classList.remove('hidden');
                submitBtnWrapper.classList.remove('hidden');
            } else if (value === 'wallet_balance') {
                walletBalanceSection.classList.remove('hidden');
                submitBtnWrapper.classList.remove('hidden');
                // Show toast notification when "Add to Wallet Balance" is selected
                toast.classList.remove('hidden');
                setTimeout(function () {
                    toast.classList.add('hidden');
                }, 4000); // Hide after 4 seconds
            }
        });
    });
</script>
@endsection
