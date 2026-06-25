@extends('layouts.app')

@section('title', 'Account Management - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-people-fill"></i> Account Management</div>
            <span class="text-muted">{{ number_format($users->count()) }} account(s)</span>
        </div>
        <div class="portal-panel-body">
            <p class="text-muted mb-0">Manage registered system accounts and remove accounts that are no longer active.</p>
        </div>
    </div>

    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-person-lines-fill"></i> Registered Accounts</div>
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
                            <th>Role</th>
                            <th>Status</th>
                            <th>Report Access</th>
                            <th>Registered</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $account)
                            <tr>
                                <td>{{ $account->name }}</td>
                                <td>{{ $account->office_name ?: 'Not provided' }}</td>
                                <td>{{ $account->email }}</td>
                                <td>{{ ucfirst($account->role) }}</td>
                                <td>
                                    @if ($account->isApproved())
                                        <span class="badge text-bg-success">Approved</span>
                                    @else
                                        <span class="badge text-bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>{{ implode(', ', $account->reportTypeLabels()) ?: 'None' }}</td>
                                <td>{{ $account->created_at->format('M d, Y h:i A') }}</td>
                                <td class="text-end">
                                    @if (auth()->id() === $account->id)
                                        <span class="text-muted small">Current account</span>
                                    @else
                                        <form method="POST" action="{{ route('accounts.destroy', $account) }}" onsubmit="return confirm('Remove this account from the system?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="bi bi-trash me-1"></i> Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-muted py-4" colspan="8">No registered accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
