@extends('layouts.app')

@section('title', 'Student Dashboard - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link active" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.notifications') }}"><i class="fas fa-bell"></i> Notifications</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . $user->name . '! Here\'s your internship overview.')

@section('header-right')
@php
    $currentWeek = 1;
    $totalWeeks = 12;
    if ($internship) {
        $totalWeeks = $internship->total_weeks ?? 12;
        $diff = now()->diffInWeeks($internship->start_date, false);
        $currentWeek = $diff >= 0 ? floor($diff) + 1 : 0;
    }
@endphp
<div class="d-flex align-items-center gap-2">
    <!-- Live Date Time -->
    <span class="badge bg-white text-dark shadow-sm px-3 py-2 d-flex align-items-center" style="border-radius: 1rem; border: 1px solid #e2e8f0;">
        <i class="far fa-clock text-primary me-2"></i>
        <span id="liveDateTime" style="font-size: 0.8rem; font-weight: 600; letter-spacing: 0.3px;">Loading...</span>
    </span>

    <!-- Internship Week -->
    <span class="badge bg-white text-dark shadow-sm px-3 py-2" style="border-radius: 1rem; border: 1px solid #e2e8f0;">
        <i class="far fa-calendar-alt text-primary me-2"></i> 
        @if($currentWeek > 0)
            Week <span class="fw-bold">{{ $currentWeek }}</span> of {{ $totalWeeks }}
        @else
            Internship <span class="fw-bold">Not Started</span>
        @endif
    </span>
</div>
@endsection

@section('main-content')
<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('student.progress') }}" class="text-decoration-none">
        <div class="premium-card stat-card">
            <div class="stat-info">
                <span class="stat-label">Total Logs</span>
                <h3 class="stat-value">{{ $totalLogs }}</h3>
            </div>
            <div class="stat-icon-wrapper icon-primary">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="premium-card stat-card">
            <div class="stat-info">
                <span class="stat-label">Approved</span>
                <h3 class="stat-value">{{ $approvedLogs }}</h3>
            </div>
            <div class="stat-icon-wrapper icon-success">
                <i class="fas fa-check"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="premium-card stat-card">
            <div class="stat-info">
                <span class="stat-label">Pending</span>
                <h3 class="stat-value">{{ $pendingLogs }}</h3>
            </div>
            <div class="stat-icon-wrapper icon-warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="premium-card stat-card">
            <div class="stat-info">
                <span class="stat-label">Rejected</span>
                <h3 class="stat-value">{{ $rejectedLogs }}</h3>
            </div>
            <div class="stat-icon-wrapper icon-danger">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Logbooks -->
    <div class="col-lg-8">
        <div class="premium-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Recent Log Entries</h5>
                    <small class="text-muted">Your latest submitted activities</small>
                </div>
                <a href="{{ route('student.log-entries') }}" class="btn btn-premium btn-premium-primary btn-sm px-3">
                    <i class="fas fa-plus"></i> New Entry
                </a>
            </div>
            <div class="table-responsive">
                <table id="recentLogEntriesTable" class="table table-premium w-100">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Date</th>
                            <th>Task Summary</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                        <tr>
                            <td class="fw-bold text-dark">W{{ $log->week_number }}</td>
                            <td>{{ $log->entry_date->format('d M Y') }}</td>
                            <td>{{ Str::limit($log->task_description, 40) }}</td>
                            <td>
                                @if($log->status === 'approved')
                                    <span class="badge-status approved">Approved</span>
                                @elseif($log->status === 'rejected')
                                    <span class="badge-status rejected">Rejected</span>
                                @elseif($log->status === 'pending')
                                    <span class="badge-status pending">Pending</span>
                                @else
                                    <span class="badge-status draft">Draft</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn-action-icon" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($log->status === 'draft')
                                <a href="{{ route('student.log-entries.edit', $log->id) }}" class="btn-action-icon ms-1" title="Edit Draft" style="color: var(--warning-text);">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Progress & Alerts -->
    <div class="col-lg-4">
        <!-- Internship Progress -->
        <a href="{{ route('student.progress') }}" class="text-decoration-none">
            <div class="premium-card p-4 mb-4" style="cursor: pointer;">
                <h5 class="fw-bold mb-1 text-dark">Internship Progress</h5>
                <p class="text-muted small mb-4">Track your overall journey</p>
                
                <div class="d-flex justify-content-between align-items-end mb-2">
                    <span class="text-dark fw-bold fs-3 lh-1">{{ round($progress) }}%</span>
                    <span class="text-muted small fw-semibold">Completed</span>
                </div>
                <div class="custom-progress-bg mb-3">
                    <div class="custom-progress-bar h-100" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex align-items-center text-muted small">
                    <i class="fas fa-flag-checkered me-2"></i>
                    <span>Started: <span class="text-dark fw-semibold">{{ $internship ? $internship->start_date->format('M d, Y') : 'Not set' }}</span></span>
                </div>
            </div>
        </a>

        <!-- Recent Alerts -->
        <div class="premium-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-dark">Recent Alerts</h5>
                <a href="{{ route('student.notifications') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" style="font-size: 0.8rem; border-color: #10b981; color: #10b981;">View All</a>
            </div>
            
            <div class="d-flex flex-column gap-3">
                @forelse($recentAlerts as $alert)
                <div class="p-3" style="background-color: #f8fafc; border-radius: 1rem; border: 1px solid #e2e8f0;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ Str::limit($alert->title, 35) }}</span>
                        <i class="fas fa-ellipsis-h text-muted" style="font-size: 0.8rem; cursor: pointer;"></i>
                    </div>
                    <div class="d-flex align-items-center text-muted mb-2" style="font-size: 0.8rem;">
                        <i class="far fa-calendar-alt me-2"></i>
                        <span>{{ $alert->created_at->format('h:i a - d M Y') }}</span>
                    </div>
                    <div class="d-flex align-items-center mt-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 24px; height: 24px; font-size: 0.7rem; margin-right: 8px; background-color: {{ strtolower($alert->type) === 'warning' ? '#ef4444' : ($alert->type === 'reminder' ? '#f59e0b' : ($alert->type === 'milestone' ? '#3b82f6' : '#10b981')) }};">
                            <i class="fas {{ strtolower($alert->type) === 'warning' ? 'fa-exclamation-triangle' : ($alert->type === 'reminder' ? 'fa-clock' : ($alert->type === 'milestone' ? 'fa-flag' : 'fa-bell')) }}"></i>
                        </div>
                        <span style="font-size: 0.85rem; color: #475569; font-weight: 500;">{{ ucfirst(str_replace('_', ' ', $alert->type)) }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4" style="background-color: #f8fafc; border-radius: 1rem; border: 1px dashed #cbd5e1;">
                    <i class="fas fa-bell-slash fs-4 mb-2 opacity-50"></i>
                    <p class="mb-0 small">No recent alerts</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/student/dashboard.js') }}"></script>
@endpush
