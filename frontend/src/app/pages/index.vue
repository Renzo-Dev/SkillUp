<template>
  <div class="index-page">
    <section class="welcome-section">
      <h1>Добро пожаловать в SkillUp!</h1>
      <p>Ваша платформа для развития навыков и обучения.</p>
      
      <!-- Если пользователь не аутентифицирован -->
      <div v-if="!auth.isAuthenticated" class="guest-content">
        <p>Войдите в систему или зарегистрируйтесь, чтобы начать обучение.</p>
        <div class="auth-buttons">
          <NuxtLink to="/login" class="btn btn-primary">Войти</NuxtLink>
          <NuxtLink to="/register" class="btn btn-secondary">Зарегистрироваться</NuxtLink>
        </div>
      </div>
      
      <!-- Если пользователь аутентифицирован -->
      <div v-else class="user-content">
        <div class="user-info">
          <h2>Привет, {{ auth.user?.name }}!</h2>
          <p>Email: {{ auth.user?.email }}</p>
        </div>
        
        <div class="user-actions">
          <button @click="handleLogout" class="btn btn-outline">
            Выйти
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '../stores/auth.store'

// Middleware для проверки аутентификации
definePageMeta({
  middleware: 'auth'
})

const auth = useAuthStore()

const handleLogout = async () => {
  try {
    await auth.logout()
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>

<style scoped>
.index-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  position: relative;
}

.index-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.03) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(0,0,0,0.03) 0%, transparent 50%);
  pointer-events: none;
}

.welcome-section {
  max-width: 900px;
  margin: 0 auto;
  padding: 80px 20px;
  text-align: center;
  position: relative;
  z-index: 1;
}

.welcome-section h1 {
  font-size: 4rem;
  margin-bottom: 1.5rem;
  color: #1a1a1a;
  font-weight: 300;
  letter-spacing: -1px;
  line-height: 1.1;
  position: relative;
}

.welcome-section h1::after {
  content: '';
  position: absolute;
  bottom: -20px;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 3px;
  background: linear-gradient(90deg, transparent, #000, transparent);
}

.welcome-section > p {
  font-size: 1.4rem;
  color: #666;
  margin-bottom: 3rem;
  font-weight: 300;
  letter-spacing: 0.5px;
  line-height: 1.6;
}

.guest-content p {
  font-size: 1.2rem;
  color: #555;
  margin-bottom: 3rem;
  font-weight: 400;
  line-height: 1.6;
}

.auth-buttons {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 2rem;
}

.user-content {
  background: #fff;
  border-radius: 20px;
  box-shadow: 
    0 20px 40px rgba(0, 0, 0, 0.1),
    0 0 0 1px rgba(0, 0, 0, 0.05);
  padding: 3rem;
  margin-top: 3rem;
  position: relative;
  backdrop-filter: blur(10px);
}

.user-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #000 0%, #333 50%, #000 100%);
  border-radius: 20px 20px 0 0;
}

.user-info h2 {
  color: #1a1a1a;
  margin-bottom: 1.5rem;
  font-size: 2.2rem;
  font-weight: 300;
  letter-spacing: -0.5px;
  position: relative;
}

.user-info h2::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 60px;
  height: 2px;
  background: linear-gradient(90deg, #000, transparent);
}

.user-info p {
  margin-bottom: 0.8rem;
  color: #555;
  font-weight: 400;
  font-size: 1.1rem;
  line-height: 1.5;
}

.user-actions {
  margin-top: 2.5rem;
  padding-top: 2rem;
  border-top: 1px solid #e9ecef;
}

.btn {
  display: inline-block;
  padding: 16px 32px;
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  border: 2px solid #000;
  cursor: pointer;
  margin: 0 0.5rem;
  letter-spacing: 0.3px;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #000 0%, #333 100%);
  color: #fff;
  border: 2px solid #000;
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.btn-primary:hover::before {
  left: 100%;
}

.btn-secondary {
  background: #fff;
  color: #000;
  border: 2px solid #000;
}

.btn-secondary:hover {
  background: #000;
  color: #fff;
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.btn-secondary:hover::before {
  left: 100%;
}

.btn-outline {
  background: transparent;
  color: #000;
  border: 2px solid #000;
}

.btn-outline:hover {
  background: #000;
  color: #fff;
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.btn-outline:hover::before {
  left: 100%;
}

@media (max-width: 768px) {
  .welcome-section {
    padding: 60px 20px;
  }
  
  .welcome-section h1 {
    font-size: 3rem;
  }
  
  .welcome-section > p {
    font-size: 1.2rem;
  }
  
  .auth-buttons {
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }
  
  .btn {
    width: 250px;
    margin: 0.25rem 0;
  }
  
  .user-content {
    padding: 2rem;
    margin: 2rem 1rem 0;
  }
  
  .user-info h2 {
    font-size: 1.8rem;
  }
}

@media (max-width: 480px) {
  .welcome-section h1 {
    font-size: 2.5rem;
  }
  
  .btn {
    width: 200px;
    padding: 14px 24px;
  }
}
</style>
