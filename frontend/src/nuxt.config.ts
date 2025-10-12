// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  // Автоматический перезапуск при изменениях
  vite: {
    server: {
      watch: {
        usePolling: true,
      },
    },
  },

  // HMR настройки
  devServer: {
    port: 3000,
    host: '0.0.0.0',
  },

  // Настройки для работы в Docker
  nitro: {
    devProxy: {
      '/api': {
        target: 'http://nginx_gateway:80/api',
        changeOrigin: true,
      },
    },
  },

  // Настройки для работы через nginx прокси
  app: {
    baseURL: '/',
    cdnURL: '/',
  },

  // Настройки для dev-сервера
  devServer: {
    port: 3000,
    host: '0.0.0.0',
    // Разрешаем все хосты для работы через nginx
    allowedHosts: 'all',
  },

  // Исправляем проблемы с виртуальными модулями
  vite: {
    server: {
      watch: {
        usePolling: true,
      },
      // Настройки для работы с виртуальными модулями
      fs: {
        allow: ['..', '/app'],
      },
    },
  },

  css: ['~/assets/styles/main.scss', 'aos/dist/aos.css'],

  modules: [
    '@nuxt/eslint',
    '@nuxt/fonts',
    '@nuxt/image',
    '@nuxt/scripts',
    '@vueuse/motion/nuxt',
  ],

  fonts: {
    families: [
      { name: 'Inter', provider: 'google', weights: [400, 500, 600, 700] },
      { name: 'Space Grotesk', provider: 'google', weights: [500, 600, 700] },
    ],
  },

  image: {
    domains: [
      'images.unsplash.com',
      'cdn.midjourney.com',
      'res.cloudinary.com',
      'cdn.jsdelivr.net',
    ],
  },
})
