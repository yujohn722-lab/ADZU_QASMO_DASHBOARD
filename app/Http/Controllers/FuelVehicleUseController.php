<?php

namespace App\Http\Controllers;

use App\Models\FuelVehicleUse;

class FuelVehicleUseController extends ModuleController
{
    protected string $modelClass = FuelVehicleUse::class;

    protected string $routeName = 'fuel-vehicle-uses';

    protected string $title = 'Fuel and Vehicle Use';

    protected string $description = 'Placeholder module for future fuel and vehicle inputs.';

    protected string $icon = 'bi-truck';

    protected ?string $placeholderMessage = 'Fuel and Vehicle Use inputs will be added later.';

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_month' => 'Month',
        'reporting_year' => 'Year',
        'remarks' => 'Remarks',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];
}
