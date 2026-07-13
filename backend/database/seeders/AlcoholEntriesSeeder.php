<?php

namespace Database\Seeders;

use App\Enums\AlcoholType;
use App\Models\AlcoholEntry;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AlcoholEntriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1; // Михаил Данюшин
        $year = 2025;

        // Типы алкоголя с их типичными объемами
        $alcoholTypes = [
            AlcoholType::BEER->value => [300, 500, 750, 1000],
            AlcoholType::VODKA->value => [50, 100, 150, 200],
            AlcoholType::WINE_DRY->value => [150, 200, 250, 300],
            AlcoholType::WINE_SEMI_SWEET->value => [150, 200, 250, 300],
            AlcoholType::CHAMPAGNE->value => [150, 200, 250],
            AlcoholType::RUM->value => [50, 75, 100],
            AlcoholType::WHISKY->value => [50, 75, 100, 150],
            AlcoholType::CHACHA->value => [50, 100, 150],
            AlcoholType::MOONSHINE->value => [50, 100, 150, 200],
            AlcoholType::CIDER->value => [330, 500, 750],
            AlcoholType::TEQUILA->value => [50, 75, 100],
            AlcoholType::LIQUEUR->value => [50, 75, 100],
            AlcoholType::GIN->value => [50, 75, 100],
        ];

        $comments = [
            'С друзьями',
            'После работы',
            'Вечеринка',
            'День рождения',
            'Выходные',
            'За ужином',
            'Праздник',
            'Встреча с коллегами',
            null,
            null,
            null, // больше null чтобы были записи без комментариев
        ];

        $entries = [];

        // Генерируем 100 записей
        for ($i = 0; $i < 100; $i++) {
            // Случайный тип алкоголя
            $alcoholType = array_rand($alcoholTypes);

            // Случайный объем для этого типа
            $amount = $alcoholTypes[$alcoholType][array_rand($alcoholTypes[$alcoholType])];

            // Случайная дата в 2025 году (с января по ноябрь, так как сейчас ноябрь)
            $month = rand(1, 11);
            $day = rand(1, Carbon::create($year, $month)->daysInMonth);
            $drinkDate = Carbon::create($year, $month, $day);

            // Случайное время в течение дня
            $drinkDate->setTime(rand(12, 23), rand(0, 59));

            $entries[] = [
                'user_id' => $userId,
                'alcohol_type' => $alcoholType,
                'amount_ml' => $amount,
                'drink_date' => $drinkDate->toDateString(),
                'comment' => $comments[array_rand($comments)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Сортируем по дате для более логичного отображения
        usort($entries, function($a, $b) {
            return strcmp($a['drink_date'], $b['drink_date']);
        });

        // Вставляем все записи
        AlcoholEntry::insert($entries);

        $this->command->info('✅ Создано 100 тестовых записей об употреблении алкоголя для пользователя #1');
    }
}
