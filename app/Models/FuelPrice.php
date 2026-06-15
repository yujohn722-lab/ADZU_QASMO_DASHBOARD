<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;

class FuelPrice extends Model
{
    use OwnedByUser;

    public const DIESEL_FIELDS = [
        'shell_fuel_save_diesel',
        'shell_v_power_diesel',
        'petron_diesel_max',
        'petron_turbo_diesel',
        'caltex_diesel',
    ];

    public const GASOLINE_FIELDS = [
        'shell_fuel_save_regular',
        'shell_v_power_premium',
        'shell_v_power_premium_sport',
        'petron_xtra_advance_regular',
        'petron_xcs_premium',
        'caltex_silver_regular',
        'caltex_platinum_premium',
    ];

    protected $fillable = [
        'user_id',
        'respondent_name',
        'reporting_month',
        'reporting_year',
        'week_number',
        'shell_fuel_save_diesel',
        'shell_v_power_diesel',
        'shell_fuel_save_regular',
        'shell_v_power_premium',
        'shell_v_power_premium_sport',
        'petron_diesel_max',
        'petron_turbo_diesel',
        'petron_xtra_advance_regular',
        'petron_xcs_premium',
        'caltex_silver_regular',
        'caltex_platinum_premium',
        'caltex_diesel',
        'remarks',
    ];

    protected $casts = [
        'reporting_month' => 'integer',
        'reporting_year' => 'integer',
        'week_number' => 'integer',
        'shell_fuel_save_diesel' => 'decimal:2',
        'shell_v_power_diesel' => 'decimal:2',
        'shell_fuel_save_regular' => 'decimal:2',
        'shell_v_power_premium' => 'decimal:2',
        'shell_v_power_premium_sport' => 'decimal:2',
        'petron_diesel_max' => 'decimal:2',
        'petron_turbo_diesel' => 'decimal:2',
        'petron_xtra_advance_regular' => 'decimal:2',
        'petron_xcs_premium' => 'decimal:2',
        'caltex_silver_regular' => 'decimal:2',
        'caltex_platinum_premium' => 'decimal:2',
        'caltex_diesel' => 'decimal:2',
    ];

    public function averageDieselPrice(): ?float
    {
        return $this->averageFields(self::DIESEL_FIELDS);
    }

    public function averageGasolinePrice(): ?float
    {
        return $this->averageFields(self::GASOLINE_FIELDS);
    }

    public function highestPrice(): ?float
    {
        $values = collect(array_merge(self::DIESEL_FIELDS, self::GASOLINE_FIELDS))
            ->map(fn (string $field) => $this->{$field})
            ->filter(fn ($value) => $value !== null);

        return $values->isEmpty() ? null : (float) $values->max();
    }

    public function lowestPrice(): ?float
    {
        $values = collect(array_merge(self::DIESEL_FIELDS, self::GASOLINE_FIELDS))
            ->map(fn (string $field) => $this->{$field})
            ->filter(fn ($value) => $value !== null);

        return $values->isEmpty() ? null : (float) $values->min();
    }

    private function averageFields(array $fields): ?float
    {
        $values = collect($fields)
            ->map(fn (string $field) => $this->{$field})
            ->filter(fn ($value) => $value !== null);

        return $values->isEmpty() ? null : round((float) $values->avg(), 2);
    }
}
