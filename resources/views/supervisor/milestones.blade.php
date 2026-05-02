@extends('layouts.app')

@section('title', 'Milestone Manager - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/supervisor-dashboard.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-tasks"></i> Review Logbooks</a>
<a class="nav-link active" href="{{ route('supervisor.milestones') }}"><i class="fas fa-flag-checkered"></i> Milestones</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-pie"></i> Analytics</a>
<a class="nav-link" href="{{ route('supervisor.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Milestone Manager')
@section('page-subtitle', 'Assign deadlines and milestones to your students.')

@section('main-content')
<div class="row g-4">
    <!-- Milestone Form Column -->
    <div class="col-lg-4">
        <div class="card card-custom p-4">
            <h5 class="fw-bold mb-4"><i class="fas fa-calendar-plus text-primary me-2"></i> Set New Milestone</h5>
                
            <form action="{{ route('supervisor.milestones.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Student</label>
                    <select class="form-select @error('student_id') is-invalid @enderror" name="student_id" required>
                        <option value="">-- Choose a student --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }} ({{ $student->matrix_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Milestone Title</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" placeholder="e.g. Final Submission, Presentation Date" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label fw-bold">Due Date</label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" name="due_date" value="{{ old('due_date') }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Time (Optional)</label>
                            <input type="time" class="form-control" name="due_time" value="{{ old('due_time', '23:59') }}">
                        </div>
                    </div>
                </div>
                
                <div class="d-grid mt-auto">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-paper-plane me-2"></i> Assign Milestone & Notify
                    </button>
                    <small class="text-muted text-center mt-2 d-block"><i class="fas fa-info-circle"></i> This triggers an app notification and email instantly.</small>
                </div>
            </form>
        </div>
    </div>

    <!-- Milestones Overview Table -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 h-100">
            <h5 class="fw-bold mb-4"><i class="fas fa-table text-success me-2"></i> Overview Table</h5>
            
            <div class="table-responsive">
                <table id="milestonesTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Milestone</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($milestones as $milestone)
                            @php
                                $isOverdue = $milestone->due_date->isPast();
                                $isToday = $milestone->due_date->isToday();
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($milestone->user->name) }}&background=random&size=32" class="rounded-circle me-2" alt="">
                                        <span class="fw-medium">{{ $milestone->user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $milestone->title }}</td>
                                <td>
                                    <span class="{{ $isOverdue ? 'text-secondary' : ($isToday ? 'text-warning fw-bold' : '') }}">
                                        {{ $milestone->due_date->format('d M Y, h:i A') }}
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
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('js/supervisor/milestones.js') }}"></script>
@endpush
