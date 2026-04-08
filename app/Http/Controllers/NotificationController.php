<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Assignment;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $lowStockItems = Item::with(['category', 'department'])
            ->where('quantity', '<=', 3)
            ->orderBy('quantity')
            ->get();

        $maintenanceItems = Item::with(['category', 'department'])
            ->where('status', 'maintenance')
            ->latest()
            ->get();

        $overdueAssignments = Assignment::with(['item', 'user', 'department'])
            ->whereNull('returned_at')
            ->whereDate('assigned_at', '<=', now()->subDays(14))
            ->latest('assigned_at')
            ->get();

        $recentAssignments = Assignment::with(['item', 'user', 'department'])
            ->latest('assigned_at')
            ->take(15)
            ->get();

        $recentActivity = AssetLog::with(['item', 'user'])
            ->latest()
            ->take(20)
            ->get();

        $alerts = collect();

        foreach ($overdueAssignments as $assignment) {
            $alerts->push([
                'id' => 'overdue-assignment-' . $assignment->id,
                'type' => 'critical',
                'title' => 'Overdue assignment',
                'message' => ($assignment->item->name ?? 'Asset') . ' is still assigned to ' . ($assignment->user->name ?? 'Unknown user'),
                'meta' => 'Assigned ' . optional($assignment->assigned_at)->format('d M Y H:i'),
                'created_at' => $assignment->assigned_at,
                'url' => route('assignments.index'),
            ]);
        }

        foreach ($lowStockItems as $item) {
            $alerts->push([
                'id' => 'low-stock-' . $item->id,
                'type' => 'warning',
                'title' => 'Low stock',
                'message' => $item->name . ' is low in stock',
                'meta' => 'Qty: ' . $item->quantity . ' • ' . ($item->department->name ?? 'No department'),
                'created_at' => $item->updated_at,
                'url' => route('items.show', $item),
            ]);
        }

        foreach ($maintenanceItems as $item) {
            $alerts->push([
                'id' => 'maintenance-' . $item->id,
                'type' => 'warning',
                'title' => 'Maintenance required',
                'message' => $item->name . ' is under maintenance',
                'meta' => ($item->category->name ?? 'No category') . ' • ' . ($item->department->name ?? 'No department'),
                'created_at' => $item->updated_at,
                'url' => route('items.show', $item),
            ]);
        }

        foreach ($recentAssignments->take(5) as $assignment) {
            $alerts->push([
                'id' => 'recent-assignment-' . $assignment->id,
                'type' => 'info',
                'title' => 'New assignment',
                'message' => ($assignment->item->name ?? 'Asset') . ' assigned to ' . ($assignment->user->name ?? 'Unknown user'),
                'meta' => optional($assignment->assigned_at)->format('d M Y H:i'),
                'created_at' => $assignment->assigned_at,
                'url' => route('assignments.index'),
            ]);
        }

        foreach ($recentActivity->take(5) as $activity) {
            $alerts->push([
                'id' => 'activity-' . $activity->id,
                'type' => 'info',
                'title' => ucfirst(str_replace('_', ' ', $activity->action)),
                'message' => ($activity->item->name ?? 'Unknown asset') . ' by ' . ($activity->user->name ?? 'System'),
                'meta' => optional($activity->created_at)->format('d M Y H:i'),
                'created_at' => $activity->created_at,
                'url' => $activity->item ? route('items.show', $activity->item) : route('dashboard'),
            ]);
        }

        $alerts = $alerts
            ->sortByDesc(fn ($alert) => $alert['created_at'] ?? now())
            ->values();

        $readIds = collect(session('read_notifications', []));
        $unreadCount = $alerts->filter(fn ($alert) => ! $readIds->contains($alert['id']))->count();

        return view('notifications.index', [
            'alerts' => $alerts,
            'unreadCount' => $unreadCount,
            'lowStockItems' => $lowStockItems,
            'maintenanceItems' => $maintenanceItems,
            'overdueAssignments' => $overdueAssignments,
            'recentAssignments' => $recentAssignments,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function markAllRead(): RedirectResponse
    {
        $lowStockIds = Item::where('quantity', '<=', 3)->pluck('id')->map(fn ($id) => 'low-stock-' . $id);
        $maintenanceIds = Item::where('status', 'maintenance')->pluck('id')->map(fn ($id) => 'maintenance-' . $id);
        $overdueIds = Assignment::whereNull('returned_at')
            ->whereDate('assigned_at', '<=', now()->subDays(14))
            ->pluck('id')
            ->map(fn ($id) => 'overdue-assignment-' . $id);
        $recentAssignmentIds = Assignment::latest('assigned_at')->take(5)->pluck('id')->map(fn ($id) => 'recent-assignment-' . $id);
        $recentActivityIds = AssetLog::latest()->take(5)->pluck('id')->map(fn ($id) => 'activity-' . $id);

        $readIds = $lowStockIds
            ->concat($maintenanceIds)
            ->concat($overdueIds)
            ->concat($recentAssignmentIds)
            ->concat($recentActivityIds)
            ->values()
            ->all();

        session(['read_notifications' => $readIds]);

        return redirect()
            ->route('notifications.index')
            ->with('success', 'All notifications marked as read.');
    }
}