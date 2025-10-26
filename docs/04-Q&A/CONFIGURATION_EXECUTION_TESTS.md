# Guide de Configuration et d'Exécution des Tests

> **Guide pratique pour configurer et exécuter tous les types de tests**
> **Date de création** : 26 octobre 2025

---

## 📚 Table des matières

1. [Configuration Backend (PHPUnit)](#configuration-backend-phpunit)
2. [Configuration Frontend (Vitest)](#configuration-frontend-vitest)
3. [Configuration E2E (Playwright)](#configuration-e2e-playwright)
4. [Scripts et commandes](#scripts-et-commandes)
5. [CI/CD avec GitHub Actions](#cicd-avec-github-actions)
6. [Pre-commit hooks](#pre-commit-hooks)
7. [Troubleshooting](#troubleshooting)

---

## 🔧 Configuration Backend (PHPUnit)

### Configuration existante (déjà en place)

Votre `phpunit.xml` est déjà bien configuré :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="MAIL_MAILER" value="array"/>
    </php>
</phpunit>
```

### Commandes de tests backend

```bash
# Tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage

# Tests d'un groupe spécifique
php artisan test --group=workflow
php artisan test --group=unit
php artisan test --group=feature

# Tests d'un fichier
php artisan test tests/Feature/Workflow/WorkflowApiTest.php

# Test spécifique
php artisan test --filter=test_can_create_workflow

# Parallélisation (plus rapide)
php artisan test --parallel

# Avec rapport de couverture HTML
php artisan test --coverage-html coverage

# Stopper au premier échec
php artisan test --stop-on-failure

# Combiner plusieurs options
php artisan test --group=workflow --coverage --stop-on-failure
```

### Configuration de la couverture de code

Pour générer des rapports de couverture, vous devez avoir **Xdebug** ou **PCOV** installé.

#### Installation de PCOV (recommandé, plus rapide)

```bash
# macOS avec Homebrew
brew install pcov

# Vérifier l'installation
php -m | grep pcov
```

#### Configuration php.ini

Ajoutez dans votre `php.ini` :

```ini
[PCOV]
pcov.enabled = 1
pcov.directory = /Users/fred/PhpstormProjects/mcp_manager/app
pcov.exclude = ~vendor~
```

#### Rapport de couverture HTML

```bash
# Générer le rapport HTML
php artisan test --coverage-html coverage

# Ouvrir le rapport
open coverage/index.html
```

---

## ⚛️ Configuration Frontend (Vitest)

### Installation

```bash
npm install -D vitest @vitest/ui @testing-library/react @testing-library/jest-dom @testing-library/user-event jsdom
```

### Configuration Vitest

**Créer** : `vitest.config.ts`

```typescript
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  test: {
    // Environnement de test (DOM simulé)
    environment: 'jsdom',

    // Variables globales (describe, it, expect, vi)
    globals: true,

    // Fichier de setup
    setupFiles: './resources/js/setupTests.ts',

    // Patterns de fichiers de test
    include: ['resources/js/**/*.{test,spec}.{ts,tsx}'],

    // Exclure
    exclude: [
      'node_modules',
      'dist',
      'resources/js/types/**',
    ],

    // Couverture de code
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      reportsDirectory: './coverage/frontend',
      include: ['resources/js/**/*.{ts,tsx}'],
      exclude: [
        'resources/js/**/*.d.ts',
        'resources/js/**/__tests__/**',
        'resources/js/types/**',
        'resources/js/setupTests.ts',
      ],
      thresholds: {
        lines: 70,
        functions: 70,
        branches: 70,
        statements: 70,
      },
    },

    // Reporters
    reporters: ['verbose'],

    // Timeout
    testTimeout: 10000,
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
});
```

### Fichier de setup

**Créer** : `resources/js/setupTests.ts`

```typescript
import '@testing-library/jest-dom';
import { cleanup } from '@testing-library/react';
import { afterEach, vi } from 'vitest';

// Nettoyage après chaque test
afterEach(() => {
  cleanup();
});

// Mock de window.matchMedia (pour responsive)
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation((query) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
});

// Mock d'IntersectionObserver (pour lazy loading)
global.IntersectionObserver = class IntersectionObserver {
  constructor() {}
  disconnect() {}
  observe() {}
  takeRecords() {
    return [];
  }
  unobserve() {}
} as any;

// Mock de window.scrollTo (souvent utilisé)
global.scrollTo = vi.fn();

// Mock de ResizeObserver
global.ResizeObserver = class ResizeObserver {
  constructor() {}
  disconnect() {}
  observe() {}
  unobserve() {}
} as any;
```

### Configuration TypeScript

Ajoutez dans `tsconfig.json` :

```json
{
  "compilerOptions": {
    "types": ["vitest/globals", "@testing-library/jest-dom"]
  }
}
```

### Scripts package.json

Ajoutez dans `package.json` :

```json
{
  "scripts": {
    "test": "vitest",
    "test:ui": "vitest --ui",
    "test:run": "vitest run",
    "test:coverage": "vitest run --coverage",
    "test:watch": "vitest --watch"
  }
}
```

### Commandes de tests frontend

```bash
# Mode watch (recommandé pour le développement)
npm run test

# Mode UI (interface graphique)
npm run test:ui

# Run une fois (pour CI)
npm run test:run

# Avec couverture
npm run test:coverage

# Tests spécifiques
npx vitest WorkflowCard
npx vitest use-workflows

# Avec filtrage
npx vitest --grep="creates workflow"
```

---

## 🌐 Configuration E2E (Playwright)

### Installation

```bash
# Installer Playwright
npm install -D @playwright/test

# Installer les navigateurs
npx playwright install

# Pour l'accessibilité
npm install -D @axe-core/playwright
```

### Configuration Playwright

**Créer** : `playwright.config.ts`

```typescript
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 30 * 1000,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,

  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['json', { outputFile: 'test-results/results.json' }],
    ['list'],
  ],

  use: {
    baseURL: 'http://localhost:3978',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    trace: 'on-first-retry',
    actionTimeout: 10 * 1000,
  },

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
  ],

  webServer: {
    command: 'php artisan serve --port=3978',
    port: 3978,
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
  },
});
```

### Structure des dossiers

```bash
# Créer la structure
mkdir -p tests/e2e/{auth,workflows,integrations,fixtures}
mkdir -p tests/e2e/fixtures/pages
```

### Setup global

**Créer** : `tests/e2e/global-setup.ts`

```typescript
import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

async function globalSetup() {
  console.log('🚀 E2E Global setup started...');

  // Préparer la base de données
  await execAsync('php artisan migrate:fresh --seed --env=testing');

  console.log('✅ Database ready');
  console.log('✅ E2E Global setup completed');
}

export default globalSetup;
```

### Fixtures d'authentification

**Créer** : `tests/e2e/fixtures/auth.ts`

```typescript
import { Page } from '@playwright/test';

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

export async function logout(page: Page): Promise<void> {
  await page.click('[data-testid="user-menu"]');
  await page.click('button:has-text("Logout")');
  await page.waitForURL('/login');
}
```

### Scripts package.json pour Playwright

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

### Commandes Playwright

```bash
# Tous les tests E2E
npm run test:e2e

# Mode UI (recommandé)
npm run test:e2e:ui

# Mode headed (voir le navigateur)
npm run test:e2e:headed

# Mode debug
npm run test:e2e:debug

# Tests spécifiques
npx playwright test tests/e2e/workflows

# Un seul navigateur
npx playwright test --project=chromium

# Générer le rapport
npm run test:e2e:report
```

---

## 📜 Scripts et commandes

### Makefile pour tous les tests

**Créer** : `Makefile`

```makefile
.PHONY: test test-backend test-frontend test-e2e test-all quality

# Tests backend
test-backend:
	php artisan test

test-backend-coverage:
	php artisan test --coverage --min=80

test-backend-parallel:
	php artisan test --parallel

# Tests frontend
test-frontend:
	npm run test:run

test-frontend-coverage:
	npm run test:coverage

test-frontend-watch:
	npm run test

# Tests E2E
test-e2e:
	npx playwright test

test-e2e-ui:
	npx playwright test --ui

test-e2e-chrome:
	npx playwright test --project=chromium

# Tous les tests
test-all:
	@echo "🧪 Running all tests..."
	@make test-backend
	@make test-frontend
	@make test-e2e
	@echo "✅ All tests completed!"

# Qualité globale
quality:
	@echo "🔍 Running quality checks..."
	./vendor/bin/pint
	./vendor/bin/phpstan analyse --level=max app
	npm run lint
	npm run types
	@make test-all
	@echo "✅ Quality checks completed!"

# Installation des dépendances de test
install-test-deps:
	composer install
	npm ci
	npx playwright install
```

### Scripts package.json complets

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "lint": "eslint . --fix",
    "format": "prettier --write resources/",
    "types": "tsc --noEmit",

    "test": "vitest",
    "test:ui": "vitest --ui",
    "test:run": "vitest run",
    "test:coverage": "vitest run --coverage",
    "test:watch": "vitest --watch",

    "test:e2e": "playwright test",
    "test:e2e:ui": "playwright test --ui",
    "test:e2e:headed": "playwright test --headed",
    "test:e2e:debug": "playwright test --debug",
    "test:e2e:chrome": "playwright test --project=chromium",
    "test:e2e:report": "playwright show-report",

    "test:all": "npm run test:run && npm run test:e2e",
    "test:ci": "npm run test:run && playwright test"
  }
}
```

### Raccourcis bash (optionnel)

Ajoutez dans votre `~/.zshrc` ou `~/.bashrc` :

```bash
# Tests MCP Manager
alias t="php artisan test"
alias tf="npm run test"
alias te="npm run test:e2e"
alias tui="npm run test:e2e:ui"
alias tw="npm run test:watch"

# Tests avec couverture
alias tc="php artisan test --coverage"
alias tfc="npm run test:coverage"

# Tous les tests
alias tall="make test-all"

# Qualité
alias q="make quality"
```

---

## 🚀 CI/CD avec GitHub Actions

### Configuration GitHub Actions

**Créer** : `.github/workflows/tests.yml`

```yaml
name: Tests

on:
  push:
    branches: [main, develop, feature/*]
  pull_request:
    branches: [main, develop]

jobs:
  backend-tests:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: password
          POSTGRES_DB: testing
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
        ports:
          - 5432:5432

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, pdo, pdo_pgsql, pcov
          coverage: pcov

      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate key
        run: php artisan key:generate

      - name: Run migrations
        run: php artisan migrate --env=testing

      - name: Run tests
        run: php artisan test --coverage --min=70

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
          flags: backend

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

      - name: Run linter
        run: npm run lint

      - name: Run type check
        run: npm run types

      - name: Run tests
        run: npm run test:coverage

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage/frontend/coverage-final.json
          flags: frontend

  e2e-tests:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: password
          POSTGRES_DB: testing
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
        ports:
          - 5432:5432

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, pdo, pdo_pgsql

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install PHP dependencies
        run: composer install

      - name: Install Node dependencies
        run: npm ci

      - name: Install Playwright browsers
        run: npx playwright install --with-deps

      - name: Build assets
        run: npm run build

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate key
        run: php artisan key:generate

      - name: Run migrations
        run: php artisan migrate --env=testing --seed

      - name: Run E2E tests
        run: npx playwright test

      - name: Upload Playwright report
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: playwright-report
          path: playwright-report/
          retention-days: 30
```

### Badge de statut

Ajoutez dans votre `README.md` :

```markdown
[![Tests](https://github.com/votre-org/mcp-manager/actions/workflows/tests.yml/badge.svg)](https://github.com/votre-org/mcp-manager/actions/workflows/tests.yml)
```

---

## 🔒 Pre-commit hooks

### Configuration Husky (déjà en place)

Votre `package.json` a déjà `lint-staged` configuré. Améliorons-le :

```json
{
  "lint-staged": {
    "resources/js/**/*.{js,jsx,ts,tsx}": [
      "prettier --write",
      "eslint --fix",
      "vitest related --run"
    ],
    "resources/css/**/*.css": [
      "prettier --write"
    ],
    "app/**/*.php": [
      "vendor/bin/pint",
      "vendor/bin/rector process",
      "php -d memory_limit=1G vendor/bin/phpstan analyse --no-progress",
      "php artisan test --filter"
    ]
  }
}
```

### Scripts Husky

**Créer** : `.husky/pre-commit`

```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

echo "🔍 Running pre-commit checks..."

# Lint-staged (PHP et JS)
npx lint-staged

echo "✅ Pre-commit checks passed!"
```

**Créer** : `.husky/pre-push`

```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

echo "🧪 Running tests before push..."

# Tests backend
php artisan test --stop-on-failure || exit 1

# Tests frontend
npm run test:run || exit 1

echo "✅ All tests passed! Pushing..."
```

### Rendre les scripts exécutables

```bash
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```

---

## 🐛 Troubleshooting

### Problèmes courants Backend

#### Erreur : "Database file not found"

```bash
# Solution
php artisan config:clear
php artisan cache:clear
```

#### Erreur : "Class not found"

```bash
# Regénérer l'autoload
composer dump-autoload
```

#### Tests lents

```bash
# Utiliser la parallélisation
php artisan test --parallel

# Ou limiter les tests
php artisan test --filter=WorkflowTest
```

### Problèmes courants Frontend

#### Erreur : "Cannot find module"

```bash
# Réinstaller les dépendances
rm -rf node_modules package-lock.json
npm install
```

#### Erreur : "ReferenceError: document is not defined"

```typescript
// Vérifier que jsdom est configuré dans vitest.config.ts
test: {
  environment: 'jsdom',
}
```

#### Tests qui timeout

```typescript
// Augmenter le timeout dans vitest.config.ts
test: {
  testTimeout: 20000, // 20 secondes
}
```

### Problèmes courants Playwright

#### Erreur : "Browsers not installed"

```bash
npx playwright install
```

#### Erreur : "Port 3978 already in use"

```bash
# Trouver le processus
lsof -ti:3978

# Tuer le processus
kill -9 $(lsof -ti:3978)
```

#### Tests flaky (instables)

```typescript
// Augmenter les timeouts
await expect(element).toBeVisible({ timeout: 10000 });

// Utiliser waitFor
await page.waitForSelector('.element');

// Désactiver les animations CSS
await page.addStyleTag({
  content: '* { animation: none !important; transition: none !important; }',
});
```

#### Navigateurs qui plantent

```bash
# Relancer avec plus de mémoire
npx playwright test --workers=1
```

---

## 📊 Résumé des commandes

### Commandes quotidiennes

```bash
# Développement backend
php artisan test --filter=WorkflowTest

# Développement frontend
npm run test -- WorkflowCard

# Mode watch frontend
npm run test:watch

# E2E en mode UI
npm run test:e2e:ui
```

### Commandes avant commit

```bash
# Qualité complète
make quality

# Ou manuellement
./vendor/bin/pint
./vendor/bin/phpstan analyse
npm run lint
npm run types
php artisan test
npm run test:run
```

### Commandes CI/CD

```bash
# Backend
php artisan test --coverage --min=70

# Frontend
npm run test:coverage

# E2E
npx playwright test
```

---

## 🎯 Checklist de configuration

### Backend
- [ ] `phpunit.xml` configuré
- [ ] PCOV ou Xdebug installé
- [ ] Tests dans `tests/Unit` et `tests/Feature`
- [ ] Groupes PHPUnit définis

### Frontend
- [ ] `vitest.config.ts` créé
- [ ] `setupTests.ts` créé
- [ ] Scripts npm définis
- [ ] Tests dans `__tests__/` dossiers

### E2E
- [ ] `playwright.config.ts` créé
- [ ] Structure `tests/e2e/` créée
- [ ] Fixtures créées
- [ ] Navigateurs installés

### CI/CD
- [ ] `.github/workflows/tests.yml` créé
- [ ] Secrets GitHub configurés (si nécessaire)
- [ ] Badge de statut ajouté au README

### Hooks
- [ ] Husky installé
- [ ] `pre-commit` configuré
- [ ] `pre-push` configuré
- [ ] `lint-staged` configuré

---

## 🎓 Conclusion

Vous avez maintenant une configuration complète de tests pour votre application MCP Manager :

- ✅ Tests backend avec PHPUnit
- ✅ Tests frontend avec Vitest
- ✅ Tests E2E avec Playwright
- ✅ CI/CD avec GitHub Actions
- ✅ Pre-commit hooks avec Husky

### Prochaines étapes

1. Suivre la checklist de configuration
2. Exécuter les tests existants
3. Ajouter de nouveaux tests pour les workflows
4. Viser 80%+ de couverture de code

**Bon développement et bons tests !** 🚀
