<?php

namespace App\Services;

use App\Models\AlcoholEntry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AlcoholEntryService
{
    /**
     * Получить записи пользователя с фильтрацией
     *
     * @param int $userId
     * @param array $filters
     * @return Collection
     */
    public function getUserEntries(int $userId, array $filters = []): Collection
    {
        $query = AlcoholEntry::where('user_id', $userId);

        // Фильтр по конкретной дате
        if (isset($filters['date'])) {
            $query->whereDate('drink_date', $filters['date']);
        }

        // Фильтр по диапазону дат
        if (isset($filters['start_date'])) {
            $query->whereDate('drink_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('drink_date', '<=', $filters['end_date']);
        }

        return $query->orderBy('drink_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Создать новую запись об алкоголе
     *
     * @param int $userId
     * @param array $data
     * @return AlcoholEntry
     */
    public function createEntry(int $userId, array $data): AlcoholEntry
    {
        return AlcoholEntry::create([
            'user_id' => $userId,
            'alcohol_type' => $data['alcohol_type'],
            'amount_ml' => $data['amount_ml'],
            'drink_date' => $data['drink_date'],
            'comment' => $data['comment'] ?? null,
        ]);
    }

    /**
     * Обновить запись об алкоголе
     *
     * @param AlcoholEntry $entry
     * @param array $data
     * @return AlcoholEntry
     */
    public function updateEntry(AlcoholEntry $entry, array $data): AlcoholEntry
    {
        $entry->update($data);
        return $entry->fresh();
    }

    /**
     * Удалить запись об алкоголе
     *
     * @param AlcoholEntry $entry
     * @return bool
     */
    public function deleteEntry(AlcoholEntry $entry): bool
    {
        return $entry->delete();
    }

    /**
     * Получить статистику за период
     *
     * @param int $userId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getStatistics(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        // По умолчанию статистика за последние 7 дней
        $startDate = $startDate ?? Carbon::now()->subDays(7)->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->format('Y-m-d');

        $entries = AlcoholEntry::where('user_id', $userId)
            ->whereDate('drink_date', '>=', $startDate)
            ->whereDate('drink_date', '<=', $endDate)
            ->get();

        // Общее количество
        $totalMl = $entries->sum('amount_ml');

        // Количество по типам
        $byType = $entries->groupBy(fn($entry) => $entry->alcohol_type->value)
            ->map(fn($group) => $group->sum('amount_ml'))
            ->toArray();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'total_ml' => $totalMl,
            'by_type' => $byType,
            'entries_count' => $entries->count(),
        ];
    }

    /**
     * Получить детальную статистику
     *
     * @param int $userId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string $groupBy - 'drink' (по типам напитков) или 'amount' (общее количество)
     * @return array
     */
    public function getDetailedStatistics(
        int $userId,
        ?string $startDate = null,
        ?string $endDate = null,
        string $groupBy = 'amount',
        ?string $alcoholType = null
    ): array {
        // По умолчанию статистика за текущий месяц
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $entries = AlcoholEntry::where('user_id', $userId)
            ->whereDate('drink_date', '>=', $startDate)
            ->whereDate('drink_date', '<=', $endDate)
            ->get();

        $entriesByType = $entries
            ->groupBy(fn ($entry) => $entry->alcohol_type->value)
            ->map(fn ($group) => $group->count())
            ->toArray();

        if ($alcoholType !== null) {
            $entries = $entries->filter(
                fn ($entry) => $entry->alcohol_type->value === $alcoholType
            );
        }

        // Если группируем по типам напитков
        if ($groupBy === 'drink') {
            $byType = $entries->groupBy(function ($entry) {
                return $entry->alcohol_type->value;
            })->map(function ($typeEntries) {
                return $typeEntries->sum('amount_ml');
            })->toArray();

            // Преобразуем в массив объектов key-value
            $data = [];
            foreach ($byType as $type => $amount) {
                $alcoholType = \App\Enums\AlcoholType::from($type);
                $data[] = [
                    'key' => $alcoholType->label(),
                    'value' => $amount
                ];
            }

            // Сортируем по убыванию количества
            usort($data, function ($a, $b) {
                return $b['value'] <=> $a['value'];
            });

            $totalMl = array_sum(array_column($data, 'value'));

            return [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'group_by' => $groupBy,
                'alcohol_type' => $alcoholType,
                'data' => $data,
                'total_ml' => $totalMl,
                'entries_by_type' => $entriesByType,
            ];
        }

        // Если группируем по количеству (по дням)
        $byDay = $entries->groupBy(function ($entry) {
            return $entry->drink_date->format('Y-m-d');
        })->map(function ($dayEntries) {
            return $dayEntries->sum('amount_ml');
        });

        // Создаем массив со всеми днями периода (даже с нулевыми значениями)
        $period = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $allDays = [];

        while ($period <= $end) {
            $dateKey = $period->format('Y-m-d');
            $allDays[$dateKey] = $byDay->get($dateKey, 0);
            $period->addDay();
        }

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'group_by' => $groupBy,
            'alcohol_type' => $alcoholType,
            'data' => $allDays,
            'total_ml' => array_sum($allDays),
            'entries_by_type' => $entriesByType,
        ];
    }

    /**
     * Получить статистику по чистому спирту
     *
     * @param int $userId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string $groupBy - 'drink' (по типам напитков) или 'amount' (по дням)
     * @return array
     */
    public function getPureAlcoholStatistics(int $userId, ?string $startDate = null, ?string $endDate = null, string $groupBy = 'amount'): array
    {
        // По умолчанию статистика за текущий месяц
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $entries = AlcoholEntry::where('user_id', $userId)
            ->whereDate('drink_date', '>=', $startDate)
            ->whereDate('drink_date', '<=', $endDate)
            ->get();

        // Если группируем по типам напитков
        if ($groupBy === 'drink') {
            $byType = $entries->groupBy(function ($entry) {
                return $entry->alcohol_type->value;
            })->map(function ($typeEntries) {
                return $typeEntries->sum(function ($entry) {
                    return $entry->pure_alcohol_ml;
                });
            })->toArray();

            // Преобразуем в массив объектов key-value
            $data = [];
            foreach ($byType as $type => $pureAlcohol) {
                $alcoholType = \App\Enums\AlcoholType::from($type);
                $data[] = [
                    'key' => $alcoholType->label(),
                    'value' => round($pureAlcohol, 2)
                ];
            }

            // Сортируем по убыванию количества
            usort($data, function ($a, $b) {
                return $b['value'] <=> $a['value'];
            });

            $totalPureAlcohol = array_sum(array_column($data, 'value'));

            return [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'group_by' => $groupBy,
                'data' => $data,
                'total_pure_alcohol_ml' => round($totalPureAlcohol, 2),
            ];
        }

        // Если группируем по дням
        $byDay = $entries->groupBy(function ($entry) {
            return $entry->drink_date->format('Y-m-d');
        })->map(function ($dayEntries) {
            return $dayEntries->sum(function ($entry) {
                return $entry->pure_alcohol_ml;
            });
        });

        // Создаем массив со всеми днями периода (даже с нулевыми значениями)
        $period = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $allDays = [];

        while ($period <= $end) {
            $dateKey = $period->format('Y-m-d');
            $allDays[$dateKey] = round($byDay->get($dateKey, 0), 2);
            $period->addDay();
        }

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'group_by' => $groupBy,
            'data' => $allDays,
            'total_pure_alcohol_ml' => round(array_sum($allDays), 2),
        ];
    }

    /**
     * Получить статистику по дням недели
     *
     * @param int $userId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string $metric - 'volume' (объем) или 'pure_alcohol' (чистый спирт)
     * @return array
     */
    public function getWeekdayStatistics(int $userId, ?string $startDate = null, ?string $endDate = null, string $metric = 'pure_alcohol'): array
    {
        // По умолчанию статистика за текущий месяц
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $entries = AlcoholEntry::where('user_id', $userId)
            ->whereDate('drink_date', '>=', $startDate)
            ->whereDate('drink_date', '<=', $endDate)
            ->get();

        // Группируем по дням недели (0 = воскресенье, 1 = понедельник, ..., 6 = суббота)
        $byWeekday = $entries->groupBy(function ($entry) {
            // Получаем номер дня недели (0-6, где 0 = воскресенье)
            return $entry->drink_date->dayOfWeek;
        });

        // Названия дней недели на русском (начиная с понедельника)
        $weekdayNames = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            0 => 'Воскресенье',
        ];

        // Формируем данные для каждого дня недели
        $data = [];
        foreach ([1, 2, 3, 4, 5, 6, 0] as $dayNumber) {
            $dayEntries = $byWeekday->get($dayNumber, collect());

            if ($metric === 'pure_alcohol') {
                $value = $dayEntries->sum(function ($entry) {
                    return $entry->pure_alcohol_ml;
                });
            } else {
                $value = $dayEntries->sum('amount_ml');
            }

            $data[] = [
                'key' => $weekdayNames[$dayNumber],
                'value' => round($value, 2),
                'count' => $dayEntries->count(), // Количество записей
            ];
        }

        $totalValue = array_sum(array_column($data, 'value'));

        $dailyTotals = $this->buildDailyConsumptionTotals($entries, $metric);
        $averageDose = $this->calculateAverageDosePerDrinkingDay($dailyTotals);
        $patternAnalysis = $this->buildPatternAnalysis($data, $totalValue, $dailyTotals);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'metric' => $metric,
            'data' => $data,
            'total' => round($totalValue, 2),
            'average_dose_ml' => $averageDose['value'],
            'days_with_entries' => $averageDose['days_count'],
            'pattern_analysis' => $patternAnalysis,
        ];
    }

    /**
     * Суммарное потребление по календарным дням (только дни с записями).
     */
    private function buildDailyConsumptionTotals($entries, string $metric): array
    {
        $byDate = $entries->groupBy(fn ($entry) => $entry->drink_date->format('Y-m-d'));

        $dailyTotals = [];
        foreach ($byDate as $date => $dayEntries) {
            $value = $metric === 'pure_alcohol'
                ? $dayEntries->sum(fn ($entry) => $entry->pure_alcohol_ml)
                : $dayEntries->sum('amount_ml');

            if ($value > 0) {
                $dailyTotals[$date] = round($value, 2);
            }
        }

        return $dailyTotals;
    }

    /**
     * Средняя доза за день с записями.
     */
    private function calculateAverageDosePerDrinkingDay(array $dailyTotals): array
    {
        $daysCount = count($dailyTotals);

        if ($daysCount === 0) {
            return ['value' => 0, 'days_count' => 0];
        }

        return [
            'value' => round(array_sum($dailyTotals) / $daysCount, 1),
            'days_count' => $daysCount,
        ];
    }

    /**
     * Анализ паттерна потребления: будни (Пн–Чт) vs выходные (Пт–Вс).
     */
    private function buildPatternAnalysis(array $weekdayData, float $totalValue, array $dailyTotals): ?array
    {
        if ($totalValue <= 0) {
            return null;
        }

        // Порядок data: Пн, Вт, Ср, Чт, Пт, Сб, Вс
        $weekdaysSum = array_sum(array_column(array_slice($weekdayData, 0, 4), 'value'));
        $weekendsSum = array_sum(array_column(array_slice($weekdayData, 4, 3), 'value'));

        $weekdaysPercent = (int) round($weekdaysSum / $totalValue * 100);
        $weekendsPercent = 100 - $weekdaysPercent;

        if ($weekendsPercent > 70) {
            $type = 'weekend_warrior';
            $label = 'Преимущественно выходные';
            $insightText = sprintf(
                'Вы практически не пьёте в будни (Пн–Чт), но %d%% чистого спирта приходится на пятницу, субботу и воскресенье. Берегите печень в субботу!',
                $weekendsPercent
            );
        } elseif ($weekdaysPercent > 60) {
            $type = 'weekday_sipper';
            $label = 'Преимущественно будни';
            $insightText = sprintf(
                'Вы потребляете %d%% алкоголя в рабочие дни (Пн–Чт). Возможно, это ваш способ снять стресс после работы. Попробуйте заменить его вечерней прогулкой.',
                $weekdaysPercent
            );
        } else {
            $type = 'steady_drinker';
            $label = 'Равномерное распределение';
            $insightText = 'Потребление распределено относительно равномерно между буднями и выходными. Нет выраженных пиков, но стоит следить за общим количеством «сухих» дней.';
        }

        $anomaly = $this->detectConsumptionAnomaly($dailyTotals, $type, $label);

        return [
            'type' => $type,
            'label' => $label,
            'weekdays_percent' => $weekdaysPercent,
            'weekends_percent' => $weekendsPercent,
            'insight_text' => $insightText,
            'anomaly' => $anomaly,
        ];
    }

    /**
     * Пиковый день, выбивающийся из доминирующего паттерна.
     */
    private function detectConsumptionAnomaly(array $dailyTotals, string $patternType, string $patternLabel): ?array
    {
        if (count($dailyTotals) < 2) {
            return null;
        }

        arsort($dailyTotals);
        $dates = array_keys($dailyTotals);
        $values = array_values($dailyTotals);

        $maxDate = $dates[0];
        $maxValue = $values[0];
        $secondMax = $values[1];
        $averageDose = array_sum($values) / count($values);

        $maxDayOfWeek = Carbon::parse($maxDate)->dayOfWeek;
        $isWeekdayPeak = in_array($maxDayOfWeek, [1, 2, 3, 4], true);
        $isWeekendPeak = in_array($maxDayOfWeek, [5, 6, 0], true);

        $isSignificantPeak = $maxValue >= $averageDose * 1.75 && $maxValue > $secondMax * 1.25;
        if (!$isSignificantPeak) {
            return null;
        }

        $isPatternMismatch = ($patternType === 'weekend_warrior' && $isWeekdayPeak)
            || ($patternType === 'weekday_sipper' && $isWeekendPeak);

        if (!$isPatternMismatch && $maxValue < $averageDose * 2) {
            return null;
        }

        $weekdayNames = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            0 => 'Воскресенье',
        ];

        $formattedDate = Carbon::parse($maxDate)->locale('ru')->translatedFormat('j F');

        return [
            'date' => $maxDate,
            'day_name' => $weekdayNames[$maxDayOfWeek],
            'formatted_date' => $formattedDate,
            'value_ml' => round($maxValue, 1),
            'text' => sprintf(
                '%s, %s, стала самым тяжёлым днём выбранного периода (%s мл спирта), что выбивается из вашего обычного паттерна «%s».',
                $weekdayNames[$maxDayOfWeek],
                $formattedDate,
                round($maxValue),
                $patternLabel
            ),
        ];
    }
}
