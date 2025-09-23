# SkillUp Project Makefile

.PHONY: help init start stop build chown composer-install create-laravel-projects key-generate migrate clear-cache logs restart fix-permissions status test-connection fix-env clean rebuild ensure-containers-running jwt-secret

# Default target
help:
	@echo "Available commands:"
	@echo "  init          - Initialize project (build, chown, install dependencies)"
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

# Initialize project
init: build chown create-laravel-projects composer-install ensure-containers-running key-generate jwt-secret migrate
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

# Ensure containers are running before Laravel operations
ensure-containers-running:
	@echo "Ensuring containers are running..."
	@if ! docker-compose ps auth-service | grep -q "Up"; then \
		echo "Starting containers..."; \
		docker-compose up -d; \
		echo "Waiting for containers to be ready..."; \
		sleep 10; \
	else \
		echo "Containers are already running."; \
	fi

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
			echo "Installing dependencies for $$service..."; \
			docker-compose exec $$service bash -c "cd /var/www/html && composer install --no-interaction --prefer-dist --optimize-autoloader" || \
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
		echo "Generating key for $$service..."; \
		docker-compose exec $$service bash -c "cd /var/www/html && php artisan key:generate" || \
		(echo "Failed to generate key for $$service, trying to fix .env and retry..." && \
		 docker-compose exec $$service bash -c "cd /var/www/html && sed -i 's/APP_NAME=SkillUp Auth Service/APP_NAME=\\\"SkillUp Auth Service\\\"/' .env" && \
		 docker-compose exec $$service bash -c "cd /var/www/html && php artisan key:generate"); \
	done
	@echo "Laravel application keys generated!"

# Generate JWT secret key
jwt-secret:
	@echo "Generating JWT secret keys..."
	@for service in auth-service; do \
		echo "Generating JWT secret for $$service..."; \
		docker-compose exec $$service bash -c "cd /var/www/html && php artisan jwt:secret --force" || \
		(echo "Failed to generate JWT secret for $$service, trying to add manually..." && \
		 docker-compose exec $$service bash -c "cd /var/www/html && echo 'JWT_SECRET=' >> .env && php artisan jwt:secret --force"); \
	done
	@echo "JWT secret keys generated!"

# Run Laravel database migrations
migrate:
	@echo "Running Laravel database migrations..."
	@for service in auth-service; do \
		echo "Running migrations for $$service..."; \
		docker-compose exec $$service bash -c "cd /var/www/html && php artisan migrate" || \
		(echo "Migration failed for $$service, trying to fix permissions and retry..." && \
		 docker-compose exec $$service bash -c "cd /var/www/html && mkdir -p storage/logs && chmod -R 775 storage && chown -R www-data:www-data storage" && \
		 docker-compose exec $$service bash -c "cd /var/www/html && php artisan migrate"); \
	done
	@echo "Database migrations completed!"

# Clear Laravel cache
clear-cache:
	@echo "Clearing Laravel cache..."
	@for service in auth-service; do \
		if docker-compose ps $$service | grep -q "Up"; then \
			echo "Clearing cache for $$service..."; \
			docker-compose exec $$service bash -c "cd /var/www/html && php artisan cache:clear"; \
			docker-compose exec $$service bash -c "cd /var/www/html && php artisan view:clear"; \
			docker-compose exec $$service bash -c "cd /var/www/html && php artisan config:clear"; \
			docker-compose exec $$service bash -c "cd /var/www/html && php artisan route:clear"; \
		else \
			echo "$$service container is not running. Start containers first with: make start"; \
		fi; \
	done
	@echo "Laravel cache cleared!"

# Restart containers
restart:
	docker-compose restart
	@echo "Containers restarted!"

# Show container status
status:
	@echo "Container status:"
	docker-compose ps

# Show logs for all services
logs:
	@echo "Showing logs for all services (last 20 lines):"
	docker-compose logs --tail=20

# Fix permissions inside containers
fix-permissions:
	@echo "Fixing permissions inside containers..."
	@for service in auth-service; do \
		if docker-compose ps $$service | grep -q "Up"; then \
			echo "Fixing permissions for $$service..."; \
			docker-compose exec $$service bash -c "cd /var/www/html && mkdir -p storage/logs && chmod -R 775 storage && chown -R www-data:www-data storage"; \
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
	@for service in auth-service; do \
		if docker-compose ps $$service | grep -q "Up"; then \
			echo "Fixing .env file for $$service..."; \
			docker-compose exec $$service bash -c "cd /var/www/html && sed -i 's/APP_NAME=SkillUp Auth Service/APP_NAME=\\\"SkillUp Auth Service\\\"/' .env"; \
		else \
			echo "$$service container is not running. Start containers first with: make start"; \
		fi; \
	done
	@echo ".env file formatting fixed!"

# Clean up containers and volumes
clean:
	@echo "Cleaning up containers and volumes..."
	docker-compose down -v
	docker system prune -f
	@echo "Cleanup completed!"

# Complete rebuild
rebuild: clean init
	@echo "Complete rebuild finished!"