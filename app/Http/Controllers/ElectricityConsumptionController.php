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

    protected string $description = 'Monthly electricity consumption per campus.';

    protected string $icon = 'bi-lightning-charge';

    protected array $searchable = ['respondent_name', 'remarks'];

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_month' => 'Month',
        'reporting_year' => 'Year',
        'main_kwh' => 'Main kWh',
        'fws_kwh' => 'FWS kWh',
        'total_kreutz_kwh' => 'Kreutz kWh',
        'total_lantaka_kwh' => 'Lantaka kWh',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'main_kwh', 'label' => 'Main Campus kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-3'],
        ['name' => 'fws_kwh', 'label' => 'FWS Campus kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-3'],
        ['name' => 'total_kreutz_kwh', 'label' => 'Kreutz Campus kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-3'],
        ['name' => 'total_lantaka_kwh', 'label' => 'Lantaka Campus kWh', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-3'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];

    protected function beforeSave(array $data, Request $request, ?Model $record): array
    {
        $data['total_salvador_kwh'] = $data['fws_kwh'] ?? 0;

        return $data;
    }
}
