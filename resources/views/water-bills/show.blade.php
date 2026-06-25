@extends('layouts.app')

@section('title', 'Water Bill - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title">
                <i class="bi bi-droplet"></i>
                Water Bill Record
            </div>
            <div class="d-flex gap-2 no-print">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('water-bills.edit', $waterBill) }}"><i class="bi bi-pencil me-1"></i> Edit</a>
                <form method="POST" action="{{ route('water-bills.destroy', $waterBill) }}" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
                </form>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('water-bills.index') }}"><i class="bi bi-list me-1"></i> Back</a>
            </div>
        </div>
        <div class="portal-panel-body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Reporting Period</label>
                        <div class="form-control-plaintext">
                            {{ $waterBill->reporting_year }} - {{ \Carbon\Carbon::create()->month($waterBill->reporting_month)->format('F') }}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Responder Name</label>
                        <div class="form-control-plaintext">
                            {{ $waterBill->responder_name ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                @foreach ($facilities as $field => $label)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ $label }}</label>
                            <div class="form-control-plaintext">
                                ₱{{ number_format($waterBill->{$field} ?? 0, 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Total Bill:</strong> ₱{{ number_format($waterBill->totalBill(), 2) }}
                    </div>
                </div>
                @if ($topContributor = $waterBill->topContributor())
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <strong>Top Contributor:</strong> {{ $topContributor['facility'] }} (₱{{ number_format($topContributor['amount'], 2) }})
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
