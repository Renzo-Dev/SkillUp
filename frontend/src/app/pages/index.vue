<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <div class="hero-text">
          <h1 class="hero-title">
            <span class="gradient-text">SkillUP</span>
            <br>
            AI-платформа нового поколения
          </h1>
          <p class="hero-subtitle">
            Создавайте качественный контент с помощью искусственного интеллекта. 
            Быстро, эффективно, профессионально.
          </p>
          <div class="hero-actions">
            <NuxtLink to="/register" class="btn btn-primary">
              <span>Начать бесплатно</span>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </NuxtLink>
            <NuxtLink to="/login" class="btn btn-secondary">
              Войти в аккаунт
            </NuxtLink>
          </div>
        </div>
        <div class="hero-visual">
          <div class="floating-card card-1">
            <div class="card-icon">🤖</div>
            <div class="card-text">AI Генерация</div>
          </div>
          <div class="floating-card card-2">
            <div class="card-icon">⚡</div>
            <div class="card-text">Быстро</div>
          </div>
          <div class="floating-card card-3">
            <div class="card-icon">🔒</div>
            <div class="card-text">Безопасно</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="container">
        <h2 class="section-title">Почему выбирают SkillUP</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Мгновенная генерация</h3>
            <p>Получайте готовый контент за секунды с помощью передовых AI-алгоритмов</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M12 22S8 18 8 12V5L12 2L16 5V12C16 18 12 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Безопасность данных</h3>
            <p>Ваши данные защищены современными технологиями шифрования</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Высокое качество</h3>
            <p>Профессиональный контент, который соответствует вашим требованиям</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped lang="scss">
.home-page {
  min-height: 100vh;
}

.hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  padding: 2rem 0;
  position: relative;
  overflow: hidden;
}

.hero-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
}

.hero-text {
  z-index: 2;
}

.hero-title {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 1.5rem;
  letter-spacing: -0.02em;
}

.gradient-text {
  background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 50%, #ff9800 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
  margin-bottom: 2.5rem;
  max-width: 500px;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  
  &.btn-primary {
    background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 100%);
    color: #1a1a1a;
    box-shadow: 0 4px 20px rgba(255, 193, 7, 0.4), 0 2px 8px rgba(255, 193, 7, 0.2);
    font-weight: 700;
    
    &:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(255, 193, 7, 0.5), 0 4px 12px rgba(255, 193, 7, 0.3);
      background: linear-gradient(135deg, #ffeb3b 0%, #ffc107 100%);
    }
  }
  
  &.btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    color: #ffc107;
    backdrop-filter: blur(20px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    
    &:hover {
      background: rgba(255, 193, 7, 0.1);
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(255, 193, 7, 0.2), 0 4px 12px rgba(0, 0, 0, 0.4);
    }
  }
}

.hero-visual {
  position: relative;
  height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.floating-card {
  position: absolute;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  text-align: center;
  animation: float 6s ease-in-out infinite;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 0 2px 8px rgba(255, 193, 7, 0.1);
  
  &.card-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
  }
  
  &.card-2 {
    top: 50%;
    right: 20%;
    animation-delay: 2s;
  }
  
  &.card-3 {
    bottom: 20%;
    left: 30%;
    animation-delay: 4s;
  }
}

.card-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.card-text {
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

.features {
  padding: 6rem 0;
  background: rgba(255, 255, 255, 0.02);
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 193, 7, 0.3), transparent);
  }
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 4rem;
  background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 50%, #ff9800 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2.5rem;
  text-align: center;
  transition: all 0.3s ease;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  
  &:hover {
    transform: translateY(-12px);
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 8px 24px rgba(255, 193, 7, 0.1);
  }
}

.feature-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 100%);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  color: #1a1a1a;
  box-shadow: 0 4px 20px rgba(255, 193, 7, 0.3);
}

.feature-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: white;
}

.feature-card p {
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.6;
  margin: 0;
}

@media (max-width: 768px) {
  .hero-content {
    grid-template-columns: 1fr;
    gap: 2rem;
    text-align: center;
  }
  
  .hero-visual {
    height: 300px;
  }
  
  .floating-card {
    position: relative;
    margin: 1rem;
    animation: none;
  }
  
  .features {
    padding: 4rem 0;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
}
</style>
