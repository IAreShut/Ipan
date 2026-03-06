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
                <div class="card week-card shadow-soft rounded-2xl h-100 {{ $isActive ? 'bg-lims-light' : 'bg-white' }}" 
                     onclick="showDrillDown({{ $week }})" id="cardWeek{{ $week }}">
                    <div class="card-body p-4 position-relative z-1">
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
            </div>
        @endfor
    </div>

    <!-- Drill-Down Timeline Container -->
    <div id="drillDownContainer" class="mt-5" style="display: none;">
        <div class="card card-custom p-4 shadow-sm mx-auto position-relative" style="max-width: 800px; border-radius: 1.5rem; border: 1px solid #E5E7EB;">
            <!-- Close Button pinned inside card -->
            <button class="btn btn-sm btn-outline-secondary rounded-circle position-absolute" 
                    style="top: 20px; right: 20px; width: 32px; height: 32px; padding: 0;"
                    onclick="closeDrillDown()" title="Close Details">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="mb-4 border-bottom pb-3 pe-4">
                <h5 class="fw-bold text-lims-navy m-0" id="drillDownTitle">Details for Week X</h5>
                <p class="text-muted small mt-1 mb-0">Review your daily entries and their approval statuses</p>
            </div>
            
            <div id="timelineWrapper">
                @for($week = 1; $week <= $internship->total_weeks; $week++)
                    @php
                        $weekLogs = $logs->where('week_number', $week)->sortByDesc('entry_date');
                    @endphp
                    <div class="drill-down-content" id="weekContent{{ $week }}">
                        @if($weekLogs->count() > 0)
                            <div class="timeline">
                                @foreach($weekLogs as $log)
                                    <div class="timeline-item">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                            <div style="flex: 1; min-width: 250px;">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="fw-bold mb-0 text-dark">{{ $log->entry_date->format('j M Y, l') }}</h6>
                                                    
                                                    @if($log->status === 'approved')
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-1 ms-3" style="font-size: 0.75rem;"><i class="fas fa-check-circle me-1"></i> Approved</span>
                                                    @elseif($log->status === 'rejected')
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2 py-1 ms-3" style="font-size: 0.75rem;"><i class="fas fa-times-circle me-1"></i> Rejected</span>
                                                    @elseif($log->status === 'pending')
                                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-2 py-1 ms-3" style="font-size: 0.75rem;"><i class="fas fa-clock me-1"></i> Pending</span>
                                                    @else
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-2 py-1 ms-3" style="font-size: 0.75rem;"><i class="fas fa-file-alt me-1"></i> Draft</span>
                                                    @endif
                                                </div>
                                                <p class="text-muted mb-2 small lh-base">{{ Str::limit($log->task_description, 120) }}</p>
                                            </div>
                                            <!-- View Details Button -->
                                            <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn btn-sm btn-light border text-primary rounded-circle shadow-sm hover-primary-button" title="View Detail Page" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                                <i class="fas fa-eye fs-6"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                                    <i class="fas fa-calendar-times fs-2 text-secondary opacity-75"></i>
                                </div>
                                <h6 class="fw-bold text-dark">No Logs Found</h6>
                                <p class="small">You haven't submitted any logs for this week yet.</p>
                                <a href="{{ route('student.log-entries') }}" class="btn btn-sm btn-primary px-3 rounded-pill mt-2">Log Activity Now</a>
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    </div>
@else
    <p class="text-muted">No internship data available.</p>
@endif

@endsection

@push('scripts')
<script>
    function showDrillDown(week) {
        // Remove active state
        document.querySelectorAll('.week-card').forEach(card => card.classList.remove('active-card'));
        
        // Add active state to clicked
        const clickedCard = document.getElementById('cardWeek' + week);
        if(clickedCard) {
            clickedCard.classList.add('active-card');
        }
        
        // Hide all week contents
        document.querySelectorAll('.drill-down-content').forEach(content => {
            content.classList.remove('show');
        });
        
        // Show correct week content
        const targetContent = document.getElementById('weekContent' + week);
        if(targetContent) {
            targetContent.classList.add('show');
        }
        
        // Update Drill-Down Title
        document.getElementById('drillDownTitle').textContent = 'Daily Logs for Week ' + week;
        
        // Show the drill-down container
        const container = document.getElementById('drillDownContainer');
        container.style.display = 'block';
        
        // Smooth scroll to drill-down container (compensating for sticky header if any)
        setTimeout(() => {
            const y = container.getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }, 50);
    }
    
    function closeDrillDown() {
        // Hide the drill-down container with a fade out effect (optional, simple none for now)
        document.getElementById('drillDownContainer').style.display = 'none';
        
        // Reset card selections
        document.querySelectorAll('.week-card').forEach(card => card.classList.remove('active-card'));
        
        // Scroll back up slightly
        window.scrollTo({ top: document.getElementById('weeklyGrid').offsetTop - 100, behavior: 'smooth' });
    }
</script>
@endpush
