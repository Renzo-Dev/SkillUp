# Техническое задание: Subscription Service

> Комментарий: Чёткое ТЗ ускорит синхронизацию между командами.

## 1. Область и цели

- Развернуть выделенный микросервис на Laravel, отвечающий за управление подписками, жизненный цикл тарифов, лимиты использования и аудит.
- Интегрировать сервис с Payment Service через RabbitMQ для обработки событий (`payment_success`, `subscription_renewed`, `payment_failed`, `subscription_cancelled`).
- Предоставить REST API для внутренних сервисов и фронтенда (Nuxt js) с возможностью получать статус, менять тариф, отменять подписку.
- Гарантировать идемпотентную обработку событий, полноту аудита и жёсткий контроль лимитов премиум-функций.
- Вне рамок: UI оплаты, биллинговые страницы, шаблоны писем (занимается Notification Service).

## 2. Заинтересованные стороны

- Инженерия: Backend (Laravel), Frontend (Vue 3), DevOps, QA, Data.
- Внешние сервисы: Payment Service, Notification Service, Auth Service, AI Service (потребляет лимиты).

## 3. Архитектура высокого уровня

- Приложение на Laravel 12 развёрнутое отдельным контейнером.
- Схема PostgreSQL `subscription` с выделенными таблицами (см. раздел 6).
- Consumer RabbitMQ (`subscriptions.payments`) для входящих платёжных событий.
- Исходящие события публикуются в `subscriptions.events`.
- Redis (общий) для кэширования статуса подписки и троттлинга.
- Аутентификация по JWT, выпускаемому Auth Service.

> Комментарий: Архитектура совместима с текущими сервисами.

## 4. Ключевые сценарии и потоки

1. **Активация новой подписки**
   - Payment Service публикует `payment_success`.
   - Subscription Service валидирует payload, создаёт/обновляет запись `subscriptions`, эмитит событие `subscription_activated`, обновляет кэш.
2. **Продление подписки**
   - Событие `subscription_renewed` продлевает период, записывает аудит, обновляет лимиты.
3. **Обработка неуспешного платежа**
   - Событие `payment_failed` переводит подписку в grace-период, шлёт уведомление Notification Service (`subscription_payment_failed`).
4. **Отмена подписки**
   - Внутренний API `POST /api/subscription/cancel` ставит статус `cancelled`, планирует дату завершения, эмитит `subscription_cancelled`.
5. **Проверка лимитов**
   - API `GET /api/subscription/limits` возвращает остатки. При списании `POST /api/subscription/usage` атомарно уменьшает счётчики и блокирует превышения.

## 5. Нефункциональные требования

- Доступность: 99,5% в месяц.
- Пропускная способность: до 200 платёжных событий/мин и 1000 проверок лимитов/мин.
- Идемпотентность: учёт уникальности `event_id` при обработке событий.
- Задержка: ответы API < 250 мс (P95).
- Аудит: хранить историю 24 месяца.
- Соответствие GDPR: поддержка удаления подписки и анонимизации PII.

## 6. Модель данных (PostgreSQL)

- Нотация: `snake_case`, временные поля `timestamptz`.
- Таблицы:
  - `plans`
    - `id` (uuid, PK)
    - `code` (text, уникальное, например `free`, `pro`)
    - `name` (text)
    - `description` (text)
    - `price_cents` (integer)
    - `currency` (char(3), по умолчанию `USD`)
    - `billing_cycle` (enum: `monthly`, `yearly`, `lifetime`)
    - `trial_period_days` (integer, nullable)
    - `is_active` (bool)
    - `created_at`, `updated_at`
  - `plan_features`
    - `id` (uuid, PK)
    - `plan_id` (uuid, FK -> `plans.id`)
    - `feature_key` (text, например `ai_requests_per_month`)
    - `limit_value` (integer или null для «без лимита»)
    - `metadata` (jsonb)
  - `subscriptions`
    - `id` (uuid, PK)
    - `user_id` (uuid, FK -> `public.users.id`)
    - `plan_id` (uuid, FK -> `plans.id`)
    - `status` (enum: `trial`, `active`, `grace`, `cancelled`, `expired`)
    - `started_at`, `expires_at`, `trial_ends_at`
    - `cancelled_at`, `cancellation_reason`
    - `auto_renew` (bool)
    - `last_payment_id` (uuid из Payment Service)
    - `source` (enum: `payment_service`, `manual`, `admin`)
    - `created_at`, `updated_at`
  - `subscription_events`
    - `id` (uuid, PK)
    - `subscription_id` (uuid, FK -> `subscriptions.id`)
    - `event_type` (enum)
    - `payload` (jsonb, исходное событие)
    - `processed_at`
    - `correlation_id`
    - `created_at`
  - `usage_counters`
    - `id` (uuid, PK)
    - `subscription_id` (uuid, FK)
    - `feature_key` (text)
    - `period_start`, `period_end`
    - `used_amount` (integer)
    - `limit_value` (integer)
    - `updated_at`
  - `webhook_logs`
    - `id` (uuid, PK)
    - `direction` (`incoming`, `outgoing`)
    - `endpoint`
    - `request_body`, `response_body` (jsonb)
    - `status_code`
    - `processed_at`

> Комментарий: Структура покрывает биллинг и контроль лимитов.

### Ограничения БД

- Уникальный индекс `subscriptions(user_id)` для статусов (`trial`, `active`, `grace`) — одна активная подписка на пользователя.
- Уникальный индекс `subscription_events(correlation_id)` — гарантия идемпотентности.
- Внешние ключи с `ON DELETE CASCADE` для зависимых таблиц, кроме `plans` (запрет удаления активных тарифов).

## 7. Контракты сообщений (RabbitMQ)

- Exchange `payments.events` (источник) с routing keys:
  - `payment.success`
  - `payment.failed`
  - `subscription.renewed`
  - `subscription.cancelled`
- Очередь `subscription.payments`, привязанная к этим routing keys.
- Пример payload для `payment.success`:
  ```json
  {
  	"event_id": "uuid",
  	"event_type": "payment_success",
  	"occurred_at": "2025-09-27T10:00:00Z",
  	"payment_id": "uuid",
  	"user_id": "uuid",
  	"plan_code": "pro",
  	"amount_cents": 1999,
  	"currency": "USD",
  	"cycle": "monthly",
  	"metadata": { "coupon": "SEPT25" }
  }
  ```
- Исходящие события публикуются в `subscriptions.events` с ключами `subscription.activated`, `subscription.renewed`, `subscription.cancelled`, `subscription.payment_failed`.
- Каждый месседж обязан содержать `event_id`, `correlation_id`, `occurred_at`, `payload_version`.

## 8. REST API (для внутренних сервисов и фронта)

- Базовый путь: `/api/subscription`.
- Аутентификация: Bearer JWT (валидация по публичному ключу Auth Service).
- Версионирование: префикс `v1` в именовании роутов.
- Эндпоинты:
  - `GET /status`
    - Возвращает текущий статус подписки, детали тарифа, остатки лимитов.
    - Ответ 200 содержит `status`, `plan`, `expires_at`, `limits`.
  - `POST /change`
    - Тело: `{ "plan_code": string, "payment_token": string (optional) }`.
    - Валидирует правила смены тарифа, инициирует платёж через Payment Service, возвращает состояние `pending`.
  - `POST /cancel`
    - Тело: `{ "reason": string (optional) }`.
    - Отключает авто-продление, переводит в `grace`.
  - `GET /history`
    - Постранично отдаёт события подписки и ссылки на платежи.
  - `GET /limits`
    - Возвращает текущие показатели использования по каждой фиче.
  - `POST /usage`
    - Тело: `{ "feature_key": string, "amount": int, "context": object }`.
    - Атомарно уменьшает лимит, отдаёт остаток (200) или 409 при превышении.
  - Админ-эндпоинты (требуют роль `subscription.admin`):
    - `POST /admin/plans`
    - `PATCH /admin/plans/{id}`
    - `POST /admin/subscriptions/{id}/adjust`

> Комментарий: Эндпоинты согласованы с фронтом.

### Валидация и обработка ошибок API

- Использовать Laravel Form Requests, возвращать ошибки в формате JSON:API (`errors` массив).
- Базовые коды: 400 (валидация), 401 (авторизация), 403 (недостаточно прав), 404 (не найдено), 409 (конфликт), 422 (бизнес-ошибка), 500 (непредвиденная ошибка).
- Каждая ошибка содержит `error_code` и локализованный `message`.

## 9. Бизнес-правила

- Бесплатные пользователи имеют `plan_code = free`, нулевая стоимость и лимиты.
- Trial автоматически конвертируется в платный тариф при успешном платеже до `trial_ends_at`.
- Grace-период: 3 дня после неуспешного платежа; по истечении статус -> `expired`.
- Понижение тарифа вступает в силу с начала следующего биллингового периода.
- Счётчики использования обнуляются по расписанию в начале периода.
- Идемпотентность: повторная обработка одного `event_id` игнорируется с логированием.

## 10. Фоновые задачи и расписание

- Воркерная очередь (Laravel Horizon) для:
  - обработки входящих сообщений RabbitMQ;
  - пересчёта кэша при смене тарифа;
  - отправки исходящих событий.
- Плановые команды:
  - `subscriptions:expire` ежедневно 00:00 UTC — завершение просроченных подписок.
  - `subscriptions:reset-usage` ежедневно — сброс месячных счётчиков на границе периода.
  - `subscriptions:sync-payment-status` ежечасно — reconciliation с Payment Service.

## 11. Безопасность и контроль доступа

- Валидация JWT по публичному ключу Auth Service; пользовательские эндпоинты требуют scope `user.subscription`.
- Админ-эндпоинты доступны при `role = admin` или `permission = manage_subscriptions`.
- Фильтрация данных по `user_id`, исключающая доступ к чужим подпискам.
- Шифровать чувствительные поля (например, `payment_token`) встроенным шифрованием Laravel.
- Лимиты запросов: `GET /status` — 30/мин на пользователя, `POST /usage` — 60/мин.

## 12. Стратегия кэширования

- Ключ `subscription:{user_id}:status` хранит сериализованный статус 5 минут.
- Инвалидация кэша по событиям и ручным изменениям.
- Счётчики лимитов поддерживаются в Redis для быстрых чтений, периодически сбрасываются в БД.

## 13. Наблюдаемость и логирование

- Структурированные логи (JSON) с тегом `service=subscription-service`.
- Метрики Prometheus: `subscription_events_processed_total`, `subscription_status_active`, `usage_quota_exhausted_total`.
- Алерты:
  - ошибка обработки >5% событий за 5 минут;
  - очередь >500 сообщений >10 минут.

## 14. Конфигурация и деплой

- Переменные окружения:
  - `DB_*`, `RABBITMQ_HOST`, `RABBITMQ_QUEUE`, `RABBITMQ_EXCHANGE`, `JWT_PUBLIC_KEY`, `CACHE_DRIVER`, `QUEUE_CONNECTION`.
- Docker-образ на базе `php:8.3-fpm` + supervisor для воркеров.
- В CI/CD обязательно прогонять миграции и smoke-тесты до выката.
- Рекомендован blue/green деплой; при обновлении consumers выполнять rolling restart.

## 15. План миграции

- Создать миграции для схемы с временными метками.
- Загрузить дефолтные тарифы (`free`, `pro`, `premium`).
- Импортировать текущих платящих пользователей из Payment Service отдельным скриптом.
- Согласовать с Auth Service наличие прав `user.subscription`, `subscription.admin`.

## 16. Стратегия тестирования

- Unit-тесты: сервисы обработки событий и расчёта лимитов.
- Feature-тесты: сценарии API (успех, авторизация, валидация).
- Интеграционные тесты: мок RabbitMQ и ответы Payment Service.
- Контрактные тесты: проверка схемы сообщений через JSON Schema.
- Нагрузочные тесты: подтверждение лимитов производительности (k6/JMeter).

## 17. Критерии приёмки

- Активация подписки обновляет БД, кэш и публикует событие ≤1 секунды на стенде.
- Все эндпоинты описаны в OpenAPI и проходят автотесты.
- Идемпотентность подтверждена: повторный `event_id` не создаёт дубликатов.
- Лимиты выдерживают минимум два конкурентных запроса без гонок.
- Правила алертинга настроены и срабатывают в тестовом сценарии.

## 18. Риски и меры

- **Риск**: дублирование сообщений → Мера: проверка `correlation_id`, дедупликация.
- **Риск**: перерасход лимитов из-за гонок → Мера: атомарные операции в Redis + fallback в БД.
- **Риск**: несинхронные тарифы с Payment Service → Мера: reconciliation job + админский override.
- **Риск**: рассинхрон кэша → Мера: TTL + событийная инвалидация.

## 19. Открытые вопросы

- Требуется подтвердить дефолтные длительности trial по тарифам.
- Нужен ли обязательный комментарий при ручной выдаче тарифа администратором?
- Какой SLA ожидается от Notification Service (sync vs async)?

> Комментарий: Вопросы нужно закрыть до начала спринта.
