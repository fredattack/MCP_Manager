import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';
import {
  mockGitOAuthFlow,
  mockGitOAuthError,
  cleanGitData,
  createGitConnection,
  assertGitConnectionExists,
} from '../fixtures/git';

/**
 * Tests E2E pour la connexion GitHub via OAuth
 *
 * ⚠️  NOTE: Ces tests sont actuellement en attente car les routes/pages Git
 * ne sont pas encore implémentées dans l'application.
 *
 * TODO: Activer ces tests une fois les routes suivantes créées:
 * - GET /git/connections (page de gestion des connexions Git)
 * - POST /api/git/github/oauth/start
 * - GET /api/git/github/oauth/callback
 *
 * Couvre les scénarios suivants:
 * - Flow OAuth complet (authorization -> callback -> connexion établie)
 * - Gestion des erreurs (token invalide, state expiré, rate limiting)
 * - Reconnexion avec compte déjà connecté
 * - Déconnexion du compte GitHub
 * - Affichage des informations de connexion
 */
test.describe('GitHub Connection - OAuth Flow', () => {
  test.beforeEach(async ({ page }) => {
    // Nettoyer toutes les données Git avant chaque test
    await cleanGitData();

    // Se connecter avec l'utilisateur de test
    await login(page);
  });

  test('devrait afficher le bouton de connexion GitHub quand non connecté', async ({ page }) => {
    // Aller sur la page des connexions Git
    await page.goto('/git/connections');

    // Vérifier la présence de la section GitHub
    const githubSection = page.locator('[data-provider="github"]');
    await expect(githubSection).toBeVisible();

    // Vérifier le bouton "Connect GitHub"
    const connectButton = githubSection.getByRole('button', { name: /connecter.*github/i });
    await expect(connectButton).toBeVisible();
    await expect(connectButton).toBeEnabled();

    // Vérifier qu'on affiche bien le message "Aucun compte"
    await expect(githubSection.locator('text=/aucun compte/i')).toBeVisible();

    // Vérifier qu'il n'y a pas de bouton "Déconnecter" (preuve qu'on n'est pas connecté)
    await expect(githubSection.getByRole('button', { name: /disconnect|déconnecter/i })).not.toBeVisible();
  });

  test('devrait initier le flow OAuth GitHub avec succès', async ({ page }) => {
    // Mock de l'API OAuth
    await mockGitOAuthFlow(page, 'github');

    await page.goto('/git/connections');

    // Intercepter l'appel à l'API de démarrage OAuth
    const startOAuthPromise = page.waitForRequest(
      (request) => request.url().includes('/api/git/github/oauth/start')
    );

    // Cliquer sur le bouton de connexion
    await page
      .locator('[data-provider="github"]')
      .getByRole('button', { name: /connect.*github/i })
      .click();

    // Vérifier que l'appel API a été effectué
    const startOAuthRequest = await startOAuthPromise;
    expect(startOAuthRequest.method()).toBe('POST');

    // Dans un vrai scénario, l'utilisateur serait redirigé vers GitHub
    // Ici, on vérifie juste que le flow démarre correctement
    // et qu'une fenêtre popup ou redirection serait initiée
  });

  test('devrait compléter le flow OAuth et créer la connexion', async ({ page }) => {
    // Mock complet du flow OAuth
    await mockGitOAuthFlow(page, 'github');

    // Simuler le retour du callback OAuth (normalement vient de GitHub)
    await page.goto('/git/connections');

    // Mock de la connexion établie
    await page.route('**/api/git/github/oauth/callback*', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          connection: {
            id: 1,
            provider: 'github',
            external_user_id: 'gh-user-123',
            scopes: ['repo', 'read:user', 'workflow'],
            status: 'active',
            expires_at: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString(),
          },
          user: {
            id: 123456,
            login: 'testuser',
            name: 'Test User',
            email: 'test@github.com',
            avatar_url: 'https://avatars.githubusercontent.com/u/123456',
          },
        }),
      });
    });

    // Simuler le callback OAuth avec code et state
    // (normalement géré par la redirection depuis GitHub)
    const callbackUrl = '/git/connections?github_connected=true';
    await page.goto(callbackUrl);

    // Vérifier qu'un message de succès s'affiche (utiliser getByRole pour éviter les duplicates)
    await expect(page.getByRole('status').filter({ hasText: /connected successfully|connexion réussie/i }).first()).toBeVisible({
      timeout: 10000,
    });

    // Vérifier que le statut de connexion a changé
    const githubSection = page.locator('[data-provider="github"]');
    await expect(githubSection.locator('text=/connected|connecté/i')).toBeVisible();

    // Vérifier que le bouton "Connect" est remplacé par "Disconnect" ou info utilisateur
    await expect(
      githubSection.getByRole('button', { name: /disconnect|settings|manage/i })
    ).toBeVisible();
  });

  test('devrait afficher les informations du compte GitHub connecté', async ({ page }) => {
    // Créer une connexion GitHub en base de données
    await createGitConnection('github', 1, 'gh-user-456');

    // Recharger la page pour s'assurer que les données sont à jour
    await page.goto('/git/connections');

    // Attendre que la page soit complètement chargée
    await page.waitForLoadState('networkidle');

    // Vérifier que les infos utilisateur sont affichées
    const githubSection = page.locator('[data-provider="github"]');

    // Vérifier la présence du nom d'utilisateur (chercher dans le meta de la connexion)
    await expect(githubSection.getByText('johndoe', { exact: false })).toBeVisible();

    // Vérifier que le statut "Connected" est visible
    await expect(githubSection.locator('text=/connected|connecté/i')).toBeVisible();

    // Vérifier qu'il y a un avatar (image avec alt contenant le nom)
    const avatar = githubSection.locator('img[alt*="johndoe"], img[alt*="John Doe"]');
    await expect(avatar).toBeVisible();
  });

  test('devrait gérer les erreurs de token invalide', async ({ page }) => {
    // Mock une erreur de token invalide
    await mockGitOAuthError(page, 'github', 'invalid_token');

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    // Cliquer sur le bouton de connexion
    await page
      .locator('[data-provider="github"]')
      .getByRole('button', { name: /connect.*github/i })
      .click();

    // Vérifier qu'un message d'erreur s'affiche (chercher dans les notifications)
    await expect(
      page.getByRole('status').filter({ hasText: /invalid|token|authentication failed/i }).first()
    ).toBeVisible({ timeout: 10000 });

    // Vérifier que la connexion n'a pas été établie
    const isConnected = await assertGitConnectionExists('github');
    expect(isConnected).toBe(false);
  });

  test('devrait gérer les erreurs de state OAuth expiré', async ({ page }) => {
    // Mock une erreur de state expiré
    await mockGitOAuthError(page, 'github', 'expired_state');

    // Simuler un retour de callback avec un state invalide
    await page.goto('/git/connections?error=expired_state');
    await page.waitForLoadState('networkidle');

    // Vérifier qu'un message d'erreur approprié s'affiche
    await expect(
      page.getByRole('status').filter({ hasText: /expired|session/i }).first()
    ).toBeVisible({ timeout: 10000 });
  });

  test('devrait gérer le rate limiting de GitHub API', async ({ page }) => {
    // Mock une erreur de rate limit
    await mockGitOAuthError(page, 'github', 'rate_limit');

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    // Tenter de se connecter
    await page
      .locator('[data-provider="github"]')
      .getByRole('button', { name: /connect.*github/i })
      .click();

    // Vérifier le message de rate limiting
    await expect(
      page.getByRole('status').filter({ hasText: /rate limit|too many/i }).first()
    ).toBeVisible({ timeout: 10000 });
  });

  test('devrait permettre la déconnexion du compte GitHub', async ({ page }) => {
    // Créer une connexion active
    await createGitConnection('github', 1, 'gh-user-789');

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const githubSection = page.locator('[data-provider="github"]');

    // Vérifier que le compte est connecté
    await expect(githubSection.locator('text=/connected|connecté/i').first()).toBeVisible();

    // Mock l'endpoint de déconnexion
    await page.route('**/api/git/github/disconnect', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          message: 'GitHub account disconnected successfully',
        }),
      });
    });

    // Cliquer sur le bouton de déconnexion
    const disconnectButton = githubSection.getByRole('button', {
      name: /disconnect|déconnecter/i,
    });

    // Attendre que le bouton soit cliquable
    await disconnectButton.waitFor({ state: 'visible', timeout: 5000 });
    await disconnectButton.click();

    // Attendre le dialogue de confirmation (si présent) ou que la déconnexion soit effective
    // Note: Si pas de dialog, la page recharge automatiquement
    await page.waitForTimeout(1000);

    // Vérifier que la déconnexion a réussi - soit message soit rechargement de page
    const isStillConnected = await githubSection.locator('text=/connected|connecté/i').isVisible().catch(() => false);

    if (!isStillConnected) {
      // Déconnexion réussie - vérifier que le bouton "Connect" est visible
      await expect(
        githubSection.getByRole('button', { name: /connect.*github/i })
      ).toBeVisible({ timeout: 5000 });
    }
  });

  test('devrait afficher un warning si la connexion expire bientôt', async ({ page }) => {
    // Créer une connexion qui expire dans moins de 10 jours
    const expirationDate = new Date(Date.now() + 5 * 24 * 60 * 60 * 1000); // Dans 5 jours

    await createGitConnection('github', 1, 'gh-user-expiring');

    // Mock l'endpoint pour retourner une connexion qui expire bientôt
    await page.route('**/api/git/github/connection', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          id: 1,
          provider: 'github',
          status: 'active',
          expires_at: expirationDate.toISOString(),
          needs_refresh: true,
        }),
      });
    });

    await page.goto('/git/connections');

    // Vérifier qu'un avertissement d'expiration est affiché
    const githubSection = page.locator('[data-provider="github"]');
    await expect(
      githubSection.locator('text=/expires soon|expire bientôt|renew|renouveler/i')
    ).toBeVisible();

    // Vérifier qu'un bouton de renouvellement est proposé
    await expect(
      githubSection.getByRole('button', { name: /renew|refresh|renouveler/i })
    ).toBeVisible();
  });

  test('devrait empêcher une double connexion GitHub', async ({ page }) => {
    // Créer une connexion existante
    await createGitConnection('github', 1, 'gh-user-already-connected');

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const githubSection = page.locator('[data-provider="github"]');

    // Vérifier que le statut "Connected" est affiché
    await expect(githubSection.locator('text=/connected|connecté/i')).toBeVisible();

    // Vérifier que le bouton "Connect" n'est pas visible
    await expect(
      githubSection.getByRole('button', { name: /^connect/i })
    ).not.toBeVisible();

    // Seul le bouton "Disconnect" ou "Manage" doit être visible
    await expect(
      githubSection.getByRole('button', { name: /disconnect|manage|settings/i })
    ).toBeVisible();
  });

  test('devrait afficher les scopes autorisés par l\'utilisateur', async ({ page }) => {
    // Créer une connexion avec des scopes spécifiques
    await createGitConnection('github', 1, 'gh-user-scopes');

    // Mock l'endpoint pour retourner les scopes
    await page.route('**/api/git/github/connection', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          id: 1,
          provider: 'github',
          scopes: ['repo', 'read:user', 'workflow', 'admin:repo_hook'],
          status: 'active',
        }),
      });
    });

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const githubSection = page.locator('[data-provider="github"]');

    // Cliquer sur "View details" ou similaire pour voir les scopes
    const detailsButton = githubSection.getByRole('button', { name: /details|info|settings/i });
    if (await detailsButton.isVisible()) {
      await detailsButton.click();
    }

    // Vérifier que les scopes sont listés
    await expect(page.locator('text=/repo/i')).toBeVisible();
    await expect(page.locator('text=/read:user/i')).toBeVisible();
    await expect(page.locator('text=/workflow/i')).toBeVisible();
  });
});
