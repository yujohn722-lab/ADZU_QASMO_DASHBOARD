<?php

namespace App\Http\Controllers;

use App\Models\FuelVehicle;
use App\Models\FuelVehicleUse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        'total_fuel_cost_incurred' => 'Total Fuel Cost for the Month (PHP)',
        'total_fuel_liters_loaded' => 'Total Liters Pumped for the Month',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'total_fuel_cost_incurred', 'label' => 'Total Fuel Cost for the Month (PHP)', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'total_fuel_liters_loaded', 'label' => 'Total Liters Pumped for the Month', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0'], 'col' => 'col-md-6'],
    ];

    public function storeVehicle(Request $request): RedirectResponse
    {
        FuelVehicle::create($request->validate([
            'vehicle_name' => ['required', 'string', 'max:255'],
            'plate_number' => ['nullable', 'string', 'max:50'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]));

        return back()->with('status', 'Vehicle added.');
    }

    public function toggleVehicle(FuelVehicle $vehicle): RedirectResponse
    {
        $vehicle->update(['is_active' => ! $vehicle->is_active]);

        return back()->with('status', $vehicle->vehicle_name.' marked '.($vehicle->is_active ? 'active.' : 'inactive.'));
    }

    public function destroyVehicle(FuelVehicle $vehicle): RedirectResponse
    {
        if ($vehicle->fuelUseEntries()->exists()) {
            $vehicle->update(['is_active' => false]);

            return back()->with('status', $vehicle->vehicle_name.' has report history, so it was marked inactive instead of deleted.');
        }

        $vehicle->delete();

        return back()->with('status', 'Vehicle deleted.');
    }

    protected function viewData(array $extra = []): array
    {
        $this->ensureDefaultVehicles();

        return parent::viewData(array_merge([
            'vehicleTableRows' => FuelVehicle::query()->orderByDesc('is_active')->orderBy('vehicle_name')->get(),
        ], $extra));
    }

    private function ensureDefaultVehicles(): void
    {
        if (FuelVehicle::query()->exists()) {
            return;
        }

        collect($this->defaultVehicles())->each(fn (array $vehicle) => FuelVehicle::create($vehicle));
    }

    private function defaultVehicles(): array
    {
        return [
            ['vehicle_name' => 'Dump Truck 1', 'plate_number' => 'JDZ 879', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Dump Truck 2', 'plate_number' => 'KBF 9632', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Grandia', 'plate_number' => 'JDO 4115', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hi-Ace 130', 'plate_number' => 'JEM 130', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hilux', 'plate_number' => 'PII 490', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hino Bus 1', 'plate_number' => 'KAR 5155', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hino Bus 2', 'plate_number' => 'KAR 5157', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hino Bus 2', 'plate_number' => 'KAR 5155', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hyundai Bus 1', 'plate_number' => 'URI 174', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Hyundai Bus 2', 'plate_number' => 'AJA 7163', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Innova', 'plate_number' => 'JAA 12005', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'L300 547', 'plate_number' => 'JCP 547', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Traviz 1', 'plate_number' => 'KAP 9140', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Traviz 2', 'plate_number' => 'KBD 1963', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Urvan 1', 'plate_number' => 'AAW 4847', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Urvan 2', 'plate_number' => 'AFA 5485', 'fuel_type' => 'DIESEL', 'notes' => ''],
            ['vehicle_name' => 'Wave 100', 'plate_number' => 'JR 8281', 'fuel_type' => 'GASOLINE', 'notes' => ''],
        ];
    }
}
