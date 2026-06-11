@extends('layouts.app')

@section('title', 'Account Settings - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi bi-person-gear"></i> Account Settings</div>
            <span class="text-muted">{{ ucfirst($user->role) }}</span>
        </div>
        <div class="portal-panel-body">
            <form method="POST" action="{{ route('account.update') }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">New password</label>
                    <input class="form-control" id="password" name="password" type="password" autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirm new password</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i> Save Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
