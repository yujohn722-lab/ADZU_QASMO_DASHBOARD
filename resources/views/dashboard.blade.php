@extends('layouts.app')

@section('title', 'Dashboard - University Monitoring Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-speedometer2"></i> Dashboard</div>
            <div class="d-flex gap-2 no-print">
                <a class="btn btn-sm btn-outline-primary" href="{{ $recordsRoute }}"><i class="bi bi-table me-1"></i> View Records</a>
                <a class="btn btn-sm btn-primary" href="{{ $createRoute }}"><i class="bi bi-plus-lg me-1"></i> Create</a>
            </div>
        </div>
        <div class="portal-panel-body">
            <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end no-print">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Chart and graph category</label>
                    <select class="form-select" name="category" onchange="this.form.submit()">
                        @foreach ($categories as $key => $category)
                            <option value="{{ $key }}" @selected($selectedCategory === $key)>{{ $category['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi {{ $pageIcon }}"></i> {{ $pageTitle }}</div>
            <span class="text-muted">-</span>
        </div>
        <div class="portal-panel-body">
            <p class="text-muted mb-0">{{ $description }}</p>
        </div>
    </div>

    @if (! empty($metrics))
        <div class="row g-3 mb-2">
            @foreach ($metrics as $metric)
                <div class="col-xl-3 col-md-6">
                    <div class="metric-card">
                        <div class="label">{{ $metric['label'] }}</div>
                        <div class="value">{{ $metric['value'] }}</div>
                        <div class="text-muted small">{{ $metric['hint'] ?? '' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-3">
        @foreach ($charts as $chart)
            <div class="{{ ! empty($chart['wide']) ? 'col-12' : 'col-xl-6' }}">
                <div class="portal-panel">
                    <div class="portal-panel-header">
                        <div class="title"><i class="bi {{ $chart['icon'] }}"></i> {{ $chart['title'] }}</div>
                        @if (! empty($chart['filterOptions']))
                            <select class="form-select form-select-sm" style="width: 240px;" data-chart-filter="{{ $chart['id'] }}">
                                @foreach ($chart['filterOptions'] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                    <div class="portal-panel-body chart-box">
                        <canvas id="{{ $chart['id'] }}"></canvas>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="portal-panel mt-3">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-chat-left-text"></i> Responder Remarks</div>
            <span class="text-muted">{{ ($remarks ?? collect())->count() }} item(s)</span>
        </div>
        <div class="portal-panel-body">
            @if (($remarks ?? collect())->isNotEmpty())
                <div class="row g-3">
                    @foreach ($remarks as $comment)
                        <div class="col-xl-6">
                            <div class="border rounded-2 p-3 h-100 bg-light">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <div>
                                        <div class="fw-semibold">{{ $comment['respondent'] }}</div>
                                        <div class="text-muted small">{{ $comment['module'] }} - {{ $comment['period'] }}</div>
                                    </div>
                                </div>

                                <div>{!! nl2br(e($comment['recordRemarks'])) !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted">No responder remarks have been added for this report category yet.</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const chartConfigs = @json($charts);
    const pointValueLabels = {
        id: 'pointValueLabels',
        afterDatasetsDraw(chart, args, pluginOptions) {
            if (! pluginOptions.display) return;

            const { ctx } = chart;
            ctx.save();
            ctx.font = '600 11px system-ui, -apple-system, "Segoe UI", sans-serif';
            ctx.fillStyle = pluginOptions.color || '#1f2937';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';

            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                if (meta.hidden) return;

                meta.data.forEach((point, index) => {
                    const rawValue = dataset.data[index];
                    const value = typeof rawValue === 'object' ? rawValue.y : rawValue;
                    if (value === null || value === undefined || value === '') return;

                    const numericValue = Number(value);
                    const label = Number.isFinite(numericValue)
                        ? numericValue.toLocaleString(undefined, { maximumFractionDigits: 2 })
                        : value;

                    ctx.fillText(label, point.x, point.y - 8);
                });
            });

            ctx.restore();
        }
    };

    function getFilteredDatasets(config, selectedGroup) {
        if (! config.filterOptions) {
            return config.datasets;
        }

        if (! selectedGroup || selectedGroup === 'all' || selectedGroup.startsWith('all-')) {
            return config.datasets;
        }

        return config.datasets.filter(dataset => dataset.filterGroup === selectedGroup);
    }

    function chartOptions(config) {
        const configuredOptions = config.options || {};
        const configuredPlugins = configuredOptions.plugins || {};

        return {
            responsive: true,
            maintainAspectRatio: false,
            ...configuredOptions,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                ...configuredPlugins,
                pointValueLabels: {
                    display: Boolean(config.showPointLabels),
                    ...(configuredPlugins.pointValueLabels || {})
                }
            }
        };
    }

    function createChart(element, config, datasets) {
        return new Chart(element, {
            type: config.type,
            data: {
                labels: config.labels,
                datasets
            },
            options: chartOptions(config),
            plugins: [pointValueLabels]
        });
    }

    function renderChart(config) {
        const element = document.getElementById(config.id);
        if (! element) return;

        const select = document.querySelector(`[data-chart-filter="${config.id}"]`);
        const selectedFilter = select ? select.value : 'all';
        const filteredDatasets = getFilteredDatasets(config, selectedFilter);

        if (config.chartInstance) {
            config.chartInstance.destroy();
        }

        config.chartInstance = createChart(element, config, filteredDatasets);

        if (select) {
            select.addEventListener('change', () => {
                const nextFilter = select.value;
                const nextDatasets = getFilteredDatasets(config, nextFilter);

                if (config.chartInstance) {
                    config.chartInstance.destroy();
                }

                config.chartInstance = createChart(element, config, nextDatasets);
            });
        }
    }

    chartConfigs.forEach(renderChart);
</script>
@endpush
