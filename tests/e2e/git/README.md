# Tests E2E - Intégrations Git (GitHub & GitLab)

Ce dossier contient les tests end-to-end complets pour les fonctionnalités d'intégration Git (GitHub et GitLab) de l'application AgentOps / MCP Manager.

## 📁 Structure des Tests

```
tests/e2e/git/
├── github-connection.spec.ts      # Tests connexion OAuth GitHub
├── gitlab-connection.spec.ts      # Tests connexion OAuth GitLab
├── repository-management.spec.ts  # Tests gestion repositories
└── README.md                      # Cette documentation
```

## 🧪 Couverture des Tests

### 1. **github-connection.spec.ts** (OAuth GitHub)

Tests couverts:
- ✅ Affichage du bouton de connexion GitHub
- ✅ Initiation du flow OAuth GitHub
- ✅ Complétion du flow OAuth et création de connexion
- ✅ Affichage des informations du compte GitHub connecté
- ✅ Gestion des erreurs (token invalide, state expiré, rate limiting)
- ✅ Déconnexion du compte GitHub
- ✅ Warning d'expiration du token
- ✅ Prévention de double connexion
- ✅ Affichage des scopes autorisés

**Total: 10 scénarios de test**

### 2. **gitlab-connection.spec.ts** (OAuth GitLab)

Tests couverts:
- ✅ Affichage du bouton de connexion GitLab
- ✅ Initiation du flow OAuth GitLab
- ✅ Complétion du flow OAuth GitLab
- ✅ Affichage des informations du compte GitLab
- ✅ Gestion des erreurs d'authentification
- ✅ Déconnexion du compte GitLab
- ✅ Support instance GitLab self-hosted
- ✅ Affichage des scopes autorisés
- ✅ Gestion de l'expiration du token
- ✅ Prévention de double connexion
- ✅ Erreur si scopes insuffisants

**Total: 11 scénarios de test**

### 3. **repository-management.spec.ts** (Gestion Repositories)

Tests couverts:
- ✅ Affichage de la liste des repositories synchronisés
- ✅ État vide quand aucun repository
- ✅ Synchronisation des repositories depuis GitHub/GitLab
- ✅ Filtrage par visibilité (public/private)
- ✅ Recherche de repositories par nom
- ✅ Clonage d'un repository
- ✅ Affichage des détails d'un repository
- ✅ Configuration de webhooks
- ✅ Affichage des statistiques Git globales
- ✅ Gestion des erreurs de synchronisation
- ✅ Archivage d'un repository
- ✅ Switch entre providers (GitHub <-> GitLab)
- ✅ Rafraîchissement des métadonnées

**Total: 13 scénarios de test**

## 🛠️ Fixtures Disponibles

Le fichier `tests/e2e/fixtures/git.ts` fournit les helpers suivants:

### Création de données de test
```typescript
createGitConnection(provider, userId, externalUserId)
createGitRepository(provider, userId, repoName)
```

### Mocking des APIs Git
```typescript
mockGitOAuthFlow(page, provider)
mockGitRepositoriesList(page, provider, repos)
mockGitClone(page, provider, externalId)
mockGitRepoSync(page, provider)
mockGitStats(page, provider)
mockGitOAuthError(page, provider, errorType)
```

### Utilitaires de nettoyage
```typescript
cleanGitData()
```

### Assertions
```typescript
assertGitConnectionExists(provider)
getRepositoryCount(provider)
```

## 🚀 Exécution des Tests

### Tous les tests Git
```bash
npx playwright test tests/e2e/git/
```

### Test spécifique
```bash
# Tests GitHub
npx playwright test tests/e2e/git/github-connection.spec.ts

# Tests GitLab
npx playwright test tests/e2e/git/gitlab-connection.spec.ts

# Tests Repository Management
npx playwright test tests/e2e/git/repository-management.spec.ts
```

### Mode headed (avec navigateur visible)
```bash
npx playwright test tests/e2e/git/ --headed
```

### Mode debug
```bash
npx playwright test tests/e2e/git/ --debug
```

### Test unique
```bash
npx playwright test tests/e2e/git/github-connection.spec.ts -g "devrait compléter le flow OAuth"
```

## 📊 Rapports de Tests

Après exécution, les rapports sont disponibles:

```bash
# Ouvrir le rapport HTML
npx playwright show-report playwright-report

# Rapport JSON
cat test-results/results.json
```

## 🔧 Configuration

Les tests utilisent la configuration définie dans `playwright.config.ts`:

- **Base URL**: `http://localhost:3978`
- **Database**: PostgreSQL (test)
- **User de test**: `info@hddev.be` / `password` (créé par seeder)
- **Workers**: 1 (pour éviter conflits DB)
- **Timeout**: 30s par test
- **Retries**: 0 en local, 2 en CI

## 🎯 Best Practices

Les tests suivent les bonnes pratiques Playwright:

### 1. Sélecteurs Accessibles
```typescript
// ✅ BON - Utilise getByRole
await page.getByRole('button', { name: /connect.*github/i });

// ❌ ÉVITER - data-testid sauf si nécessaire
await page.locator('[data-testid="connect-github"]');
```

### 2. Attentes Explicites
```typescript
// ✅ BON - Attente avec timeout
await expect(page.locator('text=/connected/i')).toBeVisible({ timeout: 5000 });

// ❌ ÉVITER - waitForTimeout
await page.waitForTimeout(5000);
```

### 3. Nettoyage Avant Chaque Test
```typescript
test.beforeEach(async ({ page }) => {
  await cleanGitData();
  await login(page);
  await createGitConnection('github', 1);
});
```

### 4. Mocking des APIs Externes
```typescript
// Toujours mocker GitHub/GitLab APIs pour éviter rate limiting
await mockGitOAuthFlow(page, 'github');
```

## 🐛 Debugging

### Logs de Debug
```typescript
// Ajouter des console.log dans les tests
console.log('Current URL:', page.url());
console.log('HTML:', await page.content());
```

### Screenshots
```typescript
// Prendre un screenshot manuel
await page.screenshot({ path: 'debug.png' });
```

### Pause Interactive
```typescript
// Mettre en pause pour inspecter
await page.pause();
```

## 📝 Commentaires en Français

Tous les commentaires dans les tests sont en français comme demandé:

```typescript
/**
 * Tests E2E pour la connexion GitHub via OAuth
 *
 * Couvre les scénarios suivants:
 * - Flow OAuth complet
 * - Gestion des erreurs
 * ...
 */
```

## ✅ Checklist de Test

Avant de commit, vérifier:

- [ ] Tous les tests passent localement
- [ ] Pas de `data-testid` inutiles (préférer `getByRole`)
- [ ] Tous les `beforeEach` nettoient correctement
- [ ] Les APIs externes sont mockées
- [ ] Les timeouts sont appropriés
- [ ] Les commentaires sont en français
- [ ] Les messages d'erreur sont clairs

## 🔗 Références

- [Playwright Documentation](https://playwright.dev)
- [Laravel Testing](https://laravel.com/docs/testing)
- [GitHub OAuth Docs](https://docs.github.com/en/apps/oauth-apps/building-oauth-apps)
- [GitLab OAuth Docs](https://docs.gitlab.com/ee/api/oauth2.html)

## 🤝 Contribution

Pour ajouter de nouveaux tests:

1. Créer le fichier de test dans `tests/e2e/git/`
2. Utiliser les fixtures existantes dans `tests/e2e/fixtures/git.ts`
3. Suivre la structure `describe` > `beforeEach` > `test`
4. Ajouter des commentaires en français
5. Exécuter et vérifier que tous les tests passent
6. Mettre à jour ce README si nécessaire

---

**Créé le**: 26 octobre 2025
**Dernière mise à jour**: 26 octobre 2025
**Auteur**: Claude Code (Test Automation Specialist)
**Total de tests**: 34 scénarios E2E
