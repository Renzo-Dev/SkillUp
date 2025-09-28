<template>
  <div>
    <!-- Hero Section -->
    <section id="hero" class="hero u-section" aria-labelledby="hero-title">
      <div class="hero__background" aria-hidden="true">
        <div class="hero__layer hero__layer--base" data-parallax-speed="0.2"></div>
        <div class="hero__layer hero__layer--mid" data-parallax-speed="0.4"></div>
        <div class="hero__layer hero__layer--grid" data-parallax-speed="0.6"></div>
        <ParticlesBackground />
        <ThreeBackground />
      </div>

      <div class="u-container hero__content">
        <div class="hero__grid">
          <Motion
            class="hero__intro"
            :initial="{ opacity: 0, y: 40 }"
            :enter="{
              opacity: 1,
              y: 0,
              transition: { duration: 0.8, ease: 'easeOut' },
            }"
          >
            <span class="u-pill">
              <SparklesIcon class="icon" />
              Senior Product Designer & Frontend Developer
            </span>
            <h1 id="hero-title" class="u-heading-xl hero__title">
              <span class="glitch" data-text="Создаю цифровые впечатления">Создаю цифровые впечатления</span>,
              <span class="u-gradient-text magic-pulse">которые продают опыт за первые 15 секунд</span>
            </h1>
            <p class="u-text-lead">
              10+ лет в продуктовых командах: Material Design, Human Interface Guidelines, AAA-гейминговые UI.
              Собираю единый визуально-кодовый язык, чтобы ваш продукт оставался узнаваемым и доступным.
            </p>

            <div class="hero__actions" role="group" aria-label="Основные действия">
              <a class="u-btn-primary hero__cta" href="mailto:renzo.dev@gmail.com">
                <EnvelopeIcon class="icon" />
                Связаться со мной
              </a>
              <a href="#projects" class="u-btn-secondary hero__cta">
                <ArrowDownIcon class="icon" />
                Мои проекты
              </a>
            </div>
          </Motion>

          <Motion
            class="hero__visual"
            :initial="{ opacity: 0, scale: 0.85 }"
            :enter="{
              opacity: 1,
              scale: 1,
              transition: { duration: 0.9, ease: 'easeOut', delay: 0.2 },
            }"
          >
            <HeroLottie />
          </Motion>
        </div>
      </div>
    </section>

    <!-- Expertise Section -->
    <section id="skills" class="expertise u-section" aria-labelledby="skills-title">
      <div class="u-container">
        <Motion
          :initial="{ opacity: 0, y: 40 }"
          :enter="{
            opacity: 1,
            y: 0,
            transition: { duration: 0.8, ease: 'easeOut' },
          }"
        >
          <header class="expertise__header">
            <h2 id="skills-title" class="u-heading-lg expertise__title">Экспертиза</h2>
            <p class="expertise__subtitle">Создаю продукты, которые пользователи любят</p>
          </header>

          <div class="u-grid-auto expertise__grid">
            <div v-for="skill in skills" :key="skill.name" class="expertise__item card-3d glow-border">
              <div class="expertise__icon magic-rotate">
                <component :is="skill.icon" />
              </div>
              <h3 class="expertise__name">{{ skill.name }}</h3>
              <p class="expertise__description">{{ skill.description }}</p>
            </div>
          </div>
        </Motion>
      </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="projects u-section" aria-labelledby="projects-title">
      <div class="u-container">
        <Motion
          :initial="{ opacity: 0, y: 40 }"
          :enter="{
            opacity: 1,
            y: 0,
            transition: { duration: 0.8, ease: 'easeOut' },
          }"
        >
          <header class="projects__header">
            <h2 id="projects-title" class="u-heading-lg projects__title">Проекты</h2>
            <p class="projects__subtitle">
              Сочетаю исследовательский подход и быструю проверку гипотез.
              Запускаю MVP с вау-эффектом, который выдерживает масштабирование.
            </p>
          </header>

          <div class="projects__list">
            <Motion
              v-for="(project, index) in projects"
              :key="project.title"
              class="project card-3d glow-border"
              :initial="{ opacity: 0, y: 40 }"
              :enter="{
                opacity: 1,
                y: 0,
                transition: { duration: 0.8, ease: 'easeOut', delay: index * 0.2 },
              }"
            >
              <article
                class="project__article"
                :aria-labelledby="`project-${index}-title`"
                :aria-describedby="`project-${index}-desc`"
              >
                <div class="project__image">
                  <NuxtImg
                    :src="project.image"
                    :alt="project.title"
                    width="600"
                    height="400"
                    loading="lazy"
                  />
                </div>
                <div class="project__content">
                  <h3 :id="`project-${index}-title`" class="project__title">{{ project.title }}</h3>
                  <p :id="`project-${index}-desc`" class="project__description">{{ project.description }}</p>
                  <div class="project__metrics" role="list">
                    <div class="project__metric" role="listitem">
                      <span>{{ project.metrics.users }}</span>
                      <p>пользователей</p>
                    </div>
                    <div class="project__metric" role="listitem">
                      <span>{{ project.metrics.rating }}</span>
                      <p>рейтинг</p>
                    </div>
                    <div class="project__metric" role="listitem">
                      <span>{{ project.metrics.conversion }}</span>
                      <p>конверсия</p>
                    </div>
                  </div>
                  <ul class="project__tech" aria-label="Использованные технологии">
                    <li v-for="tech in project.technologies" :key="tech" class="project__tech-item">
                      {{ tech }}
                    </li>
                  </ul>
                </div>
              </article>
            </Motion>
          </div>
        </Motion>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about u-section" aria-labelledby="about-title">
      <div class="u-container">
        <Motion
          :initial="{ opacity: 0, y: 40 }"
          :enter="{
            opacity: 1,
            y: 0,
            transition: { duration: 0.8, ease: 'easeOut' },
          }"
        >
          <header class="about__header">
            <h2 id="about-title" class="u-heading-lg about__title">Обо мне</h2>
            <p class="about__subtitle">10+ лет создаю цифровые продукты</p>
          </header>

          <div class="about__stats" role="list">
            <div v-for="stat in stats" :key="stat.label" class="about__stat" role="listitem">
              <span class="about__stat-number">{{ stat.value }}</span>
              <p class="about__stat-label">{{ stat.label }}</p>
            </div>
          </div>

          <div class="about__timeline">
            <h3 class="about__timeline-title">Опыт работы</h3>
            <ol class="about__timeline-list">
              <li v-for="(item, index) in timeline" :key="index" class="about__timeline-item">
                <div class="about__timeline-year">{{ item.year }}</div>
                <div class="about__timeline-content">
                  <h4 class="about__timeline-role">{{ item.role }}</h4>
                  <p class="about__timeline-company">{{ item.company }}</p>
                  <p class="about__timeline-description">{{ item.description }}</p>
                </div>
              </li>
            </ol>
          </div>
        </Motion>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact u-section" aria-labelledby="contact-title">
      <div class="u-container">
        <Motion
          :initial="{ opacity: 0, y: 40 }"
          :enter="{
            opacity: 1,
            y: 0,
            transition: { duration: 0.8, ease: 'easeOut' },
          }"
        >
          <header class="contact__header">
            <h2 id="contact-title" class="u-heading-lg contact__title">Связаться со мной</h2>
            <p class="contact__subtitle">Готов обсудить ваш проект</p>
          </header>

          <div class="contact__content">
            <div class="contact__info">
              <h3 class="contact__info-title">Контакты</h3>
              <div class="contact__links">
                <a
                  v-for="link in contactLinks"
                  :key="link.name"
                  :href="link.url"
                  class="contact__link"
                  target="_blank"
                  rel="noopener"
                >
                  <component :is="link.icon" class="contact__icon" />
                  {{ link.name }}
                </a>
              </div>
            </div>

            <form class="contact__form" aria-labelledby="contact-form-title">
              <h3 id="contact-form-title" class="sr-only">Форма обратной связи</h3>
              <div class="contact__form-group">
                <label for="name" class="contact__label">Имя</label>
                <input type="text" id="name" name="name" class="contact__input" required autocomplete="name">
              </div>
              <div class="contact__form-group">
                <label for="email" class="contact__label">Email</label>
                <input type="email" id="email" name="email" class="contact__input" required autocomplete="email">
              </div>
              <div class="contact__form-group">
                <label for="message" class="contact__label">Сообщение</label>
                <textarea id="message" name="message" rows="5" class="contact__textarea" required></textarea>
              </div>
              <button type="submit" class="u-btn-primary contact__submit">
                Отправить сообщение
              </button>
            </form>
          </div>
        </Motion>
      </div>
    </section>
  </div>
</template>

<script setup>
// Данные для секций
const skills = ref([
  {
    name: 'Product Design',
    description: 'Создаю пользовательские интерфейсы с фокусом на конверсию и доступность',
    icon: 'DesignIcon'
  },
  {
    name: 'Frontend Development',
    description: 'Vue.js, React, TypeScript. Современные фреймворки и лучшие практики',
    icon: 'CodeIcon'
  },
  {
    name: 'UX Research',
    description: 'Исследую пользователей, тестирую гипотезы, оптимизирую воронки',
    icon: 'ResearchIcon'
  },
  {
    name: 'Design Systems',
    description: 'Создаю масштабируемые дизайн-системы для продуктовых команд',
    icon: 'SystemIcon'
  }
])

const projects = ref([
  {
    title: 'E-commerce Platform',
    description: 'Полнофункциональная платформа для онлайн-торговли с современным UI/UX',
    image: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=400&fit=crop',
    metrics: {
      users: '50K+',
      rating: '4.9',
      conversion: '+40%'
    },
    technologies: ['Vue.js', 'TypeScript', 'Node.js', 'PostgreSQL']
  },
  {
    title: 'Mobile Banking App',
    description: 'Мобильное приложение для банка с фокусом на безопасность и удобство',
    image: 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=600&h=400&fit=crop',
    metrics: {
      users: '100K+',
      rating: '4.8',
      conversion: '+25%'
    },
    technologies: ['React Native', 'TypeScript', 'Node.js', 'MongoDB']
  },
  {
    title: 'SaaS Dashboard',
    description: 'Административная панель для управления бизнес-процессами',
    image: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=400&fit=crop',
    metrics: {
      users: '25K+',
      rating: '4.7',
      conversion: '+60%'
    },
    technologies: ['Vue.js', 'Nuxt', 'Python', 'FastAPI']
  }
])

const stats = ref([
  { value: '10+', label: 'лет опыта' },
  { value: '50+', label: 'проектов' },
  { value: '100K+', label: 'пользователей' },
  { value: '4.9', label: 'средний рейтинг' }
])

const timeline = ref([
  {
    year: '2020-2024',
    role: 'Senior Product Designer',
    company: 'TechCorp',
    description: 'Руководил дизайном продуктов для 100K+ пользователей. Создал дизайн-систему, которая ускорила разработку на 40%.'
  },
  {
    year: '2018-2020',
    role: 'Frontend Developer',
    company: 'StartupXYZ',
    description: 'Разрабатывал пользовательские интерфейсы на Vue.js и React. Участвовал в создании MVP, который привлек $2M инвестиций.'
  },
  {
    year: '2016-2018',
    role: 'UI/UX Designer',
    company: 'DesignStudio',
    description: 'Создавал дизайн для веб-приложений и мобильных приложений. Специализировался на Material Design и Human Interface Guidelines.'
  }
])

const contactLinks = ref([
  { name: 'renzo.dev@gmail.com', url: 'mailto:renzo.dev@gmail.com', icon: 'EnvelopeIcon' },
  { name: 'GitHub', url: 'https://github.com/Renzo-Dev', icon: 'GithubIcon' },
  { name: 'LinkedIn', url: 'https://linkedin.com/in/renzo', icon: 'LinkedinIcon' },
  { name: 'Dribbble', url: 'https://dribbble.com/renzo', icon: 'DribbbleIcon' }
])
</script>

<style scoped>
/* Базовые стили для секций */
.u-section {
  padding: 4rem 0;
}

.u-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.u-heading-xl {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 700;
  line-height: 1.1;
  margin-bottom: 1.5rem;
}

.u-heading-lg {
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 600;
  line-height: 1.2;
  margin-bottom: 1rem;
}

.u-text-lead {
  font-size: 1.125rem;
  color: #cbd5e1;
  max-width: 600px;
  margin-bottom: 2rem;
}

.u-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(56, 189, 248, 0.15);
  color: #bae6fd;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 1.5rem;
}

.u-gradient-text {
  background: linear-gradient(120deg, #38bdf8, #a855f7);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.u-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #38bdf8, #a855f7);
  color: #050714;
  border-radius: 999px;
  text-decoration: none;
  font-weight: 600;
  transition: transform 0.3s ease;
}

.u-btn-primary:hover {
  transform: translateY(-2px);
}

.u-btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #f8fafc;
  border-radius: 999px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.u-btn-secondary:hover {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(148, 163, 184, 0.4);
}

/* Hero Section */
.hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.hero__background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: -1;
}

.hero__layer {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-size: cover;
  background-position: center;
  opacity: 0.1;
}

.hero__layer--base {
  background-image: url('https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=1920&h=1080&fit=crop');
}

.hero__layer--mid {
  background-image: url('https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1920&h=1080&fit=crop');
}

.hero__layer--grid {
  background-image: url('https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=1920&h=1080&fit=crop');
}

.hero__content {
  position: relative;
  z-index: 1;
}

.hero__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  align-items: center;
  gap: 3rem;
}

.hero__visual {
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero__actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

/* Expertise Section */
.expertise {
  background: rgba(5, 7, 20, 0.5);
}

.expertise__title {
  text-align: center;
  margin-bottom: 0.5rem;
}

.expertise__subtitle {
  text-align: center;
  color: #94a3b8;
  margin-bottom: 3rem;
}

.expertise__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.expertise__item {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(148, 163, 184, 0.18);
  border-radius: 1rem;
  padding: 2rem;
  text-align: center;
  transition: transform 0.3s ease;
}

.expertise__item:hover {
  transform: translateY(-4px);
}

.expertise__icon {
  width: 3rem;
  height: 3rem;
  margin: 0 auto 1rem;
  background: linear-gradient(135deg, #38bdf8, #a855f7);
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #050714;
}

.expertise__name {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #f8fafc;
}

.expertise__description {
  color: #94a3b8;
  line-height: 1.6;
}

/* Projects Section */
.projects__title {
  text-align: center;
  margin-bottom: 0.5rem;
}

.projects__subtitle {
  text-align: center;
  color: #94a3b8;
  margin-bottom: 3rem;
}

.projects__list {
  display: flex;
  flex-direction: column;
  gap: 3rem;
}

.project {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  align-items: center;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(148, 163, 184, 0.18);
  border-radius: 1rem;
  padding: 2rem;
}

.project__image {
  border-radius: 0.75rem;
  overflow: hidden;
}

.project__image img {
  width: 100%;
  height: auto;
  display: block;
}

.project__title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #f8fafc;
}

.project__description {
  color: #94a3b8;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.project__metrics {
  display: flex;
  gap: 2rem;
  margin-bottom: 1.5rem;
}

.project__metric {
  text-align: center;
}

.project__metric span {
  display: block;
  font-size: 1.5rem;
  font-weight: 600;
  color: #38bdf8;
  margin-bottom: 0.25rem;
}

.project__metric p {
  color: #94a3b8;
  font-size: 0.875rem;
  margin: 0;
}

.project__tech {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.project__tech-item {
  background: rgba(56, 189, 248, 0.15);
  color: #bae6fd;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
}

/* About Section */
.about {
  background: rgba(5, 7, 20, 0.5);
}

.about__title {
  text-align: center;
  margin-bottom: 0.5rem;
}

.about__subtitle {
  text-align: center;
  color: #94a3b8;
  margin-bottom: 3rem;
}

.about__stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  margin-bottom: 4rem;
}

.about__stat {
  text-align: center;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(148, 163, 184, 0.18);
  border-radius: 1rem;
  padding: 2rem;
}

.about__stat-number {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  color: #38bdf8;
  margin-bottom: 0.5rem;
}

.about__stat-label {
  color: #94a3b8;
  margin: 0;
}

.about__timeline-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 2rem;
  color: #f8fafc;
}

.about__timeline-item {
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.about__timeline-year {
  font-weight: 600;
  color: #38bdf8;
  font-size: 1.125rem;
}

.about__timeline-role {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
  color: #f8fafc;
}

.about__timeline-company {
  color: #38bdf8;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.about__timeline-description {
  color: #94a3b8;
  line-height: 1.6;
  margin: 0;
}

/* Contact Section */
.contact__title {
  text-align: center;
  margin-bottom: 0.5rem;
}

.contact__subtitle {
  text-align: center;
  color: #94a3b8;
  margin-bottom: 3rem;
}

.contact__content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
}

.contact__info-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  color: #f8fafc;
}

.contact__links {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.contact__link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #94a3b8;
  text-decoration: none;
  transition: color 0.3s ease;
}

.contact__link:hover {
  color: #38bdf8;
}

.contact__icon {
  width: 1.25rem;
  height: 1.25rem;
}

.contact__form-group {
  margin-bottom: 1.5rem;
}

.contact__label {
  display: block;
  margin-bottom: 0.5rem;
  color: #f8fafc;
  font-weight: 500;
}

.contact__input,
.contact__textarea {
  width: 100%;
  padding: 0.75rem;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  color: #f8fafc;
  font-size: 1rem;
}

.contact__input:focus,
.contact__textarea:focus {
  outline: none;
  border-color: #38bdf8;
}

.contact__submit {
  width: 100%;
  justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero__actions {
    flex-direction: column;
  }

  .hero__grid {
    grid-template-columns: 1fr;
  }

  .hero__visual {
    order: -1;
  }
  
  .project {
    grid-template-columns: 1fr;
  }
  
  .about__timeline-item {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .contact__content {
    grid-template-columns: 1fr;
  }
  
  .project__metrics {
    justify-content: center;
  }
}
</style>