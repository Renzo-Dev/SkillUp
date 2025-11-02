import type { AuthResponse, User } from '../stores/auth.types'

const API_BASE = 'http://localhost/api/auth'

export const loginApi = async (email: string, password: string): Promise<AuthResponse> => {
  const response = await fetch(`${API_BASE}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  })
  const result = await response.json()
  if (!response.ok) {
    const errorMsg = result.message || 'Ошибка авторизации'
    const details = result.errors ? JSON.stringify(result.errors) : ''
    throw new Error(`${errorMsg}${details ? ': ' + details : ''}`)
  }
  return result.data
}

export const registerApi = async (name: string, email: string, password: string, passwordConfirmation: string): Promise<AuthResponse> => {
  const response = await fetch(`${API_BASE}/register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation })
  })
  const result = await response.json()
  if (!response.ok) {
    const errorMsg = result.message || 'Ошибка регистрации'
    const details = result.errors ? JSON.stringify(result.errors) : ''
    throw new Error(`${errorMsg}${details ? ': ' + details : ''}`)
  }
  return result.data
}

export const logoutApi = async (accessToken: string): Promise<void> => {
  await fetch(`${API_BASE}/logout`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${accessToken}` }
  })
}

export const refreshApi = async (refreshToken: string): Promise<AuthResponse> => {
  const response = await fetch(`${API_BASE}/refresh`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ refresh_token: refreshToken })
  })
  const result = await response.json()
  if (!response.ok) {
    const errorMsg = result.message || 'Ошибка обновления токена'
    const details = result.errors ? JSON.stringify(result.errors) : ''
    throw new Error(`${errorMsg}${details ? ': ' + details : ''}`)
  }
  return result.data
}

export const getCurrentUserApi = async (accessToken: string): Promise<User> => {
  const response = await fetch(`${API_BASE}/me`, {
    headers: { 'Authorization': `Bearer ${accessToken}` }
  })
  const result = await response.json()
  if (!response.ok) {
    const errorMsg = result.message || 'Ошибка получения пользователя'
    const details = result.errors ? JSON.stringify(result.errors) : ''
    throw new Error(`${errorMsg}${details ? ': ' + details : ''}`)
  }
  return result.data.user
}