# Guide CSRF pour Postman avec Laravel

## 🔒 Problème : CSRF Token Mismatch

Laravel protège automatiquement toutes les routes **web** (POST, PUT, PATCH, DELETE) avec une vérification CSRF. Postman ne gère pas automatiquement ces tokens comme le ferait un navigateur.

```json
{
  "message": "CSRF token mismatch.",
  "exception": "Symfony\\Component\\HttpKernel\\Exception\\HttpException"
}
```

## ✅ Solutions

### Solution 1 : Utiliser les routes API (Recommandé)

Les routes préfixées par `/api/` **ne nécessitent PAS** de token CSRF.

#### Routes Web (nécessitent CSRF)
```
POST /login                    ❌ CSRF requis
POST /register                 ❌ CSRF requis
POST /logout                   ❌ CSRF requis
POST /integrations/todoist/connect  ❌ CSRF requis
```

#### Routes API (pas de CSRF)
```
POST /api/integrations         ✅ Pas de CSRF
POST /api/workflows            ✅ Pas de CSRF
POST /api/ai/chat              ✅ Pas de CSRF
POST /api/jira/issues          ✅ Pas de CSRF
```

**Privilégier les routes API dans Postman !**

### Solution 2 : Obtenir le CSRF token dynamiquement

Pour les routes web qui n'ont pas d'équivalent API, voici comment obtenir le token CSRF.

#### Étape 1 : Créer une route pour obtenir le token

Ajouter dans `routes/web.php` :

```php
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});
```

#### Étape 2 : Configuration Postman

**2.1. Créer une requête "Get CSRF Token"**

```http
GET {{base_url}}/sanctum/csrf-cookie
```

Cette requête va définir le cookie `XSRF-TOKEN`.

**2.2. Ajouter un script de Pre-request à la collection**

Dans les paramètres de la collection → onglet "Pre-request Script" :

```javascript
// Script automatique pour extraire le CSRF token
const xsrfCookie = pm.cookies.get('XSRF-TOKEN');

if (xsrfCookie) {
    pm.environment.set('csrf_token', decodeURIComponent(xsrfCookie));
    console.log('✅ CSRF token set:', pm.environment.get('csrf_token'));
} else {
    console.warn('⚠️ CSRF token not found. Run "Get CSRF Token" request first.');
}
```

**2.3. Ajouter le header X-XSRF-TOKEN**

Dans chaque requête POST/PUT/PATCH/DELETE vers une route web, ajouter :

```
X-XSRF-TOKEN: {{csrf_token}}
```

#### Workflow complet

1. Exécuter `Get CSRF Token` (une seule fois ou quand le cookie expire)
2. Le script pre-request extrait automatiquement le token
3. Les requêtes web incluent le header `X-XSRF-TOKEN`

### Solution 3 : Désactiver CSRF pour les tests (Développement uniquement)

⚠️ **NE JAMAIS UTILISER EN PRODUCTION**

Créer un middleware dans `app/Http/Middleware/DisableCsrfForPostman.php` :

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableCsrfForPostman
{
    public function handle(Request $request, Closure $next)
    {
        // Désactiver CSRF seulement en développement et si header Postman
        if (app()->environment('local') && $request->hasHeader('X-Postman-Test')) {
            $request->session()->put('_token', 'postman-bypass');
        }

        return $next($request);
    }
}
```

Enregistrer dans `bootstrap/app.php` :

```php
->withMiddleware(function (Middleware $middleware) {
    if (app()->environment('local')) {
        $middleware->web(prepend: [
            \App\Http\Middleware\DisableCsrfForPostman::class,
        ]);
    }
    // ... reste du code
})
```

Ajouter dans les headers Postman :

```
X-Postman-Test: true
```

### Solution 4 : Routes API d'authentification alternatives

Créer des routes API pour l'authentification dans `routes/api.php` :

```php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

// API Authentication routes (no CSRF needed)
Route::prefix('auth')->group(function () {
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::middleware('auth:web')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy']);
        Route::get('user', function () {
            return response()->json(auth()->user());
        });
    });
});
```

Utiliser ensuite :

```
POST /api/auth/register   ✅ Pas de CSRF
POST /api/auth/login      ✅ Pas de CSRF
POST /api/auth/logout     ✅ Pas de CSRF
GET  /api/auth/user       ✅ Pas de CSRF
```

## 🎯 Recommandations par cas d'usage

### Pour les tests API uniquement
👉 **Utiliser Solution 1** : Routes API uniquement (pas de CSRF)

### Pour tester l'interface web complète
👉 **Utiliser Solution 2** : Obtenir le CSRF token dynamiquement

### Pour le développement rapide (local)
👉 **Utiliser Solution 3** : Désactiver temporairement (avec précautions)

### Pour une nouvelle fonctionnalité
👉 **Utiliser Solution 4** : Créer des routes API dédiées

## 📝 Checklist de débogage

Si vous avez toujours l'erreur CSRF :

- [ ] Vérifier que vous utilisez une route `/api/` si possible
- [ ] Vérifier que le cookie `XSRF-TOKEN` est présent (Cookies tab dans Postman)
- [ ] Vérifier que le header `X-XSRF-TOKEN` est envoyé
- [ ] Vérifier que vous n'avez pas de cookies expirés (Clear cookies dans Postman)
- [ ] Redémarrer la session Laravel : `php artisan config:clear && php artisan cache:clear`
- [ ] Vérifier que `APP_URL` dans `.env` correspond à l'URL testée

## 🔧 Configuration Postman recommandée

### Variables d'environnement à ajouter

```json
{
  "csrf_token": "",
  "session_cookie": ""
}
```

### Pre-request Script de collection

```javascript
// Auto-extract CSRF token from cookies
const xsrfCookie = pm.cookies.get('XSRF-TOKEN');
if (xsrfCookie) {
    pm.environment.set('csrf_token', decodeURIComponent(xsrfCookie));
}

// Auto-extract session cookie
const sessionCookie = pm.cookies.get('laravel_session');
if (sessionCookie) {
    pm.environment.set('session_cookie', sessionCookie);
}
```

### Headers à ajouter aux requêtes web

```
X-XSRF-TOKEN: {{csrf_token}}
Accept: application/json
Content-Type: application/json
Referer: {{base_url}}
```

## 🚀 Quick Start

### Approche la plus simple (sans CSRF)

1. Utiliser **uniquement les routes `/api/`** dans Postman
2. S'authentifier via une route API ou utiliser un Bearer token
3. Tester toutes les fonctionnalités via l'API

### Approche complète (avec CSRF)

1. Créer une requête `GET {{base_url}}/sanctum/csrf-cookie`
2. Ajouter le Pre-request Script à la collection
3. Ajouter `X-XSRF-TOKEN: {{csrf_token}}` aux requêtes web
4. Exécuter "Get CSRF Token" avant de tester les routes web

## 📚 Ressources

- [Laravel CSRF Protection](https://laravel.com/docs/12.x/csrf)
- [Laravel API Authentication](https://laravel.com/docs/12.x/sanctum)
- [Postman Pre-request Scripts](https://learning.postman.com/docs/writing-scripts/pre-request-scripts/)

---

**Note** : Pour un usage normal avec Postman, privilégiez **toujours les routes API** qui n'ont pas besoin de CSRF. Les routes web sont conçues pour les navigateurs avec Inertia.js.
