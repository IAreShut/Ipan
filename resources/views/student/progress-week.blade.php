@extends('layouts.app')

@section('title', 'Weekly Logs - LIMS')

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

@section('page-title', 'Weekly Details')

@section('header-right')
<a href="{{ url()->previous() }}" class="btn btn-primary-custom px-4">
    <i class="fas fa-arrow-left me-2"></i> Back
</a>
@endsection

@section('main-content')
<div class="mb-4">
    <h4 class="fw-bold text-lims-navy m-0">Daily Logs for Week {{ $week }}</h4>
    <p class="text-muted mt-1 mb-0"><i class="fas fa-info-circle me-1"></i> Review your daily entries and their approval statuses</p>
</div>

@if($weekLogs->count() > 0)
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-5">
        @foreach($weekLogs as $log)
    <div class="col">
        <div class="card week-card h-100 shadow-sm border">
            <div class="card-body p-4 d-flex flex-column">
                <!-- Profile & Date -->
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm me-2" style="width: 28px; height: 28px; background-color: #1E40AF !important;">
                            <span class="fw-bold" style="font-size: 0.75rem;">{{ $log->entry_date->format('d') }}</span>
                        </div>
                        <span class="fw-bold text-dark fs-6">{{ $log->entry_date->format('l') }}</span>
                    </div>
                    <div class="text-muted small fw-medium">
                        {{ $log->entry_date->format('d M Y') }}
                    </div>
                </div>

                <!-- Title & Attachment -->
                <div class="d-flex justify-content-between align-items-start flex-grow-1">
                    <div class="me-3">
                        <h6 class="mb-1 fw-bold text-dark">Week {{ $log->week_number }} Log Entry</h6>
                        <small class="text-muted d-block">{{ Str::limit($log->task_description, 100, '...') }}</small>
                    </div>
                    @if($log->attachments && $log->attachments->count() > 0)
                        <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn btn-sm rounded-pill fw-bold border-0 px-3 text-nowrap text-decoration-none" 
                                style="background-color: #E0F2FE; color: #0284C7; font-size: 0.75rem;">
                            <i class="fas fa-paperclip me-1"></i> +{{ $log->attachments->count() }}
                        </a>
                    @endif
                </div>

                <!-- Action / Status -->
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div>
                        @if($log->status === 'approved')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold">Approved</span>
                        @elseif($log->status === 'rejected')
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-bold">Rejected</span>
                        @elseif($log->status === 'pending')
                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-bold text-dark">Pending</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill fw-bold">Draft</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @if($log->status === 'draft')
                            <a href="{{ route('student.log-entries.edit', $log->id) }}" class="btn btn-sm btn-light bg-warning bg-opacity-10 text-warning border rounded-3 d-flex align-items-center px-3 fw-bold py-2" title="Edit Draft">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn btn-light btn-sm border rounded-3 d-flex align-items-center px-4 fw-bold py-2 text-dark" title="View Full Entry">
                            View
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
        @endforeach
</div>
    @else
        <div class="mb-5">
            <div class="text-center py-5 text-muted bg-white rounded-4 border shadow-sm">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                    <i class="fas fa-calendar-times fs-2 text-secondary opacity-75"></i>
                </div>
                <h6 class="fw-bold text-dark">No Logs Found</h6>
                <p class="small">You haven't submitted any logs for this week yet.</p>
                <a href="{{ route('student.log-entries') }}" class="btn btn-sm btn-primary-custom px-4 py-2 rounded-pill mt-3 shadow-sm">Log Activity Now</a>
            </div>
        </div>
    @endif
@endsection
