<?php

namespace App\Http\Controllers;

use App\Models\FuelPrice;

class FuelPriceController extends ModuleController
{
    protected string $modelClass = FuelPrice::class;

    protected string $routeName = 'fuel-prices';

    protected string $title = 'Weekly Fuel Prices';

    protected string $description = 'Weekly diesel and gasoline price monitoring in Zamboanga.';

    protected string $icon = 'bi-fuel-pump';

    protected array $searchable = ['respondent_name', 'remarks'];

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_month' => 'Month',
        'reporting_year' => 'Year',
        'week_number' => 'Week',
        'shell_fuel_save_diesel' => 'Shell Fuel Save Diesel',
        'shell_v_power_diesel' => 'Shell VPower Diesel',
        'shell_fuel_save_regular' => 'Shell Fuel Save Gasoline',
        'shell_v_power_premium' => 'Shell VPower Gasoline',
        'petron_xcs_premium' => 'Petron XCS',
        'petron_xtra_advance_regular' => 'Petron XTRA',
        'petron_ado' => 'Petron ADO',
        'petron_turbo_diesel' => 'Petron PTD',
        'petron_diesel_max' => 'Petron Diesel Max',
        'caltex_diesel' => 'Caltex Diesel',
        'caltex_platinum_premium' => 'Caltex Platinum',
        'caltex_silver_regular' => 'Caltex Silver',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'week_number', 'label' => 'Week number', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:53'], 'col' => 'col-md-4'],
        ['name' => 'shell_fuel_save_diesel', 'label' => 'Shell Fuel Save Diesel', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'shell_v_power_diesel', 'label' => 'Shell VPower Diesel', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'shell_fuel_save_regular', 'label' => 'Shell Fuel Save Gasoline', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'shell_v_power_premium', 'label' => 'Shell VPower Gasoline', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'petron_xcs_premium', 'label' => 'Petron XCS', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'petron_xtra_advance_regular', 'label' => 'Petron XTRA', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'petron_ado', 'label' => 'Petron ADO', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'petron_turbo_diesel', 'label' => 'Petron PTD', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'petron_diesel_max', 'label' => 'Petron Diesel Max', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'caltex_diesel', 'label' => 'Caltex Diesel', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'caltex_platinum_premium', 'label' => 'Caltex Platinum', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'caltex_silver_regular', 'label' => 'Caltex Silver', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-4'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];
}
