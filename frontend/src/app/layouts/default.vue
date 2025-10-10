<template>
  <div class="layout">
    <Header data-aos="fade-down" />
    <main class="main">
      <slot />
    </main>
    <Footer data-aos="fade-up" />
  </div>
</template>

<script setup>
// Шрифты подключаются через @nuxt/fonts в nuxt.config (Inter/Space Grotesk)
</script>

<style scoped lang="scss">
@import '~/assets/styles/main.scss';

.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  color: var(--color-text);
}

.main {
  flex: 1;
  padding: 0;
  position: relative;
}
</style>
