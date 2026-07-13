<template>
  <div class="chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup lang="ts">
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  BarController,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  BarController,
  Title,
  Tooltip,
  Legend
)

interface Props {
  data: Record<string, number> | Array<{ key: string; value: number }>
  label: string
  groupBy: 'drink' | 'amount'
}

const props = defineProps<Props>()

const chartCanvas = ref<HTMLCanvasElement | null>(null)
let chartInstance: ChartJS | null = null

const createChart = () => {
  if (!chartCanvas.value) return

  // Уничтожаем предыдущий график
  if (chartInstance) {
    chartInstance.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')
  if (!ctx) return

  // Подготовка данных в зависимости от типа группировки
  let chartLabels: string[] = []
  let chartValues: number[] = []

  if (props.groupBy === 'drink') {
    // Для напитков данные приходят как массив объектов {key: string, value: number}
    const dataArray = Array.isArray(props.data) ? props.data : []
    chartLabels = dataArray.map(item => String(item.key || ''))
    chartValues = dataArray.map(item => Number(item.value || 0))
  } else {
    // Для количества - форматируем дату и сортируем по дате
    const entries = Object.entries(props.data).sort((a, b) => {
      const dateA = new Date(a[0])
      const dateB = new Date(b[0])
      return dateA.getTime() - dateB.getTime()
    })

    chartLabels = entries.map(([key]) => {
      const d = new Date(key)
      if (isNaN(d.getTime())) {
        return key
      }
      return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
    })
    chartValues = entries.map(([, value]) => value)
  }

  // Сохраняем ссылки для использования в callbacks
  const labelsRef = chartLabels
  const groupByRef = props.groupBy

  // Создаём конфигурацию без сложных трюков
  const chartConfig: any = {
    type: 'bar',
    data: {
      labels: chartLabels,
      datasets: [{
        label: props.label,
        data: chartValues,
        backgroundColor: 'rgba(99, 102, 241, 0.6)',
        borderColor: 'rgba(99, 102, 241, 1)',
        borderWidth: 2,
        borderRadius: 8,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            color: '#111111',
          }
        },
        tooltip: {
          backgroundColor: '#ffffff',
          borderColor: '#e5e7eb',
          borderWidth: 1,
          padding: 12,
          titleColor: '#111111',
          bodyColor: '#111111',
          titleFont: {
            size: 14,
            weight: 'bold',
          },
          bodyFont: {
            size: 13,
          },
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value: any) {
              return value + ' мл'
            },
            color: '#111111',
          },
          grid: {
            color: 'rgba(0, 0, 0, 0.08)',
          }
        },
        x: {
          type: 'category', // Явно указываем категориальную шкалу
          grid: {
            display: false,
          },
          ticks: {
            maxRotation: groupByRef === 'drink' ? 45 : 0,
            minRotation: groupByRef === 'drink' ? 45 : 0,
            autoSkip: false,
            color: '#111111',
            callback: function(value: any, index: number, ticks: any) {
              if (groupByRef === 'amount') {
                if (index === 0 || index === ticks.length - 1 || index % 3 === 0) {
                  return labelsRef[index]
                }
                return ''
              }
              return value
            }
          }
        }
      }
    }
  }

  chartInstance = new ChartJS(ctx, chartConfig)
}

watch(() => [props.data, props.groupBy], () => {
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
.chart-container {
  position: relative;
  height: 400px;
  width: 100%;
}

@media (max-width: 767px) {
  .chart-container {
    height: 300px;
  }
}
</style>
