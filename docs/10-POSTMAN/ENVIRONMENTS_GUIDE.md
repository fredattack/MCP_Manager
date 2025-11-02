# Guide des Environnements Postman

## 📦 Environnements disponibles

Tu as maintenant **3 environnements** configurés pour tester l'API à différents stades :

| Environnement | Fichier | Usage |
|---------------|---------|-------|
| **Local** | `postman_environment.json` | Développement local |
| **Staging** | `postman_environment_staging.json` | Tests pré-production |
| **Production** | `postman_environment_production.json` | Production (avec précaution) |

## 🚀 Configuration rapide

### 1. Importer tous les environnements

Dans Postman :

1. Cliquer sur **Environments** (icône d'engrenage en haut à droite)
2. Cliquer sur **Import**
3. Sélectionner les 3 fichiers :
   - `postman_environment.json` (Local)
   - `postman_environment_staging.json` (Staging)
   - `postman_environment_production.json` (Production)

### 2. Sélectionner un environnement

En haut à droite de Postman, dans le menu déroulant :

- Choisir **"MCP Manager - Local"** pour le développement
- Choisir **"MCP Manager - Staging"** pour les tests
- Choisir **"MCP Manager - Production"** pour la prod

## 🔧 Configuration de chaque environnement

### Local (Développement)

**URL de base :** `http://localhost:3978`

**Configuration initiale :**

```json
{
  "base_url": "http://localhost:3978",
  "mcp_server_url": "http://localhost:8000",
  "user_email": "test@example.com",
  "user_password": "password",
  "environment": "local"
}
```

**Credentials par défaut :**
- Email : `test@example.com`
- Password : `password`

**À faire après import :**
1. ✅ Aucune modification nécessaire
2. ✅ Utiliser directement

### Staging (Pré-production)

**URL de base :** `https://staging.mcp-manager.com`

**Configuration requise :**

Tu dois configurer manuellement :

```
base_url = https://staging.mcp-manager.com
mcp_server_url = https://staging-mcp.mcp-manager.com
user_email = [ton email de test staging]
user_password = [ton password staging]
```

**À faire après import :**

1. Éditer l'environnement dans Postman
2. Remplir `user_email` et `user_password`
3. Vérifier les URLs de base
4. Sauvegarder

### Production (Avec précaution!)

**URL de base :** `https://api.mcp-manager.com`

⚠️ **ATTENTION : Utiliser la production avec précaution!**

**Configuration requise :**

```
base_url = https://api.mcp-manager.com
mcp_server_url = https://mcp.mcp-manager.com
user_email = [email production - NE PAS COMMITTER]
user_password = [password production - NE PAS COMMITTER]
admin_email = [email admin - À configurer LOCALEMENT]
admin_password = [password admin - À configurer LOCALEMENT]
```

**À faire après import :**

1. ⚠️ **NE JAMAIS committer cet environnement avec des credentials**
2. Configurer les variables **uniquement dans Postman localement**
3. Utiliser des comptes de test dédiés si possible
4. Documenter les actions effectuées en production

## 📊 Variables disponibles dans tous les environnements

### Variables d'URL

| Variable | Description | Type |
|----------|-------------|------|
| `base_url` | URL de base de l'API | default |
| `mcp_server_url` | URL du serveur MCP | default |

### Variables d'authentification

| Variable | Description | Type |
|----------|-------------|------|
| `api_token` | Token Bearer (auto-rempli après login) | secret |
| `user_email` | Email de l'utilisateur | default |
| `user_password` | Password de l'utilisateur | secret |

### Variables de ressources

| Variable | Description | Type |
|----------|-------------|------|
| `user_id` | ID utilisateur (auto-rempli) | default |
| `workflow_id` | ID d'un workflow | default |
| `repository_id` | ID d'un repository Git | default |
| `integration_id` | ID d'une intégration | default |

### Variables d'intégrations

| Variable | Description | Type |
|----------|-------------|------|
| `github_client_id` | GitHub OAuth Client ID | default |
| `gitlab_client_id` | GitLab OAuth Client ID | default |
| `notion_token` | Token Notion | secret |
| `jira_site_url` | URL du site Jira | default |

### Variables système

| Variable | Description | Type |
|----------|-------------|------|
| `environment` | Nom de l'environnement | default |
| `timeout_ms` | Timeout des requêtes | default |

## 🎯 Utilisation des variables

### Dans les URLs

```
{{base_url}}/api/postman/auth/login
{{mcp_server_url}}/health
```

### Dans les body JSON

```json
{
  "email": "{{user_email}}",
  "password": "{{user_password}}"
}
```

### Dans les headers

```
Authorization: Bearer {{api_token}}
```

### Dans les scripts

```javascript
// Sauvegarder une variable
pm.environment.set('user_id', jsonData.user.id);

// Lire une variable
const baseUrl = pm.environment.get('base_url');

// Vérifier l'environnement
if (pm.environment.get('environment') === 'production') {
    console.warn('⚠️ Running in PRODUCTION');
}
```

## 🔐 Sécurité des environnements

### ✅ Bonnes pratiques

1. **Ne JAMAIS committer les credentials**
   - Les fichiers d'environnement ont des valeurs vides par défaut
   - Configurer les credentials **uniquement dans Postman localement**

2. **Utiliser les types "secret"**
   - Tous les passwords, tokens, credentials sont marqués comme `secret`
   - Postman masque ces valeurs dans l'interface

3. **Séparer les environnements**
   - Utiliser des comptes différents pour chaque environnement
   - Ne pas utiliser les mêmes credentials en local et en production

4. **Documenter les tests en production**
   - Noter ce qui a été testé
   - Utiliser des données de test si possible
   - Avoir l'autorisation avant de tester

### ⚠️ Variables sensibles

Ces variables ne doivent **JAMAIS** être committées avec des valeurs :

- `api_token`
- `user_password`
- `notion_token`
- `admin_password`

### 🔒 Configurer les credentials en toute sécurité

**Méthode recommandée :**

1. Importer les environnements (valeurs vides)
2. Dans Postman, éditer l'environnement
3. Remplir les variables `secret` **localement**
4. Ces valeurs restent **uniquement dans Postman sur ta machine**
5. Ne jamais exporter avec les credentials

## 📋 Checklist de configuration

### Pour Local

- [ ] Environnement importé
- [ ] Serveur Laravel lancé : `php artisan serve --port=3978`
- [ ] MCP Server lancé (si nécessaire)
- [ ] Test : `GET {{base_url}}/api/postman/health`

### Pour Staging

- [ ] Environnement importé
- [ ] URLs de staging configurées
- [ ] Credentials de test staging remplis
- [ ] Test : `GET {{base_url}}/api/postman/health`
- [ ] Login testé
- [ ] Accès vérifié

### Pour Production

- [ ] Environnement importé
- [ ] ⚠️ Autorisation obtenue pour tester en production
- [ ] URLs de production configurées
- [ ] Credentials de test remplis **localement uniquement**
- [ ] Test : `GET {{base_url}}/api/postman/health`
- [ ] ⚠️ Utiliser avec précaution

## 🚦 Workflow recommandé

### Développement d'une nouvelle feature

```
Local → Staging → Production
```

1. **Local** : Développer et tester la feature
2. **Staging** : Valider avec des données de staging
3. **Production** : Déployer et vérifier

### Débugger un problème

```
Production → Staging → Local
```

1. **Production** : Identifier le problème
2. **Staging** : Reproduire en staging
3. **Local** : Débugger et fixer

## 🎨 Personnalisation

### Ajouter une variable custom

1. Éditer l'environnement dans Postman
2. Cliquer sur "Add"
3. Remplir :
   - `Variable` : nom de la variable (ex: `custom_api_key`)
   - `Initial Value` : valeur par défaut
   - `Current Value` : valeur actuelle
   - `Type` : `default` ou `secret`

### Dupliquer un environnement

Pour créer un environnement "Dev" basé sur "Local" :

1. Dans Postman → Environments
2. Clic droit sur "MCP Manager - Local"
3. **Duplicate**
4. Renommer en "MCP Manager - Dev"
5. Modifier les URLs si nécessaire

## 📝 Variables auto-remplies

Certaines variables sont automatiquement remplies par les scripts de test :

| Variable | Remplie par | Quand |
|----------|-------------|-------|
| `user_id` | Script de login | Après login réussi |
| `api_token` | Script de login | Après login réussi (si applicable) |

**Exemple de script (dans l'onglet Tests du login) :**

```javascript
// Auto-save user ID
var jsonData = pm.response.json();
if (jsonData.user && jsonData.user.id) {
    pm.environment.set('user_id', jsonData.user.id);
    console.log('✅ User ID saved:', jsonData.user.id);
}
```

## 🎯 Tips avancés

### 1. Basculer rapidement entre environnements

Utiliser le raccourci : `Ctrl/Cmd + Alt + E`

### 2. Vérifier l'environnement actif

Ajouter ce script dans le Pre-request de la collection :

```javascript
const env = pm.environment.get('environment');
console.log('🌍 Current environment:', env);

if (env === 'production') {
    console.warn('⚠️⚠️⚠️ PRODUCTION ENVIRONMENT ⚠️⚠️⚠️');
}
```

### 3. Variables conditionnelles

Dans les scripts de test :

```javascript
const isDev = pm.environment.get('environment') === 'local';
const timeout = isDev ? 5000 : 30000;

pm.test(`Response time < ${timeout}ms`, function () {
    pm.expect(pm.response.responseTime).to.be.below(timeout);
});
```

### 4. Log des variables

Débugger les variables facilement :

```javascript
console.log('📊 All environment variables:', pm.environment.toObject());
```

## 🔄 Synchronisation

### Partager les environnements avec l'équipe

**Option 1 : Via Git (sans credentials)**

Les fichiers JSON dans `docs/10-POSTMAN/` peuvent être partagés via Git car :
- ✅ Pas de credentials hardcodés
- ✅ Valeurs vides ou placeholder
- ✅ Chacun configure ses propres credentials

**Option 2 : Via Postman Teams (payant)**

- Synchronisation automatique
- Credentials séparés par membre
- Historique des versions

## 📚 Documentation associée

- `README.md` - Guide principal
- `POSTMAN_QUICK_START.md` - Démarrage rapide
- `POSTMAN_CSRF_GUIDE.md` - Gestion du CSRF
- `POSTMAN_COLLECTION_README.md` - Documentation de la collection

---

**Note** : Les environnements sont conçus pour être sécurisés par défaut. Les credentials ne sont jamais committés et doivent être configurés localement dans Postman.