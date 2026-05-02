@extends('layouts.app')

@section('title', 'Supervisor Dashboard - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link active" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.milestones') }}"><i class="fas fa-flag-checkered"></i> Milestones</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
<a class="nav-link" href="{{ route('supervisor.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Supervisor Dashboard')
@section('page-subtitle', 'Overview of your supervised students.')

@section('main-content')
<div class="row">
    <!-- Left Column: Stats & Student List -->
    <div class="col-lg-9">
        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="premium-card stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Total Students</span>
                        <h3 class="stat-value">{{ $totalStudents }}</h3>
                    </div>
                    <div class="stat-icon-wrapper icon-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="premium-card stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Pending Review</span>
                        <h3 class="stat-value">{{ $pendingReviews }}</h3>
                    </div>
                    <div class="stat-icon-wrapper icon-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="premium-card stat-card h-100">
                    <div class="stat-info">
                        <span class="stat-label">Flags / Alerts</span>
                        <h3 class="stat-value">{{ $alerts }}</h3>
                    </div>
                    <div class="stat-icon-wrapper icon-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student List -->
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold">Student List</h5>
            </div>
            
            <div class="table-responsive">
                <table id="supervisorStudentTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Company</th>
                            <th>Pending Logs</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $pendingCount = $student->logEntries->where('status', 'pending')->count();
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=random" class="rounded-circle me-2" width="32">
                                    <div>
                                        <span class="fw-bold d-block">{{ $student->name }}</span>
                                        <small class="text-muted">{{ $student->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->company ?? 'N/A' }}</td>
                            <td>
                                @if($pendingCount > 0)
                                    <span class="badge badge-status-pending">{{ $pendingCount }} Pending</span>
                                @else
                                    <span class="badge badge-status-approved">Up to Date</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('supervisor.review-logbook') }}" class="btn btn-sm btn-primary-custom">Review</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Notifications & Activity -->
    <div class="col-lg-3">
        <div class="card card-custom p-3 bg-primary text-white mb-3">
            <h5 class="fw-bold">Logbooks to Review</h5>
            <p class="mb-4">You have {{ $pendingReviews }} pending logbooks submitted this week.</p>
            <a href="{{ route('supervisor.review-logbook') }}" class="btn btn-light text-primary w-100 fw-bold">Review All</a>
        </div>

        <div class="card card-custom p-3">
            <h6 class="fw-bold mb-3">Recent Activity</h6>
            <ul class="list-unstyled small">
                @forelse($recentActivity as $activity)
                <li class="mb-3 border-bottom pb-2">
                    <span class="{{ $activity->status === 'approved' ? 'text-success' : 'text-danger' }} fw-bold text-capitalize">{{ $activity->status }}</span>
                    {{ $activity->student->name ?? 'Unknown' }} Week {{ $activity->week_number }}
                    <div class="text-muted" style="font-size: 0.8rem;">{{ $activity->updated_at->diffForHumans() }}</div>
                </li>
                @empty
                <li class="text-muted">No recent activity.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/supervisor/dashboard.js') }}"></script>
@endpush
