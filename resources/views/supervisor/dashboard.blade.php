@extends('layouts.app')

@section('title', 'Supervisor Dashboard - LIMS')

@section('sidebar-menu')
<a class="nav-link active" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.milestones') }}"><i class="fas fa-flag-checkered"></i> Milestones</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
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
                <div class="card card-custom p-3 border-start border-4 border-primary h-100">
                    <small class="text-muted fw-bold">TOTAL STUDENTS</small>
                    <h3 class="fw-bold mb-0">{{ $totalStudents }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom p-3 border-start border-4 border-warning h-100">
                    <small class="text-muted fw-bold">PENDING REVIEW</small>
                    <h3 class="fw-bold mb-0">{{ $pendingReviews }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom p-3 border-start border-4 border-danger h-100">
                    <small class="text-muted fw-bold">FLAGS / ALERTS</small>
                    <h3 class="fw-bold mb-0">{{ $alerts }}</h3>
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
<script>
$(document).ready(function() {
    $('#supervisorStudentTable').DataTable();
});
</script>
@endpush
