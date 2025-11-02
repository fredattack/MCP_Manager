import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';
import {
  mockGitRepositoriesList,
  mockGitRepoSync,
  mockGitClone,
  mockGitStats,
  cleanGitData,
  createGitConnection,
  createGitRepository,
  getRepositoryCount,
} from '../fixtures/git';

/**
 * Tests E2E pour la gestion des repositories Git (GitHub/GitLab)
 *
 * ⚠️ NOTE: La plupart de ces tests sont actuellement skip car les routes
 * /repositories ne sont pas encore implémentées. Ces features sont planifiées
 * pour Sprint 3 (Generate Code + Run Tests + Deploy).
 *
 * Les tests servent de documentation du comportement attendu et seront
 * activés dans Sprint 3 une fois les routes créées.
 *
 * Couvre les scénarios suivants:
 * - Listing des repositories
 * - Synchronisation des repositories
 * - Filtrage et recherche
 * - Clonage d'un repository
 * - Gestion des webhooks
 * - Statistiques Git
 * - Archivage de repositories
 */
test.describe('Git Repository Management', () => {
  test.beforeEach(async ({ page }) => {
    // Nettoyer les données Git
    await cleanGitData();

    // Se connecter
    await login(page);

    // Créer une connexion GitHub active pour les tests
    await createGitConnection('github', 1, 'test-user-123');
  });

  test.skip('devrait afficher la liste des repositories synchronisés', async ({ page }) => {
    // Créer quelques repositories de test
    await createGitRepository('github', 1, 'testuser/repo-1');
    await createGitRepository('github', 1, 'testuser/repo-2');
    await createGitRepository('github', 1, 'testuser/repo-3');

    // Mock l'API de listing
    await mockGitRepositoriesList(page, 'github', [
      { name: 'repo-1', fullName: 'testuser/repo-1' },
      { name: 'repo-2', fullName: 'testuser/repo-2' },
      { name: 'repo-3', fullName: 'testuser/repo-3' },
    ]);

    await page.goto('/git/repositories');

    // Vérifier que les repositories sont affichés
    await expect(page.locator('text=/testuser\/repo-1/i')).toBeVisible();
    await expect(page.locator('text=/testuser\/repo-2/i')).toBeVisible();
    await expect(page.locator('text=/testuser\/repo-3/i')).toBeVisible();

    // Vérifier le nombre total
    await expect(page.locator('text=/3.*repositories/i')).toBeVisible();
  });

  test('devrait afficher un état vide quand aucun repository n\'est synchronisé', async ({
    page,
  }) => {
    await page.goto('/git/repositories');

    // Vérifier l'état vide
    await expect(
      page.locator('text=/no repositories|aucun.*repository|empty/i')
    ).toBeVisible();

    // Vérifier qu'un bouton de synchronisation est proposé
    const syncButton = page.getByRole('button', { name: /sync.*repositories|synchroniser/i });
    await expect(syncButton).toBeVisible();
  });

  test.skip('devrait synchroniser les repositories depuis GitHub', async ({ page }) => {
    // Mock l'endpoint de sync
    await mockGitRepoSync(page, 'github');

    // Mock le listing après sync
    await mockGitRepositoriesList(page, 'github', [
      { name: 'new-repo-1', fullName: 'testuser/new-repo-1' },
      { name: 'new-repo-2', fullName: 'testuser/new-repo-2' },
    ]);

    await page.goto('/git/repositories');

    // Cliquer sur le bouton de synchronisation
    const syncButton = page.getByRole('button', { name: /sync.*repositories|synchroniser/i });
    await syncButton.click();

    // Vérifier le message de chargement
    await expect(page.locator('text=/synchronizing|syncing|en cours/i')).toBeVisible();

    // Vérifier le message de succès
    await expect(
      page.locator('text=/synchronized successfully|synchronisé.*succès/i')
    ).toBeVisible({ timeout: 10000 });

    // Vérifier que les nouveaux repositories sont affichés
    await expect(page.locator('text=/new-repo-1/i')).toBeVisible();
    await expect(page.locator('text=/new-repo-2/i')).toBeVisible();

    // Vérifier les statistiques de sync
    await expect(page.locator('text=/2.*added|ajoutés/i')).toBeVisible();
  });

  test.skip('devrait filtrer les repositories par visibilité (public/private)', async ({ page }) => {
    // Mock des repositories mixtes (public + private)
    await mockGitRepositoriesList(page, 'github', [
      { name: 'public-repo', fullName: 'testuser/public-repo', isPrivate: false },
      { name: 'private-repo', fullName: 'testuser/private-repo', isPrivate: true },
      { name: 'another-public', fullName: 'testuser/another-public', isPrivate: false },
    ]);

    await page.goto('/git/repositories');

    // Filtrer uniquement les repositories publics
    const publicFilter = page.getByRole('radio', { name: /public/i });
    await publicFilter.check();

    // Vérifier que seuls les repos publics sont affichés
    await expect(page.locator('text=/public-repo/i')).toBeVisible();
    await expect(page.locator('text=/another-public/i')).toBeVisible();
    await expect(page.locator('text=/private-repo/i')).not.toBeVisible();

    // Filtrer uniquement les repositories privés
    const privateFilter = page.getByRole('radio', { name: /private/i });
    await privateFilter.check();

    // Vérifier que seul le repo privé est affiché
    await expect(page.locator('text=/private-repo/i')).toBeVisible();
    await expect(page.locator('text=/public-repo/i')).not.toBeVisible();
    await expect(page.locator('text=/another-public/i')).not.toBeVisible();
  });

  test.skip('devrait rechercher des repositories par nom', async ({ page }) => {
    // Mock plusieurs repositories
    await mockGitRepositoriesList(page, 'github', [
      { name: 'frontend-app', fullName: 'testuser/frontend-app' },
      { name: 'backend-api', fullName: 'testuser/backend-api' },
      { name: 'mobile-app', fullName: 'testuser/mobile-app' },
      { name: 'docs', fullName: 'testuser/docs' },
    ]);

    await page.goto('/git/repositories');

    // Utiliser la barre de recherche
    const searchBox = page.getByRole('textbox', { name: /search|rechercher/i });
    await searchBox.fill('app');

    // Vérifier que seuls les repos contenant "app" sont affichés
    await expect(page.locator('text=/frontend-app/i')).toBeVisible();
    await expect(page.locator('text=/mobile-app/i')).toBeVisible();
    await expect(page.locator('text=/backend-api/i')).not.toBeVisible(); // "api" ne contient pas "app"
    await expect(page.locator('text=/^docs$/i')).not.toBeVisible();

    // Effacer la recherche
    await searchBox.clear();
    await searchBox.fill('backend');

    // Vérifier le nouveau filtre
    await expect(page.locator('text=/backend-api/i')).toBeVisible();
    await expect(page.locator('text=/frontend-app/i')).not.toBeVisible();
  });

  test.skip('devrait cloner un repository avec succès', async ({ page }) => {
    // Créer un repository
    await createGitRepository('github', 1, 'testuser/my-project');

    // Mock le listing
    await mockGitRepositoriesList(page, 'github', [
      { name: 'my-project', fullName: 'testuser/my-project' },
    ]);

    // Mock l'opération de clonage
    await mockGitClone(page, 'github', 'ext-123');

    await page.goto('/git/repositories');

    // Trouver le repository et cliquer sur "Clone"
    const repoCard = page.locator('[data-repo="testuser/my-project"]');
    const cloneButton = repoCard.getByRole('button', { name: /clone|cloner/i });
    await cloneButton.click();

    // Vérifier que la modal de clonage s'ouvre
    const cloneModal = page.locator('[data-testid="clone-modal"]');
    await expect(cloneModal).toBeVisible();

    // Sélectionner la branche (par défaut: main)
    const branchSelect = cloneModal.getByRole('combobox', { name: /branch|branche/i });
    await expect(branchSelect).toHaveValue('main');

    // Confirmer le clonage
    const confirmCloneButton = cloneModal.getByRole('button', { name: /clone|start|confirmer/i });
    await confirmCloneButton.click();

    // Vérifier le message de progression
    await expect(page.locator('text=/cloning|clonage.*en cours/i')).toBeVisible();

    // Vérifier le message de succès
    await expect(page.locator('text=/cloned successfully|cloné.*succès/i')).toBeVisible({
      timeout: 15000,
    });

    // Vérifier que le statut du repository indique "Cloned"
    await expect(repoCard.locator('text=/cloned|cloné/i')).toBeVisible();
  });

  test.skip('devrait afficher les détails d\'un repository', async ({ page }) => {
    // Créer un repository
    await createGitRepository('github', 1, 'testuser/awesome-project');

    // Mock le détail du repository
    await page.route('**/api/git/github/repos/ext-*', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          id: 1,
          external_id: 'ext-123',
          full_name: 'testuser/awesome-project',
          provider: 'github',
          default_branch: 'main',
          visibility: 'public',
          meta: {
            description: 'An awesome test project',
            stars: 150,
            forks: 42,
            language: 'TypeScript',
            url: 'https://github.com/testuser/awesome-project',
            open_issues: 12,
            created_at: '2024-01-01T00:00:00Z',
            updated_at: '2024-10-26T10:00:00Z',
          },
        }),
      });
    });

    await mockGitRepositoriesList(page, 'github', [
      { name: 'awesome-project', fullName: 'testuser/awesome-project' },
    ]);

    await page.goto('/git/repositories');

    // Cliquer sur le repository pour voir les détails
    await page.locator('text=/awesome-project/i').click();

    // Vérifier que la page de détails s'affiche
    await expect(page).toHaveURL(/\/git\/repositories\/\d+/);

    // Vérifier les informations affichées
    await expect(page.locator('text=/awesome-project/i')).toBeVisible();
    await expect(page.locator('text=/an awesome test project/i')).toBeVisible();
    await expect(page.locator('text=/150.*stars/i')).toBeVisible();
    await expect(page.locator('text=/42.*forks/i')).toBeVisible();
    await expect(page.locator('text=/typescript/i')).toBeVisible();
    await expect(page.locator('text=/12.*open.*issues/i')).toBeVisible();
  });

  test.skip('devrait configurer un webhook pour un repository', async ({ page }) => {
    // Créer un repository
    await createGitRepository('github', 1, 'testuser/webhook-repo');

    await mockGitRepositoriesList(page, 'github', [
      { name: 'webhook-repo', fullName: 'testuser/webhook-repo' },
    ]);

    // Mock l'endpoint de création de webhook
    await page.route('**/api/git/github/repos/*/webhook', async (route) => {
      await route.fulfill({
        status: 201,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          webhook: {
            id: 1,
            url: 'https://app.agentops.io/webhooks/github',
            events: ['push', 'pull_request', 'issues'],
            active: true,
          },
          message: 'Webhook created successfully',
        }),
      });
    });

    await page.goto('/git/repositories');

    // Cliquer sur le repository
    const repoCard = page.locator('[data-repo="testuser/webhook-repo"]');
    await repoCard.click();

    // Aller dans les settings du repository
    const settingsTab = page.getByRole('tab', { name: /settings|paramètres/i });
    await settingsTab.click();

    // Activer les webhooks
    const enableWebhookButton = page.getByRole('button', { name: /enable.*webhook|activer/i });
    await enableWebhookButton.click();

    // Sélectionner les événements
    const pushEvent = page.getByRole('checkbox', { name: /push/i });
    const prEvent = page.getByRole('checkbox', { name: /pull.*request/i });
    const issuesEvent = page.getByRole('checkbox', { name: /issues/i });

    await pushEvent.check();
    await prEvent.check();
    await issuesEvent.check();

    // Confirmer la création du webhook
    const confirmButton = page.getByRole('button', { name: /create.*webhook|save/i });
    await confirmButton.click();

    // Vérifier le message de succès
    await expect(page.locator('text=/webhook.*created|configuré/i')).toBeVisible({
      timeout: 5000,
    });

    // Vérifier que le webhook est actif
    await expect(page.locator('text=/webhook.*active/i')).toBeVisible();

    // Vérifier l'URL du webhook affichée
    await expect(page.locator('text=/https:\/\/app\.agentops\.io\/webhooks\/github/i')).toBeVisible();
  });

  test.skip('devrait afficher les statistiques Git globales', async ({ page }) => {
    // Mock l'endpoint des statistiques
    await mockGitStats(page, 'github');

    await page.goto('/git/repositories');

    // Cliquer sur le bouton "Stats" ou "Dashboard"
    const statsButton = page.getByRole('button', { name: /stats|dashboard|statistics/i });
    if (await statsButton.isVisible()) {
      await statsButton.click();
    }

    // Vérifier que les statistiques sont affichées
    await expect(page.locator('text=/10.*total.*repositories/i')).toBeVisible();
    await expect(page.locator('text=/7.*public/i')).toBeVisible();
    await expect(page.locator('text=/3.*private/i')).toBeVisible();
    await expect(page.locator('text=/5.*clones/i')).toBeVisible();
    await expect(page.locator('text=/256.*mb.*storage/i')).toBeVisible();
  });

  test.skip('devrait gérer les erreurs de synchronisation', async ({ page }) => {
    // Mock une erreur de synchronisation
    await page.route('**/api/git/github/repos/sync', async (route) => {
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 'sync_failed',
          message: 'Failed to synchronize repositories. GitHub API rate limit exceeded.',
        }),
      });
    });

    await page.goto('/git/repositories');

    // Tenter de synchroniser
    const syncButton = page.getByRole('button', { name: /sync.*repositories|synchroniser/i });
    await syncButton.click();

    // Vérifier le message d'erreur
    await expect(page.locator('text=/failed.*synchronize|rate limit/i')).toBeVisible({
      timeout: 5000,
    });

    // Vérifier qu'un bouton de retry est proposé
    await expect(page.getByRole('button', { name: /try again|réessayer/i })).toBeVisible();
  });

  test.skip('devrait archiver un repository', async ({ page }) => {
    // Créer un repository
    await createGitRepository('github', 1, 'testuser/old-project');

    await mockGitRepositoriesList(page, 'github', [
      { name: 'old-project', fullName: 'testuser/old-project' },
    ]);

    // Mock l'endpoint d'archivage
    await page.route('**/api/git/github/repos/*/archive', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          message: 'Repository archived successfully',
        }),
      });
    });

    await page.goto('/git/repositories');

    // Cliquer sur le repository
    const repoCard = page.locator('[data-repo="testuser/old-project"]');
    await repoCard.click();

    // Aller dans les settings
    const settingsTab = page.getByRole('tab', { name: /settings|paramètres/i });
    await settingsTab.click();

    // Cliquer sur "Archive"
    const archiveButton = page.getByRole('button', { name: /archive|archiver/i });
    await archiveButton.click();

    // Confirmer l'archivage dans la modal
    const confirmArchiveButton = page.getByRole('button', { name: /confirm.*archive|yes/i });
    await confirmArchiveButton.click();

    // Vérifier le message de succès
    await expect(page.locator('text=/archived successfully|archivé/i')).toBeVisible({
      timeout: 5000,
    });

    // Vérifier que le repository est marqué comme archivé
    await expect(page.locator('text=/archived|archivé/i')).toBeVisible();
  });

  test.skip('devrait permettre de passer d\'un provider à un autre (GitHub <-> GitLab)', async ({
    page,
  }) => {
    // Créer des connexions pour les deux providers
    await createGitConnection('github', 1, 'gh-user');
    await createGitConnection('gitlab', 1, 'gl-user');

    // Créer des repositories pour chaque provider
    await createGitRepository('github', 1, 'testuser/github-repo');
    await createGitRepository('gitlab', 1, 'testuser/gitlab-repo');

    // Mock les listings pour chaque provider
    await mockGitRepositoriesList(page, 'github', [
      { name: 'github-repo', fullName: 'testuser/github-repo' },
    ]);

    await mockGitRepositoriesList(page, 'gitlab', [
      { name: 'gitlab-repo', fullName: 'testuser/gitlab-repo' },
    ]);

    await page.goto('/git/repositories');

    // Par défaut, afficher les repositories GitHub
    await expect(page.locator('text=/github-repo/i')).toBeVisible();

    // Passer aux repositories GitLab
    const gitlabTab = page.getByRole('tab', { name: /gitlab/i });
    await gitlabTab.click();

    // Vérifier que les repositories GitLab sont affichés
    await expect(page.locator('text=/gitlab-repo/i')).toBeVisible();
    await expect(page.locator('text=/github-repo/i')).not.toBeVisible();

    // Revenir à GitHub
    const githubTab = page.getByRole('tab', { name: /github/i });
    await githubTab.click();

    await expect(page.locator('text=/github-repo/i')).toBeVisible();
    await expect(page.locator('text=/gitlab-repo/i')).not.toBeVisible();
  });

  test.skip('devrait rafraîchir les métadonnées d\'un repository spécifique', async ({ page }) => {
    // Créer un repository
    await createGitRepository('github', 1, 'testuser/active-repo');

    await mockGitRepositoriesList(page, 'github', [
      { name: 'active-repo', fullName: 'testuser/active-repo' },
    ]);

    // Mock l'endpoint de refresh
    await page.route('**/api/git/github/repos/*/refresh', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          repository: {
            full_name: 'testuser/active-repo',
            meta: {
              stars: 200, // Nombre de stars mis à jour
              forks: 50,
              updated_at: new Date().toISOString(),
            },
          },
          message: 'Repository metadata refreshed',
        }),
      });
    });

    await page.goto('/git/repositories');

    // Cliquer sur le repository
    const repoCard = page.locator('[data-repo="testuser/active-repo"]');

    // Cliquer sur le bouton de refresh (icône ou bouton)
    const refreshButton = repoCard.getByRole('button', { name: /refresh|reload|actualiser/i });
    await refreshButton.click();

    // Vérifier le message de succès
    await expect(page.locator('text=/refreshed|actualisé/i')).toBeVisible({ timeout: 5000 });

    // Vérifier que les métadonnées sont mises à jour (ex: nouvelles stats)
    await expect(page.locator('text=/200.*stars/i')).toBeVisible();
  });
});
