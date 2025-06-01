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

.PHONY: migrate
migrate:
	$(ARTISAN) migrate

.PHONY: seed
seed:
	$(ARTISAN) db:seed

.PHONY: fresh
fresh:
	$(ARTISAN) migrate:fresh --seed

.PHONY: help
help:
	@echo "Available commands:"
	@echo "  make start            - Start the app locally (port $(PORT))"
	@echo "  make test             - Run all tests"
	@echo "  make lint             - Run ESLint"
	@echo "  make stan             - Run PHPStan"
	@echo "  make rector           - Run Rector"
	@echo "  make coverage         - Generate coverage reports"
	@echo "  make docs             - Generate API documentation"
	@echo "  make format           - Prettier format"
	@echo "  make quality-check    - Run all static checks"
	@echo "  make install          - Install dependencies"
	@echo "  make migrate          - Run migrations"
	@echo "  make seed             - Run seeders"
	@echo "  make fresh            - Fresh migration with seeding"
	@echo "  make help             - Show this help"
