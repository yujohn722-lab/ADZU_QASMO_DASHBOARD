<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;

class FuelVehicleUse extends Model
{
    use OwnedByUser;

    protected $fillable = [
        'user_id',
        'respondent_name',
        'reporting_month',
        'reporting_year',
        'remarks',
    ];

    protected $casts = [
        'reporting_month' => 'integer',
        'reporting_year' => 'integer',
    ];
}
