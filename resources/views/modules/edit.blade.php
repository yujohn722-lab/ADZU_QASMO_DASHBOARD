@extends('layouts.app')

@section('title', 'Edit '.$title.' - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi {{ $icon }}"></i> Edit {{ $title }}</div>
            <span class="text-muted">-</span>
        </div>
        <div class="portal-panel-body">
            <form method="POST" action="{{ route($routeName.'.update', $record) }}">
                @method('PUT')
                @include('modules._form')
            </form>
        </div>
    </div>
@endsection
