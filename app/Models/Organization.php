<?php

namespace App\Models;

use App\Enums\OrganizationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'billing_email',
        'status',
        'max_members',
        'settings',
    ];

    protected $casts = [
        'status' => OrganizationStatus::class,
        'settings' => 'array',
        'max_members' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Organization $organization) {
            if (! $organization->slug) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            OrganizationMember::class,
            'organization_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(IntegrationAccount::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(CredentialLease::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    public function isActive(): bool
    {
        return $this->status === OrganizationStatus::Active;
    }

    public function canAddMember(): bool
    {
        return $this->members()->count() < $this->max_members;
    }

    public function getMemberCount(): int
    {
        return $this->members()->count();
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function getMemberRole(User $user): ?string
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->value('role');
    }
}
