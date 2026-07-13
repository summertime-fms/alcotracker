<template>
  <div class="statistics">
    <div class="stats-header">
      <h3>Статистика за неделю</h3>
      <div class="period-info">
        {{ formatPeriod(statistics?.period) }}
      </div>
    </div>

    <div v-if="isLoading" class="loading">
      <div class="spinner"></div>
    </div>

    <div v-else-if="statistics" class="stats-content">
      <div class="stat-card main-stat">
        <div class="stat-icon">📊</div>
        <div class="stat-info">
          <div class="stat-label">Всего выпито</div>
          <div class="stat-value">{{ statistics.total_ml }} мл</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div class="stat-info">
          <div class="stat-label">Количество записей</div>
          <div class="stat-value">{{ statistics.entries_count }}</div>
        </div>
      </div>

      <div v-if="Object.keys(statistics.by_type).length > 0" class="by-type-section">
        <h4>По типам алкоголя:</h4>
        <div class="type-list">
          <div
            v-for="(amount, type) in statistics.by_type"
            :key="type"
            class="type-item"
          >
            <div class="type-label">{{ getTypeLabel(type) }}</div>
            <div class="type-amount">{{ amount }} мл</div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="empty-stats">
      <p>Нет данных за выбранный период</p>
    </div>
  </div>
</template>

<script setup lang="ts">
const { getStatistics, alcoholTypes } = useAlcoholEntries()

const statistics = ref<any>(null)
const isLoading = ref(false)

const loadStatistics = async () => {
  try {
    isLoading.value = true
    statistics.value = await getStatistics()
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error)
  } finally {
    isLoading.value = false
  }
}

const getTypeLabel = (type: string) => {
  return alcoholTypes.value[type] || type
}

const formatPeriod = (period?: { start_date: string; end_date: string }) => {
  if (!period) return ''

  const start = new Date(period.start_date)
  const end = new Date(period.end_date)

  return `${start.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })} - ${end.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })}`
}

onMounted(() => {
  loadStatistics()
})

defineExpose({
  loadStatistics,
})
</script>

<style scoped>
.statistics {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  padding: var(--spacing-xl);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  flex-shrink: 0;
}

.stats-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--spacing-lg);
  padding-bottom: var(--spacing-md);
  border-bottom: 1px solid var(--color-border);
}

.stats-header h3 {
  margin: 0;
  font-family: var(--font-family-display);
  color: var(--color-text);
  font-weight: var(--font-weight-semibold);
}

.period-info {
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
}

.loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-2xl);
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

.stats-content {
  display: grid;
  gap: var(--spacing-lg);
}

.stat-card {
  display: flex;
  align-items: center;
  gap: var(--spacing-lg);
  padding: var(--spacing-lg);
  background: #FFFFFF;
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
}

.main-stat {
  background: var(--gradient-hero);
  border: none;
  color: white;
  box-shadow: 0 4px 16px rgba(99, 102, 241, 0.25);
}

.stat-icon {
  font-size: 2rem;
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
  margin-bottom: var(--spacing-xs);
}

.main-stat .stat-label {
  color: rgba(255, 255, 255, 0.9);
}

.stat-value {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--color-text);
}

.main-stat .stat-value {
  color: white;
  font-size: var(--font-size-2xl);
}

.by-type-section {
  margin-top: var(--spacing-md);
}

.by-type-section h4 {
  margin: 0 0 var(--spacing-md) 0;
  font-size: var(--font-size-base);
  color: var(--color-text);
}

.type-list {
  display: grid;
  gap: var(--spacing-sm);
}

.type-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-md);
  background: var(--color-accent-light);
  border-radius: var(--radius-md);
  border: 1px solid rgba(99, 102, 241, 0.1);
}

.type-label {
  font-size: var(--font-size-sm);
  color: var(--color-text);
  font-weight: var(--font-weight-medium);
}

.type-amount {
  font-size: var(--font-size-sm);
  color: var(--color-accent);
  font-weight: var(--font-weight-semibold);
}

.empty-stats {
  text-align: center;
  padding: var(--spacing-2xl);
  color: var(--color-text-muted);
}

/* Мобильные устройства (320px - 767px) */
@media (max-width: 767px) {
  .statistics {
    padding: var(--spacing-md);
  }

  .stats-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-md);
  }

  .stats-header h3 {
    font-size: var(--font-size-lg);
  }

  .period-info {
    font-size: var(--font-size-xs);
  }

  .stat-card {
    padding: var(--spacing-md);
    gap: var(--spacing-md);
  }

  .stat-icon {
    font-size: 1.5rem;
  }

  .stat-label {
    font-size: var(--font-size-xs);
  }

  .stat-value {
    font-size: var(--font-size-lg);
  }

  .main-stat .stat-value {
    font-size: var(--font-size-xl);
  }

  .by-type-section h4 {
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-sm);
  }

  .type-item {
    padding: var(--spacing-sm);
  }

  .type-label,
  .type-amount {
    font-size: var(--font-size-xs);
  }

  .empty-stats {
    padding: var(--spacing-lg);
  }
}
</style>
