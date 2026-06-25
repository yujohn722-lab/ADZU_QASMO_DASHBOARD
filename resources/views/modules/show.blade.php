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

            @if ($reportReview)
                <div class="alert {{ $reportReview->status === 'accepted' ? 'alert-success' : ($reportReview->status === 'changes_requested' ? 'alert-warning' : ($reportReview->status === 'rejected' ? 'alert-danger' : 'alert-info')) }}">
                    <div class="fw-semibold">Review Status: {{ $reportReview->statusLabel() }}</div>
                    @if ($reportReview->admin_message)
                        <div class="mt-1">{!! nl2br(e($reportReview->admin_message)) !!}</div>
                    @endif
                    @if ($reportReview->reviewer)
                        <div class="small mt-1">Reviewed by {{ $reportReview->reviewer->name }} {{ $reportReview->reviewed_at ? 'on '.$reportReview->reviewed_at->format('M d, Y h:i A') : '' }}</div>
                    @endif
                </div>

                @if (auth()->user()->isAdmin())
                    <div class="portal-panel mb-3 no-print">
                        <div class="portal-panel-header">
                            <div class="title"><i class="bi bi-clipboard-check"></i> Admin Review</div>
                            <span class="text-muted">Submitted by {{ $reportReview->respondent?->name ?? 'Respondent' }}</span>
                        </div>
                        <div class="portal-panel-body">
                            <form method="POST" action="{{ route('report-reviews.update-status', $reportReview) }}" class="row g-3">
                                @csrf
                                <div class="col-md-4">
                                    <label class="form-label" for="status">Decision</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="accepted">Accept</option>
                                        <option value="rejected">Reject</option>
                                        <option value="changes_requested">Recommend Changes</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="admin_message">Message to respondent</label>
                                    <textarea class="form-control" id="admin_message" name="admin_message" rows="2" placeholder="Required for rejection or recommended changes">{{ old('admin_message') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit"><i class="bi bi-send me-1"></i> Submit Review</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
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

                            @if (($field['type'] ?? null) === 'solar_building_inputs')
                                <tr>
                                    <th style="width: 34%;">{{ $field['label'] ?? 'Building solar entries' }}</th>
                                    <td>
                                        @php
                                            $readonlySolarBuildingInputs = true;
                                        @endphp
                                        @include('modules.partials._solar_building_inputs')
                                    </td>
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
