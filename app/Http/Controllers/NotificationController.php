<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = SystemNotification::query()
            ->where('user_id', Auth::id())
            ->latest();

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $baseQuery->whereNull('read_at');
            }

            if ($request->status === 'read') {
                $baseQuery->whereNotNull('read_at');
            }
        }

        if ($request->filled('type')) {
            $baseQuery->where('type', $request->type);
        }

        $notifications = (clone $baseQuery)->paginate(20)->withQueryString();

        $unreadCount = SystemNotification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        $criticalCount = SystemNotification::query()
            ->where('user_id', Auth::id())
            ->where('type', 'critical')
            ->whereNull('read_at')
            ->count();

        $warningCount = SystemNotification::query()
            ->where('user_id', Auth::id())
            ->where('type', 'warning')
            ->whereNull('read_at')
            ->count();

        $infoCount = SystemNotification::query()
            ->where('user_id', Auth::id())
            ->where('type', 'info')
            ->whereNull('read_at')
            ->count();

        $successCount = SystemNotification::query()
            ->where('user_id', Auth::id())
            ->where('type', 'success')
            ->whereNull('read_at')
            ->count();

        return view('notifications.index', compact(
            'notifications',
            'unreadCount',
            'criticalCount',
            'warningCount',
            'infoCount',
            'successCount'
        ));
    }

    public function open(SystemNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        if (is_null($notification->read_at)) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return redirect($notification->url ?: route('dashboard'));
    }

    public function markRead(SystemNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        if (is_null($notification->read_at)) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        SystemNotification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}