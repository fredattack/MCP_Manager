# Test Groups

All test classes in this project are organized with PHPUnit groups for easier test execution.

## Available Groups

### By Feature Area
- `auth` - Authentication tests (login, registration, password reset)
- `git` - Git integration tests (GitHub, GitLab, cloning, webhooks)
- `workflow` - Workflow orchestration tests (Sprint 2)
- `llm` - LLM service tests (OpenAI, Mistral - Sprint 2)
- `notion` - Notion integration tests
- `todoist` - Todoist integration tests
- `daily-planning` - Daily planning feature tests
- `mcp` - MCP server management tests
- `integration` - General integration account tests
- `ai` - AI chat controller tests
- `settings` - User settings tests
- `dashboard` - Dashboard tests

### By Test Type
- `unit` - Unit tests (Services, Models, Jobs, Actions)
- `feature` - Feature/Integration tests (HTTP controllers, full flows)

### By Sprint
- `sprint2` - All tests created in Sprint 2 (Workflow + LLM)

### Specific Subgroups
- `model` - Model tests
- `oauth` - OAuth flow tests
- `webhook` - Webhook tests
- `github` - GitHub-specific tests
- `chat` - Chat-related tests
- `crypto` - Cryptography service tests
- `utils` - Utility tests

## Running Tests by Group

### Run all Sprint 2 tests
```bash
php artisan test --group=sprint2
```

### Run only LLM tests
```bash
php artisan test --group=llm
```

### Run only Workflow tests
```bash
php artisan test --group=workflow
```

### Run all Git-related tests
```bash
php artisan test --group=git
```

### Run all unit tests
```bash
php artisan test --group=unit
```

### Run all feature tests
```bash
php artisan test --group=feature
```

### Combine multiple groups (OR logic)
```bash
php artisan test --group=workflow,llm
```

### Exclude groups
```bash
php artisan test --exclude-group=notion,git
```

## Sprint 2 Test Coverage

**Total: 14 tests (100% passing)**

- **Workflow API Tests** (7 tests) - `tests/Feature/Workflow/WorkflowApiTest.php`
  - List workflows
  - Create workflow
  - Show workflow
  - Update workflow
  - Delete workflow
  - Authorization checks
  - Validation

- **OpenAI Service Tests** (3 tests) - `tests/Unit/Services/LLM/OpenAIServiceTest.php`
  - Service instantiation
  - Method chaining (setMaxRetries, setTimeout)

- **Mistral Service Tests** (4 tests) - `tests/Unit/Services/LLM/MistralServiceTest.php`
  - Service instantiation
  - Method chaining
  - Chat request structure

## Quick Test Commands

```bash
# Run all tests
php artisan test

# Run Sprint 2 tests only
php artisan test --group=sprint2

# Run with verbose output
php artisan test --group=sprint2 --verbose

# Run specific test file
php artisan test tests/Feature/Workflow/WorkflowApiTest.php

# Run specific test method
php artisan test --filter=test_can_create_workflow
```

## Note on PHPUnit 12 Deprecation

PHPUnit will show warnings about `@group` annotations being deprecated in favor of PHP 8 attributes. This is expected and the tests will continue to work. Future migration to attributes:

```php
// Current (deprecated in PHPUnit 12)
/**
 * @group workflow
 * @group feature
 */
class WorkflowApiTest extends TestCase

// Future (PHP 8+ attributes)
#[Group('workflow')]
#[Group('feature')]
class WorkflowApiTest extends TestCase
```
