<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;

class StudentServiceVolume extends Model
{
    use OwnedByUser;

    protected $fillable = [
        'user_id',
        'respondent_name',
        'reporting_month',
        'reporting_year',
        'office_unit_name',
        'student_transactions_count',
        'service_types',
        'remarks',
    ];

    protected $casts = [
        'reporting_month' => 'integer',
        'reporting_year' => 'integer',
        'student_transactions_count' => 'integer',
    ];
}
