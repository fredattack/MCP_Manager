# Credential Lease System - Implementation Guide

**Date:** 2025-11-04
**Status:** Phase 1 Complete (Database & Models) ✅
**Next:** Phase 2 (Controllers & API) ⏳

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

### 1.1 Migrations Created

#### `organizations` Table
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

#### `organization_members` Table
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

#### `organization_invitations` Table
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

#### `integration_accounts` Modification
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

#### `credential_leases` Table
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

### 1.2 Enums Created

#### `OrganizationRole` Enum
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

#### `CredentialScope` Enum
```php
Personal      → Only accessible by owner
Organization  → Shared within organization
```

**File:** `app/Enums/CredentialScope.php`

#### `LeaseStatus` Enum
```php
Active   → Currently valid
Expired  → TTL exceeded
Revoked  → Manually revoked

Methods:
- isActive(): bool
- canRenew(): bool
```

**File:** `app/Enums/LeaseStatus.php`

#### `OrganizationStatus` Enum
```php
Active     → Operational
Suspended  → Temporarily disabled
Deleted    → Soft-deleted
```

**File:** `app/Enums/OrganizationStatus.php`

### 1.3 Models Created

#### `Organization` Model
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

#### `OrganizationMember` Model
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

#### `OrganizationInvitation` Model
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

#### `CredentialLease` Model
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

### 1.4 Migration Execution

```bash
✅ 2025_11_04_185610_create_organizations_table
✅ 2025_11_04_185633_create_organization_members_table
✅ 2025_11_04_185752_create_organization_invitations_table
✅ 2025_11_04_185815_add_organization_support_to_integration_accounts
✅ 2025_11_04_185900_create_credential_leases_table
```

All migrations executed successfully with proper foreign key constraints and indexes.

---

## ⏳ Phase 2: Controllers & API (PENDING)

### 2.1 Controllers to Create

#### `McpAuthController`
**Purpose:** Authenticate MCP Server requests using `MCP_TOKEN`

**Endpoints:**
```php
POST /api/mcp/auth
Body: {
    "username": "admin@agentops.be",
    "token": "OTY3Y2ViNm..."
}
Response: {
    "user_id": 42,
    "permissions": ["read:credentials", "write:own"],
    "organizations": [
        {"id": 1, "name": "Acme Corp", "role": "member"}
    ]
}
```

**File:** `app/Http/Controllers/Api/McpAuthController.php` (TO CREATE)

#### `McpCredentialLeaseController`
**Purpose:** Manage credential leases lifecycle

**Endpoints:**
```php
POST /api/mcp/credentials/lease
Body: {
    "user_id": 42,
    "services": ["notion", "jira", "todoist"],
    "ttl": 3600,
    "server_id": "mcp-server-1",
    "client_info": "Claude Code 1.0 / macOS"
}
Response: {
    "lease_id": "lse_abc123...",
    "credentials": {
        "notion": {"access_token": "...", "meta": {...}},
        "jira": {"url": "...", "token": "..."},
        "todoist": {"access_token": "..."}
    },
    "credential_sources": {
        "notion": {"scope": "personal"},
        "jira": {"scope": "organization", "org_name": "Acme Corp"}
    },
    "expires_at": "2025-11-04T15:00:00Z",
    "renewable": true
}

POST /api/mcp/credentials/lease/{lease_id}/renew
Response: {
    "lease_id": "lse_abc123...",
    "expires_at": "2025-11-04T16:00:00Z", // Extended
    "renewal_count": 2
}

DELETE /api/mcp/credentials/lease/{lease_id}
Body: {
    "reason": "User manually disconnected"
}
Response: {
    "success": true,
    "revoked_at": "2025-11-04T14:30:00Z"
}

GET /api/mcp/credentials/lease/{lease_id}
Response: {
    "lease_id": "lse_abc123...",
    "user_id": 42,
    "status": "active",
    "expires_at": "2025-11-04T15:00:00Z",
    "services": ["notion", "jira"],
    "renewal_count": 1
}
```

**File:** `app/Http/Controllers/Api/McpCredentialLeaseController.php` (TO CREATE)

### 2.2 Middleware to Create

#### `ValidateMcpServerToken`
**Purpose:** Validate that incoming request has valid MCP_TOKEN

**Logic:**
```php
1. Extract Authorization: Bearer {token} header
2. Look up UserToken where token = {token}
3. Check user is active
4. Attach user to request: $request->user
5. Log access in UserActivityLog
```

**File:** `app/Http/Middleware/ValidateMcpServerToken.php` (TO CREATE)

### 2.3 Routes to Add

**File:** `routes/api.php`

```php
Route::prefix('mcp')->group(function () {
    // Authentication (no middleware)
    Route::post('/auth', [McpAuthController::class, 'authenticate']);

    // Protected endpoints (require MCP server token)
    Route::middleware('validate.mcp.token')->group(function () {
        // Lease management
        Route::post('/credentials/lease', [McpCredentialLeaseController::class, 'create']);
        Route::get('/credentials/lease/{leaseId}', [McpCredentialLeaseController::class, 'show']);
        Route::post('/credentials/lease/{leaseId}/renew', [McpCredentialLeaseController::class, 'renew']);
        Route::delete('/credentials/lease/{leaseId}', [McpCredentialLeaseController::class, 'revoke']);

        // Convenience endpoints
        Route::get('/users/{userId}/credentials', [McpCredentialLeaseController::class, 'getUserCredentials']);
        Route::get('/users/{userId}/credentials/{service}', [McpCredentialLeaseController::class, 'getUserServiceCredential']);
    });
});
```

### 2.4 Credential Resolution Logic

**Core Function:** `resolveCredential(int $userId, string $service): ?array`

```php
// Priority: Personal > Organization

// 1. Check for personal credential
$personal = IntegrationAccount::where('user_id', $userId)
    ->where('type', $service)
    ->where('scope', CredentialScope::Personal)
    ->where('status', IntegrationStatus::Active)
    ->first();

if ($personal) {
    return $personal; // Personal always overrides
}

// 2. Check organizations the user belongs to
$memberships = OrganizationMember::where('user_id', $userId)
    ->with('organization')
    ->get();

foreach ($memberships as $membership) {
    $orgCredential = IntegrationAccount::where('organization_id', $membership->organization_id)
        ->where('type', $service)
        ->where('scope', CredentialScope::Organization)
        ->where('status', IntegrationStatus::Active)
        ->first();

    if ($orgCredential && $this->canAccessCredential($membership, $orgCredential)) {
        return $orgCredential;
    }
}

return null;
```

**Permission Check:**
```php
private function canAccessCredential(OrganizationMember $membership, IntegrationAccount $credential): bool
{
    $sharedWith = $credential->shared_with ?? [];

    // All members can access
    if (in_array('all_members', $sharedWith)) {
        return true;
    }

    // Admin-only access
    if (in_array('admins_only', $sharedWith)) {
        return $membership->isAdmin();
    }

    // Specific user access
    if (in_array("user:{$membership->user_id}", $sharedWith)) {
        return true;
    }

    // Default: deny
    return false;
}
```

### 2.5 Audit Logging

**Every credential operation must log:**

```php
UserActivityLog::create([
    'user_id' => $actingUser->id,
    'target_user_id' => $targetUser->id ?? null,
    'action' => 'lease_created',
    'entity_type' => 'CredentialLease',
    'entity_id' => $lease->id,
    'old_values' => null,
    'new_values' => [
        'lease_id' => $lease->lease_id,
        'services' => $lease->services,
        'credential_sources' => $credentialSources,
        'server_id' => $lease->server_id,
        'expires_at' => $lease->expires_at,
    ],
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

**Actions to log:**
- `lease_created`
- `lease_renewed`
- `lease_revoked`
- `lease_expired`
- `credential_accessed`
- `member_added_to_org`
- `member_removed_from_org`
- `org_credential_created`
- `org_credential_updated`

---

## ⏳ Phase 3: Frontend UI (PENDING)

### 3.1 Organization Management UI

**Pages to create:**
- `/settings/organizations` - List user's organizations
- `/settings/organizations/create` - Create new organization
- `/settings/organizations/{id}` - View organization details
- `/settings/organizations/{id}/members` - Manage members
- `/settings/organizations/{id}/credentials` - Manage org credentials
- `/settings/organizations/{id}/invitations` - Pending invitations

### 3.2 Credential Management UI Enhancement

**Update:** `/settings/integrations`

Add toggle for credential scope:
- [ ] Personal (only me)
- [ ] Organization (shared)

If Organization selected:
- Dropdown to select organization
- Multi-select for "Shared with":
  - [ ] All members
  - [ ] Admins only
  - [ ] Specific users (autocomplete)

### 3.3 Active Leases Dashboard

**Page:** `/settings/security/active-leases`

Table showing:
- Lease ID
- Server ID
- Services
- Status
- Expires At
- Actions (Revoke)

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
        # 1. Authenticate
        auth_response = await self._http_post(
            f"{self.mcp_api_url}/api/mcp/auth",
            json={
                "username": os.getenv("MCP_USERNAME"),
                "token": self.mcp_token
            }
        )

        user_data = auth_response.json()
        self.user_id = user_data['user_id']

        # 2. Request lease for all needed services
        lease_response = await self._http_post(
            f"{self.mcp_api_url}/api/mcp/credentials/lease",
            headers={"Authorization": f"Bearer {self.mcp_token}"},
            json={
                "user_id": self.user_id,
                "services": ["notion", "jira", "todoist", "sentry", "openai"],
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
            token=creds['token']
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

| Component | Status | Files | Lines of Code |
|-----------|--------|-------|---------------|
| **Migrations** | ✅ Complete | 5 files | ~300 lines |
| **Enums** | ✅ Complete | 4 files | ~150 lines |
| **Models** | ✅ Complete | 4 files | ~450 lines |
| **Controllers** | ⏳ Pending | 2 files | ~600 lines (est.) |
| **Middleware** | ⏳ Pending | 1 file | ~80 lines (est.) |
| **Routes** | ⏳ Pending | 1 file | ~30 lines |
| **Frontend UI** | ⏳ Pending | 6 pages | ~1500 lines (est.) |
| **Python Client** | ⏳ Pending | 3 files | ~400 lines (est.) |
| **Tests** | ⏳ Pending | ~10 files | ~800 lines (est.) |

---

## 🚀 Next Steps

### Immediate (This Week)
1. Create `McpAuthController` with `/api/mcp/auth` endpoint
2. Create `McpCredentialLeaseController` with CRUD endpoints
3. Create `ValidateMcpServerToken` middleware
4. Add API routes to `routes/api.php`
5. Test with Postman/curl

### Short-term (Next 2 Weeks)
6. Update `IntegrationAccount` model to use new `scope` and `organization_id`
7. Implement credential resolution logic
8. Add audit logging to all credential operations
9. Create frontend UI for organization management
10. Update integrations page to support organization credentials

### Medium-term (Weeks 3-4)
11. Implement Python credential provider in MCP Server
12. Update all MCP Server services to use ServiceFactory
13. Test end-to-end flow: Claude Code → MCP Server → MCP Manager
14. Add automatic lease cleanup job (expired/revoked leases)
15. Add lease renewal monitoring and alerting

### Testing Strategy
16. Unit tests for models and enum methods
17. Feature tests for API endpoints
18. Integration tests for full lease lifecycle
19. Security tests for permission checks
20. Load tests for lease renewal under high concurrency

---

## 📝 Configuration Required

### Environment Variables (MCP Manager)
```env
# Existing
APP_KEY=base64:...
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=mcp_manager
DB_USERNAME=...
DB_PASSWORD=...

# New (optional)
LEASE_DEFAULT_TTL=3600           # 1 hour
LEASE_MAX_RENEWALS=24            # 24 hours max
LEASE_CLEANUP_FREQUENCY=3600     # Run cleanup every hour
```

### Environment Variables (MCP Server)
```env
# Existing
NOTION_API_TOKEN=...  # Will be removed
JIRA_URL=...          # Will be removed
JIRA_API_TOKEN=...    # Will be removed

# New
MCP_API_URL=https://manager.agentops.be
MCP_USERNAME=admin@agentops.be
MCP_TOKEN=OTY3Y2ViNm...
```

### Claude Code Configuration
```json
{
  "agentops": {
    "command": "python",
    "args": ["-u", "/path/to/mcp_remote_client.py"],
    "env": {
      "MCP_API_URL": "https://mcp.agentops.be",
      "MCP_USERNAME": "admin@agentops.be",
      "MCP_TOKEN": "OTY3Y2ViNm..."
    }
  }
}
```

---

## 🔒 Security Checklist

- [x] All credentials encrypted at rest (APP_KEY)
- [x] Foreign key constraints enforce referential integrity
- [x] Unique constraints prevent duplicates
- [x] Enum types enforce valid values
- [ ] API endpoints require authentication (middleware)
- [ ] Audit logging for all credential operations
- [ ] Rate limiting on auth endpoints
- [ ] Token validation with expiration
- [ ] Organization permission checks
- [ ] User isolation (can't access other users' leases)
- [ ] HTTPS/TLS for all API communication
- [ ] Credential rotation on member removal (optional)

---

## 📞 Support & Questions

For implementation questions or issues:
1. Check this document first
2. Review the models and migrations
3. Check Laravel logs: `storage/logs/laravel.log`
4. Run tests: `php artisan test --filter Lease`

---

**Document Version:** 1.0
**Last Updated:** 2025-11-04
**Author:** Claude Code Implementation
