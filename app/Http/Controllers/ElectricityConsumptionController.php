<?php

namespace App\Http\Controllers;

use App\Models\ElectricityConsumption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ElectricityConsumptionController extends ModuleController
{
    protected string $modelClass = ElectricityConsumption::class;

    protected string $routeName = 'electricity-consumptions';

    protected string $title = 'Electricity Consumption';

    protected string $description = 'Monthly electricity consumption per campus and major building.';

    protected string $icon = 'bi-lightning-charge';

    protected array $searchable = ['respondent_name', 'remarks'];

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_month' => 'Month',
        'reporting_year' => 'Year',
        'total_salvador_kwh' => 'Salvador kWh',
        'total_kreutz_kwh' => 'Kreutz kWh',
        'total_lantaka_kwh' => 'Lantaka kWh',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'father_ernesto_carretero_kwh', 'label' => 'Father Ernesto Carretero Building kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'canisius_gonzaga_xavier_kwh', 'label' => 'Canisius-Gonzaga Building and Xavier Hall kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'bellarmine_campion_kwh', 'label' => 'Bellarmine Campion Building kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'senior_high_school_kwh', 'label' => 'Senior High School Building kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'sauras_kwh', 'label' => 'Sauras Building kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'college_of_law_kwh', 'label' => 'College of Law Building kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'jesuit_residence_kwh', 'label' => 'Jesuit Residence kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'total_salvador_kwh', 'label' => 'Total Salvador Campus kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'grade_school_complex_kwh', 'label' => 'Grade School Complex kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'junior_high_school_kwh', 'label' => 'Junior High School Building kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'total_kreutz_kwh', 'label' => 'Total Kreutz Campus kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'total_lantaka_kwh', 'label' => 'Total Lantaka Campus kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];

    protected function beforeSave(array $data, Request $request, ?Model $record): array
    {
        if (empty($data['total_salvador_kwh'])) {
            $data['total_salvador_kwh'] = $this->sumFields($data, ElectricityConsumption::SALVADOR_FIELDS);
        }

        if (empty($data['total_kreutz_kwh'])) {
            $data['total_kreutz_kwh'] = $this->sumFields($data, ElectricityConsumption::KREUTZ_FIELDS);
        }

        return $data;
    }
}
