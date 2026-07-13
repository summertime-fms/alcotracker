<template>
  <div class="chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup lang="ts">
import {
  Chart as ChartJS,
  ArcElement,
  DoughnutController,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(
  ArcElement,
  DoughnutController,
  Tooltip,
  Legend
)

interface WeekdayData {
  key: string
  value: number
  count: number
}

interface Props {
  data: WeekdayData[]
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

  const allColors = [
    'rgba(99, 102, 241, 0.8)',
    'rgba(34, 197, 94, 0.8)',
    'rgba(168, 85, 247, 0.8)',
    'rgba(251, 191, 36, 0.8)',
    'rgba(249, 115, 22, 0.8)',
    'rgba(239, 68, 68, 0.8)',
    'rgba(59, 130, 246, 0.8)',
  ]

  const allBorderColors = [
    'rgba(99, 102, 241, 1)',
    'rgba(34, 197, 94, 1)',
    'rgba(168, 85, 247, 1)',
    'rgba(251, 191, 36, 1)',
    'rgba(249, 115, 22, 1)',
    'rgba(239, 68, 68, 1)',
    'rgba(59, 130, 246, 1)',
  ]

  const filteredData = props.data
    .map((item, index) => ({ ...item, originalIndex: index }))
    .filter(item => item.value > 0)

  if (filteredData.length === 0) return

  const labels = filteredData.map(item => item.key)
  const values = filteredData.map(item => item.value)
  const counts = filteredData.map(item => item.count)
  const colors = filteredData.map(item => allColors[item.originalIndex])
  const borderColors = filteredData.map(item => allBorderColors[item.originalIndex])

  const centerTextPlugin = {
    id: 'centerText',
    beforeDraw: (chart: any) => {
      const { width, height, ctx } = chart
      ctx.restore()

      const total = values.reduce((a, b) => a + b, 0)

      const mainFontSize = (height / 160).toFixed(2)
      ctx.font = `bold ${mainFontSize}em sans-serif`
      ctx.textBaseline = 'middle'
      ctx.fillStyle = '#111111'

      const mainText = `${Math.round(total)} мл`
      const mainTextX = Math.round((width - ctx.measureText(mainText).width) / 2)
      const mainTextY = height / 2 - 10

      ctx.fillText(mainText, mainTextX, mainTextY)

      const labelFontSize = (height / 250).toFixed(2)
      ctx.font = `${labelFontSize}em sans-serif`
      ctx.fillStyle = '#111111'

      const labelText = 'Всего'
      const labelTextX = Math.round((width - ctx.measureText(labelText).width) / 2)
      const labelTextY = height / 2 + 20

      ctx.fillText(labelText, labelTextX, labelTextY)
      ctx.save()
    }
  }
  chartInstance = new ChartJS(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        label: props.label,
        data: values,
        backgroundColor: colors,
        borderColor: borderColors,
        borderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '60%',
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          align: 'center',
          labels: {
            usePointStyle: true,
            padding: 20,
            boxWidth: 12,
            boxHeight: 12,
            font: {
              size: 14,
              family: "'Inter', sans-serif",
              color: '#111111'
            },
            color: '#111111',
            generateLabels: function(chart) {
              const data = chart.data
              if (data.labels && data.labels.length && data.datasets.length) {
                return data.labels.map((label, i) => {
                  const value = values[i]
                  const percentage = values.reduce((a, b) => a + b, 0) > 0
                    ? ((value / values.reduce((a, b) => a + b, 0)) * 100).toFixed(1)
                    : 0

                  return {
                    text: `${label} (${percentage}%)`,
                    fillStyle: colors[i],
                    hidden: false,
                    index: i
                  }
                })
              }
              return []
            }
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
              const index = context.dataIndex
              const value = values[index]
              const count = counts[index]
              const total = values.reduce((a, b) => a + b, 0)
              const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0

              return [
                `${props.label}: ${Math.round(value)} мл`,
                `Процент: ${percentage}%`,
                `Событий: ${count}`
              ]
            }
          }
        }
      }
    },
    plugins: [centerTextPlugin]
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
  height: 500px;
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 767px) {
  .chart-container {
    height: 450px;
    max-width: 100%;
  }
}
</style>
