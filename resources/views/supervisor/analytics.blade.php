@extends('layouts.app')

@section('title', 'Analytics Dashboard - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/analytics.css') }}">
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
            <i class="fas fa-magic"></i> AI Assistant
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

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card-modern">
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
        <div class="stat-card-modern">
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
        <div class="stat-card-modern">
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
        <div class="stat-card-modern">
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
            <div style="height: 300px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-4 h-100">
            <h5 class="fw-bold mb-4">Logbook Breakdown</h5>
            @php
                $total = $totalLogbooks > 0 ? $totalLogbooks : 1;
                $pctApproved = round(($approvedLogbooks / $total) * 100);
                $pctPending = round(($pendingReviews / $total) * 100);
                $pctRejected = round(($rejectedLogbooks / $total) * 100);
            @endphp
            
            <h2 class="fw-bold">{{ $totalLogbooks }}</h2>
            <p class="text-muted small mb-4">Total submissions</p>
            
            <div class="progress progress-stacked mb-4">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pctApproved }}%"></div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pctPending }}%"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $pctRejected }}%"></div>
            </div>
            
            <ul class="list-unstyled">
                <li class="d-flex justify-content-between align-items-center mb-3">
                    <div><i class="fas fa-circle text-success small me-2"></i> Approved</div>
                    <div class="fw-bold">{{ $pctApproved }}% <span class="text-muted ms-2">{{ $approvedLogbooks }}</span></div>
                </li>
                <li class="d-flex justify-content-between align-items-center mb-3">
                    <div><i class="fas fa-circle text-warning small me-2"></i> Pending</div>
                    <div class="fw-bold">{{ $pctPending }}% <span class="text-muted ms-2">{{ $pendingReviews }}</span></div>
                </li>
                <li class="d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-circle text-danger small me-2"></i> Rejected</div>
                    <div class="fw-bold">{{ $pctRejected }}% <span class="text-muted ms-2">{{ $rejectedLogbooks }}</span></div>
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
    // Chart Data from Controller
    const chartData = {!! json_encode($chartData) !!};
    
    // Performance Trend Chart
    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.months,
            datasets: [
                {
                    label: 'Approved',
                    data: chartData.approved,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Pending',
                    data: chartData.pending,
                    borderColor: '#ffc107',
                    backgroundColor: 'transparent',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // AI Assistant Actions
    $(document).ready(function() {
        // Setup CSRF for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.ai-action').click(function(e) {
            e.preventDefault();
            const action = $(this).data('action');
            let endpoint = '';
            let payload = {};
            let title = '';

            if (action === 'summary') {
                endpoint = '/supervisor/analytics/ai-summary';
                title = 'Performance Summary';
            } else if (action === 'at-risk') {
                endpoint = '/supervisor/analytics/ai-at-risk';
                title = 'At-Risk Students';
            } else if (action === 'chat') {
                Swal.fire({
                    title: 'Ask Gemini AI',
                    input: 'text',
                    inputLabel: 'What do you want to know about your data?',
                    inputPlaceholder: 'e.g., Which student has the most rejected logs?',
                    showCancelButton: true,
                    confirmButtonText: 'Ask',
                    showLoaderOnConfirm: true,
                    preConfirm: (question) => {
                        return $.post('/supervisor/analytics/ai-chat', { question: question })
                            .then(response => {
                                if (!response.success) throw new Error(response.error);
                                return response.data;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`);
                            });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'AI Answer',
                            html: `<div style="text-align: left; font-size: 0.95rem;">${result.value.replace(/\n/g, '<br>')}</div>`,
                            icon: 'success'
                        });
                    }
                });
                return; // Stop here for chat action
            }

            // For summary and at-risk actions
            Swal.fire({
                title: 'Generating ' + title + '...',
                html: 'Gemini AI is analyzing your data.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    $.post(endpoint, payload)
                        .done(function(res) {
                            if (res.success) {
                                Swal.fire({
                                    title: title,
                                    html: `<div style="text-align: left; font-size: 0.95rem;">${res.data.replace(/\n/g, '<br>')}</div>`,
                                    icon: 'success',
                                    confirmButtonColor: '#1E3A8A'
                                });
                            } else {
                                Swal.fire('Error', res.error, 'error');
                            }
                        })
                        .fail(function(err) {
                            Swal.fire('Error', 'Failed to connect to AI server.', 'error');
                        });
                }
            });
        });

        // Initialize DataTables
        if ($.fn.DataTable) {
            var table = $('#topPerformingTable').DataTable({
                "pageLength": 5,
                "lengthChange": false,
                "language": {
                    "search": "",
                    "searchPlaceholder": "Search students..."
                }
            });
            // Move search box to be inline with title
            $('.dataTables_filter').appendTo('#topPerformingTableSearch');
            $('.dataTables_filter').addClass('mb-0');
            $('.dataTables_filter label').addClass('mb-0');
        }
    });
</script>
@endpush
