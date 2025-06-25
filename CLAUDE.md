# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Backend (Laravel)
- `php artisan serve --port=3978` - Start Laravel development server
- `php artisan test` - Run PHP tests (PHPUnit with sqlite in-memory DB)
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Fresh migration with seeding
- `./vendor/bin/phpstan analyse --level=max app` - Static analysis (strictest level)
- `./vendor/bin/rector process app` - Automated refactoring
- `./vendor/bin/pint` - Laravel code style fixer

### Frontend (React/TypeScript)
- `npm run dev` - Start Vite development server
- `npm run build` - Build production assets (required for production mode)
- `npm run lint` - ESLint with auto-fix
- `npm run types` - TypeScript type checking
- `npm run format` - Prettier formatting

### Unified Development
- `composer dev` - Start all services concurrently (Laravel, queue, logs, Vite)
- `make start-all` - Start Laravel and Vite servers
- `make quality-check` - Run all quality checks (test, lint, stan, rector, format)

### Testing
- Run single test: `php artisan test --filter TestClassName`
- Run single test method: `php artisan test --filter TestClassName::test_method_name`
- Run feature tests: `./vendor/bin/phpunit tests/Feature`
- Run unit tests: `./vendor/bin/phpunit tests/Unit`

## Architecture Overview

### Full-Stack Laravel + React with Inertia.js
This is a Laravel 12 backend with React 19 frontend connected via Inertia.js. The application manages MCP (Model Context Protocol) integrations, particularly Notion integration.

### Key Technology Stack
- **Backend**: Laravel 12, PHP 8.2+, Inertia.js server-side
- **Frontend**: React 19, TypeScript 5.7, TailwindCSS 4, Vite 6
- **Database**: PostgreSQL (production), SQLite (testing)
- **UI**: Radix UI components, shadcn/ui pattern
- **State Management**: React hooks, Inertia.js for page data

### Integration System Architecture
The application uses a flexible integration system centered around `IntegrationAccount` model:

#### Core Integration Model (`app/Models/IntegrationAccount.php`)
- Stores user integrations with external services (Notion, future: Gmail, OpenAI, etc.)
- Fields: `user_id`, `type` (enum), `access_token`, `meta` (JSON), `status`
- Encrypted tokens for security

#### Integration Types (`app/Enums/IntegrationType.php`, `app/Enums/IntegrationStatus.php`)
- Extensible enum system for adding new integration types
- Status management (active, inactive, error states)

#### Notion Integration Specifics
- **Service**: `app/Services/NotionService.php` - Orchestrates calls to MCP Server
- **Controllers**: `NotionController.php`, `NotionIntegrationController.php` 
- **Middleware**: `HasActiveNotionIntegration.php` - Guards Notion routes
- **Frontend**: `resources/js/pages/notion.tsx`, integration components in `resources/js/components/integrations/`

### Frontend Architecture

#### Page Structure (Inertia.js)
- Pages in `resources/js/pages/` correspond to Laravel routes
- Shared data via `HandleInertiaRequests` middleware
- Layout system in `resources/js/layouts/` (app, auth, settings)

#### Component Organization
- `components/ui/` - Reusable UI components (buttons, forms, etc.)
- `components/integrations/` - Integration-specific components
- `hooks/` - Custom React hooks for common functionality
- `types/` - TypeScript type definitions

#### State Management Patterns
- Custom hooks for integration management (`use-integrations.ts`)
- Form state managed with React hooks
- Inertia.js handles page-level state and navigation

### Authentication & Authorization
- Laravel Breeze with Inertia.js React preset
- Authentication controllers in `app/Http/Controllers/Auth/`
- Integration-specific middleware for access control

### Database Design
- Standard Laravel migrations in `database/migrations/`
- Factory pattern for testing (`database/factories/`)
- Integration accounts table designed for multiple service types

### Quality Tools Configuration
- **PHP**: PHPStan (max level), Rector (PHP 8.2, code quality, dead code removal), Pint (Laravel style)
- **TypeScript**: ESLint 9, Prettier, strict TypeScript config
- **Pre-commit hooks**: Husky + lint-staged for automatic code quality
- **Testing**: PHPUnit with in-memory SQLite, separate test/feature suites

### Rector Configuration
Rector is configured to:
- Target PHP 8.2 features
- Apply code quality improvements, dead code removal, early return patterns
- Add type declarations and improve naming
- Skip Auth and Settings controllers (likely generated code)

### Development Workflow
1. Backend API development in Laravel controllers
2. Frontend component development with TypeScript/React
3. Integration via Inertia.js (no separate API routes needed for most features)
4. Quality checks via pre-commit hooks and make commands

### MCP Server Communication
The application communicates with external MCP servers for integration functionality:
- Notion API calls proxied through Laravel backend
- Token-based authentication forwarded to MCP servers
- Error handling and response transformation in Laravel services