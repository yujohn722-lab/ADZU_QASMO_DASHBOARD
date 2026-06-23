<?php

namespace App\Http\Controllers;

use App\Models\ElectricityConsumption;
use App\Models\EstimatedSaving;
use App\Models\FuelPrice;
use App\Models\FuelVehicleUse;
use App\Models\ReportLog;
use App\Models\SolarPerformance;
use App\Models\StudentServiceVolume;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $moduleKey = $this->validatedModuleKey($request);
        $module = $moduleKey === 'all' ? null : $this->modules()[$moduleKey];
        $records = $module ? $this->filteredQuery($request, $module)->latest()->limit(200)->get() : collect();

        return view('reports.index', [
            'modules' => $this->modules(),
            'selectedModuleKey' => $moduleKey,
            'selectedModule' => $module,
            'records' => $records,
            'summary' => $this->summary($request),
            'narrative' => $this->narrative($request, $moduleKey),
            'months' => $this->months(),
            'chart' => $this->chartFor($moduleKey, $records, $request),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $moduleKey = $this->validatedModuleKey($request);
        $module = $moduleKey === 'all' ? null : $this->modules()[$moduleKey];
        $records = $module ? $this->filteredQuery($request, $module)->latest()->get() : collect();
        $summary = $this->summary($request);

        ReportLog::create([
            'user_id' => $request->user()->id,
            'module' => $moduleKey,
            'filters' => $request->only(['reporting_month', 'reporting_year', 'campus', 'office_unit_name', 'respondent_name']),
            'export_type' => 'csv',
            'generated_at' => now(),
        ]);

        $filename = 'energy-crisis-report-'.$moduleKey.'-'.now()->format('Ymd-His').'.csv';

        return Response::streamDownload(function () use ($module, $records, $summary) {
            $output = fopen('php://output', 'w');

            if (! $module) {
                fputcsv($output, ['Metric', 'Value']);
                foreach ($summary as $label => $value) {
                    fputcsv($output, [$label, $value]);
                }
                fclose($output);
                return;
            }

            fputcsv($output, array_values($module['columns']));
            foreach ($records as $record) {
                fputcsv($output, collect(array_keys($module['columns']))->map(fn (string $field) => $record->{$field})->all());
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function filteredQuery(Request $request, array $module): Builder
    {
        $query = $module['model']::query()->visibleTo($request->user());
        $available = array_keys($module['columns']);

        foreach (['reporting_month', 'reporting_year', 'respondent_name', 'office_unit_name'] as $filter) {
            if ($request->filled($filter) && in_array($filter, $available, true)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('campus') && $module['model'] === ElectricityConsumption::class) {
            $field = match ($request->input('campus')) {
                'Salvador' => 'total_salvador_kwh',
                'Kreutz' => 'total_kreutz_kwh',
                'Lantaka' => 'total_lantaka_kwh',
                default => null,
            };

            if ($field) {
                $query->where($field, '>', 0);
            }
        }

        return $query;
    }

    private function summary(Request $request): array
    {
        $user = $request->user();
        $electricity = $this->filterCollection($request, ElectricityConsumption::visibleTo($user)->get());
        $fuel = FuelPrice::visibleTo($user)
            ->when($request->filled('reporting_month'), fn ($query) => $query->where('reporting_month', $request->input('reporting_month')))
            ->when($request->filled('reporting_year'), fn ($query) => $query->where('reporting_year', $request->input('reporting_year')))
            ->orderByDesc('reporting_year')
            ->orderByDesc('reporting_month')
            ->orderByDesc('week_number')
            ->first();
        $solar = $this->filterCollection($request, SolarPerformance::visibleTo($user)->get());
        $services = $this->filterCollection($request, StudentServiceVolume::visibleTo($user)->get());
        $fuelVehicles = $this->filterCollection($request, FuelVehicleUse::visibleTo($user)->get());
        $savings = EstimatedSaving::visibleTo($user)
            ->when($request->filled('reporting_year'), fn ($query) => $query->where('reporting_year', $request->input('reporting_year')))
            ->when($request->filled('office_unit_name'), fn ($query) => $query->where('office_unit_name', $request->input('office_unit_name')))
            ->when($request->filled('respondent_name'), fn ($query) => $query->where('respondent_name', $request->input('respondent_name')))
            ->get();

        return [
            'Total electricity consumption (kWh)' => round($electricity->sum(fn ($record) => $record->totalKwh()), 2),
            'Latest average diesel price' => $fuel?->averageDieselPrice() ?? 0,
            'Latest average gasoline price' => $fuel?->averageGasolinePrice() ?? 0,
            'Total fuel cost incurred (PHP)' => round($fuelVehicles->sum(fn ($record) => (float) $record->total_fuel_cost_incurred), 2),
            'Total solar energy generated (kWh)' => round($solar->sum('monthly_solar_energy_kwh'), 2),
            'Estimated solar savings' => round($solar->sum('estimated_savings'), 2),
            'Student service transactions' => (int) $services->sum('student_transactions_count'),
            'Total estimated yearly savings' => round($savings->sum('total_estimated_savings'), 2),
        ];
    }

    private function filterCollection(Request $request, Collection $records): Collection
    {
        return $records
            ->when($request->filled('reporting_month'), fn (Collection $rows) => $rows->where('reporting_month', (int) $request->input('reporting_month')))
            ->when($request->filled('reporting_year'), fn (Collection $rows) => $rows->where('reporting_year', (int) $request->input('reporting_year')))
            ->when($request->filled('office_unit_name'), fn (Collection $rows) => $rows->where('office_unit_name', $request->input('office_unit_name')))
            ->when($request->filled('respondent_name'), fn (Collection $rows) => $rows->where('respondent_name', $request->input('respondent_name')));
    }

    private function chartFor(string $moduleKey, Collection $records, Request $request): array
    {
        return match ($moduleKey) {
            'fuel-prices' => [
                'type' => 'line',
                'labels' => $records->sortBy(fn ($record) => ($record->reporting_year * 10000) + ((int) $record->reporting_month * 100) + $record->week_number)
                    ->map(fn ($record) => $record->reporting_year.' '.$this->shortMonthName((int) $record->reporting_month).' W'.$record->week_number)
                    ->values(),
                'datasets' => [
                    ['label' => 'Diesel average', 'data' => $records->sortBy(fn ($record) => ($record->reporting_year * 10000) + ((int) $record->reporting_month * 100) + $record->week_number)->map(fn ($record) => $record->averageDieselPrice())->values()],
                    ['label' => 'Gasoline average', 'data' => $records->sortBy(fn ($record) => ($record->reporting_year * 10000) + ((int) $record->reporting_month * 100) + $record->week_number)->map(fn ($record) => $record->averageGasolinePrice())->values()],
                ],
            ],
            'fuel-vehicle-uses' => [
                'type' => 'bar',
                'labels' => $records->sortBy(fn ($record) => ($record->reporting_year * 100) + (int) $record->reporting_month)
                    ->map(fn ($record) => $record->reporting_year.' '.$this->shortMonthName((int) $record->reporting_month))
                    ->values(),
                'datasets' => [[
                    'label' => 'Total fuel cost incurred (PHP)',
                    'data' => $records->sortBy(fn ($record) => ($record->reporting_year * 100) + (int) $record->reporting_month)
                        ->map(fn ($record) => (float) $record->total_fuel_cost_incurred)
                        ->values(),
                ]],
            ],
            'electricity-consumptions' => [
                'type' => 'bar',
                'labels' => ['Salvador', 'Kreutz', 'Lantaka'],
                'datasets' => [[
                    'label' => 'kWh',
                    'data' => [
                        round($records->sum('total_salvador_kwh'), 2),
                        round($records->sum('total_kreutz_kwh'), 2),
                        round($records->sum('total_lantaka_kwh'), 2),
                    ],
                ]],
            ],
            'solar-performances' => [
                'type' => 'bar',
                'labels' => $records->groupBy('solar_panel_id')->keys()->values(),
                'datasets' => [[
                    'label' => 'Generated kWh',
                    'data' => $records->groupBy('solar_panel_id')->map(fn ($rows) => round($rows->sum('monthly_solar_energy_kwh'), 2))->values(),
                ]],
            ],
            'student-service-volumes' => [
                'type' => 'bar',
                'labels' => $records->groupBy('office_unit_name')->keys()->values(),
                'datasets' => [[
                    'label' => 'Transactions',
                    'data' => $records->groupBy('office_unit_name')->map(fn ($rows) => $rows->sum('student_transactions_count'))->values(),
                ]],
            ],
            'estimated-savings' => [
                'type' => 'bar',
                'labels' => ['Travel', 'Utilities', 'Activities/Events'],
                'datasets' => [[
                    'label' => 'Savings',
                    'data' => [
                        round($records->sum('reduced_travel_savings'), 2),
                        round($records->sum('reduced_utilities_savings'), 2),
                        round($records->sum('reduced_activities_savings'), 2),
                    ],
                ]],
            ],
            default => ['type' => 'bar', 'labels' => [], 'datasets' => []],
        };
    }

    private function narrative(Request $request, string $moduleKey): string
    {
        $period = trim(($request->filled('reporting_month') ? $this->months()[(int) $request->input('reporting_month')] : 'selected month').' '.($request->input('reporting_year') ?: 'selected year'));
        $module = $moduleKey === 'all' ? 'all report categories' : $this->modules()[$moduleKey]['label'];

        return "This report summarizes {$module} for {$period}. Figures are based on submitted records available in the system and are intended to support the monthly brief for the Office of the President and the Core Presidents Council.";
    }

    private function validatedModuleKey(Request $request): string
    {
        $key = $request->input('module', 'all');

        return $key === 'all' || array_key_exists($key, $this->modules()) ? $key : 'all';
    }

    private function modules(): array
    {
        return [
            'fuel-prices' => [
                'label' => 'Weekly Fuel Prices',
                'model' => FuelPrice::class,
                'columns' => [
                    'respondent_name' => 'Respondent',
                    'reporting_month' => 'Month',
                    'reporting_year' => 'Year',
                    'week_number' => 'Week',
                    'shell_fuel_save_diesel' => 'Shell Diesel',
                    'petron_diesel_max' => 'Petron Diesel',
                    'caltex_diesel' => 'Caltex Diesel',
                ],
            ],
            'electricity-consumptions' => [
                'label' => 'Electricity Consumption',
                'model' => ElectricityConsumption::class,
                'columns' => [
                    'respondent_name' => 'Respondent',
                    'reporting_month' => 'Month',
                    'reporting_year' => 'Year',
                    'total_salvador_kwh' => 'Salvador kWh',
                    'total_kreutz_kwh' => 'Kreutz kWh',
                    'total_lantaka_kwh' => 'Lantaka kWh',
                ],
            ],
            'fuel-vehicle-uses' => [
                'label' => 'Fuel and Vehicle Use',
                'model' => FuelVehicleUse::class,
                'columns' => [
                    'respondent_name' => 'Respondent',
                    'reporting_month' => 'Month',
                    'reporting_year' => 'Year',
                    'total_fuel_cost_incurred' => 'Total Fuel Cost Incurred (PHP)',
                    'remarks' => 'Remarks',
                ],
            ],
            'solar-performances' => [
                'label' => 'Solar Savings and Performance',
                'model' => SolarPerformance::class,
                'columns' => [
                    'respondent_name' => 'Respondent',
                    'reporting_month' => 'Month',
                    'reporting_year' => 'Year',
                    'solar_panel_id' => 'Panel ID',
                    'monthly_solar_energy_kwh' => 'Generated kWh',
                    'estimated_savings' => 'Savings',
                ],
            ],
            'student-service-volumes' => [
                'label' => 'Student Service Volume',
                'model' => StudentServiceVolume::class,
                'columns' => [
                    'respondent_name' => 'Respondent',
                    'reporting_month' => 'Month',
                    'reporting_year' => 'Year',
                    'office_unit_name' => 'Office/Unit',
                    'student_transactions_count' => 'Transactions',
                ],
            ],
            'estimated-savings' => [
                'label' => 'Estimated Savings',
                'model' => EstimatedSaving::class,
                'columns' => [
                    'respondent_name' => 'Respondent',
                    'reporting_year' => 'Year',
                    'office_unit_name' => 'Office/Unit',
                    'reduced_travel_savings' => 'Travel',
                    'reduced_utilities_savings' => 'Utilities',
                    'reduced_activities_savings' => 'Activities',
                    'total_estimated_savings' => 'Total',
                ],
            ],
        ];
    }

    private function months(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    private function shortMonthName(int $month): string
    {
        return substr($this->months()[$month] ?? 'N/A', 0, 3);
    }
}
