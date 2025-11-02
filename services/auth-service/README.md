# Auth-Service — Сервис аутентификации SkillUp

Централизованный микросервис аутентификации и управления пользователями для платформы SkillUp.

## 🎯 Возможности

- ✅ Регистрация и аутентификация пользователей
- ✅ JWT токены с **RS256** (асимметричная криптография)
- ✅ Refresh токены с автоматической ротацией
- ✅ Email верификация
- ✅ Blacklist для отозванных токенов
- ✅ Событийная архитектура через RabbitMQ
- ✅ Internal API для API Gateway
- ✅ Redis кеширование метаданных JWT
- ✅ Автоматическая очистка истекших токенов

## 🏗️ Архитектура

```
┌─────────────┐
│   Gateway   │──────────────┐
│   (Nginx)   │              │ /internal/jwt/validate
└─────────────┘              ▼
       │              ┌──────────────┐
       │              │ Auth-Service │
       ▼              │   (RS256)    │
┌─────────────┐      └──────────────┘
│  Frontend   │              │
│  (Nuxt 4)   │              ├─► PostgreSQL (users, tokens)
└─────────────┘              ├─► Redis (cache, blacklist)
       │                     └─► RabbitMQ (events)
       │
       ▼
┌─────────────┐      ┌──────────────┐
│ AI-Service  │      │Subscription  │
│  (public    │      │   Service    │
│   key only) │      └──────────────┘
└─────────────┘
```

## 📋 Стек технологий

- **Backend:** Laravel 12, PHP 8.2
- **Database:** PostgreSQL 15
- **Cache:** Redis 7
- **Message Broker:** RabbitMQ 3
- **JWT:** tymon/jwt-auth с RS256
- **Process Manager:** Supervisor

## 🚀 Быстрый старт

### Предварительные требования

- Docker & Docker Compose
- Make (опционально)

### Установка

```bash
# 1. Клонировать репозиторий
cd services/auth-service/src

# 2. Установить зависимости
composer install

# 3. Создать .env файл (используйте ENV_TEMPLATE.md)
cp ENV_TEMPLATE.md .env
# Заполните переменные окружения

# 4. Сгенерировать APP_KEY
php artisan key:generate

# 5. Сгенерировать RSA ключи для JWT
php artisan jwt:generate-keys

# 6. Скопировать ключи для Docker
mkdir -p ../jwt
cp storage/jwt/private.pem ../jwt/
cp storage/jwt/public.pem ../jwt/
chmod 600 ../jwt/private.pem
chmod 644 ../jwt/public.pem

# 7. Запустить через Docker Compose
cd ../../..
docker-compose up -d auth-service

# 8. Выполнить миграции
docker-compose exec auth-service php artisan migrate

# 9. Проверить работу
curl http://localhost:80/api/health
```

## 📚 Документация

- **[Техническое задание](../../docs/auth-service/auth-service-tz.md)** — детальное описание требований
- **[Архитектурный обзор](../../docs/auth-service/auth-service-overview.md)** — описание компонентов и потоков
- **[Миграция на RS256](./RS256_MIGRATION_GUIDE.md)** — руководство по RS256 JWT
- **[Переменные окружения](./ENV_TEMPLATE.md)** — шаблон конфигурации

## 🔐 Безопасность

### JWT с RS256

- **Приватный ключ** — только в auth-service (генерация токенов)
- **Публичный ключ** — в других сервисах (валидация токенов)
- Компрометация одного сервиса не угрожает всей системе

### Хранение токенов

- Access Token: JWT (RS256), TTL 60 минут
- Refresh Token: случайная строка 64 символа, в PostgreSQL
- Blacklist: Redis для отозванных токенов

### Дополнительная защита

- Rate limiting (60 запросов/мин)
- Валидация email и пароля
- Проверка активности пользователя
- IP whitelist для internal endpoints

## 📡 API Endpoints

### Public API

| Метод | Endpoint | Описание |
|-------|----------|----------|
| POST | `/api/auth/register` | Регистрация пользователя |
| POST | `/api/auth/login` | Вход в систему |
| POST | `/api/auth/refresh` | Обновление токенов |
| POST | `/api/auth/logout` | Выход (требует JWT) |
| GET | `/api/auth/me` | Текущий пользователь (требует JWT) |
| POST | `/api/auth/verify-email` | Подтверждение email |
| POST | `/api/auth/resend-verification` | Повторная отправка токена |
| GET | `/api/auth/verification-status` | Статус верификации |

### Internal API (для Gateway)

| Метод | Endpoint | Описание |
|-------|----------|----------|
| GET | `/api/internal/jwt/validate` | Валидация токена + метаданные |
| GET | `/api/internal/jwt/public-key` | Получение публичного ключа |
| GET | `/api/internal/health` | Health check |

### System Endpoints

| Метод | Endpoint | Описание |
|-------|----------|----------|
| GET | `/api/health` | Проверка доступности |
| GET | `/api/status` | Статус сервиса |

## 🔄 События RabbitMQ

Сервис **публикует** следующие события:

| Событие | Очередь | Описание |
|---------|---------|----------|
| `user.registered` | `user.events` | Новый пользователь зарегистрирован |
| `user.logged_in` | `user.events` | Пользователь вошел в систему |
| `user.logged_out` | `user.events` | Пользователь вышел из системы |
| `email.verification.requested` | `email.verification` | Запрошена верификация email |
| `email.verification.completed` | `email.verification` | Email подтвержден |
| `email.verification.resent` | `email.verification` | Повторная отправка токена |

> **Примечание:** Auth-Service НЕ потребляет события, только публикует.

## 🛠️ Artisan команды

```bash
# Генерация RSA ключей
php artisan jwt:generate-keys [--bits=4096] [--force]

# Очистка истекших токенов
php artisan tokens:cleanup-refresh
php artisan tokens:cleanup-email

# Управление очередями (не используется)
# php artisan queue:work
```

## 📊 Мониторинг

### Health Checks

```bash
# Основной health check
curl http://localhost:80/api/health

# Internal health check
curl http://localhost:80/api/internal/health
```

### Логи

```bash
# Все логи сервиса
docker-compose logs -f auth-service

# PHP-FPM логи
docker-compose exec auth-service tail -f /var/log/supervisor/php-fpm.out.log

# Laravel scheduler логи
docker-compose exec auth-service tail -f /var/log/supervisor/laravel-scheduler.out.log

# Laravel логи
docker-compose exec auth-service tail -f storage/logs/laravel.log
```

### Метрики

- Время ответа API: p95 < 200ms
- Internal JWT validation: p95 < 50ms
- Доступность: 99.9%

## 🧪 Тестирование

```bash
# Запуск всех тестов
docker-compose exec auth-service php artisan test

# Unit тесты
docker-compose exec auth-service php artisan test --testsuite=Unit

# Feature тесты
docker-compose exec auth-service php artisan test --testsuite=Feature
```

## 🔧 Разработка

### Структура проекта

```
src/
├── app/
│   ├── Console/Commands/          # Artisan команды
│   ├── Contracts/                 # Интерфейсы
│   ├── DTOs/                      # Data Transfer Objects
│   ├── Events/                    # Event Publishers
│   ├── Http/
│   │   ├── Controllers/           # API контроллеры
│   │   ├── Middleware/            # JWT middleware
│   │   ├── Requests/              # Form Requests
│   │   └── Resources/             # API Resources
│   ├── Models/                    # Eloquent модели
│   ├── Repositories/              # Репозитории данных
│   ├── Rules/                     # Validation Rules
│   └── Services/                  # Бизнес-логика
├── config/                        # Конфигурация
├── database/                      # Миграции, фабрики
├── routes/                        # API маршруты
│   ├── api.php                    # Public API
│   ├── internal.php               # Internal API
│   └── console.php                # Artisan команды
└── storage/                       # Логи, кеш, JWT ключи
```

### Добавление нового endpoint

1. Создать контроллер в `app/Http/Controllers/`
2. Добавить маршрут в `routes/api.php`
3. Создать Request в `app/Http/Requests/` (опционально)
4. Создать Resource в `app/Http/Resources/` (опционально)
5. Написать тесты в `tests/Feature/`

## 🐛 Troubleshooting

### JWT ошибки

```bash
# Пересоздать ключи
php artisan jwt:generate-keys --force

# Очистить blacklist
php artisan cache:clear
```

### Database ошибки

```bash
# Пересоздать базу
php artisan migrate:fresh

# Заполнить тестовыми данными
php artisan db:seed
```

### Redis connection refused

```bash
# Проверить Redis
docker-compose ps redis
docker-compose logs redis

# Перезапустить Redis
docker-compose restart redis
```

## 📦 Зависимости

### Основные

- `laravel/framework: ^12.0` — Laravel фреймворк
- `tymon/jwt-auth: ^2.0` — JWT аутентификация
- `php-amqplib/php-amqplib: ^3.0` — RabbitMQ клиент
- `predis/predis: ^2.2` — Redis клиент

### Development

- `phpunit/phpunit: ^11.5` — Тестирование
- `laravel/pint: ^1.24` — Code style
- `mockery/mockery: ^1.6` — Mocking

## 🤝 Вклад в проект

1. Fork репозитория
2. Создать feature branch
3. Commit изменений
4. Push в branch
5. Создать Pull Request

## 📄 Лицензия

Proprietary — SkillUp Platform

## 👥 Команда

- **Backend Lead:** [Ваше имя]
- **DevOps:** [Имя]
- **Security:** [Имя]

## 🔗 Связанные проекты

- **Frontend:** `frontend/` — Nuxt 4 приложение
- **AI Service:** `services/ai-service/` — Генерация контента
- **Subscription Service:** `services/subscription-service/` — Управление подписками
- **API Gateway:** `services/nginx/` — Nginx конфигурация

---

**Версия:** 1.0.0  
**Последнее обновление:** 2024  
**Документация:** [docs/auth-service/](../../docs/auth-service/)

