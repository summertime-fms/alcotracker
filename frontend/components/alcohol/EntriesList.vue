<template>
  <div class="entries-list">
    <div class="entries-header">
      <h3>Записи за {{ formatDate(selectedDate) }}</h3>
      <div class="total-info" v-if="totalMl > 0">
        Всего: <strong>{{ totalMl }} мл</strong>
      </div>
    </div>

    <div v-if="isLoading" class="loading">
      <div class="spinner"></div>
      <p>Загрузка...</p>
    </div>

    <div v-else-if="entries.length === 0" class="empty-state">
      <UiAppLogo tag="div" size="md" :show-text="false" class="empty-icon" />
      <p>Нет записей за выбранную дату</p>
      <p class="empty-hint">Добавьте первую запись, используя форму выше</p>
    </div>

    <div v-else class="entries-grid">
      <div
        v-for="entry in entries"
        :key="entry.id"
        class="entry-card"
      >
        <div class="entry-header">
          <div class="entry-type">{{ entry.alcohol_type_label }}</div>
          <button
            @click="handleDelete(entry.id)"
            class="delete-btn"
            title="Удалить запись"
          >
            ✕
          </button>
        </div>

        <div class="entry-amount">
          {{ entry.amount_ml }} мл
        </div>

        <div v-if="entry.comment" class="entry-comment">
          💬 {{ entry.comment }}
        </div>

        <div class="entry-time">
          Добавлено: {{ formatTime(entry.created_at) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  selectedDate: string
}

const props = defineProps<Props>()

const { entries, isLoading, deleteEntry } = useAlcoholEntries()

const totalMl = computed(() => {
  return entries.value.reduce((sum, entry) => sum + entry.amount_ml, 0)
})

const handleDelete = async (id: number) => {
  if (!confirm('Вы уверены, что хотите удалить эту запись?')) {
    return
  }

  try {
    await deleteEntry(id)
  } catch (error) {
    alert('Ошибка при удалении записи')
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)

  const dateOnly = date.toISOString().split('T')[0]
  const todayOnly = today.toISOString().split('T')[0]
  const yesterdayOnly = yesterday.toISOString().split('T')[0]

  if (dateOnly === todayOnly) {
    return 'сегодня'
  } else if (dateOnly === yesterdayOnly) {
    return 'вчера'
  }

  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.entries-list {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  padding: var(--spacing-xl);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  min-height: 400px;
  display: flex;
  flex-direction: column;
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  flex-shrink: 0;
  position: relative;
}

.entries-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--spacing-lg);
  padding-bottom: var(--spacing-md);
  border-bottom: 1px solid var(--color-border);
}

.entries-header h3 {
  margin: 0;
  font-family: var(--font-family-display);
  color: var(--color-text);
  font-weight: var(--font-weight-semibold);
}

.total-info {
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
}

.total-info strong {
  color: var(--color-accent);
  font-size: var(--font-size-base);
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-2xl);
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

.empty-state {
  text-align: center;
  padding: var(--spacing-2xl);
}

.empty-icon {
  margin-bottom: var(--spacing-md);
}

.empty-icon :deep(.app-logo__image) {
  width: 64px;
  height: 64px;
}

.empty-state p {
  color: var(--color-text-muted);
  margin: var(--spacing-sm) 0;
}

.empty-hint {
  font-size: var(--font-size-sm);
}

.entries-grid {
  display: grid;
  gap: var(--spacing-md);
}

.entry-card {
  padding: var(--spacing-lg);
  background: #FFFFFF;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  transition: all var(--transition-fast);
}

.entry-card:hover {
  box-shadow: var(--shadow-md);
  border-color: rgba(99, 102, 241, 0.3);
  transform: translateY(-1px);
}

.entry-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--spacing-sm);
}

.entry-type {
  font-weight: var(--font-weight-semibold);
  color: var(--color-text);
  font-size: var(--font-size-lg);
}

.delete-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  font-size: var(--font-size-lg);
  padding: var(--spacing-xs);
  line-height: 1;
  transition: color var(--transition-fast);
}

.delete-btn:hover {
  color: var(--color-error);
}

.entry-amount {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--color-accent);
  margin-bottom: var(--spacing-sm);
}

.entry-comment {
  padding: var(--spacing-sm);
  background: var(--color-accent-light);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-sm);
  color: var(--color-text);
  margin-bottom: var(--spacing-sm);
}

.entry-time {
  font-size: var(--font-size-xs);
  color: var(--color-text-muted);
}

/* Мобильные устройства (320px - 767px) */
@media (max-width: 767px) {
  .entries-list {
    padding: var(--spacing-md);
  }

  .entries-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
  }

  .entries-header h3 {
    font-size: var(--font-size-lg);
  }

  .total-info {
    font-size: var(--font-size-xs);
  }

  .entry-card {
    padding: var(--spacing-md);
  }

  .entry-type {
    font-size: var(--font-size-base);
  }

  .entry-amount {
    font-size: var(--font-size-lg);
  }

  .entry-comment {
    padding: var(--spacing-xs) var(--spacing-sm);
    font-size: var(--font-size-xs);
  }
}
</style>
