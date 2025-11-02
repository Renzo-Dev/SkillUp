// Типы для auth.store и сервисов
export interface User {
  id: number
  name: string
  email: string
  is_active: boolean
  last_login_at: string | null
  created_at: string
  updated_at: string
}

export interface AuthResponse {
  user: User
  access_token: string
  refresh_token: string
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}