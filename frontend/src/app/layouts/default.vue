<template>
  <div class="layout">
    <Header />
    <main class="main">
      <slot />
    </main>
    <Footer />
  </div>
</template>

<style scoped lang="scss">
@import '~/assets/styles/main.scss';

.layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  color: var(--color-text);
}

.main {
  flex: 1;
  padding: 0;
}
</style>

