<template>
  <div class="page">
    <div class="container">
      <header class="header">
        <div class="header-content">
          <UiAppBrand logo-tag="h1" size="lg" class="header-title" />
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
            AlcoTracker — личный дневник потребления алкоголя с наглядной аналитикой
            и инструментами для осознанного контроля привычек. Фиксируйте записи,
            изучайте динамику и отслеживайте периоды трезвости.
          </p>
        </div>

        <article class="about-content card">
          <section class="about-section">
            <h3>Зачем это нужно</h3>
            <p>
              Без регулярного учёта легко недооценить, сколько и как часто вы пьёте.
              500 мл водки и 2000 мл пива — разный объём жидкости, но совсем разное
              влияние на организм. AlcoTracker помогает видеть полную картину: не только
              литры, но и расчёт чистого спирта, тренды и паттерны по дням недели.
            </p>
          </section>

          <section class="about-section">
            <h3>Основной функционал</h3>
            <ul class="feature-list">
              <li>
                <strong>Дневник записей</strong> — добавляйте напитки с типом, объёмом
                в миллилитрах и датой. Просматривайте и редактируйте историю за любой день.
              </li>
              <li>
                <strong>Краткая статистика</strong> — на главной странице сводка за неделю:
                общий объём, количество записей и разбивка по типам напитков.
              </li>
              <li>
                <strong>Подробная аналитика</strong> — графики динамики потребления и тренда,
                фильтр по конкретному напитку, расчёт чистого спирта с учётом крепости,
                анализ по дням недели. Периоды: неделя, месяц, полгода, год и другие.
              </li>
              <li>
                <strong>Здоровье и Детокс</strong> — текущий стрик трезвости, личный рекорд,
                годовой календарь-теплокарта с цветовой индикацией: дни употребления,
                метаболизма и восстановления.
              </li>
            </ul>
          </section>

          <section class="about-section">
            <h3>Как считается чистый спирт</h3>
            <p>
              Для каждого типа напитка задана средняя крепость (ABV). Например:
              500 мл водки (40%) = 200 мл чистого спирта, а 500 мл пива (5%) = 25 мл.
              Это позволяет сравнивать разные напитки по реальному воздействию, а не
              только по объёму жидкости.
            </p>
          </section>

          <section class="about-section">
            <h3>Как это работает</h3>
            <p>
              После регистрации вы получаете личный кабинет с защищённым доступом.
              Каждая запись сохраняется в вашем профиле. На странице «Статистика»
              данные агрегируются в графики и показатели — от среднего потребления
              в день до максимальных серий трезвых дней. В разделе «Детокс» — наглядный
              календарь за год и счётчики мотивации.
            </p>
            <p>
              AlcoTracker не ставит диагнозов и не заменяет медицинскую консультацию.
              Это инструмент самонаблюдения для тех, кто хочет лучше понимать свои
              привычки и принимать более осознанные решения.
            </p>
          </section>
        </article>
      </main>

      <UiFooter />
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

useSeoMeta({
  title: 'О проекте — AlcoTracker',
  description: 'AlcoTracker — дневник учёта алкоголя с аналитикой, расчётом чистого спирта, статистикой по дням недели и разделом «Здоровье и Детокс».',
})

const { logout, isLoading } = useAuth()

const handleLogout = async () => {
  await logout()
}
</script>

<style scoped>
.main-content {
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
</style>
