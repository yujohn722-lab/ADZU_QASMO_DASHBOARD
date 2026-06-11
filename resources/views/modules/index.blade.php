@extends('layouts.app')

@section('title', $title.' - Energy Crisis Dashboard')

@section('content')
    @php
        $fieldNames = collect($fields)->pluck('name');
    @endphp

    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi {{ $icon }}"></i> {{ $title }}</div>
            <a class="btn btn-sm btn-primary no-print" href="{{ route($routeName.'.create') }}"><i class="bi bi-plus-lg me-1"></i> Create</a>
        </div>
        <div class="portal-panel-body">
            <p class="text-muted mb-3">{{ $description }}</p>

            @if ($placeholderMessage)
                <div class="alert alert-info">{{ $placeholderMessage }}</div>
            @endif

            <form method="GET" action="{{ route($routeName.'.index') }}" class="row g-2 align-items-end mb-3 no-print">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Search</label>
                    <input class="form-control" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search respondent or notes">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Respondent</label>
                    <input class="form-control" type="text" name="respondent_name" value="{{ $filters['respondent_name'] ?? '' }}">
                </div>

                @if ($fieldNames->contains('reporting_month'))
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Month</label>
                        <select class="form-select" name="reporting_month">
                            <option value="">All</option>
                            @foreach ($months as $monthValue => $monthLabel)
                                <option value="{{ $monthValue }}" @selected(($filters['reporting_month'] ?? '') == $monthValue)>{{ $monthLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($fieldNames->contains('reporting_year'))
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Year</label>
                        <input class="form-control" type="number" name="reporting_year" value="{{ $filters['reporting_year'] ?? '' }}" placeholder="2026">
                    </div>
                @endif

                @if ($fieldNames->contains('week_number'))
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Week</label>
                        <input class="form-control" type="number" name="week_number" value="{{ $filters['week_number'] ?? '' }}" min="1" max="53">
                    </div>
                @endif

                @if ($routeName === 'electricity-consumptions')
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Campus</label>
                        <select class="form-select" name="campus">
                            <option value="">All</option>
                            @foreach (['Salvador', 'Kreutz', 'Lantaka'] as $campus)
                                <option value="{{ $campus }}" @selected(request('campus') === $campus)>{{ $campus }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($fieldNames->contains('office_unit_name'))
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Office/Unit</label>
                        <input class="form-control" type="text" name="office_unit_name" value="{{ $filters['office_unit_name'] ?? '' }}">
                    </div>
                @endif

                @if ($fieldNames->contains('solar_panel_id'))
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Solar Panel ID</label>
                        <input class="form-control" type="text" name="solar_panel_id" value="{{ $filters['solar_panel_id'] ?? '' }}">
                    </div>
                @endif

                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a class="btn btn-outline-secondary" href="{{ route($routeName.'.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            @foreach ($tableColumns as $field => $label)
                                <th>{{ $label }}</th>
                            @endforeach
                            <th class="text-end no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                @foreach ($tableColumns as $field => $label)
                                    @php $value = $record->{$field}; @endphp
                                    <td>
                                        @if ($field === 'reporting_month')
                                            {{ $months[$value] ?? $value }}
                                        @elseif (is_numeric($value) && ! in_array($field, ['reporting_year', 'week_number'], true))
                                            {{ number_format((float) $value, 2) }}
                                        @else
                                            {{ \Illuminate\Support\Str::limit((string) $value, 70) }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-end no-print">
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-outline-primary" href="{{ route($routeName.'.show', $record) }}" title="View"><i class="bi bi-eye"></i></a>
                                        <a class="btn btn-outline-secondary" href="{{ route($routeName.'.edit', $record) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route($routeName.'.destroy', $record) }}" onsubmit="return confirm('Delete this record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($tableColumns) + 1 }}" class="text-center text-muted py-4">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 no-print">
                {{ $records->links() }}
            </div>
        </div>
    </div>
@endsection
