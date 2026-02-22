@extends('layouts.app')

@section('title', 'Student Dashboard - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link active" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . $user->name . '! Here\'s your internship overview.')

@section('header-right')
<span class="badge bg-light text-dark border p-2">
    <i class="far fa-calendar-alt me-1"></i> 
    Week {{ $internship ? now()->diffInWeeks($internship->start_date) + 1 : '1' }} of {{ $internship->total_weeks ?? 12 }}
</span>
@endsection

@section('main-content')
<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-6">
        <div class="card-custom p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between">
                <div>
                    <small class="text-muted fw-bold">TOTAL LOGS</small>
                    <h3 class="mb-0 fw-bold">{{ $totalLogs }}</h3>
                </div>
                <div class="text-primary opacity-50"><i class="fas fa-file-alt fa-2x"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card-custom p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between">
                <div>
                    <small class="text-muted fw-bold">APPROVED</small>
                    <h3 class="mb-0 fw-bold">{{ $approvedLogs }}</h3>
                </div>
                <div class="text-success opacity-50"><i class="fas fa-check-circle fa-2x"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between">
                <div>
                    <small class="text-muted fw-bold">PENDING</small>
                    <h3 class="mb-0 fw-bold">{{ $pendingLogs }}</h3>
                </div>
                <div class="text-warning opacity-50"><i class="fas fa-clock fa-2x"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card-custom p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between">
                <div>
                    <small class="text-muted fw-bold">REJECTED</small>
                    <h3 class="mb-0 fw-bold">{{ $rejectedLogs }}</h3>
                </div>
                <div class="text-danger opacity-50"><i class="fas fa-times-circle fa-2x"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Logbooks -->
    <div class="col-lg-8">
        <div class="card card-custom h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold">Recent Log Entries</h5>
                <a href="{{ route('student.log-entries') }}" class="btn btn-sm btn-outline-primary">+ New Entry</a>
            </div>
            <div class="table-responsive">
                <table id="recentLogEntriesTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Week</th>
                            <th>Date</th>
                            <th>Task Summary</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                        <tr>
                            <td>W{{ $log->week_number }}</td>
                            <td>{{ $log->entry_date->format('d M Y') }}</td>
                            <td>{{ Str::limit($log->task_description, 40) }}</td>
                            <td>
                                @if($log->status === 'approved')
                                    <span class="badge badge-status-approved rounded-pill px-3">Approved</span>
                                @elseif($log->status === 'rejected')
                                    <span class="badge badge-status-rejected rounded-pill px-3">Rejected</span>
                                @elseif($log->status === 'pending')
                                    <span class="badge badge-status-pending rounded-pill px-3">Pending</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Draft</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn btn-sm btn-light text-primary"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Progress & Quick Actions -->
    <div class="col-lg-4">
        <!-- Internship Progress -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold mb-3">Internship Progress</h5>
            <div class="progress mb-2" style="height: 10px;">
                <div class="progress-bar bg-primary-custom" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between text-muted small">
                <span>Started: {{ $internship ? $internship->start_date->format('M d, Y') : 'Not set' }}</span>
                <span>{{ round($progress) }}% Completed</span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card card-custom p-4">
            <h5 class="fw-bold mb-3">Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="{{ route('student.log-entries') }}" class="btn btn-primary-custom">
                    <i class="fas fa-plus me-2"></i>Add New Log Entry
                </a>
                <a href="{{ route('student.progress') }}" class="btn btn-outline-primary">
                    <i class="fas fa-chart-line me-2"></i>View Full Progress
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#recentLogEntriesTable').DataTable({
        paging: false,
        searching: false,
        info: false
    });
});
</script>
@endpush
