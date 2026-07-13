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
    public function getDetailedStatistics(int $userId, ?string $startDate = null, ?string $endDate = null, string $groupBy = 'amount'): array
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
                'data' => $data,
                'total_ml' => $totalMl,
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
            'data' => $allDays,
            'total_ml' => array_sum($allDays),
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

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'metric' => $metric,
            'data' => $data,
            'total' => round($totalValue, 2),
        ];
    }
}
