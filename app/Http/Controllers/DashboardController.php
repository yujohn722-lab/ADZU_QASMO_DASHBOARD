<?php

namespace App\Http\Controllers;

use App\Models\ElectricityConsumption;
use App\Models\EstimatedSaving;
use App\Models\FuelPrice;
use App\Models\FuelVehicleUse;
use App\Models\SolarPerformance;
use App\Models\StudentServiceVolume;
use App\Models\WaterBill;
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
            'water-bills' => $this->waterBillsData($request),
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
            'water-bills' => ['label' => 'Water Consumption', 'icon' => 'bi-droplet'],
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
                ['label' => 'Highest gasoline', 'value' => '₱' . ($latestFuel ? $this->formatDecimal($latestFuel->highestGasolineInfo()['price'] ?? 0) : '0.00'), 'hint' => ($latestFuel?->highestGasolineInfo()['brand'] ?? 'N/A') . ' - ' . ($latestFuel ? $latestFuel->reporting_year . ' ' . $this->monthName((int) $latestFuel->reporting_month) . ' W' . $latestFuel->week_number : 'N/A')],
                ['label' => 'Lowest gasoline', 'value' => '₱' . ($latestFuel ? $this->formatDecimal($latestFuel->lowestGasolineInfo()['price'] ?? 0) : '0.00'), 'hint' => ($latestFuel?->lowestGasolineInfo()['brand'] ?? 'N/A') . ' - ' . ($latestFuel ? $latestFuel->reporting_year . ' ' . $this->monthName((int) $latestFuel->reporting_month) . ' W' . $latestFuel->week_number : 'N/A')],
                ['label' => 'Highest diesel', 'value' => '₱' . ($latestFuel ? $this->formatDecimal($latestFuel->highestDieselInfo()['price'] ?? 0) : '0.00'), 'hint' => ($latestFuel?->highestDieselInfo()['brand'] ?? 'N/A') . ' - ' . ($latestFuel ? $latestFuel->reporting_year . ' ' . $this->monthName((int) $latestFuel->reporting_month) . ' W' . $latestFuel->week_number : 'N/A')],
                ['label' => 'Lowest diesel', 'value' => '₱' . ($latestFuel ? $this->formatDecimal($latestFuel->lowestDieselInfo()['price'] ?? 0) : '0.00'), 'hint' => ($latestFuel?->lowestDieselInfo()['brand'] ?? 'N/A') . ' - ' . ($latestFuel ? $latestFuel->reporting_year . ' ' . $this->monthName((int) $latestFuel->reporting_month) . ' W' . $latestFuel->week_number : 'N/A')],
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
                    ],
                    'datasets' => [
                        [
                            'label' => 'Shell Fuel Save Gasoline',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_fuel_save_regular)->values(),
                            'filterGroup' => 'gas-regular',
                            'borderColor' => '#073f8f',
                            'backgroundColor' => $this->transparentColor('#073f8f'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Petron XTRA',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->petron_xtra_advance_regular)->values(),
                            'filterGroup' => 'gas-regular',
                            'borderColor' => '#19bceb',
                            'backgroundColor' => $this->transparentColor('#19bceb'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Caltex Silver',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->caltex_silver_regular)->values(),
                            'filterGroup' => 'gas-regular',
                            'borderColor' => '#0f8b4c',
                            'backgroundColor' => $this->transparentColor('#0f8b4c'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Shell VPower Gasoline',
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
                            'label' => 'Caltex Platinum',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->caltex_platinum_premium)->values(),
                            'filterGroup' => 'gas-premium',
                            'borderColor' => '#6f42c1',
                            'backgroundColor' => $this->transparentColor('#6f42c1'),
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
                            'label' => 'Shell Fuel Save Diesel',
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
                            'label' => 'Caltex Diesel',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->caltex_diesel)->values(),
                            'filterGroup' => 'diesel-regular',
                            'borderColor' => '#0f8b4c',
                            'backgroundColor' => $this->transparentColor('#0f8b4c'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Shell VPower Diesel',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_v_power_diesel)->values(),
                            'filterGroup' => 'diesel-premium',
                            'borderColor' => '#ffc107',
                            'backgroundColor' => $this->transparentColor('#ffc107'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Petron PTD',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->petron_turbo_diesel)->values(),
                            'filterGroup' => 'diesel-premium',
                            'borderColor' => '#d9534f',
                            'backgroundColor' => $this->transparentColor('#d9534f'),
                            'tension' => .25,
                        ],
                        [
                            'label' => 'Petron ADO',
                            'data' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->petron_ado)->values(),
                            'filterGroup' => 'diesel-regular',
                            'borderColor' => '#6f42c1',
                            'backgroundColor' => $this->transparentColor('#6f42c1'),
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

        $latestElectricity = $electricity
            ->sortByDesc(fn (ElectricityConsumption $record) => ($record->reporting_year * 100) + (int) $record->reporting_month)
            ->first();
        $currentReportingPeriod = $latestElectricity
            ? $latestElectricity->reporting_year.' '.$this->monthName((int) $latestElectricity->reporting_month)
            : 'No data';
        $currentElectricity = $latestElectricity
            ? $electricity->where('reporting_year', $latestElectricity->reporting_year)->where('reporting_month', $latestElectricity->reporting_month)
            : collect();

        $electricityCampus = collect(ElectricityConsumption::CAMPUS_FIELDS)
            ->mapWithKeys(fn (string $label, string $field) => [
                $label => round($currentElectricity->sum(fn (ElectricityConsumption $record) => $record->campusKwh($field)), 2),
            ]);

        $highestCampus = $electricityCampus->filter(fn (float $value) => $value > 0)->isEmpty()
            ? ['label' => 'No data', 'value' => 0]
            : ['label' => $electricityCampus->sortDesc()->keys()->first(), 'value' => $electricityCampus->max()];

        $electricityTrend = $this->monthlyTrend($electricity, fn ($record) => $record->totalKwh());
        $electricityCampusTrendLabels = $electricityTrend->keys()->values();
        $electricityCampusTrend = collect(ElectricityConsumption::CAMPUS_FIELDS)
            ->mapWithKeys(function (string $label, string $field) use ($electricity, $electricityCampusTrendLabels) {
                $trend = $this->monthlyTrend($electricity, fn (ElectricityConsumption $record) => $record->campusKwh($field));

                return [$label => $electricityCampusTrendLabels->map(fn (string $month) => (float) ($trend[$month] ?? 0))];
            });

        return [
            'pageTitle' => 'Electricity Consumption',
            'pageIcon' => 'bi-lightning-charge',
            'description' => 'Electricity graphs by campus and monthly total.',
            'createRoute' => route('electricity-consumptions.create'),
            'recordsRoute' => route('electricity-consumptions.index'),
            'metrics' => [
                ['label' => 'Total electricity consumed', 'value' => $this->formatDecimal($electricityCampus->sum()), 'hint' => 'kWh for current reporting period'],
                ['label' => 'Highest-consuming campus', 'value' => $highestCampus['label'], 'hint' => $this->formatDecimal($highestCampus['value']).' kWh'],
                ['label' => 'Current reporting period', 'value' => $currentReportingPeriod, 'hint' => 'Latest submitted period'],
            ],
            'charts' => [
                [
                    'id' => 'campusChart',
                    'title' => 'Campus Consumption',
                    'icon' => 'bi-buildings',
                    'type' => 'bar',
                    'labels' => $electricityCampus->keys()->values(),
                    'datasets' => [[
                        'label' => 'Campus kWh',
                        'data' => $electricityCampus->values(),
                        'backgroundColor' => $electricityCampus->keys()->map(function (string $campus) {
                            return match ($campus) {
                                'Main' => '#073f8f',
                                'FWS' => '#d9534f',
                                'Kreutz' => '#0f8b4c',
                                'Lantaka' => '#19bceb',
                                default => '#6f42c1',
                            };
                        })->values()->all(),
                        'borderColor' => '#ffffff',
                        'borderWidth' => 1,
                    ]],
                    'showDataLabels' => true,
                    'options' => [
                        'layout' => ['padding' => ['top' => 24]],
                        'plugins' => [
                             'legend' => ['display' => false],  // This removes the "Campus kWh" label at the bottom
                            'tooltip' => ['enabled' => false]
                            ]
                        ],
                    'wide' => true,
                ],
                [
                    'id' => 'electricityTrendChart',
                    'title' => 'Monthly Consumption Trend',
                    'icon' => 'bi-activity',
                    'type' => 'line',
                    'labels' => $electricityTrend->keys()->values(),
                    'filterOptions' => [
                        ['value' => 'total-electricity', 'label' => 'Total'],
                        ['value' => 'Main', 'label' => 'Main'],
                        ['value' => 'FWS', 'label' => 'FWS'],
                        ['value' => 'Kreutz', 'label' => 'Kreutz'],
                        ['value' => 'Lantaka', 'label' => 'Lantaka'],
                        ['value' => 'all-electricity', 'label' => 'All Campuses'],
                    ],
                    'datasets' => collect([
                        array_merge($this->lineDataset('Total kWh', $electricityTrend->values(), '#073f8f'), ['filterGroup' => 'total-electricity']),
                    ])->merge($electricityCampusTrend->map(function ($values, string $campus) {
                        $color = match ($campus) {
                            'Main' => '#19bceb',
                            'FWS' => '#ffc107',
                            'Kreutz' => '#0f8b4c',
                            'Lantaka' => '#d9534f',
                            default => '#6f42c1',
                        };

                        return array_merge($this->lineDataset($campus.' kWh', $values, $color), ['filterGroup' => $campus]);
                    }))->values(),
                    'showDataLabels' => true,
                    'options' => ['layout' => ['padding' => ['top' => 24]]],
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
                            'type' => 'line',
                            'label' => 'Total amount spent',
                            'data' => $monthlyLabels->map(fn (string $label) => (float) ($monthlyCosts[$label] ?? 0)),
                            'borderColor' => '#d9534f',
                            'backgroundColor' => $this->transparentColor('#d9534f'),
                            'tension' => .25,
                            'yAxisID' => 'cost',
                        ],
                        [
                            'type' => 'bar',
                            'label' => 'Total liters loaded',
                            'data' => $monthlyLabels->map(fn (string $label) => (float) ($monthlyLiters[$label] ?? 0)),
                            'backgroundColor' => '#19bceb',
                            'yAxisID' => 'liters',
                        ],
                        
                    ],
                    'options' => [
                        'scales' => [
                            'cost' => ['type' => 'linear', 'position' => 'left', 'title' => ['display' => true, 'text' => 'Total Cost (PHP)']],
                            'liters' => ['type' => 'linear', 'position' => 'right', 'title' => ['display' => true, 'text' => 'Total Liters'], 'grid' => ['drawOnChartArea' => false]],
                        ],
                    ],
                    'showDataLabels' => true,
                    'wide' => true,
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
                    'showDataLabels' => true,
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
                    'showDataLabels' => true,
                ],
            ],
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
                            'Fr.Ernesto Carretero (FEC) Building' => '#073f8f',
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
                    'showDataLabels' => true,
                    'options' => ['layout' => ['padding' => ['top' => 24]]],
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
        $highestTransactionOffice = $serviceByOffice->isEmpty()
            ? ['name' => 'N/A', 'value' => 0]
            : ['name' => $serviceByOffice->keys()->first(), 'value' => (int) $serviceByOffice->values()->first()];
        $lowestTransactionOffice = $serviceByOffice->isEmpty()
            ? ['name' => 'N/A', 'value' => 0]
            : ['name' => $serviceByOffice->keys()->last(), 'value' => (int) $serviceByOffice->values()->last()];

        return [
            'pageTitle' => 'Student Service Volume',
            'pageIcon' => 'bi-people',
            'description' => 'Student transaction graphs by office/unit and month.',
            'createRoute' => route('student-service-volumes.create'),
            'recordsRoute' => route('student-service-volumes.index'),
            'metrics' => [
                ['label' => 'Student transactions', 'value' => number_format($services->sum('student_transactions_count')), 'hint' => 'Across visible records'],
                ['label' => 'Office with highest transaction', 'value' => $highestTransactionOffice['name'], 'hint' => number_format($highestTransactionOffice['value'])],
                ['label' => 'Office with lowest transaction', 'value' => $lowestTransactionOffice['name'], 'hint' => number_format($lowestTransactionOffice['value'])],
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
            ->groupBy(fn ($record) => $this->monthKey((int) $record->reporting_year, (int) $record->reporting_month))
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

    private function monthKey(int $year, int $month): string
    {
        return $year.'-'.$this->monthName($month);
    }

    private function monthLabel(int $year, int $month): string
    {
        return $year.' '.$this->monthName($month);
    }

    private function waterBillsData(Request $request): array
    {
        $waterBills = WaterBill::visibleTo($request->user())
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();

        $monthlyPeriods = $waterBills
            ->sortBy(fn ($record) => ($record->reporting_year * 100) + $record->reporting_month)
            ->map(fn ($record) => [
                'key' => $this->monthKey((int) $record->reporting_year, (int) $record->reporting_month),
                'label' => $this->monthLabel((int) $record->reporting_year, (int) $record->reporting_month),
            ])
            ->unique('key')
            ->values();
        $monthlyLabels = $monthlyPeriods->pluck('label')->values();

        $facilityTrends = collect(WaterBill::FACILITY_FIELDS)
            ->mapWithKeys(function (string $label, string $field) use ($waterBills, $monthlyPeriods) {
                $trend = $this->monthlyTrend($waterBills, fn (WaterBill $record) => (float) ($record->{$field} ?? 0));

                return [$label => $monthlyPeriods->map(fn (array $period) => (float) ($trend[$period['key']] ?? 0))];
            });

        $latestBills = $waterBills
            ->sortByDesc(fn (WaterBill $record) => ($record->reporting_year * 100) + (int) $record->reporting_month)
            ->take(5);

        $last5MonthsTotal = $latestBills->sum(fn (WaterBill $record) => $record->totalBill());

        $latestBill = $waterBills
            ->sortByDesc(fn (WaterBill $record) => ($record->reporting_year * 100) + (int) $record->reporting_month)
            ->first();

        $facilityMonthlyData = collect(WaterBill::FACILITY_FIELDS)
            ->mapWithKeys(function (string $label, string $field) use ($waterBills, $monthlyPeriods) {
                return [$label => $monthlyPeriods->map(fn (array $period) => (float) ($this->monthlyTrend($waterBills, fn (WaterBill $record) => (float) ($record->{$field} ?? 0))[$period['key']] ?? 0))];
            });

        $volatilityIndex = $this->calculateVolatilityIndex($facilityMonthlyData);

        $topContributor = $latestBill?->topContributor();

        return [
            'pageTitle' => 'Water Consumption',
            'pageIcon' => 'bi-droplet',
            'description' => 'Water bill charts and consumption summaries by facility.',
            'createRoute' => route('water-bills.create'),
            'recordsRoute' => route('water-bills.index'),
            'metrics' => [
                ['label' => 'Total (Last 5 months)', 'value' => '₱' . $this->formatDecimal($last5MonthsTotal), 'hint' => 'Sum of all facilities'],
                ['label' => 'Top contributor', 'value' => $topContributor['facility'] ?? 'N/A', 'hint' => '₱' . ($topContributor ? $this->formatDecimal($topContributor['amount']) : '0.00') . ' - ' . ($latestBill ? $latestBill->reporting_year . ' ' . $this->monthName((int) $latestBill->reporting_month) : 'N/A')],
                ['label' => 'Most volatile location', 'value' => $volatilityIndex['facility'] ?? 'N/A', 'hint' => 'Volatility Index: ' . ($volatilityIndex['index'] ?? '0.00')],
            ],
            'charts' => [
                [
                    'id' => 'waterBillTrendChart',
                    'title' => 'Monthly Water Bill by Facility',
                    'icon' => 'bi-activity',
                    'type' => 'line',
                    'labels' => $monthlyLabels,
                    'filterOptions' => collect(WaterBill::FACILITY_FIELDS)
                        ->map(fn (string $label) => ['value' => $label, 'label' => $label])
                        ->prepend(['value' => 'all-facilities', 'label' => 'All Facilities'])
                        ->values(),
                    'datasets' => $facilityTrends->map(function (Collection $values, string $facility) {
                        $colors = [
                            'LANTAKA ANNEX A' => '#073f8f',
                            'LANTAKA OLD 4-ST' => '#19bceb',
                            'JR KITCHEN' => '#ffc107',
                            'MAIN' => '#0f8b4c',
                            'FWS' => '#d9534f',
                            'PPO Shop' => '#6f42c1',
                            'AUX/ OLD DORM' => '#8e44ad',
                        ];
                        $color = $colors[$facility] ?? '#8e44ad';

                        return array_merge($this->lineDataset($facility, $values, $color), ['filterGroup' => $facility]);
                    })->values(),
                    'options' => ['layout' => ['padding' => ['top' => 24]]],
                    'wide' => true,
                ],
                [
                    'id' => 'waterBillComparisonChart',
                    'title' => 'Water Bill Comparison by Facility',
                    'icon' => 'bi-bar-chart',
                    'type' => 'bar',
                    'labels' => $monthlyLabels,
                    'datasets' => $facilityMonthlyData->map(function (Collection $values, string $facility) {
                        $colors = [
                            'LANTAKA ANNEX A' => '#073f8f',
                            'LANTAKA OLD 4-ST' => '#19bceb',
                            'JR KITCHEN' => '#ffc107',
                            'MAIN' => '#0f8b4c',
                            'FWS' => '#d9534f',
                            'PPO Shop' => '#6f42c1',
                            'AUX/ OLD DORM' => '#8e44ad',
                        ];
                        $color = $colors[$facility] ?? '#8e44ad';

                        return [
                            'label' => $facility,
                            'data' => $values,
                            'backgroundColor' => $color,
                            'borderColor' => $color,
                            'borderWidth' => 1,
                            'filterGroup' => $facility,
                        ];
                    })->values(),
                    'filterOptions' => collect(WaterBill::FACILITY_FIELDS)
                        ->map(fn (string $label) => ['value' => $label, 'label' => $label])
                        ->prepend(['value' => 'all-facilities', 'label' => 'All Facilities'])
                        ->values(),
                    'options' => ['layout' => ['padding' => ['top' => 24]]],
                    'wide' => true,
                ],
            ],
        ];
    }

    private function calculateVolatilityIndex(Collection $facilityData): array
    {
        $volatilityScores = [];

        foreach ($facilityData as $facility => $values) {
            if ($values->count() < 2) {
                $volatilityScores[$facility] = 0;
                continue;
            }

            $values = $values->filter(fn ($v) => $v !== null);
            if ($values->isEmpty()) {
                $volatilityScores[$facility] = 0;
                continue;
            }

            $mean = $values->avg();
            if ($mean == 0) {
                $volatilityScores[$facility] = 0;
                continue;
            }

            $variance = $values->map(fn ($v) => pow($v - $mean, 2))->avg();
            $stdDev = sqrt($variance);
            $volatilityScores[$facility] = round(($stdDev / $mean) * 100, 2);
        }

        $maxFacility = collect($volatilityScores)->sortDesc()->first() ?? 0;
        $topFacility = collect($volatilityScores)->sortDesc()->keys()->first() ?? null;

        return [
            'facility' => $topFacility,
            'index' => $maxFacility,
        ];
    }
}
