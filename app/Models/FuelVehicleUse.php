<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelVehicleUse extends Model
{
    use OwnedByUser;

    protected $fillable = [
        'user_id',
        'respondent_name',
        'reporting_month',
        'reporting_year',
        'total_fuel_cost_incurred',
        'total_fuel_liters_loaded',
        'remarks',
    ];

    protected $casts = [
        'reporting_month' => 'integer',
        'reporting_year' => 'integer',
        'total_fuel_cost_incurred' => 'decimal:2',
        'total_fuel_liters_loaded' => 'decimal:2',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(FuelVehicleUseEntry::class);
    }
}
