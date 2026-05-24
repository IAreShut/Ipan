@extends('layouts.app')

@section('title', 'Student Details - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/supervisor-student-show.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link active" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.tasks')}}"><i class="fas fa-flag-checkered"></i> Tasks</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
<a class="nav-link" href="{{ route('supervisor.assigned-students') }}"><i class="fas fa-user-plus"></i> Assign Student</a>
<a class="nav-link" href="{{ route('supervisor.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Student Detail')
@section('page-subtitle', 'Detailed view and progress of your supervised student.')

@section('header-right')
<a href="{{ url()->previous() }}" class="btn btn-primary-custom px-4">
    <i class="fas fa-arrow-left me-2"></i> Back
</a>
@endsection

@section('main-content')
<!-- Student Header -->
<div class="row animate-slide-up">
    <div class="col-12">
        <div class="card profile-card p-4">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
                <div class="avatar-container position-relative">
                    <img src="{{ $student->avatar ? (str_starts_with($student->avatar, 'http') ? $student->avatar : asset('storage/' . $student->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name) . '&background=random' }}" class="rounded-circle shadow-sm" width="120" height="120" style="object-fit: cover; border: 4px solid #fff;" alt="{{ $student->name }}">
                    @if($progressPct == 100)
                    <span class="position-absolute bottom-0 end-0 badge bg-success rounded-circle border border-white border-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Completed"><i class="fas fa-check"></i></span>
                    @elseif($pendingCount > 0)
                    <span class="position-absolute bottom-0 end-0 badge bg-warning rounded-circle border border-white border-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Action Needed"><i class="fas fa-exclamation"></i></span>
                    @else
                    <span class="position-absolute bottom-0 end-0 badge bg-primary rounded-circle border border-white border-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Active"><i class="fas fa-circle"></i></span>
                    @endif
                </div>

                <div class="flex-grow-1 text-center text-md-start">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-start mb-2">
                        <div>
                            <h3 class="fw-bold mb-1 text-dark">{{ $student->name }}</h3>
                            <p class="text-muted mb-0"><i class="fas fa-id-card me-2"></i>{{ $student->matrix_id ?? 'No Matrix ID' }}</p>
                        </div>
                        <div class="mt-3 mt-md-0 d-flex gap-2">
                            <a href="mailto:{{ $student->email }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-envelope me-1"></i>Email</a>
                            @if($student->phone)
                            <a href="tel:{{ $student->phone }}" class="btn btn-sm btn-outline-success rounded-pill"><i class="fas fa-phone me-1"></i>Call</a>
                            @endif
                        </div>
                    </div>

                    <hr class="text-muted my-3">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2" style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Academic Profile</h6>
                            <p class="mb-1 fw-medium" style="font-size: 0.95rem;">{{ $student->faculty ?? 'Faculty not set' }}</p>
                            <p class="mb-0 text-muted" style="font-size: 0.85rem;">Programme: {{ is_array($student->programme_code) ? implode(', ', $student->programme_code) : ($student->programme_code ?? 'Not set') }} | Class: {{ is_array($student->class) ? implode(', ', $student->class) : ($student->class ?? 'Not set') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2" style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Internship Profile</h6>
                            <p class="mb-1 fw-medium" style="font-size: 0.95rem;">{{ $internship->company_name ?? 'Not set' }}</p>
                            <p class="mb-0 text-muted" style="font-size: 0.85rem;">{{ $internship ? $internship->start_date->format('M d, Y') . ' - ' . $internship->end_date->format('M d, Y') : 'Dates not set' }} ({{ $totalWeeks }} Weeks)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Center Column: Progress & Log Feed -->
    <div class="col-lg-8 animate-slide-up delay-100">

        <!-- Interactive Progress Matrix -->
        <div class="card premium-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="fas fa-th text-primary me-2"></i> Logbook Progress Matrix</h5>
                <span class="badge bg-light text-dark border">{{ $progressPct }}% Complete</span>
            </div>

            <div class="progress-grid">
                @for($w = 1; $w <= $totalWeeks; $w++)
                    @php
                    $status=$weeklyProgress[$w] ?? 'empty' ;
                    $statusClass='empty' ;
                    $icon='fa-circle' ;
                    if ($status=='approved' ) { $statusClass='approved' ; $icon='fa-check' ; }
                    if ($status=='pending' ) { $statusClass='pending' ; $icon='fa-clock' ; }
                    if ($status=='rejected' ) { $statusClass='rejected' ; $icon='fa-times' ; }
                    if ($status=='mixed' ) { $statusClass='mixed' ; $icon='fa-spinner' ; }
                    @endphp
                    <div class="week-cell {{ $statusClass }}" data-week="{{ $w }}" title="Week {{ $w }}: {{ ucfirst($status) }}">
                    <div class="week-label">W{{ $w }}</div>
                    <i class="fas {{ $icon }}"></i>
            </div>
            @endfor
        </div>
        <div class="d-flex justify-content-center gap-3 mt-3 flex-wrap" style="font-size: 0.75rem;">
            <div class="d-flex align-items-center gap-1"><span class="legend-color approved"></span> Approved</div>
            <div class="d-flex align-items-center gap-1"><span class="legend-color pending"></span> Pending</div>
            <div class="d-flex align-items-center gap-1"><span class="legend-color rejected"></span> Rejected</div>
            <div class="d-flex align-items-center gap-1"><span class="legend-color mixed"></span> In Progress</div>
            <div class="d-flex align-items-center gap-1"><span class="legend-color empty"></span> Empty</div>
        </div>
    </div>

    <!-- Chronological Timeline -->
    <div class="card premium-card p-4">
        <h5 class="fw-bold mb-4"><i class="fas fa-history text-primary me-2"></i> Logbook Feed</h5>

        <div class="timeline-modern" id="logbookFeed">
            @forelse($logEntries as $log)
            <div class="timeline-item log-item" data-week="{{ $log->week_number }}">
                <div class="timeline-icon {{ $log->status }}"></div>
                <div class="timeline-content w-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold mb-1">Week {{ $log->week_number }} <span class="text-muted fw-normal" style="font-size: 0.85rem;">| Day {{ \Carbon\Carbon::parse($log->entry_date)->dayOfWeekIso }}</span></h6>
                            <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($log->entry_date)->format('l, d M Y') }}</p>
                        </div>
                        <span class="badge badge-status-{{ $log->status === 'approved' ? 'approved' : ($log->status === 'rejected' ? 'rejected' : 'pending') }}">
                            {{ ucfirst($log->status) }}
                        </span>
                    </div>

                    <div class="task-description-box p-3 rounded bg-light mb-3" style="font-size: 0.9rem; white-space: pre-line;">{{ $log->task_description }}</div>

                    @if($log->ai_summary)
                    <div class="ai-summary-box p-3 rounded mb-3 position-relative" style="background-color: #EFF6FF; border: 1px solid #BFDBFE;">
                        <i class="fas fa-sparkles position-absolute text-primary" style="top: -8px; left: -8px; font-size: 1.2rem;"></i>
                        <h6 class="fw-bold text-primary mb-1" style="font-size: 0.8rem; text-transform: uppercase;">AI Summary</h6>
                        <p class="mb-0 text-dark" style="font-size: 0.85rem;">{{ $log->ai_summary }}</p>
                    </div>
                    @endif

                    @if($log->attachments->count() > 0)
                    <div class="attachment-gallery d-flex flex-wrap gap-2 mb-3">
                        @foreach($log->attachments as $attachment)
                        <a href="javascript:void(0)" onclick="openImageModal('{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset($attachment->file_path) }}')" class="attachment-thumb">
                            <img src="{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset($attachment->file_path) }}" class="rounded shadow-sm" style="width: 72px; height: 72px; object-fit: cover; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                        </a>
                        @endforeach
                    </div>
                    @endif

                    @if($log->status === 'pending')
                    <div class="d-flex gap-2">
                        <form action="{{ route('supervisor.approve', $log->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold approve-btn">
                                <i class="fas fa-check me-1"></i> Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" data-bs-toggle="collapse" data-bs-target="#rejectForm-{{ $log->id }}">
                            <i class="fas fa-times me-1"></i> Reject
                        </button>
                    </div>

                    <!-- Reject Form Collapse -->
                    <div class="collapse mt-2" id="rejectForm-{{ $log->id }}">
                        <form action="{{ route('supervisor.reject', $log->id) }}" method="POST" class="p-3 border rounded bg-white shadow-sm">
                            @csrf
                            <label class="form-label fw-bold text-danger" style="font-size: 0.85rem;">Reason for Rejection (Required)</label>
                            <textarea name="supervisor_comment" class="form-control mb-2" rows="2" required placeholder="Please explain what needs to be fixed..."></textarea>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#rejectForm-{{ $log->id }}">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-danger fw-semibold">Confirm Rejection</button>
                            </div>
                        </form>
                    </div>
                    @elseif($log->status === 'rejected' && $log->supervisor_comment)
                    <div class="p-3 rounded mt-2" style="background-color: #FEF2F2; border: 1px solid #FECACA;">
                        <h6 class="fw-bold text-danger mb-1" style="font-size: 0.8rem; text-transform: uppercase;">Supervisor Feedback</h6>
                        <p class="mb-0 text-danger" style="font-size: 0.85rem;">{{ $log->supervisor_comment }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center p-5 text-muted border rounded" style="background-color: #f8fafc; border-style: dashed !important;">
                <i class="fas fa-clipboard fs-1 mb-3 opacity-50"></i>
                <p class="mb-0">No log entries found for this student.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Right Column: Task Tracker -->
<div class="col-lg-4 animate-slide-up delay-200">
    <div class="card premium-card p-4 sticky-top" style="top: 2rem;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold m-0"><i class="fas fa-tasks text-primary me-2"></i> Tasks Assigned</h5>
            <button class="btn btn-sm btn-primary-custom rounded-circle" style="width: 32px; height: 32px;" title="Assign new task to all students (global)">
                <i class="fas fa-plus"></i>
            </button>
        </div>

        <div class="task-list d-flex flex-column gap-3">
            @forelse($tasks as $task)
            <div class="p-3 {{ $task->isCompleted() ? 'bg-light text-muted' : 'bg-white shadow-sm' }}" style="border-radius: 1rem; border: 1px solid #e2e8f0;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold m-0 {{ $task->isCompleted() ? 'text-decoration-line-through' : '' }}" style="font-size: 0.9rem;">{{ $task->title }}</h6>
                    @if($task->isCompleted())
                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Done</span>
                    @else
                    <span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-clock"></i> Pending</span>
                    @endif
                </div>
                <div class="d-flex align-items-center mb-3" style="font-size: 0.8rem;">
                    <i class="far fa-calendar-alt me-2 text-primary"></i>
                    <span class="{{ $task->due_date->isPast() && !$task->isCompleted() ? 'text-danger fw-bold' : 'text-muted' }}">
                        Due: {{ $task->due_date->format('d M Y') }}
                    </span>
                </div>
                @if(!$task->isCompleted())
                <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-semibold" onclick="alert('Nudge sent! (Mock implementation)')">
                    <i class="fas fa-bell me-1"></i> Nudge Student
                </button>
                @else
                <div class="text-success text-center fw-semibold" style="font-size: 0.75rem;">
                    Completed on {{ $task->completed_at->format('d M Y') }}
                </div>
                @endif
            </div>
            @empty
            <div class="text-center p-4 text-muted border rounded" style="background-color: #f8fafc; border-style: dashed !important;">
                <i class="fas fa-clipboard-check fs-2 mb-2 opacity-50"></i>
                <p class="mb-0 small">No tasks assigned yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
</div>

<!-- Image Popup Modal -->
<div class="modal fade" id="imagePopupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0 p-2 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="background-color: rgba(255,255,255,0.8); border-radius: 50%; padding: 0.5rem;"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="popupImagePreview" src="" class="img-fluid rounded shadow-lg" style="max-height: 85vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/supervisor/student-show.js') }}"></script>
@endpush