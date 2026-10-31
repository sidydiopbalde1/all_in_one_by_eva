<?php

namespace App\Models;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role_id', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function employe(): HasOne
    {
        return $this->hasOne(Employe::class);
    }

    public function hasRole(RoleEnum $role): bool
    {
        return $this->role?->name === $role->value;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleEnum::Admin);
    }

    public function hasPermission(PermissionEnum|string $permission): bool
    {
        $name = $permission instanceof PermissionEnum ? $permission->value : $permission;

        return $this->role?->permissions->contains('name', $name) ?? false;
    }
}
