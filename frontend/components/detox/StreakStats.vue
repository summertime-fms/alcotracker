<template>
  <div class="streak-stats">
    <div class="streak-card streak-card--current">
      <div class="streak-card__icon">🔥</div>
      <div class="streak-card__content">
        <p class="streak-card__label">Текущий стрик</p>
        <p class="streak-card__value">
          Вы абсолютно трезвы: <strong>{{ currentStreakDays }}</strong>
          {{ daysLabel(currentStreakDays) }} подряд
        </p>
      </div>
    </div>

    <div class="streak-card streak-card--record">
      <div class="streak-card__icon">🏆</div>
      <div class="streak-card__content">
        <p class="streak-card__label">Рекорд трезвости</p>
        <p class="streak-card__value">
          Ваш максимальный рекорд: <strong>{{ maxStreakDays }}</strong>
          {{ daysLabel(maxStreakDays) }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  currentStreakDays: number
  maxStreakDays: number
}

defineProps<Props>()

const daysLabel = (count: number) => {
  const mod10 = count % 10
  const mod100 = count % 100

  if (mod100 >= 11 && mod100 <= 14) return 'дней'
  if (mod10 === 1) return 'день'
  if (mod10 >= 2 && mod10 <= 4) return 'дня'
  return 'дней'
}
</script>

<style scoped>
.streak-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--spacing-lg);
}

.streak-card {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-md);
  padding: var(--spacing-xl);
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
}

.streak-card--current {
  border-top: 4px solid #22c55e;
}

.streak-card--record {
  border-top: 4px solid #f59e0b;
}

.streak-card__icon {
  font-size: 2rem;
  line-height: 1;
  flex-shrink: 0;
}

.streak-card__label {
  margin: 0 0 var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
  font-weight: var(--font-weight-medium);
}

.streak-card__value {
  margin: 0;
  font-size: var(--font-size-base);
  line-height: 1.5;
  color: var(--color-text);
}

.streak-card__value strong {
  color: var(--color-accent);
  font-weight: var(--font-weight-bold);
}

@media (max-width: 767px) {
  .streak-stats {
    grid-template-columns: 1fr;
  }
}
</style>
