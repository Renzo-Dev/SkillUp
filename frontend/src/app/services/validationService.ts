// validationService.ts
export const validatePassword = (password: string): string[] => {
  const errors: string[] = []
  if (password.length < 8) errors.push('Пароль должен содержать минимум 8 символов')
  if (!/[A-Z]/.test(password)) errors.push('Пароль должен содержать заглавную букву')
  if (!/[a-z]/.test(password)) errors.push('Пароль должен содержать строчную букву')
  if (!/\d/.test(password)) errors.push('Пароль должен содержать цифру')
  if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) errors.push('Пароль должен содержать специальный символ')
  return errors
}

export const validateEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}