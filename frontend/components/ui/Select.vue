<template>
  <div class="select-wrapper">
    <label v-if="label" :for="selectId" class="select-label">
      {{ label }}
      <span v-if="required" class="required">*</span>
    </label>

    <div class="select-container" :class="{ 'select-error': error, 'select-disabled': disabled, 'select-focused': isFocused }">
      <select
        :id="selectId"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        class="select"
        @change="handleChange"
        @focus="handleFocus"
        @blur="handleBlur"
      >
        <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
        <option
          v-for="option in normalizedOptions"
          :key="getOptionValue(option)"
          :value="getOptionValue(option)"
        >
          {{ getOptionLabel(option) }}
        </option>
      </select>
      <div class="select-icon">
        <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    </div>

    <div v-if="error" class="select-error-message">
      {{ error }}
    </div>

    <div v-else-if="hint" class="select-hint">
      {{ hint }}
    </div>
  </div>
</template>

<script setup lang="ts">
interface Option {
  value: string | number
  label: string
}

interface Props {
  modelValue?: string | number
  options: Array<Option | string> | Record<string, string>
  label?: string
  placeholder?: string
  error?: string
  hint?: string
  disabled?: boolean
  required?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  required: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
  change: [value: string | number]
  blur: [event: FocusEvent]
  focus: [event: FocusEvent]
}>()

const selectId = computed(() => `select-${Math.random().toString(36).substr(2, 9)}`)
const isFocused = ref(false)

// Нормализуем options в массив объектов
const normalizedOptions = computed(() => {
  if (Array.isArray(props.options)) {
    return props.options.map(option => {
      if (typeof option === 'string') {
        return { value: option, label: option }
      }
      return option
    })
  }

  // Если это объект (Record<string, string>)
  return Object.entries(props.options).map(([value, label]) => ({
    value,
    label,
  }))
})

const getOptionValue = (option: Option | string): string | number => {
  if (typeof option === 'string') return option
  return option.value
}

const getOptionLabel = (option: Option | string): string => {
  if (typeof option === 'string') return option
  return option.label
}

const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement
  const value = target.value
  emit('update:modelValue', value)
  emit('change', value)
}

const handleFocus = (event: FocusEvent) => {
  isFocused.value = true
  emit('focus', event)
}

const handleBlur = (event: FocusEvent) => {
  isFocused.value = false
  emit('blur', event)
}
</script>

<style scoped>
.select-wrapper {
  width: 100%;
}

.select-label {
  display: block;
  margin-bottom: var(--spacing-sm);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--color-text);
}

.required {
  color: var(--color-error);
  margin-left: 2px;
}

.select-container {
  position: relative;
  width: 100%;
}

.select {
  width: 100%;
  padding: var(--spacing-md) var(--spacing-xl) var(--spacing-md) var(--spacing-md);
  font-family: var(--font-family-base);
  font-size: var(--font-size-base);
  color: var(--color-text);
  background-color: #FFFFFF;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  outline: none;
  appearance: none;
  cursor: pointer;
  padding-right: calc(var(--spacing-xl) + 12px + var(--spacing-sm));
}

.select:disabled {
  cursor: not-allowed;
  opacity: 0.6;
  background-color: var(--color-surface);
}

.select:hover:not(:disabled) {
  border-color: var(--color-accent);
}

.select:focus {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.select-container.select-focused .select {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.select-icon {
  position: absolute;
  right: var(--spacing-md);
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: var(--color-text-muted);
  transition: transform var(--transition-fast);
}

.select-container.select-focused .select-icon {
  transform: translateY(-50%) rotate(180deg);
}

.select-container.select-error .select {
  border-color: var(--color-error);
}

.select-container.select-error .select:focus {
  border-color: var(--color-error);
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.select-disabled {
  opacity: 0.6;
}

.select-error-message {
  display: block;
  margin-top: var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--color-error);
}

.select-hint {
  display: block;
  margin-top: var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
}

/* Стили для option */
.select option {
  padding: var(--spacing-md);
  background-color: var(--color-secondary);
  color: var(--color-text);
}

.select option:disabled {
  color: var(--color-text-muted);
}

/* Мобильные устройства */
@media (max-width: 767px) {
  .select {
    padding: var(--spacing-sm) var(--spacing-lg) var(--spacing-sm) var(--spacing-sm);
    font-size: var(--font-size-sm);
    padding-right: calc(var(--spacing-lg) + 12px + var(--spacing-sm));
  }

  .select-label {
    font-size: var(--font-size-xs);
  }
}
</style>
