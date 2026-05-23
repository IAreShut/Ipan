@extends('layouts.app')

@section('title', 'Notifications & Reminders - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/student-notifications.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link active" href="{{ route('student.notifications') }}"><i class="fas fa-bell"></i> Notifications</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Notifications & Reminders')
@section('page-subtitle', 'Manage your deadlines and view system alerts.')

@section('main-content')
<div class="row g-4">
    <!-- Center Column: Calendar -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h5 class="fw-bold m-0"><i class="fas fa-calendar-alt text-primary me-2"></i> My Calendar</h5>
                
                <div class="d-flex gap-2">
                    <!-- Notification Bell Dropdown -->
                    @php
                        $unreadCount = $notifications->where('is_read', false)->count();
                    @endphp
                    <div class="dropdown">
                        <button class="btn btn-light border position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px; border-radius: 50%;">
                            <i class="fas fa-bell"></i>
                            <span id="notificationBadge" class="position-absolute align-items-center justify-content-center translate-middle badge rounded-pill bg-danger" style="top: 5px; left: 85%; font-size: 0.65rem; display: {{ $unreadCount > 0 ? 'flex' : 'none' }}">
                                {{ $unreadCount }}
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" aria-labelledby="notificationDropdown" style="width: 350px; z-index: 1050;">
                            <div class="p-3 border-bottom bg-light rounded-top">
                                <h6 class="fw-bold m-0"><i class="fas fa-clock text-warning me-2"></i> Recent Alerts</h6>
                            </div>
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                @forelse($notifications as $notification)
                                    <div class="list-group-item list-group-item-action p-3 {{ $notification->is_read ? 'bg-white' : 'bg-light' }}">
                                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                                @if($notification->type == 'success')
                                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Action</span>
                                                @elseif($notification->type == 'danger')
                                                    <span class="badge bg-danger rounded-pill"><i class="fas fa-exclamation-circle"></i> Alert</span>
                                                @elseif($notification->type == 'warning')
                                                    <span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-exclamation-triangle"></i> Warning</span>
                                                @else
                                                    <span class="badge bg-primary rounded-pill"><i class="fas fa-info-circle"></i> Task</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                        <h6 class="fw-bold mt-2 mb-1" style="font-size: 0.9rem;">{{ $notification->title }}</h6>
                                        <p class="mb-2 text-secondary" style="font-size: 0.8rem;">{{ $notification->message }}</p>
                                        
                                        @if(!$notification->is_read)
                                            <form action="{{ route('student.notifications.read', $notification) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0" style="font-size: 0.75rem;">Mark as read</button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted">
                                        <i class="fas fa-check-circle fa-2x mb-2 text-light"></i>
                                        <p class="mb-0" style="font-size: 0.85rem;">You're all caught up!</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Add Reminder Button -->
                    <button type="button" class="btn btn-primary-custom btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addReminderModal" style="height: 40px;">
                        <i class="fas fa-plus me-1"></i> Add Reminder
                    </button>
                </div>
            </div>
            <!-- FullCalendar Container -->
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Right Column: Task Checklist -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold mb-4"><i class="fas fa-tasks text-primary me-2"></i> My Tasks</h5>
            
            <div class="task-list d-flex flex-column gap-3" style="max-height: 600px; overflow-y: auto;">
                @forelse($tasks->where('type', 'sv_task') as $task)
                    <div class="p-3 {{ $task->isCompleted() ? 'bg-light text-muted' : 'bg-white shadow-sm' }}" style="border-radius: 1rem; border: 1px solid #e2e8f0;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold m-0 {{ $task->isCompleted() ? 'text-decoration-line-through' : '' }}" style="font-size: 0.95rem;">{{ $task->title }}</h6>
                            @if($task->isCompleted())
                                <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Done</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-clock"></i> Pending</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center mb-3" style="font-size: 0.8rem;">
                            <i class="far fa-calendar-alt me-2 text-primary"></i>
                            <span class="{{ $task->due_date->isPast() && !$task->isCompleted() ? 'text-danger fw-bold' : 'text-muted' }}">
                                Due: {{ $task->due_date->format('d M Y, h:i A') }}
                            </span>
                        </div>
                        @if(!$task->isCompleted())
                            <form action="{{ route('student.tasks.complete', $task) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success w-100 rounded-pill fw-semibold">
                                    <i class="fas fa-check-circle me-1"></i> Mark as Done
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="text-center p-4 text-muted border rounded" style="background-color: #f8fafc; border-style: dashed !important;">
                        <i class="fas fa-clipboard-check fs-2 mb-2 opacity-50"></i>
                        <p class="mb-0 small">No pending tasks.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1" aria-labelledby="addReminderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white border-bottom-0">
                <h5 class="modal-title" id="addReminderModalLabel"><i class="fas fa-clock me-2"></i> New Personal Reminder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('student.reminders.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reminder Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Update logbook for Week 4" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" name="due_date" id="reminder_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Time</label>
                            <input type="time" name="due_time" id="reminder_time" class="form-control" value="17:00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="fas fa-save me-1"></i> Save Reminder</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script>
    window.LIMS = window.LIMS || {};
    window.LIMS.calendarEvents = @json($calendarEvents);
</script>
<script src="{{ asset('js/student/notifications.js') }}"></script>
@endpush
