# SkillUp Project Makefile

.PHONY: help init start stop build chown composer-install create-laravel-projects copy-env

# Default target
help:
	@echo "Available commands:"
	@echo "  init          - Initialize project (build, chown, install dependencies)"
	@echo "  start         - Start all containers"
	@echo "  stop          - Stop all containers"
	@echo "  build         - Build Docker images"

# Initialize project
init: build chown create-laravel-projects composer-install copy-env
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
	@for service in auth-service subscription-service payment-service ai-service content-service voice-service file-service notification-service; do \
		if [ ! -f "./services/$$service/composer.json" ]; then \
			echo "Creating Laravel project for $$service..."; \
			docker-compose exec $$service composer create-project laravel/laravel . --prefer-dist --no-interaction; \
		else \
			echo "✓ $$service: Laravel project already exists"; \
		fi; \
	done
	@echo "Laravel projects checked/created!"

# Copy .env file to Laravel services
copy-env:
	@echo "Copying .env file to Laravel services..."
	@for service in auth-service subscription-service payment-service ai-service content-service voice-service file-service notification-service; do \
		if [ -d "./services/$$service" ]; then \
			echo "Copying .env to $$service..."; \
			cp .env ./services/$$service/.env; \
		fi; \
	done
	@echo ".env files copied to all services!"

# Install PHP dependencies for Laravel services (internal target)
composer-install:
	@echo "Installing PHP dependencies for Laravel services..."
	@for service in auth-service subscription-service payment-service ai-service content-service voice-service file-service notification-service; do \
		if [ -d "./services/$$service" ]; then \
			echo "Installing dependencies for $$service..."; \
			docker-compose exec $$service composer install --no-interaction --prefer-dist --optimize-autoloader || \
			docker run --rm -v $(PWD)/services/$$service:/app composer:latest install --no-interaction --prefer-dist --optimize-autoloader; \
		fi; \
	done
	@echo "PHP dependencies installed!"

# Fix file permissions for all services (internal target)
chown:
	@echo "Fixing file permissions for all services..."
	@for service in auth-service subscription-service payment-service ai-service content-service voice-service file-service notification-service; do \
		if [ -d "./services/$$service" ]; then \
			echo "Setting permissions for $$service..."; \
			sudo chown -R $(shell whoami):$(shell whoami) ./services/$$service; \
			sudo chmod -R 755 ./services/$$service; \
		fi; \
	done
	@echo "File permissions fixed!";