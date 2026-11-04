@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-bell text-primary me-2"></i>
                Notifications
            </h1>

            <p class="text-muted mb-0">
                Equipment calibration reminders and system alerts.
            </p>
        </div>

        @if(auth()->user()->unreadNotifications()->exists())
            <form
                action="{{ route('notifications.mark-all-read') }}"
                method="POST"
            >
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-1"></i>
                    Mark All Read
                </button>
            </form>
        @endif
    </div>

    <div class="card shadow-sm border-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data ?? [];

                    $color = $data['color'] ?? 'secondary';
                    $icon = $data['icon'] ?? 'fa-bell';
                @endphp

                <a
                    href="{{ route('notifications.read', $notification) }}"
                    class="list-group-item list-group-item-action p-3 {{ $notification->read_at ? '' : 'notification-unread' }}"
                >
                    <div class="d-flex gap-3">
                        <span
                            class="notification-page-icon bg-{{ $color }} bg-opacity-10 text-{{ $color }}"
                        >
                            <i class="fas {{ $icon }}"></i>
                        </span>

                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-2">
                                <strong>
                                    {{ $data['title']
                                        ?? 'System Notification' }}
                                </strong>

                                @if(!$notification->read_at)
                                    <span class="badge bg-primary">
                                        New
                                    </span>
                                @endif
                            </div>

                            <p class="mb-1 text-muted">
                                {{ $data['message']
                                    ?? 'No additional details available.' }}
                            </p>

                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                {{ $notification->created_at?->diffForHumans()
                                    ?? 'Recently' }}
                            </small>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-bell-slash fa-3x mb-3"></i>

                    <p class="mb-0">
                        You have no notifications.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .notification-unread {
        border-left: 4px solid #0d6efd;
        background: #f5f9ff;
    }

    .notification-page-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 50%;
    }
</style>
@endpush
