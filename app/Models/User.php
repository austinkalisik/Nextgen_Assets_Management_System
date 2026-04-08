<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $name
 * @property string $email
 * @property string $role
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignments()
    {
        return $this->hasMany(Assignment::class)->whereNull('returned_at');
    }

    public function assetLogs()
    {
        return $this->hasMany(AssetLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}