@extends('layouts.app')

@section('title', 'AI Laboratory Assistant')

@section('content')
<div
    class="container-fluid ai-assistant-page"
    data-active-conversation="{{ $activeConversation?->id }}"
>
    {{-- ===================================================== --}}
    {{-- PAGE HEADER                                           --}}
    {{-- ===================================================== --}}
    <div class="card ai-page-header border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-3 py-2 mb-2">
                        <i class="fas fa-wand-magic-sparkles me-1"></i>
                        LABORATORY AI
                    </span>

                    <h2 class="fw-bold mb-2">
                        <i class="fas fa-robot me-2"></i>
                        AI Laboratory Assistant
                    </h2>

                    <p class="mb-0 text-white-50">
                        Ask questions about calibrations, certificates,
                        equipment, pricing, services and system procedures.
                    </p>
                </div>

                @can('use_ai_assistant')
                    <form
                        method="POST"
                        action="{{ route('ai-assistant.store') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-warning"
                        >
                            <i class="fas fa-plus me-1"></i>
                            New Conversation
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="alert alert-info ai-safety-notice">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-circle-info mt-1"></i>

            <div>
                <strong>Read-only assistant</strong>

                <div class="small mt-1">
                    The assistant can retrieve information available to your
                    account, but it does not approve certificates, update
                    calibration records, change pricing or delete system data.
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ================================================= --}}
        {{-- CONVERSATION SIDEBAR                              --}}
        {{-- ================================================= --}}
        <div class="col-xl-3 col-lg-4">
            <div class="card ai-sidebar-card border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-comments text-primary me-2"></i>
                                Conversations
                            </h5>
                        </div>

                        <span class="badge bg-secondary">
                            {{ number_format($conversations->total()) }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="ai-conversation-list">
                        @forelse($conversations as $conversation)
                            <a
                                href="{{ route('ai-assistant.index', [
                                    'conversation' => $conversation->id,
                                ]) }}"
                                class="ai-conversation-item {{
                                    (int) $activeConversation?->id
                                    === (int) $conversation->id
                                        ? 'active'
                                        : ''
                                }}"
                                data-conversation-id="{{ $conversation->id }}"
                            >
                                <span class="ai-conversation-icon">
                                    <i class="fas fa-message"></i>
                                </span>

                                <span class="ai-conversation-content">
                                    <strong>
                                        {{ $conversation->title
                                            ?: 'Untitled Conversation' }}
                                    </strong>

                                    <small>
                                        {{ number_format(
                                            $conversation->messages_count
                                        ) }}
                                        message(s)
                                    </small>

                                    <small>
                                        {{ $conversation->updated_at
                                            ?->diffForHumans()
                                            ?? 'Recently' }}
                                    </small>
                                </span>
                            </a>
                        @empty
                            <div class="ai-empty-conversations">
                                <i class="fas fa-comments fa-2x mb-2"></i>

                                <p class="mb-0">
                                    No conversations yet.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if($conversations->hasPages())
                    <div class="card-footer bg-white">
                        {{ $conversations->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ================================================= --}}
        {{-- CHAT AREA                                         --}}
        {{-- ================================================= --}}
        <div class="col-xl-9 col-lg-8">
            <div class="card ai-chat-card border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div class="min-width-0">
                            <h5
                                id="currentConversationTitle"
                                class="fw-bold mb-1 text-truncate"
                            >
                                {{ $activeConversation?->title
                                    ?? 'New AI Conversation' }}
                            </h5>

                            <small class="text-muted">
                                Responses are generated from the system data
                                and tools available to your account.
                            </small>
                        </div>

                        @if($activeConversation)
                            <div class="dropdown">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-ellipsis-vertical"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button
                                            type="button"
                                            class="dropdown-item"
                                            id="renameConversationButton"
                                        >
                                            <i class="fas fa-pen me-2"></i>
                                            Rename
                                        </button>
                                    </li>

                                    @can('delete_ai_conversations')
                                        <li>
                                            <button
                                                type="button"
                                                class="dropdown-item text-danger"
                                                id="deleteConversationButton"
                                            >
                                                <i class="fas fa-trash me-2"></i>
                                                Delete
                                            </button>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <div
                    id="aiMessages"
                    class="card-body ai-messages"
                >
                    @if($activeConversation)
                        @forelse($activeConversation->messages as $message)
                            <div class="ai-message ai-message-{{ $message->role }}">
                                <div class="ai-message-avatar">
                                    @if($message->role === 'assistant')
                                        <i class="fas fa-robot"></i>
                                    @else
                                        <i class="fas fa-user"></i>
                                    @endif
                                </div>

                                <div class="ai-message-body">
                                    <div class="ai-message-heading">
                                        <strong>
                                            {{ $message->role === 'assistant'
                                                ? 'Laboratory Assistant'
                                                : 'You' }}
                                        </strong>

                                        <small>
                                            {{ $message->created_at
                                                ?->format('H:i') }}
                                        </small>
                                    </div>

                                    <div class="ai-message-text">
    @if($message->role === 'assistant')
        {!! app(
            \App\Services\Ai\AiMarkdownService::class
        )->render($message->content) !!}
    @else
        {!! nl2br(e($message->content)) !!}
    @endif
</div>
                                </div>
                            </div>
                        @empty
                            @include(
                                'admin.ai-assistant.partials.welcome'
                            )
                        @endforelse
                    @else
                        @include(
                            'admin.ai-assistant.partials.welcome'
                        )
                    @endif
                </div>

                @can('use_ai_assistant')
                    <div class="card-footer bg-white ai-composer">
                        <div
                            id="aiError"
                            class="alert alert-danger d-none"
                        ></div>

                        <form id="aiMessageForm">
                            @csrf

                            <div class="ai-composer-box">
                                <textarea
                                    id="aiMessageInput"
                                    name="message"
                                    rows="2"
                                    maxlength="10000"
                                    class="form-control"
                                    placeholder="Ask about a calibration, certificate, equipment record or system procedure..."
                                    required
                                ></textarea>

                                <button
                                    type="submit"
                                    id="aiSendButton"
                                    class="btn btn-primary"
                                >
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Send</span>
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    Press Enter to send. Use Shift + Enter for
                                    a new line.
                                </small>

                                <small
                                    id="aiCharacterCount"
                                    class="text-muted"
                                >
                                    0 / 10,000
                                </small>
                            </div>
                        </form>
                    </div>
                @endcan
            </div>

            {{-- Suggested questions --}}
            <div class="card ai-suggestions-card border-0 mt-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Suggested questions
                    </h6>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary ai-suggestion"
                            data-question="What laboratory services are currently active?"
                        >
                            Active laboratory services
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary ai-suggestion"
                            data-question="Show me the configured equipment pricing."
                        >
                            Equipment pricing
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary ai-suggestion"
                            data-question="How do I register new equipment?"
                        >
                            Equipment registration
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary ai-suggestion"
                            data-question="Explain the certificate checking and approval process."
                        >
                            Certificate approval process
                        </button>
                        @canany([
    'view_finance_reports',
    'view_finance'
])
    <button
        type="button"
        class="btn btn-sm btn-outline-success ai-suggestion"
        data-question="Give me the laboratory finance summary for the current month."
    >
        Current-month finance
    </button>
@endcanany
<button
    type="button"
    class="btn btn-sm btn-outline-warning ai-suggestion"
    data-question="Summarize my pending laboratory work."
>
    Pending work
</button>
@can('view_equipment')
    <button
        type="button"
        class="btn btn-sm btn-outline-danger ai-suggestion"
        data-question="Show equipment whose calibration is overdue."
    >
        Overdue equipment
    </button>
@endcan

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary ai-suggestion"
                            data-question="How do I create a laboratory quotation?"
                        >
                            Create a quotation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .ai-assistant-page {
        padding-bottom: 2rem;
    }

    .ai-page-header {
        overflow: hidden;
        border-radius: 1rem;
        background:
            linear-gradient(
                135deg,
                #061f44 0%,
                #0057b8 58%,
                #0b6f83 100%
            );
        box-shadow: 0 12px 35px rgba(0, 87, 184, 0.22);
    }

    .ai-safety-notice {
        border-radius: 0.85rem;
    }

    .ai-sidebar-card,
    .ai-chat-card,
    .ai-suggestions-card {
        border-radius: 0.9rem;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
    }

    .ai-sidebar-card {
        position: sticky;
        top: 85px;
    }

    .ai-conversation-list {
        max-height: 640px;
        overflow-y: auto;
    }

    .ai-conversation-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #edf0f4;
        color: #212529;
        text-decoration: none;
        transition:
            background-color 0.2s ease,
            color 0.2s ease;
    }

    .ai-conversation-item:hover {
        background: #f5f8fc;
        color: #0d6efd;
    }

    .ai-conversation-item.active {
        border-left: 4px solid #0d6efd;
        background: #eaf2ff;
        color: #0d3f83;
    }

    .ai-conversation-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        border-radius: 50%;
        background: #eaf2ff;
        color: #0d6efd;
    }

    .ai-conversation-content {
        min-width: 0;
        flex: 1;
    }

    .ai-conversation-content strong,
    .ai-conversation-content small {
        display: block;
    }

    .ai-conversation-content strong {
        overflow: hidden;
        font-size: 0.9rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ai-conversation-content small {
        color: #6c757d;
        font-size: 0.73rem;
    }

    .ai-empty-conversations {
        padding: 3rem 1rem;
        color: #6c757d;
        text-align: center;
    }

    .ai-chat-card {
        min-height: 680px;
        overflow: hidden;
    }

    .ai-messages {
        height: 520px;
        overflow-y: auto;
        background:
            linear-gradient(
                180deg,
                #f8fafc 0%,
                #ffffff 100%
            );
    }

    .ai-message {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .ai-message-user {
        flex-direction: row-reverse;
    }

    .ai-message-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 50%;
    }

    .ai-message-assistant .ai-message-avatar {
        background: #eaf2ff;
        color: #0d6efd;
    }

    .ai-message-user .ai-message-avatar {
        background: #e9f8ef;
        color: #198754;
    }

    .ai-message-body {
        max-width: 82%;
    }

    .ai-message-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.3rem;
        color: #6c757d;
        font-size: 0.78rem;
    }

    .ai-message-text {
        padding: 0.85rem 1rem;
        border: 1px solid #e4e8ed;
        border-radius: 0.85rem;
        background: #ffffff;
        color: #212529;
        line-height: 1.65;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    .ai-message-user .ai-message-text {
        border-color: #cfe8d8;
        background: #e9f8ef;
    }

    .ai-message-loading .ai-message-text {
        color: #6c757d;
        font-style: italic;
    }

    .ai-composer {
        padding: 1rem;
    }

    .ai-composer-box {
        display: flex;
        align-items: flex-end;
        gap: 0.75rem;
    }

    .ai-composer-box textarea {
        min-height: 54px;
        max-height: 180px;
        resize: vertical;
    }

    .ai-composer-box .btn {
        min-width: 100px;
        height: 54px;
    }

    .ai-welcome {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 430px;
        padding: 2rem;
        text-align: center;
    }

    .ai-welcome-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 90px;
        height: 90px;
        margin-bottom: 1rem;
        border-radius: 50%;
        background: #eaf2ff;
        color: #0d6efd;
    }

    @media (max-width: 991.98px) {
        .ai-sidebar-card {
            position: static;
        }

        .ai-conversation-list {
            max-height: 300px;
        }
    }

    @media (max-width: 575.98px) {
        .ai-page-header .card-body {
            padding: 1.5rem !important;
        }

        .ai-messages {
            height: 460px;
        }

        .ai-message-body {
            max-width: calc(100% - 50px);
        }

        .ai-composer-box {
            align-items: stretch;
            flex-direction: column;
        }

        .ai-composer-box .btn {
            width: 100%;
        }
        .ai-message-text > :last-child {
    margin-bottom: 0;
}

.ai-message-text h1,
.ai-message-text h2,
.ai-message-text h3,
.ai-message-text h4 {
    margin-top: 1rem;
    margin-bottom: 0.6rem;
    font-weight: 700;
}

.ai-message-text h1 {
    font-size: 1.3rem;
}

.ai-message-text h2 {
    font-size: 1.18rem;
}

.ai-message-text h3 {
    font-size: 1.05rem;
}

.ai-message-text ul,
.ai-message-text ol {
    padding-left: 1.35rem;
}

.ai-message-text table {
    width: 100%;
    margin: 0.8rem 0;
    border-collapse: collapse;
}

.ai-message-text th,
.ai-message-text td {
    padding: 0.55rem;
    border: 1px solid #dee2e6;
    vertical-align: top;
}

.ai-message-text th {
    background: #f8f9fa;
}

.ai-message-text code {
    padding: 0.15rem 0.3rem;
    border-radius: 0.25rem;
    background: #f1f3f5;
    color: #c7254e;
}

.ai-message-text pre {
    padding: 0.85rem;
    overflow-x: auto;
    border-radius: 0.5rem;
    background: #172033;
    color: #f8f9fa;
}

.ai-message-text pre code {
    padding: 0;
    background: transparent;
    color: inherit;
}
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const page = document.querySelector('.ai-assistant-page');
    const form = document.getElementById('aiMessageForm');
    const input = document.getElementById('aiMessageInput');
    const sendButton = document.getElementById('aiSendButton');
    const messages = document.getElementById('aiMessages');
    const errorBox = document.getElementById('aiError');
    const renameButton = document.getElementById('renameConversationButton');

const deleteButton = document.getElementById('deleteConversationButton');

const titleElement = document.getElementById('currentConversationTitle');
    const characterCount =
        document.getElementById('aiCharacterCount');

    let activeConversationId =
        page?.dataset?.activeConversation || null;

    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]'
    )?.getAttribute('content');

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value;
        return element.innerHTML;
    }

    function scrollMessagesToBottom() {
        if (messages) {
            messages.scrollTop = messages.scrollHeight;
        }
    }

    function showError(message) {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    }

    function clearError() {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = '';
        errorBox.classList.add('d-none');
    }

    function appendMessage(
    role,
    content,
    loading = false,
    renderedHtml = null
) {
    if (!messages) {
        return null;
    }

    const wrapper = document.createElement('div');

    wrapper.className =
        'ai-message ai-message-' + role
        + (loading ? ' ai-message-loading' : '');

    let messageContent;

    if (loading) {
        messageContent =
            '<i class="fas fa-spinner fa-spin me-2"></i>Thinking...';
    } else if (
        role === 'assistant'
        && renderedHtml
    ) {
        /*
         * renderedHtml is generated and sanitized by Laravel.
         */
        messageContent = renderedHtml;
    } else {
        messageContent = escapeHtml(content)
            .replace(/\n/g, '<br>');
    }

    wrapper.innerHTML = `
        <div class="ai-message-avatar">
            <i class="fas ${
                role === 'assistant'
                    ? 'fa-robot'
                    : 'fa-user'
            }"></i>
        </div>

        <div class="ai-message-body">
            <div class="ai-message-heading">
                <strong>
                    ${
                        role === 'assistant'
                            ? 'Laboratory Assistant'
                            : 'You'
                    }
                </strong>

                <small>
                    ${new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    })}
                </small>
            </div>

            <div class="ai-message-text">
                ${messageContent}
            </div>
        </div>
    `;

    messages.appendChild(wrapper);
    scrollMessagesToBottom();

    return wrapper;
}

    function setSendingState(isSending) {
        if (!sendButton || !input) {
            return;
        }

        sendButton.disabled = isSending;
        input.disabled = isSending;

        sendButton.innerHTML = isSending
            ? '<i class="fas fa-spinner fa-spin"></i><span>Sending</span>'
            : '<i class="fas fa-paper-plane"></i><span>Send</span>';
    }

    async function sendMessage(message) {
        clearError();

        appendMessage('user', message);

        const loadingMessage = appendMessage(
            'assistant',
            '',
            true
        );

        setSendingState(true);

        const url = activeConversationId
            ? `{{ url('/ai-assistant') }}/${activeConversationId}/send`
            : `{{ route('ai-assistant.start-and-send') }}`;

        try {
            const response = await fetch(url, {
                method: 'POST',

                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },

                body: JSON.stringify({
                    message: message,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message
                    || 'The request could not be completed.'
                );
            }

            loadingMessage?.remove();

           appendMessage(
    'assistant',
    data.message.content,
    false,
    data.message.rendered_html
);

            if (!activeConversationId) {
                activeConversationId =
                    data.conversation.id;

                const newUrl = new URL(
                    window.location.href
                );

                newUrl.searchParams.set(
                    'conversation',
                    activeConversationId
                );

                window.history.replaceState(
                    {},
                    '',
                    newUrl
                );
            }

            const title = document.getElementById(
                'currentConversationTitle'
            );

            if (
                title
                && data.conversation?.title
            ) {
                title.textContent =
                    data.conversation.title;
            }
        } catch (error) {
            loadingMessage?.remove();

            showError(
                error.message
                || 'The AI assistant could not respond.'
            );
        } finally {
            setSendingState(false);
            input?.focus();
        }
    }

    form?.addEventListener('submit', function (event) {
        event.preventDefault();

        const message = input.value.trim();

        if (message.length < 2) {
            showError(
                'Enter a question containing at least two characters.'
            );

            return;
        }

        input.value = '';

        if (characterCount) {
            characterCount.textContent =
                '0 / 10,000';
        }

        sendMessage(message);
    });

    input?.addEventListener('keydown', function (event) {
        if (
            event.key === 'Enter'
            && !event.shiftKey
        ) {
            event.preventDefault();
            form?.requestSubmit();
        }
    });

    input?.addEventListener('input', function () {
        if (characterCount) {
            characterCount.textContent =
                `${input.value.length.toLocaleString()} / 10,000`;
        }
    });

    document
        .querySelectorAll('.ai-suggestion')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const question =
                        button.dataset.question || '';

                    if (input) {
                        input.value = question;
                        input.dispatchEvent(
                            new Event('input')
                        );
                        input.focus();
                    }
                }
            );
        });

        renameButton?.addEventListener(
    'click',
    async function () {
        if (!activeConversationId) {
            return;
        }

        const currentTitle =
            titleElement?.textContent?.trim()
            || 'New Conversation';

        const newTitle = window.prompt(
            'Enter a new conversation title:',
            currentTitle
        );

        if (newTitle === null) {
            return;
        }

        const cleanTitle = newTitle.trim();

        if (
            cleanTitle.length < 1
            || cleanTitle.length > 100
        ) {
            showError(
                'The title must contain between 1 and 100 characters.'
            );

            return;
        }

        clearError();

        try {
            const response = await fetch(
                `{{ url('/ai-assistant') }}/${activeConversationId}`,
                {
                    method: 'PATCH',

                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },

                    body: JSON.stringify({
                        title: cleanTitle,
                    }),
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message
                    || 'The conversation could not be renamed.'
                );
            }

            if (titleElement) {
                titleElement.textContent =
                    data.conversation.title;
            }

           const sidebarTitle = document.querySelector(
    `.ai-conversation-item[data-conversation-id="${activeConversationId}"] strong`
);

            if (sidebarTitle) {
                sidebarTitle.textContent =
                    data.conversation.title;
            }
        } catch (error) {
            showError(
                error.message
                || 'The conversation could not be renamed.'
            );
        }
    }
);
deleteButton?.addEventListener(
    'click',
    async function () {
        if (!activeConversationId) {
            return;
        }

        const confirmed = window.confirm(
            'Delete this conversation and all its messages?'
        );

        if (!confirmed) {
            return;
        }

        clearError();

        try {
            const response = await fetch(
                `{{ url('/ai-assistant') }}/${activeConversationId}`,
                {
                    method: 'DELETE',

                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message
                    || 'The conversation could not be deleted.'
                );
            }

            window.location.href =
                `{{ route('ai-assistant.index') }}`;
        } catch (error) {
            showError(
                error.message
                || 'The conversation could not be deleted.'
            );
        }
    }
);

    scrollMessagesToBottom();
});
</script>
@endpush
