# 🧹 Sprint 2 Cleanup - Todo List

**Created:** 2025-10-26
**Sprint:** Sprint 2 Completion (Post-Sprint Work)
**Effort:** 5.5 jours critiques + 2 jours optionnels = 7.5 jours total
**Priority:** ⚠️ **URGENT** - Bloque Sprint 3

---

## 🎯 Objectif

Compléter les tâches critiques manquantes du Sprint 2 pour débloquer Sprint 3.

**Sans ces tâches, Sprint 3 ne peut PAS démarrer.**

---

## ⚠️ Tâches Critiques (OBLIGATOIRES)

### 1. S2.11: AST Parser Integration (3j - P0) ⚠️ BLOQUANT

**Pourquoi critique:** Sans AST Parser, impossible d'analyser intelligemment le code des repositories.

**Localisation:** 📦 MCP Manager (Laravel)

**Dépendances:** Aucune - peut commencer immédiatement

#### Sous-tâches:

- [ ] **Jour 1: Setup & Recherche (8h)**
  - [ ] Installer `nikic/php-parser`: `composer require nikic/php-parser`
  - [ ] Rechercher packages pour JavaScript/Python parsing (optional)
  - [ ] Créer structure de base:
    ```bash
    php artisan make:class Services/Code/ASTParserService
    php artisan make:class Services/Code/Parsers/PHPParser
    php artisan make:class Services/Code/Parsers/ParserInterface
    ```
  - [ ] Créer enum `app/Enums/CodeLanguage.php` (PHP, JavaScript, Python, TypeScript)
  - [ ] Créer DTO `app/DataTransferObjects/CodeAnalysis/ASTResult.php`

- [ ] **Jour 2: Implementation Core (8h)**
  - [ ] Implémenter `ASTParserService::parseFile(string $path): ASTResult`
  - [ ] Implémenter `PHPParser::parse(string $content): array`
  - [ ] Implémenter `PHPParser::extractFunctions(array $ast): array`
  - [ ] Implémenter `PHPParser::extractClasses(array $ast): array`
  - [ ] Implémenter `PHPParser::extractDependencies(array $ast): array`
  - [ ] Implémenter `PHPParser::extractNamespaces(array $ast): array`
  - [ ] Support PHP files (minimum viable)

- [ ] **Jour 3: Tests & Multi-language (8h)**
  - [ ] Créer fixtures de test:
    ```bash
    mkdir -p tests/Fixtures/code-samples
    touch tests/Fixtures/code-samples/sample.php
    touch tests/Fixtures/code-samples/sample-class.php
    touch tests/Fixtures/code-samples/sample-with-dependencies.php
    ```
  - [ ] Créer `tests/Unit/Services/Code/ASTParserServiceTest.php`
  - [ ] Créer `tests/Unit/Services/Code/Parsers/PHPParserTest.php`
  - [ ] Tests parsing PHP files (classes, functions, dependencies)
  - [ ] Tests edge cases (malformed code, empty files, large files)
  - [ ] Optional: JavaScript/Python parsing (defer to Sprint 3 if complex)
  - [ ] Intégration avec `AnalyzeRepositoryAction`

**Fichiers à créer:**
```
app/
├── Services/Code/
│   ├── ASTParserService.php
│   └── Parsers/
│       ├── ParserInterface.php
│       └── PHPParser.php
├── Enums/CodeLanguage.php
└── DataTransferObjects/CodeAnalysis/
    └── ASTResult.php

tests/
├── Unit/Services/Code/
│   ├── ASTParserServiceTest.php
│   └── Parsers/PHPParserTest.php
└── Fixtures/code-samples/
    ├── sample.php
    ├── sample-class.php
    └── sample-with-dependencies.php
```

**Dépendances composer:**
```bash
composer require nikic/php-parser
```

**Code Examples:**

```php
// app/Services/Code/Parsers/ParserInterface.php
<?php

namespace App\Services\Code\Parsers;

use App\DataTransferObjects\CodeAnalysis\ASTResult;

interface ParserInterface
{
    public function parse(string $content): ASTResult;
    public function supports(string $extension): bool;
}
```

```php
// app/DataTransferObjects/CodeAnalysis/ASTResult.php
<?php

namespace App\DataTransferObjects\CodeAnalysis;

class ASTResult
{
    public function __construct(
        public readonly array $classes,
        public readonly array $functions,
        public readonly array $dependencies,
        public readonly array $namespaces,
        public readonly array $metadata,
    ) {}

    public function toArray(): array
    {
        return [
            'classes' => $this->classes,
            'functions' => $this->functions,
            'dependencies' => $this->dependencies,
            'namespaces' => $this->namespaces,
            'metadata' => $this->metadata,
        ];
    }
}
```

```php
// app/Services/Code/Parsers/PHPParser.php
<?php

namespace App\Services\Code\Parsers;

use App\DataTransferObjects\CodeAnalysis\ASTResult;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\ParserFactory;

class PHPParser implements ParserInterface
{
    private \PhpParser\Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    public function parse(string $content): ASTResult
    {
        try {
            $ast = $this->parser->parse($content);

            $traverser = new NodeTraverser();
            $visitor = new class implements NodeVisitor {
                public array $classes = [];
                public array $functions = [];
                public array $dependencies = [];
                public array $namespaces = [];

                // Implement visitor pattern to extract data
            };

            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);

            return new ASTResult(
                classes: $visitor->classes,
                functions: $visitor->functions,
                dependencies: $visitor->dependencies,
                namespaces: $visitor->namespaces,
                metadata: [
                    'lines' => substr_count($content, "\n") + 1,
                    'size_bytes' => strlen($content),
                ]
            );
        } catch (Error $error) {
            throw new \RuntimeException("Parse error: {$error->getMessage()}");
        }
    }

    public function supports(string $extension): bool
    {
        return $extension === 'php';
    }
}
```

**Critères d'acceptation:**
- [ ] Parse PHP files et extrait structure AST complète
- [ ] Extrait fonctions, classes, dépendances, namespaces
- [ ] Tests unitaires passent (>80% coverage)
- [ ] Gestion erreurs (malformed code, large files)
- [ ] Intégré dans AnalyzeRepositoryAction
- [ ] Documentation API (PHPDoc)

---

### 2. S2.12: Prompt Engineering Analyse Code (2.5j - P0) ⚠️ BLOQUANT

**Pourquoi critique:** Sans prompts optimisés, LLM ne peut pas produire analyses pertinentes.

**Localisation:** 📦 MCP Manager (Laravel)

**Dépend de:** S2.11 (AST Parser) - doit avoir AST data structure

#### Sous-tâches:

- [ ] **Jour 1: Design Prompts (8h)**
  - [ ] Créer structure de prompts:
    ```bash
    php artisan make:class Services/LLM/Prompts/BasePrompt
    php artisan make:class Services/LLM/Prompts/AnalyzeCodePrompt
    php artisan make:class Services/LLM/Prompts/PromptBuilder
    mkdir -p storage/prompts
    ```
  - [ ] Designer template prompt v1 pour analyse code
  - [ ] Créer `storage/prompts/analyze_code_v1.txt`
  - [ ] Créer `storage/prompts/analyze_code_v1_system.txt`
  - [ ] Define output format (structured JSON schema)
  - [ ] Define context variables (repo_name, language, framework, AST data)
  - [ ] Token optimization strategy (<4K tokens input)

- [ ] **Jour 2: Implementation & Testing (8h)**
  - [ ] Implémenter `BasePrompt::render(array $context): string`
  - [ ] Implémenter `AnalyzeCodePrompt::buildContext(ASTResult $ast, array $repoData): array`
  - [ ] Context injection (repo info, language, framework detected)
  - [ ] Input injection (AST structure, file list, dependencies)
  - [ ] Token counting et truncation logic
  - [ ] JSON output schema validation
  - [ ] Créer `tests/Unit/Services/LLM/Prompts/AnalyzeCodePromptTest.php`
  - [ ] Créer `tests/Unit/Services/LLM/Prompts/PromptBuilderTest.php`

- [ ] **Demi-journée: Real LLM Testing (4h)**
  - [ ] Test avec OpenAI GPT-4 (budget API ~$5-10)
  - [ ] Test avec Mistral Large
  - [ ] Valider output quality (JSON valid, insights pertinents)
  - [ ] Ajuster prompt si nécessaire (iteration)
  - [ ] Documenter prompt versioning strategy
  - [ ] Créer `storage/prompts/CHANGELOG.md` pour tracking versions

**Fichiers à créer:**
```
app/Services/LLM/Prompts/
├── BasePrompt.php
├── AnalyzeCodePrompt.php
└── PromptBuilder.php

storage/prompts/
├── analyze_code_v1.txt
├── analyze_code_v1_system.txt
└── CHANGELOG.md

tests/Unit/Services/LLM/Prompts/
├── AnalyzeCodePromptTest.php
└── PromptBuilderTest.php
```

**Template Prompt Example:**

```txt
// storage/prompts/analyze_code_v1_system.txt
You are a senior software architect with 15+ years of experience analyzing codebases.

Your specialty is:
- Identifying architecture patterns (MVC, MVVM, Clean Architecture, etc.)
- Code quality assessment (SOLID, DRY, KISS principles)
- Detecting anti-patterns and code smells
- Providing actionable recommendations

You MUST output valid JSON only, no markdown, no explanations outside JSON.
```

```txt
// storage/prompts/analyze_code_v1.txt
Repository: {{repo_name}}
Language: {{language}}
Framework: {{framework}}
Total Files: {{file_count}}
Total Lines: {{total_lines}}

=== File Structure ===
{{file_tree}}

=== AST Analysis Summary ===
Classes Found: {{class_count}}
{{class_list}}

Functions Found: {{function_count}}
{{function_list}}

Dependencies:
{{dependencies}}

Namespaces:
{{namespaces}}

=== Analysis Task ===

Analyze this codebase and provide:

1. **Architecture Pattern**: Identify the main architecture pattern used (e.g., MVC, Layered, Clean Architecture)
2. **Code Quality Score**: Rate 1-10 based on SOLID principles, maintainability, testability
3. **Framework Usage**: How well does it follow framework conventions?
4. **Issues Found**: List anti-patterns, code smells, violations
5. **Recommendations**: Actionable improvements (max 5)
6. **Complexity**: Estimate cognitive complexity (Low/Medium/High)

=== Output Format (STRICT JSON) ===

{
  "architecture": {
    "pattern": "MVC",
    "confidence": 0.85,
    "description": "Brief description"
  },
  "quality_score": 7,
  "quality_details": {
    "maintainability": 8,
    "testability": 6,
    "readability": 7,
    "solid_compliance": 7
  },
  "framework_usage": {
    "score": 8,
    "follows_conventions": true,
    "notes": "Well structured Laravel application"
  },
  "issues": [
    {
      "type": "anti-pattern",
      "severity": "high",
      "description": "God classes detected in controllers",
      "files": ["UserController.php"]
    }
  ],
  "recommendations": [
    {
      "priority": "high",
      "category": "architecture",
      "description": "Extract business logic to service layer",
      "impact": "Improved testability and maintainability"
    }
  ],
  "complexity": "medium",
  "summary": "Overall assessment in 2-3 sentences"
}

=== IMPORTANT ===
- Output ONLY valid JSON
- No markdown code blocks
- No explanations outside JSON
- Keep recommendations actionable and specific
```

**Code Examples:**

```php
// app/Services/LLM/Prompts/BasePrompt.php
<?php

namespace App\Services\LLM\Prompts;

abstract class BasePrompt
{
    abstract public function getSystemPrompt(): string;
    abstract public function getUserPrompt(array $context): string;
    abstract public function getVersion(): string;

    public function render(array $context): array
    {
        return [
            'system' => $this->getSystemPrompt(),
            'user' => $this->replaceVariables($this->getUserPrompt($context), $context),
            'version' => $this->getVersion(),
            'token_count' => $this->estimateTokens($context),
        ];
    }

    protected function replaceVariables(string $template, array $context): string
    {
        foreach ($context as $key => $value) {
            $placeholder = "{{" . $key . "}}";
            $template = str_replace($placeholder, (string) $value, $template);
        }

        return $template;
    }

    protected function estimateTokens(array $context): int
    {
        $content = $this->getUserPrompt($context);

        // Rough estimation: 1 token ≈ 4 characters
        return (int) ceil(strlen($content) / 4);
    }

    protected function truncateIfNeeded(string $content, int $maxTokens = 4000): string
    {
        $estimatedTokens = (int) ceil(strlen($content) / 4);

        if ($estimatedTokens <= $maxTokens) {
            return $content;
        }

        $maxChars = $maxTokens * 4;

        return substr($content, 0, $maxChars) . "\n\n[... truncated ...]";
    }
}
```

```php
// app/Services/LLM/Prompts/AnalyzeCodePrompt.php
<?php

namespace App\Services\LLM\Prompts;

use App\DataTransferObjects\CodeAnalysis\ASTResult;

class AnalyzeCodePrompt extends BasePrompt
{
    public function getSystemPrompt(): string
    {
        return file_get_contents(storage_path('prompts/analyze_code_v1_system.txt'));
    }

    public function getUserPrompt(array $context): string
    {
        return file_get_contents(storage_path('prompts/analyze_code_v1.txt'));
    }

    public function getVersion(): string
    {
        return 'v1.0';
    }

    public function buildContext(ASTResult $ast, array $repoData): array
    {
        return [
            'repo_name' => $repoData['name'],
            'language' => $repoData['language'] ?? 'PHP',
            'framework' => $this->detectFramework($ast, $repoData),
            'file_count' => $repoData['file_count'] ?? 0,
            'total_lines' => $ast->metadata['lines'] ?? 0,
            'file_tree' => $this->formatFileTree($repoData['files'] ?? []),
            'class_count' => count($ast->classes),
            'class_list' => $this->formatClasses($ast->classes),
            'function_count' => count($ast->functions),
            'function_list' => $this->formatFunctions($ast->functions),
            'dependencies' => $this->formatDependencies($ast->dependencies),
            'namespaces' => implode(", ", $ast->namespaces),
        ];
    }

    private function detectFramework(ASTResult $ast, array $repoData): string
    {
        // Simple detection logic
        $dependencies = $ast->dependencies;

        if (in_array('laravel/framework', $dependencies)) {
            return 'Laravel';
        }

        if (in_array('symfony/symfony', $dependencies)) {
            return 'Symfony';
        }

        return 'Unknown';
    }

    private function formatFileTree(array $files): string
    {
        $tree = [];

        foreach (array_slice($files, 0, 50) as $file) {
            $tree[] = "- {$file}";
        }

        if (count($files) > 50) {
            $tree[] = "... and " . (count($files) - 50) . " more files";
        }

        return implode("\n", $tree);
    }

    private function formatClasses(array $classes): string
    {
        $formatted = [];

        foreach (array_slice($classes, 0, 20) as $class) {
            $formatted[] = "- {$class['name']} ({$class['methods_count']} methods)";
        }

        if (count($classes) > 20) {
            $formatted[] = "... and " . (count($classes) - 20) . " more classes";
        }

        return implode("\n", $formatted);
    }

    private function formatFunctions(array $functions): string
    {
        $formatted = [];

        foreach (array_slice($functions, 0, 20) as $function) {
            $formatted[] = "- {$function['name']}()";
        }

        if (count($functions) > 20) {
            $formatted[] = "... and " . (count($functions) - 20) . " more functions";
        }

        return implode("\n", $formatted);
    }

    private function formatDependencies(array $dependencies): string
    {
        return implode("\n", array_map(fn($dep) => "- {$dep}", $dependencies));
    }
}
```

**Test Example:**

```php
// tests/Unit/Services/LLM/Prompts/AnalyzeCodePromptTest.php
<?php

namespace Tests\Unit\Services\LLM\Prompts;

use App\DataTransferObjects\CodeAnalysis\ASTResult;
use App\Services\LLM\Prompts\AnalyzeCodePrompt;
use Tests\TestCase;

class AnalyzeCodePromptTest extends TestCase
{
    public function test_builds_context_correctly(): void
    {
        $prompt = new AnalyzeCodePrompt();

        $ast = new ASTResult(
            classes: [
                ['name' => 'UserController', 'methods_count' => 5],
            ],
            functions: [
                ['name' => 'helper_function'],
            ],
            dependencies: ['laravel/framework', 'guzzlehttp/guzzle'],
            namespaces: ['App\\Controllers', 'App\\Services'],
            metadata: ['lines' => 1500]
        );

        $repoData = [
            'name' => 'my-awesome-app',
            'language' => 'PHP',
            'file_count' => 50,
            'files' => ['app/Controller/UserController.php'],
        ];

        $context = $prompt->buildContext($ast, $repoData);

        $this->assertEquals('my-awesome-app', $context['repo_name']);
        $this->assertEquals('Laravel', $context['framework']);
        $this->assertEquals(1, $context['class_count']);
        $this->assertEquals(1, $context['function_count']);
    }

    public function test_renders_prompt_with_variables(): void
    {
        $prompt = new AnalyzeCodePrompt();

        $ast = new ASTResult(
            classes: [],
            functions: [],
            dependencies: ['laravel/framework'],
            namespaces: [],
            metadata: []
        );

        $context = $prompt->buildContext($ast, [
            'name' => 'test-app',
            'language' => 'PHP',
        ]);

        $rendered = $prompt->render($context);

        $this->assertArrayHasKey('system', $rendered);
        $this->assertArrayHasKey('user', $rendered);
        $this->assertStringContainsString('test-app', $rendered['user']);
        $this->assertStringNotContainsString('{{repo_name}}', $rendered['user']);
    }

    public function test_token_estimation(): void
    {
        $prompt = new AnalyzeCodePrompt();

        $ast = new ASTResult(
            classes: [],
            functions: [],
            dependencies: [],
            namespaces: [],
            metadata: []
        );

        $context = $prompt->buildContext($ast, [
            'name' => 'test',
            'language' => 'PHP',
        ]);

        $rendered = $prompt->render($context);

        $this->assertArrayHasKey('token_count', $rendered);
        $this->assertIsInt($rendered['token_count']);
        $this->assertLessThan(5000, $rendered['token_count']); // Should be under 5K
    }
}
```

**Critères d'acceptation:**
- [ ] Prompt template créé et versionné (v1.0)
- [ ] Context injection fonctionne (variables remplacées)
- [ ] Output JSON validé avec LLM réel (GPT-4 + Mistral)
- [ ] Token count optimisé (<4K tokens)
- [ ] Tests unitaires passent (>80% coverage)
- [ ] Truncation logic pour éviter dépassement tokens
- [ ] Documentation prompt versioning

---

## 📊 Tâches Optionnelles (Recommandées)

### 3. S2.10: Tests Feature E2E (2j - P1) 📝 Recommandé

**Pourquoi important:** Validation end-to-end du workflow complet, confiance dans l'intégration.

**Localisation:** 🧪 MCP Manager (Tests)

**Dépend de:** S2.11 + S2.12 (AST Parser + Prompts)

#### Sous-tâches:

- [ ] **Jour 1: E2E Test Setup (8h)**
  - [ ] Créer `tests/Feature/Workflow/CompleteAnalyzeWorkflowTest.php`
  - [ ] Setup test database avec factories complètes:
    ```bash
    php artisan make:factory WorkflowFactory
    php artisan make:factory WorkflowExecutionFactory
    php artisan make:factory WorkflowStepFactory
    ```
  - [ ] Mock Git OAuth flow (GitHub/GitLab)
  - [ ] Mock LLM responses (OpenAI/Mistral) avec fixtures
  - [ ] Mock repository clone (fake git clone)
  - [ ] Créer fixtures LLM responses: `tests/Fixtures/llm-responses/analyze-code-success.json`

- [ ] **Jour 2: E2E Test Scenarios (8h)**
  - [ ] **Test 1**: Git OAuth → Sync repos → Clone → Analyze (happy path)
  - [ ] **Test 2**: Workflow execution avec multiple steps
  - [ ] **Test 3**: LLM fallback (OpenAI fails → Mistral succeeds)
  - [ ] **Test 4**: Error handling (clone failed, LLM timeout)
  - [ ] **Test 5**: Results storage (database assertions, JSON output)
  - [ ] Créer `tests/Feature/Workflow/WorkflowErrorHandlingTest.php`
  - [ ] Créer `tests/Feature/Workflow/WorkflowLLMFallbackTest.php`

**Fichiers à créer:**
```
tests/Feature/Workflow/
├── CompleteAnalyzeWorkflowTest.php
├── WorkflowErrorHandlingTest.php
└── WorkflowLLMFallbackTest.php

tests/Fixtures/
├── llm-responses/
│   ├── analyze-code-success.json
│   ├── analyze-code-error.json
│   └── analyze-code-timeout.json
└── repositories/
    └── sample-laravel-app/
        ├── app/
        └── composer.json

database/factories/
├── WorkflowFactory.php
├── WorkflowExecutionFactory.php
└── WorkflowStepFactory.php
```

**Test Example:**

```php
// tests/Feature/Workflow/CompleteAnalyzeWorkflowTest.php
<?php

namespace Tests\Feature\Workflow;

use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Enums\WorkflowStatus;
use App\Enums\ExecutionStatus;
use App\Services\LLM\OpenAIService;
use App\Services\Git\GitRepositoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CompleteAnalyzeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_analyze_workflow_happy_path(): void
    {
        // Arrange: User avec Git connection
        $user = User::factory()->create();

        // Mock Git OAuth
        Http::fake([
            'github.com/api/*' => Http::response([
                'repositories' => [
                    ['name' => 'my-app', 'clone_url' => 'https://github.com/user/my-app.git'],
                ],
            ], 200),
        ]);

        // Mock LLM response
        $this->mock(OpenAIService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn(json_decode(file_get_contents(
                    base_path('tests/Fixtures/llm-responses/analyze-code-success.json')
                ), true));
        });

        // Mock Git clone
        $this->mock(GitRepositoryService::class, function ($mock) {
            $mock->shouldReceive('clone')
                ->once()
                ->andReturn('/tmp/my-app');
        });

        // Create workflow
        $workflow = Workflow::factory()->create([
            'user_id' => $user->id,
            'type' => 'analyze_repository',
            'status' => WorkflowStatus::Active,
        ]);

        // Act: Execute workflow
        $execution = WorkflowExecution::factory()->create([
            'workflow_id' => $workflow->id,
            'status' => ExecutionStatus::Pending,
        ]);

        // Dispatch job
        dispatch(new \App\Jobs\RunWorkflowJob($execution));

        // Assert: Workflow executed successfully
        $this->assertDatabaseHas('workflow_executions', [
            'id' => $execution->id,
            'status' => ExecutionStatus::Completed->value,
        ]);

        // Assert: Results stored
        $execution->refresh();
        $this->assertNotNull($execution->output);
        $this->assertArrayHasKey('architecture', $execution->output);
        $this->assertArrayHasKey('quality_score', $execution->output);

        // Assert: Steps completed
        $this->assertEquals(3, $execution->steps()->count());
        $this->assertTrue($execution->steps()->where('name', 'clone_repository')->exists());
        $this->assertTrue($execution->steps()->where('name', 'analyze_code')->exists());
    }

    public function test_workflow_handles_git_clone_failure(): void
    {
        // Arrange
        $user = User::factory()->create();

        $this->mock(GitRepositoryService::class, function ($mock) {
            $mock->shouldReceive('clone')
                ->once()
                ->andThrow(new \RuntimeException('Git clone failed'));
        });

        $workflow = Workflow::factory()->create([
            'user_id' => $user->id,
            'type' => 'analyze_repository',
        ]);

        $execution = WorkflowExecution::factory()->create([
            'workflow_id' => $workflow->id,
        ]);

        // Act
        dispatch(new \App\Jobs\RunWorkflowJob($execution));

        // Assert: Execution failed
        $this->assertDatabaseHas('workflow_executions', [
            'id' => $execution->id,
            'status' => ExecutionStatus::Failed->value,
        ]);

        $execution->refresh();
        $this->assertStringContainsString('Git clone failed', $execution->error_message);
    }

    public function test_workflow_llm_fallback_on_openai_failure(): void
    {
        // Arrange
        $user = User::factory()->create();

        // OpenAI fails
        $this->mock(OpenAIService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andThrow(new \Exception('OpenAI timeout'));
        });

        // Mistral succeeds
        $this->mock(\App\Services\LLM\MistralService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn(json_decode(file_get_contents(
                    base_path('tests/Fixtures/llm-responses/analyze-code-success.json')
                ), true));
        });

        $workflow = Workflow::factory()->create([
            'user_id' => $user->id,
            'type' => 'analyze_repository',
            'config' => [
                'llm_provider' => 'openai',
                'llm_fallback' => 'mistral',
            ],
        ]);

        $execution = WorkflowExecution::factory()->create([
            'workflow_id' => $workflow->id,
        ]);

        // Act
        dispatch(new \App\Jobs\RunWorkflowJob($execution));

        // Assert: Fallback worked
        $this->assertDatabaseHas('workflow_executions', [
            'id' => $execution->id,
            'status' => ExecutionStatus::Completed->value,
        ]);

        $execution->refresh();
        $this->assertArrayHasKey('llm_provider_used', $execution->metadata);
        $this->assertEquals('mistral', $execution->metadata['llm_provider_used']);
    }
}
```

**Critères d'acceptation:**
- [ ] E2E test complet passe (OAuth → Clone → Analyze → Results)
- [ ] Error handling testé (clone failed, LLM timeout, network errors)
- [ ] LLM fallback testé (OpenAI fail → Mistral success)
- [ ] Database assertions valides (executions, steps, results)
- [ ] Coverage >75% pour workflow execution code
- [ ] Tests exécutent en <30 secondes (mocks efficaces)

---

## 📋 Checklist Completion Sprint 2

Avant de considérer Sprint 2 **100% terminé**:

### Backend Critical ⚠️
- [ ] S2.11: AST Parser fonctionnel (parse PHP files)
- [ ] S2.12: Prompt Engineering avec LLM testé (GPT-4 + Mistral)
- [ ] AnalyzeRepositoryAction utilise AST + Prompt
- [ ] Workflow execution end-to-end fonctionne
- [ ] LLM fallback logic implémentée (OpenAI → Mistral)

### Tests 🧪
- [ ] Tests unitaires AST Parser passent (>80% coverage)
- [ ] Tests unitaires Prompts passent (>80% coverage)
- [ ] Optional: Tests E2E passent (>75% coverage workflow)
- [ ] `php artisan test` passe sans erreurs

### Code Quality 💎
- [ ] Code coverage global >75%
- [ ] `./vendor/bin/pint` passe (0 errors)
- [ ] `./vendor/bin/phpstan analyse --level=max app` passe (0 errors)
- [ ] `./vendor/bin/rector process app --dry-run` passe (0 changes)
- [ ] 0 bugs critiques dans Horizon/logs

### Integration ⚙️
- [ ] UI `/workflows` peut lancer AnalyzeRepository
- [ ] Résultats LLM affichés dans UI (JSON formatted)
- [ ] Logs workflow visibles dans Horizon
- [ ] Error states affichés dans UI (clone failed, LLM timeout)

### Documentation 📚
- [ ] README Workflow Engine mis à jour
- [ ] Exemples prompt documentés (`storage/prompts/CHANGELOG.md`)
- [ ] AST Parser usage documenté (PHPDoc + examples)
- [ ] API documentation générée (optional)

### Deployment 🚀
- [ ] `.env.example` mis à jour (OPENAI_API_KEY, MISTRAL_API_KEY)
- [ ] Migration files created et testés
- [ ] Seeders fonctionnels (development data)
- [ ] Composer dependencies à jour (`composer.lock`)

---

## 🚀 Plan d'Exécution

### Option A: Séquentiel (7.5 jours) - ⭐ Recommandé pour 1 développeur

```
Jour 1-3: S2.11 AST Parser
  ├─ Jour 1: Setup + Recherche + Structure
  ├─ Jour 2: Implementation Core (PHP parsing)
  └─ Jour 3: Tests + Integration

Jour 4-5.5: S2.12 Prompt Engineering
  ├─ Jour 4: Design + Implementation prompts
  └─ Demi-journée 5: Real LLM testing + iteration

Jour 6-7.5: S2.10 E2E Tests (optionnel)
  ├─ Jour 6: Setup + Mocks + Factories
  └─ Jour 7: Test scenarios + Error handling
```

**Avantages:**
- Pas de conflits entre tâches
- Focus complet sur chaque tâche
- Facile à suivre et mesurer progrès

**Inconvénients:**
- Durée totale plus longue
- Pas de parallélisation

---

### Option B: Critique seulement (5.5 jours) - ⚡ Si pressé (Sprint 3 urgent)

```
Jour 1-3: S2.11 AST Parser (CRITIQUE)
Jour 4-5.5: S2.12 Prompt Engineering (CRITIQUE)
Reporter S2.10 E2E Tests à Sprint 3 début
```

**Avantages:**
- Sprint 3 peut démarrer plus tôt
- Focus sur bloqueurs uniquement

**Inconvénients:**
- Moins de confiance (pas E2E tests)
- Risque bugs découverts en Sprint 3

**Quand utiliser:** Si deadline Sprint 3 très urgente, ou si E2E tests peuvent être faits en parallèle Sprint 3.

---

### Option C: Parallèle (5.5 jours) - 🚀 Le plus rapide (si 2 développeurs)

```
Dev 1:
  Jour 1-3: S2.11 AST Parser
  Jour 4-5.5: S2.12 Prompt Engineering
  Total: 5.5 jours

Dev 2:
  Jour 1-2: S2.10 E2E Tests (setup + scenarios)
  Jour 3: Documentation (README, prompts, AST)
  Total: 3 jours

Durée totale: 5.5 jours (parallèle)
```

**Avantages:**
- Sprint 2 terminé le plus rapidement
- Tous les livrables complétés
- Documentation faite en parallèle

**Inconvénients:**
- Requiert 2 développeurs
- Dev 2 attend Dev 1 pour tester AST/Prompts réels (peut mocker en attendant)

**Synchronisation:**
- Jour 3: Dev 2 peut commencer E2E tests avec mocks
- Jour 4: Dev 1 merge AST Parser → Dev 2 update E2E avec real AST
- Jour 5: Dev 1 merge Prompts → Dev 2 update E2E avec real Prompts

---

## ⚠️ Risques & Mitigations

| Risque | Impact | Probabilité | Mitigation |
|--------|--------|-------------|------------|
| **AST Parser trop complexe** | Sprint 3 retardé | Moyenne | Commencer par PHP only, defer JS/Python à Sprint 3. Utiliser `nikic/php-parser` (mature). |
| **Prompt ne fonctionne pas** | Analyses LLM inutiles | Moyenne | Itérer rapidement, tester tôt avec budget API ($10). Commencer simple, améliorer v2. |
| **LLM API timeout/rate limit** | Workflow échoue | Haute | Implémenter fallback (OpenAI → Mistral). Retry logic avec backoff. Cache results. |
| **Tests E2E longs à écrire** | Retarde Sprint 3 | Faible | Optionnel, peut être fait en parallèle Sprint 3. Focus sur happy path d'abord. |
| **Token limit dépassé** | Prompt tronqué | Moyenne | Truncation logic dans prompts. Limiter AST summary. Tester avec gros repos. |
| **Budget API OpenAI** | Coût élevé testing | Faible | Utiliser Mistral (cheaper) pour tests. Limiter tests réels LLM. Mock la plupart. |

**Sévérité globale:** Moyenne (mitigations en place)

---

## 📊 Critères de Succès

Sprint 2 sera **100% terminé** quand:

✅ **AST Parser** parse code PHP et extrait structure complète (classes, functions, dependencies)
✅ **Prompt Engineering** génère analyses LLM pertinentes (JSON valid, insights actionnables)
✅ **Workflow AnalyzeRepository** fonctionne end-to-end (Git clone → AST → LLM → Results)
✅ **UI** affiche résultats analyses avec formatting JSON
✅ **Tests unitaires** passent (>75% coverage backend)
✅ **Code quality** passe (Pint + PHPStan + Rector)
✅ **0 bugs critiques** (Horizon logs clean)
✅ **Documentation** complète (README, prompts versioning, AST usage)

**À ce moment, Sprint 3 peut démarrer en toute confiance.**

---

## 📝 Notes Importantes

### Packages à installer

```bash
# AST Parser
composer require nikic/php-parser

# Optional: JavaScript/TypeScript parsing (defer à Sprint 3 si complexe)
# npm install @babel/parser

# Optional: Python parsing (defer à Sprint 3 si complexe)
# pip install ast
```

### Config .env (déjà configurée normalement)

```env
# LLM Providers
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4
OPENAI_TIMEOUT=30

MISTRAL_API_KEY=...
MISTRAL_MODEL=mistral-large-latest
MISTRAL_TIMEOUT=30

# Workflow
WORKFLOW_MAX_EXECUTION_TIME=300
WORKFLOW_RETRY_ATTEMPTS=3

# Git
GIT_CLONE_TIMEOUT=60
GIT_CLONE_PATH=/tmp/git-clones
```

### Quick Start Commands

```bash
# AST Parser
composer require nikic/php-parser
php artisan make:class Services/Code/ASTParserService
php artisan make:class Services/Code/Parsers/PHPParser
php artisan make:test Services/Code/ASTParserServiceTest --unit

# Prompt Engineering
mkdir -p app/Services/LLM/Prompts storage/prompts
php artisan make:class Services/LLM/Prompts/BasePrompt
php artisan make:class Services/LLM/Prompts/AnalyzeCodePrompt
php artisan make:test Services/LLM/Prompts/AnalyzeCodePromptTest --unit

# E2E Tests
php artisan make:test Workflow/CompleteAnalyzeWorkflowTest
php artisan make:test Workflow/WorkflowErrorHandlingTest
php artisan test --filter CompleteAnalyzeWorkflow

# Run all tests
php artisan test

# Run quality checks
./vendor/bin/pint
./vendor/bin/phpstan analyse --level=max app
./vendor/bin/rector process app --dry-run

# Check coverage
php artisan test --coverage --min=75
```

### Testing Budget API

Pour tester les prompts avec LLM réels:

```bash
# OpenAI (environ $0.01 per request avec GPT-4)
Budget recommandé: $5-10 pour 500-1000 tests

# Mistral (moins cher, environ $0.002 per request)
Budget recommandé: $2-5 pour 1000-2500 tests

Total budget testing: $10-15
```

**Tip:** Mocker la majorité des tests, garder 10-20 tests réels LLM pour validation finale.

---

## 🎯 Prochaines Étapes Immédiates

1. **Choisir plan d'exécution** (A, B, ou C)
2. **Installer dépendances:** `composer require nikic/php-parser`
3. **Créer structure de base:** Classes AST Parser + Prompts
4. **Commencer S2.11 Jour 1:** AST Parser setup
5. **Daily standup:** Tracker progrès quotidien

---

**Créé:** 2025-10-26
**Priorité:** ⚠️ URGENT
**Bloque:** Sprint 3 - Workflow Complet IA
**Effort estimé:** 5.5 jours critiques + 2 jours optionnels = 7.5 jours total
**Owner:** À assigner

---

## ✅ Prêt à Commencer

Ce todo list fournit:

- ✅ Tâches critiques vs optionnelles clairement identifiées
- ✅ Sous-tâches détaillées avec timing
- ✅ Code examples concrets
- ✅ Critères d'acceptation mesurables
- ✅ Plans d'exécution flexibles (1 ou 2 devs)
- ✅ Risques identifiés avec mitigations
- ✅ Quick start commands
- ✅ Budget API estimé

**Bonne chance pour Sprint 2 Cleanup!** 🚀
