<template>
  <div v-if="pattern" class="habit-analysis card">
    <div class="habit-analysis__header">
      <span class="habit-analysis__icon" aria-hidden="true">📊</span>
      <div>
        <h3 class="habit-analysis__title">Анализ привычек</h3>
        <p class="habit-analysis__label">{{ pattern.label }}</p>
      </div>
    </div>

    <div class="habit-analysis__bars">
      <div class="habit-bar">
        <div class="habit-bar__meta">
          <span class="habit-bar__name">Будние дни (Пн–Чт)</span>
          <span class="habit-bar__value">{{ pattern.weekdays_percent }}%</span>
        </div>
        <div class="habit-bar__track" role="progressbar" :aria-valuenow="pattern.weekdays_percent" aria-valuemin="0" aria-valuemax="100">
          <div class="habit-bar__fill habit-bar__fill--weekdays" :style="{ width: `${pattern.weekdays_percent}%` }" />
        </div>
      </div>

      <div class="habit-bar">
        <div class="habit-bar__meta">
          <span class="habit-bar__name">Выходные дни (Пт–Вс)</span>
          <span class="habit-bar__value">{{ pattern.weekends_percent }}%</span>
        </div>
        <div class="habit-bar__track" role="progressbar" :aria-valuenow="pattern.weekends_percent" aria-valuemin="0" aria-valuemax="100">
          <div class="habit-bar__fill habit-bar__fill--weekends" :style="{ width: `${pattern.weekends_percent}%` }" />
        </div>
      </div>
    </div>

    <p class="habit-analysis__insight">
      <span class="habit-analysis__insight-icon" aria-hidden="true">💡</span>
      {{ pattern.insight_text }}
    </p>

    <p v-if="pattern.anomaly" class="habit-analysis__anomaly">
      <span aria-hidden="true">⚠️</span>
      <strong>Аномалия:</strong> {{ pattern.anomaly.text }}
    </p>
  </div>
</template>

<script setup lang="ts">
interface PatternAnomaly {
  text: string
}

interface PatternAnalysis {
  label: string
  weekdays_percent: number
  weekends_percent: number
  insight_text: string
  anomaly?: PatternAnomaly | null
}

defineProps<{
  pattern: PatternAnalysis | null
}>()
</script>

<style scoped>
.habit-analysis {
  padding: var(--spacing-xl);
}

.habit-analysis__header {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-xl);
}

.habit-analysis__icon {
  font-size: 2rem;
  line-height: 1;
}

.habit-analysis__title {
  margin: 0 0 var(--spacing-xs);
  font-family: var(--font-family-display);
  font-size: var(--font-size-xl);
  color: var(--color-text);
}

.habit-analysis__label {
  margin: 0;
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--color-accent);
}

.habit-analysis__bars {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
  margin-bottom: var(--spacing-xl);
}

.habit-bar__meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-sm);
}

.habit-bar__name {
  font-size: var(--font-size-sm);
  color: var(--color-text-light);
  font-weight: var(--font-weight-medium);
}

.habit-bar__value {
  font-size: var(--font-size-sm);
  color: var(--color-text);
  font-weight: var(--font-weight-bold);
}

.habit-bar__track {
  height: 10px;
  background: var(--color-border-light);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.habit-bar__fill {
  height: 100%;
  border-radius: var(--radius-full);
  transition: width var(--transition-slow);
}

.habit-bar__fill--weekdays {
  background: var(--gradient-primary);
}

.habit-bar__fill--weekends {
  background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%);
}

.habit-analysis__insight {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-sm);
  margin: 0;
  padding: var(--spacing-md);
  font-size: var(--font-size-sm);
  line-height: var(--line-height-relaxed);
  color: var(--color-text-light);
  background: var(--color-accent-light);
  border: 1px solid rgba(99, 102, 241, 0.12);
  border-radius: var(--radius-md);
}

.habit-analysis__insight-icon {
  flex-shrink: 0;
  font-size: var(--font-size-lg);
  line-height: 1.2;
}

.habit-analysis__anomaly {
  margin: var(--spacing-md) 0 0;
  padding: var(--spacing-md);
  font-size: var(--font-size-sm);
  line-height: var(--line-height-relaxed);
  color: #92400E;
  background: #FFFBEB;
  border: 1px solid #FDE68A;
  border-radius: var(--radius-md);
}

.habit-analysis__anomaly strong {
  font-weight: var(--font-weight-semibold);
}
</style>
