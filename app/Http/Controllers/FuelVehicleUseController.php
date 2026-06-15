<?php

namespace App\Http\Controllers;

use App\Models\FuelVehicleUse;

class FuelVehicleUseController extends ModuleController
{
    protected string $modelClass = FuelVehicleUse::class;

    protected string $routeName = 'fuel-vehicle-uses';

    protected string $title = 'Fuel and Vehicle Use';

    protected string $description = 'Monthly fuel cost tracking for university vehicles.';

    protected string $icon = 'bi-truck';

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_month' => 'Month',
        'reporting_year' => 'Year',
        'total_fuel_cost_incurred' => 'Total Fuel Cost Incurred (PHP)',
        'remarks' => 'Remarks',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['type' => 'vehicle_table', 'label' => 'Vehicle list', 'col' => 'col-12'],
        ['name' => 'total_fuel_cost_incurred', 'label' => 'Total Fuel Cost Incurred (PHP)', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];

    protected function viewData(array $extra = []): array
    {
        return parent::viewData(array_merge([
            'vehicleTableRows' => $this->vehicleTableRows(),
        ], $extra));
    }

    private function vehicleTableRows(): array
    {
        return [
            ['vehicle_name' => 'Toyota Hiace', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Toyota Grandia', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Urvan old', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Urvan new', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'L300 WHK', 'fuel_type' => 'DIESEL', 'notes' => 'For Emergency'],
            ['vehicle_name' => 'L300 Infirmary', 'fuel_type' => 'DIESEL', 'notes' => 'For Emergency'],
            ['vehicle_name' => 'Hilux Pickup', 'fuel_type' => 'DIESEL', 'notes' => 'CCES (From West)'],
            ['vehicle_name' => 'Hyundai Bus A', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hyundai Bus B', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hilux pickup', 'fuel_type' => 'DIESEL', 'notes' => ''],
        ];
    }
}
