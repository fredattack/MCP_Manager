# EasyInvoice

A prototype automated system capable of connecting to a supplier portal, downloading invoices, storing them, applying OCR, and exporting data, with a user-friendly React interface and a minimum test coverage of 80%.

## 🚀 Getting Started

### Prerequisites

- PHP 8.4
- Composer
- Node.js 22
- npm
- PostgreSQL

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/fredattack/easyinvoice.git
   cd easyinvoice
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
   DB_DATABASE=easyinvoice
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. Run migrations:
   ```bash
   make migrate
   ```

7. Start the application:
   ```bash
   make start
   ```

## 🛠️ Development

### Available Commands

- `make start` - Start the app locally (port 1978)
- `make test` - Run all tests
- `make lint` - Run ESLint
- `make stan` - Run PHPStan
- `make rector` - Run Rector
- `make coverage` - Generate coverage reports
- `make docs` - Generate API documentation
- `make format` - Prettier format
- `make quality-check` - Run all static checks
- `make install` - Install dependencies
- `make migrate` - Run migrations
- `make seed` - Run seeders
- `make fresh` - Fresh migration with seeding
- `make help` - Show all available commands

### Code Quality

This project uses several tools to ensure code quality:

- **ESLint** - For JavaScript/TypeScript linting
- **Prettier** - For code formatting
- **PHPStan** - For PHP static analysis
- **Rector** - For PHP code quality and automatic refactoring
- **Pint** - For PHP code style

Pre-commit hooks are set up to run these tools automatically before each commit.

### Testing

- Run tests with `make test`
- Generate coverage reports with `make coverage`

## 📋 Project Structure

```
/app            # Laravel application code
/config         # Configuration files
/database       # Migrations, seeders, and factories
/public         # Publicly accessible files
/resources      # Frontend resources (React, CSS)
/routes         # API and web routes
/storage        # Application storage
/tests          # Test files
```

## 🧪 CI/CD

GitHub Actions workflows are set up to run tests and linting on each push to the main and develop branches, and on pull requests to these branches.

## 📝 License

This project is licensed under the MIT License.
