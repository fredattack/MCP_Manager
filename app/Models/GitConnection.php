<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property int $user_id
 * @property GitProvider $provider
 * @property string $external_user_id
 * @property array<string, mixed>|null $meta
 * @property array<int, string> $scopes
 * @property string $access_token_enc
 * @property string|null $refresh_token_enc
 * @property \Carbon\Carbon|null $expires_at
 * @property GitConnectionStatus $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GitRepository> $repositories
 */
class GitConnection extends Model
{
    /** @use HasFactory<\Database\Factories\GitConnectionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'external_user_id',
        'meta',
        'scopes',
        'access_token_enc',
        'refresh_token_enc',
        'expires_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'provider' => GitProvider::class,
        'status' => GitConnectionStatus::class,
        'meta' => 'array',
        'scopes' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the git connection.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the repositories for this connection.
     *
     * @return HasMany<GitRepository>
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(GitRepository::class, 'user_id', 'user_id')
            ->where('provider', $this->provider);
    }

    /**
     * Scope a query to only include active connections.
     *
     * @param  Builder<GitConnection>  $query
     * @return Builder<GitConnection>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', GitConnectionStatus::ACTIVE);
    }

    /**
     * Scope a query to only include connections for a specific provider.
     *
     * @param  Builder<GitConnection>  $query
     * @return Builder<GitConnection>
     */
    public function scopeForProvider(Builder $query, GitProvider $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    /**
     * Check if the token is expired or will expire soon (within 10 minutes).
     */
    public function isTokenExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return now()->addMinutes(10)->isAfter($this->expires_at);
    }

    /**
     * Get decrypted access token.
     */
    public function getAccessToken(): string
    {
        return Crypt::decryptString($this->access_token_enc);
    }

    /**
     * Set encrypted access token.
     */
    public function setAccessToken(string $token): void
    {
        $this->access_token_enc = Crypt::encryptString($token);
    }

    /**
     * Get decrypted refresh token.
     */
    public function getRefreshToken(): ?string
    {
        if ($this->refresh_token_enc === null) {
            return null;
        }

        return Crypt::decryptString($this->refresh_token_enc);
    }

    /**
     * Set encrypted refresh token.
     */
    public function setRefreshToken(?string $token): void
    {
        $this->refresh_token_enc = $token !== null ? Crypt::encryptString($token) : null;
    }
}
