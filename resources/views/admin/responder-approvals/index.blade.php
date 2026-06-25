@extends('layouts.app')

@section('title', 'Responder Approvals - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-person-check"></i> Responder Approvals</div>
            <span class="text-muted">{{ number_format($pendingResponders->count()) }} pending</span>
        </div>
        <div class="portal-panel-body">
            <p class="text-muted mb-0">Approve registered responders before they can access the dashboard.</p>
        </div>
    </div>

    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-list-check"></i> Pending Registrations</div>
            <span class="text-muted">-</span>
        </div>
        <div class="portal-panel-body p-0">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Office</th>
                            <th>Email</th>
                            <th>Reports</th>
                            <th>Registered</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingResponders as $responder)
                            <tr>
                                <td>{{ $responder->name }}</td>
                                <td>{{ $responder->office_name }}</td>
                                <td>{{ $responder->email }}</td>
                                <td>{{ implode(', ', $responder->reportTypeLabels()) }}</td>
                                <td>{{ $responder->created_at->format('M d, Y h:i A') }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <form method="POST" action="{{ route('responder-approvals.approve', $responder) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-primary" type="submit">
                                                <i class="bi bi-check-lg me-1"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('responder-approvals.reject', $responder) }}" onsubmit="return confirm('Reject this responder registration?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="bi bi-x-lg me-1"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-muted py-4" colspan="6">No pending responder registrations.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
