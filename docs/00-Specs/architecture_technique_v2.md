# Architecture Technique v2 - AgentOps Platform
**Version 2.0 - Architecture à 2 Applications**

---

## Table des Matières

1. [Vue d'Ensemble](#vue-densemble)
2. [Architecture Globale](#architecture-globale)
3. [Application Principale (Laravel + React + Inertia.js)](#application-principale)
4. [AI Engine (FastAPI)](#ai-engine)
5. [Infrastructure & Déploiement](#infrastructure--déploiement)
6. [Base de Données & Stockage](#base-de-données--stockage)
7. [Sécurité](#sécurité)
8. [Performance & Scalabilité](#performance--scalabilité)
9. [Monitoring & Observabilité](#monitoring--observabilité)
10. [CI/CD](#cicd)
11. [Stratégie de Déploiement](#stratégie-de-déploiement)

---

## Vue d'Ensemble

### Changement Architectural Majeur

**Architecture v1 (3 Applications Séparées)**:
- Backend Laravel (API REST)
- Frontend React (SPA)
- AI Engine FastAPI

**Architecture v2 (2 Applications)**:
- **Application Principale**: Laravel 12 + React 19 + Inertia.js (monolithe SSR-capable)
- **AI Engine**: FastAPI + LLM Router (service indépendant)

### Bénéfices de la Consolidation

1. **Simplicité Opérationnelle**: Un seul serveur/conteneur pour l'application principale
2. **Latence Réduite**: Pas de round-trip réseau entre backend et frontend
3. **SSR Native**: Server-Side Rendering avec Inertia.js
4. **Coûts Réduits**: Moins d'infrastructure en Phase 1
5. **Time-to-Market**: Développement et déploiement plus rapides

### Séparation AI Engine

L'AI Engine reste séparé pour:
- **Scaling Indépendant**: Les workloads ML nécessitent des ressources différentes
- **GPU Support**: Phase 3 requiert des instances GPU (AWS EKS)
- **Isolation**: Les opérations IA intensives n'affectent pas l'application principale
- **Langage Optimal**: Python pour ML/IA, PHP pour business logic

---

## Architecture Globale

### Diagramme de Composants

```
┌─────────────────────────────────────────────────────────────────────┐
│                         UTILISATEURS                                │
│                  (Navigateurs Web + WebSocket)                      │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               │ HTTPS (Nginx/Caddy)
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    APPLICATION PRINCIPALE                           │
│              Laravel 12 + React 19 + Inertia.js                     │
│─────────────────────────────────────────────────────────────────────│
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                  BACKEND (Laravel 12)                        │  │
│  │                                                              │  │
│  │  ┌────────────────┐  ┌─────────────────┐  ┌──────────────┐ │  │
│  │  │ Controllers    │  │ Services        │  │ Jobs/Queue   │ │  │
│  │  │ • Auth         │  │ • Workflow      │  │ • Redis      │ │  │
│  │  │ • Projects     │  │ • Integration   │  │ • Horizon    │ │  │
│  │  │ • Workflow     │  │ • Billing       │  │              │ │  │
│  │  │ • Webhook      │  │ • Notification  │  │              │ │  │
│  │  └────────────────┘  └─────────────────┘  └──────────────┘ │  │
│  │                                                              │  │
│  │  ┌────────────────┐  ┌─────────────────┐  ┌──────────────┐ │  │
│  │  │ Middleware     │  │ Events          │  │ WebSocket    │ │  │
│  │  │ • Sanctum      │  │ • Listeners     │  │ • Echo       │ │  │
│  │  │ • 2FA          │  │ • Observers     │  │ • Reverb     │ │  │
│  │  │ • Throttle     │  │                 │  │              │ │  │
│  │  └────────────────┘  └─────────────────┘  └──────────────┘ │  │
│  │                                                              │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│                          ▲                                          │
│                          │ Inertia.js (Bridge SSR)                 │
│                          ▼                                          │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                 FRONTEND (React 19)                          │  │
│  │                                                              │  │
│  │  ┌────────────────┐  ┌─────────────────┐  ┌──────────────┐ │  │
│  │  │ Pages          │  │ Components      │  │ Hooks        │ │  │
│  │  │ • Dashboard    │  │ • UI (shadcn)   │  │ • useForm    │ │  │
│  │  │ • Workflow     │  │ • WorkflowViz   │  │ • useQuery   │ │  │
│  │  │ • Settings     │  │ • CodeEditor    │  │ • useWebSock │ │  │
│  │  └────────────────┘  └─────────────────┘  └──────────────┘ │  │
│  │                                                              │  │
│  │  ┌────────────────┐  ┌─────────────────┐                   │  │
│  │  │ State Mgmt     │  │ Real-time       │                   │  │
│  │  │ • Inertia Form │  │ • Laravel Echo  │                   │  │
│  │  │ • React Query  │  │ • WebSocket     │                   │  │
│  │  └────────────────┘  └─────────────────┘                   │  │
│  │                                                              │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
└────────────────────────┬────────────────────────────────────────────┘
                         │
                         │ API HTTP + WebSocket
                         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        AI ENGINE (FastAPI)                          │
│                       Python 3.12 + LiteLLM                         │
│─────────────────────────────────────────────────────────────────────│
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                    LLM Router Service                        │  │
│  │                                                              │  │
│  │  • GPT-4 Turbo (OpenAI)                                     │  │
│  │  • Mistral Large (Mistral AI)                               │  │
│  │  • Claude 3.5 Sonnet (Anthropic)                            │  │
│  │  • Ollama (Local - Phase 3)                                 │  │
│  │                                                              │  │
│  │  ┌──────────────────────────────────────────────────────┐   │  │
│  │  │ Routing Logic:                                       │   │  │
│  │  │ • Latency-based selection                           │   │  │
│  │  │ • Cost optimization                                  │   │  │
│  │  │ • Fallback strategy                                  │   │  │
│  │  └──────────────────────────────────────────────────────┘   │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                 Code Analysis Service                        │  │
│  │                                                              │  │
│  │  • AST Parser Multi-Language:                               │  │
│  │    - tree-sitter (Python, PHP, JS/TS, Go, Rust)            │  │
│  │  • Code Intelligence Map Generation                         │  │
│  │  • Dependency Graph Analysis                                │  │
│  │  • Test Coverage Analysis                                   │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                 MCP Integration Layer                        │  │
│  │                                                              │  │
│  │  • Notion MCP Server                                        │  │
│  │  • GitHub MCP Server (Phase 2)                              │  │
│  │  • GitLab MCP Server (Phase 2)                              │  │
│  │  • Linear MCP Server (Phase 3)                              │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘

                         │
                         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     INFRASTRUCTURE LAYER                            │
│─────────────────────────────────────────────────────────────────────│
│                                                                     │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐ │
│  │ PostgreSQL 16    │  │ Redis 7 Cluster  │  │ RabbitMQ 3.13    │ │
│  │ + Extensions     │  │                  │  │                  │ │
│  │ • pg_vector      │  │ • Cache          │  │ • Workflow Queue │ │
│  │ • uuid-ossp      │  │ • Queue          │  │ • Event Bus      │ │
│  │ • pg_trgm        │  │ • Pub/Sub        │  │                  │ │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘ │
│                                                                     │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐ │
│  │ S3/Spaces        │  │ Prometheus       │  │ Sentry           │ │
│  │ • Repositories   │  │ • Metrics        │  │ • Error Track    │ │
│  │ • Artifacts      │  │ • Alerts         │  │                  │ │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘ │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Application Principale

### Stack Technique Laravel

| Composant | Technologie | Version | Rôle |
|-----------|-------------|---------|------|
| **Framework** | Laravel | 12.x | Backend framework PHP |
| **PHP** | PHP | 8.4+ | Langage serveur |
| **Authentification** | Laravel Sanctum | 4.x | API Token + SPA auth |
| **2FA** | laravel/fortify | 1.x | Two-Factor Auth |
| **Queue** | Laravel Horizon | 5.x | Queue monitoring (Redis) |
| **Broadcasting** | Laravel Reverb | 1.x | WebSocket server natif |
| **Billing** | Laravel Cashier | 15.x | Stripe integration |
| **Jobs** | Laravel Queue | Built-in | Background jobs |
| **Cache** | Redis | 7.2 | Cache + Session + Queue |
| **Validation** | Laravel Validation | Built-in | Request validation |
| **ORM** | Eloquent | Built-in | Database ORM |
| **Testing** | PHPUnit | 11.x | Unit/Feature tests |
| **Static Analysis** | PHPStan | 1.x | Type checking (level max) |
| **Code Style** | Laravel Pint | 1.x | Code formatter |

### Stack Technique React

| Composant | Technologie | Version | Rôle |
|-----------|-------------|---------|------|
| **Framework** | React | 19.x | UI library |
| **Bridge** | Inertia.js | 2.x | Laravel ↔ React (SSR) |
| **Langage** | TypeScript | 5.7+ | Type safety |
| **Build Tool** | Vite | 6.x | Dev server + bundler |
| **UI Components** | Radix UI | 1.x | Accessible primitives |
| **Styling** | TailwindCSS | 4.x | Utility-first CSS |
| **Forms** | Inertia Forms | Built-in | Form state management |
| **Data Fetching** | React Query | 5.x | Server state (AI Engine) |
| **WebSocket** | Laravel Echo | 1.x | Real-time events |
| **Code Editor** | Monaco Editor | Latest | In-browser code editor |
| **Visualization** | D3.js | 7.x | Workflow visualization |
| **Testing** | Vitest | 2.x | Unit tests |
| **E2E Testing** | Playwright | 1.x | End-to-end tests |

### Architecture Backend Laravel

#### Structure des Dossiers

```
app/
├── Console/
│   └── Commands/          # Artisan custom commands
├── Enums/
│   ├── IntegrationType.php
│   ├── IntegrationStatus.php
│   ├── WorkflowStatus.php
│   └── SubscriptionTier.php
├── Events/
│   ├── WorkflowCompleted.php
│   ├── TestExecuted.php
│   └── IntegrationConnected.php
├── Exceptions/
│   ├── Handler.php
│   └── WorkflowException.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/          # Breeze controllers
│   │   ├── DashboardController.php
│   │   ├── WorkflowController.php
│   │   ├── ProjectController.php
│   │   ├── IntegrationController.php
│   │   └── WebhookController.php
│   ├── Middleware/
│   │   ├── HasActiveIntegration.php
│   │   ├── CheckSubscription.php
│   │   └── LogRequest.php
│   └── Requests/
│       ├── WorkflowRequest.php
│       └── ProjectRequest.php
├── Jobs/
│   ├── ExecuteWorkflowJob.php
│   ├── RunTestSuiteJob.php
│   ├── AnalyzeCodeJob.php
│   └── SyncRepositoryJob.php
├── Listeners/
│   ├── SendWorkflowNotification.php
│   └── UpdateWorkflowStats.php
├── Models/
│   ├── User.php
│   ├── Team.php
│   ├── Project.php
│   ├── Repository.php
│   ├── Workflow.php
│   ├── WorkflowStep.php
│   ├── IntegrationAccount.php
│   └── Subscription.php
├── Notifications/
│   ├── WorkflowCompletedNotification.php
│   └── TestFailedNotification.php
├── Policies/
│   ├── ProjectPolicy.php
│   └── WorkflowPolicy.php
├── Providers/
│   ├── AppServiceProvider.php
│   ├── EventServiceProvider.php
│   └── BroadcastServiceProvider.php
└── Services/
    ├── WorkflowOrchestrator.php
    ├── AIEngineClient.php
    ├── NotionService.php
    ├── GitService.php
    └── StripeService.php
```

#### Modèle de Données (Eloquent)

**User Model** (`app/Models/User.php`)
```php
class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    public function projects(): HasManyThrough
    {
        return $this->hasManyThrough(Project::class, Team::class);
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(IntegrationAccount::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }
}
```

**Project Model**
```php
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id', 'name', 'description', 'repository_url',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }
}
```

**Workflow Model**
```php
class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'name', 'trigger', 'status', 'meta',
    ];

    protected $casts = [
        'trigger' => 'array',
        'meta' => 'array',
        'status' => WorkflowStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class);
    }
}
```

**IntegrationAccount Model**
```php
class IntegrationAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'access_token', 'refresh_token',
        'meta', 'status', 'expires_at',
    ];

    protected $casts = [
        'type' => IntegrationType::class,
        'status' => IntegrationStatus::class,
        'meta' => 'array',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token', 'refresh_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Encryption des tokens
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }
}
```

#### Services Layer

**WorkflowOrchestrator Service** (`app/Services/WorkflowOrchestrator.php`)
```php
class WorkflowOrchestrator
{
    public function __construct(
        private AIEngineClient $aiEngine,
        private GitService $git,
    ) {}

    public function execute(Workflow $workflow): void
    {
        DB::transaction(function () use ($workflow) {
            $workflow->update(['status' => WorkflowStatus::Running]);

            foreach ($workflow->steps as $step) {
                $this->executeStep($workflow, $step);
            }

            $workflow->update([
                'status' => WorkflowStatus::Completed,
                'completed_at' => now(),
            ]);
        });

        event(new WorkflowCompleted($workflow));
    }

    private function executeStep(Workflow $workflow, WorkflowStep $step): void
    {
        match ($step->type) {
            'code_analysis' => $this->aiEngine->analyzeCode($step->config),
            'test_generation' => $this->aiEngine->generateTests($step->config),
            'git_commit' => $this->git->commit($step->config),
            default => throw new WorkflowException("Unknown step type: {$step->type}")
        };
    }
}
```

**AIEngineClient Service** (`app/Services/AIEngineClient.php`)
```php
class AIEngineClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ai_engine.url');
    }

    public function analyzeCode(array $config): array
    {
        return Http::timeout(120)
            ->post("{$this->baseUrl}/api/v1/analyze", [
                'repository_path' => $config['path'],
                'language' => $config['language'],
                'include_dependencies' => true,
            ])
            ->throw()
            ->json();
    }

    public function generateTests(array $config): array
    {
        return Http::timeout(180)
            ->post("{$this->baseUrl}/api/v1/tests/generate", [
                'file_path' => $config['file'],
                'test_framework' => $config['framework'],
                'coverage_target' => $config['coverage'] ?? 80,
            ])
            ->throw()
            ->json();
    }

    public function routeLLM(string $prompt, array $options = []): array
    {
        return Http::timeout(60)
            ->post("{$this->baseUrl}/api/v1/llm/route", [
                'prompt' => $prompt,
                'max_tokens' => $options['max_tokens'] ?? 2000,
                'temperature' => $options['temperature'] ?? 0.7,
            ])
            ->throw()
            ->json();
    }
}
```

#### Jobs & Queue

**ExecuteWorkflowJob** (`app/Jobs/ExecuteWorkflowJob.php`)
```php
class ExecuteWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Workflow $workflow,
    ) {}

    public function handle(WorkflowOrchestrator $orchestrator): void
    {
        try {
            $orchestrator->execute($this->workflow);
        } catch (\Exception $e) {
            $this->workflow->update([
                'status' => WorkflowStatus::Failed,
                'error' => $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Workflow execution failed', [
            'workflow_id' => $this->workflow->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

#### Broadcasting & WebSocket

**Laravel Reverb Configuration** (`config/reverb.php`)
```php
return [
    'default' => 'reverb',

    'connections' => [
        'reverb' => [
            'driver' => 'reverb',
            'host' => env('REVERB_HOST', '0.0.0.0'),
            'port' => env('REVERB_PORT', 8080),
            'scheme' => env('REVERB_SCHEME', 'http'),
            'app_id' => env('REVERB_APP_ID'),
            'app_key' => env('REVERB_APP_KEY'),
            'app_secret' => env('REVERB_APP_SECRET'),
        ],
    ],
];
```

**WorkflowChannel** (`app/Broadcasting/WorkflowChannel.php`)
```php
class WorkflowChannel
{
    public function join(User $user, int $workflowId): bool
    {
        $workflow = Workflow::findOrFail($workflowId);

        return $user->can('view', $workflow->project);
    }
}
```

**Broadcasting Event** (`app/Events/WorkflowCompleted.php`)
```php
class WorkflowCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Workflow $workflow,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("workflow.{$this->workflow->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'workflow.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'workflow_id' => $this->workflow->id,
            'status' => $this->workflow->status->value,
            'completed_at' => $this->workflow->completed_at?->toIso8601String(),
        ];
    }
}
```

### Architecture Frontend React

#### Structure des Dossiers

```
resources/js/
├── app.tsx                 # Entry point
├── bootstrap.ts            # Laravel Echo setup
├── components/
│   ├── ui/                 # shadcn/ui components
│   │   ├── button.tsx
│   │   ├── card.tsx
│   │   ├── dialog.tsx
│   │   └── ...
│   ├── layout/
│   │   ├── AppLayout.tsx
│   │   ├── Sidebar.tsx
│   │   └── Header.tsx
│   ├── workflow/
│   │   ├── WorkflowList.tsx
│   │   ├── WorkflowViewer.tsx
│   │   ├── WorkflowGraph.tsx
│   │   └── StepExecutor.tsx
│   ├── code/
│   │   ├── CodeEditor.tsx
│   │   ├── DiffViewer.tsx
│   │   └── FileTree.tsx
│   └── integrations/
│       ├── IntegrationCard.tsx
│       ├── NotionConnect.tsx
│       └── GitHubConnect.tsx
├── hooks/
│   ├── use-workflow.ts
│   ├── use-integrations.ts
│   ├── use-websocket.ts
│   └── use-appearance.tsx
├── layouts/
│   ├── AppLayout.tsx
│   ├── AuthLayout.tsx
│   └── SettingsLayout.tsx
├── pages/
│   ├── Dashboard.tsx
│   ├── Workflows/
│   │   ├── Index.tsx
│   │   ├── Show.tsx
│   │   └── Create.tsx
│   ├── Projects/
│   │   ├── Index.tsx
│   │   └── Show.tsx
│   ├── Integrations.tsx
│   ├── Settings/
│   │   ├── Profile.tsx
│   │   ├── Team.tsx
│   │   └── Billing.tsx
│   └── Auth/
│       ├── Login.tsx
│       └── Register.tsx
├── types/
│   ├── index.d.ts
│   ├── models.ts
│   └── api.ts
└── lib/
    ├── utils.ts
    ├── api-client.ts
    └── websocket.ts
```

#### Inertia.js Integration

**Page Component Example** (`resources/js/pages/Dashboard.tsx`)
```typescript
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import AppLayout from '@/layouts/AppLayout';
import { WorkflowList } from '@/components/workflow/WorkflowList';
import { StatsCard } from '@/components/ui/StatsCard';

interface DashboardProps extends PageProps {
  stats: {
    total_workflows: number;
    active_workflows: number;
    completed_today: number;
  };
  recent_workflows: Workflow[];
}

export default function Dashboard({ auth, stats, recent_workflows }: DashboardProps) {
  return (
    <AppLayout user={auth.user}>
      <Head title="Dashboard" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <StatsCard
              title="Total Workflows"
              value={stats.total_workflows}
              icon="workflow"
            />
            <StatsCard
              title="Active Now"
              value={stats.active_workflows}
              icon="play"
            />
            <StatsCard
              title="Completed Today"
              value={stats.completed_today}
              icon="check"
            />
          </div>

          <WorkflowList workflows={recent_workflows} />
        </div>
      </div>
    </AppLayout>
  );
}
```

**Inertia Form with Real-time Updates** (`resources/js/pages/Workflows/Create.tsx`)
```typescript
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';

export default function CreateWorkflow({ projects }: { projects: Project[] }) {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    project_id: '',
    trigger: 'manual',
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('workflows.store'), {
      onSuccess: () => {
        // Inertia automatically redirects and updates the page
      },
    });
  };

  return (
    <form onSubmit={submit} className="space-y-6">
      <div>
        <label htmlFor="name">Workflow Name</label>
        <Input
          id="name"
          value={data.name}
          onChange={e => setData('name', e.target.value)}
          error={errors.name}
        />
      </div>

      <div>
        <label htmlFor="project">Project</label>
        <Select
          id="project"
          value={data.project_id}
          onChange={value => setData('project_id', value)}
          options={projects.map(p => ({ value: p.id, label: p.name }))}
          error={errors.project_id}
        />
      </div>

      <Button type="submit" disabled={processing}>
        {processing ? 'Creating...' : 'Create Workflow'}
      </Button>
    </form>
  );
}
```

#### WebSocket avec Laravel Echo

**Echo Bootstrap** (`resources/js/bootstrap.ts`)
```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: Echo;
  }
}

window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
});
```

**Real-time Workflow Updates Hook** (`resources/js/hooks/use-workflow.ts`)
```typescript
import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';

export function useWorkflow(workflowId: number) {
  const [status, setStatus] = useState<WorkflowStatus | null>(null);

  useEffect(() => {
    const channel = window.Echo.private(`workflow.${workflowId}`);

    channel
      .listen('.workflow.completed', (e: WorkflowCompletedEvent) => {
        setStatus('completed');
        // Reload Inertia page to get fresh data
        router.reload({ only: ['workflow'] });
      })
      .listen('.workflow.failed', (e: WorkflowFailedEvent) => {
        setStatus('failed');
        router.reload({ only: ['workflow'] });
      })
      .listen('.workflow.step-completed', (e: WorkflowStepEvent) => {
        // Update UI for step progress
        router.reload({ only: ['workflow.steps'] });
      });

    return () => {
      channel.stopListening('.workflow.completed');
      channel.stopListening('.workflow.failed');
      channel.stopListening('.workflow.step-completed');
    };
  }, [workflowId]);

  return { status };
}
```

#### React Query pour AI Engine

**API Client** (`resources/js/lib/api-client.ts`)
```typescript
import axios from 'axios';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_AI_ENGINE_URL,
  timeout: 30000,
});

export const aiEngineAPI = {
  analyzeCode: async (config: AnalyzeCodeRequest) => {
    const { data } = await apiClient.post('/api/v1/analyze', config);
    return data;
  },

  generateTests: async (config: GenerateTestsRequest) => {
    const { data } = await apiClient.post('/api/v1/tests/generate', config);
    return data;
  },

  routeLLM: async (prompt: string, options?: LLMOptions) => {
    const { data } = await apiClient.post('/api/v1/llm/route', {
      prompt,
      ...options,
    });
    return data;
  },
};
```

**React Query Hook** (`resources/js/hooks/use-code-analysis.ts`)
```typescript
import { useQuery, useMutation } from '@tanstack/react-query';
import { aiEngineAPI } from '@/lib/api-client';

export function useCodeAnalysis() {
  const analyzeMutation = useMutation({
    mutationFn: (config: AnalyzeCodeRequest) =>
      aiEngineAPI.analyzeCode(config),
    onSuccess: (data) => {
      // Update UI with analysis results
    },
  });

  return {
    analyze: analyzeMutation.mutate,
    isAnalyzing: analyzeMutation.isPending,
    results: analyzeMutation.data,
    error: analyzeMutation.error,
  };
}
```

---

## AI Engine

### Stack Technique

| Composant | Technologie | Version | Rôle |
|-----------|-------------|---------|------|
| **Framework** | FastAPI | 0.115+ | API framework Python |
| **Python** | Python | 3.12+ | Langage serveur |
| **LLM Router** | LiteLLM | Latest | Multi-provider LLM routing |
| **AST Parser** | tree-sitter | Latest | Code parsing multi-langage |
| **Embeddings** | sentence-transformers | Latest | Semantic code search |
| **Vector DB** | Qdrant | 1.7+ | Vector storage (Phase 3) |
| **MCP SDK** | MCP Python SDK | Latest | Model Context Protocol |
| **Testing** | pytest | 8.x | Unit/Integration tests |
| **Async** | asyncio | Built-in | Async operations |
| **Validation** | Pydantic | 2.x | Data validation |
| **HTTP Client** | httpx | 0.27+ | Async HTTP client |

### Architecture AI Engine

#### Structure des Dossiers

```
ai_engine/
├── app/
│   ├── main.py                 # FastAPI app entry
│   ├── config.py               # Configuration
│   ├── api/
│   │   ├── v1/
│   │   │   ├── endpoints/
│   │   │   │   ├── llm.py
│   │   │   │   ├── analyze.py
│   │   │   │   ├── tests.py
│   │   │   │   └── mcp.py
│   │   │   └── router.py
│   ├── core/
│   │   ├── llm_router.py      # LLM routing logic
│   │   ├── code_analyzer.py   # AST analysis
│   │   ├── test_generator.py  # Test generation
│   │   └── mcp_client.py      # MCP integration
│   ├── models/
│   │   ├── requests.py        # Pydantic request models
│   │   └── responses.py       # Pydantic response models
│   ├── services/
│   │   ├── openai_service.py
│   │   ├── mistral_service.py
│   │   ├── anthropic_service.py
│   │   └── ollama_service.py
│   └── utils/
│       ├── parsers/
│       │   ├── python_parser.py
│       │   ├── php_parser.py
│       │   ├── typescript_parser.py
│       │   └── go_parser.py
│       └── helpers.py
├── tests/
│   ├── unit/
│   └── integration/
├── requirements.txt
└── Dockerfile
```

#### FastAPI Application

**Main Application** (`ai_engine/app/main.py`)
```python
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api.v1.router import api_router
from app.config import settings

app = FastAPI(
    title="AgentOps AI Engine",
    description="AI-powered code analysis and LLM routing",
    version="2.0.0"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(api_router, prefix="/api/v1")

@app.get("/health")
async def health_check():
    return {"status": "healthy", "version": "2.0.0"}
```

#### LLM Router

**LLM Router Core** (`ai_engine/app/core/llm_router.py`)
```python
from typing import Optional
from litellm import completion, acompletion
from app.config import settings

class LLMRouter:
    """
    Intelligent LLM router with fallback strategy.
    Routes requests based on latency, cost, and availability.
    """

    PROVIDERS = {
        "gpt-4-turbo": {
            "model": "gpt-4-turbo-preview",
            "cost_per_1k": 0.01,
            "priority": 1,
        },
        "mistral-large": {
            "model": "mistral-large-latest",
            "cost_per_1k": 0.008,
            "priority": 2,
        },
        "claude-3.5-sonnet": {
            "model": "claude-3-5-sonnet-20240620",
            "cost_per_1k": 0.015,
            "priority": 3,
        },
        "ollama": {
            "model": "codellama:13b",
            "cost_per_1k": 0.0,  # Local
            "priority": 4,
        },
    }

    async def route(
        self,
        prompt: str,
        max_tokens: int = 2000,
        temperature: float = 0.7,
        prefer_local: bool = False,
    ) -> dict:
        """
        Route LLM request with automatic fallback.
        """
        providers = self._get_provider_order(prefer_local)

        for provider_key in providers:
            try:
                provider = self.PROVIDERS[provider_key]

                response = await acompletion(
                    model=provider["model"],
                    messages=[{"role": "user", "content": prompt}],
                    max_tokens=max_tokens,
                    temperature=temperature,
                    timeout=30,
                )

                return {
                    "provider": provider_key,
                    "model": provider["model"],
                    "content": response.choices[0].message.content,
                    "usage": response.usage.dict(),
                    "cost": self._calculate_cost(response.usage, provider),
                }

            except Exception as e:
                # Log and continue to next provider
                print(f"Provider {provider_key} failed: {e}")
                continue

        raise Exception("All LLM providers failed")

    def _get_provider_order(self, prefer_local: bool) -> list[str]:
        """Determine provider order based on preferences."""
        if prefer_local:
            # Prioritize Ollama if available
            return ["ollama", "mistral-large", "gpt-4-turbo", "claude-3.5-sonnet"]
        else:
            # Cost-optimized order
            return ["mistral-large", "gpt-4-turbo", "claude-3.5-sonnet", "ollama"]

    def _calculate_cost(self, usage, provider) -> float:
        """Calculate request cost."""
        total_tokens = usage.prompt_tokens + usage.completion_tokens
        return (total_tokens / 1000) * provider["cost_per_1k"]
```

#### Code Analyzer

**Code Analysis Core** (`ai_engine/app/core/code_analyzer.py`)
```python
from tree_sitter import Language, Parser
import tree_sitter_python as tspython
import tree_sitter_php as tsphp
import tree_sitter_typescript as tsts
from pathlib import Path

class CodeAnalyzer:
    """
    Multi-language code analyzer using tree-sitter.
    Generates Code Intelligence Map.
    """

    def __init__(self):
        self.parsers = {
            "python": self._init_parser(tspython),
            "php": self._init_parser(tsphp),
            "typescript": self._init_parser(tsts.language_typescript()),
            "javascript": self._init_parser(tsts.language_typescript()),
        }

    def _init_parser(self, language_module):
        parser = Parser()
        parser.set_language(Language(language_module.language()))
        return parser

    def analyze_file(self, file_path: str, language: str) -> dict:
        """
        Analyze a single file and extract structure.
        """
        parser = self.parsers.get(language)
        if not parser:
            raise ValueError(f"Unsupported language: {language}")

        with open(file_path, "rb") as f:
            code = f.read()

        tree = parser.parse(code)
        root = tree.root_node

        return {
            "file_path": file_path,
            "language": language,
            "classes": self._extract_classes(root, code),
            "functions": self._extract_functions(root, code),
            "imports": self._extract_imports(root, code),
            "dependencies": self._extract_dependencies(root, code),
        }

    def analyze_repository(self, repo_path: str) -> dict:
        """
        Analyze entire repository and generate Code Intelligence Map.
        """
        repo = Path(repo_path)
        files = self._discover_files(repo)

        results = {
            "repository": str(repo),
            "total_files": len(files),
            "files": [],
            "dependency_graph": {},
        }

        for file_info in files:
            analysis = self.analyze_file(file_info["path"], file_info["language"])
            results["files"].append(analysis)

        # Build dependency graph
        results["dependency_graph"] = self._build_dependency_graph(results["files"])

        return results

    def _extract_classes(self, root_node, code: bytes) -> list[dict]:
        """Extract class definitions."""
        classes = []
        query = "(class_definition name: (identifier) @class_name)"
        # Execute query and extract class info
        # Implementation depends on language specifics
        return classes

    def _extract_functions(self, root_node, code: bytes) -> list[dict]:
        """Extract function definitions."""
        functions = []
        # Similar to classes extraction
        return functions

    def _extract_imports(self, root_node, code: bytes) -> list[str]:
        """Extract import statements."""
        imports = []
        # Language-specific import extraction
        return imports

    def _extract_dependencies(self, root_node, code: bytes) -> list[str]:
        """Extract external dependencies."""
        deps = []
        # Parse package.json, composer.json, requirements.txt, etc.
        return deps

    def _discover_files(self, repo_path: Path) -> list[dict]:
        """Discover all code files in repository."""
        extensions = {
            ".py": "python",
            ".php": "php",
            ".ts": "typescript",
            ".tsx": "typescript",
            ".js": "javascript",
            ".jsx": "javascript",
        }

        files = []
        for ext, lang in extensions.items():
            for file_path in repo_path.rglob(f"*{ext}"):
                if "vendor" not in file_path.parts and "node_modules" not in file_path.parts:
                    files.append({"path": str(file_path), "language": lang})

        return files

    def _build_dependency_graph(self, file_analyses: list[dict]) -> dict:
        """Build dependency graph from file analyses."""
        graph = {}
        # Build graph structure
        return graph
```

#### Test Generator

**Test Generation Service** (`ai_engine/app/core/test_generator.py`)
```python
from app.core.llm_router import LLMRouter
from app.core.code_analyzer import CodeAnalyzer

class TestGenerator:
    """
    AI-powered test generation (TDD Copilot).
    """

    def __init__(self):
        self.llm = LLMRouter()
        self.analyzer = CodeAnalyzer()

    async def generate_tests(
        self,
        file_path: str,
        language: str,
        framework: str,
        coverage_target: int = 80,
    ) -> dict:
        """
        Generate comprehensive test suite for a file.
        """
        # Analyze code structure
        analysis = self.analyzer.analyze_file(file_path, language)

        # Generate tests for each function/method
        test_cases = []
        for func in analysis["functions"]:
            prompt = self._build_test_prompt(func, framework, language)

            response = await self.llm.route(
                prompt=prompt,
                max_tokens=1500,
                temperature=0.3,  # Lower temperature for code generation
            )

            test_cases.append({
                "function": func["name"],
                "test_code": response["content"],
                "provider": response["provider"],
            })

        return {
            "file_path": file_path,
            "framework": framework,
            "test_cases": test_cases,
            "estimated_coverage": self._estimate_coverage(analysis, test_cases),
        }

    def _build_test_prompt(self, func: dict, framework: str, language: str) -> str:
        """Build prompt for test generation."""
        return f"""
Generate comprehensive unit tests for the following {language} function using {framework}.

Function Signature:
{func['signature']}

Function Body:
{func['body']}

Requirements:
1. Test happy path and edge cases
2. Test error handling
3. Use proper assertions
4. Follow {framework} best practices
5. Aim for 100% code coverage

Generate ONLY the test code, no explanations.
"""

    def _estimate_coverage(self, analysis: dict, test_cases: list) -> int:
        """Estimate test coverage percentage."""
        # Simple heuristic: 1 test per function = ~80% coverage
        total_functions = len(analysis["functions"])
        tested_functions = len(test_cases)
        return min(int((tested_functions / total_functions) * 80), 100)
```

#### API Endpoints

**LLM Routing Endpoint** (`ai_engine/app/api/v1/endpoints/llm.py`)
```python
from fastapi import APIRouter, HTTPException
from app.models.requests import LLMRouteRequest
from app.models.responses import LLMRouteResponse
from app.core.llm_router import LLMRouter

router = APIRouter()
llm_router = LLMRouter()

@router.post("/route", response_model=LLMRouteResponse)
async def route_llm_request(request: LLMRouteRequest):
    """
    Route LLM request to optimal provider with fallback.
    """
    try:
        result = await llm_router.route(
            prompt=request.prompt,
            max_tokens=request.max_tokens,
            temperature=request.temperature,
            prefer_local=request.prefer_local,
        )

        return LLMRouteResponse(**result)

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
```

**Code Analysis Endpoint** (`ai_engine/app/api/v1/endpoints/analyze.py`)
```python
from fastapi import APIRouter, HTTPException
from app.models.requests import AnalyzeCodeRequest
from app.models.responses import CodeAnalysisResponse
from app.core.code_analyzer import CodeAnalyzer

router = APIRouter()
analyzer = CodeAnalyzer()

@router.post("/analyze", response_model=CodeAnalysisResponse)
async def analyze_code(request: AnalyzeCodeRequest):
    """
    Analyze code repository and generate Code Intelligence Map.
    """
    try:
        result = analyzer.analyze_repository(request.repository_path)

        return CodeAnalysisResponse(**result)

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
```

---

## Infrastructure & Déploiement

### Phase 1: DigitalOcean (MVP - Mois 1-3)

**Architecture Simplifiée**

```
┌─────────────────────────────────────────────────────────────┐
│              DigitalOcean Load Balancer                     │
│                  (SSL Termination)                          │
└────────────────────────┬────────────────────────────────────┘
                         │
         ┌───────────────┴───────────────┐
         │                               │
         ▼                               ▼
┌──────────────────────┐        ┌──────────────────────┐
│   Droplet 1          │        │   Droplet 2          │
│ Laravel+React+Inertia│        │ AI Engine (FastAPI)  │
│   (4GB RAM)          │        │   (4GB RAM)          │
│                      │        │                      │
│ • Nginx              │        │ • Gunicorn           │
│ • PHP 8.4-FPM        │        │ • Python 3.12        │
│ • Node.js (Vite SSR) │        │ • LiteLLM            │
│ • Laravel Reverb     │        │                      │
└────────┬─────────────┘        └──────────────────────┘
         │
         │ Managed Services
         ▼
┌──────────────────────────────────────────────────────────┐
│  PostgreSQL Cluster (Managed Database)                   │
│  • 2GB RAM                                               │
│  • Automated backups                                     │
│  • Extensions: pg_vector, uuid-ossp, pg_trgm            │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│  Redis Cluster (Managed Database)                        │
│  • 1GB RAM                                               │
│  • Cache + Queue + Pub/Sub                              │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│  Spaces (S3-compatible Object Storage)                   │
│  • Repository storage                                    │
│  • Build artifacts                                       │
└──────────────────────────────────────────────────────────┘
```

**Coût Mensuel Phase 1**: ~$200 USD
- Droplet Laravel (4GB): $48/mois
- Droplet AI Engine (4GB): $48/mois
- PostgreSQL Managed (2GB): $60/mois
- Redis Managed (1GB): $30/mois
- Spaces (250GB): $5/mois
- Load Balancer: $12/mois

**Configuration Nginx** (`/etc/nginx/sites-available/agentops`)
```nginx
server {
    listen 80;
    server_name app.agentops.io;
    root /var/www/agentops/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Laravel Reverb WebSocket
    location /reverb {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Supervisor Configuration** (`/etc/supervisor/conf.d/agentops.conf`)
```ini
[program:agentops-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/agentops/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/agentops/storage/logs/worker.log
stopwaitsecs=3600

[program:agentops-reverb]
command=php /var/www/agentops/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/agentops/storage/logs/reverb.log

[program:agentops-horizon]
process_name=%(program_name)s
command=php /var/www/agentops/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/agentops/storage/logs/horizon.log
stopwaitsecs=3600
```

### Phase 3: AWS EKS (Scale - Mois 9+)

**Architecture Kubernetes**

```
┌─────────────────────────────────────────────────────────────┐
│                  AWS Application Load Balancer              │
│                  (HTTPS + WebSocket Support)                │
└────────────────────────┬────────────────────────────────────┘
                         │
                         │ Ingress Controller
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                     AWS EKS Cluster                         │
│─────────────────────────────────────────────────────────────│
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │  Laravel+React Deployment                          │    │
│  │  • Replicas: 3-10 (HPA)                            │    │
│  │  • Instance: c6i.2xlarge (8 vCPU, 16GB RAM)       │    │
│  │  • PHP-FPM + Nginx sidecar                         │    │
│  │  • Inertia.js SSR                                  │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │  AI Engine Deployment                              │    │
│  │  • Replicas: 2-5 (HPA based on GPU utilization)   │    │
│  │  • Instance: g5.2xlarge (1x NVIDIA A10G GPU)      │    │
│  │  • FastAPI + Gunicorn                              │    │
│  │  • LiteLLM + Ollama                                │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │  Queue Worker Deployment                           │    │
│  │  • Replicas: 2-8 (based on queue size)            │    │
│  │  • Instance: c6i.xlarge (4 vCPU, 8GB RAM)         │    │
│  │  • Laravel Horizon                                 │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
│  ┌────────────────────────────────────────────────────┐    │
│  │  WebSocket Deployment (Laravel Reverb)             │    │
│  │  • Replicas: 2 (sticky sessions)                   │    │
│  │  • Instance: c6i.large (2 vCPU, 4GB RAM)          │    │
│  └────────────────────────────────────────────────────┘    │
│                                                             │
└────────────────────────┬────────────────────────────────────┘
                         │
                         │ AWS PrivateLink
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  Managed Services (AWS)                     │
│─────────────────────────────────────────────────────────────│
│                                                             │
│  • RDS PostgreSQL 16 (Multi-AZ)                            │
│    - Instance: db.r6g.xlarge (4 vCPU, 32GB RAM)           │
│    - Automated backups, read replicas                      │
│                                                             │
│  • ElastiCache Redis 7 (Cluster Mode)                     │
│    - Instance: cache.r6g.large (2 vCPU, 13GB RAM)         │
│    - 3 shards, 1 replica per shard                         │
│                                                             │
│  • Amazon MQ (RabbitMQ 3.13)                               │
│    - Instance: mq.m5.large (2 vCPU, 8GB RAM)              │
│    - Multi-AZ deployment                                   │
│                                                             │
│  • S3 (Object Storage)                                     │
│    - Repositories, artifacts, backups                      │
│                                                             │
│  • CloudWatch (Logs + Metrics)                             │
│  • X-Ray (Distributed Tracing)                             │
│  • GuardDuty (Security Monitoring)                         │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Kubernetes Manifests**

**Laravel Deployment** (`k8s/laravel-deployment.yaml`)
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: laravel-app
  namespace: agentops
spec:
  replicas: 3
  selector:
    matchLabels:
      app: laravel
  template:
    metadata:
      labels:
        app: laravel
    spec:
      containers:
      - name: php-fpm
        image: agentops/laravel:latest
        ports:
        - containerPort: 9000
        env:
        - name: DB_HOST
          valueFrom:
            secretKeyRef:
              name: db-credentials
              key: host
        - name: REDIS_HOST
          valueFrom:
            configMapKeyRef:
              name: redis-config
              key: host
        resources:
          requests:
            memory: "1Gi"
            cpu: "500m"
          limits:
            memory: "2Gi"
            cpu: "1000m"
        livenessProbe:
          httpGet:
            path: /health
            port: 9000
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /ready
            port: 9000
          initialDelaySeconds: 10
          periodSeconds: 5

      - name: nginx
        image: nginx:alpine
        ports:
        - containerPort: 80
        volumeMounts:
        - name: nginx-config
          mountPath: /etc/nginx/conf.d

      volumes:
      - name: nginx-config
        configMap:
          name: nginx-config
---
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: laravel-hpa
  namespace: agentops
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: laravel-app
  minReplicas: 3
  maxReplicas: 10
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Resource
    resource:
      name: memory
      target:
        type: Utilization
        averageUtilization: 80
```

**AI Engine Deployment** (`k8s/ai-engine-deployment.yaml`)
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: ai-engine
  namespace: agentops
spec:
  replicas: 2
  selector:
    matchLabels:
      app: ai-engine
  template:
    metadata:
      labels:
        app: ai-engine
    spec:
      nodeSelector:
        nvidia.com/gpu: "true"  # GPU nodes

      containers:
      - name: fastapi
        image: agentops/ai-engine:latest
        ports:
        - containerPort: 8000
        env:
        - name: OPENAI_API_KEY
          valueFrom:
            secretKeyRef:
              name: llm-credentials
              key: openai-key
        - name: ANTHROPIC_API_KEY
          valueFrom:
            secretKeyRef:
              name: llm-credentials
              key: anthropic-key
        resources:
          requests:
            memory: "8Gi"
            cpu: "2000m"
            nvidia.com/gpu: 1
          limits:
            memory: "16Gi"
            cpu: "4000m"
            nvidia.com/gpu: 1
        livenessProbe:
          httpGet:
            path: /health
            port: 8000
          initialDelaySeconds: 60
          periodSeconds: 10
---
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: ai-engine-hpa
  namespace: agentops
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: ai-engine
  minReplicas: 2
  maxReplicas: 5
  metrics:
  - type: Pods
    pods:
      metric:
        name: gpu_utilization
      target:
        type: AverageValue
        averageValue: "70"
```

**Ingress Configuration** (`k8s/ingress.yaml`)
```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: agentops-ingress
  namespace: agentops
  annotations:
    kubernetes.io/ingress.class: alb
    alb.ingress.kubernetes.io/scheme: internet-facing
    alb.ingress.kubernetes.io/target-type: ip
    alb.ingress.kubernetes.io/listen-ports: '[{"HTTP": 80}, {"HTTPS": 443}]'
    alb.ingress.kubernetes.io/ssl-redirect: "443"
    alb.ingress.kubernetes.io/certificate-arn: arn:aws:acm:...
    alb.ingress.kubernetes.io/backend-protocol: HTTP
    alb.ingress.kubernetes.io/healthcheck-path: /health
spec:
  rules:
  - host: app.agentops.io
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: laravel-service
            port:
              number: 80

      - path: /reverb
        pathType: Prefix
        backend:
          service:
            name: reverb-service
            port:
              number: 8080

  - host: ai.agentops.io
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: ai-engine-service
            port:
              number: 8000
```

**Coût Mensuel Phase 3**: ~$3,500 USD
- EKS Cluster: $72/mois
- EC2 Instances (Laravel 3x c6i.2xlarge): ~$600/mois
- EC2 Instances (AI Engine 2x g5.2xlarge avec GPU): ~$1,800/mois
- EC2 Instances (Workers 2x c6i.xlarge): ~$240/mois
- RDS PostgreSQL (db.r6g.xlarge Multi-AZ): ~$480/mois
- ElastiCache Redis (cache.r6g.large 6 nodes): ~$360/mois
- Amazon MQ RabbitMQ (mq.m5.large): ~$216/mois
- ALB: ~$30/mois
- S3 + Data Transfer: ~$100/mois
- CloudWatch + X-Ray: ~$50/mois

---

## Base de Données & Stockage

### Schéma PostgreSQL

**Users & Teams** (Multi-tenancy)
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    two_factor_secret TEXT,
    two_factor_confirmed_at TIMESTAMP,
    email_verified_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teams (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    owner_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    billing_email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE team_user (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(50) NOT NULL DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(team_id, user_id)
);

CREATE INDEX idx_team_user_team ON team_user(team_id);
CREATE INDEX idx_team_user_user ON team_user(user_id);
```

**Projects & Repositories**
```sql
CREATE TABLE projects (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    settings JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE repositories (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    provider VARCHAR(50) NOT NULL, -- 'github', 'gitlab', 'bitbucket'
    url TEXT NOT NULL,
    branch VARCHAR(255) NOT NULL DEFAULT 'main',
    last_sync_at TIMESTAMP,
    meta JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_repositories_project ON repositories(project_id);
```

**Workflows & Steps**
```sql
CREATE TYPE workflow_status AS ENUM ('pending', 'running', 'completed', 'failed', 'cancelled');
CREATE TYPE step_status AS ENUM ('pending', 'running', 'completed', 'failed', 'skipped');

CREATE TABLE workflows (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    trigger JSONB NOT NULL, -- { "type": "manual", "config": {...} }
    status workflow_status NOT NULL DEFAULT 'pending',
    meta JSONB DEFAULT '{}'::jsonb,
    error TEXT,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE workflow_steps (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    workflow_id UUID NOT NULL REFERENCES workflows(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL, -- 'code_analysis', 'test_generation', 'git_commit', etc.
    config JSONB NOT NULL,
    status step_status NOT NULL DEFAULT 'pending',
    result JSONB,
    error TEXT,
    order_index INT NOT NULL,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_workflows_project ON workflows(project_id);
CREATE INDEX idx_workflows_status ON workflows(status);
CREATE INDEX idx_workflow_steps_workflow ON workflow_steps(workflow_id);
```

**Integrations**
```sql
CREATE TYPE integration_type AS ENUM ('notion', 'github', 'gitlab', 'linear', 'slack');
CREATE TYPE integration_status AS ENUM ('active', 'inactive', 'expired', 'error');

CREATE TABLE integration_accounts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type integration_type NOT NULL,
    access_token TEXT NOT NULL, -- Encrypted
    refresh_token TEXT,         -- Encrypted
    meta JSONB DEFAULT '{}'::jsonb,
    status integration_status NOT NULL DEFAULT 'active',
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, type)
);

CREATE INDEX idx_integration_accounts_user ON integration_accounts(user_id);
CREATE INDEX idx_integration_accounts_type ON integration_accounts(type);
```

**Subscriptions (Stripe)**
```sql
CREATE TYPE subscription_tier AS ENUM ('free', 'starter', 'pro', 'enterprise');
CREATE TYPE subscription_status AS ENUM ('active', 'cancelled', 'expired', 'trialing');

CREATE TABLE subscriptions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    stripe_customer_id VARCHAR(255) UNIQUE,
    stripe_subscription_id VARCHAR(255) UNIQUE,
    tier subscription_tier NOT NULL DEFAULT 'free',
    status subscription_status NOT NULL DEFAULT 'active',
    trial_ends_at TIMESTAMP,
    ends_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subscription_items (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    subscription_id UUID NOT NULL REFERENCES subscriptions(id) ON DELETE CASCADE,
    stripe_id VARCHAR(255) NOT NULL UNIQUE,
    stripe_product VARCHAR(255) NOT NULL,
    stripe_price VARCHAR(255) NOT NULL,
    quantity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_stripe_customer ON subscriptions(stripe_customer_id);
```

**Row-Level Security (RLS)** pour Multi-tenancy
```sql
-- Enable RLS
ALTER TABLE projects ENABLE ROW LEVEL SECURITY;
ALTER TABLE repositories ENABLE ROW LEVEL SECURITY;
ALTER TABLE workflows ENABLE ROW LEVEL SECURITY;

-- Policy: Users can only access projects from their teams
CREATE POLICY projects_team_isolation ON projects
    USING (team_id IN (
        SELECT team_id FROM team_user WHERE user_id = current_setting('app.user_id')::uuid
    ));

CREATE POLICY repositories_team_isolation ON repositories
    USING (project_id IN (
        SELECT id FROM projects WHERE team_id IN (
            SELECT team_id FROM team_user WHERE user_id = current_setting('app.user_id')::uuid
        )
    ));

CREATE POLICY workflows_team_isolation ON workflows
    USING (project_id IN (
        SELECT id FROM projects WHERE team_id IN (
            SELECT team_id FROM team_user WHERE user_id = current_setting('app.user_id')::uuid
        )
    ));
```

### Extensions PostgreSQL

```sql
-- UUID generation
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Full-text search (Code Intelligence Map)
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Vector embeddings (Phase 3 - Semantic code search)
CREATE EXTENSION IF NOT EXISTS "vector";

-- Exemple table pour embeddings (Phase 3)
CREATE TABLE code_embeddings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    repository_id UUID NOT NULL REFERENCES repositories(id) ON DELETE CASCADE,
    file_path TEXT NOT NULL,
    chunk_index INT NOT NULL,
    content TEXT NOT NULL,
    embedding vector(1536), -- OpenAI ada-002 dimension
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(repository_id, file_path, chunk_index)
);

CREATE INDEX ON code_embeddings USING ivfflat (embedding vector_cosine_ops);
```

### Redis Structure

**Cache Keys**
```
cache:user:{user_id}:profile
cache:team:{team_id}:projects
cache:workflow:{workflow_id}:status
cache:integration:{user_id}:{type}:token
```

**Queue Keys**
```
queues:default
queues:workflows
queues:notifications
queues:code-analysis
```

**Pub/Sub Channels**
```
workflow:{workflow_id}:updates
team:{team_id}:notifications
user:{user_id}:events
```

---

## Sécurité

### Authentication & Authorization

**Laravel Sanctum** (SPA + API Token)
```php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    'expiration' => null, // SPA tokens don't expire

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'validate_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],
];
```

**Two-Factor Authentication** (Laravel Fortify)
```php
// config/fortify.php
'features' => [
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]),
],
```

**Policies** (Authorization)
```php
// app/Policies/ProjectPolicy.php
class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->teams->contains($project->team_id);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->teams()
            ->where('team_id', $project->team_id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->team->owner_id;
    }
}
```

### Encryption

**Database Encryption** (Sensitive Data)
```php
// app/Models/IntegrationAccount.php
protected function accessToken(): Attribute
{
    return Attribute::make(
        get: fn ($value) => decrypt($value),
        set: fn ($value) => encrypt($value),
    );
}

protected function refreshToken(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value ? decrypt($value) : null,
        set: fn ($value) => $value ? encrypt($value) : null,
    );
}
```

**Encryption Key Rotation**
```bash
# .env
APP_KEY=base64:...
APP_PREVIOUS_KEYS=base64:old_key_1,base64:old_key_2

# config/app.php
'previous_keys' => explode(',', env('APP_PREVIOUS_KEYS', '')),
```

### Rate Limiting

**API Rate Limiting** (`app/Http/Kernel.php`)
```php
protected $middlewareGroups = [
    'api' => [
        'throttle:60,1', // 60 requests per minute per IP
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];

// config/sanctum.php
'limiters' => [
    'workflows' => '100,1', // 100 workflow executions per minute
    'ai-engine' => '20,1',  // 20 AI requests per minute
],
```

**Custom Rate Limiter** (RateLimiter service)
```php
// app/Providers/AppServiceProvider.php
RateLimiter::for('ai-engine', function (Request $request) {
    $tier = $request->user()->subscription->tier;

    return match ($tier) {
        'free' => Limit::perMinute(5),
        'starter' => Limit::perMinute(20),
        'pro' => Limit::perMinute(100),
        'enterprise' => Limit::none(),
    };
});
```

### Security Headers

**Middleware** (`app/Http/Middleware/SecurityHeaders.php`)
```php
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;"
        );
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        return $response;
    }
}
```

### Secrets Management

**Phase 1**: Utilisation de `.env` avec encryption
**Phase 3**: AWS Secrets Manager

```php
// app/Services/SecretsManager.php
class SecretsManager
{
    public function getSecret(string $key): string
    {
        if (app()->environment('production')) {
            // AWS Secrets Manager
            $client = new SecretsManagerClient([
                'version' => 'latest',
                'region' => config('aws.region'),
            ]);

            $result = $client->getSecretValue(['SecretId' => $key]);
            return $result['SecretString'];
        }

        // Development: use .env
        return config($key);
    }
}
```

### Input Validation

**Form Requests** (Laravel)
```php
// app/Http/Requests/WorkflowRequest.php
class WorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Workflow::class, $this->project]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'trigger.type' => ['required', 'in:manual,webhook,schedule'],
            'trigger.config' => ['required', 'array'],
        ];
    }
}
```

**Pydantic Validation** (AI Engine)
```python
# ai_engine/app/models/requests.py
from pydantic import BaseModel, Field, validator

class AnalyzeCodeRequest(BaseModel):
    repository_path: str = Field(..., min_length=1, max_length=500)
    language: str = Field(..., regex=r'^(python|php|typescript|javascript|go|rust)$')
    include_dependencies: bool = True

    @validator('repository_path')
    def validate_path(cls, v):
        # Prevent path traversal
        if '..' in v or v.startswith('/'):
            raise ValueError('Invalid repository path')
        return v
```

---

## Performance & Scalabilité

### Caching Strategy

**Laravel Cache** (Redis)
```php
// Cache user profile
Cache::remember("user:{$userId}:profile", 3600, function () use ($userId) {
    return User::with(['teams', 'integrations'])->find($userId);
});

// Cache workflow status
Cache::tags(['workflows', "workflow:{$workflowId}"])
    ->put("workflow:{$workflowId}:status", $status, 300);

// Invalidate cache on update
Cache::tags(['workflows', "workflow:{$workflowId}"])->flush();
```

**HTTP Caching** (CDN)
```php
// app/Http/Middleware/SetCacheHeaders.php
class SetCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/public/*')) {
            $response->header('Cache-Control', 'public, max-age=3600');
        }

        return $response;
    }
}
```

### Database Optimization

**Indexing Strategy**
```sql
-- Composite indexes pour queries fréquentes
CREATE INDEX idx_workflows_project_status ON workflows(project_id, status);
CREATE INDEX idx_workflow_steps_workflow_order ON workflow_steps(workflow_id, order_index);
CREATE INDEX idx_team_user_team_role ON team_user(team_id, role);

-- Partial indexes
CREATE INDEX idx_active_workflows ON workflows(id) WHERE status = 'running';
```

**Query Optimization** (N+1 Prevention)
```php
// Eager loading
$projects = Project::with(['repositories', 'workflows.steps'])
    ->where('team_id', $teamId)
    ->get();

// Lazy eager loading
$projects->load('workflows');

// Count without loading
$projectsWithWorkflowCount = Project::withCount('workflows')->get();
```

**Database Connection Pooling** (PgBouncer en Phase 3)
```ini
; pgbouncer.ini
[databases]
agentops = host=rds-endpoint.amazonaws.com port=5432 dbname=agentops

[pgbouncer]
listen_addr = 0.0.0.0
listen_port = 6432
auth_type = md5
pool_mode = transaction
max_client_conn = 1000
default_pool_size = 25
```

### Queue Optimization

**Job Prioritization** (Laravel Horizon)
```php
// config/horizon.php
'defaults' => [
    'workflows' => [
        'connection' => 'redis',
        'queue' => ['high', 'default', 'low'],
        'balance' => 'auto',
        'minProcesses' => 2,
        'maxProcesses' => 10,
        'balanceMaxShift' => 1,
        'balanceCooldown' => 3,
        'tries' => 3,
    ],
];
```

**Job Batching** (Parallel Processing)
```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

$batch = Bus::batch([
    new AnalyzeCodeJob($file1),
    new AnalyzeCodeJob($file2),
    new AnalyzeCodeJob($file3),
])->then(function (Batch $batch) {
    // All jobs completed successfully
    event(new WorkflowCompleted($batch->options['workflow_id']));
})->catch(function (Batch $batch, Throwable $e) {
    // First failure
    Log::error('Batch failed', ['batch_id' => $batch->id, 'error' => $e]);
})->finally(function (Batch $batch) {
    // Cleanup
})->dispatch();
```

### Frontend Performance

**Code Splitting** (Vite)
```typescript
// Lazy load pages
const Dashboard = lazy(() => import('@/pages/Dashboard'));
const WorkflowShow = lazy(() => import('@/pages/Workflows/Show'));

// Route-based splitting
<Routes>
  <Route path="/dashboard" element={
    <Suspense fallback={<Loading />}>
      <Dashboard />
    </Suspense>
  } />
</Routes>
```

**Asset Optimization** (`vite.config.ts`)
```typescript
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom', '@inertiajs/react'],
          ui: ['@radix-ui/react-dialog', '@radix-ui/react-dropdown-menu'],
          editor: ['monaco-editor'],
        },
      },
    },
    chunkSizeWarningLimit: 1000,
  },
  server: {
    hmr: {
      host: 'localhost',
    },
  },
});
```

**React Query Optimization**
```typescript
// Prefetch data
queryClient.prefetchQuery({
  queryKey: ['workflows', projectId],
  queryFn: () => fetchWorkflows(projectId),
});

// Stale-while-revalidate
const { data } = useQuery({
  queryKey: ['workflow', workflowId],
  queryFn: () => fetchWorkflow(workflowId),
  staleTime: 1000 * 60, // 1 minute
  cacheTime: 1000 * 60 * 5, // 5 minutes
});
```

---

## Monitoring & Observabilité

### Metrics (Prometheus + Grafana)

**Laravel Metrics** (prometheus-exporter)
```php
// app/Providers/MetricsServiceProvider.php
class MetricsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CollectorRegistry::class, function () {
            return new CollectorRegistry(new Redis());
        });
    }

    public function boot(): void
    {
        $registry = app(CollectorRegistry::class);

        // Counter: workflow executions
        $workflowCounter = $registry->getOrRegisterCounter(
            'agentops',
            'workflows_total',
            'Total workflow executions',
            ['status', 'project']
        );

        // Histogram: workflow duration
        $workflowDuration = $registry->getOrRegisterHistogram(
            'agentops',
            'workflow_duration_seconds',
            'Workflow execution duration',
            ['project'],
            [0.1, 0.5, 1, 5, 10, 30, 60, 120, 300]
        );

        // Gauge: active workflows
        $activeWorkflows = $registry->getOrRegisterGauge(
            'agentops',
            'workflows_active',
            'Currently active workflows'
        );
    }
}
```

**AI Engine Metrics** (Prometheus Python Client)
```python
# ai_engine/app/metrics.py
from prometheus_client import Counter, Histogram, Gauge

llm_requests = Counter(
    'ai_engine_llm_requests_total',
    'Total LLM requests',
    ['provider', 'status']
)

llm_latency = Histogram(
    'ai_engine_llm_latency_seconds',
    'LLM request latency',
    ['provider'],
    buckets=(0.1, 0.5, 1.0, 2.0, 5.0, 10.0, 30.0)
)

code_analysis_duration = Histogram(
    'ai_engine_code_analysis_duration_seconds',
    'Code analysis duration',
    ['language'],
    buckets=(0.5, 1.0, 5.0, 10.0, 30.0, 60.0, 120.0)
)
```

**Grafana Dashboard Configuration**
```yaml
# grafana/dashboards/agentops.json
{
  "dashboard": {
    "title": "AgentOps Platform",
    "panels": [
      {
        "title": "Workflow Execution Rate",
        "targets": [
          {
            "expr": "rate(agentops_workflows_total[5m])",
            "legendFormat": "{{status}}"
          }
        ]
      },
      {
        "title": "LLM Request Latency (p95)",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(ai_engine_llm_latency_seconds_bucket[5m]))",
            "legendFormat": "{{provider}}"
          }
        ]
      },
      {
        "title": "Active Workflows",
        "targets": [
          {
            "expr": "agentops_workflows_active"
          }
        ]
      }
    ]
  }
}
```

### Logging (Structured Logging)

**Laravel Logging** (`config/logging.php`)
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
        'formatter' => \Monolog\Formatter\JsonFormatter::class,
    ],

    'sentry' => [
        'driver' => 'sentry',
        'level' => 'error',
    ],
],
```

**Structured Logging Example**
```php
Log::channel('workflows')->info('Workflow executed', [
    'workflow_id' => $workflow->id,
    'project_id' => $workflow->project_id,
    'duration' => $duration,
    'steps_count' => $workflow->steps->count(),
    'user_id' => auth()->id(),
]);
```

**AI Engine Logging** (Python structlog)
```python
import structlog

logger = structlog.get_logger()

logger.info(
    "llm_request",
    provider="gpt-4-turbo",
    prompt_tokens=150,
    completion_tokens=300,
    latency_ms=1200,
    cost_usd=0.015,
)
```

### Error Tracking (Sentry)

**Laravel Sentry Integration**
```php
// app/Exceptions/Handler.php
public function register(): void
{
    $this->reportable(function (Throwable $e) {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }
    });
}
```

**Frontend Sentry** (`resources/js/app.tsx`)
```typescript
import * as Sentry from '@sentry/react';

Sentry.init({
  dsn: import.meta.env.VITE_SENTRY_DSN,
  environment: import.meta.env.VITE_APP_ENV,
  integrations: [
    new Sentry.BrowserTracing(),
    new Sentry.Replay(),
  ],
  tracesSampleRate: 0.1,
  replaysSessionSampleRate: 0.1,
  replaysOnErrorSampleRate: 1.0,
});
```

### Distributed Tracing (Jaeger / AWS X-Ray)

**OpenTelemetry Laravel** (Phase 3)
```php
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\TracerProvider;

$tracer = TracerProvider::getTracer('agentops');

$span = $tracer->spanBuilder('workflow.execute')
    ->setSpanKind(SpanKind::KIND_SERVER)
    ->startSpan();

try {
    $orchestrator->execute($workflow);
    $span->setStatus(StatusCode::STATUS_OK);
} catch (\Exception $e) {
    $span->recordException($e);
    $span->setStatus(StatusCode::STATUS_ERROR);
} finally {
    $span->end();
}
```

### Health Checks

**Laravel Health Check** (`routes/api.php`)
```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'checks' => [
            'database' => DB::connection()->getPdo() ? 'ok' : 'fail',
            'redis' => Redis::ping() ? 'ok' : 'fail',
            'queue' => Queue::size() < 1000 ? 'ok' : 'degraded',
        ],
    ]);
});
```

**AI Engine Health Check**
```python
@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "checks": {
            "llm_providers": await check_llm_providers(),
            "parser": "ok",
        }
    }

async def check_llm_providers():
    providers = {}
    for name in ["gpt-4-turbo", "mistral-large", "claude-3.5-sonnet"]:
        try:
            # Test ping
            providers[name] = "ok"
        except:
            providers[name] = "fail"
    return providers
```

---

## CI/CD

### GitHub Actions Workflow

**Laravel CI/CD** (`.github/workflows/laravel.yml`)
```yaml
name: Laravel CI/CD

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_DB: agentops_test
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: postgres
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
        ports:
          - 5432:5432

      redis:
        image: redis:7-alpine
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
        ports:
          - 6379:6379

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: pdo, pdo_pgsql, redis
          coverage: xdebug

      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-progress

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate app key
        run: php artisan key:generate

      - name: Run migrations
        run: php artisan migrate --force
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_PORT: 5432
          DB_DATABASE: agentops_test
          DB_USERNAME: postgres
          DB_PASSWORD: postgres

      - name: Run PHPUnit tests
        run: php artisan test --coverage --min=80
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_PORT: 5432
          DB_DATABASE: agentops_test
          DB_USERNAME: postgres
          DB_PASSWORD: postgres

      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse --level=max app

      - name: Run Pint
        run: ./vendor/bin/pint --test

  build:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'

    steps:
      - uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install npm dependencies
        run: npm ci

      - name: Build assets
        run: npm run build

      - name: Build Docker image
        run: |
          docker build -t agentops/laravel:${{ github.sha }} .
          docker tag agentops/laravel:${{ github.sha }} agentops/laravel:latest

      - name: Push to registry
        run: |
          echo "${{ secrets.DOCKER_PASSWORD }}" | docker login -u "${{ secrets.DOCKER_USERNAME }}" --password-stdin
          docker push agentops/laravel:${{ github.sha }}
          docker push agentops/laravel:latest

  deploy:
    needs: build
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'

    steps:
      - name: Deploy to DigitalOcean
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.DROPLET_HOST }}
          username: ${{ secrets.DROPLET_USER }}
          key: ${{ secrets.DROPLET_SSH_KEY }}
          script: |
            cd /var/www/agentops
            git pull origin main
            composer install --no-dev --optimize-autoloader
            npm ci
            npm run build
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            sudo supervisorctl restart agentops-queue:*
            sudo supervisorctl restart agentops-horizon
            sudo supervisorctl restart agentops-reverb
```

**AI Engine CI/CD** (`.github/workflows/ai-engine.yml`)
```yaml
name: AI Engine CI/CD

on:
  push:
    branches: [main, develop]
    paths:
      - 'ai_engine/**'
  pull_request:
    branches: [main, develop]
    paths:
      - 'ai_engine/**'

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup Python
        uses: actions/setup-python@v5
        with:
          python-version: '3.12'

      - name: Install dependencies
        run: |
          cd ai_engine
          pip install -r requirements.txt
          pip install pytest pytest-cov pytest-asyncio

      - name: Run pytest
        run: |
          cd ai_engine
          pytest tests/ --cov=app --cov-report=xml --cov-report=term-missing

      - name: Run mypy
        run: |
          cd ai_engine
          mypy app/

  build:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'

    steps:
      - uses: actions/checkout@v4

      - name: Build Docker image
        run: |
          cd ai_engine
          docker build -t agentops/ai-engine:${{ github.sha }} .
          docker tag agentops/ai-engine:${{ github.sha }} agentops/ai-engine:latest

      - name: Push to registry
        run: |
          echo "${{ secrets.DOCKER_PASSWORD }}" | docker login -u "${{ secrets.DOCKER_USERNAME }}" --password-stdin
          docker push agentops/ai-engine:${{ github.sha }}
          docker push agentops/ai-engine:latest

  deploy:
    needs: build
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'

    steps:
      - name: Deploy to DigitalOcean
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.AI_ENGINE_HOST }}
          username: ${{ secrets.DROPLET_USER }}
          key: ${{ secrets.DROPLET_SSH_KEY }}
          script: |
            docker pull agentops/ai-engine:latest
            docker stop ai-engine || true
            docker rm ai-engine || true
            docker run -d \
              --name ai-engine \
              -p 8000:8000 \
              --env-file /etc/agentops/ai-engine.env \
              --restart unless-stopped \
              agentops/ai-engine:latest
```

### Dockerfile

**Laravel Dockerfile**
```dockerfile
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    nodejs \
    npm \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN npm ci && npm run build

# Copy configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www

EXPOSE 80 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

**AI Engine Dockerfile**
```dockerfile
FROM python:3.12-slim

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    git \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy requirements
COPY requirements.txt .

# Install Python dependencies
RUN pip install --no-cache-dir -r requirements.txt

# Copy application
COPY . .

# Expose port
EXPOSE 8000

# Run with Gunicorn
CMD ["gunicorn", "app.main:app", "--workers", "4", "--worker-class", "uvicorn.workers.UvicornWorker", "--bind", "0.0.0.0:8000"]
```

---

## Stratégie de Déploiement

### Blue-Green Deployment (Phase 3)

```yaml
# k8s/blue-green-deployment.yaml
apiVersion: v1
kind: Service
metadata:
  name: laravel-service
spec:
  selector:
    app: laravel
    version: blue  # Switch to 'green' for deployment
  ports:
  - port: 80
    targetPort: 80
---
# Blue deployment
apiVersion: apps/v1
kind: Deployment
metadata:
  name: laravel-blue
spec:
  replicas: 3
  selector:
    matchLabels:
      app: laravel
      version: blue
  template:
    metadata:
      labels:
        app: laravel
        version: blue
    spec:
      containers:
      - name: laravel
        image: agentops/laravel:v1.0.0
---
# Green deployment
apiVersion: apps/v1
kind: Deployment
metadata:
  name: laravel-green
spec:
  replicas: 3
  selector:
    matchLabels:
      app: laravel
      version: green
  template:
    metadata:
      labels:
        app: laravel
        version: green
    spec:
      containers:
      - name: laravel
        image: agentops/laravel:v1.1.0
```

**Deployment Script**
```bash
#!/bin/bash
# deploy-blue-green.sh

CURRENT=$(kubectl get service laravel-service -o jsonpath='{.spec.selector.version}')
NEW=$([[ "$CURRENT" == "blue" ]] && echo "green" || echo "blue")

echo "Current: $CURRENT, Deploying: $NEW"

# Deploy new version
kubectl apply -f k8s/laravel-$NEW-deployment.yaml
kubectl rollout status deployment/laravel-$NEW

# Run smoke tests
./smoke-tests.sh http://laravel-$NEW-service

# Switch traffic
kubectl patch service laravel-service -p "{\"spec\":{\"selector\":{\"version\":\"$NEW\"}}}"

echo "Deployment complete. Traffic switched to $NEW"
```

### Database Migration Strategy

**Zero-Downtime Migrations**
```php
// database/migrations/2024_01_01_add_column_backwards_compatible.php
public function up(): void
{
    // Step 1: Add nullable column
    Schema::table('workflows', function (Blueprint $table) {
        $table->string('new_column')->nullable();
    });
}

// Later migration after deployment
public function up(): void
{
    // Step 2: Backfill data
    DB::table('workflows')->whereNull('new_column')->update([
        'new_column' => DB::raw('COALESCE(old_column, "default")'),
    ]);

    // Step 3: Make non-nullable
    Schema::table('workflows', function (Blueprint $table) {
        $table->string('new_column')->nullable(false)->change();
    });

    // Step 4: Drop old column (separate deployment)
    // Schema::table('workflows', function (Blueprint $table) {
    //     $table->dropColumn('old_column');
    // });
}
```

### Rollback Strategy

```bash
#!/bin/bash
# rollback.sh

# Kubernetes rollback
kubectl rollout undo deployment/laravel-app
kubectl rollout undo deployment/ai-engine

# Database rollback (if needed)
php artisan migrate:rollback --step=1

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## Conclusion

Cette architecture technique v2 à **2 applications** (Laravel+React+Inertia.js + AI Engine FastAPI) offre:

### Avantages

1. **Simplicité Opérationnelle**
   - Moins de services à gérer en Phase 1
   - Infrastructure réduite (~$200/mois vs $400+)
   - Déploiement plus simple

2. **Performance**
   - Latence réduite (pas de round-trip backend ↔ frontend)
   - SSR natif avec Inertia.js
   - Meilleure expérience utilisateur

3. **Développement Rapide**
   - Stack unifiée (Laravel + React)
   - Pas besoin d'API REST entre backend et frontend
   - Inertia.js handle la communication

4. **Scalabilité**
   - AI Engine indépendant (scaling GPU en Phase 3)
   - Horizontal scaling via Kubernetes (Phase 3)
   - Caching multi-niveau

5. **Sécurité**
   - Multi-tenancy avec RLS PostgreSQL
   - 2FA natif
   - Encryption des tokens

### Trade-offs vs Architecture 3 Apps

| Aspect | 2 Apps (v2) | 3 Apps (v1) |
|--------|-------------|-------------|
| **Complexité** | ✅ Faible | ❌ Élevée |
| **Coût Phase 1** | ✅ $200/mois | ❌ $400/mois |
| **Latence** | ✅ Réduite | ❌ Network hops |
| **SSR** | ✅ Natif | ❌ Complexe |
| **Découplage** | ⚠️ Moyen | ✅ Total |
| **Scaling Indépendant** | ⚠️ AI Engine seulement | ✅ Chaque app |
| **API REST** | ❌ Non nécessaire | ✅ Flexible |

### Recommandations

**Phase 1 (MVP)**: Architecture 2 apps sur DigitalOcean
- Focus sur le time-to-market
- Coûts réduits
- Stack simplifiée

**Phase 3 (Scale)**: Migration vers AWS EKS
- Kubernetes pour orchestration
- Scaling GPU pour AI Engine
- Multi-région si nécessaire

**Evolution Future**: Si besoin de découplage total (mobile apps, API publique), migration vers architecture 3 apps possible avec minimal refactoring grâce à Inertia.js (déjà structuré en API-like patterns).

---

**Version**: 2.0
**Date**: Janvier 2025
**Auteur**: Architecture Team
**Status**: ✅ Ready for Implementation
