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
        <div class="welcome-card card">
          <h2>Привет, {{ user?.name }}! 👋</h2>
          <p class="slogan">Контролируй свои привычки, управляй своей жизнью</p>
          <p class="description">Отслеживайте потребление алкоголя, анализируйте свои привычки и принимайте осознанные решения</p>
        </div>

        <div class="date-selector">
          <label for="date-input" class="date-label">Выберите дату:</label>
          <div class="date-controls">
            <UiButton @click="previousDay" variant="outline" size="sm">
              ←
            </UiButton>
            <input
              id="date-input"
              v-model="selectedDate"
              type="date"
              class="date-input"
              :max="today"
            />
            <UiButton @click="nextDay" variant="outline" size="sm" :disabled="selectedDate >= today">
              →
            </UiButton>
            <UiButton @click="selectToday" variant="outline" size="sm">
              Сегодня
            </UiButton>
          </div>
        </div>

        <div class="content-grid">
          <div class="left-column">
            <ClientOnly>
              <AlcoholAddEntryForm
                v-if="Object.keys(alcoholTypes).length > 0"
                :alcohol-types="alcoholTypes"
                :selected-date="selectedDate"
                @success="handleEntryAdded"
              />
              <template #fallback>
                <div class="loading-placeholder">
                  <div class="spinner"></div>
                  <p>Загрузка...</p>
                </div>
              </template>
            </ClientOnly>

            <ClientOnly>
              <AlcoholStatistics ref="statisticsRef" />
            </ClientOnly>
          </div>

          <div class="right-column">
            <ClientOnly>
              <AlcoholEntriesList :selected-date="selectedDate" />
            </ClientOnly>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { user, logout, isLoading } = useAuth()
const { getAlcoholTypes, getEntries, alcoholTypes } = useAlcoholEntries()

const statisticsRef = ref()

const today = computed(() => {
  const date = new Date()
  return date.toISOString().split('T')[0]
})

const selectedDate = ref(today.value)

const handleLogout = async () => {
  await logout()
}

const previousDay = () => {
  const date = new Date(selectedDate.value)
  date.setDate(date.getDate() - 1)
  selectedDate.value = date.toISOString().split('T')[0]
}

const nextDay = () => {
  const date = new Date(selectedDate.value)
  date.setDate(date.getDate() + 1)
  const newDate = date.toISOString().split('T')[0]
  if (newDate <= today.value) {
    selectedDate.value = newDate
  }
}

const selectToday = () => {
  selectedDate.value = today.value
}

const loadData = async () => {
  try {
    await getEntries({ date: selectedDate.value })
  } catch (error) {
    console.error('Ошибка загрузки данных:', error)
  }
}

const handleEntryAdded = () => {
  // Обновляем статистику после добавления записи
  if (statisticsRef.value) {
    statisticsRef.value.loadStatistics()
  }
}

// Загружаем типы алкоголя при монтировании
onMounted(async () => {
  try {
    await getAlcoholTypes()
    await loadData()
  } catch (error) {
    console.error('Ошибка инициализации:', error)
  }
})

// Перезагружаем записи при изменении даты
watch(selectedDate, () => {
  loadData()
})
</script>

<style scoped>
.page {
  min-height: 100vh;
  padding: var(--spacing-xl) 0;
  width: 100%;
  overflow-x: hidden; /* Предотвращает горизонтальную прокрутку */
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
  width: 100%;
  min-width: 0;
}

.welcome-card {
  margin-bottom: var(--spacing-2xl);
  padding: var(--spacing-2xl);
  background: var(--gradient-hero);
  color: #FFFFFF;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-glow);
  width: 100%;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
}

.welcome-card::before {
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

.welcome-card h2 {
  color: #FFFFFF;
  margin: 0 0 var(--spacing-md) 0;
  font-family: var(--font-family-display);
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  position: relative;
}

.slogan {
  color: rgba(255, 255, 255, 0.95);
  margin: 0 0 var(--spacing-sm) 0;
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
}

.description {
  color: rgba(255, 255, 255, 0.85);
  margin: 0;
  font-size: var(--font-size-base);
}

.date-selector {
  margin-bottom: var(--spacing-2xl);
  padding: var(--spacing-xl);
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(12px);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  width: 100%;
  box-sizing: border-box;
}

.date-label {
  display: block;
  margin-bottom: var(--spacing-md);
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-medium);
  color: var(--color-text);
}

.date-controls {
  display: flex;
  gap: var(--spacing-md);
  align-items: center;
  flex-wrap: wrap;
}

.date-input {
  flex: 1;
  min-width: 200px;
  padding: var(--spacing-md);
  font-family: var(--font-family-base);
  font-size: var(--font-size-base);
  color: var(--color-text);
  background-color: #FFFFFF;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  outline: none;
}

.date-input:hover {
  border-color: var(--color-accent);
}

.date-input:focus {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(400px, 1fr) minmax(400px, 1fr);
  gap: var(--spacing-xl);
  align-items: start;
  width: 100%;
  min-height: 600px;
}

.left-column,
.right-column {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xl);
  width: 100%;
  min-width: 400px;
  min-height: 500px;
}

.loading-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-2xl);
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  min-height: 400px;
}

.loading-placeholder .spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: var(--spacing-md);
}

.loading-placeholder p {
  color: var(--color-text-muted);
  margin: 0;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Планшеты (768px - 1200px) */
@media (max-width: 1200px) {
  .content-grid {
    grid-template-columns: 1fr;
    min-height: auto;
  }

  .left-column,
  .right-column {
    min-width: 0;
    min-height: auto;
  }
}

/* Мобильные устройства (320px - 767px) */
@media (max-width: 767px) {
  .page {
    padding: var(--spacing-md) 0;
  }

  .header {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-md);
  }

  .header-title h1 {
    font-size: var(--font-size-xl);
  }

  .header-subtitle {
    font-size: var(--font-size-xs);
  }

  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-md);
  }

  .welcome-card {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-lg);
  }

  .welcome-card h2 {
    font-size: var(--font-size-xl);
  }

  .slogan {
    font-size: var(--font-size-base);
  }

  .description {
    font-size: var(--font-size-sm);
  }

  .date-selector {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-md);
  }

  .date-controls {
    flex-wrap: wrap;
  }

  .date-input {
    width: 100%;
    min-width: 100%;
  }

  .content-grid {
    gap: var(--spacing-lg);
  }

  .left-column,
  .right-column {
    gap: var(--spacing-lg);
  }
}

/* Очень маленькие экраны (320px - 480px) */
@media (max-width: 480px) {
  .container {
    padding: 0 var(--spacing-sm);
  }

  .header-title h1 {
    font-size: var(--font-size-lg);
  }

  .welcome-card h2 {
    font-size: var(--font-size-lg);
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
