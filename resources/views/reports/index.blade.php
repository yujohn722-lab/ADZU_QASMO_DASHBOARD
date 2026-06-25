@extends('layouts.app')

@section('title', 'Reports - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel no-print">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-file-earmark-bar-graph"></i> Report Filters</div>
            <span class="text-muted">-</span>
        </div>
        <div class="portal-panel-body">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end">
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">Module</label>
                    <select class="form-select" name="module">
                        <option value="all" @selected($selectedModuleKey === 'all')>All modules</option>
                        @foreach ($modules as $key => $module)
                            <option value="{{ $key }}" @selected($selectedModuleKey === $key)>{{ $module['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">Month</label>
                    <select class="form-select" name="reporting_month">
                        <option value="">All</option>
                        @foreach ($months as $value => $label)
                            <option value="{{ $value }}" @selected(request('reporting_month') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">Year</label>
                    <input class="form-control" type="number" name="reporting_year" value="{{ request('reporting_year') }}" placeholder="2026">
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">Campus</label>
                    <select class="form-select" name="campus">
                        <option value="">All</option>
                        @foreach (['Main', 'FWS', 'Kreutz', 'Lantaka'] as $campus)
                            <option value="{{ $campus }}" @selected(request('campus') === $campus)>{{ $campus }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">Office/Unit</label>
                    <input class="form-control" type="text" name="office_unit_name" value="{{ request('office_unit_name') }}">
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">Respondent</label>
                    <input class="form-control" type="text" name="respondent_name" value="{{ request('respondent_name') }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-filter me-1"></i> Generate</button>
                    <a class="btn btn-outline-secondary" href="{{ route('reports.index') }}">Reset</a>
                    <a class="btn btn-outline-success" href="{{ route('reports.export-csv', request()->query()) }}"><i class="bi bi-download me-1"></i> Export CSV</a>
                    <button class="btn btn-outline-dark" type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print / PDF</button>
                </div>
            </form>
        </div>
    </div>

    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-bank"></i> Energy Crisis Learning Continuity Report</div>
            <span class="text-muted">{{ now()->format('F d, Y') }}</span>
        </div>
        <div class="portal-panel-body">
            <div class="mb-3">
                <h5 class="mb-1">For the Office of the President and Core Presidents Council</h5>
                <p class="text-muted mb-0">{{ $narrative }}</p>
            </div>

            <div class="row g-3 mb-3">
                @foreach ($summary as $label => $value)
                    <div class="col-xl-3 col-md-6">
                        <div class="metric-card">
                            <div class="label">{{ $label }}</div>
                            <div class="value">{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($selectedModule)
                <div class="row g-3">
                    <div class="col-xl-7">
                        <div class="portal-panel mb-0">
                            <div class="portal-panel-header">
                                <div class="title"><i class="bi bi-table"></i> {{ $selectedModule['label'] }} Records</div>
                                <span class="text-muted">{{ $records->count() }} row(s)</span>
                            </div>
                            <div class="portal-panel-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead>
                                            <tr>
                                                @foreach ($selectedModule['columns'] as $label)
                                                    <th>{{ $label }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($records as $record)
                                                <tr>
                                                    @foreach (array_keys($selectedModule['columns']) as $field)
                                                        @php $value = $record->{$field}; @endphp
                                                        <td>
                                                            @if ($field === 'reporting_month')
                                                                {{ $months[$value] ?? ($value ?: 'Not available') }}
                                                            @elseif (is_numeric($value) && ! in_array($field, ['reporting_year', 'week_number'], true))
                                                                {{ number_format((float) $value, 2) }}
                                                            @else
                                                                {{ \Illuminate\Support\Str::limit((string) $value, 60) }}
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($selectedModule['columns']) }}" class="text-center text-muted py-4">No report records found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="portal-panel mb-0">
                            <div class="portal-panel-header">
                                <div class="title"><i class="bi bi-bar-chart"></i> Report Chart</div>
                                <span class="text-muted">-</span>
                            </div>
                            <div class="portal-panel-body chart-box">
                                <canvas id="reportChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info mb-0">Select a module to include detailed tables and module-specific charts.</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const reportChart = @json($chart);
    const reportValueLabels = {
        id: 'reportValueLabels',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            ctx.save();
            ctx.font = '600 11px system-ui, -apple-system, "Segoe UI", sans-serif';
            ctx.fillStyle = '#1f2937';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';

            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                if (meta.hidden) return;

                meta.data.forEach((point, index) => {
                    const value = dataset.data[index];
                    if (value === null || value === undefined || value === '') return;

                    const numericValue = Number(value);
                    const label = Number.isFinite(numericValue)
                        ? numericValue.toLocaleString(undefined, { maximumFractionDigits: 2 })
                        : value;
                    const position = typeof point.tooltipPosition === 'function'
                        ? point.tooltipPosition()
                        : { x: point.x, y: point.y };

                    ctx.fillText(label, position.x, position.y - 8);
                });
            });

            ctx.restore();
        }
    };
    const chartElement = document.getElementById('reportChart');
    if (chartElement && reportChart.labels.length) {
        new Chart(chartElement, {
            type: reportChart.type,
            data: {
                labels: reportChart.labels,
                datasets: reportChart.datasets.map((dataset, index) => ({
                    ...dataset,
                    borderColor: ['#073f8f', '#19bceb', '#0f8b4c'][index] || '#073f8f',
                    backgroundColor: ['#073f8f', '#19bceb', '#ffc107', '#0f8b4c', '#d9534f'],
                    tension: .25
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 24 } },
                plugins: { legend: { position: 'bottom' } }
            },
            plugins: [reportValueLabels]
        });
    }
</script>
@endpush
