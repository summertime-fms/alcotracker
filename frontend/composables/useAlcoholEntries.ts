interface AlcoholType {
  value: string
  label: string
}

interface AlcoholEntry {
  id: number
  alcohol_type: string
  alcohol_type_label: string
  amount_ml: number
  drink_date: string
  comment: string | null
  created_at: string
  updated_at: string
}

interface CreateEntryData {
  alcohol_type: string
  amount_ml: number
  drink_date: string
  comment?: string
}

interface UpdateEntryData {
  alcohol_type?: string
  amount_ml?: number
  drink_date?: string
  comment?: string
}

interface Statistics {
  period: {
    start_date: string
    end_date: string
  }
  total_ml: number
  by_type: Record<string, number>
  entries_count: number
}

export const useAlcoholEntries = () => {
  const api = useApi()
  const entries = useState<AlcoholEntry[]>('alcoholEntries', () => [])
  const alcoholTypes = useState<Record<string, string>>('alcoholTypes', () => ({}))
  const isLoading = useState<boolean>('entriesLoading', () => false)

  /**
   * Получить список доступных типов алкоголя
   */
  const getAlcoholTypes = async () => {
    try {
      const response = await api.get<{ data: Record<string, string> }>('/api/alcohol-entries/types')
      alcoholTypes.value = response.data
      return response.data
    } catch (error: any) {
      throw error
    }
  }

  /**
   * Получить записи за определенный период
   */
  const getEntries = async (filters?: { date?: string; start_date?: string; end_date?: string }) => {
    try {
      isLoading.value = true
      const params = new URLSearchParams()
      if (filters?.date) params.append('date', filters.date)
      if (filters?.start_date) params.append('start_date', filters.start_date)
      if (filters?.end_date) params.append('end_date', filters.end_date)

      const queryString = params.toString()
      const endpoint = queryString ? `/api/alcohol-entries?${queryString}` : '/api/alcohol-entries'

      const response = await api.get<{ data: AlcoholEntry[] }>(endpoint)
      entries.value = response.data
      return response.data
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Создать новую запись
   */
  const createEntry = async (data: CreateEntryData) => {
    try {
      isLoading.value = true
      const response = await api.post<{ data: AlcoholEntry; message: string }>('/api/alcohol-entries', data)

      // Добавляем новую запись в начало списка
      entries.value = [response.data, ...entries.value]

      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Обновить запись
   */
  const updateEntry = async (id: number, data: UpdateEntryData) => {
    try {
      isLoading.value = true
      const response = await api.put<{ data: AlcoholEntry; message: string }>(`/api/alcohol-entries/${id}`, data)

      // Обновляем запись в списке
      const index = entries.value.findIndex(e => e.id === id)
      if (index !== -1) {
        entries.value[index] = response.data
      }

      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Удалить запись
   */
  const deleteEntry = async (id: number) => {
    try {
      isLoading.value = true
      const response = await api.delete<{ message: string }>(`/api/alcohol-entries/${id}`)

      // Удаляем запись из списка
      entries.value = entries.value.filter(e => e.id !== id)

      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Получить статистику за период
   */
  const getStatistics = async (startDate?: string, endDate?: string) => {
    try {
      const params = new URLSearchParams()
      if (startDate) params.append('start_date', startDate)
      if (endDate) params.append('end_date', endDate)

      const queryString = params.toString()
      const endpoint = queryString
        ? `/api/alcohol-entries/statistics?${queryString}`
        : '/api/alcohol-entries/statistics'

      const response = await api.get<Statistics>(endpoint)
      return response
    } catch (error: any) {
      throw error
    }
  }

  /**
   * Получить детальную статистику по дням
   */
  const getDetailedStatistics = async (
    startDate?: string,
    endDate?: string,
    groupBy: string = 'total',
    alcoholType?: string
  ) => {
    try {
      const params = new URLSearchParams()
      if (startDate) params.append('start_date', startDate)
      if (endDate) params.append('end_date', endDate)
      if (groupBy) params.append('group_by', groupBy)
      if (alcoholType) params.append('alcohol_type', alcoholType)

      const queryString = params.toString()
      const endpoint = queryString
        ? `/api/alcohol-entries/statistics/detailed?${queryString}`
        : '/api/alcohol-entries/statistics/detailed'

      const response = await api.get<any>(endpoint)
      return response
    } catch (error: any) {
      throw error
    }
  }

  /**
   * Получить статистику по чистому спирту
   */
  const getPureAlcoholStatistics = async (startDate?: string, endDate?: string, groupBy: string = 'amount') => {
    try {
      const params = new URLSearchParams()
      if (startDate) params.append('start_date', startDate)
      if (endDate) params.append('end_date', endDate)
      if (groupBy) params.append('group_by', groupBy)

      const queryString = params.toString()
      const endpoint = queryString
        ? `/api/alcohol-entries/statistics/pure-alcohol?${queryString}`
        : '/api/alcohol-entries/statistics/pure-alcohol'

      const response = await api.get<any>(endpoint)
      return response
    } catch (error: any) {
      throw error
    }
  }

  /**
   * Получить статистику по дням недели
   */
  const getWeekdayStatistics = async (startDate?: string, endDate?: string, metric: string = 'pure_alcohol') => {
    try {
      const params = new URLSearchParams()
      if (startDate) params.append('start_date', startDate)
      if (endDate) params.append('end_date', endDate)
      if (metric) params.append('metric', metric)

      const queryString = params.toString()
      const endpoint = queryString
        ? `/api/alcohol-entries/statistics/weekday?${queryString}`
        : '/api/alcohol-entries/statistics/weekday'

      const response = await api.get<any>(endpoint)
      return response
    } catch (error: any) {
      throw error
    }
  }

  return {
    entries: readonly(entries),
    alcoholTypes: readonly(alcoholTypes),
    isLoading: readonly(isLoading),
    getAlcoholTypes,
    getEntries,
    createEntry,
    updateEntry,
    deleteEntry,
    getStatistics,
    getDetailedStatistics,
    getPureAlcoholStatistics,
    getWeekdayStatistics,
  }
}
