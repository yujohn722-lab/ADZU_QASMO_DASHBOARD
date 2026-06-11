<?php

namespace App\Http\Controllers;

use App\Models\EstimatedSaving;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class EstimatedSavingController extends ModuleController
{
    protected string $modelClass = EstimatedSaving::class;

    protected string $routeName = 'estimated-savings';

    protected string $title = 'Estimated Savings';

    protected string $description = 'Yearly estimated savings from reduced travel, utilities, and activities.';

    protected string $icon = 'bi-cash-coin';

    protected array $searchable = ['respondent_name', 'office_unit_name', 'savings_areas', 'remarks'];

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_year' => 'Year',
        'office_unit_name' => 'Office/Unit',
        'reduced_travel_savings' => 'Travel',
        'reduced_utilities_savings' => 'Utilities',
        'reduced_activities_savings' => 'Activities',
        'total_estimated_savings' => 'Total',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'office_unit_name', 'label' => 'University office or unit covered by the yearly savings report', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'savings_areas', 'label' => 'Areas where savings were observed or expected', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
        ['name' => 'reduced_travel_savings', 'label' => 'Estimated savings from reduced travel', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-3'],
        ['name' => 'reduced_utilities_savings', 'label' => 'Estimated savings from reduced utilities', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-3'],
        ['name' => 'reduced_activities_savings', 'label' => 'Estimated savings from reduced activities or events', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-3'],
        ['name' => 'total_estimated_savings', 'label' => 'Total estimated savings for the year', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0'], 'col' => 'col-md-3'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];

    protected function beforeSave(array $data, Request $request, ?Model $record): array
    {
        if (empty($data['total_estimated_savings'])) {
            $data['total_estimated_savings'] = $this->sumFields($data, [
                'reduced_travel_savings',
                'reduced_utilities_savings',
                'reduced_activities_savings',
            ]);
        }

        return $data;
    }
}
