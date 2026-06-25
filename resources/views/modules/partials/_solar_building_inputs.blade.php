<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <thead>
            <tr>
                <th>Building</th>
                <th style="width: 26%;">Generated kWh</th>
                <th style="width: 26%;">Estimated Savings</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($buildings ?? []) as $buildingValue => $buildingLabel)
                @php
                    $existing = ($solarBuildingRows ?? collect())->get($buildingValue);
                    $kwhName = "solar_buildings.$buildingValue.monthly_solar_energy_kwh";
                    $savingsName = "solar_buildings.$buildingValue.estimated_savings";
                    $kwhValue = old($kwhName, $existing?->monthly_solar_energy_kwh);
                    $savingsValue = old($savingsName, $existing?->estimated_savings);
                @endphp
                <tr>
                    <th>{{ $buildingLabel }}</th>
                    <td>
                        <input
                            class="form-control @error($kwhName) is-invalid @enderror"
                            name="solar_buildings[{{ $buildingValue }}][monthly_solar_energy_kwh]"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ $kwhValue }}"
                            @if (! empty($readonlySolarBuildingInputs)) readonly @endif
                        >
                        @error($kwhName)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </td>
                    <td>
                        <input
                            class="form-control @error($savingsName) is-invalid @enderror"
                            name="solar_buildings[{{ $buildingValue }}][estimated_savings]"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ $savingsValue }}"
                            @if (! empty($readonlySolarBuildingInputs)) readonly @endif
                        >
                        @error($savingsName)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
