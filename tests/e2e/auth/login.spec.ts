import { test, expect } from '@playwright/test';
import { login, logout, assertAuthenticated } from '../fixtures/auth';

test.describe('Authentication Flow', () => {
  // Pas de beforeEach - on utilise l'utilisateur créé par le global setup

  test('user can login successfully', async ({ page }) => {
    // Utiliser l'utilisateur de test créé par le seeder
    await login(page);
    await assertAuthenticated(page);

    // Vérifier qu'on est bien sur le dashboard
    await expect(page).toHaveURL(/\/dashboard/);
  });

  test('user can logout successfully', async ({ page }) => {
    await login(page);
    await assertAuthenticated(page);

    await logout(page);

    // Vérifier qu'on est redirigé vers la page de login
    await expect(page).toHaveURL(/\/login/);
  });

  test('user cannot login with invalid credentials', async ({ page }) => {
    await page.goto('/login');

    // Utiliser getByRole (best practice)
    await page.getByRole('textbox', { name: /email/i }).fill('info@hddev.be');
    await page.getByRole('textbox', { name: /password/i }).fill('wrongpassword');
    await page.getByRole('button', { name: /log in/i }).click();

    // Vérifier qu'on reste sur la page de login
    await expect(page).toHaveURL(/\/login/);

    // Vérifier qu'un message d'erreur s'affiche
    await expect(page.locator('text=/invalid|incorrect|wrong|credentials/i')).toBeVisible();
  });
});
