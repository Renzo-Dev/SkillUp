# Auth Service — Обзор

## Назначение
- Центральный сервис аутентификации SkillUp: регистрация, логин, управление JWT/refresh токенами, email-верификация.
- Обеспечивает консистентные события управления учётками для внутренних сервисов через RabbitMQ.

## Архитектура
- **Стек**: Laravel 12 (PHP 8.2), PostgreSQL, Redis (кеш/очереди + метаданные JWT), RabbitMQ (event bus).
- **Слои**: контроллеры ⇨ requests ⇨ DTO ⇨ сервисы ⇨ репозитории ⇨ модели.
- **API Gateway интеграция**: Nginx с `auth_request` вызывает внутренний эндпоинт `auth-service` для валидации JWT; успешный ответ содержит payload пользователя для проксирования downstream. *(коммент: отражаю связку с gateway)*
- **Интерфейсы**: `App\Contracts\*` отделяют доменную логику от реализаций (позволяет заменять сервисы, подключать Mock в тестах).
- **DI контейнер**: биндинги в `AppServiceProvider`.
- **Обработчики**: HTTP API (`routes/api.php`, `routes/internal.php`) + artisan-команды (потребители событий отключены логикой).

- **AuthController/AuthService** — логин, регистрация, `me`, `logout`, refresh.
- **TokenService/JwtService/RefreshTokenService** — генерация и ротация JWT/refresh, отзыв, ревокация всех токенов пользователя.
- **InternalJwtValidationController/InternalJwtValidationService** — быстрый ответ на запросы gateway: проверяет подпись, TTL, blacklist и возвращает сведения о пользователе/подписке из Redis. *(коммент: фиксирую новый контроллер)*
- **EmailVerificationService/Controller** — выпуск токенов подтверждения email, публикация событий, проверка статуса.
- **UserService/UserRepository** — CRUD над пользователями, проверка паролей, обновление `last_login_at`.
- **BlackListService** — управление JWT blacklist (через Tymon JWT Auth).
- **JwtMetadataCacheService** — кеширует claims (user-id, scopes, subscription-tier, email_verified) в Redis для ускоренной валидации gateway. *(коммент: добавляю сервис кеша)*
- **CustomLoggerService** — унифицированные логи `controller/service`.
- **RabbitMQService** — публикация доменных событий в очереди (`user.events`, `email.verification`).

## Используемые события (RabbitMQ)
- `user.registered` — после успешной регистрации.
- `user.logged_in` — успешный логин.
- `user.logged_out` — успешный logout (при вызове из контроллера).
- `email.verification.requested` — создан токен подтверждения/ресенд.
- `email.verification.completed` — email подтверждён.
- `email.verification.resent` — повторная отправка токена.

- Таблица `users`: расширена полями `is_active`, `last_login_at`, `email_verified_at`.
- Таблица `refresh_tokens`: уникальные refresh-токены (expires_at, каскадное удаление).
- Таблица `email_verification_tokens`: токены на 24 часа, фиксирует `verified_at`.
- Redis — обязателен для JWT blacklist (Tymon) и для хранения метаданных JWT (`auth:jwt:{jti}`) с TTL = `JWT_TTL`. *(коммент: уточняю хранение)*

- `POST /api/auth/register` — регистрация; возвращает `AuthResponseDTO` (user + token pair).
- `POST /api/auth/login` — логин пользователя.
- `POST /api/auth/refresh` — выдаёт новую пару токенов по refresh.
- `POST /api/auth/logout` — защищённый route (middleware `guard.jwt`), отзывает токен.
- `GET /api/auth/me` — возвращает `UserDTO` текущего пользователя.
- `POST /api/auth/verify-email` — подтверждение email по токену.
- `POST /api/auth/resend-verification` — повторный токен верификации.
- `GET /api/auth/verification-status` — статус email.
- `GET /api/health`, `GET /api/status` — сервисные проверки.
- `GET /internal/jwt/validate` — внутренний эндпоинт для gateway (возвращает 204 + заголовки `X-User-Id`, `X-Scopes`, `X-Subscription-Tier`, `X-Email-Verified`; при ошибке 401/403). *(коммент: описываю новый endpoint)*

## Конфигурация и переменные окружения
- **База**: `DB_*` (PostgreSQL), миграции присутствуют.
- **JWT**: `JWT_SECRET`, `JWT_TTL`, `JWT_REFRESH_TTL`, `JWT_BLACKLIST_ENABLED=true`.
- **RabbitMQ**: `RABBITMQ_HOST`, `PORT`, `USER`, `PASSWORD`, `VHOST`.
- **Очереди/кеш**: `QUEUE_CONNECTION=redis`, `CACHE_DRIVER=redis` (по умолчанию Laravel).
- **Почта**: события отправляются через RabbitMQ, непосредственный email-transport на стороне другого сервиса.
- **App**: `APP_URL`, `FRONTEND_URL` (используется в ссылках подтверждения).

## Точки интеграции
- **RabbitMQ** — публикация событий доменного уровня.
- **Redis** — кеш, очереди, JWT blacklist, быстрый доступ к метаданным JWT.
- **PostgreSQL** — персистентное хранение пользователей/токенов.
- **Внешние сервисы** (через события) — Email-сервис, аналитика, уведомления.

- Пароли хешируются Laravel кастом-кастом `hashed`.
- JWT blacklist для ревокации токенов (`logout`, `revokeAll`).
- Redis-кеш метаданных JWT обновляется при логине/refresh, очищается при logout/revoke. *(коммент: фиксирую синхронизацию)*
- Refresh токены в БД с TTL, очистка/ревокация (непосредственно `cleanupExpiredTokens`).
- Email верификация (24 часа TTL, повторы очищают предыдущие токены).
- Custom middleware `guard.jwt` проверяет токен, активность пользователя, логирует попытки.
- API ошибок стандартизированы через `ApiErrorResource`.
- Gateway обращается к `GET /internal/jwt/validate` по HTTPS, запросы ограничены whitelist IP или mTLS. *(коммент: безопасность связки)*

## Диагностика и логирование
- Логирование через `CustomLoggerService` (+ стандартный Laravel log).
- События RabbitMQ логируются (`info`, `warning`, `error`).
- Health/status endpoints для L7 checks.

## Обнаруженные пробелы и рекомендации
- **Документация**: отсутствовали README/архитектурные описания (закрываем текущим документом).
- **.env.example**: нет шаблона с переменными окружения (нужно добавить).
- **Rate limiting**: нет защиты от brute-force (добавить middleware `throttle` + капча на фронте).
- **Парольная политика**: правило `StrongPassword` есть, но необходимо описать требования в документации и покрыть тестами.
- **Refresh Token TTL**: значение берётся из `env('JWT_REFRESH_TTL')` в минутах — стоит синхронизировать с конфигом `config/jwt.php` (дни/минуты).
- **Тесты**: ограниченный охват (нет e2e/feature тестов на email flow и refresh).
- **Обработка logout**: публикация события `user.logged_out` не вызывается (AuthService не вызывает `publishUserLoggedOut`). Решить, нужен ли event.
- **Consumer команда**: присутствует `rabbitmq:consume-user-events`, но реализация `RabbitMQService::consume` роет исключение — уточнить сценарий, либо удалить команду.
- **Мониторинг**: не реализовано логическое отслеживание успех/ошибок (можно оставить для системных метрик вне сервиса).

## Эксплуатация
- **Миграции**: `php artisan migrate` перед запуском.
- **Инициализация JWT**: `php artisan jwt:secret`.
- **Очистка**: `php artisan refresh-tokens:cleanup` (если планируется отдельная команда) — такой команды нет, требуется создать крон/command.
- **Запуск локально**: используйте Dockerfile (PHP-FPM + supervisor) или Laravel Sail.
- **Очереди**: запустить `php artisan queue:listen` при необходимости асинхронных задач (на текущий момент сервис публикует события, не потребляет).
- **Логи**: стандартные в `storage/logs/laravel.log` + централизовать через Stackdriver/ELK по инфраструктуре.

## Следующие шаги
- Добавить `.env.example` с полным списком переменных.
- Реализовать rate limiting и аудит попыток логина.
- Покрыть feature-тестами basic happy/negative flows (login, register, refresh, verify-email).
- Завершить логику публикации `user.logged_out` и/или удалить неиспользуемый consumer.
- Документировать политику хранения refresh токенов (крон очистки, количество активных токенов на пользователя).

