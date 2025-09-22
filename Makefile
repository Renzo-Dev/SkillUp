# SkillUp Project Makefile

.PHONY: help init start stop build chown composer-install create-laravel-projects key-generate migrate clear-cache

# Default target
help:
	@echo "Available commands:"
	@echo "  init          - Initialize project (build, chown, install dependencies)"
	@echo "  start         - Start all containers"
	@echo "  stop          - Stop all containers"
	@echo "  build         - Build Docker images"
	@echo "  key-generate  - Generate Laravel application key"
	@echo "  migrate       - Run Laravel database migrations"
	@echo "  clear-cache   - Clear Laravel cache (views, config, routes)"

# Initialize project
init: build chown create-laravel-projects composer-install key-generate migrate
	@echo "Project initialized successfully!"

# Start containers
start:
	docker-compose up -d
	@echo "Containers started!"

# Stop containers
stop:
	docker-compose down
	@echo "Containers stopped!"

# Build Docker images
build:
	docker-compose build --no-cache
	@echo "Docker images built!"

# Start containers first (needed for Laravel project creation)
start-containers:
	docker-compose up -d
	@echo "Containers started for Laravel project creation!"

# Create Laravel projects if they don't exist
create-laravel-projects: start-containers
	@echo "Checking and creating Laravel projects..."
	@for service in auth-service; do \
		if [ ! -f "./services/$$service/src/composer.json" ]; then \
			echo "Creating Laravel project for $$service in src/..."; \
			docker-compose exec $$service composer create-project laravel/laravel src --prefer-dist --no-interaction; \
		else \
			echo "✓ $$service: Laravel project already exists in src/"; \
		fi; \
	done
	@echo "Laravel projects checked/created!"


# Install PHP dependencies for Laravel services (internal target)
composer-install:
	@echo "Installing PHP dependencies for Laravel services..."
	@for service in auth-service; do \
		if [ -d "./services/$$service/src" ]; then \
			echo "Installing dependencies for $$service in src/..."; \
			docker-compose exec $$service bash -c "cd src && composer install --no-interaction --prefer-dist --optimize-autoloader" || \
			docker run --rm -v $(PWD)/services/$$service/src:/app composer:latest install --no-interaction --prefer-dist --optimize-autoloader; \
		fi; \
	done
	@echo "PHP dependencies installed!"

# Fix file permissions for all services (internal target)
chown:
	@echo "Fixing file permissions for all services..."
	@for service in auth-service; do \
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
	@for service in auth-service; do \
		if docker-compose ps $$service | grep -q "Up"; then \
			echo "Generating key for $$service..."; \
			docker-compose exec $$service php artisan key:generate; \
		else \
			echo "$$service container is not running. Start containers first with: make start"; \
		fi; \
	done
	@echo "Laravel application keys generated!"

# Run Laravel database migrations
migrate:
	@echo "Running Laravel database migrations..."
	@for service in auth-service; do \
		if docker-compose ps $$service | grep -q "Up"; then \
			echo "Running migrations for $$service..."; \
			docker-compose exec $$service php artisan migrate; \
		else \
			echo "$$service container is not running. Start containers first with: make start"; \
		fi; \
	done
	@echo "Database migrations completed!"

# Clear Laravel cache
clear-cache:
	@echo "Clearing Laravel cache..."
	@for service in auth-service; do \
		if docker-compose ps $$service | grep -q "Up"; then \
			echo "Clearing cache for $$service..."; \
			docker-compose exec $$service php artisan cache:clear; \
			docker-compose exec $$service php artisan view:clear; \
			docker-compose exec $$service php artisan config:clear; \
			docker-compose exec $$service php artisan route:clear; \
		else \
			echo "$$service container is not running. Start containers first with: make start"; \
		fi; \
	done
	@echo "Laravel cache cleared!"