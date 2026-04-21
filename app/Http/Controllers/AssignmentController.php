<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Item;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\StockInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    protected StockInventoryService $stockInventoryService;

    public function __construct(StockInventoryService $stockInventoryService)
    {
        $this->stockInventoryService = $stockInventoryService;
    }

    public function index(Request $request)
    {
        $perPage = max(5, min((int) $request->integer('per_page', 10), 50));

        $query = Assignment::with(['item', 'user', 'assignedDepartment'])
            ->latest('assigned_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('receiver_name', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('asset_tag', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignedDepartment', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('returned_at');
            } elseif ($request->status === 'returned') {
                $query->whereNotNull('returned_at');
            }
        }

        return response()->json($query->paginate($perPage)->withQueryString());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validated['receiver_name'] = trim((string) $validated['receiver_name']);
        $validated['quantity'] = (int) $validated['quantity'];
        $validated['department_id'] = (int) $validated['department_id'];
        $validated['item_id'] = (int) $validated['item_id'];
        $validated['user_id'] = isset($validated['user_id']) ? (int) $validated['user_id'] : null;

        $assignment = DB::transaction(function () use ($validated) {
            $item = Item::query()->lockForUpdate()->findOrFail($validated['item_id']);
            $department = Department::query()->lockForUpdate()->find($validated['department_id']);

            if (!$department) {
                throw ValidationException::withMessages([
                    'department_id' => 'Selected department is invalid.',
                ]);
            }

            if ($item->status !== Item::STATUS_AVAILABLE) {
                throw ValidationException::withMessages([
                    'item_id' => 'Only available items can be assigned.',
                ]);
            }

            if (method_exists($item, 'isAssignable') && !$item->isAssignable()) {
                throw ValidationException::withMessages([
                    'item_id' => 'This item is not currently assignable.',
                ]);
            }

            if ($validated['quantity'] > (int) $item->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Assigned quantity exceeds available stock.',
                ]);
            }

            $assignment = Assignment::create([
                'item_id' => $item->id,
                'user_id' => $validated['user_id'],
                'receiver_name' => $validated['receiver_name'],
                'department_id' => $department->id,
                'quantity' => $validated['quantity'],
                'assigned_at' => now(),
            ]);

            $this->stockInventoryService->stockOut(
                $item,
                $validated['quantity'],
                'ASN-' . $assignment->id,
                'Assigned to ' . $assignment->receiver_name
            );

            $assignment->load(['item', 'user', 'assignedDepartment']);

            AssetLog::log(
                $item->id,
                AssetLog::ACTION_ASSIGNED,
                "{$validated['quantity']} unit(s) of {$item->name} assigned to {$assignment->receiver_name} ({$department->name})"
            );

            $this->notifyAdmins(
                'assignment_created',
                'Asset Assigned',
                "{$validated['quantity']} unit(s) of '{$item->name}' were assigned to '{$assignment->receiver_name}' in '{$department->name}'.",
                '/assignments',
                'assignment',
                $assignment->id
            );

            if (!empty($assignment->user_id)) {
                $this->notifyUser(
                    (int) $assignment->user_id,
                    'assignment_created',
                    'Asset Assigned To You',
                    "{$validated['quantity']} unit(s) of '{$item->name}' were assigned to you.",
                    '/assignments',
                    'assignment',
                    $assignment->id
                );
            }

            $item->refresh();
            $reorderLevel = (int) ($item->reorder_level ?? 5);

            if ((int) $item->quantity <= $reorderLevel) {
                $this->notifyAdmins(
                    'low_stock',
                    'Low Stock Alert',
                    "Asset '{$item->name}' is low in stock with quantity {$item->quantity}.",
                    '/inventory',
                    'item',
                    $item->id
                );
            }

            return $assignment;
        });

        return response()->json($assignment, 201);
    }

    public function returnItem(Assignment $assignment)
    {
        if ($assignment->returned_at) {
            return response()->json([
                'message' => 'This assignment has already been returned.',
            ], 422);
        }

        $assignment = DB::transaction(function () use ($assignment) {
            $assignment->loadMissing(['item', 'assignedDepartment']);

            $item = Item::query()->lockForUpdate()->findOrFail($assignment->item_id);

            $assignment->update([
                'returned_at' => now(),
            ]);

            $this->stockInventoryService->stockIn(
                $item,
                (int) $assignment->quantity,
                'RET-' . $assignment->id,
                null,
                'Returned from ' . ($assignment->receiver_name ?: 'receiver')
            );

            AssetLog::log(
                $item->id,
                AssetLog::ACTION_RETURNED,
                "{$assignment->quantity} unit(s) of {$item->name} returned from {$assignment->receiver_name}"
            );

            $this->notifyAdmins(
                'assignment_returned',
                'Asset Returned',
                "{$assignment->quantity} unit(s) of '{$item->name}' were returned from '{$assignment->receiver_name}'.",
                '/assignments',
                'assignment',
                $assignment->id
            );

            return $assignment->fresh(['item', 'user', 'assignedDepartment']);
        });

        return response()->json([
            'message' => 'Asset returned successfully.',
            'assignment' => $assignment,
        ]);
    }

    protected function notifyAdmins(
        string $type,
        string $title,
        string $message,
        string $url,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): void {
        $admins = User::query()->where('role', 'admin')->get();

        foreach ($admins as $admin) {
            SystemNotification::create([
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'read_at' => null,
            ]);
        }
    }

    protected function notifyUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        string $url,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): void {
        SystemNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'read_at' => null,
        ]);
    }
}
