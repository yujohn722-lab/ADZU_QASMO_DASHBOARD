@extends('layouts.app')

@section('title', 'Water Bills - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-droplet"></i> Water Bills</div>
            <div class="d-flex gap-2 no-print">
                <a class="btn btn-sm btn-primary" href="{{ route('water-bills.create') }}"><i class="bi bi-plus-lg me-1"></i> Create</a>
            </div>
        </div>
        <div class="portal-panel-body">
            @if ($waterBills->isEmpty())
                <p class="text-muted">No water bill records found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Responder</th>
                                <th>Total Bill</th>
                                <th>Top Facility</th>
                                <th class="no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($waterBills as $bill)
                                <tr>
                                    <td>{{ $bill->reporting_year }} - {{ \Carbon\Carbon::create()->month($bill->reporting_month)->format('M') }}</td>
                                    <td>{{ $bill->responder_name ?? 'N/A' }}</td>
                                    <td><strong>₱{{ number_format($bill->totalBill(), 2) }}</strong></td>
                                    <td>
                                        @if ($top = $bill->topContributor())
                                            {{ $top['facility'] }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="no-print">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('water-bills.show', $bill) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('water-bills.edit', $bill) }}" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                            <form method="POST" action="{{ route('water-bills.destroy', $bill) }}" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $waterBills->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
