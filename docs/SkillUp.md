# SkillUp — Архитектура платформы

## 1. Основные цели

**SkillUp** — платформа для создания контента с использованием AI на основе микросервисной архитектуры.

### Ключевые компоненты:

- **API Gateway (Nginx)**: единая точка входа, TLS termination, проверка JWT через `auth-service`, маршрутизация к микросервисам, rate limiting.
- **auth-service**: аутентификация JWT RS256, регистрация пользователей, email верификация, blacklist через Redis, Internal API для Gateway.
- **ai-service**: генерация текстов на основе промтов, синхронная и асинхронная обработка через RabbitMQ, интеграция с OpenAI/Anthropic.
- **subscribe-service**: управление подписками, валидация прав на функции, SLA и лимиты, интеграция с payment-service.
- **payment-service**: обработка платежей, управление балансом, история транзакций.

---

## 2. Поток работы пользователя

### 1. Регистрация и вход

**Frontend** → `POST /api/auth/register` или `/api/auth/login`

- Проверка email/пароля
- Генерация JWT RS256 (TTL: 60 минут) + Refresh Token (TTL: 14 дней)
- Сохранение метаданных в Redis: `auth:jwt:{jti}` → {user_id, email, scopes, tier, email_verified}
- Frontend сохраняет оба токена
- Публикация события `user.registered` или `user.logged_in` в RabbitMQ

2. **Пользователь выбирает тему и функцию**

   - Фронтенд отправляет запрос на `/generate` с JWT, темой и функцией.

### 3. API Gateway / Проверка доступа

**Nginx** получает запрос с `Authorization: Bearer {JWT}`

- TLS termination
- Rate limiting по IP
- `auth_request` → `GET /api/auth/internal/jwt/validate`
- **auth-service** проверяет:
  - Подпись токена (RS256)
  - Срок действия (exp)
  - Blacklist (Redis)
  - Статус пользователя (is_active)
- При успехе: возвращает `204 No Content` + заголовки:
  - `X-User-Id: 1`
  - `X-Scopes: read,write`
  - `X-Subscription-Tier: premium`
  - `X-Email-Verified: true`
- Gateway проксирует запрос к целевому сервису с этими заголовками
- При ошибке: 401/403 без проксирования

4. **AI-Service и Subscribe-Service**

   - `ai-service` проверяет бизнес-лимиты: обращается в `subscribe-service` за актуальным статусом подписки.
   - При отказе подписки gateway возвращает сообщение «недоступно, оформи подписку».
   - При успехе `ai-service` формирует промт через `prompt-service`.
   - Для коротких ответов синхронно вызывает AI Engine.
   - Для длинных запросов отправляет задачу в RabbitMQ (`generate.long`) и возвращает пользователю `202 Accepted` с task-id.

5. **Асинхронная обработка**

   - Worker AI Engine слушает очередь RabbitMQ, обрабатывает запрос и сохраняет результат в PostgreSQL и Redis.
   - После генерации worker отправляет событие в `notification-service` (WebSocket/push/email).

6. **Возврат результата**

   - Синхронно: `ai-service` → API Gateway → Frontend → пользователь получает текст.
   - Асинхронно: Frontend периодически запрашивает `/generate/{task-id}` или получает уведомление и забирает готовый текст из Redis/PostgreSQL.

---

## 3. Взаимодействие микросервисов

| Сервис                | Ключевые функции                                                    | Взаимодействие                                |
| --------------------- | ------------------------------------------------------------------- | --------------------------------------------- |
| **API Gateway**       | TLS, rate limit, проверка JWT через `auth-service`, circuit breaker | Frontend, auth-service, ai-service            |
| **auth-service**      | Аутентификация, выдача/ревокация JWT, валидация                     | Frontend, gateway, ai-service                 |
| **subscribe-service** | Проверка статуса подписки, доступных функций, лимитов               | ai-service, payment-service                   |
| **ai-service**        | Генерация текста, постановка задач в RabbitMQ, кеширование          | subscribe-service, RabbitMQ, Redis/PostgreSQL |
| **RabbitMQ**          | Очереди асинхронной генерации, ретраи                               | ai-service, worker AI Engine                  |

---

## 4. Асинхронность и масштабирование

- **RabbitMQ** используется для длинных и ресурсоёмких генераций, ретраев, приоритезации запросов.
  - Frontend → API Gateway → `ai-service` → RabbitMQ (`generate.long`).
  - Worker → AI Engine → Redis/PostgreSQL → `notification-service` → Frontend.
  - Отдельная очередь `generate.retry` для повторов при сбоях AI Engine.
- Горизонтальное масштабирование gateway и `ai-service` за счёт stateless-инстансов.
- Redis хранит короткие результаты, метаданные JWT и кэш подписок.

---

## 5. Инфраструктура и технологии

### Backend Stack

- **Laravel 12** (PHP 8.2+) - auth-service, ai-service, subscribe-service
- **PostgreSQL 16** - основная БД для всех сервисов
- **Redis 7** - кеш, JWT metadata, blacklist, очереди
- **RabbitMQ 3.13** - событийная шина (event bus)
- **Nginx** - API Gateway с `auth_request`

### Frontend Stack

- **Nuxt 3** (Vue 3, TypeScript)
- **Pinia** - state management
- **SCSS** - стилизация

### DevOps

- **Docker + Docker Compose** - локальная разработка
- **Supervisor** - process management в контейнерах
- **Prometheus + Grafana** - мониторинг (планируется)
- **Sentry** - error tracking (планируется)
- **ELK Stack** - централизованные логи (планируется)

### Безопасность

- **JWT RS256** - асимметричное шифрование (4096 bit)
- **TLS/HTTPS** - на уровне Gateway
- **Rate Limiting** - защита от brute-force
- **IP Whitelist** - для internal routes
- **bcrypt** - хеширование паролей
- **Blacklist** - отзыв токенов через Redis

## 6. Детальная документация

Для подробной информации по каждому сервису см. соответствующие документы:

- **[Auth Service ТЗ](./auth-service.md)** - полная спецификация сервиса аутентификации
- **AI Service ТЗ** - (в разработке)
- **Subscribe Service ТЗ** - (планируется)
- **Payment Service ТЗ** - (планируется)

---

**Версия**: 2.0  
**Последнее обновление**: 2025-11-02  
**Статус**: ✅ Auth Service готов, остальные в разработке
