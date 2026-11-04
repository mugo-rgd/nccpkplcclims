class ChatManager {
    constructor() {
        console.log('📱 ChatManager initializing...');

        this.currentConversation = null;
        this.typingTimeout = null;
        this.pollingInterval = null;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadConversations();
        this.updateUnreadCount();
        this.startPolling();

        console.log('✅ ChatManager initialized');
    }

    setupEventListeners() {
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendMessageBtn');
        const startChatBtn = document.getElementById('startNewChatBtn');
        const fileInput = document.getElementById('fileInput');
        const attachBtn = document.getElementById('attachFileBtn');

        if (messageInput) {
            messageInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            messageInput.addEventListener('input', () => {
                this.sendTypingIndicator(true);

                clearTimeout(this.typingTimeout);

                this.typingTimeout = setTimeout(() => {
                    this.sendTypingIndicator(false);
                }, 2000);
            });
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', () => {
                this.sendMessage();
            });
        }

        if (startChatBtn) {
            startChatBtn.addEventListener('click', () => {
                this.startNewChat();
            });
        }

        if (attachBtn) {
            attachBtn.addEventListener('click', () => {
                if (fileInput) {
                    fileInput.click();
                }
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    this.handleFileUpload(e.target.files[0]);
                    fileInput.value = '';
                }
            });
        }
    }

    loadConversations() {
        const container = document.getElementById('conversationsList');

        if (!container) {
            console.error('❌ conversationsList not found');
            return;
        }

        container.innerHTML = `
            <div class="text-center text-muted p-3">
                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                <p class="mb-0">Loading conversations...</p>
            </div>
        `;

        axios.get('/chat/conversations')
            .then((response) => {
                this.renderConversations(response.data);

                if (response.data.length > 0 && !this.currentConversation) {
                    this.loadConversation(response.data[0].id);
                }
            })
            .catch((error) => {
                console.error('❌ Error loading conversations:', error);

                container.innerHTML = `
                    <div class="text-center text-danger p-3">
                        <i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>
                        <p>Failed to load conversations</p>
                        <button class="btn btn-sm btn-primary" type="button" onclick="window.chat.loadConversations()">
                            <i class="fas fa-redo"></i> Retry
                        </button>
                    </div>
                `;
            });
    }

    renderConversations(conversations) {
        const container = document.getElementById('conversationsList');

        if (!container) {
            return;
        }

        if (!conversations || conversations.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted p-3">
                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                    <p>No conversations yet</p>
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#newChatModal">
                        <i class="fas fa-plus"></i> Start a chat
                    </button>
                </div>
            `;
            return;
        }

        let html = '';

        conversations.forEach((conv) => {
            const unreadCount = conv.unread_count || 0;
            const lastMessage = conv.latest_message;
            let displayName = conv.name || 'Conversation';
            let avatarHtml = '<i class="fas fa-users"></i>';

            if (conv.type === 'private') {
                const otherParticipant = conv.participants?.find((p) => Number(p.user_id) !== Number(window.userId));

                if (otherParticipant?.user) {
                    displayName = otherParticipant.user.name;
                    avatarHtml = `<i class="fas fa-user"></i>`;
                }
            }

            html += `
                <div class="conversation-item ${Number(conv.id) === Number(this.currentConversation) ? 'active' : ''}"
                     data-conversation-id="${conv.id}"
                     onclick="window.chat.loadConversation(${conv.id})">
                    <div class="conversation-avatar">
                        ${avatarHtml}
                    </div>

                    <div class="conversation-info">
                        <div class="conversation-name">${this.escapeHtml(displayName)}</div>
                        <div class="conversation-last-message">
                            ${lastMessage ? this.escapeHtml(lastMessage.message) : 'No messages'}
                        </div>
                    </div>

                    ${unreadCount > 0 ? `<span class="unread-badge">${unreadCount}</span>` : ''}
                </div>
            `;
        });

        container.innerHTML = html;
    }

    loadConversation(conversationId) {
        this.currentConversation = conversationId;

        document.querySelectorAll('.conversation-item').forEach((el) => {
            el.classList.remove('active');

            if (Number(el.dataset.conversationId) === Number(conversationId)) {
                el.classList.add('active');
            }
        });

        const container = document.getElementById('messagesContainer');

        if (!container) {
            return;
        }

        container.innerHTML = `
            <div class="text-center text-muted mt-5">
                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                <p>Loading messages...</p>
            </div>
        `;

        axios.get(`/chat/conversations/${conversationId}`)
            .then((response) => {
                this.renderMessages(response.data.messages);
                this.updateConversationInfo(response.data.conversation);
                this.markAsRead(conversationId);
            })
            .catch((error) => {
                console.error('❌ Error loading conversation:', error);

                container.innerHTML = `
                    <div class="text-center text-danger mt-5">
                        <i class="fas fa-exclamation-circle fa-2x d-block mb-3"></i>
                        <p>Failed to load messages</p>
                        <button class="btn btn-sm btn-primary" type="button" onclick="window.chat.loadConversation(${conversationId})">
                            <i class="fas fa-redo"></i> Retry
                        </button>
                    </div>
                `;
            });
    }

    renderMessages(messages) {
        const container = document.getElementById('messagesContainer');

        if (!container) {
            return;
        }

        container.innerHTML = '';

        if (!messages || messages.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted mt-5">
                    <i class="fas fa-comment-dots fa-3x d-block mb-3"></i>
                    <p>No messages yet. Start the conversation.</p>
                </div>
            `;
            return;
        }

        messages.forEach((message) => {
            const isMine = Number(message.sender_id) === Number(window.userId);
            this.appendMessage(message, isMine);
        });

        container.scrollTop = container.scrollHeight;
    }

    appendMessage(message, isMine) {
    const container = document.getElementById('messagesContainer');

    if (!container) {
        return;
    }

    const placeholder = container.querySelector('.text-center.text-muted');

    if (placeholder) {
        placeholder.remove();
    }

    const div = document.createElement('div');
    div.className = `message ${isMine ? 'sent' : 'received'}`;

    const senderName = message.sender_name || message.sender?.name || 'Unknown';
    const time = message.formatted_time || this.formatTime(message.created_at);

    let messageBody = '';

    if (message.type === 'file') {
        const fileName = message.file_name || message.message || 'Attachment';
        const fileUrl = message.file_url || `/storage/${message.file_path}`;

        messageBody = `
            <div class="message-file">
                <a href="${this.escapeHtml(fileUrl)}"
                   target="_blank"
                   class="${isMine ? 'text-white' : 'text-primary'}"
                   download>
                    <i class="fas fa-paperclip me-1"></i>
                    ${this.escapeHtml(fileName)}
                </a>
            </div>
        `;
    } else {
        messageBody = `
            <div class="message-text">
                ${this.escapeHtml(message.message || '')}
            </div>
        `;
    }

    div.innerHTML = `
        <div class="message-content">
            ${!isMine ? `<div class="sender-name">${this.escapeHtml(senderName)}</div>` : ''}
            ${messageBody}
            <div class="message-time">${this.escapeHtml(time)}</div>
        </div>
    `;

    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

    updateConversationInfo(conversation) {
        const infoContainer = document.getElementById('conversationInfo');

        if (!infoContainer || !conversation) {
            return;
        }

        let displayName = conversation.name || 'Conversation';

        if (conversation.type === 'private') {
            const otherParticipant = conversation.participants?.find((p) => Number(p.user_id) !== Number(window.userId));
            displayName = otherParticipant?.user?.name || 'Private Chat';
        }

        infoContainer.innerHTML = `
            <h6 class="mb-0">${this.escapeHtml(displayName)}</h6>
            <span class="text-muted small">${conversation.type === 'private' ? 'Private Chat' : 'Group Chat'}</span>
        `;
    }

    sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input?.value?.trim();

        if (!message) {
            return;
        }

        if (!this.currentConversation) {
            alert('Please select a conversation first.');
            return;
        }

        axios.post(`/chat/conversations/${this.currentConversation}/send`, {
            message: message
        })
            .then((response) => {
                if (response.data.success) {
                    input.value = '';
                    this.appendMessage(response.data.message, true);
                    this.sendTypingIndicator(false);
                    this.loadConversations();
                } else {
                    alert(response.data.message || 'Failed to send message.');
                }
            })
            .catch((error) => {
                console.error('❌ Error sending message:', error);
                alert('Failed to send message. Please try again.');
            });
    }

    startNewChat() {
        const recipientSelect = document.getElementById('recipientSelect');
        const messageInput = document.getElementById('newMessageText');

        const recipientId = recipientSelect?.value;
        const message = messageInput?.value?.trim();

        if (!recipientId) {
            alert('Please select a user.');
            return;
        }

        if (!message) {
            alert('Please enter a message.');
            return;
        }

        axios.post('/chat/send', {
            recipient_id: recipientId,
            message: message
        })
            .then((response) => {
                if (response.data.success) {
                    messageInput.value = '';
                    recipientSelect.value = '';

                    const modalElement = document.getElementById('newChatModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);

                    if (modal) {
                        modal.hide();
                    }

                    this.loadConversations();
                    this.loadConversation(response.data.conversation_id);
                } else {
                    alert(response.data.message || 'Failed to start chat.');
                }
            })
            .catch((error) => {
                console.error('❌ Error starting new chat:', error);
                alert('Failed to send message. Please try again.');
            });
    }

    markAsRead(conversationId) {
        axios.post(`/chat/conversations/${conversationId}/read`)
            .then(() => {
                this.updateUnreadCount();
            })
            .catch((error) => {
                console.error('❌ Error marking as read:', error);
            });
    }

    sendTypingIndicator(isTyping) {
        if (!this.currentConversation) {
            return;
        }

        axios.post(`/chat/conversations/${this.currentConversation}/typing`, {
            is_typing: isTyping
        }).catch(() => {});
    }

    updateUnreadCount() {
        axios.get('/chat/unread-count')
            .then((response) => {
                const count = response.data.unread_count || 0;

                const topBadge = document.getElementById('unreadBadge');

                if (topBadge) {
                    topBadge.textContent = count > 0 ? count : '';
                    topBadge.style.display = count > 0 ? 'inline' : 'none';
                }

                const sidebarBadge = document.getElementById('sidebarUnreadBadge');

                if (sidebarBadge) {
                    sidebarBadge.textContent = count > 0 ? count : '';
                    sidebarBadge.style.display = count > 0 ? 'inline' : 'none';
                }
            })
            .catch((error) => {
                console.error('❌ Error fetching unread count:', error);
            });
    }

    handleFileUpload(file) {
        if (!this.currentConversation) {
            alert('Please select a conversation first.');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        axios.post(`/chat/conversations/${this.currentConversation}/upload`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then((response) => {
                if (response.data.success) {
                    this.appendMessage(response.data.message, true);
                }
            })
            .catch((error) => {
                console.error('❌ Error uploading file:', error);
                alert('File upload route is not ready yet.');
            });
    }

    startPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }

        this.pollingInterval = setInterval(() => {
            if (this.currentConversation) {
                this.loadConversation(this.currentConversation);
            }

            this.updateUnreadCount();
        }, 10000);
    }

    formatTime(dateValue) {
        if (!dateValue) {
            return new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        return new Date(dateValue).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    escapeHtml(text) {
        if (text === null || text === undefined) {
            return '';
        }

        const div = document.createElement('div');
        div.textContent = String(text);

        return div.innerHTML;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('📱 DOM ready, initializing ChatManager...');

    if (!window.userId) {
        console.error('❌ window.userId missing. Define it in chat/index.blade.php before loading chat.js.');
        return;
    }

    if (!window.axios) {
        console.error('❌ Axios missing. Load Axios in layouts/app.blade.php before @stack("scripts").');
        return;
    }

    window.chat = new ChatManager();

    console.log('✅ window.chat created:', window.chat);
});
