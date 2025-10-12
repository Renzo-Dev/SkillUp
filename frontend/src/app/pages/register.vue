<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2 class="auth-title">Создание аккаунта</h2>
      
      <form class="auth-form" @submit.prevent="onRegister">
        <div class="form-group">
          <label for="name" class="form-label">Имя</label>
          <input 
            id="name"
            v-model="form.name" 
            type="text" 
            class="form-input"
            :class="{ 'error': errors.name }"
            placeholder="Введите ваше имя"
            required 
            @blur="validateName"
            @input="clearError('name')"
          >
          <div v-if="errors.name" class="field-error">{{ errors.name }}</div>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input 
            id="email"
            v-model="form.email" 
            type="email" 
            class="form-input"
            :class="{ 'error': errors.email }"
            placeholder="Введите ваш email"
            required 
            @blur="validateEmail"
            @input="clearError('email')"
          >
          <div v-if="errors.email" class="field-error">{{ errors.email }}</div>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Пароль</label>
          <div class="password-input-wrapper">
            <input 
              id="password"
              v-model="form.password" 
              :type="showPassword ? 'text' : 'password'" 
              class="form-input"
              :class="{ 'error': errors.password }"
              placeholder="Создайте пароль"
              required 
              @blur="validatePassword"
              @input="clearError('password')"
            >
            <button 
              type="button" 
              class="password-toggle"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
          <div v-if="errors.password" class="field-error">{{ errors.password }}</div>
          
          <!-- Индикатор силы пароля -->
          <div v-if="form.password" class="password-strength">
            <div class="strength-bar">
              <div 
                class="strength-fill" 
                :class="passwordStrength.level"
                :style="{ width: passwordStrength.percentage + '%' }"
              />
            </div>
            <div class="strength-text">{{ passwordStrength.text }}</div>
          </div>

          <!-- Требования к паролю -->
          <div v-if="form.password" class="password-requirements">
            <div 
              v-for="requirement in passwordRequirements" 
              :key="requirement.text"
              class="requirement-item"
              :class="{ 'valid': requirement.valid }"
            >
              <span class="requirement-icon">{{ requirement.valid ? '✓' : '○' }}</span>
              {{ requirement.text }}
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="passwordConfirmation" class="form-label">Подтверждение пароля</label>
          <input 
            id="passwordConfirmation"
            v-model="form.passwordConfirmation" 
            type="password" 
            class="form-input"
            :class="{ 'error': errors.passwordConfirmation }"
            placeholder="Повторите пароль"
            required 
            @blur="validatePasswordConfirmation"
            @input="clearError('passwordConfirmation')"
          >
          <div v-if="errors.passwordConfirmation" class="field-error">{{ errors.passwordConfirmation }}</div>
        </div>

        <button 
          :disabled="loading || !isFormValid" 
          type="submit" 
          class="auth-button"
          :class="{ 'loading': loading }"
        >
          <span v-if="loading" class="spinner" />
          {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
        </button>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>
      </form>

      <div class="auth-footer">
        <router-link class="auth-link" to="/login">
          Уже есть аккаунт? Войти
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import { useAuthStore } from '../stores/auth.store'

// Middleware для гостевых страниц
definePageMeta({
  middleware: 'guest'
})

// Состояние формы
const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: ''
})

// Ошибки валидации
const errors = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: ''
})

// UI состояние
const showPassword = ref(false)
const auth = useAuthStore()

// Реактивные свойства из store
const loading = computed(() => auth.loading)
const error = computed(() => auth.error)

// Валидация полей
const validateName = () => {
  if (!form.name.trim()) {
    errors.name = 'Имя обязательно для заполнения'
    return false
  }
  if (form.name.trim().length < 2) {
    errors.name = 'Имя должно содержать минимум 2 символа'
    return false
  }
  errors.name = ''
  return true
}

const validateEmail = () => {
  if (!form.email) {
    errors.email = 'Email обязателен'
    return false
  }
  if (!auth.validateEmail(form.email)) {
    errors.email = 'Некорректный формат email'
    return false
  }
  errors.email = ''
  return true
}

const validatePassword = () => {
  if (!form.password) {
    errors.password = 'Пароль обязателен'
    return false
  }
  
  const passwordErrors = auth.validatePassword(form.password)
  if (passwordErrors.length > 0) {
    errors.password = passwordErrors[0] // Показываем только первую ошибку
    return false
  }
  
  errors.password = ''
  return true
}

const validatePasswordConfirmation = () => {
  if (!form.passwordConfirmation) {
    errors.passwordConfirmation = 'Подтверждение пароля обязательно'
    return false
  }
  if (form.password !== form.passwordConfirmation) {
    errors.passwordConfirmation = 'Пароли не совпадают'
    return false
  }
  errors.passwordConfirmation = ''
  return true
}

const clearError = (field: keyof typeof errors) => {
  errors[field] = ''
  if (error.value) {
    auth.error = ''
  }
}

// Анализ силы пароля
const passwordStrength = computed(() => {
  if (!form.password) {
    return { level: 'weak', percentage: 0, text: '' }
  }

  const passwordErrors = auth.validatePassword(form.password)
  const validRequirements = 5 - passwordErrors.length
  const percentage = (validRequirements / 5) * 100

  if (percentage < 40) {
    return { level: 'weak', percentage, text: 'Слабый пароль' }
  } else if (percentage < 80) {
    return { level: 'medium', percentage, text: 'Средний пароль' }
  } else {
    return { level: 'strong', percentage, text: 'Надёжный пароль' }
  }
})

// Требования к паролю
const passwordRequirements = computed(() => {
  const password = form.password
  return [
    {
      text: 'Минимум 8 символов',
      valid: password.length >= 8
    },
    {
      text: 'Заглавная буква',
      valid: /[A-Z]/.test(password)
    },
    {
      text: 'Строчная буква',
      valid: /[a-z]/.test(password)
    },
    {
      text: 'Цифра',
      valid: /\d/.test(password)
    },
    {
      text: 'Специальный символ',
      valid: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    }
  ]
})

// Проверка валидности формы
const isFormValid = computed(() => {
  return form.name.trim() && 
         form.email && 
         form.password && 
         form.passwordConfirmation &&
         !errors.name && 
         !errors.email && 
         !errors.password && 
         !errors.passwordConfirmation &&
         passwordStrength.value.percentage === 100
})

// Обработка отправки формы
const onRegister = async () => {
  // Валидация перед отправкой
  const isNameValid = validateName()
  const isEmailValid = validateEmail()
  const isPasswordValid = validatePassword()
  const isPasswordConfirmationValid = validatePasswordConfirmation()
  
  if (!isNameValid || !isEmailValid || !isPasswordValid || !isPasswordConfirmationValid) {
    return
  }

  try {
    await auth.register(
      form.name.trim(), 
      form.email.toLowerCase().trim(), 
      form.password, 
      form.passwordConfirmation
    )
  } catch (error) {
    // Ошибка уже обработана в store
    console.error('Register error:', error)
  }
}

// Очистка ошибок при размонтировании
onUnmounted(() => {
  auth.error = ''
})
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 20px;
  position: relative;
}

.auth-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.05) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(0,0,0,0.05) 0%, transparent 50%);
  pointer-events: none;
}

.auth-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 
    0 20px 40px rgba(0, 0, 0, 0.1),
    0 0 0 1px rgba(0, 0, 0, 0.05);
  padding: 48px;
  width: 100%;
  max-width: 480px;
  position: relative;
  backdrop-filter: blur(10px);
}

.auth-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #000 0%, #333 50%, #000 100%);
  border-radius: 16px 16px 0 0;
}

.auth-title {
  text-align: center;
  margin-bottom: 40px;
  color: #1a1a1a;
  font-size: 32px;
  font-weight: 300;
  letter-spacing: -0.5px;
  position: relative;
}

.auth-title::after {
  content: '';
  position: absolute;
  bottom: -12px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 2px;
  background: linear-gradient(90deg, transparent, #000, transparent);
}

.auth-form {
  margin-bottom: 32px;
}

.form-group {
  margin-bottom: 24px;
  position: relative;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  color: #2c2c2c;
  font-weight: 500;
  font-size: 14px;
  letter-spacing: 0.3px;
  transition: color 0.2s ease;
}

.form-input {
  width: 100%;
  padding: 16px 20px;
  border: 1px solid #e1e5e9;
  border-radius: 12px;
  background: #fafbfc;
  color: #1a1a1a;
  font-size: 16px;
  transition: all 0.3s ease;
  box-sizing: border-box;
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: #000;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.form-input.error {
  border-color: #dc3545;
  background: #fff5f5;
}

.form-input.error:focus {
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 20px;
  padding: 8px;
  color: #666;
  transition: color 0.2s ease;
  border-radius: 6px;
}

.password-toggle:hover {
  color: #000;
  background: rgba(0, 0, 0, 0.05);
}

.field-error {
  color: #dc3545;
  font-size: 13px;
  margin-top: 6px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 4px;
}

.field-error::before {
  content: '⚠';
  font-size: 12px;
}

.password-strength {
  margin-top: 12px;
}

.strength-bar {
  width: 100%;
  height: 6px;
  background: #f0f0f0;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 8px;
  border: 1px solid #e1e5e9;
}

.strength-fill {
  height: 100%;
  transition: all 0.3s ease;
  background: linear-gradient(90deg, #dc3545 0%, #ffc107 50%, #28a745 100%);
  border-radius: 3px;
}

.strength-text {
  font-size: 12px;
  color: #666;
  text-align: right;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.password-requirements {
  margin-top: 12px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

.requirement-item {
  display: flex;
  align-items: center;
  font-size: 13px;
  margin-bottom: 6px;
  color: #666;
  font-weight: 500;
  transition: color 0.2s ease;
}

.requirement-item:last-child {
  margin-bottom: 0;
}

.requirement-item.valid {
  color: #28a745;
}

.requirement-icon {
  margin-right: 8px;
  font-weight: bold;
  font-size: 14px;
}

.auth-button {
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, #000 0%, #333 100%);
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  letter-spacing: 0.3px;
  position: relative;
  overflow: hidden;
}

.auth-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s ease;
}

.auth-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.auth-button:hover:not(:disabled)::before {
  left: 100%;
}

.auth-button:active {
  transform: translateY(0);
}

.auth-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.auth-button.loading {
  cursor: not-allowed;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid transparent;
  border-top: 2px solid #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  background: #fff5f5;
  color: #dc3545;
  padding: 16px;
  margin-top: 20px;
  font-size: 14px;
  text-align: center;
  border-radius: 12px;
  border: 1px solid #fecaca;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.error-message::before {
  content: '⚠';
  font-size: 16px;
}

.auth-footer {
  text-align: center;
  padding-top: 24px;
  border-top: 1px solid #e9ecef;
}

.auth-link {
  display: inline-block;
  color: #666;
  text-decoration: none;
  font-size: 14px;
  margin-bottom: 8px;
  transition: all 0.3s ease;
  font-weight: 500;
  padding: 8px 16px;
  border-radius: 8px;
  position: relative;
}

.auth-link::after {
  content: '';
  position: absolute;
  bottom: 4px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 1px;
  background: #000;
  transition: width 0.3s ease;
}

.auth-link:hover {
  color: #000;
  background: rgba(0, 0, 0, 0.05);
}

.auth-link:hover::after {
  width: 80%;
}

@media (max-width: 480px) {
  .auth-card {
    padding: 32px 24px;
    margin: 16px;
  }
  
  .auth-title {
    font-size: 28px;
  }
  
  .form-input {
    padding: 14px 16px;
  }
  
  .auth-button {
    padding: 14px 20px;
  }
}
</style>