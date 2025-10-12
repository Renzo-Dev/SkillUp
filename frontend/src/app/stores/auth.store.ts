import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

// Типы
interface User {
  id: number
  name: string
  email: string
  is_active: boolean
  last_login_at: string | null
  created_at: string
  updated_at: string
}

interface AuthResponse {
  user: User
  access_token: string
  refresh_token: string
}

interface _ApiErrorResponse {
  data?: {
    success: boolean
    message: string
    code: number
    errors: unknown[]
  }
  message?: string
  errors?: Record<string, string[]>
}

// API url - временно используем полный URL для отладки
const API_BASE = 'http://localhost/api/auth'

export const useAuthStore = defineStore('auth', () => {
  // state
  const user = ref<User | null>(null)
  const accessToken = ref<string>('')
  const refreshToken = ref<string>('')
  const error = ref<string>('')
  const loading = ref<boolean>(false)
  const router = useRouter()

  // авторизация
  const isAuthenticated = computed(() => !!user.value && !!accessToken.value)

  // api запрос через $fetch
  const apiRequest = async (endpoint: string, options: RequestInit = {}) => {
    const url = `${API_BASE}${endpoint}`
    console.log('API Request URL:', url) // Логируем URL для отладки
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      ...(options.headers as Record<string, string>)
    }

    // токен если нужно
    if (accessToken.value && !endpoint.includes('/login') && !endpoint.includes('/register')) {
      headers.Authorization = `Bearer ${accessToken.value}`
    }

    try {
      const data = await $fetch(url, {
        method: options.method as 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH',
        body: options.body,
        headers,
        onResponseError({ response }) {
          // обработка ошибок HTTP
          let errorMessage = 'Ошибка'
          const responseData = response._data as Record<string, unknown>
          
          if (responseData && typeof responseData === 'object') {
            const dataObj = responseData.data as Record<string, unknown>
            const messageObj = responseData.message as string
            if (dataObj?.message) errorMessage = dataObj.message as string
            else if (messageObj) errorMessage = messageObj
          }
          
          if (response.status === 401 || (responseData && responseData.data && (responseData.data as Record<string, unknown>).code === 401)) {
            errorMessage = 'Неверный логин или пароль'
          }
          
          throw new Error(errorMessage)
        }
      })

      // проверка на ошибки в ответе
      if (data && typeof data === 'object' && 'data' in data && (data as Record<string, unknown>).data && (data as Record<string, unknown>).data && typeof (data as Record<string, unknown>).data === 'object' && (data as Record<string, unknown>).data && 'success' in ((data as Record<string, unknown>).data as Record<string, unknown>) && ((data as Record<string, unknown>).data as Record<string, unknown>).success === false) {
        const dataObj = (data as Record<string, unknown>).data as Record<string, unknown>
        throw new Error((dataObj.message as string) || 'Ошибка')
      }

      return data
    } catch (err: unknown) {
      // перебрасываем ошибку с правильным сообщением
      const errorMessage = err instanceof Error ? err.message : 'Ошибка запроса'
      throw new Error(errorMessage)
    }
  }

  // сохранить токены
  const saveTokens = (tokens: { access_token: string; refresh_token: string }) => {
    accessToken.value = tokens.access_token
    refreshToken.value = tokens.refresh_token
    localStorage.setItem('access_token', tokens.access_token)
    localStorage.setItem('refresh_token', tokens.refresh_token)
  }

  // загрузить токены
  const loadTokens = () => {
    const access = localStorage.getItem('access_token')
    const refresh = localStorage.getItem('refresh_token')
    if (access && refresh) {
      accessToken.value = access
      refreshToken.value = refresh
    }
  }

  // очистить токены
  const clearTokens = () => {
    accessToken.value = ''
    refreshToken.value = ''
    localStorage.removeItem('access_token')
    localStorage.removeItem('refresh_token')
  }

  // валидация пароля
  const validatePassword = (password: string): string[] => {
    const errors: string[] = []
    if (password.length < 8) errors.push('Мин. 8 символов')
    if (!/[A-Z]/.test(password)) errors.push('Заглавная буква')
    if (!/[a-z]/.test(password)) errors.push('Строчная буква')
    if (!/\d/.test(password)) errors.push('Цифра')
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) errors.push('Спецсимвол')
    return errors
  }

  // валидация email
  const validateEmail = (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  // вход
  const login = async (email: string, password: string) => {
    loading.value = true
    error.value = ''
    try {
      if (!validateEmail(email)) throw new Error('Некорректный email')
      const data = await apiRequest('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password })
      })
      user.value = (data as AuthResponse).user
      saveTokens(data as AuthResponse)
      router.push('/')
    } catch (e: unknown) {
      const errorMessage = e instanceof Error ? e.message : 'Ошибка входа'
      error.value = errorMessage
      throw e
    } finally {
      loading.value = false
    }
  }

  // вход через useFetch (реактивный)
  const loginReactive = (email: string, password: string) => {
    if (!validateEmail(email)) {
      error.value = 'Некорректный email'
      return null
    }

    return useFetch(`${API_BASE}/login`, {
      method: 'POST',
      body: { email, password },
      onResponse({ response }) {
        if (response.ok) {
          const data = response._data as AuthResponse
          user.value = data.user
          saveTokens(data)
          router.push('/')
        }
      },
      onResponseError({ response }) {
        const responseData = response._data as Record<string, unknown>
        let errorMessage = 'Ошибка входа'
        if (responseData?.data && typeof responseData.data === 'object' && 'message' in (responseData.data as Record<string, unknown>)) {
          errorMessage = (responseData.data as Record<string, unknown>).message as string
        } else if (responseData?.message) {
          errorMessage = (responseData.message as string) || 'Ошибка'
        }
        if (response.status === 401) errorMessage = 'Неверный логин или пароль'
        error.value = errorMessage
      }
    })
  }

  // регистрация
  const register = async (name: string, email: string, password: string, passwordConfirmation: string) => {
    loading.value = true
    error.value = ''
    try {
      if (!name.trim()) throw new Error('Имя обязательно')
      if (!validateEmail(email)) throw new Error('Некорректный email')
      const passwordErrors = validatePassword(password)
      if (passwordErrors.length) throw new Error(passwordErrors.join(', '))
      if (password !== passwordConfirmation) throw new Error('Пароли не совпадают')
      const data = await apiRequest('/register', {
        method: 'POST',
        body: JSON.stringify({
          name: name.trim(),
          email: email.toLowerCase().trim(),
          password,
          password_confirmation: passwordConfirmation
        })
      })
      user.value = (data as AuthResponse).user
      saveTokens(data as AuthResponse)
      router.push('/')
    } catch (e: unknown) {
      const errorMessage = e instanceof Error ? e.message : 'Ошибка регистрации'
      error.value = errorMessage
      throw e
    } finally {
      loading.value = false
    }
  }

  // регистрация через useFetch (реактивный)
  const registerReactive = (name: string, email: string, password: string, passwordConfirmation: string) => {
    // валидация
    if (!name.trim()) {
      error.value = 'Имя обязательно'
      return null
    }
    if (!validateEmail(email)) {
      error.value = 'Некорректный email'
      return null
    }
    const passwordErrors = validatePassword(password)
    if (passwordErrors.length) {
      error.value = passwordErrors.join(', ')
      return null
    }
    if (password !== passwordConfirmation) {
      error.value = 'Пароли не совпадают'
      return null
    }

    return useFetch(`${API_BASE}/register`, {
      method: 'POST',
      body: {
        name: name.trim(),
        email: email.toLowerCase().trim(),
        password,
        password_confirmation: passwordConfirmation
      },
      onResponse({ response }) {
        if (response.ok) {
          const data = response._data as AuthResponse
          user.value = data.user
          saveTokens(data)
          router.push('/')
        }
      },
      onResponseError({ response }) {
        const responseData = response._data as Record<string, unknown>
        let errorMessage = 'Ошибка регистрации'
        if (responseData?.data && typeof responseData.data === 'object' && 'message' in (responseData.data as Record<string, unknown>)) {
          errorMessage = (responseData.data as Record<string, unknown>).message as string
        } else if (responseData?.message) {
          errorMessage = (responseData.message as string) || 'Ошибка'
        }
        error.value = errorMessage
      }
    })
  }

  // выход
  const logout = async () => {
    loading.value = true
    error.value = ''
    try {
      if (accessToken.value) await apiRequest('/logout', { method: 'POST' })
    } catch (e: unknown) {
      const errorMessage = e instanceof Error ? e.message : 'Неизвестная ошибка'
      console.warn('Ошибка выхода:', errorMessage)
    } finally {
      user.value = null
      clearTokens()
      loading.value = false
      router.push('/login')
    }
  }

  // обновить токены
  const refreshTokens = async () => {
    if (!refreshToken.value) throw new Error('Нет refresh token')
    try {
      const data = await apiRequest('/refresh', {
        method: 'POST',
        body: JSON.stringify({ refresh_token: refreshToken.value })
      })
      user.value = (data as AuthResponse).user
      saveTokens(data as AuthResponse)
      return true
    } catch (e) {
      await logout()
      throw e
    }
  }

  // получить пользователя
  const getCurrentUser = async () => {
    if (!accessToken.value) return null
    try {
      const data = await apiRequest('/me')
      user.value = (data as { user: User }).user
      return (data as { user: User }).user
    } catch {
      try {
        await refreshTokens()
        const data = await apiRequest('/me')
        user.value = (data as { user: User }).user
        return (data as { user: User }).user
      } catch (err) {
        await logout()
        throw err
      }
    }
  }

  // получить пользователя через useFetch (реактивный)
  const getCurrentUserReactive = () => {
    if (!accessToken.value) return null

    return useFetch(`${API_BASE}/me`, {
      headers: {
        Authorization: `Bearer ${accessToken.value}`
      },
      onResponse({ response }) {
        if (response.ok) {
          const data = response._data as { user: User }
          user.value = data.user
        }
      },
      onResponseError({ response }) {
        if (response.status === 401) {
          // попытка обновить токены
          refreshTokens().then(() => {
            // повторный запрос после обновления токенов
            getCurrentUserReactive()
          }).catch(() => {
            logout()
          })
        }
      }
    })
  }

  // инициализация
  const initialize = async () => {
    loadTokens()
    if (accessToken.value) {
      try {
        await getCurrentUser()
      } catch {
        clearTokens()
      }
    }
  }

  return {
    // state
    user,
    accessToken,
    refreshToken,
    error,
    loading,
    // computed
    isAuthenticated,
    // methods (async)
    login,
    register,
    logout,
    refreshTokens,
    getCurrentUser,
    initialize,
    validatePassword,
    validateEmail,
    // methods (reactive with useFetch)
    loginReactive,
    registerReactive,
    getCurrentUserReactive
  }
})