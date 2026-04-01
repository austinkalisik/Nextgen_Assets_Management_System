<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'part_no',
        'brand',
        'part_name',
        'description',
        'category_id',
        'supplier_id',
        'asset_tag',
        'serial_number',
        'status',
        'assigned_to',
        'location',
        'purchase_date',
        'quantity'
    ];

    protected $attributes = [
        'status' => 'available',
        'quantity' => 1,
    ];

    /**
     * =============================
     * RELATIONSHIPS
     * =============================
     */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(Assignment::class)
            ->whereNull('returned_at');
    }

    public function assignedUser()
    {
        return $this->hasOneThrough(
            User::class,
            Assignment::class,
            'item_id',
            'id',
            'id',
            'user_id'
        )->whereNull('assignments.returned_at');
    }

    /**
     * =============================
     *  AUTO STATUS (ERD BASED)
     * =============================
     */
    public function getComputedStatusAttribute()
    {
        return $this->activeAssignment ? 'assigned' : 'available';
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->computed_status);
    }
}