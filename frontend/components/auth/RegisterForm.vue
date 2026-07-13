<template>
  <form @submit.prevent="handleSubmit" class="auth-form">
    <h2 class="auth-title">Регистрация</h2>
    <p class="auth-subtitle">Создайте новый аккаунт</p>

    <div v-if="errorMessage" class="alert alert-error">
      {{ errorMessage }}
    </div>

    <div class="form-group">
      <UiInput
        v-model="form.name"
        type="text"
        label="Имя"
        placeholder="Ваше имя"
        :error="errors.name"
        required
        autocomplete="name"
      />
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
      Зарегистрироваться
    </UiButton>

    <div class="auth-footer">
      <p>
        Уже есть аккаунт?
        <NuxtLink to="/login" class="auth-link">Войти</NuxtLink>
      </p>
    </div>
  </form>
</template>

<script setup lang="ts">
const { register, isLoading } = useAuth()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = reactive<Record<string, string>>({})
const errorMessage = ref('')

const handleSubmit = async () => {
  // Очищаем ошибки
  Object.keys(errors).forEach(key => delete errors[key])
  errorMessage.value = ''

  try {
    await register(form)
    await router.push('/')
  } catch (error: any) {
    if (error?.data?.errors) {
      Object.assign(errors, error.data.errors)
    } else if (error?.data?.message) {
      errorMessage.value = error.data.message
    } else {
      errorMessage.value = 'Произошла ошибка при регистрации'
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



