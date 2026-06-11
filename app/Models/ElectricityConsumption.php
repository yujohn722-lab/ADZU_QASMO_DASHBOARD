<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Model;

class ElectricityConsumption extends Model
{
    use OwnedByUser;

    public const SALVADOR_FIELDS = [
        'father_ernesto_carretero_kwh',
        'canisius_gonzaga_xavier_kwh',
        'bellarmine_campion_kwh',
        'senior_high_school_kwh',
        'sauras_kwh',
        'college_of_law_kwh',
        'jesuit_residence_kwh',
    ];

    public const KREUTZ_FIELDS = [
        'grade_school_complex_kwh',
        'junior_high_school_kwh',
    ];

    public const BUILDING_LABELS = [
        'father_ernesto_carretero_kwh' => 'Father Ernesto Carretero Building',
        'canisius_gonzaga_xavier_kwh' => 'Canisius-Gonzaga Building and Xavier Hall',
        'bellarmine_campion_kwh' => 'Bellarmine Campion Building',
        'senior_high_school_kwh' => 'Senior High School Building',
        'sauras_kwh' => 'Sauras Building',
        'college_of_law_kwh' => 'College of Law Building',
        'jesuit_residence_kwh' => 'Jesuit Residence',
        'grade_school_complex_kwh' => 'Grade School Complex',
        'junior_high_school_kwh' => 'Junior High School Building',
        'total_lantaka_kwh' => 'Lantaka Campus',
    ];

    protected $fillable = [
        'user_id',
        'respondent_name',
        'reporting_month',
        'reporting_year',
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
        return (float) ($this->total_salvador_kwh ?? 0)
            + (float) ($this->total_kreutz_kwh ?? 0)
            + (float) ($this->total_lantaka_kwh ?? 0);
    }
}
