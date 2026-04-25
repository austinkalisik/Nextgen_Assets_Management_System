<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Item;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected function getIntSetting(string $key, int $default): int
    {
        $value = DB::table('settings')->where('key', $key)->value('value');

        if (! is_numeric($value)) {
            return $default;
        }

        return (int) $value;
    }

    protected function lowStockQuery(int $defaultThreshold)
    {
        return Item::query()
            ->where('quantity', '>', 0)
            ->where(function ($query) use ($defaultThreshold) {
                $query->where(function ($sub) {
                    $sub->whereNotNull('reorder_level')
                        ->whereColumn('quantity', '<=', 'reorder_level');
                })->orWhere(function ($sub) use ($defaultThreshold) {
                    $sub->whereNull('reorder_level')
                        ->where('quantity', '<=', $defaultThreshold);
                });
            });
    }

    public function index()
    {
        $lowStockThreshold = $this->getIntSetting('low_stock_threshold', 5);
        $overdueDays = $this->getIntSetting('assignment_overdue_days', 7);

        return response()->json([
            'total_assets' => Item::count(),
            'available' => Item::where('status', Item::STATUS_AVAILABLE)
                ->where('quantity', '>', 0)
                ->count(),
            'assigned' => (int) Assignment::whereNull('returned_at')->sum('quantity'),
            'maintenance' => Item::where('status', Item::STATUS_MAINTENANCE)->count(),
            'low_stock' => $this->lowStockQuery($lowStockThreshold)->count(),
            'overdue' => Assignment::whereNotNull('assigned_at')
                ->whereNull('returned_at')
                ->where('assigned_at', '<', now()->subDays($overdueDays))
                ->count(),
            'notifications_count' => Auth::id()
                ? SystemNotification::where('user_id', Auth::id())->whereNull('read_at')->count()
                : 0,
            'recent_assignments' => Assignment::with(['item', 'user', 'assignedDepartment'])
                ->latest('assigned_at')
                ->limit(5)
                ->get(),
            'recent_items' => Item::with(['category', 'supplier'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
