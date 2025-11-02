import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';
import { seedWorkflows, truncateTable } from '../fixtures/database';

test.describe('Workflows List Page', () => {
  test.beforeEach(async ({ page }) => {
    // Nettoyer uniquement la table workflows (plus rapide que resetDatabase)
    await truncateTable('workflows');

    // Utiliser l'utilisateur de test créé par le seeder
    await login(page);
  });

  test('displays empty state when no workflows exist', async ({ page }) => {
    await page.goto('/workflows');
    await page.waitForLoadState('networkidle');

    // Vérifier la présence d'un état vide
    await expect(page.locator('text=/no workflows|aucun workflow|empty/i').first()).toBeVisible();
  });

  test('displays list of workflows when they exist', async ({ page }) => {
    await seedWorkflows(3);

    await page.goto('/workflows');
    await page.waitForLoadState('networkidle');

    // Vérifier qu'on a bien des workflows affichés
    const workflowCards = page.locator('[data-testid="workflow-card"]');
    await expect(workflowCards).toHaveCount(3);
  });

  test('can navigate to workflow detail page', async ({ page }) => {
    await seedWorkflows(1);

    await page.goto('/workflows');
    await page.waitForLoadState('networkidle');

    // Cliquer sur le premier workflow
    await page.locator('[data-testid="workflow-card"]').first().click();

    // Vérifier qu'on est redirigé vers la page de détail
    await expect(page).toHaveURL(/\/workflows\/\d+/);
  });

  test('can create a new workflow', async ({ page }) => {
    await page.goto('/workflows');
    await page.waitForLoadState('networkidle');

    // Cliquer sur le bouton de création
    await page.getByRole('button', { name: /create|new.*workflow|créer/i }).click();

    // Vérifier que le modal s'affiche
    await expect(page.locator('[data-testid="create-workflow-modal"]')).toBeVisible({ timeout: 5000 });

    // Vérifier que le modal contient le titre attendu
    await expect(page.locator('[data-testid="create-workflow-modal"]').getByText(/create.*workflow/i)).toBeVisible();
  });
});
