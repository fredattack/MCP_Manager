<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property UserRole $role
 * @property array<int, string>|null $permissions
 * @property bool $is_active
 * @property bool $is_locked
 * @property \Carbon\Carbon|null $locked_at
 * @property string|null $locked_reason
 * @property \Carbon\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property int $failed_login_attempts
 * @property \Carbon\Carbon|null $last_failed_login_at
 * @property string|null $notes
 * @property int|null $created_by
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
    use HasFactory;

    use Notifiable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
        'is_locked',
        'locked_at',
        'locked_reason',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'last_failed_login_at',
        'notes',
        'created_by',
        'api_token',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'permissions' => 'array',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<IntegrationAccount, $this>
     */
    public function integrationAccounts(): HasMany
    {
        return $this->hasMany(IntegrationAccount::class);
    }

    /**
     * @return HasOne<McpServer, $this>
     */
    public function mcpServer(): HasOne
    {
        return $this->hasOne(McpServer::class);
    }

    /**
     * @return HasMany<McpIntegration, $this>
     */
    public function mcpIntegrations(): HasMany
    {
        return $this->hasMany(McpIntegration::class);
    }

    /**
     * @return HasMany<UserActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * @return HasMany<UserToken, $this>
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(UserToken::class);
    }

    /**
     * @return HasOne<McpServerUser, $this>
     */
    public function mcpServerUser(): HasOne
    {
        return $this->hasOne(McpServerUser::class);
    }

    /**
     * @return HasMany<McpAccessToken, $this>
     */
    public function mcpAccessTokens(): HasMany
    {
        return $this->hasMany(McpAccessToken::class);
    }

    /**
     * @return HasMany<McpSyncLog, $this>
     */
    public function mcpSyncLogs(): HasMany
    {
        return $this->hasMany(McpSyncLog::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasRole(UserRole|string $role): bool
    {
        if ($role instanceof UserRole) {
            return $this->role === $role;
        }

        return $this->role->value === $role;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === UserRole::ADMIN) {
            return true;
        }

        foreach ($this->role->permissions() as $rolePermission) {
            if ($this->matchesPermission($permission, $rolePermission)) {
                return true;
            }
        }

        if ($this->permissions !== null) {
            foreach ($this->permissions as $customPermission) {
                if ($this->matchesPermission($permission, $customPermission)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function matchesPermission(string $required, string $granted): bool
    {
        if ($required === $granted) {
            return true;
        }

        if (str_ends_with($granted, '.*')) {
            $prefix = substr($granted, 0, -2);

            return str_starts_with($required, $prefix.'.');
        }

        return false;
    }

    public function can($abilities, $arguments = []): bool
    {
        if (is_string($abilities)) {
            return $this->hasPermission($abilities);
        }

        return parent::can($abilities, $arguments);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::MANAGER;
    }

    public function canManageUsers(): bool
    {
        return $this->hasPermission('users.edit');
    }

    public function lock(string $reason, ?User $performedBy = null): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_reason' => $reason,
        ]);

        UserActivityLog::create([
            'user_id' => $this->id,
            'performed_by' => $performedBy?->id,
            'action' => 'locked',
            'description' => $reason,
            'ip_address' => request()?->ip(),
        ]);
    }

    public function unlock(?User $performedBy = null): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_reason' => null,
            'failed_login_attempts' => 0,
        ]);

        UserActivityLog::create([
            'user_id' => $this->id,
            'performed_by' => $performedBy?->id,
            'action' => 'unlocked',
            'ip_address' => request()?->ip(),
        ]);
    }

    public function recordLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()?->ip(),
            'failed_login_attempts' => 0,
        ]);

        UserActivityLog::create([
            'user_id' => $this->id,
            'action' => 'login',
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function recordFailedLogin(): void
    {
        $this->increment('failed_login_attempts');
        $this->update(['last_failed_login_at' => now()]);

        if ($this->failed_login_attempts >= 5) {
            $this->lock('Too many failed login attempts');
        }
    }
}
