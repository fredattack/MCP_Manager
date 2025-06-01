# Makefile for EasyInvoice

# Variables
PORT=1978
PHP=php
COMPOSER=composer
NPM=npm
ARTISAN=$(PHP) artisan
PHPUNIT=./vendor/bin/phpunit
PHPSTAN=./vendor/bin/phpstan
RECTOR=./vendor/bin/rector
PRETTIER=npx prettier
ESLINT=npx eslint

# Commands
.PHONY: start
start:
	$(ARTISAN) serve --port=$(PORT)

.PHONY: dev
dev:
	$(NPM) run dev

.PHONY: start-all
start-all:
	$(MAKE) start & $(MAKE) dev

.PHONY: test
test:
	$(PHPUNIT)

.PHONY: lint
lint:
	$(ESLINT) --ext .js,.jsx,.ts,.tsx resources/js

.PHONY: stan
stan:
	$(PHPSTAN) analyse --level=max app

.PHONY: rector
rector:
	$(RECTOR) process app

.PHONY: coverage
coverage:
	XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html coverage

.PHONY: docs
docs:
	$(ARTISAN) scramble:generate

.PHONY: format
format:
	$(PRETTIER) --write "resources/js/**/*.{js,jsx,ts,tsx}"

.PHONY: quality-check
quality-check: test lint stan rector format
	@echo "All quality checks passed!"

.PHONY: install
install:
	$(COMPOSER) install
	$(NPM) install

.PHONY: rename-project
rename-project:
	@echo "Renaming project..."
	@echo "Usage: make rename-project VENDOR=your-organization NAME=your-project-name [DESCRIPTION=\"Your project description\"] [DISPLAY_NAME=\"Your Display Name\"] [DB_NAME=your_database_name]"
	@if [ -z "$(VENDOR)" ] || [ -z "$(NAME)" ]; then \
		echo "Error: VENDOR (your organization/username) and NAME parameters are required"; \
		echo "Example: make rename-project VENDOR=acme NAME=invoice-app"; \
		exit 1; \
	fi
	$(ARTISAN) app:rename $(VENDOR) $(NAME) $(if $(DESCRIPTION),--description="$(DESCRIPTION)") $(if $(DISPLAY_NAME),--display-name="$(DISPLAY_NAME)") $(if $(DB_NAME),--database-name="$(DB_NAME)")

.PHONY: migrate
migrate:
	$(ARTISAN) migrate

.PHONY: seed
seed:
	$(ARTISAN) db:seed

.PHONY: fresh
fresh:
	$(ARTISAN) migrate:fresh --seed

.PHONY: build
build:
	$(NPM) run build

.PHONY: help
help:
	@echo "Available commands:"
	@echo "  make start            - Start the app locally (port $(PORT))"
	@echo "  make dev              - Start the Vite development server"
	@echo "  make start-all        - Start both Laravel and Vite servers"
	@echo "  make build            - Build frontend assets for production"
	@echo "  make test             - Run all tests"
	@echo "  make lint             - Run ESLint"
	@echo "  make stan             - Run PHPStan"
	@echo "  make rector           - Run Rector"
	@echo "  make coverage         - Generate coverage reports"
	@echo "  make docs             - Generate API documentation"
	@echo "  make format           - Prettier format"
	@echo "  make quality-check    - Run all static checks"
	@echo "  make install          - Install dependencies"
	@echo "  make rename-project    - Rename the project (VENDOR=your-organization NAME=your-project-name required)"
	@echo "  make migrate          - Run migrations"
	@echo "  make seed             - Run seeders"
	@echo "  make fresh            - Fresh migration with seeding"
	@echo "  make help             - Show this help"
