<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property int $category_id
 * @property int $supplier_id
 * @property int $department_id
 * @property string|null $asset_tag
 * @property string|null $serial_number
 * @property int $quantity
 * @property string $status
 * @property string|null $location
 * @property \Illuminate\Support\Carbon|null $purchase_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Assignment|null $activeAssignment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetLog> $assetLogs
 * @property-read int|null $asset_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Assignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Department $department
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereAssetTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Item extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'supplier_id',
        'department_id',
        'asset_tag',
        'serial_number',
        'quantity',
        'status',
        'location',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)->whereNull('returned_at')->latestOfMany('assigned_at');
    }

    public function assetLogs(): HasMany
    {
        return $this->hasMany(AssetLog::class);
    }
}