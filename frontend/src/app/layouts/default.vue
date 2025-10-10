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
.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
  color: #fff;
  position: relative;

  &::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
      radial-gradient(
        circle at 20% 80%,
        rgba(255, 193, 7, 0.08) 0%,
        transparent 50%
      ),
      radial-gradient(
        circle at 80% 20%,
        rgba(255, 152, 0, 0.06) 0%,
        transparent 50%
      );
    pointer-events: none;
    z-index: 0;
  }
}

.main {
  flex: 1;
  padding: 0;
  position: relative;
  z-index: 1;
}
</style>
