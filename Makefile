# Makefile for Laravel LMS (Docker: app/web/worker/scheduler/node)

# Default services
APP=app
WEB=web
NODE=node

# Helpers
EXEC_APP=docker compose exec $(APP) sh -lc
RUN_NODE=docker compose run --rm $(NODE) sh -lc

.PHONY: up build down restart ps logs sh-app sh-web sh-node \
        install key storage perms sqlite migrate seed fresh reset db \
        cache clear optimize tinker queue schedule test \
        vite-dev vite-build vite-preview \
        prepare all setup

## --- Orchestration ---
up:
	@docker compose up -d --build

build:
	@docker compose build --no-cache

down:
	@docker compose down

restart:
	@docker compose down && docker compose up -d

ps:
	@docker compose ps

logs:
	@docker compose logs -f $(APP)

sh-app:
	@docker compose exec $(APP) sh

sh-web:
	@docker compose exec $(WEB) sh

sh-node:
	@docker compose exec $(NODE) sh

## --- Project setup ---
install:
	@$(EXEC_APP) "composer install --no-interaction --prefer-dist"
	@$(RUN_NODE) "npm ci"

key:
	@$(EXEC_APP) "php artisan key:generate || true"

storage:
	@$(EXEC_APP) "php artisan storage:link || true"

perms:
	@$(EXEC_APP) "mkdir -p database storage/logs bootstrap/cache && chown -R www-data:www-data database storage bootstrap/cache"

sqlite:
	@$(EXEC_APP) "mkdir -p database && touch database/database.sqlite && chown -R www-data:www-data database"

## --- Database ---
migrate:
	@$(EXEC_APP) "php artisan migrate"

seed:
	@$(EXEC_APP) "php artisan db:seed"

fresh:
	@$(EXEC_APP) "php artisan migrate:fresh --seed"

reset: ## non-destructive reset (fallback style)
	@$(EXEC_APP) "php artisan migrate --seed"

db: sqlite migrate seed

## --- Cache / Optimize ---
cache:
	@$(EXEC_APP) "php artisan optimize"

clear:
	@$(EXEC_APP) "php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear"

optimize: clear cache

## --- Runtime tools ---
tinker:
	@$(EXEC_APP) "php artisan tinker"

queue:
	@docker compose logs -f worker

schedule:
	@docker compose logs -f scheduler

test:
	@$(EXEC_APP) "./vendor/bin/phpunit"

## --- Vite / Frontend ---
vite-dev:
	@docker compose up -d $(NODE)

vite-build:
	@$(RUN_NODE) "npm ci && npm run build"

vite-preview:
	@$(RUN_NODE) "npm run preview"

## --- One-shot Prep & All-in ---
prepare: perms sqlite install key storage
	@echo "✔ prepare done"

all: up prepare migrate seed vite-build optimize
	@echo "✔ all done -> http://localhost:8000"

setup: ## Full setup like your script, idempotent
	@docker compose up -d --build
	@$(EXEC_APP) "mkdir -p database storage/logs bootstrap/cache && touch database/database.sqlite && chown -R www-data:www-data database storage bootstrap/cache"
	@$(EXEC_APP) "composer install --no-interaction --prefer-dist"
	@$(EXEC_APP) "php artisan key:generate || true"
	@$(EXEC_APP) "php artisan storage:link || true"
	@$(EXEC_APP) "php artisan queue:table || true"
	@$(EXEC_APP) "php artisan notifications:table || true"
	@$(EXEC_APP) "php artisan migrate:fresh --seed || php artisan migrate --seed"
	@$(RUN_NODE) "npm ci && npm run build"
	@$(EXEC_APP) "rm -f public/hot && php artisan view:clear && php artisan route:clear && php artisan config:clear"

	@$(EXEC_APP) "php artisan optimize || true"
	@echo "✔ setup complete -> http://localhost:8000"