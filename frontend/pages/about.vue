<template>
  <div class="page">
    <div class="container">
      <header class="header">
        <div class="header-content">
          <div class="header-title">
            <h1>🍺 AlcoTracker</h1>
            <p class="header-subtitle">Учет потребления алкоголя</p>
          </div>
          <UiButton @click="handleLogout" variant="outline" size="sm" :loading="isLoading">
            Выйти
          </UiButton>
        </div>
      </header>

      <UiNavigation />

      <main class="main-content">
        <div class="about-hero card">
          <h2>О проекте</h2>
          <p class="about-lead">
            AlcoTracker — это личный дневник потребления алкоголя с наглядной аналитикой.
            Сервис помогает осознанно относиться к своим привычкам: фиксировать каждую запись,
            видеть общую картину и замечать закономерности.
          </p>
        </div>

        <article class="about-content card">
          <section class="about-section">
            <h3>Зачем это нужно</h3>
            <p>
              Многие люди не замечают, сколько алкоголя они выпивают за неделю или месяц.
              Без регулярного учёта легко недооценить частоту и объём. AlcoTracker даёт
              простой инструмент для ежедневного контроля — без лишней сложности и с фокусом
              на ваших данных.
            </p>
          </section>

          <section class="about-section">
            <h3>Основной функционал</h3>
            <ul class="feature-list">
              <li>
                <strong>Дневник записей</strong> — добавляйте напитки с указанием типа,
                объёма в миллилитрах и даты. Просматривайте историю за любой день.
              </li>
              <li>
                <strong>Краткая статистика</strong> — на главной странице отображается
                сводка за неделю: общий объём, количество записей и разбивка по типам напитков.
              </li>
              <li>
                <strong>Подробная аналитика</strong> — графики динамики потребления, тренды,
                распределение по видам напитков и расчёт чистого спирта с учётом крепости.
              </li>
              <li>
                <strong>Анализ по дням недели</strong> — выявляйте, в какие дни вы пьёте
                чаще всего, и отслеживайте серии дней с употреблением и без него.
              </li>
              <li>
                <strong>Гибкие периоды</strong> — статистика доступна за неделю, месяц,
                полгода, год и другие интервалы.
              </li>
            </ul>
          </section>

          <section class="about-section">
            <h3>Как это работает</h3>
            <p>
              После регистрации вы получаете личный кабинет с защищённым доступом.
              Каждая запись сохраняется в вашем профиле. На странице «Статистика»
              данные агрегируются и визуализируются в виде графиков и показателей —
              от среднего потребления в день до максимальных серий «трезвых» дней.
            </p>
            <p>
              AlcoTracker не ставит диагнозов и не заменяет медицинскую консультацию.
              Это инструмент самонаблюдения для тех, кто хочет лучше понимать свои
              привычки и принимать более осознанные решения.
            </p>
          </section>
        </article>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

useSeoMeta({
  title: 'О проекте — AlcoTracker',
  description: 'Узнайте, как AlcoTracker помогает вести учёт алкоголя, анализировать привычки и отслеживать динамику потребления с помощью графиков и статистики.',
})

const { logout, isLoading } = useAuth()

const handleLogout = async () => {
  await logout()
}
</script>

<style scoped>
.page {
  min-height: 100vh;
  padding: var(--spacing-xl) 0;
  width: 100%;
  overflow-x: hidden;
}

.header {
  margin-bottom: var(--spacing-2xl);
  padding: var(--spacing-xl);
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(12px);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  width: 100%;
  box-sizing: border-box;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header-title h1 {
  margin: 0;
  font-family: var(--font-family-display);
  font-weight: var(--font-weight-bold);
  color: var(--color-text);
  font-size: var(--font-size-2xl);
  letter-spacing: -0.02em;
}

.header-subtitle {
  margin: var(--spacing-xs) 0 0 0;
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-normal);
}

.main-content {
  animation: fadeIn 0.5s ease-out;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-2xl);
}

.about-hero {
  padding: var(--spacing-2xl);
  background: var(--gradient-hero);
  color: #FFFFFF;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-glow);
  position: relative;
  overflow: hidden;
}

.about-hero::before {
  content: '';
  position: absolute;
  top: -40%;
  right: -10%;
  width: 280px;
  height: 280px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  pointer-events: none;
}

.about-hero h2 {
  margin: 0 0 var(--spacing-md) 0;
  color: #FFFFFF;
  font-family: var(--font-family-display);
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  position: relative;
}

.about-lead {
  margin: 0;
  color: rgba(255, 255, 255, 0.9);
  font-size: var(--font-size-lg);
  line-height: var(--line-height-relaxed);
  position: relative;
}

.about-content {
  padding: var(--spacing-2xl);
}

.about-section + .about-section {
  margin-top: var(--spacing-2xl);
  padding-top: var(--spacing-2xl);
  border-top: 1px solid var(--color-border);
}

.about-section h3 {
  margin: 0 0 var(--spacing-md) 0;
  font-family: var(--font-family-display);
  font-size: var(--font-size-xl);
  color: var(--color-text);
}

.about-section p {
  margin: 0 0 var(--spacing-md) 0;
  color: var(--color-text-light);
  line-height: var(--line-height-relaxed);
}

.about-section p:last-child {
  margin-bottom: 0;
}

.feature-list {
  margin: 0;
  padding-left: var(--spacing-lg);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.feature-list li {
  color: var(--color-text-light);
  line-height: var(--line-height-relaxed);
}

.feature-list strong {
  color: var(--color-text);
}

@media (max-width: 767px) {
  .page {
    padding: var(--spacing-md) 0;
  }

  .header {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-md);
  }

  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-md);
  }

  .header-title h1 {
    font-size: var(--font-size-xl);
  }

  .about-hero,
  .about-content {
    padding: var(--spacing-lg);
  }

  .about-hero h2 {
    font-size: var(--font-size-xl);
  }

  .about-lead {
    font-size: var(--font-size-base);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
