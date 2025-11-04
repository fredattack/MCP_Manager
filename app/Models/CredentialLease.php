<?php

namespace App\Models;

use App\Enums\LeaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class CredentialLease extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'lease_id',
        'server_id',
        'services',
        'credentials',
        'credential_scope',
        'included_org_credentials',
        'expires_at',
        'renewable',
        'renewal_count',
        'max_renewals',
        'status',
        'client_info',
        'client_ip',
        'last_renewed_at',
        'revoked_at',
        'revocation_reason',
    ];

    protected $casts = [
        'services' => 'array',
        'included_org_credentials' => 'array',
        'expires_at' => 'datetime',
        'renewable' => 'boolean',
        'renewal_count' => 'integer',
        'max_renewals' => 'integer',
        'status' => LeaseStatus::class,
        'last_renewed_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CredentialLease $lease) {
            if (! $lease->lease_id) {
                $lease->lease_id = 'lse_'.Str::random(40);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getDecryptedCredentials(): array
    {
        return json_decode(Crypt::decryptString($this->credentials), true);
    }

    public function setEncryptedCredentials(array $credentials): void
    {
        $this->credentials = Crypt::encryptString(json_encode($credentials));
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isActive(): bool
    {
        return $this->status === LeaseStatus::Active && ! $this->isExpired();
    }

    public function canRenew(): bool
    {
        return $this->renewable
            && $this->status === LeaseStatus::Active
            && $this->renewal_count < $this->max_renewals
            && ! $this->isExpired();
    }

    public function renew(int $ttl = 3600): bool
    {
        if (! $this->canRenew()) {
            return false;
        }

        $this->expires_at = now()->addSeconds($ttl);
        $this->last_renewed_at = now();
        $this->renewal_count++;

        return $this->save();
    }

    public function revoke(string $reason = null): bool
    {
        $this->status = LeaseStatus::Revoked;
        $this->revoked_at = now();
        $this->revocation_reason = $reason;
        $this->renewable = false;

        return $this->save();
    }

    public function markAsExpired(): bool
    {
        $this->status = LeaseStatus::Expired;
        $this->renewable = false;

        return $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('status', LeaseStatus::Active)
            ->where('expires_at', '>', now());
    }

    public function scopeExpiringSoon($query, int $minutes = 10)
    {
        return $query->where('status', LeaseStatus::Active)
            ->whereBetween('expires_at', [now(), now()->addMinutes($minutes)]);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForServer($query, string $serverId)
    {
        return $query->where('server_id', $serverId);
    }
}
