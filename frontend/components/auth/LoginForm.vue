<template>
  <form @submit.prevent="handleSubmit" class="auth-form">
    <h2 class="auth-title">Вход в систему</h2>
    <p class="auth-subtitle">Введите свои данные для входа</p>

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
        label="Пароль"
        placeholder="••••••••"
        :error="errors.password"
        required
        autocomplete="current-password"
      />
    </div>

    <div class="form-group checkbox-group">
      <label class="checkbox-label">
        <input
          v-model="form.remember"
          type="checkbox"
          class="checkbox"
        />
        <span class="checkbox-text">Запомнить меня</span>
      </label>
    </div>

    <div class="form-actions">
      <NuxtLink to="/forgot-password" class="forgot-link">
        Забыли пароль?
      </NuxtLink>
    </div>

    <UiButton
      type="submit"
      variant="primary"
      size="lg"
      full-width
      :loading="isLoading"
    >
      Войти
    </UiButton>

    <div class="auth-footer">
      <p>
        Нет аккаунта?
        <NuxtLink to="/register" class="auth-link">Зарегистрироваться</NuxtLink>
      </p>
    </div>
  </form>
</template>

<script setup lang="ts">
const { login, isLoading } = useAuth()
const route = useRoute()

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const errors = reactive<Record<string, string>>({})
const errorMessage = ref('')

const getRedirectPath = () => {
  const redirect = route.query.redirect as string | undefined
  if (!redirect || redirect === '/login' || redirect === '/register') {
    return '/'
  }
  return redirect
}

const handleSubmit = async () => {
  // Очищаем ошибки
  Object.keys(errors).forEach(key => delete errors[key])
  errorMessage.value = ''

  try {
    await login(form)
    await navigateTo(getRedirectPath(), { replace: true })
  } catch (error: any) {
    if (error?.data?.errors) {
      Object.assign(errors, error.data.errors)
    } else if (error?.data?.message) {
      errorMessage.value = error.data.message
    } else {
      errorMessage.value = 'Произошла ошибка при входе'
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

.checkbox-group {
  margin-bottom: var(--spacing-md);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  cursor: pointer;
  user-select: none;
}

.checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: var(--color-accent);
}

.checkbox-text {
  font-size: var(--font-size-sm);
  color: var(--color-text);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-bottom: var(--spacing-lg);
}

.forgot-link {
  font-size: var(--font-size-sm);
  color: var(--color-accent);
  text-decoration: none;
  transition: color var(--transition-fast);
}

.forgot-link:hover {
  color: var(--color-accent-hover);
  text-decoration: underline;
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
