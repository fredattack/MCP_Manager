# Credential Lease System - Implementation Guide

# Instructions pour Claude

## Comportement critique

- Sois direct et critique dans tes évaluations
- Si tu vois un problème, dis-le clairement sans tourner autour du pot
- Challenge mes hypothèses et mes décisions techniques
- Propose des alternatives même si je n'en demande pas explicitement
- Ne confirme pas mes idées par défaut - évalue-les objectivement
- Signale les risques, les edge cases, et les problèmes potentiels
- Si mon approche est sous-optimale, explique pourquoi et suggère mieux

## Code review

- Pointe les bugs, même mineurs
- Identifie les problèmes de performance
- Suggère des refactorings quand le code peut être amélioré
- Mentionne les violations de bonnes pratiques

**Date:** 2025-11-04
**Status:** Phase 3 Complete ✅ | Phase 4 Pending ⏳
**Last Updated:** 2025-11-05

---

## 🎯 Executive Summary

This document tracks the implementation of a secure **Credential Lease System** with **Multi-Tenant Organizations** for MCP Manager. The system allows:

1. **Organizations** - Users can belong to organizations and share credentials
2. **Credential Scoping** - Personal vs Organization credentials with granular sharing
3. **Lease-Based Access** - MCP Server requests temporary credential leases (TTL: 1h)
4. **Automatic Renewal** - Leases auto-refresh every 50 minutes
5. **Instant Revocation** - When a user leaves an org, all their leases are revoked
6. **Audit Logging** - Complete trail of all credential access

---

## ✅ Phase 1: Database & Models (COMPLETED)

### 1.1 Migrations Created ✅

#### `organizations` Table ✅

```sql
- id (PK)
- name
- slug (unique)
- owner_id (FK users)
- billing_email
- status (active, suspended, deleted)
- max_members (default: 5)
- settings (JSON)
- timestamps
```

**File:** `database/migrations/2025_11_04_185610_create_organizations_table.php`

#### `organization_members` Table ✅

```sql
- id (PK)
- organization_id (FK organizations)
- user_id (FK users)
- role (owner, admin, member, guest)
- permissions (JSON array)
- invited_by (FK users, nullable)
- joined_at
- timestamps
- UNIQUE(organization_id, user_id)
```

**File:** `database/migrations/2025_11_04_185633_create_organization_members_table.php`

#### `organization_invitations` Table ✅

```sql
- id (PK)
- organization_id (FK organizations)
- email
- role
- token (64 chars, unique)
- invited_by (FK users)
- expires_at (default: 7 days)
- accepted_at (nullable)
- timestamps
- UNIQUE(organization_id, email)
```

**File:** `database/migrations/2025_11_04_185752_create_organization_invitations_table.php`

#### `integration_accounts` Modification ✅

```sql
ALTER TABLE integration_accounts
ADD COLUMN:
- organization_id (FK organizations, nullable)
- scope (personal|organization, default: personal)
- shared_with (JSON array, nullable)
- created_by (FK users, nullable)

CONSTRAINT:
- personal scope → organization_id IS NULL, user_id NOT NULL
- organization scope → organization_id NOT NULL
```

**File:** `database/migrations/2025_11_04_185815_add_organization_support_to_integration_accounts.php`

#### `credential_leases` Table ✅

```sql
- id (PK)
- user_id (FK users)
- organization_id (FK organizations, nullable)
- lease_id (unique, format: lse_...)
- server_id (MCP server identifier)
- services (JSON array of requested services)
- credentials (TEXT, encrypted)
- credential_scope (personal|organization|mixed)
- included_org_credentials (JSON, tracks sources)
- expires_at
- renewable (boolean)
- renewal_count
- max_renewals (default: 24)
- status (active, expired, revoked)
- client_info (user agent, etc.)
- client_ip
- last_renewed_at
- revoked_at
- revocation_reason
- timestamps
```

**File:** `database/migrations/2025_11_04_185900_create_credential_leases_table.php`

### 1.2 Enums Created ✅

#### `OrganizationRole` Enum ✅

```php
Owner  → Full control
Admin  → Can manage members and credentials
Member → Can use shared credentials
Guest  → Limited read-only access

Methods:
- displayName()
- description()
- permissions(): array
- canManageMembers(): bool
- canManageCredentials(): bool
```

**File:** `app/Enums/OrganizationRole.php`

#### `CredentialScope` Enum ✅

```php
Personal      → Only accessible by owner
Organization  → Shared within organization
```

**File:** `app/Enums/CredentialScope.php`

#### `LeaseStatus` Enum ✅

```php
Active   → Currently valid
Expired  → TTL exceeded
Revoked  → Manually revoked

Methods:
- isActive(): bool
- canRenew(): bool
```

**File:** `app/Enums/LeaseStatus.php`

#### `OrganizationStatus` Enum ✅

```php
Active     → Operational
Suspended  → Temporarily disabled
Deleted    → Soft-deleted
```

**File:** `app/Enums/OrganizationStatus.php`

### 1.3 Models Created ✅

#### `Organization` Model ✅

**Relationships:**

- `owner()` → User (who created the org)
- `members()` → OrganizationMember collection
- `users()` → User collection (through members)
- `credentials()` → IntegrationAccount collection
- `leases()` → CredentialLease collection
- `invitations()` → OrganizationInvitation collection

**Methods:**

- `isActive(): bool`
- `canAddMember(): bool` - Checks against max_members limit
- `getMemberCount(): int`
- `hasMember(User): bool`
- `getMemberRole(User): ?string`

**File:** `app/Models/Organization.php`

#### `OrganizationMember` Model ✅

**Relationships:**

- `organization()` → Organization
- `user()` → User
- `inviter()` → User (who invited)

**Methods:**

- `hasPermission(string): bool` - Checks role + custom permissions
- `canManageMembers(): bool`
- `canManageCredentials(): bool`
- `isOwner(): bool`
- `isAdmin(): bool`

**File:** `app/Models/OrganizationMember.php`

#### `OrganizationInvitation` Model ✅

**Relationships:**

- `organization()` → Organization
- `inviter()` → User

**Methods:**

- `isExpired(): bool`
- `isAccepted(): bool`
- `isPending(): bool`
- `markAsAccepted(): void`

**Auto-generation:**

- `token` → 64-char random string
- `expires_at` → now() + 7 days

**File:** `app/Models/OrganizationInvitation.php`

#### `CredentialLease` Model ✅

**Relationships:**

- `user()` → User
- `organization()` → Organization (nullable)

**Methods:**

- `getDecryptedCredentials(): array` - Decrypt credentials
- `setEncryptedCredentials(array): void` - Encrypt and store
- `isExpired(): bool`
- `isActive(): bool`
- `canRenew(): bool` - Checks renewable, status, renewal_count, expiry
- `renew(int $ttl = 3600): bool` - Extend lease by TTL seconds
- `revoke(string $reason = null): bool` - Revoke lease immediately
- `markAsExpired(): bool`

**Scopes:**

- `active()` - Status = active AND not expired
- `expiringSoon(int $minutes = 10)` - Expires in next N minutes
- `forUser(int $userId)`
- `forServer(string $serverId)`

**Auto-generation:**

- `lease_id` → `lse_` + 40 random chars

**File:** `app/Models/CredentialLease.php`

### 1.4 Migration Execution ✅

```bash
✅ 2025_11_04_185610_create_organizations_table
✅ 2025_11_04_185633_create_organization_members_table
✅ 2025_11_04_185752_create_organization_invitations_table
✅ 2025_11_04_185815_add_organization_support_to_integration_accounts
✅ 2025_11_04_185900_create_credential_leases_table
```

All migrations executed successfully with proper foreign key constraints and indexes.

---

## ✅ Phase 2: Controllers & API (COMPLETED)

### 2.1 Controllers Created ✅

#### Architecture: Single-Action Invokable Controllers

**Namespace:** `App\Http\Controllers\Mcp\` (Proxy Manager → MCP Server)

- `McpProxyController` - Proxie les requêtes vers serveur Python MCP

**Namespace:** `App\Http\Controllers\Api\Mcp\` (API MCP Server → Manager)

- `GetAuthenticatedUserController` - GET /api/mcp/me
- `CreateCredentialLeaseController` - POST /api/mcp/credentials/lease
- `RenewCredentialLeaseController` - POST /api/mcp/credentials/lease/{id}/renew
- `RevokeCredentialLeaseController` - DELETE /api/mcp/credentials/lease/{id}
- `ShowCredentialLeaseController` - GET /api/mcp/credentials/lease/{id}
- `GetUserCredentialsController` - GET /api/mcp/users/{userId}/credentials

### 2.2 Service Layer ✅

**File:** `app/Services/CredentialResolutionService.php`

**Purpose:** Résolution de credentials avec priorité Personal > Organization

**Key Methods:**

```php
public function resolveCredential(int $userId, string $service): ?array
public function resolveMultipleCredentials(int $userId, array $services): array
public function getAvailableServices(int $userId): array
private function canAccessCredential(OrganizationMember $membership, IntegrationAccount $credential): bool
```

### 2.3 Middleware Created ✅

**File:** `app/Http/Middleware/ValidateMcpServerToken.php`

**Purpose:** Validate Bearer token (UserToken) pour authentification MCP Server

**Logic:**

1. Extract Authorization: Bearer {token} header
2. Look up UserToken where token = {token}
3. Check expiration
4. Attach user to request: `$request->setUserResolver()`
5. Log access in UserActivityLog

**Alias:** `mcp.token` (registered in `bootstrap/app.php`)

### 2.4 Routes Added ✅

**File:** `routes/api.php`

```php
// MCP Server API routes (MCP Server → Manager)
Route::prefix('mcp')->group(function () {
    Route::middleware(['mcp.token'])->group(function () {
        // User info
        Route::get('me', GetAuthenticatedUserController::class);

        // Credential Lease management
        Route::post('credentials/lease', CreateCredentialLeaseController::class);
        Route::get('credentials/lease/{leaseId}', ShowCredentialLeaseController::class);
        Route::post('credentials/lease/{leaseId}/renew', RenewCredentialLeaseController::class);
        Route::delete('credentials/lease/{leaseId}', RevokeCredentialLeaseController::class);

        // Convenience endpoint
        Route::get('users/{userId}/credentials', GetUserCredentialsController::class);
    });
});
```

**File:** `routes/web.php`

```php
// MCP Proxy routes (Manager Frontend → MCP Server)
Route::prefix('api/mcp')->group(function () {
    Route::post('auth/login', [McpProxyController::class, 'login']);
    Route::get('auth/me', [McpProxyController::class, 'me']);
    Route::get('todoist/tasks/today', [McpProxyController::class, 'getTodayTasks']);
    Route::get('todoist/tasks/upcoming', [McpProxyController::class, 'getUpcomingTasks']);
    Route::any('todoist/{path?}', [McpProxyController::class, 'todoistProxy']);
});
```

### 2.5 Audit Logging ✅

**Every credential operation logs to `UserActivityLog`:**

```php
UserActivityLog::create([
    'user_id' => $actingUser->id,
    'action' => 'lease_created|lease_renewed|lease_revoked',
    'entity_type' => 'CredentialLease',
    'entity_id' => $lease->id,
    'new_values' => [...],
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

**Actions logged:**

- `lease_created` ✅
- `lease_renewed` ✅
- `lease_revoked` ✅
- `mcp_auth_success` ✅
- `mcp_auth_failed` ✅
- `mcp_unauthorized_access` ✅

### 2.6 Documentation ✅

**Files créés:**

- `MCP_AUTHENTICATION_ARCHITECTURE.md` - Architecture complète avec diagrammes
- `PHASE_2_IMPLEMENTATION_COMPLETE.md` - Détails Phase 2

---

## ✅ Phase 3: Frontend UI (COMPLETED)

**Completed:** 2025-11-05

### Summary of Phase 3 Implementation ✅

**Phase 3.1 - Organization Management UI:**
- ✅ Organizations Index page with stats, filters, and search
- ✅ Create Organization form
- ✅ Organization Details page with tabs
- ✅ Edit Organization form
- ✅ Members, Credentials, and Invitations tabs
- ✅ Backend controllers and routes
- ✅ Spatie Laravel Permission integration

**Phase 3.2 - Credential Management Enhancement:**
- ✅ IntegrationFormDynamic component with service-specific fields
- ✅ Scope selection (Personal vs Organization)
- ✅ Sharing configuration UI (all_members, admins_only, specific users)
- ✅ Performance optimizations (debouncing, virtualization)
- ✅ IntegrationCard enhancements with scope badges
- ✅ Integration Manager Dashboard refactored with real logos

**Phase 3.3 - Active Leases Dashboard:**
- ✅ ActiveLeases page with real-time countdown timers
- ✅ Stats cards (Active, Expiring Soon, Total Services, Organizations)
- ✅ Advanced filters (Status, Organization, Service, Search)
- ✅ Lease details modal with complete information
- ✅ Revoke functionality with confirmation
- ✅ Backend controller and routes
- ✅ Factories for testing (CredentialLeaseFactory, OrganizationFactory)
- ✅ Added to sidebar navigation with Key icon

**Additional Files Created:**
- `resources/js/components/integrations/integration-icon.tsx` - Real brand logos using Simple Icons CDN
- `database/factories/CredentialLeaseFactory.php` - Comprehensive factory with states
- `database/factories/OrganizationFactory.php` - Organization factory
- `app/Http/Controllers/Settings/Security/ActiveLeasesController.php` - Lease management

---

### 3.1 Organization Management UI ✅

**Design Pattern:** S'inspirer de `/admin/users` (resources/js/pages/Admin/Users/)

**Architecture similaire:**

- Index.tsx - Liste avec filters, search, stats cards
- Create.tsx - Formulaire de création
- Edit.tsx - Formulaire d'édition
- Show.tsx - Détails + actions

**Pages à créer:**

#### `/settings/organizations` - Organizations List ✅

**Fichier:** `resources/js/pages/Settings/Organizations/Index.tsx` ✅

**Fonctionnalités:**

- Liste des organisations de l'utilisateur
- Stats cards (Total Orgs, Active Members, Shared Credentials)
- Search & filters (by status, role)
- Actions: Create, View, Leave
- Badge pour le rôle (Owner, Admin, Member, Guest)
- Utilise MonologueCard pour layout

**Inspiré de:** `resources/js/pages/Admin/Users/Index.tsx`

#### `/settings/organizations/create` - Create Organization ✅

**Fichier:** `resources/js/pages/Settings/Organizations/Create.tsx` ✅

**Form Fields:**

- Organization Name (required)
- Slug (auto-generated from name, editable)
- Billing Email
- Max Members (default: 5)

**Inspiré de:** `resources/js/pages/Admin/Users/Create.tsx`

#### `/settings/organizations/{id}` - Organization Details ✅

**Fichier:** `resources/js/pages/Settings/Organizations/Show.tsx` ✅

**Sections:**

- Overview (name, slug, owner, status, members count)
- Quick Stats (Active Members, Shared Credentials, Active Leases)
- Tabs:
  - Members
  - Credentials
  - Invitations
  - Settings

**Inspiré de:** `resources/js/pages/Admin/Users/Show.tsx`

#### `/settings/organizations/{id}/edit` - Edit Organization ✅

**Fichier:** `resources/js/pages/Settings/Organizations/Edit.tsx` ✅

**Form Fields:**

- Organization Name
- Billing Email
- Max Members
- Status (Owner/Admin only)

**Inspiré de:** `resources/js/pages/Admin/Users/Edit.tsx`

#### Organization Members Tab ✅

**Component:** `resources/js/components/organizations/MembersTab.tsx` ✅

**Fonctionnalités:**

- Liste des membres avec rôle
- Actions: Change Role, Remove (Admin+)
- Invite new member button
- Permissions display

#### Organization Credentials Tab ✅

**Component:** `resources/js/components/organizations/CredentialsTab.tsx` ✅

**Fonctionnalités:**

- Liste des credentials partagés
- Qui peut accéder (all_members, admins_only, specific users)
- Actions: Edit sharing, Delete
- Add organization credential

#### Organization Invitations Tab ✅

**Component:** `resources/js/components/organizations/InvitationsTab.tsx` ✅

**Fonctionnalités:**

- Pending invitations
- Resend invitation
- Revoke invitation
- Expiration status

### 3.2 Credential Management Enhancement ✅

**Page existante à modifier:** `/integrations` (resources/js/pages/integrations.tsx) ✅

**Composant à modifier:** `resources/js/components/integrations/integration-list.tsx` ✅

#### Améliorations requises: ✅

**1. Performance Optimization** ✅

- Lazy loading pour grandes listes ✅
- Virtualization si > 20 integrations ✅
- Debounce sur search/filters ✅

**2. Scope Selection lors de l'ajout de credential** ✅

**Nouveau flow dans IntegrationForm:** ✅

```tsx
// Step 1: Select Scope
[ ] Personal (Only me)
[ ] Organization (Shared with team)

// If Personal selected:
→ Show standard credential form

// If Organization selected:
→ Step 2: Select Organization (dropdown)
→ Step 3: Configure Sharing
   [ ] All members can access
   [ ] Admins only
   [ ] Specific users (multi-select avec autocomplete)
```

**3. Credential Fields par Service Type** ✅

Basé sur les .env files analysés: ✅

```typescript
interface CredentialFields {
  notion: {
    access_token: string;        // NOTION_API_TOKEN
    database_id?: string;         // NOTION_DATABASE_ID (optional)
  };

  jira: {
    url: string;                  // JIRA_URL
    email: string;                // JIRA_EMAIL
    api_token: string;            // JIRA_API_TOKEN
    cloud: boolean;               // JIRA_CLOUD (default: true)
  };

  confluence: {
    url: string;                  // CONFLUENCE_URL
    email: string;                // CONFLUENCE_EMAIL
    api_token: string;            // CONFLUENCE_API_TOKEN
  };

  todoist: {
    api_token: string;            // TODOIST_API_TOKEN
  };

  sentry: {
    auth_token: string;           // SENTRY_AUTH_TOKEN
    org_slug: string;             // SENTRY_ORG_SLUG
    base_url?: string;            // SENTRY_BASE_URL (default: https://sentry.io/api/0)
    project?: string;             // SENTRY_PROJECT (optional)
  };

  anthropic: {
    api_key: string;              // ANTHROPIC_API_KEY
    default_model?: string;       // CLAUDE_DEFAULT_MODEL
    max_tokens?: number;          // CLAUDE_MAX_TOKENS
    temperature?: number;         // CLAUDE_DEFAULT_TEMPERATURE
  };

  google: {
    client_id: string;            // GOOGLE_CLIENT_ID
    client_secret: string;        // GOOGLE_CLIENT_SECRET
    redirect_uri: string;         // GOOGLE_REDIRECT_URI
  };

  github: {
    client_id: string;            // GITHUB_CLIENT_ID
    client_secret: string;        // GITHUB_CLIENT_SECRET
    redirect_uri: string;         // GITHUB_REDIRECT_URI
  };

  gitlab: {
    client_id: string;            // GITLAB_CLIENT_ID
    client_secret: string;        // GITLAB_CLIENT_SECRET
    redirect_uri: string;         // GITLAB_REDIRECT_URI
  };
}
```

**4. Nouveau Component: IntegrationFormDynamic** ✅

**Fichier:** `resources/js/components/integrations/integration-form-dynamic.tsx` ✅

```tsx
interface IntegrationFormDynamicProps {
  type: IntegrationType;
  scope: 'personal' | 'organization';
  organizationId?: number;
  organizations?: Organization[];
  onSubmit: (data: IntegrationFormData) => void;
}

// Génère dynamiquement les champs selon le type
// Gère la validation spécifique par type
// Support pour scope selection
```

**5. IntegrationCard Enhancement** ✅

Ajouter indicateur de scope: ✅

- Badge "Personal" (cyan)
- Badge "Organization: {name}" (blue)
- Icon pour shared_with status

### 3.3 Active Leases Dashboard ✅

**Page:** `/settings/security/active-leases` ✅

**Fichier:** `resources/js/pages/Settings/Security/ActiveLeases.tsx` ✅

**Layout inspiré de:** Admin Users Index avec MonologueCard

**Sections:**

#### Stats Cards ✅

- Active Leases ✅
- Total Services ✅
- Expiring Soon (< 10 min) ✅
- Organizations with Leases ✅

#### Table Columns ✅

- Lease ID (monospace) ✅
- Server ID ✅
- Services (badges) ✅
- Status (active/expiring/expired) ✅
- Created At ✅
- Expires At (with countdown) ✅
- Renewal Count / Max ✅
- Actions (View, Revoke) ✅

#### Filters ✅

- Status: All / Active / Expiring / Expired ✅
- Organization: All / Personal / {Org Name} ✅
- Service: All / Notion / Jira / etc. ✅

#### Actions ✅

- Revoke Lease (confirmation dialog) ✅
- View Details (modal with full lease info) ✅

---

## ⏳ Phase 4: MCP Server Python Client (PENDING)

### 4.1 Credential Provider Implementation

**File:** `mcp-server/app/services/credential_provider.py`

```python
class McpManagerCredentialProvider(CredentialProvider):
    def __init__(self, user_id: int, mcp_token: str, mcp_api_url: str):
        self.user_id = user_id
        self.mcp_token = mcp_token
        self.mcp_api_url = mcp_api_url
        self.cache = {}
        self.lease_id = None

    async def bootstrap(self):
        """Called once on server startup"""
        # 1. Authenticate with Manager
        auth_response = await self._http_get(
            f"{self.mcp_api_url}/api/mcp/me",
            headers={"Authorization": f"Bearer {self.mcp_token}"}
        )

        user_data = auth_response.json()
        self.user_id = user_data['user_id']

        # 2. Request lease for all needed services
        lease_response = await self._http_post(
            f"{self.mcp_api_url}/api/mcp/credentials/lease",
            headers={"Authorization": f"Bearer {self.mcp_token}"},
            json={
                "services": ["notion", "jira", "todoist", "sentry", "anthropic"],
                "ttl": 3600,
                "server_id": "mcp-server-1",
                "client_info": self._get_client_info()
            }
        )

        lease = lease_response.json()
        self.lease_id = lease['lease_id']
        self.cache = lease['credentials']
        self.expires_at = parse_datetime(lease['expires_at'])

        # 3. Schedule auto-refresh
        asyncio.create_task(self._auto_refresh_loop())

    async def get_credentials(self, service: str) -> Optional[Dict]:
        """Get credentials for a service"""
        if service not in self.cache:
            return None

        # Check if expiring soon
        if self._is_expiring_soon():
            await self._refresh_lease()

        return self.cache.get(service)

    async def _auto_refresh_loop(self):
        """Background task to refresh lease every 50 minutes"""
        while True:
            await asyncio.sleep(3000)  # 50 minutes

            try:
                await self._refresh_lease()
            except Exception as e:
                logger.error(f"Failed to refresh lease: {e}")

    async def _refresh_lease(self):
        """Renew the lease"""
        response = await self._http_post(
            f"{self.mcp_api_url}/api/mcp/credentials/lease/{self.lease_id}/renew",
            headers={"Authorization": f"Bearer {self.mcp_token}"}
        )

        if response.status_code == 403:
            # User revoked
            raise CredentialRevokedError("Lease renewal refused - user access revoked")

        renewed = response.json()
        self.expires_at = parse_datetime(renewed['expires_at'])
        logger.info(f"Lease renewed: {self.lease_id}, expires {self.expires_at}")

    def _is_expiring_soon(self, threshold_minutes: int = 10) -> bool:
        return (self.expires_at - datetime.now()).total_seconds() < (threshold_minutes * 60)
```

### 4.2 Service Factory Update

**File:** `mcp-server/app/services/service_factory.py`

```python
class ServiceFactory:
    def __init__(self, credential_provider: CredentialProvider):
        self.provider = credential_provider

    async def create_notion_service(self) -> NotionService:
        creds = await self.provider.get_credentials('notion')
        if not creds:
            raise ServiceUnavailableError("Notion credentials not configured")
        return NotionService(creds['access_token'])

    async def create_jira_service(self) -> JiraService:
        creds = await self.provider.get_credentials('jira')
        if not creds:
            raise ServiceUnavailableError("JIRA credentials not configured")
        return JiraService(
            url=creds['url'],
            email=creds['email'],
            token=creds['api_token']
        )
```

### 4.3 Main Application Bootstrap

**File:** `mcp-server/app/main.py`

```python
async def startup():
    # Initialize credential provider
    credential_provider = McpManagerCredentialProvider(
        user_id=None,  # Will be set during auth
        mcp_token=os.getenv("MCP_TOKEN"),
        mcp_api_url=os.getenv("MCP_API_URL")
    )

    # Bootstrap (authenticate + get initial lease)
    await credential_provider.bootstrap()

    # Initialize service factory
    service_factory = ServiceFactory(credential_provider)

    # Store in app state
    app.state.credential_provider = credential_provider
    app.state.service_factory = service_factory

# Use in routes
@router.get("/notion/databases")
async def list_notion_databases(request: Request):
    factory: ServiceFactory = request.app.state.service_factory
    notion_service = await factory.create_notion_service()
    return await notion_service.list_databases()
```

---

## 📊 Summary Statistics


| Component         | Status         | Files     | Lines of Code      |
| ----------------- | -------------- | --------- | ------------------ |
| **Migrations**    | ✅ Complete    | 5 files   | ~300 lines         |
| **Enums**         | ✅ Complete    | 4 files   | ~150 lines         |
| **Models**        | ✅ Complete    | 4 files   | ~450 lines         |
| **Controllers**   | ✅ Complete    | 7 files   | ~800 lines         |
| **Services**      | ✅ Complete    | 1 file    | ~200 lines         |
| **Middleware**    | ✅ Complete    | 1 file    | ~80 lines          |
| **Routes**        | ✅ Complete    | 2 files   | ~50 lines          |
| **Documentation** | ✅ Complete    | 2 files   | ~1500 lines        |
| **Frontend UI**   | ⏳ In Progress | ~15 files | ~2000 lines (est.) |
| **Python Client** | ⏳ Pending     | 3 files   | ~400 lines (est.)  |
| **Tests**         | ⏳ Pending     | ~10 files | ~800 lines (est.)  |

**Total Completed:** ~3580 lines of production-ready code

---

## 🚀 Next Steps

### Phase 3 - Frontend UI (CURRENT)

**Week 1: Organization Management**

1. ✅ Backend complet (models, controllers, API)
2. ⏳ Create Organization CRUD pages (Index, Create, Edit, Show)
3. ⏳ Members management tab with invite system
4. ⏳ Organization credentials tab
5. ⏳ Invitations tab

**Week 2: Integration Enhancement**
6. ⏳ Refactor IntegrationForm pour dynamic fields par service
7. ⏳ Add scope selection (Personal vs Organization)
8. ⏳ Organization selector for shared credentials
9. ⏳ Sharing configuration UI (all_members, admins_only, users)
10. ⏳ Performance optimization (lazy loading, virtualization)

**Week 3: Active Leases Dashboard**
11. ⏳ Create Active Leases page
12. ⏳ Real-time countdown for expiration
13. ⏳ Revoke lease functionality
14. ⏳ Filters and search

### Phase 4 - Python Client (AFTER PHASE 3)

**Week 4: MCP Server Integration**
15. ⏳ Implement CredentialProvider in Python
16. ⏳ Update ServiceFactory to use provider
17. ⏳ Bootstrap application with auto-refresh
18. ⏳ Remove hardcoded credentials from .env
19. ⏳ End-to-end testing

### Testing & Production

**Week 5-6:**
20. ⏳ Unit tests for all models
21. ⏳ Feature tests for API endpoints
22. ⏳ Integration tests for full flow
23. ⏳ Security audit
24. ⏳ Performance testing
25. ⏳ Documentation finalization

---

## 📝 Configuration Required

### Environment Variables (MCP Manager)

```env
# Existing
APP_KEY=base64:...
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=mcp_manager

# New (optional)
LEASE_DEFAULT_TTL=3600           # 1 hour
LEASE_MAX_RENEWALS=24            # 24 hours max
LEASE_CLEANUP_FREQUENCY=3600     # Run cleanup every hour
```

### Environment Variables (MCP Server) - PHASE 4

```env
# TO REMOVE (hardcoded credentials):
NOTION_API_TOKEN=...
JIRA_URL=...
JIRA_API_TOKEN=...
CONFLUENCE_API_TOKEN=...
TODOIST_API_TOKEN=...
SENTRY_AUTH_TOKEN=...
ANTHROPIC_API_KEY=...

# TO ADD (credential lease system):
MCP_API_URL=http://localhost:3978
MCP_TOKEN={user_token_from_manager}
```

---

## 🔒 Security Checklist

- [X]  All credentials encrypted at rest (APP_KEY)
- [X]  Foreign key constraints enforce referential integrity
- [X]  Unique constraints prevent duplicates
- [X]  Enum types enforce valid values
- [X]  API endpoints require authentication (middleware)
- [X]  Audit logging for all credential operations
- [X]  Token validation with expiration
- [X]  Organization permission checks
- [X]  User isolation (can't access other users' leases)
- [ ]  Rate limiting on auth endpoints (TODO)
- [ ]  HTTPS/TLS for production API communication (TODO)
- [ ]  Credential rotation on member removal (TODO - Phase 3)

---

## 📞 Support & Questions

For implementation questions or issues:

1. Check this document first
2. Review `MCP_AUTHENTICATION_ARCHITECTURE.md` for architecture
3. Review `PHASE_2_IMPLEMENTATION_COMPLETE.md` for API details
4. Check Laravel logs: `storage/logs/laravel.log`
5. Run tests: `php artisan test --filter Lease`

---

**Document Version:** 2.0
**Last Updated:** 2025-11-04
**Author:** Claude Code Implementation
