# Simplified SkillUp Makefile

COMPOSE ?= docker compose
ENV_SERVICES := auth-service subscription-service
LARAVEL_SERVICES := auth-service
FRONTEND_SERVICE := frontend
FRONTEND_DIR := frontend
HOST_UID := $(shell id -u)
HOST_GID := $(shell id -g)

.PHONY: init start stop clean check-frontend

init:
	@echo ">>> Building Docker images"
	$(COMPOSE) build --pull
	@echo ">>> Preparing environment files"
	@for service in $(ENV_SERVICES); do \
		if [ -d "./services/$$service/src" ]; then \
			if [ ! -f "./services/$$service/src/.env" ] && [ -f "./services/$$service/src/.env.example" ]; then \
				cp "./services/$$service/src/.env.example" "./services/$$service/src/.env"; \
				echo "Created .env for $$service"; \
			fi; \
		fi; \
	done
	-@docker volume rm skillup_node_modules >/dev/null 2>&1 || true
	@echo ">>> Starting compose stack"
	$(COMPOSE) up -d
	@sleep 5
	@echo ">>> Installing composer dependencies"
	@for service in $(LARAVEL_SERVICES); do \
		$(COMPOSE) exec $$service bash -lc "cd /var/www/html && composer install --no-interaction --prefer-dist --optimize-autoloader"; \
	done
	@echo ">>> Waiting for PostgreSQL"
	@for i in $$(seq 1 15); do \
		if $(COMPOSE) exec -T postgres pg_isready >/dev/null 2>&1; then \
			break; \
		fi; \
		if [ $$i -eq 15 ]; then \
			echo "PostgreSQL is still unavailable"; \
			exit 1; \
		fi; \
		sleep 2; \
	done
	@echo ">>> Running artisan setup"
	@for service in $(LARAVEL_SERVICES); do \
		$(COMPOSE) exec $$service bash -lc "cd /var/www/html && php artisan key:generate --force && php artisan jwt:secret --force && php artisan migrate --force"; \
	done
	@echo ">>> Frontend container will auto-run npm install && npm run dev"
	@echo ">>> Fixing file ownership"
	@chown -R $(HOST_UID):$(HOST_GID) ./frontend ./services 2>/dev/null || true
	@echo ">>> Init completed"

start:
	$(COMPOSE) up -d

stop:
	$(COMPOSE) down

clean:
	$(COMPOSE) down -v
	-@docker volume rm skillup_node_modules >/dev/null 2>&1 || true
	docker system prune -f
	@chown -R $(HOST_UID):$(HOST_GID) ./frontend ./services 2>/dev/null || true

check-frontend:
	@echo ">>> Installing frontend dependencies"
	@if [ -f "./frontend/src/package.json" ]; then \
		echo "Installing npm dependencies in frontend container"; \
		$(COMPOSE) exec frontend npm install; \
		echo "Starting Nuxt dev server"; \
		$(COMPOSE) exec -d frontend npm run dev; \
	else \
		echo "No package.json found in frontend/src"; \
	fi