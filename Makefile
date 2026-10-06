DC = docker compose
NO_TTY = $(if $(CI),-T)
EXEC = $(DC) exec $(NO_TTY)
RUN = $(DC) run $(NO_TTY) --rm
PHP = $(EXEC) php
CONSOLE = $(PHP) php bin/console
PLAYWRIGHT_ARGS ?=

.PHONY: up down build install assets assets-e2e db db-test fixtures migration test test-unit test-functional test-js deptrac phpstan cs cs-fix e2e e2e-run qa

up: ## Start the stack (app on http://localhost:8090, Vite on :5174, Mailpit on :8026)
	$(DC) up -d --wait php database node mailpit

down:
	$(DC) down

build:
	$(DC) build

install:
	$(PHP) composer install
	$(RUN) --no-deps node npm install

assets:
	$(RUN) --no-deps node npm run build

assets-e2e:
	$(RUN) --no-deps -e ASSETS_DIR=build-e2e node npm run build

db: ## Create and migrate the dev database
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration

fixtures: db ## Reset the dev database with demo data
	$(CONSOLE) doctrine:fixtures:load --no-interaction --purge-with-truncate

db-test: ## Create and migrate the test database
	$(CONSOLE) doctrine:database:create --if-not-exists --env=test
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test

migration: ## Generate a migration from mapping changes
	$(CONSOLE) doctrine:migrations:diff --no-interaction

test: db-test ## PHPUnit (unit + functional)
	$(PHP) php bin/phpunit

test-unit:
	$(PHP) php bin/phpunit --testsuite unit

test-functional: db-test
	$(PHP) php bin/phpunit --testsuite functional

test-js: ## Vitest (pure JS modules)
	$(RUN) --no-deps node npx vitest run

deptrac: ## Check onion layer dependencies
	$(PHP) vendor/bin/deptrac analyse --no-progress

cs: ## Coding standard check
	$(EXEC) -e PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix:
	$(EXEC) -e PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix

phpstan: ## Static analysis (level 10)
	$(PHP) vendor/bin/phpstan analyse --no-progress --memory-limit=1G

e2e: assets-e2e e2e-run ## Playwright against a dedicated APP_ENV=test container

e2e-run:
	$(DC) --profile e2e up -d --wait php-e2e
	$(EXEC) php-e2e php bin/console cache:clear --env=test
	$(EXEC) php-e2e php bin/console doctrine:database:drop --force --if-exists --env=test
	$(EXEC) php-e2e php bin/console doctrine:database:create --env=test
	$(EXEC) php-e2e php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test
	$(DC) --profile e2e run $(NO_TTY) --rm playwright sh -c "npm ci --no-audit --no-fund && ./node_modules/.bin/playwright test $(PLAYWRIGHT_ARGS)"

qa: cs phpstan deptrac test test-js e2e
