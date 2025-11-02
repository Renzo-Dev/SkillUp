# SkillUp — Техническая документация

## Обзор проекта

**SkillUp** — платформа для создания контента с использованием AI, построенная на микросервисной архитектуре.

## Архитектура

```
┌─────────────┐
│   Frontend  │ (Nuxt 3 + Vue 3)
└──────┬──────┘
       │ HTTPS
       ▼
┌─────────────┐
│ API Gateway │ (Nginx)
│  - TLS      │
│  - Rate     │
│  - Auth     │
└──────┬──────┘
       │
       ├─────────────┐─────────────┐─────────────┐
       ▼             ▼             ▼             ▼
┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
│   Auth   │  │    AI    │  │Subscribe │  │ Payment  │
│ Service  │  │ Service  │  │ Service  │  │ Service  │
└─────┬────┘  └─────┬────┘  └─────┬────┘  └─────┬────┘
      │             │              │              │
      └─────────────┴──────────────┴──────────────┘
                    │
         ┌──────────┴──────────┐
         ▼                     ▼
    ┌─────────┐          ┌─────────┐
    │  Redis  │          │RabbitMQ │
    │ Cache + │          │ Events  │
    │Blacklist│          │  Bus    │
    └─────────┘          └─────────┘
         ▼
    ┌─────────┐
    │PostgreSQL│
    └─────────┘
```

## Микросервисы

### 1. Auth Service

**Статус**: ✅ Реализован

Центральный сервис аутентификации и авторизации:

- JWT RS256 (firebase/php-jwt)
- Регистрация, логин, refresh токенов
- Email верификация
- Blacklist через Redis
- Internal API для Gateway

📄 **Документация**: [auth-service.md](./auth-service.md)

### 2. AI Service

**Статус**: 🚧 В разработке

Генерация контента с использованием AI:

- Синхронная и асинхронная генерация
- Интеграция с OpenAI/Anthropic
- Кеширование запросов
- Rate limiting по подписке

### 3. Subscribe Service

**Статус**: 📋 Планируется

Управление подписками и доступом:

- Тарифные планы
- Лимиты по функциям
- SLA управление
- Интеграция с Payment Service

### 4. Payment Service

**Статус**: 📋 Планируется

Обработка платежей:

- Интеграция с платежными системами
- Управление балансом
- История транзакций

## Технологический стек

### Backend

- **Laravel 12** (PHP 8.2+)
- **PostgreSQL 16** - основная БД
- **Redis 7** - кеш, очереди, blacklist
- **RabbitMQ 3.13** - событийная шина

### Frontend

- **Nuxt 3** (Vue 3)
- **TypeScript**
- **Pinia** - state management
- **SCSS** - стилизация

### Infrastructure

- **Docker** + Docker Compose
- **Nginx** - API Gateway
- **Supervisor** - process management

## Интеграции

### RabbitMQ Events

- `user.events` - события пользователей
- `email.verification` - верификация email
- `ai.generation` - генерация контента
- `subscription.changes` - изменения подписок

### Redis

- JWT Blacklist
- JWT Metadata Cache
- API Response Cache
- Session Storage

## Безопасность

### JWT (RS256)

- Асимметричное шифрование
- Приватный ключ только в auth-service
- Публичный ключ для валидации в других сервисах
- TTL: 60 минут (access), 14 дней (refresh)

### API Gateway

- TLS/HTTPS
- Rate limiting
- IP whitelist для internal routes
- CORS конфигурация

### Данные

- Хеширование паролей (bcrypt)
- Транзакционность операций
- Cascade удаление связанных данных

## Развертывание

### Локальная разработка

```bash
# Инициализация проекта
make init

# Запуск сервисов
make start

# Остановка
make stop

# Полная очистка
make clean

# Генерация JWT ключей
make jwt-keys
```

### Окружения

- **development** - локальная разработка
- **staging** - тестирование
- **production** - продакшн

## Мониторинг и логирование

### Логи

- Laravel logs: `storage/logs/`
- Supervisor logs: `/var/log/supervisor/`
- Nginx access/error logs

### Health Checks

- `GET /api/health` - статус сервиса
- `GET /api/status` - детальная информация

### Метрики (планируется)

- Prometheus + Grafana
- Sentry для ошибок
- ELK Stack для централизованных логов

## Документы

- [Архитектура проекта](./SkillUp.md)
- [Auth Service ТЗ](./auth-service.md)
- AI Service ТЗ (в разработке)
- Subscribe Service ТЗ (в разработке)

## Состояние проекта

**Последнее обновление**: 2025-11-02

### ✅ Завершено

- Auth Service полностью реализован
- JWT RS256 миграция завершена
- Docker инфраструктура настроена
- RabbitMQ интеграция работает
- Redis кеш и blacklist
- Email verification flow

### 🚧 В процессе

- AI Service разработка
- Frontend интеграция с Auth Service

### 📋 Запланировано

- Subscribe Service
- Payment Service
- Admin Panel
- Мониторинг и аналитика
