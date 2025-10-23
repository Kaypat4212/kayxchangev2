@extends('layout')

@section('content')
<div class="container my-5">
    <h4>Support Chat Dashboard</h4>
    <div class="row">
        <div class="col-md-4">
            <h5>Users</h5>
            <ul class="list-group">
                @foreach ($users as $user)
                    <li class="list-group-item" data-user-id="{{ $user->id }}">{{ $user->name }}</li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-8">
            <div id="admin-chat-widget" class="flex flex-col h-96 bg-white shadow-lg rounded-lg overflow-hidden">
                <div id="admin-chat-header" class="bg-primary text-white p-3">Select a user to chat</div>
                <div id="admin-chat-messages" class="flex-1 overflow-y-auto p-3 bg-gray-100"></div>
                <div class="p-3">
                    <input type="text" id="admin-chat-input" class="form-control w-full" placeholder="Type your message..." disabled />
                    <button id="admin-send-message" class="btn btn-primary mt-2 w-full" disabled>Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="{{ asset('js/echo.js') }}"></script>
<script>
$(document).ready(function() {
    const chatMessages = $('#admin-chat-messages');
    const chatInput = $('#admin-chat-input');
    const sendMessageBtn = $('#admin-send-message');
    const chatHeader = $('#admin-chat-header');
    let selectedUserId = null;

    Pusher.logToConsole = true;
    Echo.private(`chat.admin`)
        .listen('MessageSent', (e) => {
            if (e.user.id === parseInt(selectedUserId)) {
                appendMessage(e.message, 'user');
            }
        });

    $('.list-group-item').click(function() {
        selectedUserId = $(this).data('user-id');
        chatHeader.text(`Chatting with ${$(this).text()}`);
        chatInput.prop('disabled', false);
        sendMessageBtn.prop('disabled', false);
        chatMessages.empty();

        $.get(`/chat/history/${selectedUserId}`, function(messages) {
            messages.forEach(msg => {
                appendMessage(msg, msg.sender_id === parseInt(selectedUserId) ? 'user' : 'support');
            });
        });
    });

    sendMessageBtn.click(function() {
        const message = chatInput.val().trim();
        if (message && selectedUserId) {
            $.ajax({
                url: '/chat/send/admin',
                method: 'POST',
                data: {
                    message: message,
                    receiver_id: selectedUserId,
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    chatInput.val('');
                },
                error: function(xhr) {
                    console.log('Admin chat send error:', xhr.responseText);
                }
            });
        }
    });

    chatInput.keypress(function(e) {
        if (e.which === 13 && chatInput.val().trim() && selectedUserId) {
            sendMessageBtn.click();
        }
    });

    function appendMessage(message, type) {
        const messageClass = type === 'user' ? 'text-left bg-gray-200' : 'text-right bg-primary text-white';
        const html = `
            <div class="mb-2 p-2 rounded ${messageClass}">
                <p class="text-sm">${message.content}</p>
                <span class="text-xs text-gray-500">${new Date(message.created_at).toLocaleTimeString()}</span>
            </div>
        `;
        chatMessages.append(html);
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }
});
</script>
@endsection