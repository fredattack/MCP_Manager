<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpServerUser extends Model
{
    protected $fillable = [
        'user_id',
        'mcp_user_uuid',
        'mcp_user_id',
        'sync_status',
        'last_sync_at',
        'sync_error',
        'sync_attempts',
    ];

    protected function casts(): array
    {
        return [
            'last_sync_at' => 'datetime',
            'sync_attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->sync_status === 'pending';
    }

    public function isSynced(): bool
    {
        return $this->sync_status === 'synced';
    }

    public function hasError(): bool
    {
        return $this->sync_status === 'error';
    }

    public function isOutOfSync(): bool
    {
        return $this->sync_status === 'out_of_sync';
    }

    public function markAsSynced(): void
    {
        $this->update([
            'sync_status' => 'synced',
            'last_sync_at' => now(),
            'sync_error' => null,
            'sync_attempts' => 0,
        ]);
    }

    public function markAsError(string $error): void
    {
        $this->update([
            'sync_status' => 'error',
            'sync_error' => $error,
            'sync_attempts' => $this->sync_attempts + 1,
        ]);
    }
}
