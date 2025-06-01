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
   git clone git@github.com:fredattack/laravel-react-boilerplate.git my-project-name
   cd my-project-name
   ```

   > Replace `my-project-name` with your desired project name.

2. Rename the project:

   **Option 1: Using the make command (recommended)**
   ```bash
   make rename-project VENDOR=yourvendor NAME=your-project-name
   ```

   You can also specify optional parameters:
   ```bash
   make rename-project VENDOR=yourvendor NAME=your-project-name DESCRIPTION="Your project description" DISPLAY_NAME="Your Display Name" DB_NAME=your_database_name
   ```

   > Replace `yourvendor` with your organization or username (e.g., `acme`). This is the "vendor" name in Composer terminology, which helps identify who created the package. It's typically your GitHub username, company name, or any unique identifier for you or your organization.
   > Replace `your-project-name` with your project name (e.g., `invoice-app`).
   > Replace `Your project description` with your project description.
   > Replace `Your Display Name` with your project's display name.
   > Replace `your_database_name` with your database name (use underscores instead of hyphens).

   **Option 2: Manually**
   ```bash
   # Update composer.json with your project details
   sed -i '' 's/"name": "laravel\/react-starter-kit"/"name": "yourvendor\/your-project-name"/' composer.json
   sed -i '' 's/"description": "The skeleton application for the Laravel framework."/"description": "Your project description."/' composer.json

   # Optional: Add a name to package.json (if you want to publish to npm)
   sed -i '' '/"private": true,/a\
   \ \ \ \ "name": "your-project-name",' package.json
   ```

   > Replace `yourvendor/your-project-name` with your organization/username and project name (e.g., `acme/invoice-app`). The first part is your "vendor" name in Composer terminology, which identifies who created the package.
   > Replace `Your project description.` with your project description.
   > Replace `your-project-name` with your project name for package.json.

3. Install dependencies:
   ```bash
   make install
   ```

4. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

5. Update the .env file with your project details:
   ```bash
   # Update APP_NAME in .env file
   sed -i '' 's/APP_NAME=Laravel/APP_NAME="My Project Name"/' .env

   # Update DB_DATABASE in .env file (if using PostgreSQL or MySQL)
   sed -i '' 's/DB_DATABASE=laravel/DB_DATABASE=my_project_name/' .env
   ```

   > Replace `My Project Name` with your project's display name.
   > Replace `my_project_name` with your database name (use underscores instead of hyphens).

6. Generate application key:
   ```bash
   php artisan key:generate
   ```

7. Configure your database connection in the `.env` file:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   # DB_DATABASE should already be updated in step 5
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

8. Run migrations:
   ```bash
   make migrate
   ```

9. Start the application:
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
