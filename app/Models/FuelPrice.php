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
        'petron_ado',
        'petron_diesel_max',
        'petron_turbo_diesel',
        'caltex_diesel',
    ];

    public const GASOLINE_FIELDS = [
        'shell_fuel_save_regular',
        'shell_v_power_premium',
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
        'petron_ado',
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
        'petron_ado' => 'decimal:2',
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

    public function highestGasolineInfo(): ?array
    {
        $fieldLabels = $this->getFuelFieldLabels();
        $highest = null;
        $highestField = null;

        foreach (self::GASOLINE_FIELDS as $field) {
            $value = $this->{$field};
            if ($value !== null && ($highest === null || $value > $highest)) {
                $highest = $value;
                $highestField = $field;
            }
        }

        return $highest !== null ? ['brand' => $fieldLabels[$highestField], 'price' => $highest] : null;
    }

    public function lowestGasolineInfo(): ?array
    {
        $fieldLabels = $this->getFuelFieldLabels();
        $lowest = null;
        $lowestField = null;

        foreach (self::GASOLINE_FIELDS as $field) {
            $value = $this->{$field};
            if ($value !== null && ($lowest === null || $value < $lowest)) {
                $lowest = $value;
                $lowestField = $field;
            }
        }

        return $lowest !== null ? ['brand' => $fieldLabels[$lowestField], 'price' => $lowest] : null;
    }

    public function highestDieselInfo(): ?array
    {
        $fieldLabels = $this->getFuelFieldLabels();
        $highest = null;
        $highestField = null;

        foreach (self::DIESEL_FIELDS as $field) {
            $value = $this->{$field};
            if ($value !== null && ($highest === null || $value > $highest)) {
                $highest = $value;
                $highestField = $field;
            }
        }

        return $highest !== null ? ['brand' => $fieldLabels[$highestField], 'price' => $highest] : null;
    }

    public function lowestDieselInfo(): ?array
    {
        $fieldLabels = $this->getFuelFieldLabels();
        $lowest = null;
        $lowestField = null;

        foreach (self::DIESEL_FIELDS as $field) {
            $value = $this->{$field};
            if ($value !== null && ($lowest === null || $value < $lowest)) {
                $lowest = $value;
                $lowestField = $field;
            }
        }

        return $lowest !== null ? ['brand' => $fieldLabels[$lowestField], 'price' => $lowest] : null;
    }

    private function getFuelFieldLabels(): array
    {
        return [
            'shell_fuel_save_diesel' => 'Shell Fuel Save Diesel',
            'shell_v_power_diesel' => 'Shell VPower Diesel',
            'shell_fuel_save_regular' => 'Shell Fuel Save Gasoline',
            'shell_v_power_premium' => 'Shell VPower Gasoline',
            'petron_ado' => 'Petron ADO',
            'petron_diesel_max' => 'Petron Diesel Max',
            'petron_turbo_diesel' => 'Petron Turbo Diesel',
            'petron_xtra_advance_regular' => 'Petron XTRA Gasoline',
            'petron_xcs_premium' => 'Petron XCS',
            'caltex_silver_regular' => 'Caltex Silver',
            'caltex_platinum_premium' => 'Caltex Platinum',
            'caltex_diesel' => 'Caltex Diesel',
        ];
    }

    private function averageFields(array $fields): ?float
    {
        $values = collect($fields)
            ->map(fn (string $field) => $this->{$field})
            ->filter(fn ($value) => $value !== null);

        return $values->isEmpty() ? null : round((float) $values->avg(), 2);
    }
}
