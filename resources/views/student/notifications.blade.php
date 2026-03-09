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
    <!-- Left Column: Calendar & Add Reminder -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="fas fa-calendar-alt text-primary me-2"></i> My Calendar</h5>
                <button type="button" class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                    <i class="fas fa-plus me-1"></i> Add Reminder
                </button>
            </div>
            <!-- FullCalendar Container -->
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Right Column: Notifications Feed -->
    <div class="col-lg-4">
        
        <!-- Notifications List -->
        <div class="card card-custom p-0 h-100 overflow-hidden">
            <div class="p-4 border-bottom bg-light">
                <h5 class="fw-bold m-0"><i class="fas fa-clock text-warning me-2"></i> Recent Alerts</h5>
            </div>
            
            <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                @forelse($notifications as $notification)
                    <div class="list-group-item list-group-item-action p-4 {{ $notification->is_read ? 'bg-white' : 'bg-light' }}">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                @if($notification->type == 'success')
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i> Action Feedback</span>
                                @elseif($notification->type == 'danger')
                                    <span class="badge bg-danger rounded-pill"><i class="fas fa-exclamation-circle"></i> System Alert</span>
                                @elseif($notification->type == 'warning')
                                    <span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-exclamation-triangle"></i> Warning</span>
                                @else
                                    <span class="badge bg-primary rounded-pill"><i class="fas fa-info-circle"></i> SV Milestone</span>
                                @endif
                            </h6>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <h6 class="fw-bold mt-2">{{ $notification->title }}</h6>
                        <p class="mb-2 text-secondary">{{ $notification->message }}</p>
                        
                        @if(!$notification->is_read)
                            <form action="{{ route('student.notifications.read', $notification) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0">Mark as read</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-5 text-center text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3 text-light"></i>
                        <p>You're all caught up! No new notifications.</p>
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
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        // Prepare events data from PHP
        var events = [
            @foreach($milestones as $milestone)
            {
                title: '{{ addslashes($milestone->title) }}',
                start: '{{ $milestone->due_date->format('Y-m-d\TH:i:s') }}',
                className: '{{ $milestone->type === 'sv_milestone' ? 'fc-event-sv' : 'fc-event-personal' }}',
                allDay: false
            },
            @endforeach
        ];

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            events: events,
            displayEventTime: false,
            dateClick: function(info) {
                // Pre-fill date when clicking on calendar
                document.getElementById('reminder_date').value = info.dateStr;
                var modal = new bootstrap.Modal(document.getElementById('addReminderModal'));
                modal.show();
            },
            eventClick: function(info) {
                // Show event details (rudimentary using standard alert, or SweetAlert)
                Swal.fire({
                    title: info.event.title,
                    text: 'Due: ' + info.event.start.toLocaleString(),
                    icon: 'info',
                    confirmButtonColor: '#3b82f6'
                });
            },
            height: 600
        });
        
        calendar.render();
    });
</script>
@endpush
