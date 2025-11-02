<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpAccessToken extends Model
{
    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'token_type',
        'expires_at',
        'scope',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? decrypt($value) : null,
            set: fn (?string $value) => $value ? encrypt($value) : null,
        );
    }

    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? decrypt($value) : null,
            set: fn (?string $value) => $value ? encrypt($value) : null,
        );
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isExpiringSoon(?int $minutes = null): bool
    {
        $threshold = $minutes ?? config('mcp-server.tokens.refresh_threshold', 300) / 60;

        return $this->expires_at->diffInMinutes(now()) <= $threshold;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeExpiringSoon($query, ?int $minutes = null)
    {
        $threshold = $minutes ?? config('mcp-server.tokens.refresh_threshold', 300) / 60;

        return $query->where('expires_at', '<=', now()->addMinutes($threshold))
            ->where('expires_at', '>', now());
    }
}
