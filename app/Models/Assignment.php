<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'item_id',
        'user_id',
        'department_id',
        'assigned_at',
        'returned_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    //  Asset relationship
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    //  User assigned to asset
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //  Department (FIXED - consistent with DB)
    public function assignedDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    //  Helper: check if active
    public function isActive(): bool
    {
        return is_null($this->returned_at);
    }
}