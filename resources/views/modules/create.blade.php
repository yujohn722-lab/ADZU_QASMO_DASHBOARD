@extends('layouts.app')

@section('title', 'Create '.$title.' - Energy Crisis Dashboard')

@section('content')
    <div class="portal-panel">
        <div class="portal-panel-header">
            <div class="title"><i class="bi {{ $icon }}"></i> Create {{ $title }}</div>
            <span class="text-muted">-</span>
        </div>
        <div class="portal-panel-body">
            <form method="POST" action="{{ route($routeName.'.store') }}">
                @include('modules._form')
            </form>
        </div>
    </div>
@endsection
