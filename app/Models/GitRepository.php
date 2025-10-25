<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GitProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property GitProvider $provider
 * @property string $external_id
 * @property string $full_name
 * @property string $default_branch
 * @property string $visibility
 * @property bool $archived
 * @property \Carbon\Carbon|null $last_synced_at
 * @property array<string, mixed>|null $meta
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GitClone> $clones
 */
class GitRepository extends Model
{
    /** @use HasFactory<\Database\Factories\GitRepositoryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'external_id',
        'full_name',
        'default_branch',
        'visibility',
        'archived',
        'last_synced_at',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'provider' => GitProvider::class,
        'archived' => 'boolean',
        'last_synced_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Get the user that owns the repository.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clones for this repository.
     *
     * @return HasMany<GitClone>
     */
    public function clones(): HasMany
    {
        return $this->hasMany(GitClone::class, 'repository_id');
    }

    /**
     * Scope a query to only include non-archived repositories.
     *
     * @param  Builder<GitRepository>  $query
     * @return Builder<GitRepository>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('archived', false);
    }

    /**
     * Scope a query to filter by provider.
     *
     * @param  Builder<GitRepository>  $query
     * @return Builder<GitRepository>
     */
    public function scopeForProvider(Builder $query, GitProvider $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope a query to filter by visibility.
     *
     * @param  Builder<GitRepository>  $query
     * @return Builder<GitRepository>
     */
    public function scopeVisibility(Builder $query, string $visibility): Builder
    {
        return $query->where('visibility', $visibility);
    }

    /**
     * Mark the repository as synced.
     */
    public function markAsSynced(): void
    {
        $this->update(['last_synced_at' => now()]);
    }

    /**
     * Get the repository owner from full_name.
     */
    public function getOwner(): string
    {
        return explode('/', $this->full_name)[0] ?? '';
    }

    /**
     * Get the repository name from full_name.
     */
    public function getName(): string
    {
        return explode('/', $this->full_name)[1] ?? '';
    }
}
