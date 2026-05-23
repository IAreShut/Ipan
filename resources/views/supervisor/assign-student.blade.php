@extends('layouts.app')

@section('title', 'Assign Student - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/supervisor-assign-student.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.tasks') }}"><i class="fas fa-flag-checkered"></i> Tasks</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
<a class="nav-link active" href="{{ route('supervisor.assigned-students') }}"><i class="fas fa-user-plus"></i> Assign Student</a>
<a class="nav-link" href="{{ route('supervisor.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Assign Student')
@section('page-subtitle', 'Manage and track assigned internship students')

@section('main-content')
<!-- Top Stat Cards -->
<div class="row g-4 mb-4 animate-slide-up">
    <!-- Assigned Card -->
    <div class="col-md-3">
        <div class="card card-custom stat-card-modern h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div class="pe-2">
                    <div class="text-muted small mb-1 fw-bold">Assigned</div>
                    <h3 class="mb-0 fw-bold">{{ $totalAssigned }}</h3>
                </div>
                <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0"><i class="fas fa-user-friends"></i></div>
            </div>
            <div class="mt-3 text-muted small">Total assigned students</div>
        </div>
    </div>
    
    <!-- Completed Card -->
    <div class="col-md-3">
        <div class="card card-custom stat-card-modern h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div class="pe-2">
                    <div class="text-muted small mb-1 fw-bold">Completed</div>
                    <h3 class="mb-0 fw-bold">{{ $completedCount }}</h3>
                </div>
                <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle flex-shrink-0"><i class="fas fa-user-check"></i></div>
            </div>
            <div class="mt-3 text-muted small">Finished internship</div>
        </div>
    </div>
    
    <!-- In Progress Card -->
    <div class="col-md-3">
        <div class="card card-custom stat-card-modern h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div class="pe-2">
                    <div class="text-muted small mb-1 fw-bold">In Progress</div>
                    <h3 class="mb-0 fw-bold">{{ $inProgressCount }}</h3>
                </div>
                <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle flex-shrink-0"><i class="fas fa-spinner fa-spin-hover"></i></div>
            </div>
            <div class="mt-3 text-muted small">Active internship</div>
        </div>
    </div>
    
    <!-- Overdue Card -->
    <div class="col-md-3">
        <div class="card card-custom stat-card-modern h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div class="pe-2">
                    <div class="text-muted small mb-1 fw-bold">Overdue</div>
                    <h3 class="mb-0 fw-bold">{{ $overdueCount }}</h3>
                </div>
                <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-circle flex-shrink-0"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="mt-3 text-muted small">Needs attention</div>
        </div>
    </div>
</div>

<!-- Student Profile Cards Section -->
<div class="row mb-5 animate-slide-up delay-100">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0"><i class="fa-solid fa-id-badge text-primary me-2"></i> Registered Profiles</h4>
            <span class="badge bg-light text-dark border">{{ $students->count() }} Profiles</span>
        </div>
        
        <div class="student-grid">
            @forelse($students as $student)
                @php
                    $internship = $student->internships->first();
                @endphp
                <a href="{{ route('supervisor.students.show', $student->id) }}" class="student-profile-card">
                    <div class="avatar-wrapper">
                        <img src="{{ $student->avatar ? (str_starts_with($student->avatar, 'http') ? $student->avatar : asset('storage/' . $student->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name) . '&background=E0E7FF&color=4F46E5&size=90' }}" 
                             class="student-avatar" 
                             alt="{{ $student->name }}">
                    </div>
                    
                    <div class="student-name">{{ $student->name }}</div>
                    <div class="student-prog">
                        {{ is_array($student->programme_code) ? implode(', ', $student->programme_code) : ($student->programme_code ?? 'N/A') }} 
                        @if($student->class)
                            - {{ is_array($student->class) ? implode(', ', $student->class) : $student->class }}
                        @endif
                    </div>
                    
                    <div class="student-meta mb-1">
                        <i class="fas fa-building text-muted me-1"></i> 
                        <span class="text-truncate" style="max-width: 220px;" title="{{ $student->company ?? 'Not set' }}">
                            {{ $student->company ?? 'No company registered' }}
                        </span>
                    </div>
                    
                    <div class="student-meta">
                        <i class="fas fa-id-badge text-muted me-1"></i> ID: {{ $student->matrix_id }}
                    </div>
                    
                    <div class="student-email" onclick="event.stopPropagation();">
                        <i class="fas fa-envelope me-1"></i> {{ $student->email }}
                    </div>
                </a>
            @empty
                <div class="col-12 text-center p-5 bg-white rounded-4 border" style="border-style: dashed !important;">
                    <i class="fas fa-user-slash fs-2 mb-3 text-muted"></i>
                    <p class="text-muted mb-0">No registered students under your supervision yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Active Assign Student Table Section -->
<div class="row animate-slide-up delay-200">
    <div class="col-12">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="fas fa-users-cog text-primary me-2"></i> Active Assigned Students</h5>
            </div>

            <div class="table-responsive">
                <table id="assignedStudentsTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 35%;">Student Name / Info</th>
                            <th style="text-align: center; width: 10%;">Assigned</th>
                            <th style="text-align: center; width: 10%;">Completed</th>
                            <th style="text-align: center; width: 10%;">In Progress</th>
                            <th style="text-align: center; width: 10%;">Overdue</th>
                            <th style="text-align: center; width: 10%;">Status</th>
                            <th style="text-align: center; width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tableStudents as $row)
                        <tr>
                            <td>{{ $loop->iteration }}.</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $row->avatar ? (str_starts_with($row->avatar, 'http') ? $row->avatar : asset('storage/' . $row->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($row->name) . '&background=random&size=40' }}" 
                                         class="rounded-circle me-3" 
                                         width="40" 
                                         height="40" 
                                         style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $row->name }}</div>
                                        <div class="text-muted small">
                                            <span>ID: {{ $row->matrix_id }}</span>
                                            @if($row->email != '-')
                                                <span class="mx-1">|</span>
                                                <span>{{ $row->email }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($row->tasks_assigned !== '-')
                                    <span class="text-value-metric text-primary">{{ $row->tasks_assigned }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row->tasks_completed !== '-')
                                    <span class="text-value-metric text-success">{{ $row->tasks_completed }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row->tasks_in_progress !== '-')
                                    <span class="text-value-metric text-warning">{{ $row->tasks_in_progress }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row->tasks_overdue !== '-')
                                    <span class="text-value-metric text-danger">{{ $row->tasks_overdue }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                @if($row->registered)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill text-nowrap"><i class="fas fa-check-circle me-1"></i>Active</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill text-nowrap"><i class="fas fa-clock me-1"></i>Pending Reg</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row->registered)
                                    <a href="{{ route('supervisor.students.show', $row->user_id) }}" class="btn-table-action action-view px-3 py-1-5">
                                        <i class="fas fa-eye"></i> View Profile
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" onclick="alert('Invitation link copied for {{ $row->name }}! (Mock)')" style="font-size: 0.75rem;">
                                        <i class="fas fa-share-alt me-1"></i> Invite
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-users-slash fs-3 mb-2 d-block"></i> No assigned student records found.
                            </td>
                        </tr>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if ($.fn.DataTable.isDataTable('#assignedStudentsTable')) {
            $('#assignedStudentsTable').DataTable().destroy();
        }
        
        $('#assignedStudentsTable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search students...",
            },
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: [1, 7] }
            ]
        });
    });
</script>
@endpush
