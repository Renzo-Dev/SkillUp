# SkillUp Gateway + Auth-Service + Subscribe-Service + AI-Service Architecture

## 1. Основные цели

* **API Gateway (Nginx)**: единая точка входа, проверка JWT через `auth-service`, маршрутизация к микросервисам.
* **auth-service**: аутентификация, выдача и ревокация JWT, предоставление метаданных токена для gateway.
* **subscribe-service**: управление подписками, валидация прав на функции, SLA и лимиты.
* **ai-service**: генерация текстов на основе промтов, синхронная и асинхронная обработка запросов.

---

## 2. Поток работы пользователя

1. **Пользователь входит в SkillUp**

   * **Frontend** → `auth-service` → проверка логина/пароля → выдача JWT и refresh токена.
   * `auth-service` сохраняет метаданные токена (expiry, scopes, subscription-tier) в Redis.
   * **Frontend** хранит JWT и использует его для всех последующих запросов.

2. **Пользователь выбирает тему и функцию**

   * Фронтенд отправляет запрос на `/generate` с JWT, темой и функцией.

3. **API Gateway / Проверка доступа**

   * **API Gateway** (Nginx) принимает запрос, выполняет TLS и rate limiting.
   * Через `auth_request` делает внутренний вызов в `auth-service` (`/internal/jwt/validate`).
   * `auth-service` валидирует подпись JWT, срок, scopes, статус пользователя и возвращает payload.
   * При ошибке gateway отвечает 401/403 без проксирования вниз.
   * При успехе gateway проксирует запрос в `ai-service`, добавляя заголовок с user-id, scopes и tier.

4. **AI-Service и Subscribe-Service**

   * `ai-service` проверяет бизнес-лимиты: обращается в `subscribe-service` за актуальным статусом подписки.
   * При отказе подписки gateway возвращает сообщение «недоступно, оформи подписку».
   * При успехе `ai-service` формирует промт через `prompt-service`.
   * Для коротких ответов синхронно вызывает AI Engine.
   * Для длинных запросов отправляет задачу в RabbitMQ (`generate.long`) и возвращает пользователю `202 Accepted` с task-id.

5. **Асинхронная обработка**

   * Worker AI Engine слушает очередь RabbitMQ, обрабатывает запрос и сохраняет результат в PostgreSQL и Redis.
   * После генерации worker отправляет событие в `notification-service` (WebSocket/push/email).

6. **Возврат результата**

   * Синхронно: `ai-service` → API Gateway → Frontend → пользователь получает текст.
   * Асинхронно: Frontend периодически запрашивает `/generate/{task-id}` или получает уведомление и забирает готовый текст из Redis/PostgreSQL.

---

## 3. Взаимодействие микросервисов

| Сервис                | Ключевые функции                                                    | Взаимодействие                                    |
| --------------------- | ------------------------------------------------------------------- | ------------------------------------------------- |
| **API Gateway**       | TLS, rate limit, проверка JWT через `auth-service`, circuit breaker | Frontend, auth-service, ai-service                |
| **auth-service**      | Аутентификация, выдача/ревокация JWT, валидация                     | Frontend, gateway, ai-service                     |
| **subscribe-service** | Проверка статуса подписки, доступных функций, лимитов               | ai-service, payment-service                       |
| **ai-service**        | Генерация текста, постановка задач в RabbitMQ, кеширование          | subscribe-service, RabbitMQ, Redis/PostgreSQL     |
| **RabbitMQ**          | Очереди асинхронной генерации, ретраи                              | ai-service, worker AI Engine                      |

---

## 4. Асинхронность и масштабирование

* **RabbitMQ** используется для длинных и ресурсоёмких генераций, ретраев, приоритезации запросов.
  * Frontend → API Gateway → `ai-service` → RabbitMQ (`generate.long`).
  * Worker → AI Engine → Redis/PostgreSQL → `notification-service` → Frontend.
  * Отдельная очередь `generate.retry` для повторов при сбоях AI Engine.
* Горизонтальное масштабирование gateway и `ai-service` за счёт stateless-инстансов.
* Redis хранит короткие результаты, метаданные JWT и кэш подписок.

---

## 5. Дополнительные моменты

* **Redis**: кэширование запросов, хранение метаданных JWT, быстрый доступ к результатам задач.
* **PostgreSQL**: хранение пользователей, подписок, истории генераций, статусов задач из очередей.
* **Nginx**: API Gateway с `auth_request`, rate limit, circuit breaker, мониторинг ошибок.
* **Observability**: Prometheus + Grafana, метрики очередей RabbitMQ, latency gateway и ошибок валидации JWT.
