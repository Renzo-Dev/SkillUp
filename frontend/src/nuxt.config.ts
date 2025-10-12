// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxt/eslint', '@nuxt/fonts', '@nuxt/image', '@pinia/nuxt'],
  
  // Настройка проксирования API запросов
  nitro: {
    devProxy: {
      '/api': {
        target: 'http://localhost:80',
        changeOrigin: true,
        prependPath: true
      }
    }
  },
  
  // Настройка для production
  runtimeConfig: {
    public: {
      apiBase: '/api'
    }
  }
})