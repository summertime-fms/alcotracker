<template>
  <form @submit.prevent="handleSubmit" class="auth-form">
    <h2 class="auth-title">Восстановление пароля</h2>
    <p class="auth-subtitle">Введите ваш email для получения ссылки сброса пароля</p>

    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="alert alert-error">
      {{ errorMessage }}
    </div>

    <div class="form-group">
      <UiInput
        v-model="form.email"
        type="email"
        label="Email"
        placeholder="your@email.com"
        :error="errors.email"
        required
        autocomplete="email"
      />
    </div>

    <UiButton
      type="submit"
      variant="primary"
      size="lg"
      full-width
      :loading="isLoading"
    >
      Отправить ссылку
    </UiButton>

    <div class="auth-footer">
      <p>
        <NuxtLink to="/login" class="auth-link">Вернуться к входу</NuxtLink>
      </p>
    </div>
  </form>
</template>

<script setup lang="ts">
const { forgotPassword, isLoading } = useAuth()

const form = reactive({
  email: '',
})

const errors = reactive<Record<string, string>>({})
const errorMessage = ref('')
const successMessage = ref('')

const handleSubmit = async () => {
  // Очищаем сообщения
  Object.keys(errors).forEach(key => delete errors[key])
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await forgotPassword(form)
    successMessage.value = response.message || 'Ссылка для восстановления пароля отправлена на ваш email'
    form.email = ''
  } catch (error: any) {
    if (error?.data?.errors) {
      Object.assign(errors, error.data.errors)
    } else if (error?.data?.message) {
      errorMessage.value = error.data.message
    } else {
      errorMessage.value = 'Произошла ошибка при отправке ссылки'
    }
  }
}
</script>

<style scoped>
.auth-form {
  width: 100%;
  max-width: 400px;
}

.auth-title {
  font-family: var(--font-family-display);
  font-size: var(--font-size-3xl);
  font-weight: var(--font-weight-bold);
  color: var(--color-text);
  margin-bottom: var(--spacing-sm);
  text-align: center;
  letter-spacing: -0.03em;
}

.auth-subtitle {
  font-size: var(--font-size-base);
  color: var(--color-text-muted);
  margin-bottom: var(--spacing-xl);
  text-align: center;
}

.form-group {
  margin-bottom: var(--spacing-lg);
}

.auth-footer {
  margin-top: var(--spacing-xl);
  text-align: center;
}

.auth-footer p {
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
  margin: 0;
}

.auth-link {
  color: var(--color-accent);
  font-weight: var(--font-weight-medium);
  text-decoration: none;
  transition: color var(--transition-fast);
}

.auth-link:hover {
  color: var(--color-accent-hover);
  text-decoration: underline;
}

.alert {
  padding: var(--spacing-md);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-lg);
  font-size: var(--font-size-sm);
}

.alert-error {
  background-color: var(--color-error-light);
  color: var(--color-error);
  border: 1px solid var(--color-error);
}

.alert-success {
  background-color: var(--color-success-light);
  color: var(--color-success);
  border: 1px solid var(--color-success);
}
</style>



