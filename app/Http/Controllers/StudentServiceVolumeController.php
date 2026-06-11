<?php

namespace App\Http\Controllers;

use App\Models\StudentServiceVolume;

class StudentServiceVolumeController extends ModuleController
{
    protected string $modelClass = StudentServiceVolume::class;

    protected string $routeName = 'student-service-volumes';

    protected string $title = 'Student Service Volume';

    protected string $description = 'Monthly service volume of student-facing offices.';

    protected string $icon = 'bi-people';

    protected array $searchable = ['respondent_name', 'office_unit_name', 'service_types', 'remarks'];

    protected array $tableColumns = [
        'respondent_name' => 'Respondent',
        'reporting_month' => 'Month',
        'reporting_year' => 'Year',
        'office_unit_name' => 'Office/Unit',
        'student_transactions_count' => 'Transactions',
    ];

    protected array $fields = [
        ['name' => 'respondent_name', 'label' => 'Name of respondent', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-4'],
        ['name' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'rules' => ['required', 'integer', 'between:1,12'], 'col' => 'col-md-4'],
        ['name' => 'reporting_year', 'label' => 'Reporting year', 'type' => 'number', 'rules' => ['required', 'integer', 'min:2000', 'max:2100'], 'col' => 'col-md-4'],
        ['name' => 'office_unit_name', 'label' => 'Office or unit name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'col' => 'col-md-6'],
        ['name' => 'student_transactions_count', 'label' => 'Total number of student service transactions handled this month', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0'], 'col' => 'col-md-6'],
        ['name' => 'service_types', 'label' => 'Types of student services handled this month', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
        ['name' => 'remarks', 'label' => 'Remarks or notes', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'col' => 'col-12'],
    ];
}
