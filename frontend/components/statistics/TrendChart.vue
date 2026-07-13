<template>
  <div class="trend-chart">
    <div class="chart-container">
      <canvas ref="chartCanvas"></canvas>
    </div>
    <p class="chart-legend-hint">
      <strong>Скользящее среднее</strong> — усреднение за 7 дней, сглаживает скачки и показывает типичный уровень.
      <strong>Линия тренда</strong> — общее направление за период: растёт, снижается или стабильно.
    </p>
  </div>
</template>

<script setup lang="ts">
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  LineController,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  LineController,
  Title,
  Tooltip,
  Legend,
  Filler
)

interface Props {
  data: Record<string, number>
  label: string
}

const props = defineProps<Props>()

const chartCanvas = ref<HTMLCanvasElement | null>(null)
let chartInstance: ChartJS | null = null

/**
 * Вычисление линейной регрессии для трендовой линии
 * Возвращает коэффициенты a и b для уравнения y = ax + b
 */
const calculateLinearRegression = (values: number[]): { a: number; b: number } => {
  const n = values.length
  if (n === 0) return { a: 0, b: 0 }

  // Создаем массив индексов (x-координаты)
  const xValues = Array.from({ length: n }, (_, i) => i)

  // Вычисляем средние значения
  const sumX = xValues.reduce((sum, x) => sum + x, 0)
  const sumY = values.reduce((sum, y) => sum + y, 0)
  const meanX = sumX / n
  const meanY = sumY / n

  // Вычисляем коэффициенты
  let numerator = 0
  let denominator = 0

  for (let i = 0; i < n; i++) {
    numerator += (xValues[i] - meanX) * (values[i] - meanY)
    denominator += (xValues[i] - meanX) ** 2
  }

  const a = denominator !== 0 ? numerator / denominator : 0
  const b = meanY - a * meanX

  return { a, b }
}

/**
 * Генерация значений трендовой линии
 */
const generateTrendLine = (values: number[]): number[] => {
  const { a, b } = calculateLinearRegression(values)
  return values.map((_, index) => a * index + b)
}

/**
 * Вычисление скользящего среднего
 */
const calculateMovingAverage = (values: number[], window: number = 7): number[] => {
  const result: number[] = []
  for (let i = 0; i < values.length; i++) {
    const start = Math.max(0, i - Math.floor(window / 2))
    const end = Math.min(values.length, i + Math.ceil(window / 2))
    const slice = values.slice(start, end)
    const avg = slice.reduce((sum, val) => sum + val, 0) / slice.length
    result.push(avg)
  }
  return result
}

const createChart = () => {
  if (!chartCanvas.value) return

  // Уничтожаем предыдущий график
  if (chartInstance) {
    chartInstance.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')
  if (!ctx) return

  // Сортируем данные по дате
  const entries = Object.entries(props.data).sort((a, b) => {
    const dateA = new Date(a[0])
    const dateB = new Date(b[0])
    return dateA.getTime() - dateB.getTime()
  })

  // Подготовка меток (даты)
  const chartLabels = entries.map(([key]) => {
    const d = new Date(key)
    if (isNaN(d.getTime())) return key
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
  })

  // Значения потребления
  const chartValues = entries.map(([, value]) => value)

  // Вычисляем трендовую линию
  const trendValues = generateTrendLine(chartValues)

  // Вычисляем скользящее среднее (опционально, для сглаживания)
  const movingAverageValues = calculateMovingAverage(chartValues, 7)

  // Определяем направление тренда для подсказки
  const trend = trendValues[trendValues.length - 1] - trendValues[0]
  const trendDirection = trend > 0 ? '↗️ Рост' : trend < 0 ? '↘️ Снижение' : '→ Стабильно'

  // Создаём конфигурацию графика
  chartInstance = new ChartJS(ctx, {
    type: 'line',
    data: {
      labels: chartLabels,
      datasets: [
        {
          label: props.label,
          data: chartValues,
          borderColor: 'rgba(99, 102, 241, 1)',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointBackgroundColor: 'rgba(99, 102, 241, 1)',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          tension: 0.1,
          fill: true,
        },
        {
          label: 'Скользящее среднее (7 дней)',
          data: movingAverageValues,
          borderColor: 'rgba(251, 191, 36, 1)',
          backgroundColor: 'rgba(251, 191, 36, 0)',
          borderWidth: 2,
          borderDash: [5, 5],
          pointRadius: 0,
          pointHoverRadius: 4,
          tension: 0.4,
          fill: false,
        },
        {
          label: `Линия тренда (${trendDirection})`,
          data: trendValues,
          borderColor: trend >= 0 ? 'rgba(239, 68, 68, 0.8)' : 'rgba(34, 197, 94, 0.8)',
          backgroundColor: 'rgba(0, 0, 0, 0)',
          borderWidth: 3,
          borderDash: [10, 5],
          pointRadius: 0,
          pointHoverRadius: 0,
          tension: 0,
          fill: false,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            usePointStyle: true,
            padding: 15,
            font: {
              size: 12,
            },
            color: '#111111',
          }
        },
        tooltip: {
          backgroundColor: '#ffffff',
          borderColor: '#e5e7eb',
          borderWidth: 1,
          padding: 16,
          titleColor: '#111111',
          bodyColor: '#111111',
          titleFont: {
            size: 14,
            weight: 'bold',
          },
          bodyFont: {
            size: 13,
          },
          bodySpacing: 8,
          callbacks: {
            label: function(context: any) {
              let label = context.dataset.label || ''
              if (label) {
                label += ': '
              }
              if (context.parsed.y !== null) {
                label += Math.round(context.parsed.y) + ' мл'
              }
              return label
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value: any) {
              return value + ' мл'
            },
            font: {
              size: 11,
            },
            color: '#111111',
          },
          grid: {
            color: 'rgba(0, 0, 0, 0.08)',
          }
        },
        x: {
          grid: {
            display: false,
          },
          ticks: {
            maxRotation: 45,
            minRotation: 0,
            autoSkip: true,
            maxTicksLimit: 15,
            font: {
              size: 11,
            },
            color: '#111111',
          }
        }
      }
    }
  })
}

watch(() => props.data, () => {
  createChart()
}, { deep: true })

onMounted(() => {
  createChart()
})

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy()
  }
})
</script>

<style scoped>
.trend-chart {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.chart-container {
  position: relative;
  height: 450px;
  width: 100%;
}

.chart-legend-hint {
  margin: 0;
  padding: var(--spacing-md);
  font-size: var(--font-size-sm);
  line-height: 1.5;
  color: var(--color-text-muted);
  background: var(--color-accent-light);
  border: 1px solid rgba(99, 102, 241, 0.12);
  border-radius: var(--radius-md);
}

.chart-legend-hint strong {
  color: var(--color-text-light);
  font-weight: var(--font-weight-semibold);
}

@media (max-width: 767px) {
  .chart-container {
    height: 350px;
  }
}
</style>
