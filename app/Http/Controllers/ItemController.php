<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Item;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\StockInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    protected StockInventoryService $stockInventoryService;

    public function __construct(StockInventoryService $stockInventoryService)
    {
        $this->stockInventoryService = $stockInventoryService;
    }

    public function index(Request $request)
    {
        $perPage = max(5, min((int) $request->integer('per_page', 10), 50));

        $query = Item::with(['category', 'supplier'])->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return response()->json($query->paginate($perPage)->withQueryString());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'asset_tag' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,maintenance,lost,retired'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
        ]);

        $payload = $this->normalizePayload($validated, true);
        $initialQuantity = (int) $payload['quantity'];

        if (($payload['asset_tag'] || $payload['serial_number']) && $initialQuantity !== 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Serialized or tagged assets must have quantity 1.',
            ]);
        }

        $result = DB::transaction(function () use ($payload, $initialQuantity) {
            if ($payload['asset_tag'] && Item::query()->lockForUpdate()->where('asset_tag', $payload['asset_tag'])->exists()) {
                throw ValidationException::withMessages([
                    'asset_tag' => 'Asset tag already exists.',
                ]);
            }

            if ($payload['serial_number'] && Item::query()->lockForUpdate()->where('serial_number', $payload['serial_number'])->exists()) {
                throw ValidationException::withMessages([
                    'serial_number' => 'Serial number already exists.',
                ]);
            }

            $existing = $this->findExistingStockItem($payload);

            if ($existing) {
                if (blank($existing->sku) && filled($payload['sku'])) {
                    $skuConflict = Item::query()
                        ->lockForUpdate()
                        ->where('sku', $payload['sku'])
                        ->where('id', '!=', $existing->id)
                        ->exists();

                    if ($skuConflict) {
                        throw ValidationException::withMessages([
                            'sku' => 'SKU already exists.',
                        ]);
                    }

                    $existing->sku = $payload['sku'];
                }

                if (blank($existing->description) && filled($payload['description'])) {
                    $existing->description = $payload['description'];
                }

                if (blank($existing->location) && filled($payload['location'])) {
                    $existing->location = $payload['location'];
                }

                if ((is_null($existing->unit_cost) || $existing->unit_cost === '') && $payload['unit_cost'] !== null) {
                    $existing->unit_cost = $payload['unit_cost'];
                }

                if ((is_null($existing->reorder_level) || (int) $existing->reorder_level === 0) && isset($payload['reorder_level'])) {
                    $existing->reorder_level = $payload['reorder_level'];
                }

                $existing->save();

                $this->stockInventoryService->stockIn(
                    $existing,
                    $initialQuantity,
                    'ITEM-MERGE-' . $existing->id,
                    $payload['supplier_id'],
                    'Stock received while saving a matching asset item.'
                );

                $existing->refresh()->load(['category', 'supplier']);

                AssetLog::log(
                    $existing->id,
                    AssetLog::ACTION_UPDATED,
                    $existing->name . ' quantity increased by ' . $initialQuantity . ' by ' . (Auth::user()?->name ?? 'System')
                );

                $this->notifyAdmins(
                    'asset_updated',
                    'Asset Quantity Updated',
                    "Existing asset '{$existing->name}' found. Quantity increased by {$initialQuantity}.",
                    '/items',
                    'item',
                    $existing->id
                );

                return [
                    'status' => 200,
                    'body' => [
                        'message' => 'Existing asset found. Quantity increased successfully.',
                        'item' => $existing,
                    ],
                ];
            }

            if ($payload['sku'] && Item::query()->lockForUpdate()->where('sku', $payload['sku'])->exists()) {
                throw ValidationException::withMessages([
                    'sku' => 'SKU already exists.',
                ]);
            }

            $itemData = $payload;
            $itemData['quantity'] = 0;

            $item = Item::create($itemData);

            $this->stockInventoryService->stockIn(
                $item,
                $initialQuantity,
                'ITEM-OPEN-' . $item->id,
                $payload['supplier_id'],
                'Initial stock received during asset creation.'
            );

            $item->refresh()->load(['category', 'supplier']);

            AssetLog::log(
                $item->id,
                AssetLog::ACTION_CREATED,
                $item->name . ' created by ' . (Auth::user()?->name ?? 'System')
            );

            $this->notifyAdmins(
                'asset_created',
                'Asset Created',
                "Asset '{$item->name}' was created successfully.",
                '/items',
                'item',
                $item->id
            );

            $reorderLevel = $item->reorder_level ?? 5;

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

            return [
                'status' => 201,
                'body' => $item,
            ];
        });

        return response()->json($result['body'], $result['status']);
    }

    public function show(Item $item)
    {
        $item->load([
            'category',
            'supplier',
            'assignments.assignedDepartment',
        ]);

        return response()->json($item);
    }

    public function update(Request $request, Item $item)
    {
        $oldStatus = $item->status;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:items,sku,' . $item->id],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'asset_tag' => ['nullable', 'string', 'max:255', 'unique:items,asset_tag,' . $item->id],
            'serial_number' => ['nullable', 'string', 'max:255', 'unique:items,serial_number,' . $item->id],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,maintenance,lost,retired'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
        ]);

        $payload = $this->normalizePayload($validated, false);

        $item->update($payload);

        if (method_exists($item, 'syncAutomatedStatus')) {
            $item->refresh();
            $item->syncAutomatedStatus();
        }

        $item->refresh()->load(['category', 'supplier']);

        AssetLog::log(
            $item->id,
            AssetLog::ACTION_UPDATED,
            'Asset details updated by ' . (Auth::user()?->name ?? 'System')
        );

        $this->notifyAdmins(
            'asset_updated',
            'Asset Updated',
            "Asset '{$item->name}' was updated.",
            '/items',
            'item',
            $item->id
        );

        if ($item->status === Item::STATUS_MAINTENANCE && $oldStatus !== Item::STATUS_MAINTENANCE) {
            $this->notifyAdmins(
                'maintenance_due',
                'Maintenance Alert',
                "Asset '{$item->name}' has been moved to maintenance.",
                '/items',
                'item',
                $item->id
            );
        }

        return response()->json($item);
    }

    public function destroy(Item $item)
    {
        $itemName = $item->name;
        $itemId = $item->id;

        AssetLog::log(
            $item->id,
            AssetLog::ACTION_DELETED,
            $item->name . ' permanently deleted by ' . (Auth::user()?->name ?? 'System')
        );

        $item->delete();

        $this->notifyAdmins(
            'asset_deleted',
            'Asset Deleted',
            "Asset '{$itemName}' was deleted.",
            '/items',
            'item',
            $itemId
        );

        return response()->json(['message' => 'Item deleted successfully']);
    }

    protected function findExistingStockItem(array $payload): ?Item
    {
        if ($payload['asset_tag'] || $payload['serial_number']) {
            return null;
        }

        $name = mb_strtolower(trim((string) $payload['name']));
        $brand = $payload['brand'] ? mb_strtolower(trim((string) $payload['brand'])) : null;

        return Item::query()
            ->lockForUpdate()
            ->whereNull('asset_tag')
            ->whereNull('serial_number')
            ->where('status', Item::STATUS_AVAILABLE)
            ->where('category_id', $payload['category_id'])
            ->whereRaw('LOWER(TRIM(name)) = ?', [$name])
            ->when(
                $brand !== null,
                fn($query) => $query->whereRaw('LOWER(TRIM(brand)) = ?', [$brand]),
                fn($query) => $query->where(function ($sub) {
                    $sub->whereNull('brand')->orWhereRaw("TRIM(brand) = ''");
                })
            )
            ->first();
    }

    protected function normalizePayload(array $validated, bool $includeQuantity = true): array
    {
        $payload = [
            'name' => trim((string) $validated['name']),
            'sku' => $this->normalizeNullable($validated['sku'] ?? null),
            'brand' => $this->normalizeNullable($validated['brand'] ?? null),
            'description' => $this->normalizeNullable($validated['description'] ?? null),
            'category_id' => $validated['category_id'],
            'supplier_id' => $validated['supplier_id'],
            'asset_tag' => $this->normalizeNullable($validated['asset_tag'] ?? null),
            'serial_number' => $this->normalizeNullable($validated['serial_number'] ?? null),
            'reorder_level' => array_key_exists('reorder_level', $validated) && $validated['reorder_level'] !== null
                ? (int) $validated['reorder_level']
                : 5,
            'unit_cost' => array_key_exists('unit_cost', $validated) && $validated['unit_cost'] !== null
                ? (float) $validated['unit_cost']
                : null,
            'status' => $validated['status'],
            'location' => $this->normalizeNullable($validated['location'] ?? null),
            'purchase_date' => $validated['purchase_date'] ?? null,
        ];

        if ($includeQuantity) {
            $payload['quantity'] = (int) ($validated['quantity'] ?? 0);
        }

        return $payload;
    }

    protected function normalizeNullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function notifyAdmins(
        string $type,
        string $title,
        string $message,
        string $url,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): void {
        $admins = User::where('role', 'admin')->get();

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
}
