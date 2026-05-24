@extends('layouts.app')

@section('title', 'Task Manager - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/supervisor-dashboard.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link active" href="{{ route('supervisor.tasks') }}"><i class="fas fa-flag-checkered"></i> Tasks</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
<a class="nav-link" href="{{ route('supervisor.assigned-students') }}"><i class="fas fa-user-plus"></i> Assign Student</a>
<a class="nav-link" href="{{ route('supervisor.profile') }}"><i class="fa-solid fa-circle-user"></i> Profile</a>
@endsection

@section('page-title', 'Task Manager')
@section('page-subtitle', 'Assign deadlines and tasks to your students.')

@section('main-content')
<div class="row g-4">
    <!-- Tasks Overview Table -->
    <div class="col-lg-12">
        <div class="card card-custom p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-table text-success me-2"></i> Overview Table</h5>
                <button type="button" class="btn btn-primary-custom px-4 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="fas fa-plus me-2"></i>Add New Task
                </button>
            </div>
            
            <div class="table-responsive">
                <table id="tasksTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Student Name</th>
                            <th>Group</th>
                            <th>Task</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            @php
                                $isOverdue = $task->due_date->isPast();
                                $isToday = $task->due_date->isToday();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}.</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($task->user->name) }}&background=random&size=32" class="rounded-circle me-2" alt="">
                                        <span class="fw-medium">{{ $task->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $task->user->programme_code ?? 'N/A' }}-{{ $task->user->class ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $task->title }}</td>
                                <td>
                                    <span class="{{ $isOverdue ? 'text-secondary' : ($isToday ? 'text-warning fw-bold' : '') }}">
                                        {{ $task->due_date->format('d M Y, h:i A') }}
                                    </span>
                                </td>
                                <td>
                                    @if($isOverdue)
                                        <span class="badge bg-secondary rounded-pill px-3">Past</span>
                                    @elseif($isToday)
                                        <span class="badge bg-warning text-dark rounded-pill px-3">Today</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-3">Upcoming</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <!-- Empty state handled by DataTables automatically, but just in case -->
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary-custom text-white border-bottom-0">
                <h5 class="modal-title" id="addTaskModalLabel"><i class="fas fa-calendar-plus me-2"></i> Set New Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('supervisor.tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Task Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" placeholder="e.g. Final Submission, Presentation Date" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Due Date</label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" name="due_date" value="{{ old('due_date', date('Y-m-d')) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Time (Optional)</label>
                            <input type="time" class="form-control" name="due_time" value="{{ old('due_time', '23:59') }}">
                        </div>
                        <small class="text-muted d-block"><i class="fas fa-info-circle me-1"></i> This will notify all students in the selected programme(s) via app notification and email.</small>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                        <i class="fas fa-paper-plane me-2"></i> Assign Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('js/supervisor/tasks.js') }}"></script>
@endpush
