<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpSyncLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'sync_type',
        'direction',
        'status',
        'request_payload',
        'response_payload',
        'error_message',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'duration_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('sync_type', $type);
    }

    public static function logSync(
        ?int $userId,
        string $syncType,
        string $direction,
        string $status,
        ?array $requestPayload = null,
        ?array $responsePayload = null,
        ?string $errorMessage = null,
        ?int $durationMs = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'sync_type' => $syncType,
            'direction' => $direction,
            'status' => $status,
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
        ]);
    }
}
