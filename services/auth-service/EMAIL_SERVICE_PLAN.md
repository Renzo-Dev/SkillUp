# План сервиса почты для подтверждения email

## Обзор
Реализация сервиса для отправки писем подтверждения email согласно интерфейсу `EmailVerificationServiceInterface` с временными токенами и управлением верификацией пользователей.

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

### 3. Сервисы (согласно EmailVerificationServiceInterface)
- **EmailVerificationService** - реализация `EmailVerificationServiceInterface`
  - `generateEmailVerificationToken()` - генерация токена
  - `verifyEmail()` - подтверждение email по токену
  - `isTokenExpired()` - проверка истечения токена
  - `cleanupExpiredTokens()` - очистка истекших токенов

### 4. Контроллеры
- **EmailVerificationController** - обработка запросов верификации

## Поэтапная реализация

### Этап 1: База данных
1. Создать миграцию `create_email_verification_tokens_table`

### Этап 2: Модели
1. Создать модель `EmailVerification`
2. Добавить отношения в модель `User`

### Этап 3: Контроллеры и маршруты

1. **Маршруты**
   ```php
   POST /api/email/send-verification
   GET  /api/email/verify/{token}
   POST /api/email/resend-verification
   ```

### Этап 4: Шаблоны писем
1. HTML шаблон для письма подтверждения
2. Текстовая версия письма
3. Локализация (русский/английский)

## Конфигурация

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

### Возможные ошибки
1. **Email уже подтвержден**
2. **Токен истек**
3. **Неверный токен**
4. **Превышен лимит отправки**
5. **Email не найден**
