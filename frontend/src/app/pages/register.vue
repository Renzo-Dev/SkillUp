<template>
  <div class="auth-container">
    <h1>Регистрация</h1>
    <form @submit.prevent="onSubmit">
      <div class="form-group">
        <label for="name">Имя</label>
        <input v-model="name" type="text" id="name" required />
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input v-model="email" type="email" id="email" required />
      </div>
      <div class="form-group">
        <label for="password">Пароль</label>
        <input v-model="password" type="password" id="password" required />
      </div>
      <button type="submit">Зарегистрироваться</button>
    </form>
    <p class="auth-link">
      Уже есть аккаунт?
      <NuxtLink to="/login">Войти</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const name = ref('')
const email = ref('')
const password = ref('')

function onSubmit() {
  // TODO: интеграция с API
  if (!name.value || !email.value || !password.value) {
    alert('Заполните все поля')
    return
  }
  alert(`Регистрация: ${name.value}, ${email.value}`)
}
</script>

<style scoped>
.auth-container {
  max-width: 340px;
  margin: 60px auto;
  padding: 32px;
  background: #f8f8f8;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.form-group {
  margin-bottom: 18px;
}
label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
}
input {
  width: 100%;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 16px;
}
button {
  width: 100%;
  padding: 10px;
  background: #222;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
}
.auth-link {
  margin-top: 18px;
  text-align: center;
}
</style>
