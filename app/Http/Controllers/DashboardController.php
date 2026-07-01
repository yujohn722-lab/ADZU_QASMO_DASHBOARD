<?php

namespace App\Http\Controllers;

use App\Models\ElectricityConsumption;
use App\Models\EstimatedSaving;
use App\Models\FuelPrice;
use App\Models\FuelVehicleUse;
use App\Models\SolarPerformance;
use App\Models\StudentServiceVolume;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $categories = $this->categories($request);
        $selectedCategory = $request->input('category', 'fuel-prices');

        abort_if(empty($categories), 403);

        if (! array_key_exists($selectedCategory, $categories)) {
            $selectedCategory = array_key_first($categories) ?? 'fuel-prices';
        }

        $categoryData = match ($selectedCategory) {
            'electricity' => $this->electricityData($request),
            'fuel-vehicle-use' => $this->fuelVehicleUseData($request),
            'solar' => $this->solarData($request),
            'student-services' => $this->studentServicesData($request),
            'estimated-savings' => $this->estimatedSavingsData($request),
            default => $this->fuelPricesData($request),
        };

        return view('dashboard', array_merge($categoryData, [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
        ]));
    }

    private function categories(Request $request): array
    {
        $categories = [
            'fuel-prices' => ['label' => 'Weekly Fuel Prices', 'icon' => 'bi-fuel-pump', 'module' => 'fuel-prices'],
            'electricity' => ['label' => 'Electricity Consumption', 'icon' => 'bi-lightning-charge', 'module' => 'electricity-consumptions'],
            'fuel-vehicle-use' => ['label' => 'Fuel and Vehicle Use', 'icon' => 'bi-truck', 'module' => 'fuel-vehicle-uses'],
            'solar' => ['label' => 'Solar Savings', 'icon' => 'bi-sun', 'module' => 'solar-performances'],
            'student-services' => ['label' => 'Student Service Volume', 'icon' => 'bi-people', 'module' => 'student-service-volumes'],
            'estimated-savings' => ['label' => 'Estimated Savings', 'icon' => 'bi-cash-coin', 'module' => 'estimated-savings'],
        ];

        return collect($categories)
            ->filter(fn (array $category) => $request->user()->canAccessReportType($category['module']))
            ->all();
    }

    private function fuelPricesData(Request $request): array
    {
        $fuelPrices = FuelPrice::visibleTo($request->user())
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->orderBy('week_number')
            ->get();

        $latestFuel = $fuelPrices
            ->sortByDesc(fn (FuelPrice $record) => ($record->reporting_year * 10000) + ((int) $record->reporting_month * 100) + $record->week_number)
            ->first();

        return [
            'pageTitle' => 'Weekly Fuel Prices',
            'pageIcon' => 'bi-fuel-pump',
            'description' => 'Fuel price charts and graph summaries for the selected category.',
            'createRoute' => route('fuel-prices.create'),
            'recordsRoute' => route('fuel-prices.index'),
            'metrics' => [
                ['label' => 'Latest diesel average', 'value' => $this->formatDecimal($latestFuel?->averageDieselPrice()), 'hint' => 'Latest submitted week'],
                ['label' => 'Latest gasoline average', 'value' => $this->formatDecimal($latestFuel?->averageGasolinePrice()), 'hint' => 'Latest submitted week'],
                ['label' => 'Highest fuel price', 'value' => $this->formatDecimal($latestFuel?->highestPrice()), 'hint' => 'From latest weekly record'],
                ['label' => 'Lowest fuel price', 'value' => $this->formatDecimal($latestFuel?->lowestPrice()), 'hint' => 'From latest weekly record'],
            ],
            'charts' => [
                [
                    'id' => 'fuelMovementChart',
                    'title' => 'Gasoline Fuel Price Comparison',
                    'icon' => 'bi-graph-up',
                    'type' => 'line',
                    'labels' => $fuelPrices->map(fn (FuelPrice $record) => $record->reporting_year.' '.$this->monthName((int) $record->reporting_month).' W'.$record->week_number)->values(),
                    'filterOptions' => [
                        ['value' => 'all-gas', 'label' => 'All Gas'],
                        ['value' => 'gas-regular', 'label' => 'Gas Regular (91 RON)'],
                        ['value' => 'gas-premium', 'label' => 'Gas Premium (95 RON)'],
                        ['value' => 'gas-ultra', 'label' => 'Gas Ultra (97+ RON)'],
                    ],
                    'datasets' => [
                        [
                            'label' => 'Shell FuelSave Gasoline',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_fuel_save_regular)->values(),
                            'filterGroup' => 'gas-regular',
                            'borderColor' => '#073f8f',
                            'backgroundColor' => $this->transparentColor('#073f8f'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Petron Xtra Advance',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->petron_xtra_advance_regular)->values(),
                            'filterGroup' => 'gas-regular',
                            'borderColor' => '#19bceb',
                            'backgroundColor' => $this->transparentColor('#19bceb'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Caltex Silver 91',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->caltex_silver_regular)->values(),
                            'filterGroup' => 'gas-regular',
                            'borderColor' => '#0f8b4c',
                            'backgroundColor' => $this->transparentColor('#0f8b4c'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Shell V-Power Gasoline',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_v_power_premium)->values(),
                            'filterGroup' => 'gas-premium',
                            'borderColor' => '#ffc107',
                            'backgroundColor' => $this->transparentColor('#ffc107'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Petron XCS',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->petron_xcs_premium)->values(),
                            'filterGroup' => 'gas-premium',
                            'borderColor' => '#d9534f',
                            'backgroundColor' => $this->transparentColor('#d9534f'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Caltex Platinum 95',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->caltex_platinum_premium)->values(),
                            'filterGroup' => 'gas-premium',
                            'borderColor' => '#6f42c1',
                            'backgroundColor' => $this->transparentColor('#6f42c1'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Shell V-Power Racing',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_v_power_premium_sport)->values(),
                            'filterGroup' => 'gas-ultra',
                            'borderColor' => '#8e44ad',
                            'backgroundColor' => $this->transparentColor('#8e44ad'),
                            'tension' => .25,
                        ],
                    ],
                    'wide' => true,
                ],
                [
                    'id' => 'dieselMovementChart',
                    'title' => 'Diesel Fuel Price Comparison',
                    'icon' => 'bi-graph-up',
                    'type' => 'line',
                    'labels' => $fuelPrices->map(fn (FuelPrice $record) => $record->reporting_year.' '.$this->monthName((int) $record->reporting_month).' W'.$record->week_number)->values(),
                    'filterOptions' => [
                        ['value' => 'all-diesel', 'label' => 'All Diesel'],
                        ['value' => 'diesel-regular', 'label' => 'Diesel Regular'],
                        ['value' => 'diesel-premium', 'label' => 'Diesel Premium'],
                    ],
                    'datasets' => [
                        [
                            'label' => 'Shell FuelSave Diesel',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_fuel_save_diesel)->values(),
                            'filterGroup' => 'diesel-regular',
                            'borderColor' => '#073f8f',
                            'backgroundColor' => $this->transparentColor('#073f8f'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Petron Diesel Max',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->petron_diesel_max)->values(),
                            'filterGroup' => 'diesel-regular',
                            'borderColor' => '#19bceb',
                            'backgroundColor' => $this->transparentColor('#19bceb'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Caltex Power Diesel',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->caltex_diesel)->values(),
                            'filterGroup' => 'diesel-regular',
                            'borderColor' => '#0f8b4c',
                            'backgroundColor' => $this->transparentColor('#0f8b4c'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Shell V-Power Diesel',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_v_power_diesel)->values(),
                            'filterGroup' => 'diesel-premium',
                            'borderColor' => '#ffc107',
                            'backgroundColor' => $this->transparentColor('#ffc107'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Petron Turbo Diesel',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->petron_turbo_diesel)->values(),
                            'filterGroup' => 'diesel-premium',
                            'borderColor' => '#d9534f',
                            'backgroundColor' => $this->transparentColor('#d9534f'),
                            'tension' => .25,
                        ],
                    ],
                    'wide' => true,
                ],
            ],
            'remarks' => $this->remarksFor($fuelPrices, 'Weekly Fuel Prices'),
        ];
    }

    private function electricityData(Request $request): array
    {
        $electricity = ElectricityConsumption::query()
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();

        $electricityCampus = [
            'Salvador' => round($electricity->sum(fn ($record) => (float) $record->total_salvador_kwh), 2),
            'Kreutz' => round($electricity->sum(fn ($record) => (float) $record->total_kreutz_kwh), 2),
            'Lantaka' => round($electricity->sum(fn ($record) => (float) $record->total_lantaka_kwh), 2),
        ];

        $buildingTotals = collect(ElectricityConsumption::BUILDING_LABELS)
            ->mapWithKeys(fn (string $label, string $field) => [
                $label => round($electricity->sum(fn ($record) => (float) $record->{$field}), 2),
            ])
            ->filter(fn (float $value) => $value > 0);

        $highestBuilding = $buildingTotals->isEmpty()
            ? ['label' => 'No data', 'value' => 0]
            : ['label' => $buildingTotals->sortDesc()->keys()->first(), 'value' => $buildingTotals->max()];

        $electricityTrend = $this->monthlyTrend($electricity, fn ($record) => $record->totalKwh());

        return [
            'pageTitle' => 'Electricity Consumption',
            'pageIcon' => 'bi-lightning-charge',
            'description' => 'Electricity graphs by campus, building, and monthly total.',
            'createRoute' => route('electricity-consumptions.create'),
            'recordsRoute' => route('electricity-consumptions.index'),
            'metrics' => [
                ['label' => 'Total electricity consumption', 'value' => $this->formatDecimal(array_sum($electricityCampus)), 'hint' => 'kWh across campuses'],
                ['label' => 'Highest-consuming building', 'value' => $highestBuilding['label'], 'hint' => $this->formatDecimal($highestBuilding['value']).' kWh'],
                ['label' => 'Submitted records', 'value' => number_format($electricity->count()), 'hint' => 'Visible electricity reports'],
            ],
            'charts' => [
                [
                    'id' => 'campusChart',
                    'title' => 'Campus Consumption',
                    'icon' => 'bi-buildings',
                    'type' => 'bar',
                    'labels' => array_keys($electricityCampus),
                    'datasets' => [$this->barDataset('Campus kWh', array_values($electricityCampus))],
                ],
                [
                    'id' => 'buildingChart',
                    'title' => 'Building Consumption',
                    'icon' => 'bi-bar-chart',
                    'type' => 'bar',
                    'labels' => $buildingTotals->keys()->values(),
                    'datasets' => [$this->barDataset('Building kWh', $buildingTotals->values(), '#19bceb')],
                    'options' => ['indexAxis' => 'y'],
                ],
                [
                    'id' => 'electricityTrendChart',
                    'title' => 'Monthly Consumption Trend',
                    'icon' => 'bi-activity',
                    'type' => 'line',
                    'labels' => $electricityTrend->keys()->values(),
                    'datasets' => [$this->lineDataset('Monthly kWh', $electricityTrend->values(), '#073f8f')],
                    'wide' => true,
                ],
            ],
            'remarks' => $this->remarksFor($electricity, 'Electricity Consumption'),
        ];
    }

    private function fuelVehicleUseData(Request $request): array
    {
        $fuelVehicles = FuelVehicleUse::query()
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();

        $monthlyCosts = $this->monthlyTrend($fuelVehicles, fn ($record) => (float) $record->total_fuel_cost_incurred);
        $monthlyLiters = $this->monthlyTrend($fuelVehicles, fn ($record) => (float) $record->total_fuel_liters_loaded);
        $monthlyLabels = $monthlyCosts->keys()->merge($monthlyLiters->keys())->unique()->values();
        $latestRecord = $fuelVehicles
            ->sortByDesc(fn (FuelVehicleUse $record) => ($record->reporting_year * 100) + (int) $record->reporting_month)
            ->first();
        $latestMonthLabel = $latestRecord
            ? $latestRecord->reporting_year.' '.$this->monthName((int) $latestRecord->reporting_month)
            : 'No data';
        $latestMonthKey = $latestRecord
            ? $latestRecord->reporting_year.'-'.$this->monthName((int) $latestRecord->reporting_month)
            : null;
        $costPerLiter = $monthlyLabels->map(function (string $label) use ($monthlyCosts, $monthlyLiters) {
            $liters = (float) ($monthlyLiters[$label] ?? 0);

            return $liters > 0 ? round((float) ($monthlyCosts[$label] ?? 0) / $liters, 2) : 0;
        });

        return [
            'pageTitle' => 'Fuel and Vehicle Use',
            'pageIcon' => 'bi-truck',
            'description' => 'Monthly fleet fuel cost and liters pumped graphs.',
            'createRoute' => route('fuel-vehicle-uses.create'),
            'recordsRoute' => route('fuel-vehicle-uses.index'),
            'metrics' => [
                ['label' => 'Reporting month', 'value' => $latestMonthLabel, 'hint' => 'Latest submitted period'],
                ['label' => 'Total cost for the month', 'value' => $this->formatDecimal($latestMonthKey ? ($monthlyCosts[$latestMonthKey] ?? 0) : 0), 'hint' => 'PHP'],
                ['label' => 'Total liters pumped', 'value' => $this->formatDecimal($latestMonthKey ? ($monthlyLiters[$latestMonthKey] ?? 0) : 0), 'hint' => 'Liters']
                
            ],
            'charts' => [
                [
                    'id' => 'fleetFuelCostTrendChart',
                    'title' => 'Total Spend vs. Volume Consumption',
                    'icon' => 'bi-activity',
                    'type' => 'bar',
                    'labels' => $monthlyLabels,
                    'datasets' => [
                        [
                            'type' => 'bar',
                            'label' => 'Total liters loaded',
                            'data' => $monthlyLabels->map(fn (string $label) => (float) ($monthlyLiters[$label] ?? 0)),
                            'backgroundColor' => '#19bceb',
                            'yAxisID' => 'liters',
                        ],
                        [
                            'type' => 'line',
                            'label' => 'Total amount spent',
                            'data' => $monthlyLabels->map(fn (string $label) => (float) ($monthlyCosts[$label] ?? 0)),
                            'borderColor' => '#d9534f',
                            'backgroundColor' => $this->transparentColor('#d9534f'),
                            'tension' => .25,
                            'yAxisID' => 'cost',
                        ],
                    ],
                    'options' => [
                        'scales' => [
                            'cost' => ['type' => 'linear', 'position' => 'left', 'title' => ['display' => true, 'text' => 'Total Cost (PHP)']],
                            'liters' => ['type' => 'linear', 'position' => 'right', 'title' => ['display' => true, 'text' => 'Total Liters'], 'grid' => ['drawOnChartArea' => false]],
                        ],
                    ],
                    'wide' => true,
                ],
                [
                    'id' => 'fuelCostPerLiterChart',
                    'title' => 'Average Cost per Liter',
                    'icon' => 'bi-graph-up-arrow',
                    'type' => 'line',
                    'labels' => $monthlyLabels,
                    'datasets' => [$this->lineDataset('PHP per liter', $costPerLiter, '#0f8b4c')],
                    'options' => [
                        'scales' => [
                            'y' => ['title' => ['display' => true, 'text' => 'PHP / Liter']],
                        ],
                    ],
                ],
                [
                    'id' => 'monthlyFuelCostChart',
                    'title' => 'Monthly Total Fuel Cost',
                    'icon' => 'bi-cash-stack',
                    'type' => 'bar',
                    'labels' => $monthlyLabels,
                    'datasets' => [$this->barDataset('Total cost (PHP)', $monthlyLabels->map(fn (string $label) => (float) ($monthlyCosts[$label] ?? 0)), '#d9534f')],
                    'options' => [
                        'scales' => [
                            'y' => ['title' => ['display' => true, 'text' => 'PHP']],
                        ],
                    ],
                ],
                [
                    'id' => 'monthlyFuelLitersChart',
                    'title' => 'Monthly Total Liters Pumped',
                    'icon' => 'bi-fuel-pump',
                    'type' => 'bar',
                    'labels' => $monthlyLabels,
                    'datasets' => [$this->barDataset('Total liters', $monthlyLabels->map(fn (string $label) => (float) ($monthlyLiters[$label] ?? 0)), '#19bceb')],
                    'options' => [
                        'scales' => [
                            'y' => ['title' => ['display' => true, 'text' => 'Liters']],
                        ],
                    ],
                ],
            ],
            'remarks' => $this->remarksFor($fuelVehicles, 'Fuel and Vehicle Use'),
        ];
    }

    private function solarData(Request $request): array
    {
        $solar = SolarPerformance::query()
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();

        $solarTrend = $this->monthlyTrend($solar, fn ($record) => (float) $record->monthly_solar_energy_kwh);
        $solarSavingsTrend = $this->monthlyTrend($solar, fn ($record) => (float) $record->estimated_savings);

        $monthlyLabels = $solar
            ->sortBy(fn ($record) => ($record->reporting_year * 100) + $record->reporting_month)
            ->map(fn ($record) => $record->reporting_year.' '.$this->monthName((int) $record->reporting_month))
            ->unique()
            ->values();

        $solarByBuilding = $solar
            ->groupBy('building_name')
            ->mapWithKeys(function (Collection $rows, string $building) use ($monthlyLabels) {
                $monthlyValues = $monthlyLabels->map(function (string $label) use ($rows) {
                    return (float) $rows->first(fn ($record) => $record->reporting_year.' '.$this->monthName((int) $record->reporting_month) === $label)?->monthly_solar_energy_kwh ?? 0;
                });

                return [$building => $monthlyValues];
            });

        $buildingFilterOptions = $solarByBuilding->keys()->map(fn (string $building) => [
            'value' => $building,
            'label' => $building,
        ])->prepend(['value' => 'all-buildings', 'label' => 'All Buildings'])->values();
        $latestSolar = $solar
            ->sortByDesc(fn (SolarPerformance $record) => ($record->reporting_year * 100) + (int) $record->reporting_month)
            ->first();
        $currentReportingPeriod = $latestSolar
            ? $latestSolar->reporting_year.' '.$this->monthName((int) $latestSolar->reporting_month)
            : 'No data';

        return [
            'pageTitle' => 'Solar Savings',
            'pageIcon' => 'bi-sun',
            'description' => 'Solar generation and savings graphs.',
            'createRoute' => route('solar-performances.create'),
            'recordsRoute' => route('solar-performances.index'),
            'metrics' => [
                ['label' => 'Solar generated', 'value' => $this->formatDecimal($solar->sum('monthly_solar_energy_kwh')), 'hint' => 'kWh across buildings'],
                ['label' => 'Total solar savings', 'value' => $this->formatDecimal($solar->sum('estimated_savings')), 'hint' => 'Across visible records'],
                ['label' => 'Current reporting period', 'value' => $currentReportingPeriod, 'hint' => 'Latest submitted period'],
            ],
            'charts' => [
                [
                    'id' => 'solarBuildingChart',
                    'title' => 'Solar Performance by Building',
                    'icon' => 'bi-sun',
                    'type' => 'bar',
                    'labels' => $monthlyLabels,
                    'filterOptions' => $buildingFilterOptions,
                    'datasets' => $solarByBuilding->map(function (Collection $monthlyValues, string $building) {
                        $color = match ($building) {
                            'Ernesto Carretero (FEC) Building' => '#073f8f',
                            'GS Admin' => '#19bceb',
                            'Jose Maria Rosauro SJ Hall' => '#ffc107',
                            'Xavier Hall' => '#0f8b4c',
                            'College Building' => '#d9534f',
                            'Jesuit Residence' => '#6f42c1',
                            default => '#8e44ad',
                        };

                        return [
                            'label' => $building,
                            'data' => $monthlyValues,
                            'filterGroup' => $building,
                            'borderColor' => $color,
                            'backgroundColor' => $color,
                            'borderWidth' => 1,
                        ];
                    })->values(),
                    'wide' => true,
                ],
                [
                    'id' => 'solarTrendChart',
                    'title' => 'Monthly Solar Generation',
                    'icon' => 'bi-activity',
                    'type' => 'line',
                    'labels' => $solarTrend->keys()->values(),
                    'datasets' => [$this->lineDataset('Monthly kWh', $solarTrend->values(), '#0f8b4c')],
                    'showPointLabels' => true,
                    'options' => ['layout' => ['padding' => ['top' => 24]]],
                    'wide' => true,
                ],
                [
                    'id' => 'solarSavingsChart',
                    'title' => 'Monthly Solar Savings',
                    'icon' => 'bi-cash-coin',
                    'type' => 'line',
                    'labels' => $solarSavingsTrend->keys()->values(),
                    'datasets' => [$this->lineDataset('Total solar savings', $solarSavingsTrend->values(), '#073f8f')],
                    'showPointLabels' => true,
                    'options' => ['layout' => ['padding' => ['top' => 24]]],
                    'wide' => true,
                ],
            ],
            'remarks' => $this->remarksFor($solar, 'Solar Savings'),
        ];
    }

    private function studentServicesData(Request $request): array
    {
        $services = StudentServiceVolume::query()
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();

        $monthlyTransactions = $this->monthlyTrend($services, fn ($record) => (int) $record->student_transactions_count);
        $serviceByOffice = $services
            ->groupBy('office_unit_name')
            ->map(fn (Collection $rows) => $rows->sum('student_transactions_count'))
            ->sortDesc();

        return [
            'pageTitle' => 'Student Service Volume',
            'pageIcon' => 'bi-people',
            'description' => 'Student transaction graphs by office/unit and month.',
            'createRoute' => route('student-service-volumes.create'),
            'recordsRoute' => route('student-service-volumes.index'),
            'metrics' => [
                ['label' => 'Student transactions', 'value' => number_format($services->sum('student_transactions_count')), 'hint' => 'Across visible records'],
                ['label' => 'Offices/units tracked', 'value' => number_format($serviceByOffice->count()), 'hint' => 'Unique office/unit names'],
                ['label' => 'Submitted records', 'value' => number_format($services->count()), 'hint' => 'Student service reports'],
            ],
            'charts' => [
                [
                    'id' => 'serviceOfficeChart',
                    'title' => 'Transactions by Office/Unit',
                    'icon' => 'bi-building',
                    'type' => 'bar',
                    'labels' => $serviceByOffice->keys()->values(),
                    'datasets' => [$this->barDataset('Student transactions', $serviceByOffice->values(), '#0f8b4c')],
                    'options' => ['indexAxis' => 'y'],
                ],
                [
                    'id' => 'serviceTrendChart',
                    'title' => 'Monthly Transaction Trend',
                    'icon' => 'bi-activity',
                    'type' => 'line',
                    'labels' => $monthlyTransactions->keys()->values(),
                    'datasets' => [$this->lineDataset('Student transactions', $monthlyTransactions->values(), '#073f8f')],
                ],
            ],
            'remarks' => $this->remarksFor($services, 'Student Service Volume'),
        ];
    }

    private function estimatedSavingsData(Request $request): array
    {
        $savings = EstimatedSaving::query()
            ->orderBy('reporting_year')
            ->get();

        $savingsByCategory = [
            'Travel' => round($savings->sum(fn ($record) => (float) $record->reduced_travel_savings), 2),
            'Utilities' => round($savings->sum(fn ($record) => (float) $record->reduced_utilities_savings), 2),
            'Activities/Events' => round($savings->sum(fn ($record) => (float) $record->reduced_activities_savings), 2),
        ];

        $savingsByOffice = $savings
            ->groupBy('office_unit_name')
            ->map(fn (Collection $rows) => round($rows->sum('total_estimated_savings'), 2))
            ->sortDesc();

        $savingsByYear = $savings
            ->groupBy('reporting_year')
            ->map(fn (Collection $rows) => round($rows->sum('total_estimated_savings'), 2));

        return [
            'pageTitle' => 'Estimated Savings',
            'pageIcon' => 'bi-cash-coin',
            'description' => 'Estimated savings graphs by category, office/unit, and year.',
            'createRoute' => route('estimated-savings.create'),
            'recordsRoute' => route('estimated-savings.index'),
            'metrics' => [
                ['label' => 'Total estimated savings', 'value' => $this->formatDecimal($savings->sum('total_estimated_savings')), 'hint' => 'Across visible records'],
                ['label' => 'Offices/units tracked', 'value' => number_format($savingsByOffice->count()), 'hint' => 'Unique office/unit names'],
                ['label' => 'Submitted records', 'value' => number_format($savings->count()), 'hint' => 'Estimated savings reports'],
            ],
            'charts' => [
                [
                    'id' => 'savingsCategoryChart',
                    'title' => 'Savings by Category',
                    'icon' => 'bi-pie-chart',
                    'type' => 'doughnut',
                    'labels' => array_keys($savingsByCategory),
                    'datasets' => [$this->barDataset('Estimated savings', array_values($savingsByCategory))],
                ],
                [
                    'id' => 'savingsOfficeChart',
                    'title' => 'Savings by Office/Unit',
                    'icon' => 'bi-building',
                    'type' => 'bar',
                    'labels' => $savingsByOffice->keys()->values(),
                    'datasets' => [$this->barDataset('Total estimated savings', $savingsByOffice->values(), '#19bceb')],
                    'options' => ['indexAxis' => 'y'],
                ],
                [
                    'id' => 'savingsYearChart',
                    'title' => 'Savings by Year',
                    'icon' => 'bi-calendar3',
                    'type' => 'line',
                    'labels' => $savingsByYear->keys()->values(),
                    'datasets' => [$this->lineDataset('Total estimated savings', $savingsByYear->values(), '#073f8f')],
                    'wide' => true,
                ],
            ],
            'remarks' => $this->remarksFor($savings, 'Estimated Savings'),
        ];
    }

    private function remarksFor(Collection $records, string $moduleLabel): Collection
    {
        return $records
            ->sortByDesc(fn ($record) => ($record->reporting_year ?? 0) * 10000 + ((int) ($record->reporting_month ?? 0) * 100) + (int) ($record->week_number ?? 0))
            ->map(function ($record) use ($moduleLabel) {
                $recordRemarks = trim((string) ($record->remarks ?? ''));

                if ($recordRemarks === '') {
                    return null;
                }

                return [
                    'module' => $moduleLabel,
                    'respondent' => $record->respondent_name ?: 'Respondent',
                    'period' => $this->periodLabel($record),
                    'recordRemarks' => $recordRemarks,
                ];
            })
            ->filter()
            ->take(8)
            ->values();
    }

    private function periodLabel(object $record): string
    {
        $year = $record->reporting_year ?? null;
        $month = $record->reporting_month ?? null;
        $week = $record->week_number ?? null;

        if ($year && $month && $week) {
            return $year.' '.$this->monthName((int) $month).' W'.$week;
        }

        if ($year && $month) {
            return $year.' '.$this->monthName((int) $month);
        }

        return $year ? (string) $year : 'No period';
    }

    private function monthlyTrend(Collection $records, callable $valueResolver): Collection
    {
        return $records
            ->groupBy(fn ($record) => $record->reporting_year.'-'.$this->monthName((int) $record->reporting_month))
            ->map(fn (Collection $rows) => round($rows->sum($valueResolver), 2));
    }

    private function lineDataset(string $label, iterable $data, string $color): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'borderColor' => $color,
            'backgroundColor' => $this->transparentColor($color),
            'tension' => .25,
        ];
    }

    private function barDataset(string $label, iterable $data, $color = null): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'backgroundColor' => $color ?? ['#073f8f', '#19bceb', '#ffc107', '#0f8b4c', '#d9534f', '#6f42c1'],
        ];
    }

    private function transparentColor(string $hex): string
    {
        $hex = ltrim($hex, '#');

        return sprintf(
            'rgba(%d,%d,%d,.12)',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }

    private function formatDecimal(mixed $value): string
    {
        return number_format((float) ($value ?? 0), 2);
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec',
        ][$month] ?? 'N/A';
    }
}
