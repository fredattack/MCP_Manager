#!/usr/bin/env php
<?php

/**
 * Admin Panel Implementation Validator
 *
 * This script validates that the admin panel implementation is complete and correct.
 * Run after each phase to verify progress.
 *
 * Usage:
 *   php scripts/validate-admin-panel.php
 *   php scripts/validate-admin-panel.php --phase=1
 *   php scripts/validate-admin-panel.php --verbose
 */

declare(strict_types=1);

// Colors for terminal output
const COLOR_GREEN = "\033[0;32m";
const COLOR_RED = "\033[0;31m";
const COLOR_YELLOW = "\033[0;33m";
const COLOR_BLUE = "\033[0;34m";
const COLOR_CYAN = "\033[0;36m";
const COLOR_RESET = "\033[0m";

class AdminPanelValidator
{
    private array $results = [];

    private bool $verbose = false;

    private ?int $phase = null;

    private string $projectRoot;

    public function __construct(string $projectRoot, bool $verbose = false, ?int $phase = null)
    {
        $this->projectRoot = $projectRoot;
        $this->verbose = $verbose;
        $this->phase = $phase;
    }

    public function validate(): void
    {
        $this->printHeader();

        if ($this->phase === null) {
            $this->validatePhase1();
            $this->validatePhase2();
            $this->validatePhase3();
            $this->validatePhase4();
        } else {
            match ($this->phase) {
                1 => $this->validatePhase1(),
                2 => $this->validatePhase2(),
                3 => $this->validatePhase3(),
                4 => $this->validatePhase4(),
                default => $this->error('Invalid phase number. Use 1-4.'),
            };
        }

        $this->printSummary();
    }

    private function validatePhase1(): void
    {
        $this->section('Phase 1: Backend Infrastructure');

        // Database migrations
        $this->check('Users table has admin fields', function () {
            return $this->fileContains(
                'database/migrations',
                ['role', 'permissions', 'is_active', 'is_locked', 'last_login_at'],
                '*.php'
            );
        });

        $this->check('UserActivityLog migration exists', function () {
            return $this->fileExists('database/migrations', '*_create_user_activity_logs_table.php');
        });

        $this->check('UserToken migration exists', function () {
            return $this->fileExists('database/migrations', '*_create_user_tokens_table.php');
        });

        // Enums
        $this->check('UserRole enum exists', function () {
            return $this->fileExists('app/Enums', 'UserRole.php');
        });

        $this->check('UserRole enum has all roles', function () {
            $content = $this->getFileContent('app/Enums/UserRole.php');

            return $content &&
                   str_contains($content, 'ADMIN') &&
                   str_contains($content, 'MANAGER') &&
                   str_contains($content, 'USER') &&
                   str_contains($content, 'READ_ONLY');
        });

        $this->check('UserPermission enum exists', function () {
            return $this->fileExists('app/Enums', 'UserPermission.php');
        });

        $this->check('UserPermission enum has key permissions', function () {
            $content = $this->getFileContent('app/Enums/UserPermission.php');

            return $content &&
                   str_contains($content, 'USERS_CREATE') &&
                   str_contains($content, 'USERS_DELETE') &&
                   str_contains($content, 'USERS_MANAGE_ROLES');
        });

        // Models
        $this->check('User model has role and permissions', function () {
            $content = $this->getFileContent('app/Models/User.php');

            return $content &&
                   str_contains($content, 'role') &&
                   str_contains($content, 'permissions');
        });

        $this->check('UserActivityLog model exists', function () {
            return $this->fileExists('app/Models', 'UserActivityLog.php');
        });

        $this->check('UserToken model exists', function () {
            return $this->fileExists('app/Models', 'UserToken.php');
        });

        // Service
        $this->check('UserManagementService exists', function () {
            return $this->fileExists('app/Services', 'UserManagementService.php');
        });

        $this->check('UserManagementService has generateCredentials method', function () {
            $content = $this->getFileContent('app/Services/UserManagementService.php');

            return $content && str_contains($content, 'generateCredentials');
        });

        $this->check('UserManagementService generates Base64 credentials', function () {
            $content = $this->getFileContent('app/Services/UserManagementService.php');

            return $content &&
                   str_contains($content, 'base64_encode') &&
                   str_contains($content, 'basic_auth');
        });

        $this->check('UserManagementService has secure password generation', function () {
            $content = $this->getFileContent('app/Services/UserManagementService.php');

            return $content && (
                str_contains($content, 'generateSecurePassword') ||
                str_contains($content, 'Str::random(16)')
            );
        });

        // Middleware
        $this->check('RequireRole middleware exists', function () {
            return $this->fileExists('app/Http/Middleware', 'RequireRole.php');
        });

        $this->check('RequirePermission middleware exists', function () {
            return $this->fileExists('app/Http/Middleware', 'RequirePermission.php');
        });

        $this->check('Middleware registered in bootstrap', function () {
            $content = $this->getFileContent('bootstrap/app.php');

            return $content &&
                   (str_contains($content, 'RequireRole') || str_contains($content, 'role')) &&
                   (str_contains($content, 'RequirePermission') || str_contains($content, 'permission'));
        });

        // Controller
        $this->check('UserManagementController exists', function () {
            return $this->fileExists('app/Http/Controllers/Admin', 'UserManagementController.php');
        });

        $this->check('Controller has CRUD methods', function () {
            $content = $this->getFileContent('app/Http/Controllers/Admin/UserManagementController.php');

            return $content &&
                   str_contains($content, 'index') &&
                   str_contains($content, 'create') &&
                   str_contains($content, 'store') &&
                   str_contains($content, 'show') &&
                   str_contains($content, 'edit') &&
                   str_contains($content, 'update') &&
                   str_contains($content, 'destroy');
        });

        $this->check('Controller has generateCredentials method', function () {
            $content = $this->getFileContent('app/Http/Controllers/Admin/UserManagementController.php');

            return $content && str_contains($content, 'generateCredentials');
        });

        $this->check('Controller has lock/unlock methods', function () {
            $content = $this->getFileContent('app/Http/Controllers/Admin/UserManagementController.php');

            return $content &&
                   str_contains($content, 'lock') &&
                   str_contains($content, 'unlock');
        });

        // Routes
        $this->check('Admin routes file exists', function () {
            return $this->fileExists('routes', 'admin.php');
        });

        $this->check('Admin routes registered', function () {
            $content = $this->getFileContent('bootstrap/app.php');
            $routesContent = $this->getFileContent('routes/admin.php');

            return ($content && str_contains($content, 'admin.php')) ||
                   ($routesContent && str_contains($routesContent, 'UserManagementController'));
        });
    }

    private function validatePhase2(): void
    {
        $this->section('Phase 2: React Frontend');

        // Pages
        $this->check('UsersIndex page exists', function () {
            return $this->fileExists('resources/js/Pages/Admin/Users', 'Index.tsx');
        });

        $this->check('UsersCreate page exists', function () {
            return $this->fileExists('resources/js/Pages/Admin/Users', 'Create.tsx');
        });

        $this->check('UsersEdit page exists', function () {
            return $this->fileExists('resources/js/Pages/Admin/Users', 'Edit.tsx');
        });

        $this->check('UsersShow page exists', function () {
            return $this->fileExists('resources/js/Pages/Admin/Users', 'Show.tsx');
        });

        // Components
        $this->check('UserTable component exists', function () {
            return $this->fileExists('resources/js/Components/Admin', 'UserTable.tsx');
        });

        $this->check('CredentialGenerator component exists', function () {
            return $this->fileExists('resources/js/Components/Admin', 'CredentialGenerator.tsx');
        });

        $this->check('CredentialGenerator displays Base64', function () {
            $content = $this->getFileContent('resources/js/Components/Admin/CredentialGenerator.tsx');

            return $content &&
                   str_contains($content, 'basic_auth') &&
                   (str_contains($content, 'Base64') || str_contains($content, 'Basic Auth'));
        });

        // Monologue design system
        $this->check('Uses Monologue color palette', function () {
            $content = $this->getFileContent('resources/js/Components/Admin/UserTable.tsx') ?: '';
            $content .= $this->getFileContent('tailwind.config.js') ?: '';

            return str_contains($content, '#19d0e8') || str_contains($content, 'cyan');
        });

        $this->check('Uses DM Mono font', function () {
            $content = $this->getFileContent('tailwind.config.js') ?: '';
            $content .= $this->getFileContent('resources/css/app.css') ?: '';

            return str_contains($content, 'DM Mono') || str_contains($content, 'mono');
        });
    }

    private function validatePhase3(): void
    {
        $this->section('Phase 3: Components & Tests');

        // Advanced components
        $this->check('RoleSelector component exists', function () {
            return $this->fileExists('resources/js/Components/Admin', 'RoleSelector.tsx');
        });

        $this->check('PermissionManager component exists', function () {
            return $this->fileExists('resources/js/Components/Admin', 'PermissionManager.tsx');
        });

        $this->check('UserFilters component exists', function () {
            return $this->fileExists('resources/js/Components/Admin', 'UserFilters.tsx');
        });

        // UI components
        $this->check('Badge component exists', function () {
            return $this->fileExists('resources/js/Components/UI', 'Badge.tsx');
        });

        $this->check('Button component exists', function () {
            return $this->fileExists('resources/js/Components/UI', 'Button.tsx');
        });

        $this->check('Input component exists', function () {
            return $this->fileExists('resources/js/Components/UI', 'Input.tsx');
        });

        // Tests
        $this->check('Unit tests exist for UserManagementService', function () {
            return $this->fileExists('tests/Unit', '*UserManagementService*');
        });

        $this->check('E2E tests exist for user flows', function () {
            return $this->fileExists('tests/Browser', '*User*') ||
                   $this->fileExists('tests/E2E', '*user*');
        });

        $this->check('Test for Base64 credential generation exists', function () {
            $unitTests = $this->getFileContent('tests/Unit/UserManagementServiceTest.php') ?: '';

            return str_contains($unitTests, 'base64') || str_contains($unitTests, 'basic_auth');
        });
    }

    private function validatePhase4(): void
    {
        $this->section('Phase 4: Documentation & Data');

        // Seeders & Factories
        $this->check('UserFactory exists', function () {
            return $this->fileExists('database/factories', 'UserFactory.php');
        });

        $this->check('UserFactory has admin state', function () {
            $content = $this->getFileContent('database/factories/UserFactory.php');

            return $content && str_contains($content, 'admin');
        });

        $this->check('UserFactory has locked state', function () {
            $content = $this->getFileContent('database/factories/UserFactory.php');

            return $content && str_contains($content, 'locked');
        });

        $this->check('UserSeeder exists', function () {
            return $this->fileExists('database/seeders', 'UserSeeder.php');
        });

        // Documentation
        $this->check('User management guide exists', function () {
            return $this->fileExists('docs/admin', 'USER_MANAGEMENT_GUIDE.md') ||
                   $this->fileExists('docs', '*USER_MANAGEMENT*.md');
        });

        $this->check('Implementation checklist exists', function () {
            return $this->fileExists('docs/admin', 'IMPLEMENTATION_CHECKLIST.md') ||
                   $this->fileExists('docs', '*CHECKLIST*.md');
        });
    }

    private function check(string $description, callable $test): void
    {
        try {
            $result = $test();
            $this->results[] = ['description' => $description, 'passed' => $result];

            if ($result) {
                $this->success("✓ $description");
            } else {
                $this->fail("✗ $description");
            }
        } catch (\Exception $e) {
            $this->results[] = ['description' => $description, 'passed' => false];
            $this->fail("✗ $description (Error: {$e->getMessage()})");
        }
    }

    private function fileExists(string $directory, string $pattern): bool
    {
        $path = $this->projectRoot.'/'.$directory;
        if (! is_dir($path)) {
            if ($this->verbose) {
                $this->warn("  Directory does not exist: $path");
            }

            return false;
        }

        $files = glob($path.'/'.$pattern);
        $exists = ! empty($files);

        if ($this->verbose && $exists) {
            $this->info('  Found: '.implode(', ', array_map('basename', $files)));
        }

        return $exists;
    }

    private function fileContains(string $directory, array $needles, string $pattern = '*'): bool
    {
        $path = $this->projectRoot.'/'.$directory;
        if (! is_dir($path)) {
            return false;
        }

        $files = glob($path.'/'.$pattern);
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $allFound = true;
            foreach ($needles as $needle) {
                if (! str_contains($content, $needle)) {
                    $allFound = false;
                    break;
                }
            }
            if ($allFound) {
                return true;
            }
        }

        return false;
    }

    private function getFileContent(string $relativePath): ?string
    {
        $path = $this->projectRoot.'/'.$relativePath;

        // Handle wildcards
        if (str_contains($relativePath, '*')) {
            $files = glob($path);
            if (empty($files)) {
                return null;
            }
            $path = $files[0];
        }

        if (! file_exists($path)) {
            if ($this->verbose) {
                $this->warn("  File does not exist: $path");
            }

            return null;
        }

        return file_get_contents($path);
    }

    private function printHeader(): void
    {
        echo COLOR_CYAN."\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║     Admin Panel Implementation Validator                  ║\n";
        echo "║     MCP Manager - User Management System                  ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo COLOR_RESET."\n";

        if ($this->phase !== null) {
            echo COLOR_BLUE."Validating Phase {$this->phase} only...\n".COLOR_RESET;
        } else {
            echo COLOR_BLUE."Validating all phases...\n".COLOR_RESET;
        }
        echo "\n";
    }

    private function section(string $title): void
    {
        echo "\n".COLOR_YELLOW."━━━ $title ━━━".COLOR_RESET."\n\n";
    }

    private function success(string $message): void
    {
        echo COLOR_GREEN.$message.COLOR_RESET."\n";
    }

    private function fail(string $message): void
    {
        echo COLOR_RED.$message.COLOR_RESET."\n";
    }

    private function warn(string $message): void
    {
        echo COLOR_YELLOW.$message.COLOR_RESET."\n";
    }

    private function info(string $message): void
    {
        echo COLOR_BLUE.$message.COLOR_RESET."\n";
    }

    private function error(string $message): void
    {
        echo COLOR_RED."ERROR: $message".COLOR_RESET."\n";
        exit(1);
    }

    private function printSummary(): void
    {
        $total = count($this->results);
        $passed = count(array_filter($this->results, fn ($r) => $r['passed']));
        $failed = $total - $passed;
        $percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

        echo "\n".COLOR_CYAN."━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n".COLOR_RESET;
        echo COLOR_CYAN."SUMMARY\n".COLOR_RESET;
        echo COLOR_CYAN."━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n".COLOR_RESET;

        echo "\n";
        echo "Total checks:  $total\n";
        echo COLOR_GREEN."Passed:        $passed\n".COLOR_RESET;
        if ($failed > 0) {
            echo COLOR_RED."Failed:        $failed\n".COLOR_RESET;
        } else {
            echo "Failed:        $failed\n";
        }
        echo 'Completion:    ';

        if ($percentage >= 90) {
            echo COLOR_GREEN."$percentage%".COLOR_RESET." ✓\n";
        } elseif ($percentage >= 70) {
            echo COLOR_YELLOW."$percentage%".COLOR_RESET." ⚠\n";
        } else {
            echo COLOR_RED."$percentage%".COLOR_RESET." ✗\n";
        }

        echo "\n";

        if ($percentage === 100.0) {
            echo COLOR_GREEN."🎉 All checks passed! Implementation complete.\n".COLOR_RESET;
        } elseif ($percentage >= 90) {
            echo COLOR_YELLOW."⚠️  Almost there! A few items remaining.\n".COLOR_RESET;
        } elseif ($percentage >= 50) {
            echo COLOR_YELLOW."🔨 Good progress! Keep going.\n".COLOR_RESET;
        } else {
            echo COLOR_RED."📋 Just getting started. Follow the roadmap.\n".COLOR_RESET;
        }

        echo "\n";
    }
}

// Parse command line arguments
$options = getopt('', ['phase::', 'verbose', 'help']);

if (isset($options['help'])) {
    echo "Admin Panel Implementation Validator\n\n";
    echo "Usage:\n";
    echo "  php scripts/validate-admin-panel.php [options]\n\n";
    echo "Options:\n";
    echo "  --phase=N     Validate specific phase (1-4)\n";
    echo "  --verbose     Show detailed output\n";
    echo "  --help        Show this help message\n\n";
    echo "Examples:\n";
    echo "  php scripts/validate-admin-panel.php\n";
    echo "  php scripts/validate-admin-panel.php --phase=1\n";
    echo "  php scripts/validate-admin-panel.php --verbose\n";
    exit(0);
}

$verbose = isset($options['verbose']);
$phase = isset($options['phase']) ? (int) $options['phase'] : null;

// Detect project root
$projectRoot = dirname(__DIR__);
if (! file_exists($projectRoot.'/composer.json')) {
    echo COLOR_RED."Error: Could not find project root (no composer.json found)\n".COLOR_RESET;
    exit(1);
}

$validator = new AdminPanelValidator($projectRoot, $verbose, $phase);
$validator->validate();
