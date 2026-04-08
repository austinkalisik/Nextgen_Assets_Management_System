<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $item_id
 * @property int $user_id
 * @property string $action
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item $item
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetLog whereUserId($value)
 * @mixin \Eloquent
 */
class AssetLog extends Model
{
    protected $fillable = [
        'item_id',
        'user_id',
        'action',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}