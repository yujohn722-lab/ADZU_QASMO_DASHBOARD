<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;

class EstimatedSaving extends Model
{
    use OwnedByUser;

    protected $fillable = [
        'user_id',
        'respondent_name',
        'reporting_year',
        'office_unit_name',
        'savings_areas',
        'reduced_travel_savings',
        'reduced_utilities_savings',
        'reduced_activities_savings',
        'total_estimated_savings',
        'remarks',
    ];

    protected $casts = [
        'reporting_year' => 'integer',
        'reduced_travel_savings' => 'decimal:2',
        'reduced_utilities_savings' => 'decimal:2',
        'reduced_activities_savings' => 'decimal:2',
        'total_estimated_savings' => 'decimal:2',
    ];
}
