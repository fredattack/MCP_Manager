# Guide Didactique : Tests d'Intégration Frontend

> **Guide pour développeurs backend qui découvrent les tests frontend**
> **Niveau** : Débutant à Intermédiaire
> **Durée de lecture** : 30-45 minutes

---

## 📚 Table des matières

1. [Introduction : Qu'est-ce qu'un test d'intégration frontend ?](#introduction)
2. [Différences avec les tests backend](#différences-avec-les-tests-backend)
3. [Installation et configuration](#installation-et-configuration)
4. [Premiers pas avec Testing Library](#premiers-pas-avec-testing-library)
5. [Tester des composants React](#tester-des-composants-react)
6. [Tester des hooks personnalisés](#tester-des-hooks-personnalisés)
7. [Tester avec React Query](#tester-avec-react-query)
8. [Tester des formulaires Inertia](#tester-des-formulaires-inertia)
9. [Patterns avancés](#patterns-avancés)
10. [Debugging et troubleshooting](#debugging-et-troubleshooting)
11. [Exercices pratiques](#exercices-pratiques)

---

## 🎯 Introduction

### Qu'est-ce qu'un test d'intégration frontend ?

En tant que développeur backend, vous connaissez bien les **tests de fonctionnalité** Laravel qui testent plusieurs composants ensemble (contrôleur + service + modèle + base de données).

Les **tests d'intégration frontend** sont similaires : ils testent plusieurs composants React ensemble, avec leur contexte, leurs hooks, et leurs interactions.

#### Comparaison avec le backend

| Backend (Laravel Feature Test) | Frontend (Integration Test) |
|--------------------------------|------------------------------|
| Route → Contrôleur → Service → Modèle | Page → Composant → Hook → API |
| Base de données SQLite | DOM virtuel (jsdom) |
| `$response->assertOk()` | `expect(element).toBeInTheDocument()` |
| `RefreshDatabase` | Mocks et fixtures |
| Factories | Données de test |

### Exemple concret

**Backend (Feature Test)** :
```php
public function test_user_can_create_workflow(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/workflows', [
            'name' => 'My Workflow',
            'description' => 'Test',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('workflows', ['name' => 'My Workflow']);
}
```

**Frontend (Integration Test)** :
```typescript
test('user can create workflow', async () => {
  // Arrange : Monter le composant avec son contexte
  render(<CreateWorkflowForm />);

  // Act : Interagir comme un utilisateur
  await userEvent.type(screen.getByLabelText('Name'), 'My Workflow');
  await userEvent.type(screen.getByLabelText('Description'), 'Test');
  await userEvent.click(screen.getByRole('button', { name: /create/i }));

  // Assert : Vérifier le résultat
  expect(await screen.findByText('Workflow created')).toBeInTheDocument();
});
```

**Similitudes** :
- Structure Arrange-Act-Assert
- Simulation d'un utilisateur authentifié
- Vérification du résultat

**Différences** :
- Pas de base de données réelle (on mock les API)
- Interaction avec le DOM au lieu de HTTP
- Asynchrone par nature (attentes, animations)

---

## 🔄 Différences avec les tests backend

### Philosophie de test

#### Backend : "Test the implementation"
```php
// On teste l'implémentation technique
public function test_service_calls_api(): void
{
    Http::fake([
        'api.notion.com/*' => Http::response(['ok' => true]),
    ]);

    $service = new NotionService();
    $result = $service->createPage(['title' => 'Test']);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.notion.com/v1/pages';
    });
}
```

#### Frontend : "Test the behavior"
```typescript
// On teste le comportement utilisateur
test('user sees success message after creating page', async () => {
  // On se fiche de l'API appelée, on teste ce que voit l'utilisateur
  render(<CreatePageForm />);

  await userEvent.type(screen.getByLabelText('Title'), 'Test');
  await userEvent.click(screen.getByRole('button', { name: /create/i }));

  expect(await screen.findByText('Page created')).toBeInTheDocument();
});
```

### Asynchronicité

#### Backend : Synchrone par défaut
```php
$response = $this->get('/api/workflows'); // Bloquant
$this->assertEquals(200, $response->status());
```

#### Frontend : Asynchrone partout
```typescript
// Presque tout est async !
await userEvent.click(button);  // Async
await screen.findByText('...');  // Async
await waitFor(() => expect(...));  // Async
```

### État et contexte

#### Backend : État global (session, DB)
```php
$this->actingAs($user);  // État global de l'authentification
```

#### Frontend : Contexte React explicite
```typescript
// Il faut fournir TOUS les contextes nécessaires
render(
  <QueryClientProvider client={queryClient}>
    <AuthProvider>
      <MyComponent />
    </AuthProvider>
  </QueryClientProvider>
);
```

---

## ⚙️ Installation et configuration

### Option 1 : Vitest (recommandé)

#### Pourquoi Vitest ?

- ✅ **Rapide** : 10x plus rapide que Jest
- ✅ **Compatible Vite** : Vous utilisez déjà Vite
- ✅ **API identique à Jest** : Migration facile
- ✅ **UI intégré** : Interface graphique pour les tests
- ✅ **Watch mode intelligent** : Ne relance que les tests impactés

#### Installation

```bash
npm install -D vitest @vitest/ui jsdom @testing-library/react @testing-library/jest-dom @testing-library/user-event
```

#### Configuration

**Fichier** : `vitest.config.ts`

```typescript
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  test: {
    // Environnement de test (DOM simulé)
    environment: 'jsdom',

    // Variables globales (describe, it, expect)
    globals: true,

    // Fichier de setup
    setupFiles: './resources/js/setupTests.ts',

    // Patterns de fichiers de test
    include: ['resources/js/**/*.{test,spec}.{ts,tsx}'],

    // Couverture de code
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['resources/js/**/*.{ts,tsx}'],
      exclude: [
        'resources/js/**/*.d.ts',
        'resources/js/**/__tests__/**',
        'resources/js/types/**',
      ],
      thresholds: {
        lines: 70,
        functions: 70,
        branches: 70,
        statements: 70,
      },
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
});
```

**Fichier** : `resources/js/setupTests.ts`

```typescript
import '@testing-library/jest-dom';
import { cleanup } from '@testing-library/react';
import { afterEach, vi } from 'vitest';

// Nettoyage après chaque test
afterEach(() => {
  cleanup();
});

// Mock de window.matchMedia (pour les tests de responsive)
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

// Mock d'IntersectionObserver (pour les composants avec lazy loading)
global.IntersectionObserver = class IntersectionObserver {
  constructor() {}
  disconnect() {}
  observe() {}
  takeRecords() {
    return [];
  }
  unobserve() {}
} as any;
```

#### Scripts package.json

```json
{
  "scripts": {
    "test": "vitest",
    "test:ui": "vitest --ui",
    "test:coverage": "vitest --coverage",
    "test:run": "vitest run"
  }
}
```

### Option 2 : Jest (existant)

Si vous voulez rester sur Jest, voici la configuration minimale.

**Fichier** : `jest.config.js`

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
  transform: {
    '^.+\\.tsx?$': ['ts-jest', {
      tsconfig: {
        jsx: 'react-jsx',
      },
    }],
  },
};
```

---

## 🧪 Premiers pas avec Testing Library

### Philosophie : "Test comme un utilisateur"

> **Principe fondamental** : Vos tests ne doivent pas tester l'implémentation, mais le comportement visible par l'utilisateur.

#### ❌ Mauvais test (teste l'implémentation)

```typescript
test('bad test', () => {
  const { container } = render(<Button />);

  // Teste la structure HTML interne
  expect(container.querySelector('.btn-primary')).toBeTruthy();

  // Teste l'état interne du composant
  expect(component.state.isClicked).toBe(false);
});
```

#### ✅ Bon test (teste le comportement)

```typescript
test('good test', async () => {
  const handleClick = vi.fn();
  render(<Button onClick={handleClick}>Click me</Button>);

  // Trouve le bouton comme le ferait un utilisateur
  const button = screen.getByRole('button', { name: /click me/i });

  // Vérifie qu'il est visible
  expect(button).toBeInTheDocument();

  // Clique dessus
  await userEvent.click(button);

  // Vérifie le comportement
  expect(handleClick).toHaveBeenCalled();
});
```

### Les queries de Testing Library

#### Ordre de priorité (du meilleur au pire)

1. **Accessible pour tous** (recommandé ⭐⭐⭐)
   - `getByRole` : Trouve par rôle ARIA
   - `getByLabelText` : Trouve par label de formulaire
   - `getByPlaceholderText` : Trouve par placeholder
   - `getByText` : Trouve par texte visible

2. **Sémantique** (OK ⭐⭐)
   - `getByAltText` : Trouve par texte alternatif (images)
   - `getByTitle` : Trouve par attribut title

3. **À éviter** (dernier recours ⭐)
   - `getByTestId` : Trouve par `data-testid`

#### Variantes de queries

```typescript
// get* : Throw si non trouvé (synchrone)
const button = screen.getByRole('button');

// query* : Retourne null si non trouvé (synchrone)
const button = screen.queryByRole('button');
if (button) {
  // ...
}

// find* : Retourne une Promise (asynchrone, attend jusqu'à 1s)
const button = await screen.findByRole('button');

// *All : Retourne un tableau
const buttons = screen.getAllByRole('button');
```

#### Exemples pratiques

```typescript
// Bouton
screen.getByRole('button', { name: /submit/i });
screen.getByRole('button', { name: 'Create Workflow' });

// Input
screen.getByLabelText('Email');
screen.getByPlaceholderText('Enter your email...');

// Lien
screen.getByRole('link', { name: /learn more/i });

// Heading
screen.getByRole('heading', { name: /welcome/i, level: 1 });

// Checkbox
screen.getByRole('checkbox', { name: /agree to terms/i });

// Texte
screen.getByText('Hello World');
screen.getByText(/hello/i); // Regex (insensible à la casse)

// TestId (dernier recours)
screen.getByTestId('workflow-card');
```

### userEvent : Simuler les interactions

`userEvent` simule les interactions utilisateur de manière réaliste (contrairement à `fireEvent`).

```typescript
import userEvent from '@testing-library/user-event';

test('user interactions', async () => {
  // Setup
  const user = userEvent.setup();

  render(<MyForm />);

  // Taper du texte
  await user.type(screen.getByLabelText('Name'), 'John Doe');

  // Cliquer
  await user.click(screen.getByRole('button', { name: /submit/i }));

  // Double-clic
  await user.dblClick(screen.getByTestId('item'));

  // Hover
  await user.hover(screen.getByRole('button'));

  // Sélectionner dans un select
  await user.selectOptions(screen.getByLabelText('Country'), 'France');

  // Uploader un fichier
  const file = new File(['hello'], 'hello.txt', { type: 'text/plain' });
  await user.upload(screen.getByLabelText('Upload'), file);

  // Cocher une checkbox
  await user.click(screen.getByRole('checkbox', { name: /agree/i }));

  // Appuyer sur une touche
  await user.keyboard('{Enter}');
  await user.keyboard('{Escape}');
  await user.keyboard('{Shift>}A{/Shift}'); // Shift+A
});
```

---

## ⚛️ Tester des composants React

### Exemple 1 : Composant simple (Button)

**Composant** : `resources/js/components/ui/Button.tsx`

```typescript
import { ButtonHTMLAttributes } from 'react';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger';
  loading?: boolean;
}

export function Button({
  children,
  variant = 'primary',
  loading = false,
  disabled,
  ...props
}: ButtonProps) {
  return (
    <button
      className={`btn btn-${variant}`}
      disabled={disabled || loading}
      {...props}
    >
      {loading ? 'Loading...' : children}
    </button>
  );
}
```

**Test** : `resources/js/components/ui/__tests__/Button.test.tsx`

```typescript
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Button } from '../Button';

describe('Button', () => {
  it('renders with correct text', () => {
    render(<Button>Click me</Button>);

    expect(screen.getByRole('button', { name: 'Click me' })).toBeInTheDocument();
  });

  it('calls onClick when clicked', async () => {
    const handleClick = vi.fn();
    render(<Button onClick={handleClick}>Click me</Button>);

    await userEvent.click(screen.getByRole('button'));

    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  it('is disabled when disabled prop is true', () => {
    render(<Button disabled>Click me</Button>);

    expect(screen.getByRole('button')).toBeDisabled();
  });

  it('shows loading state', () => {
    render(<Button loading>Click me</Button>);

    expect(screen.getByRole('button')).toHaveTextContent('Loading...');
    expect(screen.getByRole('button')).toBeDisabled();
  });

  it('applies correct variant class', () => {
    const { rerender } = render(<Button variant="primary">Click me</Button>);
    expect(screen.getByRole('button')).toHaveClass('btn-primary');

    rerender(<Button variant="danger">Click me</Button>);
    expect(screen.getByRole('button')).toHaveClass('btn-danger');
  });

  it('does not call onClick when disabled', async () => {
    const handleClick = vi.fn();
    render(<Button onClick={handleClick} disabled>Click me</Button>);

    await userEvent.click(screen.getByRole('button'));

    expect(handleClick).not.toHaveBeenCalled();
  });
});
```

### Exemple 2 : Composant avec état (Counter)

**Composant** : `resources/js/components/Counter.tsx`

```typescript
import { useState } from 'react';

export function Counter({ initialCount = 0 }: { initialCount?: number }) {
  const [count, setCount] = useState(initialCount);

  return (
    <div>
      <p>Count: {count}</p>
      <button onClick={() => setCount(count + 1)}>Increment</button>
      <button onClick={() => setCount(count - 1)}>Decrement</button>
      <button onClick={() => setCount(0)}>Reset</button>
    </div>
  );
}
```

**Test** :

```typescript
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Counter } from '../Counter';

describe('Counter', () => {
  it('displays initial count', () => {
    render(<Counter initialCount={5} />);

    expect(screen.getByText('Count: 5')).toBeInTheDocument();
  });

  it('increments count when clicking increment', async () => {
    render(<Counter />);

    expect(screen.getByText('Count: 0')).toBeInTheDocument();

    await userEvent.click(screen.getByRole('button', { name: /increment/i }));

    expect(screen.getByText('Count: 1')).toBeInTheDocument();
  });

  it('decrements count when clicking decrement', async () => {
    render(<Counter initialCount={5} />);

    await userEvent.click(screen.getByRole('button', { name: /decrement/i }));

    expect(screen.getByText('Count: 4')).toBeInTheDocument();
  });

  it('resets count to zero', async () => {
    render(<Counter initialCount={10} />);

    await userEvent.click(screen.getByRole('button', { name: /reset/i }));

    expect(screen.getByText('Count: 0')).toBeInTheDocument();
  });

  it('handles multiple interactions', async () => {
    render(<Counter />);

    // Increment 3 fois
    await userEvent.click(screen.getByRole('button', { name: /increment/i }));
    await userEvent.click(screen.getByRole('button', { name: /increment/i }));
    await userEvent.click(screen.getByRole('button', { name: /increment/i }));

    expect(screen.getByText('Count: 3')).toBeInTheDocument();

    // Decrement 1 fois
    await userEvent.click(screen.getByRole('button', { name: /decrement/i }));

    expect(screen.getByText('Count: 2')).toBeInTheDocument();
  });
});
```

### Exemple 3 : Composant avec effet secondaire

**Composant** : `resources/js/components/Timer.tsx`

```typescript
import { useState, useEffect } from 'react';

export function Timer() {
  const [seconds, setSeconds] = useState(0);
  const [isRunning, setIsRunning] = useState(false);

  useEffect(() => {
    if (!isRunning) {return;}

    const interval = setInterval(() => {
      setSeconds((s) => s + 1);
    }, 1000);

    return () => clearInterval(interval);
  }, [isRunning]);

  return (
    <div>
      <p>Seconds: {seconds}</p>
      <button onClick={() => setIsRunning(!isRunning)}>
        {isRunning ? 'Stop' : 'Start'}
      </button>
      <button onClick={() => setSeconds(0)}>Reset</button>
    </div>
  );
}
```

**Test** :

```typescript
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Timer } from '../Timer';

// Mock des timers
beforeEach(() => {
  vi.useFakeTimers();
});

afterEach(() => {
  vi.restoreAllMocks();
});

describe('Timer', () => {
  it('starts at 0 seconds', () => {
    render(<Timer />);

    expect(screen.getByText('Seconds: 0')).toBeInTheDocument();
  });

  it('increments seconds when started', async () => {
    render(<Timer />);

    // Démarrer le timer
    await userEvent.click(screen.getByRole('button', { name: /start/i }));

    // Avancer de 3 secondes
    vi.advanceTimersByTime(3000);

    await waitFor(() => {
      expect(screen.getByText('Seconds: 3')).toBeInTheDocument();
    });
  });

  it('stops incrementing when stopped', async () => {
    render(<Timer />);

    // Démarrer
    await userEvent.click(screen.getByRole('button', { name: /start/i }));

    // Avancer de 2 secondes
    vi.advanceTimersByTime(2000);

    await waitFor(() => {
      expect(screen.getByText('Seconds: 2')).toBeInTheDocument();
    });

    // Arrêter
    await userEvent.click(screen.getByRole('button', { name: /stop/i }));

    // Avancer encore
    vi.advanceTimersByTime(5000);

    // Doit rester à 2
    expect(screen.getByText('Seconds: 2')).toBeInTheDocument();
  });

  it('resets to zero', async () => {
    render(<Timer />);

    await userEvent.click(screen.getByRole('button', { name: /start/i }));
    vi.advanceTimersByTime(5000);

    await waitFor(() => {
      expect(screen.getByText('Seconds: 5')).toBeInTheDocument();
    });

    await userEvent.click(screen.getByRole('button', { name: /reset/i }));

    expect(screen.getByText('Seconds: 0')).toBeInTheDocument();
  });
});
```

---

## 🪝 Tester des hooks personnalisés

### renderHook : Le TestCase pour hooks

Testing Library fournit `renderHook` pour tester les hooks en isolation.

#### Analogie avec le backend

```php
// Backend : Tester un service
$service = new NotionService();
$result = $service->createPage(['title' => 'Test']);
$this->assertTrue($result->success);
```

```typescript
// Frontend : Tester un hook
const { result } = renderHook(() => useNotionPages());
expect(result.current.isSuccess).toBe(true);
```

### Exemple 1 : Hook simple (useToggle)

**Hook** : `resources/js/hooks/use-toggle.ts`

```typescript
import { useState, useCallback } from 'react';

export function useToggle(initialValue = false) {
  const [value, setValue] = useState(initialValue);

  const toggle = useCallback(() => {
    setValue((v) => !v);
  }, []);

  const setTrue = useCallback(() => {
    setValue(true);
  }, []);

  const setFalse = useCallback(() => {
    setValue(false);
  }, []);

  return {
    value,
    toggle,
    setTrue,
    setFalse,
  };
}
```

**Test** :

```typescript
import { renderHook, act } from '@testing-library/react';
import { useToggle } from '../use-toggle';

describe('useToggle', () => {
  it('initializes with default value', () => {
    const { result } = renderHook(() => useToggle());

    expect(result.current.value).toBe(false);
  });

  it('initializes with custom value', () => {
    const { result } = renderHook(() => useToggle(true));

    expect(result.current.value).toBe(true);
  });

  it('toggles value', () => {
    const { result } = renderHook(() => useToggle());

    expect(result.current.value).toBe(false);

    act(() => {
      result.current.toggle();
    });

    expect(result.current.value).toBe(true);

    act(() => {
      result.current.toggle();
    });

    expect(result.current.value).toBe(false);
  });

  it('sets value to true', () => {
    const { result } = renderHook(() => useToggle(false));

    act(() => {
      result.current.setTrue();
    });

    expect(result.current.value).toBe(true);
  });

  it('sets value to false', () => {
    const { result } = renderHook(() => useToggle(true));

    act(() => {
      result.current.setFalse();
    });

    expect(result.current.value).toBe(false);
  });
});
```

### Exemple 2 : Hook avec dépendances (useLocalStorage)

**Hook** : `resources/js/hooks/use-local-storage.ts`

```typescript
import { useState, useEffect } from 'react';

export function useLocalStorage<T>(key: string, initialValue: T) {
  const [storedValue, setStoredValue] = useState<T>(() => {
    try {
      const item = window.localStorage.getItem(key);
      return item ? JSON.parse(item) : initialValue;
    } catch (error) {
      console.error(error);
      return initialValue;
    }
  });

  const setValue = (value: T | ((val: T) => T)) => {
    try {
      const valueToStore = value instanceof Function ? value(storedValue) : value;
      setStoredValue(valueToStore);
      window.localStorage.setItem(key, JSON.stringify(valueToStore));
    } catch (error) {
      console.error(error);
    }
  };

  return [storedValue, setValue] as const;
}
```

**Test** :

```typescript
import { renderHook, act } from '@testing-library/react';
import { useLocalStorage } from '../use-local-storage';

// Mock de localStorage
const localStorageMock = (() => {
  let store: Record<string, string> = {};

  return {
    getItem: (key: string) => store[key] || null,
    setItem: (key: string, value: string) => {
      store[key] = value.toString();
    },
    removeItem: (key: string) => {
      delete store[key];
    },
    clear: () => {
      store = {};
    },
  };
})();

Object.defineProperty(window, 'localStorage', {
  value: localStorageMock,
});

describe('useLocalStorage', () => {
  beforeEach(() => {
    window.localStorage.clear();
  });

  it('initializes with initial value', () => {
    const { result } = renderHook(() =>
      useLocalStorage('testKey', 'initialValue')
    );

    expect(result.current[0]).toBe('initialValue');
  });

  it('reads existing value from localStorage', () => {
    window.localStorage.setItem('testKey', JSON.stringify('existingValue'));

    const { result } = renderHook(() =>
      useLocalStorage('testKey', 'initialValue')
    );

    expect(result.current[0]).toBe('existingValue');
  });

  it('updates localStorage when value changes', () => {
    const { result } = renderHook(() => useLocalStorage('testKey', 'initial'));

    act(() => {
      result.current[1]('updated');
    });

    expect(result.current[0]).toBe('updated');
    expect(window.localStorage.getItem('testKey')).toBe(
      JSON.stringify('updated')
    );
  });

  it('handles function updates', () => {
    const { result } = renderHook(() => useLocalStorage('counter', 0));

    act(() => {
      result.current[1]((prev) => prev + 1);
    });

    expect(result.current[0]).toBe(1);

    act(() => {
      result.current[1]((prev) => prev + 5);
    });

    expect(result.current[0]).toBe(6);
  });

  it('handles complex objects', () => {
    const { result } = renderHook(() =>
      useLocalStorage('user', { name: 'John', age: 30 })
    );

    act(() => {
      result.current[1]({ name: 'Jane', age: 25 });
    });

    expect(result.current[0]).toEqual({ name: 'Jane', age: 25 });
    expect(JSON.parse(window.localStorage.getItem('user')!)).toEqual({
      name: 'Jane',
      age: 25,
    });
  });
});
```

---

## 🔄 Tester avec React Query

React Query (Tanstack Query) est utilisé dans votre projet pour la gestion des données. Voici comment le tester.

### Wrapper personnalisé pour React Query

**Fichier** : `resources/js/__tests__/utils/test-utils.tsx`

```typescript
import { ReactElement } from 'react';
import { render, RenderOptions } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

// Créer un QueryClient pour les tests
export function createTestQueryClient() {
  return new QueryClient({
    defaultOptions: {
      queries: {
        // Désactiver les retry en test
        retry: false,
        // Désactiver le cache
        cacheTime: 0,
      },
      mutations: {
        retry: false,
      },
    },
    // Désactiver les logs d'erreur en test
    logger: {
      log: console.log,
      warn: console.warn,
      error: () => {},
    },
  });
}

// Wrapper avec QueryClient
export function createWrapper() {
  const queryClient = createTestQueryClient();

  return ({ children }: { children: React.ReactNode }) => (
    <QueryClientProvider client={queryClient}>
      {children}
    </QueryClientProvider>
  );
}

// Render personnalisé avec QueryClient
export function renderWithQueryClient(
  ui: ReactElement,
  options?: Omit<RenderOptions, 'wrapper'>
) {
  return render(ui, { wrapper: createWrapper(), ...options });
}

// Re-export tout de Testing Library
export * from '@testing-library/react';
```

### Exemple : Tester useWorkflows

**Hook** : `resources/js/hooks/use-workflows.ts`

```typescript
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

interface Workflow {
  id: number;
  name: string;
  status: 'active' | 'inactive';
}

export function useWorkflows() {
  return useQuery({
    queryKey: ['workflows'],
    queryFn: async (): Promise<Workflow[]> => {
      const { data } = await axios.get('/api/workflows');
      return data.data;
    },
  });
}
```

**Test** :

```typescript
import { renderHook, waitFor } from '@testing-library/react';
import { createWrapper } from '../utils/test-utils';
import { useWorkflows } from '@/hooks/use-workflows';
import axios from 'axios';
import { vi } from 'vitest';

// Mock d'axios
vi.mock('axios');
const mockedAxios = vi.mocked(axios, true);

describe('useWorkflows', () => {
  it('fetches workflows successfully', async () => {
    // Mock de la réponse API
    mockedAxios.get.mockResolvedValueOnce({
      data: {
        data: [
          { id: 1, name: 'Workflow 1', status: 'active' },
          { id: 2, name: 'Workflow 2', status: 'inactive' },
        ],
      },
    });

    // Render le hook avec le wrapper
    const { result } = renderHook(() => useWorkflows(), {
      wrapper: createWrapper(),
    });

    // État initial : loading
    expect(result.current.isLoading).toBe(true);
    expect(result.current.data).toBeUndefined();

    // Attendre la résolution
    await waitFor(() => expect(result.current.isSuccess).toBe(true));

    // Vérifier les données
    expect(result.current.data).toHaveLength(2);
    expect(result.current.data?.[0].name).toBe('Workflow 1');
  });

  it('handles error state', async () => {
    // Mock d'une erreur
    mockedAxios.get.mockRejectedValueOnce(new Error('API Error'));

    const { result } = renderHook(() => useWorkflows(), {
      wrapper: createWrapper(),
    });

    // Attendre l'erreur
    await waitFor(() => expect(result.current.isError).toBe(true));

    // Vérifier l'état d'erreur
    expect(result.current.error).toBeDefined();
    expect(result.current.data).toBeUndefined();
  });

  it('calls the correct API endpoint', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: { data: [] } });

    renderHook(() => useWorkflows(), {
      wrapper: createWrapper(),
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith('/api/workflows');
    });
  });
});
```

### Tester les mutations

**Hook** : `resources/js/hooks/use-create-workflow.ts`

```typescript
import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';

interface CreateWorkflowData {
  name: string;
  description: string;
}

export function useCreateWorkflow() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (data: CreateWorkflowData) => {
      const response = await axios.post('/api/workflows', data);
      return response.data;
    },
    onSuccess: () => {
      // Invalider le cache des workflows
      queryClient.invalidateQueries({ queryKey: ['workflows'] });
    },
  });
}
```

**Test** :

```typescript
import { renderHook, waitFor } from '@testing-library/react';
import { createWrapper } from '../utils/test-utils';
import { useCreateWorkflow } from '@/hooks/use-create-workflow';
import axios from 'axios';

vi.mock('axios');
const mockedAxios = vi.mocked(axios, true);

describe('useCreateWorkflow', () => {
  it('creates workflow successfully', async () => {
    const newWorkflow = {
      id: 1,
      name: 'New Workflow',
      description: 'Test',
    };

    mockedAxios.post.mockResolvedValueOnce({ data: newWorkflow });

    const { result } = renderHook(() => useCreateWorkflow(), {
      wrapper: createWrapper(),
    });

    // État initial
    expect(result.current.isPending).toBe(false);

    // Déclencher la mutation
    act(() => {
      result.current.mutate({
        name: 'New Workflow',
        description: 'Test',
      });
    });

    // Pendant la mutation
    expect(result.current.isPending).toBe(true);

    // Attendre la fin
    await waitFor(() => expect(result.current.isSuccess).toBe(true));

    // Vérifier les données
    expect(result.current.data).toEqual(newWorkflow);
    expect(mockedAxios.post).toHaveBeenCalledWith('/api/workflows', {
      name: 'New Workflow',
      description: 'Test',
    });
  });

  it('handles mutation error', async () => {
    mockedAxios.post.mockRejectedValueOnce(new Error('Validation failed'));

    const { result } = renderHook(() => useCreateWorkflow(), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.mutate({
        name: '',
        description: '',
      });
    });

    await waitFor(() => expect(result.current.isError).toBe(true));

    expect(result.current.error).toBeDefined();
  });
});
```

---

## 📝 Tester des formulaires Inertia

### Exemple : Formulaire de création de workflow

**Composant** : `resources/js/components/workflows/CreateWorkflowForm.tsx`

```typescript
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export function CreateWorkflowForm() {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    description: '',
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post('/api/workflows');
  };

  return (
    <form onSubmit={submit}>
      <div>
        <label htmlFor="name">Name</label>
        <input
          id="name"
          type="text"
          value={data.name}
          onChange={(e) => setData('name', e.target.value)}
        />
        {errors.name && <p className="error">{errors.name}</p>}
      </div>

      <div>
        <label htmlFor="description">Description</label>
        <textarea
          id="description"
          value={data.description}
          onChange={(e) => setData('description', e.target.value)}
        />
        {errors.description && <p className="error">{errors.description}</p>}
      </div>

      <button type="submit" disabled={processing}>
        {processing ? 'Creating...' : 'Create Workflow'}
      </button>
    </form>
  );
}
```

**Test** :

```typescript
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { CreateWorkflowForm } from '../CreateWorkflowForm';
import { router } from '@inertiajs/react';

// Mock d'Inertia router
vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react');
  return {
    ...actual,
    router: {
      post: vi.fn(),
    },
    useForm: () => ({
      data: { name: '', description: '' },
      setData: vi.fn(),
      post: vi.fn(),
      processing: false,
      errors: {},
    }),
  };
});

describe('CreateWorkflowForm', () => {
  it('renders form fields', () => {
    render(<CreateWorkflowForm />);

    expect(screen.getByLabelText('Name')).toBeInTheDocument();
    expect(screen.getByLabelText('Description')).toBeInTheDocument();
    expect(
      screen.getByRole('button', { name: 'Create Workflow' })
    ).toBeInTheDocument();
  });

  it('allows typing in fields', async () => {
    render(<CreateWorkflowForm />);

    const nameInput = screen.getByLabelText('Name');
    const descInput = screen.getByLabelText('Description');

    await userEvent.type(nameInput, 'My Workflow');
    await userEvent.type(descInput, 'Test description');

    expect(nameInput).toHaveValue('My Workflow');
    expect(descInput).toHaveValue('Test description');
  });

  it('submits form with correct data', async () => {
    const mockPost = vi.fn();

    // Mock plus sophistiqué
    vi.mocked(useForm).mockReturnValue({
      data: { name: '', description: '' },
      setData: vi.fn((key, value) => {
        // Simuler la mise à jour de data
      }),
      post: mockPost,
      processing: false,
      errors: {},
    } as any);

    render(<CreateWorkflowForm />);

    await userEvent.type(screen.getByLabelText('Name'), 'My Workflow');
    await userEvent.type(screen.getByLabelText('Description'), 'Description');
    await userEvent.click(
      screen.getByRole('button', { name: 'Create Workflow' })
    );

    expect(mockPost).toHaveBeenCalledWith('/api/workflows');
  });

  it('shows validation errors', () => {
    vi.mocked(useForm).mockReturnValue({
      data: { name: '', description: '' },
      setData: vi.fn(),
      post: vi.fn(),
      processing: false,
      errors: {
        name: 'The name field is required.',
      },
    } as any);

    render(<CreateWorkflowForm />);

    expect(
      screen.getByText('The name field is required.')
    ).toBeInTheDocument();
  });

  it('disables submit button while processing', () => {
    vi.mocked(useForm).mockReturnValue({
      data: { name: 'Test', description: 'Test' },
      setData: vi.fn(),
      post: vi.fn(),
      processing: true,
      errors: {},
    } as any);

    render(<CreateWorkflowForm />);

    const button = screen.getByRole('button');
    expect(button).toBeDisabled();
    expect(button).toHaveTextContent('Creating...');
  });
});
```

---

## 🔬 Patterns avancés

### 1. Test de composants avec contexte multiple

```typescript
import { ReactNode } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ThemeProvider } from '@/contexts/ThemeContext';
import { AuthProvider } from '@/contexts/AuthContext';

export function AllTheProviders({ children }: { children: ReactNode }) {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  });

  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <ThemeProvider>
          {children}
        </ThemeProvider>
      </AuthProvider>
    </QueryClientProvider>
  );
}

// Utilisation
render(<MyComponent />, { wrapper: AllTheProviders });
```

### 2. Tester des composants avec portails (Modal, Toast)

```typescript
it('renders modal content', () => {
  // Créer un div pour le portal
  const modalRoot = document.createElement('div');
  modalRoot.setAttribute('id', 'modal-root');
  document.body.appendChild(modalRoot);

  render(<Modal isOpen={true}>Modal Content</Modal>);

  // Chercher dans tout le document
  expect(screen.getByText('Modal Content')).toBeInTheDocument();

  // Cleanup
  document.body.removeChild(modalRoot);
});
```

### 3. Tester des composants avec window events

```typescript
it('handles window resize', () => {
  render(<ResponsiveComponent />);

  // Déclencher resize
  act(() => {
    window.innerWidth = 500;
    window.dispatchEvent(new Event('resize'));
  });

  expect(screen.getByTestId('mobile-view')).toBeInTheDocument();
});
```

### 4. Snapshot testing (avec modération)

```typescript
it('matches snapshot', () => {
  const { container } = render(<WorkflowCard workflow={mockWorkflow} />);

  expect(container).toMatchSnapshot();
});

// ⚠️ Utiliser avec parcimonie, privilégier les tests de comportement
```

---

## 🐛 Debugging et troubleshooting

### Techniques de debug

#### 1. screen.debug()

```typescript
it('debug test', () => {
  render(<MyComponent />);

  // Afficher tout le DOM
  screen.debug();

  // Afficher un élément spécifique
  screen.debug(screen.getByRole('button'));

  // Limiter les lignes affichées
  screen.debug(undefined, 50000);
});
```

#### 2. logRoles()

```typescript
import { logRoles } from '@testing-library/react';

it('see available roles', () => {
  const { container } = render(<MyComponent />);

  // Afficher tous les rôles disponibles
  logRoles(container);
});
```

#### 3. Testing Library Playground

```typescript
// Ajouter dans votre test
import { prettyDOM } from '@testing-library/react';

console.log(prettyDOM(screen.getByRole('button')));

// Copier la sortie et coller dans :
// https://testing-playground.com/
```

### Erreurs courantes

#### Erreur 1 : "Unable to find element"

```typescript
// ❌ Erreur
screen.getByText('Submit'); // Throw immédiatement

// ✅ Solutions
screen.queryByText('Submit'); // Retourne null
await screen.findByText('Submit'); // Attend l'apparition
screen.getByRole('button', { name: /submit/i }); // Regex
```

#### Erreur 2 : "Not wrapped in act(...)"

```typescript
// ❌ Erreur
result.current.increment();

// ✅ Solution
act(() => {
  result.current.increment();
});
```

#### Erreur 3 : Tests qui passent parfois (flaky)

```typescript
// ❌ Mauvais (timing)
await new Promise((r) => setTimeout(r, 1000));
expect(screen.getByText('Loaded')).toBeInTheDocument();

// ✅ Bon (attente explicite)
await waitFor(() => {
  expect(screen.getByText('Loaded')).toBeInTheDocument();
});
```

#### Erreur 4 : "Can't perform a React state update on an unmounted component"

```typescript
// ❌ Problème : composant démonté avant la fin de l'async
useEffect(() => {
  fetchData().then(setData);
}, []);

// ✅ Solution : cleanup
useEffect(() => {
  let cancelled = false;

  fetchData().then((data) => {
    if (!cancelled) {
      setData(data);
    }
  });

  return () => {
    cancelled = true;
  };
}, []);
```

### Commandes de debug Vitest

```bash
# Mode debug avec breakpoints
npx vitest --inspect-brk

# UI mode (très utile!)
npx vitest --ui

# Watch mode avec filtrage
npx vitest --watch --grep="WorkflowCard"

# Verbose output
npx vitest --reporter=verbose
```

---

## 🎓 Exercices pratiques

### Exercice 1 : WorkflowCard

**Composant** :

```typescript
interface WorkflowCardProps {
  workflow: {
    id: number;
    name: string;
    status: 'active' | 'inactive';
    lastRun?: string;
  };
  onExecute: (id: number) => void;
  onDelete: (id: number) => void;
}

export function WorkflowCard({ workflow, onExecute, onDelete }: WorkflowCardProps) {
  return (
    <div className="workflow-card">
      <h3>{workflow.name}</h3>
      <span className={`status-${workflow.status}`}>{workflow.status}</span>
      {workflow.lastRun && <p>Last run: {workflow.lastRun}</p>}
      <button onClick={() => onExecute(workflow.id)}>Execute</button>
      <button onClick={() => onDelete(workflow.id)}>Delete</button>
    </div>
  );
}
```

**Votre mission** :

1. Tester que le nom du workflow s'affiche
2. Tester que le badge de statut a la bonne classe CSS
3. Tester que "Last run" s'affiche si présent
4. Tester que "Last run" ne s'affiche pas si absent
5. Tester que onExecute est appelé avec le bon ID
6. Tester que onDelete est appelé avec le bon ID

### Exercice 2 : useDebounce hook

**Hook** :

```typescript
import { useState, useEffect } from 'react';

export function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
}
```

**Votre mission** :

1. Tester que la valeur initiale est retournée immédiatement
2. Tester que la valeur est mise à jour après le délai
3. Tester que les changements rapides ne déclenchent qu'une seule mise à jour
4. Tester que le timeout est nettoyé correctement

### Exercice 3 : SearchBar avec debounce

**Composant** :

```typescript
import { useState } from 'react';
import { useDebounce } from '@/hooks/use-debounce';

interface SearchBarProps {
  onSearch: (query: string) => void;
  placeholder?: string;
}

export function SearchBar({ onSearch, placeholder }: SearchBarProps) {
  const [query, setQuery] = useState('');
  const debouncedQuery = useDebounce(query, 500);

  useEffect(() => {
    if (debouncedQuery) {
      onSearch(debouncedQuery);
    }
  }, [debouncedQuery, onSearch]);

  return (
    <input
      type="search"
      placeholder={placeholder}
      value={query}
      onChange={(e) => setQuery(e.target.value)}
    />
  );
}
```

**Votre mission** :

1. Tester que l'input se met à jour immédiatement
2. Tester que onSearch n'est appelé qu'après 500ms
3. Tester que les changements rapides ne déclenchent qu'un seul appel
4. Tester que onSearch n'est pas appelé pour une chaîne vide

---

## 📚 Solutions des exercices

### Solution Exercice 1 : WorkflowCard

```typescript
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { WorkflowCard } from '../WorkflowCard';

describe('WorkflowCard', () => {
  const mockWorkflow = {
    id: 1,
    name: 'Test Workflow',
    status: 'active' as const,
    lastRun: '2025-01-15',
  };

  const mockOnExecute = vi.fn();
  const mockOnDelete = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('displays workflow name', () => {
    render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onDelete={mockOnDelete}
      />
    );

    expect(screen.getByRole('heading', { name: 'Test Workflow' })).toBeInTheDocument();
  });

  it('displays status with correct class', () => {
    const { container } = render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onDelete={mockOnDelete}
      />
    );

    const statusBadge = screen.getByText('active');
    expect(statusBadge).toHaveClass('status-active');
  });

  it('displays last run when present', () => {
    render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onDelete={mockOnDelete}
      />
    );

    expect(screen.getByText(/Last run: 2025-01-15/)).toBeInTheDocument();
  });

  it('does not display last run when absent', () => {
    const workflowWithoutLastRun = { ...mockWorkflow, lastRun: undefined };

    render(
      <WorkflowCard
        workflow={workflowWithoutLastRun}
        onExecute={mockOnExecute}
        onDelete={mockOnDelete}
      />
    );

    expect(screen.queryByText(/Last run:/)).not.toBeInTheDocument();
  });

  it('calls onExecute with correct ID', async () => {
    render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onDelete={mockOnDelete}
      />
    );

    await userEvent.click(screen.getByRole('button', { name: /execute/i }));

    expect(mockOnExecute).toHaveBeenCalledTimes(1);
    expect(mockOnExecute).toHaveBeenCalledWith(1);
  });

  it('calls onDelete with correct ID', async () => {
    render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onDelete={mockOnDelete}
      />
    );

    await userEvent.click(screen.getByRole('button', { name: /delete/i }));

    expect(mockOnDelete).toHaveBeenCalledTimes(1);
    expect(mockOnDelete).toHaveBeenCalledWith(1);
  });
});
```

### Solution Exercice 2 : useDebounce

```typescript
import { renderHook, act } from '@testing-library/react';
import { useDebounce } from '../use-debounce';

beforeEach(() => {
  vi.useFakeTimers();
});

afterEach(() => {
  vi.restoreAllMocks();
});

describe('useDebounce', () => {
  it('returns initial value immediately', () => {
    const { result } = renderHook(() => useDebounce('initial', 500));

    expect(result.current).toBe('initial');
  });

  it('updates value after delay', () => {
    const { result, rerender } = renderHook(
      ({ value, delay }) => useDebounce(value, delay),
      { initialProps: { value: 'initial', delay: 500 } }
    );

    expect(result.current).toBe('initial');

    // Changer la valeur
    rerender({ value: 'updated', delay: 500 });

    // Toujours l'ancienne valeur
    expect(result.current).toBe('initial');

    // Avancer le temps
    act(() => {
      vi.advanceTimersByTime(500);
    });

    // Nouvelle valeur
    expect(result.current).toBe('updated');
  });

  it('only updates once for rapid changes', () => {
    const { result, rerender } = renderHook(
      ({ value, delay }) => useDebounce(value, delay),
      { initialProps: { value: 'initial', delay: 500 } }
    );

    // Changements rapides
    rerender({ value: 'value1', delay: 500 });
    act(() => vi.advanceTimersByTime(100));

    rerender({ value: 'value2', delay: 500 });
    act(() => vi.advanceTimersByTime(100));

    rerender({ value: 'value3', delay: 500 });
    act(() => vi.advanceTimersByTime(100));

    // Toujours la valeur initiale
    expect(result.current).toBe('initial');

    // Avancer jusqu'à la fin
    act(() => {
      vi.advanceTimersByTime(500);
    });

    // Seule la dernière valeur est prise
    expect(result.current).toBe('value3');
  });

  it('cleans up timeout correctly', () => {
    const { rerender, unmount } = renderHook(
      ({ value, delay }) => useDebounce(value, delay),
      { initialProps: { value: 'initial', delay: 500 } }
    );

    rerender({ value: 'updated', delay: 500 });

    // Démonter avant la fin du timeout
    unmount();

    // Ne devrait pas planter
    act(() => {
      vi.advanceTimersByTime(500);
    });
  });
});
```

### Solution Exercice 3 : SearchBar

```typescript
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { SearchBar } from '../SearchBar';

beforeEach(() => {
  vi.useFakeTimers();
});

afterEach(() => {
  vi.restoreAllMocks();
});

describe('SearchBar', () => {
  it('updates input immediately', async () => {
    const mockOnSearch = vi.fn();
    render(<SearchBar onSearch={mockOnSearch} />);

    const input = screen.getByRole('searchbox');

    await userEvent.type(input, 'test query');

    expect(input).toHaveValue('test query');
  });

  it('calls onSearch only after 500ms', async () => {
    const mockOnSearch = vi.fn();
    render(<SearchBar onSearch={mockOnSearch} />);

    await userEvent.type(screen.getByRole('searchbox'), 'test');

    // Pas encore appelé
    expect(mockOnSearch).not.toHaveBeenCalled();

    // Avancer le temps
    act(() => {
      vi.advanceTimersByTime(500);
    });

    // Maintenant appelé
    await waitFor(() => {
      expect(mockOnSearch).toHaveBeenCalledWith('test');
    });
  });

  it('debounces rapid changes', async () => {
    const mockOnSearch = vi.fn();
    render(<SearchBar onSearch={mockOnSearch} />);

    const input = screen.getByRole('searchbox');

    // Taper rapidement
    await userEvent.type(input, 't');
    act(() => vi.advanceTimersByTime(100));

    await userEvent.type(input, 'e');
    act(() => vi.advanceTimersByTime(100));

    await userEvent.type(input, 's');
    act(() => vi.advanceTimersByTime(100));

    await userEvent.type(input, 't');

    // Toujours pas appelé
    expect(mockOnSearch).not.toHaveBeenCalled();

    // Avancer de 500ms
    act(() => {
      vi.advanceTimersByTime(500);
    });

    // Appelé une seule fois avec la valeur finale
    await waitFor(() => {
      expect(mockOnSearch).toHaveBeenCalledTimes(1);
      expect(mockOnSearch).toHaveBeenCalledWith('test');
    });
  });

  it('does not call onSearch for empty query', async () => {
    const mockOnSearch = vi.fn();
    render(<SearchBar onSearch={mockOnSearch} />);

    // Pas de saisie

    act(() => {
      vi.advanceTimersByTime(500);
    });

    expect(mockOnSearch).not.toHaveBeenCalled();
  });
});
```

---

## 🎯 Récapitulatif

### Ce que vous avez appris

1. ✅ La différence entre tests backend et frontend
2. ✅ Configuration de Vitest/Jest pour React
3. ✅ Utilisation de Testing Library (queries, userEvent)
4. ✅ Tests de composants React (simple, avec état, avec effets)
5. ✅ Tests de hooks personnalisés (renderHook)
6. ✅ Tests avec React Query (queries, mutations)
7. ✅ Tests de formulaires Inertia
8. ✅ Patterns avancés et debugging

### Prochaines étapes

1. 📖 Lire le guide sur Playwright (tests E2E)
2. ⚙️ Configurer Vitest dans votre projet
3. ✍️ Écrire des tests pour vos composants Workflow
4. 🚀 Ajouter les tests dans votre CI/CD

**Bon apprentissage !** 🎉
