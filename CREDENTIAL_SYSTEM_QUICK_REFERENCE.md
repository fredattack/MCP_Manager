# Credential Management System - Quick Reference

## System Architecture at a Glance

```
Three Parallel Credential Systems:

1. GENERIC INTEGRATIONS (Notion, Gmail, Todoist, JIRA, etc.)
   └─ IntegrationAccount Model
      ├─ Encrypted: access_token
      └─ Flexible: meta (JSON)

2. GIT OAUTH CONNECTIONS (GitHub, GitLab)
   └─ GitConnection Model
      ├─ Encrypted: access_token_enc, refresh_token_enc
      ├─ Tracked: expires_at, status
      └─ Multi-account: per provider support

3. MCP SERVER INTEGRATIONS
   └─ McpIntegration Model + McpServer Model
      ├─ Config-based integration
      ├─ Status tracking
      └─ ERROR: Keys not encrypted!
```

---

## Key Database Tables

### integration_accounts (Notion, Gmail, etc.)
```sql
CREATE TABLE integration_accounts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    type VARCHAR -- notion, gmail, todoist, jira, openai, sentry, calendar
    access_token TEXT -- ENCRYPTED by Laravel
    meta JSON,
    status VARCHAR -- active, inactive
    created_at, updated_at TIMESTAMP
);
```

### git_connections (GitHub, GitLab OAuth)
```sql
CREATE TABLE git_connections (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    provider VARCHAR -- github, gitlab
    external_user_id VARCHAR,
    access_token_enc TEXT, -- MANUALLY ENCRYPTED
    refresh_token_enc TEXT,
    expires_at TIMESTAMP,
    status VARCHAR, -- active, inactive, error, expired
    scopes JSON,
    meta JSON,
    UNIQUE(user_id, provider, external_user_id)
);
```

### mcp_integrations
```sql
CREATE TABLE mcp_integrations (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT,
    mcp_server_id BIGINT,
    service_name VARCHAR,
    enabled BOOLEAN,
    status VARCHAR, -- active, inactive, error, connecting
    config JSON,
    credentials_valid BOOLEAN,
    last_sync_at TIMESTAMP,
    error_message TEXT
);
```

---

## API Endpoints Map

### Generic Integration Endpoints
```
GET    /api/integrations              List all user integrations
POST   /api/integrations              Create new integration
                                      Payload: {type, access_token, meta?}
PUT    /api/integrations/{id}         Update integration
                                      Payload: {access_token?, meta?, status?}
DELETE /api/integrations/{id}         Delete integration
```

### Git OAuth Endpoints
```
POST   /api/git/{provider}/oauth/start
GET    /api/git/{provider}/oauth/callback
DELETE /api/git/{provider}/disconnect

GET    /api/git/{provider}/repos
POST   /api/git/{provider}/repos/sync
GET    /api/git/{provider}/repos/{id}
POST   /api/git/{provider}/repos/{id}/clone
```

### Frontend Pages
```
GET /integrations                      Integrations page
GET /git/connections                   Git connections page
```

---

## Core Models (Code Snippets)

### IntegrationAccount - Generic Services
```php
class IntegrationAccount extends Model {
    protected $fillable = ['user_id', 'type', 'access_token', 'meta', 'status'];
    
    protected $casts = [
        'type' => IntegrationType::class,
        'status' => IntegrationStatus::class,
        'meta' => 'array',
        'access_token' => 'encrypted', // ✓ AUTO ENCRYPTED
    ];
    
    public function scopeActive(Builder $builder): Builder {
        return $builder->where('status', IntegrationStatus::ACTIVE);
    }
}

// Types: NOTION, GMAIL, CALENDAR, OPENAI, TODOIST, JIRA, SENTRY
```

### GitConnection - OAuth Tokens
```php
class GitConnection extends Model {
    protected $fillable = [
        'user_id', 'provider', 'external_user_id', 
        'access_token_enc', 'refresh_token_enc',
        'expires_at', 'status', 'scopes', 'meta'
    ];
    
    // Manual encryption/decryption
    public function getAccessToken(): string {
        return Crypt::decryptString($this->access_token_enc);
    }
    
    public function setAccessToken(string $token): void {
        $this->access_token_enc = Crypt::encryptString($token);
    }
    
    public function isTokenExpired(): bool {
        return $this->expires_at && 
               now()->addMinutes(10)->isAfter($this->expires_at);
    }
}

// Providers: GITHUB, GITLAB
// Status: ACTIVE, INACTIVE, ERROR, EXPIRED
```

---

## Frontend Components Map

### Page: /integrations
```
IntegrationList
├─ Fetches: GET /api/integrations
├─ Dialog: Add New Integration
│  └─ Select Type → IntegrationForm → POST /api/integrations
└─ Grid of IntegrationCards
   ├─ Edit: PUT /api/integrations/{id}
   ├─ Delete: DELETE /api/integrations/{id}
   └─ Toggle: PUT /api/integrations/{id} {status}
```

### Page: /git/connections
```
GitConnections
├─ GitHub Card
│  ├─ Show: avatar, username, email, scopes, status
│  ├─ Button: Connect → POST /api/git/github/oauth/start
│  ├─ Button: Disconnect → DELETE /api/git/github/disconnect
│  └─ Expiry Warning (if < 30 min)
└─ GitLab Card
   └─ Same as GitHub
```

---

## Encryption Methods

### Method 1: Laravel Cast Encryption
```php
// IntegrationAccount uses this
protected $casts = [
    'access_token' => 'encrypted', // Uses APP_KEY
];

// Transparent: encrypted on write, decrypted on read
$account->access_token = 'secret'; // Auto-encrypted in DB
$token = $account->access_token;   // Auto-decrypted from DB
```

### Method 2: Manual Encryption
```php
// GitConnection uses this
public function setAccessToken(string $token): void {
    $this->access_token_enc = Crypt::encryptString($token);
}

public function getAccessToken(): string {
    return Crypt::decryptString($this->access_token_enc);
}

// Manual: need to call methods explicitly
$connection->setAccessToken('secret');
$token = $connection->getAccessToken();
```

### Method 3: NOT ENCRYPTED (⚠️ Security Issue)
```php
// McpServer stores keys unencrypted
private_key   → TEXT (plain, not encrypted)
public_key    → TEXT (plain, not encrypted)

// This is a critical security issue!
```

---

## Testing Credentials

### Current Tests
```
tests/Feature/IntegrationsTest.php
├─ test_user_can_list_their_integrations
├─ test_user_can_create_an_integration
├─ test_user_cannot_create_duplicate_active_integration
├─ test_user_can_update_their_integration
├─ test_user_cannot_update_another_users_integration
├─ test_user_can_delete_their_integration
└─ test_user_cannot_delete_another_users_integration
```

### Running Tests
```bash
php artisan test --filter IntegrationsTest
```

---

## Security Checklist

### What's Protected ✓
- [x] IntegrationAccount.access_token - Encrypted via Laravel cast
- [x] GitConnection.access_token_enc - Manually encrypted
- [x] GitConnection.refresh_token_enc - Manually encrypted
- [x] User isolation - Can't access other users' creds
- [x] Authentication - All endpoints require auth
- [x] Audit logging - UserActivityLog model exists

### What's NOT Protected ✗
- [ ] McpServer private/public keys - Stored plaintext
- [ ] Token validation - No endpoint to test tokens
- [ ] Token refresh - IntegrationAccount can't auto-refresh
- [ ] Error auditing - No logging of token failures
- [ ] Rate limiting - No limits on credential ops
- [ ] Token rotation - No key rotation mechanism
- [ ] Revocation - No revocation tracking
- [ ] 2FA - No 2FA for credential changes

---

## Common Operations

### Add Integration
```typescript
const response = await fetch('/api/integrations', {
    method: 'POST',
    body: JSON.stringify({
        type: 'notion',
        access_token: 'ntn_...',
        meta: { workspace: 'My Workspace' }
    })
});
```

### Update Token
```typescript
const response = await fetch(`/api/integrations/${id}`, {
    method: 'PUT',
    body: JSON.stringify({
        access_token: 'new_token_...'
    })
});
```

### Connect GitHub
```typescript
const response = await fetch('/api/git/github/oauth/start', {
    method: 'POST'
});
// Redirects to GitHub OAuth → callback → user profile stored
```

### Disconnect Git Account
```typescript
const response = await fetch('/api/git/github/disconnect', {
    method: 'DELETE'
});
```

---

## Known Issues & Gotchas

### 1. IntegrationAccount Has No Error State
- Only has `active` / `inactive` status
- Can't mark as errored if token is invalid
- Should add error status option

### 2. GitConnection Token Validation
- Sets `expires_at` from OAuth provider
- Checks expiration with 10-min buffer
- But doesn't auto-refresh, just warns UI

### 3. McpServer Keys Not Encrypted
- Critical security issue
- Private keys visible in plaintext
- Should encrypt at rest or use env vars

### 4. No Credential Validation Endpoint
- Can store invalid tokens
- Users find out when trying to use the service
- Should add test/validate endpoint

### 5. Audit Logging Not Integrated
- UserActivityLog model exists
- But controllers don't log credential operations
- Need to add audit events

---

## Environment Variables

```env
APP_KEY=base64:...          # Used for Laravel encryption
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PASSWORD=...
```

**Note:** All credential encryption uses the APP_KEY. If compromised, all tokens are at risk.

---

## Performance Notes

### Database Indexes
- `git_connections` has index on (user_id, provider)
- `git_connections` has unique on (user_id, provider, external_user_id)
- `integration_accounts` has user_id FK

### Query Optimization
```php
// Good: with eager loading
User::with('integrationAccounts')->find($user->id);

// Bad: N+1 query
foreach ($users as $user) {
    $user->integrationAccounts; // Separate query per user
}
```

---

## Future Improvements (Priority Order)

1. **CRITICAL**: Encrypt McpServer keys
2. **HIGH**: Add token validation endpoint
3. **HIGH**: Implement error state for IntegrationAccount
4. **HIGH**: Add audit logging integration
5. **MEDIUM**: Auto-refresh expired tokens
6. **MEDIUM**: Rate limiting on credential ops
7. **MEDIUM**: Credential rotation mechanism
8. **LOW**: Team credential sharing
9. **LOW**: Credential usage analytics

---

## References

- Full analysis: `CREDENTIAL_MANAGEMENT_ANALYSIS.md`
- Models: `app/Models/{IntegrationAccount,GitConnection,McpIntegration,McpServer}.php`
- Controllers: `app/Http/Controllers/{IntegrationsController,GitConnectionsController}.php`
- Frontend: `resources/js/{pages,components}/integrations/`
- Tests: `tests/Feature/IntegrationsTest.php`

