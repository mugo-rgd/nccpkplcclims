@extends('layouts.app')

@section('title', 'Chat')

@section('content')
<div class="container-fluid chat-container">
    <div class="row g-0 h-100">
        <div class="col-md-4 col-lg-3 h-100">
            <div class="chat-sidebar">
                <div class="chat-header">
                    <h5 class="mb-0">
                        <i class="fas fa-comment-dots"></i> Messages
                    </h5>

                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newChatModal">
                        <i class="fas fa-plus"></i> New Chat
                    </button>
                </div>

                <div class="chat-search">
                    <div class="input-group">
                        <input type="text" id="conversationSearch" class="form-control" placeholder="Search conversations...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div id="conversationsList" class="conversations-list">
                    <div class="text-center text-muted p-3">
                        <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                        <p class="mb-0">Loading conversations...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-lg-9 h-100">
            <div class="chat-main">
                <div class="chat-header">
                    <div id="conversationInfo">
                        <h6 class="mb-0">Select a conversation</h6>
                        <span class="text-muted small">Start messaging</span>
                    </div>

                    <div class="chat-header-actions">
                        <button class="btn btn-sm btn-outline-secondary" type="button">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" type="button">
                            <i class="fas fa-video"></i>
                        </button>
                    </div>
                </div>

                <div id="messagesContainer" class="messages-container">
                    <div class="text-center text-muted mt-5">
                        <i class="fas fa-comment-dots fa-3x d-block mb-3"></i>
                        <p>Select a conversation to start messaging</p>
                    </div>
                </div>

                <div id="typingIndicator" class="typing-indicator" style="display: none;">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="chat-input-area">
                    <div class="chat-input-container">
                        <button class="btn btn-link" id="attachFileBtn" type="button">
                            <i class="fas fa-paperclip"></i>
                        </button>

                        <input type="file" id="fileInput" style="display: none;">

                        <textarea id="messageInput" class="form-control" rows="1" placeholder="Type a message..."></textarea>

                        <button class="btn btn-primary" id="sendMessageBtn" type="button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="newChatModalLabel" class="modal-title">Start New Conversation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="recipientSelect" class="form-label">Select User</label>
                    <select id="recipientSelect" class="form-select">
                        <option value="">Select a user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="newMessageText" class="form-label">Message</label>
                    <textarea id="newMessageText" class="form-control" rows="3" placeholder="Type your message..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-primary" id="startNewChatBtn">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.chat-container {
    height: calc(100vh - 120px);
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.chat-sidebar {
    height: 100%;
    border-right: 1px solid #e9ecef;
    background: #f8f9fa;
}

.chat-sidebar .chat-header,
.chat-main .chat-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-search {
    padding: 10px 15px;
    border-bottom: 1px solid #e9ecef;
}

.conversations-list {
    height: calc(100% - 120px);
    overflow-y: auto;
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s;
    position: relative;
}

.conversation-item:hover {
    background: #e9ecef;
}

.conversation-item.active {
    background: #e3f2fd;
}

.conversation-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 12px;
    flex-shrink: 0;
    background: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.conversation-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-name {
    font-weight: 600;
    font-size: 14px;
    color: #333;
}

.conversation-last-message {
    font-size: 13px;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    background: #0d6efd;
    color: white;
    border-radius: 50%;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
    margin-left: auto;
}

.chat-main {
    height: 100%;
    display: flex;
    flex-direction: column;
    background: white;
}

.messages-container {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 12px;
}

.message.sent {
    align-self: flex-end;
    background: #0d6efd;
    color: white;
}

.message.received {
    align-self: flex-start;
    background: #f1f3f5;
    color: #333;
}

.sender-name {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 3px;
    color: #6c757d;
}

.message-text {
    word-wrap: break-word;
}

.message-time {
    font-size: 10px;
    margin-top: 3px;
    opacity: 0.7;
    text-align: right;
}

.typing-indicator {
    padding: 10px 20px;
    display: flex;
    gap: 5px;
    align-items: center;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #6c757d;
    border-radius: 50%;
}

.chat-input-area {
    padding: 15px 20px;
    border-top: 1px solid #e9ecef;
}

.chat-input-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.chat-input-container textarea {
    flex: 1;
    resize: none;
    border-radius: 20px;
    padding: 8px 15px;
    max-height: 100px;
}

@media (max-width: 768px) {
    .chat-container {
        height: calc(100vh - 100px);
    }

    .message {
        max-width: 85%;
    }
}
</style>
@endpush

@push('scripts')
<script>
    window.userId = @json(auth()->id());
    window.userName = @json(auth()->user()->name);
    window.csrfToken = @json(csrf_token());
</script>

<script src="{{ asset('js/chat.js') }}"></script>
@endpush
