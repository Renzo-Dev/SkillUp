# Node 22 Alpine для Nuxt 4
FROM node:22-alpine

# Устанавливаем необходимые пакеты для Nuxt
RUN apk add --no-cache \
    git \
    python3 \
    make \
    g++

# Рабочая директория
WORKDIR /var/www/

# Экспонируем порты для dev и production
EXPOSE 3000 3001

# Переменные окружения для Nuxt
ENV NODE_ENV=development
ENV NUXT_HOST=0.0.0.0
ENV NUXT_PORT=3000

# Автоматически ставим зависимости и запускаем дев-сервер
CMD ["sh", "-c", "npm install && npm run dev"]
