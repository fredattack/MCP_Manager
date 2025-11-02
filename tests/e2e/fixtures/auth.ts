import { Page } from '@playwright/test';

/**
 * Connexion d'un utilisateur
 * Utilise les sélecteurs par rôle (best practice Playwright)
 */
export async function login(
  page: Page,
  email: string = 'info@hddev.be',
  password: string = 'password'
): Promise<void> {
  // Aller sur la page de login et attendre que la page soit complètement chargée
  await page.goto('/login', { waitUntil: 'networkidle' });

  // Attendre que le formulaire soit complètement chargé et Inertia initialisé
  await page.waitForLoadState('domcontentloaded');

  // Attendre un peu pour que Inertia soit prêt (éviter race condition)
  await page.waitForTimeout(500);

  // Utiliser getByRole avec le label accessible (best practice)
  // Vider d'abord les champs (au cas où ils sont pré-remplis)
  const emailField = page.getByRole('textbox', { name: /email/i });
  await emailField.clear();
  await emailField.fill(email);

  const passwordField = page.getByRole('textbox', { name: /password/i });
  await passwordField.clear();
  await passwordField.fill(password);

  // Soumettre le formulaire et attendre la navigation
  await page.getByRole('button', { name: /log in/i }).click();

  // Attendre la redirection vers dashboard
  await page.waitForURL('/dashboard', { timeout: 30000 });
}

/**
 * Déconnexion
 */
export async function logout(page: Page): Promise<void> {
  // Cliquer sur le bouton utilisateur (avec initiales FM)
  await page.locator('button:has-text("FM")').click();

  // Cliquer sur le menuitem "Log out" (pas un button)
  await page.getByRole('menuitem', { name: /log out/i }).click();

  await page.waitForURL('/login');
}

/**
 * Vérifier qu'un utilisateur est connecté
 */
export async function assertAuthenticated(page: Page): Promise<void> {
  // Vérifier qu'on est sur une page protégée (dashboard) et que la navigation est visible
  await page.waitForURL(/\/(dashboard|workflows|integrations)/, { timeout: 10000 });
  await page.getByRole('heading', { name: /dashboard|workflows|integrations/i }).waitFor({ state: 'visible' });
}
