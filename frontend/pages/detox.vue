<template>
  <div class="page">
    <div class="container">
      <header class="header">
        <div class="header-content">
          <UiAppBrand logo-tag="h1" size="lg" class="header-title" />
          <UiButton @click="handleLogout" variant="outline" size="sm" :loading="authLoading">
            Выйти
          </UiButton>
        </div>
      </header>

      <UiNavigation />

      <main class="main-content">
        <div class="hero-card">
          <h2>🌿 Здоровье и Детокс</h2>
          <p>
            Отслеживайте периоды трезвости, следите за восстановлением организма
            и мотивируйте себя на осознанный образ жизни.
          </p>
        </div>

        <div v-if="isLoading && !insights" class="loading-card">
          <div class="spinner"></div>
          <p>Загрузка данных детокса...</p>
        </div>

        <template v-else-if="insights">
          <DetoxStreakStats
            :current-streak-days="insights.current_streak_days"
            :max-streak-days="insights.max_streak_days"
          />

          <DetoxCalendar
            :calendar-data="insights.calendar_data"
            :year="selectedYear"
            :min-year="insights.period.min_year"
            :max-year="insights.period.max_year"
            @change-year="handleYearChange"
          />
        </template>

        <div v-else class="empty-card">
          <p>Не удалось загрузить данные раздела. Попробуйте обновить страницу.</p>
          <UiButton variant="outline" size="sm" @click="loadInsights">
            Повторить
          </UiButton>
        </div>
      </main>

      <UiFooter />
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { user, logout, isLoading: authLoading } = useAuth()
const { insights, isLoading, getInsights } = useDetox()

const now = new Date()
const selectedYear = ref(now.getFullYear())

const handleLogout = async () => {
  await logout()
}

const loadInsights = async () => {
  try {
    const data = await getInsights(selectedYear.value)
    selectedYear.value = data.period.year
  } catch (error) {
    console.error('Ошибка загрузки данных детокса:', error)
  }
}

const handleYearChange = async (year: number) => {
  if (!insights.value) {
    selectedYear.value = year
    await loadInsights()
    return
  }

  const { min_year: minYear, max_year: maxYear } = insights.value.period
  selectedYear.value = Math.max(minYear, Math.min(year, maxYear))
  await loadInsights()
}

onMounted(loadInsights)
</script>

<style scoped>
.main-content {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-2xl);
}

.hero-card {
  padding: var(--spacing-2xl);
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-glow);
}

.hero-card h2 {
  margin: 0 0 var(--spacing-sm);
  color: #ffffff;
  font-family: var(--font-family-display);
}

.hero-card p {
  margin: 0;
  color: rgba(255, 255, 255, 0.92);
  line-height: 1.6;
}

.loading-card,
.empty-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-md);
  padding: var(--spacing-3xl);
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 767px) {
  .hero-card {
    padding: var(--spacing-lg);
  }
}
</style>
