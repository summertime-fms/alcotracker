<template>
  <div class="input-wrapper">
    <label v-if="label" :for="inputId" class="input-label">
      {{ label }}
      <span v-if="required" class="required">*</span>
    </label>

    <div class="input-container">
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :autocomplete="autocomplete"
        :class="inputClasses"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />
    </div>

    <div v-if="displayError" class="input-error">
      {{ displayError }}
    </div>

    <div v-else-if="hint" class="input-hint">
      {{ hint }}
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  modelValue?: string | number
  type?: string
  label?: string
  placeholder?: string
  error?: string | string[]
  hint?: string
  disabled?: boolean
  required?: boolean
  autocomplete?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  disabled: false,
  required: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  blur: [event: FocusEvent]
  focus: [event: FocusEvent]
}>()

const inputId = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`)
const isFocused = ref(false)

const displayError = computed(() => {
  if (!props.error) return ''
  return Array.isArray(props.error) ? props.error[0] : props.error
})

const inputClasses = computed(() => {
  return [
    'input',
    {
      'input-error-state': displayError.value,
      'input-disabled': props.disabled,
      'input-focused': isFocused.value,
    },
  ]
})

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}

const handleBlur = (event: FocusEvent) => {
  isFocused.value = false
  emit('blur', event)
}

const handleFocus = (event: FocusEvent) => {
  isFocused.value = true
  emit('focus', event)
}
</script>

<style scoped>
.input-wrapper {
  width: 100%;
}

.input-label {
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

.input-container {
  position: relative;
  width: 100%;
}

.input {
  width: 100%;
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

.input::placeholder {
  color: var(--color-text-muted);
}

.input:hover:not(:disabled) {
  border-color: var(--color-accent);
}

.input:focus {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.input-focused {
  border-color: var(--color-accent);
}

.input-error-state {
  border-color: var(--color-error);
}

.input-error-state:focus {
  border-color: var(--color-error);
  box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.input-disabled {
  background-color: var(--color-surface);
  cursor: not-allowed;
  opacity: 0.6;
}

.input-error {
  display: block;
  margin-top: var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--color-error);
}

.input-hint {
  display: block;
  margin-top: var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
}
</style>



