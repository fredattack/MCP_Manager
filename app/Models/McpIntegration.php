<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $mcp_server_id
 * @property string $service_name
 * @property bool $enabled
 * @property string $status
 * @property array<string, mixed>|null $config
 * @property \Carbon\Carbon|null $last_sync_at
 * @property string|null $error_message
 * @property bool $credentials_valid
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class McpIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mcp_server_id',
        'service_name',
        'enabled',
        'status',
        'config',
        'last_sync_at',
        'error_message',
        'credentials_valid',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'credentials_valid' => 'boolean',
        'config' => 'array',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get the user that owns the integration
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the MCP server for this integration
     */
    public function mcpServer(): BelongsTo
    {
        return $this->belongsTo(McpServer::class);
    }

    /**
     * Check if integration is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->enabled;
    }

    /**
     * Check if integration has error
     */
    public function hasError(): bool
    {
        return $this->status === 'error' || !$this->credentials_valid;
    }

    /**
     * Get integration status details
     */
    public function getStatusDetails(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->service_name,
            'status' => $this->status,
            'enabled' => $this->enabled,
            'credentialsValid' => $this->credentials_valid,
            'lastSync' => $this->last_sync_at?->toIso8601String(),
            'errorMessage' => $this->error_message,
        ];
    }

    /**
     * Mark integration as synced
     */
    public function markAsSynced(): void
    {
        $this->update([
            'last_sync_at' => now(),
            'status' => 'active',
            'error_message' => null,
        ]);
    }

    /**
     * Mark integration as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $error,
            'credentials_valid' => false,
        ]);
    }
}
