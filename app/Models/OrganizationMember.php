<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'permissions',
        'invited_by',
        'joined_at',
    ];

    protected $casts = [
        'role' => OrganizationRole::class,
        'permissions' => 'array',
        'joined_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === OrganizationRole::Owner) {
            return true;
        }

        $rolePermissions = $this->role->permissions();

        if (in_array('*', $rolePermissions)) {
            return true;
        }

        $customPermissions = $this->permissions ?? [];

        return in_array($permission, $rolePermissions)
            || in_array($permission, $customPermissions);
    }

    public function canManageMembers(): bool
    {
        return $this->role->canManageMembers();
    }

    public function canManageCredentials(): bool
    {
        return $this->role->canManageCredentials();
    }

    public function isOwner(): bool
    {
        return $this->role === OrganizationRole::Owner;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [OrganizationRole::Owner, OrganizationRole::Admin]);
    }
}
