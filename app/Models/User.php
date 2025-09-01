<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $api_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, IntegrationAccount> $integrationAccounts
 * @property-read McpServer|null $mcpServer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, McpIntegration> $mcpIntegrations
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the integration accounts for the user.
     *
     * @return HasMany<IntegrationAccount, $this>
     */
    public function integrationAccounts(): HasMany
    {
        return $this->hasMany(IntegrationAccount::class);
    }

    /**
     * Get the MCP server for the user.
     *
     * @return HasOne<McpServer, $this>
     */
    public function mcpServer(): HasOne
    {
        return $this->hasOne(McpServer::class);
    }

    /**
     * Get the MCP integrations for the user.
     *
     * @return HasMany<McpIntegration, $this>
     */
    public function mcpIntegrations(): HasMany
    {
        return $this->hasMany(McpIntegration::class);
    }
}
