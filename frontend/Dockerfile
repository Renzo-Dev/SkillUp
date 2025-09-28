FROM node:22-alpine

WORKDIR /app

ENV NUXT_TELEMETRY_DISABLED=1 \
    HOST=0.0.0.0 \
    PORT=3000

COPY package.json package-lock.json* pnpm-lock.yaml* bun.lockb* ./

RUN npm install -g npm@11.6.1 \
    && if [ -f package-lock.json ]; then npm ci; else npm install; fi \
    && npm cache clean --force

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "3000"]

