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
  data: Record<string, number>
  label: string
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

  const labels = Object.keys(props.data).map(date => {
    const d = new Date(date)
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
  })

  const values = Object.values(props.data)

  chartInstance = new ChartJS(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: props.label,
        data: values,
        backgroundColor: 'rgba(99, 102, 241, 0.5)',
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
        },
        tooltip: {
          backgroundColor: 'rgba(44, 62, 80, 0.9)',
          padding: 12,
          titleFont: {
            size: 14,
            weight: 'bold',
          },
          bodyFont: {
            size: 13,
          },
          callbacks: {
            label: function(context: any) {
              return `${context.dataset.label}: ${context.parsed.y} мл`
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
            }
          },
          grid: {
            color: 'rgba(0, 0, 0, 0.05)',
          }
        },
        x: {
          grid: {
            display: false,
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
