// tokenService.ts
export const saveTokens = (access: string, refresh: string) => {
  localStorage.setItem('access_token', access)
  localStorage.setItem('refresh_token', refresh)
}

export const loadTokens = (): { access: string; refresh: string } => {
  return {
    access: localStorage.getItem('access_token') || '',
    refresh: localStorage.getItem('refresh_token') || ''
  }
}

export const clearTokens = () => {
  localStorage.removeItem('access_token')
  localStorage.removeItem('refresh_token')
}