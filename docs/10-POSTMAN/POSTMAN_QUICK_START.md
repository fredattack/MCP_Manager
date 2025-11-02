# Postman Quick Start Guide

## 🚀 Solution Simple : Routes API Sans CSRF

J'ai créé des routes spéciales pour Postman qui **n'ont PAS besoin de token CSRF**.

### ✨ Nouveaux Endpoints Postman

Tous ces endpoints sont préfixés par `/api/postman/` et fonctionnent **sans CSRF** :

```
✅ POST   /api/postman/auth/register      - Créer un compte
✅ POST   /api/postman/auth/login         - Se connecter
✅ GET    /api/postman/auth/user          - Utilisateur actuel
✅ POST   /api/postman/auth/logout        - Se déconnecter
✅ GET    /api/postman/health             - Health check
✅ GET    /api/postman/csrf-token         - Obtenir CSRF (si besoin)
✅ GET    /api/postman/test/ping          - Test rapide (auth requis)
✅ GET    /api/postman/test/integrations  - Tes intégrations
```

## 📝 Configuration Postman

### 1. Créer une nouvelle requête

**URL :** `http://localhost:3978/api/postman/health`
**Method :** GET
**Headers :** Aucun nécessaire

Cliquer sur **Send** → Tu devrais voir :

```json
{
  "status": "ok",
  "app": "Mcp manager",
  "environment": "local",
  "timestamp": "2025-01-01T12:00:00+00:00",
  "php_version": "8.4.1",
  "laravel_version": "12.x"
}
```

✅ Si ça fonctionne, ton API est prête !

### 2. S'authentifier

**URL :** `http://localhost:3978/api/postman/auth/login`
**Method :** POST
**Headers :**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON) :**
```json
{
  "email": "test@example.com",
  "password": "password",
  "remember": true
}
```

Cliquer sur **Send** → Tu devrais voir :

```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  },
  "note": "Session cookie has been set. Use this cookie for all subsequent requests."
}
```

✅ **Important :** Postman stocke automatiquement le cookie de session. Tu n'as rien à faire!

### 3. Tester l'authentification

**URL :** `http://localhost:3978/api/postman/test/ping`
**Method :** GET

Cliquer sur **Send** → Tu devrais voir :

```json
{
  "message": "pong",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  },
  "timestamp": "2025-01-01T12:00:00+00:00"
}
```

✅ Si ça fonctionne, tu es authentifié !

## 📦 Utiliser les autres endpoints

Maintenant que tu es authentifié, tu peux utiliser **tous les autres endpoints** de l'application :

### Exemples

#### Lister les intégrations
```
GET http://localhost:3978/api/integrations
```

#### Créer un workflow
```
POST http://localhost:3978/api/workflows
Content-Type: application/json

{
  "name": "Mon workflow",
  "description": "Test workflow"
}
```

#### Lister les projects Jira
```
GET http://localhost:3978/api/jira/projects
```

#### Obtenir les pages Notion
```
GET http://localhost:3978/api/notion/pages-tree
```

## 🔑 Endpoints d'authentification complets

### Register un nouveau compte

```http
POST http://localhost:3978/api/postman/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Réponse :**
```json
{
  "message": "User registered and logged in successfully",
  "user": {
    "id": 2,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-01-01T12:00:00.000000Z"
  },
  "note": "Session cookie has been set..."
}
```

### Login

```http
POST http://localhost:3978/api/postman/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123",
  "remember": true
}
```

### Get Current User

```http
GET http://localhost:3978/api/postman/auth/user
```

**Réponse si connecté :**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    ...
  },
  "authenticated": true
}
```

**Réponse si non connecté :**
```json
{
  "message": "Not authenticated",
  "user": null
}
```

### Logout

```http
POST http://localhost:3978/api/postman/auth/logout
```

## 🎯 Workflow complet

### Scénario : Créer un compte et tester Jira

1. **Health Check**
   ```
   GET /api/postman/health
   ```

2. **Register**
   ```
   POST /api/postman/auth/register
   Body: { name, email, password, password_confirmation }
   ```

3. **Get User**
   ```
   GET /api/postman/auth/user
   → Vérifie que tu es bien connecté
   ```

4. **Test Ping**
   ```
   GET /api/postman/test/ping
   → Vérifie que l'auth fonctionne
   ```

5. **List Jira Projects**
   ```
   GET /api/jira/projects
   ```

6. **Create Jira Issue**
   ```
   POST /api/jira/issues
   Body: { project: { key: "PROJ" }, summary: "Test", ... }
   ```

7. **Logout** (quand tu as fini)
   ```
   POST /api/postman/auth/logout
   ```

## 🐛 Troubleshooting

### Erreur "Not authenticated"

**Problème :** Le cookie de session n'est pas envoyé.

**Solution :**
1. Vérifier que Postman est configuré pour gérer les cookies automatiquement
2. Aller dans Postman → Settings → Cookies → Activer "Automatically follow redirects"
3. Re-faire le login : `POST /api/postman/auth/login`

### Erreur "CSRF token mismatch"

**Problème :** Tu utilises une route `/web` au lieu d'une route `/api`.

**Solution :**
1. ✅ Utiliser `/api/postman/auth/login` au lieu de `/login`
2. ✅ Utiliser les routes `/api/*` pour tout le reste
3. ❌ Éviter les routes web comme `/login`, `/register` dans Postman

### Comment voir les cookies dans Postman

1. Cliquer sur **Cookies** en bas de la requête
2. Tu devrais voir `laravel_session` et `XSRF-TOKEN`
3. Si tu ne les vois pas, vérifie que tu as bien appelé une route avec le middleware `web`

### Le serveur Laravel ne démarre pas

```bash
# Vérifier que le serveur tourne
php artisan serve --port=3978

# Si erreur, vérifier les logs
tail -f storage/logs/laravel.log
```

## 📋 Variables Postman recommandées

Créer ces variables d'environnement :

| Variable | Valeur |
|----------|--------|
| `base_url` | `http://localhost:3978` |
| `user_email` | `test@example.com` |
| `user_password` | `password` |

Puis dans les requêtes :

```
POST {{base_url}}/api/postman/auth/login

{
  "email": "{{user_email}}",
  "password": "{{user_password}}"
}
```

## ✅ Checklist de démarrage

- [ ] Le serveur Laravel est démarré : `php artisan serve --port=3978`
- [ ] Health check fonctionne : `GET /api/postman/health`
- [ ] Login fonctionne : `POST /api/postman/auth/login`
- [ ] Test ping fonctionne : `GET /api/postman/test/ping`
- [ ] Les cookies sont automatiques dans Postman
- [ ] Les autres routes API fonctionnent

## 🎉 C'est tout !

Tu peux maintenant tester toute l'API sans te soucier du CSRF !

**Astuce :** Créer un dossier "Quick Tests" dans Postman avec ces requêtes :
- Health Check
- Login
- Get User
- Test Ping

Comme ça tu pourras rapidement vérifier que tout fonctionne.

---

**Besoin d'aide ?** Consulte `POSTMAN_CSRF_GUIDE.md` pour les cas avancés.