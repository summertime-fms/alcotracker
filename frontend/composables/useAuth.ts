interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

interface LoginData {
  email: string
  password: string
  remember?: boolean
}

interface ForgotPasswordData {
  email: string
}

interface ResetPasswordData {
  token: string
  email: string
  password: string
  password_confirmation: string
}

export const useAuth = () => {
  const api = useApi()
  const user = useState<User | null>('user', () => null)
  const authChecked = useState<boolean>('authChecked', () => false)
  const isAuthenticated = computed(() => !!user.value)
  const isLoading = useState<boolean>('authLoading', () => false)

  /**
   * Регистрация нового пользователя
   */
  const register = async (data: RegisterData) => {
    try {
      isLoading.value = true
      const response = await api.post<{ user: User; message: string }>('/api/auth/register', data)
      user.value = response.user
      authChecked.value = true
      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Вход пользователя
   */
  const login = async (data: LoginData) => {
    try {
      isLoading.value = true
      const response = await api.post<{ user: User; message: string }>('/api/auth/login', data)
      user.value = response.user
      authChecked.value = true
      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Выход пользователя
   */
  const logout = async () => {
    try {
      isLoading.value = true
      await api.post('/api/auth/logout')
      user.value = null
      authChecked.value = true
      await navigateTo('/login', { replace: true })
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Получение текущего пользователя
   */
  const getUser = async () => {
    try {
      isLoading.value = true
      const response = await api.get<{ user: User }>('/api/auth/user')
      user.value = response.user
      authChecked.value = true
      return response.user
    } catch (error: any) {
      user.value = null
      authChecked.value = true
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Запрос на восстановление пароля
   */
  const forgotPassword = async (data: ForgotPasswordData) => {
    try {
      isLoading.value = true
      const response = await api.post<{ message: string }>('/api/auth/forgot-password', data)
      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Сброс пароля
   */
  const resetPassword = async (data: ResetPasswordData) => {
    try {
      isLoading.value = true
      const response = await api.post<{ message: string }>('/api/auth/reset-password', data)
      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Проверка аутентификации при загрузке приложения
   * Всегда проверяет аутентификацию, даже если пользователь уже в состоянии
   */
  const checkAuth = async () => {
    if (user.value) {
      authChecked.value = true
      return user.value
    }

    try {
      await getUser()
    } catch (error) {
      user.value = null
      authChecked.value = true
      throw error
    }
  }

  return {
    user: readonly(user),
    authChecked: readonly(authChecked),
    isAuthenticated,
    isLoading: readonly(isLoading),
    register,
    login,
    logout,
    getUser,
    forgotPassword,
    resetPassword,
    checkAuth,
  }
}
