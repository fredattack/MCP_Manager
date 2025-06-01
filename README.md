# Laravel React Boilerplate

> **Last Updated:** May 2025
>
> Laravel 12 · React 19 · TailwindCSS 4 · Vite 6 · TypeScript 5.7 · PostgreSQL

A modern, clean starter kit for building web applications with Laravel and React. This boilerplate provides a solid foundation with authentication, modern UI components, and comprehensive quality tools pre-configured.

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 22+
- npm
- PostgreSQL

### Installation

1. Clone the repository:
   ```bash
   git clone git@github.com:fredattack/laravel-react-boilerplate.git
   cd laravel-react-boilerplate
   ```

2. Install dependencies:
   ```bash
   make install
   ```

3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=laravel
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. Run migrations:
   ```bash
   make migrate
   ```

7. Start the application:
   ```bash
   make start-all
   ```

## 🛠️ Development

### Available Commands

- `make start` - Start the Laravel app locally (port 1978)
- `make dev` - Start the Vite development server
- `make start-all` - Start both Laravel and Vite servers
- `make test` - Run all tests
- `make lint` - Run ESLint on JavaScript/TypeScript files
- `make stan` - Run PHPStan for PHP static analysis (max level)
- `make rector` - Run Rector for PHP code quality and automatic refactoring
- `make coverage` - Generate test coverage reports
- `make docs` - Generate API documentation with Scramble
- `make format` - Format code with Prettier
- `make quality-check` - Run all static checks (test, lint, stan, rector, format)
- `make install` - Install all dependencies (Composer and npm)
- `make migrate` - Run database migrations
- `make seed` - Run database seeders
- `make fresh` - Fresh migration with seeding
- `make help` - Show all available commands

### Code Quality Tools

This project uses several tools to ensure code quality:

#### PHP Quality Tools

- **PHPStan (v2.1)** - Static analysis tool that finds errors in your code without running it
  - Configured at maximum level for strictest type checking
  - Run with `make stan`

- **Rector (v2.0)** - Automated refactoring tool
  - Automatically upgrades your code to use newer PHP features
  - Fixes code that would trigger PHPStan errors
  - Run with `make rector`

- **Laravel Pint (v1.18)** - PHP code style fixer
  - Based on PHP-CS-Fixer with Laravel defaults
  - Automatically formats your code to follow Laravel coding standards
  - Run through pre-commit hooks

#### JavaScript/TypeScript Quality Tools

- **ESLint (v9.17)** - Linting utility for JavaScript and TypeScript
  - Configured with React and React Hooks plugins
  - Run with `make lint`

- **Prettier (v3.4.2)** - Code formatter
  - Ensures consistent code style
  - Run with `make format`

- **TypeScript (v5.7.2)** - Static type checking
  - Provides better IDE support and catches errors early
  - Run type checking with `npm run types`

### Pre-commit Hooks

Pre-commit hooks are set up with Husky (v9.0.11) and lint-staged (v15.2.2) to automatically run:

- Prettier on JavaScript/TypeScript and CSS files
- ESLint on JavaScript/TypeScript files
- Pint on PHP files
- Rector on PHP files
- PHPStan on PHP files

This ensures that all committed code meets the project's quality standards.

## 📦 Main Package Versions

### Backend (PHP)

- **PHP**: ^8.2
- **Laravel**: ^12.0
- **Inertia.js**: ^2.0
- **Laravel Tinker**: ^2.10.1
- **Ziggy**: ^2.4

### Frontend (JavaScript)

- **React**: ^19.0.0
- **React DOM**: ^19.0.0
- **Vite**: ^6.0
- **TailwindCSS**: ^4.0.0
- **TypeScript**: ^5.7.2
- **Inertia.js React**: ^2.0.0

## 📋 Project Structure

```
/app            # Laravel application code
/config         # Configuration files
/database       # Migrations, seeders, and factories
/public         # Publicly accessible files
/resources      # Frontend resources (React, CSS)
  /js           # React components and pages
  /css          # TailwindCSS styles
/routes         # API and web routes
/storage        # Application storage
/tests          # Test files
```

## 🧪 CI/CD

GitHub Actions workflows are set up to run tests and linting on each push to the main and develop branches, and on pull requests to these branches.

## 📝 License

This project is licensed under the MIT License.
