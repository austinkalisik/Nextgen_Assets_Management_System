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

    public function notifications()
    {
        return $this->hasMany(SystemNotification::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAssetOfficer(): bool
    {
        return $this->role === 'asset_officer';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canManageAssets(): bool
    {
        return $this->isAdmin() || $this->isAssetOfficer();
    }

    public function canMonitorOperations(): bool
    {
        return $this->isAdmin() || $this->isManager() || $this->isAssetOfficer();
    }
}