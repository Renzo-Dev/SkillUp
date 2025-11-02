# Auth Service — Техническое задание

## 1. Назначение

**Auth Service** — центральный микросервис аутентификации и авторизации платформы SkillUp.

### Основные задачи:

- Регистрация и аутентификация пользователей
- Генерация и валидация JWT токенов (RS256)
- Управление refresh токенами
- Email верификация
- Управление сессиями и blacklist
- Предоставление Internal API для API Gateway

## 2. Технический стек

### Backend

- **Laravel 12** (PHP 8.2+)
- **firebase/php-jwt** - JWT библиотека
- **PostgreSQL 16** - основная БД
- **Redis 7** - кеш, blacklist, метаданные JWT
- **RabbitMQ 3.13** - событийная шина
- **Docker** - контейнеризация

### Архитектурные паттерны

- **Repository Pattern** - абстракция работы с данными
- **Service Layer** - бизнес-логика
- **DTO Pattern** - передача данных между слоями
- **Dependency Injection** - слабая связность
- **Event-Driven** - асинхронная коммуникация через RabbitMQ

## 3. Архитектура

```
┌─────────────────────────────────────────────────────────────┐
│                      HTTP Request                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                    Controllers Layer                        │
│  AuthController, EmailVerificationController               │
│  Internal: JwtValidationController, JwtPublicKeyController │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                      DTO Layer                              │
│  LoginRequestDTO, RegisterRequestDTO, AuthResponseDTO      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                    Services Layer                           │
│  AuthService, JwtService, TokenService,                    │
│  EmailVerificationService, BlackListService                │
└────────────┬────────────────────────┬───────────────────────┘
             │                        │
             ▼                        ▼
┌────────────────────┐    ┌──────────────────────────────────┐
│  Repositories      │    │   Infrastructure Services        │
│  UserRepository    │    │   RabbitMQService               │
│  RefreshToken...   │    │   CustomLoggerService           │
└─────────┬──────────┘    │   JwtMetadataCacheService       │
          │               └──────────────────────────────────┘
          ▼
┌─────────────────────────────────────────────────────────────┐
│                     Models Layer                            │
│              User, RefreshToken, etc.                       │
└────────────────────────┬────────────────────────────────────┘
                         │
        ┌────────────────┴────────────────┐
        ▼                                 ▼
┌──────────────┐                  ┌──────────────┐
│  PostgreSQL  │                  │    Redis     │
└──────────────┘                  └──────────────┘
```

## 4. Основные компоненты

### 4.1. Controllers

#### AuthController

- `POST /api/auth/register` - регистрация
- `POST /api/auth/login` - аутентификация
- `POST /api/auth/refresh` - обновление токенов
- `POST /api/auth/logout` - выход (blacklist)
- `GET /api/auth/me` - данные текущего пользователя

#### EmailVerificationController

- `POST /api/auth/verify-email` - подтверждение email
- `POST /api/auth/resend-verification` - повтор отправки
- `GET /api/auth/verification-status` - статус верификации

#### Internal Controllers

- `GET /api/auth/internal/jwt/validate` - валидация JWT для Gateway
- `GET /api/auth/internal/jwt/public-key` - получение публичного ключа
- `GET /api/auth/internal/health` - health check

### 4.2. Services

#### AuthService

**Контракт**: `AuthServiceInterface`

Основная бизнес-логика аутентификации:

```php
public function register(RegisterRequestDTO $dto): ?array
public function login(LoginRequestDTO $dto): ?AuthResponseDTO
public function refreshToken(string $token): ?AuthResponseDTO
public function me(): ?UserDTO
public function logout(string $token): bool
```

#### JwtService

**Контракт**: `JwtServiceInterface`

Управление JWT токенами:

```php
public function generate(User $user): string
public function validate(string $token): array
public function decode(string $token): array
public function revoke(string $token): void
public function getPublicKey(): string
public function getPublicKeyPath(): string
```

**Реализация**:

- Использует `JwtManager` (обертка над firebase/php-jwt)
- Алгоритм: RS256 (4096 bit)
- TTL: 60 минут
- Хранение метаданных в Redis

#### TokenService

**Контракт**: `TokenServiceInterface`

Управление парами токенов (access + refresh):

```php
public function generateTokenPair(User $user): array
public function refreshTokenPair(string $refreshToken): ?array
public function revokeToken(string $token): bool
```

#### BlackListService

**Контракт**: `BlackListServiceInterface`

JWT blacklist через Redis:

```php
public function addToBlackList(string $token): void
public function checkTokenInBlackList(string $token): bool
public function removeFromBlackList(string $token): void
```

**Реализация**:

- TTL = оставшееся время жизни токена
- Ключи: `jwt:blacklist:{jti}`

#### JwtMetadataCacheService

**Контракт**: `JwtMetadataCacheServiceInterface`

Кеширование JWT метаданных для быстрой валидации:

```php
public function rememberFromToken(string $token, User $user): void
public function getMetadataByToken(string $token): ?array
public function forgetByToken(string $token): void
```

**Кешируемые данные**:

- user_id
- email
- scopes
- subscription_tier
- email_verified

### 4.3. Infrastructure

#### JwtManager

Обертка над `firebase/php-jwt`:

- Кодирование токенов с RS256
- Декодирование и валидация
- Проверка срока действия
- Управление ключами

#### JwtPayloadFactory

Создание payload для JWT:

```php
[
    'iss' => 'http://localhost:8000',  // Issuer
    'iat' => time(),                     // Issued At
    'exp' => time() + 3600,              // Expiration
    'nbf' => time(),                     // Not Before
    'sub' => $user->id,                  // Subject (user ID)
    'jti' => Str::uuid()                 // JWT ID (unique)
]
```

#### Custom Exceptions

```php
namespace App\Exceptions\JWT;

- JwtException           // Базовое исключение
- TokenExpiredException  // Токен истек
- TokenInvalidException  // Невалидный токен
- TokenBlacklistedException // Токен в blacklist
```

## 5. Базы данных

### 5.1. PostgreSQL

#### Таблица: users

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### Таблица: refresh_tokens

```sql
CREATE TABLE refresh_tokens (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    refresh_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### Таблица: email_verification_tokens

```sql
CREATE TABLE email_verification_tokens (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 5.2. Redis

#### JWT Blacklist

```
Ключ: jwt:blacklist:{jti}
Значение: 1
TTL: оставшееся время жизни токена
```

#### JWT Metadata Cache

```
Ключ: auth:jwt:{jti}
Значение: JSON {user_id, email, scopes, subscription_tier, email_verified}
TTL: JWT_TTL (60 минут)
```

## 6. API Endpoints

### 6.1. Public Routes

#### POST /api/auth/register

Регистрация нового пользователя

**Request:**

```json
{
	"name": "John Doe",
	"email": "john@example.com",
	"password": "StrongPassword123!",
	"password_confirmation": "StrongPassword123!"
}
```

**Response (200):**

```json
{
	"user": {
		"id": 1,
		"name": "John Doe",
		"email": "john@example.com",
		"is_active": true,
		"email_verified_at": null
	},
	"accessToken": "eyJ0eXAiOiJKV1Q...",
	"refreshToken": "abc123...",
	"emailVerified": false
}
```

#### POST /api/auth/login

Аутентификация пользователя

**Request:**

```json
{
	"email": "john@example.com",
	"password": "StrongPassword123!"
}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Вход выполнен успешно",
  "data": {
    "user": {...},
    "access_token": "eyJ0eXAiOiJKV1Q...",
    "refresh_token": "xyz789..."
  }
}
```

#### POST /api/auth/refresh

Обновление токенов

**Request:**

```json
{
	"refresh_token": "xyz789..."
}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Токен обновлен успешно",
  "data": {
    "user": {...},
    "access_token": "NEW_JWT...",
    "refresh_token": "NEW_REFRESH..."
  }
}
```

**Логика**:

- Старый refresh токен отзывается
- Генерируется новая пара токенов
- TTL refresh: 14 дней (20160 минут)

### 6.2. Protected Routes (JWT Required)

#### GET /api/auth/me

Получение данных текущего пользователя

**Headers:**

```
Authorization: Bearer {access_token}
```

**Response (200):**

```json
{
	"id": 1,
	"name": "John Doe",
	"email": "john@example.com",
	"isActive": true,
	"emailVerifiedAt": null,
	"lastLoginAt": "2025-11-02T12:00:00.000000Z"
}
```

#### POST /api/auth/logout

Выход из системы

**Headers:**

```
Authorization: Bearer {access_token}
```

**Response (200):**

```json
{
	"success": true,
	"message": "Выход выполнен успешно"
}
```

**Логика**:

- JWT добавляется в blacklist
- Refresh токен удаляется из БД
- Метаданные удаляются из Redis

### 6.3. Internal API (для Gateway и микросервисов)

#### GET /api/auth/internal/jwt/validate

Валидация JWT токена

**Headers:**

```
Authorization: Bearer {access_token}
```

**Response (204 No Content):**

```
Headers:
X-User-Id: 1
X-Scopes:
X-Subscription-Tier:
X-Email-Verified: false
```

**Ошибки:**

- 401 - токен истек, невалиден, отсутствует
- 403 - пользователь неактивен

**Использование**: API Gateway вызывает этот эндпоинт для валидации каждого запроса

#### GET /api/auth/internal/jwt/public-key

Получение публичного RSA ключа

**Response (200):**

```
Content-Type: application/x-pem-file
Content-Disposition: inline; filename="public.pem"

-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAq0DLNZ...
-----END PUBLIC KEY-----
```

**Использование**: Другие микросервисы загружают ключ для локальной валидации JWT

#### GET /api/auth/internal/health

Internal health check

**Response (200):**

```json
{
	"status": "OK",
	"service": "auth-service-internal",
	"timestamp": "2025-11-02T12:00:00.000000Z"
}
```

## 7. JWT Implementation

### 7.1. Алгоритм: RS256

**Преимущества RS256 для микросервисов:**

- ✅ Приватный ключ только в auth-service
- ✅ Публичный ключ для валидации в других сервисах
- ✅ Невозможно подделать токен без приватного ключа
- ✅ Локальная валидация без запросов к auth-service

### 7.2. Генерация ключей

```bash
# Через artisan команду
php artisan jwt:generate-keys --force

# Через Makefile
make jwt-keys
```

**Результат:**

- `storage/jwt/private.pem` (4096 bit)
- `storage/jwt/public.pem`

### 7.3. Структура токена

```json
{
	"iss": "http://localhost:8000",
	"iat": 1730518400,
	"exp": 1730522000,
	"nbf": 1730518400,
	"sub": "1",
	"jti": "0affa80c-c5d7-4df5-9652-406d598dcdf2"
}
```

**Claims:**

- `iss` (Issuer) - кто выдал токен
- `iat` (Issued At) - время создания
- `exp` (Expiration) - время истечения
- `nbf` (Not Before) - токен не действителен до
- `sub` (Subject) - ID пользователя
- `jti` (JWT ID) - уникальный идентификатор токена

### 7.4. Валидация

**Проверки:**

1. Подпись (signature) с публичным ключом
2. Срок действия (exp)
3. Blacklist (Redis)
4. Активность пользователя
5. Метаданные в кеше

## 8. Middleware

### JwtAuthMiddleware

**Назначение**: Защита приватных роутов

**Алгоритм**:

1. Извлечь токен из `Authorization: Bearer {token}`
2. Проверить наличие в blacklist
3. Декодировать и валидировать через `JwtManager`
4. Получить пользователя из БД
5. Проверить `is_active`
6. Установить пользователя в `Auth::setUser()` и `$request`
7. Пропустить запрос дальше

**Использование**:

```php
Route::middleware('guard.jwt')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

## 9. События (RabbitMQ)

### 9.1. Exchange и Queues

**Exchange**: `user.events` (topic)
**Queues**:

- `user.registered`
- `user.logged_in`
- `user.logged_out`
- `email.verification.*`

### 9.2. Формат событий

```json
{
	"event": "user.registered",
	"timestamp": "2025-11-02T12:00:00.000000Z",
	"service": "auth-service",
	"data": {
		"user_id": 1,
		"email": "john@example.com",
		"name": "John Doe",
		"registered_at": "2025-11-02T12:00:00.000000Z"
	}
}
```

### 9.3. Типы событий

#### user.registered

Публикуется после успешной регистрации

```json
{
	"event": "user.registered",
	"data": {
		"user_id": 1,
		"email": "john@example.com",
		"name": "John Doe",
		"registered_at": "2025-11-02T12:00:00.000000Z"
	}
}
```

#### user.logged_in

Публикуется после успешного логина

```json
{
	"event": "user.logged_in",
	"data": {
		"user_id": 1,
		"email": "john@example.com",
		"logged_in_at": "2025-11-02T12:00:00.000000Z"
	}
}
```

#### user.logged_out

Публикуется после logout

```json
{
	"event": "user.logged_out",
	"data": {
		"user_id": 1,
		"email": "john@example.com",
		"logged_out_at": "2025-11-02T12:00:00.000000Z"
	}
}
```

#### email.verification.requested

Публикуется при создании токена верификации

```json
{
	"event": "email.verification.requested",
	"data": {
		"user_id": 1,
		"email": "john@example.com",
		"token": "abc123...",
		"expires_at": "2025-11-03T12:00:00.000000Z"
	}
}
```

## 10. Конфигурация (.env)

```bash
# Application
APP_NAME=SkillUp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=skillup_auth
DB_USERNAME=skillup
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# JWT Configuration
JWT_ALGO=RS256
JWT_TTL=60                    # Access token TTL (минуты)
JWT_REFRESH_TTL=20160         # Refresh token TTL (14 дней)
JWT_BLACKLIST_ENABLED=true
JWT_PUBLIC_KEY=file:///var/www/html/storage/jwt/public.pem
JWT_PRIVATE_KEY=file:///var/www/html/storage/jwt/private.pem

# JWT Cache (метаданные для быстрой валидации)
JWT_CACHE_STORE=redis
JWT_CACHE_PREFIX=auth:jwt
JWT_CACHE_TTL=60              # Должен совпадать с JWT_TTL
JWT_CACHE_MIN_TTL=5

# RabbitMQ
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
```

## 11. Команды Artisan

### jwt:generate-keys

Генерация RSA ключей для JWT

```bash
php artisan jwt:generate-keys [--force]
```

**Опции:**

- `--force` - перезаписать существующие ключи

**Результат:**

- Создает `storage/jwt/private.pem` (4096 bit)
- Создает `storage/jwt/public.pem`
- Устанавливает права 600/644

### tokens:cleanup-refresh

Очистка истекших refresh токенов

```bash
php artisan tokens:cleanup-refresh
```

**Запуск**: каждые 6 часов через Laravel Scheduler

### tokens:cleanup-email

Очистка истекших email verification токенов

```bash
php artisan tokens:cleanup-email
```

**Запуск**: каждые 6 часов через Laravel Scheduler (со смещением 30 минут)

## 12. Тестирование

### 12.1. Unit Tests

```bash
php artisan test --filter=Unit
```

**Покрытие:**

- DTO creation и validation
- Service логика
- Repository методы
- Helper функции

### 12.2. Feature Tests

```bash
php artisan test --filter=Feature
```

**Основные тесты:**

- ✅ JWT RS256 генерация и валидация
- ✅ Регистрация пользователя
- ✅ Логин с валидными/невалидными данными
- ✅ Refresh token flow
- ✅ Logout и blacklist
- ✅ Email verification flow
- ✅ Protected routes доступ

### 12.3. Integration Tests

**Тестируемые сценарии:**

- Полный flow регистрации → верификация → логин
- Refresh token rotation
- Blacklist после logout
- Internal API для Gateway
- RabbitMQ события

**Запуск:**

```bash
# Вручную через curl (примеры в scripts/)
./test-all-endpoints.sh
```

## 13. Безопасность

### 13.1. Парольная политика

**Требования:**

- Минимум 8 символов
- Хеширование: bcrypt
- Валидация: `StrongPassword` rule

### 13.2. Rate Limiting

**Защита от brute-force:**

- Login: 5 попыток / минута / IP
- Register: 3 попытки / минута / IP
- Refresh: 10 попыток / минута / IP

**Реализация**: Laravel `throttle` middleware (планируется)

### 13.3. Internal API Security

**Защита internal routes:**

- IP whitelist (только Docker network)
- mTLS (опционально)
- Отдельный rate limit
- Логирование всех обращений

### 13.4. XSS/CSRF Protection

- Laravel встроенная CSRF защита для form-based запросов
- API работает через JWT (stateless)
- Санитизация входных данных через FormRequest

## 14. Производительность

### 14.1. SLA

**Целевые показатели:**

- Public API: ≤ 200ms (p95)
- Internal API: ≤ 50ms (p95)
- JWT validation: ≤ 10ms (с кешем)

### 14.2. Оптимизации

**Redis Cache:**

- JWT метаданные (user_id, scopes, tier)
- Публичный ключ (для других сервисов)
- User активность

**Database:**

- Индексы на email, refresh_token
- Cascade delete для связанных записей
- Connection pooling

**Application:**

- Stateless сервис (горизонтальное масштабирование)
- Eager loading для связей
- Query оптимизация

## 15. Мониторинг и логирование

### 15.1. Логи

**Уровни:**

- `INFO` - успешные операции
- `WARNING` - подозрительная активность
- `ERROR` - ошибки системы

**Примеры:**

```
[2025-11-02 12:00:00] local.INFO: JWT аутентификация успешна
{"user_id":1,"email":"john@example.com","ip":"172.18.0.1"}

[2025-11-02 12:05:00] local.WARNING: JWT токен в blacklist
{"token":"eyJ0eXAi...","ip":"172.18.0.1"}

[2025-11-02 12:10:00] local.ERROR: Failed to publish RabbitMQ event
{"event":"user.registered","error":"Connection refused"}
```

### 15.2. Метрики

**Key Performance Indicators:**

- Количество регистраций / час
- Количество логинов / час
- Failed login attempts
- JWT validation time (p50, p95, p99)
- Redis cache hit rate
- RabbitMQ событий / минута

### 15.3. Алерты

**Критические:**

- DB connection lost
- Redis unavailable
- RabbitMQ connection error
- Высокий rate failed logins

**Предупреждения:**

- Высокая латентность API
- Низкий cache hit rate
- Большое количество refresh токенов на пользователя

## 16. Развертывание

### 16.1. Локальная разработка

```bash
# Клонирование
git clone https://github.com/your-org/skillup.git
cd skillup

# Инициализация
make init

# Запуск
make start

# Проверка
curl http://localhost/api/health
```

### 16.2. Production Deployment

**Pre-flight checklist:**

- [ ] `.env` настроен для production
- [ ] JWT ключи сгенерированы (`make jwt-keys`)
- [ ] Миграции выполнены (`php artisan migrate`)
- [ ] Redis доступен и настроен
- [ ] RabbitMQ доступен
- [ ] Supervisor настроен и запущен
- [ ] Nginx настроен как reverse proxy
- [ ] SSL/TLS сертификаты установлены
- [ ] Мониторинг настроен

**Deployment steps:**

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart services
supervisorctl restart all
```

### 16.3. Rollback Plan

```bash
# 1. Revert to previous version
git checkout <previous-commit>

# 2. Rollback migrations (if needed)
php artisan migrate:rollback --step=1

# 3. Clear caches
php artisan cache:clear
php artisan config:clear

# 4. Restart
supervisorctl restart all
```

## 17. Roadmap

### ✅ Phase 1: Core (Completed)

- JWT RS256 implementation
- User registration & authentication
- Refresh token flow
- Email verification
- Internal API for Gateway
- RabbitMQ integration
- Docker setup

### 🚧 Phase 2: Security & Monitoring (In Progress)

- Rate limiting implementation
- Advanced logging (ELK)
- Prometheus metrics
- Sentry error tracking
- IP whitelist for internal routes

### 📋 Phase 3: Advanced Features (Planned)

- OAuth2 integration (Google, GitHub)
- Two-factor authentication (2FA)
- User roles and permissions (RBAC)
- Session management dashboard
- Password reset via email
- Account lockout после N failed attempts

### 📋 Phase 4: Optimization (Planned)

- Read replicas для PostgreSQL
- Redis Cluster
- JWT ключи rotation automation
- Advanced caching strategies
- Performance profiling и optimization

## 18. Поддержка и вопросы

**Документация:**

- Основной README: `/services/auth-service/README.md`
- JWT ключи: `/services/auth-service/jwt/README.md`
- Deployment: `/services/auth-service/DEPLOYMENT_GUIDE.md`

**Контакты:**

- Technical Lead: [ваше имя]
- Repository: [ссылка на repo]
- Issues: [ссылка на issues]

---

**Версия документа**: 1.0  
**Дата последнего обновления**: 2025-11-02  
**Статус**: ✅ Production Ready
