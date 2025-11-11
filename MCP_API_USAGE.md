# MCP API Usage Guide

Complete guide for integrating the MCP Server (Python) with MCP Manager (Laravel).

## Quick Start

### 1. Run the Seeder

```bash
php artisan db:seed --class=McpDevelopmentSeeder
```

This will output your **MCP_TOKEN** - copy it!

### 2. Configure Python Server

Add to your Python `.env` file:

```bash
MCP_API_URL=http://localhost:3978
MCP_TOKEN=mcp_dev_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### 3. Test the Connection

```bash
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
     http://localhost:3978/api/mcp/me
```

---

## API Endpoints

### Authentication: `GET /api/mcp/me`

Get authenticated user info and available services.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "user_id": 1,
  "email": "test@mcp-manager.local",
  "name": "Test User",
  "organizations": [
    {
      "id": 1,
      "name": "Development Team",
      "role": "owner"
    }
  ],
  "available_services": ["todoist", "openai", "jira", "notion", "sentry"]
}
```

---

### Create Lease: `POST /api/mcp/credentials/lease`

Request credentials for specific services.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "services": ["todoist", "jira", "notion"],
  "ttl": 3600,
  "server_id": "mcp-server-1"
}
```

**Response:**
```json
{
  "lease_id": "lse_xxxxxxxxxxxxxx",
  "credentials": {
    "todoist": {
      "access_token": "xxxxx"
    },
    "jira": {
      "url": "https://dev-team.atlassian.net",
      "email": "jira@dev-team.local",
      "api_token": "xxxxx",
      "cloud": true
    },
    "notion": {
      "access_token": "xxxxx",
      "database_id": "xxxxx"
    }
  },
  "expires_at": "2025-11-08T23:39:56.000000Z",
  "renewable": true,
  "credential_scope": "mixed",
  "included_org_credentials": [
    {
      "organization_id": 1,
      "services": ["jira", "notion"]
    }
  ]
}
```

---

### Renew Lease: `POST /api/mcp/credentials/lease/{lease_id}/renew`

Extend the expiration time of an active lease.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "lease_id": "lse_xxxxxxxxxxxxxx",
  "expires_at": "2025-11-08T24:39:56.000000Z",
  "renewal_count": 1,
  "last_renewed_at": "2025-11-08T23:39:56.000000Z"
}
```

**Renewal Rules:**
- Leases can be renewed up to `max_renewals` times (default: 24)
- Each renewal extends the lease by the original TTL
- Cannot renew expired or revoked leases

---

### Get Lease Details: `GET /api/mcp/credentials/lease/{lease_id}`

Retrieve details about a specific lease.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "lease_id": "lse_xxxxxxxxxxxxxx",
  "status": "active",
  "services": ["todoist", "jira"],
  "expires_at": "2025-11-08T23:39:56.000000Z",
  "renewable": true,
  "renewal_count": 2,
  "credential_scope": "mixed"
}
```

---

### Revoke Lease: `DELETE /api/mcp/credentials/lease/{lease_id}`

Immediately revoke an active lease.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request (Optional):**
```json
{
  "reason": "Server shutdown"
}
```

**Response:**
```json
{
  "message": "Lease revoked successfully",
  "lease_id": "lse_xxxxxxxxxxxxxx",
  "revoked_at": "2025-11-08T23:39:56.000000Z"
}
```

---

## Credential Resolution Priority

The system resolves credentials using the following priority:

1. **Personal Credentials** (highest priority)
   - User's own integrations
   - Always take precedence over organization credentials

2. **Organization Credentials**
   - Shared integrations from organizations
   - User must be a member to access
   - Respects `shared_with` rules:
     - `all_members`: All organization members
     - `admins_only`: Only owners/admins
     - Specific user IDs

3. **Mixed Credentials**
   - When request includes services from both personal and org scopes
   - Each service uses the highest priority credential available

---

## Python Integration Example

### Step 1: Create Credential Provider

```python
# app/providers/mcp_manager_provider.py

import os
import httpx
from typing import Dict, Optional
from datetime import datetime, timedelta

class McpManagerCredentialProvider:
    def __init__(self):
        self.api_url = os.getenv("MCP_API_URL")
        self.token = os.getenv("MCP_TOKEN")
        self.lease_id = None
        self.credentials = {}
        self.expires_at = None

    async def bootstrap(self, services: list[str]):
        """Initialize and get initial lease"""
        async with httpx.AsyncClient() as client:
            # Authenticate
            me_response = await client.get(
                f"{self.api_url}/api/mcp/me",
                headers={"Authorization": f"Bearer {self.token}"}
            )
            me_response.raise_for_status()

            # Request lease
            lease_response = await client.post(
                f"{self.api_url}/api/mcp/credentials/lease",
                headers={"Authorization": f"Bearer {self.token}"},
                json={
                    "services": services,
                    "ttl": 3600,
                    "server_id": "mcp-server-1"
                }
            )
            lease_response.raise_for_status()

            data = lease_response.json()
            self.lease_id = data["lease_id"]
            self.credentials = data["credentials"]
            self.expires_at = datetime.fromisoformat(data["expires_at"])

    async def get_credentials(self, service: str) -> Optional[Dict]:
        """Get credentials for a service"""
        # Auto-refresh if expiring soon (< 10 min)
        if self.expires_at and (self.expires_at - datetime.now()) < timedelta(minutes=10):
            await self.refresh()

        return self.credentials.get(service)

    async def refresh(self):
        """Refresh the lease"""
        async with httpx.AsyncClient() as client:
            response = await client.post(
                f"{self.api_url}/api/mcp/credentials/lease/{self.lease_id}/renew",
                headers={"Authorization": f"Bearer {self.token}"}
            )
            response.raise_for_status()

            data = response.json()
            self.expires_at = datetime.fromisoformat(data["expires_at"])
```

### Step 2: Use in Services

```python
# app/services/todoist_service.py

class TodoistService:
    def __init__(self, credential_provider: McpManagerCredentialProvider):
        self.provider = credential_provider

    async def get_projects(self):
        creds = await self.provider.get_credentials('todoist')
        if not creds:
            raise Exception("Todoist not configured")

        async with httpx.AsyncClient() as client:
            response = await client.get(
                "https://api.todoist.com/rest/v2/projects",
                headers={"Authorization": f"Bearer {creds['access_token']}"}
            )
            return response.json()
```

### Step 3: Main Application Bootstrap

```python
# main.py

from app.providers.mcp_manager_provider import McpManagerCredentialProvider

@app.on_event("startup")
async def startup():
    # Initialize provider
    provider = McpManagerCredentialProvider()

    # Bootstrap with required services
    await provider.bootstrap([
        'todoist', 'jira', 'notion', 'openai', 'sentry'
    ])

    # Store in app state
    app.state.credential_provider = provider

    # Schedule auto-refresh task (every 50 minutes)
    asyncio.create_task(auto_refresh_loop(provider))

async def auto_refresh_loop(provider: McpManagerCredentialProvider):
    while True:
        await asyncio.sleep(3000)  # 50 minutes
        try:
            await provider.refresh()
        except Exception as e:
            logger.error(f"Failed to refresh lease: {e}")
```

---

## Testing

### Run Tests

```bash
php artisan test --filter=Mcp
```

### Test Coverage

The test suite covers:

**Authentication** (11 tests):
- Valid token authentication
- Invalid/expired/inactive tokens
- Token usage tracking
- Audit logging

**Credential Leases** (19 tests):
- Personal credentials
- Organization credentials
- Mixed credentials
- Lease renewal
- Lease revocation
- Credential resolution priority
- Authorization checks

---

## Security Considerations

### Token Security

1. **Never commit tokens** to version control
2. **Rotate tokens** regularly (every 90 days recommended)
3. **Limit token scopes** to minimum required permissions
4. **Monitor token usage** via activity logs

### Credential Encryption

- All `access_token` fields are encrypted at rest
- Credentials in leases are also encrypted
- Use HTTPS for all API requests in production

### Audit Trail

Every operation is logged to `user_activity_logs`:
- `mcp_auth_success` / `mcp_auth_failed`
- `lease_created`
- `lease_renewed`
- `lease_revoked`

---

## Troubleshooting

### "Unauthenticated" Error

- Check token format: must include `Bearer ` prefix
- Verify token hasn't expired
- Ensure token is active in database

### "No credentials available"

- Check user has integrations configured
- Verify organization membership for org credentials
- Confirm integration status is `ACTIVE`

### Lease Renewal Fails

- Check if lease is expired or revoked
- Verify renewal count < max_renewals
- Ensure lease is marked as `renewable`

---

## Development Data

After running the seeder, you have:

**Users:**
- `test@mcp-manager.local` (password: `password`)
- `mcp-server@system.local` (system user with token)

**Organizations:**
- **Development Team** - JIRA + Notion (shared with all members)
- **Client Projects** - Sentry (admins only)

**Personal Credentials:**
- Todoist
- OpenAI
- Gmail

**Active Leases:**
- 3 pre-created leases for testing
- 1 expiring in 5 minutes (for testing alerts)

---

## Next Steps

1. ✅ Complete Phase 4 on Python server
2. ✅ Implement `McpManagerCredentialProvider`
3. ✅ Refactor services to use credential provider
4. ✅ Remove hardcoded `.env` credentials
5. ✅ Test end-to-end integration
6. ✅ Deploy to staging/production

---

**Generated:** 2025-11-08
**Version:** 1.0.0