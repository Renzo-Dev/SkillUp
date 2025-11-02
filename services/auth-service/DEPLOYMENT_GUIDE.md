# 🚀 Руководство по развёртыванию Auth-Service

Пошаговая инструкция для запуска auth-service с RS256 JWT токенами.

## 📋 Предварительные требования

- Docker & Docker Compose
- Git
- Минимум 2GB RAM
- Порты: 80, 5432, 6379, 5672, 15672, 9000

## 🎯 Быстрый старт (5 минут)

### Шаг 1: Клонирование и настройка

```bash
# Перейти в директорию проекта
cd /path/to/SkillUp

# Создать .env файл в корне для docker-compose
cat > .env << EOF
# Database
DB_DATABASE=skillup_auth
DB_USERNAME=skillup
DB_PASSWORD=your_secure_password_here

# RabbitMQ
RABBITMQ_USER=skillup
RABBITMQ_PASSWORD=your_secure_password_here
EOF
```

### Шаг 2: Генерация JWT ключей

```bash
# Перейти в auth-service
cd services/auth-service/src

# Установить зависимости (если ещё не установлены)
docker run --rm -v $(pwd):/app composer:latest install

# Создать .env для Laravel (скопировать из шаблона)
cp ../ENV_TEMPLATE.md .env.temp
# Отредактировать .env (заполнить необходимые переменные)
nano .env

# Или использовать минимальную конфигурацию
cat > .env << EOF
APP_NAME="SkillUp Auth Service"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=skillup_auth
DB_USERNAME=skillup
DB_PASSWORD=your_secure_password_here

REDIS_HOST=redis
REDIS_PORT=6379

JWT_ALGO=RS256
JWT_TTL=60
JWT_REFRESH_TTL=10080
JWT_BLACKLIST_ENABLED=true

RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=skillup
RABBITMQ_PASSWORD=your_secure_password_here

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
EOF

# Сгенерировать APP_KEY
docker run --rm -v $(pwd):/app -w /app php:8.2-cli php artisan key:generate
```

### Шаг 3: Запуск сервисов

```bash
# Вернуться в корень проекта
cd ../../..

# Запустить зависимости (PostgreSQL, Redis, RabbitMQ)
docker-compose up -d postgres redis rabbitmq

# Подождать инициализации (30 секунд)
sleep 30

# Проверить статус
docker-compose ps
```

### Шаг 4: Генерация RSA ключей

```bash
# Выполнить команду генерации ключей внутри временного контейнера
docker run --rm \
  -v $(pwd)/services/auth-service/src:/var/www/html \
  -w /var/www/html \
  php:8.2-cli \
  php artisan jwt:generate-keys

# ИЛИ запустить auth-service и выполнить команду
docker-compose up -d auth-service
sleep 5
docker-compose exec auth-service php artisan jwt:generate-keys

# Создать директорию для Docker монтирования
mkdir -p services/auth-service/jwt

# Скопировать ключи
docker-compose exec auth-service cp /var/www/html/storage/jwt/private.pem /tmp/
docker-compose exec auth-service cp /var/www/html/storage/jwt/public.pem /tmp/
docker cp auth-service:/tmp/private.pem services/auth-service/jwt/
docker cp auth-service:/tmp/public.pem services/auth-service/jwt/

# Установить права
chmod 600 services/auth-service/jwt/private.pem
chmod 644 services/auth-service/jwt/public.pem
```

### Шаг 5: Запуск и миграция

```bash
# Перезапустить auth-service с правильными volume монтированиями
docker-compose down auth-service
docker-compose up -d auth-service

# Выполнить миграции
docker-compose exec auth-service php artisan migrate --force

# Проверить логи
docker-compose logs -f auth-service
```

### Шаг 6: Проверка работы

```bash
# Health check
curl http://localhost:80/api/health
# Ожидается: {"status":"OK","timestamp":"...","service":"auth-service"}

# Получение публичного ключа
curl http://localhost:80/api/internal/jwt/public-key
# Ожидается: содержимое PEM файла

# Проверка internal validation endpoint
curl http://localhost:80/api/internal/health
# Ожидается: {"status":"OK","service":"auth-service-internal",...}
```

## 🔧 Полная установка (Development)

### 1. Подготовка окружения

```bash
# Установить зависимости локально (опционально)
cd services/auth-service/src
composer install
npm install

# Сгенерировать ключи приложения
php artisan key:generate
```

### 2. Настройка базы данных

```bash
# Запустить PostgreSQL
docker-compose up -d postgres

# Создать базу данных (если не создалась автоматически)
docker-compose exec postgres psql -U skillup -c "CREATE DATABASE skillup_auth;"

# Выполнить миграции
docker-compose exec auth-service php artisan migrate

# Заполнить тестовыми данными (опционально)
docker-compose exec auth-service php artisan db:seed
```

### 3. Настройка Redis

```bash
# Проверить подключение к Redis
docker-compose exec auth-service php artisan tinker
# В tinker: Redis::ping()
# Ожидается: "PONG"
```

### 4. Настройка RabbitMQ

```bash
# Открыть Management UI
# http://localhost:15672
# Login: skillup / Password: your_password

# Создать очереди вручную (опционально, создадутся автоматически)
# - user.events
# - email.verification
```

### 5. Тестирование

```bash
# Запустить тесты
docker-compose exec auth-service php artisan test

# Запустить конкретный тест
docker-compose exec auth-service php artisan test --filter=AuthTest
```

## 📊 Production Deployment

### 1. Переменные окружения

```env
# Production .env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning

# Использовать сильные пароли
DB_PASSWORD=<strong-password>
RABBITMQ_PASSWORD=<strong-password>
REDIS_PASSWORD=<strong-password>

# JWT ключи из секретного хранилища
JWT_PUBLIC_KEY=file:///run/secrets/jwt_public_key
JWT_PRIVATE_KEY=file:///run/secrets/jwt_private_key
```

### 2. Docker Secrets

```yaml
# docker-compose.prod.yml
services:
  auth-service:
    secrets:
      - jwt_private_key
      - jwt_public_key
    environment:
      - JWT_PRIVATE_KEY=/run/secrets/jwt_private_key
      - JWT_PUBLIC_KEY=/run/secrets/jwt_public_key

secrets:
  jwt_private_key:
    external: true
  jwt_public_key:
    external: true
```

### 3. Мониторинг

```bash
# Настроить health checks
# Prometheus + Grafana
# ELK Stack для логов
# Sentry для ошибок
```

### 4. Backup

```bash
# Backup базы данных
docker-compose exec postgres pg_dump -U skillup skillup_auth > backup.sql

# Backup ключей (зашифровано!)
tar -czf jwt-keys-backup.tar.gz services/auth-service/jwt/
gpg --encrypt --recipient admin@skillup.com jwt-keys-backup.tar.gz
```

## 🐛 Troubleshooting

### Проблема: "Connection refused" к PostgreSQL

```bash
# Проверить статус
docker-compose ps postgres

# Проверить логи
docker-compose logs postgres

# Пересоздать контейнер
docker-compose down postgres
docker-compose up -d postgres
```

### Проблема: "JWT ключ не найден"

```bash
# Проверить наличие ключей
ls -la services/auth-service/jwt/
docker-compose exec auth-service ls -la /var/www/html/storage/jwt/

# Пересоздать ключи
docker-compose exec auth-service php artisan jwt:generate-keys --force

# Скопировать для Docker
docker cp auth-service:/var/www/html/storage/jwt/private.pem services/auth-service/jwt/
docker cp auth-service:/var/www/html/storage/jwt/public.pem services/auth-service/jwt/
```

### Проблема: "Permission denied" на ключах

```bash
# Исправить права
sudo chmod 600 services/auth-service/jwt/private.pem
sudo chmod 644 services/auth-service/jwt/public.pem

# Проверить владельца
ls -la services/auth-service/jwt/

# Изменить владельца (если нужно)
sudo chown $USER:$USER services/auth-service/jwt/*.pem
```

### Проблема: Миграции не применяются

```bash
# Проверить подключение к БД
docker-compose exec auth-service php artisan tinker
# DB::connection()->getPdo();

# Применить миграции force
docker-compose exec auth-service php artisan migrate --force

# Откатить и применить заново
docker-compose exec auth-service php artisan migrate:fresh --force
```

### Проблема: RabbitMQ не принимает сообщения

```bash
# Проверить статус
docker-compose ps rabbitmq
docker-compose logs rabbitmq

# Проверить очереди в Management UI
# http://localhost:15672

# Пересоздать контейнер
docker-compose restart rabbitmq
```

## 📝 Checklist развёртывания

- [ ] Установлены Docker и Docker Compose
- [ ] Созданы .env файлы (корень + auth-service/src)
- [ ] Сгенерированы сильные пароли для БД и RabbitMQ
- [ ] Запущены сервисы зависимостей (postgres, redis, rabbitmq)
- [ ] Сгенерированы RSA ключи для JWT
- [ ] Скопированы ключи в директорию jwt/
- [ ] Установлены правильные права на ключи (600/644)
- [ ] Выполнены миграции базы данных
- [ ] Проверен health check (/api/health)
- [ ] Проверен internal endpoint (/api/internal/jwt/validate)
- [ ] Проверена публикация событий в RabbitMQ
- [ ] Настроен мониторинг и алерты (production)
- [ ] Настроен backup (production)

## 🎉 Готово!

Auth-Service успешно развёрнут и готов к использованию!

### Следующие шаги:

1. Запустить Frontend (Nuxt 4)
2. Настроить API Gateway (Nginx)
3. Запустить AI-Service
4. Интегрировать другие микросервисы

### Полезные команды:

```bash
# Просмотр логов
docker-compose logs -f auth-service

# Вход в контейнер
docker-compose exec auth-service bash

# Очистка кешей
docker-compose exec auth-service php artisan cache:clear
docker-compose exec auth-service php artisan config:clear

# Перезапуск всех сервисов
docker-compose restart

# Остановка всех сервисов
docker-compose down

# Полная очистка (включая volumes)
docker-compose down -v
```

---

**Документация:**
- [README.md](./README.md) — Основная документация
- [RS256_MIGRATION_GUIDE.md](./RS256_MIGRATION_GUIDE.md) — Миграция на RS256
- [ENV_TEMPLATE.md](./ENV_TEMPLATE.md) — Шаблон переменных окружения

**Поддержка:** support@skillup.com  
**Версия:** 1.0.0

