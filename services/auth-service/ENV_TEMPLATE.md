# Environment Variables Template

Скопируйте содержимое в `.env` файл:

```env
# ==============================================================================
# AUTH-SERVICE ENVIRONMENT CONFIGURATION
# ==============================================================================

# ------------------------------------------------------------------------------
# Application
# ------------------------------------------------------------------------------
APP_NAME="SkillUp Auth Service"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000
APP_LOCALE=ru
APP_FALLBACK_LOCALE=ru
APP_FAKER_LOCALE=ru_RU

# Frontend URL для ссылок верификации email
FRONTEND_URL=http://localhost:3000

# ------------------------------------------------------------------------------
# Database (PostgreSQL)
# ------------------------------------------------------------------------------
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=skillup_auth
DB_USERNAME=skillup
DB_PASSWORD=secret

# ------------------------------------------------------------------------------
# Redis (Cache, Queue, JWT Blacklist)
# ------------------------------------------------------------------------------
REDIS_CLIENT=predis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0

CACHE_DRIVER=redis
CACHE_PREFIX=auth_cache

# ------------------------------------------------------------------------------
# Queue
# ------------------------------------------------------------------------------
QUEUE_CONNECTION=redis

# ------------------------------------------------------------------------------
# JWT Configuration (RS256 - Asymmetric)
# ------------------------------------------------------------------------------
# Алгоритм подписи: RS256 (рекомендуется для микросервисов) или HS256 (симметричный)
JWT_ALGO=RS256

# Secret для HS256 (используется только если JWT_ALGO=HS256)
JWT_SECRET=

# RSA ключи для RS256 (путь к файлам)
# Генерация: php artisan jwt:generate-keys
JWT_PUBLIC_KEY=file:///var/www/html/storage/jwt/public.pem
JWT_PRIVATE_KEY=file:///var/www/html/storage/jwt/private.pem
JWT_PASSPHRASE=

# Время жизни токенов (в минутах)
JWT_TTL=60                    # Access token: 1 час
JWT_REFRESH_TTL=10080         # Refresh token: 7 дней (7 * 24 * 60)

# JWT Blacklist
JWT_BLACKLIST_ENABLED=true
JWT_BLACKLIST_GRACE_PERIOD=0

# JWT Metadata Cache (Redis)
JWT_CACHE_STORE=redis
JWT_CACHE_PREFIX=auth:jwt
JWT_CACHE_MIN_TTL=5

# ------------------------------------------------------------------------------
# RabbitMQ (Event Bus)
# ------------------------------------------------------------------------------
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=skillup
RABBITMQ_PASSWORD=secret
RABBITMQ_VHOST=/

# Очереди событий
RABBITMQ_QUEUE_USER_EVENTS=user.events
RABBITMQ_QUEUE_EMAIL_VERIFICATION=email.verification

# ------------------------------------------------------------------------------
# Logging
# ------------------------------------------------------------------------------
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ------------------------------------------------------------------------------
# Email Verification
# ------------------------------------------------------------------------------
# Время жизни токена верификации email (в часах)
EMAIL_VERIFICATION_TOKEN_TTL=24

# ------------------------------------------------------------------------------
# Internal API Security
# ------------------------------------------------------------------------------
# Ключ для валидации внутренних запросов от Gateway
INTERNAL_JWT_VALIDATE_KEY=

# Whitelist IP адресов для internal endpoints (через запятую)
INTERNAL_ALLOWED_IPS=

# ------------------------------------------------------------------------------
# Session (не используется в API-сервисе, но требуется Laravel)
# ------------------------------------------------------------------------------
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ------------------------------------------------------------------------------
# Mail (не используется напрямую, email через RabbitMQ)
# ------------------------------------------------------------------------------
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_FROM_ADDRESS="noreply@skillup.local"
MAIL_FROM_NAME="${APP_NAME}"

# ------------------------------------------------------------------------------
# Services URLs (для межсервисной коммуникации)
# ------------------------------------------------------------------------------
SERVICE_SUBSCRIPTION_URL=http://subscription-service:8001
SERVICE_PAYMENT_URL=http://payment-service:8002
SERVICE_NOTIFICATION_URL=http://notification-service:8003
SERVICE_AI_URL=http://ai-service:8004

# ------------------------------------------------------------------------------
# Monitoring
# ------------------------------------------------------------------------------
HEALTH_CHECK_ENABLED=true
RATE_LIMIT_PER_MINUTE=60
```

## Инициализация

1. **Скопировать template в .env:**
   ```bash
   cp ENV_TEMPLATE.md .env
   ```

2. **Сгенерировать APP_KEY:**
   ```bash
   php artisan key:generate
   ```

3. **Сгенерировать RSA ключи:**
   ```bash
   php artisan jwt:generate-keys
   ```

4. **Запустить миграции:**
   ```bash
   php artisan migrate
   ```

