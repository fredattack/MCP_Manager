# MCP Manager - Credential Management System Analysis

## Executive Summary

The MCP Manager application has a **well-structured but incomplete** credential management system with two parallel approaches:

1. **IntegrationAccount Model** - Generic service integrations (Notion, Gmail, Todoist, JIRA, OpenAI, Sentry, Calendar)
2. **GitConnection Model** - Git provider OAuth connections (GitHub, GitLab)
3. **McpIntegration Model** - MCP server integrations with external services
4. **McpServer Model** - MCP server configuration and key management

There's good foundational work but several areas need enhancement for security, credential validation, and error handling.

---

## 1. Database & Models Architecture

### 1.1 IntegrationAccount Model

**Location:** `/Users/fred/PhpstormProjects/mcp_manager/app/Models/IntegrationAccount.php`

**Table Schema:**
```
Table: integration_accounts
- id (bigint, PK)
- user_id (bigint, FK -> users)
- type (varchar) - Enum: notion, gmail, calendar, openai, todoist, jira, sentry
- access_token (text) - ENCRYPTED automatically via cast
- meta (json) - Flexible metadata storage
- status (varchar) - active, inactive
- created_at, updated_at
```

**Key Features:**
- Access token is automatically encrypted using Laravel's encryption (`'access_token' => 'encrypted'`)
- Type is cast to `IntegrationType` enum
- Status is cast to `IntegrationStatus` enum
- Meta is JSON (unencrypted, stores public metadata only)
- Single active integration per type per user (enforced in controller)

**Issue:** Access tokens are encrypted in-place in the database, not separately. This is Laravel's default behavior and is secure.

### 1.2 GitConnection Model

**Location:** `/Users/fred/PhpstormProjects/mcp_manager/app/Models/GitConnection.php`

**Table Schema:**
```
Table: git_connections
- id (bigint, PK)
- user_id (bigint, FK)
- provider (varchar) - github, gitlab
- external_user_id (varchar) - GitHub username or GitLab user ID
- scopes (json) - OAuth scopes granted
- access_token_enc (text) - Manually encrypted
- refresh_token_enc (text, nullable) - Manually encrypted
- expires_at (timestamp, nullable)
- status (varchar) - active, inactive, error, expired
- meta (json) - User profile data (username, email, avatar_url)
- created_at, updated_at
```

**Key Features:**
- Manual encryption/decryption via `getAccessToken()` and `setAccessToken()` methods
- Supports refresh tokens for OAuth token refresh flow
- Token expiration tracking with 10-minute warning threshold
- Unique constraint on (user_id, provider, external_user_id) - allows multiple accounts per provider
- Status includes error and expired states
- Relationships to user and repositories

**Methods:**
```php
isTokenExpired(): bool
getAccessToken(): string
setAccessToken(string): void
getRefreshToken(): ?string
setRefreshToken(?string): void
scopeActive(Builder): Builder
scopeForProvider(Builder, GitProvider): Builder
```

### 1.3 McpIntegration Model

**Location:** `/Users/fred/PhpstormProjects/mcp_manager/app/Models/McpIntegration.php`

**Table Schema:**
```
Table: mcp_integrations
- id (bigint, PK)
- user_id (bigint, FK)
- mcp_server_id (bigint, FK)
- service_name (varchar)
- enabled (boolean)
- status (varchar) - active, inactive, error, connecting
- config (json) - Integration-specific configuration
- last_sync_at (timestamp, nullable)
- error_message (text, nullable)
- credentials_valid (boolean)
- created_at, updated_at
```

**Methods:**
- `isActive(): bool`
- `hasError(): bool`
- `getStatusDetails(): array`
- `markAsSynced(): void`
- `markAsFailed(string $error): void`

### 1.4 McpServer Model

**Location:** `/Users/fred/PhpstormProjects/mcp_manager/app/Models/McpServer.php`

**Table Schema:**
```
Table: mcp_servers
- id (bigint, PK)
- user_id (bigint, FK)
- name (varchar)
- url (varchar)
- public_key (text)
- private_key (text)
- server_public_key (text)
- ssl_certificate (text, nullable)
- config (json)
- status (varchar) - active, inactive, error
- session_token (text, nullable)
- error_message (text, nullable)
- created_at, updated_at
```

**Issues:**
- Private keys are stored unencrypted in the database (CRITICAL SECURITY ISSUE)
- No key rotation mechanism

### 1.5 Other Related Models

- **UserToken** - API tokens with scopes, expiration, and usage tracking
- **UserActivityLog** - Audit trail for credential operations
- **GitRepository** - Git repos synced from connections
- **GitClone** - Repository clone operations

---

## 2. API Endpoints & Controllers

### 2.1 Integration Accounts (Generic Services)

**Location:** `/Users/fred/PhpstormProjects/mcp_manager/app/Http/Controllers/IntegrationsController.php`

**Routes:**
```
GET    /api/integrations                    - List user's integrations
POST   /api/integrations                    - Create new integration
PUT    /api/integrations/{id}               - Update integration
DELETE /api/integrations/{id}               - Delete integration
```

**Controller Features:**
- Authentication checks on all endpoints
- User isolation (can't access others' integrations)
- Duplicate active integration prevention (one active per type)
- Basic validation (enum type, string token, optional meta)

**Limitations:**
- No credential validation endpoint
- No token refresh mechanism
- No error state handling beyond 422 response
- No audit logging
- No rate limiting

### 2.2 Git Connections (OAuth)

**Routes:**
```
POST   /api/git/{provider}/oauth/start      - Initiate OAuth flow
GET    /api/git/{provider}/oauth/callback   - OAuth callback handler
DELETE /api/git/{provider}/disconnect       - Disconnect account
GET    /api/git/{provider}/repos            - List repositories
POST   /api/git/{provider}/repos/sync       - Sync repositories
```

**Controllers:**
- `GitConnectionsController` - Display connections page
- `GitOAuthController` - Handle OAuth flow (not shown, but referenced in routes)
- `GitRepositoryController` - Repo management

**Features:**
- OAuth flow with state validation
- Token expiration tracking
- Multiple accounts per provider support
- Refresh token support (GitLab/GitHub)
- Automatic token refresh mechanism

**Limitations:**
- Error handling in UI is fragile
- No rate limit handling
- Limited error messages

---

## 3. Enums System

### 3.1 IntegrationType

**Supported Types:**
- `NOTION` - Notion workspace
- `GMAIL` - Gmail/Google
- `CALENDAR` - Google Calendar
- `OPENAI` - OpenAI services
- `TODOIST` - Todoist
- `JIRA` - Atlassian JIRA
- `SENTRY` - Sentry error monitoring

**Methods:**
- `displayName(): string`
- `description(): string`

### 3.2 IntegrationStatus
- `ACTIVE`
- `INACTIVE`

**Methods:**
- `displayName(): string`
- `isActive(): bool`

### 3.3 GitConnectionStatus
- `ACTIVE`
- `INACTIVE`
- `ERROR` - Connection failed
- `EXPIRED` - Token expired
- `EXPIRED` - Token needs refresh

**Methods:**
- `isActive(): bool`
- `requiresReauth(): bool`

### 3.4 GitProvider
- `GITHUB`
- `GITLAB`

**Methods:**
- `displayName(): string`
- `description(): string`
- `getAuthUrl(): string`
- `getTokenUrl(): string`
- `getApiUrl(): string`
- `getDefaultScopes(): array`

---

## 4. Frontend Components & Pages

### 4.1 Pages

#### Integrations Page
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/pages/integrations.tsx`

Features:
- Uses `IntegrationList` component
- Header with brand styling (Monologue design system)
- Browse integrations button (not yet implemented)

#### Git Connections Page
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/pages/git/connections.tsx`

Features:
- Two-column layout (GitHub | GitLab)
- OAuth flow with success/error handling
- Status display (active, expired, error)
- Token expiration warning (30-minute threshold)
- Scopes display
- Reconnect/renew functionality
- Avatar and user info display
- Disconnect confirmation dialog

### 4.2 Components

#### IntegrationList
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/components/integrations/integration-list.tsx`

Features:
- Fetches integrations on mount
- Modal dialog for adding new integrations
- Type selector dropdown
- Creates, updates, deletes integrations
- Empty state
- Loading/error states

#### IntegrationForm
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/components/integrations/integration-form.tsx`

Features:
- Password input for access token
- Type-specific help text
- Loading state on submit
- Error display
- Form validation

#### IntegrationCard
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/components/integrations/integration-card.tsx`

Features:
- Type display with icon
- Status badge
- Edit dialog for token update
- Delete confirmation dialog
- Activate/deactivate toggle
- Type-specific configuration (Todoist has special route)

#### IntegrationCardEnhanced
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/components/integrations/integration-card-enhanced.tsx`

(Not shown in detail but referenced in integration-list)

### 4.3 Custom Hooks

#### useIntegrations
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/hooks/use-integrations.ts`

Features:
- CRUD operations for integrations
- Loading/error state management
- Authorization header with API token
- Success/error callbacks
- Optimistic state updates on delete
- Proper error handling

#### useApiToken
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/hooks/use-api-token.ts`

Features:
- Retrieves user's API token for authenticated requests

### 4.4 TypeScript Types

**Location:** `/Users/fred/PhpstormProjects/mcp_manager/resources/js/types/integrations.ts`

```typescript
interface IntegrationAccount {
    id: number;
    type: string;
    access_token: string;
    meta?: Record<string, unknown> | null;
    status: string;
    created_at: string;
    updated_at: string;
}

enum IntegrationType { NOTION, GMAIL, CALENDAR, OPENAI, TODOIST, JIRA, SENTRY }
enum IntegrationStatus { ACTIVE, INACTIVE }

interface IntegrationTypeInfo {
    value: string;
    displayName: string;
    description: string;
    icon: string;
}
```

---

## 5. Services & Business Logic

### 5.1 Encryption Services

#### CryptoService
**Location:** `/Users/fred/PhpstormProjects/mcp_manager/app/Services/CryptoService.php`

**Capabilities:**
- RSA key pair generation (2048-bit default)
- RSA encryption/decryption with chunking
- Digital signing with SHA256
- Symmetric AES-256-GCM encryption
- Password hashing (bcrypt)
- Token generation
- SSL certificate validation
- Hostname verification with wildcard support

**Issues:**
- Used for RSA key management but not for all credential encryption
- IntegrationAccount uses Laravel's native encryption instead
- GitConnection uses manual encryption with Crypt::encryptString()

### 5.2 Other Services

- **McpAuthService** - MCP authentication
- **McpProxyService** - Proxy requests to MCP servers
- **NotionService** - Notion integration service
- **GoogleService** - Google integration
- **TodoistService** - Todoist integration
- **JiraService** - JIRA integration
- **UserManagementService** - User credential management
- **McpConnectionService** - MCP connection handling
- **McpServerManager** - Abstract MCP server management
- **RealMcpServerManager** - Real MCP server implementation

---

## 6. Security Implementation

### 6.1 Encryption Methods

**IntegrationAccount (Laravel Native):**
```php
protected $casts = [
    'access_token' => 'encrypted', // Uses APP_KEY from .env
];
```

**GitConnection (Manual):**
```php
public function getAccessToken(): string {
    return Crypt::decryptString($this->access_token_enc);
}

public function setAccessToken(string $token): void {
    $this->access_token_enc = Crypt::encryptString($token);
}
```

**McpServer (UNENCRYPTED - CRITICAL ISSUE):**
```
private_key stored as text
public_key stored as text
server_public_key stored as text
```

### 6.2 Access Control

**Authentication:**
- All credential endpoints require authenticated user
- User isolation enforced in controllers
- No cross-user access possible

**Authorization:**
- Controller checks `Auth::id() === $integration->user_id`
- User middleware provided (RequireRole, RequirePermission - unfinished)

### 6.3 Audit Logging

**UserActivityLog Model:**
- Tracks user actions on other users' accounts
- Captures old and new values of changes
- IP address and user agent logging
- Action and entity type tracking

**Status:**
- Model exists but not integrated into credential controllers
- No audit events fired for integration changes

---

## 7. Error Handling & Validation

### 7.1 Validation

**IntegrationsController:**
```php
$request->validate([
    'type' => ['required', new Enum(IntegrationType::class)],
    'access_token' => 'required|string',
    'meta' => 'nullable|array',
]);
```

**Limitations:**
- No credential format validation (e.g., token length, format)
- No external validation (try to use the token)
- No rate limiting on validation attempts

### 7.2 Error States

**Available in Models:**
- `IntegrationStatus` - only active/inactive (no error state)
- `GitConnectionStatus` - includes error and expired states
- `McpIntegration` - includes error state with error message

**Missing:**
- Error state for IntegrationAccount
- Validation failure reasons
- Retry mechanisms

### 7.3 Testing

**Test File:** `/Users/fred/PhpstormProjects/mcp_manager/tests/Feature/IntegrationsTest.php`

**Coverage:**
- User can list their integrations
- User can create integration
- User cannot create duplicate active integrations
- User can update their integration
- User cannot update another user's integration
- User can delete their integration
- User cannot delete another user's integration

**Missing Tests:**
- Token encryption/decryption
- Token expiration scenarios
- Credential validation
- Concurrent operations
- Rate limiting
- Scope management (for Git)

---

## 8. Existing Features (What's Working)

### Feature 1: Generic Service Integration Management
- Add/remove service integrations (Notion, Gmail, etc.)
- Store encrypted API tokens
- Manage integration status (active/inactive)
- Store service-specific metadata
- Prevent duplicate active integrations
- User isolation

### Feature 2: Git OAuth Connection
- OAuth 2.0 flow for GitHub and GitLab
- Multiple accounts per provider
- Token expiration tracking
- Refresh token support
- Repository synchronization
- Scope management
- Status tracking (active, expired, error)
- User profile caching

### Feature 3: Audit Trail
- User activity logging for admin operations
- Action tracking
- Value change history

### Feature 4: Encryption
- Automatic encryption via Laravel casts (IntegrationAccount)
- Manual encryption wrapper (GitConnection)
- RSA asymmetric encryption service available
- AES-256-GCM symmetric encryption available

---

## 9. Missing/Incomplete Features

### Critical Issues

1. **Unencrypted MCP Server Keys**
   - Private and public keys stored as plain text
   - Should be encrypted at rest
   - No key rotation mechanism

2. **No Token Validation**
   - Can store invalid tokens
   - No way to test if token is valid before saving
   - Users discover token expiration only when trying to use service

3. **No Credential Refresh**
   - IntegrationAccounts can't refresh tokens
   - GitConnection has refresh_token but no auto-refresh on expiration
   - Manual reconnect required

4. **No Audit Logging for Credential Operations**
   - UserActivityLog exists but not used
   - No record of token changes
   - No record of credential access

### Feature Gaps

5. **Credential Rotation**
   - No mechanism to rotate/regenerate tokens
   - No grace period for old tokens
   - No alerts for unused credentials

6. **Multi-Device Sessions**
   - No credential sync across devices
   - No device-specific token management
   - No session invalidation across devices

7. **Rate Limiting**
   - No rate limiting on validation endpoints
   - No brute force protection
   - No throttling for failed attempts

8. **Revocation Management**
   - No way to revoke specific credentials
   - No revocation lists
   - No notification on revocation

9. **Credential Sharing**
   - No team/group credential sharing
   - No delegation mechanism
   - No temporary access tokens

10. **Advanced Security**
    - No two-factor authentication for credential operations
    - No IP allowlisting
    - No anomaly detection
    - No credential expiration policies

---

## 10. Data Flow Diagram

```
User Interface (React)
    ↓
useIntegrations Hook (TypeScript)
    ↓
API Endpoints (Laravel)
    ├─ GET /api/integrations
    ├─ POST /api/integrations
    ├─ PUT /api/integrations/{id}
    └─ DELETE /api/integrations/{id}
    ↓
Controllers
    ├─ IntegrationsController (generic services)
    ├─ GitOAuthController (Git OAuth)
    └─ GitConnectionsController (display)
    ↓
Models
    ├─ IntegrationAccount (type-safe, encrypted)
    ├─ GitConnection (encrypted, expiring tokens)
    └─ McpIntegration (status tracking)
    ↓
Database
    ├─ integration_accounts (encrypted tokens)
    ├─ git_connections (encrypted tokens)
    └─ mcp_integrations (config storage)
```

---

## 11. Technology Stack Summary

**Backend:**
- Laravel 12 (PHP 8.4)
- Eloquent ORM
- Inertia.js v2 for server-side rendering
- PostgreSQL database
- Laravel Encryption (APP_KEY-based)
- OpenSSL for manual encryption

**Frontend:**
- React 19
- TypeScript 5.7
- TailwindCSS 4
- Shadcn/UI components
- Inertia.js React adapter
- Lucide React icons

**Security Libraries:**
- Laravel built-in encryption
- OpenSSL (PHP)
- Password hashing (bcrypt)

---

## 12. File Structure Reference

```
Backend Credential Management:
app/
├── Enums/
│   ├── IntegrationType.php
│   ├── IntegrationStatus.php
│   ├── GitProvider.php
│   ├── GitConnectionStatus.php
│   └── UserRole.php, UserPermission.php
├── Models/
│   ├── IntegrationAccount.php
│   ├── GitConnection.php
│   ├── McpIntegration.php
│   ├── McpServer.php
│   ├── User.php
│   └── UserActivityLog.php
├── Http/Controllers/
│   ├── IntegrationsController.php
│   ├── GitConnectionsController.php
│   ├── GitOAuthController.php (routes reference)
│   ├── GitRepositoryController.php
│   └── McpIntegrationController.php
├── Http/Middleware/
│   ├── RequireRole.php
│   └── RequirePermission.php
├── Http/Requests/
│   ├── Admin/ (form requests for validation)
│   └── Workflow/ (other operations)
├── Services/
│   ├── CryptoService.php
│   ├── McpAuthService.php
│   ├── GoogleService.php
│   ├── TodoistService.php
│   └── UserManagementService.php
└── DataTransferObjects/
    └── CodeAnalysis/

Frontend Credential Management:
resources/js/
├── pages/
│   ├── integrations.tsx
│   └── git/
│       ├── connections.tsx
│       └── repositories.tsx
├── components/integrations/
│   ├── integration-list.tsx
│   ├── integration-form.tsx
│   ├── integration-card.tsx
│   ├── integration-card-enhanced.tsx
│   └── google-integration-card.tsx
├── hooks/
│   ├── use-integrations.ts
│   ├── use-api-token.ts
│   └── (service-specific hooks)
└── types/
    ├── integrations.ts
    ├── mcp.types.ts
    └── admin.ts

Database:
database/
├── migrations/
│   ├── 2025_06_08_105450_create_integration_accounts_table.php
│   ├── 2025_10_24_215549_01_create_git_connections_table.php
│   ├── 2025_10_24_215549_02_create_git_repositories_table.php
│   └── 2025_11_01_100139_create_user_tokens_table.php
├── factories/
│   ├── IntegrationAccountFactory.php
│   └── GitConnectionFactory.php
└── seeders/

Tests:
tests/
├── Feature/
│   ├── IntegrationsTest.php
│   ├── NotionIntegrationTest.php
│   └── Http/Controllers/
│       └── TodoistIntegrationControllerTest.php
└── Unit/
    ├── Services/
    │   └── Git/Clients/GitLabClientTest.php
    └── (service-specific tests)
```

---

## 13. Recommendations for Next Steps

### Immediate (Critical)
1. **Encrypt McpServer keys** - Use encrypted column or environment variables
2. **Add token validation endpoint** - Test credentials before saving
3. **Implement audit logging** - Log all credential operations
4. **Fix error states** - Add error status to IntegrationAccount

### Short-term (Important)
5. Implement automatic token refresh for expiring credentials
6. Add rate limiting to credential endpoints
7. Implement credential rotation mechanism
8. Add two-factor authentication for credential operations
9. Create credential expiration policies
10. Build credential revocation system

### Medium-term (Enhancement)
11. Team/group credential sharing
12. Temporary access tokens
13. Credential usage analytics
14. Advanced security features (IP allowlisting, anomaly detection)
15. Device-specific credential management

### Testing
16. Expand test coverage for credential operations
17. Add security/penetration tests
18. Test token refresh scenarios
19. Test concurrent operations
20. Test rate limiting

---

## 14. Summary Statistics

| Category | Count | Status |
|----------|-------|--------|
| Integration Types Supported | 7 | Working |
| Git Providers Supported | 2 | Working |
| API Endpoints (Integrations) | 4 | Working |
| API Endpoints (Git) | 6+ | Working |
| Models with Credentials | 4 | Partial |
| Encrypted Fields | 2 | ✓ |
| Unencrypted Sensitive Fields | 3 | ✗ |
| Frontend Components | 5+ | Working |
| Test Files | 3+ | Basic Coverage |
| Audit Log Tracking | 1 Model | Not Integrated |
| Token Validation | None | Missing |
| Token Refresh Mechanism | Partial | GitConnection only |
| Rate Limiting | None | Missing |

