export const useApi = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase // http://localhost:8080
  const apiUrl = config.public.apiUrl   // http://localhost:8080/api

  /**
   * Получение CSRF токена из cookie
   */
  const getCsrfToken = (): string | null => {
    if (import.meta.server) return null

    const cookieValue = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1]

    return cookieValue ? decodeURIComponent(cookieValue) : null
  }

  /**
   * Получение CSRF токена для SPA аутентификации
   * Sanctum endpoint находится в web routes (без префикса /api)
   */
  const getCsrfCookie = async () => {
    await $fetch(`${apiBase}/sanctum/csrf-cookie`, {
      credentials: 'include',
      method: 'GET',
    })
  }

  /**
   * API запрос с автоматической обработкой CSRF
   */
  const apiRequest = async <T>(
    endpoint: string,
    options: RequestInit & { method?: string } = {}
  ): Promise<T> => {
    // Проверяем, что API URL настроен
    if (!apiUrl || apiUrl === 'http://localhost:8080/api') {
      console.warn('API URL не настроен или использует значение по умолчанию. Проверьте переменную окружения API_BASE_URL')
    }

    // Для методов с изменением данных получаем CSRF токен
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(options.method?.toUpperCase() || '')) {
      try {
        await getCsrfCookie()
      } catch (error) {
        console.error('Ошибка получения CSRF cookie:', error)
        // Продолжаем выполнение, так как для GET запросов это не критично
      }
    }

    // Убираем /api/ из начала endpoint, так как apiUrl уже содержит /api
    const cleanEndpoint = endpoint.startsWith('/api/') ? endpoint.substring(4) : endpoint

    // Получаем CSRF токен из cookie и добавляем в заголовки
    const csrfToken = getCsrfToken()
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...options.headers as Record<string, string>,
    }

    if (csrfToken) {
      headers['X-XSRF-TOKEN'] = csrfToken
    }

    try {
      const response = await $fetch<T>(`${apiUrl}${cleanEndpoint}`, {
        ...options,
        credentials: 'include',
        headers,
      })

      return response
    } catch (error: any) {
      // Логируем ошибку для отладки
      console.error(`Ошибка API запроса ${endpoint}:`, error)

      // Если это ошибка сети или API недоступен, пробрасываем её дальше
      if (error?.statusCode === 0 || error?.message?.includes('fetch')) {
        throw new Error('API недоступен. Проверьте настройки API_BASE_URL и доступность бекенда.')
      }

      throw error
    }
  }

  /**
   * GET запрос
   */
  const get = <T>(endpoint: string, options = {}) => {
    return apiRequest<T>(endpoint, { ...options, method: 'GET' })
  }

  /**
   * POST запрос
   */
  const post = <T>(endpoint: string, body?: any, options = {}) => {
    return apiRequest<T>(endpoint, {
      ...options,
      method: 'POST',
      body,
    })
  }

  /**
   * PUT запрос
   */
  const put = <T>(endpoint: string, body?: any, options = {}) => {
    return apiRequest<T>(endpoint, {
      ...options,
      method: 'PUT',
      body,
    })
  }

  /**
   * DELETE запрос
   */
  const del = <T>(endpoint: string, options = {}) => {
    return apiRequest<T>(endpoint, { ...options, method: 'DELETE' })
  }

  return {
    get,
    post,
    put,
    delete: del,
    getCsrfCookie,
  }
}
