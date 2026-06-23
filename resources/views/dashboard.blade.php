@extends('layouts.app')

@section('title', 'Dashboard - Energy Crisis Dashboard')

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
@endsection

@push('scripts')
<script>
    const chartConfigs = @json($charts);

    function getFilteredDatasets(config, selectedGroup) {
        if (! config.filterOptions) {
            return config.datasets;
        }

        if (! selectedGroup || selectedGroup === 'all-gas' || selectedGroup === 'all-diesel') {
            return config.datasets;
        }

        return config.datasets.filter(dataset => dataset.filterGroup === selectedGroup);
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

        config.chartInstance = new Chart(element, {
            type: config.type,
            data: {
                labels: config.labels,
                datasets: filteredDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                ...(config.options || {})
            }
        });

        if (select) {
            select.addEventListener('change', () => {
                const nextFilter = select.value;
                const nextDatasets = getFilteredDatasets(config, nextFilter);

                if (config.chartInstance) {
                    config.chartInstance.destroy();
                }

                config.chartInstance = new Chart(element, {
                    type: config.type,
                    data: {
                        labels: config.labels,
                        datasets: nextDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        ...(config.options || {})
                    }
                });
            });
        }
    }

    chartConfigs.forEach(renderChart);
</script>
@endpush
