# MCP Authentication Architecture

Ce document décrit l'architecture complète d'authentification du système MCP Manager, incluant les différents flows et contrôleurs.

## Vue d'ensemble

Le système MCP Manager utilise **deux systèmes d'authentification distincts** pour deux cas d'usage différents :

1. **Authentification utilisateurs Manager** (Laravel Breeze) - Pour les utilisateurs humains de l'interface web
2. **Authentification API MCP** (Bearer Token) - Pour la communication entre Manager et Serveur MCP

## Architecture globale

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          MCP MANAGER (Laravel)                           │
│                                                                           │
│  ┌────────────────────────────────────────────────────────────────────┐ │
│  │ Frontend (React + Inertia)                                          │ │
│  │ - Authentification utilisateurs (email/password)                    │ │
│  │ - Interface utilisateur (Dashboard, Settings, etc.)                 │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                 │                                          │
│                                 ▼                                          │
│  ┌────────────────────────────────────────────────────────────────────┐ │
│  │ Auth Controllers (app/Http/Controllers/Auth/)                       │ │
│  │ - Laravel Breeze standard controllers                               │ │
│  │ - Gestion sessions utilisateurs                                     │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                 │                                          │
│                                 ▼                                          │
│  ┌────────────────────────────────────────────────────────────────────┐ │
│  │ Session Laravel (auth:web middleware)                               │ │
│  │ - Cookie de session                                                 │ │
│  │ - Utilisateur authentifié disponible via auth()->user()            │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                           │
│       Deux chemins possibles à partir d'ici :                            │
│                                                                           │
│  ┌─────────────────────────────┐   ┌──────────────────────────────────┐ │
│  │ CHEMIN A: Proxy vers MCP    │   │ CHEMIN B: API pour MCP Server    │ │
│  │ (Manager → MCP Server)      │   │ (MCP Server → Manager)           │ │
│  └─────────────────────────────┘   └──────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

## CHEMIN A : Proxy Manager → MCP Server

**Utilisation** : Interface utilisateur Manager qui communique avec le serveur Python MCP

**Contrôleur** : `App\Http\Controllers\Mcp\McpProxyController`

**Routes** :
```php
POST   /api/mcp/auth/login       // Login vers serveur Python
GET    /api/mcp/auth/me          // User info depuis serveur Python
GET    /api/mcp/todoist/tasks/today
GET    /api/mcp/todoist/tasks/upcoming
ANY    /api/mcp/todoist/{path}
```

**Flow d'authentification** :

```
┌──────────────┐   username/     ┌─────────────────┐   username/     ┌──────────────┐
│   Frontend   │   password      │ McpProxyController│   password     │ MCP Server   │
│   (React)    │ ──────────────► │   (Laravel)     │ ──────────────► │   (Python)   │
└──────────────┘                 └─────────────────┘                 └──────────────┘
       │                                   │                                 │
       │                                   │        { access_token }         │
       │        { access_token }           │ ◄───────────────────────────── │
       │ ◄──────────────────────────────── │                                 │
       │                                                                      │
       │  Authorization: Bearer {token}    ┌─────────────────┐              │
       │ ─────────────────────────────────►│ McpProxyController│────────────►│
       │                                   │   (forward req) │              │
       │                                   └─────────────────┘              │
```

**Caractéristiques** :
- Utilisé par le frontend React
- Proxie les requêtes vers le serveur Python MCP
- Gère les erreurs et logs
- Configuration : `config('services.mcp.server_url')`

## CHEMIN B : API pour MCP Server → Manager

**Utilisation** : Serveur Python MCP qui accède aux credentials stockés dans Manager

**Contrôleurs** :
- `App\Http\Controllers\Api\Mcp\GetAuthenticatedUserController`
- `App\Http\Controllers\Api\Mcp\CreateCredentialLeaseController`
- `App\Http\Controllers\Api\Mcp\GetUserCredentialsController`
- etc.

**Middleware** : `App\Http\Middleware\ValidateMcpServerToken` (alias : `mcp.token`)

**Routes** :
```php
GET    /api/mcp/me                           // User info (avec Bearer token Manager)
POST   /api/mcp/credentials/lease            // Créer un lease
GET    /api/mcp/credentials/lease/{id}       // Détails d'un lease
POST   /api/mcp/credentials/lease/{id}/renew // Renouveler
DELETE /api/mcp/credentials/lease/{id}       // Révoquer
GET    /api/mcp/users/{userId}/credentials   // Liste credentials disponibles
```

**Flow d'authentification** :

```
┌──────────────┐  username +      ┌─────────────────────┐
│ MCP Server   │  UserToken       │ ValidateMcpServer   │
│  (Python)    │ ────────────────►│ TokenMiddleware     │
└──────────────┘  (env vars)      │ (Laravel)           │
       │                           └─────────────────────┘
       │                                     │
       │                                     ▼
       │                           Validation du UserToken
       │                           dans table `user_tokens`
       │                                     │
       │                                     ▼
       │                           Attache User au Request
       │                           ($request->user())
       │                                     │
       │                                     ▼
       │                           ┌─────────────────────┐
       │        user_info +        │  Controller API     │
       │        organizations      │  (GetAuthenticatedUserController)
       │ ◄──────────────────────── │                     │
       │                           └─────────────────────┘
```

**Variables d'environnement MCP Server** (exemple pour Claude Code) :
```json
{
  "env": {
    "MCP_API_URL": "http://localhost:3978",
    "MCP_USERNAME": "admin@agentops.be",
    "MCP_TOKEN": "OTY3Y2ViNm..."
  }
}
```

**Caractéristiques** :
- Authentification via Bearer token (`UserToken` dans Manager)
- Validation et tracking dans middleware
- Logging des accès (audit trail)
- Retourne user + organizations + permissions

## Système de Credential Lease

Le système de Credential Lease permet au serveur MCP d'accéder temporairement aux credentials des utilisateurs.

**Architecture** :

```
┌──────────────┐                    ┌─────────────────────┐
│ MCP Server   │  GET /api/mcp/me   │ Manager             │
│  (Python)    │ ─────────────────► │ (Laravel)           │
└──────────────┘  Bearer: UserToken └─────────────────────┘
       │                                     │
       │  { user_id: 1, organizations: [...] }
       │ ◄────────────────────────────────── │
       │                                      │
       │  POST /api/mcp/credentials/lease    │
       │  { services: ['notion', 'github'] } │
       │ ─────────────────────────────────► │
       │                                     │
       │                         Résolution credentials :
       │                         1. Personal (priority)
       │                         2. Organization (fallback)
       │                                     │
       │  { lease_id, expires_at, ttl }     │
       │ ◄────────────────────────────────── │
       │                                      │
       │  GET /api/mcp/credentials/lease/123 │
       │ ─────────────────────────────────► │
       │                                     │
       │  { credentials: {...encrypted...} } │
       │ ◄────────────────────────────────── │
       │                                      │
       │  POST /api/mcp/credentials/lease/123/renew
       │ ─────────────────────────────────► │
```

**Sécurité** :
- Credentials encryptés avec `APP_KEY` de Laravel
- TTL par défaut : 1 heure
- Renouvellement automatique possible
- Révocation manuelle disponible
- Audit logging de tous les accès

## Système d'organisations

Les utilisateurs peuvent appartenir à des organisations et partager des credentials :

**Rôles** :
- `Owner` : Créateur de l'organisation, tous les droits
- `Admin` : Gestion des membres et credentials
- `Member` : Accès aux credentials partagés
- `Guest` : Accès lecture seule limité

**Permissions des credentials** :
- `all_members` : Tous les membres de l'org
- `admins_only` : Seulement Owner + Admin
- `user:123` : Utilisateur spécifique

**Résolution de credentials** (ordre de priorité) :
1. **Personal credential** : Credential personnel de l'utilisateur
2. **Organization credential** : Credential partagé dans l'organisation

## Résumé des fichiers clés

### Authentification web (Laravel Breeze)
- `app/Http/Controllers/Auth/*` - Contrôleurs d'authentification standard
- Routes dans `routes/web.php` sous `Route::middleware('guest')`

### Proxy Manager → MCP Server
- `app/Http/Controllers/Mcp/McpProxyController.php` - **NOUVEAU** emplacement
- Routes dans `routes/web.php` sous `Route::prefix('api/mcp')`

### API MCP Server → Manager
- `app/Http/Controllers/Api/Mcp/*` - Controllers API single-action
- `app/Http/Middleware/ValidateMcpServerToken.php` - Middleware d'authentification
- `app/Services/CredentialResolutionService.php` - Logique de résolution
- Routes dans `routes/api.php` sous `Route::prefix('mcp')->middleware(['mcp.token'])`

### Models
- `app/Models/User.php`
- `app/Models/UserToken.php` - Tokens pour authentification MCP Server
- `app/Models/Organization.php`
- `app/Models/OrganizationMember.php`
- `app/Models/CredentialLease.php`
- `app/Models/IntegrationAccount.php`

### Migrations
- `2025_11_04_185610_create_organizations_table.php`
- `2025_11_04_185633_create_organization_members_table.php`
- `2025_11_04_185815_add_organization_support_to_integration_accounts.php`
- `2025_11_04_185900_create_credential_leases.php`

## Différences clés entre les deux chemins

| Aspect | Proxy (A) | API (B) |
|--------|-----------|---------|
| **Direction** | Manager → MCP Server | MCP Server → Manager |
| **Authentification** | Username/Password vers Python | Bearer Token (UserToken) |
| **Middleware** | `auth:web` (sessions) | `mcp.token` (Bearer) |
| **Contrôleur** | `Mcp\McpProxyController` | `Api\Mcp\*Controller` |
| **But** | Accéder aux APIs du serveur Python | Accéder aux credentials dans Manager |
| **Utilisateur** | Humain (frontend) | Serveur (Python MCP) |
| **Routes** | `/api/mcp/auth/login`, `/todoist/*` | `/api/mcp/me`, `/credentials/*` |

## Tests

```bash
# Tester le proxy (Manager → MCP Server)
curl -X POST http://localhost:3978/api/mcp/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "user@example.com", "password": "secret"}'

# Tester l'API (MCP Server → Manager)
curl http://localhost:3978/api/mcp/me \
  -H "Authorization: Bearer OTY3Y2ViNm..."

# Créer un credential lease
curl -X POST http://localhost:3978/api/mcp/credentials/lease \
  -H "Authorization: Bearer OTY3Y2ViNm..." \
  -H "Content-Type: application/json" \
  -d '{"services": ["notion", "github"], "ttl": 3600}'
```

## Schéma complet

```
┌────────────────────────────────────────────────────────────────────────┐
│                         UTILISATEUR HUMAIN                              │
└────────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌────────────────────────────────────────────────────────────────────────┐
│                    FRONTEND MANAGER (React)                             │
│                                                                          │
│  Login : email + password                                               │
│  Dashboard, Settings, Integrations UI                                   │
└────────────────────────────────────────────────────────────────────────┘
          │                                    │
          │ Email/Password                     │ Bearer Token (vers Python)
          ▼                                    ▼
┌──────────────────────────────┐   ┌────────────────────────────────────┐
│ Auth Controllers             │   │ Mcp\McpProxyController             │
│ (Laravel Breeze)             │   │ (Proxy vers MCP Server)            │
│                              │   │                                    │
│ - Login                      │   │ POST /api/mcp/auth/login           │
│ - Register                   │   │ GET  /api/mcp/auth/me              │
│ - Password Reset             │   │ GET  /api/mcp/todoist/*            │
└──────────────────────────────┘   └────────────────────────────────────┘
          │                                    │
          │ Session Cookie                     │ HTTP Proxy
          ▼                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                      BACKEND MANAGER (Laravel)                          │
│                                                                          │
│  - User Management                                                      │
│  - Organization Management                                              │
│  - Integration Accounts Storage                                         │
│  - Credential Lease System                                              │
└────────────────────────────────────────────────────────────────────────┘
                                  ▲
                                  │ Bearer Token (UserToken)
                                  │
┌────────────────────────────────────────────────────────────────────────┐
│              API MCP (MCP Server → Manager)                             │
│                                                                          │
│  ValidateMcpServerToken Middleware                                      │
│  ↓                                                                       │
│  Api\Mcp Controllers:                                                   │
│  - GetAuthenticatedUserController      (GET /api/mcp/me)               │
│  - CreateCredentialLeaseController     (POST /credentials/lease)        │
│  - RenewCredentialLeaseController      (POST /lease/{id}/renew)        │
│  - RevokeCredentialLeaseController     (DELETE /lease/{id})            │
│  - GetUserCredentialsController        (GET /users/{id}/credentials)   │
└────────────────────────────────────────────────────────────────────────┘
                                  ▲
                                  │ HTTP Requests
                                  │ (Bearer: UserToken)
                                  │
┌────────────────────────────────────────────────────────────────────────┐
│                       MCP SERVER (Python)                               │
│                                                                          │
│  FastAPI application                                                    │
│  Environment variables:                                                 │
│    - MCP_API_URL=http://localhost:3978                                 │
│    - MCP_USERNAME=admin@agentops.be                                    │
│    - MCP_TOKEN=OTY3Y2ViNm...                                           │
│                                                                          │
│  Workflow:                                                              │
│  1. Authenticate with Manager (Bearer token from env)                  │
│  2. Get user info and organizations                                     │
│  3. Request credential lease for needed services                        │
│  4. Use credentials to access external APIs                             │
│  5. Renew lease before expiration                                       │
└────────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌────────────────────────────────────────────────────────────────────────┐
│                    EXTERNAL SERVICES                                    │
│                                                                          │
│  - Notion API                                                           │
│  - GitHub API                                                           │
│  - Todoist API                                                          │
│  - etc.                                                                 │
└────────────────────────────────────────────────────────────────────────┘
```

## Conclusion

Cette architecture à deux voies permet de :
1. **Gérer les utilisateurs humains** via Laravel Breeze (sessions web)
2. **Gérer les accès programmatiques** via Bearer tokens (API)
3. **Sécuriser les credentials** avec encryption et TTL
4. **Tracer tous les accès** via audit logging
5. **Supporter les organisations** avec partage de credentials

Les deux systèmes sont indépendants mais complémentaires, permettant une séparation claire des responsabilités.
