<template>
  <div class="error-page">
    <div class="error-card">
      <p class="error-code">{{ statusCode }}</p>
      <h1 class="error-title">{{ title }}</h1>
      <p class="error-message">{{ message }}</p>

      <div class="error-actions">
        <NuxtLink to="/" class="error-btn error-btn--primary">
          На главную
        </NuxtLink>
        <NuxtLink to="/login" class="error-btn error-btn--outline">
          Войти
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps<{
  error: NuxtError
}>()

const statusCode = computed(() => props.error.statusCode || 500)

const title = computed(() => {
  if (statusCode.value === 404) {
    return 'Страница не найдена'
  }

  return 'Что-то пошло не так'
})

const message = computed(() => {
  if (statusCode.value === 404) {
    return 'Запрашиваемая страница не существует или была перемещена.'
  }

  return props.error.message || 'Произошла непредвиденная ошибка. Попробуйте вернуться на главную.'
})

useHead({
  title: `${statusCode.value} — ${title.value}`,
})
</script>

<style scoped>
.error-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-md);
  background: var(--gradient-auth-bg);
}

.error-card {
  width: 100%;
  max-width: 480px;
  padding: var(--spacing-2xl);
  text-align: center;
  background: var(--color-secondary);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-lg);
  animation: slideUp var(--transition-slow) ease-out;
}

.error-code {
  margin-bottom: var(--spacing-sm);
  font-family: var(--font-family-display);
  font-size: var(--font-size-4xl);
  font-weight: var(--font-weight-bold);
  line-height: var(--line-height-tight);
  color: var(--color-accent);
}

.error-title {
  margin-bottom: var(--spacing-md);
  font-family: var(--font-family-display);
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-semibold);
}

.error-message {
  margin-bottom: var(--spacing-xl);
  color: var(--color-text-muted);
  line-height: var(--line-height-relaxed);
}

.error-actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-sm);
  justify-content: center;
}

.error-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 140px;
  padding: var(--spacing-sm) var(--spacing-lg);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  border-radius: var(--radius-md);
  transition: background-color var(--transition-fast), color var(--transition-fast), border-color var(--transition-fast);
}

.error-btn--primary {
  color: #fff;
  background: var(--gradient-hero);
}

.error-btn--primary:hover {
  color: #fff;
  opacity: 0.92;
}

.error-btn--outline {
  color: var(--color-text);
  background: var(--color-primary);
  border: 1px solid var(--color-border);
}

.error-btn--outline:hover {
  color: var(--color-accent);
  border-color: var(--color-accent);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
