<?php

namespace App\Services;

use App\Models\AlcoholEntry;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DetoxService
{
    /**
     * Получить данные для раздела «Здоровье и Детокс»
     */
    public function getInsights(int $userId, ?int $year = null): array
    {
        $user = User::findOrFail($userId);
        $userSince = $user->created_at->copy()->startOfDay();
        $minYear = (int) $userSince->year;
        $maxYear = (int) Carbon::now()->year;

        $year = $year ?? $maxYear;
        $year = max($minYear, min($year, $maxYear));

        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();
        $today = Carbon::today();

        $allEntries = AlcoholEntry::where('user_id', $userId)
            ->orderBy('drink_date')
            ->orderBy('created_at')
            ->get();

        $lastEntry = $allEntries->sortByDesc('created_at')->first();
        $drinksByDate = $this->groupEntriesByDate($allEntries);

        return [
            'current_streak_days' => $this->calculateCurrentStreak($drinksByDate, $today),
            'max_streak_days' => $this->calculateMaxStreak($drinksByDate, $today),
            'last_drink_timestamp' => $lastEntry?->created_at?->utc()->toIso8601String(),
            'calendar_data' => $this->buildCalendarData(
                $drinksByDate,
                $yearStart,
                $yearEnd,
                $today,
                $userSince
            ),
            'period' => [
                'year' => $year,
                'min_year' => $minYear,
                'max_year' => $maxYear,
            ],
        ];
    }

    /**
     * @return array<string, array{pure_alcohol_ml: float, has_records: bool}>
     */
    private function groupEntriesByDate(Collection $entries): array
    {
        $grouped = [];

        foreach ($entries as $entry) {
            $dateKey = $entry->drink_date->format('Y-m-d');

            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [
                    'pure_alcohol_ml' => 0.0,
                    'has_records' => true,
                ];
            }

            $grouped[$dateKey]['pure_alcohol_ml'] += $entry->pure_alcohol_ml;
        }

        return $grouped;
    }

    private function calculateCurrentStreak(array $drinksByDate, Carbon $today): int
    {
        if ($drinksByDate === []) {
            return 0;
        }

        $drinkDates = array_keys($drinksByDate);
        sort($drinkDates);

        $lastDrinkDate = Carbon::parse(end($drinkDates))->startOfDay();

        if ($lastDrinkDate->greaterThanOrEqualTo($today)) {
            return 0;
        }

        return (int) $lastDrinkDate->diffInDays($today);
    }

    private function calculateMaxStreak(array $drinksByDate, Carbon $today): int
    {
        if ($drinksByDate === []) {
            return 0;
        }

        $drinkDates = array_keys($drinksByDate);
        sort($drinkDates);

        $maxStreak = 0;

        for ($index = 0; $index < count($drinkDates) - 1; $index++) {
            $currentDate = Carbon::parse($drinkDates[$index])->startOfDay();
            $nextDate = Carbon::parse($drinkDates[$index + 1])->startOfDay();
            $soberDays = (int) $currentDate->diffInDays($nextDate) - 1;
            $maxStreak = max($maxStreak, $soberDays);
        }

        $lastDrinkDate = Carbon::parse(end($drinkDates))->startOfDay();

        if ($lastDrinkDate->lessThan($today)) {
            $maxStreak = max($maxStreak, (int) $lastDrinkDate->diffInDays($today));
        }

        return $maxStreak;
    }

    /**
     * @return list<array{date: string, status: string, pure_alcohol_ml: float, sober_day_index: int}>
     */
    private function buildCalendarData(
        array $drinksByDate,
        Carbon $yearStart,
        Carbon $yearEnd,
        Carbon $today,
        Carbon $userSince
    ): array {
        $soberByDate = $this->buildSoberDayIndexes(
            $drinksByDate,
            $yearStart,
            $yearEnd,
            $today,
            $userSince
        );
        $calendarData = [];
        $cursor = $yearStart->copy();

        while ($cursor->lessThanOrEqualTo($yearEnd)) {
            $dateKey = $cursor->format('Y-m-d');
            $dayData = $drinksByDate[$dateKey] ?? null;
            $pureAlcoholMl = $dayData['pure_alcohol_ml'] ?? 0.0;

            if ($cursor->lessThan($userSince) || $cursor->greaterThan($today)) {
                $status = 'blank';
            } else {
                $status = $this->resolveDayStatus($dateKey, $drinksByDate);
            }

            $calendarData[] = [
                'date' => $dateKey,
                'status' => $status,
                'pure_alcohol_ml' => round($pureAlcoholMl, 2),
                'sober_day_index' => $status === 'blank' ? 0 : ($soberByDate[$dateKey] ?? 0),
            ];

            $cursor->addDay();
        }

        return $calendarData;
    }

    /**
     * @return array<string, int>
     */
    private function buildSoberDayIndexes(
        array $drinksByDate,
        Carbon $yearStart,
        Carbon $yearEnd,
        Carbon $today,
        Carbon $userSince
    ): array {
        $warmupStart = $yearStart->copy();

        if ($drinksByDate !== []) {
            $firstDrinkDate = Carbon::parse(min(array_keys($drinksByDate)))->startOfDay();
            if ($firstDrinkDate->lessThan($warmupStart)) {
                $warmupStart = $firstDrinkDate->copy();
            }
        }

        if ($userSince->lessThan($warmupStart)) {
            $warmupStart = $userSince->copy();
        }

        $soberByDate = [];
        $counter = 0;
        $cursor = $warmupStart->copy();
        $calculationEnd = $today->lessThan($yearEnd) ? $today->copy() : $yearEnd->copy();

        while ($cursor->lessThanOrEqualTo($calculationEnd)) {
            $dateKey = $cursor->format('Y-m-d');

            if ($cursor->lessThan($userSince)) {
                $counter = 0;
            } elseif (isset($drinksByDate[$dateKey])) {
                $counter = 0;
            } else {
                $counter++;
            }

            if ($cursor->greaterThanOrEqualTo($yearStart) && $cursor->lessThanOrEqualTo($calculationEnd)) {
                $soberByDate[$dateKey] = $counter;
            }

            $cursor->addDay();
        }

        return $soberByDate;
    }

    private function resolveDayStatus(string $dateKey, array $drinksByDate): string
    {
        if (isset($drinksByDate[$dateKey])) {
            return 'red';
        }

        $previousDate = Carbon::parse($dateKey)->subDay()->format('Y-m-d');

        if (isset($drinksByDate[$previousDate])) {
            return 'yellow';
        }

        return 'green';
    }
}
