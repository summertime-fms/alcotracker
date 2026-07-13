<template>
  <div class="add-entry-form">
    <h3>Добавить запись</h3>

    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <UiSelect
          v-model="form.alcohol_type"
          :options="alcoholTypes"
          label="Тип алкоголя"
          placeholder="Выберите тип"
          required
        />
      </div>

      <div class="form-group">
        <label for="amount" class="label">
          Количество (мл) <span class="required">*</span>
        </label>
        <input
          id="amount"
          v-model.number="form.amount_ml"
          type="number"
          class="input"
          placeholder="500"
          min="1"
          max="5000"
          required
        />
      </div>

      <div class="form-group">
        <label for="date" class="label">
          Дата <span class="required">*</span>
        </label>
        <input
          id="date"
          v-model="form.drink_date"
          type="date"
          class="input"
          :max="today"
          required
        />
      </div>

      <div class="form-group">
        <label for="comment" class="label">
          Комментарий
        </label>
        <textarea
          id="comment"
          v-model="form.comment"
          class="textarea"
          placeholder="Например: С друзьями на вечеринке"
          maxlength="500"
          rows="3"
        ></textarea>
        <div class="hint">{{ form.comment?.length || 0 }}/500</div>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div class="form-actions">
        <UiButton
          type="submit"
          :loading="isLoading"
          full-width
        >
          Добавить запись
        </UiButton>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
interface Props {
  alcoholTypes: Record<string, string>
  selectedDate?: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  success: []
}>()

const { createEntry, isLoading } = useAlcoholEntries()

const today = computed(() => {
  const date = new Date()
  return date.toISOString().split('T')[0]
})

const form = reactive({
  alcohol_type: '',
  amount_ml: null as number | null,
  drink_date: props.selectedDate || today.value,
  comment: '',
})

const error = ref('')

// Обновляем дату в форме при изменении selectedDate
watch(() => props.selectedDate, (newDate) => {
  if (newDate) {
    form.drink_date = newDate
  }
})

const handleSubmit = async () => {
  if (!form.alcohol_type || !form.amount_ml || !form.drink_date) {
    error.value = 'Пожалуйста, заполните все обязательные поля'
    return
  }

  try {
    error.value = ''
    await createEntry({
      alcohol_type: form.alcohol_type,
      amount_ml: form.amount_ml,
      drink_date: form.drink_date,
      comment: form.comment || undefined,
    })

    // Очищаем форму
    form.alcohol_type = ''
    form.amount_ml = null
    form.comment = ''
    // Дату оставляем текущей

    emit('success')
  } catch (err: any) {
    error.value = err?.data?.message || 'Произошла ошибка при добавлении записи'
  }
}
</script>

<style scoped>
.add-entry-form {
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

.add-entry-form h3 {
  margin: 0 0 var(--spacing-lg) 0;
  font-family: var(--font-family-display);
  color: var(--color-text);
  font-weight: var(--font-weight-semibold);
}

.form-group {
  margin-bottom: var(--spacing-lg);
}

.label {
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

.input,
.textarea {
  width: 100%;
  max-width: 100%;
  padding: var(--spacing-md);
  font-family: var(--font-family-base);
  font-size: var(--font-size-base);
  color: var(--color-text);
  background-color: #FFFFFF;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  outline: none;
  box-sizing: border-box;
}

.input::placeholder,
.textarea::placeholder {
  color: var(--color-text-muted);
}

.input:hover,
.textarea:hover {
  border-color: var(--color-accent);
}

.input:focus,
.textarea:focus {
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.textarea {
  resize: vertical;
  min-height: 80px;
}

.hint {
  margin-top: var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
  text-align: right;
}

.error-message {
  padding: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
  background-color: rgba(231, 76, 60, 0.1);
  border: 1px solid var(--color-error);
  border-radius: var(--radius-md);
  color: var(--color-error);
  font-size: var(--font-size-sm);
}

.form-actions {
  margin-top: var(--spacing-xl);
}

/* Мобильные устройства (320px - 767px) */
@media (max-width: 767px) {
  .add-entry-form {
    padding: var(--spacing-md);
  }

  .add-entry-form h3 {
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-md);
  }

  .form-group {
    margin-bottom: var(--spacing-md);
  }

  .input,
  .textarea {
    padding: var(--spacing-sm) var(--spacing-md);
    font-size: var(--font-size-sm);
  }

  .textarea {
    min-height: 60px;
  }

  .form-actions {
    margin-top: var(--spacing-md);
  }
}
</style>
