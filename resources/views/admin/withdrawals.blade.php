<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Withdrawals</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Withdrawal Requests</h1>
        
        <!-- Toast Notification Container -->
        <div id="toastContainer" class="fixed top-4 right-4 z-50">
            <!-- Toast messages will be appended here -->
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($withdrawals as $withdrawal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $withdrawal->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">₦{{ number_format($withdrawal->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php $bank = json_decode($withdrawal->bank_account, true); @endphp
                                    {{ $bank['bank_name'] }} / {{ $bank['account_number'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($withdrawal->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($withdrawal->status === 'approved') bg-green-100 text-green-800
                                        @elseif($withdrawal->status === 'cancelled') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if ($withdrawal->status === 'pending')
                                        <button onclick="showConfirmModal('approve', {{ $withdrawal->id }})" 
                                                class="text-green-600 hover:text-green-900 mr-4">Approve</button>
                                        <button onclick="showConfirmModal('cancel', {{ $withdrawal->id }})" 
                                                class="text-red-600 hover:text-red-900">Cancel</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirmModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 id="modalTitle" class="text-lg leading-6 font-medium text-gray-900"></h3>
                    <div class="mt-2 px-7 py-3">
                        <p id="modalMessage" class="text-sm text-gray-500"></p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <form id="actionForm" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-24 mr-2 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Confirm
                            </button>
                            <button type="button" onclick="closeModal()" 
                                    class="w-24 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showConfirmModal(action, withdrawalId) {
            const modal = $('#confirmModal');
            const form = $('#actionForm');
            const title = $('#modalTitle');
            const message = $('#modalMessage');

            if (action === 'approve') {
                title.text('Approve Withdrawal');
                message.text('Are you sure you want to approve this withdrawal?');
                form.attr('action', '{{ route("withdraw.approve", ":id") }}'.replace(':id', withdrawalId));
            } else {
                title.text('Cancel Withdrawal');
                message.text('Are you sure you want to cancel this withdrawal?');
                form.attr('action', '{{ route("withdraw.cancel", ":id") }}'.replace(':id', withdrawalId));
            }

            modal.removeClass('hidden');
        }

        function closeModal() {
            $('#confirmModal').addClass('hidden');
        }

        function showToast(message, type) {
            const toast = $('<div>', {
                class: `p-4 mb-4 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`,
                text: message
            }).appendTo('#toastContainer');

            setTimeout(() => {
                toast.fadeOut(300, () => toast.remove());
            }, 3000);
        }

        // Handle form submission with AJAX
        $('#actionForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const actionUrl = form.attr('action');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                },
                success: function(response) {
                    closeModal();
                    showToast(response.message || 'Action completed successfully!', 'success');
                    // Reload the page to refresh the withdrawal list
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    closeModal();
                    const errorMessage = xhr.responseJSON?.error || 'An error occurred. Please try again.';
                    showToast(errorMessage, 'error');
                }
            });
        });

        // Close modal when clicking outside
        $('#confirmModal').click(function(e) {
            if (e.target.id === 'confirmModal') {
                closeModal();
            }
        });

        // Display flash messages from Laravel (if redirected)
        @if (session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        @if (session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif
    </script>
</body>
</html>