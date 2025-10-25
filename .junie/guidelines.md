# 🧭 Project Guidelines –  Starter Kit

> Laravel 12 · React 19 · TailwindCSS 4 · Vite 6 · PostgreSQL

## 🎯 Purpose

This guide defines the coding standards, architecture conventions, and development best practices to ensure consistency, readability, scalability, and testability across the project. It also serves as a reference for AI agents or new contributors.

## 🚀 Build & Configuration Instructions

### Initial Setup

1. **Clone the repository**:

   ```bash
   git clone <repository-url>
   cd mcp_manager
   ```
2. **Install dependencies**:

   ```bash
   make install
   ```

   This will install both PHP (Composer) and JavaScript (npm) dependencies.
3. **Environment configuration**:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Database setup**:

   - The project uses SQLite by default for simplicity
   - To use PostgreSQL (recommended for production):
     ```bash
     # Update .env file with PostgreSQL credentials
     DB_CONNECTION=pgsql
     DB_HOST=127.0.0.1
     DB_PORT=5432
     DB_DATABASE=mcp_manager
     DB_USERNAME=postgres
     DB_PASSWORD=your_password
     ```
5. **Run migrations**:

   ```bash
   make migrate
   make seed  # Optional: seed the database with sample data
   ```

### Development Workflow

1. **Start the application**:

   ```bash
   make start-all  # Starts both Laravel (port 1978) and Vite servers
   ```
2. **Development Cycle Requirements**:

   - All development **must follow TDD**: write tests first, then implement logic.
   - Each new **feature or unit must be covered** by one or more tests (unit or feature).
   - At the **end of each cycle**, run:
     ```bash
     make test
     make quality-check
     ```
   - If errors are detected (test failures, linting, static analysis), they **must be fixed immediately**.
   - Commits should only be made if:
     - All tests pass
     - All quality checks pass (`stan`, `eslint`, `pint`, `prettier`, `rector`, etc.)
     - Code coverage is maintained at **80%+**
3. **Build for production**:

   ```bash
   make build
   ```

---

## 🧪 Testing Guidelines

### Configuring Tests

1. **Test Environment**:

   - Tests use SQLite in-memory database by default (configured in `phpunit.xml`)
   - No additional configuration is needed for basic testing
2. **Test Structure**:

   - **Unit Tests**: Located in `tests/Unit/` - for testing isolated components
   - **Feature Tests**: Located in `tests/Feature/` - for testing API endpoints and application features
   - All tests extend `Tests\TestCase`

### Running Tests

1. **Run all tests**:

   ```bash
   make test
   ```
2. **Run specific test file**:

   ```bash
   ./vendor/bin/phpunit tests/Unit/StringUtilsTest.php
   ```
3. **Run tests with coverage report**:

   ```bash
   make coverage
   ```

   This generates HTML coverage reports in the `coverage/` directory.

### Adding New Tests

**TDD is mandatory.** Every piece of logic or feature **must begin with tests** before implementation. Skipping this step is not allowed under any circumstances.

1. **Creating a Unit Test**:

   - Create a new file in `tests/Unit/` directory
   - Name the file with the suffix `Test.php` (e.g., `StringUtilsTest.php`)
   - Extend the `Tests\TestCase` class
   - Use the `RefreshDatabase` trait if your test interacts with the database
2. **Creating a Feature Test**:

   - Create a new file in `tests/Feature/` directory
   - Follow the same naming convention as unit tests
   - Use `$this->get()`, `$this->post()`, etc. for HTTP requests
   - Use `$this->actingAs($user)` for authentication
3. **Example Test**:
   Here's a simple unit test example:

   ```php
   <?php

   namespace Tests\Unit;

   use Tests\TestCase;

   class StringUtilsTest extends TestCase
   {
       /**
        * Test the string reversal function.
        */
       public function test_string_reversal()
       {
           // Create a simple function to reverse a string
           $reverseString = function (string $input): string {
               return strrev($input);
           };

           // Test with a simple string
           $input = "Hello, World!";
           $expected = "!dlroW ,olleH";

           $result = $reverseString($input);

           $this->assertEquals($expected, $result);
       }
   }
   ```
4. **Best Practices**:

   - Write descriptive test method names starting with `test_`
   - Follow the AAA pattern: Arrange, Act, Assert
   - Keep tests independent and isolated
   - Mock external dependencies when necessary
   - Aim for 80% code coverage minimum

---

## 🛠 Backend Guidelines (Laravel 12)

### 🔧 General Principles

- Strict adherence to **PSR-12** coding standards.
- Use **Laravel 12** with **PHP 8.4**.
- Favor **type hints** and **return types** everywhere.
- Use strict SOLID principles.
- Use **Service + Action Pattern** only (no repositories).
- Stick to **PHP attributes** for routing, validation, etc.
- Favor **typed DTOs** and **form request classes**.
- Use **PostgreSQL** (strict schema, typed columns).

### 🧪 Testing

- Unit tests: **PHPUnit** only, and **mandatory for each service/action**.
- Feature tests: **Pest** or PHPUnit — **required for all API endpoints**.
- Coverage goal: **80% minimum**; **must not drop** between commits.
- QA checks (`stan`, `rector`, `pint`) must be run and fixed before merge or deploy.

### 📦 Tools & Quality

- **PHPStan**: Level max, no warning allowed.
- **Rector**: Applied via `make rector`.
- **Pre-commit hooks**: Lint, format, rector check.
- Documentation via `make docs` (scramble).

### ✅ Naming Conventions

| Element     | Convention                | Example                     |
| ----------- | ------------------------- | --------------------------- |
| Services    | `PascalCase`            | `InvoiceFetcherService`   |
| Actions     | `Verb+NounAction`       | `DownloadInvoiceAction`   |
| Controllers | `PascalCaseController`  | `InvoiceController`       |
| Models      | `Singular`              | `Invoice`, `Portal`     |
| Routes      | RESTful (resource routes) | `Route::apiResource(...)` |

---

## 💻 Frontend Guidelines (React 19 + Vite 6 + TailwindCSS 4)

### 📐 Project Architecture

Modular structure inspired by feature-sliced design:

```
/pages          # Route-bound views
/services       # API logic using tanstack/react-query
/stores         # Zustand (preferred), Context fallback
/ui             # Pure UI logic with TailwindCSS
```

### 📚 State Management

- Use **Zustand** for local/global state.
- Do not overuse context unless it's for theming/auth.
- Favor **React Query** for all API calls (caching, mutation, status).

### 🧪 Testing & Linting

- ESLint (airbnb + prettier + typescript)
- Coverage: 80%+ via `make coverage`
- E2E tests: **Cypress**
- React component/unit tests: **Vitest** or **Jest**

### 🎨 Styling

- Use **TailwindCSS 4** only.
- Encapsulate complex components inside `/ui`
- Avoid inline styles unless dynamic.
- Use `clsx` or `classnames` for conditional class logic.

# 🎨 UI/UX & Web Design Guidelines

---

## 🌟 Design Principles

- **Clarity First**: Interface visuelle épurée, priorité au contenu et à la hiérarchie visuelle.
- **Consistency**: Utiliser un design system réutilisable (couleurs, composants, espacement).
- **Affordance visuelle**: Tous les éléments interactifs doivent clairement sembler cliquables.
- **Feedback immédiat**: Chaque action de l’utilisateur (clic, chargement, erreur) doit déclencher un feedback visuel ou sonore.
- **Accessibility First**: Minimum WCAG AA, contrastes adaptés, navigable au clavier, textes accessibles.
- **Mobile-First**: Concevoir et tester d’abord pour mobile, ensuite adapter pour desktop.

---

## 🧠 IA-Friendly Components

- **Composants Atomiques**: Créer des composants simples et prédictibles (`Button`, `Card`, `Modal`).
- **Prompt Zones**: Prévoir des zones pour suggestion automatique ou inline AI prompts.
- **Editable Zones**: Zones d’entrée (`textarea`, champs) bien balisées pour une prise en main facile par l'IA.
- **Design tokens**: Exposer couleurs, tailles, espacements sous forme de tokens pour manipulation programmatique.

---

## 🧩 Structure Visuelle

- **Max Width Container**: `max-w-[1200px] mx-auto px-4`
- **Spacing System**: Tailwind spacing scale strictement utilisée (ex: `space-y-6`, `gap-4`)
- **Consistent Layout Grid**: Grilles en 12 ou 24 colonnes selon le contexte.
- **Whitespace Pro Design**: Utilisation généreuse de l’espace vide pour éviter la surcharge cognitive.

---

## 🎯 UX Best Practices

- **Onboarding ultra simplifié**: Une IA doit pouvoir guider un nouvel utilisateur en 3 étapes max.
- **Zero State Design**: Afficher des visuels illustratifs ou appels à action quand une section est vide.
- **Confirmation & Undo**: Toujours permettre à l'utilisateur d'annuler une action (ex: delete avec snackbar “undo”).
- **Navigation**: `SideBar` ou `TabBar` persistante, claire et avec des icônes + labels.
- **Responsiveness**: Interactions tactiles fluides, boutons accessibles avec le pouce (mobile).

---

## 📱 Components Guidelines

| Composant        | Règle UI/UX                                                          |
| ---------------- | --------------------------------------------------------------------- |
| **Button** | Taille min.`44x44px`, états : `hover`, `focus`, `disabled`   |
| **Input**  | Label visible + placeholder explicite +`focus ring`                 |
| **Modal**  | `Scroll lock` + fermeture via `ESC` + fond semi-transparent       |
| **Table**  | `Sticky header`, responsive (`collapse` ou `scroll`)            |
| **Card**   | Ombres douces (`shadow-md`), padding cohérent (`p-4` ou `p-6`) |

### ✅ Naming Conventions psr-12

| Element  | Convention | Example                |
| -------- | ---------- | ---------------------- |
| Pages    | PascalCase | `Dashboard.jsx`      |
| Services | camelCase  | `getInvoices.js`     |
| Stores   | camelCase  | `useInvoiceStore.js` |
| UI       | PascalCase | `InvoiceCard.jsx`    |
| Folders  | kebab-case | `invoice-history`    |

---

## 🚫 Common Antipatterns

### Backend

- ❌ No `Repository` pattern — use `Service + Action`.
- ❌ No logic in controllers.
- ❌ Avoid using Facades inside services (favor dependency injection).
- ❌ Avoid complex validation rules inline (use `FormRequest`).

### Frontend

- ❌ No use of `axios` directly — always use services + react-query.
- ❌ No direct state mutation — always go through Zustand/React state logic.
- ❌ Avoid global CSS or style tags — Tailwind only.

---

## 📊 CI/CD & Automation

### Makefile Commands (key targets)

| Command                | Purpose                           |
| ---------------------- | --------------------------------- |
| `make start`         | Start the app locally (port 1978) |
| `make test`          | Run all tests                     |
| `make lint`          | Run ESLint                        |
| `make stan`          | Run PHPStan                       |
| `make rector`        | Run Rector                        |
| `make coverage`      | Generate coverage reports         |
| `make docs`          | Generate API documentation        |
| `make format`        | Prettier format                   |
| `make quality-check` | Run all static checks             |

---

## 📋 Example API Structure (Laravel)

```php
#[Route('GET', '/api/invoices')]
public function listInvoices(ListInvoicesAction $action): JsonResponse
{
    return response()->json($action->execute());
}
```

```php
// ListInvoicesAction.php
class ListInvoicesAction
{
    public function execute(): array
    {
        return Invoice::latest()->get()->toArray();
    }
}
```

---

## 📋 Example Store (Zustand)

```js
import { create } from 'zustand';

export const useInvoiceStore = create((set) => ({
  invoices: [],
  setInvoices: (data) => set({ invoices: data }),
}));
```

---

## 📋 Example Service (React)

```js
// /services/invoice.js
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

export const useInvoices = () =>
  useQuery(['invoices'], async () => {
    const { data } = await axios.get('/api/invoices');
    return data;
  });
```

---

## 📌 KPIs to Track

- ✅ 80%+ test coverage (front & back)
- ✅ CI always green
- ✅ Lint/Stan/Rector all pass with `make quality-check`
- ✅ Manual invoice extraction < 30s
- ✅ OCR accuracy > 85%

## Quality-check

- ✅ No critical bugs in production
- never add ignore rules in ESLint or PHPStan
- never edit the Makefile without user specific approval

### Automation Rules

- All branches must pass `make test` and `make quality-check` before merge.
- CI will block any push with failing tests or linting errors.
- Code reviews should verify:
  - Tests were written first
  - Coverage is sufficient
  - No skip/ignore rules were added
  - Quality tools (`PHPStan`, `ESLint`, etc.) pass clean

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.1
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- tightenco/ziggy (ZIGGY) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2
- @inertiajs/react (INERTIA) - v2
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== inertia-laravel/core rules ===

## Inertia Core

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (vite.config.js).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.
- Use `search-docs` for accurate guidance on all things Inertia.

<code-snippet lang="php" name="Inertia::render Example">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>


=== inertia-laravel/v2 rules ===

## Inertia v2

- Make use of all Inertia features from v1 & v2. Check the documentation before making any changes to ensure we are taking the correct approach.

### Inertia v2 New Features
- Polling
- Prefetching
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll

### Deferred Props & Empty States
- When using deferred props on the frontend, you should add a nice empty state with pulsing / animated skeleton.

### Inertia Form General Guidance
- Build forms using the `useForm` helper. Use the code examples and `search-docs` tool with a query of `useForm helper` for guidance.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== inertia-react/core rules ===

## Inertia + React

- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet name="Inertia Client Navigation" lang="react">

import { Link } from '@inertiajs/react'
<Link href="/">Home</Link>

</code-snippet>


=== inertia-react/v2/forms rules ===

## Inertia + React Forms

<code-snippet name="Inertia React useForm Example" lang="react">

import { useForm } from '@inertiajs/react'

const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit(e) {
    e.preventDefault()
    post('/login')
}

return (
<form onSubmit={submit}>
    <input type="text" value={data.email} onChange={e => setData('email', e.target.value)} />
    {errors.email && <div>{errors.email}</div>}
    <input type="password" value={data.password} onChange={e => setData('password', e.target.value)} />
    {errors.password && <div>{errors.password}</div>}
    <input type="checkbox" checked={data.remember} onChange={e => setData('remember', e.target.checked)} /> Remember Me
    <button type="submit" disabled={processing}>Login</button>
</form>
)

</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>