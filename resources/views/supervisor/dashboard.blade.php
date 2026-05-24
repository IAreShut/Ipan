@extends('layouts.app')

@section('title', 'Supervisor Dashboard - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/supervisor-dashboard.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link active" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.tasks')}}"><i class="fas fa-flag-checkered"></i> Tasks</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
<a class="nav-link" href="{{ route('supervisor.assigned-students') }}"><i class="fas fa-user-plus"></i> Assign Student</a>
<a class="nav-link" href="{{ route('supervisor.profile') }}"><i class="fa-solid fa-circle-user"></i> Profile</a>
@endsection

@section('page-title', 'Supervisor Dashboard')
@section('page-subtitle', 'Overview of your supervised students.')

@section('header-right')
<div class="d-flex align-items-center gap-2">
    <!-- Live Date Time -->
    <span class="badge bg-white text-dark shadow-sm px-3 py-2 d-flex align-items-center" style="border-radius: 1rem; border: 1px solid #e2e8f0;">
        <i class="far fa-clock text-primary me-2"></i>
        <span id="liveDateTime" style="font-size: 0.8rem; font-weight: 600; letter-spacing: 0.3px;">Loading...</span>
    </span>
</div>
@endsection

@section('main-content')

<div class="col-12 animate-slide-up">
    <div class="ai-digest-card">
        <div class="ai-digest-content-wrapper">
            <div class="ai-digest-icon">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="ai-digest-content">
                <h5 class="fw-bold mb-1">Supervisor's AI Digest</h5>
                <p>Good morning. You have <strong>{{ $pendingReviews }}</strong> logbooks awaiting review. <strong>{{ $alerts }}</strong> students have recent alerts. Overall submission rate is healthy.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Stats & Student List -->
    <div class="col-lg-9">
        <!-- Stats Row -->
        <div class="row g-4 mb-4 animate-slide-up delay-100">
            <div class="col-md-4">
                <div class="premium-card stat-card h-100 p-4">
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
                <div class="premium-card stat-card p-4 {{ $pendingReviews > 5 ? 'urgent' : '' }}">
                    <div class="stat-info">
                        <span class="stat-label">Pending Review</span>
                        <h3 class="stat-value">{{ $pendingReviews }}</h3>
                        @if($pendingReviews > 0)
                        <div class="stat-trend down"><i class="fas fa-arrow-up"></i> Action required</div>
                        @endif
                    </div>
                    <div class="stat-icon-wrapper icon-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="premium-card stat-card p-4 {{ $alerts > 0 ? 'urgent' : '' }}">
                    <div class="stat-info">
                        <span class="stat-label">Flags / Alerts</span>
                        <h3 class="stat-value">{{ $alerts }}</h3>
                        @if($alerts > 0)
                        <div class="stat-trend down"><i class="fas fa-exclamation-circle"></i> Needs attention</div>
                        @else
                        <div class="stat-trend up"><i class="fas fa-check-circle"></i> All good</div>
                        @endif
                    </div>
                    <div class="stat-icon-wrapper icon-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($atRiskStudents) && $atRiskStudents->count() > 0)
        <!-- At-Risk Spotlight -->
        <div class="card card-custom at-risk-card p-3 mb-4 animate-slide-up delay-200">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-exclamation-circle text-danger me-2 fs-5"></i>
                <h6 class="fw-bold m-0 text-danger">Action Required: At-Risk Students</h6>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach($atRiskStudents as $riskStudent)
                <div class="d-flex align-items-center bg-white p-2 rounded border border-danger border-opacity-25 shadow-sm">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($riskStudent->name) }}&background=random" class="rounded-circle me-2" width="24">
                    <span class="fw-bold small me-2">{{ $riskStudent->name }}</span>
                    <a href="{{ route('supervisor.review-logbook') }}" class="btn btn-sm btn-danger py-0 px-2" style="font-size: 0.7rem;">Review Now</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Student List -->
        <div class="card card-custom p-4 animate-slide-up delay-200">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold">Student List</h5>
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control form-control-sm" id="studentSearch" placeholder="Search students...">
                </div>
            </div>

            <div class="table-responsive">
                <table id="supervisorStudentTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Student Name</th>
                            <th>Company</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                        $pendingCount = $student->logEntries->where('status', 'pending')->count();
                        $approvedCount = $student->logEntries->where('status', 'approved')->count();
                        // Assuming 12 weeks is a full internship length for calculation
                        $progressPct = min(100, round(($approvedCount / 12) * 100));
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}.</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=random" class="student-avatar-lg me-3">
                                    <div>
                                        <a href="{{ route('supervisor.students.show', $student->id) }}" class="text-decoration-none">
                                            <span class="fw-bold d-block text-dark" style="transition: color 0.2s;" onmouseover="this.style.color='#1E40AF'" onmouseout="this.style.color='#212529'">{{ $student->name }}</span>
                                        </a>
                                        <small class="text-muted">{{ $student->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->company ?? 'N/A' }}</td>
                            <td>
                                <div class="progress-mini-container">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="progress-text">{{ $approvedCount }}/12 Weeks</span>
                                        <span class="progress-text">{{ $progressPct }}%</span>
                                    </div>
                                    <div class="progress-mini">
                                        <div class="progress-bar {{ $progressPct == 100 ? 'bg-success' : '' }}" role="progressbar" style="width: {{ $progressPct }}%" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($pendingCount > 0)
                                <span class="badge badge-status-pending">{{ $pendingCount }} Pending</span>
                                @else
                                <span class="badge badge-status-approved">Up to Date</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('supervisor.review-logbook') }}" class="btn-table-action action-review">
                                    <i class="fas fa-check-circle"></i> Review
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Notifications & Activity -->
    <div class="col-lg-3 animate-slide-up delay-300">
        <!-- Quick Actions Grid -->
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold mb-3">Quick Actions</h6>
            <div class="quick-action-grid">
                <a href="{{ route('supervisor.review-logbook') }}" class="quick-action-btn">
                    <i class="fas fa-check-double text-success"></i>
                    <span>Review All</span>
                </a>
                <a href="{{ route('supervisor.analytics') }}" class="quick-action-btn">
                    <i class="fas fa-chart-pie text-primary"></i>
                    <span>View Report</span>
                </a>
            </div>
        </div>

        <!-- Modern Timeline -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-4">Recent Activity</h6>
            <div class="timeline-modern">
                @forelse($recentActivity as $activity)
                <div class="timeline-item">
                    <div class="timeline-icon {{ $activity->status }}"></div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-bold d-block text-dark">{{ $activity->student->name ?? 'Unknown' }}</span>
                                <span class="text-muted" style="font-size: 0.8rem;">Week {{ $activity->week_number }} logbook <span class="{{ $activity->status === 'approved' ? 'text-success' : ($activity->status === 'rejected' ? 'text-danger' : 'text-warning') }} fw-bold">{{ ucfirst($activity->status) }}</span></span>
                            </div>
                        </div>
                        <span class="timeline-time">{{ $activity->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center p-3 text-muted">
                    <i class="fas fa-inbox fs-4 mb-2 opacity-50"></i>
                    <p class="small mb-0">No recent activity found.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/supervisor/dashboard.js') }}"></script>
@endpush