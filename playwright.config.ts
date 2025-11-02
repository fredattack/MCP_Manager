import { defineConfig, devices } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/**
 * Configuration Playwright pour MCP Manager
 * Utilise PostgreSQL pour les tests (comme en production)
 * Voir https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  // Dossier contenant les tests E2E
  testDir: './tests/e2e',

  // Setup global exécuté UNE SEULE FOIS avant tous les tests
  globalSetup: path.resolve(__dirname, './tests/e2e/global-setup.ts'),

  // Timeout global par test (30s)
  timeout: 30 * 1000,

  // Nombre de tentatives en cas d'échec
  retries: process.env.CI ? 2 : 0,

  // Nombre de workers (parallélisation)
  // Pour PostgreSQL, limiter à 1 worker en local pour éviter les conflits de DB
  workers: 1,

  // Reporter pour les résultats
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['json', { outputFile: 'test-results/results.json' }],
    ['list'],
  ],

  // Options partagées pour tous les tests
  use: {
    // URL de base de l'application
    baseURL: 'http://localhost:3978',

    // Traces en cas d'échec uniquement
    trace: 'on-first-retry',

    // Screenshots en cas d'échec
    screenshot: 'only-on-failure',

    // Vidéo en cas d'échec
    video: 'retain-on-failure',

    // Timeout pour les actions (10s)
    actionTimeout: 10 * 1000,
  },

  // Configuration des différents navigateurs
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    // Décommenter si besoin de tester sur d'autres navigateurs
    // {
    //   name: 'firefox',
    //   use: { ...devices['Desktop Firefox'] },
    // },
    // {
    //   name: 'webkit',
    //   use: { ...devices['Desktop Safari'] },
    // },
  ],

  // Serveur de développement Laravel avec env=testing
  webServer: {
    command: 'APP_ENV=testing php artisan serve --port=3978',
    port: 3978,
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
    env: {
      APP_ENV: 'testing',
    },
  },
});
