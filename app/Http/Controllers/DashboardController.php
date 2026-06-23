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
        $categories = $this->categories();
        $selectedCategory = $request->input('category', 'fuel-prices');

        if (! array_key_exists($selectedCategory, $categories)) {
            $selectedCategory = 'fuel-prices';
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

    private function categories(): array
    {
        return [
            'fuel-prices' => ['label' => 'Weekly Fuel Prices', 'icon' => 'bi-fuel-pump'],
            'electricity' => ['label' => 'Electricity Consumption', 'icon' => 'bi-lightning-charge'],
            'fuel-vehicle-use' => ['label' => 'Fuel and Vehicle Use', 'icon' => 'bi-truck'],
            'solar' => ['label' => 'Solar Savings', 'icon' => 'bi-sun'],
            'student-services' => ['label' => 'Student Service Volume', 'icon' => 'bi-people'],
            'estimated-savings' => ['label' => 'Estimated Savings', 'icon' => 'bi-cash-coin'],
        ];
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
        ];
    }

    private function electricityData(Request $request): array
    {
        $electricity = ElectricityConsumption::visibleTo($request->user())
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
        ];
    }

    private function fuelVehicleUseData(Request $request): array
    {
        $fuelVehicles = FuelVehicleUse::visibleTo($request->user())
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();

        $monthlyCosts = $this->monthlyTrend($fuelVehicles, fn ($record) => (float) $record->total_fuel_cost_incurred);

        return [
            'pageTitle' => 'Fuel and Vehicle Use',
            'pageIcon' => 'bi-truck',
            'description' => 'Fuel and vehicle cost graphs by reporting month.',
            'createRoute' => route('fuel-vehicle-uses.create'),
            'recordsRoute' => route('fuel-vehicle-uses.index'),
            'metrics' => [
                ['label' => 'Total fuel cost incurred', 'value' => $this->formatDecimal($fuelVehicles->sum(fn ($record) => (float) $record->total_fuel_cost_incurred)), 'hint' => 'Across visible records'],
                ['label' => 'Submitted records', 'value' => number_format($fuelVehicles->count()), 'hint' => 'Fuel and vehicle use reports'],
            ],
            'charts' => [[
                'id' => 'fuelVehicleCostChart',
                'title' => 'Monthly Fuel Cost Incurred',
                'icon' => 'bi-cash-stack',
                'type' => 'bar',
                'labels' => $monthlyCosts->keys()->values(),
                'datasets' => [$this->barDataset('Total fuel cost incurred', $monthlyCosts->values(), '#d9534f')],
                'wide' => true,
            ]],
        ];
    }

    private function solarData(Request $request): array
    {
        $solar = SolarPerformance::visibleTo($request->user())
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();

        $solarTrend = $this->monthlyTrend($solar, fn ($record) => (float) $record->monthly_solar_energy_kwh);
        $solarSavingsTrend = $this->monthlyTrend($solar, fn ($record) => (float) $record->estimated_savings);
        $solarByPanel = $solar
            ->groupBy('solar_panel_id')
            ->map(fn (Collection $rows) => round($rows->sum('monthly_solar_energy_kwh'), 2));

        return [
            'pageTitle' => 'Solar Savings',
            'pageIcon' => 'bi-sun',
            'description' => 'Solar generation and savings graphs.',
            'createRoute' => route('solar-performances.create'),
            'recordsRoute' => route('solar-performances.index'),
            'metrics' => [
                ['label' => 'Solar generated', 'value' => $this->formatDecimal($solar->sum('monthly_solar_energy_kwh')), 'hint' => 'kWh across panels'],
                ['label' => 'Estimated solar savings', 'value' => $this->formatDecimal($solar->sum('estimated_savings')), 'hint' => 'Across visible records'],
                ['label' => 'Solar panels tracked', 'value' => number_format($solarByPanel->count()), 'hint' => 'Unique panel IDs'],
            ],
            'charts' => [
                [
                    'id' => 'solarPanelChart',
                    'title' => 'Solar Generated by Panel',
                    'icon' => 'bi-sun',
                    'type' => 'bar',
                    'labels' => $solarByPanel->keys()->values(),
                    'datasets' => [$this->barDataset('Solar generated kWh', $solarByPanel->values(), '#ffc107')],
                ],
                [
                    'id' => 'solarTrendChart',
                    'title' => 'Monthly Solar Generation',
                    'icon' => 'bi-activity',
                    'type' => 'line',
                    'labels' => $solarTrend->keys()->values(),
                    'datasets' => [$this->lineDataset('Monthly kWh', $solarTrend->values(), '#0f8b4c')],
                ],
                [
                    'id' => 'solarSavingsChart',
                    'title' => 'Monthly Solar Savings',
                    'icon' => 'bi-cash-coin',
                    'type' => 'line',
                    'labels' => $solarSavingsTrend->keys()->values(),
                    'datasets' => [$this->lineDataset('Estimated savings', $solarSavingsTrend->values(), '#073f8f')],
                    'wide' => true,
                ],
            ],
        ];
    }

    private function studentServicesData(Request $request): array
    {
        $services = StudentServiceVolume::visibleTo($request->user())
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
        ];
    }

    private function estimatedSavingsData(Request $request): array
    {
        $savings = EstimatedSaving::visibleTo($request->user())
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
        ];
    }

    private function monthlyTrend(Collection $records, callable $valueResolver): Collection
    {
        return $records
            ->groupBy(fn ($record) => $record->reporting_year.'-'.$this->monthName((int) $record->reporting_month))
            ->map(fn (Collection $rows) => round($rows->sum($valueResolver), 2));
    }

    private function lineDataset(string $label, mixed $data, string $color): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'borderColor' => $color,
            'backgroundColor' => $this->transparentColor($color),
            'tension' => .25,
        ];
    }

    private function barDataset(string $label, mixed $data, mixed $color = null): array
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
