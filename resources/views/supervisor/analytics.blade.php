@extends('layouts.app')

@section('title', 'Analytics Dashboard - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/supervisor-analytics.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.tasks') }}"><i class="fas fa-flag-checkered"></i> Tasks</a>
<a class="nav-link active" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
<a class="nav-link" href="{{ route('supervisor.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Analytics Dashboard')
@section('page-subtitle', 'Track student performance and logbook insights')

@section('header-right')
<div>
    <button class="btn btn-outline-secondary me-2 rounded-pill"><i class="fas fa-filter"></i> Filter</button>
    <div class="dropdown d-inline-block">
        <button class="btn ai-btn dropdown-toggle" type="button" id="aiAssistantDropdownDesktop" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-wand-magic-sparkles"></i> AI Assistant
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="aiAssistantDropdownDesktop">
            <li><a class="dropdown-item ai-action py-2" href="#" data-action="summary"><i class="fas fa-chart-bar text-primary me-2"></i> Generate Performance Summary</a></li>
            <li><a class="dropdown-item ai-action py-2" href="#" data-action="at-risk"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Identify At-Risk Students</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item ai-action py-2" href="#" data-action="chat"><i class="fas fa-comment-dots text-info me-2"></i> Ask Your Data</a></li>
        </ul>
    </div>
</div>
@endsection

@section('main-content')
<!-- Mobile Buttons -->
<div class="d-lg-none mb-4 d-flex justify-content-end">
    <button class="btn btn-outline-secondary me-2 rounded-pill"><i class="fas fa-filter"></i> Filter</button>
    <div class="dropdown d-inline-block">
        <button class="btn ai-btn dropdown-toggle" type="button" id="aiAssistantDropdownMobile" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-magic"></i> AI Assistant
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="aiAssistantDropdownMobile">
            <li><a class="dropdown-item ai-action py-2" href="#" data-action="summary"><i class="fas fa-chart-bar text-primary me-2"></i> Generate Performance Summary</a></li>
            <li><a class="dropdown-item ai-action py-2" href="#" data-action="at-risk"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Identify At-Risk Students</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item ai-action py-2" href="#" data-action="chat"><i class="fas fa-comment-dots text-info me-2"></i> Ask Your Data</a></li>
        </ul>
    </div>
</div>

<div class="row g-4 mb-2">
    <div class="col-md-3">
        <div class="card card-custom stat-card-modern">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-muted small mb-1 fw-bold">Total Logbooks</div>
                    <h3 class="mb-0 fw-bold">{{ $totalLogbooks }}</h3>
                </div>
                <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle"><i class="fas fa-book"></i></div>
            </div>
            <div class="mt-3 text-muted small"><i class="fas fa-arrow-up text-success"></i> Across all students</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom stat-card-modern">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-muted small mb-1 fw-bold">Pending Reviews</div>
                    <h3 class="mb-0 fw-bold">{{ $pendingReviews }}</h3>
                </div>
                <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle"><i class="fas fa-clock"></i></div>
            </div>
            <div class="mt-3 text-muted small"><i class="fas fa-exclamation-circle text-warning"></i> Needs attention</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom stat-card-modern">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-muted small mb-1 fw-bold">Approved Logbooks</div>
                    <h3 class="mb-0 fw-bold">{{ $approvedLogbooks }}</h3>
                </div>
                <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle"><i class="fas fa-thumbs-up"></i></div>
            </div>
            <div class="mt-3 text-muted small">Successfully reviewed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom stat-card-modern">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="text-muted small mb-1 fw-bold">Active Tasks</div>
                    <h3 class="mb-0 fw-bold">{{ $activeTasks }}</h3>
                </div>
                <div class="icon-shape bg-info bg-opacity-10 text-info rounded-circle"><i class="fas fa-tasks"></i></div>
            </div>
            <div class="mt-3 text-muted small">Ongoing tasks</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0">Performance Trend</h5>
                <select class="form-select form-select-sm w-auto rounded-pill"><option>Monthly</option></select>
            </div>
            <div class="position-relative w-100" style="height: 380px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-4 h-100 d-flex flex-column">
            <h5 class="fw-bold mb-4 w-100 text-start">Logbook Breakdown</h5>
            <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1 py-3">
                @php
                    $total = $totalLogbooks > 0 ? $totalLogbooks : 1;
                    $pctApproved = round(($approvedLogbooks / $total) * 100);
                    $pctPending = round(($pendingReviews / $total) * 100);
                    $pctRejected = round(($rejectedLogbooks / $total) * 100);
                @endphp
                
                <div class="position-relative mx-auto mt-2 mb-4" style="width: 200px; height: 200px;">
                    <canvas id="breakdownChart"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                        <h2 class="fw-bold mb-0" style="font-size: 2.5rem; line-height: 1;">{{ $totalLogbooks }}</h2>
                        <span class="text-muted" style="font-size: 0.85rem; font-weight: 500;">Total</span>
                    </div>
                </div>
            </div>
            
            <ul class="list-unstyled mb-0">
                <li class="d-flex justify-content-between align-items-center mb-4">
                    <div class="fs-6"><i class="fas fa-circle text-success small me-2"></i> Approved</div>
                    <div class="fw-bold fs-6">{{ $pctApproved }}% <span class="text-muted ms-2">{{ $approvedLogbooks }}</span></div>
                </li>
                <li class="d-flex justify-content-between align-items-center mb-4">
                    <div class="fs-6"><i class="fas fa-circle text-warning small me-2"></i> Pending</div>
                    <div class="fw-bold fs-6">{{ $pctPending }}% <span class="text-muted ms-2">{{ $pendingReviews }}</span></div>
                </li>
                <li class="d-flex justify-content-between align-items-center">
                    <div class="fs-6"><i class="fas fa-circle text-danger small me-2"></i> Rejected</div>
                    <div class="fw-bold fs-6">{{ $pctRejected }}% <span class="text-muted ms-2">{{ $rejectedLogbooks }}</span></div>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="card card-custom p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0">Top Performing Students</h5>
        <div id="topPerformingTableSearch"></div>
    </div>
    <div class="table-responsive">
        <table id="topPerformingTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Student Info</th>
                    <th>Logbooks Submitted</th>
                    <th>Approval Rate</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students->take(5) as $student)
                @php
                    $stdLogs = $student->logEntries;
                    $stdTotal = $stdLogs->count();
                    $stdApp = $stdLogs->where('status', 'approved')->count();
                    $rate = $stdTotal > 0 ? round(($stdApp / $stdTotal) * 100) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=random" class="rounded-circle me-3" width="40">
                            <div>
                                <div class="fw-bold">{{ $student->name }}</div>
                                <div class="text-muted small">{{ $student->programme_code ?? 'Student' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="fw-bold">{{ $stdTotal }}</td>
                    <td>
                        <span class="badge bg-{{ $rate > 70 ? 'success' : ($rate > 40 ? 'warning' : 'danger') }} bg-opacity-10 text-{{ $rate > 70 ? 'success' : ($rate > 40 ? 'warning' : 'danger') }} border border-{{ $rate > 70 ? 'success' : ($rate > 40 ? 'warning' : 'danger') }} border-opacity-25 p-2 rounded-pill">
                            {{ $rate }}% Approved
                        </span>
                    </td>
                    <td><i class="fas fa-arrow-{{ $rate > 50 ? 'up text-success' : 'down text-danger' }}"></i></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.chartData = {!! json_encode($chartData) !!};
    window.breakdownData = {
        approved: {{ $approvedLogbooks }},
        pending: {{ $pendingReviews }},
        rejected: {{ $rejectedLogbooks }}
    };
</script>
<script src="{{ asset('js/supervisor/analytics.js') }}"></script>
@endpush
