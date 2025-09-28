<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps({
  error: Object as () => NuxtError,
})

// Обработка ошибок
const handleError = () => {
  clearError({ redirect: '/' })
}
</script>

<template>
  <div class="error-container">
    <div class="error-content">
      <h1 class="error-code">{{ error.statusCode }}</h1>
      <h2 class="error-title">{{ error.statusMessage }}</h2>
      <p class="error-message">{{ error.message }}</p>
      
      <div class="error-actions">
        <button @click="handleError" class="btn-primary">
          Попробовать снова
        </button>
        <NuxtLink to="/" class="btn-secondary">
          Вернуться на главную
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.error-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
  color: white;
  padding: 2rem;
}

.error-content {
  text-align: center;
  max-width: 500px;
}

.error-code {
  font-size: 8rem;
  font-weight: bold;
  color: #00dc82;
  margin: 0;
  line-height: 1;
}

.error-title {
  font-size: 2rem;
  margin: 1rem 0;
  color: #fff;
}

.error-message {
  color: #ccc;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background: #00dc82;
  color: #1a1a1a;
}

.btn-primary:hover {
  background: #00b870;
  transform: translateY(-2px);
}

.btn-secondary {
  background: transparent;
  color: #00dc82;
  border: 2px solid #00dc82;
}

.btn-secondary:hover {
  background: #00dc82;
  color: #1a1a1a;
}

@media (max-width: 768px) {
  .error-code {
    font-size: 6rem;
  }
  
  .error-title {
    font-size: 1.5rem;
  }
  
  .error-actions {
    flex-direction: column;
    align-items: center;
  }
}
</style>
