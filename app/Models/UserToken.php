<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserToken extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'token_type',
        'token',
        'name',
        'scopes',
        'expires_at',
        'last_used_at',
        'usage_count',
        'max_usages',
        'is_active',
        'created_by_ip',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'token',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->max_usages !== null && $this->usage_count >= $this->max_usages) {
            return false;
        }

        return true;
    }

    public function use(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }

    public function getMaskedTokenAttribute(): string
    {
        $token = (string) $this->getAttribute('token');
        $length = strlen($token);

        if ($length === 0) {
            return '';
        }

        $visibleChars = min(8, (int) ($length * 0.2));
        $maskedChars = max(0, $length - $visibleChars);

        return substr($token, 0, $visibleChars).str_repeat('*', $maskedChars);
    }
}
