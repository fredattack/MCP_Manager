<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $url
 * @property string|null $public_key
 * @property string|null $private_key
 * @property string|null $server_public_key
 * @property string|null $ssl_certificate
 * @property array<string, mixed>|null $config
 * @property string $status
 * @property string|null $session_token
 * @property string|null $error_message
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class McpServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'public_key',
        'private_key',
        'server_public_key',
        'ssl_certificate',
        'config',
        'status',
        'session_token',
        'error_message',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    protected $hidden = [
        'private_key',
        'session_token',
    ];

    /**
     * Get the user that owns the MCP server
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the integrations for this MCP server
     */
    public function integrations(): HasMany
    {
        return $this->hasMany(McpIntegration::class);
    }

    /**
     * Encrypt private key before storing
     */
    protected function privateKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Encrypt session token before storing
     */
    protected function sessionToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Check if server is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if server has error
     */
    public function hasError(): bool
    {
        return $this->status === 'error';
    }

    /**
     * Get server health status
     */
    public function getHealthStatus(): array
    {
        return [
            'status' => $this->status,
            'connected' => $this->isActive(),
            'has_session' => !empty($this->session_token),
            'error' => $this->error_message,
            'last_check' => $this->updated_at,
        ];
    }
}
