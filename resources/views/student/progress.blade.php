@extends('layouts.app')

@section('title', 'Progress - LIMS')

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link active" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.notifications') }}"><i class="fas fa-bell"></i> Notifications</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'View Progress')

@section('main-content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card card-custom p-4 text-center">
            <h6 class="text-muted">Total Logs</h6>
            <h2 class="fw-bold text-primary">{{ $logs->count() }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-4 text-center">
            <h6 class="text-muted">Approved</h6>
            <h2 class="fw-bold text-success">{{ $logs->where('status', 'approved')->count() }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-4 text-center">
            <h6 class="text-muted">Completion Rate</h6>
            @php
                $rate = $logs->count() > 0 ? round(($logs->where('status', 'approved')->count() / $logs->count()) * 100) : 0;
            @endphp
            <h2 class="fw-bold text-info">{{ $rate }}%</h2>
        </div>
    </div>
</div>

<div class="card card-custom p-4 mt-4">
    <h5 class="fw-bold mb-4">Weekly Progress</h5>
    @if($internship)
    <div class="row">
        @for($week = 1; $week <= $internship->total_weeks; $week++)
            @php
                $weekLogs = $logs->where('week_number', $week);
                $hasApproved = $weekLogs->where('status', 'approved')->count() > 0;
                $hasPending = $weekLogs->where('status', 'pending')->count() > 0;
                $hasRejected = $weekLogs->where('status', 'rejected')->count() > 0;
            @endphp
            <div class="col-md-2 col-4 mb-3">
                <div class="card p-2 text-center {{ $hasApproved ? 'border-success' : ($hasPending ? 'border-warning' : ($hasRejected ? 'border-danger' : 'border-secondary')) }}">
                    <small class="text-muted">Week {{ $week }}</small>
                    @if($hasApproved)
                        <i class="fas fa-check-circle text-success"></i>
                    @elseif($hasPending)
                        <i class="fas fa-clock text-warning"></i>
                    @elseif($hasRejected)
                        <i class="fas fa-times-circle text-danger"></i>
                    @else
                        <i class="fas fa-minus-circle text-secondary"></i>
                    @endif
                </div>
            </div>
        @endfor
    </div>
    @else
    <p class="text-muted">No internship data available.</p>
    @endif
</div>
@endsection
