@extends('layouts.app')

@section('title', isset($waterBill) ? 'Edit Water Bill - Energy Crisis Dashboard' : 'Create Water Bill - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title">
                <i class="bi bi-droplet"></i>
                {{ isset($waterBill) ? 'Edit Water Bill' : 'Create Water Bill' }}
            </div>
        </div>
        <div class="portal-panel-body">
            <form method="POST" action="{{ isset($waterBill) ? route('water-bills.update', $waterBill) : route('water-bills.store') }}">
                @csrf
                @if (isset($waterBill))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Reporting Year</label>
                        <input type="number" class="form-control @error('reporting_year') is-invalid @enderror" name="reporting_year" value="{{ old('reporting_year', $waterBill->reporting_year ?? '') }}" required>
                        @error('reporting_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reporting Month</label>
                        <select class="form-select @error('reporting_month') is-invalid @enderror" name="reporting_month" required>
                            <option value="">Select Month</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected(old('reporting_month', $waterBill->reporting_month ?? null) == $m)>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        @error('reporting_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Responder Name</label>
                        <input type="text" class="form-control @error('responder_name') is-invalid @enderror" name="responder_name" value="{{ old('responder_name', $waterBill->responder_name ?? '') }}">
                        @error('responder_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <h6 class="mb-3">Water Bill by Facility (₱)</h6>
                    </div>

                    @foreach ($facilities as $field => $label)
                        <div class="col-md-6">
                            <label class="form-label">{{ $label }}</label>
                            <input type="number" step="0.01" class="form-control @error($field) is-invalid @enderror" name="{{ $field }}" value="{{ old($field, $waterBill->{$field} ?? '') }}">
                            @error($field)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($waterBill) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('water-bills.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-1"></i>
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
