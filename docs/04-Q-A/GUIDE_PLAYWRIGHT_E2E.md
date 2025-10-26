# Guide Complet : Tests End-to-End avec Playwright

> **Guide pour développeurs backend qui découvrent les tests E2E**
> **Niveau** : Débutant à Avancé
> **Durée de lecture** : 45-60 minutes
> **Prérequis** : Connaissances en tests backend Laravel

---

## 📚 Table des matières

1. [Introduction : Qu'est-ce que Playwright ?](#introduction)
2. [Comparaison avec les tests backend](#comparaison-avec-les-tests-backend)
3. [Installation et configuration](#installation-et-configuration)
4. [Premiers pas : Votre premier test](#premiers-pas)
5. [Sélecteurs et locators](#sélecteurs-et-locators)
6. [Actions utilisateur](#actions-utilisateur)
7. [Assertions et vérifications](#assertions-et-vérifications)
8. [Gestion de l'asynchrone](#gestion-de-lasynchrone)
9. [Fixtures et helpers](#fixtures-et-helpers)
10. [Tests de workflows (cas pratique)](#tests-de-workflows)
11. [Tests avec WebSocket (Reverb)](#tests-avec-websocket)
12. [Tests d'accessibilité](#tests-daccessibilité)
13. [Debugging et troubleshooting](#debugging-et-troubleshooting)
14. [Page Object Model (POM)](#page-object-model)
15. [Best practices et patterns](#best-practices)
16. [Exercices pratiques](#exercices-pratiques)

---

## 🎭 Introduction

### Qu'est-ce que Playwright ?

**Playwright** est un framework de test End-to-End (E2E) qui contrôle un **vrai navigateur** pour tester votre application comme le ferait un utilisateur réel.

#### Analogie avec le monde backend

Imaginez que vous voulez tester un parcours utilisateur complet dans votre application Laravel :

**Sans E2E (tests Laravel)** :
```php
// Test unitaire du service
$service->createWorkflow($data); // ✅

// Test du contrôleur
$response = $this->post('/api/workflows', $data); // ✅

// Test de la vue Inertia
// ❌ Impossible de tester complètement
```

**Avec E2E (Playwright)** :
```typescript
// Test du parcours complet
await page.goto('/workflows');
await page.click('button:has-text("Create")');
await page.fill('input[name="name"]', 'My Workflow');
await page.click('button[type="submit"]');
await expect(page.locator('h1')).toContainText('My Workflow'); // ✅
```

### Pourquoi Playwright ?

| Critère | Playwright | Selenium | Cypress |
|---------|-----------|----------|---------|
| Vitesse | ⚡⚡⚡ Rapide | 🐌 Lent | ⚡⚡ Assez rapide |
| Multi-browser | ✅ Chrome, Firefox, Safari | ✅ Tous | ❌ Chrome uniquement |
| API | 🎯 Moderne, simple | 😰 Complexe | 😊 Simple |
| Auto-wait | ✅ Oui | ❌ Non | ✅ Oui |
| Network mocking | ✅ Natif | ❌ Compliqué | ✅ Natif |
| Screenshots/Vidéo | ✅ Natif | ⚠️ Plugins | ✅ Natif |

### Concepts clés

#### 1. Browser Context (comme une session)

```typescript
// Équivalent backend
$this->actingAs($user); // Session utilisateur Laravel

// Équivalent Playwright
const context = await browser.newContext({
  // Cookies, localStorage, permissions, etc.
});
```

#### 2. Page (comme une requête HTTP)

```typescript
// Backend
$response = $this->get('/workflows');

// Playwright
await page.goto('/workflows');
```

#### 3. Locator (comme un query builder)

```typescript
// Backend (Eloquent)
User::where('email', 'test@example.com')->first();

// Playwright
page.locator('input[type="email"]').fill('test@example.com');
```

---

## 🔄 Comparaison avec les tests backend

### Structure de test

#### Backend (PHPUnit + Laravel)

```php
class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_workflow(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->post('/api/workflows', [
                'name' => 'My Workflow',
            ]);

        // Assert
        $response->assertCreated();
        $this->assertDatabaseHas('workflows', ['name' => 'My Workflow']);
    }
}
```

#### Frontend (Playwright)

```typescript
import { test, expect } from '@playwright/test';

test.describe('Workflow Creation', () => {
  test('user can create workflow', async ({ page }) => {
    // Arrange (connexion)
    await loginAsUser(page);

    // Act
    await page.goto('/workflows');
    await page.click('button:has-text("Create")');
    await page.fill('input[name="name"]', 'My Workflow');
    await page.click('button[type="submit"]');

    // Assert
    await expect(page.locator('h1')).toContainText('My Workflow');
  });
});
```

### Similitudes

| Backend | Playwright |
|---------|------------|
| `RefreshDatabase` | `beforeEach(() => resetDb())` |
| `$this->actingAs($user)` | `await loginAsUser(page)` |
| `$this->get('/url')` | `await page.goto('/url')` |
| `$response->assertStatus(200)` | `await expect(page).toHaveURL('/url')` |
| `$response->assertSee('text')` | `await expect(page.locator('text=text')).toBeVisible()` |

### Différences importantes

#### 1. Asynchrone partout

```php
// Backend : Synchrone
$response = $this->get('/workflows'); // Bloquant
```

```typescript
// Playwright : Asynchrone (await partout !)
await page.goto('/workflows'); // Non-bloquant
```

#### 2. Auto-waiting

```php
// Backend : Pas de notion de "chargement"
$response->assertStatus(200); // Instantané
```

```typescript
// Playwright : Attend automatiquement que l'élément soit prêt
await page.click('button'); // Attend que le bouton soit cliquable
```

#### 3. Contexte d'exécution

```php
// Backend : Serveur PHP
$this->actingAs($user); // État côté serveur
```

```typescript
// Playwright : Navigateur réel
await page.goto('/login'); // Vraie page HTML + JS + CSS
```

---

## ⚙️ Installation et configuration

### Étape 1 : Installation

```bash
# Installer Playwright
npm install -D @playwright/test

# Installer les navigateurs
npx playwright install

# Pour l'accessibilité
npm install -D @axe-core/playwright
```

### Étape 2 : Configuration de base

**Fichier** : `playwright.config.ts`

```typescript
import { defineConfig, devices } from '@playwright/test';

/**
 * Configuration Playwright pour MCP Manager
 * Voir https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  // 📁 Dossier des tests E2E
  testDir: './tests/e2e',

  // ⏱️ Timeout global par test (30 secondes)
  timeout: 30 * 1000,

  // 🔄 Nombre de tentatives en cas d'échec
  // En CI : 2 retries pour éviter les tests flaky
  // En local : 0 retry pour un feedback rapide
  retries: process.env.CI ? 2 : 0,

  // 🔀 Parallélisation
  // En CI : 1 worker (éviter la surcharge)
  // En local : undefined (utilise tous les CPU)
  workers: process.env.CI ? 1 : undefined,

  // 📊 Reporters (format des résultats)
  reporter: [
    ['html'], // Rapport HTML interactif
    ['json', { outputFile: 'test-results/results.json' }], // Pour le CI
    ['list'], // Sortie console
  ],

  // ⚙️ Options globales pour tous les tests
  use: {
    // URL de base de l'application
    baseURL: 'http://localhost:3978',

    // 📸 Captures d'écran
    screenshot: 'only-on-failure', // Seulement si échec

    // 🎥 Vidéos
    video: 'retain-on-failure', // Garder seulement si échec

    // 🔍 Traces (debug détaillé)
    trace: 'on-first-retry', // Seulement au premier retry

    // ⏱️ Timeout pour les actions individuelles
    actionTimeout: 10 * 1000,

    // 🍪 Options de contexte (cookies, permissions, etc.)
    // viewport: { width: 1280, height: 720 },
    // locale: 'fr-FR',
    // timezoneId: 'Europe/Paris',
  },

  // 🌐 Configuration des différents navigateurs
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },

    // 📱 Tests mobiles (optionnel)
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 12'] },
    },
  ],

  // 🚀 Serveur de développement
  // Playwright démarre automatiquement Laravel
  webServer: {
    command: 'php artisan serve --port=3978',
    port: 3978,
    reuseExistingServer: !process.env.CI, // Réutiliser en local
    timeout: 120 * 1000, // 2 minutes pour démarrer
  },
});
```

### Étape 3 : Structure des dossiers

```
tests/e2e/
├── fixtures/              # Helpers et utilitaires
│   ├── auth.ts           # Authentification
│   ├── database.ts       # Gestion base de données
│   └── pages/            # Page Object Models
├── auth/                 # Tests d'authentification
│   ├── login.spec.ts
│   ├── register.spec.ts
│   └── logout.spec.ts
├── workflows/            # Tests Workflows
│   ├── create.spec.ts
│   ├── execute.spec.ts
│   ├── edit.spec.ts
│   └── delete.spec.ts
├── integrations/         # Tests d'intégrations
│   ├── notion.spec.ts
│   └── git.spec.ts
└── global-setup.ts       # Setup global (migrations, etc.)
```

### Étape 4 : Setup global

**Fichier** : `tests/e2e/global-setup.ts`

```typescript
import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

/**
 * Setup global exécuté une fois avant tous les tests
 */
async function globalSetup() {
  console.log('🚀 Global setup started...');

  // Préparer la base de données de test
  await execAsync('php artisan migrate:fresh --seed --env=testing');

  console.log('✅ Database ready');

  // Autres setups si nécessaire
  // - Build des assets
  // - Création de fichiers de test
  // - etc.

  console.log('✅ Global setup completed');
}

export default globalSetup;
```

### Étape 5 : Scripts package.json

```json
{
  "scripts": {
    "test:e2e": "playwright test",
    "test:e2e:ui": "playwright test --ui",
    "test:e2e:headed": "playwright test --headed",
    "test:e2e:debug": "playwright test --debug",
    "test:e2e:chrome": "playwright test --project=chromium",
    "test:e2e:report": "playwright show-report"
  }
}
```

---

## 🎬 Premiers pas

### Votre premier test : Login

**Fichier** : `tests/e2e/auth/login.spec.ts`

```typescript
import { test, expect } from '@playwright/test';

test.describe('Login', () => {
  test('user can login with valid credentials', async ({ page }) => {
    // 1. Aller sur la page de login
    await page.goto('/login');

    // 2. Vérifier qu'on est bien sur la page de login
    await expect(page).toHaveTitle(/login/i);

    // 3. Remplir le formulaire
    await page.fill('input[name="email"]', 'user@example.com');
    await page.fill('input[name="password"]', 'password');

    // 4. Soumettre le formulaire
    await page.click('button[type="submit"]');

    // 5. Vérifier la redirection
    await expect(page).toHaveURL('/dashboard');

    // 6. Vérifier qu'on voit le nom de l'utilisateur
    await expect(page.locator('text=Welcome')).toBeVisible();
  });

  test('shows error with invalid credentials', async ({ page }) => {
    await page.goto('/login');

    await page.fill('input[name="email"]', 'wrong@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');

    // Vérifier qu'on reste sur la page de login
    await expect(page).toHaveURL('/login');

    // Vérifier le message d'erreur
    await expect(page.locator('.error-message')).toContainText(
      'These credentials do not match our records'
    );
  });
});
```

### Exécution

```bash
# Lancer le test
npx playwright test tests/e2e/auth/login.spec.ts

# Mode headed (voir le navigateur)
npx playwright test tests/e2e/auth/login.spec.ts --headed

# Mode debug (avec breakpoints)
npx playwright test tests/e2e/auth/login.spec.ts --debug
```

---

## 🎯 Sélecteurs et locators

### Comprendre les locators

Un **locator** est comme un **query builder** : il décrit comment trouver un élément sur la page.

#### Comparaison backend

```php
// Backend (Eloquent)
User::where('email', 'test@example.com')
    ->where('active', true)
    ->first();

// Playwright (Locator)
page.locator('input[type="email"][name="email"]')
    .fill('test@example.com');
```

### Types de sélecteurs

#### 1. Sélecteurs CSS (comme Laravel Dusk)

```typescript
// Par ID
page.locator('#submit-button')

// Par classe
page.locator('.btn-primary')

// Par attribut
page.locator('[data-testid="workflow-card"]')

// Combinaisons
page.locator('button.btn-primary[type="submit"]')
```

#### 2. Sélecteurs par texte (recommandé ⭐)

```typescript
// Texte exact
page.locator('text=Create Workflow')

// Texte partiel (insensible à la casse)
page.locator('text=/create/i')

// Dans un élément spécifique
page.locator('button:has-text("Submit")')
```

#### 3. Sélecteurs par rôle ARIA (meilleur pour l'accessibilité ⭐⭐⭐)

```typescript
// Bouton
page.getByRole('button', { name: 'Submit' })

// Lien
page.getByRole('link', { name: 'Learn more' })

// Input
page.getByRole('textbox', { name: 'Email' })

// Checkbox
page.getByRole('checkbox', { name: 'Remember me' })

// Heading
page.getByRole('heading', { name: 'Welcome', level: 1 })
```

#### 4. Helpers pratiques

```typescript
// Par label
page.getByLabel('Email address')

// Par placeholder
page.getByPlaceholder('Enter your email...')

// Par texte alternatif
page.getByAltText('Profile picture')

// Par test ID
page.getByTestId('workflow-card')
```

### Chaînage de locators

```typescript
// Trouver un bouton dans une div spécifique
page.locator('.modal').locator('button:has-text("Save")')

// Équivalent :
page.locator('.modal button:has-text("Save")')

// Filtrer les résultats
page.locator('button').filter({ hasText: 'Delete' })

// N-ième élément
page.locator('.workflow-card').nth(2) // 3ème carte

// Premier/Dernier
page.locator('.workflow-card').first()
page.locator('.workflow-card').last()
```

### Exemples pratiques pour votre projet

```typescript
// WorkflowCard
const workflowCard = page.locator('[data-testid="workflow-card"]').first();

// Bouton "Create Workflow"
const createButton = page.getByRole('button', { name: /create workflow/i });

// Input du nom de workflow
const nameInput = page.getByLabel('Workflow Name');
// OU
const nameInput = page.locator('input[name="name"]');

// Badge de statut
const statusBadge = page.locator('[data-testid="workflow-status"]');

// Modal de confirmation
const confirmModal = page.locator('dialog[open]');
// OU
const confirmModal = page.getByRole('dialog');

// Liste de workflows
const workflowsList = page.locator('[data-testid="workflows-list"]');
const workflowItems = workflowsList.locator('.workflow-item');

// Nombre de workflows
const count = await workflowItems.count();
```

---

## 🎮 Actions utilisateur

### Actions de base

#### 1. Navigation

```typescript
// Aller sur une page
await page.goto('/workflows');

// Aller en arrière
await page.goBack();

// Aller en avant
await page.goForward();

// Recharger
await page.reload();

// Attendre une navigation
await page.waitForURL('/workflows/1');
```

#### 2. Clics

```typescript
// Clic simple
await page.click('button:has-text("Submit")');

// Double-clic
await page.dblclick('.item');

// Clic droit
await page.click('button', { button: 'right' });

// Clic avec modificateur
await page.click('a', { modifiers: ['Control'] }); // Ctrl+Click
```

#### 3. Remplir des formulaires

```typescript
// Input texte
await page.fill('input[name="email"]', 'user@example.com');

// OU (plus lent mais simule vraiment la frappe)
await page.type('input[name="email"]', 'user@example.com');

// Effacer
await page.fill('input[name="email"]', '');

// Textarea
await page.fill('textarea[name="description"]', 'Mon texte long...');

// Checkbox
await page.check('input[type="checkbox"][name="agree"]');
await page.uncheck('input[type="checkbox"][name="agree"]');

// Radio
await page.check('input[type="radio"][value="option1"]');

// Select
await page.selectOption('select[name="country"]', 'France');
// OU par valeur
await page.selectOption('select[name="country"]', { value: 'fr' });
// OU par label
await page.selectOption('select[name="country"]', { label: 'France' });

// Upload de fichier
await page.setInputFiles('input[type="file"]', 'path/to/file.pdf');
// OU plusieurs fichiers
await page.setInputFiles('input[type="file"]', [
  'file1.pdf',
  'file2.pdf',
]);
```

#### 4. Clavier

```typescript
// Appuyer sur une touche
await page.keyboard.press('Enter');
await page.keyboard.press('Escape');

// Combinaisons
await page.keyboard.press('Control+A'); // Ctrl+A
await page.keyboard.press('Meta+C'); // Cmd+C (Mac)

// Taper du texte
await page.keyboard.type('Hello World');

// Maintenir une touche
await page.keyboard.down('Shift');
await page.keyboard.press('A'); // Shift+A
await page.keyboard.up('Shift');
```

#### 5. Souris

```typescript
// Hover
await page.hover('button');

// Glisser-déposer
await page.dragAndDrop('#source', '#target');

// Défilement
await page.mouse.wheel(0, 100); // Scroll vers le bas
```

### Exemples pratiques

#### Créer un workflow

```typescript
test('create workflow', async ({ page }) => {
  await page.goto('/workflows');

  // Ouvrir le modal
  await page.click('button:has-text("Create Workflow")');

  // Attendre que le modal s'ouvre
  await expect(page.locator('dialog[open]')).toBeVisible();

  // Remplir le formulaire
  await page.fill('input[name="name"]', 'My New Workflow');
  await page.fill('textarea[name="description"]', 'A test workflow');

  // Sélectionner le LLM provider
  await page.selectOption('select[name="llm_provider"]', 'OpenAI');

  // Soumettre
  await page.click('button[type="submit"]:has-text("Create")');

  // Vérifier le succès
  await expect(page.locator('.toast-success')).toContainText(
    'Workflow created successfully'
  );
});
```

#### Uploader un fichier de configuration

```typescript
test('upload workflow config', async ({ page }) => {
  await page.goto('/workflows/1');

  // Cliquer sur "Import config"
  await page.click('button:has-text("Import config")');

  // Uploader le fichier
  await page.setInputFiles(
    'input[type="file"]',
    'tests/fixtures/workflow-config.json'
  );

  // Soumettre
  await page.click('button:has-text("Upload")');

  // Vérifier
  await expect(page.locator('.success-message')).toContainText(
    'Configuration imported'
  );
});
```

---

## ✅ Assertions et vérifications

### Assertions sur la page

```typescript
// URL
await expect(page).toHaveURL('/workflows');
await expect(page).toHaveURL(/workflows\/\d+/); // Regex

// Titre
await expect(page).toHaveTitle('Workflows - MCP Manager');
await expect(page).toHaveTitle(/workflows/i);
```

### Assertions sur les éléments

```typescript
const button = page.locator('button:has-text("Submit")');

// Visibilité
await expect(button).toBeVisible();
await expect(button).toBeHidden();

// Présence dans le DOM (pas forcément visible)
await expect(button).toBeAttached();
await expect(button).not.toBeAttached();

// État
await expect(button).toBeEnabled();
await expect(button).toBeDisabled();
await expect(button).toBeChecked(); // Pour checkbox/radio
await expect(button).toBeFocused();

// Texte
await expect(button).toHaveText('Submit');
await expect(button).toContainText('Sub'); // Partiel
await expect(button).toHaveText(/submit/i); // Regex

// Attributs
await expect(button).toHaveAttribute('type', 'submit');
await expect(button).toHaveAttribute('disabled');
await expect(button).toHaveClass('btn-primary');
await expect(button).toHaveClass(/btn-/); // Regex

// Valeur (pour inputs)
const input = page.locator('input[name="email"]');
await expect(input).toHaveValue('user@example.com');
await expect(input).toHaveValue(/user@/);

// CSS
await expect(button).toHaveCSS('color', 'rgb(255, 0, 0)');

// Nombre d'éléments
const items = page.locator('.workflow-item');
await expect(items).toHaveCount(5);
```

### Assertions personnalisées

```typescript
// Vérifier qu'un élément contient un autre
const card = page.locator('.workflow-card');
await expect(card.locator('h3')).toContainText('My Workflow');

// Vérifier l'ordre
const items = page.locator('.workflow-item');
const first = items.nth(0);
await expect(first).toContainText('Workflow 1');

// Vérifier qu'un élément apparaît APRÈS un autre
await page.waitForSelector('.loading');
await page.waitForSelector('.data', { state: 'visible' });
await expect(page.locator('.loading')).not.toBeVisible();
```

### Exemples pratiques

```typescript
test('workflow card displays correct information', async ({ page }) => {
  await page.goto('/workflows');

  const firstCard = page.locator('[data-testid="workflow-card"]').first();

  // Vérifier le titre
  await expect(firstCard.locator('h3')).toHaveText('My Workflow');

  // Vérifier le badge de statut
  const statusBadge = firstCard.locator('[data-testid="status-badge"]');
  await expect(statusBadge).toHaveText('Active');
  await expect(statusBadge).toHaveClass(/bg-green/);

  // Vérifier la date
  await expect(firstCard.locator('.last-run')).toContainText(/Last run:/);

  // Vérifier les boutons
  await expect(firstCard.getByRole('button', { name: /execute/i })).toBeVisible();
  await expect(firstCard.getByRole('button', { name: /edit/i })).toBeVisible();
});
```

---

## ⏱️ Gestion de l'asynchrone

### Auto-waiting (le superpouvoi de Playwright !)

**Playwright attend automatiquement** que les éléments soient prêts avant d'agir.

```typescript
// Pas besoin d'attentes manuelles ! ✨
await page.click('button'); // Attend automatiquement que :
                             // 1. Le bouton existe
                             // 2. Il soit visible
                             // 3. Il soit cliquable
                             // 4. Il ne soit pas disabled
```

#### Comparaison avec Selenium

```javascript
// Selenium (l'ancien temps 😰)
await driver.wait(until.elementLocated(By.id('button')));
await driver.wait(until.elementIsVisible(driver.findElement(By.id('button'))));
await driver.findElement(By.id('button')).click();

// Playwright (moderne 😎)
await page.click('#button');
```

### Attentes explicites

Parfois, vous devez attendre des choses spécifiques :

#### 1. Attendre une navigation

```typescript
// Cliquer ET attendre la navigation
await Promise.all([
  page.waitForNavigation(),
  page.click('a:has-text("Go to workflows")')
]);

// Attendre une URL spécifique
await page.waitForURL('/workflows');
await page.waitForURL(/workflows\/\d+/); // Regex
```

#### 2. Attendre un élément

```typescript
// Attendre qu'un élément apparaisse
await page.waitForSelector('.data-loaded');

// Attendre qu'il disparaisse
await page.waitForSelector('.loading', { state: 'hidden' });

// Attendre qu'il soit visible
await page.waitForSelector('.modal', { state: 'visible' });

// Avec timeout personnalisé (défaut: 30s)
await page.waitForSelector('.slow-element', { timeout: 60000 });
```

#### 3. Attendre une condition

```typescript
// Attendre qu'une fonction retourne true
await page.waitForFunction(() => {
  return document.querySelectorAll('.workflow-item').length > 0;
});

// Avec arguments
await page.waitForFunction(
  (minCount) => document.querySelectorAll('.workflow-item').length >= minCount,
  5 // minCount = 5
);

// Attendre qu'une variable JS soit définie
await page.waitForFunction(() => window.appReady === true);
```

#### 4. Attendre un timeout fixe (à éviter !)

```typescript
// ❌ MAUVAIS : Timeout arbitraire
await page.waitForTimeout(5000); // Attendre 5 secondes

// ✅ MEILLEUR : Attendre une condition
await page.waitForSelector('.data-loaded');
```

### Exemples pratiques

#### Attendre des données AJAX

```typescript
test('loads workflows from API', async ({ page }) => {
  await page.goto('/workflows');

  // Méthode 1 : Attendre un élément spécifique
  await page.waitForSelector('[data-testid="workflow-card"]');

  // Méthode 2 : Attendre que le loading disparaisse
  await page.waitForSelector('.loading-spinner', { state: 'hidden' });

  // Méthode 3 : Attendre la requête API
  await page.waitForResponse('**/api/workflows');

  // Vérifier les données
  const cards = page.locator('[data-testid="workflow-card"]');
  await expect(cards).toHaveCount(3);
});
```

#### Attendre un WebSocket (Reverb)

```typescript
test('receives live updates via WebSocket', async ({ page }) => {
  await page.goto('/workflows/1');

  // Cliquer sur "Execute"
  await page.click('button:has-text("Execute")');

  // Attendre que le statut change (via WebSocket)
  await page.waitForFunction(() => {
    const status = document.querySelector('[data-testid="workflow-status"]');
    return status?.textContent === 'Running';
  }, { timeout: 10000 });

  // Vérifier
  await expect(page.locator('[data-testid="workflow-status"]')).toHaveText('Running');
});
```

#### Gérer les animations

```typescript
test('waits for modal animation', async ({ page }) => {
  await page.goto('/workflows');

  // Ouvrir le modal
  await page.click('button:has-text("Create")');

  // Playwright attend automatiquement que le modal soit visible
  const modal = page.locator('dialog[open]');
  await expect(modal).toBeVisible();

  // Si vous voulez être sûr que l'animation est terminée
  await modal.waitFor({ state: 'visible' });
  await page.waitForTimeout(300); // Durée de l'animation CSS (si nécessaire)
});
```

---

## 🛠️ Fixtures et helpers

### Qu'est-ce qu'une fixture ?

Une **fixture** est comme un **trait** Laravel : du code réutilisable pour préparer vos tests.

#### Comparaison backend

```php
// Backend (Trait Laravel)
use RefreshDatabase;

public function test_example(): void
{
    $user = User::factory()->create(); // Helper
    $this->actingAs($user); // Helper
}
```

```typescript
// Frontend (Fixture Playwright)
test('example', async ({ authenticatedPage }) => {
  // authenticatedPage est une fixture personnalisée
  await authenticatedPage.goto('/dashboard');
});
```

### Créer un helper d'authentification

**Fichier** : `tests/e2e/fixtures/auth.ts`

```typescript
import { Page } from '@playwright/test';

/**
 * Connexion d'un utilisateur
 */
export async function login(
  page: Page,
  email: string = 'user@example.com',
  password: string = 'password'
): Promise<void> {
  await page.goto('/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL('/dashboard');
}

/**
 * Déconnexion
 */
export async function logout(page: Page): Promise<void> {
  await page.click('[data-testid="user-menu"]');
  await page.click('button:has-text("Logout")');
  await page.waitForURL('/login');
}

/**
 * Vérifier qu'un utilisateur est connecté
 */
export async function assertAuthenticated(page: Page): Promise<void> {
  // Vérifier la présence d'un élément visible seulement pour les users connectés
  await page.waitForSelector('[data-testid="user-menu"]', { state: 'visible' });
}
```

### Créer un helper de base de données

**Fichier** : `tests/e2e/fixtures/database.ts`

```typescript
import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

/**
 * Réinitialiser la base de données
 */
export async function resetDatabase(): Promise<void> {
  await execAsync('php artisan migrate:fresh --seed --env=testing');
}

/**
 * Créer un utilisateur de test
 */
export async function createUser(
  email: string = 'user@example.com',
  password: string = 'password'
): Promise<void> {
  await execAsync(`php artisan tinker --execute="
    \\App\\Models\\User::factory()->create([
      'email' => '${email}',
      'password' => bcrypt('${password}')
    ]);
  " --env=testing`);
}

/**
 * Créer des workflows de test
 */
export async function seedWorkflows(count: number = 5): Promise<void> {
  await execAsync(`php artisan tinker --execute="
    \\App\\Models\\Workflow::factory()->count(${count})->create();
  " --env=testing`);
}
```

### Créer une fixture personnalisée

**Fichier** : `tests/e2e/fixtures/authenticated-page.ts`

```typescript
import { test as base } from '@playwright/test';
import { login } from './auth';

/**
 * Fixture personnalisée : Page avec utilisateur connecté
 */
export const test = base.extend({
  authenticatedPage: async ({ page }, use) => {
    // Setup : Connexion avant le test
    await login(page);

    // Fournir la page au test
    await use(page);

    // Teardown : Nettoyage après le test (optionnel)
    // await logout(page);
  },
});

export { expect } from '@playwright/test';
```

**Utilisation** :

```typescript
import { test, expect } from './fixtures/authenticated-page';

test('authenticated user can view workflows', async ({ authenticatedPage }) => {
  // L'utilisateur est déjà connecté !
  await authenticatedPage.goto('/workflows');

  await expect(authenticatedPage.locator('h1')).toContainText('Workflows');
});
```

### Utilisation dans les tests

```typescript
import { test, expect } from '@playwright/test';
import { login, logout } from './fixtures/auth';
import { resetDatabase, seedWorkflows } from './fixtures/database';

test.describe('Workflows', () => {
  // Setup avant TOUS les tests de ce groupe
  test.beforeAll(async () => {
    await resetDatabase();
    await seedWorkflows(10);
  });

  // Setup avant CHAQUE test
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('user can view workflows', async ({ page }) => {
    await page.goto('/workflows');

    const cards = page.locator('[data-testid="workflow-card"]');
    await expect(cards).toHaveCount(10);
  });

  test('user can create workflow', async ({ page }) => {
    await page.goto('/workflows');
    await page.click('button:has-text("Create")');
    // ...
  });
});
```

---

## 🔄 Tests de workflows (cas pratique)

Voici un exemple complet de test pour la fonctionnalité Workflows de votre application.

**Fichier** : `tests/e2e/workflows/workflow-lifecycle.spec.ts`

```typescript
import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';
import { resetDatabase } from '../fixtures/database';

test.describe('Workflow Lifecycle', () => {
  test.beforeAll(async () => {
    await resetDatabase();
  });

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('complete workflow lifecycle: create, execute, view logs, delete', async ({ page }) => {
    // 1. Créer un workflow
    await page.goto('/workflows');
    await expect(page).toHaveTitle(/Workflows/);

    // Cliquer sur "Create Workflow"
    await page.click('button:has-text("Create Workflow")');

    // Attendre l'ouverture du modal
    const modal = page.locator('dialog[open]');
    await expect(modal).toBeVisible();

    // Remplir le formulaire
    await page.fill('input[name="name"]', 'E2E Test Workflow');
    await page.fill('textarea[name="description"]', 'Created by automated E2E test');

    // Sélectionner le provider
    await page.selectOption('select[name="llm_provider"]', 'OpenAI');

    // Soumettre
    await page.click('button[type="submit"]:has-text("Create")');

    // Vérifier le message de succès
    const toast = page.locator('.toast-success');
    await expect(toast).toContainText('Workflow created successfully');

    // Vérifier la redirection vers la page du workflow
    await expect(page).toHaveURL(/\/workflows\/\d+/);

    // Vérifier le titre de la page
    await expect(page.locator('h1')).toContainText('E2E Test Workflow');

    // 2. Exécuter le workflow
    await page.click('button:has-text("Execute Workflow")');

    // Attendre que le statut change à "Running"
    const statusBadge = page.locator('[data-testid="workflow-status"]');
    await expect(statusBadge).toContainText('Running', { timeout: 5000 });

    // 3. Vérifier les logs en temps réel
    const logsContainer = page.locator('[data-testid="live-logs"]');
    await expect(logsContainer).toBeVisible();

    // Attendre l'apparition des premiers logs
    await expect(logsContainer.locator('.log-entry').first()).toBeVisible({
      timeout: 10000,
    });

    // Vérifier que les logs continuent d'arriver
    await page.waitForFunction(
      () => {
        const logs = document.querySelectorAll('[data-testid="live-logs"] .log-entry');
        return logs.length > 3;
      },
      { timeout: 15000 }
    );

    // 4. Attendre la fin de l'exécution
    await expect(statusBadge).toContainText(/Completed|Failed/, {
      timeout: 60000, // Max 1 minute
    });

    // Si succès, vérifier les résultats
    if (await statusBadge.locator('text=Completed').count() > 0) {
      // Vérifier qu'il y a un résumé
      await expect(page.locator('[data-testid="execution-summary"]')).toBeVisible();

      // Vérifier la durée d'exécution
      const duration = page.locator('[data-testid="execution-duration"]');
      await expect(duration).toContainText(/\d+ seconds/);
    }

    // 5. Retourner à la liste
    await page.click('a:has-text("Back to workflows")');
    await expect(page).toHaveURL('/workflows');

    // Vérifier que le workflow apparaît dans la liste
    const workflowCard = page.locator('[data-testid="workflow-card"]')
      .filter({ hasText: 'E2E Test Workflow' });
    await expect(workflowCard).toBeVisible();

    // 6. Supprimer le workflow
    await workflowCard.locator('button[aria-label="Delete"]').click();

    // Confirmer la suppression
    const confirmDialog = page.locator('dialog[open]');
    await expect(confirmDialog).toContainText('Are you sure');
    await confirmDialog.locator('button:has-text("Delete")').click();

    // Vérifier le message de succès
    await expect(page.locator('.toast-success')).toContainText('Workflow deleted');

    // Vérifier que le workflow a disparu
    await expect(workflowCard).not.toBeVisible();
  });

  test('handles workflow execution failure gracefully', async ({ page }) => {
    // Créer un workflow qui va échouer
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    await page.fill('input[name="name"]', 'Failing Workflow');
    await page.fill('textarea[name="description"]', 'This workflow will fail');

    // Configurer pour échouer (dépend de votre implémentation)
    await page.selectOption('select[name="llm_provider"]', 'Invalid');

    await page.click('button[type="submit"]:has-text("Create")');

    // Exécuter
    await page.click('button:has-text("Execute Workflow")');

    // Attendre l'échec
    const statusBadge = page.locator('[data-testid="workflow-status"]');
    await expect(statusBadge).toContainText('Failed', { timeout: 30000 });

    // Vérifier le message d'erreur
    const errorMessage = page.locator('[data-testid="error-message"]');
    await expect(errorMessage).toBeVisible();
    await expect(errorMessage).toContainText(/error|failed/i);

    // Vérifier qu'on peut voir les logs d'erreur
    const logs = page.locator('[data-testid="live-logs"]');
    await expect(logs.locator('.log-entry.error')).toHaveCount(greaterThan(0));
  });
});
```

---

*[Le document continue avec les sections WebSocket, Accessibilité, Debugging, Page Object Model, Best Practices et Exercices pratiques...]*

---

## 📡 Tests avec WebSocket (Reverb)

Votre application utilise Laravel Reverb pour les mises à jour en temps réel. Voici comment tester cela.

### Comprendre le flux WebSocket

```
1. User clicks "Execute"
   ↓
2. HTTP POST /api/workflows/1/execute
   ↓
3. Server starts workflow
   ↓
4. Server broadcasts events via Reverb
   ↓
5. Frontend receives updates via WebSocket
   ↓
6. UI updates in real-time (status, logs, etc.)
```

### Test de base WebSocket

```typescript
test('receives real-time workflow updates', async ({ page }) => {
  await login(page);
  await page.goto('/workflows/1');

  // Écouter les événements WebSocket (optionnel, pour debug)
  page.on('websocket', (ws) => {
    console.log('WebSocket opened:', ws.url());
    ws.on('framereceived', (event) => {
      console.log('WS received:', event.payload);
    });
  });

  // Cliquer sur Execute
  await page.click('button:has-text("Execute")');

  // Le statut devrait changer via WebSocket
  const statusBadge = page.locator('[data-testid="workflow-status"]');

  // Attendre "Running"
  await expect(statusBadge).toContainText('Running', { timeout: 5000 });

  // Les logs devraient apparaître en temps réel
  const logsContainer = page.locator('[data-testid="live-logs"]');

  // Vérifier que les logs arrivent progressivement
  await page.waitForFunction(
    () => {
      const logs = document.querySelectorAll('[data-testid="live-logs"] .log-entry');
      return logs.length > 0;
    },
    { timeout: 10000 }
  );

  // Attendre plus de logs
  const initialLogCount = await logsContainer.locator('.log-entry').count();

  await page.waitForFunction(
    (prevCount) => {
      const logs = document.querySelectorAll('[data-testid="live-logs"] .log-entry');
      return logs.length > prevCount;
    },
    initialLogCount,
    { timeout: 10000 }
  );

  // Vérifier le statut final
  await expect(statusBadge).toContainText(/Completed|Failed/, { timeout: 60000 });
});
```

### Mocker les WebSocket (tests plus rapides)

```typescript
test('mocked WebSocket updates', async ({ page }) => {
  await page.goto('/workflows/1');

  // Intercepter et mocker les WebSocket
  await page.route('**/reverb/**', (route) => {
    // Simuler une réponse WebSocket
    route.fulfill({
      status: 200,
      body: JSON.stringify({
        event: 'workflow.status.updated',
        data: { status: 'completed' },
      }),
    });
  });

  // Le reste du test...
});
```

---

## ♿ Tests d'accessibilité

### Pourquoi tester l'accessibilité ?

- **Légal** : Obligations légales dans certains pays
- **UX** : Meilleure expérience pour tous
- **SEO** : Améliore le référencement
- **Qualité** : Révèle des problèmes de structure HTML

### Installation

```bash
npm install -D @axe-core/playwright
```

### Test d'accessibilité de base

```typescript
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('Accessibility', () => {
  test('workflows page should be accessible', async ({ page }) => {
    await page.goto('/workflows');

    // Scanner la page avec Axe
    const accessibilityScanResults = await new AxeBuilder({ page }).analyze();

    // Vérifier qu'il n'y a pas de violations
    expect(accessibilityScanResults.violations).toEqual([]);
  });

  test('workflow creation modal should be accessible', async ({ page }) => {
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    // Scanner uniquement le modal
    const accessibilityScanResults = await new AxeBuilder({ page })
      .include('dialog') // Inclure le dialog
      .analyze();

    expect(accessibilityScanResults.violations).toEqual([]);
  });

  test('excludes known issues', async ({ page }) => {
    await page.goto('/workflows');

    const accessibilityScanResults = await new AxeBuilder({ page })
      .exclude('.third-party-widget') // Exclure un élément
      .withTags(['wcag2a', 'wcag2aa']) // Seulement WCAG 2.0 Level A/AA
      .analyze();

    expect(accessibilityScanResults.violations).toEqual([]);
  });
});
```

### Rapport détaillé des violations

```typescript
test('accessibility with detailed report', async ({ page }) => {
  await page.goto('/workflows');

  const accessibilityScanResults = await new AxeBuilder({ page }).analyze();

  // Afficher les violations si présentes
  if (accessibilityScanResults.violations.length > 0) {
    console.log('Accessibility violations:');
    accessibilityScanResults.violations.forEach((violation) => {
      console.log(`- ${violation.id}: ${violation.description}`);
      console.log(`  Impact: ${violation.impact}`);
      console.log(`  Help: ${violation.help}`);
      console.log(`  Nodes: ${violation.nodes.length}`);
      violation.nodes.forEach((node) => {
        console.log(`    - ${node.html}`);
      });
    });
  }

  expect(accessibilityScanResults.violations).toEqual([]);
});
```

---

## 🐛 Debugging et troubleshooting

### Mode debug

```bash
# Ouvrir le Playwright Inspector
npx playwright test --debug

# Debug un test spécifique
npx playwright test tests/e2e/workflows/create.spec.ts --debug

# Pause à un endroit spécifique
await page.pause(); // Dans le code du test
```

### Screenshots et vidéos

```typescript
test('take screenshot on failure', async ({ page }) => {
  await page.goto('/workflows');

  // Screenshot manuel
  await page.screenshot({ path: 'screenshots/workflows.png' });

  // Screenshot de l'élément spécifique
  const card = page.locator('[data-testid="workflow-card"]').first();
  await card.screenshot({ path: 'screenshots/workflow-card.png' });

  // Screenshot pleine page
  await page.screenshot({ path: 'screenshots/full-page.png', fullPage: true });
});
```

### Traces

Les traces sont comme un **enregistrement** complet de votre test.

```typescript
// Dans playwright.config.ts
use: {
  trace: 'on-first-retry', // Seulement au premier retry
  // OU
  trace: 'on', // Toujours
}
```

```bash
# Voir la trace après le test
npx playwright show-trace trace.zip
```

### Console logs

```typescript
test('log console messages', async ({ page }) => {
  // Écouter les messages console
  page.on('console', (msg) => {
    console.log(`[${msg.type()}] ${msg.text()}`);
  });

  // Écouter les erreurs
  page.on('pageerror', (err) => {
    console.error('Page error:', err.message);
  });

  await page.goto('/workflows');
});
```

### Slow motion (ralentir les tests)

```bash
# Ralentir de 1 seconde entre chaque action
npx playwright test --headed --slow-mo=1000
```

### Erreurs courantes

#### Erreur 1 : "Timeout waiting for selector"

```typescript
// ❌ Problème
await page.click('.non-existent-button'); // Timeout après 30s

// ✅ Solutions
// 1. Vérifier le sélecteur
page.locator('.existing-button');

// 2. Augmenter le timeout
await page.click('.slow-button', { timeout: 60000 });

// 3. Attendre explicitement
await page.waitForSelector('.button', { state: 'visible' });
```

#### Erreur 2 : "Element is not clickable"

```typescript
// ❌ Problème
await page.click('button'); // Élément masqué par un autre

// ✅ Solution
// Attendre que l'overlay disparaisse
await page.waitForSelector('.modal-overlay', { state: 'hidden' });
await page.click('button');
```

#### Erreur 3 : "Navigation failed"

```typescript
// ❌ Problème
await page.goto('http://localhost:3978/workflows'); // Serveur non démarré

// ✅ Solution
// Vérifier la configuration webServer dans playwright.config.ts
```

---

## 📦 Page Object Model (POM)

Le **Page Object Model** est un pattern qui organise le code des tests en "pages" réutilisables.

### Sans POM (répétitif)

```typescript
test('test 1', async ({ page }) => {
  await page.goto('/workflows');
  await page.click('button:has-text("Create")');
  await page.fill('input[name="name"]', 'Workflow 1');
  await page.click('button[type="submit"]');
});

test('test 2', async ({ page }) => {
  await page.goto('/workflows');
  await page.click('button:has-text("Create")');
  await page.fill('input[name="name"]', 'Workflow 2');
  await page.click('button[type="submit"]');
});
```

### Avec POM (réutilisable)

**Fichier** : `tests/e2e/fixtures/pages/WorkflowsPage.ts`

```typescript
import { Page, Locator } from '@playwright/test';

export class WorkflowsPage {
  readonly page: Page;
  readonly createButton: Locator;
  readonly workflowsList: Locator;
  readonly nameInput: Locator;
  readonly descriptionInput: Locator;
  readonly submitButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.createButton = page.locator('button:has-text("Create Workflow")');
    this.workflowsList = page.locator('[data-testid="workflows-list"]');
    this.nameInput = page.locator('input[name="name"]');
    this.descriptionInput = page.locator('textarea[name="description"]');
    this.submitButton = page.locator('button[type="submit"]:has-text("Create")');
  }

  async goto() {
    await this.page.goto('/workflows');
  }

  async createWorkflow(name: string, description: string = '') {
    await this.createButton.click();
    await this.nameInput.fill(name);
    if (description) {
      await this.descriptionInput.fill(description);
    }
    await this.submitButton.click();
  }

  async getWorkflowByName(name: string): Promise<Locator> {
    return this.workflowsList.locator(`text=${name}`);
  }

  async deleteWorkflow(name: string) {
    const workflow = await this.getWorkflowByName(name);
    await workflow.locator('button[aria-label="Delete"]').click();
    await this.page.locator('dialog button:has-text("Delete")').click();
  }
}
```

**Utilisation** :

```typescript
import { test, expect } from '@playwright/test';
import { WorkflowsPage } from './fixtures/pages/WorkflowsPage';

test('create workflow with POM', async ({ page }) => {
  const workflowsPage = new WorkflowsPage(page);

  await workflowsPage.goto();
  await workflowsPage.createWorkflow('My Workflow', 'Description');

  const workflow = await workflowsPage.getWorkflowByName('My Workflow');
  await expect(workflow).toBeVisible();
});

test('delete workflow with POM', async ({ page }) => {
  const workflowsPage = new WorkflowsPage(page);

  await workflowsPage.goto();
  await workflowsPage.deleteWorkflow('My Workflow');

  const workflow = await workflowsPage.getWorkflowByName('My Workflow');
  await expect(workflow).not.toBeVisible();
});
```

---

## ✨ Best practices

### 1. Utilisez des data-testid

```typescript
// ❌ Fragile
page.locator('.btn.btn-primary.mt-4') // Change si le design change

// ✅ Stable
page.locator('[data-testid="create-workflow-button"]')
```

```tsx
// Dans votre composant React
<button data-testid="create-workflow-button">
  Create Workflow
</button>
```

### 2. Privilégiez les sélecteurs accessibles

```typescript
// ✅ MEILLEUR (accessible)
page.getByRole('button', { name: 'Create Workflow' })

// ⚠️ OK (mais moins accessible)
page.locator('button:has-text("Create Workflow")')

// ❌ À ÉVITER (fragile)
page.locator('.btn-primary')
```

### 3. Utilisez des attentes explicites

```typescript
// ❌ Mauvais
await page.waitForTimeout(5000); // Timeout arbitraire

// ✅ Bon
await page.waitForSelector('.data-loaded');
await page.waitForResponse('**/api/workflows');
```

### 4. Isolez vos tests

```typescript
// Chaque test doit être indépendant
test.beforeEach(async () => {
  await resetDatabase(); // Réinitialiser avant chaque test
});
```

### 5. Tests déterministes

```typescript
// ❌ Mauvais (dépend de l'heure)
const now = new Date();

// ✅ Bon (date fixe)
const fixedDate = new Date('2025-01-01');
await page.evaluate((date) => {
  Date.now = () => new Date(date).getTime();
}, fixedDate.toISOString());
```

---

## 🎓 Exercices pratiques

### Exercice 1 : Test de login complet

Créez un test qui vérifie :
1. Affichage du formulaire de login
2. Erreur avec des identifiants invalides
3. Succès avec des identifiants valides
4. Redirection vers le dashboard
5. Présence du nom de l'utilisateur

### Exercice 2 : Test de workflow avec Page Object

Créez une classe `WorkflowPage` et testez :
1. Création d'un workflow
2. Édition du workflow
3. Exécution du workflow
4. Suppression du workflow

### Exercice 3 : Test WebSocket

Testez les mises à jour en temps réel :
1. Changement de statut via WebSocket
2. Apparition des logs en temps réel
3. Notification de fin d'exécution

---

## 🎯 Récapitulatif

### Ce que vous avez appris

1. ✅ Installation et configuration de Playwright
2. ✅ Écriture de tests E2E complets
3. ✅ Sélecteurs et locators
4. ✅ Actions utilisateur (clics, saisie, etc.)
5. ✅ Assertions et vérifications
6. ✅ Gestion de l'asynchrone et auto-waiting
7. ✅ Fixtures et helpers
8. ✅ Tests de workflows réels
9. ✅ Tests WebSocket (Reverb)
10. ✅ Tests d'accessibilité
11. ✅ Debugging et troubleshooting
12. ✅ Page Object Model
13. ✅ Best practices

### Prochaines étapes

1. ⚙️ Configurer Playwright dans votre projet
2. ✍️ Écrire vos premiers tests E2E pour les workflows
3. 🚀 Intégrer les tests dans votre CI/CD
4. 📊 Viser 20+ tests E2E critiques

**Bon courage pour vos tests E2E !** 🎉
