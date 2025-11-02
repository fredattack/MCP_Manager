# Credential Management System - Visual Diagrams

## 1. Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     MCP Manager Application                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Frontend (React 19 + TypeScript)                               │
│  ├─ /integrations page                                          │
│  │  └─ IntegrationList → IntegrationCard(s)                     │
│  │     └─ useIntegrations hook                                  │
│  │                                                               │
│  └─ /git/connections page                                       │
│     └─ GitConnections component                                 │
│        └─ OAuth flow handler                                    │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Backend (Laravel 12 + PHP 8.4)                                 │
│  ├─ Controllers                                                 │
│  │  ├─ IntegrationsController (CRUD)                            │
│  │  ├─ GitConnectionsController (display)                       │
│  │  ├─ GitOAuthController (OAuth flow)                          │
│  │  └─ GitRepositoryController (repo ops)                       │
│  │                                                               │
│  ├─ Models (with Encryption)                                    │
│  │  ├─ IntegrationAccount (access_token: encrypted)             │
│  │  ├─ GitConnection (manual encryption)                        │
│  │  ├─ McpIntegration (config + status)                         │
│  │  └─ McpServer (KEYS NOT ENCRYPTED! ⚠️)                       │
│  │                                                               │
│  ├─ Services                                                    │
│  │  ├─ CryptoService (RSA, AES-256-GCM)                         │
│  │  ├─ McpAuthService                                           │
│  │  └─ Integration-specific services                            │
│  │                                                               │
│  └─ Middleware                                                  │
│     ├─ Authentication (all routes)                              │
│     ├─ RequireRole (role-based)                                 │
│     └─ RequirePermission (permission-based)                     │
│                                                                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Database (PostgreSQL)                                          │
│  ├─ integration_accounts (7 types)                               │
│  ├─ git_connections (2 providers)                                │
│  ├─ mcp_integrations                                            │
│  ├─ mcp_servers                                                 │
│  ├─ user_tokens                                                 │
│  └─ user_activity_logs (audit)                                  │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Data Flow Diagram

### Generic Integration Flow (Notion, Gmail, etc.)

```
User Interface
│
├─ Click: "Add Integration"
│  │
│  └─→ IntegrationList Component
│      │
│      ├─ Dialog opens
│      ├─ User selects type (Notion, Gmail, etc.)
│      ├─ IntegrationForm appears
│      │  └─ User enters API token
│      │
│      └─→ POST /api/integrations
│         │
│         └─→ IntegrationsController@store
│            │
│            ├─ Validate: type, token, meta
│            ├─ Check: no duplicate active
│            │
│            └─→ IntegrationAccount::create()
│               │
│               ├─ type → IntegrationType enum
│               ├─ access_token → ENCRYPTED by Laravel cast
│               ├─ meta → JSON (stored as-is)
│               └─ status → ACTIVE
│
├─ Database: INSERT integration_accounts
│
└─→ Response: {id, type, status, created_at}
   │
   └─ UI updates: IntegrationCard displayed with badge
```

### Git OAuth Flow (GitHub/GitLab)

```
User clicks: "Connect GitHub"
│
└─→ POST /api/git/github/oauth/start
   │
   ├─ Generate state token (CSRF protection)
   ├─ Store state in session/cache
   │
   └─ Response: {auth_url: "https://github.com/login/oauth/authorize?..."}
      │
      └─ Frontend: window.location.href = auth_url
         │
         └─ GitHub OAuth page (user grants permissions)
            │
            └─ GitHub redirects to callback with {code, state}
               │
               └─→ GET /api/git/github/oauth/callback?code=...&state=...
                  │
                  ├─ Validate state token
                  ├─ Exchange code for access_token + refresh_token
                  │
                  └─→ GitConnection::create()
                     │
                     ├─ provider → github
                     ├─ access_token_enc → ENCRYPTED manually
                     ├─ refresh_token_enc → ENCRYPTED manually
                     ├─ expires_at → from OAuth response
                     ├─ scopes → from OAuth response
                     ├─ meta → {username, email, avatar_url, ...}
                     └─ status → ACTIVE
                        │
                        └─ Database: INSERT git_connections
                           │
                           └─ Fetch & cache user repos
                              │
                              └─→ GitRepository::create() x N
                                 │
                                 └─ Database: INSERT git_repositories
```

---

## 3. Database Schema Relationships

```
┌──────────────────────────────────────────────────────────────┐
│                           users                               │
│ ┌─────────────────────────────────────────────────────────┐  │
│ │ id (PK)        │ name  │ email  │ password │ api_token    │  │
│ └─────────────────────────────────────────────────────────┘  │
└────┬──────────────────────────────────────────────────────┬──┘
     │                                                       │
     │ 1:N                                                  │ 1:N
     │                                                       │
┌────▼──────────────────────┐                ┌──────────────▼──────────────────────┐
│  integration_accounts      │                │   git_connections                   │
├────────────────────────────┤                ├─────────────────────────────────────┤
│ id (PK)                    │                │ id (PK)                             │
│ user_id (FK)               │                │ user_id (FK)                        │
│ type (enum)                │ 7 types        │ provider (enum)       2 providers   │
│ access_token (encrypted)   │                │ external_user_id                    │
│ meta (json)                │                │ access_token_enc (encrypted)        │
│ status                     │ active/        │ refresh_token_enc (encrypted)       │
│                            │ inactive       │ expires_at                          │
│                            │                │ status           active/inactive/   │
│                            │                │                   error/expired     │
│                            │                │ scopes (json)                       │
│                            │                │ meta (json)                         │
│                            │                │                                     │
│ CONSTRAINTS:               │                │ CONSTRAINTS:                        │
│ - One active per type      │                │ - Multiple per user/provider        │
│   per user                 │                │ - Unique (user_id,provider,ext_id) │
└────────────────────────────┘                └─────────────────────────────────────┘
                                                   │
                                                   │ 1:N
                                                   │
                                              ┌────▼──────────────────────┐
                                              │   git_repositories        │
                                              ├────────────────────────────┤
                                              │ id (PK)                    │
                                              │ user_id (FK)               │
                                              │ provider                   │
                                              │ external_id (repo ID)      │
                                              │ full_name                  │
                                              │ default_branch             │
                                              │ visibility                 │
                                              │ last_synced_at             │
                                              │ meta (json)                │
                                              │                            │
                                              │ CONSTRAINTS:               │
                                              │ - Unique (user_id,        │
                                              │   provider,external_id)    │
                                              └────────────────────────────┘
```

---

## 4. Integration Type & Status Matrix

```
┌─────────────────────────────────────────────────────────────┐
│          Integration Types (IntegrationType Enum)            │
├────────────┬──────────────────────┬────────────────────────┤
│   Type     │  Display Name        │   Description          │
├────────────┼──────────────────────┼────────────────────────┤
│ notion     │ Notion               │ Notion workspace       │
│ gmail      │ Gmail                │ Gmail/Google account   │
│ calendar   │ Google Calendar      │ Google Calendar        │
│ openai     │ OpenAI               │ OpenAI services        │
│ todoist    │ Todoist              │ Todoist account        │
│ jira       │ JIRA                 │ Atlassian JIRA         │
│ sentry     │ Sentry               │ Sentry monitoring      │
└────────────┴──────────────────────┴────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│      Integration Status (IntegrationStatus Enum)            │
├────────────┬──────────────────────────────────────────────┤
│   Status   │   Meaning                                    │
├────────────┼──────────────────────────────────────────────┤
│ active     │ Enabled and working                          │
│ inactive   │ Disabled or deactivated by user             │
│            │ ⚠️ No error state! (missing feature)        │
└────────────┴──────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│      Git Connection Status (GitConnectionStatus Enum)       │
├────────────┬──────────────────────────────────────────────┤
│   Status   │   Meaning                                    │
├────────────┼──────────────────────────────────────────────┤
│ active     │ Connected and valid                          │
│ inactive   │ Disconnected                                 │
│ error      │ Connection failed (API error, permissions)  │
│ expired    │ Token expired and needs refresh              │
└────────────┴──────────────────────────────────────────────┘
```

---

## 5. Encryption Strategy Comparison

```
┌─────────────────────────────────────────────────────────────┐
│            Three Different Encryption Approaches            │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1️⃣  INTEGRATIONACCOUNT (Laravel Cast Encryption)           │
│  ├─ Method: protected $casts = ['access_token' => 'encrypted']
│  ├─ When: Automatic on write/read                           │
│  ├─ Key: Uses APP_KEY from .env                             │
│  ├─ Transparent: Yes                                        │
│  ├─ Code: $account->access_token = 'secret'                 │
│  ├─ Status: ✓ WORKING                                       │
│  └─ Risk: If APP_KEY leaked, all tokens compromised         │
│                                                              │
│  2️⃣  GITCONNECTION (Manual Encryption Methods)              │
│  ├─ Method: Explicit getter/setter methods                  │
│  ├─ When: Manual call to getAccessToken()/setAccessToken() │
│  ├─ Key: Uses APP_KEY (via Crypt facade)                    │
│  ├─ Transparent: No                                         │
│  ├─ Code: $conn->setAccessToken('secret')                   │
│  ├─ Status: ✓ WORKING                                       │
│  └─ Risk: Same as #1                                        │
│                                                              │
│  3️⃣  MCPSERVER (NO ENCRYPTION - CRITICAL ISSUE)            │
│  ├─ Method: Plain text storage                              │
│  ├─ When: N/A (not encrypted)                               │
│  ├─ Key: N/A                                                │
│  ├─ Transparent: N/A                                        │
│  ├─ Columns: private_key, public_key, server_public_key    │
│  ├─ Status: ✗ CRITICAL SECURITY ISSUE                       │
│  └─ Risk: Anyone with DB access sees all keys               │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 6. User Interaction Flow - Adding Integration

```
┌───────────────────────────────────────────────────────────────┐
│                    User Journey                               │
├───────────────────────────────────────────────────────────────┤
│                                                                │
│  1. User navigates to /integrations                           │
│     ↓                                                         │
│  2. IntegrationList component mounts                          │
│     ↓                                                         │
│  3. useIntegrations.fetchIntegrations()                       │
│     └─→ GET /api/integrations                                 │
│         └─→ Returns: [{id, type, status, ...}, ...]          │
│     ↓                                                         │
│  4. Display list of existing integrations                     │
│     - Each as IntegrationCard with Edit/Delete/Toggle        │
│     ↓                                                         │
│  5. User clicks "Add Integration"                            │
│     ↓                                                         │
│  6. Dialog opens with type selector                          │
│     └─→ Options: Notion, Gmail, Todoist, JIRA, etc.         │
│     ↓                                                         │
│  7. User selects type (e.g., Notion)                         │
│     ↓                                                         │
│  8. IntegrationForm displays                                 │
│     - Help text: "Get token from https://..."               │
│     - Password input for token                               │
│     ↓                                                         │
│  9. User enters token                                        │
│     ↓                                                         │
│ 10. User clicks "Add Integration"                            │
│     ↓                                                         │
│ 11. useIntegrations.createIntegration()                      │
│     └─→ POST /api/integrations                               │
│         Payload: {                                           │
│           type: 'notion',                                    │
│           access_token: 'ntn_...',                           │
│           meta: {workspace: 'My Workspace'}                  │
│         }                                                    │
│     ↓                                                         │
│ 12. IntegrationsController validates & creates               │
│     └─→ Check: no duplicate active                           │
│     └─→ Create: IntegrationAccount                           │
│         - Encrypt token automatically                        │
│         - Store in DB                                        │
│     ↓                                                         │
│ 13. Response: {id: 42, type: 'notion', status: 'active'} ✓  │
│     ↓                                                         │
│ 14. Frontend updates state                                   │
│     └─→ New card appears in grid                             │
│     ↓                                                         │
│ 15. Dialog closes                                            │
│                                                                │
│ ⚠️  Note: No validation that token is actually valid!        │
│     User will only discover if token is invalid              │
│     when they try to use the service                         │
│                                                                │
└───────────────────────────────────────────────────────────────┘
```

---

## 7. Git Connection OAuth Flow Diagram

```
┌────────────────────────────────────────────────────────────────────┐
│                    OAuth 2.0 Flow (GitHub)                         │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  STEP 1: User clicks "Connect GitHub"                             │
│  ─────────────────────────────────────────────────────────────    │
│           ↓                                                        │
│   POST /api/git/github/oauth/start                                │
│           ↓                                                        │
│   GitOAuthController:                                             │
│   ├─ Generate state token (32 bytes random)                       │
│   ├─ Store in session: session()->put('oauth_state', ...)         │
│   ├─ Build GitHub auth URL with:                                  │
│   │  ├─ client_id                                                 │
│   │  ├─ redirect_uri: /api/git/github/oauth/callback             │
│   │  ├─ scope: repo, read:user, workflow                         │
│   │  └─ state: <generated token>                                 │
│   └─ Response: {auth_url: "https://github.com/login/..."}        │
│           ↓                                                        │
│   Frontend: window.location.href = auth_url                       │
│           ↓                                                        │
│  ┌────────────────────────────────────────┐                       │
│  │  GitHub OAuth Authorization Page       │                       │
│  │  ├─ Display: "... is requesting access"│                       │
│  │  ├─ Show scopes being requested         │                       │
│  │  └─ Buttons: Authorize / Cancel        │                       │
│  └────────────────────────────────────────┘                       │
│           ↓                                                        │
│       [User clicks Authorize]                                     │
│           ↓                                                        │
│  STEP 2: GitHub redirects to callback                            │
│  ─────────────────────────────────────────────────────────────    │
│           ↓                                                        │
│   GET /api/git/github/oauth/callback?code=...&state=...          │
│           ↓                                                        │
│   GitOAuthController:                                             │
│   ├─ Get state from URL param                                     │
│   ├─ Compare with session state: MUST MATCH                       │
│   ├─ If mismatch: ERROR (CSRF attack suspected)                  │
│   ├─ Exchange code for tokens:                                    │
│   │  POST https://github.com/login/oauth/access_token            │
│   │  ├─ client_id                                                 │
│   │  ├─ client_secret                                             │
│   │  └─ code                                                      │
│   │  Response: {access_token, refresh_token?, expires_in, ...}  │
│   ├─ Fetch user profile:                                          │
│   │  GET https://api.github.com/user                              │
│   │  Response: {id, login, email, avatar_url, ...}               │
│   └─ Create GitConnection:                                        │
│      ├─ provider: 'github'                                        │
│      ├─ external_user_id: id (GitHub user ID)                    │
│      ├─ access_token_enc: ENCRYPT(access_token)                  │
│      ├─ refresh_token_enc: ENCRYPT(refresh_token) [if present]   │
│      ├─ expires_at: now() + expires_in                           │
│      ├─ scopes: from GitHub response                              │
│      ├─ meta: {username, email, avatar_url, ...}                 │
│      └─ status: 'active'                                          │
│           ↓                                                        │
│   Database: INSERT git_connections                               │
│           ↓                                                        │
│  STEP 3: Sync repositories                                       │
│  ─────────────────────────────────────────────────────────────    │
│           ↓                                                        │
│   GET /api/git/github/repos                                      │
│   └─→ GitRepositoryController@index                              │
│       ├─ Get user's GitHub connection                             │
│       ├─ Check if token expired: isTokenExpired()                │
│       │  └─ If expired: warn user, don't call API               │
│       ├─ Call: GET https://api.github.com/user/repos             │
│       │       (with Authorization: Bearer <token>)               │
│       ├─ For each repo:                                           │
│       │  └─ Create/Update GitRepository record                    │
│       └─ Return: [...repos, sorted, paginated]                   │
│           ↓                                                        │
│   Frontend displays repos in grid                                 │
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
```

---

## 8. API Response Examples

### GET /api/integrations
```json
[
  {
    "id": 1,
    "type": "notion",
    "access_token": "ntn_encrypted_base64_string_here",
    "meta": {
      "workspace_name": "My Workspace",
      "workspace_id": "abc123"
    },
    "status": "active",
    "created_at": "2025-11-01T10:30:00Z",
    "updated_at": "2025-11-01T10:30:00Z"
  },
  {
    "id": 2,
    "type": "todoist",
    "access_token": "encrypted_todoist_token",
    "meta": null,
    "status": "active",
    "created_at": "2025-10-31T15:45:00Z",
    "updated_at": "2025-11-01T09:20:00Z"
  }
]
```

### GET /git/connections (Server-side rendered view)
```json
{
  "connections": [
    {
      "id": 1,
      "provider": "github",
      "external_user_id": "octocat",
      "username": "octocat",
      "email": "octocat@github.com",
      "avatar_url": "https://avatars.githubusercontent.com/u/1?v=4",
      "scopes": ["repo", "read:user", "workflow"],
      "status": "active",
      "expires_at": "2026-11-01T10:30:00Z",
      "created_at": "2025-11-01T10:30:00Z"
    }
  ]
}
```

---

## 9. Token Lifecycle Timeline

```
┌────────────────────────────────────────────────────────────────┐
│              Token Lifecycle - Git Connection                 │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│ T0: Token created (OAuth callback)                            │
│ ├─ access_token: stored encrypted                             │
│ ├─ expires_at: github_expiration_time                          │
│ └─ status: ACTIVE                                              │
│                                                                 │
│ ... time passes ...                                            │
│                                                                 │
│ T1: Token still valid (< expires_at - 10 min)                 │
│ ├─ isTokenExpired(): false                                     │
│ ├─ API calls work normally                                     │
│ └─ UI shows: "Connected" (green badge)                         │
│                                                                 │
│ ... more time passes ...                                       │
│                                                                 │
│ T2: Token expiring soon (< 10 min until expiry)               │
│ ├─ isTokenExpired(): true                                      │
│ ├─ API calls blocked (to be safe)                              │
│ ├─ UI shows: ⚠️  "Expires soon - Renew" warning                │
│ └─ User should click "Renew Connection"                        │
│                                                                 │
│ T3: User clicks "Renew Connection"                             │
│ ├─ POST /api/git/github/oauth/start                            │
│ ├─ Redirects to GitHub OAuth again                             │
│ └─ Callback updates token + expires_at                         │
│                                                                 │
│ T4: Token expired (past expires_at)                           │
│ ├─ isTokenExpired(): true                                      │
│ ├─ API calls fail (401 Unauthorized)                           │
│ ├─ status changed to: EXPIRED                                  │
│ └─ UI shows: 🔴 "Expired - Reconnect"                          │
│                                                                 │
│ User must manually reconnect to continue                       │
│ ⚠️  Note: No automatic refresh! Manual required!               │
│                                                                 │
└────────────────────────────────────────────────────────────────┘
```

---

## 10. Security Threat Model

```
┌─────────────────────────────────────────────────────────────────┐
│                  SECURITY THREAT ANALYSIS                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  THREAT 1: Database Compromise                                 │
│  ├─ IF: Attacker gets database access                          │
│  ├─ IntegrationAccount tokens: ENCRYPTED ✓                     │
│  │  └─ Needs APP_KEY to decrypt                                │
│  ├─ GitConnection tokens: ENCRYPTED ✓                          │
│  │  └─ Needs APP_KEY to decrypt                                │
│  └─ McpServer keys: PLAINTEXT ✗                                │
│     └─ Directly readable without decryption                    │
│                                                                  │
│  THREAT 2: Application Memory Compromise                       │
│  ├─ Tokens exist in memory during request handling             │
│  ├─ Middleware could read from $_ENV                            │
│  ├─ No memory encryption available                              │
│  └─ Risk: MEDIUM (require app compromise)                      │
│                                                                  │
│  THREAT 3: Token Leakage via Logs                              │
│  ├─ Tokens might be logged in error messages                   │
│  ├─ Check: Laravel logs, slow query logs, etc.                 │
│  └─ Risk: MEDIUM (depends on log config)                       │
│                                                                  │
│  THREAT 4: Brute Force Token Validation                        │
│  ├─ No rate limiting on /api/integrations                      │
│  ├─ Attacker could try many invalid tokens                     │
│  └─ Risk: LOW (tokens are long, random)                        │
│                                                                  │
│  THREAT 5: Cross-Site Request Forgery (CSRF)                  │
│  ├─ Git OAuth: state parameter validated ✓                     │
│  ├─ Integration endpoints: CSRF token required ✓               │
│  └─ Risk: LOW (protected)                                      │
│                                                                  │
│  THREAT 6: Cross-User Access                                   │
│  ├─ Controllers check: Auth::id() === $integration->user_id     │
│  ├─ No ability to access others' creds                         │
│  └─ Risk: LOW (protected)                                      │
│                                                                  │
│  THREAT 7: Compromised APP_KEY                                 │
│  ├─ IF: .env file stolen                                       │
│  ├─ Attacker can decrypt IntegrationAccount tokens             │
│  ├─ Attacker can decrypt GitConnection tokens                  │
│  └─ Risk: CRITICAL (master key compromise)                     │
│                                                                  │
│  THREAT 8: Token Exfiltration via OAuth Callback              │
│  ├─ Tokens passed through URL in OAuth flow                    │
│ │ ├─ GitHub: Returns tokens in POST response (not URL) ✓      │
│  │ └─ Risk: LOW (tokens not in URL)                            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 11. Component Hierarchy

```
Pages
├── /integrations
│   └── IntegrationList
│       ├── Dialog (Add Integration)
│       │   ├── Select (Type Selector)
│       │   └── IntegrationForm
│       │       ├── Input (Type display)
│       │       ├── Input (Token - password type)
│       │       ├── Button (Submit)
│       │       └── Error display
│       └── Grid
│           └── IntegrationCard x N
│               ├── Badge (Status)
│               ├── Dialog (Edit)
│               │   └── IntegrationForm (update mode)
│               ├── Dialog (Delete)
│               │   └── Confirmation
│               └── Buttons (Edit, Delete, Toggle)
│
└── /git/connections
    └── GitConnections
        ├── GitHub Card
        │   ├── Avatar + User Info
        │   ├── Badge (Status)
        │   ├── Badge x N (Scopes)
        │   ├── Warning (if expiring)
        │   └── Buttons (Connect, Disconnect, Renew)
        └── GitLab Card
            └── Same as GitHub

Hooks
├── useIntegrations
│   ├── fetchIntegrations() → GET /api/integrations
│   ├── createIntegration() → POST /api/integrations
│   ├── updateIntegration() → PUT /api/integrations/{id}
│   └── deleteIntegration() → DELETE /api/integrations/{id}
└── useApiToken
    └── getApiToken() → from storage/context
```

---

## 12. File Location Quick Map

```
Frontend Implementation
├── resources/js/pages/
│   ├── integrations.tsx (Main page)
│   └── git/connections.tsx (Git page)
├── resources/js/components/integrations/
│   ├── integration-list.tsx (Container)
│   ├── integration-form.tsx (Form)
│   ├── integration-card.tsx (Display card)
│   ├── integration-card-enhanced.tsx (Enhanced version)
│   └── google-integration-card.tsx (Google-specific)
├── resources/js/hooks/
│   ├── use-integrations.ts (API calls)
│   └── use-api-token.ts (Auth)
└── resources/js/types/
    └── integrations.ts (Types & enums)

Backend Implementation
├── app/Models/
│   ├── IntegrationAccount.php (Generic services)
│   ├── GitConnection.php (OAuth tokens)
│   ├── McpIntegration.php (MCP integration config)
│   └── McpServer.php (MCP server config)
├── app/Http/Controllers/
│   ├── IntegrationsController.php (CRUD)
│   ├── GitConnectionsController.php (Display)
│   ├── GitOAuthController.php (OAuth flow)
│   └── GitRepositoryController.php (Repo ops)
├── app/Http/Middleware/
│   ├── RequireRole.php (Role check)
│   └── RequirePermission.php (Permission check)
├── app/Enums/
│   ├── IntegrationType.php
│   ├── IntegrationStatus.php
│   ├── GitProvider.php
│   └── GitConnectionStatus.php
├── app/Services/
│   ├── CryptoService.php (Encryption)
│   ├── McpAuthService.php
│   └── (integration-specific services)
└── database/migrations/
    ├── 2025_06_08_105450_create_integration_accounts_table.php
    ├── 2025_10_24_215549_01_create_git_connections_table.php
    └── 2025_10_24_215549_02_create_git_repositories_table.php

Testing
└── tests/Feature/
    └── IntegrationsTest.php (7 tests)
```

