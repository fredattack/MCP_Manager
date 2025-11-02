# MCP Manager - Collection Postman

Cette collection Postman complète documente tous les endpoints de l'application MCP Manager.

## 📋 Table des matières

- [Installation](#installation)
- [Configuration](#configuration)
- [Structure de la collection](#structure-de-la-collection)
- [Authentification](#authentification)
- [Variables d'environnement](#variables-denvironnement)
- [Catégories d'endpoints](#catégories-dendpoints)
- [Exemples d'utilisation](#exemples-dutilisation)
- [Tests et scripts](#tests-et-scripts)

## 🚀 Installation

### Importer la collection

1. Ouvrir Postman
2. Cliquer sur **Import** (en haut à gauche)
3. Sélectionner le fichier `postman_collection.json`
4. La collection "MCP Manager API Collection" apparaîtra dans votre workspace

### Importer l'environnement

1. Cliquer sur **Import**
2. Sélectionner le fichier `postman_environment.json`
3. L'environnement "MCP Manager - Local" sera créé
4. Sélectionner cet environnement dans le menu déroulant en haut à droite

## ⚙️ Configuration

### Variables d'environnement

| Variable | Description | Valeur par défaut | Type |
|----------|-------------|-------------------|------|
| `base_url` | URL de base de l'application | `http://localhost:3978` | default |
| `api_token` | Token d'authentification API | _(vide)_ | secret |
| `user_id` | ID de l'utilisateur courant | _(vide)_ | default |
| `workflow_id` | ID d'un workflow | _(vide)_ | default |
| `repository_id` | ID d'un repository Git | _(vide)_ | default |
| `integration_id` | ID d'une intégration | _(vide)_ | default |
| `mcp_server_url` | URL du serveur MCP | `http://localhost:8000` | default |

### Obtenir un token d'authentification

**Méthode 1 : Via l'interface web**
1. Se connecter à l'application web
2. Aller dans Settings → API Token
3. Copier le token et le coller dans la variable `api_token`

**Méthode 2 : Via Postman**
1. Exécuter la requête `Authentication → Login`
2. Un cookie de session sera automatiquement stocké
3. Exécuter `Authentication → Get API Token`
4. Copier le token retourné dans la variable `api_token`

## 📁 Structure de la collection

La collection est organisée en 16 catégories principales :

```
MCP Manager API Collection/
├── Authentication (7 endpoints)
├── User Profile & Settings (4 endpoints)
├── Integrations (5 endpoints)
├── Notion (4 endpoints)
├── Jira (27 endpoints)
│   ├── Projects
│   ├── Boards
│   ├── Issues
│   ├── Epics
│   └── Sprints
├── Git Integration (20 endpoints)
│   ├── OAuth
│   ├── Repositories
│   └── Clones
├── Workflows (10 endpoints)
├── MCP Server (8 endpoints)
├── MCP Monitoring (5 endpoints)
├── MCP Proxy (5 endpoints)
├── Todoist Mock (7 endpoints)
├── Google Integrations (15 endpoints)
│   ├── Gmail
│   ├── Calendar
│   └── Setup
├── AI & Natural Language (4 endpoints)
├── Daily Planning (3 endpoints)
├── Admin (12 endpoints)
└── Webhooks (2 endpoints)
```

**Total : 138+ endpoints documentés**

## 🔐 Authentification

L'application utilise deux méthodes d'authentification :

### 1. Session-based (Cookie)

Utilisé par les pages web et Inertia.js :

```http
POST /login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "remember": true
}
```

Un cookie de session sera automatiquement stocké par Postman.

### 2. Bearer Token

Utilisé pour les API calls :

```http
GET /api/workflows
Authorization: Bearer YOUR_TOKEN_HERE
```

Le token est configuré au niveau de la collection et utilise la variable `{{api_token}}`.

## 📊 Variables d'environnement

### Créer plusieurs environnements

Vous pouvez dupliquer l'environnement pour créer différents environnements :

**Local Development**
```json
{
  "base_url": "http://localhost:3978",
  "mcp_server_url": "http://localhost:8000"
}
```

**Staging**
```json
{
  "base_url": "https://staging.mcp-manager.com",
  "mcp_server_url": "https://staging-mcp.mcp-manager.com"
}
```

**Production**
```json
{
  "base_url": "https://mcp-manager.com",
  "mcp_server_url": "https://mcp.mcp-manager.com"
}
```

## 📚 Catégories d'endpoints

### 🔑 Authentication

Gestion de l'authentification utilisateur (Laravel Breeze) :

- **Register** - Créer un nouveau compte
- **Login** - Se connecter
- **Get API Token** - Obtenir le token API
- **Logout** - Se déconnecter
- **Forgot Password** - Demander un reset
- **Reset Password** - Réinitialiser le mot de passe
- **Email Verification** - Vérifier l'email

### 👤 User Profile & Settings

Gestion du profil et des paramètres :

- **Get Profile** - Récupérer les infos du profil
- **Update Profile** - Mettre à jour le profil
- **Delete Account** - Supprimer le compte
- **Update Password** - Changer le mot de passe

### 🔌 Integrations

CRUD pour gérer les intégrations avec les services externes :

- List, Create, Show, Update, Delete integrations

### 📝 Notion

Intégration avec Notion (4 endpoints) :

- Pages Tree, Databases, Page content, Blocks

### 🎯 Jira

Intégration complète avec Jira (27 endpoints) :

- **Projects** - Gestion des projets
- **Boards** - Gestion des boards
- **Issues** - CRUD complet des issues, transitions, assignations
- **Epics** - Création, progression, issues
- **Sprints** - Gestion complète des sprints, vélocité

### 🔀 Git Integration

OAuth et gestion des repositories Git (20 endpoints) :

- **OAuth** - GitHub et GitLab OAuth flow
- **Repositories** - Liste, sync, stats, refresh
- **Clones** - Cloner et gérer les repositories localement

### ⚙️ Workflows

Gestion des workflows d'automatisation (10 endpoints) :

- CRUD workflows
- Execute, Rerun, Cancel
- Execution status & steps

### 🖥️ MCP Server

Configuration du serveur MCP (8 endpoints) :

- Configuration, test, disconnect
- Status des intégrations

### 📊 MCP Monitoring

Monitoring et métriques (5 endpoints) :

- Métriques, logs, export
- Health check, streaming (SSE)

### 🔄 MCP Proxy

Proxy pour les endpoints MCP (5 endpoints) :

- Authentification MCP
- Todoist tasks via MCP

### ✅ Todoist (Mock)

Endpoints de développement Todoist (7 endpoints) :

- Projects, Tasks CRUD
- Complete/Uncomplete tasks

### 🔔 Google Integrations

Gmail et Calendar (15 endpoints) :

- **Gmail** - Messages, search, send, labels
- **Calendar** - Events CRUD, conflicts, week view
- **Setup** - OAuth flow

### 🤖 AI & Natural Language

IA et traitement du langage naturel (4 endpoints) :

- Chat avec IA
- Commandes en langage naturel
- Suggestions et historique

### 📅 Daily Planning

Planification journalière avec IA (3 endpoints) :

- Get, Generate, Update daily plan

### 👥 Admin

Administration des utilisateurs (12 endpoints) :

Nécessite rôle `admin` ou `manager` :

- CRUD utilisateurs
- Credentials, Reset password
- Lock/Unlock accounts
- Change role, Update permissions
- Activity logs

### 🔔 Webhooks

Webhooks Git (2 endpoints) :

- GitHub webhook
- GitLab webhook

## 💡 Exemples d'utilisation

### Scénario 1 : Authentification complète

1. **Register** un nouveau compte
2. **Login** avec les credentials
3. **Get API Token** pour l'utiliser dans les headers
4. Sauvegarder le token dans `{{api_token}}`

### Scénario 2 : Configuration Git + Clone

1. **Start OAuth** pour GitHub ou GitLab
2. Compléter le flow OAuth dans le navigateur
3. **Sync Repositories** pour récupérer les repos
4. **Clone Repository** d'un repo spécifique

### Scénario 3 : Créer et exécuter un workflow

1. **Create Workflow** avec les étapes souhaitées
2. Sauvegarder l'ID dans `{{workflow_id}}`
3. **Execute Workflow** avec des paramètres
4. **Get Execution Status** pour suivre la progression
5. **Get Execution Steps** pour voir les détails

### Scénario 4 : Gestion Jira complète

1. **List Projects** pour voir les projets disponibles
2. **Search Issues** avec JQL
3. **Create Issue** pour créer une nouvelle tâche
4. **Get Transitions** pour voir les statuts possibles
5. **Transition Issue** pour changer le statut

### Scénario 5 : Monitoring MCP

1. **Get Metrics** pour voir les performances
2. **Get Logs** avec filtres (level, limit)
3. **Health Check** pour vérifier l'état
4. **Get Stream** (SSE) pour le monitoring en temps réel

## 🧪 Tests et scripts

### Ajouter des tests automatiques

Vous pouvez ajouter des tests dans l'onglet "Tests" de chaque requête :

```javascript
// Test 1: Status code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test 2: Response time
pm.test("Response time is less than 500ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});

// Test 3: JSON response
pm.test("Response has data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});

// Test 4: Save variable
pm.test("Save workflow ID", function () {
    var jsonData = pm.response.json();
    pm.environment.set("workflow_id", jsonData.data.id);
});
```

### Scripts de pré-requête

Pour ajouter des headers dynamiques :

```javascript
// Ajouter un timestamp
pm.environment.set("timestamp", new Date().toISOString());

// Générer un UUID
const uuid = require('uuid');
pm.environment.set("request_id", uuid.v4());

// Vérifier le token
if (!pm.environment.get("api_token")) {
    console.warn("⚠️ API token is not set!");
}
```

## 🎯 Bonnes pratiques

### 1. Utiliser les variables

Au lieu de hardcoder les IDs :

❌ **Mauvais**
```
GET http://localhost:3978/api/workflows/123
```

✅ **Bon**
```
GET {{base_url}}/api/workflows/{{workflow_id}}
```

### 2. Organiser les requêtes en folders

Les requêtes sont déjà organisées par catégorie. Vous pouvez créer vos propres dossiers pour vos scénarios :

```
Mes Scénarios/
├── Setup complet
│   ├── 1. Register
│   ├── 2. Configure Git
│   └── 3. Create Workflow
└── Tests E2E
    ├── Test Jira Integration
    └── Test Workflow Execution
```

### 3. Exécuter des collections

Vous pouvez exécuter toute une catégorie ou la collection complète :

1. Clic droit sur un dossier → **Run folder**
2. Sélectionner l'environnement
3. Configurer les itérations et délais
4. Voir les résultats des tests

### 4. Exporter les résultats

Après avoir exécuté une collection :

1. Cliquer sur **Export Results**
2. Choisir le format (JSON, HTML)
3. Partager avec l'équipe

## 🔧 Troubleshooting

### Le token expire

Si vous recevez une erreur 401 :

1. Exécuter `Authentication → Login`
2. Exécuter `Authentication → Get API Token`
3. Mettre à jour `{{api_token}}`

### CORS errors

Si vous testez depuis le navigateur avec Postman Web :

1. Utiliser Postman Desktop
2. Ou désactiver temporairement CORS dans Laravel (dev uniquement)

### Variables non définies

Vérifier que :

1. L'environnement correct est sélectionné
2. Les variables sont bien définies dans l'environnement
3. Les scripts de sauvegarde automatique fonctionnent

## 📖 Documentation supplémentaire

- [Documentation Laravel](https://laravel.com/docs)
- [Documentation Inertia.js](https://inertiajs.com)
- [Documentation Postman](https://learning.postman.com)
- [API Jira](https://developer.atlassian.com/cloud/jira/platform/rest/v3)
- [API Notion](https://developers.notion.com)
- [API GitHub](https://docs.github.com/en/rest)
- [API GitLab](https://docs.gitlab.com/ee/api/)

## 🤝 Contribution

Pour ajouter de nouveaux endpoints à la collection :

1. Créer la requête dans Postman
2. Ajouter la documentation dans la description
3. Ajouter des exemples de body
4. Ajouter des tests si possible
5. Exporter la collection mise à jour
6. Créer une PR avec le fichier JSON

## 📝 Notes

- Cette collection couvre **138+ endpoints**
- Tous les endpoints sont documentés avec exemples
- Les variables facilitent le passage entre environnements
- Les middlewares d'authentification sont respectés
- Les permissions (admin, manager, user) sont indiquées

## 🎉 Démarrage rapide

**En 5 minutes :**

1. Importer `postman_collection.json` et `postman_environment.json`
2. Sélectionner l'environnement "MCP Manager - Local"
3. Exécuter `Authentication → Login`
4. Exécuter `Authentication → Get API Token`
5. Copier le token dans `{{api_token}}`
6. Tester n'importe quel endpoint protégé !

---

**Créé par :** MCP Manager Team
**Dernière mise à jour :** 2025-01-01
**Version de l'API :** 1.0.0
**Version Laravel :** 12.x