<?php

namespace App\Http\Controllers;

use App\Models\SolarPerformance;

class SolarPerformanceController extends ModuleController
{
    protected string $modelClass = SolarPerformance::class;

    protected string $routeName = 'solar-performances';

    protected string $title = 'Solar Savings and Performance';

    protected string $description = 'Solar panel generation, monthly kWh output, and estimated savings.';

    protected string $icon = 'bi-sun';

    protected array $searchable = ['respondent_name', 'building_name', 'remarks'];

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_month' => 'Month',
        'reporting_year' => 'Year',
        'building_name' => 'Building',
        'monthly_solar_energy_kwh' => 'Generated kWh',
        'estimated_savings' => 'Savings',
    ];

    protected array $buildings = [
        'Ernesto Carretero (FEC) Building' => 'Ernesto Carretero (FEC) Building',
        'GS Admin' => 'GS Admin',
        'Jose Maria Rosauro SJ Hall' => 'Jose Maria Rosauro SJ Hall',
        'Xavier Hall' => 'Xavier Hall',
        'College Building' => 'College Building',
        'Jesuit Residence' => 'Jesuit Residence',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'building_name', 'label' => 'Building name', 'type' => 'select', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'monthly_solar_energy_kwh', 'label' => 'Total Monthly Solar Energy Generated in kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'estimated_savings', 'label' => 'Estimated savings from solar generation', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];

    protected function viewData(array $extra = []): array
    {
        return parent::viewData(array_merge($extra, [
            'buildings' => $this->buildings,
        ]));
    }
}
