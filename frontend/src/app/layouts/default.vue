<template>
  <div class="app-layout">
    <header class="header">
      <nav class="nav">
        <div class="nav-brand">
          <NuxtLink to="/" class="brand-link">
            SkillUp
          </NuxtLink>
        </div>
        <div class="nav-menu">
          <NuxtLink to="/" class="nav-link">Главная</NuxtLink>
          <NuxtLink to="/about" class="nav-link">О нас</NuxtLink>
          <NuxtLink to="/contact" class="nav-link">Контакты</NuxtLink>
        </div>
      </nav>
    </header>

    <main class="main">
      <slot />
    </main>

    <footer class="footer">
      <div class="footer-content">
        <p>&copy; 2024 SkillUp. Все права защищены.</p>
        <div class="footer-links">
          <NuxtLink to="/privacy" class="footer-link">Политика конфиденциальности</NuxtLink>
          <NuxtLink to="/terms" class="footer-link">Условия использования</NuxtLink>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
// Layout logic here
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  background: #1a1a1a;
  color: white;
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand-link {
  font-size: 1.5rem;
  font-weight: bold;
  color: #00dc82;
  text-decoration: none;
}

.nav-menu {
  display: flex;
  gap: 2rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.nav-link:hover {
  background-color: rgba(255,255,255,0.1);
}

.nav-link.router-link-active {
  background-color: #00dc82;
  color: #1a1a1a;
}

.main {
  flex: 1;
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
  width: 100%;
}

.footer {
  background: #2a2a2a;
  color: #ccc;
  padding: 2rem 0;
  margin-top: auto;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.footer-links {
  display: flex;
  gap: 2rem;
}

.footer-link {
  color: #ccc;
  text-decoration: none;
  transition: color 0.2s;
}

.footer-link:hover {
  color: #00dc82;
}

@media (max-width: 768px) {
  .nav {
    flex-direction: column;
    gap: 1rem;
  }
  
  .nav-menu {
    gap: 1rem;
  }
  
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
}
</style>
