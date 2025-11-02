export default defineNuxtPlugin(async () => {
  if (import.meta.client) {
    const { useAuthStore } = await import('../stores/auth.store')
    const authStore = useAuthStore()
    
    // Инициализируем store при загрузке приложения
    await authStore.initialize()
  }
})
