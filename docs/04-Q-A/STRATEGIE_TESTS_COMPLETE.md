# Stratégie Complète de Tests - MCP Manager

> **Document créé le** : 26 octobre 2025
> **Version** : 1.0
> **Objectif** : Définir une stratégie de tests exhaustive couvrant backend et frontend

---

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Infrastructure actuelle](#infrastructure-actuelle)
3. [Pyramide de tests](#pyramide-de-tests)
4. [Tests Backend (PHP/Laravel)](#tests-backend-phplarave)
5. [Tests Frontend (React/TypeScript)](#tests-frontend-reacttypescript)
6. [Tests End-to-End (E2E)](#tests-end-to-end-e2e)
7. [Stratégie de couverture](#stratégie-de-couverture)
8. [Standards et bonnes pratiques](#standards-et-bonnes-pratiques)
9. [CI/CD et automatisation](#cicd-et-automatisation)
10. [Roadmap d'amélioration](#roadmap-damélioration)

---

## 🎯 Vue d'ensemble

### Objectifs stratégiques

**Qualité** : Maintenir une couverture de tests >80% sur le code critique
**Confiance** : Permettre des déploiements rapides et sûrs
**Documentation** : Les tests servent de documentation vivante du comportement
**Régression** : Prévenir les régressions sur les fonctionnalités existantes

### Types de tests dans le projet

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│           🔺 E2E Tests (Playwright)                │
│              - Tests utilisateur                    │
│              - Tests d'intégration complète         │
│                                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│      🔶 Integration Tests (React Testing Library)  │
│         - Composants avec contexte                  │
│         - Flux utilisateur simplifiés               │
│         - Tests de hooks personnalisés              │
│                                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│  🟢 Feature Tests (PHPUnit + Laravel TestCase)     │
│     - Tests HTTP/API                                │
│     - Tests de flux métier complets                 │
│     - Tests d'autorisation                          │
│                                                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│ ⚪ Unit Tests (PHPUnit + Jest/Vitest)              │
│    - Services, modèles, helpers                     │
│    - Composants React isolés                        │
│    - Fonctions utilitaires                          │
│    - Validations, transformations                   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 🏗️ Infrastructure actuelle

### Backend (Laravel + PHPUnit)

**Configuration** : `phpunit.xml`

```xml
Test Suites:
- Unit Tests      → tests/Unit/
- Feature Tests   → tests/Feature/

Base de données   → SQLite :memory:
Cache             → Array driver
Queue             → Sync (pas d'async en test)
```

**Groupes de tests organisés** :
- Par fonctionnalité : `auth`, `git`, `workflow`, `llm`, `notion`, `todoist`
- Par type : `unit`, `feature`
- Par sprint : `sprint2`

**Statistiques actuelles** :
- ✅ 40+ tests PHP
- ✅ Organisation par domaine métier
- ✅ Factories pour tous les modèles principaux
- ✅ 100% de passage sur Sprint 2

### Frontend (React + Jest/Testing Library)

**Configuration détectée** :
- `@testing-library/react` pour les tests de composants
- `@testing-library/user-event` pour simuler les interactions
- Jest comme test runner (configuré dans package.json)

**Tests existants** :
```
resources/js/
├── components/ai/chat/__tests__/
│   ├── ChatInput.test.tsx      ✅
│   └── MessageItem.test.tsx    ✅
├── hooks/ai/__tests__/
│   └── use-claude-chat.test.ts ✅
└── lib/nlp/__tests__/
    └── nlp-engine.test.ts      ✅
```

**État actuel** : ⚠️ Couverture partielle, nécessite extension

### E2E (Playwright)

**Statut** : ⚠️ Infrastructure installée mais **pas de tests E2E existants**

**Packages installés** :
- `@playwright/test` v1.52.0
- `playwright` v1.42.1
- `@axe-core/playwright` v4.11.0 (accessibilité)

**Configuration manquante** :
- ❌ Pas de `playwright.config.ts`
- ❌ Pas de tests `.spec.ts`
- ❌ Pas de structure de dossiers pour E2E

---

## 🔺 Pyramide de tests

### Distribution recommandée

```
Type de test         | Quantité | Vitesse    | Coût maintenance
---------------------|----------|------------|------------------
Unit (70%)           | ████████ | Rapide     | Faible
Integration (20%)    | ███      | Moyenne    | Moyen
Feature (15%)        | ██       | Moyenne    | Moyen
E2E (5%)             | █        | Lente      | Élevé
```

### Quand utiliser quel type de test ?

#### Tests unitaires (70%)
✅ **Utiliser pour** :
- Services métier (`NotionService`, `GitCloneService`)
- Modèles et leurs méthodes (`User::hasActiveIntegration()`)
- Helpers et utilitaires (`StringUtils`)
- Transformations de données
- Validations

❌ **Ne pas utiliser pour** :
- Tester des composants React complets
- Tester des flux utilisateur
- Tester l'intégration entre plusieurs services

#### Tests d'intégration frontend (20%)
✅ **Utiliser pour** :
- Composants React avec contexte
- Hooks personnalisés avec état
- Formulaires complets
- Flux de navigation simples

#### Tests de fonctionnalité Laravel (15%)
✅ **Utiliser pour** :
- Endpoints API
- Flux d'authentification
- Autorisation et policies
- Webhooks
- Jobs et événements

#### Tests E2E (5%)
✅ **Utiliser pour** :
- Parcours utilisateur critiques
- Intégration complète frontend + backend
- Tests cross-browser
- Tests d'accessibilité

---

## 🔧 Tests Backend (PHP/Laravel)

### Structure actuelle

```
tests/
├── TestCase.php                           # Base class personnalisée
├── Unit/
│   ├── Actions/DailyPlanning/            # Tests d'actions
│   ├── Jobs/                             # Tests de jobs
│   ├── Models/                           # Tests de modèles
│   ├── Services/
│   │   ├── Git/                          # Services Git
│   │   ├── LLM/                          # Services LLM (Sprint 2)
│   │   ├── CryptoService                 # Cryptographie
│   │   ├── DailyPlanningService
│   │   └── NotionService
│   └── StringUtilsTest.php
│
└── Feature/
    ├── Auth/                              # Tests d'authentification
    ├── Git/                               # Tests Git (OAuth, clone, webhooks)
    ├── Workflow/                          # Tests Workflow (Sprint 2)
    ├── Http/Controllers/                  # Tests de contrôleurs
    ├── Settings/                          # Tests de paramètres
    ├── DashboardTest.php
    ├── IntegrationsTest.php
    ├── McpServerManagementTest.php
    ├── NotionIntegrationTest.php
    └── NotionTest.php
```

### Bonnes pratiques actuelles

✅ **Ce qui est bien fait** :
1. Organisation claire par domaine métier
2. Séparation Unit/Feature respectée
3. Utilisation de groupes PHPUnit
4. Base de données SQLite en mémoire (rapide)
5. Factories pour génération de données
6. Tests d'autorisation systématiques

### Standards de tests backend

#### Anatomie d'un test de qualité

```php
<?php

namespace Tests\Feature\Workflow;

use Tests\TestCase;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group workflow
 * @group feature
 * @group sprint2
 */
class WorkflowApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_only_authenticated_user_workflows(): void
    {
        // Arrange (Préparer)
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userWorkflows = Workflow::factory()
            ->count(3)
            ->create(['user_id' => $user->id]);

        Workflow::factory()
            ->count(2)
            ->create(['user_id' => $otherUser->id]);

        // Act (Agir)
        $response = $this->actingAs($user)
            ->getJson('/api/workflows');

        // Assert (Vérifier)
        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', $userWorkflows[0]->id);
    }
}
```

#### Checklist pour un test backend

- [ ] **Arrange-Act-Assert** : Structure claire en 3 parties
- [ ] **Isolation** : Utilise `RefreshDatabase` pour isolation
- [ ] **Groupes** : Annotate avec `@group`
- [ ] **Nommage** : Nom descriptif en snake_case ou camelCase
- [ ] **Happy path** : Teste le cas de succès
- [ ] **Edge cases** : Teste les cas limites
- [ ] **Authorization** : Teste les permissions
- [ ] **Validation** : Teste les erreurs de validation
- [ ] **Factories** : Utilise les factories, pas les données manuelles

### Commandes de tests backend

```bash
# Tous les tests
php artisan test

# Tests d'un groupe spécifique
php artisan test --group=workflow

# Tests d'un fichier
php artisan test tests/Feature/Workflow/WorkflowApiTest.php

# Test spécifique
php artisan test --filter=test_can_create_workflow

# Avec couverture (nécessite Xdebug)
php artisan test --coverage

# Exclure des groupes
php artisan test --exclude-group=notion,git
```

---

## ⚛️ Tests Frontend (React/TypeScript)

### État actuel de la couverture

**Points forts** ✅ :
- Tests de composants d'interaction (ChatInput)
- Tests de hooks personnalisés (use-claude-chat)
- Tests de logique métier (nlp-engine)

**Zones à améliorer** ⚠️ :
- Pas de tests pour les composants Workflow
- Pas de tests pour les intégrations (Notion, Git)
- Pas de tests pour les pages Inertia
- Pas de tests pour les composants UI génériques

### Configuration recommandée

#### Option 1 : Jest (actuel)

**Avantages** :
- Déjà configuré
- Large écosystème
- Mocking puissant

**Configuration à ajouter** : `jest.config.js`

```javascript
export default {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  roots: ['<rootDir>/resources/js'],
  testMatch: ['**/__tests__/**/*.test.{ts,tsx}'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1',
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy',
  },
  setupFilesAfterEnv: ['<rootDir>/resources/js/setupTests.ts'],
  collectCoverageFrom: [
    'resources/js/**/*.{ts,tsx}',
    '!resources/js/**/*.d.ts',
    '!resources/js/**/__tests__/**',
  ],
  coverageThresholds: {
    global: {
      branches: 70,
      functions: 70,
      lines: 70,
      statements: 70,
    },
  },
};
```

#### Option 2 : Vitest (recommandé pour 2025)

**Avantages** :
- Plus rapide que Jest
- Compatible Vite (déjà utilisé)
- Meilleure intégration TypeScript
- API compatible Jest

**Configuration** : `vitest.config.ts`

```typescript
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: './resources/js/setupTests.ts',
    include: ['resources/js/**/*.{test,spec}.{ts,tsx}'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['resources/js/**/*.{ts,tsx}'],
      exclude: [
        'resources/js/**/*.d.ts',
        'resources/js/**/__tests__/**',
        'resources/js/types/**',
      ],
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
});
```

### Types de tests frontend

#### 1. Tests de composants UI purs

**Exemple** : Composant `Button`

```tsx
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Button } from '@/components/ui/Button';

describe('Button', () => {
  it('renders with correct text', () => {
    render(<Button>Click me</Button>);
    expect(screen.getByRole('button', { name: /click me/i })).toBeInTheDocument();
  });

  it('calls onClick when clicked', async () => {
    const handleClick = jest.fn();
    render(<Button onClick={handleClick}>Click me</Button>);

    await userEvent.click(screen.getByRole('button'));

    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  it('is disabled when disabled prop is true', () => {
    render(<Button disabled>Click me</Button>);
    expect(screen.getByRole('button')).toBeDisabled();
  });
});
```

#### 2. Tests de hooks personnalisés

**Exemple** : Hook `useWorkflows`

```tsx
import { renderHook, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useWorkflows } from '@/hooks/use-workflows';

const createWrapper = () => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: { retry: false },
    },
  });

  return ({ children }: { children: React.ReactNode }) => (
    <QueryClientProvider client={queryClient}>
      {children}
    </QueryClientProvider>
  );
};

describe('useWorkflows', () => {
  it('fetches workflows successfully', async () => {
    const { result } = renderHook(() => useWorkflows(), {
      wrapper: createWrapper(),
    });

    await waitFor(() => expect(result.current.isSuccess).toBe(true));

    expect(result.current.data).toBeDefined();
    expect(Array.isArray(result.current.data)).toBe(true);
  });
});
```

#### 3. Tests de formulaires

**Exemple** : Formulaire de création de workflow

```tsx
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { CreateWorkflowForm } from '@/components/workflows/CreateWorkflowForm';

describe('CreateWorkflowForm', () => {
  it('submits form with valid data', async () => {
    const onSubmit = jest.fn();
    render(<CreateWorkflowForm onSubmit={onSubmit} />);

    const user = userEvent.setup();

    // Remplir le formulaire
    await user.type(
      screen.getByLabelText(/name/i),
      'My New Workflow'
    );

    await user.type(
      screen.getByLabelText(/description/i),
      'This is a test workflow'
    );

    // Soumettre
    await user.click(screen.getByRole('button', { name: /create/i }));

    // Vérifier
    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        name: 'My New Workflow',
        description: 'This is a test workflow',
      });
    });
  });

  it('shows validation errors for empty name', async () => {
    render(<CreateWorkflowForm />);

    const user = userEvent.setup();
    await user.click(screen.getByRole('button', { name: /create/i }));

    expect(await screen.findByText(/name is required/i)).toBeInTheDocument();
  });
});
```

#### 4. Tests de pages Inertia

**Exemple** : Page Workflows Index

```tsx
import { render, screen } from '@testing-library/react';
import { InertiaPageProps } from '@/types';
import WorkflowsIndex from '@/pages/Workflows/Index';

// Mock Inertia
jest.mock('@inertiajs/react', () => ({
  ...jest.requireActual('@inertiajs/react'),
  usePage: () => ({
    props: {
      auth: {
        user: { id: 1, name: 'Test User' },
      },
    },
  }),
}));

describe('Workflows Index Page', () => {
  it('renders workflows list', () => {
    const props = {
      workflows: [
        { id: 1, name: 'Workflow 1', status: 'active' },
        { id: 2, name: 'Workflow 2', status: 'inactive' },
      ],
    };

    render(<WorkflowsIndex {...props} />);

    expect(screen.getByText('Workflow 1')).toBeInTheDocument();
    expect(screen.getByText('Workflow 2')).toBeInTheDocument();
  });

  it('shows empty state when no workflows', () => {
    render(<WorkflowsIndex workflows={[]} />);

    expect(screen.getByText(/no workflows yet/i)).toBeInTheDocument();
  });
});
```

### Commandes de tests frontend

```bash
# Avec Jest
npm run test                    # Run all tests
npm run test:watch             # Watch mode
npm run test:coverage          # With coverage

# Avec Vitest (recommandé)
npx vitest                     # Run all tests
npx vitest --watch            # Watch mode
npx vitest --ui               # UI mode (très utile!)
npx vitest --coverage         # With coverage
```

---

## 🌐 Tests End-to-End (E2E)

### Configuration Playwright

**Fichier** : `playwright.config.ts` (à créer)

```typescript
import { defineConfig, devices } from '@playwright/test';

/**
 * Configuration Playwright pour MCP Manager
 * Voir https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  // Dossier contenant les tests E2E
  testDir: './tests/e2e',

  // Timeout global par test (30s)
  timeout: 30 * 1000,

  // Nombre de tentatives en cas d'échec
  retries: process.env.CI ? 2 : 0,

  // Nombre de workers (parallélisation)
  workers: process.env.CI ? 1 : undefined,

  // Reporter pour les résultats
  reporter: [
    ['html'],
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
    {
      name: 'firefox',
      use: { ...devices['Desktop Firefox'] },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
    // Tests mobiles
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 12'] },
    },
  ],

  // Serveur de développement
  webServer: {
    command: 'php artisan serve --port=3978',
    port: 3978,
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
  },
});
```

### Structure des tests E2E

```
tests/e2e/
├── fixtures/                    # Fixtures et helpers
│   ├── auth.ts                 # Helper d'authentification
│   └── database.ts             # Helper de base de données
├── auth/                        # Tests d'authentification
│   ├── login.spec.ts
│   ├── register.spec.ts
│   └── password-reset.spec.ts
├── workflows/                   # Tests Workflows
│   ├── create-workflow.spec.ts
│   ├── execute-workflow.spec.ts
│   └── workflow-live-logs.spec.ts
├── integrations/                # Tests d'intégrations
│   ├── notion-setup.spec.ts
│   └── git-oauth.spec.ts
└── accessibility/               # Tests d'accessibilité
    └── a11y.spec.ts
```

### Exemple de test E2E complet

**Fichier** : `tests/e2e/workflows/create-workflow.spec.ts`

```typescript
import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';

test.describe('Workflow Creation', () => {
  test.beforeEach(async ({ page }) => {
    // Authentification avant chaque test
    await login(page, 'user@example.com', 'password');
  });

  test('should create a new workflow successfully', async ({ page }) => {
    // Navigation vers la page des workflows
    await page.goto('/workflows');
    await expect(page).toHaveTitle(/Workflows/);

    // Cliquer sur le bouton "Créer un workflow"
    await page.click('button:has-text("Create Workflow")');

    // Attendre l'ouverture du modal
    await expect(page.locator('dialog')).toBeVisible();

    // Remplir le formulaire
    await page.fill('input[name="name"]', 'Test Workflow');
    await page.fill('textarea[name="description"]', 'This is a test workflow');

    // Sélectionner le LLM provider
    await page.click('select[name="llm_provider"]');
    await page.click('option:has-text("OpenAI")');

    // Soumettre le formulaire
    await page.click('button[type="submit"]:has-text("Create")');

    // Vérifier le message de succès
    await expect(page.locator('.toast-success')).toContainText(
      'Workflow created successfully'
    );

    // Vérifier la redirection vers la liste
    await expect(page).toHaveURL(/\/workflows\/\d+/);

    // Vérifier que le workflow apparaît
    await expect(page.locator('h1')).toContainText('Test Workflow');
  });

  test('should show validation errors for empty name', async ({ page }) => {
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    // Soumettre sans remplir
    await page.click('button[type="submit"]:has-text("Create")');

    // Vérifier les erreurs de validation
    await expect(page.locator('.error-message')).toContainText(
      'The name field is required'
    );
  });

  test('should cancel workflow creation', async ({ page }) => {
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    // Remplir partiellement
    await page.fill('input[name="name"]', 'Test');

    // Annuler
    await page.click('button:has-text("Cancel")');

    // Vérifier que le modal est fermé
    await expect(page.locator('dialog')).not.toBeVisible();

    // Vérifier qu'on est toujours sur la page des workflows
    await expect(page).toHaveURL('/workflows');
  });
});
```

### Test d'exécution de workflow avec WebSocket

```typescript
import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';

test.describe('Workflow Execution', () => {
  test('should execute workflow and show live logs', async ({ page }) => {
    await login(page, 'user@example.com', 'password');

    // Aller sur un workflow existant
    await page.goto('/workflows/1');

    // Cliquer sur "Execute"
    await page.click('button:has-text("Execute Workflow")');

    // Attendre que le statut passe à "running"
    await expect(page.locator('[data-testid="workflow-status"]')).toContainText(
      'Running',
      { timeout: 5000 }
    );

    // Vérifier que les logs apparaissent en temps réel
    const logsContainer = page.locator('[data-testid="live-logs"]');

    await expect(logsContainer).toContainText('Starting workflow execution');

    // Attendre l'apparition de plus de logs (WebSocket)
    await page.waitForFunction(
      (selector) => {
        const logs = document.querySelector(selector);
        return logs && logs.children.length > 3;
      },
      '[data-testid="live-logs"]',
      { timeout: 10000 }
    );

    // Vérifier le changement de statut final
    await expect(
      page.locator('[data-testid="workflow-status"]')
    ).toContainText(/completed|failed/, { timeout: 30000 });
  });
});
```

### Test d'accessibilité avec Axe

```typescript
import { test, expect } from '@playwright/test';
import { injectAxe, checkA11y } from '@axe-core/playwright';

test.describe('Accessibility Tests', () => {
  test('workflows page should be accessible', async ({ page }) => {
    await page.goto('/workflows');

    // Injecter Axe dans la page
    await injectAxe(page);

    // Vérifier l'accessibilité
    await checkA11y(page, undefined, {
      detailedReport: true,
      detailedReportOptions: {
        html: true,
      },
    });
  });

  test('workflow creation modal should be accessible', async ({ page }) => {
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    await injectAxe(page);

    // Vérifier uniquement le modal
    await checkA11y(page, 'dialog', {
      detailedReport: true,
    });
  });
});
```

### Fixtures et helpers

**Fichier** : `tests/e2e/fixtures/auth.ts`

```typescript
import { Page } from '@playwright/test';

export async function login(
  page: Page,
  email: string,
  password: string
): Promise<void> {
  await page.goto('/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');

  // Attendre la redirection
  await page.waitForURL('/dashboard');
}

export async function createUser(
  email: string = 'test@example.com',
  password: string = 'password'
): Promise<{ email: string; password: string }> {
  // Utiliser Artisan pour créer un utilisateur de test
  // Cette fonction peut être appelée dans beforeAll()
  return { email, password };
}
```

**Fichier** : `tests/e2e/fixtures/database.ts`

```typescript
import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

export async function resetDatabase(): Promise<void> {
  await execAsync('php artisan migrate:fresh --seed --env=testing');
}

export async function seedWorkflows(): Promise<void> {
  await execAsync('php artisan db:seed --class=WorkflowSeeder --env=testing');
}
```

### Commandes Playwright

```bash
# Installer les navigateurs
npx playwright install

# Lancer tous les tests E2E
npx playwright test

# Mode UI (très utile pour le développement)
npx playwright test --ui

# Lancer des tests spécifiques
npx playwright test tests/e2e/workflows

# Mode debug
npx playwright test --debug

# Générer un rapport HTML
npx playwright show-report

# Mode headed (voir le navigateur)
npx playwright test --headed

# Un seul navigateur
npx playwright test --project=chromium
```

### Patterns avancés Playwright

#### Page Object Model (POM)

```typescript
// tests/e2e/pages/WorkflowsPage.ts
import { Page, Locator } from '@playwright/test';

export class WorkflowsPage {
  readonly page: Page;
  readonly createButton: Locator;
  readonly workflowsList: Locator;

  constructor(page: Page) {
    this.page = page;
    this.createButton = page.locator('button:has-text("Create Workflow")');
    this.workflowsList = page.locator('[data-testid="workflows-list"]');
  }

  async goto() {
    await this.page.goto('/workflows');
  }

  async createWorkflow(name: string, description: string) {
    await this.createButton.click();
    await this.page.fill('input[name="name"]', name);
    await this.page.fill('textarea[name="description"]', description);
    await this.page.click('button[type="submit"]');
  }

  async getWorkflowByName(name: string): Promise<Locator> {
    return this.workflowsList.locator(`text=${name}`);
  }
}

// Utilisation dans les tests
import { WorkflowsPage } from './pages/WorkflowsPage';

test('create workflow with POM', async ({ page }) => {
  const workflowsPage = new WorkflowsPage(page);
  await workflowsPage.goto();
  await workflowsPage.createWorkflow('My Workflow', 'Description');

  const workflow = await workflowsPage.getWorkflowByName('My Workflow');
  await expect(workflow).toBeVisible();
});
```

---

## 📊 Stratégie de couverture

### Objectifs de couverture

| Couche              | Cible  | Critique |
|---------------------|--------|----------|
| Services backend    | 90%    | 100%     |
| Modèles             | 85%    | 95%      |
| Contrôleurs         | 80%    | 90%      |
| Composants React    | 75%    | 85%      |
| Hooks personnalisés | 90%    | 100%     |
| Utilitaires         | 95%    | 100%     |

### Priorisation

**P0 - Critique (100% de couverture)** :
- Authentification et autorisation
- Gestion des tokens et cryptographie
- Services de paiement (si applicable)
- Flux d'intégration OAuth

**P1 - Haute (>85% de couverture)** :
- Workflows et exécution
- Services LLM
- Gestion Git
- API endpoints

**P2 - Moyenne (>70% de couverture)** :
- Composants UI
- Helpers et utilitaires
- Pages Inertia

**P3 - Basse (>50% de couverture)** :
- Composants de présentation simples
- Pages statiques

---

## ✨ Standards et bonnes pratiques

### Principes généraux

#### 1. **Tests indépendants**
Chaque test doit pouvoir s'exécuter seul, dans n'importe quel ordre.

```typescript
// ✅ BON
test.beforeEach(() => {
  // Réinitialiser l'état pour CHAQUE test
  resetDatabase();
});

// ❌ MAUVAIS
test('first test', () => {
  createUser(); // Le second test va échouer
});

test('second test', () => {
  // Attend que l'utilisateur existe
  login();
});
```

#### 2. **Tests déterministes**
Un test doit toujours donner le même résultat.

```typescript
// ✅ BON
const fixedDate = new Date('2025-01-01');
jest.useFakeTimers().setSystemTime(fixedDate);

// ❌ MAUVAIS
const now = new Date(); // Chaque exécution donne un résultat différent
```

#### 3. **Tests lisibles**
Utiliser des noms descriptifs et une structure claire.

```typescript
// ✅ BON
test('should create workflow with valid data and return 201 status', () => {
  // ...
});

// ❌ MAUVAIS
test('test1', () => {
  // ...
});
```

#### 4. **Tests rapides**
Optimiser la vitesse d'exécution.

```php
// ✅ BON - SQLite en mémoire
'DB_CONNECTION' => 'sqlite',
'DB_DATABASE' => ':memory:',

// ❌ MAUVAIS - MySQL pour les tests
'DB_CONNECTION' => 'mysql',
```

#### 5. **Pas de logique complexe dans les tests**
Les tests doivent être simples.

```typescript
// ✅ BON
expect(result).toBe(10);

// ❌ MAUVAIS
const expected = calculateExpectedValue(input, config, settings);
expect(result).toBe(expected);
```

### Nommage des tests

#### Backend (PHP)
```php
// Style 1: it_verb_object
public function it_creates_workflow_with_valid_data(): void

// Style 2: test_verb_object
public function test_user_can_delete_own_workflow(): void

// Style 3: Descriptif complet
/** @test */
public function authenticated_users_can_only_see_their_own_workflows(): void
```

#### Frontend (TypeScript)
```typescript
// Style describe/it (BDD)
describe('WorkflowCard', () => {
  it('renders workflow name correctly', () => {});
  it('shows status badge based on workflow status', () => {});
});

// Style describe/test
describe('useWorkflows hook', () => {
  test('fetches workflows on mount', () => {});
  test('handles error state', () => {});
});
```

### Assertions

#### Backend (PHPUnit)
```php
// Assertions HTTP
$response->assertStatus(201);
$response->assertJson(['success' => true]);
$response->assertJsonPath('data.name', 'My Workflow');
$response->assertJsonCount(3, 'data');

// Assertions de base de données
$this->assertDatabaseHas('workflows', ['name' => 'My Workflow']);
$this->assertDatabaseMissing('workflows', ['id' => 999]);
$this->assertDatabaseCount('workflows', 5);

// Assertions de modèles
$this->assertInstanceOf(Workflow::class, $workflow);
$this->assertTrue($workflow->isActive());
$this->assertNull($user->deleted_at);

// Assertions d'événements
Event::assertDispatched(WorkflowCreated::class);
Queue::assertPushed(ProcessWorkflow::class);
```

#### Frontend (Jest/Vitest)
```typescript
// Assertions DOM
expect(screen.getByRole('button')).toBeInTheDocument();
expect(screen.getByText(/workflow/i)).toBeVisible();
expect(screen.queryByText('Loading')).not.toBeInTheDocument();

// Assertions d'état
expect(result.current.isLoading).toBe(false);
expect(result.current.data).toHaveLength(3);
expect(result.current.error).toBeNull();

// Assertions de fonctions
expect(mockFn).toHaveBeenCalledTimes(1);
expect(mockFn).toHaveBeenCalledWith('expected', 'args');
expect(mockFn).not.toHaveBeenCalled();

// Assertions de structures
expect(data).toEqual({ id: 1, name: 'Test' });
expect(array).toContain('item');
expect(object).toHaveProperty('key', 'value');
```

---

## 🚀 CI/CD et automatisation

### Pipeline GitHub Actions

**Fichier** : `.github/workflows/tests.yml`

```yaml
name: Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  backend-tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, pdo_sqlite
          coverage: xdebug

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run tests
        run: php artisan test --coverage --min=80

      - name: Upload coverage
        uses: codecov/codecov-action@v3

  frontend-tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install dependencies
        run: npm ci

      - name: Run tests
        run: npm run test:coverage

      - name: Upload coverage
        uses: codecov/codecov-action@v3

  e2e-tests:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: password
        options: >-
          --health-cmd pg_isready
          --health-interval 10s

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install dependencies
        run: |
          composer install
          npm ci
          npx playwright install --with-deps

      - name: Build assets
        run: npm run build

      - name: Run E2E tests
        run: npx playwright test

      - name: Upload Playwright report
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: playwright-report
          path: playwright-report/
```

### Pre-commit hooks (Husky)

**Configuration actuelle** : `package.json` (lint-staged)

```json
{
  "lint-staged": {
    "resources/js/**/*.{js,jsx,ts,tsx}": [
      "prettier --write",
      "eslint --fix",
      "vitest related --run"  // ← Ajouter ici
    ],
    "app/**/*.php": [
      "vendor/bin/pint",
      "vendor/bin/rector process",
      "php artisan test --filter"  // ← Ajouter ici
    ]
  }
}
```

### Scripts package.json recommandés

```json
{
  "scripts": {
    "test": "vitest",
    "test:unit": "vitest --run",
    "test:watch": "vitest --watch",
    "test:ui": "vitest --ui",
    "test:coverage": "vitest --coverage",
    "test:e2e": "playwright test",
    "test:e2e:ui": "playwright test --ui",
    "test:e2e:headed": "playwright test --headed",
    "test:all": "npm run test:unit && npm run test:e2e",
    "test:ci": "vitest --run && playwright test"
  }
}
```

### Makefile pour tests

**Fichier** : `Makefile`

```makefile
.PHONY: test test-backend test-frontend test-e2e test-all

# Tests backend
test-backend:
	php artisan test

test-backend-coverage:
	php artisan test --coverage --min=80

# Tests frontend
test-frontend:
	npm run test:unit

test-frontend-coverage:
	npm run test:coverage

# Tests E2E
test-e2e:
	npx playwright test

test-e2e-ui:
	npx playwright test --ui

# Tous les tests
test-all:
	make test-backend
	make test-frontend
	make test-e2e

# Qualité globale
quality:
	./vendor/bin/pint
	./vendor/bin/phpstan analyse
	npm run lint
	make test-all
```

---

## 🗺️ Roadmap d'amélioration

### Phase 1 : Fondations (Sprint 3) - 2 semaines

#### Semaine 1 : Configuration et tests frontend
- [ ] Configurer Vitest
- [ ] Créer `setupTests.ts`
- [ ] Écrire tests pour composants Workflow
- [ ] Tester les hooks `useWorkflows`, `useWorkflowUpdates`
- [ ] Atteindre 60% de couverture frontend

#### Semaine 2 : Tests E2E essentiels
- [ ] Créer `playwright.config.ts`
- [ ] Configurer la structure `tests/e2e/`
- [ ] Écrire 5 tests E2E critiques :
  - Login/Logout
  - Création de workflow
  - Exécution de workflow
  - Configuration Notion
  - OAuth Git
- [ ] Intégrer dans CI/CD

### Phase 2 : Expansion (Sprint 4) - 2 semaines

#### Semaine 3 : Couverture backend
- [ ] Atteindre 85% de couverture sur services
- [ ] Tests de tous les contrôleurs API
- [ ] Tests de policies et gates
- [ ] Tests de jobs et événements

#### Semaine 4 : Tests d'intégration
- [ ] Tests d'intégration Notion complète
- [ ] Tests d'intégration Git (GitHub, GitLab)
- [ ] Tests WebSocket (Reverb)
- [ ] Tests de webhooks

### Phase 3 : Optimisation (Sprint 5) - 1 semaine

#### Semaine 5 : Performance et qualité
- [ ] Optimiser vitesse des tests
- [ ] Parallélisation des tests E2E
- [ ] Tests de performance (temps de réponse API)
- [ ] Tests de charge (avec k6 ou Artillery)
- [ ] Documentation complète

### Métriques de succès

**À la fin de la Phase 3** :
- ✅ 85%+ de couverture backend
- ✅ 75%+ de couverture frontend
- ✅ 20+ tests E2E critiques
- ✅ Tous les tests passent en CI/CD
- ✅ Temps d'exécution < 5 min pour toute la suite
- ✅ 0 tests flaky (instables)

---

## 📚 Ressources et références

### Documentation officielle
- **PHPUnit** : https://phpunit.de/documentation.html
- **Laravel Testing** : https://laravel.com/docs/12.x/testing
- **Playwright** : https://playwright.dev/
- **Vitest** : https://vitest.dev/
- **Testing Library** : https://testing-library.com/

### Articles et guides
- [Laravel Testing Best Practices](https://laravel-news.com/testing-best-practices)
- [Testing React with Vitest](https://vitest.dev/guide/ui.html)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)

### Outils complémentaires
- **Faker** : Génération de données de test
- **Laravel Dusk** : Alternative à Playwright pour Laravel
- **Pest** : Framework de test moderne pour PHP (alternative à PHPUnit)
- **Storybook** : Documentation visuelle de composants React

---

## 🎓 Conclusion

Cette stratégie de tests vous permet de :

1. **Garantir la qualité** du code à chaque commit
2. **Détecter les régressions** avant la production
3. **Documenter le comportement** attendu de l'application
4. **Faciliter le refactoring** en toute confiance
5. **Accélérer le développement** à long terme

### Prochaines étapes immédiates

1. 📖 Lire le guide détaillé sur les tests d'intégration frontend
2. 📖 Lire le guide complet sur Playwright
3. ⚙️ Configurer Vitest dans le projet
4. ✍️ Écrire les premiers tests pour les composants Workflow
5. 🚀 Configurer Playwright et créer les premiers tests E2E

**Bon courage pour la mise en place de votre stratégie de tests !** 🎉
