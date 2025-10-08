# Auth-Service — Техническое задание

## 1. Цели
- Обеспечить централизованную аутентификацию пользователей платформы SkillUp.
- Поддержать полный цикл управления учётными записями: регистрация, логин, refresh/logout, подтверждение email.
- Предоставить стандартизированные события для интеграции с другими сервисами (почтовый, аналитика, CRM).

## 2. Область работ
- Backend-сервис на Laravel 12 с REST API.
- Подключение к PostgreSQL, Redis, RabbitMQ.
- Реализация бизнес-логики управления пользователями и токенами.
- Поддержка верификации email и событийной шины.
- Подготовка документации и тестов (unit + feature).

- Регистрация: валидация, создание пользователя, генерация пары токенов, публикация `user.registered`.
- Аутентификация: проверка пароля, активности пользователя, генерация новой токен-пары, публикация `user.logged_in`.
- Refresh токен: выдача новой пары по refresh, отзыв старого refresh токена.
- Logout: отзыв access токена (JWT blacklist) и refresh токена, публикация `user.logged_out`.
- Получение текущего пользователя: возврат DTO с базовыми полями.
- Email-верификация: выпуск токена, подтверждение, повторная отправка, публикация событий `email.*`.
- Управление активностью пользователя: поддержка `is_active`, ошибки при попытке логина неактивного пользователя.
- API ошибок — единый JSON-формат, коды 4xx/5xx.
- Внутренний эндпоинт `GET /internal/jwt/validate`: принимает JWT из заголовка Authorization, валидирует подпись, TTL, blacklist, читает метаданные пользователя (scopes, subscription-tier) из Redis и возвращает 204 + заголовки `X-User-Id`, `X-Scopes`, `X-Subscription-Tier`. Ошибки 401/403 в формате JSON. *(коммент: добавляю требование к internal endpoint)*
- Очистка Redis-кеша при revoke/logout/expiry — обязательна.

- Производительность: ответы API ≤ 200мс при p95 (без внешних зависимостей). Internal endpoint должен отвечать ≤ 50мс p95 (за счёт Redis). *(коммент: уточнение SLA)*
- Масштабируемость: stateless-сервис, все состояния в БД/Redis.
- Надёжность: транзакционный персистентный слой, повторная отправка событий при ошибках RabbitMQ (ретраи на инфраструктуре).
- Безопасность: хешированные пароли, TLS на уровне API gateway, rate limiting (throttle), защита JWT blacklist, mTLS или IP whitelist для internal маршрутов.
- Логирование: использовать `CustomLoggerService`, события RabbitMQ логировать уровнями info/warning/error.
- Локализация: сообщения об ошибках на русском, хранение в ресурсах/константах.

- `POST /api/auth/register`
  - Вход: `name`, `email`, `password`, `password_confirmation`.
  - Выход: `user`, `access_token`, `refresh_token`, статус email.
- `POST /api/auth/login`
  - Вход: `email`, `password`.
  - Выход: новая пара токенов + сведения о пользователе.
- `POST /api/auth/refresh`
  - Вход: `refresh_token`.
  - Выход: новая пара токенов.
- `POST /api/auth/logout` (защищён JWT)
  - Возвращает подтверждение выхода.
- `GET /api/auth/me` (защищён JWT)
  - Возвращает DTO пользователя.
- `POST /api/auth/verify-email`
  - Вход: `token`.
- `POST /api/auth/resend-verification`
  - Вход: `email`.
- `GET /api/auth/verification-status`
  - Вход: `email`.
- `GET /api/health`, `GET /api/status` — эксплуатация.
- `GET /internal/jwt/validate` — внутренний маршрут для gateway (rate limit по IP, авторизация сервисных ключей, заголовки `X-User-Id`, `X-Scopes`, `X-Subscription-Tier`, `X-Email-Verified`).

## 6. События и интеграции
- Публикация через RabbitMQ (`user.events`, `email.verification`).
- Формат: JSON с полями `event`, `timestamp`, `service`, `data`.
- События: `user.registered`, `user.logged_in`, `user.logged_out`, `email.verification.requested`, `email.verification.completed`, `email.verification.resent`.
- Протокол: AMQP 0-9-1, подтверждение сообщений — на стороне RabbitMQ (durable очереди, persistent messages).

## 7. Данные и миграции
- `users`: id, name, email (unique), password, is_active (bool), email_verified_at, last_login_at, timestamps.
- `refresh_tokens`: id, user_id (FK), refresh_token (unique), expires_at, timestamps.
- `email_verification_tokens`: id, user_id (FK), email, token (unique), expires_at, verified_at, timestamps.
- Требуется ensure целостности (cascade delete).
- TTL для refresh/verification контролируется через конфиг (`config/jwt.php`, env).

- `APP_ENV`, `APP_DEBUG`, `APP_URL`, `FRONTEND_URL`.
- `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- `CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `REDIS_HOST`, `REDIS_PASSWORD`.
- `JWT_SECRET`, `JWT_TTL`, `JWT_REFRESH_TTL`, `JWT_BLACKLIST_ENABLED=true`.
- `JWT_CACHE_STORE`, `JWT_CACHE_PREFIX`, `JWT_CACHE_TTL`, `JWT_CACHE_MIN_TTL` — параметры хранения метаданных в Redis. *(коммент: добавляю env)*
- `INTERNAL_JWT_VALIDATE_KEY` — ключ/секрет для вызовов gateway (если используется подпись запросов).
- `RABBITMQ_HOST`, `RABBITMQ_PORT`, `RABBITMQ_USER`, `RABBITMQ_PASSWORD`, `RABBITMQ_VHOST`.
- Ключи почтового сервиса — если требуется прямое подключение (сейчас не используется).

## 9. Бизнес-правила
- Один email — одна учётка.
- Пароль должен удовлетворять правилам `StrongPassword` (описать в отдельном документе).
- Регистрация автоматически активирует учётку (`is_active=true`).
- Попытки логина неактивного пользователя отклоняются с ошибкой 401.
- Refresh токен недействителен после использования; хранение ограниченной истории (максимум N токенов — определить, по умолчанию все активные).
- Email считается подтверждённым при наличии `email_verified_at` или подтверждённого токена.

## 10. Тестирование
- Unit: DTO, сервисы (`AuthService`, `TokenService`, `EmailVerificationService`).
- Feature: регистрация, логин, refresh, logout, verify, resend, status.
- Negative cases: неправильный пароль, истёкший токен, невалидные данные, неактивный пользователь.
- Интеграционные: публикация событий (использовать mock RabbitMQ).
- Smoke: `health/status`.

## 11. Развёртывание и эксплуатация
- Миграции перед запуском.
- Инициализация JWT ключа (`artisan jwt:secret`).
- Запуск очередей при необходимости (`queue:listen`) — хотя сервис сам события не потребляет.
- Плановая очистка refresh/email токенов (artisan-команда по расписанию) — разработать.
- Настроить мониторинг доступности через `/api/health`.

## 12. Риски и допущения
- Без rate limiting возможны атаки brute-force — требуется реализовать.
- При недоступности RabbitMQ события теряются (нужно предусмотреть ретраи/логирование).
- Ожидается внешняя система отправки писем, auth-service сам письма не шлёт.
- Требуется централизованная конфигурация `.env`, в репозитории должен быть шаблон.

## 13. Критерии готовности (Definition of Done)
- Все API методы реализованы, покрыты тестами и задокументированы.
- JWT/refresh токены работают по описанному сценарию, logout отзывает токены.
- Email-верификация полностью проходит, события публикуются.
- Логи и ошибки имеют единый формат, JWT middleware защищает приватные маршруты.
- Документация (`overview`, `TZ`) актуальна, `.env.example` присутствует.

