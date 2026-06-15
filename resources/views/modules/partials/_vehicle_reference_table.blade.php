<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <thead>
            <tr>
                <th class="text-center" style="width: 56px;">#</th>
                <th>Vehicle Name</th>
                <th style="width: 160px;">Fuel Type</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vehicleTableRows as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['vehicle_name'] }}</td>
                    <td>{{ $row['fuel_type'] }}</td>
                    <td>{{ $row['notes'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
