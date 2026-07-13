<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <span v-if="loading" class="spinner"></span>
    <slot v-else />
  </button>
</template>

<script setup lang="ts">
interface Props {
  type?: 'button' | 'submit' | 'reset'
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
  loading?: boolean
  fullWidth?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'button',
  variant: 'primary',
  size: 'md',
  disabled: false,
  loading: false,
  fullWidth: false,
})

const emit = defineEmits<{
  click: [event: MouseEvent]
}>()

const buttonClasses = computed(() => {
  return [
    'btn',
    `btn-${props.variant}`,
    `btn-${props.size}`,
    { 'btn-full': props.fullWidth },
    { 'btn-loading': props.loading },
  ]
})

const handleClick = (event: MouseEvent) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-family-base);
  font-weight: var(--font-weight-medium);
  border: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-fast);
  text-align: center;
  white-space: nowrap;
  user-select: none;
}

.btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Размеры */
.btn-sm {
  padding: var(--spacing-sm) var(--spacing-md);
  font-size: var(--font-size-sm);
  min-height: 32px;
}

.btn-md {
  padding: var(--spacing-md) var(--spacing-lg);
  font-size: var(--font-size-base);
  min-height: 40px;
}

.btn-lg {
  padding: var(--spacing-lg) var(--spacing-xl);
  font-size: var(--font-size-lg);
  min-height: 48px;
}

.btn-full {
  width: 100%;
}

/* Варианты */
.btn-primary {
  background: var(--gradient-primary);
  color: #FFFFFF;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
}

.btn-primary:hover:not(:disabled) {
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
  transform: translateY(-1px);
  filter: brightness(1.05);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
}

.btn-secondary {
  background-color: var(--color-accent-light);
  color: var(--color-accent);
}

.btn-secondary:hover:not(:disabled) {
  background-color: #E0E7FF;
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.btn-outline {
  background-color: transparent;
  border: 1.5px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover:not(:disabled) {
  background-color: var(--color-accent-light);
  border-color: var(--color-accent);
  color: var(--color-accent);
}

.btn-ghost {
  background-color: transparent;
  color: var(--color-accent);
}

.btn-ghost:hover:not(:disabled) {
  background-color: var(--color-accent-light);
}

.btn-danger {
  background-color: var(--color-error);
  color: var(--color-secondary);
}

.btn-danger:hover:not(:disabled) {
  background-color: #c0392b;
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

/* Spinner для загрузки */
.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.btn-loading {
  position: relative;
}
</style>



