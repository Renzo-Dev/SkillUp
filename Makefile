# SkillUp Project Makefile

MAKEFLAGS += --warn-undefined-variables

COMPOSE ?= docker-compose
LARAVEL_SERVICES := auth-service subscription-service
CONTAINER_SERVICES := auth-service
PRIMARY_SERVICE := auth-service
FRONTEND_DIR := frontend
COMPOSER_IGNORE_PLATFORM := --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-sockets
HOST_UID := $(shell id -u)
HOST_GID := $(shell id -g)

.PHONY: help init start stop build chown composer-install ensure-env create-laravel-projects key-generate migrate clear-cache logs restart fix-permissions status test-connection fix-env clean rebuild ensure-containers-running jwt-secret frontend-install frontend-build frontend-dev frontend-logs npm-install wait-for-db

# Default target
help:
	@echo "Available commands:"
	@echo "  init          - Build images, start stack, prepare envs, install deps"
	@echo "  start         - Start all containers"
	@echo "  stop          - Stop all containers"
	@echo "  restart       - Restart all containers"
	@echo "  build         - Build Docker images"
	@echo "  status        - Show container status"
	@echo "  logs          - Show logs for all services"
	@echo "  key-generate  - Generate Laravel application key"
	@echo "  jwt-secret    - Generate JWT secret key"
	@echo "  migrate       - Run Laravel database migrations"
	@echo "  clear-cache   - Clear Laravel cache (views, config, routes)"
	@echo "  fix-permissions - Fix file permissions in containers"
	@echo "  fix-env         - Fix .env file formatting issues"
	@echo "  test-connection - Test all service connections"
	@echo "  clean          - Clean up containers and volumes"
	@echo "  rebuild         - Complete rebuild (clean + init)"
	@echo ""
	@echo "Frontend commands:"
	@echo "  frontend-install - Install frontend dependencies"
	@echo "  frontend-build   - Build frontend for production"
	@echo "  frontend-dev     - Start frontend in development mode"
	@echo "  frontend-logs    - Show frontend container logs"

# Initialize project
init: build start ensure-env composer-install npm-install wait-for-db key-generate jwt-secret migrate
	@echo "Project initialized successfully!"

# Start containers
start:
	$(COMPOSE) up -d
	@echo "Containers started!"

# Stop containers
stop:
	$(COMPOSE) down
	@echo "Containers stopped!"

# Build Docker images
build:
	$(COMPOSE) build --no-cache
	@echo "Docker images built!"

# Start containers first (needed for Laravel project creation)
start-containers:
	$(COMPOSE) up -d
	@echo "Containers started for Laravel project creation!"

# Ensure containers are running before Laravel operations
ensure-containers-running:
	@echo "Ensuring containers are running..."
	@if ! $(COMPOSE) ps $(PRIMARY_SERVICE) | grep -q "Up"; then \
		echo "Starting containers..."; \
		$(COMPOSE) up -d; \
		echo "Waiting for containers to be ready..."; \
		sleep 10; \
	else \
		echo "Containers are already running."; \
	fi

# Ensure .env files are present for Laravel services
ensure-env:
	@echo "Ensuring .env files exist for Laravel services..."
	@for service in $(LARAVEL_SERVICES); do \
		if [ -d "./services/$$service/src" ]; then \
			if [ ! -f "./services/$$service/src/.env" ]; then \
				if [ -f "./services/$$service/src/.env.example" ]; then \
					cp "./services/$$service/src/.env.example" "./services/$$service/src/.env"; \
					echo "Created .env for $$service"; \
				else \
					echo "⚠️  Skipping $$service: .env.example not found"; \
				fi; \
			else \
				echo "✓ $$service: .env already exists"; \
			fi; \
		fi; \
	done

# Create Laravel projects if they don't exist
create-laravel-projects: start-containers
	@echo "Checking and creating Laravel projects..."
	@for service in $(CONTAINER_SERVICES); do \
		if [ ! -f "./services/$$service/src/composer.json" ]; then \
			echo "Creating Laravel project for $$service in src/..."; \
			$(COMPOSE) exec $$service composer create-project laravel/laravel src --prefer-dist --no-interaction; \
		else \
			echo "✓ $$service: Laravel project already exists in src/"; \
		fi; \
	done
	@echo "Laravel projects checked/created!"


# Install PHP dependencies for Laravel services (internal target)
composer-install:
	@echo "Installing PHP dependencies for Laravel services..."
	@for service in $(LARAVEL_SERVICES); do \
		if [ -d "./services/$$service/src" ]; then \
			if $(COMPOSE) config --services 2>/dev/null | grep -qx "$$service" && $(COMPOSE) ps $$service 2>/dev/null | grep -q "Up"; then \
				echo "Installing dependencies for $$service via running container..."; \
				$(COMPOSE) exec $$service bash -lc "cd /var/www/html && composer install --no-interaction --prefer-dist --optimize-autoloader"; \
			else \
				echo "Installing dependencies for $$service via composer image..."; \
				docker run --rm -v $(PWD)/services/$$service/src:/app composer:latest install --no-interaction --prefer-dist --optimize-autoloader $(COMPOSER_IGNORE_PLATFORM); \
			fi; \
		fi; \
	done
	@echo "PHP dependencies installed!"

# Install frontend dependencies if project exists
npm-install:
	@if [ -d "$(FRONTEND_DIR)" ]; then \
		echo "Installing frontend dependencies inside container..."; \
		if $(COMPOSE) ps frontend 2>/dev/null | grep -q "Up"; then \
			$(COMPOSE) exec frontend sh -lc "npm install -g npm@11.6.1"; \
			$(COMPOSE) exec frontend sh -lc 'rm -f package-lock.json && if [ -d node_modules ]; then find node_modules -mindepth 1 -maxdepth 1 -exec rm -rf {} +; else mkdir -p node_modules; fi && chown -R $(HOST_UID):$(HOST_GID) /app node_modules 2>/dev/null || true'; \
			$(COMPOSE) exec --user $(HOST_UID):$(HOST_GID) frontend sh -lc "npm install"; \
		else \
			echo "Frontend container is not running. Starting frontend service..."; \
			$(COMPOSE) up -d frontend; \
			sleep 5; \
			$(COMPOSE) exec frontend sh -lc "npm install -g npm@11.6.1"; \
			$(COMPOSE) exec frontend sh -lc 'rm -f package-lock.json && if [ -d node_modules ]; then find node_modules -mindepth 1 -maxdepth 1 -exec rm -rf {} +; else mkdir -p node_modules; fi && chown -R $(HOST_UID):$(HOST_GID) /app node_modules 2>/dev/null || true'; \
			$(COMPOSE) exec --user $(HOST_UID):$(HOST_GID) frontend sh -lc "npm install"; \
		fi; \
	else \
		echo "Skipping frontend dependencies: $(FRONTEND_DIR) not found"; \
	fi

# Fix file permissions for all services (internal target)
chown:
	@echo "Fixing file permissions for all services..."
	@for service in $(LARAVEL_SERVICES); do \
		if [ -d "./services/$$service/src" ]; then \
			echo "Setting permissions for $$service..."; \
			chown -R $(shell whoami):$(shell whoami) ./services/$$service/src 2>/dev/null || true; \
			chmod -R 755 ./services/$$service/src 2>/dev/null || true; \
			echo "Setting Laravel storage permissions for $$service..."; \
			chmod -R 775 ./services/$$service/src/storage 2>/dev/null || true; \
			chmod -R 775 ./services/$$service/src/bootstrap/cache 2>/dev/null || true; \
			echo "Setting Laravel views permissions for $$service..."; \
			chmod -R 775 ./services/$$service/src/storage/framework/views 2>/dev/null || true; \
		fi; \
	done
	@echo "File permissions fixed!";


# Generate Laravel application key
key-generate:
	@echo "Generating Laravel application keys..."
	@for service in $(CONTAINER_SERVICES); do \
		echo "Generating key for $$service..."; \
		$(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan key:generate" || \
		(echo "Failed to generate key for $$service, trying to fix .env and retry..." && \
		 $(COMPOSE) exec $$service bash -c "cd /var/www/html && sed -i 's/APP_NAME=SkillUp Auth Service/APP_NAME=\\\"SkillUp Auth Service\\\"/' .env" && \
		 $(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan key:generate"); \
	done
	@echo "Laravel application keys generated!"

# Generate JWT secret key
jwt-secret:
	@echo "Generating JWT secret keys..."
	@for service in $(CONTAINER_SERVICES); do \
		echo "Generating JWT secret for $$service..."; \
		$(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan jwt:secret --force" || \
		(echo "Failed to generate JWT secret for $$service, trying to add manually..." && \
		 $(COMPOSE) exec $$service bash -c "cd /var/www/html && echo 'JWT_SECRET=' >> .env && php artisan jwt:secret --force"); \
	done
	@echo "JWT secret keys generated!"

# Run Laravel database migrations
migrate:
	@echo "Running Laravel database migrations..."
	@for service in $(CONTAINER_SERVICES); do \
		echo "Running migrations for $$service..."; \
		$(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan migrate" || \
		(echo "Migration failed for $$service, trying to fix permissions and retry..." && \
		 $(COMPOSE) exec $$service bash -c "cd /var/www/html && mkdir -p storage/logs && chmod -R 775 storage && chown -R www-data:www-data storage" && \
		 $(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan migrate"); \
	done
	@echo "Database migrations completed!"

# Wait until PostgreSQL is ready to accept connections
wait-for-db:
	@echo "Waiting for PostgreSQL to become ready..."
	@for i in $$(seq 1 15); do \
		if $(COMPOSE) exec -T postgres pg_isready >/dev/null 2>&1; then \
			echo "PostgreSQL is ready."; \
			exit 0; \
		fi; \
		sleep 2; \
	done; \
	echo "PostgreSQL did not become ready in time." >&2; \
	exit 1

# Clear Laravel cache
clear-cache:
	@echo "Clearing Laravel cache..."
	@for service in $(CONTAINER_SERVICES); do \
		if $(COMPOSE) ps $$service | grep -q "Up"; then \
			echo "Clearing cache for $$service..."; \
			$(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan cache:clear"; \
			$(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan view:clear"; \
			$(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan config:clear"; \
			$(COMPOSE) exec $$service bash -c "cd /var/www/html && php artisan route:clear"; \
		else \
			echo "$$service container is not running. Start containers first with: make start"; \
		fi; \
	done
	@echo "Laravel cache cleared!"

# Restart containers
restart:
	$(COMPOSE) restart
	@echo "Containers restarted!"

# Show container status
status:
	@echo "Container status:"
	$(COMPOSE) ps

# Show logs for all services
logs:
	@echo "Showing logs for all services (last 20 lines):"
	$(COMPOSE) logs --tail=20

# Fix permissions inside containers
fix-permissions:
	@echo "Fixing permissions inside containers..."
	@for service in $(CONTAINER_SERVICES); do \
		if $(COMPOSE) ps $$service | grep -q "Up"; then \
			echo "Fixing permissions for $$service..."; \
			$(COMPOSE) exec $$service bash -c "cd /var/www/html && mkdir -p storage/logs && chmod -R 775 storage && chown -R www-data:www-data storage"; \
		else \
			echo "$$service container is not running. Start containers first with: make start"; \
		fi; \
	done
	@echo "Permissions fixed inside containers!"

# Test all service connections
test-connection:
	@echo "Testing service connections..."
	@echo "Testing nginx (port 80)..."
	@curl -I http://localhost 2>/dev/null | head -1 || echo "❌ Nginx not accessible"
	@echo "Testing frontend (port 3000)..."
	@curl -I http://localhost:3000 2>/dev/null | head -1 || echo "❌ Frontend not accessible"
	@echo "Testing auth-service (port 9000)..."
	@curl -I http://localhost:9000 2>/dev/null | head -1 || echo "❌ Auth-service not accessible"
	@echo "Testing RabbitMQ management (port 15672)..."
	@curl -I http://localhost:15672 2>/dev/null | head -1 || echo "❌ RabbitMQ management not accessible"
	@echo "Testing PostgreSQL (port 5432)..."
	@nc -z localhost 5432 2>/dev/null && echo "✅ PostgreSQL accessible" || echo "❌ PostgreSQL not accessible"
	@echo "Testing Redis (port 6379)..."
	@nc -z localhost 6379 2>/dev/null && echo "✅ Redis accessible" || echo "❌ Redis not accessible"
	@echo "Connection tests completed!"

# Fix .env file formatting issues
fix-env:
	@echo "Fixing .env file formatting issues..."
	@for service in $(CONTAINER_SERVICES); do \
		if $(COMPOSE) ps $$service | grep -q "Up"; then \
			echo "Fixing .env file for $$service..."; \
			$(COMPOSE) exec $$service bash -c "cd /var/www/html && sed -i 's/APP_NAME=SkillUp Auth Service/APP_NAME=\\\"SkillUp Auth Service\\\"/' .env"; \
		else \
			echo "$$service container is not running. Start containers first with: make start"; \
		fi; \
	done
	@echo ".env file formatting fixed!"

# Clean up containers and volumes
clean:
	@echo "Cleaning up containers and volumes..."
	$(COMPOSE) down -v
	docker system prune -f
	@echo "Cleanup completed!"

# Complete rebuild
rebuild: clean init
	@echo "Complete rebuild finished!"

# Frontend commands
frontend-install:
	@echo "Installing frontend dependencies..."
	cd $(FRONTEND_DIR) && npm install
	@echo "Frontend dependencies installed!"

frontend-build:
	@echo "Building frontend for production..."
	cd $(FRONTEND_DIR) && npm run build
	@echo "Frontend built successfully!"

frontend-dev:
	@echo "Starting frontend in development mode..."
	cd $(FRONTEND_DIR) && npm run dev

frontend-logs:
	@echo "Showing frontend container logs:"
	$(COMPOSE) logs -f frontend