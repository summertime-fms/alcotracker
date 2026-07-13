<template>
  <form @submit.prevent="handleSubmit" class="auth-form">
    <h2 class="auth-title">Новый пароль</h2>
    <p class="auth-subtitle">Введите новый пароль для вашего аккаунта</p>

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

    <div class="form-group">
      <UiInput
        v-model="form.password"
        type="password"
        label="Новый пароль"
        placeholder="••••••••"
        :error="errors.password"
        required
        autocomplete="new-password"
      />
    </div>

    <div class="form-group">
      <UiInput
        v-model="form.password_confirmation"
        type="password"
        label="Подтвердите пароль"
        placeholder="••••••••"
        :error="errors.password_confirmation"
        required
        autocomplete="new-password"
      />
    </div>

    <UiButton
      type="submit"
      variant="primary"
      size="lg"
      full-width
      :loading="isLoading"
    >
      Сбросить пароль
    </UiButton>

    <div class="auth-footer">
      <p>
        <NuxtLink to="/login" class="auth-link">Вернуться к входу</NuxtLink>
      </p>
    </div>
  </form>
</template>

<script setup lang="ts">
const { resetPassword, isLoading } = useAuth()
const router = useRouter()
const route = useRoute()

const form = reactive({
  token: route.query.token as string || '',
  email: route.query.email as string || '',
  password: '',
  password_confirmation: '',
})

const errors = reactive<Record<string, string>>({})
const errorMessage = ref('')

const handleSubmit = async () => {
  // Очищаем ошибки
  Object.keys(errors).forEach(key => delete errors[key])
  errorMessage.value = ''

  if (!form.token) {
    errorMessage.value = 'Неверная ссылка для сброса пароля'
    return
  }

  try {
    await resetPassword(form)
    await router.push('/login')
  } catch (error: any) {
    if (error?.data?.errors) {
      Object.assign(errors, error.data.errors)
    } else if (error?.data?.message) {
      errorMessage.value = error.data.message
    } else {
      errorMessage.value = 'Произошла ошибка при сбросе пароля'
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
</style>



