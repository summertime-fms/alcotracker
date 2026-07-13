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
        <div class="stats-header-card">
          <h2>📊 Статистика потребления</h2>
          <p>Анализ ваших привычек за выбранный период</p>
        </div>

        <div class="filters-card">
          <h3>Фильтры</h3>

          <div class="filters-grid">
            <div class="filter-group">
              <UiSelect
                v-model="filters.period"
                :options="periodOptions"
                label="Период"
                @change="(value) => handlePeriodChange(value)"
              />
              <p v-if="filters.startDate && filters.endDate" class="period-summary">
                {{ formatSelectedPeriod() }}
              </p>
            </div>
          </div>
        </div>

        <div v-if="isLoading" class="loading-card">
          <div class="spinner"></div>
          <p>Загрузка статистики...</p>
        </div>

        <div v-else class="dashboards-container">
          <!-- График тренда с трендовой линией -->
          <div class="chart-card">
            <div class="chart-header">
              <h3>📈 Динамика потребления и тренд</h3>
              <div class="chart-info">
                <span class="info-badge">
                  Всего: <strong>{{ amountStatistics?.total_ml || 0 }} мл</strong>
                </span>
                <span class="info-badge">
                  Среднее в день: <strong>{{ calculateAveragePerDay() }} мл</strong>
                </span>
                <span class="info-badge">
                  Период: {{ formatPeriod() }}
                </span>
              </div>
            </div>

            <TrendChart
              v-if="hasAmountData"
              :data="amountStatistics?.data || {}"
              label="Потребление (мл)"
            />

            <div v-else class="empty-state">
              <div class="empty-icon">📈</div>
              <p>Нет данных за выбранный период</p>
            </div>

            <!-- Дополнительная информация о тренде -->
            <div v-if="hasAmountData" class="trend-info">
              <div class="trend-stat">
                <span class="trend-label">Общее потребление:</span>
                <span class="trend-value">{{ amountStatistics?.total_ml || 0 }} мл</span>
              </div>
              <div class="trend-stat">
                <span class="trend-label">Дней с записями:</span>
                <span class="trend-value">{{ getDaysWithEntries() }}</span>
              </div>
              <div class="trend-stat">
                <span class="trend-label">Максимум за день:</span>
                <span class="trend-value">{{ getMaxDayConsumption() }} мл</span>
              </div>
            </div>
          </div>

          <!-- Дашборд по количеству выпитого -->
          <div class="chart-card">
            <div class="chart-header">
              <h3>Потребление по количеству выпитого</h3>
              <div class="chart-info">
                <span v-if="selectedDrinkLabel" class="info-badge">
                  Напиток: <strong>{{ selectedDrinkLabel }}</strong>
                </span>
                <span class="info-badge">
                  Всего: <strong>{{ amountStatistics?.total_ml || 0 }} мл</strong>
                </span>
                <span class="info-badge">
                  Период: {{ formatPeriod() }}
                </span>
              </div>
            </div>

            <div class="chart-filters">
              <UiSelect
                v-model="amountDrinkFilter"
                :options="drinkFilterOptions"
                label="Напиток"
                @change="handleAmountDrinkFilterChange"
              />
              <p v-if="amountDrinkFilter === 'all'" class="chart-hint">
                Общий объём жидкости без учёта крепости. 2000 мл пива и 500 мл водки на графике
                выглядят сопоставимо, хотя влияние на организм разное. Для оценки «вреда» смотрите
                раздел «Чистый спирт» ниже.
              </p>
            </div>

            <div class="amount-chart-body" :class="{ 'amount-chart-body--loading': isAmountLoading }">
              <StatisticsChart
                v-if="hasAmountData && !isAmountLoading"
                :data="amountStatistics?.data || {}"
                :label="amountChartLabel"
                group-by="amount"
              />

              <div v-else-if="isAmountLoading" class="empty-state">
                <div class="spinner"></div>
                <p>Обновление графика...</p>
              </div>

              <div v-else class="empty-state">
                <div class="empty-icon">📊</div>
                <p>{{ amountEmptyStateMessage }}</p>
              </div>
            </div>
          </div>

          <!-- Разделитель для секции чистого спирта -->
          <div class="section-divider">
            <h2 class="section-title">🧪 Статистика по чистому спирту</h2>
            <p class="section-description">
              Расчет на основе крепости напитков. Например: 500 мл водки (40%) = 200 мл чистого спирта,
              а 500 мл пива (5%) = всего 25 мл чистого спирта.
            </p>
          </div>

          <!-- График тренда по чистому спирту -->
          <div class="chart-card">
            <div class="chart-header">
              <h3>📊 Динамика потребления чистого спирта</h3>
              <div class="chart-info">
                <span class="info-badge">
                  Всего: <strong>{{ pureAlcoholStatistics?.total_pure_alcohol_ml || 0 }} мл</strong>
                </span>
                <span class="info-badge">
                  Среднее в день: <strong>{{ calculatePureAlcoholAverage() }} мл</strong>
                </span>
                <span class="info-badge">
                  Период: {{ formatPeriod() }}
                </span>
              </div>
            </div>

            <TrendChart
              v-if="hasPureAlcoholData"
              :data="pureAlcoholStatistics?.data || {}"
              label="Чистый спирт (мл)"
            />

            <div v-else class="empty-state">
              <div class="empty-icon">📊</div>
              <p>Нет данных за выбранный период</p>
            </div>

            <!-- Дополнительная информация о чистом спирте -->
            <div v-if="hasPureAlcoholData" class="trend-info pure-alcohol-info">
              <div class="trend-stat">
                <span class="trend-label">Чистого спирта:</span>
                <span class="trend-value">{{ pureAlcoholStatistics?.total_pure_alcohol_ml || 0 }} мл</span>
              </div>
              <div class="trend-stat">
                <span class="trend-label">Макс. дней подряд:</span>
                <span class="trend-value">{{ getMaxConsecutiveDaysWithAlcohol() }} {{ getMaxConsecutiveDaysWithAlcohol() === 1 ? 'день' : getMaxConsecutiveDaysWithAlcohol() < 5 ? 'дня' : 'дней' }}</span>
              </div>
              <div class="trend-stat">
                <span class="trend-label">Макс. дней без спирта:</span>
                <span class="trend-value trend-value-positive">✅ {{ getMaxConsecutiveDaysWithoutAlcohol() }} {{ getMaxConsecutiveDaysWithoutAlcohol() === 1 ? 'день' : getMaxConsecutiveDaysWithoutAlcohol() < 5 ? 'дня' : 'дней' }}</span>
              </div>
              <div class="trend-stat">
                <span class="trend-label">Макс. за день:</span>
                <span class="trend-value">{{ getMaxPureAlcoholConsumption() }} мл</span>
              </div>
            </div>
          </div>

          <!-- Разделитель для секции анализа по дням недели -->
          <div class="section-divider">
            <h2 class="section-title">📅 Анализ по дням недели</h2>
            <p class="section-description">
              В какие дни недели вы чаще всего употребляете алкоголь?
              Эта статистика помогает выявить паттерны и привычки.
            </p>
          </div>

          <!-- График по дням недели -->
          <div class="chart-card">
            <div class="chart-header">
              <h3>📊 Распределение по дням недели</h3>
              <div class="chart-info">
                <span class="info-badge">
                  Всего: <strong>{{ weekdayStatistics?.total || 0 }} мл</strong>
                </span>
                <span class="info-badge">
                  Период: {{ formatPeriod() }}
                </span>
              </div>
            </div>

            <WeekdayChart
              v-if="hasWeekdayData"
              :data="weekdayStatistics.data"
              label="Чистый спирт"
            />

            <div v-else class="empty-state">
              <div class="empty-icon">📅</div>
              <p>Нет данных за выбранный период</p>
            </div>

            <!-- Инсайты по дням недели -->
            <div v-if="hasWeekdayData" class="weekday-insights">
              <div class="insight-item">
                <span class="insight-icon">📈</span>
                <div class="insight-content">
                  <div class="insight-label">Самый активный день:</div>
                  <div class="insight-value">{{ getMostActiveWeekday() }}</div>
                </div>
              </div>
              <div class="insight-item">
                <span class="insight-icon">📉</span>
                <div class="insight-content">
                  <div class="insight-label">Самый спокойный день:</div>
                  <div class="insight-value">{{ getLeastActiveWeekday() }}</div>
                </div>
              </div>
              <div class="insight-item">
                <span class="insight-icon">🥃</span>
                <div class="insight-content">
                  <div class="insight-label">Средняя доза за день:</div>
                  <div class="insight-value">
                    {{ weekdayStatistics?.average_dose_ml || 0 }} мл чистого спирта
                  </div>
                  <div class="insight-hint">
                    В дни с записями ({{ weekdayStatistics?.days_with_entries || 0 }})
                  </div>
                </div>
              </div>
            </div>
          </div>

          <StatisticsHabitPatternAnalysis
            v-if="weekdayStatistics?.pattern_analysis"
            :pattern="weekdayStatistics.pattern_analysis"
          />
        </div>
      </main>

      <UiFooter />
    </div>
  </div>
</template>

<script setup lang="ts">
import TrendChart from '~/components/statistics/TrendChart.vue'
import WeekdayChart from '~/components/statistics/WeekdayChart.vue'

definePageMeta({
  middleware: 'auth',
})

const { user, logout, isLoading: authLoading } = useAuth()
const { getDetailedStatistics, getPureAlcoholStatistics, getWeekdayStatistics, getAlcoholTypes, alcoholTypes } = useAlcoholEntries()

const isLoading = ref(false)
const isAmountLoading = ref(false)
const amountStatistics = ref<any>(null)
const pureAlcoholStatistics = ref<any>(null)
const weekdayStatistics = ref<any>(null)
const amountDrinkFilter = ref('beer')
const isAmountFilterAuto = ref(true)

const today = computed(() => {
  const date = new Date()
  return date.toISOString().split('T')[0]
})

const filters = reactive({
  period: 'last_month',
  startDate: '',
  endDate: '',
})

const periodOptions = [
  { value: 'last_week', label: 'За неделю' },
  { value: 'last_month', label: 'За месяц' },
  { value: 'last_3_months', label: 'За 3 месяца' },
  { value: 'last_6_months', label: 'За полгода' },
  { value: 'last_year', label: 'За год' },
  { value: 'year_to_date', label: 'С начала года' },
]

const DEFAULT_DRINK_TYPE = 'beer'

const drinkFilterOptions = computed(() => [
  { value: 'all', label: 'Все напитки (общий объём)' },
  ...Object.entries(alcoholTypes.value).map(([value, label]) => ({ value, label })),
])

const selectedDrinkLabel = computed(() => {
  if (amountDrinkFilter.value === 'all') return ''
  return alcoholTypes.value[amountDrinkFilter.value] || ''
})

const amountChartLabel = computed(() => {
  if (amountDrinkFilter.value === 'all') {
    return 'Общий объём (мл)'
  }
  return `${selectedDrinkLabel.value} (мл)`
})

const amountEmptyStateMessage = computed(() => {
  if (amountDrinkFilter.value !== 'all' && selectedDrinkLabel.value) {
    return `Нет записей по напитку «${selectedDrinkLabel.value}» за выбранный период`
  }
  return 'Нет данных за выбранный период'
})

const handleLogout = async () => {
  await logout()
}

// Функция для форматирования даты в локальном времени (без сдвига часового пояса)
const formatLocalDate = (date: Date): string => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const handlePeriodChange = (value?: string | number) => {
  const period = value || filters.period
  const now = new Date()

  switch (period) {
    case 'last_week': {
      const weekStart = new Date(now)
      weekStart.setDate(now.getDate() - 6)
      filters.startDate = formatLocalDate(weekStart)
      filters.endDate = today.value
      break
    }
    case 'last_month':
      filters.startDate = formatLocalDate(new Date(now.getFullYear(), now.getMonth(), 1))
      filters.endDate = today.value
      break
    case 'last_3_months':
      filters.startDate = formatLocalDate(new Date(now.getFullYear(), now.getMonth() - 2, 1))
      filters.endDate = today.value
      break
    case 'last_6_months':
      filters.startDate = formatLocalDate(new Date(now.getFullYear(), now.getMonth() - 5, 1))
      filters.endDate = today.value
      break
    case 'last_year':
      filters.startDate = formatLocalDate(new Date(now.getFullYear(), now.getMonth() - 11, 1))
      filters.endDate = today.value
      break
    case 'year_to_date':
      filters.startDate = formatLocalDate(new Date(now.getFullYear(), 0, 1))
      filters.endDate = today.value
      break
  }

  isAmountFilterAuto.value = true
  loadStatistics()
}

const getDefaultDrinkFilter = (entriesByType?: Record<string, number>) => {
  const entries = Object.entries(entriesByType || {})
  if (entries.length === 0) {
    return DEFAULT_DRINK_TYPE
  }

  return entries.reduce((mostPopular, current) => {
    return current[1] > mostPopular[1] ? current : mostPopular
  })[0]
}

const resolveAmountDrinkFilter = (entriesByType?: Record<string, number>) => {
  if (isAmountFilterAuto.value) {
    amountDrinkFilter.value = getDefaultDrinkFilter(entriesByType)
  }
}

const loadAmountStatistics = async (entriesByType?: Record<string, number>) => {
  resolveAmountDrinkFilter(entriesByType)

  const alcoholType = amountDrinkFilter.value === 'all'
    ? undefined
    : amountDrinkFilter.value

  const amountData = await getDetailedStatistics(
    filters.startDate || undefined,
    filters.endDate || undefined,
    'amount',
    alcoholType
  )

  amountStatistics.value = amountData
  return amountData
}

const handleAmountDrinkFilterChange = async () => {
  isAmountFilterAuto.value = false

  try {
    isAmountLoading.value = true
    await loadAmountStatistics(amountStatistics.value?.entries_by_type)
  } catch (error) {
    console.error('Ошибка загрузки статистики по напитку:', error)
  } finally {
    isAmountLoading.value = false
  }
}

const formatSelectedPeriod = () => {
  if (!filters.startDate || !filters.endDate) return ''

  const start = new Date(filters.startDate + 'T00:00:00')
  const end = new Date(filters.endDate + 'T00:00:00')

  return `${start.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })} — ${end.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })}`
}

const loadStatistics = async () => {
  try {
    isLoading.value = true
    const [amountMeta, pureAlcoholData, weekdayData] = await Promise.all([
      getDetailedStatistics(
        filters.startDate || undefined,
        filters.endDate || undefined,
        'amount'
      ),
      getPureAlcoholStatistics(
        filters.startDate || undefined,
        filters.endDate || undefined,
        'amount'
      ),
      getWeekdayStatistics(
        filters.startDate || undefined,
        filters.endDate || undefined,
        'pure_alcohol'
      )
    ])

    pureAlcoholStatistics.value = pureAlcoholData
    weekdayStatistics.value = weekdayData

    if (isAmountFilterAuto.value) {
      amountDrinkFilter.value = getDefaultDrinkFilter(amountMeta.entries_by_type)
    }

    const alcoholType = amountDrinkFilter.value === 'all'
      ? undefined
      : amountDrinkFilter.value

    if (alcoholType) {
      amountStatistics.value = await getDetailedStatistics(
        filters.startDate || undefined,
        filters.endDate || undefined,
        'amount',
        alcoholType
      )
    } else {
      amountStatistics.value = amountMeta
    }
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error)
  } finally {
    isLoading.value = false
  }
}

const formatPeriod = () => {
  const stats = amountStatistics.value
  if (!stats) return ''
  const start = new Date(stats.period.start_date)
  const end = new Date(stats.period.end_date)
  return `${start.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })} - ${end.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })}`
}

const hasAmountData = computed(() => {
  if (!amountStatistics.value?.data) return false
  const values = Object.values(amountStatistics.value.data)
  return values.some(value => Number(value) > 0)
})

const hasPureAlcoholData = computed(() => {
  return pureAlcoholStatistics.value?.data && Object.keys(pureAlcoholStatistics.value.data).length > 0
})

const hasWeekdayData = computed(() => {
  return weekdayStatistics.value?.data && Array.isArray(weekdayStatistics.value.data) && weekdayStatistics.value.data.length > 0
})

// Вспомогательные функции для расчета статистики
const calculateAveragePerDay = () => {
  if (!amountStatistics.value?.data) return 0
  const data = amountStatistics.value.data
  const entries = Object.entries(data)
  if (entries.length === 0) return 0

  const totalDays = entries.length
  const totalMl = amountStatistics.value.total_ml || 0
  const average = Math.round(totalMl / totalDays)

  return average
}

const getDaysWithEntries = () => {
  if (!amountStatistics.value?.data) return 0
  const data = amountStatistics.value.data
  // Подсчитываем дни, где есть потребление (значение > 0)
  const daysWithData = Object.values(data).filter(value => value > 0).length
  return daysWithData
}

const getMaxDayConsumption = () => {
  if (!amountStatistics.value?.data) return 0
  const data = amountStatistics.value.data
  const values = Object.values(data)
  if (values.length === 0) return 0

  return Math.max(...values)
}

const calculatePureAlcoholAverage = () => {
  if (!pureAlcoholStatistics.value?.data) return 0
  const data = pureAlcoholStatistics.value.data
  const entries = Object.entries(data)
  if (entries.length === 0) return 0

  const totalDays = entries.length
  const totalMl = pureAlcoholStatistics.value.total_pure_alcohol_ml || 0
  const average = Math.round(totalMl / totalDays * 10) / 10

  return average
}

const getMaxPureAlcoholConsumption = () => {
  if (!pureAlcoholStatistics.value?.data) return 0
  const data = pureAlcoholStatistics.value.data
  const values = Object.values(data)
  if (values.length === 0) return 0

  return Math.max(...values)
}

// Рассчитать максимальную серию дней с употреблением подряд
const getMaxConsecutiveDaysWithAlcohol = () => {
  if (!pureAlcoholStatistics.value?.data) return 0
  const data = pureAlcoholStatistics.value.data

  // Сортируем даты
  const sortedDates = Object.entries(data).sort((a, b) => {
    return new Date(a[0]).getTime() - new Date(b[0]).getTime()
  })

  if (sortedDates.length === 0) return 0

  let maxStreak = 0
  let currentStreak = 0

  for (let i = 0; i < sortedDates.length; i++) {
    const [dateStr, value] = sortedDates[i]

    // Если есть потребление в этот день
    if (value > 0) {
      currentStreak++
      maxStreak = Math.max(maxStreak, currentStreak)
    } else {
      currentStreak = 0
    }
  }

  return maxStreak
}

// Рассчитать максимальную серию дней БЕЗ употребления подряд
const getMaxConsecutiveDaysWithoutAlcohol = () => {
  if (!pureAlcoholStatistics.value?.data) return 0
  const data = pureAlcoholStatistics.value.data

  // Сортируем даты
  const sortedDates = Object.entries(data).sort((a, b) => {
    return new Date(a[0]).getTime() - new Date(b[0]).getTime()
  })

  if (sortedDates.length === 0) return 0

  let maxStreak = 0
  let currentStreak = 0

  for (let i = 0; i < sortedDates.length; i++) {
    const [dateStr, value] = sortedDates[i]

    // Если НЕТ потребления в этот день
    if (value === 0) {
      currentStreak++
      maxStreak = Math.max(maxStreak, currentStreak)
    } else {
      currentStreak = 0
    }
  }

  return maxStreak
}

// Анализ по дням недели
const getMostActiveWeekday = () => {
  if (!weekdayStatistics.value?.data) return '-'
  const data = weekdayStatistics.value.data
  const maxDay = data.reduce((prev: any, current: any) => {
    return (current.value > prev.value) ? current : prev
  })
  return `${maxDay.key} (${Math.round(maxDay.value)} мл)`
}

const getLeastActiveWeekday = () => {
  if (!weekdayStatistics.value?.data) return '-'
  const data = weekdayStatistics.value.data
  const minDay = data.reduce((prev: any, current: any) => {
    return (current.value < prev.value) ? current : prev
  })
  return `${minDay.key} (${Math.round(minDay.value)} мл)`
}

onMounted(async () => {
  await getAlcoholTypes()
  handlePeriodChange()
})
</script>

<style scoped>
.stats-header-card {
  margin-bottom: var(--spacing-2xl);
  padding: var(--spacing-2xl);
  background: var(--gradient-hero);
  color: #FFFFFF;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-glow);
  position: relative;
  overflow: hidden;
}

.stats-header-card::before {
  content: '';
  position: absolute;
  top: -30%;
  right: -5%;
  width: 240px;
  height: 240px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  pointer-events: none;
}

.stats-header-card h2 {
  margin: 0 0 var(--spacing-sm) 0;
  color: #FFFFFF;
  font-family: var(--font-family-display);
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  position: relative;
}

.stats-header-card p {
  margin: 0;
  color: rgba(255, 255, 255, 0.9);
  font-size: var(--font-size-base);
}

.filters-card {
  margin-bottom: var(--spacing-2xl);
  padding: var(--spacing-xl);
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(12px);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
}

.filters-card h3 {
  margin: 0 0 var(--spacing-lg) 0;
  font-family: var(--font-family-display);
  color: var(--color-text);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-lg);
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.period-summary {
  margin: 0;
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
}

.filter-label {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--color-text);
}

.filter-input {
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

.filter-input:focus {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-3xl);
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  gap: var(--spacing-md);
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

.dashboards-container {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--spacing-2xl);
}

.chart-card {
  padding: var(--spacing-xl);
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-xl);
  flex-wrap: wrap;
  gap: var(--spacing-md);
}

.chart-header h3 {
  margin: 0;
  font-family: var(--font-family-display);
  color: var(--color-text);
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-semibold);
}

.chart-info {
  display: flex;
  gap: var(--spacing-md);
  flex-wrap: wrap;
}

.chart-filters {
  margin-bottom: var(--spacing-xl);
  max-width: 420px;
}

.chart-hint {
  margin: var(--spacing-sm) 0 0 0;
  padding: var(--spacing-md);
  font-size: var(--font-size-sm);
  line-height: 1.5;
  color: var(--color-text-muted);
  background: var(--color-accent-light);
  border: 1px solid rgba(99, 102, 241, 0.12);
  border-radius: var(--radius-md);
}

.amount-chart-body--loading {
  opacity: 0.6;
}

.info-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-sm) var(--spacing-md);
  background: var(--color-accent-light);
  border-radius: var(--radius-full);
  font-size: var(--font-size-sm);
  line-height: 1.35;
  color: var(--color-text-light);
  text-align: center;
}

.info-badge strong {
  color: var(--color-accent);
  font-weight: var(--font-weight-semibold);
}

.empty-state {
  text-align: center;
  padding: var(--spacing-3xl);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: var(--spacing-md);
}

.empty-state p {
  color: var(--color-text-muted);
  margin: var(--spacing-sm) 0;
}

.empty-hint {
  font-size: var(--font-size-sm);
}

.section-divider {
  margin: var(--spacing-3xl) 0 var(--spacing-2xl) 0;
  padding: var(--spacing-2xl);
  background: var(--gradient-soft);
  border-left: 4px solid var(--color-accent);
  border-radius: var(--radius-xl);
  border: 1px solid var(--color-border);
  border-left: 4px solid var(--color-accent);
}

.section-title {
  margin: 0 0 var(--spacing-sm) 0;
  font-family: var(--font-family-display);
  color: var(--color-text);
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
}

.section-description {
  margin: 0;
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
  line-height: 1.6;
}

.trend-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-md);
  margin-top: var(--spacing-xl);
  padding: var(--spacing-lg);
  background: var(--color-accent-light);
  border-radius: var(--radius-lg);
  border: 1px solid rgba(99, 102, 241, 0.1);
}

.pure-alcohol-info {
  background: var(--gradient-soft);
  border: 1px solid var(--color-border);
}

.trend-stat {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.trend-label {
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
  font-weight: var(--font-weight-medium);
}

.trend-value {
  font-size: var(--font-size-lg);
  color: var(--color-text);
  font-weight: var(--font-weight-bold);
}

.trend-value-positive {
  color: var(--color-success);
  font-weight: var(--font-weight-bold);
}

.weekday-insights {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacing-lg);
  margin-top: var(--spacing-xl);
  padding: var(--spacing-lg);
  background: var(--color-accent-light);
  border-radius: var(--radius-lg);
  border: 1px solid rgba(99, 102, 241, 0.1);
}

.insight-item {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-md);
  padding: var(--spacing-md);
  background: #FFFFFF;
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
}

.insight-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.insight-content {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
  flex: 1;
}

.insight-label {
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
  font-weight: var(--font-weight-medium);
}

.insight-value {
  font-size: var(--font-size-base);
  color: var(--color-text);
  font-weight: var(--font-weight-semibold);
}

.insight-hint {
  font-size: var(--font-size-xs);
  color: var(--color-text-muted);
  line-height: var(--line-height-normal);
}

@media (max-width: 767px) {
  .stats-header-card {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-lg);
  }

  .stats-header-card h2 {
    font-size: var(--font-size-xl);
  }

  .filters-card {
    padding: var(--spacing-md);
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .dashboards-container {
    gap: var(--spacing-lg);
  }

  .chart-card {
    padding: var(--spacing-md);
  }

  .chart-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .chart-info {
    width: 100%;
    align-items: stretch;
  }

  .chart-filters {
    max-width: none;
  }

  .info-badge {
    flex: 1;
    min-width: 0;
  }

  .section-divider {
    margin: var(--spacing-2xl) 0 var(--spacing-lg) 0;
    padding: var(--spacing-lg);
  }

  .section-title {
    font-size: var(--font-size-lg);
  }

  .trend-info {
    grid-template-columns: 1fr;
  }

  .weekday-insights {
    grid-template-columns: 1fr;
  }
}
</style>
