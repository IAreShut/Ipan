@extends('layouts.app')

@section('title', 'Progress - LIMS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/student-progress.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link active" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.notifications') }}"><i class="fas fa-bell"></i> Notifications</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'View Progress')

@section('main-content')
<div class="row g-4 mb-4">
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

<h5 class="fw-bold mb-4">Weekly Progress</h5>

@if($internship)
    @php
        $currentWeek = $logs->max('week_number') ?? 1;
    @endphp

    <!-- Grid of Interactive Cards -->
    <div class="row g-4" id="weeklyGrid">
        @for($week = 1; $week <= $internship->total_weeks; $week++)
            @php
                $weekLogs = $logs->where('week_number', $week);
                $logCount = $weekLogs->count();
                $approvedCount = $weekLogs->where('status', 'approved')->count();
                $pendingCount = $weekLogs->where('status', 'pending')->count();
                $rejectedCount = $weekLogs->where('status', 'rejected')->count();
                
                $isActive = ($week == $currentWeek);
                $progressPercent = min(100, ($logCount / 5) * 100); // Assume 5 logs make a full week
                
                // Status Determination
                if ($logCount == 0) {
                    $statusStr = 'Incomplete';
                    $statusIcon = 'fas fa-minus-circle text-secondary';
                } elseif ($rejectedCount > 0) {
                    $statusStr = 'Rejected Logs';
                    $statusIcon = 'fas fa-times-circle text-danger';
                } elseif ($pendingCount > 0) {
                    $statusStr = 'Pending Approval';
                    $statusIcon = 'fas fa-clock text-warning';
                } elseif ($approvedCount == $logCount) {
                    $statusStr = 'Fully Approved';
                    $statusIcon = 'fas fa-check-circle text-success';
                } else {
                    $statusStr = 'In Progress';
                    $statusIcon = 'fas fa-spinner text-info';
                }
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('student.progress.week', $week) }}" class="text-decoration-none">
                    <div class="card week-card shadow-soft rounded-2xl h-100 {{ $isActive ? 'bg-lims-light' : 'bg-white' }}">
                        <div class="card-body p-4 pb-2 position-relative z-1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-lims-navy fw-bold mb-0">Week {{ $week }}</h5>
                                <i class="{{ $statusIcon }} fs-5" title="{{ $statusStr }}"></i>
                            </div>
                            <div class="text-muted small mb-3">
                                <i class="fas fa-file-alt me-1"></i> {{ $logCount }} of 5 logs submitted
                            </div>
                            <span class="badge bg-white shadow-sm text-dark border py-2 px-3">{{ $statusStr }}</span>
                        </div>
                        <div class="progress-bar-thin z-0">
                            <div class="progress-bar-fill {{ $progressPercent == 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $progressPercent }}%;"></div>
                        </div>
                    </div>
                </a>
            </div>
        @endfor
    </div>

@else
    <p class="text-muted">No internship data available.</p>
@endif

@endsection
