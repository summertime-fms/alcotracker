<?php

namespace App\Enums;

enum AlcoholType: string
{
    case BEER = 'beer';
    case VODKA = 'vodka';
    case WINE_DRY = 'wine_dry';
    case WINE_SEMI_SWEET = 'wine_semi_sweet';
    case CHAMPAGNE = 'champagne';
    case RUM = 'rum';
    case WHISKY = 'whisky';
    case CHACHA = 'chacha';
    case MOONSHINE = 'moonshine';
    case CIDER = 'cider';
    case TEQUILA = 'tequila';
    case LIQUEUR = 'liqueur';
    case GIN = 'gin';

    /**
     * Получить локализованное название типа алкоголя
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::BEER => 'Пиво',
            self::VODKA => 'Водка',
            self::WINE_DRY => 'Вино сухое',
            self::WINE_SEMI_SWEET => 'Вино полусладкое',
            self::CHAMPAGNE => 'Шампанское',
            self::RUM => 'Ром',
            self::WHISKY => 'Виски',
            self::CHACHA => 'Чача',
            self::MOONSHINE => 'Самогон',
            self::CIDER => 'Сидр',
            self::TEQUILA => 'Текила',
            self::LIQUEUR => 'Ликёр',
            self::GIN => 'Джин',
        };
    }

    /**
     * Получить все типы алкоголя с их названиями
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Получить все значения enum
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Получить процент содержания алкоголя (ABV - Alcohol By Volume)
     *
     * @return float
     */
    public function alcoholPercentage(): float
    {
        return match ($this) {
            self::BEER => 5.0,              // Пиво: 4-6%, среднее 5%
            self::CIDER => 5.0,             // Сидр: 4-6%, среднее 5%
            self::WINE_DRY => 12.5,         // Вино сухое: 11-14%, среднее 12.5%
            self::WINE_SEMI_SWEET => 11.0,  // Вино полусладкое: 9-13%, среднее 11%
            self::CHAMPAGNE => 12.0,        // Шампанское: 11-13%, среднее 12%
            self::LIQUEUR => 20.0,          // Ликёр: 15-25%, среднее 20%
            self::VODKA => 40.0,            // Водка: обычно 40%
            self::WHISKY => 40.0,           // Виски: 40-46%, среднее 40%
            self::RUM => 40.0,              // Ром: 37.5-50%, среднее 40%
            self::GIN => 40.0,              // Джин: 37.5-47%, среднее 40%
            self::TEQUILA => 40.0,          // Текила: 35-55%, среднее 40%
            self::CHACHA => 50.0,           // Чача: 40-70%, среднее 50%
            self::MOONSHINE => 45.0,        // Самогон: 40-60%, среднее 45%
        };
    }

    /**
     * Рассчитать количество чистого спирта в миллилитрах
     *
     * @param int $volumeMl - объем напитка в мл
     * @return float - количество чистого спирта в мл
     */
    public function calculatePureAlcohol(int $volumeMl): float
    {
        return round($volumeMl * ($this->alcoholPercentage() / 100), 2);
    }

    /**
     * Получить информацию о напитке с крепостью
     *
     * @return string
     */
    public function labelWithPercentage(): string
    {
        return $this->label() . ' (' . $this->alcoholPercentage() . '%)';
    }
}
