@extends('layouts.app')

@section('title', 'Dashboard - Energy Crisis Dashboard')

@section('content')
    <div class="row g-3 mb-2">
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <div class="label">Total Electricity Consumption</div>
                <div class="value">{{ number_format($summary['totalElectricity'], 2) }}</div>
                <div class="text-muted small">kWh across campuses</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <div class="label">Latest Diesel / Gasoline Avg</div>
                <div class="value">{{ number_format($summary['latestDieselAverage'] ?? 0, 2) }} / {{ number_format($summary['latestGasolineAverage'] ?? 0, 2) }}</div>
                <div class="text-muted small">Latest weekly price averages</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <div class="label">Solar Generated</div>
                <div class="value">{{ number_format($summary['totalSolarGenerated'], 2) }}</div>
                <div class="text-muted small">kWh, estimated savings {{ number_format($summary['totalSolarSavings'], 2) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <div class="label">Estimated Savings</div>
                <div class="value">{{ number_format($summary['totalEstimatedSavings'], 2) }}</div>
                <div class="text-muted small">Reduced travel, utilities, activities</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="portal-panel">
                <div class="portal-panel-header">
                    <div class="title"><i class="bi bi-graph-up"></i> Weekly Fuel Price Movement</div>
                    <span class="text-muted">-</span>
                </div>
                <div class="portal-panel-body chart-box">
                    <canvas id="fuelChart"></canvas>
                </div>
            </div>

            <div class="portal-panel">
                <div class="portal-panel-header">
                    <div class="title"><i class="bi bi-lightning-charge"></i> Electricity Consumption</div>
                    <span class="text-muted">-</span>
                </div>
                <div class="portal-panel-body">
                    <div class="row g-3">
                        <div class="col-lg-6 chart-box">
                            <canvas id="campusChart"></canvas>
                        </div>
                        <div class="col-lg-6 chart-box">
                            <canvas id="buildingChart"></canvas>
                        </div>
                    </div>
                    <div class="mt-3 chart-box">
                        <canvas id="electricityTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="portal-panel">
                <div class="portal-panel-header">
                    <div class="title"><i class="bi bi-sun"></i> Solar and Services Performance</div>
                    <span class="text-muted">-</span>
                </div>
                <div class="portal-panel-body">
                    <div class="row g-3">
                        <div class="col-lg-6 chart-box">
                            <canvas id="solarChart"></canvas>
                        </div>
                        <div class="col-lg-6 chart-box">
                            <canvas id="serviceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="portal-panel">
                <div class="portal-panel-header">
                    <div class="title"><i class="bi bi-box-arrow-up-right"></i> Shortcuts</div>
                    <span class="text-muted">-</span>
                </div>
                <div class="portal-panel-body shortcut-list">
                    <div class="list-group">
                        <a href="{{ route('fuel-prices.create') }}" class="list-group-item list-group-item-action"><i class="bi bi-fuel-pump me-2"></i> Encode Weekly Fuel Prices</a>
                        <a href="{{ route('electricity-consumptions.create') }}" class="list-group-item list-group-item-action"><i class="bi bi-lightning-charge me-2"></i> Encode Electricity Consumption</a>
                        <a href="{{ route('solar-performances.create') }}" class="list-group-item list-group-item-action"><i class="bi bi-sun me-2"></i> Encode Solar Performance</a>
                        <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-printer me-2"></i> Generate Monthly Report</a>
                    </div>
                </div>
            </div>

            <div class="portal-panel">
                <div class="portal-panel-header">
                    <div class="title"><i class="bi bi-info-circle"></i> Monthly Briefing Summary</div>
                    <span class="text-muted">-</span>
                </div>
                <div class="portal-panel-body">
                    <table class="table table-sm align-middle">
                        <tbody>
                            <tr>
                                <th>Highest-consuming building</th>
                                <td>{{ $summary['highestBuilding']['label'] }} ({{ number_format($summary['highestBuilding']['value'], 2) }} kWh)</td>
                            </tr>
                            <tr>
                                <th>Highest fuel price</th>
                                <td>{{ number_format($summary['highestFuelPrice'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Lowest fuel price</th>
                                <td>{{ number_format($summary['lowestFuelPrice'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Student transactions</th>
                                <td>{{ number_format($summary['totalServiceTransactions']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="portal-panel">
                <div class="portal-panel-header">
                    <div class="title"><i class="bi bi-cash-coin"></i> Savings Categories</div>
                    <span class="text-muted">-</span>
                </div>
                <div class="portal-panel-body chart-box">
                    <canvas id="savingsChart"></canvas>
                </div>
            </div>

            <div class="portal-panel">
                <div class="portal-panel-header">
                    <div class="title"><i class="bi bi-truck"></i> Fuel and Vehicle Use</div>
                    <span class="text-muted">-</span>
                </div>
                <div class="portal-panel-body">
                    <div class="alert alert-info mb-2">Fuel and Vehicle Use inputs will be added later.</div>
                    <div class="small text-muted">{{ $summary['fuelVehicleCount'] }} placeholder record(s) available.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const portalColors = ['#073f8f', '#19bceb', '#ffc107', '#0f8b4c', '#d9534f', '#6f42c1'];

    function renderChart(id, type, labels, datasets, options = {}) {
        const element = document.getElementById(id);
        if (!element) return;
        new Chart(element, {
            type,
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                ...options
            }
        });
    }

    renderChart('fuelChart', 'line', @json($charts['fuelLabels']), [
        { label: 'Diesel average', data: @json($charts['dieselAverages']), borderColor: '#073f8f', backgroundColor: 'rgba(7,63,143,.12)', tension: .25 },
        { label: 'Gasoline average', data: @json($charts['gasolineAverages']), borderColor: '#19bceb', backgroundColor: 'rgba(25,188,235,.12)', tension: .25 },
        { label: 'Shell Fuel Save Diesel', data: @json($charts['shellDiesel']), borderColor: '#ffc107', backgroundColor: 'rgba(255,193,7,.12)', tension: .25 },
        { label: 'Petron Diesel MAX', data: @json($charts['petronDiesel']), borderColor: '#d9534f', backgroundColor: 'rgba(217,83,79,.12)', tension: .25 },
        { label: 'Caltex Diesel', data: @json($charts['caltexDiesel']), borderColor: '#0f8b4c', backgroundColor: 'rgba(15,139,76,.12)', tension: .25 },
        { label: 'Shell Fuel Save Regular', data: @json($charts['shellRegular']), borderColor: '#6f42c1', backgroundColor: 'rgba(111,66,193,.12)', tension: .25 }
    ]);

    renderChart('campusChart', 'bar', @json($charts['electricityCampusLabels']), [
        { label: 'Campus kWh', data: @json($charts['electricityCampusValues']), backgroundColor: portalColors }
    ]);

    renderChart('buildingChart', 'bar', @json($charts['buildingLabels']), [
        { label: 'Building kWh', data: @json($charts['buildingValues']), backgroundColor: '#19bceb' }
    ], { indexAxis: 'y' });

    renderChart('electricityTrendChart', 'line', @json($charts['electricityTrendLabels']), [
        { label: 'Monthly kWh', data: @json($charts['electricityTrendValues']), borderColor: '#073f8f', backgroundColor: 'rgba(7,63,143,.12)', tension: .25 }
    ]);

    renderChart('solarChart', 'bar', @json($charts['solarPanelLabels']), [
        { label: 'Solar generated kWh', data: @json($charts['solarPanelValues']), backgroundColor: '#ffc107' }
    ]);

    renderChart('serviceChart', 'bar', @json($charts['serviceOfficeLabels']), [
        { label: 'Student transactions', data: @json($charts['serviceOfficeValues']), backgroundColor: '#0f8b4c' }
    ]);

    renderChart('savingsChart', 'doughnut', @json($charts['savingsCategoryLabels']), [
        { label: 'Estimated savings', data: @json($charts['savingsCategoryValues']), backgroundColor: portalColors }
    ]);
</script>
@endpush
