<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_LOST = 'lost';
    public const STATUS_RETIRED = 'retired';

    protected $fillable = [
        'name',
        'sku',
        'brand',
        'description',
        'category_id',
        'supplier_id',
        'asset_tag',
        'serial_number',
        'quantity',
        'reorder_level',
        'unit_cost',
        'status',
        'location',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    protected $appends = [
        'is_low_stock',
        'is_assignable',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getIsLowStockAttribute(): bool
    {
        return $this->isLowStock();
    }

    public function getIsAssignableAttribute(): bool
    {
        return $this->isAssignable();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class)->whereNull('returned_at');
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)
            ->whereNull('returned_at')
            ->latestOfMany('assigned_at');
    }

    public function assetLogs(): HasMany
    {
        return $this->hasMany(AssetLog::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    public function isRetired(): bool
    {
        return $this->status === self::STATUS_RETIRED;
    }

    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    public function hasActiveAssignment(): bool
    {
        return $this->activeAssignments()->exists();
    }

    public function isLowStock(?int $threshold = null): bool
    {
        $threshold = $threshold ?? $this->reorder_level ?? 5;

        return (int) $this->quantity > 0 && (int) $this->quantity <= $threshold;
    }

    public function isAssignable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && (int) $this->quantity > 0;
    }

    public function getCalculatedQuantity(): int
    {
        return (int) $this->stockMovements()
            ->get()
            ->sum(fn(StockMovement $movement) => $movement->signed_quantity);
    }

    public function syncAutomatedStatus(): void
    {
        if ((int) $this->quantity < 0) {
            $this->quantity = 0;
        }

        if (in_array($this->status, [self::STATUS_MAINTENANCE, self::STATUS_RETIRED, self::STATUS_LOST], true)) {
            $this->save();
            return;
        }

        $this->status = self::STATUS_AVAILABLE;
        $this->save();
    }
}
