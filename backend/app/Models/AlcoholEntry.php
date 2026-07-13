<?php

namespace App\Models;

use App\Enums\AlcoholType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlcoholEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'alcohol_type',
        'amount_ml',
        'drink_date',
        'comment',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alcohol_type' => AlcoholType::class,
            'drink_date' => 'date',
            'amount_ml' => 'integer',
        ];
    }

    /**
     * Получить пользователя, которому принадлежит запись
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Рассчитать количество чистого спирта в миллилитрах
     *
     * @return float
     */
    public function getPureAlcoholMlAttribute(): float
    {
        return $this->alcohol_type->calculatePureAlcohol($this->amount_ml);
    }

    /**
     * Получить процент крепости напитка
     *
     * @return float
     */
    public function getAlcoholPercentageAttribute(): float
    {
        return $this->alcohol_type->alcoholPercentage();
    }
}
