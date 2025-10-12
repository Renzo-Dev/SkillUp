<template>
  <div class="auth-container">
    <h1>Вход</h1>
    <form @submit.prevent="onSubmit">
      <div class="form-group">
        <label for="email">Email</label>
        <input v-model="email" type="email" id="email" required />
      </div>
      <div class="form-group">
        <label for="password">Пароль</label>
        <input v-model="password" type="password" id="password" required />
      </div>
      <button type="submit">Войти</button>
    </form>
    <p class="auth-link">
      Нет аккаунта?
      <NuxtLink to="/register">Зарегистрироваться</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const email = ref('')
const password = ref('')

function onSubmit() {
  // TODO: интеграция с API
  if (!email.value || !password.value) {
    alert('Заполните все поля')
    return
  }
  alert(`Вход: ${email.value}`)
}
</script>
