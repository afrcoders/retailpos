.PHONY: help install dev up down logs shell mysql redis fresh migrate seed test lint format cache clear

help:
	@echo "OSPOS - Open Source Point of Sale"
	@echo ""
	@echo "Usage: make [target]"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install and setup application
	cp -n .env.example .env || true
	docker compose build
	docker compose up -d
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed
	@echo "\n✅ OSPOS installed! Access at http://localhost:8080"

dev: ## Start development environment
	docker compose up -d

up: ## Start containers
	docker compose up -d

down: ## Stop containers
	docker compose down

logs: ## View logs
	docker compose logs -f

shell: ## Open shell in app container
	docker compose exec app sh

mysql: ## Open MySQL CLI
	docker compose exec mysql mysql -u ospos -psecret ospos

redis: ## Open Redis CLI
	docker compose exec redis redis-cli

fresh: ## Fresh migrate with seeders
	docker compose exec app php artisan migrate:fresh --seed

migrate: ## Run migrations
	docker compose exec app php artisan migrate

seed: ## Run seeders
	docker compose exec app php artisan db:seed

test: ## Run tests
	docker compose exec app php artisan test

lint: ## Check code style
	docker compose exec app ./vendor/bin/pint --test

format: ## Fix code formatting
	docker compose exec app ./vendor/bin/pint

cache: ## Optimize for production
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache

clear: ## Clear all caches
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear
