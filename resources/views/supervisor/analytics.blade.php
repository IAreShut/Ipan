@extends('layouts.app')

@section('title', 'Analytics - LIMS')

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.milestones') }}"><i class="fas fa-flag-checkered"></i> Milestones</a>
<a class="nav-link active" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
@endsection

@section('page-title', 'Analytics')
@section('page-subtitle', 'View statistics and reports.')

@section('main-content')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush
<div class="row g-4">
    <div class="col-md-3">
        <div class="card card-custom p-4 text-center">
            <h6 class="text-muted">Total Students</h6>
            <h2 class="fw-bold text-primary">{{ $students->count() }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-4 text-center">
            <h6 class="text-muted">Total Logs Reviewed</h6>
            @php
                $totalReviewed = 0;
                foreach($students as $s) {
                    $totalReviewed += $s->logEntries->whereIn('status', ['approved', 'rejected'])->count();
                }
            @endphp
            <h2 class="fw-bold text-success">{{ $totalReviewed }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-4 text-center">
            <h6 class="text-muted">Pending Logs</h6>
            @php
                $pendingCount = 0;
                foreach($students as $s) {
                    $pendingCount += $s->logEntries->where('status', 'pending')->count();
                }
            @endphp
            <h2 class="fw-bold text-warning">{{ $pendingCount }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-4 text-center">
            <h6 class="text-muted">Avg. Approval Rate</h6>
            @php
                $total = 0;
                $approved = 0;
                foreach($students as $s) {
                    $total += $s->logEntries->count();
                    $approved += $s->logEntries->where('status', 'approved')->count();
                }
                $rate = $total > 0 ? round(($approved / $total) * 100) : 0;
            @endphp
            <h2 class="fw-bold text-info">{{ $rate }}%</h2>
        </div>
    </div>
</div>

<div class="card card-custom p-4 mt-4">
    <h5 class="fw-bold mb-4">Student Performance</h5>
    <div class="table-responsive">
        <table id="analyticsTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Total Logs</th>
                    <th>Approved</th>
                    <th>Rejected</th>
                    <th>Pending</th>
                    <th>Approval Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                @php
                    $logs = $student->logEntries;
                    $approvedCount = $logs->where('status', 'approved')->count();
                    $rejectedCount = $logs->where('status', 'rejected')->count();
                    $pendingCount = $logs->where('status', 'pending')->count();
                    $studentRate = $logs->count() > 0 ? round(($approvedCount / $logs->count()) * 100) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=random" class="rounded-circle me-2" width="32">
                            {{ $student->name }}
                        </div>
                    </td>
                    <td>{{ $logs->count() }}</td>
                    <td><span class="text-success">{{ $approvedCount }}</span></td>
                    <td><span class="text-danger">{{ $rejectedCount }}</span></td>
                    <td><span class="text-warning">{{ $pendingCount }}</span></td>
                    <td>
                        <div class="progress" style="width: 100px; height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $studentRate }}%"></div>
                        </div>
                        <small>{{ $studentRate }}%</small>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('js/supervisor/analytics.js') }}"></script>
@endpush
