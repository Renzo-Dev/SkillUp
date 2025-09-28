<template>
  <div>
    <h1>Тест страницы ошибок</h1>
    <button @click="throw404" class="btn">404 Ошибка</button>
    <button @click="throw500" class="btn">500 Ошибка</button>
  </div>
</template>

<script setup>
const throw404 = () => {
  throw createError({
    statusCode: 404,
    statusMessage: "Страница не найдена"
  })
}

const throw500 = () => {
  throw createError({
    statusCode: 500,
    statusMessage: "Внутренняя ошибка сервера"
  })
}
</script>

<style scoped>
.btn {
  padding: 0.5rem 1rem;
  margin: 0.5rem;
  background: #00dc82;
  color: #1a1a1a;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>
