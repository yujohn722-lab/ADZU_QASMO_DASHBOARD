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
        $user = $request->user();

        $fuelPrices = FuelPrice::visibleTo($user)
            ->orderBy('reporting_year')
            ->orderBy('week_number')
            ->get();

        $electricity = ElectricityConsumption::visibleTo($user)
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();
        $solar = SolarPerformance::visibleTo($user)
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();
        $services = StudentServiceVolume::visibleTo($user)
            ->orderBy('reporting_year')
            ->orderBy('reporting_month')
            ->get();
        $savings = EstimatedSaving::visibleTo($user)->get();
        $fuelVehicleCount = FuelVehicleUse::visibleTo($user)->count();

        $latestFuel = $fuelPrices
            ->sortByDesc(fn (FuelPrice $record) => ($record->reporting_year * 100) + $record->week_number)
            ->first();

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
        $solarTrend = $this->monthlyTrend($solar, fn ($record) => (float) $record->monthly_solar_energy_kwh);
        $serviceByOffice = $services
            ->groupBy('office_unit_name')
            ->map(fn (Collection $rows) => $rows->sum('student_transactions_count'))
            ->sortDesc();

        $solarByPanel = $solar
            ->groupBy('solar_panel_id')
            ->map(fn (Collection $rows) => round($rows->sum('monthly_solar_energy_kwh'), 2));

        $savingsByCategory = [
            'Travel' => round($savings->sum(fn ($record) => (float) $record->reduced_travel_savings), 2),
            'Utilities' => round($savings->sum(fn ($record) => (float) $record->reduced_utilities_savings), 2),
            'Activities/Events' => round($savings->sum(fn ($record) => (float) $record->reduced_activities_savings), 2),
        ];

        return view('dashboard', [
            'latestFuel' => $latestFuel,
            'summary' => [
                'totalElectricity' => round(array_sum($electricityCampus), 2),
                'electricityCampus' => $electricityCampus,
                'highestBuilding' => $highestBuilding,
                'latestDieselAverage' => $latestFuel?->averageDieselPrice(),
                'latestGasolineAverage' => $latestFuel?->averageGasolinePrice(),
                'highestFuelPrice' => $latestFuel?->highestPrice(),
                'lowestFuelPrice' => $latestFuel?->lowestPrice(),
                'totalSolarGenerated' => round($solar->sum('monthly_solar_energy_kwh'), 2),
                'totalSolarSavings' => round($solar->sum('estimated_savings'), 2),
                'totalServiceTransactions' => $services->sum('student_transactions_count'),
                'totalEstimatedSavings' => round($savings->sum('total_estimated_savings'), 2),
                'fuelVehicleCount' => $fuelVehicleCount,
            ],
            'charts' => [
                'fuelLabels' => $fuelPrices->map(fn (FuelPrice $record) => $record->reporting_year.' W'.$record->week_number)->values(),
                'dieselAverages' => $fuelPrices->map(fn (FuelPrice $record) => $record->averageDieselPrice())->values(),
                'gasolineAverages' => $fuelPrices->map(fn (FuelPrice $record) => $record->averageGasolinePrice())->values(),
                'shellDiesel' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_fuel_save_diesel)->values(),
                'petronDiesel' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->petron_diesel_max)->values(),
                'caltexDiesel' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->caltex_diesel)->values(),
                'shellRegular' => $fuelPrices->map(fn (FuelPrice $record) => (float) $record->shell_fuel_save_regular)->values(),
                'electricityCampusLabels' => array_keys($electricityCampus),
                'electricityCampusValues' => array_values($electricityCampus),
                'buildingLabels' => $buildingTotals->keys()->values(),
                'buildingValues' => $buildingTotals->values(),
                'electricityTrendLabels' => $electricityTrend->keys()->values(),
                'electricityTrendValues' => $electricityTrend->values(),
                'solarTrendLabels' => $solarTrend->keys()->values(),
                'solarTrendValues' => $solarTrend->values(),
                'solarPanelLabels' => $solarByPanel->keys()->values(),
                'solarPanelValues' => $solarByPanel->values(),
                'savingsCategoryLabels' => array_keys($savingsByCategory),
                'savingsCategoryValues' => array_values($savingsByCategory),
                'serviceOfficeLabels' => $serviceByOffice->keys()->values(),
                'serviceOfficeValues' => $serviceByOffice->values(),
            ],
        ]);
    }

    private function monthlyTrend(Collection $records, callable $valueResolver): Collection
    {
        return $records
            ->groupBy(fn ($record) => $record->reporting_year.'-'.$this->monthName((int) $record->reporting_month))
            ->map(fn (Collection $rows) => round($rows->sum($valueResolver), 2));
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
