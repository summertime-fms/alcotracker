export interface DetoxCalendarDay {
  date: string
  status: 'red' | 'yellow' | 'green' | 'blank'
  pure_alcohol_ml: number
  sober_day_index?: number
}

export interface DetoxInsights {
  current_streak_days: number
  max_streak_days: number
  last_drink_timestamp: string | null
  calendar_data: DetoxCalendarDay[]
  period: {
    year: number
    min_year: number
    max_year: number
  }
}

export const useDetox = () => {
  const api = useApi()
  const insights = useState<DetoxInsights | null>('detoxInsights', () => null)
  const isLoading = useState<boolean>('detoxLoading', () => false)

  const getInsights = async (year?: number) => {
    try {
      isLoading.value = true
      const params = new URLSearchParams()
      if (year) params.append('year', String(year))

      const queryString = params.toString()
      const endpoint = queryString
        ? `/api/detox/insights?${queryString}`
        : '/api/detox/insights'

      const response = await api.get<DetoxInsights>(endpoint)
      insights.value = response
      return response
    } catch (error: any) {
      throw error
    } finally {
      isLoading.value = false
    }
  }

  return {
    insights: readonly(insights),
    isLoading: readonly(isLoading),
    getInsights,
  }
}
