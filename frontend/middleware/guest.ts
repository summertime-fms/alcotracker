/**
 * Middleware для гостевых страниц
 * Разрешает доступ только неаутентифицированным пользователям
 */
export default defineNuxtRouteMiddleware(async () => {
  if (!import.meta.client) {
    return
  }

  const { isAuthenticated, checkAuth, authChecked } = useAuth()

  if (authChecked.value && isAuthenticated.value) {
    return navigateTo('/')
  }

  if (!authChecked.value) {
    try {
      await checkAuth()
    } catch {
      // Пользователь не аутентифицирован
    }
  }

  if (isAuthenticated.value) {
    return navigateTo('/')
  }
})
