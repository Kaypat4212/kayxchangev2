@extends('layout')

@section('content')
<div class="container my-5">
    <h4>Edit Bank Information</h4>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('update.bank') }}">
        @csrf
        <div class="form-group mb-3">
            <label for="bank_code">Bank Name</label>
            <select name="bank_code" id="bank_code" class="form-control" {{ $bankDetailsSet ? 'disabled' : '' }} required>
                <option value="">Select a bank</option>
            </select>
            @error('bank_code')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label for="account_number">Account Number</label>
            <input type="text" name="account_number" id="account_number" class="form-control" 
                   value="{{ old('account_number', $user->account_number) }}" 
                   {{ $bankDetailsSet ? 'disabled' : '' }} required>
            @error('account_number')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <div id="account-name-result" class="mt-2"></div>
        </div>
        <div class="form-group mb-3">
            <label for="account_name">Account Name</label>
            <input type="text" name="account_name" id="account_name" class="form-control" 
                   value="{{ old('account_name', $user->account_name) }}" 
                   {{ $bankDetailsSet ? 'disabled' : '' }} readonly required>
            @error('account_name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" 
                   {{ $bankDetailsSet ? 'disabled' : '' }} required>
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        @if (!$bankDetailsSet)
            <button type="submit" class="btn btn-success" id="submit-btn" disabled>Update Bank Info</button>
        @else
            <p class="text-muted">
                Bank details cannot be edited once set. To update your bank details, please contact support via 
                <a href="#" id="open-chat" class="text-primary">live chat</a> or email us at 
                <a href="mailto:info@kayxchange.net" class="text-primary">info@kayxchange.net</a>.
            </p>
        @endif
    </form>
</div>

<!-- Live Chat Popup -->
<div id="chat-popup" class="fixed bottom-0 right-0 mb-4 mr-4 w-80 bg-white shadow-lg rounded-lg overflow-hidden hidden z-50">
    <div class="bg-primary text-white p-3 flex justify-between items-center">
        <span>Support Chat</span>
        <button id="close-chat" class="text-white text-lg">&times;</button>
    </div>
    <div id="chat-messages" class="h-64 overflow-y-auto p-3 bg-light">
        <!-- Messages will be appended here -->
    </div>
    <div class="p-3 border-top">
        <input type="text" id="chat-input" class="form-control mb-2" placeholder="Type your message..." />
        <button id="send-message" class="btn btn-primary w-100">Send</button>
    </div>
</div>

<!-- Chat Toggle Button -->
<button id="chat-toggle" class="fixed bottom-4 right-4 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 50px; height: 50px;">
    <span class="fs-5">💬</span>
</button>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="{{ asset('js/echo.js') }}"></script>
<script>
$(document).ready(function() {
    // Fetch banks
    $.ajax({
        url: '{{ route('paystack.banks') }}',
        method: 'GET',
        success: function(response) {
            if (response.status && response.data) {
                response.data.forEach(function(bank) {
                    $('#bank_code').append(`<option value="${bank.code}">${bank.name}</option>`);
                });
                @if($user->bank_code)
                    $('#bank_code').val('{{ $user->bank_code }}');
                @endif
            } else {
                $('#account-name-result').html('<span class="text-danger">Failed to load banks: ' + (response.message || 'Unknown error') + '</span>');
            }
        },
        error: function(xhr) {
            $('#account-name-result').html('<span class="text-danger">Failed to load banks. Please try again.</span>');
        }
    });

    // Real-time account number validation
    $('#account_number').on('input', function() {
        let accountNumber = $(this).val();
        let bankCode = $('#bank_code').val();
        if (accountNumber.length >= 10 && bankCode) {
            $('#account-name-result').html('<span class="text-info">Validating...</span>');
            $.ajax({
                url: '{{ route('paystack.resolve-account') }}',
                method: 'POST',
                data: {
                    account_number: accountNumber,
                    bank_code: bankCode,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status && response.data) {
                        $('#account_name').val(response.data.account_name);
                        $('#account-name-result').html('<span class="text-success">Account validated successfully!</span>');
                        $('#submit-btn').prop('disabled', false);
                    } else {
                        $('#account_name').val('');
                        $('#account-name-result').html('<span class="text-danger">Invalid account: ' + (response.message || 'Unknown error') + '</span>');
                        $('#submit-btn').prop('disabled', true);
                    }
                },
                error: function(xhr) {
                    $('#account_name').val('');
                    $('#account-name-result').html('<span class="text-danger">Validation failed. Please try again.</span>');
                    $('#submit-btn').prop('disabled', true);
                }
            });
        } else {
            $('#account_name').val('');
            $('#account-name-result').html('');
            $('#submit-btn').prop('disabled', true);
        }
    });

    $('#bank_code').on('change', function() {
        $('#account_number').trigger('input');
    });

    // Live Chat Functionality
    const userId = {{ auth()->id() }};
    const chatPopup = $('#chat-popup');
    const chatMessages = $('#chat-messages');
    const chatInput = $('#chat-input');
    const sendMessageBtn = $('#send-message');
    const chatToggle = $('#chat-toggle');
    const openChatLink = $('#open-chat');
    const closeChatBtn = $('#close-chat');

    // Initialize Pusher and Echo
    Pusher.logToConsole = true; // Debugging
    Echo.private(`chat.${userId}`)
        .listen('MessageSent', (e) => {
            appendMessage(e.message.content, e.message.sender_id === userId ? 'sent' : 'received', e.message.created_at);
        });

    // Load chat history
    $.get('{{ route('chat.history') }}', function(messages) {
        messages.forEach(msg => {
            appendMessage(msg.content, msg.sender_id === userId ? 'sent' : 'received', msg.created_at);
        });
    });

    // Toggle chat popup
    chatToggle.click(function() {
        chatPopup.toggleClass('hidden');
        chatToggle.toggleClass('hidden');
    });

    openChatLink.click(function(e) {
        e.preventDefault();
        chatPopup.removeClass('hidden');
        chatToggle.addClass('hidden');
    });

    closeChatBtn.click(function() {
        chatPopup.addClass('hidden');
        chatToggle.removeClass('hidden');
    });

    // Send message
    sendMessageBtn.click(function() {
        const message = chatInput.val().trim();
        if (message) {
            $.ajax({
                url: '{{ route('chat.send') }}',
                method: 'POST',
                data: {
                    message: message,
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    chatInput.val('');
                },
                error: function(xhr) {
                    alert('Failed to send message: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }
    });

    chatInput.keypress(function(e) {
        if (e.which === 13 && chatInput.val().trim()) {
            sendMessageBtn.click();
        }
    });

    function appendMessage(content, type, createdAt) {
        const alignment = type === 'sent' ? 'text-end' : 'text-start';
        const bgColor = type === 'sent' ? 'bg-primary text-white' : 'bg-light';
        const time = new Date(createdAt).toLocaleTimeString();
        const html = `
            <div class="mb-2 p-2 rounded ${alignment} ${bgColor}">
                <p class="mb-1">${content}</p>
                <small class="text-muted">${time}</small>
            </div>
        `;
        chatMessages.append(html);
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }
});
</script>

<style>
#chat-popup { transition: all 0.3s ease; }
#chat-messages { scrollbar-width: thin; }
#chat-messages::-webkit-scrollbar { width: 6px; }
#chat-messages::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
</style>
@endsection