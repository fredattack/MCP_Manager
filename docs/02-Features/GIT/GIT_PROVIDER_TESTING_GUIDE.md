# Git Provider Testing Quick Reference Guide

## Quick Start

### Run All Git Tests
```bash
php artisan test tests/Unit/Services/Git tests/Unit/Jobs tests/Unit/Models/Git* tests/Feature/Git --compact
```

### Run with Coverage
```bash
php artisan test tests/Unit/Services/Git tests/Unit/Jobs tests/Unit/Models/Git* tests/Feature/Git --coverage --min=80
```

## Test Categories

### 1. Service Tests (High Priority - 90%+ coverage)
```bash
# All services
php artisan test tests/Unit/Services/Git

# Individual services
php artisan test tests/Unit/Services/Git/GitOAuthServiceTest.php
php artisan test tests/Unit/Services/Git/Clients/GitHubClientTest.php
php artisan test tests/Unit/Services/Git/GitRepositoryServiceTest.php
php artisan test tests/Unit/Services/Git/GitCloneServiceTest.php
php artisan test tests/Unit/Services/Git/WebhookSignatureVerifierTest.php
php artisan test tests/Unit/Services/Git/WebhookEventHandlerTest.php
```

### 2. Job Tests (Medium Priority - 85%+ coverage)
```bash
php artisan test tests/Unit/Jobs/CloneRepositoryJobTest.php
```

### 3. Model Tests (Lower Priority - 70%+ coverage)
```bash
# All Git models
php artisan test tests/Unit/Models/Git*

# Individual models
php artisan test tests/Unit/Models/GitConnectionTest.php
php artisan test tests/Unit/Models/GitRepositoryTest.php
php artisan test tests/Unit/Models/GitCloneTest.php
```

### 4. Feature Tests (Critical Flows - 80%+ coverage)
```bash
# All feature tests
php artisan test tests/Feature/Git

# Individual flows
php artisan test tests/Feature/Git/GitOAuthFlowTest.php
php artisan test tests/Feature/Git/GitRepositorySyncTest.php
php artisan test tests/Feature/Git/GitWebhookTest.php
php artisan test tests/Feature/Git/GitCloneTest.php
```

## Running Specific Tests

### By Test Method
```bash
php artisan test --filter=test_generate_auth_url_for_github_with_pkce
```

### By Test Class
```bash
php artisan test tests/Unit/Services/Git/GitOAuthServiceTest.php
```

### Exclude Specific Tests
```bash
php artisan test --exclude-group=integration
```

## Coverage Reports

### Generate HTML Coverage Report
```bash
php artisan test --coverage-html coverage-report
open coverage-report/index.html
```

### Generate Text Coverage Summary
```bash
php artisan test --coverage-text
```

### Coverage for Specific File
```bash
php artisan test --coverage-filter=app/Services/Git/GitOAuthService.php
```

## Test Output Options

### Compact Output
```bash
php artisan test --compact
```

### Verbose Output
```bash
php artisan test -v
```

### Stop on Failure
```bash
php artisan test --stop-on-failure
```

### Parallel Execution
```bash
php artisan test --parallel
```

## Debugging Tests

### Run Single Test with Full Output
```bash
php artisan test tests/Unit/Services/Git/GitOAuthServiceTest.php --filter=test_generate_auth_url_for_github_with_pkce -v
```

### Enable Debug Logging
```bash
# Add to test method
\Illuminate\Support\Facades\Log::debug('Debug info', ['data' => $variable]);

# Run test
php artisan test --filter=your_test_method
```

### Dump Variables
```bash
# In test method
dump($variable);
dd($variable); // Dump and die
```

## Test Statistics

### Total Tests: 222

#### Unit Tests: 169 (76%)
- Services: 101 tests
- Jobs: 11 tests
- Models: 57 tests

#### Feature Tests: 53 (24%)
- OAuth Flow: 10 tests
- Repository Sync: 14 tests
- Webhooks: 14 tests
- Clone Operations: 15 tests

## Expected Test Results

### Passing Tests (Should Always Pass)
- ✓ All GitOAuthService tests (13/13)
- ✓ All WebhookSignatureVerifier tests (21/21)
- ✓ All Model tests (57/57)

### Tests Requiring Route Configuration
Some feature tests may require route adjustments to match actual API endpoints:
- GitOAuthFlowTest (OAuth routes)
- GitRepositorySyncTest (Repository routes)
- GitWebhookTest (Webhook routes)
- GitCloneTest (Clone routes)

## Common Test Failures & Fixes

### 1. HTTP 404 - Route Not Found
**Problem**: Feature test making request to non-existent route
**Fix**: Verify routes in `routes/api.php` match test expectations
```bash
php artisan route:list --name=git
```

### 2. RequestException - HTTP Client Not Faked
**Problem**: Test making real HTTP call instead of using fake
**Fix**: Ensure `Http::fake()` called before HTTP request in test

### 3. Database Errors
**Problem**: Missing database tables or incorrect schema
**Fix**: Run migrations
```bash
php artisan migrate:fresh
```

### 4. Token Encryption Errors
**Problem**: APP_KEY not set or invalid
**Fix**: Generate application key
```bash
php artisan key:generate
```

### 5. Process Execution Errors
**Problem**: Process::fake() not configured
**Fix**: Add Process::fake() to setUp() method

## Test Configuration

### PHPUnit Configuration (`phpunit.xml`)
```xml
<phpunit>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
    </php>
</phpunit>
```

### Test Environment Variables
Create `.env.testing` file:
```env
APP_ENV=testing
APP_KEY=base64:...
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

## Coverage Goals

| Component | Target | Status |
|-----------|--------|--------|
| GitOAuthService | 90%+ | ✓ |
| GitHubClient | 90%+ | ✓ |
| GitRepositoryService | 90%+ | ✓ |
| GitCloneService | 90%+ | ✓ |
| WebhookSignatureVerifier | 95%+ | ✓ |
| WebhookEventHandler | 90%+ | ✓ |
| CloneRepositoryJob | 85%+ | ✓ |
| Models (Git*) | 70%+ | ✓ |
| Controllers | 80%+ | Pending |
| Overall | 80%+ | ✓ |

## CI/CD Integration

### GitHub Actions Example
```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          extensions: sqlite3

      - name: Install Dependencies
        run: composer install

      - name: Run Tests
        run: php artisan test --coverage --min=80

      - name: Upload Coverage
        uses: codecov/codecov-action@v3
```

## Best Practices

### Writing New Tests
1. Use descriptive test names: `test_action_expected_result()`
2. Follow AAA pattern: Arrange, Act, Assert
3. One assertion per test (when possible)
4. Use factories for test data
5. Mock external dependencies
6. Clean up resources in tearDown()

### Test Organization
```php
<?php

class YourServiceTest extends TestCase
{
    use RefreshDatabase;

    private YourService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new YourService;
        Http::fake();
    }

    public function test_feature_works_correctly(): void
    {
        // Arrange
        $data = ['test' => 'value'];

        // Act
        $result = $this->service->process($data);

        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

## Troubleshooting

### Tests Running Slowly?
```bash
# Use parallel execution
php artisan test --parallel

# Use in-memory SQLite (should already be configured)
# Check phpunit.xml: <env name="DB_DATABASE" value=":memory:"/>
```

### Memory Issues?
```bash
# Increase memory limit
php -d memory_limit=512M artisan test
```

### Permission Issues?
```bash
# Fix storage permissions
chmod -R 777 storage bootstrap/cache
```

## Quick Commands Cheat Sheet

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage --min=80

# Run specific test file
php artisan test tests/Unit/Services/Git/GitOAuthServiceTest.php

# Run specific test method
php artisan test --filter=test_method_name

# Stop on first failure
php artisan test --stop-on-failure

# Compact output
php artisan test --compact

# Generate HTML coverage
php artisan test --coverage-html coverage-report

# Run in parallel
php artisan test --parallel

# Run only unit tests
php artisan test tests/Unit

# Run only feature tests
php artisan test tests/Feature
```

## Getting Help

If tests are failing:
1. Check test output for specific error
2. Verify environment configuration
3. Ensure migrations are up to date
4. Check that all dependencies are installed
5. Review test logs: `storage/logs/laravel.log`

For questions about specific tests, refer to:
- `GIT_PROVIDER_TEST_SUMMARY.md` - Detailed test documentation
- Test file comments - Each test has descriptive comments
- Service documentation - Check service PHPDoc blocks
