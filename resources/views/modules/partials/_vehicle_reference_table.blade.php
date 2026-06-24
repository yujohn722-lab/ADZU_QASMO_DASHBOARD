<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <thead>
            <tr>
                <th class="text-center" style="width: 56px;">#</th>
                <th>Vehicle Name</th>
                <th style="width: 140px;">Plate No.</th>
                <th style="width: 160px;">Fuel Type</th>
                <th style="width: 120px;">Status</th>
                <th>Notes</th>
                @if (($routeName ?? null) === 'fuel-vehicle-uses')
                    <th class="text-end no-print" style="width: 190px;">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($vehicleTableRows as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row->vehicle_name }}</td>
                    <td>{{ $row->plate_number ?: '-' }}</td>
                    <td>{{ $row->fuel_type }}</td>
                    <td>
                        <span class="badge {{ $row->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $row->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $row->notes }}</td>
                    @if (($routeName ?? null) === 'fuel-vehicle-uses')
                        <td class="text-end no-print">
                            <div class="btn-group btn-group-sm">
                                <form method="POST" action="{{ route('fuel-vehicle-uses.vehicles.toggle', $row) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-outline-secondary" type="submit">
                                        {{ $row->is_active ? 'Inactive' : 'Active' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('fuel-vehicle-uses.vehicles.destroy', $row) }}" onsubmit="return confirm('Delete this vehicle? Vehicles with report history will be marked inactive instead.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger" type="submit" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
