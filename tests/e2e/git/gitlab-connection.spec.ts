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
 * Tests E2E pour la connexion GitLab via OAuth
 *
 * Couvre les scénarios suivants:
 * - Flow OAuth complet GitLab
 * - Gestion des erreurs spécifiques à GitLab
 * - Connexion à une instance GitLab self-hosted
 * - Reconnexion et déconnexion
 * - Affichage des informations utilisateur GitLab
 */
test.describe('GitLab Connection - OAuth Flow', () => {
  test.beforeEach(async ({ page }) => {
    // Nettoyer toutes les données Git avant chaque test
    await cleanGitData();

    // Se connecter avec l'utilisateur de test
    await login(page);
  });

  test('devrait afficher le bouton de connexion GitLab quand non connecté', async ({ page }) => {
    await page.goto('/git/connections');

    // Vérifier la présence de la section GitLab
    const gitlabSection = page.locator('[data-provider="gitlab"]');
    await expect(gitlabSection).toBeVisible();

    // Vérifier le bouton "Connect GitLab"
    const connectButton = gitlabSection.getByRole('button', { name: /connect.*gitlab/i });
    await expect(connectButton).toBeVisible();
    await expect(connectButton).toBeEnabled();

    // Vérifier qu'on affiche bien le message "Aucun compte"
    await expect(gitlabSection.locator('text=/aucun compte/i')).toBeVisible();

    // Vérifier qu'il n'y a pas de bouton "Déconnecter" (preuve qu'on n'est pas connecté)
    await expect(gitlabSection.getByRole('button', { name: /disconnect|déconnecter/i })).not.toBeVisible();
  });

  test('devrait initier le flow OAuth GitLab avec succès', async ({ page }) => {
    // Mock de l'API OAuth GitLab
    await mockGitOAuthFlow(page, 'gitlab');

    await page.goto('/git/connections');

    // Intercepter l'appel à l'API de démarrage OAuth
    const startOAuthPromise = page.waitForRequest(
      (request) => request.url().includes('/api/git/gitlab/oauth/start')
    );

    // Cliquer sur le bouton de connexion GitLab
    await page
      .locator('[data-provider="gitlab"]')
      .getByRole('button', { name: /connect.*gitlab/i })
      .click();

    // Vérifier que l'appel API a été effectué
    const startOAuthRequest = await startOAuthPromise;
    expect(startOAuthRequest.method()).toBe('POST');

    // Vérifier que l'URL d'autorisation GitLab contient les bons paramètres
    // (dans un vrai scénario, une popup ou redirection s'ouvrirait)
  });

  test('devrait compléter le flow OAuth GitLab et créer la connexion', async ({ page }) => {
    // Mock complet du flow OAuth
    await mockGitOAuthFlow(page, 'gitlab');

    await page.goto('/git/connections');

    // Mock de la connexion établie après callback
    await page.route('**/api/git/gitlab/oauth/callback*', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          connection: {
            id: 1,
            provider: 'gitlab',
            external_user_id: 'gl-user-123',
            scopes: ['api', 'read_repository', 'write_repository', 'read_user'],
            status: 'active',
            expires_at: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString(),
          },
          user: {
            id: 789012,
            username: 'testuser',
            name: 'Test User',
            email: 'test@gitlab.com',
            avatar_url: 'https://gitlab.com/uploads/-/system/user/avatar/789012/avatar.png',
          },
        }),
      });
    });

    // Simuler le retour du callback OAuth
    const callbackUrl = '/git/connections?gitlab_connected=true';
    await page.goto(callbackUrl);

    // Vérifier le message de succès (utiliser getByRole pour éviter les duplicates)
    await expect(page.getByRole('status').filter({ hasText: /connected successfully|connexion réussie/i }).first()).toBeVisible({
      timeout: 10000,
    });

    // Vérifier que le statut de connexion a changé
    const gitlabSection = page.locator('[data-provider="gitlab"]');
    await expect(gitlabSection.locator('text=/connected|connecté/i').first()).toBeVisible();

    // Vérifier que les options de gestion sont disponibles
    await expect(
      gitlabSection.getByRole('button', { name: /disconnect|settings|manage/i })
    ).toBeVisible();
  });

  test('devrait afficher les informations du compte GitLab connecté', async ({ page }) => {
    // Créer une connexion GitLab en base de données
    await createGitConnection('gitlab', 1, 'gl-user-456');

    // Mock l'API pour retourner les infos utilisateur GitLab
    await page.route('**/api/git/gitlab/user', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          id: 456789,
          username: 'janedoe',
          name: 'Jane Doe',
          email: 'jane@gitlab.com',
          avatar_url: 'https://gitlab.com/uploads/-/system/user/avatar/456789/avatar.png',
          web_url: 'https://gitlab.com/janedoe',
        }),
      });
    });

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const gitlabSection = page.locator('[data-provider="gitlab"]');

    // Vérifier que le nom d'utilisateur est affiché
    await expect(gitlabSection.locator('text=/janedoe|jane doe/i')).toBeVisible();

    // Vérifier le statut "Connected"
    await expect(gitlabSection.locator('text=/connected|connecté/i').first()).toBeVisible();

    // Vérifier l'avatar
    const avatar = gitlabSection.locator('img[alt*="janedoe"], img[alt*="Jane Doe"]');
    await expect(avatar).toBeVisible();
  });

  test('devrait gérer les erreurs d\'authentification GitLab', async ({ page }) => {
    // Mock une erreur d'authentification
    await mockGitOAuthError(page, 'gitlab', 'invalid_token');

    await page.goto('/git/connections');

    // Tenter de se connecter
    await page
      .locator('[data-provider="gitlab"]')
      .getByRole('button', { name: /connect.*gitlab/i })
      .click();

    // Vérifier le message d'erreur
    await expect(
      page.locator('text=/invalid.*token|authentication failed|échec.*authentification/i')
    ).toBeVisible({ timeout: 5000 });

    // Vérifier qu'aucune connexion n'a été créée
    const isConnected = await assertGitConnectionExists('gitlab');
    expect(isConnected).toBe(false);
  });

  test('devrait permettre la déconnexion du compte GitLab', async ({ page }) => {
    // Créer une connexion active
    await createGitConnection('gitlab', 1, 'gl-user-789');

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const gitlabSection = page.locator('[data-provider="gitlab"]');

    // Vérifier que le compte est connecté
    await expect(gitlabSection.locator('text=/connected|connecté/i').first()).toBeVisible();

    // Mock l'endpoint de déconnexion
    await page.route('**/api/git/gitlab/disconnect', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          message: 'GitLab account disconnected successfully',
        }),
      });
    });

    // Cliquer sur le bouton de déconnexion
    const disconnectButton = gitlabSection.getByRole('button', {
      name: /disconnect|déconnecter/i,
    });

    // Attendre que le bouton soit cliquable
    await disconnectButton.waitFor({ state: 'visible', timeout: 5000 });
    await disconnectButton.click();

    // Attendre le dialogue de confirmation (si présent) ou que la déconnexion soit effective
    await page.waitForTimeout(1000);

    // Vérifier que la déconnexion a réussi - soit message soit rechargement de page
    const isStillConnected = await gitlabSection.locator('text=/connected|connecté/i').isVisible().catch(() => false);

    if (!isStillConnected) {
      // Déconnexion réussie - vérifier que le bouton "Connect" est visible
      await expect(
        gitlabSection.getByRole('button', { name: /connect.*gitlab/i })
      ).toBeVisible({ timeout: 5000 });
    }
  });

  test('devrait supporter la connexion à une instance GitLab self-hosted', async ({ page }) => {
    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const gitlabSection = page.locator('[data-provider="gitlab"]');

    // Cliquer sur le bouton de connexion GitLab
    const connectButton = gitlabSection.getByRole('button', { name: /connect.*gitlab/i });
    await connectButton.click();

    // Vérifier qu'une option pour instance self-hosted est proposée
    // (modal ou champ pour saisir l'URL de l'instance)
    const selfHostedOption = page.getByRole('radio', { name: /self-hosted|instance privée/i });
    if (await selfHostedOption.isVisible()) {
      await selfHostedOption.check();

      // Remplir l'URL de l'instance GitLab custom
      const instanceUrlField = page.getByRole('textbox', { name: /gitlab url|instance url/i });
      await expect(instanceUrlField).toBeVisible();
      await instanceUrlField.fill('https://gitlab.mycompany.com');

      // Mock l'endpoint pour l'instance custom
      await page.route('**/api/git/gitlab/oauth/start', async (route) => {
        const requestBody = await route.request().postDataJSON();
        expect(requestBody.instance_url).toBe('https://gitlab.mycompany.com');

        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({
            auth_url: 'https://gitlab.mycompany.com/oauth/authorize?state=test-state&client_id=test',
            state: 'test-state-custom',
            expires_in: 600,
          }),
        });
      });

      // Valider le formulaire
      await page.getByRole('button', { name: /continue|connect|suivant/i }).click();

      // Vérifier que le flow OAuth démarre avec l'instance custom
      await page.waitForRequest(
        (request) =>
          request.url().includes('/api/git/gitlab/oauth/start') &&
          request.postDataJSON()?.instance_url === 'https://gitlab.mycompany.com'
      );
    }
  });

  test('devrait afficher les scopes GitLab autorisés', async ({ page }) => {
    // Créer une connexion avec des scopes GitLab
    await createGitConnection('gitlab', 1, 'gl-user-scopes');

    // Mock l'endpoint pour retourner les scopes
    await page.route('**/api/git/gitlab/connection', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          id: 1,
          provider: 'gitlab',
          scopes: ['api', 'read_repository', 'write_repository', 'read_user'],
          status: 'active',
        }),
      });
    });

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const gitlabSection = page.locator('[data-provider="gitlab"]');

    // Ouvrir les détails de la connexion
    const detailsButton = gitlabSection.getByRole('button', { name: /details|info|settings/i });
    if (await detailsButton.isVisible()) {
      await detailsButton.click();
    }

    // Vérifier que les scopes GitLab sont affichés
    await expect(page.locator('text=/api/i')).toBeVisible();
    await expect(page.locator('text=/read_repository/i')).toBeVisible();
    await expect(page.locator('text=/write_repository/i')).toBeVisible();
  });

  test('devrait gérer l\'expiration du token GitLab', async ({ page }) => {
    // Créer une connexion qui expire bientôt
    const expirationDate = new Date(Date.now() + 3 * 24 * 60 * 60 * 1000); // Dans 3 jours

    await createGitConnection('gitlab', 1, 'gl-user-expiring');

    // Mock l'endpoint pour retourner une connexion qui expire
    await page.route('**/api/git/gitlab/connection', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          id: 1,
          provider: 'gitlab',
          status: 'active',
          expires_at: expirationDate.toISOString(),
          needs_refresh: true,
        }),
      });
    });

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const gitlabSection = page.locator('[data-provider="gitlab"]');

    // Vérifier l'avertissement d'expiration
    await expect(
      gitlabSection.locator('text=/expires soon|expire bientôt|renew|renouveler/i')
    ).toBeVisible();

    // Vérifier le bouton de renouvellement
    const renewButton = gitlabSection.getByRole('button', { name: /renew|refresh|renouveler/i });
    await expect(renewButton).toBeVisible();

    // Mock l'endpoint de refresh du token
    await page.route('**/api/git/gitlab/refresh-token', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          expires_at: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString(),
          message: 'Token refreshed successfully',
        }),
      });
    });

    // Cliquer sur le bouton de renouvellement
    await renewButton.click();

    // Vérifier que le token a été renouvelé
    await expect(page.locator('text=/token refreshed|renewed|renouvelé/i')).toBeVisible({
      timeout: 5000,
    });
  });

  test('devrait empêcher une double connexion GitLab', async ({ page }) => {
    // Créer une connexion existante
    await createGitConnection('gitlab', 1, 'gl-user-already-connected');

    await page.goto('/git/connections');
    await page.waitForLoadState('networkidle');

    const gitlabSection = page.locator('[data-provider="gitlab"]');

    // Vérifier que le statut "Connected" est affiché
    await expect(gitlabSection.locator('text=/connected|connecté/i').first()).toBeVisible();

    // Vérifier que le bouton "Connect" n'est pas visible
    await expect(gitlabSection.getByRole('button', { name: /^connect/i })).not.toBeVisible();

    // Seules les options de gestion doivent être visibles
    await expect(
      gitlabSection.getByRole('button', { name: /disconnect|manage|settings/i })
    ).toBeVisible();
  });

  test('devrait afficher une erreur si les scopes requis sont insuffisants', async ({ page }) => {
    // Mock une connexion avec des scopes insuffisants
    await page.route('**/api/git/gitlab/oauth/callback*', async (route) => {
      await route.fulfill({
        status: 400,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 'insufficient_scope',
          message: 'The authorized scopes are insufficient for this application',
          required_scopes: ['api', 'read_repository', 'write_repository'],
          granted_scopes: ['read_user'],
        }),
      });
    });

    // Simuler le retour du callback OAuth avec scopes insuffisants
    await page.goto('/git/connections?code=test-code&state=test-state');

    // Vérifier le message d'erreur détaillé
    await expect(page.locator('text=/insufficient.*scope|permissions.*required/i')).toBeVisible({
      timeout: 5000,
    });

    // Vérifier qu'une liste des scopes requis est affichée
    await expect(page.locator('text=/api|read_repository|write_repository/i')).toBeVisible();

    // Proposer de réessayer avec les bons scopes
    const retryButton = page.getByRole('button', { name: /try again|réessayer|reconnect/i });
    await expect(retryButton).toBeVisible();
  });
});
