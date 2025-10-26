# Exemples Pratiques de Tests pour Workflows

> **Collection d'exemples prêts à l'emploi pour tester les workflows**
> **Niveau** : Tous niveaux
> **Date de création** : 26 octobre 2025

---

## 📚 Table des matières

1. [Tests Backend (PHPUnit)](#tests-backend-phpunit)
2. [Tests Frontend (Vitest + React Testing Library)](#tests-frontend-vitest)
3. [Tests E2E (Playwright)](#tests-e2e-playwright)
4. [Tests d'intégration complets](#tests-dintégration-complets)
5. [Exemples avec données réelles](#exemples-avec-données-réelles)

---

## 🔧 Tests Backend (PHPUnit)

### Test 1 : API Workflow - Création

**Fichier** : `tests/Feature/Workflow/WorkflowCreationTest.php`

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
 */
class WorkflowCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_workflow_with_valid_data(): void
    {
        // Arrange
        $user = User::factory()->create();

        $workflowData = [
            'name' => 'My Test Workflow',
            'description' => 'This is a test workflow',
            'llm_provider' => 'OpenAI',
            'llm_model' => 'gpt-4',
            'config' => [
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ],
        ];

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/workflows', $workflowData);

        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'status',
                    'llm_provider',
                    'llm_model',
                    'config',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.name', 'My Test Workflow')
            ->assertJsonPath('data.status', 'draft');

        // Vérifier en base de données
        $this->assertDatabaseHas('workflows', [
            'user_id' => $user->id,
            'name' => 'My Test Workflow',
            'llm_provider' => 'OpenAI',
        ]);
    }

    /** @test */
    public function workflow_creation_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/workflows', [
                'description' => 'Workflow without name',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function workflow_name_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();

        // Créer un premier workflow
        Workflow::factory()->create([
            'user_id' => $user->id,
            'name' => 'Unique Workflow',
        ]);

        // Tenter de créer un workflow avec le même nom
        $response = $this->actingAs($user)
            ->postJson('/api/workflows', [
                'name' => 'Unique Workflow',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', 'The name has already been taken.');
    }

    /** @test */
    public function different_users_can_have_workflows_with_same_name(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 1 crée un workflow
        Workflow::factory()->create([
            'user_id' => $user1->id,
            'name' => 'Common Name',
        ]);

        // User 2 peut créer un workflow avec le même nom
        $response = $this->actingAs($user2)
            ->postJson('/api/workflows', [
                'name' => 'Common Name',
            ]);

        $response->assertCreated();

        $this->assertDatabaseCount('workflows', 2);
    }

    /** @test */
    public function unauthenticated_users_cannot_create_workflows(): void
    {
        $response = $this->postJson('/api/workflows', [
            'name' => 'Test Workflow',
        ]);

        $response->assertUnauthorized();
    }
}
```

### Test 2 : Exécution de Workflow

**Fichier** : `tests/Feature/Workflow/WorkflowExecutionTest.php`

```php
<?php

namespace Tests\Feature\Workflow;

use Tests\TestCase;
use App\Models\User;
use App\Models\Workflow;
use App\Jobs\ExecuteWorkflowJob;
use App\Events\WorkflowStarted;
use App\Events\WorkflowCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;

/**
 * @group workflow
 * @group feature
 */
class WorkflowExecutionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_execute_their_workflow(): void
    {
        Queue::fake();
        Event::fake();

        $user = User::factory()->create();
        $workflow = Workflow::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/workflows/{$workflow->id}/execute");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'execution_id',
                    'status',
                    'started_at',
                ],
            ]);

        // Vérifier que le job a été dispatché
        Queue::assertPushed(ExecuteWorkflowJob::class, function ($job) use ($workflow) {
            return $job->workflow->id === $workflow->id;
        });

        // Vérifier que l'événement a été dispatché
        Event::assertDispatched(WorkflowStarted::class, function ($event) use ($workflow) {
            return $event->workflow->id === $workflow->id;
        });
    }

    /** @test */
    public function user_cannot_execute_another_users_workflow(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workflow = Workflow::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/workflows/{$workflow->id}/execute");

        $response->assertForbidden();
    }

    /** @test */
    public function cannot_execute_already_running_workflow(): void
    {
        $user = User::factory()->create();
        $workflow = Workflow::factory()->create([
            'user_id' => $user->id,
            'status' => 'running',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/workflows/{$workflow->id}/execute");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Workflow is already running');
    }

    /** @test */
    public function workflow_execution_creates_execution_log(): void
    {
        $user = User::factory()->create();
        $workflow = Workflow::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson("/api/workflows/{$workflow->id}/execute");

        $this->assertDatabaseHas('workflow_executions', [
            'workflow_id' => $workflow->id,
            'status' => 'pending',
        ]);
    }
}
```

### Test 3 : Service Workflow

**Fichier** : `tests/Unit/Services/WorkflowExecutionServiceTest.php`

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Services\WorkflowExecutionService;
use App\Services\LLM\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

/**
 * @group workflow
 * @group unit
 */
class WorkflowExecutionServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkflowExecutionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WorkflowExecutionService();
    }

    /** @test */
    public function it_executes_workflow_successfully(): void
    {
        // Arrange
        $workflow = Workflow::factory()->create([
            'llm_provider' => 'OpenAI',
            'config' => [
                'prompt' => 'Say hello',
            ],
        ]);

        // Mock du service LLM
        $mockLLM = Mockery::mock(OpenAIService::class);
        $mockLLM->shouldReceive('chat')
            ->once()
            ->with(['messages' => [['role' => 'user', 'content' => 'Say hello']]])
            ->andReturn(['choices' => [['message' => ['content' => 'Hello!']]]]);

        $this->app->instance(OpenAIService::class, $mockLLM);

        // Act
        $execution = $this->service->execute($workflow);

        // Assert
        $this->assertInstanceOf(WorkflowExecution::class, $execution);
        $this->assertEquals('completed', $execution->status);
        $this->assertNotNull($execution->result);
        $this->assertStringContainsString('Hello!', $execution->result);
    }

    /** @test */
    public function it_handles_llm_service_failure(): void
    {
        $workflow = Workflow::factory()->create([
            'llm_provider' => 'OpenAI',
        ]);

        // Mock qui lance une exception
        $mockLLM = Mockery::mock(OpenAIService::class);
        $mockLLM->shouldReceive('chat')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $this->app->instance(OpenAIService::class, $mockLLM);

        // Act
        $execution = $this->service->execute($workflow);

        // Assert
        $this->assertEquals('failed', $execution->status);
        $this->assertStringContainsString('API Error', $execution->error);
    }

    /** @test */
    public function it_broadcasts_execution_events(): void
    {
        Event::fake([
            WorkflowStarted::class,
            WorkflowCompleted::class,
        ]);

        $workflow = Workflow::factory()->create();

        $this->service->execute($workflow);

        Event::assertDispatched(WorkflowStarted::class);
        Event::assertDispatched(WorkflowCompleted::class);
    }
}
```

---

## ⚛️ Tests Frontend (Vitest)

### Test 1 : Composant WorkflowCard

**Fichier** : `resources/js/components/workflows/__tests__/WorkflowCard.test.tsx`

```typescript
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { WorkflowCard } from '../WorkflowCard';
import { vi } from 'vitest';

describe('WorkflowCard', () => {
  const mockWorkflow = {
    id: 1,
    name: 'Test Workflow',
    description: 'A test workflow',
    status: 'active' as const,
    llm_provider: 'OpenAI',
    llm_model: 'gpt-4',
    last_run: '2025-01-15T10:30:00Z',
    created_at: '2025-01-01T00:00:00Z',
  };

  const mockOnExecute = vi.fn();
  const mockOnEdit = vi.fn();
  const mockOnDelete = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders workflow information correctly', () => {
    render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    // Vérifier le titre
    expect(screen.getByRole('heading', { name: 'Test Workflow' })).toBeInTheDocument();

    // Vérifier la description
    expect(screen.getByText('A test workflow')).toBeInTheDocument();

    // Vérifier le provider
    expect(screen.getByText(/OpenAI/)).toBeInTheDocument();
    expect(screen.getByText(/gpt-4/)).toBeInTheDocument();

    // Vérifier la date
    expect(screen.getByText(/Last run:/)).toBeInTheDocument();
  });

  it('displays correct status badge', () => {
    const { rerender } = render(
      <WorkflowCard
        workflow={{ ...mockWorkflow, status: 'active' }}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    let statusBadge = screen.getByTestId('status-badge');
    expect(statusBadge).toHaveTextContent('Active');
    expect(statusBadge).toHaveClass('bg-green-100');

    // Test avec status "inactive"
    rerender(
      <WorkflowCard
        workflow={{ ...mockWorkflow, status: 'inactive' }}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    statusBadge = screen.getByTestId('status-badge');
    expect(statusBadge).toHaveTextContent('Inactive');
    expect(statusBadge).toHaveClass('bg-gray-100');

    // Test avec status "running"
    rerender(
      <WorkflowCard
        workflow={{ ...mockWorkflow, status: 'running' }}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    statusBadge = screen.getByTestId('status-badge');
    expect(statusBadge).toHaveTextContent('Running');
    expect(statusBadge).toHaveClass('bg-blue-100');
  });

  it('calls onExecute when execute button is clicked', async () => {
    const user = userEvent.setup();

    render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    const executeButton = screen.getByRole('button', { name: /execute/i });
    await user.click(executeButton);

    expect(mockOnExecute).toHaveBeenCalledTimes(1);
    expect(mockOnExecute).toHaveBeenCalledWith(1);
  });

  it('calls onEdit when edit button is clicked', async () => {
    const user = userEvent.setup();

    render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    const editButton = screen.getByRole('button', { name: /edit/i });
    await user.click(editButton);

    expect(mockOnEdit).toHaveBeenCalledTimes(1);
    expect(mockOnEdit).toHaveBeenCalledWith(1);
  });

  it('calls onDelete when delete button is clicked', async () => {
    const user = userEvent.setup();

    render(
      <WorkflowCard
        workflow={mockWorkflow}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    const deleteButton = screen.getByRole('button', { name: /delete/i });
    await user.click(deleteButton);

    expect(mockOnDelete).toHaveBeenCalledTimes(1);
    expect(mockOnDelete).toHaveBeenCalledWith(1);
  });

  it('disables execute button when workflow is running', () => {
    render(
      <WorkflowCard
        workflow={{ ...mockWorkflow, status: 'running' }}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    const executeButton = screen.getByRole('button', { name: /execute/i });
    expect(executeButton).toBeDisabled();
  });

  it('shows loading state when executing', () => {
    render(
      <WorkflowCard
        workflow={{ ...mockWorkflow, status: 'running' }}
        onExecute={mockOnExecute}
        onEdit={mockOnEdit}
        onDelete={mockOnDelete}
      />
    );

    expect(screen.getByTestId('loading-spinner')).toBeInTheDocument();
  });
});
```

### Test 2 : Hook useWorkflows

**Fichier** : `resources/js/hooks/__tests__/use-workflows.test.ts`

```typescript
import { renderHook, waitFor } from '@testing-library/react';
import { createWrapper } from '../utils/test-utils';
import { useWorkflows } from '../use-workflows';
import axios from 'axios';
import { vi } from 'vitest';

vi.mock('axios');
const mockedAxios = vi.mocked(axios, true);

describe('useWorkflows', () => {
  it('fetches workflows successfully', async () => {
    const mockWorkflows = [
      { id: 1, name: 'Workflow 1', status: 'active' },
      { id: 2, name: 'Workflow 2', status: 'inactive' },
    ];

    mockedAxios.get.mockResolvedValueOnce({
      data: { data: mockWorkflows },
    });

    const { result } = renderHook(() => useWorkflows(), {
      wrapper: createWrapper(),
    });

    // État initial : loading
    expect(result.current.isLoading).toBe(true);
    expect(result.current.data).toBeUndefined();

    // Attendre la résolution
    await waitFor(() => expect(result.current.isSuccess).toBe(true));

    // Vérifier les données
    expect(result.current.data).toEqual(mockWorkflows);
    expect(result.current.data).toHaveLength(2);
  });

  it('handles empty workflows list', async () => {
    mockedAxios.get.mockResolvedValueOnce({
      data: { data: [] },
    });

    const { result } = renderHook(() => useWorkflows(), {
      wrapper: createWrapper(),
    });

    await waitFor(() => expect(result.current.isSuccess).toBe(true));

    expect(result.current.data).toEqual([]);
    expect(result.current.data).toHaveLength(0);
  });

  it('handles error state', async () => {
    const error = new Error('Network error');
    mockedAxios.get.mockRejectedValueOnce(error);

    const { result } = renderHook(() => useWorkflows(), {
      wrapper: createWrapper(),
    });

    await waitFor(() => expect(result.current.isError).toBe(true));

    expect(result.current.error).toBeDefined();
    expect(result.current.data).toBeUndefined();
  });

  it('calls correct API endpoint', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: { data: [] } });

    renderHook(() => useWorkflows(), {
      wrapper: createWrapper(),
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith('/api/workflows');
    });
  });

  it('supports filtering by status', async () => {
    const mockWorkflows = [
      { id: 1, name: 'Workflow 1', status: 'active' },
    ];

    mockedAxios.get.mockResolvedValueOnce({
      data: { data: mockWorkflows },
    });

    const { result } = renderHook(
      () => useWorkflows({ status: 'active' }),
      { wrapper: createWrapper() }
    );

    await waitFor(() => expect(result.current.isSuccess).toBe(true));

    expect(mockedAxios.get).toHaveBeenCalledWith('/api/workflows', {
      params: { status: 'active' },
    });
  });
});
```

### Test 3 : Page Workflows Index

**Fichier** : `resources/js/pages/Workflows/__tests__/Index.test.tsx`

```typescript
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import WorkflowsIndex from '../Index';
import { vi } from 'vitest';

// Mock Inertia
vi.mock('@inertiajs/react', () => ({
  ...vi.importActual('@inertiajs/react'),
  router: {
    visit: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
  usePage: () => ({
    props: {
      auth: {
        user: { id: 1, name: 'Test User', email: 'test@example.com' },
      },
    },
  }),
}));

describe('Workflows Index Page', () => {
  const mockWorkflows = [
    {
      id: 1,
      name: 'Workflow 1',
      description: 'Description 1',
      status: 'active',
      llm_provider: 'OpenAI',
      llm_model: 'gpt-4',
      created_at: '2025-01-01T00:00:00Z',
    },
    {
      id: 2,
      name: 'Workflow 2',
      description: 'Description 2',
      status: 'inactive',
      llm_provider: 'Mistral',
      llm_model: 'mistral-large',
      created_at: '2025-01-02T00:00:00Z',
    },
  ];

  it('renders workflows list', () => {
    render(<WorkflowsIndex workflows={mockWorkflows} />);

    expect(screen.getByRole('heading', { name: /workflows/i })).toBeInTheDocument();
    expect(screen.getByText('Workflow 1')).toBeInTheDocument();
    expect(screen.getByText('Workflow 2')).toBeInTheDocument();
  });

  it('renders empty state when no workflows', () => {
    render(<WorkflowsIndex workflows={[]} />);

    expect(screen.getByText(/no workflows yet/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /create your first workflow/i })).toBeInTheDocument();
  });

  it('opens create modal when clicking create button', async () => {
    const user = userEvent.setup();
    render(<WorkflowsIndex workflows={mockWorkflows} />);

    const createButton = screen.getByRole('button', { name: /create workflow/i });
    await user.click(createButton);

    // Vérifier que le modal s'ouvre
    expect(screen.getByRole('dialog')).toBeInTheDocument();
    expect(screen.getByLabelText(/workflow name/i)).toBeInTheDocument();
  });

  it('filters workflows by search query', async () => {
    const user = userEvent.setup();
    render(<WorkflowsIndex workflows={mockWorkflows} />);

    const searchInput = screen.getByPlaceholderText(/search workflows/i);
    await user.type(searchInput, 'Workflow 1');

    // Seul "Workflow 1" devrait être visible
    await waitFor(() => {
      expect(screen.getByText('Workflow 1')).toBeVisible();
      expect(screen.queryByText('Workflow 2')).not.toBeInTheDocument();
    });
  });

  it('filters workflows by status', async () => {
    const user = userEvent.setup();
    render(<WorkflowsIndex workflows={mockWorkflows} />);

    const statusFilter = screen.getByLabelText(/filter by status/i);
    await user.selectOptions(statusFilter, 'active');

    // Seuls les workflows actifs devraient être visibles
    await waitFor(() => {
      expect(screen.getByText('Workflow 1')).toBeVisible();
      expect(screen.queryByText('Workflow 2')).not.toBeInTheDocument();
    });
  });

  it('displays workflow count', () => {
    render(<WorkflowsIndex workflows={mockWorkflows} />);

    expect(screen.getByText(/2 workflows/i)).toBeInTheDocument();
  });
});
```

---

## 🌐 Tests E2E (Playwright)

### Test 1 : Parcours complet de création de workflow

**Fichier** : `tests/e2e/workflows/create-workflow.spec.ts`

```typescript
import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';
import { resetDatabase } from '../fixtures/database';

test.describe('Workflow Creation', () => {
  test.beforeAll(async () => {
    await resetDatabase();
  });

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('user can create a new workflow', async ({ page }) => {
    // Aller sur la page des workflows
    await page.goto('/workflows');
    await expect(page).toHaveTitle(/Workflows/);

    // Vérifier qu'on voit le bouton "Create"
    const createButton = page.getByRole('button', { name: /create workflow/i });
    await expect(createButton).toBeVisible();

    // Cliquer sur "Create Workflow"
    await createButton.click();

    // Le modal devrait s'ouvrir
    const modal = page.locator('dialog[open]');
    await expect(modal).toBeVisible();
    await expect(modal).toContainText('Create Workflow');

    // Remplir le formulaire
    await page.fill('input[name="name"]', 'E2E Test Workflow');
    await page.fill(
      'textarea[name="description"]',
      'This workflow was created by an automated E2E test'
    );

    // Sélectionner le LLM provider
    await page.selectOption('select[name="llm_provider"]', 'OpenAI');

    // Sélectionner le modèle
    await page.selectOption('select[name="llm_model"]', 'gpt-4');

    // Soumettre le formulaire
    await page.click('button[type="submit"]:has-text("Create")');

    // Vérifier le message de succès
    const toast = page.locator('.toast-success');
    await expect(toast).toContainText('Workflow created successfully', {
      timeout: 5000,
    });

    // Vérifier la redirection vers la page du workflow
    await expect(page).toHaveURL(/\/workflows\/\d+/);

    // Vérifier le titre de la page
    await expect(page.locator('h1')).toContainText('E2E Test Workflow');

    // Vérifier que les informations sont correctes
    await expect(page.locator('[data-testid="workflow-description"]')).toContainText(
      'This workflow was created by an automated E2E test'
    );

    await expect(page.locator('[data-testid="llm-provider"]')).toContainText('OpenAI');
    await expect(page.locator('[data-testid="llm-model"]')).toContainText('gpt-4');

    // Vérifier le statut initial
    const statusBadge = page.locator('[data-testid="workflow-status"]');
    await expect(statusBadge).toContainText('Draft');
  });

  test('shows validation errors for empty name', async ({ page }) => {
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    // Soumettre sans remplir le nom
    await page.click('button[type="submit"]:has-text("Create")');

    // Vérifier l'erreur de validation
    const errorMessage = page.locator('.error-message');
    await expect(errorMessage).toContainText('The name field is required');

    // Le modal devrait rester ouvert
    const modal = page.locator('dialog[open]');
    await expect(modal).toBeVisible();
  });

  test('can cancel workflow creation', async ({ page }) => {
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    // Remplir partiellement
    await page.fill('input[name="name"]', 'Test');

    // Cliquer sur Cancel
    await page.click('button:has-text("Cancel")');

    // Le modal devrait se fermer
    const modal = page.locator('dialog[open]');
    await expect(modal).not.toBeVisible();

    // On devrait toujours être sur /workflows
    await expect(page).toHaveURL('/workflows');
  });

  test('creates workflow with all optional fields', async ({ page }) => {
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    // Remplir tous les champs
    await page.fill('input[name="name"]', 'Complete Workflow');
    await page.fill('textarea[name="description"]', 'Full description');
    await page.selectOption('select[name="llm_provider"]', 'OpenAI');
    await page.selectOption('select[name="llm_model"]', 'gpt-4');

    // Configuration avancée
    await page.click('button:has-text("Advanced Settings")');

    await page.fill('input[name="temperature"]', '0.7');
    await page.fill('input[name="max_tokens"]', '2000');
    await page.check('input[name="stream"]');

    // Soumettre
    await page.click('button[type="submit"]:has-text("Create")');

    // Vérifier la création
    await expect(page).toHaveURL(/\/workflows\/\d+/);

    // Vérifier les paramètres avancés
    await expect(page.locator('[data-testid="temperature"]')).toContainText('0.7');
    await expect(page.locator('[data-testid="max-tokens"]')).toContainText('2000');
    await expect(page.locator('[data-testid="stream-enabled"]')).toContainText('Yes');
  });
});
```

### Test 2 : Exécution de workflow avec logs temps réel

**Fichier** : `tests/e2e/workflows/execute-workflow.spec.ts`

```typescript
import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';
import { seedWorkflows } from '../fixtures/database';

test.describe('Workflow Execution', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
    await seedWorkflows(1); // Créer 1 workflow de test
  });

  test('executes workflow and shows real-time logs', async ({ page }) => {
    // Aller sur le workflow
    await page.goto('/workflows/1');

    // Vérifier le statut initial
    const statusBadge = page.locator('[data-testid="workflow-status"]');
    await expect(statusBadge).not.toContainText('Running');

    // Cliquer sur "Execute"
    await page.click('button:has-text("Execute Workflow")');

    // Le statut devrait changer à "Running"
    await expect(statusBadge).toContainText('Running', { timeout: 5000 });

    // Le bouton Execute devrait être désactivé
    const executeButton = page.getByRole('button', { name: /execute workflow/i });
    await expect(executeButton).toBeDisabled();

    // Les logs devraient apparaître
    const logsContainer = page.locator('[data-testid="live-logs"]');
    await expect(logsContainer).toBeVisible();

    // Attendre les premiers logs
    await expect(logsContainer.locator('.log-entry').first()).toBeVisible({
      timeout: 10000,
    });

    // Vérifier que le premier log contient "Starting"
    const firstLog = logsContainer.locator('.log-entry').first();
    await expect(firstLog).toContainText(/Starting|Initializing/i);

    // Attendre que d'autres logs apparaissent (WebSocket)
    await page.waitForFunction(
      () => {
        const logs = document.querySelectorAll('[data-testid="live-logs"] .log-entry');
        return logs.length > 3;
      },
      { timeout: 15000 }
    );

    // Vérifier que les logs ont des timestamps
    const logEntries = logsContainer.locator('.log-entry');
    const firstLogTimestamp = await logEntries.first().locator('.timestamp').textContent();
    expect(firstLogTimestamp).toMatch(/\d{2}:\d{2}:\d{2}/);

    // Attendre la fin de l'exécution
    await expect(statusBadge).toContainText(/Completed|Failed/, {
      timeout: 60000, // Max 1 minute
    });

    // Le bouton Execute devrait être réactivé
    await expect(executeButton).toBeEnabled();

    // Si succès, vérifier les résultats
    const status = await statusBadge.textContent();
    if (status?.includes('Completed')) {
      // Vérifier le résumé d'exécution
      const summary = page.locator('[data-testid="execution-summary"]');
      await expect(summary).toBeVisible();

      // Vérifier la durée
      const duration = page.locator('[data-testid="execution-duration"]');
      await expect(duration).toContainText(/\d+ (seconds?|minutes?)/);

      // Vérifier le nombre de logs
      const logCount = await logEntries.count();
      expect(logCount).toBeGreaterThan(3);
    }
  });

  test('can stop running workflow', async ({ page }) => {
    await page.goto('/workflows/1');

    // Démarrer l'exécution
    await page.click('button:has-text("Execute Workflow")');

    // Attendre que ça démarre
    const statusBadge = page.locator('[data-testid="workflow-status"]');
    await expect(statusBadge).toContainText('Running', { timeout: 5000 });

    // Le bouton "Stop" devrait apparaître
    const stopButton = page.getByRole('button', { name: /stop|cancel/i });
    await expect(stopButton).toBeVisible();

    // Cliquer sur Stop
    await stopButton.click();

    // Confirmer l'arrêt
    const confirmDialog = page.locator('dialog[open]');
    await expect(confirmDialog).toContainText('Are you sure');
    await confirmDialog.locator('button:has-text("Stop")').click();

    // Le statut devrait changer à "Cancelled"
    await expect(statusBadge).toContainText('Cancelled', { timeout: 10000 });

    // Un message devrait confirmer l'arrêt
    const toast = page.locator('.toast-info');
    await expect(toast).toContainText('Workflow stopped');
  });

  test('handles workflow execution failure', async ({ page }) => {
    // Créer un workflow qui va échouer (mock ou config spéciale)
    await page.goto('/workflows/1/edit');

    // Configurer pour échouer
    await page.fill('input[name="llm_api_key"]', 'invalid_key');
    await page.click('button:has-text("Save")');

    // Exécuter
    await page.goto('/workflows/1');
    await page.click('button:has-text("Execute Workflow")');

    // Attendre l'échec
    const statusBadge = page.locator('[data-testid="workflow-status"]');
    await expect(statusBadge).toContainText('Failed', { timeout: 30000 });

    // Vérifier le message d'erreur
    const errorMessage = page.locator('[data-testid="error-message"]');
    await expect(errorMessage).toBeVisible();
    await expect(errorMessage).toContainText(/error|failed|invalid/i);

    // Les logs devraient contenir l'erreur
    const logsContainer = page.locator('[data-testid="live-logs"]');
    const errorLogs = logsContainer.locator('.log-entry.error');
    await expect(errorLogs.first()).toBeVisible();
  });

  test('shows execution history', async ({ page }) => {
    await page.goto('/workflows/1');

    // Exécuter 2 fois
    for (let i = 0; i < 2; i++) {
      await page.click('button:has-text("Execute Workflow")');

      // Attendre la fin
      const statusBadge = page.locator('[data-testid="workflow-status"]');
      await expect(statusBadge).toContainText(/Completed|Failed/, {
        timeout: 60000,
      });

      await page.waitForTimeout(2000); // Petite pause entre les exécutions
    }

    // Aller dans l'historique
    await page.click('a:has-text("Execution History")');

    // Vérifier qu'on a 2 exécutions
    const executions = page.locator('[data-testid="execution-row"]');
    await expect(executions).toHaveCount(2);

    // Vérifier que chaque exécution a un timestamp
    const firstExecution = executions.first();
    await expect(firstExecution.locator('.timestamp')).toContainText(/\d{4}-\d{2}-\d{2}/);

    // Cliquer sur une exécution pour voir les détails
    await firstExecution.click();

    // Vérifier qu'on voit les logs de cette exécution
    const logsContainer = page.locator('[data-testid="execution-logs"]');
    await expect(logsContainer).toBeVisible();
  });
});
```

---

## 🔗 Tests d'intégration complets

### Test Full-Stack : Création → Exécution → Vérification

**Fichier** : `tests/e2e/workflows/full-lifecycle.spec.ts`

```typescript
import { test, expect } from '@playwright/test';
import { login } from '../fixtures/auth';
import { resetDatabase } from '../fixtures/database';
import axios from 'axios';

test.describe('Workflow Full Lifecycle', () => {
  test.beforeAll(async () => {
    await resetDatabase();
  });

  test('complete workflow lifecycle from creation to deletion', async ({ page }) => {
    // 1. Login
    await login(page);

    // 2. Créer un workflow
    await page.goto('/workflows');
    await page.click('button:has-text("Create Workflow")');

    const workflowName = `Test Workflow ${Date.now()}`;
    await page.fill('input[name="name"]', workflowName);
    await page.fill('textarea[name="description"]', 'Full lifecycle test');
    await page.selectOption('select[name="llm_provider"]', 'OpenAI');
    await page.selectOption('select[name="llm_model"]', 'gpt-4');

    await page.click('button[type="submit"]:has-text("Create")');

    // Attendre la création
    await expect(page).toHaveURL(/\/workflows\/\d+/);
    const url = page.url();
    const workflowId = url.match(/\/workflows\/(\d+)/)?.[1];

    console.log(`Created workflow with ID: ${workflowId}`);

    // 3. Éditer le workflow
    await page.click('button:has-text("Edit")');

    await page.fill('textarea[name="description"]', 'Updated description');
    await page.click('button[type="submit"]:has-text("Save")');

    await expect(page.locator('.toast-success')).toContainText('Workflow updated');

    // 4. Exécuter le workflow
    await page.click('button:has-text("Execute Workflow")');

    const statusBadge = page.locator('[data-testid="workflow-status"]');
    await expect(statusBadge).toContainText('Running', { timeout: 5000 });

    // Attendre la fin
    await expect(statusBadge).toContainText(/Completed|Failed/, {
      timeout: 60000,
    });

    // 5. Vérifier dans la base de données (via API)
    const response = await axios.get(`http://localhost:3978/api/workflows/${workflowId}`, {
      headers: {
        // Récupérer le cookie de session
        Cookie: await page.context().cookies().then(cookies =>
          cookies.map(c => `${c.name}=${c.value}`).join('; ')
        ),
      },
    });

    expect(response.status).toBe(200);
    expect(response.data.data.name).toBe(workflowName);
    expect(response.data.data.description).toBe('Updated description');

    // 6. Vérifier l'historique d'exécution
    await page.click('a:has-text("Execution History")');

    const executions = page.locator('[data-testid="execution-row"]');
    await expect(executions).toHaveCount(1);

    // 7. Retourner à la liste
    await page.click('a:has-text("Back to workflows")');
    await expect(page).toHaveURL('/workflows');

    // Vérifier que le workflow apparaît dans la liste
    const workflowCard = page.locator(`[data-testid="workflow-card"]:has-text("${workflowName}")`);
    await expect(workflowCard).toBeVisible();

    // 8. Supprimer le workflow
    await workflowCard.locator('button[aria-label="Delete"]').click();

    // Confirmer la suppression
    const confirmDialog = page.locator('dialog[open]');
    await expect(confirmDialog).toContainText('Are you sure');
    await confirmDialog.locator('button:has-text("Delete")').click();

    // Vérifier la suppression
    await expect(page.locator('.toast-success')).toContainText('Workflow deleted');

    // Le workflow ne devrait plus apparaître
    await expect(workflowCard).not.toBeVisible();

    // 9. Vérifier en base de données que le workflow est bien supprimé
    try {
      await axios.get(`http://localhost:3978/api/workflows/${workflowId}`, {
        headers: {
          Cookie: await page.context().cookies().then(cookies =>
            cookies.map(c => `${c.name}=${c.value}`).join('; ')
          ),
        },
      });
      // Si on arrive ici, c'est une erreur (le workflow devrait être supprimé)
      throw new Error('Workflow should have been deleted');
    } catch (error: any) {
      // On s'attend à une erreur 404
      expect(error.response?.status).toBe(404);
    }
  });
});
```

---

## 📊 Récapitulatif

Vous avez maintenant :

1. ✅ **Tests backend complets** (API, services, jobs)
2. ✅ **Tests frontend** (composants, hooks, pages)
3. ✅ **Tests E2E** (parcours utilisateur complets)
4. ✅ **Tests d'intégration** (full-stack)

### Prochaines étapes

1. Copier ces exemples dans votre projet
2. Adapter à votre structure de code
3. Exécuter les tests et corriger les erreurs
4. Ajouter plus de tests pour couvrir tous les cas

**Bon développement !** 🚀
