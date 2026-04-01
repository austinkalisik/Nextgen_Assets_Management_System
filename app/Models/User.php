<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * =============================
     * MASS ASSIGNABLE FIELDS
     * =============================
     */
    public function assignment()
    {
        return $this->hasMany(Assignment::class);
    }
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * =============================
     * DEFAULT ROLE ( IMPORTANT)
     * =============================
     */
    protected $attributes = [
        'role' => 'user', // default role
    ];

    /**
     * =============================
     * HIDDEN FIELDS
     * =============================
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}