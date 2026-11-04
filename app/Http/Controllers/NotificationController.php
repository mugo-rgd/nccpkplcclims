<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Show the authenticated user's notifications.
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view(
            'notifications.index',
            compact('notifications')
        );
    }

    /**
     * Mark one notification as read and redirect to its target.
     */
    public function read(
        Request $request,
        DatabaseNotification $notification
    ): RedirectResponse {
        abort_unless(
            (string) $notification->notifiable_id
                === (string) $request->user()->id,
            403
        );

        $notification->markAsRead();

        $targetUrl =
            $notification->data['url']
            ?? (
                route('notifications.index')
            );

        return redirect()->to($targetUrl);
    }

    /**
     * Mark all authenticated-user notifications as read.
     */
    public function markAllRead(
        Request $request
    ): RedirectResponse {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'All notifications have been marked as read.'
        );
    }
}
