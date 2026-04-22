<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Item;
use App\Services\StockInventoryService;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class AssignmentController extends Controller
{
    protected StockInventoryService $stockInventoryService;
    protected SystemNotificationService $notificationService;

    public function __construct(
        StockInventoryService $stockInventoryService,
        SystemNotificationService $notificationService
    ) {
        $this->stockInventoryService = $stockInventoryService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $perPage = max(5, min((int) $request->integer('per_page', 10), 50));

        $query = Assignment::with(['item', 'user', 'assignedDepartment'])
            ->latest('assigned_at');

        $this->applyAssignmentFilters($query, $request);

        return response()->json($query->paginate($perPage)->withQueryString());
    }

    public function report(Request $request)
    {
        $historyQuery = Assignment::with(['item', 'user', 'assignedDepartment'])
            ->latest('assigned_at');

        $this->applyAssignmentFilters($historyQuery, $request);

        $history = $historyQuery->limit(500)->get();

        $activeAssignedByItem = Assignment::query()
            ->whereNull('returned_at')
            ->select('item_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('item_id')
            ->pluck('total_quantity', 'item_id');

        $items = Item::with(['category', 'supplier'])
            ->orderBy('name')
            ->get()
            ->map(function (Item $item) use ($activeAssignedByItem) {
                $availableQuantity = (int) $item->quantity;
                $activeAssignedQuantity = (int) ($activeAssignedByItem[$item->id] ?? 0);
                $reorderLevel = (int) ($item->reorder_level ?? 0);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'asset_tag' => $item->asset_tag,
                    'sku' => $item->sku,
                    'department' => null,
                    'available_quantity' => $availableQuantity,
                    'active_assigned_quantity' => $activeAssignedQuantity,
                    'managed_quantity' => $availableQuantity + $activeAssignedQuantity,
                    'stock_state' => $this->stockState($availableQuantity, $reorderLevel),
                ];
            })
            ->values();

        $receivers = $history
            ->groupBy(fn (Assignment $assignment) => $this->receiverLabel($assignment))
            ->map(function ($rows, string $receiver) {
                $activeRows = $rows->whereNull('returned_at');
                $returnedRows = $rows->whereNotNull('returned_at');

                return [
                    'receiver' => $receiver,
                    'active_quantity' => (int) $activeRows->sum('quantity'),
                    'returned_quantity' => (int) $returnedRows->sum('quantity'),
                    'total_quantity' => (int) $rows->sum('quantity'),
                    'last_assigned_at' => optional($rows->first()->assigned_at)->toDateTimeString(),
                    'departments' => $rows
                        ->map(fn (Assignment $assignment) => $assignment->assignedDepartment?->name)
                        ->filter()
                        ->unique()
                        ->values(),
                    'items' => $rows
                        ->groupBy('item_id')
                        ->map(function ($itemRows) {
                            $first = $itemRows->first();
                            $activeQuantity = (int) $itemRows->whereNull('returned_at')->sum('quantity');
                            $returnedQuantity = (int) $itemRows->whereNotNull('returned_at')->sum('quantity');

                            return [
                                'item_id' => $first->item_id,
                                'item_name' => $first->item?->name ?? '-',
                                'asset_tag' => $first->item?->asset_tag,
                                'sku' => $first->item?->sku,
                                'quantity_assigned' => $activeQuantity + $returnedQuantity,
                                'quantity_returned' => $returnedQuantity,
                                'quantity_remaining' => $activeQuantity,
                                'last_assigned_at' => optional($first->assigned_at)->toDateTimeString(),
                                'department' => $first->assignedDepartment?->name,
                            ];
                        })
                        ->values(),
                ];
            })
            ->sortBy('receiver', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json([
            'items' => $items,
            'receivers' => $receivers,
            'history' => $history->map(fn (Assignment $assignment) => [
                'id' => $assignment->id,
                'item_name' => $assignment->item?->name ?? '-',
                'asset_tag' => $assignment->item?->asset_tag,
                'sku' => $assignment->item?->sku,
                'receiver' => $this->receiverLabel($assignment),
                'department' => $assignment->assignedDepartment?->name,
                'quantity' => (int) $assignment->quantity,
                'assigned_at' => optional($assignment->assigned_at)->toDateTimeString(),
                'returned_at' => optional($assignment->returned_at)->toDateTimeString(),
                'status' => $assignment->returned_at ? 'Returned' : 'Active',
            ])->values(),
        ]);
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

            $availableQuantity = (int) $item->quantity;

            if ($validated['quantity'] > $availableQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => $this->availableQuantityMessage($availableQuantity),
                ]);
            }

            if (method_exists($item, 'isAssignable') && !$item->isAssignable()) {
                throw ValidationException::withMessages([
                    'item_id' => 'This item is not currently assignable.',
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

            try {
                $this->stockInventoryService->stockOut(
                    $item,
                    $validated['quantity'],
                    'ASN-' . $assignment->id,
                    'Assigned to ' . $assignment->receiver_name
                );
            } catch (InvalidArgumentException $e) {
                $item->refresh();

                throw ValidationException::withMessages([
                    'quantity' => $this->availableQuantityMessage((int) $item->quantity),
                ]);
            }

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
        $this->notificationService->notifyAdmins($type, $title, $message, $url, $sourceType, $sourceId);
    }

    protected function availableQuantityMessage(int $availableQuantity): string
    {
        return "Only {$availableQuantity} unit(s) are available for assignment.";
    }

    protected function applyAssignmentFilters($query, Request $request): void
    {
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

        if ($request->filled('receiver')) {
            $receiver = trim((string) $request->receiver);

            $query->where(function ($q) use ($receiver) {
                $q->where('receiver_name', $receiver)
                    ->orWhereHas('user', function ($sub) use ($receiver) {
                        $sub->where('name', $receiver);
                    });
            });
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', (int) $request->integer('item_id'));
        }

        if ($request->filled('date_start')) {
            $query->whereDate('assigned_at', '>=', $request->date('date_start')->toDateString());
        }

        if ($request->filled('date_end')) {
            $query->whereDate('assigned_at', '<=', $request->date('date_end')->toDateString());
        }
    }

    protected function receiverLabel(Assignment $assignment): string
    {
        return $assignment->receiver_name ?: ($assignment->user?->name ?? 'Unknown receiver');
    }

    protected function stockState(int $quantity, int $reorderLevel): string
    {
        if ($quantity <= 0) {
            return 'Out of Stock';
        }

        if ($quantity <= $reorderLevel) {
            return 'Low Stock';
        }

        return 'Available';
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
        $this->notificationService->notifyUser($userId, $type, $title, $message, $url, $sourceType, $sourceId);
    }
}
