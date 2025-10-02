# План сервиса почты для подтверждения email

## Обзор
Создание сервиса для отправки писем подтверждения email с временными токенами и управлением верификацией пользователей.

## Архитектура

### 1. База данных
- **Таблица `email_verifications`**
  - `id` (primary key)
  - `user_id` (foreign key to users)
  - `email` (string, email для подтверждения)
  - `token` (string, уникальный токен)
  - `expires_at` (timestamp, время истечения)
  - `verified_at` (timestamp, время подтверждения)
  - `created_at`, `updated_at`

### 2. Модели
- **EmailVerification** - модель для работы с токенами подтверждения
- **User** - расширение существующей модели для email verification

### 3. Сервисы
- **EmailService** - отправка писем подтверждения
- **EmailVerificationService** - логика верификации

### 4. Контроллеры
- **EmailVerificationController** - обработка запросов верификации

## Поэтапная реализация

### Этап 1: База данных
1. Создать миграцию `create_email_verifications_table`
2. Добавить поле `email_verified_at` в таблицу users (если нужно)

### Этап 2: Модели
1. Создать модель `EmailVerification`
2. Добавить отношения в модель `User`
3. Создать фабрику для тестирования

### Этап 3: Сервисы
1. **EmailService**
   - `sendVerificationEmail($user, $token)`
   - `sendWelcomeEmail($user)`
   - Интеграция с SMTP/Mailgun/SendGrid

2. **EmailVerificationService**
   - `generateToken($user, $email)`
   - `verifyToken($token)`
   - `isTokenExpired($token)`
   - `cleanupExpiredTokens()`

### Этап 4: Контроллеры и маршруты
1. **EmailVerificationController**
   - `sendVerification()` - отправка письма
   - `verify($token)` - подтверждение по токену
   - `resend()` - повторная отправка

2. **Маршруты**
   ```php
   POST /api/email/send-verification
   GET  /api/email/verify/{token}
   POST /api/email/resend-verification
   ```

### Этап 5: Middleware и валидация
1. **EmailVerificationMiddleware** - проверка верификации
2. **Валидация токенов**
3. **Обработка ошибок**

### Этап 6: Шаблоны писем
1. HTML шаблон для письма подтверждения
2. Текстовая версия письма
3. Локализация (русский/английский)

## Конфигурация

### Переменные окружения
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@skillup.com
MAIL_FROM_NAME="SkillUp"

# Email verification settings
EMAIL_VERIFICATION_EXPIRES_HOURS=24
EMAIL_VERIFICATION_TOKEN_LENGTH=64
```

### Настройки токенов
- **Длина токена**: 64 символа
- **Время жизни**: 24 часа
- **Алгоритм**: SHA-256
- **Формат**: Base64 URL-safe

## Безопасность

### Защита от атак
1. **Rate limiting** - ограничение частоты отправки
2. **CSRF protection** - защита от CSRF атак
3. **Token validation** - проверка валидности токенов
4. **Email validation** - валидация email адресов

### Логирование
1. Логирование попыток верификации
2. Отслеживание подозрительной активности
3. Аудит безопасности

## API Endpoints

### Отправка письма подтверждения
```http
POST /api/email/send-verification
Content-Type: application/json

{
    "email": "user@example.com"
}
```

### Подтверждение email
```http
GET /api/email/verify/{token}
```

### Повторная отправка
```http
POST /api/email/resend-verification
Content-Type: application/json

{
    "email": "user@example.com"
}
```

## Обработка ошибок

### Возможные ошибки
1. **Email уже подтвержден**
2. **Токен истек**
3. **Неверный токен**
4. **Превышен лимит отправки**
5. **Email не найден**

### HTTP статус коды
- `200` - успешная верификация
- `400` - неверные данные
- `404` - токен не найден
- `422` - ошибка валидации
- `429` - превышен лимит запросов

## Тестирование

### Unit тесты
1. Тестирование генерации токенов
2. Тестирование валидации
3. Тестирование истечения токенов

### Integration тесты
1. Тестирование полного flow верификации
2. Тестирование отправки писем
3. Тестирование API endpoints

## Мониторинг

### Метрики
1. Количество отправленных писем
2. Процент успешных верификаций
3. Время обработки запросов
4. Ошибки отправки писем

### Алерты
1. Высокий процент неудачных верификаций
2. Проблемы с отправкой писем
3. Подозрительная активность

## Развертывание

### Docker
1. Обновление Dockerfile для email сервиса
2. Настройка переменных окружения
3. Конфигурация SMTP

### Миграции
1. Запуск миграций базы данных
2. Создание индексов для производительности
3. Настройка cleanup задач

## Дальнейшее развитие

### Возможные улучшения
1. **Двухфакторная аутентификация** через email
2. **Уведомления** о смене email
3. **Массовая рассылка** для администраторов
4. **Аналитика** по email активности
5. **Интеграция** с внешними email провайдерами

### Масштабирование
1. **Очереди** для асинхронной отправки
2. **Кэширование** токенов
3. **Горизонтальное масштабирование**
4. **CDN** для статических ресурсов писем
