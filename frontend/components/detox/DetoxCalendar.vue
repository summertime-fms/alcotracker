<template>
  <div ref="calendarRoot" class="detox-calendar">
    <div class="detox-calendar__header">
      <h3>Календарь детокса</h3>
      <div class="detox-calendar__controls">
        <UiButton variant="outline" size="sm" :disabled="isMinYear" @click="goToPreviousYear">
          ←
        </UiButton>
        <span class="detox-calendar__period">{{ yearLabel }}</span>
        <UiButton variant="outline" size="sm" :disabled="isMaxYear" @click="goToNextYear">
          →
        </UiButton>
      </div>
    </div>

    <div class="detox-calendar__legend">
      <span class="legend-item"><span class="legend-swatch legend-swatch--red" /> Употребление</span>
      <span class="legend-item"><span class="legend-swatch legend-swatch--yellow" /> Метаболизм</span>
      <span class="legend-item"><span class="legend-swatch legend-swatch--green" /> Восстановление</span>
    </div>

    <div ref="gridWrapper" class="detox-calendar__grid-wrapper" @mouseleave="hideTooltip">
      <div class="detox-calendar__weekdays-column">
        <div class="detox-calendar__weekdays">
          <span v-for="label in weekdayLabels" :key="label" class="weekday-label">{{ label }}</span>
        </div>
        <div class="detox-calendar__months-spacer" aria-hidden="true" />
      </div>

      <div class="detox-calendar__main">
        <div class="detox-calendar__grid">
          <div
            v-for="(week, weekIndex) in weekColumns"
            :key="weekIndex"
            class="detox-calendar__week-column"
          >
            <div
              v-for="(cell, rowIndex) in week"
              :key="`${weekIndex}-${rowIndex}`"
              class="detox-calendar__cell"
              :class="{ 'detox-calendar__cell--empty': !cell }"
            >
              <span
                v-if="cell"
                class="day-square"
                :class="{
                  'day-square--blank': cell.status === 'blank',
                  'day-square--interactive': cell.status !== 'blank',
                }"
                :style="getCellStyle(cell)"
                @mouseenter="showTooltip($event, cell, rowIndex)"
              />
            </div>
          </div>
        </div>

        <div class="detox-calendar__months">
          <span
            v-for="(label, weekIndex) in monthLabelsByWeek"
            :key="weekIndex"
            class="month-label"
          >{{ label }}</span>
        </div>
      </div>
    </div>

    <div
      v-if="tooltip.visible"
      class="detox-calendar__tooltip"
      :class="`detox-calendar__tooltip--${tooltip.placement}`"
      :style="tooltipStyle"
    >
      {{ tooltip.text }}
    </div>
  </div>
</template>

<script setup lang="ts">
import type { DetoxCalendarDay } from '~/composables/useDetox'

interface Props {
  calendarData: DetoxCalendarDay[]
  year: number
  minYear: number
  maxYear: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'change-year': [year: number]
}>()

const weekdayLabels = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
const calendarRoot = ref<HTMLElement | null>(null)
const gridWrapper = ref<HTMLElement | null>(null)

const tooltip = reactive({
  visible: false,
  text: '',
  x: 0,
  y: 0,
  placement: 'top' as 'top' | 'bottom',
})

const tooltipStyle = computed(() => ({
  left: `${tooltip.x}px`,
  top: `${tooltip.y}px`,
}))

const yearLabel = computed(() => String(props.year))

const isMinYear = computed(() => props.year <= props.minYear)
const isMaxYear = computed(() => props.year >= props.maxYear)

const maxPureAlcohol = computed(() => {
  const values = props.calendarData
    .filter(day => day.status === 'red')
    .map(day => day.pure_alcohol_ml)

  return Math.max(...values, 1)
})

const weekColumns = computed(() => {
  const firstWeekday = (new Date(props.year, 0, 1).getDay() + 6) % 7
  const totalSlots = firstWeekday + props.calendarData.length
  const numWeeks = Math.ceil(totalSlots / 7)
  const columns: Array<Array<DetoxCalendarDay | null>> = Array.from(
    { length: numWeeks },
    () => Array(7).fill(null)
  )

  props.calendarData.forEach((day, index) => {
    const slotIndex = firstWeekday + index
    const week = Math.floor(slotIndex / 7)
    const row = slotIndex % 7
    columns[week][row] = day
  })

  return columns
})

const monthLabelsByWeek = computed(() => {
  return weekColumns.value.map((week) => {
    const days = week.filter((day): day is DetoxCalendarDay => day !== null)

    for (const day of days) {
      const date = new Date(`${day.date}T00:00:00`)

      if (date.getDate() === 1) {
        return date
          .toLocaleDateString('ru-RU', { month: 'short' })
          .replace(/\.$/, '')
      }
    }

    return ''
  })
})

const formatDateLabel = (dateKey: string) => {
  const date = new Date(`${dateKey}T00:00:00`)
  return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' })
}

const getTooltip = (day: DetoxCalendarDay) => {
  const dateLabel = formatDateLabel(day.date)

  if (day.status === 'red') {
    return `${dateLabel}: Употребление, ${day.pure_alcohol_ml} мл чистого спирта`
  }

  if (day.status === 'yellow') {
    return `${dateLabel}: День метаболизма`
  }

  return `${dateLabel}: Трезвый день`
}

const getCellStyle = (day: DetoxCalendarDay) => {
  if (day.status === 'blank') {
    return { backgroundColor: 'rgba(148, 163, 184, 0.28)' }
  }

  if (day.status === 'red') {
    const intensity = 0.45 + (day.pure_alcohol_ml / maxPureAlcohol.value) * 0.55
    return { backgroundColor: `rgba(239, 68, 68, ${intensity})` }
  }

  if (day.status === 'yellow') {
    return { backgroundColor: 'rgba(245, 158, 11, 0.82)' }
  }

  const soberIndex = day.sober_day_index || 1
  const intensity = 0.35 + Math.min(soberIndex / 14, 1) * 0.65
  return { backgroundColor: `rgba(16, 185, 129, ${intensity})` }
}

const showTooltip = (event: MouseEvent, day: DetoxCalendarDay, rowIndex: number) => {
  if (day.status === 'blank' || !calendarRoot.value) {
    return
  }

  const cell = event.currentTarget as HTMLElement
  const rootRect = calendarRoot.value.getBoundingClientRect()
  const cellRect = cell.getBoundingClientRect()
  const showBelow = rowIndex <= 1

  tooltip.text = getTooltip(day)
  tooltip.x = cellRect.left - rootRect.left + cellRect.width / 2
  tooltip.placement = showBelow ? 'bottom' : 'top'
  tooltip.y = showBelow
    ? cellRect.bottom - rootRect.top + 8
    : cellRect.top - rootRect.top - 8
  tooltip.visible = true
}

const hideTooltip = () => {
  tooltip.visible = false
}

const goToPreviousYear = () => {
  if (isMinYear.value) return
  emit('change-year', props.year - 1)
}

const goToNextYear = () => {
  if (isMaxYear.value) return
  emit('change-year', props.year + 1)
}
</script>

<style scoped>
.detox-calendar {
  position: relative;
  padding: var(--spacing-xl);
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(12px);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  overflow: visible;
}

.detox-calendar__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
  flex-wrap: wrap;
}

.detox-calendar__header h3 {
  margin: 0;
  font-family: var(--font-family-display);
  font-size: var(--font-size-xl);
}

.detox-calendar__controls {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.detox-calendar__period {
  min-width: 72px;
  text-align: center;
  font-weight: var(--font-weight-semibold);
  color: var(--color-text);
}

.detox-calendar__legend {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
}

.legend-item {
  display: inline-flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
}

.legend-swatch {
  width: 14px;
  height: 14px;
  border-radius: var(--radius-sm);
}

.legend-swatch--red {
  background: rgba(239, 68, 68, 0.85);
}

.legend-swatch--yellow {
  background: rgba(245, 158, 11, 0.82);
}

.legend-swatch--green {
  background: rgba(16, 185, 129, 0.85);
}

.detox-calendar__grid-wrapper {
  display: flex;
  align-items: stretch;
  gap: var(--spacing-sm);
  width: 100%;
  overflow: visible;
}

.detox-calendar__weekdays-column {
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  width: 24px;
}

.detox-calendar__main {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.detox-calendar__weekdays {
  display: grid;
  flex: 1;
  grid-template-rows: repeat(7, 1fr);
  gap: 3px;
}

.detox-calendar__months-spacer {
  height: 14px;
  flex-shrink: 0;
}

.weekday-label {
  display: flex;
  align-items: center;
  font-size: 10px;
  color: var(--color-text-muted);
  line-height: 1;
}

.detox-calendar__grid {
  display: flex;
  flex: 1;
  gap: 3px;
  min-width: 0;
}

.detox-calendar__months {
  display: flex;
  gap: 3px;
  min-height: 14px;
}

.month-label {
  flex: 1;
  min-width: 0;
  font-size: 10px;
  line-height: 1;
  color: var(--color-text-muted);
  text-align: left;
  overflow: hidden;
  white-space: nowrap;
}

.detox-calendar__week-column {
  display: grid;
  grid-template-rows: repeat(7, 1fr);
  gap: 3px;
  flex: 1;
  min-width: 0;
}

.detox-calendar__cell {
  width: 100%;
  aspect-ratio: 1;
  min-width: 0;
}

.detox-calendar__cell--empty {
  visibility: hidden;
}

.day-square {
  display: block;
  width: 100%;
  height: 100%;
  border-radius: 2px;
}

.day-square--interactive {
  cursor: pointer;
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
}

.day-square--interactive:hover {
  transform: scale(1.2);
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.35);
}

.day-square--blank {
  cursor: default;
  pointer-events: none;
}

.detox-calendar__tooltip {
  position: absolute;
  z-index: 20;
  padding: var(--spacing-xs) var(--spacing-sm);
  font-size: var(--font-size-sm);
  line-height: 1.4;
  color: var(--color-text);
  white-space: nowrap;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  pointer-events: none;
}

.detox-calendar__tooltip--top {
  transform: translate(-50%, -100%);
}

.detox-calendar__tooltip--bottom {
  transform: translate(-50%, 0);
}

.detox-calendar__tooltip--top::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-top-color: #ffffff;
}

.detox-calendar__tooltip--bottom::after {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-bottom-color: #ffffff;
}

@media (max-width: 767px) {
  .detox-calendar {
    padding: var(--spacing-md);
  }

  .detox-calendar__header {
    flex-direction: column;
    align-items: flex-start;
  }

  .detox-calendar__controls {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
