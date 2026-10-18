<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Role Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'mpdc_staff';
    }

    public function isInspector(): bool
    {
        return $this->role === 'inspector';
    }

    // Permission Checking
    public function hasPermission(string $permission): bool
    {
        // System Admins automatically bypass permission checks
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }
}