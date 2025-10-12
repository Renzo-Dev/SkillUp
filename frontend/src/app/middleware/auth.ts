export default defineNuxtRouteMiddleware(async (_to) => {
  if (import.meta.client) {
    const { useAuthStore } = await import('../stores/auth.store')
    const authStore = useAuthStore()
    
    if (!authStore.isAuthenticated) {
      return navigateTo('/login')
    }
  }
})
