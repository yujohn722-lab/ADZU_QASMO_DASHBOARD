<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;

class SolarPerformance extends Model
{
    use OwnedByUser;

    protected $fillable = [
        'user_id',
        'respondent_name',
        'reporting_month',
        'reporting_year',
        'solar_panel_id',
        'monthly_solar_energy_kwh',
        'estimated_savings',
        'remarks',
    ];

    protected $casts = [
        'reporting_month' => 'integer',
        'reporting_year' => 'integer',
        'monthly_solar_energy_kwh' => 'decimal:2',
        'estimated_savings' => 'decimal:2',
    ];
}
