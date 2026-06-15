@extends('layouts.app')

@section('title', $title.' Details - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi {{ $icon }}"></i> {{ $title }} Details</div>
            <div class="d-flex gap-2 no-print">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route($routeName.'.index') }}"><i class="bi bi-arrow-left me-1"></i> Back</a>
                <a class="btn btn-sm btn-primary" href="{{ route($routeName.'.edit', $record) }}"><i class="bi bi-pencil me-1"></i> Edit</a>
            </div>
        </div>
        <div class="portal-panel-body">
            @if ($placeholderMessage)
                <div class="alert alert-info">{{ $placeholderMessage }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <tbody>
                        @foreach ($fields as $field)
                            @if (($field['type'] ?? null) === 'vehicle_table')
                                <tr>
                                    <th style="width: 34%;">{{ $field['label'] ?? 'Vehicle list' }}</th>
                                    <td>@include('modules.partials._vehicle_reference_table')</td>
                                </tr>
                                @continue
                            @endif

                            @php
                                $name = $field['name'];
                                $value = $record->{$name};
                            @endphp
                            <tr>
                                <th style="width: 34%;">{{ $field['label'] }}</th>
                                <td>
                                    @if ($name === 'reporting_month')
                                        {{ $months[$value] ?? ($value ?: 'Not available') }}
                                    @elseif (is_numeric($value) && ! in_array($name, ['reporting_year', 'week_number'], true))
                                        {{ number_format((float) $value, 2) }}
                                    @else
                                        {!! nl2br(e($value ?: 'Not available')) !!}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form class="no-print" method="POST" action="{{ route($routeName.'.destroy', $record) }}" onsubmit="return confirm('Delete this record?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i> Delete Record</button>
            </form>
        </div>
    </div>
@endsection
