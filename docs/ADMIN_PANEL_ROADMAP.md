# 🗺️ Roadmap - Admin Panel & User Management System

> **Projet** : MCP Manager
> **Objectif** : Implémenter un système complet d'administration des utilisateurs
> **Durée estimée** : 5-7 jours
> **Priorité** : 🔴 CRITIQUE

---

## 📊 I. VUE D'ENSEMBLE

### Problème Actuel

Le projet MCP Manager dispose d'un excellent dashboard pour gérer les intégrations MCP, mais **manque complètement** de fonctionnalités d'administration des utilisateurs :

- ❌ Pas de page admin pour gérer les utilisateurs
- ❌ Pas de système de rôles et permissions
- ❌ Pas de générateur de credentials (passwords, API tokens, Base64)
- ❌ Pas de gestion multi-tenant avancée
- ❌ Pas de logs d'activité utilisateur

### Solution Proposée

Implémenter un **Admin Panel complet** avec :

1. ✅ Gestion CRUD des utilisateurs
2. ✅ Système de rôles (Admin, Manager, User, ReadOnly)
3. ✅ Générateur de credentials sécurisés
4. ✅ Encodage Base64 pour Basic Auth
5. ✅ Gestion des permissions granulaires
6. ✅ Logs d'activité et audit trail
7. ✅ Dashboard analytics pour les admins

---

## 🎯 II. OBJECTIFS PAR PHASE

### Phase 1 - Infrastructure Backend (2 jours)
- Migrations base de données
- Modèles et relations
- Enums pour rôles et permissions
- Middleware d'autorisation

### Phase 2 - API & Controllers (1 jour)
- Controllers d'administration
- Endpoints API
- Validation des requêtes
- Services métier

### Phase 3 - Interface React (2 jours)
- Pages admin
- Composants UI
- Formulaires avec validation
- Intégration React Query

### Phase 4 - Générateur de Credentials (1 jour)
- Génération passwords sécurisés
- Génération API tokens
- Encodage Base64 pour Basic Auth
- Interface de copie/partage

### Phase 5 - Tests & Documentation (1 jour)
- Tests unitaires et E2E
- Documentation utilisateur
- Guide déploiement
- Seeders de démo

---

## 📅 III. ROADMAP DÉTAILLÉE

## PHASE 1 - Infrastructure Backend (2 jours)

### 1.1 Migrations Database

#### Migration 1 : Ajouter rôles et permissions aux users

**Fichier** : `database/migrations/2025_11_02_000001_add_roles_and_permissions_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Système de rôles
            $table->string('role')->default('user')->after('email');

            // Permissions granulaires (JSON array)
            $table->json('permissions')->nullable()->after('role');

            // Statut du compte
            $table->boolean('is_active')->default(true)->after('permissions');
            $table->boolean('is_locked')->default(false)->after('is_active');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->string('locked_reason')->nullable()->after('locked_at');

            // Tracking de connexion
            $table->timestamp('last_login_at')->nullable()->after('locked_reason');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_ip');
            $table->timestamp('last_failed_login_at')->nullable()->after('failed_login_attempts');

            // Metadata
            $table->text('notes')->nullable()->after('last_failed_login_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Indexes
            $table->index('role');
            $table->index('is_active');
            $table->index('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
```

#### Migration 2 : Table d'audit utilisateur

**Fichier** : `database/migrations/2025_11_02_000002_create_user_activity_logs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            // Type d'action
            $table->string('action'); // login, logout, created, updated, deleted, credentials_generated, etc.
            $table->string('entity_type')->nullable(); // User, McpServer, Integration, etc.
            $table->unsignedBigInteger('entity_id')->nullable();

            // Détails
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();

            // Context
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();

            // Metadata
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('performed_by');
            $table->index('action');
            $table->index('created_at');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
```

#### Migration 3 : Tokens de réinitialisation personnalisés

**Fichier** : `database/migrations/2025_11_02_000003_create_user_tokens_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Token info
            $table->string('token_type'); // api_token, invitation, password_reset, mcp_auth
            $table->string('token', 128)->unique();
            $table->string('name')->nullable(); // Friendly name for the token

            // Scopes & permissions
            $table->json('scopes')->nullable();

            // Expiration
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            // Security
            $table->integer('usage_count')->default(0);
            $table->integer('max_usages')->nullable();
            $table->boolean('is_active')->default(true);

            // Metadata
            $table->string('created_by_ip')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('token_type');
            $table->index('token');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tokens');
    }
};
```

---

### 1.2 Enums & Models

#### Enum : UserRole

**Fichier** : `app/Enums/UserRole.php`

```php
<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case USER = 'user';
    case READ_ONLY = 'read_only';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::USER => 'User',
            self::READ_ONLY => 'Read Only',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full access to all features including user management',
            self::MANAGER => 'Can manage integrations and workflows but not users',
            self::USER => 'Standard user with access to own resources',
            self::READ_ONLY => 'View-only access, cannot modify anything',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::ADMIN => [
                'users.*',
                'mcp_servers.*',
                'integrations.*',
                'workflows.*',
                'logs.*',
                'settings.*',
            ],
            self::MANAGER => [
                'mcp_servers.view',
                'mcp_servers.manage',
                'integrations.*',
                'workflows.*',
                'logs.view',
            ],
            self::USER => [
                'mcp_servers.view',
                'integrations.view',
                'integrations.manage_own',
                'workflows.view',
                'workflows.execute',
            ],
            self::READ_ONLY => [
                'mcp_servers.view',
                'integrations.view',
                'workflows.view',
                'logs.view',
            ],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn (self $role) => [
                'value' => $role->value,
                'label' => $role->label(),
                'description' => $role->description(),
            ],
            self::cases()
        );
    }
}
```

#### Enum : UserPermission

**Fichier** : `app/Enums/UserPermission.php`

```php
<?php

namespace App\Enums;

enum UserPermission: string
{
    // Users
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_EDIT = 'users.edit';
    case USERS_DELETE = 'users.delete';
    case USERS_MANAGE_ROLES = 'users.manage_roles';

    // MCP Servers
    case MCP_SERVERS_VIEW = 'mcp_servers.view';
    case MCP_SERVERS_CREATE = 'mcp_servers.create';
    case MCP_SERVERS_EDIT = 'mcp_servers.edit';
    case MCP_SERVERS_DELETE = 'mcp_servers.delete';
    case MCP_SERVERS_MANAGE = 'mcp_servers.manage';

    // Integrations
    case INTEGRATIONS_VIEW = 'integrations.view';
    case INTEGRATIONS_CREATE = 'integrations.create';
    case INTEGRATIONS_EDIT = 'integrations.edit';
    case INTEGRATIONS_DELETE = 'integrations.delete';
    case INTEGRATIONS_MANAGE_OWN = 'integrations.manage_own';

    // Workflows
    case WORKFLOWS_VIEW = 'workflows.view';
    case WORKFLOWS_CREATE = 'workflows.create';
    case WORKFLOWS_EDIT = 'workflows.edit';
    case WORKFLOWS_DELETE = 'workflows.delete';
    case WORKFLOWS_EXECUTE = 'workflows.execute';

    // Logs
    case LOGS_VIEW = 'logs.view';
    case LOGS_EXPORT = 'logs.export';
    case LOGS_DELETE = 'logs.delete';

    // Settings
    case SETTINGS_VIEW = 'settings.view';
    case SETTINGS_EDIT = 'settings.edit';

    public function label(): string
    {
        return str_replace(['_', '.'], ' ', ucwords($this->value, '_.'));
    }

    public function category(): string
    {
        return explode('.', $this->value)[0];
    }

    public static function groupedByCategory(): array
    {
        $grouped = [];
        foreach (self::cases() as $permission) {
            $category = $permission->category();
            $grouped[$category][] = [
                'value' => $permission->value,
                'label' => $permission->label(),
            ];
        }
        return $grouped;
    }
}
```

#### Model : UserActivityLog

**Fichier** : `app/Models/UserActivityLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $performed_by
 * @property string $action
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $description
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'performed_by',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'session_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function getFormattedDescriptionAttribute(): string
    {
        $performer = $this->performedBy?->name ?? 'System';
        $target = $this->user->name;

        return match ($this->action) {
            'login' => "{$target} logged in",
            'logout' => "{$target} logged out",
            'created' => "{$performer} created user {$target}",
            'updated' => "{$performer} updated user {$target}",
            'deleted' => "{$performer} deleted user {$target}",
            'credentials_generated' => "{$performer} generated credentials for {$target}",
            'password_reset' => "{$target} reset their password",
            'role_changed' => "{$performer} changed {$target}'s role",
            'locked' => "{$performer} locked user {$target}",
            'unlocked' => "{$performer} unlocked user {$target}",
            default => $this->description ?? $this->action,
        };
    }
}
```

#### Model : UserToken

**Fichier** : `app/Models/UserToken.php`

```php
<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token_type
 * @property string $token
 * @property string|null $name
 * @property array|null $scopes
 * @property Carbon|null $expires_at
 * @property Carbon|null $last_used_at
 * @property int $usage_count
 * @property int|null $max_usages
 * @property bool $is_active
 * @property string|null $created_by_ip
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserToken extends Model
{
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

    protected $casts = [
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->max_usages && $this->usage_count >= $this->max_usages) {
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
        $length = strlen($this->token);
        $visibleChars = min(8, (int) ($length * 0.2));
        $maskedChars = $length - $visibleChars;

        return substr($this->token, 0, $visibleChars) . str_repeat('*', $maskedChars);
    }
}
```

#### Update Model : User

**Fichier** : `app/Models/User.php` (modifications)

```php
<?php

namespace App\Models;

use App\Enums\UserRole;
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
 * @property string $role
 * @property array|null $permissions
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
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

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

    // Relations existantes
    public function integrationAccounts(): HasMany
    {
        return $this->hasMany(IntegrationAccount::class);
    }

    public function mcpServer(): HasOne
    {
        return $this->hasOne(McpServer::class);
    }

    public function mcpIntegrations(): HasMany
    {
        return $this->hasMany(McpIntegration::class);
    }

    // Nouvelles relations
    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(UserToken::class);
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    // Authorization methods
    public function hasRole(UserRole|string $role): bool
    {
        if ($role instanceof UserRole) {
            return $this->role === $role;
        }

        return $this->role->value === $role;
    }

    public function hasPermission(string $permission): bool
    {
        // Admins have all permissions
        if ($this->role === UserRole::ADMIN) {
            return true;
        }

        // Check role default permissions
        $rolePermissions = $this->role->permissions();
        foreach ($rolePermissions as $rolePermission) {
            if ($this->matchesPermission($permission, $rolePermission)) {
                return true;
            }
        }

        // Check custom permissions
        if ($this->permissions) {
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
        // Exact match
        if ($required === $granted) {
            return true;
        }

        // Wildcard match (e.g., users.* matches users.view, users.edit, etc.)
        if (str_ends_with($granted, '.*')) {
            $prefix = substr($granted, 0, -2);
            return str_starts_with($required, $prefix . '.');
        }

        return false;
    }

    public function can($abilities, $arguments = []): bool
    {
        // Override Laravel's default can() to use our permission system
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

    // Account status methods
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
            'ip_address' => request()->ip(),
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
            'ip_address' => request()->ip(),
        ]);
    }

    public function recordLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'failed_login_attempts' => 0,
        ]);

        UserActivityLog::create([
            'user_id' => $this->id,
            'action' => 'login',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function recordFailedLogin(): void
    {
        $this->increment('failed_login_attempts');
        $this->update(['last_failed_login_at' => now()]);

        // Auto-lock after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->lock('Too many failed login attempts');
        }
    }
}
```

---

### 1.3 Middleware & Services

#### Middleware : RequireRole

**Fichier** : `app/Http/Middleware/RequireRole.php`

```php
<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $allowedRoles = array_map(fn ($role) => UserRole::from($role), $roles);

        if (! in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Insufficient permissions. Required role: ' . implode(', ', array_map(fn ($r) => $r->label(), $allowedRoles)));
        }

        return $next($request);
    }
}
```

#### Middleware : RequirePermission

**Fichier** : `app/Http/Middleware/RequirePermission.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        foreach ($permissions as $permission) {
            if (! $user->hasPermission($permission)) {
                abort(403, "Insufficient permissions. Required: {$permission}");
            }
        }

        return $next($request);
    }
}
```

#### Service : UserManagementService

**Fichier** : `app/Services/UserManagementService.php`

```php
<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementService
{
    public function createUser(array $data, ?User $performedBy = null): User
    {
        $password = $data['password'] ?? Str::random(16);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => $data['role'] ?? UserRole::USER->value,
            'permissions' => $data['permissions'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null,
            'created_by' => $performedBy?->id,
        ]);

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'created',
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
            'ip_address' => request()->ip(),
        ]);

        return $user;
    }

    public function updateUser(User $user, array $data, ?User $performedBy = null): User
    {
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
        ];

        $user->update($data);

        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
        ];

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);

        return $user;
    }

    public function deleteUser(User $user, ?User $performedBy = null): bool
    {
        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'deleted',
            'old_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
            'ip_address' => request()->ip(),
        ]);

        return $user->delete();
    }

    public function generateCredentials(User $user, ?User $performedBy = null): array
    {
        // Generate secure password
        $password = $this->generateSecurePassword();

        // Generate API token
        $apiToken = hash('sha256', Str::random(60));

        // Update user
        $user->update([
            'password' => Hash::make($password),
            'api_token' => $apiToken,
        ]);

        // Generate Basic Auth header
        $basicAuth = base64_encode("{$user->email}:{$password}");

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'credentials_generated',
            'description' => 'New credentials generated',
            'ip_address' => request()->ip(),
        ]);

        return [
            'password' => $password,
            'api_token' => $apiToken,
            'basic_auth' => $basicAuth,
            'basic_auth_header' => "Authorization: Basic {$basicAuth}",
        ];
    }

    public function resetPassword(User $user, ?string $newPassword = null, ?User $performedBy = null): string
    {
        $password = $newPassword ?? $this->generateSecurePassword();

        $user->update([
            'password' => Hash::make($password),
        ]);

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'password_reset',
            'description' => $performedBy ? 'Password reset by admin' : 'Password reset by user',
            'ip_address' => request()->ip(),
        ]);

        return $password;
    }

    public function generateSecurePassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}';

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    }

    public function changeRole(User $user, UserRole $newRole, ?User $performedBy = null): User
    {
        $oldRole = $user->role;

        $user->update(['role' => $newRole]);

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'role_changed',
            'old_values' => ['role' => $oldRole->value],
            'new_values' => ['role' => $newRole->value],
            'ip_address' => request()->ip(),
        ]);

        return $user;
    }

    public function updatePermissions(User $user, array $permissions, ?User $performedBy = null): User
    {
        $oldPermissions = $user->permissions ?? [];

        $user->update(['permissions' => $permissions]);

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'permissions_updated',
            'old_values' => ['permissions' => $oldPermissions],
            'new_values' => ['permissions' => $permissions],
            'ip_address' => request()->ip(),
        ]);

        return $user;
    }
}
```

---

## PHASE 2 - API & Controllers (1 jour)

### 2.1 Form Requests

#### CreateUserRequest

**Fichier** : `app/Http/Requests/Admin/CreateUserRequest.php`

```php
<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('users.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
```

#### UpdateUserRequest

**Fichier** : `app/Http/Requests/Admin/UpdateUserRequest.php`

```php
<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('users.edit');
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'role' => ['sometimes', 'required', Rule::enum(UserRole::class)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
```

---

### 2.2 Controllers

#### UserManagementController

**Fichier** : `app/Http/Controllers/Admin/UserManagementController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function __construct(
        protected UserManagementService $userService
    ) {
        $this->middleware('auth');
        $this->middleware('role:admin,manager')->except(['index', 'show']);
    }

    public function index(Request $request): Response
    {
        $query = User::query()
            ->with(['mcpServer', 'activityLogs' => fn ($q) => $q->latest()->limit(5)])
            ->withCount('integrationAccounts');

        // Filters
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_locked')) {
            $query->where('is_locked', $request->boolean('is_locked'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($request->get('per_page', 15));

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'is_active', 'is_locked', 'sort_by', 'sort_order']),
            'roles' => UserRole::options(),
            'can' => [
                'create' => $request->user()->hasPermission('users.create'),
                'edit' => $request->user()->hasPermission('users.edit'),
                'delete' => $request->user()->hasPermission('users.delete'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => UserRole::options(),
        ]);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('mcpServer', 'integrationAccounts'),
        ], 201);
    }

    public function show(User $user): Response
    {
        $user->load([
            'mcpServer',
            'integrationAccounts',
            'activityLogs.performedBy',
            'tokens' => fn ($q) => $q->latest(),
        ]);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'can' => [
                'edit' => request()->user()->hasPermission('users.edit'),
                'delete' => request()->user()->hasPermission('users.delete'),
            ],
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => UserRole::options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->updateUser(
            $user,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh()->load('mcpServer', 'integrationAccounts'),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account',
            ], 403);
        }

        $this->userService->deleteUser($user, auth()->user());

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function generateCredentials(User $user): JsonResponse
    {
        $credentials = $this->userService->generateCredentials(
            $user,
            auth()->user()
        );

        return response()->json([
            'message' => 'Credentials generated successfully',
            'credentials' => $credentials,
        ]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $password = $this->userService->resetPassword(
            $user,
            $request->input('password'),
            auth()->user()
        );

        return response()->json([
            'message' => 'Password reset successfully',
            'password' => $password,
        ]);
    }

    public function lock(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user->lock($request->input('reason'), auth()->user());

        return response()->json([
            'message' => 'User locked successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function unlock(User $user): JsonResponse
    {
        $user->unlock(auth()->user());

        return response()->json([
            'message' => 'User unlocked successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function changeRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $user = $this->userService->changeRole(
            $user,
            UserRole::from($request->input('role')),
            auth()->user()
        );

        return response()->json([
            'message' => 'Role changed successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function updatePermissions(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        $user = $this->userService->updatePermissions(
            $user,
            $request->input('permissions'),
            auth()->user()
        );

        return response()->json([
            'message' => 'Permissions updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function activityLog(User $user): JsonResponse
    {
        $logs = $user->activityLogs()
            ->with('performedBy')
            ->latest()
            ->paginate(50);

        return response()->json($logs);
    }
}
```

---

### 2.3 Routes

**Fichier** : `routes/admin.php` (nouveau fichier)

```php
<?php

use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // User Management
    Route::resource('users', UserManagementController::class);

    // User Actions
    Route::post('users/{user}/generate-credentials', [UserManagementController::class, 'generateCredentials'])
        ->name('users.generate-credentials');
    Route::post('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
        ->name('users.reset-password');
    Route::post('users/{user}/lock', [UserManagementController::class, 'lock'])
        ->name('users.lock');
    Route::post('users/{user}/unlock', [UserManagementController::class, 'unlock'])
        ->name('users.unlock');
    Route::post('users/{user}/change-role', [UserManagementController::class, 'changeRole'])
        ->name('users.change-role');
    Route::post('users/{user}/permissions', [UserManagementController::class, 'updatePermissions'])
        ->name('users.permissions');
    Route::get('users/{user}/activity-log', [UserManagementController::class, 'activityLog'])
        ->name('users.activity-log');
});
```

**Fichier** : `routes/web.php` (ajout)

```php
// Add to existing routes
require __DIR__.'/admin.php';
```

---

**[CONTINUÉ DANS LA PARTIE 2 DU FICHIER...]**

---

## PHASE 3 - Interface React (Suite dans prochain message)

**À suivre** :
- Pages React (Index, Create, Edit, Show)
- Composants UI (UserTable, CredentialGenerator, RoleSelector)
- Formulaires avec validation
- Intégration React Query

---

**Dernière mise à jour** : 2025-11-02
**Version** : 1.0
**Auteur** : Claude Code Analysis