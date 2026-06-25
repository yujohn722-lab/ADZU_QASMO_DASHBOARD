<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;

class ElectricityConsumption extends Model
{
    use OwnedByUser;

    public const CAMPUS_FIELDS = [
        'main_kwh' => 'Main',
        'fws_kwh' => 'FWS',
        'total_kreutz_kwh' => 'Kreutz',
        'total_lantaka_kwh' => 'Lantaka',
    ];

    protected $fillable = [
        'user_id',
        'respondent_name',
        'reporting_month',
        'reporting_year',
        'main_kwh',
        'fws_kwh',
        'father_ernesto_carretero_kwh',
        'canisius_gonzaga_xavier_kwh',
        'bellarmine_campion_kwh',
        'senior_high_school_kwh',
        'sauras_kwh',
        'college_of_law_kwh',
        'jesuit_residence_kwh',
        'total_salvador_kwh',
        'grade_school_complex_kwh',
        'junior_high_school_kwh',
        'total_kreutz_kwh',
        'total_lantaka_kwh',
        'remarks',
    ];

    protected $casts = [
        'reporting_month' => 'integer',
        'reporting_year' => 'integer',
        'main_kwh' => 'decimal:2',
        'fws_kwh' => 'decimal:2',
        'father_ernesto_carretero_kwh' => 'decimal:2',
        'canisius_gonzaga_xavier_kwh' => 'decimal:2',
        'bellarmine_campion_kwh' => 'decimal:2',
        'senior_high_school_kwh' => 'decimal:2',
        'sauras_kwh' => 'decimal:2',
        'college_of_law_kwh' => 'decimal:2',
        'jesuit_residence_kwh' => 'decimal:2',
        'total_salvador_kwh' => 'decimal:2',
        'grade_school_complex_kwh' => 'decimal:2',
        'junior_high_school_kwh' => 'decimal:2',
        'total_kreutz_kwh' => 'decimal:2',
        'total_lantaka_kwh' => 'decimal:2',
    ];

    public function totalKwh(): float
    {
        return (float) ($this->main_kwh ?? 0)
            + (float) ($this->fws_kwh ?? $this->total_salvador_kwh ?? 0)
            + (float) ($this->total_kreutz_kwh ?? 0)
            + (float) ($this->total_lantaka_kwh ?? 0);
    }

    public function campusKwh(string $field): float
    {
        if ($field === 'fws_kwh') {
            return (float) ($this->fws_kwh ?? $this->total_salvador_kwh ?? 0);
        }

        return (float) ($this->{$field} ?? 0);
    }

    public function getFwsKwhAttribute($value): ?string
    {
        return $value ?? $this->attributes['total_salvador_kwh'] ?? null;
    }
}
