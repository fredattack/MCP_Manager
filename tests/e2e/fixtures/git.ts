import { Page } from '@playwright/test';
import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

/**
 * Fixture pour créer une connexion Git de test dans la base de données
 * Utilisé pour simuler une connexion OAuth déjà établie
 */
export async function createGitConnection(
  provider: 'github' | 'gitlab',
  userId: number = 1,
  externalUserId: string = 'test-user-123'
): Promise<number> {
  // Generate appropriate usernames based on external user ID
  const username = externalUserId.replace('gh-user-', '').replace('gl-user-', '');
  const usernameMap: Record<string, string> = {
    '456': 'johndoe',
    '789': 'testuser',
    'scopes': 'testuser',
    'expiring': 'testuser',
    'already-connected': 'testuser',
  };
  const actualUsername = usernameMap[username] || 'testuser';

  const result = await execAsync(`php artisan tinker --execute="
    \\$connection = \\App\\Models\\GitConnection::create([
      'user_id' => ${userId},
      'provider' => '${provider}',
      'external_user_id' => '${externalUserId}',
      'meta' => json_encode([
        'username' => '${actualUsername}',
        'email' => '${actualUsername}@example.com',
        'avatar_url' => 'https://avatars.githubusercontent.com/u/${Date.now()}',
        'name' => '${actualUsername.charAt(0).toUpperCase() + actualUsername.slice(1)}',
      ]),
      'scopes' => json_encode(['repo', 'read:user']),
      'access_token_enc' => encrypt('test-token-${Date.now()}'),
      'status' => 'active',
      'expires_at' => now()->addYear(),
    ]);
    echo \\$connection->id;
  " --env=testing`);

  return parseInt(result.stdout.trim());
}

/**
 * Créer un repository de test dans la base de données
 */
export async function createGitRepository(
  provider: 'github' | 'gitlab',
  userId: number = 1,
  repoName: string = 'test/repo'
): Promise<number> {
  const result = await execAsync(`php artisan tinker --execute="
    \\$repo = \\App\\Models\\GitRepository::create([
      'user_id' => ${userId},
      'provider' => '${provider}',
      'external_id' => 'ext-${Date.now()}',
      'full_name' => '${repoName}',
      'default_branch' => 'main',
      'visibility' => 'public',
      'archived' => false,
      'meta' => json_encode(['stars' => 42, 'forks' => 10]),
    ]);
    echo \\$repo->id;
  " --env=testing`);

  return parseInt(result.stdout.trim());
}

/**
 * Mock l'API OAuth pour GitHub/GitLab
 * Intercepte les appels API et retourne des données de test
 */
export async function mockGitOAuthFlow(page: Page, provider: 'github' | 'gitlab'): Promise<void> {
  // Mock de l'endpoint pour démarrer le flow OAuth
  await page.route(`**/api/git/${provider}/oauth/start`, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        auth_url: `https://${provider}.com/login/oauth/authorize?state=test-state&client_id=test`,
        state: 'test-state-123',
        expires_in: 600,
      }),
    });
  });

  // Mock du callback OAuth (simulation du retour après autorisation)
  await page.route(`**/api/git/${provider}/oauth/callback*`, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        connection: {
          id: 1,
          provider: provider,
          external_user_id: 'mock-user-123',
          scopes: ['repo', 'read:user', 'workflow'],
          status: 'active',
          expires_at: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString(),
        },
        user: {
          id: 123456,
          login: 'testuser',
          name: 'Test User',
          email: 'test@example.com',
          avatar_url: 'https://avatars.githubusercontent.com/u/123456',
        },
      }),
    });
  });
}

/**
 * Mock l'API de listing des repositories
 */
export async function mockGitRepositoriesList(
  page: Page,
  provider: 'github' | 'gitlab',
  repos: Array<{ name: string; fullName: string; isPrivate?: boolean }>
): Promise<void> {
  await page.route(`**/api/git/${provider}/repos`, async (route) => {
    const mockRepos = repos.map((repo, index) => ({
      id: index + 1,
      external_id: `ext-${index + 1}`,
      full_name: repo.fullName,
      name: repo.name,
      provider: provider,
      default_branch: 'main',
      visibility: repo.isPrivate ? 'private' : 'public',
      archived: false,
      last_synced_at: new Date().toISOString(),
      meta: {
        description: `Test repository ${repo.name}`,
        stars: Math.floor(Math.random() * 100),
        forks: Math.floor(Math.random() * 20),
        language: 'TypeScript',
        url: `https://${provider}.com/${repo.fullName}`,
      },
    }));

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        data: mockRepos,
        meta: {
          total: mockRepos.length,
          synced_at: new Date().toISOString(),
        },
      }),
    });
  });
}

/**
 * Mock l'opération de clonage d'un repository
 */
export async function mockGitClone(
  page: Page,
  provider: 'github' | 'gitlab',
  externalId: string
): Promise<void> {
  await page.route(`**/api/git/${provider}/repos/${externalId}/clone`, async (route) => {
    await route.fulfill({
      status: 201,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        clone: {
          id: 1,
          repository_id: 1,
          status: 'completed',
          branch: 'main',
          local_path: '/tmp/clones/test-repo',
          size_bytes: 1024000,
          cloned_at: new Date().toISOString(),
        },
        message: 'Repository cloned successfully',
      }),
    });
  });
}

/**
 * Mock l'endpoint de sync des repositories
 */
export async function mockGitRepoSync(page: Page, provider: 'github' | 'gitlab'): Promise<void> {
  await page.route(`**/api/git/${provider}/repos/sync`, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        synced: 5,
        added: 2,
        updated: 3,
        removed: 0,
        message: 'Repositories synchronized successfully',
      }),
    });
  });
}

/**
 * Mock l'endpoint des statistiques Git
 */
export async function mockGitStats(page: Page, provider: 'github' | 'gitlab'): Promise<void> {
  await page.route(`**/api/git/${provider}/repos/stats`, async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        total_repositories: 10,
        public_repositories: 7,
        private_repositories: 3,
        archived_repositories: 1,
        total_clones: 5,
        storage_used_mb: 256.5,
      }),
    });
  });
}

/**
 * Nettoyer toutes les données Git de test
 */
export async function cleanGitData(): Promise<void> {
  await execAsync(`php artisan tinker --execute="
    \\App\\Models\\GitClone::truncate();
    \\App\\Models\\GitRepository::truncate();
    \\App\\Models\\GitConnection::truncate();
  " --env=testing`);
}

/**
 * Simuler une erreur de connexion OAuth
 */
export async function mockGitOAuthError(
  page: Page,
  provider: 'github' | 'gitlab',
  errorType: 'invalid_token' | 'expired_state' | 'rate_limit' = 'invalid_token'
): Promise<void> {
  const errorMessages = {
    invalid_token: 'Invalid or expired OAuth token',
    expired_state: 'OAuth state not found or expired',
    rate_limit: 'Rate limit exceeded. Please try again later.',
  };

  await page.route(`**/api/git/${provider}/oauth/**`, async (route) => {
    await route.fulfill({
      status: errorType === 'rate_limit' ? 429 : 400,
      contentType: 'application/json',
      body: JSON.stringify({
        error: errorType,
        message: errorMessages[errorType],
      }),
    });
  });
}

/**
 * Vérifier qu'une connexion Git existe en base de données
 */
export async function assertGitConnectionExists(provider: 'github' | 'gitlab'): Promise<boolean> {
  const result = await execAsync(`php artisan tinker --execute="
    \\$exists = \\App\\Models\\GitConnection::where('provider', '${provider}')->exists();
    echo \\$exists ? 'true' : 'false';
  " --env=testing`);

  return result.stdout.trim() === 'true';
}

/**
 * Récupérer le nombre de repositories synchronisés
 */
export async function getRepositoryCount(provider: 'github' | 'gitlab'): Promise<number> {
  const result = await execAsync(`php artisan tinker --execute="
    echo \\App\\Models\\GitRepository::where('provider', '${provider}')->count();
  " --env=testing`);

  return parseInt(result.stdout.trim());
}
