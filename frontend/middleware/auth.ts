const AUTH_PAGES = ['/login', '/register', '/forgot-password', '/reset-password']

const getLoginRedirectPath = (to: { fullPath?: string; path?: string }) => {
  const currentPath = to.path || '/'

  if (AUTH_PAGES.includes(currentPath)) {
    return undefined
  }

  return to.fullPath || currentPath
}

/**
 * Middleware для защиты страниц
 * Разрешает доступ только аутентифицированным пользователям
 */
export default defineNuxtRouteMiddleware(async (to) => {
  if (!import.meta.client) {
    return
  }

  const { isAuthenticated, checkAuth, authChecked } = useAuth()

  if (authChecked.value && !isAuthenticated.value) {
    return navigateTo({
      path: '/login',
      query: getLoginRedirectPath(to) ? { redirect: getLoginRedirectPath(to) } : undefined,
      replace: true,
    })
  }

  if (!authChecked.value) {
    try {
      await checkAuth()
    } catch {
      // Пользователь не аутентифицирован
    }
  }

  if (!isAuthenticated.value) {
    return navigateTo({
      path: '/login',
      query: getLoginRedirectPath(to) ? { redirect: getLoginRedirectPath(to) } : undefined,
      replace: true,
    })
  }
})
