<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2 class="auth-title">Вход в аккаунт</h2>
      
      <form class="auth-form" @submit.prevent="onLogin">
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
              placeholder="Введите пароль"
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
        </div>

        <button 
          :disabled="!isFormValid" 
          type="submit" 
          class="auth-button"
        >
          Войти
        </button>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>
      </form>

      <div class="auth-footer">
        <router-link class="auth-link" to="/register">
          Нет аккаунта? Зарегистрируйтесь
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
  email: '',
  password: ''
})

// Ошибки валидации
const errors = reactive({
  email: '',
  password: ''
})

// UI состояние
const showPassword = ref(false)
const auth = useAuthStore()

// Реактивные свойства из store
const error = computed(() => auth.error)

// Валидация полей
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
  errors.password = ''
  return true
}

const clearError = (field: keyof typeof errors) => {
  errors[field] = ''
  if (error.value) {
    auth.error = ''
  }
}

// Проверка валидности формы
const isFormValid = computed(() => {
  return form.email && form.password && !errors.email && !errors.password
})

// Обработка отправки формы
const onLogin = async () => {
  // Валидация перед отправкой
  const isEmailValid = validateEmail()
  const isPasswordValid = validatePassword()
  
  if (!isEmailValid || !isPasswordValid) {
    return
  }

  try {
    await auth.login(form.email, form.password)
  } catch (error) {
    // Ошибка уже обработана в store
    console.error('Login error:', error)
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
  max-width: 420px;
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