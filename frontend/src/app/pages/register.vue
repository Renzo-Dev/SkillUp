<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <h1 class="auth-title">Регистрация</h1>

        <form class="auth-form" @submit.prevent="handleRegister">
          <div class="form-row">
            <div class="form-group">
              <label for="firstName">Имя</label>
              <input
                id="firstName"
                v-model="form.firstName"
                type="text"
                required
                class="form-input"
                placeholder="Имя"
              />
            </div>

            <div class="form-group">
              <label for="lastName">Фамилия</label>
              <input
                id="lastName"
                v-model="form.lastName"
                type="text"
                required
                class="form-input"
                placeholder="Фамилия"
              />
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="form-input"
              placeholder="Введите ваш email"
            />
          </div>

          <div class="form-group">
            <label for="password">Пароль</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="form-input"
              placeholder="Создайте пароль"
            />
          </div>

          <div class="form-group">
            <label for="confirmPassword">Подтвердите пароль</label>
            <input
              id="confirmPassword"
              v-model="form.confirmPassword"
              type="password"
              required
              class="form-input"
              placeholder="Подтвердите пароль"
            />
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input
                v-model="form.agreeToTerms"
                type="checkbox"
                required
                class="checkbox"
              />
              <span
                >Я согласен с
                <a href="#" class="link">условиями использования</a></span
              >
            </label>
          </div>

          <button
            type="submit"
            class="btn btn-primary"
            :disabled="loading || !isFormValid"
          >
            {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
          </button>
        </form>

        <div class="auth-footer">
          <p>Уже есть аккаунт? <NuxtLink to="/login">Войти</NuxtLink></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Логика регистрации
const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  confirmPassword: '',
  agreeToTerms: false,
})

const loading = ref(false)

const isFormValid = computed(() => {
  return (
    form.value.firstName &&
    form.value.lastName &&
    form.value.email &&
    form.value.password &&
    form.value.confirmPassword &&
    form.value.password === form.value.confirmPassword &&
    form.value.agreeToTerms
  )
})

const handleRegister = async () => {
  if (!isFormValid.value) return

  loading.value = true

  try {
    // Здесь будет API запрос для регистрации
    console.log('Register data:', form.value)

    // Имитация запроса
    await new Promise(resolve => setTimeout(resolve, 1000))

    // После успешной регистрации перенаправляем на главную
    await navigateTo('/')
  } catch (error) {
    console.error('Register error:', error)
  } finally {
    loading.value = false
  }
}

// SEO
useHead({
  title: 'Регистрация - SkillUP',
  meta: [{ name: 'description', content: 'Создайте аккаунт в SkillUP' }],
})
</script>

<style scoped lang="scss">
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 0;
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
      radial-gradient(circle at 30% 20%, rgba(255, 193, 7, 0.08) 0%, transparent 50%),
      radial-gradient(circle at 70% 80%, rgba(255, 152, 0, 0.06) 0%, transparent 50%);
    pointer-events: none;
  }
}

.auth-container {
  width: 100%;
  max-width: 520px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.auth-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 8px 24px rgba(255, 193, 7, 0.1);
  padding: 3rem;
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(255, 193, 7, 0.6), transparent);
  }
}

.auth-title {
  text-align: center;
  margin-bottom: 2.5rem;
  background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 50%, #ff9800 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;

  label {
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
  }
}

.form-input {
  padding: 1rem 1.25rem;
  border-radius: 12px;
  font-size: 1rem;
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);

  &:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 30px rgba(255, 193, 7, 0.2), 0 4px 12px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
  }

  &::placeholder {
    color: rgba(255, 255, 255, 0.5);
  }
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  cursor: pointer;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.5;

  .checkbox {
    margin: 0;
    width: 1.25rem;
    height: 1.25rem;
    accent-color: #ffc107;
    border-radius: 4px;
  }

  .link {
    color: #ffc107;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;

    &:hover {
      color: #ffeb3b;
      text-decoration: underline;
    }
  }
}

.btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;

  &.btn-primary {
    background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 100%);
    color: #1a1a1a;
    box-shadow: 0 4px 20px rgba(255, 193, 7, 0.4), 0 2px 8px rgba(255, 193, 7, 0.2);
    font-weight: 700;

    &:hover:not(:disabled) {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(255, 193, 7, 0.5), 0 4px 12px rgba(255, 193, 7, 0.3);
      background: linear-gradient(135deg, #ffeb3b 0%, #ffc107 100%);
    }

    &:disabled {
      background: rgba(255, 255, 255, 0.1);
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
  }
}

.auth-footer {
  text-align: center;
  margin-top: 2rem;

  p {
    color: rgba(255, 255, 255, 0.7);
    margin: 0;

    a {
      color: #ffc107;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;

      &:hover {
        color: #ffeb3b;
        text-decoration: underline;
      }
    }
  }
}

@media (max-width: 640px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .auth-card {
    margin: 0 1rem;
    padding: 2rem;
  }
  
  .auth-title {
    font-size: 1.75rem;
  }
}
</style>
