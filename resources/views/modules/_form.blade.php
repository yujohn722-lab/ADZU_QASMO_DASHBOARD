@csrf

@if ($placeholderMessage)
    <div class="alert alert-info">{{ $placeholderMessage }}</div>
@endif

<div class="row g-3">
    @foreach ($fields as $field)
        @php
            $type = $field['type'] ?? 'text';
        @endphp

        @if ($type === 'vehicle_table')
            <div class="{{ $field['col'] ?? 'col-12' }}">
                <label class="form-label">{{ $field['label'] ?? 'Vehicle list' }}</label>
                @include('modules.partials._vehicle_reference_table')
            </div>
            @continue
        @endif

        @if ($type === 'solar_building_inputs')
            <div class="{{ $field['col'] ?? 'col-12' }}">
                <label class="form-label">{{ $field['label'] ?? 'Building solar entries' }}</label>
                @include('modules.partials._solar_building_inputs')
                @error('solar_buildings')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
            @continue
        @endif

        @php
            $name = $field['name'];
            $value = old($name, $record->{$name} ?? '');
            $required = in_array('required', $field['rules'] ?? [], true);
        @endphp
        <div class="{{ $field['col'] ?? 'col-md-6' }}">
            <label class="form-label" for="{{ $name }}">
                {{ $field['label'] }}
                @if ($required)
                    <span class="text-danger">*</span>
                @endif
            </label>

            @if ($type === 'textarea')
                <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" rows="3">{{ $value }}</textarea>
            @elseif ($type === 'month')
                <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" @required($required)>
                    <option value="">Select month</option>
                    @foreach ($months as $monthValue => $monthLabel)
                        <option value="{{ $monthValue }}" @selected((string) $value === (string) $monthValue)>{{ $monthLabel }}</option>
                    @endforeach
                </select>
            @elseif ($type === 'select')
                <select class="form-select @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" @required($required)>
                    <option value="">Select {{ str_replace('_', ' ', $name) }}</option>
                    @if ($name === 'building_name')
                        @foreach (($buildings ?? []) as $buildingValue => $buildingLabel)
                            <option value="{{ $buildingValue }}" @selected((string) $value === (string) $buildingValue)>{{ $buildingLabel }}</option>
                        @endforeach
                    @endif
                </select>
            @else
                <input
                    class="form-control @error($name) is-invalid @enderror"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    type="{{ $type }}"
                    value="{{ $value }}"
                    @isset($field['step']) step="{{ $field['step'] }}" @endisset
                    @if (! empty($field['readonly'])) readonly @endif
                    @required($required)
                >
            @endif

            @error($name)
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endforeach
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i> Save Record</button>
    <a class="btn btn-outline-secondary" href="{{ route($routeName.'.index') }}"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
